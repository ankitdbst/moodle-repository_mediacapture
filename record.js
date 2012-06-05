/**
 * @file Javascript file to handle the recording using nanogong applet
 */

/**
 * Submits the recorded sound using default form elements
 */
function submitAudio() {
    // locate the filename
    var filename;
    if (!(filename = document.getElementById('audio_filename')) || !(filename = filename.value + '.wav')) {
        filename = new Date().toGMTString().replace('+', ' ');
    }

    // find nanogong applet
    var recorder;
    if (!(recorder = document.getElementById('audio_recorder')) || !(recorder.sendGongRequest)) {
        alert(mediacapture['appletnotfound']);
        return false;
    }

    // check there is a recording
    var duration = parseInt(recorder.sendGongRequest("GetMediaDuration", "audio")) || 0
    if (duration <= 0) {
        alert(mediacapture['norecordingfound']);
        return false;
    }

    // form post url
    var postURL = document.getElementById('posturl').value;
    // submit the sound file
    var fileloc = recorder.sendGongRequest("PostToForm", decodeURIComponent(postURL), "repo_upload_audio", "cookie=nanogong", "myfile");

    // move the audio to a temp fileloc
    if (!fileloc) {
        alert(mediacapture['filenotsaved']);
        return false;
    }

    audio_loc = document.getElementById('audio_loc');
    audio_loc.value = fileloc;

    return true;
}
