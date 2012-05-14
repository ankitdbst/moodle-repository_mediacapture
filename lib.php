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
        global $action, $itemid;
        parent::__construct($repositoryid, $context, $options);
    }

    public static function get_type_option_names() {
        return array('audio_format', 'sampling_rate');
    }

    public static function type_config_form($mform, $classname = 'repository') {
        parent::type_config_form($mform);
        $audio_format_options = array(
            get_string('audio_format_imaadpcm', 'repository_mediacapture'),
            get_string('audio_format_speex', 'repository_mediacapture'),
        );
        $sampling_rate_options = array(
            get_string('sampling_rate_low', 'repository_mediacapture'),
            get_string('sampling_rate_medium', 'repository_mediacapture'),
            get_string('sampling_rate_normal', 'repository_mediacapture'),
            get_string('sampling_rate_high', 'repository_mediacapture'),
        );

        $mform->addElement('select', 'audio_format', get_string('audio_format', 'repository_mediacapture'), $audio_format_options);
        $mform->addElement('select', 'sampling_rate', get_string('sampling_rate', 'repository_mediacapture'), $sampling_rate_options);
    }

    /**
     * Method to get the repository content.
     *
     * @param string $path current path in the repository
     * @param string $page current page in the repository path
     * @return array structure of listing information
     */
    public function get_listing($path = '', $page = '') {
        global $CFG, $PAGE, $action;

        $list = array();
        $list['page'] = (int)$page;
        if ($list['page'] < 1) {
            $list['page'] = 1;
        }
        $list['nologin'] = true;
        $list['nosearch'] = true;

        return $list;
    }

    /**
     * Returns the suported file types
     *
     * @return array of supported file types and extensions.
     */
    public function supported_filetypes() {
        return array('web_audio');
    }

    /*
     * Main function for audio/video recording
     */
    public function audio_video_recorder($media = 'audio') {
        // browser support
        // os support
        record();
        // record
        // format
        // upload
        // save
    }

    public function record($media) {
        if ($media == 'audio') {
            print_record_audio();
        } else if ($media == 'video') {
            print_record_video();
        }
    }

    public function print_record_audio() {
        global $CFG, $PAGE;

        $sampling_rates = array(
            array(8000, 11025, 22050, 44100),
            array(8000, 16000, 32000, 44100)
        );
        $audio_formats = array('ImaADPCM', 'Speex');

        $audio_format = get_config('mediacapture', 'audio_format');
        $sampling_rate = get_config('mediacapture', 'sampling_rate');

        $sampling_rate = $sampling_rates[$audio_format][$sampling_rate];
        $audio_format = $audio_formats[$audio_format];

        // we need some JS libraries for AJAX
        require_js(array('yui_yahoo', 'yui_dom', 'yui_event', 'yui_element', 'yui_connection', 'yui_json'));

        $PAGE->requires->js('repository/mediacapture/record.js');
        $PAGE->requires->data_for_js('mediacapture', array(
            'unexpectedevent' => get_string('unexpectedevent', 'repository_mediacapture'),
            'appletnotfound' => get_string('appletnotfound', 'repository_mediacapture'),
            'norecordingfound' => get_string('norecordingfound', 'repository_mediacapture'),
            'nonamefound' => get_string('nonamefound', 'repository_mediacapture')
        ));

        $record_html =
                    '<div class="nanogong_container">
                        <form onsubmit="nanogongSubmit(); return false;">
                            <input type="hidden" id="repo_id" name="repo_id" value="' . $this->id . '" />
                            <label for="filename">' . get_string('name', 'repository_mediacapture') . ':</label>
                            <input type="text" name="filename" id="filename" /><br />
                            <applet id="nanogong_recorder" name="nanogong_recorder" code="gong.NanoGong" width="180" height="40" archive="' . $CFG->httpswwwroot . '/repository/mediacapture/nanogong.jar">
                                <param name="AudioFormat" value="' . $audio_format . '" />
                                <param name="SamplingRate" value="' . $sampling_rate . '" />
                                <p>' . get_string('javanotfound', 'repository_mediacapture') . '</p>
                            </applet><br /><br />
                            <input type="submit" value="' . get_string('save', 'repository_mediacapture') . '" />
                        </form>
                    </div>';
        echo $record_html;
    }

    public function print_record_video() {
        // video options
    }

}
