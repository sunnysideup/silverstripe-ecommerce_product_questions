<?php



/**
 * adds functionality to Products
 *
 *
 *
 */
class ProductQuestion_ProductVariationsAdditions extends DataExtension
{
    private static $many_many = array(
        'ProductAttributeTypes' => 'ProductAttributeType',
        'ProductAttributeValues' => 'ProductAttributeValue',
        'ProductVariations' => 'ProductVariation'
    );

    public function updateCMSFields(FieldList $fields)
    {
    }

    public function onAfterWrite()
    {
        //go through types to add to variations
        //go through values to add to variations
    }
}
