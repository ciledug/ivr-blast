@include('layouts.include_page_header')
@include('layouts.include_sidebar')

<main id="main" class="main">
  {{-- @php echo '<pre>'; print_r($campaign); echo '</pre>'; @endphp --}}

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

        <div class="card">
          <div class="card-body">
            <h5 class="card-title">
                Detail Campaign
            </h5>

            <!-- info container -->
            <div class="row mt-2">
              <!-- left column -->
              <div class="col-md-4">

                <!-- name -->
                <div class="row">
                  <div class="col-md-12">
                    <div class="row">
                      <div class="col-12 label fw-bold">Name</div>
                    </div>
                    <div class="row">
                      <div id="dialog-detail-campaign-name" class="col-12 h5">{{ $campaign->name }}</div>
                    </div>
                  </div>
                </div>
                
                <!-- total data -->
                <div class="row">
                  <div class="col-md-12 mt-2">
                    <div class="row">
                      <div class="col-12 label fw-bold">Total Data</div>
                    </div>
                    <div class="row">
                      <div id="dialog-detail-campaign-total-data" class="col-12 h5">
                        {{ number_format($campaign->total_data, 0, ',', '.') }}
                      </div>
                    </div>
                  </div>
                </div>

                <!-- campaign progress -->
                <div class="row">
                  <div class="col-md-12 mt-2">
                    <div class="row">
                      <div class="col-12 label fw-bold">Campaign Progress (%)</div>
                    </div>
                    <div class="row">
                      <div id="dialog-detail-campaign-progress" class="col-12 h5">
                        @php
                          echo ($campaign_info->dialed_contacts > 0) ? number_format((($campaign_info->dialed_contacts / $campaign->total_data) * 100), 2, ',', '.') : '0';
                        @endphp
                      </div>
                    </div>
                  </div>
                </div>

                <!-- status -->
                <div class="row">
                  <div class="col-md-12 mt-2">
                    <div class="row">
                      <div class="col-12 label fw-bold">Status</div>
                    </div>
                    <div class="row">
                      <div id="dialog-detail-campaign-status" class="col-12 h5">
                        @php
                          if ($campaign->total_data > 0) {
                            switch ($campaign->status) {
                              case 0: echo 'Ready'; break;
                              case 1: echo 'Running'; break;
                              case 2: echo 'Paused'; break;
                              case 3: echo 'Finished'; break;
                              default: echo '-'; break;
                            }
                          }
                          else {
                            echo '-';
                          }
                        @endphp
                      </div>
                    </div>
                  </div>
                </div>

                <!-- start button -->
                @if (($campaign->total_data > 0) && ($campaign->status !== 3))
                <div class="row">
                  <div class="col-md-12 mt-4">
                      @if ($campaign->status === 0)
                      <button type="button" class="btn btn-success btn-campaign-status" id="btn-start-campaign" data-status-value="0">Start Campaign</button>
                      @elseif ($campaign->status === 1)
                      <button type="button" class="btn btn-danger btn-campaign-status" id="btn-pause-campaign" data-status-value="1">Pause Campaign</button>
                      @elseif ($campaign->status === 2)
                      <button type="button" class="btn btn-success btn-campaign-status" id="btn-resume-campaign" data-status-value="2">Resume Campaign</button>
                      @endif
                  </div>
                </div>
                @endif
              </div>

              <!-- middle column -->
              <div class="col-md-4">
                <!-- created date -->
                <div class="row">
                  <div class="col-md-12">
                    <div class="row">
                      <div class="col-12 label fw-bold">Created Date</div>
                    </div>
                    <div class="row">
                      <div id="dialog-detail-campaign-created-date" class="col-12 h5">
                        {{ date('d/m/Y - H:i', strtotime($campaign->created_at)) }}
                      </div>
                    </div>
                  </div>
                </div>

                <!-- date started -->
                <div class="row">
                  <div class="col-md-12 mt-2">
                    <div class="row">
                      <div class="col-12 label fw-bold">Started Date</div>
                    </div>
                    <div class="row">
                      <div id="dialog-detail-campaign-date-started" class="col-12 h5">
                        {{ $campaign_info->started ? date('d/m/Y - H:i', strtotime($campaign_info->started)) : '-' }}
                      </div>
                    </div>
                  </div>
                </div>

                <!-- date finished -->
                <div class="row">
                  <div class="col-md-12 mt-2">
                    <div class="row">
                      <div class="col-12 label fw-bold">Finished Date</div>
                    </div>
                    <div class="row">
                      <div id="dialog-detail-campaign-date-finished" class="col-12 h5">
                        {{ $campaign_info->finished ? date('d/m/Y - H:i', strtotime($campaign_info->finished)) : '-' }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- right column -->
              <div class="col-md-4">
                <!-- total calls -->
                <div class="row">
                  <div class="col-md-12">
                    <div class="row">
                      <div class="col-12 label"><strong>Total Calls</strong></div>
                    </div>
                    <div class="row">
                      <div id="dialog-detail-campaign-total-calls" class="col-12 h5">{{ number_format($campaign_info->total_calls, 0, ',', '.') }}</div>
                    </div>
                  </div>
                </div>

                <!-- success calls -->
                <div class="row">
                  <div class="col-md-12 mt-2">
                    <div class="row">
                      <div class="col-12 label"><strong>Success Calls</strong></div>
                    </div>
                    <div class="row">
                      <div id="dialog-detail-campaign-success-calls" class="col-12 h5">{{ number_format($campaign_info->success, 0, ',', '.') }}</div>
                    </div>
                  </div>
                </div>

                <!-- failed calls -->
                <div class="row">
                  <div class="col-md-12 mt-2">
                    <div class="row">
                      <div class="col-12 label"><strong>Failed Calls</strong></div>
                    </div>
                    <div class="row">
                      <div id="dialog-detail-campaign-failed-calls" class="col-12 h5">{{ number_format($campaign_info->failed, 0, ',', '.') }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <hr class="mt-4 mb-4" />

            <!-- contacts container -->
            <table class="table table-hover">
              <thead>
                <tr>
                  @if($template_headers->count() > 0)
                  <th scope="col" class="align-top">#</th>

                  @foreach($template_headers AS $keyHeader => $valHeader)
                  <th scope="col" class="align-top">
                    @php
                    $tempInfo = [];
                    if ($valHeader->is_mandatory) $tempInfo[] = 'mandatory';
                    if ($valHeader->is_unique) $tempInfo[] = 'unique';
                    if ($valHeader->is_voice) $tempInfo[] = 'voice-' . $valHeader->voice_position;
                    echo strtoupper($valHeader->name) . '<br><small class="fw-lighter">(' . implode(', ', $tempInfo) . ')</small>';
                    @endphp
                  </th>
                  @endforeach

                  <th scope="col" class="align-top">CALL DATE</th>
                  <th scope="col" class="align-top">CALL RESPONSE</th>
                  <th scope="col" class="align-top">TOTAL CALLS</th>
                  <th scope="col" class="align-top">ACTION</th>
                  @else
                  <th scope="col"></th>
                  @endif
                </tr>
              </thead>

              <tbody>
                @foreach ($contacts->chunk(300) as $chunks)
                  @foreach ($chunks as $valueData)
                    <tr>
                      <td class="text-end">{{ $row_number++ }}.</td>
                      @php $headerName = ''; @endphp
                      @foreach($template_headers AS $keyHeader => $valHeader)
                        @php $headerName = strtolower($valHeader->name); @endphp

                        @if ($valHeader->column_type === 'string')
                        <td>{{ $valueData->$headerName }}</td>
                        @elseif ($valHeader->column_type === 'datetime')
                        <td>{{ $valueData->$headerName ? date('d/m/Y H:i', strtotime($valueData->$headerName)) : '-' }}</td>
                        @elseif ($valHeader->column_type === 'date')
                        <td>{{ $valueData->$headerName ? date('d/m/Y', strtotime($valueData->$headerName)) : '-' }}</td>
                        @elseif ($valHeader->column_type === 'time')
                        <td>{{ $valueData->$headerName ? date('H:i:s', strtotime($valueData->$headerName)) : '-' }}</td>
                        @elseif ($valHeader->column_type === 'handphone')
                        <td>{{ substr($valueData->$headerName, 0, 4) . 'xxxxxx' . substr($valueData->$headerName, strlen($valueData->$headerName) - 3) }}</td>
                        @elseif ($valHeader->column_type === 'numeric')
                        <td class="text-end">{{ number_format($valueData->$headerName, 0, ',', '.') }}</td>
                        @endif
                      @endforeach

                      <td>{{ $valueData->call_dial ? date('d/m/Y H:i', strtotime($valueData->call_dial)) : '-' }}</td>
                      <td>{{ $valueData->call_response or '-' }}</td>
                      <td class="text-end">{{ number_format($valueData->total_calls, 0, ',', '.') }}</td>
                      <td>
                        <a href="{{ route('contacts.show', [ 'id' => $valueData->id, 'campaignId' => $campaign->id ]) }}" class="btn btn-sm btn-info">Detail</a>
                      </td>
                    </tr>
                  @endforeach  
                @endforeach
              </tbody>
            </table>

            {{ $contacts->links() }}

            <div class="col-md-12 mt-4">
              <form id="form-campaign-export" class="g-3 needs-validation" method="POST" action="{{ url('campaigns/export') }}" enctype="multipart/form-data">
                <a href="{{ url('campaigns') }}" class="btn btn-secondary btn-back">Close</a>

                @if ($campaign->total_data > 0)
                <button type="button" class="btn btn-success btn-export-as" id="btn-export-excel" data-export-as="excel">Export Excel</button>
                <button type="button" class="btn btn-danger btn-export-as" id="btn-export-pdf" data-export-as="pdf">Export PDF</button>
                <input type="hidden" id="campaign-export-type" name="export_type" value="">
                <input type="hidden" id="campaign-export-key" name="campaign" value="{{ $campaign->id }}">
                {{ csrf_field() }}
                @endif
                
              </form>
            </div>

          </div>
        </div>

      </div>
    </div>
  </section>
</main>

@push('javascript')
<script src="{{ url('js/xlsx.full.min.js') }}"></script>
<script type="text/javascript">
  $(document).ready(function() {
    $('#btn-export-excel').click(function(e) {
      $('#campaign-export-type').val('excel');
      $('#form-campaign-export').submit();
    });

    $('#btn-export-pdf').click(function(e) {
      $('#campaign-export-type').val('pdf');
      $('#form-campaign-export').submit();
    });

    $('.btn-campaign-status').click(function(e) {
      $('.btn-campaign-status').addClass('disabled');
      startStopCampaign({{ $campaign->id }}, $(this).attr('data-status-value'));
    });
  });

  function startStopCampaign(campaignKey, currentStatus) {
    $('#modal-spinner').modal('show');

    $.ajax({
      method: 'PUT',
      url: '{{ route('campaigns.update.startstop') }}',
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      data: JSON.stringify({
        campaign: campaignKey,
        currstatus: currentStatus,
        startstop: true,
        _token: '{{ csrf_token() }}'
      }),
      processData: false,
      contentType: 'application/json',
      cache: false,
      success: function(response) {
        console.log(response);
        if (response.code === 200) {
          location.reload();
        }
        else {
          $('.btn-campaign-status').removeClass('disabled');
        }
      },
      error: function(error) {
        console.log(error.responseText);
      }
    })
    .always(function() {
    });
  };


  function sheet_to_csv_cb(ws, cb, opts, batch = 1000) {
    XLSX.stream.set_readable(() => ({
      __done: false,
      // this function will be assigned by the SheetJS stream methods
      _read: function() { this.__done = true; },
      // this function is called by the stream methods
      push: function(d) { if(!this.__done) cb(d); if(d == null) this.__done = true; },
      resume: function pump() { for(var i = 0; i < batch && !this.__done; ++i) this._read(); if(!this.__done) setTimeout(pump.bind(this), 0); }
    }));
    return XLSX.stream.to_csv(ws, opts);
  }

  /* this callback will run once the main context sends a message */
  // self.addEventListener('message', async(e) => {
  //   try {
  //     postMessage({state: "fetching " + e.data.url});
  //     /* Fetch file */
  //     const res = await fetch(e.data.url);
  //     const ab = await res.arrayBuffer();

  //     /* Parse file */
  //     postMessage({state: "parsing"});
  //     const wb = XLSX.read(ab, {dense: true});
  //     const ws = wb.Sheets[wb.SheetNames[0]];

  //     /* Generate CSV rows */
  //     postMessage({state: "csv"});
  //     const strm = sheet_to_csv_cb(ws, (csv) => {
  //       if(csv != null) postMessage({csv});
  //       else postMessage({state: "done"});
  //     });
  //     strm.resume();
  //   } catch(e) {
  //     /* Pass the error message back */
  //     postMessage({error: String(e.message || e) });
  //   }
  // }, false);

  // worker.onmessage = function(e) {
  //   if(e.data.error) { console.error(e.data.error); /* show an error message */ }
  //   else if(e.data.state) { console.info(e.data.state); /* current state */ }
  //   else {
  //     /* e.data.csv is the row generated by the stream */
  //     console.log(e.data.csv);
  //   }
  // };
</script>
@endpush

@include('layouts.include_page_footer')
@include('layouts.include_js')
