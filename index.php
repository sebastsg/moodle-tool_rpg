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
 * The main page where admin can get an overview, and users can view their adventure.
 *
 * @package    tool_rpg
 * @copyright  2024 Sebastian Gundersen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_rpg\output\adventure_page;
use tool_rpg\output\teacher_page;
use tool_rpg\rpg_character;

require_once(__DIR__ . '/../../../config.php');

require_login();

$title = get_config('tool_rpg', 'title');

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_url(new moodle_url('/admin/tool/rpg'));
$PAGE->set_secondary_navigation(false);

$output = $PAGE->get_renderer('tool_rpg');

echo $output->header();
if (has_capability('tool/rpg:viewteacher', context_system::instance())) {
    echo $output->render(new teacher_page());
    echo '<hr>';
}

echo $output->render(new adventure_page(rpg_character::get_user_character($USER->id)));
echo $output->footer();
