<div id="shop_category" class="form-group">
    <div class="col-xs-12" id="categoryBox">
        <select name="shop_category[]" id="one_category" class="sku_menu_row1" {% if disabled is defined and disabled == 1 %}disabled="disabled"{% endif %}>
            <option value="0">--请选择--</option>
            {% if category is defined %}
            {% for k,v in category %}
            <option {% if categoryID[0] is defined and categoryID[0] == v['id'] %}selected{% endif %} value="{{v['id']}}">{{v['category_name']}}</option>
            {% endfor %}
            {% endif %}
        </select>
        <select class="sku_menu_row1" name="shop_category[]" id="two_category" {% if categoryID[1] is not defined %}style="display: none;"{% endif %} {% if disabled is defined and disabled == 1 %}disabled="disabled"{% endif %}>
            {% if category_is[0] is defined %}
            {% if categoryID[1] == 0 %}<option selected value="0">--请选择--</option>{% endif %}
            {% for k,v in category_is[0] %}
            <option {% if categoryID[1] is defined and categoryID[1] == v['id'] %}selected{% endif %} value="{{v['id']}}">{{v['category_name']}}</option>
            {% endfor %}
            {% endif %}
        </select>
        <select class="sku_menu_row1" name="shop_category[]" id="three_category" {% if categoryID[2] is not defined %}style="display: none;"{% endif %} {% if disabled is defined and disabled == 1 %}disabled="disabled"{% endif %}>
            {% if category_is[1] is defined %}
            {% if categoryID[2] == 0 %}<option selected value="0">--请选择--</option>{% endif %}
            {% for k,v in category_is[1] %}
            <option {% if categoryID[2] is defined and categoryID[2] == v['id'] %}selected{% endif %} value="{{v['id']}}">{{v['category_name']}}</option>
            {% endfor %}
            {% endif %}
        </select>
        <span class="category_son">
            {% if category_is[2] is defined %}
            <select name="shop_category[]" class="category_infinite sku_menu_row1" {% if disabled is defined and disabled == 1 %}disabled="disabled"{% endif %}>
                {% for k,v in category_is[2] %}
                <option {% if categoryID[3] is defined and categoryID[3] == v['id'] %}selected{% endif %} value="{{v['id']}}">{{v['category_name']}}</option>
                {% endfor %}
            </select>
            {% endif %}

            {% if category_is[3] is defined %}
            <select name="shop_category[]" class="category_infinite sku_menu_row1">
                {% for k,v in category_is[3] %}
                <option {% if categoryID[4] is defined and categoryID[4] == v['id'] %}selected{% endif %} value="{{v['id']}}">{{v['category_name']}}</option>
                {% endfor %}
            </select>
            {% endif %}
            {% if category_is[4] is defined %}
            <select name="shop_category[]" class="category_infinite sku_menu_row1">
                {% for k,v in category_is[4] %}
                <option {% if categoryID[5] is defined and categoryID[5] == v['id'] %}selected{% endif %} value="{{v['id']}}">{{v['category_name']}}</option>
                {% endfor %}
            </select>
            {% endif %}
        </span>
    </div>
</div>
