/**
 * Created by Administrator on 2016/9/6.
 */

$(function(){
    //提交form
    $("#form").submit(function(){
        var value = $(':input[name=value]').val();
        var limit_number = $(':input[name=limit_number]').val();
        if(!regMoney.test(value) && $('#type').find('option:selected').val() == 1){
            layer_required('请输入正确的价格！');
            return false;
        }
        if((!regMoney.test(value) || value <= 0 || value >= 10) && $('#type').find('option:selected').val() == 2){
            layer_required('请输入正确的折扣！');
            return false;
        }
        if(!$('#platform :checkbox').is(':checked')){
            layer_required('请选择适用平台！');
            return false;
        }
        if(typeof(limit_number) != "undefined" && (!regNumber.test(limit_number) || !regMoney.test(limit_number))){
            layer_required('请输入正确的限购数量！');
            is_ok = 1;
            return false;
        }
        ajaxSubmit('form');
        return false;
    });
    //删除操作
    $('.row').on('click', '.del', function(){
        var request = location.search;
        layer_confirm('', '/goodsprice/del', {tag_goods_id: $(this).attr('data-id'),request :request});
    });
});
