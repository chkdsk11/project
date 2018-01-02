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

            </div>
            <form id="addAdForm"  class="form-horizontal" action="" method="post"  enctype="multipart/form-data">

            <div class="row">
                <div class="col-xs-12">
                    <!-- PAGE CONTENT BEGINS -->

                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-right"> 入口名称</label>

                        <div class="col-sm-9">
                            <input type="text" name="name" class="col-xs-10 col-sm-5" value="{% if  data['name'] is defined %}{{ data['name'] }}{% endif %}" placeholder="请输入入口名称" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-right"> 活动开始时间 </label>
                        <div class="col-sm-9">
                            <input type="text" name="start_time" value=" {% if  data['start_time'] is defined %}{{ date('Y-m-d H:i:s',data['start_time']) }}{% endif %}" id="start_time" placeholder="请选择开始时间" class="col-xs-10 col-sm-5"  />
                        </div>
                    </div>

                    <div class="space-4"></div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-right"> 活动结束时间 </label>
                        <div class="col-sm-9">
                            <input type="text" name="end_time" value=" {% if  data['end_time'] is defined %}{{ date('Y-m-d H:i:s',data['end_time']) }}{% endif %}" id="end_time" placeholder="请选择开始时间" class="col-xs-10 col-sm-5"  />
                        </div>
                    </div>
                    {% if  is_wap is defined %}
                        <input type="hidden" id="is_wap" name="is_wap" value="{{ is_wap }}"/>
                    {% else %}

                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right"> 开始版本 </label>
                            <div class="col-xs-12 col-sm-9">
                                <select name="start_version" id="start_version">
                                    <option value="0" selected = "selected" >请选择</option>
                                    {% for v in app_versions %}
                                    <option data-num="{{v['version_num']}}" value="{{v['id']}}" {% if  data['start_version'] is defined  and v['id'] == data['start_version'] %} selected = "selected" {% endif %} >{{v['version']}}</option>
                                    {% endfor %}
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-right"> 结束版本 </label>
                        <div class="col-xs-12 col-sm-9">
                            <select name="end_version" id="end_version">
                                <option value="0" selected = "selected" >请选择</option>
                                {% for v in app_versions %}
                                <option data-num="{{v['version_num']}}" value="{{v['id']}}" {% if data['end_version'] is defined  and  v['id'] == data['end_version'] %} selected = "selected" {% endif %} >{{v['version']}}</option>
                                {% endfor %}
                        </select>
                    </div>

                    {% endif %}
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right"> 目标地址 </label>

                    <div class="col-sm-9">
                        <input type="text" value="{% if  data['link'] is defined %}{{ data['link'] }}{% endif %}"  name="link"  class="col-xs-10 col-sm-5" placeholder="请输入目标地址" />
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right"> 上传图片： </label>
                    <div class="col-xs-12 col-sm-9">
                        <input class="col-xs-10 col-sm-5" type="file" multiple="true" name="file_upload"  data-img="icon_img" id="file_upload"/>
                        <input type="hidden" value="{% if  data['icon_img'] is defined %}{{ data['icon_img'] }}{% endif %}" name="icon_img"/>
                    </div>
                    <div class="col-xs-12 col-sm-9">
                        <img id="icon_img" style="width:50px; height:50px" src="{% if  data['icon_img'] is defined %}{{ data['icon_img'] }}{% endif %}"/>
                    </div>

                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right">状态 </label>
                    <div class="checkbox promotion_platform">
                        <label>
                            <input name="status" type="radio" class="ace" value="0" {% if  data['status'] is defined and data['status']==0 %} checked {% else %}checked {% endif %} >
                            <span class="lbl">&nbsp;显示</span>
                        </label>
                        <label>
                            <input name="status" type="radio" class="ace" value="1" {% if  data['status'] is defined and data['status']==1 %} checked {% endif %}>
                            <span class="lbl">&nbsp;隐藏</span>
                        </label>
                    </div>

                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right"> 备注： </label>
                    <div class="col-xs-12 col-sm-9">
                        <textarea rows="5" cols="50" id="action_description" name="remark">{% if  data['remark'] is defined %} {{ data['remark'] }} {% endif %}</textarea>
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
                        </button>   &nbsp;                    &nbsp; &nbsp; &nbsp;
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
<script src="http://{{ config.domain.static }}/assets/admin/js/handyentry/edit.js"></script>
        {% endblock %}