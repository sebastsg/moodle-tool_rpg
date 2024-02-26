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
 * Defines battle class.
 *
 * @package    tool_rpg
 * @copyright  2024 Sebastian Gundersen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_rpg;

use coding_exception;
use context_system;
use stdClass;
use tool_rpg\event\battle_ended;

/**
 * Represents a battle.
 */
class rpg_battle {

    /** The battle has been provoked, but the character has not yet accepted or declined. */
    const STATE_NOTSTARTED = 0;

    /** The battle is still ongoing. */
    const STATE_ONGOING = 1;

    /** The character was victorious. */
    const STATE_VICTORY = 2;

    /** The character was defeated. */
    const STATE_DEFEAT = 3;

    /** The character has retreated. */
    const STATE_RETREATED = 4;

    /** @var int|null ID. */
    private ?int $id = null;

    /** @var int Character ID. */
    private int $characterid = 0;

    /** @var int Monster ID. */
    private int $monsterid = 0;

    /** @var int Monster's current HP. */
    private int $monsterhp = 0;

    /** @var int Timestamp when the battle was started */
    private int $timecreated = 0;

    /** @var int The current state of the battle. */
    private int $state = -1;

    /**
     * Initialize from a record, with or without an id.
     * If no record is passed, default values will be used.
     *
     * @param stdClass|null $record
     */
    public function __construct(?stdClass $record = null) {
        if (!$record) {
            return;
        }
        $this->id = $record->id ?? null;
        $this->characterid = $record->characterid;
        $this->monsterid = $record->monsterid;
        $this->monsterhp = $record->monsterhp;
        $this->timecreated = $record->timecreated;
        $this->state = $record->state;
    }

    /**
     * Set up the battle with a random monster. This will save the battle to the database.
     * Requires that this battle has not yet been persisted.
     *
     * @param int $characterid
     * @return void
     */
    public function setup(int $characterid): void {
        if ($this->id) {
            throw new coding_exception('Battle is already setup');
        }
        if ($this->characterid !== 0 || $this->monsterid !== 0 || $this->state !== -1) {
            throw new coding_exception('Battle has already been setup');
        }
        $monster = rpg_monster::find_random_monster();
        if ($monster) {
            $this->characterid = $characterid;
            $this->monsterid = $monster->get_id();
            $this->monsterhp = $monster->get_hp();
            $this->timecreated = time();
            $this->state = self::STATE_NOTSTARTED;
            $this->save();
        }
    }

    /**
     * Checks whether we can retreat or not based on the current state.
     *
     * @return bool
     */
    public function can_retreat(): bool {
        return $this->state === self::STATE_NOTSTARTED || $this->state === self::STATE_ONGOING;
    }

    /**
     * Changes the state. Make sure to call save() to persist changes.
     *
     * @param int $state
     * @return void
     */
    public function change_state(int $state): void {
        if ($state < self::STATE_NOTSTARTED || $state > self::STATE_RETREATED) {
            throw new coding_exception("Invalid battle state $state");
        }
        $this->state = $state;
    }

    /**
     * Returns the battle's id if persisted.
     *
     * @return int|null
     */
    public function get_id(): ?int {
        return $this->id;
    }

    /**
     * Returns the battling character's id.
     *
     * @return int
     */
    public function get_characterid(): int {
        return $this->characterid;
    }

    /**
     * Returns the battling monster's id.
     *
     * @return int
     */
    public function get_monsterid(): int {
        return $this->monsterid;
    }

    /**
     * Returns how much HP the monster has left.
     *
     * @return int
     */
    public function get_monsterhp(): int {
        return $this->monsterhp;
    }

    /**
     * Returns the current state of the battle.
     *
     * @return int
     */
    public function get_state(): int {
        return $this->state;
    }

    /**
     * Returns timestamp when this battle was started.
     *
     * @return int
     */
    public function get_timecreated(): int {
        return $this->timecreated;
    }

