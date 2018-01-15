{% extends "layout.volt" %}

{% block content %}
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/bootstrap.min.css" />
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/font-awesome/4.2.0/css/font-awesome.min.css" />

<!-- page specific plugin styles -->
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/jquery-ui.custom.min.css" />
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/chosen.min.css" />
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/datepicker.min.css" />
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/bootstrap-timepicker.min.css" />
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/daterangepicker.min.css" />
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/colorpicker.min.css" />
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/product_spu.css" />

<!-- text fonts -->
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/fonts/fonts.googleapis.com.css" />

<!-- ace styles -->
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/ace.min.css" class="ace-main-stylesheet" id="main-ace-style" />

<!--[if lte IE 9]>
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/ace-part2.min.css" class="ace-main-stylesheet" />
<![endif]-->

<!--[if lte IE 9]>
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/ace-ie.min.css" />
<![endif]-->

<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/jquery-ui.min.css" />

<!-- inline styles related to this page -->

<!-- ace settings handler -->
<script src="http://{{ config.domain.static }}/assets/js/ace-extra.min.js"></script>

<!-- HTML5shiv and Respond.js for IE8 to support HTML5 elements and media queries -->

<!--[if lte IE 8]>
<script src="http://{{ config.domain.static }}/assets/js/html5shiv.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/respond.min.js"></script>
<![endif]-->
<script src="http://{{ config.domain.static }}/assets/js/jquery.min.js"></script>
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/product.css" />
<style type="text/css">
    #shop_category .col-xs-12,#shop_category,.width500{
        width:500px;
    }
    #shop_category select {
        width:32%;
    }
    .width500{
        float: left;
    }
    .text-red-p{
        line-height: 42px;
    }
