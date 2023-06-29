<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadPregaoPresencialSessaoPublica.php
# Autor:    Eliakim Ramos | João Madson
# Data:     10/12/2019
# Objetivo: Programa de incluir contrato
#-------------------------------------------------------------------------

# Exibe Aba Qualificação Econômica e Financeira - Formulário C #
function ExibeAbaFornecedorCredenciado(){
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
            document.CadContratoIncluir.Destino.value = Destino;
            document.CadContratoIncluir.submit();
        }
        function enviar(valor){
            document.CadContratoIncluir.Botao.value = valor;
            document.CadContratoIncluir.submit();
        }
        <?php MenuAcesso(); ?>
        //-->
    </script>
    <link rel="stylesheet" type="text/css" href="../estilo.css">
    <body background="../midia/bg.gif" marginwidth="0" marginheight="0">
    <script language="JavaScript" src="../menu.js"></script>
    <script language="JavaScript">Init();</script>
    <form action="CadContratoIncluir.php" method="post" name="CadContratoIncluir">
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
                                            <?php echo NavegacaoAbas(off,off,on,off,off); ?>



                                            <table cellpadding="0" cellspacing="0" border="0" bordercolor="#75ADE6" width="1024px" summary="">
                                                <tr bgcolor="#bfdaf2">
                                                    <td colspan="4">



                                                        <table
                                                            id="scc_material"
                                                            summary=""
                                                            bgcolor="#bfdaf2"
                                                            border="1"
                                                            bordercolor="#75ADE6"
                                                            width="100%"
                                                        >
                                                            <tbody>
                                                            <tr>
                                                                <td
                                                                    colspan="17"
                                                                    class="titulo3 itens_material"
                                                                    align="center"
                                                                    bgcolor="#75ADE6"
                                                                    valign="middle"
                                                                >FORNECEDORES COM REPRESENTANTE CREDENCIADO</td>
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
                                                                $TipoEmpresaOrigem	= (($Linha[8] == 0 or $Linha[8] == '' or $Linha[8] == null) ? 0 : $Linha[8]);

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
                                                                <td class="textonormal" align="center">

                                                                    <?= ($Linha[1] == ""
                                                                        ?
                                                                        (substr($Linha[0], 0, 2).'.'.substr($Linha[0], 2, 3).'.'.substr($Linha[0], 5, 3).'/'.substr($Linha[0], 8, 4).'-'.substr($Linha[0], 12, 2))
                                                                        :
                                                                        (substr($Linha[1], 0, 3).'.'.substr($Linha[1], 3, 3).'.'.substr($Linha[1], 6, 3).'-'.substr($Linha[1], 9, 2)));?>

                                                                </td>

                                                                <!--  Coluna 2  = Razão Social -->
                                                                <td class="textonormal" align="center">

                                                                    <?= $Linha[2] ?>

                                                                </td>

                                                                <!--  Coluna 3  = Tipo de Empresa -->
                                                                <td class="textonormal" align="center" title="<?=$DescTipoEmpresa?>" style="cursor: help">

                                                                    <?= $TipoEmpresa ?>

                                                                </td>

                                                                <!--  Coluna 4  = Representante Nome -->
                                                                <td class="textonormal" align="center">

                                                                    <?= $Linha[3] ?>

                                                                </td>

                                                                <!--  Coluna 5  = Representante R.G. -->
                                                                <td class="textonormal" align="center">

                                                                    <?= $Linha[4] ?>
                                                                    <?= $Linha[7] ?>

                                                                </td>


                                                                <!--  Coluna 6 = Situação-->
                                                                <td
                                                                    class="textonormal"
                                                                    style="text-align: center !important; color: <?

                                                                    switch ($Linha[5]) {
                                                                        case "A":
                                                                            echo "black";
                                                                            break;
                                                                        case "C":
                                                                            echo "blue";
                                                                            break;
                                                                        case "N":
                                                                            echo "red";
                                                                            break;
                                                                    }

                                                                    ?>;"
                                                                >
                                                                    <?
                                                                    switch ($Linha[5]) {
                                                                        case "A":
                                                                            echo "AGUARDANDO";
                                                                            break;
                                                                        case "C":
                                                                            echo "CREDENCIADO";
                                                                            break;
                                                                        case "N":
                                                                            echo "NÃO CREDENCIADO";
                                                                            break;
                                                                    }
                                                                    ?>
                                                                </td>

                                                                <?php
                                                                $Linha = $result->fetchRow();
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
                                                                >Nenhum Fornecedor credenciado</td>
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
                                                                    >TOTAL DE FORNECEDORES COM REPRESENTANTE CREDENCIADO:</td>

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

                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>



                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" align="right">
                                            <input type="button" value="Próxima Aba" class="botao" onclick="javascript:enviar('C');">
                                            <input type="hidden" name="Botao" value="">
                                            <input type="hidden" name="Origem" value="C">
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
