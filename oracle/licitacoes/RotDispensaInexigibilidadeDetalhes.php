<?php
#---------------------------------------------------------------------------
# Portal da DGCO
# Programa: RotDispensaInexigibilidadeDetalhes.php
# Autor:    Álvaro Faria
# Data:     17/01/2006
# Objetivo: Programa de Consulta Dispensa/Inexigibilidade
#---------------------------------------------
# Alterado: Ariston Cordeiro
# Data:     28/12/2010 - Amarrando o select da dispensa/inexigibilidade com o órgão, pois há casos que vem o objeto de outro órgão
#---------------------------------------------
# OBS.:     Tabulação 2 espaços
#---------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET"){
		$Numero                   = $_GET['Numero'];
		$Ano                      = $_GET['Ano'];
		$TipoDisIne               = $_GET['TipoDisIne'];
		$Select                   = $_GET['Select'];
		$Opcao                    = $_GET['Opcao'];
		$Botao                    = $_GET['Botao'];
		$ObjetoP                  = urldecode($_GET['ObjetoP']);
		$Objeto                   = urldecode($_GET['Objeto']);
		$Orgao                    = $_GET['Orgao'];
		$Unidade                  = $_GET['Unidade'];
		$DataIni  								= $_GET['DataIni'];
		$DataFim  								= $_GET['DataFim'];
		$OrgaoUnidadeP            = $_GET['OrgaoUnidadeP'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Dados da licitação
# Abre a Conexão com Oracle #
$dbora = ConexaoOracle();
# Resgata os dados das Dispensas e Inexigibilidades #
if ($Select == 1){
		$sql   = "SELECT LIC.ALICITANOL, LIC.CTPLICCODI, LIC.ALICITLICI, ";                                       // Chaves - Ano, Tipo, Número da licitação
		$sql  .= "       ITE.DEXERCANOR, ITE.CORGORCODI, ITE.CUNDORCODI, ";                                       // Exercício, Código do órgão e Unidade do Órgão
		$sql  .= "       to_char(LIC.DLICITHOML,'YYYY/MM/DD'), to_char(LIC.DLICITVIGE,'YYYY/MM/DD'), ";           // Data de Publicação e Vigência
		$sql  .= "       LIC.XLICITOBJE, ";                                                                       // Objeto
		$sql  .= "       LIC.CLEIFENUME, LIC.CLEIARARTI, LIC.CLEIARINCI, to_char(LEI.DLEIFEDATA,'YYYY/MM/DD'), "; // Lei
		$sql  .= "       LIC.VLICITLICI, '' ";                                                                    // Valor total da licitação
		$sql  .= "  FROM SFCO.TBLICITACAO LIC, ";
		$sql  .= "       SPCS.TBLEI LEI, ";
		$sql  .= "       SFCO.TBITEMLICITACAO ITE ";
		$sql  .= " WHERE LIC.CLEIFENUME = LEI.CLEIFENUME ";                                                       // chave Licitação/Lei
		$sql  .= "   AND LIC.ALICITANOL = ITE.ALICITANOL "; 							                                        // chave Licitação/Item
		$sql  .= "   AND LIC.CTPLICCODI = ITE.CTPLICCODI ";								                                        // chave Licitação/Item
		$sql  .= "   AND LIC.ALICITLICI = ITE.ALICITLICI "; 							                                        // chave Licitação/Item
		$sql  .= "   AND LIC.ALICITANOL = $Ano AND LIC.ALICITLICI = $Numero AND LIC.CTPLICCODI = $TipoDisIne ";   // Dados da Licitação
}elseif($Select == 2) {
		$sql   = "SELECT LIC.DEXERCANOR, LIC.CTPLICCODI, LIC.APRDISSEQ2, ";                                       // Chaves - Ano, Tipo, Número da licitação
		$sql  .= "       LIC.DEXERCANOR, LIC.CORGORCODI, LIC.CUNDORCODI, ";                                       // Exercício, Código do órgão e Unidade do Órgão
		$sql  .= "       to_char(LIC.DPRDISINIC,'YYYY/MM/DD'), to_char(LIC.DPRDISVIGE,'YYYY/MM/DD'), ";           // Data de Publicação e Vigência
		$sql  .= "       LIC.XPRDISOBJE, ";                                                                       // Objeto
		$sql  .= "       LIC.CLEIFENUME, LIC.CLEIARARTI, LIC.CLEIARINCI, to_char(LEI.DLEIFEDATA,'YYYY/MM/DD'), "; // Lei
		$sql  .= "       LIC.VPRDISVALO, LIC.APRDISSEQU ";                                                        // Valor total da licitação
		$sql  .= "  FROM SFCO.TBPROCESSODISPENSA LIC, ";
		$sql  .= "       SPCS.TBLEI LEI ";
		$sql  .= " WHERE LIC.CLEIFENUME = LEI.CLEIFENUME ";                                                       // chave Licitação/Lei
		$sql  .= "   AND LIC.DEXERCANOR = $Ano AND LIC.APRDISSEQ2 = $Numero AND LIC.CTPLICCODI = $TipoDisIne AND LIC.CORGORCODI = $Orgao ";   // Dados da Licitação
}
$res  = $dbora->query($sql);
if( PEAR::isError($res) ){
		$dbora->disconnect();
		ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		exit;
}else{
		$Linha = $res->fetchRow();
		$TipoDisIne     = $Linha[1];
		$NumeroAno      = $Linha[2]."/".$Linha[0];
		$Exercicio      = $Linha[3];
		$Orgao          = $Linha[4];
		$Unidade        = $Linha[5];
		$DataPublicacao = substr($Linha[6],8,2) ."/". substr($Linha[6],5,2) ."/". substr($Linha[6],0,4);
		$DataVigencia   = substr($Linha[7],8,2) ."/". substr($Linha[7],5,2) ."/". substr($Linha[7],0,4);
		$ObjetoDetalhes = $Linha[8];
		$Lei            = $Linha[9];
		$Artigo         = $Linha[10];
		$Inciso         = $Linha[11];
		$DataLei        = substr($Linha[12],8,2) ."/". substr($Linha[12],5,2) ."/". substr($Linha[12],0,4);
		$ValorTotal     = $Linha[13];
		$NumeroSequ     = $Linha[14];
		# Define a descrição do tipo
		$Dispensa = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,28,29,30,39,40,41,42,43,44,45,46,51,52,56,57);
		$Inexigibilidade = array(54,55,60,61,62,63,64,66,67,71,72,73,74,75,76,90);
		if(in_array($TipoDisIne,$Dispensa)){
				$TipoDesc = "DISPENSA";
		}elseif(in_array($TipoDisIne,$Inexigibilidade)){
				$TipoDesc = "INEXIGIBILIDADE";
		}
}
$dbora->disconnect();

