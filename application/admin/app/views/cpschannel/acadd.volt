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
                        添加推广活动
                    </h1>
                </div><!-- /.page-header -->
            </div>
            <form id="addPromotionForm"  class="form-horizontal" action="/cpschannel/acadd" method="post" onsubmit="ajaxSubmit(addPromotionForm);return false;">
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
                                    <input type="text"  class="col-xs-10 col-sm-5"  id="act_name" name="act_name" placeholder="请输入活动名称" />
                                </div>
                            </div>

                           

                            <div class="space-4"></div>

                            

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> <span  class="text-red">*</span>开始时间 </label>
                                <div class="col-sm-9">
                                       <input type="text" id="start_time" name="start_time" class="tools-txt datetimepk" value="{% if channel['start_time'] is defined and channel['start_time']  %}{{ channel['start_time'] }}{%endif%}" readonly placeholder="请输入开始时间"/>
                                </div>

                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_content"> <span  class="text-red">*</span>结束时间 </label>
                                <div class="col-sm-9">
                                  <input type="text" id="end_time" name="end_time" class="tools-txt datetimepk" value="{% if channel['end_time'] is defined and channel['end_time']  %}{{ channel['end_time'] }}{%endif%}" readonly placeholder="请输入开始时间"/>  
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_copywriter"> 渠道 </label>
                                <div class="col-sm-9">
                                <select name="channel_id" id="channel_id">
                                  <option value="0">--请选择渠道--</option> 
  								 {% if  channel_list is defined  %}
                                 {% for v in channel_list %}
                                    <option value="{{v['channel_id']}}">{{v['channel_name']}}</option> 
                                 {% endfor %}
                                 {% endif %}
                                </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_copywriter"> 适用于 </label>
                                <div class="col-sm-9">
  								<input  type="radio" name="for_users" value="0" style="width: 30px" checked />全部
                                <input  type="radio" name="for_users" value="1" style="width: 30px" />新用户
                                <input  type="radio" name="for_users" value="2" style="width: 30px" />老用户
                                </div>
                            </div>

							<div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_copywriter">排序</label>
                                <div class="col-sm-9">
                                    <input  value="" id="sort" name="sort" placeholder="排序" type="text">&nbsp;&nbsp;&nbsp; <label class="control-label" style="color:red;">数字越大排越前</label>
                                </div>
                            </div>
                   			
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_copywriter"> 类型 </label>
                                <div class="col-sm-9">
                                  <input class="is_permanent" type="radio" name="type_id" value="1" style="width: 30px" checked />全部
                                <input class="is_permanent" type="radio" name="type_id" value="3" style="width: 30px" />品牌
                                <input class="is_permanent" type="radio" name="type_id" value="4" style="width: 30px" />单品
                                </div>
                            </div>
                            <div class="form-group" id="permanent_action"  >
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_copywriter"  > 首单返利比例 </label>
                                <div class="col-sm-9">
                                 <label> <input type="text" id="all_first_rebate" name="all_first_rebate" class="col-xs-10 col-sm-5"  value="{% if list['channel']['first_rebate'] is defined %}{{list['channel']['first_rebate'] }}{%endif%}" placeholder="请输入返利"/>%</label>
								   <label> 正常返利比例</label>
                                 <label><input type="text" id="all_back_percent" name="all_back_percent" class="col-xs-10 col-sm-5"  value="{% if list['channel']['back_percent'] is defined %}{{list['channel']['back_percent'] }}{%endif%}" placeholder="请输入返利" />%</label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <span id="goods_add" style="margin-left:198px;float:left;width:31%">
                                    <label class="col-sm-3 control-label no-padding-right" for="promotion_copywriter" id="cout1">添加商品</label>
                                    <div class="col-sm-9">
                                      <input type="text" value="" id="search_key" placeholder=""/>
                            		  <span class="btn btn-primary btn_search">搜索</span>
                                    </div>
                                </span>
                                <span id="goods_add2" style="margin-left:198px;float:left;width:31%">
                                    <label class="col-sm-3 control-label no-padding-right" for="promotion_copywriter" id="cout1">添加品牌</label>
                                    <div class="col-sm-9">
                                      <input type="text" value="" id="search_key2" placeholder=""/>
                                      <span class="btn btn-primary btn_search">搜索</span>
                                    </div>
                                </span>
                                <span id="bulkImport" style="float:left;display:none;">
                                    <button type="button" id="import_btn" class="btn btn-primary">批量导入</button>
                                    <a class="btn btn-primary btn-purple template" href="/cpschannel/acadd?isTemplate=1" target="_blank">模板下载</a>
                                </span>
                            </div>
                           
                            <div class="form-group" id="goods_show" style="display:none">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_copywriter" id="cout2">商品</label>
                                <div class="col-sm-9">
                                    <select class="search_result" style="" id="goods_1"   >
                                    </select>
                                    <a class="btn btn-primary" id="add"> 添加 </a>
                                </div>
                            </div>

                            <!-- 适用范围end -->

                            <!-- 设置不参加活动start -->
                            <div id="NotJoinActivity"></div>
                            <!-- 设置不参加活动end -->
                            
                            <div style="search_list" id="search_list" style="display:none">
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

                                    <tbody>

                                    </tbody>
                                </table>
                                <table id="rule-table2" class="table table-striped table-bordered table-hover" style="display:none">
                                    <thead>
                                        <tr>
                                          <th>商品ID</th>
                                          <th>商品名称</th>
                                          <th>首单返利</th>
                                          <th>正常返利</th>
                                          <th>操作</th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                    </tbody>
                                </table>
                            </div><!-- /.rule_table -->
							
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_copywriter"><span  class="text-red">*</span>分享标题（活动标题）</label>
                                <div class="col-sm-9">
                                  
                                  
                                  <input  value="" id="act_share_title" name="act_share_title" placeholder="请输入标题" type="text">&nbsp;&nbsp;&nbsp;
                                  <label class="control-label" style="color:red;">建议20字以内</label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_copywriter"><span  class="text-red">*</span>分享描述（活动描述）</label>
                                <div class="col-sm-9">
                                   <textarea cols="55" rows="5" name="act_desc" id="channel_content" placeholder="请输入描述"></textarea>&nbsp;&nbsp;&nbsp;
                                   <label class="control-label" style="color:red;">建议40字以内</label>
                                </div>
                            </div>
                         
                            
                            
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="promotion_copywriter"><span  class="text-red">*</span>分享链接</label>
                                <div class="col-sm-9">
                                   <input  value="" id="act_share_link" name="act_share_link" placeholder="（例:http://www.163.com）" type="text">&nbsp;&nbsp;&nbsp;
                                   <label class="control-label" style="color:red;">只能填写网址,比如: www.abc.com</label>
                                </div>
                            </div> 
                            <div class="form-horizontal">
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> <span class="text-red">*</span>分享logo图片： </label>

                                <div class="col-sm-9">
                                    <input type="file" id="move_logo" name="move_logo" data-img="brand_logo" />
                                    <img src="" id="brand_logo" class="img-rounded">
                                    <input type="hidden" name="brand_logo" />
                                    <span class="tigs">（300px*300px以上正方形）</span>
                                </div>
                            </div>
                            <div class="space-4"></div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> <span class="text-red">*</span>活动背景图 ： </label>

                                <div class="col-sm-9">
                                    <input type="file" id="move_image" name="move_image" data-img="list_image" />
                                    <img src="" id="list_image" class="img-rounded">
                                    <input type="hidden" name="list_image" />
                                    <span class="tigs">（640px*300px）</span>
                                </div>
                            </div>
                            
                             <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> 活动说明 </label>

                                 <div class="col-sm-9">
                                    <textarea id="brand_desc" name="brand_desc"  ></textarea>
                                </div>
                            </div>
                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <button id="" class="btn btn-info" type="button" onclick="adds()">
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
            <form target="import_ifm" id="import_tel" method="post" action="/cpschannel/acadd?import=1" enctype="multipart/form-data">
                <input id="import_input" name="file" type="file" style="width: 0;height: 0;">
            </form>
            <iframe id="import_ifm" name="import_ifm" style="display:none"></iframe>
        </div>
        </div>
