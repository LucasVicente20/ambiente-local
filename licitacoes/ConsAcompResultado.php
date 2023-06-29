<?php
#----------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsAcompResultado.php
# Autor:    Rossana Lira
# Data:     06/05/03
# Objetivo: Programa de Resultado do Acompanhamento de Licitações que já
#						foram realizadas (data de abertura inferior à data atual)
# Alterado: Carlos Abreu
# Data:     20/02/2007 - Colocar ano como argumento da pesquisa para reduzir estouro da variável de sessão
# Alterado: Carlos Abreu
# Data:     07/06/2007 - troca de variaveis get para session
# Alterado: Rodrigo Melo
# Data:     11/01/2008 - Corrigindo filtro de buscar para pesquisar com caracteres maiúsculos ou minúsculos.
# OBS.:     Tabulação 2 espaços
#----------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

/*
foreach($_SESSION['GetUrl'] as $Url){
	if (strpos($Url,"ConsAcompDetalhes.php")=== false){
		$Tmp[] = $Url;
	}
}
$_SESSION['GetUrl'] = $Tmp;
*/

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/licitacoes/ConsAcompPesquisar.php' );
AddMenuAcesso( '/licitacoes/ConsAcompDetalhes.php' );

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
	$TipoItemLicitacao	  =	$_SESSION['TipoItemLicitacao'] ;
	$Item				  =	$_SESSION['Item'] ;		
	
	/* 
    echo "<p> Selecao".$_SESSION['Selecao']."</p>";
    echo "<p> Objeto".$_SESSION['Objeto']."</p>";
    echo "<p> OrgaoLicitanteCodigo".$_SESSION['OrgaoLicitanteCodigo']."</p>";
    echo "<p> TipoItemLicitacao ".$_SESSION['TipoItemLicitacao']."</p>";
    echo "<p> Item".$_SESSION['Item']."</p>";    
    
	
    exit;
	*/ 
	
	
}

	




# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "ConsAcompResultado.php";

//echo "Tipo".$TipoItemLicitacao;
//echo "Item".$Item;
//exit;

 
# Redireciona dados para ConsAcompPesquisar.php se Houve Erro #
if ( ($Item=="") and ($TipoItemLicitacao)!="" ) {
    $_SESSION['Mensagem']             = "Falta digitar o texto do Item";
    $_SESSION['Mens']                 = 1;
    $_SESSION['Tipo']                 = 1;
    $_SESSION['Objeto']               = $Objeto;
    $_SESSION['OrgaoLicitanteCodigo'] = $OrgaoLicitanteCodigo;
    $_SESSION['ComissaoCodigo']       = $ComissaoCodigo;
    $_SESSION['ModalidadeCodigo']     = $ModalidadeCodigo;
    $_SESSION['Selecao']              = $Selecao;
    $_SESSION['RetornoPesquisa']      = 1;
    $_SESSION['TipoItemLicitacao'] 	  = $TipoItemLicitacao ;
	$_SESSION['Item'] 				  = $Item;			
  	header("location: ConsAcompPesquisar.php");
  	exit();
}
	


# Redireciona dados para ConsAcompPesquisar.php #
if( $Botao == "Pesquisa" ){
  	header("location: ConsAcompPesquisar.php?Selecao=".$Selecao);
  	exit();
}

if( $Botao == "carregaProcesso" ){
	list($_SESSION['GrupoCodigoDet'],$_SESSION['ProcessoDet'],$_SESSION['ProcessoAnoDet'],$_SESSION['ComissaoCodigoDet'],$_SESSION['OrgaoLicitanteCodigoDet']) = explode("-",$carregaProcesso);
  	header("location: ConsAcompDetalhes.php");
  	exit();
}

