$(function(){
	//是否允许提交
	var isAllowSubmit = 1;
	var pcUrl = $("#pcUrl").val();

	// 弹框事件
	var $popFrame = $(".pop-frame-content");
	var $popFrameshadow= $(".pop-frame-shadow");
	var $popAddRemark = $(".pop-add-remark");
	var $popExpress = $(".pop-express");
	
	//点击审核获取订单信息
	$(".checkOrder").click(function(){
		if (isAllowSubmit == 0) {
			return false;
		}
		var totalSn = $(this).attr('data-total');
		isAllowSubmit = 0;
		$.ajax({
            type: 'post',
            url: '/order/orderAudit',
            data: {'totalSn':totalSn,'isAudit':0},
            cache: false,
            dataType:'json',
            success: function(msg){
                if(msg.status == 'success'){
					$(".pop-frame p").text('回拨电话：'+msg.data.phone);
					$("#auditTotalSn").val(msg.data.total_sn);
					var len = msg.data.products.productList.length;
					var tableVal = '';
					$(".auditOrder tbody").empty();
					if (len > 0) {
						$.each(msg.data.products.productList,function(k,v) {
							tableVal += '<tr>'
							tableVal += '<td>' + v.goods_id + '</td>';
							tableVal += '<td><a href="' + pcUrl + '/product/' + v.goods_id + '.html" target="_blank">';
							if (v.drug_type == 1) {
								tableVal += '<span class="icon-drug-red">Rx</span>';
							} else if (v.drug_type == 2) {
								tableVal += '<span class="icon-drug-red">OTC</span>';
							} else if (v.drug_type == 3) {
								tableVal += '<span class="icon-drug-green">OTC</span>';
							}
							tableVal += v.goods_name + '</a></td>';
							if (v.name_id2 && v.name_id3) {
								tableVal += '<td>' + v.name_id2 + '，' + v.name_id3 + '</td>';
							} else if (v.name_id2 && !v.name_id3) {
								tableVal += '<td>' + v.name_id2 + '</td>';
							} else if (!v.name_id2 && v.name_id3) {
								tableVal += '<td>' + v.name_id3 + '</td>';
							} else {
								tableVal += '<td></td>';
							}
							tableVal += '<td>' + v.goods_number + '</td>';
							tableVal += '<td>' + v.promotion_total + '</td>';
							tableVal += '</tr>';
						});
					} else {
						tableVal += '<tr><td>该订单没有商品数据</td></tr>';
					}
					$(".auditOrder tbody").append(tableVal);
					if (msg.data.photo) {
						$(".auditOrderPhoto div").empty();
						$(".auditOrderPhoto div").append('<a href="' + msg.data.photo + '" class="lightbox" rel="thumbnails">' + 
							'<img src="' + msg.data.photo + '" >' + 
							'</a>');
						$(".auditOrderPhoto").show();
					} else {
						$(".auditOrderPhoto").hide();
					}
					showPop();
                }else{
                    layer_required(msg.info);
                }
                isAllowSubmit = 1;
            }
        });
	});
	
	//显示审核弹窗
	function showPop(){
		$popFrame.show();
		$popFrameshadow.show();
		$("body").css("overflow","hidden");
		$popFrame.css("left",($(window).width()-$popFrame.outerWidth())/2);
		$popFrame.css("top",($(window).height()-$popFrame.outerHeight())/2+$(window).scrollTop());
	}
	
	//右上角X关闭审核弹窗
	$popFrame.on("click",'.glyphicon-remove',function(even){
		if($(this).hasClass("glyphicon-remove")){
			closeFrame(2);
		}
	});
	
	//显示发货弹窗
	$(".deliverGoods").click(function(){
		if (isAllowSubmit == 0) {
			return false;
		}
		var orderSn = $(this).attr("data-id");
		$("#orderSn").val(orderSn);
		isAllowSubmit = 0;
		 $.ajax({
		 	url:"/order/shipments",
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
	
	//显示添加备注弹窗
	$(".addRemark").click(function(){
		if (isAllowSubmit == 0) {
			return false;
		}
		var orderSn = $(this).attr("data-id");
		$("#orderSn").val(orderSn);
		isAllowSubmit = 0;
		$.ajax({
			type: 'post',
			url: '/order/addOrderRemark',
			data: {
				'orderSn':orderSn,
				'isAdd':0,
			},
			cache: false,
			dataType:'json',
			success: function(msg){
				if(msg.status == 'success'){
					$(".serviceAccount").empty();
					$(".serviceAccount").append('当前账号：' + msg.data.account);
					$popAddRemark.show();
					$popFrameshadow.show();
					$("body").css("overflow","hidden");
					$popAddRemark.css("left",($(window).width()-$popAddRemark.outerWidth())/2);
					$popAddRemark.css("top",($(window).height()-$popAddRemark.outerHeight())/2+$(window).scrollTop());
				}else{
					layer_required(msg.info);
				}
				isAllowSubmit = 1;
			}
		});
	});
	$(window).on('resize scroll',function(){
		$popFrame.css("left",($(window).width()-$popFrame.outerWidth())/2);
		$popFrame.css("top",($(window).height()-$popFrame.outerHeight())/2+$(window).scrollTop());
		$popAddRemark.css("left",($(window).width()-$popAddRemark.outerWidth())/2);
		$popAddRemark.css("top",($(window).height()-$popAddRemark.outerHeight())/2+$(window).scrollTop());
	});
	
	//实时计算不审核通过原因输入字数
	$(".pop-frame textarea").on("keyup",function(){
		var $num = $(this).parent().find(".max-txt i");
		$num.html(strlen($(this).val()));
		if(strlen($(this).val())>200){
			$num.addClass("red-txt");
		}else{
			$num.removeClass("red-txt");
		}
	});
	
	//实时计算备注输入字数
	$(".pop-add-remark textarea").on("keyup",function(){
		var $num = $(this).parent().find(".max-txt i");
		$num.html(strlen($(this).val()));
		if(strlen($(this).val())>200){
			$num.addClass("red-txt");
		}else{
			$num.removeClass("red-txt");
		}
	});
	
	//添加备注
	$(".pop-add-remark #ensure").on('click',function(){
		if (isAllowSubmit == 0) {
			return false;
		}
		var len = $(".pop-add-frame .max-txt i").html();
		if (len == 0) {
			layer_required('请填写备注');
			return false;
		}
		if(len>200){
			layer_required('备注内容不能超过200个字符');
			return false;
		}
		var orderSn = $("#orderSn").val();
		var remark = $.trim($("#remark").val());
		if (remark == '') {
			layer_required('备注不能全是空格');
			return false;
		}
		isAllowSubmit = 0;
		$.ajax({
			type: 'post',
			url: '/order/addOrderRemark',
			data: {
				'orderSn':orderSn,
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
					closeFrame(1);
				}
				isAllowSubmit = 1;
			}
		});
	});
	
	//添加备注取消
	$(".pop-add-remark").on("click","#cancel",function(){
		closeFrame(1);
	});
	
	//右上角X关闭备注弹出
	$(".pop-add-remark").on("click",".glyphicon-remove",function(){
		closeFrame(1);
	});
	
	//审核结果修改
	$("#verifyResult").on("change",function(){
		var option = $(this).find("option:selected").val();
		if(option == "2"){
			$(".reason").show();
		} else {
			$(".reason").hide();
		}
		$("#reason").val('');
	})
	
	//textarea 内字数显示变化
	$(".reason textarea").on("keyup",function(){
		var $num = $(this).parent().find(".max-txt i");
		$num.html(strlen($(this).val()));
		if(strlen($(this).val())>200){
			$num.addClass("red-txt");
		}else{
			$num.removeClass("red-txt");
		}
	});
	
	//审核订单
	$(".submitAudit").on('click',function(){
		if (isAllowSubmit == 0) {
			return false;
		}
		var len = $(".pop-frame-main-content .max-txt i").html();
		if(len>200){
			layer_required('备注内容不能超过200个字符');
			return false;
		}
		var state = $("#verifyResult").val();
		if (state == 0) {
			layer_required('请选择审核结果');
			return false;
		}
		var totalSn = $("#auditTotalSn").val();
		var reason = $.trim($("#reason").val());
		if (state == 2 && reason == '') {
			layer_required('请填写审核不通过原因');
			return false;
		}
		isAllowSubmit = 0;
		$.ajax({
			type: 'post',
			url: '/order/orderAudit',
			data: {'totalSn':totalSn,'state':state,'isAudit':1,'reason':reason},
			cache: false,
			dataType:'json',
			success: function(msg){
				if(msg.status == 'success'){
					layer_required(msg.info);
					location.reload();
				}else{
					layer_required(msg.info);
					closeFrame(2);
				}
				isAllowSubmit = 1;
			}
		});
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

	// 关闭弹窗
	function closeFrame(val){
		if (val == 1) {
			$("#remark").val('');
			$("#orderSn").val('');
			$("body").css("overflow","auto");
			$(".pop-frame-shadow").hide();
			$(".pop-add-remark").hide();
		} else if (val == 2) {
			$("#reason").val('');
			$("#auditTotalSn").val('');
			$popFrame.hide();
			$popFrameshadow.hide();
			$("body").css("overflow","auto");
		} else if (val == 3) {
			$("#orderSn").val('');
			$("body").css("overflow","auto");
			$popExpress.hide();
			$popFrameshadow.hide();
		}
	}
	
	// 时间插件
    $('#start_time,#end_time').datetimepicker({step:10});
	
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
			url:"/order/shipments",
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
			url:"/order/shipments",
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
	
	//批量发货跳转
	$(".batchDelivery").on("click",function(){
		location.href = "/order/batchShipments";
	});

	//导出发货单
	$(".guideInvoices").on("click",function(){
		$.ajax({
			url:"/order/guideInvoices",
		 	type:"post",
		 	datatype:"json",
		 	data:{},
		 	success:function(msg){
				if(msg.status == 'success'){
					window.open("/order/guideInvoices");
				}else{
					layer_required(msg.info);
				}
		 	}
		});
	});


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
	//普通订单列表导出
	$("#exportAction").click(function () {
		if($("input[name^='export_title']:checked").length>0){
			$("#my_form").attr("action",'/order/orderexcel');
			return true;
		}else{
			layer_required("请选择要导出的字段！");
			return false;
		}
	});
	//普通订单列表查询
	$("#submit").click(function () {
		$("#my_form").attr("action",'/order/orderlist');
		return true;
	});
	//育学园订单列表查询
	$("#yukonSubmit").click(function () {
		$("#my_form").attr("action",'/order/yukonOrderList');
		return true;
	});
	//育学园订单列表导出
	$("#yukonExportAction").click(function () {
		if($("input[name^='export_title']:checked").length>0){
			$("#my_form").attr("action",'/order/yukonOrderExcel');
			return true;
		}else{
			layer_required("请选择要导出的字段！");
			return false;
		}
	});
	$("select[name='userType']").on("change", function(){
		var userType = $(this).val();
		var goodsType = $("select[name='goodsType']").val();
		$("select[name='goodsType']").empty();
		if (userType == 'baiyangwang') {
			$("select[name='goodsType']").append('<option value="yukon">育学园商品</option>');
		} else {
			var option = '<option value="0">全部</option>';
            option += goodsType == 'yukon' 
            	? '<option selected value="yukon">育学园商品</option>' : '<option value="yukon">育学园商品</option>';
            option += goodsType == 'baiyangwang' 
            	? '<optionselected  value="baiyangwang">非育学园商品</option>' : '<option value="baiyangwang">非育学园商品</option>';
			$("select[name='goodsType']").append(option);
		}
	});
	$("select[name='goodsType']").on("change", function(){
		var goodsType = $(this).val();
		var userType = $("select[name='userType']").val();
		$("select[name='userType']").empty();
		if (goodsType == 'baiyangwang') {
			$("select[name='userType']").append('<option value="yukon">育学园用户</option>');
		} else {
			var option = '<option value="0">全部</option>';
            option += userType == 'baiyangwang' 
            	? '<option selected value="baiyangwang">奇药用户</option>' : '<option value="baiyangwang">奇药用户</option>';
            option += userType == 'yukon' 
            	? '<option selected value="yukon">育学园用户</option>' : '<option value="yukon">育学园用户</option>';
			$("select[name='userType']").append(option);
		}
	});
});
