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
                        添加限购活动
                    </h1>
                </div><!-- /.page-header -->
            </div>
            <form id="addLimitPromotionForm"  class="form-horizontal" action="/limitbuy/add" method="post" onsubmit="ajaxSubmit(addLimitPromotionForm);return false;">
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
                                    <label>
                                        <input name="promotion_platform_pc" type="checkbox" class="ace" value="1" checked>
                                        <span class="lbl">&nbsp;PC</span>
                                    </label>
                                    <label>
                                        <input name="promotion_platform_app" type="checkbox" class="ace" value="1" checked>
                                        <span class="lbl">&nbsp;APP</span>
                                    </label>
                                    <label>
                                        <input name="promotion_platform_wap" type="checkbox" class="ace" value="1" checked>
                                        <span class="lbl">&nbsp;WAP</span>
                                    </label>
                                    <label style="display: none">
                                        <input name="promotion_platform_wechat" type="checkbox" class="ace" value="0">
                                        <span class="lbl">&nbsp;微商城</span>
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_content"> <span  class="text-red">*</span>活动说明 </label>
                                <div class="col-sm-9">
                                    <textarea name="promotion_content" class="col-xs-10 col-sm-5" id="promotion_content" placeholder="不可为空" ></textarea>

                                </div>
                            </div>

                            <div class="">
                                <h3>
                                    条件信息
                                </h3>
                            </div><!-- /.condition -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_member_level"> <span  class="text-red">*</span>会员等级 </label>
                                <div class="col-xs-12 col-sm-9">
                                    <select id="promotion_member_level" name="promotion_member_level">
                                        {% if limitEnum['memberLevel'] is defined %}
                                        {% for k,v in limitEnum['memberLevel'] %}
                                        <option value="{{k}}">{{v}}</option>
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
                            <!-- 适用范围start -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_scope"> <span  class="text-red">*</span>适用范围 </label>
                                <div class="col-xs-12 col-sm-9">
                                    <select name="promotion_scope" id="promotion_scope">
                                        <option value="0">请选择</option>
                                        {% if limitEnum['limitBuyForScope'] is defined %}
                                        {% for k,v in limitEnum['limitBuyForScope'] %}
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
                                </div>

                            </div>
                            <input type='hidden' name='shop_single' id='shop_single'>
                            <input type='hidden' name='shop_more' id='shop_more'>
                            <div id="shop_brand_temp" style="display: none;"></div>
                            <div id="shop_single_temp" style="display: none;"></div>
                            <div id="shop_more_temp" style="display: none;"></div>
                            <!-- 适用范围end -->

                            <!-- 设置不参加活动start -->
                            <div id="NotJoinActivity"></div>
                            <!-- 设置不参加活动end -->

                            <div class="form-group" id="limit_number">

                            </div>
                            <input id="shop_brand_json" name="shop_brand_json" type="hidden" value=''>
                            <input id="shop_single_json" name="shop_single_json" type="hidden" value=''>
                            <input id="shop_more_json" name="shop_more_json" type="hidden" value=''>
                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <button id="addLimitPromotion" class="btn btn-info" type="button">
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
<script type="text/html" id="xg">
    <label class="col-sm-3 control-label no-padding-right"> <span  class="text-red">*</span>会员限购 </label>
    <div class="col-sm-9">

        <input type="text" name="limit_number" class="col-xs-10 col-sm-5" placeholder="请输入正整数数值" />
        &nbsp;&nbsp;
        <select id="limit_unit" name="limit_unit">
            {% if limitEnum['limitBuyUnit'] is defined %}
            {% for k,v in limitEnum['limitBuyUnit'] %}
            <option value="{{k}}">{{v}}</option>
            {% endfor %}
            {% endif %}
        </select>
        （每个会员可以买的总件数/种类数/次数 ）
    </div>
</script>
<script src="http://{{ config.domain.static }}/assets/js/jquery.datetimepicker.js"></script>
<script src="http://{{ config.domain.static }}/assets/admin/js/promotion/promotion-public.js"></script>
<script src="http://{{ config.domain.static }}/assets/admin/js/promotion/addLimitBuy.js"></script>
{% endblock %}