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
                                    <th>启用/禁用</th>
                                    <th>操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                    {% if list is defined and list is not empty %}
                                        {% for v in list %}
                                            <tr>
                                                <td>
                                                    <a href="">{{ v['role_id'] }}</a>
                                                </td>
                                                <td>{{ v['role_name'] }}</td>
                                                <td>
                                                    {{ v['is_enable']?'启用':'禁用' }}
                                                </td>
                                                <td>
                                                    <div class="hidden-sm hidden-xs action-buttons">
                                                        {% if v['is_super'] == 0 %}
                                                            <a class="green" href="/adminrole/edit?role_id={{ v['role_id'] }}">
                                                                <i class="ace-icon fa fa-pencil bigger-130"></i>
                                                            </a>
                                                            <a class="red bootbox-confirm" href="javascript:;" value="/adminRole/del?role_id={{ v['role_id'] }}">
                                                                <i class="ace-icon fa fa-trash-o bigger-130"></i>
                                                            </a>
                                                            <a class="red" href="/adminrole/assignaccess?role_id={{ v['role_id'] }}">
                                                            <i class="ace-icon fa fa-exchange bigger-130"></i>
                                                            分配权限
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
    <script>
        $(function(){
            //删除功能权限
            $(document).on('click','.bootbox-confirm',function(){
                var id = $(this).attr('id');
                var url = $(this).attr('value');
                var is_del=confirm('是否删除此功能');
                if(!is_del){
                    return;
                }
                $.getJSON(url, function(msg){
                    if (msg.code == 200) {
                        layer.msg('删除成功！', {icon: 1});
                        setTimeout(function(){
                            top.window.location.reload();
                        },1000);
                    } else {
                        layer.msg(msg.data, {icon: 2});
                    }
                });
            });
        });
    </script>
{% endblock %}