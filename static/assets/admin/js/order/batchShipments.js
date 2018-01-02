$(function(){
	//文件选择
	$('#uploadFiles input[name="files"]').change(function(){
		var files = $(this).val();
		if (files == '') {
			$(".warning").html("请选择上传的文件!");
			return false;
		}
		var name = files.split('\\');
		$(".warning").html(name[name.length-1]);
		$(".warning").show();
	});

	//上传文件
	$(".import").on("click", function(){
		var files = $('#uploadFiles input[name="files"]').val();
		if (files == '') {
			layer_required("请选择上传的文件");
			return false;
		}
		var extStart = files.lastIndexOf(".");
		var ext = files.substring(extStart, files.length).toUpperCase();
		if (ext != '.XLSX' && ext != '.XLS') {
			$(".warning").html("文件格式不对，请重新选择文件！");
			$(".warning").show();
			return false;
		}
	});
	
	//显示正常发货
	$(".normal").on("click", function(){
		$(this).addClass('active');
		$(".abnormality").removeClass('active');
		$(".validOrder").show();
		$(".invalidOrder").hide();
	});
	
	//显示发货信息缺失
	$(".abnormality").on("click", function(){
		$(this).addClass('active');
		$(".normal").removeClass('active');
		$(".validOrder").hide();
		$(".invalidOrder").show();
	});
	
	$(".confirmShipments").on("click",function(){
		//ajax提交
        ajaxSubmit('uploadFiles');
	});
});
