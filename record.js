/**
 * @file Javascript file to handle nanogong recording
 */
/**
 * Submits the recorded sound using default form elements
 */
function submitAudio() {
    // locate the filename
    var filename;
    if (!(filename = document.getElementById('audio_filename')) || !(filename = filename.value)) {
        filename = new Date().toGMTString().replace('+', ' ');
    }

    // find nanogong applet
    var recorder;
    if (!(recorder = document.getElementById('audio_recorder')) || !(recorder.sendGongRequest)) {
        alert(mediacapture['appletnotfound']);
        return;
    }

    // check there is a recording
    var duration = parseInt(recorder.sendGongRequest("GetMediaDuration", "audio")) || 0
    if (duration <= 0) {
        alert(mediacapture['norecordingfound']);
        return;
    }

    // form post url
    var postURL = document.getElementById('posturl').value;
    // submit the sound file
    var filedata = recorder.sendGongRequest("PostToForm", decodeURIComponent(postURL), "repo_upload_audio", "cookie=nanogong", "myfile");
    uploadFile(filename, filedata);
}

/**
 * Uploads the audio file with the given filename
 */
function uploadFile(filename, filedata) {
    f = document.getElementById('audio_filename');
    g = document.getElementById('audio_loc');

    f.value = filename;
    g.value = filedata;

    while(f.tagName != 'FORM') {
        f = f.parentNode;
    }

    f.repo_upload_file.type = 'hidden';
    f.repo_upload_file.value = 'bogus.mp3';
    f.nextSibling.getElementsByTagName('button')[0].click();
}
