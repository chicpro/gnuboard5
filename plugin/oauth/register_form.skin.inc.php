<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if($w != 'u' || $is_guest)
    return;

if((defined('G5_NAVER_OAUTH_CLIENT_ID') && G5_NAVER_OAUTH_CLIENT_ID) || (defined('G5_KAKAO_OAUTH_REST_API_KEY') && G5_KAKAO_OAUTH_REST_API_KEY) || (defined('G5_FACEBOOK_CLIENT_ID') && G5_FACEBOOK_CLIENT_ID) || (defined('G5_GOOGLE_CLIENT_ID') && G5_GOOGLE_CLIENT_ID)) {

add_stylesheet('<link rel="stylesheet" href="'.G5_PLUGIN_URL.'/oauth/style.css">', 10);
add_javascript('<script src="'.G5_PLUGIN_URL.'/oauth/jquery.oauth.login.js"></script>', 10);

$social_oauth_url = G5_PLUGIN_URL.'/oauth/login.php?mode=connect&amp;service=';

include_once(G5_PLUGIN_PATH.'/oauth/functions.php');

// 연동여부 확인
$nid_ico = '';
$kko_ico = '';
$fcb_ico = '';
$ggl_ico = '';

if($member['mb_id']) {
    if(!is_social_connected($member['mb_id'], 'naver'))
        $nid_class = ' sns-icon-not';

    if(!is_social_connected($member['mb_id'], 'kakao'))
        $kko_class = ' sns-icon-not';

    if(!is_social_connected($member['mb_id'], 'facebook'))
        $fcb_class = ' sns-icon-not';

    if(!is_social_connected($member['mb_id'], 'google'))
        $ggl_class = ' sns-icon-not';
}
?>

<tr>
    <th scope="row">소셜로그인</th>
    <td>
        <div class="reg-form sns-wrap-32 sns-wrap-over">
            <div class="sns-wrap">
                <?php if(defined('G5_NAVER_OAUTH_CLIENT_ID') && G5_NAVER_OAUTH_CLIENT_ID) { ?>
                <a href="<?php echo $social_oauth_url.'naver'; ?>" target="_blank" id="sns-naver" class="sns-icon oauth_connect sns-naver<?php echo $nid_class; ?>"><span class="ico"></span><span class="txt">네이버 로그인</span></a>
                <?php } ?>
                <?php if(defined('G5_KAKAO_OAUTH_REST_API_KEY') && G5_KAKAO_OAUTH_REST_API_KEY) { ?>
                <a href="<?php echo $social_oauth_url.'kakao'; ?>" target="_blank" id="sns-kakao" class="sns-icon oauth_connect sns-kk<?php echo $kko_class; ?>"><span class="ico"></span><span class="txt">카카오 로그인</span></a>
                <?php } ?>
                <?php if(defined('G5_FACEBOOK_CLIENT_ID') && G5_FACEBOOK_CLIENT_ID) { ?>
                <a href="<?php echo $social_oauth_url.'facebook'; ?>" target="_blank" id="sns-facebook" class="sns-icon oauth_connect sns-fb<?php echo $fcb_class; ?>"><span class="ico"></span><span class="txt">페이스북 로그인</span></a>
                <?php } ?>
                <?php if(defined('G5_GOOGLE_CLIENT_ID') && G5_GOOGLE_CLIENT_ID) { ?>
                <a href="<?php echo $social_oauth_url.'google'; ?>" target="_blank" id="sns-google" class="sns-icon oauth_connect sns-gg<?php echo $ggl_class; ?>"><span class="ico"></span><span class="txt">구글 로그인</span></a>
                <?php } ?>
            </div>
        </div>
    </td>
</tr>
<?php
}
?>