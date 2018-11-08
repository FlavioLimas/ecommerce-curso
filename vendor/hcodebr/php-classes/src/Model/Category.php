<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class Category extends Model
{

	/**
	 * [listAll Lista todas as categorias]
	 * @return [type] [Objeto com todos usuarios]
	 */
	public static function listAll()
	{
		
		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_categories ORDER BY descategory");

	}

	/**
	 * [save Salva as categorias]
	 * @return [type] [description]
	 */
	public function save()
	{

		$sql = new Sql();

		$results = $sql->select("CALL sp_categories_save(:idcategory, :descategory)",
			array(
				":idcategory"=>$this->getidcategory(),
				"descategory"=>$this->getdescategory()
			)
		);

		if (count(results) > 0) {

			// Setando o valor retornado da procedure
			$this->setData($results[0]);
		}

		// Atualizando o footer no front com as inforções alteradas das categorias
		Category::updateFile();

	}

	public function get($idcategory)
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory", [
				":idcategory"=>$idcategory
			]
		);

		if (count(results) > 0) {

			$this->setData($results[0]);

		}
	}

	public function delete()
	{
		$sql = new Sql();

		$sql->query("DELETE FROM tb_categories WHERE idcategory = :idcategory", [
				':idcategory'=>$this->getidcategory()
			]
		);

		// Atualizando o footer no front com as inforções alteradas das categorias
		Category::updateFile();
	}

	/**
	 * [updateFile Atualiza arquivos responsavel por renderizar as categorias no footer do site]
	 * @return [type] [description]
	 */
	public static function updateFile()
	{
		$categories = Category::listAll();

		$html = [];

		foreach ($categories as $row) {
			
			array_push(
				$html,
				'<li><a href="/categories/' . $row["idcategory"] . '">' . $row['descategory'] . '</a></li>'
			);

		}

		// Salvando arquivo implode() = converte de um array para string
		file_put_contents(
			$_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . "categories-menu.html", implode('', $html)
		);
	}

	/**
	 * [getProducts description] Metodo que verifica do banco a listagem de produtos relacionados e os que não são relacionados a categoria
	 * @param  boolean [$reLated recebe os produtos relacionados]
	 * @return [bool]           [Por default true = relacionados]
	 */
	public function getProducts($related = true)
	{

		$sql = new Sql();

		if ($related === true) {
			
			return $sql->select("
				SELECT * FROM TB_PRODUCTS WHERE IDPRODUCT IN (
					SELECT 
				    A.IDPRODUCT
					FROM
						TB_PRODUCTS A
					INNER JOIN
						TB_PRODUCTSCATEGORIES B
					USING(IDPRODUCT)
					WHERE B.IDCATEGORY = :idcategory
				);
			", [
				':idcategory'=>$this->getidcategory()
			]);

		}else{

			return $sql->select("
				
				SELECT * FROM TB_PRODUCTS WHERE IDPRODUCT NOT IN (
					SELECT 
				    A.IDPRODUCT
					FROM
						TB_PRODUCTS A
					INNER JOIN
						TB_PRODUCTSCATEGORIES B
					USING(IDPRODUCT)
					WHERE B.IDCATEGORY = :idcategory
				);

			", [
				':idcategory'=>$this->getidcategory()
			]);

		}

	}

	/**
	 * [addProduct Associar produto da categoria selecionada]
	 * @param [Product] $products [Obj]
	 */
	public function addProduct(Product $product)
	{

		$sql = new Sql();

		$sql->query("INSERT INTO tb_productscategories (idcategory, idproduct) VALUES(:idcategory, :idproduct)", [
				':idcategory'=>$this->getidcategory(),
				':idproduct'=>$product->getidproduct()
			]
		);

	}

	/**
	 * [removeProduct Desassociar produto da categoria selecionada]
	 * @param  Product $product [Objeto estanciado no arquivo de rota admin-categories]
	 * @return [type]           [Objeto]
	 */
	public function removeProduct(Product $product)
	{

		$sql = new Sql();

		$sql->query("DELETE FROM tb_productscategories WHERE idcategory = :idcategory  AND idproduct = :idproduct", [
				'idcategory'=>$this->getidcategory(),
				'idproduct'=>$product->getidproduct()
			]
		);

	}

	/**
	 * [getProductsPage Gerenciamento da paginação da pagina de produtos]
	 * @param  integer $page         [Ordem de eibição da pagina]
	 * @param  integer $itemsPerPage [Quantos itens serão apresentados por página]
	 * @return [Array]                [Lista com todos os produtos; Contagem total dos produtos; Quantidade de paginação que iremos ter]
	 */
	public function getProductsPage($page = 1, $itemsPerPage = 8)
	{

		$start = ($page - 1) * $itemsPerPage;

		$sql = new Sql();

		$results = $sql->select("
			SELECT 
		    SQL_CALC_FOUND_ROWS *
			FROM
			    TB_PRODUCTS A
			INNER JOIN TB_PRODUCTSCATEGORIES B ON A.IDPRODUCT = B.IDPRODUCT
			INNER JOIN TB_CATEGORIES C ON C.IDCATEGORY = B.IDCATEGORY
			WHERE C.IDCATEGORY = :idcategory
			LIMIT $start, $itemsPerPage;
		", [
				':idcategory'=>$this->getidcategory()
			]
		);

		$resultTotal = $sql->select ("SELECT FOUND_ROWS() AS NRTOTAL;");

		return [
			'data'=>Product::checkList($results),
			'total'=>(int)$resultTotal[0]["NRTOTAL"],
			'pages'=>ceil($resultTotal[0]["NRTOTAL"] / $itemsPerPage)
		];

	}

}




 ?>