{% extends "layout.volt" %}

{% block content %}
<style>
    #table-edit-stock {text-align: center;}
    #table-edit-stock th {
        text-align: center;
        background: #f2f2f2;
    }
    #table-edit-stock tbody input,#table-edit-stock tbody select{
        display: none;
    }
    #table-edit-stock tbody .on select{
        display: block;
    }
    #table-edit-stock tbody .on .stock-type{
        display: none;
    }
    #table-edit-stock tbody select{
        height: 33px;
        line-height: 14px;
        padding-top: 1px;
        margin:0 auto;
    }
    #table-edit-stock tbody td {
        height: 41px;
        vertical-align:middle;
    }
</style>
<div class="page-content">
    <div class="row">
        <div class="col-xs-12">
            <div class="row">
                <div class="col-xs-12">

                    <div class="form-group clearfix">
                        <div class="row">
                            <form action="/stock/list" method="get" id="export_button">
                                <div class="tools-box">
                                    <label class="clearfix">
                                        <span>SKU编码：</span><input type="text" style="width:200px;" name="sku_id" value="{{ sku_id }}" class="tools-txt" />
                                    </label>
                                    <label class="clearfix">
                                        <span>SPU编码：</span><input type="text" style="width:200px;" name="spu_id" value="{{ spu_id }}" class="tools-txt" />
                                    </label>
                                    <label class="clearfix">
                                        <span>商品名称：</span><input type="text" style="width:200px;" name="name" value="{{ name }}" class="tools-txt" />
                                    </label>

                                    <label class="clearfix" style="float: right;">
                                        <button class="btn btn-primary"  type="submit">搜索</button>
                                    </label>
                                </div>
                            </form>

                        </div>
                    </div>

                    <div>
                        <table id="table-edit-stock" class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th class="center col-xs-1" rowspan="2">
                                        <label class="pos-rel">
                                            <input type="checkbox" class="ace" />
                                            <span class="lbl"></span>
                                            编辑
                                        </label>
                                    </th>
                                    <th rowspan="2" class="col-xs-1" >SKU编码</th>
                                    <th rowspan="2" class="col-xs-1" >SPU编码</th>
                                    <th rowspan="2" class="col-xs-2">商品名称</th>
                                    <!--<th rowspan="2" class="col-xs-2">属性</th>-->
                                    <th rowspan="2" class="col-xs-1">库存类型</th>
                                    <th colspan="5" class="col-xs-3">库存数量</th>
                                </tr>
                                <tr>
                                    <th style="width: 6%;">公共</th>
                                    <th style="width: 6.5%;">PC</th>
                                    <th style="width: 6.5%;">APP</th>
                                    <th style="width: 6.5%;">WAP</th>
                                    <th style="width: 6.5%;">微商城</th>
                                </tr>
                            </thead>
                            <tbody>

                            <!-- 遍历商品信息 -->
                            {% if list is defined %}
                            {% if list != null %}
                                {% for v in list %}
                                    <tr>
                                        <td class="center">
                                            <label class="pos-rel">
                                                <input type="checkbox" class="ace" />
                                                <span class="lbl"></span>
                                            </label>
                                        </td>
                                        <td><a href="#" class="id">{{ v['id'] }}</a></td>
                                        <td>
                                            {% if v['spu_id'] != 0 %}
                                            {{ v['spu_id'] }}
                                            {% endif %}
                                        </td>
                                        <td>{{ v['goods_name'] }}</td>
                                        <!--<td>颜色</td>-->
                                        <td>
                                            <span class="stock-type">
                                                {% if v['is_use_stock'] == 1 %}
                                                    真实
                                                {% elseif v['is_use_stock'] == 2 %}
                                                    虚拟统一
                                                {% elseif v['is_use_stock'] == 3 %}
                                                    虚拟分端
                                                {% endif %}
                                            </span>
                                            <select name="" class="stock-select">
                                                <option {% if v['is_use_stock'] == 1 %}selected{% endif %} value="1">真实</option>
                                                <!-- 公共不能编辑，其他不显示 -->
                                                <option {% if v['is_use_stock'] == 2 %}selected{% endif %} value="2">虚拟统一</option>
                                                <!-- 公共可以编辑，其他不显示 -->
                                                <!--<option {% if v['is_use_stock'] == 3 %}selected{% endif %} value="3">虚拟分端</option>-->
                                                <!-- 公共不显示，其他可以编辑 -->
                                            </select>
                                        </td>
                                        <td>
                                            <span class="stock-puplic">
                                                {% if v['is_use_stock'] == 1 %}
                                                {{ v['v_stock'] }}
                                                {% else %}
                                                {{ v['virtual_stock_default'] }}
                                                {% endif %}
                                            </span>
                                            <input type="text" name="" value="{{ v['virtual_stock_default'] }}" class="col-xs-12" >
                                            <input type="hidden" name="" value="{{ v['v_stock'] }}">
                                        </td>
                                        <td>
                                            <span class="stock-pc">{{ v['virtual_stock_pc'] }}</span>
                                            <input type="text" name="" value="{{ v['virtual_stock_pc'] }}" class="col-xs-12">
                                            <!--<input type="hidden" name="" value="{{ v['virtual_stock_pc'] }}">-->
                                        </td>
                                        <td>
                                            <span class="stock-app">{{ v['virtual_stock_app'] }}</span>
                                            <input type="text" name="" value="{{ v['virtual_stock_app'] }}" class="col-xs-12">
                                            <!--<input type="hidden" name="" value="{{ v['virtual_stock_app'] }}">-->
                                        </td>
                                        <td>
                                            <span class="stock-wap">{{ v['virtual_stock_wap'] }}</span>
                                            <input type="text" name="" value="{{ v['virtual_stock_wap'] }}" class="col-xs-12">
                                            <!--<input type="hidden" name="" value="{{ v['virtual_stock_wap'] }}">-->
                                        </td>
                                        <td>
                                            <span class="stock-weishang">{{ v['virtual_stock_wechat'] }}</span>
                                            <input type="text" name="" value="{{ v['virtual_stock_wechat'] }}" class="col-xs-12">
                                            <!--<input type="hidden" name="" value="{{ v['virtual_stock_wechat'] }}">-->
                                        </td>
                                    </tr>
                                {% endfor %}
                            {% else %}
                                <tr>
                                    <td class="center" colspan="10">
                                        暂无数据……
                                    </td>
                                </tr>
                            {% endif %}
                            {% endif %}
                            <!-- 遍历商品信息 end -->

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div><!-- /.col -->
    </div><!-- /.row -->
