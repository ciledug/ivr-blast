@include('layouts.include_page_header')
@include('layouts.include_sidebar')

<link rel="stylesheet" type="text/css" href="{{ asset('css/datepicker.min.css') }}">

{{-- @php dd($campaigns); @endphp --}}
{{-- @php dd($startdate); @endphp --}}

<main id="main" class="main">
  <div class="pagetitle" style="display: flex;align-items: center;justify-content: space-between;">
    <h1>Call Logs</h1>
    <div class="toolbar-export">
      <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#modalExport">
        Export
      </button>
    </div>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-12">

        <div class="card">
          <div class="card-body">
            <form action="{{ route('calllogs.list') }}" method="GET" id="form-filter">
              {{ csrf_field() }}
              <div class="row mt-4">
                <div class="col-md-6">Call Dial Date</div>
                <div class="col-md-6 text-end"><label for="select-campaign-list">Select Campaign</label></div>
              </div>
              
              <div class="row mt-2 mb-4">
                <!-- call-logs date -->
                <div class="col-md-6">
                  <div class="row form-group">
                    <div class="col-md-4">
                      <input type="text" name="startdate" class="form-control" placeholder="Start Date" readonly id="startdate" value="{{ $startdate ? $startdate : null }}">
                    </div>
                    <div class="col-md-4">
                      <input type="text" name="enddate" class="form-control" placeholder="End Date" readonly id="enddate" value="{{ $enddate ? $enddate : null }}">
                    </div>

                    @if($startdate)
                    <div class="col-md-4">
                      {{-- <a href="{{ route('calllogs') }}" class="btn btn-outline-primary">Clear Date</a> --}}
                      <a href="{{ route('calllogs.list', ['campaign' => $selectedCampaign[0]->camp_id]) }}" class="btn btn-outline-primary">Clear Date</a>
                    </div>
                    @endif
                  </div>
                </div>

                <!-- campaigns drop-down -->
                <div class="col-md-4 offset-md-2">
                  <select class="form-select" id="select-campaign-list" aria-label="Campaign" name="campaign">
                    <option value="" selected></option>
                    @foreach ($campaigns AS $keyCampaign => $valueCampaign)
                      @if ($valueCampaign->id === (count($selectedCampaign) > 0 ? $selectedCampaign[0]->camp_id : ''))
                      <option value="{{ $valueCampaign->id }}" selected>{{ $valueCampaign->name }}</option>
                      @else
                      <option value="{{ $valueCampaign->id }}">{{ $valueCampaign->name }}</option>
                      @endif
                    @endforeach
                    </select>
                </div>
              </div>
            </form>

            <table id="table-campaign-list-container" class="table table-hover">
              <thead class="align-top">
                <tr>
                  @if (count($selectedCampaign) > 0)
                    <th scope="col" class="align-top text-center">#</th>
                    
                    @foreach($selectedCampaign AS $keyHeader => $valHeader)
                    <th scope="col" class="align-top">{{ strtoupper($valHeader->tmpl_header_name) }}</th>
                    @endforeach
                      
                    <th scope="col" class="align-top">CALL DIAL</th>
                    <th scope="col" class="align-top">CALL CONNECT</th>
                    <th scope="col" class="align-top">CALL DISCONNECT</th>
                    <th scope="col" class="align-top">CALL DURATION</th>
                    <th scope="col" class="align-top">CALL RESPONSE</th>
                    <th scope="col" class="align-top">RECORDING</th>
                  @else
                  <th scope="col" class="align-top"></th>
                  @endif
                </tr>
              </thead>

              <tbody>
                @if(!is_array($calllogs) && $calllogs->count() > 0)
                  @foreach ($calllogs AS $keyCallLog => $valueCallLog)
                  <tr>
                    <td class="text-end">{{ $row_number++ }}.</td>

                    @foreach($selectedCampaign AS $keyHeader => $valHeader)
                      @php $columnName = strtolower($valHeader->tmpl_header_name) @endphp

                      @if ($valHeader->templ_is_mandatory)
                        @if ($valHeader->tmpl_col_type === 'string')
                        <td>{{ $valueCallLog->$columnName }}</td>
                        @elseif ($valHeader->tmpl_col_type === 'handphone')
                        <td>{{ substr($valueCallLog->$columnName, 0, 4) . 'xxxxxx' . substr($valueCallLog->$columnName, strlen($valueCallLog->$columnName) - 3) }}</td>
                        @elseif ($valHeader->tmpl_col_type === 'numeric')
                        <td class="text-end">{{ number_format($valueCallLog->$columnName, 0, '.', '.') }}</td>
                        @elseif ($valHeader->tmpl_col_type === 'datetime')
                        <td>{{ date('d/m/Y H:i:s', strtotime($valueCallLog->$columnName)) }}</td>
                        @elseif ($valHeader->tmpl_col_type === 'date')
                        <td>{{ date('d/m/Y', strtotime($valueCallLog->$columnName)) }}</td>
                        @elseif ($valHeader->tmpl_col_type === 'time')
                        <td>{{ date('H:i:s', strtotime($valueCallLog->$columnName)) }}</td>
                        @endif
                      @endif
                    @endforeach

                    <td>{{ $valueCallLog->cl_call_dial ? date('d/m/Y', strtotime($valueCallLog->cl_call_dial)) : '' }}</td>
                    <td>{{ $valueCallLog->cl_call_connect ? date('H:i:s', strtotime($valueCallLog->cl_call_connect)) : '' }}</td>
                    <td>{{ $valueCallLog->cl_call_disconnect ? date('H:i:s', strtotime($valueCallLog->cl_call_disconnect)) : '' }}</td>
                    <td class="text-end">{{ $valueCallLog->cl_call_duration > 0 ? App\Helpers\Helpers::secondsToHms($valueCallLog->cl_call_duration) : '' }}</td>
                    <td>{{ strtoupper($valueCallLog->cl_call_response) }}</td>

                    <td style="padding:4px 0;" class="text-center" >
                      @if (!empty($valueCallLog->cl_call_duration))
                      <button type="button" class="btn btn-success btn-sm btn-play-recording" data-bs-toggle="modal" data-bs-target="#modal-play-recording" data-value="{{ $valueCallLog->cl_call_recording }}">
                        <i class="bi bi-play-fill"></i> 
                      </button>
                      @endif
                    </td>
                  </tr>
                  @endforeach
                @else
                  <tr>
                    <td colspan="{{ count($selectedCampaign) + 7 }}" class="text-center">No call logs data</td>
                  </tr>
                @endif
              </tbody>
            </table>

            @if (!is_array($calllogs))
              {{ $calllogs->links() }}
            @endif

            {{--
            <div class="col-md-12 mt-4">
              <form id="form-campaign-export" class="g-3 needs-validation" method="POST" target="_blank" action="{{ route('calllogs.export') }}" enctype="multipart/form-data">
                <button type="button" class="btn btn-success btn-export-as" id="btn-export-excel" data-export-as="excel">Export Excel</button>
                <button type="button" class="btn btn-danger btn-export-as" id="btn-export-pdf" data-export-as="pdf">Export PDF</button>
                <input type="hidden" id="campaign-export-type" name="export_type" value="">
                <input type="hidden" id="campaign-export" name="campaign" value="{{ $selectedCampaign or '' }}">
                <input type="hidden" name="startdate" value="{{ $startdate }}">
                <input type="hidden" name="enddate" value="{{ $enddate }}">
                {{ csrf_field() }}
              </form>
            </div>
            --}}

          </div>
        </div>

      </div>
    </div>
  </section>

  {{-- MODAL RECORDING --}}
  <div class="modal fade" id="modal-play-recording" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" style="display:none;" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Call Recording</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <audio id="audio-player-container" src="" type="" controls autoplay style="width:100%;border-radius: 4px">
            Your browser does not support the audio element.
          </audio> 
        </div>

        <div class="modal-footer">
          <button id="btn-close-play-recording" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button id="btn-download-recording" type="button" class="btn btn-primary" data-bs-dismiss="modal">Download</button>
        </div>
      </div>
    </div>
  </div>

  {{-- Modal Export --}}
  <div class="modal fade" data-bs-backdrop="static" id="modalExport" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5 fw-bold" id="exampleModalLabel">Export Call Logs</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <form action="{{ route('calllogs.export') }}" method="POST" id="form-export" class="needs-validation" novalidate="" target="_blank">
              {{ csrf_field() }}
              <div class="container-fluid">
                <div class="row mb-3">
                  <label for="inputText" class="col-sm-4 col-form-label">Date Range *</label>
                  <div class="col-sm-4">
                    <input type="text" name="export_startdate" class="form-control" placeholder="Start Date"  id="export-startdate" autocomplete="off" required="required" tabindex="-1">
                  </div>
                  <div class="col-sm-4">
                    <input type="text" name="export_enddate" class="form-control" placeholder="End Date"  id="export-enddate" autocomplete="off" required="requireds">
                  </div>
                  <div class="col-sm-8 offset-4">
                    <div class="invalid-feedback date-range-validation">The date range cannot exceed 31 days</div>
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="inputText" class="col-sm-4 col-form-label">Campaign <span class="text-black-50">(Optional)</span></label>
                  <div class="col-sm-8">
                    <select class="form-select" name="export_campaign" autocomplete="off">
                      <option value="" selected>-- Select Campaign --</option>
                      @foreach ($campaigns AS $keyCampaign => $valueCampaign)
                        <option value="{{ $valueCampaign->id }}">{{ $valueCampaign->name }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="row mb-3 checkbox-disabled-date-range visually-hidden">
                  <div class="col-sm-8 offset-sm-4">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" value="" id="disableDateRange" autocomplete="off">
                      <label class="form-check-label" for="disableDateRange" style="font-size: 12px">All logs from this campaign without date range</label>
                    </div>
                  </div>
                </div>
              </div>
              <button type="submit" class="visually-hidden"></button>
              <button type="reset" class="visually-hidden"></button>
            </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" id="btn-submit-export">Export</button>
        </div>
      </div>
    </div>
  </div>

</main>

@push('javascript')
<script src="{{ asset('js/datepicker.min.js') }}"></script>
<script type="text/javascript">
  $(document).ready(function() {
    preparePlayRecordingButtons();
    prepareChangeCampaignDropDown();
    
    $('#btn-close-play-recording').click(function(e) {
      const audio = document.getElementById('audio-player-container');
      audio.pause();
      audio.currentTime = 0;
    });

    $('#startdate').datepicker({
        format: "dd/mm/yyyy",
        autoclose: true,
        minView:2,
    }).on('changeDate', function(evt){
      $('#enddate').datepicker('setStartDate',evt.date).focus().val('');
    });
    $('#enddate').datepicker({
        format: "dd/mm/yyyy",
        autoclose: true,
        minView:2,
    }).on('changeDate', function(evt){
      if($('#startdate').val().length > 0){
        $('#modal-spinner').modal('show');
        document.getElementById('form-filter').submit();
      }else{
        alert('please fill startdate and enddate');
      }
    });


    document.getElementById('select-campaign-list').addEventListener('change', (e) => {
      let value = e.target.value;
      document.getElementById('form-filter').submit();
    });
  });

  function preparePlayRecordingButtons() {
    $('.btn-play-recording').click(function(e) {
      var fileName = $(this).attr('data-value');
      $('#audio-player-container').attr('src', '{{ asset('storage/files/recordings') }}/' + fileName);
      $('#audio-player-container').attr('type', 'audio/mpeg');

      let urlDownloadRecording = '{{ url('calllogs/recording?audio=') }}'+fileName;
      $('#btn-download-recording').attr('onclick','window.open("'+urlDownloadRecording+'","_blank")');
    });
  };

  function prepareChangeCampaignDropDown() {
    $('#select-campaign-list').change(function() {
      $('#startdate, #enddate').val('');
      $('#modal-spinner').modal('show');
      location.href = '{{ route('calllogs.list') }}/' + $(this).find(':selected').val();
    });
  };


  // Export
  const elModalExport = document.getElementById('modalExport');
  const formExport = elModalExport.querySelector('#form-export');
  const modalExport = new bootstrap.Modal('#modalExport', {
    keyboard: false
  })


  $(formExport).find('#export-startdate').datepicker({
      format: "dd/mm/yyyy",
      autoclose: true,
      minView:2,
      endDate:new Date()
  }).on('changeDate', function(evt){
    $(formExport).find('#export-enddate').datepicker('setStartDate',evt.date).focus().val('');
  });

  $(formExport).find('#export-enddate').datepicker({
      format: "dd/mm/yyyy",
      autoclose: true,
      minView:2,
      endDate:new Date()
  }).on('changeDate', function(evt){
    const startdate = $(formExport).find('#export-startdate').datepicker('getDate');
    const enddate = evt.date;
    const intervalDay = (enddate - startdate)/1000/60/60/24;
    if(intervalDay >= 31){
      $(formExport).find('.date-range-validation').show();
    }else{
      $(formExport).find('.date-range-validation').hide();
    }
  });

  $(formExport).find('select[name="export_campaign"]').on('change', function(){
    let val = $(this).val();
    let checkboxDisabledDateRange = $(formExport).find('.checkbox-disabled-date-range');
    if(val.length > 0){
      checkboxDisabledDateRange.removeClass('visually-hidden');
    }else{
      checkboxDisabledDateRange.addClass('visually-hidden');
      checkboxDisabledDateRange.find('input[type="checkbox"]').prop('checked', false);
      $(formExport).find('#export-startdate, #export-enddate').removeAttr('disabled');
    }
  });

  $(formExport).find('.checkbox-disabled-date-range').on('change', function(e){
    if(e.target.checked){
      $(formExport).find('#export-startdate, #export-enddate').attr('disabled',true).val('');
    }else{
      $(formExport).find('#export-startdate, #export-enddate').removeAttr('disabled');
    }
  });
  
  elModalExport.querySelector('#btn-submit-export').addEventListener('click', () => {
    formExport.querySelector('button[type="submit"]').click();
  });

  formExport.addEventListener('submit', function(e){
    const startdate = $(formExport).find('#export-startdate');
    const enddate   = $(formExport).find('#export-enddate');
    if(!startdate.prop('disabled') && !enddate.prop('disabled')){
      const exStartdate = startdate.datepicker('getDate');
      const exEnddate   = enddate.datepicker('getDate');   
      const intervalDay = (exEnddate - exStartdate)/1000/60/60/24;
      
      if(startdate.val().length == 0 || enddate.val().length == 0){
        e.preventDefault();
      }else if(intervalDay >= 31){
        $(formExport).find('#export-startdate')[0].setCustomValidity('d');
        $(formExport).find('#export-enddate')[0].setCustomValidity('d');
        $(formExport).find('.date-range-validation').show();
        e.preventDefault();
      }else{
        $(formExport).find('#export-startdate')[0].setCustomValidity('');
        $(formExport).find('#export-enddate')[0].setCustomValidity('');
        $(formExport).find('.date-range-validation').hide();
        modalExport.hide();
      }
    }    
  });

  // modal export on close
  elModalExport.addEventListener('hidden.bs.modal', (event) => {
    formExport.classList.remove('was-validated');
    formExport.reset();
    $(formExport).find('.checkbox-disabled-date-range').addClass('visually-hidden');
    $(formExport).find('input[type="checkbox"]').prop('checked', false);
    $(formExport).find('#export-startdate, #export-enddate').removeAttr('disabled');
  });
</script>
@endpush

@include('layouts.include_page_footer')
@include('layouts.include_js')
