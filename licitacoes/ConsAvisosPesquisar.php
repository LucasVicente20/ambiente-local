<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsAvisosPesquisar.php
# Autor:    Roberta Costa
# Data:     24/04/2003
# Objetivo: Programa de Pesquisa de Avisos de Licitação
#---------------------------
# Alterado: Carlos Abreu
# Data:     25/08/2006 - Mudança de Variáveis GET para POST
# Alterado: Ariston Cordeiro
# Data:     24/03/2008 	- Juntando todas funcionalidades de ConsAvisosResultado.php (que será deletado)
#												- Listar resultado de todos avisos de licitações disponíveis, caso nenhuma pesquisa tenha sido feita.
# Alterado: Ariston Cordeiro
# Data:     05/06/2009 	- Removendo link do ícone do disquete, pois ele já dá submit do form, e tendo um link (href) mais dando submit está causando alguns efeitos incomuns no firefox. (exemplo, não guardando no histórico ao clicar no link)
#
# Alterado: Everton Lino
# Data:     08/07/2010 	- ALTERAÇÃO DE CAMPO - EXIBIR ENDEREÇO, TELEFONE E FAX.
# Correção : Everton Lino
# Data:     20/09/2010 	- ALTERAÇÃO DE CAMPO SELECT
#
# Alterado: Heraldo Botelho
# Data:     01/10/2011 -  Construção da nova query dando JOIN nas tabelas de SFPC.TBMATERIALPORTAL
#                         e SFPC.TBSERVICOPORTAL SERV executada quando o usuário optar por consultar pela
#                         descrição dos itens de materias ou serviço
# Alterado: Pitang Agile IT
# Data:     25/05/2015
# Objetivo: [CR remine 74235] Checar por que versão de produção "deixou" de ter as CRs redmine 22 e 23
# Versão:   v1.16.1-74-g93b87c3
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include_once "../funcoes.php";

# Executa o controle de segurança #
session_start();

Seguranca();

# Adiciona páginas no MenuAcesso #
//AddMenuAcesso( '/licitacoes/ConsAvisosResultado.php' );
AddMenuAcesso('/licitacoes/ConsAvisosDocumentos.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Botao                = $_POST['Botao'];
    $Objeto               = $_POST['Objeto'];
    $OrgaoLicitanteCodigo = $_POST['OrgaoLicitanteCodigo'];
    $ComissaoCodigo       = $_POST['ComissaoCodigo'];
    $ModalidadeCodigo     = $_POST['ModalidadeCodigo'];
    $TipoItemLicitacao    = $_POST['TipoItemLicitacao'];
    $Item                 = $_POST['Item'];

    $Mens2                 = $_POST['Mens2'];
    $Tipo2                 = $_POST['Tipo2'];
    $Mensagem2             = urldecode($_POST['Mensagem2']);
} else {
    $Mens2                 = $_GET['Mens2'];
    $Tipo2                 = $_GET['Tipo2'];
    $Mensagem2             = urldecode($_GET['Mensagem2']);
}

# Copiar mensagens  de erro da sessão para variável auxiliar e dar reset na sessao
/*
 if (!empty($_SESSION['Mensagem'])) {
    $Mens=$_SESSION['Mensagem'];
    $Tipo=$_SESSION['Mens'];
    $Mensagem=$_SESSION['Tipo'];
    $_SESSION['Mensagem']="";
    $_SESSION['Mens']="";
    $_SESSION['Tipo']="";
 }
*/

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "ConsAvisosPesquisar.php";

?>
<html>
<?php
# Carrega o layout padrão #
layout();

?>
<script language="javascript" type="">
<!--

window.onload = function(){

limparTextoItem();

}

function enviar(valor){

	document.Avisos.Botao.value=valor;
	document.Avisos.submit();
}