$Mens = 0;
if( $Mens == 0 ) {
		$db   = Conexao();
		$Data = date("Y-m-d");
		if( $Selecao == 1 ){
				$Titulo=' Anos Anteriores';
				# Seleciona anos anteriores #
				$sql  = "SELECT distinct C.EGREMPDESC, E.EMODLIDESC, D.ECOMLIDESC, A.CLICPOPROC, ";
				$sql .= "       A.ALICPOANOP, A.CLICPOCODL, A.ALICPOANOL, A.XLICPOOBJE, ";
				$sql .= "       A.TLICPODHAB, B.EORGLIDESC, A.CGREMPCODI, A.CCOMLICODI, ";
				$sql .= "       A.CORGLICODI ";
				$sql .= " FROM SFPC.TBLICITACAOPORTAL A, SFPC.TBORGAOLICITANTE B, SFPC.TBGRUPOEMPRESA C, ";
				$sql .= "       SFPC.TBCOMISSAOLICITACAO D, SFPC.TBMODALIDADELICITACAO E ";
				
				if ( $TipoItemLicitacao==1 ){
					$sql .=   "	,SFPC.tbitemlicitacaoportal F  ,SFPC.tbmaterialportal g ";
				}
				if ( $TipoItemLicitacao==2 ){
					$sql .=   "	,SFPC.tbitemlicitacaoportal F  ,SFPC.tbservicoportal g ";
				}
				
				
				$sql .= " WHERE A.CORGLICODI = B.CORGLICODI AND A.FLICPOSTAT = 'A' ";
				$sql .= "   AND A.CGREMPCODI = C.CGREMPCODI AND A.CCOMLICODI = D.CCOMLICODI ";
				$sql .= "   AND TO_CHAR(A.TLICPODHAB,'YYYY') = '$LicitacaoAno' ";
				$sql .= "   AND A.CMODLICODI = E.CMODLICODI AND (EXTRACT(YEAR FROM A.TLICPODHAB) < EXTRACT(YEAR FROM CURRENT_DATE)) ";

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
				$sql  = "SELECT C.EGREMPDESC, E.EMODLIDESC, D.ECOMLIDESC, A.CLICPOPROC, ";
				$sql .= "       A.ALICPOANOP, A.CLICPOCODL, A.ALICPOANOL, A.XLICPOOBJE, ";
				$sql .= "       A.TLICPODHAB, B.EORGLIDESC, A.CGREMPCODI, A.CCOMLICODI, ";
				$sql .= "       A.CORGLICODI ";
				$sql .= " FROM SFPC.TBLICITACAOPORTAL A, SFPC.TBORGAOLICITANTE B, SFPC.TBGRUPOEMPRESA C, ";
				$sql .= "       SFPC.TBCOMISSAOLICITACAO D, SFPC.TBMODALIDADELICITACAO E ";

				if ( $TipoItemLicitacao==1 ){
					$sql .=   "	,SFPC.tbitemlicitacaoportal F  ,SFPC.tbmaterialportal g ";
				}
				if ( $TipoItemLicitacao==2 ){
					$sql .=   "	,SFPC.tbitemlicitacaoportal F  ,SFPC.tbservicoportal g ";
				}
				
				
				
				$sql .= " WHERE A.CORGLICODI = B.CORGLICODI AND A.FLICPOSTAT = 'A' ";
				$sql .= "   AND A.CGREMPCODI = C.CGREMPCODI AND A.CCOMLICODI = D.CCOMLICODI ";
				$sql .= "   AND A.CMODLICODI = E.CMODLICODI AND (EXTRACT(YEAR FROM A.TLICPODHAB) = EXTRACT(YEAR FROM CURRENT_DATE)) ";
				$sql .= "   AND A.TLICPODHAB <= '$Data 23:59:59' ";

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
				
				
				
		}else{
				header("location: ConsAcompPesquisar.php?Selecao=2");
				exit;
		}

		if( $Objeto != "" ){ $sql .= " AND (A.XLICPOOBJE LIKE '%$Objeto%')"; }
		if( $ComissaoCodigo != "" ){ $sql .= " AND A.CCOMLICODI = $ComissaoCodigo "; }
		if( $ModalidadeCodigo != "" ){ $sql .= " AND A.CMODLICODI = $ModalidadeCodigo "; }
		if( $OrgaoLicitanteCodigo != "" ){ $sql .= " AND A.CORGLICODI = $OrgaoLicitanteCodigo "; }

		if( $Selecao == 1 ){
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
				echo "<form action=\"ConsAcompResultado.php\" method=\"post\" name=\"Acomp\">\n";
				echo "<br><br><br><br>\n";
				echo "<table cellpadding=\"3\" border=\"0\" summary=\"\">\n";
				echo "  <!-- Caminho -->\n";
				echo "  <tr>\n";
				echo "    <td width=\"150\"><img border=\"0\" src=\"../midia/linha.gif\" alt=\"\"></td>\n";
				echo "    <td align=\"left\" class=\"textonormal\" colspan=\"2\"><br>\n";
				echo "      <font class=\"titulo2\">|</font>\n";
				echo "      <a href=\"../index.php\"><font color=\"#000000\">Página Principal</font></a> > Licitações > Acompanhamento > $Titulo\n";
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
				echo "		    					ACOMPANHAMENTO DE LICITAÇÕES\n";
				echo strtoupper2($Titulo);
				echo "		    					- RESULTADO\n";
				echo "		          	</td>\n";
				echo "		        	</tr>\n";
				echo "<tr>";
				/*
				echo "<p>Modalidade = $TipoItemLicitacao </p>";
				echo "<p>LicitacaoAno = $Item </p>";
				*/
				echo "</td>";
				echo "</tr>";
				echo "";
				
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
     	    $_SESSION['TipoItemLicitacao'] 	  = $TipoItemLicitacao ;
 			$_SESSION['Item'] 				  = $Item;			
		    header("location: ConsAcompPesquisar.php");
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
