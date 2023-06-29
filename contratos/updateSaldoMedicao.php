<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: AbaContratoIncluir.php
# Autor:    Eliakim Ramos | João Madson
# Data:     10/12/2019
# Objetivo: Programa de incluir contrato
#-------------------------------------------------------------------------
require_once dirname(__FILE__) . '/../funcoes.php';
require_once "./ClassContratos.php";

session_start();

# Exibe Aba Membro de Comissão - Formulário A #
    
    $ObjContrato = new Contrato();
    if($_SERVER['REQUEST_METHOD'] == "POST"){

        if(isset($_POST["update"])){

            $db = conexao();
            $sqlS = "UPDATE sfpc.tbmedicaocontrato medcontrato
                        SET dmedcoaprt = '2021-06-16 14:00:00', vmedcovalm = (SELECT sum(itemm.vimedcvalr)
                        from sfpc.tbmedicaocontrato medc
                        inner join sfpc.tbitemmedicaocontrato itemm
                        on medc.cdocpcsequ = itemm.cdocpcsequ
                        and medc.cmedcosequ = itemm.cmedcosequ
                        and medc.cdocpcsequ = medcontrato.cdocpcsequ
                        and medc.cmedcosequ = medcontrato.cmedcosequ
                        GROUP BY medc.cdocpcsequ,medc.cmedcosequ)
                        WHERE medcontrato.vmedcovalm is null";
            
            $resultado = executarSQL($db, $sqlS);
            $teste = "Atualizado!";
    
        }else{
            
            $teste = "Erro ao atualizar!";
        }
    }
?>
    <html>
    <?php
# Carrega o layout padrão #
    layout();
    ?>
    <script language="javascript" src="../janela.js" type="text/javascript"></script>
    <script language="javascript" src="../import/jquery/jquery-1.7.2.min.js" type="text/javascript"></script>
    <script language="javascript" src="../import/jquery/jquery.maskmoney.js" type="text/javascript"></script>
    <script language="javascript" src="../import/jquery/jquery.maskedinput.js" type="text/javascript"></script>​
    <script language="javascript" type=""> 

        <?php MenuAcesso(); ?>
        
    </script>

    <body background="../midia/bg.gif" marginwidth="0" marginheight="0">
    <script language="JavaScript" src="../menu.js"></script>
    <script language="JavaScript">
        Init();
    </script> 
    <form action="updateSaldoMedicao.php" method="POST" id="updateSaldoMedicao" name="updateSaldoMedicao">
        <div style="padding-left: 25vw; padding-right: 25vw; padding-bottom: 25vw; padding-top: 15vw;">
            <table cellpadding="3" border="0" summary="" align="center">
                <tr bgcolor="#ffffff">
                    <td colspan="8" align="center">
                    <?php
                        echo !empty($teste)?$teste:"Clique para atualizar o saldo da medição:";
                    ?>
                    <br><br>
                    <input type="submit" name="update" value="Atualizar Saldo Medição" class="botao"></button>
                    </td>
            
                </tr>       
            </table>
        </div>
    </form>
 
    <!-- Fim Modal -->
    </body>
    </html>

