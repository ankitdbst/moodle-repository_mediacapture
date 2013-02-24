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
 * repository_mediacapture_red5recorder class
 *
 * @copyright  2012 Ankit Gupta <mailtoankitgupta@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/recorder.php');

class repository_mediacapture_red5recorder extends recorder {

    /**
     * Type option names for the recorder
     *
     * @return array $options
     */
    public static function get_type_option_names() {
        return array('red5_rtmp_server', 'red5_max_length', 'red5recorder');
    }

    /**
     * Admin config settings for the type options defined in get_type_option_names()
     *
     * @param $mform
     */
    public function add_config_form($mform) {
        $mform->addElement('advcheckbox', 'red5recorder', get_string('red5recorder', 'repository_mediacapture'), null,
                            array('group' => 1));
        $mform->addElement('text', 'red5_rtmp_server', get_string('red5rtmpserver', 'repository_mediacapture'),
                            'maxlength="100" size="25" ');
        $mform->setType('red5_rtmp_server', PARAM_NOTAGS);
        $mform->setDefault('red5_rtmp_server', '127.0.0.1');
        $mform->addElement('text', 'red5_max_length', get_string('red5maxlength', 'repository_mediacapture'), null,
                            array('group' => 1));
        $mform->setType('red5_max_length', PARAM_INT);
        $mform->setDefault('red5_max_length', 90);
    }

    /**
     * The form should contain the following required parameters by mediacapture
     *
     * @param moodleform $mform instance of recoder form
     * @param array $options recorder options
     */
    public function view($mform, $options) {
        global $CFG;

        $tmpname    = sha1(uniqid(rand(), true));
        $rtmpserver = $options['red5_rtmp_server'];
        $maxlength  = $options['red5_max_length'];
        $streampath = "http://$rtmpserver:5080/red5recorder/streams/$tmpname.flv";

        $url = new moodle_url("$CFG->wwwroot/repository/mediacapture/recorders/red5recorder/assets/red5recorder.swf");
        $flashvars = "?server=rtmp://$rtmpserver/red5recorder/&fileName=$tmpname&maxLength=$maxlength";

        $recorder   = '
                    <object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"
                        id="red5recorder" width="100%" height="100%"
                        codebase="http://fpdownload.macromedia.com/get/flashplayer/current/swflash.cab">
                        <param name="movie" value="' . $url . '" />
                        <param name="quality" value="high" />
                        <param name="bgcolor" value="#869ca7" />
                        <param name="allowScriptAccess" value="sameDomain" />
                        <embed src="' . $url . $flashvars . '" quality="high" bgcolor="#869ca7"
                            width="320px" height="240px" name="red5recorder" align="middle"
                            play="true"
                            loop="false"
                            quality="high"
                            allowScriptAccess="sameDomain"
                            type="application/x-shockwave-flash"
                            pluginspage="http://www.adobe.com/go/getflashplayer">
                        </embed>
                    </object>';
        $mform->addElement('html', $recorder);
        $mform->addElement('hidden', 'filepath', urlencode($streampath));
        $mform->addElement('hidden', 'filetype', $this->supported_filetype());
        $mform->addElement('hidden', 'tmpname', $tmpname);
        $mform->addElement('text', 'filename', get_string('name', 'repository_mediacapture'));
        $mform->addElement('submit', 'save', get_string('save', 'repository_mediacapture'));
    }

    /**
     * Url for submitting the recorded file (via ajax) to temp_dir()
     *
     * @return string $url
     */
    public function post_url() {
        global $CFG;
        $posturl = new moodle_url("$CFG->wwwroot/repository/mediacapture/recorders/red5recorder/record.php");
        return $posturl;
    }

    /**
     * List of all string keys defined by the recorder in the lang file
     *
     * @return array $strings
     */
    public function string_keys() {
        return array(
            'red5recorder', 'red5rtmpserver', 'filenotsaved',
            'nored5recordingfound', 'nonamefound', 'red5maxlength'
        );
    }

    /**
     * Min version of supported_mediatypes() required by the recorder
     *
     * @return array $version
     */
    public function min_version() {
        return array('flash' => 9);
    }

    /**
     * Supported media viz array('audio', 'video')
     *
     * @return array $media
     */
    public function supported_media() {
        return array('video');
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
        return '.flv';
    }
}