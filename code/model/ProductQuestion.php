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
		'Question' => 'Varchar(30)',
		'Label' => 'Varchar(30)',
		'Options' => 'Text'
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

	public function getFieldForProduct(Product $product){
		if($this->Options) {
			$finalOptions = array();
			$optionArray = explode(",", $this->Options);
			foreach($optionArray as $option) {
				$option = trim($option);
				$finalOptions[Convert::raw2htmlatt($option)] = $option;
			}
			return new DropdownField($this->getFieldForProductName(), $this->Question, $finalOptions);
		}
		else {
			return new TextField($this->getFieldForProductName(), $this->Question);
		}
	}


	public function getFieldForProductName(Product $product){
		return "ProductQuestionsAnswer"; //"Question-".$product->ID."_"$this->ID;
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
		$fields->addFieldToTab("Root.Main.Questions" new CheckboxSetField("ProductQuestions", "Additional Questions"));
	}

}
