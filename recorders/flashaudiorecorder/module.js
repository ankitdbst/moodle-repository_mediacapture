/**
* Method to validate the audio recording form and save
* the recording to temp file
*/

M.repository_mediacapture_flashaudiorecorder = {};

var posturl, Y;

M.repository_mediacapture_flashaudiorecorder.init = function(innerY, params) {
    posturl = decodeURIComponent(params);
    Y = innerY;
}

M.repository_mediacapture_flashaudiorecorder.validate = function (filename, filedata) {
    var recorder    = Y.one('#onlineaudiorecorder').getDOMNode(),
        filepath    = Y.one('*[name="filepath"]'),
        form        = Y.one('#mform1');

    filename = filename.replace(/^\s+|\s+$/g,"");
    filename = filename.replace(".mp3", "");
    if (!filename) {
        alert(M.str.repository_mediacapture.nonamefound);
        return false;
    }
    Y.one('*[name="filename"]').set('value', filename);

    var path;
    // Define a function to handle the response data.
    function complete(id, o) {
        var id = id; // Transaction ID.
        var data = o.responseText; // Response data.
        path = data;
    };
    // Subscribe to event "io:complete"
    Y.on('io:complete', complete, Y);
    // Make an HTTP POST request to posturl.
    cfg = {
        method: 'POST',
        data: 'filedata=' + encodeURIComponent(filedata) +
              '&filename=' + filename,
        sync:true
    };
    var request = Y.io(posturl, cfg);
    if (!path) {
        alert(M.str.repository_mediacapture.noflashaudiofound);
        return false;
    }

    filepath.set('value', path);
    form.submit();
}
