@include('layouts.include_page_header')
@include('layouts.include_sidebar')

<main id="main" class="main">
  <div class="pagetitle">
    <h1>Campaigns</h1>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-lg-12">

        <div class="card">
          <div class="card-body">
            <h5 class="card-title">
              <a href="{{ route('campaigns.create') }}" id="btn-add-campaign" class="btn btn-primary">
                Add Campaign
              </a>
            </h5>

            <table id="table-campaign-list-container" class="table table-hover">
              <thead>
                <tr>
                  <th scope="col" width="40px">#</th>
                  <th scope="col" width="160px">Date Created</th>
                  <th scope="col">Name</th>
                  <th scope="col" width="100px">Total Data</th>
                  <th scope="col">Status</th>
                  <th scope="col">Created By</th>
                  <th scope="col" width="250px">Action</th>
                </tr>
              </thead>
              
              <tbody>
                @if($campaigns->count() > 0)
                  @foreach ($campaigns AS $keyCampaign => $valueCampaign)
                  <tr>
                    <td>{{ $row_number++ }}.</td>
                    <td>{{ $valueCampaign->created_at }}</td>
                    <td>{{ $valueCampaign->name }}</td>
                    <td class="text-end">{{ number_format($valueCampaign->total_data, 0, ',', '.') }}</td>
                    <td>
                      @php
                          switch($valueCampaign->status) {
                            default: break;
                            case 0: echo 'Ready'; break;
                            case 1: echo 'Running'; break;
                            case 2: echo 'Paused'; break;
                            case 3: echo 'Finished'; break;
                          }
                      @endphp
                    </td>
                    <td>{{ $valueCampaign->created_by }}</td>
                    <td>
                      @if ($valueCampaign->status != 3)
                        @if ($valueCampaign->status == 0)
                        <button type="button" class="btn btn-sm btn-success btn-start-stop" data-key="{{ $valueCampaign->id}}" onclick="startStopCampaign('{{ $valueCampaign->id }}', '{{ $valueCampaign->status }}')">Start</button>
                        @elseif ($valueCampaign->status == 1)
                        <button type="button" class="btn btn-sm btn-danger btn-start-stop" data-key="{{ $valueCampaign->id}}" onclick="startStopCampaign('{{ $valueCampaign->id }}', '{{ $valueCampaign->status }}')">Paused</button>
                        @elseif ($valueCampaign->status == 2)
                        <button type="button" class="btn btn-sm btn-success btn-start-stop" data-key="{{ $valueCampaign->id}}" onclick="startStopCampaign('{{ $valueCampaign->id }}', '{{ $valueCampaign->status }}')">Resume</button>
                        @endif
                      @endif

                      <a href="{{ route('campaigns.show', ['id' => $valueCampaign->id]) }}" class="btn btn-sm btn-info btn-modal-spinner">Detail</a>

                      @if ($valueCampaign->status != 3)
                        @if ($valueCampaign->dialed_contacts == 0)
                          <a href="{{ route('campaigns.edit', ['id' => $valueCampaign->id]) }}" class="btn btn-sm btn-warning btn-modal-spinner">Edit</a>
                        @endif
                      @endif


                      @if ($valueCampaign->status != 3)
                        @if ($valueCampaign->dialed_contacts == 0)
                          <a href="{{ route('campaigns.delete', ['id' => $valueCampaign->id]) }}" class="btn btn-sm btn-danger btn-modal-spinner">Delete</a>
                        @endif
                      @endif
                    </td>
                  </tr>
                  @endforeach
                @else
                  <tr>
                    <td colspan="7" class="text-center">There's no campaign data</td>
                  </tr>
                @endif  
              </tbody>
            </table>

            {{ $campaigns->links() }}

          </div>
        </div>

      </div>
    </div>
  </section>
</main>

@push('javascript')
<script type="text/javascript">
  $(document).ready(function() {
    $('.btn-modal-spinner').click(function(e) {
      $('#modal-spinner').modal('show');
    });
  });

  function startStopCampaign(campaignKey, currentStatus) {
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
        location.reload();
      },
      error: function(error) {
        console.log(error.responseText);
      }
    })
    .always(function() {
    });
  };
</script>
@endpush

@include('layouts.include_page_footer')
@include('layouts.include_js')
