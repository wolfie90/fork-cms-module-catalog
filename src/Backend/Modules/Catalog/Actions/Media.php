<?php

namespace Backend\Modules\Catalog\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\ActionIndex as BackendBaseActionIndex;
use Backend\Core\Engine\Language as BL;
use Backend\Core\Engine\Authentication as BackendAuthentication;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Engine\DataGridDB as BackendDataGridDB;
use Backend\Core\Engine\DataGridFunctions as BackendDataGridFunctions;
use Backend\Modules\Catalog\Engine\Model as BackendCatalogModel;

/**
 * This is the media action, it will display the overview of media for a specific product.
 *
 * @author Bart De Clercq <info@lexxweb.be>
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class Media extends BackendBaseActionIndex
{
	/**
	 * The product id
	 *
	 * @var	int
	 */
	private $id;

	/**
	* The project record
	*
	* @var	array
	*/
	private $product;
    
	/**
	 * Datagrid with published items
	 *
	 * @var	DataGrid
	 */
	protected $dgImages, $dgFiles, $dgVideos;

	/**
	 * Execute the action
	 */
	public function execute()
	{
		$this->id = $this->getParameter('product_id', 'int');
		
		if($this->id !== null && BackendCatalogModel::exists($this->id))
		{
			parent::execute();

			$this->getData();
			$this->loadDataGridImages();
			$this->loadDataGridFiles();
			$this->loadDataGridVideos();
			$this->parse();
			$this->display();
		}
		
		// the project does not exist
		else $this->redirect(BackendModel::createURLForAction('index') . '&error=non-existing');
	}

	/**
	 * Gets all necessary data
	 */
	protected function getData()
	{
		$this->product = BackendCatalogModel::get($this->id);
	}

	/**
	 * Loads the datagrid of the images
	 */
	protected function loadDataGridImages()
	{
	// set image link
	$imageLink = FRONTEND_FILES_URL . '/' . $this->module . '/[product_id]/64x64';
    
	// create images datagrid
	$this->dgImages = new BackendDataGridDB(BackendCatalogModel::QRY_DATAGRID_BROWSE_IMAGES, $this->id);
	$this->dgImages->setAttributes(array('class' => 'dataGrid sequenceByDragAndDrop'));
	$this->dgImages->setAttributes(array('id' => 'products_images_dg'));	
	  
	$this->dgImages->setColumnHidden('sequence');
	$this->dgImages->setColumnHidden('product_id');
	  
	  $this->dgImages->addColumn('dragAndDropHandle', null, '<span>' . BL::lbl('Move') . '</span>');
	  $this->dgImages->setColumnsSequence('dragAndDropHandle');
	$this->dgImages->setColumnAttributes('dragAndDropHandle', array('class' => 'dragAndDropHandle'));
	  	  
	  $this->dgImages->setRowAttributes(array('data-id' => '[id]'));
        $this->dgImages->setSortParameter('asc');
	  $this->dgImages->addColumn('edit', null, BL::lbl('Edit'), BackendModel::createURLForAction('edit_image') . '&amp;id=[id]&amp;product_id=[product_id]', BL::lbl('Edit'));
      
	$this->dgImages->setColumnFunction(array(new BackendDataGridFunctions(), 'showImage'), array($imageLink, '[filename]'), 'filename');
        $this->dgImages->setColumnAttributes('filename', array('class' => 'thumbnail'));
	  $this->dgImages->addColumn('checkbox', '<span class="checkboxHolder block"><input type="checkbox" name="toggleChecks" value="toggleChecks" />', '<input type="checkbox" name="id[]" value="[id]" class="inputCheckbox" /></span>');
        $this->dgImages->setColumnsSequence('checkbox');
      
	  $ddmMassAction = new \SpoonFormDropdown('action', array('deleteImages' => BL::lbl('Delete')), 'deleteImages');
      $this->dgImages->setMassAction($ddmMassAction);
	  $this->dgImages->setColumnAttributes('title', array('data-id' => '{id:[id]}'));

	  $this->dgImages->setAttributes(array('data-action' => "sequence_media_images"));
    }

	/**
	 * Loads the datagrid of the files
	 */
	protected function loadDataGridFiles()
	{
	// create files datagrid
	$this->dgFiles = new BackendDataGridDB(BackendCatalogModel::QRY_DATAGRID_BROWSE_FILES, $this->id);
	
	$this->dgFiles->setAttributes(array('class' => 'dataGrid sequenceByDragAndDrop'));
	$this->dgFiles->setAttributes(array('id' => 'products_files_dg'));	
      
	  $this->dgFiles->setColumnHidden('sequence');
	  $this->dgFiles->setColumnHidden('product_id');
      
	  $this->dgFiles->addColumn('dragAndDropHandle', null, '<span>' . BL::lbl('Move') . '</span>');
      $this->dgFiles->setColumnsSequence('dragAndDropHandle');
      $this->dgFiles->setColumnAttributes('dragAndDropHandle', array('class' => 'dragAndDropHandle'));
      
	  $this->dgFiles->setRowAttributes(array('data-id' => '[id]'));	
      
      $this->dgFiles->setSortingColumns(array('title', 'sequence'), 'sequence');
      $this->dgFiles->setSortParameter('asc');

      $this->dgFiles->addColumn('edit', null, BL::lbl('Edit'), BackendModel::createURLForAction('edit_file') . '&amp;id=[id]&amp;product_id=[product_id]', BL::lbl('Edit'));      
	  $this->dgFiles->addColumn('checkbox', '<span class="checkboxHolder block"><input type="checkbox" name="toggleChecks" value="toggleChecks" />', '<input type="checkbox" name="id[]" value="[id]" class="inputCheckbox" /></span>');
      $this->dgFiles->setColumnsSequence('checkbox');
      
      $ddmMassAction = new \SpoonFormDropdown('action', array('deleteFiles' => BL::lbl('Delete')), 'deleteFiles');
      $this->dgFiles->setMassAction($ddmMassAction);
	  $this->dgFiles->setColumnAttributes('title', array('data-id' => '{id:[id]}'));
	}

	/**
	 * Loads the datagrid of the videos
	 */
	protected function loadDataGridVideos()
	{
      // create videos datagrid
      $this->dgVideos = new BackendDataGridDB(BackendCatalogModel::QRY_DATAGRID_BROWSE_VIDEOS, $this->id);
	
      $this->dgVideos->setAttributes(array('class' => 'dataGrid sequenceByDragAndDrop'));
      $this->dgVideos->setAttributes(array('id' => 'products_videos_dg'));	
      
	  $this->dgVideos->setColumnHidden('sequence');
	  $this->dgVideos->setColumnHidden('product_id');
            
	  $this->dgVideos->addColumn('dragAndDropHandle', null, '<span>' . BL::lbl('Move') . '</span>');
      $this->dgVideos->setColumnsSequence('dragAndDropHandle');
      $this->dgVideos->setColumnAttributes('dragAndDropHandle', array('class' => 'dragAndDropHandle'));
      
	  $this->dgVideos->setRowAttributes(array('data-id' => '[id]'));	
      
      $this->dgVideos->setSortingColumns(array('title', 'sequence'), 'sequence');
      $this->dgVideos->setSortParameter('asc');

      $this->dgVideos->addColumn('edit', null, BL::lbl('Edit'), BackendModel::createURLForAction('edit_video') . '&amp;id=[id]&amp;product_id=[product_id]', BL::lbl('Edit'));
      $this->dgVideos->addColumn('checkbox', '<span class="checkboxHolder block"><input type="checkbox" name="toggleChecks" value="toggleChecks" />', '<input type="checkbox" name="id[]" value="[id]" class="inputCheckbox" /></span>');
      $this->dgVideos->setColumnsSequence('checkbox');
      
      $ddmMassAction = new \SpoonFormDropdown('action', array('deleteVideos' => BL::lbl('Delete')), 'deleteVideos');
      $this->dgVideos->setMassAction($ddmMassAction);
	  $this->dgVideos->setColumnAttributes('title', array('data-id' => '{id:[id]}'));
	}
    
	/**
	 * Parse & display the page
	 */
	protected function parse()
	{
		$this->tpl->assign('dataGridImages', ($this->dgImages->getNumResults() != 0) ? $this->dgImages->getContent() : false);
		$this->tpl->assign('dataGridFiles', ($this->dgFiles->getNumResults() != 0) ? $this->dgFiles->getContent() : false);
		$this->tpl->assign('dataGridVideos', ($this->dgVideos->getNumResults() != 0) ? $this->dgVideos->getContent() : false);
        
		$this->tpl->assign('product', $this->product);
	}
}
