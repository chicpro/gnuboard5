<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

$sql = " select * from {$g5['new_win_table']}
          where '".G5_TIME_YMDHIS."' between nw_begin_time and nw_end_time
            and nw_device IN ( 'both', 'mobile' )
          order by nw_id asc ";
$result = sql_query($sql, false);
?>

<!-- 팝업레이어 시작 { -->
<div id="hd_pop">
    <h2>팝업레이어 알림</h2>

<?php
for ($i=0; $nw=sql_fetch_array($result); $i++)
{
    // 이미 체크 되었다면 Continue
    if (isset($_COOKIE["hd_pops_{$nw['nw_id']}"]) && $_COOKIE["hd_pops_{$nw['nw_id']}"])
        continue;
?>

    <div id="hd_pops_<?php echo $nw['nw_id'] ?>" class="hd_pops" style="top:<?php echo $nw['nw_top']?>px;left:<?php echo $nw['nw_left']?>px;">
        <div class="hd_pops_con" style="width:<?php echo $nw['nw_width'] ?>px;height:<?php echo $nw['nw_height'] ?>px">
            <?php echo conv_content($nw['nw_content'], 1); ?>
        </div>
        <div class="hd_pops_footer">
            <button class="hd_pops_reject hd_pops_<?php echo $nw['nw_id']; ?> <?php echo $nw['nw_disable_hours']; ?>"><strong><?php echo $nw['nw_disable_hours']; ?></strong>시간 동안 다시 열람하지 않습니다.</button>
            <button class="hd_pops_close hd_pops_<?php echo $nw['nw_id']; ?>">닫기</button>
        </div>
    </div>
<?php }
if ($i == 0) echo '<span class="sound_only">팝업레이어 알림이 없습니다.</span>';
?>
</div>

<script>
function popupLayerResize(device_width) {
    $(".hd_pops").each(function(i) {
        var $el = $(this);
        var elWidth = $el.outerWidth(true);

        var width = elWidth + parseInt($el.css("left"));

        if (width > device_width) {
            var left;

            if(width > device_width)
                left = 0;
            else
                left = parseInt((width - device_width) / 2);

            if (left < 0)
                left = 0;

            if (left == 0 && elWidth < device_width)
                left = parseInt((device_width - elWidth) / 2);

            $el.find("img").each(function(idx) {
                var $img = $(this);

                if ($img.width() > elWidth) {
                    $img.css("width", "100%").css("height", "");
                }
            });

            if (elWidth > device_width) {
                //$el.css("width", "100%");
                $el.outerWidth(device_width, true);
                $el.find(".hd_pops_con").css("width", "100%").css("height", "");
            }

            $el.css("left", left);
        }
    });
}

$(function() {
    var device_width = $(window).width();

    $(".hd_pops").each(function(i) {
        var $el = $(this);

        if($el.data("left") == undefined) {
            $el.data("left", $el.css("left"));

            $el.find(".hd_pops_con").each(function(k) {
                var $t = $(this);

                if ($t.css("width") != undefined)
                    $t.data("width", $t.css("width"));

                if ($t.css("height") != undefined)
                    $t.data("height", $t.css("height"));
            });
        }
    });

    $(".hd_pops_reject").click(function() {
        var id = $(this).attr('class').split(' ');
        var ck_name = id[1];
        var exp_time = parseInt(id[2]);
        $("#"+id[1]).css("display", "none");
        set_cookie(ck_name, 1, exp_time, g5_cookie_domain);
    });
    $('.hd_pops_close').click(function() {
        var idb = $(this).attr('class').split(' ');
        $('#'+idb[1]).css('display','none');
    });

    // 레이어 width 기기에 맞게 재조정
    popupLayerResize(device_width);

    $( window ).resize(function() {
        $(".hd_pops").each(function(i) {
            var $el = $(this);

            $el.css("width", "");

            if ($el.data("left") != undefined) {
                $el.css("left", $el.data("left"));

                $el.find(".hd_pops_con").each(function(j) {
                    var $t = $(this);

                    if ($t.data("width") != undefined)
                        $t.css("width", $t.data("width"));

                    if ($t.data("height") != undefined)
                        $t.css("height", $t.data("height"));
                });
            }
        });

        device_width = $(window).width();
        popupLayerResize(device_width);
    });

});
</script>
<!-- } 팝업레이어 끝 -->