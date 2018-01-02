{% extends "layout.volt" %}

{% block content %}
<div class="page-header">

</div>
<form id="site_form" class="form-horizontal" role="form" action="/site/edit" method="post">
    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="form-field-1">站点名称</label>

        <div class="col-sm-9">
            <input type="text" id="site_name" name="site_name" id="form-field-1" placeholder="站点名称" class="col-xs-10 col-sm-5" value="{{ site_menu['site_name'] }}" required/>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="form-field-1">是否启用</label>
        <div class="col-sm-9">
            <div class="radio">
                <label>
                    <input name="is_enable" value="1" class="ace" type="radio" {{ site_menu['is_enable']?'checked':'' }}/>
                    <span class="lbl">是</span>
                </label>
                <label>
                    <input name="is_enable" value="0" class="ace" type="radio" {{ !site_menu['is_enable']?'checked':'' }}/>
                    <span class="lbl">否</span>
                </label>
            </div>
            <input type="hidden" name="site_id" value="{{ site_menu['site_id'] }}" />
        </div>
    </div>

    <!--row-->
    <div class="checkbox">
        <label class="control-label bolder blue">
            <input id="SelectAll" class="ace ace-checkbox-2 " type="checkbox" />
            <span class="lbl">全选/全不选</span>
        </label>
    </div>
    {% for k,v in menus %}
    <h3 class="header smaller lighter blue">
      {{ v['menu_title'] }}
    </h3>
    <div class="row">
        {% if v['son'] is defined and v['son'] is not empty %}
    {% for key,val in v['son']  %}
        <div class="col-xs-12 col-sm-2">
            <div class="control-group">
                <div class="checkbox">
                    <label class="control-label bolder blue">
                        <input id="g_level_{{ val['id'] }}" class="ace ace-checkbox-2 first-level" data-id = "{{ val['id'] }}" type="checkbox" />
                        <span class="lbl">{{ val['menu_title'] }}</span>
                    </label>
                </div>
                {% if val['son'] is defined and val['son'] is not empty %}
                {% for kk,vv in val['son'] %}
                <div class="checkbox">
                    <label style="margin-left:15px;">
                        <input name="menu_id[]" value="{{ vv['id'] }}" data-pid="{{ val['id'] }}" class="ace ace-checkbox-2" type="checkbox" {{ vv['check'] is defined?vv['check']:'' }}/>
                        <span class="lbl">{{ vv['menu_title'] }}</span>
                    </label>
                </div>
                {% endfor %}
                {% endif %}
            </div>
        </div>
    {% endfor %}
        {% endif %}
    </div>
    {% endfor %}
    <!-- /.row -->

    <div class="col-md-offset-3 col-md-9">
        <input   class="btn btn-lg btn-yellow" id="saveBtn" type="button" value="保存" name="submit" />
        &nbsp; &nbsp; &nbsp;
        <a href="javascript:void(0)" onclick="javascript:history.go(-1);return false;">
            <button class="btn btn-lg" type="button">
                返回
            </button>
        </a>
    </div>
</form>
{% endblock %}
{% block footer %}
<script src="http://{{ config.domain.static }}/assets/js/jquery.validate.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/messages_zh.min.js"></script>
    <script type="text/javascript">
        $('.first-level').prop('checked',true);
        $('.first-level').on('click',function () {
            var $this = $(this);
            var id = $this.data('id');
            var CheckStatus = $this.is(":checked");
            if(CheckStatus){
                //选中
                $('input[data-pid="'+id+'"]').prop('checked',true);
            }else{
                //全不选
                $('input[data-pid="'+id+'"]').prop('checked',false);
                $('#SelectAll').prop('checked',false);
            }
            var rb = 0;
            $('input[name="menu_id[]"]').each(function () {
                if(!$(this).is(":checked")){
                    rb = 1;
                }
            });
            if(rb == 0){
                $('#SelectAll').prop('checked',true);
            }
        });
        var _f = 0;
        $('#SelectAll').on('click',function () {
            if(_f == 0){
                $('.first-level').prop('checked',true);
                $('input[name="menu_id[]"]').prop('checked',true);
                _f = 1;
            }else{
                $('.first-level').prop('checked',false);
                $('input[name="menu_id[]"]').prop('checked',false);
                _f = 0;
            }
        });
        $('input[name="menu_id[]"]').each(function () {
            var $this = $(this);
            var pid = $this.data('pid');
            var CheckStatus = $this.is(":checked");
            if(!CheckStatus){
                $('#g_level_'+pid).prop('checked',false);
            }
        });
        $('input[name="menu_id[]"]').on('click',function () {
            var $this = $(this);
            var pid = $this.data('pid');
            var CheckStatus = $this.is(":checked");
            if(!CheckStatus){
                $('#g_level_'+pid).prop('checked',false);
                $('#SelectAll').prop('checked',false);
            }else{
                $('#g_level_'+pid).prop('checked',true);
            }
            var rb = 0;
            $('input[name="menu_id[]"]').each(function () {
                if(!$(this).is(":checked")){
                    rb = 1;
                }
            });
            if(rb == 0){
                $('#SelectAll').prop('checked',true);
            }
        });
        $('#saveBtn').on('click',function () {
            if($('#site_name').val().length < 1){
                layer_required('站点不能为空');
                return false;
            }
            ajaxSubmit('site_form');
        });
    </script>
{% endblock %}
