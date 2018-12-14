/**
 * 单独封装的自定义对象
 * @type {Object}
 */
var my = {
    use_pjax: (typeof pjax_mode === "undefined") ? 0 : pjax_mode, //是否使用pjax 加载 0|1
    unbind_form: false, //取消form的提交绑定事件 false 不取消； true取消
    /**
     * 初始化
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-19
     * @param    {[type]}     argument [description]
     * @return   {[type]}              [description]
     */
    init: function() {
        my.form();
        my.toastr_init();
    },
    /**
     * form 提交绑定 支持多个表单
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-19
     * @param    {[type]}     argument [默认不需要填写，需要说明绑定的表单]
     * @return   {[type]}              [description]
     */
    form: function() {
        if (arguments.length > 0) {
            var form = [];
            for (i = 0; i < arguments.length; i++) {
                form[i] = arguments[i];
            }
        } else {
            //排除table插件 commonsearch的表单
            var form = $("form").not('.form-commonsearch');
        }
        $(form).unbind();
        if (form) {
            var form_length = form.length;
            //有1到多个form
            for (var index = 0; index < form_length; index++) {
                //解绑
                $(form[index]).unbind();
                $(form[index]).find("[type=submit]").unbind();
                //绑定事件
                $(form[index]).find("[type=submit]").click(function(e) {
                    e.preventDefault();
                    var this_form = $(this).parents('form');
                    //取消普通form 事件
                    if (my.unbind_form) {
                        return false;
                    }
                    var check = my.formSubmit($(this_form), e);
                    if (check === false) {
                        return false;
                    }
                    return false;
                });
                $(form[index]).on('submit', function(e) {
                    e.preventDefault();
                    var this_form = $(this).parents('form');
                    //取消普通form 事件
                    if (my.unbind_form) {
                        return false;
                    }
                    var check = my.formSubmit($(this_form), e);
                    if (check === false) {
                        return false;
                    }
                    return false;
                });
            }
        }
        return false;
    },
    /**
     * 绑定pjax 提交form表单 警告(form 提交按钮一定不要设置为 submit）
     * @Author   ZhaoXianFang
     * @DateTime 2018-10-25
     * @param    {[type]}     from_ele          [form 的 id 或者 class]
     * @param    {[type]}     submit_btn        [提交的触发事件 提示：提交按钮最好设置为 button]
     * @param    {[type]}     pjax_view_content [pjax 返回数据后渲染内容输出的位置]
     * @return   {[type]}                       [description]
     * demo my.bindFormPjaxSubmit("#search-form",".btn-search");
     */
    bindFormPjaxSubmit: function(from_ele, submit_btn, pjax_view_content = '') {
        pjax_view_content = pjax_view_content ? pjax_view_content : '#xf_content';
        $("[type=submit]").unbind();
        $(from_ele).unbind();
        //解除form绑定事件
        my.unbind_form = true;
        //绑定监听事件
        $(submit_btn).click(function(event) {
            if (typeof(form_before) == "function") {
                if (form_before(event) === false) {
                    event.preventDefault();
                    return false;
                }
            }
            var form = $(from_ele);
            var jsonData = my.getdivform(form);
            var url_link = form.attr("action") + my.urlEncode(jsonData);
            $.pjax({
                url: url_link,
                container: pjax_view_content,
            })
        })
        $(document).keydown(function(e) {
            if (e.keyCode == 13) {
                $(submit_btn).click();
                e.preventDefault();
                return false;
            }
        })
        $(from_ele).on('submit', function(e) {
            // $(submit_btn).click();
            e.preventDefault();
            return false;
        })
        $(from_ele).find('[type=submit]').each(function(index, elenode) {
            throw 'bindFormPjaxSubmit 方法中禁止使用 submit 的提交类型，建议改为 button';
        });
    },
    /**
     * form 提交
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-19
     * @param    {[type]}     form [description]
     * @return   {[type]}          [description]
     */
    formSubmit: function(form, e) {
        e.preventDefault();
        var form_url = form.attr("action");
        form_url = form_url ? form_url : location.href;
        //移除pjax 标签
        form_url = my.setUrlParam('_pjax', '', form_url);
        form_url = form_url.replace('_pjax', '');
        var formdata = form.serializeArray();
        //验证数据
        var validate = my.formValidate(form);
        if (validate === false) {
            return false;
        }
        layer.msg('正在处理中……');
        //表单提交前的操作
        if (typeof(form_before) == "function") {
            if (form_before(e) === false) {
                e.preventDefault();
                return false;
            }
        }
        my.ajax(form_url, formdata, function(succ) {
            //thinphp 返回数据 0：失败 ；1：成功
            if (succ.code == 1) {
                toastr.success(succ.msg);
            } else {
                //内容,标题
                toastr.error(succ.msg, '出错啦');
            }
            //表单提交后的操作
            if (typeof(form_after) == "function") {
                form_after(succ);
            }
            //关闭弹出层
            if (typeof succ.data != 'undefined' && succ.data.close == 1) {
                var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                setInterval(function() {
                    //关闭弹出层
                    parent.layer.close(index); //再执行关闭
                }, succ.wait * 1000);
            }
            if (succ.url) {
                setInterval(function() {
                    //跳转
                    window.location.href = succ.url;
                }, succ.wait * 1000);
            }
        }, function(jqXHR, textStatus, errorThrown) {
            // console.log(jqXHR.responseText)
            // console.log(textStatus)
            /*弹出jqXHR对象的信息*/
            toastr.error(my.delhtmltag(jqXHR.responseText), '出错啦！');
        });
        return false;
    },
    /**
     * [formValidate 表单验证]
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-27
     * @param    {[type]}     EleId     [form id 或者 form内的 元素id或者class或者选择器 ，为空则默认form]
     * @param    {Boolean}    isFormEle [是不是form id 默认true]
     * @return   {[type]}               [description]
     */
    formValidate: function(EleId, isFormEle) {
        EleId = EleId ? EleId : 'form';
        isFormEle = (isFormEle == undefined) ? true : isFormEle;
        var form;
        if (isFormEle) {
            form = $(EleId);
        } else {
            form = $(EleId).parents('form');
        }
        //移除样式
        $("").removeClass("has-error");
        // e.preventDefault();
        var issuccess = true;
        $(form).find('input,textarea,select').each(function(i, v) {
            // SELECT,INPUT,TEXTAREA
            // 移除错误样式
            $(this).parents('.form-group').removeClass("has-error");
            if ($(this).data('rule') != undefined && !$(this).is(":hidden") && !my.isEmpty($(this).data('rule'))) {
                if (my.regExpFormValidate($(this).val(), $(this).data('rule')) === false) {
                    //添加 错误样式
                    $(this).parents('.form-group').addClass("has-error");
                    issuccess = false;
                    toastr.error('请按照指定格式填写表单');
                    return false; //跳出循环
                }
            }
            if ($(this).attr('required') != undefined && my.isEmpty($(this).val()) && !$(this).is(":hidden")) {
                //添加 错误样式
                $(this).parents('.form-group').addClass("has-error");
                issuccess = false;
                toastr.error('表单未填写完整');
                return false; //跳出循环
            }
        });
        return issuccess;
    },
    /**
     * 正则验证表单
     * @Author   ZhaoXianFang
     * @DateTime 2018-10-24
     * @param    {[type]}     reg_exp_str [description]
     * @param    {[type]}     value       [description]
     * @return   {[type]}                 [description]
     */
    regExpFormValidate: function(value, reg_exp_str) {
        if (my.isEmpty(reg_exp_str)) {
            return true;
        }
        var regExp = {
            'email': '^[a-z0-9]+([._\\-]*[a-z0-9])*@([a-z0-9]+[-a-z0-9]*[a-z0-9]+.){1,63}[a-z0-9]+$',
            'url': '[a-zA-Z0-9][-a-zA-Z0-9]{0,62}(/.[a-zA-Z0-9][-a-zA-Z0-9]{0,62})+/.?',
            'mobile': '^[1][3,4,5,7,8,9][0-9]{9}$',
            'id_card': '^[1-9]\d{5}(18|19|([23]\d))\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$',
            'id_card_15': '^[1-9]\d{5}\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{2}$',
            'strong_pwd': '^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-._]).{8,}$', //强密码 (密码中必须包含大小写字母、数字、特称字符，至少8位)
            'password': '^[a-zA-Z]\w{5,17}$', //密码 以字母开头，长度在6~18之间，只能包含字母、数字和下划线
            'date': '^\d{4}-\d{1,2}-\d{1,2}', //日期
            'zh_cn': '^[\u4e00-\u9fa5]{0,}$', //汉字
            'en_num': '^[A-Za-z0-9]+$', //英文和数字
            'cn_en_num': '^[\u4E00-\u9FA5A-Za-z0-9_]+$', //中文、英文、数字包括下划线
            'number': '^[0-9]*$', //数字
        };
        //开始验证
        var checkArr = reg_exp_str.split("|");
        if (typeof(checkArr[0])) {
            //验证第一表达式
            if (typeof(checkArr[0]) != "undefined") {
                if ('required' == checkArr[0]) {
                    if (my.isEmpty(value)) {
                        return false;
                    }
                } else {
                    if (my.isEmpty(value)) {
                        return true;
                    }
                    var regExpObj1 = new RegExp(regExp[checkArr[0]]);
                    if (regExpObj1.test(value) === false) {
                        return false;
                    }
                }
            } else {
                return true
            }
            //验证第二表达式
            if (typeof(checkArr[1]) != "undefined") {
                var regExpObj2 = new RegExp(regExp[checkArr[1]]);
                if (regExpObj2.test(value) === false) {
                    return false;
                }
            }
        }
        return true;
    },
    /**
     * [getdivform 获取某个div或者form 内的表单]
     * @Author   ZhaoXianFang
     * @DateTime 2018-07-10
     * @param    {[type]}     ele [被操作的form或者div 的class 或者id]
     * @return   {[type]}         [description]
     */
    getdivform: function(ele, return_null = false) {
        var json_data = {};
        $(ele).find('input,textarea,select').each(function(index, elenode) {
            if (return_null) {
                if ($(elenode).attr('type') == 'radio' || $(elenode).attr('type') == 'checkbox') {
                    json_data[$(elenode).attr('name')] = $("input[name='" + $(elenode).attr('name') + "']:checked").val();
                } else {
                    json_data[$(elenode).attr('name')] = $(elenode).val();
                }
            } else {
                if ($(elenode).attr('name') !== undefined && !my.isEmpty($(elenode).attr('name')) && !my.isEmpty($(elenode).val())) {
                    if ($(elenode).attr('type') == 'radio' || $(elenode).attr('type') == 'checkbox') {
                        json_data[$(elenode).attr('name')] = $("input[name='" + $(elenode).attr('name') + "']:checked").val();
                    } else {
                        json_data[$(elenode).attr('name')] = $(elenode).val();
                    }
                }
            }
            //     return false; //跳出循环
        });
        return json_data;
    },
    //判断字符是否为空的方法
    isEmpty: function(obj) {
        if (typeof obj == "undefined" || obj == null || obj == "") {
            return true;
        } else {
            return false;
        }
    },
    /**
     * 去除html 标签
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-19
     * @param    {[type]}     argument [description]
     * @return   {[type]}              [description]
     */
    delhtmltag: function(str) {
        return str ? str.replace(/<\/?.+?>/g, "") : '';
    },
    /**
     * 初始化 toastr 配置
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-20
     * @return   {[type]}     [description]
     */
    toastr_init: function() {
        if (typeof window.toastr === 'undefined') {
            return;
        }
        // 参数名称一定要用引号
        window.toastr.options = {
            "closeButton": true, //是否显示关闭按钮（提示框右上角关闭按钮）； 
            "debug": false, //调试模式
            "progressBar": true, //是否显示进度条（设置关闭的超时时间进度条）；
            "positionClass": "toast-top-right", //  顶端右边
            "onclick": null, //点击事件
            "showDuration": "300", //显示动作时间 
            "hideDuration": "1000", //隐藏动作时间
            "timeOut": "3000", //自动关闭超时时间 
            "extendedTimeOut": "1000", //  加长展示时间
            "showEasing": "swing", //  显示时的动画缓冲方式
            "hideEasing": "linear", //   消失时的动画缓冲方式
            "showMethod": "fadeIn", //显示的方式，和jquery相同 
            "hideMethod": "fadeOut", //隐藏的方式，和jquery相同
            "newestOnTop": true //最新在上面
        };
    },
    /**
     * layer iframe窗 打开一个弹出窗口
     * @Author   ZhaoXianFang
     * @DateTime 2018-05-31
     * @param    {[type]}     url   [弹出层的地址]
     * @param    {[type]}     title [弹出层标题]
     * @param    {[type]}     opt   [设置参数]
     * @return   {[type]}           [description]
     */
    open: function(url, title, options, param) {
        // param 参数
        param = param ? param.name + "=" + param.value : '';
        title = title ? title : "信息";
        url = param ? url + (url.indexOf("?") > -1 ? "&" : "?") + param : url;
        options = options ? options : {};
        //默认layer open 参数配置
        var defaultOptions = {
            type: 2,
            title: '信息', //标题
            // skin: 'layui-layer-lan', //样式类名 深蓝
            shadeClose: false, //是否点击遮罩关闭
            shade: [0.8, '#393D49'], //遮罩 关闭设置 false
            maxmin: true, //开启最大化最小化按钮
            area: my.window.width() > 893 ? ['893px', '600px'] : (my.window.width() > 480 ? [my.window.width() * 0.9 + 'px', '95%'] : 'auto'), //弹出层大小
            moveOut: true, //是否允许拖拽到窗口外
            offset: 'auto', //弹出层坐标（位置） auto(默认，垂直水平居中)  t（顶部）r(右边缘)b(底部)l(左边)lt(左上角)lb(左下角)rt(右上)rb(右下)
            content: url,
            zIndex: layer.zIndex
        };
        options.title = title;
        options = my.mergeJSON(defaultOptions, options);
        options = $.extend({
            success: function(layero, index) {
                var that = this;
                //存储callback事件
                $(layero).data("callback", that.callback);
                layer.setTop(layero);
                var frame = layer.getChildFrame('html', index);
                var layerfooter = frame.find(".layer-footer");
                my.layerfooter(layero, index, that);
                //绑定事件
                if (layerfooter.length > 0) {
                    // 监听窗口内的元素及属性变化
                    // Firefox和Chrome早期版本中带有前缀
                    var MutationObserver = window.MutationObserver || window.WebKitMutationObserver || window.MozMutationObserver
                    // 选择目标节点
                    var target = layerfooter[0];
                    // 创建观察者对象
                    var observer = new MutationObserver(function(mutations) {
                        my.layerfooter(layero, index, that);
                        mutations.forEach(function(mutation) {});
                    });
                    // 配置观察选项:
                    var config = {
                        attributes: true,
                        childList: true,
                        characterData: true,
                        subtree: true
                    }
                    // 传入目标节点和观察选项
                    observer.observe(target, config);
                    // 随后,你还可以停止观察
                    // observer.disconnect();
                }
            },
            end: function(index) {
                //刷新页面
                my.load(null, false);
            }
        }, options ? options : {});
        if ($(window).width() < 480 || (/iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream && top.$(".tab-pane.active").length > 0)) {
            var winWidth = my.window.width() - 35;
            var winHeight = my.window.height() * 0.9;
            options.area = [winWidth + "px", winHeight + "px"];
            options.offset = 'auto';
        }
        //定义请求方式
        options.content = (my.use_pjax == 1 && options.type == 2) ? options.content + (options.content.indexOf("?") > -1 ? "&" : "?") + '_pjax=%23xf_content_null' : options.content;
        layer.open(options);
        return false;
    },
    layerfooter: function(layero, index, that) {
        var frame = layer.getChildFrame('html', index);
        var layerfooter = frame.find(".layer-footer");
        if (layerfooter.length > 0) {
            $(".layui-layer-footer", layero).remove();
            var footer = $("<div />").addClass('layui-layer-btn layui-layer-footer');
            footer.html(layerfooter.html());
            if ($(".row", footer).length === 0) {
                $(">", footer).wrapAll("<div class='row'></div>");
            }
            footer.insertAfter(layero.find('.layui-layer-content'));
            //绑定事件
            footer.on("click", ".btn", function() {
                //方法一 ajax 提交
                var form = $(".btn:eq(" + $(this).index() + ")", layerfooter)[0].form;
                $(form).find(":submit").click();
                //方法二 form 提交
                // if ($(this).hasClass("disabled") || $(this).parent().hasClass("disabled")) {
                //     return;
                // }
                // $(".btn:eq(" + $(this).index() + ")", layerfooter).trigger("click");
            });
            var titHeight = layero.find('.layui-layer-title').outerHeight() || 0;
            var btnHeight = layero.find('.layui-layer-btn').outerHeight() || 0;
            //重设iframe高度
            $("iframe", layero).height(layero.height() - titHeight - btnHeight);
        }
        //修复iOS下弹出窗口的高度和iOS下iframe无法滚动的BUG
        if (/iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream) {
            var titHeight = layero.find('.layui-layer-title').outerHeight() || 0;
            var btnHeight = layero.find('.layui-layer-btn').outerHeight() || 0;
            $("iframe", layero).parent().css("height", layero.height() - titHeight - btnHeight);
            $("iframe", layero).css("height", "100%");
        }
    },
    close: function(callback) {
        var index = parent.layer.getFrameIndex(window.name);
        //执行关闭
        parent.layer.close(index);
        //再调用回传函数
        if (typeof callback === 'function') {
            callback && callback();
        }
    },
    window: {
        width: function() {
            return $(window).width(); //浏览器时下窗口可视区域宽度
        },
        height: function() {
            return $(window).height(); //浏览器时下窗口可视区域高度
        },
    },
    /**
     * JSON合并
     * @Author   ZhaoXianFang
     * @DateTime 2018-05-31
     * @param    {[type]}     json1
     * @param    {[type]}     json2
     * @param    {[type]}     cover [是否修改 json1 的结构]
     * @return   {[type]}           [description]
     */
    mergeJSON: function(json1, json2, cover = true) {
        if (cover) {
            return $.extend(true, json1, json2);
        } else {
            return $.extend({}, json1, json2);
        }
    },
    /**
     * 原生JavaScript实现JSON合并(后者覆盖前者)
     * @Author   ZhaoXianFang
     * @DateTime 2018-05-31
     * @param    {[type]}     minor [json1]
     * @param    {[type]}     main  [json2]
     * @param    {[type]}     cover [相同元素是否覆盖]
     * @return   {[type]}           [description]
     */
    mergeJSON2: function(minor, main, cover = true) {
        if (cover) {
            for (var p in main) {
                // if(main.hasOwnProperty(p) && (!minor.hasOwnProperty(p) ))
                if (main.hasOwnProperty(p)) {
                    minor[p] = main[p];
                }
            }
        } else {
            minor = minor ? minor : {};
            //将main追加到minor
            for (var i = 0; i < main.length; i++) {
                minor.push(main[i]);
            }
        }
        return minor;
    },
    /**
     * 是否为json
     * @Author   ZhaoXianFang
     * @DateTime 2018-05-31
     * @param    {[type]}     target [被检测的对象]
     * @return   {Boolean}           [description]
     */
    isJSON: function(target) {
        console.log(target)
        return typeof target == "object" && target.constructor == Object;
    },
    /**
     * 选择集
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-12
     * @param    {[type]}     array   [description]
     * @param    {[type]}     val     [description]
     * @param    {[type]}     checked [description]
     * @return   {[type]}             [description]
     */
    checkdata: function(array, val, checked) {
        //不在数组中 需要添加
        ($.inArray(val, array) == -1 && checked) && array.push(val);
        //在数组中 需要删除
        ($.inArray(val, array) > -1 && !checked) && array.splice($.inArray(val, array), 1);
        return array;
    },
    /**
     * ajax 请求
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-14
     * @param    {[type]}     url        [请求地址]
     * @param    {[type]}     data       [请求数据]
     * @param    {[type]}     successFun [成功回调]
     * @param    {[type]}     errorFun   [失败回调]
     * @return   {[type]}                [description]
     */
    ajax: function(url, data, successFun, errorFun, reqtype) {
        reqtype = reqtype ? (reqtype.toUpperCase() == 'GET' ? 'GET' : 'POST') : 'POST';
        var index = layer.msg('正在处理中……', {
            icon: 16,
            shade: [0.8, '#393D49'],
            time: 0
        });
        // console.log('send ajax')
        $.ajax({
            async: false,
            cache: false,
            url: url,
            type: reqtype,
            timeout: 30000, // 设置超时时间30秒
            data: data,
            dataType: "json",
            error: function(jqXHR, textStatus, errorThrown) {
                layer.close(index);
                errorFun && errorFun(jqXHR, textStatus, errorThrown);
                /*弹出jqXHR对象的信息*/
                // alert(jqXHR.responseText);
                // alert(jqXHR.status);
                // alert(jqXHR.readyState);
                // alert(jqXHR.statusText);
                // /*弹出其他两个参数的信息*/
                // alert(textStatus);
                // alert(errorThrown);
            },
            success: function(suc) {
                layer.close(index);
                successFun && successFun(suc);
            }
        });
    },
    /**
     * json转url参数 推荐使用
     * @Author   ZhaoXianFang
     * @DateTime 2018-10-25
     * @param    {[type]}     param  [json对象 将要转为URL参数字符串的对象 ]
     * @param    {[type]}     key    [URL参数字符串的前缀 ]
     * @param    {[type]}     encode [true/false 是否进行URL编码,默认为true ]
     * @return   {[type]}            [description]
     * var obj={name:'tom','class':{className:'class1'},classMates:[{name:'lily'}]};  
     * console.log(my.jsonToUrl(obj));  //output: &name=tom&class.className=class1&classMates[0].name=lily  
     * console.log(my.jsonToUrl(obj,'stu'));  //output: &stu.name=tom&stu.class.className=class1&stu.classMates[0].name=lily 
     */
    jsonToUrl: function(param, key, encode) {
        if (param == null) return '';
        var paramStr = '';
        var t = typeof(param);
        if (t == 'string' || t == 'number' || t == 'boolean') {
            paramStr += key + '=' + ((encode == null || encode) ? encodeURIComponent(param) : param);
        } else {
            for (var i in param) {
                var k = key == null ? i : key + (param instanceof Array ? '[' + i + ']' : '.' + i);
                paramStr += ((paramStr == '') ? '?' : '&') + my.jsonToUrl(param[i], k, encode);
            }
        }
        return paramStr;
    },
    /**
     * [getStr 字符串截取]
     * @Author   ZhaoXianFang
     * @DateTime 2018-07-09
     * @param    {[type]}     string [被截取的字符串]
     * @param    {[type]}     str    [截取字符串的标识字符串]
     * @param    {Number}     type   [截取类型  1：截取str前的字符串；2：截取str后的字符串 ]
     *                               split(),用于把一个字符串分割成字符串数组;
     *                               split(str)[0],读取数组中索引为0的值（第一个值）,所有数组索引默认从0开始;
     * @return   {[type]}            [description]
     */
    getStr: function(string, str, type = 1) {
        type = type == 1 ? 1 : 2;
        var str_before = string.split(str)[0];
        var str_after = string.split(str)[1];
        return (type == 1) ? str_before : str_after;
    },
    //获取url中的参数
    urlQuery: function(name, location, get_last) {
        get_last = get_last ? get_last : true; //是否获取地址里面最后一次出现的参数值
        var url = location ? location : window.location.href;
        var splitIndex = url.indexOf("?") + 1;
        var paramStr = url.substr(splitIndex, url.length);
        var arr = paramStr.split('&');
        var lastVal; //最后一次出现的值 在 get_last 为 true 时候生效
        var allParamData = {};
        for (var i = 0; i < arr.length; i++) {
            var kv = arr[i].split('=');
            if (name) {
                if (kv[0] == name) {
                    if (get_last) {
                        lastVal = kv[1];
                    } else {
                        return kv[1];
                    }
                }
            } else {
                if (lastVal == undefined) {
                    lastVal = {};
                }
                //所有
                lastVal[kv[0]] = kv[1];
            }
        }
        return lastVal;
    },
    // 设置路由参数
    setUrlParam: function(name, value, location) {
        var url = location ? location : window.location.href;
        var splitIndex = url.indexOf("?") + 1;
        var paramStr = url.substr(splitIndex, url.length);
        var url_param = {}; //用来存储 url 参数 用于去重
        var newUrl = url.substr(0, splitIndex);
        // - if exist , replace 
        var arr = paramStr.split('&');
        for (var i = 0; i < arr.length; i++) {
            var kv = arr[i].split('=');
            if (kv[0] == name) {
                //记录下 参数
                if (url_param[name] == undefined) {
                    url_param[name] = value
                    newUrl += kv[0] + "=" + value;
                }
            } else {
                if (kv[1] != undefined) {
                    newUrl += kv[0] + "=" + kv[1];
                }
            }
            if (i != arr.length - 1) {
                newUrl += "&";
            }
        }
        // - if new, add
        if (newUrl.indexOf(name) < 0) {
            newUrl += splitIndex == 0 ? "?" + name + "=" + value : "&" + name + "=" + value;
        }
        // return newUrl;
        return ((newUrl == name + '=') || (newUrl == '?' + name + '=') || (newUrl == '&' + name + '=')) ? url : newUrl;
    },
    load: function(url, setpage = true) {
        url = url ? url : window.location.href;
        //搜索的参数名称
        var searchval = my.urlQuery("search", url, 1);
        // - 重写 url
        if (setpage) {
            url = my.setUrlParam("page", 1, url);
        }
        if (searchval !== undefined) {
            url = my.setUrlParam("search", searchval, url);
        }
        if (my.use_pjax == 1) {
            $.pjax({
                url: url,
                container: '#xf_content',
            });
        } else {
            window.location.href = url;
        }
    },
    /**
     * 对url 进行编码与解码
     * @Author   ZhaoXianFang
     * @DateTime 2018-07-09
     * @param    {[type]}     url_str [需要操作的url 字符串]
     * @param    {String}     type    [en:编码；de:解码]
     * @return   {[type]}             [description]
     */
    urlEnCode: function(url_str, type = 'de') {
        if (!url_str) {
            return '';
        }
        if (type && type.toUpperCase() == 'DE') {
            //解码
            return decodeURIComponent(url_str);
        } else {
            //编码
            return encodeURIComponent(url_str);
        }
    },
    /**
     * [autoFontSize 范围内的文字自动缩小字号]
     * @Author   ZhaoXianFang
     * @DateTime 2018-07-05
     * @param    {[type]}     ele [要操作的元素]
     * @return   {[type]}         [description]
     *           说明 需要 word-wrap: break-word; 自动换行属性
     *           使用示例：my.autoFontSize($(".sys_spec_img li span"));
     */
    autoFontSize: function(ele) {
        if ($(ele).length < 1) {
            return false;
        }
        var str = $(ele);
        $(ele).each(function() {
            var fontw = $(this).css('font-size').replace(/[^0-9]/ig, ""); //截取数字; //初始的字体大小
            var fonth = fontw * 1 + 2; //初始字体高度
            var width = $(this).width(); //DIV的宽度 内宽
            // var width=$(this).innerWidth();//DIV的宽度 包含padding 宽
            var height = $(this).height(); //div的高度
            if (height < fonth * 2) {
                height = fonth;
            }
            var lenstr = $(this).text().length; //DIV里文字长度
            fonts = fontw * fonth * lenstr; //字体面积
            divs = width * height; //DIV面积
            // if(fonts>divs){
            var rfont; //重设字体大小为rfont
            rfont = Math.round((Math.sqrt(4 * lenstr * divs - 4 * lenstr * lenstr) - 2 * lenstr) / (2 * lenstr));
            // rfont=Math.round( fontw * (divs/fonts) );
            var lineHeight = $(this).css('line-height').replace(/[^0-9]/ig, ""); //获取行高 ,如果没有设置会得到normal.. 
            var compare = fonts / divs; //参照值
            // console.log(compare);
            if (lineHeight > 0 && compare > 1.3) {
                // Math.ceil(fonts/divs); //向上取整,有小数就整数部分加1 
                var setlineHeight = Math.round(lineHeight / Math.ceil(compare));
                if (setlineHeight <= 17) {
                    setlineHeight = 17;
                }
                $(this).css('line-height', setlineHeight + "px");
            }
            $(this).css("font-size", rfont + "px");
            // }
        });
    },
    /**
     * [array_intersection 求两个数组的交集]
     * @Author   ZhaoXianFang
     * @DateTime 2018-07-06
     * @param    {[type]}     arr_one [数组1]
     * @param    {[type]}     arr_tow [数组2]
     * @return   {[type]}             [description]
     */
    array_intersection: function(arr_one, arr_tow) {
        if (!arr_one || !arr_tow) {
            return false;
        }
        var intersection = arr_one.filter(function(v) {
            return arr_tow.indexOf(v) !== -1
        })
        return intersection;
    },
    /**
     * [array_diff 求两个数组的差]
     * @Author   ZhaoXianFang
     * @DateTime 2018-07-06
     * @param    {[type]}     arr_one [数组1]
     * @param    {[type]}     arr_tow [数组2]
     * @return   {[type]}             [description]
     */
    array_diff: function(arr_one, arr_tow) {
        //第一个数组减去第二个数组
        if (arr_tow.length == 0) {
            return arr_one
        }　　
        var diff = [];　　
        var str = arr_tow.join("&quot;&quot;");　　
        for (var e in arr_one) {　　
            if (str.indexOf(arr_one[e]) == -1) {　　　　
                diff.push(arr_one[e]);　　　　
            }　　
        }　　
        return diff;
    },
    /**
     * [array_merge 求两个数组的并集]
     * @Author   ZhaoXianFang
     * @DateTime 2018-07-11
     * @param    {[type]}     argument [参数，可以是任意个数组]
     * @return   {[type]}              [description]
     */
    array_merge: function(argument) {
        var arr = new Array();
        var obj = {};
        for (var i = 0; i < arguments.length; i++) {
            for (var j = 0; j < arguments[i].length; j++) {
                var str = arguments[i][j];
                if (!obj[str]) {
                    obj[str] = 1;
                    arr.push(str);
                }
            }
        }
        return $.unique(arr);
    },
    /*判断终端是不是电脑*/
    isPc: function() {
        var sUserAgent = navigator.userAgent.toLowerCase();
        var bIsIpad = sUserAgent.match(/ipad/i) == "ipad";
        var bIsIphoneOs = sUserAgent.match(/iphone os/i) == "iphone os";
        var bIsMidp = sUserAgent.match(/midp/i) == "midp";
        var bIsUc7 = sUserAgent.match(/rv:1.2.3.4/i) == "rv:1.2.3.4";
        var bIsUc = sUserAgent.match(/ucweb/i) == "ucweb";
        var bIsAndroid = sUserAgent.match(/android/i) == "android";
        var bIsCE = sUserAgent.match(/windows ce/i) == "windows ce";
        var bIsWM = sUserAgent.match(/windows mobile/i) == "windows mobile";
        if (bIsIpad || bIsIphoneOs || bIsMidp || bIsUc7 || bIsUc || bIsAndroid || bIsCE || bIsWM) {
            return false;
        } else {
            return true;
        }
    }
};
//将my渲染至全局
window.my = my;
my.init();