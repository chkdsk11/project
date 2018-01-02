//公用方法封装
/**
 * @descr 多选框全选(全不选)方法
 * @param {Object} obj1 参数1为表格里全选的多选框 [单个]
 * @param {Object} obj2 参数2为表格内容里边的所有多选框 [多个]
 * @author 吴俊华
 * @date 2016-08-25
 * 如：checkAll($('#checkall'),$('.checkbox'));
 */
function checkAll(obj1,obj2){
	$(obj1).on('click',function(){
		if(obj1.prop('checked')){
			obj2.each(function(a,b){
      			$(b).prop('checked',true);
			});
		}else{
			obj2.each(function(a,b){
      			$(b).prop('checked',false);
			});
		}
	});
}

//jsonp 跨域：
function jsonp(url,func){
	var m_script = $('<script>');
	var temp = 'jsonp_' + Math.ceil(Math.random() * 1000000);
	var path = url + '&' + 'callback=' + temp;
	m_script.attr('src',path);
	window[temp] = func;
	$('body').append(m_script);
}


/******** 正则表达式 ********/
/** 
	使用方法：
	var price = $('#price').val();
	if (!regMoney.test(price)) {
		alert(价格只能是数字且最多是两位小数);
    	return;
   	}
 */

//只能输入数字的验证
var regNumber = /^[0-9]*$/;
//金额验证
var regMoney = /^[0-9]+(.[0-9]{1,2})?$/;
//1折验证
var regDiscount =/^[0-9]+(.[0-9]{0,1})?$/;
//身高体重验证
var regHeightWeight = /^[0-9]+(.[0-9]{1,2})?$/;
//手机号验证
var regMobliePhone = /^0?1[3|4|5|8|7][0-9]\d{8}$/;
//电话验证(支持手机号码，3-4位区号，7-8位直播号码，1-4位分机号)
var regPhone = /^1\d{10}$|^(0\d{2,3}-?|\(0\d{2,3}\))?[1-9]\d{4,7}(-\d{1,8})?$/;
//电子邮箱验证
var regEmail = /^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
//URL验证
var regUrl = /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.%-?=]*)*\/?$/;
//ip验证
var regIp = /((2[0-4]\d|25[0-5]|[01]?\d\d?)\.){3}(2[0-4]\d|25[0-5]|[01]?\d\d?)/;
//app格式验证
var appUrl = /^(App?:\/\/)?type=[1-7]&&&value=(.*)+$/;

var checkO = /^0{1,}[0-9]+$/;

var usNumber = /^[1-9]\d*$/;

var oneDecimal = /^[1-9](\.\d)?$|^0(\.[1-9])$/;
var twoDecimal = /^[1-9]\d*(\.\d{1,2})?$|^0\.(0[1-9]?|[1-9]\d?)$/;
//导出excel方法
function idownload(url){
	var i = document.createElement('iframe');
	i.style.display = 'none';
	i.name = 'idownload_iframe';
	i.src = url;
	document.body.appendChild(i);
	setTimeout(function(){
		$("iframe[name='idownload_iframe']").remove();
	},5000);
};

/******************** 引入layer.js后可使用以下方法 Start***********************/
/**
 使用方法：
 layer_required('活动名称不能为空');
 */
//必填项提示框
function layer_required(tips){
    layer.msg(tips,{
        time:2000
    });
    return false;
}
//成功sucess提示
function layer_success(tips,url){
    layer.open({
        content:tips,
        icon: 6,
        yes:function(index,layero){
            layer.close(index);
            layer.load(20, {
                shade: [0.3,'#ffffff'] //0.1透明度的白色背景
            });
            window.location.href = url;
        }
    });
    return false;
}
//失败error提示
function layer_error(tips){
    layer.open({
        content:tips,
        icon: 5
    });
    return false;
}
//确认confirm提示
function layer_confirm(msg,url,param){
    var msg = msg ? msg : '确定要删除吗?';
    if(url && param){
        layer.confirm(
            msg,
            function(index){
                $.post(url,param,function(data){
                    if(!data){
                        layer_error('操作失败');
                        return false;
                    }
                    if(data.status == 'error'){
                        layer_error(data.info);
                        return false;
                    }
                    if(data.status == 'success'){
                        layer.msg(
                            data.info,
                            {shade: 0.3, time: 1000},
                            function(){
                                layer.load(20, {
                                    shade: [0.3,'#ffffff'] //0.1透明度的白色背景
                                });
                                window.location.href = data.url;
                            }
                        )
                        //layer_success(data.info,data.url);
                        return false;
                    }
                },'json');
            }
        );
    }else{
        layer.confirm(
            msg,
            {icon: 2}
        );
    }

}
//ajax的get或post请求
function layer_request(type,url,param){
    var type = type ? type : 'post';
    if(type == 'post'){
        $.post(url,param,function(data){
            if(!data){
                layer_error('操作失败');
                return false;
            }
            if(data.status == 'error'){
                layer_error(data.info);
                return false;
            }
            if(data.status == 'success'){
                layer_success(data.info,data.url);
                return false;
            }
        },'json');
    }
    if(type == 'get'){
        $.get(url,param,function(data){
            if(!data){
                layer_error('操作失败');
                return false;
            }
            if(data.status == 'error'){
                layer_error(data.info);
                return false;
            }
            if(data.status == 'success'){
                layer_success(data.info,data.url);
                return false;
            }
        },'json');
    }
}


