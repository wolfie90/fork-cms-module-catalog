{*
  variables that are available:
  - {$productsInShoppingCart}: contains data about the products
  - {$totalPrice}: total price of products
  - {$personalDataUrl}: url to personal data page
  - {$catalogUrl}: url to catalog page
*}

{option:productsInShoppingCart}
{* classnames are important for javascript actions *}
<div class="productsInShoppingCart">
    <table>
        <tbody>
            <tr>
                <th>{$lblProduct|ucfirst}</th>
                <th>{$lblAmount|ucfirst}</th>
                <th>{$lblPrice|ucfirst}</th>
                <th>{$lblTotal|ucfirst}</th>
                <th></th>
            </tr>
            {iteration:productsInShoppingCart}
            <tr>
                <td><a href="{$productsInShoppingCart.full_url}">{$productsInShoppingCart.title}</a></td>
                <td>
                    <div class="editProductAmountInCheckout">
                        <input type="text" name="amountOfProducts" id="inputAmountOfProducts-{$productsInShoppingCart.product_id}" value="{$productsInShoppingCart.amount}">
                        <p><a href="#" id="{$productsInShoppingCart.product_id}">{$lblRecalculatePrice|ucfirst}</a></p>
                    </div>
                </td>
                <td>{$productsInShoppingCart.price|formatcurrency}</td>
                <td>{$productsInShoppingCart.subtotal_price|formatcurrency}</td>
                <td>
                    <div class="removeProductFromShoppingCart"><a href="#" id="{$productsInShoppingCart.product_id}">{$lblDelete|ucfirst}</a></div>
                </td>
            </tr>
            {/iteration:productsInShoppingCart}
            <tr>
                <td></td>
                <td></td>
                <td>{$lblTotal|ucfirst}</td>
                <td>{$totalPrice|formatcurrency}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
</div>
{/option:productsInShoppingCart}

{option:!productsInShoppingCart}
    <p>{$msgNoProductsInShoppingCart|ucfirst}</p>
{/option:!productsInShoppingCart}

{option:productsInShoppingCart}
    <p><a href="{$personalDataUrl}">{$lblGoToPersonalData|ucfirst}</a></p>
{/option:productsInShoppingCart}

<p><a href="{$catalogUrl}">{$lblContinueShopping|ucfirst}</a></p>