# Dados do órgão
$db      = Conexao(); // Conexão com Postgree
$sqlorg  = "SELECT CUNIDOORGA, CUNIDOCODI, EUNIDODESC ";
$sqlorg .= "  FROM SFPC.TBUNIDADEORCAMENTPORTAL ";
$sqlorg .= " WHERE CUNIDOORGA = $Orgao AND CUNIDOCODI = $Unidade ";
//$sqlorg .= "   AND TUNIDOEXER = 2006 ";
$resorg  = $db->query($sqlorg);
if( PEAR::isError($resorg) ){
		$db->disconnect();
		ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlorg");
		exit;
}else{
		$LinhaOrg  = $resorg->fetchRow();
		$OrgaoDesc = $LinhaOrg[2];
}
$db->disconnect;

# Página
echo "<html>\n";
echo "<head>\n";
echo "<script language=\"javascript\">\n";
echo "<!--\n";
echo "function voltar(){\n";
echo "	history.back();\n";
echo "}\n";
echo "//-->\n";
echo "</script>\n";
echo "<title>Portal de Compras - Prefeitura do Recife</title>\n";
echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"../../estilo.css\">\n";
echo "</head>\n";
echo "<body background=\"../../midia/bg.gif\" marginwidth=\"0\" marginheight=\"0\">\n";