var time = 2000;//提示时间

//收集form表单信息
function ajaxSubmit(formid){
	var form = $('#'+formid);
	var url = form.attr('action');
	var data = {};
	data = form.serializeArray();
	_submitFn(url,data);
}

//ajax发送form表单信息
function _submitFn(url,data){

    //layer.load(0, {shade: false});

    //layer.load(20, {
    //    shade: [0.3,'#ffffff'] //0.1透明度的白色背景
    //});
	$.ajax({
		type: 'POST',
		url: url,
		data: data,
		cache: false,
		dataType:'json',
		success: okCallback,
		error: errorCallback
	});
}

//ajax发送请求信息
function ajaxFn(url,data,type){
	$.ajax({
		type: type,
		url: url,
		data: data,
		cache: false,
		dataType:'json',
		success: okCallback,
		error: errorCallback
	});
}

//重新加载页面
function refresh(){
	setTimeout(function(){
		location.reload(true);
	},1000);
}

//form表单发送成功处理
function okCallback(msg){
	if(msg.status == 'error'){
        layer.load(20, {time: 1});
		layer.msg(msg.info, {shade: 0.3, time: 1000}); return false;
	}else if(msg.status == 'success'){
        parent.layer.msg(
			msg.info,
			{shade: 0.3, time: time},
			function(){
                layer.load(20, {
                    shade: [0.3,'#ffffff'] //0.1透明度的白色背景
                });
                parent.window.location.href = msg.url; return true;
			}
		)
	}
}

//form表单发送失败处理
function errorCallback(){
    layer.load(20, {time: 1});
	layer.msg('操作失败!');return false;
}

//删除操作提示
function showConfirm(url,msg){
	var msg = msg?msg:'确定要删除吗?';
	layer.confirm(msg,{icon: 2},function(index){
		$.ajax({
			type: 'post',
			url: url,
			cache: false,
			success: function(msg){
			    if(typeof msg == 'string'){
                    msg = $.parseJSON(msg);
                }
				layer.msg(msg.info,{shade: 0.3});
				refresh();
			},
			error: errorCallback
		});

	});

}

//弹窗页面
function showWindow(title,url,width,height,scroll,is_load){
    var win_width = width?width + 'px':'500px';
    var win_height = height?height + 'px':'500px';
    s = scroll == true?'':'no';
    layer.open({
        type: 2,
        title: title?title:'',
        fix: false,
        shadeClose: false,
        maxmin: true,
        area: [win_width, win_height],
        content: [url,s],
        scrollbar: false,
        end: function () {
            is_load == true ? location.reload() : '';
        }
    });
}

/******************** 引入layer.js后可使用以下方法 END ***********************/

/**
 * @desc 商品分类三级联动
 * @author 吴俊华
 * @date 2016-08-29
 * @modify 梁伟 增加无限极分类 2016-09-7
 */
