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
                            <form action="/cpschannel/activity" method="get">
                                <div>
                                    <label class="clearfix">
                                        <span>活动名称： </span><input type="text" style="width:200px;" placeholder="活动名称" name="act_name" value="{% if channel['act_name'] %}{{ channel['act_name'] }}{% endif %}" class="tools-txt" />
                                    </label>
                                    <label class="clearfix" style="margin-left:10px;">
                                        <span>渠道：</span>
                                        <select name="channel">
                                            <option  value="0">--选择渠道--</option>
                                                {% for date in channel_lset %}
                                                    <option value="{{date['channel_id']}}" {% if channel['channel'] == date['channel_id'] %} selected="selected" {% endif %}  > {{date['channel_name']}} </option>
                                                {% endfor %} 
                                        </select>
                                    </label>
                                    <label class="clearfix" style="margin-left:10px;">
                                        <span>返利类型：</span>
                                        <select name="type_id">
											<option value="0" >全部</option>
											<option value="1" {% if channel['type_id'] == 1 %} selected="selected" {% endif %}>全场</option>
                                            <option value="3" {% if channel['type_id'] == 3 %} selected="selected" {% endif %}>品牌</option>
											<option value="4" {% if channel['type_id'] == 4 %} selected="selected" {% endif %}>商品</option>
                                        </select>
                                    </label>
                                    <label class="clearfix" style="margin-left:10px;">
                                        <span>状态：</span>
                                        <select name="act_status">
											<option value="0">全部</option>
                                           	<option value="start" {% if channel['act_status'] == 'start' %} selected="selected" {% endif %}>未开始</option>
											<option value="middle" {% if channel['act_status'] == 'middle' %} selected="selected" {% endif %}>进行中</option>
											<option value="end" {% if channel['act_status'] == 'end' %} selected="selected" {% endif %}>已结束</option>
											<option value="cancel" {% if channel['act_status'] == 'cancel' %} selected="selected" {% endif %}>已取消</option>
                                        </select>
                                    </label>

                                    <label class="clearfix" style="margin-left:10px;">
                                        <button class="btn btn-primary"  type="submit">搜索</button>
                                        <a class="btn btn-primary" href="/cpschannel/activity">清除搜索条件</a>
                                        <a class="btn btn-primary" href="/cpschannel/acadd">添加返利活动</a>
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
                                <th class="center">活动名称</th>
                                <th class="center">渠道</th>
                                <th class="center">返利类型</th>
                                <th class="center">排序</th>
                                <th class="center">开始时间</th>
                                <th class="center">结束时间</th>
                                <th class="center">状态</th>
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
                                     {{v['act_id']}}
                                    </td>
                                    <td class="center">
                                     {{v['act_name']}}
                                    </td>
                                     <td class="center">
                                     {{v['channel_id']}}
                                    </td>
                                     <td class="center">
                                     {{v['type_name']}}
                                    </td>
                                     <td class="center">
                                     {{v['sort']}}
                                    </td>
                                    <td class="center">
                                     {{v['start_time']}}
                                    </td>
                                    <td class="center">
                                     {{v['end_time']}}
                                    </td>
                                    <!--<td class="center">-->
                                      
                                    <!--</td>-->
                                    <td class="center">
                                     {{v['act_status']}}
                                    </td>
                                    <td class="center">
                                        {% if v['act_status'] == '已取消' %}
                                        <a href="/cpschannel/copy?act_id={{v['act_id']}}&p_id=ck" class="btn btn-success table-btn-sm is_delet_click" id="{{v['channel_id']}}" name='1'>
                                            <i class="ace-icon fa fa-pencil bigger-130"></i>
                                            查看
                                        </a>
                                        <a href="/cpschannel/copy?act_id={{v['act_id']}}" class="btn btn-success table-btn-sm is_delet_click" id="{{v['channel_id']}}" name='1'>
                                            <i class="ace-icon fa fa-pencil bigger-130"></i>
                                            复制
                                        </a>
                                        {% elseif v['act_status'] == '未开始' %}
                                        <a href="/cpschannel/acedit?act_id={{v['act_id']}}" class="btn btn-success table-btn-sm is_delet_click" id="{{v['channel_id']}}" name='1'>
                                            <i class="ace-icon fa fa-pencil bigger-130"></i>
                                            修改
                                        </a>
                                        <a href="/cpschannel/copy?act_id={{v['act_id']}}" class="btn btn-success table-btn-sm is_delet_click" id="{{v['channel_id']}}" name='1'>
                                            <i class="ace-icon fa fa-pencil bigger-130"></i>
                                            复制
                                        </a>
                                        {% elseif v['act_status'] == '进行中' %}
                                        <a href="/cpschannel/acedit?act_id={{v['act_id']}}" class="btn btn-success table-btn-sm is_delet_click" id="{{v['channel_id']}}" name='1'>
                                            <i class="ace-icon fa fa-pencil bigger-130"></i>
                                            修改
                                        </a>
                                        <a href="/cpschannel/copy?act_id={{v['act_id']}}" class="btn btn-success table-btn-sm is_delet_click" id="{{v['channel_id']}}" name='1'>
                                            <i class="ace-icon fa fa-pencil bigger-130"></i>
                                            复制
                                        </a>
                                        {% elseif v['act_status'] == '已结束' %}
                                        <a href="/cpschannel/copy?act_id={{v['act_id']}}&p_id=ck" class="btn btn-success table-btn-sm is_delet_click" id="{{v['channel_id']}}" name='1'>
                                            <i class="ace-icon fa fa-pencil bigger-130"></i>
                                            查看
                                        </a>
                                        <a href="/cpschannel/copy?act_id={{v['act_id']}}" class="btn btn-success table-btn-sm is_delet_click" id="{{v['channel_id']}}" name='1'>
                                            <i class="ace-icon fa fa-pencil bigger-130"></i>
                                            复制
                                        </a> 
                                        {% endif %} 
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
	 /*
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
        });*/
</script>
{% endblock %}