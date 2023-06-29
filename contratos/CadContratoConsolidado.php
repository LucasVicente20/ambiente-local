<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadPregaoPresencialSessaoPublica.php
# Autor:    Eliakim Ramos | João Madson
# Data:     10/12/2019
# Objetivo: Programa de incluir contrato
#-------------------------------------------------------------------------


# Acesso ao arquivo de funções #
include "../funcoes.php";
require_once "ClassContratoManter.php";
require_once "ClassContratos.php";
require_once "ClassMedicao.php";
require_once "ClassContratoConsolidado.php";
include "funcoesAbaContratos.php";
# Abas
include "AbaContratoOriginal.php";
include "AbaContratoConsolidado.php";
include "AbaContratoConsolidadoMedicao.php";
include "AbaContratoConsolidadoAditivo.php";
include "AbaContratoConsolidadoApostilamento.php";

# Executa o controle de segurança	#
session_start();
// var_dump($_POST);die;
if(empty($_POST['internet']) && empty($_GET['internet'])){
  Seguranca();
  
}
unset( $_SESSION['fiscal_selecionado']);

?>
<html>
<script language="javascript" src="../import/jquery/jquery-1.7.2.min.js" type="text/javascript"></script>
<script language="javascript" type="">

        var sWidth = screen.width;
        function postCCI(sWidth){
                $.post("postDadosCCI.php", {op:"pegaSize", sWidth:sWidth});
        }
        postCCI(sWidth);

</script>
<input type="hidden" id="size" name="size" value="">
</html>
<?php
// var_dump($_POST);die;
# Variáveis com o global off #
$idRegistro = '';
$idKeyAditivo = 0;
$idKeyApostilamento = 0;
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$Origem            	= $_POST['Origem'];
	$Destino           	= $_POST['Destino'];
    $idRegistro         = $_POST['idregistro'];
    $idKeyAditivo		= $_POST['idAditivo'];
    $idKeyApostilamento	= $_POST['idApostilamento'];
}else if ($_SERVER['REQUEST_METHOD'] == "GET") {
	$Origem            	= $_GET['Origem'];
	$Destino           	= $_GET['Destino'];
    $idRegistro         = $_GET['idregistro'];
    $idKeyAditivo		= $_GET['idAditivo'];
    $idKeyApostilamento	= $_GET['idApostilamento'];
} else {
	$Origem            = "";
	$Destino           = $_GET['Destino'];
} 

 ExibeAbas($Destino,$idKeyAditivo,$idKeyApostilamento);
 
# Função para Chamada do Formulário de cada Aba #
function ExibeAbas($Destino,$idKeyAditivo,$idKeyApostilamento){
	if( $Destino == "A" or $Destino == "" ){
        ExibeAbaContratoConsolidado();
	} else if( $Destino == "B" ){
        ExibeAbaContratoOriginal();
	} else if( $Destino == "C" ){
        ExibeAbaContratoMedicao();
	} else if( $Destino == "D" ){
        ExibeAbaContratoAditivo($idKeyAditivo);
	} else if( $Destino == "E" ){
        ExibeAbaContratoApostilamento($idKeyApostilamento);
	}
}

?>
