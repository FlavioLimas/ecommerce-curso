<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

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

}




 ?>