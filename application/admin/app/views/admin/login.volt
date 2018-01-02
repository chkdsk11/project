<style>
    .error {
        color: red;
    }
</style>
<div class="main-container">
    <div class="main-content">
        <div class="row">
            <div class="col-sm-10 col-sm-offset-1">
                <div class="login-container">
                    <div class="center">
                        <h1>
                            <span class="green glyphicon glyphicon-home"></span>
                            <span class="orange2">统一运营后台</span>
                        </h1>
                    </div>

                    <div class="space-6"></div>

                    <div class="position-relative">
                        <div id="login-box" class="login-box visible widget-box no-border">
                            <div class="widget-body">
                                <div class="widget-main">
                                    <div class="space-6"></div>

                                    <form method="post" action="" id="loginForm">
                                        <fieldset>
                                            <label class="block clearfix">
														<span class="block input-icon input-icon-right">
															<input name="username" type="text" class="form-control" placeholder="用户名" required/>
															<i class="ace-icon fa fa-user"></i>
														</span>
                                            </label>

                                            <label class="block clearfix">
														<span class="block input-icon input-icon-right">
															<input name="password" type="password" class="form-control" placeholder="密码" required/>
															<i class="ace-icon fa fa-lock"></i>
														</span>
                                            </label>
                                            <label class="block clearfix">
														<span class="block input-icon input-icon-right">
															<input name="code" type="text" class="form-control" placeholder="验证码" required/>
															<i class="ace-icon fa fa-lock"></i>
														</span>
                                            </label>
                                            <div class="clearfix">
                                                <img id="img_code" src="/code">&nbsp;<a id="flush_code" class="glyphicon glyphicon-repeat btn" title="点击刷新"></a>
                                            </div>
                                            <div class="space"></div>
                                            <div class="clearfix">
                                               <input class="btn btn-app btn-yellow" type="submit" value="登录" name="submit" />
                                            </div>
                                            {% if error is defined %}
                                            <div id="auth_error" class="alert alert-danger">
                                                <button type="button" class="close" data-dismiss="alert">
                                                    <i class="ace-icon fa fa-times"></i>
                                                </button>

                                                <strong>
                                                    <i class="ace-icon fa fa-times"></i>
                                                </strong>
                                                {{ error }}
                                                <br />
                                            </div>
                                            {% endif %}
                                            <div class="space-4"></div>
                                        </fieldset>
                                    </form>
                                </div><!-- /.widget-main -->
                            </div><!-- /.widget-body -->
                        </div><!-- /.login-box -->

                    <div class="navbar-fixed-top align-right">
                        <br />
                        &nbsp;
                        <a id="btn-login-dark" href="#">深</a>
                        &nbsp;
                        <span class="blue">/</span>
                        &nbsp;
                        <a id="btn-login-blur" href="#">蓝</a>
                        &nbsp;
                        <span class="blue">/</span>
                        &nbsp;
                        <a id="btn-login-light" href="#">灰</a>
                        &nbsp; &nbsp; &nbsp;
                    </div>
                </div>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.main-content -->
</div><!-- /.main-container -->

<!-- basic scripts -->

<!--[if !IE]> -->
<script src="http://{{ config.domain.static }}/assets/js/jquery.2.1.1.min.js"></script>
<!-- <![endif]-->

<!--[if IE]>
<script src="http://{{ config.domain.static }}/assets/js/jquery.1.11.1.min.js"></script>
<![endif]-->

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

<!-- inline scripts related to this page -->
    <script src="http://{{ config.domain.static }}/assets/js/bootstrap.min.js"></script>
    <script src="http://{{ config.domain.static }}/assets/js/jquery.validate.min.js"></script>
    <script src="http://{{ config.domain.static }}/assets/js/messages_zh.min.js"></script>

<script type="text/javascript">
    jQuery(function($) {
        $(document).on('click', '.toolbar a[data-target]', function(e) {
            e.preventDefault();
            var target = $(this).data('target');
            $('.widget-box.visible').removeClass('visible');//hide others
            $(target).addClass('visible');//show target
        });
    });

    //表单验证
    $(document).ready(function(){
       $('#loginForm').validate();
    });

    //登录页样式
    $(function(){
        $('body').attr('class', 'login-layout blur-login');
        $('#id-text2').attr('class', 'white');
        $('#id-company-text').attr('class', 'light-blue');
    });

    //刷新验证码
    jQuery(function($){
        $('#flush_code').on('click',function(){
            var code=document.getElementById('img_code');
            code.src="http://{{ config.domain.admin }}/code?t=" + Math.random();
        });
    });

    //错误休息三秒后自动隐藏
    jQuery(function($){
       setTimeout(function () {
            $('#auth_error').hide();
       },3000);
    });

    //you don't need this, just used for changing background
    jQuery(function($) {
        $('#btn-login-dark').on('click', function(e) {
            $('body').attr('class', 'login-layout');
            $('#id-text2').attr('class', 'white');
            $('#id-company-text').attr('class', 'blue');

            e.preventDefault();
        });
        $('#btn-login-light').on('click', function(e) {
            $('body').attr('class', 'login-layout light-login');
            $('#id-text2').attr('class', 'grey');
            $('#id-company-text').attr('class', 'blue');

            e.preventDefault();
        });
        $('#btn-login-blur').on('click', function(e) {
            $('body').attr('class', 'login-layout blur-login');
            $('#id-text2').attr('class', 'white');
            $('#id-company-text').attr('class', 'light-blue');

            e.preventDefault();
        });

    });
</script>