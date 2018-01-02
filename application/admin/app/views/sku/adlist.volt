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
                            <form action="/sku/adlist" method="get">
                                <div class="tools-box">
                                    <label class="clearfix">
                                       <span>广告模板名称：</span> <input type="text" id="ad_name" placeholder="广告模板名称" name="ad_name" value="{{ ad_name }}" class="tools-txt" />
                                    </label>


                                    <label class="clearfix">
                                        <button class="btn btn-primary"  type="submit">搜索</button>
                                    </label>

                                    <label class="clearfix" style="float: right;margin-right: 30px;">
                                        <a class="btn btn-primary" href="/sku/adadd">新建商品广告模板</a>
                                    </label>

                                </div>
                            </form>

                        </div>
                    </div>
                    <div>
                        <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                            <thead>
                            <tr>
                                <th class="center">
                                    ID
                                </th>
                                <th class="center">名称</th>
                                <th class="center">平台</th>
                                <!--<th class="center">-->
                                    <!--添加时间-->
                                <!--</th>-->
                                <!--<th class="center">-->
                                    <!--修改时间-->
                                <!--</th>-->
                                <th class="center">
                                    操作
                                </th>
                            </tr>
                            </thead>
                            <tbody id="productRuleList">

                            <!-- 遍历商品信息 -->
                            {% if list['list'] is defined %}
                            {% if list['list'] > 0 %}
                                {% for v in list['list'] %}
                                <tr>
                                    <td class="center">
                                        {{ v['id'] }}
                                    </td>
                                    <td class="center">
                                        {{ v['ad_name'] }}
                                    </td>
                                    <td class="center">
                                        {% if v['platform'] is 'pc' %}PC端{% elseif v['platform'] is 'mobile' %}移动端{% endif %}
                                    </td>
                                    <!--<td class="center">-->
                                        <!--{{ date('Y-m-d H:i:s',v['add_time']) }}-->
                                    <!--</td>-->
                                    <!--<td class="center">-->
                                        <!--{{ date('Y-m-d H:i:s',v['update_time']) }}-->
                                    <!--</td>-->
                                    <td class="center">
                                        <a class="green" href="/sku/adedit?id={{ v['id'] }}">
                                            <i class="ace-icon fa fa-pencil bigger-130"></i>
                                            编辑
                                        </a> |
                                        <a class="red" href="javascript:void(0);" onclick="showConfirm('/sku/addel?id={{ v['id'] }}','');">
                                            <i class="ace-icon fa fa-trash-o bigger-130"></i>
                                            删除
                                        </a> |
                                        <a class="green isNotShow_click" data-id="{{ v['is_show'] }}" data-name="{{ v['id'] }}" href="javascript:void(0);">
                                            {% if v['is_show'] is 1 %}
                                            <i class="ace-icon fa fa-ban"></i>暂停
                                            {% else %}
                                            <i class="ace-icon fa  fa-circle-o"></i>启用
                                            {% endif %}
                                        </a>
                                    </td>
                                </tr>
                                {% endfor %}
                            {% else %}
                                <tr>
                                    <td class="center" colspan="6">
                                        暂无数据……
                                    </td>
                                </tr>
                            {% endif %}
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
{% if list['page'] is defined %}{{ list['page'] }}{% endif %}
{% endblock %}

{% block footer %}
<script>
    $(document).keypress(function (e) {
        if( e.which == 13 ){
            return false;
        }
    });
    //启用|暂停控制
    $(document).on('click','.isNotShow_click',function(){
        var id = $(this).attr('data-name');
        var is_show = $(this).attr('data-id');
        var th = this;
        var html = '';
        if(is_show == 1){
            html += '<i class="ace-icon fa  fa-circle-o"></i>启用';
            var name = 0;
        }else{
            html += '<i class="ace-icon fa fa-ban"></i>暂停';
            var name = 1;
        }
        $.getJSON('/sku/isShowAd?id='+id+'&is_show='+name, function(msg){
            if(msg){
                $(th).html(html);
                $(th).attr('data-id',name);
            }else{
                layer.msg(msg.info, {shade: 0.3, time: 300}); return false;
            }
        });
    });
</script>
{% endblock %}