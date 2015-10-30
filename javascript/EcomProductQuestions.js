/**
	* @description:
	* This class provides extra functionality for the
	**/
(function($){
	$(document).ready(
		function() {
			EcomProductQuestions.init();
		}
	);
})(jQuery);

EcomProductQuestions = {

	selectVariationSelector: '.configureLink',

	colorboxDialogOptions: {
		iframe: false,
		loadingClass: "loading",
		onComplete: function (event) {
			EcomCart.reinit();
		},
		title: function(){
			return jQuery(this).text();
		}
	},


	init: function(){
		jQuery(".configureLink").colorbox(
			EcomProductQuestions.colorboxDialogOptions
		).removeAttr("target");
	}


}




