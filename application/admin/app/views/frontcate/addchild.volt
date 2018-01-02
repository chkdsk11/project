{% extends "layout.volt" %}

{% block path %}
<li class="active">前台分类管理</li>
<li class="active"><a href="/frontcate/applist">app分类管理</a></li>
{% if act is not defined %}
<li class="active"><a href="/frontcate/addchild">添加子分类</a></li>
{% else %}
<li class="active">编辑分类</li>
{% endif %}
{% endblock %}

{% block content %}
<style>
    .form-group {
        margin-top:10px;
    }
    .text-red {
        color: red;
    }
</style>
    <div class="page-header">
        {% if act is not defined %}
        <h1>添加分类</h1>
        {% else %}
            <h1>修改分类</h1>
        <input type="hidden" name="id" value="{{ category[0]['id'] }}">
        {% endif %}
    </div>
    <div class="row">
        <div class="col-xs-12">
            <!-- PAGE CONTENT BEGINS -->
            <form id="form" class="form-horizontal" action="{% if act is not defined %}/frontcate/addchild{% else %}/category/edit{% endif %}" method="post">

                <div class="form-group">
                    <label class="control-label col-xs-12 col-sm-3 no-padding-right">上级分类</label>

                    <div class="col-xs-12 col-sm-9">
                        <input type="text" disabled value="{{ category[0]['category_name'] }}">
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-xs-12 col-sm-3 no-padding-right"><span class="text-red">*</span>关联后台分类</label>

                    <div class="col-xs-12 col-sm-9 {% if act is not defined %}selectOption{% endif %}">
                        <select id="select2" {% if act is defined %}disabled{% endif %} name="product_category_id">
                            <option value="0">--顶级分类--</option>

                            {% for v in bcategory %}
                            <option data-value="{{ v['level'] }}" value="{{ v['id'] }}">|--{{ v['category_name'] }}</option>
                            {% if v['son'] is defined %}
                            {% for v1 in v['son'] %}
                            <option data-value="{{ v1['level'] }}" value="{{ v1['id'] }}">&nbsp;&nbsp;&nbsp;|---{{ v1['category_name'] }}</option>
                            {% if v1['son'] is defined %}
                            {% for v2 in v1['son'] %}
                            <option data-value="{{ v2['level'] }}" value="{{ v2['id'] }}">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|---{{ v2['category_name'] }}</option>
                            {% if v2['son'] is defined %}
                            {% for v3 in v2['son'] %}
                            <option data-value="{{ v3['level'] }}" value="{{ v3['id'] }}">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|---{{ v3['category_name'] }}</option>
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

                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right">短名称</label>

                    <div class="col-sm-9">
                        <div class="pos-rel">
                            <input dir="auto" style="position: relative; vertical-align: top; background-color: transparent;" spellcheck="false" autocomplete="off" class="typeahead scrollable tt-input" placeholder="不能为空" name="nickname" value="{% if categoryOne['nickname'] is defined %}{{ categoryOne['nickname'] }} {% endif %}" type="text">
                            <span style=" height:37px; line-height:37px;margin-left: 10px;">限10字</span>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right">商品类型</label>

                    <div class="col-sm-9">
                        <div class="pos-rel">
                            <div class="col-xs-12 col-sm-9">
                                <select id="select1" name="product_type_id">
                                    {% for v in types %}
                                    <option value="{{ v['product_type_id'] }}">{{ v['type_name'] }}</option>
                                    {% endfor %}
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-6"></div>
                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right">是否主分类</label>

                    <div class="col-sm-9">
                        <div class="pos-rel">
                            <select id="select3"  name="main_category">
                                <option value="0">否</option>
                                <option value="1">是</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="space-6"></div>

                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right">是否启用</label>

                    <div class="col-sm-9">
                        <div class="pos-rel">
                            <div class="pos-rel">
                                <select id="select4"  name="enable">
                                    <option value="0">不启用</option>
                                    <option value="1">启用</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-6"></div>

                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right">排序</label>

                    <div class="col-sm-9">
                        <div class="pos-rel">
                            <input dir="auto" style="position: relative; vertical-align: top; background-color: transparent;" spellcheck="false" autocomplete="off" class="typeahead scrollable tt-input" value='0' placeholder="" name="sort" value="{% if categoryOne['sort'] is defined %}{{ categoryOne['sort'] }} {% endif %}" type="text">

                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right"> <span class="text-red">*</span>列表页图片： </label>

                    <div class="col-sm-9">
                        <input type="file" id="move_logo" name="move_logo" data-img="picture" />
                        <img src="" id="picture" class="img-rounded">
                        <input type="hidden" name="picture" />
                        <span class="tigs">（300px*300px以上正方形）</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right"> <span class="text-red">*</span>大图路径： </label>

                    <div class="col-sm-9">
                        <input type="file" id="pc_logo" name="pc_logo" data-img="image" />
                        <img src="" id="image" class="img-rounded">
                        <input type="hidden" name="image" />
                        <span class="tigs">（300px*300px以上正方形）</span>
                    </div>
                </div>

                <div class="hr hr-16 hr-dotted"></div>
                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right">SEO-页面标题</label>

                    <div class="col-sm-9">
                        <div class="pos-rel">
                            <input dir="auto" style="position: relative; vertical-align: top; background-color: transparent;" spellcheck="false" autocomplete="off" class="typeahead scrollable tt-input" placeholder="SEO-页面标题" name="seo_title" value="{% if categoryOne['seo_title'] is defined %}{{ categoryOne['seo_title'] }} {% endif %}" type="text">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right">SEO-页面描述</label>

                    <div class="col-sm-9">
                        <div class="pos-rel">
                            <input dir="auto" style="position: relative; vertical-align: top; background-color: transparent;" spellcheck="false" autocomplete="off" class="typeahead scrollable tt-input" placeholder="SEO-页面描述" name="seo_keywords" value="{% if categoryOne['seo_keywords'] is defined %}{{ categoryOne['seo_keywords'] }} {% endif %}" type="text">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right">SEO-页面关键字</label>

                    <div class="col-sm-9">
                        <div class="pos-rel">
                            <input dir="auto" style="position: relative; vertical-align: top; background-color: transparent;" spellcheck="false" autocomplete="off" class="typeahead scrollable tt-input" placeholder="SEO-页面关键字" name="seo_description" value="{% if categoryOne['seo_description'] is defined %}{{ categoryOne['seo_description'] }} {% endif %}" type="text">
                        </div>
                    </div>
                </div>

                <input type="hidden" name="pid" value="{{ category[0]['id'] }}">
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
<script src="/js/kindeditor/kindeditor-min.js"></script>
<script src="/js/kindeditor/lang/zh_CN.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/ajaxfileupload.js"></script>
    <script>

        /**
         * Created by Administrator on 2016/8/26.
         */
        //上传图片
        $(document).on('change', '#move_logo,#move_image,#pc_logo,#pc_image', function(){

            uploadFile('/frontcate/upload', $(this).attr('id'), $(this).attr('data-img'));
        });

            $('.ajax_button').on('click',function () {
                var act = true;
                var name = $('input[name="category_name"]').val();
                var nickname = $('input[name="nickname"]').val();
                var _link = $('input[name="category_link"]').val();
                var _sort = $('input[name="sort"]').val();
                var _select2 = $('#select2').val();
                if(_select2 == '0'){
                    act = false;
                    layer_required('请选择对应的关联后台分类');
                }
                if(name.length > 10 || name.length < 1){
                    act = false;
                    layer_required('分类名称长度不能大于10或为空');
                }
                if(nickname.length > 10 || nickname.length < 1){
                    act = false;
                    layer_required('短分类名称长度不能大于10或为空');
                }
                if(_sort.length < 1){
                    act = false;
                    layer_required('排序值不可小于0或为空');
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