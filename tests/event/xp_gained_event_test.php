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
 * Test the xp_gained event.
 *
 * @package    tool_rpg
 * @copyright  2024 Sebastian Gundersen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_rpg\event;

use advanced_testcase;
use tool_rpg\rpg_character;
use tool_rpg_generator;

/**
 * Test the xp_gained event.
 *
 * @package tool_rpg
 * @group tool_rpg
 * @covers \tool_rpg\event\xp_gained
 * @category test
 * @copyright 2024 Sebastian Gundersen
 */
class xp_gained_event_test extends advanced_testcase {

    /**
     * Test that the xp_gained event is triggered correctly when a character gains some XP.
     */
    public function test_event_triggered(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        /** @var tool_rpg_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('tool_rpg');
        $characterrecord = $generator->create_character(['userid' => $user->id]);
        $character = new rpg_character($characterrecord);
        $sink = $this->redirectEvents();
        $character->grant_xp(1000);
        $events = $sink->get_events();
        $sink->close();
        $this->assertCount(1, $events);
        $event = $events[0];
        $this->assertInstanceOf(xp_gained::class, $event);
        $this->assertEquals('tool_rpg_character', $event->objecttable);
        $this->assertEquals($character->get_userid(), $event->userid);
    }

}
