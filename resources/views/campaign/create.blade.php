@include('layouts.include_page_header')
@include('layouts.include_sidebar')

<style>
  .readonly {
    background-color: #e9ecef;
  }
</style>

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
          <form id="form-create-campaign" class="g-3 needs-validation" method="POST" action="{{ url('campaigns') }}" enctype="multipart/form-data" novalidate>
            <div class="card-body">
              <h5 class="card-title">
                  Add Campaign
              </h5>

              @if ($errors->has('campaign_referense_exists'))
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <h4 class="alert-heading">Campaign Creation Error</h4>
                <p>{{ $errors->first('campaign_referense_exists') }}</p>
              </div>
              @endif

              @if ($errors->has('no_template_headers'))
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <h4 class="alert-heading">Campaign Creation Error</h4>
                <p>{{ $errors->first('no_template_headers') }}</p>
              </div>
              @endif

              <div id="alert-different-templates-container" class="alert alert-danger alert-dismissible fade show" role="alert">
                <h4 class="alert-heading">Campaign Creation Error</h4>
                <p><span class="fw-bold">Upload Contacts Data File</span> structure does not match the selected <span class="fw-bold">Campaign Template</span> structure.</p>
              </div>

              <!-- campaign name -->
              <div class="col-md-12">
                <label class="form-label" for="input-campaign-name">Campaign Name (5-100 chars)</label>
                <input
                  type="text"
                  class="form-control"
                  id="input-campaign-name"
                  name="name"
                  minlength="5" maxlength="100"
                  placeholder="Campaign name (5-100 chars)"
                  onkeyup="fillInputTemplateReference()"
                  required
                >
              </div>

              <!-- campaign templates -->
              <div class="col-md-12 mt-3">
                  <label class="form-label" for="option-campaign-templates">Campaign Templates</label>
                  <select class="form-select" id="option-campaign-templates" name="select_campaign_template" aria-label="Campaign Templates" required>
                    @php $headers = []; @endphp
                    @foreach ($templates AS $keyTemplate => $valTemplate)
                    <option value="{{ $valTemplate['id'] }}">{{ strtoupper($valTemplate['name']) }}</option>
                    @endforeach
                  </select>
              </div>

              {{--
              <!-- campaign template reference -->
              <div class="col-md-12 mt-3">
                <label class="form-label" for="input-template-reference">Campaign Template Reference</label>
                <input
                  type="text"
                  class="form-control readonly"
                  id="input-template-reference"
                  name="template_reference"
                  minlength="5" maxlength="100"
                  placeholder="Campaign template reference"
                  value=""
                  readonly required
                >
                  
                @if ($errors->has('template_reference'))
                <div class="invalid-feedback mt-1 mb-1" style="display:block">
                    <strong>{{ $errors->first('template_reference') }}</strong>
                </div>
                @endif
              </div>
              --}}

              <!-- campaign voice text -->
              <div class="col-md-12 mt-3 mb-3">
                <label class="form-label" for="campaign-text-voice">Voice Text</label>
                <textarea id="campaign-text-voice" name="campaign_text_voice" class="form-control" rows="6" placeholder="Campaign text message"></textarea>
              </div>

              <!-- campaign voice gender -->
              <fieldset class="row mt-3 mb-3">
                <legend class="col-form-label col-lg-3 col-md-2 col-sm-2 pt-0">Voice Gender</legend>
                <div class="col-lg-4 col-md-3 col-sm-5">
                  <div class="form-check">
                    <input
                      class="form-check-input"
                      type="radio"
                      name="campaign_input_voice_gender" id="radio-campaign-input-voice-male-normal"
                      value="male_normal"
                      required
                    >
                    <label class="form-check-label" for="radio-campaign-input-voice-male-normal">Male (normal)</label>
                  </div>
                  <div class="form-check">
                    <input
                      class="form-check-input"
                      type="radio"
                      name="campaign_input_voice_gender" id="radio-campaign-input-voice-male-strong"
                      value="male_strong"
                      required
                    >
                    <label class="form-check-label" for="radio-campaign-input-voice-male-strong">Male (strong)</label>
                  </div>
                </div>

                <div class="col-lg-4 col-md-3 col-sm-5">
                  <div class="form-check">
                    <input
                      class="form-check-input"
                      type="radio"
                      name="campaign_input_voice_gender" id="radio-campaign-input-voice-female-normal"
                      value="female_normal"
                      required checked
                    >
                    <label class="form-check-label" for="radio-campaign-input-voice-female-normal">Female (normal)</label>
                  </div>
                  <div class="form-check">
                    <input
                      class="form-check-input"
                      type="radio"
                      name="campaign_input_voice_gender" id="radio-campaign-input-voice-female-strong"
                      value="female_strong"
                      required
                    >
                    <label class="form-check-label" for="radio-campaign-input-voice-female-strong">Female (strong)</label>
                  </div>
                </div>

                @if ($errors->has('campaign_edit_voice_gender'))
                <div class="invalid-feedback mt-1 mb-1" style="display:block">
                    <strong>{{ $errors->first('campaign_edit_voice_gender') }}</strong>
                </div>
                @endif
              </fieldset>

              <!-- campaign upload file -->
              {{--
              <div class="col-md-12 mt-3">
                <div class="form-floating">
                  <div class="input-group">
                    <input class="form-control" type="file" id="input-campaign-excel-file" accept=".xls, .xlsx" required>
                    
                    <a href="{{ url('campaigns/template') }}" id="btn-download-campaign-template" class="btn btn-success" type="button">
                      <i class="bi bi-download"></i>
                      &nbsp; Contact Template
                    </a>
                    
                  </div>
                </div>
              </div>
              --}}

              <div class="col-md-12 mt-3 mb-4">
                <label class="form-label" for="input-campaign-excel-file">Upload Contacts Data File</label>

                <div class="col-md-12 input-group">
                  <input class="form-control" type="file" id="input-campaign-excel-file" accept=".xls, .xlsx" required>
                  <a href="{{ url('campaigns/template') }}" id="btn-download-campaign-template" class="btn btn-success" type="button">
                    <i class="bi bi-download"></i>
                    &nbsp; Contacts Data Template
                  </a>
                </div>
              </div>

              <hr />

              <!-- preview table -->
              <div class="col-md-12 mt-4 mb-2" id="div-preview-contact-data-container">
                <table id="table-preview-contact-data-container" class="table table-hover">
                  <thead>
                    <tr id="row-preview-contact-data-container">
                      {{-- <th scope="col">#</th> --}}
                      @foreach ($templates AS $key => $value)
                      <th scope="col">{{ strtoupper($value['headers'][0]->name) }}</th>
                      @endforeach
                    </tr>
                  </thead>

                  <tbody id="tbody-preview-contact-data-container">
                  </tbody>
                </table>
              </div>

              <!-- campaign notes -->
              <div class="col-md-12 mt-3 mb-2">
                <small>
                  <div class="row">
                    <div class="col-md-12">Column notes:</div>
                  </div>
                  <div class="row mt-1">
                    <div class="col-md-2">Mandatory</div>
                    <div class="col-md-10">Column must have value.</div>
                  </div>
                  <div class="row">
                    <div class="col-md-2">Unique</div>
                    <div class="col-md-10">Column must have value and each value is different from the others.</div>
                  </div>
                  <div class="row">
                    <div class="col-md-2">Voice</div>
                    <div class="col-md-10">Column will be played in voice-call, based on displayed-number sequence.</div>
                  </div>
                </small>
              </div>

              <!-- buttons container -->
              <div class="col-md-12 mt-4">
                <button type="button" class="btn btn-secondary btn-back">Cancel</button>
                <button type="submit" class="btn btn-primary" id="btn-submit-create-campaign">Save</button>&nbsp;
                <div id="submit-spinner-save-contacts" class="spinner-border spinner-border-sm text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
                <span id="submit-spinner-save-contacts-text">&nbsp;This may take a moment. Please wait...</span>
                {{ csrf_field() }}
                <input type="hidden" id="input-campaign-rows" name="input_campaign_rows" value="" required>
                <input type="hidden" id="input-template-reference" name="template_reference" required>
              </div>

            </div>
          </form>
        </div>
        @endif

        @if (session('failed_contacts'))
        <div id="input-failed-contacts-container" class="card">
          <div class="card-body">
            <h5 class="card-title">Failed Uploaded Contacts</h5>

            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <h4 class="alert-heading">Data Input Error</h4>
              <p>Some contact data can not be saved. Please check the table below to see them.</p>
            </div>

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
              </table>
            </div>

            <div class="col-md-12 mt-3">
              <form id="form-campaign-export" class="g-3 needs-validation" method="POST" target="_blank" action="{{ url('campaigns/export/failed') }}" enctype="multipart/form-data">
                <a href="{{ url('campaigns') }}" class="btn btn-secondary">Close</a>
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
<script src="{{ url('js/xlsx.full.min.js') }}"></script>
<script type="text/javascript">
var previewContactDataContainer = $('#table-preview-contact-data-container').DataTable();
var failedContactDataContainer = '';
var templates = JSON.parse('@php echo !empty($templates) ? json_encode($templates) : '[]' @endphp');
var selectedTemplate = '';
var headers = '';
var replacePattern = /\W+/ig;

var worker = '';

  $(document).ready(function() {
    initWorker();
    preparePreviewContactTable(0);
    // prepareFailedContactsTable();
    // prepareSubmitCampaign();

    $('#input-campaign-excel-file').val('');
    $('#input-campaign-rows').val('');
    $('#submit-spinner-save-contacts').hide();
    $('#submit-spinner-save-contacts-text').hide();
    $('#alert-different-templates-container').hide();

    $('#input-campaign-excel-file').on("change", function(e) {
      handleFileAsync(e);
    });

    $('#option-campaign-templates').on('change', function(e) {
      preparePreviewContactTable($(this).val());
      fillInputTemplateReference();

      var inputFileContainer = $('#input-campaign-excel-file');
      inputFileContainer[0].files = null;
      $(inputFileContainer).val('');
      
      $('#alert-different-templates-container').hide();
      $('#input-campaign-rows').val('');
    });

    $('#btn-download-campaign-template').click(function(e) {
      e.preventDefault();
      window.location.href = '{{ url('campaigns/template') }}/' + $('#option-campaign-templates').val();
    });
    
    $('.btn-back').click(function(e) {
      history.back();
    });

    $('#option-campaign-templates').trigger('change');

    @if (isset($failed_contacts))
    populateFailedContacts();
    @endif
  });

  function preparePreviewContactTable(templateId) {
    previewContactDataContainer.destroy();
    $('#table-preview-contact-data-container').remove();

    var columns = [];
    var columnDefs = [];
    selectedTemplate = templates['t_' + templateId];

    var tempPreviewTable = '<table id="table-preview-contact-data-container" class="table table-hover">';
    tempPreviewTable += '  <thead class="align-top">';
    tempPreviewTable += '    <tr id="row-preview-contact-data-container"></tr>';
    tempPreviewTable += '  </thead>';
    tempPreviewTable += '</table>';
    $('#div-preview-contact-data-container').append(tempPreviewTable);
    
    if (selectedTemplate instanceof Object) {
      headers = selectedTemplate.headers;

      if (headers.length > 0) {
        headers.map((v, k) => {
          var tempInfo = [];
          if (v.is_mandatory === 1) { tempInfo.push('mandatory'); }
          if (v.is_unique === 1) { tempInfo.push('unique'); }
          if (v.is_voice === 1) { tempInfo.push('voice-' + v.voice_position); }
          if (tempInfo.length > 0) {
            columns.title += '<br>(' + tempInfo.join(', ') + ')';
          }

          columns.push({
            title: v.header_name.toUpperCase() + ((tempInfo.length > 0) ? '<br><small class="fw-lighter">(' + tempInfo.join(', ') + ')</small>' : ''),
            data: v.header_name.replaceAll(replacePattern, '_').toLowerCase()
          });

          if (v.column_type === 'numeric') {
            columnDefs.push({
              targets: k,
              className: 'dt-body-right'
            });
          }

          // $('#row-preview-contact-data-container').append('<th scope="col">' + v.header_name + '</th>');
        });

        previewContactDataContainer = $('#table-preview-contact-data-container').DataTable({
          processing: true,
          lengthMenu: [5, 10, 15, 20, 50, 100],
          pageLength: 15,
          columns: columns,
          columnDefs: columnDefs
        });
      }
    }

    // console.log(columns);
    // console.log(columnDefs);
  };

  function prepareFailedContactsTable() {
    failedContactDataContainer = $('#table-failed-contacts-container').DataTable({
      processing: true,
      lengthMenu: [5, 10, 15, 20, 50, 100],
      pageLength: 10,
      columns: [
        { data: 'account_id' },
        { data: 'name' },
        { data: 'phone' },
        { data: 'bill_date' },
        { data: 'due_date' },
        { data: 'nominal' },
        { 
          data: 'failed',
          render: function(data, type) {
            return '<span class="text-danger">' + data + '</span>';
          }
        }
      ],
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

      worker.postMessage({
        action: 'hide_interactive_elements'
      });
    });
  };

  async function handleFileAsync(e) {
    $('#alert-different-templates-container').hide();
    $('#input-campaign-rows').val('');

    worker.postMessage({
      action: 'hide_interactive_elements'
    });

    const file = e.target.files[0];
    const data = await file.arrayBuffer();
    const workbook = XLSX.read(data, { dense:true });
    const tempNewPhoneRows = [];
    const campaignRows = [];

    const dataRows = XLSX.utils.sheet_to_json(
      workbook.Sheets[workbook.SheetNames[0]],
      { header:1 }
    );

    var tempHeaders = [];
    headers.map((val, key) => {
      tempHeaders.push(val.header_name.toLowerCase().replaceAll(replacePattern, ''));
    });
    // console.log(tempHeaders);

    var isTemplateOk = true;
    dataRows[0].map((val, key) => {
      var tempName = val.toLowerCase();

      if (tempName.indexOf('(') > -1) {
        tempName = val.substr(0, val.indexOf('(')).toLowerCase();
      }
      tempName = tempName.replaceAll(replacePattern, '');
      // console.log(tempName);
      
      if (!tempHeaders.includes(tempName)) {
        isTemplateOk = false;
      }
    });

    if (isTemplateOk) {
      dataRows.shift();

      $.each(dataRows, function(k, v) {
        var tempNewRow = {};

        headers.map((val, key) => {
          tempNewRow[val.header_name.replaceAll(replacePattern, '_').toLowerCase()] = v[key] || '';
        });

        tempNewPhoneRows.push(tempNewRow);
        campaignRows.push(tempNewRow);
      });

      $('#input-campaign-rows').val(JSON.stringify(tempNewPhoneRows));
    }
    else {
      $('#alert-different-templates-container').show();
    }

    previewContactDataContainer.clear();
    previewContactDataContainer.rows.add(tempNewPhoneRows);
    previewContactDataContainer.draw();
    
    worker.postMessage({
      action: 'show_interactive_elements'
    });
  };

  function fillInputTemplateReference() {
    var templateIdx = $('#option-campaign-templates').val();
    var referenceName = '';

    if (templateIdx !== '') {
      var today = new Date();
      var templateName = templates['t_' + templateIdx].name;
      var campaignName = $('#input-campaign-name').val();
      referenceName = 'c_' + campaignName.substr(0, 6) + '_t_' + templateName.substr(0, 6);
      referenceName = referenceName.replaceAll(replacePattern, '_').toLowerCase().substr(0, 100) + '_' + String(today.getTime()).substr(6);
    }

    $('#input-template-reference').val(referenceName);
  };

  function isInputFieldsDisplayed(isDisplayed) {
    if (isDisplayed) {
      $('#input-campaign-name').removeAttr('disabled');
      $('#input-campaign-excel-file').removeAttr('disabled');
      $('#btn-submit-create-campaign').removeClass('disabled');
      $('#submit-spinner-save-contacts').hide();
      $('#submit-spinner-save-contacts-text').hide();
      $('.btn-back').removeClass('disabled');
    }
    else {
      $('#input-campaign-name').attr('disabled', 'disabled');
      $('#input-campaign-excel-file').attr('disabled', 'disabled');
      $('#btn-submit-create-campaign').addClass('disabled');
      $('#submit-spinner-save-contacts').show();
      $('#submit-spinner-save-contacts-text').show();
      $('.btn-back').addClass('disabled');
    }
  };

  function initWorker() {
    worker = new Worker('{{ url('js/demo_worker.js') }}');
    worker.onmessage = function(e) {
      // console.log(e.data);
      if (e.data.action === 'from_worker_hide_interactive_elements') {
        isInputFieldsDisplayed(false);
      }
      if (e.data.action === 'from_worker_show_interactive_elements') {
        isInputFieldsDisplayed(true);
      }
      else if (e.data.action === 'from_worker_start_get_progress_import') {
        // initSseImportProgress();
      }
    }
  };

  @if (isset($failed_contacts))
  function populateFailedContacts() {
    var failedContactsText = '@php echo($failed_contacts); @endphp';
    var failedContactsObjects = JSON.parse(failedContactsText);

    $('#input-failed-contacts').val(failedContactsText);

    failedContactDataContainer.clear();
    failedContactDataContainer.rows.add(failedContactsObjects);
    failedContactDataContainer.draw();
  }
  @endif

  /*
  async function handleFileAsync(e) {
    worker.postMessage({
      action: 'hide_interactive_elements'
    });
    
    const file = e.target.files[0];
    const data = await file.arrayBuffer();
    const workbook = XLSX.read(data, { dense:true });
    const tempNewPhoneRows = [];
    const campaignRows = [];

    const dataRows = XLSX.utils.sheet_to_json(
      workbook.Sheets[workbook.SheetNames[0]],
      { header:1 }
    );
    dataRows.shift();

    $.each(dataRows, function(k, v) {
      var tempNewRow = {
        'account_id': v[0],
        'name': v[1],
        'phone': v[2],
        'bill_date': v[3],
        'due_date': v[4],
        'nominal': v[5]
      };

      customHeaders.map((val, key) => {
        tempNewRow[val.name.toLowerCase()] = v[5 + (key + 1)];
      });

      tempNewPhoneRows.push(tempNewRow);
      campaignRows.push(tempNewRow);
    });

    previewContactDataContainer.clear();
    previewContactDataContainer.rows.add(tempNewPhoneRows);
    previewContactDataContainer.draw();

    $('#input-campaign-rows').val(JSON.stringify(tempNewPhoneRows));
    
    worker.postMessage({
      action: 'show_interactive_elements'
    });
  };
  
  function preparePreviewContactTable() {
    var columns = [
      { data: 'account_id' },
      { data: 'name' },
      { data: 'phone' },
      { data: 'bill_date' },
      { data: 'due_date' },
      { data: 'nominal' }
    ];
    var columnDefs = [
      {
        targets: 5,
        className: 'dt-body-right'
      }
    ];

    customHeaders.map((v, k) => {
      columns.push({
        data: v.name.toLowerCase()
      });

      if (v.column_type === 'numeric') {
        columnDefs.push({
          targets: 5 + (k + 1),
          className: 'dt-body-right'
        });
      }
    });

    previewContactDataContainer = $('#table-preview-contact-data-container').DataTable({
      processing: true,
      lengthMenu: [5, 10, 15, 20, 50, 100],
      pageLength: 15,
      columns: columns,
      columnDefs: columnDefs
    });
  };
  */
</script>
@endpush

@include('layouts.include_page_footer')
@include('layouts.include_js')