</div><!-- /.main-container -->

{% endblock %}

{% block footer %}
<script src="http://{{ config.domain.static }}/assets/js/jquery.datetimepicker.js"></script>

<script src="/js/kindeditor/kindeditor-min.js"></script>
<script src="/js/kindeditor/lang/zh_CN.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/ajaxfileupload.js"></script>
<script type="text/javascript">
    var url = '/brands/add',brandName = '';
</script>

<script src="http://{{ config.domain.static }}/assets/js/jquery.validate.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/jquery.autosize.min.js"></script>


<script>

function adds(){
  ajaxSubmit('addPromotionForm');
}    	


 $('.btn_search').on('click',function(){
	  
	 
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
        $("#goods_1").html(result);
       });
     }else{
        var exp = /^[1-9]\d*$/;
        var  type_id = 4;
        var search_type_name='product'; 
        var  param = exp.test(search_key) ? "&product_id = "+search_key : "&name="+search_key;
     
        $.get("/cpschannel/add?goods=1&bar="+param, function(result){
        $("#goods_show").show();
        $("#goods_1").html(result);
       });
     } 
	
 });
 
//选择添加商品
$('#add').click(function(){
    var val = $('.search_result').val();
    var text = $('.search_result').children(":selected").text();
    var type_id = $('input[name="type_id"]:checked').val();
    if(!val || !text)
    {
        alert('请选择要添加内容！');
        return false;
    }
	if (checkRepeat(val, type_id)) {
        //限购类型
        var msg = (type_id == 3) ? '品牌ID: ' : '商品ID: ';
        alert(msg + ' ' + val +' 不能重复添加！');
        return false;
    }
    add_product(val, text, '', '', type_id);
});

