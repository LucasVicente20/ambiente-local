<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: AbaContrato.php
# Autor:    Eliakim Ramos | João Madson
# Data:     10/12/2019
# Objetivo: Aba inclusão de contrato para CadContratoManter
#-------------------------------------------------------------------------

# Exibe Aba Classificação - Formulário D #
function ExibeAbaApostilamentoContratoManter(){
    // $ObjContrato = new Contrato();
    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        $idRegistro = $_POST['idregistro'];
        // $dadosItensContrato = $ObjContrato->GetItensContrato($idRegistro);
    }
    ?>
    <html>
    <?php
    # Carrega o layout padrão #
    layout();
    ?>
    <script language="javascript" src="../janela.js" type="text/javascript"></script>
    <script language="javascript" type="">
        <!--
        function Submete(Destino) {
            document.CadContratoManter.Destino.value = Destino;
            document.CadContratoManter.submit();
        }

        function enviar(valor){
            document.CadContratoManter.Botao.value = valor;
            document.CadContratoManter.submit();
        }

        function AbreJanela(url,largura,altura) {
            window.open(url,'pagina','status=no,scrollbars=no,left=20,top=130,width='+largura+',height='+altura);
        }

        function enviarDestino(valor, Destino){
            document.CadContratoManter.Destino.value = Destino;
            document.CadContratoManter.Botao.value = valor;
            document.CadContratoManter.submit();
        }
        <?php MenuAcesso(); ?>
        //-->
    </script>
    <link rel="stylesheet" type="text/css" href="../estilo.css">
    <body background="../midia/bg.gif" marginwidth="0" marginheight="0">
    <script language="JavaScript" src="../menu.js"></script>
    <script language="JavaScript">Init();</script>
    <form action="CadContratoManter.php" method="post" name="CadContratoManter">
        <input type="hidden" name="idregistro" value="<?php echo $idRegistro;?>">
        <br><br><br><br><br>
        <table cellpadding="3" border="0" summary="">
            <!-- Caminho -->
            <tr>
                <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
                <td align="left" class="textonormal" colspan="2"><br>
                    <font class="titulo2">|</font>
                    <a href="../index.php"><font color="#000000">Página Principal</font></a> > Contratos > Gestão
                </td>
            </tr>
            <!-- Fim do Caminho-->

            <!-- Erro -->
            <tr>
                <td width="100"></td>
                <td align="left" colspan="2">
                    <?php if( $_SESSION['Mens'] != 0 ){ ExibeMens($_SESSION['Mensagem'],$_SESSION['Tipo'],$_SESSION['Virgula']); }

                    $_SESSION['Mens'] = null;
                    $_SESSION['Tipo'] = null;
                    $_SESSION['Mensagem'] = null

                    ?>
                </td>
            </tr>
            <!-- Fim do Erro -->

            <!-- Corpo -->
            <tr>
                <td width="100"></td>
                <td class="textonormal">
                    <table  border="0" cellspacing="0" cellpadding="3" summary="" width="1024px">
                        <tr>
                            <td class="textonormal">
                                <table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
                                    <tr>
                                        <td align="left">
                                            <?php echo NavegacaoAbas(off,off,off,on,off); ?>
                                            <table cellpadding="0" cellspacing="0" border="0" bordercolor="#75ADE6" width="1024px" summary="">
                                                <tr bgcolor="#bfdaf2">
                                                    <td colspan="4">
                                                        <table id="scc_material" summary="" bgcolor="#bfdaf2" border="1" bordercolor="#75ADE6" width="100%">
                                                            <tbody>
                                                            <tr>
                                                                <td colspan="17" class="titulo3 itens_material" align="left" bgcolor="#DCEDF7" valign="left" style="font-weight: bold;">
                                                                        
                                                                </td>
                                                            </tr>

                                                            <?php if($CodLoteSelecionado > 0) { ?>
                                                            <tr>
                                                                <td colspan="7">

                                                                    <!-- INÍCIO - DETALHES DO LOTE -->

                                                                    <table border="0" width="100%" summary="">
                                                                        <tr>
                                                                            <td width="20%" align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Nº Lote:</td>
                                                                            <td align="left" class="textonormal" colspan="3" >
                                                                                <label style="width:500px;"><?php echo $_SESSION['NumeroLoteSelecionado'];?></label>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Descrição Resumida:</td>
                                                                            <td align="left" class="textonormal" colspan="3" >
                                                                                <input type="text" size="110" maxlength="100" name="DescricaoLoteSelecionado" value="<?php echo $_SESSION['DescricaoLoteSelecionado'];?>" />

                                                                                <input
                                                                                    name="SalvarDescricaoLote"
                                                                                    value="<?=$_SESSION['DescricaoLoteSelecionado'] == '' ? "Salvar" : "Alterar" ?>"
                                                                                    class="botao"
                                                                                    onclick="javascript:enviarDestino('SalvarDescricaoLote', 'D');"
                                                                                    type="button">
                                                                            </td>
                                                                        </tr>

                                                                    </table>
                                                                    <!-- FIM - DETALHES DO LOTE -->

                                                                </td>
                                                            </tr>

                                                            <tr>
                                                                <td
                                                                    colspan="17"
                                                                    class="titulo3 itens_material"
                                                                    align="center"
                                                                    bgcolor="#75ADE6"
                                                                    valign="middle"
                                                                >FORNECEDORES/CLASSIFICAÇÃO</td>
                                                            </tr>
                                                            <!-- Headers ITENS DA SOLICITAÇÃO DE MATERIAL  -->
                                                            <tr class="head_principal">

                                                                <?php // <!--  Coluna 1 = ORD--> ?>
                                                                <td
                                                                    class="textoabason"
                                                                    align="center"
                                                                    bgcolor="#DCEDF7"
                                                                    width="10%"
                                                                ><br /> ORD </td>

                                                                <?php // <!--  Coluna 2 = CNPJ/CPF--> ?>
                                                                <td
                                                                    class="textoabason"
                                                                    align="center"
                                                                    bgcolor="#DCEDF7"
                                                                    width="15%"
                                                                ><img
                                                                        src="../midia/linha.gif"
                                                                        alt=""
                                                                        border="0"
                                                                        height="1px"
                                                                    /> <br /> CNPJ/CPF </td>

                                                                <?php // <!--  Coluna 3 = RAZÃO SOCIAL/NOME--> ?>
                                                                <td
                                                                    class="textoabason"
                                                                    align="center"
                                                                    bgcolor="#DCEDF7"
                                                                    width="25%"
                                                                ><img
                                                                        src="../midia/linha.gif"
                                                                        alt=""
                                                                        border="0"
                                                                        height="1px"
                                                                    /> <br /> RAZÃO SOCIAL/NOME </td>

                                                                <?php // <!--  Coluna 4 = TIPO -> ?>
                                                                <td
                                                                    class="textoabason"
                                                                    align="center"
                                                                    bgcolor="#DCEDF7"
                                                                    width="5%"
                                                                ><img
                                                                        src="../midia/linha.gif"
                                                                        alt=""
                                                                        border="0"
                                                                        height="1px"
                                                                    /> <br /> TIPO </td>

                                                                <?php // <!--  Coluna 5 = REPRESENTANTE--> ?>
                                                                <td
                                                                    class="textoabason"
                                                                    align="center"
                                                                    bgcolor="#DCEDF7"
                                                                    width="25%"
                                                                ><img
                                                                        src="../midia/linha.gif"
                                                                        alt=""
                                                                        border="0"
                                                                        height="1px"
                                                                    /> <br /> REPRESENTANTE </td>

                                                                <?php // <!--  Coluna 6 = R.G.--> ?>
                                                                <td
                                                                    class="textoabason"
                                                                    align="center"
                                                                    bgcolor="#DCEDF7"
                                                                    width="10%"
                                                                ><img
                                                                        src="../midia/linha.gif"
                                                                        alt=""
                                                                        border="0"
                                                                        height="1px"
                                                                    /> <br /> R.G </td>

                                                                <?php // <!--  Coluna 7 = SITUAÇÃO--> ?>
                                                                <td
                                                                    class="textoabason"
                                                                    align="center"
                                                                    bgcolor="#DCEDF7"
                                                                    width="15%"
                                                                ><img
                                                                        src="../midia/linha.gif"
                                                                        alt=""
                                                                        border="0"
                                                                        height="1px"
                                                                    /> <br /> SITUAÇÃO </td>

                                                                <?php

                                                                // Membros do POST-----------------------------------

                                                                for ($itr = 0; $itr < $QuantidadeFornecedores; ++ $itr) {

                                                                //Início: Tipo de Empresa
                                                                $TipoEmpresaOrigem	= (($LinhaFornecedor[11] == 0 or $LinhaFornecedor[11] == '' or $LinhaFornecedor[11] == null) ? 0 : $LinhaFornecedor[11]);

                                                                switch($TipoEmpresaOrigem)
                                                                {
                                                                    case 0:
                                                                        $TipoEmpresa 		= 'OE';
                                                                        $DescTipoEmpresa 	= 'Outras Empresas';
                                                                        break;
                                                                    case 1:
                                                                        $TipoEmpresa 		= 'ME';
                                                                        $DescTipoEmpresa 	= 'Micro Empresa';
                                                                        break;
                                                                    case 2:
                                                                        $TipoEmpresa 		= 'EPP';
                                                                        $DescTipoEmpresa 	= 'Empresa de Pequeno Porte';
                                                                        break;
                                                                    case 3:
                                                                        $TipoEmpresa 		= 'MEI';
                                                                        $DescTipoEmpresa 	= 'Micro Empreendedor Individual';
                                                                        break;
                                                                }

                                                                //Fim: Tipo de Empresa
                                                                ?>

                                                                <!-- Dados MEMBRO DE COMISSÃO  -->
                                                            <tr>
                                                                <!--  Coluna 1 = Codido-->
                                                                <td
                                                                    class="textonormal"
                                                                    align="center"
                                                                    style="text-align: center"
                                                                >
                                                                    <?= ($itr + 1)?>
                                                                </td>

                                                                <!--  Coluna 2  = CPF/CNPJ -->
                                                                <td class="textonormal" align="<?=(($SituacaoLote <> 3) ? ("left") : ("center"))?>">
                                                                    <input type="hidden" name="CodLoteSelecionadoClassificacao<?=$LinhaFornecedor[6]?>" value="<?=$LinhaFornecedor[10]?>">
                                                                    <input type="hidden" name="CodSituacaoSelecionadoClassificacao<?=$LinhaFornecedor[6]?>" value="<?=$LinhaFornecedor[9]?>">

                                                                    <?
                                                                    if($SituacaoLote <> 3)
                                                                    {
                                                                        ?>
                                                                        <input
                                                                            name="CodFornecedorSelecionadoClassificacao"
                                                                            value="<?=$LinhaFornecedor[6]?>"
                                                                            type="radio"
                                                                            onclick="javascript:enviarDestino('FornecedorSelecionadoClassificacao','D');"
                                                                            <?
                                                                            if($_SESSION['CodFornecedorSelecionadoClassificacao'] == $LinhaFornecedor[6])
                                                                            {
                                                                                echo 'checked';
                                                                            }
                                                                            ?>
                                                                        />
                                                                        <?
                                                                    }
                                                                    ?>


                                                                    <?= ($LinhaFornecedor[1] == ""
                                                                        ?
                                                                        (substr($LinhaFornecedor[0], 0, 2).'.'.substr($LinhaFornecedor[0], 2, 3).'.'.substr($LinhaFornecedor[0], 5, 3).'/'.substr($LinhaFornecedor[0], 8, 4).'-'.substr($LinhaFornecedor[0], 12, 2))
                                                                        :
                                                                        (substr($LinhaFornecedor[1], 0, 3).'.'.substr($LinhaFornecedor[1], 3, 3).'.'.substr($LinhaFornecedor[1], 6, 3).'-'.substr($LinhaFornecedor[1], 9, 2)));?>

                                                                </td>

                                                                <!--  Coluna 2  = Razão Social -->
                                                                <td class="textonormal">

                                                                    <?
                                                                    $Encoding = 'UTF-8';
                                                                    $CodFornecedorSelecionado 	= $LinhaFornecedor[6];
                                                                    $CodLoteSelecionado			= $LinhaFornecedor[10] ;
                                                                    $CodSituacaoClassificacao	= $LinhaFornecedor[9] ;
                                                                    $UrlItem = "CadPregaoPresencialClassificarFornecedor.php?CodFornecedorSelecionado=$CodFornecedorSelecionado&CodLoteSelecionado=$CodLoteSelecionado&CodSituacaoClassificacao=$CodSituacaoClassificacao";
                                                                    ?>

                                                                    <?
                                                                    if($SituacaoLote <> 3)
                                                                    {
                                                                        ?>
                                                                        <a href="javascript:AbreJanela('<?= $UrlItem ?>',700,550);"><font color="#000000">
                                                                                <?= $LinhaFornecedor[2]?></font>
                                                                        </a>
                                                                        <?
                                                                    }
                                                                    else
                                                                    {
                                                                        ?>
                                                                        <?= $LinhaFornecedor[2]?>
                                                                        <?
                                                                    }
                                                                    ?>
                                                                </td>

                                                                <!--  Coluna 3  = Tipo de Empresa -->
                                                                <td class="textonormal" align="center" title="<?=$DescTipoEmpresa?>" style="cursor: help">

                                                                    <?= $TipoEmpresa ?>

                                                                </td>

                                                                <!--  Coluna 4  = Representante Nome -->
                                                                <td class="textonormal" align="center">

                                                                    <?= ($LinhaFornecedor[3] == '' ? '-' : $LinhaFornecedor[3]) ?>

                                                                </td>

                                                                <!--  Coluna 5  = Representante R.G. -->
                                                                <td class="textonormal" align="center">

                                                                    <?= ($LinhaFornecedor[3] == '' ? '-' : $LinhaFornecedor[4]) ?>
                                                                    <?= ($LinhaFornecedor[3] == '' ? '' : $LinhaFornecedor[7]) ?>

                                                                </td>


                                                                <!--  Coluna 6 = Situação Classificatória-->
                                                                <td
                                                                    class="textonormal"
                                                                    style="text-align: center !important; color: <?

                                                                    if($LinhaFornecedor[9] == 1)
                                                                    {
                                                                        echo "blue";
                                                                    }
                                                                    else
                                                                    {
                                                                        echo "red";
                                                                    }

                                                                    ?>;"
                                                                >
                                                                    <?= $LinhaFornecedor[8] ?>
                                                                </td>

                                                                <?php
                                                                $LinhaFornecedor = $resultFornecedores->fetchRow();
                                                                }
                                                                ?>



                                                                <?php

                                                                if ($QuantidadeFornecedores <= 0) {
                                                                ?>
                                                                <tr>
                                                                    <td
                                                                        class="textonormal itens_material"
                                                                        colspan="7"
                                                                        style="color: red"
                                                                    >Nenhum Fornecedor inscrito</td>
                                                                </tr>
                                                                <!-- FIM Dados ITENS DA SOLICITAÇÃO DE MATERIAL  -->

                                                            <?php

                                                            }
                                                            ?>

                                                            <?php

                                                            if ($QuantidadeFornecedores > 0) {
                                                                ?>

                                                                <tr>
                                                                    <td
                                                                        colspan="6"
                                                                        class="titulo3 itens_material menosum"
                                                                        width="95%"
                                                                    >TOTAL DE FORNECEDORES VINCULAOS AO LOTE:</td>

                                                                    <td
                                                                        class="textonormal"
                                                                        align="center"
                                                                        width="5%"
                                                                    >
                                                                        <div id="MaterialTotal" style="font-weight: bold;"><?= $QuantidadeFornecedores ?></div>
                                                                    </td>
                                                                </tr>

                                                                <?php

                                                            }
                                                            ?>

                                                            <?php

                                                            if ($_SESSION['CodFornecedorSelecionadoClassificacao'] <> null and $SituacaoLote <> 3) {


                                                            $Encoding = 'UTF-8';
                                                            $CodFornecedorSelecionado 	= $_SESSION['CodFornecedorSelecionadoClassificacao'];
                                                            $CodLoteSelecionado			= $_SESSION['CodLoteSelecionadoClassificacao'] ;
                                                            $CodSituacaoClassificacao	= $_SESSION['CodSituacaoSelecionadoClassificacao'] ;
                                                            $UrlItem = "CadPregaoPresencialClassificarFornecedor.php?CodFornecedorSelecionado=$CodFornecedorSelecionado&CodLoteSelecionado=$CodLoteSelecionado&CodSituacaoClassificacao=$CodSituacaoClassificacao";


                                                            ?>

                                                            <tr>
                                                                <td
                                                                    class="textonormal"
                                                                    colspan="7"
                                                                    align="left"
                                                                >
                                                                    <input
                                                                        name="AlterarSituacaoClassificacao"
                                                                        value="Alterar"
                                                                        class="botao"
                                                                        onclick="javascript:AbreJanela('<?= $UrlItem ?>',700,550);"
                                                                        type="button"
                                                                    >

                                                                    <?php

                                                                    }
                                                                    ?>
                                                                </td>


                                                            </tr>

                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>



                                        </td>
                                    </tr>

                                    <?php }?>
                                    <tr>
                                        <td colspan="4" align="right">
                                        <!-- onclick="javascript:enviar('D');" -->
                                            <input type="button" value="Salvar" class="botao" >
                                            <input type="hidden" name="Botao" value="">
                                            <input type="hidden" name="Origem" value="D">
                                            <input type="hidden" name="Destino">
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
    <?php
    exit;
}