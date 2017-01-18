<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

include_once(G5_PLUGIN_PATH.'/oauth/kakao/oauth.lib.php');

if(!defined('G5_KAKAO_OAUTH_REST_API_KEY') || !G5_KAKAO_OAUTH_REST_API_KEY)
    alert_opener_url('카카오로그인 API 정보를 설정해 주십시오.');

$oauth = new KAKAO_OAUTH(G5_KAKAO_OAUTH_REST_API_KEY);

if($oauth->check_valid_state_token($_GET['state'])) {
    if($oauth->get_access_token($_GET['code'])) {
        $oauth->get_profile();

        //var_dump($oauth->profile); exit;

        if($oauth->profile->id) {
            $email = '';
            $info  = get_oauth_member_info($oauth->profile->id, $oauth->profile->properties->nickname, 'kakao');

            if($info['id']) {
                unset($member);

                $member = array(
                            'mb_id'       => $info['id'],
                            'mb_password' => $info['pass'],
                            'mb_email'    => $email,
                            'mb_nick'     => $info['nick'],
                            'mb_name'     => $oauth->profile->properties->nickname,
                            'mb_level'    => 2,
                            'mb_point'    => 0
                        );

                set_session('ss_oauth_member_no',                               'kko_'.$oauth->profile->id);
                set_session('ss_oauth_member_kko_'.$oauth->profile->id.'_info', $member);
            }
        } else {
            alert_close('서비스 장애 또는 정보가 올바르지 않습니다.');
        }
    } else {
        alert_close('서비스 장애 또는 정보가 올바르지 않습니다.');
    }
} else {
    alert_close('올바른 방법으로 이용해 주십시오.');
}
?>