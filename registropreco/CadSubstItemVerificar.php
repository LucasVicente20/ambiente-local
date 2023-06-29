<?php
/**
 * Portal de Compras
 * 
 * Programa: CadSubstItemVerificar.php
 * Autor:    Lucas Baracho
 * Data:     14/06/2019
 * Objetivo: Tarefa Redmine 217790
 * ------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     03/07/2019
 * Objetivo: Tarefa Redmine 220040
 * ------------------------------------------------------------------------
 */

 // 220038--

// Acesso ao arquivo de funções
include "../funcoes.php";

// Executa o controle de segurança
session_start();
Seguranca();

// Variáveis com o global off
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Botao          = $_POST['Botao'];
    $acao           = $_POST['Acao'];
    $tipoItem       = $_POST['TipoItem'];
    $codigoItem     = $_POST['CodigoItem'];
    $codigoItemNovo = $_POST['CodigoItemNovo'];
}

// Identificando o programa para erro de banco de dados
$erroPrograma = "CadSubstItemVerificar.php";

// Inicia a conexão com o banco de dados
$db = Conexao();

// Define ação e nome do botão
$acaoBotao = 'Verificar';
$nomeBotao = 'Verificar';

// Determina qual a tabela e campo para realizar a pesquisa do item. Será sempre o mesmo tanto para o item atual quanto para o novo
if ($tipoItem == 'M') {
    $tabelaItemSQL        = "TBMATERIALPORTAL";
    $campoItemSQL         = "CMATEPSEQU";
    $campoItemSituacaoSQL = "CMATEPSITU";
} else {
    $tabelaItemSQL        = "TBSERVICOPORTAL";
    $campoItemSQL         = "CSERVPSEQU";
    $campoItemSituacaoSQL = "CSERVPSITU";
}

// Ação do botão Verificar
if ($Botao == 'Verificar') {
    // Verifica o preenchimento dos campos
    $Mens     = 0;
    $Mensagem = "Informe: ";

    // Verifica se alguma ação foi selecionada
    if ($acao == '') {
        $Mens      = 1;
        $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript:document.CadSubstItemVerificar.Acao.focus();\" class=\"titulo2\">A ação para ser executada</a>";
    }

    // Verifica se foi informado o item atual, o que será alterado
    if (empty($codigoItem)) {
        if ($Mens == 1) {
            $Mensagem .= ", o ";
        } else {
            $Mensagem .= "O ";
        }

        $Mens      = 1;
        $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript:document.CadSubstItemVerificar.CodigoItem.focus();\" class=\"titulo2\">código do item que será alterado</a>";
    }

    // Quando for para substituir um item, verifica se foi informado o item novo, que será o substituto
    if (empty($codigoItemNovo) && $acao == 1) {
        if ($Mens == 1) {
            $Mensagem .= ", o ";
        } else {
            $Mensagem .= "O ";
        }

        $Mens      = 1;
        $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript:document.CadSubstItemVerificar.CodigoItemNovo.focus();\" class=\"titulo2\">código do item substituto</a>";
    }

    // Quando for para substituir um item, verifica se não está sendo inserido como substituto o código do mesmo item que será alterado, considerando que ambos os campos estão preenchidos
    if (!empty($codigoItem) && !empty($codigoItemNovo) && $acao == 1 && $codigoItem == $codigoItemNovo) {
        $Mens     = 1;
        $Tipo     = 2;
        $Mensagem = "O item substituto não pode ter o mesmo código do item atual";
    }

    // Substituir item
    if ($Mens == 0) {
        // Verifica se existem atas ativas e vigentes com o item que será alterado
        $res2 = pesquisaAtas($tabelaItemSQL, $campoItemSQL, $codigoItem);
         
        if ($res2->numRows() == 0) {
            $Mens     = 1;
            $Tipo     = 2;
            $Mensagem = "Não existem atas cadastradas com o item " . $codigoItem;
        }

        if ($acao == 1 && $Mens == 0) {
            // Quando for para substituir um item, verifica se o item novo existe e está ativo nas tabelas do CADUM/CADUS
            $sq1  = "SELECT COUNT(*) ";
            $sq1 .= "FROM   SFPC." . $tabelaItemSQL;
            $sq1 .= " WHERE " . $campoItemSQL . " = " . $codigoItemNovo;
            $sq1 .= "       AND " . $campoItemSituacaoSQL . " = 'A' ";

            $res1 = $db->query($sq1);

            if (PEAR::isError($res1)) {
                ExibeErroBD("$erroPrograma\nLinha: ".__LINE__."\nSql: $sq1");
            } else {
                $Linha = $res1->fetchRow();

                if ($Linha[0] == 0) {
                    $Mens     = 1;
                    $Tipo     = 2;
                    $Mensagem = "O item substituto (" . $codigoItemNovo . ") não existe ou está inativo nas tabelas CADUM/CADUS";
                }
            }

            if ($Mens == 0) {
                // Define ação e nome do botão quando for para substituir um item
                $acaoBotao = 'Alterar';
                $nomeBotao = 'Substituir';
            }
        }
        
        if ($acao == 2 && $Mens == 0) {
            // Define a ação e o nome do botão
            $acaoBotao = 'Alterar';
            $nomeBotao = 'Inativar';
        }        
    }
}

