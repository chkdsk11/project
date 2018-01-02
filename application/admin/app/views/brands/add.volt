{% extends "layout.volt" %}

{% block content %}
<style type="text/css">
    img{width:140px; display:none;}
    .text-red{ color: red;}
</style>
<div class="main-container" id="main-container">
    <div class="main-content">
        <div class="main-content-inner">
            <form id="form" action="/brands/add" method="post">
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        添加品牌
                    </h1>
                </div><!-- /.page-header -->
                <div class="row">
                    <div class="col-xs-12">
                        <!-- PAGE CONTENT BEGINS -->
                        <div class="form-horizontal">
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> <span class="text-red">*</span>品牌名称： </label>

                                <div class="col-sm-9">
                                    <input type="text" id="brand_name" name="brand_name" class="col-xs-10 col-sm-5" placeholder="不可为空" />
                                    <span class="tigs" id="brand_tig"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                    <div class="page-header">
                        <h1>
                            移动端品牌街设置
                        </h1>
                    </div><!-- /.basic -->
                    <div class="row">
                    <div class="col-xs-12">
                        <!-- PAGE CONTENT BEGINS -->
                        <div class="form-horizontal">
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> logo： </label>

                                <div class="col-sm-9">
                                    <input type="file" id="move_logo" name="move_logo" data-img="brand_logo" />
                                    <img src="" id="brand_logo" class="img-rounded">
                                    <input type="hidden" name="brand_logo" />
                                    <span class="tigs">（180px*126px）</span>
                                </div>
                            </div>

                            <div class="space-4"></div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> 品牌街列表图： </label>

                                <div class="col-sm-9">
                                    <input type="file" id="move_image" name="move_image" data-img="list_image" />
                                    <img src="" id="list_image" class="img-rounded">
                                    <input type="hidden" name="list_image" />
                                    <span class="tigs">（640px*320px）</span>
                                </div>
                            </div>

                            <div class="space-4"></div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> 专场标题： </label>

                                <div class="col-sm-9">
                                    <input type="text" id="mon_title" name="mon_title" class="col-xs-10 col-sm-5" placeholder="" />
                                    <span class="tigs">（限15字）</span>
                                </div>
                            </div>

                            <div class="space-4"></div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> 品牌描述： </label>

                                <div class="col-sm-9">
                                    <input type="text" name="brand_describe" class="col-xs-10 col-sm-5" placeholder="" />
                                    <span class="tigs">（限20字）</span>
                                </div>
                            </div>

                            <div class="space-4"></div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> 品牌街排序： </label>

                                <div class="col-sm-9">
                                    <input type="text" name="sort" class="col-xs-10 col-sm-1" value="0" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> 品牌排序： </label>

                                <div class="col-sm-9">
                                    <input type="text" name="brand_sort" class="col-xs-10 col-sm-1" value="1" />
                                </div>
                            </div>


                            <div class="space-4"></div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> 是否热销： </label>
                                <div class="col-xs-12 col-sm-9">
                                    <label class="radio-inline">
                                        <input type="radio" name="is_hot" value="1" checked>是
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" name="is_hot" value="0">否
                                    </label>
                                </div>
                            </div>

                            <div class="space-4"></div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> 是否启用： </label>
                                <div class="col-xs-12 col-sm-9">
                                    <label class="radio-inline">
                                        <input type="radio" name="status" value="1" checked>是
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" name="status" value="0">否
                                    </label>
                                </div>
                            </div>
                            </div>
                    </div>
                    </div><!-- /.main-content -->
                <!-- /.main-content -->
                <div class="row">
                    <div class="col-xs-12">
                        <!-- PAGE CONTENT BEGINS -->
                        <div class="form-horizontal">
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> 详情内容： </label>

                                <div class="col-sm-9">
                                    <textarea id="brand_desc" name="brand_desc"></textarea>
                                </div>
                            </div>
                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <button id="addFullMinues" class="btn btn-info" type="submit">
                                        <i class="ace-icon fa fa-check bigger-110"></i>
                                        确认添加
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        </div>
</div><!-- /.main-container -->

{% endblock %}

{% block footer %}
<script src="/js/kindeditor/kindeditor-min.js"></script>
<script src="/js/kindeditor/lang/zh_CN.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/ajaxfileupload.js"></script>
<script type="text/javascript">
    var url = '/brands/add',brandName = '';
</script>
<script src="http://{{ config.domain.static }}/assets/admin/js/brands/globalBrands.js"></script>
{% endblock %}