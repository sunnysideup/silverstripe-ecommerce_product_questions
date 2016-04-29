<?php

/**
 * adds functionality to OrderItems
 *
 *
 *
 */

class ProductQuestion_OrderExtension extends DataExtension {

        function updateSubmitErrors(){
                $array = array();
                foreach($this->owner->OrderItems() as $item) {
                        if(!$item->AllRequiredQuestionsAnswered()) {
                                $txt = _t("ProductQuestion.PROVIDE_MORE_INFORMATION", "Provide more information for");
                                $array[$item->ID] = $txt." <em>".$item->getTableTitle()."</em>";
                        }
                }
                return count($array) ? $array : null;
        }
}
