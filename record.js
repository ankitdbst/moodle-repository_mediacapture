/**
 * @file Javascript file to handle nanogong recording
 */
/**
 * Submits the recorded sound using default form elements
 */
function submitAudio() {
    // locate the filename
    var filename;
    if (!(filename = document.getElementById('filename')) || !(filename = filename.value)) {
        filename = new Date().toGMTString().replace('+', ' ');
    }
    // form post url
    var postURL = document.getElementById('posturl').value;
    // submit the sound file
    var ret = uploadFile('audio_recorder', decodeURIComponent(postURL),
        'repo_upload_audio', 'cookie=nanogong', filename);
    if(!ret) {
        alert(mediacapture['unexpectedevent'] + ' (upload)');
        return;
    }
    alert(ret);
}

/**
 * Submits the recorded sound using function arguments
 */
function uploadFile(applet_id, postURL, inputname, cookie, filename) {
    // find nanogong applet
    var recorder;
    if (!(recorder = document.getElementById(applet_id)) || !(recorder.sendGongRequest)) {
        alert(mediacapture['appletnotfound']);
        return;
    }

    // check there is a recording
    var duration = parseInt(recorder.sendGongRequest("GetMediaDuration", "audio")) || 0
    if (duration <= 0) {
        alert(mediacapture['norecordingfound']);
        return
    }

    if (!filename) {
        alert(mediacapture['nonamefound']);
        return;
    }

    // upload the sound file to the server
    console.log('PostToForm', postURL, inputname, cookie, filename);
    var msg = recorder.sendGongRequest('PostToForm', postURL, inputname, cookie, filename);
    return msg;
}
