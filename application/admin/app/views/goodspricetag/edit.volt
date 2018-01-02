{% extends "layout.volt" %}

{% block content %}
<div class="main-container" id="main-container">
    <div class="main-content">
        <div class="main-content-inner">
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        编辑会员标签
                    </h1>
                </div><!-- /.page-header -->
                    <div class="">
                        <p>
                            基本信息
                        </p>
                    </div><!-- /.basic -->
                    <form id="form" action="/goodspricetag/edit" method="post">
                    <div class="row">
                    <div class="col-xs-12">
                        <!-- PAGE CONTENT BEGINS -->
                        <div class="form-horizontal">
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> 会员标签名称： </label>

                                <div class="col-sm-9">
                                    <input type="text" id="tag_name" name="tag_name" class="col-xs-10 col-sm-5" placeholder="不可为空" value="{{ info['tag_name'] }}" />
                                </div>
                            </div>
                            <div class="space-4"></div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> 是否启用： </label>
                                <div class="col-xs-12 col-sm-9">
                                    <label class="radio-inline">
                                        <input type="radio" name="status" value="1"  {% if info['status'] == 1 %}checked{% endif %}>是
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" name="status" value="0" {% if info['status'] == 0 %}checked{% endif %}>否
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> 标签备注： </label>

                                <div class="col-sm-9">
                                    <input type="text" id="remark" name="remark" class="col-xs-10 col-sm-5" placeholder="不可为空" value="{{ info['remark'] }}" />
                                </div>
                            </div>
                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <button id="addFullMinues" class="btn btn-info" type="submit">
                                        <i class="ace-icon fa fa-check bigger-110"></i>
                                        保存
                                        <input type="hidden" name="tag_id" value="{{ info['tag_id'] }}" />
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
<script src="http://{{ config.domain.static }}/assets/admin/js/goodspricetag/globalGoodsPriceTag.js"></script>
{% endblock %}