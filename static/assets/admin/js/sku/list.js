$(function(){
    //点击处理上下架
//        $(document).on('click','.is_on_sale',function(){
//            var id = $(this).attr('data-id');
//            var act = $(this).attr('name');
//            var th = this;
//            var url = "/sku/setSale";
//            var data = {'id':id,'act':act};
//            ajaxSet(th,url,data);
//        });
    //点击处理热门商品
    $(document).on('click','.is_hot',function(){
        var id = $(this).attr('data-id');
        var act = $(this).attr('name');
        var th = this;
        var url = "/sku/setHot";
        var data = {'id':id,'act':act};
        ajaxSet(th,url,data);
    });
    //点击处理推荐商品
    $(document).on('click','.is_recommend',function(){
        var id = $(this).attr('data-id');
        var act = $(this).attr('name');
        var th = this;
        var url = "/sku/setRecommend";
        var data = {'id':id,'act':act};
        ajaxSet(th,url,data);
    });
    //点击处理商品是否锁定
    $(document).on('click','.is_lock',function(){
        var id = $(this).attr('data-id');
        var act = $(this).attr('name');
        var th = this;
        var url = "/sku/setIsLock";
        var data = {'id':id,'act':act};
        ajaxSet(th,url,data);
    });
    //批量上下架处理
    /*设置变量，保存选取的商品*/
    var data_sale = {};
    $('.is_shelves').click(function(){
        var type = $(this).attr('data-id');
        var data = {};
        var i = 1;
        $("input:checkbox[name='checkbox']:checked").each(function() {
            data[i] = $(this).val();
            i++;
        });
        if(i==1){
            layer_required('请先选择商品');
            return;
        }
        $('#popup_sale').removeClass('hide');
        data['shelves'] = type;
        data_sale = data;
    });
    //修改上下架信息
    $(document).on('click','#save_stock',function(){
        var url = '/sku/setSales';
        var i = 1;
        data_sale['stock_checkbox'] = {};
        $("input:checkbox[name='stock_checkbox']:checked").each(function() {
            data_sale['stock_checkbox'][i] = $(this).val();
            i++;
        });
//            console.log(data_sale)
        $('#popup_sale').addClass('hide');
        $.ajax({
            type: 'post',
            url: url,
            data: data_sale,
            cache: false,
            dataType:'json',
            success: function(msg){
                if(msg.status == 'success'){
                    if(msg.info != ''){
                        layer_required(msg.info);
                        window.setTimeout("sleepHref()",3000);
                    }else{
                        window.location.href=location.href;
                    }
                }else{
                    layer_required(msg.info);
                }
            }
        });
    });
    //跟换显示上下架状态
    $(document).on('change','.change_sale',function(){
        var type = $(this).val();
//            console.log(change_sale)
        var i = 0,
            len = change_sale.length;
        for(i;i<len;i++){
            if(type == 'pc'){
                if( change_sale[i]['is_on_sale'] == 1 ){
                    $('.sale_is_points_'+change_sale[i]['id']).html('<span>上架</span>');
                }else if( change_sale[i]['is_on_sale'] == 0 ){
                    $('.sale_is_points_'+change_sale[i]['id']).html('<span>下架</span>');
                }
            }else if( type == 'app' ){
                if( change_sale[i]['sale_timing_app'] == 1 ){
                    $('.sale_is_points_'+change_sale[i]['id']).html('<span>上架</span>');
                }else if( change_sale[i]['sale_timing_app'] == 0 ){
                    $('.sale_is_points_'+change_sale[i]['id']).html('<span>下架</span>');
                }
            }else if( type == 'wap' ){
                if( change_sale[i]['sale_timing_wap'] == 1 ){
                    $('.sale_is_points_'+change_sale[i]['id']).html('<span>上架</span>');
                }else if( change_sale[i]['sale_timing_wap'] == 0 ){
                    $('.sale_is_points_'+change_sale[i]['id']).html('<span>下架</span>');
                }
            }else if( type == 'wechat' ){
                if( change_sale[i]['sale_timing_wechat'] == 1 ){
                    $('.sale_is_points_'+change_sale[i]['id']).html('<span>上架</span>');
                }else if( change_sale[i]['sale_timing_wechat'] == 0 ){
                    $('.sale_is_points_'+change_sale[i]['id']).html('<span>下架</span>');
                }
            }else{
                var html = '';
                if( change_sale[i]['is_on_sale'] == 1 || change_sale[i]['sale_timing_wap'] == 1 || change_sale[i]['sale_timing_app'] == 1 || change_sale[i]['sale_timing_wechat'] == 1){
                    html += '<span>上架</span>';
                }
                if( change_sale[i]['is_on_sale'] == 0 || change_sale[i]['sale_timing_wap'] == 0 || change_sale[i]['sale_timing_app'] == 0 || change_sale[i]['sale_timing_wechat'] == 0){
                    html += '<br/><span>下架</span>';
                }
                $('.sale_is_points_'+change_sale[i]['id']).html(html);
            }
        }
    });
    //隐藏弹窗
    $(document).on('click','#close_popup_stock',function(){
        $('#popup_sale').addClass('hide');
    });
    //全选功能
    $('.checkbox_select').change(function(){
        var act = $(this).prop('checked');
        $("input:checkbox[name='checkbox']").each(function() {
            $(this).prop('checked',act);
        });
    });
    $(document).keypress(function (e) {
        if( e.which == 13 ){
            return false;
        }
    });
    //双击编辑排序信息
    $(document).on('dblclick','.update_sort',function(){
        var id = $(this).parent().find('input').val();
        var sort = parseInt($(this).text());
        if(sort === ''){
            sort=1;
        }
        var html = '<input type="text" class="update_sort_num" style="width: 98%;text-align:center;" value="'+sort+'">';
        $(this).html(html);
    });

    $('#btn-export').on('click', function() {
        $('#popup-export').show();
    });

    $('.js-close').on('click', function() {
        $('#popup-export').hide();
    });

    $('#btnokspu').click(function(){
        var len = $('#Echeckboxspu .select-item');
        if($("#btnokspu input").prop("checked"))
        {
            var flag = true;
        }else{
            var flag = false;
        }
        for(var i=0;i<len.length;i++)
        {
            if(flag)
            {
                $(len[i]).find('input').prop('checked','checked');
            }else{
                $(len[i]).find('input').prop('checked','');
            }
        }
    });

    $('#btnokattr').click(function(){
        var len = $('#Echeckboxattr .select-item');
        if($("#btnokattr input").prop("checked"))
        {
            var flag = true;
        }else{
            var flag = false;
        }
        for(var i=0;i<len.length;i++)
        {
            if(flag)
            {
                $(len[i]).find('input').prop('checked','checked');
            }else{
                $(len[i]).find('input').prop('checked','');
            }
        }
    });

    $('#btnokgoods').click(function(){
        var len = $('#Echeckboxgoods .select-item');
        if($("#btnokgoods input").prop("checked"))
        {
            var flag = true;
        }else{
            var flag = false;
        }
        for(var i=0;i<len.length;i++)
        {
            if(flag)
            {
                $(len[i]).find('input').prop('checked','checked');
            }else{
                $(len[i]).find('input').prop('checked','');
            }
        }
    });

    //导入商品
    var import_error_msg = '';
    $('#import').click(function(){
        if(import_error_msg){
            layer_required(import_error_msg);
            return false;
        }
        if($('#files').val() == ''){
           // layer_required('请选择导入的文件！');
            return false;
        }
        var import_type = $('#import_type option:selected') .val();
        $.ajaxFileUpload({
            url: '/spu/import',
            data: { import_type: import_type },
            secureuri: false,
            fileElementId: 'files',
            dataType: 'json',
            success: function (data, status) {
                if(data.status == 'success') {
                    $('.file-name').text('请选择导入的文件');
                    layer_required('导入成功！');
                } else {
                    if(data['info'][0] == '文件类型错误'){
                        import_error_msg = "仅支持导入xlsx文件！";
                        layer_required('仅支持导入xlsx文件！');
                        sleepHref();
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
    //失去焦点时修改商品排序
    $(document).on('blur','.update_sort_num',function(){
        var id = $(this).parent().parent().find('input').val();
        var sort = $(this).val();
        if(sort == '' || !regNumber.test(sort)){
            sort = 1;
        }
        var th = this;
        $.ajax({
            type: 'get',
            url: '/Sku/setSkuSort',
            data: 'id='+id+'&sort='+sort,
            cache: false,
            dataType:'json',
            success: function(msg){
                if(msg){
                    $(th).parent().html(sort);
                }else{
                    layer_required('修改失败！');
                }
            }
        });
    });
})
function sleepHref(){
    window.location.href=location.href;
}
function ajaxSet(th,url,data){
    $.ajax({
        type: 'post',
        url: url,
        data: data,
        cache: false,
        dataType:'json',
        success: function(msg){
            if(msg.start == 'success'){
                if(msg.data == 1){
                    $(th).html('<i class="ace-icon glyphicon btn-xs btn-info glyphicon-ok"></i>');
                    $(th).attr('name',msg.data);
                }else{
                    $(th).html('<i class="ace-icon glyphicon btn-xs btn-danger glyphicon-remove"></i>');
                    $(th).attr('name',msg.data);
                }
            }else{
                layer.load(20, {time: 1});
                layer.msg(msg.info);return false;
            }
        }
    });
}
function submit_search(){
    var act = true;
    var one_category = $('#one_category').val();
    if( one_category > 0){
        act = isCategorySelect();
    }
    return act;
}