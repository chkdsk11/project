{% extends "layout.volt" %}


        {% block content %}
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/jquery.datetimepicker.css" class="ace-main-stylesheet" />
        <!--颜色插件-->


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
                        <label class="col-sm-3 control-label no-padding-right"> <span  class="text-red">*</span>主题路径 </label>

                        <div class="col-sm-9">
                            <input type="text" value="{% if info is defined %}{{ info['path'] }}{% endif %}" name="wap_path" id="wap_path" class="col-xs-10 col-sm-5" placeholder="填写主题路径" />
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
                            </button>   &nbsp;                          &nbsp; &nbsp; &nbsp;
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
<script src="http://{{ config.domain.static }}/assets/admin/js/apptheme/wapedit.js"></script>

{% endblock %}