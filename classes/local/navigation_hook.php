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
 * Navigation hook for tool_rpg.
 *
 * @package    tool_rpg
 * @copyright  2024 Sebastian Gundersen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_rpg\local;

use core\hook\navigation\primary_extend;
use moodle_url;
use navigation_node;

/**
 * Hook to add the RPG link to the primary navigation.
 */
class navigation_hook {

    /**
     * Add the link to index of tool_rpg to the primary navigation.
     *
     * @param primary_extend $hook
     */
    public static function callback(primary_extend $hook): void {
        if (!isloggedin()) {
            return;
        }
        $primaryview = $hook->get_primaryview();
        $siteadmin = $primaryview->find('siteadminnode', navigation_node::TYPE_SITE_ADMIN);
        $beforekey = $siteadmin ? 'siteadminnode' : null;
        $url = new moodle_url('/admin/tool/rpg');
        $title = get_config('tool_rpg', 'title');
        $node = navigation_node::create($title, $url, navigation_node::TYPE_ROOTNODE, $title, 'rpg');
        $primaryview->add_node($node, $beforekey);
    }

}
