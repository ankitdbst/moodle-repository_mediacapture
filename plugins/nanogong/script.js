
/**
* Method to validate the audio recording form and save
* the recording to temp file
*/
function submit_audio(posturl, tmpdir) {
    var filename    = document.getElementById('filename'),
        recorder    = document.getElementById('audio_recorder'),
        fileloc     = document.getElementById('fileloc'),
        tmpdir      = document.getElementById('tmpdir'),
        posturl     = document.getElementById('posturl');
        
    if (!filename.value) {
        alert(mediacapture['nonamefound']);
        return false;
    }
    filename.value = filename.value + '.wav';
    if (!recorder || !(recorder.sendGongRequest)) {
        alert(mediacapture['appletnotfound']);
        return false;
    }

    var duration = parseInt(recorder.sendGongRequest("GetMediaDuration", "audio")) || 0
    if (duration <= 0) {
        alert(mediacapture['norecordingfound']);
        return false;
    }

    posturl.value = decodeURIComponent(posturl.value) + '?recorder=nanogong&tmp_dir=' + 
                decodeURIComponent(tmpdir.value);
    fileloc.value = encodeURIComponent(recorder.sendGongRequest("PostToForm", posturl.value, "repo_upload_audio", "cookie=nanogong", filename.value));
    
    if (!fileloc.value) {
        alert(mediacapture['filenotsaved']);
        return false;
    }
    
    return true;
}