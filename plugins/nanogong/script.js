
/**
* Method to validate the audio recording form and save
* the recording to temp file
*/
function submit_nanogong_audio() {

    var filename    = document.getElementById('id_filename'),
        recorder    = document.getElementById('audio_recorder'),
        filepath    = document.getElementsByName('filepath')[0],
        tempdir     = document.getElementsByName('tempdir')[0],
        posturl     = document.getElementsByName('posturl')[0];

    if (!filename.value) {
        alert(mediacapture['nonamefound']);
        return false;
    }
    filename.value = filename.value.replace(/^\s+|\s+$/g,"");
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
                decodeURIComponent(tempdir.value);
    filepath.value = encodeURIComponent(recorder.sendGongRequest("PostToForm", posturl.value, "repo_upload_audio", "cookie=nanogong", filename.value));

    if (!filepath.value) {
        alert(mediacapture['filenotsaved']);
        return false;
    }

    // Submit the form to callback url
    var form = document.getElementById('mform1');
    //form.action = form.action + '?filetype=' + 'wav';
    form.submit();
}