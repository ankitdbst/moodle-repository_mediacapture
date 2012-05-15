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
        $this->audio_video_recorder('audio');
    }

    /**
     * Returns the suported file types
     *
     * @return array of supported file types and extensions.
     */
    public function supported_filetypes() {
        return array('web_audio');
    }

    /**
     * This is the main function that would record the audio/video
     * stream and upload it to the server.
     *
     * @param string $media type of the recording for the media
     */
    public function audio_video_recorder($media) {
        if ($media == 'audio') {
            $this->print_record_audio();
        } else if ($media == 'video') {
            $this->print_record_video();
        }
    }

    /**
     * Prints the audio recording applet html in the filepicker instance
     * of the plugin
     */
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

        echo '<div class="audio_container">';
        echo '<form onsubmit="">';
        echo '<input type="hidden" id="repo_id" name="repo_id" value="' . $this->id . '" />';
        echo '<label for="filename">' . get_string('name', 'repository_medicapture') . ':</label>';
        echo '<input type="text" name="filename" id="filename" /><br />';
        echo '<applet id="audio_recorder" name="audio_recorder" code="gong.NanoGong" width="180" height="40" archive="' . $CFG->httpswwwroot . '/repository/medicapture/nanogong.jar">';
        echo '<param name="AudioFormat" value="' . $audio_format . '" />';
        echo '<param name="SamplingRate" value="' . $sampling_rate . '" />';
        echo '<p>' . get_string('javanotfound', 'repository_medicapture') . '</p>';
        echo '</applet><br /><br />';
        echo '<input type="submit" value="' . get_string('save', 'repository_medicapture') . '" />';
        echo '</form>';
        echo '</div>';
    }

    /**
     * Prints the video recording applet html in the filepicker instance
     */
    public function print_record_video() {
        // video options
    }

}
