{% extends "layout.volt" %}


{% block content %}
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/jquery.datetimepicker.css" class="ace-main-stylesheet" />
<style>
    .select-discount a{display: inline-block;margin-bottom:0;float: left;margin-right: 10px;}
</style>
            <div class="page-content">
                <div class="page-header">
                    <h1>PC端LOG列表</h1>
                </div>

                <div class="row">

                    <div class="top-search">
                        <form class="form-inline" action="/subjectpc/loglist" method="post">
                            <div class="form-group input-daterange">
                                <label>时间：</label>
                                <input type="text" id="start_time"  name="start" placeholder="开始时间" class="tools-txt datetimepk" value="{% if start is defined %}{{ start }}{%endif%}" readonly/>
                                <input type="text" id="end_time"  name="end" placeholder="结束时间" class="tools-txt datetimepk" value="{% if end is defined %}{{ end }}{%endif%}" readonly/>
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
                                <th>专题ID</th>
                                <th>操作人ID</th>
								<th>操作人员帐号</th>
                                <th>
                                    <i class="ace-icon fa fa-clock-o bigger-110 hidden-480"></i>
                                    操作时间
                                </th>
                                <th>字段</th>
                                <th>变更前</th>
								<th>变更后</th>
                            </tr>
                            </thead>

                            <tbody>
                            <!-- 遍历专题活动信息 -->
                            {% if logList['list'] is defined and logList['list'] != 0  %}
                            {% for k,v in logList['list'] %}
                            <tr>
                                <td class="center">{{ v['log_id'] }}</td>
                                <td class="center">{{ v['subject_id'] }}</td>
                                <td >{{ v['user_id'] }}</td>
								<td >{{ v['admin_account'] }}</td>
                                <td>{{ v['add_time'] }}</td>
                                <td>{{ v['field_name'] }}</td>
                                <td>{{ v['old_value'] }}</td>
								<td>{{ v['new_value'] }}</td>
                            </tr>
                            {% endfor %}
                            {% elseif logList['res'] is defined and logList['res'] == 'error'  %}
                            <tr>
                                <td colspan="6" align="center">暂时无数据...</td>
                            </tr>
                            {% endif %}
                            <!-- 遍历专题活动信息 end -->

                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- /.page-header -->
			</div><!-- /.page-content -->
            {% if logList['page'] is defined and logList['list'] != 0 %}
            {{ logList['page'] }}
            {% endif %}
{% endblock %}

{% block footer %}
<script src="http://{{ config.domain.static }}/assets/js/jquery.datetimepicker.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/jquery.validate.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/jquery.autosize.min.js"></script>
<script type="text/javascript">
    $(function () {
        $('.datetimepk').datetimepicker({
            step: 10,
            allowBlank:true
        });
    });
</script>
{% endblock %}