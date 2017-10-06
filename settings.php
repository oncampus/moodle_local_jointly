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

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    //global $CFG, $USER, $DB;

    $settings = new admin_settingpage('local_jointly', get_string('pluginname', 'local_jointly'));
    $ADMIN->add('localplugins', $settings);
	
	$setting = new admin_setting_configcheckbox('local_jointly/freeforall', get_string('freeforall_title', 'local_jointly'), get_string('freeforall_desc', 'local_jointly'), 0);
	$settings->add($setting);
	
	$setting = new admin_setting_configcheckbox('local_jointly/admins_only', get_string('admins_only_title', 'local_jointly'), get_string('admins_only_desc', 'local_jointly'), 0);
	$settings->add($setting);
	
	require_once($CFG->dirroot.'/local/jointly/lib.php');
    $options = local_jointly_get_license_types();
    $settings->add(new admin_setting_configmultiselect('local_jointly/license_types',
        get_string('license_types', 'local_jointly'), get_string('license_types_desc', 'local_jointly'),
        array_keys($options), $options));
	
	$name = 'local_jointly/file_types';
	$title = get_string('file_types', 'local_jointly');
	$description = get_string('file_types_desc', 'local_jointly');
	$setting = new admin_setting_configtext($name, $title, $description, get_string('file_types_default', 'local_jointly'));
	$settings->add($setting);
}

