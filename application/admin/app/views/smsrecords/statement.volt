{% extends "layout.volt" %}


{% block content %}
    <link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/jquery.datetimepicker.css" class="ace-main-stylesheet" />
    <div class="main-content">
        <div class="main-content-inner">
            <div>
                <h3>
                    短信数据
                    <small>
                        更新时间:{{ date('Y-m-d H:i:s') }}
                        <div class="pull-right" id="selectTime">
                            <button class="btn btn-white btn-primary active" value="today">当天</button>
                            <button class="btn btn-white btn-primary" value="onemonth">最近一个月</button>
                            <button class="btn btn-white btn-primary" value="threemonth">最近三个月</button>
                        </div>
                    </small>
                </h3>
            </div>
            <br />
            <div class="row">
                <div class="col-sm-12">
                    <table class="table table-striped table-bordered table-hover">
                        <tbody id="recordes">

                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 历史数据 -->
            <div class="row">
                <h3>历史数据</h3>
                <div class="col-md-12" id="sendsms">
                    <div style="overflow: hidden;">
                        <div class="pull-left" id="sendsms-send">
                            <button class="btn btn-info active" value="send">发送短信数</button>
                            <button class="btn btn-info" value="rsend">补发短信数</button>
                        </div>
                        <div class="pull-right" id="sendsms-time">
                            <button class="btn btn-white btn-primary pull-right" value="seltime">指定时间</button>
                            <button class="btn btn-white btn-primary pull-right" value="threehour">最近三小时</button>
                            <button class="btn btn-white btn-primary pull-right active" value="onehour">最近一小时</button>
                        </div>
                    </div>
                    <div class="pull-right tools-box" id="seltime" style="display: none;">
                        <label class="clearfix checktime">
                            <input id="starttime" class="laydate-icon tools-txt" name="starttime" value="{% if option['starttime'] is not empty %}{{ option['starttime'] }}{% endif %}">
                        </label>
                        <label class="clearfix checktime">
                            <span>—</span>
                            <input id="endtime" class="laydate-icon tools-txt" name="endtime" value="{% if option['endtime'] is not empty %}{{ option['endtime'] }}{% endif %}">
                        </label>
                        <label class="clearfix">
                            <button class="btn btn-primary" value="search">搜索</button>
                        </label>
                    </div>
                </div>
                <br /><br /><br /><br />
                <!-- 为ECharts准备一个具备大小（宽高）的Dom -->
                <div id="main" style="width: 1200px;height:400px;">

                </div>
            </div>
        </div>
    </div>
