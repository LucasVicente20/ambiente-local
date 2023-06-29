<?php
/**
 * Portal de Compras
 * 
 * Programa: CadPregaoPresencialPrecoInicialPorFornecedor.php
 * Autor:    Pitang Agile TI - Caio Coutinho
 * Data:     22/01/2019
 * Objetivo: Tarefa Redmine 208468
 * -------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     09/05/2019
 * Objetivo: Tarefa Redmine 216422
 * -------------------------------------------------------------------------
 */

header("Content-Type: text/html; charset=UTF-8",true);

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/tabelasbasicas/TabUsuarioAlterar.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $UsuarioCodigo                        = $_POST['UsuarioCodigo'];
    $Critica                              = $_POST['Critica'];
    $_SESSION['Botao']					  = $_POST['Botao'];
    $_SESSION['CodSituacaoClassificacao'] = $_POST['CodSituacaoClassificacao'];
    $_SESSION['MotivoSituacao']			  = strtoupper ( $_POST['MotivoSituacao']);
} else {
    $Critica       						  = $_GET['Critica'];
    $Mensagem      						  = urldecode($_GET['Mensagem']);
    $Mens          						  = $_GET['Mens'];
    $Tipo          						  = $_GET['Tipo'];
    $_SESSION['CodFornecedorSelecionado'] = $_GET['CodFornecedorSelecionado'];
}

if (($_POST['MotivoSituacao'] == null or $_POST['MotivoSituacao'] == "") and $_POST['CodSituacaoClassificacao'] <> 1) {
	$_SESSION['MotivoSituacao'] = $Linha[0];
}

$TamanhoMaximoMotivo = 500;

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "CadPregaoPresencialPrecoInicialPorFornecedor.php";

if ($Critica == 1) {
    # Critica dos Campos #
    $Mens     = 0;
    $Mensagem = "Informe: ";
}

if ($_POST['Botao'] == 'Salvar') {
    $postFornecedores  = $_POST['fornecedores'];
    $postPropostas     = $_POST['propostas'];
    $postTipo          = $_POST['tipo'];
    $postPrecoInicial  = $_POST['precoInicial'];
    $valores           = $fornecedoresPropostas = array();

    // Id fornecedor - id lote (insert)/id preco inicial(update) - valor da proposta
    foreach ($postFornecedores as $key => $value) {
        $fornecedoresPropostas[$value] = $postPropostas[$value];
    }

    // Verificar empate
    $empate = false;
    
    foreach ($postPropostas as $key => $value) {
        foreach ($value as $valor) {
            if(in_array($valor, $valores) && $valor <> 0) {
                $_SESSION['Mensagem'] .= " Houve empate, a mesma deverá ser solucionada na tela de Preços Iniciais por Lote.";
                $empate = true;
                break;
            } else {
                $valores[] = $valor;
            }
        }

        if ($empate) {
            break;
        }
    }

    if (true) { // TODO verificar condição true
        # Recebe o último código de Preço Inicial# TODO remover
        $db  = Conexao();

        $sql = "SELECT MAX(CPREGPSEQU) FROM SFPC.TBPREGAOPRESENCIALPRECOINICIAL";

        $res = $db->query($sql);

        if (PEAR::isError($res)) {
            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
        } else {
            $LinhaPrecoInicialId = $res->fetchRow();
            $CodigoPrecoInicial  = $LinhaPrecoInicialId[0] + 1;
        }
        
        foreach ($fornecedoresPropostas as $key => $value) {
            foreach ($value as $key_ => $value_) {

                if ($value_ == '') {
                    $value_ = '0,0000';
                }

                if (empty($postPrecoInicial[$key][$key_])) {
                    $sql  = "INSERT INTO SFPC.TBPREGAOPRESENCIALPRECOINICIAL ( ";
                    $sql .= "CPREGPSEQU, CPREGTSEQU, CPREGFSEQU, VPREGPVALI, FPREGPALAN, CPREGPOEMP, DPREGPCADA, TPREGPULAT) VALUES ( ";
                    $sql .= "$CodigoPrecoInicial, $key_, $key, '".moeda2float($value_)."', 1, 0, '".date("Y-m-d")."', '".date("Y-m-d H:i:s")."' ";
                    $sql .= ")";

                    $result = $db->query($sql);
                    
                    if (PEAR::isError($result)) {
                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                    }
                    
                    $CodigoPrecoInicial++;
                } else {
                    $sql  = "UPDATE SFPC.TBPREGAOPRESENCIALPRECOINICIAL SET ";
                    $sql .= "VPREGPVALI = '".moeda2float($value_)."', ";
                    $sql .= "TPREGPULAT = '".date("Y-m-d H:i:s")."' ";
                    $sql .= "WHERE CPREGPSEQU = " . $postPrecoInicial[$key][$key_];

                    $result = $db->query($sql);

                    if (PEAR::isError($result)) {
                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                    }
                }
            }
        }

        $_SESSION['Mens']      = 1;
        $_SESSION['Tipo']      = 1;
        $_SESSION['Mensagem'] .= " Propostas inseridas com sucesso";
        
        header("Location: ./CadPregaoPresencialPrecoInicialPorFornecedor.php");
        die();
    }

    $db->disconnect();
}

