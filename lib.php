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

require_once(dirname(__FILE__) . '/locallib.php');

class repository_mediacapture extends repository {

    /**
     * Constructor
     *
     * @param int $repositoryid
     * @param stdClass $context
     * @param array $options
     */
    public function __construct($repositoryid, $context = SITEID, $options = array()) {
        parent::__construct($repositoryid, $context, $options);
    }

    /**
     * @return Admin config type option names
     */
    public static function get_type_option_names() {
        $options = array('pluginname');
        
        $recorders = get_recorder_list();
        
        foreach ($recorders as $recorder) {
            $classname = 'repository_mediacapture_' . $recorder;
            $client = new $classname();
            $options = array_merge($options, $client->get_type_option_names());
        }

        return $options;
    }

    /**
     * Admin settings for the plugin
     *
     * @param object $mform
     * @param string $classname
     */
    public static function type_config_form($mform, $classname = 'repository') {
        parent::type_config_form($mform);

        $recorders = get_recorder_list();
        
        foreach ($recorders as $recorder) {
            $classname = 'repository_mediacapture_' . $recorder;
            $client = new $classname();
            $client->get_config_form($mform);
        }
    }

    /**
     * Plugin doesn't support global search, since we don't have anything to search
     */
    public function global_search() {        
        return false;
    }

    /**
     * @return The callback url for uploading the recorded content
     */
    public function get_callback_url() {
        return new moodle_url('/repository/mediacapture/callback.php',
                 array('repo_id'=>$this->id));
    }

    /**
     * Method to get the repository content.
     *
     * @param string $path current path in the repository
     * @param string $page current page in the repository path
     * @return array structure of listing information
     */
    public function get_listing($path = null, $page = null) {
        $callbackurl = $this->get_callback_url();
        $mimetypesstr = '';
        
        $url = new moodle_url('/repository/mediacapture/renderer.php',
                 array('returnurl' => $callbackurl));
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
     * Returns the supported file types
     *
     * @return array of supported file types and extensions.
     */
    public function supported_filetypes() {
        return array('web_audio', 'web_video');
    }
}
