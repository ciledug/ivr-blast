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
                Add User
            </h5>
            
            <form id="form-create-user" class="row g-3 needs-validation" method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data" novalidate>
              {{ csrf_field() }}

              <div class="col-md-12">
                <div class="form-floating">
                    <input type="text" class="form-control" id="input-user-name" name="name" minlength="5" maxlength="30" placeholder="Name" value="{{ old('name') }}" required>
                    <label for="input-user-name">Name (min. 5 chars)</label>
                </div>
              </div>

              <div class="col-md-12 mt-3">
                <div class="form-floating">
                    <input type="email" class="form-control" id="input-user-email" name="email" minlength="10" maxlength="50" placeholder="Email (optional)" value="{{ old('email') }}">
                    <label for="input-user-email">Email (optional)</label>
                </div>
                @if ($errors->has('email'))
                <div class="invalid-feedback" style="display:block">
                  {{ $errors->first('email') }}
                </div>
                @endif
              </div>

              <div class="col-md-12 mt-3">
                <div class="form-floating">
                    <input type="text" class="form-control" id="input-user-username" name="username" minlength="5" maxlength="15" placeholder="Username" value="{{ old('username') }}" required>
                    <label for="input-user-username">Username (min. 5 chars)</label>
                </div>
                @if ($errors->has('username'))
                <div class="invalid-feedback" style="display:block">
                  {{ $errors->first('username') }}
                </div>
                @endif
              </div>

              <div class="col-md-12 mt-3" id="row-input-user-password">
                <div class="form-floating">
                    <input type="password" class="form-control" id="input-user-password" name="password" minlength="6" maxlength="15" placeholder="Password (min. 6 chars)" required>
                    <label for="input-user-password">Password (min. 6 chars)</label>
                </div>
              </div>

              <div class="col-md-12 mt-3" id="row-input-user-confirm-password">
                <div class="form-floating">
                    <input type="password" class="form-control" id="input-user-confirm-password" name="password_confirmation" minlength="6" maxlength="15" placeholder="Confirm Password (min. 6 chars)" required>
                    <label for="input-user-confirm-password">Confirm Password (min. 6 chars)</label>
                </div>
                @if ($errors->has('password'))
                <div class="invalid-feedback" style="display:block">
                  {{ $errors->first('password') }}
                </div>
                @endif
              </div>
              
              <div class="col-md-12 mt-4">
                <button type="button" id="btn-cancel-create-user" class="btn btn-secondary btn-back">Cancel</button>
                <button type="submit" id="btn-submit-create-user" class="btn btn-primary">Save</button>
                &nbsp;<div id="submit-spinner" class="spinner-border spinner-border-sm text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
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
    $('#submit-spinner').hide();

    $('.btn-back').click(function(e) {
      history.back();
    });

    $('#form-create-user').submit(function() {
      var isOkProceed = true;

      if (
        ($('#input-user-name').val().length < 5) || ($('#input-user-username').val().length < 5) ||
        ($('#input-user-password').val().length < 6) || ($('#input-user-confirm-password').val().length < 6)
      ) {
        isOkProceed = false;
      }

      if (!isOkProceed) return isOkProceed;
      
      $('.btn-back').addClass('disabled');
      $('#btn-submit-create-user').addClass('disabled');
      $('#submit-spinner').show();
    });
  });
</script>
@endpush

@include('layouts.include_page_footer')
@include('layouts.include_js')