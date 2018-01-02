<head>
    <meta http-equiv="Content-Language" content="zh-CN">
    <meta HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=utf-8">
    <meta http-equiv="refresh" content="{{ time }};url={{ url }}">
    <title>诚仁堂商城提示</title>
    <style>
        .
    </style>
</head>
<body>
<table width="500" border="1" align="center" cellpadding="3" cellspacing="0">
     <tr>
      <th align="center" bgcolor="#cccccc">温馨提示</th>
     </tr>
     <tr>
      <td>&nbsp;&nbsp;&nbsp;{% if status is 'success' %}成功{% else %}错误{% endif %}提示：<br/>
       <span class="{{ status }}">
           {{ infoI }}
           </span><br />
       跳转页面路径：{{ url }}<br />
       停留时间：{{ time }}<br />
       <span id="time_tiao">{{ time }}</span>秒后返回指定页面！<br />
       如果浏览器无法跳转，<a href="{{ url }}" rel="external nofollow" rel="external nofollow" >请点击此处</a>。</td>
     </tr>
</table>
<script>
    function timeJ(){
        var t = document.getElementById('time_tiao').innerHTML;
        window.setTimeout("timeJ()",1000);
        document.getElementById('time_tiao').innerHTML = t-1;
    }
    window.setTimeout("timeJ()",1000);
</script>
</body>