$(function(){
    var one_category = $('#one_category'); //一级分类
    var two_category = $('#two_category'); //二级分类
    var three_category = $('#three_category'); //三级分类
    var category_son = $('.category_son').html();//是否需要无限极分类
    var categoryBox = $('#categoryBox');

    //输出一级分类下面的子分类
    one_category.on('change',function(){
        //初始化城市选项名
        two_category.find("option").remove();
        $('#three_category').find("option").remove();
        two_category.append('<option value="0">--请选择--</option>');
        $('#three_category').hide();
        var one_category_id = one_category.val();
        if(one_category_id == 0){
            if(category_son != undefined){
                $('.category_son').html('');
            }
            return;
        }
        $.post('/promotion/getCategory',{pid: one_category_id},function(data){
            if(!data){
                return;
            }
            if(data.status == 'success'){
                if(data.data.length > 0){
                    two_category.show();
                    for(var i = 0;i < data.data.length;i++){
                        two_category.append('<option value="'+data.data[i]['id']+'">'+data.data[i]['category_name']+'</option>');
                    }
                    if(category_son != undefined){
                        $('.category_son').html('');
                    }
                }
            }
        },"json");

    });

    //输出二级分类下面的子分类
    two_category.on('change',function(){
        //初始化区县选项名
        three_category.find("option").remove();
        three_category.append('<option value="0">--请选择--</option>');
        var two_category_id = two_category.val();
        if(two_category_id == 0){
            if(category_son != undefined){
                $('.category_son').html('');
            }
            return;
        }
        $.post('/promotion/getCategory',{pid: two_category_id},function(data){
            if(!data){
                if(category_son != undefined){
                    $('.category_son').html('');
                }
                return;
            }
            if(data.status == 'success'){
                if(data.data.length > 0){
                    three_category.show();
                    for(var i = 0;i < data.data.length;i++){
                        three_category.append('<option value="'+data.data[i]['id']+'">'+data.data[i]['category_name']+'</option>');
                    }
                    if(category_son != undefined){
                        $('.category_son').html('');
                    }
                }
            }
        },"json");
    });
    if(category_son != undefined){
        //无限极分类
        $("#three_category").on('change',function(){
            var one_category_id = $(this).val();
            if(one_category_id == 0){
                if(category_son != undefined){
                    $('.category_son').html('');
                }
                return;
            }
            $('.category_son').html('');
            $.post('/promotion/getCategory',{pid: one_category_id},function(data){
                if(!data){
                    if(category_son != undefined){
                        $('.category_son').html('');
                    }
                    return;
                }
                if(data.status == 'success'){
                    if(data.data.length > 0){
                        var html = '<select name="shop_category[]" class="category_infinite">';
                        html += '<option value="">--请选择--</option>';
                        for(var i = 0;i < data.data.length;i++){
                            html += '<option value="'+data.data[i]['id']+'">'+data.data[i]['category_name']+'</option>';
                        }
                        html += '</select>';
                        $('.category_son').append(html);
                    }
                }
            },"json");
        });
        $(document).on('change','.category_infinite',function(){
            var one_category_id = $(this).val();
            if(one_category_id == 0){
                $(this).nextAll().remove();
                return;
            }
            var th = this;
            $.post('/promotion/getCategory',{pid: one_category_id},function(data){
                if(!data){
                    $(th).nextAll().remove();
                    return;
                }
                if(data.status == 'success'){
                    if(data.data.length > 0){
                        var html = '<select name="shop_category[]" class="category_infinite">';
                        html += '<option value="">--请选择--</option>';
                        for(var i = 0;i < data.data.length;i++){
                            html += '<option value="'+data.data[i]['id']+'">'+data.data[i]['category_name']+'</option>';
                        }
                        html += '</select>';
                        $('.category_son').append(html);
                    }else{
                        $(th).nextAll().remove();
                    }
                }
            },"json");
        });
    }
})
/**
 * 判断分类是否全选
 * return bool  true|false;
 */
function isCategorySelect(){
    var act = true;
    var one_category = $('#one_category').val();
    var two_category = $('#two_category').val();
    var three_category = $("#three_category").val();
    if(one_category == null || one_category == 0){
        layer_required('分类请添加完整！');
        act = false;
    }
    if(two_category == null || two_category == 0){
        layer_required('分类请添加完整！');
        act = false;
    }
    if(three_category == null || three_category == 0){
        layer_required('分类请添加完整！');
        act = false;
    }
    var son = $(".category_son").children(":last").val();
    if(son != undefined){
        if(son <= 0){
            layer_required('分类请添加完整！');
            act = false;
        }
    }
    return act;
}
function arrRepeat(arr){
    var arrStr = JSON.stringify(arr);
    for (var i = 0; i < arr.length; i++) {
        if (arrStr.indexOf(arr[i]) != arrStr.lastIndexOf(arr[i])){
            return true;
        }
    };
    return false;
}
//$('body').on('keyup','input,textarea',function () {
//    var inputValue = $(this).val();
//    if(inputValue.length > 0){
//        if(inputValue.indexOf('，') > 0){
//            $(this).val(inputValue.replace('，',','));
//            layer_required('发现中文逗号字符,系统已经自动为您替换');
//        }
//    }
//});

