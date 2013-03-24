<?php

class ProductQuestion_OrderItemExtension extends DataObjectDecorator {

	/**
	 * standard SS method
	 * defines additional statistics
	 */
	function extraStatics() {
		return array(
			'db' => array(
				'ProductQuestionsAnswer' => 'HTMLText'
			),
			'casting' => array(
				'ProductQuestionsAnswerNOHTML' => 'Text'
			)
		);
	}

	function ProductQuestionsAnswerNOHTML(){
		return $this->getProductQuestionsAnswerNOHTML();
	}

	function getProductQuestionsAnswerNOHTML(){
		return strip_tags($this->ProductQuestionsAnswer);
	}

	function updateTableSubTitle($value) {
		return "TEST TEST TEST TEST TEST TEST TEST TEST TEST ";
	}

}
