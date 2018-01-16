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
                        修改推广活动
                    </h1>
                </div><!-- /.page-header -->
            </div>
            <form id="addPromotionForm"  class="form-horizontal" action="/cpschannel/acedit?act_id={% if act_id is defined and act_id  %}{{ act_id }}{%endif%}" method="post" onsubmit="ajaxSubmit(addPromotionForm);return false;">
			<input type="hidden" name="act_id" value="{% if act_id is defined and act_id  %}{{ act_id }}{%endif%}"/>
                <div class="">
                    <h3>
                        基本信息
                    </h3>
                </div><!-- /.basic -->
                <div class="row">
                    <div class="col-xs-12">
                        <!-- PAGE CONTENT BEGINS -->
                       
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_title"> <span  class="text-red">*</span>活动名称 </label>

                                <div class="col-sm-9">
                                    <input type="text"  class="col-xs-10 col-sm-5"   disabled value="{% if list['activity']['act_name'] is defined %}{{list['activity']['act_name']}}{%endif%}" />				
									<input type="hidden"   id="act_name" name="act_name"  value="{% if list['activity']['act_name'] is defined %}{{list['activity']['act_name']}}{%endif%}" />
                                </div>
                            </div>

                            <div class="space-4"></div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> <span  class="text-red">*</span>开始时间 </label>
                                <div class="col-sm-9">
                                       <input type="text" id="start_time" name="start_time" class="tools-txt datetimepk"  readonly="" value="{% if list['activity']['start_time'] is defined and list['activity']['start_time']  %}{{ list['activity']['start_time'] }}{%endif%}" readonly/>
                                </div>

                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_content"> <span  class="text-red">*</span>结束时间 </label>
                                <div class="col-sm-9">
                                  <input type="text" id="end_time" name="end_time" class="tools-txt datetimepk"  readonly="" value="{% if list['activity']['end_time'] is defined and list['activity']['end_time']  %}{{ list['activity']['end_time'] }}{%endif%}" readonly/>  
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_copywriter"> 渠道 </label>
                                <div class="col-sm-9">
                                <select name="channel_id" id="channel_id">
                                  <option value="0">--请选择渠道--</option> 
  								 {% if  channel_list is defined  %}
                                 {% for v in channel_list %}
                                    <option value="{{v['channel_id']}}"    {% if list['activity']['channel_id']== v['channel_id']  %} selected {%endif%}  >{{v['channel_name']}}</option> 
                                 {% endfor %}
                                 {% endif %}
                                </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_copywriter"> 适用于 </label>
                                <div class="col-sm-9">
  								<input  type="radio" name="for_users" value="0" style="width: 30px"  {% if list['activity']['for_users']== 0  %} checked {%endif%}   />全部
                                <input  type="radio" name="for_users" value="1" style="width: 30px" {% if list['activity']['for_users']== 1  %} checked {%endif%}  />新用户
                                <input  type="radio" name="for_users" value="2" style="width: 30px"  {% if list['activity']['for_users']== 2  %} checked {%endif%} />老用户
                                </div>
                            </div>

							<div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_copywriter">排序</label>
                                <div class="col-sm-9">
                                    <input  value="{% if list['activity']['sort'] is defined and list['activity']['sort']  %}{{ list['activity']['sort'] }}{%endif%}" id="sort" name="sort"  type="text">&nbsp;&nbsp;&nbsp; <label class="control-label" style="color:red;">数字越大排越前</label>
                                </div>
                            </div>
                   			
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_copywriter"> 类型 </label>
                                <div class="col-sm-9">
                                  <input class="is_permanent" type="radio" name="type_id" value="1" style="width: 30px" {% if list['activity']['type_id']== 1  %} checked {%endif%}
								  {% if list['activity']['type_id']!= 1 and list['has_result']==1   %} disabled {%endif%}  />全部
                                <input class="is_permanent" type="radio" name="type_id" value="3" style="width: 30px" {% if list['activity']['type_id']== 3  %} checked {%endif%} {% if list['activity']['type_id']!= 3 and list['has_result']==1  %} disabled {%endif%}/>品牌
                                <input class="is_permanent" type="radio" name="type_id" value="4" style="width: 30px" {% if list['activity']['type_id']== 4  %} checked {%endif%}{% if list['activity']['type_id']!= 4 and list['has_result']==1   %} disabled {%endif%} />单品
                                </div>
                            </div>
                            <div class="form-group" id="permanent_action"  {% if list['activity']['type_id']!= 1  %} style="display:none" {%endif%}  >
							 <label class="col-sm-3 control-label no-padding-right" for="promotion_copywriter"  > 首单返利比例 </label>
                                <label> <input type="text" id="all_first_rebate" name="all_first_rebate" class="col-xs-10 col-sm-5"  value="{% if list['activity']['relation_list'][0]['first_rebate'] is defined %}{{list['activity']['relation_list'][0]['first_rebate'] }}{%endif%}" />%</label>
								  <label> 正常返利比例</label>
                                 <label><input type="text" id="all_back_percent" name="all_back_percent" class="col-xs-10 col-sm-5"  value="{% if list['activity']['relation_list'][0]['back_percent'] is defined %}{{list['activity']['relation_list'][0]['back_percent'] }}{%endif%}" />%</label>
                                </div>
                            </div>
                            
                            
                            <div class="form-group" id="goods_add" {% if list['activity']['type_id']!= 4  %} style="display:none" {%endif%}  >
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_copywriter" id="cout1"  >添加商品</label>
                                <div class="col-sm-9">
                                  <input type="text" value="" id="search_key" placeholder="" disabled/>
                        		  <span class="btn btn-primary" disabled>搜索</span>
                                </div>
                            </div>
                           
                            <div class="form-group" id="goods_add2" {% if list['activity']['type_id']!= 3  %} style="display:none"  {%endif%}>
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_copywriter" id="cout1">添加品牌</label>
                                <div class="col-sm-9">
                                  <input type="text" value="" id="search_key2" placeholder="" disabled/>
                                  <span class="btn btn-primary" disabled>搜索</span>
                                </div>
                            </div>
                           
                           
                            <div class="form-group" id="goods_show" style="display:none">
                               <label class="col-sm-3 control-label no-padding-right" for="promotion_copywriter" id="cout2">商品</label>
                               <div class="col-sm-9">
                               <select class="search_result" style="" id="goods_1"   >
                               </select>
                               </div>
                            </div>

                            <!-- 适用范围end -->

                            <!-- 设置不参加活动start -->
                            <div id="NotJoinActivity"></div>
                            <!-- 设置不参加活动end -->
                            
                            <div style="search_list" id="search_list" >
                                {% if list['activity']['type_id'] == 3  %}
                                <table id="rule-table" class="table table-striped table-bordered table-hover">
                                    <thead>
                                    <tr>
                                      <th>品牌ID</th>
                                      <th>品牌名称</th>
                                      <th>首单返利</th>
                                      <th>正常返利</th>
                                      <th>操作</th>
                                    </tr>
                                    </thead>
                                    <tbody id="rule-tbody">
                                        {% if  list['activity']['relation_list'] is defined  and list['activity']['relation_list']!="" %}
                                            {% for v in list['activity']['relation_list'] %}
                                                <tr>
                                                    <td>{{v['belong_id']}}</td>
                                                    <td>{{v['relation_name']}}</td>
                                                    <td>
                                                        <div class="input-group col-md-5">
                                                            <input value="{{v['belong_id']}}" name="item_list[]" type="hidden">
                                                            <input name="first_rebates[]" value="{{v['first_rebate']}}" type="hidden">
                                                            <input class="form-control" style="width:95px;" value="{{v['first_rebate']}}" disabled type="text">
                                                            <div class="input-group-addon">%</div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="input-group col-md-5">
                                                            <input value="{{v['relation_name']}}" name="name_arr[]" type="hidden">
                                                            <input name="back_percents[]" value="{{v['back_percent']}}" type="hidden">
                                                            <input class="form-control" style="width:95px;" value="{{v['back_percent']}}" disabled type="text">
                                                            <div class="input-group-addon">%</div>
                                                        </div>
                                                    </td>
                                                    <td><a href="javascript:void(0)" class="btn btn-delete">删除</a>
                                                    </td>
                                                </tr>
                                            {% endfor %}
                                         {% endif %}
                                    </tbody>
                                </table>
                              {% elseif list['activity']['type_id'] == 4 %}
                                <table id="rule-table2" class="table table-striped table-bordered table-hover">
                                  <thead>
                                  <tr>
                                    <th>商品ID</th>
                                    <th>商品名称</th>
                                    <th>首单返利</th>
                                    <th>正常返利</th>
                                    <th>操作</th>
                                  </tr>
                                  </thead>

                                  <tbody id="rule-tbody">
                                    {% if  list['activity']['relation_list'] is defined  and list['activity']['relation_list']!="" %}
                                        {% for v in list['activity']['relation_list'] %}
                                            <tr>
                                                <td>{{v['belong_id']}}</td>
                                                <td>{{v['relation_name']}}</td>
                                                <td>
                                                    <div class="input-group col-md-5">
                                                        <input value="{{v['belong_id']}}" name="item_list[]" type="hidden">
                                                        <input name="first_rebates[]" value="{{v['first_rebate']}}" type="hidden">
                                                        <input class="form-control" name="first_rebates[]" style="width:95px;" placeholder="输入返利比例" value="{{v['first_rebate']}}" disabled type="text">
                                                        <div class="input-group-addon">%</div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="input-group col-md-5">
                                                        <input value="{{v['relation_name']}}" name="name_arr[]" type="hidden">
                                                        <input name="back_percents[]" value="{{v['back_percent']}}" type="hidden">
                                                        <input class="form-control" name="back_percents[]" style="width:95px;" placeholder="输入返利比例" value="{{v['back_percent']}}" disabled type="text">
                                                        <div class="input-group-addon">%</div>
                                                    </div>
                                                </td>
                                                <td><a href="javascript:void(0)" class="btn btn-delete">删除</a>
                                                </td>
                                            </tr>
                                        {% endfor %}
                                     {% endif %}
                                  </tbody>
                                </table>
                              {% endif %}
                            </div><!-- /.rule_table -->
                            <div>
                                <button id="add" class="btn btn-info" type="button" style="display:none">
                                    添加下一条规则
                                </button>
                            </div>
							
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_copywriter"><span  class="text-red">*</span>分享标题（活动标题）</label>
                                <div class="col-sm-9">
                                  
                                  
                                  <input   id="act_share_title" name="act_share_title"   type="text"  
								  value="{% if list['activity']['act_share_title'] is defined %}{{list['activity']['act_share_title'] }} {%endif%}">&nbsp;&nbsp;&nbsp;
                                  <label class="control-label" style="color:red;">建议20字以内</label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_copywriter"><span  class="text-red">*</span>分享描述（活动描述）</label>
                                <div class="col-sm-9">
                                   <textarea cols="55" rows="5" name="act_desc" id="channel_content">{% if list['activity']['act_desc'] is defined %}{{list['activity']['act_desc'] }}{%endif%}</textarea>&nbsp;&nbsp;&nbsp;
                                   <label class="control-label" style="color:red;">建议40字以内</label>
                                </div>
                            </div>
                         
                            
                            
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_copywriter"><span  class="text-red">*</span>分享链接</label>
                                <div class="col-sm-9">
                                   <input  value="{% if list['activity']['act_share_link'] is defined %}{{list['activity']['act_share_link'] }} {%endif%}" id="act_share_link" name="act_share_link" placeholder="分享链接" type="text">&nbsp;&nbsp;&nbsp;
                                   <label class="control-label" style="color:red;">只能填写网址,比如: www.abc.com</label>
                                </div>
                            </div> 
                            <div class="form-horizontal">
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> <span class="text-red">*</span>分享logo图片： </label>

                                <div class="col-sm-9">
                                    <input type="file" id="move_logo" name="move_logo" data-img="brand_logo" />
                                    <img src="{% if list['activity']['act_logo'] is defined %}{{list['activity']['act_logo'] }}{%endif%}" id="brand_logo" class="img-rounded">
                                    <input type="hidden" name="brand_logo" value="{% if list['activity']['act_logo'] is defined %}{{list['activity']['act_logo'] }}{%endif%}" />
                                    <span class="tigs">（300px*300px以上正方形）</span>
                                </div>
                            </div>
                            <div class="space-4"></div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> <span class="text-red">*</span>活动背景图 ： </label>

                                <div class="col-sm-9">
                                    <input type="file" id="move_image" name="move_image" data-img="list_image" />
                                     <img src="{% if list['activity']['act_image'] is defined %}{{list['activity']['act_image'] }}{%endif%}" id="list_image" class="img-rounded">
                                     <input type="hidden" name="list_image" value="{% if list['activity']['act_image'] is defined %}{{list['activity']['act_image'] }}{%endif%}" />
                                    <span class="tigs">（640px*300px）</span>
                                </div>
                            </div>
                            
                             <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> 活动说明 </label>

                                 <div class="col-sm-9">
                                    <textarea id="brand_desc" name="brand_desc">{% if list['activity']['act_share_content'] is defined %}{{list['activity']['act_share_content'] }}{%endif%}</textarea>
                                </div>
                            </div>
                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <button id="" class="btn btn-info" type="button" onclick="adds()">
                                        <i class="ace-icon fa fa-check bigger-110"></i>
                                        确认编辑
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
        </div>
        </div>
