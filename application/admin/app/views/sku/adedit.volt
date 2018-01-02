{% extends "layout.volt" %}

{% block content %}
<style>
    .form-group {
        margin-top:10px;
    }
</style>
    <div class="page-header">
        {% if act is not defined %}
        <h1>商品广告模板添加</h1>
        {% else %}
            <h1>编辑商品广告模板</h1>
        {% endif %}
    </div>
    <div class="row">
        <div class="col-xs-12">
            <!-- PAGE CONTENT BEGINS -->
            <form id="form" class="form-horizontal" action="{% if act is not defined %}/sku/adadd{% else %}/sku/adedit{% endif %}" method="post">

                <div class="space-6"></div>
                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right">广告模板名称</label>
                    <div class="col-sm-9">
                        <div class="pos-rel">
                            <input dir="auto" style="position: relative; vertical-align: top; background-color: transparent;" spellcheck="false" autocomplete="off" class="typeahead scrollable tt-input" placeholder="不能为空" name="ad_name" value="{% if ad['ad_name'] is defined %}{{ ad['ad_name'] }}{% endif %}" type="text">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right">是否启用</label>
                    <div class="col-sm-9">
                        <div class="pos-rel">
                            <select name="is_show" {% if isLimit is 1 %}disabled{% endif %}>
                                <option value="1" {% if ad['is_show'] is defined and ad['is_show'] is 1 %}selected{% endif %}>启用</option>
                                <option value="0" {% if ad['is_show'] is defined and ad['is_show'] is not 1 %}selected{% endif %}>暂停</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right">平台</label>
                    <div class="col-sm-9">
                        <div class="pos-rel">
                            <select name="platform" {% if isLimit is 1 %}disabled{% endif %}>
                                <option value="pc" {% if ad['platform'] is defined and ad['platform'] is 'pc' %}selected{% endif %}>PC端</option>
                                <option value="mobile" {% if ad['platform'] is defined and ad['platform'] is 'mobile' %}selected{% endif %}>移动端</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right">广告内容</label>
                    <div class="col-sm-9">
                        <div class="pos-rel">
                            <textarea id="skuad_desc" name="content">{% if ad['content'] is defined %}{{ ad['content'] }}{% endif %}</textarea>
                        </div>
                    </div>
                </div>

                {% if act is defined %}
                <input type="hidden" name="id" value="{{ ad['id'] }}">
                {% endif %}
                <div class="">
                    <div class="col-md-offset-3 col-md-9">
                        <button class="btn btn-info ajax_button" type="submit">
                            <i class="ace-icon fa fa-check bigger-110"></i>
                            保存
                        </button>
                        {% if act is defined %}
                        &nbsp; &nbsp; &nbsp;
                        <a href="javascript:void(0)" onclick="javascript:history.go(-1);return false;">
                            <button class="btn" type="reset">
                                <i class="ace-icon fa fa-undo bigger-110"></i>
                                返回
                            </button>
                        </a>
                        {% endif %}
                    </div>
                </div>
            </form>

        </div><!-- /.col -->
    </div>
{% endblock %}

{% block footer %}
    <script src="/js/kindeditor/kindeditor-min.js"></script>
    <script src="/js/kindeditor/lang/zh_CN.js"></script>
    <script src="http://{{ config.domain.static }}/assets/js/ajaxfileupload.js"></script>
    <script src="http://{{ config.domain.static }}/assets/admin/js/skuad/skuad.js"></script>
{% endblock %}