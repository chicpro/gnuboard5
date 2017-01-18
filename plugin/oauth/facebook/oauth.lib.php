<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

class FACEBOOK_OAUTH {
    public $client_id;
    public $secret_key;
    public $token;
    public $redirect_uri;
    public $ss_name;
    public $access_token;
    public $app_access_token;
    public $authorize_url;
    public $token_url;
    public $token_debug;
    public $profile_url;
    public $profile;

    function __construct($client_id, $secret_key)
    {
        $this->client_id     = $client_id;
        $this->secret_key    = $secret_key;
        $this->redirect_uri  = G5_OAUTH_CALLBACK_URL.'?service=facebook';
        $this->ss_name       = 'ss_fb_state_token';
        $this->authorize_url = 'https://www.facebook.com/dialog/oauth';
        $this->token_url     = 'https://graph.facebook.com/v2.3/oauth/access_token';
        $this->token_debug   = 'https://graph.facebook.com/debug_token';
        $this->profile_url   = 'https://graph.facebook.com/me';
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

        return $this->authorize_url.'?client_id='.$this->client_id.'&response_type=code&redirect_uri='.$uri.'&state='.$this->token.'&scope=email';
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
        $redirect_uri = urlencode(urldecode($this->redirect_uri));

        $url = $this->token_url.'?client_id='.$this->client_id.'&redirect_uri='.$redirect_uri.'&client_secret='.$this->secret_key.'&code='.$code;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $json = curl_exec($ch);
        curl_close($ch);

        if($json) {
            $result = json_decode($json);

            $this->access_token = $result->access_token;

            return true;
        } else {
            return false;
        }
    }

    function check_valid_access_token()
    {
        $url = $this->token_url.'?client_id='.$this->client_id.'&client_secret='.$this->secret_key.'&grant_type=client_credentials';

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $json = curl_exec($ch);
        curl_close($ch);

        if($json) {
            $result = json_decode($json);

            $this->app_access_token = $result->access_token;
        }

        if(!$this->access_token || !$this->app_access_token)
            return false;

        $url = $this->token_debug.'?input_token='.$this->access_token.'&access_token='.$this->app_access_token;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $json = curl_exec($ch);
        curl_close($ch);

        if($json) {
            $result = json_decode($json);

            return ($result->data->is_valid && $result->data->app_id == $this->client_id);
        } else {
            return false;
        }
    }

    function get_profile()
    {
        $url = $this->profile_url.'?fields=id,name,email&access_token='.$this->access_token;

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