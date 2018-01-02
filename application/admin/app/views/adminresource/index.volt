{% extends "layout.volt" %}

{% block content %}
	<style type="text/css">
	    tr[class^="level-son"]{
	        display: none;
	    }
	    tr.level-son-1{
	        display: table-row;
	    }
	    .toggle-level{
	        cursor: pointer;
	    }
	    td > i{
	        display: inline-block;
	        width: 20px;
	        height: 14px;
	    }
	    .table-btn-sm{
	        border-width: 2px;
	        font-size: 12px;
	        padding: 2px 5px;
	        line-height: 1.39;
	    }
	    .table-btn-sm + .table-btn-sm{
	        margin-left: 20px;
	    }
	    a.btn:active {
	        top: 1px;
	    }
	    #addFrist{
	        position: absolute;
	        top: 5px;
	        right: 10px;
	    }
	    #addFrist:active{
	        top: 6px;
	    }
	</style>
	<table id="myTable" class="table table-bordered table-hover dataTable no-footer" role="grid">
	    <thead>
		    <tr role="row">
		        <th tabindex="0" style="width: 15%;">ID</th>
		        <th tabindex="0" style="width: 30%;">资源权限名称</th>
		        <th tabindex="0" style="width: 5%;">等级</th>
                <th tabindex="0" style="width: 15%;">导航显示</th>
                <th tabindex="0" style="width: 10%;">导航排序</th>
		        <th tabindex="0" style="width: 40%;position: relative;">操作
		            <a href="/adminresource/add?site=0" class="btn btn-success table-btn-sm" id="addFrist">
		                <i class="ace-icon glyphicon glyphicon-plus"></i>
		                添加资源模块
		            </a>
		        </th>
		    </tr>
	    </thead>
	    <tbody>
	    
	    </tbody>
	</table>
{% endblock %}

