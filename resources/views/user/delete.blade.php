@include('layouts.include_page_header')
@include('layouts.include_sidebar')

<main id="main" class="main">
  <div class="pagetitle">
    <h1>Users</h1>
    <!--
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item">Create</li>
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
                Delete User
            </h5>
            
            <form id="form-delete-user" class="row g-3 needs-validation" method="POST" action="{{ route('user.destroy') }}" enctype="multipart/form-data" novalidate>
              {{ csrf_field() }}

              <div class="col-md-12">
                <div class="form-floating">
                    <input type="text" class="form-control disabled" id="input-user-name" name="name" value="{{ $user->name or '' }}" disabled>
                    <label for="input-user-name">Name</label>
                </div>
              </div>

              <div class="col-md-12 mt-3">
                <div class="form-floating">
                    <input type="email" class="form-control disabled" id="input-user-email" name="email" value="{{ $user->email or '' }}" disabled>
                    <label for="input-user-email">Email (optional)</label>
                </div>
              </div>

              <div class="col-md-12 mt-3">
                <div class="form-floating">
                    <input type="text" class="form-control disabled" id="input-user-username" name="username" value="{{ $user->username or '' }}" disabled>
                    <label for="input-user-username">Username</label>
                </div>
              </div>
              
              <div class="col-md-12 mt-4">
                <button type="button" id="btn-cancel-delete-user" class="btn btn-secondary btn-back">Cancel</button>
                <button type="submit" id="btn-submit-delete-user" class="btn btn-danger">Delete</button>
                <input type="hidden" id="input-delete-user" name="user" value="_{{ $user->username or ''}}">
                <input type="hidden" name="_method" value="DELETE">
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
  $(document).ready(function(e) {
    $('.btn-back').click(function(e) {
      history.back();
    });

    $('#form-delete-user').submit(function() {
      $('.btn-back').addClass('disabled');
      $('#btn-submit-delete-user').addClass('disabled');
    });
  });
</script>
@endpush

@include('layouts.include_page_footer')
@include('layouts.include_js')