</div><!-- /.main-container -->

{% endblock %}

{% block footer %}
<script src="http://{{ config.domain.static }}/assets/js/jquery.datetimepicker.js"></script>

<script src="/js/kindeditor/kindeditor-min.js"></script>
<script src="/js/kindeditor/lang/zh_CN.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/ajaxfileupload.js"></script>

<script src="http://{{ config.domain.static }}/assets/js/jquery.validate.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/jquery.autosize.min.js"></script>


<script>
function transdate(endTime) {
    var date = new Date();
    date.setFullYear(endTime.substring(0, 4));
    date.setMonth(endTime.substring(5, 7) - 1);
    date.setDate(endTime.substring(8, 10));
    date.setHours(endTime.substring(11, 13));
    date.setMinutes(endTime.substring(14, 16));
    date.setSeconds(endTime.substring(17, 19));
    return Date.parse(date) / 1000;
}
function adds(){
    ajaxSubmit('addPromotionForm');
}    	


 $('.btn-primary').on('click',function(){
	 var search_key = $('#search_key').val(); 
     var search_key2 = $('#search_key2').val(); 
        if (!search_key&&!search_key2) {
            alert('请输入查询条件内容！');
            return false;
        }
		
	    var bar = $('input[name="type_id"]:checked').val();  
      
	 if(bar==3){
        var exp = /^[1-9]\d*$/;
        var  type_id = 4;
        var search_type_name='product'; 
        var  param = exp.test(search_key2) ? "&product_id = "+search_key2 : "&name="+search_key2;
     
        $.get("/cpschannel/acadd?bar="+bar+param, function(result){
        $("#goods_show").show();
        $("#add").show();
        $("#goods_1").html(result);
       });
     }else{
        var exp = /^[1-9]\d*$/;
        var  type_id = 4;
        var search_type_name='product'; 
        var  param = exp.test(search_key) ? "&product_id = "+search_key : "&name="+search_key;
     
        $.get("/cpschannel/add?goods=1&bar="+param, function(result){
        $("#goods_show").show();
        $("#add").show();
        $("#goods_1").html(result);
       });
     } 
	
 });
 
  //选择添加商品
    $('#add').click(function(){
        var val = $('.search_result').val();
        var text = $('.search_result').children(":selected").text();
        if(!val || !text)
        {
            alert('请选择要添加内容！');
            return false;
        }
		
        add_product(val, text, '');
    });
	
	//添加商品到列表
