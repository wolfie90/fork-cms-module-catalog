{*
	variables that are available:
	- {$item}: contains data about the product
	- {$images}: item images
	- {$videos}: item videos
	- {$files}: item files
	- {$specifications}: item specifications
	- {$related}: related items
	- {$comments}: item comments
	- {$commentsCount}: amount of comments
	- {$commentsMultiple}: multiple comments (true/false)
	- {$commentIsInModeration}: feedback when form submit (true/false)
	- {$commentIsSpam}: feedback when form submit (true/false)
	- {$commentIsAdded}: feedback when form submit (true/false)
	- {$commentsForm}: add comment form
*}

{*$item|dump*}

{option:item}
{* Product information: see dump for additional info *}
<div id="productContainer">
	<div class="title"><h1>{$item.title}</h1></div>
	<div class="price"><p><b>{$lblPrice|ucfirst}:</b> {$item.price|formatcurrency}</p></div>
	<div class="category"><p><b>{$lblCategory|ucfirst}:</b> <a href="{$item.category_full_url}">{$item.category_title}</a></p></div>
	<div class="brand"><p><b>{$lblBrand|ucfirst}:</b> <a href="{$item.brand.full_url}">{$item.brand.title}</a></p></div>

	<!--<div class="summary">{$item.summary}</div>-->
	<div class="content">
		<p><b>{$lblContent|ucfirst}:</b></p>
		<p>{$item.text}</p>
	</div>
	
	{* Product images *}
	{option:images}
		<div class="images">
			<p><b>{$lblImages|ucfirst}:</b></p>
			<ul>
				{iteration:images}
				<li><img src="{$images.image}" alt="{$images.title}" title="{$images.title}" /></li>
				{/iteration:images}
			</ul>
		</div>
	{/option:images}
	
	{* Product videos *}
	{option:videos}
	<div class="videos">
		{iteration:videos}
			<p><b>{$lblVideos|ucfirst}:</b></p>
			<a class="fancybox fancybox.iframe" rel="gallery" href="{$videos.url}">
				<img src="{$videos.image}" alt="{$videos.title}" title="{$videos.title}">
			</a>
		{/iteration:videos}
	</div>
	{/option:videos}
	
	{* Product files *}
	{option:files}
	<div class="videos">
		<p><b>{$lblFiles|ucfirst}:</b></p>
		<ul>
		{iteration:files}
			<li><a href="{$files.url}">{$files.title}</a></li>
		{/iteration:files}
		</ul>
	</div>
	{/option:files}
	
	<div class="addProductToShoppingCart">
		<p><a href="#" id="{$item.id}">{$lblAddProductToShoppingCart|ucfirst}</a></p>
	</div>
	
	{* Product specifications *}
	{option:specifications}
		<hr>
		<h2>{$lblSpecifications|ucfirst}</h2>
		<div class="specifications">
			{iteration:specifications}
				<p><b>{$specifications.title}:</b> {$specifications.value}</p>
			{/iteration:specifications}
		</div>
	{/option:specifications}

	
	{* Related products *}
	{option:related}
		<hr>
		<h2>{$lblRelatedProducts|ucfirst}</h2>
		<div class="relatedProducts">
			{iteration:related}
			<div class="relatedProduct">
				<div class="title"><h3>{$related.title}</h3></div>
				<div class="price"><p><b>{$lblPrice|ucfirst}:</b> {$related.price|formatcurrency}</p></div>
				<div class="category"><p><b>{$lblCategory|ucfirst}:</b> <a href="{$item.category_full_url}">{$item.category_title}</a></p></div>
				<div class="addProductToShoppingCart">
					<p><a href="#" id="{$related.id}">{$lblAddProductToShoppingCart}</a></p>
				</div>
			</div>
			{/iteration:related}
		</div>
		<div class="clearfix"></div>
	{/option:related}
	
	{* Product comments *}
	{option:item.allow_comments}
		<hr>
		<h2>{$lblComments|ucfirst}</h2>
		<div class="comments">
		{option:comments}
			{iteration:comments}				
				<p>
					<b>{$comments.author}</b>
					{option:comments.website}(<a href="{$comments.website}">{$comments.website}</a>){/option:comments.website}
					- {$comments.created_on|timeago}
				</p>
				<p>{$comments.text|ucfirst}</p>
			<hr>
			{/iteration:comments}
		{/option:comments}
		{option:!comments} <a href="{$item.full_url}#{$actComment}" itemprop="discussionUrl">{$msgCatalogNoComments}</a>{/option:!comments}
		</div>
		
		<div class="form">
			{option:commentIsInModeration}<div class="message warning"><p>{$msgCatalogCommentInModeration}</p></div>{/option:commentIsInModeration}
			{option:commentIsSpam}<div class="message error"><p>{$msgCatalogCommentIsSpam}</p></div>{/option:commentIsSpam}
			{option:commentIsAdded}<div class="message success"><p>{$msgCatalogCommentIsAdded}</p></div>{/option:commentIsAdded}
			{form:commentsForm}
				<p {option:txtAuthorError}class="errorArea"{/option:txtAuthorError}>
					<label for="author">{$lblName|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
					{$txtAuthor} {$txtAuthorError}
				</p>
				
				<p {option:txtEmailError}class="errorArea"{/option:txtEmailError}>
					<label for="email">{$lblEmail|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
					{$txtEmail} {$txtEmailError}
				</p>
				
				<p class="bigInput{option:txtWebsiteError} errorArea{/option:txtWebsiteError}">
					<label for="website">{$lblWebsite|ucfirst}</label>
					{$txtWebsite} {$txtWebsiteError}
				</p>
	
				<p class="bigInput{option:txtMessageError} errorArea{/option:txtMessageError}">
					<label for="message">{$lblMessage|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
					{$txtMessage} {$txtMessageError}
				</p>
	
				<p>
					<input class="inputSubmit" type="submit" name="comment" value="{$msgComment|ucfirst}" />
				</p>
			{/form:commentsForm}
		</div>
		<hr>
	{/option:item.allow_comments}
	
	{* Back to overview *}
	<p><a href="{$var|geturlforblock:'catalog'}" title="{$msgToCatalogOverview|ucfirst}">{$msgToCatalogOverview|ucfirst}</a></p>
	
{/option:item}
</div>