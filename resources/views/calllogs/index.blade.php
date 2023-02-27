@include('layouts.include_page_header')
@include('layouts.include_sidebar')

<main id="main" class="main">
  <div class="pagetitle">
    <h1>Call Logs</h1>
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
            </h5>

            <div class="col-md-4">
              <div class="form-floating mb-3">
                <select class="form-select" id="select-campaign-list" aria-label="Campaign">
                  <option value=""></option>
                  @foreach ($campaigns AS $keyCampaign => $valueCampaign)
                    @if ($valueCampaign->id === $selectedCampaign)
                    <option value="{{ $valueCampaign->id }}" selected>{{ $valueCampaign->name }}</option>
                    @else
                    <option value="{{ $valueCampaign->id }}">{{ $valueCampaign->name }}</option>
                    @endif
                  @endforeach
                </select>
                <label for="select-campaign">Campaign</label>
              </div>
            </div>

            <table id="table-campaign-list-container" class="table table-hover">
              <thead>
                <tr>
                  <th scope="col">#</th>
                  <th scope="col">Name</th>
                  <th scope="col">Call Dial</th>
                  <th scope="col">Call Connect</th>
                  <th scope="col">Call Disconnect</th>
                  <th scope="col">Call Duration (sec.)</th>
                  <th scope="col">Call Response</th>
                  <th scope="col">Call Recording</th>
                </tr>
              </thead>

              <tbody>
                @foreach ($calllogs AS $keyCallLog => $valueCallLog)
                <tr>
                  <td>{{ $valueCallLog->id }}</td>
                  <td>{{ $valueCallLog->name }}</td>
                  <td>{{ $valueCallLog->call_dial }}</td>
                  <td>{{ $valueCallLog->call_connect }}</td>
                  <td>{{ $valueCallLog->call_disconnect }}</td>
                  <td class="text-end">{{ $valueCallLog->call_duration }}</td>
                  <td>{{ $valueCallLog->call_response }}</td>
                  <td>
                    @if (!empty($valueCallLog->call_duration))
                    <button type="button" class="btn btn-success btn-play-recording" data-bs-toggle="modal" data-bs-target="#modal-play-recording" data-value="{{ $valueCallLog->call_recording }}">
                      <i class="bi bi-play-fill"></i> 
                    </button>
                    @endif
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>

            {{ $calllogs->links() }}

            <div class="col-md-12 mt-4">
              <form id="form-campaign-export" class="g-3 needs-validation" method="POST" target="_blank" action="{{ route('calllog.export') }}" enctype="multipart/form-data">
                <button type="button" class="btn btn-success btn-export-as" id="btn-export-excel" data-export-as="excel">Export Excel</button>
                <button type="button" class="btn btn-danger btn-export-as" id="btn-export-pdf" data-export-as="pdf">Export PDF</button>
                <input type="hidden" id="campaign-export-type" name="export_type" value="">
                <input type="hidden" id="campaign-export" name="campaign" value="{{ $selectedCampaign or '' }}">
                {{ csrf_field() }}
              </form>
            </div>

            <div class="modal fade" id="modal-play-recording" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" style="display:none;" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">Call Recording</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>

                  <div class="modal-body">
                    <audio id="audio-player-container" src="" type="" controls autoplay>
                      Your browser does not support the audio element.
                    </audio> 
                  </div>

                  <div class="modal-footer">
                    <button id="btn-close-play-recording" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>

      </div>
    </div>
  </section>
</main>

@push('javascript')
<script type="text/javascript">
  $(document).ready(function() {
    preparePlayRecordingButtons();
    prepareChangeCampaignDropDown();

    $('#btn-close-play-recording').click(function(e) {
      audio.pause();
      audio.currentTime = 0;
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

  function preparePlayRecordingButtons() {
    $('.btn-play-recording').click(function(e) {
      var fileName = $(this).attr('data-value');
      $('#audio-player-container').attr('src', '{{ asset('storage/files/recordings') }}/' + fileName);
      $('#audio-player-container').attr('type', 'audio/mpeg');
    });
  };

  function prepareChangeCampaignDropDown() {
    $('#select-campaign-list').change(function() {
      $('#modal-spinner').modal('show');
      location.href = '{{ route('calllogs') }}/' + $(this).find(':selected').val();
    });
  };
</script>
@endpush

@include('layouts.include_page_footer')
@include('layouts.include_js')
