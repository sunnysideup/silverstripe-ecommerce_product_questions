<?php



/**
 * adds functionality to Products
 *
 *
 *
 */
class ProductQuestion_ProductAttributeTypes extends DataExtension
{

    private static $many_many = array(
        'ProductQuestions' => 'ProductQuestion'
    );

    public function updateCMSFields(FieldList $fields)
    {
    }

    public function onAfterWrite()
    {
        foreach ($this->ProductQuestions() as $question) {
            $question->write();
        }
    }
}
