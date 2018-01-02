/**
 * Created by Administrator on 2016/9/6.
 */

$(function(){
    //三级分类获取属性
    $('.row').on('change', '#three_category', function(){
        var category_id = $('#three_category').val();
        $.ajax({
                url: '/attrname/list',
                type: 'post',
                dataType: 'json',
                data: {category_id: category_id}
            })
            .done(function(data){
                if(data.status == 'success'){
                    var string = [];
                    for(var i in data.data){
                        var status = data.data[i]['status'] == 1 ? '<i class="ace-icon glyphicon btn-xs btn-info glyphicon-ok status" data-id="' + data.data[i]['id'] + '" data="' + data.data[i]['status'] + '" style="cursor: pointer;"></i>' : '<i class="ace-icon glyphicon btn-xs btn-danger glyphicon-remove status" data-id="' + data.data[i]['id'] + '" data="' + data.data[i]['status'] + '" style="cursor: pointer;"></i>';
                        var is_null = data.data[i]['is_null'] == 1 ? '<i class="ace-icon glyphicon btn-xs btn-info glyphicon-ok is_null" data-id="' + data.data[i]['id'] + '" data="' + data.data[i]['is_null'] + '" style="cursor: pointer;"></i>' : '<i class="ace-icon glyphicon btn-xs btn-danger glyphicon-remove is_null" data-id="' + data.data[i]['id'] + '" data="' + data.data[i]['is_null'] + '" style="cursor: pointer;"></i>';
                        string.push('<tr>\
                                <td>' + data.data[i].id + '</td>\
                        <td>' + data.data[i].attr_name + '</td>\
                        <td>' + data.data[i].attr_value + '</td>\
                        <td>' + is_null + '</td>\
                        <td>' + status + '</td>\
                        <td>\
                            <div class="hidden-sm hidden-xs action-buttons">\
                                <a class="green" href="/attrname/edit?id=' + data.data[i].id + '&category_id=' + category_id + '" title="编辑">\
                                    <i class="ace-icon fa fa-pencil bigger-130"></i>\
                                </a>\
                                <a class="red del" href="javascript:;" data-id="' + data.data[i].id + '" title="删除">\
                                    <i class="ace-icon fa fa-trash-o bigger-130"></i>\
                                </a>\
                            </div>\
                            </td>\
                            </tr>');
                    }
                    $('tbody').empty().append(string.join(''));
                }else{
                    $('tbody').empty().append('<tr><td colspan="20">'+ data.info +'</td></tr>');
                }
            });
    });
    //更新启用状态
    $('.row').on('click', '.status', function(){
        var thisObj = $(this);
        var id = thisObj.attr('data-id');
        var status = thisObj.attr('data');
        status = status == 1 ? 0 : 1;
        $.ajax({
                url: '/attrname/update',
                type: 'post',
                dataType: 'json',
                data: {
                    'id' : id,
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
    //更新必填状态
    $('.row').on('click', '.is_null', function(){
        var thisObj = $(this);
        var id = thisObj.attr('data-id');
        var is_null = thisObj.attr('data');
        is_null = is_null == 1 ? 0 : 1;
        $.ajax({
                url: '/attrname/update',
                type: 'post',
                dataType: 'json',
                data: {
                    'id' : id,
                    'is_null' : is_null
                }
            })
            .done(function(data){
                if(data.status == 'success'){
                    thisObj.attr('data', is_null);
                    thisObj.removeClass();
                    if(is_null == 1){
                        thisObj.addClass('ace-icon glyphicon btn-xs btn-info glyphicon-ok is_null');
                    }else{
                        thisObj.addClass('ace-icon glyphicon btn-xs btn-danger glyphicon-remove is_null');
                    }
                }else{
                    layer_required(data.info);
                }
            });
    });
    //删除操作
    $('.row').on('click', '.del', function(){
        var _thisObj = $(this);
        layer.confirm(
            '确定要删除吗?',
            {icon: 2},
            function(index){
                layer.closeAll('dialog');
                $.ajax({
                        url: '/attrname/del',
                        type: 'post',
                        dataType: 'json',
                        data: {id: _thisObj.attr('data-id')}
                    })
                    .done(function(data){
                        if(data.status == 'success'){
                            _thisObj.parents('tr').toggle('slow', function(){
                                _thisObj.parents('tr').remove();
                            });
                        }else{
                            layer_required(data.info);
                        }
                    });
            }
        );
    });
    //添加跳转
    $('#addAttrValue').click(function(){
        var category_id = $('#three_category').val();
        if(!category_id || category_id == 0){
            layer_required('请选择完整分类！');
            return false;
        }
        window.location.href = '/attrname/add?category_id='+category_id;
    });
    //提交form
    $("#form").submit(function(){
        var valueJsonStr = {};
        var attr_name = ':input[name=attr_name]';
        if($(attr_name).val() == ''){
            layer_required('属性名称不能为空！');
            $(attr_name).focus();
        }else{
            var is_ok = 0;
            $('.attr_value').each(function(index) {
                if($(this).val() == ''){

                    layer_required('属性值不能为空！');
                    is_ok = 1;
                    return false;
                }
                valueJsonStr[index] = {};
                valueJsonStr[index]['id'] = $(this).attr('data-id');
                valueJsonStr[index]['attr_value'] = $(this).val();
            });
            $('input[name="attrValueJson"]').val(JSON.stringify(valueJsonStr));
            is_ok == 0 ? ajaxSubmit('form') : '';
        }
        return false;
    });
    //添加属性值
    $('#add').click(function(){
        var html = '<tr>\
                <td><input type="text" name="attr_value" class="attr_value" data-id=""></td>\
                <td><button class="btn btn-xs btn-danger delvalue" type="button">删除</button></td>\
                </tr>';
        $('#attr_table').append(html);
    });
    //导入属性
    $('#import_attr').click(function(){
        if($('#attr_file').val() == ''){
            layer_required('请选择导入的文件！');
            return false;
        }
        $.ajaxFileUpload({
            url: '/attrname/import',
            secureuri: false,
            fileElementId: 'attr_file',
            dataType: 'json',
            success: function (data, status) {
                if(data.status == 'success') {
                    $('.file-name').text('请选择导入的文件');
                    layer_required('导入成功！');
                    $('#three_category').change();
                } else {
                    if(data['info'][0] == '文件类型错误'){
                        layer_required('仅支持导入csv文件！');
                    }else{
                        layer_required(data['info']);
                    }
                }
            },
            error: function (data, status, e) {
                layer_required(e);
            }
        });
    });
    //删除属性值
    $(document).on('click', '.delvalue', function(){
        $(this).parents('tr').remove();
    })
});