{% block footer %}
<script>
    $(function(){
        $.post('/adminresource/index',{}, function(data){
            var arr = [];
            arr = filterData(arr, data);
            createTable(arr);
        });

        function filterData(arr, data){
            console.dir(data);
            var len = data.length,
                    son = false;
            if(!len) return arr;
            for(var i = 0; i < len; i++){
                obj = data[i];
                son = obj.son ? true : false;
                arr.push({
                    "id" : obj.id,
                    "name" : obj.name,
                    "level" : obj.level,
                    "pid": obj.pid,
                    "route": obj.route,
                    "son": son,
                    'show_nav': obj.show_nav,
                    'show_order': obj.show_order,
                });
                obj.son && filterData(arr, obj.son);
            }
            return arr;
        }

        function createTable(data) {
            var html = '',
                    obj = null,
                    cls = '';
            for(var i = 0, len = data.length; i < len; i++){
                obj = data[i];
                if(obj.id == 1009){
                    console.log('sss');
                }
                if(obj.son && obj.level < 3){
                    cls = 'ace-icon glyphicon glyphicon-plus toggle-level';
                }else{
                    cls = '';
                }
                html +=
                        '<tr role="row" id="resource_'+ obj.id +'" class="level-son-'+ obj.level +'" data-pid='+ obj.pid +'>'+
                        '<td>├ ';
                for(var j=1;j<obj.level;j++){
                    html += '——';
                }
                var disable_str = obj.level >= 3 ? 'disabled="true"':'';
                var show_nav_str = obj.show_nav > 0 ? '是':'否';
                if(obj.level <= 3){
                    html += '<i class="'+ cls +'" data-pid='+ obj.pid +' data-id='+ obj.id +' data-level='+ obj.level +' data-son='+ obj.son +'></i>'+
                        obj.id +
                        '</td>'+
                        '<td>'+ obj.name +'</td>'+
                        '<td>'+ obj.level +'</td>'+
                        '<td>'+ show_nav_str +'</td>'+
                        '<td>'+ obj.show_order +'</td>'+
                        '<td>'+
                        '<a '+ disable_str +' href="/adminresource/add?type=son&pid='+obj.id+'" class="btn btn-success table-btn-sm">'+
                        '<i class="ace-icon glyphicon glyphicon-plus"></i>'+
                        '添加子模块'+
                        '</a>'+
                        '<a href="/adminresource/edit?id='+obj.id+'" class="btn btn-success table-btn-sm">'+
                        '<i class="ace-icon glyphicon glyphicon-pencil"></i>'+
                        '编辑'+
                        '</a>'+
                        '<a href="javascript:void(0);" class="btn btn-success table-btn-sm is_enable_click" id="'+obj.id+'" name="'+obj.route+'">';
                    if(obj.route == 1){
                        html += '<i class="ace-icon fa  fa-circle-o"></i>启用';
                    }else{
                        html += '<i class="ace-icon fa fa-ban"></i>删除';
                    }
                    html += '</a>'+
                        '</td>'+
                        '</tr>';
                }

            };
            $('#myTable tbody').html(html);
        }

        //切换分级事件（展开 or 收缩）
        $('#myTable tbody').on('click', '.toggle-level', function(){
            var $this = $(this),
                    on = $this.hasClass('glyphicon-minus'),     //判断是否展开
                    level = $this.data('level'),                //当前等级
                    id = $this.data('id'),                      //当前id
                    $son = $('.level-son-' + (Number(level) + 1) + '[data-pid='+ id +']');
            if(on){     //当前收缩
                hideAllson(level, id);
                $this.removeClass('glyphicon-minus').addClass('glyphicon-plus');
            }else{      //当前展开(把其他展开的先收缩)
                hideOther($this);
                $son.css('display', 'table-row');
                $this.addClass('glyphicon-minus').removeClass('glyphicon-plus');
            }
        });

//        function hideAllson(level, id){
//            var $son = $('.level-son-' + (Number(level) + 1) + '[data-pid='+ id +']'),
//                    $toggle = $son.find('.toggle-level');
//            if($toggle.hasClass('glyphicon-minus')){
//                hideAllson($toggle.data('level'), $toggle.data('id'));
//            }
//            $son.css('display', 'none');
//            $toggle.removeClass('glyphicon-minus').addClass('glyphicon-plus');
//        }
        function hideAllson(level, id){
            var $son = $('.level-son-' + (Number(level) + 1) + '[data-pid='+ id +']'),
                    $toggle = $son.find('.toggle-level');
            for(var i = $toggle.length; i--;){
                var $obj = $($toggle[i]);
                if($obj.hasClass('glyphicon-minus')){
                    hideAllson($obj.data('level'), $obj.data('id'));
                }
            }
            $son.css('display', 'none');
            $toggle.removeClass('glyphicon-minus').addClass('glyphicon-plus');
        }


        function hideOther($obj){
            var level = $obj.data('level'),
                    all = null;
            if(level == '1'){
                all = $('#myTable .glyphicon-minus[data-level="1"][data-son="true"]');
            }else{
                all = $('#myTable .glyphicon-minus[data-pid='+ $obj.data('pid') +']');
            }
            for(var i = all.length; i--;){
                var $toggle = $(all[i]);
                if($toggle.data('id') != $obj.data('id')){
                    hideAllson($toggle.data('level'), $toggle.data('id'));
                    $toggle.removeClass('glyphicon-minus').addClass('glyphicon-plus');
                }
            }
        }
        //删除功能权限
        $(document).on('click','.is_enable_click',function(){
            var id = $(this).attr('id');
            var resourceObj = $("#resource_"+id);
            var is_del=confirm('是否删除此功能');
            if(!is_del){
                return;
            }
            $.getJSON('/adminresource/del?id='+id, function(msg){
                if (msg.code == 200) {
                    layer.msg('删除成功！', {icon: 1});
                    resourceObj.hide();
                } else {
                    layer.msg(msg.data, {icon: 2});
                }
            });
        });
    });
</script>
{% endblock %}
