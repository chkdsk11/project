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
    #categoryBox{
        width: 520px;
    }
    #categoryBox select{
        width: 20%;
    }
</style>
<div class="page-content">
    <div class="row">
        <div class="col-xs-12">
            <div class="row">
                <div class="col-xs-12">

                    <div class="form-group clearfix">
                        <div class="row">
                            <form action="/spu/list" method="get">
                                <div class="tools-box">
                                    <label class="clearfix">
                                        <span>SPU名或SPU ID: </span><input type="text" style="width:200px;" placeholder="SPU名或SPU ID" name="name" value="{{ name }}" class="tools-txt" />
                                    </label>
                                    <label class="clearfix">
                                        <span>后台分类: </span>
                                    </label>
                                    <label style="width:auto;">
                                        {% include "library/category.volt"%}
                                    </label>
                                    <label class="clearfix" style="float: right;margin-right: 30px;">
                                        <a class="btn btn-primary" href="/spu/add">添加商品</a>
                                    </label>
                                    <label class="clearfix" style="float: right;">
                                        <button class="btn btn-primary"  type="submit">搜索</button>
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
                                    SPU ID
                                </th>
                                <th class="center">名称</th>
                                <!--<th class="center">属性</th>-->
                                <th class="center">
                                    修改时间
                                </th>
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
                                        {{ v['spu_id'] }}
                                    </td>
                                    <td class="center">
                                        {{ v['spu_name'] }}
                                    </td>
                                    <!--<td class="center">-->
                                        <!--{% if v['productRule'] is defined %}{{ v['productRule'] }}{% endif %}-->
                                    <!--</td>-->
                                    <td class="center">
                                        {{ date('Y-m-d H:i:s',v['update_time']) }}
                                    </td>
                                    <td class="center">
                                        <a class="green" href="/spu/edit?id={{ v['spu_id'] }}">
                                            <i class="ace-icon fa fa-pencil bigger-130"></i>
                                            编辑
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
    function submit_search(){
        var act = true;
        var one_category = $('#one_category').val();
        if( one_category > 0){
            act = isCategorySelect();
        }
        return act;
    }
    //键盘点击事件
    $(document).keypress(function (e) {
        if( e.which == 13 ){
            return false;
        }
    });
</script>
{% endblock %}