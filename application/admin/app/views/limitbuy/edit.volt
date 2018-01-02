{% extends "layout.volt" %}

{% block content %}
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/jquery.datetimepicker.css" class="ace-main-stylesheet" />
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/admin/css/coupon_addon.css" class="ace-main-stylesheet" />
<style>
    #categoryBox{
        top:0;left:0;
    }
</style>
<div class="main-container" id="main-container">
    <div class="main-content">
        <div class="main-content-inner">
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        编辑限购活动
                    </h1>
                </div><!-- /.page-header -->
            </div>
            <form id="addLimitPromotionForm"  class="form-horizontal" action="/limitbuy/edit" method="post" onsubmit="ajaxSubmit(addLimitPromotionForm);return false;">
                <div class="">
                    <h3>
                        基本信息
                    </h3>
                </div><!-- /.basic -->
                <div class="row">
                    <div class="col-xs-12">
                        <!-- PAGE CONTENT BEGINS -->
                        <div>
                            <input id="promotion_type" type="hidden" name="promotion_type" value="30" />
                            <input type="hidden" name="promotion_id" value="{% if limitPromotionDetail['promotion_id'] is defined  %}{{ limitPromotionDetail['promotion_id'] }}{% endif %}"/>
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_title"> <span  class="text-red">*</span>活动名称 </label>

                                <div class="col-sm-9">
                                    <input type="text" id="promotion_title" name="promotion_title" class="col-xs-10 col-sm-5" placeholder="不可为空" value="{% if limitPromotionDetail['promotion_title'] is defined  %}{{ limitPromotionDetail['promotion_title'] }}{% endif %}"/>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> <span  class="text-red">*</span>活动开始时间 </label>

                                <div class="col-sm-9">
                                    <input type="text" id="start_time" name="promotion_start_time" class="col-xs-10 col-sm-5" placeholder="不可为空" value="{% if limitPromotionDetail['promotion_start_time'] is defined  %}{{ limitPromotionDetail['promotion_start_time'] }}{% endif %}"/>
                                </div>
                            </div>

                            <div class="space-4"></div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> <span  class="text-red">*</span>活动结束时间 </label>

                                <div class="col-sm-9">
                                    <input type="text" id="end_time" name="promotion_end_time" class="col-xs-10 col-sm-5" placeholder="不可为空" value="{% if limitPromotionDetail['promotion_end_time'] is defined  %}{{ limitPromotionDetail['promotion_end_time'] }}{% endif %}"/>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> <span  class="text-red">*</span>活动平台 </label>
                                <div class="checkbox promotion_platform">
                                    <label>
                                        <input name="promotion_platform_pc" type="checkbox" class="ace" {% if limitPromotionDetail['promotion_platform_pc'] is defined %}value="{{ limitPromotionDetail['promotion_platform_pc'] }}" {% if limitPromotionDetail['promotion_platform_pc'] == 1 %}checked{% endif %}{% endif %}>
                                        <span class="lbl">&nbsp;PC</span>
                                    </label>
                                    <label>
                                        <input name="promotion_platform_app" type="checkbox" class="ace" {% if limitPromotionDetail['promotion_platform_app'] is defined %}value="{{ limitPromotionDetail['promotion_platform_app'] }}" {% if limitPromotionDetail['promotion_platform_app'] == 1 %}checked{% endif %}{% endif %}>
                                        <span class="lbl">&nbsp;APP</span>
                                    </label>
                                    <label>
                                        <input name="promotion_platform_wap" type="checkbox" class="ace" {% if limitPromotionDetail['promotion_platform_wap'] is defined %}value="{{ limitPromotionDetail['promotion_platform_wap'] }}" {% if limitPromotionDetail['promotion_platform_wap'] == 1 %}checked{% endif %}{% endif %}>
                                        <span class="lbl">&nbsp;WAP</span>
                                    </label>
                                    <label style="display: none">
                                        <input name="promotion_platform_wechat" type="checkbox" class="ace" {% if limitPromotionDetail['promotion_platform_wechat'] is defined %}value="{{ limitPromotionDetail['promotion_platform_wechat'] }}" {% if limitPromotionDetail['promotion_platform_wechat'] == 1 %}checked{% endif %}{% endif %}>
                                        <span class="lbl">&nbsp;微商城</span>
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_content"> <span  class="text-red">*</span>活动说明 </label>
                                <div class="col-sm-9">
                                    <textarea name="promotion_content" class="col-xs-10 col-sm-5" id="promotion_content" placeholder="不可为空">{% if limitPromotionDetail['promotion_content'] is defined  %}{{ limitPromotionDetail['promotion_content'] }}{% endif %}</textarea>

                                </div>
                            </div>

                            <div class="">
                                <h3>
                                    条件信息
                                </h3>
                            </div><!-- /.condition -->

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> <span  class="text-red">*</span>会员等级 </label>
                                <div class="col-xs-12 col-sm-9">
                                    <select id="promotion_member_level" name="promotion_member_level">
                                        {% if limitEnum['memberLevel'] is defined %}
                                        {% for k,v in limitEnum['memberLevel'] %}
                                        <option value="{{k}}" {% if limitPromotionDetail['promotion_member_level'] is defined and limitPromotionDetail['promotion_member_level'] == k %}selected{% endif %}>{{v}}</option>
                                        {% endfor %}
                                        {% endif %}
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_for_users"> <span  class="text-red">*</span>适用人群 </label>
                                <div class="col-xs-12 col-sm-9">
                                    <select id="promotion_for_users" name="promotion_for_users">
                                        {% if limitEnum['forPeople'] is defined %}
                                        {% for k,v in limitEnum['forPeople'] %}
                                        <option value="{{k}}" {% if limitPromotionDetail['promotion_for_users'] is defined and limitPromotionDetail['promotion_for_users'] == k %}selected{% endif %}>{{v}}</option>
                                        {% endfor %}
                                        {% endif %}
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="member_tag"> 会员标签 </label>
                                <div class="col-xs-12 col-sm-9">
                                    <select id="member_tag" name="member_tag">
                                        <option value="0" {% if limitPromotionDetail['member_tag'] is defined and limitPromotionDetail['member_tag'] == 0 %}selected{% endif %}>不指定</option>
                                        {% if memberTag is defined and memberTag is not empty%}
                                        {% for k,v in memberTag %}
                                        <option value="{{v['tag_id']}}" {% if limitPromotionDetail['member_tag'] is defined and limitPromotionDetail['member_tag'] == v['tag_id'] %}selected{% endif %}>{{v['tag_name']}}</option>
                                        {% endfor %}
                                        {% endif %}
                                    </select>
                                </div>
                            </div>
                            <!-- 适用范围start -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_scope"> <span  class="text-red">*</span>适用范围 </label>
                                <div class="col-xs-12 col-sm-9">
                                    <select name="promotion_scope" id="promotion_scope">
                                        <option value="0">请选择</option>
                                        {% if limitEnum['limitBuyForScope'] is defined %}
                                        {% for k,v in limitEnum['limitBuyForScope'] %}
                                        <option value="{{k}}" {% if limitPromotionDetail['promotion_scope'] is defined and limitPromotionDetail['promotion_scope'] == k %}selected{% endif %}>{{v}}</option>
                                        {% endfor %}
                                        {% endif %}
                                    </select>
                                </div>
                            </div>
                            {% if limitPromotionDetail['promotion_scope'] is defined and limitPromotionDetail['promotion_scope'] == 'category' %}
                            <div id="shop_category" class="form-group" >
                                <label class="col-sm-3 control-label no-padding-right"> <span  class="text-red">*</span>添加分类 </label>
                                <div class="col-xs-12 col-sm-9" id="categoryBox">
                                    {% include "library/category.volt"%}
                                </div>

                            </div>
                            {% else %}
                            <div id="shop_category" class="form-group" style="display: none;">
                                <label class="col-sm-3 control-label no-padding-right"> <span  class="text-red">*</span>添加分类 </label>
                                <div class="col-xs-12 col-sm-9" id="categoryBox">
                                    {% include "library/category.volt"%}
                                </div>

                            </div>
                            {% endif %}
                            <input type='hidden' name='shop_single' id='shop_single'>
                            <input type='hidden' name='shop_more' id='shop_more'>
                            <div id="shop_brand_temp"></div>
                            <div id="shop_single_temp"></div>
                            <div id="shop_more_temp" style="display: none;"></div>
                            <!-- 适用范围end -->

                            <!-- 模板隐藏域赋值start -->
                            <input id="expextCategoryIds" type="hidden" value='{% if limitPromotionDetail['except_category_id'] is defined and limitPromotionDetail['except_category_id'] is not empty %}{{limitPromotionDetail['except_category_id']}}{% endif %}' />
                            <input id="expextBrandIds" type="hidden" value='{% if limitPromotionDetail['except_brand_id'] is defined and limitPromotionDetail['except_brand_id'] is not empty %}{{limitPromotionDetail['except_brand_id']}}{% endif %}' />
                            <input id="expextSingleIds" type="hidden" value='{% if limitPromotionDetail['except_good_id'] is defined and limitPromotionDetail['except_good_id'] is not empty %}{{limitPromotionDetail['except_good_id']}}{% endif %}' />

                            <input id="limitBuyBrands" type="hidden" value='{% if limitBuyBrands is defined and limitBuyBrands is not empty %}{{limitBuyBrands}}{% endif %}' />
                            <input id="limitBuyGoods" type="hidden" value='{% if limitBuyGoods is defined and limitBuyGoods is not empty %}{{limitBuyGoods}}{% endif %}' />
                            <input id="limitBuyMore" type="hidden" value='{% if limitBuyMore is defined and limitBuyMore is not empty %}{{limitBuyMore}}{% endif %}' />
                            <!-- 模板隐藏域赋值end -->

                            <!-- 设置不参加活动start -->
                            <div id="NotJoinActivity"></div>
                            <!-- 设置不参加活动end -->
                            <div id="limit_number"></div>
                          
                            <input id="shop_brand_json" name="shop_brand_json" type="hidden" value=''>
                            <input id="shop_single_json" name="shop_single_json" type="hidden" value=''>
                            <input id="shop_more_json" name="shop_more_json" type="hidden" value=''>
                        </div>
                    </div>
                </div><!-- /.main-content -->
                <div class="clearfix form-actions">
                    <div class="col-md-offset-3 col-md-9">
                        {% if sign is defined and sign == 0 %}
                            <button id="addLimitPromotion2" class="btn btn-info" type="button" disabled>
                                <i class="ace-icon fa fa-check bigger-110"></i>
                                保存
                            </button>
                        {% else %}
                        <button id="addLimitPromotion" class="btn btn-info" type="button" >
                            <i class="ace-icon fa fa-check bigger-110"></i>
                            保存
                        </button>
                        {% endif %}

                        &nbsp; &nbsp; &nbsp;
                        <a href="javascript:void(0)" onclick="javascript:history.go(-1);return false;">
                            <button class="btn" type="button">
                                <i class="ace-icon fa fa-undo bigger-110"></i>
                                返回
                            </button>
                        </a>
                    </div>
                </div>
            </form>
        </div>
        </div>
