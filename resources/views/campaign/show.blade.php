@include('layouts.include_page_header')
@include('layouts.include_sidebar')

<main id="main" class="main">
  <div class="pagetitle">
    <h1>Campaigns</h1>
    <!--
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.html">Home</a></li>
        <li class="breadcrumb-item">Tables</li>
        <li class="breadcrumb-item active">General</li>
      </ol>
    </nav>
    -->
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-12">

        <div class="card">
          <div class="card-body">
            <h5 class="card-title">
                Detail Campaign
            </h5>

            <div class="row">
              <div class="col-md-6">
                <div class="row">
                  <div class="col-md-12">
                    <div class="row">
                      <div class="col-lg-3 col-md-4 label">Name</div>
                    </div>
                    <div class="row">
                      <div id="dialog-detail-campaign-name" class="col-lg-9 col-md-8 h5">{{ $campaign->name }}</div>
                    </div>
                  </div>
                </div>
                
                <div class="row">
                  <div class="col-md-12 mt-2">
                    <div class="row">
                      <div class="col-lg-3 col-md-4 label">Total Data</div>
                    </div>
                    <div class="row">
                      <div id="dialog-detail-campaign-total-data" class="col-lg-9 col-md-8 h5">{{ $campaign->total_data }}</div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12 mt-2">
                    <div class="row">
                      <div class="col-lg-3 col-md-4 label">Status</div>
                    </div>
                    <div class="row">
                      <div id="dialog-detail-campaign-status" class="col-lg-9 col-md-8 h5">{{ $campaign->status }}</div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12 mt-2">
                    <div class="row">
                      <div class="col-lg-4 col-md-5 label">Created Date</div>
                    </div>
                    <div class="row">
                      <div id="dialog-detail-campaign-created-date" class="col-lg-9 col-md-8 h5">{{ $campaign->created_at }}</div>
                    </div>
                  </div>
                </div>

                @if (strtolower($campaign->status) !== 'finished')
                <div class="row">
                  <div class="col-md-12 mt-4">
                      @if (strtolower($campaign->status) === 'ready')
                      <button type="button" class="btn btn-success btn-campaign-status" id="btn-start-campaign" data-status-value="ready">Start Campaign</button>
                      @elseif (strtolower($campaign->status) === 'running')
                      <button type="button" class="btn btn-danger btn-campaign-status" id="btn-pause-campaign" data-status-value="running">Pause Campaign</button>
                      @elseif (strtolower($campaign->status) === 'paused')
                      <button type="button" class="btn btn-success btn-campaign-status" id="btn-resume-campaign" data-status-value="paused">Resume Campaign</button>
                      @endif
                  </div>
                </div>
                @endif
              </div>

              <div class="col-md-6">
                <div class="row">
                  <div class="col-md-12 mt-2">
                    <div class="row">
                      <div class="col-lg-4 col-md-4 label">Date Started</div>
                    </div>
                    <div class="row">
                      <div id="dialog-detail-campaign-date-started" class="col-lg-8 col-md-8 h5">{{ $campaign->started or '-' }}</div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12 mt-2">
                    <div class="row">
                      <div class="col-lg-4 col-md-4 label">Date Finished</div>
                    </div>
                    <div class="row">
                      <div id="dialog-detail-campaign-date-finished" class="col-lg-8 col-md-8 h5">{{ $campaign->finished or '-' }}</div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12 mt-2">
                    <div class="row">
                      <div class="col-lg-4 col-md-4 label">Total Calls</div>
                    </div>
                    <div class="row">
                      <div id="dialog-detail-campaign-total-calls" class="col-lg-8 col-md-8 h5">{{ $campaign->total_calls or '0' }}</div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12 mt-2">
                    <div class="row">
                      <div class="col-lg-4 col-md-4 label">Success Calls</div>
                    </div>
                    <div class="row">
                      <div id="dialog-detail-campaign-success-calls" class="col-lg-8 col-md-8 h5">{{ $campaign->success or '0' }}</div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12 mt-2">
                    <div class="row">
                      <div class="col-lg-4 col-md-4 label">Failed Calls</div>
                    </div>
                    <div class="row">
                      <div id="dialog-detail-campaign-failed-calls" class="col-lg-8 col-md-8 h5">{{ $campaign->failed or '0' }}</div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12 mt-2">
                    <div class="row">
                      <div class="col-lg-5 col-md-4 label">Campaign Progress (%)</div>
                    </div>
                    <div class="row">
                      <div id="dialog-detail-campaign-progress" class="col-lg-7 col-md-8 h5">{{ $campaign->progress or '0' }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <hr />

            <table class="table table-hover">
              <thead>
                <tr>
                    {{-- <th scope="col">#</th> --}}
                    <th scope="col">Account ID</th>
                    <th scope="col">Name</th>
                    <th scope="col">Phone</th>
                    <th scope="col">Bill Date</th>
                    <th scope="col">Due Date</th>
                    <th scope="col">Nominal</th>
                    <th scope="col">Call Date</th>
                    <th scope="col">Call Response</th>
                    <th scope="col">Total Calls</th>
                    <th scope="col">Action</th>
                </tr>
              </thead>

              <tbody>
                @foreach ($contacts['data'] AS $keyData => $valueData)
                <tr>
                  <th>{{ $valueData->account_id }}</th>
                  <td>{{ $valueData->name }}</td>
                  <td>{{ $valueData->phone }}</td>
                  <td>{{ $valueData->bill_date }}</td>
                  <td>{{ $valueData->due_date }}</td>
                  <td class="text-end">{{ $valueData->nominal }}</td>
                  <td>{{ $valueData->call_date }}</td>
                  <td>{{ $valueData->call_response }}</td>
                  <td class="text-end">{{ $valueData->total_calls }}</td>
                  <td><a href="{{ route('contact.show') }}/_{{ $valueData->account_id }}/{{ $campaign->id }}" class="btn btn-sm btn-info">Detail</a></td>
                </tr>
                @endforeach
              </tbody>
            </table>

            {{ $contacts['data']->links() }}

            <!--
            <div class="col-md-12 mt-4">
              <table id="table-contact-list-container" class="table table-hover">
                <thead>
                  <tr>
                    {{-- <th scope="col">#</th> --}}
                    <th scope="col">Account ID</th>
                    <th scope="col">Name</th>
                    <th scope="col">Phone</th>
                    <th scope="col">Bill Date</th>
                    <th scope="col">Due Date</th>
                    <th scope="col">Nominal</th>
                    <th scope="col">Call Date</th>
                    <th scope="col">Call Response</th>
                    <th scope="col">Total Calls</th>
                    <th scope="col">Action</th>
                  </tr>
                </thead>
              </table>
            </div>
            -->

            <div class="col-md-12 mt-4">
              <form id="form-campaign-export" class="g-3 needs-validation" method="POST" target="_blank" action="{{ route('campaign.export') }}" enctype="multipart/form-data">
                <a href="{{ route('campaign') }}" class="btn btn-secondary btn-back">Close</a>
                <button type="button" class="btn btn-success btn-export-as" id="btn-export-excel" data-export-as="excel">Export Excel</button>
                <button type="button" class="btn btn-danger btn-export-as" id="btn-export-pdf" data-export-as="pdf">Export PDF</button>
                <input type="hidden" id="campaign-export-type" name="export_type" value="">
                <input type="hidden" id="campaign-export-key" name="campaign" value="">
                {{ csrf_field() }}
              </form>
            </div>

          </div>
        </div>

      </div>
    </div>
  </section>
</main>

@push('javascript')
<script type="text/javascript">
  var contactList = [];
  var contactDataContainer = '';

  $(document).ready(function() {
    // prepareContactListTable();

    $('#campaign-export-key').val('_{{ $campaign->unique_key }}');

    $('#btn-export-excel').click(function(e) {
      $('#campaign-export-type').val('excel');
      $('#form-campaign-export').submit();
    });

    $('#btn-export-pdf').click(function(e) {
      $('#campaign-export-type').val('pdf');
      $('#form-campaign-export').submit();
    });

    $('.btn-campaign-status').click(function(e) {
      $('.btn-campaign-status').addClass('disabled');
      startStopCampaign('{{ $campaign->unique_key }}', $(this).attr('data-status-value'));
    });
  });

  function prepareContactListTable() {
    contactDataContainer = $('#table-contact-list-container').DataTable({
      processing: true,
      lengthMenu: [5, 10, 15, 20, 50, 100],
      pageLength: 10,
      serverSide: true,
      ajax: {
        url: '{{ route('contact.list.ajax') }}',
        type: 'POST',
        data: {
          campaign: '_{{ $campaign->unique_key }}'
        },
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
      },
      columns: [
        { data: 'account_id' },
        { data: 'name' },
        { data: 'phone' },
        { data: 'bill_date' },
        { data: 'due_date' },
        { data: 'nominal' },
        { data: 'call_dial' },
        { data: 'call_response' },
        { data: 'total_calls' },
      ],
      columnDefs: [
        {
          targets: 5,
          className: 'dt-body-right'
        },
        {
          targets: 8,
          className: 'dt-body-right'
        },
        {
          targets: 9,
          orderable: false,
          data: null,
          render: function(data, type, row, meta) {
            // console.log(type);
            contactList['_' + row.account_id] = row;
            data = '<a href="{{ route('contact.show') }}/_' + row.account_id + '/{{ $campaign->id }}" class="btn btn-sm btn-info">Detail</a>';
            return data;
          }
        }
      ]
    });
  };

  function startStopCampaign(campaignKey, currentStatus) {
    $.ajax({
      method: 'PUT',
      url: '{{ route('campaign.update.startstop') }}',
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      data: JSON.stringify({
        campaign: campaignKey,
        currstatus: currentStatus,
        startstop: true,
        _token: '{{ csrf_token() }}'
      }),
      processData: false,
      contentType: 'application/json',
      cache: false,
      success: function(response) {
        if (response.code === 200) {
          location.reload();
        }
        else {
          $('.btn-campaign-status').removeClass('disabled');
        }
      },
      error: function(error) {
        console.log(error.responseText);
      }
    })
    .always(function() {
    });
  };


  function sheet_to_csv_cb(ws, cb, opts, batch = 1000) {
    XLSX.stream.set_readable(() => ({
      __done: false,
      // this function will be assigned by the SheetJS stream methods
      _read: function() { this.__done = true; },
      // this function is called by the stream methods
      push: function(d) { if(!this.__done) cb(d); if(d == null) this.__done = true; },
      resume: function pump() { for(var i = 0; i < batch && !this.__done; ++i) this._read(); if(!this.__done) setTimeout(pump.bind(this), 0); }
    }));
    return XLSX.stream.to_csv(ws, opts);
  }

  /* this callback will run once the main context sends a message */
  // self.addEventListener('message', async(e) => {
  //   try {
  //     postMessage({state: "fetching " + e.data.url});
  //     /* Fetch file */
  //     const res = await fetch(e.data.url);
  //     const ab = await res.arrayBuffer();

  //     /* Parse file */
  //     postMessage({state: "parsing"});
  //     const wb = XLSX.read(ab, {dense: true});
  //     const ws = wb.Sheets[wb.SheetNames[0]];

  //     /* Generate CSV rows */
  //     postMessage({state: "csv"});
  //     const strm = sheet_to_csv_cb(ws, (csv) => {
  //       if(csv != null) postMessage({csv});
  //       else postMessage({state: "done"});
  //     });
  //     strm.resume();
  //   } catch(e) {
  //     /* Pass the error message back */
  //     postMessage({error: String(e.message || e) });
  //   }
  // }, false);

  // worker.onmessage = function(e) {
  //   if(e.data.error) { console.error(e.data.error); /* show an error message */ }
  //   else if(e.data.state) { console.info(e.data.state); /* current state */ }
  //   else {
  //     /* e.data.csv is the row generated by the stream */
  //     console.log(e.data.csv);
  //   }
  // };



</script>
@endpush

@include('layouts.include_page_footer')
@include('layouts.include_js')
