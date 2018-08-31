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
 
defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");
 
class meta_form extends moodleform {
    function definition() {
        global $CFG;
 
        $mform = $this->_form;
 
		$attributes = array('size' => 5, 'maxlength' => '5');
		$mform->addElement('text', 'language', get_string('editmeta_language', 'local_jointly'), $attributes);
		$mform->setType('language', PARAM_RAW);
		$mform->addRule('language', null, 'required', null, 'client');
		
		$attributes = array('size' => 80, 'maxlength' => '80');
		$mform->addElement('text', 'identifier', get_string('editmeta_identifier', 'local_jointly'), $attributes);
		$mform->setType('identifier', PARAM_RAW);
		$mform->addRule('identifier', null, 'required', null, 'client');
		
		$attributes = array('size' => 80, 'maxlength' => '255');
		$mform->addElement('text', 'title', get_string('editmeta_title', 'local_jointly'), $attributes);
		$mform->setType('title', PARAM_RAW);
		$mform->addRule('title', null, 'required', null, 'client');
		
		$mform->addElement('htmleditor', 'description', get_string('editmeta_description', 'local_jointly'));
		$mform->setType('description', PARAM_TEXT);
		$mform->addRule('description', null, 'required', null, 'client');
 
		$attributes = array('size' => 80, 'maxlength' => '255');
		$mform->addElement('text', 'keywords', get_string('editmeta_keywords', 'local_jointly'), $attributes);
		$mform->setType('keywords', PARAM_RAW);
		
		$attributes = array('size' => 10, 'maxlength' => '10');
		$mform->addElement('text', 'metadataprefix', get_string('editmeta_metadataprefix', 'local_jointly'), $attributes);
		$mform->setType('metadataprefix', PARAM_RAW);
		
		$attributes = array('size' => 10, 'maxlength' => '10');
		$mform->addElement('text', 'listidentprefix', get_string('editmeta_listidentprefix', 'local_jointly'), $attributes);
		$mform->setType('listidentprefix', PARAM_RAW);
		
		$mform->addElement('hidden', 'id');
		$mform->setType('id', PARAM_RAW);
		
		$this->add_action_buttons(true, get_string('save'));
    }

}