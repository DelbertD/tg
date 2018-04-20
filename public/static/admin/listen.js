define(['jquery', 'admin.plugs', 'jquery.table2csv'], function () {

    /*! 定义当前body对象 */
    this.$body = $('body');

    /*! 注册 data-load 事件行为 */
    this.$body.on('click', '[data-load]', function () {
        var url = $(this).attr('data-load'), tips = $(this).attr('data-tips');

        function _goLoad() {
            $.form.load(url, {}, 'GET', null, true, tips);
        }

        if ($(this).attr('data-confirm')) {
            return $.msg.confirm($(this).attr('data-confirm'), _goLoad);
        }
        return _goLoad.call(this);
    });

    /*! 注册 data-serach 表单搜索行为 */
    this.$body.on('submit', 'form.form-search', function () {
        var url = $(this).attr('action');
        var split = url.indexOf('?') === -1 ? '?' : '&';
        if ((this.method || 'get').toLowerCase() === 'get') {
            window.location.href = '#' + $.menu.parseUri(url + split + $(this).serialize());
        } else {
            $.form.load(url, this, 'post');
        }
    });

    /*! 注册 data-modal 事件行为 */
    this.$body.on('click', '[data-modal]', function () {
        var area = eval($(this).attr('data-area')) || '80%';
        return $.form.modal($(this).attr('data-modal'), 'open_type=modal', $(this).attr('data-title') || '编辑', null, false, '请稍候', area);
    });

    /*! 注册 data-open 事件行为 */
    this.$body.on('click', '[data-open]', function () {
        $.form.href($(this).attr('data-open'), this);
    });

    /*! 注册 data-reload 事件行为 */
    this.$body.on('click', '[data-reload]', function () {
        $.form.reload();
    });

    /*! 注册 data-check 事件行为 */
    this.$body.on('click', '[data-check-target]', function () {
        var checked = !!this.checked;
        $($(this).attr('data-check-target')).map(function () {
            this.checked = checked;
        });
    });

    /*! 注册 data-update 事件行为 */
    this.$body.on('click', '[data-update]', function () {
        var id = $(this).attr('data-update') || (function () {
                var data = [];
                return $($(this).attr('data-list-target') || 'input.list-check-box').map(function () {
                    (this.checked) && data.push(this.value);
                }), data.join(',');
            }).call(this);
        if (id.length < 1) {
            return $.msg.tips('请选择需要操作的数据！');
        }
        var action = $(this).attr('data-action') || $(this).parents('[data-location]').attr('data-location');
        var value = $(this).attr('data-value') || 0, field = $(this).attr('data-field') || 'status';
        var msg = $(this).attr('data-confirm-msg') || '确定要操作这些数据吗？';
        $.msg.confirm(msg, function () {
            $.form.load(action, {field: field, value: value, id: id}, 'POST');
        });
    });

    this.$body.on('click', '[data-multi-modal]', function () {
        var id = $(this).attr('data-update') || (function () {
                var data = [];
                return $($(this).attr('data-list-target') || 'input.list-check-box').map(function () {
                    (this.checked) && data.push(this.value);
                }), data.join(',');
            }).call(this);
        if (id.length < 1) {
            return $.msg.tips('请选择需要操作的数据！');
        }
        var area = eval($(this).attr('data-area')) || ['800px', '360px'];
        return $.form.modal($(this).attr('data-action'), 'open_type=modal&id=' + id, $(this).attr('data-title') || '编辑', null, false, '请稍候', area);
    });

    /*! 注册 data-href 事件行为 */
    this.$body.on('click', '[data-href]', function () {
        var href = $(this).attr('data-href');
        if (href && href.indexOf('#') !== 0) {
            window.location.href = href;
        }
    });

    /*! 注册 data-page-href 事件行为 */
    this.$body.on('click', 'a[data-page-href]', function () {
        window.location.href = '#' + $.menu.parseUri(this.href, this);
    });

    /*! 注册 data-file 事件行为 */
    this.$body.on('click', '[data-file]', function () {
        var type = $(this).attr('data-type') || 'jpg,png';
        var field = $(this).attr('data-field') || 'file';
        var method = $(this).attr('data-file') === 'one' ? 'one' : 'mtl';
        var title = $(this).attr('data-title') || '文件上传';
        var uptype = $(this).attr('data-uptype') || '';
        var url = window.ROOT_URL + '/index.php/admin/plugs/upfile.html?uptype=' + uptype + '&type=' + type + '&field=' + field + '&mode=' + method;
        $.form.iframe(url, title || '文件管理');
    });

    /*! 注册 data-iframe 事件行为 */
    this.$body.on('click', '[data-iframe]', function () {
        $.form.iframe($(this).attr('data-iframe'), $(this).attr('data-title') || '窗口');
    });

    /*! 注册 data-icon 事件行为 */
    this.$body.on('click', '[data-icon]', function () {
        var field = $(this).attr('data-icon') || $(this).attr('data-field') || 'icon';
        var url = window.ROOT_URL + '/index.php/admin/plugs/icon.html?field=' + field;
        $.form.iframe(url, '图标选择');
    });

    /*! 注册 data-tips-image 事件行为 */
    this.$body.on('click', '[data-tips-image]', function () {
        var src = this.getAttribute('data-tips-image') || this.src, img = new Image();
        var imgWidth = this.getAttribute('data-width') || '480px';
        img.onload = function () {
            layer.open({
                type: 1, area: imgWidth, title: false, closeBtn: 1, skin: 'layui-layer-nobg', shadeClose: true,
                content: $(img).appendTo('body').css({background: '#fff', width: imgWidth, height: 'auto'}),
                end: function () {
                    $(img).remove();
                }
            });
        };
        img.src = src;
    });

    /*! 注册 data-tips-text 事件行为 */
    this.$body.on('mouseenter', '[data-tips-text]', function () {
        var text = $(this).attr('data-tips-text');
        var placement = $(this).attr('data-tips-placement') || 'auto';
        $(this).tooltip({title: text, placement: placement}).tooltip('show');
    });

    /*! 注册 data-phone-view 事件行为 */
    this.$body.on('click', '[data-phone-view]', function () {
        var $container = $('<div class="mobile-preview pull-left"><div class="mobile-header">公众号</div><div class="mobile-body"><iframe id="phone-preview" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"></iframe></div></div>').appendTo('body');
        $container.find('iframe').attr('src', this.getAttribute('data-phone-view') || this.href);
        layer.style(layer.open({
            type: 1,
            scrollbar: !1,
            area: ['330px', '600px'],
            title: !1,
            closeBtn: 1,
            skin: 'layui-layer-nobg',
            shadeClose: !!1,
            content: $container,
            end: function () {
                $container.remove();
            }
        }), {boxShadow: 'none'});
    });

    this.$body.on('click', '[data-export]', function () {
        var table = $(this).attr('data-value');
        var filename = $(this).attr('data-export-filename') || (new Date()).getTime() + '.csv';
        table = $(table);
        table.TableCSVExport({
            delivery: 'download',
            filename: filename
        })
    });

    /*! 后台菜单控制初始化 */
    $.menu.listen();

    /*! 表单监听初始化 */
    $.validate.listen(this);
});