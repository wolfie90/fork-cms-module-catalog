{include:{$BACKEND_CORE_PATH}/layout/templates/head.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/structure_start_module.tpl}

{form:editSpecification}
	<div class="box">
		<div class="heading">
			<h3>{$lblCatalog|ucfirst}: {$msgEditSpecification|ucfirst}: {$item.title}</h3>
		</div>
		<div class="options horizontal">

			<p>
				<label for="name">{$lblTitle|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
				{$txtTitle} {$txtTitleError}
			</p>

		</div>
	</div>

	<div class="fullwidthOptions">
		<a href="{$var|geturl:'delete_specification'}&amp;id={$item.id}" data-message-id="confirmDelete" class="askConfirmation button linkButton icon iconDelete">
			<span>{$lblDelete|ucfirst}</span>
		</a>
		<div id="confirmDelete" title="{$lblDelete|ucfirst}?" style="display: none;">
			<p>
				{$msgConfirmDeleteSpecification|sprintf:{$item.title}}
			</p>
		</div>

		<div class="buttonHolderRight">
			<input id="editButton" class="inputButton button mainButton" type="submit" name="edit" value="{$lblSave|ucfirst}" />
		</div>
	</div>
{/form:editSpecification}

{include:{$BACKEND_CORE_PATH}/layout/templates/structure_end_module.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/footer.tpl}