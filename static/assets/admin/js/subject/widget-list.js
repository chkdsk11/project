/*!
 * @author liyuelong1020@gmail.com
 * @date 2017/6/23 023
 * @description 组件列表
 */

$(function() {


    var update = function(url, param) {
        $.post(url, param, function (data) {
            if(data.status == 'success'){
                layer_required("更新成功！");
            } else {
                layer_required(''+data.info+'');
				setTimeout("window.parent.location.reload()",2000);
                return false;
            }
        });

    };


    $(document).on('click', 'input.pc-widget-status[type="checkbox"]', function() {

        // pc组件状态修改
        update('/subjectpc/updateWidgetStatus', {
            component_id: $(this).val().trim()
        });

    }).on('click', 'input.mb-widget-status[type="checkbox"]', function() {

        // 移动端组件状态修改
        update('/subjectmobile/updateWidgetStatus', {
            component_id: $(this).val().trim()
        });

    }).on('click', 'input.goods-price-tag[type="checkbox"]', function() {

        // 商品/价格标签状态修改
        update('/subjecttag/updateTagStatus', {
            tag_id: $(this).val().trim()
        });

    });

});
