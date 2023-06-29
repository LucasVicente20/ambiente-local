<?php
/**
 * Prefeitura do Recife
 * Portal de Compras
 * 
 * Programa: CadAtaRegistroPrecoManter.php
 * Autor:    Rossana Lira
 * Data:     27/03/2007
 * Objetivo: Programa de inclusão/exclusão das atas de registro de preço
 * -------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     18/01/2023
 * Objetivo: Tarefa Redmine 277806
 * -------------------------------------------------------------------------------------------
 */

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/registropreco/CadAtaRegistroPrecoSelecionar.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Critica              = $_POST['Critica'];
    $Botao                = $_POST['Botao'];
    $Processo             = $_POST['Processo'];
    $ProcessoAno          = $_POST['ProcessoAno'];
    $ComissaoCodigo       = $_POST['ComissaoCodigo'];
    $OrgaoLicitante       = $_POST['OrgaoLicitante'];
    $FaseCodigo           = $_POST['FaseCodigo'];
    $QuantArquivos        = $_POST['QuantArquivos'];
    $AtaRegistroPreco     = $_POST['AtaRegistroPreco'];
    $Grupo                = $_POST['Grupo'];
} else {
    $Grupo                = $_GET['Grupo'];
    $Processo             = $_GET['Processo'];
    $ProcessoAno          = $_GET['ProcessoAno'];
    $ComissaoCodigo       = $_GET['ComissaoCodigo'];
    $OrgaoLicitante       = $_GET['OrgaoLicitante'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "CadAtaRegistroPrecoManter.php";

# Redireciona para a página de excluir #
if ($Botao == "Excluir") {
    if ($QuantArquivos > 0) {
        $db = Conexao();

        for ($Row = 0 ; $Row < $QuantArquivos ; $Row++) {
            if ($AtaRegistroPreco[$Row] != "") {
                $db->query("BEGIN TRANSACTION");

                $sql    = "DELETE FROM SFPC.TBAtaRegistroPreco ";
                $sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
                $sql   .= "   AND CGREMPCODI = $Grupo AND CCOMLICODI = $ComissaoCodigo ";
                $sql   .= "   AND CATARPCODI = ".$AtaRegistroPreco[$Row];

                $result = $db->query($sql);

                if (PEAR::isError($result)) {
                    $db->query("ROLLBACK");
                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                } else {
                    $Arquivo = $GLOBALS["CAMINHO_UPLOADS"]."registropreco/"."ATAREGISTROPRECO".$Grupo."_".$Processo."_".$ProcessoAno."_".$ComissaoCodigo."_".$OrgaoLicitante."_".$AtaRegistroPreco[$Row];

                    if (file_exists($Arquivo)) {
                        if (unlink($Arquivo)) {
                            $Mens = 1;
                            $Tipo = 1;
                            $Mensagem = "Atas(s) Excluída(s) com Sucesso";
                        } else {
                            $Mens = 1;
                            $Tipo = 2;
                            $Mensagem = "Erro na Exclusão do Arquivo";
                        }
                    } else {
                        $Mens = 1;
                        $Tipo = 1;
                        $Mensagem = "Atas(s) Excluída(s) com Sucesso";
                        $AtaRegistroPrecoDescricao = "";
                    }

                    $db->query("COMMIT");
                    $db->query("END TRANSACTION");
                }
            }
        }

        $db->disconnect();
    }
} elseif ($Botao == "Voltar") {
    header("location: CadAtaRegistroPrecoSelecionar.php");
    exit();
} else {
    # Critica dos Campos #
    if ($Critica == 1) {
        $_FILES['NomeArquivo']['name'] = RetiraAcentos($_FILES['NomeArquivo']['name']);

        $Tamanho = 5242880; /* 5MB */
            
        if (($_FILES['NomeArquivo']['size'] > $Tamanho) || ($_FILES['NomeArquivo']['size'] == 0)) {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }
                
            $Mens = 1;
            $Tipo = 2;
            $Kbytes = $Tamanho/1024;
            $Kbytes = (int) $Kbytes;
            $Mensagem .= "Este arquivo é muito grande ou está vazio. Tamanho Máximo: $Kbytes Kb";
        }
            
        $AtaRegistroPrecoDescricao = strtoupper2(trim($AtaRegistroPrecoDescricao));
            
        if (strlen($AtaRegistroPrecoDescricao) > 200) {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }

            $Mens      = 1;
            $Tipo      = 2;
            $Mensagem .= "Observação das Atas com até 200 Caracteres ( atualmente com ". strlen($AtaRegistroPrecoDescricao) ." )";
        }

        $Tam = strlen($_FILES['NomeArquivo']['name']);

        if (strlen($_FILES['NomeArquivo']['name']) > 100) {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }

            $Mens      = 1;
            $Tipo      = 2;
            $Mensagem .= "Nome do Arquivo com até 100 Caracateres ( atualmente com ".strlen($_FILES['NomeArquivo']['name'])." )";
        }

        if ($Mens == 0) {
            $db = Conexao();

            $sql    = "SELECT MAX(CATARPCODI) FROM SFPC.TBATAREGISTROPRECO ";
            $sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
            $sql   .= "   AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = $Grupo AND CORGLICODI = $OrgaoLicitante ";

            $result = $db->query($sql);

            if (PEAR::isError($result)) {
                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
            } else {
                $Linha = $result->fetchRow();
                $AtaRegistroPrecoCod = $Linha[0] + 1;

                # Insere na tabela de Atas do Registro de Preço #
                $db->query("BEGIN TRANSACTION");

                $sql       = "INSERT INTO SFPC.TBATAREGISTROPRECO( ";
                $sql      .= "CLICPOPROC, ALICPOANOP, CGREMPCODI, ";
                $sql      .= "CCOMLICODI, CORGLICODI, CATARPCODI, EATARPNOME, ";
                $sql      .= "TATARPDATA, TATARPULAT ";
                $sql      .= ") VALUES ( ";
                $sql      .= "$Processo, $ProcessoAno, $Grupo, ";
                $sql      .= "$ComissaoCodigo, $OrgaoLicitante, $AtaRegistroPrecoCod, '" . $_FILES['NomeArquivo']['name'] . "', ";
                $sql      .= "'" . date("Y-m-d") . "', '" . date("Y-m-d H:i:s") . "' )";

                $result   = $db->query($sql);

                if (PEAR::isError($result)) {
                    $db->query("ROLLBACK");
                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                } else {
                    $db->query("COMMIT");
                    $db->query("END TRANSACTION");

                    $Arquivo = $GLOBALS["CAMINHO_UPLOADS"]."registropreco/ATAREGISTROPRECO".$Grupo."_".$Processo."_".$ProcessoAno."_".$ComissaoCodigo."_".$OrgaoLicitante."_".$AtaRegistroPrecoCod;

                    if (file_exists($Arquivo)) {
                        unlink($Arquivo);
                    }

                    if (@move_uploaded_file($_FILES['NomeArquivo']['tmp_name'], $Arquivo)) {
                        $Mens              = 1;
                        $Tipo              = 1;
                        $Mensagem          = "Ata Carregada com Sucesso";
                        $AtaRegistroPrecoDescricao = "";
                    } else {
                        $Mens     = 1;
                        $Tipo     = 2;
                        $Mensagem = "Erro no Carregamento do Arquivo";
                    }
                }
            }

            $db->disconnect();
        }
    }
}