</style>
            <!--    新建商品&#45;&#45;多品规  start              -->
            <div class="product-container">
                <!--创建spu  start  -->
                <div class="product-panel add_spu_on">
                    <div class="title-prompt">基础信息</div>
                    <form class="form-horizontal" action="{% if act is not defined %}/spu/add{% else %}/spu/edit{% endif %}" method="post" role="form" id="form_step1">
                        <div class="form-group">
                            <label class="col-sm-1 control-label no-padding-right"><span  class="text-red">*</span>SPU通用名</label>
                            <div class="col-sm-9">
                                <input type="text" name="spu_name" id="spu_name" value="{% if spu['spu_name'] is defined %}{{ spu['spu_name'] }} {% endif %}" class="col-xs-10 col-sm-5">
											<span class="help-inline col-xs-12 col-sm-7">
												<span class="middle">最多输入<span id="word_left">30</span>字</span>
											</span>
                            </div>
                        </div>
                        <div class="space-4"></div>
                        <div class="form-group">
                            <label class="col-sm-1 control-label no-padding-right" for="spu_supplier"><span  class="text-red">*</span>商家</label>
                            <div class="col-sm-5" style="width: 366px;">
                                <select name="shop_id" class="chosen-select col-xs-5 col-sm-5" id="spu_supplier" data-placeholder="请选择商家" style="display: none;">
                                    <option value=""></option>
                                    {% if supplier is defined %}
                                    {% for v in supplier %}
                                    <option  {% if spu['shop_id'] is defined and spu['shop_id'] == v['id'] %}selected{% endif %} value="{{v['id']}}">{{v['name']}}</option>
                                    {% endfor %}
                                    {% endif %}
                                </select>
                            </div>
                        </div>
                        <div class="space-4"></div>
                        <div class="form-group">
                            <label class="col-sm-1 control-label no-padding-right" for="spu_type"><span  class="text-red">*</span>品牌</label>
                            <div class="col-sm-5" style="width: 366px;">
                                <select name="brand_id" class="chosen-select col-xs-5 col-sm-5" id="spu_type" data-placeholder="请选择品牌" style="display: none;">
                                    <option value=""></option>
                                    {% if brand is defined %}
                                    {% for v in brand %}
                                    <option  {% if spu['brand_id'] is defined and spu['brand_id'] == v['id'] %}selected{% endif %} value="{{v['id']}}">{{v['brand_name']}}</option>
                                    {% endfor %}
                                    {% endif %}
                                </select>
                            </div>
                            <div style="height:32px;line-height:32px;"><a href="/brands/add" target="_blank">添加新品牌</a></div>
                        </div>
                        <div class="space-4"></div>
                        <div class="form-group">
                            <label class="col-sm-1 control-label no-padding-right" for="product_kind" ><span  class="text-red" >*</span>药物类型</label>
                            <div class="col-sm-9">
                                <select name="drug_type" class="col-xs-5 col-sm-5" id="product_kind">
                                    <option value="">--请选择--</option>
                                    <option {% if spu['drug_type'] is defined and spu['drug_type'] == 1 %}selected{% endif %} value="1">处方药</option>
                                    <option {% if spu['drug_type'] is defined and spu['drug_type'] == 2 %}selected{% endif %} value="2">红色非处方药</option>
                                    <option {% if spu['drug_type'] is defined and spu['drug_type'] == 3 %}selected{% endif %} value="3">绿色非处方药</option>
                                    <option {% if spu['drug_type'] is defined and spu['drug_type'] == 4 %}selected{% endif %} value="4">非药物</option>
                                    <option {% if spu['drug_type'] is defined and spu['drug_type'] == 5 %}selected{% endif %} value="5">虚拟商品</option>
                                </select>
                            </div>
                        </div>
                        <div class="space-4"></div>
                        <div class="form-group">
                            <label class="col-sm-1 control-label no-padding-right"  ><span  class="text-red">*</span>商品分类</label>
                            <label class="width500 control-label no-padding-right product-classify">
                                {% include "library/category.volt"%}
                            </label>
                            <span class="text-red text-red-p">修改分类会清空多品规和参数信息，请谨慎操作</span>
                        </div>
                        {% if act is defined %}<input id="spu_id" name="id" type="hidden" value="{{ spu['spu_id'] }}">{% endif %}
                        <div class="space-4"></div>
                        <div class="col-md-offset-3 col-md-9">
                            <button class="btn btn-info" type="button" id="save_spu_btn">保存spu</button>
                        </div>
                    </form>
                </div>
                <!--创建spu   end -->

                <!--添加商品  start  -->
                <div class="product-panel">
                    <div class="edit-rule-list">
                        <div class="title-prompt">添加商品属性</div>
                        <table id="product_table" class="product-table table table-bordered ">
                            <thead>
                            <tr>
                                <th class="center">商品ID</th>
                                <th class="center">ERP编码</th>
                                <th class="center">{% if productRule is defined and productRule['name_id'] is defined %}{{ productRule['name_id']['name'] }}{% endif %}</th>
                                <th class="center">{% if productRule is defined and productRule['name_id2'] is defined %}{{ productRule['name_id2']['name'] }}{% endif %}</th>
                                <th class="center">{% if productRule is defined and productRule['name_id3'] is defined %}{{ productRule['name_id3']['name'] }}{% endif %}</th>

                                <th class="center">销售价</th>
                                <th class="center">市场价</th>
                                <th class="center">是否为赠品</th>
                                <th class="center">库存</th>
                                <th class="center">是否锁定</th>
                                <th class="center">排序</th>
                                <th class="center">商家</th>
                                <th class="center">操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            {% if skuinfo is defined and is_array(skuinfo) %}
                            {% for v in skuinfo %}
                            <tr id="product_list_{{ v['id'] }}">
                                <form class="form-horizontal form_sku" action="/sku/edit" method="post" role="form" >
                                    <td class="center">
                                    <input type="text" name="sku_id" value="{{ v['id'] }}" readonly />
                                    </td>
                                    <td class="center">
                                    <input type="text" name="product_code" value="{{ v['product_code'] }}" readonly />
                                    </td>
                                    <td class="center">
                                    <input type="text" disabled="true" name="rule_value[]" class="rule_value_1" value="{% if v['rule'] is defined and v['rule'][0] is defined and productRule is defined and productRule['name_id'] is defined and v['rule'][0]['pid'] is productRule['name_id']['id'] %}{{ v['rule'][0]['name'] }}{% endif %}" {% if productRule is not defined or productRule['name_id'] is not defined %}readonly{% endif %}/>
                                    <input type="hidden" name="rule_pid[]" value="{% if productRule is defined and productRule['name_id'] is defined %}{{ productRule['name_id']['id'] }}{% else %}0{% endif %}">
                                    </td>
                                    <td class="center">
                                    <input type="text" disabled="true" name="rule_value[]" class="rule_value_2" value="{% if v['rule'] is defined and v['rule'][1] is defined and productRule is defined and productRule['name_id2'] is defined and v['rule'][1]['pid'] is productRule['name_id2']['id'] %}{{ v['rule'][1]['name'] }}{% endif %}" {% if productRule is not defined or productRule['name_id2'] is not defined %}readonly{% endif %}/>
                                        <input type="hidden" name="rule_pid[]" value="{% if productRule is defined and productRule['name_id2'] is defined %}{{ productRule['name_id2']['id'] }}{% else %}0{% endif %}">
                                    </td>
                                    <td class="center">
                                    <input type="text" disabled="true" name="rule_value[]" class="rule_value_3" value="{% if v['rule'] is defined and v['rule'][2] is defined and productRule is defined and productRule['name_id3'] is defined and v['rule'][2]['pid'] is productRule['name_id3']['id'] %}{{ v['rule'][2]['name'] }}{% endif %}" {% if productRule is not defined or productRule['name_id3'] is not defined %}readonly{% endif %}/>
                                        <input type="hidden" name="rule_pid[]" value="{% if productRule is defined and productRule['name_id3'] is defined %}{{ productRule['name_id3']['id'] }}{% else %}0{% endif %}">
                                    </td>
                                    <!--销售价-->
                                    <td class="center">
                                        <input type="text" disabled="true"  name="goods_price" value="{{ v['goods_price'] }}" class="product-price {% if v['is_unified_price'] is defined and v['is_unified_price'] == 1 %}hide{% endif %}" />
                                        <div class="price-all {% if v['is_unified_price'] is defined and v['is_unified_price'] == 0 %}hide{% endif %}">
                                        {% if shopPlatform is defined and shopPlatform is not empty %}
                                            {% set platformCount = shopPlatform|length %}
                                            {% for lowerPlatform,platform in shopPlatform %}
                                                {% set goodsPlatform = "goods_price_"~lowerPlatform %}
                                                <div>{{ platform }}: <input disabled="true" type="text" name="goods_price_{{ lowerPlatform }}" value="{{ v['info'][goodsPlatform] }}" /> </div>
                                            {% endfor %}
                                        {% endif %}
                                        <!--<div>P C:  <input disabled="true" type="text" name="goods_price_pc" value="{{ v['info']['goods_price_pc'] }}" /> </div>
                                        <div>APP: <input disabled="true" type="text" name="goods_price_app" value="{{ v['info']['goods_price_app'] }}" /> </div>
                                        <div>WAP: <input disabled="true" type="text" name="goods_price_wap" value="{{ v['info']['goods_price_wap'] }}" /> </div>
                                        <div>微商城: <input disabled="true" type="text" name="goods_price_wechat" value="{{ v['info']['goods_price_wechat'] }}" /> </div>-->
                                        </div>
                                        <br/>
                                        {% if platformCount is defined and platformCount > 1 %}
                                        <button type="button" class="set_price">{% if v['is_unified_price'] is defined and v['is_unified_price'] == 1 %}设置默认{% else %}设置不同端{% endif %}</button>
                                        {% elseif v['is_unified_price'] is defined and v['is_unified_price'] == 1 %}
                                            <button type="button" class="set_price">设置默认</button>
                                        {% endif %}
                                        <input type="hidden" name="is_unified_price" class="is_unified_price" value="{% if v['is_unified_price'] is defined and v['is_unified_price'] == 1 %}1{% else %}0{% endif %}" />
                                    </td>
                                    <!--市场价-->
                                    <td class="center">
                                        <input type="text" disabled="true" name="market_price" value="{{ v['market_price'] }}"  class="product-price {% if v['is_unified_price'] is defined and v['is_unified_price'] == 1 %}hide{% endif %}"  />
                                        <div class="price-all {% if v['is_unified_price'] is defined and v['is_unified_price'] == 0 %}hide{% endif %}">
                                            {% if shopPlatform is defined and shopPlatform is not empty %}
                                                {% for lowerPlatform,platform in shopPlatform %}
                                                    {% set marketPlatform = 'market_price_'~ lowerPlatform %}
                                                    <div>{{ platform }}: <input disabled="true" type="text" name="market_price_{{ lowerPlatform }}" value="{{ v['info'][marketPlatform] }}" /> </div>
                                                {% endfor %}
                                            {% endif %}
                                        <!--<div>P C: <input type="text" disabled="true" name="market_price_pc" value="{{ v['info']['market_price_pc'] }}" /> </div>
                                        <div>APP: <input type="text" disabled="true" name="market_price_app" value="{{ v['info']['market_price_app'] }}" /> </div>
                                        <div>WAP: <input type="text" disabled="true" name="market_price_wap" value="{{ v['info']['market_price_wap'] }}" /> </div>
                                        <div>微商城: <input type="text" disabled="true" name="market_price_wechat" value="{{ v['info']['market_price_wechat'] }}" /> </div>-->
                                        </div>
                                    </td>

                                    <td class="center">
                                        <select id="is_gift_change" name="is_gift" disabled="true" class="is_gift {% if v['info']['whether_is_gift'] is defined and v['info']['whether_is_gift'] == 1 %}hide{% endif %}">
                                            <option  value="0" {% if v['product_type'] is defined and v['product_type'] == 0 %}selected{% endif %}>否</option>
                                            <option value="1" {% if v['product_type'] is defined and v['product_type'] == 1 %}selected{% endif %}>普通赠品</option>
                                            <option value="2" {% if v['info'] is defined and v['info']['whether_is_gift'] is defined and v['info']['whether_is_gift'] == 2 %}selected{% endif %}>附属赠品</option>
                                        </select>
                                        <div class="gift-all {% if v['info']['whether_is_gift'] is not defined or v['info']['whether_is_gift'] != 1 %}hide{% endif %}">
                                            {% if shopPlatform is defined and shopPlatform is not empty %}
                                                {% for lowerPlatform,platform in shopPlatform %}
                                                    {% set giftPlatform = 'gift_'~ lowerPlatform %}
                                                    <div>
                                                        <span>{{ platform }}:</span>
                                                        <select name="gift_{{ lowerPlatform }}" disabled="true">
                                                            <option  value="0" {% if v['info'][giftPlatform] is not defined or v['info'][giftPlatform] != 1 %}selected{% endif %}>否</option>
                                                            <option value="1" {% if v['info'][giftPlatform] is defined and v['info'][giftPlatform] == 1 %}selected{% endif %}>普通赠品</option>
                                                        </select>
                                                    </div>
                                                {% endfor %}
                                            {% endif %}
                                            <!--<div>
                                                <span>P C:</span>
                                                <select name="gift_pc" disabled="true">
                                                    <option  value="0" {% if v['info']['gift_pc'] is not defined or v['info']['gift_pc'] != 1 %}selected{% endif %}>否</option>
                                                    <option value="1" {% if v['info']['gift_pc'] is defined and v['info']['gift_pc'] == 1 %}selected{% endif %}>普通赠品</option>
                                                </select>
                                            </div>
                                            <div>
                                                <span>APP:</span>
                                                <select name="gift_app" disabled="true">
                                                    <option  value="0" {% if v['info']['gift_app'] is not defined or v['info']['gift_app'] != 1 %}selected{% endif %}>否</option>
                                                    <option value="1" {% if v['info']['gift_app'] is defined and v['info']['gift_app'] == 1 %}selected{% endif %}>普通赠品</option>
                                                </select>
                                            </div>
                                            <div>
                                                <span>WAP:</span>
                                                <select name="gift_wap" disabled="true">
                                                    <option  value="0" {% if v['info']['gift_wap'] is not defined or v['info']['gift_wap'] != 1 %}selected{% endif %}>否</option>
                                                    <option value="1" {% if v['info']['gift_wap'] is defined and v['info']['gift_wap'] == 1 %}selected{% endif %}>普通赠品</option>
                                                </select>
                                            </div>
                                            <div>
                                                <span>微商城:</span>
                                                <select name="gift_wechat" disabled="true">
                                                    <option  value="0" {% if v['info']['gift_wechat'] is not defined or v['info']['gift_wechat'] != 1 %}selected{% endif %}>否</option>
                                                    <option value="1" {% if v['info']['gift_wechat'] is defined and v['info']['gift_wechat'] == 1 %}selected{% endif %}>普通赠品</option>
                                                </select>
                                            </div>-->
                                        </div>
                                        <br/>

                                        {% if platformCount is defined and platformCount > 1 %}
                                        <button type="button" class="set_whether_is_gift">{% if v['info']['whether_is_gift'] is defined and v['info']['whether_is_gift'] == 1 %}设置默认{% else %}设置不同端{% endif %}</button>
                                        {% elseif v['is_unified_price'] is defined and v['is_unified_price'] == 1 %}
                                        <button type="button" class="set_whether_is_gift">设置默认</button>
                                        {% endif %}
                                        <input type="hidden" name="set_whether_is_gift" class="set_whether_is_gift" value="{% if v['info']['whether_is_gift'] is defined and v['info']['whether_is_gift'] == 1 %}1{% else %}0{% endif %}">
                                    </td>

                                    <td class="center" data-id="{{ v['id'] }}">
                                        <div class="product_real_stock">
                                            {% if v['is_use_stock'] is defined and v['is_use_stock'] == 1 %}
                                                真实库存：{{ v['v_stock'] }}件
                                            {% elseif v['is_use_stock'] is defined and v['is_use_stock'] == 2 %}
                                                公共虚拟库存：{{ v['info']['virtual_stock_default'] }}件
                                            {% elseif v['is_use_stock'] is defined and v['is_use_stock'] == 3 %}
                                                {% if shopPlatform is defined and shopPlatform is not empty %}
                                                    {% for lowerPlatform,platform in shopPlatform %}
                                                        {% set stockPlatform = 'virtual_stock_'~ lowerPlatform %}
                                                        <div>{{ platform }}虚拟库存: {{ v['info'][stockPlatform] }}件 </div>
                                                    {% endfor %}
                                                {% endif %}
                                                <!--<div>APP虚拟库存: {{ v['info']['virtual_stock_app'] }}件 </div>
                                                <div>WAP虚拟库存: {{ v['info']['virtual_stock_wap'] }}件</div>
                                                <div>微商城虚拟库存: {{ v['info']['virtual_stock_wechat'] }}件</div>-->
                                            {% endif %}
                                            </div>
                                        <br><a href="javascript:;" class="modify-stock" >修改库存</a>
                                    </td>
                                    <td class="center">
                                        <select name="is_lock" disabled="true">
                                            <option  value="0" {% if v['is_lock'] is defined and v['is_lock'] == 0 %}selected{% endif %}>否</option>
                                            <option value="1" {% if v['is_lock'] is defined and v['is_lock'] == 1 %}selected{% endif %}>是</option>
                                        </select>
                                    </td>
                                    <td class="center">
                                        <input type="text" disabled="true" name="sort" style="width: 50px;text-align:center;" value="{{ v['sort'] }}">
                                    </td>
                                    <td class="center">
                                        {{ v['supplier_name'] }}
                                    </td>
                                    <td class="center">
                                        <button type="button" class="product-list-delect"> 删除</button>
                                        <br>
                                            <input type="hidden" name="spu_id" value="{{ spu['spu_id'] }}">
                                        <button type="button" class="save_msg"> 修改</button>
                                            <!--<br>-->
                                        <!--<button type="button" class="save_msg_cancel hide">取消</button>-->
                                    </td>
                                </form>
                            </tr>
                            {% endfor %}
                            {% endif %}
                            </tbody>
                        </table>
                        <div class="col-md-offset-3">
                            <input type="hidden" id="productRuleNum" value="{{ productRuleNum }}">
                            <button class="btn btn-info" type="button" id="add_product" >+添加商品</button>
                        </div>
                    </div>
                    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close close-stock" data-dismiss="modal" aria-hidden="true">
                                        &times;
                                    </button>
                                    <h4 class="modal-title" id="myModalLabel">
                                        库存管理
                                    </h4>
                                </div>
                                <form  action="/sku/setStock" method="post" id="set_stock_save" role="form" >
                                    <div class="modal-body">
                                        <div id="real-stock"><label><input type="radio" name="stock" class="stock stock-real" value="1">真实库存：</label><span class="stock-real-k">2000</span>件</div>
                                        <div><label><input type="radio" name="stock" class="stock stock-public" value="2">公共虚拟库存：</label><input type="text" class="pdc-stock-default" name="virtual_stock_default" value="">件</div>
                                        <div>
                                            <label><input type="radio" name="stock" class="stock stock-virtual" value="3">虚拟库存:</label><br>
                                            {% if shopPlatform is defined and shopPlatform is not empty %}
                                                {% for lowerPlatform,platform in shopPlatform %}
                                                    {% set stockPlatform = 'virtual_stock_'~ lowerPlatform %}
                                                    {% set pdc_stock = 'pdc-stock-'~ lowerPlatform %}
                                                    <label class="virtual_stock_value">{{ platform }}虚拟库存：<input type="text" class="{{ pdc_stock }}" name="{{ stockPlatform }}" value="">件</label>
                                                {% endfor %}
                                            {% endif %}
                                            <!--<label class="virtual_stock_value">PC虚拟库存：<input type="text" class="pdc-stock-pc" name="virtual_stock_pc" value="">件</label>
                                            <label class="virtual_stock_value">WAP虚拟库存：<input type="text" class="pdc-stock-wap" name="virtual_stock_wap" value="">件</label>
                                            <label class="virtual_stock_value">APP虚拟库存：<input type="text" class="pdc-stock-app" name="virtual_stock_app" value="">件</label>
                                            <label class="virtual_stock_value">微商城虚拟库存：<input type="text" class="pdc-stock-wechat" name="virtual_stock_wechat" value="">件</label>-->
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">关闭
                                        </button>
                                        <input type="hidden" name="id" value="" class="pdc-id">
                                        <button type="button" class="btn btn-primary">
                                            提交更改
                                        </button>
                                    </div>
                                </form>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal -->
                    </div>
                <!--添加商品   end -->

                <!--添加商品信息 start -->
                <div class="product-panel set-img edit-img-list">
                    <div class="title-prompt">图片管理</div>
                    <!--默认图片-->
                    <div class="row" id="setimg_0">
                        <div class="col-sm-2 product-setimg-left setimg-goods-name"><span class="span_red">*</span>默认图片:<br/><span class="span_prompt">至少上传1张</span></div>
                        <div class="col-sm-10 product_setimg">
                            <div class="img-main">
                                <img onclick="fileSelect(0);" id="img-main-0" src="{{ spu['small_path'] }}"  title="默认主图,点击可重新上传">
                                <div>主图</div>
                            </div>
                            <ul class="list list-0" data-id="0">
                                {% if spuImg is defined and is_array(spuImg) %}
                                {% for v1 in spuImg %}
                                <li class="{% if v1['is_default'] == 1 %}active{% endif %}" data-id="{{ v1['id'] }}">
                                    <img src="{{ v1['sku_image'] }}" data-id="{{ v1['id'] }}">
                                    <a class="close-btn" href="javascript:;" data-id="{{ v1['id'] }}">×</a>
                                </li>
                                {% endfor %}
                                {% endif %}
                            </ul>
                            <a class="add_img_btn add-btn">
                                +
                                <input type="file" class="upload-img move_image" id="file_img_0"  name="file_img_0[]" multiple />
                            </a>
                        </div>
                        <!--<div class="explain">(说明：1.拖拽图片可对图片展示顺序进行排序；2.点击“+”号，可一次性上传多张图片；3.至少上传5张图片。)</div>-->
                    </div>
                    <!--默认图片 end -->
                    {% if skuimg is defined and is_array(skuimg) %}
                    {% for v in skuimg %}
                    <div class="row" id="setimg_{{ v['id'] }}">
                        <div class="col-sm-2 product-setimg-left setimg-goods-name"><span class="span_red">*</span>{{ v['id'] }} {{ v['rule_value_id'] }}<br/><span class="span_prompt">至少上传一张图片</span></div>
                        <div class="col-sm-10 product_setimg">
                            <div class="img-main" >
                                <img onclick="fileSelect({{ v['id'] }});" id="img-main-{{ v['id'] }}" src="{{ v['small_path'] }}"  title="商品主图,点击可上传">
                                <div>主图</div>
                            </div>
                            <ul class="list list-{{ v['id'] }}" data-id="{{ v['id'] }}">
                            {% if v['img'] is defined and is_array(v['img']) %}
                            {% for v1 in v['img'] %}
                            <li class="{% if v1['is_default'] == 1 %}active{% endif %}" data-id="{{ v1['id'] }}">
                                <img src="{{ v1['sku_image'] }}" data-id="{{ v1['id'] }}">
                                <a class="close-btn" href="javascript:;" data-id="{{ v1['id'] }}">×</a>
                            </li>
                            {% endfor %}
                            {% endif %}
                            </ul>
                            <a class="add_img_btn add-btn">
                                +
                                <input type="file" class="upload-img move_image" id="file_img_{{ v['id'] }}"  name="file_img_{{ v['id']}}[]" multiple />
                            </a>
                        </div>
                        <!--<div class="explain">(说明：1.拖拽图片可对图片展示顺序进行排序；2.点击“+”号，可一次性上传多张图片；3.至少上传5张图片。)</div>-->
                    </div>
                    {% endfor %}
                    {% endif %}
                </div>
                <!--添加商品信息  end-->

                <!--商品参数管理  start -->
                <div class="product-panel edit-attr-list">
                    <div class="product-property">
                        <a id="msg_0" class="active" href="javascript:;">默认</a>
                        {% if skuinfo is defined and is_array(skuinfo) %}
                        {% for v in skuinfo %}
                        <a id="msg_{{ v['id'] }}" href="javascript:;">{{ v['id'] }} {{ v['rule_value_id'] }}</a>
                        {% endfor %}
                        {% endif %}
                    </div>
                    <div class="title-prompt">商品参数管理</div>
                    <div >
                        <form class="form-horizontal" action="/sku/editInfo" role="form" id="save_product_attr_form" >
                            <input type="hidden" name="sku_id" id="fp_hide_id" value="0"/>
                            <!--<p >商品参数管理: </p>-->
                            <div class="div-form">
                                <div class="form-group">
                                    <label class="col-sm-2 control-label no-padding-right">别名：</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="col-xs-10 col-sm-5" name="sku_alias_name" id="fp_name" value="{% if skuDefault['sku_alias_name'] is defined %}{{ skuDefault['sku_alias_name']}}{% endif %}" />
                                    </div>
                                </div>
                                <div class="space-4"></div>
                                {% set pceShow = 'show' %}
                                {% if shopPlatform['pc'] is not defined %}
                                {% set pceShow = 'hide' %}
                                {% endif %}
                                <div class="form-group {{ pceShow }}">
                                    <label class="col-sm-2 control-label no-padding-right">商品名称PC端：</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="col-xs-10 col-sm-5" id="fp_name_pc1" name="sku_pc_name" value="{% if skuDefault['sku_pc_name'] is defined %}{{ skuDefault['sku_pc_name']}}{% endif %}" />
                                    </div>
                                </div>
                                <div class="space-4"></div>
                                <div class="form-group {{ pceShow }}">
                                    <label class="col-sm-2 control-label no-padding-right">商品副标题PC端：</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="col-xs-10 col-sm-5" id="fp_name_pc2" name="sku_pc_subheading" value="{% if skuDefault['sku_pc_subheading'] is defined %}{{ skuDefault['sku_pc_subheading']}}{% endif %}" />
                                    </div>
                                </div>
                                <div class="space-4"></div>
                                {% set mobileShow = 'show' %}
                                {% if shopPlatform['wap'] is not defined and shopPlatform['app'] is not defined and shopPlatform['wechat'] is not defined %}
                                    {% set mobileShow = 'hide' %}
                                {% endif %}
                                <div class="form-group {{ mobileShow }}">
                                    <label class="col-sm-2 control-label no-padding-right">商品名称移动端：</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="col-xs-10 col-sm-5" id="fp_name_mobile" name="sku_mobile_name" value="{% if skuDefault['sku_mobile_name'] is defined %}{{ skuDefault['sku_mobile_name']}}{% endif %}" />
                                    </div>
                                </div>
                                <div class="space-4"></div>
                                <div class="form-group  {{ mobileShow }}">
                                    <label class="col-sm-2 control-label no-padding-right">商品副标题移动端：</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="col-xs-10 col-sm-5"  id="fp_name_mobile2" name="sku_mobile_subheading" value="{% if skuDefault['sku_mobile_subheading'] is defined %}{{ skuDefault['sku_mobile_subheading']}}{% endif %}"/>
                                    </div>
                                </div>
                                <div class="space-4"></div>
                                <div class="form-group">
                                    <div class="input-box">
                                        <label class="col-sm-2 control-label no-padding-right">批准文号：</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="col-xs-10 col-sm-5" id="fp_zqwh" name="sku_batch_num" value="{% if skuDefault['sku_batch_num'] is defined %}{{ skuDefault['sku_batch_num']}}{% endif %}" />
                                        </div>
                                    </div>
                                </div>
                                <div class="space-4"></div>
                                <div class="form-group">
                                    <div class="input-box">
                                        <label class="col-sm-2 control-label no-padding-right">条形码：</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="col-xs-10 col-sm-5" id="fp_code" name="barcode" value="{% if skuDefault['barcode'] is defined %}{{ skuDefault['barcode']}}{% endif %}"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="space-4"></div>
                                <div class="form-group">
                                    <div class="input-box">
                                        <label class="col-sm-2 control-label no-padding-right">生产企业：</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="col-xs-10 col-sm-5" id="fp_company" name="manufacturer" value="{% if skuDefault['manufacturer'] is defined %}{{ skuDefault['manufacturer']}}{% endif %}" />
                                        </div>
                                    </div>
                                </div>
                                <div class="space-4"></div>
                                <div class="form-group">
                                    <div class="input-box">
                                        <label class="col-sm-2 control-label no-padding-right">规格：</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="col-xs-10 col-sm-5" id="fp_specifications" name="specifications" value="{% if skuDefault['specifications'] is defined %}{{ skuDefault['specifications']}}{% endif %}" />
                                        </div>
                                    </div>
                                </div>
                                <div class="space-4"></div>
                                <div class="form-group">
                                    <div class="space-4"></div>
                                    <div class="input-box">
                                        <label class="col-sm-2 control-label no-padding-right">重量：</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="col-xs-10 col-sm-5" id="fp_weight" name="sku_weight" value="{% if skuDefault['sku_weight'] is defined %}{{ skuDefault['sku_weight']}}{% endif %}" />
                                        </div>
                                    </div>
                                </div>
                                <div class="space-4"></div>
                                <div class="form-group">
                                    <div class="space-4"></div>
                                    <div class="input-box">
                                        <label class="col-sm-2 control-label no-padding-right">体积：</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="col-xs-10 col-sm-5" id="fp_volume" name="sku_bulk" value="{% if skuDefault['sku_bulk'] is defined %}{{ skuDefault['sku_bulk']}}{% endif %}" />
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="space-4"></div>
                                    <div class="input-box">
                                        <label class="col-sm-2 control-label no-padding-right">产品标签：</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="col-xs-2 col-sm-2" id="fp_label0" name="sku_label[]" value="{% if skuDefault['sku_label'][0] is defined %}{{ skuDefault['sku_label'][0]}}{% endif %}" />
                                            <input type="text" class="col-xs-2 col-sm-2" id="fp_label1" name="sku_label[]" value="{% if skuDefault['sku_label'][1] is defined %}{{ skuDefault['sku_label'][1]}}{% endif %}" />
                                            <input type="text" class="col-xs-2 col-sm-2" id="fp_label2" name="sku_label[]" value="{% if skuDefault['sku_label'][2] is defined %}{{ skuDefault['sku_label'][2]}}{% endif %}" />
                                        </div>
                                    </div>
                                </div>
                                <div class="space-4"></div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label no-padding-right">通用名：</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="col-xs-10 col-sm-5" name="prod_name_common" id="fp_prod_name_common" value="{% if skuDefault['prod_name_common'] is defined %}{{ skuDefault['prod_name_common']}}{% endif %}" />
                                    </div>
                                </div>
                                <div class="space-4"></div>
                                <div class="form-group">
                                    <div class="input-box">
                                        <label class="col-sm-2 control-label no-padding-right">有效期：</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="col-xs-10 col-sm-5"  id="fp_time" name="period" value="{% if skuDefault['period'] is defined %}{{ skuDefault['period']}}{% endif %}"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="space-4"></div>
                                <div class="form-group">
                                    <div class="input-box">
                                        <label class="col-sm-2 control-label no-padding-right">用法：</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="col-xs-10 col-sm-5" id="fp_sku_usage" name="sku_usage" value="{% if skuDefault['sku_usage'] is defined %}{{ skuDefault['sku_usage']}}{% endif %}" />
                                        </div>
                                    </div>
                                </div>
                                <hr/>
                                <div class="category-attr-list">
                                {% if categoryAttr is defined and is_array(categoryAttr) %}
                                {% for v in categoryAttr %}
                                <div class="form-group">
                                    <label class="col-sm-2 control-label no-padding-right">{% if v['is_null'] is 1 %}<span class="span_red">*</span>{% endif %}{{ v['attr_name'] }}：</label>
                                    <div class="col-sm-10">
                                        <select class="col-xs-5 col-sm-5 {% if v['is_null'] is 1 %}attr_is_null_required{% endif %}" name="product_other[{{ v['id'] }}]" id="fp_other_{{ v['id'] }}" >
                                            <option value="">--请选择--</option>
                                            {% if v['attr_value'] is defined and is_array(v['attr_value']) %}
                                            {% for v1 in v['attr_value'] %}
                                            <option value="{{ v1['id'] }}" {% if attrValue is defined and attrValue[v['id']] is defined and attrValue[v['id']] == v1['id']  %}selected{% endif %}>{{ v1['attr_value'] }}</option>
                                            {% endfor %}
                                            {% endif %}
                                        </select>
                                    </div>
                                </div>
                                {% endfor %}
                                {% endif %}
                                </div>
                            </div>
                            <!--<p >绑定赠品: </p>-->
                            <div class="title-prompt">绑定赠品</div>
                            <div class="div-form">
                                <div class="form-group gift-row">
                                    <label class="col-sm-1 control-label no-padding-right" >赠品名称：</label>
                                    <input type="text" class="col-sm-3" placeholder="请输入赠品名称或者赠品id"  id="gift_text" />
                                    <a href="javascript:;" id="search_gift" class="col-sm-1 glyphicon glyphicon-search"></a>
                                    <select class="col-sm-4 col-sm-offset-1" id="gift_chose">
                                        <!--<option value=""></option>-->
                                    </select>
                                    <a href="javascript:;" id="add_gift"  class="col-sm-1">添加</a>

                                </div>
                                <table id="gift_table" class=" gift-table table table-bordered " >
                                    <thead>
                                    <tr>
                                        <th class="center">赠品ID</th>
                                        <th class="center">赠品名称</th>
                                        <th class="center">数量</th>
                                        <th class="center">操作</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    {% if giftArr is defined and is_array(giftArr) %}
                                    {% for gift in giftArr %}
                                    <tr>
                                        <td class="center">
                                            <input type="text" class="gift-id" id="gift_id" name="bind_gift[]" value="{{ gift['id'] }}" readonly />
                                        </td>
                                        <td class="center"> {{ gift['goods_name'] }}  </td>
                                        <td class="center"><input type="text" name="bind_gift_num[]" class="get_gift_value" value="{{ gift['num'] }}"></td>
                                        <td class="center">
                                            <a href="javascript:;" class="delect_gift"> 删除</a>
                                        </td>
                                    </tr>
                                    {% endfor %}
                                    {% endif %}
                                    </tbody>
                                </table>
                            </div>
                            <div class="space-4"></div>

                            <div class="div-form">
                                <div class="form-group">
                                    <label class="col-sm-2 control-label no-padding-right" >meta标题：</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="col-xs-10 col-sm-5" name="meta_title" id="fp_meta_title" value="{% if skuDefault['meta_title'] is defined %}{{ skuDefault['meta_title']}}{% endif %}"  />
                                    </div>
                                </div>
                                <div class="space-4"></div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label no-padding-right" >meta关键字：</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="col-xs-10 col-sm-5" name="meta_keyword" id="fp_meta_key" value="{% if skuDefault['meta_keyword'] is defined %}{{ skuDefault['meta_keyword']}}{% endif %}" />
                                    </div>
                                </div>
                                <div class="space-4"></div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label no-padding-right" >meta描述：</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="col-xs-10 col-sm-5" name="meta_description" id="fp_meta_desc" value="{% if skuDefault['meta_description'] is defined %}{{ skuDefault['meta_description']}}{% endif %}"  />
                                    </div>
                                </div>
                                <div class="space-4"></div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label no-padding-right" >设置视频：</label>
                                    <div class="col-sm-10">
                                        <span id="video-url">{% if video['data'] is defined %}{{ video['data'][0]['video_name'] }}{% endif %}</span>
                                        <input type="hidden" class="form-product-video" name="sku_video" id="fp_video" value="{% if video['data'] is defined %}{{ video['data'][0]['id'] }}{% endif %}"  />
                                        <a href="javascript:;" class="video-upload" >选择视频</a>
                                        <a href="javascript:;" class="video-delect {% if video['data'] is not defined %}hide{% endif %}" >删除</a>
                                    </div>
                                </div>
                            </div>
                            <div class="space-4"></div>
                            <div class="col-md-offset-3">
                                <input type="hidden" name="spu_id" value="{{ spu['spu_id'] }}">
                                <button class="btn btn-info save_product_msg save_product_attr" type="button" >保存设置</button>
                            </div>
                        </form>
                    </div>
                </div>
                <!--商品参数管理  end   -->

                <!--商品说明书管理  start -->
                <div id="sku_instructions" class="{% if spu['drug_type'] is 4 %}hide{% endif %}">
                    <div class="title-prompt title-prompt1">药品说明书</div>
                    <form action="/sku/editInstruction" role="form" id="save_product_instruction_form">
                        <!--<div class="product-instruction {% if skuinfo[0] is defined  %}hide{% endif %}" style="padding:5px;text-align:center;">暂无数据……</div>-->
                        <div class="product-instruction">
                            <div class="product-instruction-nav clearfix">
                                {% if skuinfo is defined and is_array(skuinfo) %}
                                {% for k,v in skuinfo %}
                                <a id="instruction_{{ v['id'] }}" class="{% if k is 0 %}active{% endif %}" data-id="{{ v['id'] }}" href="javascript:;">{{ v['id'] }} {{ v['rule_value_id'] }}</a>
                                {% endfor %}
                                {% endif %}
                            </div>
                            <!--点击的商品id-->
                            <input name="goods-id" value="{% if skuinfo[0] is defined and skuinfo[0]['id'] > 0 %}{{ skuinfo[0]['id'] }}{% else %}0{% endif %}" id="goods_instruction_id" type="hidden">
                            <div class="chose-item clearfix sku_instruction_info">
                                <div class="form-group col-sm-5 col-lg-offset-1">
                                    <label class="col-sm-6 control-label no-padding-right">【通用名称】</label>
                                    <div class="col-sm-6">
                                        <input id="instructions_common_name" name="common_name" class="col-xs-10 col-sm-8" type="text" value="{{ instruction['common_name'] }}">
                                    </div>
                                </div>
                                <div class="form-group col-sm-5">
                                    <label class="col-sm-6 control-label no-padding-right">【老年用药】</label>
                                    <div class="col-sm-6">
                                        <input id="instructions_use_in_elderly" name="use_in_elderly" class="col-xs-10 col-sm-8" type="text" value="{{ instruction['use_in_elderly'] }}">
                                    </div>
                                </div>
                                <div class="form-group col-sm-5 col-lg-offset-1">
                                    <label class="col-sm-6 control-label no-padding-right">【英文名称】</label>
                                    <div class="col-sm-6">
                                        <input id="instructions_eng_name" name="eng_name" class="col-xs-10 col-sm-8" type="text" value="{{ instruction['eng_name'] }}">
                                    </div>
                                </div>
                                <div class="form-group col-sm-5">
                                    <label class="col-sm-6 control-label no-padding-right">【药物相互作用】</label>
                                    <div class="col-sm-6">
                                        <input id="instructions_drug_interactions" name="drug_interactions" class="col-xs-10 col-sm-8" type="text" value="{{ instruction['drug_interactions'] }}">
                                    </div>
                                </div>
                                <div class="form-group col-sm-5 col-lg-offset-1">
                                    <label class="col-sm-6 control-label no-padding-right">【商品名称】</label>
                                    <div class="col-sm-6">
                                        <input id="instructions_cn_name" name="cn_name" class="col-xs-10 col-sm-8" type="text" value="{{ instruction['cn_name'] }}">
                                    </div>
                                </div>
                                <div class="form-group col-sm-5">
                                    <label class="col-sm-6 control-label no-padding-right">【药物过量】</label>
                                    <div class="col-sm-6">
                                        <input id="instructions_overdosage" name="overdosage" class="col-xs-10 col-sm-8" type="text" value="{{ instruction['overdosage'] }}">
                                    </div>
                                </div>
                                <div class="form-group col-sm-5 col-lg-offset-1">
                                    <label class="col-sm-6 control-label no-padding-right">【成份】</label>
                                    <div class="col-sm-6">
                                        <input id="instructions_component" name="component" class="col-xs-10 col-sm-8" type="text" value="{{ instruction['component'] }}">
                                    </div>
                                </div>
                                <div class="form-group col-sm-5">
                                    <label class="col-sm-6 control-label no-padding-right">【临床试验】</label>
                                    <div class="col-sm-6">
                                        <input id="instructions_clinicalTrial" name="clinicalTrial" class="col-xs-10 col-sm-8" type="text" value="{{ instruction['clinicalTrial'] }}">
                                    </div>
                                </div>
                                <div class="form-group col-sm-5 col-lg-offset-1">
                                    <label class="col-sm-6 control-label no-padding-right">【性状】</label>
                                    <div class="col-sm-6">
                                        <input id="instructions_description" name="description" class="col-xs-10 col-sm-8" type="text" value="{{ instruction['description'] }}">
                                    </div>
                                </div>
                                <div class="form-group col-sm-5">
                                    <label class="col-sm-6 control-label no-padding-right">【药理毒理】</label>
                                    <div class="col-sm-6">
                                        <input id="instructions_mechanismAction" name="mechanismAction" class="col-xs-10 col-sm-8" type="text" value="{{ instruction['mechanismAction'] }}">
                                    </div>
                                </div>
                                <div class="form-group col-sm-5 col-lg-offset-1">
                                    <label class="col-sm-6 control-label no-padding-right">【作用类别】</label>
                                    <div class="col-sm-6">
                                        <input id="instructions_functionCategory" name="functionCategory" class="col-xs-10 col-sm-8" type="text" value="{{ instruction['functionCategory'] }}">
                                    </div>
                                </div>
                                <div class="form-group col-sm-5">
                                    <label class="col-sm-6 control-label no-padding-right">【药代动力学】</label>
                                    <div class="col-sm-6">
                                        <input id="instructions_pharmacokinetics" name="pharmacokinetics" class="col-xs-10 col-sm-8" type="text" value="{{ instruction['pharmacokinetics'] }}">
                                    </div>
                                </div>
                                <div class="form-group col-sm-5 col-lg-offset-1">
                                    <label class="col-sm-6 control-label no-padding-right">【适应症】</label>
                                    <div class="col-sm-6">
                                        <input id="instructions_indication" name="indication" class="col-xs-10 col-sm-8" type="text" value="{{ instruction['indication'] }}">
                                    </div>
                                </div>
                                <div class="form-group col-sm-5">
                                    <label class="col-sm-6 control-label no-padding-right">【贮藏】</label>
                                    <div class="col-sm-6">
                                        <input id="instructions_storage" name="storage" class="col-xs-10 col-sm-8" type="text" value="{{ instruction['storage'] }}">
                                    </div>
                                </div>
                                <div class="form-group col-sm-5 col-lg-offset-1">
                                    <label class="col-sm-6 control-label no-padding-right">【规格】</label>
                                    <div class="col-sm-6">
                                        <input id="instructions_form" name="form" class="col-xs-10 col-sm-8" type="text" value="{{ instruction['form'] }}">
                                    </div>
                                </div>
                                <div class="form-group col-sm-5">
                                    <label class="col-sm-6 control-label no-padding-right">【包装】</label>
                                    <div class="col-sm-6">
                                        <input id="instructions_pack" name="pack" class="col-xs-10 col-sm-8" type="text" value="{{ instruction['pack'] }}">
                                    </div>
                                </div>
                                <div class="form-group col-sm-5 col-lg-offset-1">
                                    <label class="col-sm-6 control-label no-padding-right">【用法用量】</label>
                                    <div class="col-sm-6">
                                        <input id="instructions_dosage" name="dosage" class="col-xs-10 col-sm-8" type="text" value="{{ instruction['dosage'] }}">
                                    </div>
                                </div>
                                <div class="form-group col-sm-5">
                                    <label class="col-sm-6 control-label no-padding-right">【有效期】</label>
                                    <div class="col-sm-6">
                                        <input id="instructions_period" name="period" class="col-xs-10 col-sm-8" type="text" value="{{ instruction['period'] }}">
                                    </div>
                                </div>
                                <div class="form-group col-sm-5 col-lg-offset-1">
                                    <label class="col-sm-6 control-label no-padding-right">【不良反应】</label>
                                    <div class="col-sm-6">
                                        <input id="instructions_adverse_reactions" name="adverse_reactions" class="col-xs-10 col-sm-8" type="text" value="{{ instruction['adverse_reactions'] }}">
                                    </div>
                                </div>
                                <div class="form-group col-sm-5">
                                    <label class="col-sm-6 control-label no-padding-right">【执行标准】</label>
                                    <div class="col-sm-6">
                                        <input id="instructions_standard" name="standard" class="col-xs-10 col-sm-8" type="text" value="{{ instruction['standard'] }}">
                                    </div>
                                </div>
                                <div class="form-group col-sm-5 col-lg-offset-1">
                                    <label class="col-sm-6 control-label no-padding-right">【禁忌】</label>
                                    <div class="col-sm-6">
                                        <input id="instructions_contraindications" name="contraindications" class="col-xs-10 col-sm-8" type="text" value="{{ instruction['contraindications'] }}">
                                    </div>
                                </div>
                                <div class="form-group col-sm-5">
                                    <label class="col-sm-6 control-label no-padding-right">【批准文号】</label>
                                    <div class="col-sm-6">
                                        <input id="instructions_approve_code" name="approve_code" class="col-xs-10 col-sm-8" type="text" value="{{ instruction['approve_code'] }}">
                                    </div>
                                </div>
                                <div class="form-group col-sm-5 col-lg-offset-1">
                                    <label class="col-sm-6 control-label no-padding-right">【注意事项】</label>
                                    <div class="col-sm-6">
                                        <input id="instructions_precautions" name="precautions" class="col-xs-10 col-sm-8" type="text" value="{{ instruction['precautions'] }}">
                                    </div>
                                </div>
                                <div class="form-group col-sm-5">
                                    <label class="col-sm-6 control-label no-padding-right">【生产企业】</label>
                                    <div class="col-sm-6">
                                        <input id="instructions_company_name" name="company_name" class="col-xs-10 col-sm-8" type="text" value="{{ instruction['company_name'] }}">
                                    </div>
                                </div>
                                <div class="form-group col-sm-5 col-lg-offset-1">
                                    <label class="col-sm-6 control-label no-padding-right">【孕妇及哺乳期妇女用药】</label>
                                    <div class="col-sm-6">
                                        <input id="instructions_use_in_pregLact" name="use_in_pregLact" class="col-xs-10 col-sm-8" type="text" value="{{ instruction['use_in_pregLact'] }}">
                                    </div>
                                </div>
                                <div class="form-group col-sm-5">
                                    <label class="col-sm-6 control-label no-padding-right">【商品编码】</label>
                                    <div class="col-sm-6">
                                        <input id="instructions_commodity_code" name="commodity_code" class="col-xs-10 col-sm-8" type="text" value="{{ instruction['commodity_code'] }}">
                                    </div>
                                </div>
                                <div class="form-group col-sm-5 col-lg-offset-1">
                                    <label class="col-sm-6 control-label no-padding-right">【儿童用药】</label>
                                    <div class="col-sm-6">
                                        <input id="instructions_use_in_children" name="use_in_children" class="col-xs-10 col-sm-8" type="text" value="{{ instruction['use_in_children'] }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-offset-3 btn-border">
                                <button class="btn btn-info save_product_instruction" type="button">
                                    保存设置
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <!--商品说明书管理  end -->

                <!--选择模板图片  start -->
                <div class="title-prompt title-prompt1">详情管理</div>
                <div class="product-panel edit-info-list">
                    <div class="goods-port">
                        <a id="pc" class="active {{ pceShow }}" href="javascript:;">PC</a>
                        <a id="mobile" class="{% if pceShow is defined and pceShow == 'hide'  %}active{% endif %} {{ mobileShow }}" href="javascript:;">移动端</a>
                    </div>
                        <div class="goods-nav">
                            <a id="model_0" class="active" href="javascript:;">默认</a>
                            {% if skuinfo is defined and is_array(skuinfo) %}
                            {% for v in skuinfo %}
                            <a id="model_{{ v['id'] }}" href="javascript:;">{{ v['id'] }} {{ v['rule_value_id'] }}</a>
                            {% endfor %}
                            {% endif %}
                        </div>
                        <form  class="form-horizontal" action="/sku/editModel" role="form" id="save_product_model_form">
                            <!--判断点击的是pc 还是移动端-->
                            <input type="hidden" name="is-pc" value="{% if pceShow is defined and pceShow == 'hide'  %}mobile{% else %}pc{% endif %}" id="is_port" />
                            <!--点击的商品id-->
                            <input type="hidden" name="goods-id" value="0" id="goods_model_id" />

                            <!-- 广告模板-->
                            <!--<div class="ad-model" id="ad_model_s">-->
                                <!--<div class="ad-list" id="ad_list" >-->
                                <!--{% if ad is defined and is_array(ad) %}-->
                                <!--{% for v in ad %}-->
                                    <!--<div id="adt_{{ v['id'] }}"> <input name="ad[]" value="{{ v['id'] }}" type="hidden"><div class="ad-list-title">模板名称：<span>{{ v['ad_name'] }}</span><a href="/sku/adedit?id={{ v['id'] }}&isLimit=1" target="_blank">修改</a><a href="javascript:;" class="delect-model">删除</a> </div><div class="ad-list-img"><img src="assets/images/gallery/image-1.jpg" alt=""><img src="assets/images/gallery/image-1.jpg" alt=""><img src="assets/images/gallery/image-1.jpg" alt=""></div></div>-->
                                <!--{% endfor %}-->
                                <!--{% endif %}-->
                                <!--</div>-->
                                <!--<a href="javascript:;" class="add-model-btn">+添加顶部广告模板</a>-->
                            <!--</div>-->
                            <!--<br/>-->
                            <!--<hr/>-->
                            <!-- 底部广告模板-->
                            <!--<div class="ad-model" id="ad_model_x">-->
                                <!--<div class="ad-list" id="btm_ad_model" >-->
                                <!--{% if btomAd is defined and is_array(btomAd) %}-->
                                <!--{% for v in btomAd %}-->
                                <!--<div class="ad-list">-->
                                    <!--<div id="adt_{{ v['id'] }}"> <input name="btom-ad[]" value="{{ v['id'] }}" type="hidden"><div class="ad-list-title">模板名称：<span>{{ v['ad_name'] }}</span><a href="/sku/adedit?id={{ v['id'] }}&isLimit=1" target="_blank">修改</a><a href="javascript:;" class="delect-model">删除</a> </div><div class="ad-list-img"><img src="assets/images/gallery/image-1.jpg" alt=""><img src="assets/images/gallery/image-1.jpg" alt=""><img src="assets/images/gallery/image-1.jpg" alt=""></div></div></div>-->
                                <!--{% endfor %}-->
                                <!--{% endif %}-->
                                <!--</div>-->
                                <!--<a href="javascript:;" class="btom-model-btn">+添加底部广告模板</a>-->
                            <!--</div>-->
                            <!--<br/>-->
                            <!--<hr/>-->

                            <!--商品详情  药品说明-->
                            <div class="edit-info-s">
                            <div class="chose-product-desc single">
                                <a href="javascript:;">商品详情</a>
                            </div>
                            <div class="chose-item">
                                <textarea id="skuinfo_desc" name="content">{% if skuDefault['sku_detail_pc'] is defined %}{{ skuDefault['sku_detail_pc'] }}{% endif %}</textarea>
                            </div>
                            </div>
                            <div class="col-md-offset-3">
                                <input type="hidden" name="spu_id" value="{{ spu['spu_id'] }}">
                                <button class="btn btn-info save_product_msg save_product_info_ad" type="button">保存设置</button>
                            </div>
                        </form>
                </div>
                <!--选择模板图片  end   -->

                <!--上下架  start -->
                <div class="product-panel edit-other-list">
                    <!--<div class="title-prompt">其他管理</div>-->
                    <form  action="/sku/editTiming" role="form" id="save_product_time_form" >
                        <div class="edit-other-x">
                            <div class="chose-operation">请选择你的操作：
                                <label>
                                    <input name="goods-shelves" checked type="radio" class="ace product_time_form_is_on_sale" value="1" >
                                    <span class="lbl">立即上下架</span>
                                </label>
                                <label>
                                    <input name="goods-shelves" type="radio" class="ace up_down product_time_form_is_on_sale" value="2" >
                                    <span class="lbl">定时上下架</span>
                                </label>
                                <div class="chose-goods-shelves chose-goods-shelves1" id="product_time_is_on_sale">
                                    {% if skuinfo is defined and is_array(skuinfo) %}
                                    {% for v in skuinfo %}
                                    <div id="goods_check_1_{{ v['id'] }}" class="checkbox-list">
                                        <label>
                                            <span class="lbl">{{ v['id'] }} {{ v['rule_value_id'] }}</span>
                                            <select class="select-sale-pc-{{ v['id'] }} {% if shopPlatform['pc'] is not defined %}hide{% endif %}" name="is_on_sale[]">
                                                <option value="1" {% if v['is_on_sale'] is defined and v['is_on_sale'] == 1 %}selected{% endif %}>pc 上架</option>
                                                <option value="0" {% if v['is_on_sale'] is defined and v['is_on_sale'] != 1 %}selected{% endif %}>pc 下架</option>
                                            </select>
                                            <select class="select-sale-app-{{ v['id'] }} {% if shopPlatform['app'] is not defined %}hide{% endif %}" name="sale_timing_app[]">
                                                <option value="1" {% if v['sale_timing_app'] is defined and v['sale_timing_app'] == 1 %}selected{% endif %}>app 上架</option>
                                                <option value="0" {% if v['sale_timing_app'] is defined and v['sale_timing_app'] != 1 %}selected{% endif %}>app 下架</option>
                                            </select>
                                            <select class="select-sale-wap-{{ v['id'] }} {% if shopPlatform['wap'] is not defined %}hide{% endif %}" name="sale_timing_wap[]">
                                                <option value="1" {% if v['sale_timing_wap'] is defined and v['sale_timing_wap'] == 1 %}selected{% endif %}>wap 上架</option>
                                                <option value="0" {% if v['sale_timing_wap'] is defined and v['sale_timing_wap'] != 1 %}selected{% endif %}>wap 下架</option>
                                            </select>
                                            <select class="select-sale-wechat-{{ v['id'] }} {% if shopPlatform['wechat'] is not defined %}hide{% endif %}" name="sale_timing_wechat[]">
                                                <option value="1" {% if v['sale_timing_wechat'] is defined and v['sale_timing_wechat'] == 1 %}selected{% endif %}>微商城 上架</option>
                                                <option value="0" {% if v['sale_timing_wechat'] is defined and v['sale_timing_wechat'] != 1 %}selected{% endif %}>微商城 下架</option>
                                            </select>
                                        </label>
                                    </div>
                                    <input type="hidden" name="sku_id[]" value="{{ v['id'] }}">
                                    {% endfor %}
                                    {% endif %}
                                </div>
                            </div>
                            <div class="time-shelves hide">
                                <!--<div class="row">-->
                                    <!--<label class="col-sm-2 control-label" >定时上架</label>-->
                                    <!--<div class="col-sm-4 pr">-->
                                        <!--<input placeholder="请选择上架时间" name="time_start" class="laydate-icon time_start" value="" onclick="laydate({istime: true, format: 'YYYY-MM-DD hh:mm:ss',laydate})">-->
                                    <!--</div>-->
                                    <!--<div class="col-sm-6">系统会在该时间自动进行上架操作。</div>-->
                                <!--</div>-->
                                <!--<div class="row mt_10">-->
                                    <!--<label class="col-sm-2 control-label " >定时下架</label>-->
                                    <!--<div class="col-sm-4">-->
                                        <!--<input placeholder="请选择下架时间" name="time_end" class="laydate-icon time_end" value="" onclick="laydate({istime: true, format: 'YYYY-MM-DD hh:mm:ss'})">-->
                                    <!--</div>-->
                                    <!--<div class="col-sm-6">系统会在该时间自动进行下架操作。</div>-->
                                <!--</div>-->
                                <div class="setAllTime">
                                    选择设置方式：<select class="setTimeEnd" name="setTimeEnd">
                                                        <option {% if setTime is defined and setTime['allTime'] != 2 %}selected{% endif %} value="1">所有端</option>
                                                        <option {% if setTime is defined and setTime['allTime'] == 2 %}selected{% endif %} value="2">各个端</option>
                                                 </select>
                                </div>
                                {% if skuinfo is defined and is_array(skuinfo) %}
                                {% for v in skuinfo %}
                                <div id="time_goods_check_{{ v['id'] }}" class="checkbox-list-time">
                                    <div class="show_sku_information">
                                        <span class="lbl">{{ v['id'] }} {{ v['rule_value_id'] }}</span>
                                        <div class="current_left">
                                            <!--当前上下架状态：-->
                                            <span class="allTime">
                                                所有端
                                                <em class="span_red">
                                                    ({% if shopPlatform['pc'] is defined %}PC端{% if v['is_on_sale'] is defined and v['is_on_sale'] == 1 %}已上架{% else %}未上架{% endif %}{% endif %},
                                                    {% if shopPlatform['app'] is defined %}APP端{% if v['sale_timing_app'] is defined and v['sale_timing_app'] == 1 %}已上架{% else %}未上架{% endif %}{% endif %},
                                                    {% if shopPlatform['wap'] is defined %}WAP端{% if v['sale_timing_wap'] is defined and v['sale_timing_wap'] == 1 %}已上架{% else %}未上架{% endif %}{% endif %},
                                                    {% if shopPlatform['wechat'] is defined %}微商城{% if v['sale_timing_wechat'] is defined and v['sale_timing_wechat'] == 1 %}已上架{% else %}未上架{% endif %}{% endif %})
                                                </em>
                                            </span>
                                            {% if shopPlatform['pc'] is defined %}
                                                <span class="eachTime">
                                                    PC端
                                                    <em class="span_red">
                                                        ({% if v['is_on_sale'] is defined and v['is_on_sale'] == 1 %}已上架{% else %}未上架{% endif %})
                                                    </em>
                                                </span>
                                            {% endif %}
                                            {% if shopPlatform['app'] is defined %}
                                                <span class="eachTime">
                                                    APP端
                                                    <em class="span_red">
                                                        ({% if v['sale_timing_app'] is defined and v['sale_timing_app'] == 1 %}已上架{% else %}未上架{% endif %})
                                                    </em>
                                                </span>
                                            {% endif %}
                                            {% if shopPlatform['wap'] is defined %}
                                                <span class="eachTime">
                                                    WAP端
                                                    <em class="span_red">
                                                        ({% if v['sale_timing_wap'] is defined and v['sale_timing_wap'] == 1 %}已上架{% else %}未上架{% endif %})
                                                    </em>
                                                </span>
                                            {% endif %}
                                            {% if shopPlatform['wechat'] is defined %}
                                                <span class="eachTime">
                                                    微商城
                                                    <em class="span_red">({% if v['sale_timing_wechat'] is defined and v['sale_timing_wechat'] == 1 %}已上架{% else %}未上架{% endif %})
                                                    </em>
                                                </span>
                                            {% endif %}
                                        </div>
                                    </div>
                                    <!--<input name="sku_id_time[]" type="hidden" value="{{ v['id'] }}">-->
                                    <div class="allTime">
                                        <div class="row">
                                        <label class="col-sm-2 control-label" >定时上架</label>
                                        <div class="col-sm-4 pr">
                                        <input placeholder="请选择上架时间" name="time_start[]" class="laydate-icon time_start" value="" onclick="laydate({istime: true, format: 'YYYY-MM-DD hh:mm:ss',laydate})">
                                        </div>
                                        </div>
                                        <div class="row mt_10">
                                        <label class="col-sm-2 control-label " >定时下架</label>
                                        <div class="col-sm-4">
                                        <input placeholder="请选择下架时间" name="time_end[]" class="laydate-icon time_end" value="" onclick="laydate({istime: true, format: 'YYYY-MM-DD hh:mm:ss'})">
                                        </div>
                                        </div>
                                    </div>
                                    <div class="eachTime">
                                        <div class="row">
                                            <label class="col-sm-2 control-label" >定时上架</label>
                                            {% if shopPlatform is defined and shopPlatform is not empty %}
                                                {% for lowerPlatform,platform in shopPlatform %}
                                                    <div class="col-sm-4 pr">
                                                        <input placeholder="请选择上架时间" name="time_start_{{ lowerPlatform }}[]" class="laydate-icon time_start_{{ lowerPlatform }}" value="" onclick="laydate({istime: true, format: 'YYYY-MM-DD hh:mm:ss',laydate})">
                                                    </div>
                                                {% endfor %}
                                            {% endif %}
                                            <!--<div class="col-sm-4 pr">
                                                <input placeholder="请选择上架时间" name="time_start_pc[]" class="laydate-icon time_start_pc" value="" onclick="laydate({istime: true, format: 'YYYY-MM-DD hh:mm:ss',laydate})">
                                            </div>
                                            <div class="col-sm-4 pr">
                                                <input placeholder="请选择上架时间" name="time_start_app[]" class="laydate-icon time_start_app" value="" onclick="laydate({istime: true, format: 'YYYY-MM-DD hh:mm:ss',laydate})">
                                            </div>
                                            <div class="col-sm-4 pr">
                                                <input placeholder="请选择上架时间" name="time_start_wap[]" class="laydate-icon time_start_wap" value="" onclick="laydate({istime: true, format: 'YYYY-MM-DD hh:mm:ss',laydate})">
                                            </div>
                                            <div class="col-sm-4 pr">
                                                <input placeholder="请选择上架时间" name="time_start_wechat[]" class="laydate-icon time_start_wechat" value="" onclick="laydate({istime: true, format: 'YYYY-MM-DD hh:mm:ss',laydate})">
                                            </div>-->
                                        </div>
                                        <div class="row mt_10">
                                            <label class="col-sm-2 control-label " >定时下架</label>
                                            {% if shopPlatform is defined and shopPlatform is not empty %}
                                                {% for lowerPlatform,platform in shopPlatform %}
                                                    <div class="col-sm-4">
                                                        <input placeholder="请选择下架时间" name="time_end_{{ lowerPlatform }}[]" class="laydate-icon time_end_{{ lowerPlatform }}" value="" onclick="laydate({istime: true, format: 'YYYY-MM-DD hh:mm:ss'})">
                                                    </div>
                                                {% endfor %}
                                            {% endif %}
                                            <!--<div class="col-sm-4">
                                                <input placeholder="请选择下架时间" name="time_end_pc[]" class="laydate-icon time_end_pc" value="" onclick="laydate({istime: true, format: 'YYYY-MM-DD hh:mm:ss'})">
                                            </div>
                                            <div class="col-sm-4">
                                                <input placeholder="请选择下架时间" name="time_end_app[]" class="laydate-icon time_end_app" value="" onclick="laydate({istime: true, format: 'YYYY-MM-DD hh:mm:ss'})">
                                            </div>
                                            <div class="col-sm-4">
                                                <input placeholder="请选择下架时间" name="time_end_wap[]" class="laydate-icon time_end_wap" value="" onclick="laydate({istime: true, format: 'YYYY-MM-DD hh:mm:ss'})">
                                            </div>
                                            <div class="col-sm-4">
                                                <input placeholder="请选择下架时间" name="time_end_wechat[]" class="laydate-icon time_end_wechat" value="" onclick="laydate({istime: true, format: 'YYYY-MM-DD hh:mm:ss'})">
                                            </div>-->
                                        </div>
                                    </div>
                                </div>
                                {% endfor %}
                                {% endif %}
                            </div>
                        </div>

                        <div class="chose-operation">推荐商品：
                            <div class="chose-goods-shelves chose-goods-shelves2">
                                {% if skuinfo is defined and is_array(skuinfo) %}
                                {% for v in skuinfo %}
                                <div id="goods_check_2_{{ v['id'] }}" class="checkbox-list">
                                    <label>
                                        <input name="goods_recommend[]" {% if v['is_recommend'] == 1 %}checked{% endif %} type="checkbox" class="ace" value="{{ v['id'] }}">
                                        <span class="lbl">{{ v['id'] }} {{ v['rule_value_id'] }}</span>
                                    </label>
                                </div>
                                {% endfor %}
                                {% endif %}
                            </div>
                        </div>
                        <div class="chose-operation">热门商品：
                            <div class="chose-goods-shelves chose-goods-shelves3">
                                {% if skuinfo is defined and is_array(skuinfo) %}
                                {% for v in skuinfo %}
                                <div id="goods_check_3_{{ v['id'] }}" class="checkbox-list">
                                    <label>
                                        <input name="goods_hot[]" {% if v['is_hot'] == 1 %}checked{% endif %} type="checkbox" class="ace" value="{{ v['id'] }}">
                                        <span class="lbl">{{ v['id'] }} {{ v['rule_value_id'] }}</span>
                                    </label>
                                </div>
                                {% endfor %}
                                {% endif %}
                            </div>
                        </div>
                        <!--<div class="chose-operation">选择运费模板：-->
                            <!--<div class="chose-goods-shelves">-->
                                <!--<select name="freight">-->
                                    <!--<option value="0">&#45;&#45;选择运费模板&#45;&#45;</option>-->
                                    <!--{% if freight is defined and is_array(freight) %}-->
                                    <!--{% for v in freight %}-->
                                    <!--<option {% if sku is defined and sku[0] is defined and sku[0]['pc_freight_temp_id'] == v['id']  %}selected{% endif %} value="{{ v['id'] }}">{{ v['template_name'] }}</option>-->
                                    <!--{% endfor %}-->
                                    <!--{% endif %}-->
                                <!--</select>-->
                            <!--</div>-->
                        <!--</div>-->

                        <div class="edit-other-x">
                            <div class="chose-operation">选择运费模板：
                                <div class="chose-goods-shelves chose-goods-shelves1">
                                    {% if skuinfo is defined and is_array(skuinfo) %}
                                    {% for v in skuinfo %}
                                    <div id="freight_{{ v['id'] }}" class="checkbox-list">
                                        <label>
                                            <span class="lbl">{{ v['id'] }} {{ v['rule_value_id'] }}</span>
                                            <select name="freight[]">
                                                <option value="0">--选择运费模板--</option>
                                                {% if freight is defined and is_array(freight) %}
                                                {% for vs in freight %}
                                                <option {% if v is defined and v['pc_freight_temp_id'] == vs['id']  %}selected{% endif %} value="{{ vs['id'] }}">{{ vs['template_name'] }}</option>
                                                {% endfor %}
                                                {% endif %}
                                            </select>
                                        </label>
                                    </div>
                                    {% endfor %}
                                    {% endif %}
                                </div>
                            </div>
                        </div>

                        <div class="edit-other-x">
                            <div class="chose-operation">退换货设置：
                                <div class="chose-goods-shelves chose-goods-shelves1">
                                    {% if skuinfo is defined and is_array(skuinfo) %}
                                    {% for v in skuinfo %}
                                    <div id="returned_goods_check_{{ v['id'] }}" class="checkbox-list">
                                        <label>
                                            <span class="lbl">{{ v['id'] }} {{ v['rule_value_id'] }}</span>
                                            <select class="returned_goods returned_goods_{{ v['id'] }}" name="returned_goods_act[]">
                                                <option value="0" {% if v['info']['returned_goods_time'] is defined and v['info']['returned_goods_time'] == 0 %}selected{% endif %}>不支持</option>
                                                <option value="1" {% if v['info']['returned_goods_time'] is defined and v['info']['returned_goods_time'] != 0 %}selected{% endif %}>支持</option>
                                            </select>
                                                <span class="returned_goods_value {% if v['info']['returned_goods_time'] is not defined or v['info']['returned_goods_time'] == 0 %}hide{% endif %}">
                                                退货时间：<select name="returned_goods_value[]">
                                                    <option {% if v['info']['returned_goods_time'] is defined and v['info']['returned_goods_time'] == 7 %}selected{% endif %} value="7">7 天</option>
                                                    <option {% if v['info']['returned_goods_time'] is defined and v['info']['returned_goods_time'] == 15 %}selected{% endif %} value="15">15 天</option>
                                                    <option {% if v['info']['returned_goods_time'] is defined and v['info']['returned_goods_time'] == 20 %}selected{% endif %} value="20">20 天</option>
                                                </select>
                                                </span>
                                        </label>
                                    </div>
                                    {% endfor %}
                                    {% endif %}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-offset-3">
                        <input name="spu_id" type="hidden" value="{{ spu['spu_id'] }}">
                        <button class="btn btn-info save-goods-shelves" type="button" id="save_goods_time_shelves">保存设置</button>
                        </div>
                    </form>
                </div>
                <!--上下架  end   -->

            </div>


            <!--       新建商品&#45;&#45;多品规  end                -->
