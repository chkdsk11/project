{% extends "layout.volt" %}

{% block content %}
<style>
</style>
<div class="main-container" id="main-container">
    <div class="main-content">
        <div class="main-content-inner">
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        设置支付方式
                    </h1>
                </div>
            </div>
            <form id="editPayment"  class="form-horizontal" action="/SystemLayout/editPayments" method="post">
                {% if allPayment is defined and allPayment is not empty %}
                    {% for key,payments in allPayment %}
                        <div class="" style="padding-left: 100px;">
                            <h3>
                                {% if channelName[key] is defined %}
                                    {{ channelName[key] }}
                                {% else %}
                                    移动端
                                {% endif %}
                                支付控制
                            </h3>
                        </div>
                        <div class="row">
                            {% for k,payment in payments %}
                                {% if k === 'cash' %}
                                    <div class="col-xs-12">
                                        <div class="form-group">
                                            <label class="col-sm-3 control-label no-padding-right" for="promotion_title">
                                                <input type="checkbox" name="cash_{{ key }}" value="{{ payment['id'] }}" {% if payment['status'] == 1 %}checked="checked"{% endif %} />&nbsp;&nbsp;
                                                货到付款
                                            </label>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-3 control-label no-padding-right" for="promotion_title">
                                                排除商品
                                            </label>
                                            <div class="col-sm-9">
                                                <textarea cols="55" rows="5" name="other_guide_{{ key }}">{{ payment['other_guide'] }}</textarea>&nbsp;&nbsp;&nbsp;
                                                <label class="control-label" style="color:red;">格式：10054,10055</label>
                                            </div>
                                        </div>
                                    </div>
                                {% else %}
                                    <div class="col-xs-12">
                                        <div class="form-group">
                                            <label class="col-sm-3 control-label no-padding-right" for="promotion_title">
                                                <input type="checkbox" name="online_payment_{{ key }}" onclick="changeOnlinePayment({{ key }})" value="1" />&nbsp;&nbsp;
                                                在线支付
                                            </label>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-3 control-label no-padding-right" for="promotion_title">
                                            </label>
                                            <div class="col-sm-9">
                                                {% for val in payment %}
                                                    <input type="checkbox" class="payment_{{ key }}" name="{{ val['alias'] }}_{{ key }}" onclick="changePayment({{ key }})" value="{{ val['id'] }}" {% if val['status'] == 1 %}checked="checked"{% endif %} />
                                                    {{ val['name'] }}
                                                    &nbsp;&nbsp;&nbsp;&nbsp;
                                                {% endfor %}
                                            </div>
                                        </div>
                                    </div>
                                {% endif %}
                            {% endfor %}
                        </div>
                    {% endfor %}
                    <div class="clearfix form-actions">
                        <div class="col-md-offset-3 col-md-9">
                            <button class="btn btn-info" type="button" onclick="saveEdit()">
                                <i class="ace-icon fa fa-check bigger-110"></i>
                                保存
                            </button>
                        </div>
                    </div>
                {% else %}
                    <div class="" style="padding-left: 100px;">
                        <h3>
                            没有支付方式
                        </h3>
                    </div>
                {% endif %}
            </form>
        </div>
    </div>
</div>

{% endblock %}

{% block footer %}
<script src="http://{{ config.domain.static }}/assets/admin/js/systemlayout/payment.js?<?php echo time(); ?>"></script>
{% endblock %}