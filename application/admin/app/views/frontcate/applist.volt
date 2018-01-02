{% extends "layout.volt" %}

{% block path %}
<li class="active">商品与分类管理</li>
<li class="active"><a href="/category/list">分类管理</a></li>
<li class="active">分类列表</li>
{% endblock %}

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
        <!--<th tabindex="0" style="width: 15%;">分类ID</th>-->
        <th tabindex="0" style="width: 15%;">分类名称</th>
        <th tabindex="0" style="width: 5%;">等级</th>
        <th tabindex="0" style="width: 5%;">排序</th>
        <th tabindex="0" style="width: 40%;position: relative;">操作
            <a href="/frontcate/addfirst?type=app" class="btn btn-success table-btn-sm" id="addFrist">
                <i class="ace-icon glyphicon glyphicon-plus"></i>
                添加一级分类
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
        $.getJSON('/frontcate/getcate?type=app', function(data){
            //console.log(data);
            var arr = [];
            arr = filterData(arr, data);
            createTable(arr);
        });

        function filterData(arr, data){
            var len = data.length,
                    son = false;
            if(!len) return arr;
            for(var i = 0; i < len; i++){
                obj = data[i];
                son = obj.son ? true : false;
                arr.push({
                    "id" : obj.id,
                    "categoryName" : obj.category_name,
                    "level" : obj.level,
                    "pid": obj.pid,
                    'sort': obj.sort,
                    "is_enable": obj.enable,
                    "son": son
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
                if(obj.son){
                    cls = 'ace-icon glyphicon glyphicon-plus toggle-level';
                }else{
                    cls = '';
                }
                html +=
                        '<tr role="row" class="level-son-'+ obj.level +'" data-pid='+ obj.pid +'>'+
                        '<td>├ ';
                for(var j=1;j<obj.level;j++){
                    html += '——';
                }
                html += '<i class="'+ cls +'" data-pid='+ obj.pid +' data-id='+ obj.id +' data-level='+ obj.level +' data-son='+ obj.son +'></i>'+
                        obj.categoryName +
                        '</td>'+
                        '<td>'+ obj.level +'</td>'+
                        '<td class="center"><input class="sort-edit" style="width:60px;" data-id='+ obj.id +' value='+ obj.sort +' ></td>'+
                        '<td class="center">'+
                        '<a href="/frontcate/addchild?type=app&id='+obj.id+'" class="btn btn-success table-btn-sm">'+
                        '<i class="ace-icon glyphicon glyphicon-plus"></i>'+
                        '添加子分类'+
                        '</a>'+
                        '<a href="/frontcate/edit?id='+obj.id+'" class="btn btn-success table-btn-sm">'+
                        '<i class="ace-icon glyphicon glyphicon-pencil"></i>'+
                        '编辑'+
                        '</a>'+
                        '<a href="javascript:void(0);" class="btn btn-success table-btn-sm is_enable_click" id="'+obj.id+'" name="'+obj.is_enable+'">';
                if(obj.is_enable == 0){
                    html += '<i class="ace-icon fa  fa-circle-o"></i>启用';
                }else{
                    html += '<i class="ace-icon fa fa-ban"></i>禁用';
                }
                html += '</a>'+
                        '</td>'+
                        '</tr>';
            }
            $('#myTable tbody').html(html);
        }

        /*更新sort*/
        $('#myTable').on('change','.sort-edit',function(){
            var sort = $(this).val();
            var objId = $(this).data('id');
            if(objId){
                layer_request('post','/frontcate/editsort', {id:objId,sort:sort});
            }
        })

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
        //启用|禁用控制
        $(document).on('click','.is_enable_click',function(){
            var id = $(this).attr('id');
            var is_enable = $(this).attr('name');
            var th = this;
            var _html = '';
            if(is_enable == 0){
                _html += '<i class="ace-icon fa fa-ban"></i>禁用';
                var _name = 1;
            }else{
                _html += '<i class="ace-icon fa  fa-circle-o"></i>启用';
                var _name = 0;
            }
            $.getJSON('/frontcate/isSwitch?id='+id+'&is_enable='+is_enable+'&type=app', function(msg){
                if(msg.status == 'success'){
                    $(th).html(_html);
                    $(th).attr('name',_name);
                }else{
                    layer.msg(msg.info, {shade: 0.3, time: 300}); return false;
                }
            });
        });
        $(document).keypress(function (e) {
            if( e.which == 13 ){
                return false;
            }
        });
    });
</script>
{% endblock %}