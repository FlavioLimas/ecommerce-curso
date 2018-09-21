<?php 

namespace Hcode;
class PageAdmin extends Page 
{
	/**
	 * Metodo que sobrescreve o construtor declarado na Classe Pai Page
	 * @param array  $opts    [description] padrão
	 * @param string $tpl_dir [description] altera o caminho de carregamento do template
	 */
	public function __construct($opts = array(), $tpl_dir = "/views/admin/")
	{
		parent::__construct($opts, $tpl_dir);
	}
}



 ?>