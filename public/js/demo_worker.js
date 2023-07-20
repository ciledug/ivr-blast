self.onmessage = (e) => {
    // console.log(e.data);

    var data = e.data;
    // console.log(data);

    if (data.action === 'run_read_excel_data') {
        self.postMessage({
            action: 'from_worker_run_read_excel_data',
            dataRows: data.dataRows
        });
    }
    else if (data.action === 'clear_preview_table') {
        self.postMessage({
            action: 'from_worker_clear_preview_table'
        });
    }
    else if (data.action === 'show_interactive_elements') {
        self.postMessage({
            action: 'from_worker_show_interactive_elements'
        });
    }
};
