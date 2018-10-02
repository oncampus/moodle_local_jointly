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

$string['pluginname'] = 'jointly';

$string['admins_only_title'] = 'Admins only';
$string['admins_only_desc'] = 'When checkbox is activated, only users with the capability moodle/site:config can access the view.php.';
$string['file_types'] = 'File types';
$string['file_types_desc'] = 'The following file types will be considered. 
                              Leave the field blank to search for all file types.
							  Separate file types with comma (pdf,jpg,...)';
$string['file_types_default'] = '';
$string['freeforall_title'] = 'free for all';
$string['freeforall_desc'] = 'When checkbox is activated, all files can be downloaded by every user without authentification.';
$string['license_types'] = 'Allowed licence typesn';
$string['license_types_desc'] = 'Only records which matching the selected licenses will be shown.';

$string['component'] = 'component';
$string['description'] = 'description';
$string['filearea'] = 'filearea';
$string['filename'] = 'filename';
$string['filesize'] = 'filesize';
$string['mimetype'] = 'mimetype';
$string['author'] = 'author';
$string['license'] = 'license';
$string['timecreated'] = 'timecreated';
$string['timemodified'] = 'timemodified';

$string['editmetadata'] = 'Edit Metadata';
$string['editmeta_language'] = 'Language key';
$string['editmeta_identifier'] = 'ID (Persistent Identifier)';
$string['editmeta_title'] = 'Title';
$string['editmeta_description'] = 'Description';
$string['editmeta_keywords'] = 'Keywords';
$string['editmeta_metadataprefix'] = 'MetadataPrefix';
$string['editmeta_listidentprefix'] = 'Prefix for ListIdentifiers';

$string['editmeta_selecterror'] = 'Database error! The record could not be read.';
$string['editmeta_updateerror'] = 'Database error! Unable to update the selected record.';
$string['editmeta_inserterror'] = 'Database error! Unable to insert the selected record.';

$string['getmetadataerror'] = 'Please add metadata for the selected language.';
$string['editmetadata_languageswitch'] = 'Select language';
$string['editmeta_newlanguage'] = 'Add new language';