function add_product(id_val, name_val,first,percent){
	//设置默认值
	if(!arguments[3]) percent = '';
    var tr_list = $('.search_list table tr:gt(0)');
    var is_valid = 0;
    var id = 0;
    if(tr_list.length)
    {
        tr_list.each(function () {
            id = $(this).children("td:eq(2)").find(':hidden').val();
            if(id == id_val)
            {
                is_valid = 1;
                return false;
            }
        })
    }
    if(is_valid)
    {
        //限购类型
        var msg = ($('.type_id:checked').val() == 3) ? '品牌ID: ' : '商品ID: ';
        alert(msg+ id +' 不能重复添加！');
        return false;
    }
    var appent_str = '<tr>' +
        '<td>' + id_val + '</td>' +
        '<td>' + name_val + '</td>' + 
		'<td><div class="input-group col-md-5">' + 
		'<input type="hidden" value="' + id_val + '" name="item_list[]"/>' +
        '<input type="text" class="form-control" name="first_rebates[]" style="width:95px;" placeholder="输入返利比例" value="'+ first +'">' +
        '<div class="input-group-addon">%</div></div></td>' +
        '<td><div class="input-group col-md-5">' +
		'<input type="hidden" value="' + name_val + '" name="name_arr[]"/>' +
        '<input type="text" class="form-control" name="back_percents[]" style="width:95px;" placeholder="输入返利比例" value="'+ percent +'">' +
        '<div class="input-group-addon">%</div></div></td>' +
        '<td><a href="javascript:void(0)" class="btn btn-delete">删除</a></td>'+
        '</tr>';
    if($('input[name="type_id"]:checked').val()==3){
        $('#rule-table').append(appent_str);
    }else{
       $('#rule-table2').append(appent_str);
    }
   
}
//删除元素
    /*$('#search_list').on("click", ".btn-delete", function(event){
        $(this).parents('tr').remove();
    })*/
	
	
