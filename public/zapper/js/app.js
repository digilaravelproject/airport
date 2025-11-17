let idx = 0;
let zapName = "";
let _i = ENABLE.input;
let zapTimer;
let saveLastChannelHandler = null;
let zapChannelHandler = null;
const SAVE_LAST_CH_TIMEOUT = 180 * 1000;   // 180s
const LS_LAST_CH = "lastChannel";

class ChannelHistory {
  _maxSize = 2;
  _history = [null, null];
  _curIdx = 0;

  constructor() { }

  addNewChannelRecord(channel)  {
    this.addNewChannelRecordWithIdx(-1, channel);
  }

  addNewChannelRecordWithIdx(idx, channel)  {
    // There will only be at most one idx = -1 (from zap(), which always trigger a reload), 
    // and there is unlikely to update the channel list (invalid or out-out-date index) 
    // in real time / without cause a reload
    if (this._history[this._curIdx]?.idx === idx) {
      return;
    }

    this._advance();
    this._history[this._curIdx] = {
      idx: idx,
      channel: structuredClone(channel)
    }
  }

  getPrevChannelHistory() {
    if (this._peek() === null) {
      return null;
    }
    this._advance();
    return structuredClone(this._history[this._curIdx]);
  }

  _advance() {
    this._curIdx = (this._curIdx + 1) % this._maxSize;
  }

  _peek() {
    const peekIdx = (this._curIdx + 1) % this._maxSize;
    return this._history[peekIdx];
  }
}
window.runtimeChannelHistory = new ChannelHistory();

const dtvCtrl = {
    zapIndex(id) {
        idx = id;
        zapIndex();
    },
    getCurrentChannelIndex() {
        return idx;
    },
    getTextTrack() {
        return videoControl.getTextTrack();
    },
    setTextTrack(id) {
        return videoControl.setTextTrack(id);
    },
    unsetTextTrack() {
        return videoControl.unsetTextTrack();
    },
    getAudioTrack() {
        return videoControl.getAudioTrack();
    },
    setAudioTrack(id) {
        return videoControl.setAudioTrack(id);
    }
};
const videoControl = {
    get: ENABLE.player.getInstance,
    src: null,
    _resetAll() {
        ENABLE.player.releaseAll();
    },
    getTextTrack() {
        return this.get(0).getTextTrack();
    },
    setTextTrack(id) {
        return this.get(0).setTextTrack(id);
    },
    unsetTextTrack() {
        return this.get(0).unsetTextTrack();
    },
    getAudioTrack() {
        return this.get(0).getAudioTrack();
    },
    setAudioTrack(id) {
        return this.get(0).setAudioTrack(id);
    },
    getPlayPosition() {
        return this.get(0).getPlayPosition();
    },
    getDuration() {
        return this.get(0).getDuration();
    },
    isLive() {
        return this.get(0).isLiveStream();
    },
    getSource() {
        return this.get(0).getSource();
    },
    stop() {
        if (Array.isArray(this.src)) {
            this.src.forEach ((e,i) => this.get(i).stop());
        } else {
            this.get(0).stop();
        }
    },
    _storedCallback: null,
    setEventCallback(fn) {
        this._storedCallback = fn;
    },
    setSource(src, config) {
        if (Array.isArray(this.src)) {
            this._resetAll();
        }
        this.src = src;

        if (Array.isArray(src)) {
            const len = src.length;

            if (typeof this._storedCallback === "function" && src.length < 5) {
                src.forEach((e, i) => this.get(i).setEventCallback(this._storedCallback));
            }

            const { status_code, width, height } = ENABLE.output.getHdmiStatus();
            // Default to innerWidth and innerHeight
            let w = innerWidth / 2;
            let h = innerHeight / 2;

            if (status_code === ENABLE.status.SUCCESS && width != null && height != null) {
                // HDMI is connected and we have valid width and height
                // Limit to 1920x1080
                w = Math.min(width, 1920) / 2;
                h = Math.min(height, 1080) / 2;
            }
            if (len === 0) {
                console.error("src array is empty");
            } else if (len > 4) {
                console.error("src array length > 4 is not supported");
            } else if (len === 1) {
                this.get(0).setSource(src[0], config);
                this.get(0).setVideoRect(0, 0, -1, -1);
            this.get(0).setResizeMode(ENABLE.preference.RESIZE_MODE_FILL);
                this.get(0).setAudioRendering(true);
            } else if (len >= 2 && len <= 4) {
                // setSource will switch player, we should do it first
                src.forEach ((e,i) => this.get(i).setSource(e, config));
                // no audio rendering for all players
                this.get(0).setAudioRendering(false);
                if (util.isScreenVertical()) {
                    w = w * 2;
                    h = h * 2;
                    // Vertical screen layout, but getHdmiStatus() still returns horizontal
                    const videoHeight = w / len;
                    let videoWidth = (videoHeight * 16) / 9;
                    let xOffset = Math.max(0, (h - videoWidth) / 2);
                    let yOffset = 0;

                    if (videoWidth > h) {
                        videoWidth = h;
                        const adjustedVideoHeight = (videoWidth * 9) / 16;
                        yOffset = Math.max(0, (w - (adjustedVideoHeight * len)) / 2);
                        for (let i = 0; i < len; i++) {
                            this.get(i).setVideoRect(0, yOffset + i * adjustedVideoHeight, videoWidth, adjustedVideoHeight);
                        }
                    } else {
                        for (let i = 0; i < len; i++) {
                            this.get(i).setVideoRect(xOffset, i * videoHeight, videoWidth, videoHeight);
                        }
                    }
                } else {
                    // Horizontal screen layout
                    switch(len) {
                        case 2:
                            this.get(0).setVideoRect(0, h/2, w, h);
                            this.get(1).setVideoRect(w, h/2, w, h);
                            break;
                        case 3:
                            this.get(0).setVideoRect(0, 0, w, h);
                            this.get(1).setVideoRect(w, 0, w, h);
                            this.get(2).setVideoRect(0, h, w, h);
                            break;
                        case 4:
                            this.get(0).setVideoRect(0, 0, w, h);
                            this.get(1).setVideoRect(w, 0, w, h);
                            this.get(2).setVideoRect(0, h, w, h);
                            this.get(3).setVideoRect(w, h, w, h);
                            break;
                    }
                }
            }
        } else {
            if (typeof this._storedCallback === "function") {
                this.get(0).setEventCallback(this._storedCallback);
            }
            this.get(0).setSource(src, config);
            this.get(0).setVideoRect(0, 0, -1, -1);
        this.get(0).setResizeMode(ENABLE.preference.RESIZE_MODE_FILL);
            this.get(0).setAudioRendering(true);
        }
    }
}
let networkCallbackQueue = [];
function addNetworkCallback(callback) {
    const info = ENABLE.network.getInterfaceInfo().result ?? [];
    const networkAvailable = info.reduce(((acc, net) => acc || net.linking), false);
    if (networkAvailable) {
      callback();
    } else {
      networkCallbackQueue.push(callback);
    }
}

