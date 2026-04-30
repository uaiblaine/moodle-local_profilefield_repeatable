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
 * Domain and CSV management UI.
 *
 * @package    local_profilefield_repeatable
 * @copyright  2026 Anderson Blaine
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('local_profilefield_repeatable_manage');

$context = context_system::instance();
require_capability('local/profilefield_repeatable:managereference', $context);

$PAGE->set_url(new moodle_url('/local/profilefield_repeatable/manage.php'));
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('managereference', 'local_profilefield_repeatable'));
$PAGE->set_heading(get_string('managereference', 'local_profilefield_repeatable'));

$manager = new \local_profilefield_repeatable\local\manager();
$error = '';

$createform = new \local_profilefield_repeatable\form\create_domain_form($PAGE->url->out(false));
$importform = new \local_profilefield_repeatable\form\import_csv_form($PAGE->url->out(false));

if ($createdata = $createform->get_data()) {
    try {
        $domain = $manager->upsert_domain(
            (string)$createdata->domainshortname,
            (string)($createdata->domainname ?? '')
        );
        redirect($PAGE->url, get_string('domainsaved', 'local_profilefield_repeatable', $domain->shortname));
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

if ($importdata = $importform->get_data()) {
    try {
        $csvcontent = $importform->get_csv_content($importdata);
        if (trim($csvcontent) === '') {
            throw new moodle_exception('csvrequired', 'local_profilefield_repeatable');
        }

        $items = $manager->parse_csv_content($csvcontent);
        $summary = (object)$manager->upsert_items((string)$importdata->importdomainshortname, $items);
        redirect($PAGE->url, get_string('importsummary', 'local_profilefield_repeatable', $summary));
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$domains = [];
try {
    $domains = $manager->list_domains();
} catch (Throwable $e) {
    $error = $e->getMessage();
}

echo $OUTPUT->header();

if ($error !== '') {
    echo $OUTPUT->notification($error, \core\output\notification::NOTIFY_ERROR);
}

$createform->display();
$importform->display();

$domainrows = [];
foreach ($domains as $domain) {
    $domainrows[] = [
        'shortname' => (string)$domain->shortname,
        'name' => (string)$domain->name,
        'timemodified' => userdate((int)$domain->timemodified),
    ];
}

$templatecontext = [
    'existingdomains' => get_string('existingdomains', 'local_profilefield_repeatable'),
    'shortname' => get_string('shortname', 'local_profilefield_repeatable'),
    'name' => get_string('name', 'local_profilefield_repeatable'),
    'timemodified' => get_string('timemodified', 'local_profilefield_repeatable'),
    'none' => get_string('none'),
    'hasdomains' => !empty($domainrows),
    'domains' => $domainrows,
];

echo $OUTPUT->render_from_template('local_profilefield_repeatable/manage', $templatecontext);

echo $OUTPUT->footer();