</div><!-- /.main-container -->

{% endblock %}

{% block footer %}
<script type="text/html" id="xg">
    <label class="col-sm-3 control-label no-padding-right"> <span  class="text-red">*</span>会员限购 </label>
    <div class="col-sm-9">

        <input type="text" name="limit_number" class="col-xs-10 col-sm-5" placeholder="请输入正整数数值" value="{% if limitPromotionDetail['limit_number'] is defined and limitPromotionDetail['limit_number'] is not empty %}{{limitPromotionDetail['limit_number']}}{% endif %}"/>
        &nbsp;&nbsp;
        <select id="limit_unit" name="limit_unit">
            {% if limitEnum['limitBuyUnit'] is defined %}
            {% for k,v in limitEnum['limitBuyUnit'] %}
            <option value="{{k}}" {% if limitPromotionDetail['limit_unit'] is defined and limitPromotionDetail['limit_unit'] == k %}selected{% endif %}>{{v}}</option>
            {% endfor %}
            {% endif %}
        </select>
        （每个会员可以买的总件数/种类数/次数）
    </div>
</script>
<script type="text/html" id="member_limit">
    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right"> 会员限购 </label>
        <div class="col-sm-9">
            <input type="text" name="limit_number" class="col-xs-10 col-sm-5" placeholder="请输入正整数数值" value="{% if limitPromotionDetail['limit_number'] is defined and limitPromotionDetail['limit_number'] is not empty %}{{limitPromotionDetail['limit_number']}}{% endif %}" />
            &nbsp;&nbsp;
            <select id="limit_unit" name="limit_unit">
                {% if limitEnum['limitBuyUnit'] is defined %}
                {% for k,v in limitEnum['limitBuyUnit'] %}
                <option value="{{k}}" {% if limitPromotionDetail['limit_unit'] is defined and limitPromotionDetail['limit_unit'] == k %}selected{% endif %}>{{v}}</option>
                {% endfor %}
                {% endif %}
            </select>
            （每个会员可以买的总件数/种类数/次数）
        </div>
    </div>
