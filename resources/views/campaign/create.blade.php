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

              {{--
              <div id="alert-different-templates-container" class="alert alert-danger alert-dismissible fade show" role="alert">
                <h4 class="alert-heading">Campaign Creation Error</h4>
                <p><span class="fw-bold">Upload Contacts Data File</span> structure does not match the selected <span class="fw-bold">Campaign Template</span> structure.</p>
              </div>
              --}}

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
                <label class="form-label" for="campaign-text-voice">Preview Voice Text</label>
                <textarea
                  id="campaign-text-voice" name="campaign_text_voice"
                  class="form-control disabled"
                  rows="6"
                  placeholder="Campaign text message"
                  readonly disabled
                ></textarea>
              </div>

              {{--
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
              --}}

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

              <!-- campaign upload/download template -->
              <div class="col-md-12 mt-3">
                <label class="form-label" for="input-campaign-excel-file">Upload Contacts Data File</label>

                <div class="col-md-12 input-group">
                  <input class="form-control" type="file" id="input-campaign-excel-file" accept=".xls, .xlsx" required>
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

              <!-- progress animation -->
              <div class="col-md-12 mt-3 mb-4">
                <label id="progress-bar-title" class="form-label disabled">Progress</label>&nbsp;
                <div class="spinner-border spinner-border-sm text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>

                {{----}}
                <div class="progress" style="height:50px;">
                  <div
                      id="progress-bar-value"
                      class="progress-bar progress-bar-striped active"
                      role="progressbar"
                      aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"
                      style="width:0%"
                  >0%</div>
                </div>
                {{----}}
              </div>

              <hr />

              <!-- data check alert -->
              <div id="alert-upload-problems" class="alert alert-danger alert-dismissible fade show" role="alert">
                <h4 class="alert-heading">Data Check Results</h4>
                <p>
                  <span class="text-danger">
                    <span class="fw-bold"><span id="upload-problems-rows-count"></span> row(s)</span> with
                    <span class="fw-bold"><span id="upload-problems-data-count"></span> invalid</span> data.
                  </span>
                </p>
              </div>

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
<script type="text/javascript">
var previewContactDataContainer = $('#table-preview-contact-data-container').DataTable();
var templates = JSON.parse('@php echo !empty($templates) ? json_encode($templates) : '[]' @endphp');
var selectedTemplate = '';
var selectedHeaders = '';
var replacePattern = /\W+/ig;
var worker = '';

