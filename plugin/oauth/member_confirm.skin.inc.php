<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if($is_guest)
    return;

if((defined('G5_NAVER_OAUTH_CLIENT_ID') && G5_NAVER_OAUTH_CLIENT_ID) || (defined('G5_KAKAO_OAUTH_REST_API_KEY') && G5_KAKAO_OAUTH_REST_API_KEY) || (defined('G5_FACEBOOK_CLIENT_ID') && G5_FACEBOOK_CLIENT_ID) || (defined('G5_GOOGLE_CLIENT_ID') && G5_GOOGLE_CLIENT_ID)) {

add_stylesheet('<link rel="stylesheet" href="'.G5_PLUGIN_URL.'/oauth/style.css">', 10);
add_javascript('<script src="'.G5_PLUGIN_URL.'/oauth/jquery.oauth.login.js"></script>', 10);

$social_oauth_url = G5_PLUGIN_URL.'/oauth/login.php?mode=confirm&amp;service=';

include_once(G5_PLUGIN_PATH.'/oauth/functions.php');
?>

<div class="conf-form sns-wrap-32 sns-wrap-over">
    <div class="sns-wrap">
        <?php if(defined('G5_NAVER_OAUTH_CLIENT_ID') && G5_NAVER_OAUTH_CLIENT_ID) { ?>
        <a href="<?php echo $social_oauth_url.'naver'; ?>" target="_blank" class="sns-icon oauth_confirm sns-naver"><span class="ico"></span><span class="txt">네이버 로그인</span></a>
        <?php } ?>
        <?php if(defined('G5_KAKAO_OAUTH_REST_API_KEY') && G5_KAKAO_OAUTH_REST_API_KEY) { ?>
        <a href="<?php echo $social_oauth_url.'kakao'; ?>" target="_blank" class="sns-icon oauth_confirm sns-kk"><span class="ico"></span><span class="txt">카카오 로그인</span></a>
        <?php } ?>
        <?php if(defined('G5_FACEBOOK_CLIENT_ID') && G5_FACEBOOK_CLIENT_ID) { ?>
        <a href="<?php echo $social_oauth_url.'facebook'; ?>" target="_blank" class="sns-icon oauth_confirm sns-fb"><span class="ico"></span><span class="txt">페이스북 로그인</span></a>
        <?php } ?>
        <?php if(defined('G5_GOOGLE_CLIENT_ID') && G5_GOOGLE_CLIENT_ID) { ?>
        <a href="<?php echo $social_oauth_url.'google'; ?>" target="_blank" class="sns-icon oauth_confirm sns-gg"><span class="ico"></span><span class="txt">구글 로그인</span></a>
        <?php } ?>
    </div>
</div>

<script>
window.name = "member_confirm";
</script>
<?php
}
?>