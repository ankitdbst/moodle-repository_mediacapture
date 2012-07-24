<?php

$recorder = $_REQUEST['recorder'];
$tmp_dir = $_REQUEST['tmp_dir'];

if (isset($recorder) && $recorder === 'nanogong') {
	$elname = 'repo_upload_audio';
	$tmp_file = $_FILES[$elname]['tmp_name'];
	$tmp_name = $_FILES[$elname]['name'];
	// move the uploaded file to temp dir and return location
	if (!move_uploaded_file($tmp_file, $tmp_dir.'/'.$tmp_name)) {
	    echo '';
	} else {
	    echo $tmp_dir.'/'.$tmp_name;
	}
}
