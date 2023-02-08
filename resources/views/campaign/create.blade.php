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
          <form class="g-3 needs-validation" method="POST" action="{{ route('campaign.store') }}" enctype="multipart/form-data">
            <div class="card-body">
              <h5 class="card-title">
                  Add Campaign
              </h5>

              <div class="col-md-12">
                <div class="form-floating">
                  <input type="text" class="form-control" id="input-campaign-name" name="name" minlength="5" maxlength="30" placeholder="Name" required>
                  <label for="input-user-name">Campaign Name</label>
                </div>
              </div>

              <div class="col-md-12 mt-3">
                <div class="form-floating">
                  <div>
                    <input class="form-control" type="file" id="input-campaign-excel-file" accept=".xls, .xlsx" required>
                  </div>
                </div>
              </div>

              <div id="row-preview-contact-data-container" class="col-md-12 mt-4">
                <table id="table-preview-contact-data-container" class="table table-hover">
                  <thead>
                    <tr>
                      {{-- <th scope="col">#</th> --}}
                      <th scope="col">Account ID</th>
                      <th scope="col">Name</th>
                      <th scope="col">Phone</th>
                      <th scope="col">Bill Date</th>
                      <th scope="col">Due Date</th>
                      <th scope="col">Nominal</th>
                    </tr>
                  </thead>
                </table>
              </div>

              <div class="col-md-12 mt-4">
                <button type="button" class="btn btn-secondary btn-back">Close</button>
                <button type="submit" class="btn btn-primary" id="btn-submit-create-campaign">Save</button>
                {{ csrf_field() }}
                <input type="hidden" id="input-campaign-rows" name="input_campaign_rows" value="" required>
              </div>
            </div>
          </form>
        </div>

      </div>
    </div>
  </section>
</main>

@push('javascript')
<script type="text/javascript">
var previewContactDataContainer = '';
var contactDataContainer = '';

  $(document).ready(function() {
    preparePreviewContactTable();

    $('#input-campaign-excel-file').on("change", function(e) {
      handleFileAsync(e);
    });
    $('.btn-back').click(function(e) {
      history.back();
    });
    $('#input-campaign-excel-file').val('');
    $('#input-campaign-rows').val('');
  });

  function preparePreviewContactTable() {
    previewContactDataContainer = $('#table-preview-contact-data-container').DataTable({
      columns: [
        { data: 'account_id' },
        { data: 'name' },
        { data: 'phone' },
        { data: 'bill_date' },
        { data: 'due_date' },
        { data: 'nominal' }
      ],
      columnDefs: [
        {
          targets: 5,
          className: 'dt-body-right'
        }
      ]
    });
  }

  async function handleFileAsync(e) {
    const file = e.target.files[0];
    const data = await file.arrayBuffer();
    const workbook = XLSX.read(data);
    const tempRows = [];

    const dataRows = XLSX.utils.sheet_to_json(
      workbook.Sheets[workbook.SheetNames[0]],
      { header:1 }
    );
    dataRows.shift();

    dataRows.map((v, k) => {
      tempRows.push({
        'account_id': v[0],
        'name': v[1],
        'phone': v[2],
        'bill_date': v[3],
        'due_date': v[4],
        'nominal': v[5]
      });
    });

    previewContactDataContainer.clear();
    previewContactDataContainer.rows.add(tempRows);
    previewContactDataContainer.draw();
    $('#input-campaign-rows').val(JSON.stringify(tempRows));
  }
</script>
@endpush

@include('layouts.include_page_footer')
@include('layouts.include_js')
