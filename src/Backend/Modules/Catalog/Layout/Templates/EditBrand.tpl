{include:{$BACKEND_CORE_PATH}/Layout/Templates/Head.tpl}
{include:{$BACKEND_CORE_PATH}/Layout/Templates/StructureStartModule.tpl}

<div class="pageTitle">
    <h2>{$lblCatalog|ucfirst}: {$msgEditBrand|sprintf:{$item.title}}</h2>
</div>

{form:editBrand}
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
                    {option:item.image}
                        <p class="image">
                            <p><img src="{$FRONTEND_FILES_URL}/catalog/brands/150x150/{$item.image}"/></p>
                            <label for="deleteImage">{$chkDeleteImage} {$lblDelete|ucfirst}</label>
                            {$chkDeleteImageError}
                        </p>
                    {/option:item.image}
                    <label for="image">{$lblImage|ucfirst}</label>
                    {$fileImage} {$fileImageError}
                </div>
            </div>
        </div>

        <div id="tabSEO">
            {include:{$BACKEND_CORE_PATH}/Layout/Templates/Seo.tpl}
        </div>
    </div>

    <div class="fullwidthOptions">
        {option:showDelete}
            <a href="{$var|geturl:'delete_brand'}&amp;id={$item.id}" data-message-id="confirmDelete" class="askConfirmation button linkButton icon iconDelete">
                <span>{$lblDelete|ucfirst}</span>
            </a>
            <div id="confirmDelete" title="{$lblDelete|ucfirst}?" style="display: none;">
                <p>
                    {$msgConfirmDelete|sprintf:{$item.title}}
                </p>
            </div>
        {/option:showDelete}

        <div class="buttonHolderRight">
            <input id="editButton" class="inputButton button mainButton" type="submit" name="edit" value="{$lblSave|ucfirst}" />
        </div>
    </div>
{/form:editBrand}

{include:{$BACKEND_CORE_PATH}/Layout/Templates/StructureEndModule.tpl}
{include:{$BACKEND_CORE_PATH}/Layout/Templates/Footer.tpl}
