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
 * Defines character class.
 *
 * @package    tool_rpg
 * @copyright  2024 Sebastian Gundersen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_rpg;

use coding_exception;
use context_system;
use stdClass;
use tool_rpg\event\xp_gained;
use tool_rpg\local\xp_table;

/**
 * Represents a character.
 */
class rpg_character {

    /** @var int|null ID. */
    private ?int $id = null;

    /** @var int User ID. */
    private int $userid = 0;

    /** @var int How much XP the character has. */
    private int $xp = 0;

    /** @var int Timestamp when the character was created. */
    private int $timecreated = 0;

    /** @var int The character's current HP. */
    private int $hp = 0;

    /**
     * Initialize from a record, with or without an id.
     * If no record is passed, default values will be used.
     *
     * @param stdClass|null $record
     */
    public function __construct(?stdClass $record) {
        if (!$record) {
            return;
        }
        $this->id = $record->id ?? null;
        $this->userid = $record->userid;
        $this->hp = $record->hp;
        $this->xp = $record->xp;
        $this->timecreated = $record->timecreated;
    }

    /**
     * Returns the character's id if persisted.
     *
     * @return int|null
     */
    public function get_id(): ?int {
        return $this->id;
    }

    /**
     * Returns the user's id for this character.
     *
     * @return int
     */
    public function get_userid(): int {
        return $this->userid;
    }

    /**
     * Returns how much XP this character has gained.
     *
     * @return int
     */
    public function get_xp(): int {
        return $this->xp;
    }

    /**
     * Returns the current level for this character based on the current XP.
     *
     * @return int
     */
    public function get_level(): int {
        return xp_table::level_from_xp($this->xp);
    }

    /**
     * Returns the max HP for this character based on the current level.
     *
     * @return int
     */
    public function get_max_hp(): int {
        return xp_table::max_hp_from_level($this->get_level());
    }

    /**
     * Restores the character's HP to the maximum. This function does not persist the change.
     *
     * @return void
     */
    public function restore_max_hp(): void {
        $this->hp = $this->get_max_hp();
    }

    /**
     * Returns the current HP for the character.
     *
     * @return int
     */
    public function get_hp(): int {
        return $this->hp;
    }

    /**
     * Returns the timestamp for when this character was created.
     *
     * @return int
     */
    public function get_timecreated(): int {
        return $this->timecreated;
    }

    /**
     * Returns the current local record.
     *
     * @return stdClass
     */
    public function get_record(): stdClass {
        $record = new stdClass();
        if ($this->id) {
            $record->id = $this->id;
        }
        $record->userid = $this->userid;
        $record->hp = $this->hp;
        $record->xp = $this->xp;
        $record->timecreated = $this->timecreated;
        return $record;
    }

    /**
     * Saves the local record to the database.
     *
     * @return void
     */
    public function save(): void {
        global $DB;
        if ($this->id) {
            $DB->update_record('tool_rpg_character', $this->get_record());
        } else {
            $this->id = $DB->insert_record('tool_rpg_character', $this->get_record());
        }
    }

    /**
     * Add XP to the character, and trigger the xp_gained event.
     *
     * @param int $xp
     * @return void
     */
    public function grant_xp(int $xp): void {
        if ($xp <= 0) {
            return;
        }
        $snapshot = $this->get_record();
        $oldlevel = xp_table::level_from_xp($this->xp);
        $this->xp += $xp;
        $newlevel = xp_table::level_from_xp($this->xp);
        if ($newlevel > $oldlevel) {
            $this->hp = xp_table::max_hp_from_level($newlevel);
        }
        $this->save();
        $event = xp_gained::create([
            'context' => context_system::instance(),
            'objectid' => $this->id,
            'relateduserid' => $this->userid,
            'other' => [
                'oldlevel' => $oldlevel,
                'newlevel' => $newlevel,
                'leveledup' => $newlevel > $oldlevel,
                'xpgained' => $xp,
                'oldxp' => $snapshot->xp,
                'newxp' => $this->xp,
            ],
        ]);
        $event->add_record_snapshot('tool_rpg_character', $snapshot);
        $event->trigger();
    }

    /**
     * Take some damage. This function does not persist the change.
     *
     * @param int $damage
     * @return void
     */
    public function take_damage(int $damage): void {
        $this->hp -= $damage;
        if ($this->hp < 0) {
            $this->hp = 0;
        }
    }

    /**
     * Add an item to the character's inventory.
     *
     * @param int $itemid
     * @return void
     */
    public function add_item_to_inventory(int $itemid): void {
        global $DB;
        if (!$this->id) {
            throw new coding_exception('Unable to give item to character not yet in database');
        }
        $item = rpg_item::find($itemid);
        if ($item === null) {
            throw new coding_exception("Invalid itemid provided: $itemid");
        }
        if ($item->is_stackable()) {
            $iteminstance = $DB->get_record('tool_rpg_item_instance', ['itemid' => $itemid]);
            if ($iteminstance) {
                $iteminstance->stack++;
                $DB->update_record('tool_rpg_item_instance', $iteminstance);
                return;
            }
        }
        $DB->insert_record('tool_rpg_item_instance', [
            'characterid' => $this->id,
            'itemid' => $itemid,
            'timecreated' => time(),
        ]);
    }

    /**
     * Find a character based on an id.
     *
     * @param int $id
     * @return rpg_character|null
     */
    public static function find(int $id): ?rpg_character {
        global $DB;
        $record = $DB->get_record('tool_rpg_character', ['id' => $id]) ?: null;
        return empty($record) ? null : new rpg_character($record);
    }

    /**
     * Find a character based on a user's id.
     *
     * @param int $userid
     * @return rpg_character
     */
    public static function get_user_character(int $userid): rpg_character {
        global $DB, $USER;
        $record = $DB->get_record('tool_rpg_character', ['userid' => $userid]) ?: null;
        if (!$record) {
            $record = new stdClass();
            $record->userid = $USER->id;
            $record->timecreated = time();
            $characterid = $DB->insert_record('tool_rpg_character', $record);
            return self::find($characterid);
        }
        return new rpg_character($record);
    }

}
