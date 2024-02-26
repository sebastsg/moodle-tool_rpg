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
 * Admin settings for configuring the RPG.
 *
 * @package    tool_rpg
 * @copyright  2024 Sebastian Gundersen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $page = new admin_settingpage('tool_rpg', 'RPG', 'tool/rpg:config');
    if ($ADMIN->fulltree) {
        $page->add(new admin_setting_configtext(
            'tool_rpg/title',
            'Title',
            'Give the RPG a title that apppears in headers and the navigation link.',
            'RPG',
            PARAM_TEXT
        ));
        $page->add(new admin_setting_configtext(
            'tool_rpg/max_level',
            'Max level',
            'The max level any user can achieve.',
            '100',
            PARAM_INT
        ));
        $page->add(new admin_setting_configtext(
            'tool_rpg/xp_target_base',
            'Base XP target',
            'The XP required for the first level up. (Level 2)',
            '120',
            PARAM_INT
        ));
        $page->add(new admin_setting_configtext(
            'tool_rpg/xp_table',
            'XP table',
            'Multiplier for next level. Ideally this would be a custom table or formula.',
            '1.1',
            PARAM_FLOAT
        ));
    }
    $ADMIN->add('tools', $page);
}
