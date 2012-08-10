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
 * repository_mediacapture_flashaudiorecorder class
 *
 * @copyright  2012 Ankit Gupta <mailtoankitgupta@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/recorder.php');

class repository_mediacapture_flashaudiorecorder extends recorder {

    /**
     * Type option names for the recorder
     *
     * @return array $options
     */
    public static function get_type_option_names() {
        return array('flashaudiorecorder');
    }

    /**
     * Admin config settings for the type options defined in get_type_option_names()
     *
     * @param $mform
     */
    public function add_config_form($mform) {
        $mform->addElement('advcheckbox', 'flashaudiorecorder', get_string('flashaudiorecorder', 'repository_mediacapture'),
                             null, array('group' => 1));
    }

    /**
     * The form should contain the following required parameters by mediacapture
     *
     * @param moodleform $mform instance of recoder form
     * @param array $options recorder options
     */
    public function view($mform, $options) {
        global $CFG, $PAGE;

        $url = new moodle_url("$CFG->wwwroot/repository/mediacapture/recorders/flashaudiorecorder/assets/recorder.swf?gateway=form");
        $js = urlencode("(function(a,b) { M.repository_mediacapture_flashaudiorecorder.validate(a,b); })");
        $flashvars = "&callback={$js}&filename=Untitled";

        $recorder   = '
                    <object id="onlineaudiorecorder" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="225" height="138">
                        <param name="movie" value="' . $url . $flashvars . '" />
                        <param name="wmode" value="transparent" />
                        <!--[if !IE]>-->
                        <object type="application/x-shockwave-flash" data="' . $url . $flashvars . '" width="215" height="138">
                        <!--<![endif]-->
                        <div><p><a href="http://www.adobe.com/go/getflashplayer">
                        <img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif"
                                alt="Get Adobe Flash player" />
                        </a></p></div>
                        </object>
                        <!--<![endif]-->
                    </object>';
        $mform->addElement('html', $recorder);
        $mform->addElement('hidden', 'filepath', '');
        $mform->addElement('hidden', 'filename', '');
        $mform->addElement('hidden', 'filetype', $this->supported_filetype());
    }

    /**
     * Url for submitting the recorded file (via ajax) to temp_dir()
     *
     * @return string $url
     */
    public function post_url() {
        global $CFG;
        $posturl = new moodle_url("$CFG->wwwroot/repository/mediacapture/recorders/flashaudiorecorder/record.php");
        return $posturl;
    }

    /**
     * List of all string keys defined by the recorder in the lang file
     *
     * @return array $strings
     */
    public function string_keys() {
        return array(
            'flashnotfound', 'noflashaudiofound',
            'nonamefound', 'filenotsaved'
        );
    }

    /**
     * Min version of supported_mediatypes() required by the recorder
     *
     * @return array $version
     */
    public function min_version() {
        return array('flash' => 10.1);
    }

    /**
     * Supported media viz array('audio', 'video')
     *
     * @return array $media
     */
    public function supported_media() {
        return array('audio');
    }

    /**
     * Supported type viz array('html5', 'flash', 'java')
     *
     * @return array $type
     */
    public function supported_mediatypes() {
        return array('flash');
    }

    /**
     * Return string of supported filetype associated with the recording
     *
     * @return string of supported file types/extensions.
     */
    public function supported_filetype() {
        return '.mp3';
    }
}