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
                    密码修改记录
                </div>
                <!-- 搜索 -->
                <form action="/smsprovider/pwLog" method="get">
                    <div class="tools-box">
                        <label class="clearfix">
                            <select class="tools-txt" name="selecttime">
                                <option value="0">全部</option>
                                <option value="1" {% if option['selecttime'] == 1 %}selected{% endif %}>最近一个月</option>
                                <option value="2" {% if option['selecttime'] == 2 %}selected{% endif %}>最近三个月</option>
                                <option value="3" {% if option['selecttime'] == 3 %}selected{% endif %}>最近半年</option>
                                <option value="4" {% if option['selecttime'] == 4 %}selected{% endif %}>最近一年</option>
                            </select>
                        </label>

                        <label class="clearfix">
                            <select class="tools-txt" name="provider_id">
                                <option value="">全部服务商</option>
                                {% if data['providers'] is defined and data['providers'] is not empty %}
                                    {% for v in data['providers'] %}
                                        <option value="{{ v['provider_id'] }}" {% if option['provider_id'] == v['provider_id'] %}selected{% endif %}>{{ v['provider_name'] }}</option>
                                    {% endfor %}
                                {% endif %}
                            </select>
                        </label>

                        <label class="clearfix">
                            <button class="btn btn-primary" type="submit">搜索</button>
                        </label>
                    </div>
                </form>
                <div>
                    以下为短信服务商平台密码的修改记录
                </div>

                <!-- div.dataTables_borderWrap -->
                <div>
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>服务商</th>
                                <th>修改人</th>
                                <th>时间</th>
                                <th>修改密码</th>
                            </tr>
                        </thead>

                        <tbody>
                            <!-- 遍历商品信息 -->
                            {% if data['list'] is defined and data['list'] is not empty %}
                                {% for v in data['list'] %}
                                    <tr>
                                        <td>
                                            {{ v['provider_name'] }}&nbsp;&nbsp;
                                            {% if in_array(v['id'],data['newids'])%}<span style="color: green;">(最新)</span>{% endif %}
                                        </td>
                                        <td>
                                            {% if v['modify_type'] == 1%}
                                                {{ v['admin_account'] }}
                                            {% else %}
                                                系统自动更新
                                            {% endif %}
                                        </td>
                                        <td>
                                            {{ v['create_time'] }}
                                        </td>
                                        <td>
                                            {{ v['new_password'] }}
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