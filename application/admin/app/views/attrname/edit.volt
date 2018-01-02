{% extends "layout.volt" %}

{% block content %}
<div class="main-container" id="main-container">
    <div class="main-content">
        <div class="main-content-inner">
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        编辑商品属性
                    </h1>
                </div><!-- /.page-header -->
                <div class="">
                    <p>
                        基本信息
                    </p>
                </div><!-- /.basic -->
                <form id="form" action="/attrname/edit" method="post">
                    <input type="hidden" name="category_id" value="{{ category_id }}">
                    <input type="hidden" name="id" value="{{ info['id'] }}">
                    <input type="hidden" name="attrValueJson">
                    <div class="row">
                        <div class="col-xs-12">
                            <!-- PAGE CONTENT BEGINS -->
                            <div class="form-horizontal">
                                <div class="form-group" style="margin-left: 180px;">
                                    <label class="col-sm-2 control-label no-padding-right"> 属性名称： </label>

                                    <div class="col-sm-10">
                                        <input type="text" name="attr_name" value="{{ info['attr_name'] }}" class="col-xs-10 col-sm-5" />
                                    </div>
                                </div>
                                <div style="width: 300px; margin: 0 auto;">
                                    <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                                        <thead>
                                        <tr>
                                            <th>属性值</th>
                                            <th>操作&nbsp;&nbsp;&nbsp;<button class="btn btn-xs btn-primary" type="button" id="add">添加</button></th>
                                        </tr>
                                        </thead>
                                        <tbody id="attr_table">
                                            {% if valueData != '' %}
                                            {% for v in valueData %}
                                            <tr>
                                                <td><input type="text" name="attr_value" class="attr_value" data-id="{{ v['id'] }}" value="{{ v['attr_value'] }}"></td>
                                                <td><button class="btn btn-xs btn-danger delvalue" type="button">删除</button></td>
                                            </tr>
                                            {% endfor %}
                                            {% endif %}
                                        </tbody>
                                    </table>
                                </div>
                                <div class="clearfix form-actions">
                                    <div class="col-md-offset-3 col-md-9 add_sku_submit">
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
<script src="http://{{ config.domain.static }}/assets/admin/js/attrname/globalAttrName.js"></script>
{% endblock %}