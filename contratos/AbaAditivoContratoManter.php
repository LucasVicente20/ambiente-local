<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadPregaoPresencialSessaoPublica.php
# Autor:    Eliakim Ramos | João Madson
# Data:     10/12/2019
# Objetivo: Programa de incluir contrato
#-------------------------------------------------------------------------

# Exibe Aba Qualificação Econômica e Financeira - Formulário C #
function ExibeAbaAditivoContratoManter(){
    $ObjContrato = new ContratoManter();
    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        $idRegistro = $_POST['idregistro'];
        $dadosAditivo = $ObjContrato->GetAditivos($idRegistro);
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
                                            <?php echo NavegacaoAbas(off,off,on,off,off); ?>



                                            <table cellpadding="0" cellspacing="0" border="0" bordercolor="#75ADE6" width="1024px" summary="">
                                                <tr bgcolor="#bfdaf2">
                                                    <td colspan="4">
                                                        <table id="scc_material" summary="" bgcolor="#bfdaf2" border="1" bordercolor="#75ADE6" width="100%">
                                                            <tbody>
                                                            <tr>
                                                                <td colspan="17" class="titulo3 itens_material" align="center" bgcolor="#75ADE6" valign="middle">
                                                                    MANTER ADITIVO
                                                                </td>
                                                            </tr>
                                                            <!-- Headers ITENS DA SOLICITAÇÃO DE MATERIAL  -->
                                                            <tr class="head_principal">
                                                                <td class="textoabason" align="center" bgcolor="#DCEDF7" width="10%">
                                                                    <br /> 
                                                                </td>
                                                                <td class="textoabason" align="center" bgcolor="#DCEDF7" width="15%">
                                                                    <img src="../midia/linha.gif" alt="" border="0" height="1px" /> 
                                                                     <br /> ADITIVO 
                                                                </td>
                                                                <td class="textoabason" align="center" bgcolor="#DCEDF7" width="25%" >
                                                                    <img src="../midia/linha.gif" alt="" border="0" height="1px" /> 
                                                                    <br /> DATA 
                                                                </td>
                                                                <td class="textoabason" align="center" bgcolor="#DCEDF7" width="5%" >
                                                                    <img src="../midia/linha.gif" alt="" border="0" height="1px" /> 
                                                                    <br /> ALTERAÇAO PRAZO 
                                                                </td>
                                                                <td class="textoabason" align="center" bgcolor="#DCEDF7" width="25%" >
                                                                     <img src="../midia/linha.gif" alt="" border="0" height="1px" /> 
                                                                     <br /> ALTERAÇAO VALOR 
                                                                </td>
                                                                <td class="textoabason" align="center" bgcolor="#DCEDF7" width="10%">
                                                                    <img src="../midia/linha.gif" alt="" border="0" height="1px" /> 
                                                                    <br /> ALTERAÇAO CONTRATANTE
                                                                </td>
                                                                <td class="textoabason" align="center" bgcolor="#DCEDF7" width="15%">
                                                                    <img src="../midia/linha.gif" alt="" border="0" height="1px" /> 
                                                                    <br /> SITUAÇÃO
                                                                </td>
                                                            </tr>
                                                            <?php if($dadosAditivo){
                                                                        foreach($dadosAditivo as $aditivo){
                                                                ?>
                                                            <tr>
                                                                <!--  Coluna 1 = Codido-->
                                                                <td class="textonormal" align="center" style="text-align: center" >
                                                                    <input type="radio" name="codAditivo" id="codAditivo" value="<?php echo $aditivo->cdocpcsequ;?>">
                                                                </td>

                                                                <!--  Coluna 2  = CPF/CNPJ -->
                                                                <td class="textonormal" align="center">
                                                                    <?php echo str_pad($aditivo->aaditinuad,2,0,STR_PAD_LEFT);?>
                                                                </td>

                                                                <!--  Coluna 2  = Razão Social -->
                                                                <td class="textonormal" align="center">
                                                                    <?php echo  DataBarra($aditivo->daditicada);?>
                                                                </td>

                                                                <!--  Coluna 3  = Tipo de Empresa -->
                                                                <td class="textonormal" align="center"  style="cursor: help">
                                                                    <?php echo $aditivo->faditialpz;?>
                                                                </td>

                                                                <!--  Coluna 4  = Representante Nome -->
                                                                <td class="textonormal" align="center">
                                                                    <?php echo $aditivo->faditialvl;?>
                                                                </td>

                                                                <!--  Coluna 5  = Representante R.G. -->
                                                                <td class="textonormal" align="center">
                                                                    <?php echo $aditivo->faditialct;?>
                                                                </td>
                                                                <!--  Coluna 6 = Situação-->
                                                                <td class="textonormal" style="text-align: center !important;">
                                                                </td>
                                                            </tr>
                                                                <?php
                                                                  }
                                                                }else{
                                                                ?>
                                                            <tr>
                                                                <td class="textonormal itens_material" colspan="7" style="color: red" >Nenhum Aditivo</td>
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
