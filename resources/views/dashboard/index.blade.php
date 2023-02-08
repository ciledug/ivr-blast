@include('layouts.include_page_header')
@include('layouts.include_sidebar')

<main id="main" class="main">
  <div class="pagetitle">
    <h1>Dashboard</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.html">Home</a></li>
        <li class="breadcrumb-item active">Dashboard</li>
      </ol>
    </nav>
  </div>
  
  <section class="section dashboard">
    <div class="row">
      
      <!-- Left side columns -->
      <div class="col-lg-8">
        <div class="row">

          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Running Campaigns</h5>
              <table id="table-running-campaign-list-container" class="table table-hover">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Name</th>
                    <th scope="col">Call Progress (%)</th>
                  </tr>
                </thead>
              </table>
            </div>
          </div>
        </div>
      </div>
      <!-- End Left side columns -->
      
      <!-- Right side columns -->
      <div class="col-lg-4">
        <div class="card">
          <div class="card-body pb-0">
            <h5 class="card-title">Live Broadcast Call <span>| Today</span></h5>
            <div id="chart-total-calls" style="min-height:400px;"></div>
          </div>
        </div>
      </div>
      <!-- End Right side columns -->

    </div>
  </section>
</main>

@push('javascript')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script type="text/javascript">
  var dataTableContainer = '';
  var callsChart = '';

  $(document).ready(function() {
    prepareTableContainer();
    prepareChart('chart-total-calls');
    getCampaignList(dataTableContainer);
    getCallStatus();
  });

  function prepareTableContainer() {
    dataTableContainer = $('#table-running-campaign-list-container').DataTable({
      columns: [
        { data: 'seq' },
        { data: 'name' },
        { data: 'progress' },
      ],
      columnDefs: [
        {
          targets: 2,
          className: 'dt-body-right',
        }
      ],
    });
  }

  function prepareChart(chartContainer) {
    callsChart = echarts.init(document.getElementById(chartContainer));
    callsChart.setOption({
      tooltip: {
        trigger: 'item'
      },
      legend: {
        top: '5%',
        left: 'center'
      },
      series: [{
        type: 'pie',
        radius: ['40%', '70%'],
        avoidLabelOverlap: false,
        label: {
          show: false,
          position: 'center'
        },
        emphasis: {
          label: {
            show: true,
            fontSize: '18',
            fontWeight: 'bold'
          }
        },
        labelLine: {
          show: false
        },
        data: []
      }]
    });
  }

  function getCampaignList(dataTableContainer) {
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
        dataTableContainer.clear();
        dataTableContainer.rows.add(response.data);
        dataTableContainer.draw();
      },
      error: function(error) {
        console.log(error.responseText);
      }
    })
    .always(function() {
    });
  }

  function getCallStatus() {
    $.ajax({
      type: 'GET',
      url: '{{ route('call.status') }}',
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      processData: false,
      contentType: false,
      cache: false,
      success: function(response) {
        if (response.code === 200) {
          var tempData = [];
          var tempName = '';
          var tempValue = 0;

          for (var key in response.data) {
            if (response.data[key[0]].cr_0 != undefined) {
              tempName = 'Answered'; tempValue = response.data[key[0]].cr_0;
            }
            else if (response.data[key[0]].cr_1 != undefined) {
              tempName = 'No Answer'; tempValue = response.data[key[0]].cr_1;
            }
            else if (response.data[key[0]].cr_2 != undefined) {
              tempName = 'Busy'; tempValue = response.data[key[0]].cr_2;
            }
            else if (response.data[key[0]].cr_3 != undefined) {
              tempName = 'Failed'; tempValue = response.data[key[0]].cr_3;
            }

            tempData.push({
              value: tempValue,
              name: tempName
            });
          };

          callsChart.setOption({
            series: [{
              data: tempData
            }]
          });
        }
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