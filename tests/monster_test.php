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
 * Unit tests for the rpg_monster class.
 *
 * @package    tool_rpg
 * @copyright  2024 Sebastian Gundersen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_rpg;

use advanced_testcase;
use cache;
use stdClass;

/**
 * Unit tests for the rpg_monster class.
 *
 * @package tool_rpg
 * @group tool_rpg
 * @covers \tool_rpg\rpg_monster
 * @category test
 * @copyright 2024 Sebastian Gundersen
 */
class monster_test extends advanced_testcase {

    /**
     * Test construction of a new monster with default values, and check if the properties are as expected.
     */
    public function test_default_monster_constructor(): void {
        $monster = new rpg_monster();
        $this->assertNull($monster->get_id());
        $this->assertEquals('', $monster->get_name());
        $this->assertEquals(100, $monster->get_hp());
        $this->assertEquals(3, $monster->get_level());
    }

    /**
     * Test construction of a new monster with our defined values, and check if the properties are as expected.
     * Save the monster, and assert that it now has a valid id property.
     * We also test that the monsters cache has a record of the new monster.
     */
    public function test_save_new_monster(): void {
        $this->resetAfterTest();

        $record = new stdClass();
        $record->name = 'Imp';
        $record->hp = 200;
        $record->level = 5;

        $monster = new rpg_monster($record);
        $this->assertNull($monster->get_id());
        $this->assertEquals('Imp', $monster->get_name());
        $this->assertEquals(200, $monster->get_hp());
        $this->assertEquals(5, $monster->get_level());
        $monster->save();
        $this->assertNotNull($monster->get_id());
        $this->assertTrue(cache::make('tool_rpg', 'monsters')->has($monster->get_id()));
    }

    /**
     * First test that the cached monster record exists and has the expected properties we set in the constructor.
     * Set a new name, save the item again, and confirm that the cached monster now has the updated record.
     */
    public function test_update_monster(): void {
        $this->resetAfterTest();

        $record = new stdClass();
        $record->name = 'Imp';
        $record->hp = 100;
        $record->level = 5;

        $monster = new rpg_monster($record);
        $monster->save();
        $this->assertNotNull($monster->get_id());
        $monsterid = $monster->get_id();
        $cachedmonster = cache::make('tool_rpg', 'monsters')->get($monster->get_id());
        $this->assertEquals($cachedmonster->name, $record->name);
        $monster->set_name('Goblin');
        $monster->save();
        $this->assertEquals($monsterid, $monster->get_id());
        $cachedmonster = cache::make('tool_rpg', 'monsters')->get($monster->get_id());
        $this->assertEquals('Goblin', $monster->get_name());
        $this->assertEquals('Goblin', $cachedmonster->name);
    }

}
