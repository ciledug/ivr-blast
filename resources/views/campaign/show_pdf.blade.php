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
      font-family: "Open Sans", sans-serif;
      background: white;
      color: #444444;
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
  </style>
</head>

<body>

<main id="main" class="main">
    <div class="row">
      <div class="col-lg-12">

        <h5 class="card-title"></h5>

        <div id="export-campaign-header" class="col-md-12 mt-4">
          <table class="table table-hover" style="width:100%;">
            <tr>
              <td style="width:50%; line-height:0.85em;"><span style="font-weight:bold">Name</span><br>{{ $campaign->name or '' }}</td>
              <td style="width:50%; line-height:0.85em;"><span style="font-weight:bold">Date Started</span><br>{{ $campaign->started or '-' }}</td>
            </tr>
            <tr>
              <td style="width:50%; padding-top:0.85em; line-height:0.85em;"><span style="font-weight:bold">Total Data</span><br>{{ $campaign->total_data or '0' }}</td>
              <td style="width:50%; padding-top:0.85em; line-height:0.85em;"><span style="font-weight:bold">Date Finished</span><br>{{ $campaign->finished or '-' }}</td>
            </tr>
            <tr>
              <td style="width:50%; padding-top:0.85em; line-height:0.85em;"><span style="font-weight:bold">Status</span><br>{{ $campaign->status or '-' }}</td>
              <td style="width:50%; padding-top:0.85em; line-height:0.85em;"><span style="font-weight:bold">Total Calls</span><br>{{ $campaign->total_calls or '0' }}</td>
            </tr>
            <tr>
              <td style="width:50%; padding-top:0.85em; line-height:0.85em;"><span style="font-weight:bold">Created Date</span><br>{{ $campaign->created_at or '-' }}</td>
              <td style="width:50%; padding-top:0.85em; line-height:0.85em;"><span style="font-weight:bold">Success Calls</span><br>{{ $campaign->success or '0' }}</td>
            </tr>
            <tr>
              <td style="width:50%; padding-top:0.85em; line-height:0.85em;"><span style="font-weight:bold">Campaign Progress (%)</span><br>{{ $campaign->progress or '0' }}</td>
              <td style="width:50%; padding-top:0.85em; line-height:0.85em;"><span style="font-weight:bold">Failed Calls</span><br>{{ $campaign->failed or '0' }}</td>
            </tr>
          </table>
        </div>

        <hr />

        <div class="col-md-12 mt-4" style="padding-top:0.85em;">
          <table class="table table-hover" style="width:100%; font-size:0.73em;">
            <thead>
              <tr>
                <th scope="col">#</th>
                <th scope="col">Account ID</th>
                <th scope="col">Name</th>
                <th scope="col">Phone</th>
                <th scope="col">Bill<br>Date</th>
                <th scope="col">Due<br>Date</th>
                <th scope="col">Nominal<br>(Rp.)</th>
                <th scope="col">Call<br>Date</th>
                <th scope="col">Call<br>Response</th>
                <th scope="col">Total<br>Calls</th>
              </tr>

              @foreach($contacts AS $contact)
              <tr>
                <td>{{ $loop->iteration }}.</td>
                <td>{{ $contact->ACCOUNT_ID }}</td>
                <td>{{ $contact->CONTACT_NAME }}</td>
                <td>{{ $contact->CONTACT_PHONE }}</td>
                <td>{{ $contact->BILL_DATE }}</td>
                <td>{{ $contact->DUE_DATE }}</td>
                <td style="text-align:right;">{{ number_format($contact->NOMINAL, 0, ',', '.') }}</td>
                <td>{{ $contact->CALL_DATE }}</td>
                <td>{{ $contact->CALL_RESPONSE }}</td>
                <td style="text-align:right;">{{ number_format($contact->TOTAL_CALLS, 0, ',', '.') }}</td>
              </tr>
              @endforeach

            </thead>
          </table>
        </div>

      </div>
    </div>
</main>

</body>
</html>