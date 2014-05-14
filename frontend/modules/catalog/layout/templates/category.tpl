{*
	variables that are available:
	- {$record}: contains data about the product
	- {$categories}: contains the subcategories which have products in it
	- {$products}: item products
	- {$subcategoriesFlat}: contains a flat array of all categories
	- {$subcategoriesTree}: tree view of categories/subcategories
	- {$subcategoriesHTML} : contains the subcategories in html list
*}

<h1>{$lblCategory|ucfirst}: {$title}</h1>

{option:products}
<div class="productsContainer">
	{iteration:products}
		<div class="title"><p><a href="{$products.full_url}" title="{$products.title}">{$products.title|ucfirst}</a></p></div>
		<div class="price"><p>{$lblPrice|ucfirst}: {$products.price|formatcurrency}</p></div>
		<div class="summary"><p>{$products.summary}</p></div>
		<div class="addProductToShoppingCart">
		<p>{$lblAmount|ucfirst}:
			<select id="productAmount{$products.id}">
			   <option value="0">0</option>
			   <option value="1">1</option>
			   <option value="2">2</option>
			   <option value="3">3</option>
			   <option value="4">4</option>
			   <option value="5">5</option>
			   <option value="6">6</option>
			   <option value="7">7</option>
			   <option value="8">8</option>
			   <option value="9">9</option>
			   <option value="10">10</option>
			</select>
			<a href="#" id="{$products.id}">{$lblAddProductToShoppingCart|ucfirst}</a>
		</p>
		</div>
		<hr>
	{/iteration:products}
</div>
{/option:products}

{option:subcategoriesHTML}
 <h3>{$lblSubCategories|ucfirst}</h3>	 
 <div class="bd content">
	{$subcategoriesHTML}
 </div>
{/option:subcategoriesHTML}

{*FLAT VIEW OF SUBCATEGORIES 
{option:subcategories}
<div class="categoriesContainer">
   <h3>{$lblSubCategories|ucfirst}</h3>	 
   {iteration:subcategories}
		<a href="{$subcategories.full_url}" title="{$subcategories.title}">{$subcategories.title|ucfirst}</a>
   {/iteration:subcategories} 
</div>
{/option:subcategories}
*}

{* FLAT VIEW *
{option:subcategoriesFlat}
   <h3>{$lblSubCategories|ucfirst}</h3>	 
 <div class="bd content">
 {iteration:subcategoriesFlat}
 	<a href="{$subcategoriesFlat.full_url}" title="{$subcategoriesFlat.title}">{$subcategoriesFlat.title|ucfirst}</a> 
 {/iteration:subcategoriesFlat}
 </div>
{/option:subcategoriesFlat}
*}

{* TREE VIEW 
{option:subcategoriesTree}
  <h3>{$lblSubCategories|ucfirst}</h3>	 
 <div class="bd content">
 <ul>
 {iteration:subcategoriesTree}
  <li><a href="{$subcategoriesTree.full_url}" title="{$subcategoriesTree.title}">{$subcategoriesTree.title|ucfirst}&nbsp;({$subcategoriesTree.total})</a>
  
  {option:subcategoriesTree.children}
   <ul>
   {iteration:subcategoriesTree.children}
   <li><a href="{$subcategoriesTree.children.full_url}" title="{$subcategoriesTree.children.title}">{$subcategoriesTree.children.title|ucfirst}&nbsp;({$subcategoriesTree.children.total})</a>
   
    {option:subcategoriesTree.children.children}
     <ul>
     {iteration:subcategoriesTree.children.children}
      <li><a href="{$subcategoriesTree.children.children.full_url}" title="{$subcategoriesTree.children.children.title}">{$subcategoriesTree.children.children.title|ucfirst}&nbsp;({$subcategoriesTree.children.children.total})</a>
     {/iteration:subcategoriesTree.children.children}
     </li></ul>
    {/option:subcategoriesTree.children.children}
   
   {/iteration:subcategoriesTree.children}
   </li></ul>
  {/option:subcategoriesTree.children}
  
 {/iteration:subcategoriesTree}
  </li></ul>
 </div>
{/option:subcategoriesTree}
*}
