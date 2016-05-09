<?php



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
    private static $allowed_actions = array(
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
     * @param OrderItem (optional)
     * @return Form
     */
    function ProductQuestionsAnswerForm($orderItem = null){
        //the second part (instanceof) is important, because otherwise it becomes a request ...
        if($orderItem && $orderItem instanceof OrderItem) {
            $this->productQuestionOrderItem = $orderItem;
        } else {
            $this->getProductQuestionOrderItem();
        }
        if($this->productQuestionOrderItem) {
            return $this->productQuestionOrderItem->owner->ProductQuestionsAnswerForm($this->owner, $name = "ProductQuestionsAnswerForm");
        }
        else {
            user_error("could not submit...");
        }
    }

    /**
     * returns the fields from the form
     * @return FieldSet
     */
    function ProductQuestionsAnswerFormFields(){
        $fieldSet = new FieldList();
        $product = $this->owner->dataRecord;
        $productQuestions = $product->ApplicableProductQuestions();
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
        if(isset($data["BackURL"]) && $data["BackURL"]){
            $this->owner->redirect($data["BackURL"]);
        }
        else {
            $this->owner->redirectBack();
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
            $this->productQuestionOrderItem = OrderItem::get()->byID($id);
        }
        if( ! $this->productQuestionOrderItem) {
            user_error("NO this->productQuestionOrderItem specified");
        }
        return $this->productQuestionOrderItem;
    }

}
