<?php
# -------------------------------------------------------------------------
# Portal da Compras
# Programa: CadContratoManter.php
# Autor:    Eliakim Ramos | João Madson
# Data:     12/12/2019
# -------------------------------------------------------------------------
session_start();

switch($_POST['op']){
    case "pegaSize":
        $_SESSION['sWidth'] = $_POST['sWidth'];
    break;
}