<?php

require_once(dirname(__FILE__) . '/lib.php');

$tmpdir = temp_dir();

$elname = 'nanogong';
$tmpfile = $_FILES[$elname]['tmp_name'];
$tmpname = $_FILES[$elname]['name'];
// move the uploaded file to temp dir and return location
if (!move_uploaded_file($tmpfile, $tmpdir.'/'.$tmpname)) {
    echo '';
} else {
    echo $tmpdir.'/'.$tmpname;
}
