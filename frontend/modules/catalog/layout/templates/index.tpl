{*	
	variables that are available:
	- {$categoriesFlat}: contains a flat array of all categories
	- {$categoriesTree}: multidimensional array of categories/subcategories
	- {$categoriesHTML} : contains the categories in html list
	- {$products}: contains all products
*}

{option:!categoriesHTML}
 {$msgNoCategories|ucfirst}
{/option:!categoriesHTML}

{option:categoriesHTML}
 <h3>{$lblCategories|ucfirst}</h3>
 <div class="bd content">
	{$categoriesHTML}
 </div>
{/option:categoriesHTML}