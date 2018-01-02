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
                        编辑促销活动
                    </h1>
                </div><!-- /.page-header -->
            </div>
            <form id="addPromotionForm"  class="form-horizontal" action="/promotion/edit" method="post" onsubmit="ajaxSubmit(addPromotionForm);return false;">
                <div class="">
                    <h3>
                        基本信息
                    </h3>
                </div><!-- /.basic -->
                <div class="row">
                    <div class="col-xs-12">
                        <!-- PAGE CONTENT BEGINS -->
                        <div>
                            <input type="hidden" name="promotion_id" value="{% if promotionDetail['promotion_id'] is defined  %}{{ promotionDetail['promotion_id'] }}{% endif %}"/>
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_type"> <span  class="text-red">*</span>优惠类型 </label>
                                <div class="col-xs-12 col-sm-9">
                                    <select id="promotion_type" name="promotion_type">
                                        {% if promotionEnum['offerType'] is defined %}
                                        {% for k,v in promotionEnum['offerType'] %}
                                        <option value="{{k}}" {% if promotionDetail['promotion_type'] is defined and promotionDetail['promotion_type'] == k %}selected{% endif %}>{{v}}</option>
                                        {% endfor %}
                                        {% endif %}
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_title"> <span  class="text-red">*</span>活动名称 </label>

                                <div class="col-sm-9">
                                    <input type="text" id="promotion_title" name="promotion_title" class="col-xs-10 col-sm-5" placeholder="不可为空" value="{% if promotionDetail['promotion_title'] is defined  %}{{ promotionDetail['promotion_title'] }}{% endif %}"/>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> <span  class="text-red">*</span>活动开始时间 </label>

                                <div class="col-sm-9">
                                    <input type="text" id="start_time" name="promotion_start_time" class="col-xs-10 col-sm-5" placeholder="不可为空" value="{% if promotionDetail['promotion_start_time'] is defined  %}{{ promotionDetail['promotion_start_time'] }}{% endif %}"/>
                                </div>
                            </div>

                            <div class="space-4"></div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> <span  class="text-red">*</span>活动结束时间 </label>

                                <div class="col-sm-9">
                                    <input type="text" id="end_time" name="promotion_end_time" class="col-xs-10 col-sm-5" placeholder="不可为空" value="{% if promotionDetail['promotion_end_time'] is defined  %}{{ promotionDetail['promotion_end_time'] }}{% endif %}"/>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> <span  class="text-red">*</span>活动平台 </label>
                                <div class="checkbox promotion_platform">
                                    <label>
                                        <input name="promotion_platform_pc" type="checkbox" class="ace" {% if promotionDetail['promotion_platform_pc'] is defined %}value="{{ promotionDetail['promotion_platform_pc'] }}" {% if promotionDetail['promotion_platform_pc'] == 1 %}checked{% endif %}{% endif %}>
                                        <span class="lbl">&nbsp;PC</span>
                                    </label>
                                    <label>
                                        <input name="promotion_platform_app" type="checkbox" class="ace" {% if promotionDetail['promotion_platform_app'] is defined %}value="{{ promotionDetail['promotion_platform_app'] }}" {% if promotionDetail['promotion_platform_app'] == 1 %}checked{% endif %}{% endif %}>
                                        <span class="lbl">&nbsp;APP</span>
                                    </label>
                                    <label>
                                        <input name="promotion_platform_wap" type="checkbox" class="ace" {% if promotionDetail['promotion_platform_wap'] is defined %}value="{{ promotionDetail['promotion_platform_wap'] }}" {% if promotionDetail['promotion_platform_wap'] == 1 %}checked{% endif %}{% endif %}>
                                        <span class="lbl">&nbsp;WAP</span>
                                    </label>
                                    <label style="display: none">
                                        <input name="promotion_platform_wechat" type="checkbox" class="ace" {% if promotionDetail['promotion_platform_wechat'] is defined %}value="{{ promotionDetail['promotion_platform_wechat'] }}" {% if promotionDetail['promotion_platform_wechat'] == 1 %}checked{% endif %}{% endif %}>
                                        <span class="lbl">&nbsp;微商城</span>
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_content"> <span  class="text-red">*</span>活动说明 </label>
                                <div class="col-sm-9">
                                    <textarea name="promotion_content" class="col-xs-10 col-sm-5" id="promotion_content" placeholder="不可为空">{% if promotionDetail['promotion_content'] is defined  %}{{ promotionDetail['promotion_content'] }}{% endif %}</textarea>

                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_copywriter"> 促销文案 </label>
                                <div class="col-sm-9">
                                    <textarea name="promotion_copywriter" class="col-xs-10 col-sm-5" id="promotion_copywriter" placeholder="选填，未填写促销文案系统通过原规则自行生成促销文案">{% if promotionDetail['promotion_copywriter'] is defined and promotionDetail['promotion_copywriter'] is not empty %} {{ promotionDetail['promotion_copywriter'] }} {% endif %}</textarea>

                                </div>
                            </div>

                            <div class="form-group real_pay" {% if promotionDetail['promotion_type'] is defined and promotionDetail['promotion_type'] != 15 %}style="display: none;"{% endif %}>
                                <label class="col-sm-3 control-label no-padding-right"> 是否使用实付 </label>
                                <div class="col-xs-12 col-sm-9">
                                    <select name="promotion_is_real_pay" class="ace" id="selector">
                                        <option value="0" {% if promotionDetail['promotion_is_real_pay'] is defined %}value="{{ promotionDetail['promotion_is_real_pay'] }}" {% if promotionDetail['promotion_is_real_pay'] == 0 %}selected="selected"{% endif %}{% endif %}>否</option>
                                        <option value="1" {% if promotionDetail['promotion_is_real_pay'] is defined %}value="{{ promotionDetail['promotion_is_real_pay'] }}" {% if promotionDetail['promotion_is_real_pay'] == 1 %}selected="selected"{% endif %}{% endif %}>是</option>
                                    </select>
                                </div>
                            </div>

                            <div class="">
                                <h3>
                                    条件信息
                                </h3>
                            </div><!-- /.condition -->

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> 互斥活动 </label>
                                <div class="checkbox promotion_mutex">
                                    {% if promotionEnum['mutexAlone'] is defined %}
                                    {% for k,v in promotionEnum['mutexAlone'] %}
                                    <label>
                                        {% if promotionDetail['promotion_type'] is defined and  promotionDetail['promotion_mutex'] is defined %}
                                        <input name="promotion_mutex[]"
                                        {% if promotionDetail['promotion_type'] == 20 %}{% if k != 20 %}name="promotion_mutex[]"{% endif %}{% if k == 20 %}disabled="disabled"{% endif %}{% endif %}
                                                 type="checkbox" class="ace" value="{{k}}"

                                               {% if in_array(k,promotionDetail['promotion_mutex']) %}checked{% endif %}>
                                        <span class="lbl">&nbsp;{{v}}</span>
                                        {% endif %}
                                    </label>
                                    {% endfor %}
                                    {% endif %}
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_for_users"> <span  class="text-red">*</span>适用人群 </label>
                                <div class="col-xs-12 col-sm-9">
                                    <select id="promotion_for_users" name="promotion_for_users">
                                        {% if promotionEnum['forPeople'] is defined %}
                                        {% for k,v in promotionEnum['forPeople'] %}
                                        <option value="{{k}}" {% if promotionDetail['promotion_for_users'] is defined and promotionDetail['promotion_for_users'] == k %}selected{% endif %}>{{v}}</option>
                                        {% endfor %}
                                        {% endif %}
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="member_tag"> 会员标签 </label>
                                <div class="col-xs-12 col-sm-9">
                                    <select id="member_tag" name="member_tag">
                                        <option value="0" {% if promotionDetail['member_tag'] is defined and promotionDetail['member_tag'] == 0 %}selected{% endif %}>不指定</option>
                                        {% if memberTag is defined and memberTag is not empty%}
                                        {% for k,v in memberTag %}
                                        <option value="{{v['tag_id']}}" {% if promotionDetail['member_tag'] is defined and promotionDetail['member_tag'] == v['tag_id'] %}selected{% endif %}>{{v['tag_name']}}</option>
                                        {% endfor %}
                                        {% endif %}
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="join_times"> 单用户最多参与 </label>

                                <div class="col-sm-9">
                                    <input type="text" id="join_times" name="join_times" class="col-xs-10 col-sm-2" value="{% if promotionDetail['join_times'] is defined  %}{{ promotionDetail['join_times'] }}{% endif %}"/><span>&nbsp;&nbsp;次&nbsp;&nbsp;（不填代表不限制）</span>
                                </div>
                            </div>
                            <!-- 适用范围start -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_scope">  <span  class="text-red">*</span>适用范围 </label>
                                <div class="col-xs-12 col-sm-9">
                                    <select name="promotion_scope" id="promotion_scope">
                                        {% if promotionEnum['forScope'] is defined %}
                                        {% for k,v in promotionEnum['forScope'] %}
                                        <option value="{{k}}" {% if promotionDetail['promotion_scope'] is defined and promotionDetail['promotion_scope'] == k %}selected{% endif %}>{{v}}</option>
                                        {% endfor %}
                                        {% endif %}
                                    </select>
                                </div>
                            </div>
                            <div id="shop_category" class="form-group" hidden>
                                <label class="col-sm-3 control-label no-padding-right"> <span  class="text-red">*</span>添加分类 </label>
                                <div class="col-xs-12 col-sm-9" id="categoryBox">
                                    {% include "library/category.volt"%}
                                </div>
                            </div>
                            <input type='hidden' name='shop_single' id='shop_single' {% if promotionDetail['promotion_scope'] is defined and promotionDetail['promotion_scope'] == 'single' %} value="{{promotionDetail['condition_string']}}" {% endif %}>

                            <div id="shop_brand_temp"></div>
                            {% if promotionDetail['promotion_scope'] is defined and promotionDetail['promotion_scope'] == 'brand' %}
                                <input type="hidden" id="brand_ids_tmp" value="{{ promotionDetail['condition_string']}}">
                            {% endif %}
                            {% if promotionDetail['promotion_scope'] is defined and promotionDetail['promotion_scope'] == 'single' %}
                            <input type="hidden" id="single_flag">
                            {% endif %}
                            <div id="shop_single_temp"></div>
                            <input type="hidden" id="premiums_group" name="premiums_group" />
                            <input type="hidden" id="premiums_group_detail" value='{{json_rule_premiums_value}}'>
                            <input type="hidden" id="is_superimposed" value='{{promotionDetail["is_superimposed"]}}'>
                            <!-- 适用范围end -->

                            <!-- 模板隐藏域赋值start -->
                            <input id="brandDetail" type="hidden" value='{% if brandDetail is defined %}{{brandDetail}}{% endif %}' />
                            <input id="singleDetail" type="hidden" value='{% if singleDetail is defined %}{{singleDetail}}{% endif %}' />

                            <input id="expextCategoryIds" type="hidden" value='{% if promotionDetail['except_category_id'] is defined and promotionDetail['except_category_id'] is not empty %}{{promotionDetail['except_category_id']}}{% endif %}' />
                            <input id="expextBrandIds" type="hidden" value='{% if promotionDetail['except_brand_id'] is defined and promotionDetail['except_brand_id'] is not empty %}{{promotionDetail['except_brand_id']}}{% endif %}' />
                            <input id="expextSingleIds" type="hidden" value='{% if promotionDetail['except_good_id'] is defined and promotionDetail['except_good_id'] is not empty %}{{promotionDetail['except_good_id']}}{% endif %}' />

                            <input id="ruleValue" type="hidden" value='{% if promotionDetail['rule_value'] is defined and promotionDetail['rule_value'] is not empty %}{{promotionDetail['rule_value']}}{% endif %}' />

                            <!-- 模板隐藏域赋值end -->

                            <!-- 设置不参加活动start -->
                            <div id="NotJoinActivity"></div>
                            <!-- 设置不参加活动end -->
                            <div class="">
                                <h3>
                                    规则信息
                                </h3>
                            </div><!-- /.rule -->
                            <div>
                                <table id="rule-table" class="table table-striped table-bordered table-hover">
                                    <thead>
                                    <tr>
                                        <th>门槛</th>
                                        <th>优惠方式</th>
                                        <th>优惠说明</th>
                                        <th>是否可叠加</th>
                                        <th>操作</th>
                                    </tr>
                                    </thead>

                                    <tbody id="rule-tbody">

                                    </tbody>
                                </table>
                            </div><!-- /.rule_table -->
                            <div>
                                <button id="addNextRule" class="btn btn-info" type="button">
                                    添加下一条规则
                                </button>
                            </div>

                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                {% if sign is defined and sign == 0 %}
                                <button id="addPromotion_tmp" class="btn btn-info" type="button" disabled>
                                        <i class="ace-icon fa fa-check bigger-110"></i>
                                        保存
                                    </button>
                                {% else %}
                                <button id="addPromotion" class="btn btn-info" type="button">
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
                        </div>
                    </div>
                </div><!-- /.main-content -->
            </form>
        </div>
        </div>