if ($Botao == 'Alterar') {
    $res2 = pesquisaAtas($tabelaItemSQL, $campoItemSQL, $codigoItem);

    while ($Linha2 = $res2->fetchRow()) {
        $codAta = $Linha2[0];

        $sql  = "UPDATE SFPC.TBITEMATAREGISTROPRECONOVA ";
    
        if ($acao == 1) {
            $sql .= "SET " . $campoItemSQL . " = " . $codigoItemNovo;
        } else {
            $sql .= "SET FITARPSITU = 'I' ";
        }

        $sql .= " WHERE CARPNOSEQU = " . $codAta;
        $sql .= "       AND " . $campoItemSQL . " = " . $codigoItem;

        $res = $db->query($sql);

        if (PEAR::isError($res)) {
            ExibeErroBD("$erroPrograma\nLinha: ".__LINE__."\nSql: $sql");
        } else {
            $Mens     = 1;
            $Tipo     = 1;
            $Mensagem = "Item alterado com sucesso";
        }
    }
    
    // Limpa os campos
    $acao           = '';
    $tipoItem       = '';
    $codigoItem     = '';
    $codigoItemNovo = '';
}

if ($Botao == 'Limpar') {
    $acao           = '';
    $tipoItem       = '';
    $codigoItem     = '';
    $codigoItemNovo = '';
}

function valorAtaFormatado($codOrgaoAta, $numAta, $anoAta) {
    $db = Conexao();

    $sql  = "SELECT CCP.CCENPOCORG, CCP.CCENPOUNID, CCP.CORGLICODI ";
    $sql .= "FROM   SFPC.TBCENTROCUSTOPORTAL CCP ";
    $sql .= "WHERE  CCP.CORGLICODI = " . $codOrgaoAta;    

    $res = $db->query($sql);

    if (PEAR::isError($res)) {
        ExibeErroBD("$erroPrograma\nLinha: ".__LINE__."\nSql: $sql");
    } else {
        $Linha = $res->fetchRow();
        $orgaoCC = $Linha[0];
        $unidCC  = $Linha[1];
    }

    $centroCusto = $orgaoCC . str_pad($unidCC, 2, '0', STR_PAD_LEFT);

    $numAtaFormatado = $orgaoCC . str_pad($unidCC, 2, '0', STR_PAD_LEFT) . "." . str_pad($numAta, 4, "0", STR_PAD_LEFT) . "/" . $anoAta;

    $db->disconnect();

    return $numAtaFormatado;
}

