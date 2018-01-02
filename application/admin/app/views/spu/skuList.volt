{% extends "layout.volt" %}

{% block script %}
<!--<script>-->
    <!--var change_sale = new Array;-->
    <!--var i = 0;-->
<!--</script>-->
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
    <script>
        var change_sale = new Array;
        var i = 0;
    </script>
    <div class="row">
        <div class="col-xs-12">
            <div class="row">
                <div class="col-xs-12">

                    <div class="form-group clearfix">
                        <div class="row">
                            <form action="/spu/skuList" method="get" id="search_form">
                                <div class="tools-box" style="margin-left:20px ">
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
                    <div class="tools-box" style="height:38px;">
                            <button class="btn btn-sm btn-info is_shelves" data-id="1">上架</button>
                            <button class="btn btn-sm btn-info is_shelves" data-id="0">下架</button>
                        <button class="btn btn-primary btn-rig" type="button" id="btn-export">导出搜索结果</button>
                                <button class="btn btn-primary btn-rig" type="button" id="import">确认导入</button>
                                <label class="btn-rig">
                                    <span class="btn btn-primary" >批量导入商品</span>

                                    <span class="file-name">请选择导入的文件</span>
                                    <input type="file" id="files" class="js-file-name" name="files" onchange="(function(input, tip) {
                                         tip.html(input.get(0).files[0].name);
                                    })($(this), $(this).parent().find('.file-name'))"/>
                                </label>
                        <label class="btn-rig">
                            &nbsp;请选择导入类型:
                            <select name="import_type" id="import_type" class="sku_select">
                                <option value="1">商品信息</option>
                                <option value="2">药品说明书</option>
                                <option value="3">修改商品信息</option>
                            </select>
                        </label>
                        <button type="button" class="btn btn-sm btn-purple" onclick="location.href='http://{{ config.domain.static }}/assets/csv/新增商品模板.xlsx'">新增商品模板下载</button>
                        <button type="button" class="btn btn-sm btn-purple" onclick="location.href='http://{{ config.domain.static }}/assets/csv/修改商品模板.xlsx'">修改商品模板下载</button>
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
                                    {% if v['small_path'] is defined %}<img style="width:40px;" src="{{v['small_path']}}">{% endif %}{{ v['sku_mobile_name'] }}
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
                                    {% if v['is_on_sale'] == 1 or v['sale_timing_wap'] == 1 or v['sale_timing_wechat'] == 1 %}
                                    <span>上架</span>
                                    {% endif %}
                                    <br/>
                                    {% if v['is_on_sale'] == 0 or v['sale_timing_wap'] == 0 or v['sale_timing_wechat'] == 0 %}
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
                                <td class="center update_sort">
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
                                //change_sale[i]['sale_timing_wap'] = {{ v['sale_timing_wap'] }};
                                //change_sale[i]['sale_timing_app'] = {{ v['sale_timing_app'] }};
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
            <!--<div class="checkbox stock-checkbox">
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
            </div>-->
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
<div id="popup-export" class="popup-import" style="display: none;">
    <form action="/spu/export" method="post" id="export_form">
    <div class="popup-content">
        <i class="icon-close js-close">&times;</i>
        <input type="hidden" name="thisurl" value='{{ thisurl }}'>
        <div class="popup-title">
            <label class="select-all" id="btnokspu">商品SPU信息<input type="checkbox" data-select="export">全选/取消全选</label>
        </div>
        <div class="popup-body" id="Echeckboxspu">
            <label class="select-item"><input name="spu_id" value="spu_id" type="checkbox">SPU编号</label>
            <label class="select-item"><input name="spu_name" value="spu_name" type="checkbox">SPU通用名</label>
            <label class="select-item"><input name="brand_name" value="brand_name" type="checkbox">品牌</label>
            <label class="select-item"><input name="drug_type" value="drug_type" type="checkbox">药品类型</label>
            <label class="select-item"><input name="category_id" value="category_id" type="checkbox">所属分类</label>

        </div>
        <div class="popup-title">
            <label class="select-all" id="btnokattr">商品属性信息<input type="checkbox" data-select="export">全选/取消全选</label>
        </div>
        <div class="popup-body" id="Echeckboxattr">
            <label class="select-item"><input name="attr_list" value="attr_list" type="checkbox">属性</label>
            <label class="select-item"><input name="goods_id" value="goods_id" type="checkbox">SKUID</label>
            <label class="select-item"><input name="product_code" value="product_code" type="checkbox">ERP编码</label>
            <label class="select-item"><input name="attribute_value_id" value="attribute_value_id" type="checkbox">属性值</label>
            <label class="select-item"><input name="goods_price" value="goods_price" type="checkbox">销售价</label>
            <label class="select-item"><input name="market_price" value="market_price" type="checkbox">市场价</label>
            <label class="select-item"><input name="stock_1" value="stock_1" type="checkbox">真实库存</label>
            <label class="select-item"><input name="stock_2" value="stock_2" type="checkbox">虚拟库存</label>
            <label class="select-item"><input name="is_lock" value="is_lock" type="checkbox">是否锁定</label>
            <label class="select-item"><input name="sort" value="sort" type="checkbox">排序</label>
            <label class="select-item"><input name="gift_yes" value="gift_yes" type="checkbox">是否赠品</label>
            <label class="select-item"><input name="status_1" value="status_1" type="checkbox">上架信息</label>
            <label class="select-item"><input name="status" value="status" type="checkbox">下架信息</label>

        </div>
        <div class="popup-title">
            <label class="select-all" id="btnokgoods">商品参数信息<input type="checkbox" data-select="export">全选/取消全选</label>
        </div>
        <div class="popup-body" id="Echeckboxgoods">
            <label class="select-item"><input name="sku_alias_name" value="sku_alias_name" type="checkbox">别名</label>
            <label class="select-item"><input name="goods_name" value="goods_name" type="checkbox">商品名称PC端</label>
            <label class="select-item"><input name="sku_pc_subheading" value="sku_pc_subheading" type="checkbox">商品副标题PC端</label>
            <label class="select-item"><input name="sku_mobile_name" value="sku_mobile_name" type="checkbox">商品名称移动端</label>
            <label class="select-item"><input name="sku_mobile_subheading" value="sku_mobile_subheading" type="checkbox">商品副标题移动端</label>
            <label class="select-item"><input name="sku_label" value="sku_label" type="checkbox">产品标签</label>
            <label class="select-item"><input name="period" value="period" type="checkbox">有效期</label>
            <label class="select-item"><input name="usage" value="usage" type="checkbox">用法</label>
            <label class="select-item"><input name="zysx" value="zysx" type="checkbox">参数自有属性</label>

        </div>
        <div class="popup-title">
            <label class="select-all" id="btnok">药品说明书<input type="checkbox" name="instruction" value="instruction">全选</label>
        </div>
        <div class="popup-title">
            <label class="select-all">导出条数:</label>
            <select name="type_num" id="type_num" class="sku_select">
                {% for v in list['numarr'] %}
                    <option value="{{v}}">{{v}}</option>
                {% endfor %}
            </select>
        </div>
        <div class="popup-foot">
            <p class="brn-wrap">
                <button type="submit" class="addbtn-link" onclick="$('#popup-export').hide();">导出</button>
            </p>
        </div>
    </div>
    </form>
</div>
{% endblock %}

{% block footer %}
<script src="http://{{ config.domain.static }}/assets/js/ajaxfileupload.js"></script>
<script src="http://{{ config.domain.static }}/assets/admin/js/sku/list.js"></script>
{% endblock %}