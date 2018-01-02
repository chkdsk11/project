{% extends "layout.volt" %}

{% block content %}
<style>
    .form-group {
        margin-top:10px;
    }
    #sku_id {
        width: 600px;
        height: 260px;
    }
</style>
    <div class="page-header">
        {% if act is not defined %}
        <h1>商品管理</h1>
        {% else %}
            <h1>编辑商品广告模板</h1>
        {% endif %}
    </div>
    <div class="row">
        <div class="col-xs-12">
            <!-- PAGE CONTENT BEGINS -->
            <form id="form" class="form-horizontal" action="/spu/updateCache" method="post">


                <div class="form-group">
                    <label class="col-sm-3 control-label no-padding-right">商品ID</label>
                    <div class="col-sm-9">
                        <div class="pos-rel">
                            <textarea id="sku_id" placeholder="多个商品ID用英文逗号隔开" name="goodsIds"></textarea>
                        </div>
                    </div>
                </div>
                <div class="col-md-offset-3 col-md-9">
                    <button class="btn btn-info" type="button" id="save_spu_btn">更新</button>
                </div>
            </form>

        </div><!-- /.col -->
    </div>
{% endblock %}

{% block footer %}
    <script>
        $('#save_spu_btn').click(function(){
            var goodsIds = $('#sku_id').val();
            var act = /[^0-9,]$/.test(goodsIds);
            if(act){
                layer_required('请填写正确的数据');
                return;
            }
            var form = $('#form');
            var url = form.attr('action');
            var data = {};
            data = form.serializeArray();
            $.ajax({
                type: 'post',
                url: url,
                data: data,
                cache: false,
                dataType:'json',
                success: function(msg){
                    layer_required(msg.info);
                    return;
                }
            });

        });
    </script>
{% endblock %}