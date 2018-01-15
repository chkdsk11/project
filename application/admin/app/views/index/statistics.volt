{% extends "layout.volt" %}

{% block path %}
<!--放页面路径如：-->

{% endblock %}

{% block content %}

<!--放页面内容-->
<div>
    <div>
        <h1>仪表盘</h1>
        <div class="pagedesc" style="float: right;margin-right: 40px;height: 60px; display: none;" id="flush-cache">
            <span style="float: left;margin-right: 40px;line-height: 30px;">数据缓存一小时，也可手动刷新缓存</span>
            <input type="button" class="btn btn-primary" value="刷新" onclick="clearCatch()"/>
        </div>
        <table class="table table-striped table-bordered table-hover">
            <tr>
                <th style="text-align: center;padding: 40px;width:25%;">
                    <label>本周订单量</label>
                    <div style="font-size: xx-large;color: gray" id="order_count">0</div>
                </th>
                <th style="text-align: center;padding: 40px;width:25%;">
                    <label>本周支付订单总量</label>
                    <div style="font-size: xx-large;color: gray" id="order_paid_count">0</div>
                </th>
                <th style="text-align: center;padding: 40px;width:25%;">
                    <label>本周取消订单量</label>
                    <div style="font-size: xx-large;color: gray" id="order_canceled_count">0</div>
                </th>
                <th style="text-align: center;padding: 40px;width:25%;">
                    <label>过去一周注册用户数</label>
                    <div style="font-size: xx-large;color: gray" id="user_average_daily_count">0</div>
                </th>
            </tr>
        </table>
    </div>
    <div class="row-fluid">
        <div id="data_order" class="col-xs-12 col-lg-6" style="height:350px;display: none;"></div>
        <div id="week_order" class="col-xs-12 col-lg-6" style="height:350px;display: none;"></div>
        <div id="channel_order" class="col-xs-12 col-lg-6" style="height:350px;display: none;"></div>
        <div id="area_order" class="col-xs-12 col-lg-6" style="height:350px;display: none;"></div>
        <div id="week_register_user" class="col-xs-12 col-lg-6" style="height: 350px;display: none;"></div>
        <div id="week_login_user" class="col-xs-12 col-lg-6" style="height: 350px;display: none;"></div>
    </div>
    <div class="row-fluid" style="width: 100%;">
        <div id="goods_area" style="margin-bottom: 5px; display: none;">
            <div style="height: 350px;float: left;margin-bottom: 100px;">
                <label><h4 style="font-weight: 600;">商品动销</h4></label>
                <div style="height: 350px;overflow-y: auto;">
                    <table class="table table-striped table-bordered table-hover">
                        <tr>
                            <th>动销商品</th>
                            <th>动销数量</th>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /.page-content -->
{% endblock %}