// Pesquisa por atas ativas e vigentes que possuem o item atual
function pesquisaAtas($tabelaItemSQL, $campoItemSQL, $codigoItem) {
    $db = Conexao();
    
    $sq2  = "SELECT DISTINCT ARPI.CARPNOSEQU, ";
    $sq2 .= "       ARPI.CLICPOPROC, ";
    $sq2 .= "       ARPI.ALICPOANOP, ";
    $sq2 .= "       CL.ECOMLIDESC, ";
    $sq2 .= "       ARPI.CORGLICODI, ";
    $sq2 .= "       OL.EORGLIDESC, ";
    $sq2 .= "       ARPI.CARPINCODN, ";
    $sq2 .= "       ARPI.AARPINANON ";
    $sq2 .= "FROM   SFPC.TBATAREGISTROPRECOINTERNA ARPI ";
    $sq2 .= "       LEFT JOIN SFPC.TBATAREGISTROPRECONOVA ARPN ON ARPN.CARPNOSEQU = ARPI.CARPNOSEQU ";
    $sq2 .= "       LEFT JOIN SFPC.TBITEMATAREGISTROPRECONOVA IARPN ON IARPN.CARPNOSEQU = ARPI.CARPNOSEQU ";
    $sq2 .= "       JOIN SFPC." . $tabelaItemSQL . " ITEM ON ITEM." . $campoItemSQL . " = IARPN." . $campoItemSQL;
    $sq2 .= "       JOIN SFPC.TBCOMISSAOLICITACAO CL ON CL.CCOMLICODI = ARPI.CCOMLICODI ";
    $sq2 .= "       JOIN SFPC.TBORGAOLICITANTE OL ON OL.CORGLICODI = ARPI.CORGLICODI ";
    $sq2 .= "WHERE  ARPN.CARPNOTIAT = 'I' ";
    $sq2 .= "       AND ARPI.FARPINSITU = 'A' ";
    $sq2 .= "       AND IARPN." . $campoItemSQL . " = " . $codigoItem;
    $sq2 .= "       AND now() BETWEEN ARPI.TARPINDINI AND ARPI.TARPINDINI + (ARPI.AARPINPZVG::text || 'month')::interval ";
    $sq2 .= "ORDER BY OL.EORGLIDESC ASC, ARPI.AARPINANON DESC, ARPI.CARPINCODN ASC ";

    $res2 = $db->query($sq2);

    if (PEAR::isError($res2)) {
        ExibeErroBD("$erroPrograma\nLinha: ".__LINE__."\nSql: $sq2");
    }

    return $res2;
}

