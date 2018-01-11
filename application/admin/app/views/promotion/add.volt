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
                        添加促销活动
                    </h1>
                </div><!-- /.page-header -->
            </div>
            <form id="addPromotionForm"  class="form-horizontal" action="/promotion/add" method="post" onsubmit="ajaxSubmit(addPromotionForm);return false;">
                <div class="">
                    <h3>
                        基本信息
                    </h3>
                </div><!-- /.basic -->
                <div class="row">
                    <div class="col-xs-12">
                        <!-- PAGE CONTENT BEGINS -->
                        <div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_type"> <span  class="text-red">*</span>优惠类型 </label>
                                <div class="col-xs-12 col-sm-9">
                                    <select id="promotion_type" name="promotion_type">
                                        {% if promotionEnum['offerType'] is defined %}
                                        {% for k,v in promotionEnum['offerType'] %}
                                        <option value="{{k}}">{{v}}</option>
                                        {% endfor %}
                                        {% endif %}
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_title"> <span  class="text-red">*</span>活动名称 </label>

                                <div class="col-sm-9">
                                    <input type="text" id="promotion_title" name="promotion_title" class="col-xs-10 col-sm-5" placeholder="不可为空" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> <span  class="text-red">*</span>活动开始时间 </label>

                                <div class="col-sm-9">
                                    <input type="text" id="start_time" name="promotion_start_time" class="col-xs-10 col-sm-5" placeholder="不可为空" />
                                </div>
                            </div>

                            <div class="space-4"></div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> <span  class="text-red">*</span>活动结束时间 </label>

                                <div class="col-sm-9">
                                    <input type="text" id="end_time" name="promotion_end_time" class="col-xs-10 col-sm-5" placeholder="不可为空" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> <span  class="text-red">*</span>活动平台 </label>
                                <div class="checkbox promotion_platform">
                                    {% if shopPlatform is defined and shopPlatform is not empty %}
                                        {% for key,platform in shopPlatform %}
                                            <label>
                                                <input name="promotion_platform_{{ key }}" type="checkbox" class="ace" value="1" checked>
                                                <span class="lbl">&nbsp;{{ platform }}</span>
                                            </label>
                                        {% endfor %}
                                    {% endif %}
                                </div>

                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_content"> <span  class="text-red">*</span>活动说明 </label>
                                <div class="col-sm-9">
                                    <textarea name="promotion_content" class="col-xs-10 col-sm-5 no-padding-right" id="promotion_content" placeholder="不可为空" ></textarea>

                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_copywriter"> 促销文案 </label>
                                <div class="col-sm-9">
                                    <textarea name="promotion_copywriter" class="col-xs-10 col-sm-5 no-padding-right" id="promotion_copywriter" placeholder="选填，未填写促销文案系统通过原规则自行生成促销文案" ></textarea>

                                </div>
                            </div>

                            <div class="form-group real_pay" style="display: none;">
                                <label class="col-sm-3 control-label no-padding-right"> 是否使用实付 </label>
                                <div class="col-xs-12 col-sm-9">
                                    <select name="promotion_is_real_pay" class="ace" id="selector">
                                        <option value="0">否</option>
                                        <option value="1">是</option>
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
                                        <input name="promotion_mutex[]" type="checkbox" class="ace" value="{{k}}">
                                        <span class="lbl">&nbsp;{{v}}</span>
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
                                        <option value="{{k}}">{{v}}</option>
                                        {% endfor %}
                                        {% endif %}
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="member_tag"> 会员标签 </label>
                                <div class="col-xs-12 col-sm-9">
                                    <select id="member_tag" name="member_tag" class="col-sm-2">
                                        <option value="0">不指定</option>
                                        {% if memberTag is defined and memberTag is not empty%}
                                        {% for k,v in memberTag %}
                                        <option value="{{v['tag_id']}}">{{v['tag_name']}}</option>
                                        {% endfor %}
                                        {% endif %}
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="join_times"> 单用户最多参与 </label>

                                <div class="col-sm-9">
                                    <input type="text" id="join_times" name="join_times" class="col-xs-10 col-sm-2" /><span>&nbsp;&nbsp;次&nbsp;&nbsp;（不填代表不限制）</span>
                                </div>
                            </div>
                            <!-- 适用范围start -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_scope">  <span  class="text-red">*</span>适用范围 </label>
                                <div class="col-xs-12 col-sm-9">
                                    <select name="promotion_scope" id="promotion_scope">
                                        {% if promotionEnum['forScope'] is defined %}
                                        {% for k,v in promotionEnum['forScope'] %}
                                        <option value="{{k}}">{{v}}</option>
                                        {% endfor %}
                                        {% endif %}
                                    </select>
                                </div>
                            </div>

                            <div id="shop_category" class="form-group" style="display: none;">
                                <label class="col-sm-3 control-label no-padding-right"> <span  class="text-red">*</span>添加分类 </label>
                                <div class="col-xs-12 col-sm-9" id="categoryBox">
                                    {% include "library/category.volt"%}
                                    <!--<select name="shop_category[]" id="one_category">
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

                                    </select>-->
                                </div>

                            </div>
                            <input id="premiums_group" type="hidden" name="premiums_group"/>
                            <input type='hidden' name='shop_single' id='shop_single'>
                            <div id="shop_brand_temp" style="display: none;"></div>
                            <div id="shop_single_temp" style="display: none;"></div>
                            <!-- 适用范围end -->

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
                                    <button id="addPromotion" class="btn btn-info" type="button">
                                        <i class="ace-icon fa fa-check bigger-110"></i>
                                        确认添加
                                    </button>
                                    &nbsp; &nbsp; &nbsp;
                                    <!--<button class="btn" type="reset">
                                        <i class="ace-icon fa fa-undo bigger-110"></i>
                                        重置
                                    </button>-->
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
<script src="http://{{ config.domain.static }}/assets/js/jquery.datetimepicker.js"></script>
<script src="http://{{ config.domain.static }}/assets/admin/js/promotion/promotion-public.js"></script>
<script src="http://{{ config.domain.static }}/assets/admin/js/promotion/addPromotion.js"></script>
{% endblock %}