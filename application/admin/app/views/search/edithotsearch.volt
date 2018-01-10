{% extends "layout.volt" %}

{% block content %}
<style>
    .form-group {
        margin-top:10px;
    }
    .middle-header{
        margin-left:50px;
        font-size:120%;
    }
</style>
    <div class="page-header">
        <h1>热门搜索</h1>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <!-- PAGE CONTENT BEGINS -->
            <form id="form" class="form-horizontal" action="/search/editHotSearch" method="post">
                    <div class="space-6"></div>
               <!--
               <div class="middle-header">PC热门搜索</div>
                          <div class="form-group">
                              <label class="col-sm-2 control-label no-padding-right">关键词</label>
                              <div class="col-sm-9">
                                  <div class="pos-rel">
                                        <textarea rows="3" cols="70"  placeholder="输入关键词, 用,号分隔多个关键词,超过9个关键词,只取前9个" name="pc" id="pc">{{data['pc']}}</textarea>
                                  </div>
                              </div>
                          </div>
                -->
                <div class="space-6"></div>
                <div class="middle-header">移动端热门搜索</div>
                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">关键词</label>
                    <div class="col-sm-9">
                        <div class="pos-rel">
                              <textarea rows="3" cols="70"  placeholder="输入关键词, 用,号分隔多个关键词,超过9个关键词,只取前9个" name="mobile" id="mobile">{{data['mobile']}}</textarea>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="col-md-offset-3 col-md-9">
                        <button class="btn btn-info ajax_button" type="button">
                            <i class="ace-icon fa fa-check bigger-110"></i>
                            保存
                        </button>
                    </div>
                </div>
            </form>
    </div>

{% endblock %}

{% block footer %}
    <script>
            $('.ajax_button').on('click',function () {
                    ajaxSubmit('form');
            });
    </script>
{% endblock %}