self.importScripts('./luxon.min.js');
self.importScripts('https://unpkg.com/libphonenumber-js@1.9.6/bundle/libphonenumber-max.js');

self.onmessage = (e) => {
    // console.log(e.data);

    var data = e.data;
    // console.log(data);

    if (data.action === 'hide_interactive_elements') {
        self.postMessage({
            action: 'from_worker_hide_interactive_elements'
        });
    }
    else if (data.action === 'show_interactive_elements') {
        self.postMessage({
            action: 'from_worker_show_interactive_elements'
        });
    }
    else if (data.action === 'run_read_excel_data') {
        self.postMessage({
            action: 'from_worker_run_read_excel_data',
            dataRows: data.dataRows
        });
    }
    else if (data.action === 'run_data_check') {
        var tempUniques = [];

        e.data.dataRows.shift();

        if (e.data.editAction === 'merge') {
            var tempNewRow = [];

            e.data.previousContacts.map((valContact, keyContact) => {
                var tempContact = [];

                e.data.tempHeaders.map((valHeader, keyHeader) => {
                    tempContact.push(valContact[valHeader.name]);
                    valHeader = undefined;
                });

                tempNewRow.push(tempContact);
                tempContact = undefined;
            });

            e.data.dataRows = tempNewRow.concat(e.data.dataRows);
        }

        e.data.dataRows.map((valDataRow, keyDataRow) => {
            var checkedData = readExcelData(valDataRow, e.data.tempHeaders, tempUniques);
            tempUniques.push(checkedData.tempUniques);
            // console.log(tempUniques.length);

            self.postMessage({
                action: 'from_worker_run_data_check',
                total_count: e.data.dataRows.length,
                sequence: keyDataRow + 1,
                row: checkedData.newRow,
                dataErrorsCount: checkedData.dataErrorsCount,
                editAction: e.data.editAction
            });

            valDataRow = undefined;
            keyDataRow = undefined;
        });
    }
    else if (data.action === 'change_element_text') {
        self.postMessage({
            action: 'from_worker_change_element_text',
            element: data.element,
            text: data.text
        })
    }
};

