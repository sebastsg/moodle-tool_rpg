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
 * Unit tests for the rpg_item class.
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
 * Unit tests for the rpg_item class.
 *
 * @package tool_rpg
 * @group tool_rpg
 * @covers \tool_rpg\rpg_item
 * @category test
 * @copyright 2024 Sebastian Gundersen
 */
class item_test extends advanced_testcase {

    /**
     * Test construction of a new item with default values, and check if the properties are as expected.
     */
    public function test_default_item_constructor(): void {
        $item = new rpg_item();
        $this->assertNull($item->get_id());
        $this->assertEquals('', $item->get_name());
        $this->assertEquals('common', $item->get_rarity());
        $this->assertEquals('', $item->get_type());
        $this->assertFalse($item->is_stackable());
    }

    /**
     * Test construction of a new item with our defined values, and check if the properties are as expected.
     * Save the item, and assert that it now has a valid id property.
     * We also test that the items cache has a record of the new item.
     */
    public function test_save_new_item(): void {
        $this->resetAfterTest();

        $record = new stdClass();
        $record->name = 'Potion';
        $record->rarity = 'rare';
        $record->type = 'potion';
        $record->stackable = 1;

        $item = new rpg_item($record);
        $this->assertNull($item->get_id());
        $this->assertEquals('Potion', $item->get_name());
        $this->assertEquals('rare', $item->get_rarity());
        $this->assertEquals('potion', $item->get_type());
        $this->assertTrue($item->is_stackable());
        $item->save();
        $this->assertNotNull($item->get_id());
        $this->assertTrue(cache::make('tool_rpg', 'items')->has($item->get_id()));
    }

    /**
     * First test that the cached item record exists and has the expected properties we set in the constructor.
     * Set a new name, and make the item stackable.
     * Save the item again, and confirm that the cached item now has the updated record.
     */
    public function test_update_item(): void {
        $this->resetAfterTest();

        $record = new stdClass();
        $record->name = 'Potion';
        $record->rarity = 'rare';
        $record->type = 'potion';
        $record->stackable = 1;

        $item = new rpg_item($record);
        $item->save();
        $this->assertNotNull($item->get_id());
        $itemid = $item->get_id();
        $cacheditem = cache::make('tool_rpg', 'items')->get($item->get_id());
        $this->assertEquals($cacheditem->name, $record->name);
        $item->set_name('Health Potion');
        $item->set_stackable(false);
        $item->save();
        $this->assertEquals($itemid, $item->get_id());
        $cacheditem = cache::make('tool_rpg', 'items')->get($item->get_id());
        $this->assertEquals('Health Potion', $item->get_name());
        $this->assertEquals('Health Potion', $cacheditem->name);
    }

}
