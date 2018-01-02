{% extends "layout.volt" %}


{% block content %}
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/jquery.datetimepicker.css" class="ace-main-stylesheet" />
<style>
    .select-discount a{display: inline-block;margin-bottom:0;float: left;margin-right: 10px;}
</style>
<div class="page-content">
    <div class="page-header">
        <h1>PC端专题列表</h1>
    </div>

    <div class="row">

        <div class="top-search">
            <form class="form-inline" action="/subjectpc/list" method="post">
                <div class="form-group">
                    <label>专题标题（title）：</label>
                    <input type="text" class="form-control" id="exampleInputName2" placeholder="" name="title" value="{% if title is defined %}{{ title }}{%endif%}">
                </div>
                &nbsp;&nbsp;
                <div class="form-group">
                    <label>专题状态：</label>
                    <select class="form-control" name="status">
                        <option value="">全部</option>
                        {% if arr is defined %}
                        {% for k,v in arr %}
                        <option value="{{k}}" {% if status is defined AND status == k %}selected{% endif %}>{{k}}</option>
                        {% endfor %}
                        {% endif %}
                    </select>
                </div>
                &nbsp;&nbsp;
                <div class="form-group input-daterange">
                    <label>修改时间：</label>
                    <input type="text" id="start_time"  name="start" placeholder="开始时间" class="tools-txt datetimepk" value="{% if start is defined %}{{ start }}{%endif%}" readonly/>
                    <input type="text" id="end_time"  name="end" placeholder="结束时间" class="tools-txt datetimepk" value="{% if end is defined %}{{ end }}{%endif%}" readonly/>
                </div>
                &nbsp;&nbsp;
                <button type="submit" class="btn btn-info">搜索</button>
			<label class="clearfix sku_label"><a class="btn btn-primary" href="/subjectpc/add">添加专题</a></label>
            </form>
        </div>

        <div class="special-table">
            <table id="simple-table" class="table  table-bordered table-hover">
                <thead>
                <tr>
                    <th class="detail-col">ID</th>
                    <th>专题名称</th>
                    <th>状态</th>
                    <th>
                        <i class="ace-icon fa fa-clock-o bigger-110 hidden-480"></i>
                        修改时间
                    </th>
                    <th>链接</th>
                    <th>操作</th>
                </tr>
                </thead>

                <tbody>
                <!-- 遍历促销活动信息 -->
                {% if subjectList['list'] is defined and subjectList['list'] != 0  %}
                {% for k,v in subjectList['list'] %}
                <tr>
                    <td class="center">
                        {{ v['id'] }}
                    </td>
                    <td>
                        {{ v['title'] }}
                    </td>
                    <td class="hidden-480">
                        <span class="label label-sm {% if v['status'] == '已停用' %} label-warning{% endif %} {% if v['status'] == '未发布' %} label-danger{% endif %} label-success">{{ v['status'] }}</span>
                    </td>
                    <td>{{ v['update_time'] }}</td>
                    <td><a class="blue" href="{{ v['link'] }}"  target="_blank">{{ v['link'] }}</a></td>
                    <td>
                        <div class="hidden-sm hidden-xs action-buttons">
                            <a class="green" href="/subjectpc/add?id={{ v['id'] }}" title="编辑">
                                <i class="ace-icon fa fa-pencil bigger-130"></i>
                            </a>
                            <a class="blue" href="/subjectpc/review?id={{ v['id'] }}" title="查看" target="_blank">
                                <i class="ace-icon fa fa-search-plus bigger-130"></i>
                            </a>
							<a class="red" onclick="copypost('{{ v['id'] }}')" title="复制">
                                <i class="ui-icon ace-icon fa fa-plus center bigger-110 blue"></i>
                            </a>
							{% if v['status'] != '已停用' %}
                            <a class="red"  onclick="stoppost('{{ v['id'] }}')" title="停用">
                                <i class="red glyphicon glyphicon-pause"></i>
                            </a>
                            {% endif %}
                            {% if v['status'] == '已停用' %}
                            <a class="green"  onclick="stoppost('{{ v['id'] }}')" title="启用">
                                <i class="green glyphicon glyphicon-play"></i>
                            </a>
                            {% endif %}
                        </div>
                    </td>

                </tr>
                {% endfor %}
                {% elseif subjectList['res'] is defined and subjectList['res'] == 'error'  %}
                <tr>
                    <td colspan="6" align="center">暂时无数据...</td>
                </tr>
                {% endif %}
                <!-- 遍历促销活动信息 end -->

                </tbody>
            </table>
        </div>
    </div>
    <!-- /.page-header -->
</div><!-- /.page-content -->
{% if subjectList['page'] is defined and subjectList['list'] != 0 %}
{{ subjectList['page'] }}
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
	
	//停用/启用
	function stoppost(param){
		$.post("/subjectpc/stopsubject", {id:param} ,function (data) {
			if(data.status == 'success'){
					layer_success("操作成功！",'list');
				} else {
					layer_required(''+data.info+'');
					return false;
				}
		});
	}
	//复制
	function copypost(param){
		$.post("/subjectpc/copysubject", {id:param} ,function (data) {
			if(data.status == 'success'){
					layer_success("操作成功！",'list');
				} else {
					layer_required("操作失败！");
					return false;
				}
		});
	}
</script>
{% endblock %}