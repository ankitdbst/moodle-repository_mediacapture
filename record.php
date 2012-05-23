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
 * This file moves the temp audio file created through nanogong
 * to a temporary location.
 * TODO : nanogong should be modified to return the temp location
 * instead of using this file to extract & save it.
 *
 * @package    repository_mediacapture
 * @category   repository
 * @copyright  2012 Ankit Gupta
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// include config file
require_once(dirname(dirname(__FILE__)).'/../config.php');

global $CFG;

// temporary location to move the recorded audios
$dir = $CFG->dataroot.'/temp';

// builds a unique temp filename for the media file
$i = 0;
do {
    if ($i > 0)
        sleep(1);
    $filename = $dir . '/' . time() . '.wav';
    $i++;
} while ($i < 3 && file_exists($filename)); // try 3 times for unique filename

if (!move_uploaded_file($_FILES['repo_upload_audio']['tmp_name'], $filename)) {
    print '';
} else {
    print $filename;
}
