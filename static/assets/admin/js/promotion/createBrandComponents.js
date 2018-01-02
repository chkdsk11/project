function createBrandComponents(ele){
    //生成uuid附加到Class里 防止组件交叉Crash
    var pre=function() {
        var s = [];
        var hexDigits = "0123456789abcdef";
        for (var i = 0; i < 36; i++) {
            s[i] = hexDigits.substr(Math.floor(Math.random() * 0x10), 1);
        }
        s[14] = "4";
        s[19] = hexDigits.substr((s[19] & 0x3) | 0x8, 1);
        s[8] = s[13] = s[18] = s[23] = "-";
        var uuid = s.join("");
        return uuid;
    }();

    //生成基础html代码
    var base_html='<div class=form-group><label class="col-sm-3 control-label no-padding-right">添加品牌</label><div class=col-sm-5><div class="input-group append_area_'+pre+'"><input class="form-control col-sm-3 search_goods_input_'+pre+'" placeholder="品牌ID/名称" type=text><span class=input-group-btn><button class="btn btn-sm btn-success do_search_btn_'+pre+'" type=button>搜索</button></span></div></div></div><div class=form-group><label class="col-sm-3 control-label no-padding-right"></label><div class=col-sm-5><table class="table table-striped table-bordered table-hover"><thead><tr><th>品牌id</th><th>品牌名称</th><th>操作</th><tbody class="brand_table_body_'+pre+'"></table></div></div>';
    
    $(ele).html(base_html);

    $(".do_search_btn_"+pre).on("click", function () {
        var search_goods_input=$(".search_goods_input_"+pre).val();
        if(search_goods_input.length>0){
            $.post("/coupon/getBrandSearchComponents",{input:search_goods_input}, function (output) {
                if(output!="0"){
                    var obj= $.parseJSON(output);
                    if($('.select_brand_list_'+pre)[0]){
                        $('.select_brand_list_'+pre).html("");
                        $.each(obj, function (n,v) {
                            $('.select_brand_list_'+pre).append('<option value="'+ v["id"]+'">'+ v["brand_name"]+'</option>');
                        });
                    }else{
                        $('.append_area_'+pre).append('<input type="hidden" class="hiddenBrandids_'+pre+'" name="shop_brand"><select class="form-control select_brand_list_'+pre+'" ></select><span class="input-group-btn"><button class="btn btn-sm btn-danger do_select_btn_'+pre+'" type="button">确定</button></span>');
                        $.each(obj, function (n,v) {
                            $('.select_brand_list_'+pre).append('<option value="'+ v["id"]+'">'+ v["brand_name"]+'</option>');
                        });
                    }
                    $(".do_select_btn_"+pre).on("click", function () {
                        var brandID=$('.select_brand_list_'+pre).val();
                        var brandName= $.trim($('.select_brand_list_'+pre).find("option:selected").text());
                        var _flag=1;
                        $(".brand_table_body_"+pre+" tr").find("td:eq(0)").each(function () {
                            var v=$(this).html();
                            if(brandID==v){
                                _flag=0;
                                layer_required("不能添加相同品牌");
                            }
                        })
                        if(_flag==1){
                            $(".brand_table_body_"+pre).append("<tr><td>"+brandID+"</td><td>"+brandName+"</td><td><a class='del' href='javascript:void(0);'>删除</a></td></tr>");
                            var _tempPool=[];
                            $(".brand_table_body_"+pre+" tr").find("td:eq(0)").each(function () {
                                var v=$(this).html();
                                _tempPool.push(v);
                            })
                            $(".hiddenBrandids_"+pre).val(_tempPool.join(","));
                            $(".brand_table_body_"+pre+" tr td a.del").on("click", function () {
                                $(this).parent().parent().remove();
                                if($(".brand_table_body_"+pre+" tr")[0]){
                                    var _tempPool=[];
                                    $(".brand_table_body_"+pre+" tr").find("td:eq(0)").each(function () {
                                        var v=$(this).html();
                                        _tempPool.push(v);
                                    })
                                    $(".hiddenBrandids_"+pre).val(_tempPool.join(","));
                                }else{
                                    $(".hiddenBrandids_"+pre).val("");
                                }
                            })
                        }

                    })
                }
            })
        }else{
            layer_required("输入不能为空");
        }

    })
}