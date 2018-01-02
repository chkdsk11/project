{% extends "layout.volt" %}

{% block content %}
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/jquery.datetimepicker.css" class="ace-main-stylesheet" />
<!--放页面内容-->
<div class="page-content">
    <div class="page-header">
        <h1>商品标签列表</h1>
    </div>


    <div class="row">

        <div class="top-search">
            <form class="form-inline">
                <div class="form-group">
                    <label>标签名称：</label>
                    <input type="text" class="form-control" id="exampleInputName2" placeholder="用券减50" name="tag_name" value="{% if tag_name is defined %}{{ tag_name }}{%endif%}">
                </div>
                &nbsp;&nbsp;
                <div class="form-group">
                    <label>组件状态：</label>
                    <select class="form-control" name='status'>
                        <option>全部</option>
                        {% if arr is defined %}
                        {% for k,v in arr %}
                        <option value="{{k}}" {% if status is defined AND status == k %}selected{% endif %}>{{k}}</option>
                        {% endfor %}
                        {% endif %}
                    </select>
                </div>
                &nbsp;&nbsp;
                <button type="submit" class="btn btn-info">搜索</button>
            </form>
        </div>

        <div class="special-table">
            <table id="simple-table" class="table  table-bordered table-hover">
                <thead>
                <tr>
                    <th class="detail-col">ID</th>
                    <th>标签名称</th>
                    <th>是否启用</th>
                    <th>操作</th>
                    <th>生效时间</th>
                </tr>
                </thead>

                <tbody>
                <!-- 遍历组件活动信息 -->
                {% if tagList['list'] is defined and tagList['list'] != 0  %}
                {% for k,v in tagList['list'] %}
                <tr>
                    <td class="center">
                        {{ v['id'] }}
                    </td>
                    <td>
                        {{ v['tag_name'] }}
                    </td>
                    <td class="hidden-480">
                        <label class="pos-rel">
                            <input type="checkbox" class="ace goods-price-tag" value="{{ v['id'] }}" name="status[{{v['id']}}]" {% if v['status'] == '启用' %}checked="true"{% endif %}>
                            <span class="lbl"></span>
                        </label>
                    </td>
                    <td>
                        <div class="hidden-sm hidden-xs action-buttons">
                            <a class="green" href="/subjecttag/editGood?id={{ v['id'] }}" title="编辑">
                                <i class="ace-icon fa fa-pencil bigger-130"></i>
                            </a>
                        </div>
                    </td>
                    <td>
                        {{ v['update_time'] }}
                    </td>
                </tr>
                {% endfor %}
                {% elseif tagList['res'] is defined and tagList['res'] == 'error'  %}
                <tr>
                    <td colspan="5" align="center">暂时无数据...</td>
                </tr>
                {% endif %}
                </tbody>
            </table>
            {% if tagList['page'] is defined and tagList['list'] != 0 %}
            {{ tagList['page'] }}
            {% endif %}
            <div class="clearfix form-actions">
                <div class="col-md-12">
                    <a href="/subjecttag/editGood">
                        <button class="btn btn-info" type="button">
                            <i class="ace-icon fa fa-check bigger-110"></i>
                            添加标签
                        </button>
                    </a>
                </div>
            </div>
        </div>

    </div>



    <!-- /.page-header -->
</div>
<!-- /.page-content -->

{% endblock %}

{% block footer %}
<script src="http://{{ config.domain.static }}/assets/js/jquery.datetimepicker.js"></script>
<script src="http://{{ config.domain.static }}/assets/admin/js/promotion/promotionList.js"></script>
<script src="http://{{ config.domain.static }}/assets/admin/js/subject/widget-list.js"></script>
{% endblock %}