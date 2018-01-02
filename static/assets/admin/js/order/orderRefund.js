$(function(){
	//刷新全部不选
	// $("thead .checkAll").prop("checked",false);
	// $("tbody .option").each(function(i,item){
	// 	$(item).prop("checked",false);
	// });
	// $(".refundNum").val(0);
	//单项选择
	$(document).on("click","tbody .option",function(){
		var $text = $(this).parents("tr").find('input[name="num[]"]');
		var inputAllState = true;
		$("tbody .option").each(function(i,item){
			!$(item).prop("checked") ? inputAllState = false:"";
		});
		if($(this).prop("checked")){
			$text.removeClass("hide");
			$text.val($text.attr("data-num"));
		}else{
			$text.addClass("hide");
			$text.val("0");
		}
		if(inputAllState){
			$("thead .checkAll").prop("checked",true);
		}else{
			$("thead .checkAll").prop("checked",false);
		}
	})
	
	//全选或全不选
	$("thead .checkAll").on("click",function(){
		if($(this).prop("checked")){
			$("tbody .option").each(function(i,item){
				var $text = $(item).parents("tr").find('input[name="num[]"]');
				$text.val($text.attr("data-num"));
			});
			$("tbody .option").prop("checked",true);
			$('tbody input[name="num[]"]').removeClass("hide");
		}else{
			$("tbody .option").each(function(i,item){
				var $text = $(item).parents("tr").find('input[name="num[]"]');
				$text.val("0");
			});
			$("tbody .option").prop("checked",false);
			$('tbody input[name="num[]"]').addClass("hide");
		}
	});
	
	$("#explain").on("keyup",function(){
		if(strlen($(this).val())>200){
			layer_required('原因说明不能超过200个字符');
			return false;
		}
	});
	
	//提交表单验证
	$(".confirmRefund").on("click", function(){
		$isSubmit = 1;
		//订单号
		var orderSn = $("#orderSn").val();
		if (orderSn == '') {
			layer_required('订单不存在，请重新进入申请页面');
			$isSubmit = 0;
			return false;
		}
		var $checked = $("tbody .option:checked");
		//商品选择数量
		if ($checked.length == 0) {
			layer_required('请选择商品');
			$isSubmit = 0;
			return false;
		}
		//选择的商品数量校验
		$checked.each(function(i,item){
			var $text = $(item).parents("tr").find('input[name="num[]"]');
			var num = $text.val();
			var oldNum = $text.attr("data-num");
			var name = $text.attr("data-name");
			if (!!isNaN(num)) {
				layer_required('请正确输入 ' + name + ' 的退货数量');
				$isSubmit = 0;
				return false;
			} else if (parseInt(num) < 0) {
				layer_required(name + ' 的退货数量小于0');
				$isSubmit = 0;
				return false;
			} else if (num > oldNum) {
				layer_required(name + ' 的退货数量大于可退数量');
				$isSubmit = 0;
				return false;
			}
		});
		//退货原因
		var reason = $("#reason").val();
		if (reason == 0) {
			layer_required('请选择退货原因');
			$isSubmit = 0;
			return false;
		}
		//退货原因说明
		var explain = $("#explain").val();
		if (explain == '') {
			layer_required('请填写原因说明');
			$isSubmit = 0;
			return false;
		} else if (strlen(explain) > 200) {
			layer_required('原因说明不能超过200个字符');
			$isSubmit = 0;
			return false;
		}
		if ($isSubmit == 0) {
			return false;
		}
		//ajax提交
        ajaxSubmit('refundForm');
	});

	// 计算字符串长度
	function strlen(str){
		var len = 0;
		for (var i=0; i<str.length; i++) {
			var c = str.charCodeAt(i);
			//单字节加1
			if ((c >= 0x0001 && c <= 0x007e) || (0xff60<=c && c<=0xff9f)) {
				len++;
			} else {
				len+=2;
			}
		}
		return len;
	}
});
