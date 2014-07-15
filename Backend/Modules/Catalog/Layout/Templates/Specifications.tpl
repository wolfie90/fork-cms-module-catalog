{include:{$BACKEND_CORE_PATH}/Layout/Templates/Head.tpl}
{include:{$BACKEND_CORE_PATH}/Layout/Templates/StructureStartModule.tpl}

<div class="pageTitle">
	<h2>{$lblCatalog|ucfirst}: {$lblSpecifications|ucfirst}</h2>

	<div class="buttonHolderRight">
		<a href="{$var|geturl:'add_specification'}" class="button icon iconAdd"><span>{$lblAddSpecification|ucfirst}</span></a>
	</div>
</div>

{option:dataGrid}
	<div class="dataGridHolder">
		{$dataGrid}
	</div>
{/option:dataGrid}

{option:!dataGrid}
	{$msgNoSpecifications|ucfirst}
{/option:!dataGrid}

{include:{$BACKEND_CORE_PATH}/Layout/Templates/StructureEndModule.tpl}
{include:{$BACKEND_CORE_PATH}/Layout/Templates/Footer.tpl}