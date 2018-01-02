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
            <input type="text" class="width-100" value="{{ data['admin_account'] }}" readonly="true" />
             <input type="hidden" class="width-100" name="admin_account" value="{{ data['admin_account'] }}" readonly="true" />
        </div>
        <div class="help-block col-xs-12 col-sm-3"> 
			
		</div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="admin_password">管理员密码</label>
        <div class="col-sm-5">
            <input type="password" name="admin_password" id="admin_password" placeholder="管理员密码" class="col-xs-10 col-sm-12" value="" />
        </div>
        <div class="help-block col-xs-12 col-sm-3"> 
            
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
        	var admin_password = $('#admin_password').val();
            if(!admin_password){
                layer.tips('管理员密码不能为空！', '#admin_password');
                $('#admin_password').focus();
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
