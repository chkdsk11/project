{% extends "layout.volt" %}

{% block content %}
    <div class="main-content">
        <div class="main-content-inner">
            <div>
                <h3>自动更新密码</h3>
            </div>
            <br />
            <div class="row">
                <div class="col-sm-12">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>服务商</th>
                                <th>下次更新时间</th>
                                <th>周期频率</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            {% if list is defined and list is not empty %}
                                {% for v in list %}
                                    {% if v['is_auto_change_password'] == 1%}
                                        <tr>
                                            <td>{{ v['provider_name'] }}</td>
                                            <td>{{ v['next_change_password_time'] }}</td>
                                            <td><input type="text" name="frequency" value="{{ v['frequency'] }}" disabled>天/次</td>
                                            <td><button class="btn btn-primary editFrequency" value="{{ v['provider_id'] }}">修改</button></td>
                                        </tr>
                                    {% endif %}
                                {% endfor %}
                            {% endif %}
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 手动更新密码 -->
            <div class="row">
                <h3>手动更新密码</h3>
                <div class="col-md-12">
                    <div>
                        <span class="col-md-12">以下可以进行手动修改短信服务商平台密码，请慎重操作</span>
                        {% if list is defined and list is not empty %}
                            <div class="form-horizontal">
                                <div class="form-group">
                                    <div class="col-sm-9">
                                        <select class="col-xs-10 col-sm-5" name="provider_id" id="changeProv">
                                            <option value="0" data-id="1">请选择服务商</option>
                                            {% if list is defined and list is not empty %}
                                                {% for v in list %}
                                                    <option value="{{ v['provider_id'] }}" data-id="{{ v['is_auto_change_password'] }}">{{ v['provider_name'] }}</option>
                                                {% endfor %}
                                            {% endif %}
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group" style="display: none;" id="phoneInput">
                                    <div class="col-sm-9">
                                        <input type="text" name="phone" class="col-xs-10 col-sm-5" placeholder="请输入手机号码"/>
                                        用于验证是否在对应短信后台已更改密码
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-sm-9">
                                        <input type="text" name="password" class="col-xs-10 col-sm-5" placeholder="请输入密码"/>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-sm-9">
                                        <button class="btn btn-info" id="editPw">
                                            <i class="ace-icon fa fa-check bigger-110"></i>
                                            更改密码
                                        </button>
                                    </div>
                                </div>
                            </div>
                        {% endif %}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{% endblock %}
{% block footer %}
    <script src="http://{{ config.domain.static }}/assets/admin/js/sms/sms.js"></script>
{% endblock %}