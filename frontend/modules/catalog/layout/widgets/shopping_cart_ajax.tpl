{*
	variables that are available:
	- {$productsInShoppingCart}: contains data about the products
	- {$totalPrice}: total price of products
	- {$overviewUrl}: url to catalog page
*}

{option:cookiesEnabled}
<div class="data productsInShoppingCart">
		
		{option:productsInShoppingCart}
				<table>
						<tr>
								<th>{$lblProduct|ucfirst}</th>
								<th>{$lblAmount|ucfirst}</th>
								<th>{$lblTotal|ucfirst}</th>
								<th></th>
						</tr>
						{iteration:productsInShoppingCart}
						<tr>
								<td><a href="{$productsInShoppingCart.full_url}">{$productsInShoppingCart.title}</a></td>
								<td>{$productsInShoppingCart.amount}</td>
								<td>{$productsInShoppingCart.subtotal_price|formatcurrency}</td>
		<td data-title="{$lblDelete|ucfirst}">
		    <div class="removeProductFromShoppingCart"><a href="#" id="{$productsInShoppingCart.product_id}">{$lblDelete|ucfirst}</a></div>
		</td>
						</tr>
						{/iteration:productsInShoppingCart}
						<tr>
								<td></td>
								<td>{$lblTotal|ucfirst}</td>
								<td>{$totalPrice|formatcurrency}</td>
						</tr>
				</table>
		{/option:productsInShoppingCart}
		
		{option:!productsInShoppingCart}
				<p>{$msgNoProductsInShoppingCart}</p>
		{/option:!productsInShoppingCart}
</div>

<label><a href="{$overviewUrl}">{$lblGoToShoppingCartOverview|ucfirst}</a></label>
{/option:cookiesEnabled}

{option:!cookiesEnabled}
<div class="cookies">
		<p>{$msgEnableCookies}.</p>
</div>
{/option:!cookiesEnabled}

