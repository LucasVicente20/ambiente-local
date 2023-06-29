<?php
#------------------------------------------------------------------------------
# Portal da DGC
# Programa: TabParametrosGerais.php
# Autor:    Marcos Túlio de Almeida Alves
# Data:     19/07/2011
# Objetivo: Manter os Tipos de Parametros Gerais
# OBS.:     Tabulação 2 espaços
#------------------------------------------------------------------------------
# Alterado: Luiz Alves de Oliveira Neto
# Data:     27/10/2011
#------------------------------------------------------------------------------
# Alterado: João Batista Brito
# Data:     19/06/2012
# Objetivo: Acrescentar 04 campos na tela Tipos de Parametros Gerais
#------------------------------------------------------------------------------
# Alterado: João Batista Brito
# Data:     26/06/2012
# Objetivo: Exemplo de formato nas tabelas de Parâmetros Gerais - Redmine 11192
#------------------------------------------------------------------------------
# Alterado: João Batista Brito
# Data:     26/06/2012
# Objetivo: Aumentar para 20 SubElementos Despesas Especiais SCCE - Redmine 13712
#------------------------------------------------------------------------------
# Alterado: José Almir <jose.almir@pitang.com>
# Data:     12/11/2014
# Objetivo: Adiciona parâmetro que define o período de bloqueio da virada de ano
#------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		11/07/2018
# Objetivo: Tarefa Redmine 199020
#------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		19/10/2018
# Objetivo: Tarefa Redmine 205439
#------------------------------------------------------------------------------
# Alterado: Pitang Agile Ti - Caio Coutinho
# Data:		10/12/2018
# Objetivo: Tarefa Redmine 206574
#------------------------------------------------------------------------------
# Alterado: João Madson
# Data:		03/12/2019
# Objetivo: Tarefa Redmine 208774
#------------------------------------------------------------------------------
# Alterado: João Madson
# Data:		13/12/2019
# Objetivo: Tarefa Redmine #227829
#------------------------------------------------------------------------------
# Alterado: João Madson
# Data:		07/02/2020
# Objetivo: Tarefa Redmine #229732
#------------------------------------------------------------------------------
# Alterado: João Madson
# Data:		11/02/2021
# Objetivo: Tarefa Redmine #243591
#------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../compras/funcoesCompras.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabParametrosGeraisManter.php' );

