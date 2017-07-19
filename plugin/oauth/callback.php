<?php
include_once('./_common.php');
include_once(G5_PLUGIN_PATH.'/oauth/functions.php');
include_once(G5_LIB_PATH.'/mailer.lib.php');

//var_dump($_REQUEST); exit;

$req_mb_id   = get_session('ss_oauth_request_mb_id');
$req_mode    = get_session('ss_oauth_request_mode');
$req_service = get_session('ss_oauth_request_service');

set_session('ss_oauth_request_mb_id',   '');
set_session('ss_oauth_request_mode',    '');
set_session('ss_oauth_request_service', '');

if($member['mb_id'] && $req_mode != 'connect' && $req_mode != 'confirm')
    alert_opener_url();

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

require G5_PLUGIN_PATH.'/oauth/'.$service.'/callback.php';

// 테이블 생성
if(defined('G5_OAUTH_TABLE_CREATE') && G5_OAUTH_TABLE_CREATE && $g5['social_member_table'] && !sql_query(" DESCRIBE {$g5['social_member_table']} ", false)) {
    sql_query(" CREATE TABLE IF NOT EXISTS `{$g5['social_member_table']}` (
                  `sm_no` int(11) NOT NULL AUTO_INCREMENT,
                  `sm_id` varchar(20) NOT NULL DEFAULT '',
                  `mb_id` varchar(255) NOT NULL DEFAULT '',
                  `sm_service` varchar(10) NOT NULL DEFAULT '',
                  `sm_ip` varchar(255) NOT NULL DEFAULT '',
                  `sm_datetime` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                  PRIMARY KEY (`sm_no`),
                  UNIQUE KEY `sm_id` (`sm_id`, `sm_service`),
                  KEY `mb_id` (`mb_id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ", false);
}

if($is_member && $req_mode == 'connect' && $req_service == $service) {
    $mb = get_member($req_mb_id);

    if($mb['mb_id'] && $mb['mb_id'] == get_session('ss_mb_id')) {
        // 기존연동체크
        $sql = " select sm_id from {$g5['social_member_table']} where sm_id = '{$member['mb_id']}' and mb_id = '{$mb['mb_id']}' and sm_service = '$service' ";
        $row = sql_fetch($sql);

        if($row['sm_id']) {
            //alert_opener_url();
            echo '<script>'.PHP_EOL;
            echo 'var popup = window.opener;'.PHP_EOL;
            echo 'var url   = "";'.PHP_EOL;
            echo 'if(popup.document.getElementsByName("url").length) {'.PHP_EOL;
            echo '    url = decodeURIComponent(popup.document.getElementsByName("url")[0].value);'.PHP_EOL;
            echo '    popup.location.href = url;'.PHP_EOL;
            echo '}'.PHP_EOL;
            echo 'window.close();'.PHP_EOL;
            echo '</script>';
            exit;
        }

        // 연동정보 입력
        $sql = " insert into {$g5['social_member_table']}
                    set sm_id       = '{$member['mb_id']}',
                        mb_id       = '{$mb['mb_id']}',
                        sm_service  = '$service',
                        sm_ip       = '{$_SERVER['REMOTE_ADDR']}',
                        sm_datetime = '".G5_TIME_YMDHIS."' ";

        if(!sql_query($sql, false))
            alert_opener_url('소셜로그인 서비스를 회원 아이디 '.$mb['mb_id'].' 에 연결할 수 없습니다.', G5_URL);
    }
}

if($is_member && $req_mode == 'confirm' && $req_service == $service) {
    $sql = " select mb_id from {$g5['social_member_table']} where sm_id = '{$member['mb_id']}' and sm_service = '$service' ";
    $row = sql_fetch($sql);

    if($row['mb_id'] && $req_mb_id == $row['mb_id']) {
        $mb = get_member($req_mb_id, 'mb_id, mb_password');

        if(!$mb['mb_id'])
            alert_close('회원 정보가 존재하지 않습니다.');

        set_session('ss_mb_id', $req_mb_id);
        set_session('ss_oauth_member_'.get_session('ss_oauth_member_no').'_info', '');
        set_session('ss_oauth_member_no', '');

        echo '<form name="fconfirm" method="post" target="member_confirm" action="'.G5_HTTP_BBS_URL.'/register_form.php">'.PHP_EOL;
        echo '<input type="hidden" name="w" value="u">'.PHP_EOL;
        echo '<input type="hidden" name="mb_id" value="'.$mb['mb_id'].'">'.PHP_EOL;
        echo '<input type="hidden" name="mb_password" value="'.$mb['mb_password'].'">'.PHP_EOL;
        echo '<input type="hidden" name="is_update" value="1">'.PHP_EOL;
        echo '</form>'.PHP_EOL;
        echo '<script>'.PHP_EOL;
        echo 'document.fconfirm.submit();'.PHP_EOL;
        echo 'window.close();'.PHP_EOL;
        echo '</script>';
        exit;
    } else {
        unset($member);

        set_session('ss_mb_id', $req_mb_id);
        set_session('ss_oauth_member_'.get_session('ss_oauth_member_no').'_info', '');
        set_session('ss_oauth_member_no', '');

        alert_close('올바른 방법으로 이용해 주십시오.');
    }
}

// 가입 또는 연동 내역있으면 로그인 처리
if($g5['social_member_table']) {
    $sql = " select mb_id from {$g5['social_member_table']} where sm_id = '{$member['mb_id']}' and sm_service = '$service' ";
    $row = sql_fetch($sql);
    if($row['mb_id']) {
        $mb = get_member($row['mb_id'], 'mb_id');

        if($mb['mb_id']) {
            unset($member);

            set_session('ss_mb_id', $mb['mb_id']);
            set_session('ss_oauth_member_'.get_session('ss_oauth_member_no').'_info', '');
            set_session('ss_oauth_member_no', '');

            if($req_mode != 'connect') {
                //alert_opener_url();
                opener_url_reload();
            }

            // 정보수정에서 연동일 때 처리
            echo '<script>'.PHP_EOL;
            echo 'var $opener = window.opener;'.PHP_EOL;
            echo '$opener.$("#sns-'.$service.'").removeClass("sns-icon-not");'.PHP_EOL;
            echo 'window.close();'.PHP_EOL;
            echo '</script>';
            exit;
        }
    }
}

// 회원가입처리
if(defined('G5_OAUTH_MEMBER_REGISTER') && G5_OAUTH_MEMBER_REGISTER && $member['mb_id']) {
    if(defined('G5_OAUTH_MEMBER_REGISTER_SELECT') && G5_OAUTH_MEMBER_REGISTER_SELECT) {
        $mb_reg = get_session('ss_oauth_member_register');
        $mb = get_member($member['mb_id'], 'mb_id');

        if($mb['mb_id']) {
            reset_social_info();
            alert_opener_url($mb['mb_id'].'는(은) 다른 회원이 사용 중이므로 사용할 수 없습니다.', G5_URL);
        }

        if(!$mb['mb_id'] && $mb_reg == 'R' && $mb_reg != 'Y' && $mb_reg != 'N') {
            $url1 = G5_PLUGIN_URL.'/oauth/login.php?service='.$service.'&register=Y';
            $url2 = G5_PLUGIN_URL.'/oauth/login.php?service='.$service.'&register=N';
            $url3 = G5_URL;

            confirm($config['cf_title'].' 사이트에 회원가입 하시겠습니까?', $url1, $url2, $url3);
        }

        unset($_SESSION['ss_oauth_member_register']);
    }

    // 중복체크
    $sql = " select count(*) as cnt from {$g5['member_table']} where mb_id = '{$member['mb_id']}' ";
    $row = sql_fetch($sql);
    if($row['cnt'] > 0) {
        reset_social_info();
        alert_opener_url('다른 회원이 사용 중인 아이디로는 회원가입할 수 없습니다.', G5_URL);
    }

    $sql = " select count(*) as cnt from {$g5['member_table']} where mb_email = '{$member['mb_email']}' and mb_id <> '{$member['mb_id']}' ";
    $row = sql_fetch($sql);
    if($row['cnt'] > 0) {
        reset_social_info();
        alert_opener_url('다른 회원이 사용 중인 이메일로는 회원가입할 수 없습니다.', G5_URL);
    }

    $sql = " select count(*) as cnt from {$g5['member_table']} where mb_id = '{$member['mb_id']}' and mb_id <> '{$member['mb_id']}' ";
    $row = sql_fetch($sql);
    if($row['cnt'] > 0) {
        reset_social_info();
        alert_opener_url('다른 회원이 사용 중인 닉네임으로는 회원가입할 수 없습니다.', G5_URL);
    }

    $mb_id    = $member['mb_id'];
    $mb_email = $member['mb_email'];
    $mb_nick  = $member['mb_nick'];
    $mb_name  = $member['mb_name'];

    // 회원정보 입력
    $sql = " insert into {$g5['member_table']}
                set mb_id = '{$mb_id}',
                    mb_password = '{$member['mb_password']}',
                    mb_name = '{$mb_name}',
                    mb_nick = '{$mb_nick}',
                    mb_nick_date = '".G5_TIME_YMD."',
                    mb_email = '{$mb_email}',
                    mb_email_certify = '".G5_TIME_YMDHIS."',
                    mb_today_login = '".G5_TIME_YMDHIS."',
                    mb_datetime = '".G5_TIME_YMDHIS."',
                    mb_ip = '{$_SERVER['REMOTE_ADDR']}',
                    mb_level = '{$config['cf_register_level']}',
                    mb_login_ip = '{$_SERVER['REMOTE_ADDR']}',
                    mb_mailling = '0',
                    mb_sms = '0',
                    mb_open = '0',
                    mb_open_date = '".G5_TIME_YMD."' ";

    $result = sql_query($sql, false);

    if($result) {
        $sql = " insert into {$g5['social_member_table']}
                    set sm_id       = '$mb_id',
                        mb_id       = '$mb_id',
                        sm_service  = '$service',
                        sm_ip       = '{$_SERVER['REMOTE_ADDR']}',
                        sm_datetime = '".G5_TIME_YMDHIS."' ";
        sql_query($sql, false);
    } else {
        alert_opener_url('회원 정보 입력 중 오류가 발생했습니다. 다시 시도해 주십시오.', G5_URL);
    }

    // 회원가입 포인트 부여
    insert_point($mb_id, $config['cf_register_point'], '회원가입 축하', '@member', $mb_id, '회원가입');

    // 최고관리자님께 메일 발송
    if ($config['cf_email_mb_super_admin']) {
        $subject = '['.$config['cf_title'].'] '.$mb_nick .' 님께서 회원으로 가입하셨습니다.';

        ob_start();
        include_once (G5_BBS_PATH.'/register_form_update_mail2.php');
        $content = ob_get_contents();
        ob_end_clean();

        mailer($mb_nick, $mb_email, $config['cf_admin_email'], $subject, $content, 1);
    }

    set_session('ss_mb_id', $mb_id);

    set_session('ss_mb_reg', $mb_id);

    unset($member);
    unset($_SESSION['ss_oauth_member_register']);

    alert_opener_url('', G5_OAUTH_MEMEBER_RESULT_URL);
}

// 소셜로그인 아이디가 사용 중이라면 알림
$mb = get_member($member['mb_id'], 'mb_id');
if($mb['mb_id']) {
    reset_social_info();
    alert_opener_url($mb['mb_id'].'는(은) 다른 회원이 사용 중이므로 로그인할 수 없습니다.', G5_URL);
}

opener_url_reload();
?>