function keyHandler(e) {
    var key = e.key;
    var NUM_KEYS = [_i.KEY_0, _i.KEY_1, _i.KEY_2, _i.KEY_3, _i.KEY_4, _i.KEY_5,
    _i.KEY_6, _i.KEY_7, _i.KEY_8, _i.KEY_9, _i.KEY_ENTER];

    // return to skip the following keys comparison
    if (noticeKeyHandler(key)) {
        return;
    }
    if (channelListKeyHandler(key, dtvCtrl)) {
        trackListKeyHandler(_i.KEY_BACK);
        hideInfoBar();
        return;
    }
    if (trackListKeyHandler(key, dtvCtrl)) {
        channelListKeyHandler(_i.KEY_BACK);
        hideInfoBar();
        return;
    }
    if (key === _i.KEY_CHANNELUP || key === "PageUp") {
        idx++;
        zapIndex();
    } else if (key === _i.KEY_CHANNELDOWN || key == "PageDown") {
        idx--;
        zapIndex();
    } else if (NUM_KEYS.indexOf(key) >= 0) {
        zapNumber(key);
    } else if (key === _i.KEY_INFO || key === "i") {
        showInfoBar();
    } else if (key === _i.KEY_BACK) {
      const channelRecord = runtimeChannelHistory.getPrevChannelHistory();
      if (channelRecord === null) {
        return;
      }

      if (channelRecord.idx >= 0) {
        idx = channelRecord.idx;
        zapIndex();
      } else {
        zap(channelRecord.channel);
      }
    }
};

function zapNumber(key) {
    let zapTimeout;
    if (key !== _i.KEY_ENTER && zapName.length < 4) {
        zapName = zapName.concat(key);
        console.info("Digit input: " + zapName);
        showDigitBar(zapName);

        // Wait for next input digit
        zapTimeout = 1000;
    } else if (key === _i.KEY_ENTER && zapName.length > 0) {
        zapTimeout = 100;
    } else {
        return;
    }

    if (zapTimer)
        clearTimeout(zapTimer);
    zapTimer = setTimeout(function () {
        idx = SYSTEM_CONFIG.findDTVChannelIndex(zapName);
        zapIndex();
        zapName = "";
    }, zapTimeout);
}

function zapIndex() {
    let length = SYSTEM_CONFIG.getDTV_Array().length;
    if (idx < 0)
        idx = length - 1;
    if (idx >= length)
        idx = 0;

    let dtv = SYSTEM_CONFIG.getDTV(idx);
    showInfoBar(dtv.name, dtv.desc);
    let src = SYSTEM_CONFIG.getDTV_SourceString(idx);
    let zapData = SYSTEM_CONFIG.getDTV_SourceConfig(idx);

    if (src.indexOf("&rtspsvrtype=") >= 0) {
        src = src.split("&rtspsvrtype=")[0];
    }
    if (src !== "") {
        if (zapChannelHandler != null) {
            clearTimeout(zapChannelHandler);
            zapChannelHandler = null;
        }
        zapChannelHandler = setTimeout(function () {
            videoControl.setSource(src, zapData);
            runtimeChannelHistory.addNewChannelRecordWithIdx(idx, {url: src, ...zapData});
            saveLastChannel();
        }, 250);
    }
};

