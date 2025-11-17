const validateLineupList = (() => {
  const URL_PATTERN =
    /^(https?|udp|igmpv3|rtsp|srt|file|rtmps?|dolbyio):\/\/.+/;

  function validateItem(item, itemResult) {
    if ("addr" in item) {
      validateChannelv1(item, itemResult);
    } else if (Array.isArray(item.url)) {
      validateChannelv3(item, itemResult);
    } else {
      validateChannelv2(item, itemResult);
    }
  }

  function validateCommonProperties(item, itemResult) {
    if (!item.name) {
      itemResult.reasons.push('Missing channel name');
    } else if (typeof item.name !== "string") {
      itemResult.reasons.push('Channel name is not in text format');
    }

    if (item.desc !== undefined && typeof item.desc !== "string") {
      itemResult.reasons.push('Channel description is not in text format');
    }
  }

  function validateUrl(url, itemResult) {
    const dolbyioPattern = /^dolbyio:\/\/([^\/]+)\/([^\/]+)$/;
    if (!dolbyioPattern.test(url)) {
        itemResult.reasons.push(`Invalid URL format: ${url}. Expected format: dolbyio://<acc_id>/<stream_name>`);
    }
  }

  function validateChannelv1(item, itemResult) {
    const supportedProtocols = [
      "https",
      "http",
      "udp",
      "igmpv3",
      "rtsp",
      "srt",
      "nativedash",
    ];

    validateCommonProperties(item, itemResult);

    if (!item.addr) itemResult.reasons.push('Missing stream address');
    if (!item.type) itemResult.reasons.push('Missing "type" property');
    else if (!supportedProtocols.includes(item.type))
      itemResult.reasons.push('Unsupported channel type')
    if (item.port && isNaN(Number(item.port))) {
      itemResult.reasons.push('Invalid port number');
    }
  }

  function validateChannelv2(item, itemResult) {
    validateCommonProperties(item, itemResult);

    if (!item.url) {
      itemResult.reasons.push('Missing "url" property');
    } else {
      if (typeof item.url !== "string") {
        itemResult.reasons.push('URL is not in text format');
      } else {
        if (!URL_PATTERN.test(item.url)) {
          itemResult.reasons.push(`Invalid URL format: ${item.url}`);
        }
        if (item.url.includes("dolbyio://")) {
          validateUrl(item.url, itemResult);
        }
      }
    }
  }
  function validateChannelv3(item, itemResult) {
    validateCommonProperties(item, itemResult);

    if (!Array.isArray(item.url)) {
      itemResult.reasons.push(
        'Missing or invalid "url" property; must be an array'
      );
    } else if (item.url.length < 1 || item.url.length > 4) {
      itemResult.reasons.push('Too many or too few stream addresses (should be between 1 and 4)');
    } else {
      item.url.forEach((url, index) => {
        if (typeof url !== "string" || !URL_PATTERN.test(url)) {
          itemResult.reasons.push(
            `Invalid URL format at index ${index}: ${url}`
          );
        } else if (url.includes("dolbyio://")) {
          validateUrl(url, itemResult);
        }
      });
    }
  }

  return function(jsonArray) {
    const results = [];

    for (const item of jsonArray) {
      const itemResult = { item, reasons: [] };

      if (typeof item !== "object" || item === null) {
        itemResult.reasons.push("Item is not a valid object");
        results.push(itemResult);
        continue;
      }

      validateItem(item, itemResult);
      itemResult.valid = itemResult.reasons.length === 0;
      results.push(itemResult);
    }

    return results;
  };
})();
