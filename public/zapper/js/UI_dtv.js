let infoBarTimeout = null;
let errorMessageTimeout = null;

function hideInfoBar() {
    document.getElementById("digit").style.display = "none";
}

function showDigitBar(text) {
    document.getElementById("digit").innerText = text;
    document.getElementById("digit").style.display = "";
}

function showInfoBar(name, desc) {
    if (name)
        document.getElementById("digit").innerText = (desc || "") + " " + name;
    document.getElementById("digit").style.display = "";
    if (infoBarTimeout)
        clearTimeout(infoBarTimeout);
    infoBarTimeout = setTimeout(function () {
        hideInfoBar();
    }, 6*1000);
}

function hideErrorMessage() {
    document.getElementById("error").style.display = "none";
    if (errorMessageTimeout) {
        clearInterval(errorMessageTimeout);
        errorMessageTimeout = null;
    }

    // routeTo was suspended, do routing now
    const searchParams = new URL(document.location).searchParams;
    const routeTo = searchParams.get("routeTo");
    if (routeTo === "zapperEditor") {
        util.openZapperEditor();
    }
}

function noticeKeyHandler(key) {
    if (document.getElementById("error").style.display === "none")
        return false;

    if (key === _i.KEY_ENTER || key === "Enter") {
        hideErrorMessage()
    } else if (key === _i.KEY_DOWN || key === _i.KEY_PAGE_DOWN) {
        document.querySelector('.table-container').scrollBy(0, 12);
    } else if (key === _i.KEY_UP || key === _i.KEY_PAGE_UP) {
        document.querySelector('.table-container').scrollBy(0, -12);
    }
    // consume all keys until enter is pressed
    return true;
}

function showErrorMessage(title, message, hideTimeout = 0, tableData = null) {
    document.querySelector('.error-title').innerText = title;
    document.querySelector('.error-message').innerText = message;

    const errorTable = document.querySelector('.error-table');
    errorTable.innerHTML = '';

    if (tableData && Array.isArray(tableData) && tableData.length > 0) {
        const thead = document.createElement('thead');
        const headerRow = document.createElement('tr');
        Object.keys(tableData[0]).forEach(key => {
            const th = document.createElement('th');
            const title = key[0].toUpperCase() + key.slice(1);
            th.textContent = title;
            headerRow.appendChild(th);
        });
        thead.appendChild(headerRow);
        errorTable.appendChild(thead);

        const tbody = document.createElement('tbody');
        tableData.forEach(rowData => {
            const dataRow = document.createElement('tr');
            Object.values(rowData).forEach(value => {
                const td = document.createElement('td');
                td.textContent = value;
                dataRow.appendChild(td);
            });
            tbody.appendChild(dataRow);
        });
        errorTable.appendChild(tbody);
    }

    document.getElementById("error").style.display = "";
    if (errorMessageTimeout) {
        clearInterval(errorMessageTimeout);
        errorMessageTimeout = null;
    }
    if (hideTimeout > 0) {
        let remainingTime = hideTimeout;
        const noticeButton = document.querySelector('.notice-button');
        noticeButton.innerText = `Dismiss in ${remainingTime} second(s)`;

        errorMessageTimeout = setInterval(function () {
            remainingTime--;
            if (remainingTime > 0) {
                noticeButton.innerText = `Dismiss in ${remainingTime} second(s)`;
            } else {
                hideErrorMessage()
            }
        }, 1000);
    } else {
        document.querySelector('.notice-button').innerText = "Dismiss";
    }
}