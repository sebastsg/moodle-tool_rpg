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
 * XP table.
 *
 * @package    tool_rpg
 * @copyright  2024 Sebastian Gundersen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_rpg\local;

/**
 * Calculate XP, levels, and stats based on XP or level.
 */
class xp_table {

    /** @var int[] */
    private static array $cachedlevels = [];

    /** The max level that is possible to achieve. */
    const MAXLEVEL = 100;

    /**
     * Returns a list of all XP requirements indexed by level.
     *
     * @return int[] required xp indexed by level
     */
    public static function levels(): array {
        if (empty(self::$cachedlevels)) {
            $xptargetbase = (int)get_config('tool_rpg', 'xp_target_base');
            $xptargetmultiplier = (float)get_config('tool_rpg', 'xp_table');
            self::$cachedlevels = [1 => 0];
            $xp = (float)$xptargetbase;
            for ($level = 2; $level <= self::MAXLEVEL; $level++) {
                self::$cachedlevels[$level] = (int)$xp;
                $xp *= $xptargetmultiplier;
            }
        }
        return self::$cachedlevels;
    }

    /**
     * Calculate the required XP for a given level.
     *
     * @param int $level
     * @return int|null
     */
    public static function xp_from_level(int $level): ?int {
        $levels = self::levels();
        return $levels[$level] ?? null;
    }

    /**
     * Calculate the level reached for a given XP.
     *
     * @param int $xp
     * @return int
     */
    public static function level_from_xp(int $xp): int {
        foreach (self::levels() as $level => $xprequired) {
            if ($xprequired >= $xp) {
                return $level;
            }
        }
        return self::MAXLEVEL;
    }

    /**
     * Calculate how much XP is needed to get to the next level.
     *
     * @param int $xp
     * @return int
     */
    public static function remaining_xp_until_next_level(int $xp): int {
        $level = self::level_from_xp($xp);
        $targetxp = self::xp_from_level($level + 1);
        if ($targetxp === null) {
            return 0;
        }
        return $targetxp - $xp;
    }

    /**
     * Calculate how many hitpoints a character or monster can have based on a level.
     *
     * @param int $level
     * @return int
     */
    public static function max_hp_from_level(int $level): int {
        $hp = 40.0 + (float)$level * 16.0;
        for ($i = 1; $i <= $level; $i++) {
            $hp *= 1.05;
        }
        return (int)$hp;
    }

}
