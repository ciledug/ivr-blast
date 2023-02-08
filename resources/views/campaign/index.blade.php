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
              <a href="{{ route('campaign.create') }}" id="btn-add-campaign" class="btn btn-primary">
                Add Campaign
              </a>
            </h5>

            <table id="table-campaign-list-container" class="table table-hover">
              <thead>
                <tr>
                  <th scope="col">#</th>
                  <th scope="col">Date Created</th>
                  <th scope="col">Name</th>
                  <th scope="col">Total Data</th>
                  <th scope="col">Status</th>
                  <th scope="col">Created By</th>
                  <th scope="col">Action</th>
                </tr>
              </thead>
            </table>
          </div>
        </div>

      </div>
    </div>
  </section>
</main>

@push('javascript')
<script type="text/javascript">
var campaignListContainer = '';
var campaignList = [];

  $(document).ready(function() {
    prepareCampaignListTable();
    getCampaignList();
  });
 
  function prepareCampaignListTable() {
    campaignListContainer = $('#table-campaign-list-container').DataTable({
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
              case 'running': titleStartStop = 'Stop'; btnStartStopCss = ' btn-danger'; btnEditCss = ' btn-outline-warning disabled'; btnDeleteCss = ' btn-outline-danger disabled'; break;
              case 'finished': btnStartStopCss += ' btn-outline-success disabled'; btnEditCss = ' btn-outline-warning disabled'; btnDeleteCss = ' btn-outline-danger disabled'; break;
              case 'ready':
              default: break;
            }

            tempContent += '<button type="button" class="btn btn-sm' + btnStartStopCss + ' btn-start-stop" data-key="_' + row.key + '" onclick="startStopCampaign(\'' + row.key + '\')">' + titleStartStop + '</button>';
            tempContent += '&nbsp;<a href="{{ route('campaign.show') }}/_' + row.key + '" class="btn btn-sm btn-info">Detail</a>';

            if (row.progress <= 0) {
              tempContent += '&nbsp;<a href="{{ route('campaign.edit') }}/_' + row.key + '" class="btn btn-sm' + btnEditCss + '">Edit</button>';
            }
            
            tempContent += '&nbsp;<a href="{{ route('campaign.delete') }}/_' + row.key + '" class="btn btn-sm' + btnDeleteCss + '">Delete</button>';
            return tempContent;
          }
        },
      ]
    });
  }

  function getCampaignList() {
    $.ajax({
      type: 'GET',
      url: '{{ route('campaign.list') }}',
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      processData: false,
      contentType: false,
      cache: false,
      success: function(response) {
        campaignListContainer.clear();
        campaignListContainer.rows.add(response.data);
        campaignListContainer.draw();
      },
      error: function(error) {
        console.log(error.responseText);
      }
    })
    .always(function() {
    });
  }

  function startStopCampaign(campaignKey) {
    $.ajax({
      method: 'PUT',
      url: '{{ route('campaign.update.startstop') }}',
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      data: JSON.stringify({
        campaign: campaignKey,
        startStop: true,
        _token: '{{ csrf_token() }}'
      }),
      processData: false,
      contentType: 'application/json',
      cache: false,
      success: function(response) {
        getCampaignList();
      },
      error: function(error) {
        console.log(error.responseText);
      }
    })
    .always(function() {
    });
  }
</script>
@endpush

@include('layouts.include_page_footer')
@include('layouts.include_js')
