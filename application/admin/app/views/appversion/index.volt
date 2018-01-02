{% extends "layout.volt" %}

{% block script %}
<script>
    var change_sale = new Array;
    var i = 0;
</script>
{% endblock %}

{% block content %}
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
                                    <label class="clearfix"><a class="btn btn-primary" href="/appversion/add">添加app版本</a></label>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div>
                        <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                            <thead>
                            <tr>
                                <th class="center sku_menu_row5">编号</th>
                                <th class="center sku_menu_row4">版本</th>
                                <th class="center sku_menu_row5">版本描述</th>
                                <th class="center sku_menu_row4">使用平台</th>
                                <th class="center sku_menu_row4">是否强制更新</th>
                                <th class="center sku_menu_row5">状态</th>
                                <th class="center sku_menu_row4"> 操作</th>
                            </tr>
                            </thead>
                            <tbody id="productRuleList">

                            <!-- 遍历商品信息 -->
                            {% if list['list'] is defined %}
                            {% if list['list'] > 0 %}
                            {% for v in list['list'] %}
                            <tr>
                                <td class="center">
                                    {{ v['versions_id'] }}
                                </td>
                                <td class="center">
                                    {{ v['versions'] }}
                                </td>
                                <td class="center">
                                    {{ v['versions_description'] }}
                                </td>
                                <td class="center" style="word-wrap:break-word;word-break:break-all;">
                                    {% if v['channel'] ==89 %}
                                    ios
                                    {% elseif v['channel'] == 90 %}
                                    安卓
                                    {% else %}
                                    未确定
                                    {% endif %}
                                </td>
                                <td class="center">
                                    {% if v['is_compulsive'] ==0 %}
                                    否
                                    {% elseif v['is_compulsive'] == 1 %}
                                    是
                                    {% endif %}
                                </td>

                                <td class="center">
                                    {% if v['status'] ==1 %}
                                    启用
                                    {% elseif v['status'] == 0 %}
                                    未启用
                                    {% endif %}
                                </td>
                                <td class="center">
                                    <a class="green" href="/appversion/edit?id={{ v['versions_id'] }}">
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