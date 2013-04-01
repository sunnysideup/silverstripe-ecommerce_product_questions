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
				$imagesInFolder = DataObject::get("Image", "\ParentID\" = ".$this->FolderID);
				if($imagesInFolder) {
					$imagesInFolderArray =$imagesInFolder->map("ID", "Name");
					$fields->addFieldToTab("Root.Main", new ReadonlyField("ImagesInFolder", "Images in folder", implode(",", $imagesInFolderArray)));
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
			"InternalCode" => _t("ProductQuestion.INTERNALCODE", "Code used to identify question (not snown to customers)"),
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
	public function getFieldForProduct(Product $product){
		if($this->Options) {
			//if HasImages?
			$finalOptions = array();
			$optionArray = explode(",", $this->Options);
			foreach($optionArray as $option) {
				$option = trim($option);
				$finalOptions[Convert::raw2htmlatt($option)] = $option;
			}
			if($this->HasImages) {
				return new ProductQuestionImageSelectorField($this->getFieldForProductName($product), $this->Question, $finalOptions, null, $this->FolderID);
			}
			else {
				return new DropdownField($this->getFieldForProductName($product), $this->Question, $finalOptions);
			}
		}
		else {
			return new TextField($this->getFieldForProductName($product), $this->Question);
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



/**
 * adds functionality to Products
 *
 *
 *
 */
class ProductQuestion_ProductDecorator extends DataObjectDecorator {

	/**
	 * standard SS method
	 * defines additional statistics
	 */
	function extraStatics() {
		return array(
			'belongs_many_many' => array(
				'ProductQuestions' => 'ProductQuestion'
			)
		);
	}


	function updateCMSFields($fields) {
		$productQuestions = DataObject::get("ProductQuestion");
		if($productQuestions){
			$productQuestionsArray = $productQuestions->map("ID", "FullName");
			$fields->addFieldToTab("Root.Content.Details", new CheckboxSetField("ProductQuestions", "Additional Questions", $productQuestionsArray));
		}
	}

	function ProductQuestionsAnswerFormLink($id = 0){
		return $this->owner->Link("productquestionsanswerselect")."/".$id."/?BackURL=".urlencode(Controller::curr()->Link());
	}

}



/**
 * adds functionality to ProductControllers
 *
 *
 *
 */
class ProductQuestion_ProductControllerDecorator extends Extension {


	/**
	 * we need this here to
	 * because otherwise the extension will not work
	 */
	static $allowed_actions = array(
		"ProductQuestionsAnswerForm",
		"productquestionsanswerselect"
	);

	/**
	 * Stores the related OrderItem
	 * @var OrderItem
	 */
	protected $productQuestionOrderItem = null;

	/**
	 * renders a form with the product questions
	 * @return String (HTML)
	 */
	function productquestionsanswerselect(){
		$this->getProductQuestionOrderItem();
		return $this->owner->customise(
			array(
				"Title" => $this->productQuestionOrderItem->getTableTitle(),
				"Form" => $this->ProductQuestionsAnswerForm()
			)
		)->renderWith("productquestionsanswerselect") ;
	}

	/**
	 * returns a form with questions
	 * @return Form
	 */
	function ProductQuestionsAnswerForm(){
		$this->getProductQuestionOrderItem();
		if($this->productQuestionOrderItem) {
			return $this->productQuestionOrderItem->ProductQuestionsAnswerForm($this->owner, $name = "ProductQuestionsAnswerForm");
		}
	}

	/**
	 * returns the fields from the form
	 * @return FieldSet
	 */
	function ProductQuestionsAnswerFormFields(){
		$fieldSet = new FieldSet();
		$product = $this->owner->dataRecord;
		$productQuestions = $product->ProductQuestions();
		if($productQuestions) {
			foreach($productQuestions as $productQuestion) {
				$fieldSet->push($productQuestion->getFieldForProduct($product));
			}
		}
		return $fieldSet;
	}

	/**
	 * processes a form and
	 * adds product question answer(s) to order item.
	 * The answers are added as HTML and JSON
	 * and redirects back to the previous page or a set BackURL
	 * (set in the form data)
	 * @param Array $data - form data
	 * @param Form form - data from the form
	 */
	function addproductquestionsanswer($data, $form){
		$this->getProductQuestionOrderItem();
		$data = Convert::raw2sql($data);
		if($this->productQuestionOrderItem) {
			$this->productQuestionOrderItem->updateOrderItemWithProductAnswers(
				$answers = $data["ProductQuestions"],
				$write = true
			);
		}
		if(isset($data["BackURL"])){
			Director::redirect($data["BackURL"]);
		}
		else {
			Director::redirectBack();
		}
	}

	/**
	 * retrieves order item from post / get variables.
	 * @return OrderItem | Null
	 */
	protected function getProductQuestionOrderItem(){
		$id = intval($this->owner->request->param("ID"));
		if(!$id) {
			$id = intval($this->owner->request->postVar("OrderItemID"));
		}
		if(!$id) {
			$id = intval($this->owner->request->getVar("OrderItemID"));
		}
		if($id) {
			$this->productQuestionOrderItem = DataObject::get_by_id("OrderItem", $id);
		}
		if(!$this->productQuestionOrderItem) {
			user_error("NO this->productQuestionOrderItem specified");
		}
		return $this->productQuestionOrderItem;
	}

}
