{include:{$BACKEND_CORE_PATH}/layout/templates/head.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/structure_start_module.tpl}

<div class="pageTitle">
	<h2>{$lblProducts|ucfirst}: {$lblAddImage}</h2>
</div>

{form:addImage}
	<div class="tabs">
		<ul>
			<li><a href="#tabContent">{$lblContent|ucfirst}</a></li>
		</ul>

		<div id="tabContent">
			<table border="0" cellspacing="0" cellpadding="0" width="100%">
				<tr>
					<td id="leftColumn">
						<div class="box">
							<div class="heading">
								<h3>{$lblTitle|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></h3>
							</div>
							<div class="options">
								{$txtTitle} {$txtTitleError}
							</div>
						</div>

						<div class="box">
							<div class="heading">
								<h3>{$lblImage|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></h3>
							</div>
							<div class="options">
								<label for="image">{$lblImage|ucfirst}</label>
								{$fileImage} {$fileImageError}
							</div>
						</div>
					</td>
				</tr>
			</table>
		</div>
	</div>

	<div class="fullwidthOptions">
		{option:showProjectsImages}
		<a href="{$var|geturl:'media'}&product_id={$product.id}" class="button">
			<span>{$lblBackToOverview|ucfirst}</span>
		</a>
		{/option:showProjectsImages}

		<div class="buttonHolderRight">
			<input id="addButton" class="inputButton button mainButton" type="submit" title="edit" value="{$lblSave|ucfirst}" />
		</div>
	</div>
{/form:addImage}

{include:{$BACKEND_CORE_PATH}/layout/templates/structure_end_module.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/footer.tpl}