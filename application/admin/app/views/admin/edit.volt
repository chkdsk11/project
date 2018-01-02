{% extends "layout.volt" %}
{% block content %}
<form class="form-horizontal" role="form" action="" method="post">
    <div class="form-group">
       <label class="col-sm-3 control-label no-padding-right" for="name">id</label>
        <div class="col-xs-12 col-sm-5">
            <span class="block input-icon input-icon-right">
                <input type="text" class="width-100" value="{{ data['id'] }}" readonly="true" />
                <input type="hidden" name="id" value="{{ data['id'] }}" />
            </span>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="role_name">管理员名称</label>
        <div class="col-sm-5">
            <input type="text" name="admin_account" id="admin_account" placeholder="管理员名称" class="col-xs-10 col-sm-12" value="{{ data['admin_account'] }}" />
        </div>
        <div class="help-block col-xs-12 col-sm-3"> 
			
		</div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="role_id">所属角色</label>
        <div class="col-sm-5">
            <select class="form-control" id="role_id" name="role_id">
            	<!-- 不显示超级管理员 -->
               	<option value="">--请选择角色--</option>
                {% if roleList is defined and roleList is not empty %}
	                {% for v in roleList %}
	                	{% if v['is_super'] == 0 %}
	                		<option value="{{ v['role_id'] }}" {% if v['role_id'] == data['role_id'] %}selected{% endif %}>{{ v['role_name'] }}</option>
	                	{% endif %}
	                {% endfor %}
                {% endif %}
			</select>
        </div>
        <div class="help-block col-xs-12 col-sm-4"> 
			
		</div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="is_lock">是否锁定</label>
        <div class="col-sm-5">
            <select class="form-control" id="is_lock" name="is_lock">
                <option value="0" {% if data['is_lock'] == 0 %}selected{% endif %}>否</option>
                <option value="1" {% if data['is_lock'] == 1 %}selected{% endif %}>是</option>
			</select>
        </div>
        <div class="help-block col-xs-12 col-sm-4"> 
			
		</div>
    </div>
    <div class="col-md-offset-3 col-md-9">
        <input class="btn btn-lg btn-yellow" type="submit" value="提交" name="submit" />
    </div>
</form>
{% endblock %}
{% block footer %}
	<script src="http://{{ config.domain.static }}/assets/js/layer/layer.js"></script>
	<script src="http://{{ config.domain.static }}/assets/js/jquery.form.js"></script>
	<script>
		$('.form-horizontal').on('submit', function() {
        	var admin_account = $('#admin_account').val();
        	if(!admin_account){
        		layer.tips('管理员名称不能为空！', '#admin_account');
        		$('#admin_account').focus();
        		return false;
        	}
        	//return false;
	        $(this).ajaxSubmit({
	            type: 'post', // 提交方式 get/post
	            url: '', // 需要提交的 url
	            data: {
	            },
	            success: function(data) { 
	            	// data 保存提交后返回的数据，一般为 json 数据
	                // 此处可对 data 作相关处理
	                if(data.code == 200){
	                	//history.back();
                        location.href = data.data;
	                }else{
	                	layer.tips(data.data.message, '#'+data.data.field);
	                }
	            }
	        });
	       	// 阻止表单自动提交事件
	        return false; 
	    });
	</script>
{% endblock %}