function AbreDocumentos(Objeto,OrgaoLicitanteCodigo,ComissaoCodigo,ModalidadeCodigo,GrupoCodigo,LicitacaoProcesso,LicitacaoAno){
	document.Aviso.Objeto.value=Objeto;
	document.Aviso.OrgaoLicitanteCodigo.value=OrgaoLicitanteCodigo;
	document.Aviso.ComissaoCodigo.value=ComissaoCodigo;
	document.Aviso.ModalidadeCodigo.value=ModalidadeCodigo;
	document.Aviso.GrupoCodigo.value=GrupoCodigo;
	document.Aviso.LicitacaoProcesso.value=LicitacaoProcesso;
	document.Aviso.LicitacaoAno.value=LicitacaoAno;

	document.Aviso.submit();
}

function limpar(){
	document.Avisos.Objeto.value="";
	document.Avisos.OrgaoLicitanteCodigo.value="";
	document.Avisos.ComissaoCodigo.value="";
	document.Avisos.ModalidadeCodigo.value="";
	document.Avisos.TipoItemLicitacao.value="";
	document.Avisos.Item.value="";
	document.Avisos.Botao.value="";
	//document.location.reload();
	document.Avisos.submit();
}

function limparTextoItem(){
	var valorSel = document.getElementById('idTipoItemLicitacao').value;
	if ( valorSel=="") {
		document.getElementById('idItem').value ="";
	    document.getElementById('idItem').disabled =true;
	}
	else
	{
	    document.getElementById('idItem').disabled =false;
	}
}

