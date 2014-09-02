<h1>{$lblBrand|ucfirst}: {$title}</h1>

{option:products}
    <div class="productsContainer">
        {iteration:products}
            <div class="title"><p><a href="{$products.full_url}" title="{$products.title}">{$products.title|ucfirst}</a></p></div>
            <div class="price"><p>{$lblPrice|ucfirst}: {$products.price|formatcurrency}</p></div>
            <div class="summary"><p>{$products.summary}</p></div>
            <div class="addProductToShoppingCart">
                <p><a href="#" id="{$products.id}">{$lblAddProductToShoppingCart|ucfirst}</a></p>
            </div>
            <hr>
        {/iteration:products}
    </div>
{/option:products}