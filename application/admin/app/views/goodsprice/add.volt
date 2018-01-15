{% extends "layout.volt" %}

{% block content %}
<div class="page-content">

    <div class="row">
        <div class="col-xs-12">
            <!-- PAGE CONTENT BEGINS -->
            <div class="clearfix">
                <div class="pull-right tableTools-container"></div>
            </div>
            <div class="page-header">
                <h1>
                    添加会员商品
                </h1>
            </div><!-- /.page-header -->

            <div class="tools-box">
                <label class="clearfix">
                    <form action="/goodstreatment/list" id="search">
                    <input type="text" name="goods_name" placeholder="输入商品名称或ID" class="tools-txt" >
                    <button class="btn btn-primary" type="submit">搜索</button>
                    </form>
                </label>
                <label class="clearfix" id="showgoods" style="display: none;">
                    <span>商品：</span>
                    <select name="goods" id="goods" class="tools-txt">
                    </select>
                </label>
            </div>
            <!-- div.table-responsive -->

            <!-- div.dataTables_borderWrap -->
            <form id="addform" action="/goodsprice/add" method="post">
            <div>
                <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>商品名称</th>
                        <th>原价</th>
                        <th>会员价/折扣</th>
                        <th>标签</th>
                        <!--<th>限购数量</th>-->
                        <th>适用平台</th>
                        <th width="82">互斥活动</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
            <div class="form-horizontal">
                <div class="tools-box" style="padding: 0;">
                    <input type="submit" class="btn btn-primary btn-rig" id="uploadBtn" value="确认添加" />
                </div>
            </div>
            </form>
        </div><!-- /.col -->
    </div><!-- /.row -->

</div><!-- /.page-content -->

{% endblock %}

