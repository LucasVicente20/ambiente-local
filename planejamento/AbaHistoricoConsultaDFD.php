<?php
/**
 * Portal de Compras
 * Programa: AbaHistoricoConsultaDFD.phpJoão Madson
 * Autor: João Madson
 * Data:  07/03/2023
 * Objetivo: Aba de amostragem das informações da DFD Selecioanada
 * -------------------------------------------------------------------
 */

//inicio da sessão para a página
session_start();

// A função abaixo é a montagem da tela na aba histórico de ConsDFD.php
function ExibeAbaHistoricoDFD() {
    $dadosSessaoHistorico = $_SESSION['historico'];
?>
    <html>
    <script language="javascript" src="../import/jquery/jquery-1.7.2.min.js" type="text/javascript"></script>
    <script language="javascript" src="../import/jquery/jquery.maskmoney.js" type="text/javascript"></script>
    <script language="javascript" src="../import/jquery/jquery.maskedinput.js" type="text/javascript"></script>​
    <script language="javascript" type="">

    function Submete(Destino) {
        document.ConsDFD.Destino.value = Destino;
        document.ConsDFD.submit();
    }


    </script>
    <link rel="stylesheet" type="text/css" href="../estilo.css?v=<?php echo time();?>">

    <body background="../midia/bg.gif" marginwidth="0" marginheight="0">
    <!-- <script language="JavaScript" src="../menu.js"></script> -->
    <script language="JavaScript">Init();</script>
    <form action="ConsultaDFD.php" method="post" id="formAbaHistorico" name="ConsDFD">
    <input type="hidden" name="op" id="op" value="">
    <input type="hidden" name="Destino" id="Destino" value="">
    <!-- <br><br><br><br> -->

    <table cellpadding="3" border="0" summary="" width="1024px">
        <!-- Caminho -->
        <tr>
            <!-- <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td> -->
            <td align="left" class="textonormal" colspan="2">
                <font class="titulo2">|</font>
                <a href="../index.php"><font color="#000000">Página Principal</font></a> > Planejamento > DFD > Consultar
            </td>
        </tr>
        <!-- Fim do Caminho-->
        
        <!-- Corpo -->
        <tr>
            <!-- <td width="100"></td> -->
            <td class="textonormal">
                <br>
                <table  border="1" cellspacing="0" cellpadding="3" summary="" width="1024px" bordercolor="#75ADE6" bgcolor="#FFFFFF">
                    <th colspan="3" >
                        <?php echo NavegacaoAbasConsultaDFD(on,off); ?>
                    </th>
                    <tr>
                        <td class="textonormal" border="0" bordercolor="#75ADE6">
                            <!-- <table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF" width="1024px"> -->
                            <table summary="" bgcolor="#bfdaf2" border="1" bordercolor="#75ADE6" width="1024px">
                                <thead>
                                    <td class="titulo3" colspan="17" align="center" bgcolor="#75ADE6" valign="middle"> <b>HISTÓRICO - DOCUMENTO DE FORMALIZAÇÃO DE DEMANDA (DFD)</b>
                                    </td>
                                </thead>
                                <td align="left">
                                    <table class="textonormal" id="scc_material" summary="" width="100%">
                                        <tr bgcolor="#bfdaf2">
                                            <td class="textonormal" align="center" bgcolor="#DCEDF7" width="5%">DATA</th>
                                            <td class="textonormal" align="center" bgcolor="#DCEDF7" width="5%">SITUAÇÃO</th>
                                            <td class="textonormal" align="center" bgcolor="#DCEDF7" width="5%">USUÁRIO RESPONSÁVEL</th>
                                        </tr>
                                        <tbody>
                                        <?php
                                        $html = '';
                                        foreach ($dadosSessaoHistorico as $dadoSessao) {
                                            $data = date('d/m/Y', strtotime($dadoSessao->tplhsiincl));
                                            if (!empty($dadoSessao)) {
                                                $html .= '<tr>';
                                                    $html .= '<td class="textonormal" align="center" bgcolor="#DCEDF7" width="5%">'.$data.'</td>';
                                                    $html .= '<td class="textonormal" align="center" bgcolor="#DCEDF7" width="5%">'.$dadoSessao->eplsitnome.'</td>';
                                                    $html .= '<td class="textonormal" align="center" bgcolor="#DCEDF7" width="5%">'.$dadoSessao->eusuporesp.'</td>';
                                                $html .= '</tr>';
                                            }
                                        }
                                        echo $html;
                                        ?>
                                        </tbody>
                                    </table>
                                </td>
                                
                            </table>
                            <tr>
                                <!-- <td colspan="8"> -->
                                    <!-- <a type="button" class="botao" size="20"  width="" style="float:right; width:50px; text-align:center" href="ConsPesquisarDFD.php">Voltar</a> -->
                                <!-- </td> -->
                            </tr>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <!-- Fim do Corpo -->
    </table>
<?php
}
?>

