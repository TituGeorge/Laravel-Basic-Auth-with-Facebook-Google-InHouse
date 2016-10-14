<?php
/**
 * Created By Titu George
 * titugeorge@gmail.com
 */

namespace App\Http\Controllers;

use App\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator,View,Hash, Config;
use App\Http\Requests;
use Illuminate\Contracts\Auth\Authenticatable;
use Google_Client;
use Google_Service_Plus;

class AuthenticationController extends Controller
{
    public function insert_user(Request $request)
    {

        $data['email']    = $request['email'];
        $data['password'] = $request['password'];
        $data['ip']       = $_SERVER["REMOTE_ADDR"];

        $validator = Validator::make($data, [
                        'email' => 'required|email|max:255|unique:users',
                        'password' => 'required|min:6']);

        if ($validator->fails())
        {
            $err_response = $validator->errors();
            return Controller::JSONResponse($err_response, API_FAILURE);
        }

        $user = new Users();
        $user->email = $data['email'];
        $user->password = Hash::make($data['password']);
        $user->ip = $data['ip'];
        $user->save();

        if($user->id) {
            return Controller::JSONResponse('', API_SUCCESS);
        }

        return Controller::JSONResponse('', API_FAILURE);
    }


    public function validate_login(Request $request)
    {
        $data['email']      = $request['user_email'];
        $data['password']   = $request['user_password'];
        $data['login_type'] = $request['login_type'];


        if(empty($data['login_type'])) {

            return Controller::JSONResponse('Invalid login params', API_FAILURE);

        }

        if(empty($data['email']) || empty($data['password']) || empty($data['login_type'])) {

            return Controller::JSONResponse('Invalid data', API_FAILURE);

        }


        switch($data['login_type']) {

            case Config::get('constants.NORMAL_LOGIN'):

                $user = Users::whereRaw('email = ?', array($data['email']))->first();
                if ( ! $user) {

                    return Controller::JSONResponse('Invalid username and password', API_FAILURE);

                }

                if (Hash::check($data['password'], $user->password)) {

                    $user_details['id']     = $user->id;
                    $user_details['email']  = $user->email;

                    $request->session()->put('LoggedUser', $user_details);

                    return Controller::JSONResponse('', API_SUCCESS);

                } else {

                    return Controller::JSONResponse('Invalid username and password', API_FAILURE);

                }

                break;

            default:
                return Controller::JSONResponse('Invalid login attempt', API_FAILURE);
                break;
        }
    }



    public function social_google_auth(Request $request)
    {
        if (!session_id()) {
            session_start();
        }

        $client = new Google_Client();
        $client->setClientId(Config::get('constants.GOOGLE_CLIENT_ID'));
        $client->setClientSecret(Config::get('constants.GOOGLE_CLIENT_SECRET'));
        $client->setRedirectUri(url('/') . "/social_google_login");
        $scopes = array('https://www.googleapis.com/auth/userinfo.profile', 'https://www.googleapis.com/auth/userinfo.email');
        $client->setScopes($scopes);

        if (! isset($_GET['code'])) {
            $auth_url = $client->createAuthUrl();
            header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
            exit;
        } else {
            $client->authenticate($_GET['code']);
            $_SESSION['access_token'] = $client->getAccessToken();

            if ($client->getAccessToken())
            {
                $plus = new Google_Service_Plus($client);
                $client->setAccessToken($_SESSION['access_token']);
                $me = $plus->people->get('me');

                if(!$me || empty($me)) {
                    echo "Unable to get user details";
                    exit;
                }

                $data['email']        = $me['emails'][0]['value'];
                $data['google_uid'] = $me->getId();

                $validator = Validator::make($data, [
                    'email' => 'required|email|max:255|unique:users']);

                if ($validator->fails()) {

                    $user = Users::whereRaw('email = ?', array($data['email']))->first();

                    if(!$user) {
                        echo "Invalid user";
                        exit;
                    }

                    if($user->google_uid === $data['google_uid']) {
                        $user_details['id']     = $user->id;
                        $user_details['email']  = $user->email;

                        $request->session()->put('LoggedUser', $user_details);
                        return redirect('/dashboard');



                    }elseif($user->google_uid == ''){
                        $update_user = Users::where('email', $data['email'])
                            ->update(['google_uid' => $data['google_uid']]);

                        if($update_user !== 1) {
                            echo "Unable to update user";
                            exit;
                        }

                        $user_details['id']     = $user->id;
                        $user_details['email']  = $user->email;

                        $request->session()->put('LoggedUser', $user_details);
                        return redirect('/dashboard');

                    }else {

                        //Occurs when App changes
                        echo "Unable to validate, contact admin";
                        exit;
                    }

                } else {

                    $user = new Users();
                    $user->email = $data['email'];
                    //create unique password and sent mail to user for alternative login
                    $user->password = Hash::make(uniqid());
                    $user->google_uid = $data['google_uid'];
                    $user->ip = $_SERVER['REMOTE_ADDR'];
                    $user->save();

                    if(!$user->id) {
                        echo "Unable to register user";
                        exit;
                    } else {
                        $user_details['id']     = $user->id;
                        $user_details['email']  = $user->email;

                        //store in session and redirect
                        $request->session()->put('LoggedUser', $user_details);
                        return redirect('/dashboard');
                    }
                }

            }
        }
    }



