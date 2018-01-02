$(function(){
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
	});

	var serviceSn = $("#serviceSn").val();
	//是否允许提交
	var isAllowSubmit = 1;

	// 修改订单状态
	$("#changeStatus").on("click",function(){
		if (isAllowSubmit == 0) {
			return false;
		}
		var $select = $(this).parent().find("select");
		var $status = $(this).parent().find("span");
		var txt = $status.html().trim();
		var find = "option:contains('"+txt+"')";
		if($(this).attr("data-value") == 1){
			isAllowSubmit = 0;
			$.ajax({
				type: 'post',
				url: '/refund/changeRefundState',
				data: {
					'serviceSn':serviceSn,
					'isUpdate':0,
				},
				cache: false,
				dataType:'json',
				success: function(msg){
					if(msg.status == 'success'){
						$("#changeStatus").attr("data-value","2");
						$("#changeStatus").html("确定");
						$select.find(find).prop("selected",true);
						$select.show();
						$status.hide();
					}else{
						layer_required(msg.info);
					}
					isAllowSubmit = 1;
				}
			});
		}else if ($(this).attr("data-value") == 2){
			var seltxt = $select.find("option:selected").html();
			var state = $select.find("option:selected").val();
			var oldState = $select.attr("data-status");
			if (state != oldState) {
				isAllowSubmit = 1;
				$.ajax({
					type: 'post',
					url: '/refund/changeRefundState',
					data: {
						'serviceSn':serviceSn,
						'state':state,
						'isUpdate':1,
					},
					cache: false,
					dataType:'json',
					success: function(msg){
						if(msg.status == 'success'){
							$select.attr("data-status", state);
							$status.html(seltxt);
							$("#changeStatus").html("修改状态");
							$("#changeStatus").attr("data-value","1");
							$select.hide();
							$status.show();
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
	
	//插入一条操作日志显示
	function addLogShow(data){
		var tag = '<p>';
		tag += '<span>' + data.username + '</span>';
		tag += data.content + '<br />';
		tag += data.time;
		tag += '</p>';
		$(".orderLog").prepend(tag);
	}
});