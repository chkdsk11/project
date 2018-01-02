{% extends "layout.volt" %}
{% block path %}
    <li class="active">促销管理</li>
    <li class="active"><a href="/groupact/list">拼团管理</a></li>
    <li class="active">添加拼团活动</li>
{% endblock %}
{% block content %}
    <link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/jquery.datetimepicker.css" class="ace-main-stylesheet" />
    <link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/product_spu.css" />
    <style type="text/css">
        img{width:140px; display:none;}
        .text-red{ color: red;}
    </style>
    <div class="main-container" id="main-container">
        <div class="main-content">
            <div class="main-content-inner">
                <form id="myform" action="" method="post">
                    <div class="page-content">
                        <div class="page-header">
                            <h1>
                                活动信息
                            </h1>
                        </div>
                        <div class="row">
                            <div class="col-xs-12">
                                <!-- PAGE CONTENT BEGINS -->
                                <div class="form-horizontal">
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label no-padding-right"><span class="text-red">*</span>活动名称：</label>
                                        <div class="col-sm-9">
                                            <input type="text" id="" name="gfa_name" class="col-xs-10 col-sm-5" placeholder="不可为空" />
                                            <span class="tigs" id=""></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label no-padding-right"> <span class="text-red">*</span>开始时间 </label>
                                        <div class="col-sm-9">
                                            <input type="text" id="start_time" name="gfa_starttime" class="col-xs-10 col-sm-5" placeholder="不可为空" readonly/>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label no-padding-right"> <span class="text-red">*</span>结束时间 </label>
                                        <div class="col-sm-9">
                                            <input type="text" id="end_time" name="gfa_endtime" class="col-xs-10 col-sm-5" placeholder="不可为空" readonly/>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label no-padding-right"><span class="text-red">*</span>成团人数：</label>
                                        <div class="col-sm-9">
                                            <input type="text" id="" name="gfa_user_count" class="col-xs-10 col-sm-5" placeholder="不可为空" />
                                            <span class="tigs" id="">（2~10人，必填）</span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label no-padding-right"><span class="text-red">*</span>组团周期：</label>
                                        <div class="col-sm-9">
                                            <input type="text" id="" name="gfa_cycle" class="col-xs-10 col-sm-5" placeholder="不可为空" />
                                            <span class="tigs" id="">小时（整数）</span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label no-padding-right" for="promotion_type"><span  class="text-red">*</span>参团用户类型：</label>
                                        <div class="col-xs-12 col-sm-9">
                                            <select name="gfa_user_type">
                                                <option value="0">新老用户</option>
                                                <option value="1">新用户</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label no-padding-right"><span class="text-red">*</span>用户参加次数：</label>
                                        <div class="col-sm-9">
                                            <input type="text" id="" name="gfa_allow_num" class="col-xs-10 col-sm-5" placeholder="不可为空" />
                                            <span class="tigs" id="">填写0，表示不限制参加的次数</span>
                                        </div>
                                    </div>
                                    <!-- 是否抽奖start -->
                                    <div class="form-group" id="checkdraw">
                                        <label class="col-sm-3 control-label no-padding-right"> <span class="text-red">*</span> 用户参与抽奖：</label>
                                        <div class="col-xs-12 col-sm-9">
                                            <label class="radio-inline">
                                                <input type="radio" name="gfa_type" value="1">是
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" name="gfa_type" value="0" checked>否
                                            </label>
                                        </div>
                                    </div>
                                    <!-- 中奖方式切换 -->
                                    <div id="draw" style="display:none;">
                                        <div class="form-group" id="drawway">
                                            <label class="col-sm-3 control-label no-padding-right"> 设置中奖方式：</label>
                                            <div class="col-xs-12 col-sm-9">
                                                <label class="radio-inline">
                                                    <input type="radio" name="gfa_way" value="1">按比例设置
                                                </label>
                                                <label class="radio-inline">
                                                    <input type="radio" name="gfa_way" value="2">固定中奖个数
                                                </label>
                                            </div>
                                        </div>
                                        <div id="drawway-check" style="display: none;">
                                            <div class="form-group" id="drawscale">
                                                <label class="col-sm-3 control-label no-padding-right"><span class="text-red">*</span>中奖率：</label>
                                                <div class="col-sm-9">
                                                    <input type="text" id="" name="draw_scale" class="col-xs-10 col-sm-5" placeholder="不可为空" />
                                                    <span class="tigs" id="">中奖率（填写不大于100的整数）</span>
                                                </div>
                                            </div>
                                            <div class="form-group" id="drawnum">
                                                <label class="col-sm-3 control-label no-padding-right"><span class="text-red">*</span>中奖个数：</label>
                                                <div class="col-sm-9">
                                                    <input type="text" id="" name="draw_num" class="col-xs-10 col-sm-5" placeholder="不可为空" />
                                                    <span class="tigs" id="">该活动最终中奖个数</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- 是否抽奖end -->
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label no-padding-right"> <span class="text-red">*</span> 是否在热门拼团展示：</label>
                                        <div class="col-xs-12 col-sm-9">
                                            <label class="radio-inline">
                                                <input type="radio" name="is_show_hot" value="0" checked>是
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" name="is_show_hot" value="1">否
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="page-header">
                            <h1>
                                商品信息
                            </h1>
                        </div><!-- /.basic -->
                        <div class="row">
                            <div class="col-xs-12">
                                <!-- PAGE CONTENT BEGINS -->
                                <div class="form-horizontal">
                                    <div class="form-group gift-row">
                                        <label class="col-sm-3 control-label no-padding-right">选择商品：</label>
                                        <input id="goods" class="col-sm-2" placeholder="请输入商品名称或者商品ID" type="text">
                                        <a id="searchGoods" class="col-sm-1 glyphicon glyphicon-search" href="javascript:;">搜索</a>
                                        <select name="goods_id" id="goods_id" class="col-sm-4 col-sm-offset-1">
                                            <option value="">请选择商品</option>
                                        </select>
                                    </div>
                                    <div class="space-4"></div>
                                    <div class="space-4"></div>
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label no-padding-right">商品标题： </label>
                                        <div class="col-sm-9">
                                            <input type="text" id="" name="goods_name" class="col-xs-10 col-sm-5" placeholder="" />
                                            <span class="tigs">不可为空，40字以内</span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label no-padding-right">商品卖点： </label>
                                        <div class="col-sm-9">
                                            <input type="text" id="" name="goods_introduction" class="col-xs-10 col-sm-5" placeholder="" />
                                            <span class="tigs">不可为空，56字以内</span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label no-padding-right">权重排序： </label>
                                        <div class="col-sm-9">
                                            <input type="text" id="" name="gfa_sort" value="127" class="col-xs-10 col-sm-5" placeholder="" />
                                            <span class="tigs">数字越大，排序越前，与前台列表显示排序关联（1~127）</span>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-sm-3 control-label no-padding-right"> <span class="text-red">*</span>商品列表图：<br>（750*340）</label>
                                        <div class="col-sm-9">
                                            <input type="file" id="move_logo" name="move_logo" data-img="goods_image" />
                                            <img src="" id="goods_image" class="img-rounded">
                                            <input type="hidden" name="goods_image" />
                                            <span class="tigs"></span>
                                        </div>
                                    </div>

                                    <!--添加商品轮播图 start -->
                                    <div class="form-group">
                                        <div class="col-sm-3 control-label no-padding-right"><span class="span_red">*</span>商品轮播图<br/><span class="span_prompt">（至少上传一张，<br>建议比例尺寸750*750）</span></div>
                                        <div class="col-sm-9 product_setimg">
                                            <ul class="list">

                                            </ul>
                                            <a class="add_img_btn add-btn">
                                                + <input type="file" class="upload-img move_image" id="file_img"  name="file_img[]" multiple />
                                            </a>
                                        </div>
                                    </div>
                                    <!--添加商品轮播图  end-->

                                    <div class="form-group">
                                        <label class="col-sm-3 control-label no-padding-right"> <span class="text-red">*</span>团购价格： </label>
                                        <div class="col-sm-9">
                                            <input type="text" id="" name="gfa_price" class="col-xs-10 col-sm-5" placeholder="" />
                                            <span class="tigs"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label no-padding-right"><span class="text-red">*</span>已参团人数： </label>
                                        <div class="col-sm-9">
                                            <input type="text" id="" name="gfa_num_init" class="col-xs-10 col-sm-5" placeholder="" />
                                            <span class="tigs">虚拟人数，当有真实订单之后，前端显示+1</span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label no-padding-right"> <span class="text-red">*</span>分享标题： </label>
                                        <div class="col-sm-9">
                                            <input type="text" id="" name="share_title" class="col-xs-10 col-sm-5" placeholder="" />
                                            <span class="tigs"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label no-padding-right"> <span class="text-red">*</span>分享内容： </label>
                                        <div class="col-sm-9">
                                            <input type="text" id="" name="share_content" class="col-xs-10 col-sm-5" placeholder="" />
                                            <span class="tigs"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label no-padding-right"> <span class="text-red">*</span>分享图片：<br>（60px*60px） </label>
                                        <div class="col-sm-9">
                                            <input type="file" id="move_share" name="move_share" data-img="share_image" />
                                            <img src="" id="share_image" class="img-rounded">
                                            <input type="hidden" name="share_image" />
                                            <span class="tigs"></span>
                                        </div>
                                    </div>
                                    <div class="clearfix form-actions">
                                        <div class="col-md-offset-3 col-md-9">
                                            <button id="actSubmit" class="btn btn-info" type="button" data-url="/groupact/add">
                                                <i class="ace-icon fa fa-check bigger-110"></i>
                                                确认添加
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div><!-- /.main-content -->
                    </div>
                </form>
            </div>
        </div><!-- /.main-container -->
{% endblock %}

{% block footer %}
<script src="http://{{ config.domain.static }}/assets/js/jquery.datetimepicker.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/ajaxfileupload.js"></script>
<script src="http://{{ config.domain.static }}/assets/admin/js/group/groupAct.js"></script>
    <script type="text/javascript">
        $('#start_time,#end_time').datetimepicker({step: 10});
    </script>
{% endblock %}