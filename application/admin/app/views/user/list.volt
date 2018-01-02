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
                                    <th>所属站点</th>
                                    <th>是否锁定</th>
                                    <th>操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                {% if user is defined and user is not empty %}
                                {% for v in user %}
                                <tr>
                                    <td>
                                        {{ v['id'] }}
                                    </td>
                                    <td>{{ v['admin_account'] }}</td>
                                    <td>
                                        {{ (role[v['id']] is defined)?role[v['id']]:'' }}
                                    </td>
                                    <td>
                                        {{ (site[v['site_id']] is defined)?site[v['site_id']]:'' }}
                                    </td>
                                    <td>
                                        {{ (v['is_lock']==0)?'否':'是' }}
                                    </td>
                                    <td>
                                        <div class="hidden-sm hidden-xs action-buttons">
                                            <a class="green" href="/user/edit?id={{ v['id'] }}">
                                                <i class="ace-icon fa fa-pencil bigger-130"></i>
                                            </a>
                                        </div>

                                        <div class="hidden-md hidden-lg">
                                            <div class="inline pos-rel">
                                                <button class="btn btn-minier btn-yellow dropdown-toggle" data-toggle="dropdown" data-position="auto">
                                                    <i class="ace-icon fa fa-caret-down icon-only bigger-120"></i>
                                                </button>

                                                <ul class="dropdown-menu dropdown-only-icon dropdown-yellow dropdown-menu-right dropdown-caret dropdown-close">
                                                    <li>
                                                        <a href="#" class="tooltip-success" data-rel="tooltip" title="Edit">
																				<span class="green">
																					<i class="ace-icon fa fa-pencil-square-o bigger-120"></i>
																				</span>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="#" class="tooltip-error" data-rel="tooltip" title="Delete">
																				<span class="red">
																					<i class="ace-icon fa fa-trash-o bigger-120"></i>
																				</span>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
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