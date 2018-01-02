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
                        编辑
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
                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right"> <span  class="text-red">*</span>活动名称 </label>

                            <div class="col-sm-9">
                                <input type="text" id="ad_name" name="name" class="col-xs-10 col-sm-5"  placeholder="请输入活动名称" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right"> <span  class="text-red">*</span>活动开始时间 </label>

                            <div class="col-sm-9">
                                <input type="text" name="start_time" id="start_time" placeholder="请选择开始时间" class="col-xs-10 col-sm-5"  />
                            </div>
                        </div>

                        <div class="space-4"></div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right"> <span  class="text-red">*</span>活动结束时间 </label>

                            <div class="col-sm-9">
                                <input type="text" id="end_time" name="end_time" class="col-xs-10 col-sm-5" placeholder="请选择结束时间" />
                            </div>
                        </div>
                        <div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> <span  class="text-red">*</span>选择类型 </label>
                                <div class="col-xs-12 col-sm-9">
                                    <select name="start_version">
                                        <option value="0" selected = "selected" >图片广告</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> 备注： </label>
                                <div class="col-xs-12 col-sm-9">
                                    <input type="button" class="btn btn-sm btn-primary ajax-show" value="添加广告图" id="add_img">
                                    <table border="1" style="width:60%;" id="ad_imgs" >
                                        <tr >
                                            <th class="text-center">图片地址</th>
                                            <th class="text-center">目标地址</th>
                                            <th class="text-center">广告图</th>
                                            <th class="text-center">排序</th>
                                            <th class="text-center">操作</th>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <button id="addAd" class="btn btn-info" type="button">
                                        <i class="ace-icon fa fa-check bigger-110"></i>
                                        确认添加
                                    </button>
                                    <button  class="btn btn-info" onclick="history.go(-1);return false;" type="button">
                                        <i class="ace-icon fa fa-check bigger-110"></i>
                                        返 回
                                    </button>   &nbsp;                                   &nbsp; &nbsp; &nbsp;
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
<script src="http://{{ config.domain.static }}/assets/admin/js/adhometop/edit.js"></script>
{% endblock %}