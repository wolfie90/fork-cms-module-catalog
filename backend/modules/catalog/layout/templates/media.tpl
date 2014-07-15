{include:{$BACKEND_CORE_PATH}/Layout/Templates/Head.tpl}
{include:{$BACKEND_CORE_PATH}/Layout/Templates/StructureStartModule.tpl}

<div class="pageTitle">
	<h2>{$product.title}: {$lblMedia|ucfirst}</h2>
</div>

<div class="tabs">
		<ul>
			<li><a href="#tabImages">{$lblImages|ucfirst}</a></li>
			<li><a href="#tabFiles">{$lblFiles|ucfirst}</a></li>
			<li><a href="#tabVideos">{$lblVideos|ucfirst}</a></li>
		</ul>
		
		<div id="tabImages">
			<div class="buttonHolderRight">
				<a href="{$var|geturl:'add_image'}&amp;product_id={$product.id}" class="button icon iconAdd" title="{$lblAddImage|ucfirst}">
					<span>{$lblAddImage|ucfirst}</span>
				</a>
			</div>
      
      <div class="seperator">&nbsp;</div>
      
      <div id="dataGridProductImagesHolder">
        {option:dataGridImages}
          <div class="dataGridImagesHolder">
            <form action="{$var|geturl:'mass_media_action'}" method="get" class="forkForms submitWithLink" id="massAction">
            <fieldset>
              <input type="hidden" name="product_id" value="{$product.id}" />
              {$dataGridImages}
            </fieldset>
            </form>
          </div>
        {/option:dataGridImages}
      </div>
      {option:!dataGridImages}<p>{$msgNoImages}</p>{/option:!dataGridImages}
		</div>
    
		<div id="tabFiles">
			<div class="buttonHolderRight">
				<a href="{$var|geturl:'add_file'}&amp;product_id={$product.id}" class="button icon iconAdd" title="{$lblAddFile|ucfirst}">
					<span>{$lblAddFile|ucfirst}</span>
				</a>
			</div>
      
      <div class="seperator">&nbsp;</div>
      
      <div id="dataGridProductFilesHolder">
        {option:dataGridFiles}
          <div class="dataGridProductsHolder">
            <form action="{$var|geturl:'mass_media_action'}" method="get" class="forkForms submitWithLink" id="massAction">
            <fieldset>
              <input type="hidden" name="product_id" value="{$product.id}" />
              {$dataGridFiles}
            </fieldset>
            </form>
          </div>
        {/option:dataGridFiles}
      </div>
      {option:!dataGridFiles}<p>{$msgNoFiles}</p>{/option:!dataGridFiles}
    </div>
    
    <div id="tabVideos">
			<div class="buttonHolderRight">
				<a href="{$var|geturl:'add_video'}&amp;product_id={$product.id}" class="button icon iconAdd" title="{$lblAddVideo|ucfirst}">
					<span>{$lblAddVideo|ucfirst}</span>
				</a>
			</div>
      
      <div class="seperator">&nbsp;</div>
      
      <div id="dataGridProductVideosHolder">
        {option:dataGridVideos}
          <div class="dataGridProductsHolder">
            <form action="{$var|geturl:'mass_media_action'}" method="get" class="forkForms submitWithLink" id="massAction">
            <fieldset>
              <input type="hidden" name="product_id" value="{$product.id}" />
              {$dataGridVideos}
            </fieldset>
            </form>
          </div>
        {/option:dataGridVideos}
      </div>
      {option:!dataGridVideos}<p>{$msgNoVideos}</p>{/option:!dataGridVideos}
    </div>
</div>

<div class="fullwidthOptions">
	<a href="{$var|geturl:'index'}" class="button">
		<span>{$lblBackToOverview|ucfirst}</span>
	</a>
</div>

{include:{$BACKEND_CORE_PATH}/Layout/Templates/StructureEndModule.tpl}
{include:{$BACKEND_CORE_PATH}/Layout/Templates/Footer.tpl}