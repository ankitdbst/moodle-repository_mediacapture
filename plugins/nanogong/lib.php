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

require_once(dirname(dirname(dirname(__FILE__))) . '/mediacapture.php');

class repository_mediacapture_nanogong implements mediacapture {

	/**
	 * Default constructor
	 */
	public function __construct() {
        global $PAGE, $CFG;
    }

    /**
     * Returns a list of type option names for nanogong
     */
    public static function get_type_option_names() {
    	return array('audio_format', 'sampling_rate', 'nanogong');
    }

    /**
     * Returns the config form elements for nanogong
     */
    public function get_config_form($mform) {    	
        $audio_format_options = array(
            get_string('audioformatimaadpcm', 'repository_mediacapture'),
            get_string('audioformatspeex', 'repository_mediacapture'),
        );

        $sampling_rate_options = array(
            get_string('samplingratelow', 'repository_mediacapture'),
            get_string('samplingratemedium', 'repository_mediacapture'),
            get_string('samplingratenormal', 'repository_mediacapture'),
            get_string('samplingratehigh', 'repository_mediacapture'),
        );

        $mform->addElement('select', 'audio_format', get_string('audioformat', 'repository_mediacapture'), $audio_format_options);
        $mform->addElement('select', 'sampling_rate', get_string('samplingrate', 'repository_mediacapture'), $sampling_rate_options);
        $mform->addElement('advcheckbox', 'nanogong', get_string('nanogong', 'repository_mediacapture'), null, array('group' => 1));
    }

    /**
     * Returns the view renderer for nanogong
     */
    public function renderer() {
    	global $CFG, $PAGE;
        
        $url = new moodle_url($CFG->wwwroot . 
                '/repository/mediacapture/plugins/nanogong/nanogong.jar');        
        $post_url = urlencode(new moodle_url($CFG->wwwroot . 
                '/repository/mediacapture/plugins/nanogong/record.php'));
        $tmp_dir = urlencode(get_temp_dir());

        // Get recorder settings from the config form
        $sampling_rates = array(
            array(8000, 11025, 22050, 44100),
            array(8000, 16000, 32000, 44100)
        );
        $audio_formats = array('ImaADPCM', 'Speex');

        $audio_format = get_config('audio_format', 'repository_mediacapture');
        $sampling_rate = get_config('sampling_rate', 'repository_mediacapture');

        if (empty($audio_format)) {
            $audio_format = 0;
        }

        if (empty($sampling_rate)) {
            $sampling_rate = 0;
        }

        $sampling_rate = $sampling_rates[$audio_format][$sampling_rate];
        $audio_format = $audio_formats[$audio_format];

        $javanotfound = get_string('javanotfound', 'repository_mediacapture');
        $save = get_string('save', 'repository_mediacapture');

        $callbackurl = new moodle_url('/repository/mediacapture/callback.php');

        // Set the layout elements for the recorder applet
        $recorder = '
                <form method="post" action="'.$callbackurl.'" onsubmit="return submit_audio();">
                    <applet id="audio_recorder" name="audio_recorder" code="gong.NanoGong" width="160" height="40" archive="' . $url . '">
                        <param name="AudioFormat" value="' . $audio_format .'" />
                        <param name="ShowSaveButton" value="false" />
                        <param name="ShowTime" value="true" />
                        <param name="SamplingRate" value="' . $sampling_rate . '" />
                        <p>' . $javanotfound . '</p>
                    </applet><br /><br />
                    <input type="hidden" id="fileloc" name="fileloc" />
                    <input type="hidden" id="tmpdir" name="tmpdir" value="' . $tmp_dir . '" />
                    <input type="hidden" id="posturl" name="posturl" value="' . $post_url . '"/>
                    <input type="text" id="filename" name="filename" value="Untitled"/>
                    <br />
                    <input type="submit" value="'. $save .'" />
                </form>';
        return $recorder;
    }

    /**
     * @return string definitions for the plugin
     */
    public function get_string_defs() {
        return array('audioformat', 'audioformatimaadpcm', 'audioformatspeex',
                'samplingrate', 'samplingratelow', 'samplingratemedium',
                'samplingratenormal', 'samplingratehigh'
                );
    }

    /**
     * @return minumum version required by the plugin
     */
    public function get_min_version() {
        return 1.5;
    }

    /**
     * @return array of supported media.
     */
    public function supported_media() {
        return array('audio');
    }

    /**
     * @return array of supported web technology
     */
    public function supported_technology() {
        return array('java');
    }
}