// Lotes
$lotesPregao = array();

$db     = Conexao();

$sql    = "SELECT DISTINCT 	pl.cpregtnuml, pl.epregtdesc, pl.cpregtsequ ";
$sql   .= "  FROM 		   	sfpc.tbpregaopresenciallote pl";
$sql   .= "  WHERE 			pl.cpregasequ = ".$_SESSION['PregaoCod']." ";
$sql   .= "  ORDER BY 		pl.cpregtnuml";

$result = $db->query($sql);

if (PEAR::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
} else {
    $ComissaoCodigoAnt = "";
    
    while ($Linha = $result->fetchRow()) {
        $lotesPregao[] = $Linha;
    }
}

$db->disconnect();

// Verificar propostas cadastradas
function precoIncial($fornecedor, $lote) {
    $db = Conexao();
    
    $sql = "SELECT  CPREGPSEQU, VPREGPVALI 
            FROM    SFPC.TBPREGAOPRESENCIALPRECOINICIAL 
            WHERE   CPREGFSEQU = $fornecedor AND CPREGTSEQU = $lote
            ORDER BY CPREGPSEQU DESC";

    $result = resultLinhaUnica($db->query($sql));

    $db->disconnect();

    return $result;
}
?>
<html>
<head>
    <title>Portal de Compras - Incluir Fornecedor</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <script language="javascript" src="../import/jquery/jquery-1.7.2.min.js" type="text/javascript"></script>
    <script language="javascript" src="../import/jquery/jquery.maskmoney.js" type="text/javascript"></script>
    <script language="javascript" src="../import/jquery/jquery.maskedinput.js" type="text/javascript"></script>
    <script language="javascript" src="../funcoes.js" type="text/javascript"></script>
    <script language="javascript" type="text/javascript">
        <!--
        function checktodos(){
	        document.CadPregaoPresencialPrecoInicialPorFornecedor.Subclasse.value = '';
	        document.CadPregaoPresencialPrecoInicialPorFornecedor.submit();
        }

        function enviar(valor){
            document.CadPregaoPresencialPrecoInicialPorFornecedor.Botao.value = valor;
    
            /*if (valor == 'Salvar') {
                validar = validarPropostas();
                
                if(validar == 'false') {
                    alert('Informe o valor de todas as Propostas');
                    return false;
                }
            }*/

            document.CadPregaoPresencialPrecoInicialPorFornecedor.submit();
        }

        function validarPropostas() {
            retorno = 'true';
            $('.propostas').each(function(i) {
                
                if($(this).val() == "") {
                    retorno = 'false';
                }
            });

            return retorno;
        }

        function limpar() {
            $('.propostas').each(function(i) {
                $(this).val('0,0000')
            });
        }

        function AbreJanela(url,largura,altura) {
	        window.open(url,'paginadetalhe','status=no,scrollbars=yes,left=45,top=150,width='+largura+',height='+altura);
        }

        function voltar(){
	        self.close();
        }
        //-->
    </script>
    <link rel="stylesheet" type="text/css" href="../estilo.css">
