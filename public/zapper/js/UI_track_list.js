var trackListKeyHandler = (function () {
    var TYPE_MAP = {};

    var _i = ENABLE.input;
    var MAX_ITME_DISPLAY = 10;
    var SCROLLING_THRESHOLD = 5;
    var TRACK_MODE_AUDIO = 1, TRACK_MODE_TEXT = 2;
    var AUDIO_KEY_LIST = [_i.KEY_GREEN, _i.KEY_MEDIA, "a"];
    var TEXT_KEY_LIST = [_i.KEY_RED, _i.KEY_SUBTITLE, _i.KEY_CLOSED_CAPTION, _i.KEY_SUBTITLE_CC, "t"];

    var cur_index = 0;
    var ui_index = 0;
    var display_timeout = null;
    var apply_timeout = null;
    var dom_track_list = null;
    var array = [];
    var mode = 0;

    function drawTrackListFocus() {
        document.querySelectorAll('#track_list > li').forEach(function (ele, i) {
            // update focus
            if (ui_index === i)
                ele.classList.add("focus");
            else
                ele.classList.remove("focus");
        });
    };

    function isListShowing() {
        return dom_track_list.style.display === "";
    }
    function toggleListVisibility(show) {
        if (show)
            dom_track_list.style.display = "";
        else
            dom_track_list.style.display = "none";
    }

    function drawTrackList(begin) {
        document.querySelectorAll('#track_list > li').forEach(function (ele, i) {
            if (i < array.length) {
                var lang, type, default_txt;
                if (mode === TRACK_MODE_AUDIO) {
                    lang = array[i + begin].lang;
                    type = array[i + begin].streamType;
                    default_txt = "Audio Track " + (i + 1);
                } else if (mode === TRACK_MODE_TEXT) {
                    lang = array[i + begin].lang;
                    type = array[i + begin].component_type;
                    default_txt = "Text Track " + (i + 1);
                }
                ele.style.display = "block";
                ele.children[0].innerText = LANGUAGE_MAP[lang] || lang || default_txt;
                ele.children[1].innerText = TYPE_MAP[type] || "";

            } else {
                ele.children[0].innerText = "";
                ele.children[1].innerText = "";
                ele.style.display = "none";
            }
        });
    };

    function applySelectedText(dtvCtrl) {
        if (!array[cur_index]) {
            console.error("ERR: wrong index: " + cur_index);
            return;
        }

        if (cur_index === 0) {  // OFF
            dtvCtrl.unsetTextTrack();
        } else if (array[cur_index].id >= 0) {
            dtvCtrl.setTextTrack(array[cur_index].id);
        }
    };

    function findTextArrayAndIndex(dtvCtrl) {
        array = [{ lang: "OFF" }];
        var result = dtvCtrl.getTextTrack();
        array = array.concat(result.tracks);
        cur_index = result.selected + 1;
        if (cur_index < 0)
            cur_index = 0;
    }

    function updateTrackList(dtvCtrl) {
        if (mode === TRACK_MODE_AUDIO) {
            var result = dtvCtrl.getAudioTrack();
            cur_index = result.selected;
            array = result.tracks;
        } else if (mode === TRACK_MODE_TEXT) {
            findTextArrayAndIndex(dtvCtrl);
        }
        updateFocus(cur_index);
    };

    function updateFocus(index) {
        // extend timeout for related key pressed
        timeoutToHide(10);
        if (index < 0 || index >= array.length)
            return;

        // long array, need to handle scrolling
        if (array.length > MAX_ITME_DISPLAY) {

            // calculate UI index
            if (index <= SCROLLING_THRESHOLD) {
                ui_index = index;
                drawTrackList(0);
            } else if (index >= array.length - SCROLLING_THRESHOLD) {
                ui_index = MAX_ITME_DISPLAY - array.length + index;
                drawTrackList(array.length - MAX_ITME_DISPLAY);
            } else {
                ui_index = SCROLLING_THRESHOLD;
                drawTrackList(index - SCROLLING_THRESHOLD);
            }
            drawTrackListFocus();

            // always update index;
        } else {
            ui_index = index;
            drawTrackList(0);
            drawTrackListFocus();
        }
        cur_index = index;
    };

    function timeoutToApplySelected(sec, dtvCtrl) {
        if (apply_timeout)
            clearTimeout(apply_timeout);
        apply_timeout = setTimeout(function (dtvCtrl) {
            apply_timeout = null;
            if (mode === TRACK_MODE_AUDIO) {
                dtvCtrl.setAudioTrack(array[cur_index].id);
            } else if (mode === TRACK_MODE_TEXT) {
                applySelectedText(dtvCtrl);
            }
            timeoutToHide(1);
        }, sec * 1000, dtvCtrl);
    }

    function timeoutToHide(sec) {
        if (display_timeout)
            clearTimeout(display_timeout);
        display_timeout = setTimeout(function () {
            toggleListVisibility(false);
            display_timeout = null;
        }, sec * 1000)
    }

    function toggleOrTrackList(dtvCtrl, newMode) {
        if (!isListShowing()) {
            mode = newMode;
            updateTrackList(dtvCtrl);
            timeoutToHide(10);
            toggleListVisibility(true);
        } else {
            if (newMode !== mode) {
                toggleListVisibility(false);
            } else {
                updateFocus((cur_index + 1) % array.length);
                timeoutToApplySelected(1, dtvCtrl);
            }
        }
    }

    return function (key, dtvCtrl) {
        if (!dom_track_list) {
            dom_track_list = document.getElementById("track_list");
            for (var i = 0; i < MAX_ITME_DISPLAY; i++) {
                var dom_li = document.createElement("LI");
                var dom_div_name = document.createElement("div");
                var dom_div_remark = document.createElement("div");
                dom_div_name.classList.add("name");
                dom_div_remark.classList.add("remark");
                dom_li.appendChild(dom_div_name);
                dom_li.appendChild(dom_div_remark);
                dom_track_list.appendChild(dom_li);
            }
        }

        // case1: hide when some keys pressed
        if (key === _i.KEY_BACK || key === _i.KEY_EXIT || key === "Escape") {
            timeoutToHide(0);

            // cancel audio/text selection
            if (apply_timeout)
                clearTimeout(apply_timeout);
            // return false to allow key processed by other key handlers
            return false;
        }

        // case2: toggle show, hide if text key pressed but audio showing
        if (TEXT_KEY_LIST.indexOf(key) >= 0) {
            toggleOrTrackList(dtvCtrl, TRACK_MODE_TEXT);
            return true;
        }
        if (AUDIO_KEY_LIST.indexOf(key) >= 0) {
            toggleOrTrackList(dtvCtrl, TRACK_MODE_AUDIO);
            return true;
        }

        // case3: react when showing
        if (isListShowing()) {
            if (key === _i.KEY_UP) {
                updateFocus(cur_index - 1);
                return true;
            } else if (key === _i.KEY_DOWN) {
                updateFocus(cur_index + 1);
                return true;
            } else if (key === _i.KEY_ENTER) {
                timeoutToApplySelected(0, dtvCtrl);
                return true;
            }
        }

        return false;
    }
})();