$Ano = date("Y");

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST"){
	$Botao                                       = $_POST['Botao'];	
	$ExtensaoArquivosValidos                     = $_POST['ExtensaoArquivosValidos'];
	$TamanhoMaxNomeDocumento                     = $_POST['TamanhoMaxNomeDocumento'];
	$TamanhoMaxDocumento                         = $_POST['TamanhoMaxDocumento'];
	$TamanhoMaxObjeto                            = $_POST['TamanhoMaxObjeto'];
	$TamanhoMaxJustificativa                     = $_POST['TamanhoMaxJustificativa'];
	$TamanhoMaxDescricaoServico                  = $_POST['TamanhoMaxDescricaoServico'];
	$PrazoMaxValidadePrecoCompraDireta           = $_POST['PrazoMaxValidadePrecoCompraDireta'];
	$PrazoMaxValidadePrecoLicitacao              = $_POST['PrazoMaxValidadePrecoLicitacao'];
	$QuantidadeMediaLicitacao                    = $_POST['QuantidadeMediaLicitacao'];
	$QuantidadeDiasEncerramentoVigenciaDispensa  = $_POST['QuantidadeDiasEncerramentoVigenciaDispensa'];
	$Email                                       = $_POST['Email'];
	$SubElementos                                = $_POST['SubElementos'];
	$Orgao                                       = $_POST['Orgao'];
	$NomeEmpresa								 = $_POST['NomeEmpresa'];
	$NomeOrgao1									 = $_POST['NomeOrgao1'];
	$NomeOrgao2									 = $_POST['NomeOrgao2'];
	$NomeSetor1									 = $_POST['NomeSetor1'];
	$NomeSistema								 = $_POST['NomeSistema'];
	
	# Inicio Campos Novos Adicionados # 
	$NumDiasVigenciaLicitacao                    = $_POST['NumDiasVigenciaLicitacao'];
	$SubElementosSubEmpenharMenor				 = $_POST['SubElementosSubEmpenharMenor'];
	$SubElementoObras							 = $_POST['SubElementoObras']; 
	$PercentualLimitePrecos 					 = $_POST['PercentualLimitePrecos'];
	$critica  									 = $_POST['critica'];
	$OrgaosPiloto								 = $_POST['OrgaosPiloto'];
	$FatorCarona                                 = $_POST['FatorCarona'];
    $PercentualGeralAdesao      				 = $_POST['PercentualGeralAdesao'];
    $PercentualAdesao							 = $_POST['PercentualAdesao'];
    $FatorCompraMaxCarona						 = $_POST['FatorCompraMaxCarona'];
	# Final Campos Novos Adicionados #
		
	// Campo de período de bloqueio
	$dataInicialBloqueio						 = $_POST['DataIni'];
	$dataFinalBloqueio							 = $_POST['DataFim'];
	// Campo de Liberação de Validação de Bloqueio Orçamentário |MADSON|
	$dataInicialLibValBloqueio						 = $_POST['DataIniLibValBloq'];
	$dataFinalLibValBloqueio						 = $_POST['DataFimLibValBloq'];
	// var_dump($_POST['DataIniLibValBloq']);exit;
	$emailGestoresARP                     = $_POST['emailGestoresARP'];
} else {
	$Mens     = $_GET['Mens'];
	$Tipo     = $_GET['Tipo'];
	$Mensagem = urldecode($_GET['Mensagem']);
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma ="TabParametrosGeraisManter.php";

$db = Conexao();
if ($Botao == "Limpar") {
	header("location: TabParametrosGeraisManter.php");
	exit;
} else if ($Botao == "Alterar") {
	$Mens     = 0;
	$Mensagem = "Informe: ";
	$MensErroDataIni = ValidaData($dataInicialBloqueio);

	if ($MensErroDataIni != "") {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		$Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.DataIni.focus();\" class=\"titulo2\">Data Inicial Válida</a>";
	}
	$MensErroDataFim = ValidaData($dataFinalBloqueio);
	
	if ($MensErroDataFim != "") {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}	
		$Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.DataFim.focus();\" class=\"titulo2\">Data Final Válida</a>";
	}
	// Conforme #227829 Retirada por não obrigação das datas
	// //Madson Validação para as datas de liberação da validação do bloqueio
	
	// $MensErroDataIniValLib = ValidaData($dataInicialLibValBloqueio);
	
	// if ($MensErroDataIniValLib != "") {
	// 	if ($Mens == 1) {
	// 		$Mensagem .= ", ";
	// 	}
	// 	$Mens  = 1;
	// 	$Tipo  = 2;
	// 	$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.DataIniLibValBloq.focus();\" class=\"titulo2\">Data Inicial da Liberação Válida</a>";
	// }
	// $MensErroDataFimValLib = ValidaData($dataFinalLibValBloqueio);
	
	// if ($MensErroDataFimValLib != "") {
	// 	if ($Mens == 1) {
	// 		$Mensagem .= ", ";
	// 	}	
	// 	$Mens  = 1;
	// 	$Tipo  = 2;
	// 	$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.DataFimLibValBloq.focus();\" class=\"titulo2\">Data Final da Liberação Válida</a>";
	// }
	$MensErroDataIniValLib = $dataInicialLibValBloqueio;
	$MensErroDataFimValLib = $dataFinalLibValBloqueio;
	//daqui pra baixo
	if (($MensErroDataIniValLib != "" && $MensErroDataFimValLib == "" )) {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		$Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.DataFimLibValBloq.focus();\" class=\"titulo2\">Data final da Liberação de Validação de Bloqueio Orçamentário precisa ser informada</a>";
	}
	// 
	if (($MensErroDataIniValLib == "" && $MensErroDataFimValLib != "")) {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		$Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.DataIniLibValBloq.focus();\" class=\"titulo2\">Data inicial da Liberação de Validação de Bloqueio Orçamentário precisa ser informada</a>";
	}
	
	if ($MensErroDataIni == '' && $MensErroDataFim == '') {
		$MensErroPeriodo = ValidaPeriodo(
			$dataInicialBloqueio, 
			$dataFinalBloqueio, 
			$Mens, 
			"TabParametrosGeraisManter"
		);
		
		if ($MensErroPeriodo != "") {
			if ($Mens == 1) {
				$Mensagem .= ", ";
			}
			$Mens  = 1;
			$Tipo  = 2;
			$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.DataIni.focus();\" class=\"titulo2\">Data Final igual ou maior que Data Inicial</a>";
		}
	}
	if ($MensErroDataIniValLib == '' && $MensErroDataFimValLib == '') {
		$MensErroPeriodo = ValidaPeriodo(
			$dataInicialBloqueio, 
			$dataFinalBloqueio, 
			$Mens, 
			"TabParametrosGeraisManter"
		);
		
		if ($MensErroPeriodo != "") {
			if ($Mens == 1) {
				$Mensagem .= ", ";
			}
			$Mens  = 1;
			$Tipo  = 2;
			$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.DataIni.focus();\" class=\"titulo2\">Data Final igual ou maior que Data Inicial</a>";
		}
	}
	
	if ($ExtensaoArquivosValidos == "") {
      	if($Mens == 1){ $Mensagem.=", "; }
	    	$Mens  = 1;
		 	$Tipo  = 2;
		 	$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.ExtensaoArquivosValidos.focus();\" class=\"titulo2\">Tipos de arquivos válidos</a>";
	  	} elseif(!preg_match("/^(\.[a-zA-Z]{3,4}(,(\s)*\.[a-zA-Z]{3,4})*)$/" , $ExtensaoArquivosValidos)) {
		    if ($Mens == 1) {
				$Mensagem.=", ";
			}
		    $Mens  = 1;
			$Tipo  = 2;
			$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.ExtensaoArquivosValidos.focus();\" class=\"titulo2\">Tipos de arquivos válidos deve ser extensões contendo ponto e separados de vírgulas. Exemplo: .zip, .jpg, .bmp, .docx</a>";		
		}		    
		
	if ($TamanhoMaxNomeDocumento == "") {
      	if ($Mens == 1) {
			$Mensagem.=", ";
		}
		$Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.TamanhoMaxNomeDocumento.focus();\" class=\"titulo2\">Tamanho Máximo do Nome do Documento</a>";
	} elseif ($TamanhoMaxNomeDocumento == 0) {
		if ($Mens == 1) {
			$Mensagem.=", ";
		}
		$Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.TamanhoMaxNomeDocumento.focus();\" class=\"titulo2\">O Tamanho Máximo do Nome do Documento deve ser númerico e diferente de zero</a>";
	} elseif (strlen($TamanhoMaxNomeDocumento)  > 4) {
		if ($Mens == 1) {
			$Mensagem.=", ";
		}
		$Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.TamanhoMaxNomeDocumento.focus();\" class=\"titulo2\"> O Campo Tamanho máximo do nome do documento  deverá conter até 4 caracteres numéricos</a>";	
	}
		
	if ($TamanhoMaxDocumento == "") {
	  	if ($Mens == 1) {
			  $Mensagem.=", ";
		}
	    $Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.TamanhoMaxDocumento.focus();\" class=\"titulo2\">Tamanho Máximo do documento</a>";
	} elseif($TamanhoMaxDocumento == 0) {
		if ($Mens == 1) {
			$Mensagem.=", ";
		}
		$Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.TamanhoMaxDocumento.focus();\" class=\"titulo2\">Tamanho Máximo do Documento deve ser númerico e diferente de 0</a>";
	} elseif (!(SoNumeros($TamanhoMaxDocumento))) {
      	if ($Mens == 1) {
			  $Mensagem.=", ";
		}
		$Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.TamanhoMaxDocumento.focus();\" class=\"titulo2\">O Tamanho Máximo do Documento deve conter apenas números</a>";	
	} elseif (strlen($TamanhoMaxDocumento)  > 4) {
		if ($Mens == 1) {
			$Mensagem.=", ";
		}
		$Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.TamanhoMaxDocumento.focus();\" class=\"titulo2\">O Tamanho Máximo do Documento deve conter até 4 caracteres númericos</a>";	
  	}
	
	if ($TamanhoMaxObjeto == "") {
	  	if ($Mens == 1) {
			  $Mensagem.=", "; 
		}
		$Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.TamanhoMaxObjeto.focus();\" class=\"titulo2\">Tamanho Máximo do Objeto</a>";
	} elseif ($TamanhoMaxObjeto == 0) {
		if ($Mens == 1) {
			$Mensagem.=", ";
		}
		$Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.TamanhoMaxObjeto.focus();\" class=\"titulo2\">Tamanho Máximo do Objeto deve ser númerico e diferente de 0</a>";
	} elseif ($TamanhoMaxObjeto < 4 || ($TamanhoMaxObjeto > 1000)) {
	    if ($Mens == 1) {
			$Mensagem.=", ";
		}
		$Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.TamanhoMaxObjeto.focus();\" class=\"titulo2\">valor do campo Tamanho Máximo do Objeto deve ser um número de 4 a 1000</a>";
	} elseif (!(SoNumeros($TamanhoMaxObjeto))) {
        if ($Mens == 1) {
			$Mensagem.=", ";
		}
		$Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.TamanhoMaxObjeto.focus();\" class=\"titulo2\">O Tamanho Máximo do Objeto deve conter apenas números e ser diferente de zero</a>";	
	}
	
	if ($TamanhoMaxJustificativa == "") {
	  	if ($Mens == 1) {
			  $Mensagem.=", ";
		}
		$Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.TamanhoMaxJustificativa.focus();\" class=\"titulo2\">O Tamanho da Justificativa</a>";
	} elseif ($TamanhoMaxJustificativa == 0) {
		if ($Mens == 1) {
			$Mensagem.=", ";
		}
	    $Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.TamanhoMaxJustificativa.focus();\" class=\"titulo2\">O Tamanho da Justificativa deve ser valor númerico e diferente de 0</a>";
	} elseif (($TamanhoMaxJustificativa < 4) || ($TamanhoMaxJustificativa > 1000)) {
	    if ($Mens == 1) {
			$Mensagem.=", ";
		}
		$Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.TamanhoMaxJustificativa.focus();\" class=\"titulo2\">Tamanho da Justificativa deve possuir valores de 4 a 1000</a>";
	}
	
	if ($TamanhoMaxDescricaoServico == "") {
	  	if ($Mens == 1) {
			  $Mensagem.=", ";
		}
		$Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.TamanhoMaxDescricaoServico.focus();\" class=\"titulo2\">O Tamanho da Descrição Detalhada do Serviço</a>";
	} elseif ($TamanhoMaxDescricaoServico == 0) {
		if ($Mens == 1) {
			$Mensagem.=", ";
		}
		$Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.TamanhoMaxDescricaoServico.focus();\" class=\"titulo2\">O Tamanho da Descrição Detalhada do Serviço deve ter valor diferente de 0</a>";
	} elseif (($TamanhoMaxDescricaoServico < 4 ) || ($TamanhoMaxDescricaoServico > 1000 )) {
	    if ($Mens == 1) {
			$Mensagem.=", ";
		}
		$Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.TamanhoMaxDescricaoServico.focus();\" class=\"titulo2\">O Tamanho da Descrição Detalhada do Serviço deve possuir valores de 4 a 1000</a>";
	}
	
	if ($PrazoMaxValidadePrecoCompraDireta == "") {
	  	if ($Mens == 1) {
			  $Mensagem.=", ";
		}
		$Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.PrazoMaxValidadePrecoCompraDireta.focus();\" class=\"titulo2\">O Prazo Máximo de Validade do Preço da Compra Direta</a>";
	} elseif (!(SoNumeros($PrazoMaxValidadePrecoCompraDireta))) {
        if ($Mens == 1) {
			$Mensagem.=", ";
		}
		$Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.PrazoMaxValidadePrecoCompraDireta.focus();\" class=\"titulo2\">O Prazo Máximo de Validade do Preço da Compra Direta deve conter apenas números</a>";	
	} elseif ($PrazoMaxValidadePrecoCompraDireta == 0) {
		if ($Mens == 1) {
			$Mensagem.=", ";
		}
		$Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.PrazoMaxValidadePrecoCompraDireta.focus();\" class=\"titulo2\">O Prazo Máximo de Validade do Preço Compra Direta deve ter o valor diferente de 0</a>";
	} elseif (($PrazoMaxValidadePrecoCompraDireta < 4 ) || ($PrazoMaxValidadePrecoCompraDireta > 1000)) {
		if ($Mens == 1) {
			$Mensagem.=", ";
		}
		$Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.PrazoMaxValidadePrecoCompraDireta.focus();\" class=\"titulo2\">O Prazo máximo de validade do preço da Compra Direta deve possuir valores de 4 a 1000</a>";	
	}
	
	if ($PrazoMaxValidadePrecoLicitacao == "") {
	  	if ($Mens == 1) {
			  $Mensagem.=", ";
		}
		$Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.PrazoMaxValidadePrecoLicitacao.focus();\" class=\"titulo2\">O Prazo Máximo de Validade do Preço de Licitação</a>";
	} elseif ($PrazoMaxValidadePrecoLicitacao == 0) {
		if ($Mens == 1) {
			$Mensagem.=", ";
		}
		$Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.PrazoMaxValidadePrecoLicitacao.focus();\" class=\"titulo2\">O Prazo Máximo de Validade do Preço de Licitação deve ser númerico e maior que zero</a>";
	} elseif (strlen($PrazoMaxValidadePrecoLicitacao) > 4) {
	    if ($Mens == 1) {
			$Mensagem.=", ";
		}
		$Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.PrazoMaxValidadePrecoLicitacao.focus();\" class=\"titulo2\">O Prazo Máximo de Validade do Preço de Licitação deve possuir até 4 caracteres númericos</a>";
	}
	
	/*else  if (!(SoNumeros($PrazoMaxValidadePrecoLicitacao))){
         	if($Mens == 1){ $Mensagem.=", "; }
		    $Mens      = 1;
		    $Tipo      = 2;
		    $Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.PrazoMaxValidadePrecoLicitacao.focus();\" class=\"titulo2\">O Prazo Máximo de Validade do Preço de Licitação deve conter apenas números</a>";	
	}else if ( !($PrazoMaxValidadePrecoLicitacao = 4)){
			 if($Mens == 1){ $Mensagem.=", "; }
		    $Mens      = 1;
		    $Tipo      = 2;
		    $Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.PrazoMaxValidadePrecoLicitacao.focus();\" class=\"titulo2\">O Prazo Máximo de Validade do Preço de Licitação deve possuir 4 caracteres númericos</a>";	
		}
		*/
	
	if ($QuantidadeMediaLicitacao == "" ) {
	  	if ($Mens == 1) {
			  $Mensagem.=", ";
		}
		$Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.QuantidadeMediaLicitacao.focus();\" class=\"titulo2\">A Quantidade média de valores homologados para licitação</a>";
   	}
	
	if ($QuantidadeMediaLicitacao == 0 ) {
		if ($Mens == 1) {
			$Mensagem.=", ";
		}
		$Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.QuantidadeMediaLicitacao.focus();\" class=\"titulo2\">Quantidade média de valores homologados para licitação deve ser diferente de zero</a>";
	} elseif (!(preg_match("^[0-9]{2}$", $QuantidadeMediaLicitacao))) {
	  	if ($Mens == 1) {
			  $Mensagem.=", ";
		}
		$Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.QuantidadeMediaLicitacao.focus();\" class=\"titulo2\">A Quantidade média de valores homologados para licitação deve ter 2 caracteres numéricos</a>"; 
    }

	if ($QuantidadeDiasEncerramentoVigenciaDispensa == "") {
	  	if ($Mens == 1) {
			  $Mensagem.=", ";
		}
		$Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.QuantidadeDiasEncerramentoVigenciaDispensa.focus();\" class=\"titulo2\">A Quantidade de dias para encerramento da data de vigência da dispensa e Inexigibilidade</a>";
	} elseif (strlen($QuantidadeDiasEncerramentoVigenciaDispensa) > 4) {
	    if ($Mens == 1) {
			$Mensagem.=", ";
		}
		$Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.QuantidadeDiasEncerramentoVigenciaDispensa.focus();\" class=\"titulo2\">A Quantidade de dias para encerramento da data de vigência da dispensa e Inexigibilidade deve conter até 4 caracteres numéricos</a>";
	} elseif ($QuantidadeDiasEncerramentoVigenciaDispensa == 0) {
		if ($Mens == 1) {
			$Mensagem.=", ";
		}
		$Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.QuantidadeDiasEncerramentoVigenciaDispensa.focus();\" class=\"titulo2\">A Quantidade de dias para encerramento da data de vigência da dispensa e Inexigibilidade deve ter valor diferente de 0</a>";
    }
	
	if ($Email == "" || !strchr($Email, "@")) {
       if ($Mens == 1) {
		   $Mensagem.=", ";
		}
		$Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.Email.focus();\" class=\"titulo2\">E-Mail Válido</a>";
	} elseif (!(isEmailCorporativo($Email))) {
	    if ($Mens == 1) {
			$Mensagem.=", ";
		}
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.Email.focus();\" class=\"titulo2\">E-Mail deve conter @recife.pe.gov.br</a>";		
	}
	
	if ($SubElementos == "") {
      	if ($Mens == 1) {
			  $Mensagem.=", ";
		}
  	    $Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.SubElementos.focus();\" class=\"titulo2\">SubElementos</a>";
   	} else {
		#verificando se algum subelemento separado por vírgula está fora do padrão
		$subElementosArray = explode(',', $SubElementos);
		$isSubElementoValorInvalido = false;
		
		foreach ($subElementosArray as $subElementoItem) {
			$subElementoItem = trim($subElementoItem);
			
			if (!isElementoDespesa($subElementoItem)) {
				$isSubElementoValorInvalido = true;
			}
		}
	
		if ($isSubElementoValorInvalido) {
       		if ($Mens == 1) {
		   		$Mensagem.=", ";
			}
			$Mens  = 1;
			$Tipo  = 2;
			$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.SubElementos.focus();\" class=\"titulo2\">Campo 'SubElementos de Despesas especiais para a SCC' foi preenchido incorretamente: Um ou mais subelementos estão inválidos. Note que eles devem ser separados por vírgula</a>";		
		}
   	}	
	# Inicio do if dos Campos Novos Adicionados #
	//daqui pra cima
	if ($NumDiasVigenciaLicitacao == "") {
	  	if ($Mens == 1) {
			  $Mensagem.=", ";
		}
		$Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.NumDiasVigenciaLicitacao.focus();\" class=\"titulo2\">Número de Dias da Vigência de Licitação</a>";
	} elseif ($NumDiasVigenciaLicitacao == 0) {
		if ($Mens == 1) {
			$Mensagem.=", ";
		}
		$Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.NumDiasVigenciaLicitacao.focus();\" class=\"titulo2\">Número de Dias da Vigência de Licitação deve ser númerico e diferente de 0</a>";
	} elseif (!(SoNumeros($NumDiasVigenciaLicitacao))) {
        if ($Mens == 1) {
			$Mensagem.=", ";
		}
		$Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.NumDiasVigenciaLicitacao.focus();\" class=\"titulo2\">O Número de Dias da Vigência de Licitação deve conter apenas números</a>";	
	} elseif (strlen($NumDiasVigenciaLicitacao) > 3) {
		if ($Mens == 1) {
			$Mensagem.=", ";
		}
		$Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.NumDiasVigenciaLicitacao.focus();\" class=\"titulo2\">O Número de Dias da Vigência de Licitação deve conter até 3 caracteres númericos</a>";	
	}
	
	if ($SubElementosSubEmpenharMenor == "") {
	  	if ($Mens == 1) {
			  $Mensagem.=", ";
		}
		$Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.SubElementosSubEmpenharMenor.focus();\" class=\"titulo2\">Subelementos que permitem subempenhar a menor</a>";
    } else {
		# Verificando se algum subelemento separado por vírgula está fora do padrão
        $subElementosArray = explode(',', $SubElementosSubEmpenharMenor);
	    $isSubElementoValorInvalido = false;
		 
		foreach ($subElementosArray as $subElementoItem) {
		    $subElementoItem = trim($subElementoItem);
			 
			if (!isElementoDespesa($subElementoItem)) {
		     	$isSubElementoValorInvalido = true;
		   	}
	  	}
		
		if ($isSubElementoValorInvalido) {
  	  		if ($Mens == 1) {
					$Mensagem.=", ";
			}
		 	$Mens  = 1;
		 	$Tipo  = 2;
		 	$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.SubElementosSubEmpenharMenor.focus();\" class=\"titulo2\">Subelementos que permitem subempenhar a menor está inválido, o campo deve ser separados por vírgula</a>";		
	  	}
	}
	
	if ($SubElementoObras == "") {
	  	if ($Mens == 1) {
			$Mensagem.=", ";
		}
		$Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.SubElementoObras.focus();\" class=\"titulo2\">Subelementos de obras</a>";
    } else {
		# Verificando se algum subelemento separado por vírgula está fora do padrão
		$subElementosArray = explode(',', $SubElementoObras);
		$isSubElementoValorInvalido = false;
		
		foreach ($subElementosArray as $subElementoItem) {
			$subElementoItem = trim($subElementoItem);
			
			if (!isElementoDespesa($subElementoItem)) {
				$isSubElementoValorInvalido = true;
			}
		}

		if ($isSubElementoValorInvalido) {
			if ($Mens == 1) {
				$Mensagem.=", "; 
			}
			$Mens  = 1;
			$Tipo  = 2;
			$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.SubElementoObras.focus();\" class=\"titulo2\">Subelementos de Obras está inválido, o campo deve ser separados por vírgula</a>";		
		}
	}

	if (empty($OrgaosPiloto)) {
		$OrgaosPiloto = null;
	} else {
		$OrgaosPiloto = str_replace(',,', ',', str_replace(' ', '', $OrgaosPiloto));
		$OrgaosPiloto = (substr($OrgaosPiloto, -1) == ',') ? substr($OrgaosPiloto, 0, -1) : $OrgaosPiloto;
	}

    if (empty($PercentualGeralAdesao)) {
        $PercentualGeralAdesao = 0;
    }

    if (empty($PercentualAdesao)) {
        $PercentualAdesao = 0;
    }

    if (empty($FatorCompraMaxCarona)) {
        $FatorCompraMaxCarona = 0;
    }
		
	if ($PercentualLimitePrecos == "") {
		if ($Mens == 1){ 
			$Mensagem.=", "; 
		}
		$Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.PercentualLimitePrecos.focus();\" class=\"titulo2\">Percentual Limite de Preços TRP em relação a Média</a>";
	} elseif ($PercentualLimitePrecos == 0) {
		if ($Mens == 1) {
			$Mensagem.=", ";
		}
		$Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.PercentualLimitePrecos.focus();\" class=\"titulo2\">Percentual Limite de Preços TRP em relação a Média deve ser númerico e diferente de 0</a>";
	} elseif (!(SoNumeros($PercentualLimitePrecos))) {
        if ($Mens == 1) {
			$Mensagem.=", ";
		}
		$Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.PercentualLimitePrecos.focus();\" class=\"titulo2\">Percentual Limite de Preços TRP em relação a Média deve conter apenas números</a>";	
	} elseif (strlen($PercentualLimitePrecos) > 5) {
		if ($Mens == 1) {
			$Mensagem.=", "; 
		}
		$Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TabParametrosGeraisManter.PercentualLimitePrecos.focus();\" class=\"titulo2\">Percentual Limite de Preços TRP em relação a Média deve conter até 5 caracteres númericos</a>";	
	}			
	# Final do if dos Campos Novos Adicionados #	
	
	if ($Mens == 0) { 
		# Conecta com os bancos de dados #
    	$Data   = date("Y-m-d H:i:s");
	
		$db->query("BEGIN TRANSACTION");
	
		$sql = " SELECT MAX(CPARGETBID) FROM SFPC.TBPARAMETROSGERAIS ";
	
		$result  = $db->query($sql);
		 
		if (PEAR::isError($result)) {
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	 	} else {
	 		if (!empty($dataInicialBloqueio)) {
	 			$dataInicialBloqueio = new DataHora($dataInicialBloqueio);
	 			$dataInicialBloqueio = $dataInicialBloqueio->formata('Y-m-d') . ' 00:00:00';
	 		}
	 	
	 		if (!empty($dataFinalBloqueio)) {
	 			$dataFinalBloqueio = new DataHora($dataFinalBloqueio);
	 			$dataFinalBloqueio = $dataFinalBloqueio->formata('Y-m-d') . ' 23:59:59';
			 }
			// Madson Adição do campo de data liberação validação bloqueio #227829 alteração na cr anterior para permitir data vazia
			
			if (!empty($dataInicialLibValBloqueio)) {
				$dataInicialLibValBloqueio = new DataHora($dataInicialLibValBloqueio);
				$dataInicialLibValBloqueio = $dataInicialLibValBloqueio->formata('Y-m-d') . ' 00:00:00';
				$dataInicialLibValBloqueio = "'".$dataInicialLibValBloqueio."'";
			}else{
				$dataInicialLibValBloqueio = 'null';
			}
			if (!empty($dataFinalLibValBloqueio)) {
				$dataFinalLibValBloqueio = new DataHora($dataFinalLibValBloqueio);
				$dataFinalLibValBloqueio = $dataFinalLibValBloqueio->formata('Y-m-d') . ' 23:59:59';
				$dataFinalLibValBloqueio = "'".$dataFinalLibValBloqueio."'";
			}else{
				$dataFinalLibValBloqueio = 'null';
			}
			$Sequencial = $result->fetchRow();
			
	  		if ($Sequencial[0] == 0 || $Sequencial[0] == "" || $Sequencial[0] == null) { 
				$Sequencial = 1;
				
				if (empty($Orgao)) {
					$OrgaoAux = "null";
				}
				
		 		$sql  = " INSERT INTO SFPC.TBPARAMETROSGERAIS (";
		 		$sql .= " CPARGETBID , EPARGETDOV , QPARGEQMAC , QPARGETMAD , QPARGETMAOBJETO, QPARGETMAJUSTIFICATIVA , ";
		 		$sql .= " QPARGEDESCSE , QPARGEVCD , QPARGEVLI , QPARGEQVHL , QPARGEQDVI , EPARGEEFSC ,  ";
		 		$sql .= " EPARGESUBELEMESPEC , CUSUPOCODI , TPARGEULAT , ";
		 		$sql .= " QPARGEVILI , EPARGESUBM , EPARGESUBO , VPARGEPERL, CORGLICODI, ";
		 		$sql .= " epargeempr, epargeorg1, epargeorg2, epargesetr, epargesist, ";
		 		$sql .= " epargedati, epargedatf, qpargecaro, qpargepacc, qpargepagc, qpargecar1, tpargeilib, tpargeflib, epargeeval ";
		 		$sql .= ") VALUES (";
		 		$sql .= " $Sequencial , '$ExtensaoArquivosValidos' , $TamanhoMaxNomeDocumento , $TamanhoMaxDocumento , $TamanhoMaxObjeto , ";
		 		$sql .= " $TamanhoMaxJustificativa , $TamanhoMaxDescricaoServico , $PrazoMaxValidadePrecoCompraDireta , ";
		 		$sql .= " $PrazoMaxValidadePrecoLicitacao , $QuantidadeMediaLicitacao , $QuantidadeDiasEncerramentoVigenciaDispensa , "; 
	     		$sql .= " '$Email' , '$SubElementos' , ".$_SESSION['_cusupocodi_']." , '$Data' , ";
		 		$sql .= " $NumDiasVigenciaLicitacao , '$SubElementosSubEmpenharMenor' , '$SubElementoObras' , $PercentualLimitePrecos, $OrgaoAux, ";
		 		$sql .= " '$NomeOrgao1' , '$NomeOrgao2', '$NomeSetor1', '$NomeSistema' )";
		 		$sql .= " '$dataInicialBloqueio', '$dataFinalBloqueio', $FatorCarona, $PercentualGeralAdesao, $PercentualAdesao, $FatorCompraMaxCarona, $dataInicialLibValBloqueio, $dataFinalLibValBloqueio, '$emailGestoresARP' )";
	  		} else { 
		 		$sql  = " UPDATE SFPC.TBPARAMETROSGERAIS ";
		 		$sql .= " SET EPARGETDOV = '$ExtensaoArquivosValidos' , QPARGEQMAC = $TamanhoMaxNomeDocumento , ";
		 		$sql .= " QPARGETMAD = $TamanhoMaxDocumento , QPARGETMAOBJETO = $TamanhoMaxObjeto , QPARGETMAJUSTIFICATIVA = $TamanhoMaxJustificativa , ";
		 		$sql .= " QPARGEDESCSE = $TamanhoMaxDescricaoServico , QPARGEVCD = $PrazoMaxValidadePrecoCompraDireta , QPARGEVLI = $PrazoMaxValidadePrecoLicitacao , ";
		 		$sql .= " QPARGEQVHL = $QuantidadeMediaLicitacao , QPARGEQDVI = $QuantidadeDiasEncerramentoVigenciaDispensa ,EPARGEEFSC = '$Email' , "; 
		 		$sql .= " EPARGESUBELEMESPEC = '$SubElementos' , CUSUPOCODI = ".$_SESSION['_cusupocodi_']." , TPARGEULAT = '$Data' , ";
		 		$sql .= " QPARGEVILI = $NumDiasVigenciaLicitacao , EPARGESUBM = '$SubElementosSubEmpenharMenor' , EPARGESUBO = '$SubElementoObras' , VPARGEPERL = $PercentualLimitePrecos , EPARGEOPRP = '$OrgaosPiloto', QPARGECARO = $FatorCarona, QPARGEPACC = $PercentualGeralAdesao, QPARGEPAGC = $PercentualAdesao, QPARGECAR1 = $FatorCompraMaxCarona, ";
		 			if (empty($Orgao)) {
						$sql .= "CORGLICODI  = null , ";
					} else {
						$sql .= "CORGLICODI  = $Orgao , ";
					}
		 		$sql .= " epargeempr = '$NomeEmpresa', epargeorg1 = '$NomeOrgao1', epargeorg2 = '$NomeOrgao2', epargesetr = '$NomeSetor1', epargesist = '$NomeSistema', ";
		 		$sql .= " epargedati = '$dataInicialBloqueio', epargedatf = '$dataFinalBloqueio', tpargeilib = $dataInicialLibValBloqueio, tpargeflib = $dataFinalLibValBloqueio, epargeeval = '$emailGestoresARP' ";
		 		$sql .= " WHERE CPARGETBID = 1 ";
			}

		 	$result  = $db->query($sql);
			 
			if (PEAR::isError($result)) {
			 	$db->query("ROLLBACK");
			 	$db->query("END TRANSACTION");
			 	$db->disconnect();
			 	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		 	} else {
		     	$db->query("COMMIT");
			 	$db->query("END TRANSACTION");
			 	$db->disconnect();			
				# Envia mensagem para página selecionar #
			 	$Mensagem = urlencode("Parâmetros Alterados com Sucesso");
			 	$Url = "TabParametrosGeraisManter.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
				
			 	if (!in_array($Url,$_SESSION['GetUrl'])) {
					 $_SESSION['GetUrl'][] = $Url;
				}				
			 	header("location: ".$Url);
			 	exit();
		 	}
	 	}		
	}			
} elseif ($Botao == "") {
	$sql = "SELECT 	EPARGETDOV, QPARGEQMAC, QPARGETMAD, QPARGETMAOBJETO, QPARGETMAJUSTIFICATIVA, QPARGEDESCSE, QPARGEVCD,
			        QPARGEVLI, QPARGEQVHL, QPARGEQDVI, EPARGEEFSC, EPARGESUBELEMESPEC, QPARGEVILI, EPARGESUBM, EPARGESUBO, VPARGEPERL,
			        CORGLICODI, EPARGEEMPR, EPARGEORG1, EPARGEORG2, EPARGESETR, EPARGESIST, EPARGEDATI, EPARGEDATF, EPARGEOPRP, QPARGECARO,
			        QPARGEPACC, QPARGEPAGC, QPARGECAR1, TPARGEILIB, TPARGEFLIB, EPARGEEVAL
	     	FROM	SFPC.TBPARAMETROSGERAIS 
			WHERE	CPARGETBID = (SELECT MAX(CPARGETBID) FROM SFPC.TBPARAMETROSGERAIS) ";
			
	$result = $db->query($sql);
	
	if (PEAR::isError($result)) {
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	} else {
		if ($Linha = $result->fetchRow()) {
			$ExtensaoArquivosValidos					= $Linha[0];
			$TamanhoMaxNomeDocumento					= $Linha[1];
			$TamanhoMaxDocumento						= $Linha[2];
			$TamanhoMaxObjeto							= $Linha[3];
			$TamanhoMaxJustificativa					= $Linha[4];
			$TamanhoMaxDescricaoServico					= $Linha[5];
			$PrazoMaxValidadePrecoCompraDireta			= $Linha[6];
			$PrazoMaxValidadePrecoLicitacao				= $Linha[7];
			$QuantidadeMediaLicitacao					= $Linha[8];
			$QuantidadeDiasEncerramentoVigenciaDispensa = $Linha[9];
			$Email										= $Linha[10];
			$SubElementos								= $Linha[11];
			$NumDiasVigenciaLicitacao					= $Linha[12];
			$SubElementosSubEmpenharMenor				= $Linha[13];
		    $SubElementoObras							= $Linha[14];
		    $PercentualLimitePrecos						= $Linha[15];
		    $Orgao               						= $Linha[16];
		    $NomeEmpresa								= $Linha[17];
		    $NomeOrgao1									= $Linha[18];
		    $NomeOrgao2									= $Linha[19];
		    $NomeSetor1									= $Linha[20];
		    $NomeSistema								= $Linha[21];
		    $dataInicialBloqueioSalva					= $Linha[22];
			$dataFinalBloqueioSalva						= $Linha[23];
			$OrgaosPiloto								= $Linha[24];
			$FatorCarona								= $Linha[25];
            $PercentualGeralAdesao      				= $Linha[26];
            $PercentualAdesao							= $Linha[27];
			$FatorCompraMaxCarona						= $Linha[28];
			$dataInicialLiValBloqSalva					= $Linha[29];
			$dataFinalLiValBloqSalva					= $Linha[30];
			$emailGestoresARP							= $Linha[31];
		}
	}
}