</head>
<body background="../midia/bg.jpg" marginwidth="0" marginheight="0">
    <form action="CadPregaoPresencialPrecoInicialPorFornecedor.php" method="post" name="CadPregaoPresencialPrecoInicialPorFornecedor">
    <input type="hidden" name="tipo" value="insert" />
    <table cellpadding="3" border="0" summary="" width="100%">
    	<!-- Erro -->
	    <tr>
		    <td>
                <?php
                if ($_SESSION['Mens'] != 0) {
                    ExibeMens($_SESSION['Mensagem'],$_SESSION['Tipo'],$_SESSION['Virgula']);
                }
                $_SESSION['Mens'] = null;
                $_SESSION['Tipo'] = null;
                $_SESSION['Mensagem'] = null
			    ?>
		    </td>
	    </tr>
	    <!-- Fim do Erro -->
	    <!-- Corpo -->
	    <tr>
		    <td class="textonormal">
			    <table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF" >
                    <tr>
                        <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
                            PREÇO INICIAL POR FORNECEDOR - FORNECEDORES
                            <?php
                            $db              = Conexao();
                            $PregaoCod 		 = $_SESSION['PregaoCod'];
                            $sqlSolicitacoes = "SELECT	FN.APREGFCCGC, FN.APREGFCCPF, FN.NPREGFRAZS, FN.NPREGFNOMR, FN.APREGFNURG, FN.EPREGFSITU, FN.CPREGFSEQU, NPREGFORGU, FN.FPREGFMEPP
                                                FROM 	SFPC.TBPREGAOPRESENCIALFORNECEDOR FN
                                                WHERE	FN.CPREGASEQU  = $PregaoCod 
                                                ORDER BY fn.npregfrazs ASC, fn.npregfnomr ASC";

                            $resultFornecedores = $db->query($sqlSolicitacoes);

                            if (PEAR::isError($resultFornecedores)) {
                                ExibeErroBD("$ErroPrograma\nLinhaPrecoInicial: ".__LINE__."\nSql: $sqlSolicitacoes");
                            }

                            $LinhaPrecoInicial = $resultFornecedores->fetchRow();
                            $QuantidadeFornecedores = 0;
                            $QuantidadeFornecedores = $resultFornecedores->numRows();

                            $db->disconnect();
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="textonormal">
                            <p align="justify">
                            Para incluir e classificar os preços iniciais de um Fornecedor, preencha o Campo de Proposta
                            de todos os fornecedores e clique no botão "Salvar". Caso o Fornecedor não cote nenhum preço, deve-se deixar o valor
                            "0.00".
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table border="0" summary="" width="100%">
                                <tr>
                                    <td class="textonormal" bgcolor="#FFFFFF">
                                        <table border="0" width="100%" summary="">
                                            <tr>
                                                <td colspan="2">
                                                    <table cellpadding="0" cellspacing="0" border="0" bordercolor="#75ADE6" width="100%" summary="">
                                                        <tr bgcolor="#bfdaf2">
                                                            <td>
                                                                <table id="scc_material" summary="" bgcolor="#bfdaf2" border="1" bordercolor="#75ADE6" width="100%">
                                                                    <tbody>
                                                                        <?php
                                                                        $ContadorPrecosParticipantes = 0;
                                                                        
                                                                        for ($itr = 0; $itr < $QuantidadeFornecedores; ++ $itr) {
                                                                            ?>
                                                                            <tr>
                                                                                <td colspan="17" class="titulo3 itens_material" align="center" bgcolor="#75ADE6" valign="middle">
                                                                                    <?php echo $LinhaPrecoInicial[2] ?> -
                                                                                    <?php
                                                                                    echo ($LinhaPrecoInicial[1] == "" ? (substr($LinhaPrecoInicial[0], 0, 2).'.'.substr($LinhaPrecoInicial[0], 2, 3).'.'.substr($LinhaPrecoInicial[0], 5, 3).'/'.substr($LinhaPrecoInicial[0], 8, 4).'-'.substr($LinhaPrecoInicial[0], 12, 2))
                                                                                    : (substr($LinhaPrecoInicial[1], 0, 3).'.'.substr($LinhaPrecoInicial[1], 3, 3).'.'.substr($LinhaPrecoInicial[1], 6, 3).'-'.substr($LinhaPrecoInicial[1], 9, 2)));
                                                                                    ?>
                                                                                </td>
                                                                                <input type="hidden" name="fornecedores[]" value="<?php echo $LinhaPrecoInicial[6]; ?>" />
                                                                            </tr>

                                                                            <!-- Headers ITENS DA SOLICITAÇÃO DE MATERIAL  -->
                                                                            <tr class="head_principal">
                                                                                <td class="textoabason" align="center" bgcolor="#DCEDF7" width="10%">LOTE</td>
                                                                                <td class="textoabason" align="center" bgcolor="#DCEDF7" width="75%">DESCRIÇÃO DO LOTE</td>
                                                                                <td class="textoabason" align="center" bgcolor="#DCEDF7" width="15%">PROPOSTA (R$)</td>
                                                                            </tr>

                                                                            <?php foreach($lotesPregao as $lote) {?>
                                                                                <tr>
                                                                                    <td class="textonormal" align="center" style="text-align: center"><?php echo $lote[0]?></td>
                                                                                    <td class="textonormal" align="center">
                                                                                        <?php echo (!empty($lote[1])) ? $lote[1] : '-' ?>
                                                                                    </td>
                                                                                    <td align="center" class="textonormal">
                                                                                        <?php
                                                                                        $valorInicial = '';
                                                                                        $precoInicial = precoIncial($LinhaPrecoInicial[6], $lote[2]);
                                                                                
                                                                                        if (is_array($precoInicial) && !empty($precoInicial[0]) && !empty($precoInicial[1])) {
                                                                                            $valorInicial = converte_valor_estoques($precoInicial[1]);
                                                                                            echo '<input type="hidden" name="precoInicial['.$LinhaPrecoInicial[6].']['.$lote[2].']" value="'.$precoInicial[0].'" />';
                                                                                        }
                                                                                        ?>
                                                                                        <input class="textonormal dinheiro4casas propostas" type="text" value="<?php echo $valorInicial; ?>" name="propostas[<?php echo $LinhaPrecoInicial[6]; ?>][<?php echo $lote[2]; ?>]" />
                                                                                    </td>
                                                                                    <?php
                                                                                }
                                                                                $LinhaPrecoInicial = $resultFornecedores->fetchRow();
                                                                            }
                                                                            ?>
                                                                        </tbody>
                                                                    </table>
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
                        <tr>
                            <td class="textonormal" align="right">
                                <input type="button" value="Salvar" class="botao" onclick="javascript:enviar('Salvar');">
                                <input type="button" value="Limpar" class="botao" onclick="javascript:limpar();">
                                <input type="button" value="Voltar" class="botao" onclick="javascript:voltar();">
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