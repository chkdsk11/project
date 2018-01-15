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
                            会员商品列表
                        </div>
                        <form action="/goodsprice/list" method="get">
                            <div class="tools-box">
                                <label class="clearfix">
                                    <span>商品名称/ID：</span>
                                    <input type="text" name="goods_name" placeholder="输入商品名称或ID" class="tools-txt" value="{{ seaData['goods_name'] }}">
                                </label>
                                <label class="clearfix">
                                    <span>会员标签：</span>
                                    <select name="tag_id" class="tools-txt">
                                        <option value="">全部</option>
                                        {% for val in tagData %}
                                        <option value="{{ val['tag_id'] }}" {% if seaData['tag_id'] == val['tag_id'] %}selected{% endif %}>{{ val['tag_name'] }}</option>
                                        {% endfor %}
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
                                <a href="/goodsprice/add"><button class="btn btn-primary btn-rig" type="button">添加会员商品</button></a>
                            </div>
                        </form>
                        <!-- div.table-responsive -->

                        <!-- div.dataTables_borderWrap -->
                        <div>
                            <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th>商品ID</th>
                                    <th width="230">商品名称</th>
                                    <th>适用平台</th>
                                    <th>商品编码</th>
                                    <th>原价</th>
                                    <th>会员价</th>
                                    <th>会员折扣</th>
                                    <th>会员标签</th>
                                    <!--<th>限购数量</th>-->
                                    <th>更新时间</th>
                                    <th>操作</th>
                                </tr>
                                </thead>

                                <tbody>

                                <!-- 遍历商品信息 -->
                                {% for v in data['list'] %}
                                <tr>
                                    <td>{{ v['id'] }}</td>
                                    <td>{{ v['goods_name'] }}</td>
                                    <td>{{ v['platform'] }}</td>
                                    <td>{{ v['product_code'] }}</td>
                                    <td>{{ v['price'] }}</td>
                                    <td>{% if v['type'] == 1 %}{{ v['member_price'] }}{% else %}-{% endif %}</td>
                                    <td>{% if v['type'] == 2 %}{{ v['rebate'] }}折{% else %}-{% endif %}</td>
                                    <td>{% if v['tag_id'] != 0 %}{{ v['tag_name'] }}{% else %}商城全体{% endif %}</td>
                                    <!--<td>{% if v['tag_id'] == tag_id %}{{ v['limit_number'] }}{% else %}请到促销设置限购{% endif %}</td>-->
                                    <td>{{ date('Y-m-d H:i:s', v['add_time']) }}</td>
                                    <td>
                                        <div class="hidden-sm hidden-xs action-buttons">
                                            <a class="green" href="/goodsprice/edit?id={{ v['tag_goods_id'] }}" title="编辑">
                                                <i class="ace-icon fa fa-pencil bigger-130"></i>
                                            </a>
                                            <a class="red del" href="javascript:;" data-id="{{ v['tag_goods_id'] }}" title="删除">
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
<script src="http://{{ config.domain.static }}/assets/admin/js/goodsprice/globalGoodsPrice.js"></script>
{% endblock %}