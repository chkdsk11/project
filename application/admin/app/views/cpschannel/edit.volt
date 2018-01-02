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
                        修改推广渠道
                    </h1>
                </div><!-- /.page-header -->
            </div>
            <form id="addPromotionForm"  class="form-horizontal" action="/cpschannel/edit?id={{id}}" method="post" onsubmit="ajaxSubmit(addPromotionForm);return false;">
                <div class="">
                    <h3>
                        基本信息
                    </h3>
                </div><!-- /.basic -->
                <div class="row">
                    <div class="col-xs-12">
                        <!-- PAGE CONTENT BEGINS -->
                       
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_title"> <span  class="text-red">*</span>渠道名称 </label>

                                <div class="col-sm-9">
                                    <input type="hidden"  id="channel_name" name="channel_name" class="col-xs-10 col-sm-5"  value="{% if list['channel']['channel_name'] is defined %}{{list['channel']['channel_name'] }}{%endif%}" />
									
									<input type="text" class="col-xs-10 col-sm-5" placeholder="不可为空"  disabled value="{% if list['channel']['channel_name'] is defined %}{{list['channel']['channel_name'] }}{%endif%}" />
									 <input type="hidden" value="{{id}}" name="channel_id">
                                </div>
                            </div>

                           

                            <div class="space-4"></div>

                            

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> <span  class="text-red">*</span>APP注册返利 </label>
                                <div class="col-sm-9">
                                        <input type="text" id="back_amount" name="back_amount" class="col-xs-10 col-sm-5"  value="{% if list['channel']['back_amount'] is defined %}{{list['channel']['back_amount'] }}{%endif%}" />&nbsp;&nbsp;&nbsp;
									    <label class="control-label" style="color:red;">填写大于0的数字</label>
                                </div>

                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_content"> <span  class="text-red">*</span>WAP注册返利 </label>
                                <div class="col-sm-9">
                                   <input type="text" id="wap_back_amount" name="wap_back_amount" class="col-xs-10 col-sm-5"  value="{% if list['channel']['wap_back_amount'] is defined %}{{list['channel']['wap_back_amount'] }}{%endif%}"/>&nbsp;&nbsp;&nbsp;
                                   <label class="control-label" style="color:red;">填写大于0的数字</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_copywriter"> 渠道标签 </label>
                                <div class="col-sm-9">
                                 <input type="hidden" id="tags" name="tags" class="col-xs-10 col-sm-5"  value="{% if list['channel']['tags'] is defined %}{{list['channel']['tags'] }}{%endif%}" />
  								 <input type="text"  disabled class="col-xs-10 col-sm-5" placeholder="不可为空"  value="{% if list['channel']['tags'] is defined %}{{list['channel']['tags'] }}{%endif%}"/>
  								 &nbsp;&nbsp;&nbsp;
  								  <label class="control-label" style="color:red;">2位英文字母</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_copywriter" > 永久渠道 </label>
                                <div class="col-sm-9">
                                   
  								<input class="is_permanent" type="radio" name="is_permanent" value="1" style="width: 30px"  {% if list['channel']['is_permanent'] ==1 %}  checked {%endif%}     />是
                                <input class="is_permanent" type="radio" name="is_permanent" value="0" style="width: 30px" {% if list['channel']['is_permanent'] ==0 %}  checked {%endif%}/>否
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_copywriter"> 返利周期</label>
                                <div class="col-sm-9">
                                  <input type="text" id="expire_day" name="expire_day" class="col-xs-10 col-sm-5" placeholder="不可为空" value="{% if list['channel']['expire_day'] is defined %}{{list['channel']['expire_day'] }}{%endif%}" />&nbsp;&nbsp;&nbsp;
                                  <label class="control-label" style="color:red;">单位是天,填写大于0的数字</label>
                                </div>
                            </div>
                            
							<div class="form-group" id="permanent_action" {% if list['channel']['is_permanent'] ==0 %}   style="position:relative; display:none" {%endif%}>
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_copywriter" > 永久活动 首单返利</label>
                                <div class="col-sm-9">
                                 <label> <input type="text" id="first_rebate" name="first_rebate" class="col-xs-10 col-sm-5" placeholder="不可为空" value="{% if list['channel']['first_rebate'] is defined %}{{list['channel']['first_rebate'] }}{%endif%}" />%</label>
								 正常返利:
                                 <label><input type="text" id="back_percent" name="back_percent" class="col-xs-10 col-sm-5" placeholder="不可为空" value="{% if list['channel']['back_percent'] is defined %}{{list['channel']['back_percent'] }}{%endif%}" />% &nbsp;&nbsp;&nbsp;<label class="control-label" style="color:red;">填写大于0的数字</label></label>
                                </div>
                            </div>
                   			
                            <div class="form-group" >
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_copywriter">添加商品</label>
                                <div class="col-sm-9">
                                  
                                  <input type="text" value="" id="search_key" placeholder=""/>
                        		  <span class="btn btn-primary" id="btn_search">搜索</span>
                                </div>
                            </div>
                             <div class="form-group" id="goods_show" style="display:none">
                               <label class="col-sm-3 control-label no-padding-right" for="promotion_copywriter">商品</label>
                               <div class="col-sm-9">
                               <select class="search_result" style="" id="goods_1"   >
                               </select>
                               </div>
                            </div>

                            <!-- 适用范围end -->

                            <!-- 设置不参加活动start -->
                            <div id="NotJoinActivity"></div>
                            <!-- 设置不参加活动end -->
                            <div class="">
                                <h3>
                                    商品
                                </h3>
                            </div><!-- /.rule -->
                            <div style="search_list" id="search_list">
                                <table id="rule-table" class="table table-striped table-bordered table-hover">
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
									 {% if  list['relation_list'][0]['belong_id'] is defined and list['relation_list'][0]['belong_id']!=""  %}
                    					
										{% for v in list['relation_list'] %}
										
										<tr>
											<td>{{v['belong_id']}}</td>
											<td>{{v['relation_name']}}</td>
											<td>
											<div class="input-group col-md-5">
											<input value="{{v['belong_id']}}" name="item_list[]" type="hidden">
											<input class="form-control" name="first_rebates[]" style="width:95px;" placeholder="输入返利比例" value="{{v['first_rebate']}}" type="text">
											<div class="input-group-addon">%</div>
											</div>
											</td>
											<td>
											<div class="input-group col-md-5"><input value="{{v['relation_name']}}" name="name_arr[]" type="hidden">
											<input class="form-control" name="back_percents[]" style="width:95px;" placeholder="输入返利比例" value="{{v['back_percent']}}" type="text">
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
                            </div><!-- /.rule_table -->
                            <div>
                                <button id="add" class="btn btn-info" type="button" style="display:none">
                                    添加下一条规则
                                </button>
                            </div>
							
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_copywriter"><span  class="text-red">*</span>分享标题</label>
                                <div class="col-sm-9">
                                  <input type="text" id="title" name="title" class="col-xs-10 col-sm-5" placeholder="不可为空"  value="{% if list['channel']['title'] is defined %}{{list['channel']['title'] }}{%endif%}"  />&nbsp;&nbsp;&nbsp;
                                 <label class="control-label" style="color:red;">建议20字以内</label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_copywriter"><span  class="text-red">*</span>分享描述</label>
                                <div class="col-sm-9">
                                   <textarea cols="55" rows="5" name="content" id="channel_content">{% if list['channel']['content'] is defined %}{{list['channel']['content'] }}{%endif%}</textarea>&nbsp;&nbsp;&nbsp;
                                   <label class="control-label" style="color:red;">建议40字以内</label>
                                </div>
                            </div>
                           
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_copywriter"><span  class="text-red">*</span>分享链接</label>
                                <div class="col-sm-9">
                                   <input type="text" id="link" name="link" class="col-xs-10 col-sm-5" placeholder="不可为空" value="{% if list['channel']['link'] is defined %}{{list['channel']['link'] }}{%endif%}" />&nbsp;&nbsp;&nbsp;
                                     <label class="control-label" style="color:red;">只能填写网址,比如: www.abc.com</label>
                                </div>
                            </div> 
                            
                            
                          
                            <div class="form-horizontal">
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> <span class="text-red">*</span>分享logo图片： </label>

                                <div class="col-sm-9">
									<input type="file" id="move_logo" name="move_logo" data-img="brand_logo" />
                                    <img src="{% if list['channel']['logo'] is defined %}{{list['channel']['logo'] }}{%endif%}" id="brand_logo" class="img-rounded">
                                    <input type="hidden" name="brand_logo" value="{% if list['channel']['logo'] is defined %}{{list['channel']['logo'] }}{%endif%}" />
                                    <span class="tigs">（300px*300px以上正方形）</span>
                                </div>
                            </div>
                            <div class="space-4"></div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> <span class="text-red">*</span>活动背景图 ： </label>

                                <div class="col-sm-9">
									<input type="file" id="move_image" name="move_image" data-img="list_image" />
                                    <img src="{% if list['channel']['channel_image'] is defined %}{{list['channel']['channel_image'] }}{%endif%}" id="list_image" class="img-rounded">
                                    <input type="hidden" name="list_image" value="{% if list['channel']['channel_image'] is defined %}{{list['channel']['channel_image'] }}{%endif%}" />
                                    <span class="tigs">（640px*320px）</span>
                                </div>
                            </div>
                            
                             <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> 活动说明: </label>

                                 <div class="col-sm-9">
                                    <textarea id="brand_desc" name="brand_desc">{% if list['channel']['description'] is defined %}{{list['channel']['description'] }}{%endif%}</textarea>
                                </div>
                            </div>
                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <button id="" class="btn btn-info" type="button" onclick="add_ss()">
									
                                        <i class="ace-icon fa fa-check bigger-110"></i>
                                        确认
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
<script src="http://{{ config.domain.static }}/assets/admin/js/promotion/promotion-public.js"></script>

