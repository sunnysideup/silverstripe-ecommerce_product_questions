<?php

/**
 *
 * @package ecommerce
 * @subpackage ProductQuestion
 */
class ProductQuestion extends DataObject {

	/**
	 * Standard SS variable.
	 */
	public static $db = array(
		'InternalCode' => 'Varchar(30)',
		'Question' => 'Varchar(30)',
		'Label' => 'Varchar(30)',
		'Options' => 'Text',
		"HasImages" => "Boolean"
	);

	/**
	 * Standard SS variable.
	 */
	public static $casting = array(
		'FullName' => 'Varchar'
	);

	/**
	 * Standard SS variable.
	 * Links questions to products
	 */
	static $many_many = array(
		'Products' => 'Product'
	);

	/**
	 * Standard SS variable.
	 * Links to folder for images
	 */
	static $has_one = array(
		'Folder' => 'Folder'
	);

	/**
	 * Standard SS variable.
	 * Links questions to products
	 */
	static $summary_fields = array(
		'InternalCode' => 'InternalCode',
		'Question' => 'Question'
	);

	/**
	 * Standard SS variable.
	 */
	public static $default_sort = "\"Question\" ASC";

	/**
	 * Standard SS variable.
	 */
	public static $singular_name = "Product Question";
		function i18n_singular_name() { return _t("ProductQuestion.PRODUCT_QUESTION", "Product Question");}

	/**
	 * Standard SS variable.
	 */
	public static $plural_name = "Product Variations";
		function i18n_plural_name() { return _t("ProductQuestion.PRODUCT_QUESTIONS", "Product Questions");}
		public static function get_plural_name(){
			$obj = Singleton("ProductQuestion");
			return $obj->i18n_plural_name();
		}

	/**
	 * turns an option into potential file names
	 * e.g. red
	 * returns
	 * red.jpg, red.png, red.gif
	 * @param String $option
	 * @return Array
	 */
	public static function create_file_array_from_option($option) {
		$option = str_replace(' ', "-", trim($option));
		$option = preg_replace("/[^a-z0-9_-]+/i", "", $option);
		$imageOptions = array(
			$option.".png",
			$option.".PNG",
			$option.".gif",
			$option.".GIF" ,
			$option.".jpg",
			$option.".JPG",
			$option.".jpeg",
			$option.".JPEG"
		);
		return $imageOptions;
	}

	/**
	 * Standard SS method
	 * @return FieldSet
	 */
	function getCMSFields() {
		$fields = parent::getCMSFields();
		$productCount = DB::query("SELECT COUNT(\"ID\") AS C  FROM \"Product\";")->value();
		if($productCount > 0 && $productCount < 500) {
			$products = DataObject::get("Product");
			if($products) {
				$productMap = $products->map("ID", "FullName");
				$fields->replaceField("Products", new CheckboxSetField("Products", _t("ProductQuestion.PRODUCTS", "Products"), $productMap));
			}
		}
		$fields->addFieldToTab("Root.Main", new HeaderField("Images", _t("ProductQuestion.IMAGES", "Images"), 2), "HasImages");
		if($this->HasImages) {
			$folders = DataObject::get("Folder", "\"Sort\"");
			if($folders) {
				$folderMap = $folders->map("ID", "Title");
				$folders = null;
				$labelArray = $this->customFieldLabels();
				$labelArray = $labelArray["FolderID"];
				$fields->replaceField(
					"Folder",
					new TreeDropdownField("FolderID", _t("ProductQuestion.FOLDER", "Folder"), "Folder")
				);
			}
			if($this->FolderID) {
				$imagesInFolder = DataObject::get("Image", "\"ParentID\" = ".$this->FolderID);
				if($imagesInFolder) {
					$imagesInFolderArray = $imagesInFolder->map("ID", "Name");
					$options = explode(",", $this->Options);
					$imagesInFolderField = new ReadonlyField("ImagesInFolder", _t("ProductQuestion.NO_IMAGES", "Images in folder"), implode("<br />", $imagesInFolderArray));
					$imagesInFolderField->dontEscape = true;
					$fields->addFieldToTab("Root.Images", $imagesInFolderField);
					//matches
					if($this->exists()) {
						$matchesInFolderArray = array();
						$nonMatchesInFolderArray = array();
						$options = explode(",", $this->Options);
						if(count($options)) {
							foreach($options as $option) {
								$fileNameArray = self::create_file_array_from_option($option);
								foreach($fileNameArray as $fileName) {
									if(in_array($fileName, $imagesInFolderArray)) {
										$matchesInFolderArray[$option] = "<strong>".$option."</strong>: ".$fileName;
									}
								}
								if(!isset($matchesInFolderArray[$option])) {
									$nonMatchesInFolderArray[$option] = "<strong>".$option."</strong>: ".implode(",", $fileNameArray);
								}
							}
						}
						$matchesInFolderField = new ReadonlyField("MatchesInFolder", _t("ProductQuestion.MATCHES_IN_FOLDER", "Matches in folder"), implode("<br />", $matchesInFolderArray));
						$matchesInFolderField->dontEscape = true;
						$fields->addFieldToTab("Root.Images", $matchesInFolderField);
						$nonMatchesInFolderField = new ReadonlyField("NonMatchesInFolder", _t("ProductQuestion.NON_MATCHES_IN_FOLDER", "NON Matches in folder"), implode("<br />", $nonMatchesInFolderArray));
						$nonMatchesInFolderField->dontEscape = true;
						$fields->addFieldToTab("Root.Images", $nonMatchesInFolderField);
					}
				}
				else {
					$imagesInFolderField = new ReadonlyField("ImagesInFolder", "Images in folder", _t("ProductQuestion.NO_IMAGES", "There are no images in this folder"));
					$fields->addFieldToTab("Root.Main", $imagesInFolderField);
				}
			}
		}
		else {
			$fields->removeByName("Folder");
		}
		if($products) {
			$randomProduct = $products->First();
			$fields->addFieldToTab("Root.Example", $this->getFieldForProduct($randomProduct));
		}
		$this->extend('updateCMSFields', $fields);
		return $fields;
	}


