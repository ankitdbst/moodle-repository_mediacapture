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
     * @return array $options Array of type options used by the recorder
     */
    public static function get_type_option_names() {
        return array('rtmp_server', 'red5recorder');
    }

    /**
     * Adds the settings configuration needed by the recorder to the plugin
     * @param object $mform
     */
    public function add_config_form($mform) {
        $mform->addElement('advcheckbox', 'red5recorder', get_string('red5recorder', 'repository_mediacapture'), null,
                            array('group' => 1));
        $mform->addElement('text', 'rtmp_server', get_string('rtmpserver', 'repository_mediacapture'),
                            'maxlength="100" size="25" ');
        $mform->setType('rtmp_server', PARAM_NOTAGS);
        $mform->setDefault('rtmp_server', '127.0.0.1');
    }

    /**
     * @param string $callbackurl for the plugin
     * @return string $recorder HTML for the recorder.
     */
    public function view($mform, $options) {
        global $CFG;

        $rtmpserver = $options['rtmp_server'];
        $tmpname = sha1(uniqid(rand(), true));
        $streampath = "http://$rtmpserver:5080/red5recorder/streams/$tmpname.flv";

        $url = new moodle_url("$CFG->wwwroot/repository/mediacapture/recorders/red5recorder/assets/red5recorder.swf");
        $flashvars = "?server=rtmp://$rtmpserver/red5recorder/&fileName=$tmpname";

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
        $mform->addElement('hidden', 'filetype', 'flv');
        $mform->addElement('hidden', 'tmpname', $tmpname);
        $mform->addElement('text', 'filename', get_string('name', 'repository_mediacapture'));
        $mform->addElement('submit', 'save', get_string('save', 'repository_mediacapture'));
    }

    /**
     * @return string $url
     */
    public function post_url() {
        global $CFG;
        $posturl = new moodle_url("$CFG->wwwroot/repository/mediacapture/recorders/red5recorder/record.php");
        return $posturl;
    }

    /**
     * @return string $keys Array of string keys used by the recorder.
     */
    public function string_keys() {
        return array(
            'red5recorder', 'rtmpserver', 'filenotsaved',
            'norecordingfound', 'nonamefound'
        );
    }

    /**
     * @return array $version Minimum version of $type required by the recorder.
     */
    public function min_version() {
        return array('flash' => 9);
    }

    /**
     * @return array $media Supported media by the recorder.
     */
    public function supported_media() {
        return array('video');
    }

    /**
     * @return array $type Supported technology by the recorder.
     */
    public function supported_types() {
        return array('flash');
    }
}