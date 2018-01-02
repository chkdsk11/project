{% extends "layout.volt" %}

{% block path %}
<li class="active">推广</li>
<li class="active"><a href="/spu/skuList">推广会员</a></li>
<li class="active">推广二维码下载</li>
{% endblock %}

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
                            <form action="/cpsqrcode/list" method="get">
                                <div>
                                    <label class="clearfix">
                                        <span>姓名： </span><input type="text" style="width:200px;" placeholder="姓名" name="name" value="{% if channel['name'] is defined %} {{channel['name']}}{%endif%} " class="tools-txt" />
                                    </label>
                                    <label class="clearfix" style="margin-left:20px;">
                                        <span>用户名： </span><input type="text" style="width:200px;" placeholder="用户名" name="username" value="{% if channel['username'] is defined %} {{channel['username']}}{%endif%} " class="tools-txt" />
                                    </label>
                                    <label class="clearfix" style="margin-left:20px;">
                                        <span>活动筛选： </span>
                                        <select name="status" style="width:100px;height:30px;border:1px #797979 solid;">
                                            <option value="0">全部</option>
                                            <option value="1" {% if channel['status']  ==1 %}selected{%endif%}>进行中</option>
                              				<option value="2"{% if channel['status']  == 2 %}selected{%endif%}>未开始</option>
                              				<option value="3"{% if channel['status']  == 3 %}selected{%endif%}>过期</option>
                                            <option value="4"{% if channel['status']  == 4 %}selected{%endif%}>已取消</option>
                                            </select>
                                    </label>
                                    <label class="clearfix" style="margin-left:20px;">
                                        <span>只显示渠道二维码： </span>
                                        <select name="show_channel_qrcode" >
                                            <option value="0">全部</option>
                                                <option value="1" {% if  channel['show_channel_qrcode'] is defined   and channel['show_channel_qrcode'] == 1 %} selected="selected" {% endif %} >是</option>
                                         </select>
                                        </select>
                                    </label>
                                    <br /><br/>
                                    <label class="clearfix">
                                        <span>会员渠道： </span>
                                        <select name="channel_id" id="channel_id" onchange="get_activity(this.value)">
                            				<option value="0">全部</option>
                              					{% for date in channel_lset %}
                                   					<option value="{{date['channel_id']}}" {% if channel['channel_id'] == date['channel_id'] %} selected="selected" {% endif %}  > {{date['channel_name']}} </option>
                              					{% endfor %} 
                            			</select>
                                    </label>
                                    <label class="clearfix" style="margin-left:20px;">
                                        <span>推广活动： </span>
                                        <select name="act_id" id="act_id">
                            				<option value="0">全部</option>
											 {% if act is defined  %}
                              					{% for date in act %}
                                   					<option value="{{date['act_id']}}" {% if channel['act_id'] == date['act_id'] %} selected="selected" {% endif %}  > {{date['act_name']}} </option>
                              					{% endfor %}
											{% endif %} 
                            			</select>
                                    </label>
									 
                                    <label class="clearfix" style="margin-left:20px;">
                                        <span>地区： </span>
                                        <select name="province" id="province" onchange="get_address(this.value)">
                            				<option value="0">请选择</option>
                              					{% for date in address %}
                                   					<option value="{{date['id']}}" {% if channel['province'] == date['id'] %} selected="selected" {% endif %}  > {{date['region_name']}} </option>
                              					{% endfor %} 
                            			</select>&nbsp;&nbsp;
                                        <select name="city" id="city">
                            				<option value="0">请选择</option>
                              					{% for date in city %}
                                   					<option value="{{date['id']}}" {% if channel['city'] == date['id'] %} selected="selected" {% endif %}  > {{date['region_name']}} </option>
                              					{% endfor %} 
                            			</select>
                                     </label>
                                    <label class="clearfix" style="float: right;">
                                        <button class="btn btn-primary"  type="submit">搜索</button>
                                    </label>
                                    <br /><br/>
                                </div>
                            </form>
							<a class="btn btn-primary" href="/cpsqrcode/list?name={{channel['name']}}&username={{channel['username']}}&status={{channel['status']}}&channel_id={{channel['channel_id']}}&act_id={{channel['act_id']}}&show_channel_qrcode={{channel['show_channel_qrcode']}}&province={{channel['province']}}&city={{channel['city']}}&size=100"  target="_blank">下载所有二维码（100）</a>
						    <a class="btn btn-primary" href="/cpsqrcode/list?name={{channel['name']}}&username={{channel['username']}}&status={{channel['status']}}&channel_id={{channel['channel_id']}}&act_id={{channel['act_id']}}&show_channel_qrcode={{channel['show_channel_qrcode']}}&province={{channel['province']}}&city={{channel['city']}}&size=200&name={{channel['name']}}&username={{channel['username']}}"   target="_blank">下载所有二维码（200）</a>
                        </div>
                    </div>
                    <div>
                        <table id="dynamic-table" class="table table-bordered table-hover dataTable no-footer">
                            <thead>
                            <tr role="row">
                                <th class="center">编号</th>
                                <th class="center">用户名</th>
                                <th class="center">姓名</th>
                                <th class="center">推广渠道</th>
                                <th class="center">推广活动</th>
                                <th class="center">省</th>
                                <th class="center">市</th>
                                <th class="center">二维码（100*100px）</th>
                                <th class="center">二维码（200*200px）</th>
                                
                            </tr>
                            </thead>
                            <tbody id="productRuleList">
                               {% if list['list'] is defined %}
                               {% if list['list'] > 0 %}
                               {% for v in list['list'] %}
                                    {% if v['show_channel_qrcode'] ==1 %}
                                        <tr>
                                            <td class="center">
                                              {{ v['cps_id'] }}
                                            </td>
                                            <td class="center">
                                              {{ v['user_id'] }}
                                            </td>
                                            <td class="center">
                                              {{ v['user_name'] }}
                                            </td>
                                            <td class="center">
                                              {{ v['channel_name'] }}
                                            </td>
                                            <td class="center">
                                              渠道:  {{ v['channel_name'] }}
                                            </td>
                                            <td class="center">
                                              {{ v['province'] }}
                                            </td>
                                            <td class="center">
                                              {{ v['city'] }}
                                            </td>
                                            <td class="center">
                                                <img src="{{v['chinnal_qrcode_100']}}" height="60" width="60"><br />
                                                <a href="/cpsqrcode/list?stu=2&img={{v['chinnal_qrcode_100_1']}}&name={{v['chinnal_qrcode_100_name']}}" alt="无分享链接" target="_blank"><input type="button" value=" 下载 "></a>
                                            </td>
                                            <td class="center">
                                                <img src="{{v['chinnal_qrcode_200']}}" height="60" width="60"><br />
                                                <a href="/cpsqrcode/list?stu=2&img={{v['chinnal_qrcode_200_1']}}&name={{v['chinnal_qrcode_200_name']}}" alt="无分享链接"  target="_blank"><input type="button" value=" 下载 "></a>
                                            </td>
                                        </tr>
                                    {% endif %}
                                    {% if channel['show_channel_qrcode'] != 1 and v['act_id']!='' %}
                                        <tr>
                                            <td class="center">
                                              {{ v['cps_id'] }}
                                            </td>
                                            <td class="center">
                                              {{ v['user_id'] }}
                                            </td>
                                            <td class="center">
                                              {{ v['user_name'] }}
                                            </td>
                                            <td class="center">
                                              {{ v['channel_name'] }}
                                            </td>
                                            <td class="center">
                                                {{v['act_name']}}
                                            </td>
                                            <td class="center">
                                              {{ v['province'] }}
                                            </td>
                                            <td class="center">
                                              {{ v['city'] }}
                                            </td>
                                            <td class="center">
                                                <img src="{{v['qrcode_100']}}" height="60" width="60"><br />
                                                <a href="/cpsqrcode/list?stu=2&img={{v['qrcode_100_1']}}&name={{v['qrcode_100_name']}}" alt="无分享链接" target="_blank"><input type="button" value=" 下载 "></a>
                                            </td>
                                            <td class="center">
                                                <img src="{{v['qrcode_200']}}" height="60" width="60"><br />
                                                <a href="/cpsqrcode/list?stu=2&img={{v['qrcode_200_1']}}&name={{v['qrcode_200_name']}}" alt="无分享链接" target="_blank"><input type="button" value=" 下载 "></a>
                                            </td>
                                            
                                        </tr>
                                    {% endif %}
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
	
	 function get_activity(id) {
        $("select[name='act_id']").html('<option value="0">全部</option>');
        if (id != 0) {
            $.ajax({
                url: "/cpsqrcode/list",
                data: {"channel_id": id,"stu":1},
                type: "get",
                dataType : "html",
                success: function (data) {
                    $("select[name='act_id']").html(data);
                }
            });
        }
    }
	 function get_address(id) {
        $("select[name='city']").html('<option>--请选择--</option>');
        $.ajax({
            url: "/cpsqrcode/list",
            data: { "province": id,"stu":1},
            type: "get",
            dataType : "html",
            success: function (data) {
                $("select[name='city']").html(data);
            }
        });
    }
</script>
{% endblock %}