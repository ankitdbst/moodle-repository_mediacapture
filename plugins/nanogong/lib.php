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
 * mediacapture class
 * class which handles all the audio/video recording operations
 * of the repostory_mediacapture
 *
 * @copyright  2012 Ankit Gupta <mailtoankitgupta@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/../../config.php');

class mediacapture_plugins_nanogong extends mediacapture {

	/**
	 * Default constructor
	 */
	public function __construct() {
        global $PAGE, $CFG;
    }

    /**
     * Returns a list of type option names for nanogong
     */
    public function get_type_option_names() {
    	return array();
    }

    /**
     * Returns the config form elements for nanogong
     */
    public function get_config_form() {
    	return array();
    }

    /**
     * Returns the view renderer for nanogong
     */
    public function renderer() {
    	return '';
    }

    /**
     * Returns the supported recorder type
     */
    public function recorder_type() {
        return 'audio';
    }
}

