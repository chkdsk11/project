/*** 支付控制配置 ***/
//渠道号
var channel = [1,85,89,90,91,95];
/**
 * 页面操作JS
 * @author CSL
 * @date 2018-01-02
 */
$(function() {
    $.each(channel,function(k,v) {
        //页面加载时判断各端是否选在线支付
        $('.payment_' + v).each(function () {
            if ($(this).prop('checked') == true) {
                $("input:checkbox[name='online_payment_" + v + "']").prop('checked', true);
                return false;
            }
        });
    });
});

/**
 * 在线支付不选，下面的支付方式都不选
 * @param channalId int 端口号
 */
function changeOnlinePayment(channalId) {
    $("input:checkbox[name='online_payment_" + channalId + "']").change(function () {
        var act = $(this).prop('checked');
        if (act == false) {
            $('.payment_'+ channalId).each(function () {
                $(this).prop('checked', act);
            });
        }
    });
}

/**
 * 在线支付下面的支付方式选其一，在线支付选择
 * @param channalId int 端口号
 */
function changePayment(channalId) {
    $('.payment_'+ channalId).change(function () {
        var act = $(this).prop('checked');
        if (act == false) {
            $('.payment_'+ channalId).each(function () {
                if ($(this).prop('checked') == true) {
                    act = true;
                }
            });
        }
        $("input:checkbox[name='online_payment_"+ channalId +"']").prop('checked', act);
    });
}

/**
 * 提交时校验
 */
function saveEdit() {
    var isSubmit = 1;
    $.each(channel,function(k,v) {
        //在线支付校验
        if ($("input:checkbox[name='online_payment_" + v + "']").prop('checked') == true) {
            var is_error = 1;
            $('.payment_' + v).each(function () {
                if ($(this).prop('checked') == true) {
                    is_error = 0;
                    return false;
                }
            });
            var msg = '移动端';
            if (v == 95) {
                msg = 'PC';
            } else if (v == 85) {
                msg = '微商城';
            } else if (v == 89) {
                msg = 'IOS';
            } else if (v == 90) {
                msg = 'Android';
            } else if (v == 91) {
                msg = 'WAP';
            }
            if (is_error == 1) {
                isSubmit = 0;
                layer_required('请选择一种 ' + msg + ' 在线支付方式');
                return false;
            }
        }
    });
    if (isSubmit == 1) {
        ajaxSubmit('editPayment');
    }
}