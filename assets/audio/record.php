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

require_once(dirname(dirname(__FILE__)) . '/../mediacapture.php');

$mc = new mediacapture();
$elname = 'repo_upload_audio';

if (isset($_FILES[$elname]['tmp_name'])) {
    $filename = $mc->get_unused_filename('.wav');
    print $mc->save_temp_file($_FILES[$elname]['tmp_name'], $filename);
}
