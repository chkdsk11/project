{% extends "layout.volt" %}

{% block content %}
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/jquery.datetimepicker.css" class="ace-main-stylesheet" />
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/admin/css/coupon_addon.css" class="ace-main-stylesheet" />
<style>
    #categoryBox{
        top:0;left:0;
    }
</style>
<div class="main-container" id="main-container">
    <div class="main-content">
        <div class="main-content-inner">
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        添加推广会员
                    </h1>
                </div><!-- /.page-header -->
            </div>
            <form id="addPromotionForm"  class="form-horizontal" action="/cpsuser/add" method="post" onsubmit="ajaxSubmit(addPromotionForm);return false;">
                <div class="">
                    <h3>
                        基本信息
                    </h3>
                </div><!-- /.basic -->
                <div class="row">
                    <div class="col-xs-12">
                        <!-- PAGE CONTENT BEGINS -->
                       
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_title"> <span  class="text-red">*</span>会员渠道 </label>

                                <div class="col-sm-9">
                                    <select name="channel_id" id="channel_id">
									  <option value="">全部</option>
										{% for date in channel_lset %}
											 <option value="{{date['channel_id']}}"  > {{date['channel_name']}} </option>
										{% endfor %} 
									  </select>
                                </div>
                            </div>
                            <div class="space-4"></div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> <span  class="text-red">*</span>会员ID </label>
                                <div class="col-sm-9">
                                        <input type="text" id="user_id" name="user_id" class="col-xs-10 col-sm-5" placeholder="不可为空" />
                                </div>

                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_content"> 姓名 </label>
                                <div class="col-sm-9">
                                   <input type="text" id="user_name" name="user_name" class="col-xs-10 col-sm-5" placeholder="不可为空" />
                                    
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_copywriter"> 员工号 </label>
                                <div class="col-sm-9">
                                    <input type="text" id="employee_id" name="employee_id" class="col-xs-10 col-sm-5" placeholder="不可为空" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_copywriter"> 地区 </label>
                                <div class="col-sm-9">
                                   <select name="province" id="province" onchange="get_address(this.value)">
                            			<option value="">请选择</option>
                          					{% for date in red %}
                               					<option value="{{date['id']}}"  > {{date['region_name']}} </option>
                          					{% endfor %} 
                            		</select>&nbsp;&nbsp;
                                    <select name="city_id" id="city_id">
                        				<option value="">请选择</option>
                      					{% for date in city %}
                           					<option value="{{date['id']}}" {% if channel['city'] == date['id'] %} selected="selected" {% endif %}  > {{date['region_name']}} </option>
                      					{% endfor %} 
                        			</select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_copywriter"> 医院</label>
                                <div class="col-sm-9">
                                  <input type="text" id="hospital" name="hospital" class="col-xs-10 col-sm-5"  />
                                </div>
                            </div>
                            
							<div class="form-group" id="permanent_action">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_copywriter">科室</label>
                                <div class="col-sm-9">
                                  <input type="text" id="department" name="department" class="col-xs-10 col-sm-5"  />
                                </div>
                            </div>

                            <div>
                                <button id="add" class="btn btn-info" type="button" >
                                    添加
                                </button>
                                <span style="float:right;">
                                    <button type="button" id="import_btn" class="btn btn-sm btn-primary">批量导入</button>
                                    <a class="btn btn-sm btn-purple" href="/cpsuser/add?isTemplate=1" target="_blank">模板下载</a>
                                </span>
                            </div>
                            
                            <div id="user_list">
                                <table id="rule-table" class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th ></th>
                                            <th >编号</th>
                                            <th >会员ID</th>
                                            <th >姓名</th>
                                            <th >员工号</th>
                                            <th >省</th>
                                            <th >市</th>
                                            <th >医院</th>
                                            <th >科室</th>
                                            <th >操作</th>
                                        </tr>
                                    </thead>
                                    <tbody id="rule-tbody">

                                    </tbody>
                                </table>
                            </div><!-- /.rule_table -->
                          
                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <button id="" class="btn btn-info" type="button" onclick="add_ss()">
                                        <i class="ace-icon fa fa-check bigger-110"></i>
                                        确认添加
                                    </button>
                                    &nbsp; &nbsp; &nbsp;
                                    <!--<button class="btn" type="reset">
                                        <i class="ace-icon fa fa-undo bigger-110"></i>
                                        重置
                                    </button>-->
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div><!-- /.main-content -->
            </form>
            <form target="import_ifm" id="import_tel" method="post" action="/cpsuser/add?isImport=1" enctype="multipart/form-data">
                <input id="import_input" name="userFile" type="file" style="width: 0;height: 0;">
            </form>
            <iframe id="import_ifm" name="import_ifm" style="display:none"></iframe>
        </div>
        </div>
