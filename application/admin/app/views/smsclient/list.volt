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
                    短信/图片验证列表
                </div>

                <!-- 搜索 -->
                <form action="/smsclient/list" method="get">
                    <div class="tools-box">
                        <label class="clearfix">
                            <span><strong>启用短信/图形验证</strong>（打勾即启用）</span>
                        </label>
                        <label class="clearfix" style="float: right;">
                            <input type="text" name="template_name" placeholder="请输入场景名称" class="tools-txt" value="{{ option['template_name'] }}">
                            <button class="btn btn-primary" type="submit">搜索</button>
                        </label>
                    </div>
                </form>

                <!-- div.dataTables_borderWrap -->
                <div>
                    <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>使用场景</th>
                                <th>短信服务</th>
                                <th>图形验证码</th>
                            </tr>
                        </thead>

                        <tbody>
                            <!-- 遍历商品信息 -->
                            {% if data['list'] is defined and data['list'] is not empty %}
                                {% for v in data['list'] %}
                                    <tr>
                                        <td>
                                            {{ v['template_name'] }}
                                        </td>
                                        <td>
                                            {% for vv in data['client'] %}
                                                {% if vv['client_state'] != 0 %}
                                                    <input type="checkbox" name="client[]" disabled>
                                                {% else %}
                                                    <input type="checkbox" name="client[]" data-tid="{{ v['template_id'] }}" data-cid="{{ vv['client_id'] }}" data-type="is_enable_client" {% if data['relation'][v['template_id']][vv['client_id']] is defined and (data['relation'][v['template_id']][vv['client_id']]['is_enable_client'] == 0 or data['relation'][v['template_id']][vv['client_id']]['is_enable_client'] == 2) %}checked{% endif %}>
                                                {% endif %}
                                                {{ vv['client_name'] }}&nbsp;&nbsp;
                                            {% endfor %}
                                        </td>
                                        <td>
                                            {% for vv in data['client'] %}
                                                {% if vv['client_state'] != 0 %}
                                                    <input type="checkbox" name="client[]" disabled>
                                                {% else %}
                                                    <input type="checkbox" name="client[]" data-tid="{{ v['template_id'] }}" data-cid="{{ vv['client_id'] }}" data-type="is_enable_captcha" {% if data['relation'][v['template_id']][vv['client_id']] is defined and (data['relation'][v['template_id']][vv['client_id']]['is_enable_captcha'] == 0 or data['relation'][v['template_id']][vv['client_id']]['is_enable_captcha'] == 2) %}checked{% endif %}>
                                                {% endif %}
                                                {{ vv['client_name'] }}&nbsp;&nbsp;
                                            {% endfor %}
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