function readExcelData(valDataRow, tempHeaders, tempUniques) {
    var tempNewRow = {};
    var tempColInfo = [];
    var tempHeaderName = '';
    var tempErrors = [];
    var isContentOk = true;
    var dataErrorsCount = 0;

    tempHeaders.map((valHeader, keyHeader) => {
        tempHeaderName = valHeader.name;
        tempNewRow[tempHeaderName] = valDataRow[keyHeader];
        tempColInfo.push({
            name: tempHeaderName,
            type: valHeader.type,
            value: valDataRow[keyHeader],
            is_mandatory: valHeader.is_mandatory,
            is_unique: valHeader.is_unique
        });

        if (tempNewRow[tempHeaderName] === undefined) {
            tempNewRow[tempHeaderName] = '';
        }

        switch (valHeader.type) {
            case 'numeric':
                if ((tempNewRow[tempHeaderName].length > 0) && isNaN(tempNewRow[tempHeaderName])) {
                    tempErrors.push('<small class="text-danger"><span class="fw-bold">' + tempHeaderName.toUpperCase() + '</span> data invalid</small>');
                    isContentOk = false;
                    dataErrorsCount++;
                }
                break;
            case 'datetime':
                if (tempNewRow[tempHeaderName].length > 0) {
                    var dateTime = luxon.DateTime.fromFormat(tempNewRow[tempHeaderName], 'yyyy-MM-dd HH:mm:ss');
                    if (!dateTime.isValid) {
                        tempErrors.push('<small class="text-danger"><span class="fw-bold">' + tempHeaderName.toUpperCase() + '</span> data invalid</small>');
                        isContentOk = false;
                        dataErrorsCount++;
                    }
                    // console.log(tempHeaderName + ' ' + valHeader.type + ' ' + tempNewRow[tempHeaderName] + ' isValid: ' + dateTime.isValid);
                    dateTime = undefined;
                }
                break;
            case 'date':
                if (tempNewRow[tempHeaderName].length > 0) {
                    var dateTime = luxon.DateTime.fromFormat(tempNewRow[tempHeaderName], 'yyyy-MM-dd');
                    if (!dateTime.isValid) {
                        tempErrors.push('<small class="text-danger"><span class="fw-bold">' + tempHeaderName.toUpperCase() + '</span> data invalid</small>');
                        isContentOk = false;
                        dataErrorsCount++;
                    }
                    // console.log(tempHeaderName + ' ' + valHeader.type + ' ' + tempNewRow[tempHeaderName] + ' isValid: ' + dateTime.isValid);
                    dateTime = undefined;
                }
                break;
            case 'time':
                if (tempNewRow[tempHeaderName].length > 0) {
                    var dateTime = luxon.DateTime.fromFormat(tempNewRow[tempHeaderName], 'HH:mm:ss');
                    if (!dateTime.isValid) {
                        tempErrors.push('<small class="text-danger"><span class="fw-bold">' + tempHeaderName.toUpperCase() + '</span> data invalid</small>');
                        isContentOk = false;
                        dataErrorsCount++;
                    }
                    // console.log(tempHeaderName + ' ' + valHeader.type + ' ' + tempNewRow[tempHeaderName] + ' isValid: ' + dateTime.isValid);
                    dateTime = undefined;
                }
                break;
            case 'handphone':
                tempNewRow[tempHeaderName] = tempNewRow[tempHeaderName].toString();
                if (tempNewRow[tempHeaderName].length > 0) {
                    if (tempNewRow[tempHeaderName].length >= 10 && tempNewRow[tempHeaderName].length <= 15) {
                        if (isNaN(tempNewRow[tempHeaderName])) {
                            tempErrors.push('<small class="text-danger"><span class="fw-bold">' + tempHeaderName.toUpperCase() + '</span> data invalid</small>');
                            isContentOk = false;
                            dataErrorsCount++;
                        }
                        else {
                            var phone = libphonenumber.parsePhoneNumber(tempNewRow[tempHeaderName], 'ID');
                            if (!phone.isValid()) {
                                tempErrors.push('<small class="text-danger"><span class="fw-bold">' + tempHeaderName.toUpperCase() + '</span> data invalid</small>');
                                isContentOk = false;
                                dataErrorsCount++;
                            }
                            phone = undefined;
                        }
                    }
                    else {
                        tempErrors.push('<small class="text-danger"><span class="fw-bold">' + tempHeaderName.toUpperCase() + '</span> data invalid</small>');
                        isContentOk = false;
                        dataErrorsCount++;
                    }
                }
                break;
            default: break;
        }
        
        if (valHeader.is_mandatory) {
            if (tempNewRow[tempHeaderName].length == 0) {
                tempErrors.push('<small class="text-danger"><span class="fw-bold">' + tempHeaderName.toUpperCase() + '</span> data invalid</small>');
                isContentOk = false;
                dataErrorsCount++;
            }
        }
        
        if (valHeader.is_unique) {
            if ((tempNewRow[tempHeaderName].length > 0) && tempUniques.includes(tempNewRow[tempHeaderName])) {
                tempErrors.push('<small class="text-danger"><span class="fw-bold">' + tempHeaderName.toUpperCase() + '</span> data duplicate</small>');
                isContentOk = false;
                dataErrorsCount++;
            }
            else {
                tempUniques.push(tempNewRow[tempHeaderName]);
            }
        }
    });

    tempNewRow['col_info'] = tempColInfo;

    if (isContentOk) {
        tempNewRow['errors'] = null;
    }
    else {
        tempNewRow['errors'] = tempErrors.join('<br>');
    }

    var checkedData = {
        newRow: tempNewRow,
        tempUniques: tempUniques,
        dataErrorsCount: dataErrorsCount
    };
    // console.log(checkedData);

    return checkedData;
};
