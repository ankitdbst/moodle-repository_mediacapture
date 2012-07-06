/**
 * @file Javascript file to handle the audio/video recording
 * of the mediacapture plugin
 */

/**
 * Custom timer event
 */
var secs;
var timerID = null;
var timerRunning = false;
var maxTime = 20;
var delay = 1000;

function InitializeTimer() {
    // Set the length of the timer, in seconds
    secs = 0;
    StopTheClock();
    StartTheTimer();
}

function PauseTheClock() {
    if(timerRunning) {
        timerRunning = false;
        clearTimeout(timerID);
    } else {
        timerRunning = true;
        timerID = self.setTimeout("StartTheTimer()", delay);
    }
}

function StopTheClock() {
    if(timerRunning) {
        clearTimeout(timerID);
    }
    timerRunning = false;
}

function StartTheTimer()
{
    setTimer('00:'+zeroFill(secs,2)+'/00:20');
    if (secs == maxTime)
    {
        StopTheClock();
    }
    else
    {
        self.status = secs;
        secs = secs + 1;
        timerRunning = true;
        timerID = self.setTimeout("StartTheTimer()", delay);
    }
}

function zeroFill( number, width ) {
    width -= number.toString().length;
    if (width > 0)
    {
        return new Array( width + (/\./.test( number ) ? 2 : 1) ).join( '0' ) + number;
    }
    return number + ""; // always return a string
}

 /**
  * Start the appropriate audio/video recorder via ajax
  * in response to user selection
  */
function load_recorder(type) {
    // Create a YUI instance using io-base module.
    YUI().use('node', function(Y) {
        var object = Y.one('object'),
        doc = Y.Node.getDOMNode(object.get('contentDocument'));

        YUI({ doc: doc }).use('node', 'io-base', function(innerY) {        
            var uri = decodeURIComponent(innerY.one('#ajax_uri').get('value'));
            var applet = innerY.one('#appletcontainer');
            // Define a function to handle the response data.
            function complete(id, o) {
                var id = id; // Transaction ID.
                var data = o.responseText; // Response data.
                applet.setContent(data);
            };

            // Subscribe to event "io:complete"
            innerY.on('io:complete', complete, innerY);

            // Make an HTTP POST request to posturl.
            cfg = {
                method: 'POST',  
                data:   'type='+type+
                        '&java='+parent.BrowserPlugins.java+
                        '&flash='+parent.BrowserPlugins.flash+
                        '&quicktime='+parent.BrowserPlugins.quicktime+
                        '&os='+parent.BrowserDetect.OS,
            };

            var request = innerY.io(uri, cfg);
        });

    });

    return false;
}  

/**
 * Method to validate the audio recording form and save
 * the recording to temp file
 */
function submit_java_audio() {

    YUI().use('node', function(Y) {
        var object = Y.one('object'),
        doc = object.get('contentDocument');
        
        var filename = Y.Node.getDOMNode(doc.one('#filename')),
            recorder = Y.Node.getDOMNode(doc.one('#audio_recorder')),
            posturl = Y.Node.getDOMNode(doc.one('#posturl')),
            fileloc = Y.Node.getDOMNode(doc.one('#fileloc'));

        filename.value = filename.value.replace('.wav', '');
        filename.value = filename.value.replace('*', '');

        if (!filename.value) {
            alert(parent.mediacapture['nonamefound']);
            filename.value = '*.wav';
            return false;
        }

        filename.value += '.wav';

        if (!recorder || !(recorder.sendGongRequest)) {
            alert(parent.mediacapture['appletnotfound']);
            return false;
        }

        var duration = parseInt(recorder.sendGongRequest("GetMediaDuration", "audio")) || 0
        if (duration <= 0) {
            alert(parent.mediacapture['norecordingfound']);
            return false;
        }

        posturl.value = decodeURIComponent(posturl.value) + '?type=upload_audio';
        fileloc.value = encodeURIComponent(recorder.sendGongRequest("PostToForm", posturl.value, "repo_upload_audio", "cookie=nanogong", "myfile"));
        
        if (!fileloc.value) {
            alert(parent.mediacapture['filenotsaved']);
            return false;
        }
        
        Y.Node.getDOMNode(doc.one('form')).submit();
    });    
}

function submit_flash_audio(a, b) {
    YUI().use('node', function(Y) {
        var object = Y.one('object'),
        doc = object.get('contentDocument');

        var filename = Y.Node.getDOMNode(doc.one('#filename')),
            filedata = Y.Node.getDOMNode(doc.one('#filedata'));
        
        filename.value = a;
        filedata.value = b;

        Y.Node.getDOMNode(doc.one('form')).submit();
    });
}

/**
 * Status of the applet.
 * This is the hidden element in the interface
 * Useful for debug options (document.getElementById('Status').value)
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
    YUI().use('node', function(Y) {
        var object = Y.one('object'),
        doc = object.get('contentDocument');
        
        var status = Y.Node.getDOMNode(doc.one('#Status'));
        status.value = str;
    });
}

/**
 * Start the timer for the recording
 */
