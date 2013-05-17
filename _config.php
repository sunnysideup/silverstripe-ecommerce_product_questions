<?php

// __________________________________ START ECOMMERCE PRODUCT QUESTIONS MODULE CONFIG __________________________________
//**** MUST SET
//Object::add_extension("Product", "ProductQuestion_ProductDecorator");
//Object::add_extension("ProductPage_Controller", "ProductQuestion_ProductControllerDecorator");
//Object::add_extension("OrderItem", "ProductQuestion_OrderItemExtension");
//**** MAY SET
//Object::add_extension("ProductVariation", "ProductQuestion_ProductVariationDecorator");


/***** HIGHLY RECOMMENDED ****
ecommerce.yaml:
ProductsAndGroupsModelAdmin:
	managed_models: [
		...
		ProductQuestion
	]
*/
// __________________________________ END ECOMMERCE PRODUCT QUESTIONS MODULE CONFIG __________________________________
