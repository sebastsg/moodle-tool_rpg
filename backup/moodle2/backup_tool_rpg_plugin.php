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
 * Backup.
 *
 * @package    tool_rpg
 * @copyright  2024 Sebastian Gundersen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_rpg\rpg_filearea;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/backup/moodle2/backup_tool_plugin.class.php');

/**
 * Backup.
 */
class backup_tool_rpg_plugin extends backup_tool_plugin {

    /**
     * Define structure.
     *
     * @return backup_plugin_element
     */
    protected function define_course_plugin_structure(): backup_plugin_element {
        $items = new backup_nested_element('items');
        $item = new backup_nested_element('item', ['id'], [
            'name',
            'rarity',
            'type',
            'stackable',
        ]);
        $characters = new backup_nested_element('characters');
        $character = new backup_nested_element('character', ['id'], [
            'userid',
            'xp',
            'hp',
            'timecreated',
        ]);
        $iteminstances = new backup_nested_element('item/instances');
        $iteminstance = new backup_nested_element('instance', ['id'], [
            'itemid',
            'characterid',
            'stack',
            'timecreated',
        ]);
        $monsters = new backup_nested_element('monsters');
        $monster = new backup_nested_element('monster', ['id', [
            'name',
            'hp',
            'level',
        ]]);

        $iteminstances->add_child($iteminstance);
        $item->add_child($iteminstances);
        $items->add_child($item);
        $characters->add_child($character);
        $monsters->add_child($monster);

        $item->set_source_table('tool_rpg_item', []);
        $character->set_source_table('tool_rpg_character', []);
        $iteminstance->set_source_table('tool_rpg_iteminstance', ['itemid' => backup::VAR_PARENTID]);

        $character->annotate_ids('user', 'userid');

        $plugin = $this->get_plugin_element();
        $plugin->add_child($items);
        $plugin->add_child($characters);
        $plugin->add_child($monsters);

        $plugin->annotate_files('tool_rpg', rpg_filearea::ITEM, null);
        $plugin->annotate_files('tool_rpg', rpg_filearea::MONSTER, null);

        return $plugin;
    }

}
