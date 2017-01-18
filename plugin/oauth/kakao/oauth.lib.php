<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

class KAKAO_OAUTH {
    public $client_id;
    public $token;
    public $redirect_uri;
    public $ss_name;
    public $access_token;
    public $refresh_token;
    public $authorize_url;
    public $token_url;
    public $profile_url;
    public $profile;

    function __construct($client_id)
    {
        $this->client_id     = $client_id;
        $this->redirect_uri  = G5_OAUTH_CALLBACK_URL.'?service=kakao';
        $this->ss_name       = 'ss_kko_state_token';
        $this->authorize_url = 'https://kauth.kakao.com/oauth/authorize';
        $this->token_url     = 'https://kauth.kakao.com/oauth/token';
        $this->profile_url   = 'https://kapi.kakao.com/v1/user/me';
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

        return $this->authorize_url.'?client_id='.$this->client_id.'&response_type=code&redirect_uri='.$uri.'&state='.$this->token;
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
            'grant_type'   => 'authorization_code',
            'client_id'    => $this->client_id,
            'redirect_uri' => $this->redirect_uri,
            'code'         => $code
        );

        $post_data = http_build_query($data);

        $ch = curl_init();

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

    function get_profile()
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->profile_url);
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$this->access_token));

        $json = curl_exec($ch);
        curl_close($ch);

        if($json)
            $this->profile = json_decode($json);
    }
}
?>