{% extends "layout.volt" %}


{% block content %}
<style>
    .tools-box {
        /*overflow: hidden;*/
    }
    .tools-box label {
        float: left;
    }

    .tools-box .span-width {
        display: inline-block;
        margin: 0 5px;
        float: none;
    }
    .btn-sort i.on {color: #f00;}
    .btn-sort{position: relative;height:24px;font-size: 12px;width: 12px;}
    .btn-sort i {position: absolute;font-style: normal;cursor: pointer;font-family:sans-serif;}
    .btn-asc{transform:rotate(-90deg);-ms-transform:rotate(-90deg);-webkit-transform:rotate(-90deg);-o-transform:rotate(-90deg);-moz-transform:rotate(-90deg);left: 3px;  top: -3px;}
    .btn-desc{transform:rotate(-90deg);-ms-transform: rotate(-90deg);-webkit-transform: rotate(-90deg);-o-transform:rotate(-90deg);-moz-transform: rotate(-90deg);top: 5px;
        left: 3px;}
</style>
<link rel="stylesheet" href="http://{{ config.domain.static }}/assets/css/time.css">
<div class="page-content" style="
    height: 332px;width:100%;">
    <div class="row">
        <div class="col-xs-12">
            <div class="row">
                <div class="col-xs-12">

                    <div class="form-group clearfix">
                        <div class="row">
                            <form action="/search/hotList" method="get" id="export_button">
                                <div class="tools-box">
                                    <label class="clearfix">
                                        <span>搜索热词: </span><input type="text" style="width:200px;" name="keywords" value="{{ keywords }}" class="tools-txt" />
                                    </label>
                                    <label class="clearfix">
                                        <span>商品个数: </span><input type="text" class="tools-txt" style="width:80px;float:none;" name="startCount" value="{{ startCount }}" />
                                        <span class="span-width">-</span>
                                        <input type="text" class="tools-txt " style="width:80px;float:none;margin-left: 0" name="endCount" value="{{ endCount }}" />
                                    </label>
                                    <!--时间控制器 start -->
                                    <input type="hidden" value="{{ dataAt }}" name="dateAt" id="selectTime">
                                    <input type="hidden" value="{{ dateText }}" name="dateText" id="currentState">
                                    <div class="dtpicker" style="z-index: 1000;margin-left: 13px;">
                                        <div class="dtpicker-main">
				<span class="dtpicker-main-text">
					<span class="tit">最近1天</span>
					<span class="num"></span>
				</span>
                                            <i class="icon-cal"></i>
                                        </div>
                                        <div class="dtpicker-menu">
                                            <ul>
                                                <li class="active" data-type="1">
                                                    <span class="range-text" >最近1天</span>
                                                    <span class="date-text num"></span>
                                                </li>
                                                <li class="" data-type="7">
                                                    <span class="range-text" >最近7天</span>
                                                    <span class="date-text num"></span>
                                                </li>
                                                <li class="" data-type="30">
                                                    <span class="range-text" >最近30天</span>
                                                    <span class="date-text num"></span>
                                                </li>
                                                <li class="">
                                                    <span class="range-text">自然日</span>
                                                    <span class="icon-angle-disabled-right"></span>
                                                </li>
                                                <li class="">
                                                    <span class="range-text">自然周</span>
                                                    <span class="icon-angle-disabled-right"></span>
                                                </li>
                                                <li class="">
                                                    <span class="range-text">自然月</span>
                                                    <span class="icon-angle-disabled-right"></span>
                                                </li>
                                            </ul>
                                            <div class="calendar-wrapper">
                                                <div class="ui-calendar show month">
                                                    <div class="ui-calendar-pannel" data-role="pannel">
                                                        <span class="ui-calendar-control" data-role="prev-year">&lt;&lt;</span>
                                                        <span class="ui-calendar-control" data-role="prev-month">&lt;</span>
                                                        <span class="ui-calendar-control year" data-role="current-year"></span>
                                                        <span class="ui-calendar-control month" data-role="current-month"></span>
                                                        <span class="ui-calendar-control" data-role="next-month">&gt;</span>
                                                        <span class="ui-calendar-control" data-role="next-year">&gt;&gt;</span>
                                                    </div>
                                                    <div class="ui-calendar-container" data-role="container">
                                                        <table class="ui-calendar-date ui-calendar-hide" data-role="date-column">
                                                            <tbody>
                                                            <tr class="ui-calendar-day-column">
                                                                <th class="ui-calendar-day" data-role="day" data-value="0">一</th>
                                                                <th class="ui-calendar-day" data-role="day" data-value="1">二</th>
                                                                <th class="ui-calendar-day" data-role="day" data-value="2">三</th>
                                                                <th class="ui-calendar-day" data-role="day" data-value="3">四</th>
                                                                <th class="ui-calendar-day" data-role="day" data-value="4">五</th>
                                                                <th class="ui-calendar-day" data-role="day" data-value="5">六</th>
                                                                <th class="ui-calendar-day" data-role="day" data-value="6">日</th>
                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                        <table class="ui-calendar-month ui-calendar-hide" data-role="month-column">
                                                            <tbody>
                                                            <tr class="ui-calendar-month-column">
                                                                <td class="" data-role="01">1月</td>
                                                                <td class="" data-role="02">2月</td>
                                                                <td class="" data-role="03">3月</td>
                                                            </tr>
                                                            <tr class="ui-calendar-month-column">
                                                                <td class="" data-role="04">4月</td>
                                                                <td class="" data-role="05">5月</td>
                                                                <td class="" data-role="06">6月</td>
                                                            </tr>
                                                            <tr class="ui-calendar-month-column">
                                                                <td class="" data-role="07">7月</td>
                                                                <td class="" data-role="08">8月</td>
                                                                <td class="" data-role="09">9月</td>
                                                            </tr>
                                                            <tr class="ui-calendar-month-column">
                                                                <td class="" data-role="10">10月</td>
                                                                <td class="" data-role="11">11月</td>
                                                                <td class="" data-role="12">12月</td>
                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--时间控制器 end-->
                                    <label class="clearfix">
                                        <select name="platformId" style="margin-left: 10px;">
                                            <option value="0">所有端</option>
                                            <option {% if platformId is 'pc' %}selected{% endif %} value="pc">PC端</option>
                                            <option {% if platformId is 'wap' %}selected{% endif %} value="wap">WAP端</option>
                                        </select>
                                    </label>
                                    <input type="hidden" id="act_sort" name="act" value="{{ act }}">
                                    <input type="hidden" id="export" name="export" value="0">
                                    <label class="clearfix" style="float: right;">
                                        <button class="btn btn-primary export_button"  type="button">导出</button>
                                    </label>
                                    <label class="clearfix" style="float: right;">
                                        <input type="hidden" id="psize" name="psize" value="{{ psize }}" />
                                        <button class="btn btn-primary" id="submit" type="submit">搜索</button>
                                    </label>
                                </div>
                            </form>

                        </div>
                    </div>
                    <div>
                        <div style="margin-bottom: 15px;">
                            <button class="btn btn-sm btn-info word-list" data-id="1">加入词库</button>
                            <button class="btn btn-sm btn-info word-list" data-id="2">撤销加入词库</button>
                            <!--<button class="btn btn-sm btn-info word-list" data-id="3">加入黑名单</button>-->
                        </div>
                    </div>
                    <div>
                        <table id="dynamic-table" class="table table-striped table-bordered table-hover">
                            <thead>
                            <tr>
                                <th class="center">
                                    <input type="checkbox" class="checkbox_select">全选
                                </th>
                                <th class="center">关键词</th>
                                <th class="center">平台</th>
                                <th class="center">搜索次数
                                    <span class="btn-sort">
                                        <i class="btn-asc word-order-list {% if act is '1' %}on{% endif %}" data-id="1">></i>
                                        <i class="btn-desc word-order-list {% if act is '2' %}on{% endif %}" data-id="2"><</i>
                                    </span>
                                </th>
                                <th class="center">
                                    商品个数
                                    <span class="btn-sort">
                                        <i class="btn-asc word-order-list {% if act is '3' %}on{% endif %}" data-id="3">></i>
                                        <i class="btn-desc word-order-list {% if act is '4' %}on{% endif %}" data-id="4"><</i>
                                    </span>
                                </th>
                                <!--<th class="center">搜索结果页点击率</th>-->
                                <th class="center">
                                    操作
                                </th>
                            </tr>
                            </thead>
                            <tbody id="productRuleList">

                            <!-- 遍历商品信息 -->
                            {% if list is defined %}
                            {% if list != null %}
                                {% for v in list %}
                                <tr>
                                    <td class="center">
                                        <input type="checkbox" name="checkbox" value="{{ v['keywords'] }}">
                                    </td>
                                    <td class="center" {% if v['max_res'] is 0 and v['isOn'] is 1 %}style="color:red;"{% endif %}>
                                        {{ v['keywords'] }}
                                    </td>
                                    <td class="center">
                                        {{ v['platform_name'] }}
                                    </td>
                                    <td class="center">
                                        {{ v['count'] }}
                                    </td>
                                    <td class="center">
                                       <a target="_blank" href="{{ pcurl }}?keyword={{ v['keywords'] }}"  {% if v['max_res'] is 0 and v['isOn'] is 1 %}style="color:red;"{% endif %}>
                                           {% if v['min_res'] == v['max_res'] %}
                                                {{ v['min_res'] }}
                                           {% else %}
                                                {{ v['min_res'] }}-{{ v['max_res'] }}
                                           {% endif %}
                                       </a>
                                    </td>
                                    <!--<td class="center">-->
                                        <!--暂无数据……-->
                                    <!--</td>-->
                                    <td class="center">
                                        <button type="button" data-act="{{ v['keywords'] }}" class="word-list-delect {% if v['isOn'] is not 1 %}hide{% endif %}"> 撤销加入词库</button>
                                        <button type="button" data-act="{{ v['keywords'] }}" class="word-list-add {% if v['isOn'] is 1 %}hide{% endif %}"> 加入词库</button>
                                        <!--<button type="button" data-act="{{ v['keywords'] }}" class="black-list-add"> 加入黑名单</button>-->

                                    </td>
                                </tr>
                                {% endfor %}
                            {% else %}
                                <tr>
                                    <td class="center" colspan="6">
                                        暂无数据……
                                    </td>
                                </tr>
                            {% endif %}
                            {% endif %}
                            <!-- 遍历商品信息 end -->

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div><!-- /.col -->
    </div><!-- /.row -->
</div>
{{ page }}
{% endblock %}

{% block footer %}
<script type="text/javascript">
    $(document).ready(function() {
        //添加词库
        $(document).on('click', '.word-list-add', function () {
            var word = $(this).attr('data-act');
            var th = $(this);
            $.ajax({
                type: 'get',
                url: '/search/addWord?word=' + word,
                cache: false,
                dataType: 'json',
                success: function (msg) {
                    if (msg.status == 'success') {
                        layer_required(msg.info);
                        th.parent().find('.word-list-add').addClass('hide');
                        th.parent().find('.word-list-delect').removeClass('hide');
                        return;
                    } else {
                        layer_required(msg.info);
                        return;
                    }
                }
            })
        });

        //添加黑名单
        $(document).on('click', '.black-list-add', function () {
            var word = $(this).attr('data-act');
            var th = $(this);
            $.ajax({
                type: 'get',
                url: '/search/appendToBlacklist?word=' + word,
                cache: false,
                dataType: 'json',
                success: function (msg) {
                    if (msg.status == 'success') {
                        layer_required(msg.info);
                        window.location.reload();
                        return;
                    } else {
                        layer_required(msg.info);
                        return;
                    }
                }
            })
        });

        //移除词库
        $(document).on('click', '.word-list-delect', function () {
            var word = $(this).attr('data-act');
            var th = $(this);
            $.ajax({
                type: 'get',
                url: '/search/removeWord?word=' + word,
                cache: false,
                dataType: 'json',
                success: function (msg) {
                    if (msg.status == 'success') {
                        layer_required(msg.info);
                        th.parent().find('.word-list-delect').addClass('hide');
                        th.parent().find('.word-list-add').removeClass('hide');
                        return;
                    } else {
                        layer_required(msg.info);
                        return;
                    }
                }
            })
        });

        //导出功能
        $('.export_button').click(function () {
            $('#export').val('1');
            $("#submit").click();
        });

        //全选功能
        $('.checkbox_select').change(function () {
            var act = $(this).prop('checked');
            $("input:checkbox[name='checkbox']").each(function () {
                $(this).prop('checked', act);
            });
        });

        //批量操作
        $(document).on('click', '.word-list', function () {
            var act = $(this).attr('data-id');
            var word = '';
            $("input:checkbox[name='checkbox']:checked").each(function () {
                if (word == '') {
                    word += $(this).val();
                } else {
                    word += ',' + $(this).val();
                }
            });
            if (word == '') {
                layer_required('请选择要操作的数据！');
                return;
            }
            var url = '';
            switch (act) {
                case '1':
                    url = '/search/addWord?word=' + word;
                    break;
                case '2':
                    url = '/search/removeWord?word=' + word;
                    break;
                case '3':
                    url = '/search/appendToBlacklist?word=' + word;
                    break;
                default:
                    layer_required('请选择操作！');
                    return;
            }
            $.ajax({
                type: 'get',
                url: url,
                cache: false,
                dataType: 'json',
                success: function (msg) { msg.status= 'success';
                    if (msg.status == 'success') {
                        layer_required(msg.info);
                        $("input:checkbox[name='checkbox']:checked").each(function () {
                            switch (act) {
                                case '1':
                                    $(this).parent().parent().find('.word-list-add').addClass('hide');
                                    $(this).parent().parent().find('.word-list-delect').removeClass('hide');
                                    break;
                                case '2':
                                    $(this).parent().parent().find('.word-list-delect').addClass('hide');
                                    $(this).parent().parent().find('.word-list-add').removeClass('hide');
                                    break;
                                case '3':
                                    window.location.reload();
                                    break;
                            }
                        });
                        return;
                    } else {
                        layer_required(msg.info);
                        return;
                    }
                }
            })
        });

        //排序
        $(document).on('click', '.word-order-list', function () {
            var act = $(this).attr('data-id');
            $('#act_sort').val(act);
            $('#submit').click();
        });
    })


//    日期控制器
    var $dtpicker = $('.dtpicker');
    var $dtpickerMenu = $('.dtpicker-menu');
    var $uiCalendar = $(".ui-calendar");

    // 获取时间 y-m-d
    function getFormatDate(day){
        var day = day ? day : 0;
        var d = new Date( new Date().getTime() +86400000*day );
        var month = (d.getMonth()+1) < 10 ? '0'+(d.getMonth()+1) : (d.getMonth()+1);
        var date = d.getDate() < 10 ? '0'+d.getDate() : d.getDate();
        var str = d.getFullYear()+"-"+month+"-"+date;
        return str;
    }
    // 选择日期
    function choiceDate(type){
        var calendarMonth = $('.ui-calendar-month');
        var date = new Date();
        var curYear = date.getFullYear();
        var curMonth = date.getMonth()+1;
        $('[data-role="current-year"]').html(curYear+"年");
        $('[data-role="current-month"]').html(curMonth+"月");

        if(type == "month"){
            $(".ui-calendar").addClass("month").removeClass("week").removeClass("day");
            createKalendarMonth(curYear,curMonth);
        }
        if(type == "week"){
            $(".ui-calendar").addClass("week").removeClass("day").removeClass("month");
            createKalendarWeek(curYear,curMonth);
        }
        if(type == "day"){
            $(".ui-calendar").addClass("day").removeClass("week").removeClass("month");
            createKalendarDay(curYear,curMonth);
        }
    }

    // 创建日历- month
    function createKalendarMonth(year,month){
        var date = new Date();
        var curYear = date.getFullYear();
        var curMonth = date.getMonth()+1;
        var $td = $(".ui-calendar-month td");
        if(curYear == year){
            for(var i=0;i<12;i++){
                if(i < curMonth-1){
                    $td.eq(i).removeClass();
                }else if(i==curMonth-1){
                    $td.eq(i).removeClass();
                }else if(i > curMonth-1){
                    $td.eq(i).removeClass();
                    $td.eq(i).addClass("disabled-element");
                }
            }
            $('[data-role="prev-year"]').removeClass("disabled");
            $('[data-role="prev-month"]').removeClass("disabled");
            $('[data-role="next-year"]').addClass("disabled");
            month == curMonth ? $('[data-role="next-month"]').addClass("disabled") : $('[data-role="next-month"]').removeClass("disabled") ;
        }else{
            for(var i=0;i<12;i++){
                if(i < curMonth){
                    $td.eq(i).removeClass();
                    $td.eq(i).addClass("disabled-element");
                }else if(i==curMonth){
                    $td.eq(i).removeClass();
                }else if(i > curMonth-1){
                    $td.eq(i).removeClass();
                }
            }
            $('[data-role="next-month"]').removeClass("disabled");
            $('[data-role="next-year"]').removeClass("disabled");
            $('[data-role="prev-year"]').addClass("disabled");
            month == curMonth+1 ? $('[data-role="prev-month"]').addClass("disabled") : $('[data-role="prev-month"]').removeClass("disabled") ;
        }
        $td.eq(month-1).addClass("focused-element");
    }

    // 创建日历- day
    function createKalendarDay(year,month){
        flag = false;
        var date1 = new Date(year,month-1,1);
        var getDay1 = date1.getDay();		//这个月第一天是星期几
        var date2 = new Date(year,month,0);
        var getDate2 = date2.getDate();		//这个月多少天
        var date3 = new Date(year,month-1,0);
        var getDate3 = date3.getDate();		//上个月多少天

        var $table = $("table.ui-calendar-date");
        var html = '';
        $table.find(".ui-calendar-date-column").remove();

        var arrayDate = [];
        getDay1 == 0 ? getDay1=7 : "";
        arrayDate[getDay1-1] = 1;
        var arrayDateLen = arrayDate.length;
        for(var i=0;i<arrayDateLen-1;i++){
            arrayDate[arrayDateLen-2-i] = getDate3-i;
        }
        for(var i=0;i<7-arrayDateLen;i++){
            arrayDate[arrayDateLen+i] = 2 +i;
        }
        $table.append(kalendarWeek(arrayDate,year,month,getDate2,getDay1));


        var x = arrayDate[arrayDate.length-1];  //第一周最后的日期
        var arrayDate2 = [];
        for(var j=0;j<getDate2-x;j++){
            arrayDate2[j] = x+1+j;
        }
        if(arrayDate2.length%7 != 0){
            var num = arrayDate2.length%7;
            for(var i=0;i<7-num;i++){
                arrayDate2.push(1+i);
            }
        }
        for(var k=0;k<arrayDate2.length/7;k++){
            var arrayDate3 = arrayDate2.slice(7*k,7*(k+1));
            $table.append(kalendarWeek(arrayDate3,year,month,getDate2,getDay1));
        }

        // 当天日期高亮
        // var d= new Date();
        // if(year == d.getFullYear() && month == d.getMonth()+1){
        // 	$table.find("td").each(function(i,item){
        // 		d.getDate() == parseInt($(item).html())? $(item).addClass("focused-element"):"";
        // 	});
        // }

        $('[data-role="next-month"]').removeClass("disabled");
        $('[data-role="next-year"]').removeClass("disabled");
        $('[data-role="prev-year"]').removeClass("disabled");
        $('[data-role="prev-month"]').removeClass("disabled");
    }

    // 创建日历- week
    function createKalendarWeek(year,month){
        flag = false;
        var date1 = new Date(year,month-1,1);
        var getDay1 = date1.getDay();		//这个月第一天是星期几
        var date2 = new Date(year,month,0);
        var getDate2 = date2.getDate();		//这个月多少天
        var date3 = new Date(year,month-1,0);
        var getDate3 = date3.getDate();		//上个月多少天

        var $table = $("table.ui-calendar-date");
        var html = '';
        $table.find(".ui-calendar-date-column").remove();

        var arrayDate = [];
        getDay1 == 0 ? getDay1=7 : "";
        arrayDate[getDay1-1] = 1;
        var arrayDateLen = arrayDate.length;
        for(var i=0;i<arrayDateLen-1;i++){
            arrayDate[arrayDateLen-2-i] = getDate3-i;
        }
        for(var i=0;i<7-arrayDateLen;i++){
            arrayDate[arrayDateLen+i] = 2 +i;
        }
        $table.append(kalendarWeek(arrayDate,year,month,getDate2,getDay1));

        var x = arrayDate[arrayDate.length-1];  //第一周最后的日期
        var arrayDate2 = [];
        for(var j=0;j<getDate2-x;j++){
            arrayDate2[j] = x+1+j;
        }
        if(arrayDate2.length%7 != 0){
            var num = arrayDate2.length%7;
            for(var i=0;i<7-num;i++){
                arrayDate2.push(1+i);
            }
        }
        for(var k=0;k<arrayDate2.length/7;k++){
            var arrayDate3 = arrayDate2.slice(7*k,7*(k+1));
            $table.append(kalendarWeek(arrayDate3,year,month,getDate2,getDay1));
        }
        $('[data-role="next-month"]').removeClass("disabled");
        $('[data-role="next-year"]').removeClass("disabled");
        $('[data-role="prev-year"]').removeClass("disabled");
        $('[data-role="prev-month"]').removeClass("disabled");

        $(".dtpicker").on("mouseenter",".week .ui-calendar-date-column td",function(e){
            if(!$(this).hasClass("focused-element")){
                $(this).parent().find("td").addClass("focused-element");
            }
        }).on("mouseleave",".week .ui-calendar-date-column td",function(e){
            $(this).parent().find("td").removeClass("focused-element");
        })
    }

    // 创建日历的一个星期
    var flag = false;
    function kalendarWeek(array,year,month,totlalDay,firstDay){
        var html = '';
        var len = array.length;
        html += '<tr class="ui-calendar-date-column">';
        var y = year;
        var m = month;
        var trLen = $(".ui-calendar-date tr").length;
        for(var i=0;i<len;i++){
            month = m;
            year = y;

            if(i<firstDay-1 && trLen<2){
                if(month-1>0){
                    month = month -1;
                }else{
                    year = year - 1;
                    month = 12;
                }
            }

            if(array[0] > 7 && array[i] < 7 && trLen > 2){
                if(month+1 > 12){
                    month = 1;
                    year = year + 1;
                }else{
                    month = month + 1;
                }
            }

            if(array[i] == 1 ){
                flag ? flag = false : flag = true ;
            }
            if(flag){
                html += '<td class="current-month ui-calendar-day-'+(i+1)+'" data-role="date" data-value="'+(year+"-"+month+"-"+array[i])+'">'+array[i]+'</td>';
            }else{
                html += '<td class="previous-month ui-calendar-day-'+(i+1)+'" data-role="date" data-value="'+(year+"-"+month+"-"+array[i])+'">'+array[i]+'</td>';
            }

        }
        html += '</tr>';
        return html;
    }

    $dtpicker.find('.dtpicker-main').on("click",function(event){
        if($dtpicker.hasClass("open")){
            $dtpicker.removeClass("open");
        }else{
            $dtpicker.addClass("open");
        };
        event.stopPropagation();
    });

    $(document).on("click",function(){
        $dtpicker.removeClass("open");
    });

    $(".calendar-wrapper").on("click",".disabled-element,.ui-calendar-day-column,.ui-calendar-pannel",function(e){
        event.stopPropagation();
    });
    $dtpickerMenu.on("click",function(event){
        // event.stopPropagation();
    }).find('ul li').mouseenter(function(){
        if($(this).index() == 0){
            $("#currentState").val(1);
            $(this).find(".date-text").html('（'+getFormatDate(-1)+'~'+getFormatDate(-1)+'）');
        }else if($(this).index() == 1){
            $("#currentState").val(2);
            $(this).find(".date-text").html('（'+getFormatDate(-7)+'~'+getFormatDate(-1)+'）');
        }else if($(this).index() == 2){
            $("#currentState").val(3);
            $(this).find(".date-text").html('（'+getFormatDate(-30)+'~'+getFormatDate(-1)+'）');
        }else if($(this).index() == 3){      //日
            $("#currentState").val(4);
            $dtpicker.addClass("calendar-show");
            $(".ui-calendar-container table").each(function(i,item){
                $(item).addClass('ui-calendar-hide');
            });
            $('.ui-calendar-date').removeClass('ui-calendar-hide');
            choiceDate('day');
        }else if($(this).index() == 4){      //周
            $("#currentState").val(5);
            $dtpicker.addClass("calendar-show");
            $(".ui-calendar-container table").each(function(i,item){
                $(item).addClass('ui-calendar-hide');
            });
            $('.ui-calendar-date').removeClass('ui-calendar-hide');
            choiceDate('week');
        }else if($(this).index() == 5){     //月
            $("#currentState").val(6);
            $dtpicker.addClass("calendar-show");
            $(".ui-calendar-container table").each(function(i,item){
                $(item).addClass('ui-calendar-hide');
            });
            $('.ui-calendar-month').removeClass('ui-calendar-hide');
            choiceDate('month');
        };
        $(this).index() == 0 || $(this).index() == 1 || $(this).index() == 2 ? $dtpicker.removeClass("calendar-show") : "";
        $dtpickerMenu.find('ul li').each(function(i,item){
            $(item).removeClass('active');
        });
        $(this).addClass('active');
    }).on("click",function(e){
        if($(this).data("type")){
            var html = $(this).find(".date-text").html();
            var txt = $(this).find(".range-text").html();
            choiceTime(txt,html);
        }
    });



    $(".dtpicker").on("click",".day .ui-calendar-date td,.week .ui-calendar-date td,.month .ui-calendar-month td",function(e){
        if($(".ui-calendar").hasClass("day")){
            var html = '（'+$(this).data("value")+'~'+$(this).data("value")+'）';
            choiceTime("自然日",html);
        }
        if($(".ui-calendar").hasClass("week")){
            var $child = $(this).parent().children();
            var html = '（'+$child.eq(0).data("value")+'~'+$child.eq(6).data("value")+'）';
            choiceTime("自然周",html);
        }
        if($(".ui-calendar").hasClass("month")){
            var y = parseInt($('[data-role="current-year"]').html());
            var m = parseInt($(this).data("role"));
            var d = new Date(y,m,0);
            m < 10 ? m = "0"+m : m;
            var lastDay = d.getDate();
            var html = '（'+y+'-'+m+'-01~'+y+'-'+m+'-'+lastDay+'）';
            choiceTime("自然月",html);
        }
    })

    // 选择时间-上一年
    $('[data-role="prev-year"]').on("click",function(){
        var showYear = parseInt($('[data-role="current-year"]').html());
        var showMonth = parseInt($('[data-role="current-month"]').html());
        $('[data-role="current-year"]').html(showYear-1+"年");
        if($(".ui-calendar").hasClass("month")){
            $('[data-role="current-month"]').html("12月");
            createKalendarMonth(parseInt($('[data-role="current-year"]').html()),12);
        }
        if($(".ui-calendar").hasClass("week")){
            flag = false;
            createKalendarWeek(parseInt($('[data-role="current-year"]').html()),showMonth);
        }
        if($(".ui-calendar").hasClass("day")){
            flag = false;
            createKalendarDay(parseInt($('[data-role="current-year"]').html()),showMonth);
        }
    });

    // 选择时间-上个月
    $('[data-role="prev-month"]').on("click",function(){
        var showYear = parseInt($('[data-role="current-year"]').html());
        var showMonth = parseInt($('[data-role="current-month"]').html());
        if(showMonth-1==0){
            $('[data-role="current-year"]').html(showYear-1+"年");
            $('[data-role="current-month"]').html("12月");
        }else{
            $('[data-role="current-month"]').html(showMonth-1+"月");
        }
        if($(".ui-calendar").hasClass("month")){
            if(parseInt($('[data-role="current-month"]').html()) == 0){
                $('[data-role="current-year"]').html(showYear-1+"年");
                $('[data-role="current-month"]').html("12月");
            }
            createKalendarMonth(parseInt($('[data-role="current-year"]').html()),parseInt($('[data-role="current-month"]').html()));
        }
        if($(".ui-calendar").hasClass("week")){
            flag = false;
            createKalendarWeek(parseInt($('[data-role="current-year"]').html()),parseInt($('[data-role="current-month"]').html()));
        }
        if($(".ui-calendar").hasClass("day")){
            flag = false;
            createKalendarDay(parseInt($('[data-role="current-year"]').html()),parseInt($('[data-role="current-month"]').html()));
        }
    });

    // 选择时间-下个月
    $('[data-role="next-month"]').on("click",function(){
        var showYear = parseInt($('[data-role="current-year"]').html());
        var showMonth = parseInt($('[data-role="current-month"]').html());
        if(showMonth+1>12){
            $('[data-role="current-year"]').html(showYear+1+"年");
            $('[data-role="current-month"]').html("1月");
        }else{
            $('[data-role="current-month"]').html(showMonth+1+"月");
        }
        if($(".ui-calendar").hasClass("month")){
            createKalendarMonth(parseInt($('[data-role="current-year"]').html()),parseInt($('[data-role="current-month"]').html()));
        }
        if($(".ui-calendar").hasClass("week")){
            flag = false;
            createKalendarWeek(parseInt($('[data-role="current-year"]').html()),parseInt($('[data-role="current-month"]').html()));
        }
        if($(".ui-calendar").hasClass("day")){
            flag = false;
            createKalendarDay(parseInt($('[data-role="current-year"]').html()),parseInt($('[data-role="current-month"]').html()));
        }
    });

    // 选择时间-下一年
    $('[data-role="next-year"]').on("click",function(){
        var showYear = parseInt($('[data-role="current-year"]').html());
        var showMonth = parseInt($('[data-role="current-month"]').html());
        $('[data-role="current-year"]').html(showYear+1+"年");
        if($(".ui-calendar").hasClass("month")){
            $('[data-role="current-month"]').html("1月");
            createKalendarMonth(parseInt($('[data-role="current-year"]').html()),1);
        }
        if($(".ui-calendar").hasClass("week")){
            flag = false;
            createKalendarWeek(parseInt($('[data-role="current-year"]').html()),showMonth);
        }
        if($(".ui-calendar").hasClass("day")){
            flag = false;
            createKalendarDay(parseInt($('[data-role="current-year"]').html()),showMonth);
        }
    });

    function choiceTime(txt,time){
        $(".dtpicker-main .tit").html(txt);
        $(".dtpicker-main .num").html(time);
        $("#selectTime").val(time.replace('（',"").replace('）',""));
        localStorage.setItem("searchTime",time);
        localStorage.setItem("searchTxt",txt);
    }
    if(localStorage.getItem("searchTime") && $("#currentState").val()!= 1){
        $(".dtpicker-main .num").html(localStorage.getItem("searchTime"));
    }else{
        $(".dtpicker-main .num").html('（'+getFormatDate(-1)+'~'+getFormatDate(-1)+'）');
    }

    if(localStorage.getItem("searchTxt") && $("#currentState").val()!= 1){
        $(".dtpicker-main-text .tit").html(localStorage.getItem("searchTxt"));
    }else{
        $(".dtpicker-main-text .tit").html('最近1天');
    }
</script>
{% endblock %}