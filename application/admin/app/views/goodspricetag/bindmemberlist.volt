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
                            绑定会员列表
                        </div>
                        <form action="/goodspricetag/bindmemberlist" method="get">
                            <div class="tools-box">
                                <label class="clearfix">
                                    <input type="text" name="keyword" placeholder="输入手机号/会员名/标签名" class="tools-txt" value="{{ seaData['keyword'] }}">
                                </label>
                                <label class="clearfix">
                                    <select name="searchTag" class="tools-txt">
                                        <option value="0">请选择标签名</option>
                                        {% for tv in tagList %}
                                        <option value="{{ tv['tag_id'] }}" {% if seaData['searchTag'] == tv['tag_id'] %}selected{% endif %}>{{ tv['tag_name'] }}</option>
                                        {% endfor %}
                                    </select>
                                    <button class="btn btn-primary" type="submit">搜索</button>
                                </label>
                                <a href="/goodspricetag/downtagtpl"><button class="btn btn-primary btn-rig" type="button" id="downTpl">标签模版</button></a>
                                <button class="btn btn-primary btn-rig" type="button" id="import">确认导入</button>
                                <label class="btn-rig">
                                    <span class="btn btn-primary" style="cursor: pointer;">导入会员标签</span>
                                    <span class="file-name">请选择导入的文件</span>
                                    <input type="file" id="files" class="js-file-name" name="files" onchange="(function(input, tip) {
                                         tip.html(input.get(0).files[0].name);
                                    })($(this), $(this).parent().find('.file-name'))"/>
                                </label>
                                <label class="btn-rig">
                                    <span>导入会员：</span>
                                    <select name="tag_id" id="tag_id" class="tools-txt">
                                        <option value="0">请选择标签名</option>
                                        {% for tv in tagList %}
                                        <option value="{{ tv['tag_id'] }}">{{ tv['tag_name'] }}</option>
                                        {% endfor %}
                                    </select>
                                </label>
                            </div>
                            <div class="tools-box">
                                <label class="clearfix">
                                    <span>单个添加新会员：</span>
                                    <input type="text" name="phone" placeholder="手机号" class="tools-txt">
                                </label>
                                <label class="clearfix">
                                    <select name="tagid" class="tools-txt">
                                        <option value="0">请选择标签名</option>
                                        {% for tv in tagList %}
                                        <option value="{{ tv['tag_id'] }}">{{ tv['tag_name'] }}</option>
                                        {% endfor %}
                                    </select>
                                    <button class="btn btn-primary" type="button" id="addtag">确认添加</button>
                                </label>
                                <label class="clearfix">
                                <button class="btn btn-primary" type="button" id="batchdel">批量删除</button>
                                </label>
                            </div>
                        </form>
                        <!-- div.table-responsive -->

                        <!-- div.dataTables_borderWrap -->
                        <div>
                            <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th class="center">
                                        <input type="checkbox" class="checkbox_select">
                                    </th>
                                    <th>手机号</th>
                                    <th>会员名</th>
                                    <th>会员标签</th>
                                    <th>更新时间</th>
                                    <th>标签是否启用</th>
                                    <th>备注</th>
                                    <th>操作</th>
                                </tr>
                                </thead>

                                <tbody>

                                <!-- 遍历商品信息 -->
                                {% for v in data['list'] %}
                                <tr>
                                    <td class="center" style="width: 10px;">
                                        <input type="checkbox" name="checkbox" value="{{ v['tag_id'] }}" u-id="{{ v['user_id'] }}">
                                    </td>
                                    <td>{{ v['phone'] }}</td>
                                    <td>{{ v['username'] }}</td>
                                    <td>{{ v['tag_name'] }}</td>
                                    <td>{{ date('Y-m-d H:i:s', v['add_time']) }} </td>
                                    <td>
                                        <i class="ace-icon glyphicon btn-xs btn-{% if v['status'] == 1 %}info glyphicon-ok{% else %}danger glyphicon-remove{% endif %} userstatus" data-id="{{ v['tag_id'] }}" data="{{ v['status'] }}" user-id="{{ v['user_id'] }}"  style="cursor: pointer;" ></i>
                                    </td>
                                    <td>
                                        <div class="tools-box">
                                            <label class="clearfix">
                                                <input type="text" name="remark" placeholder="" class="tools-txt" value="{{ v['remark'] }}">
                                            </label>
                                            <label class="clearfix">
                                                <button class="btn btn-primary update" tagid="{{ v['tag_id'] }}" userid="{{ v['user_id'] }}" type="button">保存</button>
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="hidden-sm hidden-xs action-buttons">
                                            <a class="red deltag" href="javascript:;" data-id="{{ v['tag_id'] }}" u-id="{{ v['user_id'] }}" title="删除">
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
<script src="http://{{ config.domain.static }}/assets/js/ajaxfileupload.js"></script>
<script src="http://{{ config.domain.static }}/assets/admin/js/goodspricetag/globalGoodsPriceTag.js"></script>
{% endblock %}