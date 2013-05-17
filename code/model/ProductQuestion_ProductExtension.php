<?php



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
			'db' => array(
				'ConfigureLabel' => 'Varchar(50)'
			),
			'belongs_many_many' => array(
				'ProductQuestions' => 'ProductQuestion'
			)
		);
	}


	function updateCMSFields($fields) {
		$productQuestions = DataObject::get("ProductQuestion");
		if($productQuestions){
			$productQuestionsArray = $productQuestions->map("ID", "FullName");
			$fields->addFieldToTab("Root.Content.Questions", new TextField("ConfigureLabel", "Configure Link Label"));
			$fields->addFieldToTab("Root.Content.Questions", new CheckboxSetField("ProductQuestions", "Additional Questions", $productQuestionsArray));
		}
	}

	function ProductQuestionsAnswerFormLink($id = 0){
		return $this->owner->Link("productquestionsanswerselect")."/".$id."/?BackURL=".urlencode(Controller::curr()->Link());
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
				return _t("ProductQuestion.CONFIGURE", "Configure");
			}
		}
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
