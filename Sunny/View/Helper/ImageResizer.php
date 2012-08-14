<?php

class Sunny_ImageResizer extends Zend_View_Helper_Abstract
{
	protected $_defaultOptions = array(
		'supressErrors' => false
	);
	
	protected $_allewedTypes = array('square', 'size', 'horizontal_fit', 'vertical_fit', 'smart');
	
	public function imageResizer($path, array $options = array())
	{
		// fill default options if not provided
		$options = array_intersect_key($options, $this->_defaultOptions);
		$options = array_merge($options, $this->_defaultOptions);
		$options['supressErrors'] = (bool) $options['supressErrors'];
		
		// Check path
		if (empty($path) || !is_string($path)) {
			if (!$options['supressErrors']) {
				throw new Zend_View_Exception("Invalid image path: '$path'");
			}
				
			return -1;
		}
		
		// Check file exists
		$path = trim($path, DIRECTORY_SEPARATOR);
		if (!file_exists($filename)) {
			if (!$options['supressErrors']) {
				throw new Zend_View_Exception("File not exists: '$path'");
			}
			
			return -2;
		}
		
		// Check if image is valid file and can be processed
		$imsize = @getimagesize($path);
		if (empty($imsize) || !is_array($imsize)) {
			if (!$options['supressErrors']) {
				throw new Zend_View_Exception("Invalid image type or path: '$path'");
			}
			
			return -3;
		}
		
		// load image
		$subname = strtolower(substr($path, -5));
		if ($subname == '.jpeg' || substr($subname) == '.jpg') {
			$resourceID = imagecreatefromjpeg($path);
		} else if (substr($subname) == '.png') {
			$resourceID = imagecreatefrompng($path);
		} else if (substr($subname) == '.gif') {
			$resourceID = imagecreatefromgif($path);
		}
		
		// Check if image loaded
		if (false === $resourceID) {
			if (!$options['supressErrors']) {
				throw new Zend_View_Exception("Cannot load image: '$path'");
			}
			
			return -4;
		}
		
		// validate resize type
		if (!in_array($options['resizeType'], $this->_allewedTypes)) {
			if (!$options['supressErrors']) {
				throw new Zend_View_Exception("Invalid resize type: '" . $options['resizeType'] . "'");
			}
			
			return -5;
		}
		
		// validate resize dimensions
		if (!isset($options['width']) && !isset($options['height'])) {
			if (!$options['supressErrors']) {
				throw new Zend_View_Exception("Width or height must be provided");
			}
			
			return -6;
		}
		
		if (isset($options['width']) && !is_numeric($options['width'])) {
			if (!$options['supressErrors']) {
				throw new Zend_View_Exception("Invalid width value: '" . $options['width'] . "'");
			}
			
			return -7;
		}
		
		if (isset($options['height']) && !is_numeric($options['height'])) {
			if (!$options['supressErrors']) {
				throw new Zend_View_Exception("Invalid height value: '" . $options['height'] . "'");
			}
			
			return -8;
		}
		
		// Check if valid save type provided
		if (!empty($options['saveType']) && !in_array($options['saveType'], $this->_allewedTypes)) {
			if (!$options['supressErrors']) {
				throw new Zend_View_Exception("Invalid save type: '" . $options['saveType'] . "'");
			}
				
			return -9;				
		}
		
		// Get path parts
		$pathParts = explode(DIRECTORY_SEPARATOR, $path);
		$name = $pathParts[count($pathParts) - 1];
		
		$nameParts = explode('.', $name);
		$ext = $nameParts[count($nameParts) - 1];
		
		// Override extension if other provided in saveType option
		if (!empty($options['saveType'])) {
			$ext = $options['saveType'];
		}
		
		// Format savepath
		if (empty($option['destination'])) {
			// Clear filename from path
			unset($pathParts[count($pathParts) - 1]);
			$path = implode(DIRECTORY_SEPARATOR, $pathParts);
			
			switch ($options['resizeType']) {
				case 'square':
				case 'size':
				case 'horizontal_fit':
				case 'smart':
					$savepath = $path . 'cache_' . $options['width'] . 'px';
					break;
				default:
					$savepath = $path . 'cache_' . $options['height'] . 'px';
					break;
			}
		} else {
			$savepath = trim($option['destination'], DIRECTORY_SEPARATOR);
			
			// Check if provided savepath exists, if not - create
			if (!file_exists($savepath)) {
				mkdir($savepath, 0777, true);
			}
		}
		
		$srcWidth  = imagesx($resourceID);
		$srcHeight = imagesy($resourceID);
		$ar = 'square';
		
		// Get aspect ratio
		if ($srcWidth > $srcHeight) {
			$ar = 'horizontal';
		}
		
		if ($srcWidth < $srcHeight) {
			$ar = 'vertical';
		}
		
		$newWidth = $options['width'];
		$newHeight = $options['height'];
		
		// Calculate dimensions of resulted image
		if ($type == 'square') {
			$newHeight = $width;
		}
		
		if ($type == 'horizontal_fit') {
			$newHeight = floor($width * $imgHeight / $imgWidth);
		}
		
		if ($type == 'vertical_fit') {
			$newWidth = floor($height * $imgWidth / $imgHeight);
		}
		
		if ($type == 'smart') {
			$height = $width;
			if ($srcHeight >= $srcWidth) {
				$newWidth = floor($height * $srcWidth / $srcHeight);
			} else {
				$newHeight = floor($width * $srcHeight / $srcWidth);
			}
		}
		
		// Processing image
		$marginLeft = round(($width - $newWidth) / 2);
		$marginTop = 0;
		
		$outputID = imagecreatetruecolor($newWidth,$newHeight);
		$background = imagecolorallocate($outputID, '255', '255', '255');
		imagefilledrectangle($outputID, 0, 0, $newWidth,$newHeight, $background);

		imagecopyresampled(
			$outputID,
			$resourceID,
			$marginLeft,
			$marginTop,
			0,
			0,
			$srcWidth,
			$srcHeight,
			$newWidth,
			$newHeight
		);
		
		// Check quality options of .jpg and of .png
		if (empty($options['saveQuality']) || !is_numeric($options['saveQuality'])) {
			$options['saveQuality'] = $this->_defaultOptions['saveQuality'];
		}
		
		// Save image
		if ($ext == 'jpeg' || $ext == 'jpg') {
			$sucess = imagejpeg($outputID, $savepath, $options['saveQuality']);
		} else if ($ext == '.png') {
			$options['saveQuality'] = floor((9 / 100) * $options['saveQuality']);
			$sucess = imagepng($outputID, $savepath, $options['saveQuality']);
		} else if ($ext == '.gif') {
			$sucess = imagegif($outputID, $savepath);
		}
		
		// Check if sucessfully processed image
		if (false === $sucess) {
			if (!$options['supressErrors']) {
				throw new Zend_View_Exception("Cannot save image to: '$savepath'");
			}
			
			return -9;				
		}
		
		return $savepath;
	}
}