    public function social_fb_auth(Request $request)
    {
        if (!session_id()) {
            session_start();
        }

        $fb = new \Facebook\Facebook([
            'app_id' => Config::get('constants.FB_APP_ID'),
            'app_secret' => Config::get('constants.FB_APP_SECRET'),
            'default_graph_version' => 'v2.7',
            //'default_access_token' => '{access-token}', // optional
        ]);

        $helper = $fb->getRedirectLoginHelper();

        try {
            $accessToken = $helper->getAccessToken();

        } catch (\Facebook\Exceptions\FacebookResponseException $e) {

            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {

            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        if (isset($accessToken)) {

            // Logged in!
            $_SESSION['facebook_access_token'] = (string)$accessToken;

            try {
                // Get the \Facebook\GraphNodes\GraphUser object for the current user.
                // If you provided a 'default_access_token', the '{access-token}' is optional.
                $response = $fb->get('/me?fields=id,email,name', $accessToken);
            } catch (\Facebook\Exceptions\FacebookResponseException $e) {

                // When Graph returns an error
                echo 'Graph returned an error: ' . $e->getMessage();
                exit;
            } catch (\Facebook\Exceptions\FacebookSDKException $e) {

                // When validation fails or other local issues
                echo 'Facebook SDK returned an error: ' . $e->getMessage();
                exit;
            }

            $me = $response->getGraphUser();


            if(!$me->getEmail()) {
                echo "Unable to get email id";
                exit;
            }

            $data['email']        = $me->getEmail();
            $data['facebook_uid'] = $me->getId();

            $validator = Validator::make($data, [
                'email' => 'required|email|max:255|unique:users']);

            if ($validator->fails()) {

                $user = Users::whereRaw('email = ?', array($data['email']))->first();

                if(!$user) {
                    echo "Invalid user";
                    exit;
                }

                if($user->facebook_uid === $data['facebook_uid']) {
                    $user_details['id']     = $user->id;
                    $user_details['email']  = $user->email;

                    $request->session()->put('LoggedUser', $user_details);
                    return redirect('/dashboard');



                }elseif($user->facebook_uid == ''){
                    $update_user = Users::where('email', $data['email'])
                                        ->update(['facebook_uid' => $data['facebook_uid']]);

                    if($update_user !== 1) {
                        echo "Unable to update user";
                        exit;
                    }

                    $user_details['id']     = $user->id;
                    $user_details['email']  = $user->email;

                    $request->session()->put('LoggedUser', $user_details);
                    return redirect('/dashboard');

                }else {

                    //Occurs when App changes
                    echo "Unable to validate, contact admin";
                    exit;
                }

            } else {

                $user = new Users();
                $user->email = $data['email'];
                //create unique password and sent mail to user for alternative login
                $user->password = Hash::make(uniqid());
                $user->facebook_uid = $data['facebook_uid'];
                $user->ip = $_SERVER['REMOTE_ADDR'];
                $user->save();

                if(!$user->id) {
                    echo "Unable to register user";
                    exit;
                } else {
                    $user_details['id']     = $user->id;
                    $user_details['email']  = $user->email;

                    //store in session and redirect
                    $request->session()->put('LoggedUser', $user_details);
                    return redirect('/dashboard');
                }
            }
        } else {

            $permissions = ['email', 'user_likes']; // optional
            $url = url('/') . "/social_fb_login";
            $loginUrl = $helper->getLoginUrl($url, $permissions);

            header('Location: ' . $loginUrl);
            exit;
        }

    }
}