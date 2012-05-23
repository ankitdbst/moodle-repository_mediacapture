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
 * repository_mediacapture class
 * This is a subclass of repository class
 *
 * @package    repository_mediacapture
 * @category   repository
 * @copyright  2012 Ankit Gupta
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class repository_mediacapture extends repository {

    /**
     * Constructor
     *
     * @param int $repositoryid
     * @param stdClass $context
     * @param array $options
     */
    public function __construct($repositoryid, $context = SITEID, $options = array()) {
        global $PAGE, $CFG, $action, $itemid;
        parent::__construct($repositoryid, $context, $options);

        // include record js file
        $PAGE->requires->js( new moodle_url($CFG->wwwroot . '/repository/mediacapture/record.js') );

        // strings for displaying js errors
        $unexpectedevent = get_string('unexpectedevent', 'repository_mediacapture');
        $appletnotfound = get_string('appletnotfound', 'repository_mediacapture');
        $norecordingfound = get_string('norecordingfound', 'repository_mediacapture');
        $nonamefound = get_string('nonamefound', 'repository_mediacapture');
        $filenotsaved = get_string('filenotsaved', 'repository_mediacapture');
        $PAGE->requires->data_for_js('mediacapture', array(
            'unexpectedevent' => $unexpectedevent,
            'appletnotfound' => $appletnotfound,
            'norecordingfound' => $norecordingfound,
            'nonamefound' => $nonamefound,
            'filenotsaved' => $filenotsaved
        ));
    }

    public static function get_type_option_names() {
        return array('audio_format', 'sampling_rate');
    }

    /**
     * Admin settings for the plugin
     * Displays the Audio format and Sampling rate options
     * at which the recording is to be done.
     *
     * @param object $mform
     * @param string $classname
     */
    public static function type_config_form($mform, $classname = 'repository') {
        parent::type_config_form($mform);
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

    public function check_login() {
        // Needs to return false so that the "login" form is displayed (print_login())
        return false;
    }

    public function global_search() {
        // Plugin doesn't support global search, since we don't have anything to search
        return false;
    }

    /**
     * Method to get the repository content.
     *
     * @param string $path current path in the repository
     * @param string $page current page in the repository path
     * @return array structure of listing information
     */
    public function get_listing($path = '', $page = '') {
        return array();
    }

    /**
     * Prints the audio recording applet html
     * in the filepicker instance of the plugin
     */
    public function print_login() {
        global $CFG, $PAGE;

        $recorder = "";
        $url = new moodle_url($CFG->wwwroot.'/repository/mediacapture/nanogong.jar');
        $posturl = urlencode(new moodle_url($CFG->wwwroot . '/repository/mediacapture/record.php'));
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

        $recorder = '
            <div style="position: absolute; top:0;left:0;right:0;bottom:0; background-color:#f2f2f2;">
                <div class="appletcontainer" id="appletcontainer" style="margin:20% auto; text-align:center;">
                    <input type="hidden" id="repo_id" name="repo_id" value="'. $this->id .'" />
                    <input type="hidden" id="posturl" name="posturl" value="'. $posturl .'" />
                    <input type="hidden" id="audio_loc" name="audio_loc" />
                    <applet id="audio_recorder" name="audio_recorder" code="gong.NanoGong" width="120" height="40" archive="'. $url .'">
                        <param name="AudioFormat" value="'. $audio_format .'" />
                        <param name="ShowSaveButton" value="false" />
                        <param name="ShowTime" value="true" />
                        <param name="SamplingRate" value="'. $sampling_rate .'" />
                        <p>'.$javanotfound.'</p>
                    </applet><br /><br />
                    <label for="audio_filename">File Name</label>
                    <input type="text" id="audio_filename" name="audio_filename" />
                    <input type="button" onclick="submitAudio()" value="'. $save .'" />
                </div>
            </div>
            ';
        $ret = array();
        $ret['upload'] = array('label'=>$recorder, 'id'=>'repo-form');
        return $ret;
    }

    /**
     * Process uploaded file
     * @return array|bool
     */
    public function upload($saveas_filename, $maxbytes) {
        global $USER, $CFG;

        $record = new stdClass();
        $record->filearea = 'draft';
        $record->component = 'user';
        $record->filepath = optional_param('savepath', '/', PARAM_PATH);
        $record->itemid   = optional_param('itemid', 0, PARAM_INT);
        $record->license  = optional_param('license', $CFG->sitedefaultlicense, PARAM_TEXT);
        $record->author   = optional_param('author', '', PARAM_TEXT);

        $context = get_context_instance(CONTEXT_USER, $USER->id);
        $filename = required_param('audio_filename', PARAM_FILE);
        $fileloc = required_param('audio_loc', PARAM_PATH);

        $fs = get_file_storage();
        $sm = get_string_manager();

        if ($record->filepath !== '/') {
            $record->filepath = file_correct_filepath($record->filepath);
        }

        $record->filename = $filename;

        if (empty($record->itemid)) {
            $record->itemid = 0;
        }

        $record->contextid = $context->id;
        $record->userid    = $USER->id;
        $record->source    = '';

        if (repository::draftfile_exists($record->itemid, $record->filepath, $record->filename)) {
            $existingfilename = $record->filename;
            $unused_filename = repository::get_unused_filename($record->itemid, $record->filepath, $record->filename);
            $record->filename = $unused_filename;
            $stored_file = $fs->create_file_from_pathname($record, $fileloc);
            $event = array();
            $event['event'] = 'fileexists';
            $event['newfile'] = new stdClass;
            $event['newfile']->filepath = $record->filepath;
            $event['newfile']->filename = $unused_filename;
            $event['newfile']->url = moodle_url::make_draftfile_url($record->itemid, $record->filepath, $unused_filename)->out();

            $event['existingfile'] = new stdClass;
            $event['existingfile']->filepath = $record->filepath;
            $event['existingfile']->filename = $existingfilename;
            $event['existingfile']->url      = moodle_url::make_draftfile_url($record->itemid, $record->filepath, $existingfilename)->out();;
            return $event;
        } else {
            $stored_file = $fs->create_file_from_pathname($record, $fileloc);
            // removes the temporary file from the 'temp' dataroot
            unlink($fileloc);
            return array(
                'url'=>moodle_url::make_draftfile_url($record->itemid, $record->filepath, $record->filename)->out(),
                'id'=>$record->itemid,
                'file'=>$record->filename);
        }
    }

    /**
     * Returns the suported file types
     *
     * @return array of supported file types and extensions.
     */
    public function supported_filetypes() {
        return array('web_audio');
    }

}
