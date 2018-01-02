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
    .thumb{
        width: 120px;
        height: 120px;
    }
</style>
<div class="main-container" id="main-container">
    <div class="main-content">
        <div class="main-content-inner">
            <div class="page-content">
                <div class="page-header">

                </div><!-- /.page-header -->
            </div>
            <form id="addAdForm"  class="form-horizontal" action="" method="post"  enctype="multipart/form-data">

                <div class="row">
                    <div class="col-xs-12">
                        <!-- PAGE CONTENT BEGINS -->

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">活动名称 </label>

                                <div class="col-sm-9">
                                    <input type="text" name="action_name" id="action_name" value="{% if info is defined %}{{info['name']}}{% endif %}" class="col-xs-10 col-sm-5" placeholder="请输入活动名称" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">活动开始时间 </label>

                                <div class="col-sm-9">
                                    <input type="text" name="start_time"value="{% if info is defined %}{{ date('Y-m-d H:i:s',info['start_time'])}}{% endif %}" id="start_time" placeholder="请选择开始时间" class="col-xs-10 col-sm-5"  />
                                </div>
                            </div>

                            <div class="space-4"></div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">活动结束时间 </label>

                                <div class="col-sm-9">
                                    <input type="text" id="end_time" value="{% if info is defined %}{{ date('Y-m-d H:i:s',info['end_time'])}}{% endif %}" name="end_time" class="col-xs-10 col-sm-5" placeholder="请选择结束时间" />
                                </div>
                            </div>
                            <div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">选择位置 </label>
                                <div class="col-xs-12 col-sm-9">
                                    <select name="position"  {% if info is defined %} disabled="disabled" {% endif %}>
                                        <option value="0" selected = "selected" >请选择</option>
                                        {% if ad_position is defined %}
                                            {% for p in ad_position %}
                                            <option {% if info is defined and info['position_id']== p['id'] %} selected="selected" {% endif %} value="{{ p['id'] }}" data-type="{{ p['ad_type'] }}">{{ p['name'] }}</option>
                                            {% endfor %}
                                        {% endif %}
                                    </select>
                                <input id="position" type="hidden" value="{% if info is defined  %}}{{info['position_id']}}{% endif %}"/>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">使用终端 </label>
                                <div class="col-xs-12 col-sm-9">
                                    <select name="channel" id="channel"  {% if info is defined %} disabled="disabled" {% endif %}>
                                        <option value="0" {% if info is defined and info['channel']== 0 %} selected="selected" {% endif %}>所有</option>
                                        <option value="1" {% if info is defined and info['channel']== 1 %} selected="selected" {% endif %} >app</option>
                                        <option value="2" {% if info is defined and info['channel']== 2 %} selected="selected" {% endif %}>wap</option>
                                    </select>
                                    <input  name="channel"  type="hidden" value="{% if info is defined  %}}{{info['channel']}}{% endif %}"/>
                                </div>
                            </div>
                            <div class="form-group">
                                    <label class="col-sm-3 control-label no-padding-right">选择类型 </label>
                                    <div class="col-xs-12 col-sm-9">
                                        <select name="advertisement_type"  {% if info is defined %} disabled="disabled" {% endif %}>
                                            <option value="1" {% if info is defined and info['ad_type']== 2 %} selected="selected" {% else %} selected = "selected" {% endif %}  >图片广告</option>
                                            <option value="2" {% if info is defined and info['ad_type']== 1 %} selected="selected" {% endif %}>商品推荐</option>
                                        </select>
                                        <input id="advertisement_type" name="advertisement_type" type="hidden" value="{% if info is defined  %}{{ info['ad_type'] }}{% endif %}"/>
                                    </div>
                            </div>
                             <!--  图片广告 start -->
                            <div id="PD" {% if info is defined and info['ad_type'] == 2 %} style="display: block;"{% else %} style="display: none;" {% endif %}>
                                <div class="">
                                    <h3>
                                        添加图片
                                    </h3>
                                </div><!-- /.condition -->
                                <div class="form-group">
                                    <label class="col-sm-3 control-label no-padding-right"> 上传图片： </label>
                                    <input type="file"  data-img="first_image" name="first_image_upload" id="first_image_upload"/>
                                    <img  class="thumb" src="{% if info['img_path'] is defined %}{{ info['img_path'] }}{% endif %}" id="first_image"/>
                                    <input type="hidden" value="{% if info['img_path'] is defined %}{{ info['img_path'] }}{% endif %}"  name="first_image" />
                                </div>
                                {% if info['channel'] == 0 or info['channel'] == 1 %}
                                <div class="form-group">
                                    <label class="col-sm-3 control-label no-padding-right">app目标地址 </label>
                                    <div class="col-xs-12 col-sm-9">
                                        <input class="col-xs-10 col-sm-5"  value="{% if info['app_location'] is defined %}{{ info['app_location'] }}{% endif %}" type="text" name="app_location" id="app_location" style="width:80%" placeholder="请输入app目标地址"/>
                                    </div>
                                </div>
                                {% endif %}
                                {% if info['channel'] == 0 or info['channel'] == 2 %}
                                <div class="form-group">
                                    <label class="col-sm-3 control-label no-padding-right" for="member_tag"> wap目标地址 </label>
                                    <div class="col-xs-12 col-sm-9">
                                        <input class="col-xs-10 col-sm-5" value="{% if info['wap_location'] is defined %}{{ info['wap_location'] }}{% endif %}" type="text" name="wap_location" id="wap_location" style="width:80%" placeholder="请输入wap目标地址"/>
                                    </div>
                                </div>
                                {% endif %}
                                <div class="form-group">
                                    <label class="col-sm-3 control-label no-padding-right" for="member_tag"> 排序： </label>
                                    <div class="col-xs-12 col-sm-9">
                                        <input type="text" value="{% if info['sort'] is defined %} {{ info['sort'] }} {% endif %}" name="sort" id="sort" placeholder="请输入排序序号"/>
                                    </div>
                                </div>
                            </div>
                            <!--  图片广告 end -->
                            <!--  商品推荐 start -->
                            <div id="TJ" {% if info is defined and info['ad_type'] == 1 %} style="display: block;"{% else %} style="display: none;" {% endif %}>
                                <div class="">
                                    <h3>
                                        商品推荐
                                    </h3>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label no-padding-right"></label>
                                    <div class="col-xs-12 col-sm-9">
                                        <table width="90%" border="0" style="margin-top:-20px;text-align:left;">
                                            <tr>
                                                <td width="20%"><input type="text" id="product_word" placeholder="请输入商品id或名称"/></td>
                                                <td width="5%"><input type="button" class="btn" onclick="searchgoods()" value="搜索" id="product_search"></td>
                                                <td width="55%"><select id="search_result"></select></td>
                                                <td width="20%"><input type="button" class="btn  btn-sm btn-primary ajax-show" value="添加" id="add_product"></td>
                                            </tr>
                                        </table><br />
                                        <table width="90%" border="0" style="text-align:left" id="product_list">
                                            <tr >
                                                <th width="10%">商品id</th>
                                                <th width="45%">商品名称</th>
                                                <th width="20%">商品广告图</th>
                                                <th width="15%">排序</th>
                                                <th width="10%">&nbsp;操作</th>
                                            </tr>
                                        {% if info['products'] is defined %}
                                            {% for p in info['products'] %}
                                                <tr >
                                                    <th class='pid'>{{ p['product_id'] }}</th>
                                                    <th>{{ p['product_name'] }}</th>
                                                    <th>
                                                        <input type='hidden' name='product_id[]' value="{{ p['product_id'] }}"/>
                                                        <input type='file' class='upload_image' id="input_{{ p['product_id']}}" data-id="{{ p['product_id'] }}" name='product_image1' value='上传'>
                                                        <input type='hidden' value="{{ p['img_path'] }}"  id="p_{{ p['product_id'] }}"  class='product_image' name='product_image[]'>
                                                        <img style="width: 120px;height: 120px;"  id="img_{{ p['product_id'] }}"  src="{{ p['img_path'] }}" /></th>
                                                    <th><input type='text' name='order_product[]' value="{{ p['sort'] }}" class='product_order' size='5'/></th>
                                                    <th>&nbsp;<input type='button' value='删除' onclick='deleteok(this)' class='btn'/> </th>
                                                </tr>
                                            {% endfor %}
                                        {% endif %}
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <!--  商品推荐 end -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> 活动说明： </label>
                                <div class="col-xs-12 col-sm-9">
                                    <textarea rows="5" cols="50" id="action_description" name="action_description">{% if info['description'] is defined %}{{info['description']}}{% endif %}</textarea>
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
                                    </button>   &nbsp; &nbsp; &nbsp;
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
<script src="http://{{ config.domain.static }}/assets/admin/js/adaccesproes/edit.js"></script>
{% endblock %}