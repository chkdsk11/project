{% extends "layout.volt" %}
{% block script %}
<script>
    var change_sale = new Array;
    var i = 0;
</script>
{% endblock %}
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
    .tools-box-layout > label{
        float: left;
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
                                <div class="tools-box tools-box-layout" >
                                    <label class="clearfix">
                                        活动名称:<input type="text" style="float:none;" placeholder="请输入广告名称或ID" name="name" value="{% if search['name'] is defined  %}{{search['name']}}{% endif %}" class="tools-txt sku_input" />
                                    </label>
                                    <label class="clearfix">
                                        广告位置:
                                        <select name="ad_position" class="sku_select">
                                            <option value="-1">--请选择--</option>
                                            {% if position %}
                                                {% for v in position %}
                                                <option value="{{ v['id']}}" {% if search['ad_position'] is defined and search['ad_position']==v['id'] %} selected = "selected" {% endif %}>{{ v['name']}}</option>
                                                {% endfor %}
                                            {% endif %}
                                        </select>
                                    </label>
                                        <label class="clearfix">
                                            活动类型:
                                            <select name="ad_type" class="sku_select">
                                                <option value="0">-请选择-</option>
                                                <option value="1" {% if search['ad_type'] is defined and search['ad_type']=='1' %} selected = "selected" {% endif %}>商品推荐</option>
                                                <option value="2" {% if search['ad_type'] is defined and search['ad_type']=='2' %} selected = "selected" {% endif %}>图片广告</option>
                                            </select>
                                        </label>
                                        <label class="clearfix">
                                            活动状态:
                                            <select name="ad_status" id="ad_status">
                                                <option value="0">-请选择-</option>
                                                <option value="start"{% if search['ad_status'] is defined and search['ad_status']=='start' %} selected = "selected" {% endif %}>未开始</option>
                                                <option value="middle"{% if search['ad_status'] is defined and search['ad_status']=='middle' %} selected = "selected" {% endif %}>进行中</option>
                                                <option value="end"{% if search['ad_status'] is defined and search['ad_status']=='end' %} selected = "selected" {% endif %}>已结束</option>
                                                <option value="cancel"{% if search['ad_status'] is defined and search['ad_status']=='cancel' %} selected = "selected" {% endif %}>取消</option>
                                            </select>
                                        </label>
                                        <label class="clearfix">
                                            使用端:
                                            <select name="ad_channel" >
                                                <option value="0">-所有-
                                                </option><option value="1" {% if search['ad_channel'] is defined and search['ad_channel']=='1' %} selected = "selected" {% endif %}>APP</option>
                                                <option value="2" {% if search['ad_channel'] is defined and search['ad_channel']=='2' %} selected = "selected" {% endif %}>WAP</option>
                                            </select>
                                        </label>
                                        <!-- <div style="float: right;margin-top: 2px;"> -->
                                            <label class="clearfix" style="margin-top: 2px;"><button class="btn btn-primary clear_search" type="submit" >清除搜索条件</button></label>
                                            <label class="clearfix" style="width:60px;margin-top: 2px;">
                                                <button class="btn btn-primary sku_label_search" type="submit" >搜索</button>
                                            </label>
                                            <label class="clearfix sku_label" style="margin-right:0;margin-top: 2px;"><a class="btn btn-primary" href="/adaccesories/add" >添加海外购</a></label>
                                        <!-- </div> -->
                                        
                                </div>
                            </form>

                        </div>
                    </div>
                    <div>
                        <table id="dynamic-table" class="table table-striped table-bordered table-hover" style="table-layout:fixed;">
                            <thead>
                            <tr>
                                <th class="center sku_menu_row5">编号</th>
                                <th class="center sku_menu_row4">活动名称</th>
                                <th class="center sku_menu_row4">广告位</th>
                                <th class="center sku_menu_row4">类型</th>
                                <th class="center sku_menu_row5">开始时间</th>
                                <th class="center sku_menu_row5">结束时间</th>
                                <th class="center sku_menu_row5">状态</th>
                                <th class="center sku_menu_row5">使用端</th>
                                <th class="center sku_menu_row4" style="width:115px;"> 操作</th>
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
                                    {{ v['name'] }}
                                </td>
                                <td class="center">
                                    {{ v['pname'] }}
                                </td>
                                <td class="center">
                                    {% if  v['ad_type'] ==1 %}
                                    商品推荐
                                    {% elseif v['ad_type'] ==2 %}
                                    图片
                                    {% else %}

                                    {% endif %}
                                </td>
                                <td class="center">
                                    {{ date('Y-m-d H:i:s', v['start_time']) }}
                                </td>
                                <td class="center">
                                    {{ date('Y-m-d H:i:s', v['end_time']) }}
                                </td>
                                <td class="center ">
                                    {% if v['status']==0 %} 已取消{% else %}
                                        {% if time()  <  v['start_time']  %}
                                        未开始
                                        {% elseif time() > v['end_time'] %}
                                        已结束
                                        {% else %}
                                        进行中
                                        {% endif %}
                                    {% endif %}

                                </td>
                                <td class="center update_sort">
                                    {% if  v['channel']==0  %}
                                    所有端
                                    {% elseif v['channel']==1 %}
                                    app
                                    {% elseif v['channel']==2 %}
                                    wap
                                    {% endif %}
                                </td>
                                <td class="center">
                                    <a class="green" href="/adaccesories/edit?id={{ v['id'] }}">
                                        <i class="ace-icon fa fa-pencil bigger-130"></i>
                                        编辑
                                    </a>
                                    |
                                    <a class="red del" data-id="{{ v['id'] }}" href="javascript:;"><i class="ace-icon fa fa-file-text"></i>删除</a>
                                    {% if v['status'] != 0%}
                                        {% if time()  <  v['end_time']  %}
                                    |<a class="cancel " data-id="{{ v['id'] }}" href="javascript:;"><i class="ace-icon fa fa-file-text"></i>取消</a>
                                        {% endif %}
                                    {% endif %}
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
    <script src="http://{{ config.domain.static }}/assets/admin/js/adaccesproes/common.js"></script>
{% endblock %}