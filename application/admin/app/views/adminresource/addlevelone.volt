{% extends "layout.volt" %}
{% block content %}
<form class="form-horizontal" role="form" action="" method="post">
    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="name">资源模块名称</label>
        <div class="col-sm-5">
            <input type="text" name="name" id="name" placeholder="资源模块名称" class="col-xs-10 col-sm-12" value="" />
            <input type="hidden" value="1" name="level" />
        </div>
        <div class="help-block col-xs-12 col-sm-3"> 
			
		</div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="nav_icon">导航图标</label>
        <div class="col-sm-5">
            <input type="text" name="nav_icon" id="nav_icon" placeholder="导航图标" class="col-xs-10 col-sm-12" value="" />
        </div>
        <div class="help-block col-xs-12 col-sm-4"> 
			选填，PC默认：icon-flatscreen,统一后台默认：icon-pro
		</div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="路由链接">路由链接</label>
        <div class="col-sm-5">
            <input type="text" name="route" id="route" placeholder="路由链接" class="col-xs-10 col-sm-12" value="" />
        </div>
        <div class="help-block col-xs-12 col-sm-4"> 
            默认为空
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="show_nav">导航显示</label>
        <div class="col-sm-5">
            <select class="form-control" id="show_nav" name="show_nav">
                <option value="1">显示</option>
                <option value="0">不显示</option>
			</select>
        </div>
        <div class="help-block col-xs-12 col-sm-4"> 
			
		</div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="target">打开方式</label>
        <div class="col-sm-5">
            <select class="form-control" id="target" name="target">
                <option value="_self">本窗口</option>
                <option value="_blank">新开窗口</option>
			</select>
        </div>
        <div class="help-block col-xs-12 col-sm-4"> 
			
		</div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="show_order">排序</label>
        <div class="col-sm-5">
            <input type="text" name="show_order" id="show_order" placeholder="请输入数字" class="col-xs-10 col-sm-12" value="0" />
        </div>
        <div class="help-block col-xs-12 col-sm-4"> 
			请输入数字
		</div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="sign">标识</label>
        <div class="col-sm-5">
            <input type="text" name="sign" id="sign" placeholder="请输入标识" class="col-xs-10 col-sm-12" value="" />
        </div>
        <div class="help-block col-xs-12 col-sm-4"> 
            特别模块标识，如海外购，菜单样式都与其他的不一样
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="site">所属站点</label>
        <div class="col-sm-5">
            <select class="form-control" id="site" name="site">
                <option value="0" {% if site == 0 %} selected {% endif %}>统一后台</option>
                <option value="1" {% if site == 1 %} selected {% endif %}>PC后台</option>
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
        	var name = $('#name').val();
        	if(!name){
        		layer.tips('名称不能为空！', '#name');
        		$('#name').focus();
        		return false;
        	}
        	//return false;
	        $(this).ajaxSubmit({
	            type: 'post', // 提交方式 get/post
	            url: '', // 需要提交的 url
	            data: {
	            	'name' : name
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
