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
 * This file contains 'repository_mediacapture' repository plugin language strings
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/locallib.php');

$string['pluginname'] = 'Media Capture';
$string['mediacapture:view'] = 'Use Media Capture in file picker';
$string['configplugin'] = 'Media Capture repository configuration';
$string['repositoryname'] = 'MediaCapture';

$string['startaudio'] = 'Start Audio';
$string['startvideo'] = 'Start Video';

$string['video_quality'] = 'Video quality';
$string['video_low'] = 'LowQuality';
$string['video_normal'] = 'NormalQuality';
$string['video_high'] = 'HighQuality';

$string['recordnew'] = 'New recording';
$string['unexpectedevent'] = 'An unexpected event was detected. Please report this problem to the Moodle developers.';
$string['appletnotfound'] = 'The applet was not detected. Please make sure that you have Java Runtime Environment (JRE) installed and the applet loaded properly.';
$string['norecordingfound'] = 'There is no sound recorded. Please use the red disc button to start recording. Use the black square button to stop recording.';
$string['nonamefound'] = 'Please provide a name for your recording.';
$string['javanotfound'] = 'Your browser does not have Java support. Consider installing the latest version of <a href="http://www.java.com/en/download/">Java Runtime Engine</a>';
$string['flashnotfound'] = 'Your browser does not have Flash support. Consider installing the latest version of <a href="http://www.adobe.com/go/getflashplayer">Adobe Flash Player</a>';
$string['quicktimenotfound'] = 'Your browser does not have Quicktime player installed. Consider installing the latest version of <a href="https://www.apple.com/quicktime/download">Quicktime Player</a>';
$string['filenotsaved'] = 'The file could not be saved! Please try again';

$string['required'] = 'Required';
$string['installed'] = 'Installed';
$string['java'] = 'Java';
$string['flash'] = 'Flash';

$string['save'] = 'Save';
$string['name'] = 'Name';
$string['account'] = 'Account';

$client = new mediacapture_recorder();
$files = $client->list_files();
foreach ($files as $file) {
    require_once($file);
}