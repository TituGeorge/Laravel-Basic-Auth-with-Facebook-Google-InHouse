@include('header')

        <div class="col-sm-6 auth-top-5">

            <form id="frm_login" method="post" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="login_type" value="normal">

                <div class="form-group">
                    <label for="user_email">Email address</label>
                    <input type="email" class="form-control" id="user_email" name="user_email" placeholder="Email" autocomplete="off">
                </div>
                <div class="form-group">
                    <label for="user_password">Password</label>
                    <input type="password" class="form-control" id="user_password" name="user_password" placeholder="Password" autocomplete="off">
                </div>

                <button type="submit" class="btn btn-warning">Login</button>

                <div id="login_loader" class="auth-top-2" style="display: none;">Authenticating, Please wait..</div>
                <div id="login_err" class="auth-top-2"></div>

                <div class="auth-top-2">Not registered yet? <a href="#" id="signup_btn_click">Signup</a> </div>
            </form>

            <form id="frm_signup" method="post" enctype="multipart/form-data" style="display: none;">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="form-group">
                    <label for="email">Email address</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email" autocomplete="off">
                    <div id="email_err"></div>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" autocomplete="off">
                    <div id="password_err"></div>
                </div>

                <button type="submit" class="btn btn-success">Signup</button>
                <div id="signup_loader" style="display: none;">Please wait</div>
                <div id="signup_err" class="auth-top-2"></div>
                <div id="signup_Success" class="auth-top-2"></div>
                <div class="auth-top-2">Already have an account?<a href="#" id="login_btn_click">Login</a> </div>
            </form>


            <hr>
            <div class="pull-left auth-top-2">

                <div class="col-sm-6">
                <form id="frm_facebook" method="post" action="social_fb_login" enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="login_type" value="facebook">
                <button type="submit" class="btn btn-primary">Sign in with Facebook</button>
                </form>

                </div>

                <div class="col-sm-6">
                <form id="frm_google" method="post" action="social_google_login" enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="login_type" value="google">
                    <button type="submit" class="btn btn-danger">Sign in with Google</button>
                </form>
                </div>


            </div>


        </div>

@include('footer')