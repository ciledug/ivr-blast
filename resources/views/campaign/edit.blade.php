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
          <form id="form-create-campaign" class="g-3 needs-validation" method="POST" action="{{ route('campaigns.update', [ 'campaign' => $campaign_headers[0]->camp_id ]) }}" enctype="multipart/form-data" novalidate>
            <div class="card-body">
              <h5 class="card-title">
                  Edit Campaign
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
                <label class="form-label" for="input-campaign-name">Campaign Name (5-50 chars)</label>
                <input
                  type="text"
                  class="form-control"
                  id="input-campaign-name"
                  name="name"
                  minlength="5" maxlength="50"
                  placeholder="Campaign name (5-50 chars)"
                  value="{{ $campaign_headers[0]->camp_name }}"
                  required
                >
              </div>

              <!-- campaign templates -->
              <div class="col-md-12 mt-3">
                  <label class="form-label" for="option-campaign-templates">Campaign Templates</label>
                  <select class="form-select" id="option-campaign-templates" name="selected_template" aria-label="Campaign Templates" required>
                    @php $headers = []; @endphp
                    @foreach ($templates AS $keyTemplate => $valTemplate)
                    <option value="{{ $valTemplate['id'] }}">{{ strtoupper($valTemplate['name']) }}</option>
                    @endforeach
                  </select>
              </div>

              <!-- campaign voice text -->
              <div class="col-md-12 mt-3 mb-3">
                <label class="form-label" for="campaign-text-voice">Preview Voice Text</label>
                <textarea
                  id="campaign-text-voice" name="campaign_text_voice"
                  class="form-control disabled"
                  rows="6"
                  placeholder="Campaign text message"
                  readonly disabled
                ></textarea>
              </div>

              <!-- campaign upload/download template -->
              <div class="col-md-12 mt-3">
                <label class="form-label" for="input-campaign-excel-file">Upload Contacts Data File</label>

                <div class="col-md-12 input-group">
                  <input class="form-control" type="file" id="input-campaign-excel-file" accept=".xls, .xlsx">
                  <button id="btn-download-campaign-template" class="btn btn-success" type="button">
                    <i class="bi bi-download"></i>
                    &nbsp; Contacts Data Template
                  </button>
                </div>

                <div id="different-templates-message" class="invalid-feedback mt-1 mb-1" style="display:block">
                  <span class="fw-bold">Upload Contacts Data File</span> structure does not match the selected <span class="fw-bold">Campaign Template</span>
                  structure.
                </div>
              </div>

              <!-- upload contacts action -->
              <fieldset class="row mt-3 mb-4">
                <legend class="col-form-label col-lg-3 col-md-2 col-sm-2 pt-0">Upload Contacts Action</legend>
                <div class="col-lg-9 col-md-10 col-sm-10">
                  <div class="form-check">
                    <input class="form-check-input radio-campaign-edit-action" type="radio" name="campaign_edit_action" id="radio-campaign-edit-action-merge" value="merge" disabled>
                    <label class="form-check-label" for="radio-campaign-edit-action-merge">Merge Contacts (add new contacts into old contacts)</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input radio-campaign-edit-action" type="radio" name="campaign_edit_action" id="radio-campaign-edit-action-replace" value="replace" disabled>
                    <label class="form-check-label" for="radio-campaign-edit-action-replace">Replace Contacts (delete all old contacts and add new contacts)</label>
                  </div>
                </div>
              </fieldset>

              <!-- progress bar --->
              <div class="col-md-12 mt-3 mb-3">
                <label id="progress-bar-title" class="form-label disabled">Progress</label>&nbsp;
                <div class="spinner-border spinner-border-sm text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
                {{--
                <div class="progress" style="height:50px;">
                  <div
                      id="progress-bar-value"
                      class="progress-bar progress-bar-striped active"
                      role="progressbar"
                      aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"
                      style="width:0%"
                  >0%</div>
                </div>
                --}}
              </div>

              <hr />

              <!-- data check alert -->
              <div id="alert-upload-problems" class="alert alert-danger alert-dismissible fade show" role="alert">
                <h4 class="alert-heading">Data Check Results</h4>
                <p>
                  <span class="text-danger">
                    There are <span class="fw-bold"><span id="upload-problems-rows-count"></span> row(s)</span> with incorrect data.
                    {{-- with <span class="fw-bold"><span id="upload-problems-data-count"></span> problem(s)</span> or invalid data. --}}
                  </span>
                </p>
              </div>

              <!-- preview table -->
              <div class="col-md-12 mt-4 mb-2" id="div-preview-contact-data-container">
                <table id="table-preview-contact-data-container" class="table table-hover">
                  <thead>
                    <tr id="row-preview-contact-data-container">
                      {{-- <th scope="col">#</th> --}}
                      @foreach ($campaign_headers AS $key => $value)
                      <th scope="col">{{ strtoupper($value->name) }}</th>
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
                <input type="hidden" name="_method" value="PUT">
                <input type="hidden" id="input-campaign-rows" name="input_campaign_rows" value="" required>
                <input type="hidden" id="input-previous-campaign" name="previous_campaign" value="" required>
                <input type="hidden" id="input-previous-template" name="previous_template" value="" required>
                {{-- <input type="hidden" id="input-template-reference" name="template_reference" required> --}}
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
<script src="{{ url('js/luxon.min.js') }}"></script>
<script src="https://unpkg.com/libphonenumber-js@1.9.6/bundle/libphonenumber-max.js"></script>
<script type="text/javascript">
var previewContactDataContainer = $('#table-preview-contact-data-container').DataTable();
var failedContactDataContainer = '';
var templates = JSON.parse('@php echo !empty($templates) ? json_encode($templates) : '[]' @endphp');
var selectedTemplate = '';
var selectedHeaders = '';
var replacePattern = /\W+/ig;

var worker = '';
var previousCampaigns = JSON.parse('@php echo isset($campaign_headers) ? json_encode($campaign_headers) : '{}'; @endphp');
var previousContacts = JSON.parse('@php echo $contacts->count() > 0 ? json_encode($contacts) : '[]' @endphp');
var previousUniqueValues = [];
var tempNewValidMergeContacts = [];
var tempNewInvalidMergeContacts = [];
var tempNewValidReplaceContacts = [];
var tempNewInvalidReplaceContacts = [];
var dataRows = null;
var tempPreviewTableContacts = [];

  $(document).ready(function() {
    initWorker();
    preparePreviewContactTable(0);
    // prepareFailedContactsTable();
    prepareSubmitCampaign();
    showUploadProblemsReport(false, 0, 0);
    checkPreviousUniqueValues();
    setInputFieldsVisibility(true);

    $('#input-campaign-excel-file').val('');
    $('#input-campaign-rows').val('');

    $('#alert-different-templates-container').hide();
    $('#input-campaign-excel-file').removeClass('is-invalid');
    $('#different-templates-message').hide();

    $('#input-campaign-excel-file').on("change", function(e) {
      // worker.postMessage({
      //   action: 'open_data_file'
      // });

      setTimeout(() => {
        handleFileAsync(e);
      }, 1000);
    });

    $('#option-campaign-templates').on('change', function(e) {
      $('#progress-bar-value').text('0%');
      $('#progress-bar-value').css('width', '0%');

      selectedTemplate = templates['t_' + $(this).val()];
      selectedHeaders = selectedTemplate.headers;

      var inputFileContainer = $('#input-campaign-excel-file');
      inputFileContainer[0].files = null;
      $(inputFileContainer).val('');
      
      $('#campaign-text-voice').val(selectedTemplate.voice_text);
      $('#alert-different-templates-container').hide();
      $('#input-campaign-rows').val('').trigger('change');

      setEnabledDataAction(false, false);
      showUploadProblemsReport(false, 0, 0);
      preparePreviewContactTable();

      if (selectedTemplate.id === previousCampaigns[0].camp_templ_id) {
        previewContactDataContainer.rows.add(previousContacts);
        previewContactDataContainer.draw();
      }
    });

    $('.radio-campaign-edit-action').on('click', function(e) {
      var editAction = $(this).val().toLowerCase();
      previewContactDataContainer.rows().remove().draw();
      showUploadProblemsReport(false, 0, 0);

      console.log(editAction);
      console.log('tempNewValidMergeContacts: ' + tempNewValidMergeContacts.length);
      console.log('tempNewInvalidMergeContacts: ' + tempNewInvalidMergeContacts.length);
      console.log('tempNewValidReplaceContacts: ' + tempNewValidReplaceContacts.length);
      console.log('tempNewInvalidReplaceContacts: ' + tempNewInvalidReplaceContacts.length);

      if (editAction == 'merge') {
        if (tempNewValidMergeContacts.length == 0 && tempNewInvalidMergeContacts.length == 0) {
          $('#progress-bar-title').text('Checking data, please wait...');

          var tempTableContacts = JSON.parse(JSON.stringify(tempPreviewTableContacts));
          var tempPreviousUniques = JSON.parse(JSON.stringify(previousUniqueValues));

          tempTableContacts.map((valContact, keyContact) => {
            var tempValContact = JSON.parse(JSON.stringify(valContact));

            tempValContact.col_info.map((valColInfo, keyConInfo) => {
              if (valColInfo.is_unique) {
                if (tempPreviousUniques.includes(valColInfo.value)) {
                  tempValContact.errors.push('<small class="text-danger"><span class="fw-bold">' + valColInfo.name.toUpperCase() + '</span> data duplicate</small>');
                }
                else {
                  tempPreviousUniques.push(valColInfo.value);
                }
              }
            });

            if (tempValContact.errors.length > 0) { 
              tempValContact.errors = tempValContact.errors.join('<br>');
              tempNewInvalidMergeContacts.push(tempValContact);
            }
            else {
              tempNewValidMergeContacts.push(tempValContact);
            }

            setTimeout((keyContact, tempTableContacts) => {
              if (tempTableContacts !== undefined) {
                var percentage = ((keyContact + 1) / tempTableContacts.length) * 100;
                $('#progress-bar-title-percentage').text(percentage.toFixed(2));
              }
            }, 800, keyContact, tempTableContacts);

            tempValContact = undefined;
            valColInfo = undefined;
            keyConInfo = undefined;
          });

          valContact = undefined;
          keyContact = undefined;
          tempPreviousUniques = undefined;
          tempTableContacts = undefined;
        }

        if (previousContacts.length > 0) previewContactDataContainer.rows.add(previousContacts).draw();
        if (tempNewValidMergeContacts.length > 0) previewContactDataContainer.rows.add(tempNewValidMergeContacts).draw();
        if (tempNewInvalidMergeContacts.length > 0) previewContactDataContainer.rows.add(tempNewInvalidMergeContacts).draw();

        showUploadProblemsReport(true, tempNewInvalidMergeContacts.length, null);
        $('#input-campaign-rows').val(JSON.stringify(tempNewValidMergeContacts));
      }
      else if (editAction == 'replace') {
        if (tempNewValidReplaceContacts.length == 0 && tempNewInvalidReplaceContacts.length == 0) {
          $('#progress-bar-title').text('Checking data, please wait...');

          var tempTableContacts = JSON.parse(JSON.stringify(tempPreviewTableContacts));
          var tempPreviousUniques = [];

          tempTableContacts.map((valContact, keyContact) => {
            var tempValContact = JSON.parse(JSON.stringify(valContact));

            tempValContact.col_info.map((valColInfo, keyColInfo) => {
              if (valColInfo.is_unique) {
                if (tempPreviousUniques.includes(valColInfo.value)) {
                  tempValContact.errors.push('<small class="text-danger"><span class="fw-bold">' + valColInfo.name.toUpperCase() + '</span> data duplicate</small>');
                }
                else {
                  tempPreviousUniques.push(valColInfo.value);
                }
              }
            });

            if (valContact.errors.length > 0) {
              valContact.errors = valContact.errors.join('<br>');
              tempNewInvalidReplaceContacts.push(valContact);
            }
            else tempNewValidReplaceContacts.push(valContact);

            setTimeout((keyContact, tempTableContacts) => {
              if (tempTableContacts !== undefined) {
                var percentage = ((keyContact + 1) / tempTableContacts.length) * 100;
                $('#progress-bar-title-percentage').text(percentage);
              }
            }, 800, keyContact, tempTableContacts);

            tempValContact = undefined;
            valContact = undefined;
            keyContact = undefined;
          });

          tempPreviousUniques = undefined;
          tempTableContacts = undefined;
        }

        if (tempNewValidReplaceContacts.length > 0) previewContactDataContainer.rows.add(tempNewValidReplaceContacts).draw();
        if (tempNewInvalidReplaceContacts.length > 0) previewContactDataContainer.rows.add(tempNewInvalidReplaceContacts).draw();

        showUploadProblemsReport(true, tempNewInvalidReplaceContacts.length, null);
        $('#input-campaign-rows').val(JSON.stringify(tempNewValidReplaceContacts));
      }

      $('#btn-submit-create-campaign').removeClass('disabled').removeAttr('disabled');
      setInputFieldsVisibility(true);

      console.log('tempNewValidMergeContacts: ' + tempNewValidMergeContacts.length);
      console.log('tempNewInvalidMergeContacts: ' + tempNewInvalidMergeContacts.length);
      console.log('tempNewValidReplaceContacts: ' + tempNewValidReplaceContacts.length);
      console.log('tempNewInvalidReplaceContacts: ' + tempNewInvalidReplaceContacts.length);

      $('#progress-bar-title').text('Checking data finished');
    });

    $('#btn-download-campaign-template').click(function(e) {
      e.preventDefault();
      window.location.href = '{{ url('templates/download') }}/' + $('#option-campaign-templates').val();
    });
    
    $('.btn-back').click(function(e) {
      history.back();
    });

    $('#option-campaign-templates').val(previousCampaigns[0].camp_templ_id).trigger('change');

    @if (isset($failed_contacts))
    // populateFailedContacts();
    @endif
  });

  function checkPreviousUniqueValues() {
    if (typeof previousContacts !== 'undefined') {
      previousContacts.map((valContact, keyContact) => {
        previousCampaigns.map((valCampaign, keyCampaign) => {
          if (valCampaign.th_is_unique) {
            var tempHeaderName = valCampaign.th_name.toLowerCase().replaceAll(replacePattern, '_');
            previousUniqueValues.push(valContact[tempHeaderName]);
          }
        });
      });
    }
  };

  function preparePreviewContactTable() {
    previewContactDataContainer.destroy();
    $('#table-preview-contact-data-container').remove();

    var columns = [];
    var columnDefs = [];

    var tempPreviewTable = '<table id="table-preview-contact-data-container" class="table table-hover">';
    tempPreviewTable += '  <thead class="align-top">';
    tempPreviewTable += '    <tr id="row-preview-contact-data-container"></tr>';
    tempPreviewTable += '  </thead>';
    tempPreviewTable += '</table>';
    $('#div-preview-contact-data-container').append(tempPreviewTable);
    
    if (selectedTemplate instanceof Object) {
      if (selectedHeaders.length > 0) {
        selectedHeaders.map((v, k) => {
          var tempInfo = [];
          if (v.is_mandatory === 1) { tempInfo.push('mandatory'); }
          if (v.is_unique === 1) { tempInfo.push('unique'); }
          if (v.is_voice === 1) { tempInfo.push('voice-' + v.voice_position); }
          if (tempInfo.length > 0) { columns.title += '<br>(' + tempInfo.join(', ') + ')'; }

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
        });

        columns.push({
          title: 'PROBLEMS',
          data: 'errors'
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
    
    columnDefs = undefined;
    columns = undefined;
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
      var postedNewContacts = JSON.parse($('#input-campaign-rows').val());
      var editAction = '';

      if ($('#radio-campaign-edit-action-merge').is(':checked')) {
        editAction = 'merge';
      }
      else if ($('#radio-campaign-edit-action-replace').is(':checked')) {
        editAction = 'replace';
      };

      if (postedNewContacts.length > 0) {
        if ((editAction === 'merge') || (editAction === 'replace')) {}
        else { return false; }
      }

      $('#input-previous-campaign').val(previousCampaigns[0].camp_id);
      $('#input-previous-template').val(previousCampaigns[0].camp_templ_id);
      $('#progress-bar-title').text('Uploading data, please wait...');

      worker.postMessage({
        action: 'hide_interactive_elements'
      });
    });
  };

  async function handleFileAsync(e) {
    // showUploadProblemsReport(false, 0, 0);
    $('#alert-different-templates-container').hide();
    $('#input-campaign-rows').val('');
    // $('#progress-bar-title').text('Checking data, please wait...');

    tempNewValidMergeContacts = [];
    tempNewInvalidMergeContacts = [];
    tempNewValidReplaceContacts = [];
    tempNewInvalidReplaceContacts = [];

    worker.postMessage({
      action: 'clear_preview_table'
    });

    var file = e.target.files[0];
    
    if (file != undefined) {
      var fileArrayBuffer = await file.arrayBuffer();
      var workbook = XLSX.read(fileArrayBuffer, { dense:true });

      dataRows = XLSX.utils.sheet_to_json(
        workbook.Sheets[workbook.SheetNames[0]],
        { header:1 }
      );
      // console.log(dataRows);

      workbook = undefined;
      fileArrayBuffer = undefined;

      readExcelData(dataRows);
    }

    file = undefined;
  };

  async function readExcelData(dataRows) {
    var tempHeaders = [];
    var isTemplateOk = true;
    var tempRowData = [];
    var tempUniques = [];

    selectedHeaders.map((val, key) => {
      tempHeaders.push({
        name: val.header_name.toLowerCase().replaceAll(replacePattern, ''),
        type: val.column_type,
        is_mandatory: val.is_mandatory,
        is_unique: val.is_unique
      });
    });
    // console.log(tempHeaders);

    // --- template checking
    if (dataRows[0].length == tempHeaders.length) {
      dataRows[0].map((val, key) => {
        var tempName = val.toLowerCase();

        if (tempName.indexOf('(') > -1) {
          tempName = val.substr(0, val.indexOf('(')).toLowerCase();
        }
        tempName = tempName.replaceAll(replacePattern, '');
        // console.log(tempName);
        
        if (tempName !== tempHeaders[key].name) {
          isTemplateOk = false;
        }

        tempName = null;
      });
    }
    else {
      isTemplateOk = false;
    }

    if (isTemplateOk) {
      var tempDataRows = [];
      var newTempContacts = [];
      var percentage = 0;
      var dataCount = 0;
      var dataErrorsCount = 0;
      var tempPreviousUniques = [];
      tempPreviewTableContacts = [];

      previousUniqueValues.map((valUnique, keyUnique) => {
        tempPreviousUniques.push(valUnique);
      });

      dataRows.shift();
      dataCount = dataRows.length;

      dataRows.map((valDataRow, keyDataRow) => {
        var tempNewRow = {
          col_info: [],
          errors: [],
          value: ''
        };
        var tempHeaderName = '';
        var tempErrors = [];
        var tempReplaceActionErrors = [];
        var isContentOk = true;

        var tempValidMerge = [];
        var tempValidReplace = [];

        tempHeaders.map((valHeader, keyHeader) => {
          tempHeaderName = valHeader.name;
          tempNewRow[tempHeaderName] = valDataRow[keyHeader];
          // console.log('tempHeaderName: ' + tempHeaderName + ', type: ' + valHeader.type + ', tempNewRow[tempHeaderName]: ' + tempNewRow[tempHeaderName]);

          if (tempNewRow[tempHeaderName] === undefined || tempNewRow[tempHeaderName] === null) {
            tempNewRow[tempHeaderName] = '';
          }

          switch (valHeader.type) {
            case 'numeric':
              tempNewRow.col_info.push({ name: tempHeaderName, type: valHeader.type, value: tempNewRow[tempHeaderName], is_unique: valHeader.is_unique });
              if ((tempNewRow[tempHeaderName].length > 0) && isNaN(tempNewRow[tempHeaderName].value)) {
                tempNewRow.errors.push('<small class="text-danger"><span class="fw-bold">' + tempHeaderName.toUpperCase() + '</span> data invalid</small>');
                dataErrorsCount++;
              }
              break;
            case 'datetime':
              tempNewRow.col_info.push({ name: tempHeaderName, type: valHeader.type, value: tempNewRow[tempHeaderName], is_unique: valHeader.is_unique });
              if (tempNewRow[tempHeaderName].length > 0) {
                var dateTime = luxon.DateTime.fromFormat(tempNewRow[tempHeaderName], 'yyyy-MM-dd HH:mm:ss');
                if (!dateTime.isValid) {
                  tempNewRow.errors.push('<small class="text-danger"><span class="fw-bold">' + tempHeaderName.toUpperCase() + '</span> data invalid</small>');
                  dataErrorsCount++;
                }
                dateTime = undefined;
              }
              break;
            case 'date':
              tempNewRow.col_info.push({ name: tempHeaderName, type: valHeader.type, value: tempNewRow[tempHeaderName], is_unique: valHeader.is_unique });
              if (tempNewRow[tempHeaderName].length > 0) {
                var dateTime = luxon.DateTime.fromFormat(tempNewRow[tempHeaderName], 'yyyy-MM-dd');
                if (!dateTime.isValid) {
                  tempNewRow.errors.push('<small class="text-danger"><span class="fw-bold">' + tempHeaderName.toUpperCase() + '</span> data invalid</small>');
                  dataErrorsCount++;
                }
                dateTime = undefined;
              }
              break;
            case 'time':
              tempNewRow.col_info.push({ name: tempHeaderName, type: valHeader.type, value: tempNewRow[tempHeaderName], is_unique: valHeader.is_unique });
              if (tempNewRow[tempHeaderName].length > 0) {
                var dateTime = luxon.DateTime.fromFormat(tempNewRow[tempHeaderName], 'HH:mm:ss');
                if (!dateTime.isValid) {
                  tempNewRow.errors.push('<small class="text-danger"><span class="fw-bold">' + tempHeaderName.toUpperCase() + '</span> data invalid</small>');
                  dataErrorsCount++;
                }
                dateTime = undefined;
              }
              break;
            case 'handphone':
              tempNewRow.col_info.push({ name: tempHeaderName, type: valHeader.type, value: tempNewRow[tempHeaderName], is_unique: valHeader.is_unique });
              if (tempNewRow[tempHeaderName].length > 0) {
                if (isNaN(tempNewRow[tempHeaderName])) {
                  tempNewRow.errors.push('<small class="text-danger"><span class="fw-bold">' + tempHeaderName.toUpperCase() + '</span> data invalid</small>');
                  dataErrorsCount++;
                }
                else {
                  var phone = libphonenumber.parsePhoneNumber(tempNewRow[tempHeaderName], 'ID');
                  if (!phone.isValid()) {
                    tempNewRow.errors.push('<small class="text-danger"><span class="fw-bold">' + tempHeaderName.toUpperCase() + '</span> data invalid</small>');
                    dataErrorsCount++;
                  }
                  phone = undefined;
                }
              }
              break;
            default:
              tempNewRow.col_info.push({ name: tempHeaderName, type: valHeader.type, value: tempNewRow[tempHeaderName], is_unique: valHeader.is_unique });
              break;
          }
          
          if (valHeader.is_mandatory) {
            if (tempNewRow[tempHeaderName].length == 0) {
              tempNewRow.errors.push('<small class="text-danger"><span class="fw-bold">' + tempHeaderName.toUpperCase() + '</span> data required</small>');
              dataErrorsCount++;
            }
          }
        });
        
        tempPreviewTableContacts.push(tempNewRow);

        // ---
        // --- these lines to update progress bar
        // ---
        // percentage = (((keyDataRow + 1) / dataCount) * 100).toFixed(2);
        // setTimeout((percentage) => {
        //   updateProgressBar(percentage);
        // }, 800, percentage);

        // console.log(tempNewRow);
        tempNewRow = undefined;
      });
      
      console.log('contacts: ' + tempPreviewTableContacts.length);

      setTimeout(() => {
        if (selectedTemplate.id === previousCampaigns[0].camp_templ_id) {
          setEnabledDataAction(true, true);
          $('#radio-campaign-edit-action-merge').trigger('click');
        } else {
          setEnabledDataAction(false, true);
          $('#radio-campaign-edit-action-replace').trigger('click');
        }
      }, 1500);

      percentage = undefined;
      newTempContacts = undefined;
      tempDataRows = undefined;
    }
    else {
      $('#input-campaign-excel-file').addClass('is-invalid');
      $('#different-templates-message').show();
      $('#alert-different-templates-container').show();
      $('#btn-submit-create-campaign').addClass('disabled').attr('disabled', 'disabled');

      worker.postMessage({
        action: 'show_interactive_elements'
      });
    }

    tempUniques = undefined;
    tempRowData = undefined;
    isTemplateOk = undefined;
    tempHeaders = undefined;
  };

  function updateProgressBar(percentage) {
    // console.log('percentage: ' + percentage);
    $('#upload-progress').val(percentage + '%');
    $('#progress-bar-value').text(percentage + '%');
    $('#progress-bar-value').css({ 'width': percentage + '%' });
  };

  function setInputFieldsVisibility(isDisplayed) {
    if (isDisplayed) {
      $('#input-campaign-name').removeAttr('disabled');
      $('#option-campaign-templates').removeAttr('disabled');
      $('#input-campaign-excel-file').removeAttr('disabled');
      $('#btn-download-campaign-template').removeClass('disabled').removeAttr('disabled');
      $('#submit-spinner-save-contacts').hide();
      $('#submit-spinner-save-contacts-text').hide();
      $('.btn-back').removeClass('disabled');
      $('.spinner-border').hide();
    }
    else {
      $('#input-campaign-name').attr('disabled', 'disabled');
      $('#option-campaign-templates').attr('disabled', 'disabled');
      $('#input-campaign-excel-file').attr('disabled', 'disabled');
      $('#btn-download-campaign-template').addClass('disabled').prop('disabled', 'disabled');
      $('#submit-spinner-save-contacts').show();
      $('#submit-spinner-save-contacts-text').show();
      $('.btn-back').addClass('disabled');
      $('.spinner-border').show();
      setEnabledDataAction(false, false);
    }
  };

  function showProgressBarContainer(isDisplayed) {
    // console.log(isDisplayed + ' ' + $('#modal-progress').is(':visible'));
    if (!isDisplayed) {
      if ($('#modal-progress').is(':visible')) {
        $('#modal-progress').modal('hide');
      }
    }
    else {
      if (!$('#modal-progress').is(':visible')) {
        $('#modal-progress').modal('show');
      }
    }
  };

  function initWorker() {
    worker = new Worker('{{ url('js/demo_worker.js') }}');
    worker.onmessage = function(e) {
      // console.log(e.data);
      if (e.data.action === 'from_worker_run_read_excel_data') {
        readExcelData(e.data.dataRows);
      }
      else if (e.data.action === 'from_worker_clear_preview_table') {
        previewContactDataContainer.rows().remove().draw();
        updateProgressBar(0);
        showUploadProblemsReport(false, 0, 0);
        setInputFieldsVisibility(false);
        $('#btn-submit-create-campaign').addClass('disabled').attr('disabled', 'disabled');
      }
      else if (e.data.action === 'from_worker_show_interactive_elements') {
        setInputFieldsVisibility(true);
      }
    }
  };

  function showUploadProblemsReport(isDisplayed, rowsCount, dataCount) {
    $('#upload-problems-rows-count').text(rowsCount);
    $('#upload-problems-data-count').text(dataCount);
    isDisplayed ? $('#alert-upload-problems').show() : $('#alert-upload-problems').hide();
  };

  function setEnabledDataAction(isMergeEnabled, isReplaceEnabled) {
    var radioDataAction = $('.radio-campaign-edit-action');

    if (radioDataAction !== undefined) {
      $(radioDataAction).removeAttr('required');
      $(radioDataAction).prop('checked', false).prop('disabled', 'disabled');

      if (isMergeEnabled && isReplaceEnabled) {
        $('#radio-campaign-edit-action-merge').removeAttr('disabled').prop('required', 'required');
        $('#radio-campaign-edit-action-replace').removeAttr('disabled').prop('required', 'required');
      }
      else if (isMergeEnabled) {
        $('#radio-campaign-edit-action-merge').removeAttr('disabled').prop('required', 'required');
      }
      else if (isReplaceEnabled) {
        $('#radio-campaign-edit-action-replace').removeAttr('disabled').prop('required', 'required');
      }
    }
  };

  @if (isset($failed_contacts))
  // function populateFailedContacts() {
  //   var failedContactsText = '@php echo($failed_contacts); @endphp';
  //   var failedContactsObjects = JSON.parse(failedContactsText);

  //   $('#input-failed-contacts').val(failedContactsText);

  //   failedContactDataContainer.clear();
  //   failedContactDataContainer.rows.add(failedContactsObjects);
  //   failedContactDataContainer.draw();
  // }
  @endif
</script>
@endpush

@include('layouts.include_page_footer')
@include('layouts.include_js')
