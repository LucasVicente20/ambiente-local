<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadContratoManterEspecial.php
# Autor:    Eliakim Ramos | João Madson
# Data:     10/12/2019
# Objetivo: Programa de incluir contrato
#-------------------------------------------------------------------------


# Acesso ao arquivo de funções #
include "../funcoes.php";
require_once "ClassContratoManter.php";
require_once "ClassContratos.php";
require_once "ClassMedicao.php";
include "funcoesAbaContratos.php";
# Abas
include "AbaContratoManter.php";
include "AbaApostilamentoContratoManter.php";
include "AbaItemContratoManter.php";
include "AbaAditivoContratoManter.php";
include "AbaItemPregao.php";

# Executa o controle de segurança	#
session_start();
Seguranca();

# Variáveis com o global off #
$idRegistro = '';
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if(!empty($_SESSION['item'])){
        $_SESSION['itemincluir'] = $_SESSION['item'];
        unset($_SESSION['item']);
        $Destino        = "B";
    }else{
        $Destino       	= $_POST['Destino'];
    }
	$Origem            	= $_POST['Origem'];
	// $Destino           	= $_POST['Destino'];
	$IdMembro		   	= $_POST['Membro'];
	$IdFornecedorInsc  	= $_POST['IdFornecedorInsc'];
	$SituacaoFornecedor = $_POST['SituacaoFornecedor'];
    $CodLoteSelecionado = $_POST['CodLoteSelecionado'];
    $idRegistro         = $_POST['idregistro'];
    // print_r($dadosContratos);
	$_SESSION['Botao'] = $_POST['Botao'];
} else {
	$Origem            = "";
	$Destino           = $_GET['Destino'];
} 

$Processo             = $_SESSION['Processo'];
$ProcessoAno          = $_SESSION['ProcessoAno'];
$ComissaoCodigo       = $_SESSION['ComissaoCodigo'];
$OrgaoLicitanteCodigo = $_SESSION['OrgaoLicitanteCodigo'];
$PregaoCod			= $_SESSION['PregaoCod'];
																				
$_SESSION['CodFornecedorSelecionadoClassificacao'] 	= null;
$_SESSION['CodLoteSelecionadoClassificacao'] 		= null;
$_SESSION['CodSituacaoSelecionadoClassificacao'] 	= null;


# Aba de Membro da Comissão  - Formulário A #
if( $Origem == "A" or $Origem == "" ){
    
    //Validação Aba A
    if( $_SESSION['Botao'] == "A" ){
            $Destino = "B";
    }
     //var_dump($Linha);die;
    if($idRegistro)
    {
        //Validação Aba B
        if( $_SESSION['Botao'] == "B"){
            $Destino = "C";
        }

        if($idRegistro) {
            ExibeAbas($Destino);
        } else if ($Destino == "C" or $Destino == "D" or $Destino == "E") {
            if($Origem == "A" and $_SESSION['Mens'] == 0) {
                $_SESSION['Mens'] = 1;
                $_SESSION['Tipo'] = 2;
                $_SESSION['Mensagem'] .= "Para avançar, é necessário que todos os Fornecedores estejam com situação diferente de AGUARDANDO";
            }

            ExibeAbas($Origem);
        } else if($_SESSION['Botao'] == "B" or $Destino == "B") {
            $Destino = "B";
            ExibeAbas($Destino);
        } else {
            ExibeAbas($Origem);
        }
    } else {
        if($Origem == "A" and $_SESSION['Mens'] == 0) {
            $_SESSION['Mens'] = 1;
            $_SESSION['Tipo'] = 2;
            $_SESSION['Mensagem'] .= "Para avançar, é necessário que exista um Membro de Comissão marcado como Pregoeiro";
        }
        ExibeAbas($Origem);
    }
}

# Aba de Fornecedores Inscritos - Formulário B #
if( $Origem == "B" ){
        
    //Validação Aba B
    if( $_SESSION['Botao'] == "B"){
            $Destino = "C";
    }

    if($idRegistro) {
        ExibeAbas($Destino);
    } else if ($Destino == "C" or $Destino == "D" or $Destino == "E") {
        if($Origem == "B" and $_SESSION['Mens'] == 0) {
            $_SESSION['Mens'] = 1;
            $_SESSION['Tipo'] = 2;
            $_SESSION['Mensagem'] .= "Para avançar, é necessário que todos os Fornecedores estejam com situação diferente de AGUARDANDO";
        }

        ExibeAbas($Origem);
    } else if($_SESSION['Botao'] == "A" or $Destino == "A") {
        $Destino = "A";
        ExibeAbas($Destino);
    } else {
        ExibeAbas($Origem);
    }
}

# Aba de Fornecedores Credenciados - Formulário C #
if( $Origem == "C" ){
    if( $_SESSION['Botao'] == "C" ){
            $Destino = "D";
    }
    ExibeAbas($Destino);
}

# Aba de Itens - Formulário D #
if( $Origem == "D" ){
    if( $_SESSION['Botao'] == "D" ){
            $Destino = "E";
    }

    ExibeAbas($Destino);
}

# Aba de Itens - Formulário D #
if( $Origem == "E" ){
    if( $_SESSION['Botao'] == "E" ){
            $Destino = "A";
    }

    ExibeAbas($Destino);
}
# Função para Chamada do Formulário de cada Aba #
function ExibeAbas($Destino){
	if( $Destino == "A" or $Destino == "" ){
        ExibeAbaContratoManterEspecial();
	} else if( $Destino == "B" ){
        ExibeAbaItemContratoManter();
	} else if( $Destino == "C" ){
        ExibeAbaAditivoContratoManter();
	} else if( $Destino == "D" ){
        ExibeAbaApostilamentoContratoManter();
	} else if( $Destino == "E" ){
        ExibeAbaItemPregao();
	}
}

?>
