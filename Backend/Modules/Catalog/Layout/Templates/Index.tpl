{include:{$BACKEND_CORE_PATH}/Layout/Templates/Head.tpl}
{include:{$BACKEND_CORE_PATH}/Layout/Templates/StructureStartModule.tpl}

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

{include:{$BACKEND_CORE_PATH}/Layout/Templates/StructureEndModule.tpl}
{include:{$BACKEND_CORE_PATH}/Layout/Templates/Footer.tpl}