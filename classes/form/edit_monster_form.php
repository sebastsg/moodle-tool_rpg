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
 * The form to edit a monster.
 *
 * @package    tool_rpg
 * @copyright  2024 Sebastian Gundersen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_rpg\form;

use html_writer;
use moodleform;
use tool_rpg\rpg_monster;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

/**
 * The form for administrators to edit monsters.
 */
class edit_monster_form extends moodleform {

    /**
     * Creates the form definition.
     */
    protected function definition(): void {
        $form = $this->_form;

        $form->addElement('text', 'name', 'Name');
        $form->setType('name', PARAM_TEXT);

        $form->addElement('text', 'hp', 'Hitpoints');
        $form->setType('hp', PARAM_INT);

        $form->addElement('text', 'level', 'Level');
        $form->setType('level', PARAM_INT);

        $monsterid = optional_param('monsterid', null, PARAM_INT);
        if ($monsterid) {
            $iconfile = rpg_monster::find_icon_file($monsterid);
            if ($iconfile !== null) {
                $icon = html_writer::img(rpg_monster::get_icon_url($monsterid), 'Current icon', ['width' => '64px']);
            } else {
                $icon = 'This monster does not have an icon yet.';
            }
            $form->addElement('static', 'currenticon', 'Current icon', $icon);
        }

        $form->addElement('filepicker', 'icon', 'Icon', null, [
            'maxbytes' => 1024 * 512,
            'accepted_types' => 'web_image',
        ]);
        $form->setType('icon', PARAM_FILE);

        $this->add_action_buttons();
    }

    /**
     * Validate the fields.
     *
     * @param array $data
     * @param array $files
     * @return string[]
     */
    public function validation($data, $files): array {
        $errors = [];
        if (empty($data['name'])) {
            $errors['name'] = get_string('invalidname', 'tool_rpg');
        }
        if (!isset($data['level']) || $data['level'] < 1) {
            $errors['level'] = get_string('invalidlevel', 'tool_rpg');
        }
        if (!isset($data['hp']) || $data['hp'] < 1) {
            $errors['hp'] = get_string('invalidhp', 'tool_rpg');
        }
        return $errors;
    }

}
