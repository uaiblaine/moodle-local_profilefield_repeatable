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

if ($error !== '') {
    echo $OUTPUT->notification($error, 'notifyproblem');
}

echo html_writer::tag('p', s(get_string('csvhelp', 'local_profilefield_repeatable')), ['class' => 'text-muted mb-4']);

$form = [];
$form[] = html_writer::start_tag('form', [
    'method' => 'post',
    'action' => $PAGE->url,
    'class' => 'mb-4 card card-body',
]);
$form[] = html_writer::empty_tag('input', [
    'type' => 'hidden',
    'name' => 'action',
    'value' => 'createdomain',
]);
$form[] = html_writer::empty_tag('input', [
    'type' => 'hidden',
    'name' => 'sesskey',
    'value' => sesskey(),
]);
$form[] = html_writer::tag('h3', s(get_string('createdomain', 'local_profilefield_repeatable')), ['class' => 'h5']);
$form[] = html_writer::start_div('row g-3 align-items-end');
$form[] = html_writer::start_div('col-md-4');
$form[] = html_writer::tag(
    'label',
    s(get_string('domainshortname', 'local_profilefield_repeatable')),
    ['for' => 'id_domainshortname']
);
$form[] = html_writer::empty_tag('input', [
    'type' => 'text',
    'name' => 'domainshortname',
    'id' => 'id_domainshortname',
    'class' => 'form-control',
    'placeholder' => 'diretoria',
]);
$form[] = html_writer::end_div();
$form[] = html_writer::start_div('col-md-5');
$form[] = html_writer::tag('label', s(get_string('domainname', 'local_profilefield_repeatable')), ['for' => 'id_domainname']);
$form[] = html_writer::empty_tag('input', [
    'type' => 'text',
    'name' => 'domainname',
    'id' => 'id_domainname',
    'class' => 'form-control',
    'placeholder' => 'Diretoria',
]);
$form[] = html_writer::end_div();
$form[] = html_writer::start_div('col-md-3');
$form[] = html_writer::empty_tag('input', [
    'type' => 'submit',
    'class' => 'btn btn-primary w-100',
    'value' => get_string('createdomain', 'local_profilefield_repeatable'),
]);
$form[] = html_writer::end_div();
$form[] = html_writer::end_div();
$form[] = html_writer::end_tag('form');

echo implode('', $form);

$importform = [];
$importform[] = html_writer::start_tag('form', [
    'method' => 'post',
    'action' => $PAGE->url,
    'class' => 'mb-4 card card-body',
    'enctype' => 'multipart/form-data',
]);
$importform[] = html_writer::empty_tag('input', [
    'type' => 'hidden',
    'name' => 'action',
    'value' => 'importcsv',
]);
$importform[] = html_writer::empty_tag('input', [
    'type' => 'hidden',
    'name' => 'sesskey',
    'value' => sesskey(),
]);
$importform[] = html_writer::tag('h3', s(get_string('importcsv', 'local_profilefield_repeatable')), ['class' => 'h5']);
$importform[] = html_writer::start_div('row g-3');
$importform[] = html_writer::start_div('col-md-4');
$importform[] = html_writer::tag(
    'label',
    s(get_string('domainshortname', 'local_profilefield_repeatable')),
    ['for' => 'id_importdomainshortname']
);
$importform[] = html_writer::empty_tag('input', [
    'type' => 'text',
    'name' => 'importdomainshortname',
    'id' => 'id_importdomainshortname',
    'class' => 'form-control',
    'placeholder' => 'diretoria',
]);
$importform[] = html_writer::end_div();
$importform[] = html_writer::start_div('col-md-8');
$importform[] = html_writer::tag('label', s(get_string('csvfile', 'local_profilefield_repeatable')), ['for' => 'id_csvfile']);
$importform[] = html_writer::empty_tag('input', [
    'type' => 'file',
    'name' => 'csvfile',
    'id' => 'id_csvfile',
    'class' => 'form-control',
    'accept' => '.csv,text/csv,text/plain',
]);
$importform[] = html_writer::end_div();
$importform[] = html_writer::start_div('col-12');
$importform[] = html_writer::tag('label', s(get_string('csvtext', 'local_profilefield_repeatable')), ['for' => 'id_csvtext']);
$importform[] = html_writer::tag('textarea', '', [
    'name' => 'csvtext',
    'id' => 'id_csvtext',
    'class' => 'form-control',
    'rows' => 6,
]);
$importform[] = html_writer::end_div();
$importform[] = html_writer::start_div('col-12');
$importform[] = html_writer::empty_tag('input', [
    'type' => 'submit',
    'class' => 'btn btn-secondary',
    'value' => get_string('importcsv', 'local_profilefield_repeatable'),
]);
$importform[] = html_writer::end_div();
$importform[] = html_writer::end_div();
$importform[] = html_writer::end_tag('form');

echo implode('', $importform);

echo html_writer::tag('h3', s(get_string('existingdomains', 'local_profilefield_repeatable')), ['class' => 'h5']);
if (empty($domains)) {
    echo $OUTPUT->notification(get_string('none'), 'notifyinfo');
} else {
    $table = new html_table();
    $table->head = [
        get_string('shortname', 'local_profilefield_repeatable'),
        get_string('name', 'local_profilefield_repeatable'),
        get_string('timemodified', 'local_profilefield_repeatable'),
    ];

    foreach ($domains as $domain) {
        $table->data[] = [
            s((string)$domain->shortname),
            s((string)$domain->name),
            userdate((int)$domain->timemodified),
        ];
    }

    echo html_writer::table($table);
}

echo $OUTPUT->footer();