var editAction = 'replace';
var tempAllContacts = [];
var tempValidContacts = [];
var invalidRowsCount = 0;
var invalidDataCount = 0;

  $(document).ready(function() {
    initWorker();
    preparePreviewContactTable(0);
    prepareSubmitCampaign();
    showUploadProblemsReport(false, 0, 0);
    showTemplateError(false);
    setInputFieldsDisplayed(true);

    $('#input-campaign-excel-file').val('');
    $('#input-campaign-rows').val('');

    $('#input-campaign-excel-file').on("change", function(e) {
      updateProgressBar(0);
      setInputFieldsDisplayed(false);
      showTemplateError(false);
      showUploadProblemsReport(false, 0, 0);

      worker.postMessage({
        action: 'change_element_text',
        element: '#progress-bar-title',
        text: 'Reading file ...'
      });

      setTimeout(() => {
        handleFileAsync(e);
      }, 100);
    });

    $('#option-campaign-templates').on('change', function(e) {
      selectedTemplate = templates['t_' + $(this).val()];
      selectedHeaders = selectedTemplate.headers;

      var inputFileContainer = $('#input-campaign-excel-file');
      inputFileContainer[0].files = null;
      $(inputFileContainer).val('');
      
      $('#campaign-text-voice').val(selectedTemplate.voice_text);
      $('#input-campaign-rows').val('');

      updateProgressBar(0);
      showUploadProblemsReport(false, 0, 0);
      showTemplateError(false);
      preparePreviewContactTable();
    });

    $('#btn-download-campaign-template').click(function(e) {
      e.preventDefault();
      window.location.href = '{{ url('templates/download') }}/' + $('#option-campaign-templates').val();
    });
    
    $('.btn-back').click(function(e) {
      history.back();
    });

    $('#option-campaign-templates').trigger('change');
  });

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
              className: 'dt-body-right',
              render: function(data, type, row, meta) {
                return data.toLocaleString('id-ID');
              }
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
  };

  function prepareSubmitCampaign() {
    $('#form-create-campaign').submit(function() {
      var tempPostedContacts = $('#input-campaign-rows').val();

      if (tempPostedContacts.trim() !== '') {
        var postedNewContacts = JSON.parse(tempPostedContacts);
        var editAction = 'replace';

        if (($('#input-campaign-name').val().trim().length < 5) || ($('#input-campaign-rows').val().trim() === '')) {
          return false;
        }

        var isConfirmSubmit = true;
        if (postedNewContacts.length > 10000) {
          isConfirmSubmit = confirm('You are going to upload more than 10.000 data. This might slow your system down.\r\n\r\nAre you sure?');
        }
        
        if (!isConfirmSubmit)
          return false;

        worker.postMessage({
          action: 'change_element_text',
          element: '#progress-bar-title',
          text: 'Uploading data, please wait...'
        });

        worker.postMessage({
          action: 'hide_interactive_elements'
        });

        isConfirmSubmit = undefined;
        editAction = undefined;
        postedNewContacts = undefined;
        tempPostedContacts = undefined;
      }
      else {
        showTemplateError(true);
        tempPostedContacts = undefined;
        return false;
      }
    });
  };

  async function handleFileAsync(e) {
    tempAllContacts = [];
    tempValidContacts = [];
    invalidRowsCount = 0;
    invalidDataCount = 0;
    previewContactDataContainer.rows().remove().draw();
    
    $('#input-campaign-rows').val('');

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

      // var tempUniques = [];
      var tempHeaders = [];
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
      var isTemplateOk = true;
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
      // console.log(isTemplateOk);

      if (isTemplateOk) {
        worker.postMessage({
          action: 'change_element_text',
          element: '#progress-bar-title',
          text: 'Checking data ...'
        });
        
        worker.postMessage({
          action: 'run_data_check',
          editAction: 'replace',
          dataRows: dataRows,
          tempHeaders: tempHeaders
        });
      }
      else {
        setInputFieldsDisplayed(true);
        showTemplateError(true);

        worker.postMessage({
          action: 'change_element_text',
          element: '#progress-bar-title',
          text: 'Progress'
        });
      }
    }

    file = undefined;
  };

  function setInputFieldsDisplayed(isDisplayed) {
    if (isDisplayed) {
      $('#input-campaign-name').removeAttr('disabled');
      $('#input-campaign-excel-file').removeAttr('disabled');
      $('#btn-submit-create-campaign').removeClass('disabled');
      $('#btn-download-campaign-template').removeClass('disabled');
      $('#submit-spinner-save-contacts').hide();
      $('#submit-spinner-save-contacts-text').hide();
      $('#option-campaign-templates').removeAttr('disabled');
      $('.btn-back').removeClass('disabled');
      $('.spinner-border').hide();
      
    }
    else {
      $('#input-campaign-name').attr('disabled', 'disabled');
      $('#input-campaign-excel-file').attr('disabled', 'disabled');
      $('#btn-submit-create-campaign').addClass('disabled');
      $('#btn-download-campaign-template').addClass('disabled');
      $('#submit-spinner-save-contacts').show();
      $('#submit-spinner-save-contacts-text').show();
      $('#option-campaign-templates').attr('disabled', 'disabled');
      $('.btn-back').addClass('disabled');
      $('.spinner-border').show();
    }
  };

  function showUploadProblemsReport(isDisplayed, rowsCount, dataCount) {
    $('#upload-problems-rows-count').text(rowsCount);
    $('#upload-problems-data-count').text(dataCount);
    isDisplayed ? $('#alert-upload-problems').show() : $('#alert-upload-problems').hide();
  };

  function showTemplateError(isDisplayed) {
    if (isDisplayed) {
      // $('#alert-different-templates-container').show();
      $('#input-campaign-excel-file').addClass('is-invalid');
      $('#different-templates-message').show();
    }
    else {
      // $('#alert-different-templates-container').hide();
      $('#input-campaign-excel-file').removeClass('is-invalid');
      $('#different-templates-message').hide();
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
      if (e.data.action === 'from_worker_hide_interactive_elements') {
        setInputFieldsDisplayed(false);
      }
      else if (e.data.action === 'from_worker_show_interactive_elements') {
        setInputFieldsDisplayed(true);
      }
      else if (e.data.action === 'from_worker_update_progress_count') {
        // setInputFieldsDisplayed(true);
        // updateProgressCount(e.data.campaignRows, e.data.dataRowsCount);

        var percentage = ((e.data.campaignRows.length / e.data.dataRowsCount) * 100).toFixed(2);
        $('#progress-bar-value').text(percentage + '%');
        $('#progress-bar-value').css('width', percentage + '%');
      }
      else if (e.data.action === 'from_worker_run_data_check') {
        tempAllContacts.push(e.data.row);

        if (e.data.row.errors !== null) {
          invalidRowsCount++;
          invalidDataCount += e.data.dataErrorsCount;
        }
        else {
          tempValidContacts.push(e.data.row);
        }
        
        var percentage = ((tempAllContacts.length / e.data.total_count) * 100).toFixed(2);
        updateProgressBar(percentage);
        
        if (tempAllContacts.length === e.data.total_count) {
          setTimeout((tempValidContacts) => {
            $('#input-campaign-rows').val(JSON.stringify(tempValidContacts));

            if (invalidRowsCount > 0) {
              showUploadProblemsReport(true, invalidRowsCount, invalidDataCount);
            }

            updateTableContents(JSON.parse(JSON.stringify(tempAllContacts)));
            setInputFieldsDisplayed(true);

            worker.postMessage({
              action: 'change_element_text',
              element: '#progress-bar-title',
              text: 'Progress'
            });

            tempValidContacts = undefined;
          }, 500, tempValidContacts);
        }
      }
      else if (e.data.action === 'from_worker_change_element_text') {
        $(e.data.element).text(e.data.text);
      }
    }
  };

  function updateTableContents(newData) {
    // console.log(newData.length);
    previewContactDataContainer.rows.add(newData).draw();
  };

  function updateProgressBar(percentage) {
    // console.log('percentage: ' + percentage);
    $('#upload-progress').val(percentage + '%');
    $('#progress-bar-value').text(percentage + '%');
    $('#progress-bar-value').css({ 'width': percentage + '%' });
  };
</script>
@endpush

@include('layouts.include_page_footer')
@include('layouts.include_js')
