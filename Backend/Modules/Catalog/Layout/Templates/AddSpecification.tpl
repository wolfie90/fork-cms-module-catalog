{include:{$BACKEND_CORE_PATH}/Layout/Templates/Head.tpl}
{include:{$BACKEND_CORE_PATH}/Layout/Templates/StructureStartModule.tpl}

<div class="pageTitle">
	<h2>{$lblCatalog|ucfirst}: {$lblAddSpecification|ucfirst}</h2>
</div>

{form:addSpecification}
  <div class="box">
    <div class="heading">
      <h3><label for="title">{$lblTitle|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label></h3>
    </div>
    <div class="options">
      {$txtTitle} {$txtTitleError}
    </div>
  </div>

	<div class="fullwidthOptions">
		<div class="buttonHolderRight">
			<input id="addButton" class="inputButton button mainButton" type="submit" name="add" value="{$lblAddSpecification|ucfirst}" />
		</div>
	</div>
{/form:addSpecification}

{include:{$BACKEND_CORE_PATH}/Layout/Templates/StructureEndModule.tpl}
{include:{$BACKEND_CORE_PATH}/Layout/Templates/Footer.tpl}