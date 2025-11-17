const util = {
    get(url, config) {
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function () {
            if (this.readyState == 4) {
                config.callback(this);
            }
        };
        xmlhttp.open("GET", url, config.async);
        if (config.no_cache)
            xmlhttp.setRequestHeader("Cache-Control", "no-cache, no-store, max-age=0");
        xmlhttp.send();
    },
    async checkAndRetryEelmConnection() {
      const tryConnectEelm = (resolve) => {
        this.checkEELMAvailability(() => {
          resolve();
        }, retryOnFailed.bind(null, resolve), retryOnFailed.bind(null, resolve))
      }

      const retryOnFailed = (resolve) => {
          console.debug("Failed to ping the EELM server... retry...");
          setTimeout(tryConnectEelm, 1000, resolve);
      } 
      return new Promise(tryConnectEelm);
    },
    checkEELMAvailability(resolve, reject, onFailed) {
        try {
            const r = new XMLHttpRequest();
            r.open('GET', `http://localhost:8090`, false);
            r.send();
            if (r.status === 200) {
                resolve();
            } else {
                reject();
            }
        } catch (e) {
          console.warn(e);
          if (!!onFailed) {
            onFailed();
          }
        }
    },
    deleteEELMChannel(config) {
        try {
            const r = new XMLHttpRequest();
            r.open('DELETE', `http://localhost:8090/api/v2/zapper/channels/${config.name}`, false);  // `false` makes the request synchronous
            r.send();
            return r.status === 200;
        } catch (e) { console.error(e); }
        return false;
    },
    postEELMChannel(config) {
        if (config.port)
            config.port = parseInt(config.port);
        try {
            const r = new XMLHttpRequest();
            r.open('POST', `http://localhost:8090/api/v2/zapper/channels`, false);
            r.send(JSON.stringify(config));
            return r.status === 200;
        } catch (e) { console.error(e); }
        return false;
    },
    getEELMChannels() {
        try {
            const r = new XMLHttpRequest();
            r.open('GET', `http://localhost:8090/api/v2/zapper/channels`, false);  // `false` makes the request synchronous
            r.send();
            if (r.status === 200) {
                const json = JSON.parse(r.responseText);
                if (json && json.channels)
                    return json.channels;
            }
        } catch (e) { console.error(e); }
        return null;
    },
    async openZapperEditor(params = {}) {
      const lineupEditorUrl = new URL("http://localhost:8090/lineupEditor/index.html");
      Object.entries(params).forEach(([key, value]) => {
        lineupEditorUrl.searchParams.set(key, value);
      });
      const loadZapperEditorLink = () => { window.location.replace(lineupEditorUrl); }
      return this.checkAndRetryEelmConnection().then(loadZapperEditorLink);
    },
    isScreenVertical() {
        try {
            const r = new XMLHttpRequest();
            r.open('GET', `http://localhost:8090/api/v2/system/rotation`, false);  // `false` makes the request synchronous
            r.send();
            if (r.status === 200) {
                const json = JSON.parse(r.responseText);
                if (json && json.rotation)
                    return json.rotation === 1 || json.rotation === 3;
            }
        } catch (e) { console.error(e); }
        return false;
    }
};
