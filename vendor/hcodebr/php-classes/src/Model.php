<?php 
/**
 * Criando geters and seters de forma dinamica com metodos magicos
 */
namespace Hcode;

class Model
{
	// Todos os campos do objeto
	private $values = [];

	/**
	 * [__call Identificar toda vez que um metodo é chamado] 
	 * @param  [String] $name [Nome do metodo]
	 * @param  [Undefyned] $args [argumentos passados]
	 * @return [Undefyned]       [Retorna o valor se o metodo for get]
	 */
	public function __call($name, $args){

		$method = substr($name, 0, 3);
		$fieldName = substr($name, 3, strlen($name));

		switch ($method){

			case "get":
				return (isset($this->values[$fieldName])) ? $this->values[$fieldName] : null;
			break;

			case "set":
				$this->values[$fieldName] = $args[0];
			break;
		}

	}
	public function setData($data = array()){

		foreach ($data as $key => $value) {
			
			$this->{"set".$key}($value);

		}
	}

	public function getValues(){

		return $this->values;

	}

}


	
 ?>