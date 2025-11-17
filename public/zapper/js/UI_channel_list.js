var channelListKeyHandler = (function () {
    let _i = ENABLE.input;
    var cur_index = 0;
    var ui_index = 0;
    var timeout_handle = null;
    var dom_ch_list = null;
    var array = [];
    var MAX_ITME_DISPLAY = 10;
    var SCROLLING_THRESHOLD = 5;

    function drawChannelListFocus() {
        document.querySelectorAll('#ch_list > li').forEach(function (ele, i) {
            // update focus
            if (ui_index === i)
                ele.classList.add("focus");
            else
                ele.classList.remove("focus");
        });
    };

    function drawChannelList(begin) {
        document.querySelectorAll('#ch_list > li').forEach(function (ele, i) {
            if (i < array.length) {
                ele.style.display = "block";
                ele.children[0].innerText = array[i + begin].name;
                ele.children[1].innerText = array[i + begin].desc;

            } else {
                ele.children[0].innerText = "";
                ele.children[1].innerText = "";
                ele.style.display = "none";
            }
        });
    };

    function updateChannelList(cur_index) {
        array = SYSTEM_CONFIG.getDTV_Array();
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
                drawChannelList(0);
            } else if (index >= array.length - SCROLLING_THRESHOLD) {
                ui_index = MAX_ITME_DISPLAY - array.length + index;
                drawChannelList(array.length - MAX_ITME_DISPLAY);
            } else {
                ui_index = SCROLLING_THRESHOLD;
                drawChannelList(index - SCROLLING_THRESHOLD);
            }
            drawChannelListFocus();

            // always update index;
        } else {
            ui_index = index;
            drawChannelList(0);
            drawChannelListFocus();
        }
        cur_index = index;
    };

    function timeoutToHide(sec) {
        if (timeout_handle) {
            clearTimeout(timeout_handle);
            timeout_handle = null;
        }
        timeout_handle = setTimeout(function () {
            dom_ch_list.style.display = "none";
        }, sec * 1000)
    }

    return function (key, dtvCtrl) {
        if (!dom_ch_list) {
            dom_ch_list = document.getElementById("ch_list");
            for (var i = 0; i < MAX_ITME_DISPLAY; i++) {
                var dom_li = document.createElement("li");
                var dom_div_num = document.createElement("div");
                var dom_div_name = document.createElement("div");
                dom_div_num.classList.add("ch_num");
                dom_div_name.classList.add("ch_name");
                dom_li.appendChild(dom_div_num);
                dom_li.appendChild(dom_div_name);
                dom_ch_list.appendChild(dom_li);
            }
        }

        // case1: hide when some keys pressed
        if ((key >= _i.KEY_0 && key <= _i.KEY_9)
            || key === _i.KEY_BACK || key === _i.KEY_EXIT || key === "Escape") {
            timeoutToHide(0);

            // return false to allow key processed by other key handlers
            return false;
        }

        // case2: toggle hide/show
        if (key === _i.KEY_MENU || key === _i.KEY_GUIDE || key === "m" || key === "g") {
            if (dom_ch_list.style.display === "none") {
                updateChannelList(dtvCtrl.getCurrentChannelIndex());
                timeoutToHide(10);
                dom_ch_list.style.display = "";
            } else {
                timeoutToHide(0);
            }
            return true;
        }

        // case3: react when showing
        if (dom_ch_list.style.display === "") {
            if (key === _i.KEY_UP) {
                updateFocus(cur_index - 1);
                return true;
            } else if (key === _i.KEY_DOWN) {
                updateFocus(cur_index + 1);
                return true;
            } else if (key === _i.KEY_ENTER) {
                dtvCtrl.zapIndex(cur_index);
                timeoutToHide(0);
                return true;
            }
        }

        return false;
    }
})();
