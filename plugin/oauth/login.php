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
    } else {
        alert_opener_url();
    }
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