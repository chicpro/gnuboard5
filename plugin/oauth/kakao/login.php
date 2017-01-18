<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

include_once(G5_PLUGIN_PATH.'/oauth/kakao/oauth.lib.php');

if(!defined('G5_KAKAO_OAUTH_REST_API_KEY') || !G5_KAKAO_OAUTH_REST_API_KEY)
    alert_opener_url('카카오로그인 API 정보를 설정해 주십시오.');

$oauth = new KAKAO_OAUTH(G5_KAKAO_OAUTH_REST_API_KEY);

$oauth->set_state_token();

$query = $oauth->get_auth_query();

//header('Location: '.$query);
?>