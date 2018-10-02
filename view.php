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
 * @copyright  2017 Jan Rieger, ILD, University of Applied Sciences Lübeck <jan.rieger@fh-luebeck.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir.'/tablelib.php');
require_once('search_form.php');
require_once('locallib.php');

$search = optional_param('search', '', PARAM_RAW);
$verb = optional_param('verb', '', PARAM_RAW);
$language = optional_param('language', '', PARAM_RAW);

$context = context_system::instance();
 
if (get_config('local_jointly', 'freeforall') != 1) {
	require_login();
}

if (get_config('local_jointly', 'admins_only') == 1 and !has_capability('moodle/site:config', $context)) {
	redirect($CFG->wwwroot);
}
 
$PAGE->set_context($context);
$PAGE->set_url('/local/jointly/view.php');

$mform = new search_form(new moodle_url('/local/jointly/view.php'));

if ($mform->is_cancelled()) {
	redirect(new moodle_url('/local/jointly/view.php'));
}
else if ($fromform = $mform->get_data()) {
	//
}

$config = get_config('local_jointly');
$license_ids = explode(',', $config->license_types);
$license_types = get_license_types_string($license_ids);

$sql = 'SELECT * 
		  FROM {files} 
		 WHERE license in '.$license_types.' 
		   AND component != :resource ';
		   
$and = '';

if ($search != '') {
	$and = 'AND (license LIKE :search1 OR filename LIKE :search2)';
}
		   
$params = array('resource' => 'user',
				'search1' => '%'.$search.'%',
				'search2' => '%'.$search.'%');
		 
$files = $DB->get_records_sql($sql.$and, $params);

if ($verb == '') {
	echo $OUTPUT->header();
	echo $OUTPUT->heading(get_string('pluginname', 'local_jointly'));
	
	$mform->display();
	
	if (has_capability('moodle/site:config', $context)) {
		$url = new moodle_url('/local/jointly/meta_edit.php?language=' . $CFG->lang);
		echo html_writer::link($url, get_string('editmetadata', 'local_jointly')) . ' | ';
	}

	// View getRecord and ListIdentifiers
    $url = new moodle_url('/local/jointly/view.php?verb=ListIdentifiers');
	echo html_writer::link($url, 'XML ListIdentifiers', array('target' => '_blank')) . ' | ';
    $url = new moodle_url('/local/jointly/view.php?verb=getRecord');
	echo html_writer::link($url, 'XML getRecord', array('target' => '_blank')) . '<br><br>';
		
	$table = new flexible_table('MODULE_TABLE');
	$table->define_columns(array('filename', 
								 'description',
								 'license',
								 'filesize', 
								 'mimetype', 
								 'author', 
								 'component', 
								 'filearea',
								 'timecreated', 
								 'timemodified'));
	$table->define_headers(array(get_string('filename', 'local_jointly'),
								 get_string('description', 'local_jointly'),
								 get_string('license', 'local_jointly'),
								 get_string('filesize', 'local_jointly'), 
								 get_string('mimetype', 'local_jointly'), 
								 get_string('author', 'local_jointly'),
								 get_string('component', 'local_jointly'), 
								 get_string('filearea', 'local_jointly'), 
								 get_string('timecreated', 'local_jointly'), 
								 get_string('timemodified', 'local_jointly')));
	$table->define_baseurl($CFG->wwwroot.'/local/jointly/view.php');
	$table->set_attribute('class', 'admintable generaltable');
	$table->sortable(false, 'license', SORT_ASC);
	$table->setup();
	
	echo 'files: '.count($files);

	foreach ($files as $file) {
		$data = get_table_row($file);
		$table->add_data($data);
	}
	
	$table->print_html();
	
	echo $OUTPUT->footer();
}
elseif ($verb == 'json') {
	$metadata = get_metadata_array($files);
	echo json_encode($metadata, JSON_UNESCAPED_SLASHES);
}
elseif ($verb == 'getRecord') {
	get_lom($files, $language);
}
elseif ($verb == 'ListIdentifiers') {
	get_listidentifiers($files);
}


