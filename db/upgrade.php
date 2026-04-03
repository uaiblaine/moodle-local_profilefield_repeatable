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
 * Upgrade script for local_profilefield_repeatable.
 *
 * @package    local_profilefield_repeatable
 * @copyright  2026 Anderson Blaine
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade steps.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_profilefield_repeatable_upgrade(int $oldversion): bool {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026030601) {
        $legacydomain = new xmldb_table('local_pfr_domain');
        $legacyitem = new xmldb_table('local_pfr_item');
        $currentdomain = new xmldb_table('local_profilefield_repeatable_domain');
        $currentitem = new xmldb_table('local_profilefield_repeatable_item');

        if ($dbman->table_exists($legacydomain) && !$dbman->table_exists($currentdomain)) {
            $dbman->rename_table($legacydomain, $currentdomain->getName());
        }

        if ($dbman->table_exists($legacyitem) && !$dbman->table_exists($currentitem)) {
            $dbman->rename_table($legacyitem, $currentitem->getName());
        }

        upgrade_plugin_savepoint(true, 2026030601, 'local', 'profilefield_repeatable');
    }

    return true;
}
