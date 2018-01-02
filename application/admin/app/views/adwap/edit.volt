{% extends "layout.volt" %}

{% block content %}
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/jquery.datetimepicker.css" class="ace-main-stylesheet" />
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/admin/css/coupon_addon.css" class="ace-main-stylesheet" />
<!--颜色插件-->
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/colorpicker/css/colorpicker.css"/>


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
                        编辑广告活动
                    </h1>
                </div><!-- /.page-header -->
            </div>
            <form id="addAdForm"  class="form-horizontal" action="" method="post"  enctype="multipart/form-data">
                <div class="">
                    <h3>
                        基本信息
                    </h3>
                </div><!-- /.basic -->
                <div class="row">
                    <div class="col-xs-12">
                        <!-- PAGE CONTENT BEGINS -->

                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right"> <span  class="text-red">*</span>活动名称 </label>

                            <div class="col-sm-9">
                                <input type="text" name="action_name" id="action_name" value="{{info['advertisement']}}" class="col-xs-10 col-sm-5" placeholder="请输入活动名称" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right"> <span  class="text-red">*</span>活动开始时间 </label>

                            <div class="col-sm-9">
                                <input type="text" name="start_time" readOnly="true" value="{{ date('Y-m-d H:i:s', info['start_time']) }}"  id="start_time" placeholder="请选择开始时间" class="col-xs-10 col-sm-5"  />
                            </div>
                        </div>

                        <div class="space-4"></div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right"> <span  class="text-red">*</span>活动结束时间 </label>

                            <div class="col-sm-9">
                                <input type="text" id="end_time" readOnly="true" value="{{ date('Y-m-d H:i:s', info['end_time']) }}" name="end_time" class="col-xs-10 col-sm-5" placeholder="请选择结束时间" />
                            </div>
                        </div>
                        <div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> <span  class="text-red">*</span>选择位置 </label>
                                <div class="col-xs-12 col-sm-9">
                                    <select name="ad_position" disabled="disabled">
                                        <option value="{{ad_position['id']}}" selected = "selected" >{{ad_position['adpositionid_name']}}</option>
                                    </select>
                                    <input type="hidden" value="{{info['adp_id']}}" name="adp_id" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> <span  class="text-red">*</span>选择类型 </label>
                                <div class="col-xs-12 col-sm-9">
                                    <select name="advertisement_type"  id="advertisement_type">
                                        {% if info['advertisement_type'] == 1 %}
                                        <option value="1"  selected = "selected">图片广告</option>
                                        {% elseif info['advertisement_type'] == 2 %}
                                        <option value="2" selected = "selected">商品推荐</option>
                                        {% elseif info['advertisement_type'] == 3 %}
                                        <option value="3" selected = "selected" >文字广告</option>
                                        {% endif%}
                                    </select>
                                </div>
                            </div>

                            <!--  图片广告 start -->
                            <div id="PD" {% if info['advertisement_type'] == 1 %} style="display: block;"{% else %} style="display: none;" {% endif%} >
                                <div class="">
                                    <h3>
                                        添加图片
                                    </h3>
                                </div><!-- /.condition -->
                                <div class="form-group">
                                    <label class="col-sm-3 control-label no-padding-right"> 上传图片： </label>
                                    <div class="col-xs-12 col-sm-9">
                                        <input class="col-xs-10 col-sm-5" type="file"  data-img="first_image" name="first_image_upload" id="first_image_upload"/>
                                        <img src="{{info['image_url']}}" id="first_image" class="img-rounded">
                                        <input type="hidden" name="first_image" />
                                    </div>
                                </div>
                                <!--
                                <div class="form-group navimg" {% if info['adp_id'] == 42 or info['adp_id'] == 43  %} style="display: block;"{% else %} style="display: none;" {% endif%}>
                                    <label class="col-sm-3 control-label no-padding-right"> 变换图图片： </label>
                                    <div class="col-xs-12 col-sm-9">
                                        <input class="col-xs-10 col-sm-5" type="file"  data-img="change_image" name="changet_image_upload" id="changet_image_upload"/>
                                        <img src="{{info['change_img_url']}}" id="change_image" class="img-rounded">
                                        <input type="hidden" value="{{info['change_img_url']}}" name="change_image" />
                                    </div>
                                </div>
                                -->
                                <div class="form-group">
                                    <label class="col-sm-3 control-label no-padding-right">或图片url </label>
                                    <div class="col-xs-12 col-sm-9">
                                        <input value="{{info['image_url']}}"  class="col-xs-10 col-sm-5" type="text" name="second_image" id="second_image" style="width:80%" placeholder="上传或填写URL二选一"/>
                                    </div>
                                </div>
                                <!--
                                <div class="form-group navimg" {% if info['adp_id'] == 42 or info['adp_id'] == 43 %} style="display: block;"{% else %} style="display: none;" {% endif%}>
                                    <label class="col-sm-3 control-label no-padding-right">或变换图图片url </label>
                                    <div class="col-xs-12 col-sm-9">
                                        <input class="col-xs-10 col-sm-5" value="{{info['change_img_url']}}"  type="text" name="changet_second_image" id="changet_second_image" style="width:80%" placeholder="上传或填写URL二选一"/>
                                    </div>
                                </div>
                                -->
                                <div class="form-group">
                                    <label class="col-sm-3 control-label no-padding-right" for="member_tag"> 背景色： </label>
                                    <div class="col-xs-12 col-sm-9">
                                        <input type="text" value="{{info['backgroud']}}" style="background: {{info['backgroud']}};" name="background"  id="picker" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label no-padding-right" for="member_tag"> 广告语： </label>
                                    <div class="col-xs-12 col-sm-9">
                                        <input class="col-xs-10 col-sm-5" value="{{info['slogan']}}" type="text" name="slogan_image" id="slogan_image" style="width:80%" placeholder="请输入广告语"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label no-padding-right" for="member_tag"> 目标地址： </label>
                                    <div class="col-xs-12 col-sm-9">
                                        <input class="col-xs-10 col-sm-5" value="{{info['location']}}" type="text" name="location_image" id="location_image" style="width:80%" placeholder="请输入目标地址"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label no-padding-right" for="member_tag"> 排序： </label>
                                    <div class="col-xs-12 col-sm-9">
                                        <input type="text" value="{{info['order']}}" name="order_image" id="order_image" placeholder="请输入排序序号"/>
                                    </div>
                                </div>
                            </div>
                            <!--  图片广告 end -->
                            <!--  商品推荐 start -->
                            <div id="TJ" {% if info['advertisement_type'] == 2 %} style="display: block;"{% else %} style="display: none;" {% endif%}>
                                <div class="">
                                    <h3>
                                        商品推荐
                                    </h3>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label no-padding-right"></label>
                                    <div class="col-xs-12 col-sm-9">
                                        <table border="0" style="margin-top:-20px;text-align:left;">
                                            <tr>
                                                <td>
                                                    <input type="text" id="product_word" placeholder="请输入商品id或名称" style="margin-right:10px;"/>
                                                </td>
                                                <td>
                                                    <select id="is_global" style="margin-right:10px;display:none;">
                                                        <option value="0">非海外购</option>
                                                        <option value="1">海外购</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="button" class="btn" onclick="searchGoods()" value="搜索" id="product_search" style="margin-right:10px;">
                                                </td>
                                                <td>
                                                    <select id="search_result" style="width:250px;margin-right:10px;"></select>
                                                </td>
                                                <td>
                                                    <input type="button" class="btn  btn-sm btn-primary ajax-show" value="添加" id="add_product">
                                                </td>
                                            </tr>
                                        </table><br />
                                        <table width="80%" border="0" style="text-align:left" id="product_list">
                                            <tr >
                                                <th width="10%">商品id</th>
                                                <th width="45%">商品名称</th>
                                                <th width="20%"">商品广告图</th>
                                                <th width="15%">排序</th>
                                                <th width="10%"">&nbsp;操作</th>
                                            </tr>
                                            {% if info['products'] %}
                                            {% for p in info['products'] %}
                                            <tr >

                                                <td class='pid'>{{p['product_id']}}</td>
                                                <td><input type='hidden' name='product_id[]' value="{{p['product_id']}}"/>{{p['product_name']}}</td>
                                                <td>
                                                <input type='file'  name='product_image1' id="input_{{p['product_id']}}" data-id="{{p['product_id']}}" value='上传' class='upload_image'/>
                                                <input type='hidden' id="p_{{p['product_id']}}" value="{{p['default_image']}}" name="product_image[{{p['product_id']}}]"/>
                                                <img style="width: 120px; height: 120px;"  id="img_{{p['product_id']}}" src="{{p['default_image']}}">

                                                </td>
                                            <td><input type='text' name="order_product[{{p['product_id']}}]" value="{{p['sort']}}" class='product_order' size='5'></td>
                                            <td>&nbsp;<input type="button" value="删除" onclick="deleteok(this)" class="btn"></td>
                                            </tr>
                                            {% endfor %}
                                            {% endif %}
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <!--  商品推荐 end -->
                            <!--  文字推荐 start -->
                            <div id="TD" {% if info['advertisement_type'] ==3 %} style="display: block;"{% else %} style="display: none;" {% endif%} >
                                <div class="">
                                    <h3>
                                        添加推荐
                                    </h3>
                                </div><!-- /.condition -->
                                <div class="form-group">
                                    <label class="col-sm-3 control-label no-padding-right">广告文字</label>
                                    <div class="col-xs-12 col-sm-9">
                                        <input type="text"  style="width:80%" value="{{info['slogan']}}" id="slogan_text" name="slogan_text" placeholder="请输入文字"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label no-padding-right"> 目标地址： </label>
                                    <div class="col-xs-12 col-sm-9">
                                        <input type="text"  style="width:80%" name="location_text" value="{{info['location']}}" id="location_text" placeholder="请输入目标地址"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label no-padding-right"> 排序： </label>
                                    <div class="col-xs-12 col-sm-9">
                                        <input type="text"  name="order_text" id="order_text" value="{{info['order']}}" placeholder="请输入排序"/>
                                    </div>
                                </div>
                            </div>
                            <!--  文字推荐 end -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> 活动说明： </label>
                                <div class="col-xs-12 col-sm-9">
                                    <textarea rows="5" cols="50" id="action_description" name="action_description">{{info['advertisement_desc']}}</textarea>
                                </div>
                            </div>

                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <button id="addAd" class="btn btn-info" type="button">
                                        <i class="ace-icon fa fa-check bigger-110"></i>
                                        保存
                                    </button>
                                    <button  class="btn btn-info" onclick="history.go(-1);return false;" type="button">
                                        <i class="ace-icon fa fa-check bigger-110"></i>
                                        返 回
                                    </button>   &nbsp;                              &nbsp; &nbsp; &nbsp;
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
<script src="http://{{ config.domain.static }}/assets/js/ajaxfileupload.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/jquery.datetimepicker.js"></script>
<script src="http://{{ config.domain.static }}/assets/colorpicker/js/colorpicker.js"></script>
<script src="http://{{ config.domain.static }}/assets/admin/js/adwap/addAd.js?<?php echo time();?>"></script>

{% endblock %}