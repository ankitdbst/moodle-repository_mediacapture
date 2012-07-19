
/**
* Method to validate the audio recording form and save
* the recording to temp file
*/
function submit_audio() {

    var filename    = document.getElementById('id_filename'),
        recorder    = document.getElementById('audio_recorder'),
        fileloc     = document.getElementsByName('fileloc')[0],
        tmpdir      = document.getElementsByName('tmpdir')[0],
        posturl     = document.getElementsByName('posturl')[0];    
    if (!filename.value) {
        alert(mediacapture['nonamefound']);
        return false;
    }
    filename.value = filename.value.replace(/^\s+|\s+$/g,"") + '.wav';
    if (!recorder || !(recorder.sendGongRequest)) {
        alert(mediacapture['appletnotfound']);
        return false;
    }

    var duration = parseInt(recorder.sendGongRequest("GetMediaDuration", "audio")) || 0
    if (duration <= 0) {
        alert(mediacapture['norecordingfound']);
        return false;
    }

    posturl.value = posturl.value + '?recorder=nanogong&tmp_dir=' + 
                decodeURIComponent(tmpdir.value);
    fileloc.value = encodeURIComponent(recorder.sendGongRequest("PostToForm", posturl.value, "repo_upload_audio", "cookie=nanogong", filename.value));
    
    if (!fileloc.value) {
        alert(mediacapture['filenotsaved']);
        return false;
    }

    // Submit the form to callback url
    var form = document.getElementById('mform1');
    form.submit();
}