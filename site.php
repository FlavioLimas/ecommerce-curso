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

/**
 * Rota que passa o ID da Categoria para pagina dos respectivos produtos
 */
$app->get("/categories/:idcategory", function($idcategory){

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new Page();

	$page->setTpl("category", [
		'category'=>$category->getValues(),
		'products'=>[]
		]
	);

});




 ?>