</div><!-- /.main-container -->

{% endblock %}

{% block footer %}
<script>
function add_ss(){
  $('#mes').html('');
		$('#message').html('');
		var channel = $("#channel_id option:selected").val();

		var tr_list = $('#user_list tbody tr');
		var len = tr_list.length;
		if (!len && channel == 0) {
		alert('会员渠道必选&&没有添加任何会员');
			
			return false;
		} else if (channel == 0) {
		alert('会员渠道必选');
			
			return false;
		} else if (!len) {
		alert('没有添加任何会员');
			
			return false;
		} 
  ajaxSubmit('addPromotionForm');
   
}    
 $('.btn-primary').on('click',function(){
 });
 
//选择添加商品
$('#add').click(function(){
    $('#message').html('');
	var user_id=$('#user_id').val();
	var user_name=$('#user_name').val();
	var user_name=user_name.replace(/\s+/g,"");
	var employee_id=$('#employee_id').val();
	
    var county = $("select[name='province']").val();
    var city = $("select[name='city_id']").val();
	
	
	
    var hospital=$('#hospital').val();
    var department=$('#department').val();

    var county_html = $("select[name='province'] option:selected").text();
    var city_html = $("select[name='city_id'] option:selected").text();

    if(county_html==='--请选择--'){county = 0;county_html = '';}
    if(city_html==='--请选择--'){city = 0;city_html = '';}

	var rel = /^1[34578]\d{9}$/;//手机号
	if (!user_id) {
	    alert('会员ID必填');
		
		return false;
	} else if (!rel.test(user_id)) {
		
		alert('会员ID格式错误（手机号）');
		return false;
	}
    var isRepeat = checkRepeat(user_id,employee_id);
    if (isRepeat == 1) {
        alert('会员ID '+user_id+' 已经存在');
        return false;
    } else if (isRepeat == 2) {
        alert('员工号 '+employee_id+' 已经存在');
        return false;
    } else if (isRepeat == 3) {
        alert('会员ID '+user_id+' 和员工号 '+employee_id+' 都已经存在');
        return false;
    }
	add_user(user_id, user_name, employee_id,county,city,hospital,department,county_html,city_html);
});

//校验是否有重复
function checkRepeat(user_id,employee_id){
    var is_id = 0;
    var is_employee = 0;
    var id = 0;
    var employee = 0;
    var tr_list = $('#user_list tbody tr');
    if (tr_list.length) {
        tr_list.each(function () {
            id = $(this).children("td:eq(2)").find(':hidden').val();
            employee = $(this).children("td:eq(4)").find(':hidden').val();
            if(id == user_id) {
                is_id = 1;
            }
            if(employee == employee_id && employee_id != '') {
                is_employee = 1;
            }
        })
    }
    if (is_id && is_employee) {
        return 3;
    } else if (is_id && !is_employee) {
        return 1;
    } else if (!is_id&& is_employee) {
        return 2;
    }
    return false;
}
	
