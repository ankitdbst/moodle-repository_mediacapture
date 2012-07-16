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
 * @package    repository_mediacapture
 * @category   repository
 * @copyright  2012 Ankit Gupta <mailtoankitgupta@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(__FILE__)).'/../config.php');

$browserplugins = new stdClass();

$browserplugins->os = optional_param('os', '', PARAM_TEXT);
$browserplugins->java = optional_param('java', -1.0, PARAM_FLOAT);
$browserplugins->flash = optional_param('flash', -1.0, PARAM_FLOAT);
$browserplugins->quicktime = optional_param('quicktime', -1.0, PARAM_FLOAT);

$media = optional_param('media', '', PARAM_TEXT);

if (!empty($media)) {
    echo print_recorder($media, $browserplugins);
}

/**
 * @param string $media Type of recorder ('audio', 'video')
 * @param object $browserplugins List of client browser plugins along with version installed.
 * @return string $html HTML for the recorder
 */
function print_recorder($media, $browserplugins) {
    $recorders = get_installed_recorders();

    foreach (supported_type() as $type) {
        $list = $recorders->$media->$type;
        foreach ($list as $recorder) {
            if (get_config('mediacapture', $recorder)) {
                $classname = 'repository_mediacapture_' . $recorder;
                $client = new $classname();
                if ($browserplugins->$type >= $client->get_min_version()) {
                    return $client->renderer(); // return the first recorder in the priority list
                }
            }
        }
    }
}

/**
 * Initializes the recorder with javascript files of sub-plugins
 */
function init() {
    global $PAGE, $CFG;

    // Include general scripts and language strings used by the plugin
    $PAGE->requires->js(new moodle_url($CFG->wwwroot .
                         '/repository/mediacapture/script.js'));

    $recorders = get_recorder_list();
    $stringdefs = get_string_defs();

    foreach ($recorders as $recorder) {
        $classname = 'repository_mediacapture_' . $recorder;
        $client = new $classname();
        if (file_exists($CFG->dirroot . '/repository/mediacapture/plugins/' . $recorder . '/script.js')) {
            $PAGE->requires->js(new moodle_url($CFG->wwwroot .
                 '/repository/mediacapture/plugins/' . $recorder . '/script.js'));
        }                
        array_merge($stringdefs, $client->get_string_defs());
    }

    $PAGE->requires->data_for_js('mediacapture', get_string_js($stringdefs));        

    $ajax_uri = urlencode(new moodle_url($CFG->wwwroot . '/repository/mediacapture/locallib.php'));

    $html = '<input type="hidden" id="ajax_uri" name="ajax_uri" value="'.$ajax_uri.'" />
                <div class="mediacontainer" id="mediacontainer">';
    
    $list = array();
    foreach (supported_media() as $media) {
        $list[$media] = false;
    }

    $recorders = get_installed_recorders();
    foreach (supported_media() as $media) {
        foreach (supported_type() as $type) {
            if (get_config('mediacapture', $recorders->$media->$type)) {
                $list[$media] = true;
                break;
            }
        }
    }

    if ($list['audio']) {
        $html .= '<input type="button" onclick="return load_recorder(\'audio\')" value="Start Audio" /> ';
    }

    if ($list['video']) {
        $html .= '<input type="button" onclick="return load_recorder(\'video\')" value="Start Video" />';
    }

    $html .= '</div>';

    return $html;
}

/**
 * Include the css files for the sub-plugins
 */
function require_css() {
    global $PAGE, $CFG;
    $recorders = get_recorder_list();

    $pluginsdir = $CFG->dirroot . '/repository/mediacapture/plugins';
    foreach ($recorders as $recorder) {
       if (file_exists($CFG->dirroot . '/repository/mediacapture/plugins/' . $recorder . '/styles.css')) {
            $PAGE->requires->css(new moodle_url($CFG->wwwroot .
                 '/repository/mediacapture/plugins/' . $recorder . '/styles.css'));
        }        
    }
}

/**
 * $return array $stringdefs List of all the general string definitions for the plugin 
 */
function get_string_defs() {
    return array('unexpectedevent', 'appletnotfound', 'norecordingfound',
            'nonamefound', 'filenotsaved');
}

/**
 * @return arrray $files List of the required language files of the sub-plugins
 */
function init_lang() {
    global $CFG;

    $files = array();

    $recorders = get_recorder_list();

    $pluginsdir = $CFG->dirroot . '/repository/mediacapture/plugins';
    foreach ($recorders as $recorder) {
        $lang = $pluginsdir . '/' . $recorder 
            . '/lang/en/repository_mediacapture_' . $recorder . '.php';
        if (file_exists($lang)) {
            $files[] = $lang;
        }        
    }

    return $files;
}

/**
 * @return object $recorders Object structure containing media/type/name of recorders installed.
 */
function get_installed_recorders() {
    global $CFG;
    
    $recorders = new stdClass();
    foreach (supported_media() as $media) {
        $recorders->$media = new stdClass();
        foreach (supported_type() as $type) {
            $recorders->$media->$type = array();
        }
    }

    $pluginsdir = $CFG->dirroot . '/repository/mediacapture/plugins';
    if ($handle = opendir($pluginsdir)) {
        while (false !== ($pluginname = readdir($handle))) {
            if ($pluginname != "." && $pluginname != "..") {
                $lib = $pluginsdir . '/' . $pluginname . '/lib.php';
                if (file_exists($lib)) {
                    require_once($lib);
                    $classname = 'repository_mediacapture_' . $pluginname;
                    if (class_exists($classname)) {
                        $client = new $classname();
                        $media = $client->supported_media();
                        $types = $client->supported_technology();
                        foreach ($media as $medium) {
                            foreach ($types as $type) {
                                if (in_array($medium, supported_media()) && 
                                    in_array($type, supported_type())) {
                                    array_push($recorders->$medium->$type, $pluginname);
                                }
                            }
                        }
                    } 
                } else {
                    throw new moodle_exception('error');
                }
            }
        }
        closedir($handle);
    }

    return $recorders;
}   

/**
 * @return array $list Array of recorders currently installed
 */
function get_recorder_list() {
    $recorders = get_installed_recorders();

    $list = array();
    foreach (supported_media() as $media) {
        foreach (supported_type() as $type) {
            foreach ($recorders->$media->$type as $recorder) {
                $list[] = $recorder;
            }
        }
    }

    return $list;
}

/**
 * @return array $jsstring Array of string definitions to be used by javascript.
 */
function get_string_js($stringdefs) {        
    $jsstring = array();
    
    foreach ($stringdefs as $str) {
        $jsstring[$str] = get_string($str, 'repository_mediacapture');
    }

    return $jsstring;
}

/**
 * @return string $path Path of the temp directory to store the local recorded media before uploading.
 */
function get_temp_dir() {
    global $USER;
    
    return make_temp_directory('repository/medicapture/' . $USER->id);
}

/**
 * @return array $media Supported media allowed.
 */
function supported_media() {
    return array('audio', 'video');
}

/**
 * @return array $type Supported type allowed.
 */
function supported_type() {
    return array('html5', 'flash', 'java');
}
