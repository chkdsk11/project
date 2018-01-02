

$('#frist_select').change(function(){
    $('#second_select').hide();
    $('#third_select').hide();
    var id = $(this).val();
    $('#ad_position').val(id);
    if(id<=0){
        return false;
    }
    $.post('/ad/ajax_get_position',{postition_id:id},function(data){
        if(data.code=200){
            var list = data.data;
            if(data.data.length>0){
                var html='<option value="0">请选择</option>';
                for(var i = 0;i< data.data.length;i++ ){
                    html +='<option value="'+list[i].id+'">'+list[i].adpositionid_name+'</option>';
                }
                $('#second_select').html(html);
                $('#second_select').show();

            }
        }
    });
});

$('#second_select').change(function(){
    $('#third_select').hide();
    $('#fourth_select').hide();
    var id = $(this).val();
    if(id<=0){
        return false;
    }
    $('#ad_position').val(id);
    $.post('/ad/ajax_get_position',{postition_id:id},function(data){
        if(data.code=200){
            var list = data.data;
            if(data.data.length>0){
                var html='<option value="0">请选择</option>';
                for(var i = 0;i< data.data.length;i++ ){
                    html +='<option value="'+list[i].id+'">'+list[i].adpositionid_name+'</option>';
                }
                $('#third_select').html(html);
                $('#third_select').show();

            }
        }
    });

});
$('#third_select').change(function(){
    $('#fourth_select').hide();
    var id = $(this).val();
    if(id<=0){
        return false;
    }
    $('#ad_position').val(id);
    $.post('/ad/ajax_get_position',{postition_id:id},function(data){
        if(data.code=200){
            var list = data.data;
            if(data.data.length>0){
                var html='<option value="0">请选择</option>';
                for(var i = 0;i< data.data.length;i++ ){
                    html +='<option value="'+list[i].id+'">'+list[i].adpositionid_name+'</option>';
                }
                $('#fourth_select').html(html);
                $('#fourth_select').show();
            }
        }
    });
});

$('#fourth_select').change(function(){
    var id = $(this).val();
    $('#ad_position').val(id);
});