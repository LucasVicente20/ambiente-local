<?php
# -------------------------------------------------------------------------
# Alterado: Pitang Agile Ti - Caio coutinho
# Data:		22/01/2019
# Objetivo: Tarefa Redmine 208468
# -------------------------------------------------------------------------

# Exibe Aba Membro de Comissão - Formulário A #
function ExibeAbaMembroComissao(){ ?>
    <html>
    <?php
        # Carrega o layout padrão #
        layout();
    ?>
    <script language="javascript" src="../janela.js" type="text/javascript"></script>
    <script language="javascript" type="">
        <!--
        function AbreJanela(url,largura,altura) {
            window.open(url,'detalhe','status=no,scrollbars=yes,left=70,top=130,width='+largura+',height='+altura);
        }
        function Submete(Destino){
            document.CadPregaoPresencialSessaoPublica.Destino.value = Destino;
            document.CadPregaoPresencialSessaoPublica.submit();
        }
        function enviar(valor){
            document.CadPregaoPresencialSessaoPublica.Botao.value = valor;
            document.CadPregaoPresencialSessaoPublica.submit();
        }
        function enviarDestino(valor, Destino){
            document.CadPregaoPresencialSessaoPublica.Destino.value = Destino;
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
        <br><br><br><br><br>
        <table cellpadding="3" border="0" summary="">
            <!-- Caminho -->
            <tr>
                <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
                <td align="left" class="textonormal" colspan="2">
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
                                            <?php
                                            $db     = Conexao();
                                            $PregaoCod = $_SESSION['PregaoCod'];

                                            //Verificando se existe licitações ligadas a o processo , para ver qual programa devo chamar
                                            $sqlSolicitacoes = "SELECT		up.eusuporesp, pm.epregmtipo, pm.cpregmsequ
													FROM 		sfpc.tbpregaopresencialmembro pm, sfpc.tbusuarioportal up
													WHERE		pm.cpregasequ  = $PregaoCod 
														AND 	up.cusupocodi  = pm.cusupocodi
													ORDER BY	pm.epregmtipo DESC,
																up.eusuporesp ASC";

                                            $result = $db->query($sqlSolicitacoes);

                                            if( PEAR::isError($resultSoli) ){
                                                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlSolicitacoes");
                                            }

                                            $Linha             = $result->fetchRow();
                                            $QuantidadeMembros = 0;
                                            $QuantidadeMembros = $result->numRows();
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
                                                                  onclick="javascript:enviarDestino('AlterarPregaoTipo','A');"
                                                                  type="button">

                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="left">
                                            <?php echo NavegacaoAbas(on,off,off,off,off); ?>
                                            <table cellpadding="0" cellspacing="0" border="0" bordercolor="#75ADE6" width="1024px" summary="">
                                                <tr bgcolor="#bfdaf2">
                                                    <td colspan="4">
                                                        <table id="scc_material" summary="" bgcolor="#bfdaf2" border="1" bordercolor="#75ADE6" width="100%">
                                                            <tbody>
                                                            <tr>
                                                                <td colspan="17" class="titulo3 itens_material" align="center" bgcolor="#75ADE6" valign="middle">MEMBROS DA COMISSÃO</td>
                                                            </tr>
                                                            <!-- Headers ITENS DA SOLICITAÇÃO DE MATERIAL  -->
                                                            <tr class="head_principal">
                                                                <?php // <!--  Coluna 1 = ORD--> ?>
                                                                <td class="textoabason" align="center" bgcolor="#DCEDF7" width="10%">
                                                                    <br /> ORD
                                                                </td>
                                                                <?php // <!--  Coluna 2 = DESCRIÇÃO DO MATERIAL--> ?>
                                                                <td class="textoabason" align="center" bgcolor="#DCEDF7" width="70%">
                                                                    <img src="../midia/linha.gif" alt="" border="0" height="1px"/> <br /> NOME
                                                                </td>
                                                                <?php // <!--  Coluna 3 = CÓD.RED. CADUM--> ?>
                                                                <td
                                                                    class="textoabason"
                                                                    align="center"
                                                                    bgcolor="#DCEDF7"
                                                                    width="20%"
                                                                ><img
                                                                        src="../midia/linha.gif"
                                                                        alt=""
                                                                        border="0"
                                                                        height="1px"
                                                                    /> <br /> FUNÇÃO </td>

                                                                <?php

                                                                // Membros do POST-----------------------------------

                                                                for ($itr = 0; $itr < $QuantidadeMembros; ++ $itr) {
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

                                                                <!--  Coluna 2  = Descricao -->
                                                                <td class="textonormal">
                                                                    <input
                                                                        name="Membro"
                                                                        value="<?php echo $Linha[2] ?>"
                                                                        type="radio"
                                                                    />

                                                                    <?= $Linha[0] ?>

                                                                </td>
                                                                <!--  Coluna 3 = Cod CADUM-->
                                                                <td
                                                                    class="textonormal"
                                                                    style="text-align: center !important; color: <?= $Linha[1] == 'P' ? "blue" : "black"; ?>;"
                                                                >
                                                                    <?= $Linha[1] == 'M' ? 'APOIO' : 'PREGOEIRO' ?>
                                                                </td>

                                                                <?php
                                                                $Linha = $result->fetchRow();
                                                                }
                                                                ?>

                                                                <?php

                                                                if ($QuantidadeMembros <= 0) {
                                                                ?>
                                                            <tr>
                                                                <td
                                                                    class="textonormal itens_material"
                                                                    colspan="3"
                                                                    style="color: red"
                                                                >Nenhum Membro da Comissão informado</td>
                                                            </tr>
                                                            <!-- FIM Dados MEMBRO DE COMISSÃO -->

                                                            <?php

                                                            }
                                                            ?>

                                                            <?php

                                                            if ($QuantidadeMembros > 0) {
                                                                ?>

                                                                <tr>
                                                                    <td
                                                                        colspan="2"
                                                                        class="titulo3 itens_material menosum"
                                                                        width="95%"
                                                                    >TOTAL DE MEMBROS DA COMISSÃO:</td>

                                                                    <td
                                                                        class="textonormal"
                                                                        align="center"
                                                                        width="5%"
                                                                    >
                                                                        <div id="MaterialTotal" style="font-weight: bold;"><?= $QuantidadeMembros ?></div>
                                                                    </td>
                                                                </tr>

                                                                <?php

                                                            }
                                                            ?>

                                                            <tr>
                                                                <td
                                                                    class="textonormal"
                                                                    colspan="3"
                                                                    align="center"
                                                                ><input
                                                                        name="IncluirMembro"
                                                                        value="Incluir Membro"
                                                                        class="botao"
                                                                        onclick="javascript:AbreJanela('../pregaopresencial/CadPregaoPresencialIncluirMembro.php?ProgramaOrigem=CadPregaoPresencialSessaoPublica;PesqApenas=C', 800, 280);"
                                                                        type="button"
                                                                    >

                                                                    <?php

                                                                    if ($QuantidadeMembros > 0) {
                                                                        ?>

                                                                        <input
                                                                            name="MarcarPregoeiro"
                                                                            value="Marcar como Pregoeiro"
                                                                            class="botao"
                                                                            onclick="javascript:enviar('MarcarPregoeiro');"
                                                                            type="button"
                                                                        >

                                                                        <input
                                                                            name="RemoverMembro"
                                                                            value="Remover Membro"
                                                                            class="botao"
                                                                            onclick="javascript:enviar('RemoverMembro');"
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

                                    <tr>
                                        <td colspan="4" align="right">
                                            <input type="button" value="Próxima Aba" class="botao" onclick="javascript:enviar('A');">
                                            <input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
                                            <input type="hidden" name="Botao" value="">
                                            <input type="hidden" name="Origem" value="A">
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
