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
              <a href="{{ route('campaign.create') }}" id="btn-add-campaign" class="btn btn-primary">
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
                    <td>{{ $loop->index + 1 }}.</td>
                    <td>{{ $valueCampaign->created }}</td>
                    <td>{{ $valueCampaign->name }}</td>
                    <td class="text-end">{{ number_format($valueCampaign->total_data, 0, ',', '.') }}</td>
                    <td>{{ ucwords($valueCampaign->status) }}</td>
                    <td>{{ $valueCampaign->created_by }}</td>
                    <td>
                      @if ($valueCampaign->status != 'finished')
                        @if ($valueCampaign->status === 'ready')
                        <button type="button" class="btn btn-sm btn-success btn-start-stop" data-key="_{{ $valueCampaign->unique_key}}" onclick="startStopCampaign('{{ $valueCampaign->unique_key }}', '{{ $valueCampaign->status }}')">Start</button>
                        @elseif ($valueCampaign->status === 'running')
                        <button type="button" class="btn btn-sm btn-danger btn-start-stop" data-key="_{{ $valueCampaign->unique_key}}" onclick="startStopCampaign('{{ $valueCampaign->unique_key }}', '{{ $valueCampaign->status }}')">Paused</button>
                        @elseif ($valueCampaign->status === 'paused')
                        <button type="button" class="btn btn-sm btn-success btn-start-stop" data-key="_{{ $valueCampaign->unique_key}}" onclick="startStopCampaign('{{ $valueCampaign->unique_key }}', '{{ $valueCampaign->status }}')">Resume</button>
                        @endif
                      @endif

                      <a href="{{ route('campaign.show') }}/_{{ $valueCampaign->unique_key }}" class="btn btn-sm btn-info btn-modal-spinner">Detail</a>

                      @if ($valueCampaign->status != 'finished')
                        @if ($valueCampaign->status !== 'ready')
                          <a href="#" class="btn btn-sm btn-warning-outline disabled">Edit</a>
                        @else
                          <a href="{{ route('campaign.edit') }}/_{{ $valueCampaign->unique_key }}" class="btn btn-sm btn-warning btn-modal-spinner">Edit</a>
                        @endif
                      @endif
                      

                      @if ($valueCampaign->status != 'finished')
                        @if ($valueCampaign->status !== 'ready')
                          <a href="{{ route('campaign.delete') }}/_{{ $valueCampaign->unique_key }}" class="btn btn-sm btn-outline-danger disabled">Delete</a>
                        @else
                          <a href="{{ route('campaign.delete') }}/_{{ $valueCampaign->unique_key }}" class="btn btn-sm btn-danger btn-modal-spinner">Delete</a>
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
// var campaignListContainer = '';
// var campaignList = [];

  $(document).ready(function() {
    // prepareCampaignListTable();
    $('.btn-modal-spinner').click(function(e) {
      $('#modal-spinner').modal('show');
    });
  });

  function startStopCampaign(campaignKey, currentStatus) {
    $.ajax({
      method: 'PUT',
      url: '{{ route('campaign.update.startstop') }}',
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
        // campaignListContainer.ajax.reload();
        location.reload();
      },
      error: function(error) {
        console.log(error.responseText);
      }
    })
    .always(function() {
    });
  };

  /*
  function prepareCampaignListTable() {
    campaignListContainer = $('#table-campaign-list-container').DataTable({
      processing: true,
      lengthMenu: [5, 10, 15, 20, 50, 100],
      pageLength: 10,
      responsive: true,
      serverSide: true,
      ajax: {
        url: '{{ route('campaign.list.ajax') }}',
        type: 'POST',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
      },
      columns: [
        { data: null },
        { data: 'created' },
        { data: 'name' },
        { data: 'total' },
        { data: 'status' },
        { data: 'created_by' }
      ],
      columnDefs: [
        {
          targets: 0,
          orderable: false,
          render: function(data, type, row, meta) {
            return ++meta.row + '.';
          }
        },
        {
          targets: 3,
          className: 'dt-body-right'
        },
        {
          targets: 6,
          data: null,
          render: function(data, type, row, meta) {
            campaignList['_' + row.key] = row;

            var tempContent = '';
            var titleStartStop = 'Start';
            var btnStartStopCss = ' btn-success';
            var btnEditCss = ' btn-warning';
            var btnDeleteCss = ' btn-danger';
            var rowStatus = row.status.toLowerCase();

            switch (rowStatus) {
              case 'running': titleStartStop = 'Pause'; btnStartStopCss = ' btn-danger'; btnEditCss = ' btn-outline-warning disabled'; btnDeleteCss = ' btn-outline-danger disabled'; break;
              case 'paused': titleStartStop = 'Resume'; btnDeleteCss = ' btn-outline-danger disabled'; break;
              case 'finished': btnStartStopCss += ' btn-outline-success disabled'; btnEditCss = ' btn-outline-warning disabled'; btnDeleteCss = ' btn-outline-danger disabled'; break;
              case 'ready':
              default: break;
            }

            tempContent += '<button type="button" class="btn btn-sm' + btnStartStopCss + ' btn-start-stop" data-key="_' + row.key + '" onclick="startStopCampaign(\'' + row.key + '\', \'' + rowStatus + '\')">' + titleStartStop + '</button>';
            tempContent += '&nbsp;<a href="{{ route('campaign.show') }}/_' + row.key + '" class="btn btn-sm btn-info">Detail</a>';

            if (row.progress <= 0) {
              tempContent += '&nbsp;<a href="{{ route('campaign.edit') }}/_' + row.key + '" class="btn btn-sm' + btnEditCss + '">Edit</a>';
            }
            
            tempContent += '&nbsp;<a href="{{ route('campaign.delete') }}/_' + row.key + '" class="btn btn-sm' + btnDeleteCss + '">Delete</a>';
            return tempContent;
          }
        },
      ]
    });
  };
  */
</script>
@endpush

@include('layouts.include_page_footer')
@include('layouts.include_js')
