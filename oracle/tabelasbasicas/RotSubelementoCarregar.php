<?php
#-------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RotSubelementoCarregar.php
# Autor:    Roberta Costa
# Data:     21/12/2004
# Objetivo: Programa que Carrega os Subelementos de Despesa num Array
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET" ){
		$NomePrograma  = urldecode($_GET['NomePrograma']);
}

# Ano Atual do Exercicio #
$Ano = date("Y");

# Abre a Conexão com Oracle #
$db = ConexaoOracle();

# Verificando se existe Unidade Orçamentária integrada para o ano de exercicio #
$sql  = "SELECT CELED1ELE1, CELED2ELE2, CELED3ELE3, CELED4ELE4, CSUBEDELEM, NSUBEDNOME ";
$sql .= "  FROM SPOD.TBSUBELEMENTODESPESA ";
$sql .= " WHERE DEXERCANOR = $Ano";
$sql .= " ORDER BY CELED1ELE1, CELED2ELE2, CELED3ELE3, CELED4ELE4, CSUBEDELEM";
$res  = $db->query($sql);
if( PEAR::isError($res) ){
    $Mensagem = urlencode("Oracle: Erro na Consulta de FORNECEDOR - Linha: ".__LINE__."");
		$Url = "tabelasbasicas/RotSubelementoIntegrar.php?Carrega=S&Mens=1&Tipo=1&Mensagem=$Mensagem";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		RedirecionaPost($Url);
}else{
		while( $Linha = $res->fetchRow() ){
				$Elemento1 = $Linha[0];
				$Elemento2 = $Linha[1];
				$Elemento3 = $Linha[2];
				$Elemento4 = $Linha[3];
				$ElementoM = $Linha[4];
				$SubNome   = $Linha[5];

        if( $Elemento1 != "" and $Elemento2 != "" and $Elemento3 != "" and $Elemento4 != "" and $Elemento5 != "" ){
						$Opcoes = Array("3_3_90_30", "3_3_90_31", "3_3_90_36", "3_3_90_37", "3_3_90_39");
						if( in_array("".$Elemento1."_".$Elemento2."_".$Elemento3."_".$Elemento4."", $Opcoes) ){
								$Subelemento[$Rows] = $Elemento1."_".$Elemento2."_".$Elemento3."_".$Elemento4."_".$ElementoM."_".$SubNome;
								$Conteudo .= $Subelemento[$Rows]."\r\n";

								# Cria o arquivo TXT #
								$Arquivo = "./tmp/subelemento.txt"; //Caminho do arquivo TXT
								if( ! ( file_exists($Arquivo) ) ){
									 # Cria o arquivo TXT #
								   $PathArq = "./tmp/subelemento.txt";
								   if( ! $fp = fopen($PathArq,"w") ) {
					   						$Mensagem = urlencode("Erro ao tentar criar o arquivo");
												$Url = "tabelasbasicas/RotSubelementoIntegrar.php?Carrega=S&Mens=1&Tipo=1&Mensagem=$Mensagem";
												if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
												RedirecionaPost($Url);
								   }
									 fclose($fp);
								}

								# Verifica se o arquivo pode ser aberto #
								if( ! $fp = fopen($Arquivo,"w") ){
										$Mensagem = urlencode("Erro ao tentar abrir o arquivo");
										$Url = "tabelasbasicas/RotSubelementoIntegrar.php?Carrega=S&Mens=1&Tipo=1&Mensagem=$Mensagem";
										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
										RedirecionaPost($Url);
								}else{
										# Verifica se o arquivo é gravável #
										if( is_writable($Arquivo) ){
										   	if( ! $Abrir = fopen($Arquivo, 'a') ){
														$Mensagem = urlencode("Erro ao tentar abrir o arquivo");
														$Url = "tabelasbasicas/RotSubelementoIntegrar.php?Carrega=S&Mens=1&Tipo=1&Mensagem=$Mensagem";
														if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
														RedirecionaPost($Url);
										   	}
										   	if( ! fwrite($Abrir, $Conteudo) ){
														$Mensagem = urlencode("Erro ao tentar escrever no arquivo");
														$Url = "tabelasbasicas/RotSubelementoIntegrar.php?Carrega=S&Mens=1&Tipo=1&Mensagem=$Mensagem";
														if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
														RedirecionaPost($Url);
										   	}
										   	fclose($Abrir);
										}else{
												$Mensagem = urlencode("Erro ao tentar gravar o arquivo");
												$Url = "tabelasbasicas/RotSubelementoIntegrar.php?Carrega=S&Mens=1&Tipo=1&Mensagem=$Mensagem";
												if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
												RedirecionaPost($Url);
										}
								}
								$Rows++;
						}
				}
		}
}
$db->disconnect();

$Url = "tabelasbasicas/RotSubelementoIntegrar.php?Carrega=S";
if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
RedirecionaPost($Url);
?>
