@include('layouts.include_page_header')
@include('layouts.include_sidebar')

<main id="main" class="main">
  <div class="pagetitle">
    <h1>Users</h1>
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
              <a href="{{ route('user.create') }}" id="btn-add-user" class="btn btn-primary btn-user-action">
                Add User
              </a>
            </h5>
            
            <table id="table-user-list-container" class="table table-hover">
              <thead>
                <tr>
                  <th scope="col">#</th>
                  <th scope="col">Name</th>
                  <th scope="col">Username</th>
                  <th scope="col">Last Login</th>
                  <th scope="col">Last IP Addr</th>
                  <th scope="col">Added By</th>
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
  $(document).ready(function() {
    prepareUserListTable();
    // getUserList(userListContainer);
  });

  var userListContainer = '';
  var userList = [];

  // function prepareUserListTable() {
  //   userListContainer = $('#table-user-list-container').DataTable({
  //     columns: [
  //       { data: null },
  //       { data: 'name' },
  //       { data: 'username' },
  //       { data: 'last_login' },
  //       { data: 'last_ip_address' },
  //       { data: 'added_by' }
  //     ],
  //     columnDefs: [
  //       {
  //         targets: 0,
  //         render: function(data, type, row, meta) {
  //           return ++meta.row + '.';
  //         }
  //       },
  //       {
  //         targets: 6,
  //         data: null,
  //         render: function(data, type, row, meta) {
  //           userList['_' + row.username] = row;

  //           var tempContent = '';
  //           tempContent += '<a href="{{ route('user.resetpass') }}/_' + row.username + '" class="btn btn-sm btn-info">Password</a>&nbsp;';
  //           tempContent += '<a href="{{ route('user.edit') }}/_' + row.username + '" class="btn btn-sm btn-warning">Edit</button>&nbsp;';
  //           tempContent += '<a href="{{ route('user.delete') }}/_' + row.username + '" class="btn btn-sm btn-danger">Delete</button>';
  //           return tempContent;
  //         }
  //       },
  //     ]
  //   });
  // };

  // function getUserList(dataContainer) {
  //   $.ajax({
  //     type: 'GET',
  //     url: '{{ route('user.list') }}',
  //     headers: {
  //       'X-CSRF-TOKEN': '{{ csrf_token() }}'
  //     },
  //     processData: false,
  //     contentType: false,
  //     cache: false,
  //     success: function(response) {
  //       dataContainer.clear();
  //       dataContainer.rows.add(response.data);
  //       dataContainer.draw();
  //     },
  //     error: function(error) {
  //       console.log(error.responseText);
  //     }
  //   })
  //   .always(function() {
  //   });
  // };

  function prepareUserListTable() {
    userListContainer = $('#table-user-list-container').DataTable({
      processing: true,
      serviceSide: true,
      ajax: {
        url: '{{ route('user.list.ajax') }}',
        type: 'POST',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
      },
      columns: [
        { data: null },
        { data: 'name' },
        { data: 'username' },
        { data: 'last_login' },
        { data: 'last_ip_address' },
        { data: 'added_by' }
      ],
      columnDefs: [
        {
          targets: 0,
          render: function(data, type, row, meta) {
            return ++meta.row + '.';
          }
        },
        {
          targets: 6,
          orderable: false,
          data: null,
          render: function(data, type, row, meta) {
            userList['_' + row.username] = row;

            var tempContent = '';
            tempContent += '<a href="{{ route('user.resetpass') }}/_' + row.username + '" class="btn btn-sm btn-info">Password</a>&nbsp;';
            tempContent += '<a href="{{ route('user.edit') }}/_' + row.username + '" class="btn btn-sm btn-warning">Edit</a>&nbsp;';
            tempContent += '<a href="{{ route('user.delete') }}/_' + row.username + '" class="btn btn-sm btn-danger">Delete</a>';
            return tempContent;
          }
        },
      ]
    });
  }
</script>
@endpush

@include('layouts.include_page_footer')
@include('layouts.include_js')