{% extends "layout.volt" %}

{% block content %}
<div class="page-header">

</div>

<form id='editForm' class="form-horizontal" role="form" action="" method="post">
    <input type="hidden" name="type" value="2"/>
    {% if user is defined and user is not empty %}
        {% if isCanModifyUser == 0 %}
    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="form-field-1">用户</label>

        <div class="col-sm-9">
            <input type="text" name="username" autocomplete="nope" id="form-field-1" placeholder="用户名称" class="col-xs-10 col-sm-5" value="{{ user['admin_account'] }}" />
            <input type="hidden" name="id" value="{{ user['id'] }}" />
        </div>
    </div>
    {% else %}
        <input type="hidden" name="username" value="{{ user['admin_account'] }}" />
        <input type="hidden" name="id" value="{{ user['id'] }}" />
    {% endif %}
    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="form-field-2">密码</label>
        <div class="col-sm-9">
            <input type="password" name="password" autocomplete="nope" id="form-field-2" class="col-xs-10 col-sm-5" value="" />
        </div>
    </div>
{% if isCanModifyUser == 0 %}
    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="form-field-1">角色</label>
        <div class="col-sm-9">
            <select name="role_id" class="col-xs-10 col-sm-5" id="form-field-select-1">
                <option value="">--请选择角色--</option>
                {% if role is defined and role is not empty %}
                {% for v in role %}
                <option value="{{ v['role_id'] }}" {{ (v['role_id']==roleuser[0]['role_id'])?'selected':'' }}>{{ v['role_name'] }}</option>
                {% endfor %}
                {% endif %}
            </select>
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="form-field-1">是否锁定</label>
        <div class="col-sm-9">
            <div class="radio">
                <label>
                    <input name="is_lock" value="1" class="ace" type="radio" {{ (user['is_lock']==1)?'checked':'' }}/>
                    <span class="lbl">是</span>
                </label>
                <label>
                    <input name="is_lock" value="0" class="ace" type="radio" {{ (user['is_lock']==0)?'checked':'' }}/>
                    <span class="lbl">否</span>
                </label>
            </div>
        </div>
    </div>
    {% else %}
        <input type="hidden" name="role_id" value="{{ roleuser[0]['role_id'] }}" />
        
        <input type="hidden" name="is_lock" value="{{ user['is_lock'] }}" />
{% endif %}
    <div class="col-md-offset-3 col-md-9">
        <input class="btn btn-lg btn-yellow" type="button" id='saveBtn' value="保存" name="submit" />
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
    <script type="text/javascript">
        $('#saveBtn').on('click',function () {
            ajaxSubmit('editForm');
        });
    </script>
{% endblock %}
