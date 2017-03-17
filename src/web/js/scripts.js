if (typeof dvizh == "undefined" || !dvizh) {
    var dvizh = {};
}

dvizh.cart = {
    init: function () {

        $cartElementsCount = '[data-role=cart-element-count]';
        $buyElementButton = '[data-role=cart-buy-button]';
        $deleteElementButton = '[data-role=cart-delete-button]';
        $truncateCartButton = '[data-role=truncate-cart-button]';

        dvizh.cart.csrf = jQuery('meta[name=csrf-token]').attr("content");
        dvizh.cart.csrf_param = jQuery('meta[name=csrf-param]').attr("content");

        $(document).on('change', $cartElementsCount, function () {

            var self = this,
                url = $(self).data('href');

            if ($(self).val() < 0) {
                $(self).val('0');
                return false;
            }

            cartElementId = $(self).data('id');
            cartElementCount = $(self).val();

            dvizh.cart.changeElementCount(cartElementId, cartElementCount, url);

        });

        $(document).on('click', $buyElementButton, function () {

            var self = this,
                url = $(self).data('url'),
                itemModelName = $(self).data('model'),
                itemId = $(self).data('id'),
                itemCount = $(self).data('count'),
                itemPrice = $(self).data('price'),
                itemOptions = $(self).data('options');

            dvizh.cart.addElement(itemModelName, itemId, itemCount, itemPrice, itemOptions, url);

            return false;
        });

        $(document).on('click', $truncateCartButton, function () {

            var self = this,
                url = $(self).data('url');

            dvizh.cart.truncate(url);
            
            return false;
        });

        $(document).on('click', $deleteElementButton, function (e) {

            e.preventDefault();

            var self = this,
                url = $(self).data('url'),
                elementId = $(self).data('id');

            dvizh.cart.deleteElement(elementId, url);

            if (lineSelector = $(self).data('line-selector')) {
                $(self).parents(lineSelector).last().hide('slow');
            }

            return false;
        });
        
        $(document).on('click', '.dvizh-arr', this.changeInputValue);
        $(document).on('change', '.dvizh-cart-element-before-count', this.changeBeforeElementCount);
        $(document).on('change', '.dvizh-option-values-before', this.changeBeforeElementOptions);
        $(document).on('change', '.dvizh-option-values', this.changeElementOptions);

        return true;
    },
    elementsListWidgetParams: [],
    jsonResult: null,
    csrf: null,
    csrf_param: null,
    changeElementOptions: function () {
        jQuery(document).trigger("changeCartElementOptions", this);

        var id = $(this).data('id');

        var options = {};

        if ($(this).is('select')) {
            var els = $('.dvizh-cart-option' + id);
        }
        else {
            var els = $('.dvizh-cart-option' + id + ':checked');
            console.log('radio');
        }

        $(els).each(function () {
            var name = $(this).data('id');

            options[id] = $(this).val();
        });

        var data = {};
        data.CartElement = {};
        data.CartElement.id = id;
        data.CartElement.options = JSON.stringify(options);

        dvizh.cart.sendData(data, jQuery(this).data('href'));

        return false;
    },
    changeBeforeElementOptions: function () {
        var id = $(this).data('id');
        var filter_id = $(this).data('filter-id');
        var buyButton = $('.dvizh-cart-buy-button' + id);

        var options = $(buyButton).data('options');
        if (!options) {
            options = {};
        }

        options[filter_id] = $(this).val();

        $(buyButton).data('options', options);
        $(buyButton).attr('data-options', options);

        $(document).trigger("beforeChangeCartElementOptions", options);

        return true;
    },
    deleteElement: function (elementId, url) {

        dvizh.cart.sendData({elementId: elementId}, url);

        return false;
    },
    changeInputValue: function () {
        var val = parseInt(jQuery(this).siblings('input').val());
        var input = jQuery(this).siblings('input');

        if (jQuery(this).hasClass('dvizh-downArr')) {
            if (val <= 0) {
                return false;
            }
            jQuery(input).val(val - 1);
        }
        else {
            jQuery(input).val(val + 1);
        }

        jQuery(input).change();

        return false;
    },
    changeBeforeElementCount: function () {
        if ($(this).val() <= 0) {
            $(this).val('0');
        }

        var id = $(this).data('id');
        var buyButton = $('.dvizh-cart-buy-button' + id);
        $(buyButton).data('count', $(this).val());
        $(buyButton).attr('data-count', $(this).val());

        return true;
    },
    changeElementCount: function (cartElementId, cartElementCount, url) {

        var data = {};
        data.CartElement = {};
        data.CartElement.id = cartElementId;
        data.CartElement.count = cartElementCount;

        dvizh.cart.sendData(data, url);

        return false;
    },
    addElement: function (itemModelName, itemId, itemCount, itemPrice, itemOptions, url) {

        var data = {};
        data.CartElement = {};
        data.CartElement.model = itemModelName;
        data.CartElement.item_id = itemId;
        data.CartElement.count = itemCount;
        data.CartElement.price = itemPrice;
        data.CartElement.options = itemOptions;

        dvizh.cart.sendData(data, url);

        return false;
    },
    truncate: function (url) {
        dvizh.cart.sendData({}, url);
        return false;
    },
    sendData: function (data, link) {
        if (!link) {
            link = '/cart/element/create';
        }

        jQuery(document).trigger("sendDataToCart", data);

        data.elementsListWidgetParams = dvizh.cart.elementsListWidgetParams;
        data[dvizh.cart.csrf_param] = dvizh.cart.csrf;

        jQuery('.dvizh-cart-block').css({'opacity': '0.3'});
        jQuery('.dvizh-cart-count').css({'opacity': '0.3'});
        jQuery('.dvizh-cart-price').css({'opacity': '0.3'});

        jQuery.post(link, data,
            function (json) {
                jQuery('.dvizh-cart-block').css({'opacity': '1'});
                jQuery('.dvizh-cart-count').css({'opacity': '1'});
                jQuery('.dvizh-cart-price').css({'opacity': '1'});

                if (json.result == 'fail') {
                    console.log(json.error);
                }
                else {
                    dvizh.cart.renderCart(json);
                }

            }, "json");

        return false;
    },
    renderCart: function (json) {
        if (!json) {
            var json = {};
            jQuery.post('/cart/default/info', {},
                function (answer) {
                    json = answer;
                }, "json");
        }

        jQuery('.dvizh-cart-block').replaceWith(json.elementsHTML);
        jQuery('.dvizh-cart-count').html(json.count);
        jQuery('.dvizh-cart-price').html(json.price);

        jQuery(document).trigger("renderCart", json);

        return true;
    },
};

dvizh.cart.init();
