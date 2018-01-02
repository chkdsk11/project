{% extends "layout.volt" %}

{% block content %}
    <div class="page-content">
        <div class="row">
            <div class="col-xs-12">
                <!-- PAGE CONTENT BEGINS -->
                <div class="clearfix">
                    <div class="pull-right tableTools-container"></div>
                </div>
                <div class="table-header">
                    短信模板列表
                </div>
                <!-- 搜索 -->
                <form action="/smstemplate/list" method="get">
                    <div class="tools-box">
                        <label class="clearfix">
                            <select name="signature" class="tools-txt">
                                <option value="">全部签名</option>
                                {% if data['signatures'] is defined %}
                                    {% for v in data['signatures'] %}
                                        <option value="{{ v }}" {% if v == option['signature']  %}selected{% endif %}>{{ v }}</option>
                                    {% endfor %}
                                {% endif %}
                            </select>
                        </label>
                        <label class="clearfix">
                            <input type="text" name="contents" placeholder="可输入场景或短信内容" class="tools-txt" value="{{ option['contents'] }}">
                            <button class="btn btn-primary" type="submit">搜索</button>
                        </label>
                    </div>
                </form>

                <!-- div.dataTables_borderWrap -->
                <div>
                    <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>使用场景</th>
                                <th>场景码</th>
                                <th>使用签名</th>
                                <th>短信内容</th>
                                <th>操作</th>
                            </tr>
                        </thead>

                        <tbody>
                            <!-- 遍历商品信息 -->
                            {% if data['list'] is defined and data['list'] is not empty %}
                                {% for v in data['list'] %}
                                    <tr>
                                        <td>
                                            {{ v['template_name'] }}
                                        </td>
                                        <td>
                                            {{ v['template_code'] }}
                                        </td>
                                        <td>
                                            {{ v['signature'] }}
                                        </td>
                                        <td>
                                            {{ v['content'] }}
                                        </td>
                                        <td>
                                            <div class="hidden-sm hidden-xs action-buttons">
                                                <a class="green" href="javascript:;" onclick="showWindow('编辑短信模板', '/smstemplate/edit?template_id={{ v['template_id'] }}', 800, 500, true, true);" title="编辑">
                                                    <i class="ace-icon fa fa-pencil bigger-130"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                {% endfor %}
                            {% else %}
                                <tr>
                                    <td colspan="30">暂无数据</td>
                                </tr>
                            {% endif %}
                            <!-- 遍历商品信息 end -->
                        </tbody>
                    </table>
                </div>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.page-content -->
    {{ data['page'] }}
{% endblock %}

{% block footer %}
    <script src="http://{{ config.domain.static }}/assets/admin/js/sms/sms.js"></script>
{% endblock %}