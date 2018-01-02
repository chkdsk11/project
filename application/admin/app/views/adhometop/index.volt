{% extends "layout.volt" %}

{% block content %}
<style>
    .sku_label_search{
        margin-right: 150px;
    }
    #categoryBox{
        top: -21px;
        left: 31px;
    }
    .sku_menu_main{
        padding-top: 7px;
        height: 44px;
    }
    .sku_search_keywords{
        margin-top: -19px;
        margin-bottom: -30px;
    }
    .popup {
        position: fixed;
        z-index: 999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        /* display: -webkit-box;
         display: -moz-box;
         display:         box;
         -webkit-box-pack: center;
         -moz-box-pack: center;
         box-pack:         center;
         -webkit-box-align: center;
         -moz-box-align: center;
         box-align:         center;*/
        overflow-x: hidden;
        overflow-y: auto;
        background-color: rgba(0,0,0,0.7);

    }
    .popup .popup-content {
        position: absolute;
        left: 50%;
        top: 50%;
        width: 660px ;
        transform: translateY(-50%);
        width:500px;
        padding: 10px;
        text-align: center;
        position: relative;
        border-radius: .5rem;
        background-color: #ffffff;
        margin-left: -260px;

    }
    .popup-content .pro-title {
        position: relative;
        width: 100%;
        height: 40px;
        line-height: 40px;
        text-align: center;
        color: #333333;
        font-size: 16px;
        border-bottom: #cccccc solid 1px;
        margin-bottom: 20px;

    }
    .popup-content .pro-title a {
        display: block;
        position: absolute;
        right: 0;
        top: 0;
        height: 40px;
        width: 40px;
        color: #333333;
        font-size: 24px;
        text-align: center;
    }

    .popup-content .title {
        position: relative;
        width: 100%;
        height: 40px;
        line-height: 40px;
        text-align: center;
        color: #333333;
        font-size: 16px;
        border-bottom: #cccccc solid 1px;

    }
    .popup-content .title a {
        display: block;
        position: absolute;
        right: 0;
        top: 0;
        height: 40px;
        width: 40px;
        color: #333333;
        font-size: 24px;
        text-align: center;
    }
    .popup-content .stock-radio {
        text-align: left;
    }
    .popup-content .stock-checkbox {
        margin-left: 30px ;
    }
    .popup-content .stock-price {
        width: 80px;
    }

    .popup-content button {
        margin: 10px 0;
    }
    .f-none{
        float: none !important;
    }
    .w-60{
        width: 60px !important;
    }
    #dynamic-table{
        table-layout: fixed;
    }
    #dynamic-table thead tr th:last-child{
        width: 105px;
    }
</style>
<div class="page-content">
    <div class="row">
        <div class="col-xs-12">
            <div class="row">
                <div class="col-xs-12">

                    <div class="form-group clearfix">
                        <div class="row">
                            <form action="" method="get">
                                <div class="tools-box" >
                                    <label class="clearfix">
                                        活动名称:<input type="text" style="float:none;" placeholder="请输入活动名称" name="name" value="{% if search['name'] is defined %}{{ search['name']}}{% endif %}" class="tools-txt sku_input" />
                                    </label>

                                    <label class="clearfix">
                                        活动ID:<input type="text" style="float:none;" placeholder="请输入活动ID" name="id" value="{% if search['id'] is defined %}{{ search['id']}}{% endif %}" class="tools-txt sku_input" />
                                    </label>

                                    <label class="clearfix">
                                        活动状态:
                                        <select name="status" class="sku_select">
                                            <option value="">-请选择-</option>
                                            <option value="start" {% if search['status'] is defined and search['status']=='start' %} selected = "selected" {% endif %}>未开始</option>
                                            <option value="middle"{% if search['status'] is defined and search['status']=='middle' %} selected = "selected" {% endif %}>进行中</option>
                                            <option value="end" {% if search['status'] is defined and search['status']=='end' %} selected = "selected" {% endif %}>已结束</option>
                                        </select>
                                    </label>
                                    <label class="clearfix"><button class="btn btn-primary clear_search f-none" type="submit">清除搜索条件</button></label>
                                    <label class="clearfix w-60" >
                                        <button class="btn btn-primary sku_label_search f-none" type="submit">搜索</button>
                                    </label>
                                    <label class="clearfix sku_label" ><a class="btn btn-primary" href="/adhometop/add" style="margin-top: 2px;">添加</a></label>
                                </div>
                            </form>

                        </div>
                    </div>
                    <div>
                        <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                            <thead>
                            <tr>
                                <th class="center sku_menu_row5" style="width:10%;">编号</th>
                                <th class="center sku_menu_row3">活动名称</th>
                                <th class="center sku_menu_row5" style="width:10%;">类型</th>
                                <th class="center sku_menu_row4" style="width:10%;">广告图数</th>
                                <th class="center sku_menu_row5" style="width:15%;">开始时间</th>
                                <th class="center sku_menu_row5" style="width:15%;">结束时间</th>
                                <th class="center sku_menu_row5" style="width:10%;">活动状态</th>
                                <!--<th class="center sku_menu_row5">排序</th><-->
                                <th class="center sku_menu_row4" style="width:165px;"> 操作</th>
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
                                <td>
                                    {{ v['ad_name'] }}
                                </td>
                                <td class="center">
                                    图片
                                </td>
                                <td class="center">
                                    {{ v['img_num'] }}
                                </td>
                                <td class="center">
                                    {{ date('Y-m-d H:i:s', v['start_time']) }}
                                </td>
                                <td class="center">
                                    {{ date('Y-m-d H:i:s', v['end_time']) }}
                                </td>
                                <td class="center ">
                                    {% if time()  <  v['start_time']  %}
                                    未开始
                                    {% elseif time() > v['end_time'] %}
                                    已结束
                                    {% else %}
                                    进行中
                                    {% endif %}
                                </td>
                                <!--<td class="center update_sort">
                                    {{ v['order'] }}
                                </td>-->
                                <td class="center">
                                    <a class="green" href="/adhometop/edit?id={{ v['id'] }}&status=1">
                                        <i class="ace-icon fa fa-pencil bigger-130"></i>
                                        查看
                                    </a>
                                    {% if  time() < v['end_time'] %}
                                    |
                                    <a href="/adhometop/edit?id={{ v['id'] }}"><i class="ace-icon fa fa-file-text"></i>编辑</a>
                                    {% endif %}

                                    |
                                    <a class="red del" data-id="{{ v['id'] }}" href="javascript:;"><i class="ace-icon fa fa-file-text"></i>删除</a>
                                </td>
                            </tr>
                            {% endfor %}
                            {% else %}
                            <tr>
                                <td class="center" colspan="10">
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
    <script src="http://{{ config.domain.static }}/assets/admin/js/adhometop/common.js"></script>
{% endblock %}