<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadEnviaEmailGestorContratoAVencer.php
# Autor:    João Madson
# Data:     28/01/2021
# Objetivo: Disparar emails para os gestores de contratos a vencer em 90 dias
#-------------------------------------------------------------------------
require_once dirname(__FILE__) . '/../funcoes.php';
require_once "./ClassContratos.php";

session_start();


EnviaEmail('joao.madson@recife.pe.gov.br',$NomeLocalTitulo." Assunto teste","Mensagem teste \n Madson.",$GLOBALS["EMAIL_FROM"]);
echo 'Enviado';
?>
    <html>
    

    <body background="../midia/bg.gif" marginwidth="0" marginheight="0">
    <form action="updateaforcrseq1.php" method="post" id="updateaforcrseq1" name="updateaforcrseq1">
    <input type="hidden" name="op" value="IncluirContrato">
    <input type="hidden" name="ctpcomcodi" value="<?php echo $_SESSION['origemScc'];?>">
        <br><br><br><br><br>
        <table cellpadding="3" border="0" summary="" align="center">
            <tr bgcolor="#ffffff">
                <td colspan="8" align="center">
                <?php
                    echo !empty($teste)?$teste:"Clique para atualizar o aforcrsq1";
                ?>
                <br>
                <input type="submit" name="update" value="update" class="botao"></button>
                </td>
                <?php
                    if(!empty($dbDados)){
                        for($i=0; $i<count($dbDados); $i++){
                            echo $dbDados[$i];
                            echo '<br>';
                        }
                    }
                ?>
            </tr>       
        </table>
    </form>
 
    <!-- Fim Modal -->
    </body>
    </html>

