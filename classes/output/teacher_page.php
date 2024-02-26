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
 * The teacher page.
 *
 * @package    tool_rpg
 * @copyright  2024 Sebastian Gundersen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_rpg\output;

use context_system;
use moodle_url;
use renderable;
use renderer_base;
use templatable;
use tool_rpg\local\xp_table;
use tool_rpg\rpg_item;
use tool_rpg\rpg_monster;

/**
 * The teacher page is where administrators can create and edit items and monsters.
 */
class teacher_page implements renderable, templatable {

    /**
     * Export parameters needed for the template.
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output): array {
        global $DB, $PAGE;

        $items = $DB->get_records('tool_rpg_item', sort: 'id ASC');
        $monsters = $DB->get_records('tool_rpg_monster', sort: 'id ASC');
        $characters = $DB->get_records('tool_rpg_character', sort: 'id ASC');
        $users = $DB->get_records_list('user', 'id', array_column($characters, 'userid'));

        $canedit = has_capability('tool/rpg:edit', context_system::instance());
        $isediting = $PAGE->user_is_editing();

        $data = [
            'canedit' => $canedit && $isediting,
            'items' => [],
            'monsters' => [],
            'characters' => [],
            'newitemlink' => new moodle_url('/admin/tool/rpg/edititem.php'),
            'newmonsterlink' => new moodle_url('/admin/tool/rpg/editmonster.php'),
        ];

        foreach ($items as $item) {
            $data['items'][] = [
                'id' => $item->id,
                'name' => $item->name,
                'rarity' => get_string($item->rarity, 'tool_rpg'),
                'type' => get_string($item->type, 'tool_rpg'),
                'editlink' => new moodle_url('/admin/tool/rpg/edititem.php', ['itemid' => $item->id]),
                'deletelink' => new moodle_url('/admin/tool/rpg/edititem.php', [
                    'itemid' => $item->id,
                    'action' => 'delete',
                ]),
                'iconurl' => rpg_item::get_icon_url($item->id),
            ];
        }

        foreach ($monsters as $monster) {
            $data['monsters'][] = [
                'id' => $monster->id,
                'name' => $monster->name,
                'level' => $monster->level,
                'hp' => $monster->hp,
                'editlink' => new moodle_url('/admin/tool/rpg/editmonster.php', ['monsterid' => $monster->id]),
                'deletelink' => new moodle_url('/admin/tool/rpg/editmonster.php', [
                    'monsterid' => $monster->id,
                    'action' => 'delete',
                ]),
                'iconurl' => rpg_monster::get_icon_url($monster->id),
            ];
        }

        foreach ($characters as $character) {
            $user = $users[$character->userid] ?? null;
            $data['characters'][] = [
                'displayname' => $user !== null ? fullname($user) : get_string('unknownuser'),
                'level' => xp_table::level_from_xp($character->xp),
            ];
        }

        return $data;
    }

}
