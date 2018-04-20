require(['layui'], function () {
    layui.use(['element','form'],function(){
        var $ = layui.jquery;
        $('.left_open i').click(function(event) {
            if($('.layui-nav-tree .layui-nav-item').width() == 220){
                $('.left-nav,.layui-logo').animate({
                    left: '-160px'
                }, 100);
                $('.layui-nav-tree .layui-nav-item,.layui-nav-tree,.layui-side-scroll,.layui-side').animate({
                    width: '60px'
                }, 100);
                $('.layui-nav-tree .layui-nav-item span').hide();
                $('.layadmin-flexible').animate({
                    left: '-180px'
                }, 100);
                $('#LAY_app_body').animate({
                    left: '60px'
                }, 100);
                $('.page-content-bg').hide();
            }else{
                $('.left-nav,.layui-logo').animate({
                    left: '0px'
                }, 100);
                $('.layui-nav-tree .layui-nav-item,.layui-nav-tree,.layui-side-scroll,.layui-side').animate({
                    width: '220px'
                }, 100);
                $('.layui-nav-tree .layui-nav-item span').show();
                $('.layadmin-flexible').animate({
                    left: '0px'
                }, 100);
                $('#LAY_app_body').animate({
                    left: '220px'
                }, 100);
                //点击显示后，判断屏幕宽度较小时显示遮罩背景
                if($(window).width() < 768) {
                    $('.page-content-bg').show();
                }
            }
        });
        $(".layui-nav-tree .layui-nav-item").hover(function(){
            $(".layui-nav-bar").show();
            var index = $(".layui-nav-tree .layui-nav-item").index($(this));
            var tops = $(".layui-nav-tree .layui-nav-item").eq(index).offset().top - $(window).scrollTop();
            $(".layui-nav-bar").css("top",tops);
        },function(){
            $(".layui-nav-bar").hide();
        });
    });
})


