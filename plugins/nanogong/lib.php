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
     * @return array $options Array of type options used by the recorder
     */
    public static function get_type_option_names() {
    	return array('audio_format', 'sampling_rate', 'nanogong');
    }

    /**
     * Adds the settings configuration needed by the recorder to the plugin
     * @param object $mform
     */
    public function get_config_form($mform) {    	
        $audioformatoptions = array(
            get_string('audioformatimaadpcm', 'repository_mediacapture'),
            get_string('audioformatspeex', 'repository_mediacapture'),
        );

        $samplingrateoptions = array(
            get_string('samplingratelow', 'repository_mediacapture'),
            get_string('samplingratemedium', 'repository_mediacapture'),
            get_string('samplingratenormal', 'repository_mediacapture'),
            get_string('samplingratehigh', 'repository_mediacapture'),
        );

        $mform->addElement('select', 'audio_format', get_string('audioformat', 'repository_mediacapture'), $audioformatoptions);
        $mform->addElement('select', 'sampling_rate', get_string('samplingrate', 'repository_mediacapture'), $samplingrateoptions);
        $mform->addElement('advcheckbox', 'nanogong', get_string('nanogong', 'repository_mediacapture'), null, array('group' => 1));
    }

    /**
     * @param string $callbackurl for the plugin
     * @return string $recorder HTML for the recorder. 
     */
    public function renderer() {
    	global $CFG, $PAGE;
        
        $url = new moodle_url($CFG->wwwroot . 
                '/repository/mediacapture/plugins/nanogong/nanogong.jar');        
        $tmpdir = urlencode(get_temp_dir());

        // Get recorder settings from the config form
        $samplingrates = array(
            array(8000, 11025, 22050, 44100),
            array(8000, 16000, 32000, 44100)
        );
        $audioformats = array('ImaADPCM', 'Speex');

        $audioformat = get_config('audio_format', 'repository_mediacapture');
        $samplingrate = get_config('sampling_rate', 'repository_mediacapture');

        if (empty($audioformat)) {
            $audioformat = 0;
        }
        if (empty($sampling_rate)) {
            $samplingrate = 0;
        }

        $samplingrate = $samplingrates[$audioformat][$samplingrate];
        $audioformat = $audioformats[$audioformat];

        $javanotfound = get_string('javanotfound', 'repository_mediacapture');
        $save = get_string('save', 'repository_mediacapture');

        $recorder = array(
            'html' => '<applet id="audio_recorder" name="audio_recorder" code="gong.NanoGong" width="160" height="40" archive="' . $url . '">
                        <param name="AudioFormat" value="' . $audioformat .'" />
                        <param name="ShowSaveButton" value="false" />
                        <param name="ShowTime" value="true" />
                        <param name="SamplingRate" value="' . $samplingrate . '" />
                        <p>' . $javanotfound . '</p>
                    </applet>',
            'filename' => true,

        );
        return $recorder;
    }

    public function get_ajax_uri() {
        global $CFG;
        $ajaxuri = new moodle_url($CFG->wwwroot . '/repository/mediacapture/plugins/nanogong/record.php');
        return $ajaxuri;
    }

    /**
     * @return string $stringdefs Array of string definitions used by the recorder.
     */
    public function get_string_defs() {
        return array('audioformat', 'audioformatimaadpcm', 'audioformatspeex',
                'samplingrate', 'samplingratelow', 'samplingratemedium',
                'samplingratenormal', 'samplingratehigh'
                );
    }

    /**
     * @return array $version Minimum version of $type required by the recorder.
     */
    public function get_min_version() {
        return array('java' => 1.5);
    }

    /**
     * @return array $media Supported media by the recorder.
     */
    public function supported_media() {
        return array('audio');
    }

    /**
     * @return array $type Supported technology by the recorder.
     */
    public function supported_technology() {
        return array('java');
    }
}

