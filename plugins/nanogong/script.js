
/**
* Method to validate the audio recording form and save
* the recording to temp file
*/
function submit_audio() {
    console.log('hello!');
    var filename    = document.getElementById('id_filename'),
        recorder    = document.getElementById('audio_recorder'),
        fileloc     = document.getElementsByName('fileloc')[0],
        tmpdir      = document.getElementsByName('tmpdir')[0],
        posturl     = document.getElementsByName('ajaxuri')[0];
        
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

    posturl.value = posturl.value + '?recorder=nanogong&tmp_dir=' + 
                decodeURIComponent(tmpdir.value);
    fileloc.value = encodeURIComponent(recorder.sendGongRequest("PostToForm", posturl.value, "repo_upload_audio", "cookie=nanogong", filename.value));
    
    if (!fileloc.value) {
        alert(mediacapture['filenotsaved']);
        return false;
    }
    
    var form = document.getElementById('myform1');
    return form.submit();
}

YUI().use('event', function (Y) {
    var save = Y.one('#id_save');
    console.log('#id_save' + save);
    save.on("click", function (e) {
        submit_audio();
    });
});