{% block footer %}
<script type="text/javascript">
    var tagId = {{ tag_id }};
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
                            var option_data = data.data[i].price;
                            if (data.data[i].is_unified_price == 1) {
                                option_data = 'PC:'+ data.data[i].goods_price_pc+'<br>'+'app:'+ data.data[i].goods_price_app+'<br>wap:'+ data.data[i].goods_price_wap;
                            }
                            string.push('<option value="' + data.data[i].id + '" data="' + option_data + '">' + data.data[i].goods_name +'</option>');
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
        if (goods_id <= 0){
            return false;
        }
        var length = $('tbody tr').length;
        if(length >= 1){
            var trList = $("tbody").children("tr")
            for (var i=0;i<trList.length;i++) {
                var tdArr = trList.eq(i).find("td");
                var tmp_goods_id = tdArr.eq(0).find('input').val();
                if(tmp_goods_id == goods_id) {
                    return false;
                }
            }
        }
        var html = '<tr height="35px;">\
                        <td><input type="hidden" name="gooddata[' + goods_id + '][goods_id]" value="' + goods_id + '">' + goods_id + '</td>\
                        <td>' + $(this).find("option:selected").text() + '</td>\
                        <td>' + $(this).find("option:selected").attr('data') + '</td>\
                        <td width="16%" class="jk"><input type="text" style="width: 100px; float: left; margin-right: 5px;" name="gooddata[' + goods_id+ '][value]"><select style="float: left;" name="gooddata[' + goods_id + '][type]"><option value="1">元</option><option value="2">折</option></select></td>\
                        <td><select style="width: 100px;" id="' + goods_id + '" name="gooddata[' + goods_id + '][tag_id]" onchange="selectTag(this);"><option value="">--请选择--</option>{% for val in tagData %}<option value="{{ val['tag_id'] }}">{{ val['tag_name'] }}</option>{% endfor %}</select></td>\
                        <td class="platform">';
        if (shopPlatform.pc) {
            html += '<label><input class="platform_checkbox ace" data-platform="pc" data-gid="' + goods_id + '" name="gooddata[' + goods_id + '][platform_pc]" type="checkbox" value="1" checked><span class="lbl">&nbsp;PC</span></label>';
        }
        if (shopPlatform.app) {
            html += '<label><input class="platform_checkbox ace" data-platform="app" data-gid="' + goods_id + '" name="gooddata[' + goods_id + '][platform_app]" type="checkbox" value="1" checked><span class="lbl">&nbsp;APP</span></label>';
        }
        if (shopPlatform.wap) {
            html += '<label><input class="platform_checkbox ace" data-platform="wap" data-gid="' + goods_id + '" name="gooddata[' + goods_id + '][platform_wap]" type="checkbox" value="1" checked><span class="lbl">&nbsp;WAP</span></label>';
        }
        if (shopPlatform.wechat) {
            html += '<label><input class="platform_checkbox ace" data-platform="wechat" data-gid="' + goods_id + '" name="gooddata[' + goods_id + '][platform_wechat]" type="checkbox" value="1" checked><span class="lbl">&nbsp;微商城</span></label>';
        }
        html += '</td>\
                        <td> {% if goodsPriceEnum["mutexPromotion"] is defined %}{% for k,v in goodsPriceEnum["mutexPromotion"] %} <label> <input name="gooddata[' + goods_id + '][good_set_mutex][]" type="checkbox" class="ace" value="{{k}}"> <span class="lbl">&nbsp;{{v}}</span> </label>{% endfor %}{% endif %}</td>\
                        <td><input type="button" class="btn btn-xs btn-danger delete" data-id="" value="删除" /></td>\
                        </tr>';
        $('tbody').append(html);
    });
    //删除
    $('.row').on('click', '.delete', function(){
        var _thisObj = $(this);
        _thisObj.parents('tr').toggle('slow', function(){
            _thisObj.parents('tr').remove();
        });
    });
    $('#addform').submit(function(){
        var is_ok = 0;
        var trLength = $(this).find('table>tbody>tr').length;
        if(!trLength){
            layer_required('请输入商品名称或ID');
            return false;
        }
        $(".row .jk").each(function(i,selval){
            var value = $(this).find('input').val();
            var type = $(this).find('select option:selected').val();
            if(!regMoney.test(value) && type == 1){
                layer_required('请输入正确的价格！');
                is_ok = 1;
                return false;
            }
            if(!oneDecimal.test(value) && type == 2){
                layer_required('请输入正确的折扣！');
                is_ok = 1;
                return false;
            }
        });
        if(is_ok == 0){
            $(".row select option:selected").each(function(i,selval){
                if($(this).val() == ''){
                    layer_required('请选择会员标签！');
                    is_ok = 1;
                    return false;
                }
            });
        }
        if(is_ok == 0){
            $(".row .limit").each(function(){
                var limit_number = $(this).val();
                if(typeof($(this).attr("disabled")) == "undefined" && (!regNumber.test(limit_number) || !regMoney.test(limit_number))){
                    layer_required('请输入正确的限购数量！');
                    is_ok = 1;
                    return false;
                }
            });
        }
        if(is_ok == 0){
            $(".row .platform").each(function(){
                if(!$(this).find(':checkbox').is(':checked')){
                    layer_required('请选择适用平台！');
                    is_ok = 1;
                    return false;
                }
            });
        }
        if(is_ok == 0)
        {
            ajaxSubmit('addform');
        }

        return false;
    });
    //如果选择的不是辣 妈标签，限购输入框为disabled
    function selectTag(event)
    {
        var selectTag = event.value;
        if(selectTag == tagId)
        {
            $($('input[name="gooddata[' + event.id + '][limit_number]"]')).attr('disabled',false);
            $($('input[name="gooddata[' + event.id + '][limit_number]"]')).attr('placeholder','');
        }else{
            $($('input[name="gooddata[' + event.id + '][limit_number]"]')).attr('disabled',true);
            $($('input[name="gooddata[' + event.id + '][limit_number]"]')).attr('placeholder','非辣妈标签不用填写');
            $($('input[name="gooddata[' + event.id + '][limit_number]"]')).val('');
        }
    }

    $('body').on('click','.platform_checkbox',function () {
       var gid = $(this).data('gid');
        var use_platform_arr=[];
        $(this).parent().parent().find('input').each(function () {
           if($(this).is(':checked')){
               use_platform_arr.push($(this).data('platform'));
           }
        });
        if(use_platform_arr.length > 0 ){
            $.post('/goods/isOnShelf',{platform:use_platform_arr,ids:gid},function (res) {
                if (res.code == '400') {
                    layer_required(res.msg);
                    return false;
                }
            });
        }
    });
</script>
{% endblock %}