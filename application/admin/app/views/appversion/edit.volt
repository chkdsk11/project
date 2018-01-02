{% extends "layout.volt" %}



{% block content %}
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/jquery.datetimepicker.css" class="ace-main-stylesheet" />
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/admin/css/coupon_addon.css" class="ace-main-stylesheet" />
<!--颜色插件-->
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/colorpicker/css/colorpicker.css"/>


<style>
    #categoryBox{
        top:0;left:0;
    }
</style>
<div class="main-container" id="main-container">
    <div class="main-content">
        <div class="main-content-inner">
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        编辑
                    </h1>
                </div><!-- /.page-header -->
            </div>
            <form id="addAdForm"  class="form-horizontal" action="" method="post"  enctype="multipart/form-data">
                <div class="">
                </div><!-- /.basic -->
                <div class="row">
                    <div class="col-xs-12">
                        <!-- PAGE CONTENT BEGINS -->

                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right"> 版本号 </label>

                            <div class="col-sm-9">
                                <input type="text" name="versions" id="versions" value="{% if info['versions'] is defined %} {{ info['versions'] }}{% endif %}" class="col-xs-10 col-sm-5" placeholder="请输入版本号" />

                            </div>
                        </div>

                        <div class="space-4"></div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> 使用平台 </label>
                                <div class="col-xs-12 col-sm-9">
                                    <select id="channel" name="channel">
                                        {% if info['channel'] is defined %}
                                            {% if info['channel'] == 89 %}
                                                <option  selected = "selected" value="89">IOS</option>
                                            {% elseif info['channel'] == 90 %}
                                                <option  selected = "selected" value="90">Android</option>
                                            {% endif %}
                                        {% else %}
                                            <option  value="90">Android</option>
                                            <option  selected = "selected" value="89">IOS</option>
                                        {% endif %}
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">是否强制更新 </label>
                                <div class="checkbox promotion_platform">

                                    <label>
                                        <input name="is_up" type="radio" class="ace" {% if info['is_compulsive'] is defined and  info['is_compulsive']=='0' %} checked {% else %}checked{% endif %}   value="0">
                                        <span class="lbl">&nbsp;否</span>
                                    </label>
                                    <label>
                                        <input name="is_up" type="radio" {% if info['is_compulsive'] is defined and  info['is_compulsive']=='1' %} checked {% endif %} class="ace" value="1">

                                        <span class="lbl">&nbsp;是</span>
                                    </label>
                                </div>
                            </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right">是否启用 </label>
                            <div class="checkbox promotion_platform">
                                <label>
                                    <input name="status" type="radio" class="ace" {% if info['status'] is defined and  info['status']==1 %} checked {% else %}checked{% endif %}   value="1">
                                    <span class="lbl">&nbsp;启用</span>
                                </label>
                                <label>
                                    <input name="status" type="radio" {% if info['status'] is defined and  info['status']==0 %} checked {% endif %} class="ace" value="0">
                                    <span class="lbl">&nbsp;不启用</span>
                                </label>
                            </div>
                        </div>
                            <!--  安卓 start -->
                            <div id="Android" {% if info['channel'] is defined and  info['channel']==90 %}style="display:block;" {% else %}style="display:none;"{% endif %}  >
                                <div class="form-group">
                                    <label class="col-sm-3 control-label no-padding-right"></label>
                                    <div class="col-xs-12 col-sm-9">
                                        <table width="90%" border="0" style="margin-top:-20px;text-align:left;">
                                            <tr>
                                                <td width="10%">选择渠道</td>
                                                <td width="20%">
                                                    <select id="clannel_data">
                                                        <option value="">未选择</option>
                                                        {% if clannel %}
                                                            {% for v in clannel %}
                                                            <option value="{{ v['id'] }}">{{ v['name'] }}</option>
                                                            {% endfor %}
                                                        {% endif %}
                                                    </select>
                                                </td>
                                                <td width="5%">下载url</td>
                                                <td width="55%"><input type="text" id="clannel_url" style="width:80%;" placeholder="请输入下载地址"/></td>
                                                <td width="20%"><input type="button" class="btn  btn-sm btn-primary ajax-show" value="添加" id="add_clannel"></td>
                                            </tr>
                                        </table><br />
                                        <table width="90%" border="0" style="text-align:left" id="product_list">
                                            <tr >
                                                <th width="10%">下载渠道</th>
                                                <th width="45%">下载链接</th>
                                                <th width="10%"">&nbsp;操作</th>
                                            </tr>
                                            {% if download is defined %}
                                            {% for d in download %}
                                            <tr >
                                                <td><input class='pid' type='hidden' value="{{ d['down_channel']}}"/> {{ d['name']}}</td>
                                                <td>{{ d['url']}}</td>
                                                <th width="10%"">&nbsp;<input type="button" value="删除" onclick="delclannel(this,{{ d['id']}} )" class="btn"></th>
                                            </tr>
                                            {% endfor %}
                                            {% endif %}
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <!--  安卓 end -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right"> 版本说明： </label>
                                <div class="col-xs-12 col-sm-9">
                                    <textarea rows="5" cols="50" id="description" name="description">{% if info['versions_description'] is defined %} {{info['versions_description']}} {% endif %}</textarea>
                                </div>
                            </div>

                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <button id="addAd" class="btn btn-info" type="button">
                                        <i class="ace-icon fa fa-check bigger-110"></i>
                                        保存
                                    </button>
                                    <button  class="btn btn-info" onclick="history.go(-1);return false;" type="button">
                                        <i class="ace-icon fa fa-check bigger-110"></i>
                                        返 回
                                    </button>   &nbsp;                                &nbsp; &nbsp; &nbsp;
                                    <!--<button class="btn" type="reset">
                                        <i class="ace-icon fa fa-undo bigger-110"></i>
                                        重置
                                    </button>-->
                                </div>
                            </div>
                        </div>
                    </div><!-- /.main-content -->
            </form>
        </div>
    </div>
</div><!-- /.main-container -->

{% endblock %}

{% block footer %}
<script src="http://{{ config.domain.static }}/assets/admin/js/appversion/edit.js"></script>

{% endblock %}