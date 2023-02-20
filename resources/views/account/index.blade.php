@include('layouts.include_page_header')
@include('layouts.include_sidebar')

<main id="main" class="main">
  <div class="pagetitle">
    <h1>My Account</h1>
  </div>
  
  <section class="section">
    <div class="row">

      <div class="col-lg-6">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Profile</h5>

            @if (session('profile_changed_ok'))
            <div id="alert-password-change-success" class="alert alert-success alert-dismissible fade show" role="alert">
              <i class="bi bi-check-circle me-1"></i>
              {{ session('profile_changed_ok') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif
            
            <form id="form-account-profile" class="row g-3 needs-validation" method="POST" action="{{ route('account.update') }}" novalidate>
              {{ csrf_field() }}
              
              <div class="col-md-12">
                <label for="input-profile-name" class="form-label">Name (min. 5 chars)</label>
                <input type="text" class="form-control" id="input-profile-name" name="name" value="{{ Auth::user()->name }}" minlength="5" maxlength="30" placeholder="Full name" required>
                <div class="invalid-feedback">
                  Name failed!
                </div>
              </div>

              <div class="col-md-12">
                <label for="input-profile-email" class="form-label">Email (optional)</label>
                <input type="email" class="form-control" id="input-profile-email" name="email" value="{{ Auth::user()->email }}" minlength="10" maxlength="50" placeholder="Email">
                @if ($errors->has('email'))
                <div class="invalid-feedback" style="display:block;">
                  {{ $errors->first('email') }}
                </div>
                @endif
              </div>

              <div class="col-md-12">
                <label for="input-profile-username" class="form-label">Username (min. 5 chars)</label>
                <input type="text" class="form-control" id="input-profile-username" name="username" value="{{ Auth::user()->username }}" minlength="5" maxlength="15" placeholder="Username" required>
                @if ($errors->has('username'))
                <div class="invalid-feedback" style="display:block;">
                  {{ $errors->first('username') }}
                </div>
                @endif
              </div>

              <div class="col-12">
                <button type="submit" class="btn btn-primary" id="btn-submit-profile">Save Profile</button>
                &nbsp;<div id="submit-spinner-profile" class="spinner-border spinner-border-sm text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
                {{ csrf_field() }}
                <input type="hidden" name="_method" value="PUT">
              </div>
            </form>

          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Password</h5>

            @if (session('password_changed_ok'))
            <div id="alert-password-change-success" class="alert alert-success alert-dismissible fade show" role="alert">
              <i class="bi bi-check-circle me-1"></i>
              {{ session('password_changed_ok') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif
            
            <form id="form-account-password" class="row g-3 needs-validation" method="POST" action="{{ route('account.update.password') }}" novalidate>
              {{ csrf_field() }}

              <div class="col-md-12">
                <label for="input-old-password" class="form-label">Old Password (min. 6 chars)</label>
                <input type="password" class="form-control" id="input-old-password" name="old_password" value="" minlength="6" maxlength="15" placeholder="Old password" required>
                @if ($errors->has('old_password'))
                <div class="invalid-feedback" style="display:block;">
                  {{ $errors->first('old_password') }}
                </div>
                @endif
              </div>

              <div class="col-md-12">
                <label for="input-new-password" class="form-label">New Password (min. 6 chars)</label>
                <input type="password" class="form-control" id="input-new-password" name="password" value="" minlength="6" maxlength="15" placeholder="New password" required>
              </div>

              <div class="col-md-12">
                <label for="input-confirm-password" class="form-label">Confirm Password (min. 6 chars)</label>
                <input type="password" class="form-control" id="input-confirm-password" name="password_confirmation" value="" minlength="6" maxlength="15" placeholder="Confirm password" required>
                @if ($errors->has('password'))
                <div class="invalid-feedback" style="display:block;">
                  {{ $errors->first('password') }}
                </div>
                @endif
              </div>

              <div class="col-12">
                <button type="submit" class="btn btn-primary" id="btn-submit-password">Save Password</button>
                &nbsp;<div id="submit-spinner-password" class="spinner-border spinner-border-sm text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
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
  $('#submit-spinner-profile').hide();
  $('#submit-spinner-password').hide();

  $('#form-account-profile').submit(function() {
    if (($('#input-profile-name').val().trim().length < 5) || ($('#input-profile-username').val().trim().length < 5)) {
      return false;
    }

    $('#submit-spinner-profile').show();
    disableAllButtons();
  });

  $('#form-account-password').submit(function() {
    if (
      ($('#input-old-password').val().trim().length < 6)
      || ($('#input-new-password').val().trim().length < 6)
      || ($('#input-confirm-password').val().trim().length < 6)
    ) {
      return false;
    }

    $('#submit-spinner-password').show();
    disableAllButtons();
  });

  function disableAllButtons() {
    $('#btn-submit-profile').addClass('disabled');
    $('#btn-submit-password').addClass('disabled');
  }
</script>
@endpush

@include('layouts.include_page_footer')
@include('layouts.include_js')