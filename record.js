/**
 * @file Javascript file to handle the audio/video recording
 */

/**
 * Method to validate the audio recording form and save
 * the recording to temp file
 */
function submitAudio() {
    var filename    = document.getElementById('audio_filename'),
        recorder    = document.getElementById('audio_recorder'),
        posturl     = document.getElementById('posturl'),
        fileloc     = document.getElementById('audio_loc');

    filename.value = filename.value.replace('.wav', '') + '.wav';
    if (!filename) {
        alert(mediacapture['nonamefound']);
        return false;
    }

    if (!recorder || !(recorder.sendGongRequest)) {
        alert(mediacapture['appletnotfound']);
        return false;
    }

    var duration = parseInt(recorder.sendGongRequest("GetMediaDuration", "audio")) || 0
    if (duration <= 0) {
        alert(mediacapture['norecordingfound']);
        return false;
    }

    fileloc.value = recorder.sendGongRequest("PostToForm", decodeURIComponent(posturl.value),
                                        "repo_upload_audio", "cookie=nanogong", "myfile");
    if (!fileloc) {
        alert(mediacapture['filenotsaved']);
        return false;
    }

    simulateClick(recorder);

    return true;
}

/**
 * Simulates the 'click' event for the form upload
 */
function simulateClick(el) {

    while(el.tagName != 'FORM') {
        el = el.parentNode;
    }

    el.repo_upload_file.type = 'hidden';
    el.repo_upload_file.value = 'temp.wav';
    el.nextElementSibling.getElementsByTagName('button')[0].click();
}
