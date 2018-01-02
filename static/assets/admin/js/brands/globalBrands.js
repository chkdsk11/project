/**
 * Created by Administrator on 2016/8/26.
 */
//上传图片
$(document).on('change', '#move_logo,#move_image,#pc_logo,#pc_image', function(){
    uploadFile('/brands/upload', $(this).attr('id'), $(this).attr('data-img'));
});
$(function(){
    //编辑器
    var editor;
    KindEditor.ready(function(K) {
        editor = K.create('#brand_desc', {
            height: "280px",
            allowFileManager: true,
            uploadJson: '/brands/upload',
            items : ['source','formatblock','fontname','fontsize','forecolor','hilitecolor','bold','italic','underline','strikethrough','removeformat','justifyleft','justifycenter','justifyright','plainpaste','wordpaste','link','unlink','image','multiimage','clearhtml','fullscreen'],
            afterBlur: function(){this.sync();},
            afterCreate : function() {
                this.loadPlugin('autoheight');
            },
            afterUpload: function(url){
                console.log(url)
            }
        });
    });
    //检测类似的品牌名称
    $('.row').on('blur', '#brand_name', function(){
        var brand_name = $(':input[name=brand_name]').val();
        $('#brand_tig').text('');
        if(brand_name != '' && brand_name != brandName){
            $.ajax({
                url: '/brands/search',
                type: 'post',
                dataType: 'json',
                data: {brand_name: brand_name}
            })
            .done(function(data){
                if(data.status == 'success'){
                    $('#brand_tig').append('（已经添加相似的品牌有：<b style="color:blue;">' + data['data'][0]['brand_name'] + '</b>）');
                }
            });
        }
    });
    //提交form
    $("form").submit(function(){
        var brand_name = $('#brand_name').val();
        var brand_desc = $('#brand_desc').val();
        var brand_logo = $(':input[name=brand_logo]').val();
        var list_image = $(':input[name=list_image]').val();
        var mon_title = $(':input[name=mon_title]').val();
        var brand_describe = $(':input[name=brand_describe]').val();
        var pc_describe = $('#brand_describe_pc').val();
        var arrEntities={'lt':'<','gt':'>','nbsp':' ','amp':'&','quot':'"'};
        brandName = brandName.replace(/&(lt|gt|nbsp|amp|quot);/ig,function(all,t){return arrEntities[t];});
        if(brand_name != '' && brand_name != brandName){
            var is_return = false;
            $.ajax({
                url: '/brands/unique',
                type: 'post',
                dataType: 'json',
                async: false,
                data: {brand_name: brand_name}
            })
            .done(function(data){
                if(data.status == 'success'){
                    is_return = true;
                    layer_required('品牌名称已存在！');
                }else{
                    is_return = false;
                }
            });
            if(is_return){
                return false;
            }
        }
        if(brand_name == ''){
            layer_required('品牌名称不能为空！');
            return false;
        }
        // else if(brand_logo == ''){
        //     layer_required('请上传移动端logo图片！');
        //     return false;
        // }else if(list_image == ''){
        //     layer_required('请上传移动端品牌街列表图！');
        //     return false;
        // }
        else if(mon_title.length > 15){
            layer_required('移动端专场标题长度不能大于15字！');
            return false;
        }else if(brand_describe.length > 20){
            layer_required('移动端品牌描述长度不能大于20字！');
            return false;
        }else if(!regNumber.test($(':input[name=sort]').val())){
            layer_required('移动端排序只能为数字！');
            return false;
        }
        // else if($(':input[name=brand_logo_pc]').val() == ''){
        //     layer_required('请上传PC端logo图片！');
        //     return false;
        // }else if($(':input[name=list_image_pc]').val() == ''){
        //     layer_required('请上传PC端品牌街列表图！');
        //     return false;
        // }else if(pc_describe.length > 20){
        //     layer_required('PC端品牌描述长度不能大于20字！');
        //     return false;
        // }else if(!regNumber.test($(':input[name=sort_pc]').val())){
        //     layer_required('PC端排序只能为数字！');
        //     return false;
        // }
        ajaxSubmit('form');
        return false;
    });
    //删除操作
    $('.row').on('click', '.del', function(){
        layer_confirm('', '/brands/del', {id: $(this).attr('data-id')});
    });
    //修改排序
    $('.row').on('click', '.update', function(){
        var brand_id = $(this).attr('brand_id');
        var brand_sort = $(this).parents('.tools-box').find("input[name=sort]").val();
        if(brand_sort == ''){
            layer_required('请输入排序！');
        }
        $.ajax({
            url: '/brands/update',
            type: 'post',
            dataType: 'json',
            data: {
                'id' : brand_id,
                'brand_sort' : brand_sort
            }
        })
            .done(function(data){
                layer_required(data.info);
            });
    });
    //修改热门状态
    $('.row').on('click', '.is_hot', function(){
        var thisObj = $(this);
        var id = thisObj.attr('data-id');
        var is_hot = thisObj.attr('data');
        is_hot = is_hot == 1 ? 0 : 1;
        $.ajax({
                url: '/brands/update',
                type: 'post',
                dataType: 'json',
                data: {
                    'id' : id,
                    'is_hot' : is_hot
                }
            })
            .done(function(data){
                if(data.status == 'success'){
                    thisObj.attr('data', is_hot);
                    thisObj.removeClass();
                    if(is_hot == 1){
                        thisObj.addClass('ace-icon glyphicon btn-xs btn-info glyphicon-ok is_hot');
                    }else{
                        thisObj.addClass('ace-icon glyphicon btn-xs btn-danger glyphicon-remove is_hot');
                    }
                }else{
                    layer_required(data.info);
                }
            });
    });
});
