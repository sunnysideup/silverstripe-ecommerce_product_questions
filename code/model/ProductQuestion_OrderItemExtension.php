<?php

/**
 * adds functionality to OrderItems.
 */
class ProductQuestion_OrderItemExtension extends DataExtension
{
    private static $db = array(
        'ProductQuestionsAnswer' => 'HTMLText',
        'JSONAnswers' => 'Text',
    );

    private static $casting = array(
        'ProductQuestionsAnswerNOHTML' => 'Text',
        'ConfigureLabel' => 'Varchar',
        'ConfigureLink' => 'Varchar',
    );

    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldsToTab(
            'Root.Questions',
            array(
                ReadOnlyField::create('ProductQuestionsAnswer', _t('ProductQuestions.ANSWERS', 'Answers')),
                ReadOnlyField::create('JSONAnswers', _t('ProductQuestions.JSON_ANSWERS', 'Answers as JSON')),
            )
        );
        return $fields;
    }

    /**
     * @return bool
     */
    public function AllQuestionsAnswered()
    {
        if ($answers = $this->owner->ProductQuestionsAnswers()) {
            foreach ($answers as $productQuestion) {
                if (!$productQuestion->Answer) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    public function AllRequiredQuestionsAnswered()
    {
        if ($answers = $this->owner->ProductQuestionsAnswers()) {
            foreach ($answers as $productQuestion) {
                if ($productQuestion->AnswerRequired) {
                    if (!$productQuestion->Answer) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * casted variable.
     *
     * @return string
     */
    public function ProductQuestionsAnswerNOHTML()
    {
        return $this->owner->getProductQuestionsAnswerNOHTML();
    }

    public function getProductQuestionsAnswerNOHTML()
    {
        return strip_tags($this->owner->ProductQuestionsAnswer);
    }

    /**
     * can the order item be configured.
     *
     * @return bool
     */
    public function canConfigure()
    {
        if ($this->owner->Order()->IsSubmitted()) {
            return false;
        }

        return true;
    }

    /**
     * can the order item be configured.
     *
     * @return bool
     */
    public function HasRequiredQuestions()
    {
        if ($this->owner->AllRequiredQuestionsAnswered()) {
            return false;
        }

        return true;
    }

    /**
     * returns a link to configure an OrderItem
     * and adds the relevant requirements.
     *
     * @return string
     */
    public function ConfigureLabel()
    {
        Requirements::javascript('ecommerce_product_questions/javascript/EcomProductQuestions.js');

        return $this->owner->ProductQuestionsAnswerFormLabel();
    }

    /**
     * returns a link to configure an OrderItem
     * and adds the relevant requirements.
     *
     * @return string
     */
    public function ConfigureLink()
    {
        Requirements::javascript('ecommerce_product_questions/javascript/EcomProductQuestions.js');

        return $this->owner->ProductQuestionsAnswerFormLink();
    }

    /**
     * returns the link to edit the products.
     *
     * @return string
     */
    public function ProductQuestionsAnswerFormLabel()
    {
        if ($this->owner->HasProductQuestions()) {
            $buyable = $this->productQuestionBuyable();
            if ($buyable) {
                if ($label = $buyable->CustomConfigureLabel()) {
                    return $label;
                }
            }

            return _t('ProductQuestion.CONFIGURE', 'Configure');
        }

        return '';
    }

    /**
     * returns the link to edit the products.
     *
     * @return string
     */
    public function ProductQuestionsAnswerFormLink()
    {
        if ($this->owner->HasProductQuestions()) {
            $buyable = $this->productQuestionBuyable();
            if ($buyable) {
                return $buyable->ProductQuestionsAnswerFormLink($this->owner->ID);
            }
        }

        return '';
    }

    /**
     * cache only!
     *
     * @var array
     */
    private static $_has_product_questions = array();

    /**
     * Does the buyable associated with the orderitem
     * have product questions?
     *
     * @return bool
     */
    public function HasProductQuestions()
    {
        if (!isset(self::$_has_product_questions[$this->owner->ID])) {
            $productQuestions = $this->owner->ProductQuestions();
            if ($productQuestions && $productQuestions->count()) {
                self::$_has_product_questions[$this->owner->ID] = true;
            } else {
                self::$_has_product_questions[$this->owner->ID] = false;
            }
        }

        return self::$_has_product_questions[$this->owner->ID];
    }

    /**
     * cache only!
     *
     * @var array
     */
    private static $_product_questions = array();

    /**
     * @alias for ProductQuestions
     */
    public function ApplicableProductQuestions()
    {
        return $this->ProductQuestions();
    }

    /**
     * @return DataList | Null
     */
    public function ProductQuestions()
    {
        if (!isset(self::$_product_questions[$this->owner->ID])) {
            if ($buyable = $this->owner->productQuestionBuyable()) {
                self::$_product_questions[$this->owner->ID] = $buyable->ApplicableProductQuestions();
            } else {
                self::$_product_questions[$this->owner->ID] = null;
            }
        }

        return self::$_product_questions[$this->owner->ID];
    }

    /**
     * cache only!
     *
     * @var array
     */
    private static $_product_question_product = null;

    /**
     * @return Product | Null
     */
    public function productQuestionBuyable()
    {
        if (!isset(self::$_product_question_product[$this->owner->ID])) {
            self::$_product_question_product[$this->owner->ID] = $this->owner->Buyable();
        }

        return self::$_product_question_product[$this->owner->ID];
    }

    /**
     *
     * @return Form
     */
    public function ProductQuestionsAnswerFormInCheckoutPage()
    {
        return ModelAsController::controller_for($this->owner->Buyable())->ProductQuestionsAnswerForm($this->owner);
    }

    /**
     * @return Form
     */
    public function ProductQuestionsAnswerForm($controller = null, $name = 'productquestionsanswerselect')
    {
        $productQuestions = $this->owner->ProductQuestions();
        $buyable = $this->productQuestionBuyable();
        $backURL = Session::get('BackURL');
        if ($backURL || empty($_GET['BackURL'])) {
            //do nothing
        } else {
            $backURL = $_GET['BackURL'];
        }
        if ($productQuestions && $productQuestions->count()) {
            $requiredfields = array();
            $fields = new FieldList(
                new HiddenField('OrderItemID', 'OrderItemID', $this->owner->ID),
                new HiddenField('BackURL', 'BackURL', $backURL)
            );
            $values = array();
            if ($this->owner->JSONAnswers) {
                $values = json_decode($this->owner->JSONAnswers);
            }
            foreach ($productQuestions as $productQuestion) {
                $value = empty($values->{$productQuestion->ID}) ? null : $values->{$productQuestion->ID};
                $fields->push($productQuestion->getFieldForProduct($buyable, $value)); //TODO: perhaps use a dropdown instead (eliminates need to use keyboard)
            }
            $actions = new FieldList(
                array(
                    new FormAction('addproductquestionsanswer', _t('ProductQuestion.ANSWER_QUESTION', 'Update Selection')),
                )
            );
            $validator = new RequiredFields($requiredfields);
            $form = new Form($controller, $name, $fields, $actions, $validator);
            Requirements::themedCSS('Cart');

            return $form;
        }
    }

    /**
     * @return ArrayList | NULL
     */
    public function ProductQuestionsAnswers()
    {
        if ($this->owner->HasProductQuestions()) {
            $al = new ArrayList();
            $values = json_decode($this->owner->JSONAnswers);
            if ($questions = $this->owner->ProductQuestions()) {
                foreach ($questions as $question) {
                    $newQuestion = clone $question;
                    $answer = empty($values->{$question->ID}) ? null : $values->{$question->ID};
                    if ($answer) {
                        $newQuestion->Answer = $answer;
                    } elseif ($newQuestion->AnswerRequired) {
                        $newQuestion->Answer = null;
                    } else {
                        $newQuestion->Answer = $newQuestion->DefaultAnswer;
                    }
                    $al->push($newQuestion);
                }

                return $al;
            }
        }
    }

    /**
     * @param array $answers
     *                       ID = ProductQuestion.ID
     *                       "ID" => "Answer" (String)
     * @param bool  $write
     */
    public function updateOrderItemWithProductAnswers($answers, $write = true)
    {
        if ($this->owner->canEdit()) {
            $this->owner->ProductQuestionsAnswer = '';
            if (is_array($answers) && count($answers)) {
                foreach ($answers as $productQuestionID => $productQuestionAnswer) {
                    $question = ProductQuestion::get()->byID(intval($productQuestionID));
                    if ($question) {
                    }
                    //$form->addErrorMessage("ProductQuestions", $message, $type);
                }
                $this->owner->ProductQuestionsAnswer = $this->owner->renderWith('ProductQuestionsAnswers')->getValue();
            }
            $this->owner->JSONAnswers = json_encode($answers);
            if ($write) {
                $this->owner->write();
            }
        }
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if (!empty($this->owner->Parameters)) {
            if (!empty($this->owner->Parameters['productquestions'])) {
                $answers = array();
                $params = $this->owner->Parameters['productquestions'];
                $params = urldecode($params);
                $items = explode('|', $params);
                if ($items && is_array($items) && count($items)) {
                    foreach ($items as $item) {
                        if ($item) {
                            $itemArray = explode('=', $item);
                            if (is_array($itemArray) && count($itemArray) == 2) {
                                $key = intval(str_replace(array('ProductQuestions[', ']'), '', $itemArray[0]));
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
