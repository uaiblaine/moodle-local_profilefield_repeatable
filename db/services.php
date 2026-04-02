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
 * Web service definitions for local_profilefield_repeatable.
 *
 * @package    local_profilefield_repeatable
 * @copyright  2026 Anderson Blaine
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_profilefield_repeatable_upsert_reference_items' => [
        'classname' => 'local_profilefield_repeatable\\external\\upsert_reference_items',
        'methodname' => 'execute',
        'classpath' => '',
        'description' => 'Upsert reference code-label pairs for one domain.',
        'type' => 'write',
        'capabilities' => 'local/profilefield_repeatable:managereference',
        'ajax' => false,
    ],
    'local_profilefield_repeatable_get_reference_labels' => [
        'classname' => 'local_profilefield_repeatable\\external\\get_reference_labels',
        'methodname' => 'execute',
        'classpath' => '',
        'description' => 'Resolve labels for reference codes in one domain.',
        'type' => 'read',
        'capabilities' => 'local/profilefield_repeatable:managereference',
        'ajax' => false,
    ],
];

$services = [
    'Profilefield Repeatable Reference API' => [
        'functions' => [
            'local_profilefield_repeatable_upsert_reference_items',
            'local_profilefield_repeatable_get_reference_labels',
        ],
        'restrictedusers' => 0,
        'enabled' => 1,
        'shortname' => 'local_profilefield_repeatable_api',
    ],
];