//检测是否重复添加
function checkRepeat(id_val,type_id){
    var table = '';
    if(type_id==3){
        table = 'rule-table';
    }else{
        table = 'rule-table2';
    }
    var tr_list = $('#'+ table + ' tbody tr');
    var is_valid = 0;
    var id = 0;
    if(tr_list.length)
    {
        tr_list.each(function () {
            id = $(this).children("td:eq(2)").find(':hidden').val();
            if(id == id_val)
            {
                is_valid = 1;
            }
        })
    }
    return is_valid;
}

//添加商品到列表
function add_product(id_val, name_val,first,percent,type_id){
    var table = '';
    if(type_id==3){
        table = 'rule-table';
    }else{
        table = 'rule-table2';
    }
	//设置默认值
	if(!arguments[3]) percent = '';
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
    $('#' + table).append(appent_str);
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
               $("#bulkImport").hide();
			   $("#rule-table").hide();
			   $("#goods_show").hide();
            }else if(type_id==3) {
               $("#permanent_action").hide();
               $("#goods_add2").show();
               $("#search_list").show();
               $("#rule-table").show();
               $("#bulkImport").show();
               $("#rule-table2").hide();
               $("#rule-table2 tbody").empty();
               $("#goods_add").hide();
               $("#goods_show").hide();
               $("#goods_1").empty();
			   $("#cout2").html("品牌");
               $(".template").attr('href','/cpschannel/acadd?isTemplate=1&type_id=3');
               $("#import_tel").attr('action','/cpschannel/acadd?import=1&type_id=3');
            }else if(type_id==4){
               $("#goods_add2").hide(); 
               $("#permanent_action").hide();
               $("#goods_add").show();
               $("#search_list").show();
               $("#bulkImport").show();
               $("#rule-table").hide();
               $("#rule-table tbody").empty();
               $("#rule-table2").show(); 
               $("#goods_show").hide();
               $("#goods_1").empty();
			   $("#cout2").html("商品");
               $(".template").attr('href','/cpschannel/acadd?isTemplate=1&type_id=4');
               $("#import_tel").attr('action','/cpschannel/acadd?import=1&type_id=4');
            }
  }); });
  $("#search_list").hide();
  $("#goods_add").hide();
  $("#goods_add2").hide();


$("#import_btn").on("click", function () {
    $("#import_input").trigger("click");
});
$("#import_input").change(function () {
    var _f = $(this).val();
    var type_id = $('input[name="type_id"]:checked').val();
    if (_f == "") {
        layer_error("还没选择文件");
        return false;
    } else {
        $('#import_tel').submit();
        $("#import_ifm").load(function () {
            var json_data = $(this).contents().find('body pre').html();
            $(this).contents().find('body pre').html('');
            var obj_data = $.parseJSON(json_data);
            var repeatData = '';
            if (obj_data.status == 'success') {
                var importData = obj_data.data;
                $.each(importData, function (i, v) {
                    if (!checkRepeat(v.id, type_id)) {
                        add_product(v.id, v.name,v.first,v.normal,type_id)
                    } else {
                        repeatData += repeatData ? ',' + v.id : v.id;
                    }
                });
                var text = '';
                if (obj_data.errorData) {
                    text += (type_id == 3) ? '品牌ID ' : '商品ID ';
                    text += obj_data.errorData;
                    text += (type_id == 3) ? ' 不存在\r\n' : ' 四个端没上架或是赠品\r\n';
                }
                if (repeatData) {
                    text += (type_id == 3) ? '品牌ID ' : '商品ID ';
                    text += repeatData;
                    text += ' 不能重复添加';
                }
                if (text) {
                    alert(text);
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