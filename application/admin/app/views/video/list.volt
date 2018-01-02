{% extends "layout.volt" %}

{% block content %}
            <div class="page-content">

                <div class="row">
                    <div class="col-xs-12">
                        <!-- PAGE CONTENT BEGINS -->
                        <div class="clearfix">
                            <div class="pull-right tableTools-container"></div>
                        </div>
                        <form action="/video/list" method="get">
                            <div class="tools-box">
                                <label class="clearfix">
                                    <input type="text" name="video_name" placeholder="输入视频名称" class="tools-txt" value="{{ seaData['video_name'] }}">
                                </label>
                                <label class="clearfix">
                                    <select name="status" class="tools-txt">
                                        <option value="0">视频状态</option>
                                        {% for key, value in status %}
                                        <option value="{{ value }}" {% if value == seaData['status'] %}selected{% endif %} >{{ key }}</option>
                                        {% endfor %}
                                    </select>
                                    <button class="btn btn-primary" type="submit">搜索</button>
                                </label>
                                <button class="btn btn-primary btn-rig" id="up_video" type="button">更新视频状态</button>
                                <button class="btn btn-primary btn-rig" type="button" onclick="showWindow('添加视频','/video/add', 600, 335);">添加视频</button>
                            </div>
                        </form>
                        <div class="table-header">
                            视频列表
                        </div>
                        <!-- div.table-responsive -->

                        <!-- div.dataTables_borderWrap -->
                        <div>
                            <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>视频ID</th>
                                    <th>视频截图</th>
                                    <th>视频名称</th>
                                    <th>视频简介</th>
                                    <th>视频地址</th>
                                    <th>播放时长</th>
                                    <th>状态</th>
                                    <th>上传时间</th>
                                    <th style="width:65px;">操作</th>
                                </tr>
                                </thead>

                                <tbody>

                                <!-- 遍历商品信息 -->
                                {% for v in video['list'] %}
                                <tr>
                                    <td>{{ v['id'] }}</td>
                                    <td>{{ v['video_id'] }}</td>
                                    <td><img src="{{ v['img'] }}"> </td>
                                    <td>{{ v['video_name'] }}</td>
                                    <td style="width: 200px;word-break: break-all;">{{ v['video_desc'] }}</td>
                                    <td style="width: 230px;word-break: break-all;">{{ v['video_url'] }}</td>
                                    <td>{{ v['video_duration'] }}</td>
                                    <td>{{ v['status'] }}</td>
                                    <td>{{ date('Y-m-d H:i:s', v['add_time']) }}</td>
                                    <td>
                                        <div class="hidden-sm hidden-xs action-buttons">
                                            <a class="green" href="/video/edit?id={{ v['id'] }}" title="编辑">
                                                <i class="ace-icon fa fa-pencil bigger-130"></i>
                                            </a>
                                            <a class="red del" href="javascript:;" data-id="{{ v['video_id'] }}" title="删除">
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
            {{ video['page'] }}
{% endblock %}

{% block footer %}
<script src="http://{{ config.domain.static }}/assets/admin/js/video/sds_sdk.js"></script>
<script src="http://{{ config.domain.static }}/assets/admin/js/video/globalvideo.js"></script>
{% endblock %}