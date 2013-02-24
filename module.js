/**
 * @file Javascript file to handle the audio/video recording
 * of the mediacapture plugin
 */

M.repository_mediacapture = {};

M.repository_mediacapture.init = function(Y) {
    Y.one('*[name="browserplugins"]').set('value', JSON.stringify(BrowserPlugins));
    Y.one('*[name="browserdetect"]').set('value', JSON.stringify(BrowserDetect));

    var audio = Y.one("#id_recordaudio"),
        video = Y.one("#id_recordvideo");

    if (audio) {
        audio.on("click", function (e) {
            Y.one('*[name="type"]').set('value', 'audio');
            Y.one('#mform1').submit();
        });
    }

    if (video) {
        video.on("click", function (e) {
            Y.one('*[name="type"]').set('value', 'video');
            Y.one('#mform1').submit();
        });
    }
}

/*
 * Returns an object with found plugins and their versions
 * BrowserPlugins['java'] returns version of JRE
 * BrowserPlugins['quicktime'] returns version of Apple QT player
 * BrowserPlugins['flash'] returns Adobe Flash player version
 */
var BrowserPlugins = (function(){
    var found = {};
    var version_reg = /[0-9]+.[0-9]+/;

    /*
     * Differentiate between IE (detection via ActiveXObject)
     * and the rest (detection via navigator.plugins)
     */
    if (window.ActiveXObject) {
        var plugin_list = {
            flash: 'ShockwaveFlash.ShockwaveFlash.1',
            quicktime: 'QuickTime.QuickTime'
        }

        for (var plugin in plugin_list){
            var version = msieDetect(plugin_list[plugin]);
            if (version){
                var version_reg_val = version_reg.exec(version);
                found[plugin] = (version_reg_val && version_reg_val[0]) || '';
            }
        }

        if (navigator.javaEnabled()){
            found['java'] = '';
        }
    } else {
        var plugins = navigator.plugins;
        var reg = /Flash|Java|QuickTime/;
        for (var i = 0; i < plugins.length; i++) {
            var reg_val = reg.exec(plugins[i].description);
            if (reg_val){
                var plugin = reg_val[0].toLowerCase();
                /*
                 * Search in version property, if not available concat name and description
                 * and search for a version number in there
                 */
                var version = plugins[i].version ||
                    (plugins[i].name + ' ' + plugins[i].description);
                var version_reg_val = version_reg.exec(version);
                if (!found[plugin]) {
                    found[plugin] = (version_reg_val && version_reg_val[0]) || '';
                }
            }
        }
    }

    return found;

    /*
     * Return version number if plugin installed
     * Return true if plugin is installed but no version number found
     * Return false if plugin not found
     */
    function msieDetect(name){
        try {
            var active_x_obj = new ActiveXObject(name);
            try {
                return active_x_obj.GetVariable('$version');
            } catch(e) {
                try {
                    return active_x_obj.GetVersions();
                } catch (e) {
                    try {
                        var version;
                        for (var i = 1; i < 9; i++) {
                            if (active_x_obj.isVersionSupported(i + '.0')){
                                version = i;
                            }
                        }
                        return version || true;
                    } catch (e) {
                        return true;
                    }
                }
            }
        } catch(e){
            return false;
        }
    }
})();

/*
 * Returns an object with the browser name, version and OS details
 * BrowserDetect.browser returns browser name
 * BrowserDetect.version returns browser version
 * BrowserDetect.OS returns Operating System, Win, Mac, Linux
 */
var BrowserDetect = {
    init: function () {
        this.browser = this.searchString(this.dataBrowser) || "an unknown browser";
        this.version = this.searchVersion(navigator.userAgent)
            || this.searchVersion(navigator.appVersion)
            || "an unknown version";
        this.OS = this.searchString(this.dataOS) || "an unknown OS";
    },
    searchString: function (data) {
        for(var i = 0; i < data.length; i++) {
            var dataString = data[i].string;
            var dataProp = data[i].prop;
            this.versionSearchString = data[i].versionSearch || data[i].identity;
            if (dataString) {
                if (dataString.indexOf(data[i].subString) != -1) {
                    return data[i].identity;
                }
            }
            else if (dataProp) {
                return data[i].identity;
            }
        }
    },
    searchVersion: function (dataString) {
        var index = dataString.indexOf(this.versionSearchString);
        if (index == -1) {
            return;
        }
        return parseFloat(dataString.substring(index+this.versionSearchString.length+1));
    },
    dataBrowser: [
        {
            string: navigator.userAgent,
            subString: "Chrome",
            identity: "Chrome"
        },
        {
            string: navigator.vendor,
            subString: "Apple",
            identity: "Safari",
            versionSearch: "Version"
        },
        {
            prop: window.opera,
            identity: "Opera",
            versionSearch: "Version"
        },
        {
            string: navigator.userAgent,
            subString: "Firefox",
            identity: "Firefox"
        },
        {
            string: navigator.userAgent,
            subString: "MSIE",
            identity: "Explorer",
            versionSearch: "MSIE"
        },
        {
            string: navigator.userAgent,
            subString: "Gecko",
            identity: "Mozilla",
            versionSearch: "rv"
        },
        {       // for older Netscapes (4-)
            string: navigator.userAgent,
            subString: "Mozilla",
            identity: "Netscape",
            versionSearch: "Mozilla"
        }
    ],
    dataOS : [
        {
            string: navigator.platform,
            subString: "Win",
            identity: "Windows"
        },
        {
            string: navigator.platform,
            subString: "Mac",
            identity: "Mac"
        },
        {
               string: navigator.userAgent,
               subString: "iPhone",
               identity: "iPhone/iPod"
        },
        {
            string: navigator.platform,
            subString: "Linux",
            identity: "Linux"
        }
    ]

};

BrowserDetect.init();