</script>
<script type="text/html" id="dp">

    <label class="col-sm-3 control-label no-padding-right"> <span  class="text-red">*</span>添加商品 </label>
    <div class="col-sm-8 add-production">
        <div class="search-strat">
            <input type="text" placeholder="请输入商品名称(可以多个)或者商品ID(可以多个)以英文逗号隔开" class="searchValue" id="search_value">
            <a class="search" id="searchGoods" href="javascript:;"></a>
        </div>
        <div class="discount-left">
            <ul class="search-item" id="discountLeft">

            </ul>
        </div>
        <div class="select-discount">
            <div style="margin-bottom: 10px;">限购数量：<input type="text" id="xg_num" style="width:60px;"></div>
            <a href="javascript:;" id="addOptionAll">全部添加&gt;&gt;</a>
            <a href="javascript:;" id="addOption">添加&gt;&gt;</a>
            <a href="javascript:;" id="delOption">&lt;&lt;删除</a>
            <a href="javascript:;" id="delOptionAll">&lt;&lt;全部删除</a>
        </div>
        <div class="discount-right" >
            <ul class="search-item" id='discountRight'>
            </ul>
        </div>
    </div>
</script>
<script type="text/html" id="more_dp">
    <label class="col-sm-3 control-label no-padding-right"> <span  class="text-red">*</span>添加商品 </label>
    <div class="form-group col-sm-8 add-production">
        <div class="search-strat">
            <input type="text" placeholder="请输入商品名称(可以多个)或者商品ID(可以多个)以英文逗号隔开" class="searchValue" id="search_value">
            <a class="search" id="searchGoods" href="javascript:;"></a>
        </div>
        <div class="discount-left">
            <ul class="search-item" id="moreLeft">

            </ul>
        </div>
        <div class="select-discount">
            <a href="javascript:;" id="addOptionAll">全部添加&gt;&gt;</a>
            <a href="javascript:;" id="addOption">添加&gt;&gt;</a>
            <a href="javascript:;" id="delOption">&lt;&lt;删除</a>
            <a href="javascript:;" id="delOptionAll">&lt;&lt;全部删除</a>
        </div>
        <div class="discount-right" >
            <ul class="search-item" id='moreRight'>
            </ul>
        </div>
    </div>
</script>
<script src="http://{{ config.domain.static }}/assets/js/jquery.datetimepicker.js"></script>
<script src="http://{{ config.domain.static }}/assets/admin/js/promotion/promotion-public.js"></script>
<script src="http://{{ config.domain.static }}/assets/admin/js/promotion/addLimitBuy.js"></script>
{% endblock %}