<!--弹窗   库存管理 -->
<div class="popup hide" id="popup_stock">
    <div class="popup-content">
        <div class="title">库存管理 <a id="close_popup_stock" href="javascript:;">&times;</a></div>
        <div class="radio stock-radio">
            <label>
                <input name="stock-radio" type="radio" class="ace" checked="checked" value="real_stock" >
                <span class="lbl">真实库存:<span id="popup_real_stock"></span>件</span>
            </label>
        </div>
        <div class="radio stock-radio">
            <label>
                <input name="stock-radio" type="radio" class="ace" value="fictitious"  >
                <span class="lbl">虚拟库存</span>
            </label>
            <br/>
            <div class="checkbox stock-checkbox">
                <label>
                    <input name="stock-checkbox" type="checkbox" class="ace" value="0" >
                    <span class="lbl">虚拟公共库存</span>
                </label>
                <input type="text" name="stock" class="stock-price" value="" />件
            </div>
	    {% if shopPlatform is defined and shopPlatform is not empty %}
                {% for lowerPlatform,platform in shopPlatform %}
		    <div class="checkbox stock-checkbox">
	                <label>
	                    <input name="stock-checkbox" type="checkbox" class="ace"  value="{% if lowerPlatform == 'pc' %}1{% elseif lowerPlatform == 'app' %}2{% elseif lowerPlatform == 'wechat' %}4{% else %}3{% endif %}">
	                    <span class="lbl">{{ platform }}虚拟公存</span>
	                </label>
	                <input type="text" name="stock" class="stock-price" value="" />件
	            </div>
                {% endfor %}
            {% endif %}
            <!--<div class="checkbox stock-checkbox">
                <label>
                    <input name="stock-checkbox" type="checkbox" class="ace"  value="1">
                    <span class="lbl">PC虚拟公存</span>
                </label>
                <input type="text" name="stock" class="stock-price" value="" />件
            </div>
            <div class="checkbox stock-checkbox">
                <label>
                    <input name="stock-checkbox" type="checkbox" class="ace" value="2" >
                    <span class="lbl">APP虚拟公存</span>
                </label>
                <input type="text" name="stock" class="stock-price" value="" />件
            </div>
            <div class="checkbox stock-checkbox">
                <label>
                    <input name="stock-checkbox" type="checkbox" class="ace" value="3" >
                    <span class="lbl">WAP虚拟公存</span>
                </label>
                <input type="text" name="stock" class="stock-price" value="" />件
            </div>
            <div class="checkbox stock-checkbox">
                <label>
                    <input name="stock-checkbox" type="checkbox" class="ace" value="3" >
                    <span class="lbl">微商城虚拟公存</span>
                </label>
                <input type="text" name="stock" class="stock-price" value="" />件
            </div>-->
        </div>
        <button	 id="save_stock" >保存</button>
    </div>
