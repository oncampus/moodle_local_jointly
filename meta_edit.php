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
 * @package    local_jointly
 * @copyright  2018 Stefan Bomanns, ILD, University of Applied Sciences Lübeck <stefan.bomanns@fh-luebeck.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
require_once('../../config.php');
require_once('meta_form.php');
require_once('locallib.php');

$language = optional_param('language', '', PARAM_RAW);

$context = context_system::instance();

if (!has_capability('moodle/site:config', $context)) {
	redirect(new moodle_url('/local/jointly/view.php'));
}

$PAGE->set_context($context);
$PAGE->set_url('/local/jointly/meta_edit.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('editmetadata', 'local_jointly'));

$mform = new meta_form();

// Check if data exists
if ($DB->record_exists('local_jointly', array('language' => $language))) {
	if ($record_exists = $DB->get_records('local_jointly', array('language' => $language))) {
		foreach($record_exists as $re) {
			$toform['id'] = $re->id;
			$toform['language'] = $re->language;
			$toform['identifier'] = $re->identifier;
			$toform['title'] = $re->title;
			$toform['description'] = $re->description;
			$toform['keywords'] = $re->keywords;
			$toform['metadataprefix'] = $re->metadataprefix;
			$toform['listidentprefix'] = $re->listidentprefix;
		}
		
		$mform->set_data($toform);	
		
	} else {
		print_error('selecterror', 'local_jointly');
		redirect(new moodle_url('/local/jointly/view.php'));
	}
} 

if ($mform->is_cancelled()) {
	
	redirect(new moodle_url('/local/jointly/view.php'));
	
} elseif ($fromform = $mform->get_data()) {
	
	if (!empty($fromform->id)) {		
		if (!$DB->update_record('local_jointly', $fromform)) {
			print_error('updateerror', 'local_jointly');
		}
	} else {
		if (!$DB->insert_record('local_jointly', $fromform)) {
			print_error('inserterror', 'local_jointly');
		}
	}

	redirect(new moodle_url('/local/jointly/view.php'));
	
} else {
	
	echo $OUTPUT->header();
	echo html_writer::tag('b', get_string('editmetadata_languageswitch', 'local_jointly')) . "<br>";
	meta_language_switch();
	$mform->display();
	echo $OUTPUT->footer();
}