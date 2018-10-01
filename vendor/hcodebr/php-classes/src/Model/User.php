<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class User extends Model
{

	const SESSION = "User";

	public static function login($login, $password)
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
				":LOGIN"=>$login
			)
		);	

		if (count($results) === 0) {
			throw new \Exception("Usuário inexistente ou senha inválida.");
		}

		$data = $results[0];

		/**
		 * Verificando se a senha passada como parametro é e mesma do banco de dados
		 */
		if (password_verify($password, $data["despassword"]) === true) {

			$user = new User();

			/**
			 * Criando geters and seters de forma dinamica com metodos magicos
			 */
			$user->setData($data);

			$_SESSION[User::SESSION] = $user->getValues();

			return $user;
		}else {
			throw new \Exception("Usuário inexistente ou senha inválida.");
			
		}

	}

	public static function verifyLogin($inadmin = true)
	{

		if (
			!isset($_SESSION[User::SESSION])
			||
			!$_SESSION[User::SESSION]
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0
			||
			(bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin
		) {
			header("Location: /admin/login");
			exit;
		}

	}

	/**
	 * [logout Efetua logout da area administrativa]
	 * @return [type] [void]
	 */
	public static function logout()
	{

		$_SESSION[User::SESSION] = NULL;

	}

	/**
	 * [listAll Lista todos usuários]
	 * @return [type] [Objeto com todos usuarios]
	 */
	public static function listAll()
	{
		
		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");

	}

	/**
	 * [Salva os registros referente as classes Pessoas e Usuários chamando as respectivas procedures encarregadas de armazenar os valores] 
	 * @return [type] [void]
	 */
	public function save()
	{
		$sql = new Sql();

		$results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
				":desperson"=>$this->getdesperson(),
				":deslogin"=>$this->getdeslogin(),
				":despassword"=>$this->getdespassword(),
				":desemail"=>$this->getdesemail(),
				":nrphone"=>$this->getnrphone(),
				":inadmin"=>$this->getinadmin()
			)
		);

		$this->setData($results[0]);
	}

	public function get($iduser)
	{
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array(
				":iduser"=>$iduser
			)
		);

		$this->setData($results[0]);
	}

	/**
	 * [update Atualiza todos os registros]
	 * @return [type] [description]
	 */
	public function update()
	{

		$sql = new Sql();

		$results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
				":iduser"=>$this->getiduser(),
				":desperson"=>$this->getdesperson(),
				":deslogin"=>$this->getdeslogin(),
				":despassword"=>$this->getdespassword(),
				":desemail"=>$this->getdesemail(),
				":nrphone"=>$this->getnrphone(),
				":inadmin"=>$this->getinadmin()
			)
		);

		$this->setData($results[0]);

	}
		
	/**
	* [delete Deleta registro a partir do ID]
	* 
	*/
	public function delete()
	{
		$sql = new Sql();
		
		$sql->query("CALL sp_users_delete(:iduser)", array(
				":iduser"=>$this->getiduser()
			)
		);
	}

}




 ?>