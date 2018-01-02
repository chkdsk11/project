/*!
 * @author liyuelong1020@gmail.com
 * @date 2017/6/7 007
 * @description 编辑移动端专题
 */


$(function () {

    // 返回给定范围内的随机整数
    var getRandomInt = function (min, max) {
        return Math.floor(Math.random() * (max - min + 1)) + min;
    };

    // 生成随机字符串
    var randomStr = function (len) {

        var str = 'qwertyuiopasdfghjklzcxvbnm';
        var result = '';
        var strLen = str.length - 1;

        while (result.length < len) {
            result += str[getRandomInt(0, strLen)];
        }

        return result;

    };


    // 生成插件css代码
    var createCssCode = function (widget, callback) {

        template.openTag = '{%';
        template.closeTag = '%}';

        var code = '#' + widget.id + '{ width: 100%; &::after{content:"";display: block;clear: both;width: 0;height: 0;}' + widget.widget.css_value + ' }';
        code = template.compile(code)(widget.config);

        template.openTag = '<%';
        template.closeTag = '%>';

        less.render(code, function (err, result) {
            if (err) {
                throw err.message;
            } else {
                callback(result.css);
            }

        });

    };

    // 生成插件html代码
    var createHTMLCode = function (widget) {

        template.openTag = '{%';
        template.closeTag = '%}';

        var code = template.compile(widget.widget.html_value)(widget.config);
        template.openTag = '<%';
        template.closeTag = '%>';

        return '<div id="' + widget.id + '">' + code + '</div>';
    };

    // 生成插件js代码
    var createJsCode = function (widget) {

        template.openTag = '{%';
        template.closeTag = '%}';

        var code = template.compile(widget.widget.javascript_value)(widget.config);

        template.openTag = '<%';
        template.closeTag = '%>';

        return '(function(){' + Babel.transform(code, {presets: ['react', 'es2015']}).code + '})();';
    };

    // 已添加的控件列表
    var addWidgetList = [];

    // 编辑控件属性值
    var setOptions = (function () {
        var paramNode = $('#current-widget');
        var temp = template.compile(paramNode.children('script').html());
        var currentWidget;

        // 上传图片文件
        var uploadImg = function (inputs, callback) {

            var fileSize = inputs.size();

            var onLoad = function () {
                fileSize--;

                if (fileSize < 1) {
                    callback();
                }
            };

            inputs.each(function () {
                var that = $(this);
                var file = this.files;

                if (file.length) {

                    var id = 'file_' + randomStr(10);
                    that.attr('id', id);

                    $.ajaxFileUpload({
                        url: '/subjectmobile/upload',
                        secureuri: false,
                        fileElementId: id,
                        dataType: 'json',
                        success: function (data, status) {
                            if (data.status == 'success') {

                                currentWidget.config[that.attr('name')] = data['data'][0]['src'];

                                onLoad();

                            } else {
                                alert(data.info);
                            }
                        },
                        error: function (data, status, e) {
                            alert(e);
                        }

                    });

                } else {

                    onLoad();

                }

            });

        };

        paramNode.hide().on('click', '#set-config', function () {
            console.log(currentWidget)
            if (currentWidget) {

                var param = paramNode.find('form').serializeArray();

                // 是否有文件修改
                var files = paramNode.find('form input[type="file"]');

                // 保存控件属性值
                $.each(param, function (i, item) {
                    currentWidget.config[item.name] = item.value;
                });

                // 如果有图片则先上传图片
                if (files.size()) {
                    uploadImg(files, render);
                } else {
                    // 刷新页面控件
                    render();
                }


            }

        });


        return function (widget) {
            if (widget &&
                widget.widget &&
                widget.widget.field &&
                widget.widget.field.length) {

                // 保存当前组件
                currentWidget = widget;
                // 显示编辑项
                paramNode.html(temp(widget)).show();

            } else {

                paramNode.hide();

            }
        };

    })();

    // 加载控件到专题预览
    var render = (function () {

        var wrap = $('#special-review');

        // 专题预览编辑效果css
        var editStyle = 'body > div > .edit-box {position: relative;padding:1px}body > div > .edit-box > p.edit-bar {display: none;position: absolute;z-index: 100000;background: #06f;width: 100%;height: 36px;margin: 0;line-height: 36px;text-align: right;color: #fff;}body > div > .edit-box > p.edit-bar span {display: inline-block;margin: 0 10px;cursor: pointer;}body > div > .edit-box:hover {border: 1px dashed #06f;border-top: 0;}body > div > .edit-box:hover > p.edit-bar {display: block;}';

        // 创建组件预览
        var createFrame = function (style, html, javascript) {

            // 创建预览iframe
            var frame = $('<iframe>').attr({
                width: '100%',
                height: 'auto',
                frameborder: 'no',
                border: '0',
                marginwidth: '0',
                marginheight: '0',
                scrolling: 'yes',
                allowtransparency: 'yes'
            });

            wrap.html(frame);
            var contentDocument = frame.get(0).contentDocument;

            var wrapId = 'sort-' + randomStr(16);

            // 将css，html，js替换至模板
            contentDocument.write(htmlTemplate
                .replace('{{style}}', '<style>' + editStyle + style + '</style>')
                .replace('{{html}}', '<div id="' + wrapId + '">' + html + '</div>')
                .replace('{{javascript}}', '<script>' + javascript + '</script>'));

            var htmlElement = contentDocument.documentElement;

            // 轮询直到iframe页面加载完毕
            var timer = setInterval(function () {

                if (contentDocument.body) {
                    clearInterval(timer);

                    var bodyElement = contentDocument.body;

                    // 设置预览iframe高度
                    frame.height(htmlElement.scrollHeight);

                    var sortWrap = $(bodyElement).find('#' + wrapId);
                    var editBox = sortWrap.children('.edit-box');

                    // 根据元素节点排序更新组件排序
                    var sort = function () {

                        var oldWidgetList = addWidgetList.concat();

                        sortWrap.children('.edit-box').each(function (i, c) {
                            var that = $(this);
                            var index = Number(that.attr('data-index'));
                            addWidgetList[i] = oldWidgetList[index];
                            that.attr('data-index', i);
                        });

                        oldWidgetList = null;

                    };

                    editBox.each(function () {
                        var that = $(this);

                        that.on('click', '.js-up', function () {
                                // 向上移动操作
                                var prev = that.prev();
                                prev.before(that);
                                sort();
                            })
                            .on('click', '.js-down', function () {
                                // 向下移动操作
                                var next = that.next();
                                next.after(that);
                                sort();

                            })
                            .on('click', '.js-edit', function () {
                                // 编辑操作
                                var index = Number(that.attr('data-index'));
                                setOptions(addWidgetList[index]);

                            })
                            .on('click', '.js-del', function () {
                                // 删除操作
                                var index = Number(that.attr('data-index'));
                                addWidgetList.splice(index, 1);

                                // 刷新页面控件
                                render();
                                setOptions();
                            });

                    });

                    // 绑定拖放操作
                    sortWrap.disableSelection().sortable({
                        stop: sort
                    });

                }

            }, 50);

        };


        return function () {

            var style = '', html = '', javascript = '';

            // 获取所有组件代码
            $.each(addWidgetList, function (i, widget) {
                createCssCode(widget, function (css) {
                    style += css;
                });

                // 模块添加编辑效果
                html += '<div class="edit-box" data-index="' + i + '" data-id="' + widget.widget.id + '">' +
                    '<p class="edit-bar">' +
                    '<span class="js-up">↑</span>' +
                    '<span class="js-down">↓</span>' +
                    '<span class="js-edit">编辑</span>' +
                    '<span class="js-del">删除</span>' +
                    '</p>' +
                    createHTMLCode(widget) +
                    '</div>';

                javascript += createJsCode(widget);
            });

            createFrame(style, html, javascript);

        }

    })();

    // 生成专题源码
    var getSpecialCode = function () {

        var style = '', html = '', javascript = '';

        // 获取所有组件代码
        $.each(addWidgetList, function (i, widget) {
            createCssCode(widget, function (css) {
                style += css;
            });

            // 模块添加编辑效果
            html += createHTMLCode(widget);

            javascript += createJsCode(widget);
        });

        return htmlTemplate
            .replace('{{style}}', '<style>' + style + '</style>')
            .replace('{{html}}', html)
            .replace('{{javascript}}', '<script>' + javascript + '</script>')
    };


    // 所有组件列表
    var widgetArray,

    // 专题组件与配置
        specialConfig,

    // 当前专题ID
        specialId = $('input[name="id"][type="hidden"]').val(),

    // 专题基础html模板
        htmlTemplate,

    // 加载组件列表与专题预览
        initSpecial = function () {

            $('#widget-list').each(function () {
                var that = $(this);
                var temp = that.children('script').html();

                if (widgetArray && widgetArray.length) {

                    // 显示所有控件
                    if (temp) {
                        temp = template.compile(temp)({widget: widgetArray});
                    }

                    // 点击添加控件
                    that.html(temp).on('click', '[data-index]', function () {
                        var index = $(this).attr('data-index');

                        var widgetItem = widgetArray[index];

                        if (widgetItem) {

                            // 保存控件id用于模板使用
                            var randomId = randomStr(16);

                            var newWidget = {
                                widget: widgetItem,
                                id: randomId,
                                config: {
                                    __id__: randomId,
                                    __special_id__: specialId
                                }
                            };

                            $.each(widgetItem.field, function (i, item) {
                                newWidget.config[item.field_name] = '';
                            });

                            // 将控件添加到已添加数组
                            addWidgetList.push(newWidget);

                            // 将控件加载到页面
                            render();

                            // 显示配置信息
                            setOptions(newWidget);
                        }

                    });

                }


            });

            // 加载旧有的控件
            if (specialConfig && specialConfig.length) {

                $.each(specialConfig, function (i, item) {

                    // 保存控件id用于模板使用
                    item.id = item.config.__id__ = randomStr(16);

                    // 专题Id
                    item.config.__special_id__ = specialId;

                    $.each(widgetArray, function (i, widget) {
                        if (widget.component_id == item.component_id) {
                            item.widget = widget;
                        }
                    });

                    if (item.widget) {
                        // 将控件添加到已添加数组
                        addWidgetList.push(item);
                    }

                });

                // 将控件加载到页面
                render();

            }

        };


    var loading = layer.load(20);

    $.post(location.pathname, {id: specialId}, function (data) {

        if (data && data.WidgetList && data.WidgetList.length && data.html && data.meta) {

            widgetArray = data.WidgetList;

            htmlTemplate = data.html;

            try {
                specialConfig = JSON.parse(data.oldWidget);
            } catch (e) {
                specialConfig = null;
            }

            initSpecial();

            layer.close(loading);
        }

    }, 'json');


    // 点击保存
    $('#save-special').on('click', function () {

        if (addWidgetList.length) {

            var WidgetList = [];

            $.each(addWidgetList, function (i, item) {
                WidgetList.push({
                    config: item.config,
                    component_id: item.widget.component_id
                });
            });

            loading = layer.load(20);

            $.post(location.pathname, {

                id: specialId,
                config: JSON.stringify(WidgetList),
                html: getSpecialCode(),
                url: '/subjectmobile/list'

            }, function (data) {

                layer.close(loading);

                if (data && data.status == 'success') {
                    layer_required('保存成功！');
                }

            });
        }

    });

    // 点击预览
    $('#review-special').on('click', function () {

        if (addWidgetList.length) {
            var newPage = window.open("about:blank", "专题预览");
            newPage.document.write(getSpecialCode());
        }

    });

});
