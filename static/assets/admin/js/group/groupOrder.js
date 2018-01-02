$(function(){
    $('#startTime,#endTime').datetimepicker({step: 10});

    //点击拼团详情获取该团信息
    $(".groupDetail").click(function(){
        var gf_id = $(this).attr('data-total');
        $.ajax({
            type: 'post',
            url: '/grouporder/groupDetail',
            data: {'gf_id':gf_id},
            cache: false,
            dataType:'json',
            success: function(msg){
                if(msg.status == 'success'){
                    if(msg.data.gfa_type == 1){
                        $(".group-type").text('拼团抽奖');
                    }else{
                        $(".group-type").text('普通拼团');
                    }
                    $(".group-status").text(msg.data.status);
                    $(".group-joinnum").text(msg.data.join_num + '/' + msg.data.gfa_user_count);
                    $(".group-starttime").text(msg.data.gf_start_time);
                    $(".group-endtime").text(msg.data.gf_end_time);
                    var len = msg.data.list.length;
                    var tableVal = '';
                    $(".group-detail tbody").empty();
                    if (len > 0) {
                        $.each(msg.data.list,function(k,v) {
                            tableVal += '<tr>'
                            tableVal += '<td width="25%">'
                            if(v.is_head == 1){
                                tableVal += '开团人';
                            }else{
                                tableVal += '参团人';
                            }
                            tableVal += '</td>'
                            tableVal += '<td width="25%">' + v.username;
                            if (v.gfa_type == 1 && v.gfa_is_draw == 1 && v.is_win == 1) {
                                tableVal += '<strong class="red">(中奖)</strong>';
                            }
                            tableVal += '</td>';
                            tableVal += '<td width="25%">订单号</td>';
                            tableVal += '<td width="25%"><a href="/grouporder/orderdetail?orderSn=' + v.order_sn + '">' + v.order_sn + '</td>';
                            tableVal += '</tr>';
                        });
                    } else {
                        tableVal += '<tr><td>该团尚未开启</td></tr>';
                    }
                    $(".group-detail tbody").append(tableVal);
                    showPop();
                }else{
                    layer_required(msg.info);
                }
            }
        });
    });

    //显示详情弹框
    function showPop(){
        var $popFrame = $(".pop-frame-content");
        $popFrame.show();
        $("body").css("overflow","hidden");
        $popFrame.css("left",($(window).width()-$popFrame.outerWidth())/2);
        $popFrame.css("top",($(window).height()-$popFrame.outerHeight())/2+$(window).scrollTop());
    }

    //查询订单
    $("#orderListAction").click(function(){
        $("#my_form").attr("action",'/grouporder/orderlist');
        return true;
    });

    //拼团订单导出
    $("#exportGroupAction").click(function () {
        if($("input[name^='export_title']:checked").length>0){
            $("#my_form").attr("action",'/grouporder/orderexcel');
            return true;
        }else{
            layer_required("请选择要导出的字段！");
            return false;
        }
    });
})