</div>
<!--商品id   弹窗  -->
<div class="popup hide" id="popup_add_product">
    <div class="popup-content" style="height: 200px;">
        <div class="pro-title"> <a id="close_add_product" href="javascript:;">&times;</a>添加商品</div>
        <div style="margin-left: 40px">
       <label style="float:left;"> 商&nbsp;品&nbsp;ID&nbsp;:&nbsp;<input type="text" name="product" placeholder="请输入商品id" id="product_id" /><font style="color: #ff3c41;">&nbsp;非必填,可系统生成</font></label><br/>
        <label style="float:left;color: red;">*ERP编码:&nbsp;<input type="text" name="product_code" placeholder="请输入ERP编码" id="product_code" />&nbsp;&nbsp;<button class="btn btn-primary" id="add_product_sure" >确定</button></label>
        <!--<br/>-->
        <!--<br/>-->
        <!--<button id="add_product_sure" >确定</button>-->
        </div>
    </div>
</div>
<!--视频选择 弹窗  -->
<div class="popup  hide" id="popup_video">
    <div class="popup-content2 " id="popup_video_list">


    </div>
</div>

<!--广告模板 弹窗  -->
<div class="popup hide" id="popup_model">
    <div class="popup-content2 model-content">
        <div class="title">请选择广告模板<a id="close_popup_model" href="javascript:;">&times;</a></div>
        <div class="row model-title">
            <div class="col-sm-12">模板名称</div>
            <!--<div class="col-sm-9">预览图</div>-->
        </div>
        <button class="btn btn-info chose-model-btn " type="button" id="chose_model_sure">确定</button>

    </div>
