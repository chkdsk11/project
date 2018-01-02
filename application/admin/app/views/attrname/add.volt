{% extends "layout.volt" %}

{% block content %}
<div class="main-container" id="main-container">
    <div class="main-content">
        <div class="main-content-inner">
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        添加商品属性
                    </h1>
                </div><!-- /.page-header -->
                <div class="">
                    <p>
                        基本信息
                    </p>
                </div><!-- /.basic -->
                <form id="form" action="/attrname/add" method="post">
                    <input type="hidden" name="category_id" value="{{ category_id }}">
                    <input type="hidden" name="attrValueJson">
                    <div class="row">
                        <div class="col-xs-12">
                            <!-- PAGE CONTENT BEGINS -->
                            <div class="form-horizontal">
                                <div class="form-group" style="margin-left: 180px;">
                                    <label class="col-sm-2 control-label no-padding-right"> 属性名称： </label>

                                    <div class="col-sm-10">
                                        <input type="text" name="attr_name" class="col-xs-10 col-sm-5" />
                                    </div>
                                </div>
                                <div class="add_sku_attr">
                                    <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                                        <thead>
                                        <tr>
                                            <th>属性值</th>
                                            <th>操作&nbsp;&nbsp;&nbsp;<button class="btn btn-xs btn-primary" type="button" id="add">添加</button></th>
                                        </tr>
                                        </thead>
                                        <tbody id="attr_table">
                                        </tbody>
                                    </table>
                                </div>
                                <div class="clearfix form-actions">
                                    <div class="col-md-offset-3 col-md-9 add_sku_submit">
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
<script src="http://{{ config.domain.static }}/assets/admin/js/attrname/globalAttrName.js"></script>
{% endblock %}