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

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");
 
class search_form extends moodleform {
    //Add elements to form
    function definition() {
        global $CFG;
 
        $mform = $this->_form;
 
		$mform->addElement('text', 'search', get_string('search'));
		$mform->setDefault('search', '');
		/*
		$mform->addElement('date_selector', 'from', get_string('from'));
		$mform->addElement('date_selector', 'to', get_string('to'));
		*/
		$this->add_action_buttons(true, get_string('search'));
    }

    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
}