<?php
#---------------------------------------------------------------------------
# Portal da DGCO
# Programa: RotValidaBloqueio.php
# Autor:    Roberta Costa
# Data:     22/12/2004
# Alterado: Álvaro Faria
# Data:     03/07/2006 - Uso do Pear / Mudanças para rodar em Cohab/Varzea / Correções
# Objetivo: Programa de Valida o Bloqueio em SFCO.TBBLOQUEIO
# OBS.:     Tabulação 2 espaços
#---------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET"){
		$NomePrograma         = urldecode($_GET['NomePrograma']);
		$ProgramaOrigem       = urldecode($_GET['ProgramaOrigem']);
		$Bloqueio             = $_GET['Bloqueio'];
		$BloqueiosDot         = $_GET['BloqueiosDot'];
		$Exercicio            = $_GET['Exercicio'];
		$Orgao                = $_GET['Orgao'];
		$Unidade              = $_GET['Unidade'];
		$ExercicioDot         = $_GET['ExercicioDot'];
		$OrgaoDot             = $_GET['OrgaoDot'];
		$UnidadeDot           = $_GET['UnidadeDot'];
		$OrgaoLicitanteCodigo = $_GET['OrgaoLicitanteCodigo'];
		$Processo             = $_GET['Processo'];
		$ProcessoAno          = $_GET['ProcessoAno'];
		$ComissaoCodigo       = $_GET['ComissaoCodigo'];
		$FaseCodigo           = $_GET['FaseCodigo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $BloqueiosDot == "" ){
		if( $Bloqueio != "" ){
				# Conectando no SFCO.TBBLOQUEIO - Oracle #
				$db   = ConexaoOracle();
				$Sql  = "SELECT CFUNCACODI, CPRGORCODI, CSUBPOCODI, CCAPATCODI, ";
				$Sql .= "       APRJATORDE, CELED1ELE1, CELED2ELE2, CELED3ELE3, ";
				$Sql .= "       CELED4ELE4, CFONTERECU, VBLOQUBLOQ, VBLOQUANO1, ";
				$Sql .= "       VBLOQUANO2, VBLOQUANO3, VBLOQUANO4, VBLOQUANO5, ";
				$Sql .= "       FBLOQUHOML ";
				$Sql .= "  FROM SFCO.TBBLOQUEIO";
				$Sql .= " WHERE DEXERCANOR = $Exercicio AND CORGORCODI = $Orgao ";
				$Sql .= "   AND CUNDORCODI = $Unidade AND CDSTBLCODI = 1 ";
				$Sql .= "   AND ABLOQUSEQU = $Bloqueio";
				$res = $db->query($Sql);
				if( PEAR::isError($res) ){
						$db->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $Sql");
						exit;
				}else{
						$Linha = $res->fetchRow();
						$Funcao                = $Linha[0];
						$Subfuncao             = $Linha[1];
						$Programa              = $Linha[2];
						$TipoProjAtiv          = $Linha[3];
						$ProjAtividade         = $Linha[4];
						$Elemento1             = $Linha[5];
						$Elemento2             = $Linha[6];
						$Elemento3             = $Linha[7];
						$Elemento4             = $Linha[8];
						$Fonte                 = $Linha[9];
						$Valor                 = $Linha[10];
						$Valor1                = $Linha[11];
						$Valor2                = $Linha[12];
						$Valor3                = $Linha[13];
						$Valor4                = $Linha[14];
						$Valor5                = $Linha[15];
						$AlteraValorHomologado = $Linha[16];
				}
				$db->disconnect();

				if( $Elemento1 == "" ){
						$Url = "licitacoes/$NomePrograma?ProgramaOrigem=".urlencode($ProgramaOrigem)."&Existe=N&Bloqueio=$Bloqueio&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&Orgao=$Orgao&Unidade=$Unidade&Exercicio=$Exercicio&FaseCodigo=$FaseCodigo";
						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
						RedirecionaPost($Url);
				}else{
						# Soma Valores #
						$Valor = $Valor + $Valor1 + $Valor2 + $Valor3 + $Valor4 + $Valor5;

						# Redireciona para a página que Solicitou a Rotina #
						$Url = "licitacoes/$NomePrograma?ProgramaOrigem=".urlencode($ProgramaOrigem)."&Existe=S&Bloqueio=$Bloqueio&Exercicio=$Exercicio&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo&Orgao=$Orgao&Unidade=$Unidade&Funcao=$Funcao&Subfuncao=$Subfuncao&Programa=$Programa&TipoProjAtiv=$TipoProjAtiv&ProjAtividade=$ProjAtividade&Elemento1=$Elemento1&Elemento2=$Elemento2&Elemento3=$Elemento3&Elemento4=$Elemento4&Fonte=$Fonte&Valor=$Valor&AlteraValorHomologado=$AlteraValorHomologado";
						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
						RedirecionaPost($Url);
				}
		}else{
				$Url = "licitacoes/$NomePrograma?ProgramaOrigem=".urlencode($ProgramaOrigem)."&Existe=S&Bloqueio=$Bloqueio&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo&Orgao=$Orgao&Unidade=$Unidade&Exercicio=$Exercicio";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				RedirecionaPost($Url);
		}
}else{
		$dot       = explode("_",$BloqueiosDot);
		$Exercicio = explode("_",$ExercicioDot);
		$Orgao     = explode("_",$OrgaoDot);
		$Unidade   = explode("_",$UnidadeDot);
		for( $j=1; $j < count($dot);$j++ ){
				# Conectando no SFCO.TBBLOQUEIO - Oracle #
				$db   = ConexaoOracle();
				$Sql  = "SELECT VBLOQUBLOQ, VBLOQUANO1, VBLOQUANO2, VBLOQUANO3, ";
				$Sql .= "       VBLOQUANO4, VBLOQUANO5, FBLOQUHOML ";
				$Sql .= "  FROM SFCO.TBBLOQUEIO";
				$Sql .= " WHERE DEXERCANOR = $Exercicio[$j] AND CORGORCODI = $Orgao[$j] ";
				$Sql .= "   AND CUNDORCODI = $Unidade[$j] AND CDSTBLCODI = 1 ";
				$Sql .= "   AND ABLOQUSEQU = $dot[$j]";
				$res = $db->query($Sql);
				if( PEAR::isError($res) ){
						$db->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $Sql");
						exit;
				}else{
						$Rows                  = $res->numRows();
						$Linha                 = $res->fetchRow();
						$Valor                 = $Linha[0];
						$Valor1                = $Linha[1];
						$Valor2                = $Linha[2];
						$Valor3                = $Linha[3];
						$Valor4                = $Linha[4];
						$Valor5                = $Linha[5];
						$AlteraValorHomologado = $Linha[6];						
				}
				$db->disconnect();
				
				//if( $Cols == 0 ){
				if( $Rows == 0 ){
						$Url = "licitacoes/$NomePrograma?ProgramaOrigem=".urlencode($ProgramaOrigem)."&Existe=N&Bloqueio=$Bloqueio&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&Orgao=$Orgao&Unidade=$Unidade&FaseCodigo=$FaseCodigo";
						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
						RedirecionaPost($Url);
						break; // colocado - ver com Rossana
				}else{
						# Soma Valores #
						$Valor    = $Valor + $Valor1 + $Valor2 + $Valor3 + $Valor4 + $Valor5;
						$ValorBlo = $ValorBlo."_".$Valor;
						$AlteraValorHomologadoBlo = $AlteraValorHomologadoBlo."_".$AlteraValorHomologado;
				}
		}
		$Url = "licitacoes/$NomePrograma?ProgramaOrigem=".urlencode($ProgramaOrigem)."&Existe=S&ValorBlo=$ValorBlo&AlteraValorHomologadoBlo=$AlteraValorHomologadoBlo&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo&FaseCodigo=$FaseCodigo";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		RedirecionaPost($Url);
}
?>
