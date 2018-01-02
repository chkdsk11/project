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
															<input name="admin_account" id="admin_account" type="text" class="form-control" placeholder="用户名" />
															<i class="ace-icon fa fa-user"></i>
														</span>
                                            </label>

                                            <label class="block clearfix">
														<span class="block input-icon input-icon-right">
															<input name="admin_password" id="admin_password" type="password" class="form-control" placeholder="密码" />
															<i class="ace-icon fa fa-lock"></i>
														</span>
                                            </label>
                                            <label class="block clearfix">
														<span class="block input-icon input-icon-right">
															<input name="code" id="code" type="text" class="form-control" placeholder="验证码" />
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

<script src="http://{{ config.domain.static }}/assets/js/layer/layer.js"></script>
<script src="http://{{ config.domain.static }}/assets/js/jquery.form.js"></script>
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
       //$('#loginForm').validate();
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
    $('#loginForm').on('submit', function() {
        var action = $(this).attr('action'),admin_account = $('#admin_account').val(),admin_password = $('#admin_password').val(),code = $('#code').val();
        if(!admin_account){
            layer.tips('管理员用户名不能为空！','#admin_account');
            $('#admin_account').focus();
            return false;
        }
        if(!admin_password){
            layer.tips('管理员密码不能为空！','#admin_password');
            $('#admin_password').focus();
            return false;
        }
        if(!code){
            layer.tips('验证码不能为空！','#code');
            $('#code').focus();
            return false;
        }
            //return false;
        $(this).ajaxSubmit({
            type: 'post', // 提交方式 get/post
            url: '', // 需要提交的 url
            data: {
            },
            success: function(data) { 
                // data 保存提交后返回的数据，一般为 json 数据
                // 此处可对 data 作相关处理
                if(data.code == 200){
                    location.href = data.data;
                }else{
                    if(data.data.field){
                        layer.tips(data.msg, '#'+data.data.field);
                    }else{

                        layer.msg(data.msg);
                    }
                }
            }
        });
        // 阻止表单自动提交事件
        return false; 
    });
</script>
