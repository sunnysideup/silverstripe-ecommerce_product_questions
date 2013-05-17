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
			'many_many' => array(
				'IgnoreProductQuestions' => 'ProductQuestion',
				'AdditionalProductQuestions' => 'ProductQuestion'
			)
		);
	}


	function updateCMSFields($fields) {
		$productQuestionsDefault = $this->owner->Product()->ProductQuestions();
		if($productQuestionsDefault){
			$productQuestionsDefaultArray = $productQuestionsDefault->map("ID", "FullName");
			$fields->addFieldToTab("Root.Questions", new CheckboxSetField("IgnoreProductQuestions", "Ignore Questions for this variation", $productQuestionsDefaultArray));
		}
		if(empty($productQuestionsDefaultArray) || count($productQuestionsDefaultArray)) {
			$productQuestionsDefaultArray = array(1 => 1);
		}
		$productQuestionsAdditional = DataObject::get("ProductQuestion", "ProductQuestion.ID NOT IN(".implode(array_flip($productQuestionsDefaultArray)).")");
		if($productQuestionsAdditional){
			$productQuestionsAdditionalArray = $productQuestionsAdditional->map("ID", "FullName");
			$fields->addFieldToTab("Root.Questions", new CheckboxSetField("AdditionalProductQuestions", "Additional Questions for this variation", $productQuestionsAdditionalArray));
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


