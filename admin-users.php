<?php 

use \Hcode\PageAdmin;
use \Hcode\Model\User;

/**
 * Rota para listagem de usuarios (List)
 */
$app->get("/admin/users", function()
{

	// Verificar se esta logado
	User::verifyLogin();

	$users = User::listAll();

	$page = new PageAdmin();
	
	// Chama temlpate (HTML)
	$page->setTpl("users", array(
			"users"=>$users
		)
	);

});

/**
 * Rota para criação de usuarios (Create)
 */
$app->get("/admin/users/create", function()
{

	// Verificar se esta logado
	User::verifyLogin();

	$page = new PageAdmin();

	// Chama template (HTML)
	$page->setTpl("users-create");

});

/**
 * Rota para deletar usuario (Delete)
 */
$app->get("/admin/users/:iduser/delete", function($iduser)
{

	// Verificar se esta logado
	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	$user->delete();

	header("Location: /admin/users");
	exit;

});

/**
 * Rota para atualização de usuario (Update)
 */
$app->get("/admin/users/:iduser", function($iduser)
{

	// Verificar se esta logado
	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	$page = new PageAdmin();

	// Chama template (HTML)
	$page->setTpl("users-update", array(
			"user"=>$user->getValues()
		)
	);

});

/**
 * Rota para inserir dados (Insert)
 */
$app->post("/admin/users/create", function()
{

	// Verificar se esta logado
	User::verifyLogin();
	
	$user = new User();

	// Verificando se está checado o inadmin
	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

	$user->setData($_POST);

	$user->save();

	header("Location: /admin/users");
	exit;
});

/**
 * Rota para botão salvar a edição (Save na tela de Update)
 */
$app->post("/admin/users/:iduser", function($iduser)
{

	// Verificar se esta logado
	User::verifyLogin();

	$user = new User();

	// Verificando se está checado o inadmin
	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

	$user->get((int)$iduser);

	$user->setData($_POST);

	$user->update();

	header("Location: /admin/users");
	exit;

});



 ?>