</div>

<!--默认图片选择-->
<form id="form_face_img" action="/sku/setMainImg" enctype="multipart/form-data" style="width:auto;">
    <input type="file" name="fileToUpload" id="fileToUpload" onchange="fileSelected();" style="display:none;">
    <input type="hidden" name="id" value="0">
    <input type="hidden" name="spu_id" value="{{ spu['spu_id'] }}">
</form>


{% endblock %}

{% block footer %}

<!-- basic scripts -->

<!--<script src="http://{{ config.domain.static }}/assets/js/bootstrap.min.js"></script>-->

<!-- page specific plugin scripts -->
<script src="http://{{ config.domain.static }}/assets/js/jquery-ui.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/jquery.ui.touch-punch.min.js"></script>



<script src="http://{{ config.domain.static }}/assets/js/jquery-ui.custom.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/chosen.jquery.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/fuelux.spinner.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/bootstrap-datepicker.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/bootstrap-timepicker.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/moment.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/daterangepicker.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/bootstrap-datetimepicker.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/bootstrap-colorpicker.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/jquery.knob.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/jquery.autosize.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/jquery.inputlimiter.1.3.1.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/jquery.maskedinput.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/bootstrap-tag.min.js"></script>

<!-- page specific plugin scripts -->
<script src="http://{{ config.domain.static }}/assets/js/jquery.dataTables.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/jquery.dataTables.bootstrap.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/dataTables.tableTools.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/dataTables.colVis.min.js"></script>


