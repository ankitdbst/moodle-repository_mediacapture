<?php

require_once(dirname(__FILE__) . '/lib.php');

$tmp_dir = temp_dir();

$elname = 'nanogong';
$tmp_file = $_FILES[$elname]['tmp_name'];
$tmp_name = $_FILES[$elname]['name'];
// move the uploaded file to temp dir and return location
if (!move_uploaded_file($tmp_file, $tmp_dir.'/'.$tmp_name)) {
    echo '';
} else {
    echo $tmp_dir.'/'.$tmp_name;
}
