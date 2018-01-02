/*!
 * @author yuchunfeng
 * @date 2016/6/27 027
 * @description Description
 */

$(document).ready(function(){

    //判断spu名称是否重复
    $('.is_spu_name').blur(function(){
        var name = $(this).val();
        var id = $('#spu_id').val();
        $.ajax({
            type: 'get',
            url: '/spu/exitst?name='+name+'&id='+id,
            cache: false,
            success: function(msg){
                if(msg){
                    layer_required('SPU名称重复');
                }
            },
        });
    });

    //值改变
    $('#spu_name').blur(function(){
        var value = $(this).val();
        if( value.length > 30 ){
            layer_required('SPU通用名最长30个字符');
        }
    });

    //保存spu创建
    var saveSpuBtnLock = false;
    $('#save_spu_btn').click(function() {
        var spu_name = $('#spu_name').val(),           //SPU通用名
            spu_type = $('#spu_type option:selected').val(),                 //SPU品牌
            product_kind = $('#product_kind option:selected').val();           //SPU药物类型
        if( spu_name.length == 0 ) {
            layer_required('SPU通用名不能为空');
        }else if( spu_name.length > 30 ){
            layer_required('SPU通用名最长30个字符');
        }else if( spu_type == '' ) {
            layer_required('品牌不能为空');
        }else if( product_kind == '' ) {
            layer_required('药物类型不能为空');
        }else if( isCategorySelect() ) {
            if (saveSpuBtnLock) {return false;}
            saveSpuBtnLock = true;
            $.ajax({
                type: 'POST',
                url: $("#form_step1").attr('action'),
                data: $("#form_step1").serializeArray(),
                cache: false,
                dataType:'json',
                success: function(data){
                    saveSpuBtnLock = false;
                    okCallback(data);
                    //window.location.reload();
                },
                error: errorCallback
            });
        }
    });

    //添加商品
    var popup_add_product = $('#popup_add_product');     //输入商品id弹窗

    $('#add_product').click(function() {
        popup_add_product.removeClass('hide');

    });

    $(document).on('click','#close_add_product',function(){
        popup_add_product.addClass('hide');
        if(popup_add_product_html != undefined && popup_add_product_html != ''){
            $('#popup_add_product .popup-content').html(popup_add_product_html);
        }
    });

    $(document).on('click','#add_product_sure',function(){
        var brand = $('#spu_type').val();
        var id = $('#product_id').val();
        if ( id!= ''&&!regNumber.test(id)) {
            layer_required('请输入正确的商品ID');
            return;
        }
        var spu_id = $('#spu_id').val();
        var shop_id = $('#spu_supplier').val();
        var erp_id = $('#product_code').val();

        if (erp_id == '') {
            layer_required('请输入正确的erp编码');
            return;
        }
        if(spu_id != '' && erp_id != ''){
            $.ajax({
                type: 'get',
                url: '/sku/getSkuInfo?id='+id+'&spu_id='+spu_id+'&erp_id='+erp_id+'&shop_id='+shop_id+'&brand_id='+brand,
                cache: false,
                dataType:'json',
                success: function(msg){
                    //console.log(msg.info != null);
                    //console.log(msg.res != 'error');
                    //console.log(msg.status != 'error');
                    //console.log(msg.res != 'error_info');
                    //return;
                    if( msg.info != null && msg.res != 'error' && msg.status != 'error' && msg.res != 'error_info'){
                        document.location.reload();return;
                        popup_add_product.addClass('hide');
                        var str= '' ;
                        str += '<tr id="product_list_'+ msg['id'] +'"> ' +
                            '<td class="center"> ' +
                            '<input type="text" name="sku_id" value="'+msg['id']+'" readonly /></td> ';
                        str +=  '<td class="center"> ' +
                            '<input type="text" class="rule_value_1" name="rule_value" value=""  ';
                        if(!msg['productRule']['name_id']){
                            str += 'readonly';
                        }
                        str += '/> <input type="hidden" name="rule_pid" value="';
                        if(msg['productRule']['name_id']){
                            str += msg['productRule']['name_id']['id'];
                        }
                        str += '"></td> <td class="center"> ' +
                            '<input type="text" class="rule_value_2" name="rule_value" value=""  ';
                        if(!msg['productRule']['name_id2']){
                            str += 'readonly';
                        }
                        str += '/><input type="hidden" name="rule_pid" value="';
                        if(msg['productRule']['name_id2']){
                            str += msg['productRule']['name_id2']['id'];
                        }
                        str += '"></td><td class="center"> ' +
                            '<input type="text" class="rule_value_3" name="rule_value" value=""  ';
                        if(!msg['productRule']['name_id3']){
                            str += 'readonly';
                        }
                        str += '/><input type="hidden" name="rule_pid" value="';
                        if(msg['productRule']['name_id3']){
                            str += msg['productRule']['name_id3']['id'];
                        }
                        str += '"></td><td class="center"> ' +
                            '<input type="text" name="goods_price" class="product-price ';
                        if( msg['is_unified_price'] == 1 ){
                            str += 'hide';
                        }
                        str += '" value="'+msg['goods_price']+'"/> ' +
                            '<div class="price-all ';
                        if( msg['is_unified_price'] != 1 ){
                            str += 'hide';
                        }
                        if(!msg['info']){
                            msg['info'] = new Array;
                            msg['info']['goods_price_pc'] = '0.00';
                            msg['info']['goods_price_app'] = '0.00';
                            msg['info']['goods_price_wap'] = '0.00';
                            msg['info']['market_price_pc'] = '0.00';
                            msg['info']['market_price_app'] = '0.00';
                            msg['info']['market_price_wap'] = '0.00';
                            msg['info']['goods_price_wechat'] = '0.00';
                            msg['info']['market_price_wechat'] = '0.00';
                            msg['info']['virtual_stock_default'] = 0;
                            msg['info']['virtual_stock_pc'] = 0;
                            msg['info']['virtual_stock_app'] = 0;
                            msg['info']['virtual_stock_wap'] = 0;
                            msg['info']['virtual_stock_wechat'] = 0;
                            msg['info']['whether_is_gift'] = 0;
                            msg['info']['gift_pc'] = 0;
                            msg['info']['gift_app'] = 0;
                            msg['info']['gift_wap'] = 0;
                            msg['info']['gift_wechat'] = 0;
                        }
                        str += '"> ' +
                            '<div>P C:  <input type="text" name="goods_price_pc" value="'+msg['info']['goods_price_pc']+'" /> </div> ' +
                            '<div>APP: <input type="text" name="goods_price_app" value="'+msg['info']['goods_price_app']+'" /> </div> ' +
                            '<div>WAP: <input type="text" name="goods_price_wap" value="'+msg['info']['goods_price_wap']+'" /> </div> ' +
                            '<div>微商城: <input type="text" name="goods_price_wechat" value="'+msg['info']['goods_price_wechat']+'" /> </div> ' +
                            '</div>' +
                            '<br/> <button type="button" class="set_price">';
                        if( msg['is_unified_price'] == 1 ){
                            str += '设置默认';
                        }else{
                            str += '设置不同端';
                        }
                        str += '</button>' +
                            '<input type="hidden" name="is_unified_price" class="is_unified_price"  value="'+msg.is_unified_price+'"/>' +    // 0：默认  1：设置不同端
                            '</td>' +
                            '<td class="center">' +
                            '<input type="text" name="market_price" class="product-price ';
                        if( msg['is_unified_price'] == 1 ){
                            str += 'hide';
                        }
                        str += '" value="'+msg['market_price']+'" />' +
                            '<div class="price-all ';
                        if( msg['is_unified_price'] != 1 ){
                            str += 'hide';
                        }
                        str += '">' +
                            '<div>P C: <input type="text" name="market_price_pc" value="'+msg['info']['market_price_pc']+'" /> </div>' +
                            '<div>APP: <input type="text" name="market_price_app" value="'+msg['info']['market_price_app']+'" /> </div>' +
                            '<div>WAP: <input type="text" name="market_price_wap" value="'+msg['info']['market_price_wap']+'" /> </div>' +
                            '<div>微商城: <input type="text" name="market_price_wechat" value="'+msg['info']['market_price_wechat']+'" /> </div>' +
                            '</div>' +
                            '</td>' +
                            '<td class="center">' +
                            '<select id="is_gift_change" name="is_gift" class="is_gift';
                        if(msg['info']['whether_is_gift'] == 1){
                            str += ' hide';
                        }
                        str += '"><option value="0" ';
                            if( msg['product_type'] == 0 ){
                                str += 'selected';
                            }
                        str += '>否</option>' +
                            '<option value="1"';
                        if( msg['product_type'] == 1 ){
                            str += 'selected';
                        }
                        str += '>普通赠品</option>' +
                            '<option value="2"';
                        if( msg['info']['whether_is_gift'] == 2 ){
                            str += 'selected';
                        }
                        str += '>附属赠品</option>' +
                            '</select>';
                        str += '<div class="gift-all ';
                        if(msg['info']['whether_is_gift'] != 1){
                            str += 'hide';
                        }
                        str += '"><div>P C:  <select name="gift_pc"><option  value="0" ';
                        if( msg['info']['gift_pc'] == 0 ){
                            str += 'selected';
                        }
                        str += '>否</option><option value="1" ';
                        if( msg['info']['gift_pc'] == 1 ){
                            str += 'selected';
                        }
                        str += '>普通赠品</option></select></div><div>APP: <select name="gift_app"><option  value="0" ';
                        if( msg['info']['gift_app'] == 0 ){
                            str += 'selected';
                        }
                        str += '>否</option><option value="1" ';
                        if( msg['info']['gift_app'] == 1 ){
                            str += 'selected';
                        }
                        str += '>普通赠品</option></select></div><div>WAP: <select name="gift_wap"><option  value="0" ';
                        if( msg['info']['gift_wap'] == 0 ){
                            str += 'selected';
                        }
                        str += '>否</option><option value="1" ';
                        if( msg['info']['gift_wap'] == 1 ){
                            str += 'selected';
                        }
                        str += '>普通赠品</option></select></div><div>微商城: <select name="gift_wechat"><option  value="0" ';
                        if( msg['info']['gift_wechat'] == 0 ){
                            str += 'selected';
                        }
                        str += '>否</option><option value="1" ';
                        if( msg['info']['gift_wechat'] == 1 ){
                            str += 'selected';
                        }
                        str += '>普通赠品</option></select></div></div>';
                        str += '<br/> <button type="button" class="set_whether_is_gift';
                        if(msg['info']['whether_is_gift'] == 2){
                            str += ' hide';
                        }
                        str += '">';
                        if(msg['info']['whether_is_gift'] == 1){
                            str += '统一设置';
                        }else{
                            str += '设置不同端';
                        }
                        str += '</button><input type="hidden" name="set_whether_is_gift" class="set_whether_is_gift" value="';
                        str += (msg['info']['whether_is_gift']==1)?msg['info']['whether_is_gift']:0;
                        str +=  '"></td><td class="center" data-id="'+msg['id']+'">' +
                            '<div class="product_real_stock"> ';
                        if( msg['is_use_stock'] == 1 ){
                            str += '真实库存：'+msg['v_stock']+'件';
                        }

                        if( msg['is_use_stock'] == 2 ){
                            str += '公共虚拟库存：'+msg['info']['virtual_stock_default']+'件';;
                        }
                        if( msg['is_use_stock'] == 3 ){
                        str += '<div>PC虚拟库存: '+msg['info']['virtual_stock_pc']+'件</div>'+
                            '<div>APP虚拟库存: '+msg['info']['virtual_stock_app']+'件</div>'+
                            '<div>WAP虚拟库存: '+msg['info']['virtual_stock_wap']+'件 </div>'+
                            '<div>微商城虚拟库存: '+msg['info']['virtual_stock_wechat']+'件 </div>';
                        }
                        str += '</div><br/><a href="javascript:;" class="modify-stock">修改库存</a></td>' ;

                        str +=  '<td class="center"><select name="is_lock"><option  value="0" ';
                        if(msg['is_lock'] != 1){
                            str += 'selected';
                        }
                        str += ' >否</option><option value="1" ';
                        if(msg['is_lock'] == 1){
                            str += 'selected';
                        }
                        str += '>是</option></select> </td><td class="center"><button type="button" class="product-list-delect"> 删除</button><br>' +
                            '<input type="hidden" name="spu_id" value="'+spu_id+'">' +
                            '<button type="button" class="save_msg_ajax" data-id="'+msg['id']+'"> 保存</button>' +
                            '</td> ' +
                            '</tr>' ;
                        $('#product_table tbody').append(str);
                        $('#product_id').val('');
                        /************************追加属性**************************/
                        var setimg_ary = msg['img'] ;
                        var img_str = '',
                            godds_color = $('#product_list_'+ id +' .rule_value_1').val() ,
                            goods_size = $('#product_list_'+ id +'  .rule_value_2').val() ;
                        godds_color = '';
                        goods_size = '';
                        // 添加商品后，追加商品的图片设置
                        img_str += '<div class="row" id="setimg_'+ id +'" >'+
                            '<div class="col-sm-2 product-setimg-left setimg-goods-name"><span class="span_red">*</span>'+ id +'&nbsp;'+ godds_color +'&nbsp;'+ goods_size +'<br/><span class="span_prompt">至少上传一张图片</span></div>'+
                            '<div class="col-sm-10 product_setimg"><div class="img-main" ><img onclick="fileSelect('+ id +');" id="img-main-'+ id +'" src="'+ msg.small_path +'"  title="商品主图,点击可重新上传"> <div>主图</div></div><ul class="list list-'+ id +'" data-id="'+ id +'">';

                        if(setimg_ary && setimg_ary.length){
                            setimg_ary.forEach(function(img) {
                                img_str += '<li data-id="'+img['id'] +'">'+
                                    '<img src="'+img['sku_image']+'" >' +
                                    '<a href="javascript:;" class="close-btn" data-id="'+ img['id']+'">&times;</a>'+
                                    '</li>';
                            });
                        }
                        img_str += '</ul><a class="add_img_btn add-btn">'+
                            '+'+
                            '<input type="file" class="upload-img move_image"  id="file_img_'+ id+'"  name="file_img_'+ id +'[]" multiple /> '+
                            '</a>' +
                            '</div>' +
                            '</div>';
                        $('.set-img').append(img_str);
                        $('.list-'+ id ).dragsort({ dragSelector: "img", dragBetween: false,  dragEnd : saveOrder, placeHolderTemplate: "<li class='placeHolder'><div></div></li>" });

                        // 添加商品后，追加商品的参数
                        var goods_msg_str = '<a id="msg_'+ id +'" href="javascript:;">'+  id + '&nbsp;'+ godds_color +
                            '&nbsp;'+ goods_size +
                            '</a>' ;
                        $('.product-property').append(goods_msg_str) ;

                        //追加说明书
                        var goods_msg_instruction = '<a id="instruction_'+ id +'" data-id="'+ id +'" href="javascript:;">'+  id + '&nbsp;'+ godds_color +
                            '&nbsp;'+ goods_size +
                            '</a>' ;
                        $('.product-instruction-nav').append(goods_msg_instruction) ;

                        // 添加商品后，追加商品的模板
                        var goods_model_str = '<a id="model_'+ id +'" href="javascript:;">'+  id +
                            '&nbsp;'+ godds_color +
                            '&nbsp;'+ goods_size +
                            '</a>' ;
                        $('.goods-nav').append(goods_model_str) ;

                        // 添加商品后，追加商品的上下架时间
                        if($("input:radio[name='goods-shelves']:checked").val()=='2'){
                            var goods_shelves1 = '<div id="goods_check_1_'+ msg['id']+'" class="checkbox-list">' +
                                ' <label> <span class="lbl">'+ msg['id']+ msg['rule_value_id']+'</span> ' +
                                '<select class="select-sale-pc-'+ msg['id']+'" name="is_on_sale[]">' +
                                '<option value="1"' ;
                            if( msg['is_on_sale'] == 1 ){
                                goods_shelves1 += 'selected';
                            }
                            goods_shelves1 += '>pc 上架</option>' +
                                ' <option value="0" ';
                            if( msg['is_on_sale'] != 1 ){
                                goods_shelves1 += 'selected';
                            }
                            goods_shelves1 += '>pc 下架</option>' +
                                ' </select><select class="select-sale-app-'+ msg['id']+'" name="sale_timing_app[]"> ' +
                                '<option value="1" ' ;
                            if( msg['sale_timing_app'] == 1 ){
                                goods_shelves1 += 'selected';
                            }
                            goods_shelves1 += '>app 上架</option>' +
                                ' <option value="0" ';
                            if( msg['sale_timing_app'] != 1 ){
                                goods_shelves1 += 'selected';
                            }
                            goods_shelves1 += '>app 下架</option>' +
                                ' </select><select class="select-sale-wap-'+ msg['id']+'" name="sale_timing_wap[]"> ' +
                                '<option value="1" ' ;
                            if( msg['sale_timing_wap'] == 1 ){
                                goods_shelves1 += 'selected';
                            }
                            goods_shelves1 += '>wap 上架</option>' +
                                ' <option value="0" ';
                            if( msg['sale_timing_wap'] != 1 ){
                                goods_shelves1 += 'selected';
                            }
                            goods_shelves1 += '>wap 下架</option>' +
                                ' </select><select class="select-sale-wechat-'+ msg['id']+'" name="sale_timing_wechat[]"> ' +
                                '<option value="1" ' ;
                            if( msg['sale_timing_wechat'] == 1 ){
                                goods_shelves1 += 'selected';
                            }
                            goods_shelves1 += '>微商城 上架</option>' +
                                ' <option value="0" ';
                            if( msg['sale_timing_wechat'] != 1 ){
                                goods_shelves1 += 'selected';
                            }
                            goods_shelves1 += '>微商城 下架</option>' +
                                ' </select></label></div><input type="hidden" name="sku_id[]" value="'+ msg['id']+'">';
                        }else {
                            var goods_shelves1 = '<div id="goods_check_1_'+ msg['id']+'" class="checkbox-list">' +
                                ' <label> <span class="lbl">'+ msg['id']+ msg['rule_value_id']+'</span> ' +
                                '<select class="select-sale-pc-'+ msg['id']+'" name="is_on_sale[]">' +
                                '<option value="1"' ;
                            if( msg['is_on_sale'] == 1 ){
                                goods_shelves1 += 'selected';
                            }
                            goods_shelves1 += '>pc 上架</option>' +
                                ' <option value="0" ';
                            if( msg['is_on_sale'] != 1 ){
                                goods_shelves1 += 'selected';
                            }
                            goods_shelves1 += '>pc 下架</option>' +
                                ' </select><select class="select-sale-app-'+ msg['id']+'" name="sale_timing_app[]"> ' +
                                '<option value="1" ' ;
                            if( msg['sale_timing_app'] == 1 ){
                                goods_shelves1 += 'selected';
                            }
                            goods_shelves1 += '>app 上架</option>' +
                                ' <option value="0" ';
                            if( msg['sale_timing_app'] != 1 ){
                                goods_shelves1 += 'selected';
                            }
                            goods_shelves1 += '>app 下架</option>' +
                                ' </select><select class="select-sale-wap-'+ msg['id']+'" name="sale_timing_wap[]"> ' +
                                '<option value="1" ' ;
                            if( msg['sale_timing_wap'] == 1 ){
                                goods_shelves1 += 'selected';
                            }
                            goods_shelves1 += '>wap 上架</option>' +
                                ' <option value="0" ';
                            if( msg['sale_timing_wap'] != 1 ){
                                goods_shelves1 += 'selected';
                            }
                            goods_shelves1 += '>wap 下架</option>' +
                                ' </select><select class="select-sale-wechat-'+ msg['id']+'" name="sale_timing_wechat[]"> ' +
                                '<option value="1" ' ;
                            if( msg['sale_timing_wechat'] == 1 ){
                                goods_shelves1 += 'selected';
                            }
                            goods_shelves1 += '>微商城 上架</option>' +
                                ' <option value="0" ';
                            if( msg['sale_timing_wechat'] != 1 ){
                                goods_shelves1 += 'selected';
                            }
                            goods_shelves1 += '>微商城 下架</option>' +
                                ' </select></label></div><input type="hidden" name="sku_id[]" value="'+ msg['id']+'">';
                        }

                        var goods_shelves2 = '<div class="checkbox-list" id="goods_check_2_'+ id +'">' +
                            '<label><input';
                            if(msg['is_recommend'] == 1){
                                goods_shelves2 += ' checked';
                            }
                            goods_shelves2 += ' name="goods_recommend[]" type="checkbox" class="ace" value="'+ id  +'" >'+
                            '<span class="lbl">'+ id +'&nbsp;'+ godds_color +'&nbsp;'+ goods_size +
                            '</span>' +
                            '</label>' +
                            '</div>';

                        var goods_shelves3 = '<div class="checkbox-list" id="goods_check_3_'+ id +'">' +
                            '<label><input';
                            if(msg['is_hot'] == 1){
                                goods_shelves3 += ' checked';
                            }
                        goods_shelves3 += ' name="goods_hot[]" type="checkbox" class="ace" value="'+ id  +'" >'+
                            '<span class="lbl">'+ id +'&nbsp;'+ godds_color +'&nbsp;'+ goods_size +
                            '</span>' +
                            '</label>' +
                            '</div>';
                        $('.chose-goods-shelves1').append(goods_shelves1) ;
                        $('.chose-goods-shelves2').append(goods_shelves2) ;
                        $('.chose-goods-shelves3').append(goods_shelves3) ;
                    }else{
                        if( msg.res == 'error_info' ){
                            popup_add_product_html = $('#popup_add_product .popup-content').html();
                            $('#popup_add_product .popup-content').empty();
                            var html = '<div class="pro-title"> <a id="close_add_product" href="javascript:;">×</a></div>';
                            html += 'ID:'+id+'的商品已在其他spu中，请确定是否更换？<br/><br/><button id="popup_add_product_is_yes" style="margin-right: 10px;" data-id="'+id+'" data-erp="'+erp_id+'" type="button">是</button><button id="popup_add_product_is_not" type="button">否</button>';
                            $('#popup_add_product .popup-content').append(html);
                        }else if(msg.res == 'error' || msg.status == 'error'){
                            layer_required(msg.info);
                        }else if(msg.info == null){
                            layer_required('未查到该商品');
                        }else{
                            layer_required('未知错误');
                        }
                    }
                },
            });
        } else {
            layer_required('请输入商品id');
        }

    });
    //不切换商品时执行
    $(document).on('click','#popup_add_product_is_not',function(){
        $('#popup_add_product .popup-content').html(popup_add_product_html);
    });
    //切换时执行
    $(document).on('click','#popup_add_product_is_yes',function(){
        var erp_id = $(this).attr('data-erp');
        var spu_id = $('#spu_id').val();
        $.ajax({
            type: 'get',
            url: '/sku/getSkuInfo?erp_id='+erp_id+'&spu_id='+spu_id+'&force=1',
            cache: false,
            dataType:'json',
            success: function(msg){
                if( msg != null && msg.res != 'error' && msg.status != 'error' ){
                    document.location.reload();return;
                    var tmp_id = $("#product_list_"+id).html();
                    if( tmp_id != undefined){
                        $('#popup_add_product .popup-content').html(popup_add_product_html);
                        $('#popup_add_product').addClass('hide');
                        return;
                    }
                    var str= '' ;
                    str += '<tr id="product_list_'+ msg['id'] +'"> ' +
                        '<td class="center"> ' +
                        '<input type="text" name="sku_id" value="'+msg['id']+'" readonly /></td> ';
                    str +=  '<td class="center"> ' +
                        '<input type="text" class="rule_value_1" name="rule_value" value=""  ';
                    if(!msg['productRule']['name_id']){
                        str += 'readonly';
                    }
                    str += '/> <input type="hidden" name="rule_pid" value="';
                    if(msg['productRule']['name_id']){
                        str += msg['productRule']['name_id']['id'];
                    }
                    str += '"></td> <td class="center"> ' +
                        '<input type="text" class="rule_value_2" name="rule_value" value=""  ';
                    if(!msg['productRule']['name_id2']){
                        str += 'readonly';
                    }
                    str += '/><input type="hidden" name="rule_pid" value="';
                    if(msg['productRule']['name_id2']){
                        str += msg['productRule']['name_id2']['id'];
                    }
                    str += '"></td><td class="center"> ' +
                        '<input type="text" class="rule_value_3" name="rule_value" value=""  ';
                    if(!msg['productRule']['name_id3']){
                        str += 'readonly';
                    }
                    str += '/><input type="hidden" name="rule_pid" value="';
                    if(msg['productRule']['name_id3']){
                        str += msg['productRule']['name_id3']['id'];
                    }
                    str += '"></td><td class="center"> ' +
                        '<input type="text" name="goods_price" class="product-price ';
                    if( msg['is_unified_price'] == 1 ){
                        str += 'hide';
                    }
                    str += '" value="'+msg['goods_price']+'"/> ' +
                        '<div class="price-all ';
                    if( msg['is_unified_price'] != 1 ){
                        str += 'hide';
                    }
                    if(!msg['info']){
                        msg['info'] = new Array;
                        msg['info']['goods_price_pc'] = '0.00';
                        msg['info']['goods_price_app'] = '0.00';
                        msg['info']['goods_price_wap'] = '0.00';
                        msg['info']['goods_price_wechat'] = '0.00';
                        msg['info']['market_price_pc'] = '0.00';
                        msg['info']['market_price_app'] = '0.00';
                        msg['info']['market_price_wap'] = '0.00';
                        msg['info']['market_price_wechat'] = '0.00';
                        msg['info']['virtual_stock_default'] = 0;
                        msg['info']['virtual_stock_pc'] = 0;
                        msg['info']['virtual_stock_app'] = 0;
                        msg['info']['virtual_stock_wap'] = 0;
                        msg['info']['virtual_stock_wechat'] = 0;
                        msg['info']['whether_is_gift'] = 0;
                        msg['info']['gift_pc'] = 0;
                        msg['info']['gift_app'] = 0;
                        msg['info']['gift_wap'] = 0;
                        msg['info']['gift_wechat'] = 0;
                    }
                    str += '"> ' +
                        '<div>P C:  <input type="text" name="goods_price_pc" value="'+msg['info']['goods_price_pc']+'" /> </div> ' +
                        '<div>APP: <input type="text" name="goods_price_app" value="'+msg['info']['goods_price_app']+'" /> </div> ' +
                        '<div>WAP: <input type="text" name="goods_price_wap" value="'+msg['info']['goods_price_wap']+'" /> </div> ' +
                        '<div>微商城: <input type="text" name="goods_price_wechat" value="'+msg['info']['goods_price_wechat']+'" /> </div> ' +
                        '</div>' +
                        '<br/> <button type="button" class="set_price">';
                    if( msg['is_unified_price'] == 1 ){
                        str += '设置默认';
                    }else{
                        str += '设置不同端';
                    }
                    str += '</button>' +
                        '<input type="hidden" name="is_unified_price" class="is_unified_price"  value="'+msg.is_unified_price+'"/>' +    // 0：默认  1：设置不同端
                        '</td>' +
                        '<td class="center">' +
                        '<input type="text" name="market_price" class="product-price ';
                    if( msg['is_unified_price'] == 1 ){
                        str += 'hide';
                    }
                    str += '" value="'+msg['market_price']+'" />' +
                        '<div class="price-all ';
                    if( msg['is_unified_price'] != 1 ){
                        str += 'hide';
                    }
                    str += '">' +
                        '<div>P C: <input type="text" name="market_price_pc" value="'+msg['info']['market_price_pc']+'" /> </div>' +
                        '<div>APP: <input type="text" name="market_price_app" value="'+msg['info']['market_price_app']+'" /> </div>' +
                        '<div>WAP: <input type="text" name="market_price_wap" value="'+msg['info']['market_price_wap']+'" /> </div>' +
                        '<div>微商城: <input type="text" name="market_price_wechat" value="'+msg['info']['market_price_wechat']+'" /> </div>' +
                        '</div>' +
                        '</td>' +
                        '<td class="center">' +
                        '<select id="is_gift_change" name="is_gift" class="is_gift';
                    if(msg['info']['whether_is_gift'] == 1){
                        str += ' hide';
                    }
                    str += '"><option value="0" ';
                    if( msg['product_type'] == 0 ){
                        str += 'selected';
                    }
                    str += '>否</option>' +
                        '<option value="1"';
                    if( msg['product_type'] == 1 ){
                        str += 'selected';
                    }
                    str += '>普通赠品</option>' +
                        '<option value="2"';
                    if( msg['info']['whether_is_gift'] == 2 ){
                        str += 'selected';
                    }
                    str += '>附属赠品</option>' +
                        '</select>';
                    str += '<div class="gift-all ';
                    if(msg['info']['whether_is_gift'] != 1){
                        str += 'hide';
                    }
                    str += '"><div>P C:  <select name="gift_pc"><option  value="0" ';
                    if( msg['info']['gift_pc'] == 0 ){
                        str += 'selected';
                    }
                    str += '>否</option><option value="1" ';
                    if( msg['info']['gift_pc'] == 1 ){
                        str += 'selected';
                    }
                    str += '>普通赠品</option></select></div><div>APP: <select name="gift_app"><option  value="0" ';
                    if( msg['info']['gift_app'] == 0 ){
                        str += 'selected';
                    }
                    str += '>否</option><option value="1" ';
                    if( msg['info']['gift_app'] == 1 ){
                        str += 'selected';
                    }
                    str += '>普通赠品</option></select></div><div>WAP: <select name="gift_wap"><option  value="0" ';
                    if( msg['info']['gift_wap'] == 0 ){
                        str += 'selected';
                    }
                    str += '>否</option><option value="1" ';
                    if( msg['info']['gift_wap'] == 1 ){
                        str += 'selected';
                    }
                    str += '>普通赠品</option></select></div><div>微商城: <select name="gift_wechat"><option  value="0" ';
                    if( msg['info']['gift_wechat'] == 0 ){
                        str += 'selected';
                    }
                    str += '>否</option><option value="1" ';
                    if( msg['info']['gift_wechat'] == 1 ){
                        str += 'selected';
                    }
                    str += '>普通赠品</option></select></div></div>';
                    str += '<br/> <button type="button" class="set_whether_is_gift';
                    if(msg['info']['whether_is_gift'] == 2){
                        str += ' hide';
                    }
                    str += '">';
                    if(msg['info']['whether_is_gift'] == 1){
                        str += '统一设置';
                    }else{
                        str += '设置不同端';
                    }
                    str += '</button><input type="hidden" name="set_whether_is_gift" class="set_whether_is_gift" value="';
                    str += (msg['info']['whether_is_gift']==1)?msg['info']['whether_is_gift']:0;
                    str +=  '"></td><td class="center" data-id="'+msg['id']+'">' +
                        '<div class="product_real_stock"> ';
                    if( msg['is_use_stock'] == 1 ){
                        str += '真实库存：'+msg['v_stock']+'件';
                    }

                    if( msg['is_use_stock'] == 2 ){
                        str += '公共虚拟库存：'+msg['info']['virtual_stock_default']+'件';;
                    }
                    if( msg['is_use_stock'] == 3 ){
                        str += '<div>PC虚拟库存: '+msg['info']['virtual_stock_pc']+'件</div>'+
                            '<div>APP虚拟库存: '+msg['info']['virtual_stock_app']+'件</div>'+
                            '<div>WAP虚拟库存: '+msg['info']['virtual_stock_wap']+'件 </div>'+
                            '<div>微商城虚拟库存: '+msg['info']['virtual_stock_wechat']+'件 </div>';
                    }
                    str += '</div><br/><a href="javascript:;" class="modify-stock">修改库存</a></td>';
                    str +=  '<td class="center"><select name="is_lock"><option  value="0" ';
                    if(msg['is_lock'] != 1){
                        str += 'selected';
                    }
                    str += ' >否</option><option value="1" ';
                    if(msg['is_lock'] == 1){
                        str += 'selected';
                    }
                    str += '>是</option></select> </td><td class="center"><button type="button" class="product-list-delect"> 删除</button><br>' +
                        '<input type="hidden" name="spu_id" value="'+spu_id+'">' +
                        '<button type="button" class="save_msg_ajax" data-id="'+msg['id']+'"> 保存</button>' +
                        '</td> ' +
                        '</tr>' ;
                    $('#product_table tbody').append(str);
                    $('#product_id').val('');
                    /************************追加属性**************************/
                    var setimg_ary = msg['img'] ;
                    var img_str = '',
                        godds_color = $('#product_list_'+ id +' .rule_value_1').val() ,
                        goods_size = $('#product_list_'+ id +'  .rule_value_2').val() ;
                    godds_color = '';
                    goods_size = '';
                    // 添加商品后，追加商品的图片设置
                    img_str += '<div class="row" id="setimg_'+ id +'" >'+
                        '<div class="col-sm-2 product-setimg-left setimg-goods-name"><span class="span_red">*</span>'+ id +'&nbsp;'+ godds_color +'&nbsp;'+ goods_size +'<br/><span class="span_prompt">至少上传一张图片</span></div>'+
                        '<div class="col-sm-10 product_setimg"><div class="img-main" ><img onclick="fileSelect('+ id +');" id="img-main-'+ id +'" src="'+ msg.small_path +'"  title="商品主图,点击可重新上传"> <div>主图</div></div><ul class="list list-'+ id +'" data-id="'+ id +'">';

                    if(setimg_ary && setimg_ary.length){
                        setimg_ary.forEach(function(img) {
                            img_str += '<li data-id="'+img['id'] +'">'+
                                '<img src="'+img['sku_image']+'" >' +
                                '<a href="javascript:;" class="close-btn" data-id="'+ img['id']+'">&times;</a>'+
                                '</li>';
                        });
                    }
                    img_str += '</ul><a class="add_img_btn add-btn">'+
                        '+'+
                        '<input type="file" class="upload-img move_image"  id="file_img_'+ id+'"  name="file_img_'+ id +'[]" multiple /> '+
                        '</a>' +
                        '</div>' +
                        '</div>';
                    $('.set-img').append(img_str);
                    $('.list-'+ id ).dragsort({ dragSelector: "img", dragBetween: false,  dragEnd : saveOrder, placeHolderTemplate: "<li class='placeHolder'><div></div></li>" });

                    // 添加商品后，追加商品的参数
                    var goods_msg_str = '<a id="msg_'+ id +'" href="javascript:;">'+  id + '&nbsp;'+ godds_color +
                        '&nbsp;'+ goods_size +
                        '</a>' ;
                    $('.product-property').append(goods_msg_str) ;

                    //追加说明书
                    var goods_msg_instruction = '<a id="instruction_'+ id +'" data-id="'+ id +'" href="javascript:;">'+  id + '&nbsp;'+ godds_color +
                        '&nbsp;'+ goods_size +
                        '</a>' ;
                    $('.product-instruction-nav').append(goods_msg_instruction) ;

                    // 添加商品后，追加商品的模板
                    var goods_model_str = '<a id="model_'+ id +'" href="javascript:;">'+  id +
                        '&nbsp;'+ godds_color +
                        '&nbsp;'+ goods_size +
                        '</a>' ;
                    $('.goods-nav').append(goods_model_str) ;

                    // 添加商品后，追加商品的上下架时间
                    if($("input:radio[name='goods-shelves']:checked").val()=='2'){
                        var goods_shelves1 = '<div id="goods_check_1_'+ msg['id']+'" class="checkbox-list">' +
                            ' <label> <span class="lbl">'+ msg['id']+ msg['rule_value_id']+'</span> ' +
                            '<select class="select-sale-pc-'+ msg['id']+'" name="is_on_sale[]">' +
                            '<option value="1"' ;
                        if( msg['is_on_sale'] == 1 ){
                            goods_shelves1 += 'selected';
                        }
                        goods_shelves1 += '>pc 上架</option>' +
                            ' <option value="0" ';
                        if( msg['is_on_sale'] != 1 ){
                            goods_shelves1 += 'selected';
                        }
                        goods_shelves1 += '>pc 下架</option>' +
                            ' </select><select class="select-sale-app-'+ msg['id']+'" name="sale_timing_app[]"> ' +
                            '<option value="1" ' ;
                        if( msg['sale_timing_app'] == 1 ){
                            goods_shelves1 += 'selected';
                        }
                        goods_shelves1 += '>app 上架</option>' +
                            ' <option value="0" ';
                        if( msg['sale_timing_app'] != 1 ){
                            goods_shelves1 += 'selected';
                        }
                        goods_shelves1 += '>app 下架</option>' +
                            ' </select><select class="select-sale-wap-'+ msg['id']+'" name="sale_timing_wap[]"> ' +
                            '<option value="1" ' ;
                        if( msg['sale_timing_wap'] == 1 ){
                            goods_shelves1 += 'selected';
                        }
                        goods_shelves1 += '>wap 上架</option>' +
                            ' <option value="0" ';
                        if( msg['sale_timing_wap'] != 1 ){
                            goods_shelves1 += 'selected';
                        }
                        goods_shelves1 += '>wap 下架</option>' +
                            ' </select><select class="select-sale-wechat-'+ msg['id']+'" name="sale_timing_wechat[]"> ' +
                            '<option value="1" ' ;
                        if( msg['sale_timing_wechat'] == 1 ){
                            goods_shelves1 += 'selected';
                        }
                        goods_shelves1 += '>微商城 上架</option>' +
                            ' <option value="0" ';
                        if( msg['sale_timing_wechat'] != 1 ){
                            goods_shelves1 += 'selected';
                        }
                        goods_shelves1 += '>微商城 下架</option>' +
                            ' </select></label></div><input type="hidden" name="sku_id[]" value="'+ msg['id']+'">';
                    }else {
                        var goods_shelves1 = '<div id="goods_check_1_'+ msg['id']+'" class="checkbox-list">' +
                            ' <label> <span class="lbl">'+ msg['id']+ msg['rule_value_id']+'</span> ' +
                            '<select class="select-sale-pc-'+ msg['id']+'" name="is_on_sale[]">' +
                            '<option value="1"' ;
                        if( msg['is_on_sale'] == 1 ){
                            goods_shelves1 += 'selected';
                        }
                        goods_shelves1 += '>pc 上架</option>' +
                            ' <option value="0" ';
                        if( msg['is_on_sale'] != 1 ){
                            goods_shelves1 += 'selected';
                        }
                        goods_shelves1 += '>pc 下架</option>' +
                            ' </select><select class="select-sale-app-'+ msg['id']+'" name="sale_timing_app[]"> ' +
                            '<option value="1" ' ;
                        if( msg['sale_timing_app'] == 1 ){
                            goods_shelves1 += 'selected';
                        }
                        goods_shelves1 += '>app 上架</option>' +
                            ' <option value="0" ';
                        if( msg['sale_timing_app'] != 1 ){
                            goods_shelves1 += 'selected';
                        }
                        goods_shelves1 += '>app 下架</option>' +
                            ' </select><select class="select-sale-wap-'+ msg['id']+'" name="sale_timing_wap[]"> ' +
                            '<option value="1" ' ;
                        if( msg['sale_timing_wap'] == 1 ){
                            goods_shelves1 += 'selected';
                        }
                        goods_shelves1 += '>wap 上架</option>' +
                            ' <option value="0" ';
                        if( msg['sale_timing_wap'] != 1 ){
                            goods_shelves1 += 'selected';
                        }
                        goods_shelves1 += '>wap 下架</option>' +
                            ' </select><select class="select-sale-wechat-'+ msg['id']+'" name="sale_timing_wechat[]"> ' +
                            '<option value="1" ' ;
                        if( msg['sale_timing_wechat'] == 1 ){
                            goods_shelves1 += 'selected';
                        }
                        goods_shelves1 += '>微商城 上架</option>' +
                            ' <option value="0" ';
                        if( msg['sale_timing_wechat'] != 1 ){
                            goods_shelves1 += 'selected';
                        }
                        goods_shelves1 += '>微商城 下架</option>' +
                            ' </select></label></div><input type="hidden" name="sku_id[]" value="'+ msg['id']+'">';
                    }

                    var goods_shelves2 = '<div class="checkbox-list" id="goods_check_2_'+ id +'">' +
                        '<label><input';
                        if(msg['is_recommend'] == 1){
                            goods_shelves2 += ' checked';
                        }
                        goods_shelves2 += ' name="goods_recommend[]" type="checkbox" class="ace" value="'+ id  +'" >'+
                        '<span class="lbl">'+ id +'&nbsp;'+ godds_color +'&nbsp;'+ goods_size +
                        '</span>' +
                        '</label>' +
                        '</div>';

                    var goods_shelves3 = '<div class="checkbox-list" id="goods_check_3_'+ id +'">' +
                        '<label><input';
                        if(msg['is_hot'] == 1){
                            goods_shelves3 += ' checked';
                        }
                        goods_shelves3 += ' name="goods_hot[]" type="checkbox" class="ace" value="'+ id  +'" >'+
                            '<span class="lbl">'+ id +'&nbsp;'+ godds_color +'&nbsp;'+ goods_size +
                            '</span>' +
                            '</label>' +
                            '</div>';
                    $('.chose-goods-shelves1').append(goods_shelves1) ;
                    $('.chose-goods-shelves2').append(goods_shelves2) ;
                    $('.chose-goods-shelves3').append(goods_shelves3) ;
                    $('#popup_add_product').addClass('hide');
                    if( popup_add_product_html != '' && popup_add_product_html != undefined){
                        $('#popup_add_product .popup-content').html(popup_add_product_html);
                    }
                }else{
                    if( msg != null ){
                        layer_required(msg.info);
                    }else{
                        layer_required('该商品不存在，请核实ID号是否正确');
                    }
                }
            },
        });
    });

    //添加商品  设置默认价格
    var popup_stock = $('#popup_stock'),
        tr_flag = '' ;

    $(document).on('click','.set_price, .set_whether_is_gift, .modify-stock, #close_popup_stock, #save_stock, .product-list-delect, .is-self, .save-product-list, .close-btn ',function(e) {
        //设置不同端的价格
        if (e.target.className.indexOf("set_price") >= 0) {
            if( $(this).text() == '设置不同端' ){
                $(this).parents('tr').find('.product-price').addClass('hide');
                $(this).parents('tr').find('.price-all').removeClass('hide');
                $(this).siblings('.is_unified_price').val('1');
                $(this).text('设置默认');
            }else {
                $(this).parents('tr').find('.product-price').removeClass('hide');
                $(this).parents('tr').find('.price-all').addClass('hide');
                $(this).text('设置不同端');
                $(this).siblings('.is_unified_price').val('0');

            }
        }
        //设置不同端赠品
        if (e.target.className.indexOf("set_whether_is_gift") >= 0) {
            if( $(this).text() == '设置不同端' ){
                $(this).parents('tr').find('.is_gift').addClass('hide');
                $(this).parents('tr').find('.gift-all').removeClass('hide');
                $(this).siblings('.set_whether_is_gift').val('1');
                $(this).text('统一设置');
            }else {
                $(this).parents('tr').find('.is_gift').removeClass('hide');
                $(this).parents('tr').find('.gift-all').addClass('hide');
                $(this).text('设置不同端');
                $(this).siblings('.set_whether_is_gift').val('0');

            }
        }
        //显示库存弹窗
        if (e.target.className.indexOf("modify-stock") >= 0) {
            if( $(this).text() == '改为虚拟库存' ){
                $('.modal.fade').addClass('in');
                $('.modal.fade').show();
                var pdc_id=$(this).parent().attr('data-id');
                $('.pdc-id').val(pdc_id);
            } else {
                var pdc_id=$(this).parent().attr('data-id');
                $.ajax({
                    type: 'get',
                    url: '/sku/getStock?id='+pdc_id,
                    //data: data,
                    cache: false,
                    dataType:'json',
                    success: function(msg){
                        if(msg){
                            $('.pdc-id').val(pdc_id);
                            $('.pdc-stock-default').val(msg['virtual_stock_default']);
                            $('.stock-real-k').text(msg['v_stock']);
                            $('.pdc-stock-pc').val(msg['virtual_stock_pc']);
                            $('.pdc-stock-wap').val(msg['virtual_stock_wap']);
                            $('.pdc-stock-app').val(msg['virtual_stock_app']);
                            $('.pdc-stock-wechat').val(msg['virtual_stock_wechat']);
                            //判断商品是否为虚拟商品
                            //var type = $('#product_kind').val();
                            //if(type == 5 && msg['is_use_stock'] == 1){
                            //    msg['is_use_stock'] = 2;
                            //    //隐藏真实库存选项
                            //    $('#real-stock').addClass('hide');
                            //    $('.pdc-stock-default').val(0);
                            //}
                            var cla;
                            if( msg['is_use_stock'] == 1 ) {
                                cla = 'stock-real';
                            }else if( msg['is_use_stock'] == 2 ) {
                                cla = 'stock-public';
                            }else if( msg['is_use_stock'] == 3 ){
                                cla = 'stock-virtual';
                            }
                            $('.'+cla).attr('checked',true);
                            $('.modal.fade').addClass('in');
                            $('.modal.fade').show();
                        }else{
                            layer_required('参数错误');
                        }
                    }
                });
            }

        }

        //删除spu中的商品
        if (e.target.className.indexOf("product-list-delect") >= 0) {
            var a=confirm("确定删除？");
            if(a){
                var th = this;
                var id = $(this).parents('tr').attr('id').split('_')[2];
                $.ajax({
                    type: 'get',
                    url: '/sku/del?id='+id,
                    cache: false,
                    dataType:'json',
                    success: function(msg){
                        if(msg){console.log(msg);
                            location.reload();
                            //$(th).parent().parent().remove();
                            //$('#setimg_' + id).remove();         // 删除设置图片列表中的商品
                            //$('#msg_' + id).remove();            // 删除商品参数列表中的商品
                            //$('#model_' + id).remove();          // 删除添加模板列表中的商品
                            //$('#goods_check_1_' + id).remove();    // 删除选择上下架列表中的商品
                            //$('.time-shelves_' + id).remove();
                            //$('#goods_check_2_' + id).remove();    // 删除选择上下架列表中的商品
                            //$('#goods_check_3_' + id).remove();    // 删除选择上下架列表中的商品
                        }else{
                            layer_required('删除失败！');
                        }
                    }
                });
            }
        }

        //上架、 下架
        if (e.target.className.indexOf("is-self") >= 0) {
            var id = $(this).parents('tr').attr('id').split('_')[2];
            var th = this;
            var url = "/sku/setSale";
            if( $(this).text() == '上架' ){    //点击上架
                var act = 0;
                var data = {'id':id,'act':act};
                $.ajax({
                    type: 'post',
                    url: url,
                    data: data,
                    cache: false,
                    dataType:'json',
                    success: function(msg){
                        $(th).parent().siblings('td').find('.out_self_text').addClass('hide');
                        $(th).removeClass('out-self').addClass('on-self');
                        $(th).text('下架') ;
                    }
                });
            } else {                          //点击下架
                var act = 1;
                var data = {'id':id,'act':act};
                $.ajax({
                    type: 'post',
                    url: url,
                    data: data,
                    cache: false,
                    dataType:'json',
                    success: function(msg){
                        $(th).parent().siblings('td').find('.out_self_text').removeClass('hide');
                        $(th).removeClass('on-self').addClass('out-self');
                        $(th).text('上架') ;
                    }
                });
            }

        }

        //删除上传的图片
        if (e.target.className.indexOf("close-btn") >= 0) {
            e.stopPropagation();
            var id = $(this).attr('data-id');
            var th = this;
            $.ajax({
                type: 'get',
                url: '/sku/delskuimg?id='+id,
                //data: data,
                cache: false,
                dataType:'json',
                success: function(msg){
                    if(msg){
                        $(th).parent().remove();
                    }else{
                        layer_required('删除失败');
                    }
                }
            });
        }
    });

    //切换赠品时显示|隐藏分端设置
    $(document).on('change','#is_gift_change',function(){
       if($(this).val() == 2){
            $(this).parent().parent().find('.set_whether_is_gift').addClass('hide');
        }else{
            $(this).parent().parent().find('.set_whether_is_gift').removeClass('hide');
        }
    });

    //更改虚拟库存
    $(document).on("click",'.btn-default,.close-stock',function(){
        $('.modal.fade').removeClass('in');
        $('.modal.fade').hide();
    });
    $('.btn-primary').on("click",function(){
        var $this = $(this);
        var stock = $this.parent().siblings().find('input[name="stock"]:checked').val();
        if(stock == 1){

        }else if(stock == 2){
            var virtual_stock_default = $this.parent().siblings().find('input[name="virtual_stock_default"]').val();
            if (!regNumber.test(virtual_stock_default)) {
                layer_required('虚拟公共库存必须为大于等于0的整数！');
                return;
            }
        }else if(stock == 3){
            var virtual_stock_pc = $this.parent().siblings().find('input[name="virtual_stock_pc"]').val();
            var virtual_stock_app = $this.parent().siblings().find('input[name="virtual_stock_app"]').val();
            var virtual_stock_wap = $this.parent().siblings().find('input[name="virtual_stock_wap"]').val();
            var virtual_stock_wechat = $this.parent().siblings().find('input[name="virtual_stock_wechat"]').val();
            if (!regNumber.test(virtual_stock_pc)) {
                layer_required('虚拟PC库存必须为大于等于0的整数！');
                return;
            }
            if (!regNumber.test(virtual_stock_app)) {
                layer_required('虚拟APP库存必须为大于等于0的整数！');
                return;
            }
            if (!regNumber.test(virtual_stock_wap)) {
                layer_required('虚拟WAP库存必须为大于等于0的整数！');
                return;
            }
            if (!regNumber.test(virtual_stock_wechat)) {
                layer_required('虚拟WAP库存必须为大于等于0的整数！');
                return;
            }
        }else{
            return;
        }
        $('.modal.fade').removeClass('in');
        $('.modal.fade').hide();
        var typeId=$('input[name="stock"]:checked ').val();
        var pcd_id=$('.pdc-id').val();
        var stock_real_k = $('.stock-real-k').text();
        var form = $("#set_stock_save");
        var url = form.attr('action');
        var data = {};
        data = form.serializeArray();
        $.ajax({
            type: 'post',
            url: url,
            data: data,
            cache: false,
            dataType:'json',
            success: function(msg){
                if(msg['status'] == 'success'){
                    if(typeId==1){
                        $('#product_list_'+pcd_id+' .product_real_stock ').html('真实库存：'+stock_real_k+'件');
                    }else if(typeId==2){
                        $('#product_list_'+pcd_id+' .product_real_stock ').html('公共虚拟库存：'+ msg['data']['virtual_stock_default']+'件');
                    }else {
                        $('#product_list_'+pcd_id+' .product_real_stock ').html(
                            '<div>PC虚拟库存: '+ msg['data']['virtual_stock_pc']+'件 </div>'+
                            '<div>APP虚拟库存: '+ msg['data']['virtual_stock_app']+'件 </div>'+
                            '<div>WAP虚拟库存: '+ msg['data']['virtual_stock_wap']+'件</div>'+
                            '<div>微商城虚拟库存: '+ msg['data']['virtual_stock_wechat']+'件</div>');
                    }
                }else{
                    layer_required(msg['info']);
                }
            }
        });
    });
    //点击图片添加红色边框 设为默认图片
    //$(document).on('click','.product_setimg div',function(){
    //    var imgId = $(this).attr('data-id');
    //    var th = this;
    //    $.ajax({
    //        type: 'get',
    //        url: '/sku/updateskuimg?id='+imgId,
    //        //data: data,
    //        cache: false,
    //        dataType:'json',
    //        success: function(msg){
    //            if(msg){
    //                $(th).addClass('active').siblings().removeClass('active');
    //            }else{
    //                layer_required('修改失败');
    //            }
    //        }
    //    });
    //})


    //商品参数管理
    //  点击商品
    $(document).on('click','.product-property a',function() {

        $(this).addClass('active').siblings().removeClass('active');
        var id = $(this).attr('id').split('_')[1];
        var spu_id = $('#spu_id').val();
        $('#fp_hide_id').val( id );  // 设置隐藏的商品id
        $.ajax({
            type: 'get',
            url: '/spu/getAttrAll?id='+id+'&spu_id='+spu_id,
            //data: data,
            cache: false,
            dataType:'json',
            success: function(msg){
                if(msg){
                    $('#fp_name').val(msg['sku']['sku_alias_name']);
                    $('#fp_name_pc1').val(msg['sku']['sku_pc_name']);
                    $('#fp_name_pc2').val(msg['sku']['sku_pc_subheading']);
                    $('#fp_name_mobile').val(msg['sku']['sku_mobile_name']);
                    $('#fp_name_mobile2').val(msg['sku']['sku_mobile_subheading']);
                    $('#fp_zqwh').val(msg['sku']['sku_batch_num']);
                    $('#fp_time').val(msg['sku']['period']);
                    $('#fp_code').val(msg['sku']['barcode']);
                    $('#fp_company').val(msg['sku']['manufacturer']);
                    $('#fp_weight').val(msg['sku']['sku_weight']);
                    $('#fp_volume').val(msg['sku']['sku_bulk']);
                    $('#fp_meta_title').val(msg['sku']['meta_title']);
                    $('#fp_meta_key').val(msg['sku']['meta_keyword']);
                    $('#fp_meta_desc').val(msg['sku']['meta_description']);
                    $('#fp_specifications').val(msg['sku']['specifications']);
                    $('#fp_sku_usage').val(msg['sku']['sku_usage']);
                    $('#fp_label0').val(msg['sku']['sku_label'][0]);
                    $('#fp_label1').val(msg['sku']['sku_label'][1]);
                    $('#fp_label2').val(msg['sku']['sku_label'][2]);
                    $('#fp_prod_name_common').val(msg['sku']['prod_name_common']);

                    //下拉框  属性
                    $('#fp_other').empty();
                    var option_str = '';
                    var i = 0,
                        ary_len = msg['categoryAttr'].length ;
                    for( i ; i< ary_len ; i++ ){
                        option_str += '<div class="form-group"><label class="col-sm-2 control-label no-padding-right" >';
                        if(msg['categoryAttr'][i]['is_null'] == 1){
                            option_str += '<span class="span_red">*</span>';
                        }
                        option_str += msg['categoryAttr'][i]['attr_name']+'：</label><div class="col-sm-10"><select class="col-xs-5 col-sm-5';
                        if(msg['categoryAttr'][i]['is_null'] == 1){
                            option_str += ' attr_is_null_required';
                        }
                        option_str += '" name="product_other['+msg['categoryAttr'][i]['id']+']" id="fp_other_'+msg['categoryAttr'][i]['id']+'" ><option value="">--请选择--</option>';
                        var j = 0,
                            jary_len = msg['categoryAttr'][i]['attr_value'].length ;
                        for( j ;j<jary_len ;j++ ){
                            option_str += '<option value="'+msg['categoryAttr'][i]['attr_value'][j]['id']+'"';
                            if( msg['attrValue'] && msg['categoryAttr'][i]['attr_value'][j]['id'] == msg['attrValue'][msg['categoryAttr'][i]['id']] ){
                                option_str += 'selected';
                            }
                            option_str += '>'+msg['categoryAttr'][i]['attr_value'][j]['attr_value']+'</option>'
                        }

                        option_str += '</select></div></div>';
                    }
                    $('.category-attr-list').html(option_str);

                    //添加赠品
                    $('#gift_table tbody').empty();
                    var gift_ary = msg['giftArr'],
                        gift_str = '';
                    if(gift_ary && gift_ary.length ){
                        gift_ary.forEach(function(each){
                            gift_str += '<tr>' +
                                '<td class="center">' +
                                '<input type="text" class="gift-id" name="bind_gift[]" value="'+each.id+'" readonly />' +
                                '</td>' +
                                '<td class="center">'+ each.goods_name + '</td>' +
                                '<td class="center"><input type="text" name="bind_gift_num[]" value="'+each.num+'"></td>' +
                                '<td class="center">' +
                                '<a href="javascript:;" class="delect_gift"> 删除</a>' +
                                '</td>' +
                                '</tr>' ;
                        });
                    }
                    //$('#gift_table tbody').append(gift_str) ;
                    $('#gift_table tbody').html( gift_str );
                    if(msg['video']){
                        $('#video-url').text(msg['video']['video_name']);
                        $('#fp_video').val(msg['video']['id']);
                        $('.video-delect').removeClass('hide');
                    }else{
                        $('#video-url').text('');
                        $('#fp_video').val('');
                        $('.video-delect').addClass('hide');
                    }
                }
            }
        });
    });

    // 查找赠品
    $('#search_gift').click(function() {
        var gift_text  =  $('#gift_text').val();
        var shop_id = $('#spu_supplier').val();
        var data = {'test':gift_text, 'shop_id':shop_id};
        if( gift_text.length > 0 ){
            $.ajax({
                type: 'post',
                url: '/sku/getBindGift',
                data: data,
                cache: false,
                dataType:'json',
                success: function(msg){
                    if(msg){
                        if( msg.status == 'error' ){
                            layer_required(msg.info);
                            return;
                        }
                        $('#gift_chose').empty();
                        var str = '';
                        for(var i=0;i<msg.length;i++){
                            str += '<option value="'+ msg[i]['id'] +'">'+ msg[i]['goods_name'] + msg[i]['rule_value_id'] +'</option>' ;
                        }
                        $('#gift_chose').append(str) ;
                        $('#gift_text').val('');
                    }else{
                        $('#gift_chose').empty();
                        layer_required('未查找到数据……');
                    }
                }
            });

        }  else {
            layer_required('请输入赠品名称或者赠品id');
        }

    });
    //添加赠品
    $('#add_gift').click(function() {
        var select_gift = $('#gift_chose option:selected').text();
        var select_gift_id = $('#gift_chose option:selected').val();
        if(select_gift_id != undefined){
            var str = '<tr>' +
                '<td class="center">' +
                '<input type="text" class="gift-id" id="gift_id" name="bind_gift[]" value="'+select_gift_id+'" readonly />' +
                '</td>' +
                '<td class="center"><p class="gift-name-limit">'+ select_gift + '</p></td>' +
                '<td class="center"><input type="text" class="get_gift_value" name="bind_gift_num[]" value="1"></td>' +
                '<td class="center">' +
                '<a href="javascript:;" class="delect_gift"> 删除</a>' +
                '</td>' +
                '</tr>' ;
            $('#gift_table tbody').append( str );
        }
    });


    $(document).on('click','.delect_gift,#close_popup_video, .page-list a,.video-upload, .video-list img, .video-delect',function(e) {
        //删除选中的赠品
        if (e.target.className.indexOf("delect_gift") >= 0) {
            $(this).parent().parent().remove() ;
        }

        //显示选择视频弹窗
        if (e.target.className.indexOf("video-upload") >= 0) {
            $.ajax({
                type: 'get',
                url: '/video/getAllVideo',
                //data: data,
                cache: false,
                dataType:'json',
                success: function(msg){
                    if(msg.res == 'success'){
                        if(msg.list != 0){
                            var html = '<div class="title">选择视频<a id="close_popup_video" href="javascript:;">&times;</a></div><div class="video-img clearfix">';
                            for(var i=0;i<msg['list'].length;i++){
                                html += '<div class="video-list"><img src="'+msg.list[i]['img']+'" alt=""/><p data-id="'+msg.list[i]['id']+'" class="video-name">'+msg.list[i]['video_name']+'</p><p>时长: '+msg.list[i]['video_duration']+' 秒</p></div>';
                            }
                            html += '</div><div class="col-sm-12"><div class="col-sm-6 text-l page-list">';
                            if(msg.pages<=5){
                                for(var i=1;i<=msg.pages;i++){
                                    if(i==msg.page){
                                        html += '<a class="active" href="javascript:;">'+i+'</a>';
                                    }else{
                                        html += '<a href="javascript:;">'+i+'</a>';
                                    }
                                }
                            }else{
                                if(msg.page <= 3){
                                    for(var i=1;i<=5;i++){
                                        if(i==msg.page){
                                            html += '<a class="active" href="javascript:;">'+i+'</a>';
                                        }else{
                                            html += '<a href="javascript:;">'+i+'</a>';
                                        }
                                    }
                                }else if(msg.page+3 >= msg.pages){
                                    for(var i=msg.pages-5;i<=msg.pages;i++){
                                        if(i==msg.page){
                                            html += '<a class="active" href="javascript:;">'+i+'</a>';
                                        }else{
                                            html += '<a href="javascript:;">'+i+'</a>';
                                        }
                                    }
                                }else{
                                    for(var i=msg.page-2;i<=msg.page+2;i++){
                                        if(i==msg.page){
                                            html += '<a class="active" href="javascript:;">'+i+'</a>';
                                        }else{
                                            html += '<a href="javascript:;">'+i+'</a>';
                                        }
                                    }
                                }
                            }
                             html +=  '</div><div class="col-sm-6 text-r"><p>共'+msg.pages+'页 4条记录，第'+msg.page+'页</p></div></div>';
                            $('#popup_video_list').html(html);
                            $('#popup_video').removeClass('hide');
                        }else{
                            layer_required('无符合视频数据');
                        }
                    }
                }
            });
        }

        // 关闭选择视频 弹窗
        if (e.target.id == 'close_popup_video' ) {
            $(this).parents('#popup_video').addClass('hide') ;
        }

        // 选择视频
        if (e.target.nodeName == 'IMG') {
            $(this).parents('#popup_video').addClass('hide') ;
            var chose_video = $(this).siblings('.video-name');
            $('#video-url').text(chose_video.text());
            $('#fp_video').val(chose_video.attr('data-id'));
            $('.video-delect').removeClass('hide');
        }


        //选择视频  分页
        if(e.target.nodeName == 'A'){
            var page = $(this).text();  // page 点击第几页
            $.ajax({
                type: 'get',
                url: '/video/getAllVideo?page='+page,
                //data: data,
                cache: false,
                dataType:'json',
                success: function(msg){
                    if(msg.res == 'success'){
                        if(msg.list != 0){
                            var html = '<div class="title">选择视频<a id="close_popup_video" href="javascript:;">&times;</a></div><div class="video-img clearfix">';
                            for(var i=0;i<msg['list'].length;i++){
                                html += '<div class="video-list"><img src="'+msg.list[i]['img']+'" alt=""/><p data-id="'+msg.list[i]['id']+'" class="video-name">'+msg.list[i]['video_name']+'</p><p>时长: '+msg.list[i]['video_duration']+' 秒</p></div>';
                            }
                            html += '</div><div class="col-sm-12"><div class="col-sm-6 text-l page-list">';
                            if(msg.pages<=5){
                                for(var i=1;i<=msg.pages;i++){
                                    if(i==msg.page){
                                        html += '<a class="active" href="javascript:;">'+i+'</a>';
                                    }else{
                                        html += '<a href="javascript:;">'+i+'</a>';
                                    }
                                }
                            }else{
                                if(msg.page <= 3){
                                    for(var i=1;i<=5;i++){
                                        if(i==msg.page){
                                            html += '<a class="active" href="javascript:;">'+i+'</a>';
                                        }else{
                                            html += '<a href="javascript:;">'+i+'</a>';
                                        }
                                    }
                                }else if(msg.page+2 >= msg.pages){
                                    for(var i=msg.pages-4;i<=msg.pages;i++){
                                        if(i==msg.page){
                                            html += '<a class="active" href="javascript:;">'+i+'</a>';
                                        }else{
                                            html += '<a href="javascript:;">'+i+'</a>';
                                        }
                                    }
                                }else{
                                    for(var i=msg.page-2;i<=msg.page+2;i++){
                                        if(i==msg.page){
                                            html += '<a class="active" href="javascript:;">'+i+'</a>';
                                        }else{
                                            html += '<a href="javascript:;">'+i+'</a>';
                                        }
                                    }
                                }
                            }
                            html +=  '</div><div class="col-sm-6 text-r"><p>共'+msg.pages+'页 4条记录，第'+msg.page+'页</p></div></div>';
                            $('#popup_video_list').html(html);
                            $('#popup_video').removeClass('hide');
                        }else{
                            layer_required('无符合视频数据');
                        }
                    }
                }
            });
            //$(this).addClass('active').siblings().removeClass('active');
        }

        //删除选中的视频
        if (e.target.className.indexOf("video-delect") >= 0) {
            $('#video-url').text('');
            $('#fp_video').val('');
            $(this).addClass('hide');
        }

    })


    //设置商品模板
    // 点击切换 pc端和移动端
    $(document).on('click','.goods-port a',function(){
        $(this).addClass('active').siblings().removeClass('active');
        var id = $(this).attr('id');
        var type = id;
        var spu_id = $('#spu_id').val();
        var skuId = $('#goods_model_id').val();
        $('#is_port').val( id );   // 设置隐藏的商品id
        getSkuInfo(type,skuId,spu_id);
    });
    //点击切换商品
    $(document).on('click','.goods-nav a',function(){
        $(this).addClass('active').siblings().removeClass('active');
        var id = $(this).attr('id').split('_')[1];
        var type = $('#is_port').val();
        var spu_id = $('#spu_id').val();
        $('#goods_model_id').val( id );   // 设置隐藏的商品id
        getSkuInfo(type,id,spu_id);
    });

    var is_modify = false ,         //判断是否是修改选择模板
        tr_id ,                     //修改和删除的模板的id
        is_botm_ad;                // 1:广告模板  2:底部广告模板
    $(document).on('click','.add-model-btn, .btom-model-btn, #close_popup_model, #chose_model_sure, .delect-model,.modify-model',function(e) {
         var ad_len=$('#ad_model_s').find('input').length,
             btm_ad_len=$('#ad_model_x').find('input').length,
             ad_is_chose = [],           //广告模板数组，存储已经选择的模板id
        btom_is_chose = [],        //底部广告模板数组，存储已经选择的模板id;
        ad_is_chose_name = [],           //广告模板数组，存储已经选择的模板id
            btom_is_chose_name = [] ;        //底部广告模板数组，存储已经选择的模板id;
        for(var i=0;i<ad_len;i++){
            ad_is_chose_name.push($('#ad_model_s').find('span').eq(i).text());
            ad_is_chose.push($('#ad_model_s').find('input').eq(i).val());
        }
        for(var j=0;j<btm_ad_len;j++){
            btom_is_chose_name.push($('#ad_model_x').find('span').eq(j).text());
            btom_is_chose.push($('#ad_model_x').find('input').eq(j).val());
        }
        // 点击添加模板按钮，点击添加模板按钮，  显示弹窗
        if (e.target.className.indexOf("add-model-btn") >= 0 || e.target.className.indexOf("btom-model-btn") >= 0 ) {
            var type = $('.product-panel .edit-info-list .goods-port').find('.active').attr('id');
            $.ajax({
                type: 'post',
                url: '/sku/getAdAll?type='+type,
                //data: data,
                cache: false,
                dataType:'json',
                success: function(msg){
                    if(msg.status == 'error'){
                        layer_required(msg.info);
                        return;
                    }
                    var html = '';
                    $('.row-model-line').remove();
                    if(msg){
                        var i = 0,
                            len = msg.length;
                        for( i ; i < len; i++ ){
                            html += '<div class="row row-model-line"><div class="col-sm-12"><label><input name="chose-model" type="checkbox" class="ace" value="'+msg[i]['id']+'"><span class="lbl">'+msg[i]['ad_name']+'</span></label></div><div class="hide"><div class="model-list"></div></div></div>';
                        }
                    }else{
                        html += '<div class="row row-model-line"><div class="col-sm-12"><span class="lbl">无符合广告模板……</span></label></div><div class="hide"><div class="model-list"></div></div></div>';
                    }
                    $('#chose_model_sure').before(html);
                    $('#popup_model').removeClass('hide') ;
                    if(e.target.className.indexOf("add-model-btn") >= 0){
                        is_botm_ad = 1 ;
                    }else {
                        is_botm_ad = 2 ;
                    }
                }
            });
        }
        // 关闭选择广告模板 弹窗
        if (e.target.id == 'close_popup_model' ) {
            $(this).parents('#popup_model').addClass('hide') ;
            $('#popup_model input[type="checkbox"]').removeAttr('checked');
        }

        //  选择模板 确认按钮
        if (e.target.id == 'chose_model_sure' ) {
            var radio_chose = $('input:checkbox[name="chose-model"]:checked');

            var chose_text = [] ,        //获取模板名称
                ary_img = [],                                              //空数组，存储模板图片
            chose_id = [] ;                            //选择的模板id

            for(var i=0;i<radio_chose.siblings('span').length;i++){
                chose_text.push(radio_chose.siblings('span').eq(i).text());
                chose_id.push(radio_chose.eq(i).val());
            }
            //console.log(chose_id);
            var is_botm = (is_botm_ad == 1) ? 'adt_' : 'adb_' ;             // adt_:广告模板  adb_:底部广告模板
            var is_chose = (is_botm_ad == 1) ? ad_is_chose : btom_is_chose ;
            var name = (is_botm_ad == 1) ? 'ad[]':'btom-ad[]' ;      //隐藏文本框的属性name   ad：广告模板   btom-ad：底部广告模板

            if( chose_text ) {
                var ary_index = is_chose.indexOf(chose_id[0]);
                console.log(ary_index)
                if( ary_index < 0 || is_modify ){
                    if( is_modify ){
                        for(var i=0;i<chose_id.length;i++){
                            if( chose_id[i] != tr_id ){
                                if( is_chose.indexOf(chose_id[i]) >=0 ){
                                    layer_required('你已经添加了'+chose_text[i]+'广告模型');
                                    return false ;
                                }
                            }
                        }
                    }
                    radio_chose.parents('.row').find('.model-list img').each(function() {
                        ary_img.push($(this).attr('src')) ;                     //把选中的模板图片存储进数组
                    });
                    var str = '' ;
                    for(var j=0;j<chose_text.length;j++){
                        if( is_modify ){
                            str += '<input type="hidden" name="'+ name +'" value="'+ chose_id[j] +'" />'+
                                '<div class="ad-list-title">模板名称：<span>'+ chose_text[j] +'</span>'+
                                '<a href="/sku/adedit?id='+chose_id[j]+'&isLimit=1" target="_blank" class="modify-model">修改</a><a href="javascript:;" class="delect-model">删除</a> </div>'+
                                '<div class="ad-list-img">';
                        }else {
                            str += '<div id="'+ is_botm + chose_id[j] +'"> '+
                                '<input type="hidden" name="'+ name +'" value="'+ chose_id[j] +'" />'+
                                '<div class="ad-list-title">模板名称：<span>'+ chose_text[j] +'</span>'+
                                '<a href="/sku/adedit?id='+chose_id[j]+'&isLimit=1" target="_blank" class="modify-model">修改</a><a href="javascript:;" class="delect-model">删除</a> </div>'+
                                '<div class="ad-list-img">';
                        }
                        var i = 0,
                            len = ary_img.length;

                        for(i ; i < len ; i++ ){
                            str +=  '<img src="'+ i +'" alt=""/>';
                        }

                        if( is_modify ){
                            str += '</div>' ;
                        } else {
                            str += '</div></div>' ;
                        }
                    }

                    if( is_modify ){      //修改模板，先清空，再追加
                        $('#' + is_botm + tr_id ).empty();
                        $('#' + is_botm + tr_id ).append(str);
                        $('#' + is_botm + tr_id ).attr('id', is_botm + chose_id );
                        is_chose.splice(ary_index,1,chose_id);    //替换选中的模板
                        is_modify = false ;
                    } else {
                        var add_ele = (is_botm_ad == 1) ? 'ad_list' : 'btm_ad_model' ;
                        $('#' + add_ele ).append(str);
                        is_chose.push(chose_id) ;       // 把选中的模板id保存进数组
                    }
                    tr_id = '' ;
                    if( is_botm_ad == 1 ){
                        ad_is_chose = is_chose;
                    }else {
                        btom_is_chose = is_chose;
                    }
                    $(this).parents('#popup_model').addClass('hide') ;
                    $('#popup_model input[type="checkbox"]').removeAttr('checked');

                } else {
                    layer_required('你已经添加了'+chose_text[0]+'广告模型');
                }
            } else  {
                layer_required('请选择模板');
            }
        }
        // 删除模板
        if (e.target.className.indexOf("delect-model") >= 0) {
            var id_text = $(this).parent().parent().attr('id'),
                id = id_text.split('_')[1],      //获取修改模板的id
                text = id_text.split('_')[0];

            if( text == 'adt' ){
                is_botm_ad = 1;
                var ary_index = ad_is_chose.indexOf(id);
                if (ary_index > -1) {
                    ad_is_chose.splice(ary_index, 1);       // 从数组中删除id
                }

            }else if( text == 'adb'){
                is_botm_ad = 2;
                var ary_index = btom_is_chose.indexOf(id);
                if (ary_index > -1) {
                    btom_is_chose.splice(ary_index, 1);
                }
            }

            $(this).parent().parent().remove();
        }

    });

    //药品说明书切换
    $(document).on('click','.product-instruction-nav a',function() {
        var id = $(this).attr('data-id');
        $('#goods_instruction_id').val(id);
        $.ajax({
            type: 'get',
            url: '/sku/getInstruction?id='+id,
            //data: data,
            cache: false,
            dataType:'json',
            success: function(msg){
                if(msg){
                    if(msg.status == 'error'){
                        layer_required(msg.info);
                        return;
                    }
                    var k = '';
                    for(k in msg){
                        $('#instructions_'+k).val(msg[k]);
                    }
                }else{
                    $('.sku_instruction_info').find('input').each(function(){
                        $(this).val('');
                    });
                }
            }
        });
        $(this).addClass('active').siblings().removeClass('active');
        $('.chose-item').eq(id).removeClass('hide').siblings('.chose-item').addClass('hide');
    });

/************************** ajax 提交 *********************************/
    //保存sku信息
    var _clickDisable = false;
    $(document).on('click','.save_msg',function(){
        var savetxt=$(this).text();
        if(savetxt.trim()=='修改'){
            $(this).text('保存');
            $(this).parent().parent().find('input').removeAttr('disabled');
            $(this).parent().parent().find('select').removeAttr('disabled');
            $(this).parent().parent().find('.save_msg_cancel').removeClass('hide');
        }else{
            //判断是否合法
            var $this = $(this);
            var is_unified_price = $this.parent().siblings().find('input[name="is_unified_price"]').val();
            if(is_unified_price == 0){
                var goods_price = $this.parent().siblings().find('input[name="goods_price"]').val();
                var market_price = $this.parent().siblings().find('input[name="market_price"]').val();
                if(goods_price=='' || goods_price==0){layer_required('请输入正确的统一销售价');return;};
                if(market_price=='' || market_price==0){layer_required('请输入正确的统一市场价');return;};
                if (!regMoney.test(goods_price)) { layer_required('统一销售价只能是数字且最多保留两位小数');return;}
                if (!regMoney.test(market_price)) { layer_required('统一市场价只能是数字且最多保留两位小数');return;}
            }else if(is_unified_price == 1){
                var goods_price_pc = $this.parent().siblings().find('input[name="goods_price_pc"]').val();
                var goods_price_app = $this.parent().siblings().find('input[name="goods_price_app"]').val();
                var goods_price_wap = $this.parent().siblings().find('input[name="goods_price_wap"]').val();
                var goods_price_wechat = $this.parent().siblings().find('input[name="goods_price_wechat"]').val();
                var market_price_pc = $this.parent().siblings().find('input[name="market_price_pc"]').val();
                var market_price_app = $this.parent().siblings().find('input[name="market_price_app"]').val();
                var market_price_wap = $this.parent().siblings().find('input[name="market_price_wap"]').val();
                var market_price_wechat = $this.parent().siblings().find('input[name="market_price_wechat"]').val();
                if(goods_price_pc=='' || goods_price_pc==0){layer_required('请输入正确的pc销售价');return;};
                if(goods_price_app=='' || goods_price_app==0){layer_required('请输入正确的app销售价');return;};
                if(goods_price_wap=='' || goods_price_wap==0){layer_required('请输入正确的wap销售价');return;};
                if(market_price_pc=='' || market_price_pc==0){layer_required('请输入正确的pc市场价');return;};
                if(market_price_app=='' || market_price_app==0){layer_required('请输入正确的app市场价');return;};
                if(market_price_wap=='' || market_price_wap==0){layer_required('请输入正确的wap市场价');return;};
                if(goods_price_wechat=='' || goods_price_wechat==0){layer_required('请输入正确的微商城市场价');return;};
                if(market_price_wechat=='' || market_price_wechat==0){layer_required('请输入正确的统一市场价');return;};
                if (!regMoney.test(goods_price_pc)) { layer_required('pc销售价只能是数字且最多保留两位小数');return;}
                if (!regMoney.test(goods_price_app)) { layer_required('app销售价只能是数字且最多保留两位小数');return;}
                if (!regMoney.test(goods_price_wap)) { layer_required('wap销售价只能是数字且最多保留两位小数');return;}
                if (!regMoney.test(market_price_pc)) { layer_required('pc市场价只能是数字且最多保留两位小数');return;}
                if (!regMoney.test(market_price_app)) { layer_required('app市场价只能是数字且最多保留两位小数');return;}
                if (!regMoney.test(market_price_wap)) { layer_required('wap市场价只能是数字且最多保留两位小数');return;}
                if (!regMoney.test(goods_price_wechat)) { layer_required('微商城销售价只能是数字且最多保留两位小数');return;}
                if (!regMoney.test(market_price_wechat)) { layer_required('微商城市场价只能是数字且最多保留两位小数');return;}

            }else{
                return;
            }
            var sort_tmp = $this.parent().siblings().find("input[name='sort']").val();
            if (!regNumber.test(sort_tmp)) { layer_required('请输入正确的排序');return;}
            var form = $(this).parent().siblings(".form_sku");
            var url = form.attr('action');
            var data = {};
            data = form.serializeArray();
            if (_clickDisable) {return false;}
            _clickDisable = true;
            $.ajax({
                type: 'POST',
                url: url,
                data: data,
                cache: false,
                dataType:'json',
                success: function(msg){
                    _clickDisable = false;
                    if(msg.status == 'error'){
                        layer.load(20, {time: 1});
                        layer.msg(msg.info, {shade: 0.3, time: 1000}); return false;
                    }else if(msg.status == 'success'){
                        layer.load(20, {time: 1});
                        layer.msg(msg.info, {shade: 0.3, time: 1000});
                        $this.text('修改');
                        $this.parent().parent().find('.save_msg_cancel').addClass('hide');
                        $this.parent().parent().find('input').prop('disabled','true');
                        $this.parent().parent().find('select').prop('disabled','true');
                        if(sort_tmp == ''){
                            $this.parent().siblings().find("input[name='sort']").val(1);
                        }
                        var id = $this.parent().parent().attr('id').split('_')[2]; // 获取修改的商品id
                        var godds_color = $('#product_list_'+ id +' .rule_value_1').val() , //获取商品的颜色
                            goods_size = $('#product_list_'+ id +' .rule_value_2').val() ;   //获取商品的尺寸
                            goods_rule = $('#product_list_'+ id +' .rule_value_3').val() ;
                        //品规信息
                        if( godds_color == undefined ){
                            godds_color = '';
                        }
                        if( goods_size == undefined || goods_size == '' ){
                            goods_size = (goods_rule == undefined)?goods_rule:'';
                        }else{
                            goods_size += '+'+goods_rule;
                        }
                        // 修改设置图片列表中的商品名称
                        $('#setimg_' + id).find('.setimg-goods-name').html( id + '&nbsp;' + godds_color + goods_size );
                        // 修改商品参数列表中的商品名称
                        $('#msg_' + id).html(id + '&nbsp;' + godds_color + goods_size);
                        // 修改添加模板列表中的商品名称
                        $('#model_' + id).html(id + '&nbsp;' + godds_color + goods_size);
                        // 修改说明书中的商品名称
                        $('#instruction_' + id).html(id + '&nbsp;' + godds_color + '&nbsp;' + goods_size);
                        // 修改选择上下架列表中的商品名称
                        $('#goods_check_1_'+ id).find('span').html(id + '&nbsp;' + godds_color + goods_size);
                        $('#goods_check_2_'+ id).find('span').html(id + '&nbsp;' + godds_color + goods_size);
                        $('#goods_check_3_'+ id).find('span').html(id + '&nbsp;' + godds_color + goods_size);
                        return false;
                    }
                },
                error: errorCallback
            });

        }
    });

    //保存sku商品信息
    $(document).on('click','.save_msg_ajax',function(){
        var savetxt=$(this).text();
        if(savetxt.trim()=='修改'){
            $(this).text('保存');
            $(this).parent().parent().find('input').removeAttr('disabled');
            $(this).parent().parent().find('select').removeAttr('disabled');
            $(this).parent().parent().find('.save_msg_cancel').removeClass('hide');
        }else{
            //判断是否合法
            var $this = $(this);
            var is_unified_price = $this.parent().siblings().find('input[name="is_unified_price"]').val();
            if(is_unified_price == 0){
                var goods_price = $this.parent().siblings().find('input[name="goods_price"]').val();
                var market_price = $this.parent().siblings().find('input[name="market_price"]').val();
                if (!regMoney.test(goods_price)) { layer_required('统一销售价只能是数字且最多保留两位小数');return;}
                if (!regMoney.test(market_price)) { layer_required('统一市场价只能是数字且最多保留两位小数');return;}
            }else if(is_unified_price == 1){
                var goods_price_pc = $this.parent().siblings().find('input[name="goods_price_pc"]').val();
                var goods_price_app = $this.parent().siblings().find('input[name="goods_price_app"]').val();
                var goods_price_wap = $this.parent().siblings().find('input[name="goods_price_wap"]').val();
                var goods_price_wechat = $this.parent().siblings().find('input[name="goods_price_wechat"]').val();
                var market_price_pc = $this.parent().siblings().find('input[name="market_price_pc"]').val();
                var market_price_app = $this.parent().siblings().find('input[name="market_price_app"]').val();
                var market_price_wap = $this.parent().siblings().find('input[name="market_price_wap"]').val();
                var market_price_wechat = $this.parent().siblings().find('input[name="market_price_wechat"]').val();
                if (!regMoney.test(goods_price_pc)) { layer_required('pc销售价只能是数字且最多保留两位小数');return;}
                if (!regMoney.test(goods_price_app)) { layer_required('app销售价只能是数字且最多保留两位小数');return;}
                if (!regMoney.test(goods_price_wap)) { layer_required('wap销售价只能是数字且最多保留两位小数');return;}
                if (!regMoney.test(market_price_pc)) { layer_required('pc市场价只能是数字且最多保留两位小数');return;}
                if (!regMoney.test(market_price_app)) { layer_required('app市场价只能是数字且最多保留两位小数');return;}
                if (!regMoney.test(market_price_wap)) { layer_required('wap市场价只能是数字且最多保留两位小数');return;}
                if (!regMoney.test(goods_price_wechat)) { layer_required('微商城市场价只能是数字且最多保留两位小数');return;}
                if (!regMoney.test(market_price_wechat)) { layer_required('微商城市场价只能是数字且最多保留两位小数');return;}
            }else{
                return;
            }
            var id = $(this).attr('data-id');
            var formId = '#product_list_'+id;
            var data = {};

            $(formId).find('input').each(function(idx){
                var key = $(this).attr('name');
                var val = $(this).val();
                if(data[key]){
                    data[key] +='+'+val;
                }else{
                    data[key] = val;
                }
            });
            $(formId).find('select').each(function(idx){
                var key = $(this).attr('name');
                var val = $(this).val();
                if(data[key]){
                    data[key] +='+'+val;
                }else{
                    data[key] = val;
                }
            });
            //console.log(data);
            var url = '/sku/edit';
            $.ajax({
                type: 'POST',
                url: url,
                data: data,
                cache: false,
                dataType:'json',
                success: function(msg){
                    if(msg.status == 'error'){
                        layer.load(20, {time: 1});
                        layer.msg(msg.info, {shade: 0.3, time: 1000}); return false;
                    }else if(msg.status == 'success'){
                        layer.load(20, {time: 1});
                        layer.msg(msg.info, {shade: 0.3, time: 1000});
                        $this.text('修改');
                        $this.parent().parent().find('.save_msg_cancel').addClass('hide');
                        $this.parent().parent().find('input').prop('disabled','true');
                        $this.parent().parent().find('select').prop('disabled','true');
                        var id = $this.parent().parent().attr('id').split('_')[2]; // 获取修改的商品id
                        var godds_color = $('#product_list_'+ id +' .rule_value_1').val() , //获取商品的颜色
                            goods_size = $('#product_list_'+ id +' .rule_value_2').val(),   //获取商品的尺寸
                            goods_rule = $('#product_list_'+ id +' .rule_value_3').val() ;
                        if( godds_color == undefined ){
                            godds_color = '';
                        }
                        if( goods_size == undefined || goods_size == '' ){
                            goods_size = (goods_rule == undefined)?goods_rule:'';
                        }else{
                            goods_size += '+'+goods_rule;
                        }
                        // 修改设置图片列表中的商品名称
                        $('#setimg_' + id).find('.setimg-goods-name').html( id + '&nbsp;' + godds_color + '&nbsp;' + goods_size );
                        // 修改商品参数列表中的商品名称
                        $('#msg_' + id).html(id + '&nbsp;' + godds_color + '&nbsp;' + goods_size);
                        // 修改添加模板列表中的商品名称
                        $('#model_' + id).html(id + '&nbsp;' + godds_color + '&nbsp;' + goods_size);
                        // 修改说明书中的商品名称
                        $('#instruction_' + id).html(id + '&nbsp;' + godds_color + '&nbsp;' + goods_size);
                        // 修改选择上下架列表中的商品名称
                        //$('#goods_check_' + id).find('span').html(id + '&nbsp;' + godds_color + '&nbsp;' + goods_size);
                        $('#goods_check_1_'+ id).find('span').html(id + '&nbsp;' + godds_color + '&nbsp;' + goods_size);
                        $('#goods_check_2_'+ id).find('span').html(id + '&nbsp;' + godds_color + '&nbsp;' + goods_size);
                        $('#goods_check_3_'+ id).find('span').html(id + '&nbsp;' + godds_color + '&nbsp;' + goods_size);
                        return false;
                    }
                },
                error: errorCallback
            });

        }

    });

    //取消编辑
    $(document).on('click','.save_msg_cancel',function(){
        $(this).parent().parent().find('.save_msg').text('修改');
        $(this).parent().parent().find('.save_msg_ajax').text('修改');
        $(this).addClass('hide');
        $(this).parent().parent().find('input').prop('disabled','true');
        $(this).parent().parent().find('select').prop('disabled','true');
    });

    //上传图片
    $(document).on('change', '.move_image', function()    {
        var id = $(this).attr('id').split('_')[2];
        var spu_id = 0;
        if( id == 0 ){
            var spu_id = $('#spu_id').val();
        }
        if(filesize(this)){
            uploadFileAll('/sku/uploadImg?id='+id+'&spu_id='+spu_id, $(this).attr('id'));
        }
    });
    //判断上传图片是否符合要求
    function filesize(ele) {
        var type = 'jpeg,jpg,png,gif';
        // 返回 KB，保留小数点后两位
        var i= 0,len=ele.files.length;
        for( i ; i < len ; i++ ){
            if( type.indexOf(ele.files[i].type.replace('image/','')) < 0){
                layer_required('文件类型错误');
                return false;
            }
            if( (ele.files[i].size / 1024).toFixed(2) > 2048 ){
                layer_required('文件超出大小限制');
                return false;
            }
        }
        return true;
    }
    //保存sku属性信息
    var saveProductAttrLock = false;
    $('.save_product_attr').click(function(){
        var act = false;
        $('.attr_is_null_required').each(function(){
            if($(this).val() == ''){
                act = true;return;
            }
        });
        if(act){
            layer_required('请确定所以必填参数都已填选！');
            return;
        }
        $('.get_gift_value').each(function(){
            if (!regNumber.test($(this).val()) || $(this).val() == 0) {
                act = true;return;
            }
        });
        if(act){
            layer_required('赠品数量只能是大于0的整数！');
            return;
        }
        if(saveProductAttrLock){return false;}
        saveProductAttrLock = true;
        var form = $('#save_product_attr_form');
        var url = form.attr('action');
        var data = {};
        data = form.serializeArray();
        $.ajax({
            type: 'POST',
            url: url,
            data: data,
            cache: false,
            dataType:'json',
            success: function(data){
                saveProductAttrLock = false;
                okCallback(data);
            },
            error: errorCallback
        });   
    });

    //保存sku详情信息
    var saveProductInfoAdLock = false;
    $('.save_product_info_ad ').click(function(){
        if(saveProductInfoAdLock){return false;}
        saveProductInfoAdLock = true;
        var form = $('#save_product_model_form');
        var url = form.attr('action');
        var data = {};
        data = form.serializeArray();
        $.ajax({
            type: 'POST',
            url: url,
            data: data,
            cache: false,
            dataType:'json',
            success: function(data){
                saveProductInfoAdLock = false;
                okCallback(data);
            },
            error: errorCallback
        });
    });

    //保存sku定时上下架功能
    var saveGoodsTimeShelvesLock = false;
    $('#save_goods_time_shelves ').click(function(){
        var form = $('#save_product_time_form');
        var url = form.attr('action');
        var data = {};
        data = form.serializeArray();
        if($('#product_time_is_on_sale').find('input').val() == undefined){
            layer_error('无商品信息');
            return;
        }
        if(saveGoodsTimeShelvesLock){return false;}
        saveGoodsTimeShelvesLock = true;
        $.ajax({
            type: 'post',
            url: url,
            data: data,
            cache: false,
            dataType:'json',
            success: function(msg){
                saveGoodsTimeShelvesLock = false;
                var i= 0,len=msg.data.num;
                var show = '';
                for(i;i<len;i++){
                    $('.select-sale-pc-'+msg.data[i]['sku_id']).find("option").each(function(){
                        if($(this).val() != msg.data[i]['sale']['is_on_sale']){
                            $(this).prop("selected",false);
                        }else{
                            $(this).prop("selected",true);
                        }
                    });
                    $('.select-sale-app-'+msg.data[i]['sku_id']).find("option").each(function(){
                        if($(this).val() != msg.data[i]['sale']['sale_timing_app']){
                            $(this).prop("selected",false);
                        }else{
                            $(this).prop("selected",true);
                        }
                    });
                    $('.select-sale-wap-'+msg.data[i]['sku_id']).find("option").each(function(){
                        if($(this).val() != msg.data[i]['sale']['sale_timing_wap']){
                            $(this).prop("selected",false);
                        }else{
                            $(this).prop("selected",true);
                        }
                    });
                    show += msg.data[i]['sku_id']+',';
                }
                if(!show){
                    layer_required(msg.info);
                }else{
                    layer_required(show+'无主图，无法执行上架操作');
                }
                return;
            }
        });

    });

    $(document).keypress(function (e) {
        if( e.which == 13 ){
            return false;
        }
    });

    //编辑器
    var editor;
    KindEditor.ready(function(K) {
        editor = K.create('#skuinfo_desc', {
            height: "280px",
            allowFileManager: true,
            uploadJson: '/sku/upload',
            //imageSizeLimit : '24KB', //批量上传图片单张最大容量
            items : ['source','formatblock','fontname','fontsize','forecolor','hilitecolor','bold','italic','underline','strikethrough','removeformat','justifyleft','justifycenter','justifyright','plainpaste','wordpaste','link','unlink','image','multiimage','clearhtml','fullscreen'],
            afterBlur: function(){this.sync();},
            afterCreate : function() {
                this.loadPlugin('autoheight');
            },
            afterUpload: function(url){
                //console.log(url)
            }
        });
    });

    //点击切换上下架状态
    $(document).on('change','.product_time_form_is_on_sale',function(){
        var type = $(this).val();
        var spu_id = $('#spu_id').val();
        var data = { 'type':type,'spu_id':spu_id};
        if( type == 2 ){
            var ids = 'sku_id';
        }else{
            var ids = 'id';
        }
        $.ajax({
            type: 'post',
            url: '/sku/getIsOnSale',
            data: data,
            cache: false,
            dataType:'json',
            success: function(msg){
                if(msg) {
                    if(msg.status == 'error'){
                        layer_required(msg.info);
                        return;
                    }
                    if(msg.allTime != 1 && msg.allTime != 2){
                        var i = 0;
                        var len = msg.length;
                        for (i; i < len; i++) {
                            $('.select-sale-pc-' + msg[i][ids]).find('option[value=' + msg[i]['is_on_sale'] + ']').prop("selected", msg[i]['is_on_sale']);
                            $('.select-sale-app-' + msg[i][ids]).find('option[value=' + msg[i]['sale_timing_app'] + ']').prop("selected", msg[i]['sale_timing_app']);
                            $('.select-sale-wap-' + msg[i][ids]).find('option[value=' + msg[i]['sale_timing_wap'] + ']').prop("selected", msg[i]['sale_timing_wap']);
                            $('.select-sale-wechat-' + msg[i][ids]).find('option[value=' + msg[i]['sale_timing_wechat'] + ']').prop("selected", msg[i]['sale_timing_wechat']);
                        }
                    }else{
                        //定时上下架
                        var i = 0;
                        var len = msg.sku.length;
                        var act = msg.time;
                        for (i; i < len; i++) {//layer_required(msg.sku[i]['is_on_sale']);
                            var str = '<span class="allTime">所有端<em class="span_red">(PC端';
                            if(msg.sku[i]['is_on_sale'] != undefined && msg.sku[i]['is_on_sale'] == 1){
                                str += '已上架';
                            }else{
                                str += '未上架'
                            }
                            str += ',APP端';
                            if(msg.sku[i]['sale_timing_app'] != undefined && msg.sku[i]['sale_timing_app'] == 1){
                                str += '已上架';
                            }else{
                                str += '未上架'
                            }
                            str += ',WAP端';
                            if(msg.sku[i]['sale_timing_wap'] != undefined && msg.sku[i]['sale_timing_wap'] == 1){
                                str += '已上架';
                            }else{
                                str += '未上架'
                            }
                            str += ',微商城';
                            if(msg.sku[i]['sale_timing_wechat'] != undefined && msg.sku[i]['sale_timing_wechat'] == 1){
                                str += '已上架';
                            }else{
                                str += '未上架'
                            }
                            str += ')</em></span><span class="eachTime">PC端<em class="span_red">(';
                            if(msg.sku[i]['is_on_sale'] != undefined && msg.sku[i]['is_on_sale'] == 1){
                                str += '已上架';
                            }else{
                                str += '未上架';
                            }
                            str += ')</em></span><span class="eachTime">APP端<em class="span_red">(';
                            if(msg.sku[i]['sale_timing_app'] != undefined && msg.sku[i]['sale_timing_app'] == 1){
                                str += '已上架';
                            }else{
                                str += '未上架';
                            }
                            str += ')</em></span><span class="eachTime">WAP端<em class="span_red">(';
                            if(msg.sku[i]['sale_timing_wap'] != undefined && msg.sku[i]['sale_timing_wap'] == 1){
                                str += '已上架';
                            }else{
                                str += '未上架';
                            }
                            str += ')</em></span><span class="eachTime">微商城<em class="span_red">(';
                            if(msg.sku[i]['sale_timing_wechat'] != undefined && msg.sku[i]['sale_timing_wechat'] == 1){
                                str += '已上架';
                            }else{
                                str += '未上架';
                            }
                            str += ')</em></span>';
                            $('#time_goods_check_'+ msg.sku[i]['id'] + ' .show_sku_information .current_left').html(str);
                            if(act == false){
                                msg.time = new Array();
                            }
                            if(msg.time[msg.sku[i]['id']] == undefined){
                                msg.time[msg.sku[i]['id']] = new Array();
                                msg.time[msg.sku[i]['id']]['time_start'] = false;
                                msg.time[msg.sku[i]['id']]['time_end'] = false;
                                msg.time[msg.sku[i]['id']]['time_start_pc'] = false;
                                msg.time[msg.sku[i]['id']]['time_start_app'] = false;
                                msg.time[msg.sku[i]['id']]['time_start_wap'] = false;
                                msg.time[msg.sku[i]['id']]['time_start_wechat'] = false;
                                msg.time[msg.sku[i]['id']]['time_end_pc'] = false;
                                msg.time[msg.sku[i]['id']]['time_end_app'] = false;
                                msg.time[msg.sku[i]['id']]['time_end_wap'] = false;
                                msg.time[msg.sku[i]['id']]['time_end_wechat'] = false;
                            }
                            //修改定时上下架时间
                            $('#time_goods_check_' + msg.sku[i]['id'] + ' .allTime .time_start').val(msg.time[msg.sku[i]['id']]['time_start']?msg.time[msg.sku[i]['id']]['time_start']:'');
                            $('#time_goods_check_' + msg.sku[i]['id'] + ' .allTime .time_end').val(msg.time[msg.sku[i]['id']]['time_end']?msg.time[msg.sku[i]['id']]['time_end']:'');

                            $('#time_goods_check_' + msg.sku[i]['id'] + ' .eachTime .time_start_pc').val(msg.time[msg.sku[i]['id']]['time_start_pc']?msg.time[msg.sku[i]['id']]['time_start_pc']:'');
                            $('#time_goods_check_' + msg.sku[i]['id'] + ' .eachTime .time_start_app').val(msg.time[msg.sku[i]['id']]['time_start_app']?msg.time[msg.sku[i]['id']]['time_start_app']:'');
                            $('#time_goods_check_' + msg.sku[i]['id'] + ' .eachTime .time_start_wap').val(msg.time[msg.sku[i]['id']]['time_start_wap']?msg.time[msg.sku[i]['id']]['time_start_wap']:'');
                            $('#time_goods_check_' + msg.sku[i]['id'] + ' .eachTime .time_start_wechat').val(msg.time[msg.sku[i]['id']]['time_start_wechat']?msg.time[msg.sku[i]['id']]['time_start_wechat']:'');
                            $('#time_goods_check_' + msg.sku[i]['id'] + ' .eachTime .time_end_pc').val(msg.time[msg.sku[i]['id']]['time_end_pc']?msg.time[msg.sku[i]['id']]['time_end_pc']:'');
                            $('#time_goods_check_' + msg.sku[i]['id'] + ' .eachTime .time_end_app').val(msg.time[msg.sku[i]['id']]['time_end_app']?msg.time[msg.sku[i]['id']]['time_end_app']:'');
                            $('#time_goods_check_' + msg.sku[i]['id'] + ' .eachTime .time_end_wap').val(msg.time[msg.sku[i]['id']]['time_end_wap']?msg.time[msg.sku[i]['id']]['time_end_wap']:'');
                            $('#time_goods_check_' + msg.sku[i]['id'] + ' .eachTime .time_end_wechat').val(msg.time[msg.sku[i]['id']]['time_end_wechat']?msg.time[msg.sku[i]['id']]['time_end_wechat']:'');
                        }
                    }

                }

                if( type == 2 ){
                    $('.time-shelves').removeClass('hide');
                    $('#product_time_is_on_sale').addClass('hide');
                    var aa = $('.setTimeEnd').val();
                    if(aa == 2){
                        $('.eachTime').removeClass('hide');
                        $('.allTime').addClass('hide');
                    }else{
                        $('.eachTime').addClass('hide');
                        $('.allTime').removeClass('hide');
                    }
                }else{
                    $('.time-shelves').addClass('hide');
                    $('#product_time_is_on_sale').removeClass('hide');
                }
            }
        });
    });

    //切换定时上下架所有端|各个端
    $(document).on('change','.setTimeEnd',function(){
        var aa = $(this).val();
        if(aa == 2){
            $('.eachTime').removeClass('hide');
            $('.allTime').addClass('hide');
        }else{
            $('.eachTime').addClass('hide');
            $('.allTime').removeClass('hide');
        }
    });

    //点击确定是否设置定时上下架
    $(document).on('change','.click_is_on_sale',function(){
        var is_on_sale = $('.product_time_form_is_on_sale:checked').val();
        if( is_on_sale == 2 ){
            var id = $(this).val();
            if( $(this).prop("checked") ){
                $('.time-shelves_'+id+' .time-start').attr('name','time_start[]');
                $('.time-shelves_'+id+' .time-end').attr('name','time_end[]');
            }else{
                $('.time-shelves_'+id+' .time-start').removeAttr('name');
                $('.time-shelves_'+id+' .time-end').removeAttr('name');
            }
        }
    });

    //提交说明书数据
    var saveProInstruceLock = false;
    $('.save_product_instruction').click(function(){
        var id = $('#goods_instruction_id').val();
        if(!parseInt(id)){
            layer_required('请选择要修改的说明书');
            return;
        }
        if(saveProInstruceLock){return false;}
        saveProInstruceLock = true;
        var form = $('#save_product_instruction_form');
        var url = form.attr('action');
        var data = {};
        data = form.serializeArray();
        $.ajax({
            type: 'POST',
            url: url,
            data: data,
            cache: false,
            dataType:'json',
            success: function(data){
                saveProInstruceLock = false;
                okCallback(data);
            },
            error: errorCallback
        });
    });

    //退货切换
    $('.returned_goods').change(function(){
        if(act = $(this).val() == 1){
            $(this).parent().children('.returned_goods_value').removeClass('hide');
        }else{
            $(this).parent().children('.returned_goods_value').addClass('hide');
        }
    });
})

