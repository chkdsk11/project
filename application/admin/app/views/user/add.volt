{% extends "layout.volt" %}

{% block content %}
<div class="page-header">

</div>

<form id="addForm" class="form-horizontal" id="addform" role="form" action="" method="post">
    <input type="hidden" name="type" value="2"/>
    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="form-field-1">用户</label>

        <div class="col-sm-9">
            <input type="text" name="username" autocomplete="nope" id="form-field-1" placeholder="用户名称" class="col-xs-10 col-sm-5" value="" required/>
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="form-field-2">密码</label>
        <div class="col-sm-9">
            <input type="password" name="password" autocomplete="nope" id="form-field-2" class="col-xs-10 col-sm-5" value="" required/>
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="form-field-1">角色</label>
        <div class="col-sm-9">
            <select name="role_id" class="col-xs-10 col-sm-5" id="form-field-select-1">
                <option value="">--请选择角色--</option>
                {% if role is defined and role is not empty %}
                {% for v in role %}
                <option value="{{ v['role_id'] }}">{{ v['role_name'] }}</option>
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
                    <input name="is_lock" value="1" class="ace" type="radio" />
                    <span class="lbl">是</span>
                </label>
                <label>
                    <input name="is_lock" value="0" class="ace" type="radio" checked/>
                    <span class="lbl">否</span>
                </label>
            </div>
        </div>
    </div>

    <div class="col-md-offset-3 col-md-9">
        <input class="btn btn-lg btn-yellow" type="button" id='saveBtn' value="提交" name="submit" />
    </div>
</form>

{% endblock %}
{% block footer %}
<script src="http://{{ config.domain.static }}/assets/js/bootstrap.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/jquery.validate.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/messages_zh.min.js"></script>

<script type="text/javascript">
    //用户名验证
    $("input[name='username']").on('blur',function(){
        var username=$(this).val();
        if(username!=''){
            $.post('/user/add',{username:username,type:1},function (data) {
                if(data.status){
                    layer_error("用户名重复");
                }
            });
        }
    });
    $('#saveBtn').on('click',function () {
       ajaxSubmit('addForm');
    });
    //表单验证
    $(document).ready(function(){
        $('#addform').validate();
    });
</script>
{% endblock %}
