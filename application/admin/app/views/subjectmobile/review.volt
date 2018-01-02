{% extends "layout.volt" %}

{% block content %}
<style>
    .form-group {
        margin-top:10px;
    }
    .view-content {
        margin: 30px auto;
        overflow: hidden;
    }
    .mobile {
        width: 375px;
        height: 667px;
    }

    iframe {
        width: 100%;
        height: 100%;
    }


</style>
<div class="row">
    <div class="view-content mobile">
        {% if link is defined %}
		<iframe src="{{ link }}" frameborder="0"></iframe>
		{% else %}
		暂无预览...
		{% endif %}
    </div>
</div>
{% endblock %}

{% block footer %}
    <script src="http://{{ config.domain.static }}/assets/js/ajaxfileupload.js"></script>
    <script src="http://{{ config.domain.static }}/assets/admin/js/skuad/skuad.js"></script>
{% endblock %}