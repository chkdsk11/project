{% extends "layout.volt" %}

{% block content %}
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/bootstrap.min.css" />
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/font-awesome/4.2.0/css/font-awesome.min.css" />

<!-- page specific plugin styles -->
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/jquery-ui.custom.min.css" />
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/chosen.min.css" />
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/datepicker.min.css" />
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/bootstrap-timepicker.min.css" />
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/daterangepicker.min.css" />
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/colorpicker.min.css" />
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/product.css" />

 <!--text fonts-->
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/fonts/fonts.googleapis.com.css" />

<!-- ace styles -->
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/ace.min.css" class="ace-main-stylesheet" id="main-ace-style" />

<!--[if lte IE 9]>
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/ace-part2.min.css" class="ace-main-stylesheet" />
<![endif]-->

<!--[if lte IE 9]>
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/ace-ie.min.css" />
<![endif]-->

<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/jquery-ui.min.css" />

<!-- inline styles related to this page -->

<!-- ace settings handler -->
<script src="http://{{ config.domain.static }}/assets/js/ace-extra.min.js"></script>

<!-- HTML5shiv and Respond.js for IE8 to support HTML5 elements and media queries -->

<!--[if lte IE 8]>
<script src="http://{{ config.domain.static }}/assets/js/html5shiv.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/respond.min.js"></script>
<![endif]-->
<script src="http://{{ config.domain.static }}/assets/js/jquery.min.js"></script>
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/product_spu.css" />
            <!--    新建商品--多品规  start              -->
            <div class="product-container">
                <!--创建spu  start  -->
                <div class="product-panel add_spu_on">
                    <div class="title-prompt">基础信息</div>
                    <form class="form-horizontal" action="{% if act is not defined %}/spu/add{% else %}/spu/edit{% endif %}" method="post" role="form" id="form_step1">
                        <div class="form-group">
                            <label class="col-sm-1 control-label no-padding-right"><span  class="text-red">*</span>SPU通用名</label>
                            <div class="col-sm-9">
                                <input type="text" name="spu_name" id="spu_name" value="{% if spu['spu_name'] is defined %}{{ spu['spu_name'] }} {% endif %}" class="col-xs-10 col-sm-5">
											<span class="help-inline col-xs-12 col-sm-7">
												<span class="middle">最多输入<span id="word_left">30</span>字</span>
											</span>
                            </div>
                        </div>
                        <div class="space-4"></div>
                        <div class="form-group">
                            <label class="col-sm-1 control-label no-padding-right" for="spu_supplier"><span  class="text-red">*</span>商家</label>
                            <div class="col-sm-5" style="width: 366px;">
                                <select name="shop_id" class="chosen-select col-xs-5 col-sm-5" id="spu_supplier" data-placeholder="请选择商家" style="display: none;">
                                    <option value=""></option>
                                    {% if supplier is defined %}
                                    {% for v in supplier %}
                                    <option  {% if spu['shop_id'] is defined and spu['shop_id'] == v['id'] %}selected{% endif %} value="{{v['id']}}">{{v['name']}}</option>
                                    {% endfor %}
                                    {% endif %}
                                </select>
                            </div>
                        </div>
                        <div class="space-4"></div>
                        <div class="form-group">
                            <label class="col-sm-1 control-label no-padding-right" for="spu_type"><span  class="text-red">*</span>品牌</label>
                            <div class="col-sm-5" style="width: 366px;">
                                <select name="brand_id" class="chosen-select col-xs-5 col-sm-5" id="spu_type" data-placeholder="请选择品牌" style="display: none;">
                                    <option value=""></option>
                                    {% if brand is defined %}
                                    {% for v in brand %}
                                    <option  {% if spu['brand_id'] is defined and spu['brand_id'] == v['id'] %}selected{% endif %} value="{{v['id']}}">{{v['brand_name']}}</option>
                                    {% endfor %}
                                    {% endif %}
                                </select>
                            </div>
                            <div style="height:32px;line-height:32px;"><a href="/brands/add" target="_blank">添加新品牌</a></div>
                        </div>
                        <div class="space-4"></div>
                        <div class="form-group">
                            <label class="col-sm-1 control-label no-padding-right" for="product_kind" ><span  class="text-red" >*</span>药物类型</label>
                            <div class="col-sm-9">
                                <select name="drug_type" class="col-xs-5 col-sm-5" id="product_kind">
                                    <option value="">--请选择--</option>
                                    <option {% if spu['drug_type'] is defined and spu['drug_type'] == 1 %}selected{% endif %} value="1">处方药</option>
                                    <option {% if spu['drug_type'] is defined and spu['drug_type'] == 2 %}selected{% endif %} value="2">红色非处方药</option>
                                    <option {% if spu['drug_type'] is defined and spu['drug_type'] == 3 %}selected{% endif %} value="3">绿色非处方药</option>
                                    <option {% if spu['drug_type'] is defined and spu['drug_type'] == 4 %}selected{% endif %} value="4">非药物</option>
                                    <option {% if spu['drug_type'] is defined and spu['drug_type'] == 5 %}selected{% endif %} value="5">虚拟商品</option>
                                </select>
                            </div>
                        </div>
                        <div class="space-4"></div>
                        <div class="form-group">
                            <label class="col-sm-1 control-label no-padding-right"  ><span  class="text-red">*</span>商品分类</label>
                            <label class="col-sm-9 control-label no-padding-right product-classify">
                                {% include "library/category.volt"%}
                            </label>
                        </div>
                        {% if act is defined %}<input id="spu_id" name="id" type="hidden" value="{{ spu['spu_id'] }}">{% endif %}
                        <div class="space-4"></div>
                        <div class="col-md-offset-3 col-md-9">
                            <button class="btn btn-info" type="button" id="save_spu_btn">保存spu</button>
                        </div>
                    </form>
                </div>
                <!--创建spu   end -->
            </div>
{% endblock %}

