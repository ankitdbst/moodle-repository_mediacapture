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

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');

global $CFG, $PAGE;

require_once("$CFG->libdir/formslib.php");

/**
 * This is a class used to define a mediacapture form for the recorders
 */
class mediacapture_form extends moodleform {
    /** @var string action */
    protected $action;

    /**
     * Definition of the moodleform
     */
    public function definition() {
        global $CFG;

        $mform =& $this->_form;

        $this->action = $this->_customdata['action'];
        $mform->addElement('html', '<div class="mediacontainer" id="mediacontainer">');
        switch ($this->action) {
            case 'init': // initial form for selection from audio/video recorders
                view($mform);
                break;
            case 'display': // displays the form for the recorder selected
                $this->_customdata['recorder']->view($mform);
                $this->add_action_buttons(false, get_string('save', 'repository_mediacapture'));
                break;
            case 'nodisplay': // in case no recorders are available for client
                break;
        }
        $mform->addElement('html', '</div>');
    }
}

/**
 * @param string $media Type of recorder ('audio', 'video')
 * @param object $browserplugins
 */
function print_recorder($media, $browserplugins) {
    global $PAGE, $CFG, $OUTPUT;

    $recorders = installed_recorders();
    $compatible = true;

    foreach($recorders[$media] as $recorder) {
        if (get_config('mediacapture', $recorder)) {
            $classname = 'repository_mediacapture_' . $recorder;
            $client = new $classname();
            $version = $client->min_version();
            $types = $client->supported_types();
            foreach ($types as $type) {
                if ( !(isset($browserplugins->$type) &&
                     $browserplugins->$type >= $version[$type]) ) {
                    $compatible = false;
                    break;
                }
            }
            // check for the compatible plugin-recorder
            if ($compatible) {
                $PAGE->requires->css(new moodle_url("$CFG->wwwroot/repository/mediacapture/plugins/$recorder/styles.css"));
                echo $OUTPUT->header();
                $jsmodule = array(
                    'name' => 'repository_mediacapture_$recorder',
                    'fullpath' => "/repository/mediacapture/plugins/$recorder/module.js",
                    'requires' => array('event', 'node', 'io', 'base'),
                    'strings' => list_strings($client->string_keys())
                );
                $data = array(urlencode($client->post_url()));
                $PAGE->requires->js_init_call("M.repository_mediacapture_$recorder.init", $data, false, $jsmodule);
                $formaction = callback_url();
                $options = array(
                    'action' => 'display',
                    'recorder' => $client
                );
                $mform = new mediacapture_form($formaction, $options);
                $mform->display();
                echo $OUTPUT->footer();
                return;
            }
        }
    }

    // no recorder selected : display appropriate message
    $options = array('action' => 'nodisplay');
    $formaction = new moodle_url('/repository/mediacapture/view.php');
    $mform = new mediacapture_form($formaction, $options);
    $mform->display();
}

/**
 * Initializes the recorder
 */
function init($returnurl) {
    global $PAGE, $CFG, $OUTPUT;
    echo $OUTPUT->header();
    $jsmodule = array(
        'name' => 'repository_mediacapture',
        'fullpath' => '/repository/mediacapture/module.js',
        'requires' => array('event', 'node', 'json'),
        'strings' => list_strings(string_keys())
    );
    $PAGE->requires->js_init_call('M.repository_mediacapture.init', array(), false, $jsmodule);
    $formaction = new moodle_url('/repository/mediacapture/view.php', array('returnurl' => $returnurl));
    // check non-empty list of recorders
    $mform = new mediacapture_form($formaction, array('action' => 'init'));
    $mform->display();
    echo $OUTPUT->footer();
}

/**
 * View for the plugin
 * @param object $mform
 */
function view($mform) {
    $recorders = installed_recorders();

    if (sizeof($recorders['audio'])) {
        $mform->addElement('button', 'startaudio', get_string('startaudio', 'repository_mediacapture'));
    }
    if (sizeof($recorders['video'])) {
        $mform->addElement('button', 'startvideo', get_string('startvideo', 'repository_mediacapture'));
    }
    $mform->addElement('hidden', 'type', '');
    $mform->addElement('hidden', 'browserplugins', '');
    $mform->addElement('hidden', 'browserdetect', '');
}

/**
 * @return array $recorders array structure containing list recorders installed.
 */
function installed_recorders() {
    global $CFG;

    $recorders = array(
        'audio' => array(),
        'video' => array()
        );

    $pluginsdir = "$CFG->dirroot/repository/mediacapture/plugins";
    if ($handle = opendir($pluginsdir)) {
        while (false !== ($pluginname = readdir($handle))) {
            if ($pluginname != "." && $pluginname != "..") {
                if (file_exists("$pluginsdir/$pluginname/lib.php")) {
                    require_once("$pluginsdir/$pluginname/lib.php");
                    $classname = 'repository_mediacapture_' . $pluginname;
                    if (class_exists($classname)) {
                        $client = new $classname();
                        $media = array_intersect($client->supported_media(), array('audio', 'video'));
                        $types = array_intersect($client->supported_types(), array('html5', 'flash', 'java'));
                        if ($media && $types) {
                            for ($i = 0; $i < sizeof($media); $i++) {
                                array_push($recorders[$media[$i]], $pluginname);
                            }
                        } else {
                            throw new moodle_exception('error'); // incompatible plugin
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
 * @return array $keys List of all string keys for the plugin
 */
function string_keys() {
    return array(
        'unexpectedevent', 'appletnotfound', 'norecordingfound',
        'nonamefound', 'filenotsaved'
    );
}

/**
 * @return array $strings Array of string definitions to be used by javascript.
 */
function list_strings($keys) {
    $strings = array();

    foreach ($keys as $key) {
        $strings[] = array($key, 'repository_mediacapture');
    }

    return $strings;
}

/**
 * @return arrray $files List of the required language files of the sub-plugins
 */
function list_files() {
    global $CFG;

    $recorders = installed_recorders()
;
    $pluginsdir = "$CFG->dirroot/repository/mediacapture/plugins";
    foreach (array_merge($recorders['audio'], $recorders['video']) as $recorder) {
        $file = "$pluginsdir/$recorder/lang/en/repository_mediacapture_$recorder.php";
        if (file_exists($file)) {
            $files[] = $file;
        }
    }

    return $files;
}

/**
 * @return string $path Path of the temp directory
 */
function temp_dir() {
    global $USER;
    return make_temp_directory('repository/medicapture/' . $USER->id);
}

/**
 * @return Repository callback url
 */
function callback_url() {
    return new moodle_url('/repository/mediacapture/callback.php');
}