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

$manager = new \local_profilefield_repeatable\local\Manager();
$error = '';
$action = optional_param('action', '', PARAM_ALPHA);

if ($action !== '' && confirm_sesskey() && data_submitted()) {
    try {
        if ($action === 'createdomain') {
            $shortname = optional_param('domainshortname', '', PARAM_RAW_TRIMMED);
            $name = optional_param('domainname', '', PARAM_TEXT);

            if (trim($shortname) === '') {
                throw new invalid_parameter_exception(get_string('domainrequired', 'local_profilefield_repeatable'));
            }

            if (!preg_match('/^[a-z0-9_]+$/', core_text::strtolower(trim($shortname)))) {
                throw new invalid_parameter_exception(get_string('domaininvalid', 'local_profilefield_repeatable'));
            }

            $domain = $manager->upsert_domain($shortname, $name);
            redirect($PAGE->url, get_string('domainsaved', 'local_profilefield_repeatable', $domain->shortname));
        }

        if ($action === 'importcsv') {
            $shortname = optional_param('importdomainshortname', '', PARAM_RAW_TRIMMED);
            if (trim($shortname) === '') {
                throw new invalid_parameter_exception(get_string('domainrequired', 'local_profilefield_repeatable'));
            }

            $csvcontent = '';
            if (
                !empty($_FILES['csvfile']['tmp_name']) &&
                ($_FILES['csvfile']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK &&
                is_uploaded_file($_FILES['csvfile']['tmp_name'])
            ) {
                $csvcontent = (string)file_get_contents($_FILES['csvfile']['tmp_name']);
            }

            if (trim($csvcontent) === '') {
                $csvcontent = optional_param('csvtext', '', PARAM_RAW);
            }

            if (trim($csvcontent) === '') {
                throw new invalid_parameter_exception(get_string('csvrequired', 'local_profilefield_repeatable'));
            }

            $items = $manager->parse_csv_content($csvcontent);
            $summary = (object)$manager->upsert_items($shortname, $items);
            redirect($PAGE->url, get_string('importsummary', 'local_profilefield_repeatable', $summary));
        }
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

$domainrows = [];
foreach ($domains as $domain) {
    $domainrows[] = [
        'shortname' => (string)$domain->shortname,
        'name' => (string)$domain->name,
        'timemodified' => userdate((int)$domain->timemodified),
    ];
}

$context = [
    'errornotification' => $error !== '' ? $OUTPUT->notification($error, 'notifyproblem') : '',
    'csvhelp' => get_string('csvhelp', 'local_profilefield_repeatable'),
    'url' => $PAGE->url->out(false),
    'sesskey' => sesskey(),
    'createdomain' => get_string('createdomain', 'local_profilefield_repeatable'),
    'importcsv' => get_string('importcsv', 'local_profilefield_repeatable'),
    'domainshortname' => get_string('domainshortname', 'local_profilefield_repeatable'),
    'domainname' => get_string('domainname', 'local_profilefield_repeatable'),
    'csvfile' => get_string('csvfile', 'local_profilefield_repeatable'),
    'csvtext' => get_string('csvtext', 'local_profilefield_repeatable'),
    'existingdomains' => get_string('existingdomains', 'local_profilefield_repeatable'),
    'shortname' => get_string('shortname', 'local_profilefield_repeatable'),
    'name' => get_string('name', 'local_profilefield_repeatable'),
    'timemodified' => get_string('timemodified', 'local_profilefield_repeatable'),
    'none' => get_string('none'),
    'placeholderdomainshortname' => get_string('placeholderdomainshortname', 'local_profilefield_repeatable'),
    'placeholderdomainname' => get_string('placeholderdomainname', 'local_profilefield_repeatable'),
    'hasdomains' => !empty($domainrows),
    'domains' => $domainrows,
];

echo $OUTPUT->render_from_template('local_profilefield_repeatable/manage', $context);

echo $OUTPUT->footer();
