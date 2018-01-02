{% extends "layout.volt" %}
{% block content %}
<style>
.width-percent-50{
    width: 50% !important;
}
.nav-tabs>li.active>a, 
.nav-tabs>li.active>a:hover,
.nav-tabs>li.active>a:focus{
    box-shadow: none;
    border-radius: 5px 5px 0 0 !important;
    text-align: center;
}
.nav-tabs>li>a,
.nav-tabs>li>a:focus{
    text-align: center;
}
.tab-content{
	border:0;
}
.node-one{
	padding-left: 20px !important;
}
.node-two{
	padding-left: 40px !important;
}
.node-three{
	padding-left: 40px !important;
}
</style>
<form class="form-horizontal" role="form" action="" method="post">
	<input type="hidden" name="role_id" value="{{ adminRole['role_id'] }}"/>
	<ul class="nav nav-tabs" id="myTab">
		<li class="active width-percent-50">
			<a data-toggle="tab" href="#base">统一后台权限资源</a>
		</li>
		<li class="width-percent-50">
			<a data-toggle="tab" href="#pc">PC后台权限资源</a>
		</li>
	</ul><!-- /.nav-tabs -->
	<div class="tab-content">
        <div id="base" class="tab-pane fade in active">
            <div class="widget-body">
                <h5>您正在为角色：<b>{{    adminRole['role_name'] }}</b> 分配权限，项目和模块有全选和取消全选功能</h5>
                <div class="widget-main no-padding">
                    <input type="checkbox" class="ace" level="all" site="0"/>
                    <span class="lbl green"> 全选 </span>
                    {% for vo in reourceList %}
                        <table class="table table-bordered table-striped table-hover text-center">
                            <thead>
                                <tr>
                                    <th class="left node-one">
                                        <input type="checkbox" class="ace" level="one" hassoon="true" obj="node_{{ vo['id'] }}_" name="resource_id[]" value="{{ vo['id'] }}" site="0" />
                                        <span class="lbl green"> {{ vo['name'] }} </span>
                                        <label class="pull-right open-it" data-toggle="collapse" href="#collapse_node_{{ vo['id'] }}">
                                            <a>
                                                <i class="ace-icon fa fa-caret-right blue"></i>
                                                展开
                                            </a>
                                        </label>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="collapse_node_{{ vo['id'] }}" class="collapse">
                                {% if vo['son'] is defined and vo['son'] is not empty %}
                                    {% for vvo in vo['son']  %}
                                        <tr>
                                            <td class="left text-left node-two">
                                                <input type="checkbox" class="ace" level="two" hassoon="true" obj="node_{{ vo['id'] }}_{{ vvo['id'] }}_" upobj="node_{{ vo['id'] }}_" name="resource_id[]" value="{{ vvo['id'] }}" site="0" />
                                                <span class="lbl green"> {{ vvo['name'] }} </span>
                                            </td>
                                        </tr>
                                        {% if vvo['son'] is defined and vvo['son'] is not empty %}
                                            <tr>
                                                <td class="node-three">
                                                    <div class="control-group">
                                                        {% for v in vvo['son']  %}
                                                            <div class="checkbox col-xs-12 col-sm-3 text-left no-margin-top">
                                                                <label>
                                                                    <input name="resource_id[]" type="checkbox" class="ace" value="{{ v['id'] }}" obj="node_{{ vo['id'] }}_{{ vvo['id'] }}_{{ v['id'] }}" upobj="node_{{ vo['id'] }}_{{ vvo['id'] }}_" level="three" site="0" />
                                                                    <span class="lbl"> {{ v['name'] }} </span>
                                                                </label>
                                                            </div>
                                                        {% endfor %}
                                                    </div>
                                                </td>
                                            </tr>
                                        {% endif %}
                                    {% endfor %}
                                {% endif %}
                            </tbody>
                        </table>
                    {% endfor %}
                    <!-- submit -->
                    <div class="col-xs-12 col-sm-1  no-padding-left" style="margin-top:20px;">
                        <button class="btn btn-primary submit">
                            <i class="ace-icon fa fa-check bigger-110"></i>
                            提交
                        </button>
                    </div>
                    <div class="col-xs-12 col-sm-1 col-sm-offset-1" style="margin-top:20px;">
                        <button class="btn btn-info reset">
                            <i class="ace-icon glyphicon glyphicon-refresh"></i>
                            恢复
                        </button>
                    </div>
                    <div class="col-xs-12 col-sm-1 col-sm-offset-1" style="margin-top:20px;">
                        <button class="btn btn-danger empty">
                            <i class="ace-icon fa fa-undo bigger-110"></i>
                            清空
                        </button>
                    </div>
                </div><!-- /.widget-main -->
            </div><!-- /.widget-body -->
        </div>
		<div id="pc" class="tab-pane fade">
			<div class="widget-body">
                <h5>您正在为角色：<b>{{	adminRole['role_name'] }}</b> 分配权限，项目和模块有全选和取消全选功能</h5>
                <div class="widget-main no-padding">
                	<input type="checkbox" class="ace" level="all" site="1"/>
                    <span class="lbl green"> 全选 </span>
                	{% for vo in reourcePcList %}
                        <table class="table table-bordered table-striped table-hover text-center">
                        	<thead>
								<tr>
									<th class="left node-one">
										<input type="checkbox" class="ace" level="one" hassoon="true" obj="node_{{ vo['id'] }}_" name="resource_id[]" value="{{ vo['id'] }}" site="1" />
                                        <span class="lbl green"> {{ vo['name'] }} </span>
										<label class="pull-right open-it" data-toggle="collapse" href="#collapse_node_{{ vo['id'] }}">
											<a>
												<i class="ace-icon fa fa-caret-right blue"></i>
												展开
											</a>
										</label>
									</th>
								</tr>
							</thead>
                            <tbody id="collapse_node_{{ vo['id'] }}" class="collapse">
                            	{% if vo['son'] is defined and vo['son'] is not empty %}
                            		{% for vvo in vo['son']  %}
		                            	<tr>
											<td class="left text-left node-two">
												<input type="checkbox" class="ace" level="two" hassoon="true" obj="node_{{ vo['id'] }}_{{ vvo['id'] }}_" upobj="node_{{ vo['id'] }}_" name="resource_id[]" value="{{ vvo['id'] }}" site="1" />
		                                        <span class="lbl green"> {{ vvo['name'] }} </span>
											</td>
										</tr>
										{% if vvo['son'] is defined and vvo['son'] is not empty %}
											<tr>
			                                    <td class="node-three">
			                                        <div class="control-group">
			                                        	{% for v in vvo['son']  %}
			                                                <div class="checkbox col-xs-12 col-sm-3 text-left no-margin-top">
			                                                    <label>
			                                                        <input name="resource_id[]" type="checkbox" class="ace" value="{{ v['id'] }}" obj="node_{{ vo['id'] }}_{{ vvo['id'] }}_{{ v['id'] }}" upobj="node_{{ vo['id'] }}_{{ vvo['id'] }}_" level="three" site="1" />
			                                                        <span class="lbl"> {{ v['name'] }} </span>
			                                                    </label>
			                                                </div>
			                                            {% endfor %}
			                                        </div>
			                                    </td>
			                                </tr>
		                                {% endif %}
		                            {% endfor %}
                                {% endif %}
                            </tbody>
                        </table>
                	{% endfor %}
                    <!-- submit -->
                    <div class="col-xs-12 col-sm-1  no-padding-left" style="margin-top:20px;">
                        <button class="btn btn-primary submit">
                            <i class="ace-icon fa fa-check bigger-110"></i>
                            提交
                        </button>
                    </div>
                    <div class="col-xs-12 col-sm-1 col-sm-offset-1" style="margin-top:20px;">
                        <button class="btn btn-info reset">
                            <i class="ace-icon glyphicon glyphicon-refresh"></i>
                            恢复
                        </button>
                    </div>
                    <div class="col-xs-12 col-sm-1 col-sm-offset-1" style="margin-top:20px;">
                        <button class="btn btn-danger empty">
                            <i class="ace-icon fa fa-undo bigger-110"></i>
                            清空
                        </button>
                    </div>
                </div><!-- /.widget-main -->
            </div><!-- /.widget-body -->
		</div>
	<div>
