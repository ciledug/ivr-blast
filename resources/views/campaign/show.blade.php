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
                      <div class="col-lg-3 col-md-4 label">Created Date</div>
                    </div>
                    <div class="row">
                      <div id="dialog-detail-campaign-created-date" class="col-lg-9 col-md-8 h5">{{ $campaign->created_at }}</div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="row">
                  <div class="col-md-12 mt-2">
                    <div class="row">
                      <div class="col-lg-4 col-md-4 label">Date Started</div>
                    </div>
                    <div class="row">
                      <div id="dialog-detail-campaign-date-started" class="col-lg-8 col-md-8 h5">{{ $campaign->started }}</div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12 mt-2">
                    <div class="row">
                      <div class="col-lg-4 col-md-4 label">Date Finished</div>
                    </div>
                    <div class="row">
                      <div id="dialog-detail-campaign-date-finished" class="col-lg-8 col-md-8 h5">{{ $campaign->finished }}</div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12 mt-2">
                    <div class="row">
                      <div class="col-lg-4 col-md-4 label">Total Calls</div>
                    </div>
                    <div class="row">
                      <div id="dialog-detail-campaign-total-calls" class="col-lg-8 col-md-8 h5">{{ $campaign->total_calls }}</div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12 mt-2">
                    <div class="row">
                      <div class="col-lg-4 col-md-4 label">Success Calls</div>
                    </div>
                    <div class="row">
                      <div id="dialog-detail-campaign-success-calls" class="col-lg-8 col-md-8 h5">{{ $campaign->success }}</div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12 mt-2">
                    <div class="row">
                      <div class="col-lg-4 col-md-4 label">Failed Calls</div>
                    </div>
                    <div class="row">
                      <div id="dialog-detail-campaign-failed-calls" class="col-lg-8 col-md-8 h5">{{ $campaign->failed }}</div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12 mt-2">
                    <div class="row">
                      <div class="col-lg-5 col-md-4 label">Campaign Progress (%)</div>
                    </div>
                    <div class="row">
                      <div id="dialog-detail-campaign-progress" class="col-lg-7 col-md-8 h5">{{ $campaign->progress }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <hr />

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
                    <th scope="col">Action</th>
                  </tr>
                </thead>
              </table>
            </div>

            <div class="col-md-12 mt-4">
              <form id="form-campaign-export" class="g-3 needs-validation" method="POST" action="{{ route('campaign.export') }}" enctype="multipart/form-data">
                <button type="button" class="btn btn-secondary btn-back">Close</button>
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
    prepareContactListTable();
    getContactList();

    $('#campaign-export-key').val('_{{ $campaign->unique_key }}');

    $('.btn-back').click(function(e) {
      history.back();
    });

    $('#btn-export-excel').click(function(e) {
      $('#campaign-export-type').val('excel');
      $('#form-campaign-export').submit();
    });

    $('#btn-export-pdf').click(function(e) {
      $('#campaign-export-type').val('pdf');
      $('#form-campaign-export').submit();
    });
  });

  function prepareContactListTable() {
    contactDataContainer = $('#table-contact-list-container').DataTable({
      columns: [
        { data: 'account_id' },
        { data: 'name' },
        { data: 'phone' },
        { data: 'bill_date' },
        { data: 'due_date' },
        { data: 'nominal' },
        { data: 'call_dial' },
        { data: 'call_response' },
      ],
      columnDefs: [
        {
          targets: 5,
          data: 'nominal',
          className: 'dt-body-right'
        },
        {
          targets: 8,
          data: null,
          render: function(data, type, row, meta) {
            contactList['_' + row.account_id] = row;
            var tempContent = '<a href="{{ route('contact.show') }}/_' + row.account_id + '" class="btn btn-sm btn-info">Detail</a>';
            return tempContent;
          }
        }
      ]
    });
  }

  function getContactList() {
    $.ajax({
      method: 'GET',
      url: "{{ route('contact.list') }}/{{ $campaign->unique_key }}",
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      processData: false,
      contentType: false,
      cache: false,
      success: function(response) {
        contactDataContainer.clear();
        contactDataContainer.rows.add(response.data);
        contactDataContainer.draw();
      },
      error: function(error) {
        console.log(error.responseText);
      }
    })
    .always(function() {
    });
  }
</script>
@endpush

@include('layouts.include_page_footer')
@include('layouts.include_js')
