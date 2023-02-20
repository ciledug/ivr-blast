@include('layouts.include_page_header')
@include('layouts.include_sidebar')

<main id="main" class="main">
  <div class="pagetitle">
    <h1>Dashboard</h1>
    <!--
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.html">Home</a></li>
        <li class="breadcrumb-item active">Dashboard</li>
      </ol>
    </nav>
    -->
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
                <tbody>
                  @foreach ($campaigns AS $campaign)
                  <tr>
                    <td scope="col"></td>
                    <td scope="col">{{ $campaign['name'] }}</td>
                    <td scope="col">{{ $campaign['progress'] }}</td>
                  </tr>
                  @endforeach
                </tbody>
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
  });

  function prepareTableContainer() {
    dataTableContainer = $('#table-running-campaign-list-container').DataTable({
      processing: true,
      lengthMenu: [5, 10, 15, 20, 50, 100],
      pageLength: 10,
      responsive: true,
      columns: [
        { data: null },
        { data: 'name' },
        { data: 'progress' },
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
          targets: 2,
          className: 'dt-body-right',
        }
      ],
    });
  };

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
        data: [
          { value: {{ $answered }}, name: 'Answered' },
          { value: {{ $noanswer }}, name: 'No Answer' },
          { value: {{ $busy }}, name: 'Busy' },
          { value: {{ $failed }}, name: 'Failed' },
        ]
      }]
    });
  };
</script>
@endpush

@include('layouts.include_page_footer')
@include('layouts.include_js')