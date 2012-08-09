/**
* Method to validate the audio recording form and save
* the recording to temp file
*/

M.repository_mediacapture_nanogong = {};

M.repository_mediacapture_nanogong.init = function(Y, params) {
    var posturl = decodeURIComponent(params),
        form = Y.one('#mform1');

    form.on('submit', function(e) {
        e.preventDefault();
        var recorder = Y.one('#nanogong').getDOMNode(),
            filename = Y.one('#id_filename').get('value'),
            filepath = Y.one('*[name="filepath"]');

        filename = filename.replace(/^\s+|\s+$/g,"");
        if (!filename) {
            alert(M.str.repository_mediacapture.nonamefound);
            return false;
        }

        var duration = parseInt(recorder.sendGongRequest("GetMediaDuration", "audio")) || 0
        if (duration <= 0) {
            alert(M.str.repository_mediacapture.nonanogongrecordingfound);
            return false;
        }

        path = encodeURIComponent(recorder.sendGongRequest("PostToForm", posturl, "nanogong", "cookie=nanogong", filename));
        if (!path) {
            alert(M.str.repository_mediacapture.filenotsaved);
            return false;
        }

        filepath.set('value', path);
        form.submit();
    });
}
