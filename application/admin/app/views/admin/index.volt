{% extends "layout.volt" %}

{% block title %}

{% endblock %}

{% block content %}
<div class="page-header">

</div>
<!--放页面内容-->
<div class="main-container" id="main-container">
    <div class="main-content">
        <div class="main-content-inner">
            <div class="page-content">
                <div class="row">
                    <div class="col-xs-12">
                        <!-- div.dataTables_borderWrap -->
                        <div>
                            <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>用户</th>
                                    <th>所属角色</th>
                                    <th>是否锁定</th>
                                    <th>操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                {% if list is defined and list is not empty %}
                                {% for v in list %}
                                <tr>
                                    <td>
                                        {{ v['id'] }}
                                    </td>
                                    <td>{{ v['admin_account'] }}</td>
                                    <td>
                                        {{ (roleList[v['role_id']] is defined)?roleList[v['role_id']]['role_name']:'' }}
                                    </td>
                                    <td>
                                        {{ (v['is_lock']==0)?'否':'是' }}
                                    </td>
                                    <td>
                                        <div class="hidden-sm hidden-xs action-buttons">
                                            {% if roleList[v['role_id']] is defined %}
                                                {% if roleList[v['role_id']]['is_super'] == 0 %}
                                                    <a class="green" href="/admin/edit?id={{ v['id'] }}">
                                                        <i class="ace-icon fa fa-pencil bigger-130"></i>
                                                    </a>
                                                    <a class="green" href="/admin/password?id={{ v['id'] }}">
                                                        <i class="glyphicon glyphicon-edit"></i>
                                                        修改密码
                                                    </a>
                                                {% endif %}
                                            {% else %}
                                                <a class="green" href="/admin/edit?id={{ v['id'] }}">
                                                    <i class="ace-icon fa fa-pencil bigger-130"></i>
                                                </a>
                                                <a class="green" href="/admin/password?id={{ v['id'] }}">
                                                    <i class="glyphicon glyphicon-edit"></i>
                                                    修改密码
                                                </a>
                                            {% endif %}
                                        </div>
                                    </td>
                                </tr>
                                {% endfor %}
                                {% endif %}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div><!-- PAGE CONTENT ENDS -->
        </div><!-- /.col -->
    </div><!-- /.row -->
</div><!-- /.page-content -->
{{ (page is defined)?page:'' }}
{% endblock %}

{% block footer %}

{% endblock %}