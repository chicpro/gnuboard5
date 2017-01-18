<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

class GOOGLE_OAUTH {
    public $client_id;
    public $client_secret;
    public $token;
    public $redirect_uri;
    public $ss_name;
    public $access_token;
    public $authorize_url;
    public $token_url;
    public $token_info;
    public $profile_url;
    public $profile;
    public $scope;

    function __construct($client_id, $client_secret)
    {
        $this->client_id     = $client_id;
        $this->client_secret = $client_secret;
        $this->redirect_uri  = G5_OAUTH_CALLBACK_URL.'?service=google';
        $this->ss_name       = 'ss_ggl_state_token';
        $this->authorize_url = 'https://accounts.google.com/o/oauth2/auth';
        $this->token_url     = 'https://www.googleapis.com/oauth2/v4/token';
        $this->token_info    = 'https://www.googleapis.com/oauth2/v2/tokeninfo';
        $this->profile_url   = 'https://www.googleapis.com/oauth2/v2/userinfo';
        $this->scope         = 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile';
    }

    function set_state_token()
    {
        $mt   = microtime();
        $rand = mt_rand();

        $token = md5($mt . $rand);

        set_session($this->ss_name, $token);

        $this->token = $token;
    }

    function get_auth_query()
    {
        $uri = urlencode(urldecode($this->redirect_uri));

        return $this->authorize_url.'?access_type=offline&approval_prompt=auto&client_id='.$this->client_id.'&response_type=code&redirect_uri='.$uri.'&state='.$this->token.'&scope='.urlencode($this->scope);
    }

    function check_valid_state_token($token)
    {
        $ss_token = get_session($this->ss_name);

        set_session($this->ss_name, '');

        if(!$ss_token || !$token)
            return false;

        return ($ss_token == $token);
    }

    function get_access_token($code)
    {
        $data = array(
            'grant_type'    => 'authorization_code',
            'client_id'     => $this->client_id,
            'client_secret' => $this->client_secret,
            'redirect_uri'  => $this->redirect_uri,
            'code'          => $code
        );

        $post_data = http_build_query($data);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type' => 'application/http',
            'Content-Transfer-Encoding' => 'binary',
            'MIME-Version' => '1.0'
        ));
        curl_setopt($ch, CURLOPT_URL, $this->token_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $json = curl_exec($ch);
        curl_close($ch);

        if($json) {
            $result = json_decode($json);

            $this->access_token  = $result->access_token;
            $this->refresh_token = $result->refresh_token;

            return true;
        } else {
            return false;
        }
    }

    function check_valid_access_token()
    {
        $url = $this->token_info.'?access_token='.$this->access_token;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $json = curl_exec($ch);
        curl_close($ch);

        if($json) {
            $result = json_decode($json);

            return ($result->issued_to == $this->client_id);
        } else {
            return false;
        }
    }

    function get_profile()
    {
        $url = $this->profile_url.'?access_token='.$this->access_token;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $json = curl_exec($ch);
        curl_close($ch);

        if($json)
            $this->profile = json_decode($json);
    }
}
?>