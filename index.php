<?php 
/**
 * Iniciando a sessão
 */
session_start();

require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;

$app = new Slim();

$app->config('debug', true);

/**
 * Rota para Index
 */
$app->get('/', function()
{
    
	$page = new Page();

	// Chamada do template (HTML)
	$page->setTpl("index");

});

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

	// Validando login (usuario e senha)
	User::login($_POST["login"], $_POST["password"]);

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
$app->get("admin/users/:iduser/delete", function($iduser)
{

	// Verificar se esta logado
	User::verifyLogin();

});

/**
 * Rota para atualização de usuario (Update)
 */
$app->get("/admin/users/:iduser", function($iduser)
{

	// Verificar se esta logado
	User::verifyLogin();

	$page = new PageAdmin();

	// Chama template (HTML)
	$page->setTpl("users-update");

});

/**
 * Rota para inserir dados (Insert)
 */
$app->post("/admin/users/create", function()
{

	// Verificar se esta logado
	User::verifyLogin();
	
	$user = new User();

	$user->setData($_POST);

	$user->save();

	header("Location: /admin/users");
	exit;
});

/**
 * Rota para salvar a edição (Save Update)
 */
$app->post("/admin/users/:iduser", function($iduser)
{

	// Verificar se esta logado
	User::verifyLogin();

});

// Executa $app
$app->run();

 ?>