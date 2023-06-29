<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadPregaoPresencialSessaoPublica.php
# Autor:    Eliakim Ramos | Edson Dionisio
# Data:     23/04/2020
# Objetivo: Programa de incluir contrato
#-------------------------------------------------------------------------


# Acesso ao arquivo de funções #
include "../funcoes.php";


# Executa o controle de segurança	#
session_start();
Seguranca();

?>
<html>
<?php

//$dadosTipoCompra = $ObjApostilamento->ListTipoCompra();
# Carrega o layout padrão #
// layout();
?>
<!-- <script language="javascript" src="../janela.js" type="text/javascript"></script>​
<script language="javascript" type=""></script>
<link rel="stylesheet" type="text/css" href="../estilo.css?v=<?php echo time(); ?>"> -->

<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
    <!-- <script language="JavaScript" src="../menu.js"></script> -->
    <!-- <script language="JavaScript">
        Init();
    </script> -->
    <form action="../registropreco/RotEnviaEmailGestorARPAVencer.php" method="get" id="EnviaEmails" name="RotEnviaEmailGestorARPAVencer">
        <input type="hidden" name="acao" value="enviar">
        <input type="submit" name="Envia" value="Envia Emails ARPs a Vencer"></button>
    </form>
    <form action="RotEnviaEmailGestorContratoAVencer.php" method="get" id="EnviaEmails" name="RotEnviaEmailGestorContratoAVencer">
        <input type="hidden" name="acao" value="enviar">
        <input type="submit" name="Envia" value="Envia Emails Contratos a Vencer"></button>
    </form>
    <form action="../fornecedores/RotEmailFornecedoresCertidoesVencidas.php" method="get" id="EnviaEmails" name="RotEmailFornecedoresCertidoesVencidas">
        <input type="hidden" name="acao" value="enviar">
        <input type="submit" name="Envia" value="Envia Emails Certidões Vencidas"></button>
    </form>
</body>

</html>