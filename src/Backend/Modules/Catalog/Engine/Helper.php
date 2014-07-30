<?php

namespace Backend\Modules\Catalog\Engine;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * In this file we store all generic functions that we will be using in the catalog module
 *
 * @author Bart De Clercq <info@lexxweb.be>
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class Helper
{
	
	/**
	 * Adds a source image and additional images according to the provided formats.
	 * Files should not be uploaded yet
	 *
	 * @param SpoonFileField $filefield The formfield to upload images for.
	 * @param string $path The path to the file.
	 * @param string $filename The file's name.
	 * @param array[optional] $formats The formats of the images to generate based on the source.
	 * @return bool	Returns true if every file succeeded
	 */
	public static function addImages($filefield, $path, $filename, array $formats = null)
	{
		// check input
		if(empty($filefield) || empty($filename)) return false;

		// create the path up to the source dir
		if(!\SpoonDirectory::exists($path . '/source')) \SpoonDirectory::create($path . '/source');

		// source path
		$pathSource = $path . '/source';

		try {
			// move image
			$success[] = $filefield->moveFile($pathSource . '/' . $filename);

			// (re)size the image
			self::generateImages($path, $filename, $formats);
		} catch(Exception $e) {
			throw new SpoonException($e->getMessage());
		}

		// returns true if everything succeeded
		return true;
	}

	/**
	 * Generates the desired image formats based on the source image
	 *
	 * @param string $path The path we write the images to.
	 * @param string $filename The name of the source file.
	 * @param array[optional] $formats The formats of the images to generate based on the source.
	 * @return bool	Returns true if every file succeeded
	 */
	public static function generateImages($path, $filename, array $formats = null)
	{
		// check input
		if(empty($filename)) return false;

		// create the path up to the source dir
		if(!\SpoonDirectory::exists($path . '/source')) \SpoonDirectory::create($path . '/source');

		// source path
		$pathSource = $path . '/source';
        		
		// formats found
		if(!empty($formats)) {
			// loop the formats
			foreach($formats as $format) {
				// create the path for this product
				if(!\SpoonDirectory::exists($path . '/' . $format['size'])) \SpoonDirectory::create($path . '/' . $format['size']);

				// exploded format
				$explodedFormat = explode('x', $format['size']);

				// set enlargement/aspect ratio
				$allowEnlargement = (isset($format['allow_enlargement'])) ? $format['allow_enlargement'] : true;
				$forceAspectRatio = (isset($format['force_aspect_ratio'])) ? $format['force_aspect_ratio'] : true;
								
				// get measurements of the source file
				$sourceDimensions = getimagesize($pathSource . '/' . $filename);

				// source width is bigger than what it should be
				$width = ($sourceDimensions[0] > $explodedFormat[0]) ? $explodedFormat[0] : $sourceDimensions[0];
				$height = (isset($explodedFormat[1]) && $sourceDimensions[1] > $explodedFormat[1]) ? $explodedFormat[1] : $sourceDimensions[1];

				// check if height is empty or not
				if(empty($height)) $height = null;

				// make a thumbnail for the provided format
				$thumbnail = new \SpoonThumbnail($pathSource . '/' . $filename, $width, ($forceAspectRatio ? null : $height));
				$thumbnail->setAllowEnlargement($allowEnlargement);
				$thumbnail->setForceOriginalAspectRatio($forceAspectRatio);
				$success[] = $thumbnail->parseToFile($path . '/' . $format['size'] . '/' . $filename);
				unset($thumbnail);	
			}
		}
	}

	/**
	 * Get the modules that have a slideshows hook
	 *
	 * @return array
	 */
	public static function getModules()
	{
		return BackendModel::getModuleSetting('slideshows', 'modules');
	}

	/**
	 * Get the supported methods by module.
	 *
	 * @param string $module The module we are fetching the supported methods for.
	 * @return array
	 */
	public static function getSupportedMethodsByModule($module)
	{
		$helperFile = FRONTEND_MODULES_PATH . '/' . $module . '/engine/slideshows.php';
		$helperFileContents = \SpoonFile::getContent($helperFile);
		$results = array();

		preg_match_all('/public static function (.*)\((.*)\)/', $helperFileContents, $matches);

		if(isset($matches[1]) && !empty($matches[1])) {
			foreach($matches[1] as $key => $method)
			{
				$results[$key]['class'] = 'Frontend' . \SpoonFilter::toCamelCase($module) . 'SlideshowsModel';
				$results[$key]['methods'][] = $method;
			}
		}

		return $results;
	}

	/**
	 * Get the supported methods by module as pairs.
	 *
	 * @param string $module The module we are fetching the supported methods for.
	 * @return array
	 */
	public static function getSupportedMethodsByModuleAsPairs($module)
	{
		$methods = self::getSupportedMethodsByModule($module);

		$results = array();

		foreach($methods as $key => $item) {
			if(is_array($item)) {
				if(isset($item['methods'])) {
					foreach($item['methods'] as $key => $value) {
						$results[$item['class']][$item['class'] . '::' . $value] = $value . '()';
					}
				}
			}
		}

		return $results;
	}

	/**
	 * This is used in the ajax request to repopulate the methods dropdown on slideshow edit forms.
	 *
	 * @param string $module The module we are fetching the supported methods for.
	 * @return array
	 */
	public static function getSupportedMethodsByModuleAsPairsString($module)
	{
		$methods = self::getSupportedMethodsByModule($module);
		$results = '';

		foreach($methods as $key => $item) {
			if(is_array($item)) {
				if(isset($item['methods'])) {
					$results .= '<optgroup label="' . $item['class'] . '">' . PHP_EOL;
					foreach($item['methods'] as $key => $value){
						$results .= '<option value="' . $item['class'] . '::' . $value . '">' . $value . '()' . '</option>' . PHP_EOL;
					}

					$results .= '</optgroup>';
				}
			}
		}

		return $results;
	}

	/**
	 * Get the supported modules.
	 *
	 * @return array
	 */
	public static function getSupportedModules()
	{
		$results = array();
		$modules = self::getModules();

		if(!empty($modules)) {
			// add the modules to the results
			foreach($modules as $module) $results[$module] = $module;

			// add an empty value to the first element of the array
			array_unshift($results, '');
		}

		return $results;
	}

	/**
	 * Write a slideshows helper file for the specified module
	 *
	 * @param string $module The module to write the helper file for.
	 */
	public static function writeHelperFile($module)
	{
		$camelcasedModule = \SpoonFilter::toCamelCase($module);
		$helperFile = FRONTEND_MODULES_PATH . '/' . $module . '/engine/slideshows.php';

		if(!\SpoonFile::exists($helperFile)) {
			$content = '<?php
						class Frontend' . $camelcasedModule . 'SlideshowsModel
						{
							public static function getImages()
							{
								$db = FrontendModel::getContainer()->get(\'database\');
						
								// This should work with an interface so people know what fields to add.
								// For now, check slideshows/layout/templates/basic.tpl to mimick the array structure.
								$records = array();
						
								return $records;
							}
						}
						';

			\SpoonFile::setContent($helperFile, $content);
		}
	}
}