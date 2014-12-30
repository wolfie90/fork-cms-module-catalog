{include:{$BACKEND_CORE_PATH}/Layout/Templates/Head.tpl}
{include:{$BACKEND_CORE_PATH}/Layout/Templates/StructureStartModule.tpl}

<div class="pageTitle">
	<h2>{$lblCatalog|ucfirst}: {$lblAddProduct|ucfirst}</h2>
</div>

{form:add}
	<label for="title">{$lblTitle|ucfirst}</label>
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
			<li><a href="#tabComments">{$lblComments|ucfirst}</a></li>
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
								<h3><label for="productSpecifications">{$lblSpecifications|ucfirst}</label></h3>
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
										{$msgNoSpecifications}
									</div>	
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
							<div class="options">
								<label for="categoryId">{$lblCategory|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
								{$ddmCategoryId} {$ddmCategoryIdError}
							</div>
							<div class="options">
								<label for="tags">{$lblTags|ucfirst}</label>
								{$txtTags} {$txtTagsError}
							</div>
                            <div class="options">
                                <label for="brandId">{$lblBrand|ucfirst}</label>
                                {$ddmBrandId} {$ddmBrandIdError}
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

		<div id="tabPermissions">
			<table width="100%">
				<tr>
					<td>
						{$chkAllowComments} <label for="allowComments">{$lblAllowComments|ucfirst}</label>
					</td>
				</tr>
			</table>
		</div>

		<div id="tabSEO">
			{include:{$BACKEND_CORE_PATH}/Layout/Templates/Seo.tpl}
		</div>
	</div>

	<div class="fullwidthOptions">
		<div class="buttonHolderRight">
			<input id="addButton" class="inputButton button mainButton" type="submit" name="add" value="{$lblPublish|ucfirst}" />
		</div>
	</div>
	
	{* Dialog for a textbox *}
	<div id="textboxDialog" title="{$lblAddSpecification|ucfirst}" class="dialog" style="display: none;">
		<input type="hidden" name="textbox_id" id="textboxId" value="" />
			<div class="horizontal">
				<div class="options">
					<p>
						<label for="textboxLabel">{$lblTitle|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
						{$txtTextboxLabel}
						<span id="textboxLabelError" class="formError" style="display: none;"></span>
					</p>
				</div>
			</div>
		</div>
	</div>
{/form:add}

{include:{$BACKEND_CORE_PATH}/Layout/Templates/StructureEndModule.tpl}
{include:{$BACKEND_CORE_PATH}/Layout/Templates/Footer.tpl}