<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css" />
<body background="../midia/bg.gif" bgcolor="#FFFFFF" text="#000000" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2"><br>
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Avisos
    </td>
  </tr>
  <!-- Fim do Caminho-->

    <?php
    // -----------------------------------------------------
    // Menagens de Erro do Sistema
    // -----------------------------------------------------
    $erro = false;
    $campo = "";

    // ---------------------------------------------------------
    //  1) Criticar se escolher o tipo de item e não digitar o texto do Item
    // --------------------------------------------------------
    if (($Item == "") and ($TipoItemLicitacao) != "") {
        $erro = true;
        $campo = "Item";
        ?>

		<tr>
			<td width="100"></td>
		  	<td align="left" colspan="2">
<?php
ExibeMens("Falta digitar o texto do Item", 1, 1);
        ?>
			</td>
		</tr>

    <?php

    }
    ?>


	<tr>
       <td width="100"></td>
	  <td  colspan="2">

	  <div id="mensagem" class="titulo2" >

	  </div>
	  </td>
	</tr>



	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="100"></td>
		<td class="textonormal">
      <table  border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="">
        <tr>
	      	<td class="textonormal">
	      		<form action="ConsAvisosPesquisar.php" method="post" name="Avisos"    >
	        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
		    					AVISOS DE LICITAÇÕES
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	      	    		<p align="justify">

	        	    		Para consultar as licitações a serem realizadas, selecione o item de pesquisa e  clique no botão "Pesquisar". Para limpar a pesquisa, clique no botão "Limpar".
	        	    		<br/><br/>
	        	    		Para visualizar ou dar download dos documentos da Licitação, clique no documento desejado e preencha as informações solicitadas para o documento ficar disponível. Qualquer dúvida entrar em contato com a comissão de licitação responsável pela Licitação.
	          	   	</p>
	          		</td>
		        	<tr>
	  	        	<td>
	    	      		<table class="textonormal" border="0" summary="">

	        	      		<td class="textonormal" bgcolor="#DCEDF7">Objeto</td>
	          	    		<td class="textonormal">
	          	    			<input type="text" name="Objeto" size="45" maxlength="60" value="<?php echo $Objeto;?>" class="textonormal">
	          	    		</td>
	            			</tr>
										<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Órgão Licitante</td>
	          	    		<td class="textonormal">
		  				  	      <select name="OrgaoLicitanteCodigo" class="textonormal">
										<option value="">Todos os Órgãos Licitantes...</option>
										<?php
                                        $db     = Conexao();
                                        $sql    = "SELECT CORGLICODI, EORGLIDESC FROM SFPC.TBORGAOLICITANTE ORDER BY EORGLIDESC";
                                        $result = $db->query($sql);
                                        if (PEAR::isError($result)) {
                                            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                        } else {
                                            while ($Linha = $result->fetchRow()) {
                                                if ($Linha[0] == $OrgaoLicitanteCodigo) {
                                                    echo "<option value=\"$Linha[0]\" selected>$Linha[1]</option>\n";
                                                } else {
                                                    echo "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
                                                }
                                            }
                                        }
                                        $db->disconnect();
                                        ?>
								  </select>
							  </td>
	            			</tr>
	            			<tr>
		              		<td class="textonormal"  bgcolor="#DCEDF7">Comissão </td>
		              		<td class="textonormal">
		  				  	      <select name="ComissaoCodigo" class="textonormal">
										<option value="">Todas as Comissões...</option>
										<?php
                                        $db     = Conexao();
                                        $sql    = "SELECT CCOMLICODI, ECOMLIDESC, CGREMPCODI ";
                                        $sql   .= "FROM SFPC.TBCOMISSAOLICITACAO ORDER BY CGREMPCODI,ECOMLIDESC";
                                        $result = $db->query($sql);
                                        if (PEAR::isError($result)) {
                                            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                        } else {
                                            while ($Linha = $result->fetchRow()) {
                                                if ($Linha[0] == $ComissaoCodigo) {
                                                    echo "<option value=\"$Linha[0]\" selected>$Linha[1]</option>\n";
                                                } else {
                                                    echo "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
                                                }
                                            }
                                        }
                                        $db->disconnect();
                                        ?>
								  </select>
							  </td>
	            			</tr>
	            			<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Modalidade</td>
								<td class="textonormal">
		  				  	     	<select name="ModalidadeCodigo" class="textonormal">
										<option value="">Todas as Modalidades...</option>
										<?php
                                        $db     = Conexao();
                                        $sql    = "SELECT CMODLICODI, EMODLIDESC FROM SFPC.TBMODALIDADELICITACAO ORDER BY AMODLIORDE";
                                          $result = $db->query($sql);
                                        if (PEAR::isError($result)) {
                                            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                        } else {
                                            while ($Linha = $result->fetchRow()) {
                                                if ($Linha[0] == $ModalidadeCodigo) {
                                                    echo "<option value=\"$Linha[0]\" selected>$Linha[1]</option>\n";
                                                } else {
                                                    echo "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
                                                }
                                            }
                                        }
                                        $db->disconnect();
                                        ?>
								  </select>
							    </td>
							</tr>
							<tr>
							   <td class="textonormal" bgcolor="#DCEDF7">Item</td>
									<td class="textonormal">
									  <select name="TipoItemLicitacao" id="idTipoItemLicitacao" class="textonormal" onChange="limparTextoItem();"    >
										<option value="" >Selecione o Item...</option>
										<option value="1" <?php if ($TipoItemLicitacao == 1) {
                                            echo 'selected';
} ?>>Material</option>
										<option value="2" <?php if ($TipoItemLicitacao == 2) {
                                            echo 'selected';
}?>>Serviço</option>
									  </select>
									 <input type="text" name="Item" id="idItem" value="<?php echo $Item; ?>" size="50" maxlength="60" class="textonormal"   >
									</td>
							</tr>
	          			</table>
		          	</td>
		        	</tr>
	  	      	<tr>
   	  	  			<td class="textonormal" align="right">
	          	  	<input type="button" name="Pesquisar" value="Pesquisar" class="botao" onclick="javascript:enviar('Pesquisar');">

        	      	<input type="button" name="Limpar" value="Limpar" class="botao" onclick="limpar();">
	                <input type="hidden" name="Botao" value="">


		          	</td>
		        	</tr>
    	  	  </table>
   	  	  </form>
					</td>
				</tr>
				<tr>