# Busca descrição da comissão #
$db = Conexao();

$sql = "SELECT A.ECOMLIDESC FROM SFPC.TBCOMISSAOLICITACAO A WHERE A.CCOMLICODI = " . $ComissaoCodigo;

$result = $db->query($sql);

if (PEAR::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
} else {
    $Linha = $result->fetchRow();
    $ComissaoDescricao = $Linha[0];
}
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
    <!--
    function enviar(valor) {
	    document.AtaRegistroPreco.Botao.value=valor;
	    document.AtaRegistroPreco.submit();
    }

    <?php MenuAcesso(); ?>
    //-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
    <script language="JavaScript" src="../menu.js"></script>
    <script language="JavaScript">Init();</script>
    <form enctype="multipart/form-data" action="CadAtaRegistroPrecoManter.php" method="post" name="AtaRegistroPreco">
        <br><br><br><br><br>
        <table cellpadding="3" border="0" summary="">
	        <!-- Caminho -->
	        <tr>
		        <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		        <td align="left" class="textonormal" colspan="2">
			        <font class="titulo2">|</font>
			        <a href="../index.php"><font color="#000000">Página Principal</font></a> > Registro Preço > Ata Interna
                </td>
	        </tr>
	        <!-- Fim do Caminho-->
	        <!-- Erro -->
	        <?php
            if ($Mens == 1) {
                ?>
                <tr>
  	                <td width="150"></td>
		            <td align="left" colspan="2"><?php ExibeMens($Mensagem, $Tipo, 1); ?></td>
	            </tr>
	            <?php 
            }
            ?>
	        <!-- Fim do Erro -->
	        <!-- Corpo -->
	        <tr>
		        <td width="150"></td>
		        <td class="textonormal"><br>
			        <table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
                        <tr>
                            <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	        	                MANTER - ATA DE REGISTRO DE PREÇO
                            </td>
                        </tr>
                        <tr>
                            <td class="textonormal">
		                        <p align="justify">
		                            Para incluir a Ata, localize o arquivo e clique no botão "Incluir". Para apagar a(s) Atas(s), selecione-a(s) e clique no botão "Excluir".
		                        </p>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table border="0" summary="">
                                    <tr>
	                                    <td class="textonormal" bgcolor="#DCEDF7" height="20">Comissão </td>
	                                    <td class="textonormal">
                                            <?php echo $ComissaoDescricao; ?>
                                        </td>
	                                </tr>
 							        <tr>
	                                    <td class="textonormal" bgcolor="#DCEDF7" height="20">Processo </td>
	                                    <td class="textonormal">
                                            <?php echo substr($Processo + 10000, 1); ?>
                                        </td>
	                                </tr>
	                                <tr>
	                                    <td class="textonormal" bgcolor="#DCEDF7" height="20">Ano </td>
	                                    <td class="textonormal">
                                            <?php echo $ProcessoAno; ?>
                                        </td>
	                                </tr>
							        <tr>
	                                    <td class="textonormal" bgcolor="#DCEDF7" height="20">Arquivo* </td>
								        <td class="textonormal">
									        <input type="file" name="NomeArquivo" class="textonormal">
									        <input type="hidden" name="Grupo" value="<?php echo $Grupo; ?>">
									        <input type="hidden" name="Processo" value="<?php echo $Processo; ?>">
									        <input type="hidden" name="ProcessoAno" value="<?php echo $ProcessoAno; ?>">
									        <input type="hidden" name="ComissaoCodigo" value="<?php echo $ComissaoCodigo; ?>">
									        <input type="hidden" name="OrgaoLicitante" value="<?php echo $OrgaoLicitante; ?>">
									        <input type="hidden" name="Critica" value="1">
								        </td>
	                                </tr>
							        <tr>
	                                    <td class="textonormal" bgcolor="#DCEDF7" valign="top"> Atas Cadastradas </td>
								        <td class="textonormal">
									        <table border="0" width="100%" summary="">
									            <?php
                                                $sql    = "SELECT CATARPCODI, EATARPNOME, TATARPDATA ";
                                                $sql   .= "  FROM SFPC.TBATAREGISTROPRECO ";
                                                $sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
                                                $sql   .= "   AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = $Grupo ";

                                                $result = $db->query($sql);

                                                if (PEAR::isError($result)) {
                                                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                                } else {
                                                    $Rows = $result->numRows();

                                                    if ($Rows > 0) {
                                                        echo "<tr><td class=\"textonormal\"> (Nome da Ata - Data)</td></tr>\n";

                                                        while ($Linha = $result->fetchRow()) {
                                                            $cont++;
                                                            $row  = $cont-1;
                                                            $Data = substr($Linha[2], 8, 2)."/".substr($Linha[2], 5, 2)."/".substr($Linha[2], 0, 4);

                                                            echo "<tr>\n";
                                                            echo "	<td class=\"textonormal\"><input type=checkbox name=\"AtaRegistroPreco[$row]\" value=\"".$Linha[0]."\"> $Linha[1] - $Data<br> </td> \n";
                                                            echo "</tr>\n";
                                                            echo "<tr>\n";
                                                            echo "	<td class=\"textonormal\" valign=top>".str_replace("\n", "<br>", $Linha[3])."</td>";
                                                            echo "</tr>\n";
                                                        }
                                                    } else {
                                                        echo "<tr>\n";
                                                        echo "	<td class=\"textonormal\" height=\"20\">\n";
                                                        echo "		Nenhum Ata Cadastrada!\n";
                                                        echo "	</td>\n";
                                                        echo "</tr>\n";
                                                    }
                                                }

                                                $db->disconnect();
                                                ?>
									        </table>
								        </td>
	                                </tr>
                                </table>
                                <input type="hidden" name="QuantArquivos" value="<?php echo $Rows; ?>">
                            </td>
                        </tr>
                        <tr>
 	                        <td class="textonormal" align="right">
                                <input type="button" value="Incluir" class="botao" onclick="javascript:enviar('Incluir');">
						        <input type="button" value="Excluir" class="botao" onclick="javascript:enviar('Excluir');">
                                <input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
                                <input type="hidden" name="Botao" value="">
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