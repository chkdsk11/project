$(function () {
    //setup treeTable
    var option = {
        theme: 'vsStyle',
        expandLevel: 2,
        beforeExpand: function ($treeTable, id) {
            //判断id是否已经有了孩子节点，如果有了就不再加载，这样就可以起到缓存的作用
            if ($('.' + id, $treeTable).length) {
                return;
            }
            //这里的html可以是ajax请求
            var html = ''
                + '';

            $treeTable.addChilds(html);
        },
        onSelect: function ($treeTable, id) {
            window.console && console.log('onSelect:' + id);

        }

    };

    //bootstrap treeTable
    $('#treeTable1').treeTable(option);

    //为checkbox绑定选择事件
    //一级分类
    $("input").on('change', function () {
        var id = $(this).parent().parent().attr('id');//节点ID
        var checked = $(this).is(':checked');//是否选中

        //选中时，选中下级
        if (checked) {
            $('tr.'+ id).find('input').prop({checked:true});
        }else{//取消选中时，
            $('tr.'+ id).find('input').prop({checked:false});
        }
    });

    //二级分类
    $("input[level=1]").on('change', function () {
        var id = $(this).parent().parent().attr('id');//节点ID
        var checked = $(this).is(':checked');//是否选中

        //选中时，选中下级
        if (checked) {
            $('tr.'+ id).find('input').prop({checked:true});
        }else{//取消选中时，
            $('tr.'+ id).find('input').prop({checked:false});
        }
    });
});