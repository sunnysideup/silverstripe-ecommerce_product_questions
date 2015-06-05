<?php



/**
 * adds functionality to Products
 *
 *
 *
 */
class ProductQuestion_ProductDecorator extends DataExtension {

	private static $db = array('ConfigureLabel' => 'Varchar(50)');

	private static $belongs_many_many = array('ProductQuestions' => 'ProductQuestion');

	function updateCMSFields(FieldList $fields) {
		$productQuestions = ProductQuestion::get();
		if($productQuestions->count()){
			$fields->addFieldToTab("Root.Questions", new TextField("ConfigureLabel", _t("ProductQuestion.CONFIGURE_LINK_LABEL", "Configure link label")));
			$fields->addFieldToTab("Root.Questions",
				$gridField = new CheckboxSetField(
					'ProductQuestions',
					_t("ProductQuestion.PLURAL_NAME", "Product Questions"),
					ProductQuestion::get()->map("ID", "Title")->toArray()
				)
			);
		}
		$fields->addFieldToTab("Root.Questions", new LiteralField("EditProductQuestions", "<h2><a href=\"/admin/product-config/ProductQuestion/\">"._t("ProductQuestion.EDIT_PRODUCT_QUESTIONS", "Edit Product Questions")."</a></h2>"));
		foreach($this->owner->ProductQuestions() as $productQuestion) {
			$fields->addFieldToTab(
				"Root.Questions",
				new LiteralField(
					"EditProductQuestion".$productQuestion->ID,
					"<h5><a href=\"".$productQuestion->CMSEditLink()."\">"._t("ProductQuestion.EDIT", "Edit")." ".$productQuestion->Title."</a></h5>"
				)
			);
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
		if($this->HasProductQuestions()) {
			if($this->owner->ConfigureLabel) {
				return $this->owner->ConfigureLabel;
			}
			else {
				return _t("ProductQuestion.CONFIGURE", "Configure");
			}
		}
	}

	/**
	 * Does this buyable have product questions?
	 * @return Boolean
	 */
	public function HasProductQuestions(){
		if($this->owner->ProductQuestions()) {
			if($this->owner->ProductQuestions()->count()) {
				return true;
			}
		}
		return true;
	}

}

