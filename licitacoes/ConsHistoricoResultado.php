<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsHistoricoResultado.php
# Autor:    Rossana Lira
# Data:     06/05/03
# Objetivo: Programa de Resultado do Historico de Licitações
# Alterado: Carlos Abreu
# Data:     20/02/2007 - Colocar ano como argumento da pesquisa para reduzir estouro da variável de sessão
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/licitacoes/ConsHistoricoPesquisar.php' );
AddMenuAcesso( '/licitacoes/ConsHistoricoDetalhes.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao                = $_POST['Botao'];
		$Critica              = $_POST['Critica'];
		$Selecao              = $_POST['Selecao'];
		$Objeto               = strtoupper2($_POST['Objeto']);
		$OrgaoLicitanteCodigo = $_POST['OrgaoLicitanteCodigo'];
		$ComissaoCodigo       = $_POST['ComissaoCodigo'];
		$ModalidadeCodigo     = $_POST['ModalidadeCodigo'];
		$LicitacaoAno         = $_POST['LicitacaoAno'];
		$TipoItemLicitacao    = $_POST['TipoItemLicitacao'];
		$Item                 = $_POST['Item'];
		
		
}else{
		$Selecao              = $_GET['Selecao'];
		$Objeto               = strtoupper2($_GET['Objeto']);
		$OrgaoLicitanteCodigo = $_GET['OrgaoLicitanteCodigo'];
		$ComissaoCodigo       = $_GET['ComissaoCodigo'];
		$ModalidadeCodigo     = $_GET['ModalidadeCodigo'];
		$LicitacaoAno         = $_GET['LicitacaoAno'];
		$TipoItemLicitacao    = $_GET['TipoItemLicitacao'];
		$Item                 = $_GET['Item'];
}

//echo "<p> $TipoItemLicitacao </p>";
//echo "<p> $Item   </p>";
//exit;


# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "ConsHistoricoResultado.php";

 
# Redireciona dados para ConsAcompPesquisar.php se Houve Erro #
if ( ($Item=="") and ($TipoItemLicitacao)!="" ) {
    $Mensagem  = "Falta digitar o texto do Item";
    $Mens      = 1;
    $Tipo      = 1;
	$Url = "ConsHistoricoPesquisar.php?Selecao=$Selecao&Mensagem=$Mensagem&Tipo=$Tipo&Mens=$Mens";
	if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
  	header("location: ".$Url);
  	exit();
}



# Redireciona dados para ConsHistoricoPesquisar.php #
if( $Botao == "Pesquisa" ){
		$Url = "ConsHistoricoPesquisar.php?Selecao=$Selecao";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
  	header("location: ".$Url);
  	exit();
}

