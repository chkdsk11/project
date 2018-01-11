{% extends "layout.volt" %}

{% block content %}
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/jquery.datetimepicker.css" class="ace-main-stylesheet" />
<style>
    .select-discount a{display: inline-block;margin-bottom:0;float: left;margin-right: 10px;}
</style>
<div class="page-content">

    <!-- /.page-header -->
    <div class="row">
        <div class="col-xs-12">
            <!-- PAGE CONTENT BEGINS -->
            <form class="form-horizontal" role="form" method="get" action="/goodset/list">
                <div class="page-header">
                    <h1>商品套餐列表</h1>
                </div>
                <div class="tools-box">
                    <label class="clearfix">

                             <span>组合套餐名称：</span>
                            <input type="text" id="promotion_code" name="group_name" class="tools-txt" value="{% if group_name is defined %}{{group_name}}{%endif%}">
                    </label>
                    <label>
                        <span>套餐状态：</span>
                        <span>
                            <select name="group_status">
                                <option value="0" {% if status is defined and status == 0 %}selected{%endif%}>全部</option>
                                <option value="1" {% if status is defined and status == 1 %}selected{%endif%}>未开始</option>
                                <option value="2" {% if status is defined and status == 2 %}selected{%endif%}>进行中</option>
                                <option value="3" {% if status is defined and status == 3 %}selected{%endif%}>已结束</option>
                            </select>
                        </span>
                    </label>
                    <label>
                        <span>适用平台：</span>
                        <span>
                            <select name="platform_status">
                                <option value="0">全部</option>
                                {% if shopPlatform is defined and shopPlatform is not empty %}
                                    {% for key,channel in shopPlatform %}
                                        <option value="{{ key }}" {% if platform is defined and platform == key  %}selected{% endif %}>{{ channel }}</option>
                                    {% endfor %}
                                {% endif %}
                            </select>
                        </span>
                    </label>
                </div>
                <div class="tools-box">
                    <label>
                    <span>组合套餐时间：</span>
                    <input type="text" id="start_time" name="start_time" class="tools-txt datetimepk" value="{% if group_name is defined %}{{ start_time }}{%endif%}" readonly/>
                    <span>-</span>
                    <input type="text" id="end_time" name="end_time" class="tools-txt datetimepk" value="{% if group_name is defined %}{{ end_time }}{%endif%}" readonly/>
                    </label>
                    <label>
                        <button class="btn btn-primary" type="submit">搜索</button>
                    </label>

                </div>


                <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>组合套餐名称</th>
                        <th>状态</th>
                        <th>适用平台</th>
                        <th>开始时间</th>
                        <th>结束时间</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    {% if GoodSetList['list'] is defined and GoodSetList['list'] != 0  %}
                    {% for k,v in GoodSetList['list'] %}
                    <tr>
                        <td style="vertical-align: middle;">{{ v["id"] }}</td>
                        <td style="vertical-align: middle;">{{ v["group_name"] }}</td>
                        <td style="vertical-align: middle;">
                            {% if v["start_time"] > time() %}未开始{% endif %}
                            {% if v["start_time"] < time() and v["end_time"] > time() %}进行中{% endif %}
                            {% if v["end_time"] < time() %}已结束{% endif %}
                        </td>
                        <td style="vertical-align: middle;">{{ v["platform_value"] }}</td>
                        <td style="vertical-align: middle;">
                            {{ date('Y-m-d H:i:s',v['start_time'])}}
                        </td>
                        <td style="vertical-align: middle;">
                            {{ date('Y-m-d H:i:s',v['end_time'])}}
                        </td>
                        <td>
                            <div class="select-discount" style="padding-top: 0;width: 100%;vertical-align: middle">
                                {% if v["start_time"] > time() %}<a title="编辑" href="javascript:void(0);" data-mid="{{ v['id'] }}" class="do_edit green"><i class="ace-icon fa fa-pencil
bigger-130"></i></a>
<a title="查看" href="javascript:void(0);" data-mid="{{ v['id'] }}" class="do_edit_view green"><i class="ace-icon fa fa-search-plus bigger-120"></i></a>
{% endif %}
                                {% if v["start_time"] < time() %}<a title="查看" href="javascript:void(0);" data-mid="{{ v['id'] }}" class="do_edit green"><i class="ace-icon fa fa-search-plus bigger-120"></i></a>{% endif %}
                        
                                
                                <a title="删除" href="javascript:void(0);" data-mid="{{ v['id'] }}" class="do_del red"><i class="ace-icon fa fa-trash-o bigger-130"></i></a>
                                
                            </div>
                        </td>
                    </tr>
                    {% endfor %}
                    {% elseif GoodSetList['res'] is defined and GoodSetList['res'] == 'error'  %}
                    <tr>
                        <td colspan="11" align="center">暂时无数据...</td>
                    </tr>
                    {% endif %}
                    </tbody>
                </table>
            </form>
            <!-- PAGE CONTENT ENDS -->
        </div><!-- /.col -->
    </div><!-- /.row -->

</div>
{% if GoodSetList['page'] is defined and GoodSetList['list'] != 0 %}
{{ GoodSetList['page'] }}
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
    $(".do_edit").on("click",function () {
        var mid=$(this).data("mid");
        location.href="/goodset/edit?id="+mid;
    })
    $(".do_edit_view").on("click",function () {
        var mid=$(this).data("mid");
        location.href="/goodset/edit?view=1&id="+mid;
    })
    $(".do_del").on("click",function () {
        var request = location.search;
        var mid=$(this).data("mid");
        layer_confirm('确定要删除这个套餐吗?','/goodset/del',{mid :mid,request :request});
        //location.reload();
    });
</script>
{% endblock %}