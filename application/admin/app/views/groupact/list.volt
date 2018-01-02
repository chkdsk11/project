{% extends "layout.volt" %}

{% block path %}
    <li class="active">促销管理</li>
    <li class="active"><a href="/groupact/list">拼团管理</a></li>
    <li class="active">拼团活动列表</li>
{% endblock %}

{% block content %}
    <div class="page-content">
        <div class="row">
            <div class="col-xs-12">
                <!-- PAGE CONTENT BEGINS -->
                <div class="clearfix">
                    <div class="pull-right tableTools-container"></div>
                </div>
                <div class="table-header">
                    拼团活动列表
                </div>
                <!-- 搜索 -->
                <form action="" method="get">
                    <div class="tools-box">
                        <label class="clearfix">
                            <span>活动状态：</span>
                            <select class="tools-txt" name="gfastate">
                                <option value="">全部</option>
                                <option value="1" {% if seaData['gfastate'] == 1 %}selected{% endif %}>进行中</option>
                                <option value="s0" {% if seaData['gfastate'] == 's0' %}selected{% endif %}>未开始</option>
                                <option value="2" {% if seaData['gfastate'] == 2 %}selected{% endif %}>已结束</option>
                                <option value="3" {% if seaData['gfastate'] == 3 %}selected{% endif %}>已取消</option>
                            </select>
                        </label>

                        <label class="clearfix">
                            <span>商品名称或ID：</span>
                            <input type="text" name="goods" placeholder="" class="tools-txt" value="{{ seaData['goods'] }}">
                        </label>
                        <label class="clearfix">
                            <span>成团人数：</span>
                            <input type="text" name="gfanum" placeholder="" class="tools-txt" value="{{ seaData['gfanum'] }}">
                        </label>
                        <label class="clearfix">
                            <span>参团用户：</span>
                            <select class="tools-txt" name="gfa_user_type">
                                <option value="">选择参团用户类型</option>
                                <option value="s0" {% if seaData['gfa_user_type'] == 's0' %}selected{% endif %}>新老用户</option>
                                <option value="1" {% if seaData['gfa_user_type'] == 1 %}selected{% endif %}>新用户</option>
                            </select>
                            <button class="btn btn-primary" type="submit">搜索</button>
                        </label>
                        <label class="clearfix" style="float: right;">
                            <button class="btn btn-primary" type="button" onclick="location.href='/groupact/add'">添加活动</button>
                        </label>
                    </div>
                </form>

                <!-- div.dataTables_borderWrap -->
                <div>
                    <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>活动编号</th>
                                <th>商品ID</th>
                                <th>商品名称</th>
                                <th>拼团价格</th>
                                <th>成团人数</th>
                                <th>参团用户</th>
                                <th>开始时间</th>
                                <th>结束时间</th>
                                <th>排序</th>
                                <th>活动状态</th>
                                <th>前台连接</th>
                                <th>操作</th>
                            </tr>
                        </thead>

                        <tbody>
                            <!-- 遍历商品信息 -->
                            {% if data['list'] is defined and data['list'] is not empty %}
                                {% for v in data['list'] %}
                                    <tr>
                                        <td>{{ v['gfa_id'] }}</td>
                                        <td>{{ v['goods_id'] }}</td>
                                        <td style="max-width: 180px;">{{ v['goods_name'] }}</td>
                                        <td>{{ v['gfa_price'] }}</td>
                                        <td>{{ v['gfa_user_count'] }}</td>
                                        <td>{% if v['gfa_user_type'] == 1 %}新用户{% else %}新老用户{% endif %}</td>
                                        <td>{{ date('Y-m-d H:i:s',v['gfa_starttime']) }}</td>
                                        <td>{{ date('Y-m-d H:i:s',v['gfa_endtime']) }}</td>
                                        <td>{{ v['gfa_sort'] }}</td>
                                        <td>
                                        {% if v['gfa_state'] == 3 %}已取消{% elseif v['gfa_starttime'] > time() %}未开始
                                        {% elseif v['gfa_endtime'] < time() %}已结束{% else %}进行中{% endif %}
                                        </td>
                                        <td>{{ wap_url }}group-product-detail.html?group_id={{ v['gfa_id'] }}</td>
                                        <td>
                                            <a class="blue" href="/groupact/edit?id={{ v['gfa_id'] }}&show=1">
                                                <i class="ace-icon fa fa-search-plus bigger-130"></i>查看
                                            </a>
                                        {% if v['gfa_state'] == 3 %}
                                            <a class="green" href="/groupact/edit?id={{ v['gfa_id'] }}&copy=1">
                                                <i class="ace-icon fa fa-plus"></i>复制
                                            </a>
                                        {% elseif v['gfa_starttime'] > time() %}
                                            <a class="green" href="/groupact/edit?id={{ v['gfa_id'] }}">
                                                <i class="ace-icon fa fa-pencil bigger-130"></i>编辑
                                            </a>
                                            <a class="red del" href="javascript:;" data-id="{{ v['gfa_id'] }}">
                                                <i class="ace-icon fa fa-trash-o bigger-130"></i>删除
                                            </a>
                                        {% elseif v['gfa_endtime'] < time() %}
                                            <a class="green" href="/groupact/edit?id={{ v['gfa_id'] }}&copy=1">
                                                <i class="ace-icon fa fa-plus"></i>复制
                                            </a>
                                        {% else %}
                                            <a class="green" href="/groupact/edit?id={{ v['gfa_id'] }}&copy=1">
                                                <i class="ace-icon fa fa-plus"></i>复制
                                            </a>
                                            <a class="red cancel" href="javascript:;" data-id="{{ v['gfa_id'] }}">
                                                <i class="ace-icon fa fa-times bigger-125"></i>取消
                                            </a>
                                        {% endif %}
                                        </td>
                                    </tr>
                                {% endfor %}
                            {% else %}
                                <tr>
                                    <td colspan="30">暂无数据</td>
                                </tr>
                            {% endif %}
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
    <script src="http://{{ config.domain.static }}/assets/admin/js/group/groupAct.js"></script>
{% endblock %}