$(function(){
	//是否允许提交
	var isAllowSubmit = 1;
	//订单号
	var orderSn = $("#orderSn").val();
	// 修改订单状态
	var $changeStatus = $("#changeStatus");
	$changeStatus.on("click",function(){
		if (isAllowSubmit == 0) {
			return false;
		}
		var $select = $(this).parent().find("select");
		var $status = $(this).parent().find("span");
		var txt = $status.html().trim();
		var find = "option:contains('"+txt+"')";
		if($(this).html().trim() == '修改状态'){
			isAllowSubmit = 0;
			$.ajax({
				type: 'post',
				url: '/order/changeOrderState',
				data: {
					'orderSn':orderSn,
					'isUpdate':0,
				},
				cache: false,
				dataType:'json',
				success: function(msg){
					if(msg.status == 'success'){
						$changeStatus.html("确定");
						$select.find(find).prop("selected",true);
						$select.show();
						$status.hide();
					}else{
						layer_required(msg.info);
					}
					isAllowSubmit = 1;
				}
			});
		}else if ($(this).html() == '确定'){
			var seltxt = $select.find("option:selected").html();
			var state = $select.find("option:selected").val();
			var oldState = $select.attr("data-status");
			if (state != oldState) {
				isAllowSubmit = 0;
				$.ajax({
					type: 'post',
					url: '/order/changeOrderState',
					data: {
						'orderSn':orderSn,
						'state':state,
						'isUpdate':1,
					},
					cache: false,
					dataType:'json',
					success: function(msg){
						if(msg.status == 'success'){
							$status.html(seltxt);
							$(this).html("修改状态");
							$select.hide();
							$status.show();
							$select.attr("data-status", state);
							addLogShow(msg.data)
							layer_required(msg.info);
						}else{
							layer_required(msg.info);
						}
						isAllowSubmit = 1;
					}
				});
			}
		}
	});
	
	//省份选择
	$("#provinceSel").on("change",function(){
		var selVal = $(this).find("option:selected").val();
		if (selVal == 0) {
			layer_required('请选择省份');
			return false;
		}
		changSelVal(1,selVal);
	});
	
	//市选择
	$("#citySel").on("change",function(){
		var selVal = $(this).find("option:selected").val();
		if (selVal == 0) {
			layer_required('请选择市');
			return false;
		}
		changSelVal(2,selVal);
	});
	
	//省市选择更改获取数据
	function changSelVal(item,selVal){
		if (isAllowSubmit == 0) {
			return false;
		}
		isAllowSubmit = 0;
		$.ajax({
			type: 'post',
			url: '/order/changeAddress',
			data: {
				'orderSn':orderSn,
				'pid':selVal,
				'isUpdate':0
			},
			cache: false,
			dataType:'json',
			success: function(msg){
				if(msg.status == 'success'){
					var tag = '<option value="0">请选择</option>';
					$.each(msg.data,function(k,v) {
						tag += '<option value="' + k + '">' + v + '</option>';
					});
					if (item == 1) {
						$("#citySel").empty();
						$("#countySel").empty();
						$("#countySel").append('<option value="0">请选择</option>');
						$("#citySel").append(tag);
					} else {
						$("#countySel").empty();
						$("#countySel").append(tag);
					}
				}else{
					layer_required(msg.info);
				}
				isAllowSubmit = 1;
			}
		});
	}
	
	//收货地址修改
	$(".addressSubmit").on("click",function(){
		if (isAllowSubmit == 0) {
			return false;
		}
		var consignee = $.trim($("#consignee").val());
		if (consignee == '') {
			layer_required('请填写收货人');
			return false;
		}
		var telephone = $.trim($("#telephone").val());
		if (telephone == '') {
			layer_required('请填写手机号');
			return false;
		}
		var pattern = /^1[34578]\d{9}$/;
		if (!pattern.test(telephone)) {
			layer_required('手机号格式错误');
			return false;
		} 
		var province = $("#provinceSel").val();
		if (province == 0) {
			layer_required('请选择省份');
			return false;
		}
		var city = $("#citySel").val();
		if (city == 0) {
			layer_required('请选择市');
			return false;
		}
		var county = $("#countySel").val();
		if (county == 0) {
			layer_required('请选择区');
			return false;
		}
		var address = $.trim($("#addressInfo").val());
		if (address == '') {
			layer_required('请填写详细地址');
			return false;
		}
		isAllowSubmit = 0;
		$.ajax({
			type: 'post',
			url: '/order/changeAddress',
			data: {
				'orderSn':orderSn,
				'isUpdate':1,
				'consignee':consignee,
				'telephone':telephone,
				'province':province,
				'city':city,
				'county':county,
				'address':address,
			},
			cache: false,
			dataType:'json',
			success: function(msg){
				if(msg.status == 'success'){
					var provinceText = $("#provinceSel ").find("option:selected").text();
					var cityText = $("#citySel").find("option:selected").text();
					var countyText = $("#countySel").find("option:selected").text();
					var tag = '<p>收  货  人 ：' + consignee + '</p>';
					tag += '<p>手机号码：' + telephone + '</p>';
					tag += '<div class="more-content">';
					tag += '<span>收货地址：</span>';
					tag += '<p>' + provinceText + ' ' + cityText + ' ' + countyText + ' ' + address + '</p>';
					tag += '</div>';
					$(".receivingInfo").empty();
					$(".receivingInfo").append(tag);
					addLogShow(msg.data);
					closeFrame(2);
					layer_required(msg.info);
				}else{
					layer_required(msg.info);
				}
				isAllowSubmit = 1;
			}
		});
	});

	$("#titleType").on("change",function(){
		var titleType = $(this).val();
		if (titleType == '单位') {
			$("#taxpayerNumber").show();
		} else {
			$("#taxpayerNumber").hide();
		}
	});
	
	//发票信息修改
	$(".changBill").on("click",function(){
		if (isAllowSubmit == 0) {
			return false;
		}
		var invoiceType = $("#invoiceType").val();
		var titleType = $("#titleType").val();
		var titleName = $.trim($("#titleName").val());
		var contentType = $("#contentType").val();
		var taxpayerNumber = $.trim($("#taxpayerNumber input").val());
		if (titleType == '单位' && titleName == '') {
			layer_required('请填写发票抬头');
			return false;
		}
		if (titleType == '单位' && taxpayerNumber == '') {
			layer_required('请填写税号');
			return false;
		} else if (titleType == '个人') {
			taxpayerNumber = '';
		}
		isAllowSubmit = 0;
		$.ajax({
			type: 'post',
			url: '/order/changeBill',
			data: {
				'orderSn':orderSn,
				'invoiceType':invoiceType,
				'titleType':titleType,
				'titleName':titleName,
				'contentType':contentType,
				'taxpayerNumber':taxpayerNumber,
			},
			cache: false,
			dataType:'json',
			success: function(msg){
				if(msg.status == 'success'){
					var tag = '<p>发票类型：';
					if (invoiceType == 0) {
						tag += '不需要发票';
					} else if (invoiceType == 1) {
						tag += '个人';
					} else if (invoiceType == 2) {
						tag += '单位';
					} else if (invoiceType == 3) {
						tag += '纸质发票</p>';
					}
					tag += '</p>';
					if (invoiceType != 0) {
						tag += '<div class="more-content">';
						tag += '<span>发票抬头：</span>';
						tag += '<p>' + titleName + '（' + titleType + '）' + '</p>';
						tag += '</div>';
						if (taxpayerNumber) {
							tag += '<div class="more-content">';
							tag += '<span>税<font style="margin-left:26px;">号</font>：</span>';
							tag += '<p>' + taxpayerNumber + '</p>';
							tag += '</div>';
						}
						tag += '<div class="more-content">';
						tag += '<span>发票内容：</span>';
						tag += '<p>' + contentType + '</p>';
						tag += '</div>';
					}
					$(".billDetail").empty();
					$(".billDetail").append(tag);
					addLogShow(msg.data);
					layer_required(msg.info);
				}else{
					layer_required(msg.info);
				}
				isAllowSubmit = 1;
			}
		});
		closeFrame(1);
	});
	
	//插入一条操作日志显示
	function addLogShow(data){
		var tag = '<p>';
		tag += '<span>' + data.username + '</span>';
		tag += data.content + '<br />';
		tag += data.time;
		tag += '</p>';
		$(".orderLog").prepend(tag);
	}

	// 收起-展开
	var $orderOperateLog = $(".operate");
	$orderOperateLog.on("click",'a',function(){
		if($(this).html().trim() == '收起'){
			$(this).html("展开");
			$(".orderLog").hide();
		}else if($(this).html().trim() == '展开'){
			$(this).html("收起");
			$(".orderLog").show();
		}
	})

	var $popFrameshadow= $(".pop-frame-shadow");
	var $popModifyAddress = $(".pop-modify-address");
	var $popModifyBill = $(".pop-modify-bill");
	var $popExpress = $(".pop-express");
	// 修改地址-弹窗
	$(".receiver-info a").on("click",function(){
		$popFrameshadow.show();
		$popModifyAddress.show();
		$("body").css("overflow","hidden");
		$popModifyAddress.css("left",($(window).width()-$popModifyAddress.outerWidth())/2);
		$popModifyAddress.css("top",($(window).height()-$popModifyAddress.outerHeight())/2+$(window).scrollTop());
	});

	//右上角X关闭修改地址弹窗
	$popModifyAddress.on("click",'.glyphicon-remove',function(even){
		if($(this).hasClass("glyphicon-remove")){
			closeFrame(2);
		}
	});

	//修改发票-弹窗
	$(".bill-info a").on("click",function(){
		$popFrameshadow.show();
		$popModifyBill.show();
		$("body").css("overflow","hidden");
		$popModifyBill.css("left",($(window).width()-$popModifyBill.outerWidth())/2);
		$popModifyBill.css("top",($(window).height()-$popModifyBill.outerHeight())/2+$(window).scrollTop());
	});
	//右上角X关闭修改发票弹窗
	$popModifyBill.on("click",'.glyphicon-remove',function(even){
		if($(this).hasClass("glyphicon-remove")){
			closeFrame(1);
		}
	});
	
	// 关闭弹窗
	function closeFrame(val){
		if (val == 1) {
			$popModifyBill.hide();
			$popFrameshadow.hide();
			$("body").css("overflow","auto");
		} else if (val == 2) {
			$popModifyAddress.hide();
			$popFrameshadow.hide();
			$("body").css("overflow","auto");
		} else {
			$popExpress.hide();
			$popFrameshadow.hide();
			$("body").css("overflow","auto");
		}
	}

	$(window).on('resize scroll',function(){
		$popModifyAddress.css("left",($(window).width()-$popModifyAddress.outerWidth())/2);
		$popModifyAddress.css("top",($(window).height()-$popModifyAddress.outerHeight())/2+$(window).scrollTop());
		$popModifyBill.css("left",($(window).width()-$popModifyBill.outerWidth())/2);
		$popModifyBill.css("top",($(window).height()-$popModifyBill.outerHeight())/2+$(window).scrollTop());
	});

	//显示发货弹窗
	$(".express-info a").click(function(){		
		if (isAllowSubmit == 0) {
			return false;
		}
		isAllowSubmit = 0;
		$.ajax({
		 	url:"/order/changeExpressInfo",
		 	type:"post",
		 	datatype:"json",
		 	data:{
				"orderSn":orderSn,
				"isCheckOrder":1,
		 	},
		 	success:function(msg){
				if(msg.status == 'success'){
					$popExpress.show();
					$popFrameshadow.show();
					$("body").css("overflow","hidden");
					$popExpress.css("left",($(window).width()-$popExpress.outerWidth())/2);
					$popExpress.css("top",($(window).height()-$popExpress.outerHeight())/2+$(window).scrollTop());
				}else{
					layer_required(msg.info);
				}
				isAllowSubmit = 1;
			}
		});
	});
	
	//右上角X关闭发货弹窗
	$popExpress.on("click",'.glyphicon-remove',function(even){
		if($(this).hasClass("glyphicon-remove")){
			closeFrame(3);
		}
	});

	//实时匹配输入的物流单号
	var time = 0;
	$("#checkExpress").on("input propertychange",function(e){
		var val = $("#checkExpress").val();
		var orderSn = $("#orderSn").val();
		if(val.indexOf("|") == -1){
			var filterVal = $("#checkExpress").val().replace(/[^0-9a-zA-Z]/g,"");
			if(filterVal.length >25){
				filterVal = filterVal.substr(0,25);
				layer_required("请输入4-25位正确的物流单号");
				return false;
			}
			$(this).val(filterVal);
			if(filterVal){
				clearTimeout(time);
				time = setTimeout(getExpressCompany(orderSn,filterVal),300);
			}else{
				$(".shotrcut > p").remove();
				$(".pop-express-m .shotrcut").hide();
			}
		}
	});

	$(".pop-express-m").on("click",".shotrcut p",function(){
		if($(this).data("role")){ 
			$(".shotrcut").hide();
			$("#checkExpress").val($("#checkExpress").val().trim()+"   |   ").focus();
		}else{
			var company = $(this).find("span").text();
			
			$(".shotrcut").hide();
			$("#checkExpress").val($("#checkExpress").val().trim()+"   |   "+company).focus();
		}
	});

	//确定发货
	$("#expressInquiry").on("click",function(){
		var val = $("#checkExpress").val().split("|");
		var orderSn = $("#orderSn").val();
		if(!val[0]){
			layer_required("请输入物流单号");
			return false;
		}
		if(val[0] && val[0].length<4){
			layer_required("请输入4-25位正确的物流单号");
			return false;
		}
		if(!val[1]){
			layer_required("请输入物流公司");
			return false;
		}
		var expressNo = val[0].trim();
		var expressCompany = val[1].trim();
		getExpressInfo(orderSn,expressNo,expressCompany);
	});


	// 发送请求
	function getExpressInfo(orderSn,no,company){
		if (isAllowSubmit == 0) {
			return false;
		}
		isAllowSubmit = 0;
		$.ajax({
			url:"/order/changeExpressInfo",
		 	type:"post",
		 	datatype:"json",
		 	data:{
				"orderSn":orderSn,
				"isCheckOrder":0,
		 		"expressSn":no,
				"isSend":1,
				"company":company,
		 	},
		 	success:function(msg){
				var html = "";
				if(msg.status == 'success'){
					layer_required(msg.info);
					location.reload();
				}else{
					layer_required(msg.info);
					closeFrame(3);
				}
				isAllowSubmit = 1;
		 	}
		});
	}

	// 查询快递公司
	function getExpressCompany(orderSn,no){
		if (isAllowSubmit == 0) {
			return false;
		}
		isAllowSubmit = 0;
		$.ajax({
			url:"/order/changeExpressInfo",
		 	type:"post",
		 	datatype:"json",
		 	data:{
				"orderSn":orderSn,
				"isCheckOrder":0,
		 		"expressSn":no,
				"isSend":0,
		 	},
		 	success:function(msg){
				var html = "";
				if(msg.status == 'success'){
					$.each(msg.data,function(k,v) {
						html += '<p>'+no+'<span>'+v+'</span></p>';
					});
					$(".shotrcut > p").remove();
					$(".pop-express-m .shotrcut").prepend(html).show();
				}else{
					html += '<p data-role="user-defined"><label for="checkExpress">'+no+'<span>请输入物流公司</span></label></p>';
					$(".shotrcut > p").remove();
					$(".pop-express-m .shotrcut").prepend(html).show();
				}
				isAllowSubmit = 1;
		 	}
		});
	}
});
