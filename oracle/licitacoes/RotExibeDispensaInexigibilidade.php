<?php
#---------------------------------------------------------------------------
# Portal da DGCO
# Programa: RotExibeDispensaInexigibilidade.php
# Autor:    Álvaro Faria
# Data:     23/01/2006
# Objetivo: Programa de Consulta Dispensa/Inexigibilidade
# Alterado: Rossana Lira
# Data:     01/06/2007 - Correção do e-mail de dgco@ para dlc@
# OBS.:     Tabulação 2 espaços
#---------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/oracle/licitacoes/RotDispensaInexigibilidadeDetalhes.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET"){
		$Botao                    	= $_GET['Botao'];
		$Opcao                    	= $_GET['Opcao'];
		$ObjetoP                   	= urldecode($_GET['ObjetoP']);
		$Orgao                    	= $_GET['Orgao'];
		$Unidade                    = $_GET['Unidade'];
		$OrgaoUnidadeP              = $_GET['OrgaoUnidadeP'];
		$DataIni  									= $_GET['DataIni'];
		$DataFim  									= $_GET['DataFim'];
} else {
		$Botao                    	= $_POST['Botao'];
		$Opcao                    	= $_POST['Opcao'];
		$ObjetoP                   	= $_POST['ObjetoP'];
		$OrgaoUnidadeP              = $_POST['OrgaoUnidadeP'];
		$Orgao                    	= $_POST['Orgao'];
		$Unidade                    = $_POST['Unidade'];
		$DataIni  									= $_POST['DataIni'];
		$DataFim  									= $_POST['DataFim'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Email quando houver erro #
$Mail         = "rossanalira@recife.pe.gov.br,alvarof@recife.pe.gov.br,abreu@recife.pe.gov.br";
$Assunto      = "TESTE - Rotina de Exibição de Dispensa/Inexigibilidade";
$From         = $GLOBALS["EMAIL_FROM"];

if( $Botao == "Pesquisar"){
		# Abre a Conexão com Oracle #
		$dbora = ConexaoOracle();
		# Resgata os dados das Dispensas e Inexigibilidades #
		$sql .= "SELECT LIC.ALICITANOL, LIC.CTPLICCODI, LIC.ALICITLICI, ";                                      // Chaves - Ano, Tipo, Número da licitação
    $sql .= "       ITE.CORGORCODI, ITE.CUNDORCODI, ";                                                      // Código do órgão e Unidade do Órgão
    $sql .= "       to_char(LIC.DLICITHOML,'YYYY'), to_char(LIC.DLICITHOML,'YYYY/MM/DD'), LIC.XLICITOBJE, UNI.NUNDORNOME, '1' "; //Ano de Publicacao, Data de Publicação e Objeto
    $sql .= "  FROM SFCO.TBLICITACAO LIC, SFCO.TBITEMLICITACAO ITE, SPOD.TBUNIDADEORCAMENT UNI ";
    $sql .= " WHERE LIC.ALICITANOL = ITE.ALICITANOL ";
		$sql .= "   AND LIC.CTPLICCODI = ITE.CTPLICCODI ";
		$sql .= "   AND LIC.ALICITLICI = ITE.ALICITLICI ";
		$sql .= "   AND ITE.AITLICITEM = '1' ";
		$sql .= "   AND to_char(LIC.DLICITHOML,'YYYYMMDD') >= $DataIni ";
		$sql .= "   AND to_char(LIC.DLICITHOML,'YYYYMMDD') <= $DataFim ";
		$sql .= "   AND to_char(LIC.DLICITHOML,'YYYYMMDD') <= 20060220 ";
		$sql .= "   AND UNI.CORGORCODI = ITE.CORGORCODI ";
		$sql .= "   AND UNI.CUNDORCODI = ITE.CUNDORCODI ";
		$sql .= "   AND UNI.DEXERCANOR = ITE.DEXERCANOR ";
		# Possíveis filtros da pesquisa
		if ($Opcao) {
				if ($Opcao == 'D'){        // Dispensa
						$sql .= " AND LIC.CTPLICCODI IN(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,28,29,30,39,40,41,42,43,44,45,46,51,52,56,57) ";
				} elseif ($Opcao == 'I') { // Inexigibilidade
						$sql .= " AND LIC.CTPLICCODI IN(54,55,60,61,62,63,64,66,67,71,72,73,74,75,76,90) ";
				}
		} else {
				$sql .= " AND LIC.CTPLICCODI IN(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,28,29,30,39,40,41,42,43,44,45,46,51,52,54,55,56,57,60,61,62,63,64,66,67,71,72,73,74,75,76,90) ";
		}
		if ($ObjetoP) {
				$sql .= " AND LIC.XLICITOBJE LIKE '%".strtoupper2($ObjetoP)."%' ";
		}
		if ($Orgao) {
				$sql .= " AND ITE.CORGORCODI = $Orgao ";
				$sql .= " AND ITE.CUNDORCODI = $Unidade ";
		}
		$sql  .= " UNION ";
    $sql  .= "SELECT LIC.DEXERCANOR, LIC.CTPLICCODI, LIC.APRDISSEQ2, ";                                     // Chaves - Ano, Tipo, Número da licitação
    $sql  .= "       LIC.CORGORCODI, LIC.CUNDORCODI, ";                                                     // Código do órgão e Unidade do Órgão
		$sql  .= "       to_char(LIC.DPRDISINIC,'YYYY'), to_char(LIC.DPRDISINIC,'YYYY/MM/DD'), LIC.XPRDISOBJE, UNI.NUNDORNOME, '2' ";             // Data de Publicação, Ano de Publicacao e Objeto
		$sql  .= "  FROM SFCO.TBPROCESSODISPENSA LIC, SPOD.TBUNIDADEORCAMENT UNI ";
		$sql  .= " WHERE to_char(LIC.DPRDISINIC,'YYYYMMDD') >= $DataIni ";
		$sql  .= "   AND to_char(LIC.DPRDISINIC,'YYYYMMDD') <= $DataFim ";
		$sql  .= "   AND UNI.CORGORCODI = LIC.CORGORCODI ";
		$sql  .= "   AND UNI.CUNDORCODI = LIC.CUNDORCODI ";
		$sql  .= "   AND UNI.DEXERCANOR = LIC.DEXERCANOR ";
		# Possíveis filtros da pesquisa
		if ($Opcao) {
				if ($Opcao == 'D'){        // Dispensa
						$sql .= " AND LIC.CTPLICCODI IN(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,28,29,30,39,40,41,42,43,44,45,46,51,52,56,57) ";
				} elseif ($Opcao == 'I') { // Inexigibilidade
						$sql .= " AND LIC.CTPLICCODI IN(54,55,60,61,62,63,64,66,67,71,72,73,74,75,76,90) ";
				}
		} else {
				$sql .= " AND LIC.CTPLICCODI IN(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,28,29,30,39,40,41,42,43,44,45,46,51,52,54,55,56,57,60,61,62,63,64,66,67,71,72,73,74,75,76,90) ";
		}
		if ($ObjetoP) {
				$sql .= " AND LIC.XPRDISOBJE LIKE '%".strtoupper2($ObjetoP)."%' ";
		}
		if ($Orgao) {
				$sql .= " AND LIC.CORGORCODI = $Orgao ";
				$sql .= " AND LIC.CUNDORCODI = $Unidade ";
		}
		$sql  .= " ORDER BY 6, 9, 1, 3 ";
		$res  = $dbora->query($sql);
		
		if( PEAR::isError($res) ){
				$dbora->disconnect();
				ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				exit;
		}else{
				$disp = array();
				$inex = array();
				while( $cols = $res->fetchRow() ){
						$cont++;
						$Dispensa 			 = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,28,29,30,39,40,41,42,43,44,45,46,51,52,56,57);
						$Inexigibilidade = array(54,55,60,61,62,63,64,66,67,71,72,73,74,75,76,90);
						if(in_array($cols[1],$Dispensa)){
								$disp[$cont-1] = "$cols[0]æ$cols[1]æ$cols[2]æ$cols[3]æ$cols[4]æ$cols[5]æ$cols[6]æ$cols[7]æ$cols[8]æDæ$cols[9]";
						}elseif(in_array($cols[1],$Inexigibilidade)){
								$inex[$cont-1] = "$cols[0]æ$cols[1]æ$cols[2]æ$cols[3]æ$cols[4]æ$cols[5]æ$cols[6]æ$cols[7]æ$cols[8]æIæ$cols[9]";
						}
				}
				$dados = array_merge($disp,$inex);
				$TipoDisIne = "";
				if( $cont != 0 ){
						echo "<html>\n";
						echo "<head>\n";
						echo "<title>Portal de Compras - Prefeitura do Recife</title>\n";
						echo "<script language=\"javascript\">\n";
						echo "<!--\n";
						echo "function voltar(){\n";
						$Url = "".$RedirecionaJanela."licitacoes/ConsDispensaInexigibilidadePesquisar.php";
						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
						echo " window.open('$Url','_top');\n";
						echo "}\n";
						echo "//-->\n";
						echo "</script>\n";
						echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"../../estilo.css\">\n";
						echo "</head>\n";
						echo "<body background=\"../../midia/bg.gif\" marginwidth=\"0\" marginheight=\"0\">\n";
						echo "<form action=\"RotExibeDispensaInexigibilidade.php\" method=\"post\" name=\"Disp\">\n";
						echo "<table cellpadding=\"3\" border=\"0\" summary=\"\">\n";
						echo "	<!-- Corpo -->\n";
						echo "	<tr>\n";
						echo "	 <td class=\"textonormal\">\n";
						echo "	  <table border=\"1\" cellpadding=\"3\" cellspacing=\"0\" bordercolor=\"#75ADE6\" summary=\"\" class=\"textonormal\">\n";
						echo "	   <tr>\n";
						echo "	    <td align=\"center\" bgcolor=\"#75ADE6\" valign=\"middle\" colspan=\"5\" class=\"titulo3\">\n";
						echo "		   DISPENSA/INEXIGIBILIDADE - RESULTADO\n";
						echo "		  </td>\n";
						echo "		 </tr>\n";
						echo "	   <tr>\n";
						echo "	    <td colspan=\"5\" class=\"textonormal\">\n";
						echo "	     Para visualizar mais informações sobre a Dispensa/Inexigibilidade, clique no número do Processo desejado. Para realizar uma nova pesquisa, selecione o botão \"Voltar\"\n";
						echo "		  </td>\n";
						echo "		 </tr>\n";
						echo "	   <tr>\n";
						echo "	    <td class=\"textonormal\" colspan=\"5\" align=\"right\">\n";
						echo "	     <input type=\"button\" name=\"Voltar\" value=\"Voltar\" class=\"botao\" onclick=\"javascript:voltar();\">\n";
						echo "      </td>\n";
						echo "		 </tr>\n";
						for ( $Row = 0 ; $Row < $cont ; $Row++ ){
								$Linha = explode("æ",$dados[$Row]);
								$TipoDisIne = $Linha[1];
								$NumeroAno  = $Linha[2]."/".$Linha[0];
								$DataPublicacao = substr($Linha[6],8,2)."/".substr($Linha[6],5,2)."/".substr($Linha[6],0,4);
								$Objeto    = $Linha[7];
								if ( ($Ano != substr($Linha[6],0,4)) or ($DispOuInex != $Linha[9]) ){
										$Ano         = substr($Linha[6],0,4);
										$DispOuInex  = $Linha[9];
										if ($DispOuInex == "D") {
												$TipoEscreve = "DISPENSA";
										} else {
												$TipoEscreve = "INEXIGIBILIDADE";
										}
										echo "<tr><td align=\"center\" class=\"titulo3\" colspan=\"5\" bgcolor=\"#DCEDF7\">$TipoEscreve - $Ano</td></tr>\n";
										$Orgao = "";
								}
								if( ($Orgao   != $Linha[3]) or ($Unidade != $Linha[4]) ){
										$Orgao     = $Linha[3];
										$Unidade   = $Linha[4];
										$OrgaoDesc = $Linha[8];
										echo "<tr><td align=\"center\" class=\"titulo2\" colspan=\"5\">$OrgaoDesc</td></tr>\n";
										echo "<tr><td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">NÚMERO/ANO</td>\n";
										echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">OBJETO</td>\n";
										echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">DATA DE PUBLICACAO</td>\n";
								}
								echo "<tr>\n";
								$Url  = "RotDispensaInexigibilidadeDetalhes.php?Numero=$Linha[2]&Ano=$Linha[0]&TipoDisIne=$TipoDisIne&";
								$Url .= "Select=$Linha[10]&Opcao=$Opcao&Objeto=".urlencode($Objeto)."&ObjetoP=".urlencode($ObjetoP)."&";
								$Url .= "Orgao=$Orgao&Unidade=$Unidade&";
								$Url .= "DataIni=$DataIni&DataFim=$DataFim&Botao=$Botao&+=$OrgaoUnidadeP";
								if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
								echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\"><a href=\"$Url\"><font color=\"#000000\">$NumeroAno</font></a></td>\n";
								if ($Objeto) {
									echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">".strtoupper2($Objeto)."</td>\n";
								}else{
										echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">OBJETO NÃO INFORMADO</td>\n";
								}
								echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$DataPublicacao</td>\n";
								echo "</tr>\n";
						}
						echo "   </table>\n";
						echo "  </form>\n";
						echo " </body>\n";
						echo "</html>\n";
				}else{
						# Chama o arquivo "ConsDispensaInexigibilidadePesquisar" mostrando não retorno de ocorrências #
						$Mensagem = urlencode("Nenhuma ocorrência foi encontrada");
						$Url = "licitacoes/ConsDispensaInexigibilidadePesquisar.php?Mens=1&Tipo=1&Mensagem=$Mensagem";
						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
						RedirecionaPost($Url);
						exit;
				}
		}
		$dbora->disconnect();
}
?>