/**
 * Created by Administrator on 2016/8/26.
 */
//上传图片
$(document).on('change', '#move_logo,#move_image,#pc_logo,#pc_image', function(){
    uploadFile('/cpschannel/upload', $(this).attr('id'), $(this).attr('data-img'));
});
$(function(){
    //编辑器
    var editor;
    KindEditor.ready(function(K) {
        editor = K.create('#brand_desc', {
            height: "280px",
            allowFileManager: true,
            uploadJson: '/cpschannel/upload',
            items : ['source','formatblock','fontname','fontsize','forecolor','hilitecolor','bold','italic','underline','strikethrough','removeformat','justifyleft','justifycenter','justifyright','plainpaste','wordpaste','link','unlink','image','multiimage','clearhtml','fullscreen'],
            afterBlur: function(){this.sync();},
            afterCreate : function() {
                this.loadPlugin('autoheight');
            },
            afterUpload: function(url){
                console.log(url)
            }
        });
    });
    
    
    //删除操作
    $('.row').on('click', '.del', function(){
        layer_confirm('', '/brands/del', {id: $(this).attr('data-id')});
    });
   
  
  $(function () {
        $('.datetimepk').datetimepicker({
            step: 10,
            allowBlank:true
        });
    });
    
  $('.is_permanent').change(function () {
            var type_id = $(this).val();
            if (type_id == 1) {
			 $("#permanent_action").show();
               $("#goods_add").hide();
			   $("#goods_add2").hide();
               $("#search_list").hide(); 
			   $("#rule-table").hide();
			   $("#goods_show").hide();
			   $("#add").hide();
				
            }else if(type_id==3) {
              
               $("#permanent_action").hide();
               $("#goods_add2").show();
               $("#search_list").show();
               $("#rule-table").show();
               $("#rule-table2").hide();
               $("#goods_add").hide();
               $("#goods_1").empty();
			   $("#cout2").html("品牌");   
            }else if(type_id==4){
               $("#goods_add2").hide(); 
               $("#permanent_action").hide();
               $("#goods_add").show();
               $("#search_list").show();
               $("#rule-table").hide();
               $("#rule-table2").show(); 
               $("#goods_1").empty();
			   $("#cout2").html("商品");     
            }
  }); });
 
</script>
{% endblock %}