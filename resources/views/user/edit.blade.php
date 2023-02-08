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
                Update User
            </h5>
            
            <form id="form-update-user" class="row g-3 needs-validation" method="POST" action="{{ route('user.update') }}" enctype="multipart/form-data" novalidate>
              {{ csrf_field() }}

              <div class="col-md-12">
                <div class="form-floating">
                    <input type="text" class="form-control" id="input-user-name" name="name" minlength="5" maxlength="30" placeholder="Name" value="{{ $user->name or '' }}" required>
                    <label for="input-user-name">Name</label>
                </div>
              </div>

              <div class="col-md-12 mt-3">
                <div class="form-floating">
                    <input type="email" class="form-control" id="input-user-email" name="email" minlength="10" maxlength="50" placeholder="Email (optional)" value="{{ $user->email or '' }}">
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
                    <input type="text" class="form-control" id="input-user-username" name="username" minlength="5" maxlength="15" placeholder="Username" value="{{ $user->username or '' }}" required>
                    <label for="input-user-username">Username (min. 5 chars)</label>
                </div>
                @if ($errors->has('username'))
                <div class="invalid-feedback" style="display:block">
                  {{ $errors->first('username') }}
                </div>
                @endif
              </div>
              
              <div class="col-md-12 mt-4">
                <button type="button" id="btn-cancel-update-user" class="btn btn-secondary btn-back">Cancel</button>
                <button type="submit" id="btn-submit-update-user" class="btn btn-primary">Save</button>
                <input type="hidden" id="input-user-user" name="user" value="_{{ $user->username or ''}}">
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
  $(document).ready(function(e) {
    $('.btn-back').click(function(e) {
      history.back();
    });

    $('#form-update-user').submit(function() {
      if (
        $('#input-user-name').val().trim() === ''
        || $('#input-user-username').val().trim() === ''
      ) {
        return false;
      }

      $('.btn-back').addClass('disabled');
      $('#btn-submit-update-user').addClass('disabled');
    });
  });
</script>
@endpush

@include('layouts.include_page_footer')
@include('layouts.include_js')