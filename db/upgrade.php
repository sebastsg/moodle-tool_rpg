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
 * The tool_rpg upgrade script.
 *
 * @package    tool_rpg
 * @copyright  2024 Sebastian Gundersen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * The tool_rpg upgrade script.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_tool_rpg_upgrade(int $oldversion): bool {
    global $DB;
    $dbman = $DB->get_manager();
    if ($oldversion < 2024022305) {
        // Define field ongoing to be added to tool_rpg_battle.
        $table = new xmldb_table('tool_rpg_battle');
        $field = new xmldb_field('state', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'monsterhp');

        // Conditionally launch add field stackable.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2024022305, 'tool', 'rpg');
    }
    if ($oldversion < 2024022310) {
        // Define field ongoing to be added to tool_rpg_battle.
        $table = new xmldb_table('tool_rpg_character');
        $field = new xmldb_field('hp', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'xp');

        // Conditionally launch add field stackable.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2024022310, 'tool', 'rpg');
    }
    return true;
}
