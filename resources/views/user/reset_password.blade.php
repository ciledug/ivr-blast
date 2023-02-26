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
              Reset Password for {{ $user->username }} ({{ $user->name }})
            </h5>

            <form id="form-reset-password" class="row g-3 needs-validation" method="POST" action="{{ route('user.update.password') }}" enctype="multipart/form-data" novalidate>
              {{ csrf_field() }}
              
              <div class="col-md-12">
                <div class="form-floating">
                  <input type="password" class="form-control" id="input-reset-password" name="password" minlength="6" maxlength="15" placeholder="Password (min. 6 chars)" required>
                  <label for="input-user-password">Password (min. 6 chars)</label>
                </div>
              </div>

              <div class="col-md-12 mt-3">
                <div class="form-floating">
                  <input type="password" class="form-control" id="input-reset-confirm-password" name="password_confirmation" minlength="6" maxlength="15" placeholder="Confirm Password (min. 6 chars)" required>
                  <label for="input-user-confirm-password">Confirm Password (min. 6 chars)</label>
                </div>
                @if ($errors->has('password'))
                <div class="invalid-feedback" style="display:block">
                  {{ $errors->first('password') }}
                </div>
                @endif
              </div>

              <div class="col-md-12 mt-4">
                <button type="button" id="btn-cancel-reset-password" class="btn btn-secondary btn-back">Cancel</button>
                <button type="submit" id="btn-submit-reset-password" class="btn btn-primary">Save</button>
                &nbsp;<div id="submit-spinner" class="spinner-border spinner-border-sm text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
                <input type="hidden" id="input-reset-user" name="user" value="_{{ $user->username}}">
                <input type="hidden" id="input-reset-method" name="_method" value="PUT">
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
  $('#submit-spinner').hide();

  $('.btn-back').click(function(e) {
    history.back();
  });

  $('#form-reset-password').submit(function() {
    var isOkProceed = true;

    if (($('#input-reset-password').val().length < 6) || ($('#input-reset-confirm-password').val().length < 6)) {
      isOkProceed = false;
    }

    if (!isOkProceed) return isOkProceed;

    $('.btn-back').addClass('disabled');
    $('#btn-submit-reset-password').addClass('disabled');
    $('#submit-spinner').show();
  });
</script>
@endpush

@include('layouts.include_page_footer')
@include('layouts.include_js')