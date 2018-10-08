<?php 

use \Hcode\PageAdmin;
use \Hcode\Model\User;

/**
 * Rota para Admin
 */
$app->get('/admin', function()
{

// Validandar se esta logado
	User::verifyLogin();

	$page = new PageAdmin();
	
	// Chamada do template (HTML)
	$page->setTpl("index");
});

/**
 * Rota para login
 */
$app->get('/admin/login', function()
{
	
	// Desativando header e o footer da pagina
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	// Chamada do template (HTML)
	$page->setTpl("login");
});


/**
 * Rota para autenticação
 */
$app->post('/admin/login', function()
{

	// Encriptando a senha
	/*$password = password_hash($_POST["password"], PASSWORD_DEFAULT, [
		"cost"=>12
	]);*/
	$password = $_POST["password"];
	
	User::login($_POST["login"], $password);

	// Validando login (usuario e senha)
	// User::login($_POST["login"], $_POST["password"]);

	header("Location: /admin");
	exit;

});

/**
 * Rota para logout
 */
$app->get('/admin/logout', function()
{

	// Chamado logout
	User::logout();

	// Redirect
	header("Location: /admin/login");
	// Para a execução
	exit;

});


/**
 * Rota para esqueci a minha senha
 */
$app->get("/admin/forgot", function(){

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot");

});

/**
 * Rota para pegar o endereço de e-mail que deve ser o destinatario da redefinição da senha
 */
$app->post("/admin/forgot", function(){

	$user = User::getForgot($_POST["email"]);

	header("Location: /admin/forgot/sent");
	exit;

});

/**
 * Rota para enviar o e-mail de recuperação de senha
 */
$app->get("/admin/forgot/sent", function(){

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-sent");

});

/**
 * Rota para validar o rash rgerado e que permite a alteração da senha
 */
$app->get("/admin/forgot/reset", function(){

	// Valida o codigo (rash) que permite o reset senha dentro do periodo de uma hora
	$user = User::validForgotDecrypt($_GET["code"]);

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-reset", array(
			"name"=>$user["desperson"],
			"code"=>$_GET["code"]
		)
	);

});

$app->post("/admin/forgot/reset", function(){

	// Valida o codigo (rash) que permite o reset senha dentro do periodo de uma hora
	$forgot = User::validForgotDecrypt($_POST["code"]);

	// Seta o date time da utilização do rash de redefinição da senha
	User::setForgotUsed($forgot["idrecovery"]);

	$user = new User();

	$user->get((int)$forgot["iduser"]);

	// Encriptando a senha
	$password = password_hash($_POST["password"], PASSWORD_DEFAULT, [
		"cost"=>12
	]);

	$user->setPassword($password);

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-reset-success");

});




 ?>