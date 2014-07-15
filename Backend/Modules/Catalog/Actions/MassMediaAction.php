<?php

namespace Backend\Modules\Catalog\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This action is used to perform mass actions on product media (delete, ...)
 * 
 * @author Bart De Clercq <info@lexxweb.be>
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class BackendCatalogMassMediaAction extends BackendBaseAction
{
	/**
	 * Execute the action
	 */
	public function execute()
	{
		parent::execute();
		
		// action to execute
		$action = SpoonFilter::getGetValue('action', array('deleteImages', 'deleteFiles', 'deleteVideos'), 'delete');
		
		if(!isset($_GET['id'])) $this->redirect(BackendModel::createURLForAction('index') . '&error=no-selection');
		
		// at least one id
		else
		{
			// redefine id's
			$aIds = (array) $_GET['id'];
			$slideshowID = (int) $_GET['product_id'];

			// delete media
			if($action == 'deleteImages')
			{
				BackendCatalogModel::deleteImage($aIds);
			}
			
			// delete files
			else if($action == 'deleteFiles')
			{
				BackendCatalogModel::deleteFile($aIds);
			}
			
			// delete videos
			else if($action == 'deleteVideos')
			{
				BackendCatalogModel::deleteVideo($aIds);
			}
		}

		$this->redirect(BackendModel::createURLForAction('media') . '&product_id=' . $slideshowID . '&report=deleted');
	}
}
