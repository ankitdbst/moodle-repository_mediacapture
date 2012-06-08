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

require_once(dirname(dirname(__FILE__)).'/../config.php');

class mediacapture {

    /**
     * Constructor
     */
    public function __construct() {
        global $CFG;
    }

    /**
     * Saves the temp file in dataroot->temp dir
     */
    public function save_temp_file($tmp_file) {
        global $CFG;
        $dir = $CFG->dataroot.'/temp';
        $filename = $this->get_unused_filename($dir);

        if (!move_uploaded_file($tmp_file, $filename)) {
            return '';
        } else {
            return  $filename;
        }
    }

    /**
     * Type option names for the audio recorder
     */
    public function get_audio_option_names() {
        return array('audio_format', 'sampling_rate');
    }

    /**
     * Type option names for the video recorder
     */
    public function get_video_option_names() {
        return array();
    }

    /**
     * Fetch config form for audio recorder
     */
    public function get_audio_config_form($mform) {
        $audio_format_options = array(
            get_string('audio_format_imaadpcm', 'repository_mediacapture'),
            get_string('audio_format_speex', 'repository_mediacapture'),
        );
        $sampling_rate_options = array(
            get_string('sampling_rate_low', 'repository_mediacapture'),
            get_string('sampling_rate_medium', 'repository_mediacapture'),
            get_string('sampling_rate_normal', 'repository_mediacapture'),
            get_string('sampling_rate_high', 'repository_mediacapture'),
        );

        $mform->addElement('select', 'audio_format', get_string('audio_format', 'repository_mediacapture'), $audio_format_options);
        $mform->addElement('select', 'sampling_rate', get_string('sampling_rate', 'repository_mediacapture'), $sampling_rate_options);
    }

    /**
     * Prints the audio recorder applet in the filepicker
     */
    public function print_audio_recorder() {
        global $CFG, $PAGE;
        $url = new moodle_url($CFG->wwwroot.'/repository/mediacapture/nanogong.jar');
        $posturl = urlencode(new moodle_url($CFG->wwwroot . '/repository/mediacapture/record.php'));

        // Get recorder settings from the config form
        $sampling_rates = array(
            array(8000, 11025, 22050, 44100),
            array(8000, 16000, 32000, 44100)
        );
        $audio_formats = array('ImaADPCM', 'Speex');

        $audio_format = get_config('mediacapture', 'audio_format');
        $sampling_rate = get_config('mediacapture', 'sampling_rate');

        $sampling_rate = $sampling_rates[$audio_format][$sampling_rate];
        $audio_format = $audio_formats[$audio_format];

        $repo_name = get_string('name', 'repository_mediacapture');
        $javanotfound = get_string('javanotfound', 'repository_mediacapture');
        $save = get_string('save', 'repository_mediacapture');

        // set the layout elements for the recorder applet
        $recorder = '<style>.mdl-left,.fp-saveas,.fp-setauthor,.fp-setlicense,.fp-upload-btn { visibility:hidden; }  .appletcontainer { position:absolute; left:47%; overflow:hidden; text-align:center; }  #audio_filename { width:140px; }</style>';
        $recorder .= '<div class="appletcontainer" id="appletcontainer">
                        <input type="hidden" id="posturl" name="posturl" value="' . $posturl . '" />
                        <input type="hidden" id="audio_loc" name="audio_loc" />
                        <applet id="audio_recorder" name="audio_recorder" code="gong.NanoGong" width="160" height="40" archive="' . $url . '">
                            <param name="AudioFormat" value="' . $audio_format .'" />
                            <param name="ShowSaveButton" value="false" />
                            <param name="ShowTime" value="true" />
                            <param name="SamplingRate" value="' . $sampling_rate . '" />
                            <p>' . $javanotfound . '</p>
                        </applet><br /><br />
                        <input type="text" id="audio_filename" name="audio_filename" onfocus="this.select()" value="untitled"/><br /><br />
                        <input type="button" onclick="submitAudio()" value="'. $save .'" />
                    </div>';
        return $recorder;
    }

    /**
     * @return associative array of string definitions in js
     */
    public function get_string_js() {
        $unexpectedevent = get_string('unexpectedevent', 'repository_mediacapture');
        $appletnotfound = get_string('appletnotfound', 'repository_mediacapture');
        $norecordingfound = get_string('norecordingfound', 'repository_mediacapture');
        $nonamefound = get_string('nonamefound', 'repository_mediacapture');
        $filenotsaved = get_string('filenotsaved', 'repository_mediacapture');

        return array(
            'unexpectedevent' => $unexpectedevent,
            'appletnotfound' => $appletnotfound,
            'norecordingfound' => $norecordingfound,
            'nonamefound' => $nonamefound,
            'filenotsaved' => $filenotsaved);
    }

    /**
     * Creates a unique temp file name for the recording
     */
    private function get_unused_filename($dir) {
        $i = 0;
        do {
            if ($i > 0)
                sleep(1);
            $filename = $dir . '/' . time() . '.wav';
            $i++;
        } while ($i < 3 && file_exists($filename)); // try 3 times for unique filename

        return $filename;
    }

}
