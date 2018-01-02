{% extends "layout.volt" %}

{% block content %}
    <div class="page-header">
		<h1>编辑移动端模板</h1>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <!-- PAGE CONTENT BEGINS -->
            <form class="form-horizontal" action="/subjectmobile/template" method="post" id="subject_mobile_temp">

                <div class="space-6"></div>
                
				<div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right" for="form-field-1"></label>

                    <div class="col-sm-9">
						<span class="red">*</span>注意事项：此页面给开发人员使用；非开发人员不可轻易改动，以免影响其它页面相关功能。
                    </div>

                </div>
                
				<div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">模版内容</label>
                    <div class="col-sm-6">
                        <div class="pos-rel">
                            <textarea id="skuad_desc" rows="50" class="form-control col-sm-12" name="contents" >{% if contents is defined %}{{ contents }}{% endif %}</textarea>
                        </div>
                    </div>
                </div>
                <div class="">
                    <div class="col-md-offset-3 col-md-9">
                        <button class="btn btn-info ajax_button" type="button" id="do_ajax_temp">
                            <i class="ace-icon fa fa-check bigger-110"></i>
                            保存
                        </button>
                    </div>
                </div>
            </form>

        </div><!-- /.col -->
    </div>
{% endblock %}

{% block footer %}
	<script type="text/javascript">
   //提交添加
    $("#do_ajax_temp").on("click",function () {
        if($("#skuad_desc").val()==""){
            layer_required("不能为空");
            return false;
        }
        ajaxSubmit("subject_mobile_temp");
    });
</script>
{% endblock %}