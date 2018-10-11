<?php 

use \Hcode\Page;
use \Hcode\Model\Product;

/**
 * Rota para o Index do site
 */
$app->get('/', function(){

	$products = Product::listAll();
    
	$page = new Page();

	// Chamada do template (HTML)
	$page->setTpl("index", 
	[
		'products'=>Product::checkList($products)
	]
	);

});




 ?>