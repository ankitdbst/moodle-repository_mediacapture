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
 * @copyright  2012 Ankit Gupta <mailtoankitgupta@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/lib.php');

$tmpdir = temp_dir();

$tmpfile = required_param('filepath', PARAM_RAW);
$tmpname = required_param('filename', PARAM_TEXT);

$tmpfile = urldecode($tmpfile);
// Copy the uploaded file to temp dir and return location.
if (file_exists($tmpfile) && filesize($tmpfile) > 0 &&
         copy($tmpfile, "$tmpdir/$tmpname.flv")) {
    // Remove the file from streams dir.
    unlink($tmpfile);
    echo urlencode("$tmpdir/$tmpname.flv");
} else {
    echo '';
}
