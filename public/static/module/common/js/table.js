var Table = {
    list: {},
    defaults: {
        url: "", //AJAX获取表格数据的url
        contentType: "application/x-www-form-urlencoded", //一种编码。在post请求的时候需要用到。这里用的get请求，注释掉这句话也能拿到数据
        height: '', //600
        search: true, //是否显示表格搜索
        searchText: '',
        commonSearch: false, //是否启用通用搜索
        searchFormVisible: false, //是否始终显示搜索表单
        searchOnEnterKey: false, //设置为 true时，按回车触发搜索方法，否则自动触发搜索方法
        searchAlign: "right", //指定搜索框水平方向位置
        showRefresh: false, //是否显示刷新按钮
        showToggle: false, //是否显示详细视图和列表视图的切换按钮
        showExport: false, //是否显示导出按钮 true || false
        showColumns: false, //
        sidePagination: 'server',
        method: 'get', //请求方法
        toolbar: "#toolbar", //工具栏
        cardView: false, //是否显示详细视图 卡片视图
        detailView: false, //是否显示详情
        detailFormatter: false,
        cache: false, // 设置为 false 禁用 AJAX 数据缓存， 默认为true
        sortable: true, //是否启用排序
        sortOrder: "desc", //排序方式
        sortName: 'id', // 要排序的字段
        rowStyle: false, //行样式
        minimumCountColumns: 2, //最少允许的列数
        pk: 'id',
        autoRefresh: false, //表格是否设置数据自动更新 int 类型 ,单位秒
        pageNumber: 1, //初始化加载第一页，默认第一页
        pageSize: 10,
        pageList: [10, 25, 50, 'All'],
        pagination: true, //启用分页
        showPaginationSwitch: false, // 显示分页开关
        paginationLoop: false, //当前页是边界时是否可以继续按
        paginationFirstText: "首页",
        paginationPreText: "上一页",
        paginationNextText: "下一页",
        paginationLastText: "尾页",
        clickToSelect: true, //是否启用点击选中
        singleSelect: false, //是否启用单选
        showFooter: false, //显示脚部统计
        responseHandler: false, //响应处理器
        strictSearch: false, //是否全局匹配,false模糊匹配
        idField: "id", //定义id字段
        icons: {
            refresh: "glyphicon-refresh",
            toggle: "glyphicon-list-alt",
            columns: "glyphicon-list",
            export: "glyphicon-download-alt", //glyphicon-export,
            fullscreen: 'glyphicon-resize-full'
        },
        titleForm: '', //为空则不显示标题，不定义默认显示：普通搜索
        idTable: 'commonTable',
        exportDataType: "all", //basic当前页', 'all所有, 'selected'.
        exportTypes: ['json', 'xml', 'csv', 'txt', 'doc', 'excel'],
        exportOptions: {
            ignoreColumn: [0], //忽略某一列的索引
            fileName: '信息报表', //文件名称设置
            worksheetName: 'sheet1', //表格工作区名称
            tableName: '信息报表',
            excelstyles: ['background-color', 'color', 'font-size', 'font-weight']
        },
        locale: 'zh-CN',
        mobileResponsive: true, //是否自适应移动端
        checkOnInit: true, //是否在初始化时判断
        escape: true, //是否对内容进行转义
        striped: true, //设置为 true 会有隔行变色效果
        showFullscreen: false, //是否显示全屏按钮
        trimOnSearch: true, //搜索内容是否自动去除前后空格
        extend: {
            index_url: '',
            add_url: '',
            edit_url: '',
            del_url: '',
            import_url: '', //文件上传
            multi_url: '',
            dragsort_url: 'ajax/weigh',
            text: {
                add_text: '',
                edit_text: '',
                multi_text: '',
                del_text: '',
                import_text: '',
            },
            upload:{
                //最大可上传文件大小
                maxsize: "10mb",
                //文件类型
                mimetype : "csv,xls,xlsx,png,jpg",
                //请求的表单参数
                multipart : true,//[true:multipart/form-data的形式来上传文件][false:以二进制的格式来上传文件]
                //是否支持批量上传
                multiple : false
            }
        }
    },
    // Bootstrap-table 列配置
    columnDefaults: {
        align: 'center',
        valign: 'middle',
    },
    config: {
        firsttd: 'tbody tr td:first-child:not(:has(div.card-views))',
        toolbar: '#toolbar',
        refreshbtn: '.btn-refresh',
        addbtn: '.btn-add',
        editbtn: '.btn-edit',
        delbtn: '.btn-del',
        importbtn: '.btn-import',
        multibtn: '.btn-multi',
        disabledbtn: '.btn-disabled',
        editonebtn: '.btn-editone',
        dragsortfield: 'weigh'
    },
    api: {
        init: function(defaults, columnDefaults, locales) {
            defaults = defaults ? defaults : {};
            columnDefaults = columnDefaults ? columnDefaults : {};
            locales = locales ? locales : {};
            // 写入bootstrap-table默认配置
            $.extend(true, $.fn.bootstrapTable.defaults, Table.defaults, defaults, {
                showExport: Table.api.isPc()
            });
            // 写入bootstrap-table column配置
            $.extend($.fn.bootstrapTable.columnDefaults, Table.columnDefaults, columnDefaults);
            //用户接管事件
            $.extend($.fn.bootstrapTable.defaults, (typeof(tableInit) !== 'undefined' && typeof(tableInit.operate) !== 'undefined') ? tableInit.operate : {});
            // 写入bootstrap-table defaults配置
            $.extend($.fn.bootstrapTable.defaults, {
                formatCommonSearch: function() {
                    return '搜索';
                },
                formatCommonSubmitButton: function() {
                    return '提交';
                },
                formatCommonResetButton: function() {
                    return '重置';
                },
                formatCommonCloseButton: function() {
                    return '关闭';
                },
                formatCommonChoose: function() {
                    return '选择';
                }
            });
            if (typeof(tableInit) !== 'undefined' && typeof(tableInit.events) !== 'undefined') {
                //追加用户自定义点击事件
                // my.mergeJSON(Table.api.events.operate,tableInit.events || $.noop);
                $.extend(Table.api.events.operate, tableInit.events || $.noop);
            }
        },
        getOptions: function(table) {
            try {
                //放在前面优先判断
                //table 1.12 新增 bootstrapVersion 
                if (typeof $.fn.bootstrapTable.utils.bootstrapVersion === 'undefined') {
                    return table.bootstrapTable('getOptions');
                }
                if (typeof table === 'undefined' || typeof table.table === 'undefined') {
                    return $.fn.bootstrapTable.defaults;
                }
            } catch (err) {}
            return {};
        },
        // 绑定事件
        bindevent: function(table) {
            //Bootstrap-table的父元素,包含table,toolbar,pagnation
            var parenttable = table.closest('.bootstrap-table');
            //Bootstrap-table配置
            var options = table.bootstrapTable('getOptions');
            //Bootstrap操作区
            var toolbar = $(options.toolbar, parenttable);
            //当刷新表格时
            table.on('load-error.bs.table', function(status, res, e) {
                if (e.status === 0) {
                    return;
                }
                // Toastr.error('未知的数据格式');
            });
            //当刷新表格时
            table.on('refresh.bs.table', function(e, settings, data) {
                $(Table.config.refreshbtn, toolbar).find(".fa").addClass("fa-spin");
            });
            //当双击单元格时
            table.on('dbl-click-row.bs.table', function(e, row, element, field) {
                //用户接管双击事件
                if (typeof(tableInit) !== 'undefined' && typeof(tableInit.dblclickCallback) === "function") {
                    tableInit.dblclickCallback(row, field);
                } else {
                    $(Table.config.editonebtn, element).trigger("click");
                }
            });
            //当单击单元格时
            table.on('click-row.bs.table', function(e, row, element, field) {
                //用户接管单击事件
                if (typeof(tableInit) !== 'undefined' && typeof(tableInit.clickCallback) === "function") {
                    tableInit.clickCallback(row, field);
                }
            });
            //当内容渲染完成后
            table.on('post-body.bs.table', function(e, settings, json, xhr) {
                $(Table.config.refreshbtn, toolbar).find(".fa").removeClass("fa-spin");
                $(Table.config.delbtn, toolbar).toggleClass('disabled', true);
                $(Table.config.editbtn, toolbar).toggleClass('disabled', true);
                $(Table.config.multibtn, toolbar).toggleClass('disabled', true);
            });
            // 处理选中筛选框后按钮的状态统一变更
            table.on('check.bs.table uncheck.bs.table check-all.bs.table uncheck-all.bs.table fa.event.check', function(value, row, element) {
                var table = $(this).closest('table');
                var ids = Table.api.selectedids(table);
                $(Table.config.multibtn, toolbar).toggleClass('disabled', !table.bootstrapTable('getSelections').length);
                $(Table.config.delbtn, toolbar).toggleClass('disabled', !table.bootstrapTable('getSelections').length);
                $(Table.config.editbtn, toolbar).toggleClass('disabled', !table.bootstrapTable('getSelections').length);
                $(Table.config.multi_url, toolbar).toggleClass('disabled', !table.bootstrapTable('getSelections').length);
            });
            // 刷新按钮事件
            $(toolbar).on('click', Table.config.refreshbtn, function() {
                table.bootstrapTable('refresh');
            });
            // 添加按钮事件
            $(toolbar).on('click', Table.config.addbtn, function() {
                var ids = Table.api.selectedids(table);
                var url = options.extend.add_url;
                if (url.indexOf("{ids}") !== -1) {
                    url = Table.api.replaceurl(url, {
                        ids: ids.length > 0 ? ids.join(",") : 0
                    }, table);
                }
                my.open(url, options.extend.text.add_text ? options.extend.text.add_text : '新增', options.layerOptions || $(this).data());
            });
            // 导入按钮事件
            $(toolbar).on('click', Table.config.importbtn, function() {
                Table.api.plupload(table, $(Table.config.importbtn, toolbar), function(data, ret) {
                    if(ret.code == 1){
                        toastr.success(ret.msg);
                    }else{
                        toastr.error(ret.msg);
                    }
                    table.bootstrapTable('refresh');
                    
                });
            });
            // 批量编辑按钮事件
            $(toolbar).on('click', Table.config.editbtn, function() {
                var that = this;
                //循环弹出多个编辑框
                $.each(table.bootstrapTable('getSelections'), function(index, row) {
                    var url = options.extend.edit_url;
                    row = $.extend({}, row ? row : {}, {
                        ids: row[options.pk]
                    });
                    var url = Table.api.replaceurl(url, row, table);
                    // 用户在编辑前重写URL 用可自己接入的函数 setUrlBeforeEdit(url,row)
                    if (typeof(tableInit) !== 'undefined' && typeof(tableInit.setUrlBeforeEdit) === "function") {
                        var userSetUrl = tableInit.setUrlBeforeEdit(url, row);
                        url = userSetUrl ? userSetUrl : url;
                    }
                    my.open(url, options.extend.text.edit_text ? options.extend.text.edit_text : '编辑', options.layerOptions || $(this).data());
                });
            });
            // 批量操作按钮事件
            $(toolbar).on('click', Table.config.multibtn, function() {
                var ids = Table.api.selectedids(table);
                //ZhaoXianFang 2018-03-30
                ids = ids.length ? ids : Table.radio_val;
                Table.api.multi($(this).data("action"), ids, table, this);
            });
            // 批量删除按钮事件
            $(toolbar).on('click', Table.config.delbtn, function() {
                var that = this;
                var ids = Table.api.selectedids(table);
                layer.confirm('你确定要删除选中的 ' + ids.length + ' 项 ?', {
                    icon: 3,
                    title: '温馨提示',
                    offset: 0,
                    shadeClose: true
                }, function(index) {
                    Table.api.multi("del", ids, table, that);
                    layer.close(index);
                });
            });
            //绑定定时刷新
            if (typeof($.fn.bootstrapTable.defaults.autoRefresh) === 'number' && ($.fn.bootstrapTable.defaults.autoRefresh) % 1 === 0 && ($.fn.bootstrapTable.defaults.autoRefresh) > 0) {
                setInterval(function() {
                    var page = table.bootstrapTable('getOptions').pageNumber; //用户所在页码
                    table.bootstrapTable('selectPage', +page);
                }, ($.fn.bootstrapTable.defaults.autoRefresh) * 1000);
            }
            var id = table.attr("id");
            Table.list[id] = table;
            return table;
        },
        // 获取选中的条目ID集合
        selectedids: function(table) {
            var options = table.bootstrapTable('getOptions');
            if (options.templateView) {
                return $.map($("input[data-id][name='checkbox']:checked"), function(dom) {
                    return $(dom).data("id");
                });
            } else {
                return $.map(table.bootstrapTable('getSelections'), function(row) {
                    return row[options.pk];
                });
            }
        },
        // 批量操作请求
        multi: function(action, ids, table, element) {
            var options = table.bootstrapTable('getOptions');
            var data = element ? $(element).data() : {};
            var ids = ($.isArray(ids) ? ids.join(",") : ids);
            var url = typeof data.url !== "undefined" ? data.url : (action == "del" ? options.extend.del_url : options.extend.multi_url);
            url = this.replaceurl(url, {
                ids: ids
            }, table);
            var params = typeof data.params !== "undefined" ? (typeof data.params == 'object' ? $.param(data.params) : data.params) : '';
            var ajaxData = {
                action: action,
                ids: ids,
                params: params
            }; //请求的数据
            // 用户在编辑前重写URL 用可自己接入的函数 setUrlBeforeMulti(url,data,selections,ids)
            if (typeof(tableInit) !== 'undefined' && typeof(tableInit.setUrlBeforeMulti) === "function") {
                var selections = table.bootstrapTable('getSelections'); //选中的项
                var userSetUrl = tableInit.setUrlBeforeMulti(url, data, selections, ids);
                //重写url
                if (typeof(userSetUrl.url) !== 'undefined' && userSetUrl.url) {
                    url = userSetUrl.url;
                }
                //重写data 请求数据
                if (typeof(userSetUrl.data) !== 'undefined' && userSetUrl.data) {
                    ajaxData = userSetUrl.data;
                }
            }
            my.ajax(url, ajaxData, function(data, ret) {
                var success = $(element).data("success") || $.noop;
                if (typeof success === 'function') {
                    if (false === success.call(element, data, ret)) {
                        return false;
                    }
                }
                table.bootstrapTable('refresh');
            }, function(data, ret) {
                var error = $(element).data("error") || $.noop;
                if (typeof error === 'function') {
                    if (false === error.call(element, data, ret)) {
                        return false;
                    }
                }
            });
        },
        // 单元格元素事件
        events: {
            operate: {
                'click .btn-editone': function(e, value, row, index) {
                    e.stopPropagation();
                    e.preventDefault();
                    var table = $(this).closest('table');
                    var options = table.bootstrapTable('getOptions');
                    var ids = row[options.pk];
                    row = $.extend({}, row ? row : {}, {
                        ids: ids
                    });
                    var url = options.extend.edit_url;
                    // 用户在编辑前重写URL 用可自己接入的函数 setUrlBeforeEdit(url,row)
                    if (typeof(setUrlBeforeEdit) === "function") {
                        var userSetUrl = setUrlBeforeEdit(url, row);
                        url = userSetUrl ? userSetUrl : url;
                    }
                    my.open(Table.api.replaceurl(url, row, table), options.extend.text.edit_text ? options.extend.text.edit_text : '编辑', options.layerOptions || $(this).data());
                },
                'click .btn-delone': function(e, value, row, index) {
                    e.stopPropagation();
                    e.preventDefault();
                    var that = this;
                    var top = $(that).offset().top - $(window).scrollTop();
                    var left = $(that).offset().left - $(window).scrollLeft() - 260;
                    if (top + 154 > $(window).height()) {
                        top = top - 154;
                    }
                    if ($(window).width() < 480) {
                        top = left = undefined;
                    }
                    layer.confirm('确定删除此项?', {
                        icon: 3,
                        title: '温馨提示',
                        skin: 'layui-layer-lan', //样式类名 深蓝
                        offset: [top, left],
                        shadeClose: true
                    }, function(index) {
                        var table = $(that).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        Table.api.multi("del", row[options.pk], table, that);
                        layer.close(index);
                    });
                }
            }
        },
        // 单元格数据格式化
        formatter: {
            icon: function(value, row, index) {
                if (!value) return '';
                value = value === null ? '' : value.toString();
                value = value.indexOf(" ") > -1 ? value : "fa fa-" + value;
                //渲染fontawesome图标
                return '<i class="' + value + '"></i> ' + value;
            },
            image: function(value, row, index) {
                value = value ? value : '/assets/img/blank.gif';
                var classname = typeof this.classname !== 'undefined' ? this.classname : 'img-sm img-center';
                return '<a href="' + value + '" target="_blank"><img class="' + classname + '" src="' + value + '" /></a>';
            },
            images: function(value, row, index) {
                value = value === null ? '' : value.toString();
                var classname = typeof this.classname !== 'undefined' ? this.classname : 'img-sm img-center';
                var arr = value.split(',');
                var html = [];
                $.each(arr, function(i, value) {
                    value = value ? value : '/assets/img/blank.gif';
                    html.push('<a href="' + value + '" target="_blank"><img class="' + classname + '" src="' + value + '" /></a>');
                });
                return html.join(' ');
            },
            status: function(value, row, index) {
                var custom = {
                    normal: 'success',
                    hidden: 'gray',
                    deleted: 'danger',
                    locked: 'info'
                };
                if (typeof this.custom !== 'undefined') {
                    custom = $.extend(custom, this.custom);
                }
                this.custom = custom;
                this.icon = 'fa fa-circle';
                return Table.api.formatter.normal.call(this, value, row, index);
            },
            normal: function(value, row, index) {
                var colorArr = ["primary", "success", "danger", "warning", "info", "gray", "red", "yellow", "aqua", "blue", "navy", "teal", "olive", "lime", "fuchsia", "purple", "maroon"];
                var custom = {};
                if (typeof this.custom !== 'undefined') {
                    custom = $.extend(custom, this.custom);
                }
                value = value === null ? '' : value.toString();
                var keys = typeof this.searchList === 'object' ? Object.keys(this.searchList) : [];
                var index = keys.indexOf(value);
                var color = value && typeof custom[value] !== 'undefined' ? custom[value] : null;
                var display = index > -1 ? this.searchList[value] : null;
                var icon = typeof this.icon !== 'undefined' ? this.icon : null;
                if (!color) {
                    color = index > -1 && typeof colorArr[index] !== 'undefined' ? colorArr[index] : 'primary';
                }
                if (!display) {
                    display = value.charAt(0).toUpperCase() + value.slice(1);
                }
                var html = '<span class="text-' + color + '">' + (icon ? '<i class="' + icon + '"></i> ' : '') + display + '</span>';
                if (this.operate != false) {
                    html = '<a href="javascript:;" class="searchit" data-toggle="tooltip" title="' + '点击搜索 ' + display + '" data-field="' + this.field + '" data-value="' + value + '">' + html + '</a>';
                }
                return html;
            },
            toggle: function(value, row, index) {
                var color = typeof this.color !== 'undefined' ? this.color : 'success';
                var yes = typeof this.yes !== 'undefined' ? this.yes : 1;
                var no = typeof this.no !== 'undefined' ? this.no : 0;
                return "<a href='javascript:;' data-toggle='tooltip' title='" + '点击开关' + "' class='btn-change' data-id='" + row.id + "' data-params='" + this.field + "=" + (value == yes ? no : yes) + "'><i class='fa fa-toggle-on " + (value == yes ? 'text-' + color : 'fa-flip-horizontal text-gray') + " fa-2x'></i></a>";
            },
            url: function(value, row, index) {
                return '<div class="input-group input-group-sm" style="width:250px;margin:0 auto;"><input type="text" class="form-control input-sm" value="' + value + '"><span class="input-group-btn input-group-sm"><a href="' + value + '" target="_blank" class="btn btn-default btn-sm"><i class="fa fa-link"></i></a></span></div>';
            },
            search: function(value, row, index) {
                return '<a href="javascript:;" class="searchit" data-toggle="tooltip" title="' + '点击搜索 ' + value + '" data-field="' + this.field + '" data-value="' + value + '">' + value + '</a>';
            },
            addtabs: function(value, row, index) {
                var url = Table.api.replaceurl(this.url, row, this.table);
                var title = this.atitle ? this.atitle : "搜索 " + value;
                return '<a href="' + url + '" class="addtabsit" data-value="' + value + '" title="' + title + '">' + value + '</a>';
            },
            dialog: function(value, row, index) {
                var url = Table.api.replaceurl(this.url, row, this.table);
                var title = this.atitle ? this.atitle : "查看 " + value;
                return '<a href="' + url + '" class="dialogit" data-value="' + value + '" title="' + title + '">' + value + '</a>';
            },
            flag: function(value, row, index) {
                var that = this;
                value = value === null ? '' : value.toString();
                var colorArr = {
                    index: 'success',
                    hot: 'warning',
                    recommend: 'danger',
                    'new': 'info'
                };
                //如果字段列有定义custom
                if (typeof this.custom !== 'undefined') {
                    colorArr = $.extend(colorArr, this.custom);
                }
                var field = this.field;
                if (typeof this.customField !== 'undefined' && typeof row[this.customField] !== 'undefined') {
                    value = row[this.customField];
                    field = this.customField;
                }
                //渲染Flag
                var html = [];
                var arr = value.split(',');
                $.each(arr, function(i, value) {
                    value = value === null ? '' : value.toString();
                    if (value == '') return true;
                    var color = value && typeof colorArr[value] !== 'undefined' ? colorArr[value] : 'primary';
                    var display = typeof that.searchList !== 'undefined' && typeof that.searchList[value] !== 'undefined' ? that.searchList[value] : value.charAt(0).toUpperCase() + value.slice(1);
                    html.push('<a href="javascript:;" class="searchit" data-toggle="tooltip" title="' + '点击搜索 ' + display + '" data-field="' + field + '" data-value="' + value + '"><span class="label label-' + color + '">' + display + '</span></a>');
                });
                return html.join(' ');
            },
            label: function(value, row, index) {
                return Table.api.formatter.flag.call(this, value, row, index);
            },
            datetime: function(value, row, index) {
                var datetimeFormat = typeof this.datetimeFormat === 'undefined' ? 'YYYY-MM-DD HH:mm:ss' : this.datetimeFormat;
                if (isNaN(value)) {
                    return value ? moment(value).format(datetimeFormat) : '空';
                } else {
                    return value ? moment(parseInt(value) * 1000).format(datetimeFormat) : '空';
                }
            },
            operate: function(value, row, index) {
                var table = this.table;
                // 操作配置
                var options = table ? table.bootstrapTable('getOptions') : {};
                // 默认按钮组
                var buttons = $.extend([], this.buttons || []);
                // 所有按钮名称
                var names = [];
                buttons.forEach(function(item) {
                    names.push(item.name);
                });
                if (options.extend.dragsort_url !== '' && names.indexOf('dragsort') === -1) {
                    buttons.push({
                        name: 'dragsort',
                        icon: 'fa fa-arrows',
                        title: 'Drag to sort',
                        extend: 'data-toggle="tooltip"',
                        classname: 'btn btn-xs btn-primary btn-dragsort'
                    });
                }
                if (options.extend.edit_url !== '' && names.indexOf('edit') === -1) {
                    buttons.push({
                        name: 'edit',
                        icon: 'fa fa-pencil',
                        title: options.extend.text.edit_text ? options.extend.text.edit_text : '编辑',
                        text: options.extend.text.edit_text ? options.extend.text.edit_text : '编辑',
                        extend: 'data-toggle="tooltip"',
                        classname: 'btn btn-xs btn-success btn-editone',
                        url: options.extend.edit_url
                    });
                }
                if (options.extend.del_url !== '' && names.indexOf('del') === -1) {
                    buttons.push({
                        name: 'del',
                        icon: 'fa fa-trash',
                        title: options.extend.text.del_text ? options.extend.text.del_text : '删除',
                        text: options.extend.text.del_text ? options.extend.text.del_text : '删除',
                        extend: 'data-toggle="tooltip"',
                        classname: 'btn btn-xs btn-danger btn-delone'
                    });
                }
                var userBtn = '';
                var useSysBtn = true; //使用系统按钮
                // 追加用户自定义btn 用可自己接入的函数 initOperateBtn(row, index, field)
                if (typeof(tableInit) !== 'undefined' && typeof(tableInit.initOperateBtn) === "function") {
                    userRes = tableInit.initOperateBtn(row, index, 'operate');
                    if (userRes === false) {
                        //禁用默认按钮
                        useSysBtn = false;
                        userBtn = '';
                    } else if (typeof userRes === 'string') {
                        userBtn = userRes || '';
                    } else if (userRes instanceof Array) {
                        userBtn = userRes['0'] || '';
                        if (typeof(userRes['1']) !== 'undefined' && userRes['1'] === false) {
                            //禁用默认按钮
                            useSysBtn = false;
                        }
                    }
                }
                return userBtn + (useSysBtn ? Table.api.buttonlink(this, buttons, value, row, index, 'operate') : '');
            },
            buttons: function(value, row, index) {
                // 默认按钮组
                var buttons = $.extend([], this.buttons || []);
                return Table.api.buttonlink(this, buttons, value, row, index, 'buttons');
            }
        },
        buttonlink: function(column, buttons, value, row, index, type) {
            var table = column.table;
            type = typeof type === 'undefined' ? 'buttons' : type;
            var options = table ? table.bootstrapTable('getOptions') : {};
            var html = [];
            var url, classname, icon, text, title, extend;
            var fieldIndex = column.fieldIndex;
            $.each(buttons, function(i, j) {
                if (type === 'operate') {
                    if (j.name === 'dragsort' && typeof row[Table.config.dragsortfield] === 'undefined') {
                        return true;
                    }
                    if (['add', 'edit', 'del', 'multi', 'dragsort'].indexOf(j.name) > -1 && !options.extend[j.name + "_url"]) {
                        return true;
                    }
                }
                var attr = table.data(type + "-" + j.name);
                if (typeof attr === 'undefined' || attr) {
                    url = j.url ? j.url : '';
                    url = url ? url : 'javascript:;';
                    classname = j.classname ? j.classname : 'btn-primary btn-' + name + 'one';
                    icon = j.icon ? j.icon : '';
                    text = j.text ? j.text : '';
                    title = j.title ? j.title : text;
                    refresh = j.refresh ? 'data-refresh="' + j.refresh + '"' : '';
                    confirm = j.confirm ? 'data-confirm="' + j.confirm + '"' : '';
                    extend = j.extend ? j.extend : '';
                    html.push('<a href="' + url + '" class="' + classname + '" ' + (confirm ? confirm + ' ' : '') + (refresh ? refresh + ' ' : '') + extend + ' title="' + title + '" data-table-id="' + (table ? table.attr("id") : '') + '" data-field-index="' + fieldIndex + '" data-row-index="' + index + '" data-button-index="' + i + '"><i class="' + icon + '"></i>' + (text ? ' ' + text : '') + '</a>');
                }
            });
            return html.join(' ');
        },
        //替换URL中的数据
        replaceurl: function(url, row, table) {
            var options = table ? table.bootstrapTable('getOptions') : null;
            var ids = options ? row[options.pk] : 0;
            row.ids = ids ? ids : (typeof row.ids !== 'undefined' ? row.ids : 0);
            //自动添加ids参数
            url = !url.match(/\{ids\}/i) ? url + (url.match(/(\?|&)+/) ? "&ids=" : "/ids/") + '{ids}' : url;
            url = url.replace(/\{(.*?)\}/gi, function(matched) {
                matched = matched.substring(1, matched.length - 1);
                if (matched.indexOf(".") !== -1) {
                    var temp = row;
                    var arr = matched.split(/\./);
                    for (var i = 0; i < arr.length; i++) {
                        if (typeof temp[arr[i]] !== 'undefined') {
                            temp = temp[arr[i]];
                        }
                    }
                    return typeof temp === 'object' ? '' : temp;
                }
                return row[matched];
            });
            return url;
        },
        /*判断终端是不是PC--用于判断文件是否导出(电脑需要导出)*/
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
        },
        // 根据行索引获取行数据
        getrowdata: function(table, index) {
            index = parseInt(index);
            var data = table.bootstrapTable('getData');
            return typeof data[index] !== 'undefined' ? data[index] : null;
        },
        // 根据行索引获取行数据
        getrowbyindex: function(table, index) {
            return Table.api.getrowdata(table, index);
        },
        // 根据主键ID获取行数据
        getrowbyid: function(table, id) {
            var row = {};
            var options = table.bootstrapTable('getOptions');
            $.each(table.bootstrapTable('getData'), function(i, j) {
                if (j[options.pk] == id) {
                    row = j;
                    return false;
                }
            });
            return row;
        },
        //Plupload上传
        plupload: function(table, element, onUploadSuccess, onUploadError, onUploadComplete) {
            //Bootstrap-table配置
            var options = table.bootstrapTable('getOptions');
            // element = typeof element === 'undefined' ? pupload.config.classname : element;
            // $(element, pupload.config.container).each(function() {
            $(element).each(function() {
                if ($(this).attr("initialized")) {
                    return true;
                }
                $(this).attr("initialized", true);
                var that = this;
                var id = $(this).prop("id");
                var url = $(this).data("url");
                var maxsize = $(this).data("maxsize");
                var mimetype = $(this).data("mimetype");
                var multipart = $(this).data("multipart");
                var multiple = $(this).data("multiple");
                //填充ID
                var input_id = $(that).data("input-id") ? $(that).data("input-id") : "";
                //预览ID
                var preview_id = $(that).data("preview-id") ? $(that).data("preview-id") : "";
                //上传URL
                url = url ? url : options.extend.import_url;
                //最大可上传文件大小
                maxsize = typeof maxsize !== "undefined" ? maxsize : options.extend.upload.maxsize;
                //文件类型
                mimetype = typeof mimetype !== "undefined" ? mimetype : options.extend.upload.mimetype;
                //请求的表单参数
                multipart = typeof multipart !== "undefined" ? multipart : options.extend.upload.multipart;
                //是否支持批量上传
                multiple = typeof multiple !== "undefined" ? multiple : options.extend.upload.multiple;
                var mimetypeArr = new Array();
                //支持后缀和Mimetype格式,以,分隔
                if (mimetype && mimetype !== "*" && mimetype.indexOf("/") === -1) {
                    var tempArr = mimetype.split(',');
                    for (var i = 0; i < tempArr.length; i++) {
                        mimetypeArr.push({
                            title: '文件',
                            extensions: tempArr[i]
                        });
                    }
                    mimetype = mimetypeArr;
                }
                
                //生成Plupload实例
                Upload.list[id] = new plupload.Uploader({
                    runtimes: 'html5,flash,silverlight,html4',
                    multi_selection: multiple, //是否允许多选批量上传
                    browse_button: id, // 浏览按钮的ID
                    container: $(this).parent().get(0), //取按钮的上级元素
                    flash_swf_url: '/assets/libs/plupload/js/Moxie.swf',
                    silverlight_xap_url: '/assets/libs/plupload/js/Moxie.xap',
                    filters: {
                        max_file_size: maxsize,
                        mime_types: mimetype,
                    },
                    url: url,
                    multipart_params: $.isArray(multipart) ? {} : multipart,
                    init: {
                        PostInit: Upload.events.onPostInit,
                        FilesAdded: Upload.events.onFileAdded,
                        BeforeUpload: Upload.events.onBeforeUpload,
                        UploadProgress: function(up, file) {
                            var button = up.settings.button;
                            $(button).prop("disabled", true).html("<i class='fa fa-upload'></i> " + '上传' + file.percent + "%");
                            Upload.events.onUploadProgress(up, file);
                        },
                        FileUploaded: function(up, file, info) {
                            var button = up.settings.button;
                            //还原按钮文字及状态
                            $(button).prop("disabled", false).html($(button).data("bakup-html"));
                            var ret = Upload.events.onUploadResponse(info.response, info, up, file);
                            file.ret = ret;
                            if (ret.code === 1) {
                                Upload.events.onUploadSuccess(up, ret, file);
                            } else {
                                Upload.events.onUploadError(up, ret, file);
                            }
                        },
                        UploadComplete: Upload.events.onUploadComplete,
                        Error: function(up, err) {
                            var button = up.settings.button;
                            $(button).prop("disabled", false).html($(button).data("bakup-html"));
                            var ret = {
                                code: err.code,
                                msg: err.message,
                                data: null
                            };
                            Upload.events.onUploadError(up, ret);
                        }
                    },
                    onUploadSuccess: onUploadSuccess,
                    onUploadError: onUploadError,
                    onUploadComplete: onUploadComplete,
                    button: that
                });
                Upload.list[id].init();
            });
        },
    }
};
//将Table渲染至全局
window.Table = Table;