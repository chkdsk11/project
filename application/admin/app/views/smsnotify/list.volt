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
                    预警通知人列表
                </div>
                <!-- 搜索 -->
                <form action="/smsnotify/list" method="get">
                    <div class="tools-box">
                        <label class="clearfix">
                            <input type="text" name="content" placeholder="通知人名称或手机号" class="tools-txt" value="{{ option['content'] }}">
                            <button class="btn btn-primary" type="submit">搜索</button>
                        </label>
                        <label class="clearfix" style="float: right;">
                            <span>已{% if status['config_value'] is defined and status['config_value'] == 0 %}开启{% else %}停用{% endif %}短信预警通知设置</span>
                            <button class="btn btn-primary" type="button" id="changNotify">
                                {% if status['config_value'] is defined and status['config_value'] == 0 %}停用{% else %}开启{% endif %}
                            </button>
                        </label>
                        <label class="clearfix" style="float: right;">
                            <button class="btn btn-primary" type="button" onclick="showWindow('添加预警通知人', '/smsnotify/add', 800, 500, true, true);">添加通知人</button>
                        </label>
                    </div>
                </form>

                <!-- div.dataTables_borderWrap -->
                <div>
                    <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>通知人</th>
                                <th>电话号</th>
                                <th>操作</th>
                            </tr>
                        </thead>

                        <tbody>
                            <!-- 遍历商品信息 -->
                            {% if data['list'] is defined and data['list'] is not empty %}
                                {% for v in data['list'] %}
                                    <tr>
                                        <td>
                                            {{ v['user_name'] }}
                                        </td>
                                        <td>
                                            {{ v['phone'] }}
                                        </td>
                                        <td>
                                            <div class="hidden-sm hidden-xs action-buttons">
                                                <a class="red del delnotify" href="javascript:;" data-id="{{ v['notify_user_id'] }}" data-name="{{ v['user_name'] }}" title="删除">
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
    <script src="http://{{ config.domain.static }}/assets/admin/js/sms/sms.js"></script>
{% endblock %}