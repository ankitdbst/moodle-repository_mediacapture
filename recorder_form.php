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
 * Recorder form will show recorder in filepicker
 * @package    repository_mediacapture
 * @copyright  2012 Ankit Gupta <mailtoankitgupta@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/locallib.php');

global $CFG, $PAGE;

require_once("$CFG->libdir/formslib.php");

/**
 * This is a class used to define a mediacapture form for the recorders
 */
class recorder_form extends moodleform {
    /** @var string action */
    protected $action;

    /**
     * Definition of the moodleform
     */
    public function definition() {
        global $CFG;

        $mediacapture = new mediacapture();

        $mform =& $this->_form;

        $this->action = $this->_customdata['action'];
        $mform->addElement('html', '<div class="mediacontainer" id="mediacontainer">');
        switch ($this->action) {
            case 'init': // Initial form for selection from audio/video recorders.
                $mediacapture->viewrecorderselection($mform, $this->_customdata['enabledrecorders'], $this->_customdata['autodetect']);
                break;
            case 'display': // Displays the form for the recorder selected.
                $this->_customdata['recorder']->view($mform, $this->_customdata['recorderoptions']);
                break;
            case 'nodisplay': // In case no recorders are available for client.
                $mediacapture->display_errors($mform, $this->_customdata['errors']);
                break;
        }
        $mform->addElement('html', '</div>');
    }
}