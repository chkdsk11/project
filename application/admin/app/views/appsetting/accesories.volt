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

                </div><!-- /.page-header -->
            </div>
            <form id="addAdForm"  class="form-horizontal" action="" method="post"  enctype="multipart/form-data">

                <div class="row">
                    <div class="col-xs-12">


                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right">是否开启 </label>
                            <div class="checkbox promotion_platform">
                                <label>
                                    <input name="config_value" type="radio" class="ace" value="1"  {% if conf['config_value'] == 1 %} checked {% endif%} >
                                    <span class="lbl">&nbsp;开启</span>
                                </label>
                                <label>
                                    <input onclick="return confirm('确定 关闭 海外购功能?');" name="config_value" type="radio" class="ace" value="0" {% if conf['config_value'] == 0 %} checked {% endif%}>
                                    <span class="lbl">&nbsp;关闭</span>
                                </label>
                            </div>

                        </div>

                        <div class="space-4"></div>


                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <button id="addAd" onclick="ajaxSubmit('addAdForm'); return false;" class="btn btn-info" type="button">
                                        <i class="ace-icon fa fa-check bigger-110"></i>
                                        保存
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