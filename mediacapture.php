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
    public function save_temp_file($tmp_file, $filename) {
        global $CFG;
        $dir = $CFG->dataroot.'/temp';
        $filename = $dir . '/' . $filename;

        if (!move_uploaded_file($tmp_file, $filename)) {
            return '';
        } else {
            return  $filename;
        }
    }

    /**
     * Get details of the client 
     */
    public function get_client_details() {
        // fetch user-agent string and parse values        
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
        return array('video_quality', 'frame_size');
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
     * Fetch config form for video recorder
     */
    public function get_video_config_form($mform) {
        $video_quality_options = array(
            get_string('video_low', 'repository_mediacapture'),
            get_string('video_normal', 'repository_mediacapture'),
            get_string('video_high', 'repository_mediacapture'),
        );
        $video_frame_size = array(
            get_string('frame_small', 'repository_mediacapture'),
            get_string('frame_large', 'repository_mediacapture'),
        );

        $mform->addElement('select', 'video_quality', get_string('video_quality', 'repository_mediacapture'), $video_quality_options);
        $mform->addElement('select', 'frame_size', get_string('frame_size', 'repository_mediacapture'), $video_frame_size);
    }
    
    /**
     * Initialize the plugin and load the start screen
     */    
    public function init() {
        global $PAGE, $CFG;
        $ajax_uri = urlencode(new moodle_url($CFG->wwwroot.'/repository/mediacapture/lib_ajax.php'));
        $html = '
            <input type="hidden" id="ajax_uri" name="ajax_uri" value="'.$ajax_uri.'" />
            <div class="appletcontainer" id="appletcontainer">
                <input type="button" onclick="return load_recorder(\'show_audio\')" value="Start Audio" />
                <input type="button" onclick="return load_recorder(\'show_video\')" value="Start Video" />
            </div>';
        return $html;
    }

    /**
     * Prints the audio recorder applet in the filepicker
     */
    public function print_audio_recorder() {
        global $CFG, $PAGE;
        
        $url = new moodle_url($CFG->wwwroot.'/repository/mediacapture/assets/audio/applet/nanogong.jar');
        $post_url = urlencode(new moodle_url($CFG->wwwroot . '/repository/mediacapture/lib_ajax.php'));

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

        $javanotfound = get_string('javanotfound', 'repository_mediacapture');
        $save = get_string('save', 'repository_mediacapture');

        // set the layout elements for the recorder applet
        $recorder = '            
                <applet id="audio_recorder" name="audio_recorder" code="gong.NanoGong" width="160" height="40" archive="' . $url . '">
                    <param name="AudioFormat" value="' . $audio_format .'" />
                    <param name="ShowSaveButton" value="false" />
                    <param name="ShowTime" value="true" />
                    <param name="SamplingRate" value="' . $sampling_rate . '" />
                    <p>' . $javanotfound . '</p>
                </applet><br /><br />
                <input type="hidden" id="posturl" name="posturl" value="' . $post_url . '" />
                <input type="hidden" id="fileloc" name="fileloc" />
                <input type="text" class="audio_filename" id="filename" name="filename" onfocus="this.select()" value="*.wav"/><br /><br />
                <input type="button" onclick="submitAudio()" value="'. $save .'" />
                ';
        return $recorder;
    }
       
    /**
     * Prints the video recorder applet in the filepicker
     */
    public function print_video_recorder() {
        global $CFG, $PAGE;
        
        $url = new moodle_url($CFG->wwwroot.'/repository/mediacapture/assets/video/applet/VideoApplet.jar');
        $post_url = new moodle_url($CFG->wwwroot .'/repository/mediacapture/lib_ajax.php');
        $img_dir = new moodle_url($CFG->wwwroot.'/repository/mediacapture/assets/video/img');
        $tmp_loc = urlencode($CFG->dataroot. '/temp');
        $save = get_string('save', 'repository_mediacapture');
        
        // get video preferences for the plugin
        $sampling_rates = array('96,24', '160,32','256,48');
        $video_quality_options = array(
            get_string('video_low', 'repository_mediacapture'),
            get_string('video_normal', 'repository_mediacapture'),
            get_string('video_high', 'repository_mediacapture'),
        );
        $frame_size_options = array('small', 'large');

        $video_quality = get_config('mediacapture', 'video_quality');   
        $frame_size = get_config('mediacapture', 'frame_size');
        
        $sampling_rate = $sampling_rates[$video_quality];
        $video_quality = $video_quality_options[$video_quality];
        $frame_size = $frame_size_options[$frame_size];
        if ($frame_size === "small") {
            $height = 175;
            $width = 195;
        } else {
            $height = 275;
            $width = 325;
        }

        // set the layout elements for the recorder applet
        $recorder = '
                <applet  
                  ID       = "applet"
                  ARCHIVE  = "'.$url.'"
                  codebase = "'.dirname($url).'"
                  code     = "com.vimas.videoapplet.VimasVideoApplet.class"
                  name     = "VimasVideoApplet"
                  width    = "'.$width.'"
                  height   = "'.$height.'"
                  align    = "middle"
                 MAYSCRIPT>
                    <param name = "Registration"        value = "demo">
                    <param name = "LocalizationFile"    value = "localization.xml">
                    <param name = "ServerScript"        value = "'.$post_url.'">
                    <param name = "TimeLimit"           value = "30">
                    <param name = "BlockSize"           value = "10240">
                    <param name = "'.$video_quality.'"  value = "'.$sampling_rate.'">
                    <param name = "FrameSize"           value = "'.$frame_size.'">
                    <param name = "interface"           value = "compact">
                    <param name = "UserPostVariables"   value = "type">
                    <param name = "type"                value = "upload_video">
                </applet>
                <div id="toolbar">
                    <img src="'.$img_dir . '/rec.gif" onclick="record_rp()"/>
                    <img src="'.$img_dir . '/play.gif" onclick="playback_rp()"/>
                    <img src="'.$img_dir . '/pause.gif" onclick="pause_rp()"/>
                    <img src="'.$img_dir . '/stop.gif" onclick="stop_rp()"/>
                    <input type="text" name="Timer" id="Timer" disabled/>
                </div><br />
                <input type="hidden" id="Status" name="Status" value="" />
                <input type="hidden" id="fileloc" name="fileloc" value="'.$tmp_loc.'"/>
                <input type="text" class="video_filename" id="filename" name="filename" onfocus="this.select()" value="*.mp4"/><br /><br />
                <input type="button" onclick="upload_rp();" value="'. $save .'" />
                ';
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
    public function get_unused_filename($type) {
        global $CFG;
        $dir = $CFG->dataroot.'/temp';
        $i = 0;
        do {
            if ($i > 0)
                sleep(1);
            $filename = time() . $type;
            $i++;
        } while ($i < 3 && file_exists($dir . '/' . $filename)); // try 3 times for unique filename

        return $filename;
    }

}
