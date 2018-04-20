// 当前资源URL目录
var baseUrl = (function () {
    var scripts = document.scripts, src = scripts[scripts.length - 1].src;
    return src.substring(0, src.lastIndexOf("/") + 1);
})();
// RequireJs 配置参数
require.config({
    baseUrl: baseUrl,
    waitSeconds: 0,
    map: {'*': {css: baseUrl + '../plugs/require/require.css.js'}},
    paths: {
        // 自定义插件（源码自创建或已修改源码）
        'admin.plugs': ['plugs'],
        'admin.listen': ['listen'],
        'layui': ['../plugs/layui/layui.min'],
        'laydate': ['../plugs/layui/lay/modules/laydate'],
        'pace': ['../plugs/jquery/pace.min'],
        'jquery': ['../plugs/jquery/jquery.min'],
        'jquery.cookies': ['../plugs/jquery/jquery.cookie'],
        'jquery.table2csv': ['../plugs/jquery/jquery.TableCSVExport'],
        'echarts': ['../plugs/echarts/echarts'],
    },
    shim: {
        'layui': {deps: ['jquery']},
        'jquery.cookies': {deps: ['jquery']},
        'admin.plugs': {deps: ['jquery', 'layui']},
        'admin.listen': {deps: ['jquery', 'jquery.cookies', 'admin.plugs']},
    },
    deps: ['css!' + baseUrl + '../plugs/awesome-4.7.0/css/font-awesome.min.css'],
    // 开启debug模式，不缓存资源
    // urlArgs: "ver=" + (new Date()).getTime()
});

// UI框架初始化
require(['pace', 'jquery', 'layui'], function () {
    layui.config({dir: baseUrl + '../plugs/layui/'});
    layui.use(['layer', 'form'], function () {
        window.layer = layui.layer;
        window.form = layui.form;
        require(['admin.listen']);
    });
});
