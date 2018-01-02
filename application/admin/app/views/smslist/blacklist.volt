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
                    黑名单列表
                </div>
                <!-- 搜索 -->
                <form action="/smslist/blacklist" method="get">
                    <div class="tools-box">
                        <label class="clearfix">
                            <select class="tools-txt" name="selecttime">
                                <option value="0">全部</option>
                                <option value="1" {% if option['selecttime'] == 1 %}selected{% endif %}>最近一个月</option>
                                <option value="2" {% if option['selecttime'] == 2 %}selected{% endif %}>最近三个月</option>
                                <option value="3" {% if option['selecttime'] == 3 %}selected{% endif %}>最近半年</option>
                                <option value="4" {% if option['selecttime'] == 4 %}selected{% endif %}>最近一年</option>
                                <option value="5" {% if option['selecttime'] == 5 %}selected{% endif %}>自选</option>
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
                        <label class="clearfix" style="float: right;">
                            <button class="btn btn-primary" type="button" onclick="showWindow('添加黑名单', '/smslist/addblack', 800, 500, true, true);">添加黑名单</button>
                        </label>
                    </div>
                </form>

                <!-- div.dataTables_borderWrap -->
                <div>
                    <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>IP</th>
                                <th>手机号</th>
                                <th>时间</th>
                                <th>类型</th>
                                <th>操作</th>
                            </tr>
                        </thead>

                        <tbody>
                            <!-- 遍历商品信息 -->
                            {% if data['list'] is defined and data['list'] is not empty %}
                                {% for v in data['list'] %}
                                    <tr>
                                        <td>
                                            {{ v['ip_address'] }}
                                        </td>
                                        <td>
                                            {{ v['phone'] }}
                                        </td>
                                        <td>
                                            {{ v['create_time'] }}
                                        </td>
                                        <td>
                                            {% if v['list_info_type'] == 0 %}系统识别{% else %}手动添加{% endif %}
                                        </td>
                                        <td>
                                            <i class="ace-icon glyphicon btn-xs btn-info delBlackList" data-id="{{ v['list_id'] }}" phone="{{ v['phone'] }}" ip="{{ v['ip_address'] }}" style="cursor: pointer;">解除黑名单</i>
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
    <script src="http://{{ config.domain.static }}/assets/laydate/laydate.js"></script>
    <script>
                                var selval = $(".tools-txt").find("option:selected").val();
                                if (selval == 5) {
                                    $(".checktime").show();
                                } else {
                                    $(".checktime").hide();
                                }
                                $(".row option").click(function () {
                                    var val = $(this).val();
                                    if (val == 5) {
                                        $(".checktime").show();
                                    } else {
                                        $(".checktime").hide();
                                    }
                                });
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