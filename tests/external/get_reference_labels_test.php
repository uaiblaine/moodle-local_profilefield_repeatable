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

namespace local_profilefield_repeatable\external;

use core_external\external_api;

/**
 * Tests for get_reference_labels external function.
 *
 * @package    local_profilefield_repeatable
 * @covers     \local_profilefield_repeatable\external\get_reference_labels
 * @copyright  2026 Anderson Blaine
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class get_reference_labels_test extends \advanced_testcase {
    /**
     * Skip when plugin tables are not present.
     */
    private function require_tables(): void {
        global $DB;
        $dbman = $DB->get_manager();
        if (
            !$dbman->table_exists(new \xmldb_table('local_profilefield_repeatable_domain')) ||
            !$dbman->table_exists(new \xmldb_table('local_profilefield_repeatable_item'))
        ) {
            $this->markTestSkipped('local_profilefield_repeatable tables are not available.');
        }
    }

    /**
     * Returns labels for known codes only.
     */
    public function test_execute_returns_resolved_labels(): void {
        $this->resetAfterTest();
        $this->require_tables();
        $this->setAdminUser();

        $manager = new \local_profilefield_repeatable\local\manager();
        $manager->upsert_domain('diretoria', 'Diretoria');
        $manager->upsert_items('diretoria', [
            ['code' => '01', 'label' => 'Sao Paulo'],
        ]);

        $result = get_reference_labels::execute('diretoria', ['01', '99']);
        $result = external_api::clean_returnvalue(get_reference_labels::execute_returns(), $result);

        $this->assertCount(1, $result['items']);
        $this->assertSame('01', $result['items'][0]['code']);
        $this->assertSame('Sao Paulo', $result['items'][0]['label']);
    }

    /**
     * Throws when batch size exceeds the configured maximum.
     */
    public function test_execute_rejects_oversized_batch(): void {
        $this->resetAfterTest();
        $this->require_tables();
        $this->setAdminUser();

        $manager = new \local_profilefield_repeatable\local\manager();
        $manager->upsert_domain('diretoria', 'Diretoria');

        $codes = array_fill(0, \local_profilefield_repeatable\local\manager::MAX_BATCH_SIZE + 1, 'x');

        $this->expectException(\invalid_parameter_exception::class);
        get_reference_labels::execute('diretoria', $codes);
    }

    /**
     * Requires the manage capability.
     */
    public function test_execute_requires_capability(): void {
        $this->resetAfterTest();
        $this->require_tables();

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $this->expectException(\required_capability_exception::class);
        get_reference_labels::execute('diretoria', ['01']);
    }
}
