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

namespace local_profilefield_repeatable;

/**
 * Tests for local_profilefield_repeatable manager and resolver.
 *
 * @package    local_profilefield_repeatable
 * @covers     \local_profilefield_repeatable\local\Manager
 * @covers     \local_profilefield_repeatable\Resolver
 * @copyright  2026 Anderson Blaine
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class manager_test extends \advanced_testcase {
    /** @var string Test city label. */
    private const CITY_LABEL = 'Sao Paulo';
    /**
     * Ensure item upsert updates cache-visible label resolution.
     */
    public function test_upsert_items_updates_resolved_label(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        if (
            !$DB->get_manager()->table_exists(new \xmldb_table('local_profilefield_repeatable_domain')) ||
            !$DB->get_manager()->table_exists(new \xmldb_table('local_profilefield_repeatable_item'))
        ) {
            $this->markTestSkipped('local_profilefield_repeatable tables are not available.');
        }

        $manager = new \local_profilefield_repeatable\local\Manager();
        $manager->upsert_domain('diretoria', 'Diretoria');

        $first = $manager->upsert_items('diretoria', [[
            'code' => '16',
            'label' => self::CITY_LABEL,
        ]]);
        $this->assertSame(1, $first['inserted']);
        $this->assertSame(0, $first['updated']);

        $this->assertSame('Sao Paulo', \local_profilefield_repeatable\Resolver::resolve('diretoria', '16'));

        $second = $manager->upsert_items('diretoria', [[
            'code' => '16',
            'label' => 'Sao Paulo - Capital',
        ]]);
        $this->assertSame(0, $second['inserted']);
        $this->assertSame(1, $second['updated']);

        $this->assertSame('Sao Paulo - Capital', \local_profilefield_repeatable\Resolver::resolve('diretoria', '16'));
    }

    /**
     * Ensure CSV parser accepts optional header and returns code-label rows.
     */
    public function test_parse_csv_content_with_header(): void {
        $manager = new \local_profilefield_repeatable\local\Manager();
        $items = $manager->parse_csv_content("code,label\n16,Sao Paulo\n17,Rio");

        $this->assertCount(2, $items);
        $this->assertSame('16', $items[0]['code']);
        $this->assertSame('Sao Paulo', $items[0]['label']);
        $this->assertSame('17', $items[1]['code']);
        $this->assertSame('Rio', $items[1]['label']);
    }
}
