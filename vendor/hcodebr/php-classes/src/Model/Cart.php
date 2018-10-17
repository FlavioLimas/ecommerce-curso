<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class Cart extends Model
{
	// Armazenamento da sessão
	const SESSION = "Cart";

	/**
	 * [getFromSession Verifica se já existe uma sessão se não existe cria uma; recupera os dados do carinho e carrega no front]
	 * @return [void] [description]
	 */
	public static function getFromSession()
	{

		$cart = new Cart();

		if (isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]['idcart'] > 0) {
			$cart->get((int)$_SESSION[Cart::SESSION]['idcart']);
		} else{

			$cart->getFromSessionID();

			if (!(int)$cart->getidcart() > 0) {
				
				$data = [
					'dessessionid'=>session_id()
				];

				$user = User::getFromSession();
			}

		}

		return $cart;

	}

	public function getFromSessionID()
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM TB_CARTS WHERE IDCART = :idcart", [
				':dessessionid'=>session_id()
			]
		);

		if (count($results) > 0) {
		
			$this->setData($results[0]);
			
		}

	}

	public function get(int $idcart)
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM TB_CARTS WHERE IDCART = :idcart", [
				':idcart'=>$idcart
			]
		);

		if (count($results) > 0) {

			$this->setData($results[0]);

		}

	}

	/**
	 * [save Salvar produto no carrinho de compras]
	 * @return [type] [description]
	 */
	public function save()
	{

		$sql = new Sql();
		$results = $sql->select("CALL sp_carts_save(
				:idcart,
				:dessessionid,
				:iduser,
				:deszipcode,
				:vlfreight,
				:nrdays
			)", [
				':idcart'=>$this->getidcart(),
				':dessessionid'=>$this->getdessessionid(),
				':iduser'=>$this->getiduser(),
				':deszipcode'=>$this->getdeszipcode(),
				':vlfreight'=>$this->getvlfreight(),
				':nrdays'=>$this->getnrdays()
			]
		);

		$this->setData($results[0]);
	}

}




 ?>