</div>
{{ page }}
{% if list['page'] is defined %}{{ list['page'] }}{% endif %}
{% endblock %}

{% block footer %}
<script>
    $(function(){
        var $table = $("#table-edit-stock");
        var $ckeckbox = $("#table-edit-stock tbody").find("input[type=checkbox]");
        $table.on("click","tbody input[type=checkbox]",function(event){
            if($(this).is(':checked')){
                $(this).parents("tr").addClass("on");
                var type = $(this).parents("tr").find(".stock-type").html();
                var find = 'option:contains('+type+')';
                var $allCheckbox = $table.find("tbody tr checkbox");
                var falg = true;
                var $checkAll = $table.find("thead input[type=checkbox]");
                $(this).parents("tr").find(find).prop("selected", true);
                selectStockType($(this).parents("tr").find("select"),type);
                $allCheckbox.each(function(i,item){
                    $(item).prop("checked") ? "" : falg = false;
                });
                falg ? $checkAll.prop("checked",true) : "";
            }else{
                $(this).parents("tr").removeClass("on");
                initStockType($(this),true);
                var $checkAll = $table.find("thead input[type=checkbox]");
                $checkAll.prop("checked") ? $checkAll.prop("checked",false) : "";
            }

        });

        $table.on("change","tbody select",function(event){
            var txt = $(this).find("option:selected").html();
            selectStockType($(this),txt);
            $(this).siblings(".stock-type").html(txt);
        });

        $table.find("tbody select").each(function(i,item){
            initStockType($(this));
        });

        // 全选
        $table.on("click","thead input[type=checkbox]",function(event){
            if($(this).prop("checked")){
                $table.find("tbody input[type=checkbox]").prop("checked",true);
                $table.find("tbody input[type=checkbox]").each(function(i,item){
                    $(item).parents("tr").addClass("on");
                    var type = $(item).parents("tr").find(".stock-type").html();
                    var find = 'option:contains('+type+')';
                    $(item).parents("tr").find(find).prop("selected", true);
                    selectStockType($(item).parents("tr").find("select"),type);
                });
            }else{
                $table.find("tbody input[type=checkbox]").prop("checked",false);
                $table.find("tbody input[type=checkbox]").each(function(i,item){
                    $(item).parents("tr").removeClass("on");
                    initStockType($(item),false);
                });
                ajaxAll();
            }
        });

        // 选择库存类型
        function selectStockType($obj,type){
            var $public = $obj.parents("tr").find(".stock-puplic");
            var $pc = $obj.parents("tr").find(".stock-pc");
            var $app = $obj.parents("tr").find(".stock-app");
            var $wap = $obj.parents("tr").find(".stock-wap");
            var $wei = $obj.parents("tr").find(".stock-weishang");
            $obj.parents("tr").find('input[type=text]').hide();
            $public.show();
            $pc.show();
            $app.show();
            $wap.show();
            $wei.show();
            if(type.trim() == '真实'){
                // var num = $public.parent().find('input[type=hidden]').val();
                var num = $public.siblings('input[type=hidden]').val();
                $public.html(num);
                $pc.html("—");
                $app.html("—");
                $wap.html("—");
                $wei.html("—");
            }
            if(type.trim() == '虚拟统一'){
                $public.hide();
                $public.parent().find('input[type=text]').show();
                $pc.html("—");
                $app.html("—");
                $wap.html("—");
                $wei.html("—");
            }
            if(type.trim() == '虚拟分端'){
                $public.html("—");
                $pc.hide();
                $pc.parent().find('input[type=text]').show();
                $app.hide();
                $app.parent().find('input[type=text]').show();
                $wap.hide();
                $wap.parent().find('input[type=text]').show();
                $wei.hide();
                $wei.parent().find('input[type=text]').show();
            }
        }

        // 初始化库存类型
        function initStockType($obj,ajax){
            var id = $obj.parents("tr").find(".stock-puplic");
            var $public = $obj.parents("tr").find(".stock-puplic");
            var $pc = $obj.parents("tr").find(".stock-pc");
            var $app = $obj.parents("tr").find(".stock-app");
            var $wap = $obj.parents("tr").find(".stock-wap");
            var $wei = $obj.parents("tr").find(".stock-weishang");
            var type = $obj.parents("tr").find(".stock-type").html();
            if(type.trim() == '真实'){
                var num = $public.siblings('input[type=hidden]').val();
                $public.html(num);
                $public.siblings('input[type=text]').hide();
                $pc.html("—");
                $app.html("—");
                $wap.html("—");
                $wei.html("—");
            }
            if(type.trim() == '虚拟统一'){
                var num = $public.siblings('input[type=text]').val() == 0 ? 0 : $public.siblings('input[type=text]').val();
                $public.show().html(num);
                $public.siblings('input[type=text]').hide();
                $pc.html("—");
                $app.html("—");
                $wap.html("—");
                $wei.html("—");
            }
            if(type.trim() == '虚拟分端'){
                $public.html("—");
                var txtPc = $pc.siblings('input[type=text]').val() == 0 ? 0 : $pc.siblings('input[type=text]').val();
                var txtApp = $app.siblings('input[type=text]').val() == 0 ? 0 : $app.siblings('input[type=text]').val();
                var txtWap = $wap.siblings('input[type=text]').val() == 0 ? 0 : $wap.siblings('input[type=text]').val();
                var txtWei = $wei.siblings('input[type=text]').val() == 0 ? 0 : $wei.siblings('input[type=text]').val();
                $pc.show().html(txtPc);
                $pc.siblings('input[type=text]').hide();
                $app.show().html(txtApp);
                $app.siblings('input[type=text]').hide();
                $wap.show().html(txtWap);
                $wap.siblings('input[type=text]').hide();
                $wei.show().html(txtWei);
                $wei.siblings('input[type=text]').hide();
            }
            if(ajax){
                var id  = $obj.parents("tr").find('.id').html();
//                var type  = $obj.parents("tr").find('.stock-type').html();
                var type  = $obj.parents("tr").find('.stock-select').val();
                var public = parseInt($obj.parents("tr").find('.stock-puplic').html());
                var pc = parseInt($obj.parents("tr").find('.stock-pc').html());
                var app = parseInt($obj.parents("tr").find('.stock-app').html());
                var wap = parseInt($obj.parents("tr").find('.stock-wap').html());
                var wei = parseInt($obj.parents("tr").find('.stock-weishang').html());
                public ? "" : public = 0;
                pc ? "" : pc = 0;
                app ? "" : app = 0;
                wap ? "" : wap = 0;
                wei ? "" : wei = 0;

                $.ajax({
                    type: 'post',
                    url: '/stock/setStock',
                    data: {
                            id : {0:id},
                            type : {0:type},
                            public : {0:public},
                            pc : {0:pc},
                            app : {0:app},
                            wap : {0:wap},
                            wei : {0:wei}
                    },
                    cache: false,
                    dataType: 'json',
                    success: function (msg) {
                        if (msg.status == 'success') {
                            layer_required(msg.info);
//                            window.location.reload();
                            return;
                        } else {
                            layer_required(msg.info);
                            return;
                        }
                    }
                })
            }
        }

        function ajaxAll(){
            var $tr = $table.find("tbody tr");
            var trLength = $tr.length;
            var obj = {
                id:{},
                type:{},
                public:{},
                pc:{},
                app:{},
                wap:{},
                wei:{},
            };
            $tr.each(function(i,item){
                var public = parseInt($(item).find(".stock-puplic").html());
                var pc = parseInt($(item).find(".stock-pc").html());
                var app = parseInt($(item).find(".stock-app").html());
                var wap = parseInt($(item).find(".stock-wap").html());
                var wei = parseInt($(item).find(".stock-wap").html());
                public ? "" : public = 0;
                pc ? "" : pc = 0;
                app ? "" : app = 0;
                wap ? "" : wap = 0;
                wei ? "" : wei = 0;
                obj.id[i] = $(item).find(".id").html();
                obj.type[i] = $(item).find('.stock-select').val();;
                obj.public[i] = public;
                obj.pc[i] = pc;
                obj.app[i] = app;
                obj.wap[i] = wap;
                obj.wei[i] = wap;
            });

            $.ajax({
                type: 'post',
                url: '/stock/setStock',
                data: obj,
                cache: false,
                dataType: 'json',
                success: function (msg) {
                    if (msg.status == 'success') {
                        layer_required(msg.info);
                        window.location.reload();
                        return;
                    } else {
                        layer_required(msg.info);
                        return;
                    }
                }
            })
        }
    });
</script>
{% endblock %}