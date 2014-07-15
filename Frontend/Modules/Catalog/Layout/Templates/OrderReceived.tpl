{*
	variables that are available:
	- {$firstName}: first name of person that ordered
	- {$catalogUrl}: url to index page
*}

<h1>{$lblThanks|ucfirst}, {$firstName}!</h1>
<a href="{$catalogUrl}">{$msgToCatalogOverview}</a>
