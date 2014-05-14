{include:{$BACKEND_CORE_PATH}/layout/templates/head.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/structure_start_module.tpl}

<div class="pageTitle">
	<h2>
		{$lblCatalog|ucfirst}:

		{option:!filterCategory}{$lblProducts}{/option:!filterCategory}
		{option:filterCategory}{$msgProductsFor|sprintf:{$filterCategory.title}}{/option:filterCategory}
	</h2>

	<div class="buttonHolderRight">
		{option:filterCategory}<a href="{$var|geturl:'add':null:'&category={$filterCategory.id}'}" class="button icon iconAdd" title="{$lblAdd|ucfirst}">{/option:filterCategory}
		{option:!filterCategory}<a href="{$var|geturl:'add'}" class="button icon iconAdd" title="{$lblAdd|ucfirst}">{/option:!filterCategory}
			<span>{$lblAddProduct|ucfirst}</span>
		</a>
	</div>
</div>

{form:filter}
	<p class="oneLiner">
		<label for="category">{$msgShowOnlyProductsInCategory}:</label>
		&nbsp;{$ddmCategory} {$ddmCategoryError}
	</p>
{/form:filter}

{option:dgProducts}
	<div class="dataGridHolder">
		<div class="tableHeading">
			<h3>{$lblProducts|ucfirst}</h3>
		</div>
		{$dgProducts}
	</div>
{/option:dgProducts}

{option:!dgProducts}
	{option:filterCategory}<p>{$msgNoProducts|sprintf:{$var|geturl:'add':null:'&category={$filterCategory.id}'}}</p>{/option:filterCategory}
	{option:!filterCategory}<p>{$msgNoProducts|sprintf:{$var|geturl:'add'}}</p>{/option:!filterCategory}
{/option:!dgProducts}

{include:{$BACKEND_CORE_PATH}/layout/templates/structure_end_module.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/footer.tpl}