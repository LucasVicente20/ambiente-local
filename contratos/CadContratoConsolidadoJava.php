<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadContratoConsolidadoJava.php
# Autor:    João Madson
# Data:     19/04/2021
# Objetivo: Gerar link direto para Contrato Consolidado Java.
#-------------------------------------------------------------------------
require_once dirname(__FILE__) . '/../funcoes.php';
session_start();

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
        <div style="padding-left: 25vw; padding-right: 25vw; padding-bottom: 25vw; padding-top: 15vw;">
                <p>Clique no botão para ser direcionado à página de Contrato Consolidado na versão de Java</p>
                <input type="submit" name="Prosseguir" value="Prosseguir" onclick="window.location.href = 'http://www.recife.pe.gov.br/contratos-emprel/paginaspublicas/ConsultaContratoConsolidado.jsf?portalCompras=true'" >

        </div>
    </body>

</html>
