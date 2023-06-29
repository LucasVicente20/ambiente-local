<?php
function consultaNaturezaJuridica(){
    $sql = "SELECT cfornjsequ, efornjtpnj from sfpc.tbfornecedortiponaturezajuridica";
    $result = executarPGSQL($sql);
    
    while($result->fetchInto($dadosResultado, DB_FETCHMODE_OBJECT)){
            $dadosRetorno[] = $dadosResultado;
    }
    return $dadosRetorno;
}
?>