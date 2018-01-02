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
                </div><!-- /.page-header -->
            </div>
            <form id="addAdForm"  class="form-horizontal" action="" method="post"  enctype="multipart/form-data">
                <div class="">
                </div><!-- /.basic -->
                <div class="row">
                    <div class="col-xs-12">
                        <!-- PAGE CONTENT BEGINS -->



                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> <span  class="text-red">*</span>开始时间 </label>

                                <div class="col-sm-9">
                                    <input type="text" value="{% if info is defined %}{{date('Y-m-d H:i:s',info['start_time'])}}{% endif %}" name="start_time" id="start_time" placeholder="请选择开始时间" class="col-xs-10 col-sm-5"  />
                                </div>
                            </div>

                            <div class="space-4"></div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> <span  class="text-red">*</span>结束时间 </label>

                                <div class="col-sm-9">
                                    <input type="text" value="{% if info is defined %}{{date('Y-m-d H:i:s',info['end_time'])}}{% endif %}" id="end_time" name="end_time" class="col-xs-10 col-sm-5" placeholder="请选择结束时间" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> 上传主题包： </label>
                                <div class="col-xs-12 col-sm-9">
                                    <input class="col-xs-10 col-sm-5" type="file"  data-img="first_image" name="first_image_upload" id="first_image_upload"/>
                                    <p>{% if info is defined %}{{info['path']}}{% endif %}</p>
                                    <input type="hidden" value="{% if info is defined %}{{info['path']}}{% endif %}" name="theme_zip" />
                                </div>
                            </div>
                            <div class="form-group">
                                    <label class="col-sm-3 control-label no-padding-right"> <span  class="text-red">*</span>客户端选择 </label>
                                    <div class="col-xs-12 col-sm-9">
                                        <select name="channel" id="channel">
                                            {% if info is defined and  info['channel'] ==89 %}
                                                <option value="89">ios</option>
                                            {% elseif  info['channel'] ==90 %}
                                                <option value="90">安卓</option>
                                            {% else %}
                                                <option value="89">ios</option>
                                                <option value="90">安卓</option>
                                            {% endif%}

                                        </select>
                                    </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">是否显示主会场 </label>
                                <div class="checkbox promotion_platform">
                                    <label>
                                        <input name="is_show_local" type="radio" class="ace" value="1" {% if info is defined and  info['is_show_local'] ==1 %} checked {% else %} checked {% endif%}>
                                        <span class="lbl">&nbsp;是</span>
                                    </label>
                                    <label>
                                        <input name="is_show_local" type="radio" class="ace" value="0" {% if info is defined and  info['is_show_local'] ==0 %} checked {% endif%}>
                                        <span class="lbl">&nbsp;否</span>
                                    </label>
                                </div>

                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> <span  class="text-red">*</span>主会场地址 </label>

                                <div class="col-sm-9">
                                    <input type="text" value="{% if info is defined %}{{info['local_url']}}{% endif %}" name="local_url" id="local_url" class="col-xs-10 col-sm-5" placeholder="主会场地址" />
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
                                    </button>   &nbsp;
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
<script src="http://{{ config.domain.static }}/assets/js/ajaxfileupload.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/jquery.datetimepicker.js"></script>
<script src="http://{{ config.domain.static }}/assets/admin/js/apptheme/appedit.js"></script>

{% endblock %}