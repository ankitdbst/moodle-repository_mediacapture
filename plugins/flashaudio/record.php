<?php

require_once(dirname(__FILE__) . '/lib.php');

$tmpdir = temp_dir();

$tmpdata = required_param('filedata', PARAM_RAW);
$tmpname = required_param('filename', PARAM_TEXT);

$tmpdata = base64_decode($tmpdata);
echo "$tmpdir/$tmpname.mp3";
exit;
// copy the uploaded file to temp dir and return location
if ($tmpdata) {
    file_put_contents("$tmpdir/$tmpname.mp3", $tmpdata);
    echo urlencode("$tmpdir/$tmpname.mp3");
} else {
    echo '';
}
