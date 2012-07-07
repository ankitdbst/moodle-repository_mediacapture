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
     * Loads the required js files and populates lang strings
     */
    public function include_js() {
        global $PAGE, $CFG;
        $PAGE->requires->js(new moodle_url($CFG->wwwroot . '/repository/mediacapture/assets/prerequisites.js'));
        $PAGE->requires->js(new moodle_url($CFG->wwwroot . '/repository/mediacapture/script.js'));
        $PAGE->requires->data_for_js('mediacapture', $this->get_string_js());
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
     * Type option names for the audio recorder
     */
    public function get_audio_option_names() {
        return array('audio_format', 'sampling_rate');
    }

    /**
     * Type option names for the video recorder
     */
    public function get_video_option_names() {
        return array('video_quality','rtmp_server','max_video_length');
    }

    /**
     * Type option names for the recorders
     */
    public function get_recorder_names() {
        return array('flash_video_recorder', 'flash_audio_recorder',
                    'java_video_recorder', 'java_audio_recorder');
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

        $mform->addElement('select', 'video_quality', get_string('video_quality', 'repository_mediacapture'), $video_quality_options);

        $mform->addElement('text', 'rtmp_server', get_string('rtmpserver', 'repository_mediacapture'), 'maxlength="100" size="25" ');
        $mform->setType('rtmp_server', PARAM_NOTAGS);
        $mform->setDefault('rtmp_server', 'rtmp://127.0.0.1');
    }

    /**
     * Fetch config form for available recorders
     */
    public function get_recorder_config_form($mform) {
        $mform->addElement('advcheckbox', 'flash_video_recorder', get_string('flash_video_recorder', 'repository_mediacapture'), '  (requires Red5)', array('group' => 1));        
        $mform->addElement('advcheckbox', 'flash_audio_recorder', get_string('flash_audio_recorder', 'repository_mediacapture'), null, array('group' => 1));
        $mform->addElement('advcheckbox', 'java_video_recorder', get_string('java_video_recorder', 'repository_mediacapture'), '  (Windows only)', array('group' => 1));
        $mform->addElement('advcheckbox', 'java_audio_recorder', get_string('java_audio_recorder', 'repository_mediacapture'), null, array('group' => 1));
        // set defaults
        $mform->setDefault('flash_video_recorder', 0);
        $mform->setDefault('flash_audio_recorder', 1);
        $mform->setDefault('java_video_recorder', 0);
        $mform->setDefault('java_audio_recorder', 1);
    }
    
    /**
     * Initialize the plugin and load the start screen
     */    
    public function init() {
        global $PAGE, $CFG;

        // Include recorder JS
        $this->include_js();

        // Get list of available recorders
        unset($recorder);
        $recorder = new stdClass();
        
        $recorder->flash = new stdClass();
        $recorder->flash->audio = get_config('mediacapture', 'flash_audio_recorder');
        $recorder->flash->video = get_config('mediacapture', 'flash_video_recorder');

        $recorder->java = new stdClass();
        $recorder->java->audio = get_config('mediacapture', 'java_audio_recorder');
        $recorder->java->video = get_config('mediacapture', 'java_video_recorder');

        $ajax_uri = urlencode(new moodle_url($CFG->wwwroot.'/repository/mediacapture/lib_ajax.php'));

        $html = '<input type="hidden" id="ajax_uri" name="ajax_uri" value="'.$ajax_uri.'" /><div class="appletcontainer" id="appletcontainer">';
        if ($recorder->flash->audio or $recorder->java->audio) {
            $html .= '<input type="button" onclick="return load_recorder(\'show_audio\')" value="Start Audio" /> ';
        }
        if ($recorder->flash->video or $recorder->java->video) {
            $html .= '<input type="button" onclick="return load_recorder(\'show_video\')" value="Start Video" />';
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * Prints the appropriate audio recorder by checking
     * client's browser plugins else outputs error messages.
     */
    public function print_audio_recorder($plugins) {
        $error = array();

        unset($recorder);
        $recorder = new stdClass();
        
        $recorder->flash = new stdClass();
        $recorder->flash->audio = get_config('mediacapture', 'flash_audio_recorder');

        $recorder->java = new stdClass();
        $recorder->java->audio = get_config('mediacapture', 'java_audio_recorder');

        if ($plugins->flash >= 9 && $recorder->flash->audio) {
            return $this->print_flash_audio_recorder();
        } else if ($plugins->java >= 1.5 && $recorder->java->audio) {            
            return $this->print_java_audio_recorder();
        } else {
            array_push($error, get_string('flashnotfound', 'repository_mediacapture'));
            array_push($error, get_string('javanotfound', 'repository_mediacapture'));    
            return $this->print_error($error);
        }
    }

    /**
     * Prints the appropriate video recorder by checking
     * client's browser plugins else outputs error messages.
     */
    public function print_video_recorder($plugins) {
        $errors = array();

        unset($recorder);
        $recorder = new stdClass();

        $recorder->flash = new stdClass();
        $recorder->flash->video = get_config('mediacapture', 'flash_video_recorder');

        $recorder->java = new stdClass();
        $recorder->java->video = get_config('mediacapture', 'java_video_recorder');

        if ($plugins->flash >= 9 && $recorder->flash->video) {
            return $this->print_flash_video_recorder();
        } else if ($plugins->java >= 1.5 && $plugins->quicktime >= 1.0 && 
                    $plugins->os !== 'Linux' && $recorder->java->video) {            
            return $this->print_java_video_recorder();
        } else {
            array_push($errors, get_string('flashnotfound', 'repository_mediacapture'));
            if ($plugins->java < 1.5) {
                array_push($errors, get_string('javanotfound', 'repository_mediacapture'));    
            } elseif ($plugins->quicktime < 1.0) {
                array_push($errors, get_string('quicktimenotfound', 'repository_mediacapture'));    
            }
            return $this->print_error($errors);
        }
    }

    /**
     * Prints the audio recorder applet in the filepicker
     */
    public function print_java_audio_recorder() {
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

        $callbackurl = new moodle_url('/repository/mediacapture/callback.php');

        // Set the layout elements for the recorder applet
        $recorder = '
                <form method="post" action="'.$callbackurl.'" onsubmit="return submit_java_audio();">
                    <applet id="audio_recorder" name="audio_recorder" code="gong.NanoGong" width="160" height="40" archive="' . $url . '">
                        <param name="AudioFormat" value="' . $audio_format .'" />
                        <param name="ShowSaveButton" value="false" />
                        <param name="ShowTime" value="true" />
                        <param name="SamplingRate" value="' . $sampling_rate . '" />
                        <p>' . $javanotfound . '</p>
                    </applet><br /><br />
                    <input type="hidden" id="posturl" name="posturl" value="' . $post_url . '" />
                    <input type="hidden" id="fileloc" name="fileloc" />
                    <input type="text" id="filename" name="filename" onfocus="this.select()" value="*.wav"/>
                    <br />
                    <input type="submit" value="'. $save .'" />
                </form>';
        return $recorder;
    }

    /**
     * Prints the flash audio recorder in the filepicker
     * Uses the mp3 recorder by Paul Nicholls which doesn't require red5
     */
    public function print_flash_audio_recorder() {    
        global $CFG, $PAGE;

        $url = new moodle_url($CFG->wwwroot.'/repository/mediacapture/assets/audio/flash/recorder.swf?gateway=form');
        $tmp_loc = urlencode($CFG->dataroot);
        $js_callback = urlencode("(function(a, b){d=document;d.g=d.getElementById;fn=d.g('filename');fn.value=a;fd=d.g('filedata');fd.value=b;d.forms[0].submit();})");
        $callbackurl = new moodle_url('/repository/mediacapture/callback.php');
        $flashvars = "&callback={$js_callback}&filename=new_recording";

        $recorder = '
                <form method="post" action="'.$callbackurl.'">
                    <object id="onlineaudiorecorder" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="215" height="138">
                        <param name="movie" value="'.$url.$flashvars.'" />
                        <param name="wmode" value="transparent" />
                        <!--[if !IE]>-->
                        <object type="application/x-shockwave-flash" data="'.$url.$flashvars.'" width="215" height="138">
                        <!--<![endif]-->
                        <div>
                            <p>
                                <a href="http://www.adobe.com/go/getflashplayer"><img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" />
                                </a>
                            </p>
                        </div>
                        <!--[if !IE]>-->
                        </object>
                        <!--<![endif]-->
                    </object>
                    <input type="hidden" name="filename" id="filename" />
                    <input type="hidden" name="fileloc" id="fileloc" value="'.$tmp_loc.'" />
                    <textarea name="filedata" id="filedata" style="display:none;"></textarea>
                </form>';
                
        return $recorder;
    }
       
    /**
     * Prints the java video recorder applet in the filepicker
     */
    public function print_java_video_recorder() {
        global $CFG, $PAGE;
        
        $url = new moodle_url($CFG->wwwroot.'/repository/mediacapture/assets/video/applet/VideoApplet.jar');
        $post_url = new moodle_url($CFG->wwwroot .'/repository/mediacapture/lib_ajax.php');
        $callbackurl = new moodle_url('/repository/mediacapture/callback.php');
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
        $video_quality = get_config('mediacapture', 'video_quality');   
        
        $sampling_rate = $sampling_rates[$video_quality];
        $video_quality = $video_quality_options[$video_quality];

        // set the layout elements for the recorder applet
        $recorder = '
            <form method="post" action="'.$callbackurl.'" onsubmit="return upload_rp();"> 
                <applet  
                  ID       = "applet"
                  ARCHIVE  = "'.$url.'"
                  codebase = "'.dirname($url).'"
                  code     = "com.vimas.videoapplet.VimasVideoApplet.class"
                  name     = "VimasVideoApplet"
                  width    = "325"
                  height   = "240"
                  align    = "middle"
                 MAYSCRIPT>
                    <param name = "Registration"        value = "demo">
                    <param name = "LocalizationFile"    value = "localization.xml">
                    <param name = "ServerScript"        value = "'.$post_url.'">
                    <param name = "TimeLimit"           value = "30">
                    <param name = "BlockSize"           value = "10240">
                    <param name = "'.$video_quality.'"  value = "'.$sampling_rate.'">
                    <param name = "FrameSize"           value = "large">
                    <param name = "interface"           value = "compact">
                    <param name = "UserPostVariables"   value = "type">
                    <param name = "type"                value = "upload_video">
                </applet>
                <div id="toolbar" class="clearfix">
                    <button id="rec" onclick="return  record_rp()">
                        <img src="'.$img_dir.'/rec.gif" />
                    </button>
                    <button id="play" onclick="return playback_rp()" disabled>
                        <img src="'.$img_dir.'/play.gif" />
                    </button>
                    <button id="pause" onclick="return pause_rp()" disabled>
                        <img src="'.$img_dir.'/pause.gif" />    
                    </button>
                    <button id="stop" onclick="return stop_rp()" disabled>
                        <img src="'.$img_dir.'/stop.gif" />
                    </button>
                    <input type="text" name="Timer" id="Timer" disabled/>
                </div>
                <input type="hidden" id="Status" name="Status" value="" />
                <input type="hidden" id="fileloc" name="fileloc" value="' . $tmp_loc . '"/>
                <input type="text" id="filename" name="filename" onfocus="this.select()" value="*.mp4"/><br />
                <input type="submit" value="'. $save .'" />
            </form>';
        return $recorder;
    }

    /**
     * Prints the flash/red5 video recorder in the filepicker
     */
    public function print_flash_video_recorder() {
        global $CFG, $PAGE;

        $url = new moodle_url($CFG->wwwroot.'/repository/mediacapture/assets/video/flash/red5recorder.swf');
        $rtmp_server = get_config('mediacapture', 'rtmp_server');
        $rtmp_server .= '/red5recorder/';
        $max_length = '120';
        
        $tmp_loc = $CFG->dataroot. '/streams';
        $tmp_name = $this->get_unused_filename('.flv', $tmp_loc);
        $tmp_loc = urlencode($tmp_loc.'/'.$tmp_name);

        $flashvars = '?server='.$rtmp_server.'&maxLength='.$max_length.'&fileName='.basename($tmp_name, '.flv');        
        $callbackurl = new moodle_url('/repository/mediacapture/callback.php');
        $post_url = new moodle_url($CFG->wwwroot .'/repository/mediacapture/lib_ajax.php');
        $save = get_string('save', 'repository_mediacapture');

        $recorder = '
                <form method="post" action="'.$callbackurl.'" onsubmit="return submit_flash_video();"> 
                    <object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"
                        id="red5recorder" width="100%" height="100%"
                        codebase="http://fpdownload.macromedia.com/get/flashplayer/current/swflash.cab">
                        <param name="movie" value="'.$url.'" />
                        <param name="quality" value="high" />
                        <param name="bgcolor" value="#869ca7" />
                        <param name="allowScriptAccess" value="sameDomain" />
                        <embed src="'.$url.$flashvars.'" quality="high" bgcolor="#869ca7"
                            width="320px" height="240px" name="red5recorder" align="middle"
                            play="true"
                            loop="false"
                            quality="high"
                            allowScriptAccess="sameDomain"
                            type="application/x-shockwave-flash"
                            pluginspage="http://www.adobe.com/go/getflashplayer">
                        </embed>
                    </object><br /><br />
                    <input type="hidden" id="fileloc" name="fileloc" value="' . $tmp_loc . '" />
                    <input type="hidden" id="posturl" name="posturl" value="' . $post_url . '" />
                    <input type="text" id="filename" name="filename" onfocus="this.select()" value="*.flv"/><br /><br />
                    <input type="submit" value="'.$save .'" />
                </form>';
        return $recorder;
    }

    /**
     * Outputs error messages for the user to correct
     */
    public function print_error($errors) {
        $html = '<p>Please correct the following errors and reload</p>
                <ul>';
        foreach($errors as $error) {
            $html .= '<li>'.$error.'</li>';
        }
        $html .= '</ul>';
        return $html;
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
    public function get_unused_filename($type, $dir = '') {
        global $CFG;
        if (empty($dir)) {
            $dir = $CFG->dataroot.'/temp';
        }
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
