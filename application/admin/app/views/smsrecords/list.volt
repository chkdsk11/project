{% extends "layout.volt" %}

{% block content %}
    <link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/jquery.datetimepicker.css" class="ace-main-stylesheet" />
    <div class="page-content">
        <div class="row">
            <div class="col-xs-12">
                <!-- PAGE CONTENT BEGINS -->
                <div class="clearfix">
                    <div class="pull-right tableTools-container"></div>
                </div>
                <div class="table-header">
                    短信发送列表
                </div>
                <!-- 搜索 -->
                <form action="/smsrecords/list" method="get">
                    <div class="tools-box">
                        <label class="clearfix">
                            <select class="tools-txt" name="provider_code">
                                <option value="">全部服务商</option>
                                {% if data['providers'] is defined and data['providers'] is not empty %}
                                    {% for v in data['providers'] %}
                                        <option value="{{ v['provider_code'] }}" {% if option['provider_code'] == v['provider_code'] %}selected{% endif %}>{{ v['provider_name'] }}</option>
                                    {% endfor %}
                                {% endif %}
                            </select>
                        </label>
                        <label class="clearfix">
                            <select class="tools-txt" name="client_code">
                                <option value="">全部客户端</option>
                                {% if data['clients'] is defined and data['clients'] is not empty %}
                                    {% for v in data['clients'] %}
                                        <option value="{{ v['client_code'] }}" {% if option['client_code'] == v['client_code'] %}selected{% endif %}>{{ v['client_name'] }}</option>
                                    {% endfor %}
                                {% endif %}
                            </select>
                        </label>

                        <label class="clearfix checktime">
                            <span>时间：</span>
                            <input id="starttime" class="laydate-icon tools-txt" name="starttime" value="{% if option['starttime'] is not empty %}{{ option['starttime'] }}{% endif %}">
                        </label>
                        <label class="clearfix checktime">
                            <span>—</span>
                            <input id="endtime" class="laydate-icon tools-txt" name="endtime" value="{% if option['endtime'] is not empty %}{{ option['endtime'] }}{% endif %}">
                        </label>

                        <label class="clearfix">
                            <input type="text" name="content" placeholder="输入IP或手机号" class="tools-txt" value="{{ option['content'] }}">
                            <button class="btn btn-primary" type="submit">搜索</button>
                        </label>
                    </div>
                </form>
                <div>
                    共有{{ data['count'] }}条
                </div>

                <!-- div.dataTables_borderWrap -->
                <div>
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>服务商</th>
                                <th>客户端</th>
                                <th>IP</th>
                                <th>手机号</th>
                                <th>内容</th>
                                <th>时间</th>
                            </tr>
                        </thead>

                        <tbody>
                            <!-- 遍历商品信息 -->
                            {% if data['list'] is defined and data['list'] is not empty %}
                                {% for v in data['list'] %}
                                    <tr>
                                        <td>
                                            {{ v['provider_name'] }}
                                        </td>
                                        <td>
                                            {{ v['client_name'] }}
                                        </td>
                                        <td>
                                            {% if v['ip_address'] is empty%}
                                                —
                                            {% else %}
                                                {{ v['ip_address'] }}
                                            {% endif %}
                                        </td>
                                        <td>
                                            {{ v['phone'] }}
                                        </td>
                                        <td>
                                            {{ v['content'] }}
                                        </td>
                                        <td>
                                            {{ v['create_time'] }}
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
    <script src="http://{{ config.domain.static }}/assets/laydate/laydate.js"></script>
    <script>
        var start = {
            elem: '#starttime',
            format: 'YYYY-MM-DD hh:mm:ss',
            //min: '2016-07-01', //设定最小日期为当前日期
            max: laydate.now(), //最大日期
            istime: true,
            istoday: false,
            choose: function (datas) {
                end.min = datas; //开始日选好后，重置结束日的最小日期
                end.start = datas; //将结束日的初始值设定为开始日
            }
        };
        var end = {
            elem: '#endtime',
            format: 'YYYY-MM-DD hh:mm:ss',
            //min: laydate.now(),
            max: laydate.now(),
            istime: true,
            istoday: false,
            choose: function (datas) {
                start.max = datas; //结束日选好后，重置开始日的最大日期
            }
        };
        laydate(start);
        laydate(end);
    </script>
{% endblock %}