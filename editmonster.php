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
 * Page for admins to create, edit, or delete monsters.
 *
 * @package    tool_rpg
 * @copyright  2024 Sebastian Gundersen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_rpg\form\edit_monster_form;
use tool_rpg\rpg_filearea;
use tool_rpg\rpg_monster;

require_once(__DIR__ . '/../../../config.php');

$monsterid = optional_param('monsterid', null, PARAM_INT);
$action = optional_param('action', null, PARAM_TEXT);

$context = context_system::instance();

require_login();
require_capability('tool/rpg:edit', $context);

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('editmonster', 'tool_rpg'));
$PAGE->set_heading(get_string('pluginname', 'tool_rpg'));
$PAGE->set_url(new moodle_url('/admin/tool/rpg/editmonster.php', ['monsterid' => $monsterid]));
$PAGE->set_secondary_navigation(false);

$monster = empty($monsterid) ? new rpg_monster() : rpg_monster::find($monsterid);

if ($action === 'delete') {
    $monster->delete();
    redirect(new moodle_url('/admin/tool/rpg'));
}

$form = new edit_monster_form(new moodle_url('/admin/tool/rpg/editmonster.php', ['monsterid' => $monsterid]));

if ($form->is_cancelled()) {
    redirect(new moodle_url('/admin/tool/rpg'));
}

$data = $form->get_data();
if ($data) {
    $monster->set_name($data->name);
    $monster->set_hp($data->hp);
    $monster->set_level($data->level);
    $monster->save();
    rpg_monster::replace_icon($monster->get_id(), $data->icon);
    redirect(new moodle_url('/admin/tool/rpg'));
}

$draftitemid = file_get_submitted_draft_itemid('icon');

file_prepare_draft_area($draftitemid, $context->id, 'tool_rpg', rpg_filearea::MONSTER, $monster->get_id(), [
    'subdirs' => 0,
    'maxbytes' => 1024 * 512,
    'maxfiles' => 1,
]);

$form->set_data([
    'name' => $monster->get_name(),
    'hp' => $monster->get_hp(),
    'level' => $monster->get_level(),
    'icon' => $draftitemid,
]);

$output = $PAGE->get_renderer('tool_rpg');
echo $output->header();
$form->display();
echo $output->footer();
