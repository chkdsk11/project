{% extends "layout.volt" %}

{% block content %}
<style>
    .form-group {
        margin-top:10px;
    }
</style>
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/admin/css/subject/special.css"/>

<!--放页面内容-->
<div class="page-content">
    <div class="page-header">
        <h1>PC端组件编辑</h1>
    </div>


    <div class="row">
        <form action="" class="form-horizontal" action="/subjectpc/editwidget" method="post" enctype="multipart/form-data" id='widget_mobile_edit'>
            <div class="col-xs-4  widget-code">
                <div>
                    <label >HTML代码：</label>

                    <textarea class="form-control" id="html_value" placeholder="HTML代码" name="html_value">{% if component['html_value'] is defined %}{{component['html_value']}}{% endif %}</textarea>
                </div>
                <div>
                    <label >CSS代码：</label>

                    <textarea class="form-control" id="css_value" placeholder="CSS代码" name="css_value">{% if component['css_value'] is defined %}{{component['css_value']}}{% endif %}</textarea>
                </div>
                <div>
                    <label >JavaScript代码：</label>

                    <textarea class="form-control" id="javascript_value" placeholder="JavaScript代码" name="javascript_value">{% if component['javascript_value'] is defined %}{{component['javascript_value']}}{% endif %}</textarea>
                </div>

            </div>


            <div class="col-xs-6 col-xs-offset-1 widget-param">

                <div class="widget-box">
                    <div class="widget-header">
                        <h4 class="widget-title">动态编辑字段</h4>

                        <div class="widget-toolbar">
                            <a href="javascript:;" title="添加新字段" id="btn-add-param">
                                <i class="ui-icon ace-icon fa fa-plus center bigger-110 blue"></i>
                            </a>
                        </div>
                    </div>

                    <div class="widget-body">
                        <div class="widget-main" id="widget-params">

                            {% if field is defined and field != 0 %}
                            {% for k,v in field %}
                            <div class="form-group js-param">
                                <label class="col-sm-2 control-label no-padding-right" ><span class="red">*</span>字段名：</label>
                                <div class="col-sm-2">
                                    <input type="text"  placeholder="width" class="col-sm-12" name="field_name[{{ k }}]" value="{{ v['field_name'] }}">
                                </div>
                                <label class="col-sm-1 control-label no-padding-right" ><span class="red">*</span>描述：</label>
                                <div class="col-sm-2">
                                    <input type="text"  placeholder="图片宽度" class="col-sm-12" name="field_label[{{ k }}]" value="{{ v['field_label'] }}">
                                </div>
                                <label class="col-sm-1 control-label no-padding-right" ><span class="red">*</span>类型：</label>
                                <div class="col-sm-2">
                                    <select class="form-control js-selector" name="field_type[{{ k }}]">
                                        <option value="1" {% if v['field_type'] is defined AND v['field_type'] == 1 %}selected{% endif %}>文本</option>
                                        <option value="2" {% if v['field_type'] is defined AND v['field_type'] == 2 %}selected{% endif %}>文件</option>
                                        <option value="3" {% if v['field_type'] is defined AND v['field_type'] == 3 %}selected{% endif %}>下拉菜单</option>
                                        <option value="4" {% if v['field_type'] is defined AND v['field_type'] == 4 %}selected{% endif %}>复选框</option>
                                        <option value="5" {% if v['field_type'] is defined AND v['field_type'] == 5 %}selected{% endif %}>文本域</option>
                                    </select>
                                </div>
                                <label class="col-sm-1 control-label" >
                                    <a href="javascript:;" class="js-del-param">
                                        <i class="red ace-icon fa fa-trash-o bigger-130"></i>
                                    </a>
                                </label>
                                <div class="col-sm-12 select-options js-select-options" style="display: none">
                                    <label class="col-sm-2 control-label no-padding-right" ><span class="red">*</span>下拉选项：</label>
                                    <div class="col-sm-8">
                                        <textarea class="form-control" placeholder="下拉菜单选项，多个选项用英文逗号分隔" name="select_value[{{ k }}]">{{ v['select_value'] }}</textarea>
                                    </div>
                                </div>
                            </div>
                            {% endfor %}
                            {% else %}
                            <div class="form-group js-param">
                                <label class="col-sm-2 control-label no-padding-right" ><span class="red">*</span>字段名：</label>
                                <div class="col-sm-2">
                                    <input type="text"  placeholder="width" class="col-sm-12" name="field_name[]">
                                </div>
                                <label class="col-sm-1 control-label no-padding-right" ><span class="red">*</span>描述：</label>
                                <div class="col-sm-2">
                                    <input type="text"  placeholder="图片宽度" class="col-sm-12" name="field_label[]">
                                </div>
                                <label class="col-sm-1 control-label no-padding-right" ><span class="red">*</span>类型：</label>
                                <div class="col-sm-2">
                                    <select class="form-control js-selector" name="field_type[]">
                                        <option value="1">文本</option>
                                        <option value="2">文件</option>
                                        <option value="3">下拉菜单</option>
                                        <option value="4">复选框</option>
                                        <option value="5">文本域</option>
                                    </select>
                                </div>
                                <label class="col-sm-1 control-label" >
                                    <a href="javascript:;" class="js-del-param">
                                        <i class="red ace-icon fa fa-trash-o bigger-130"></i>
                                    </a>
                                </label>
                                <div class="col-sm-12 select-options js-select-options" style="display: none">
                                    <label class="col-sm-2 control-label no-padding-right" ><span class="red">*</span>下拉选项：</label>
                                    <div class="col-sm-8">
                                        <textarea class="form-control" placeholder="下拉菜单选项，多个选项用英文逗号分隔" name="select_value[]"></textarea>
                                    </div>
                                </div>
                            </div>
                            {% endif %}

                        </div>
                    </div>
                </div>
                <div class="form-horizontal">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">组件名称：</label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" placeholder="商品组" name="component_name" value="{% if component['component_name'] is defined %}{{component['component_name']}}{% endif %}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label  class="col-sm-2 control-label">是否启用：</label>
                        <div class="checkbox col-sm-6">
                            <label>
                                <input type="checkbox" {% if component['status'] is defined and component['status'] != 1  %} {% else %}checked{% endif %}  value="1" name="status">
                            </label>
                        </div>
                    </div>
                </div>

            </div>

            <input type="hidden" name="id" value="{% if field_ids is defined %}{{ id }}{% endif %}">
            {% if field_ids is defined %}
            <input type="hidden" name="field_ids" value="{{ field_ids }}">
            {% endif %}
            <input type="hidden" name="channel" value="95" placeholder="渠道号：91:移动端 95:PC">
            <input type="hidden" name="url" value="/subjectpc/listwidget" placeholder="提交后跳转地址">
            <div class="col-xs-12 add-widget-act">
                <button class="btn btn-info" type="button" id="do_ajax_add">
                    <i class="ace-icon fa fa-check bigger-110"></i>
                    确认
                </button>
            </div>

        </form>
    </div>



    <!-- /.page-header -->
</div>


<!-- /.page-header -->
{% endblock %}

{% block footer %}
<script src="http://{{ config.domain.static }}/assets/js/ajaxfileupload.js"></script>
<script src="http://{{ config.domain.static }}/assets/admin/js/subject/widget-edit.js"></script>
<script type="text/javascript">
   //提交添加
    $("#do_ajax_add").on("click",function () {
        if($("#html_value").val()==""){
            layer_required("请添加HTML");
            return false;
        }
        ajaxSubmit("widget_mobile_edit");
    });
</script>
{% endblock %}