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
                                    <th>角色</th>
                                    <th>所属站点</th>
                                    <th>启用/禁用</th>
                                    <th>操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                {% if role is defined and role is not empty %}
                                {% for v in role %}
                                <tr>
                                    <td>
                                        <a href="#">{{ v['role_id'] }}</a>
                                    </td>
                                    <td>{{ v['role_name'] }}</td>
                                    <td>
                                        {% if site is defined and site is not empty %}
                                        {{ site[v['site_id']] }}
                                        {% endif %}
                                    </td>
                                    <td>
                                        {{ v['is_enable']?'启用':'禁用' }}
                                    </td>
                                    <td>
                                        <div class="hidden-sm hidden-xs action-buttons">
                                            <a class="green" href="/role/edit?role_id={{ v['role_id'] }}">
                                                <i class="ace-icon fa fa-pencil bigger-130"></i>
                                            </a>
                                            <!--<a class="red" href="#" onclick="javascript:ban_role({{ v['role_id'] }},{{ v['is_enable']?0:1 }})">-->
                                                <!--<i class="ace-icon fa fa-ban bigger-130"></i>-->
                                            <!--</a>-->
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
<script type="text/javascript">
    //禁止角色
    function ban_role(role_id,is_enable){
        var is_commit=confirm('是否更新状态');
        if(!is_commit){
            return false;
        }
       $.getJSON("/role/ban",{role_id:role_id,is_enable:is_enable},function(ret){
            if(ret.status){
                location.reload();
            }else{
                layer_error('更新失败');
            }
        })
    }


</script>
{% endblock %}