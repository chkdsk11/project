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
                                <div class="tools-box  tools-box-layout" >
                                    <label class="clearfix">
                                        名称:<input type="text" style="float:none;" placeholder="请输入入口名称" name="name" value="{% if search['name'] is defined %}{{search['name']}}{% endif %}" class="tools-txt sku_input" />
                                    </label>
                                    <label class="clearfix">
                                        编号:<input type="text" style="float:none;" placeholder="请输入入口编号" name="id" value="{% if search['id'] is defined %}{{search['id']}}{% endif %}" class="tools-txt sku_input" />
                                    </label>
                                    <label class="clearfix">
                                        状态:
                                        <select name="status" class="sku_select">
                                            <option value=''>-全部-</option>
                                            <option value="0" {% if search['status'] is defined and search['status']=='0' %} selected = "selected" {% endif %}>显示</option>
                                        <option value="1" {% if search['status'] is defined and search['status']=='1' %} selected = "selected" {% endif %}>隐藏</option>
                                </select>
                            </label>
                            <label class="clearfix" style="margin-top:2px;">
                                <button class="btn btn-primary clear_search" type="submit">清除搜索条件</button>
                            </label>
                            <label class="clearfix" style="width:60px;margin-top:2px;">
                                <button class="btn btn-primary sku_label_search" type="submit">搜索</button>
                            </label>
                            <label class="clearfix sku_label" style="float:right;margin-top:2px;"><a class="btn btn-primary" href="/handyentry/{{action['add']}}">添加</a></label>
                        </div>
                    </form>

                        </div>
                    </div>
                    <div>
                        <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                            <thead>
                            <tr>
                                <th class="center sku_menu_row5">编号</th>
                                <th class="center sku_menu_row4">名称</th>
                                <th class="center sku_menu_row5">图片</th>
                                <th class="center sku_menu_row4">目标地址</th>
                                <th class="center sku_menu_row4">添加时间</th>
                                <th class="center sku_menu_row5">状态</th>
                                <th class="center sku_menu_row5">排序</th>
                                <th class="center sku_menu_row5">备注</th>
                                <!--<th class="center sku_menu_row5">排序</th><-->
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
                                    {{ v['id'] }}
                                </td>
                                <td class="center">
                                    {{ v['name'] }}
                                </td>
                                <td class="center">
                                    <img style="height: 30px; width: 30px; " src="{{ v['icon_img'] }}"/>
                                </td>
                                <td class="center" style="word-wrap:break-word;word-break:break-all;">
                                    {{ v['link'] }}
                                </td>
                                <td class="center">
                                    {{ date('Y-m-d H:i:s', v['add_time']) }}
                                </td>
                                <td class="center">
                                    {% if v['status'] ==0 %}
                                    显示
                                    {% elseif v['status'] == 1 %}
                                    隐藏
                                    {% endif %}
                                </td>

                                <td class="center">
                                    <input class="editsort" style="width:30px;" data-id="{{ v['id'] }}" value="{{  v['sort'] }}"/>
                                </td>
                                <td class="center ">
                                    {{  v['remark'] }}
                                </td>
                                <!--<td class="center update_sort">
                                    {{ v['order'] }}
                                </td>-->
                                <td class="center">
                                    {% if v['status'] ==0 %}
                                    <a class="green chagehide" href="javascript:;" data-id="{{ v['id'] }}"><i class="ace-icon fa fa-file-text"></i>隐藏</a>
                                    {% elseif v['status'] == 1 %}
                                    <a class="green chageshow" href="javascript:;" data-id="{{ v['id'] }}"><i class="ace-icon fa fa-file-text"></i>显示</a>
                                    {% endif %}
                                    |
                                    <a  href="/handyentry/{{action['edit']}}?id={{ v['id'] }}"><i class="ace-icon fa fa-file-text"></i>编辑</a>
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
<script src="http://{{ config.domain.static }}/assets/admin/js/handyentry/common.js"></script>
{% endblock %}