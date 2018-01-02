{% extends "layout.volt" %}

{% block content %}
<div class="main-container" id="main-container">
    <div class="main-content">
        <div class="main-content-inner">
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        编辑会员商品
                    </h1>
                </div><!-- /.page-header -->
                    <div class="">
                        <p>
                            基本信息
                        </p>
                    </div><!-- /.basic -->
                    <form id="form" action="/goodsprice/edit" method="post">
                    <div class="row">
                    <div class="col-xs-12">
                        <!-- PAGE CONTENT BEGINS -->
                        <div class="form-horizontal">
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> 商品ID： </label>

                                <div class="col-sm-9">
                                    <input type="text" name="goods_id" class="col-xs-10 col-sm-5" disabled value="{{ info['goods_id'] }}" />
                                </div>
                            </div>
                            <div class="space-4"></div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> 商品名称： </label>

                                <div class="col-sm-9">
                                    <input type="text" name="goods_name" class="col-xs-10 col-sm-5" disabled value="{{ info['goods_name'] }}" />
                                </div>
                            </div>
                            <div class="space-4"></div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> 原价： </label>

                                <div class="col-sm-9">
                                    <input type="text" name="purchase_price" class="col-xs-10 col-sm-5" disabled value="{{ info['purchase_price'] }}" />
                                </div>
                            </div>
                            <div class="space-4"></div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> 会员价/折扣： </label>

                                <div class="col-sm-9">
                                    <input type="text" name="value" class="col-xs-10 col-sm-1" value="{{ info['value'] }}" />
                                    <div class="col-xs-10 col-sm-1">
                                        <select id="type" name="type">
                                            <option value="1" {% if info['type'] == 1 %}selected{% endif %}>元</option>
                                            <option value="2" {% if info['type'] == 2 %}selected{% endif %}>折</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group" id="platform">
                                <label class="col-sm-3 control-label no-padding-right"> 适用平台： </label>
                                <div class="checkbox promotion_mutex">
                                    <label>
                                        <input name="platform_pc" type="checkbox" {% if info['platform_pc'] == 1 %}checked{% endif %} class="ace" value="1">
                                        <span class="lbl">&nbsp;PC</span>
                                    </label>
                                    <label>
                                        <input name="platform_app" type="checkbox" {% if info['platform_app'] == 1 %}checked{% endif %} class="ace" value="1">
                                        <span class="lbl">&nbsp;APP</span>
                                    </label>
                                    <label>
                                        <input name="platform_wap" type="checkbox" {% if info['platform_wap'] == 1 %}checked{% endif %} class="ace" value="1">
                                        <span class="lbl">&nbsp;WAP</span>
                                    </label>
                                    <label style="display: none">
                                        <input name="platform_wechat" type="checkbox" {% if info['platform_wechat'] == 1 %}checked{% endif %} class="ace" value="0">
                                        <span class="lbl">&nbsp;微商城</span>
                                    </label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> 互斥活动： </label>
                                <div class="checkbox mutex">
                                    {% if goodsPriceEnum['mutexPromotion'] is defined %}
                                    {% for k,v in goodsPriceEnum['mutexPromotion'] %}
                                    <label>
                                        <input name="good_set_mutex[]" type="checkbox" class="ace" value="{{k}}" {% if info['mutex'] != ''%}{% for p,m in info['mutex'] %}{% if m == k %}checked{% endif %}{% endfor %}{% endif %}>
                                        <span class="lbl">&nbsp;{{v}}</span>
                                    </label>
                                    {% endfor %}
                                    {% endif %}
                                </div>
                            </div>
                            {% if info['tag_id'] == tag_id %}
                            <div class="space-4"></div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> 限购数量： </label>

                                <div class="col-sm-9">
                                    <input type="text" name="limit_number" class="col-xs-10 col-sm-5" value="{{ info['limit_number'] }}" />
                                </div>
                            </div>
                            {% endif %}
                            <div class="space-4"></div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> 会员标签： </label>

                                <div class="col-sm-9">
                                    <select name="tag_id" disabled>
                                        {% for val in tagData %}
                                        <option value="{{ val['tag_id'] }}" {% if info['tag_id'] == val['tag_id'] %}selected{% endif %}>{{ val['tag_name'] }}</option>
                                        {% endfor %}
                                    </select>
                                </div>
                            </div>

                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <button id="addFullMinues" class="btn btn-info" type="submit">
                                        <i class="ace-icon fa fa-check bigger-110"></i>
                                        保存<input type="hidden" name="tag_goods_id" value="{{ info['tag_goods_id'] }}">
                                    </button>

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
        </div>
</div><!-- /.main-container -->

{% endblock %}

{% block footer %}
<script src="http://{{ config.domain.static }}/assets/admin/js/goodsprice/globalGoodsPrice.js"></script>
{% endblock %}