
/**
* Method to validate the audio recording form and save
* the recording to temp file
*/

M.repository_mediacapture_flashaudio = {};

M.repository_mediacapture_flashaudio.init = function(Y, params) {
    var posturl = decodeURIComponent(params),
        form = Y.one('#mform1');

    var validate = function(filename, filedata) {
        var recorder = Y.one('#onlineaudiorecorder').getDOMNode(),
            filepath = Y.one('*[name="filepath"]');

        Y.one('*[name="filename"]').set('value', filename);
        Y.one('*[name="filedata"]').set('value', filedata);

        filename = filename.replace(/^\s+|\s+$/g,"");
        if (!filename) {
            alert(M.str.repository_mediacapture.nonamefound);
            return false;
        }

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
            data: 'filedata=' + filedata +
                  '&filename=' + filename,
            sync:true
        };
        var request = Y.io(posturl, cfg);
        if (!path) {
            alert(M.str.repository_mediacapture.norecordingfound);
            return false;
        }

        filepath.set('value', path);
        form.submit();
    }
}
