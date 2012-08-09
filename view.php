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
 * @copyright  2012 Ankit Gupta <mailtoankitgupta@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/locallib.php');

$returnurl      = required_param('returnurl', PARAM_URL);
$type           = optional_param('type', '', PARAM_TEXT);
$browserplugins = optional_param('browserplugins', '', PARAM_TEXT);
$browserdetect  = optional_param('browserdetect', '', PARAM_TEXT);
$repositoryid   = required_param('repositoryid', PARAM_INT);
$contextid      = required_param('contextid', PARAM_INT);

$PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));
$PAGE->set_url('/repository/mediacapture/view.php', array('returnurl'=>$returnurl));
$PAGE->set_pagelayout('embedded');

require_sesskey();
require_login();

$mediacapture = new mediacapture();
$mediacaptureoptions = $mediacapture->get_mediacapture_instance_options($repositoryid, $contextid);
$enabledrecorders = $mediacapture->get_enabled_recorders($mediacaptureoptions);
$recordername = null;
if (empty($type)) {
    foreach ($enabledrecorders as $rectype => $recorders) {
        foreach ($recorders as $recorder) {
            $sentrecorder = optional_param($recorder, '', PARAM_TEXT);
            if (!empty($sentrecorder)) {
                $type = $rectype;
                $recordername = $recorder;
                break;
            }
        }
    }
    $mediacapture->display_recorder($type, $repositoryid, $contextid, json_decode($browserplugins), $recordername);
} else {
    switch ($type) {
        case 'init':
            $mediacapture->init($returnurl, $repositoryid, $contextid);
            break;
        default:
            $mediacapture->display_recorder($type, $repositoryid, $contextid, json_decode($browserplugins));
            break;
    }
}