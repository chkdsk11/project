{% extends "layout.volt" %}


        {% block content %}
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/jquery.datetimepicker.css" class="ace-main-stylesheet" />
<style>
.sku_label_search{
    margin-right: 150px;
}
#categoryBox{
    top: -21px;
    left: 31px;
}
.sku_menu_main{
    padding-top: 7px;
    height: 44px;
}
.sku_search_keywords{
    margin-top: -19px;
    margin-bottom: -30px;
}
.popup {
    position: fixed;
    z-index: 999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    /* display: -webkit-box;
     display: -moz-box;
     display:         box;
     -webkit-box-pack: center;
     -moz-box-pack: center;
     box-pack:         center;
     -webkit-box-align: center;
     -moz-box-align: center;
     box-align:         center;*/
    overflow-x: hidden;
    overflow-y: auto;
    background-color: rgba(0,0,0,0.7);

}
.popup .popup-content {
    position: absolute;
    left: 50%;
    top: 50%;
    width: 660px ;
    transform: translateY(-50%);
    width:500px;
    padding: 10px;
    text-align: center;
    position: relative;
    border-radius: .5rem;
    background-color: #ffffff;
    margin-left: -260px;

}
.popup-content .pro-title {
    position: relative;
    width: 100%;
    height: 40px;
    line-height: 40px;
    text-align: center;
    color: #333333;
    font-size: 16px;
    border-bottom: #cccccc solid 1px;
    margin-bottom: 20px;

}
.popup-content .pro-title a {
    display: block;
    position: absolute;
    right: 0;
    top: 0;
    height: 40px;
    width: 40px;
    color: #333333;
    font-size: 24px;
    text-align: center;
}

.popup-content .title {
    position: relative;
    width: 100%;
    height: 40px;
    line-height: 40px;
    text-align: center;
    color: #333333;
    font-size: 16px;
    border-bottom: #cccccc solid 1px;

}
.popup-content .title a {
    display: block;
    position: absolute;
    right: 0;
    top: 0;
    height: 40px;
    width: 40px;
    color: #333333;
    font-size: 24px;
    text-align: center;
}
.popup-content .stock-radio {
    text-align: left;
}
.popup-content .stock-checkbox {
    margin-left: 30px ;
}
.popup-content .stock-price {
    width: 80px;
}

.popup-content button {
    margin: 10px 0;
}
</style>
<div class="page-content">
<div class="row">
    <div class="col-xs-12">
        <div class="row">
            <div class="col-xs-12">

                <div class="form-group clearfix">
                    <div class="row">
                        <form action="" method="get">
                            <div class="tools-box" >
                                <label class="clearfix">
                                    开始时间:<input id="start_time" type="text" style="float:none;" placeholder="开始时间" name="start_time" value="{% if search['start_time'] is defined %} {{ search['start_time']}} {% endif %}" class="tools-txt sku_input" />
                                </label>

                                <label class="clearfix">
                                    结束时间:<input id="end_time" type="text" style="float:none;" placeholder="结束时间" name="end_time" value="{% if search['end_time'] is defined %}{{ search['end_time']}}  {% endif %}" class="tools-txt sku_input" />
                                </label>

                                <label class="clearfix"><button class="btn btn-primary clear_search" type="submit">清除搜索条件</button></label>
                                <label class="clearfix">
                                    <button class="btn btn-primary sku_label_search" type="submit">搜索</button>
                                </label>
                                <label class="clearfix sku_label"><a class="btn btn-primary" href="/apptheme/appedit">添加app主题</a></label>

                            </div>
                        </form>

                    </div>
                </div>
                <div>
                    <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                        <thead>
                        <tr>
                            <th class="center sku_menu_row5">编号</th>
                            <th class="center sku_menu_row5">客户端</th>
                            <th class="center sku_menu_row5">主题包</th>
                            <th class="center sku_menu_row5">分辨率</th>
                            <th class="center sku_menu_row4">开始时间-结束时间</th>
                            <th class="center sku_menu_row5">是否显示会场</th>
                            <th class="center sku_menu_row5">主会场地址</th>
                            <th class="center sku_menu_row4"> 操作</th>
                        </tr>
                        </thead>
                        <tbody id="productRuleList">
                        {% if list['list'] is defined %}
                        {% if list['list'] > 0 %}
                        {% for v in list['list'] %}
                        <tr>
                            <td class="center">
                                {{ v['theme_id'] }}
                            </td>
                            <td>
                                {% if v['channel']==89 %}
                                ios
                                {% elseif v['channel']==90 %}
                                安卓
                                {% elseif v['channel']==91 %}
                                wap
                                {% endif %}
                            </td>
                            <td class="center">
                                <a href="{{ v['path'] }}">下载主题包</a>
                            </td>
                            <td class="center">
                               {{ v['scale'] }}
                            </td>
                            <td class="center">
                                {{ date('Y-m-d H:i:s', v['start_time']) }}-{{ date('Y-m-d H:i:s', v['end_time']) }}
                            </td>
                            <td class="center">
                                {% if v['is_show_local']==0 %}
                                    不显示
                                {% elseif v['is_show_local']==1 %}
                                    显示
                                {% endif %}
                            </td>
                            <td class="center">
                                {{ v['local_url'] }}
                            </td>
                            <td class="center">
                                <a class="green" href="/apptheme/appedit?id={{ v['theme_id'] }}">
                                    <i class="ace-icon fa fa-pencil bigger-130"></i>
                                    编辑
                                </a>
                            </td>
                        </tr>
                        {% endfor %}
                        {% else %}
                        <tr>
                            <td class="center" colspan="10">
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
<script src="http://{{ config.domain.static }}/assets/js/jquery.datetimepicker.js"></script>
<script src="http://{{ config.domain.static }}/assets/admin/js/apptheme/pubilc.js"></script>
        {% endblock %}