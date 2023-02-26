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
          <div class="card-body">
            <form id="form-update-campaign" class="g-3 needs-validation" method="POST" action="{{ route('campaign.update') }}" enctype="multipart/form-data">
              <h5 class="card-title">
                  Edit Campaign
              </h5>

              @if(session('already_running'))
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <h4 class="alert-heading">Campaign Already Running</h4>
                <p>This campaign already running once so can not be edited or changed</p>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
              @endif

              <div class="col-md-12">
                <div class="form-floating">
                  <input type="text" class="form-control" id="edit-campaign-name" name="name" minlength="4" maxlength="50" placeholder="Name" value="{{ $campaign->name or '' }}" required>
                  <label for="edit-campaign-name">Campaign Name</label>
                </div>
              </div>

              <div class="col-md-12 mt-3">
                <div class="row form-floating">
                  <div class="input-group">
                    <input class="form-control" type="file" id="edit-campaign-excel-file" accept=".xls, .xlsx">
                    <a href="{{ route('campaign.template') }}" class="btn btn-success" type="button">
                      <i class="bi bi-download"></i>
                      &nbsp; Contact Template
                    </a>
                  </div>
                </div>
              </div>
              
              <fieldset class="row mt-3 mb-4">
                <legend class="col-form-label col-sm-2 pt-0">New Data Action</legend>
                <div class="col-sm-10">
                  <div class="form-check">
                    <input class="form-check-input radio-campaign-edit-action" type="radio" name="campaign_edit_action" id="radio-campaign-edit-action-merge" value="merge" disabled required>
                    <label class="form-check-label" for="radio-campaign-edit-action-merge">Merge Contacts (add new data into old data)</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input radio-campaign-edit-action" type="radio" name="campaign_edit_action" id="radio-campaign-edit-action-replace" value="replace" disabled required>
                    <label class="form-check-label" for="radio-campaign-edit-action-replace">Replace Contacts (replace old data with new data)</label>
                  </div>
                </div>
              </fieldset>

              <hr>

              <div id="row-table-previous-contacts" class="col-md-12 mt-4">
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
                    </tr>
                  </thead>
    
                  <tbody>
                    @foreach ($contacts AS $keyData => $valueData)
                    <tr>
                      <td>{{ $valueData->account_id }}</td>
                      <td>{{ $valueData->name }}</td>
                      <td>{{ $valueData->phone }}</td>
                      <td>{{ $valueData->bill_date }}</td>
                      <td>{{ $valueData->due_date }}</td>
                      <td class="text-end">{{ number_format($valueData->nominal, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>

                {{ $contacts->links() }}
              </div>

              <div id="row-table-excel-contacts" class="col-md-12 mt-4">
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
                </table>
              </div>

              <div class="col-md-12 mt-4">
                <button type="button" class="btn btn-secondary btn-back">Cancel</button>
                <button type="submit" class="btn btn-outline-primary disabled" id="btn-submit-edit-campaign">Save</button>
                <input type="hidden" id="edit-new-campaign-rows" name="rows" value="">
                <input type="hidden" id="edit-campaign-key" name="campaign" value="_{{ $campaign->unique_key }}">
                <input type="hidden" id="edit-campaign-action" name="action" value="">
                {{ csrf_field() }}
                <input type="hidden" name="_method" value="PUT">
              </div>
            </form>
          </div>
        </div>
        @endif

        @if (session('saved_contacts'))
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Saved Uploaded Contacts</h5>

            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <h4 class="alert-heading">Data Input Error</h4>
              <p>Some contact data can not be saved. Please check the 'Failed Contacts' table below to see them.</p>
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
var previousCampaignName = '{{ $campaign->name }}';
var previousContactData= [];
var editAction = '';
var tempRows = [];

  $(document).ready(function() {
    preparePreviewContactTable();
    prepareSavedContactTable();
    prepareFailedContactsTable();
    prepareRadioButtons();
    // getContactList();

    $('#row-table-excel-contacts').hide();
    $('#edit-new-campaign-rows').val('');
    $('#edit-campaign-action').val('');
    $('#edit-campaign-excel-file').val('');
    $('#edit-campaign-excel-file').on("change", function(e) {
      handleFileAsync(e);
    });

    $('#edit-campaign-name').keyup(function() {
      var newName = $(this).val().trim();
      if (previousCampaignName.localeCompare(newName) != 0) {
        if (editAction === '') {
          $('#btn-submit-edit-campaign').removeClass('btn-outline-primary disabled').addClass('btn-primary');
        }
      }
      else {
        if ((editAction === '') && !$('#btn-submit-edit-campaign').hasClass('disabled')) {
          $('#btn-submit-edit-campaign').removeClass('btn-primary').addClass('btn-outline-primary disabled');
        }
      }
    });

    $('.btn-back').click(function(e) {
      history.back();
    });

    $('#form-update-campaign').submit(function() {
      $('.btn-back').addClass('disabled');
      $('#btn-submit-edit-campaign').addClass('disabled');
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

  function prepareRadioButtons() {
    $('#radio-campaign-edit-action-merge').prop('checked', false).prop('disabled', true);
    $('#radio-campaign-edit-action-replace').prop('checked', false).prop('disabled', true);
    $('.radio-campaign-edit-action').click(function(e) {
      $('#edit-campaign-action').val($(this).val());
      changeEditActionButtons();
    });
  };

  function getContactList() {
    $.ajax({
      method: 'GET',
      url: "{{ route('contact.list') }}/_{{ $campaign->unique_key}}",
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      processData: false,
      contentType: false,
      cache: false,
      success: function(response) {
        previousContactData = response.data;

        previewContactDataContainer.clear();
        previewContactDataContainer.rows.add(response.data);
        previewContactDataContainer.draw();
      },
      error: function(error) {
        console.log(error.responseText);
      }
    })
    .always(function() {
    });
  };

  async function handleFileAsync(e) {
    const file = e.target.files[0];
    const data = await file.arrayBuffer();
    const workbook = XLSX.read(data);
    const dataRows = XLSX.utils.sheet_to_json(
      workbook.Sheets[workbook.SheetNames[0]],
      { header:1 }
    );
    dataRows.shift();

    tempRows = [];
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

    $('#row-table-previous-contacts').hide();
    $('#row-table-excel-contacts').show();
    
    previewContactDataContainer.clear();
    previewContactDataContainer.rows.add(tempRows);
    previewContactDataContainer.draw();

    $('#edit-new-campaign-rows').val(JSON.stringify(tempRows));
    $('#btn-edit-contact-action-merge').removeClass('disabled');
    $('#btn-edit-contact-action-replace').removeClass('disabled');
    $('.radio-campaign-edit-action').removeAttr('disabled');
  };

  function changeEditActionButtons() {
    editAction = $('#edit-campaign-action').val();
    // previewContactDataContainer.clear();

    if (editAction === 'merge') {
      $('#btn-submit-edit-campaign').removeClass('btn-outline-primary disabled').addClass('btn-primary');
    }
    else if (editAction === 'replace') {
      $('#btn-submit-edit-campaign').removeClass('btn-outline-primary disabled').addClass('btn-primary');
    }

    // previewContactDataContainer.rows.add(tempRows);
    // previewContactDataContainer.draw();
  };
</script>
@endpush

@include('layouts.include_page_footer')
@include('layouts.include_js')
