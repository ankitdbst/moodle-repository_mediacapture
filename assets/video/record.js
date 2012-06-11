/**
 * @file Javascript file to handle the audio recording
 */
 
function setStatus(num, str) {
    // Handle status changes
    //**********************
    // Status codes:
    // StartUpload = 0;
    // UploadDone = 1;
    // StartRecord = 2;
    // StartPlay = 3;
    // PauseSet = 4;
    // Stopped = 5;
    document.getElementById('Status').value = str;
}

function setTimer(str) {
    document.getElementById('Timer').value = str;
}

function record_rp() {
    document.VimasVideoApplet.RECORD_VIDEO();
}

function playback_rp() {
    document.VimasVideoApplet.PLAY_VIDEO();
}

function pause_rp() {
    document.VimasVideoApplet.PAUSE_VIDEO();
}

function stop_rp() {
    document.VimasVideoApplet.STOP_VIDEO();
}

function upload_rp() {
    filename = document.getElementById('video_filename');
    filename.value = filename.value.replace('.mp4', '') + '.mp4';
    document.VimasVideoApplet.UPLOAD_VIDEO(String(filename.value));
    fileloc = document.getElementById('video_loc');
    fileloc.value = encodeURIComponent(decodeURIComponent(fileloc.value) + '/' + filename.value);
	simulateClick(filename);
}

/**
 * Simulates the 'click' event for the form upload
 */
function simulateClick(el) {

    while(el.tagName != 'FORM') {
        el = el.parentNode;
    }

    el.repo_upload_file.type = 'hidden';
    el.repo_upload_file.value = 'temp.mp4';
    el.nextElementSibling.getElementsByTagName('button')[0].click();
}