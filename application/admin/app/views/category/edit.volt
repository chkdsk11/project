{% extends "layout.volt" %}

{% block content %}
<style>
    .form-group {
        margin-top:10px;
    }
</style>
    <div class="page-header">
        {% if act is not defined %}
        <h1>添加分类</h1>
        {% else %}
            <h1>修改分类</h1>
        <input type="hidden" name="id" value="{{ categoryOne['id'] }}">
        {% endif %}
    </div>
    <div class="row">
        <div class="col-xs-12">
            <!-- PAGE CONTENT BEGINS -->
            <form id="form" class="form-horizontal" action="{% if act is not defined %}/category/add{% else %}/category/edit{% endif %}" method="post">

                <div class="form-group">
                    <label class="control-label col-xs-12 col-sm-3 no-padding-right">上级分类</label>

                    <div class="col-xs-12 col-sm-9 {% if act is not defined %}selectOption{% endif %}">
                        <select id="select1" {% if act is defined %}disabled{% endif %} name="pid">
                            <option value="0">--顶级分类--</option>

                            {% for v in category %}
                                <option data-value="{{ v['level'] }}" {% if isset(pid) and pid is v['id'] %}selected{% endif %} {% if isset(categoryOne['pid']) and categoryOne['pid'] is v['id'] %}selected{% endif %} value="{{ v['id'] }}">|--{{ v['category_name'] }}</option>
                                {% if v['son'] is defined %}
                                    {% for v1 in v['son'] %}
                                    <option data-value="{{ v1['level'] }}" {% if isset(pid) and pid is v1['id'] %}selected{% endif %} {% if isset(categoryOne['pid']) and categoryOne['pid'] is v1['id'] %}selected{% endif %} value="{{ v1['id'] }}">&nbsp;&nbsp;&nbsp;|---{{ v1['category_name'] }}</option>
                                        {% if v1['son'] is defined %}
                                        {% for v2 in v1['son'] %}
                                        <option data-value="{{ v2['level'] }}" {% if isset(pid) and pid is v2['id'] %}selected{% endif %} {% if isset(categoryOne['pid']) and categoryOne['pid'] is v2['id'] %}selected{% endif %} value="{{ v2['id'] }}">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|---{{ v2['category_name'] }}</option>
                                            {% if v2['son'] is defined %}
                                            {% for v3 in v2['son'] %}
                                            <option data-value="{{ v3['level'] }}" {% if isset(pid) and pid is v3['id'] %}selected{% endif %} {% if isset(categoryOne['pid']) and categoryOne['pid'] is v3['id'] %}selected{% endif %} value="{{ v3['id'] }}">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|---{{ v3['category_name'] }}</option>
                                            {% endfor %}
                                            {% endif %}
                                        {% endfor %}
                                        {% endif %}
                                    {% endfor %}
                                {% endif %}
                            {% endfor %}

                        </select>
                    </div>
                </div>

                <div class="hr hr-16 hr-dotted"></div>
                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right">分类名称</label>

                    <div class="col-sm-9">
                        <div class="pos-rel">
                            <input dir="auto" style="position: relative; vertical-align: top; background-color: transparent;" spellcheck="false" autocomplete="off" class="typeahead scrollable tt-input" placeholder="不能为空" name="category_name" value="{% if categoryOne['category_name'] is defined %}{{ categoryOne['category_name'] }} {% endif %}" type="text">
                            <span style=" height:37px; line-height:37px;margin-left: 10px;">限10字</span>
                        </div>
                    </div>
                </div>

                <div class="space-6"></div>
                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right">别名(在前端显示)</label>

                    <div class="col-sm-9">
                        <div class="pos-rel">
                            <input dir="auto" style="position: relative; vertical-align: top; background-color: transparent;" spellcheck="false" autocomplete="off" class="typeahead scrollable tt-input" name="alias" value="{% if categoryOne['alias'] is defined %}{{ categoryOne['alias'] }} {% endif %}" type="text">

                        </div>
                    </div>
                </div>
<!--<div id="productRule1">-->

<!--</div>-->
                <div class="space-6"></div>
                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right">SEO-网页标题</label>

                    <div class="col-sm-9">
                        <div class="pos-rel">
                            <input dir="auto" style="position: relative; vertical-align: top; background-color: transparent;" spellcheck="false" autocomplete="off" class="typeahead scrollable tt-input" placeholder="" name="meta_title" value="{% if categoryOne['meta_title'] is defined %}{{ categoryOne['meta_title'] }} {% endif %}" type="text">
                        </div>
                    </div>
                </div>

                <div class="space-6"></div>

                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right">SEO-关键字</label>

                    <div class="col-sm-9">
                        <div class="pos-rel">
                            <input dir="auto" style="position: relative; vertical-align: top; background-color: transparent;" spellcheck="false" autocomplete="off" class="typeahead scrollable tt-input" placeholder="" name="meta_keyword" value="{% if categoryOne['meta_keyword'] is defined %}{{ categoryOne['meta_keyword'] }} {% endif %}" type="text">
                        </div>
                    </div>
                </div>

                <div class="space-6"></div>

                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right">SEO-网页描述</label>

                    <div class="col-sm-9">
                        <div class="pos-rel">
                            <input dir="auto" style="position: relative; vertical-align: top; background-color: transparent;" spellcheck="false" autocomplete="off" class="typeahead scrollable tt-input" placeholder="" name="meta_description" value="{% if categoryOne['meta_description'] is defined %}{{ categoryOne['meta_description'] }} {% endif %}" type="text">

                        </div>
                    </div>
                </div>
                {% if act is defined %}
                <input type="hidden" name="id" value="{{ categoryOne['id'] }}">
                {% endif %}
                <div class="">
                    <div class="col-md-offset-3 col-md-9">
                        <button class="btn btn-info ajax_button" type="button">
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

            <!-- PAGE CONTENT ENDS -->
        </div><!-- /.col -->

    </div>

{% endblock %}

{% block footer %}
    <script>
            $('.ajax_button').on('click',function () {
                var act = true;
                var name = $('input[name="category_name"]').val();
                if(name.length > 10){
                    act = false;
                    layer_required('分类名称长度不能大于10');
                }
                if(act){
                    ajaxSubmit('form');
                }
            });
            $(document).keypress(function (e) {
                if( e.which == 13 ){
                    return false;
                }
            });

    </script>
{% endblock %}