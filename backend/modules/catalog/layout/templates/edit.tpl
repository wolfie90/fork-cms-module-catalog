{include:{$BACKEND_CORE_PATH}/layout/templates/head.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/structure_start_module.tpl}

<div class="pageTitle">
	<h2>{$lblCatalog|ucfirst}: {$lblEdit}</h2>
</div>

{form:edit}
	<label for="titel">{$lblTitle|ucfirst}</label>
	{$txtTitle} {$txtTitleError}

	<div id="pageUrl">
		<div class="oneLiner">
			{option:detailURL}<p><span><a href="{$detailURL}">{$detailURL}/<span id="generatedUrl"></span></a></span></p>{/option:detailURL}
			{option:!detailURL}<p class="infoMessage">{$errNoModuleLinked}</p>{/option:!detailURL}
		</div>
	</div>


	<div class="tabs">
		<ul>
			<li><a href="#tabContent">{$lblContent|ucfirst}</a></li>
			<li><a href="#tabSEO">{$lblSEO|ucfirst}</a></li>
		</ul>

		<div id="tabContent">
			<table border="0" cellspacing="0" cellpadding="0" width="100%">
				<tr>
					<td id="leftColumn">

						<div class="box">
							<div class="heading">
								<h3>
									<label for="summary">{$lblSummary|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
								</h3>
							</div>
							<div class="optionsRTE">
								{$txtSummary} {$txtSummaryError}
							</div>
						</div>

						<div class="box">
							<div class="heading">
								<h3>
									<label for="content">{$lblContent|ucfirst}</label>
								</h3>
							</div>
							<div class="optionsRTE">
								{$txtText} {$txtTextError}
							</div>
						</div>
						
						<div class="box" id="formElements">
							<div class="heading">
								<h3><label for="content">{$lblSpecifications|ucfirst}</label></h3>
							</div>
							
							<div class="horizontal">
								<div id="specificationsHolder" class="sequenceByDragAndDrop">
									{option:specifications}
										{iteration:specifications}
											{$specifications.specification}
										{/iteration:specifications}
									{/option:specifications}
									
									{* This row always needs to be here. We show/hide it with javascript *}
									<div id="noSpecifications" class="options"{option:specifications} style="display: none;"{/option:specifications}>
										{$lblNoSpecifications}
									</div>	
								</div>
							</div>

					</td>

					<td id="sidebar">

							<div class="box">
								<div class="heading">
									<h3>
										<label for="information">{$lblMetaData|ucfirst}</label>
									</h3>
								</div>
								<div class="options horizontal">
									<label for="price">{$lblPrice|ucfirst}</label>
									{$txtPrice} {$txtPriceError}
								</div>
								<div class="options horizontal">
									<label for="categoryId">{$lblCategory|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
									{$ddmCategoryId} {$ddmCategoryIdError}
								</div>
								<div class="options">
									<label for="tags">{$lblTags|ucfirst}</label>
									{$txtTags} {$txtTagsError}
								</div>
							</div>
							
							<div class="box">
								<div class="heading">
									<h3>
										<label for="relatedProducts">{$lblRelatedProducts|ucfirst}</label>
									</h3>
								</div>
								<div class="options relatedProducts">
									{$ddmRelatedProducts} {$ddmRelatedProductsError}
								</div>
							</div>
							
					</td>
				</tr>
			</table>
		</div>
	
		<div id="tabSEO">
			{include:{$BACKEND_CORE_PATH}/layout/templates/seo.tpl}
		</div>
	</div>

	<div class="fullwidthOptions">
		<a href="{$var|geturl:'delete'}&amp;id={$product.id}" data-message-id="confirmDelete" class="askConfirmation button linkButton icon iconDelete">
			<span>{$lblDelete|ucfirst}</span>
		</a>
		<div class="buttonHolderRight">
			<input id="addButton" class="inputButton button mainButton" type="submit" name="add" value="{$lblSave|ucfirst}" />
		</div>
	</div>

	<div id="confirmDelete" title="{$lblDelete|ucfirst}?" style="display: none;">
		<p>
			{$msgConfirmDelete|sprintf:{$product.title}}
		</p>
	</div>
{/form:edit}

{include:{$BACKEND_CORE_PATH}/layout/templates/structure_end_module.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/footer.tpl}