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
 * @copyright  2012 Ankit Gupta <mailtoankitgupta@gmail.com>
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
    }

    public static function get_type_option_names() {
        $client = new mediacapture();
        $audio_option = $client->get_audio_option_names();
        $video_option = $client->get_video_option_names();
        $recorders = $client->get_recorder_names();
        return array_merge($audio_option, $video_option, $recorders);
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
        $client->get_video_config_form($mform);
        $client->get_recorder_config_form($mform);
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
    public function get_listing($path = null, $page = null) {
        global $COURSE;
        $callbackurl = new moodle_url('/repository/mediacapture/callback.php', array('repo_id'=>$this->id));
        $mimetypesstr = '';
        
        $url = new moodle_url('/repository/mediacapture/view.php', array('returnurl' => $callbackurl));
        $list = array();
        $list['object'] = array();
        $list['object']['type'] = 'text/html';
        $list['object']['src'] = $url->out(false);
        $list['nologin']  = true;
        $list['nosearch'] = true;
        $list['norefresh'] = true;
        return $list;
    }

    /**
     * Upload the recorded file
     *
     * @param string $url the url of file
     * @param string $filename save location
     * @return string the location of the file
     */
    public function get_file($url, $filename = '') {
        global $USER;
        $path = $this->prepare_file($filename);
        $url1 = unserialize(base64_decode($url));        
        $filedata = base64_decode($url1->filedata);
        // Check whenever raw data is not passed
        if (empty($filedata)) {
            $filedata = file_get_contents($url1->url);
        }
        file_put_contents($path, $filedata);
        // Delete the temp file only when raw data is not passed
        if (empty($filedata)) {
            unlink($url1->url);
        }        

        return array('path'=>$path, 'url'=>$url);
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
