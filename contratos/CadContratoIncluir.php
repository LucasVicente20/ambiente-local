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
include "funcoesAbaContratos.php";
# Abas
include "AbaContratoIncluir.php";
include "AbaItemContratoIncluir.php";

# Executa o controle de segurança	#
session_start();
Seguranca();

# Variáveis com o global off #

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$Origem            	= $_POST['Origem'];
	$Destino           	= $_POST['Destino'];
	$IdMembro		   	= $_POST['Membro'];
	$IdFornecedorInsc  	= $_POST['IdFornecedorInsc'];
	$SituacaoFornecedor = $_POST['SituacaoFornecedor'];
	$CodLoteSelecionado = $_POST['CodLoteSelecionado'];
	
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


# Aba de Inclusão de Contrato  - Formulário A #

        ExibeAbas($Destino);


# Função para Chamada do Formulário de cada Aba #
function ExibeAbas($Destino){
	if( $Destino == "A" or $Destino == "" ){
        ExibeAbaContratoIncluir();
	} else if( $Destino == "B" ){
        ExibeAbaItemContratoIncluir();
	} 
}

?>
