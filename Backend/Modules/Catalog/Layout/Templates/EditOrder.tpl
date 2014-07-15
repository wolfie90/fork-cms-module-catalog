{include:{$BACKEND_CORE_PATH}/layout/templates/head.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/structure_start_module.tpl}

<!--{$products|dump}-->

<div class="pageTitle">
	<h2>{$lblCatalog|ucfirst}: {$msgEditOrderOn|sprintf:{$orderPerson}}</h2>
</div>

{form:editOrder}
	<div class="box">
		<div class="heading">
			<h3>{$lblOrder|ucfirst}</h3>
		</div>
		<div class="options">
			<p {option:txtEmailError}class="errorArea"{/option:txtEmailError}>
				<label for="email">{$lblEmail|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
				{$txtEmail} {$txtEmailError}
			</p>
			<p>
				<label for="fname">{$lblFirstName|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
				{$txtFname} {$txtFnameError}
			</p>
			<p>
				<label for="lname">{$lblLastName|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
				{$txtLname} {$txtLnameError}
			</p>
			<p>
				<label for="address">{$lblAddress|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
				{$txtAddress} {$txtAddressError}
			</p>
			<p>
				<label for="hnumber">{$lblHouseNumber|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
				{$txtHnumber} {$txtHnumberError}
			</p>
      <p>
				<label for="postal">{$lblPostalCode|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
				{$txtPostal} {$txtPostalError}
			</p>
      <p>
				<label for="hometown">{$lblCity|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
				{$txtHometown} {$txtHometownError}
			</p>			
		</div>
	</div>

	{option:dgProducts}
			<div class="dataGridHolder">
				{$dgProducts}
			</div>
	{/option:dgProducts}
	{option:!dgProducts}{$msgNoOProducts}{/option:!dgProducts}

	<div class="fullwidthOptions">
		<div class="buttonHolderRight">
			<input id="editButton" class="inputButton button mainButton" type="submit" name="edit" value="{$lblSave|ucfirst}" />
		</div>
	</div>

{/form:editOrder}

{include:{$BACKEND_CORE_PATH}/layout/templates/structure_end_module.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/footer.tpl}