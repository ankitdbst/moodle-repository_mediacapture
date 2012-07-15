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
 * Interface mediacapture that every sub-plugin should implement
 * @copyright  2012 Ankit Gupta <mailtoankitgupta@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/locallib.php');

interface mediacapture {

	/**
	 * List of type options for the recorder
	 */
	static function get_type_option_names();

	/**
	 * Admin config settings for the type options defined in
	 * get_type_option_names()
	 *
	 * @param $mform
	 */
	function get_config_form($mform);

	/**
	 * Renderer for the recorder
	 *
	 * @return raw/html
	 */
	function renderer();

	/**
	 * List of all the strings defined in the lang/en dir
	 */
	function get_string_defs();

	/**
	 * Min version of java/flash required by the plugin
	 *
	 * @return array structure containing the version
	 */	
	function get_min_version();

	/**
	 * array('audio', 'video')
	 *
     * @return array of supported media.
     */
    function supported_media();

    /**
     * array('html5', 'flash', 'java')
     *
     * @return array of supported web technology
     */
    function supported_technology();

}