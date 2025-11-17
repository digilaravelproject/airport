// Helper for channel configuration
SYSTEM_CONFIG = (function () {
    var _storage_key = "apollo_zapper_key";
    var _data_migrate = null;
    var _migrate_handler = null;

    function removeAllLineup() {
        var lineup = util.getEELMChannels();
        if (lineup && Array.isArray(lineup) && lineup.length > 0) {
            for (let i = 0; i < lineup.length; i++) {
                if (util.deleteEELMChannel(lineup[i]) === false) {
                    return false;
                }
            }
        }
        return true;
    }
    function saveLineup(lineup) {
        for (let i = 0; i < lineup.length; i++) {
            if (util.postEELMChannel(lineup[i]) === false) {
                return false;
            }
        }
        return true;
    }
    async function migration() {
        var oldMasterLineup = ENABLE.persistStorage.getItem(_storage_key).result;
        if (oldMasterLineup) {
            window.migration = true;
            let success = removeAllLineup();
            if (!success) {
                console.error("Failed to remove old lineup from EELM");
                return false;
            }
            success = saveLineup(JSON.parse(oldMasterLineup));
            if (!success) {
                console.error("Failed to save new lineup to EELM");
                return false;
            }
            ENABLE.persistStorage.removeItem(_storage_key);
            _migrate_handler = setTimeout(() => {
                window.migration = false;
            }, 5000)
            console.log("persistStorage migration done");
        }
        if (_data_migrate) {
            if (_migrate_handler) {
                clearTimeout(_migrate_handler);
                _migrate_handler = null;
            }
            window.migration = true;
            let success = removeAllLineup();
            if (!success) {
                console.error("Failed to remove old lineup from EELM");
                return false;
            }
            success = saveLineup(_data_migrate);
            if (!success) {
                console.error("Failed to save new lineup to EELM");
                return false;
            }
            _data_migrate = null;
            _migrate_handler = setTimeout(() => {
                window.migration = false;
            }, 5000)
            console.log("INI migration done");
        }
        return true;
    }
    async function retryMigration() {
        await util.checkAndRetryEelmConnection();
        return await migration();
    }
    function applyMasterLineup() {
        var masterLineup = util.getEELMChannels();
        if (masterLineup && Array.isArray(masterLineup)) {
            _config.DTV = masterLineup;
            SYSTEM_CONFIG.generateLookupMap();
            localStorage.setItem(_storage_key, JSON.stringify(_config));
        }
    }
    async function retryGettingMaster() {
        await util.checkAndRetryEelmConnection();
        applyMasterLineup();
    }

    function compareSource(input1, input2) {
        // Check if both inputs are of the same type
        if (typeof input1 !== typeof input2) return false;

        // Handle strings
        if (typeof input1 === 'string') return input1 === input2;

        // Handle arrays
        if (Array.isArray(input1)) {
            if (input1.length !== input2.length) return false;
            return input1.every((item, index) => item === input2[index]);
        }

        // Return false for unsupported types
        return false;
    }

    function compareConfig(config1, config2) {
        // Create copies of configs to avoid modifying originals
        const normalizedConfig1 = { ...config1 };
        const normalizedConfig2 = { ...config2 };

        // Treat empty encryption_type and encrypting_server as undefined (no change)
        if (normalizedConfig1.encryption_type === '') delete normalizedConfig1.encryption_type;
        if (normalizedConfig1.encrypting_server === '') delete normalizedConfig1.encrypting_server;
        if (normalizedConfig2.encryption_type === '') delete normalizedConfig2.encryption_type;
        if (normalizedConfig2.encrypting_server === '') delete normalizedConfig2.encrypting_server;

        // Remove keys with undefined values
        for (const key in normalizedConfig1) {
            if (normalizedConfig1[key] === undefined) delete normalizedConfig1[key];
        }
        for (const key in normalizedConfig2) {
            if (normalizedConfig2[key] === undefined) delete normalizedConfig2[key];
        }

        // Compare keys and values
        const keys1 = Object.keys(normalizedConfig1);
        const keys2 = Object.keys(normalizedConfig2);

        if (keys1.length !== keys2.length) return false;

        for (const key of keys1) {
            if (normalizedConfig1[key] !== normalizedConfig2[key]) return false;
        }

        return true;
    }

    window.addEventListener("zapperEvent", (e) => {
        if (window.migration)
            return;
        let currentSrc = SYSTEM_CONFIG.getDTV_SourceString(idx);
        let currentData = SYSTEM_CONFIG.getDTV_SourceConfig(idx);
        applyMasterLineup();
        let updatedSrc = SYSTEM_CONFIG.getDTV_SourceString(idx);
        let updatedData = SYSTEM_CONFIG.getDTV_SourceConfig(idx);

        if (compareSource(currentSrc, updatedSrc) && compareConfig(currentData, updatedData)) {
            // no change in source or config
            const { name, desc } = SYSTEM_CONFIG.getDTV(idx);
            document.getElementById("digit").innerText = (desc || "") + " " + name;
            return;
        }
        zapIndex();
    });

    function getSourceString(dtv) {
        if (typeof dtv.url === "string")
            return dtv.url;
        if (Array.isArray(dtv.url))
            return dtv.url;
        var s = dtv.type + '://' + dtv.addr;
        if (dtv.type === 'udp' || dtv.type === 'igmp' || dtv.type === 'igmpv3') {
            // multicast channels
            s = 'udp://' + dtv.addr + ':' + dtv.port;

            if (dtv.freq_band !== undefined) {
                s += "/" + dtv.freq_band;
            }
            if (dtv.type === 'igmpv3') {
                if (dtv.igmpv3_addr_list.length > 0) {
                    var addr_list = [].concat(dtv.igmpv3_addr_list);
                    s += '?mcast=ssm_' + dtv.igmpv3_mode;
                    while (addr_list.length)
                        s += '&src_addr=' + addr_list.shift();
                } else {
                    s += '?mcast=asm';
                }
            }
        } else if (dtv.type === 'dvbt-triplet' || dtv.type === 'dvbt2-triplet') {
            s = "dvb://" + [dtv.dvbt_nid, dtv.dvbt_tsid, dtv.dvbt_sid].join('.');
        } else if (dtv.type === 'mpegdash' || dtv.type === 'nativedash') {
            s = dtv.addr;
        } else if (dtv.type === 'rtsp') {
            s += '&rtspsvrtype=' + dtv.serverType;
        } else if (dtv.type !== 'http' && dtv.type !== 'file' && dtv.type !== 'https' && dtv.type !== 'srt') {
            // tuner channels
            s += '/' + dtv.port;

            if (dtv.symbolrate !== undefined) {
                s += '?symbolRate=' + dtv.symbolrate;
                numParams++;
            }
            if (dtv.bandwidth !== undefined) {
                s += (numParams > 0) ? '&' : '?';
                s += 'bandwidth=' + dtv.bandwidth;
                numParams++;
            }
            if (dtv.constellation !== undefined) {
                s += (numParams > 0) ? '&' : '?';
                s += 'constellation=' + dtv.constellation;
                numParams++;
            }
            if (dtv.lnb_freq !== undefined) {
                s += (numParams > 0) ? '&' : '?';
                s += 'lnb_osc_freq=' + dtv.lnb_freq;
                numParams++;
            }
            if (dtv.lnb !== undefined) {
                s += (numParams > 0) ? '&' : '?';
                s += 'lnb=' + dtv.lnb;
                numParams++;
            }
            if (dtv.polarity !== undefined) {
                s += (numParams > 0) ? '&' : '?';
                s += 'polarity=' + dtv.polarity;
                numParams++;
            }
            if (dtv.freq_band !== undefined) {
                s += (numParams > 0) ? '&' : '?';
                s += 'freq_band=' + dtv.freq_band;
                numParams++;
            }
        } else if ((dtv.type === 'https' && dtv.addr.includes("https://"))
            || (dtv.type === 'http' && dtv.addr.includes("http://"))
            || (dtv.type === 'file' && dtv.addr.includes("file:///"))) {
            s = dtv.addr;
        }
        return s;
    };

    async function tryLoadLineupFromEelm() {
        await retryMigration();
        await retryGettingMaster();
    };

    var default_config = {
        DTV: [], WEB: [], PiP: {}, PVR: {},
        PLTV_BUFFER_SIZE: 0,
        PLTV_DELAY: 0,
        PREFERRED_AUDIO_LANG: "",
        PREFERRED_TEXT_LANG: "",
        STANDBY: {
            powerSaving: {
                enabled: false,
                timeout: 120
            }
        }
    };
    var _lookupMap = {};
    var _lookupKeysDesc = [];

    var _config = (function () {
        var pref = localStorage.getItem(_storage_key);
        if (pref) {
            try {
                _config = JSON.parse(pref);
            } catch (e) {
                console.error("SYSTEM_CONFIG error: " + e);
                _config = {};
            }
            for (cfg in default_config) {
                _config[cfg] = _config[cfg] || default_config[cfg];
            }
            _config.STANDBY.powerSaving = _config.STANDBY.powerSaving || default_config.STANDBY.powerSaving;
            return _config;
        }
        return _config = default_config;
    })();

    return {
        save: function () {
            localStorage.setItem(_storage_key, JSON.stringify(_config));
        },
        writeLineupToEELM: async function (data, maxAttempts = 5, delayMs = 2000) {
            _data_migrate = data;
            for (let attempt = 1; attempt <= maxAttempts; attempt++) {
                let success = await retryMigration();
                if (!success) {
                    if (attempt < maxAttempts) {
                        console.warn("Retrying in " + delayMs + "ms..." + " Attempt: " + attempt + "/" + maxAttempts);
                        await new Promise(resolve => setTimeout(resolve, delayMs));
                    } else {
                        console.error("Max attempts reached. Migration failed.");
                        return false;
                    }
                }
                return true;
            }
        },
        getDTV_Array: function () {
            return _config.DTV;
        },
        setDTV_Array: function (data) {
            _config.DTV = data;
            SYSTEM_CONFIG.generateLookupMap();
        },
        getDTV: function (i) {
            if (!_config.DTV.length || i >= _config.DTV.length) {
                return '';
            }
            return _config.DTV[i];
        },
        getDTV_SourceString: function (i) {
            if (!_config.DTV.length || i >= _config.DTV.length) {
                return '';
            }
            var dtv = _config.DTV[i];
            return getSourceString(dtv);
        },
        getDTV_SourceConfig(i) {
            let zapData = {
                pltdelay: _config.PLTV_DELAY,
                pltbuf: _config.PLTV_BUFFER_SIZE,
                initial_bitrate: _config.HLS_INITIAL_BITRATE
            };
            let dtv = this.getDTV(i);
            // special way to call nativedash
            zapData.encryption_type = dtv.encryption_type;
            zapData.encrypting_server = dtv.encrypting_server;
            zapData.encrypting_request_header = dtv.encrypting_request_header;
            zapData.drm_session_type = dtv.drm_session_type;

            zapData.loop_on_end = (dtv.loop_on_end === undefined) ? JSON.parse(ENABLE.system.getINIValue("webapp.loop_on_end").result || false) : dtv.loop_on_end;
            return zapData;
        },
        setPltvConfig: function (size, delay) {
            _config.PLTV_BUFFER_SIZE = size;
            _config.PLTV_DELAY = delay;
        },
        setHlsInitialBitrate: function (bitrate) {
            _config.HLS_INITIAL_BITRATE = bitrate;
        },
        findDTVChannelIndex: function (value) {
            // For example, we have channels 100, 110, 120 and 130.
            // [input value -> return value]
            // 135->130, 101->100, 119->110, 60->100

            if (_lookupKeysDesc.length === 0)
                SYSTEM_CONFIG.generateLookupMap();
            if (_lookupKeysDesc.length === 0)
                return -1;

            if (_lookupMap[value])
                return _lookupMap[value]._idx;

            var int_value = parseInt(value);
            for (var i = 0, len = _lookupKeysDesc.length; i < len; ++i) {
                if (int_value > _lookupKeysDesc[i])
                    return _lookupMap[_lookupKeysDesc[i]]._idx;
            }
            return _lookupMap[_lookupKeysDesc[len - 1]]._idx;
        },
        findDTVChannelIndexByParam: function (channelName, channelDesc) {
            // Simply loop the list without indexing
            const result = _config.DTV.find(({ name, desc }) => {
                if (channelName === null) {
                    return channelDesc === desc;
                } else if (channelDesc === null) {
                    return channelName === name;
                }
                return channelName === name && channelDesc === desc;
            });
            if (result && result._idx >= 0)
                return result._idx;
            return -1;
        },
        generateLookupMap: function () {
            var arr = _config.DTV;
            _lookupMap = {};
            for (var i = 0, len = arr.length; i < len; ++i) {
                _lookupMap[arr[i].name] = arr[i];
                arr[i]._idx = i;
            }
            _lookupKeysDesc = Object.keys(_lookupMap).sort(function (a, b) { return b - a });
        },
        findStartingChannel() {
            let arr = _config.DTV;
            let idx = arr.findIndex(function (ch) {
                if (ch.starting == true) // use == , in case someone really set a string
                    return true;
            });
            return idx;
        },
        getZapperSourceString(dtv) {
            return getSourceString(dtv);
        },
        async loadLineupFromEelm() {
            return await tryLoadLineupFromEelm();
        }
    };
})();

SYSTEM_CONFIG.generateLookupMap();
