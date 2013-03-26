<?php

/**
 *
 * @package ecommerce
 * @subpackage buyables
 */
class ProductQuestion extends DataObject {

	/**
	 * Standard SS variable.
	 */
	public static $db = array(
		'InternalCode' => 'Varchar(30)',
		'Question' => 'Varchar(30)',
		'Label' => 'Varchar(30)',
		'Options' => 'Text'
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
		$count = DB::query("SELECT COUNT(\"ID\") AS C  FROM \"Product\";")->value();
		if($count > 0 && $count < 200) {
			$products = DataObject::get("Product");
			$productMap = $products->map("ID", "FullName");
			$fields->replaceField("Products", new CheckboxSetField("Products", "Products", $productMap));
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

	public function FullName(){
		return $this->getFullName();
	}
	public function getFullName(){
		return $this->Question." (".$this->InternalCode.")";
	}

	public function getFieldForProduct(Product $product){
		if($this->Options) {
			$finalOptions = array();
			$optionArray = explode(",", $this->Options);
			foreach($optionArray as $option) {
				$option = trim($option);
				$finalOptions[Convert::raw2htmlatt($option)] = $option;
			}
			return new DropdownField($this->getFieldForProductName($product), $this->Question, $finalOptions);
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
			$this->InternalCode = $this->ID;
		}
	}

}

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

class ProductQuestion_ProductControllerDecorator extends Extension {


	/**
	 * we need this here to
	 * because otherwise the extension will not work
	 */
	static $allowed_actions = array(
		"ProductQuestionsAnswerForm",
		"productquestionsanswerselect"
	);

	protected $productQuestionOrderItem = null;

	function productquestionsanswerselect(){
		if(!$this->getProductQuestionOrderItem()) {
			user_error("NO this->productQuestionOrderItem specified");
		}
		return $this->owner->customise(
			array(
				"Title" => $this->productQuestionOrderItem->getTableTitle(),
				"Form" => $this->ProductQuestionsAnswerForm()
			)
		)->renderWith("productquestionsanswerselect") ;
	}

	function ProductQuestionsAnswerForm(){
		if(!$this->getProductQuestionOrderItem()) {
			user_error("NO this->productQuestionOrderItem specified");
		}
		if($this->productQuestionOrderItem) {
			return $this->productQuestionOrderItem->ProductQuestionsAnswerForm($this->owner, $name = "ProductQuestionsAnswerForm");
		}
	}

	function addproductquestionsanswer($data, $form){
		if(!$this->getProductQuestionOrderItem()) {
			user_error("NO this->productQuestionOrderItem specified");
		}
		$data = Convert::raw2sql($data);
		if($this->productQuestionOrderItem) {
			if($this->productQuestionOrderItem->canEdit()) {
				$this->productQuestionOrderItem->ProductQuestionsAnswer = "";
				$productQuestionsArray = $data["ProductQuestions"];
				if(is_array($productQuestionsArray) && count($productQuestionsArray)) {
					foreach($productQuestionsArray as $productQuestionID => $productQuestionAnswer) {
						$question = DataObject::get_by_id("ProductQuestion", intval($productQuestionID));
						if($question) {
							$this->productQuestionOrderItem->ProductQuestionsAnswer .= "
								<span class=\"productQuestion\">
									<strong class=\"productQuestionsLabel\">".$question->Label."</strong>:
									<em class=\"productQuestionsAnswer\">".$productQuestionAnswer."</em>
								</span>";
						}
					}
				}
				$this->productQuestionOrderItem->JSONAnswers = Convert::raw2json($data);
				$this->productQuestionOrderItem->write();
			}
		}
		if(isset($data["BackURL"])){
			Director::redirect($data["BackURL"]);

		}
		else {
			Director::redirectBack();
		}
		return;
	}

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
		return $this->productQuestionOrderItem;
	}

}
