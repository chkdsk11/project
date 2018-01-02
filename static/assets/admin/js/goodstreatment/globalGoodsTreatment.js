/**
 * Created by Administrator on 2016/9/6.
 */

$(function(){
    //更新启用状态
    $('.row').on('click', '.status', function(){
        var thisObj = $(this);
        var id = thisObj.attr('data-id');
        var status = thisObj.attr('data');
        status = status == 1 ? 2 : 1;
        $.ajax({
                url: '/goodstreatment/update',
                type: 'post',
                dataType: 'json',
                data: {
                    'goods_id' : id,
                    'status' : status
                }
            })
            .done(function(data){
                if(data.status == 'success'){
                    thisObj.attr('data', status);
                    thisObj.removeClass();
                    if(status == 1){
                        thisObj.text('进行中').addClass('ace-icon glyphicon btn-xs btn-info glyphicon-time status');
                    }else{
                        thisObj.text('暂停').addClass('ace-icon glyphicon btn-xs btn-danger glyphicon-off status');
                    }
                }else{
                    layer_required(data.info);
                }
            });
    });
    //删除操作
    $('.row').on('click', '.del', function(){
        var request = location.search;
        layer_confirm('确定要取消吗', '/goodstreatment/update', {goods_id: $(this).attr('data-id'), status: 0,request :request});
    });
    //提交form
    $("#search").submit(function(){
        var goods_name = $(':input[name=goods_name]').val();
        if(goods_name == ''){
            layer_required('请输入商品名称或ID！');
            return false;
        }
        $.ajax({
            url: '/sku/search',
            type: 'post',
            dataType: 'json',
            data: {'goods_name': goods_name}
        })
        .done(function(data){
            if(data.status == 'success'){
                var string = [];
                string.push('<option value="">请选择商品</option>');
                for(var i=0;i<data.data.length;i++){
                    string.push('<option value="' + data.data[i].id + '">' + data.data[i].goods_name + '</option>');
                }
                $('#showgoods').show();
                $('#goods').empty().append(string.join(''));
            }else{
                layer_required(data.info);
            }
        });
        return false;
    });
    //选择商品
    $('#goods').change(function(){
        var goods_id = $(this).val();
        var use_platform_arr=[];
        $(':input[name=platform_pc]').is(':checked') ? use_platform_arr.push('pc'):'';
        $(':input[name=platform_app]').is(':checked')? use_platform_arr.push('app'):'';
        $(':input[name=platform_wap]').is(':checked')? use_platform_arr.push('wap'):'';
        $(':input[name=platform_wechat]').is(':checked')? use_platform_arr.push('wechat'):'';
        if(goods_id != ''){
            $.post('/goods/isOnShelf',{platform:use_platform_arr,ids:goods_id},function (res) {
                if(res.code == '400'){
                    layer_required(res.msg);
                    return false;
                }

                $.ajax({
                    url: '/goodstreatment/search',
                    type: 'post',
                    dataType: 'json',
                    data: {'goods_id': goods_id}
                })

                    .done(function(data){
                        var html = '<tr height="35px;">\
                    <td><input type="text" name="min_goods_number" id="min_goods_number"></td>\
                    <td><input type="text" name="unit_price" id="unit_price"></td>\
                    <td><input type="text" name="promotion_msg" id="promotion_msg"></td>\
                    <td><input type="button" class="btn btn-xs btn-primary" id="save" value="保存" /></td>\
                    </tr>';

                        if(data.status == 'success'){
                            if(data.data.length > 0){
                                $('.ace').prop('checked', false);
                                data.data[0]['platform_pc'] == 1 ? $(':input[name=platform_pc]').prop('checked', true) : $(':input[name=platform_pc]').prop('checked', false);
                                data.data[0]['platform_app'] == 1 ? $(':input[name=platform_app]').prop('checked', true) : $(':input[name=platform_app]').prop('checked', false);
                                data.data[0]['platform_wap'] == 1 ? $(':input[name=platform_wap]').prop('checked', true) : $(':input[name=platform_wap]').prop('checked', false);
                                data.data[0]['platform_wechat'] == 1 ? $(':input[name=platform_wechat]').prop('checked', true) : $(':input[name=platform_wechat]').prop('checked', false);
                                $("input[name='promotion_mutex[]']").each(function(){
                                    for(var j=0;j<data.data[0]['promotion_mutex'].length;j++){
                                        if($(this).val() == data.data[0]['promotion_mutex'][j]){
                                            $(this).prop('checked', true);
                                        }
                                    }
                                });

                            }

                            for(var i=0;i<data.data.length;i++){
                                html += '<tr height="35px;" class="allsave" id="ids' + data.data[i]['id'] + '">\
                        <td><input type="checkbox" onclick="checkall();" class="batchdel" name="id" value="' + data.data[i]['id'] + '">&nbsp;&nbsp;' + data.data[i]['min_goods_number'] + '</td>\
                        <td>' + data.data[i]['unit_price'] + '</td>\
                        <td>' + data.data[i]['promotion_msg'] + '</td>\
                        <td><input type="button" class="btn btn-xs btn-danger delete" data-id="' + data.data[i]['id'] + '" value="删除" /></td>\
                        </tr>';
                            }
                        }
                        $('tbody').html(html);
                    });
            });
        }
    });
    $('.row').on('click', '.delete', function(){
        var _thisObj = $(this);
        layer.confirm(
            '确定要删除吗?',
            {icon: 2},
            function(index){
                layer.closeAll('dialog');
                $.ajax({
                        url: '/goodstreatment/update',
                        type: 'post',
                        dataType: 'json',
                        data: {
                            id: _thisObj.attr('data-id'),
                            status: 0
                        }
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
    $('.row').on('click', '#save', function(){
        var promotion_mutex = '';//互斥活动
        $("input[name='promotion_mutex[]']:checked").each(function(){
            promotion_mutex += promotion_mutex == '' ? $(this).val() : ',' + $(this).val();
        });
        var platform_pc = $(':input[name=platform_pc]').is(':checked');
        var platform_app = $(':input[name=platform_app]').is(':checked');
        var platform_wap = $(':input[name=platform_wap]').is(':checked');
        var platform_wechat = $(':input[name=platform_wechat]').is(':checked');
        var datas = {
            'goods_id': $(':input[name=goods]').val(),
            'min_goods_number': $(':input[name=min_goods_number]').val(),
            'unit_price': $(':input[name=unit_price]').val(),
            'promotion_msg': $(':input[name=promotion_msg]').val(),
            'platform_pc':  platform_pc ? 1 : 0,
            'platform_app': platform_app ? 1 : 0,
            'platform_wap': platform_wap ? 1 : 0,
            'platform_wechat': platform_wechat ? 1 : 0,
            'promotion_mutex': promotion_mutex
        };
        if(!platform_pc && !platform_app && !platform_wap && !platform_wechat){
            layer_required('请选择活动平台！');
            return false;
        }
        if(datas.min_goods_number < 2)
        {
            layer_required('件数要大于等于2！');
            return false;
        }
        if(datas.min_goods_number > 200){
            layer_required('件数不能超过200！');
            return false;
        }
        if(!regMoney.test(datas.min_goods_number) || !regNumber.test(datas.min_goods_number)){
            layer_required('件数只能为整数！');
            return false;
        }
        if(!regMoney.test(datas.unit_price)){
            layer_required('请输入正确的价格！');
            return false;
        }
        if(!$.trim(datas.promotion_msg)){
            layer_required('促销语不能为空！');
            return false;
        }
        $.ajax({
            url: '/goodstreatment/add',
            type: 'post',
            dataType: 'json',
            data: datas
        })
        .done(function(data){
            layer_required(data.info);
            if(data.status == 'success'){
                var html = '<tr height="35px;" class="allsave" id="ids' + data.data + '">\
                <td><input type="checkbox" class="batchdel" name="id" value="' + data.data + '">&nbsp;&nbsp;' + datas.min_goods_number + '</td>\
                <td>' + datas.unit_price + '</td>\
                <td>' + datas.promotion_msg + '</td>\
                <td><input type="button" class="btn btn-xs btn-danger delete" data-id="' + data.data + '" value="删除" /></td>\
                </tr>';
            }
            $('tbody').append(html);
        });
    });
    $('.row').on('click', '.save', function(){
        var promotion_mutex = '';//互斥活动
        $("input[name='promotion_mutex[]']:checked").each(function(){
            promotion_mutex += promotion_mutex == '' ? $(this).val() : ',' + $(this).val();
        });
        var id = $(this).attr('data-id');
        var platform_pc = $(':input[name=platform_pc]').is(':checked');
        var platform_app = $(':input[name=platform_app]').is(':checked');
        var platform_wap = $(':input[name=platform_wap]').is(':checked');
        var platform_wechat = $(':input[name=platform_wechat]').is(':checked');
        var datas = {
            'id': id,
            'goods_id': $(this).attr('goods-id'),
            'min_goods_number': $('#min_goods_number_' + id).val(),
            'unit_price': $('#unit_price_' + id).val(),
            'promotion_msg': $('#promotion_msg_' + id).val(),
            'platform_pc':  platform_pc ? 1 : 0,
            'platform_app': platform_app ? 1 : 0,
            'platform_wap': platform_wap ? 1 : 0,
            'platform_wechat': platform_wechat ? 1 : 0,
            'promotion_mutex': promotion_mutex
        };
        if(!platform_pc && !platform_app && !platform_wap && !platform_wechat){
            layer_required('请选择活动平台！');
            return false;
        }
        if(!regMoney.test(datas.min_goods_number) || !regNumber.test(datas.min_goods_number)){
            layer_required('件数只能为整数！');
            return false;
        }
        if(!regMoney.test(datas.unit_price)){
            layer_required('请输入正确的价格！');
            return false;
        }
        if(!$.trim(datas.promotion_msg)){
            layer_required('促销语不能为空！');
            return false;
        }
        $.ajax({
                url: '/goodstreatment/edit',
                type: 'post',
                dataType: 'json',
                data: datas
            })
            .done(function(data){
                layer_required(data.info);
            });
    });
    $('#batchdel').on('click', function(){
        var batchdel = $('.batchdel');
        if(!batchdel.is(':checked')){
            layer_required('请选择批量删除的数据！');
            return false;
        }
        for(var i=0; i<batchdel.length; i++){
            if($(batchdel[i]).is(":checked")){
                var id = $(batchdel[i]).val();
                $('#ids'+id).remove();
                $.ajax({
                        url: '/goodstreatment/update',
                        type: 'post',
                        dataType: 'json',
                        data: {
                            id: id,
                            status: 0
                        }
                    })
                    .done(function(data){
                        if(data.status == 'error'){
                            layer_required(data.info);
                        }
                    });
            }
        }
    });
    $('#allsave').on('click', function(){
        var promotion_mutex = '';//互斥活动
        $("input[name='promotion_mutex[]']:checked").each(function(){
            promotion_mutex += promotion_mutex == '' ? $(this).val() : ',' + $(this).val();
        });
        var is_true = true;
        var max_goods_number = $('.row .allsave').find('input[name=min_goods_number]').eq(0).val()?$('.row .allsave').find('input[name=min_goods_number]').eq(0).val():0;
        var max_unit_price = $('.row .allsave').find('input[name=unit_price]').eq(0).val()?$('.row .allsave').find('input[name=unit_price]').eq(0).val():0;
        $('.row .allsave').each(function(i,selval){
            var id = $(this).find('input[name=id]').val();
            var platform_pc = $(':input[name=platform_pc]').is(':checked');
            var platform_app = $(':input[name=platform_app]').is(':checked');
            var platform_wap = $(':input[name=platform_wap]').is(':checked');
            var platform_wechat = $(':input[name=platform_wechat]').is(':checked');
            var goods_number = $(this).find('input[name=min_goods_number]').val();
            var unit_price = $(this).find('input[name=unit_price]').val();
            if((parseInt(goods_number) > parseInt(max_goods_number)) && (parseFloat(unit_price) >= parseFloat(max_unit_price))){
                layer_required('请按照件数越多价格越低的规则设置');
                is_true = false;
                return false;
            }else{
                max_goods_number = goods_number;
                max_unit_price = unit_price;
            }
            var datas = {
                'id': id,
                'goods_id': $(this).find('.save').attr('goods-id'),
                'min_goods_number': goods_number,
                'unit_price': unit_price,
                'promotion_msg': $(this).find('input[name=promotion_msg]').val(),
                'platform_pc':  platform_pc ? 1 : 0,
                'platform_app': platform_app ? 1 : 0,
                'platform_wap': platform_wap ? 1 : 0,
                'platform_wechat': platform_wechat ? 1 : 0,
                'promotion_mutex': promotion_mutex
            };
            if(!platform_pc && !platform_app && !platform_wap && !platform_wechat){
                layer_required('请选择活动平台！');
                is_true = false;
                return false;
            }
            if(!regMoney.test(datas.min_goods_number) || !regNumber.test(datas.min_goods_number)){
                layer_required('件数只能为整数！');
                is_true = false;
                return false;
            }
            if(!regMoney.test(datas.unit_price)){
                layer_required('请输入正确的价格！');
                is_true = false;
                return false;
            }
            if(!$.trim(datas.promotion_msg)){
                layer_required('促销语不能为空！');
                is_true = false;
                return false;
            }
            $.ajax({
                    url: '/goodstreatment/edit',
                    type: 'post',
                    dataType: 'json',
                    async: false,
                    data: datas
                })
                .done(function(data){
                    if(data.status == 'error'){
                        layer_required(data.info);
                        is_true = false;
                        return false;
                    }
                });
            if(!is_true){
                return false;
            }
        });
        if(is_true){
            layer_required('修改成功！');
        }
    });
    //全选
    $('.all').on('click', function(){
        if(this.checked){
            $("#list :checkbox").prop("checked", true);
        }else{
            $("#list :checkbox").prop("checked", false);
        }
    });
    $('#list :checkbox').on('click', function(){
        checkall();
    });
});
function checkall(){
    var num_all = $("#list :checkbox").size(); //选项总个数
    var num_checked = $("#list :checkbox:checked").size(); //选中个数
    if (num_all == num_checked) { //若选项总个数等于选中个数
        $(".all").prop("checked", true); //全选选中
    } else {
        $(".all").prop("checked", false);
    }
}
