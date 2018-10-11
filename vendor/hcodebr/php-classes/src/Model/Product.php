<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class Product extends Model
{

	/**
	 * [listAll Lista todas as categorias]
	 * @return [type] [Objeto com todos usuarios]
	 */
	public static function listAll()
	{
		
		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_products ORDER BY desproduct");

	}

	public static function checkList($list)
	{

		foreach ($list as &$row) {
			
			$p = new Product();
			$p->setData($row);
			$row = $p->getValues();
		}

		return $list;

	}

	/**
	 * [save Salva as categorias]
	 * @return [type] [description]
	 */
	public function save()
	{

		$sql = new Sql();

		$results = $sql->select("CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl)",
			array(
				":idproduct"=>$this->getidproduct(),
				":desproduct"=>$this->getdesproduct(),
				":vlprice"=>$this->getvlprice(),
				":vlwidth"=>$this->getvlwidth(),
				":vlheight"=>$this->getvlheight(),
				":vllength"=>$this->getvllength(),
				":vlweight"=>$this->getvlweight(),
				":desurl"=>$this->getdesurl()
			)
		);

		// Setando o valor retornado da procedure
		$this->setData($results[0]);

	}

	public function get($idproduct)
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct", [
				":idproduct"=>$idproduct
			]
		);

		$this->setData($results[0]);
	}

	public function delete()
	{
		$sql = new Sql();

		$sql->query("DELETE FROM tb_products WHERE idproduct = :idproduct", [
				':idproduct'=>$this->getidproduct()
			]
		);

	}

	/**
	 * [checkPhoto description Verifica se exite uma foto ou não no caminho expecifico]
	 * @return [string] [description] caminho do arquivo
	 */
	public function checkPhoto()
	{

		if (
			file_exists(
				$_SERVER['DOCUMENT_ROOT'].
				DIRECTORY_SEPARATOR.
				"res".
				DIRECTORY_SEPARATOR.
				"site".
				DIRECTORY_SEPARATOR.
				"img".
				DIRECTORY_SEPARATOR.
				"products".
				DIRECTORY_SEPARATOR.
				$this->getidproduct().
				".jpg"
			)
		) {
			
			$url = "/res/site/img/products/" . $this->getidproduct() . ".jpg";
		} else {
			// Se não tiver nenhuma foto cadastrada retornará uma foto padrão
			$url = "/res/site/img/products/product.jpg";
		}

		$this->setdesphoto($url);

	}

	/**
	 * [getValues description] Sobreescrevendo metodo getValues sómente para classe Product para checar se a foto exite 
	 * @return [Data] [description] dados da banco
	 */
	public function getValues()
	{

		// Verificar se tem uma foto ou não
		$this->checkPhoto();

		// parent permite referenciar ao metodo original escrito na classe pai (Model) para que assim possa ser rescrito nessa classe
		$values = parent::getValues();

		return $values;

	}

	/**
	 * [setPhoto description] Efetuando upload do arquivo para o server
	 * @param [type] $file [description] imagem para upload
	 */
	public function setPhoto($file)
	{

		// Detectando a extensão do arquivo, convertendo em array e separando a partir do ponto
		$extension = explode('.', $file['name']);
		// A extensão é a umtima posição do array
		$extension = end($extension);
		/**
		 * Criando imagem .jpg idenpedente da extensão do arquivo enviado
		 */
		switch ($extension){

			case "jpg":
			case "jpeg":
			$image = imagecreatefromjpeg($file["tmp_name"]);
			break;

			case "gif":
			$image = imagecreatefromgif($file["tmp_name"]);
			break;

			case "png":
			$image = imagecreatefrompng($file["tmp_name"]);
			break;

		}

		$dist = $_SERVER['DOCUMENT_ROOT'] .
				DIRECTORY_SEPARATOR .
				"res" .
				DIRECTORY_SEPARATOR .
				"site" .
				DIRECTORY_SEPARATOR .
				"img" .
				DIRECTORY_SEPARATOR .
				"products" .
				DIRECTORY_SEPARATOR .
				$this->getidproduct() .
				".jpg";

		imagejpeg($image, $dist);

		imagedestroy($image);

		$this->checkPhoto();

	}

}




 ?>