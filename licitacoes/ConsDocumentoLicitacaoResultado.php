<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsDocumentoLicitacaoResultado.php
# Autor:    Rodrigo Melo
# Data:     18/03/11
# Objetivo: Listar o resultado da pesquisa das documentações dos 
#           processos licitatórios postados no portal
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/licitacoes/ConsDocumentoLicitacaoPesquisar.php' );
AddMenuAcesso( '/licitacoes/ConsDocumentoLicitacaoDetalhes.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$carregaProcesso  = $_POST['carregaProcesso'];
		$Botao            = $_POST['Botao'];
		$Critica          = $_POST['Critica'];
}

if ( $_SESSION['Pesquisar'] == 1 ){
	$Selecao              = $_SESSION['Selecao'];
	$Objeto               = strtoupper2($_SESSION['Objeto']);
	$OrgaoLicitanteCodigo = $_SESSION['OrgaoLicitanteCodigo'];
	$ComissaoCodigo       = $_SESSION['ComissaoCodigo'];
	$ModalidadeCodigo     = $_SESSION['ModalidadeCodigo'];
	$LicitacaoAno         = $_SESSION['LicitacaoAno'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "ConsDocumentoLicitacaoResultado.php";

# Redireciona dados para ConsDocumentoLicitacaoPesquisar.php #
if( $Botao == "Pesquisa" ){
  	header("location: ConsDocumentoLicitacaoPesquisar.php?Selecao=".$Selecao);
  	exit();
}

if( $Botao == "carregaProcesso" ){
	list($_SESSION['GrupoCodigoDet'],$_SESSION['ProcessoDet'],$_SESSION['ProcessoAnoDet'],$_SESSION['ComissaoCodigoDet'],$_SESSION['OrgaoLicitanteCodigoDet']) = explode("-",$carregaProcesso);
  	header("location: ConsDocumentoLicitacaoDetalhes.php");
  	exit();
}

$Mens = 0;
if( $Mens == 0 ) {
		$db   = Conexao();
		$Data = date("Y-m-d");
				
		$sql  = "SELECT C.EGREMPDESC, E.EMODLIDESC, D.ECOMLIDESC, A.CLICPOPROC, ";
		$sql .= "       A.ALICPOANOP, A.CLICPOCODL, A.ALICPOANOL, A.XLICPOOBJE, ";
		$sql .= "       A.TLICPODHAB, B.EORGLIDESC, A.CGREMPCODI, A.CCOMLICODI, ";
		$sql .= "       A.CORGLICODI ";
		$sql .= "  FROM SFPC.TBLICITACAOPORTAL A, SFPC.TBORGAOLICITANTE B, SFPC.TBGRUPOEMPRESA C, ";
		$sql .= "       SFPC.TBCOMISSAOLICITACAO D, SFPC.TBMODALIDADELICITACAO E ";		
		$sql .= " WHERE A.CORGLICODI = B.CORGLICODI AND A.FLICPOSTAT = 'A' ";
		$sql .= "   AND A.CGREMPCODI = C.CGREMPCODI AND A.CCOMLICODI = D.CCOMLICODI ";
		
		if($LicitacaoAno < date('Y')){
			$sql .= "   AND TO_CHAR(A.TLICPODHAB,'YYYY') = '$LicitacaoAno' "; //ANOS ANTERIORES
			$sql .= "   AND A.CMODLICODI = E.CMODLICODI AND (EXTRACT(YEAR FROM A.TLICPODHAB) < EXTRACT(YEAR FROM CURRENT_DATE)) ";
		} else {
			$sql .= "   AND A.CMODLICODI = E.CMODLICODI AND (EXTRACT(YEAR FROM A.TLICPODHAB) = EXTRACT(YEAR FROM CURRENT_DATE)) ";
			$sql .= "   AND A.TLICPODHAB <= '$Data 23:59:59' "; //ANO ATUAL
		}		

		if( $Objeto != "" ){ $sql .= " AND (A.XLICPOOBJE LIKE '%$Objeto%')"; }
		if( $ComissaoCodigo != "" ){ $sql .= " AND A.CCOMLICODI = $ComissaoCodigo "; }
		if( $ModalidadeCodigo != "" ){ $sql .= " AND A.CMODLICODI = $ModalidadeCodigo "; }
		if( $OrgaoLicitanteCodigo != "" ){ $sql .= " AND A.CORGLICODI = $OrgaoLicitanteCodigo "; }

		if($LicitacaoAno < date('Y')){
				$sql .= " ORDER BY C.EGREMPDESC, E.EMODLIDESC, D.ECOMLIDESC, A.ALICPOANOP DESC, A.CLICPOPROC DESC";
		}else{
				$sql .= " ORDER BY C.EGREMPDESC, E.EMODLIDESC, D.ECOMLIDESC, A.ALICPOANOP, A.CLICPOPROC ";
		}
		
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}
		while( $cols = $result->fetchRow() ){
				$cont++;
				$dados[$cont-1] = "$cols[0]_$cols[1]_$cols[2]_$cols[3]_$cols[4]_$cols[5]_$cols[6]_$cols[7]_$cols[8]_$cols[9]_$cols[10]_$cols[11]_$cols[12]";
		}

		$GrupoDescricao = "";
		if( $cont != 0 ){
				echo "<html>\n";
				# Carrega o layout padrão #
				layout();
				echo "<script language=\"javascript\">\n";
				echo "<!--\n";
				echo "function enviar(valor){\n";
				echo "	document.Acomp.Botao.value=valor;\n";
				echo "	document.Acomp.submit();\n";
				echo "}\n";
				echo "function carregaProcesso(valor){\n";
				echo "	document.Acomp.Botao.value='carregaProcesso';\n";
				echo "	document.Acomp.carregaProcesso.value=valor;\n";
				echo "	document.Acomp.submit();\n";
				echo "}\n";
				MenuAcesso();
				echo "//-->\n";
				echo "</script>\n";
				echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"../estilo.css\">\n";
				?>
				<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
				<script language="JavaScript" src="../menu.js"></script>
				<script language="JavaScript">Init();</script>
				<?php
				echo "<form action=\"ConsDocumentoLicitacaoResultado.php\" method=\"post\" name=\"Acomp\">\n";
				echo "<br><br><br><br>\n";
				echo "<table cellpadding=\"3\" border=\"0\" summary=\"\">\n";
				echo "  <!-- Caminho -->\n";
				echo "  <tr>\n";
				echo "    <td width=\"150\"><img border=\"0\" src=\"../midia/linha.gif\" alt=\"\"></td>\n";
				echo "    <td align=\"left\" class=\"textonormal\" colspan=\"2\"><br>\n";
				echo "      <font class=\"titulo2\">|</font>\n";
				echo "      <a href=\"../index.php\"><font color=\"#000000\">Página Principal</font></a> > Licitações > Documento > Auditoria\n";
				echo "    </td>\n";
				echo "  </tr>\n";
				echo "  <!-- Fim do Caminho-->\n";
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
				echo "		<td width=\"100\"></td>\n";
				echo "		<td class=\"textonormal\">\n";
				echo "      <table  border=\"0\" cellspacing=\"0\" cellpadding=\"3\" bgcolor=\"#ffffff\" summary=\"\">\n";
				echo "        <tr>\n";
				echo "	      	<td class=\"textonormal\">\n";
				echo "	        	<table border=\"1\" cellpadding=\"3\" cellspacing=\"0\" bordercolor=\"#75ADE6\" summary=\"\" class=\"textonormal\">\n";
				echo "	          	<tr>\n";
				echo "	            	<td align=\"center\" bgcolor=\"#75ADE6\" valign=\"middle\" colspan=\"5\" class=\"titulo3\">\n";
				echo "		    					CONSULTA DE DOCUMENTOS DE LICITAÇÃO (AUDITORIA)\n";				
				echo "		    					- RESULTADO\n";
				echo "		          	</td>\n";
				echo "		        	</tr>\n";
				echo "	          	<tr>\n";
				echo "	            	<td colspan=\"5\" class=\"textonormal\">\n";
				echo "	        	    		Para visualizar mais informações sobre a Licitação, clique no número do Processo desejado. Para realizar uma nova pesquisa, selecione o botão \"Nova Pesquisa\"\n";
				echo "		          	</td>\n";
				echo "		        	</tr>\n";
				echo "	          	<tr>\n";
				echo "	    	      	<td class=\"textonormal\" colspan=\"5\" align=\"right\">\n";
			  	echo "			            <input type=\"hidden\" name=\"Selecao\" value=\"$Selecao\">\n";
				echo "	          			<input type=\"button\" name=\"Pesquisa\" value=\"Nova Pesquisa\" class=\"botao\" onclick=\"javascript:enviar('Pesquisa');\">\n";
			  	echo "			            <input type=\"hidden\" name=\"Botao\" value=\"\">\n";
			  	echo "			            <input type=\"hidden\" name=\"carregaProcesso\" value=\"\">\n";
				echo "          			</td>\n";
				echo "		        	</tr>\n";
				for ( $Row = 0 ; $Row < $cont ; $Row++ ){
						$Linha = explode("_",$dados[$Row]);
						if( $GrupoDescricao != $Linha[0] ){
								$GrupoDescricao = $Linha[0];
								echo "<tr><td align=\"center\" class=\"titulo3\" colspan=\"5\" bgcolor=\"#DCEDF7\">$GrupoDescricao</td></tr>\n";
								$ModalidadeDescricao = "";
						}
						if( $ModalidadeDescricao != $Linha[1] ){
								$ModalidadeDescricao = $Linha[1];
								echo "<tr><td align=\"center\" class=\"titulo3\" colspan=\"5\">$ModalidadeDescricao</td></tr>\n";
								$ComissaoDescricao = "";
						}
						if( $ComissaoDescricao != $Linha[2] ){
								$ComissaoDescricao = $Linha[2];
								echo "<tr><td class=\"titulo2\" colspan=\"5\" color=\"#000000\">$ComissaoDescricao</td></tr>\n";
								echo "<tr><td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">PROCESSO</td>\n";
								echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">LICITAÇÃO</td>\n";
								echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">OBJETO</td>\n";
								echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">DATA/HORA ABERTURA</td>\n";
								echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">ÓRGÃO LICITANTE</td></tr>\n";
						}
						$NProcesso  = substr($Linha[3] + 10000,1);
						$NLicitacao = substr($Linha[4] + 10000,1);
						$Linha[5]   = substr($Linha[5] + 10000,1);
						$Linha[6]   = substr($Linha[6] + 10000,1);
						$LicitacaoDtAbertura = substr($Linha[8],8,2) ."/". substr($Linha[8],5,2) ."/". substr($Linha[8],0,4);
						$LicitacaoHoraAbertura = substr($Linha[8],11,5);
						echo "<tr>\n";
						$Url = $Linha[10]."-".$Linha[3]."-".$Linha[4]."-".$Linha[11]."-".$Linha[12];
						echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\"><a href=\"javascript:carregaProcesso('$Url');\"><font color=\"#000000\"><u>$NProcesso/$Linha[4]</u></font></a></td>\n";
						echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$Linha[5]/$Linha[6]</td>\n";
						echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$Linha[7]</td>\n";
						echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$LicitacaoDtAbertura<br>$LicitacaoHoraAbertura h</td>\n";
						echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$Linha[9]</td>\n";
						echo "</tr>\n";
					}
		}else{
		    # Envia mensagem para página selecionar #
		    $_SESSION['Mensagem']             = "Nenhuma ocorrência foi encontrada";
		    $_SESSION['Mens']                 = 1;
		    $_SESSION['Tipo']                 = 1;
		    $_SESSION['Objeto']               = $Objeto;
		    $_SESSION['OrgaoLicitanteCodigo'] = $OrgaoLicitanteCodigo;
		    $_SESSION['ComissaoCodigo']       = $ComissaoCodigo;
		    $_SESSION['ModalidadeCodigo']     = $ModalidadeCodigo;
		    $_SESSION['Selecao']              = $Selecao;
		    $_SESSION['RetornoPesquisa']      = 1;
		    header("location: ConsDocumentoLicitacaoPesquisar.php");
		    exit();
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
