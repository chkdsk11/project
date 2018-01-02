{% extends "layout.volt" %}


{% block content %}
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/jquery.datetimepicker.css" class="ace-main-stylesheet" />
<!--放页面内容-->
<div class="page-content">
    <div class="page-header">
        <h1>移动端组件列表</h1>
    </div>



    <div class="row">

        <div class="top-search">
            <form class="form-inline" action="/subjectmobile/listwidget" method="post">
                <div class="form-group">
                    <label>组件名称：</label>
                    <input type="text" class="form-control" id="exampleInputName2" placeholder="头部滚动" name="component_name" value="{% if component_name is defined %}{{ component_name }}{%endif%}">
                </div>
                &nbsp;&nbsp;
                <div class="form-group">
                    <label>组件状态：</label>
                    <select class="form-control" name="status">
                        <option>全部</option>
                        {% if arr is defined %}
                        {% for k,v in arr %}
                        <option value="{{k}}" {% if status is defined AND status == k %}selected{% endif %}>{{k}}</option>
                        {% endfor %}
                        {% endif %}
                    </select>
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
                    <th>组件名称</th>
                    <th>是否启用</th>
                    <th>操作</th>
                </tr>
                </thead>

                <tbody>
                <!-- 遍历组件活动信息 -->
                {% if widgetList['list'] is defined and widgetList['list'] != 0  %}
                {% for k,v in widgetList['list'] %}
                <tr>
                    <td class="center">
                        {{ v['component_id'] }}
                    </td>
                    <td>
                        {{ v['component_name'] }}
                    </td>
                    <td class="hidden-480">
                        <label class="pos-rel">
                            <input type="checkbox" class="ace mb-widget-status" value=" {{ v['component_id'] }}" name="status[{{v['component_id']}}]" {% if v['status'] == 1 %}checked="true"{% endif %} >
                            <span class="lbl"></span>
                        </label>
                    </td>
                    <td>
                        <div class="hidden-sm hidden-xs action-buttons">
                            <a class="green" href="/subjectmobile/editwidget?id={{ v['component_id'] }}" title="编辑">
                                <i class="ace-icon fa fa-pencil bigger-130"></i>
                            </a>
							<a class="red"   onclick="dele('{{ v['component_id'] }}')" title="删除">
                                <i class="ace-icon fa fa-trash-o bigger-130"></i>
                            </a>
                        </div>
                    </td>

                </tr>
                {% endfor %}
                {% elseif widgetList['res'] is defined and widgetList['res'] == 'error'  %}
                <tr>
                    <td colspan="4" align="center">暂时无数据...</td>
                </tr>
                {% endif %}
                <!-- 遍历组件活动信息 end -->


                </tbody>
            </table>
            <div class="row">
                {% if widgetList['page'] is defined and widgetList['list'] != 0 %}
                {{ widgetList['page'] }}
                {% endif %}
            </div>
            <div class="clearfix form-actions">
                <div class="col-md-12">
                    <a href="/subjectmobile/editwidget">
                    <button class="btn btn-info" type="button">
                        <i class="ace-icon fa fa-check bigger-110"></i>
                        添加组件
                    </button>
                        </a>
                </div>
            </div>
        </div>

    </div>



    <!-- /.page-header -->
</div>

{% endblock %}

{% block footer %}
<script src="http://{{ config.domain.static }}/assets/js/jquery.datetimepicker.js"></script>
<script src="http://{{ config.domain.static }}/assets/admin/js/promotion/promotionList.js"></script>
<script src="http://{{ config.domain.static }}/assets/admin/js/subject/widget-list.js"></script>
<script type="text/javascript">
   //删除组件
   function dele(id){
       var is_del=confirm('是否删除此此组件');
       if(!id){
          return false;
       }
	   if(is_del){
			$.post('/subjectmobile/delewidget', {id:id, is_del:is_del} ,function (data) {
				
				if (data.status == 'success') {
					layer_success('删除成功');
					window.location.reload();
				} else {   
					layer_error('操作失败，请肖后再试');
				}
			});
       }
    }
  </script>
{% endblock %}