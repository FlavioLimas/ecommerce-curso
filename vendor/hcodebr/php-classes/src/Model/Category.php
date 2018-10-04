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

		// Setando o valor retornado da procedure
		$this->setData($results[0]);

		// Atualizando o footer no front com as inforções alteradas das categorias
		Category::updateFile();

	}

	public function get($idcategory)
	{

		$sql = new Sql();

		$results = $sql->select("SELECT* FROM tb_categories WHERE idcategory = :idcategory", [
				":idcategory"=>$idcategory
			]
		);

		$this->setData($results[0]);
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

}




 ?>