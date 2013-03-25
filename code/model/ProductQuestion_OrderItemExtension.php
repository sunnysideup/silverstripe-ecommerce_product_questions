<?php

class ProductQuestion_OrderItemExtension extends DataObjectDecorator {

	/**
	 * standard SS method
	 * defines additional statistics
	 */
	function extraStatics() {
		return array(
			'db' => array(
				'ProductQuestionsAnswer' => 'HTMLText',
				'JSONAnswers' => 'Text'
			),
			'casting' => array(
				'ProductQuestionsAnswerNOHTML' => 'Text',
				'ConfigureLink' => 'HTMLText'
			)
		);
	}

	function ProductQuestionsAnswerNOHTML(){
		return $this->owner->getProductQuestionsAnswerNOHTML();
	}

	function getProductQuestionsAnswerNOHTML(){
		return strip_tags($this->owner->ProductQuestionsAnswer);
	}

	function ConfigureLink() {
		Requirements::javascript("ecommerce_product_questions/javascript/EcomProductQuestions.js");
		return $this->owner->ProductQuestionsAnswerFormLink();
	}

	function updateTableSubTitle($value) {
		return $this->updateTableTitle($value);
	}

	/**
	 *
	 * @return String
	 */
	function ProductQuestionsAnswerFormLink(){
		if($this->owner->HasProductQuestions()) {
			$buyable = $this->productQuestionProduct();
			if($buyable) {
				return $buyable->ProductQuestionsAnswerFormLink($this->owner->ID);
			}
		}
		return "";
	}

	protected static $has_product_questions = null;

	/**
	 *
	 * @return Boolean
	 */
	function HasProductQuestions(){
		if(self::$has_product_questions === null) {
			$productQuestions = $this->owner->ProductQuestions();
			if($productQuestions && $productQuestions->count()) {
				self::$has_product_questions = true;
			}
			else {
				self::$has_product_questions = false;
			}
		}
		return self::$has_product_questions;
	}

	/**
	 *
	 * @return DataObjectSet | Null
	 */
	function ProductQuestions(){
		if($buyable = $this->productQuestionProduct()) {
			return $buyable->ProductQuestions();
		}
		return null;
	}

	protected static $product_question_product = null;

	protected function productQuestionProduct(){
		if(self::$$product_question_product === null) {
			self::$$product_question_product = $this->owner->Buyable();
			if(self::$$product_question_product) {
				if(self::$$product_question_product instanceOF ProductVariation) {
					self::$$product_question_product = self::$$product_question_product->Product();
				}
			}
		}
		return self::$$product_question_product;
	}

	/**
	 *
	 * @return Form
	 */
	function ProductQuestionsAnswerForm($controller, $name = "productquestionsanswerselect") {
		$productQuestions = $this->owner->ProductQuestions();
		$buyable = $this->productQuestionProduct();
		$backURL = Session::get("BackURL");
		if($backURL || empty($_GET["BackURL"])) {
			//do nothing
		}
		else {
			$backURL = $_GET["BackURL"];
		}
		if($productQuestions && $productQuestions->count()) {
			$requiredfields = array();
			$fields = new FieldSet(
				new HiddenField("OrderItemID", "OrderItemID", $this->owner->ID),
				new HiddenField("BackURL", "BackURL", $backURL)
			);
			foreach($productQuestions as $productQuestion) {
				$fields->push($productQuestion->getFieldForProduct($buyable)); //TODO: perhaps use a dropdown instead (elimiates need to use keyboard)
			}
			$actions = new FieldSet(
				array(
					new FormAction('addproductquestionsanswer', _t("ProductQuestion.ANSWER_QUESTION","Update Selection")),
				)
			);
			$validator = new RequiredFields($requiredfields);
			$form = new Form($controller, $name,$fields,$actions,$validator);
			Requirements::themedCSS("ProductQuestions");
			return $form;
		}
	}


}
