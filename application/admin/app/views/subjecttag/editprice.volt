{% extends "layout.volt" %}

{% block content %}
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/jquery.datetimepicker.css" class="ace-main-stylesheet" />
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/admin/css/coupon_addon.css" class="ace-main-stylesheet" />
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/colorpicker.min.css" />
<!--放页面内容-->
<div class="page-content">
    <div class="page-header">
        <h1>添加价格标签</h1>
    </div>


    <div class="row tag-edit">
        <div class="col-xs-12">
            <form role="form" class="form-horizontal" action="/subjecttag/editPrice" method="post" id="edit_price_tag">

                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right" for="form-field-1"><span class="red">*</span>标签名称</label>

                    <div class="col-sm-9">
                        <input type="text" id="tag_name" placeholder="" class="col-xs-10 col-sm-5" name="tag_name" value='{% if tag['tag_name'] is defined  %}{{ tag['tag_name'] }}{% endif %}'>
                    </div>

                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right" for="form-field-1">标签图片</label>

                    <div class="col-sm-9">
						<input type="file" name='move_img' id="move_img" placeholder="" data-img="img_url" class="col-xs-10 col-sm-5" >
                           <img src="{% if tag['img_url'] is defined  %}{{ tag['img_url'] }}{% endif %}" id="img_url" class="img-rounded">
                           <input type="hidden" name="img_url" value='{% if tag['img_url'] is defined  %}{{ tag['img_url'] }}{% endif %}'/>
                    </div>

                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right" for="form-field-1">标签颜色</label>

                    <div class="col-sm-9">
                        <input type="text" id="background" placeholder="请输入色号" class="col-xs-10 col-sm-5" name="background" value='{% if tag['background'] is defined  %}{{ tag['background'] }}{% endif %}'>
                    </div>

                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right" for="form-field-1">
                        <span class="red">*</span>生效时间
                    </label>

                    <div class="col-sm-9">
                        <input type="text" data-date-format="yyyy-mm-dd" name="start_time" id="start_time" placeholder="开始时间" class="tools-txt datetimepk" value="{% if tag['start_time'] is defined %}{{ tag['start_time'] }}{%endif%}" readonly>
                        <input type="text" data-date-format="yyyy-mm-dd" name="end_time" id="end_time" placeholder="结束时间" class="tools-txt datetimepk" value="{% if tag['end_time'] is defined %}{{ tag['end_time'] }}{%endif%}" readonly>
                    </div>

                </div>
                <div class="form-group use-special">
                    <label class="col-sm-3 control-label no-padding-right"
                           for="form-field-1"><span class="red">*</span>应用的专题</label>
					<div class="col-sm-9">
						<input type="hidden" placeholder="专题id(多个以英文逗号隔开)" class="col-xs-10 col-sm-5" id="search_value" name="subject_ids" value="{% if tag['subject_ids'] is defined  %}{{ tag['subject_ids'] }}{% endif %}">
                    </div>
                </div><hr/>
				
                <div class="col-sm-9 col-sm-offset-1">
                    <div class="form-inline">

                        <div class="special-table">
                            <table id="simple-table" class="table  table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th class="center sorting_disabled" rowspan="1" colspan="1" aria-label="">
                                        <label class="pos-rel">
                                            <input type="checkbox" class="ace" id="select_all">
                                            <span class="lbl"></span>
                                        </label>
                                    </th>
                                    <th class="detail-col">ID</th>
                                    <th>平台</th>
                                    <th width="350px">专题名称</th>
                                    <th>状态</th>
                                    <th>
                                        <i class="ace-icon fa fa-clock-o bigger-110 hidden-480"></i>
                                        修改时间
                                    </th>
									<th width="350px">
                                        标签
                                    </th>
                                </tr>
                                </thead>

                                <tbody id='simple_table_tr'>
                                </tbody>
                            </table>
                            <div class="row" id='col_page'>
                                
                            </div>
                        </div>
                    </div>


                </div>

				<input type="hidden" name="id" value='{% if id is defined  %}{{ id }}{% endif %}'/>
                <input type="hidden" name="type" value="2" placeholder="类型 1:商品标签　2:价格标签">
                <input type="hidden" name="status" value="1" placeholder="0：不启用 1：启用">
                <input type="hidden" name="url" value="/subjecttag/priceList" placeholder="提交后跳转地址">

                <div class="clearfix form-actions col-sm-12">
                    <div class="col-md-offset-3 col-md-9">
                        <!--
						<button class="btn btn-info" type="button" data-toggle="modal" data-target="#special-review-modal">
                            <i class="ace-icon fa fa-check bigger-110"></i>
                            预览
                        </button>
                        &nbsp; &nbsp; &nbsp;
						-->
                        <button class="btn btn-info" type="button" id="do_ajax_sure_btn">
                            <i class="ace-icon fa fa-check bigger-110"></i>
                            提交
                        </button>
                    </div>
                </div>

                <!--预览弹窗-->
                <div class="modal fade" id="special-review-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title" id="myModalLabel">标签预览</h4>
                            </div>
                            <div class="modal-body">
                                ...
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                            </div>
                        </div>
                    </div>
                </div>

            </form>

        </div>

    </div>

    <!-- /.page-header -->
</div>
<!-- /.page-content -->

{% endblock %}

