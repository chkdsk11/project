{% extends "layout.volt" %}

        {% block content %}
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/jquery.datetimepicker.css" class="ace-main-stylesheet" />
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/admin/css/coupon_addon.css" class="ace-main-stylesheet" />



<style>
#categoryBox{
    top:0;left:0;
}
</style>
<div class="main-container" id="main-container">
<div class="main-content">
    <div class="main-content-inner">
        <div class="page-content">
        </div>
        <div class="">
            <div class="table-list" id="load_priv">
	        <span style="color:red;font-size:14px;font-weight:bold;">
		        注：目前只有首页相关广告位的操作在APP端有效！
	        </span>
                <form id="defaultForm" method="post" action='' onsubmit="ajaxSubmit('defaultForm');return false;" >
                    <table width="100%" cellspacing="0" id="treeTable1">
                        <tbody>
                        {% if list %}
                        {% for v in list %}
                        <tr id="{{ v['id'] }}" pID='{{ v['parent_id'] }}'>
                        <td>
                            <input type='checkbox' name='menuid[]' value="{{ v['id'] }}" level="{{ v['lev'] }}" {{ v['checked'] }} />
                            {{ v['adpositionid_name'] }}
                            {{ v['versions'] }}
                            {{ v['channel'] }}
                        </td>
                        </tr>
                        {% endfor %}
                        {% else %}
                        <tr id="{{ v['id'] }}" pID='{{ v['parent_id'] }}'>
                        <td>没有广告位</td>
                    </tr>
                    {% endif %}
                </tbody>
            </table>
            <div class="btn">
                <input type="submit"  class="btn btn-info"  name="do_submit" value="提交" />
            </div>
        </form>
    </div>
        </div><!-- /.basic -->
    </div><!-- /.main-content -->

</div>
        </div>
        </div><!-- /.main-container -->

        {% endblock %}

        {% block footer %}
<script>
    var STATIC_URL = "http://{{ config.domain.static }}";
</script>
<script src="http://{{ config.domain.static }}/assets/JqueryTreeTable/treeTable/jquery.treeTable.js"></script>
<script src="http://{{ config.domain.static }}/assets/admin/js/ad/home_show.js"></script>

        {% endblock %}