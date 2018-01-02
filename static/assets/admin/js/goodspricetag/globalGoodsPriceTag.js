/**
 * Created by Administrator on 2016/9/6.
 */

$(function(){
    //提交form
    $("#form").submit(function(){
        if ($('#tag_name').val() == ''){
            layer_required('会员标签名称不能为空！');
            return false;
        }
        if ($('#remark').val() == ''){
            layer_required('标签备注不能为空！');
            return false;
        }
        ajaxSubmit('form');
        return false;
    });
    //修改启用状态
    $('.row').on('click', '.status', function(){
        var thisObj = $(this);
        var id = thisObj.attr('data-id');
        var status = thisObj.attr('data');
        status = status == 1 ? 0 : 1;
        $.ajax({
                url: '/goodspricetag/edit',
                type: 'post',
                dataType: 'json',
                data: {
                    'tag_id' : id,
                    'status' : status
                }
            })
            .done(function(data){
                if(data.status == 'success'){
                    thisObj.attr('data', status);
                    thisObj.removeClass();
                    if(status == 1){
                        thisObj.addClass('ace-icon glyphicon btn-xs btn-info glyphicon-ok status');
                    }else{
                        thisObj.addClass('ace-icon glyphicon btn-xs btn-danger glyphicon-remove status');
                    }
                }else{
                    layer_required(data.info);
                }
            });
    });
    $('.row').on('click', '.update', function(){
        var tag_id = $(this).attr('tagid');
        var user_id = $(this).attr('userid');
        var remark = $(this).parents('.tools-box').find("input[name=remark]").val();
        if(remark == ''){
            layer_required('请输入备注信息！');
        }
        $.ajax({
            url: '/goodspricetag/edittag',
            type: 'post',
            dataType: 'json',
            data: {
                'tag_id' : tag_id,
                'user_id' : user_id,
                'remark' : remark
            }
        })
        .done(function(data){
            layer_required(data.info);
        });
    });
    //删除操作
    $('.row').on('click', '.del', function(){
        var request = location.search;
        layer_confirm('', '/goodspricetag/del', {id: $(this).attr('data-id'),request :request});
    });
    //删除会员标签操作
    $('.row').on('click', '.deltag', function(){
        var request = location.search;
        layer_confirm('', '/goodspricetag/deltag', {tag_id: $(this).attr('data-id'), user_id: $(this).attr('u-id'),request :request});
    });
    //全选功能
    $('.checkbox_select').change(function(){
        var act = $(this).prop('checked');
        $("input:checkbox[name='checkbox']").each(function() {
            $(this).prop('checked',act);
        });
    });
    //批量删除
    $('#batchdel').click(function(){
        var i = 1;
        var Arr = [];
        $("input:checkbox[name='checkbox']:checked").each(function() {
            var data = {};
            data['tag_id'] = $(this).val();
            data['user_id'] = $(this).attr('u-id');
            Arr.push(data);
            i++;
        });

        if(i==1){
            layer_required('请选择要删除的内容!');
            return;
        }
        var j = i-1;
        if (confirm("共"+j+"个,真的要删除吗？")) {
            $.ajax({
                url: '/goodspricetag/betchdel',
                type: 'post',
                dataType: 'json',
                data: {
                    'data': Arr,
                },
                success: function (data, status) {
                    if (data.status == 'success') {
                        layer_success(data.info, '/goodspricetag/bindmemberlist');
                    } else {
                        layer_required(data.info);
                    }
                },
                error: function (data, status, e) {
                    layer_required(e);
                }
            });
        }else{
            return false;
        }
    });
    //导入属性
    $('#import').click(function(){
        var tag_id = $(':input[name=tag_id]').val();
        if(tag_id == 0){
            layer_required('请选择标签名！');
            return false;
        }
        if($('#files').val() == ''){
            layer_required('请选择导入的文件！');
            return false;
        }
        $.ajaxFileUpload({
            url: '/goodspricetag/importtag',
            data: {tag_id: tag_id},
            secureuri: false,
            fileElementId: 'files',
            dataType: 'json',
            success: function (data, status) {
                if(data.status == 'success') {
                    layer_success(data.info, '/goodspricetag/bindmemberlist');
                } else {
                    if(data['info'][0] == '文件类型错误'){
                        layer_required('仅支持导入csv文件！');
                    }else{
                        typeof(data['data']) == "undefined" ? layer_required(data['info'][0]) : layer_required(data['info']);
                    }
                }
            },
            error: function (data, status, e) {
                layer_required(e);
            }
        });
    });
    //添加会员绑定标签
    $('#addtag').click(function(){
        var tag_id = $(':input[name=tagid]').val();
        var phone = $(':input[name=phone]').val();
        if(phone == ''){
            layer_required('请输入手机号！');
            return false;
        }
        if(tag_id == 0){
            layer_required('请选择标签名！');
            return false;
        }
        ajaxFn('/goodspricetag/addtag', {tag_id: tag_id, phone: phone}, 'POST');
    });
    //修改单个会员
    $('.row').on('click', '.userstatus', function(){
        var thisObj = $(this);
        var user_id = thisObj.attr('user-id');
        var id = thisObj.attr('data-id');
        var status = thisObj.attr('data');
        status = status == 1 ? 0 : 1;
        $.ajax({
            url: '/goodspricetag/edituser',
            type: 'post',
            dataType: 'json',
            data: {
                'user_id': user_id,
                'tag_id' : id,
                'status' : status
            }
        })
            .done(function(data){
                if(data.status == 'success'){
                    thisObj.attr('data', status);
                    thisObj.removeClass();
                    if(status == 1){
                        thisObj.addClass('ace-icon glyphicon btn-xs btn-info glyphicon-ok userstatus');
                    }else{
                        thisObj.addClass('ace-icon glyphicon btn-xs btn-danger glyphicon-remove userstatus');
                    }
                }else{
                    layer_required(data.info);
                }
            });
    });
});
