$.pjax.defaults.timeout = 10000;
$.pjax.defaults.maxCacheLength = 0;
pjax_mode == 1 && $(document).pjax('a[target!=_blank]:not([notpjax])', '#xf_content', []);
// console.log("pjax_mode",pjax_mode)
$(document).on('pjax:send', function() {
    // console.log('加载中...');
    $("body").mLoading({
        text: "努力加载中...", //加载文字，默认值：加载中...
        icon: "", //加载图标，默认值：一个小型的base64的gif图片
        html: false, //设置加载内容是否是html格式，默认值是false
        content: "", //忽略icon和text的值，直接在加载框中显示此值
        mask: false //是否显示遮罩效果，默认显示
    });
});
var set_stop_load = true;
//  ajax执行beforeSend函数时触发，可在回调函数中设置额外的请求头参数。可调用e.preventDefault();取消pjax
$(document).on('pjax:beforeSend', function(xhr, options) {
    // xhr.preventDefault();
    // console.log(xhr)
    try {
        var is_menu = xhr.currentTarget.activeElement.attributes.menu; //目录标识

        if (xhr.currentTarget.activeElement.tagName.toUpperCase() == 'A' && is_menu === undefined) {
            if (set_stop_load) {
                xhr.preventDefault();
                var request_url = window.location.href;
                var nowparam = my.urlQuery(xhr.currentTarget.activeElement.search, null, 1); //现请求参数
                $.each(nowparam, function(index, value) {
                    if (index && value) {
                        request_url = my.setUrlParam(request_url, index, value);
                    }
                });
                set_stop_load = false;
                $.pjax({
                    url: decodeURIComponent(request_url),
                    container: '#xf_content',
                });
                set_stop_load = true;
            }
        }
    } catch (err) {
        console.log("ERR:" + err)
    }
});
$(document).on('pjax:start', function() {
    if (typeof layui !== 'undefined') {
        layui.cache.event = {};
    }
});
document.onkeydown = function(e) { //键盘按键控制
    e = e || window.event;
    if ((e.ctrlKey && e.keyCode == 82) || e.keyCode == 116) { ////ctrl+R || F5刷新，禁止
        // return false
    }
}
$(document).on('pjax:complete', function() {
    //加载结束后 写入查询条件 如果有的话
    var url_param = my.urlQuery(window.location.href, null, 1);
    $.each(url_param, function(index, value) {
        if (!my.isEmpty(value)) {
            $("#" + index).val(decodeURIComponent(value));
        }
    });
    $("#search").focus()
    // console.log('加载结束');
    $("body").mLoading('hide');
    var xf_title_hidden = $("#xf_title_hidden").val();
    if ((xf_title_hidden != null && xf_title_hidden != '') || typeof(sys_config.name) != undefined) {
        document.title = xf_title_hidden && sys_config.name ? xf_title_hidden + '|' + sys_config.name : xf_title_hidden + sys_config.name;
    }
    //重新绑定数据
    my.init();
    /**
     * PJAX模式重写get请求提交处理
     */
    $('.ajax-get').click(function() {
        var target;
        if ($(this).hasClass('confirm')) {
            if (!confirm('确认要执行该操作吗?')) {
                return false;
            }
        }
        if ((target = $(this).attr('href')) || (target = $(this).attr('url'))) {
            if ($(this).attr('is-jump') == 'true') {
                $.pjax({
                    url: target,
                    container: '.content'
                });
            } else {
                $.get(target).success(function(data) {
                    obalertp(data);
                });
            }
        }
        return false;
    });
    /**
     * PJAX模式重写表单POST提交处理
     */
    $('.ajax-post').click(function() {
        var target, query, form;
        var target_form = $(this).attr('target-form');
        var that = this;
        var nead_confirm = false;
        if (($(this).attr('type') == 'submit') || (target = $(this).attr('href')) || (target = $(this).attr('url'))) {
            form = $('.' + target_form);
            if ($(this).attr('hide-data') === 'true') { //无数据时也可以使用的功能
                form = $('.hide-data');
                query = form.serialize();
            } else if (form.get(0) == undefined) {
                return false;
            } else if (form.get(0).nodeName == 'FORM') {
                if ($(this).hasClass('confirm')) {
                    if (!confirm('确认要执行该操作吗?')) {
                        return false;
                    }
                }
                if ($(this).attr('url') !== undefined) {
                    target = $(this).attr('url');
                } else {
                    target = form.get(0).action;
                }
                query = form.serialize();
            } else if (form.get(0).nodeName == 'INPUT' || form.get(0).nodeName == 'SELECT' || form.get(0).nodeName == 'TEXTAREA') {
                form.each(function(k, v) {
                    if (v.type == 'checkbox' && v.checked == true) {
                        nead_confirm = true;
                    }
                })
                if (nead_confirm && $(this).hasClass('confirm')) {
                    if (!confirm('确认要执行该操作吗?')) {
                        return false;
                    }
                }
                query = form.serialize();
            } else {
                if ($(this).hasClass('confirm')) {
                    if (!confirm('确认要执行该操作吗?')) {
                        return false;
                    }
                }
                query = form.find('input,select,textarea').serialize();
            }
            var is_ladda_button = $(that).hasClass('ladda-button');
            is_ladda_button ? button.start('.ladda-button') : $(that).addClass('disabled').attr('autocomplete', 'off').prop('disabled', true);
            $.post(target, query).success(function(data) {
                obalertp(data);
                is_ladda_button ? button.stop('.ladda-button') : $(that).removeClass('disabled').prop('disabled', false);
            });
        }
        return false;
    });
    //排序
    $(".sort-text").change(function() {
        var val = $(this).val();
        if (!((/^(\+|-)?\d+$/.test(val)) && val >= 0)) {
            toast.warning('请输入正整数');
            return false;
        }
        $.post($(this).attr("href"), {
            id: $(this).attr('id'),
            value: val
        }, function(data) {
            obalertp(data);
        }, "json");
    });
    //全选|全不选
    $(".checkbox-select-all").click(function() {
        var select_status = $(this).find("input").is(":checked");
        var table_input = $(".table").find("input");
        if (select_status) {
            table_input.prop("checked", true);
        } else {
            table_input.prop("checked", false);
        }
    });
    //批量处理
    $('.batch_btn').click(function() {
        var $checked = $('.table input[type="checkbox"]:checked');
        if ($checked.length != 0) {
            if (confirm('您确认批量操作吗？')) {
                $.post($(this).attr("href"), {
                    ids: $checked.serializeArray(),
                    status: $(this).attr("value")
                }, function(data) {
                    obalertp(data);
                }, "json");
            }
        } else {
            toast.warning('请选择批量操作数据');
        }
        return false;
    });
});
/**
 * PJAX模式重写跳转处理
 */
var obalertp = function(data) {
    if (data.code) {
        toast.success(data.msg);
    } else {
        if (typeof data.msg == "string") {
            toast.error(data.msg);
        } else {
            var err_msg = '';
            for (var item in data.msg) {
                err_msg += "Θ " + data.msg[item] + "<br/>";
            }
            toast.error(err_msg);
        }
    }
    data.url && $.pjax({
        url: data.url,
        container: '.content'
    });
};
/**
 * PJAX模式左侧菜单优化点击显示
 */
$('.sidebar-menu li').click(function() {
    if ($(this).find('ul').length <= 0) {
        $(this).siblings('li').removeClass('menu-open active');
        $(this).addClass('active');
    }
});
