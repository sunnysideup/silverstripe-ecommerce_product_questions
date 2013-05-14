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
				'IgnoreProductQuestions' => 'ProductQuestion'
				'AdditionalProductQuestions' => 'ProductQuestion'
			)
		);
	}


	function updateCMSFields($fields) {
		$productQuestions = $this->Product()->ProductQuestions();
		if($productQuestions){
			$productQuestionsArray = $productQuestions->map("ID", "FullName");
			$fields->addFieldToTab("Root.Content.Questions", new CheckboxSetField("IgnoreProductQuestions", "Ignore Questions for this variation", $productQuestionsArray));
		}
		$productQuestions = DataObject::get("ProductQuestion");
		if($productQuestions){
			$productQuestionsArray = $productQuestions->map("ID", "FullName");
			$fields->addFieldToTab("Root.Content.Questions", new CheckboxSetField("AdditionalProductQuestions", "Additional Questions for this variation", $productQuestionsArray));
		}
	}

	function ProductQuestionsAnswerFormLink($id = 0){
		return $this->owner->Link("productquestionsanswerselect")."/".$id."/?BackURL=".urlencode(Controller::curr()->Link());
	}

	function ProductQuestions(){
		$product = $this->Product();
		$productQuestions = $product->ProductQuestions();
		$productQuestionsArray = array();
		if($productQuestions) {
			$productQuestionsArray = $productQuestions->map("ID", "ID");
		}
		$ignoreProductQuestions = $this->IgnoreProductQuestions();
		if($ignoreProductQuestions && $ignoreProductQuestions->count()) {
			foreach($ignoreProductQuestions as $ignoreProductQuestion) {
				unset($productQuestionsArray[$ignoreProductQuestion->ID]);
			}
		}
		$additionalProductQuestions = $this->AdditionalProductQuestions();
		if($additionalProductQuestions && $additionalProductQuestions->count()) {
			foreach($additionalProductQuestions as $additionalProductQuestion) {
				$productQuestionsArray[$additionalProductQuestion->ID] = $additionalProductQuestion->ID;
			}
		}
		return DataObject::get("ProductQuestion", "ProductQuestion.ID IN (".implode(",", $productQuestionsArray).")");
	}

}


