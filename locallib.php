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
    public static $installedrecorders;

    /**
     * Renders appropriate recorder
     *
     * @param string $media name of the media type
     * @param int $repositoryid repository id
     * @param int $contextid context id in which reposity was initalised
     * @param object $browserplugins client browser information
     * @param string $recordername name of recorder to display
     * @return type
     */
    public function display_recorder($media, $repositoryid, $contextid, $browserplugins = null, $recordername = null) {
        global $PAGE, $CFG, $OUTPUT;

        $errors = array();
        // Check if only one recorder is enabled, if no then try show options
        $mediacaptureoptions = $this->get_mediacapture_instance_options($repositoryid, $contextid);
        $recorders = $this->get_enabled_recorders($mediacaptureoptions);
        $recorders = $recorders[$media];
        $client = null;
        // If browserplugin is given then check best suitable media recorder
        if (!empty($browserplugins) && empty($recordername) && !empty($mediacaptureoptions['mediacaptureautodetect'])) {
            foreach ($recorders as $recordername) {
                if (!empty($mediacaptureoptions[$recordername])) {
                    $classname = 'repository_mediacapture_' . $recordername;
                    $client = new $classname();
                    $compatible = true;
                    $version = $client->min_version();
                    $types = $client->supported_mediatypes();
                    foreach ($types as $type) {
                        if (!(isset($browserplugins->$type) && ($browserplugins->$type >= $version[$type]))) {
                            $compatible = false;
                            $errors[$type] = array(
                                'installed' => $browserplugins->$type,
                                'required' => $version[$type]
                                );
                        }
                    }
                }
                if ($compatible) {
                    break;
                }
            }
        } else if (!empty($recordername)){
            $classname = 'repository_mediacapture_' . $recordername;
            $client = new $classname();
        }

        // Check for the compatible plugin-recorder.
        if ($client) {
            $PAGE->requires->css(new moodle_url("$CFG->wwwroot/repository/mediacapture/recorders/$recordername/styles.css"));
            $jsmodule = array(
                'name' => 'repository_mediacapture_$recorder',
                'fullpath' => "/repository/mediacapture/recorders/$recordername/module.js",
                'requires' => array('event', 'node', 'io', 'base'),
                'strings' => $this->list_strings($client->string_keys())
            );
            $data = array(urlencode($client->post_url()));
            echo $OUTPUT->header();
            $PAGE->requires->js_init_call("M.repository_mediacapture_$recordername.init", $data, false, $jsmodule);
            $formaction = $this->callback_url();
            $options = array(
                'action' => 'display',
                'recorder' => $client,
                'recorderoptions' => $mediacaptureoptions
            );
            $mform = new recorder_form($formaction, $options);
            $mform->display();
            echo $OUTPUT->footer();
            return;
        }

        echo $OUTPUT->header();
        // No recorder selected : display appropriate message.
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
    public function init($returnurl, $repositoryid, $contextid) {
        global $PAGE, $CFG, $OUTPUT;
        // Check if only one recorder is enabled, if no then try show options
        $mediacaptureoptions = $this->get_mediacapture_instance_options($repositoryid, $contextid);
        $enabledrecorders = $this->get_enabled_recorders($mediacaptureoptions);

        if (!empty($enabledrecorders)) {
            // If count is 1 then only audio or video is there, so show it.
            if (count($enabledrecorders) == 1) {
                $mediatype = array_keys($enabledrecorders);
                $mediatype = $mediatype[0];
                if (count($enabledrecorders[$mediatype]) == 1) {
                    // Show recorder, else show options
                    $this->display_recorder($mediatype, $repositoryid, $contextid, null, $enabledrecorders[$mediatype][0]);
                    return;
                }
            }
            // Else we need to show options now.
            echo $OUTPUT->header();
            $jsmodule = array(
                'name' => 'repository_mediacapture',
                'fullpath' => '/repository/mediacapture/module.js',
                'requires' => array('event', 'node', 'json')
            );
            $PAGE->requires->js_init_call('M.repository_mediacapture.init', array(), false, $jsmodule);
            $formaction = new moodle_url('/repository/mediacapture/view.php', array('returnurl' => $returnurl,
                'repositoryid' => $repositoryid,
                'contextid' => $contextid));
            $mform = new recorder_form($formaction, array('action' => 'init',
                        'enabledrecorders' => $enabledrecorders,
                        'autodetect' => $mediacaptureoptions['mediacaptureautodetect']));
            $mform->display();
            echo $OUTPUT->footer();
        } else {
            echo $OUTPUT->header();
            $mform = new recorder_form($formaction, array('action' => 'nodisplay'));
            $mform->display();
            echo $OUTPUT->footer();
        }
    }

    /**
     * View recorder selection options
     *
     * @param object $mform
     */
    public function viewrecorderselection($mform, $enabledrecorders, $autodetect) {
        if ($autodetect) {
            if (count($enabledrecorders['audio'])) {
                $mform->addElement('button', 'recordaudio', get_string('recordaudio', 'repository_mediacapture'));
            }
            if (count($enabledrecorders['video'])) {
                $mform->addElement('button', 'recordvideo', get_string('recordvideo', 'repository_mediacapture'));
            }
        } else {
            foreach ($enabledrecorders as $type => $recorders) {
                foreach ($recorders as $recorder) {
                    $mform->addElement('submit', $recorder, get_string($recorder.'submit', 'repository_mediacapture'));
                }
            }
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
                            $types = array_intersect($recorder->supported_mediatypes(), array('html5', 'flash', 'java'));
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
     * Return list of enabled recorders for this instance of mediacapture
     *
     * @param array $recorders list of installed recorders
     * @param array $mediacaptureoptions mediacapture options
     * @return array of enabled recorders
     */
    public function get_enabled_recorders($mediacaptureoptions, $recordertypes = null) {
        $enabledrecorders = array();
        // Get all installed recorders if no recorder list is passed
        if (empty($recordertypes)) {
            $recordertypes = self::get_installed_recorders();
        }
        // Find if it's enabled else unset it.
        foreach ($recordertypes as $type => $recorders) {
            foreach ($recorders as $key => $recorder) {
                if (!empty($mediacaptureoptions[$recorder])) {
                    // Initalize array if not available
                    if (empty($enabledrecorders[$type])) {
                        $enabledrecorders[$type] = array();
                    }
                    $enabledrecorders[$type][] = $recorder;
                }
            }
        }
        return $enabledrecorders;
    }

    /**
     * Get mediacapture instace options
     *
     * @param int $repositoryid instance id of repository.
     */
    public function get_mediacapture_instance_options($repositoryid, $context) {
        GLOBAL $CFG;
        require_once(dirname(__FILE__) . '/../lib.php');
        require_once(dirname(__FILE__) . '/lib.php');

        $mediacapturerepo = new repository_mediacapture($repositoryid, $context);
        return $mediacapturerepo->get_option();
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
