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

  {{-- @php dd($templates); @endphp --}}
  @php
    $tempCamp = Session::get('campaign_failed_contacts');
    if ($tempCamp) $campaign = $tempCamp;
  @endphp
  {{-- @php dd($campaign); @endphp --}}

  <section class="section">
    <div class="row">
      <div class="col-lg-12">

        @if (!isset($failed_contacts))
        <div class="card">
          <form id="form-update-campaign" class="g-3 needs-validation" method="POST" action="{{ url('campaigns') }}" enctype="multipart/form-data" novalidate>

            <div class="card-body">
              <h5 class="card-title">
                Edit Campaign
              </h5>
            
              @if ($campaign[0]->camp_status != 0)
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <h4 class="alert-heading">Campaign Edit Error</h4>
                <p>This campaign is being or has been processed so can not be edited or changed.</p>
              </div>
              @endif

              <div id="alert-different-templates-container" class="alert alert-danger alert-dismissible fade show" role="alert">
                <h4 class="alert-heading">Campaign Creation Error</h4>
                <p>
                  <span class="fw-bold">Upload Contacts Data File</span> structure does not match the selected <span class="fw-bold">Campaign Template</span> structure.
                </p>
              </div>
              
              <!-- campaign name -->
              <div class="col-md-12 mb-3">
                <label class="form-label" for="edit-campaign-name">Campaign name (5-100 chars)</label>
                <input
                  type="text"
                  class="form-control"
                  id="edit-campaign-name"
                  name="campaign_name"
                  minlength="5" maxlength="100"
                  placeholder="Campaign name (5-100 chars)"
                  value="{{ $campaign[0]->camp_name or '' }}"
                  onkeyup="fillInputTemplateReference()"
                  required
                  @if ($campaign[0]->camp_status != 0) disabled @endif
                >
              </div>

              <!-- campaign templates -->
              <div class="col-md-12 mt-3 mb-3">
                <label class="form-label" for="option-campaign-templates">Campaign Templates</label>
                <select
                  class="form-select"
                  id="option-campaign-templates"
                  name="select_campaign_template"
                  aria-label="Campaign Templates"
                  required
                  @if ($campaign[0]->camp_status != 0) disabled @endif
                >
                  @php $headers = []; @endphp
                  @foreach ($templates AS $keyTemplate => $valTemplate)
                  <option value="{{ $valTemplate['id'] }}">{{ strtoupper($valTemplate['name']) }}</option>
                  @endforeach
                </select>
              </div>

              {{--
              <!-- campaign template reference -->
              <div class="col-md-12 mt-3" style=>
                <label class="form-label" for="input-template-reference">Campaign Template Reference</label>
                <input
                  type="text"
                  class="form-control readonly"
                  id="input-template-reference"
                  name="template_reference"
                  minlength="5" maxlength="100"
                  placeholder="Campaign template reference"
                  value="" readonly required
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
                <textarea
                  id="campaign-text-voice"
                  name="campaign_text_voice"
                  class="form-control"
                  rows="6"
                  placeholder="Campaign text message">{{ trim($campaign[0]->camp_text_voice) }}</textarea>
              </div>

              <!-- campaign voice gender -->
              <fieldset class="row mt-3 mb-3">
                <legend class="col-form-label col-lg-3 col-md-2 col-sm-2 pt-0">Voice Gender</legend>
                <div class="col-lg-4 col-md-3 col-sm-5">
                  <div class="form-check">
                    <input
                      class="form-check-input"
                      type="radio"
                      name="campaign_voice_gender" id="radio-campaign-voice-male-normal"
                      value="male_normal"
                      required @if ($campaign[0]->camp_voice_gender === 'male_normal') checked @endif
                    >
                    <label class="form-check-label" for="radio-campaign-voice-male-normal">Male (normal)</label>
                  </div>
                  <div class="form-check">
                    <input
                      class="form-check-input"
                      type="radio"
                      name="campaign_voice_gender" id="radio-campaign-voice-male-strong"
                      value="male_strong"
                      required @if ($campaign[0]->camp_voice_gender === 'male_strong') checked @endif
                    >
                    <label class="form-check-label" for="radio-campaign-voice-male-strong">Male (strong)</label>
                  </div>
                </div>

                <div class="col-lg-4 col-md-3 col-sm-5">
                  <div class="form-check">
                    <input
                      class="form-check-input"
                      type="radio"
                      name="campaign_voice_gender" id="radio-campaign-voice-female-normal"
                      value="female_normal"
                      required @if ($campaign[0]->camp_voice_gender === 'female_normal') checked @endif
                    >
                    <label class="form-check-label" for="radio-campaign-voice-female-normal">Female (normal)</label>
                  </div>
                  <div class="form-check">
                    <input
                      class="form-check-input"
                      type="radio"
                      name="campaign_voice_gender" id="radio-campaign-voice-female-strong"
                      value="female_strong"
                      required @if ($campaign[0]->camp_voice_gender === 'female_strong') checked @endif
                    >
                    <label class="form-check-label" for="radio-campaign-voice-female-strong">Female (strong)</label>
                  </div>
                </div>

                @if ($errors->has('campaign_voice_gender'))
                <div class="invalid-feedback mt-1 mb-1" style="display:block">
                    <strong>{{ $errors->first('campaign_voice_gender') }}</strong>
                </div>
                @endif
              </fieldset>

              <!-- campaign upload files -->
              <div class="col-md-12 mt-3 mb-3">
                <label class="form-label" for="edit-campaign-excel-file">Upload Contacts Data File</label>

                <div class="col-md-12 input-group">
                  <input class="form-control" type="file" id="edit-campaign-excel-file" accept=".xls, .xlsx" @if ($campaign[0]->camp_status != 0) disabled @endif>
                  <a
                    href="{{ url('campaigns/template') }}"
                    class="btn btn-success @if ($campaign[0]->camp_status != 0) disabled @endif"
                    type="button"
                    @if ($campaign[0]->camp_status != 0) disabled @endif
                  >
                    <i class="bi bi-download"></i>
                    &nbsp; Contacts Data Template
                  </a>
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
                    <label class="form-check-label" for="radio-campaign-edit-action-replace">Replace Contacts (replace old contacts with new contacts)</label>
                  </div>
                  <div id="radio-campaign-edit-error" class="text-danger fw-smaller">
                    Please choose the action for the new contacts data.
                  </div>
                </div>
              </fieldset>

              <hr>

              <!-- previous contacts -->
              <div id="row-table-previous-contacts" class="col-md-12 mt-4">
                <table class="table table-hover">
                  <thead>
                    <tr>
                        <th scope="col" class="align-top text-center">#</th>

                        @foreach ($campaign AS $key => $value)
                        <th scope="col" class="align-top">
                          {{ strtoupper($value->templ_header_name) }}
                          @php
                            $tempAdd = [];
                            if ($value->templ_is_mandatory) $tempAdd[] = 'mandatory';
                            if ($value->templ_is_unique) $tempAdd[] = 'unique';
                            if ($value->templ_is_voice) $tempAdd[] = 'voice-' . $value->templ_voice_position;
                            if (!empty($tempAdd)) echo '<br><small class="fw-lighter">(' . implode(', ', $tempAdd) . ')</small>';
                          @endphp
                        </th>
                        @endforeach
                    </tr>
                  </thead>
    
                  <tbody>
                    @if ($contacts->count() > 0)
                      @foreach ($contacts AS $keyData => $valueData)
                      @php $headerName = ''; @endphp
                      <tr>
                        <td class="text-end">{{ $row_number++ }}.</td>

                        @foreach($campaign AS $keyCampaign => $valCampaign)
                          @php $headerName = strtolower($valCampaign->templ_header_name); @endphp

                          @if ($valCampaign->templ_column_type === 'string') <td>{{ $valueData->$headerName }}</td>
                          @elseif ($valCampaign->templ_column_type === 'datetime') <td>{{ date('d/m/Y H:i:s', strtotime($valueData->$headerName)) }}</td>
                          @elseif ($valCampaign->templ_column_type === 'date') <td>{{ date('d/m/Y', strtotime($valueData->$headerName)) }}</td>
                          @elseif ($valCampaign->templ_column_type === 'time') <td>{{ date('H:i:s', strtotime($valueData->$headerName)) }}</td>
                          @elseif ($valCampaign->templ_column_type === 'handphone')
                          <td>{{ substr($valueData->$headerName, 0, 4) . 'xxxxxx' . substr( $valueData->$headerName, strlen($valueData->$headerName) - 3) }}</td>
                          @elseif ($valCampaign->templ_column_type === 'numeric') <td class="text-end">{{ number_format($valueData->$headerName, 0, ',', '.') }}</td>
                          @endif

                        @endforeach
                      </tr>
                      @endforeach
                    @else
                    <tr>
                      <td class="text-center" colspan="{{ $campaign->count() + 1 }}">No contacts data</td>
                    </tr>
                    @endif
                  </tbody>
                </table>

                {{ count($contacts) > 0 ? $contacts->links() : '' }}
              </div>

              <!-- uploaded contacts -->
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
                @if ($campaign[0]->camp_status === 0)
                {{ csrf_field() }}
                <input type="hidden" id="edit-new-campaign-name" name="campaign_name" value="">
                <input type="hidden" id="edit-campaign-rows" name="contact_rows" value="">
                <input type="hidden" id="edit-campaign-key" name="campaign" value="{{ $campaign[0]->camp_id }}">
                <input type="hidden" id="edit-campaign-action" name="action" value="">
                <input type="hidden" id="edit-template-reference" name="template_reference" value="{{ $campaign[0]->camp_reference_table }}">
                <input type="hidden" name="_method" value="PUT">
                {{-- <button type="submit" class="btn btn-outline-primary disabled" id="btn-submit-edit-campaign">Save</button>&nbsp; --}}
                <button type="submit" class="btn btn-primary">Save</button>&nbsp;
                <div id="submit-spinner-edit-contacts" class="spinner-border spinner-border-sm text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
                <span id="submit-spinner-edit-contacts-text">&nbsp;This may take a moment. Please wait...</span>
                @endif
              </div>
            </div>

          </form>
        </div>
        @endif

        @if (isset($failed_contacts))
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Failed Uploaded Contacts</h5>

            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <h4 class="alert-heading">Data Input Error</h4>
              <p>Some contact data can not be saved. Please check the table below to see them.</p>
            </div>

            <div id="row-table-failed-upload" class="col-md-12 mt-4">
              @php
                $headers = array();
                $tempAdd = [];
              @endphp
              <table id="table-failed-contacts-container" class="table table-hover">
                <thead>
                  <tr>
                    <th scope="col" class="align-top text-center">#</th>

                    @foreach ($campaign AS $key => $value)
                    <th scope="col" class="align-top">
                      {{ strtoupper($value->templ_name) }}
                      @php
                        $headers[] = array(
                          'col_name' => $value->templ_name,
                          'col_type' => $value->templ_column_type,
                        );
                        $tempAdd = [];
                        if ($value->templ_is_mandatory) $tempAdd[] = 'mandatory';
                        if ($value->templ_is_unique) $tempAdd[] = 'unique';
                        if ($value->templ_is_voice) $tempAdd[] = 'voice-' . $value->templ_voice_position;
                        if (!empty($tempAdd)) echo '<br><small class="fw-lighter">(' . implode(', ', $tempAdd) . ')</small>';
                      @endphp
                    </th>
                    @endforeach

                    <th scope="col" class="align-top">Reason</th>
                  </tr>
                </thead>

                <tbody>
                  @php
                    $failed_contacts = json_decode($failed_contacts);
                    $tempHeaderName = '';
                    $tempHeaderType = '';
                  @endphp
                  @foreach ($failed_contacts AS $keyFailed => $valFailed)
                  <tr>
                    <td class="text-end">{{ $keyFailed + 1 }}.</td>

                    @foreach($headers AS $keyHeader => $valHeader)
                      @php
                      $tempHeaderName = strtolower($valHeader['col_name']);
                      $tempHeaderType = $valHeader['col_type'];
                      @endphp

                      @if ($tempHeaderType === 'numeric')
                    <td><span class="text-end">{{ number_format($valFailed->$tempHeaderName, 0, ',', '.') }}</span></td>
                      @else
                    <td>{{ $valFailed->$tempHeaderName }}</td>
                      @endif
                    @endforeach

                    <td><span class="text-danger">{{ $valFailed->failed }}</span></td>
                  </tr>
                  @endforeach
                </tbody>
                
              </table>
            </div>

            
            <div class="col-md-12 mt-3">
              <form
                id="form-campaign-export"
                class="g-3 needs-validation"
                method="POST"
                target="_blank"
                action="{{ url('campaigns/export/failed') }}"
                enctype="multipart/form-data"
              >
                <a href="{{ url('campaigns') }}" class="btn btn-secondary">Close</a>
                <button type="submit" class="btn btn-success btn-export-as" id="btn-export-excel" data-export-as="excel">Export Excel</button>
                {{ csrf_field() }}
                <input type="hidden" name="failed_contacts_campaign" value="{{ $campaign[0]->camp_id }}">
                <input type="hidden" id="campaign-failed-contacts" name="campaign_failed_contacts" value="{{ json_encode($failed_contacts) }}">
                <input type="hidden" id="campaign-failed-contacts-headers" name="campaign_failed_contacts_headers" value="{{ json_encode($headers) }}">
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

var previousCampaignTemplId = @php echo isset($campaign) ? $campaign[0]->camp_templ_id : 0; @endphp;
var previousCampaignName = '@php echo isset($campaign) ? strtolower(trim($campaign[0]->camp_name)) : ''; @endphp';
var previousCampaignRefTable = '@php echo isset($campaign) ? strtolower(trim($campaign[0]->camp_reference_table)) : ''; @endphp';
var editAction = '';
var tempDatatableRows = [];


  $(document).ready(function() {
    initWorker();
    preparePreviewContactTable();
    prepareRadioButtons();
    showPreviewTable(false);

    @if (isset($failed_contacts))
    prepareFailedContactsTable();
    @endif

    $('#radio-campaign-edit-error').hide();
    $('#submit-spinner-edit-contacts').hide();
    $('#submit-spinner-edit-contacts-text').hide();
    $('#alert-different-templates-container').hide();

    $('#edit-campaign-rows').val('');
    $('#edit-campaign-action').val('');
    $('#edit-campaign-excel-file').val('');

    $('#edit-campaign-excel-file').on("change", function(e) {
      handleFileAsync(e);
    });

    $('#option-campaign-templates').on('change', function(e) {
      selectedTemplate = templates['t_' + $(this).val()];
      // console.log(selectedTemplate);

      $('#radio-campaign-edit-action-merge').prop('checked', false).prop('disabled', true);
      $('#radio-campaign-edit-action-replace').prop('checked', false).prop('disabled', true);
      preparePreviewContactTable();

      if ((selectedTemplate instanceof Object) && (selectedTemplate.id === previousCampaignTemplId)) {
        showOriginalTable(true);
        showPreviewTable(false);
      }
      else {
        showOriginalTable(false);
        showPreviewTable(true);
      }
      fillInputTemplateReference();

      var inputFileContainer = $('#edit-campaign-excel-file');
      inputFileContainer[0].files = null;
      $(inputFileContainer).val('');
      
      $('#alert-different-templates-container').hide();
      $('#edit-campaign-rows').val('');
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

      $('#option-campaign-templates').trigger('change');
    });

    // ---
    // --- update form submit
    // ---
    $('#form-update-campaign').submit(function() {
      if ($('#edit-campaign-name').val().trim().length < 5) {
        return false;
      }

      if (!$('#radio-campaign-edit-action-merge').is(':checked') && !$('#radio-campaign-edit-action-replace').is(':checked')) {
        $('#radio-campaign-edit-error').show();
        return false;
      }

      $('#edit-new-campaign-name').val($('#edit-campaign-name').val());

      worker.postMessage({
        action: 'hide_interactive_elements'
      });
    });

    $('.btn-back').click(function(e) {
      window.history.back();
    });

    $('#option-campaign-templates').val(@php echo isset($campaign) ? $campaign[0]->camp_templ_id : 0; @endphp);
    $('#option-campaign-templates').trigger('change');
  });

  function preparePreviewContactTable() {
    // console.log(selectedTemplate);

    previewContactDataContainer.destroy();
    $('#table-preview-contact-data-container').remove();

    var columns = [];
    var columnDefs = [];

    var tempPreviewTable = '<table id="table-preview-contact-data-container" class="table table-hover">';
    tempPreviewTable += '  <thead class="align-top">';
    tempPreviewTable += '    <tr id="row-preview-contact-data-container"></tr>';
    tempPreviewTable += '  </thead>';
    tempPreviewTable += '</table>';
    $('#row-table-excel-contacts').append(tempPreviewTable);
    
    if (selectedTemplate instanceof Object) {
      headers = selectedTemplate.headers || [];
      // console.log(headers);

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



  function prepareRadioButtons() {
    $('#radio-campaign-edit-action-merge').prop('checked', false).prop('disabled', true);
    $('#radio-campaign-edit-action-replace').prop('checked', false).prop('disabled', true);
    $('.radio-campaign-edit-action').click(function(e) {
      $('#edit-campaign-action').val($(this).val());
      changeEditActionButtons();
    });
  };

  function fillInputTemplateReference() {
    if (selectedTemplate instanceof Object) {
      var today = new Date();
      var campaignName = $('#edit-campaign-name').val();
      referenceName = 'c_' + campaignName.substr(0, 6) + '_t_' + selectedTemplate.name.substr(0, 6);
      referenceName = referenceName.replaceAll(replacePattern, '_').toLowerCase().substr(0, 100) + '_' + String(today.getTime()).substr(6);
      $('#edit-template-reference').val(referenceName);
    }
  };

  async function handleFileAsync(e) {
    $('#alert-different-templates-container').hide();
    $('#edit-campaign-rows').val('');

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
      // console.log(tempName);

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
          // console.log('key => ' + key + ', header => ' + val.header_name.replaceAll(replacePattern, '_').toLowerCase() + ', val => ' + v[key] + ', tempNewRow => ' + tempNewRow[val.header_name.replaceAll(replacePattern, '_').toLowerCase()]);
        });

        tempNewPhoneRows.push(tempNewRow);
        campaignRows.push(tempNewRow);
      });
      // console.log(campaignRows);
      
      $('#edit-campaign-rows').val(JSON.stringify(tempNewPhoneRows));
    }
    else {
      $('#alert-different-templates-container').show();
    }

    previewContactDataContainer.clear();
    previewContactDataContainer.rows.add(tempNewPhoneRows);
    previewContactDataContainer.draw();

    showOriginalTable(false);
    showPreviewTable(true);
    
    worker.postMessage({
      action: 'show_interactive_elements'
    });
  };

  function changeEditActionButtons() {
    editAction = $('#edit-campaign-action').val();

    if (editAction === 'merge') {
      $('#btn-submit-edit-campaign').removeClass('btn-outline-primary disabled').addClass('btn-primary');
    }
    else if (editAction === 'replace') {
      $('#btn-submit-edit-campaign').removeClass('btn-outline-primary disabled').addClass('btn-primary');
    }
  };

  function isInputFieldsDisplayed(isDisplayed) {
    if (isDisplayed) {
      $('#edit-campaign-name').removeAttr('disabled');
      $('#edit-campaign-excel-file').removeAttr('disabled');
      $('#submit-spinner-edit-contacts').hide();
      $('#submit-spinner-edit-contacts-text').hide();
      $('.btn-back').removeClass('disabled');

      if ($('#edit-campaign-rows').val() !== '') {
        $('#btn-edit-contact-action-merge').removeClass('disabled');
        $('#btn-edit-contact-action-replace').removeClass('disabled');
        $('.radio-campaign-edit-action').removeAttr('disabled');
      }

      // if (editAction !== '') {
      //   $('#btn-submit-edit-campaign').removeClass('disabled');
      // }
    }
    else {
      $('#edit-campaign-name').attr('disabled', 'disabled');
      $('#edit-campaign-excel-file').attr('disabled', 'disabled');
      $('#btn-submit-edit-campaign').addClass('disabled');
      $('#submit-spinner-edit-contacts').show();
      $('#submit-spinner-edit-contacts-text').show();
      $('.btn-back').addClass('disabled');

      $('#btn-edit-contact-action-merge').addClass('disabled');
      $('#btn-edit-contact-action-replace').addClass('disabled');
      $('.radio-campaign-edit-action').attr('disabled', 'disabled');
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
      }
    }
  };

  function showOriginalTable(isShown) {
    if (isShown) $('#row-table-previous-contacts').show();
    else $('#row-table-previous-contacts').hide();
  };

  function showPreviewTable(isShown) {
    if (isShown) $('#row-table-excel-contacts').show();
    else $('#row-table-excel-contacts').hide();
  };
  
  @if (isset($failed_contacts))
  function prepareFailedContactsTable() {
    failedContactDataContainer = $('#table-failed-contacts-container').DataTable({
        processing: true,
        lengthMenu: [5, 10, 15, 20, 50, 100],
        pageLength: 15
      });
  };
  @endif

</script>
@endpush

@include('layouts.include_page_footer')
@include('layouts.include_js')
