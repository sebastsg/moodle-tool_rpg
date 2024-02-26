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
 * The external function to attack a monster.
 *
 * @package    tool_rpg
 * @copyright  2024 Sebastian Gundersen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_rpg\external;

use coding_exception;
use context_system;
use external_api;
use external_function_parameters;
use external_value;
use tool_rpg\local\xp_table;
use tool_rpg\rpg_battle;
use tool_rpg\rpg_character;
use tool_rpg\rpg_monster;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

/**
 * Attack a monster in an ongoing battle.
 */
class attack_monster extends external_api {

    /**
     * Get the function parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'battleid' => new external_value(PARAM_INT, 'The id for the battle to attack in'),
        ]);
    }

    /**
     * Attack the monster, then get attacked by the monster should the attack not be fatal.
     *
     * @param int $battleid
     */
    public static function execute(int $battleid): void {
        self::validate_context(context_system::instance());
        $params = self::validate_parameters(self::execute_parameters(), ['battleid' => $battleid]);
        $battleid = $params['battleid'];
        $battle = rpg_battle::find($battleid);
        if (!$battle) {
            throw new coding_exception("No battle exists with this id: $battleid");
        }
        if (!$battle->is_ongoing()) {
            throw new coding_exception('Battle must be ongoing.');
        }
        $character = rpg_character::find($battle->get_characterid());
        $level = xp_table::level_from_xp($character->get_xp());
        $battle->damage_monster(random_int($level * 2, $level * 4));
        if (!$battle->has_ended()) {
            $monster = rpg_monster::find($battle->get_monsterid());
            $battle->damage_character(random_int($monster->get_level() * 3, $monster->get_level() * 4));
        }
    }

    /**
     * We don't need to return anything.
     */
    public static function execute_returns() {
        return null;
    }

}