{% block footer %}

<!-- basic scripts -->

<!--<script src="http://{{ config.domain.static }}/assets/js/bootstrap.min.js"></script>-->

<!-- page specific plugin scripts -->
<script src="http://{{ config.domain.static }}/assets/js/jquery-ui.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/jquery.ui.touch-punch.min.js"></script>



<script src="http://{{ config.domain.static }}/assets/js/jquery-ui.custom.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/chosen.jquery.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/fuelux.spinner.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/bootstrap-datepicker.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/bootstrap-timepicker.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/moment.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/daterangepicker.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/bootstrap-datetimepicker.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/bootstrap-colorpicker.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/jquery.knob.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/jquery.autosize.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/jquery.inputlimiter.1.3.1.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/jquery.maskedinput.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/bootstrap-tag.min.js"></script>

<!-- page specific plugin scripts -->
<script src="http://{{ config.domain.static }}/assets/js/jquery.dataTables.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/jquery.dataTables.bootstrap.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/dataTables.tableTools.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/dataTables.colVis.min.js"></script>


<!-- ace scripts -->
<script src="http://{{ config.domain.static }}/assets/js/ace-elements.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/ace.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/jquery.dragsort-0.5.2.js"></script>
<script src="/js/kindeditor/kindeditor-min.js"></script>
<script src="/js/kindeditor/lang/zh_CN.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/ajaxfileupload.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/product.js"></script>

<script>
    jQuery(function($) {

        if(!ace.vars['touch']) {
            $('.chosen-select').chosen({allow_single_deselect:true});
            //resize the chosen on window resize

            $(window)
                    .off('resize.chosen')
                    .on('resize.chosen', function() {
                        $('.chosen-select').each(function() {
                            var $this = $(this);
                            $this.next().css({'width': $this.parent().width()});
                        })
                    }).trigger('resize.chosen');
            //resize chosen on sidebar collapse/expand
            $(document).on('settings.ace.chosen', function(e, event_name, event_val) {
                if(event_name != 'sidebar_collapsed') return;
                $('.chosen-select').each(function() {
                    var $this = $(this);
                    $this.next().css({'width': $this.parent().width()});
                })
            });


            $('#chosen-multiple-style .btn').on('click', function(e){
                var target = $(this).find('input[type=radio]');
                var which = parseInt(target.val());
                if(which == 2) $('#form-field-select-4').addClass('tag-input-style');
                else $('#form-field-select-4').removeClass('tag-input-style');
            });
        }

        //jquery tabs
        $( "#tabs" ).tabs();

        $( "#video-tabs" ).tabs();

        $( "#model-tabs" ).tabs();
        $( "#model-pc-tabs" ).tabs();
        $( "#model-mobile-tabs" ).tabs();

    });
    $(document).keypress(function (e) {
        if( e.which == 13 ){
            return false;
        }
    });
</script>
{% endblock %}