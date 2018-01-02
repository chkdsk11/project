{% extends "layout.volt" %}

{% block content %}
<style>
    .productRule{
        display: inline-block;
        float: left;
        margin-left: 20px;
        width: 60%;
    }
    .editProductRule {
        display: inline-block;
        float: right;
        margin-right:20px;
    }
</style>
<div class="page-content">
    <div class="row">
        <div class="col-xs-12">
            <div class="row">
                <div class="col-xs-12">

                    <div class="form-group clearfix">
                        <div class="row">
                            <form action="/customes/list" method="get">
                                <div class="tools-box">

                                    <label class="clearfix">
                                       <span>反馈内容：</span> <input type="text" id="msg_content" placeholder="反馈内容" name="msg_content" value="{{ msg_content }}" class="tools-txt" />
                                    </label>

                                    <label class="clearfix">
                                       <span>客服编号：</span> <input type="text" id="serv_nickname" placeholder="客服编号" name="serv_nickname" value="{{ serv_nickname }}" class="tools-txt" />
                                    </label>

                                    <label class="clearfix">
                                       <span>审核状态：</span>
                                        <select name="msg_status" id="msg_status" class="tools-txt">
                                            <option value="">请选择</option>
                                            <option value="0">未审核</option>
                                            <option value="1">审核通过</option>
                                            <option value="2">审核不通过</option>
                                        </select>
                                    </label>


                                    <label class="clearfix">
                                        <button class="btn btn-primary"  type="submit">搜索</button>
                                    </label>
                                    <!--
                                    <label class="clearfix" style="float: right;margin-right: 30px;">
                                        <a class="btn btn-primary" href="/sku/adadd">新建商品广告模板</a>
                                    </label>
                                    -->
                                </div>
                            </form>

                        </div>
                    </div>
                    <div class="form-group clearfix">
                        <div class="row">
                                <div class="tools-box">

                                    <label class="clearfix">
                                        <input type="checkbox" value="0" name="isCancel" id="isCancel" >全选
                                    </label>

                                    <label class="clearfix">
                                        <button class="btn btn-primary cancel_fb" data-id="1" type="button">审核通过</button>
                                    </label>
                                    <label class="clearfix">
                                        <button class="btn btn-primary cancel_fb" data-id="2" type="button">审核不通过</button>
                                    </label>
                                    <!--
                                    <label class="clearfix" style="float: right;margin-right: 30px;">
                                        <a class="btn btn-primary" href="/sku/adadd">新建商品广告模板</a>
                                    </label>
                                    -->
                                </div>

                        </div>
                    </div>
                    <div>
                        <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                            <thead>
                            <tr>
                                <th class="center">编号</th>
                                <th class="center">反馈内容</th>
                                <th class="center">反馈时间</th>
                                <th class="center">审核状态</th>
                                <th class="center">审核时间</th>
                                <th class="center">客服ID</th>
                                <th class="center">客服编号</th>
                                <th class="center">操作</th>
                            </tr>
                            </thead>
                            <tbody id="cltData">

                            <!-- 遍历商品信息 -->
                            {% if cltData['list'] is defined %}
                            {% if cltData['list'] > 0 %}
                                {% for v in cltData['list'] %}
                                <tr>
                                    <td class="center">
                                        {% if (v['msg_status']) == '未审核' %}
                                        <input type="checkbox" data-id="{{ v['msg_id'] }}" name="cancel_id[]" class="cancel_id" >
                                        {% endif %}
                                        {{ loop.index }}
                                    </td>
                                    <td class="center">
                                        {{ v['msg_content'] }}
                                    </td>
                                    <td class="center">
                                        {{ date('Y-m-d H:i:s',v['msg_time']) }}
                                    </td>
                                    <td class="center">
                                        {{ v['msg_status'] }}
                                    </td>
                                    <td class="center">
                                        {% if (v['adult_time']) != 0 %}
                                        {{ date('Y-m-d H:i:s',v['adult_time']) }}
                                        {% endif %}
                                    </td>
                                    <td class="center">
                                        {{ v['serv_id'] }}
                                    </td>
                                    <td class="center">
                                        {{ v['serv_nickname'] }}
                                    </td>
                                    <td class="center">
                                        {% if (v['msg_status']) == '未审核' %}
                                        <label class="clearfix">
                                            <button class="btn btn-primary check_sh" data-id="{{ v['msg_id'] }}" data-sts="1" type="button">审核通过</button>
                                        </label>
                                        <label class="clearfix">
                                            <button class="btn btn-primary check_sh" data-id="{{ v['msg_id'] }}" data-sts="2" type="button">审核不通过</button>
                                        </label>
                                        {% else %}
                                        <label class="clearfix">
                                            已审核
                                        </label>
                                        {% endif %}
                                    </td>
                                </tr>
                                {% endfor %}
                            {% else %}
                                <tr>
                                    <td class="center" colspan="9">
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
{% if cltData['page'] is defined %}{{ cltData['page'] }}{% endif %}
{% endblock %}

{% block footer %}
<script>
    $(function () {
        var _phar = "{{ msg_status }}";
        var _sel = $('#msg_status option[value="'+_phar+'"]').attr('selected', 'selected');
        
        $('#isCancel').on('change', function (e) {
            var _isCheck = $(this).prop('checked');

            if (_isCheck)
            {
                $('.cancel_id').prop('checked', true);
            }else{
                $('.cancel_id').prop('checked', false);
            }
        });

        $('.cancel_id').on('change', function (e) {
            var _len = $('.cancel_id:checked').size();
            if (_len != 15)
            {
                $('#isCancel').prop('checked', false);
            }else{
                $('#isCancel').prop('checked', true);
            }
        })
        
        $('.cancel_fb').on('click', function (e) {
            var _status = $(this).data('id');
            var _html = $(this).html();
            var _len = $('.cancel_id:checked').size();
            if (_len < 1) {layer_error('请至少选择一个咨询!'); return false;}
            var _ids = [];
            $('.cancel_id:checked').each(function(i){
                var _id = $(this).data('id');
                _ids.push(_id);
            })
            layer_confirm('确定要对这些反馈进行'+_html+'操作吗?', '/customes/batchCancelClt', {'ids':_ids,'msg_status':_status})
        })

        $('.check_sh').on('click', function (e) {
            var _status = $(this).data('sts');
            var _ids = $(this).data('id');
            var _html = $(this).html();
            layer_confirm('确定要对这些反馈进行'+_html+'操作吗?', '/customes/batchCancelClt', {'ids':_ids,'msg_status':_status})
        })
    })

    $(document).keypress(function (e) {
        if( e.which == 13 ){
            return false;
        }
    });
</script>
{% endblock %}