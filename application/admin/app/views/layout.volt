<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta charset="utf-8" />
    <meta name="description" content="overview &amp; stats" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    <title>{% block title %}统一运营后台{% endblock %}</title>
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

    <!-- SKU页面的样式 -->
    <link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/product.css" />

    <!-- ace settings handler -->
    <script src="http://{{ config.domain.static }}/assets/js/ace-extra.min.js"></script>

    <!-- HTML5shiv and Respond.js for IE8 to support HTML5 elements and media queries -->

    <!--[if lte IE 8]>
    <script src="http://{{ config.domain.static }}/assets/js/html5shiv.min.js"></script>
    <script src="http://{{ config.domain.static }}/assets/js/respond.min.js"></script>
    <![endif]-->

</head>

<body class="no-skin">

<div id="navbar" class="navbar navbar-default navbar-fixed-top">
    <script type="text/javascript">
        try{ace.settings.check('navbar' , 'fixed')}catch(e){}
    </script>

    <div class="navbar-container" id="navbar-container">
        <button type="button" class="navbar-toggle menu-toggler pull-left" id="menu-toggler" data-target="#sidebar">
            <span class="sr-only">Toggle sidebar</span>

            <span class="icon-bar"></span>

            <span class="icon-bar"></span>

            <span class="icon-bar"></span>
        </button>

        <div class="navbar-header pull-left">
            <a href="/" class="navbar-brand">
                <small>
                    <i class="fa fa-leaf"></i>
                    统一运营后台
                </small>
            </a>
        </div>

        <div class="navbar-buttons navbar-header pull-right" role="navigation">
            <ul class="nav ace-nav">

                <li class="orange">
                    <a class="dropdown-toggle" href="/admin/loginedpassword" >
                        <span class="badge badge-grey">修改密码</span>
                    </a>
                </li>

                <li class="light-blue">
                    <a data-toggle="dropdown" class="dropdown-toggle">
								<span class="user-info">
                                    <small>欢迎您，</small>
									{{ session.admin_account }}
								</span>
                        <i class="ace-icon fa fa-caret-down"></i>
                    </a>

                    <ul class="user-menu dropdown-menu-right dropdown-menu dropdown-yellow dropdown-caret dropdown-close">
                        <li>
                            <a href="/login/logout" id="logout">
                                <i class="ace-icon fa fa-power-off"></i>
                                退出
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div><!-- /.navbar-container -->
</div>

