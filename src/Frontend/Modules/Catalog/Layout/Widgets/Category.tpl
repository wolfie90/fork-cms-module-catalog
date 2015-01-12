{option:category}
    <h3>
        <a href="{$category.full_url}" title="{$category.title}">
            {$category.title}
        </a>
    </h3>
{/option:category}
{option:products}
    <ul>
        {iteration:products}
            <li>
                <a href="{$products.full_url}" class="{$products.title}">
                    {$products.title}
                </a>
            </li>
        {/iteration:products}
    </ul>
{/option:products}