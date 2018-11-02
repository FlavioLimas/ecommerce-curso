<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;
use \Hcode\Model\User;
use \Hcode\Model\Product;

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

				if(User::checkLogin(false)){

					$user = User::getFromSession();

					$data['iduser'] = $user->getiduser();

				}

				$cart->setData($data);

				$cart->save();

				$cart->setToSession();

			}

		}

		return $cart;

	}

	public function setToSession()
	{
		$_SESSION[Cart::SESSION] = $this->getValues();
	}

	public function getFromSessionID()
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM TB_CARTS WHERE dessessionid = :dessessionid", [
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

		$results = $sql->select("SELECT * FROM TB_CARTS WHERE idcart = :idcart", [
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

	/**
	 * [addProduct Adicionar produto ao carinho]
	 * @param Product $product [Uma estancia da classe Product]
	 */
	public function addProduct(Product $product)
	{

		$sql = new Sql();

		$sql->query("INSERT INTO tb_cartsproducts (idcart, idproduct) VALUES(:idcart, :idproduct)", [
				':idcart'=>$this->getidcart(),
				':idproduct'=>$product->getidproduct()
			]
		);

		$this->getCalculateTotal();
	}

	/**
	 * [removeProduct Remover produto do carinho]
	 * @param  Product $product [Uma estancia da classe Product]
	 * @param  boolean $all     [Por padrão o parametro false determina que a será excluido 1 produto por vez do carinho, se for false serão excluidos todos os produtos do carinho]
	 */
	public function removeProduct(Product $product, $all = false)
	{

		$sql = new Sql();

		if ($all) {
			
			$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL", [
					':idcart'=>$this->getidcart(),
					':idproduct'=>$product->getidproduct()
				]
			);
		} else{

			$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL LIMIT 1", [
					':idcart'=>$this->getidcart(),
					':idproduct'=>$product->getidproduct()
				]
			);

		}

		$this->getCalculateTotal();

	}

	/**
	 * Listar todos os produtos para o carinho
	 * @return [type] [Lista com todos os produtos]
	 */
	public function getProducts()
	{

		$sql = new Sql();
		
		$rows = $sql->select("
			SELECT b.idproduct, b.desproduct , b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl, COUNT(*) AS nrqtd, SUM(b.vlprice) AS vltotal 
			FROM tb_cartsproducts a 
			INNER JOIN tb_products b ON a.idproduct = b.idproduct 
			WHERE a.idcart = :idcart AND a.dtremoved IS NULL 
			GROUP BY b.idproduct, b.desproduct , b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl 
			ORDER BY b.desproduct
			", [
				':idcart'=>$this->getidcart()
			]
		);

		return Product::checklist($rows);

	}

	public function getProductsTotals()
	{

		$sql = new Sql();

		$results = $sql->select("
			SELECT SUM(vlprice) AS vlprice, SUM(vlwidth) AS vlwidth, SUM(vlheight) AS vlheight, SUM(vllength) AS vllength, SUM(vlweight) AS vlweight, COUNT(*) AS nrqtd
			FROM TB_PRODUCTS A
			INNER JOIN TB_CARTSPRODUCTS B ON A.IDPRODUCT = B.IDPRODUCT
			WHERE B.IDCART = :idcart
			AND B.DTREMOVED IS NULL
		", [
			':idcart'=>$this->getidcart()
			]
		);

		if(count($results) > 0){
			
			return $results[0];

		} else {

			return [];

		}

	}

	/**
	 * [setFreight Serviço (API dos correios) que consulta do valor do frete]
	 * @param [string] $nrzipcode [numero do cep]
	 */
	/*public function setFreight($nrzipcode)
	{

		$nrzipcode = str_replace('-', '', $nrzipcode);

		$totals = $this->getProductsTotals();

		if (totals['nrqtd'] > 0) {
			
			// Função que recebe XML
			simplexml_load_file("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?");
			
		} else {



		}

	}*/

	public function setFreight($nrzipcode)
	{
		$nrzipcode = str_replace('-', '', $nrzipcode);
		$totals = $this->getProductsTotals();
		if ($totals['nrqtd'] > 0) {
			if ($totals['vlheight'] < 2) $totals['vlheight'] = 2;
			if ($totals['vllength'] < 16) $totals['vllength'] = 16;
			$qs = http_build_query([
				'nCdEmpresa'=>'',
				'sDsSenha'=>'',
				'nCdServico'=>'40010',
				'sCepOrigem'=>'09853120',
				'sCepDestino'=>$nrzipcode,
				'nVlPeso'=>$totals['vlweight'],
				'nCdFormato'=>'1',
				'nVlComprimento'=>$totals['vllength'],
				'nVlAltura'=>$totals['vlheight'],
				'nVlLargura'=>$totals['vlwidth'],
				'nVlDiametro'=>'0',
				'sCdMaoPropria'=>'S',
				'nVlValorDeclarado'=>$totals['vlprice'],
				'sCdAvisoRecebimento'=>'S'
			]);
			$xml = simplexml_load_file("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?".$qs);
			$result = $xml->Servicos->cServico;
			if ($result->MsgErro != '') {
				Cart::setMsgError($result->MsgErro);
			} else {
				Cart::clearMsgError();
			}
			$this->setnrdays($result->PrazoEntrega);
			$this->setvlfreight(Cart::formatValueToDecimal($result->Valor));
			$this->setdeszipcode($nrzipcode);
			$this->save();
			return $result;
		} else {
		}
	}

	/*TERMINA*/

	public function updateFreight()
	{

		if ($this->getdeszipcode() != '') {
			
			$this->setFreight($this->getdeszipcode());

		}

	}

	/**
	 * Sobrescrevendo o metodo que passsa os valores para o template TPL e adicionando as informações dos totais
	 */
	public function getValues()
	{

		$this->getCalculateTotal();

		return parent::getValues();

	}

	public function getCalculateTotal()
	{

		$this->updateFreight();

		$totals = $this->getProductsTotals();

		$this->setvlsubtotal($totals['vlprice']);
		$this->setvltotal($totals['vlprice'] + (float)$this->getvlfreight());

	}

}




 ?>