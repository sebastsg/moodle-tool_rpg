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
 * The battle ended event.
 *
 * @package    tool_rpg
 * @copyright  2024 Sebastian Gundersen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_rpg\event;

use coding_exception;
use tool_rpg\rpg_battle;

/**
 * This event is fired when rpg_battle::has_ended() becomes true.
 *
 * @property-read array $other {
 *     - int $oldstate: One of [rpg_battle::NOT_STARTED, rpg_battle::ONGOING].
 *     - int $newstate: One of [rpg_battle::STATE_VICTORY, rpg_battle::STATE_DEFEAT, rpg_battle::STATE_RETREATED].
 * }
 */
class battle_ended extends \core\event\base {

    /**
     * Initialize the default data.
     */
    protected function init(): void {
        $this->data['objecttable'] = 'tool_rpg_battle';
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    /**
     * Returns the localized event name.
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string('eventbattleended', 'tool_rpg');
    }

    /**
     * Returns the localized event description.
     *
     * @return string
     */
    public function get_description(): string {
        return "The battle with id '$this->objectid' has ended.";
    }

    /**
     * Validates the oldstate and newstate properties.
     * Ensures we go from an unfinished battle to a finished one.
     */
    protected function validate_data(): void {
        parent::validate_data();
        foreach (['oldstate', 'newstate'] as $key) {
            if (!isset($this->other[$key])) {
                throw new coding_exception("The '$key' value must be set in other.");
            }
            if (!is_int($this->other[$key])) {
                throw new coding_exception("The '$key' value must be an integer.'");
            }
        }
        $validoldstates = [rpg_battle::STATE_NOTSTARTED, rpg_battle::STATE_ONGOING];
        if (!in_array($this->other['oldstate'], $validoldstates)) {
            throw new coding_exception("oldstate must be either STATE_NOTSTARTED or STATE_ONGOING.");
        }
        $validnewstates = [rpg_battle::STATE_VICTORY, rpg_battle::STATE_DEFEAT, rpg_battle::STATE_RETREATED];
        if (!in_array($this->other['newstate'], $validnewstates)) {
            throw new coding_exception("newstate must be either STATE_VICTORY, STATE_DEFEAT, or STATE_RETREATED.");
        }
    }

}