{% block footer %}
<script src="http://{{ config.domain.static }}/assets/js/jquery.datetimepicker.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/ajaxfileupload.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/bootstrap-colorpicker.min.js"></script>
<script type="text/javascript">
    $(function () {
        $('.datetimepk').datetimepicker({
            step: 10,
            allowBlank:true
        });
    });
    

    //提交添加
    $("#do_ajax_sure_btn").on("click",function () {
        if($("#tag_name").val()==""){
            layer_required("请添加标签名称");
            return false;
        }
        if($("#start_time").val()==""){
            layer_required("开始时间不能为空");
            return false;
        }
        if($("#end_time").val()==""){
            layer_required("结束时间不能为空");
            return false;
        }
		if($("#subject_ids").val()==""){
            layer_required("请添加专题ID");
            return false;
        }
        var start_time_timestamp = new Date($("#start_time").val()).getTime();
        var end_time_timestamp = new Date($("#end_time").val()).getTime();
        var now_time = new Date().getTime();
        if(end_time_timestamp<start_time_timestamp){
            layer_required("结束时间不能早于开始时间");
            return false;
        }
        if (now_time >= end_time_timestamp) {
            layer_required("结束时间不能早于当前时间");
            return false;
        }
        ajaxSubmit("edit_price_tag");
    });
		//上传图片
$(document).on('change', '#move_img', function(){
    uploadFile('/subjecttag/upload', $(this).attr('id'), $(this).attr('data-img'));
});

(function ($) {
   $.getUrlParam = function (name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return unescape(r[2]); return null;
   }
  })(jQuery);
  var xx = $.getUrlParam('id');
  if(xx!='0'){
	//初始化加载单品
    $.post("/subjecttag/allsubjectList2?id="+xx+"",{id:xx,type:2} , function (data) {
        if(data!="0"){
            var html = '';
            $.each(data['list'], function (n,v) {
				html += "<tr><td class='center'><label class='pos-rel'><input type='checkbox' " + (v['tag_name'] ? "disabled " : "") + " value='"+v['id']+"' class='ace js-select' "+v['checked']+"><span class='lbl'></span></label></td><td>"+v['id']+"</td><td>"+v['channel']+"</td><td>"+v['title']+"</td><td class='hidden-480'><span class='label label-sm label-success'>"+v['status']+"</span></td><td>"+v['update_time']+"</td><td width='350px' >"+v['tag_name']+"</td></tr>";
                setTagsSelect(v['id'], v['checked'] == 'checked');
            });
            $("#simple_table_tr").html(html);
			$("#col_page").append(data['page']);
        }
    });
  }else{
	
    $.post("/subjecttag/allsubjectList2?id="+xx+"",{id:xx,type:2} , function (data) {
        if(data!="0"){
            var html = '';
            $.each(data['list'], function (n,v) {
				html += "<tr><td class='center'><label class='pos-rel'><input type='checkbox' " + (v['tag_name'] ? "disabled " : "") + "  value='"+v['id']+"' class='ace js-select' "+v['checked']+"><span class='lbl'></span></label></td><td>"+v['id']+"</td><td>"+v['channel']+"</td><td>"+v['title']+"</td><td class='hidden-480'><span class='label label-sm label-success'>"+v['status']+"</span></td><td>"+v['update_time']+"</td><td width='350px' >"+v['tag_name']+"</td></tr>";
                setTagsSelect(v['id'], v['checked'] == 'checked');
            });
            $("#simple_table_tr").html(html);
			$("#col_page").append(data['page']);
        }
    });
	
  }

	function tiaopost(data){
		$.post("/subjecttag/allsubjectList2?id="+xx+"&page="+data+"", {id:xx,type:2} ,function (data) {
			if(data!="0"){
				var kan='';
				$.each(data['list'], function (n,v) {
					kan  += "<tr><td class='center'><label class='pos-rel'><input type='checkbox' " + (v['tag_name'] ? "disabled " : "") + "  value='"+v['id']+"' class='ace js-select' "+v['checked']+"><span class='lbl'></span></label></td><td>"+v['id']+"</td><td>"+v['channel']+"</td><td>"+v['title']+"</td><td class='hidden-480'><span class='label label-sm label-success'>"+v['status']+"</span></td><td>"+v['update_time']+"</td><td width='350px' >"+v['tag_name']+"</td></tr>";
                    setTagsSelect(v['id'], v['checked'] == 'checked');
                });
				$("#simple_table_tr").html(kan);
				$("#col_page").html(data['page']);
			}
		});
	}


    var select_input = $('#search_value');

    // 设置商品选择/取消选择
    function setTagsSelect(id, select) {
        var val_arr = select_input.val().split(',');
        var id_index = $.inArray(id, val_arr);

        if(select && id_index < 0){
            val_arr.push(id);
        }

        if(!select && id_index > -1){
            val_arr.splice(id_index, 1);
        }

        select_input.val($.grep(val_arr, function(n){
            return !!n;
        }).join(','));
    }

    // 点击商品选择/取消选择商品
    $(document).on('click select_all', 'input.js-select[type="checkbox"]:enabled', function() {
        setTagsSelect(this.value, this.checked);
    });

    // 点击全选/取消全选
    $('#select_all').on('click', function() {
        var checked = this.checked;
        $('input.js-select[type="checkbox"]:enabled').prop('checked', checked).trigger('select_all');
    });

    // 颜色取值器
    $('#background').colorpicker();
</script>
{% endblock %}