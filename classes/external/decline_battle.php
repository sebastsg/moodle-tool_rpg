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
 * The external function to decline a battle or retreat from a battle.
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
use tool_rpg\rpg_battle;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

/**
 * Decline a battle that hasn't yet started, or retreat from an ongoing battle.
 */
class decline_battle extends external_api {

    /**
     * Get the function parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'battleid' => new external_value(PARAM_INT, 'The id for the battle to decline'),
        ]);
    }

    /**
     * Decline the battle request, or retreat from an ongoing battle.
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
        if (!$battle->can_retreat()) {
            throw new coding_exception('Battle cannot already be started.');
        }
        $battle->change_state(rpg_battle::STATE_RETREATED);
        $battle->save();
    }

    /**
     * We don't need to return anything.
     */
    public static function execute_returns() {
        return null;
    }

}
