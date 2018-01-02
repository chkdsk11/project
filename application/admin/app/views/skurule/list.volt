{% extends "layout.volt" %}

{% block content %}
<style>
    .productRule{
        display: inline-block;
        float: left;
        margin-left: 20px;
        width: 60%;
    }
    .editProductRule {
        display: inline-block;
        float: right;
        margin-right:20px;
    }
</style>
<div class="page-content">
    <div class="row">
        <div class="col-xs-12">
            <div class="row">
                <div class="col-xs-12">

                    <div class="form-group clearfix">
                        <div class="row">
                            <div class="col-xs-12 col-sm-9" >
                               请选择分类： <select name="shop_category[]" id="get_category">
                                    {% if category is defined %}
                                    {% for k,v in category %}
                                    <option value="{{v['id']}}">{{v['category_name']}}</option>
                                    {% endfor %}
                                    {% endif %}
                                </select>
                                <select id="get_category_3">
                                    {% if category2 is defined %}
                                    {% for k,v in category2 %}
                                    <option value="{{v['id']}}">{{v['category_name']}}</option>
                                    {% endfor %}
                                    {% endif %}
                                </select>
                            </div>
                        </div>
                    </div>
                    <div>
                        <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                            <thead>
                            <tr>
                                <th class="center" style="width:25%;">
                                    三级分类
                                </th>
                                <th class="center" style="width:25%;">品规名一</th>
                                <th class="center" style="width:25%;">品规名二</th>
                                <th class="center" style="width:25%;">
                                    品规名三
                                </th>
                            </tr>
                            </thead>
                            <tbody id="productRuleList">

                            <!-- 遍历商品信息 -->
                            {% if list is defined %}
                            {% for v in list %}
                            <tr>
                                <td class="center">
                                    {{ v[0]['category_name'] }}
                                </td>
                                <td class="center">
                                    <div name="1">
                                        <span class="productRule">
                                        {% if v[0]['productRule'][0]['name'] is defined %}{{ v[0]['productRule'][0]['name'] }}{% endif %}
                                        </span>
                                        <a name="{{ v[0]['id'] }}" href="javascript:;" id="{% if v[0]['productRule'][0]['id'] is defined %}{{ v[0]['productRule'][0]['id'] }}{% endif %}" class="editProductRule">编辑</a>
                                    </div>
                                </td>
                                <td class="center">
                                    <div name="2" class="clearfix"><span class="productRule">
                                    {% if v[0]['productRule'][1]['name'] is defined %}{{ v[0]['productRule'][1]['name'] }}{% endif %}</span>
                                        <a name="{{ v[0]['id'] }}" href="javascript:;" id="{% if v[0]['productRule'][1]['id'] is defined %}{{ v[0]['productRule'][1]['id'] }}{% endif %}" class="editProductRule">编辑</a>
                                        </div>
                                </td>
                                <td class="center">
                                    <div name="3"><span class="productRule">
                                    {% if v[0]['productRule'][2]['name'] is defined %}{{ v[0]['productRule'][2]['name'] }}{% endif %}</span>
                                    <a name="{{ v[0]['id'] }}" href="javascript:;" id="{% if v[0]['productRule'][2]['id'] is defined %}{{ v[0]['productRule'][2]['id'] }}{% endif %}" class="editProductRule">编辑</a>
                                    </div>
                                </td>
                            </tr>
                            {% endfor %}
                            {% endif %}
                            <!-- 遍历商品信息 end -->

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div><!-- /.col -->
    </div><!-- /.row -->
</div>
{% endblock %}

