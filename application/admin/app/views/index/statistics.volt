{% extends "layout.volt" %}

{% block path %}
<!--放页面路径如：-->

{% endblock %}

{% block content %}

<!--放页面内容-->
<div class="page-content">


	<div class="page-header">
		<h1>
			统计
			<div class="pagedesc" style="float: right;margin-right: 40px;height: 60px;">
                <span style="float: left;margin-right: 40px;line-height: 30px;">数据缓存一小时，也可手动刷新缓存</span>
                <input type="button" class="stdbtn" value="刷新" onclick="clearCatch()"/>
            </div>
			{#<small>
				<i class="ace-icon fa fa-angle-double-right"></i>
				overview &amp; stats
			</small>#}
		</h1>

	</div><!-- /.page-header -->

    <div id="contentwrapper" style="width: 100%;">
            <table cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-hover dataTable no-footer">
                <tr>
                    <th colspan="4"><h3>综合信息</h3></th>
                </tr>
                <tr>
                    <td width="200px">会员数量：</td>
                    <td colspan="3" style="text-align: left;" id="totalCount">{$info['user']['totalCount']}</td>
                </tr>
                <tr>
                    <td>已完成订单：</td>
                    <td style="text-align: left;" id="orderCount">{$info['orderCount']}</td><!--<td>转换率：</td><td></td>-->
                </tr>
            </table>
            <table cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-hover dataTable no-footer" id="userCountList">
                <tr>
                    <th colspan="7"><h3>会员信息</h3></th>
                </tr>
                <tr class="trcolor">
                    <td>端口</td>
                    <td>会员数量</td>
                    <td>昨日新增会员</td>
                    <td>本日新增会员</td>
                    <td>本月新增会员</td>
                    <td>本季新增会员</td>
                    <td>本年新增会员</td>
                </tr>
            </table>
            <table cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-hover dataTable no-footer" id="goodsCountList">
                <tr>
                    <th colspan="7"><h3>商品信息</h3></th>
                </tr>
                <tr class="trcolor">
                    <td>商品类型</td>
                    <td>商品总数量</td>
                    <td>缺货商品数量</td>
                    <td>昨日动销数量</td>
                    <td>本日动销数量</td>
                    <td>本月动销数量</td>
                </tr>
            </table>
            <table cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-hover dataTable no-footer" id="orderCountList">

            </table>
            <table cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-hover dataTable no-footer" id="kjOrderCountList">

            </table>
        </div>
    </div><!-- /.page-content -->
{% endblock %}

{% block footer %}
<!--放尾部需加载的样式和js，禁止出现显示内容-->
<!-- inline scripts related to this page -->
<script type="text/javascript">
        $(function () {
            var range = 50;             //距下边界长度/单位px
            var maxnum = 2;            //设置加载最多次数
            var num = 1;
            var totalheight = 0;
            var main = $("#contentwrapper");
            main.css('min-height',$(window).height()-200+'px');
            function showTopTable() {
                $.ajax({
                    url: "/",
                    type: "POST",
                    dataType: "json",
                    data: {type: 'top'}
                })
                        .done(function (data) {
                            if (data) {
                                var info = data.info;
                                var goodsCountList = data.goodsCountList;
                                var userCountList = data.userCountList;
                                $('#totalCount').html(info.totalCount);
                                $('#orderCount').html(info.orderCount);
                                var userCountListTrs = '';
                                $.each(userCountList, function (index, value) {
                                    userCountListTrs += '<tr>' +
                                            '<td>' + value.channel_name + '</td>' +
                                            '<td>' + value.count[0] + '</td>' +
                                            '<td>' + value.countYesterday + '</td>' +
                                            '<td>' + value.countToday + '</td>' +
                                            '<td>' + value.countMonth + '</td>' +
                                            '<td>' + value.countQuarter + '</td>' +
                                            '<td>' + value.countYear + '</td>' +
                                            '</tr>';
                                });
                                $('#userCountList').append(userCountListTrs);

                                var goodsCountListTrs = '';
                                $.each(goodsCountList, function (index, value) {
                                    goodsCountListTrs += '<tr>' +
                                            '<td>' + value.type + '</td>' +
                                            '<td>' + value.total[0] + '</td>' +
                                            '<td>' + value.stockOutCount + '</td>' +
                                            '<td>' + value.yesterdaySaleCount + '</td>' +
                                            '<td>' + value.todaySaleCount + '</td>' +
                                            '<td>' + value.monthSaleCount + '</td>' +
                                            '</tr>';
                                });
                                $('#goodsCountList').append(goodsCountListTrs);
                            }
                        })
            }

            function showDownTable() {
                $.ajax({
                    url: "/",
                    type: "POST",
                    dataType: "json",
                    data: {type: 'down'}
                })
                        .done(function (data) {

                            var orderCountList = data.orderCountList;
                            var kjOrderCountList = data.kjOrderCountList;

                            var orderCountListTrs = '' +
                                    '<tr><th colspan="17"><h3>国内—订单信息</h3></th></tr>' +
                                    '<tr class="trcolor">' +
                                    '<td></td>' +
                                    '<td colspan="2">总订单</td>' +
                                    '<td colspan="2">待付款订单</td>' +
                                    '<td colspan="2">待发货订单</td>' +
                                    '<td colspan="2">待收货订单</td>' +
                                    '<td colspan="2">待评价订单</td>' +
                                    '<td colspan="2">已完成订单</td>' +
                                    '<td colspan="2">退款/售后订单</td>' +
                                    '<td colspan="2">取消订单</td>' +
                                    '</tr>' +
                                    '<tr class="trcolor">' +
                                    '<td>端口</td>' +
                                    '<td>数量</td>' +
                                    '<td>金额</td>' +
                                    '<td>数量</td>' +
                                    '<td>金额</td>' +
                                    '<td>数量</td>' +
                                    '<td>金额</td>' +
                                    '<td>数量</td>' +
                                    '<td>金额</td>' +
                                    '<td>数量</td>' +
                                    '<td>金额</td>' +
                                    '<td>数量</td>' +
                                    '<td>金额</td>' +
                                    '<td>数量</td>' +
                                    '<td>金额</td>' +
                                    '<td>数量</td>' +
                                    '<td>金额</td>' +
                                    '</tr>';
                            $.each(orderCountList, function (index, value) {
                                orderCountListTrs += '<tr>' +
                                        '<td>' + value.channel_name + '</td>' +
                                        '<td>' + value.count + '</td>' +
                                        '<td>' + value.total + '</td>' +
                                        '<td>' + value.payingCount + '</td>' +
                                        '<td>' + value.payingTotal + '</td>' +
                                        '<td>' + value.shippingCount + '</td>' +
                                        '<td>' + value.shippingTotal + '</td>' +
                                        '<td>' + value.shippedCount + '</td>' +
                                        '<td>' + value.shippedTotal + '</td>' +
                                        '<td>' + value.evaluatingCount + '</td>' +
                                        '<td>' + value.evaluatingTotal + '</td>' +
                                        '<td>' + value.finishedCount + '</td>' +
                                        '<td>' + value.finishedTotal + '</td>' +
                                        '<td>' + value.refundCount + '</td>' +
                                        '<td>' + value.refundTotal + '</td>' +
                                        '<td>' + value.canceledCount + '</td>' +
                                        '<td>' + value.canceledTotal + '</td>' +
                                        '</tr>';
                            });
                            $('#orderCountList').append(orderCountListTrs);
                            var kjOrderCountListTrs = ''+
                            '<tr><th colspan="17"><h3>海外购—订单信息</h3></th></tr>' +
                            '<tr class="trcolor">' +
                            '<td></td>' +
                            '<td colspan="2">总订单</td>' +
                            '<td colspan="2">待付款订单</td>' +
                            '<td colspan="2">待发货订单</td>' +
                            '<td colspan="2">待收货订单</td>' +
                            '<td colspan="2">待评价订单</td>' +
                            '<td colspan="2">已完成订单</td>' +
                            '<td colspan="2">退款/售后订单</td>' +
                            '<td colspan="2">取消订单</td>' +
                            '</tr>' +
                            '<tr class="trcolor">' +
                            '<td>端口</td>' +
                            '<td>数量</td>' +
                            '<td>金额</td>' +
                            '<td>数量</td>' +
                            '<td>金额</td>' +
                            '<td>数量</td>' +
                            '<td>金额</td>' +
                            '<td>数量</td>' +
                            '<td>金额</td>' +
                            '<td>数量</td>' +
                            '<td>金额</td>' +
                            '<td>数量</td>' +
                            '<td>金额</td>' +
                            '<td>数量</td>' +
                            '<td>金额</td>' +
                            '<td>数量</td>' +
                            '<td>金额</td>' +
                            '</tr>';
                            $.each(kjOrderCountList, function (index, value) {
                                kjOrderCountListTrs += '<tr>' +
                                        '<td>' + value.channel_name + '</td>' +
                                        '<td>' + value.count + '</td>' +
                                        '<td>' + value.total + '</td>' +
                                        '<td>' + value.payingCount + '</td>' +
                                        '<td>' + value.payingTotal + '</td>' +
                                        '<td>' + value.shippingCount + '</td>' +
                                        '<td>' + value.shippingTotal + '</td>' +
                                        '<td>' + value.shippedCount + '</td>' +
                                        '<td>' + value.shippedTotal + '</td>' +
                                        '<td>' + value.evaluatingCount + '</td>' +
                                        '<td>' + value.evaluatingTotal + '</td>' +
                                        '<td>' + value.finishedCount + '</td>' +
                                        '<td>' + value.finishedTotal + '</td>' +
                                        '<td>' + value.refundCount + '</td>' +
                                        '<td>' + value.refundTotal + '</td>' +
                                        '<td>' + value.canceledCount + '</td>' +
                                        '<td>' + value.canceledTotal + '</td>' +
                                        '</tr>';
                            });
                            $('#kjOrderCountList').append(kjOrderCountListTrs);
                        });
            }

            showTopTable();
//			showDownTable();

            $(window).scroll(function () {
                var srollPos = $(window).scrollTop();    //滚动条距顶部距离(页面超出窗口的高度)
                totalheight = parseFloat($(window).height()) + parseFloat(srollPos);
                if(($(document).height()-range) <= totalheight  && num != maxnum) {
                    showDownTable();
                    num++;
                }
            });
        });
        function clearCatch() {
            $.ajax({
                url: "/",
                type: "POST",
                dataType: "json",
                data: {type: 'clearCatch'}
            })
                    .done(function (data) {
                        if (data.error == 0) {
                            alert('更新缓存成功!');
                            setTimeout(function () {
                                window.location.reload()
                            }, 1000);
                            ;
                        }
                    })
        }
</script>
{% endblock %}

