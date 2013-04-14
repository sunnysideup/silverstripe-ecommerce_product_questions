/**
	* @description:
	* This class provides extra functionality for the
	**/
(function($){
	$(document).ready(
		function() {
			EcomProductQuantity.init();
		}
	);
})(jQuery);

EcomProductQuantity = {

	selectVariationSelector: '.configureLink',

	colorboxDialogOptions: {
		iframe: false
	},


	init: function(){
		jQuery(".configureLink").colorbox(
			EcomProductQuantity.colorboxDialogOptions
		).removeAttr("target");
	}


}




