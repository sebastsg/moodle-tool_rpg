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
 *
 * The form to edit an item.
 *
 * @package    tool_rpg
 * @copyright  2024 Sebastian Gundersen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_rpg\form;

use html_writer;
use moodleform;
use stdClass;
use tool_rpg\rpg_item;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

/**
 * The form for administrators to edit items.
 */
class edit_item_form extends moodleform {

    /**
     * Creates the form definition.
     */
    protected function definition(): void {
        $form = $this->_form;

        $form->addElement('text', 'name', 'Name');
        $form->setType('name', PARAM_TEXT);

        $form->addElement('select', 'rarity', 'Rarity', [
            'verycommon' => get_string('verycommon', 'tool_rpg'),
            'common' => get_string('common', 'tool_rpg'),
            'uncommon' => get_string('uncommon', 'tool_rpg'),
            'rare' => get_string('rare', 'tool_rpg'),
            'ultrarare' => get_string('ultrarare', 'tool_rpg'),
            'legendary' => get_string('legendary', 'tool_rpg'),
        ]);
        $form->setType('rarity', PARAM_TEXT);
        $form->setDefault('rarity', 'common');

        $form->addElement('select', 'type', 'Type', [
            'food' => get_string('food', 'tool_rpg'),
            'potion' => get_string('potion', 'tool_rpg'),
            'weapon' => get_string('weapon', 'tool_rpg'),
            'other' => get_string('other', 'tool_rpg'),
            'tool' => get_string('tool', 'tool_rpg'),
        ]);
        $form->setType('type', PARAM_TEXT);

        $form->addElement('checkbox', 'stackable', 'Stackable');
        $form->setType('stackable', PARAM_BOOL);

        $itemid = optional_param('itemid', null, PARAM_INT);
        if ($itemid) {
            $iconfile = rpg_item::find_icon_file($itemid);
            if ($iconfile !== null) {
                $icon = html_writer::img(rpg_item::get_icon_url($itemid), 'Current icon', ['width' => '64px']);
            } else {
                $icon = 'This item does not have an icon yet.';
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
     * We don't do any validation yet. This is a problem!
     *
     * @todo Add validation
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files): array {
        return parent::validation($data, $files);
    }

}
