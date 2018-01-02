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
                    	绑定推广员
                </div>
                <div class="space-4"></div>

                <!-- PAGE CONTENT BEGINS -->
               
                    <div class="form-horizontal">
                      
                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right">绑定用户手机号 :</label>
                            <div class="col-sm-9">
                                <input type="text" name="user_id" id="user_id"  value="{% if channel['userId'] is defined and channel['user_id']  %}{{ channel['user_id'] }}{%endif%}" class="tools-txt" placeholder="不可为空"/>   
                            </div>
                              <label class="col-sm-3 control-label no-padding-right">推广员手机号:</label>
                            <div class="col-sm-9">
                                <input type="text" name="cps_user_id" id='cps_user_id' value="{% if channel['cps_user_id'] is defined and channel['cps_user_id']  %}{{ channel['cps_user_id'] }}{%endif%}" class="tools-txt" placeholder="不可为空"/>   
                            </div>
                        </div>

                    
                       
                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right"></label>
                            <div class="col-sm-9">
                                <button class="btn btn-info" id="editTemplate">
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
    
    <script type="text/javascript">
   $("#editTemplate").click(function(){
		var	userId = $("#user_id").val();
		var	cps_userId =  $("#cps_user_id").val();
			$.ajax({
				url:'/cpsdetail/bang',
				data:{
					user_id:userId,cps_user_id:cps_userId,stu:1
				},
				type:'get',
				dataType:'json',
				cache:false,
				success:function(data){
					alert(data.msg);
					if(data.code==0){
						window.location.reload('/cpsdetail/list');
					}
					
				}
			});
		});
    	
    </script>
</body>