<?php
//-- Início do conteúdo do arquivo ConsAvisosResultado --------------------------
// EM DESENVOLVIMENTO ESSA PÁGINA, A CONSULTA ABAIXO, NÃO TRAZ RESULTADO, VERIFICAR A DATA - O ANO - PARA TRAZER RESULTADO.

//--------------------------
//-- Marcar Inicio
//--------------------------
if ($erro) {
    $campoParaSetarFoco = "document.Avisos.".$campo.".focus();"
    ?>
    <script language="javascript" type="">
    <?php
    echo $campoParaSetarFoco
    ?>
	</script>
    <?php

}

if ($erro) {
    exit;
}

$Data = date("Y-m-d H:i:s");
//$Data = '2013-09-09 13:43:46';

if ($Mens == 0) {
    $db   = Conexao();
    $sql   = " SELECT DISTINCT GRUPRE.EGREMPDESC, MODLIC.EMODLIDESC, COLIC.ECOMLIDESC, LICPO.CLICPOPROC, LICPO.ALICPOANOP, ";
    $sql  .= " LICPO.CLICPOCODL, LICPO.ALICPOANOL, LICPO.XLICPOOBJE, LICPO.TLICPODHAB, ORGLIC.EORGLIDESC, ";
    $sql  .= " LICPO.CGREMPCODI, LICPO.CCOMLICODI, LICPO.CORGLICODI, COLIC.ECOMLILOCA, COLIC.ACOMLIFONE, COLIC.ACOMLINFAX,  ";
    $sql  .= " ITEM.CMATEPSEQU, ITEM.CSERVPSEQU,ITEM.EITELPDESCMAT,ITEM.EITELPDESCSE";
    $sql  .= " FROM SFPC.TBLICITACAOPORTAL LICPO, SFPC.TBORGAOLICITANTE ORGLIC, SFPC.TBGRUPOEMPRESA GRUPRE, ";
    $sql  .= " SFPC.TBCOMISSAOLICITACAO COLIC,SFPC.TBMODALIDADELICITACAO MODLIC,";
    $sql  .= " SFPC.TBITEMLICITACAOPORTAL ITEM";
    $sql  .= " WHERE LICPO.CORGLICODI = ORGLIC.CORGLICODI AND LICPO.FLICPOSTAT = 'A' ";
    $sql  .= " AND LICPO.CGREMPCODI = GRUPRE.CGREMPCODI AND LICPO.CCOMLICODI = COLIC.CCOMLICODI  ";
    $sql  .= " AND LICPO.CMODLICODI = MODLIC.CMODLICODI AND LICPO.TLICPODHAB >= '$Data' ";
    $sql  .= " AND ITEM.corglicodi = ORGLIC.CORGLICODI ";

    if (($Item != "") and ($TipoItemLicitacao == 1)) {
        $sql = " SELECT DISTINCT GRUPRE.EGREMPDESC, MODLIC.EMODLIDESC, COLIC.ECOMLIDESC, ";
        $sql .= " LICPO.CLICPOPROC, LICPO.ALICPOANOP,  LICPO.CLICPOCODL, LICPO.ALICPOANOL, ";
        $sql .= " LICPO.XLICPOOBJE, LICPO.TLICPODHAB, ORGLIC.EORGLIDESC,  LICPO.CGREMPCODI, ";
        $sql .= " LICPO.CCOMLICODI, LICPO.CORGLICODI, COLIC.ECOMLILOCA, COLIC.ACOMLIFONE, ";
        $sql .= " COLIC.ACOMLINFAX   ";
        $sql .= " FROM  SFPC.TBORGAOLICITANTE ORGLIC, ";
        $sql .= " SFPC.TBGRUPOEMPRESA GRUPRE,  ";
        $sql .= " SFPC.TBCOMISSAOLICITACAO COLIC, ";
        $sql .= " SFPC.TBMODALIDADELICITACAO MODLIC, ";
        $sql .= " SFPC.TBLICITACAOPORTAL LICPO, ";
        $sql .= " SFPC.TBITEMLICITACAOPORTAL ILIC,";
        $sql .= " SFPC.TBMATERIALPORTAL MAT ";
        $sql .= " WHERE LICPO.CORGLICODI = ORGLIC.CORGLICODI ";
        $sql .= " AND LICPO.FLICPOSTAT = 'A'  AND LICPO.CGREMPCODI = GRUPRE.CGREMPCODI ";
        $sql .= " AND LICPO.CCOMLICODI = COLIC.CCOMLICODI   ";
        $sql .= " AND LICPO.CMODLICODI = MODLIC.CMODLICODI ";
        $sql .= " AND LICPO.TLICPODHAB > '$Data'";
        $sql .= " AND LICPO.CLICPOPROC = ILIC.CLICPOPROC ";
        $sql .= " AND LICPO.ALICPOANOP = ILIC.ALICPOANOP ";
        $sql .= " AND LICPO.CGREMPCODI = ILIC.CGREMPCODI ";
        $sql .= " AND LICPO.CCOMLICODI = ILIC.CCOMLICODI ";
        $sql .= " AND LICPO.CORGLICODI = ILIC.CORGLICODI ";
        $sql .= " AND ILIC.CMATEPSEQU  = MAT.CMATEPSEQU  ";
        $sql .= " AND(MAT.EMATEPDESC ILIKE '%".strtoupper2($Item)."%')";
    }
    if (($Item != "") and ($TipoItemLicitacao == 2)) {
        $sql = " SELECT DISTINCT GRUPRE.EGREMPDESC, MODLIC.EMODLIDESC, COLIC.ECOMLIDESC, ";
        $sql .= " LICPO.CLICPOPROC, LICPO.ALICPOANOP,  LICPO.CLICPOCODL, LICPO.ALICPOANOL, ";
        $sql .= " LICPO.XLICPOOBJE, LICPO.TLICPODHAB, ORGLIC.EORGLIDESC,  LICPO.CGREMPCODI, ";
        $sql .= " LICPO.CCOMLICODI, LICPO.CORGLICODI, COLIC.ECOMLILOCA, COLIC.ACOMLIFONE, ";
        $sql .= " COLIC.ACOMLINFAX   ";
        $sql .= " FROM  SFPC.TBORGAOLICITANTE ORGLIC,";
        $sql .= " SFPC.TBGRUPOEMPRESA GRUPRE,  ";
        $sql .= " SFPC.TBCOMISSAOLICITACAO COLIC ,";
        $sql .= " SFPC.TBMODALIDADELICITACAO MODLIC, ";
        $sql .= " SFPC.TBLICITACAOPORTAL LICPO ,";
        $sql .= " SFPC.TBITEMLICITACAOPORTAL ILIC,";
        $sql .= " SFPC.TBSERVICOPORTAL SERV ";
        $sql .= " WHERE   LICPO.CORGLICODI = ORGLIC.CORGLICODI ";
        $sql .= " AND LICPO.FLICPOSTAT = 'A' ";
        $sql .= " AND LICPO.CGREMPCODI = GRUPRE.CGREMPCODI ";
        $sql .= " AND LICPO.CCOMLICODI = COLIC.CCOMLICODI   ";
        $sql .= " AND LICPO.CMODLICODI = MODLIC.CMODLICODI ";
        $sql .= " AND LICPO.TLICPODHAB > '$Data'";
        $sql .= " AND LICPO.CLICPOPROC = ILIC.CLICPOPROC ";
        $sql .= " AND LICPO.ALICPOANOP = ILIC.ALICPOANOP ";
        $sql .= " AND LICPO.CGREMPCODI = ILIC.CGREMPCODI ";
        $sql .= " AND LICPO.CCOMLICODI = ILIC.CCOMLICODI ";
        $sql .= " AND LICPO.CORGLICODI = ILIC.CORGLICODI ";
        $sql .= " AND ILIC.CSERVPSEQU  = SERV.CSERVPSEQU ";
        $sql .= " AND(SERV.ESERVPDESC ILIKE '%".strtoupper2($Item)."%') ";
    }

    if ($Objeto != "") {
        $sql .= " AND ( LICPO.XLICPOOBJE ILIKE '%".strtoupper2($Objeto)."%')";
    }
    if ($ComissaoCodigo != "") {
        $sql .= " AND LICPO.CCOMLICODI = $ComissaoCodigo ";
    }
    if ($ModalidadeCodigo != "") {
        $sql .= " AND LICPO.CMODLICODI = $ModalidadeCodigo ";
    }
    if ($OrgaoLicitanteCodigo != "") {
        $sql .= " AND LICPO.CORGLICODI = $OrgaoLicitanteCodigo ";
    }

    $sql .= " ORDER BY GRUPRE.EGREMPDESC, MODLIC.EMODLIDESC, COLIC.ECOMLIDESC,  LICPO.ALICPOANOP, LICPO.CLICPOPROC"; // A.ALICPOANOP

        //echo $sql;
        //exit;

        $result = $db->query($sql);
    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
    } else {
        $Rows = $result->numRows();
    }

    $GrupoDescricaoricao = "";
    if ($Rows != 0) {
        //--------------------------
//-- Marcar Final
//--------------------------
        echo "<form action=\"ConsAvisosDocumentos.php\" method=\"post\" name=\"Aviso\">\n";
        echo "<table width='100%' cellpadding=\"0\" border=\"0\">\n";
        echo "	<tr>\n";
        echo "		<td class=\"textonormal\">\n";
        echo "      <table  width='100%' border=\"0\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"#ffffff\" summary=\"\">\n";
        echo "        <tr>\n";
        echo "	      	<td class=\"textonormal\">\n";
        echo "	        	<table width='100%' border=\"1\" cellpadding=\"3\" cellspacing=\"0\" bordercolor=\"#75ADE6\" class=\"textonormal\" summary=\"\">\n";
        echo "	          	<tr>\n";
        echo "	            	<td align=\"center\" bgcolor=\"#75ADE6\" valign=\"middle\" colspan=\"7\" class=\"titulo3\">\n";
        echo "		    					RESULTADO DA PESQUISA\n";
        echo "		          	</td>\n";
        echo "		        	</tr>\n";
        $DescDet = null;
        while ($Linha = $result->fetchRow()) {
            if (!empty($Linha[16])) {
                $DescDet = $Linha[18];
            } elseif (!empty($Linha[17])) {
                $DescDet = $Linha[19];
            }

            if ($GrupoDescricaoricao != $Linha[0]) {
                $GrupoDescricaoricao = $Linha[0];
                echo "<tr><td align=\"center\" class=\"titulo3\" colspan=\"7\" bgcolor=\"#DCEDF7\">$GrupoDescricaoricao</td></tr>\n";
                $ModalidadeDescricaoricao = "";
            }
            if ($ModalidadeDescricaoricao != $Linha[1]) {
                $ModalidadeDescricaoricao = $Linha[1];
                echo "<tr><td align=\"center\" class=\"titulo3\" colspan=\"7\">$ModalidadeDescricaoricao</td></tr>\n";
                $ComissaoDescricaoricao = "";
            }
            if ($ComissaoDescricaoricao != $Linha[2]) {
                $ComissaoDescricaoricao = $Linha[2];
                echo "<tr><td class=\"titulo2\" colspan=\"7\" color=\"#000000\">$ComissaoDescricaoricao</tr></td>\n";
                echo "<tr><td class=\"textonormal\" colspan=\"7\" color=\"#000000\">$Linha[13] - TEL: $Linha[14] - FAX: $Linha[15]</tr></td>\n";
                echo "<tr><td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">PROCESSO</td>\n";
                echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">LICITAÇÃO</td>\n";
                echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">OBJETO</td>\n";
                echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">DESC DETALHADA</td>\n";
                echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">DATA/HORA ABERTURA</td>\n";
                echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">ÓRGÃO LICITANTE</td>\n";
                echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">EDITAIS</td></tr>\n";
            }
            $NProcesso               = substr($Linha[3] + 10000, 1);
            $NLicitacao            = substr($Linha[5] + 10000, 1);
            $LicitacaoDtAbertura   = substr($Linha[8], 8, 2)."/".substr($Linha[8], 5, 2)."/".substr($Linha[8], 0, 4);
            $LicitacaoHoraAbertura = substr($Linha[8], 11, 5);
            $DescDet = $DescDet == '' ? '<center> -- </center>' : $DescDet;
            echo "<tr>\n";
            echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$NProcesso/$Linha[4]</td>\n";
            echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$NLicitacao/$Linha[6]</td>\n";
            echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$Linha[7]</td>\n";
            echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$DescDet</td>\n";
            echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$LicitacaoDtAbertura<br>$LicitacaoHoraAbertura&nbsp;h</td>\n";
            echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$Linha[9]</td>\n";
            echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\">";
            echo "<img src=\"../midia/disquete.gif\" border=\"0\" onMouseOver=\"this.style.cursor='pointer'\" onclick=\"AbreDocumentos('$Objeto', '$Linha[12]','$Linha[11]','$ModalidadeCodigo','$Linha[10]','$Linha[3]','$Linha[4]');\" /></td>\n";
            echo "</tr>\n";
        }
    } else {
        ?>

     	    <!--  ******************************************************   -->
		    <!--  Exibir a mensagem de aviso de nenhuma ocorrência aqui    -->
     	    <!--  ******************************************************   -->

			<script type="text/javascript">
				  var mensagem = document.getElementById('mensagem');
				  var tr, td, brink, tx;
				  tr = document.createElement('tr');
				  td = document.createElement('td');
				  td.setAttribute('bgcolor','DCEDF7');
				  brink = document.createElement('blink');
				  tx = document.createTextNode('Atenção!');

				  brink.appendChild(tx);
				  td.appendChild(brink);
				  tr.appendChild(td);
				  mensagem.appendChild(tr);

				  tr = document.createElement('tr');
				  td = document.createElement('td');
				  tx = document.createTextNode('Nenhuma ocorrência foi selecionada.');
				  td.appendChild(tx);
				  tr.appendChild(td);

				  mensagem.appendChild(tr);
			</script>
		<?php

    }

    echo "    	  	  </table>\n";

    echo "						<input type=\"hidden\" name=\"Objeto\">";
    echo "						<input type=\"hidden\" name=\"OrgaoLicitanteCodigo\">";
    echo "						<input type=\"hidden\" name=\"ComissaoCodigo\">";
    echo "						<input type=\"hidden\" name=\"ModalidadeCodigo\">";
    echo "						<input type=\"hidden\" name=\"GrupoCodigo\">";
    echo "						<input type=\"hidden\" name=\"LicitacaoProcesso\">";
    echo "						<input type=\"hidden\" name=\"LicitacaoAno\">";

    echo "					</td>\n";
    echo "				</tr>\n";
    echo "      </table>\n";
    echo "		</td>\n";
    echo "	</tr>\n";
    echo "	<!-- Fim do Corpo -->\n";
    echo "</table>\n";
    echo "</form>\n";
    $db->disconnect();
}

//-- Fim do conteúdo do arquivo ConsAvisosResultado --------------------------
?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
</body>
</html>


<script language="javascript" type="">
<!--
document.Avisos.Objeto.focus();
//-->
</script>
