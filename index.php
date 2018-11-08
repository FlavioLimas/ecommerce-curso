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
// Funções compartilhadas
require_once("functions.php");
// Rota relacionadas ao Admin (Login e esqueci minha senha)
require_once("admin.php");
// Rota Admin relacionadas aos Usuários
require_once("admin-users.php");
// Rota Admin relacionadas as Categorias
require_once("admin-categories.php");
// Rota Admin relacionadas aos Produtos
require_once("admin-products.php");
// Rota Admin relacionada as Ordens
require_once("admin-orders.php");

// Executa $app
$app->run();

 


 ?>