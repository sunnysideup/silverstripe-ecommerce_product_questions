<?php

/**
 * adds functionality to OrderItems
 *
 *
 *
 */

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

	/**
	 *
	 * @return String
	 */
	function ProductQuestionsAnswerNOHTML(){return $this->owner->getProductQuestionsAnswerNOHTML();}
	function getProductQuestionsAnswerNOHTML(){
		return strip_tags($this->owner->ProductQuestionsAnswer);
	}


	/**
	 * returns a link to configure an OrderItem
	 * and adds the relevant requirements
	 * @return String
	 */
	function ConfigureLink() {
		Requirements::javascript("ecommerce_product_questions/javascript/EcomProductQuestions.js");
		if($this->owner->Order()->IsSubmitted()) {
			return "";
		}
		else {
			return $this->owner->ProductQuestionsAnswerFormLink();
		}
	}

	/**
	 *returns the link to edit the products.
	 * @return String
	 */
	function ProductQuestionsAnswerFormLink(){
		if($this->owner->HasProductQuestions()) {
			$buyable = $this->productQuestionBuyable();
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
		if($buyable = $this->owner->productQuestionBuyable()) {
			return $buyable->ProductQuestions();
		}
		return null;
	}

	/**
	 * product relating to an orderItem
	 * @var Product
	 */
	protected static $product_question_product = null;


	/**
	 *
	 * @return Product | Null
	 */
	protected function productQuestionBuyable(){
		if(self::$product_question_product === null) {
			self::$product_question_product = $this->owner->Buyable();
		}
		return self::$product_question_product;
	}

	/**
	 *
	 * @return Form
	 */
	function ProductQuestionsAnswerForm($controller, $name = "productquestionsanswerselect") {
		$productQuestions = $this->owner->ProductQuestions();
		$buyable = $this->productQuestionBuyable();
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
			$values = array();
			if($this->owner->JSONAnswers) {
				$values = @Convert::json2array($this->owner->JSONAnswers);
			}
			foreach($productQuestions as $productQuestion) {
				$value = empty($values[$productQuestion->ID]) ? null : $values[$productQuestion->ID];
				$fields->push($productQuestion->getFieldForProduct($buyable, $value)); //TODO: perhaps use a dropdown instead (elimiates need to use keyboard)
			}
			$actions = new FieldSet(
				array(
					new FormAction('addproductquestionsanswer', _t("ProductQuestion.ANSWER_QUESTION","Update Selection")),
				)
			);
			$validator = new RequiredFields($requiredfields);
			$form = new Form($controller, $name,$fields,$actions,$validator);
			Requirements::themedCSS("Cart");
			return $form;
		}
	}

	/**
	 *
	 * @param Array $answers
	 * 	ID = ProductQuestion.ID
	 * 	"ID" => "Answer" (String)
	 *
	 */
	function updateOrderItemWithProductAnswers($answers, $write = true){
		if($this->owner->canEdit()) {
			$this->owner->ProductQuestionsAnswer = "";
			if(is_array($answers) && count($answers)) {
				foreach($answers as $productQuestionID => $productQuestionAnswer) {
					$question = DataObject::get_by_id("ProductQuestion", intval($productQuestionID));
					if($question) {
						$this->owner->ProductQuestionsAnswer .= "
							<span class=\"productQuestion\">
								<strong class=\"productQuestionsLabel\">".$question->Label."</strong>:
								<em class=\"productQuestionsAnswer\">".$productQuestionAnswer."</em>
							</span>";
					}
					//$form->addErrorMessage("ProductQuestions", $message, $type);
				}
			}
			$this->owner->JSONAnswers = Convert::raw2json($answers);
			if($write) {
				$this->owner->write();
			}
		}
	}

	function onBeforeWrite(){
		if(!empty($this->owner->Parameters)) {
			if(!empty($this->owner->Parameters["productquestions"])){
				$answers = array();
				$params = $this->owner->Parameters["productquestions"];
				$params = urldecode($params);
				$items = explode("|", $params);
				if($items && is_array($items) && count($items)) {

					foreach($items as $item) {
						if($item) {
							$itemArray = explode("=", $item);
							if(is_array($itemArray) && count($itemArray) == 2) {
								$key = intval(str_replace(array("ProductQuestions[", "]"), "", $itemArray[0]));
								$value = convert::raw2sql($itemArray[1]);
								$answers[$key] = $value;
							}
						}
					}
				}
				unset($this->owner->Parameters);
				$this->updateOrderItemWithProductAnswers($answers, false);
			}
		}
	}

}
