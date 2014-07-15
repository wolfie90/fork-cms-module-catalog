{include:{$BACKEND_CORE_PATH}/Layout/Templates/Head.tpl}
{include:{$BACKEND_CORE_PATH}/Layout/Templates/StructureStartModule.tpl}

<div class="pageTitle">
	<h2>{$lblCatalog|ucfirst}: {$lblAddCategory}</h2>
</div>

{form:addCategory}
	<div class="tabs">
		<ul>
			<li><a href="#tabContent">{$lblContent|ucfirst}</a></li>
			<li><a href="#tabSEO">{$lblSEO|ucfirst}</a></li>
		</ul>

		<div id="tabContent">
			<div class="box">
				<div class="heading">
					<h3><label for="title">{$lblTitle|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label></h3>
				</div>
				<div class="options">
					{$txtTitle} {$txtTitleError}
				</div>
				<div class="options">
					<label for="image">{$lblImage|ucfirst}</label>
					{$fileImage} {$fileImageError}
				</div>
				<div class="options">
					<label for="category">{$lblInCategory|ucfirst}</label>
					{$ddmParentId} {$ddmParentIdError}
				</div>
			</div>
		</div>

		<div id="tabSEO">
			{include:{$BACKEND_CORE_PATH}/Layout/Templates/Seo.tpl}
		</div>
	</div>

	<div class="fullwidthOptions">
		<div class="buttonHolderRight">
			<input id="addButton" class="inputButton button mainButton" type="submit" name="add" value="{$lblAddCategory|ucfirst}" />
		</div>
	</div>
{/form:addCategory}

{include:{$BACKEND_CORE_PATH}/Layout/Templates/StructureEndModule.tpl}
{include:{$BACKEND_CORE_PATH}/Layout/Templates/Footer.tpl}