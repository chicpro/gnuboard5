<?php
include_once('./_common.php');
include_once(G5_PLUGIN_PATH.'/oauth/functions.php');

add_stylesheet('<link rel="stylesheet" href="'.G5_PLUGIN_URL.'/oauth/style.css">', 10);
$service = preg_replace('#[^a-z]#', '', $_GET['service']);

switch($service) {
    case 'naver' :
    case 'kakao' :
    case 'facebook' :
    case 'google' :
        break;
    default :
        alert_opener_url('소셜 로그인 서비스가 올바르지 않습니다.');
        break;
}

if(defined('G5_OAUTH_MEMBER_REGISTER_SELECT') && G5_OAUTH_MEMBER_REGISTER_SELECT && $_GET['register'] == 'Y') {
    unset($member);

    set_session('ss_mb_id', '');
    set_session('ss_oauth_member_'.get_session('ss_oauth_member_no').'_info', '');
    set_session('ss_oauth_member_no', '');
}

if($member['mb_id']) {
    if($_GET['mode'] == 'connect') {
        // 기존 연동체크
        $sql = " select sm_id from {$g5['social_member_table']} where mb_id = '{$member['mb_id']}' and sm_service = '$service' ";
        $row = sql_fetch($sql);
        if($row['sm_id'])
            alert_close('회원 아이디 '.$member['mb_id'].'에 연동된 서비스입니다.');

        // 연동처리를 위한 세션
        set_session('ss_oauth_request_mb_id',   $member['mb_id']);
        set_session('ss_oauth_request_mode',    'connect');
        set_session('ss_oauth_request_service', $service);
    } else if($_GET['mode'] == 'confirm') {
        // 회원정보 체크
        $mb = get_member($member['mb_id'], 'mb_id');
        if(!$mb['mb_id'])
            alert_close('회원 정보가 존재하지 않습니다.');

        // 연동처리를 위한 세션
        set_session('ss_oauth_request_mb_id',   $member['mb_id']);
        set_session('ss_oauth_request_mode',    'confirm');
        set_session('ss_oauth_request_service', $service);
    } else {
        alert_opener_url();
    }
}

// 회원가입 선택
if(defined('G5_OAUTH_MEMBER_REGISTER_SELECT') && G5_OAUTH_MEMBER_REGISTER_SELECT) {
    if(!isset($_SESSION['ss_oauth_member_register']))
        set_session('ss_oauth_member_register', 'R');

    if($_GET['register'] == 'Y')
        set_session('ss_oauth_member_register', 'Y');
    else if($_GET['register'] == 'N')
        set_session('ss_oauth_member_register', 'N');
}

require G5_PLUGIN_PATH.'/oauth/'.$service.'/login.php';

$g5['title'] = '소셜 로그인';
include_once(G5_PATH.'/head.sub.php');
?>

<div class="social-login-loading">
    <p>소셜 로그인 서비스에 연결 중입니다.<br>잠시만 기다려 주십시오<br><br><img src="<?php echo G5_URL; ?>/plugin/oauth/img/loading_icon.gif" alt="로딩중"></p>
</div>

<script>
$(function() {
    document.location.href = "<?php echo $query; ?>";
});
</script>

<?php
include_once(G5_PATH.'/tail.sub.php');
?>