<div class="main-container" id="main-container">
    <script type="text/javascript">
        try{ace.settings.check('main-container' , 'fixed')}catch(e){}
    </script>

    <div id="sidebar" class="sidebar h-sidebar navbar-collapse collapse" style="background-color: transparent;position: static;">
        <script type="text/javascript">
            try{ace.settings.check('sidebar' , 'fixed')}catch(e){}
        </script>
        <div class="sidebar-shortcuts" id="sidebar-shortcuts" style="width:190px">
            <div class="sidebar-shortcuts-mini" id="sidebar-shortcuts-mini" style="margin:0 auto">
                <span class="btn btn-success"></span>
                <span class="btn btn-info"></span>
                <span class="btn btn-warning"></span>
                <span class="btn btn-danger"></span>
            </div>
        </div><!-- /.sidebar-shortcuts -->

        <!-- 头部 -->
        <ul class="nav nav-list" style="background: #f2f2f2;">
            {% if main_menu is defined and main_menu is not empty %}
                {% for k,v in main_menu %}
                    <li class="hover {% if v['current'] == 1 %}active{% endif %}">
                        <a href="{{ v['route'] }}" class="{% if v['son'] is defined %}dropdown-toggle{% endif %}" target="{{ v['target'] }}">
                            <i class="icon_admin icon-pro {{ v['nav_icon']?v['nav_icon']:'icon-system' }}"></i>
                            <span class="menu-text"> {{ v['name'] }} </span>
                            <b class="arrow fa fa-angle-down"></b>
                        </a>
                        <b class="arrow"></b>
                        {% if v['son'] is defined %}
                        <ul class="submenu">
                            {% for kk,vv in v['son'] %}
                                <li class="open hover">
                                    <a href="#" class="dropdown-toggle">
                                        <i class="menu-icon fa fa-caret-right"></i>
                                        {{ vv['name'] }}
                                        <b class="arrow fa fa-angle-down"></b>
                                    </a>
                                    <b class="arrow"></b>
                                    {% if vv['son'] is defined %}
                                        <ul class="submenu">
                                            {% for kkk,vvv in vv['son'] %}
                                            <li class="hover">
                                                <a href="{{ vvv['route'] }}" target="{{ vvv['target'] }}">
                                                    <i class="menu-icon fa fa-caret-right"></i>
                                                    {{ vvv['name'] }}
                                                </a>

                                                <b class="arrow"></b>
                                            </li>
                                            {% endfor %}
                                        </ul>
                                    {% endif %}
                                </li>
                            {% endfor %}
                        </ul>
                        {% endif %}
                    </li>
                {% endfor %}
            {% endif %}
        </ul><!-- /.nav-list -->

        <!-- 左边 -->
        <div class="sidebar responsive">
            <ul class="nav nav-list">
                <li class="" style="width: 100%">
                    {% if current_menu is defined and current_menu is not empty %}
                        <ul class="submenu"  style="display: block" >
                            {% for k,v in current_menu %}
                            <li class="{% if v['current'] == 1 %}active{% endif %}">
                                <a href="#" class="dropdown-toggle">
                                    <i class="menu-icon fa fa-caret-right"></i>
                                    {{ v['name'] }}
                                    <b class="arrow fa fa-angle-down"></b>
                                </a>
                                <b class="arrow"></b>
                                <ul class="submenu">
                                    {% if v['son'] is defined %}
                                        {% for kk,vv in v['son'] %}
                                            <li class="{% if vv['current'] == 1 %}active open{% endif %}">
                                                <a href="{{ vv['route'] }}" target="{{ vv['target'] }}">
                                                    <i class="menu-icon fa fa-caret-right"></i>
                                                    {{ vv['name'] }}
                                                </a>
                                                <b class="arrow"></b>
                                            </li>
                                         {% endfor %}
                                    {% endif %}
                                </ul>
                            </li>
                            {% endfor %}
                        </ul>
                    {% endif %}
                </li>
            </ul><!-- /.nav-list -->
        </div>
        <!-- 面包屑 -->
        <div class="main-content sku_add_frame">
            <div class="main-content-inner">
                <div class="breadcrumbs" id="breadcrumbs">
                    <script type="text/javascript">
                        try{ace.settings.check('breadcrumbs' , 'fixed')}catch(e){}
                    </script>

                    <ul class="breadcrumb">
                        <li>
                            <i class="ace-icon fa fa-home home-icon"></i>
                            <a href="/">首页</a>
                        </li>
                        {% block path %}
                            {% if bread_crumb is defined and bread_crumb is not empty %}
                                {% for v in bread_crumb %}
                                    <li>
                                        <i class="ace-icon"></i>
                                        {% if v['route'] %}
                                            <a href="{{ v['route'] }}">{{ v['name'] }}</a>
                                        {% else %}
                                            {{ v['name'] }}
                                        {% endif %}
                                    </li>
                                    {% if v['son'] is defined %}
                                        {% for vv in v['son'] %}
                                            <li>
                                                <i class="ace-icon"></i>
                                                {% if vv['route'] %}
                                                    <a href="{{ vv['route'] }}">{{ vv['name'] }}</a>
                                                {% else %}
                                                    {{ vv['name'] }}
                                                {% endif %}
                                            </li>
                                            {% if vv['son'] %}
                                                {% for vvv in vv['son'] %}
                                                    <li>
                                                        <i class="ace-icon"></i>
                                                        {% if vvv['route'] %}
                                                            <a href="{{ vvv['route'] }}">{{ vvv['name'] }}</a>
                                                        {% else %}
                                                            {{ vvv['name'] }}
                                                        {% endif %}
                                                    </li>
                                                {% endfor %}
                                            {% endif %}
                                        {% endfor %}
                                    {% endif %}
                                {% endfor %}
                            {% endif %}
                        {% endblock %}
                    </ul><!-- /.breadcrumb -->

                    {#<div class="nav-search" id="nav-search">
                    <form class="form-search">
								<span class="input-icon">
									<input type="text" placeholder="Search ..." class="nav-search-input" id="nav-search-input" autocomplete="off" />
									<i class="ace-icon fa fa-search nav-search-icon"></i>
								</span>
                    </form>
                </div>#}<!-- /.nav-search -->
                </div>
                <div class="page-content">
                    {% block content %}{% endblock %}
                </div><!-- /.page-content -->
            </div>
        </div><!-- /.main-content -->
        <script type="text/javascript">
            try{ace.settings.check('sidebar' , 'collapsed')}catch(e){}
        </script>
    </div>
</div>


</div>


<!-- basic scripts -->

<!--[if !IE]> -->
<script src="http://{{ config.domain.static }}/assets/js/jquery.2.1.1.min.js"></script>

<!-- <![endif]-->

<!--[if IE]>
<script src="http://{{ config.domain.static }}/assets/js/jquery.1.11.1.min.js"></script>
<![endif]-->
<script type="text/javascript">
    //商城各端，json格式（{"pc":"PC","app":"APP","wap":"WAP","wechat":"\u5fae\u5546\u57ce"}）
    var shopPlatform = {{ shopPlatformJson }};
</script>
<!--[if !IE]> -->
<script type="text/javascript">
    window.jQuery || document.write("<script src='http://{{ config.domain.static }}/assets/js/jquery.min.js'>"+"<"+"/script>");
</script>

<!-- <![endif]-->

<!--[if IE]>
<script type="text/javascript">
    window.jQuery || document.write("<script src='http://{{ config.domain.static }}/assets/js/jquery1x.min.js'>"+"<"+"/script>");
</script>
<![endif]-->
<script type="text/javascript">
    if('ontouchstart' in document.documentElement) document.write("<script src='http://{{ config.domain.static }}/assets/js/jquery.mobile.custom.min.js'>"+"<"+"/script>");
</script>
<script src="http://{{ config.domain.static }}/assets/js/bootstrap.min.js"></script>

<!-- page specific plugin scripts -->

<!--[if lte IE 8]>
<script src="http://{{ config.domain.static }}/assets/js/excanvas.min.js"></script>
<![endif]-->
<script src="http://{{ config.domain.static }}/assets/js/jquery-ui.custom.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/jquery.ui.touch-punch.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/jquery.easypiechart.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/jquery.sparkline.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/jquery.flot.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/jquery.flot.pie.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/jquery.flot.resize.min.js"></script>

<!-- ace scripts -->
<script src="http://{{ config.domain.static }}/assets/js/ace-elements.min.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/ace.min.js"></script>

<!-- layer&common scripts -->
<script src="http://{{ config.domain.static }}/assets/js/layer/layer.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/common.js"></script>


{% block footer %}

{% endblock %}

{% block script %}

{% endblock %}
</body>



