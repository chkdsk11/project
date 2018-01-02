/******************** 广告分类三级联动 ***********************/
$(function(){
    var one_postition = $('#one_postition'); //一级分类
    var two_postition = $('#two_postition'); //二级分类
    var three_postition = $('#three_postition'); //三级分类
    var postition_son = $('.postition_son').html();//是否需要无限极分类
    var postitionBox = $('#postitionBox');

    //输出一级分类下面的子分类
    one_postition.on('change',function(){
        //初始化城市选项名
        two_postition.find("option").remove();
        $('#three_postition').find("option").remove();
        two_postition.append('<option value="0">--请选择--</option>');
        $('#three_postition').hide();
        var one_postition_id = one_postition.val();
        if(one_postition_id == 0){
            if(postition_son != undefined){
                $('.postition_son').html('');
            }
            return;
        }
        $.post('/ad/getPosition',{pid: one_postition_id},function(data){
            if(!data){
                return;
            }
            if(data.status == 'success'){
                if(data.data.length > 0){
                    two_postition.show();
                    for(var i = 0;i < data.data.length;i++){
                        two_postition.append('<option value="'+data.data[i]['id']+'">'+data.data[i]['adpositionid_name']+'('+data.data[i]['versions']+')['+data.data[i]['channel']+']</option>');
                    }
                    if(postition_son != undefined){
                        $('.postition_son').html('');
                    }
                }
            }
        },"json");

    });

    //输出二级分类下面的子分类
    two_postition.on('change',function(){
        //初始化区县选项名
        three_postition.find("option").remove();
        three_postition.append('<option value="0">--请选择--</option>');
        var two_postition_id = two_postition.val();
        if(two_postition_id == 0){
            if(postition_son != undefined){
                $('.postition_son').html('');
            }
            return;
        }
        $.post('/ad/getPosition',{pid: two_postition_id},function(data){
            if(!data){
                if(postition_son != undefined){
                    $('.postition_son').html('');
                }
                return;
            }
            if(data.status == 'success'){
                if(data.data.length > 0){
                    three_postition.show();
                    for(var i = 0;i < data.data.length;i++){
                        three_postition.append('<option value="'+data.data[i]['id']+'">'+data.data[i]['adpositionid_name']+'('+data.data[i]['versions']+')['+data.data[i]['channel']+']</option>');
                    }
                    if(postition_son != undefined){
                        $('.postition_son').html('');
                    }
                }
            }
        },"json");
    });
    if(postition_son != undefined){
        //无限极分类
        $("#three_postition").on('change',function(){
            var one_postition_id = $(this).val();
            if(one_postition_id == 0){
                if(postition_son != undefined){
                    $('.postition_son').html('');
                }
                return;
            }
            $('.postition_son').html('');
            $.post('/ad/getPosition',{pid: one_postition_id},function(data){
                if(!data){
                    if(postition_son != undefined){
                        $('.postition_son').html('');
                    }
                    return;
                }
                if(data.status == 'success'){
                    if(data.data.length > 0){
                        var html = '<select name="ad_position[]" class="postition_infinite sku_menu_row1">';
                        html += '<option value="">--请选择--</option>';
                        for(var i = 0;i < data.data.length;i++){
                            html += '<option value="'+data.data[i]['id']+'">'+data.data[i]['adpositionid_name']+'('+data.data[i]['versions']+')['+data.data[i]['channel']+']</option>';
                        }
                        html += '</select>';
                        $('.postition_son').append(html);
                    }
                }
            },"json");
        });
        $(document).on('change','.postition_infinite',function(){
            var one_postition_id = $(this).val();
            if(one_postition_id == 0){
                $(this).nextAll().remove();
                return;
            }
            var th = this;
            $.post('/ad/getPosition',{pid: one_postition_id},function(data){
                if(!data){
                    $(th).nextAll().remove();
                    return;
                }
                if(data.status == 'success'){
                    if(data.data.length > 0){
                        var html = '<select name="ad_position[]" class="postition_infinite sku_menu_row1">';
                        html += '<option value="">--请选择--</option>';
                        for(var i = 0;i < data.data.length;i++){
                            html += '<option value="'+data.data[i]['id']+'">'+data.data[i]['adpositionid_name']+'('+data.data[i]['versions']+')['+data.data[i]['channel']+']</option>';
                        }
                        html += '</select>';
                        $('.postition_son').append(html);
                    }else{
                        $(th).nextAll().remove();
                    }
                }
            },"json");
        });
    }
})