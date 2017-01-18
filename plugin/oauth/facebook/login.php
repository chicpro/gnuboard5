<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

include_once(G5_PLUGIN_PATH.'/oauth/facebook/oauth.lib.php');

if(!defined('G5_FACEBOOK_CLIENT_ID') || !G5_FACEBOOK_CLIENT_ID || !defined('G5_FACEBOOK_SECRET_KEY') || !G5_FACEBOOK_SECRET_KEY)
    alert_opener_url('페이스북로그인 API 정보를 설정해 주십시오.');

$oauth = new FACEBOOK_OAUTH(G5_FACEBOOK_CLIENT_ID, G5_FACEBOOK_SECRET_KEY);

$oauth->set_state_token();

$query = $oauth->get_auth_query();

//header('Location: '.$query);
?>