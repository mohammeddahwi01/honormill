define([
    'jquery',
    'MGS_AjaxCart/js/config',
    'MGS_AjaxCart/js/action',
], function($, modal) {
    "use strict";
    jQuery.widget('mgs.catalogAddToCart', jQuery.mgs.action, {
        options: {
            bindSubmit: true,
            redirectToCatalog: false
        },
        _create: function() {
            if (this.options.bindSubmit) {
                this._super();
                this._on({
                    'submit': function(event) {
                        event.preventDefault();

						var data = this.element.serializeArray();
						data.push({
							name: 'action_url',
							value: this.element.attr('action')
						});
                        if(this.element.attr('class') == "product-view-validate") {
                            if (this.swatchValidator()) {
                                this.fire(this.element,this.getActionId(), this.element.attr('action'), data, this.options.redirectToCatalog);
                            }
                        } else {
                            this.fire(this.element,this.getActionId(), this.element.attr('action'), data, this.options.redirectToCatalog);
                        }
                    }
                });
            }
            
        },
        getActionId: function() {
            return 'catalog-add-to-cart-' + jQuery.now()
        },
        swatchValidator: function() {
            if(this.isAnySwatchSelected()) {
                return true;
            }
            else if(this.ownMaterialOptionChecked()) {
                return true;
            }
            else if($('.catalog-product-view .product-options-wrapper > .fieldset > .field.active.last > .control').length) {
                this.swatchRequired();
            }
            else if(this.ownMaterialOptionExist()) {
                this.ownMaterialOptionRequired();
            }
            else {
                return true;    //THERE ARE NO CUSTOM OPTIONS
            }
            return false;
        },
        ownMaterialOptionExist: function() {
            return $('.catalog-product-view .product-options-wrapper > .fieldset > .field.ownMaterialOptionExist').length;
        },
        ownMaterialOptionChecked: function() {
            if($('.catalog-product-view .product-options-wrapper > .fieldset > .field.ownMaterialOptionExist input').length) {
                return $('.catalog-product-view .product-options-wrapper > .fieldset > .field.ownMaterialOptionExist input').prop('checked');
            }
            return false;
        },
        isAnySwatchSelected: function() {
            var flag = false;
            $('.catalog-product-view .product-options-wrapper > .fieldset > .field select').each(function() {
                if($(this).val()) {
                    flag = true;
                }
            });
            return flag;
        },
        swatchRequired: function() {
            $('.catalog-product-view .product-options-wrapper > .fieldset').after('<div class="mage-error" generated="true" id="swatch-required">Please select at least one option.</div>');
            $('.catalog-product-view .product-options-wrapper .fieldset > .field').css('border','1px dotted #ff0000');
            setTimeout(function() {
                $('.catalog-product-view .product-options-wrapper > #swatch-required').fadeOut('slow').remove();
                $('.catalog-product-view .product-options-wrapper .fieldset > .field').css('border','0px solid #ccc');
            },
            5000);
        },
        ownMaterialOptionRequired: function() {
            $('.catalog-product-view .product-options-wrapper > .fieldset > .field.active.ownMaterialOptionExist').after('<div class="mage-error" generated="true" id="option-required">This is a required option.</div>');
            $('.catalog-product-view .product-options-wrapper .fieldset > .field.active.ownMaterialOptionExist').css('border','1px dotted #ff0000');
            setTimeout(function() {
                $('.catalog-product-view .product-options-wrapper > .fieldset #option-required').fadeOut('slow').remove();
                $('.catalog-product-view .product-options-wrapper .fieldset > .field.active.ownMaterialOptionExist').css('border','0px solid #ccc');
            },
            5000);
        }
    });

    return jQuery.mgs.catalogAddToCart;
});