<!-- ace scripts -->
<script src="http://{{ config.domain.static }}/assets/js/ace-elements.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/ace.min.js"></script>
<!--<script src="http://{{ config.domain.static }}/assets/js/ajaxfileupload.js"></script>-->
<script src="http://{{ config.domain.static }}/assets/js/jquery.dragsort-0.5.2.js"></script>
<script src="/js/kindeditor/kindeditor-min.js"></script>
<script src="/js/kindeditor/lang/zh_CN.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/ajaxfileupload.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/product.js?<?php echo time();?>"></script>
<script src="http://{{ config.domain.static }}/assets/admin/js/spu/laydate/laydate.js"></script>

<script>
    jQuery(function($) {

        if(!ace.vars['touch']) {
            $('.chosen-select').chosen({allow_single_deselect:true});
            //resize the chosen on window resize

            $(window)
                    .off('resize.chosen')
                    .on('resize.chosen', function() {
                        $('.chosen-select').each(function() {
                            var $this = $(this);
                            $this.next().css({'width': $this.parent().width()});
                        })
                    }).trigger('resize.chosen');
            //resize chosen on sidebar collapse/expand
            $(document).on('settings.ace.chosen', function(e, event_name, event_val) {
                if(event_name != 'sidebar_collapsed') return;
                $('.chosen-select').each(function() {
                    var $this = $(this);
                    $this.next().css({'width': $this.parent().width()});
                })
            });


            $('#chosen-multiple-style .btn').on('click', function(e){
                var target = $(this).find('input[type=radio]');
                var which = parseInt(target.val());
                if(which == 2) $('#form-field-select-4').addClass('tag-input-style');
                else $('#form-field-select-4').removeClass('tag-input-style');
            });
        }

        //jquery tabs
        $( "#tabs" ).tabs();

        $( "#video-tabs" ).tabs();

        $( "#model-tabs" ).tabs();
        $( "#model-pc-tabs" ).tabs();
        $( "#model-mobile-tabs" ).tabs();
    });
</script>

{% endblock %}