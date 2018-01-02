{% extends "layout.volt" %}

{% block content %}
<style>
    /*弹窗*/
    .popup {
        position: fixed;
        z-index: 999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow-x: hidden;
        overflow-y: auto;
        background-color: rgba(0,0,0,0.7);

    }
    .popup .popup-content {
        position: absolute;
        left: 50%;
        top: 50%;
        width: 660px ;
        transform: translateY(-50%);
        width:500px;
        padding: 20px;
        text-align: center;
        position: relative;
        border-radius: .5rem;
        background-color: #ffffff;
        margin-left: -260px;

    }
    .popup-content .pro-title {
        position: relative;
        width: 100%;
        height: 40px;
        line-height: 40px;
        text-align: center;
        color: #333333;
        font-size: 16px;
        border-bottom: #cccccc solid 1px;
        margin-bottom: 20px;
        text-align: left;

    }
    .popup-content .pro-title a {
        display: block;
        position: absolute;
        right: 0;
        top: 0;
        height: 40px;
        width: 40px;
        color: #333333;
        font-size: 24px;
        text-align: center;
    }

    .popup-content .title {
        position: relative;
        width: 100%;
        height: 40px;
        line-height: 40px;
        text-align: center;
        color: #333333;
        font-size: 16px;
        border-bottom: #cccccc solid 1px;

    }
    .pro-title-info em{
        font-style: normal;
        color: red;

    }
    .div-list {
        text-align: left;
        margin-top: 10px;
    }
    .div-list label {
        width: 150px;
        text-align: left;
    }
    .div-list input {
        width: 250px;
    }
    .popup-content .title a {
        display: block;
        position: absolute;
        right: 0;
        top: 0;
        height: 40px;
        width: 40px;
        color: #333333;
        font-size: 24px;
        text-align: center;
    }
    .popup-content .stock-radio {
        text-align: left;
    }
    .popup-content .stock-checkbox {
        margin-left: 30px ;
    }
    .popup-content .stock-price {
        width: 80px;
    }

    .popup-content button {
        margin: 10px 0;
    }
</style>
<div class="page-content">
    <div class="row">
        <div class="col-xs-12">
            <div class="row">
                <div class="col-xs-12">

                    <div class="form-group clearfix">
                        <div class="row">
                            <form action="/supplier/list" method="get" id="export_button">
                                <div class="tools-box">
                                    <label class="clearfix">
                                        <span>店铺名称：</span><input type="text" style="width:200px;" name="name" value="{{ name }}" class="tools-txt" />
                                    </label>

                                    <label class="clearfix">
                                        <span>店铺ID：</span><input type="text" style="width:200px;" name="id" value="{{ id }}" class="tools-txt" />
                                    </label>

                                    <label class="clearfix">
                                        <span>退货收货人：</span><input type="text" style="width:200px;" name="user_name" value="{{ user_name }}" class="tools-txt" />
                                    </label>

                                    <label class="clearfix">
                                        <span>收货详细地址：</span><input type="text" style="width:200px;" name="address" value="{{ address }}" class="tools-txt" />
                                    </label>

                                    <label class="clearfix">
                                        <span>电话：</span><input type="text" style="width:200px;" name="phone" value="{{ phone }}" class="tools-txt" />
                                    </label>

                                    <label class="clearfix">
                                        <span>邮编：</span><input type="text" style="width:200px;" name="code" value="{{ code }}" class="tools-txt" />
                                    </label>

                                    <label class="clearfix" style="float: right;">
                                        <button class="btn btn-primary search"  type="submit">搜索</button>
                                    </label><label class="clearfix" style="float: right;">
                                        <button class="btn btn-primary add_shop"  type="button">添加店铺</button>
                                    </label>
                                </div>
                            </form>

                        </div>
                    </div>

                    <div>
                        <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                            <thead>
                            <tr>
                                <!--<th class="center">-->
                                    <!--ID-->
                                <!--</th>-->
                                <th class="center">
                                    店铺ID
                                </th>
                                <th class="center">店铺名称</th>
                                <th class="center">退货收货人</th>
                                <th class="center">收货详细地址</th>
                                <th class="center">邮编</th>
                                <th class="center">电话</th>
                                <th class="center">固定电话</th>
                                <th class="center">
                                    操作
                                </th>
                            </tr>
                            </thead>
                            <tbody id="productRuleList">

                            <!-- 遍历商品信息 -->
                            {% if list is defined %}
                            {% if list != null %}
                                {% for v in list %}
                                <tr>
                                    <td class="center">
                                        {{ v['id'] }}
                                    </td>
                                    <!--<td class="center">-->
                                        <!--{{ v['shop_id'] }}-->
                                    <!--</td>-->
                                    <td class="center">
                                        {{ v['name'] }}
                                    </td>
                                    <td class="center">
                                        {{ v['user_name'] }}
                                    </td>
                                    <td class="center">
                                        {{ v['address'] }}
                                    </td>
                                    <td class="center">
                                        {{ v['code'] }}
                                    </td>
                                    <td class="center">
                                        {{ v['phone'] }}
                                    </td>
                                    <td class="center">
                                        {{ v['telephone'] }}
                                    </td>
                                    <td class="center">
                                        <button type="button" data-id="{{ v['id'] }}" class="supplier_edit"> 编辑</button>
                                    </td>
                                </tr>
                                {% endfor %}
                            {% else %}
                                <tr>
                                    <td class="center" colspan="8">
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
    <div class="popup hide" id="popup_add_product">
        <div class="popup-content">
            <form id="form" action="/supplier/setSupplier">
                <div class="pro-title">
                    <span class="pro-title-info"></span>
                    <a id="close_add_product" href="javascript:;">&times;</a>
                </div>
                <div class="div-list">
                    <label>退货收货人：</label><input type="text" name="user_name"  id="user_name" />
                </div>
                <div class="div-list">
                    <label>收货详细地址：</label><input type="text" name="address"  id="address" />
                </div>
                <div class="div-list">
                    <label>邮编：</label><input type="text" name="code"  id="code" /><br/>
                </div>
                <div class="div-list">
                    <label>移动电话：</label><input type="text" name="phone"  id="phone" />
                </div>
                <div class="div-list">
                    <label>固定电话：</label><input type="text" name="telephone"  id="telephone" />
                </div >
                <br/>
                <input type="hidden" name="id" id="id" value="">
                <button id="supplier_edit_save" type="button">确定</button>
            </form>
        </div>
    </div>
    <div class="popup hide" id="popup_add_shop">
        <div class="popup-content">
            <form id="add_form" action="/supplier/addSupplier">
                <div class="pro-title">
                    <span class="pro-title-info"></span>
                    <a id="close_add_shop" href="javascript:;">&times;</a>
                </div>
                <div class="div-list">
                    <label>店铺名称：</label><input type="text" name="name"  id="add_name" />
                </div>
                <div class="div-list">
                    <label>退货收货人：</label><input type="text" name="user_name"  id="add_user_name" />
                </div>
                <div class="div-list">
                    <label>收货详细地址：</label><input type="text" name="address"  id="add_address" />
                </div>
                <div class="div-list">
                    <label>邮编：</label><input type="text" name="code"  id="add_code" /><br/>
                </div>
                <div class="div-list">
                    <label>移动电话：</label><input type="text" name="phone"  id="add_phone" />
                </div>
                <div class="div-list">
                    <label>固定电话：</label><input type="text" name="telephone"  id="add_telephone" />
                </div >
                <br/>
                <button id="supplier_add_save" type="button">确定</button>
            </form>
        </div>
    </div>
