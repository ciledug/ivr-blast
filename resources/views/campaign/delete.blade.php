@include('layouts.include_page_header')
@include('layouts.include_sidebar')

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

        <div class="card">
          <div class="card-body">
            <h5 class="card-title">
                Delete Campaign
            </h5>

            @if ($campaign->total_calls > 0)
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <h4 class="alert-heading">Campaign Delete Error</h4>
              <p>Can not delete a campaign that has been ran or finished.</p>
            </div>
            @endif

            <div class="row">
              <!-- left column -->
              <div class="col-md-6">
                <div class="row">
                  <div class="col-md-12">
                    <div class="row">
                      <div class="col-lg-3 col-md-4 label">Name</div>
                    </div>
                    <div class="row">
                      <div id="dialog-detail-campaign-name" class="col-lg-9 col-md-8 h5">{{ $campaign->name }}</div>
                    </div>
                  </div>
                </div>
                
                <div class="row">
                  <div class="col-md-12 mt-2">
                    <div class="row">
                      <div class="col-lg-3 col-md-4 label">Total Data</div>
                    </div>
                    <div class="row">
                      <div id="dialog-detail-campaign-total-data" class="col-lg-9 col-md-8 h5">
                        {{ number_format($campaign->total_data, 0, ',', '.') }}
                      </div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12 mt-2">
                    <div class="row">
                      <div class="col-lg-3 col-md-4 label">Status</div>
                    </div>
                    <div class="row">
                      <div id="dialog-detail-campaign-status" class="col-lg-9 col-md-8 h5">
                      @php
                          switch ($campaign->status) {
                            default: break;
                            case 0: echo 'Ready'; break;
                            case 1: echo 'Running'; break;
                            case 2: echo 'Paused'; break;
                            case 3: echo 'Finished'; break;
                          }
                      @endphp
                      </div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12 mt-2">
                    <div class="row">
                      <div class="col-lg-5 col-md-4 label">Created Date</div>
                    </div>
                    <div class="row">
                      <div id="dialog-detail-campaign-created-date" class="col-lg-7 col-md-8 h5">
                        {{ date('d/m/Y - H:i', strtotime($campaign->created_at)) }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- right column -->
              <div class="col-md-6">
                <div class="row">
                  <div class="col-md-12 mt-2">
                    <div class="row">
                      <div class="col-lg-4 col-md-4 label">Date Started</div>
                    </div>
                    <div class="row">
                      <div id="dialog-detail-campaign-date-started" class="col-lg-8 col-md-8 h5">
                        {{ $campaign->started ? date('d/m/Y - H:i', strtotime($campaign->started)) : '-' }}
                      </div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12 mt-2">
                    <div class="row">
                      <div class="col-lg-4 col-md-4 label">Date Finished</div>
                    </div>
                    <div class="row">
                      <div id="dialog-detail-campaign-date-finished" class="col-lg-8 col-md-8 h5">
                        {{ $campaign->finished ? date('d/m/Y - H:i', strtotime($campaign->finished)) : '-' }}
                      </div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12 mt-2">
                    <div class="row">
                      <div class="col-lg-4 col-md-4 label">Total Calls</div>
                    </div>
                    <div class="row">
                      <div id="dialog-detail-campaign-total-calls" class="col-lg-8 col-md-8 h5">{{ number_format($campaign->total_calls, 0, ',', '.') }}</div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12 mt-2">
                    <div class="row">
                      <div class="col-lg-4 col-md-4 label">Success Calls</div>
                    </div>
                    <div class="row">
                      <div id="dialog-detail-campaign-success-calls" class="col-lg-8 col-md-8 h5">{{ number_format($campaign->success, 0, ',', '.') }}</div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12 mt-2">
                    <div class="row">
                      <div class="col-lg-4 col-md-4 label">Failed Calls</div>
                    </div>
                    <div class="row">
                      <div id="dialog-detail-campaign-failed-calls" class="col-lg-8 col-md-8 h5">{{ number_format($campaign->failed, 0, ',', '.') }}</div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12 mt-2">
                    <div class="row">
                      <div class="col-lg-6 col-md-4 label">Campaign Progress (%)</div>
                    </div>
                    <div class="row">
                      <div id="dialog-detail-campaign-progress" class="col-lg-7 col-md-8 h5">
                        @php
                          echo ($campaign->dialed_contacts > 0) ? number_format((($campaign->dialed_contacts / $campaign->total_data) * 100), 2, ',', '.') : '0';
                        @endphp
                      </div>
                    </div>
                  </div>
                </div>

              </div>
            </div>

            <hr />

            <!-- contacts container -->
            <div class="col-md-12 mt-4">
              <table id="table-contact-list-container" class="table table-hover">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Account ID</th>
                    <th scope="col">Name</th>
                    <th scope="col">Phone</th>
                    <th scope="col">Bill Date</th>
                    <th scope="col">Due Date</th>
                    <th scope="col">Nominal</th>
                    <th scope="col">Call Date</th>
                    <th scope="col">Call Response</th>
                    <th scope="col">Action</th>
                  </tr>
                </thead>

                <tbody>
                  @foreach($contacts AS $keyContact => $valContact)
                  <tr>
                    <td class="text-end">{{ $row_number++ }}.</td>
                    <td>{{ $valContact->account_id }}</td>
                    <td>{{ $valContact->name }}</td>
                    <td>{{ $valContact->phone }}</td>
                    <td>{{ $valContact->bill_date }}</td>
                    <td>{{ $valContact->due_date }}</td>
                    <td class="text-end">{{ number_format($valContact->nominal, 2, ',', '.') }}</td>
                    <td>{{ $valContact->call_dial }}</td>
                    <td>{{ $valContact->call_response }}</td>
                    <td>
                      <a href="{{ url('contacts') }}/{{ $valContact->id }}" class="btn btn-sm btn-info">Detail</a>
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>

              {{ $contacts->links() }}
            </div>

            <div class="row mt-4">
              <form id="form-delete-campaign" method="POST" action="{{ url('campaigns') }}">
                <a href="{{ route('campaigns') }}" class="btn btn-secondary btn-back">Cancel</a>

                @if ($campaign->total_calls == 0)
                <button type="submit" class="btn btn-danger" id="btn-delete-campaign">Delete</button>&nbsp;
                <div id="submit-spinner-delete-campaign" class="spinner-border spinner-border-sm text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
                <input type="hidden" id="input-delete-campaign" name="campaign" value="{{ $campaign->id }}">
                {{ csrf_field() }}
                <input type="hidden" id="input-delete-method" name="_method" value="DELETE">
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
<script type="text/javascript">
  $(document).ready(function() {
    $('#submit-spinner-delete-campaign').hide();

    $('#form-delete-campaign').submit(function() {
      $('.btn-back').addClass('disabled');
      $('#btn-delete-campaign').addClass('disabled');
      $('#submit-spinner-delete-campaign').show();
    });
  });
</script>
@endpush

@include('layouts.include_page_footer')
@include('layouts.include_js')
