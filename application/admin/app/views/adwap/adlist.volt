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
                                        活动名称:<input type="text" style="float:none;" placeholder="请输入活动名称" name="ad_name" value="{% if search['ad_name'] is defined %} {{ search['ad_name']}} {% endif %}" class="tools-txt sku_input" />
                                    </label>
                                    <label class="clearfix">
                                        位置:
                                        <select name="ad_position" class="ad_type" id="frist_select">
                                            <option value="0">-所有-</option>
                                            {% for k in ad_position %}
                                            <option value="{{ k['id']}}"  {% if search['ad_position'] is defined and search['ad_position']==  k['id'] %} selected = "selected" {% endif %} >{{ k['adpositionid_name'] }}</option>
                                            {% endfor %}
                                        </select>
                                    </label>

                                    <label class="clearfix">
                                        活动类型:
                                        <select name="ad_type" id="ad_type">
                                            <option value="">-请选择-</option>
                                            <option value="1" {% if search['ad_type'] is defined and search['ad_type']=='1' %} selected = "selected" {% endif %}>图片广告</option>
                                            <option value="2" {% if search['ad_type'] is defined and search['ad_type']=='2' %} selected = "selected" {% endif %}>商品推荐</option>
                                            <option value="3" {% if search['ad_type'] is defined and search['ad_type']=='3' %} selected = "selected" {% endif %}>文字广告</option>
                                        </select>
                                    </label>

                                    <label class="clearfix">
                                        活动状态:
                                        <select name="ad_status" class="sku_select">
                                            <option value="">--请选择--</option>
                                            <option value="start" {% if search['ad_status'] is defined and search['ad_status']=='start' %} selected = "selected" {% endif %}>未开始</option>
                                            <option value="middle" {% if search['ad_status'] is defined and search['ad_status']=='middle' %} selected = "selected" {% endif %}>进行中</option>
                                            <option value="end" {% if search['ad_status'] is defined and search['ad_status']=='end' %} selected = "selected" {% endif %}>已结束</option>
                                        </select>
                                    </label>
                                    <label class="clearfix"><button class="btn btn-primary clear_search f-none" type="submit">清除搜索条件</button></label>
                                    <label class="clearfix w-60">
                                        <button class="btn btn-primary sku_label_search f-none" type="submit">搜索</button>
                                    </label>
                                    <label class="clearfix sku_label"><a class="btn btn-primary" href="/adwap/add" style="margin-top: 2px;">添加</a></label>
                                </div>
                            </form>

                        </div>
                    </div>
                    <div>
                        <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                            <thead>
                            <tr>
                                <th class="center sku_menu_row5">编号</th>
                                <th class="center sku_menu_row4">活动名称</th>
                                <th class="center sku_menu_row4">广告位</th>
                                <th class="center sku_menu_row5">类型</th>
                                <th class="center sku_menu_row5">开始时间</th>
                                <th class="center sku_menu_row5">结束时间</th>
                                <th class="center sku_menu_row5">活动状态</th>
                                <th class="center sku_menu_row5">排序</th>
                                <th class="center sku_menu_row4"> 操作</th>
                            </tr>
                            </thead>
                            <tbody id="productRuleList">

                            <!-- 遍历商品信息 -->
                            {% if list['list'] is defined %}
                            {% if list['list'] > 0 %}
                            {% for v in list['list'] %}
                            <tr>
                                <td class="center">
                                    {{ v['advertisement_id'] }}
                                </td>
                                <td class="center">
                                    {{ v['advertisement'] }}
                                </td>
                                <td class="center">
                                    {{ v['adpositionid_name'] }}
                                </td>
                                <td class="center">
                                    {% if v['advertisement_type'] == 1 %}
                                    图片广告
                                    {% elseif v['advertisement_type'] == 2 %}
                                    商品推荐
                                    {% elseif v['advertisement_type'] == 3 %}
                                    文字广告
                                    {% elseif v['advertisement_type'] == 4 %}
                                    公告（满减）
                                    {% elseif v['advertisement_type'] == 5 %}
                                    公告（满赠）
                                    {% elseif v['advertisement_type'] == 6 %}
                                    公告（折扣）
                                    {% elseif v['advertisement_type'] == 7 %}
                                    公告（包邮）
                                    {% elseif v['advertisement_type'] == 8 %}
                                    公告（优惠）
                                    {% endif %}
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
                                <td class="center update_sort">
                                    {{ v['order'] }}
                                </td>
                                <td class="center">
                                    <a class="green" href="/adwap/edit?id={{ v['advertisement_id'] }}">
                                        <i class="ace-icon fa fa-pencil bigger-130"></i>
                                        编辑
                                    </a>
                                    {% if time()  <  v['end_time']  %}
                                    |<a class="cancel " data-id="{{ v['advertisement_id'] }}" href="javascript:;"><i class="ace-icon fa fa-file-text"></i>取消</a>
                                    {% endif %}
                                    |
                                    <a class="red del" href="javascript:;" data-id="{{ v['advertisement_id'] }}"><i class="ace-icon fa fa-file-text"></i>删除</a>
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
<script src="http://{{ config.domain.static }}/assets/admin/js/adwap/adList.js"></script>
        {% endblock %}