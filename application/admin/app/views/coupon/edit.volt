{% extends "layout.volt" %}

{% block content %}
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/jquery.datetimepicker.css" class="ace-main-stylesheet" />
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/admin/css/coupon_addon.css" class="ace-main-stylesheet" />
<style>
    .col-sm-3{
        width: 16.666%;
    }
</style>
<div class="page-content">

    <!-- /.page-header -->
    <div class="row">
        <div class="col-xs-12">
            <!-- PAGE CONTENT BEGINS -->
            <form class="form-horizontal" role="form" id="edit_coupon" method="post" action="/coupon/edit" enctype="multipart/form-data" >
                <div class="page-header">
                    <h1>基本信息</h1>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right" for="coupon_name" ><span  class="text-red">*</span> 优惠券名称 </label>
                    <div class="col-sm-5">
                        <input type="text" id="coupon_name" name="coupon_name" placeholder="此处输入优惠券名称" class="col-xs-10 col-sm-5" value="{{getEditCouponByParam['coupon_name']}}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right" for="start_provide_time" ><span  class="text-red">*</span> 开始发放时间 </label>
                    <div class="col-sm-5">
                        <input type="text" id="start_provide_time" name="start_provide_time" class="col-xs-10 col-sm-5 datetimepk " value="{{date('Y-m-d H:i:s',getEditCouponByParam['start_provide_time'])}}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right" for="end_provide_time" > <span  class="text-red">*</span>结束发放时间 </label>
                    <div class="col-sm-5">
                        <input type="text" id="end_provide_time" name="end_provide_time" class="col-xs-10 col-sm-5 datetimepk " value="{{date('Y-m-d H:i:s',getEditCouponByParam['end_provide_time'])}}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right" for="validitytype" ><span  class="text-red">*</span> 有效期类型 </label>
                    <div class="col-sm-2">
                        <select class="form-control" name="validitytype" id="validitytype">
                            <option value="1" {% if getEditCouponByParam['validitytype'] == "1" %} selected {% endif %}>绝对有效期</option>
                            <option value="2" {% if getEditCouponByParam['validitytype'] == "2" %} selected {% endif %}>相对有效期</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" id="hd_relative_validity" {% if getEditCouponByParam['validitytype'] == "1" %} hidden {% endif %}>
                    <label class="col-sm-2 control-label no-padding-right" for="relative_validity" > <span  class="text-red">*</span>有效期时间 </label>
                    <div class="col-sm-5 relative_validity">
                        <div class="c_left">领取</div>
                        <div>
                            <input type="text" id="relative_validity" name="relative_validity" class="col-xs-10 col-sm-5 " {% if getEditCouponByParam['validitytype'] == "2" %} value="{{getEditCouponByParam['relative_validity']}}" {% endif %} >
                        </div>
                        <div class="c_right">天后过期</div>
                    </div>
                </div>
                <div class="form-group" id="start_use_time_form" {% if getEditCouponByParam['validitytype'] == "2" %}hidden{% endif %}>
                    <label class="col-sm-2 control-label no-padding-right" for="start_use_time" ><span  class="text-red">*</span> 开始使用时间 </label>
                    <div class="col-sm-5">
                        <input type="text" id="start_use_time" name="start_use_time" class="col-xs-10 col-sm-5 datetimepk "  {% if getEditCouponByParam['validitytype'] == "1" %}value="{{date('Y-m-d H:i:s',getEditCouponByParam['start_use_time'])}}"{% endif %}>
                    </div>
                </div>
                <div class="form-group" id="end_use_time_form" {% if getEditCouponByParam['validitytype'] == "2" %}hidden{% endif %}>
                    <label class="col-sm-2 control-label no-padding-right" for="end_use_time" ><span  class="text-red">*</span> 结束使用时间 </label>
                    <div class="col-sm-5">
                        <input type="text" id="end_use_time" name="end_use_time" class="col-xs-10 col-sm-5 datetimepk " {% if getEditCouponByParam['validitytype'] == "1" %}value="{{date('Y-m-d H:i:s',getEditCouponByParam['end_use_time'])}}"{% endif %}>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right" ><span  class="text-red">*</span> 活动平台</label>
                    <div class="col-sm-5">
                        <div class="checkbox">
                            {% if shopPlatform is defined and shopPlatform is not empty %}
                                {% for key,platform in shopPlatform %}
                                    {% set selPlatform = key~'_platform' %}
                                    <label>
                                        <input name="use_platform[]" type="checkbox" class="ace" value="{% if key === 'pc' %}1{% elseif key === 'app' %}2{% elseif key === 'wechat' %}4{% else %}3{% endif %}" {% if getEditCouponByParam[selPlatform] == "1" %}checked{% endif %}>
                                        <span class="lbl">&nbsp;{{ platform }}</span>
                                    </label>
                                {% endfor %}
                            {% endif %}
                        </div>
                    </div>
                </div>
                <input type="hidden" id="use_platform_hidden">
                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right" for="coupon_description" ><span  class="text-red">*</span> 活动说明 </label>
                    <div class="col-sm-5">
                        <textarea id="coupon_description" name="coupon_description" class="autosize-transition form-control" style="overflow: hidden; word-wrap: break-word; resize: horizontal; height: 52px;">{{getEditCouponByParam['coupon_description']}}</textarea>
                    </div>
                </div>
                <div class="page-header">
                    <h1>优惠信息</h1>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right" for="provide_type" ><span  class="text-red">*</span> 发放方式 </label>
                    <div class="col-sm-2">
                        <select class="form-control" name="provide_type" id="provide_type">
                            <option value="1" {% if getEditCouponByParam['provide_type'] == "1" %}selected{% endif %}>线上优惠券</option>
                            <option value="2" {% if getEditCouponByParam['provide_type'] == "2" %}selected{% endif %}>统一码</option>
                            <option value="3" {% if getEditCouponByParam['provide_type'] == "3" %}selected{% endif %}>激活码</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right" > <span  class="text-red">*</span>优惠券类型 </label>
                    <div class="col-sm-5">
                        <div class="radio">
                            <label>
                                <input name="coupon_type" type="radio" class="ace" value="1" {% if getEditCouponByParam['coupon_type'] == "1" %}checked{% endif %}>
                                <span class="lbl">&nbsp;满减券</span>
                            </label>
                            <label>
                                <input name="coupon_type" type="radio" class="ace" value="2" {% if getEditCouponByParam['coupon_type'] == "2" %}checked{% endif %}>
                                <span class="lbl">&nbsp;折扣券</span>
                            </label>
                            <label>
                                <input name="coupon_type" type="radio" class="ace" value="3" {% if getEditCouponByParam['coupon_type'] == "3" %}checked{% endif %} disabled>
                                <span class="lbl">&nbsp;包邮券</span>
                            </label>
                        </div>

                    </div>
                </div>
                <div id="vt_area_a"></div>

                <div class="form-group {% if shopPlatform['app'] is not defined %}hide{% endif %}">
                    <label class="col-sm-2 control-label no-padding-right" for="app_url" > APP链接 </label>
                    <div class="col-sm-5">
                        <input type="text" id="app_url" name="app_url" placeholder="此处输入APP链接" class="col-xs-10 col-sm-9 checkURL" style="margin-right:50px;" value="{{getEditCouponByParam['app_url']}}">
                        <span class="text-red">如:App://type=6&&&value=商品名称 </span>
                    </div>
                </div>
                <div class="form-group {% if shopPlatform['wap'] is not defined %}hide{% endif %}">
                    <label class="col-sm-2 control-label no-padding-right" for="wap_url" > WAP链接 </label>
                    <div class="col-sm-5">
                        <input type="text" id="wap_url" name="wap_url" placeholder="此处输入WAP链接" class="col-xs-10 col-sm-9 checkURL" style="margin-right:50px;" value="{{getEditCouponByParam['wap_url']}}">
                        <span  class="text-red">如:http://www.baidu.com</span>
                    </div>
                </div>
                <div class="form-group {% if shopPlatform['pc'] is not defined %}hide{% endif %}">
                    <label class="col-sm-2 control-label no-padding-right" for="pc_url" > PC链接 </label>
                    <div class="col-sm-5">
                        <input type="text" id="pc_url" name="pc_url" placeholder="此处输入PC链接" class="col-xs-10 col-sm-9 checkURL" style="margin-right:50px;" value="{{getEditCouponByParam['pc_url']}}">
                        <span  class="text-red">如:http://www.baidu.com</span>
                    </div>
                </div>
                <div class="form-group {% if shopPlatform['wechat'] is not defined %}hide{% endif %}">
                    <label class="col-sm-2 control-label no-padding-right" for="wechat_url" > 微商城链接 </label>
                    <div class="col-sm-5">
                        <input type="text" id="wechat_url" name="wechat_url" placeholder="此处输入微商城链接" class="col-xs-10 col-sm-9 checkURL" style="margin-right:50px;" value="{{getEditCouponByParam['wechat_url']}}">
                        <span  class="text-red">如:http://www.baidu.com</span>
                    </div>
                </div>

                <div class="page-header">
                    <h1>条件信息</h1>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right" for="channel_id" > <span  class="text-red">*</span>发放渠道 </label>
                    <div class="col-sm-2">
                        {% if CpsChannelList is defined %}
                            <select class="form-control" name="channel_id" id="channel_id">
                                {% for v in CpsChannelList %}
                                    <option value="{{ v['channel_id'] }}" {% if getEditCouponByParam['channel_id'] == v['channel_id'] %}selected{% endif %}>{{ v['channel_name'] }}</option>
                                {% endfor %}
                            </select>
                        {% else %}
                            <select class="form-control" name="channel_id" id="channel_id">
                                <option value="0">本站</option>
                            </select>
                        {% endif %}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right" for="coupon_number" > <span  class="text-red">*</span>发放张数 </label>
                    <div class="col-sm-5 limitC">
                        <div>
                            <input type="text" id="coupon_number" name="coupon_number" class="col-xs-10 col-sm-5 " value="{{getEditCouponByParam['coupon_number']}}" placeholder="请输入正整数数值">
                        </div>
                        <div class="c_right">张</div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right" for="limit_number" ><span  class="text-red">*</span> 每人限领取 </label>
                    <div class="col-sm-5 limitC">
                        <div>
                            <input type="text" id="limit_number" name="limit_number" class="col-xs-10 col-sm-5 " value="{{getEditCouponByParam['limit_number']}}" placeholder="请输入正整数数值">
                        </div>
                        <div class="c_right">张</div>

                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right" for="medicine_type" > <span  class="text-red">*</span>适用药品类型 </label>
                    <div class="col-sm-2">
                        <select class="form-control" name="medicine_type" id="medicine_type">
                            {% if promotionEnum['drugType'] is defined %}
                            {% for k,v in promotionEnum['drugType'] %}
                            <option value="{{k}}" {% if getEditCouponByParam['drug_type'] == k %}selected{% endif %}>{{v}}</option>
                            {% endfor %}
                            {% endif %}
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right" for="group" > <span  class="text-red">*</span>使用人群 </label>
                    <div class="col-sm-2">
                        <select class="form-control" name="group" id="group">
                            <option value="0" {% if getEditCouponByParam['group_set'] == "0" %}selected{% endif %}>所有人</option>
                            <option value="1" {% if getEditCouponByParam['group_set'] == "1" %}selected{% endif %}>新会员</option>
                            <option value="2" {% if getEditCouponByParam['group_set'] == "2" %}selected{% endif %}>老会员</option>
                            <option value="3" {% if getEditCouponByParam['group_set'] == "3" %}selected{% endif %}>指定会员</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right" for="goods_tag" > <span  class="text-red">*</span>会员标签 </label>
                    <div class="col-sm-2">
                        <select class="form-control" name="goods_tag" id="goods_tag">
                            <option value="0" {% if getEditCouponByParam['goods_tag_id'] == '0' %}selected{% endif %}>不指定</option>
                            {% if GoodsTagList is defined %}
                            {% for k,v in GoodsTagList %}
                            <option value="{{ v['tag_id'] }}" {% if getEditCouponByParam['goods_tag_id'] == v['tag_id'] %}selected{% endif %}>{{ v['tag_name'] }}</option>
                            {% endfor %}
                            {% endif %}
                        </select>
                    </div>
                </div>
                <div id="is_register_bonus" class="form-group" {% if getEditCouponByParam['group_set'] == "3" %}hidden{% endif %}>
                    <label class="col-sm-2 control-label no-padding-right" > 是否注册发放 </label>
                    <div class="col-sm-5">
                        <div class="radio">
                            <label>
                                <input name="register_bonus" type="radio" class="ace" value="1" {% if getEditCouponByParam['register_bonus'] == "1" %}checked{% endif %}>
                                <span class="lbl">&nbsp;是</span>
                            </label>
                            <label>
                                <input name="register_bonus" type="radio" class="ace" value="0" {% if getEditCouponByParam['register_bonus'] == "0" %}checked{% endif %}>
                                <span class="lbl">&nbsp;否</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div id="is_pl" class="form-group">
                    <label class="col-sm-2 control-label no-padding-right" for="group" > <span  class="text-red">*</span>设为可赠送优惠券 </label>
                    <div class="col-sm-2">
                        <select class="form-control" name="is_present" id="is_present">
                            <option value="0" {% if getEditCouponByParam['is_present'] == "0" %}selected{% endif %}>否</option>
                            <option value="1" {% if getEditCouponByParam['is_present'] == "1" %}selected{% endif %}>是</option>
                        </select>
                    </div>
                </div>
                <div id="special_add" {% if getEditCouponByParam['group_set'] != "3" %}style="display: none;"{% endif %}>
                    <div class="form-group">
                        <label class="col-sm-2 control-label no-padding-right" for="add_tel" > 添加账号 </label>
                        <div class="col-sm-5">
                            <input type="text" id="add_tel" class="col-xs-10 col-sm-5 " placeholder="请输入手机号码">
                            <button type="button" id="add_tag_btn" class="btn btn-sm btn-yellow">添加</button>
                            <button type="button" id="import_btn" class="btn btn-sm btn-primary">批量导入</button>
                            <button type="button" class="btn btn-sm btn-purple" onclick="location.href='http://{{ config.domain.static }}/assets/csv/example.csv'">模板下载</button>
                            <input id="tels" type="hidden" name="tels" {% if getEditCouponByParam['group_set'] == "3" %}value="{{getEditCouponByParam['tels']}}"{% endif %}>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label no-padding-right"  >  </label>
                        <div class="col-sm-9">
                            <div id="tags_area" class="tags" style="width: 630px;height:96px;border:0;overflow:auto;">
                                {% if (getEditCouponByParam['group_set'] == "3") and (getEditCouponByParam['tels'] != "") %}
                                    {% if tels_arr is defined %}
                                        {% for v in tels_arr %}
                                <span class="tag" data-tel="{{v}}">{{v}}<button type="button" class="close close_tag" onclick="close_tag($(this))">×</button></span>
                                        {% endfor %}
                                    {% endif %}
                                {% else %}

                                {% endif %}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right" for="use_range" > <span  class="text-red">*</span>使用范围 </label>
                    <div class="col-sm-2">
                        <select class="form-control" name="use_range" id="use_range">
                            {% if promotionEnum['forScope'] is defined %}
                            {% for k,v in promotionEnum['forScope'] %}
                            <option value="{{k}}" {% if getEditCouponByParam['use_range'] == k %}selected{% endif %}>{{v}}</option>
                            {% endfor %}
                            {% endif %}
                        </select>
                    </div>
                </div>
                <div id="ajax_use_range_area"></div>
                <input type="hidden" id="codeNum" value="{{ codenum }}">
                <input type="hidden" id="hiddenUseRange" name="hiddenUseRange" {% if getEditCouponByParam['use_range'] == "category" %}value="{{getEditCouponByParam['category_ids']}}"{% endif %}{% if getEditCouponByParam['use_range'] == "single" %}value="{{getEditCouponByParam['product_ids']}}"{% endif %}/>
                {% if getEditCouponByParam['use_range'] == "single" %}
                    <input type="hidden" id="single_flag"/>
                {% endif %}
                <input type="hidden" id="hiddenAddGift" name="hiddenAddGift"/>
                {% if brand_ids is defined %}
                    <input type="hidden" id="brand_ids_tmp" value="{{brand_ids}}"/>
                {% endif %}
                <div id="NotJoinActivity">

                </div>
                <div id="active_code_area"></div>
                <input type="hidden" name="id" value="{{sid}}">
                {% if getEditCouponByParam['use_range'] != "single" %}
                        <input type="hidden" id="ban_join_rule" value='{{ban_join_rule}}'/>
                {% endif %}
                <div class="clearfix form-actions">
                    <div class="col-md-offset-3 col-md-9">
                    {% if isshow == "0" %}
                    <button class="btn btn-info" type="button" id="do_ajax_sure_save_btn_tmp" disabled>
                            <i class="ace-icon fa fa-check bigger-110"></i>
                            确认
                        </button>
                    {% else %}
                    <button class="btn btn-info" type="button" id="do_ajax_sure_save_btn" >
                            <i class="ace-icon fa fa-check bigger-110"></i>
                            确认
                        </button>
                    {% endif %}
                        

                    </div>
                </div>
            </form>
            <form target="import_ifm" id="import_tel" method="post" action="/coupon/importTelList" enctype="multipart/form-data">
                <input id="import_tel_input" name="import_tel_input" type="file" style="width: 0;height: 0;">
            </form>
            <iframe id="import_ifm" name='import_ifm' style="display:none"></iframe>
            <!-- PAGE CONTENT ENDS -->
        </div><!-- /.col -->
    </div><!-- /.row -->