	/**
	 * definition of field lables
	 * TODO: is this a common SS method?
	 * @return Array
	 */
	function customFieldLabels(){
		$newLabels = array(
			"InternalCode" => _t("ProductQuestion.INTERNALCODE", "Code used to identify question (not shown to customers)"),
			"Question" => _t("ProductQuestion.QUESTION", "Question (e.g. what configuration do you prefer?)"),
			"Label" => _t("ProductQuestion.LABEL", "Label (e.g. Your selected configuration)"),
			"Options" => _t("ProductQuestion.OPTIONS", "Predefined Options (leave blank for any option).  These must be comma separated (e.g. red, blue, yellow, orange)"),
			"HasImages" => _t("ProductQuestion.HAS_IMAGES", "Has Images? .... Select this to link each option to an image (e.g. useful if you have colour swatches). Once selected and reloaded you will be able to select a folder from where to select the images"),
			"FolderID" => _t("ProductQuestion.FOLDER_ID", "Select the folder in which the images live.  The images need to have the exact same file name as the options listed.  For example, if one of your options is 'red' then there should be a file in your folder called 'red.png' or 'red.jpg' or 'red.gif', the following filenames would not work: 'Red.png', 'red1.jpg', 'RED.gif', etc...  "),
		);
		return $newLabels;
	}


	/**
	 * standard SS method for decorators.
	 * @param Array - $fields: array of fields to start with
	 * @return null ($fields variable is automatically updated)
	 */
	function fieldLabels($includerelations = true) {
		$defaultLabels = parent::fieldLabels();
		$newLabels = $this->customFieldLabels();
		$labels = array_merge($defaultLabels, $newLabels);
		$this->extend('updateFieldLabels', $labels);
		return $labels;
	}

	/**
	 * casted variable
	 * @return String
	 */
	public function FullName(){return $this->getFullName();}
	public function getFullName(){
		return $this->Question." (".$this->InternalCode.")";
	}

	/**
	 *
	 * @return FormField
	 */
	public function getFieldForProduct(Product $product, $value = null){
		if($this->Options) {
			//if HasImages?
			$finalOptions = array();
			$optionArray = explode(",", $this->Options);
			foreach($optionArray as $option) {
				$option = trim($option);
				$finalOptions[Convert::raw2htmlatt($option)] = $option;
			}
			if($this->HasImages) {
				return new ProductQuestionImageSelectorField($this->getFieldForProductName($product), $this->Question, $finalOptions, $value, $this->FolderID);
			}
			else {
				return new DropdownField($this->getFieldForProductName($product), $this->Question, $finalOptions, $value);
			}
		}
		else {
			return new TextField($this->getFieldForProductName($product), $this->Question, $value);
		}
	}


	public function getFieldForProductName(Product $product){
		return "ProductQuestions[".$this->ID."]";
	}

	function onBeforeWrite(){
		parent::onBeforeWrite();
		if(!$this->InternalCode) {
			$this->InternalCode = $this->Label;
		}
		if(!$this->HasImages) {
			$this->FolderID = 0;
		}
	}

}

