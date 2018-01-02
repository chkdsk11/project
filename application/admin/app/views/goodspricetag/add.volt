{% extends "layout.volt" %}

{% block content %}
<div class="main-container" id="main-container">
    <div class="main-content">
        <div class="main-content-inner">
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        添加会员标签
                    </h1>
                </div><!-- /.page-header -->
                    <div class="">
                        <p>
                            基本信息
                        </p>
                    </div><!-- /.basic -->
                    <form id="form" action="/goodspricetag/add" method="post">
                    <div class="row">
                    <div class="col-xs-12">
                        <!-- PAGE CONTENT BEGINS -->
                        <div class="form-horizontal">
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> 会员标签名称： </label>

                                <div class="col-sm-9">
                                    <input type="text" id="tag_name" name="tag_name" class="col-xs-10 col-sm-5" placeholder="不可为空" />
                                </div>
                            </div>
                            <div class="space-4"></div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> 是否启用： </label>
                                <div class="col-xs-12 col-sm-9">
                                    <label class="radio-inline">
                                        <input type="radio" name="status" value="1" checked>是
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" name="status" value="0" >否
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> 标签备注： </label>

                                <div class="col-sm-9">
                                    <input type="text" id="remark" name="remark" class="col-xs-10 col-sm-5" placeholder="不可为空" />
                                </div>
                            </div>
                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <button id="addFullMinues" class="btn btn-info" type="submit">
                                        <i class="ace-icon fa fa-check bigger-110"></i>
                                        确认添加
                                    </button>
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