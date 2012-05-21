<?php

require_once(dirname(dirname(__FILE__)).'/config.php');
require_once($CFG->dirroot.'/repository/mediacapture/lib.php');

$client = new repository_mediacapture();

$filename = $_FILES['repo_upload_audio']['name'];
$filedata = $_FILES['repo_upload_audio']['tmp_name'];

$ret = $client->upload($filename, $filedata);
if (isset($ret['existingfile'])) {
    print 'File with the same name already exists!';
} else {
    print 'File has been uploaded succesfully!';
}

?>

