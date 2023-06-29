<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: updateFornecedorOriginal.php
# Autor:    Eliakim Ramos | João Madson
# Data:     10/12/2019
# Objetivo: Programa de incluir contrato
#-------------------------------------------------------------------------
require_once dirname(__FILE__) . '/../funcoes.php';
require_once "./ClassContratos.php";

session_start();

# Exibe Aba Membro de Comissão - Formulário A #
    
    $ObjContrato = new Contrato();
    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        if(!empty($_POST["update"])){
            $db = conexao();
            $sqlS = 'select cdocpcsequ, aforcrsequ from sfpc.tbcontratosfpc where aforcrseq1 is null ';
            
            $resultado = executarSQL($db, $sqlS);
            while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
                $dbDados[] = $retorno;
            }
            if(!empty($dbDados)){
                for($i=0; $i<count($dbDados); $i++){
                    $sqlU = "update sfpc.tbcontratosfpc set aforcrseq1 = ".$dbDados[$i]->aforcrsequ." where aforcrseq1 is null and cdocpcsequ = ".$dbDados[$i]->cdocpcsequ;
                    $resultado = executarSQL($db, $sqlU);
                }
                $teste = "Atualizado!";
            }else{
                $teste = "Nada a atualizar!";
            }
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
    <form action="updateFornecedorOriginal.php" method="post" id="FornecedorOriginal" name="FornecedorOriginal">
    <input type="hidden" name="op" value="IncluirContrato">
    <input type="hidden" name="ctpcomcodi" value="<?php echo $_SESSION['origemScc'];?>">
        <div style="padding-left: 25vw; padding-right: 25vw; padding-bottom: 25vw; padding-top: 15vw;">
            <table cellpadding="3" border="0" summary="" align="center">
                <tr bgcolor="#ffffff">
                    <td colspan="8" align="center">
                    <?php
                        echo !empty($teste)?$teste:"Clique para trazer o fornecedor para o campo de fornecedor original:";
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
        </div>
    </form>
 
    <!-- Fim Modal -->
    </body>
    </html>

