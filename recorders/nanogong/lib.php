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
 * repository_mediacapture_nanogong class
 *
 * @copyright  2012 Ankit Gupta <mailtoankitgupta@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/recorder.php');

class repository_mediacapture_nanogong extends recorder {

    /**
     * Type option names for the recorder
     *
     * @return array $options
     */
    public static function get_type_option_names() {
        return array('nanogong_audio_format', 'nanogong_sampling_rate', 'nanogong');
    }

    /**
     * Admin config settings for the type options defined in get_type_option_names()
     *
     * @param $mform
     */
    public function add_config_form($mform) {
        $audioformatoptions = array(
            get_string('nanogongaudioformatimaadpcm', 'repository_mediacapture'),
            get_string('nanogongaudioformatspeex', 'repository_mediacapture'),
        );

        $samplingrateoptions = array(
            get_string('nanogongsamplingratelow', 'repository_mediacapture'),
            get_string('nanogongsamplingratemedium', 'repository_mediacapture'),
            get_string('nanogongsamplingratenormal', 'repository_mediacapture'),
            get_string('nanogongsamplingratehigh', 'repository_mediacapture'),
        );

        $mform->addElement('select', 'nanogong_audio_format', get_string('nanogongaudioformat', 'repository_mediacapture'), $audioformatoptions);
        $mform->addElement('select', 'nanogong_sampling_rate', get_string('nanogongsamplingrate', 'repository_mediacapture'), $samplingrateoptions);
        $mform->addElement('advcheckbox', 'nanogong', get_string('nanogong', 'repository_mediacapture'), null, array('group' => 1));
    }

    /**
     * The form should contain the following required parameters by mediacapture
     *
     * @param moodleform $mform instance of recoder form
     * @param array $options recorder options
     */
    public function view($mform, $options) {
        global $CFG, $PAGE;

        $url = new moodle_url("$CFG->wwwroot/repository/mediacapture/recorders/nanogong/nanogong.jar");
        $samplingrates = array(
            array(8000, 11025, 22050, 44100),
            array(8000, 16000, 32000, 44100)
        );
        $audioformats   = array('ImaADPCM', 'Speex');
        $audioformat    = $options['nanogong_audio_format'];
        $samplingrate   = $options['nanogong_sampling_rate'];
        if (empty($audioformat)) {
            $audioformat = 0;
        }
        if (empty($sampling_rate)) {
            $samplingrate = 0;
        }
        $samplingrate   = $samplingrates[$audioformat][$samplingrate];
        $audioformat    = $audioformats[$audioformat];
        $javanotfound   = get_string('javanotfound', 'repository_mediacapture');

        $recorder = '
                    <applet id="nanogong" name="nanogong" code="gong.NanoGong" width="160" height="40" archive="' . $url . '">
                        <param name="AudioFormat" value="' . $audioformat .'" />
                        <param name="ShowSaveButton" value="false" />
                        <param name="ShowTime" value="true" />
                        <param name="SamplingRate" value="' . $samplingrate . '" />
                        <p>' . $javanotfound . '</p>
                    </applet>';
        $mform->addElement('html', $recorder);
        $mform->addElement('hidden', 'filepath', '');
        $mform->addElement('hidden', 'filetype', $this->supported_filetype());
        $mform->addElement('text', 'filename', get_string('name', 'repository_mediacapture'));
        $mform->addElement('submit', 'save', get_string('save', 'repository_mediacapture'));
    }

    /**
     * Url for submitting the recorded file (via ajax) to temp_dir()
     *
     * @return string $url
     */
    public function post_url() {
        global $CFG;
        $posturl = new moodle_url("$CFG->wwwroot/repository/mediacapture/recorders/nanogong/record.php");
        return $posturl;
    }

    /**
     * List of all string keys defined by the recorder in the lang file
     *
     * @return array $strings
     */
    public function string_keys() {
        return array(
            'appletnotfound', 'nonanogongrecordingfound', 'nonamefound',
            'filenotsaved', 'nanogongaudioformat', 'nanogongaudioformatimaadpcm',
            'nanogongaudioformatspeex', 'nanogongsamplingrate', 'nanogongsamplingratelow',
            'nanogongsamplingratemedium', 'nanogongsamplingratenormal', 'nanogongsamplingratehigh'
        );
    }

    /**
     * Min version of supported_type() required by the recorder
     *
     * @return array $version
     */
    public function min_version() {
        return array('java' => 1.2);
    }

    /**
     * Supported media viz array('audio', 'video')
     *
     * @return array $media
     */
    public function supported_media() {
        return array('audio');
    }

    /**
     * Supported type viz array('html5', 'flash', 'java')
     *
     * @return array $type
     */
    public function supported_mediatypes() {
        return array('java');
    }

    /**
     * Return string of supported filetype associated with the recording
     *
     * @return string of supported file types/extensions.
     */
    public function supported_filetype() {
        return '.wav';
    }
}

