{% extends "layout.volt" %}

{% block content %}
<style>
    .form-group {
        margin-top:10px;
    }
</style>
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/admin/css/subject/jquery-ui.css"/>
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/admin/css/subject/special.css"/>

<div class="page-content">
    <div class="page-header">
        <h1>编辑PC端专题</h1>
    </div>
    <div class="row">
        <div class="special-box review col-xs-12">
            <h3 class="title">预览</h3>
            <div class="review-content" id="special-review"></div>
        </div>
        <div class="special-box widget col-xs-2">
            <h3 class="title">组件</h3>
            <ul class="widget-list" id="widget-list">
                <script type="text/html">
                    <% if(widget && widget.length){ %>
                    <% widget.forEach(function(item, i) { %>
                    <li class="widget-item" data-index="<%= i %>"><%= item.component_name %></li>
                    <% });%>
                    <% } %>
                </script>
            </ul>
        </div>
        <div class="special-box param col-xs-5 col-xs-offset-1" id="current-widget">
            <script type="text/html">
                <% if(config){ %>
                <h3 class="title">属性</h3>
                <form class="form-horizontal">
                    <% if(widget.field && widget.field.length){ %>
                    <% widget.field.forEach(function(item) { %>
                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-right" ><%= item.field_label %></label>
                        <div class="col-sm-9">
                            <% if(item.field_type == 1){ %>
                            <input type="text" name="<%= item.field_name %>" value="<%= config[item.field_name] %>" placeholder="Username">
                            <% } else if(item.field_type == 2){ %>
                            <input type="file" name="<%= item.field_name %>" placeholder="Username">
                            <% if(config[item.field_name]){ %>
                            <span class="img-upload-review" style="background-image: url(<%= config[item.field_name] %>)"></span>
                            <% } %>
                            <% } else if(item.field_type == 3 && item.select_value){ %>
                            <% var options = item.select_value.split(','); %>
                            <select  name="<%= item.field_name %>">
                                <option value="">请选择</option>
                                <% options.forEach(function(option) { %>
                                <option <% if(config[item.field_name]==option){ %>selected<% } %> value="<%= option %>"><%= option %></option>
                                <% }) %>
                            </select>
                            <% } else if(item.field_type == 4){ %>
                            <input type="checkbox" value="1" name="<%= item.field_name %>" <% if(config[item.field_name]==1){ %>checked<% } %> placeholder="Username">
                            <% } else if(item.field_type == 5){ %>
                            <textarea name="<%= item.field_name %>" class="col-sm-12" rows="5"><%= config[item.field_name] %></textarea>
                            <% } %>
                        </div>
                    </div>
                    <% }); %>
                    <% } %>
                    <div class="form-group text-center">
                        <button class="btn btn-info" type="button" id="set-config">确定</button>
                    </div>
                </form>
                <% } %>
            </script>
        </div>
    </div>
	
    <input type="hidden" name="url" value="/subjectpc/list" placeholder="提交后跳转地址">
	<input type="hidden" name="id" value="{% if id is defined  %}{{ id }}{% endif %}" placeholder="专题id">
    <div class="clearfix form-actions">
        <div class="col-md-offset-3 col-md-9">
            <button class="btn btn-info" type="button" id="save-special">
                <i class="ace-icon fa fa-check bigger-110"></i>
                保存
            </button>
            &nbsp; &nbsp; &nbsp;
            <button class="btn btn-info" type="button" id="review-special">
                <i class="ace-icon fa fa-undo bigger-110"></i>
                预览
            </button>
        </div>
    </div>
</div>


<!-- /.page-header -->
{% endblock %}

{% block footer %}
<script src="http://{{ config.domain.static }}/assets/admin/js/subject/jquery-ui.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/ajaxfileupload.js"></script>
<script src="http://{{ config.domain.static }}/assets/admin/js/subject/template.js"></script>
<script src="http://{{ config.domain.static }}/assets/admin/js/subject/less.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/admin/js/subject/babel.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/admin/js/subject/special-edit.js"></script>

{% endblock %}