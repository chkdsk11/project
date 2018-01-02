<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta charset="utf-8" />
    <meta name="description" content="overview &amp; stats" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    <title>统一运营后台</title>
    <!-- bootstrap & fontawesome -->
    <link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="http://{{ config.domain.static }}/assets/font-awesome/4.2.0/css/font-awesome.min.css" />

    <!-- page specific plugin styles -->

    <!-- text fonts -->
    <link rel="stylesheet" href="http://{{ config.domain.static }}/assets/fonts/fonts.googleapis.com.css" />

    <!-- ace styles -->
    <link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/ace.min.css" class="ace-main-stylesheet" id="main-ace-style" />

    <!--[if lte IE 9]>
    <link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/ace-part2.min.css" class="ace-main-stylesheet" />
    <![endif]-->

    <!--[if lte IE 9]>
    <link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/ace-ie.min.css" />
    <![endif]-->

    <!-- inline styles related to this page -->
    <link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/module/layout.css" />
    <!-- ace settings handler -->
    <script src="http://{{ config.domain.static }}/assets/js/ace-extra.min.js"></script>

    <!-- HTML5shiv and Respond.js for IE8 to support HTML5 elements and media queries -->

    <!--[if lte IE 8]>
    <script src="http://{{ config.domain.static }}/assets/js/html5shiv.min.js"></script>
    <script src="http://{{ config.domain.static }}/assets/js/respond.min.js"></script>
    <![endif]-->
    <!--[if !IE]> -->
    <script src="http://{{ config.domain.static }}/assets/js/jquery.2.1.1.min.js"></script>

    <!-- <![endif]-->

    <!--[if IE]>
    <script src="http://{{ config.domain.static }}/assets/js/jquery.1.11.1.min.js"></script>
    <![endif]-->
    <!-- layer&common scripts -->
    <script src="http://{{ config.domain.static }}/assets/js/layer/layer.js"></script>
    <script src="http://{{ config.domain.static }}/assets/js/common.js"></script>
</head>

<body class="no-skin">
    <div class="page-content">
        <div class="row">
            <div class="col-xs-12">
                <!-- PAGE CONTENT BEGINS -->
                <div class="clearfix">
                    <div class="pull-right tableTools-container"></div>
                </div>
                <div class="table-header">
                    短信补发优先级
                </div>
                <div class="space-4"></div>
                <!-- PAGE CONTENT BEGINS -->
                <div class="form-horizontal">
                    {% for v in list %}
                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right">{{ v['provider_name'] }}： </label>
                            <div class="col-sm-9">
                                {% if v['provider_state'] == 0 %}
                                    <input type="text" name="{{ v['provider_id'] }}" value="{{ v['priority'] }}" class="col-xs-10 col-sm-5" placeholder="请输入整数"/>
                                {% else %}
                                    <span style="color: red;">已停用,无法设置</span>
                                {% endif %}   
                            </div>
                        </div>
                    {% endfor%}

                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-right"></label>
                        <div class="col-sm-9">
                            <span class="col-sm-9" style="color: red;">数字越大优先级越高，“0”优先级最低</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label no-padding-right"></label>
                        <div class="col-sm-9">
                            <button class="btn btn-info" id="editPriority">
                                <i class="ace-icon fa fa-check bigger-110"></i>
                                确认
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- /.row -->

    </div><!-- /.page-content -->

    <!--上传文件-->
    <script src="http://{{ config.domain.static }}/assets/admin/js/sms/sms.js"></script>
</body>
