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
                    管理商列表
                </div>

                <!-- div.dataTables_borderWrap -->
                <div>
                    <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>服务商</th>
                                <th>发送比例&nbsp;&nbsp;<input onclick="showWindow('编辑发送比例', '/smsprovider/editscale', 800, 500, true, true);" class="btn btn-xs btn-primary save" value="设置" type="button"></th>
                                <th>补发优先级&nbsp;&nbsp;<input onclick="showWindow('编辑补发优先级', '/smsprovider/editpriority', 800, 500, true, true);" class="btn btn-xs btn-primary save" value="设置" type="button"></th>
                                <th>操作</th>
                            </tr>
                        </thead>

                        <tbody>
                            <!-- 遍历商品信息 -->
                            {% if list is defined and list is not empty %}
                                {% for v in list %}
                                    <tr>
                                        <td>
                                            {{ v['provider_name'] }}&nbsp;&nbsp;
                                            {% if v['provider_state'] == 0 %}
                                                <span style="color: green;">使用中</span>
                                            {% else %}
                                                <span style="color: red;">已停用</span>
                                            {% endif %}
                                        </td>
                                        <td>
                                            {% if v['provider_state'] == 0 %}
                                                {{ v['scale'] }}
                                            {% else %}
                                                失效
                                            {% endif %}
                                        </td>
                                        <td>
                                            {% if v['provider_state'] == 0 %}
                                                {{ v['priority'] }}
                                            {% else %}
                                                失效
                                            {% endif %}
                                        </td>
                                        <td>
                                            {%  if v['provider_state'] == 1 %}
                                                <i class="ace-icon glyphicon btn-xs btn-info glyphicon-time providerStatus" data-id="{{ v['provider_id'] }}" data="{{ v['provider_state'] }}" style="cursor: pointer;">开启</i>
                                            {% elseif v['provider_state'] == 0 %}
                                                <i class="ace-icon glyphicon btn-xs btn-danger glyphicon-off providerStatus" data-id="{{ v['provider_id'] }}" data="{{ v['provider_state'] }}" style="cursor: pointer;">停用</i>
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
{% endblock %}

{% block footer %}
    <script src="http://{{ config.domain.static }}/assets/admin/js/sms/sms.js"></script>
{% endblock %}