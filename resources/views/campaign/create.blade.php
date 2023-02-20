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

        @if (!session('saved_contacts'))
        <div class="card">
          <form id="form-create-campaign" class="g-3 needs-validation" method="POST" action="{{ route('campaign.store') }}" enctype="multipart/form-data">
            <div class="card-body">
              <h5 class="card-title">
                  Add Campaign
              </h5>

              <div class="col-md-12">
                <div class="form-floating">
                  <input type="text" class="form-control" id="input-campaign-name" name="name" minlength="5" maxlength="30" placeholder="Name" required>
                  <label for="input-user-name">Campaign Name (min 5. chars)</label>
                </div>
              </div>

              <div class="col-md-12 mt-3">
                <div class="form-floating">
                  <div class="input-group">
                    <input class="form-control" type="file" id="input-campaign-excel-file" accept=".xls, .xlsx" required>
                    <a href="{{ route('campaign.template') }}" class="btn btn-success" type="button">
                      <i class="bi bi-download"></i>
                      &nbsp; Contact Template
                    </a>
                  </div>
                </div>
              </div>

              <div class="col-md-12 mt-4">
                <table id="table-preview-contact-data-container" class="table table-hover">
                  <thead>
                    <tr>
                      {{-- <th scope="col">#</th> --}}
                      <th scope="col">Account ID</th>
                      <th scope="col">Name</th>
                      <th scope="col">Phone</th>
                      <th scope="col">Bill Date</th>
                      <th scope="col">Due Date</th>
                      <th scope="col">Nominal</th>
                    </tr>
                  </thead>

                  @if (session('success_contacts'))
                    @php
                      $successContacts = json_decode(session('success_contacts'))
                    @endphp

                    @foreach ($successContacts AS $successContact)
                      <tbody>
                        <tr>
                          {{-- <td scope="col">#</td> --}}
                          <td scope="col">{{ $successContact->account_id }}</td>
                          <td scope="col">{{ $successContact->name }}</td>
                          <td scope="col">{{ $successContact->phone }}</td>
                          <td scope="col">{{ $successContact->bill_date }}</td>
                          <td scope="col">{{ $successContact->due_date }}</td>
                          <td scope="col">{{ $successContact->nominal }}</td>
                        </tr>
                      </tbody>
                    @endforeach
                  @endif
                </table>
              </div>

              <div class="col-md-12 mt-4">
                <button type="button" class="btn btn-secondary btn-back">Cancel</button>
                <button type="submit" class="btn btn-primary" id="btn-submit-create-campaign">Save</button>
                &nbsp;<div id="submit-spinner-save-contacts" class="spinner-border spinner-border-sm text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
                {{ csrf_field() }}
                <input type="hidden" id="input-campaign-rows" name="input_campaign_rows" value="" required>
              </div>
            </div>
          </form>
        </div>
        @endif

        @if (session('saved_contacts'))
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Saved Uploaded Contacts</h5>

            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <h4 class="alert-heading">Data Input Error</h4>
              <p>Some contact data can not be saved. Please check the 'Failed Uploaded Contacts' table below to see them.</p>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

            <div class="col-md-12 mt-4">
              <table id="table-saved-contact-data-container" class="table table-hover">
                <thead>
                  <tr>
                    {{-- <th scope="col">#</th> --}}
                    <th scope="col">Account ID</th>
                    <th scope="col">Name</th>
                    <th scope="col">Phone</th>
                    <th scope="col">Bill Date</th>
                    <th scope="col">Due Date</th>
                    <th scope="col">Nominal</th>
                  </tr>
                </thead>

                @php
                  $savedContacts = json_decode(session('saved_contacts'));
                @endphp

                <tbody>
                  @foreach ($savedContacts AS $savedContact)
                  <tr>
                    {{-- <td scope="col">#</td> --}}
                    <td scope="col">{{ $savedContact->account_id }}</td>
                    <td scope="col">{{ $savedContact->name }}</td>
                    <td scope="col">{{ $savedContact->phone }}</td>
                    <td scope="col">{{ $savedContact->bill_date }}</td>
                    <td scope="col">{{ $savedContact->due_date }}</td>
                    <td scope="col">{{ $savedContact->nominal }}</td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>

            <div class="col-md-12 mt-3">
              <a href="{{ route('campaign') }}" class="btn btn-secondary">Close</a>
            </div>
          </div>
        </div>
        @endif

        @if (session('failed_contacts'))
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Failed Uploaded Contacts</h5>

            <div class="col-md-12 mt-4">
              <table id="table-failed-contacts-container" class="table table-hover">
                <thead>
                  <tr>
                    {{-- <th scope="col">#</th> --}}
                    <th scope="col">Account ID</th>
                    <th scope="col">Name</th>
                    <th scope="col">Phone</th>
                    <th scope="col">Bill Date</th>
                    <th scope="col">Due Date</th>
                    <th scope="col">Nominal</th>
                    <th scope="col">Failed Reason</th>
                  </tr>
                </thead>

                @php
                  $failedContacts = json_decode(session('failed_contacts'));
                @endphp

                <tbody>
                  @foreach($failedContacts AS $failedContact)
                  <tr>
                    {{-- <th scope="col">#</td> --}}
                    <td scope="col">{{ $failedContact->account_id }}</td>
                    <td scope="col">{{ $failedContact->name }}</td>
                    <td scope="col">{{ $failedContact->phone }}</td>
                    <td scope="col">{{ $failedContact->bill_date }}</td>
                    <td scope="col">{{ $failedContact->due_date }}</td>
                    <td scope="col">{{ $failedContact->nominal }}</td>
                    <td scope="col">{{ $failedContact->failed }}</td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>

            <div class="col-md-12 mt-3">
              <form id="form-campaign-export" class="g-3 needs-validation" method="POST" target="_blank" action="{{ route('campaign.export.failed') }}" enctype="multipart/form-data">
                <a href="{{ route('campaign') }}" class="btn btn-secondary">Close</a>
                <button type="submit" class="btn btn-success btn-export-as" id="btn-export-excel" data-export-as="excel">Export Excel</button>
                {{ csrf_field() }}
                <input type="hidden" name="input_key" value="{{ session('key') }}">
                <input type="hidden" name="input_name" value="{{ session('name') }}">
                <input type="hidden" name="input_failed_contacts" value="{{ session('failed_contacts') }}">
              </form>
            </div>
          </div>
        </div>
        @endif

      </div>
    </div>
  </section>
</main>

@push('javascript')
<script type="text/javascript">
var previewContactDataContainer = '';
var savedContactDataContainer = '';
var failedContactDataContainer = '';

  $(document).ready(function() {
    preparePreviewContactTable();
    prepareSavedContactTable();
    prepareFailedContactsTable();
    prepareSubmitCampaign();

    $('#input-campaign-excel-file').val('');
    $('#input-campaign-rows').val('');
    $('#submit-spinner-save-contacts').hide();

    $('#input-campaign-excel-file').on("change", function(e) {
      handleFileAsync(e);
    });
    $('.btn-back').click(function(e) {
      history.back();
    });
  });

  function preparePreviewContactTable() {
    previewContactDataContainer = $('#table-preview-contact-data-container').DataTable({
      processing: true,
      lengthMenu: [5, 10, 15, 20, 50, 100],
      pageLength: 10,
      columns: [
        { data: 'account_id' },
        { data: 'name' },
        { data: 'phone' },
        { data: 'bill_date' },
        { data: 'due_date' },
        { data: 'nominal' }
      ],
      columnDefs: [
        {
          targets: 5,
          className: 'dt-body-right'
        }
      ]
    });
  };

  function prepareSavedContactTable() {
    savedContactDataContainer = $('#table-saved-contact-data-container').DataTable({
      processing: true,
      lengthMenu: [5, 10, 15, 20, 50, 100],
      pageLength: 10,
      columnDefs: [
        {
          targets: 5,
          className: 'dt-body-right'
        }
      ]
    });
  };

  function prepareFailedContactsTable() {
    failedContactDataContainer = $('#table-failed-contacts-container').DataTable({
      processing: true,
      lengthMenu: [5, 10, 15, 20, 50, 100],
      pageLength: 10,
      columnDefs: [
        {
          targets: 5,
          className: 'dt-body-right'
        }
      ]
    });
  };

  function prepareSubmitCampaign() {
    $('#form-create-campaign').submit(function() {
      if (($('#input-campaign-name').val().trim().length < 5) || ($('#input-campaign-rows').val().trim() === '')) {
        return false;
      }

      $('#btn-submit-create-campaign').addClass('disabled');
      $('.btn-back').addClass('disabled');
      $('#submit-spinner-save-contacts').show();
    });
  };

  async function handleFileAsync(e) {
    const file = e.target.files[0];
    const data = await file.arrayBuffer();
    const workbook = XLSX.read(data);
    const tempRows = [];

    const dataRows = XLSX.utils.sheet_to_json(
      workbook.Sheets[workbook.SheetNames[0]],
      { header:1 }
    );
    dataRows.shift();

    dataRows.map((v, k) => {
      tempRows.push({
        'account_id': v[0],
        'name': v[1],
        'phone': v[2],
        'bill_date': v[3],
        'due_date': v[4],
        'nominal': v[5]
      });
    });

    previewContactDataContainer.clear();
    previewContactDataContainer.rows.add(tempRows);
    previewContactDataContainer.draw();
    $('#input-campaign-rows').val(JSON.stringify(tempRows));
  };
</script>
@endpush

@include('layouts.include_page_footer')
@include('layouts.include_js')
