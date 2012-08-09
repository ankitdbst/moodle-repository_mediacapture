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
 * @copyright  2012 Ankit Gupta <mailtoankitgupta@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/recorder_form.php');

/**
 * Mediacapture class
 * This will take care of loading appropriate recorder
 *
 * @package    repository_mediacapture
 * @copyright  2012 Ankit Gupta <mailtoankitgupta@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mediacapture {

    /**
     * Keep list of all installed recorders
     * @var array
     */
    private static $installedrecorders;

    /**
     * @param string $media Type of recorder ('audio', 'video')
     * @param object $browserplugins
     */
    public function print_recorder($media, $browserplugins, $recorderoptions) {
        global $PAGE, $CFG, $OUTPUT;

        $errors = array();
        $recorders = self::get_installed_recorders($media);

        foreach ($recorders as $recorder) {
            if (isset($recorderoptions[$recorder]) && $recorderoptions[$recorder]) {
                $classname = 'repository_mediacapture_' . $recorder;
                $client = new $classname();
                $version = $client->min_version();
                $types = $client->supported_types();
                $compatible = true;
                foreach ($types as $type) {
                    if (!(isset($browserplugins->$type) && ($browserplugins->$type >= $version[$type]))) {
                        $compatible = false;
                        $errors[$type] = array(
                            'installed' => $browserplugins->$type,
                            'required' => $version[$type]
                            );
                    }
                }

                // Check for the compatible plugin-recorder.
                if ($compatible) {
                    $PAGE->requires->css(new moodle_url("$CFG->wwwroot/repository/mediacapture/recorders/$recorder/styles.css"));
                    echo $OUTPUT->header();
                    $jsmodule = array(
                        'name' => 'repository_mediacapture_$recorder',
                        'fullpath' => "/repository/mediacapture/recorders/$recorder/module.js",
                        'requires' => array('event', 'node', 'io', 'base'),
                        'strings' => $this->list_strings($client->string_keys())
                    );
                    $data = array(urlencode($client->post_url()));
                    $PAGE->requires->js_init_call("M.repository_mediacapture_$recorder.init", $data, false, $jsmodule);
                    $formaction = $this->callback_url();
                    $options = array(
                        'action' => 'display',
                        'recorder' => $client,
                        'recorderoptions' => $recorderoptions
                    );
                    $mform = new recorder_form($formaction, $options);
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
        $mform = new recorder_form($formaction, $options);
        $mform->display();
        echo $OUTPUT->footer();
    }

    /**
     * Initializes the recorder
     *
     * @param string $returnurl
     */
    public function init($returnurl, $options) {
        global $PAGE, $CFG, $OUTPUT;
        echo $OUTPUT->header();
        $jsmodule = array(
            'name' => 'repository_mediacapture',
            'fullpath' => '/repository/mediacapture/module.js',
            'requires' => array('event', 'node', 'json')
        );
        $PAGE->requires->js_init_call('M.repository_mediacapture.init', array(), false, $jsmodule);
        $formaction = new moodle_url('/repository/mediacapture/view.php', array('returnurl' => $returnurl, 'options' => $options));
        $mform = new recorder_form($formaction, array('action' => 'init'));
        $mform->display();
        echo $OUTPUT->footer();
    }

    /**
     * View for the plugin
     *
     * @param object $mform
     */
    public function view($mform) {
        $recorders = self::get_installed_recorders();

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
     * Return list of installed recorders
     *
     * @return array $recorders array structure containing list of recorders installed.
     */
    public static function installed_recorders() {
        global $CFG;

        $recorders = array(
            'audio' => array(),
            'video' => array()
        );

        $recordersdir = "$CFG->dirroot/repository/mediacapture/recorders";
        if ($handle = opendir($recordersdir)) {
            while (false !== ($recordername = readdir($handle))) {
                if ($recordername != "." && $recordername != "..") {
                    if (file_exists("$recordersdir/$recordername/lib.php")) {
                        require_once("$recordersdir/$recordername/lib.php");
                        $classname = 'repository_mediacapture_' . $recordername;
                        if (class_exists($classname)) {
                            $recorder = new $classname();
                            $media = array_intersect($recorder->supported_media(), array('audio', 'video'));
                            $types = array_intersect($recorder->supported_types(), array('html5', 'flash', 'java'));
                            if ($media && $types) {
                                for ($i = 0; $i < count($media); $i++) {
                                    array_push($recorders[$media[$i]], $recordername);
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
     * Return list of installed recorders
     *
     * @param string $media name of media 'audio', 'video', 'image'
     * @return array of recorders corresponding to the media type
     */
    public static function get_installed_recorders($media = null) {
        if (!isset(self::$installedrecorders)) {
           self::$installedrecorders = self::installed_recorders();
        }

        if ($media == null) {
            return self::$installedrecorders;
        } else {
            return self::$installedrecorders[$media];
        }
    }

    /**
     * Return list of strings used in JS
     *
     * @return array $strings Array of string definitions to be used by javascript.
     */
    public function list_strings($keys) {
        foreach ($keys as $key) {
            $strings[] = array($key, 'repository_mediacapture');
        }
        return $strings;
    }

    /**
     * Return array of possible error messages
     *
     * @return $errors array structure containing the compatibility errors
     */
    public function display_errors($mform, $errors) {

        if (empty($errors)) { // When no error messages, recorders are disabled in plugin settings.
            $msg = get_string('norecordersfound', 'repository_mediacapture');
            $mform->addElement('html', $msg);
        }

        foreach ($errors as $type => $error) {
            $msg = get_string($type, 'repository_mediacapture'). ' => ' .
                   get_string('required', 'repository_mediacapture')    . ':' . $error['required']  . ' (' .
                   get_string('installed', 'repository_mediacapture')   . ':' . $error['installed'] . ')';
            $mform->addElement('html', $msg);
        }
    }

    /**
     * Path for callback url, which will upload file
     *
     * @return Repository callback url
     */
    public static function callback_url() {
        return new moodle_url('/repository/mediacapture/callback.php');
    }
}
