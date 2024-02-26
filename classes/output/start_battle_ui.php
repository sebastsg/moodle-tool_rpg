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
 * The start battle UI.
 *
 * @package    tool_rpg
 * @copyright  2024 Sebastian Gundersen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_rpg\output;

use renderable;
use renderer_base;
use templatable;
use tool_rpg\rpg_battle;
use tool_rpg\rpg_monster;

/**
 * The UI for accepting a new battle.
 */
class start_battle_ui implements renderable, templatable {

    /** @var rpg_battle The new battle to render. */
    private rpg_battle $battle;

    /** @var rpg_monster The monster that provoked the battle. */
    private rpg_monster $monster;

    /**
     * Initialize.
     *
     * @param rpg_battle $battle
     * @param rpg_monster $monster
     */
    public function __construct(rpg_battle $battle, rpg_monster $monster) {
        $this->battle = $battle;
        $this->monster = $monster;
    }

    /**
     * Export parameters needed for the template.
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output): array {
        return [
            'battleid' => $this->battle->get_id(),
            'monsterimage' => rpg_monster::get_icon_url($this->monster->get_id()),
            'monstername' => $this->monster->get_name(),
            'attacktext' => $this->get_attack_text(),
            'declinetext' => $this->get_decline_text(),
        ];
    }

    /**
     * Returns a random text to use for the "Attack!" button.
     *
     * @return string
     */
    private function get_attack_text(): string {
        return match (random_int(0, 4)) {
            0 => get_string('letsgo', 'tool_rpg'),
            default => get_string('attack', 'tool_rpg')
        };
    }

    /**
     * Returns a random text to use for the "No thanks" button.
     *
     * @return string
     */
    private function get_decline_text(): string {
        return match (random_int(0, 5)) {
            0 => get_string('idrathereatdirt', 'tool_rpg'),
            1 => get_string('imterrified', 'tool_rpg'),
            default => get_string('nothanks', 'tool_rpg')
        };
    }

}
