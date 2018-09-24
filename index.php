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
$app->get('/', function() {
    
	$page = new Page();

	$page->setTpl("index");

});

/**
 * Rota para Admin
 */
$app->get('/admin', function(){

	User::verifyLogin();

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("index");
});

/**
 * Rota para login
 */
$app->get('/admin/login', function(){
	
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("login");
});

/**
 * Rota para autenticação
 */
$app->post('/admin/login', function(){

	// Validando login (usuario e senha)
	User::login($_POST["login"], $_POST["password"]);

	header("Location: /admin");
	exit;

});

$app->get('/admin/logout', function(){

	User::logout();

	header("Location: /admin/login");
	exit;

});

$app->run();

 ?>