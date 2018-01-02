{% extends "layout.volt" %}
{% block content %}
<div class="page-header">
    <h1>
        功能权限
        <small>
            <i class="ace-icon fa fa-angle-double-right"></i>
            {{ title }}
        </small>
    </h1>
</div>
<form class="form-horizontal" role="form" action="" method="post">
    {% if menus is defined and menus is not empty %}
    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="form-field-1">功能名称</label>

        <div class="col-sm-9">
            <input type="text" name="menu_title" id="form-field-1" placeholder="功能模块名称" class="col-xs-10 col-sm-5" value="{{ menus['menu_title'] }}" />
            <input type="hidden" name="menu_level" value="{{ menus['menu_level'] }}" />
            <input type="hidden" value="{{ menus['id'] }}" name="id" />
            <input type="hidden" value="{{ menus['has_child'] }}" name="has_child" />
            <input type="hidden" name="parent_id" value="0" />
            <input type="hidden" name="menu_path" value="" />
        </div>
    </div>

    <div class="col-md-offset-3 col-md-9">
        <input class="btn btn-lg btn-yellow" type="submit" value="提交" name="submit" />
        &nbsp; &nbsp; &nbsp;
        <a href="javascript:void(0)" onclick="javascript:history.go(-1);return false;">
            <button class="btn btn-lg" type="button">
                返回
            </button>
        </a>
    </div>
    {% endif %}
</form>
{% endblock %}
{% block footer %}

{% endblock %}
