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
 * Defines observer class.
 *
 * @package    tool_rpg
 * @copyright  2024 Sebastian Gundersen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_rpg;

use mod_quiz\event\attempt_submitted;
use mod_quiz\event\attempt_updated;
use question_engine;
use tool_rpg\event\battle_ended;

/**
 * Observer for events we listen to.
 */
class rpg_observer {

    /**
     * The user has updated the quiz attempt in some way, which probably means they answered a question
     * or completed a step. We don't know the grade here, but to reward the user they can receive some XP.
     *
     * @param attempt_updated $event
     * @return void
     */
    public static function quiz_attempt_updated(attempt_updated $event): void {
        $slots = optional_param('slots', '', PARAM_SEQUENCE);
        if (!empty($slots)) {
            $character = rpg_character::get_user_character($event->userid);
            $character?->grant_xp(count(explode(',', $slots)) * 4);
        }
    }

    /**
     * The user has completed their quiz attempt. The more questions they got right, the more XP they will receive.
     * If every question was answered correctly, they will also receive a random item.
     *
     * @param attempt_submitted $event
     * @return void
     */
    public static function quiz_attempt_submitted(attempt_submitted $event): void {
        $character = rpg_character::get_user_character($event->userid);
        $quizattempt = $event->get_record_snapshot('quiz_attempts', $event->objectid);
        $quba = question_engine::load_questions_usage_by_activity($quizattempt->uniqueid);
        $xpgained = 0;
        $everythingcorrect = true;
        foreach ($quba->get_slots() as $slot) {
            $questionattempt = $quba->get_question_attempt($slot);
            if ($questionattempt->get_state()->is_correct()) {
                $xpgained += 19;
            } else {
                $everythingcorrect = false;
            }
        }
        $character->grant_xp($xpgained);
        if ($everythingcorrect) {
            $randomitem = rpg_item::find_random_item();
            if ($randomitem) {
                $character->add_item_to_inventory($randomitem->get_id());
            }
        }
    }

    /**
     * A battle has ended. If the character was victorious, they receive a random item.
     * If the character was defeated, they lose a random item.
     *
     * @param battle_ended $event
     * @return void
     */
    public static function rpg_battle_ended(battle_ended $event): void {
        global $DB;
        $battle = new rpg_battle($event->get_record_snapshot('tool_rpg_battle', $event->objectid));
        $character = rpg_character::find($battle->get_characterid());
        if ($battle->get_state() === rpg_battle::STATE_VICTORY) {
            $randomitem = rpg_item::find_random_item();
            if ($randomitem) {
                $character->add_item_to_inventory($randomitem->get_id());
            }
        } else if ($battle->get_state() === rpg_battle::STATE_DEFEAT) {
            $items = $DB->get_records('tool_rpg_item_instance', ['characterid' => $character->get_id()]);
            shuffle($items);
            $itemlost = array_shift($items);
            if ($itemlost) {
                if ($itemlost->stack > 1) {
                    $itemlost->stack = min($itemlost->stack - 1, (int)((float)$itemlost->stack * 0.8));
                    $DB->update_record('tool_rpg_item_instance', $itemlost);
                } else {
                    $DB->delete_records('tool_rpg_item_instance', ['id' => $itemlost->id]);
                }
            }
        }
    }

}
