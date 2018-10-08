<?php 

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Product;

/**
 * Rota para Produtos
 */
$app->get("/admin/products", function(){
	
	// Verificando se o usuario esta logado
	User::verifyLogin();

	$products = Products::listAll();

	$page = new PageAdmin();

	$page->setTpl("products", [
		"products"=>$products
		]
	);


});



 ?>