</div>
{% endblock %}
{% block footer %}
<script type="text/html" id="select_active_code">
    <div class="form-group" id="create_code">
        <label class="col-sm-2 control-label no-padding-right" > 生成激活码 </label>
        <div class="col-sm-5" >
            <div class="checkbox">
                <label>
                    <input name="is_activecode" type="checkbox" class="ace" value="1" >
                    <span class="lbl"></span>
                </label>
            </div>
        </div>
    </div>
</script>
<script type="text/html" id="show_active_code_num">
    <div class="form-group" id="create_code_num">
        <label class="col-sm-2 control-label no-padding-right" for="use_range" > 生成数量 </label>
        <div class="col-sm-5" >
            <input type="number" class="col-xs-10 col-sm-5 " name="create_min_num" placeholder="请输入少于发放的张数" min="0">
        </div>
    </div>
</script>
<script type="text/html" id="fullcut_vt">
    <div class="form-group">
        <label class="col-sm-2 control-label no-padding-right"><span  class="text-red">*</span> 优惠券金额 </label>
        <div class="col-sm-5 coupon">
            <input type="text"  name="coupon_value" class="col-xs-10 col-sm-5  " {% if getEditCouponByParam['coupon_type'] == "1" %}value="{{getEditCouponByParam['coupon_value']}}"{% endif %} onchange="y1($(this).val());">
            <div class="c_right">
                元
            </div>
        </div>

    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label no-padding-right" for="min_cost" ><span  class="text-red">*</span> 订单金额 </label>
        <div class="col-sm-5 coupon">
            <div class="c_left">满</div>
            <div><input type="text" name="min_cost" class="col-xs-10 col-sm-5  " {% if getEditCouponByParam['coupon_type'] == "1" %}value="{{getEditCouponByParam['min_cost']}}"{% endif %} onchange="y2($(this).val());">
                <div class="c_right">
                    元可用
                </div>
            </div>
        </div>
    </div>
