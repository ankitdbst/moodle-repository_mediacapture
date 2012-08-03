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
 * repository_mediacapture_localaudiorecorder class
 *
 * @copyright  2012 Ankit Gupta <mailtoankitgupta@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/mediacapture.php');

class repository_mediacapture_localaudiorecorder implements mediacapture {

    /**
     * Default constructor
     */
    public function __construct() {
        global $PAGE, $CFG;
    }

    /**
     * @return array $options Array of type options used by the recorder
     */
    public static function get_type_option_names() {
    	return array('localaudiorecorder');
    }

    /**
     * Adds the settings configuration needed by the recorder to the plugin
     * @param object $mform
     */
    public function get_config_form($mform) {
        $mform->addElement('advcheckbox', 'localaudiorecorder', get_string('localaudiorecorder', 'repository_mediacapture'), null, array('group' => 1));
    }

    /**
     * @param object $mform
     */
    public function view($mform) {
    	global $CFG, $PAGE;

        $url        = new moodle_url("$CFG->wwwroot/repository/mediacapture/plugins/localaudiorecorder/assets/recorder.swf?gateway=form");
        $js         = urlencode("(function(a,b) { M.repository_mediacapture_localaudiorecorder.validate(a,b); })");
        $flashvars  = "&callback={$js}&filename=Untitled";

        $recorder   = '
                    <object id="onlineaudiorecorder" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="225" height="138">
                        <param name="movie" value="' . $url . $flashvars . '" />
                        <param name="wmode" value="transparent" />
                        <!--[if !IE]>-->
                        <object type="application/x-shockwave-flash" data="' . $url . $flashvars . '" width="215" height="138">
                        <!--<![endif]-->
                        <div><p><a href="http://www.adobe.com/go/getflashplayer">
                        <img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" />
                        </a></p></div>
                        </object>
                        <!--<![endif]-->
                    </object>';
        $mform->addElement('html', $recorder);
        $mform->addElement('hidden', 'filepath', '');
        $mform->addElement('hidden', 'filename', '');
        $mform->addElement('hidden', 'filetype', 'mp3');
    }

    /**
     * @return string $url
     */
    public function post_url() {
        global $CFG;
        $posturl = new moodle_url("$CFG->wwwroot/repository/mediacapture/plugins/localaudiorecorder/record.php");
        return $posturl;
    }

    /**
     * @return string $keys Array of string keys used by the recorder.
     */
    public function string_keys() {
        return array(
            'flashnotfound', 'norecordingfound',
            'nonamefound', 'filenotsaved'
        );
    }

    /**
     * @return array $version Minimum version of $type required by the recorder.
     */
    public function min_version() {
        return array('flash' => 10.1);
    }

    /**
     * @return array $media Supported media by the recorder.
     */
    public function supported_media() {
        return array('audio');
    }

    /**
     * @return array $type Supported type by the recorder.
     */
    public function supported_types() {
        return array('flash');
    }
}

