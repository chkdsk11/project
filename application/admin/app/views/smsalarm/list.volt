{% extends "layout.volt" %}

{% block content %}
    <div class="main-content">
        <div class="main-content-inner">
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        预警值设置
                    </h1>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        {% if data is defined and data is not empty %}
                            {% for k,v in data %}
                                <div class="form-horizontal">
                                    <div>
                                        <label class="col-sm-3 control-label no-padding-right">{{ v['name'] }}</label>
                                        <div class="col-md-9">
                                            <button class="btn btn-primary btn-info editAlarm" style="float: right;">修改</button>
                                        </div>
                                    </div>
                                    {% for vv in v['list'] %}
                                        <div class="form-group">
                                            <label class="col-sm-3 control-label no-padding-right">{{ vv['alarm_name'] }}</label>
                                            <div class="col-sm-9">
                                                <input type="text" name="{{ vv['alarm_id'] }}"  value="{{ vv['alarm_value'] }}" class="col-xs-10 col-sm-5 inputs" placeholder="不可为空" disabled/>
                                            </div>
                                        </div>
                                    {% endfor %}
                                    <div class="space-4"></div>
                                </div>
                            {% endfor %}
                        {% else %}
                            暂无数据
                        {% endif %}
                    </div>
                </div>
            </div>
        </div>
    </div>
{% endblock %}

{% block footer %}
    <script src="http://{{ config.domain.static }}/assets/admin/js/sms/sms.js"></script>
{% endblock %}