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
require_once(dirname(__FILE__) . '/mediacapture.php');

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
        $this->include_js();
    }

    public static function get_type_option_names() {
        $client = new mediacapture();
        $audio_option = $client->get_audio_option_names();
        $video_option = $client->get_video_option_names();
        return array_merge($audio_option, $video_option);
    }

    /**
     * Admin settings for the media capture plugin
     *
     * @param object $mform
     * @param string $classname
     */
    public static function type_config_form($mform, $classname = 'repository') {
        parent::type_config_form($mform);
        $client = new mediacapture();
        $client->get_audio_config_form($mform);
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
     * Loads the required js files and populates lang strings
     */
    private function include_js() {
        global $PAGE, $CFG;
        $PAGE->requires->js( new moodle_url($CFG->wwwroot . '/repository/mediacapture/record.js') );
        $client = new mediacapture();
        $string_js = $client->get_string_js();
        $PAGE->requires->data_for_js('mediacapture', $string_js);
    }

    /**
     * Prints the appropriate recorder html in the filepicker
     */
    public function print_login() {
        global $CFG, $PAGE;

        $client = new mediacapture();
        $recorder = $client->print_audio_recorder();
        $ret = array();
        $ret['upload'] = array('label'=>$recorder, 'id'=>'repo-form');
        return $ret;
    }

    /**
     * Process uploaded file
     * @return array|bool
     */
    public function upload($saveas_filename, $maxbytes) {
        global $CFG;

        $types = optional_param_array('accepted_types', '*', PARAM_RAW);
        $savepath = optional_param('savepath', '/', PARAM_PATH);
        $itemid = optional_param('itemid', 0, PARAM_INT);
        $license = optional_param('license', $CFG->sitedefaultlicense, PARAM_TEXT);
        $author = optional_param('author', '', PARAM_TEXT);

        $filename = required_param('audio_filename', PARAM_FILE);
        $fileloc = required_param('audio_loc', PARAM_PATH);

        return $this->process_upload($saveas_filename, $maxbytes, $types, $savepath, $itemid, $license, $author, $filename, $fileloc);
    }

    /**
     * Do the actual processing of the uploaded file
     * @param string $saveas_filename name to give to the file
     * @param int $maxbytes maximum file size
     * @param mixed $types optional array of file extensions that are allowed or '*' for all
     * @param string $savepath optional path to save the file to
     * @param int $itemid optional the ID for this item within the file area
     * @param string $license optional the license to use for this file
     * @param string $author optional the name of the author of this file
     * @param string $filename required the name of the recording
     * @param string $fileloc required the tmp location of the recorded stream
     * @return object containing details of the file uploaded
     */
    public function process_upload($saveas_filename, $maxbytes, $types = '*', $savepath = '/', $itemid = 0, $license = null, $author = '', $filename, $fileloc) {
        global $USER, $CFG;

        $record = new stdClass();
        $record->filearea = 'draft';
        $record->component = 'user';
        $record->filepath = $savepath;
        $record->itemid = $itemid;
        $record->license = $license;
        $record->author = $author;

        $context = get_context_instance(CONTEXT_USER, $USER->id);

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
        $record->userid = $USER->id;
        $record->source = '';

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
        return array('web_audio', 'web_video');
    }

}
