{include:{$BACKEND_CORE_PATH}/layout/templates/head.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/structure_start_module.tpl}

<div class="pageTitle">
	<h2>{$lblCatalog|ucfirst}: {$lblOrders|ucfirst}</h2>
</div>

<div id="tabs" class="tabs">
	<ul>
		<li><a href="#tabModeration">{$lblWaitingForModeration|ucfirst} ({$numModeration})</a></li>
		<li><a href="#tabCompleted">{$lblCompleted|ucfirst} ({$numCompleted})</a></li>
	</ul>

	<div id="tabModeration">
		{option:dgModeration}
			<form action="{$var|geturl:'mass_order_action'}" method="get" class="forkForms" id="ordersModeration">
				<div class="dataGridHolder">
					<input type="hidden" name="from" value="moderation" />
					{$dgModeration}
				</div>
			</form>
		{/option:dgModeration}
		{option:!dgModeration}{$msgNoOrders}{/option:!dgModeration}
	</div>

	<div id="tabCompleted">
		{option:dgCompleted}
			<form action="{$var|geturl:'mass_order_action'}" method="get" class="forkForms" id="ordersCompleted">
				<div class="dataGridHolder">
					<input type="hidden" name="from" value="completed" />
					<div class="generalMessage infoMessage">
						{$msgDeleteAllCompleted}:
						<a href="{$var|geturl:'delete_completed'}">{$lblDelete|ucfirst}</a>
					</div>
					{$dgCompleted}
				</div>
			</form>
		{/option:dgCompleted}
		{option:!dgCompleted}{$msgNoOrders}{/option:!dgCompleted}
	</div>
</div>

<div id="confirmModerationCompleted" title="{$lblCompleted|ucfirst}?" style="display: none;">
	<p>{$msgConfirmMassCompleted}</p>
</div>
<div id="confirmDeleteModeration" title="{$lblDelete|ucfirst}?" style="display: none;">
	<p>{$msgConfirmMassDelete}</p>
</div>
<div id="confirmCompletedModeration" title="{$lblCompleted|ucfirst}?" style="display: none;">
	<p>{$msgConfirmMassCompleted}</p>
</div>
<div id="confirmDeletCompleted" title="{$lblDelete|ucfirst}?" style="display: none;">
	<p>{$msgConfirmMassDelete}</p>
</div>

{include:{$BACKEND_CORE_PATH}/layout/templates/structure_end_module.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/footer.tpl}