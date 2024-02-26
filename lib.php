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
 * Library functions. Only used for pluginfile callback at this point.
 *
 * @package    tool_rpg
 * @copyright  2024 Sebastian Gundersen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_rpg\rpg_filearea;

/**
 * Checks if the filearea and itemid are correct, then serves stored file.
 *
 * @param stdClass|null $course
 * @param stdClass|null $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function tool_rpg_pluginfile(?stdClass $course, ?stdClass $cm, context $context,
                              string $filearea, array $args, bool $forcedownload, array $options): bool {
    if ($context->contextlevel !== CONTEXT_SYSTEM) {
        return false;
    }
    if (!isloggedin()) {
        return false;
    }
    if ($filearea !== rpg_filearea::ITEM && $filearea !== rpg_filearea::MONSTER) {
        return false;
    }
    $itemid = array_shift($args);
    foreach (get_file_storage()->get_area_files($context->id, 'tool_rpg', $filearea, $itemid) as $file) {
        if ($file->get_filesize() > 0) {
            send_stored_file($file, 60 * 60 * 24, 0, $forcedownload, $options);
            return true; // Just for clarity. Exit has been called.
        }
    }
    return false;
}
