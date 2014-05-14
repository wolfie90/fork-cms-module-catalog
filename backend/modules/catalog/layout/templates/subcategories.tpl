{include:{$BACKEND_CORE_PATH}/layout/templates/head.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/structure_start_module.tpl}

<div class="pageTitle">
	<h2>{$lblCatalog|ucfirst}: {$lblCategoriesAndProducts|ucfirst}</h2>
	<div class="buttonHolderRight">
		<a href="{$var|geturl:'add'}" class="button icon iconAdd" title="{$lblAddProduct|ucfirst}">
			<span>{$lblAddProduct|ucfirst}</span>
		</a>
	</div>
</div>

<div id="dataGridCatalogHolder">
	{option:dataGrids}
		{iteration:dataGrids}
			<div class="dataGridHolder" id="dataGrid-{$dataGrids.id}">
				<div class="tableHeading clearfix">
					<h3>
							{option:dataGrids.url}{$dataGrids.url}{/option:dataGrids.url}
							{option:!dataGrids.url}{$dataGrids.title}{/option:!dataGrids.url}
					</h3>
				</div>
				{option:dataGrids.content}
					{$dataGrids.content}
				{/option:dataGrids.content}

				{option:!dataGrids.content}
					{$emptyDatagrid}
				{/option:!dataGrids.content}
			</div>
		{/iteration:dataGrids}
	{/option:dataGrids}
</div>

{option:!dataGrids}
	<p>{$msgNoItems}</p>
{/option:!dataGrids}

{include:{$BACKEND_CORE_PATH}/layout/templates/structure_end_module.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/footer.tpl}