// Encerra a conexão com o banco de dados
//$db->disconnect();
?>
<html>
<?
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.SubstItem.Botao.value=valor;
	document.SubstItem.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif">
    <script language="JavaScript" src="../menu.js"></script>
    <script language="JavaScript">Init();</script>
    <form action="CadSubstItemVerificar.php" method="post" name="SubstItem">
        <br><br><br><br><br>
        <table cellpadding="3" border="0">
            <!-- Caminho -->
            <tr>
                <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
                <td align="left" class="textonormal" colspan="2">
                    <font class="titulo2">|</font>
                    <a href="../index.php"><font color="#000000">Página Principal</font></a> > Reg. Preços > Ata Interna > Alterar Itens
                </td>
            </tr>
            <!-- Fim do caminho-->
	        <!-- Erro -->
	        <tr>
	            <td width="100"></td>
	            <td align="left" colspan="2">
	  	            <?php if ($Mens == 1) { ExibeMens($Mensagem, $Tipo, 1); } ?>
	            </td>
	        </tr>
	        <!-- Fim do erro -->
	        <!-- Corpo -->
	        <tr>
		        <td width="100"></td>
		        <td class="textonormal">
      		        <table border="0" cellspacing="0" cellpadding="3">
        		        <tr>
	      			        <td class="textonormal">
	        			        <table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
	          				        <tr>
	            				        <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	            					        ALTERAR ITENS DAS ATAS DE REGISTRO DE PREÇO INTERNAS
	            				        </td>
		        			        </tr>
	  	      				        <tr>
	    	      				        <td class="textonormal">
	      	    					        <p>
                                                Para realizar alterações em um determinado item, informe qual a ação que deseja executar e qual será o item alterado.
                                                <br>
                                                Será alterado este item em todas as atas ativas e vigentes, de acordo com a ação selecionada pelo usuário.
                                                <br>
                                                Após o preenchimento dos campos obrigatórios, clique em 'Verificar' para que seja validado a ação e sejam carregados dados das atas à serem alteradas, para confirmação.
	        	    				        </p>
	          					        </td>
		        			        </tr>
		        			        <tr>
	  	        				        <td>
	    	      					        <table class="textonormal" border="0" align="left" summary="">
                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Ação*</td>
                                                    <td class="textonormal">
                                                        <select name="Acao" id="Acao" class="textonormal" onChange="javascript:enviar('Acao');">
                                                            <option value=''>Selecione uma ação...</option>      
                                                            <option value="1" <?php if ($acao == '1') { echo 'selected'; } ?>>Substituir item</option>
                                                            <option value="2" <?php if ($acao == '2') { echo 'selected'; } ?>>Inavitar item</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Tipo de item</td>
                                                    <td class="textonormal">
                                                        <select name="TipoItem" id="TipoItem" class="textonormal">
                                                            <option value="M" <?php if ($tipoItem == 'M') { echo 'selected'; } ?>>Material</option>
                                                            <option value="S" <?php if ($tipoItem == 'S') { echo 'selected'; } ?>>Serviço</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Código do item*</td>
                                                    <td class="textonormal">
                                                        <input type="text" name="CodigoItem" value="<?php echo $codigoItem; ?>" size="6" maxlength="8" class="textonormal">
                                                    </td>
                                                </tr>
                                                <?php
                                                if ($acao == 1) {
                                                    ?>
                                                    
                                                    <tr>
                                                        <td class="textonormal" bgcolor="#DCEDF7">Código do item substituto*</td>
                                                        <td class="textonormal">
                                                            <input type="text" name="CodigoItemNovo" value="<?php echo $codigoItemNovo; ?>" size="6" maxlength="8" class="textonormal">
                                                        </td>
                                                    </tr>
                                                    <?php
                                                }
                                                ?>
	            					        </table>
		          				        </td>
		        			        </tr>
                                    <?php
                                    if ($Botao == 'Verificar' && $Mens == 0) {
                                        ?>
                                        <tr>
	    	      				            <td>
                                                <table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF" width="100%">
                                                    <tr>
                                                        <td class="titulo3" bgcolor="#F7F7F7" width="20%" align="center">Nº da ata</td>
                                                        <td class="titulo3" bgcolor="#F7F7F7" width="40%" align="center">Órgão</td>
                                                        <td class="titulo3" bgcolor="#F7F7F7" width="40%" align="center">Processo licitatório</td>
                                                    </tr>
                                                    <?php
                                                    while ($Linha2 = $res2->fetchRow()) {
                                                        $codAta           = $Linha2[0];
                                                        $numProcesso      = $Linha2[1];
                                                        $anoProcesso      = $Linha2[2];
                                                        $comissaoProcesso = $Linha2[3];
                                                        $codOrgaoAta      = $Linha2[4];
                                                        $orgaoAta         = $Linha2[5];
                                                        $numAta           = $Linha2[6];
                                                        $anoAta           = $Linha2[7];

                                                        // Formata o número da ata
                                                        $numAtaFormatado = valorAtaFormatado($codOrgaoAta, $numAta, $anoAta);

                                                        // Formata os dados do processo licitatório: [PROCESSO]/[ANO_PROCESSO] - [DESCRIÇÃO COMISSÃO LICITAÇÃO]
                                                        $dadosProcesso = $numProcesso . "/" . $anoProcesso . " - " . $comissaoProcesso;
                                                        ?>
                                                        <tr>
                                                            <td class="texto" align="center"><?=$numAtaFormatado?></td>
                                                            <td class="texto" align="center"><?=$orgaoAta?></td>
                                                            <td class="texto" align="center"><?=$dadosProcesso?></td>
                                                        </tr>
                                                        <?php
                                                    }
                                                    ?>
                                                </table>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                    <tr>
	    	      				        <td>
	   	  	  						        <table class="textonormal" border="0" align="right" summary="">
	        	      					        <tr>
	          	      						        <td>
	          	      							        <input type="button" value="<?=$nomeBotao?>" class="Botao" onClick="javascript:enviar('<?=$acaoBotao?>');">
                                                        <input type="button" value="Limpar" class="Botao" onClick="javascript:enviar('Limpar');">
	     										        <input type="hidden" name="Botao" value="">
	          	      						        </td>
	            	  					        </tr>
	            					        </table>
		          				        </td>
                                    </tr>
    	  	  			        </table>
					        </td>
				        </tr>
			        </table>
		        </td>
	        </tr>
	        <!-- Fim do Corpo -->
        </table>
    </form>
</body>
</html>
<script language="JavaScript">
    <!--
    document.SubstItem.Acao.focus();
    //-->
</script>