function zap(searchParams) {
    var param = new URLSearchParams(searchParams);
    const json = JSON.parse(param.get("lineup"));
    showInfoBar(json.name, json.desc);
    videoControl.setSource(SYSTEM_CONFIG.getZapperSourceString(json), json);
    runtimeChannelHistory.addNewChannelRecord(searchParams);
}

function saveLastChannel(saveNow) {
    let ret = ENABLE.system.getINIValue("webapp.save_last_channel");
    if (ret.result !== "true")
        return;

    // cancel previous one
    if (saveLastChannelHandler !== null) {
        clearTimeout(saveLastChannelHandler);
        saveLastChannelHandler = null;
    }

    let dtv = SYSTEM_CONFIG.getDTV(idx);
    if (saveNow) {
        localStorage.setItem(LS_LAST_CH, dtv.name);
    } else {
        saveLastChannelHandler = setTimeout(function () {
            localStorage.setItem(LS_LAST_CH, dtv.name);
        }, SAVE_LAST_CH_TIMEOUT);
    }
}

function getChannelIndex() {
    // 1. try to get channel from URL parameter
    let idx = getChannalNumberFromUrl();
    if (idx >= 0)
        return idx;

    // 2. try to find last channel
    let ret = ENABLE.system.getINIValue("webapp.save_last_channel");
    if (ret.result === "true") {
        // if stored channel number found
        if (localStorage.hasOwnProperty(LS_LAST_CH))
            return SYSTEM_CONFIG.findDTVChannelIndex(localStorage.getItem(LS_LAST_CH));
    }

    // 3. try to find 1st channel from lineup
    idx = SYSTEM_CONFIG.findStartingChannel();
    if (idx >= 0)
        return idx;

    // 4. use 1st channel by default
    return 0;
}

function systemEventHandler(obj) {
    switch (obj.event) {
        case ENABLE.network.NETWORK_READY:
            for (const callback of networkCallbackQueue) {
                callback();
            }
            networkCallbackQueue = [];
            break;
        case ENABLE.system.APP_STOP:
            saveLastChannel(true);
            videoControl.stop();
            break;
        case ENABLE.system.APP_RESTART:
            zapIndex();
            break;
    }
}

function zapNextChannel() {
    idx++;
    zapIndex();
}

function playerEventHandler(obj) {
    let dtv = SYSTEM_CONFIG.getDTV(idx);
    if (obj.event === ENABLE.player.VIDEOEVENT_ERROR) {
        if (dtv.retry_on_error !== undefined) {
            if (!dtv.retry_on_error) {
                console.debug("zapping to next channel due to lineup param retry_on_error = false");
                zapNextChannel();
            }
        } else if (ENABLE.system.getINIValue("webapp.retry_on_error").result === "false") {
            console.debug("zapping to next channel due to ini webapp.retry_on_error = false");
            zapNextChannel();
        }
    } else if (obj.event === ENABLE.player.VIDEOEVENT_END_OF_STREAM) {
        if (videoControl.isLive()) {
            const {source} = videoControl.getSource();
            if (source != null && source.startsWith("dolbyio://")) {
                showErrorMessage("Live broadcast Ended", "The live broadcast has ended from the server side.", 5);
                return;
            }
        }
        console.debug("zapping to next channel upon END_OF_STREAM");
        zapNextChannel();
    } else if (obj.event === ENABLE.player.VIDEOEVENT_PLAYBACK_STOPPED) {
        // RTSP source does not send EOS event, work around for now
        if (dtv.type === "rtsp") {
            let playPosition = videoControl.getPlayPosition().result;
            let duration = videoControl.getDuration().result;
            if (Math.abs(duration - playPosition) < 5000 && duration > 0) {
                let loop = (dtv.loop_on_end === undefined) ? JSON.parse(ENABLE.system.getINIValue("webapp.loop_on_end").result || false) : dtv.loop_on_end;
                if (loop) {
                    videoControl.stop();
                    setTimeout(zapIndex, 100);
                }
                else {
                    zapNextChannel();
                }
            }
        }
        // work around, need review later
    }
}

function getChannalNumberFromUrl() {
    let params = (new URL(document.location)).searchParams;
    let channelNum = parseInt(params.get("channum"));
    if (channelNum >= 0)
        return SYSTEM_CONFIG.findDTVChannelIndex(channelNum);
    let channelName = params.get("channame");
    let channelDesc = params.get("chandesc");
    return SYSTEM_CONFIG.findDTVChannelIndexByParam(channelName, channelDesc);
}
