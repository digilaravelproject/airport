const lineup = (() => {
  const arraysEqual = (a1, a2) => {
    return (
      a1.length === a2.length && a1.every((o, idx) => objectsEqual(o, a2[idx]))
    );
  };

  const objectsEqual = (o1, o2) => {
    // ignore lookupMap index
    delete o1._idx;
    if (o1.port) o1.port = parseInt(o1.port);
    if (o2.port) o2.port = parseInt(o2.port);
    return JSON.stringify(o1) === JSON.stringify(o2);
  };

  const checkDuplicateChannels = (dtv) => {
    let errorMessage = "";
    if (dtv) {
      let duplicatedDtv = dtv
        .filter((value) => {
          // more than 1 occurrence in the array
          return (
            dtv
              .map((channel) => channel.name === value.name)
              .filter((value) => value).length > 1
          );
        })
        .sort((a, b) => parseInt(a.name) - parseInt(b.name));
      if (duplicatedDtv.length > 0) {
        errorMessage += "Import failed due to duplicate channel name(s):\n";
        duplicatedDtv.forEach((channel, index) => {
          errorMessage += `${index === 0 ? "" : "\n"}${channel.name} ${channel.desc
            }`;
        });
      }
    }
    return errorMessage;
  };
  const convertLineup = function (ini) {
    let lineup = "";
    if (ini !== null) {
      lineup = JSON.parse(ini);
    }
    if (lineup && lineup.DTV) lineup = lineup.DTV;

    return lineup;
  };

  const saveToConfig = async function (data) {
    // for backward compatibility
    data = data instanceof Array ? data : data.DTV;

    if (!Array.isArray(data)) {
      showErrorMessage(
        "Invalid Channel Data",
        "Channel line-up could not be applied. Cannot find list of line-up data.",
        5
      );
      return;
    }

    let ini_lineup = localStorage.getItem("ini_lineup");
    let lineup = convertLineup(ini_lineup);
    if (ini_lineup === null || !arraysEqual(lineup, data)) {
      // check for duplicate channel names
      let errorMsg = checkDuplicateChannels(data);
      if (errorMsg) {
        showErrorMessage("Duplicate Channel Names Detected", errorMsg, "60");
        return;
      }

      // Validates the provided lineup data and displays an error message if any channels are invalid.
      let results = validateLineupList(data);
      const validCount = results.filter((e) => e.valid).length;
      if (validCount !== data.length) {
        let message = `${validCount} out of ${data.length} channels were successfully imported. Some channels couldn't be imported for the following reasons:`;
        let invalidChannels = results.filter(({ valid }) => !valid).map(({ item, reasons }) => ({
          name: item.name,
          desc: item.desc,
          reasons: reasons.join("\n")
        }));

        const invalidCount = invalidChannels.length;
        let timeout = 120;
        if (invalidCount < 5) {
          timeout = 30;
        } else if (invalidCount < 10) {
          timeout = 60;
        }
        showErrorMessage("Invalid Channel Data in Line-up", message, timeout, invalidChannels);
        //return; we still want to save the lineup
        data = results.filter((e) => e.valid).map(({ item }) => (item));
      }

      if (validCount === 0) return;

      let success = await SYSTEM_CONFIG.writeLineupToEELM(data);
      if (success) {
        // use INI lineup when it is the first ini update / changes made to the ini lineup
        localStorage.setItem("ini_lineup", JSON.stringify(data));
        console.debug("Update lineup from INI");
        SYSTEM_CONFIG.setDTV_Array(data);
        SYSTEM_CONFIG.save();
      }
    }
  };

  return {
    import() {
      const applyJSON = async function (json_str, resolve) {
        try {
          let obj = JSON.parse(json_str);
          await saveToConfig(obj);
        } catch (error) {
          console.error("Invalid json format", json_str);
          showErrorMessage(
            "Invalid Channel Format",
            "Channel line-up could not be applied. JSON format file was expected.",
            5
          );
        }
        resolve();
      };

      const readLineup = async function (resolve) {
        // USB lineup with higher priority
        let ret = ENABLE.file.getUSBLineupIfExist();
        if (ret.result !== "") {
          await applyJSON(ret.result, resolve);
          return;
        }
        ret = ENABLE.file.getINILineupIfExist();
        if (ret.result !== "") {
          await applyJSON(ret.result, resolve);
          return;
        }
        ret = ENABLE.system.getINIValue("webapp.lineup_json");
        if (ret.result !== "") {
          await applyJSON(ret.result, resolve);
          return;
        }
        resolve();
      };
      return new Promise(readLineup);
    },
  };
})();
