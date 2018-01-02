/*$(".register_send").on("click", function () {
    var mid=$(this).data("mid");
    $.post("/coupon/postRegisterBonus",{mid:mid}, function (res) {
        if(res == 0){
            layer_required("修改状态失败");
        }
    })
})*/
$(".cancelCoupon").on("click", function () {
    var mid=$(this).data("mid");
    var request = location.search;
    layer_confirm('确定要取消这个优惠券吗?','/coupon/del',{mid :mid,request :request});
    //location.reload();
})