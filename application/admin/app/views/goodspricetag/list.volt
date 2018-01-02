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
                            会员标签列表
                        </div>
                        <form action="/goodspricetag/list" method="get">
                            <div class="tools-box">
                                <label class="clearfix">
                                    <span>标签名称：</span>
                                    <input type="text" name="tag_name" placeholder="输入标签名称" class="tools-txt" value="{{ seaData['tag_name'] }}">
                                </label>
                                <label class="clearfix">
                                    <span>状态：</span>
                                    <select name="status" class="tools-txt">
                                        <option value="-1">全部</option>
                                        <option value="1" {% if seaData['status'] == 1 %}selected{% endif %}>开启</option>
                                        <option value="0" {% if seaData['status'] == 0 %}selected{% endif %}>关闭</option>
                                    </select>
                                    <button class="btn btn-primary" type="submit">搜索</button>
                                </label>
                                <a href="/goodspricetag/add"><button class="btn btn-primary btn-rig" type="button">添加标签</button></a>
                            </div>
                        </form>
                        <!-- div.table-responsive -->

                        <!-- div.dataTables_borderWrap -->
                        <div>
                            <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>会员标签</th>
                                    <th>商品数量</th>
                                    <th>是否开启</th>
                                    <th>备注</th>
                                    <th>更新时间</th>
                                    <th>操作</th>
                                </tr>
                                </thead>

                                <tbody>

                                <!-- 遍历商品信息 -->
                                {% for v in data['list'] %}
                                <tr>
                                    <td>{{ v['tag_id'] }}</td>
                                    <td>{{ v['tag_name'] }}</td>
                                    <td>{{ v['goods_number'] }}</td>
                                    <td>
                                        <i class="ace-icon glyphicon btn-xs btn-{% if v['status'] == 1 %}info glyphicon-ok{% else %}danger glyphicon-remove{% endif %} status" data-id="{{ v['tag_id'] }}" data="{{ v['status'] }}" style="cursor: pointer;"></i>
                                    </td>
                                    <td>{{ v['remark'] }}</td>
                                    <td>{{ date('Y-m-d H:i:s', v['add_time']) }}</td>
                                    <td>
                                        <div class="hidden-sm hidden-xs action-buttons">
                                            <a class="green" href="/goodspricetag/edit?id={{ v['tag_id'] }}" title="编辑">
                                                <i class="ace-icon fa fa-pencil bigger-130"></i>
                                            </a>
                                            <a class="red del" href="javascript:;" data-id="{{ v['tag_id'] }}" title="删除">
                                                <i class="ace-icon fa fa-trash-o bigger-130"></i>
                                            </a>
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
            {{ data['page'] }}
{% endblock %}

{% block footer %}
<script src="http://{{ config.domain.static }}/assets/admin/js/goodspricetag/globalGoodsPriceTag.js"></script>
{% endblock %}