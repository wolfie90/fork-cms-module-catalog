{*
  NOTE: To add more levels

	variables that are available:
	- {$widgetCatalogCategoriesFlat}: Flat array of all categories
	- {$widgetCatalogCategoriesTree}: Tree array of all categories
*}



{* TREE VIEW *}
{option:widgetCatalogBrands}
    <h3>{$lblBrands|ucfirst}</h3>
    <ul>
        {iteration:widgetCatalogBrands}
            <li>
                <a href="{$widgetCatalogBrands.full_url}" title="{$widgetCatalogBrands.title}">{$widgetCatalogBrands.title|ucfirst}
                    &nbsp;({$widgetCatalogBrands.total})</a>
            </li>
        {/iteration:widgetCatalogBrands}
    </ul>
{/option:widgetCatalogBrands}