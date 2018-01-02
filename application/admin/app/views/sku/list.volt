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
</style>
<div class="page-content">
    <div class="row">
        <div class="col-xs-12">
            <div class="row">
                <div class="col-xs-12">

                    <div class="form-group clearfix">
                        <div class="row">
                            <form action="/sku/list" method="get">
                                <div class="tools-box sku_main">
                                    <label class="clearfix">
                                        商品名或ID:<input type="text" style="float:none;" placeholder="商品名或ID" name="name" value="{{ name }}" class="tools-txt sku_input" />
                                    </label>
                                    <label class="clearfix">
                                        spu名称: <input type="text" style="float:none;" placeholder="spu名称" name="spu_name" value="{{ spu_name }}" class="tools-txt sku_input" />
                                    </label>
                                    <label class="clearfix">
                                        品牌名:<input type="text" style="float:none;" placeholder="品牌名" name="brand" value="{{ brand }}" class="tools-txt sku_input" />
                                    </label>
                                    <label class="sku_menu_row2 sku_menu_main">
                                        分类:{% include "library/category.volt"%}
                                    </label>
                                    <br/>
                                    &nbsp;&nbsp;&nbsp;                <div class="sku_search_keywords">
                                    <label class="clearfix">
                                        是否热门商品:
                                        <select name="is_hot" class="sku_select">
                                            <option value="-1">--请选择--</option>
                                            <option {% if is_hot is defined and is_hot == 1 %}selected{% endif %} value="1">是</option>
                                            <option {% if is_hot is defined and is_hot == 0 %}selected{% endif %} value="0">否</option>
                                        </select>
                                    </label>
                                    <label class="clearfix">
                                        是否推荐商品:
                                        <select name="is_recommend" class="sku_select">
                                            <option value="-1">--请选择--</option>
                                            <option {% if is_recommend is defined and is_recommend == 1 %}selected{% endif %} value="1">是</option>
                                            <option {% if is_recommend is defined and is_recommend == 0 %}selected{% endif %} value="0">否</option>
                                        </select>
                                    </label>
                                    <label class="clearfix">
                                        商品类型:
                                        <select name="drug_type" class="sku_select">
                                            <option value="0">--请选择--</option>
                                            <option {% if drug_type is defined and drug_type == 1 %}selected{% endif %} value="1">处方药</option>
                                            <option {% if drug_type is defined and drug_type == 2 %}selected{% endif %} value="2">红色非处方药</option>
                                            <option {% if drug_type is defined and drug_type == 3 %}selected{% endif %} value="3">绿色非处方药</option>
                                            <option {% if drug_type is defined and drug_type == 4 %}selected{% endif %} value="4">非药物</option>
                                            <option {% if drug_type is defined and drug_type == 5 %}selected{% endif %} value="5">虚拟商品</option>
                                        </select>
                                    </label>
                                    <label class="clearfix">
                                        上下架状态:
                                        <select name="is_on_sale" class="sku_select">
                                            <option value="0">所有端</option>
                                            <option {% if is_on_sale is defined and is_on_sale == 1 %}selected{% endif %} value="1">pc端上架</option>
                                            <option {% if is_on_sale is defined and is_on_sale == 2 %}selected{% endif %} value="2">pc端下架</option>
                                            <option {% if is_on_sale is defined and is_on_sale == 3 %}selected{% endif %} value="3">app端上架</option>
                                            <option {% if is_on_sale is defined and is_on_sale == 4 %}selected{% endif %} value="4">app端下架</option>
                                            <option {% if is_on_sale is defined and is_on_sale == 5 %}selected{% endif %} value="5">wap端上架</option>
                                            <option {% if is_on_sale is defined and is_on_sale == 6 %}selected{% endif %} value="6">wap端下架</option>
                                            <option {% if is_on_sale is defined and is_on_sale == 7 %}selected{% endif %} value="7">微商城端上架</option>
                                            <option {% if is_on_sale is defined and is_on_sale == 8 %}selected{% endif %} value="8">微商城端下架</option>
                                        </select>
                                    </label>
                                </div>
                                    <label class="clearfix sku_label">
                                        <button class="btn btn-primary sku_label_search" type="submit">搜索</button>
                                    </label>

                                </div>
                            </form>

                        </div>
                    </div>
                    <div>
                        <div style="margin-bottom: 15px;">
                            <button class="btn btn-sm btn-info is_shelves" data-id="1">上架</button>
                            <button class="btn btn-sm btn-info is_shelves" data-id="0">下架</button>
                        </div>
                    </div>
                    <div>
                        <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                            <thead>
                            <tr>
                                <th class="center">
                                    <input type="checkbox" class="checkbox_select">全选
                                </th>
                                <th class="center sku_menu_row5">
                                    SKU ID
                                </th>
                                <th class="center sku_menu_row3">商品名称</th>
                                <th class="center sku_menu_row4">类型</th>
                                <th class="center sku_menu_row4">
                                    分类
                                </th>
                                <th class="center sku_menu_row5">
                                    <select class="change_sale">
                                        <option value="0">所有端</option>
                                        <option value="pc">pc端</option>
                                        <option value="app">app端</option>
                                        <option value="wap">wap端</option>
                                        <option value="wechat">微商城端</option>
                                    </select>
                                </th>
                                <th class="center sku_menu_row5">
                                    热门商品
                                </th>
                                <th class="center sku_menu_row5">
                                    推荐商品
                                </th>
                                <th class="center sku_menu_row5">
                                    是否锁定
                                </th>
                                <th class="center sku_menu_row5">
                                    排序
                                </th>
                                <th class="center sku_menu_row4">
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
                                <td class="center" style="width: 100px;">
                                    <input type="checkbox" name="checkbox" value="{{ v['id'] }}">
                                </td>
                                <td class="center">
                                    {{ v['id'] }}
                                </td>
                                <td>
                                    {% if v['small_path'] is defined %}<img style="width:40px;" src="{{v['small_path']}}">{% endif %}{{ v['goods_name'] }}
                                </td>
                                <td class="center">
                                    {% if v['drug_type'] is defined %}
                                    {{ v['drug_type'] }}
                                    {% endif %}
                                </td>
                                <td class="center">
                                    {% if v['category_name'] is defined %}
                                    {{ v['category_name'] }}
                                    {% endif %}
                                </td>
                                <td class="center sale_is_points_{{ v['id'] }}">
                                    {% if v['is_on_sale'] == 1 or v['sale_timing_wap'] == 1 or v['sale_timing_app'] == 1 or v['sale_timing_wechat'] == 1 %}
                                    <span>上架</span>
                                    {% endif %}
                                    <br/>
                                    {% if v['is_on_sale'] == 0 or v['sale_timing_wap'] == 0 or v['sale_timing_app'] == 0 or v['sale_timing_wechat'] == 0 %}
                                    <span>下架</span>
                                    {% endif %}
                                </td>
                                <td class="center">
                                    <a href="javascript:" class="is_hot" data-id="{{ v['id'] }}" name="{{ v['is_hot'] }}">
                                        {% if v['is_hot'] == 1%}<i class="ace-icon glyphicon btn-xs btn-info glyphicon-ok"></i>
                                        {% else %}<i class="ace-icon glyphicon btn-xs btn-danger glyphicon-remove"></i>
                                        {% endif %}
                                    </a>
                                </td>
                                <td class="center">
                                    <a href="javascript:" class="is_recommend" data-id="{{ v['id'] }}" name="{{ v['is_recommend'] }}">
                                        {% if v['is_recommend'] == 1%}<i class="ace-icon glyphicon btn-xs btn-info glyphicon-ok"></i>
                                        {% else %}<i class="ace-icon glyphicon btn-xs btn-danger glyphicon-remove"></i>
                                        {% endif %}
                                    </a>
                                </td>
                                <td class="center">
                                    <a href="javascript:" class="is_lock" data-id="{{ v['id'] }}" name="{{ v['is_lock'] }}">
                                        {% if v['is_lock'] == 1%}<i class="ace-icon glyphicon btn-xs btn-info glyphicon-ok"></i>
                                        {% else %}<i class="ace-icon glyphicon btn-xs btn-danger glyphicon-remove"></i>
                                        {% endif %}
                                    </a>
                                </td>
                                <td class="center">
                                    {{ v['sort'] }}
                                </td>
                                <td class="center">
                                    <a class="green" href="/spu/edit?id={{ v['spu_id'] }}">
                                        <i class="ace-icon fa fa-pencil bigger-130"></i>
                                        编辑
                                    </a>
                                    <!--|-->
                                    <!--<a class="green" href="#">-->
                                    <!--<i class="ace-icon fa fa-file-text"></i>-->
                                    <!--查看-->
                                    <!--</a>-->
                                </td>
                            </tr>
                            <script>
                                change_sale[i] = {};
                                change_sale[i]['id'] = {{ v['id'] }};
                                change_sale[i]['is_on_sale'] = {{ v['is_on_sale'] }};
                                change_sale[i]['sale_timing_wap'] = {{ v['sale_timing_wap'] }};
                                change_sale[i]['sale_timing_app'] = {{ v['sale_timing_app'] }};
                                change_sale[i]['sale_timing_wechat'] = {{ v['sale_timing_wechat'] }};
                                i++;
                            </script>
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
<div class="popup hide" id="popup_sale">
    <div class="popup-content">
        <div class="title">请选择需要上架\下架的平台： <a id="close_popup_stock" href="javascript:;">&times;</a></div>
        <div class="radio stock-radio">
            <div class="checkbox stock-checkbox">
                <label>
                    <input name="stock_checkbox" type="checkbox" class="ace" value="pc" >
                    <span class="lbl">pc 端</span>
                </label>
            </div>
            <div class="checkbox stock-checkbox">
                <label>
                    <input name="stock_checkbox" type="checkbox" class="ace"  value="app">
                    <span class="lbl">app 端</span>
                </label>
            </div>
            <div class="checkbox stock-checkbox">
                <label>
                    <input name="stock_checkbox" type="checkbox" class="ace" value='wap' >
                    <span class="lbl">wap 端</span>
                </label>
            </div>
            <div class="checkbox stock-checkbox">
                <label>
                    <input name="stock_checkbox" type="checkbox" class="ace" value='wechat' >
                    <span class="lbl">微商城 端</span>
                </label>
            </div>
            <button	 id="save_stock" style="margin-left:230px;">保存</button>
            <input type="hidden" name="sale" value=""/>
        </div>
    </div>
</div>
{% endblock %}

{% block footer %}
<script src="http://{{ config.domain.static }}/assets/admin/js/sku/list.js"></script>
{% endblock %}