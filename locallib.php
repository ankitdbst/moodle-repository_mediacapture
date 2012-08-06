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
require_once(dirname(__FILE__) . '/mediacapture_form.php');

class mediacapture_recorder {

    private $installedrecorders;

    /**
     * Populate list of installed recorders
     */
    public function __construct() {
        $this->installedrecorders = $this->installed_recorders();
    }

    /**
     * @param string $media Type of recorder ('audio', 'video')
     * @param object $browserplugins
     */
    public function print_recorder($media, $browserplugins) {
        global $PAGE, $CFG, $OUTPUT;

        $errors = array();
        $recorders = $this->get_installed_recorders($media);

        foreach ($recorders as $recorder) {
            if (get_config('mediacapture', $recorder)) {
                $classname = 'repository_mediacapture_' . $recorder;
                $client = new $classname();
                $version = $client->min_version();
                $types = $client->supported_types();
                $compatible = true;
                foreach ($types as $type) {
                    if ( !(isset($browserplugins->$type) &&
                         $browserplugins->$type >= $version[$type]) ) {
                        $compatible = false;
                        $errors[$type] = array(
                            'installed' => $browserplugins->$type,
                            'required' => $version[$type]
                        );
                    }
                }

                // Check for the compatible plugin-recorder.
                if ($compatible) {
                    $PAGE->requires->css(new moodle_url("$CFG->wwwroot/repository/mediacapture/plugins/$recorder/styles.css"));
                    echo $OUTPUT->header();
                    $jsmodule = array(
                        'name' => 'repository_mediacapture_$recorder',
                        'fullpath' => "/repository/mediacapture/plugins/$recorder/module.js",
                        'requires' => array('event', 'node', 'io', 'base'),
                        'strings' => $this->list_strings($client->string_keys())
                    );
                    $data = array(urlencode($client->post_url()));
                    $PAGE->requires->js_init_call("M.repository_mediacapture_$recorder.init", $data, false, $jsmodule);
                    $formaction = $this->callback_url();
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

        // No recorder selected : display appropriate message.
        echo $OUTPUT->header();
        $options = array(
            'action' => 'nodisplay',
            'errors' => $errors
        );
        $formaction = new moodle_url('/repository/mediacapture/view.php');
        $mform = new mediacapture_form($formaction, $options);
        $mform->display();
        echo $OUTPUT->footer();
    }

    /**
     * Initializes the recorder
     */
    public function init($returnurl) {
        global $PAGE, $CFG, $OUTPUT;
        echo $OUTPUT->header();
        $jsmodule = array(
            'name' => 'repository_mediacapture',
            'fullpath' => '/repository/mediacapture/module.js',
            'requires' => array('event', 'node', 'json')
        );
        $PAGE->requires->js_init_call('M.repository_mediacapture.init', array(), false, $jsmodule);
        $formaction = new moodle_url('/repository/mediacapture/view.php', array('returnurl' => $returnurl));
        $mform = new mediacapture_form($formaction, array('action' => 'init'));
        $mform->display();
        echo $OUTPUT->footer();
    }

    /**
     * View for the plugin
     * @param object $mform
     */
    public function view($mform) {
        $recorders = $this->get_installed_recorders();

        if (count($recorders['audio'])) {
            $mform->addElement('button', 'startaudio', get_string('startaudio', 'repository_mediacapture'));
        }
        if (count($recorders['video'])) {
            $mform->addElement('button', 'startvideo', get_string('startvideo', 'repository_mediacapture'));
        }
        $mform->addElement('hidden', 'type', '');
        $mform->addElement('hidden', 'browserplugins', '');
        $mform->addElement('hidden', 'browserdetect', '');
    }

    /**
     * @return array $recorders array structure containing list of recorders installed.
     */
    public function installed_recorders() {
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
                                for ($i = 0; $i < count($media); $i++) {
                                    array_push($recorders[$media[$i]], $pluginname);
                                }
                            } else {
                                throw new moodle_exception('error'); // Incompatible plugin.
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
     * @return array of recorders corresponding to the media type
     */
    public function get_installed_recorders($media = null) {
        if ($media == null) {
            return $this->installedrecorders;
        } else {
            return $this->installedrecorders[$media];
        }
    }

    /**
     * @return array $strings Array of string definitions to be used by javascript.
     */
    public function list_strings($keys) {
        foreach ($keys as $key) {
            $strings[] = array($key, 'repository_mediacapture');
        }
        return $strings;
    }

    /**
     * @return arrray $files List of the required language files of the sub-plugins
     */
    public function list_files() {
        global $CFG;

        $recorders = $this->get_installed_recorders();
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
     * @return $errors array structure containing the compatibility errors
     */
    public function display_errors($mform, $errors) {
        foreach ($errors as $type => $error) {
            $msg = get_string($type, 'repository_mediacapture'). ' => ' .
                   get_string('required', 'repository_mediacapture')    . ':' . $error['required']  . ' (' .
                   get_string('installed', 'repository_mediacapture')   . ':' . $error['installed'] . ')';
            $mform->addElement('html', $msg);
        }
    }

    /**
     * @return Repository callback url
     */
    public function callback_url() {
        return new moodle_url('/repository/mediacapture/callback.php');
    }
}
