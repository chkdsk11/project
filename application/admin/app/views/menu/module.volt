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
    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="form-field-1">功能模块名称</label>

        <div class="col-sm-9">
            <input type="text" name="menu_title" id="form-field-1" placeholder="功能模块名称" class="col-xs-10 col-sm-5" value="" />
            <input type="hidden" value="1" name="menu_level" />
        </div>
    </div>

    <div class="col-md-offset-3 col-md-9">
        <input class="btn btn-lg btn-yellow" type="submit" value="提交" name="submit" />
    </div>
</form>
{% endblock %}
{% block footer %}

{% endblock %}
