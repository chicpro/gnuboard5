$(function() {
    $("a.social_oauth").on("click", function() {
        var option = "left=50, top=50, width=600, height=450, scrollbars=1";
        window.open(this.href, "win_social_login", option);

        return false;
    });

    $("a.oauth_connect").on("click", function(e) {
        var $this = $(this);

        if(!$this.hasClass("sns-icon-not")) {
            if(!confirm("소셜로그인 연동을 해제하시겠습니까?"))
                return false;

            var url = this.href;
            url = url.replace("login.php", "disconnect.php");
            url = url.replace("&amp;", "&");
            url = url.replace("mode=connect&", "");

            $.ajax({
                url: url,
                type: "GET",
                async: false,
                cache: false,
                dataType: "json",
                success: function(data) {
                    if(data.error != "") {
                        alert(data.error);
                        return false;
                    }

                    $this.addClass("sns-icon-not");
                }
            });
        } else {
            var option = "left=50, top=50, width=600, height=450, scrollbars=1";

            window.open(this.href, "win_social_login", option);
        }

        return false;
    });

    $("a.oauth_confirm").on("click", function(e) {
        var option = "left=50, top=50, width=600, height=450, scrollbars=1";
        window.open(this.href, "win_social_login", option);

        return false;
    });
});