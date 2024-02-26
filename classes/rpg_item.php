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
 * Defines item class.
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
 * Represents an item.
 */
class rpg_item {

    /** @var int|null ID. */
    private ?int $id = null;

    /** @var string The item's name. */
    private string $name = '';

    /** @var string The rarity of the item. */
    private string $rarity = 'common';

    /** @var string What type of item this is. */
    private string $type = '';

    /** @var int Whether this is a stackable item or not. */
    private int $stackable = 0;

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
        $this->rarity = $record->rarity;
        $this->type = $record->type;
        $this->stackable = $record->stackable;
    }

    /**
     * Returns the item's id if persisted.
     *
     * @return int|null
     */
    public function get_id(): ?int {
        return $this->id;
    }

    /**
     * Returns the item's name.
     *
     * @return string
     */
    public function get_name(): string {
        return $this->name;
    }

    /**
     * Returns the rarity of the item.
     *
     * @return string
     */
    public function get_rarity(): string {
        return $this->rarity;
    }

    /**
     * Returns the type of item this is.
     *
     * @return string
     */
    public function get_type(): string {
        return $this->type;
    }

    /**
     * Returns whether this item is stackable or not.
     *
     * @return bool
     */
    public function is_stackable(): bool {
        return $this->stackable !== 0;
    }

    /**
     * Rename the item. This function does not persist the change.
     *
     * @param string $name
     * @return void
     */
    public function set_name(string $name): void {
        $this->name = $name;
    }

    /**
     * Set a new rarity for the item. This function does not persist the change.
     *
     * @param string $rarity
     * @return void
     */
    public function set_rarity(string $rarity): void {
        $this->rarity = $rarity;
    }

    /**
     * Set a new type for the item. This function does not persist the change.
     *
     * @param string $type
     * @return void
     */
    public function set_type(string $type): void {
        $this->type = $type;
    }

    /**
     * Change whether the item is stackable or not. This function does not persist the change.
     *
     * @param bool $stackable
     * @return void
     */
    public function set_stackable(bool $stackable): void {
        $this->stackable = $stackable ? 1 : 0;
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
        $record->rarity = $this->rarity;
        $record->type = $this->type;
        $record->stackable = $this->stackable;
        return $record;
    }

    /**
     * Deletes this item.
     *
     * @return void
     */
    public function delete(): void {
        global $DB;
        if (!$this->id) {
            throw new \coding_exception('This item does not yet have an id, and can\'t be deleted.');
        }
        self::delete_icon($this->id);
        $DB->delete_records('tool_rpg_item', ['id' => $this->id]);
        cache::make('tool_rpg', 'items')->delete($this->id);
    }

    /**
     * Saves the local record to the database.
     *
     * @return void
     */
    public function save(): void {
        global $DB;
        if ($this->id) {
            $DB->update_record('tool_rpg_item', $this->get_record());
        } else {
            $this->id = $DB->insert_record('tool_rpg_item', $this->get_record());
        }
        cache::make('tool_rpg', 'items')->set($this->id, $this->get_record());
    }

    /**
     * Find an item based on an id.
     *
     * @param int $itemid
     * @return rpg_item|null
     */
    public static function find(int $itemid): ?rpg_item {
        global $DB;
        $record = cache::make('tool_rpg', 'items')->get($itemid);
        if (!$record) {
            $record = $DB->get_record('tool_rpg_item', ['id' => $itemid]);
        }
        return empty($record) ? null : new rpg_item($record);
    }

    /**
     * Find a random item. If no items exist, null is returned.
     *
     * @return rpg_item|null
     */
    public static function find_random_item(): ?rpg_item {
        global $DB;
        // Is there a better way to simulate "order by random()"?
        // Most likely. Please tell me.
        $items = $DB->get_records('tool_rpg_item');
        shuffle($items);
        $record = array_shift($items);
        if ($record) {
            cache::make('tool_rpg', 'items')->set($record->id, $record);
        }
        return empty($record) ? null : new rpg_item($record);
    }

    /**
     * Find a list of items based on an array of item IDs.
     *
     * @param int[] $itemids
     * @return rpg_item[] indexed by id
     */
    public static function find_list(array $itemids): array {
        global $DB;
        $items = [];
        $cache = cache::make('tool_rpg', 'items');
        foreach ($DB->get_records_list('tool_rpg_item', 'id', $itemids) as $itemid => $record) {
            $items[$itemid] = new rpg_item($record);
            $cache->set($record->id, $record);
        }
        return $items;
    }

    /**
     * Find the icon file for a given item.
     *
     * @param int $itemid
     * @return stored_file|null
     */
    public static function find_icon_file(int $itemid): ?stored_file {
        $context = context_system::instance();
        foreach (get_file_storage()->get_area_files($context->id, 'tool_rpg', rpg_filearea::ITEM, $itemid) as $file) {
            if ($file->get_filesize() > 0) {
                return $file;
            }
        }
        return null;
    }

    /**
     * Returns the URL for an item's icon.
     *
     * @param int $itemid
     * @return moodle_url
     */
    public static function get_icon_url(int $itemid): moodle_url {
        global $PAGE;
        $file = self::find_icon_file($itemid);
        if ($file !== null) {
            $context = context_system::instance();
            return moodle_url::make_pluginfile_url($context->id, 'tool_rpg', rpg_filearea::ITEM, $itemid, '', '');
        } else {
            return $PAGE->theme->image_url('f/unknown', 'moodle');
        }
    }

    /**
     * Deletes the icon for a given item.
     *
     * @param int $itemid
     * @return void
     */
    public static function delete_icon(int $itemid): void {
        $context = context_system::instance();
        foreach (get_file_storage()->get_area_files($context->id, 'tool_rpg', rpg_filearea::ITEM, $itemid) as $file) {
            if ($file->get_filesize() > 0) {
                $file->delete();
            }
        }
    }

    /**
     * Replaces the icon for a given item.
     *
     * @param int $itemid
     * @param int $draftitemid
     * @return void
     */
    public static function replace_icon(int $itemid, int $draftitemid): void {
        $context = context_system::instance();
        self::delete_icon($itemid);
        file_save_draft_area_files($draftitemid, $context->id, 'tool_rpg', rpg_filearea::ITEM, $itemid, [
            'subdirs' => 0,
            'maxbytes' => 1024 * 512,
            'maxfiles' => 1,
        ]);
    }

}
