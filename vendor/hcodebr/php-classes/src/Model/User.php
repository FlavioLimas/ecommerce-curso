<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class User extends Model
{

	const SESSION 			= "User";
	const SECRET 			= "UdemyPhp7_Secret";
	const ERROR 			= "UserError";
	const ERROR_REGISTER 	= "UserErrorRegister";

	public static function getFromSession()
	{
		$user = new User();
	
		if (isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser'] > 0) {

			$user->setData($_SESSION[User::SESSION]);

		}

		return $user;

	}

	public static function checkLogin($inadmin = true)
	{

		if (
			!isset($_SESSION[User::SESSION])
			||
			!$_SESSION[User::SESSION]
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0
		) {
			
			// Não está logado
			return false;

		} else{ // Está logado

			if ($inadmin === true && (bool)$_SESSION[User::SESSION]["inadmin"] === true) {
				
				return true;

			} else if ($inadmin === false){

				return true;

			} else{ // Não está logado

				return false;

			}

		}

	}

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

			// $data['desperson'] = utf8_encode($data['desperson']);

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

		if (!User::checkLogin($inadmin)) {

			if ($inadmin) {
				header("Location: /admin/login");
			}
			if (!$inadmin) {
				header("Location:/login");
			}
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
				":desperson"=>utf8_decode($this->getdesperson()),
				":deslogin"=>$this->getdeslogin(),
				":despassword"=>User::getPasswordHash($this->getdespassword()),
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

		$data = $reuslts[0];

		$data['desperson'] = utf8_encode($data['desperson']);

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
				":desperson"=>utf8_decode($this->getdesperson()),
				":deslogin"=>$this->getdeslogin(),
				":despassword"=>User::getPasswordHash($this->getdespassword()),
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

	public static function getForgot($email, $inadmin = true)
	{

		$sql = new Sql();

		$results = $sql->select("
			SELECT *
			FROM
			    TB_PERSONS a
			INNER JOIN
			    TB_USERS b USING (idperson)
			WHERE
			    a.desemail = :email;
			    ", array(
				":email"=>$email
			)
		);
		/**
		 * Verificando se encoutrou o email na base
		 */
		if (count($results) === 0) {
			
			throw new \Exception("Não foi possível recuperar a senha.");
			
		}else {

			// Recuperando o endereço de email e o IP do usuário
			$data = $results[0];

			// Chamada de dados da tabela para recuperação de senha
			$results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
					":iduser"=>$data["iduser"],
					":desip"=>$_SERVER["REMOTE_ADDR"]
				)
			);
			// Se não retornar nada
			if (count($results2) === 0) {
				throw new \Exception("Não foi possível recuperar a senha.");
			}else{

				// Se retornar
				$dataRecovery = $results2[0];
				/**
				 * [$code Recebe retorno da função que encripta o ID da tabela e gera um rach para enviar via e-mail para recuperção da senha]
				 * @var [type]
				 */
				$code = base64_encode(
					mcrypt_encrypt(
						MCRYPT_RIJNDAEL_128, 
						User::SECRET, // Esse parametro deve ter no minimo 16 caracteres
						$dataRecovery["idrecovery"],
						MCRYPT_MODE_ECB
					)
				);

				// Rota para recuperar a senha do Admin
				if ($inadmin === true) {
					$link = "http://www.ecommerce.com.br/admin/forgot/reset?code=$code";
				} else{
					// Rota para recuperar a senha da loja
					$link = "http://www.ecommerce.com.br/forgot/reset?code=$code";
				}			

				$mailer = new Mailer(
					$data["desemail"], 
					$data["desperson"], 
					"Redefinir Senha da Udemy Store", 
					"forgot", 
					array(
						"name"=>$data["desperson"],
						"link"=>$link
					)
				);

				$mailer->send();

				return $data;

			}

		}

		$this->setData($results[0]);

	}

	public static function validForgotDecrypt($code)
	{

		$idrecovery = mcrypt_decrypt(
			MCRYPT_RIJNDAEL_128,
			User::SECRET,
			base64_decode($code),
			MCRYPT_MODE_ECB
		);

		$sql = new Sql();

		$results = $sql->select("
			SELECT *
			FROM tb_userspasswordsrecoveries a
			INNER JOIN tb_users b USING(iduser)
			INNER JOIN tb_persons c USING(idperson)
			WHERE 
				a.idrecovery = :idrecovery
			AND 
				a.dtrecovery IS NULL
			AND 
				DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();
		", array(
				":idrecovery"=>$idrecovery
			)
		);

		if (count($results) === 0) {
			throw new \Exception("Não foi possível recuperar a senha.");
			
		}else{

			return results[0];

		}

	}

	/**
	 * [setForgotUsed Seta o date time da utilização do rash de redefinição da senha]
	 */
	public static function setForgotUsed($idrecovery){

		$sql = new Sql();

		$sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery", array(
				":idrecovery"=>$idrecovery
			)
		);

	}

	public function setPassword($password){

		$sql = new Sql();

		$sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser", array(
				":password"=>$password,
				":iduser"=>$this->getiduser()
			)
		);

	}

	public static function setError($msg)
	{

		$_SESSION[User::ERROR] = $msg;

	}

	public static function getError()
	{

		$msg = (isset($_SESSION[User::ERROR]) && $_SESSION[User::ERROR]) ? $_SESSION[User::ERROR] : '';

		User::clearError();

		return $msg;

	}

	public static function clearError()
	{

		$_SESSION[User::ERROR] = NULL;

	}

	public static function setErrorRegister($msg)
	{

		$_SESSION[User::ERROR_REGISTER] = $msg;

	}

	public static function getErrorRegister()
	{

		$msg = (isset($_SESSION[User::ERROR_REGISTER]) && $_SESSION[User::ERROR_REGISTER]) ? $_SESSION[User::ERROR_REGISTER] : '';

		User::clearErrorRegister();

		return $msg;

	}

	public static function clearErrorRegister()
	{

		$_SESSION[User::ERROR_REGISTER] = NULL;

	}

	public static function checkLoginExists($Login)
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :deslogin", [
				':deslogin'=>$login
			]
		);

		return (count($results) > 0);

	}

	public static function getPasswordHash($password)
	{

		return password_hash($password, PASSWORD_DEFAULT, ['cost'=>12]);

	}

}




 ?>