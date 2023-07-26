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

              <!-- progress animation -->
              <div class="col-md-12 mt-3 mb-3">
                <label id="progress-bar-title" class="form-label disabled">Progress</label>&nbsp;
                <div class="spinner-border spinner-border-sm text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
                
                <div class="progress" style="height:50px;">
                  <div
                      id="progress-bar-value"
                      class="progress-bar progress-bar-striped active"
                      role="progressbar"
                      aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"
                      style="width:0%"
                  >0%</div>
                </div>
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

var previousCampaigns = JSON.parse('@php echo isset($campaign_headers) ? json_encode($campaign_headers) : '{}'; @endphp');
var previousContacts = JSON.parse('@php echo $contacts->count() > 0 ? json_encode($contacts) : '[]' @endphp');
var tempNewValidMergeContacts = { validContacts:[], invalidContacts:[], rowErrorsCount:0, dataErrorsCount:0 };
var tempNewValidReplaceContacts = { validContacts:[], invalidContacts:[], rowErrorsCount:0, dataErrorsCount:0 };
var tempValidContacts = [];
var tempInvalidContacts = [];
var invalidDataCount = 0;
var dataRows = null;
var tempHeaders = [];

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
      showTemplateError(false);
      showUploadProblemsReport(false, 0, 0);
      setInputFieldsDisplayed(false);

      worker.postMessage({
        action: 'change_element_text',
        element: '#progress-bar-title',
        text: 'Reading file ...'
      });

      setTimeout(() => {
        handleFileAsync(e);
      }, 1000);
    });

    $('#option-campaign-templates').on('change', function(e) {
      selectedTemplate = templates['t_' + $(this).val()];
      selectedHeaders = selectedTemplate.headers;

      var inputFileContainer = $('#input-campaign-excel-file');
      inputFileContainer[0].files = null;
      $(inputFileContainer).val('');
      
      $('#campaign-text-voice').val(selectedTemplate.voice_text);
      $('#input-campaign-rows').val('').trigger('change');

      updateProgressBar(0);
      setEnabledDataAction(false, false);
      showUploadProblemsReport(false, 0, 0);
      showTemplateError(false);
      preparePreviewContactTable();

      if (selectedTemplate.id === previousCampaigns[0].camp_templ_id) {
        previewContactDataContainer.rows.add(previousContacts).draw();
      }
    });

    $('.radio-campaign-edit-action').on('click', function(e) {
      var editAction = $(this).val().toLowerCase();

      preparePreviewContactTable();
      showUploadProblemsReport(false, 0, 0);
      setInputFieldsDisplayed(false);

      worker.postMessage({
        action: 'change_element_text',
        element: '#progress-bar-title',
        text: 'Checking data ...'
      });

      setTimeout((editAction, invalidContactsCount, invalidDataCount) => {
        if (editAction === 'merge') {
          if (tempNewValidMergeContacts.validContacts.length === 0 && tempNewValidMergeContacts.invalidContacts.length === 0) {
            worker.postMessage({
              action: 'run_data_check',
              editAction: 'merge',
              dataRows: dataRows,
              tempHeaders: tempHeaders,
              previousContacts: previousContacts
            });
          }
          else {
            var tempAllContacts = tempNewValidMergeContacts.invalidContacts.concat(tempNewValidMergeContacts.validContacts);
            var invalidContactsCount = tempNewValidMergeContacts.rowErrorsCount;
            var invalidDataCount = tempNewValidMergeContacts.dataErrorsCount;

            $('#input-campaign-rows').val(JSON.stringify(tempNewValidMergeContacts.validContacts));
            previewContactDataContainer.rows.add(tempAllContacts).draw();

            setInputFieldsDisplayed(true);
            
            if (invalidContactsCount > 0) showUploadProblemsReport(true, invalidContactsCount, invalidDataCount);

            worker.postMessage({
              action: 'change_element_text',
              element: '#progress-bar-title',
              text: 'Progress'
            });
          }
        }
        else if (editAction === 'replace') {
          if (tempNewValidReplaceContacts.validContacts.length === 0 && tempNewValidReplaceContacts.invalidContacts.length === 0) {
            worker.postMessage({
              action: 'run_data_check',
              editAction: 'replace',
              dataRows: dataRows,
              tempHeaders: tempHeaders
            });
          }
          else {
            var tempAllContacts = tempNewValidReplaceContacts.invalidContacts.concat(tempNewValidReplaceContacts.validContacts);
            var invalidContactsCount = tempNewValidReplaceContacts.rowErrorsCount;
            var invalidDataCount = tempNewValidReplaceContacts.dataErrorsCount;

            $('#input-campaign-rows').val(JSON.stringify(tempNewValidReplaceContacts.validContacts));
            previewContactDataContainer.rows.add(tempAllContacts).draw();

            setInputFieldsDisplayed(true);

            if (invalidContactsCount > 0) showUploadProblemsReport(true, invalidContactsCount, invalidDataCount);

            worker.postMessage({
              action: 'change_element_text',
              element: '#progress-bar-title',
              text: 'Progress'
            });
          }
        }
      }, 500, editAction);
    });

    $('#btn-download-campaign-template').click(function(e) {
      e.preventDefault();
      window.location.href = '{{ url('templates/download') }}/' + $('#option-campaign-templates').val();
    });
    
    $('.btn-back').click(function(e) {
      history.back();
    });

    $('#option-campaign-templates').val(previousCampaigns[0].camp_templ_id).trigger('change');
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
          if (tempInfo.length > 0) { columns.title += '<br>(' + tempInfo.join(', ') + ')'; }

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
    
    columnDefs = undefined;
    columns = undefined;
  };

  function prepareSubmitCampaign() {
    $('#form-create-campaign').submit(function() {
      var tempPostedContacts = $('#input-campaign-rows').val();

      if (tempPostedContacts.trim() !== '') {
        var postedNewContacts = JSON.parse(tempPostedContacts);
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
      }
      else {
        showTemplateError(true);
        tempPostedContacts = undefined;
        return false;
      }
    });
  };

  async function handleFileAsync(e) {
    tempValidContacts = [];
    tempInvalidContacts = [];
    invalidDataCount = 0;

    tempNewValidMergeContacts.validContacts = [];
    tempNewValidMergeContacts.invalidContacts = [];
    tempNewValidMergeContacts.rowErrorsCount = 0;
    tempNewValidMergeContacts.dataErrorsCount = 0;

    tempNewValidReplaceContacts.validContacts = [];
    tempNewValidReplaceContacts.invalidContacts = [];
    tempNewValidReplaceContacts.rowErrorsCount = 0;
    tempNewValidReplaceContacts.dataErrorsCount = 0;

    preparePreviewContactTable();

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

      tempHeaders = [];
      selectedHeaders.map((val, key) => {
        tempHeaders.push({
          name: val.header_name.toLowerCase().replaceAll(replacePattern, ''),
          type: val.column_type,
          is_mandatory: val.is_mandatory,
          is_unique: val.is_unique
        });
      });

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
      
      if (isTemplateOk) {
        if (typeof previousCampaigns !== 'undefined') {
          if (previousCampaigns[0].camp_templ_id === selectedTemplate.id) {
            setEnabledDataAction(true, true);
            $('#radio-campaign-edit-action-merge').trigger('click');
          }
          else {
            setEnabledDataAction(false, true);
            $('#radio-campaign-edit-action-replace').trigger('click');
          }
        }
        else {
          // runReadExcelData(dataRows);
        }
      }
      else {
        showTemplateError(true);
        setInputFieldsDisplayed(true);

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
      $('#input-campaign-name').removeClass('disabled').removeAttr('disabled');
      $('#option-campaign-templates').removeClass('disabled').removeAttr('disabled');
      $('#input-campaign-excel-file').removeClass('disabled').removeAttr('disabled');
      $('.radio-campaign-edit-action').removeClass('disabled').removeAttr('disabled');

      $('#btn-download-campaign-template').removeClass('disabled').removeAttr('disabled');
      $('#btn-submit-create-campaign').removeClass('disabled').removeAttr('disabled');
      $('.btn-back').removeClass('disabled').removeAttr('disabled');

      $('#submit-spinner-save-contacts').hide();
      $('#submit-spinner-save-contacts-text').hide();
      $('.spinner-border').hide();
    }
    else {
      $('#input-campaign-name').addClass('disabled').attr('disabled', 'disabled');
      $('#option-campaign-templates').addClass('disabled').attr('disabled', 'disabled');
      $('#input-campaign-excel-file').addClass('disabled').attr('disabled', 'disabled');
      $('.radio-campaign-edit-action').addClass('disabled').attr('disabled', 'disabled');

      $('#btn-download-campaign-template').addClass('disabled').prop('disabled', 'disabled');
      $('#btn-submit-create-campaign').addClass('disabled').prop('disabled', 'disabled');
      $('.btn-back').addClass('disabled').prop('disabled', 'disabled');

      $('#submit-spinner-save-contacts').show();
      $('#submit-spinner-save-contacts-text').show();
      $('.spinner-border').show();
    }
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
        setInputFieldsVisibility(true);
      }
      else if (e.data.action === 'from_worker_change_element_text') {
        $(e.data.element).text(e.data.text);
      }
      else if (e.data.action === 'from_worker_run_data_check') {
        if (e.data.row.errors !== null) {
          invalidDataCount += e.data.dataErrorsCount;
          tempInvalidContacts.push(e.data.row);
        }
        else {
          tempValidContacts.push(e.data.row);
        }
        
        var percentage = ((e.data.sequence / e.data.total_count) * 100).toFixed(2);
        updateProgressBar(percentage);
        
        if (e.data.sequence === e.data.total_count) {
          setTimeout(() => {
            if (e.data.editAction === 'merge') {
              tempNewValidMergeContacts.validContacts = JSON.parse(JSON.stringify(tempValidContacts));
              tempNewValidMergeContacts.invalidContacts = JSON.parse(JSON.stringify(tempInvalidContacts));
              tempNewValidMergeContacts.rowErrorsCount = tempInvalidContacts.length;
              tempNewValidMergeContacts.dataErrorsCount = invalidDataCount;
            }
            else if (e.data.editAction === 'replace') {
              tempNewValidReplaceContacts.validContacts = JSON.parse(JSON.stringify(tempValidContacts));
              tempNewValidReplaceContacts.invalidContacts = JSON.parse(JSON.stringify(tempInvalidContacts));
              tempNewValidReplaceContacts.rowErrorsCount = tempInvalidContacts.length;
              tempNewValidReplaceContacts.dataErrorsCount = invalidDataCount;
            }
            
            $('#input-campaign-rows').val(JSON.stringify(tempValidContacts));
            
            if (tempInvalidContacts.length > 0) {
              showUploadProblemsReport(true, tempInvalidContacts.length, invalidDataCount);
            }

            updateTableContents(tempInvalidContacts.concat(tempValidContacts));
            setInputFieldsDisplayed(true);

            worker.postMessage({
              action: 'change_element_text',
              element: '#progress-bar-title',
              text: 'Progress'
            });

            tempValidContacts = [];
            tempInvalidContacts = [];
          }, 500);
        }
      }
    }
  };

  function updateTableContents(newData) {
    console.log(newData.length);
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
