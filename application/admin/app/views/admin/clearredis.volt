{% extends "layout.volt" %}

{% block content %}
<style>
.width-percent-50{
    width: 50% !important;
}
.nav-tabs>li.active>a, 
.nav-tabs>li.active>a:hover,
.nav-tabs>li.active>a:focus{
    box-shadow: none;
    border-radius: 5px 5px 0 0 !important;
    text-align: center;
}
.nav-tabs>li>a,
.nav-tabs>li>a:focus{
    text-align: center;
}
.tab-content{
    border:0;
}
.node-one{
    padding-left: 20px !important;
}
.node-two{
    padding-left: 40px !important;
}
.node-three{
    padding-left: 40px !important;
}
</style>
<form class="form-horizontal" role="form" action="" method="post">
    <input type="hidden" name="role_id" value="{{ adminRole['role_id'] }}"/>
    <ul class="nav nav-tabs" id="myTab">
        <li class="active width-percent-50">
            <a data-toggle="tab" href="#base">redis前缀key</a>
        </li>
        <li class="width-percent-50">
            <a data-toggle="tab" href="#pc">其他手动填写</a>
        </li>
    </ul><!-- /.nav-tabs -->
    <div class="tab-content">
        <div id="base" class="tab-pane fade in active">
            <div class="widget-body">
                <h5>redis前缀key</h5> 
                <div class="widget-main no-padding">
                    <input type="checkbox" class="ace" level="all" site="0">
                    <table class="table table-bordered table-striped table-hover text-center">
                        <thead>
                            <tr>
                                <th class="left node-one">
                                    <input type="checkbox" class="ace" level="all">
                                        <span class="lbl green"> 全选 </span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="collapse_node_1" class="collapse in" aria-expanded="true">
                                <tr>
                                   <td class="node-three">
                                        <div class="control-group">
                                        {% for ko,vo in redisIndexKeys %}
                                            <div class="checkbox col-xs-12 col-sm-3 text-left no-margin-top">
                                                <label>
                                                    <input name="index[]" type="checkbox" class="ace" value="{{ ko }}" level="son">
                                                    <span class="lbl"> {{ vo }} </span>
                                                </label>
                                            </div>
                                        {% endfor %}
                                        </div>
                                    </td>

                                </tr>
                            </tbody>
                    </table>                   
                    <!-- submit -->
                    <div class="col-xs-12 col-sm-1 col-sm-offset-3" style="margin-top:20px;">
                        <button class="btn btn-danger delete">
                            <i class="ace-icon fa fa-undo bigger-110"></i>
                            删除
                        </button>
                    </div>
                </div><!-- /.widget-main -->
            </div><!-- /.widget-body -->
        </div>
        <div id="pc" class="tab-pane fade">
            <div class="widget-body">
                <div class="widget-main no-padding">
                   <div class="page-header">
                    <h1>redis缓存处理：<span>key前缀(也可以是整个key,则只是删除这个key)</span></h1>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <!-- PAGE CONTENT BEGINS -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">key前缀</label>
                                <div class="col-sm-9">
                                    <div class="pos-rel">
                                        <textarea id="keys" placeholder="一行一个key" name="keys"></textarea>
                                    </div>
                                </div>
                            </div>
                    </div><!-- /.col -->
                </div>
                    <!-- submit -->
                    <div class="col-xs-12 col-sm-1 col-sm-offset-3" style="margin-top:20px;">
                        <button class="btn btn-danger delete">
                            <i class="ace-icon fa fa-undo bigger-110"></i>
                            删除
                        </button>
                    </div>
                </div><!-- /.widget-main -->
            </div><!-- /.widget-body -->
        </div>
    <div>
</form>
<style>
    .form-group {
        margin-top:10px;
    }
    #keys {
        width: 600px;
        height: 260px;
    }
</style>
   
{% endblock %}

{% block footer %}
<script src="http://{{ config.domain.static }}/assets/js/jquery.form.js"></script>
    <script>
        $(function(){
            // 全选--start
            $("input[level='all']").click(function(){
                $("input[level='son']").prop("checked",$(this).prop("checked"));
            });
            // 全选--end
        });
        $('.form-horizontal').on('submit', function() {
            var index = layer.load(1, {
                  shade: [0.1,'#fff'] //0.1透明度的白色背景
                });
            $(this).ajaxSubmit({
                type: 'post', // 提交方式 get/post
                url: '', // 需要提交的 url
                data: {

                },
                success: function(data) { 
                    layer.close(index);
                    layer_required(data.msg);
                }
            });
            // 阻止表单自动提交事件
            return false; 
        });
    </script>
{% endblock %}