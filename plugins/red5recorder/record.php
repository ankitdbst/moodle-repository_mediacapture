<?php

require_once(dirname(__FILE__) . '/lib.php');

$tmpdir = temp_dir();

$tmpfile = required_param('filepath', PARAM_PATH);
$tmpname = required_param('filename', PARAM_TEXT);

$tmpfile = urldecode($tmpfile);
// copy the uploaded file to temp dir and return location
if (file_exists($tmpfile) && filesize($tmpfile) > 0 &&
         copy($tmpfile, "$tmpdir/$tmpname.flv")) {
    // remove the file from streams dir
    unlink($tmpfile);
    echo "$tmpdir/$tmpname.flv";
} else {
    echo '';
}
