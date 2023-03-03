@include('layouts.include_page_header')
@include('layouts.include_sidebar')

<main id="main" class="main">
  <div class="pagetitle">
    <h1>Contacts</h1>
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
                Detail Contact
            </h5>

            <div class="row">
              <div class="col">                
                <div class="row">
                  <div class="col-md-12 mt-2">
                    <div class="row">
                      <div class="col-lg-4 col-md-4 label">Account ID</div>
                    </div>
                    <div class="row">
                      <div class="col-lg-8 col-md-8 h5" id="dialog-detail-contact-account-id">{{ $dataContact->account_id }}</div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12 mt-2">
                    <div class="row">
                      <div class="col-lg-4 col-md-4 label">Name</div>
                    </div>
                    <div class="row">
                      <div class="col-lg-8 col-md-8 h5" id="dialog-detail-contact-name">{{ $dataContact->name }}</div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12 mt-2">
                    <div class="row">
                      <div class="col-lg-4 col-md-4 label">Phone</div>
                    </div>
                    <div class="row">
                      <div class="col-lg-8 col-md-8 h5" id="dialog-detail-contact-phone">{{ $dataContact->phone }}</div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col">
                <div class="row">
                  <div class="col-md-12">
                    <div class="row">
                      <div class="col-lg-4 col-md-4 label">Bill Date</div>
                    </div>
                    <div class="row">
                      <div class="col-lg-8 col-md-8 h5" id="dialog-detail-contact-bill-date">{{ $dataContact->bill_date }}</div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12 mt-2">
                    <div class="row">
                      <div class="col-lg-4 col-md-4 label">Due Date</div>
                    </div>
                    <div class="row">
                      <div class="col-lg-8 col-md-8 h5" id="dialog-detail-contact-due-date">{{ $dataContact->due_date }}</div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12 mt-2">
                    <div class="row">
                      <div class="col-lg-5 col-md-4 label">Nominal (Rp.)</div>
                    </div>
                    <div class="row">
                      <div class="col-lg-7 col-md-8 h5" id="dialog-detail-contact-nominal">{{ number_format($dataContact->nominal,0,'.','.') }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <br>
            <h5 class="card-title">
                Call Logs
            </h5>
            <div class="row">
              <div class="col">
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th scope="col">Call Date</th>
                      <th scope="col">Call Dial</th>
                      <th scope="col">Call Connect</th>
                      <th scope="col">Call Disconnect</th>
                      <th scope="col">Call Duration</th>
                      <th scope="col">Call Response</th>
                      <th scope="col" style="text-align: center;" width="100px">Recording</th>
                    </tr>
                  </thead>
                  <tbody>
                    @if($dataLogs->count() > 0)
                      @foreach($dataLogs as $logs)
                        <tr>
                          <td>{{ date('d/m/Y', strtotime($logs->call_dial)) }}</td>
                          <td>{{ date('H:i:s', strtotime($logs->call_dial)) }}</td>
                          <td>{{ $logs->call_connect ? date('H:i:s', strtotime($logs->call_connect)) : '' }}</td>
                          <td>{{ $logs->call_disconnect ? date('H:i:s', strtotime($logs->call_disconnect)) : '' }}</td>
                          <td>{{ $logs->call_duration > 0 ? App\Helpers\Helpers::secondsToHms($logs->call_duration) : '' }}</td>
                          <td>{{ $logs->call_response }}</td>
                          <td style="text-align: center;padding:4px 0px">
                            @if($logs->call_duration > 0)
                              <button type="button" class="btn btn-sm btn-success btn-play-recording" data-bs-toggle="modal" data-bs-target="#modal-play-recording" data-value="{{ $logs->call_recording }}">
                                <i class="bi bi-play-fill"></i> 
                              </button>
                            @endif
                          </td>
                        </tr>
                      @endforeach
                    @else
                      <tr>
                        <td colspan="7" style="text-align: center">There's no call logs data</td>
                      </tr>
                    @endif
                  </tbody>
                </table>  
              </div>
            </div>

            <button type="button" class="btn btn-secondary btn-block btn-back" data-bs-dismiss="modal">Back</button>
          </div>
        </div>

      </div>
    </div>

    <div class="modal fade" id="modal-play-recording" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" style="display:none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Call Recording</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
              <audio id="audio-player-container" controls style="width:100%;border-radius: 4px;">
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
  </section>
</main>

@push('javascript')
<script type="text/javascript">
  $(document).ready(function(e) {
    $('.btn-back').click(function(e) {
      history.back();
    });
  });

  const modalRec = document.getElementById('modal-play-recording');
  const audioRec = modalRec.querySelector('#audio-player-container');

  modalRec.addEventListener('shown.bs.modal', () => {
    let src = document.querySelector('button.btn-play-recording').getAttribute('data-value');
    let urlRecording = '{{ url('calllog/recording?audio=') }}'+src;
    audioRec.src = urlRecording;
    audioRec.play();

    modalRec.querySelector('#btn-download-recording').setAttribute('onclick','window.open("'+urlRecording+'","_blank")');
  });

  modalRec.addEventListener('hide.bs.modal', (e) => {
    audioRec.pause();
    audioRec.currentTime = 0;
  });
</script>
@endpush

@include('layouts.include_page_footer')
@include('layouts.include_js')
