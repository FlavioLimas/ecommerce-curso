<?php 

use \Hcode\Page;	

/**
 * Rota para o Index do site
 */
$app->get('/', function()
{
    
	$page = new Page();

	// Chamada do template (HTML)
	$page->setTpl("index");

});





 ?>