</div>
{% endblock %}
{% block footer %}
    <script src="http://{{ config.domain.static }}/assets/js/echarts.js"></script>
    <script src="http://{{ config.domain.static }}/assets/laydate/laydate.js"></script>
    <!-- 请求短信数据 -->
    <script type="text/javascript">
        //首次进入请求当天数据
        getRecordes("today");
        //点击时间切换短信数据
        $("#selectTime button").click(function () {
            var button = $(this);
            var time = button.val();
            //调整样式
            button.parent("div #selectTime").find('button').removeClass('active');
            button.addClass('active');
            getRecordes(time);
        });
        //请求数据
        function getRecordes(time) {
            //请求数据，改变样式，切换数据
            $.ajax({
                type: "post", //post请求方式
                async: true, //异步请求（同步请求将会锁住浏览器，用户其他操作必须等待请求完成才可以执行）
                url: "/smsrecords/statement", //请求发送到ShowInfoIndexServlet处
                data: {name: "recordes", seltime: time}, //请求内包含一个key为name，value为A0001的参数；服务器接收到客户端请求时通过request.getParameter方法获取该参数值
                dataType: "json", //返回数据形式为json
                success: function (result) {
                    //请求成功时执行该函数内容，result即为服务器返回的json对象
                    if (result.status == "success") {
                        //处理数据
                        $("#recordes").empty();
                        var statement = result.data.statement;
                        var str = "";
                        str += "<tr><td><h3>" + result.data.send + "</h3></td><td><h3>" + result.data.rsend + "</h3></td><td><h3>" + result.data.residue + "</h3></td></tr>";
                        str += "<tr><td><h5>发送短信数</h5></td><td><h5>补发短信数</h5></td><td><h5>剩余短信数</h5></td></tr>";
                        for (var i in statement) {
                            str += "<tr>";
                            for (var j in statement[i]) {
                                str += "<td>" + statement[i][j] + "</td>";
                            }
                            str += "</tr>";
                        }
                        $("#recordes").append(str);
                    } else {
                        //返回的数据为空时显示提示信息
                        alert(result.info);
                    }
                },
                error: function (errorMsg) {
                    //请求失败时执行该函数
                    alert("请求数据失败");
                }
            });
        }
    </script>
    <!-- 曲线图 -->
    <script type="text/javascript">
        // 基于准备好的dom，初始化echarts实例
        var myChart = echarts.init(document.getElementById('main'));
        //初始参数
        var sendname = "send";
        var seltime = "onehour";
        var starttime = "";
        var endtime = "";
        //首次进入初始数据
        getCurves(sendname, seltime, starttime, endtime);
        //点击切换图表
        $("#sendsms button").click(function () {
            var button = $(this);
            button.parent("div").find('button').removeClass('active');
            button.addClass("active");
            if (button.val() == "send" || button.val() == "rsend") {
                sendname = button.val();
                if (seltime == "seltime") {
                    starttime = $("input[name='starttime']").val();
                    endtime = $("input[name='endtime']").val();
                }
            } else {
                seltime = button.val();
                if (seltime == "seltime") {
                    return false;
                }
                if (seltime == "search") {
                    seltime = "seltime";
                    starttime = $("input[name='starttime']").val();
                    endtime = $("input[name='endtime']").val();
                }
            }
            getCurves(sendname, seltime, starttime, endtime);
        });
        //获取图表数据
        function getCurves(sendname, seltime, starttime, endtime) {
            //数据加载完之前先显示一段简单的loading动画
            myChart.showLoading();
            //请求数据
            $.ajax({
                type: "post", //post请求方式
                async: true, //异步请求（同步请求将会锁住浏览器，用户其他操作必须等待请求完成才可以执行）
                url: "/smsrecords/statement", //请求发送到ShowInfoIndexServlet处
                data: {name: "curves", sendname: sendname, seltime: seltime, starttime: starttime, endtime: endtime}, //请求内包含一个key为name，value为A0001的参数；服务器接收到客户端请求时通过request.getParameter方法获取该参数值
                dataType: "json", //返回数据形式为json
                success: function (result) {
                    //请求成功时执行该函数内容，result即为服务器返回的json对象
                    if (result.status == "success") {
                        var rowdata = splitData(result.data);
                        myChart.hideLoading(); //隐藏加载动画
                        //载入数据
                        myChart.setOption(option = {
                            title: {
                                text: '短信数',
                                //subtext: '八时流量',
                                //textAlign: 'center',
                                //left: 'center'
                            },
                            tooltip: {
                                trigger: 'axis',
                                position: function (pt) {
                                    return [pt[0], '10%'];
                                }
                            },
                            dataZoom: [
                                {
                                    type: 'slider', //支持鼠标滚轮缩放
                                    start: 10, //默认数据初始缩放范围为10%到90%
                                    end: 90
                                },
                                {
                                    type: 'inside', //支持单独的滑动条缩放
                                    start: 10, //默认数据初始缩放范围为10%到90%
                                    end: 90
                                }
                            ],
                            legend: {
                                show: true,
                                data: rowdata.provider
                            },
                            color: [
                                '#68CFE8', //蓝色
                                '#53FF53', //绿色
                                '#FF3333', //红色
                                '#B15BFF', //紫色
                                '#FFDC35', //黄色
                            ],
                            toolbox: {
                                show: true,
                                feature: {
                                    saveAsImage: {}
                                }
                            },
                            grid: {
                                left: '3%',
                                right: '4%',
                                bottom: '3%',
                                containLabel: true
                            },
                            xAxis: {
                                type: 'category',
                                boundaryGap: false, //新坐标显示在中间或者断点处
                                data: (rowdata.units).map(function (str) {
                                    return str.replace(' ', '\n');
                                }),
                                splitLine: {//分割线
                                    show: false,
                                    interval: 'auto'
                                }
                            },
                            yAxis: {
                                type: 'value',
                                //minInterval: 1,
                                min: 0,
                                axisLabel: {
                                    formatter: '{value}条'
                                }
                            },
                            series: rowdata.seriesdata
                        });
                    } else {
                        //返回的数据为空时显示提示信息
                        alert(result.info);
                        myChart.hideLoading();
                    }
                },
                error: function (errorMsg) {
                    //请求失败时执行该函数
                    alert("图表请求数据失败，可能是服务器开小差了");
                    myChart.hideLoading();
                }
            });
        }

        {#var provider = ['助通', '微网'];
        var units = ['周一', '周二', '周三', '周四', '周五', '周六', '周日', '周一', '周二', '周三', '周四', '周五', '周六', '周日', '周一', '周二', '周三', '周四', '周五', '周六', '周日'];
        var seriesdata = [
            {
                name: '助通',
                type: 'line',
                smooth: true,
                symbol: 'circle', //设置折线图中表示每个坐标点的符号；emptycircle：空心圆；emptyrect：空心矩形；circle：实心圆；emptydiamond：菱形
                data: [100, 132, 101, 134, 90, 230, 210, 100, 132, 101, 134, 90, 230, 210, 100, 132, 101, 134, 90, 230, 210],
                animation: true
            },
            {
                name: '微网',
                type: 'line',
                smooth: true,
                symbol: 'circle',
                data: [220, 182, 191, 234, 290, 330, 310, 220, 182, 191, 234, 290, 330, 310, 220, 182, 191, 234, 290, 330, 310],
                animation: true
            }
        ];#}
            //处理数据格式
            function splitData(rowdata) {
                var provider = rowdata.provider;
                var units = rowdata.units;
                var datas = rowdata.seriesdata;
                var seriesdata = [];
                for (var i in datas) {
                    var pro = {
                        name: datas[i].name,
                        type: 'line',
                        smooth: true,
                        symbol: 'circle', //设置折线图中表示每个坐标点的符号；emptycircle：空心圆；emptyrect：空心矩形；circle：实心圆；emptydiamond：菱形
                        data: datas[i].records,
                        animation: true
                    }
                    seriesdata.push(pro);
                }
                return {
                    provider: provider,
                    units: units,
                    seriesdata: seriesdata
                };
            }
            //切换按钮时间插件显示与隐藏
            $("#sendsms-time button").click(function () {
                var button = $(this).val();
                if (button == "seltime") {
                    $("#seltime").show();
                } else {
                    $("#seltime").hide();
                }
            });
    </script>
    <!-- 时间插件 -->
    <script type="text/javascript">
        var start = {
            elem: '#starttime',
            format: 'YYYY-MM-DD hh:mm:ss',
            //min: '2016-07-01', //设定最小日期为当前日期
            max: laydate.now(), //最大日期
            istime: true,
            istoday: false,
            choose: function (datas) {
                end.min = datas; //开始日选好后，重置结束日的最小日期
                end.start = datas; //将结束日的初始值设定为开始日
            }
        };
        var end = {
            elem: '#endtime',
            format: 'YYYY-MM-DD hh:mm:ss',
            //min: laydate.now(),
            max: laydate.now(),
            istime: true,
            istoday: false,
            choose: function (datas) {
                start.max = datas; //结束日选好后，重置开始日的最大日期
            }
        };
        laydate(start);
        laydate(end);
    </script>
{% endblock %}