</form>
{% endblock %}
{% block footer %}
	<script src="http://{{ config.domain.static }}/assets/js/layer/layer.js"></script>
	<script src="http://{{ config.domain.static }}/assets/js/jquery.form.js"></script>
	<script>
		// 打开面板
		$(".open-it").click();
        //初始化数据
        function setAccess(){
            //清空所有已选中的
            $("input[type='checkbox']").prop("checked",false);
            var access=$.parseJSON('{{ adminRole['rules'] }}');
            var access_length = access.length;
            if(access_length>0){
                for(var i=0;i<access_length;i++){
                    $("input[type='checkbox'][value='" + access[i] + "']").prop("checked","checked");
                }
            }
        }  
		$(function(){
            // 全选--start
            $("input[level='all'][site='0']").click(function(){
               	$("input[site='0']").prop("checked",$(this).prop("checked"));
            });
            $("input[level='all'][site='1']").click(function(){
               	$("input[site='1']").prop("checked",$(this).prop("checked"));
            });
            // 全选--end
            // 分组
            $("input[hassoon='true']").click(function(){
                var obj=$(this).attr("obj");
                $("input[obj^='"+obj+"']").prop("checked",$(this).prop("checked"));
                var level=$(this).attr("level");
            });
            // 监听最后一级
            $("input[level='three']").click(function(){
                var upobj=$(this).attr("upobj");
                // 二级
                var _two = $("input[obj='"+upobj+"']");
                var _two_val = _two.val();
                // 判断二级下面三级所有input的状态
                var _two_obj = _two.attr('obj');
                var _two_obj_input = $("input[obj^='"+_two_obj+"']");
                var _two_checked = false;
                _two_obj_input.each(function(i){
                	if(_two_val != $(this).val()){
	                	var checked = $(this).prop("checked");
	                	if(checked){
	                		_two_checked = true;
	                	}
                	}
                });
                _two.prop("checked",_two_checked);
                // 判断一级下面的二级状态
                var _one_obj = _two.attr('upobj');
                var _one = $("input[obj='"+_one_obj+"']");
                var _one_val = _one.val();
                var _one_obj_input = $("input[obj^='"+_one_obj+"']");
                var _one_checked = false;
                _one_obj_input.each(function(i){
                    if(_one_val != $(this).val()){
                        var _checked = $(this).prop("checked");
                        if(_checked){
                            _one_checked = true;
                        }
                    }
                });
                _one.prop("checked",_one_checked);
            });
            // 监听二级
            $("input[level='two']").click(function(){
                var upobj=$(this).attr("upobj");
                var _one = $("input[obj='"+upobj+"']");
                // 判断一级下面的二级状态
                var _one_val = _one.val();
                var _one_obj_input = $("input[obj^='"+upobj+"']");
                var _one_checked = false;
                _one_obj_input.each(function(i){
                    if(_one_val != $(this).val()){
                        var _checked = $(this).prop("checked");
                        if(_checked){
                            _one_checked = true;
                        }
                    }
                });
                _one.prop("checked",_one_checked);
            });
            //执行初始化数据操作
            setAccess();
            //重置初始状态，勾选错误时恢复
            $(".reset").click(function(){
                setAccess();
                return false;
            });
            //清空当前已经选中的
            $(".empty").click(function(){
                $("input[type='checkbox']").prop("checked",false);
                return false;
            });
        });
		$('.form-horizontal').on('submit', function() {
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