<script src="http://{{ config.domain.static }}/assets/admin/js/brands/globalBrands.js"></script>


<script src="/js/kindeditor/kindeditor-min.js"></script>
<script src="/js/kindeditor/lang/zh_CN.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/ajaxfileupload.js"></script>
<script type="text/javascript">
    var url = '/brands/add',brandName = '';
</script>



<script>
function add_ss(){
   var str = /^[a-zA-Z]{2}$/;//两个英文字符
		var num = /^[0-9]{1,}$/;//整数
		var w = /^[A-Za-z]+:\/\/[A-Za-z0-9-_:]+\.[A-Za-z0-9-_%&\?\/.=:]+$/;
		var fl = /^\d{1,2}(\.\d{1,2})?$/;
		var name = document.getElementById("channel_name");
		var tags = document.getElementById("tags");
		var link = document.getElementById("link");
		var title = document.getElementById("title");
		var logo = $("#brand_logo")[0].src;
		
		var img = $("#list_image")[0].src; 
		var content = document.getElementById("channel_content");
		var description = document.getElementById("brand_desc");
		var expire_day = document.getElementById("expire_day");
		var is_permanent = $('.is_permanent:checked').val();
		
		if (name.value.length == 0) {
			alert('渠道名称必填');
			return false;
		}
		if (tags.value.length == 0) {
			alert('渠道标签必填');
			return false;
		} else if (tags.value.length != 2) {
			alert('渠道标签字符长度为2');
			return false;
		}
		if (!str.test(tags.value)) {
			alert('渠道标签由两个英文字符组成');
			return false;
		}
		if (is_permanent == 1) {
			var first_rebate = $('input[name="first_rebate"]').val();
			var back_percent = $('input[name="back_percent"]').val();
			if (!first_rebate && !back_percent) {
				alert('永久渠道的首单返利和正常返利必填');
				return false;
			} else if (!first_rebate) {
				alert('永久渠道的首单返利必填');
				return false;
			} else if (!back_percent) {
				alert('永久渠道的正常返利必填');
				return false;
			} else if (!fl.test(first_rebate) || !fl.test(back_percent)) {
				alert('请输入合理的返利');
				return false;
			}
		}
		if (expire_day.value == 0) {
			alert('返利周期必填');
			return false;
		}
		if (!num.test(expire_day.value)) {
			alert('返利周期必须是正整数');
			return false;
		}
		if (title.value.length == 0) {
			alert('分享标题必填');
			return false;
		}
		if (content.value.length == 0) {
			alert('分享描述必填');
			return false;
		}
		if (link.value.length == 0) {
			alert('分享链接必填');
			return false;
		} 
/*else if (!w.test(link.value)) {
			alert('分享链接格式错误(如：http://www.baiyjk.com)');
			return false;
		}
*/
		if (logo=="") {
		alert('请上活动logo图');
		return false;
		}
		if (img=="") {
		alert('请上传活动背景图');
		return false;
		}
		/*
		if (logo.value.length == 0) {
			alert('请上活动logo图');
			return false;
		}
		if (img.value.length == 0) {
			alert('请上传活动背景图');
			return false;
		}*/
		if (description.value.length == 0) {
			alert('活动说明必填');
			return false;
		}
  ajaxSubmit('addPromotionForm');
   
}    
 $('.btn-primary').on('click',function(){
	
	
	 var search_key = $('#search_key').val();
        if (!search_key) {
            alert('请输入查询条件内容！');
            return false;
        }
		var exp = /^[1-9]\d*$/;
		 var  type_id = 4;
		 var search_type_name='product';
         var  param = exp.test(search_key) ? "&product_id = "+search_key : "&name="+search_key;
	      
	  
	$.get("/cpschannel/add?goods=1"+param, function(result){
        $("#goods_show").show();
		$("#add").show();
		$("#goods_1").html(result);
  	});
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
    var tr_list =  $('#search_list tbody tr');
	
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
    $('#rule-table').append(appent_str);
}
//删除元素
    $('#search_list').on("click", ".btn-delete", function(event){
        $(this).parents('tr').remove();
    })
	
	
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
    //检测类似的品牌名称
    $('.row').on('blur', '#brand_name', function(){
        var brand_name = $(':input[name=brand_name]').val();
        $('#brand_tig').text('');
        if(brand_name != '' && brand_name != brandName){
            $.ajax({
                url: '/brands/search',
                type: 'post',
                dataType: 'json',
                data: {brand_name: brand_name}
            })
            .done(function(data){
                if(data.status == 'success'){
                    $('#brand_tig').append('（已经添加相似的品牌有：<b style="color:blue;">' + data['data'][0]['brand_name'] + '</b>）');
                }
            });
        }
    });

    //删除操作
    $('.row').on('click', '.del', function(){
        layer_confirm('', '/brands/del', {id: $(this).attr('data-id')});
    });
    //修改排序
    $('.row').on('click', '.update', function(){
        var brand_id = $(this).attr('brand_id');
        var brand_sort = $(this).parents('.tools-box').find("input[name=sort]").val();
        if(brand_sort == ''){
            layer_required('请输入排序！');
        }
        $.ajax({
            url: '/brands/update',
            type: 'post',
            dataType: 'json',
            data: {
                'id' : brand_id,
                'brand_sort' : brand_sort
            }
        })
            .done(function(data){
                layer_required(data.info);
            });
    });
    //修改热门状态
    $('.row').on('click', '.is_hot', function(){
        var thisObj = $(this);
        var id = thisObj.attr('data-id');
        var is_hot = thisObj.attr('data');
        is_hot = is_hot == 1 ? 0 : 1;
        $.ajax({
                url: '/brands/update',
                type: 'post',
                dataType: 'json',
                data: {
                    'id' : id,
                    'is_hot' : is_hot
                }
            })
            .done(function(data){
                if(data.status == 'success'){
                    thisObj.attr('data', is_hot);
                    thisObj.removeClass();
                    if(is_hot == 1){
                        thisObj.addClass('ace-icon glyphicon btn-xs btn-info glyphicon-ok is_hot');
                    }else{
                        thisObj.addClass('ace-icon glyphicon btn-xs btn-danger glyphicon-remove is_hot');
                    }
                }else{
                    layer_required(data.info);
                }
            });
    });
});
$('.is_permanent').change(function () {
            var is_permanent = $(this).val();
            if (is_permanent == 1) {
                $('#permanent_action').show();
            } else {
                $('#permanent_action').hide();
            }
        });
	
</script>
{% endblock %}