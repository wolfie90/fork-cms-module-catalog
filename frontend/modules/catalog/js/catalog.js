/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * Interaction for the Catalog module
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
jsFrontend.catalog =
{
	// constructor
	init: function()
	{
		//jsFrontend.catalog.areCookiesEnabled();
		jsFrontend.catalog.onChange();
	},
	
	onChange: function()
	{				
		$addToShoppingCart = $('.addProductToShoppingCart a');
		$removeFromShoppingCart = $('.removeProductFromShoppingCart a');
		
		// add or update product
		$addToShoppingCart.live('click', function(){
			var $this = $(this);
			
			var $productId = $this.attr('id');
			var $productAmount = $( "#productAmount" + $productId ).val();
			var $action = 'add-update';
			
			// execute fork action from ajax event
			$.ajax({
				type: 'POST',
				data: {
					fork: { module: 'catalog', action: 'save_shopping_cart'},
					productAmount: $productAmount,
					productId: $productId,
					action: $action,
					value: $this.val()
				},
				success: function (data) {
					jsFrontend.catalog.updateShoppingCart();
					jsFrontend.catalog.displayFeedback('success', 'center', jsFrontend.locale.lbl('ProductAdded'));
				}
			});
		});
		jsFrontend.catalog.updateShoppingCart();
		
		// delete product
		$removeFromShoppingCart.live('click', function(){
			var $this = $(this);
						
			var $productId = $this.attr('id');
			var $action = 'delete';
			
			// execute fork action from ajax event
			$.ajax({
				type: 'POST',
				data: {
					fork: { module: 'catalog', action: 'save_shopping_cart'},
					productId: $productId,
					action: $action,
					value: $this.val()
				},
				success: function (data) {
					jsFrontend.catalog.updateShoppingCart();
					jsFrontend.catalog.updateCheckoutCart();
					jsFrontend.catalog.displayFeedback('success', 'center', jsFrontend.locale.lbl('ProductRemoved'));
				}
			});
		});
	},

	updateShoppingCart: function()
	{
	    $.ajax({
		data: {
		    fork: { module: 'catalog', action: 'update_shopping_cart' }
		},
		success: function (result) {
		    var $target = $('#shoppingCartWidget');
						
		    if ($target && $target.length) {
				$target.html(result.data);
		    }
		}
	    });
	},
	
	updateCheckoutCart: function()
	{
	    $.ajax({
		data: {
		    fork: { module: 'catalog', action: 'update_checkout_cart' }
		},
		success: function (result) {
		    var $target = $('#shoppingCartCheckout');
						
		    if ($target && $target.length) {
				$target.html(result.data);
		    }
		}
	    });
	},
	
	displayFeedback: function(type, layout, message)
	{
		var n = noty({
		    text        : message,
		    type        : type,
		    dismissQueue: true,
		    layout      : layout,
		    theme       : 'defaultTheme'
		});
	},
	
	areCookiesEnabled: function()
	{
		$cookieEnabled = navigator.cookieEnabled;
		
		if ($cookieEnabled == true) {
			console.log('true');
		} else {
			console.log('false');
		}
		
	}

}

$(jsFrontend.catalog.init);


