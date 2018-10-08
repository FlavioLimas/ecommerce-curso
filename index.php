<?php 
/**
 * Iniciando a sessão
 */
session_start();

require_once("vendor/autoload.php");

use \Slim\Slim;

$app = new Slim();

$app->config('debug', true);

/**
 * Rotas
 */
// Rota da home page
require_once("site.php");
// Rotas relacionadas ao Admin (Login e esqueci minha senha)
require_once("admin.php");
// Rotas Admin relacionadas aos Usuários
require_once("admin-users.php");
// Rotas Admin relacionadas as Categorias
require_once("admin-categories.php");
// Rotas Admin relacionadas aos Produtos
require_once("admin-products.php");

// Executa $app
$app->run();

 


 ?>