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
 * Renderer for tool_rpg.
 *
 * @package    tool_rpg
 * @copyright  2024 Sebastian Gundersen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_rpg\output;

use plugin_renderer_base;

/**
 * Renderer for tool_rpg.
 */
class renderer extends plugin_renderer_base {

    /**
     * Render the teacher page.
     *
     * @param teacher_page $page
     * @return string
     */
    public function render_teacher_page(teacher_page $page): string {
        $data = $page->export_for_template($this);
        return parent::render_from_template('tool_rpg/teacher_page', $data);
    }

    /**
     * Render the adventure page.
     *
     * @param adventure_page $page
     * @return string
     */
    public function render_adventure_page(adventure_page $page): string {
        $data = $page->export_for_template($this);
        return parent::render_from_template('tool_rpg/adventure_page', $data);
    }

}
