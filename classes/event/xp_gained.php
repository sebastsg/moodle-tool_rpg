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
 * The XP gained event.
 *
 * @package    tool_rpg
 * @copyright  2024 Sebastian Gundersen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_rpg\event;

use coding_exception;
use context_system;

/**
 * This event is fired when a character gains some xp.
 *
 * @property-read array $other {
 *     - int $oldlevel: The character's old level.
 *     - int $newlevel: The character's new level.
 *     - bool $leveledup: Did the character just level up as a result of this XP gain?
 *     - int $xpgained: How much XP the character just gained.
 *     - int $oldxp: How much XP the character had before they gained XP.
 *     - int $newxp: How much XP the character has after gaining XP.
 * }
 */
class xp_gained extends \core\event\base {

    /**
     * Initialize the default data.
     */
    protected function init(): void {
        $this->data['objecttable'] = 'tool_rpg_character';
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    /**
     * Returns the localized event name.
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string('eventxpgained', 'tool_rpg');
    }

    /**
     * Returns the localized event description.
     *
     * @return string
     */
    public function get_description(): string {
        return "The user with id '$this->userid' gained experience.";
    }

    /**
     * Validates all the properties have correct datatypes.
     * Ensures we don't gain negative or zero XP, and that we don't have less XP after gaining XP.
     */
    protected function validate_data(): void {
        parent::validate_data();
        foreach (['oldlevel', 'newlevel', 'leveledup', 'xpgained', 'oldxp', 'newxp'] as $key) {
            if (!isset($this->other[$key])) {
                throw new coding_exception("The '$key' value must be set in other.");
            }
            if ($key === 'leveledup') {
                if (!is_bool($this->other['leveledup'])) {
                    throw new coding_exception("The 'leveledup' value must be a boolean.'");
                }
            } else if (!is_int($this->other[$key])) {
                throw new coding_exception("The '$key' value must be an integer.'");
            }
        }
        if ($this->other['xpgained'] <= 0) {
            throw new coding_exception("The 'xpgained' value cannot be less than or equal to 0.");
        }
        if ($this->other['oldlevel'] > $this->other['newlevel']) {
            throw new coding_exception("The 'oldlevel' value cannot be greater than 'newlevel'.'");
        }
        if ($this->other['oldxp'] > $this->other['newxp']) {
            throw new coding_exception("The 'oldxp' value cannot be greater than 'newxp'.'");
        }
    }

}
