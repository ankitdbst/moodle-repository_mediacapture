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
     * Type option names for the recorder
     * @return array $options
     */
    static function get_type_option_names();

    /**
     * Admin config settings for the type options defined in
     * get_type_option_names()     *
     * @param $mform
     */
    function get_config_form($mform);

    /**
     * The form should contain the following required parameters by the plugin
     * @param filename : filename for the recorded stream
     * @param filepath : filepath for the temporary location of the file
     * @param filetype : filetype associated with the recording viz 'wav', 'mp3', 'flv'
     *
     * @param object $mform
     */
    function view($mform);

    /**
     * List of all string keys defined by the recorder in the lang file
     * @return array $strings
     */
    function string_keys();

    /**
     * Min version of supported_type() required by the recorder
     * @return array $version
     */
    function min_version();

    /**
     * Supported media viz array('audio', 'video')
     * @return array $media
     */
    function supported_media();

    /**
     * Supported type viz array('html5', 'flash', 'java')
     * @return array $type
     */
    function supported_types();

}