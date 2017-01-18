<?php
include_once('./_common.php');
include_once(G5_LIB_PATH.'/json.lib.php');

if($is_guest) {
    $data = array('error' => '올바른 방법으로 이용해 주십시오.');
    die(json_encode($data));
}

$service = preg_replace('#[^a-z]#', '', $_GET['service']);

switch($service) {
    case 'naver' :
    case 'kakao' :
    case 'facebook' :
    case 'google' :
        break;
    default :
        $data = array('error' => '소셜 로그인 서비스가 올바르지 않습니다.');
        die(json_encode($data));
        break;
}

$sql = " delete from {$g5['social_member_table']} where mb_id = '{$member['mb_id']}' and sm_service = '$service' ";
sql_query($sql, false);

$data = array('error' => '');
die(json_encode($data));
?>