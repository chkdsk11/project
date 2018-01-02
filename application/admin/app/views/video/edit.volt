{% extends "layout.volt" %}

{% block content %}
<div class="main-container" id="main-container">
    <div class="main-content">
        <div class="main-content-inner">
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        编辑视频
                    </h1>
                </div><!-- /.page-header -->
                    <div class="">
                        <p>
                            基本信息
                        </p>
                    </div><!-- /.basic -->
                    <form id="form" action="/video/edit" method="post">
                    <div class="row">
                    <div class="col-xs-12">
                        <!-- PAGE CONTENT BEGINS -->
                        <div class="form-horizontal">
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> 视频ID： </label>

                                <div class="col-sm-9">
                                    <input type="text" name="video_id" class="col-xs-10 col-xs-2" value="{{ info['video_id'] }}" readonly />
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> 视频名称： </label>

                                <div class="col-sm-9">
                                    <input type="text" name="video_name" class="col-xs-10 col-sm-5" value="{{ info['video_name'] }}"/>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> 视频简介： </label>

                                <div class="col-sm-9">
                                    <textarea name="video_desc" class="col-xs-10 col-sm-5">{{ info['video_desc'] }}</textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> 标签： </label>

                                <div class="col-sm-9">
                                    <input type="text" name="tag" class="col-xs-10 col-sm-5" value="{{ info['tag'] }}"/><span class="tigs">（多个标签用空格隔开）</span>
                                </div>
                            </div>

                            <div class="space-4"></div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> 是否可以下载： </label>
                                <div class="col-xs-12 col-sm-9">
                                    <label class="radio-inline">
                                        <input type="radio" name="isdownload" value="1"  {% if info['isdownload'] == 1 %}checked{% endif %}>是
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" name="isdownload" value="0" {% if info['isdownload'] == 0 %}checked{% endif %}>否
                                    </label>
                                </div>
                            </div>

                            <div class="space-4"></div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> 是否收费： </label>
                                <div class="col-xs-12 col-sm-9">
                                    <label class="radio-inline">
                                        <input type="radio" name="is_pay" value="1" {% if info['is_pay'] == 1 %}checked{% endif %}>是
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" name="is_pay" value="0" {% if info['is_pay'] == 0 %}checked{% endif %}>否
                                    </label>
                                </div>
                            </div>

                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <input type="hidden" name="id" value="{{ info['id'] }}" />
                                    <button id="addFullMinues" class="btn btn-info" type="submit">
                                        <i class="ace-icon fa fa-check bigger-110"></i>
                                        保存
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
<script src="http://{{ config.domain.static }}/assets/admin/js/video/globalvideo.js"></script>
{% endblock %}