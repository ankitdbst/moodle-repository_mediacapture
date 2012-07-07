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
 * This file returns the appropriate recorder audio/video using
 * ajax request from the plugin interface
 *
 * @package    repository_mediacapture
 * @category   repository
 * @copyright  2012 Ankit Gupta <mailtoankitgupta@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/mediacapture.php');

$type       = required_param('type', PARAM_TEXT);
// List of plugins supported by the client browser
$browser_plugins = new stdClass;

$browser_plugins->os         = optional_param('os', '', PARAM_TEXT);
$browser_plugins->java       = optional_param('java', -1.0, PARAM_FLOAT);
$browser_plugins->flash      = optional_param('flash', -1.0, PARAM_FLOAT);
$browser_plugins->quicktime  = optional_param('quicktime', -1.0, PARAM_FLOAT);

$client = new mediacapture();

switch ($type) {
    case 'show_audio':
        echo $client->print_audio_recorder($browser_plugins);
        break;
    case 'show_video':
        echo $client->print_video_recorder($browser_plugins);
        break; 
    case 'upload_audio':
        $elname = 'repo_upload_audio';
        $tmp_file = $_FILES[$elname]['tmp_name'];
        $tmp_name = $client->get_unused_filename('.wav');
        echo $client->save_temp_file($tmp_file, $tmp_name);
        break;
    case 'upload_video':
        $elname = 'USERFILE';
        $tmp_file = $_FILES[$elname]['tmp_name'];
        $tmp_name = $_FILES[$elname]['name'];
        echo $client->save_temp_file($tmp_file, $tmp_name);
        break;
    case 'check_duration':
        $tmp_file = urldecode(optional_param('tmp_loc', '', PARAM_PATH));       
        if (!file_exists($tmp_file) || !filesize($tmp_file)) {
            echo 'NONE';
        }
        break;
    default:        
}