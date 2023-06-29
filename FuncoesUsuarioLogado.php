<?php


require_once dirname(__FILE__)."/funcoes.php";
// if (!@require_once dirname(__FILE__)."/funcoes.php") {
// 	throw new Exception("Error Processing Request - funcoes", 1);
// }

require_once dirname(__FILE__)."/bootstrap.php";


class FuncoesUsuarioLogado{
	
	public function __construct()
	{
		
	}

    public static function getOrgaoLicitanteCodigo($grupoCodigo, $perfilCorporativo = false)
     {
        
        $criteria = new Criteria();
        $criteria->setFromTable("sfpc.tbgrupoorgao");
        $criteria->setColumnTable("sfpc.tbgrupoorgao",array("corglicodi"));
        $criteria->addWhere("sfpc.tbgrupoorgao","cgrempcodi",$grupoCodigo);
        $criteria->generateSelectSQL();

        $database = self::getConexao();
	    $res = $database->getOne($criteria->getSQL(), $criteria->getValues());
       return (integer) $res;
     }    
	
	public static function getConexao()
	{
	   $conexao = & Conexao();
	   return $conexao;
	}
	 public static function obterOrgaoUsuarioLogado()
	{
		$grupoUsuario = $_SESSION['_cgrempcodi_'];
	
		//Recupera o orgão do usuário logado
		$orgaoUsuario = self::getOrgaoLicitanteCodigo($grupoUsuario);
	
		return 37;
	}
}


