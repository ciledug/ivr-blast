@include('layouts.include_header_body')

<main>
    <div class="container">
        <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
            <div class="container">
                <div class="row justify-content-center">

                    <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">
                        <div class="d-flex justify-content-center py-4">
                            <a href="index.html" class="logo d-flex align-items-center w-auto">
                                <span class="d-none d-lg-block">DALnet System - IVR Broadcast</span>
                            </a>
                        </div>
              
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="pt-4 pb-2">
                                    <h5 class="card-title text-center pb-0 fs-4">Login to Your Account</h5>
                                    <p class="text-center small">Enter your username &amp; password to login</p>
                                </div>
                  
                                <form id="form-login" class="row g-3 needs-validation" method="POST" action="{{ route('login') }}">
                                    {{ csrf_field() }}
                                    
                                    <!--
                                    <div class="col-12">
                                        <label for="input-login-username" class="form-label">Username</label>
                                        <div class="input-group has-validation">
                                            <span class="input-group-text" id="input-login-username">@</span>
                                            <input type="text" id="input-login-username" name="input-login-username" class="form-control" required>
                                            <div class="invalid-feedback">Please enter your username.</div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <label for="input-login-password" class="form-label">Password</label>
                                        <input type="password" id="input-login-password" name="input-login-password" class="form-control" required>
                                        <div class="invalid-feedback">Please enter your password!</div>
                                    </div>

                                    <div class="col-12">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="text" id="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
                                        @if ($errors->has('email'))
                                        <div class="invalid-feedback">
                                            <strong>{{ $errors->first('email') }}</strong>
                                        </div>
                                        @endif
                                    </div>
                                    -->

                                    <div class="col-12">
                                        <label for="username" class="form-label">Username</label>
                                        <input type="text" id="username" name="username" class="form-control" value="{{ old('username') }}" required autofocus>
                                        @if ($errors->has('username'))
                                        <div class="invalid-feedback">
                                            <strong>{{ $errors->first('username') }}</strong>
                                        </div>
                                        @endif
                                    </div>
                    
                                    <div class="col-12">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="password" id="password" name="password" class="form-control" required>
                                        @if ($errors->has('password'))
                                        <div class="invalid-feedback">
                                            <strong>{{ $errors->first('password') }}</strong>
                                        </div>
                                        @endif
                                    </div>
                    
                                    <!--
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="input-login-remember" name="input-login-remember" value="true">
                                            <label class="form-check-label" for="input-login-remember">Remember me</label>
                                        </div>
                                    </div>
                                    -->
                    
                                    <div class="col-12">
                                        <button type="submit" id="btn-login-submit" class="btn btn-primary w-100">Login</button>
                                    </div>
                                </form>
                            </div>
                        </div>
              
                        <div class="credits">
                            <!-- All the links in the footer should remain intact. -->
                            <!-- You can delete the links only if you purchased the pro version. -->
                            <!-- Licensing information: https://bootstrapmade.com/license/ -->
                            <!-- Purchase the pro version with working PHP/AJAX contact form: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/ -->
                            Designed by <a href="https://bootstrapmade.com/">BootstrapMade</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

@include('layouts.include_js')