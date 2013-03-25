<?php

// __________________________________ START ECOMMERCE PRODUCT QUESTIONS MODULE CONFIG __________________________________
/**** MUST SET ****/
//Object::add_extension("Product_Controller", "ProductQuestion_ProductDecorator");
//Object::add_extension("OrderItem", "ProductQuestion_OrderItemExtension");

/***** HIGHLY RECOMMENDED ****
ecommerce.yaml:
ProductsAndGroupsModelAdmin:
	managed_models: [
		...
		ProductQuestion
	]
*/
// __________________________________ END ECOMMERCE PRODUCT QUESTIONS MODULE CONFIG __________________________________
