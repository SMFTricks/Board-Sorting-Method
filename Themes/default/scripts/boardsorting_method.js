/**
 * @package Board Sorting Method
 * @version 1.0
 * @author Diego Andrés <diegoandres_cortes@outlook.com>
 * @copyright Copyright (c) 2022, SMF Tricks
 */

// Get the labels and inputs
let boardsorting_label = document.querySelector('#BoardSortingMethod_label');
let boardsorting_input = document.querySelector('#BoardSortingMethod');
let boardsorting_order_label = document.querySelector('#BoardSortingOrder_label');
let boardsorting_order_input = document.querySelector('#BoardSortingOrder');

// Toggle them if the checkbox is checked
if (bsm_redirect)
{
	boardsorting_label.parentElement.style.display = "none";
	boardsorting_input.parentElement.style.display = "none";
	boardsorting_order_label.parentElement.style.display = "none";
	boardsorting_order_input.parentElement.style.display = "none";
}

// Now toggle the display of the sorting method and order, depending on the redirect checkbox
$("#redirect_enable").click(function() {
	// Check if they toggle checkbox
	if (this.checked)
	{
		boardsorting_label.parentElement.style.display = "none";
		boardsorting_input.parentElement.style.display = "none";
		boardsorting_order_label.parentElement.style.display = "none";
		boardsorting_order_input.parentElement.style.display = "none";
	}
	else
	{
		boardsorting_label.parentElement.style.display = "";
		boardsorting_input.parentElement.style.display = "";
		boardsorting_order_label.parentElement.style.display = "";
		boardsorting_order_input.parentElement.style.display = "";
	}
});