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
		jsFrontend.catalog.onChange();
	},
	
	onChange: function()
	{				
		$addToShoppingCart = $('.addProductToShoppingCart a');
		$editProductAmountInCheckout = $('.editProductAmountInCheckout a');
		$removeFromShoppingCart = $('.removeProductFromShoppingCart a');
		
		// only allow numbers for input field
		$("#inputAmountOfProducts").keypress(function (e) {
			if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
				return false;
			}
		});
		
		// add or update product
		$addToShoppingCart.live('click', function(){
			var $this = $(this);
			
			var $productId = $this.attr('id');
			
			// calculate new product amount, shopping cart widget has to be on page
			// in order to make this work
			var $currentAmountText = $("#shoppingCartTable #productAmountCell-" + $productId).text();
			var $currentAmount =  parseInt($currentAmountText) || 0;
			var $productAmount = $currentAmount + 1;
			
			var $action = 'add-update';
			
			// execute fork action from ajax event
			$.ajax({
				type: 'POST',
				data: {
					fork: { module: 'Catalog', action: 'SaveShoppingCart'},
					productAmount: $productAmount,
					productId: $productId,
					action: $action,
					value: $this.val()
				},
				success: function (data) {
					jsFrontend.catalog.updateShoppingCart();
					jsFrontend.catalog.displayFeedback('success', 'center', jsFrontend.locale.lbl('ProductAdded'));
				},
				error: function (request, status, error) {
					alert(request.responseText);
				}
			});
		});
		
		// add or update product
		$editProductAmountInCheckout.live('click', function(){
			var $this = $(this);
			var $productId = $this.attr('id');
			var $productAmount = $("#inputAmountOfProducts-" + $productId).val();
			
			console.log($productAmount);
			
			var $action = 'add-update';
			
			// execute fork action from ajax event
			$.ajax({
				type: 'POST',
				data: {
					fork: { module: 'Catalog', action: 'SaveShoppingCart'},
					productAmount: $productAmount,
					productId: $productId,
					action: $action,
					value: $this.val()
				},
				success: function (data) {
					jsFrontend.catalog.updateShoppingCart();
					jsFrontend.catalog.updateCheckoutCart();
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
					fork: { module: 'Catalog', action: 'SaveShoppingCart'},
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
		console.log('in functie...');
		
	    $.ajax({
		data: {
		    fork: { module: 'Catalog', action: 'UpdateShoppingCart' }
		},
		success: function (result) {
			console.log('updating shopping cart succesfull...');
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
		    fork: { module: 'Catalog', action: 'UpdateCheckoutCart' }
		},
		success: function (result) {
		    var $target = $('#shoppingCartCheckout');
						
		    if ($target && $target.length) {
				$target.html(result.data);
		    }
		},
		error: function (request, status, error) {
			alert(request.responseText);
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
	}
}

$(jsFrontend.catalog.init);