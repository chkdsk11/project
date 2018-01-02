{% extends "layout.volt" %}

{% block content %}
<div class="page-content">

    <div class="row">
        <div class="col-xs-12">
            <!-- PAGE CONTENT BEGINS -->
            <div class="page-header">
                <h1>
                    编辑疗程
                </h1>
            </div>
            <div class="tools-box">
                <label class="clearfix">
                    <span><span style="color: #ff0000">*</span>活动平台：</span>
                    <label>
                        <input name="platform_pc" type="checkbox" {% if data['data'][0]['platform_pc'] == 1 %}checked{% endif %} class="ace">
                        <span class="lbl">&nbsp;PC</span>
                    </label>
                    <label>
                        <input name="platform_app" type="checkbox" {% if data['data'][0]['platform_app'] == 1 %}checked{% endif %} class="ace">
                        <span class="lbl">&nbsp;APP</span>
                    </label>
                    <label>
                        <input name="platform_wap" type="checkbox" {% if data['data'][0]['platform_wap'] == 1 %}checked{% endif %} class="ace">
                        <span class="lbl">&nbsp;WAP</span>
                    </label>
                    <label style="display: none">
                        <input name="platform_wechat" type="checkbox" {% if data['data'][0]['platform_wechat'] == 1 %}checked{% endif %} class="ace" value="1">
                        <span class="lbl">&nbsp;微商城</span>
                    </label>
                </label><br/>
                <label class="clearfix">
                    <span>互斥活动：</span>
                    {% if promotionEnum is defined %}
                    {% for k,v in promotionEnum %}
                    <label>
                        <input name="promotion_mutex[]" type="checkbox" class="ace" value="{{k}}" {% if in_array(k, data['data'][0]['promotion_mutex']) %}checked{% endif %}>
                        <span class="lbl">&nbsp;{{v}}&nbsp;&nbsp;</span>
                    </label>
                    {% endfor %}
                    {% endif %}
                </label>
            </div>
            <div class="tools-box">
                <label class="clearfix">
                    <button class="btn btn-primary" type="button" id="allsave">全部保存</button>
                </label>
                <label class="clearfix">
                    <button class="btn btn-primary" type="button" id="batchdel">批量删除</button>
                </label>
            </div>
            <!-- div.dataTables_borderWrap -->
            <div>
                <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
                        <th><input type="checkbox" name="all" class="all">&nbsp;&nbsp;件数</th>
                        <th>单价价格</th>
                        <th>促销语</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody id="list">
                    {% for v in data['data'] %}
                    <tr height="35px;" class="allsave" id="ids{{ v['id'] }}">
                        <td><input type="checkbox" class="batchdel" name="id" value="{{ v['id'] }}">&nbsp;&nbsp;<input type="text" name="min_goods_number" id="min_goods_number_{{ v['id'] }}" value="{{ v['min_goods_number'] }}"></td>
                        <td><input type="text" name="unit_price" id="unit_price_{{ v['id'] }}" value="{{ v['unit_price'] }}"></td>
                        <td><input type="text" name="promotion_msg" id="promotion_msg_{{ v['id'] }}" value="{{ v['promotion_msg'] }}"></td>
                        <td>
                            <input type="button" class="btn btn-xs btn-primary save" data-id="{{ v['id'] }}" goods-id="{{ v['goods_id'] }}" value="保存" />
                            <input type="button" class="btn btn-xs btn-danger delete" data-id="{{ v['id'] }}" value="删除" />
                        </td>
                    </tr>
                    {% endfor %}
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