// Listar Orgãos
$sql = "SELECT CORGLICODI AS CODIGO, EORGLIDESC AS DESCRICAO FROM SFPC.TBORGAOLICITANTE ORDER BY 2 ";

$result	= executarTransacao($db, $sql);
$indice = -1;

while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
  	$indice++;
  	$codOrgao[$indice] = $row->codigo;
  	$descOrgao[$indice] = $row->descricao;		
}


// Capturar a descrição
if (!empty($Orgao)) {
	$sql = "SELECT EORGLIDESC AS DESCRICAO FROM SFPC.TBORGAOLICITANTE WHERE CORGLICODI = ".$Orgao;
	
	$result	= executarTransacao($db, $sql);
	
	$row = $result->fetchRow(DB_FETCHMODE_OBJECT);
	$descOrgaoAux = $row->descricao;
}
$db->disconnect();
?>
<html>
	<?php
	# Carrega o layout padrão #
	layout();
	?>
	<script language="javascript" type="">
		<!--
			function enviar(valor){
				document.TabParametrosGeraisManter.Botao.value=valor;
				document.TabParametrosGeraisManter.submit();
			}
			<?php MenuAcesso(); ?>
		//-->
	</script>
	<link rel="stylesheet" type="text/css" href="../estilo.css">
	<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form action="TabParametrosGeraisManter.php" method="post" name="TabParametrosGeraisManter">
		<br><br><br><br><br>
		<table cellpadding="3" border="0" summary="">
			<!-- Caminho -->
			<tr>
				<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
				<td align="left" class="textonormal" colspan="8">
					<font class="titulo2">|</font>
					<a href="../index.php">
						<font color="#000000">Página Principal</font>
					</a>
					> Tabelas > Tipo de Parametros Gerais > Manter
				</td>
			</tr>
			<!-- Fim do Caminho -->
			<!-- Erro -->
			<?php if ($Mens == 1) { ?>
				<tr>
					<td width="150"></td>
					<td align="left" colspan="8"><?php ExibeMens ($Mensagem, $Tipo, 1); ?></td>
				</tr>
			<?php } ?>
			<!-- Fim do Erro -->
			<!-- Corpo -->
			<tr>
				<td width="100"></td>
				<td class="textonormal">
					<table border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff">
						<tr>
							<td class="textonormal">
								<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" class="textonormal" summary="">
									<tr>
										<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
											TIPOS DE PARAMETROS GERAIS
										</td>
									</tr>
									<tr>
										<td class="textonormal">
											<p align="justify">
												Para Alterar ou Inserir algum Tipo de Parametro Geral Digite todos os campos!
											</p>
										</td>
									</tr>
									<tr>
										<td>
											<table class="textonormal" border="0" align="left"  summary="">
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Empresa*</td>
													<td class="textonormal">
	          	    			               			<input title="Digite o nome da empresa" type="text" name="NomeEmpresa" value="<?php echo $NomeEmpresa; ?>" size="45" maxlength="60" class="textonormal">
	          	    		                		</td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Órgão 1*</td>
													<td class="textonormal">
	          	    			               			<input title="Digite o nome do Órgão 1" type="text" name="NomeOrgao1" value="<?php echo $NomeOrgao1; ?>" size="45" maxlength="60" class="textonormal">
	          	    		                		</td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Órgão 2*</td>
													<td class="textonormal">
	          	    			               			<input title="Digite o nome do Órgão 2" type="text" name="NomeOrgao2" value="<?php echo $NomeOrgao2; ?>" size="45" maxlength="60" class="textonormal">
	          	    		                		</td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Setor 1*</td>
													<td class="textonormal">
	          	    			               			<input title="Digite o nome do Setor 1" type="text" name="NomeSetor1" value="<?php echo $NomeSetor1; ?>" size="45" maxlength="60" class="textonormal">
	          	    		                		</td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Nome sistema*</td>
													<td class="textonormal">
	          	    			               			<input title="Digite o nome do Sistema" type="text" name="NomeSistema" value="<?php echo $NomeSistema; ?>" size="45" maxlength="60" class="textonormal">
	          	    		                		</td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Tipos de arquivos válidos*</td>
													<td class="textonormal">
	          	    			               			<input title="Colocar arquivos com ponto separados de vírgula. Exemplo: .txt, .pdf, .doc" type="text" name="ExtensaoArquivosValidos" value="<?php echo $ExtensaoArquivosValidos; ?>" size="45" maxlength="60" class="textonormal">
	          	    		                		</td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Tamanho máximo do nome do documento*</td>
										    		<td class="textonormal">
	          	    			               			<input title="Preenchido com até 4 Caracteres Numéricos. Exemplo: 1111" type="text" name="TamanhoMaxNomeDocumento" value="<?php echo $TamanhoMaxNomeDocumento; ?>" size="45" maxlength="60" class="textonormal">
	          	    		                		</td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Tamanho máximo do documento*</td>
                                            		<td class="textonormal">
	          	    			              			<input title="Preenchido com até 4 Caracteres Numéricos. Exemplo: 2222" type="text" name="TamanhoMaxDocumento" value="<?php echo $TamanhoMaxDocumento; ?>" size="45" maxlength="60" class="textonormal">
	          	    		                		</td>
												</tr>
									    		<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Tamanho máximo do objeto*</td>
								            		<td class="textonormal">
	          	    			              			<input title="Preenchido com um número de 4 até 1000. Exemplo: 123" type="text" name="TamanhoMaxObjeto" value="<?php echo $TamanhoMaxObjeto; ?>" size="45" maxlength="1000" class="textonormal">
	          	    		                		</td>
									    		</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Tamanho máximo da justificativa*</td>
								       	    		<td class="textonormal">
	          	    			               			<input title="Preenchido com um número de 4 até 1000. Exemplo: 456" type="text" name="TamanhoMaxJustificativa" value="<?php echo $TamanhoMaxJustificativa; ?>" size="45" maxlength="60" class="textonormal">
	          	    		                		</td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Tamanho máximo da descrição detalhada do Serviço*</td>
													<td class="textonormal">
	          	    			               			<input title="Preenchido com um número de 4 até 1000. Exemplo: 789" type="text" name="TamanhoMaxDescricaoServico" value="<?php echo $TamanhoMaxDescricaoServico; ?>" size="45" maxlength="60" class="textonormal">
	          	    		                		</td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Prazo máximo de validade do preço da compra direta*</td>
										    		<td class="textonormal">
	          	    			                		<input title="Preenchido com um número de 4 até 1000. Exemplo: 256" type="text" name="PrazoMaxValidadePrecoCompraDireta" value="<?php echo $PrazoMaxValidadePrecoCompraDireta; ?>" size="45" maxlength="100" class="textonormal">
	          	    		                		</td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Prazo máximo de validade do preço da licitação*</td>
													<td class="textonormal">
	          	    			               			<input title="Preenchido com até 4 Caracteres Numéricos. Exemplo: 3333" type="text" name="PrazoMaxValidadePrecoLicitacao" value="<?php echo $PrazoMaxValidadePrecoLicitacao; ?>" size="45" maxlength="60" class="textonormal">
	          	    		                		</td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Quantidade média de valores homologados para licitação*</td>
										    		<td class="textonormal">
	          	    			               			<input title="Preenchido com até 2 Caracteres Numéricos. Exemplo: 99" type="text" name="QuantidadeMediaLicitacao" value="<?php echo $QuantidadeMediaLicitacao; ?>" size="45" maxlength="60" class="textonormal">
	          	    		                		</td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Quantidade de dias para encerramento da data de vigência da dispensa e inexigibilidade*</td>
                                            		<td class="textonormal">
	          	    			               			<input title="Preenchido com até 4 Caracteres Numéricos. Exemplo: 4444" type="text" name="QuantidadeDiasEncerramentoVigenciaDispensa" value="<?php echo $QuantidadeDiasEncerramentoVigenciaDispensa; ?>" size="45" maxlength="60" class="textonormal">
	          	    		                		</td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Email para aviso de fracionamento da solicitação de compra*</td>
													<td class="textonormal">
	          	    			               			<input title="Preenchido com email válido. Exemplo: nome@recife.pe.gov.br" type="text" name="Email" value="<?php echo $Email; ?>" size="45" maxlength="60" class="textonormal" style="text-transform: none !important;">
	          	    		                		</td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Sub-elementos de despesas especiais para a SCC*</td>										
													<td class="textonormal">
	          	    			               			<input title="Digite 01 ou mais SubElementos com Caracteres Númericos. Exemplo: 5.6.89.65.65" type="text" name="SubElementos" value="<?php echo $SubElementos ; ?>" size="45" maxlength="460" class="textonormal">											   
	          	    		                		</td>
												</tr>
												<input type="hidden" name="critica" value="1">
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Quantidade de dias de vigência da licitação*</td>
                                            		<td class="textonormal">
	          	    			              			<input title="Preenchido com até 3 Caracteres Numéricos. Exemplo: 999" type="text" name=NumDiasVigenciaLicitacao value="<?php echo $NumDiasVigenciaLicitacao; ?>" size="45" maxlength="60" class="textonormal">
	          	    		                		</td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Sub-elementos que permitem sub-empenhar a Menor*</td>
                                            		<td class="textonormal">
	          	    			              			<input title="Digite 01 ou mais SubElementos com Caracteres Númericos. Exemplo: 5.6.89.65.65" type="text" name="SubElementosSubEmpenharMenor" value="<?php echo $SubElementosSubEmpenharMenor; ?>" size="45" maxlength="60" class="textonormal">
	          	    		                		</td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Sub-elementos de obras*</td>
                                            		<td class="textonormal">
	          	    			              			<input title="Digite 01 ou mais SubElementos com Caracteres Númericos. Exemplo: 5.6.89.65.65" type="text" name="SubElementoObras" value="<?php echo $SubElementoObras; ?>" size="45" maxlength="60" class="textonormal">
	          	    		                		</td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Percentual limite de preços TRP em relação a média*    <?php echo number_format($PercentualLimitePrecos,0); ?> </td>
                                            		<td class="textonormal">
                                                		<?php $PercentualLimitePrecos = number_format($PercentualLimitePrecos,0); ?>
	          	    			                		<input title="Preenchido com até 5 Caracteres Númericos. Exemplo:12345   " type="text" name="PercentualLimitePrecos" value="<?php echo $PercentualLimitePrecos ; ?>" size="45" maxlength="60" class="textonormal">
	          	    			            		</td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Fator multiplicativo da quantidade/valor máximo para caronas a partir do original da ata de RP</td>
                                            		<td class="textonormal">
	          	    			                		<input title="Informe o fator multiplicativo para caronas" type="text" name="FatorCarona" value="<?php echo $FatorCarona ; ?>" size="45"class="textonormal">
	          	    			            		</td>
									    		</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Órgão(s) piloto para utilizar SARP - Registro de Preços</td>
                                            		<td class="textonormal">
	          	    			                		<input title="Informe os Orgãos Separados Por Vírgula " type="text" name="OrgaosPiloto" value="<?php echo $OrgaosPiloto ; ?>" size="45"class="textonormal">
	          	    			            		</td>
									    		</tr>
                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7" height="20">Percentual geral de adesão</td>
                                                    <td class="textonormal">
                                                        <input title="Informe os Orgãos Separados Por Vírgula " type="text" name="PercentualGeralAdesao" value="<?php echo $PercentualGeralAdesao ; ?>" size="45"class="textonormal">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7" height="20">Percentual de adesão</td>
                                                    <td class="textonormal">
                                                        <input title="Informe os Orgãos Separados Por Vírgula " type="text" name="PercentualAdesao" value="<?php echo $PercentualAdesao ; ?>" size="45"class="textonormal">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7" height="20">Fator multiplicativo em compras corporativas da quantidade máxima carona</td>
                                                    <td class="textonormal">
                                                        <input title="Informe os Orgãos Separados Por Vírgula " type="text" name="FatorCompraMaxCarona" value="<?php echo $FatorCompraMaxCarona ; ?>" size="45"class="textonormal">
                                                    </td>
                                                </tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Órgão gestor padrão de agrupamento de SCC</td>
                                            		<td class="textonormal">
		  	              								<select name="Orgao" class="textonormal">
		  	              						    		<option value="">Nenhum Orgão</option>
		  	              						    		<?php for ($i = 0; $i < count($codOrgao); $i++) {   ?>
		  	              						        		<?php if ($descOrgaoAux==$descOrgao[$i]) { ?>
		  	              							        		<option value="<?php echo $codOrgao[$i];  ?>" selected><?php echo $descOrgao[$i];  ?></option>
		  	              						        		<?php } else { ?>
		  	              							        		<option value="<?php echo $codOrgao[$i];  ?>"><?php echo $descOrgao[$i];  ?></option>
		  	              						        		<?php } ?>
		  	              						    		<?php  } ?>   
		        	        							</select>
	          	    			            		</td>
									    		</tr>									    
									    		<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">
														Período bloqueio virada ano
													</td>	          	    			            
	          	    			            		<td class="textonormal">
														<?php
														$DataIni = $_POST['DataIni'] ? $_POST['DataIni'] : "";
																	
														if (!empty($dataInicialBloqueioSalva)) {
															$DataIni = new DataHora($dataInicialBloqueioSalva);
															$DataIni = $DataIni->formata("d/m/Y");
														}													
														$DataFim = $_POST['DataFim'] ? $_POST['DataFim'] : "";

														if (!empty($dataFinalBloqueioSalva)) {
															$DataFim = new DataHora($dataFinalBloqueioSalva);
															$DataFim = $DataFim->formata("d/m/Y");
														}																									
														$URLIni = "../calendario.php?Formulario=TabParametrosGeraisManter&Campo=DataIni";
														$URLFim = "../calendario.php?Formulario=TabParametrosGeraisManter&Campo=DataFim";
														?>
														
														<input type="text" name="DataIni" size="10" maxlength="10" value="<?php echo $DataIni;?>" class="textonormal data">
														<a href="javascript:janela('<?php echo $URLIni ?>','Calendario',220,245,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
														&nbsp;a&nbsp;
														<input type="text" name="DataFim" size="10" maxlength="10" value="<?php echo $DataFim;?>" class="textonormal data">
														<a href="javascript:janela('<?php echo $URLFim ?>','Calendario',220,245,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
													</td>
									    		</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">
													Liberação de Validação de Bloqueio Orçamentário
													</td>	          	    			            
	          	    			            		<td class="textonormal">
														<?php
														$DataIniLibValBloq = $_POST['DataIniLibValBloq'] ? $_POST['DataIniLibValBloq'] : "";
																	
														if (!empty($dataInicialLiValBloqSalva)) {
															$DataIniLibValBloq = new DataHora($dataInicialLiValBloqSalva);
															$DataIniLibValBloq = $DataIniLibValBloq->formata("d/m/Y");
														}													
														
														$DataFimLibValBloq = $_POST['DataFimLibValBloq'] ? $_POST['DataFimLibValBloq'] : "";

														if (!empty($dataFinalLiValBloqSalva)) {
															$DataFimLibValBloq = new DataHora($dataFinalLiValBloqSalva);
															$DataFimLibValBloq = $DataFimLibValBloq->formata("d/m/Y");
														}																									
														$URLIniLV = "../calendario.php?Formulario=TabParametrosGeraisManter&Campo=DataIniLibValBloq";
														$URLFimLV = "../calendario.php?Formulario=TabParametrosGeraisManter&Campo=DataFimLibValBloq";
														?>
														<!-- Data inicial: -->
														<input type="text" name="DataIniLibValBloq" size="10" maxlength="10" value="<?php echo $DataIniLibValBloq;?>" class="textonormal data">
														<a href="javascript:janela('<?php echo $URLIniLV ?>','Calendario',220,245,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
														<!-- &nbsp;Data Final: -->
														&nbsp;a&nbsp;
														<input type="text" name="DataFimLibValBloq" size="10" maxlength="10" value="<?php echo $DataFimLibValBloq;?>" class="textonormal data">
														<a href="javascript:janela('<?php echo $URLFimLV ?>','Calendario',220,245,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
													</td>
									    		</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Email para aviso de vencimento de Ata de Registro de Preços</td>
													<td class="textonormal">
	          	    			               			<input title="Colocar arquivos com ponto separados de vírgula. Exemplo: teste@recife.pe.gov.br, teste@gmail.com" type="text" name="emailGestoresARP" value="<?php echo $emailGestoresARP; ?>" size="45"  class="textonormal" style="text-transform: none !important;">
	          	    		                		</td>
												</tr>		
											</table>
										</td>
									</tr>
									<tr>
										<td class="textonormal" align="right" >
											<input type="button" value="Alterar" class="botao" onclick="javascript:enviar('Alterar');">
											<input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
											<input type="hidden" name="Botao" value="">
											<input type="hidden" name="critica" value="1">	 
										</td>
									</tr>	
								</table>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<!-- Fim do Corpo -->
		</table>
	</form>
	</body>
</html>