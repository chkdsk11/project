$(function(){
	//是否允许提交
	var isAllowSubmit = 1;
	var pcUrl = $("#serviceSn").attr("data-pcUrl");

	// 弹框事件
	var $popFrameshadow= $(".pop-frame-shadow");
	var $popVerify = $(".pop-verify");
	var $popRemark = $(".pop-remark");

	//审核服务单弹窗显示
	$(".checkOrder").click(function(){
		if (isAllowSubmit == 0) {
			return false;
		}
		var serviceSn = $(this).attr("data-id");
		if (!serviceSn) {
			layer_required('请选择服务单');
			return false;
		}
		$("#serviceSn").val(serviceSn);
		isAllowSubmit = 0;
		$.ajax({
			type: 'post',
			url: '/refund/refundAudit',
			data: {
				'serviceSn':serviceSn,
				'isAudit':0,
			},
			cache: false,
			dataType:'json',
			success: function(msg){
				if(msg.status == 'success'){
					var data = msg.data;
					iframVoluation(data, 1);
					popIfram($popVerify);
				}else{
					layer_required(msg.info);
				}
				isAllowSubmit = 1;
			}
		});
	});
	
	//收货弹窗显示
	$(".getGoods").click(function(){
		if (isAllowSubmit == 0) {
			return false;
		}
		var serviceSn = $(this).attr("data-id");
		if (!serviceSn) {
			layer_required('请选择服务单');
			return false;
		}
		$("#serviceSn").val(serviceSn);
		isAllowSubmit = 0;
		$.ajax({
			type: 'post',
			url: '/refund/receivingGoods',
			data: {
				'serviceSn':serviceSn,
				'isReceiving':0,
			},
			cache: false,
			dataType:'json',
			success: function(msg){
				if(msg.status == 'success'){
					var data = msg.data;
					iframVoluation(data, 3);
					popIfram($popVerify);
				}else{
					layer_required(msg.info);
				}
				isAllowSubmit = 1;
			}
		});
	});
	
	//退款弹窗显示
	$(".returnMoney").click(function(){
		if (isAllowSubmit == 0) {
			return false;
		}
		var serviceSn = $(this).attr("data-id");
		if (!serviceSn) {
			layer_required('请选择服务单');
			return false;
		}
		$("#serviceSn").val(serviceSn);
		isAllowSubmit = 0;
		$.ajax({
			type: 'post',
			url: '/refund/confirmRefund',
			data: {
				'serviceSn':serviceSn,
				'isConfirm':0,
			},
			cache: false,
			dataType:'json',
			success: function(msg){
				if(msg.status == 'success'){
					var data = msg.data;
					iframVoluation(data, 2);
					popIfram($popVerify);
				}else{
					layer_required(msg.info);
				}
				isAllowSubmit = 1;
			}
		});
	});
	
	//弹窗数据替换
	function iframVoluation(data, type) {
		$(".pop-frame-content .pop-frame").empty();
		$(".pop-frame-content .refundGoods tbody").empty();
		$(".pop-frame-content .reasonShow").empty();
		$(".pop-frame-content .pro-frame-img").empty();
		var popTag = '<h2>服务单信息</h2>';
		popTag += '<p>';
		popTag += '<span>服务单号：' + data.service_sn + '</span>';
		popTag += '<span>订单编号：' + data.order_sn + '</span>';
		popTag += '<span>订单状态：' + data.orderStatus + '</span>';
		popTag += '</p>';
		popTag += '<p>';
		popTag += '<span>付款方式：' + data.paymentName + '</span>';
		popTag += '<span>应退金额：' + data.refund_amount + '</span>';
		popTag += '</p>';
		popTag += '<p>用户电话：' + data.phone + '</p>';
		$(".pop-frame-content .pop-frame").append(popTag);
		var tbodyTag = '';
		if (data.products.length > 0) {
			$.each(data.products,function(k,v) {
				tbodyTag += '<tr>';
				tbodyTag += '<td>' + v.goods_id + '</td>';
				tbodyTag += '<td><a href="' + pcUrl + '/product/' + v.goods_id + '.html" target="_blank">';
				if (v.drug_type == 1) {
					tbodyTag += '<span class="icon-drug-red">Rx</span>';
				} else if (v.drug_type == 2) {
					tbodyTag += '<span class="icon-drug-red">OTC</span>';
				} else if (v.drug_type == 3) {
					tbodyTag += '<span class="icon-drug-green">OTC</span>';
				}
				tbodyTag += v.goods_name;
				tbodyTag += '</a></td>';
				if (v.name_id2 && v.name_id3) {
					tbodyTag += '<td>' + v.name_id2 + "，" + v.name_id3 + '</td>';
				} else if (v.name_id2 && !v.name_id3) {
					tbodyTag += '<td>' + v.name_id2 + '</td>';
				} else if (!v.name_id2 && v.name_id3) {
					tbodyTag += '<td>' + v.name_id3 + '</td>';
				} else {
					tbodyTag += '<td></td>';
				}
				tbodyTag += '<td>' + v.refundNum + '</td>';
				tbodyTag += '<td>' + v.goodsRefunds + '</td>';
				tbodyTag += '</tr>';
			});
		} else {
			tbodyTag += '<tr><td colspan="5">该订单没有商品数据</td></tr>';
		}
		$(".pop-frame-content .refundGoods tbody").append(tbodyTag);
		var reasonTag = '<p>退款原因：' + data.reason + '</p>';
		reasonTag += '<p>原因说明：' + data.explain + '</p>';
		$(".pop-frame-content .reasonShow").append(reasonTag);
		if (data.images != null && data.images.length > 0) {
			var imgTag = '<p>上传图片：</p>';
			imgTag += '<div id="checkPrescription" class="attachments">';
			$.each(data.images,function(k,v) {
				imgTag += '<a href="' + v + '" class="lightbox" rel="thumbnails">';
				imgTag += '<img src="' + v + '">';
				imgTag += '</a>';
			});
			imgTag += '</div>';
			$(".pop-frame-content .pro-frame-img").show();
			$(".pop-frame-content .pro-frame-img").append(imgTag);
			$(".pop-frame-content .operationAudit").css("margin-top","0px");
			$(".pop-frame-content .operationConfirm").css("margin-top","0px");
		} else {
			$(".pop-frame-content .pro-frame-img").hide();
			$(".pop-frame-content .operationAudit").css("margin-top","10px");
			$(".pop-frame-content .operationConfirm").css("margin-top","10px");
		}
		
		if (type == 1) {
			$("#verifyResult").empty();
			var option = '<option value="0">请选择</option>';
			if (data.orderStatus != '待发货') {
				option += '<option value="goods">退货退款</option>';
			}
			option += '<option value="money">仅退款</option>';
			option += '<option value="noPass">不通过审核</option>';
			$("#verifyResult").append(option);
			$(".pop-frame-content .operationAudit").show();
			$(".pop-frame-content .operationConfirm").hide();
			$(".pop-frame-content .receivingGoods").hide();
		} else if (type == 2) {
			var textVal = ''; 
			if (data.return_type == 0) {
				textVal= '货到付款取消';
			} else if (data.return_type == 1) {
				textVal= '仅退款';
			} else {
				textVal= '退款退货';
			}
			$(".moneyInfo").attr({"data-refundAmoun":data.refund_amount,"data-balancePrice":data.balance_price,"data-paymentName":data.paymentName});
			refundMoney(parseFloat(data.refund_amount),parseFloat(data.balance_price),data.paymentName,0);
			$(".pop-frame-content .operationConfirm .auditResult").html(textVal);
			$(".pop-frame-content .operationAudit").hide();
			$(".pop-frame-content .operationConfirm").show();
			$(".pop-frame-content .receivingGoods").hide();
		} else {
			$(".pop-frame-content .operationAudit").hide();
			$(".pop-frame-content .operationConfirm").hide();
			$(".pop-frame-content .receivingGoods").show();
		}
	}

	//退款金额分配
    function refundMoney(refundAmount,balancePrice,paymentName,inputVal) {
    	var balance_price = refundAmount >= balancePrice 
			? balancePrice : refundAmount;
		var pay_fee = refundAmount > balancePrice 
			? (Number(refundAmount - balancePrice)).toFixed(2) : (Number(0)).toFixed(2);
		var moneyInfo = '退款路径：';
		if (inputVal > 0) {
			if (inputVal > refundAmount) {
				$("#partAmount").val(refundAmount);
				layer_required('退款金额不能大于应退金额');
			} else {
				if (balance_price >= inputVal) {
					balance_price = (Number(inputVal)).toFixed(2);
					pay_fee = (Number(0)).toFixed(2);
				} else {
					pay_fee = (Number(inputVal - balance_price)).toFixed(2);
				}
			}
		}
		if (pay_fee >= 0 && paymentName != '余额支付') {
			moneyInfo += paymentName + '：￥' + pay_fee;
		}
		if (balance_price >= 0 && balancePrice > 0) {
			moneyInfo += ' 余额：￥' + balance_price;
		}
		$(".moneyInfo").empty();
		$(".moneyInfo").append(moneyInfo);
    }
	
	//收货
	$(".receivedGoods").click(function(){
		if (isAllowSubmit == 0) {
			return false;
		}
		var serviceSn = $("#serviceSn").val();
		if (!serviceSn) {
			layer_required('请选择服务单');
			return false;
		}
		isAllowSubmit = 0;
		$.ajax({
			type: 'post',
			url: '/refund/receivingGoods',
			data: {
				'serviceSn':serviceSn,
				'isReceiving':1,
			},
			cache: false,
			dataType:'json',
			success: function(msg){
				if(msg.status == 'success'){
					layer_required(msg.info);
					location.reload();
				}else{
					layer_required(msg.info);
					closePop($popVerify);
				}
				isAllowSubmit = 1;
			}
		});
	});
	
	//审核提交
	$(".submitAudit").click(function(){
		if (isAllowSubmit == 0) {
			return false;
		}
		var serviceSn = $("#serviceSn").val();
		if (!serviceSn) {
			layer_required('请选择服务单');
			return false;
		}
		var verifyResult = $("#verifyResult").val();
		if (verifyResult == 0) {
			layer_required('请选择审核结果');
			return false;
		}
		var auditRemark = $.trim($("#auditRemark").val());
		var len = $(".operationAudit .max-txt i").html();
		if (len > 200) {
			layer_required('备注内容不能超过200个字符');
			return false;
		}
		isAllowSubmit = 0;
		$.ajax({
			type: 'post',
			url: '/refund/refundAudit',
			data: {
				'serviceSn':serviceSn,
				'isAudit':1,
				'resultCode':verifyResult,
				'auditRemark':auditRemark,
			},
			cache: false,
			dataType:'json',
			success: function(msg){
				if(msg.status == 'success'){
					layer_required(msg.info);
					location.reload();
				}else{
					layer_required(msg.info);
					closePop($popVerify);
				}
				isAllowSubmit = 1;
			}
		});
	});

	//退款金额选择
	$("#amountSel").on("change",function(){
		var option = $(this).find("option:selected").html().trim();
		if(option == "部分退款"){
			$(".refund-amount input").show();
		}else{
			$(".refund-amount input").val();
			$("#partAmount").val('');
			$(".refund-amount input").hide();
		}
	});

	//金额输入校验
	$("#partAmount").bind('input propertychange', function() {
		var amount = $(this).val();
		if (amount < 0) {
			layer_required('退款金额不能小于0');
		}
		var rule = /^\d+\.?\d{0,2}$/ ;
		if (amount != '' && !rule.test(amount)){
			layer_required('请输入正确的退款金额');
		}
		var paymentName = $(".moneyInfo").attr("data-paymentName");
		var balancePrice = parseFloat($(".moneyInfo").attr("data-balancePrice"));
		var refundAmoun = parseFloat($(".moneyInfo").attr("data-refundAmoun"));
		refundMoney(refundAmoun,balancePrice,paymentName,parseFloat(amount));
	});

	//添加备注弹窗显示
	$(".addRemark").click(function(){
		var serviceSn = $(this).attr("data-id");
		$("#serviceSn").val(serviceSn);
		if (!serviceSn) {
			layer_required('请选择服务单');
			return false;
		}
		$.ajax({
			type: 'post',
			url: '/refund/addRemark',
			data: {
				'serviceSn':serviceSn,
				'isAdd':0,
			},
			cache: false,
			dataType:'json',
			success: function(msg){
				if(msg.status == 'success'){
					$(".serviceAccount").empty();
					$(".serviceAccount").append('当前账号：' + msg.data.account);
					popIfram($popRemark);
				}else{
					layer_required(msg.info);
				}
			}
		});
	});

	//同意退款
	$popVerify.find("button.agreen").on("click",function(){
		if (isAllowSubmit == 0) {
			return false;
		}
		var serviceSn = $("#serviceSn").val();
		if (!serviceSn) {
			layer_required('请选择服务单');
			return false;
		}
		var amountSel = $("#amountSel").val();
		var partAmount = $("#partAmount").val();
		var remark = $.trim($("#confirmRemark").val());
		var len = $(".operationAudit .max-txt i").html();
		if (len > 200) {
			layer_required('备注内容不能超过200个字符');
			return false;
		}
		if (amountSel == 0) {
			layer_required('请选择退款金额');
			return false;
		} else if (amountSel == 'part' && !partAmount) {
			layer_required('请输入退款金额');
			return false;
		}
		isAllowSubmit = 0;
		$.ajax({
			type: 'post',
			url: '/refund/confirmRefund',
			data: {
				'serviceSn':serviceSn,
				'isConfirm':1,
				'amountSel':amountSel,
				'partAmount':partAmount,
				'remark':remark,
			},
			cache: false,
			dataType:'json',
			success: function(msg){
				if(msg.status == 'success'){
					layer_required(msg.info);
					location.reload();
				}else{
					layer_required(msg.info);
					closePop($popVerify);
				}
				isAllowSubmit = 1;
			}
		});
	});

	//拒接退款
	$popVerify.find("button.disagreen").on("click",function(){
		if (isAllowSubmit == 0) {
			return false;
		}
		var serviceSn = $("#serviceSn").val();
		if (!serviceSn) {
			layer_required('请选择服务单');
			return false;
		}
		var remark = $.trim($("#confirmRemark").val());
		var len = $(".operationAudit .max-txt i").html();
		if (len > 200) {
			layer_required('备注内容不能超过200个字符');
			return false;
		}
		isAllowSubmit = 0;
		$.ajax({
			type: 'post',
			url: '/refund/confirmRefund',
			data: {
				'serviceSn':serviceSn,
				'isConfirm':1,
				'remark':remark,
				'disagreen':1,
			},
			cache: false,
			dataType:'json',
			success: function(msg){
				if(msg.status == 'success'){
					layer_required(msg.info);
					location.reload();
				}else{
					layer_required(msg.info);
					closePop($popVerify);
				}
				isAllowSubmit = 1;
			}
		});
	});

	//添加备注
	$popRemark.find("#ensure").on("click",function(){
		if (isAllowSubmit == 0) {
			return false;
		}
		var serviceSn = $.trim($("#serviceSn").val());
		if (serviceSn == '') {
			layer_required('请选择服务单');
			return false;
		}
		var len = $(".pop-add-frame .max-txt i").html();
		if (len == 0) {
			layer_required('请填写备注');
			return false;
		} else if (len > 200) {
			layer_required('备注内容不能超过200个字符');
			return false;
		}
		var remark = $("#remark").val();
		if ($.trim(remark) == '') {
			layer_required('备注不能全是空格');
			return false;
		}
		isAllowSubmit = 0;
		$.ajax({
			type: 'post',
			url: '/refund/addRemark',
			data: {
				'serviceSn':serviceSn,
				'remark':remark,
				'isAdd':1,
			},
			cache: false,
			dataType:'json',
			success: function(msg){
				if(msg.status == 'success'){
					layer_required(msg.info);
					location.reload();
				}else{
					layer_required(msg.info);
					closePop($popRemark);
				}
				isAllowSubmit = 1;
			}
		});
	});

	$(window).on('resize scroll',function(){
		$popVerify.css("left",($(window).width()-$popVerify.outerWidth())/2);
		$popVerify.css("top",($(window).height()-$popVerify.outerHeight())/2+$(window).scrollTop());
		$popRemark.css("left",($(window).width()-$popRemark.outerWidth())/2);
		$popRemark.css("top",($(window).height()-$popRemark.outerHeight())/2+$(window).scrollTop());
	});

	//添加备注取消按钮关闭弹窗
	$popRemark.find("#cancel").on("click",function(){
		return closePop($popRemark);
	})
	
	//收货取消按钮关闭弹窗
	$popVerify.find("#cancel").on("click",function(){
		return closePop($popVerify);
	})
	
	//审核/退款右上角X关闭弹窗
	$popVerify.on("click",".glyphicon-remove",function(){
		closePop($popVerify);
	});
	
	
	//备注右上角X关闭弹窗
	$popRemark.on("click",".glyphicon-remove",function(){
		closePop($popRemark);
	});

	//备注输入实时永久
	$("textarea").on("keyup",function(){
		var $num = $(this).parent().find(".max-txt i");
		$num.html(strlen($(this).val()));
		if(strlen($(this).val())>200){
			$num.addClass("red-txt");
		}else{
			$num.removeClass("red-txt");
		}
	});

	//处理结果选择
	$("#verifyResult").on("change",function(){
		var option = $(this).find("option:selected").html().trim();
		if(option == '退货退款'){
			$('.retun-way').show();
		}else{
			$('.retun-way').hide();
		}
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

	//关闭弹窗
	function closePop(obj){
		obj.hide();
		$popFrameshadow.hide();
		$("body").css("overflow","auto");
	}

	//显示弹窗
	function popIfram(obj){
		$popVerify.find("textarea").val("");
		obj.show();
		$popFrameshadow.show();
		$("body").css("overflow","hidden")
		obj.css("left",($(window).width()-obj.outerWidth())/2);
		obj.css("top",($(window).height()-obj.outerHeight())/2+$(window).scrollTop());
	}
	
	// 时间插件
    $('#startTime,#endTime').datetimepicker({step:10});


	$("#excel_export").click(function () {

		$("#export_div").modal('show');

		return false;

	})

	$("#check_all_group").click(function () {
		var is_check = $(this).prop('checked');
		$("input[name^='export_title']").each(function () {
			$(this).prop('checked',is_check);
		})

		$(".check_all").each(function () {
			$(this).prop('checked',is_check);
		})
	})


	$('.check_all').on('click',function(){

		var is_check = $(this).prop('checked');
		var group = $(this).val();
		//
		$("."+ group).each(function () {
			$(this).prop('checked',is_check);
		})
	});

	$("#exportAction").click(function () {
		if($("input[name^='export_title']:checked").length>0){
			$("#my_form").attr("action",'/refund/serviceexcel');
			return true;
		}else{
			alert("请选择要导出的字段！");
			return false;
		}
	})
	$("#submit").click(function () {
		$("#my_form").attr("action",'/refund/refundlist');
		return true;
	})
});