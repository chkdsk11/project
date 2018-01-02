{% extends "layout.volt" %}

{% block content %}
            <div class="page-content">

                <div class="row">
                    <div class="col-xs-12">
                        <!-- PAGE CONTENT BEGINS -->
                        <div class="clearfix">
                            <div class="pull-right tableTools-container"></div>
                        </div>
                        <div class="table-header">
                            品牌列表
                        </div>
                        <form action="/brands/list" method="get">
                            <div class="tools-box">
                                <label class="clearfix">
                                    <span>品牌名称或ID：</span>
                                    <input type="text" name="brand_name" placeholder="输入品牌名称或ID" class="tools-txt" value="{{ brand_name }}">
                                    <button class="btn btn-primary">搜索</button>
                                </label>
                                <label class="clearfix">
                                    <span>是否是热门推荐：</span>
                                    <select name="is_hot" class="tools-txt">
                                        <option value="-1">全部</option>
                                        <option value="1" {% if is_hot == 1 %}selected{% endif %}>是</option>
                                        <option value="0" {% if is_hot == 0 %}selected{% endif %}>否</option>
                                    </select>
                                    <button class="btn btn-primary" type="submit">搜索</button>
                                </label>
                                <button class="btn btn-primary btn-rig" type="button" onclick="location.href='/brands/add'">添加品牌</button>

                            </div>
                        </form>
                        <!-- div.table-responsive -->

                        <!-- div.dataTables_borderWrap -->
                        <div>
                            <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th>品牌ID</th>
                                    <th>品牌名称</th>
                                    <th>热门推荐</th>
                                    <th>品牌排序</th>
                                    <th>添加时间</th>
                                    <th>操作</th>
                                </tr>
                                </thead>

                                <tbody>

                                <!-- 遍历商品信息 -->
                                {% for v in brand['list'] %}
                                <tr>
                                    <td>{{ v['id'] }}</td>
                                    <td>{{ v['brand_name'] }}</td>
                                    <td>
                                        <i class="ace-icon glyphicon btn-xs btn-{% if v['is_hot'] == 1 %}info glyphicon-ok{% else %}danger glyphicon-remove{% endif %} is_hot" data-id="{{ v['id'] }}" data="{{ v['is_hot'] }}" style="cursor: pointer;"></i>
                                    </td>
                                    <td>    <div class="tools-box">
                                        <label class="clearfix">
                                            <input type="text" name="sort" placeholder=""  class="tools-txt" value="{{ v['brand_sort'] }}">
                                        </label>
                                        <label class="clearfix">
                                            <button class="btn btn-primary update" brand_id="{{ v['id'] }}" }}" type="button">保存</button>
                                        </label>
                                    </div></td>
                                    <td>{{ date('Y-m-d H:i:s', v['add_time']) }}</td>
                                    <td>
                                        <div class="hidden-sm hidden-xs action-buttons">
                                            <a class="green" href="/brands/edit?id={{ v['id'] }}" title="编辑">
                                                <i class="ace-icon fa fa-pencil bigger-130"></i>
                                            </a>
                                            <!--<a class="red del" href="javascript:;" data-id="{{ v['id'] }}" title="删除">
                                                <i class="ace-icon fa fa-trash-o bigger-130"></i>
                                            </a>-->
                                        </div>
                                    </td>
                                </tr>
                                {% else %}
                                <tr>
                                    <td colspan="30">暂无数据</td>
                                </tr>
                                {% endfor %}
                                <!-- 遍历商品信息 end -->
                                </tbody>
                            </table>
                        </div>
                    </div><!-- /.col -->
                </div><!-- /.row -->

			</div><!-- /.page-content -->
            {{ brand['page'] }}
{% endblock %}

{% block footer %}
<script src="/js/kindeditor/kindeditor-min.js"></script>
<script src="/js/kindeditor/lang/zh_CN.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/ajaxfileupload.js"></script>
<script src="http://{{ config.domain.static }}/assets/admin/js/brands/globalBrands.js"></script>
{% endblock %}