self.onmessage = (e) => {
    // console.log(e.data);

    var data = e.data;


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
    else if (data.action === 'start_get_progress_import') {
        self.postMessage({
            action: 'from_worker_start_get_progress_import'
        });
    }
    else if (data.action === 'stop') {
        self.postMessage('from_worker_stop_worker');
    }
    else if (data.action === 'clear_preview_tables') {
        self.postMessage('from_worker_clear_preview_tables')
    }
    // else if (data.action === 'check_phone_numbers') {
    //     var dataRows = data.dataRows;
    //     var tempPhoneList = [];
    //     var tempContact = {};

    //     dataRows.map((v, k) => {
    //         // console.log(v);
    //         if (tempPhoneList.indexOf(v[2]) !== -1) {
    //             tempContact = {
    //                 'account_id': v[0],
    //                 'name': v[1],
    //                 'phone': v[2],
    //                 'bill_date': v[3],
    //                 'due_date': v[4],
    //                 'nominal': v[5],
    //                 'failed': 'Phone number exists',
    //             }

    //             self.postMessage({
    //                 action: 'from_worker_add_invalid_phone',
    //                 contact: tempContact
    //             });
    //         }
    //         else {
    //             tempContact = {
    //                 'account_id': v[0],
    //                 'name': v[1],
    //                 'phone': v[2],
    //                 'bill_date': v[3],
    //                 'due_date': v[4],
    //                 'nominal': v[5]
    //             }
    //             tempPhoneList.push(v[2]);

    //             self.postMessage({
    //                 action: 'from_worker_add_valid_phone',
    //                 contact: tempContact
    //             });
    //         }

    //         v = null;
    //     });

    //     self.postMessage('from_worker_finish_check_phone_numbers');
    // }
    else if (data.action === 'add_valid_contact') {
        self.postMessage({
            action: 'from_worker_add_valid_phone',
            contact: {
                'account_id': data.contact[0],
                'name': data.contact[1],
                'phone': data.contact[2],
                'bill_date': data.contact[3],
                'due_date': data.contact[4],
                'nominal': data.contact[5]
            }
        });
    }
    else if (data.action === 'add_invalid_contact') {
        self.postMessage({
            action: 'from_worker_add_invalid_phone',
            contact: {
                'account_id': data.contact[0],
                'name': data.contact[1],
                'phone': data.contact[2],
                'bill_date': data.contact[3],
                'due_date': data.contact[4],
                'nominal': data.contact[5],
                'failed': data.contact[6],
            }
        });
    }
    else if (data.action === 'add_contacts_finished') {
        self.postMessage('from_worker_add_contacts_finished');
    }
};
