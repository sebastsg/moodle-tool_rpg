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
 * The ongoing battle UI.
 *
 * @package    tool_rpg
 * @copyright  2024 Sebastian Gundersen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_rpg\output;

use coding_exception;
use renderable;
use renderer_base;
use templatable;
use tool_rpg\rpg_battle;
use tool_rpg\rpg_monster;

/**
 * The UI for an ongoing battle.
 */
class ongoing_battle_ui implements renderable, templatable {

    /** @var rpg_battle The ongoing battle to render. */
    private rpg_battle $battle;

    /**
     * Initialize.
     *
     * @param rpg_battle $battle
     */
    public function __construct(rpg_battle $battle) {
        $this->battle = $battle;
    }

    /**
     * Export parameters needed for the template.
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output): array {
        $monster = rpg_monster::find($this->battle->get_monsterid());
        if (!$monster) {
            throw new coding_exception('Monster does not exist: ' . $this->battle->get_monsterid());
        }
        $data['battleid'] = $this->battle->get_id();
        $data['monsterimage'] = rpg_monster::get_icon_url($monster->get_id());
        $data['monstername'] = $monster->get_name();
        $data['monsterlevel'] = $monster->get_level();
        $data['monstermaxhp'] = $monster->get_hp();
        $data['monstercurrenthp'] = $this->battle->get_monsterhp();
        return $data;
    }

}
