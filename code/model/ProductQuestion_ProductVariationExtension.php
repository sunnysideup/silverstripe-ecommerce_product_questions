<?php



/**
 * adds functionality to Products
 *
 *
 *
 */
class ProductQuestion_ProductVariationDecorator extends DataObjectDecorator {

	/**
	 * standard SS method
	 * defines additional statistics
	 */
	function extraStatics() {
		return array(
			'db' => array(
				'ConfigureLabel' => 'Varchar(50)'
			),
			'many_many' => array(
				'IgnoreProductQuestions' => 'ProductQuestion',
				'AdditionalProductQuestions' => 'ProductQuestion'
			)
		);
	}


	function updateCMSFields($fields) {
		if(DataObject::get("ProductQuestion")) {
			$fields->addFieldToTab("Root.Content", new TextField("ConfigureLabel", "Configure Link Label"));
			$productQuestionsDefault = $this->owner->Product()->ProductQuestions();
			$productQuestionsDefaultArray = array(0 => 0);
			if($productQuestionsDefault && $productQuestionsDefault->count()){
				$productQuestionsDefaultArray = $productQuestionsDefault->map("ID", "FullName");
				$fields->addFieldToTab("Root.Questions", new CheckboxSetField("IgnoreProductQuestions", "Ignore Questions for this variation", $productQuestionsDefaultArray));
			}
			$productQuestionsAdditional = DataObject::get("ProductQuestion", "ProductQuestion.ID NOT IN(".implode(",", array_flip($productQuestionsDefaultArray)).")");
			if($productQuestionsAdditional && $productQuestionsAdditional->count()){
				$productQuestionsAdditionalArray = $productQuestionsAdditional->map("ID", "FullName");
				$fields->addFieldToTab("Root.Questions", new CheckboxSetField("AdditionalProductQuestions", "Additional Questions for this variation", $productQuestionsAdditionalArray));
			}
		}
	}

	/**
	 * returns the fields from the form
	 * @return FieldSet
	 */
	function ProductQuestionsAnswerFormFields(){
		$fieldSet = new FieldSet();
		$productQuestions = $this->ProductQuestions();
		if($productQuestions && $productQuestions->count()) {
			foreach($productQuestions as $productQuestion) {
				$fieldSet->push($productQuestion->getFieldForProduct($this));
			}
		}
		return $fieldSet;
	}

	/**
	 * returns a label that is used to allow customers to open the form
	 * for answering the Product Questions.
	 * @return String
	 */
	public function CustomConfigureLabel(){
		if($this->owner->ProductQuestions()) {
			if($this->owner->ProductQuestions()->count()) {
				if($this->owner->ConfigureLabel) {
					return $this->owner->ConfigureLabel;
				}
				elseif($product = $this->owner->Product()) {
					if($label = $product->owner->CustomConfigureLabel()) {
						return $label;
					}
				}
			}
		}
		return "";
	}

	function ProductQuestionsAnswerFormLink($id = 0){
		return $this->owner->Link("productquestionsanswerselect")."/".$id."/?BackURL=".urlencode(Controller::curr()->Link());
	}

	function ProductQuestions(){
		$product = $this->owner->Product();
		$productQuestions = $product->ProductQuestions();
		$productQuestionsArray = array(0 => 0);
		if($productQuestions && $productQuestions->count()) {
			$productQuestionsArray = $productQuestions->map("ID", "ID");
		}
		$ignoreProductQuestions = $this->owner->IgnoreProductQuestions();
		if($ignoreProductQuestions && $ignoreProductQuestions->count()) {
			foreach($ignoreProductQuestions as $ignoreProductQuestion) {
				unset($productQuestionsArray[$ignoreProductQuestion->ID]);
			}
		}
		$additionalProductQuestions = $this->owner->AdditionalProductQuestions();
		if($additionalProductQuestions && $additionalProductQuestions->count()) {
			foreach($additionalProductQuestions as $additionalProductQuestion) {
				$productQuestionsArray[$additionalProductQuestion->ID] = $additionalProductQuestion->ID;
			}
		}
		if(!count($productQuestionsArray)) {
			$productQuestionsArray = array(0 => 0);
		}
		return DataObject::get("ProductQuestion", "ProductQuestion.ID IN (".implode(",", $productQuestionsArray).")");
	}



}


