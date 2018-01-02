/*!
 * @author liyuelong1020@gmail.com
 * @date 2017/6/7 007
 * @description 编辑组件
 */


$(function () {

    // 动态编辑字段特效

    var paramWrap = $('#widget-params');

    var paramItem = $('<div class="form-group js-param">' +
        '<label class="col-sm-2 control-label no-padding-right" ><span class="red">*</span>字段名：</label>' +
        '<div class="col-sm-2">' +
        '<input type="text"  placeholder="width" class="col-sm-12" name="field_name[]">' +
        '</div>' +
        '<label class="col-sm-1 control-label no-padding-right" ><span class="red">*</span>描述：</label>' +
        '<div class="col-sm-2">' +
        '<input type="text"  placeholder="图片宽度" class="col-sm-12" name="field_label[]">' +
        '</div>' +
        '<label class="col-sm-1 control-label no-padding-right" ><span class="red">*</span>类型：</label>' +
        '<div class="col-sm-2">' +
        '<select class="form-control js-selector" name="field_type[]">' +
        '<option value="1">文本</option>' +
        '<option value="2">文件</option>' +
        '<option value="3">下拉菜单</option>' +
        '<option value="4">复选框</option>' +
        '<option value="5">文本域</option>' +
        '</select>' +
        '</div>' +
        '<label class="col-sm-1 control-label" >' +
        '<a href="javascript:;" class="js-del-param">' +
        '<i class="red ace-icon fa fa-trash-o bigger-130"></i>' +
        '</a>' +
        '</label>' +
        '<div class="col-sm-12 select-options js-select-options" style="display: none">' +
        '<label class="col-sm-2 control-label no-padding-right" ><span class="red">*</span>下拉选项：</label>' +
        '<div class="col-sm-8">' +
        '<textarea class="form-control" placeholder="下拉菜单选项，多个选项用英文逗号分隔" name="select_value[]"></textarea>' +
        '</div>' +
        '</div>' +
        '</div>');

    var btnAddParam = $('#btn-add-param');


    paramWrap.on('change', '.js-selector', function () {
        if ($(this).val() == 3) {
            $(this).parents('.js-param').find('.js-select-options').show();
        } else {
            $(this).parents('.js-param').find('.js-select-options').hide();
        }
    }).on('click', '.js-del-param', function () {
        $(this).parents('.js-param').remove();
    });

    btnAddParam.on('click', function () {
        paramWrap.append(paramItem.clone());
    });

});

















