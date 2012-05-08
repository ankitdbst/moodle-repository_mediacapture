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
        parent::__construct($repositoryid, $context, $options);
    }

    /**
     * Get file listing
     *
     * @param string $path
     * @param string $page
     */
    public function get_listing($path = '', $page = '') {
        $list = array();
        $list['list'] = array();
        // the management interface url
        $list['manage'] = false;
        // dynamically loading
        $list['dynload'] = true;
        // the current path of this list.
        $list['path'] = array(
            array('name'=>'root', 'path'=>''),
            array('name'=>'sub_dir', 'path'=>'/sub_dir')
            );
        // set to true, the login link will be removed
        $list['nologin'] = false;
        // set to true, the search button will be removed
        $list['nosearch'] = false;
        // a file in listing
        $list['list'][] = array('title'=>'file.txt',
            'size'=>'1kb',
            'date'=>'2008.1.12',
            'thumbnail'=>'http://localhost/xx.png',
            'thumbnail_wodth'=>32,
            // plugin-dependent unique path to the file (id, url, path, etc.)
            'source'=>'',
            // the accessible url of the file
            'url'=>''
        );
        // a folder in listing
        $list['list'][] = array('title'=>'foler',
            'size'=>'0',
            'date'=>'2008.1.12',
            'childre'=>array(),
            'thumbnail'=>'http://localhost/xx.png',
        );
        return $list;
    }

    /**
     * Check if user logged in
     */
    public function check_login() {
        global $SESSION;
        //if (!empty($SESSION->logged)) {
            //return true;
        //} else {
            //return false;
        //}
        return true;
    }

    /**
     * if check_login returns false,
     * this function will be called to print a login form.
     */
    public function print_login() {
        $user_field->label = get_string('username').': ';
        $user_field->id    = 'demo_username';
        $user_field->type  = 'text';
        $user_field->name  = 'demousername';
        $user_field->value = '';

        $passwd_field->label = get_string('password').': ';
        $passwd_field->id    = 'demo_password';
        $passwd_field->type  = 'password';
        $passwd_field->name  = 'demopassword';

        $form = array();
        $form['login'] = array($user_field, $passwd_field);
        return $form;
    }

    /**
     * Search in external repository
     *
     * @param string $text
     */
    public function search($text) {
        $search_result = array();
        // search result listing's format is the same as
        // file listing
        $search_result['list'] = array();
        return $search_result;
    }
    /**
     * move file to local moodle
     * the default implementation will download the file by $url using curl,
     * that file will be saved as $file_name.
     *
     * @param string $url
     * @param string $filename
     */
    /**
    public function get_file($url, $file_name = '') {
    }
    */

    /**
     * when logout button on file picker is clicked, this function will be
     * called.
     */
    public function logout() {
        global $SESSION;
        unset($SESSION->logged);
        return true;
    }

    /**
     * this function must be static
     *
     * @return array
     */
    public static function get_instance_option_names() {
        return array('account');
    }

    /**
     * Instance config form
     */
    public function instance_config_form(&$mform) {
        $mform->addElement('text', 'account', get_string('account', 'repository_mediacapture'), array('value'=>'','size' => '40'));
    }

    /**
     * Type option names
     *
     * @return array
     */
    public static function get_type_option_names() {
        return array('api_key');
    }

    /**
     * Type config form
     */
    public function type_config_form(&$mform) {
        $mform->addElement('text', 'api_key', get_string('api_key', 'repository_mediacapture'), array('value'=>'','size' => '40'));
    }
    /**
     * will be called when installing a new plugin in admin panel
     *
     * @return bool
     */
    public static function plugin_init() {
        $result = true;
        // do nothing
        return $result;
    }

    /**
     * Supports file linking and copying
     *
     * @return int
     */
    public function supported_returntypes() {
        // From moodle 2.3, we support file reference
        // see moodle docs for more information
        //return FILE_INTERNAL | FILE_EXTERNAL | FILE_REFERENCE;
        return FILE_INTERNAL | FILE_EXTERNAL;
    }
}
