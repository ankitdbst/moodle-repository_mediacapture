
/**
* @Javascript file to validate the red5 video recording form
*/

M.repository_mediacapture_red5recorder = {};

M.repository_mediacapture_red5recorder.init = function(Y, params) {
    var posturl = decodeURIComponent(params),
        form = Y.one('#mform1');

    form.on('submit', function(e) {
        e.preventDefault();
        var filename = Y.one('#id_filename').get('value'),
            filepath = Y.one('*[name="filepath"]');

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
            data: 'filepath=' + filepath.get('value') +
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
    });
}