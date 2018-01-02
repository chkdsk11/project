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
            </div>
            <form id="addAdForm"  class="form-horizontal" action="" method="post"  enctype="multipart/form-data">

                <div class="row">
                    <div class="col-xs-12">
                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right">开启订单审核 </label>
                            <div class="checkbox promotion_platform">
                                <label>
                                    <input name="order_audit" type="radio" {% if conf['order_audit'] == 1 %} checked {% endif%} class="ace" value="1" >
                                    <span class="lbl">&nbsp;开启</span>
                                </label>
                                <label>
                                    <input onclick="return confirm('确定 关闭 订单审核?');" name="order_audit"  type="radio" class="ace" value="0" {% if conf['order_audit'] == 0 %} checked {% endif%} >
                                    <span class="lbl">&nbsp;关闭</span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right">跳过审核的商品 </label>
                            <div class="checkbox promotion_platform">
                                <label>
                                    <input type="checkbox" name="order_no_audit_goods_type[]"  value="1" {% if in_array(1, conf['order_no_audit_goods_type']) %} checked='checked' {% endif %} />处方药
                                </label>
                                <label>
                                    <input type="checkbox" name="order_no_audit_goods_type[]"  value="2"  {% if in_array(2,conf['order_no_audit_goods_type']) %}checked="checked" {% endif %}/>红色非处方药
                                </label>
                                <label>
                                    <input type="checkbox" name="order_no_audit_goods_type[]"  value="3"  {% if in_array(3,conf['order_no_audit_goods_type']) %}checked="checked" {% endif %}/>绿色非处方药
                                </label>
                                <label>
                                    <input type="checkbox" name="order_no_audit_goods_type[]"  value="4"  {% if in_array(4,conf['order_no_audit_goods_type']) %}checked="checked" {% endif %}/>非药物
                                </label>
                                <label>
                                    <span style="color:red">勾上表示这类型的商品不需要审核</span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right">跳过审核的时间 </label>

                            <div class="col-xs-12 col-sm-9">
                                <label>
                                    <input name="order_auto_audit_pass_time" type="text" value="{{ conf['order_auto_audit_pass_time'] }}"/>
                                </label>
                                <select name="time_unit"  id="time_unit">
                                    <option value="s">秒</option>
                                    <option value="i">分</option>
                                    <option value="h">小时</option>
                                    <option value="d">天</option>
                                </select>
                            </div>
                        </div>

                        <div class="space-4"></div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right"> 商品IDs： </label>
                            <div class="col-xs-12 col-sm-9">
                                <textarea rows="5" cols="50" id="product_ids" name="product_ids">{{ conf['product_ids'] }}</textarea>
                                <span style="color:red">用英文,隔开</span>
                            </div>
                        </div>
                        <div class="clearfix form-actions">
                            <div class="col-md-offset-3 col-md-9">
                                <button id="addAd" onclick="ajaxSubmit('addAdForm'); return false;"  class="btn btn-info" type="button">
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
                    </div><!-- /.main-content -->
            </form>
        </div>
    </div>
</div><!-- /.main-container -->

{% endblock %}

{% block footer %}
<script src="http://{{ config.domain.static }}/assets/js/jquery.datetimepicker.js"></script>
<script src="http://{{ config.domain.static }}/assets/admin/js/adhometop/edit.js"></script>
{% endblock %}