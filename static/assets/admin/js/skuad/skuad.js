/**
 * Created by Administrator on 2016/8/26.
 */
//上传图片
$(document).on('change', '#logoUpload,#logoUpload2,#pc_image,#pc_image2', function()    {
    uploadFile('/sku/upload', $(this).attr('id'), $(this).attr('data-img'));
});
$(function(){
    //编辑器
    var editor;
    KindEditor.ready(function(K) {
        editor = K.create('#skuad_desc', {
            height: "280px",
            allowFileManager: true,
            uploadJson: '/sku/upload',
            items : ['source','formatblock','fontname','fontsize','forecolor','hilitecolor','bold','italic','underline','strikethrough','removeformat','justifyleft','justifycenter','justifyright','plainpaste','wordpaste','link','unlink','image','multiimage','clearhtml','fullscreen'],
            afterBlur: function(){this.sync();},
            afterCreate : function() {
                this.loadPlugin('autoheight');
            },
            afterUpload: function(url){
                console.log(url)
            }
        });
    });
    //提交form
    $("#form").submit(function(){
        var ad_name = $(':input[name=ad_name]').val();
        if ($(ad_name).val() == '')
        {
            layer_required('名称不能为空！');
            $(ad_name).focus();
        }else if(ad_name.length>20){
            layer_required('名称太长，不能超出20字！');
        }else {
            ajaxSubmit('form');
        }
        return false;
    });
    $(document).keypress(function (e) {
        if( e.which == 13 ){
            return false;
        }
    });
});
