<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

class NAVER_OAUTH {
    public $client_id;
    public $secret_key;
    public $token;
    public $redirect_uri;
    public $ss_name;
    public $access_token;
    public $refresh_token;
    public $authorize_url;
    public $token_url;
    public $profile_url;
    public $profile;

    function __construct($client_id, $secret_key)
    {
        $this->client_id     = $client_id;
        $this->secret_key    = $secret_key;
        $this->redirect_uri  = G5_OAUTH_CALLBACK_URL.'?service=naver';
        $this->ss_name       = 'ss_nid_state_token';
        $this->authorize_url = 'https://nid.naver.com/oauth2.0/authorize';
        $this->token_url     = 'https://nid.naver.com/oauth2.0/token';
        $this->profile_url   = 'https://apis.naver.com/nidlogin/nid/getUserProfile.xml';
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
        $url = $this->token_url.'?client_id='.$this->client_id.'&client_secret='.$this->secret_key.'&grant_type=authorization_code&state='.$this->token.'&code='.$code;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
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
        $data = array();

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->profile_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$this->access_token));

        $xml = curl_exec($ch);
        curl_close($ch);

        if($xml)
            $data = $this->get_assoc_array($xml);

        if(!empty($data))
            $this->profile = (object)$data;
    }

    function get_assoc_array($xml)
    {
        $data = array();

        $parse = xml_parser_create();
        xml_parse_into_struct($parse, $xml, $vals, $index);
        xml_parser_free($parse);

        if(is_array($vals) && !empty($vals)) {
            foreach($vals as $val) {
                if($val['type'] != 'complete')
                    continue;

                $key = strtolower($val['tag']);
                $data[$key] = $val['value'];
            }
        }

        return $data;
    }
}
?>