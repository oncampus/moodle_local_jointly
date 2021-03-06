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

function local_jointly_cron() {
	//
}

function local_jointly_extend_settings_navigation() {
	//
}

function local_jointly_get_license_types() {
	global $DB;
	$license_types = array();
	$records = $DB->get_records('license');
	foreach ($records as $record) {
		$license_types[$record->id] = $record->shortname;
	}
	return $license_types;
}
?>