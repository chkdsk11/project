{% extends "layout.volt" %}

{% block content %}
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/colorpicker.min.css" />
<style>
    .form-group {
        margin-top:10px;
    }
</style>
    <div class="page-header">
        <h1>添加/编辑PC端专题</h1>
    </div>
    <!-- /.page-header -->

    <div class="row">
        <div class="col-xs-12">
            <form role="form" class="form-horizontal" action="/subjectpc/add" method="post" enctype="multipart/form-data" id="subject_pc_add">

                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right" for="form-field-1"><span
                                class="red">*</span>专题标题（title）</label>

                    <div class="col-sm-9">
                        <input type="text" name='title' id="title" placeholder="" class="col-xs-10 col-sm-5" value="{% if param['title'] is defined  %}{{ param['title'] }}{% endif %}">
                    </div>

                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right" for="form-field-1"><span
                                class="red">*</span>专题关键字（keywords）</label>

                    <div class="col-sm-9">
                        <input type="text" name='keywords' id="keywords" placeholder="" class="col-xs-10 col-sm-5" value="{% if param['keywords'] is defined  %}{{ param['keywords'] }}{% endif %}">
                    </div>

                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right" for="form-field-1"><span
                                class="red">*</span>专题描述（description）</label>

                    <div class="col-sm-9">
                        <input type="text" name='description' id="description" placeholder="" class="col-xs-10 col-sm-5" value="{% if param['description'] is defined  %}{{ param['description'] }}{% endif %}">
                    </div>

                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right"
                           for="form-field-1">背景颜色</label>

                    <div class="col-sm-9">
                        <input type="text" name='background' id="background" placeholder="请输入色号"
                               class="col-xs-10 col-sm-5" value="{% if param['background'] is defined  %}{{ param['background'] }}{% endif %}">
                    </div>

                </div>

                <input type="hidden" name="order" value="0" placeholder="排序">
                <input type="hidden" name="url" value="/subjectpc/edit" placeholder="提交后跳转地址">
                <!--<input type="hidden" name="status" value="1" placeholder="0：不启用 1：启用"> -->
                <input type="hidden" name="channel" value="95" placeholder="渠道号：91:wap 95:PC">
				<input type="hidden" name="id" value="{% if param['id'] is defined  %}{{ param['id'] }}{% endif %}" placeholder="专题id">
                <div class="clearfix form-actions">
                    <div class="col-md-offset-3 col-md-9">
                        <button class="btn btn-info ajax_button"  type="button" id="do_ajax_add">
                            <i class="ace-icon fa fa-check bigger-110"></i>
                            下一步
                        </button>
                        &nbsp; &nbsp; &nbsp;
                        <button class="btn" type="button" id="btn-reset">
                            <i class="ace-icon fa fa-undo bigger-110"></i>
                            重置
                        </button>
                    </div>
                </div>

            </form>

        </div>

    </div>
{% endblock %}

{% block footer %}
    <script src="http://{{ config.domain.static }}/assets/js/ajaxfileupload.js"></script>
    <script src="http://{{ config.domain.static }}/assets/admin/js/skuad/skuad.js"></script>
    <script src="http://{{ config.domain.static }}/assets/js/bootstrap-colorpicker.min.js"></script>

<script type="text/javascript">
    //提交添加
    $("#do_ajax_add").on("click",function () {
        if($("#title").val()==""){
            layer_required("请添加专题名称");
            return false;
        }
        if($("#keywords").val()==""){
            layer_required("请添加关键字");
            return false;
        }
        if($("#description").val()==""){
            layer_required("请添加描述");
            return false;
        }
        ajaxSubmit("subject_pc_add");
    });

    // 颜色取值器
    $('#background').colorpicker();

    // 重置按钮
    $('#btn-reset').on('click', function() {
        var form = $(this.form);
        form.find('[value]').attr('value', '');
        form.get(0).reset();
    });

</script>
{% endblock %}