    /**
     * Returns whether the battle has ended or not based on the current state.
     *
     * @return bool
     */
    public function has_ended(): bool {
        return $this->state === self::STATE_DEFEAT
            || $this->state === self::STATE_VICTORY
            || $this->state === self::STATE_RETREATED;
    }

    /**
     * Returns whether the battle is still ongoing or not based on the current state.
     *
     * @return bool
     */
    public function is_ongoing(): bool {
        return $this->state === self::STATE_ONGOING;
    }

    /**
     * Damage the monster. Changes are only persisted if the monster dies.
     * If the monster dies, the character gains xp, and a battle_ended event is triggered.
     * Otherwise, the caller will call damage_character() as well, which will make sure changes are saved.
     *
     * @param int $damage
     * @return void
     */
    public function damage_monster(int $damage): void {
        $this->monsterhp -= $damage;
        if ($this->monsterhp <= 0) {
            $this->monsterhp = 0;
            $oldstate = $this->state;
            $this->change_state(self::STATE_VICTORY);
            $this->save();
            $character = rpg_character::find($this->characterid);
            $character->grant_xp($damage);
            $event = battle_ended::create([
                'context' => context_system::instance(),
                'objectid' => $this->id,
                'userid' => $character->get_userid(),
                'relateduserid' => $character->get_userid(),
                'other' => [
                    'oldstate' => $oldstate,
                    'newstate' => $this->state,
                ],
            ]);
            $event->add_record_snapshot('tool_rpg_battle', $this->get_record());
            $event->trigger();
        }
    }

    /**
     * Damage the character. If the character dies, restore their HP and trigger a battle_ended event.
     *
     * @param int $damage
     * @return void
     */
    public function damage_character(int $damage): void {
        if ($this->characterid === 0) {
            throw new coding_exception('This battle has not been setup');
        }
        $character = rpg_character::find($this->characterid);
        if (!$character) {
            return;
        }
        $character->take_damage($damage);
        if ($character->get_hp() === 0) {
            $character->restore_max_hp();
            $oldstate = $this->state;
            $this->change_state(self::STATE_DEFEAT);
            $this->save();
            $event = battle_ended::create([
                'context' => context_system::instance(),
                'objectid' => $this->id,
                'userid' => $character->get_userid(),
                'relateduserid' => $character->get_userid(),
                'other' => [
                    'oldstate' => $oldstate,
                    'newstate' => $this->state,
                ],
            ]);
            $event->add_record_snapshot('tool_rpg_battle', $this->get_record());
            $event->trigger();
        }
        $character->save();
        $this->save();
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
        $record->characterid = $this->characterid;
        $record->monsterid = $this->monsterid;
        $record->monsterhp = $this->monsterhp;
        $record->timecreated = $this->timecreated;
        $record->state = $this->state;
        return $record;
    }

    /**
     * Find a battle based on an id.
     *
     * @param int $battleid
     * @return rpg_battle|null
     */
    public static function find(int $battleid): ?rpg_battle {
        global $DB;
        $record = $DB->get_record('tool_rpg_battle', ['id' => $battleid]);
        return empty($record) ? null : new rpg_battle($record);
    }

    /**
     * Find a battle based on a character's id and the battle's state.
     *
     * @param int $characterid
     * @param int $state
     * @return rpg_battle|null
     */
    public static function find_for_character(int $characterid, int $state): ?rpg_battle {
        global $DB;
        $record = $DB->get_record('tool_rpg_battle', ['characterid' => $characterid, 'state' => $state]);
        return empty($record) ? null : new rpg_battle($record);
    }

    /**
     * Saves the local record to the database.
     *
     * @return void
     */
    public function save(): void {
        global $DB;
        if ($this->id) {
            $DB->update_record('tool_rpg_battle', $this->get_record());
        } else {
            $this->id = $DB->insert_record('tool_rpg_battle', $this->get_record());
        }
    }

}
