<?php
# -------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RotChecaAtualizacaoCC.php
# Autor:    Álvaro Faria
# Data:     08/02/2006
# OBS.:     Tabulação 2 espaços
# -------------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     03/07/2006
# Objetivo: Checa se houve atualização dos Centros de Custo no
# 			Oracle que ainda não foi gerado no DGCO
# -------------------------------------------------------------------------------
# Alterado : HERALDO BOTELHO
# Data     : 15/04/2013
# Objetivo : Enviar Email também para os administradores do sistema, já que eles são os responsáveis pela sicronização entre as tabelas do CC    
# -------------------------------------------------------------------------------

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET"){
		$Flag = $_GET['Flag'];
}

# Acesso ao arquivo de funções #
if( $Flag == "P" ){
		include "../../funcoes.php";
}else{
		include "/home/wwwdisco1/portal/html/pr/secfinancas/portalcompras/programas/funcoes.php";
}

# Identifica o Programa para Erro de Banco de Dados #
//$ErroPrograma = __FILE__;

# Abre a Conexão com o Oracle #
$dbora = ConexaoOracle();
# Verifica a última atualização de Centros de Custos no Oracle #
$sql  = "SELECT MAX(TESTCPULAT) FROM SFCP.TBESTRUTURACUSTO";
# Roda a Query
$res  = $dbora->query($sql);
if( PEAR::isError($res) ){
		$dbora->disconnect();
		//ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		EmailErroDB("Erro de SQL", "Ocorreu erro de SQL", $res);
		exit;
}else{
		$row  = $res->fetchRow();
	  $DataOracle = DataPost($row[0]);
}
$dbora->disconnect();

# Abre a Conexão com o Postgree #
$db   = Conexao();
# Verifica a última atualização de Centros de Custos no Postgree #
$sqlpost  = "SELECT MAX(TCENPOULAT) FROM SFPC.TBCENTROCUSTOPORTAL";
# Roda a Query
$respost  = $db->query($sqlpost);
if( PEAR::isError($respost) ){
		$db->disconnect();
		//ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlpost");
		EmailErroDB("Erro de SQL", "Ocorreu erro de SQL", $respost);
		exit;
}else{
		$LinhaPost = $respost->fetchRow();
		$DataPost  = substr($LinhaPost[0],0,10);
}
$db->disconnect;

# Compara as datas diariamente, se forem diferentes, envia o e-mail
if ($DataPost < $DataOracle) {
 $today = date("d/m/y  G:i:s");
	 
 $Assunto = "CENTROS DE CUSTO DESATUALIZADO";

 $Texto  = "========================================================\n";
 $Texto .= "                            ATENÇÃO!                    \n";
/// $Texto .= "   	ISTO É APENAS UM TESTE(APENAS NO COHAB.RECIFE)      \n";
 
 $Texto .= "========================================================\n";
 $Texto .= "OS CENTROS DE CUSTO DO PORTAL DE COMPRAS ESTÁ DESATUALIZADO EM\n";  
 $Texto .= "RELAÇÃO AO SOFIN. É NECESSÁRIO GERAR E INTEGRAR OS DADOS NO PORTAL DE COMPRAS.  \n";
 $Texto .=  "\n";
 $Texto .= "DATA ÚLTIMA ATUALIZAÇÃO (FORMATO AAAA/MM/DD):  \n";
 $Texto .= "* NO SOFIN==>$DataOracle \n";
 $Texto .= "* NO PORTAL DE COMPRAS==>$DataPost  \n";
 $Texto .= "======================================================\n";
 EnviaEmailSistema($Assunto, $Texto); 	// envia só para os analistas (suporte do sistema)
 EnviaEmailSistema($Assunto, $Texto, true);  // envia só para os gerentes
 
 } 
 

  
//}
?>
