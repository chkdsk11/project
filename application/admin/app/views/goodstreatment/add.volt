{% extends "layout.volt" %}

{% block content %}
<div class="page-content">

    <div class="row">
        <div class="col-xs-12">
            <!-- PAGE CONTENT BEGINS -->
            <div class="clearfix">
                <div class="pull-right tableTools-container"></div>
            </div>
            <div class="page-header">
                <h1>
                    添加疗程
                </h1>
            </div>

            <div class="tools-box">
                <label class="clearfix">
                    <!--<span class="text-red">*</span>-->

                    <span><span style="color: #ff0000">*</span>活动平台：</span>
                    <label>
                        <input name="platform_pc" type="checkbox" class="ace" value="1" checked>
                        <span class="lbl">&nbsp;PC</span>
                    </label>
                    <label>
                        <input name="platform_app" type="checkbox" class="ace" value="1" checked>
                        <span class="lbl">&nbsp;APP</span>
                    </label>
                    <label>
                        <input name="platform_wap" type="checkbox" class="ace" value="1" checked>
                        <span class="lbl">&nbsp;WAP</span>
                    </label>
                    <label style="display: none">
                        <input name="platform_wechat" type="checkbox" class="ace" value="0">
                        <span class="lbl">&nbsp;微商城</span>
                    </label>
                </label><br/>
                <label class="clearfix">
                    <span>互斥活动：</span>
                    {% if promotionEnum is defined %}
                    {% for k,v in promotionEnum %}
                    <label>
                        <input name="promotion_mutex[]" type="checkbox" class="ace" value="{{k}}">
                        <span class="lbl">&nbsp;{{v}}&nbsp;&nbsp;</span>
                    </label>
                    {% endfor %}
                    {% endif %}
                </label>
            </div>
            <div class="tools-box">
                <label class="clearfix">
                    <button class="btn btn-primary" type="button" id="batchdel">批量删除</button>
                </label>
                <label class="clearfix">
                    <form action="/goodstreatment/list" id="search">
                        <input type="text" name="goods_name" placeholder="输入商品名称或ID" class="tools-txt" >
                        <button class="btn btn-primary" type="submit">搜索</button>
                    </form>
                </label>
                <label class="clearfix" id="showgoods" style="display: none;">
                    <span>商品：</span>
                    <select name="goods" id="goods" class="tools-txt">
                    </select>
                </label>
            </div>
            <!-- div.table-responsive -->

            <!-- div.dataTables_borderWrap -->
            <div>
                <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
                        <th><input type="checkbox" name="all" class="all">&nbsp;&nbsp;件数</th>
                        <th>单件价格</th>
                        <th>促销语</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody id="list">
                    </tbody>
                </table>
            </div>
        </div><!-- /.col -->
    </div><!-- /.row -->

</div><!-- /.page-content -->
{% endblock %}

{% block footer %}
<script src="http://{{ config.domain.static }}/assets/admin/js/goodstreatment/globalGoodsTreatment.js"></script>
{% endblock %}