function setTimer(str) {
    YUI().use('node', function(Y) {
        var object = Y.one('object'),
        doc = object.get('contentDocument');
        
        var timer = Y.Node.getDOMNode(doc.one('#Timer'));
        timer.value = str;
    });
}

/**
 * Start recording
 */
function record_rp() {
    YUI().use('node', function(Y) {
        var object = Y.one('object'),
        doc = object.get('contentDocument');
        
        Y.Node.getDOMNode(doc.one('applet')).RECORD_VIDEO();
        InitializeTimer();
        Y.Node.getDOMNode(doc.one('#rec')).disabled = true;
        Y.Node.getDOMNode(doc.one('#play')).disabled=true;
        Y.Node.getDOMNode(doc.one('#stop')).disabled=false;
        Y.Node.getDOMNode(doc.one('#pause')).disabled=false;
    });

    return false;
}

/**
 * Playback for the recorded video
 */
function playback_rp() {
    YUI().use('node', function(Y) {
        var object = Y.one('object'),
        doc = object.get('contentDocument');
        
        Y.Node.getDOMNode(doc.one('applet')).PLAY_VIDEO();
        StartTheTimer();
        Y.Node.getDOMNode(doc.one('#pause')).disabled = false;
    });

    return false;
}

/**
 * Pause the playback/recording
 */
function pause_rp() {
    YUI().use('node', function(Y) {
        var object = Y.one('object'),
        doc = object.get('contentDocument');
        
        Y.Node.getDOMNode(doc.one('applet')).PAUSE_VIDEO();
        PauseTheClock();
    });

    return false;
}

/**
 * Stop recording
 */
function stop_rp() {    
    YUI().use('node', function(Y) {
        var object = Y.one('object'),
        doc = object.get('contentDocument');
        
        Y.Node.getDOMNode(doc.one('applet')).STOP_VIDEO();
        StopTheClock();
        Y.Node.getDOMNode(doc.one('#rec')).disabled=false;
        Y.Node.getDOMNode(doc.one('#stop')).disabled=true;
        Y.Node.getDOMNode(doc.one('#pause')).disabled=true;
        Y.Node.getDOMNode(doc.one('#play')).disabled=false;
    });

    return false;
}

/**
 * Method to upload the recorded video to
 * a tmp location on server.
 */
function upload_rp() {
    YUI().use('node', function(Y) {
        var object = Y.one('object'),
        doc = object.get('contentDocument');
        
        var filename = Y.Node.getDOMNode(doc.one('#filename')),
            fileloc = Y.Node.getDOMNode(doc.one('#fileloc')),
            duration = Y.Node.getDOMNode(doc.one('#Timer'));
        /*
        if (!duration.value.trim()) {
            alert(parent.mediacapture['norecordingfound']);
            return false;
        }
        */

        filename.value = filename.value.replace('.mp4', '');
        filename.value = filename.value.replace('*', '');
        if (!filename.value) {
            alert(parent.mediacapture['nonamefound']);
            filename.value = '*.mp4';
            return false;
        }
        filename.value = filename.value + '.mp4';

        Y.Node.getDOMNode(doc.one('applet')).UPLOAD_VIDEO(String(filename.value));
        fileloc.value = encodeURIComponent(decodeURIComponent(fileloc.value) + '/' + filename.value);
        Y.Node.getDOMNode(doc.one('form')).submit();
    });
}

/**
 * Submits the video recording to the server 
 * for processing upload
 */
function submit_flash_video() {
    YUI().use('node', function(Y) {
        var object = Y.one('object'),
        doc = object.get('contentDocument');

        var filename = Y.Node.getDOMNode(doc.one('#filename')),
            fileloc = Y.Node.getDOMNode(doc.one('#fileloc'));

        filename.value = filename.value.replace('.flv', '');
        filename.value = filename.value.replace('*', '');
        if (!filename.value) {
            alert(parent.mediacapture['nonamefound']);
            filename.value = '*.flv';
            return false;
        }
        
        filename.value = filename.value + '.flv';
        fileloc.value = fileloc.value;

        var duration = 90; // max-duration

        win = Y.Node.getDOMNode(object.get('contentDocument'));

        // Create a YUI instance using io-base module.
        YUI({ win: win }).use('node', 'io-base', function(innerY) { 
            var uri = decodeURIComponent(innerY.one('#posturl').get('value'));
            // Define a function to handle the response data.
            function complete(id, o) {
                var id = id; // Transaction ID.
                var data = o.responseText; // Response data.
                if (data === 'NONE') {
                    duration = 0;
                }
            };

            // Subscribe to event "io:complete"
            innerY.on('io:complete', complete, innerY);

            // Make an HTTP POST request to posturl.
            cfg = {
                method: 'POST',  
                data:   'type=check_duration',
                sync:true
            };
            var request = innerY.io(uri, cfg);  
        });

        if (duration <= 0) {    
            alert(parent.mediacapture['norecordingfound']);
            return false;
        }

        Y.Node.getDOMNode(doc.one('form')).submit();
    });

}