{% block footer %}
<script>
    //设置本页面全局变量
    var editable_submit_th_productRule = '';
    $(function () {
        //一级分类改变时执行
        $("#get_category").change(function () {
            var id = $(this).val();
            $.ajax({
                type: 'get',
                url: '/category/getCategory?id='+id,
                cache: false,
                dataType:'json',
                success: function(msg) {
                    var html = '';
                    if(msg['data'].length > 0){
                    for (var i = 0; i < msg['data'].length; i++) {
                        html += " <option value='" + msg['data'][i].id + "'>" + msg['data'][i].category_name + "</option>";
                    }
                    }else{
                        html += "<option>--请选择--</option>";
                    }
                    $("#get_category_3").html(html);
                    //获得三级分类信息
                    if(msg['data'].length > 0){
                        var id = msg['data'][0]['id'];
                        $.ajax({
                            type: 'get',
                            url: '/category/getCategory?id='+id+"&productRule=1",
                            cache: false,
                            dataType:'json',
                            success: function(msg){
                                var html_list = '';
                                if(msg['data'].length > 0){
                                    for(var i = 0;i < msg['data'].length;i++){
                                        html_list += "<tr><td class='center'>"+msg['data'][i]['category_name']+"</td>";
                                        if(msg['data'][i]['productRule'] != undefined){
                                            if(msg['data'][i]['productRule'][0] != undefined){
                                                html_list += "<td  class='center'><div name='1'><span class='productRule'>" + msg['data'][i]['productRule'][0]['name'] + "</span><a name='" + msg['data'][i]['id'] + "' href='javascript:;' id='" + msg['data'][i]['productRule'][0]['id'] + "' class='editProductRule'>编辑</a></div></td>";
                                            }else{
                                                html_list += "<td class='center'><div name='1'><span class='productRule'></span><a name='" + msg['data'][i]['id'] + "' href='javascript:;' id='' class='editProductRule'>编辑</a></div></td>";
                                            }
                                            if(msg['data'][i]['productRule'][1] != undefined){
                                                html_list += "<td class='center'><div name='2'><span class='productRule'>" + msg['data'][i]['productRule'][1]['name'] + "</span><a name='" + msg['data'][i]['id'] + "' href='javascript:;' id='" + msg['data'][i]['productRule'][1]['id'] + "' class='editProductRule'>编辑</a></div></td>";
                                            }else{
                                                html_list += "<td class='center'><div name='2'><span class='productRule'></span><a name='" + msg['data'][i]['id'] + "' href='javascript:;' id='' class='editProductRule'>编辑</a></div></td>";
                                            }
                                            if(msg['data'][i]['productRule'][2] != undefined){
                                                html_list += "<td class='center'><div name='3'><span class='productRule'>" + msg['data'][i]['productRule'][2]['name'] + "</span><a name='" + msg['data'][i]['id'] + "' href='javascript:;' id='" + msg['data'][i]['productRule'][2]['id'] + "' class='editProductRule'>编辑</a></div></td>";
                                            }else{
                                                html_list += "<td class='center'><div name='3'><span class='productRule'></span><a name='" + msg['data'][i]['id'] + "' href='javascript:;' id='' class='editProductRule'>编辑</a></div></td>";
                                            }
                                        }else{
                                            html_list += "<td class='center'><div name='1'><span class='productRule'></span><a name='" + msg['data'][i]['id'] + "' href='javascript:;' id='' class='editProductRule'>编辑</a></div></td>";
                                            html_list += "<td class='center'><div name='2'><span class='productRule'></span><a name='" + msg['data'][i]['id'] + "' href='javascript:;' id='' class='editProductRule'>编辑</a></div></td>";
                                            html_list += "<td class='center'><div name='3'><span class='productRule'></span><a name='" + msg['data'][i]['id'] + "' href='javascript:;' id='' class='editProductRule'>编辑</a></div></td>";
                                        }
                                        html_list += "</tr>";
                                        $("#productRuleList").html(html_list);
                                    }
                                }else{
                                    html_list += "<tr><td class='center' colspan='4'>无数据……</td></tr>";
                                    $("#productRuleList").html(html_list);
                                }

                            }
                        });
                    }else{
                        html_list = "<tr><td class='center' colspan='4'>无数据……</td></tr>";
                        $("#productRuleList").html(html_list);
                    }
                }
            });
        });
        //二级分类改变时执行
        $("#get_category_3").change(function () {
            var id = $(this).val();
            $.ajax({
                type: 'get',
                url: '/category/getCategory?id='+id+"&productRule=1",
                cache: false,
                dataType:'json',
                success: function(msg){
                    var html = '';
                    if(msg['data'].length > 0) {
                        for (var i = 0; i < msg['data'].length; i++) {
                            html += "<tr><td class='center'>" + msg['data'][i]['category_name'] + "</td>";
                            if (msg['data'][i]['productRule'] != undefined) {
                                if (msg['data'][i]['productRule'][0] != undefined) {
                                    html += "<td class='center'><div name='1'><span class='productRule'>" + msg['data'][i]['productRule'][0]['name'] + "</span><a name='" + msg['data'][i]['id'] + "' href='javascript:;' id='" + msg['data'][i]['productRule'][0]['id'] + "' class='editProductRule'>编辑</a></div></td>";
                                } else {
                                    html += "<td class='center'><div name='1'><span class='productRule'></span><a name='" + msg['data'][i]['id'] + "' href='javascript:;' id='' class='editProductRule'>编辑</a></div></td>";
                                }
                                if (msg['data'][i]['productRule'][1] != undefined) {
                                    html += "<td class='center'><div name='2'><span class='productRule'>" + msg['data'][i]['productRule'][1]['name'] + "</span><a name='" + msg['data'][i]['id'] + "' href='javascript:;' id='" + msg['data'][i]['productRule'][1]['id'] + "' class='editProductRule'>编辑</a></div></td>";
                                } else {
                                    html += "<td class='center'><div name='2'><span class='productRule'></span><a name='" + msg['data'][i]['id'] + "' href='javascript:;' id='' class='editProductRule'>编辑</a></div></td>";
                                }
                                if (msg['data'][i]['productRule'][2] != undefined) {
                                    html += "<td class='center'><div name='3'><span class='productRule'>" + msg['data'][i]['productRule'][2]['name'] + "</span><a name='" + msg['data'][i]['id'] + "' href='javascript:;' id='" + msg['data'][i]['productRule'][2]['id'] + "' class='editProductRule'>编辑</a></div></td>";
                                } else {
                                    html += "<td class='center'><div name='3'><span class='productRule'></span><a name='" + msg['data'][i]['id'] + "' href='javascript:;' id='' class='editProductRule'>编辑</a></div></td>";
                                }
                            } else {
                                html += "<td class='center'><div name='1'><span class='productRule'></span><a name='" + msg['data'][i]['id'] + "' href='javascript:;' id='' class='editProductRule'>编辑</a></div></td>";
                                html += "<td class='center'><div name='2'><span class='productRule'></span><a name='" + msg['data'][i]['id'] + "' href='javascript:;' id='' class='editProductRule'>编辑</a></div></td>";
                                html += "<td class='center'><div name='3'><span class='productRule'></span><a name='" + msg['data'][i]['id'] + "' href='javascript:;' id='' class='editProductRule'>编辑</a></div></td>";
                            }
                            html += "</tr>";
                            $("#productRuleList").html(html);
                        }
                    }else{
                        html += "<tr><td class='center' colspan='4'>无数据……</td></tr>";
                        $("#productRuleList").html(html);
                    }

                }
            });
        });
        //点击编辑执行
        $(document).on('click','.editProductRule',function(){
            var id = $(this).attr('id');
            var value = '';
            if(id > 0){
                value = $(this).prev().html();
            }
            var order = $(this).parent().attr('name');
            var category_id = $(this).attr('name');
            var formHtml = '<form style="" action="/skuRule/edit" method="post" class="form-inline editableform"><div class="control-group form-group"><div><div style="position: relative;" class="editable-input"><input type="hidden" name="order" value="'+order+'"><input type="hidden" name="category_id" value="'+category_id+'"><input type="hidden" name="id" value="'+id+'"><input name="name" value="' + value + '" class="form-control input-sm" type="text"><span class="editable-clear-x"></span></div><div class="editable-buttons"><button type="button" class="btn btn-info editable-submit"><i class="ace-icon fa fa-check"></i></button><button type="button" class="btn editable-cancel"><i class="ace-icon fa fa-times"></i></button></div></div><div style="display: none;" class="editable-error-block help-block"></div></div></form>';
            //getProductRule
            $(this).parent().css('display','none');
            $(this).parent().parent().append(formHtml);
        });
        //点击关闭按钮执行
        $(document).on('click','.editable-cancel',function(){
            $(this).parent().parent().parent().parent().prev().css('display','block');
            $(this).parent().parent().parent().parent().remove();
        });
        //点击确认执行
        $(document).on('click','.editable-submit',function(){
            var form = $(this).parent().parent().parent().parent();
            var url = form.attr('action');
            var data = {};
            data = form.serializeArray();
            editable_submit_th_productRule = this;
            _submitFn(url,data);
        });
    });
    //返回数据处理
    function okCallback(msg){
        if(msg.status == 'error'){
            layer.msg(msg.info, {shade: 0.3, time: 1000});
            return false;
        }else if(msg.status == 'success'){
            layer.load(20, {
                shade: [0.3,'#ffffff'],
                time : 1
            });
            $(editable_submit_th_productRule).parent().parent().parent().parent().prev().find('.productRule').html(msg.data['name']);
            if(msg.data['id'] > 0){
                $(editable_submit_th_productRule).parent().parent().parent().parent().prev().find('.editProductRule').attr('id',msg.data['id']);
            }
            $(editable_submit_th_productRule).parent().parent().parent().parent().prev().css('display','block');
            $(editable_submit_th_productRule).parent().parent().parent().parent().remove();
            layer.msg(msg.info, {shade: 0.3, time: time});
            return false;
        }
    }
</script>
{% endblock %}