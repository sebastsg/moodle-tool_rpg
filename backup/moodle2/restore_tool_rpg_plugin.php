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
 * Restore from backup.
 *
 * @package    tool_rpg
 * @copyright  2024 Sebastian Gundersen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_rpg\rpg_filearea;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/backup/moodle2/restore_tool_plugin.class.php');

/**
 * Restore from backup
 */
class restore_tool_rpg_plugin extends restore_tool_plugin {

    /**
     * Define structure.
     *
     * @return restore_path_element[]
     */
    protected function define_course_plugin_structure(): array {
        $paths = [];
        $paths[] = new restore_path_element('item', '/items/item');
        $paths[] = new restore_path_element('monster', '/monsters/monster');
        if ($this->get_setting_value('userinfo')) {
            $paths[] = new restore_path_element('character', '/characters/character');
            $paths[] = new restore_path_element('item_instance', '/items/item/instances/instance');
        }
        return $paths;
    }

    /**
     * Restore item from backup.
     *
     * @param stdClass|array $data
     * @return void
     */
    public function process_item(stdClass|array $data): void {
        global $DB;
        $data = (object)$data;
        $oldid = $data->id;
        $data->id = $DB->insert_record('tool_rpg_item', $data);
        $this->set_mapping('tool_rpg_item', $oldid, $data->id);
    }

    /**
     * Restore monster from backup.
     *
     * @param stdClass|array $data
     * @return void
     */
    public function process_monster(stdClass|array $data): void {
        global $DB;
        $data = (object)$data;
        $oldid = $data->id;
        $data->id = $DB->insert_record('tool_rpg_monster', $data);
        $this->set_mapping('tool_rpg_monster', $oldid, $data->id);
    }

    /**
     * Restore character from backup.
     *
     * @param stdClass|array $data
     * @return void
     */
    public function process_character(stdClass|array $data): void {
        global $DB;
        $data = (object)$data;
        $oldid = $data->id;
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->id = $DB->insert_record('tool_rpg_character', $data);
        $this->set_mapping('tool_rpg_character', $oldid, $data->id);
    }

    /**
     * Restore item instance from backup.
     *
     * @param stdClass|array $data
     * @return void
     */
    public function process_item_instance(stdClass|array $data): void {
        global $DB;
        $data = (object)$data;
        $oldid = $data->id;
        $data->itemid = $this->get_new_parentid('item');
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->id = $DB->insert_record('tool_rpg_item_instance', $data);
        $this->set_mapping('tool_rpg_item_instance', $oldid, $data->id);
    }

    /**
     * Restore files.
     *
     * @return void
     */
    protected function after_execute(): void {
        $this->add_related_files('tool_rpg', rpg_filearea::ITEM, null);
        $this->add_related_files('tool_rpg', rpg_filearea::MONSTER, null);
    }

}
