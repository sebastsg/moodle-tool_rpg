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
 * Defines monster class.
 *
 * @package    tool_rpg
 * @copyright  2024 Sebastian Gundersen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_rpg;

use cache;
use context_system;
use moodle_url;
use stdClass;
use stored_file;

/**
 * Represents a monster.
 */
class rpg_monster {

    /** @var int|null ID. */
    private ?int $id = null;

    /** @var string The monster's name. */
    private string $name = '';

    /** @var int The maximum HP for this monster. */
    private int $hp = 100;

    /** @var int The monster's level. */
    private int $level = 3;

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
        $this->name = $record->name;
        $this->hp = $record->hp;
        $this->level = $record->level;
    }

    /**
     * Returns the monster's id if persisted.
     *
     * @return int|null
     */
    public function get_id(): ?int {
        return $this->id;
    }

    /**
     * Returns the monster's name.
     *
     * @return string
     */
    public function get_name(): string {
        return $this->name;
    }

    /**
     * Returns the monster's maximum hitpoints.
     * To get the current HP, you must get it from a battle record.
     *
     * @return int
     */
    public function get_hp(): int {
        return $this->hp;
    }

    /**
     * Returns the monster's level.
     *
     * @return int
     */
    public function get_level(): int {
        return $this->level;
    }

    /**
     * Rename this monster. This function does not persist the change.
     *
     * @param string $name
     * @return void
     */
    public function set_name(string $name): void {
        $this->name = $name;
    }

    /**
     * Set the new max HP for this monster. This function does not persist the change.
     *
     * @param int $hp
     * @return void
     */
    public function set_hp(int $hp): void {
        $this->hp = $hp;
    }

    /**
     * Set the level for this monster. This function does not persist the change.
     *
     * @param int $level
     * @return void
     */
    public function set_level(int $level): void {
        $this->level = $level;
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
        $record->name = $this->name;
        $record->hp = $this->hp;
        $record->level = $this->level;
        return $record;
    }

    /**
     * Deletes this monster.
     *
     * @return void
     */
    public function delete(): void {
        global $DB;
        if (!$this->id) {
            throw new \coding_exception('This monster does not yet have an id, and can\'t be deleted.');
        }
        self::delete_icon($this->id);
        $DB->delete_records('tool_rpg_monster', ['id' => $this->id]);
        cache::make('tool_rpg', 'monsters')->delete($this->id);
    }

    /**
     * Saves the local record to the database.
     *
     * @return void
     */
    public function save(): void {
        global $DB;
        if ($this->id) {
            $DB->update_record('tool_rpg_monster', $this->get_record());
        } else {
            $this->id = $DB->insert_record('tool_rpg_monster', $this->get_record());
        }
        cache::make('tool_rpg', 'monsters')->set($this->id, $this->get_record());
    }

    /**
     * Find a monster based on an id.
     *
     * @param int $monsterid
     * @return rpg_monster|null
     */
    public static function find(int $monsterid): ?rpg_monster {
        global $DB;
        $record = cache::make('tool_rpg', 'monsters')->get($monsterid);
        if (!$record) {
            $record = $DB->get_record('tool_rpg_monster', ['id' => $monsterid]);
        }
        return empty($record) ? null : new rpg_monster($record);
    }

    /**
     * Find a random monster. If no monsters exist, null is returned.
     *
     * @return rpg_monster|null
     */
    public static function find_random_monster(): ?rpg_monster {
        global $DB;
        // Is there a better way to simulate "order by random()"?
        // Most likely. Please tell me.
        $monsters = $DB->get_records('tool_rpg_monster');
        shuffle($monsters);
        $record = array_shift($monsters);
        return empty($record) ? null : new rpg_monster($record);
    }

    /**
     * Find the icon file for a given monster.
     *
     * @param int $monsterid
     * @return stored_file|null
     */
    public static function find_icon_file(int $monsterid): ?stored_file {
        $context = context_system::instance();
        $fs = get_file_storage();
        foreach ($fs->get_area_files($context->id, 'tool_rpg', rpg_filearea::MONSTER, $monsterid) as $file) {
            if ($file->get_filesize() > 0) {
                return $file;
            }
        }
        return null;
    }

    /**
     * Returns the URL for a monster's icon.
     *
     * @param int $monsterid
     * @return moodle_url
     */
    public static function get_icon_url(int $monsterid): moodle_url {
        global $PAGE;
        $file = self::find_icon_file($monsterid);
        if ($file !== null) {
            $context = context_system::instance();
            return moodle_url::make_pluginfile_url($context->id, 'tool_rpg', rpg_filearea::MONSTER, $monsterid, '', '');
        } else {
            return $PAGE->theme->image_url('f/unknown', 'moodle');
        }
    }

    /**
     * Deletes the icon for a given monster.
     *
     * @param int $monsterid
     * @return void
     */
    public static function delete_icon(int $monsterid): void {
        $context = context_system::instance();
        $fs = get_file_storage();
        foreach ($fs->get_area_files($context->id, 'tool_rpg', rpg_filearea::MONSTER, $monsterid) as $file) {
            if ($file->get_filesize() > 0) {
                $file->delete();
            }
        }
    }

    /**
     * Replaces the icon for a given monster
     *
     * @param int $monsterid
     * @param int $draftitemid
     * @return void
     */
    public static function replace_icon(int $monsterid, int $draftitemid): void {
        $context = context_system::instance();
        self::delete_icon($monsterid);
        file_save_draft_area_files($draftitemid, $context->id, 'tool_rpg', rpg_filearea::MONSTER, $monsterid, [
            'subdirs' => 0,
            'maxbytes' => 1024 * 512,
            'maxfiles' => 1,
        ]);
    }

}
