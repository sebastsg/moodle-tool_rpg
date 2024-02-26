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
 * Generator for tool_rpg.
 *
 * @package    tool_rpg
 * @copyright  2024 Sebastian Gundersen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Generator for tool_rpg.
 */
class tool_rpg_generator extends component_generator_base {

    /**
     * Create a new character.
     *
     * @param array $data
     * @return stdClass
     */
    public function create_character(array $data): stdClass {
        global $DB;
        if (empty($data['timecreated'])) {
            $data['timecreated'] = time();
        }
        $id = $DB->insert_record('tool_rpg_character', $data);
    }

    /**
     * Create a new item.
     *
     * @param array $data
     * @return void
     */
    public function create_item(array $data): void {
        global $DB;
        if (empty($data['timecreated'])) {
            $data['timecreated'] = time();
        }
        $DB->insert_record('tool_rpg_item', $data);
    }

}
