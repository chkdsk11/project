{% extends "layout.volt" %}

{% block content %}
<div class="page-header">

</div>
<form id="role_form" class="form-horizontal" role="form" action="" method="post">
    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="form-field-1">角色</label>

        <div class="col-sm-9">
            <input type="text" name="role_name" id="form-field-1" placeholder="角色名称" class="col-xs-10 col-sm-5" value="" />
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="form-field-1">所属站点</label>
        <div class="col-sm-9">
            <select name="site_id" class="col-xs-10 col-sm-5" id="form-field-select-1">
                {% if site is defined and site is not empty %}
                {% for v in site %}
                <option value="{{ v['site_id'] }}">{{ v['site_name'] }}</option>
                {% endfor %}
                {% endif %}
            </select>
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="form-field-1">是否启用</label>
        <div class="col-sm-9">
            <div class="radio">
                <label>
                    <input name="is_enable" value="1" class="ace" type="radio" checked/>
                    <span class="lbl">是</span>
                </label>
                <label>
                    <input name="is_enable" value="0" class="ace" type="radio" />
                    <span class="lbl">否</span>
                </label>
            </div>
        </div>
    </div>
    <!--row-->
    <div class="checkbox">
        <label class="control-label bolder blue">
            <input id="SelectAll" class="ace ace-checkbox-2 " type="checkbox" />
            <span class="lbl">全选/全不选</span>
        </label>
    </div>
    <div id="menu">
        {% if menus is defined and menus is not empty %}
        {% for v in menus %}
        <h3 class="header smaller lighter blue">{{ v['menu_title'] }}</h3>
        <div class="row">
            {% if v['son'] is defined and v['son'] is not empty %}
            {% for vv in v['son'] %}
            <div class="col-xs-12 col-sm-2">
                <div class="control-group">
                    <div class="checkbox">
                        <label class="control-label bolder blue">
                            <input id="g_level_{{ vv['id'] }}" class="ace ace-checkbox-2 first-level" data-id = "{{ vv['id'] }}" type="checkbox" />
                            <span class="lbl">{{ vv['menu_title'] }}</span>
                        </label>
                    </div>
                    {% if vv['son'] is defined and vv['son'] is not empty %}
                    {% for value in vv['son'] %}
                    {% if value['is_enable'] is defined %}
                    <div class="checkbox">
                        <label style="margin-left:15px;">
                            <input name="menu_id[]" value="{{ value['id'] }}" data-pid="{{ vv['id'] }}" class="ace ace-checkbox-2" type="checkbox" />
                            <span class="lbl">{{ value['menu_title'] }}</span>
                        </label>
                    </div>
                    {% endif %}
                    {% endfor %}
                    {% endif %}
                </div>
            </div>
            {% endfor %}
            {% endif %}
        </div>
        {% endfor %}
        {% endif %}
    </div>
    <!-- /.row -->

    <div class="col-md-offset-3 col-md-9">
        <input class="btn btn-lg btn-yellow insertBTN" type="button" value="提交" name="submit" />
    </div>
</form>

{% endblock %}
{% block footer %}

<script type="text/javascript">

    //根据站点ID，权限不同
    $('#form-field-select-1').change(function(){
        var role_id=$("input[name='role_id']").val();
        var site_id=$("#form-field-select-1 option:selected").val();
        $.getJSON('/role/sitemenu',{site_id:site_id}, function(ret){
            if(ret.status) {
                $('#menu').html('');
                $('#menu').html(ret.data);
            }
        });
    });
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
        if($('input[name="role_name"]').val().length < 1){
            layer_required('角色名称不能为空');
            return false;
        }
        ajaxSubmit('role_form');
    });
    $('.insertBTN').on('click',function(){
        if($('input[name="role_name"]').val().length < 1){
            layer_required('角色名称不能为空');
            return false;
        }
        ajaxSubmit('role_form');
    });
</script>

{% endblock %}
