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
                            疗程列表
                        </div>
                        <form action="/goodstreatment/list" method="get">
                            <div class="tools-box">
                                <label class="clearfix">
                                    <span>商品名称或ID：</span>
                                    <input type="text" name="goods_name" placeholder="输入商品名称或ID" class="tools-txt" value="{{ seaData['goods_name'] }}">
                                </label>
                                <label class="clearfix">
                                    <span>状态：</span>
                                    <select name="status" class="tools-txt">
                                        <option value="">全部</option>
                                        <option value="1" {% if seaData['status'] == 1 %}selected{% endif %}>进行中</option>
                                        <option value="2" {% if seaData['status'] == 2 %}selected{% endif %}>暂停</option>
                                    </select>
                                    <span>适用平台：</span>
                                    <select name="platform" class="tools-txt">
                                        <option value="">全部</option>
                                        {% if shopPlatform is defined and shopPlatform is not empty %}
                                            {% for key,platform in shopPlatform %}
                                                {% set sel = 'platform_'~key %}
                                                <option value="platform_{{ key }}" {% if seaData['platform'] == sel  %}selected{% endif %}>{{ platform }}</option>
                                            {% endfor %}
                                        {% endif %}
                                    </select>
                                    <button class="btn btn-primary" type="submit">搜索</button>
                                </label>
                                <a href="/goodstreatment/add"><button class="btn btn-primary btn-rig" type="button">添加疗程</button></a>
                            </div>
                        </form>
                        <!-- div.table-responsive -->

                        <!-- div.dataTables_borderWrap -->
                        <div>
                            <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th style="width: 300px;">商品名称</th>
                                    <th>疗程阶梯数</th>
                                    <th>疗程阶梯价</th>
                                    <th>适用平台</th>
                                    <th>状态</th>
                                    <th>创建时间</th>
                                    <th>操作</th>
                                </tr>
                                </thead>

                                <tbody>

                                <!-- 遍历商品信息 -->
                                {% for v in data['list'] %}
                                <tr>
                                    <td>{{ v['goods_id'] }}</td>
                                    <td>{{ v['goods_name'] }}</td>
                                    <td>{{ v['t_count'] }}</td>
                                    <td>
                                        {% for val in v['ladder'] %}
                                        <p>{{ val['min_goods_number'] }}&nbsp;&nbsp;&nbsp;&nbsp;{{ val['unit_price'] }}/件&nbsp;&nbsp;&nbsp;&nbsp;促销语： {{ val['promotion_msg'] }}</p>
                                        {% endfor %}
                                    </td>
                                    <td>{{ v['platform'] }}</td>
                                    <td>
                                        {%  if v['status'] == 1 %}
                                        <i class="ace-icon glyphicon btn-xs btn-info glyphicon-time status" data-id="{{ v['goods_id'] }}" data="{{ v['status'] }}" style="cursor: pointer;">进行中</i>
                                        {% elseif v['status'] == 2 %}
                                        <i class="ace-icon glyphicon btn-xs btn-danger glyphicon-off status" data-id="{{ v['goods_id'] }}" data="{{ v['status'] }}" style="cursor: pointer;">暂停</i>
                                        {% endif %}
                                    </td>
                                    <td>{{ date('Y-m-d', v['create_time']) }}</td>
                                    <td>
                                        <div class="hidden-sm hidden-xs action-buttons">
                                            <a class="green" href="/goodstreatment/edit?goods_id={{ v['goods_id'] }}"  title="编辑">
                                                <i class="ace-icon fa fa-pencil bigger-130"></i>
                                            </a>
                                            <a class="red del" href="javascript:;" data-id="{{ v['goods_id'] }}" title="删除">
                                                <i class="ace-icon fa fa-trash-o bigger-130"></i>
                                            </a>
                                        </div>
                                     </td>
                                </tr>
                                {% else %}
                                <tr>
                                    <td colspan="30">暂无数据</td>
                                </tr>
                                {% endfor %}
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
<script src="http://{{ config.domain.static }}/assets/admin/js/goodstreatment/globalGoodsTreatment.js"></script>
{% endblock %}