//获取数据
function getSkuInfo(type,skuId,spuId){
    var data = {'type':type,'skuId':skuId,'spuId':spuId};
    $.ajax({
        type: 'post',
        url: '/spu/getMobile',
        data: data,
        cache: false,
        dataType:'json',
        success: function(msg){
            if(msg){
                if(msg.status == 'error'){
                    layer_required(msg.info);
                    return;
                }
                //写入数据
                if(!msg['info']){
                    msg['info'] = new Array;
                    msg['info']['sku_detail_'+type] = '';
                }
                KindEditor.html('#skuinfo_desc', msg['info']['sku_detail_'+type]);
                var ad = '',
                    btomAd = '';
                var i = 0,
                    len = msg['ad'].length;
                for( i ; i < len ; i++ ){
                    ad += '<div class="ad-list"><div id="' +
                        msg['ad'][i]['id'] + '"> <input name="ad[]" value="' +
                        msg['ad'][i]['id'] + '" type="hidden"><div class="ad-list-title">模板名称：<span>' +
                        msg['ad'][i]['ad_name'] + '</span><a href="/sku/adedit?id=' +
                        msg['ad'][i]['id'] + '&isLimit=1" target="_blank">修改</a><a href="javascript:;" class="delect-model">删除</a> </div><div class="ad-list-img"><img src="assets/images/gallery/image-1.jpg" alt=""><img src="assets/images/gallery/image-1.jpg" alt=""><img src="assets/images/gallery/image-1.jpg" alt=""></div></div></div>';
                }

                var i = 0,
                    len = msg['btomAd'].length;
                for( i ; i < len ; i++ ){
                    btomAd += '<div class="ad-list"><div id="' +
                        msg['btomAd'][i]['id'] + '"> <input name="btom-ad[]" value="' +
                        msg['btomAd'][i]['id'] + '" type="hidden"><div class="ad-list-title">模板名称：<span>' +
                        msg['btomAd'][i]['ad_name'] + '</span><a href="/sku/adedit?id=' +
                        msg['btomAd'][i]['id'] + '&isLimit=1" target="_blank">修改</a><a href="javascript:;" class="delect-model">删除</a> </div><div class="ad-list-img"><img src="assets/images/gallery/image-1.jpg" alt=""><img src="assets/images/gallery/image-1.jpg" alt=""><img src="assets/images/gallery/image-1.jpg" alt=""></div></div></div>';
                }
                $('#ad_model_s').find('.ad-list').empty();
                $('#ad_model_s').prepend(ad);
                $('#ad_model_x').find('.ad-list').empty();
                $('#ad_model_x').prepend(btomAd);
            }
        }
    });
}

//图片拖拽
$(".list").dragsort({ dragSelector: "img", dragBetween: false,  dragEnd : saveOrder, placeHolderTemplate: "<li class='placeHolder'><div></div></li>" });

function saveOrder() {
    var data={};
    var i = 0;
    $(this).parent().parent().find('li').each(function(){
        data[i] = $(this).attr('data-id');
        i++;
    });
    //console.log(data);
    $.ajax({
        type: 'post',
        url: '/sku/setSkuImgSort',
        data: data,
        cache: false,
        dataType:'json',
        success: function(msg){
            if(msg){
                //console.log(msg);
            }
        }
    });
};

//默认图片上传
function fileSelect(id) {
    var id = id;
    $('#form_face_img input[name="id"]').val(id);
    document.getElementById("fileToUpload").click();
}

function fileSelected() {
    // 文件选择后触发次函数
    var id = $('#form_face_img input[name="id"]').val();
    var spu_id = 0;
    if( id == 0 ){
        var spu_id = $('#spu_id').val();
    }
    uploadFileMain('/sku/uploadMianImg?id='+id+'&spu_id='+spu_id, 'fileToUpload',id);
}