echo "<form action=\"RotExibeDispensaInexigibilidade.php\" method=\"post\" name=\"Detalhes\">\n";
echo "<table cellpadding=\"3\" border=\"0\" width=100% summary=\"\">\n";
echo "	<!-- Erro -->\n";
if ( $Mens == 1 ) {
		echo "	<tr>\n";
		echo "	  <td width=\"100\"></td>\n";
		echo "	  <td align=\"left\" colspan=\"2\">\n";
		if ( $Mens == 1 ) { ExibeMens($Mensagem,$Tipo,1); }
		echo "    </td>\n";
		echo "	</tr>\n";
}
echo "	<!-- Fim do Erro -->\n";
echo "	<!-- Corpo -->\n";
echo "	<tr>\n";
echo "	 <td class=\"textonormal\">\n";
echo "    <table border=\"1\" cellspacing=\"0\" cellpadding=\"3\" width=100% bordercolor=\"#75ADE6\" summary=\"\">\n";
echo "     <tr>\n";
echo "	    <td align=\"center\" bgcolor=\"#75ADE6\" valign=\"middle\" colspan=\"2\" class=\"titulo3\">\n";
echo "		   DISPENSA/INEXIGIBILIDADE - DETALHAMENTO\n";
echo "		  </td>\n";
echo "		 </tr>\n";
echo "	   <tr>\n";
echo "	    <td colspan=\"2\" class=\"textonormal\">\n";
echo "	     Para voltar à tela de resultados, selecione o botão \"Voltar\"\n";
echo "		  </td>\n";
echo "		 </tr>\n";
echo "	   <tr>\n";
echo "	    <td class=\"textonormal\" colspan=\"2\" align=\"right\">\n";
echo "			 <input type=\"hidden\" name=\"Botao\" value=\"$Botao\">\n";
echo "			 <input type=\"hidden\" name=\"Opcao\" value=\"$Opcao\">\n";
echo "			 <input type=\"hidden\" name=\"ObjetoP\" value=\"$ObjetoP\">\n";
echo "			 <input type=\"hidden\" name=\"$OrgaoUnidadeP\" value=\"$OrgaoUnidadeP\">\n";
echo "			 <input type=\"hidden\" name=\"DataIni\" value=\"$DataIni\">\n";
echo "			 <input type=\"hidden\" name=\"DataFim\" value=\"$DataFim\">\n";
echo "	     <input type=\"submit\" name=\"Voltar\" value=\"Voltar\" class=\"botao\">\n";
echo "      </td>\n";
echo "		 </tr>\n";
echo "     <tr><td class=\"textonegrito\" color=\"#000000\">Tipo:</td><td align=\"left\" class=\"titulo3\" bgcolor=\"#DCEDF7\">$TipoDesc</td></tr>\n";
echo "     <tr><td class=\"textonegrito\" color=\"#000000\">Órgão:</td><td class=\"titulo2\">$OrgaoDesc</td></tr>\n";
echo "     <tr><td class=\"textonegrito\" color=\"#000000\">Número / Ano:</td><td class=\"titulo3\" color=\"#000000\">$NumeroAno</td></tr>\n";
if ($ObjetoDetalhes) {
		echo "     <tr><td class=\"textonegrito\" color=\"#000000\">Objeto:</td><td bgcolor=\"#F7F7F7\" class=\"textonormal\">".strtoupper2($ObjetoDetalhes)."</td></tr>\n";
}else{
		echo "     <tr><td class=\"textonegrito\" color=\"#000000\">Objeto:</td><td bgcolor=\"#F7F7F7\" class=\"textonormal\">OBJETO NÃO INFORMADO</td></tr>\n";
}
echo "     <tr><td class=\"textonegrito\" color=\"#000000\">Data de Publicação:</td><td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$DataPublicacao</td></tr>\n";
echo "     <tr><td class=\"textonegrito\" color=\"#000000\">Data de Vigência:</td><td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$DataVigencia</td></tr>\n";
echo "     <tr><td class=\"textonegrito\" color=\"#000000\">Fundamentação Legal:</td><td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">Lei: $Lei, Artigo: $Artigo, Inciso: $Inciso, Data da Lei: $DataLei.</td></tr>\n";
# Resgata os fornecedores vencedores #
if ($Select == 1){
		$sql   = "SELECT CRE.CTPCRECODI, CRE.ACREDONUME, CRE.ACREDOCGCC, CRE.ACREDOCPFF, CRE.NCREDONOME, ";
		$sql  .= "       NVL(ITE.QITLICITEM,0), NVL(ITE.QITLICADIT,0), NVL(ITE.VITLICUNIT,0), ITE.AITLICITEM ";
		$sql  .= "  FROM SFCO.TBCREDOR CRE, ";
		$sql  .= "       SFCO.TBITEMLICITACAO ITE ";
		$sql  .= " WHERE CRE.CTPCRECODI = ITE.CTPCRECODI ";
		$sql  .= "   AND CRE.ACREDONUME = ITE.ACREDONUME ";
		$sql  .= "   AND ITE.ALICITANOL = $Ano AND ITE.ALICITLICI = $Numero AND ITE.CTPLICCODI = $TipoDisIne ";
		$sql  .= " ORDER BY CRE.NCREDONOME";
}elseif ($Select == 2) {
		$sql   = "SELECT CRE.CTPCRECODI, CRE.ACREDONUME, CRE.ACREDOCGCC, CRE.ACREDOCPFF, CRE.NCREDONOME, ";
		$sql  .= "       NVL(ITE.QITPRDITEM,0), NVL(ITE.QITPRDADIT,0), NVL(ITE.VITPRDUNIT,0), ITE.AITPRDSEQU ";
		$sql  .= "  FROM SFCO.TBCREDOR CRE, ";
		$sql  .= "       SFCO.TBITEMPROCESSODISP ITE ";
		$sql  .= " WHERE CRE.CTPCRECODI = ITE.CTPCRECODI ";
		$sql  .= "   AND CRE.ACREDONUME = ITE.ACREDONUME ";
		$sql  .= "   AND ITE.DEXERCANOR = $Ano AND ITE.APRDISSEQU = $NumeroSequ ";
		$sql  .= "   AND ITE.CORGORCODI = $Orgao ";
		$sql  .= " ORDER BY CRE.NCREDONOME";
}
$dbora = ConexaoOracle("us_portal", "portal#13", "dbemprel");
$res  = $dbora->query($sql);
if( PEAR::isError($res) ){
		$dbora->disconnect();
		ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		exit;
}else{
		while( $Linha = $res->fetchRow() ){
				$cont++;
				$DispInexDeta[$cont-1] = "$Linha[0]æ$Linha[1]æ$Linha[2]æ$Linha[3]æ$Linha[4]æ$Linha[5]æ$Linha[6]æ$Linha[7]";
				$QuantItem      = $Linha[5];
				$QuantAdit      = $Linha[6];
				$ValorItemUnit  = $Linha[7];
				$ValorTotalDispInex = $ValorTotalDispInex + (($QuantItem + $QuantAdit) * $ValorItemUnit);
		}
		echo "     <tr><td class=\"textonegrito\" color=\"#000000\">Valor Total:</td><td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">R$ ".converte_valor(sprintf("%01.2f",$ValorTotalDispInex))."</td></tr>\n";
		echo "     <tr><td class=\"textonegrito\" color=\"#000000\">Fornecedor(es) vencedor(es):</td>\n";
		echo "		     <td>";
		echo "             <table border=0>";
		for($i=0;$i<count($DispInexDeta);$i++ ){
				$Linha = explode("æ",$DispInexDeta[$i]);
				$CredorCod      = $Linha[0];
				$CredorNume     = $Linha[1];
				$CNPJ           = $Linha[2];
				$CPF            = $Linha[3];
				$FornecedorNome = $Linha[4];
				$QuantItem      = $Linha[5];
				$QuantAdit      = $Linha[6];
				$ValorItemUnit  = $Linha[7];
				$ValorItem      = ($QuantItem + $QuantAdit) * $ValorItemUnit;
//echo "<font face=Arial size=1>($QuantItem + $QuantAdit) X $ValorItemUnit (QITPRDITEM + QITPRDADIT) X VITPRDUNIT = $ValorItem</font><BR>";

				if (  ( ($CredorCod == $CredorCodVerif) and ($CredorNume == $CredorNumeVerif) ) or ( ($CredorCodVerif == null) and ($CredorNumeVerif == null) )  ){
						$ValorItemTotal = $ValorItemTotal + $ValorItem;
				}else{
						$CredorCodVerif  = $CredorCod;
						$CredorNumeVerif = $CredorNume;
						echo "<tr><td class=\"textonormal\" color=\"#000000\">*</td><td class=\"textonormal\" color=\"#000000\">";
						if ($CPF) {
								echo "CPF: $CPF - \n";
						}elseif($CNPJ){
								echo "CNPJ: $CNPJ - \n";
						}
						echo "$FornecedorNome, \n";
						echo "Valor: R$ ".converte_valor(sprintf("%01.2f",$ValorItemTotal))."\n";
						echo "</td></tr>";
						$ValorItemTotal = 0;
				}
		}
		if ($ValorItemTotal != 0) {
				echo "<tr><td class=\"textonormal\" color=\"#000000\">*</td><td class=\"textonormal\" color=\"#000000\">";
				if ($CPF) {
						echo "CPF: ".FormataCPF($CPF)." - \n";
				}elseif($CNPJ){
						echo "CNPJ: ".FormataCNPJ($CNPJ)." - \n";
				}
				echo "$FornecedorNome, \n";
				echo "Valor: R$ ".converte_valor(sprintf("%01.2f",$ValorItemTotal))."\n";
				echo "</td></tr>";
				echo "</table>";
		}
}
$dbora->disconnect();
echo "</td></tr></table>";

echo "</td></tr></table>\n";
echo "</form>\n";
echo "</html>\n";
?>
