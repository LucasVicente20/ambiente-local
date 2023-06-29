<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsComissao.php
# Autor:    Rossana Lira
# Data:     15/05/2003
# Objetivo: Programa de Consulta de Comissões de Licitações
# OBS.:     Tabulação 2 espaços
# Alterado: Carlos Abreu - Substituição de variáveis GET(Pessoa e Email) por SELECT
# Data:     06/09/2006
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/institucional/RotEmailPadrao.php' );

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "ConsComissao.php";

if( $Mens == 0 ) {
		$db     = Conexao();
		$sql    = "SELECT ECOMLIDESC, NCOMLIPRES, ECOMLIMAIL, ECOMLILOCA, ";
		$sql   .= "       ACOMLIFONE, ACOMLINFAX, EGREMPDESC, CCOMLICODI ";
		$sql   .= "  FROM SFPC.TBCOMISSAOLICITACAO A, SFPC.TBGRUPOEMPRESA B ";
		$sql   .= " WHERE A.CGREMPCODI = B.CGREMPCODI AND A.FCOMLISTAT = 'A' ";
		$sql   .= " ORDER BY EGREMPDESC,ECOMLIDESC";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}
		echo "<html>\n";
		# Carrega o layout padrão #
		layout();
		echo "<script language=\"javascript\" type=\"\">\n";
		echo "<!--\n";
		MenuAcesso();
		echo "//-->\n";
		echo "</script>\n";
		echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"../estilo.css\">\n";
		?>
		<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
		<script language="JavaScript" src="../menu.js"></script>
		<script language="JavaScript">Init();</script>
		<?php
		echo "<form action=\"ConsComissao.php\" method=\"post\" name=\"Comissao\">\n";
		echo "<br><br><br><br><br>\n";
		echo "<table cellpadding=\"3\" border=\"0\" summary=\"\">\n";
		echo "  <!-- Caminho -->\n";
		echo "  <tr>\n";
		echo "    <td width=\"150\"><img border=\"0\" src=\"../midia/linha.gif\" alt=\"\"></td>\n";
		echo "    <td align=\"left\" class=\"textonormal\" colspan=\"2\">\n";
		echo "      <font class=\"titulo2\">|</font>\n";
		echo "      <a href=\"../index.php\"><font color=\"#000000\">Página Principal</font></a> > Licitações > Comissão > Consultar\n";
		echo "    </td>\n";
		echo "  </tr>\n";
		echo "  <!-- Fim do Caminho-->\n";
		echo "	<!-- Erro -->\n";
		echo "	<tr>\n";
		echo "	  <td width=\"100\"></td>\n";
		echo "	  <td align=\"left\" colspan=\"2\">\n";
		if ( $Mens == 1 ) { ExibeMens($Mensagem,$Tipo,1); }
		echo "    </td>\n";
		echo "	</tr>\n";
		echo "	<!-- Fim do Erro -->\n";
		echo "	<!-- Corpo -->\n";
		echo "	<tr>\n";
		echo "		<td width=\"100\"></td>\n";
		echo "		<td class=\"textonormal\">\n";
		echo "      <table  border=\"0\" cellspacing=\"0\" cellpadding=\"3\" bgcolor=\"#FFFFFF\" summary=\"\">\n";
		echo "        <tr>\n";
		echo "	      	<td class=\"textonormal\">\n";
		echo "	        	<table border=\"1\" cellpadding=\"3\" cellspacing=\"0\" bordercolor=\"#75ADE6\" summary=\"\" class=\"textonormal\">\n";
		echo "	          	<tr>\n";
		echo "	            	<td align=\"center\" bgcolor=\"#75ADE6\" valign=\"middle\" colspan=\"6\">\n";
		echo "		    					<font class=\"titulo3\">CONSULTA DAS COMISSÕES DE LICITAÇÃO</font>\n";
		echo "		          	</td>\n";
		echo "		        	</tr>\n";
		$GrupoDescricao = "";
		while( $Linha = $result->fetchRow() ){
				if( $GrupoDescricao != $Linha[6] ){
						$GrupoDescricao = $Linha[6];
						echo "					<tr><td align=\"center\" class=\"titulo3\" colspan=\"6\" bgcolor=\"#DCEDF7\">$Linha[6]</td></tr>\n";
						echo "					<tr>\n";
						echo "						<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">COMISSÃO</td>\n";
						echo "						<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">PRESIDENTE</td>\n";
						echo "						<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">E-MAIL</td>\n";
						echo "						<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">LOCALIZAÇÃO</td>\n";
						echo "						<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">TELEFONE</td>\n";
						echo "						<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">FAX </td>\n";
						echo "					</tr>\n";
				}
				echo "						<tr>\n";
				echo "								<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$Linha[0]</td>\n";
				echo "								<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$Linha[1]</td>\n";
				if( $Linha[2]<> "" ){
						$Url = "../institucional/RotEmailPadrao.php?Comissao=$Linha[7]";
						if (!in_array($Url,$_SESSION['GetUrl'])){
								$_SESSION['GetUrl'][] = $Url;
						}
						echo "							<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\"><a href=\"$Url\"><font color=\"#000000\">$Linha[2]</font></a><br></td>\n";
				}else{
						echo "							<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">&nbsp;</td>\n";
				}
				echo "								<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$Linha[3]</td>\n";
				if( $Linha[4]<> "" ){
						echo "							<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$Linha[4]</td>\n";
				}else{
						echo "							<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">&nbsp;</td>\n";
				}
				if( $Linha[5]<> "" ){
						echo "							<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$Linha[5]</td>\n";
				}else{
						echo "							<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">&nbsp;</td>\n";
				}
				echo "						</tr>\n";
		}
		echo "    	  	  </table>\n";
		echo "					</td>\n";
		echo "				</tr>\n";
		echo "      </table>\n";
		echo "		</td>\n";
		echo "	</tr>\n";
		echo "	<!-- Fim do Corpo -->\n";
		echo "</table>\n";
		echo "</form>\n";
		echo "</body>\n";
		echo "</html>\n";
		$db->disconnect();
}
?>