</div><!-- /.main-container -->

{% endblock %}

{% block footer %}
<script type="text/html" id="select_category">
    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right"  > 选择品类 </label>
        <div class="col-xs-12 col-sm-9">
            {% if category_arr is defined %}
            {% for info in category_arr%}
            {% if info["level"] == "1" %}
            <select name="shop_category[]" id="one_category">
                <option value="0">请选择</option>
                {% if info['info'] is defined %}
                {% for cat in info['info'] %}
                <option value="{{cat['id']}}" {% if cat['id'] == info["cid"] %}selected{% endif %}>{{cat['category_name']}}</option>
                {% endfor %}
                {% endif %}
            </select>
            {% endif %}
            {% if info["level"] == "2" %}
            <select name="shop_category[]" id="two_category">
                <option value="0">请选择</option>
                {% if info['info'] is defined %}
                {% for cat in info['info'] %}
                <option value="{{cat['id']}}" {% if cat['id'] == info["cid"] %}selected{% endif %}>{{cat['category_name']}}</option>
                {% endfor %}
                {% endif %}
            </select>
            {% endif %}
            {% if info["level"] == "3" %}
            <select name="shop_category[]" id="three_category">
                <option value="0">请选择</option>
                {% if info['info'] is defined %}
                {% for cat in info['info'] %}
                <option value="{{cat['id']}}" {% if cat['id'] == info["cid"] %}selected{% endif %}>{{cat['category_name']}}</option>
                {% endfor %}
                {% endif %}
            </select>
            {% endif %}
            {% endfor %}
            {% else %}
            <select name="shop_category[]" id="one_category">
                <option value="0">请选择</option>
                {% if category is defined %}
                {% for k,v in category %}
                <option value="{{v['id']}}">{{v['category_name']}}</option>
                {% endfor %}
                {% endif %}
            </select>
            <select name="shop_category[]" id="two_category" style="display: none;">

            </select>
            <select name="shop_category[]" id="three_category" style="display: none;">

            </select>
            {% endif %}
        </div>
    </div>
</script>
<script src="http://{{ config.domain.static }}/assets/js/jquery.datetimepicker.js"></script>
<script src="http://{{ config.domain.static }}/assets/admin/js/promotion/promotion-public.js"></script>
<script src="http://{{ config.domain.static }}/assets/admin/js/promotion/addPromotion.js"></script>
{% endblock %}