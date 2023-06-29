<?php
//---------------------------- 
// Classe Conexão
//---------------------------- 
include_once("/home/wwwdisco1/portal/html/common/dataSourcePHP/dataSourcePHP.phb"); // A extensão é (.phb), pois a classe está compilada
class DataSourceSFPC extends dataSourcePHP {

     public function query($sql) { 	
         return $this->instance->query($sql);      	
     } 
}
?>