</div>
{{ page }}
{% if list['page'] is defined %}{{ list['page'] }}{% endif %}
{% endblock %}

{% block footer %}
<script>
    $(document).ready(function() {
        //编辑
        $(document).on('click', '#supplier_edit_save', function () {
            if (!regMobliePhone.test($('#phone').val())) {
                layer_required('请填写正确的手机号！');
                return;
            }
//            if (!regPhone.test($('#telephone').val())) {
//                layer_required('请填写正确的固定电话号！');
//                return;
//            }
            if (!regNumber.test($('#code').val())) {
                layer_required('请填写正确的邮编！');
                return;
            }
            var form = $("#form");
            var url = form.attr('action');
            var data = {};
            data = form.serializeArray();
            $.ajax({
                type: 'post',
                url: url,
                data: data,
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
        });

        $(document).on('click', '#supplier_add_save', function () {
            var _name = $('#add_name').val().length;
            if (_name < 1) {layer_required('请添加店铺名称！')}
            if (!regMobliePhone.test($('#add_phone').val())) {
                layer_required('请填写正确的手机号！');
                return;
            }
//            if (!regPhone.test($('#add_telephone').val())) {
//                layer_required('请填写正确的固定电话号！');
//                return;
//            }
            if (!regNumber.test($('#add_code').val())) {
                layer_required('请填写正确的邮编！');
                return;
            }
            var form = $("#add_form");
            var url = form.attr('action');
            var data = {};
            data = form.serializeArray();
            $.ajax({
                type: 'post',
                url: url,
                data: data,
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
        });

        $(document).on('click', '.supplier_edit', function () {
            var id = $(this).attr('data-id');
            $.ajax({
                type: 'get',
                url: '/supplier/getSupplier?id=' + id,
                cache: false,
                dataType: 'json',
                success: function (msg) {
                    if(msg){
                        $('.pro-title .pro-title-info').html('编辑<em> "' + msg.name + '"</em>');
                        $('#id').val(msg.id);
                        $('#user_name').val(msg.user_name);
                        $('#address').val(msg.address);
                        $('#code').val(msg.code);
                        $('#phone').val(msg.phone);
                        $('#telephone').val(msg.telephone);
                        $('#popup_add_product').removeClass('hide');
                    }else{
                        layer_required('参数错误');
                        return;
                    }
                }
            });
        });

        $(document).on('click', '.add_shop', function () {
            $('.pro-title .pro-title-info').html('添加店铺');
            $('#popup_add_shop').removeClass('hide');
        });

        $(document).on('click','#close_add_product',function(){

            $('#popup_add_product').addClass('hide');
        });
        $(document).on('click','#close_add_shop',function(){

            $('#popup_add_shop').addClass('hide');
        });
    })
</script>
{% endblock %}