<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * The inventory UI.
 *
 * @package    tool_rpg
 * @copyright  2024 Sebastian Gundersen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_rpg\output;

use renderable;
use renderer_base;
use templatable;
use tool_rpg\rpg_item;

/**
 * The inventory UI lists all the items owned by a character.
 */
class inventory_ui implements renderable, templatable {

    /** @var int Which character we want to render the inventory for. */
    private int $characterid;

    /**
     * Initialize.
     *
     * @param int $characterid
     */
    public function __construct(int $characterid) {
        $this->characterid = $characterid;
    }

    /**
     * Export parameters needed for the template.
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output): array {
        global $DB;
        $instances = $DB->get_records('tool_rpg_item_instance', ['characterid' => $this->characterid], 'id');
        $definitions = rpg_item::find_list(array_column($instances, 'itemid'));
        $items = [];
        foreach ($instances as $instance) {
            $item = $definitions[$instance->itemid] ?? null;
            if ($item === null) {
                $DB->delete_records('tool_rpg_item_instance', ['itemid' => $instance->itemid]);
                continue;
            }
            $items[] = [
                'iteminstanceid' => $instance->id,
                'stack' => $instance->stack,
                'itemid' => $item->get_id(),
                'itemname' => $item->get_name(),
                'stackable' => $item->is_stackable(),
                'itemtooltip' => $item->get_name(),
                'itemrarity' => $item->get_rarity(),
                'itemtype' => $item->get_type(),
                'itemicon' => rpg_item::get_icon_url($item->get_id()),
            ];
        }
        if (count($items) < 16) {
            $items = array_merge($items, $this->get_placeholder_items(16 - count($items)));
        }
        return ['items' => $items];
    }

    /**
     * Creates placeholder items that can be used in the template.
     *
     * @param int $count Number of placeholder items to create
     * @return array[]
     */
    private function get_placeholder_items(int $count): array {
        $placeholders = [];
        for ($i = 0; $i < $count; $i++) {
            $placeholders[] = [
                'placeholder' => true,
                'itemicon' => 'fa-solid fa-diamond',
                'itemtooltip' => get_string('emptyinventoryslot', 'tool_rpg'),
            ];
        }
        return $placeholders;
    }

}