//添加单个会员信息到列表
function add_user(user_id, user_name, employee_id, county,city,hospital,department,county_html,city_html){
    if(!county_html){
        county_html = county;
        city_html = city;
    }
    var tr_list = $('#user_list tbody tr');
    var last = 0;
    if (tr_list.length) {
        tr_list.each(function () {
            last = $(this).children("td:eq(1)").find(':hidden').val();
        })
    }
	last= parseInt(last)+1;
	var label='';
	label+='<tr>'
	label+='<td><input type="checkbox" name="ids" value="'+last+'"></td>';
	label+='<td><input type="hidden" value="'+last+'"/>'+last+'</td>';
	label+='<td><input type="hidden" name="user_id[]" value="'+user_id+'"/>'+user_id+'</td>';
	label+='<td><input type="hidden" name="user_name[]" value="'+user_name+'"/>'+user_name+'</td>';
	label+='<td><input type="hidden" name="employee_id[]" value="'+employee_id+'"/>'+employee_id+'</td>';
	label+='<td><input type="hidden" name="county[]" value="'+county+'"/><input type="hidden" name="county_html[]" value="'+county_html+'"/>'+county_html+'</td>';
	label+='<td><input type="hidden" name="city[]" value="'+city+'"/><input type="hidden" name="city_html[]" value="'+city_html+'"/>'+city_html+'</td>';
	label+='<td><input type="hidden" name="hospital[]" value="'+hospital+'"/>'+hospital+'</td>';
	label+='<td><input type="hidden" name="department[]" value="'+department+'"/>'+department+'</td>';
	label+='<td><a href="javascript:void(0)" class="btn btn-delete">删除</a></td>';
	label+='</tr>'
	$('#rule-tbody').append(label);
}
//删除元素
$('#user_list').on("click", ".btn-delete", function(event){
    $(this).parents('tr').remove();
})
	

//获取城市下一级
function get_address(id) {
    $("select[name='city']").html('<option>--请选择--</option>');
    $.ajax({
        url: "/cpsuser/add",
        data: { "re": id,"stu":1},
        type: "get",
        dataType : "html",
        success: function (data) {
            $("select[name='city_id']").html(data);
        }
    });
}

$("#import_btn").on("click", function () {
    $("#import_input").trigger("click");
});
$("#import_input").change(function () {
    var _f = $(this).val();
    if (_f == "") {
        layer_error("还没选择文件");
        return false;
    } else {
        $('#import_tel').submit();
        $("#import_ifm").load(function () {
            var json_data = $(this).contents().find('body pre').html();
            $(this).contents().find('body pre').html('');
            var obj_data = $.parseJSON(json_data);
            var repeatUserId = '';
            var repeatEmployeeId = '';
            if (obj_data.status == 'success') {
                var userData = obj_data.data;
                $.each(userData, function (i, v) {
                    var isRepeat = checkRepeat(v[0],v[2]);
                    if (isRepeat == 1) {
                        repeatUserId += repeatUserId ? ','+v[0]:v[0];
                    } else if (isRepeat == 2) {
                        repeatEmployeeId += repeatEmployeeId ? ','+v[2]:v[2];
                    } else if (isRepeat == 3) {
                        repeatUserId += repeatUserId ? ','+v[0]:v[0];
                        repeatEmployeeId += repeatEmployeeId ? ','+v[2]:v[2];
                    } else {
                        add_user(v[0],v[1],v[2],'','',v[5],v[6],v[3],v[4]);
                    }
                });
                if (repeatUserId && repeatEmployeeId) {
                    alert('会员ID '+repeatUserId+' 和员工号 '+repeatEmployeeId+' 已经存在');
                    return false;
                } else if (repeatUserId && !repeatEmployeeId) {
                    alert('会员ID '+repeatUserId+' 已经存在');
                    return false;
                } else if (!repeatUserId && repeatEmployeeId) {
                    alert('员工号 '+repeatEmployeeId+' 已经存在');
                    return false;
                }
            } else {
                layer_required(obj_data.info);
            }
        });
    }
});
</script>
{% endblock %}