@include('layouts.include_page_header')
@include('layouts.include_sidebar')
<link rel="stylesheet" type="text/css" href="{{ asset('css/datepicker.min.css') }}">
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
            <form action="{{ route('calllogs') }}" method="GET" id="form-filter">
              {{ csrf_field() }}
              <div class="row mt-4 mb-4">
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
                      <a href="{{ route('calllogs') }}" class="btn btn-outline-primary">Clear Date</a>
                    </div>
                    @endif
                  </div>
                </div>
                <div class="col-md-4 offset-md-2">
                    <select class="form-select" id="select-campaign-list" aria-label="Campaign" name="campaign">
                      <option value="" selected>-- All Campaign --</option>
                      @foreach ($campaigns AS $keyCampaign => $valueCampaign)
                        @if ($valueCampaign->id === $selectedCampaign)
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
              <thead>
                <tr>
                  <th scope="col">Call Date</th>
                  <th scope="col">Account ID</th>
                  <th scope="col">Phone Number</th>
                  <th scope="col" width="110px" >Dial</th>
                  <th scope="col" width="110px" >Connect</th>
                  <th scope="col" width="110px" >Disconnect</th>
                  <th scope="col" width="110px" >Duration</th>
                  <th scope="col">Call Response</th>
                  <th scope="col" width="80px" class="text-center">Recording</th>
                </tr>
              </thead>

              <tbody>
                @if($calllogs->count() > 0)
                  @foreach ($calllogs AS $keyCallLog => $valueCallLog)
                  <tr>
                    <td>{{ date('d/m/Y', strtotime($valueCallLog->call_dial)) }}</td>
                    <td>{{ $valueCallLog->account_id }}</td>
                    <td>{{ $valueCallLog->phone }}</td>
                    <td>{{ date('H:i:s', strtotime($valueCallLog->call_dial)) }}</td>
                    <td>{{ $valueCallLog->call_connect ? date('H:i:s', strtotime($valueCallLog->call_connect)) : '' }}</td>
                    <td>{{ $valueCallLog->call_disconnect ? date('H:i:s', strtotime($valueCallLog->call_disconnect)) : '' }}</td>
                    <td>{{ $valueCallLog->call_duration > 0 ? App\Helpers\Helpers::secondsToHms($valueCallLog->call_duration) : '' }}</td>
                    <td>{{ $valueCallLog->call_response }}</td>
                    <td style="padding:4px 0;" class="text-center" >
                      @if (!empty($valueCallLog->call_duration))
                      <button type="button" class="btn btn-success btn-sm btn-play-recording" data-bs-toggle="modal" data-bs-target="#modal-play-recording" data-value="{{ $valueCallLog->call_recording }}">
                        <i class="bi bi-play-fill"></i> 
                      </button>
                      @endif
                    </td>
                  </tr>
                  @endforeach
                @else
                  <tr>
                    <td colspan="9" class="text-center">There's no data</td>
                  </tr>
                @endif
              </tbody>
            </table>

            {{ $calllogs->links() }}

            {{-- <div class="col-md-12 mt-4">
              <form id="form-campaign-export" class="g-3 needs-validation" method="POST" target="_blank" action="{{ route('calllog.export') }}" enctype="multipart/form-data">
                <button type="button" class="btn btn-success btn-export-as" id="btn-export-excel" data-export-as="excel">Export Excel</button>
                <button type="button" class="btn btn-danger btn-export-as" id="btn-export-pdf" data-export-as="pdf">Export PDF</button>
                <input type="hidden" id="campaign-export-type" name="export_type" value="">
                <input type="hidden" id="campaign-export" name="campaign" value="{{ $selectedCampaign or '' }}">
                <input type="hidden" name="startdate" value="{{ $startdate }}">
                <input type="hidden" name="enddate" value="{{ $enddate }}">
                {{ csrf_field() }}
              </form>
            </div> --}}

            

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
            <form action="{{ route('calllog.export') }}" method="POST" id="form-export" class="needs-validation" novalidate="" target="_blank">
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

      let urlDownloadRecording = '{{ url('calllog/recording?audio=') }}'+fileName;
      $('#btn-download-recording').attr('onclick','window.open("'+urlDownloadRecording+'","_blank")');
    });
  };

  // function prepareChangeCampaignDropDown() {
  //   $('#select-campaign-list').change(function() {
  //     $('#modal-spinner').modal('show');
  //     location.href = '{/{ route('calllogs') }}/' + $(this).find(':selected').val();
  //   });
  // };


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