$Mens = 0;
if( $Mens == 0 ) {
		$db = Conexao();
		if( $Selecao == 1 ) {
				$Titulo=' Anos Anteriores';
				# Seleciona anos anteriores #
				$sql  = "select distinct c.EGREMPDESC,e.EMODLIDESC,d.ECOMLIDESC,a.CLICPOPROC,a.ALICPOANOP,a.CLICPOCODL,a.ALICPOANOL,a.XLICPOOBJE, ";
				$sql .= "a.TLICPODHAB,b.EORGLIDESC,a.CGREMPCODI,a.CCOMLICODI,a.CORGLICODI ";
				$sql .= "from SFPC.TBLICITACAOPORTAL a, SFPC.TBORGAOLICITANTE b, SFPC.TBGRUPOEMPRESA c, SFPC.TBCOMISSAOLICITACAO d, SFPC.TBMODALIDADELICITACAO e ";
				
				if ( $TipoItemLicitacao==1 ){
					$sql .=   "	,SFPC.tbitemlicitacaoportal F  ,SFPC.tbmaterialportal g ";
				}
				if ( $TipoItemLicitacao==2 ){
					$sql .=   "	,SFPC.tbitemlicitacaoportal F  ,SFPC.tbservicoportal g ";
				}
				
				
				$sql .= "where a.CORGLICODI = b.CORGLICODI and a.CGREMPCODI = c.CGREMPCODI ";
				$sql .= "   AND TO_CHAR(A.TLICPODHAB,'YYYY') = '$LicitacaoAno' ";
				$sql .= "and a.CCOMLICODI = d.CCOMLICODI and a.CMODLICODI = e.CMODLICODI and (extract(year from a.TLICPODHAB) < extract(year from current_date)) ";

				if ( ($TipoItemLicitacao==1) or ($TipoItemLicitacao==2) ){
					$sql .=   "	AND a.CLICPOPROC = f.CLICPOPROC  ";
					$sql .=   "	AND a.ALICPOANOP = f.ALICPOANOP  ";
					$sql .=   "	AND a.CGREMPCODI = f.CGREMPCODI  ";
					$sql .=   "	AND a.CCOMLICODI = f.CCOMLICODI  ";
					$sql .=   "	AND a.CORGLICODI = f.CORGLICODI  ";
				}
				
				if ( $TipoItemLicitacao==1 ){
			        $sql .=   " AND f.CMATEPSEQU  = g.CMATEPSEQU  ";
			        $sql .=   " AND(g.EMATEPDESC ILIKE '%$Item%') ";
				}
				
				if ( $TipoItemLicitacao==2 ){
			        $sql .=   " AND f.CSERVPSEQU  = g.CSERVPSEQU  ";
			        $sql .=   " AND(g.ESERVPDESC ILIKE '%$Item%') ";
				}
				
 
				
				
		}elseif( $Selecao == 2 ){
				$Titulo=' Ano Atual';
				# Seleciona Ano Atual #
				$sql  = "SELECT distinct c.EGREMPDESC,e.EMODLIDESC,d.ECOMLIDESC,a.CLICPOPROC,a.ALICPOANOP,a.CLICPOCODL,a.ALICPOANOL,a.XLICPOOBJE, ";
				$sql .= "a.TLICPODHAB,b.EORGLIDESC,a.CGREMPCODI,a.CCOMLICODI,a.CORGLICODI ";
				$sql .= "FROM SFPC.TBLICITACAOPORTAL a, SFPC.TBORGAOLICITANTE b, SFPC.TBGRUPOEMPRESA c, SFPC.TBCOMISSAOLICITACAO d, SFPC.TBMODALIDADELICITACAO e ";
				
				if ( $TipoItemLicitacao==1 ){
					$sql .=   "	,SFPC.tbitemlicitacaoportal F  ,SFPC.tbmaterialportal g ";
				}
				if ( $TipoItemLicitacao==2 ){
					$sql .=   "	,SFPC.tbitemlicitacaoportal F  ,SFPC.tbservicoportal g ";
				}
				
				
				$sql .= "WHERE a.CORGLICODI = b.CORGLICODI and a.CGREMPCODI = c.CGREMPCODI ";
				$sql .= "AND a.CCOMLICODI = d.CCOMLICODI and a.CMODLICODI = e.CMODLICODI and (extract(year from a.TLICPODHAB) >= extract(year from current_date)) ";
				
				
				
				
				if ( ($TipoItemLicitacao==1) or ($TipoItemLicitacao==2) ){
					$sql .=   "	AND a.CLICPOPROC = f.CLICPOPROC  ";
					$sql .=   "	AND a.ALICPOANOP = f.ALICPOANOP  ";
					$sql .=   "	AND a.CGREMPCODI = f.CGREMPCODI  ";
					$sql .=   "	AND a.CCOMLICODI = f.CCOMLICODI  ";
					$sql .=   "	AND a.CORGLICODI = f.CORGLICODI  ";
				}
				
				if ( $TipoItemLicitacao==1 ){
			        $sql .=   " AND f.CMATEPSEQU  = g.CMATEPSEQU  ";
			        $sql .=   " AND(g.EMATEPDESC ILIKE '%$Item%') ";
				}
				
				if ( $TipoItemLicitacao==2 ){
			        $sql .=   " AND f.CSERVPSEQU  = g.CSERVPSEQU  ";
			        $sql .=   " AND(g.ESERVPDESC ILIKE '%$Item%') ";
				}
				
				
		}

		if( $ModalidadeCodigo != "" ){
			  $sql .= "and a.CMODLICODI = $ModalidadeCodigo ";
		}

		if( $Objeto != "" ){
				$sql .= "AND (a.XLICPOOBJE LIKE '%$Objeto%')";
		}

		if( $ComissaoCodigo != "" ){ $sql .= "and a.CCOMLICODI = $ComissaoCodigo "; }
		if( $ModalidadeCodigo != "" ){ $sql .= "and a.CMODLICODI = $ModalidadeCodigo "; }
		if( $OrgaoLicitanteCodigo != "" ){ $sql .= "and a.CORGLICODI = $OrgaoLicitanteCodigo"; }

		if( $Selecao == 1 ) {
				$sql .= "order by c.EGREMPDESC, e.EMODLIDESC, d.ECOMLIDESC, a.ALICPOANOP DESC, a.CLICPOPROC DESC";
		}else{
				$sql .= "order by c.EGREMPDESC, e.EMODLIDESC, d.ECOMLIDESC, a.ALICPOANOP, a.CLICPOPROC";
		}
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}
		$Rows = $result->numRows();
		$GrupoDescricao = "";
		if( $Rows != 0 ){
				echo "<html>\n";
				# Carrega o layout padrão #
				layout();
				echo "<script language=\"javascript\" type=\"\">\n";
				echo "<!--\n";
				echo "function enviar(valor){\n";
				echo "	document.Historico.Botao.value=valor;\n";
				echo "	document.Historico.submit();\n";
				echo "}\n";
				MenuAcesso();
				echo "//-->\n";
				echo "</script>\n";
			  echo "<link rel=\"Stylesheet\" type=\"Text/Css\" href=\"../estilo.css\">\n";
				?>
				<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
				<script language="JavaScript" src="../menu.js"></script>
				<script language="JavaScript">Init();</script>
				<?php
				echo "<form action=\"ConsHistoricoResultado.php\" method=\"post\" name=\"Historico\">\n";
				echo "<br><br><br><br><br>\n";
				echo "<table cellpadding=\"3\" border=\"0\">\n";
				echo "  <!-- Caminho -->\n";
				echo "  <tr>\n";
				echo "    <td width=\"150\"><img border=\"0\" src=\"../midia/linha.gif\" alt=\"\"></td>\n";
				echo "    <td align=\"left\" class=\"textonormal\" colspan=\"2\">\n";
				echo "      <font class=\"titulo2\">|</font>\n";
				echo "      <a href=\"../index.php\"><font color=\"#000000\">Página Principal</font></a> > Licitações > Histórico > $Titulo\n";
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
				echo "      <table  border=\"0\" cellspacing=\"0\" cellpadding=\"3\" bgcolor=\"#FFFFFF\">\n";
				echo "        <tr>\n";
				echo "	      	<td class=\"textonormal\">\n";
				echo "	        	<table border=\"1\" cellpadding=\"3\" cellspacing=\"0\" bordercolor=\"#75ADE6\" summary=\"\" class=\"textonormal\">\n";
				echo "	          	<tr>\n";
				echo "	            	<td align=\"center\" bgcolor=\"#75ADE6\" valign=\"middle\" colspan=\"6\">\n";
				echo "		    					<font class=\"titulo3\">HISTÓRICO DE LICITAÇÕES\n";
				echo strtoupper2($Titulo);
				echo "		    					- RESULTADO</font>\n";
				echo "		          	</td>\n";
				echo "		        	</tr>\n";
				echo "	          	<tr>\n";
				echo "	            	<td colspan=\"6\" class=\"textonormal\">\n";
				echo "	        	    		Para visualizar mais informações sobre a Licitação, clique no número da Licitação desejada. Para realizar uma nova pesquisa, selecione o botão \"Nova Pesquisa\".\n";
				echo "		          	</td>\n";
				echo "		        	</tr>\n";
				echo "	          	<tr>\n";
				echo "   			  			<td class=\"textonormal\" align=\"right\" colspan=\"6\">\n";
				echo "	          			<input type=\"button\" name=\"Pesquisa\" value=\"Nova Pesquisa\" class=\"botao\" onclick=\"javascript:enviar('Pesquisa');\">\n";
			  echo "			          	<input type=\"hidden\" name=\"Botao\" value=\"\">\n";
				echo "          			</td>\n";
				echo "		        	</tr>\n";
				while( $Linha = $result->fetchRow() ){
						if( $GrupoDescricao != $Linha[0] ){
								$GrupoDescricao = $Linha[0];
								echo "<tr><td align=\"center\" class=\"titulo3\" colspan=\"6\" bgcolor=\"#DCEDF7\">$GrupoDescricao</td></tr>\n";
								$ModalidadeDescricao = "";
						}
						if( $ModalidadeDescricao != $Linha[1] ){
								$ModalidadeDescricao = $Linha[1];
								echo "<tr><td align=\"center\" class=\"titulo3\" colspan=\"6\">$ModalidadeDescricao <input type=\"hidden\" name=\"Selecao\" value=$Selecao size=\"1\"></td>\n";
								echo "</tr>\n";
								$ComissaoDescricao = "";
						}
						if( $ComissaoDescricao != $Linha[2] ){
								$ComissaoDescricao = $Linha[2];
								echo "<tr><td class=\"titulo2\" colspan=\"6\" color=\"#000000\">$ComissaoDescricao</tr></td>\n";
								echo "<tr><td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">PROCESSO</td>\n";
								echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">LICITAÇÃO</td>\n";
								echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">OBJETO</td>\n";
								echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">DATA/HORA ABERTURA</td>\n";
								echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">ÓRGÃO LICITANTE</td>\n";
						}
						$NProcesso 	= substr($Linha[3] + 10000,1);
						$NLicitacao = substr($Linha[5] + 10000,1);
						$LicitacaoDtAbertura = substr($Linha[8],8,2) ."/". substr($Linha[8],5,2) ."/". substr($Linha[8],0,4);
						$LicitacaoHoraAbertura = substr($Linha[8],11,5);
						echo "<tr>\n";
						$Url = "ConsHistoricoDetalhes.php";
            $Parametros = "?Selecao=$Selecao&GrupoCodigo=$GrupoCodigo&LicitacaoProcesso=$LicitacaoProcesso&LicitacaoAno=$LicitacaoAno&ComissaoCodigo=$ComissaoCodigo&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&Objeto=$Objeto&GrupoCodigoDet=$Linha[10]&LicitacaoProcessoDet=$Linha[3]&LicitacaoAnoDet=$Linha[4]&ComissaoCodigoDet=$Linha[11]&OrgaoLicitanteCodigoDet=$Linha[12]&TipoItemLicitacao=$TipoItemLicitacao&Item=$Item";
            $Url .= $Parametros;
						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
						echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\"><a href=\"$Url\"><font color=\"#000000\">$NProcesso/$Linha[4]</font></td>\n";
						echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$NLicitacao/$Linha[6]</font></td>\n";
						echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$Linha[7]</td>\n";
						echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$LicitacaoDtAbertura<br>$LicitacaoHoraAbertura&nbsp;h</td>\n";
						echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$Linha[9]</td>\n";
						echo "</tr>\n";
				}
		}else{
				$Mens = 1; $Tipo = 1; $Critica = 0;
		    $Mensagem = "Nenhuma ocorrência foi encontrada";
				$Mensagem = urlencode($Mensagem);
		    # Envia mensagem para página selecionar #
		    $Url = "ConsHistoricoPesquisar.php?Mensagem=$Mensagem&Mens=$Mens&Tipo=$Tipo&Selecao=$Selecao&Objeto=$Objeto&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&ComissaoCodigo=$ComissaoCodigo&ModalidadeCodigo=$ModalidadeCodigo&TipoItemLicitacao=$TipoItemLicitacao&Item=$Item";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		    header("location: ".$Url);
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