{% block footer %}
<!--放尾部需加载的样式和js，禁止出现显示内容-->
<!-- inline scripts related to this page -->
<script src="http://echarts.baidu.com/build/dist/echarts.js"></script>
<script type="text/javascript">
    // 路径配置
    require.config({
        paths: {
            echarts: 'http://echarts.baidu.com/build/dist'
        }
    });
    var info = '';

    $(function () {
        function showTopTable() {
            $.ajax({
                url: "/",
                type: "POST",
                dataType: "json",
                data: {type: 'top'}
            })
                .done(function (data) {
                    if (data) {
                        info = data;
                        if(info.flushCache && info.flushCache == 1){
                            $('#flush-cache').show();
                        }
                        if(info.topStatistics) {
                            $("#order_count").html(info.topStatistics.order_count);
                            $("#order_paid_count").html(info.topStatistics.order_paid_count);
                            $("#order_canceled_count").html(info.topStatistics.order_canceled_count);
                            $("#user_average_daily_count").html(info.topStatistics.user_count);
                        }
                        if(info.commodityPin) {
                            var goodsCountListTrs = '';
                            if (info.commodityPin.goodMoveList) {
                                $('#goods_area').show();
                                $.each(info.commodityPin.goodMoveList, function (index, value) {
                                    goodsCountListTrs += '<tr>' +
                                        '<td>' + value.name + '</td>' +
                                        '<td>' + value.count + '</td>' +
                                        '</tr>';
                                });
                                $('#goods_area table').append(goodsCountListTrs);
                            }
                        }
                    }
                    show();
                })
        }
        showTopTable();
    });
    function clearCatch() {
        $.ajax({
            url: "/",
            type: "POST",
            dataType: "json",
            data: {type: 'clearCatch'}
        })
            .done(function (data) {
                if (data.error == 0) {
                    alert('更新缓存成功!');
                    setTimeout(function () {
                        window.location.reload()
                    }, 1000);
                    ;
                }
            })
    }
    function show() {
        // 使用
        require(
            [
                'echarts',
                'echarts/theme/macarons',
                'echarts/chart/bar', // 使用柱状图就加载bar模块，按需加载
                'echarts/chart/line',
                'echarts/chart/map'
            ],
            function (ec) {
                // 基于准备好的dom，初始化echarts实例
                // 指定图表的配置项和数据
                //每日订单量（15天内）
                if(info.dailyOrdersMonthOnMonth) {
                    $('#data_order').show();
                    var data_order_chart = ec.init(document.getElementById('data_order'),'macarons');
                    data_order_option = {
                        title: {
                            text: '每日订单及环比情况',
                        },
                        tooltip: {
                            trigger: 'axis',
                            axisPointer: {
                                type: 'cross',
                                crossStyle: {
                                    color: '#999'
                                }
                            }
                        },
                        toolbox: {
                            feature: {
                                dataView: {show: true, readOnly: false},
                                magicType: {show: true, type: ['line', 'bar']},
                                restore: {show: true},
                                saveAsImage: {show: true}
                            }
                        },
                        xAxis: [
                            {
                                type: 'category',
                                data: info.dailyOrdersMonthOnMonth.daysOrder.days,
                                axisLabel: {
                                    interval: 0
                                },
                                axisPointer: {
                                    type: 'shadow'
                                }
                            }
                        ],
                        yAxis: [
                            {
                                type: 'value',
                                name: '订单量',
                                min: info.dailyOrdersMonthOnMonth.daysOrder.min,
                                max: info.dailyOrdersMonthOnMonth.daysOrder.max,
                                interval: 50,
                                axisLabel: {
                                    formatter: '{value}'
                                }
                            },
                            {
                                type: 'value',
                                name: '每日订单环比',
                                min: info.dailyOrdersMonthOnMonth.daysOrder.min,
                                max: info.dailyOrdersMonthOnMonth.daysOrder.max,
                                interval: 5,
                                axisLabel: {
                                    formatter: '{value} %'
                                }
                            }
                        ],
                        series: [
                            {
                                name: '订单量',
                                type: 'bar',
                                data: info.dailyOrdersMonthOnMonth.daysOrder.order_counts
                            },
                            {
                                name: '每日订单环比',
                                type: 'line',
                                yAxisIndex: 1,
                                data: info.dailyOrdersMonthOnMonth.daysOrder.percent,
                                label: {
                                    normal: {
                                        show: true,
                                        position: 'outside',
                                        formatter: '{value} %'
                                    }
                                },
                            }
                        ]
                    };
                    data_order_chart.setOption(data_order_option);
                }
                //前后周对比
                if(info.analysisOrderThisWeekAndLastWeek) {
                    $('#week_order').show();
                    var week_order_chart = ec.init(document.getElementById('week_order'),'macarons');
                    week_order_option = {
                        title: {
                            text: '本周与上周订单数据分析',
                        },
                        tooltip: {
                            trigger: 'axis',
                            axisPointer: {
                                type: 'cross',
                                crossStyle: {
                                    color: '#999'
                                }
                            }
                        },
                        toolbox: {
                            feature: {
                                dataView: {show: true, readOnly: false},
                                magicType: {show: true, type: ['line', 'bar']},
                                restore: {show: true},
                                saveAsImage: {show: true}
                            }
                        },
                        xAxis: [
                            {
                                type: 'category',
                                data: ['上周', '本周'],
                                axisPointer: {
                                    type: 'shadow'
                                }
                            }
                        ],
                        yAxis: [
                            {
                                type: 'value',
                                name: '订单总量',
                                min: 0,
                                max: 250,
                                interval: 50,
                                axisLabel: {
                                    formatter: '{value}'
                                }
                            },
                            {
                                type: 'value',
                                name: '日均订单量',
                                min: 0,
                                max: 25,
                                interval: 5,
                                axisLabel: {
                                    formatter: '{value}'
                                }
                            }
                        ],
                        series: [
                            {
                                name: '订单总量',
                                type: 'bar',
                                data: [info.analysisOrderThisWeekAndLastWeek.weekOrderContrast.last.order_count, info.analysisOrderThisWeekAndLastWeek.weekOrderContrast.this.order_count]
                            },
                            {
                                name: '日均订单量',
                                type: 'line',
                                yAxisIndex: 1,
                                data: [info.analysisOrderThisWeekAndLastWeek.weekOrderContrast.last.average, info.analysisOrderThisWeekAndLastWeek.weekOrderContrast.this.average],
                                axisLabel: {
                                    formatter: '{value} %'
                                }

                            }
                        ]
                    };
                    week_order_chart.setOption(week_order_option);
                }
                //渠道订单
                if(info.differentTerminalOrders) {
                    $('#channel_order').show();
                    var channel_order_chart = ec.init(document.getElementById('channel_order'),'macarons');
                    channel_order_option = {
                        title: {
                            text: '不同终端订单量'
                        },
                        tooltip: {
                            trigger: 'axis',
                            axisPointer: {            // 坐标轴指示器，坐标轴触发有效
                                type: 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
                            }
                        },
                        legend: {
//                            data: ['微商城', 'IOS', 'Android', 'WAP', 'PC'],
                        data: ['微商城', 'WAP'],
                            x: 'right'
                        },
                        grid: {
                            left: '3%',
                            right: '4%',
                            bottom: '3%',
                            containLabel: true
                        },
                        xAxis: {
                            type: 'value'
                        },
                        yAxis: {
                            type: 'category',
                            data: info.differentTerminalOrders.channelOrderContrast.days
                        },
                        series: [
                            {
                                name: '微商城',
                                type: 'bar',
                                stack: '总量',
                                itemStyle: {normal: {label: {show: true, position: 'insideRight'}}},
                                label: {
                                    normal: {
                                        show: true,
                                        position: 'insideRight'
                                    }
                                },
                                data: info.differentTerminalOrders.channelOrderContrast.channel.wechat
                            },
//                            {
//                                name: 'IOS',
//                                type: 'bar',
//                                stack: '总量',
//                                itemStyle: {normal: {label: {show: true, position: 'insideRight'}}},
//                                label: {
//                                    normal: {
//                                        show: true,
//                                        position: 'insideRight'
//                                    }
//                                },
//                                data: info.differentTerminalOrders.channelOrderContrast.channel.ios
//                            },
//                            {
//                                name: 'Android',
//                                type: 'bar',
//                                stack: '总量',
//                                itemStyle: {normal: {label: {show: true, position: 'insideRight'}}},
//                                label: {
//                                    normal: {
//                                        show: true,
//                                        position: 'insideRight'
//                                    }
//                                },
//                                data: info.differentTerminalOrders.channelOrderContrast.channel.android
//                            },
                            {
                                name: 'WAP',
                                type: 'bar',
                                stack: '总量',
                                itemStyle: {normal: {label: {show: true, position: 'insideRight'}}},
                                label: {
                                    normal: {
                                        show: true,
                                        position: 'insideRight'
                                    }
                                },
                                data: info.differentTerminalOrders.channelOrderContrast.channel.wap
                            },
//                            {
//                                name: 'PC',
//                                type: 'bar',
//                                stack: '总量',
//                                itemStyle: {normal: {label: {show: true, position: 'insideRight'}}},
//                                label: {
//                                    normal: {
//                                        show: true,
//                                        position: 'insideRight'
//                                    }
//                                },
//                                data: info.differentTerminalOrders.channelOrderContrast.channel.pc
//                            },
                        ]
                    };
                    channel_order_chart.setOption(channel_order_option);
                }
                //省区域订单分布
                if(info.newOrderDistributionThisWeek) {
                    $('#area_order').show();
                    var area_order_chart = ec.init(document.getElementById('area_order'),'macarons');
                    area_order_option = {
                        title: {
                            text: '本周新增订单分布',
                            x: 'left'
                        },
                        tooltip: {
                            trigger: 'item'
                        },
                        dataRange: {
                            min: 0,
                            max: info.newOrderDistributionThisWeek.areaOrder.max,
                            x: 'left',
                            y: 'bottom',
                            text: ['高', '低'],           // 文本，默认为数值文本
                            calculable: true
                        },
                        toolbox: {
                            show: false,
                            orient: 'vertical',
                            x: 'right',
                            y: 'center',
                            feature: {
                                mark: {show: true},
                                dataView: {show: true, readOnly: false},
                                restore: {show: true},
                                saveAsImage: {show: true}
                            }
                        },
                        series: [
                            {
                                name: '订单总量',
                                type: 'map',
                                mapType: 'china',
                                roam: false,
                                itemStyle: {
                                    normal: {label: {show: true}},
                                    emphasis: {label: {show: true}}
                                },
                                data: info.newOrderDistributionThisWeek.areaOrder.list
                            }
                        ]
                    };
                    area_order_chart.setOption(area_order_option);
                }
                //注册分布
                if(info.numberDailyRegistrationsPastWeek) {
                    $('#week_register_user').show();
                    var week_register_user_chart = ec.init(document.getElementById('week_register_user'),'macarons');
                    week_register_user_option = {
                        title: {
                            text: '过去一周每日注册用户数',
                            subtext: info.numberDailyRegistrationsPastWeek.weekUserRegister.sum,
                            x: 'left'
                        },
                        tooltip: {
                            trigger: 'axis'
                        },
                        legend: {
                            data: ['每日注册用户数'],
                            x: 'right'
                        },
                        toolbox: {
                            show: false,
                            feature: {
                                mark: {show: true},
                                dataView: {show: true, readOnly: false},
                                magicType: {show: true, type: ['line', 'bar', 'stack', 'tiled']},
                                restore: {show: true},
                                saveAsImage: {show: true}
                            }
                        },
                        calculable: true,
                        xAxis: [
                            {
                                type: 'category',
                                boundaryGap: false,
                                data: info.numberDailyRegistrationsPastWeek.weekUserRegister.days
                            }
                        ],
                        yAxis: [
                            {
                                type: 'value'
                            }
                        ],
                        series: [
                            {
                                name: '每日注册用户数',
                                type: 'line',
                                stack: '总量',
                                data: info.numberDailyRegistrationsPastWeek.weekUserRegister.counts,
                                markLine: {
                                    data: [
                                        {type: 'average', name: '平均值'}
                                    ]
                                }
                            }
                        ]
                    };
                    week_register_user_chart.setOption(week_register_user_option);
                }
            }
        );
    }
</script>
{% endblock %}

