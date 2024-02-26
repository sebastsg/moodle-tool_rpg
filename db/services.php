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
 * Declare external functions provided by tool_rpg.
 *
 * @package    tool_rpg
 * @copyright  2024 Sebastian Gundersen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_rpg\external\attack_monster;
use tool_rpg\external\decline_battle;
use tool_rpg\external\start_battle;

defined('MOODLE_INTERNAL') || die();

$functions = [
    'tool_rpg_start_battle' => [
        'classname' => start_battle::class,
        'description' => 'Start a battle that has been provoked',
        'type' => 'write',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
    'tool_rpg_decline_battle' => [
        'classname' => decline_battle::class,
        'description' => 'Decline a battle that has been provoked',
        'type' => 'write',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
    'tool_rpg_attack_monster' => [
        'classname' => attack_monster::class,
        'description' => 'Attack the monster in an ongoing battle',
        'type' => 'write',
        'ajax' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
];
