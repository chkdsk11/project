{% extends "layout.volt" %}

{% block content %}
<div class="page-content">

    <div class="row">
        <div class="col-xs-12">
            <!-- PAGE CONTENT BEGINS -->
            <div class="clearfix">
                <div class="pull-right tableTools-container"></div>
            </div>
            <form action="/attrname/list" method="get">
                <div class="tools-box" id="shop_category">
                    <label class="clearfix">
                        <span>选择分类：</span>
                        <select name="shop_category[]" id="one_category">
                            <option value="0">请选择</option>
                            {% if category is defined %}
                            {% for k,v in category %}
                                {% if categoryData['category'] is defined %}
                                <option value="{{ v['id'] }}" {% if in_array(v['id'], categoryData['category']) %}selected{% endif %} >{{ v['category_name'] }}</option>
                                {% else %}
                                <option value="{{ v['id'] }}">{{ v['category_name'] }}</option>
                                {% endif %}
                            {% endfor %}
                            {% endif %}
                        </select>
                    </label>
                    {% if categoryData is defined %}
                    <label class="clearfix">
                        <select name="shop_category[]" id="two_category">
                            {% for v in categoryData['two_category'] %}
                            <option value="{{ v['id'] }}" {% if in_array(v['id'], categoryData['category']) %}selected{% endif %} >{{ v['category_name'] }}</option>
                            {% endfor %}
                        </select>
                    </label>
                    <label class="clearfix">
                        <select name="shop_category[]" id="three_category">
                            {% for v in categoryData['three_category'] %}
                            <option value="{{ v['id'] }}" {% if in_array(v['id'], categoryData['category']) %}selected{% endif %} >{{ v['category_name'] }}</option>
                            {% endfor %}
                        </select>
                    </label>
                    {% else %}
                    <label class="clearfix">
                        <select name="shop_category[]" id="two_category" style="display: none;">

                        </select>
                    </label>
                    <label class="clearfix">
                        <select name="shop_category[]" id="three_category" style="display: none;">

                        </select>
                    </label>
                    {% endif %}

                    <button class="btn btn-primary btn-rig" type="button" id="addAttrValue">添加商品属性</button>
                    <button class="btn btn-primary btn-rig" type="button" id="import_attr">确认导入属性</button>
                    <label class="btn-rig">
                        <span class="btn btn-primary" style="cursor: pointer;">导入属性</span>
                        <span class="file-name">请选择导入的文件</span>
                        <input type="file" id="attr_file" class="js-file-name" name="attr_file" onchange="(function(input, tip) {
                             tip.html(input.get(0).files[0].name);
						})($(this), $(this).parent().find('.file-name'))"/>
                    </label>
                    <button type="button" class="btn btn-sm btn-purple btn-rig" onclick="location.href='http://{{ config.domain.static }}/assets/csv/goodsattr.csv'">模板下载</button>

                </div>
            </form>
            <div class="table-header">
                商品属性列表
            </div>
            <!-- div.table-responsive -->

            <!-- div.dataTables_borderWrap -->
            <div>
                <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>属性名称</th>
                        <th>属性值</th>
                        <th>是否必填</th>
                        <th>是否启用</th>
                        <th style="width:65px;">操作</th>
                    </tr>
                    </thead>

                    <tbody>
                    {% if attrData is defined and attrData != '' %}
                    {% for v in attrData %}
                    <tr>
                        <td>{{ v['id'] }}</td>
                        <td>{{ v['attr_name'] }}</td>
                        <td>{{ v['attr_value'] }}</td>
                        <td>
                            <i class="ace-icon glyphicon btn-xs btn-{% if v['is_null'] == 1 %}info glyphicon-ok{% else %}danger glyphicon-remove{% endif %} is_null" data-id="{{ v['id'] }}" data="{{ v['is_null'] }}" style="cursor: pointer;"></i>
                        </td>
                        <td>
                            <i class="ace-icon glyphicon btn-xs btn-{% if v['status'] == 1 %}info glyphicon-ok{% else %}danger glyphicon-remove{% endif %} status" data-id="{{ v['id'] }}" data="{{ v['status'] }}" style="cursor: pointer;"></i>
                        </td>
                        <td>
                            <div class="hidden-sm hidden-xs action-buttons">
                                <a class="green" href="/attrname/edit?id={{ v['id'] }}&category_id={{ category_id }}" title="编辑">
                                    <i class="ace-icon fa fa-pencil bigger-130"></i>
                                </a>
                                <a class="red del" href="javascript:;" data-id="{{ v['id'] }}" title="删除">
                                    <i class="ace-icon fa fa-trash-o bigger-130"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    {% endfor %}
                    {% else %}
                    <tr>
                        <td colspan="30">暂无数据</td>
                    </tr>
                    {% endif %}
                    </tbody>
                </table>
            </div>
        </div><!-- /.col -->
    </div><!-- /.row -->

</div><!-- /.page-content -->
{% endblock %}

{% block footer %}
<script src="http://{{ config.domain.static }}/assets/js/ajaxfileupload.js"></script>
<script src="http://{{ config.domain.static }}/assets/admin/js/attrname/globalAttrName.js"></script>
{% endblock %}