</script>
<script type="text/html" id="discout_vt">
    <div class="form-group">
        <label class="col-sm-2 control-label no-padding-right"  > <span  class="text-red">*</span>优惠券折扣 </label>
        <div class="col-sm-5 coupon">
            <input type="text" name="coupon_value" class="col-xs-10 col-sm-5  "  {% if getEditCouponByParam['coupon_type'] == "2" %}value="{{getEditCouponByParam['coupon_value']}}"{% endif %} onchange="y3($(this).val());">
            <div class="c_right">
                折
            </div>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label no-padding-right" > <span  class="text-red">*</span>订单金额/商品件数 </label>
        <div class="col-sm-5 coupon">
            <div class="c_left">满</div>
            <div class="pull-left">
                <input type="text" name="min_cost" class="col-xs-10 col-sm-5  " {% if getEditCouponByParam['coupon_type'] == "2" %}value="{{getEditCouponByParam['min_cost']}}"{% endif %} onchange="y4($(this).val());">
                <select class="coupon" name="discount_unit" id="discount_unit">
                    <option value="1" {% if (getEditCouponByParam['coupon_type'] == "2") and (getEditCouponByParam['discount_unit'] == "1") %}selected{% endif %}>元</option>
                    <option value="2" {% if (getEditCouponByParam['coupon_type'] == "2") and (getEditCouponByParam['discount_unit'] == "2") %}selected{% endif %}>件</option>
                </select>
            </div>

        </div>
    </div>
