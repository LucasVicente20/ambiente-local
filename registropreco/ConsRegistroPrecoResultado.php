<?php
#----------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsRegistroPrecoResultado.php
# Autor:    Rossana Lira
# Data:     19/03/2007
# Objetivo: Programa de Resultado de Licitações do tipo Registro de Preço e
#           homologadas
# Alterado: Carlos Abreu
# Data:     20/09/2007 - Ajuste no redirecionamento da tela
# Alterado: Carlos Abreu
# Data:     21/09/2007 - Inclusao do translate na consulta
# OBS.:     Tabulação 2 espaços
#----------------------------------------------------------------------------

// 220038--

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/registropreco/ConsRegistroPrecoPesquisar.php');
AddMenuAcesso('/registropreco/ConsRegistroPrecoDetalhes.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Botao                = $_POST['Botao'];
    $Critica              = $_POST['Critica'];
    $OrgaoLicitanteCodigo = $_POST['OrgaoLicitanteCodigo'];
    $ComissaoCodigo       = $_POST['ComissaoCodigo'];
    $ModalidadeCodigo     = $_POST['ModalidadeCodigo'];
    $Objeto               = strtoupper2($_POST['Objeto']);
    $LicitacaoAno         = $_POST['LicitacaoAno'];
} else {
    $Objeto               = strtoupper2($_GET['Objeto']);
    $OrgaoLicitanteCodigo = $_GET['OrgaoLicitanteCodigo'];
    $ComissaoCodigo       = $_GET['ComissaoCodigo'];
    $ModalidadeCodigo     = $_GET['ModalidadeCodigo'];
    $LicitacaoAno         = $_GET['LicitacaoAno'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "ConsRegistroPrecoResultado.php";

# Redireciona dados para ConsRegistroPrecoPesquisar.php #
if ($Botao == "Pesquisa") {
    $Url = "ConsRegistroPrecoPesquisar.php";
    if (!in_array($Url, $_SESSION['GetUrl'])) {
        $_SESSION['GetUrl'][] = $Url;
    }
    header("location: ".$Url);
    exit();
}

$Mens = 0;
if ($Mens == 0) {
    $db   = Conexao();
    $Data = date("Y-m-d");
        # Seleciona Ano Atual #
        $sql  = "SELECT DISTINCT C.EGREMPDESC, E.EMODLIDESC, D.ECOMLIDESC, A.CLICPOPROC, ";
    $sql .= "       A.ALICPOANOP, A.CLICPOCODL, A.ALICPOANOL, A.XLICPOOBJE, ";
    $sql .= "       A.TLICPODHAB, B.EORGLIDESC, A.CGREMPCODI, A.CCOMLICODI, ";
    $sql .= "       A.CORGLICODI ";
    $sql .= "  FROM SFPC.TBLICITACAOPORTAL A, SFPC.TBORGAOLICITANTE B, SFPC.TBGRUPOEMPRESA C, ";
    $sql .= "       SFPC.TBCOMISSAOLICITACAO D, SFPC.TBMODALIDADELICITACAO E, SFPC.TBFASELICITACAO F, SFPC.TBATAREGISTROPRECO G ";
    $sql .= " WHERE A.CORGLICODI = B.CORGLICODI AND A.FLICPOSTAT = 'A' ";
    $sql .= "   AND A.CGREMPCODI = C.CGREMPCODI AND A.CCOMLICODI = D.CCOMLICODI ";
    $sql .= "   AND A.CMODLICODI = E.CMODLICODI AND A.TLICPODHAB <= '$Data 23:59:59' ";
    $sql .= "   AND A.CLICPOPROC = F.CLICPOPROC AND A.ALICPOANOP = F.ALICPOANOP ";
    $sql .= "   AND A.CORGLICODI = F.CORGLICODI AND A.CGREMPCODI = F.CGREMPCODI ";
    $sql .= "   AND A.CCOMLICODI = F.CCOMLICODI AND F.CFASESCODI = 13 ";
    $sql .= "   AND A.CGREMPCODI = G.CGREMPCODI AND A.CCOMLICODI = G.CCOMLICODI ";
    $sql .= "   AND A.CLICPOPROC = G.CLICPOPROC AND G.ALICPOANOP = F.ALICPOANOP ";
    $sql .= "   AND A.CORGLICODI = G.CORGLICODI ";

    if ($Objeto != "") {
        $sql .= " AND TRANSLATE(A.XLICPOOBJE,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '%".strtoupper2(RetiraAcentos($Objeto))."%' ";
    }
    if ($ComissaoCodigo != "") {
        $sql .= " AND A.CCOMLICODI = $ComissaoCodigo ";
    }
    if ($ModalidadeCodigo != "") {
        $sql .= " AND A.CMODLICODI = $ModalidadeCodigo ";
    }
    if ($OrgaoLicitanteCodigo != "") {
        $sql .= " AND A.CORGLICODI = $OrgaoLicitanteCodigo ";
    }
    $sql .= " ORDER BY C.EGREMPDESC, E.EMODLIDESC, D.ECOMLIDESC, A.ALICPOANOP, A.CLICPOPROC ";
    $result = $db->query($sql);
    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
    }
    while ($cols = $result->fetchRow()) {
        $cont++;
        $dados[$cont-1] = "$cols[0]_$cols[1]_$cols[2]_$cols[3]_$cols[4]_$cols[5]_$cols[6]_$cols[7]_$cols[8]_$cols[9]_$cols[10]_$cols[11]_$cols[12]";
    }

    $GrupoDescricao = "";
    if ($cont != 0) {
        echo "<html>\n";
                # Carrega o layout padrão #
                layout();
        echo "<script language=\"javascript\">\n";
        echo "<!--\n";
        echo "function enviar(valor){\n";
        echo "	document.Acomp.Botao.value=valor;\n";
        echo "	document.Acomp.submit();\n";
        echo "}\n";
        MenuAcesso();
        echo "//-->\n";
        echo "</script>\n";
        echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"../estilo.css\">\n"; ?>
				<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
				<script language="JavaScript" src="../menu.js"></script>
				<script language="JavaScript">Init();</script>
				<?php
                echo "<form action=\"ConsRegistroPrecoResultado.php\" method=\"post\" name=\"Acomp\">\n";
        echo "<br><br><br><br>\n";
        echo "<table cellpadding=\"3\" border=\"0\" summary=\"\">\n";
        echo "  <!-- Caminho -->\n";
        echo "  <tr>\n";
        echo "    <td width=\"150\"><img border=\"0\" src=\"../midia/linha.gif\" alt=\"\"></td>\n";
        echo "    <td align=\"left\" class=\"textonormal\" colspan=\"2\"><br>\n";
        echo "      <font class=\"titulo2\">|</font>\n";
        echo "      <a href=\"../index.php\"><font color=\"#000000\">Página Principal</font></a> > Registro Preço > Consulta $Titulo\n";
        echo "    </td>\n";
        echo "  </tr>\n";
        echo "  <!-- Fim do Caminho-->\n";
        echo "	<!-- Erro -->\n";
        if ($Mens == 1) {
            echo "	<tr>\n";
            echo "	  <td width=\"100\"></td>\n";
            echo "	  <td align=\"left\" colspan=\"2\">\n";
            if ($Mens == 1) {
                ExibeMens($Mensagem, $Tipo, 1);
            }
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
        echo "		    					CONSULTA DE ATA DE REGISTRO DE PREÇO\n";
        echo strtoupper2($Titulo);
        echo "		    					- RESULTADO\n";
        echo "		          	</td>\n";
        echo "		        	</tr>\n";
        echo "	          	<tr>\n";
        echo "	            	<td colspan=\"5\" class=\"textonormal\">\n";
        echo "	        	    		Para visualizar mais informações, clique no número do Processo desejado. Para realizar uma nova pesquisa, selecione o botão \"Nova Pesquisa\".\n";
        echo "	        	    		Só serão exibidos os Processos Licitatórios do Tipo Registro de Preço já Homologado\n";
        echo "		          	</td>\n";
        echo "		        	</tr>\n";
        echo "	          	<tr>\n";
        echo "	    	      	<td class=\"textonormal\" colspan=\"5\" align=\"right\">\n";
        echo "	          			<input type=\"button\" name=\"Pesquisa\" value=\"Nova Pesquisa\" class=\"botao\" onclick=\"javascript:enviar('Pesquisa');\">\n";
        echo "			            <input type=\"hidden\" name=\"Botao\" value=\"\">\n";
        echo "          			</td>\n";
        echo "		        	</tr>\n";
        for ($Row = 0 ; $Row < $cont ; $Row++) {
            $Linha = explode("_", $dados[$Row]);
            if ($GrupoDescricao != $Linha[0]) {
                $GrupoDescricao = $Linha[0];
                echo "<tr><td align=\"center\" class=\"titulo3\" colspan=\"5\" bgcolor=\"#DCEDF7\">$GrupoDescricao</td></tr>\n";
                $ModalidadeDescricao = "";
            }
            if ($ModalidadeDescricao != $Linha[1]) {
                $ModalidadeDescricao = $Linha[1];
                echo "<tr><td align=\"center\" class=\"titulo3\" colspan=\"5\">$ModalidadeDescricao</td></tr>\n";
                $ComissaoDescricao = "";
            }
            if ($ComissaoDescricao != $Linha[2]) {
                $ComissaoDescricao = $Linha[2];
                echo "<tr><td class=\"titulo2\" colspan=\"5\" color=\"#000000\">$ComissaoDescricao</td></tr>\n";
                echo "<tr><td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">PROCESSO</td>\n";
                echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">LICITAÇÃO</td>\n";
                echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">OBJETO</td>\n";
                echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">DATA/HORA ABERTURA</td>\n";
                echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">ÓRGÃO LICITANTE</td></tr>\n";
            }
            $NProcesso  = substr($Linha[3] + 10000, 1);
            $NLicitacao = substr($Linha[4] + 10000, 1);
            $Linha[5]   = substr($Linha[5] + 10000, 1);
            $Linha[6]   = substr($Linha[6] + 10000, 1);
            $LicitacaoDtAbertura = substr($Linha[8], 8, 2) ."/". substr($Linha[8], 5, 2) ."/". substr($Linha[8], 0, 4);
            $LicitacaoHoraAbertura = substr($Linha[8], 11, 5);
            echo "<tr>\n";
            $Url = "ConsRegistroPrecoDetalhes.php?GrupoCodigo=$Linha[10]&Processo=$Linha[3]&ProcessoAno=$Linha[4]&ComissaoCodigo=$Linha[11]&OrgaoLicitanteCodigo=$Linha[12]";
            if (!in_array($Url, $_SESSION['GetUrl'])) {
                $_SESSION['GetUrl'][] = $Url;
            }
            echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\"><a href=\"$Url\"><font color=\"#000000\">$NProcesso/$Linha[4]</font></td>\n";
            echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$Linha[5]/$Linha[6]</td>\n";
            echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$Linha[7]</td>\n";
            echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$LicitacaoDtAbertura<br>$LicitacaoHoraAbertura h</td>\n";
            echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$Linha[9]</td>\n";
            echo "</tr>\n";
        }
    } else {
        # Envia mensagem para página selecionar #
            $Mensagem = urlencode("Nenhuma ocorrência foi encontrada");
        $Url = "ConsRegistroPrecoPesquisar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
        if (!in_array($Url, $_SESSION['GetUrl'])) {
            $_SESSION['GetUrl'][] = $Url;
        }
        header("location: ".$Url);
        exit;
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
