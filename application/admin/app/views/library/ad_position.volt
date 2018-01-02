<div id="shop_postition" class="form-group">
    <div class="col-xs-12" id="postitionBox">
        <select name="ad_position[]" id="one_postition" class="sku_menu_row1" {% if disabled is defined and disabled == 1 %}disabled="disabled"{% endif %}>
        <option value="0">--请选择--</option>
        {% if ad_position is defined %}
        {% for k,v in ad_position %}
        <option {% if ad_positionID[0] is defined and ad_positionID[0] == v['id'] %}selected{% endif %} value="{{v['id']}}">{{v['adpositionid_name']}}({{v['versions']}})[{{v['channel']}}]</option>
    {% endfor %}
    {% endif %}
</select>
<select class="sku_menu_row1" name="ad_position[]" id="two_postition" {% if postitionID[1] is not defined %}style="display: none;"{% endif %} {% if disabled is defined and disabled == 1 %}disabled="disabled"{% endif %}>
        {% if postition_is[0] is defined %}
        {% if postitionID[1] == 0 %}<option selected value="0">--请选择--</option>{% endif %}
        {% for k,v in postition_is[0] %}
<option {% if postitionID[1] is defined and postitionID[1] == v['id'] %}selected{% endif %} value="{{v['id']}}">{{v['postition_name']}}({{v['versions']}})[{{v['channel']}}]</option>
        {% endfor %}
        {% endif %}
        </select>
<select class="sku_menu_row1" name="ad_position[]" id="three_postition" {% if postitionID[2] is not defined %}style="display: none;"{% endif %} {% if disabled is defined and disabled == 1 %}disabled="disabled"{% endif %}>
        {% if postition_is[1] is defined %}
        {% if postitionID[2] == 0 %}<option selected value="0">--请选择--</option>{% endif %}
        {% for k,v in postition_is[1] %}
<option {% if postitionID[2] is defined and postitionID[2] == v['id'] %}selected{% endif %} value="{{v['id']}}">{{v['postition_name']}}</option>
        {% endfor %}
        {% endif %}
        </select>
<span class="postition_son">
            {% if postition_is[2] is defined %}
            <select name="ad_position[]" class="postition_infinite sku_menu_row1" {% if disabled is defined and disabled == 1 %}disabled="disabled"{% endif %}>
                {% for k,v in postition_is[2] %}
                <option {% if postitionID[3] is defined and postitionID[3] == v['id'] %}selected{% endif %} value="{{v['id']}}">{{v['postition_name']}}</option>
        {% endfor %}
        </select>
        {% endif %}

        {% if postition_is[3] is defined %}
<select name="ad_position[]" class="postition_infinite sku_menu_row1">
{% for k,v in postition_is[3] %}
<option {% if postitionID[4] is defined and postitionID[4] == v['id'] %}selected{% endif %} value="{{v['id']}}">{{v['postition_name']}}</option>
        {% endfor %}
        </select>
        {% endif %}
        {% if postition_is[4] is defined %}
<select name="ad_position[]" class="postition_infinite sku_menu_row1">
{% for k,v in postition_is[4] %}
<option {% if postitionID[5] is defined and postitionID[5] == v['id'] %}selected{% endif %} value="{{v['id']}}">{{v['postition_name']}}</option>
        {% endfor %}
        </select>
        {% endif %}
        </span>
        </div>
        </div>
