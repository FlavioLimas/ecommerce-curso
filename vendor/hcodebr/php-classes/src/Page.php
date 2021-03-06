<?php 
// Declaração do namespace
namespace Hcode;

// Chamada do microframework RainTPL
use Rain\Tpl;

class Page {

	private $tpl;
	private $options = [];
	private $defaults = [
		"header"=>true,
		"footer"=>true,
		"data"=>[]
	];

	public function __construct($opts = array(), $tpl_dir = "/views/"){
		/**
		 * [$this->options description]
		 * @var array_merge() O ultimo argumento passado como parametro sobrescreve o primeiro
		 */
		$this->options = array_merge($this->defaults, $opts);

		// config template
		$config = array(
			"tpl_dir"       => $_SERVER["DOCUMENT_ROOT"].$tpl_dir,
			"cache_dir"     => $_SERVER["DOCUMENT_ROOT"]."/views-cache/",
			"debug"         => false // set to false to improve the speed
		);

		Tpl::configure( $config );

		// create the Tpl object
		$this->tpl = new Tpl;

		$this->setData($this->options["data"]);

		if ($this->options["header"] === true) $this->tpl->draw("header");

	}
	/** Percorrendo objeto para setar chave valor dos dados que serão enviados para o template (view)
	 * @data Coleção dos dados que serão enviados array
	 */
	private function setData($data = array())
	{
		foreach ($data as $key => $value) {
			$this->tpl->assign($key, $value);
		}
	}


	/**Função responsável por alimentar o conteúdo da página
	 * @name nome do arquivo responsável por alimentar o conteúdo string
	 * @data Os dados que serão alimentados na página array
	 * @returnHTML O HTML que alimentará a página OBS: que por padrão é false boolean
	 */
	public function setTpl($name, $data = array(), $returnHTML = false){

		$this->setData($data);

		return $this->tpl->draw($name, $returnHTML);

	}
	
	public function __destruct(){
	
	if($this->options["footer"] === true )$this->tpl->draw("footer");
	
	}




}




 ?>
