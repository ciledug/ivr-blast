<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">


  <!-- Template Main CSS File -->
  <link href="./css/style.css" rel="stylesheet">
  <style>
    body {
      font-family: "Verdana", sans-serif;
      background: white;
      color: #444444;
      font-size:10px !important;
    }

    #main {
      margin-top: 0px !important;
    }

    #export-campaign-header {
      font-size: 0.9em;
    }

    @page {
      margin: 40px 40px !important;
      padding: 0 !important;
      background-color: white;
    }

    .table-data{
      border-collapse: collapse !important;
    }

    .table-data tr th,
    .table-data tr td {
      border:.5px solid #000;
      padding:2px 4px;
    }

    .text-center {
      text-align: center;
    }

    .text-right {
      text-align: right;
    }

    .bg-primary {
      background-color: #6776f4;
      color: white;
    }

    .bg-info {
      background-color: #f0f8fa;
    }
  </style>
</head>

<body>

  {{-- @php dd($campaign) @endphp --}}

<main id="main" class="main">
    <div class="row">
      <div class="col-lg-12">

        <div id="export-campaign-header">
          <table class="table table-hover" style="width:100%;">
            <tr>
              <td style="width:50%;"><span style="font-weight:bold">Name</span><br>{{ $campaign[0]->camp_name or '' }}</td>
              <td style="width:50%;"><span style="font-weight:bold">Date Started</span><br>{{ $campaignInfo->started ? date('d/m/Y - H:i', strtotime($campaignInfo->started)) : '-' }}</td>
            </tr>
            <tr>
              <td style="width:50%; padding-top:0.85em;"><span style="font-weight:bold">Total Data</span><br>{{ $campaign[0]->camp_total_data or '0' }}</td>
              <td style="width:50%; padding-top:0.85em;"><span style="font-weight:bold">Date Finished</span><br>{{ $campaignInfo->finished ? date('d/m/Y - H:i', strtotime($campaignInfo->finished)) : '-' }}</td>
            </tr>
            <tr>
              <td style="width:50%; padding-top:0.85em;">
                <span style="font-weight:bold">Status</span><br>
                @php
                switch ($campaign[0]->camp_status) {
                  case 0: echo 'Ready'; break;
                  case 1: echo 'Running'; break;
                  case 2: echo 'Paused'; break;
                  case 3: echo 'Finished'; break;
                  default: echo '-'; break;
                }
                @endphp
              </td>
              <td style="width:50%; padding-top:0.85em;"><span style="font-weight:bold">Total Calls</span><br>{{ $campaignInfo->total_calls or '0' }}</td>
            </tr>
            <tr>
              <td style="width:50%; padding-top:0.85em;"><span style="font-weight:bold">Created Date</span><br>{{ date('d/m/Y - H:i', strtotime($campaign[0]->camp_created_at)) }}</td>
              <td style="width:50%; padding-top:0.85em;"><span style="font-weight:bold">Success Calls</span><br>{{ $campaignInfo->success or '0' }}</td>
            </tr>
            <tr>
              <td style="width:50%; padding-top:0.85em;"><span style="font-weight:bold">Campaign Progress (%)</span><br>{{ $progress or '0' }}</td>
              <td style="width:50%; padding-top:0.85em;"><span style="font-weight:bold">Failed Calls</span><br>{{ $campaignInfo->failed or '0' }}</td>
            </tr>
          </table>
        </div>

        <hr style="border:none;" />

        <div style="padding-top:0.85em;">
          @php $headerName = ''; @endphp
          
          <table class="table table-hover table-data" style="width:100%; font-size:0.73em;">
            <thead>
              <tr class="bg-primary">
                <th scope="col" class="text-center">#</th>
                @foreach ($campaign AS $keyCamp => $valCamp)
                <th scope="col">{{ strtoupper($valCamp->header_name) }}</th>
                @endforeach

                <th scope="col">CALL DATE</th>
                <th scope="col">CALL RESPONSE</th>
                <th scope="col">TOTAL CALLS</th>
              </tr>
            </thead>

            <tbody>
              @foreach($contacts->chunk(300) AS $datacontact)
                @foreach($datacontact AS $contact)

                @if ($loop->iteration % 2 == 0)
                <tr class="bg-info">
                @else
                <tr>
                @endif

                  <td class="text-right">{{ $loop->iteration }}.</td>
                  @php
                  foreach ($campaign AS $keyCamp => $valCamp) {
                    $headerName = strtolower($valCamp->header_name);
                    switch ($valCamp->header_type) {
                      case 'numeric':
                        echo '<td class="text-right">' . number_format($contact->$headerName, 0, ',', '.');
                        break;
                      case 'handphone':
                        echo '<td>' . substr($contact->$headerName, 0, 4) . 'xxxxxx' . substr($contact->$headerName, strlen($contact->$headerName) - 3);
                        break;
                      default:
                        echo '<td>' . $contact->$headerName;
                        break;
                    }
                    echo '</td>';
                  }
                  @endphp

                  <td>{{ $contact->call_dial ? date('d/m/Y - H:i', strtotime($contact->call_dial)) : '' }}</td>
                  <td>{{ $contact->call_response ? $contact->call_response : '' }}</td>
                  <td class="text-right" style="padding-right:10px;">{{ number_format($contact->total_calls, 0, ',', '.') }}</td>

                </tr>
                @endforeach
              @endforeach
            </tbody>
            
          </table>
        </div>

      </div>
    </div>
</main>

</body>
</html>