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
        <label class="col-sm-3 control-label no-padding-right" for="form-field-1">{{ (menus['menu_level']==2)?'控制器':'功能模块名称' }}</label>

        <div class="col-sm-9">
            <input type="text" name="menu_title" id="form-field-1" placeholder="功能模块名称" class="col-xs-10 col-sm-5" value="" />
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="form-field-2">权限路径</label>

        <div class="col-sm-9">
            <input type="text" name="menu_path" id="form-field-2" placeholder="权限路径" class="col-xs-10 col-sm-5" value="" />
        </div>
        <input type="hidden" value="{{ menus['menu_level'] }}" name="menu_level"  />
        <input type="hidden" value="{{ menus['parent_id'] }}" name="parent_id"  />
    </div>
    {% if menus['menu_level'] is defined and menus['menu_level']>2 %}
    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="form-field-1">左边菜单</label>
        <div class="col-sm-9">
            <div class="radio">
                <label>
                    <input name="is_show_left" value="1" class="ace" type="radio" />
                    <span class="lbl">是</span>
                </label>
                <label>
                    <input name="is_show_left" value="0" class="ace" type="radio" checked/>
                    <span class="lbl">否</span>
                </label>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="form-field-1">顶部菜单</label>
        <div class="col-sm-9">
            <div class="radio">
                <label>
                    <input name="is_show_top" value="1" class="ace" type="radio" />
                    <span class="lbl">是</span>
                </label>
                <label>
                    <input name="is_show_top" value="0" class="ace" type="radio" checked/>
                    <span class="lbl">否</span>
                </label>
            </div>
        </div>
    </div>
    {% endif %}
    <div class="col-md-offset-3 col-md-9">
        <input class="btn btn-lg btn-yellow" type="submit" value="提交" name="submit" />
    </div>
    {% endif %}
</form>
{% endblock %}
{% block footer %}

{% endblock %}
