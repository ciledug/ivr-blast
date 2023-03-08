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
              <a href="{{ route('users.create') }}" id="btn-add-user" class="btn btn-primary btn-user-action">
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

              <tbody>
                @if($users->count() > 0)
                  @foreach($users AS $keyUser => $valueUser)
                    <tr>
                      <td class="text-end">{{ $loop->index + 1 }}.</td>
                      <td>{{ $valueUser->name }}</td>
                      <td>{{ $valueUser->username }}</td>
                      <td>{{ $valueUser->last_login }}</td>
                      <td>{{ $valueUser->last_ip_address }}</td>
                      <td>{{ $valueUser->added_by or '-' }}</td>
                      <td>
                        <a href="{{ route('users.resetpass', ['id' => $valueUser->id]) }}" class="btn btn-sm btn-info">Password</a>&nbsp;
                        <a href="{{ route('users.edit', ['id' => $valueUser->id ]) }}" class="btn btn-sm btn-warning">Edit</a>&nbsp;
                        <a href="{{ route('users.delete', ['id' => $valueUser->id ]) }}" class="btn btn-sm btn-danger">Delete</a>
                      </td>
                    </tr>
                  @endforeach
                @else
                  <tr>
                    <td colspan="7" class="text-center">There's no users data</td>
                  </tr>
                @endif
              </tbody>
            </table>

            {{ $users->links() }}

          </div>
        </div>

      </div>
    </div>
  </section>
</main>

@push('javascript')
<script type="text/javascript">
  $(document).ready(function() {
  });
</script>
@endpush

@include('layouts.include_page_footer')
@include('layouts.include_js')