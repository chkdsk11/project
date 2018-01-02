{% extends "layout.volt" %}
{% block content %}
<form class="form-horizontal" role="form" action="" method="post">
	<div class="form-group">
       <label class="col-sm-3 control-label no-padding-right" for="name">id</label>
        <div class="col-xs-12 col-sm-5">
            <span class="block input-icon input-icon-right">
                <input type="text" class="width-100" value="{{ data['role_id'] }}" readonly="true" />
                <input type="hidden" name="role_id" value="{{ data['role_id'] }}" />
            </span>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="role_name">角色名称</label>
        <div class="col-sm-5">
            <input type="text" name="role_name" id="role_name" placeholder="角色名称" class="col-xs-10 col-sm-12" value="{{ data['role_name'] }}" />
        </div>
        <div class="help-block col-xs-12 col-sm-3"> 
			
		</div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="is_enable">状态</label>
        <div class="col-sm-5">
            <select class="form-control" id="is_enable" name="is_enable">
                <option value="1" {% if data['is_enable'] == 1 %}selected{% endif %}>启用</option>
                <option value="0" {% if data['is_enable'] == 0 %}selected{% endif %}>禁用</option>
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
        	var role_name = $('#role_name').val();
        	if(!role_name){
        		layer.tips('角色名称不能为空！', '#role_name');
        		$('#role_name').focus();
        		return false;
        	}
        	//return false;
	        $(this).ajaxSubmit({
	            type: 'post', // 提交方式 get/post
	            url: '', // 需要提交的 url
	            data: {
	            	'role_name' : role_name
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
