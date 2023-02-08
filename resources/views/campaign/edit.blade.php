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
            <form class="g-3 needs-validation" method="POST" action="{{ route('campaign.update') }}" enctype="multipart/form-data">
              <h5 class="card-title">
                  Edit Campaign
              </h5>

              <div class="col-md-12">
                <div class="form-floating">
                  <input type="text" class="form-control" id="edit-campaign-name" name="name" minlength="4" maxlength="50" placeholder="Name" value="{{ $campaign->name or '' }}" required>
                  <label for="edit-campaign-name">Campaign Name</label>
                </div>
              </div>

              <div class="col-md-12 mt-3">
                <div class="row form-floating">
                  <div class="col-md-9">
                    <input class="form-control" type="file" id="edit-campaign-excel-file" accept=".xls, .xlsx">
                  </div>

                  <div class="col-md-3">
                    <button type="button" class="btn btn-outline-success btn-edit-action disabled" id="btn-edit-contact-action-merge" data-btn-edit-action="merge">Merge</button>
                    <button type="button" class="btn btn-outline-warning btn-edit-action disabled" id="btn-edit-contact-action-replace" data-btn-edit-action="replace">Replace</button>
                  </div>
                </div>
              </div>

              <div class="col-md-12 mt-4">
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
                <button type="button" class="btn btn-secondary btn-back">Cancel</button>
                <button type="submit" class="btn btn-outline-primary disabled" id="btn-submit-edit-campaign">Save</button>
                <input type="hidden" id="edit-campaign-rows" name="rows" value="">
                <input type="hidden" id="edit-campaign-key" name="campaign" value="_{{ $campaign->unique_key }}">
                <input type="hidden" id="edit-campaign-action" name="action" value="">
                {{ csrf_field() }}
                <input type="hidden" name="_method" value="PUT">
              </div>
            </form>
          </div>
        </div>

      </div>
    </div>
  </section>
</main>

@push('javascript')
<script type="text/javascript">
var previewContactDataContainer = '';
var previousCampaignName = '{{ $campaign->name }}';
var previousContactData= [];
var editAction = '';

  $(document).ready(function() {
    preparePreviewContactTable();
    // getContactList();

    $('#edit-campaign-rows').val('');
    $('#edit-campaign-action').val('');
    $('#edit-campaign-excel-file').val('');
    $('#edit-campaign-excel-file').on("change", function(e) {
      handleFileAsync(e);
    });

    $('#edit-campaign-name').keyup(function() {
      var newName = $(this).val().trim();
      if (previousCampaignName.localeCompare(newName) != 0) {
        if (editAction === '') {
          $('#btn-submit-edit-campaign').removeClass('btn-outline-primary disabled').addClass('btn-primary');
        }
      }
      else {
        if ((editAction === '') && !$('#btn-submit-edit-campaign').hasClass('disabled')) {
          $('#btn-submit-edit-campaign').removeClass('btn-primary').addClass('btn-outline-primary disabled');
        }
      }
    });

    $('.btn-back').click(function(e) {
      history.back();
    });

    $('#btn-edit-contact-action-merge').click(function(e) {
      $('#edit-campaign-action').val('merge');
      changeEditActionButtons();
    });

    $('#btn-edit-contact-action-replace').click(function(e) {
      $('#edit-campaign-action').val('replace');
      changeEditActionButtons();
    });
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

  function getContactList() {
    $.ajax({
      method: 'GET',
      url: "{{ route('contact.list') }}/_{{ $campaign->unique_key}}",
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      processData: false,
      contentType: false,
      cache: false,
      success: function(response) {
        previousContactData = response.data;

        previewContactDataContainer.clear();
        previewContactDataContainer.rows.add(response.data);
        previewContactDataContainer.draw();
      },
      error: function(error) {
        console.log(error.responseText);
      }
    })
    .always(function() {
    });
  }

  async function handleFileAsync(e) {
    const file = e.target.files[0];
    const data = await file.arrayBuffer();
    const workbook = XLSX.read(data);
    const dataRows = XLSX.utils.sheet_to_json(
      workbook.Sheets[workbook.SheetNames[0]],
      { header:1 }
    );
    dataRows.shift();

    const tempRows = [];
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

    $('#edit-campaign-rows').val(JSON.stringify(tempRows));
    $('#btn-edit-contact-action-merge').removeClass('disabled');
    $('#btn-edit-contact-action-replace').removeClass('disabled');
  }

  function changeEditActionButtons() {
    editAction = $('#edit-campaign-action').val();

    if (editAction === 'merge') {
      $('#btn-edit-contact-action-merge').removeClass('btn-outline-success').addClass('btn-success');
      $('#btn-edit-contact-action-replace').removeClass('btn-warning').addClass('btn-outline-warning');
      $('#btn-submit-edit-campaign').removeClass('btn-outline-primary disabled').addClass('btn-primary');
    }
    else if (editAction === 'replace') {
      $('#btn-edit-contact-action-merge').removeClass('btn-success').addClass('btn-outline-success');
      $('#btn-edit-contact-action-replace').removeClass('btn-outline-warning').addClass('btn-warning');
      $('#btn-submit-edit-campaign').removeClass('btn-outline-primary disabled').addClass('btn-primary');
    }
  }
</script>
@endpush

@include('layouts.include_page_footer')
@include('layouts.include_js')
