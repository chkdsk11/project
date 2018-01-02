/**
 * Created by yanbo
 */
$(function () {
    /**
     * ------------------------------------------------------------------------
     * 服务商管理
     * ------------------------------------------------------------------------
     */

    //更新服务商启用状态
    $('.row').on('click', '.providerStatus', function () {
        var thisObj = $(this);
        var id = thisObj.attr('data-id');
        var status = thisObj.attr('data');
        var msg = "开启后该服务商将启用短信服务，确定开启？";
        if (status == 0) {
            msg = "停用后该服务商将停止短信服务，确定停用？";
        }
        status = status == 0 ? 1 : 0;
        layer_confirm(msg, '/smsprovider/update', {provider_id: id, provider_state: status});
    });

    //更新发送比例
    $('.row').on('click', '#editScale', function () {
        var inputList = document.getElementsByTagName("input");
        var arr = [];
        var sum = 0;
        var flag = true;
        for (var i = 0; i < inputList.length; i++) {
            if (inputList[i].name && inputList[i].value) {
                arr.push({'name': inputList[i].name, 'value': inputList[i].value});
                sum += Number(inputList[i].value);
            } else {
                flag = false;
            }
        }
        if (!flag) {
            layer_required("比例输入不可为空");
            return false;
        }
        if (sum !== 1) {
            layer_required("比例之和必须为1");
            return false;
        }
        //去修改
        $.ajax({
            url: '/smsprovider/editscale',
            type: 'post',
            dataType: 'json',
            data: {'arr': JSON.stringify(arr)}
        })
                .done(function (data) {
                    if (data.status == 'success') {
                        parent.layer.close(1);
                    } else {
                        layer_required(data.info);
                    }
                });
    });

    //更新补发优先级
    $('.row').on('click', '#editPriority', function () {
        var inputList = document.getElementsByTagName("input");
        var arr = [];
        var flag = true;
        for (var i = 0; i < inputList.length; i++) {
            if (!(/(^[0-9]\d*$)/.test(inputList[i].value))) { //整数
                flag = false;
                break;
            }
            if (inputList[i].name && inputList[i].value) {
                arr.push({'name': inputList[i].name, 'value': inputList[i].value});
            }
        }
        if (!flag) {
            layer_required("补发优先级必填且为整数");
            return false;
        }
        //去修改
        $.ajax({
            url: '/smsprovider/editpriority',
            type: 'post',
            dataType: 'json',
            data: {'arr': JSON.stringify(arr)}
        })
                .done(function (data) {
                    if (data.status == 'success') {
                        parent.layer.close(1);
                    } else {
                        layer_required(data.info);
                    }
                });
    });

    //修改自动刷新周期
    $(".row").on('click', '.editFrequency', function () {
        var content = $(this).html();
        if (content == '修改') {
            $(this).html("确认修改");
            $(this).parents("tr").find('input').removeAttr('disabled');
        } else {
            var frequency = $(this).parents("tr").find('input').val();
            var provider_id = $(this).val();
            if (!provider_id) {
                layer_required("服务商不存在");
                return false;
            }
            if (!(/(^[0-9]\d*$)/.test(frequency)) || frequency <= 0) { //整数
                layer_required("不可为空且为正整数");
                return false;
            }
            //去修改
            $.ajax({
                url: '/smsprovider/editpw',
                type: 'post',
                dataType: 'json',
                data: {'name': 'editFrequency', 'provider_id': provider_id, 'frequency': frequency}
            })
                    .done(function (data) {
                        layer_required(data.info);
                        if (data.status == 'success') {
                            location.reload();
                        }
                    });
        }
    });

    //手动修改密码下拉框变化
    $("#changeProv").on('click', 'option', function () {
        var data_id = $(this).attr("data-id");
        if (data_id == 1) {
            $("#phoneInput").hide();
        } else {
            $("#phoneInput").show();
        }
    });

    //手动修改密码
    $(".row").on('click', '#editPw', function () {
        var provider_id = $("#changeProv option:selected").val();
        var password = $("input[name='password']").val();
        var phone = $("input[name='phone']").val();
        if (provider_id <= 0 || !provider_id) {
            layer_required("服务商不存在");
            return false;
        }
        if (!password) {
            layer_required("密码不能为空");
            return false;
        }
        //去修改
        $.ajax({
            url: '/smsprovider/editpw',
            type: 'post',
            dataType: 'json',
            data: {'name': 'editPw', 'provider_id': provider_id, 'password': password, 'phone': phone}
        })
                .done(function (data) {
                    layer_required(data.info);
                    if (data.status == 'success') {
                        location.reload();
                    }
                });
    });

    /**
     * ------------------------------------------------------------------------
     * 短信模板管理
     * ------------------------------------------------------------------------
     */

    //修改短信模板
    $('.row').on('click', '#editTemplate', function () {
        var template_id = $("input[name='template_id']").val();
        var signature = $("input[name='signature']").val();
        var content = $("textarea[name='content']").val();
        if (!$.trim(template_id)) {
            layer_required('获取该模板失败');
            return false;
        }
        if (!$.trim(signature)) {
            layer_required('使用签名不可为空');
            return false;
        }
        if (!$.trim(content) || content.length > 70) {
            layer_required('短信内容不可为空且不能多于70字');
            return false;
        }
        //去修改
        $.ajax({
            url: '/smstemplate/edit',
            type: 'post',
            dataType: 'json',
            data: {'template_id': template_id, 'signature': signature, 'content': content}
        })
                .done(function (data) {
                    if (data.status == 'success') {
                        parent.layer.close(1);
                    } else {
                        layer_required(data.info);
                    }
                });
    });

    //修改短信或图形验证
    $("input[name='client[]']").click(function () {
        var template_id = $(this).attr('data-tid');
        var client_id = $(this).attr('data-cid');
        var data_type = $(this).attr('data-type');
        var checked = $(this).is(':checked');
        if (!template_id || !client_id || !data_type) {
            layer_required('参数不全，数据有误');
            return false;
        }
        //去修改
        var datas = {
            'template_id': template_id,
            'client_id': client_id,
            'data_type': data_type,
            'status': checked ? 0 : 1
        };
        $.ajax({
            url: '/smsclient/edit',
            type: 'post',
            dataType: 'json',
            data: datas
        })
                .done(function (data) {
                    if (data.status == 'success') {
                        layer_required(data.info);
                    } else {
                        layer_required(data.info);
                        return false;
                    }
                });
    });

    /**
     * ------------------------------------------------------------------------
     * 警戒值管理
     * ------------------------------------------------------------------------
     */

    //修改警戒值设置
    $('.row').on('click', '.editAlarm', function () {
        var content = $(this).html();
        if (content == '修改') {
            $(this).html("确认修改");
            $(this).parents("div .form-horizontal").find('input').removeAttr('disabled');
        } else {
            var inputList = $(this).parents("div .form-horizontal").find('input');
            if (inputList.length > 0) {
                var arr = [];
                var flag = true;
                for (var i = 0; i < inputList.length; i++) {
                    if (!(/(^[0-9]\d*$)/.test(inputList[i].value))) { //整数
                        flag = false;
                        break;
                    }
                    if (inputList[i].name && inputList[i].value) {
                        arr.push({'name': inputList[i].name, 'value': inputList[i].value});
                    }
                }
                if (!flag) {
                    layer_required("不可为空且为整数");
                    return false;
                }
                //去修改
                $.ajax({
                    url: '/smsalarm/edit',
                    type: 'post',
                    dataType: 'json',
                    data: {'arr': JSON.stringify(arr)}
                })
                        .done(function (data) {
                            if (data.status == 'success') {
                                layer_required(data.info);
                                location.reload();
                            } else {
                                layer_required(data.info);
                            }
                        });
            }
        }
    });

    /**
     * ------------------------------------------------------------------------
     * 预警通知管理
     * ------------------------------------------------------------------------
     */

    //删除预警通知人
    $('.row').on('click', '.delnotify', function () {
        var thisObj = $(this);
        var id = thisObj.attr('data-id');
        var name = thisObj.attr('data-name');
        var msg = "确认删除此通知人?</br>" + name;
        layer_confirm(msg, '/smsnotify/del', {notify_user_id: id});
    });

    //添加预警通知人
    $('.row').on('click', '#addNotify', function () {
        var user_name = $("input[name='user_name']").val();
        var phone = $("input[name='phone']").val();
        if (!$.trim(user_name)) {
            layer_required('通知人不可为空');
            return false;
        }
        var regMobliePhone = /^1[3|4|5|7|8][0-9]\d{8}$/;
        if (!regMobliePhone.test(phone)) {
            layer_required('请填写正确格式手机号');
            return false;
        }
        //去修改
        $.ajax({
            url: '/smsnotify/add',
            type: 'post',
            dataType: 'json',
            data: {'user_name': user_name, 'phone': phone}
        })
                .done(function (data) {
                    if (data.status == 'success') {
                        parent.layer.close(1);
                    } else {
                        layer_required(data.info);
                    }
                });
    });

    //更新短信预警通知启用状态
    $('.row').on('click', '#changNotify', function () {
        layer_confirm('确定修改短信预警通知状态么？', '/smsnotify/update', []);
    });

    /**
     * ------------------------------------------------------------------------
     * 黑白名单管理
     * ------------------------------------------------------------------------
     */

    //添加黑名单
    $('.row').on('click', '#addBlackList', function () {
        var ip_address = $("input[name='ip_address']").val();
        var phone = $("input[name='phone']").val();
        if (!$.trim(ip_address) && !$.trim(phone)) {
            layer_required('IP或手机号至少填写一项');
            return false;
        }
        if ($.trim(ip_address)) {
            var regIp = /((2[0-4]\d|25[0-5]|[01]?\d\d?)\.){3}(2[0-4]\d|25[0-5]|[01]?\d\d?)/;
            if (!regIp.test(ip_address)) {
                layer_required('请填写正确格式IP');
                return false;
            }
        }
        if ($.trim(phone)) {
            var regMobliePhone = /^1[3|4|5|7|8][0-9]\d{8}$/;
            if (!regMobliePhone.test(phone)) {
                layer_required('请填写正确格式手机号');
                return false;
            }
        }
        //去修改
        $.ajax({
            url: '/smslist/addblack',
            type: 'post',
            dataType: 'json',
            data: {'ip_address': ip_address, 'phone': phone}
        })
                .done(function (data) {
                    if (data.status == 'success') {
                        parent.layer.close(1);
                    } else {
                        layer_required(data.info);
                    }
                });
    });

    //解除黑名单
    $('.row').on('click', '.delBlackList', function () {
        var thisObj = $(this);
        var id = thisObj.attr('data-id');
        var ip = thisObj.attr('ip');
        var phone = thisObj.attr('phone');
        var msg = "确认解除黑名单？";
        if (ip) {
            msg += "</br> IP:" + ip;
        }
        if (phone) {
            msg += "</br> 手机号：" + phone;
        }
        layer_confirm(msg, '/smslist/delblack', {list_id: id});
    });

    //添加白名单
    $('.row').on('click', '#addWhiteList', function () {
        var ip_address = $("input[name='ip_address']").val();
        var phone = $("input[name='phone']").val();
        if (!$.trim(ip_address) && !$.trim(phone)) {
            layer_required('IP或手机号至少填写一项');
            return false;
        }
        if ($.trim(ip_address)) {
            var regIp = /((2[0-4]\d|25[0-5]|[01]?\d\d?)\.){3}(2[0-4]\d|25[0-5]|[01]?\d\d?)/;
            if (!regIp.test(ip_address)) {
                layer_required('请填写正确格式IP');
                return false;
            }
        }
        if ($.trim(phone)) {
            var regMobliePhone = /^1[3|4|5|7|8][0-9]\d{8}$/;
            if (!regMobliePhone.test(phone)) {
                layer_required('请填写正确格式手机号');
                return false;
            }
        }
        //去修改
        $.ajax({
            url: '/smslist/addwhite',
            type: 'post',
            dataType: 'json',
            data: {'ip_address': ip_address, 'phone': phone}
        })
                .done(function (data) {
                    if (data.status == 'success') {
                        parent.layer.close(1);
                    } else {
                        layer_required(data.info);
                    }
                });
    });

    //解除白名单
    $('.row').on('click', '.delWhiteList', function () {
        var thisObj = $(this);
        var id = thisObj.attr('data-id');
        var ip = thisObj.attr('ip');
        var phone = thisObj.attr('phone');
        var msg = "确认解除白名单？";
        if (ip) {
            msg += "</br> IP:" + ip;
        }
        if (phone) {
            msg += "</br> 手机号：" + phone;
        }
        layer_confirm(msg, '/smslist/delwhite', {list_id: id});
    });

});
