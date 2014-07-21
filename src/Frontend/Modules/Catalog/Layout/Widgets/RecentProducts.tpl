{*
	variables that are available:
	- {$widgetCatalogRecentProducts}: contains an array with all products, each element contains data about the product
*}

{option:widgetCatalogRecentProducts}
	<section id="blogRecentProductsWidget" class="mod">
		<div class="inner">
			<header class="hd">
				<h3>{$lblRecentProducts|ucfirst}</h3>
			</header>
			<div class="bd content">
				<ul>
					{iteration:widgetCatalogRecentProducts}
						<li><a href="{$widgetCatalogRecentProducts.full_url}" title="{$widgetCatalogRecentProducts.title}">{$widgetCatalogRecentProducts.title}</a></li>
					{/iteration:widgetCatalogRecentProducts}
				</ul>
			</div>
		</div>
	</section>
{/option:widgetCatalogRecentProducts}