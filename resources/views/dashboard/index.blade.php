@include('layouts.include_page_header')
@include('layouts.include_sidebar')

<main id="main" class="main">
  <div class="pagetitle">
    <h1>Dashboard</h1>
  </div>
  
  <section class="section dashboard">
    <div class="row">
      
      <!-- Left side columns -->
      <div class="col-lg-8">
        <div class="row">
          <div class="card">
            <div class="card-body" style="min-height: 465px;">
              <h5 class="card-title">Running Campaigns</h5>

              <table id="table-running-campaign-list-container" class="table">
                <thead>
                  <tr>
                    <th scope="col" width="40px">No</th>
                    <th scope="col">Name</th>
                    <th scope="col" width="100px">Called</th>
                    <th scope="col" width="100px">Remaining</th>
                    <th scope="col" width="100px">Progress</th>
                    <th scope="col" width="80px">Detail</th>
                  </tr>
                </thead>
                <tbody>
                  {{-- @if($campaigns->count() > 0)
                    @foreach ($campaigns AS $campaign)
                    <tr data-row-index="{{ $campaign->id }}">
                      <td>{{ $loop->index + 1 }}.</td>
                      <td>{{ $campaign->name }}</td>
                      <td id="called-data">{{ number_format($campaign->data_called,0,'.','.') }}</td>
                      <td id="remaining-data">{{ number_format($campaign->data_remaining,0,'.','.') }}</td>
                      <td id="progress-data">{{ round(($campaign->data_called / ($campaign->data_called + $campaign->data_remaining)) * 100, 2) }} %</td>
                      <td><a href="{{ route('campaign.show') }}/_{{ $campaign->unique_key }}" class="btn btn-sm btn-primary btn-modal-spinner">Detail</a></td>
                    </tr>
                    @endforeach
                  @else --}}
                    <tr>
                      <td colspan="6" class="text-center">There's no running campaign today</td>
                    </tr>
                  {{-- @endif --}}
                </tbody>
              </table>

              {{-- {{ $campaigns->links() }} --}}
            </div>
          </div>
        </div>
      </div>
      <!-- End Left side columns -->
      
      <!-- Right side columns -->
      <div class="col-lg-4">
        <div class="card">
          <div class="card-body pb-0"style="min-height: 465px;">
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
<script src="{{ asset('js/jquery.sse.min.js') }}"></script>
<script type="text/javascript">
  $(document).ready(function() {
    $('.btn-modal-spinner').click(function(e) {
      $('#modal-spinner').modal('show');
    });

    const callsChart = echarts.init(document.getElementById('chart-total-calls'), {width:'100%'});
    callsChart.setOption({
      tooltip: {
        trigger: 'item',
        textStyle:{
          fontSize:12
        }
      },
      legend: {
        orient: 'horizontal',
        bottom: '5%',
        itemWidth: 14,
        itemGap: 20
      },
      color:['#91CC75', '#EE6666', '#FAC858', '#5470C6'],
      series: [
        {
          type: 'pie',
          height: 300,
          width:'100%',
          radius: '60%',
          label: {
            show: true,
            position:'outside',
            formatter: '{c}',
            fontWeight: 600
          },
          labelLine:{
            show: true,
            length: 5,
            lineStyle:{
              width: 2
            }
          },
          data: [
            { value: null, name: 'Answered' },
            { value: null, name: 'No Answer' },
            { value: null, name: 'Busy' },
            { value: null, name: 'Failed' },
          ],
          emphasis: {
            itemStyle: {
              shadowBlur: 10,
              shadowOffsetX: 10,
              shadowColor: 'rgba(0, 0, 0, 0.5)'
            }
          }
        }
      ]
    });
  });

  var sseDashboard = $.SSE('{{ route('dashboard.stream') }}', {
      onMessage: function(e){ 
        let data = JSON.parse(e.data);
        
        if(data.status){
          // running campaign
          let table = document.getElementById('table-running-campaign-list-container');
          let rows = '';
          for(let i in data.campaigns){
            let campaign = data.campaigns[i];
            
            if(data.campaigns.length > 0){
              let no = ++i;
              let progress = eval((campaign.data_called / (campaign.data_called + campaign.data_remaining)) * 100).toFixed(2);
              
              if(progress.split('.')[1] == '00'){
                progress = progress.split('.')[0];
              }

              rows += '<tr>\
                        <td>'+ no +'</td>\
                        <td>'+ campaign.name +'</td>\
                        <td>'+ new Intl.NumberFormat('id-ID').format(campaign.data_called) +'</td>\
                        <td>'+ new Intl.NumberFormat('id-ID').format(campaign.data_remaining) +'</td>\
                        <td>'+ progress +' %</td>\
                        <td><a href="{{ route('campaign.show') }}/_'+campaign.unique_key+'" class="btn btn-sm btn-primary btn-modal-spinner">Detail</a></td>\
                      </tr>';
            }
          }

          if(rows.length > 0){
            table.querySelector('tbody').innerHTML = rows;
          }



          // chart calls
          const chartCallsStream = echarts.getInstanceByDom(document.getElementById('chart-total-calls'));
          chartCallsStream.setOption({
            series: [{
              data: [
                { value: data.calls.call_answered, name: 'Answered' },
                { value: data.calls.call_noanswer, name: 'No Answer' },
                { value: data.calls.call_busy, name: 'Busy' },
                { value: data.calls.call_failed, name: 'Failed' },
              ]
            }]
          });
        }
      }
  });

  sseDashboard.start();
</script>
@endpush

@include('layouts.include_page_footer')
@include('layouts.include_js')