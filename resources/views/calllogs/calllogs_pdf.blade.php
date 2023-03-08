<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

  <!-- Google Fonts -->
  {{-- <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">
 --}}

  <!-- Template Main CSS File -->
  <link href="./css/style.css" rel="stylesheet">
  <style>
    body {
      /*font-family: "Verdana", sans-serif;*/
      background: white;
      color: #444444;
      font-size:10px !important;
    }

    #main {
      margin-top: 0px;
      padding: 20px 30px;
    }

    #export-campaign-header {
      font-size: 0.9em;
    }

    @page {
      margin: 0;
      padding: 0;
      background-color: white;
    }
    .table-data{
      border-collapse: collapse !important;
    }
    .table-data tr th,
    .table-data tr td{
      border:.5px solid #000;
      padding:2px 4px;
    }
  </style>
</head>

<body>
<main id="main" class="main">
    <div class="row">
      <div class="col-lg-12">

        <h1 style="font-size: 18px !important"><strong>Call Logs Report</strong></h1>

        @if($startdate)
          <p style="margin:0"><strong>Date Range : {{ $startdate.' to '.$enddate }}</strong></p>
        @endif

        @if($campaign)
          <p style="margin:0"><strong>Campaign : {{ $campaign->name }}</strong></p>
        @endif
        <br>
        <div class="col-md-12 mt-4" style="padding-top:0.85em;">
          <table class="table table-hover table-data" style="width:100%; font-size:0.73em;">
            <thead>
              <tr>
                <th scope="col">Call Date</th>
                <th scope="col">Account ID</th>
                <th scope="col">Phone</th>
                <th scope="col">Dial</th>
                <th scope="col">Connect</th>
                <th scope="col">Disconnect</th>
                <th scope="col">Duration</th>
                <th scope="col">Call Response</th>
              </tr>
            </thead>
            <tbody>
              @foreach($data as $row)
                <tr>
                  <td>{{ $row['call_dial'] }}</td>
                  <td>{{ $row['account_id'] }}</td>
                  <td>{{ $row['phone'] }}</td>
                  <td>{{ $row['dial'] }}</td>
                  <td>{{ $row['connect'] }}</td>
                  <td>{{ $row['disconnect'] }}</td>
                  <td>{{ $row['duration'] }}</td>
                  <td>{{ $row['response'] }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

      </div>
    </div>
</main>

</body>
</html>