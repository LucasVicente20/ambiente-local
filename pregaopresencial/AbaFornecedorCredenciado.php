<?php
# -------------------------------------------------------------------------
# Alterado: Pitang Agile Ti - Caio coutinho
# Data:		22/01/2019
# Objetivo: Tarefa Redmine 208468
# -------------------------------------------------------------------------

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
            document.CadPregaoPresencialSessaoPublica.Destino.value = Destino;
            document.CadPregaoPresencialSessaoPublica.submit();
        }
        function enviar(valor){
            document.CadPregaoPresencialSessaoPublica.Botao.value = valor;
            document.CadPregaoPresencialSessaoPublica.submit();
        }
        <?php MenuAcesso(); ?>
        //-->
    </script>
    <link rel="stylesheet" type="text/css" href="../estilo.css">
    <body background="../midia/bg.gif" marginwidth="0" marginheight="0">
    <script language="JavaScript" src="../menu.js"></script>
    <script language="JavaScript">Init();</script>
    <form action="CadPregaoPresencialSessaoPublica.php" method="post" name="CadPregaoPresencialSessaoPublica">
        <br><br><br><br>
        <table cellpadding="3" border="0" summary="">
            <!-- Caminho -->
            <tr>
                <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
                <td align="left" class="textonormal" colspan="2"><br>
                    <font class="titulo2">|</font>
                    <a href="../index.php"><font color="#000000">Página Principal</font></a> > Pregão Presencial > Sessão Pública
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
                                        <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
                                            <?
                                            $PregaoCod = $_SESSION['PregaoCod'];

                                            $db     = Conexao();

                                            //Verificando se existe licitações ligadas a o processo , para ver qual programa devo chamar
                                            $sqlSolicitacoes = "SELECT		fn.apregfccgc, fn.apregfccpf, fn.npregfrazs, fn.npregfnomr, fn.apregfnurg, fn.epregfsitu, fn.cpregfsequ, npregforgu, fn.fpregfmepp
													FROM 		sfpc.tbpregaopresencialfornecedor fn
													WHERE		fn.cpregasequ  = $PregaoCod
														AND		fn.epregfsitu  = 'C'
													ORDER BY	fn.npregfrazs ASC,
																fn.npregfnomr ASC";



                                            $db     = Conexao();
                                            $result = $db->query($sqlSolicitacoes);

                                            if( PEAR::isError($result) ){
                                                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlSolicitacoes");
                                            }

                                            $Linha = $result->fetchRow();

                                            $QuantidadeFornecedores = 0;

                                            $QuantidadeFornecedores = $result->numRows();

                                            $db->disconnect();
                                            ?>


                                            PREGÃO PRESENCIAL - SESSÃO PÚBLICA
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="textonormal" >
                                            <table border="0" width="100%" summary="">
                                                <tr>
                                                    <td width="20%" align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Comissão:</td>
                                                    <td align="left" class="textonormal" colspan="3" >
                                                        <label style="width:500px;"><?php echo $_SESSION['NomeComissao'];?></label>
                                                        <input type="hidden" name="CodigoDaComissao" value="<?php echo $_SESSION['CodigoComissao'];?>" />
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Processo:</td>
                                                    <td align="left" class="textonormal" colspan="3" >
                                                        <label><?php echo substr($_SESSION['NumeroDoProcesso'] + 10000,1); ?></label>
                                                        <input type="hidden" name="NumeroDoProcesso" value="<?php echo substr($_SESSION['NumeroDoProcesso'] + 10000,1);?>" />
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Ano:</td>
                                                    <td align="left" class="textonormal" colspan="3" >
                                                        <label><?php echo $_SESSION['AnoDoExercicio']; ?></label>
                                                        <input type="hidden" name="AnoDoExercicio" value="<?php echo $_SESSION['AnoDoExercicio'];?>" />
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Modalidade:</td>
                                                    <td align="left" class="textonormal" colspan="3" >
                                                        <label><?php echo $_SESSION['Modalidade']; ?></label>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Registro de Preço:</td>
                                                    <td align="left" class="textonormal" colspan="3" >
                                                        <input type="hidden" id="registroPreco" name="registroPreco" value="<?php echo $_SESSION['RegistroPreco'];?>"/>
                                                        <label>
                                                            <?php
                                                            if ($RegistroPreco) {
                                                                echo "Sim";
                                                            } else {
                                                                echo "Não";
                                                            }
                                                            ?>
                                                        </label>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Licitação:</td>
                                                    <td align="left" class="textonormal" colspan="3" >
                                                        <label><?php echo substr($_SESSION['Licitação'] + 10000,1); ?></label>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Ano da Licitação:</td>
                                                    <td align="left" class="textonormal" colspan="3" >
                                                        <label><?php echo $_SESSION['AnoLicitação']; ?></label>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Objeto:</td>
                                                    <td>
                                                        <label class="textonormal" style="word-wrap:break-word;" ><?php echo $_SESSION['Objeto'];?></label>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Tratamento diferenciado EPP/MEI/ME:</td>
                                                    <td align="left" class="textonormal" colspan="3" >
                                                        <input type="hidden" id="tratamentoDiferenciado" name="tratamentoDiferenciado" value="<?php echo $_SESSION['TratamentoDiferenciado'];?>"/>
                                                        <label>
                                                            <?php
                                                            if	($_SESSION['TratamentoDiferenciado'] == 'N'){
                                                                echo "NÃO";
                                                            } elseif ($_SESSION['TratamentoDiferenciado'] == 'E') {
                                                                echo "EXCLUSIVO";
                                                            } elseif ($_SESSION['TratamentoDiferenciado'] == 'C') {
                                                                echo "COTA RESERVADA";
                                                            } elseif ($_SESSION['TratamentoDiferenciado'] == 'S') {
                                                                echo "SUBCONTRATAÇÃO";
                                                            } elseif ($_SESSION['TratamentoDiferenciado'] == 'M') {
                                                                echo "COTA RESERVADA/EXCLUSIVA";
                                                            } else {
                                                                echo "NÃO INFORMADO";
                                                            }
                                                            ?>
                                                        </label>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Órgão demandante:</td>
                                                    <td>
                                                        <label class="textonormal" style="word-wrap:break-word;" ><?php echo $_SESSION['OrgaoDemandante'];?></label>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Tipo de Licitação:</td>
                                                    <td>

                                                        <select name="PregaoTipoSelecionado" class="textonormal">
                                                            <option value="N" <?php echo ($_SESSION['PregaoTipo'] == 'N' ? 'selected' : '');?>>MENOR PREÇO</option>
                                                            <option value="M" <?php echo ($_SESSION['PregaoTipo'] == 'M' ? 'selected' : '');?>>MAIOR OFERTA</option>
                                                        </select>

                                                        <input	name="AlterarPregaoTipo"
                                                                  value="Alterar"
                                                                  class="botao"
                                                                  onclick="javascript:enviarDestino('AlterarPregaoTipo','C');"
                                                                  type="button">

                                                    </td>
                                                </tr>

                                            </table>
                                        </td>
                                    </tr>
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
