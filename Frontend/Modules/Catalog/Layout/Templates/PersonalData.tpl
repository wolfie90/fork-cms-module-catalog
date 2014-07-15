{*
	variables that are available:
	- {$checkoutUrl}: url for checkout page
	- {$personalDataForm}: personal data form
*}

<h1 itemprop="name">{$lblPersonalData|ucfirst}</h1>

<section id ="catalogOrderForm">
 {form:personalDataForm}
    <p {option:txtEmailError}class="errorArea"{/option:txtEmailError}>
      <label for="email">{$lblEmail|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
      {$txtEmail} {$txtEmailError}
    </p>
    
    <p {option:txtFnameError}class="errorArea"{/option:txtFnameError}>
      <label for="firstName">{$lblFirstName|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
      {$txtFname} {$txtFnameError}
    </p>

    <p {option:txtLnameError}class="errorArea"{/option:txtLnameError}>
      <label for="lastName">{$lblLastName|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
      {$txtLname} {$txtLnameError}
    </p>

    <p {option:txtAddressError}class="errorArea"{/option:txtAddressError}>
      <label for="address">{$lblAddress|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
      {$txtAddress} {$txtAddressError}
    </p>
    
    <p {option:txtHnumberError}class="errorArea"{/option:txtHnumberError}>
      <label for="addressNumber">{$lblAddressNumber|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
      {$txtHnumber} {$txtHnumberError}
    </p>

    <p {option:txtPostalError}class="errorArea"{/option:txtPostalError}>
      <label for="postalCode">{$lblPostalCode|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
      {$txtPostal} {$txtPostalError}
    </p>
    
    <p {option:txtHometownError}class="errorArea"{/option:txtHometownError}>
      <label for="city">{$lblCity|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
      {$txtHometown} {$txtHometownError}
    </p>
    
    <p>
      <input class="inputSubmit filled" type="submit" name="order" value="{$lblSubmitOrder|ucfirst}" />
    </p>
 {/form:personalDataForm}
</section>

<label><a href="{$checkoutUrl}">{$lblGoToShoppingCartOverview|ucfirst}</a></label>      

