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
 * The adventure page.
 *
 * @package    tool_rpg
 * @copyright  2024 Sebastian Gundersen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_rpg\output;

use renderable;
use renderer_base;
use templatable;
use tool_rpg\local\xp_table;
use tool_rpg\rpg_battle;
use tool_rpg\rpg_character;
use tool_rpg\rpg_monster;

/**
 * The adventure page is where users can see their character info and battle monsters.
 */
class adventure_page implements renderable, templatable {

    /** @var rpg_character The character we want to show the adventure page for. */
    private rpg_character $character;

    /**
     * Initialize.
     *
     * @param rpg_character $character
     */
    public function __construct(rpg_character $character) {
        $this->character = $character;
    }

    /**
     * Export parameters needed for the template.
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output): array {
        $inventoryui = new inventory_ui($this->character->get_id());
        $inventory = $inventoryui->export_for_template($output);
        $ongoingbattle = [];
        $startbattle = [];
        $battle = rpg_battle::find_for_character($this->character->get_id(), rpg_battle::STATE_ONGOING);
        if ($battle) {
            $ongoingbattleui = new ongoing_battle_ui($battle);
            $ongoingbattle = $ongoingbattleui->export_for_template($output);
        } else {
            $battle = rpg_battle::find_for_character($this->character->get_id(), rpg_battle::STATE_NOTSTARTED);
            if (!$battle && random_int(0, 100) > 50) {
                $battle = new rpg_battle();
                $battle->setup($this->character->get_id());
            }
            if ($battle) {
                $monster = rpg_monster::find($battle->get_monsterid());
                if ($monster) {
                    $startbattleui = new start_battle_ui($battle, $monster);
                    $startbattle = $startbattleui->export_for_template($output);
                }
            }
        }
        return [
            'xp' => $this->character->get_xp(),
            'level' => $this->character->get_level(),
            'remainingxp' => xp_table::remaining_xp_until_next_level($this->character->get_xp()),
            'targetxp' => xp_table::xp_from_level($this->character->get_level() + 1),
            'characterhp' => $this->character->get_hp(),
            'charactermaxhp' => $this->character->get_max_hp(),
            'inventory' => $inventory,
            'ongoingbattle' => $ongoingbattle,
            'startbattle' => $startbattle,
            'lookfortroubletext' => $this->get_look_for_trouble_text(),
        ];
    }

    /**
     * Returns a random text to use for the "Look for trouble..." button.
     *
     * @return string
     */
    private function get_look_for_trouble_text(): string {
        return match (random_int(0, 10)) {
            0, 1, 2 => get_string('followthepathfurther', 'tool_rpg'),
            default => get_string('lookfortrouble', 'tool_rpg')
        };
    }

}