</script>
<script type="text/html" id="freepacket_vt">
    <div class="form-group">
        <label class="col-sm-2 control-label no-padding-right" for="min_cost" ><span  class="text-red">*</span> 订单金额 </label>
        <div class="col-sm-5 coupon">
            <div class="c_left">满</div>
            <div>
                <input type="text" id="min_cost" name="min_cost" class="col-xs-10 col-sm-5  " {% if getEditCouponByParam['coupon_type'] == "3" %}value="{{getEditCouponByParam['min_cost']}}"{% endif %} onchange="y5($(this).val());">
                <div class="c_right">
                    元可用
                </div>
            </div>
        </div>
    </div>
</script>
<script type="text/html" id="select_category">
    <div class="form-group">
        <label class="col-sm-2 control-label no-padding-right"  > 选择品类 </label>
        <div class="col-xs-12 col-sm-9">
            {% if category_arr is defined %}
                {% for info in category_arr%}
                    {% if info["level"] == "1" %}
                        <select name="shop_category[]" id="one_category">
                            <option value="0">请选择</option>
                            {% if info['info'] is defined %}
                            {% for cat in info['info'] %}
                                <option value="{{cat['id']}}" {% if cat['id'] == info["cid"] %}selected{% endif %}>{{cat['category_name']}}</option>
                            {% endfor %}
                            {% endif %}
                        </select>
                    {% endif %}
                    {% if info["level"] == "2" %}
                        <select name="shop_category[]" id="two_category">
                            <option value="0">请选择</option>
                            {% if info['info'] is defined %}
                            {% for cat in info['info'] %}
                            <option value="{{cat['id']}}" {% if cat['id'] == info["cid"] %}selected{% endif %}>{{cat['category_name']}}</option>
                            {% endfor %}
                            {% endif %}
                        </select>
                    {% endif %}
                    {% if info["level"] == "3" %}
                        <select name="shop_category[]" id="three_category">
                            <option value="0">请选择</option>
                            {% if info['info'] is defined %}
                            {% for cat in info['info'] %}
                            <option value="{{cat['id']}}" {% if cat['id'] == info["cid"] %}selected{% endif %}>{{cat['category_name']}}</option>
                            {% endfor %}
                            {% endif %}
                        </select>
                    {% endif %}
                {% endfor %}
            {% else %}
            <select name="shop_category[]" id="one_category">
                <option value="0">请选择</option>
                {% if category is defined %}
                {% for k,v in category %}
                <option value="{{v['id']}}">{{v['category_name']}}</option>
                {% endfor %}
                {% endif %}
            </select>
            <select name="shop_category[]" id="two_category" style="display: none;">

            </select>
            <select name="shop_category[]" id="three_category" style="display: none;">

            </select>
            {% endif %}
        </div>
    </div>
</script>
<script src="http://{{ config.domain.static }}/assets/js/jquery.datetimepicker.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/jquery.validate.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/jquery.autosize.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/admin/js/promotion/promotion-public.js?v=<?php echo time();?>"></script>
<script src="http://{{ config.domain.static }}/assets/admin/js/coupon/main.js?v=<?php echo time();?>"></script>
{% endblock %}