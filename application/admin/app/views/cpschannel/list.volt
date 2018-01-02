{% extends "layout.volt" %}


{% block content %}
<style type="text/css">
    tr[class^="level-son"]{
        display: none;
    }
    tr.level-son-1{
        display: table-row;
    }
    .toggle-level{
        cursor: pointer;
    }
    td &gt; i{
        display: inline-block;
        width: 20px;
        height: 14px;
    }
    .table-btn-sm{
        border-width: 2px;
        font-size: 12px;
        padding: 2px 5px;
        line-height: 1.39;
    }
    .table-btn-sm + .table-btn-sm{
        margin-left: 20px;
    }
    a.btn:active {
        top: 1px;
    }
    #addFrist{
        position: absolute;
        top: 5px;
        right: 10px;
    }
    #addFrist:active{
        top: 6px;
    }
</style>
<div class="page-content">
    <div class="row">
        <div class="col-xs-12">
            <div class="row">
                <div class="col-xs-12">

                    <div class="form-group clearfix">
                        <div class="row">
                            <form action="/cpschannel/list" method="get">
                                <div class="tools-box">
                                    <label class="clearfix">
                                        <span>推广渠道： </span><input type="text" style="width:200px;" placeholder="推广渠道" name="channel" value="{{channel}}" class="tools-txt" />
                                    </label>
                                    
                                    <label class="clearfix" style="float: right;margin-right: 30px;">
                                        <a class="btn btn-primary" href="/cpschannel/add">添加渠道</a>
                                    </label>
                                    <label class="clearfix" style="float: right;">
                                        <button class="btn btn-primary"  type="submit">搜索</button>
                                    </label>

                                </div>
                            </form>

                        </div>
                    </div>
                    <div>
                        <table id="dynamic-table" class="table table-bordered table-hover dataTable no-footer">
                            <thead>
                            <tr role="row">
                                <th class="center">
                                    编号
                                </th>
                                <th class="center">渠道名称</th>
                                <th class="center">永久渠道</th>
                                <th class="center">
                                    操作
                                </th>
                            </tr>
                            </thead>
                            <tbody id="productRuleList">
                               {% if list['list'] is defined %}
                               {% if list['list'] > 0 %}
                               {% for v in list['list'] %}
                                <tr>
                                    <td class="center">
                                      {{ v['channel_id'] }}
                                    </td>
                                    <td class="center">
                                      {{ v['channel_name'] }}
                                    </td>
                                    <!--<td class="center">-->
                                        <!--{% if v['productRule'] is defined %}{{ v['productRule'] }}{% endif %}-->
                                    <!--</td>-->
                                    <td class="center">
                                       {% if v['is_permanent'] == 1 %} 是 {%else%} 否 {% endif %}
                                    </td>
                                    <td class="center">
                                        <a class="green" href="/cpschannel/edit?id={{ v['channel_id'] }}">
                                            <i class="ace-icon fa fa-pencil bigger-130"></i>
                                            编辑
                                        </a>
                                        {% if v['channel_status'] == 1 %}  
                                          <a href="javascript:void(0);" class="btn btn-success table-btn-sm is_enable_click" id="{{v['channel_id']}}" name='2'>
                                          <i class="ace-icon fa fa-ban"></i>禁用
                                        {%else%}
                                          <a href="javascript:void(0);" class="btn btn-success table-btn-sm is_enable_click" id="{{v['channel_id']}}" name='1'>
                                          <i class="ace-icon fa  fa-circle-o"></i>启用
                                         {% endif %}
                                       
                                         <a href="javascript:void(0);" class="btn btn-success table-btn-sm is_delet_click" id="{{v['channel_id']}}" name='1'>
                                            <i class="ace-icon fa fa-pencil bigger-130"></i>
                                            删除
                                        </a>
                                        <a class="green" href="/cpschannel/csv?id={{ v['channel_id'] }}">
                                            <i class="ace-icon fa fa-pencil bigger-130"></i>
                                            导出统计数据
                                        </a>
                                    </td>
                                </tr>
                            {% endfor %}   
                            {% else %}
                                <tr>
                                    <td class="center" colspan="6">
                                        暂无数据……
                                    </td>
                                </tr>
                            {% endif %}
                            {% endif %}
                            <!-- 遍历商品信息 end -->

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div><!-- /.col -->
    </div><!-- /.row -->
</div>
{% if list['page'] is defined %}{{ list['page'] }}{% endif %}
{% endblock %}

{% block footer %}
<script>
    function submit_search(){
        var act = true;
        var one_category = $('#one_category').val();
        if( one_category > 0){
            act = isCategorySelect();
        }
        return act;
    }
    //键盘点击事件
    $(document).keypress(function (e) {
        if( e.which == 13 ){
            return false;
        }
    });
	
	  //启用|禁用控制
        $(document).on('click','.is_enable_click',function(){
            var id = $(this).attr('id');
            var is_enable = $(this).attr('name');
            var th = this;
            var html = '';
            if(is_enable == 1){
                html = '<i class="ace-icon fa fa-ban"></i>禁用';
                var name = 2;
            }else{
                html = '<i class="ace-icon fa  fa-circle-o"></i>启用';
                var name = 1;
            }
            $.getJSON('/cpschannel/pause?id='+id+'&dt='+is_enable, function(msg){
                if(msg.status == 'success'){
                    $(th).html(html);
                    $(th).attr('name',name);
                }else{
                    layer.msg(msg.info, {shade: 0.3, time: 300}); return false;
                }
            });
        });
	 //删除功能权限
        $(document).on('click','.is_delet_click',function(){
            var id = $(this).attr('id');
            var is_del=confirm('是否删除此功能');
            if(!is_del){
                return;
            }
            $.getJSON('/cpschannel/dele?id='+id, function(msg){
                if (msg.status == 'success') {
                    layer_success('删除成功');
                    window.location.reload();
                } else {   
                    layer_error('删除失败');
                }
            });
        });
</script>
{% endblock %}