<?php
/**
 * Portal de Compras
 * 
 * Programa: CadNotaFiscalMaterialIncluir.php
 * Objetivo: Programa de inclusão de material via Nota Fiscal
 * Data:     17/08/2005
 * Autor:    Altamiro Pedrosa
 * ------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Álvaro Faria
 * Data:     08/11/2005
 * Objetivo: Validação de empenho
 * ------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Marcus Thiago
 * Data:     04/01/2006
 * ------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Álvaro Faria
 * Data:     26/05/2006
 * ------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Álvaro Faria
 * Data:     08/06/2006
 * Objetivo: Data de entrada pelo sistema, não mais pelo usuário
 * ------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Álvaro Faria
 * Data:     19/06/2006
 * Objetivo: Checagem de quantidade e valor válido no totalizar
 * ------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Álvaro Faria
 * Data:     27/07/2006
 * Objetivo: Permitir mais de um empenho
 * ------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Álvaro Faria
 * Data:     24/11/2006
 * Objetivo: Identação
 * ------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Álvaro Faria
 * Data:     14/12/2006
 * Objetivo: strtoupper no número de série da nota
 * ------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Carlos Abreu
 * Data:     15/12/2006
 * Objetivo: Filtro no carregamento dos almoxarifados para bloquear quando sob inventário
 * ------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Carlos Abreu
 * Data:     27/12/2006
 * Objetivo: Filtro no carregamento dos almoxarifados para liberar almox educação quando sob inventário
 * ------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Carlos Abreu
 * Data:     22/03/2007
 * Objetivo: Ajuste para restringir o ano de emissão entre o ano atual e o ano anterior
 * ------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Carlos Abreu
 * Data:     30/05/2007
 * Objetivo: Correção para aparecer o nome "informe:" nas mensagens
 * ------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Álvaro Faria
 * Data:     20/12/2007
 * Objetivo: Correção do select de almoxarifado para bloquear almoxarifados em inventário ou no período de inventário
 * ------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Rodrigo Melo
 * Data:     09/01/2008
 * Objetivo: Correção do select de almoxarifado, pois o mesmo não está liberando os almoxarifados a realizarem as movimentações após a realização do inventário
 * ------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Rodrigo Melo
 * Data:     27/02/2008
 * Objetivo: Alteração para que na entrada de nota fiscal seja possível a entrada de materiais inavitos que foram cadastrados na licitação
 * 			 do SOFIN antes da inativação do material no Portal de Compras
 * ------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Rodrigo Melo
 * Data:     28/03/2008
 * Objetivo: Alteração do colspan de 5 para 6, para melhorar a exibição dos botões: Incluir Item, Retirar Item e Totalizar
 * ------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Rodrigo Melo
 * Data:     07/04/2008
 * Objetivo: Correção para não permitir que materiais inativos sejam inseridos na base de dados e do foco quando o usuário receber críticas
 *           de quantidade e valor unitário inválidos ou em branco
 * ------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Rodrigo Melo
 * Data:     11/06/2008
 * Objetivo: Alteração para informar o valor total da nota fiscal, para comparar com o valor total dos materiais
 * ------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Rodrigo Melo
 * Data:     07/07/2008
 * Objetivo: Alteração para inserir no campo estoque virtual na tabela de armazenamento de material e flag para identificar uma nota fiscal virtual
 * ------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Rodrigo Melo
 * Data:	 10/07/2008
 * Objetivo: Alteração para obter empenhos válidos, ou seja, não nulos e que não sejam subempenhos. Além de obter o valor anulado do empenho
 * ------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Rodrigo Melo
 * Data:     28/07/2008
 * Objetivo: Correção para que o valor da nota sejam igual ou maior do que o valor do empenho e crítica para valor unitário
 * ------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Rodrigo Melo
 * Data:     01/08/2008
 * Objetivo: Correção para calcular o valor dos empenhos apenas de notas fiscais não canceladas
 * ------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Rodrigo Melo
 * Data:     12/08/2008
 * Objetivo: Alteração para que possa entrar com valores de notas fiscais com uma diferença de até R$ 2,00 do valor do empenho/subempenho
 * ------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     13/08/2008
 * Objetivo: Arredondamento de total de valor dos itens da nota fiscal para 4 dígitos fracionários para comparação com o total de nota fiscal informado pelo usuário
 * ------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     10/09/2008
 * Objetivo: Removendo acesso a sfpc.tbfornecedorestoque
 * ------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Rodrigo Melo
 * Data:     26/11/2008
 * Objetivo: Corrigindo programa para permitir entrada de empenhos com sequenciais diferentes, ou seja, diferentes subempenhos
 * ------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     19/11/2008
 * Objetivo: Ao selecionar nota fiscal, agora esta ferramenta permite criar a requisição vinculada à nota
 * ------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Rodrigo Melo
 * Data:     26/11/2008
 * Objetivo: Corrigindo programa para permitir entrada de empenhos com sequenciais diferentes, ou seja, diferentes subempenhos
 * ------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     11/12/2008
 * Objetivo: Setar data default da data atual
 * ------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     14/08/2009
 * Objetivo: CR 2699 - Correção da movimentaçaõ 4 (saída por requisição) valor médio estava igual ao valor unitário
 * ------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     27/08/2009
 * Objetivo: CR 2699 - Correção para notas fiscais virtuaus quando o material não existe no almoxarifado
 * 			 CR 2699 - Correção - Nota fiscal virtual deve recalcular o valor médio
 * 			 CR 2699 - Correção - Saída por requisição deve pegar o valor médio no estoque do almoxarifado (armazenamento material)
 * ------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     28/08/2009
 * Objetivo: Remoção de função de comparar datas que não estão sendo usadas em nenhum lugar
 * ------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - José Almir
 * Data:     19/11/2014
 * Objetivo: CR 213 - Alterar as funcionalidades 'Incluir / Manter Nofa Fiscal' e 'Inclui / Manter / Atender Requisição' para liberar
 * 					  movimentações dos usuários dos órgãos nos períodos cadastrados na nova funcionalidade de liberação de movimentação
 * ------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     02/08/2018
 * Objetivo: Tarefa Redmine 130310
 * ------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     02/08/2018
 * Objetivo: Tarefa Redmine 200989
 * ------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     04/10/2018
 * Objetivo: Tarefa Redmine 204895
 * ------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     13/12/2018
 * Objetivo: Tarefa Redmine 207888
 * ------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     01/08/2019
 * Objetivo: Tarefa Redmine 220348
 * ------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:	 02/08/2019
 * Objetivo: Retorno ao estado anterior (Data: 13/12/2018)
 * ------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:	 02/08/2019
 * Objetivo: Tarefa Redmine 220348
 * ------------------------------------------------------------------------------------------------------------------------------------------
 */

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso ('/estoques/CadIncluirItem.php');
AddMenuAcesso ('/estoques/CadItemDetalhe.php');
AddMenuAcesso ('/estoques/CadIncluirEmpenho.php');
AddMenuAcesso ('/estoques/CadIncluirCentroCusto.php');

$ProgramaOrigem = "CadNotaFiscalMaterialIncluir";

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$Botao               = $_POST['Botao'];
	$InicioPrograma      = $_POST['InicioPrograma'];
	$TipoUsuario         = $_POST['TipoUsuario'];
	$CentroCusto         = $_POST['CentroCusto'];
	$OrgaoUsuario        = $_POST['OrgaoUsuario'];
	$Almoxarifado        = $_POST['Almoxarifado'];
	$CarregaAlmoxarifado = $_POST['CarregaAlmoxarifado'];
	$Localizacao         = $_POST['Localizacao'];
	$CarregaLocalizacao  = $_POST['CarregaLocalizacao'];
	$NumeroNota          = $_POST['NumeroNota'];
	$SerieNota           = strtoupper2(RetiraAcentos($_POST['SerieNota']));
	$DataEmissao         = $_POST['DataEmissao'];
	$AnoRequisicao       = date("Y");
	$DataRequisicao      = $_POST['DataRequisicao'];
	$Observacao    		 = $_POST['Observacao'];
		
	if ($DataEmissao != "") {
		$DataEmissao = FormataData($DataEmissao);
	}
	
	$CNPJ_CPF            = $_POST['CNPJ_CPF'];

	if ($_POST['CnpjCpf'] != "") {
		if ($CNPJ_CPF == 2) {
			$CnpjCpf  = substr("00000000000".$_POST['CnpjCpf'],-11);    // CPF
		} else {
			$CnpjCpf  = substr("00000000000000".$_POST['CnpjCpf'],-14); // CNPJ
		}
	} else {
		$CnpjCpf = $_POST['CnpjCpf'];
	}
	
	$RazaoSocial         = $_POST['RazaoSocial'];
	$DataEntrada         = $_POST['DataEntrada'];
	$Empenhos            = $_POST['Empenhos'];
	$CheckEmp            = $_POST['CheckEmp'];
	$ValorNota           = $_POST['ValorNota'];
    $EstoqueVirtual      = $_POST['EstoqueVirtual'];
    $ValorTotalNota      = $_POST['ValorTotalNota'];

	# Dados do detalhe da nota fiscal #
	$CheckItem           = $_POST['CheckItem'];
	$Material            = $_POST['Material'];
	$DescMaterial        = $_POST['DescMaterial'];
	$Unidade             = $_POST['Unidade'];
    $SituacaoMaterial    = $_POST['SituacaoMaterial'];
	$Quantidade          = $_POST['Quantidade'];
	$ValorUnitario       = $_POST['ValorUnitario'];
	$ValorTotal          = $_POST['ValorTotal'];
		
	for ($i=0; $i<count($DescMaterial); $i++) {
		$ItemNotaFiscal[$i] = $DescMaterial[$i].$SimboloConcatenacaoArray.$Material[$i].$SimboloConcatenacaoArray.$Unidade[$i].$SimboloConcatenacaoArray.$SituacaoMaterial[$i].$SimboloConcatenacaoArray.str_replace(".","",$Quantidade[$i]).$SimboloConcatenacaoArray.str_replace(".","",$ValorUnitario[$i]);
	}
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Ano e Data do Exercicio #
$AnoExercicio = date("Y");
$DataAtual    = date("Y-m-d");

# Caso haja informações de empenho na variável de sessão, traz para a nota e apaga a variável #
if ($_SESSION['Empenho']) {
	if (!$Empenhos) {
		$Empenhos = array();
	}
	
	if (!in_array($_SESSION['Empenho'],$Empenhos)) {
		$Empenhos[] = $_SESSION['Empenho'];
	}
	unset($_SESSION['Empenho']);
}

$Mensagem = "Informe: ";

if ($Botao == "Verificar") {
	$Mens     = 0;
	
	if ($CNPJ_CPF == "") {
		if ($Mens == 1) {
			$Mensagem.=", ";
		}
		
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "A opção CNPJ ou CPF";
	} else {
		if ($CNPJ_CPF == 1) {
			$TipoDocumento = "CNPJ";
		} else {
			$TipoDocumento = "CPF";
		}
		
		if ($CnpjCpf == "") {
			$RazaoSocial = null;
			
			if ($Mens == 1) {
				$Mensagem.=", ";
			}
			
			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialIncluir.CnpjCpf.focus();\" class=\"titulo2\">$TipoDocumento</a>";
		} else {
			if ($CNPJ_CPF == 1) {
				$valida_cnpj = valida_CNPJ($CnpjCpf);
				
				if ($valida_cnpj === false) {
					$RazaoSocial = null;
					
					if ($Mens == 1) {
						$Mensagem.=", ";
					}
					
					$Mens      = 1;
					$Tipo      = 2;
					$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialIncluir.CnpjCpf.focus();\" class=\"titulo2\">CNPJ Válido</a>";
				}
			} else {
				$valida_cpf = valida_CPF($CnpjCpf);
				
				if ($valida_cpf === false) {
					$RazaoSocial = null;
					
					$Mens      = 1;
					$Tipo      = 2;
					$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialIncluir.CnpjCpf.focus();\" class=\"titulo2\">CPF Válido</a>";
				}
			}
		}
		
		if (($CNPJ_CPF == 1 and $valida_cnpj === true) or ($CNPJ_CPF == 2 and $valida_cpf === true)) {
			# Verifica se o CNPJ Existe no cadastro de Fornecedores Credenciados #
			$db = Conexao();
			
			$sql  = "SELECT NFORCRRAZS FROM SFPC.TBFORNECEDORCREDENCIADO ";
			$sql .= " WHERE ";
			
				if ($CNPJ_CPF == 1) {
					$sql .= " AFORCRCCGC = '$CnpjCpf' ";
				} else {
					$sql .= " AFORCRCCPF = '$CnpjCpf' ";
				}
				
			$res = $db->query($sql);
				
			if (PEAR::isError($res)) {
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			} else {
				$rows = $res->numRows();
					
				if ($rows > 0) {
					$linha       = $res->fetchRow();
					$RazaoSocial = $linha[0];
				} else {
					if ($Mens == 1) {
						$Mensagem.=", ";
					}
						
					$Mens     = 1;
					$Tipo     = 1;
					$Mensagem = "Fornecedor Não Cadastrado";
				}
			}
		}
	}
}

if ($Botao == "Incluir") {
	# Faz as validações do formulário #
	$Mens = 0;

	if (($Almoxarifado == "") && ($CarregaAlmoxarifado == 'N')) {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "Almoxarifado";
	} elseif ($Almoxarifado == "") {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialIncluir.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
	}
	
	if (($Localizacao == "") && ($CarregaLocalizacao == 'N')) {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "Localização";
	} elseif ($Localizacao == "") {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialIncluir.Localizacao.focus();\" class=\"titulo2\">Localização</a>";
	}
	
	if ($NumeroNota == "") {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialIncluir.NumeroNota.focus();\" class=\"titulo2\">Número da Nota</a>";
	} else {
		if (!SoNumeros($NumeroNota)) {
			if ($Mens == 1) {
				$Mensagem .= ", ";
			}
			
			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialIncluir.NumeroNota.focus();\" class=\"titulo2\">Número da Nota Válido</a>";
		}
	}
	
	if ($SerieNota == "") {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialIncluir.SerieNota.focus();\" class=\"titulo2\">Série da Nota</a>";
	}
	
	if ($DataEmissao == "") {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialIncluir.DataEmissao.focus();\" class=\"titulo2\">Data de Emissão</a>";
	} else {
		$DataValida = ValidaData($DataEmissao) ;
		
		if ($DataValida != "") {
			if ($Mens == 1) {
				$Mensagem .= ", ";
			}
			
			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialIncluir.DataEmissao.focus();\" class=\"titulo2\">Data da Nota Válida</a>";
		} elseif (DataInvertida($DataEmissao) > $DataAtual) {
			if ($Mens == 1) {
				$Mensagem .= ", ";
			}
			
			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialIncluir.DataEmissao.focus();\" class=\"titulo2\">Data de Emissão menor ou igual a atual</a>";
		}
	}

   	$ValorTotalNotaCritica  = str_replace(",",".",str_replace(".","",$ValorTotalNota));
   
	if ($ValorTotalNota == "") {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialIncluir.ValorTotalNota.focus();\" class=\"titulo2\">Valor Total da Nota Fiscal</a>";
	
	} elseif ($ValorTotalNotaCritica != sprintf("%01.4f",str_replace(",",".",$ValorNota))) {
      	if ($Mens == 1) {
			$Mensagem .= ", ";
		}
			
		$Mens      = 1;
		$Tipo      = 2;
      	$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialIncluir.ValorTotalNota.focus();\" class=\"titulo2\">Valor Total da Nota Fiscal diferente do Valor Total dos Itens da Nota Fiscal</a>";
    }

	if ($CNPJ_CPF == "") {
		$RazaoSocial = null;
		
		if ($Mens == 1) {
			$Mensagem.=", ";
		}
		
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "A opção CNPJ ou CPF";
	} else {
		if ($CNPJ_CPF == 1) {
			$TipoDocumento = "CNPJ";
		} else {
			$TipoDocumento = "CPF";
		}
		
		if ($CnpjCpf == "") {
			$RazaoSocial = null;
			
			if ($Mens == 1) {
				$Mensagem.=", ";
			}
			
			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialIncluir.CnpjCpf.focus();\" class=\"titulo2\">$TipoDocumento</a>";
		} else {
			if ($CNPJ_CPF == 1) {
				$valida_cnpj = valida_CNPJ($CnpjCpf);
				
				if ($valida_cnpj === false) {
					$RazaoSocial = null;
					
					if ($Mens == 1) {
						$Mensagem.=", ";
					}
					
					$Mens      = 1;
					$Tipo      = 2;
					$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialIncluir.CnpjCpf.focus();\" class=\"titulo2\">CNPJ Válido</a>";
				}
			} else {
				$valida_cpf = valida_CPF($CnpjCpf);
				
				if ($valida_cpf === false) {
					$RazaoSocial = null;
										
					if ($Mens == 1) {
						$Mensagem.=", ";
					}
					
					$Mens      = 1;
					$Tipo      = 2;
					$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialIncluir.CnpjCpf.focus();\" class=\"titulo2\">CPF Válido</a>";
				}
			}
		}
		
		if (($CNPJ_CPF == 1 and $valida_cnpj === true) or ($CNPJ_CPF == 2 and $valida_cpf === true)) {
			# Verifica se o CNPJ Existe no cadastro de Fornecedores Credenciados #
			$db = Conexao();
			
			$sql  = "SELECT NFORCRRAZS FROM SFPC.TBFORNECEDORCREDENCIADO ";
			$sql .= " WHERE ";
				
				if ($CNPJ_CPF == 1) {
					$sql .= " AFORCRCCGC = '$CnpjCpf' ";
				} else {
					$sql .= " AFORCRCCPF = '$CnpjCpf' ";
				}
			
			$res  = $db->query($sql);
			
			if (PEAR::isError($res)) {
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			} else {
				$rows = $res->numRows();
				
				if ($rows > 0) {
					$linha = $res->fetchRow();
					$RazaoSocial = $linha[0];
				} else {
					if ($Mens == 1) {
						$Mensagem.=", ";
					}
					
					$Mens     = 1;
					$Tipo     = 1;
					$Mensagem = "Fornecedor Não Cadastrado";
				}
			}
		}
	}

	# Verifica se existe algum item da nota #
	if (count($Quantidade) == 0) {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "Pelo menos um Item";
	}

	# Valida quantidade #
	if (count($Quantidade) != 0) {
		# Verifica se existe alguma quantidade igual a branco ou zero #
		for ($i=0; $i<count($Quantidade); $i++) {
			$Quantidade[$i] = str_replace(".","",$Quantidade[$i]);
			
			if ((str_replace(",",".",$Quantidade[$i]) == 0 or $Quantidade[$i] == "") and $Existe == "") {
				$Existe  = "S";
				$Posicao = $i;
			}
		}
		
		for ($i=0; $i<count($Quantidade); $i++) {
			if ($Quantidade[$i] > 9000000) {
				$loc = $i;
				
				if ($Mens == 1) {
					$Mensagem .= ", ";
				}
				
				$loc = ($loc * 8) + 15;
				
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.getElementsByName('Quantidade[$i]').item(0).focus();\" class=\"titulo2\">Quantidade até 9.000.000,00</a>";
			}

			if ($ValorUnitario[$i] > 9000000) {
				$loc2 = $i;
				
				if ($Mens == 1) {
					$Mensagem .= ", ";
				}
				
				$loc2 = ($loc2 * 8) + 16;
				
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.getElementsByName('ValorUnitario[$i]').item(0).focus();\" class=\"titulo2\">Valor Unitário até 9.000.000,00</a>";
			}
		}

		if ($Existe == "S") {
			if ($Mens == 1) {
				$Mensagem .= ", ";
			}
			
			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "<a href=\"javascript:document.getElementsByName('Quantidade[$Posicao]').item(0).focus();\" class=\"titulo2\">Quantidade</a>";
		}

		# Verifica se as quantidades só são numeros e decimais #
		if ($Existe == "") {
			$Posicao = "";
			
			for ($k=0; $k<count($Quantidade); $k++) {
				if ((!SoNumVirg($Quantidade[$k])) and ($Existe == "")) {
					$Existe  = "S";
					$Posicao = $k;
				}
			}
			
			if ($Existe == "") {
				for($j=0; $j<count($Quantidade); $j++){
					if ((!Decimal($Quantidade[$j])) and $Existe == "") {
						$Existe  = "S";
						$Posicao = $j;
					}
				}
			}
			
			if ($Existe == "S") {
				if ($Mens == 1) {
					$Mensagem .= ", ";
				}
					
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.getElementsByName('Quantidade[$Posicao]').item(0).focus();\" class=\"titulo2\">Quantidade Válida</a>";
			}
		}
	}

	# Valida valor unitario #
	if (count($ValorUnitario) != 0) {
		$Existe = "";
		
		# Verifica se existe algum valor unitario igual a branco ou zero #
		for ($i=0; $i<count($ValorUnitario); $i++) {
			if ((str_replace(",",".",$ValorUnitario[$i]) == 0 or $ValorUnitario[$i] == "") and ! SoNumeros($ValorUnitario[$i]) and $Existe == "") {
				$Existe  = "S";
				$Posicao = $i;
			}
		}

		if ($Existe == "S") {
			if ($Mens == 1) {
				$Mensagem .= ", ";
			}
					
			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "<a href=\"javascript:document.getElementsByName('ValorUnitario[$Posicao]').item(0).focus();\" class=\"titulo2\">Valor Unitário</a>";
		}

		# Verifica se os valores só são numeros e decimais #
		if ($Existe == "") {
			$Posicao = "";
			
			for ($k=0; $k<count($ValorUnitario); $k++) {
				if ((!SoNumVirg($ValorUnitario[$k])) and ($Existe == "")) {
					$Existe  = "S";
					$Posicao = $k;
				}
			}
			
			if ($Existe == "") {
				for ($j=0; $j<count($ValorUnitario); $j++) {
					if ((!DecimalValor($ValorUnitario[$j])) and $Existe == "") {
						$Existe  = "S";
						$Posicao = $j;
					}
				}
			}
			
			if ($Existe == "S") {
				if ($Mens == 1) {
					$Mensagem .= ", ";
				}
				
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.getElementsByName('ValorUnitario[$Posicao]').item(0).focus();\" class=\"titulo2\">Valor Unitário Válido</a>";
			}
		}
	}

	# Checa se nenhum número de empenho foi digitado #
	if (!$Empenhos) {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "Pelo menos um Número de Empenho";
	}

	if ($EstoqueVirtual=='S') {
		# Faz todas as validações de requisição de nota fiscal virtual aqui #
		if ($CentroCusto == "") {
			if ($Mens == 1) {
				$Mensagem .= ", ";
			}
			
			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "Centro de Custo";
		}
		
		if ($DataRequisicao == "") {
			if ($Mens == 1) {
				$Mensagem .= ", ";
			}
			
			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialIncluir.DataRequisicao.focus();\" class=\"titulo2\">Data da Requisição</a>";
		} else {
			$DataValida = ValidaData($DataRequisicao);
			
			if ($DataValida != "" ) {
				if ($Mens == 1) {
					$Mensagem .= ", ";
				}
				
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialIncluir.DataRequisicao.focus();\" class=\"titulo2\">Data da Requisição Válida</a>";
			} else {
				if (DataInvertida($DataRequisicao) > $DataAtual) {
					if ($Mens == 1) {
						$Mensagem .= ", ";
					}
					
					$Mens      = 1;
					$Tipo      = 2;
					$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialIncluir.DataRequisicao.focus();\" class=\"titulo2\">Data da Requisição menor ou igual a atual</a>";
				}
			}
		}
		
		if ($NCaracteresO > "200") {
			if ($Mens == 1) {
				$Mensagem .= ", ";
			}
			
			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "Observação menor que 200 caracteres";
		}
	}

	if ($Mens == 0) {
		# Verifica se existe algum material com armazenamento zerado #
		$db = Conexao();
		
		for ($i=0; $i< count($ItemNotaFiscal); $i++) {
			$sql  = "SELECT COUNT(*) FROM SFPC.TBARMAZENAMENTOMATERIAL ";
			$sql .= "WHERE CMATEPSEQU = $Material[$i] AND CLOCMACODI = $Localizacao ";
			
			$res  = $db->query($sql);
			
			if (PEAR::isError($res)) {
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			} else {
				$Linha = $res->fetchRow();
				$QtdRec = $Linha[0];
				
				if ($QtdRec > 0) {
					$sql  = "SELECT VARMATUMED, AARMATQTDE FROM SFPC.TBARMAZENAMENTOMATERIAL ";
					$sql .= "WHERE CMATEPSEQU = $Material[$i] AND CLOCMACODI = $Localizacao ";
					
					$res  = $db->query($sql);
					
					if (PEAR::isError($res)) {
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
					} else {
						$Linha = $res->fetchRow();
						$ValArmazenado = $Linha[0];
						$QtdArmazenado = $Linha[1];
						
						if (($ValArmazenado == 0 or $ValArmazenado == "") and ($QtdArmazenado != 0 ) and ($Existe == "")) {
							$Existe  = "S";
							$Posicao = $i;
						}
					}
				} else {
					$Existe = "";
				}
			}
		}
		
		$db->disconnect();
		
		if ($Existe == "S") {
			$Mens      = 1;
			$Tipo      = 2;
			$Virgula   = 2;
			$Dados     = explode($SimboloConcatenacaoDesc,$DescMaterial[$Posicao]);
			$Mensagem  = "Inclusão da Nota Fiscal não poderá ser efetuada, pois o item $Dados[1] não tem valor unitário";
		} else {
			# Verifica se o usuário está ligado a algum centro de custo #
			if ($_SESSION['_cgrempcodi_'] != 0) {
				$db = Conexao();
				
				# Verifica se o Usuário está ligado a algum centro de Custo #
				$sql = "SELECT USUCEN.CUSUPOCODI ";
				$sql .= "FROM SFPC.TBUSUARIOCENTROCUSTO USUCEN, SFPC.TBCENTROCUSTOPORTAL CENCUS, ";
				$sql .= "SFPC.TBGRUPOEMPRESA GRUEMP,SFPC.TBORGAOLICITANTE ORGSOL,SFPC.TBUSUARIOPORTAL USUPOR ";
				$sql .= "WHERE USUCEN.CGREMPCODI <> 0 AND USUCEN.CCENPOSEQU = CENCUS.CCENPOSEQU AND USUCEN.FUSUCCTIPO IN ('T','R') ";
				$sql .= "AND USUCEN.CGREMPCODI = GRUEMP.CGREMPCODI AND ";
				$sql .= "CENCUS.CORGLICODI = ORGSOL.CORGLICODI AND ";
				$sql .= "USUCEN.CUSUPOCODI = USUPOR.CUSUPOCODI AND ";
				$sql .= "USUCEN.CGREMPCODI = ".$_SESSION['_cgrempcodi_']." AND USUCEN.CUSUPOCODI = ".$_SESSION['_cusupocodi_']." ";
				$sql .= "AND CENCUS.FCENPOSITU <> 'I' "; // Inclusão da condição para mostrar centro de custos diferentes de inativos
				$sql .= "ORDER BY GRUEMP.EGREMPDESC, ORGSOL.EORGLIDESC, CENCUS.ECENPODESC, USUPOR.EUSUPORESP ";
				
				$res = $db->query($sql);
					
				if (PEAR::isError($res)) {
					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				} else {
					$Rows = $res->numRows();
					
					if ($Rows == 0) {
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem = "O Usuário não está ligado a nenhum Centro de Custo";
					}
				}
				
				$db->disconnect();
			}

			$db = Conexao();
			
			# Verifica se o Fornecedor está cadastrado antes de continuar a rotina de inclusão #
			$sql  = "SELECT AFORCRSEQU FROM SFPC.TBFORNECEDORCREDENCIADO ";
			$sql .= " WHERE ";
				
				if ($CNPJ_CPF == 1) {
					$sql .= " AFORCRCCGC = '$CnpjCpf' ";
				} else {
					$sql .= " AFORCRCCPF = '$CnpjCpf' ";
				}
				
			$res = $db->query($sql);
			
			if (PEAR::isError($res)) {
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			} else {
				$rows = $res->numRows();
				
				if ($rows > 0) {
					$Linha = $res->fetchRow();
					$FornecedorSequ = $Linha[0];
				}
				
				if ((!$FornecedorCodi) and (!$FornecedorSequ)) {
					if ($Mens == 1) {
						$Mensagem .= ", ";
					}
					
					$Mens      = 1;
					$Tipo      = 2;
					$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialIncluir.CnpjCpf.focus();\" class=\"titulo2\">Fornecedor Cadastrado</a>";
				}
			}
			
			$db->disconnect();

			if ($NumeroNota != "" and SoNumeros($NumeroNota)) {
				# Verifica se já existe alguma nota com o mesmo número/série do mesmo fornecedor #
				$db = Conexao();
				
				$sql  = "SELECT COUNT(*) AS QTD FROM SFPC.TBENTRADANOTAFISCAL ";
				$sql .= "WHERE AENTNFNOTA = $NumeroNota AND AENTNFSERI = '$SerieNota' ";
				
					if ($FornecedorSequ) {
						$sql .= "AND AFORCRSEQU = $FornecedorSequ ";
					} else {
						$sql .= "AND CFORESCODI = $FornecedorCodi ";
					}
					
				$sql .= "  AND (FENTNFCANC IS NULL OR FENTNFCANC = 'N')";
				
				$res = $db->query($sql);
				
				if (PEAR::isError($res)) {
					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				} else {
					$Linha  = $res->fetchRow();
					
					if ($Linha[0] > 0) {
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem = "<a href=\"javascript:document.CadNotaFiscalMaterialIncluir.NumeroNota.focus();\" class=\"titulo2\">Número da Nota Fiscal já cadastrado para este Fornecedor</a>";
					}
				}
				
				$db->disconnect();
			}
			
			/**
			 *  Colocar para este caso se o material estiver inativo, verificar se a data de emissão do empenho > data de atualização do material. Logo,
			 *  devemos considerar que os materiais inativos, não podem entrar mais através da entrada de nota fiscal. Porém se o item for inativo e a 
			 * data de emissão do empenho <= data de alteração do material este item pode entrar no estoque por meio da entrada por nota fiscal.
            */
			
			if (in_array('I',$SituacaoMaterial)) {
              	$SequencialMateriais = implode(", ", $Material);

              	for ($i=0; $i< count($Empenhos); $i++) {
                	$Emp = explode(".",$Empenhos[$i]);
                	$DataEmissaoEmp[$i] = $Emp[1]; //TESTE
                	//$DataEmissaoEmp[$i] = $Emp[0]; //ORIGINAL
              	}

				if(!empty($DataEmissaoEmp[0])){
              	// Obtém os materiais inativos válidos, ou seja, que foram inativados após a data de emissão do empenho.
					$db = Conexao();
					
					$sql  = "SELECT CMATEPSEQU FROM SFPC.TBMATERIALPORTAL ";
					$sql .= "WHERE CMATEPSEQU IN ($SequencialMateriais) AND CMATEPSITU = 'I' ";
					if (count($DataEmissaoEmp) > 0) {
						$sql .= "   AND (  ";
						$sql .= "   TMATEPULAT < '$DataEmissaoEmp[0]'";
						
							if (count($DataEmissaoEmp) > 1) {
								for ($i=1; $i < count($DataEmissaoEmp); $i++) {
									$sql .= "   OR TMATEPULAT < '$DataEmissaoEmp[$i]' ";
								}
							}
						
						$sql .= "   )  ";
					}

              	$res = $db->query($sql);
					
					if (PEAR::isError($res)) {
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
					} else {
						$QtdeMateriaisInativos = $res->numRows();
						
						if ($QtdeMateriaisInativos > 0) {
							$Mens      = 1;
							$Tipo      = 1;
							$Mensagem = "Favor remover o(s) material(is) inativo(s) com o(s) Cód. Red.: ";

							for ($i = 0; $i < $QtdeMateriaisInativos; $i++) {
								$Linha = $res->fetchRow();
								$MateriaisInativos[$i] = $Linha[0];
							}

                  		$IndiceMatInativosInvalidos = array();

                  		for ($i = 0; $i < count($MateriaisInativos); $i++) {
                    		$IndiceMatInativoInvalido = array_keys($Material, $MateriaisInativos[$i]); //Só existe um código para o cada elemento do array, portanto sempre retornará um único registro para cada busca no array.
                    		array_push($IndiceMatInativosInvalidos, $IndiceMatInativoInvalido[0]);
                  		}

                  		for ($i = 0; $i < count($IndiceMatInativosInvalidos); $i++) {
                    		$indice = $IndiceMatInativosInvalidos[$i];
                    		$Mensagem .= "<a href=\"javascript: document.getElementById('codMaterial$indice').style.fontWeight='bold'; document.getElementsByName('CheckItem[$indice]').item(0).focus();\" class=\"titulo2\">$Material[$indice]</a>";

                    		if ($i == count($IndiceMatInativosInvalidos) - 2) {
                      			$Mensagem .= " e ";
                    		} else {
                      			if ($i < count($IndiceMatInativosInvalidos) - 2) {
                        			$Mensagem .= ", ";
                      			}
                    		}
                  		}
                	}
              	}
              	$db->disconnect();
            }
		}
            if ($EstoqueVirtual == null || $EstoqueVirtual == '') {
              	$EstoqueVirtual = 'N';
            }

            //Verificando se existe valor de empenho disponível para a nota fiscal.
            if ($Mens == 0) {
              	$ValorEmpTotal = 0;

              	//Variaveis para obter o valor do(s) empenho(s)
              	for ($i=0; $i< count($Empenhos); $i++) {
                	//O índice 1 - refere-se a data de emissão do empenho/subempenho.
                	$Emp = explode(".",$Empenhos[$i]);
                	$ValorEmpTotal = $ValorEmpTotal + str_replace(",",".",$Emp[0]);
                	$AnoEmp[$i]         = $Emp[2];
                	$OrgaoEmp[$i]       = $Emp[3];
                	$UnidadeEmp[$i]     = $Emp[4];
                	$SequencialEmp[$i]  = $Emp[5];
                	$ParcelaEmp[$i]     = $Emp[6];

                	# Se o empenho não tiver parcela, recebe 0 para pesquisar no banco #
                	if (!$ParcelaEmp[$i]) {
						$ParcelaEmp[$i] = 0;
					}
              	}

              	$db = Conexao();
				  
				$sql  = "SELECT SUM(VENTNFTOTA) FROM SFPC.TBENTRADANOTAFISCAL ENT ";
              	$sql .= " WHERE (FENTNFCANC IS NULL OR FENTNFCANC = 'N')  ";
				$sql .= " AND (ENT.CALMPOCODI, ENT.AENTNFANOE, ENT.CENTNFCODI) IN  ";
                $sql .= "   (  ";
                $sql .= "   SELECT DISTINCT EMP.CALMPOCODI, EMP.AENTNFANOE, EMP.CENTNFCODI  ";
                $sql .= "   FROM SFPC.TBNOTAFISCALEMPENHO EMP, SFPC.TBENTRADANOTAFISCAL ENT  ";
                $sql .= "     WHERE  ";
                $sql .= "       (   ";
                $sql .= "         EMP.ANFEMPANEM = ".$AnoEmp[0]." AND EMP.CNFEMPOREM = ".$OrgaoEmp[0]."   ";
                $sql .= "         AND EMP.CNFEMPUNEM = ".$UnidadeEmp[0]." AND EMP.CNFEMPSEEM = ".$SequencialEmp[0]." ";
                $sql .= "         AND EMP.CNFEMPPAEM = ".$ParcelaEmp[0]."  ";
                $sql .= "       )  ";

                	if (count($Empenhos) > 1) {
                  		for ($i=1; $i < count($Empenhos); $i++) {
                    		$sql .= "      OR   ";
                    		$sql .= "       (   ";
                    		$sql .= "         EMP.ANFEMPANEM = ".$AnoEmp[$i]." AND EMP.CNFEMPOREM = ".$OrgaoEmp[$i]."   ";
                    		$sql .= "         AND EMP.CNFEMPUNEM = ".$UnidadeEmp[$i]." AND EMP.CNFEMPSEEM = ".$SequencialEmp[$i]." ";
                    		$sql .= "         AND EMP.CNFEMPPAEM = ".$ParcelaEmp[$i]."  ";
                    		$sql .= "       )  ";
                  		}
                	}
				
				$sql .= "   )  ";

              	$res = $db->query($sql);
				  
				if (PEAR::isError($res)) {
					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				} else {
                 	$Linha = $res->fetchRow();
                 	$ValorNotaTotal = $Linha[0];

                 	if ($ValorNotaTotal == null || $ValorNotaTotal == '') {
                   		$ValorNotaTotal = 0;
                 	}
              	}

              	$db->disconnect();

              	//Verificar se o valor do(s) empenho(s) > [valor da(s) nota(s) fisca(is) associada(s) ao(s) empenho(s) + Valor da nota a ser inserida]
              	$ValorDisponivel = $ValorEmpTotal - ($ValorNotaTotal + $ValorNota);
				// lucas
              	//if ($ValorDisponivel < -2) { //Está sendo colocado -2 para que possa entrar com valores de notas fiscais com uma diferença de até R$ 2,00 do valor do empenho/subempenho.
                //  	$Mens      = 1;
                //  	$Tipo      = 2;
                //  	$Mensagem .= "Valor do(s) empenhos disponível(is) menor do que valor da nota fiscal";
              	//}
            }

			# Faz a inserção a partir daqui #
			if ($Mens == 0) {
				$Requisicao = 0;
				$DataHora = date("Y-m-d H:i:s");
				
				$db = Conexao();
				$db->query("BEGIN TRANSACTION");
				
				if ($_SESSION['_cgrempcodi_'] != 0) {
					# Recupera o último Código da NotaFiscal pro Almoxarifado/Ano e incrementa mais um #
					$sql    = "SELECT MAX(CENTNFCODI) FROM SFPC.TBENTRADANOTAFISCAL ";
					$sql   .= "WHERE	AENTNFANOE = $AnoExercicio AND CALMPOCODI = $Almoxarifado";
					
					$result = $db->query($sql);
					
					if (PEAR::isError($result)) {
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
					} else {
						$Linha      = $result->fetchRow();
						$NotaFiscal = $Linha[0] + 1;
						
						# Insere a Nota Fiscal - Criação da Query #
						$sql  = "INSERT INTO SFPC.TBENTRADANOTAFISCAL( ";
						$sql .= "CALMPOCODI, AENTNFANOE, CENTNFCODI, AFORCRSEQU, ";
						$sql .= "CFORESCODI, AENTNFNOTA, AENTNFSERI, DENTNFEMIS, ";
						$sql .= "DENTNFENTR, ";
						$sql .= "CGREMPCODI, CUSUPOCODI, ";
						$sql .= "TENTNFULAT, FENTNFVIRT  ";
						$sql .= ") VALUES ( ";
						$sql .= "$Almoxarifado, $AnoExercicio, $NotaFiscal, ";

							if ($FornecedorSequ != "") {
								$sql .= "$FornecedorSequ, NULL, ";
							} else {
								$sql .= "NULL, $FornecedorCodi, ";
							}
						
						$sql .= "$NumeroNota, '$SerieNota', '".DataInvertida($DataEmissao)."',  ";
						$sql .= "'".DataInvertida($DataEntrada)."', ";
						$sql .= $_SESSION['_cgrempcodi_'].", ".$_SESSION['_cusupocodi_'].",";
						$sql .= "'".$DataHora."', '$EstoqueVirtual' ) ";
						
						$sqlentradanf = $sql;
						
						$result = $db->query($sql);
						
						if (PEAR::isError($result)) {
							$Rollback = 1;
							$CodErroEmail  = $result->getCode();
							$DescErroEmail = $result->getMessage();
							$db->query("ROLLBACK");
							$db->disconnect();
							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						} else {
							# Insere os Empenhos #
							for ($i=0; $i < count($Empenhos) and !$Rollback; $i++) {
								# Separa Ano, Órgão, Unidade, Sequencial e Parcela #
								$Emp = explode(".",$Empenhos[$i]);
                                $AnoEmp        = $Emp[2];
								$OrgaoEmp      = $Emp[3];
								$UnidadeEmp    = $Emp[4];
								$SequencialEmp = $Emp[5];
								$ParcelaEmp    = $Emp[6];

								# Se o empenho não tiver parcela, recebe o valor para inserir no banco #
								if (!$ParcelaEmp) {
									$ParcelaEmp = 0;
								}
								
								# Insere empenho #
								$sqlemp  = "INSERT INTO SFPC.TBNOTAFISCALEMPENHO ( ";
								$sqlemp .= " CALMPOCODI, AENTNFANOE, CENTNFCODI, ";
								$sqlemp .= " ANFEMPANEM, CNFEMPOREM, CNFEMPUNEM, ";
								$sqlemp .= " CNFEMPSEEM, CNFEMPPAEM, TNFEMPULAT ";
								$sqlemp .= ") VALUES ( ";
								$sqlemp .= " $Almoxarifado, $AnoExercicio, $NotaFiscal, ";
								$sqlemp .= " $AnoEmp, $OrgaoEmp, $UnidadeEmp, ";
								$sqlemp .= " $SequencialEmp, $ParcelaEmp, '".$DataHora."' ) ";
								
								$resemp = $db->query($sqlemp);
								
								if (PEAR::isError($resemp)) {
									$Rollback = 1;
									$CodErroEmail  = $resemp->getCode();
									$DescErroEmail = $resemp->getMessage();
									$db->query("ROLLBACK");
									$db->disconnect();
									ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlemp\n\n$DescErroEmail ($CodErroEmail)");
								}
							}

							#Insere requisição de nota fiscal virtual
							if ($EstoqueVirtual == 'S') {
								$DataGravacao = date("Y-m-d H:i:s");

								if ($_SESSION['DataGravacao']) {
									$DataGravacaoSession = str_replace(":","-",str_replace(" ","-",$_SESSION['DataGravacao']));
									$DGS = explode("-",$DataGravacaoSession);
									$MomentoSession = (86400*$DGS[2]) + (3600*$DGS[3]) + (60*$DGS[4]) + ($DGS[5]); // Dia, Hora, Minuto, Segundo --> Segundos
									$DataGravacaoVariavel = str_replace(":","-",str_replace(" ","-",$DataGravacao));
									$DGV = explode("-",$DataGravacaoVariavel);
									$MomentoVariavel = (86400*$DGV[2]) + (3600*$DGV[3]) + (60*$DGV[4]) + ($DGV[5]); // Dia, Hora, Minuto, Segundo --> Segundos
								}
								
								$CodErro = -3; // Para provocar a primeira entrada
								
								while ($CodErro == -3) { // Enquanto o erro for de chave duplicada, faz tudo de novo, pega o max e tenta inserir
									$CodErro        = null; // Seta null em CodErro, para só voltar a ser -3 se houver outra chave duplicada na próxima tentativa
									$ErroGravaBanco = null;
									
									# Recupera o último Código da Requisição e incrementa mais um #
									$sql  = "SELECT MAX(CREQMACODI) FROM SFPC.TBREQUISICAOMATERIAL ";
									$sql .= " WHERE AREQMAANOR = $AnoRequisicao AND CORGLICODI = $OrgaoUsuario";
									
									$res = $db->query($sql);
									
									if (PEAR::isError($res)) {
										$ErroGravaBanco = 1;
										$CodErroEmail  = $res->getCode();
										$DescErroEmail = $res->getMessage();
										$db->query("ROLLBACK");
										$db->query("END TRANSACTION");
										$db->disconnect();
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
										exit(0);
									}
									
									$Linha      = $res->fetchRow();
									$Requisicao = $Linha[0] + 1;
									
									# Insere a requisição #
									$sql  = "INSERT INTO SFPC.TBREQUISICAOMATERIAL ( ";
									$sql .= "CREQMASEQU, CORGLICODI, AREQMAANOR, ";
									$sql .= "CREQMACODI, CCENPOSEQU, CGREMPCODI, ";
									$sql .= "CUSUPOCODI, FREQMATIPO, EREQMAOBSE, DREQMADATA, ";
									$sql .= "TREQMAULAT, CALMPOCODI ";
									$sql .= ") VALUES ( ";
									$sql .= "nextval('SFPC.TBrequisicaomaterial_creqmasequ_seq'), $OrgaoUsuario, $AnoRequisicao, ";
									$sql .= "$Requisicao, $CentroCusto, ".$_SESSION['_cgrempcodi_'].", ";
									$sql .= "".$_SESSION['_cusupocodi_'].", ";
									$sql .= "'R', '$Observacao', '".DataInvertida($DataRequisicao)."', '".$DataGravacao."', $Almoxarifado )";
									
									$res  = $db->query($sql);
									
									if (PEAR::isError($res)) {
										$ErroGravaBanco = 1;
										$CodErroEmail   = $res->getCode();
										$DescErroEmail  = $res->getMessage();
										$CodErro        = $CodErroEmail;
										$Rollback = 1;
										$db->query("ROLLBACK");
										$db->query("END TRANSACTION");
										$db->disconnect();
										
										if ($CodErro != -3) { // Outro erro, diferente de chave duplicada, exibe mensagem de erro e envia e-mail para o analista
											ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
										}
									}
								}
								
								if ($ErroGravaBanco != 1) {
									# Recupera o Sequencial da Requisição estabelecido pelo nextval do Insert acima #
									$sql  = "SELECT CREQMASEQU ";
									$sql .= "  FROM SFPC.TBREQUISICAOMATERIAL ";
									$sql .= " WHERE CALMPOCODI = $Almoxarifado ";
									$sql .= "   AND CORGLICODI = $OrgaoUsuario ";
									$sql .= "   AND AREQMAANOR = $AnoRequisicao ";
									$sql .= "   AND CREQMACODI = $Requisicao ";
									$sql .= "   AND CCENPOSEQU = $CentroCusto ";
									$sql .= "   AND CGREMPCODI = ".$_SESSION['_cgrempcodi_']." ";
									$sql .= "   AND CUSUPOCODI = ".$_SESSION['_cusupocodi_']." ";
									$sql .= "   AND FREQMATIPO = 'R' ";
									$sql .= "   AND DREQMADATA = '".DataInvertida($DataRequisicao)."' ";
									$sql .= "   AND TREQMAULAT = '".$DataGravacao."' ";
									
									$res = $db->query($sql);
									
									if (PEAR::isError($res)) {
										$CodErroEmail  = $res->getCode();
										$DescErroEmail = $res->getMessage();
										$Rollback = 1;
										$db->query("ROLLBACK");
										$db->query("END TRANSACTION");
										$db->disconnect();
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
									} else {
										$Linha          = $res->fetchRow();
										$SeqRequisicao  = $Linha[0];
										
										# Código do tipo da situação 1 (EM ANÁLISE) #
										$sql  = "INSERT INTO SFPC.TBSITUACAOREQUISICAO ( ";
										$sql .= "CREQMASEQU, CTIPSRCODI, TSITRESITU, ";
										$sql .= "CGREMPCODI, CUSUPOCODI, TSITREULAT ";
										$sql .= ") VALUES ( ";
										$sql .= "$SeqRequisicao, 3 , '".$DataGravacao."', ";
										$sql .= "".$_SESSION['_cgrempcodi_']." ,".$_SESSION['_cusupocodi_'].", '".$DataGravacao."' )";
										
										$res  = $db->query($sql);
										
										if (PEAR::isError($res)) {
											$CodErroEmail  = $res->getCode();
											$DescErroEmail = $res->getMessage();
											$Rollback = 1;
											$db->query("ROLLBACK");
											$db->query("END TRANSACTION");
											$db->disconnect();
											ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
										} else {

											# Insere Itens de Requisição de Material #
											$Carregou = "";
											$ItemRequisicao = $ItemNotaFiscal;
											
											for ($i=0; $i< count($ItemRequisicao) and !$ErrosNoFor; $i++) {
												$QtdSol = str_replace(",",".",$Quantidade[$i]);
												
												if ($QtdSol != 0) {
													$Ordem  = $i + 1;
													
													$sql  = "INSERT INTO SFPC.TBITEMREQUISICAO ( ";
													$sql .= "CREQMASEQU, CMATEPSEQU, AITEMRORDE, ";
													$sql .= "AITEMRQTSO, AITEMRQTAP, AITEMRQTAT, ";
													$sql .= "AITEMRQTCA, CGREMPCODI, CUSUPOCODI, ";
													$sql .= "TITEMRULAT ";
													$sql .= ") VALUES ( ";
													$sql .= "$SeqRequisicao, $Material[$i], $Ordem, ";
													$sql .= "$QtdSol, NULL, $QtdSol, ";
													$sql .= "NULL, ".$_SESSION['_cgrempcodi_'].", ".$_SESSION['_cusupocodi_'].", ";
													$sql .= " '".$DataGravacao."' )";
													
													$result = $db->query($sql);
																											
													if (PEAR::isError($result)) {
														$CodErroEmail  = $result->getCode();
														$DescErroEmail = $result->getMessage();
														$ErrosNoFor = 1;
														$Carregou = "N";
														$Rollback = 1;
														$db->query("ROLLBACK");
														$db->query("END TRANSACTION");
														$db->disconnect();
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
													} else {
														$Carregou ="S";
													}
												}	
											}
												
											if ($Carregou == "S") {
												$_SESSION['OrgaoUsuario']   = $OrgaoUsuario;
												$_SESSION['CentroCusto']    = $CentroCusto;
												$_SESSION['DataRequisicao'] = $DataRequisicao;
												$_SESSION['Material']       = $Material;
												$_SESSION['Quantidade']     = $Quantidade;
												$_SESSION['DataGravacao']   = $DataGravacao;
												$_SESSION['Requisicao']     = $Requisicao;
												$_SESSION['AnoRequisicao']  = $AnoRequisicao;
											}
										}
									}
								}
							}

							if ($Rollback != 1) {
								# Insere Itens da Nota Fiscal #
								$decValorNota = 0;
								
								for ($i=0; $i< count($ItemNotaFiscal); $i++) {
									# Formata Valores para uso de calculos #
									$decQtdItem   = str_replace(",",".",$Quantidade[$i]);
									$decValItem   = str_replace(",",".",$ValorUnitario[$i]);
									$decTotalItem = str_replace(",",".",($decQtdItem*$decValItem));
									$decValorNota = $decValorNota + $decTotalItem;

									# Insere os itens da Nota Fiscal #
									$sqlnf  = "INSERT INTO SFPC.TBITEMNOTAFISCAL ( ";
									$sqlnf .= "CALMPOCODI, AENTNFANOE, CENTNFCODI, CMATEPSEQU, ";
									$sqlnf .= "AITENFQTDE, VITENFUNIT, TITENFULAT ";
									$sqlnf .= ") VALUES ( ";
									$sqlnf .= "$Almoxarifado, $AnoExercicio, $NotaFiscal, $Material[$i], ";
									$sqlnf .= "$decQtdItem, $decValItem, '".$DataHora."' ) ";
									
									$resnf = $db->query($sqlnf);
																		
									if (PEAR::isError($resnf)) {
										$Rollback = 1;
										$db->query("ROLLBACK");
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlnf");
									} else {
										# Pega o Máximo valor do Movimento de Material #
										$sqlmaxmov  = "SELECT MAX(CMOVMACODI) AS CODIGO FROM SFPC.TBMOVIMENTACAOMATERIAL";
										$sqlmaxmov .= " WHERE CALMPOCODI = $Almoxarifado AND AMOVMAANOM = $AnoExercicio ";

										$resmaxmov  = $db->query($sqlmaxmov);
										
										if (PEAR::isError($resmaxmov)) {
											ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmaxmov");
										} else {
											$Linhamaxmov = $resmaxmov->fetchRow();
											$Movimento   = $Linhamaxmov[0] + 1;
										}
																			
										if ($Quantidade[$i] != "") {
											# Pega o valor e quantidade atual do estoque(armazenamento) #
											$sqlarmat  = "SELECT VARMATUMED, AARMATQTDE, AARMATESTR, AARMATVIRT FROM SFPC.TBARMAZENAMENTOMATERIAL ";
											$sqlarmat .= "WHERE CMATEPSEQU = $Material[$i] AND CLOCMACODI = $Localizacao ";
											
											$resarmat  = $db->query($sqlarmat);
											
											if (PEAR::isError($resarmat)) {
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlarmat");
											} else {
												$rowsarmat = $resarmat->numRows();
												
												# É um item do estoque(armazenamento) #
												if ($rowsarmat > 0) {
													$Linhaarmat           = $resarmat->fetchRow();
													$decValEstoque        = str_replace(",",".",$Linhaarmat[0]);
													$decQtdEstoque        = str_replace(",",".",$Linhaarmat[1]);
													$decQtdEstoqueReal    = str_replace(",",".",$Linhaarmat[2]);
													$decQtdEstoqueVirtual = str_replace(",",".",$Linhaarmat[3]);
													$decTotalEstoque      = str_replace(",",".",($decQtdEstoque*$decValEstoque));

                                                    if ($decQtdEstoqueReal == null || $decQtdEstoqueReal == '') {
                                                      	$decQtdEstoqueReal = 0;
                                                    }

                                                    if ($decQtdEstoqueVirtual == null || $decQtdEstoqueVirtual == '') {
                                                      	$decQtdEstoqueVirtual = 0;
                                                    }

													# Calcula o valor medio a partir do totalizador do estoque atual e o totalizador do item da nota #
													if (($decQtdEstoque == 0) or ($decQtdEstoque == '')) {
														$decValorMedio = str_replace(",",".",$decValItem); // Se o estoque está zerado, o valor médio será o valor do item na nota
													} else {
														$ValorMedio    = ( $decTotalItem + $decTotalEstoque ) / ( $decQtdItem + $decQtdEstoque );
														$decValorMedio = str_replace(",",".",$ValorMedio);
													}

													# Atualiza o valor unitário medio e o ultimo valor de compra de cada item #
													$sqlupdarmat  = "UPDATE SFPC.TBARMAZENAMENTOMATERIAL ";
													$sqlupdarmat .= "   SET ";
													
														if ($EstoqueVirtual != 'S') {  //estoque virtual menos requição não alteram quantidade no estoque
															$sqlupdarmat .= "   		AARMATQTDE = ($decQtdItem + $decQtdEstoque), ";
															$sqlupdarmat .= "       AARMATESTR = ($decQtdItem + $decQtdEstoqueReal), ";
														}
													
													$sqlupdarmat .= "       VARMATUMED = $decValorMedio, VARMATULTC = $decValItem, ";
													$sqlupdarmat .= "       CGREMPCODI = ".$_SESSION['_cgrempcodi_'].", CUSUPOCODI = ".$_SESSION['_cusupocodi_'].", ";
													$sqlupdarmat .= "       TARMATULAT = '".$DataHora."' ";
													$sqlupdarmat .= " WHERE CMATEPSEQU = $Material[$i] AND CLOCMACODI = $Localizacao ";
													
													$resupdarmat = $db->query($sqlupdarmat);
													
													if (PEAR::isError($resupdarmat)) {
														$Rollback = 1;
														$db->query("ROLLBACK");
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlupdarmat");
														exit(0);
													}
													
													# Pega o Máximo valor do Movimento do Material do Tipo - ENTRADA POR NF DE COMPRA #
													$sqltipo  = "SELECT MAX(CMOVMACODT) FROM SFPC.TBMOVIMENTACAOMATERIAL";
													$sqltipo .= " WHERE CALMPOCODI = $Almoxarifado AND AMOVMAANOM = ".date("Y")." ";
													$sqltipo .= "   AND CTIPMVCODI = 3 ";

													$restipo  = $db->query($sqltipo);
													
													if (PEAR::isError($restipo)) {
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqltipo");
													} else {
														$LinhaTipo     = $restipo->fetchRow();
														$TipoMovimento = $LinhaTipo[0] + 1;
													}																										
													
													# Insere na tabela de Movimentação de Material - Tipo 3 Entrada por NF de Compra #
													$sqlmovmat  = "INSERT INTO SFPC.TBMOVIMENTACAOMATERIAL ( ";
													$sqlmovmat .= "CALMPOCODI, AMOVMAANOM, CMOVMACODI, DMOVMAMOVI, ";
													$sqlmovmat .= "CTIPMVCODI, CREQMASEQU, CMATEPSEQU, AMOVMAQTDM, ";
													$sqlmovmat .= "VMOVMAVALO, VMOVMAUMED, CGREMPCODI, CUSUPOCODI, TMOVMAULAT, ";
													$sqlmovmat .= "AENTNFANOE, CENTNFCODI, CMOVMACODT, AMOVMAMATR, ";
													$sqlmovmat .= "NMOVMARESP ";
													$sqlmovmat .= ") VALUES ( ";
													$sqlmovmat .= "$Almoxarifado, $AnoExercicio, $Movimento, '".date('Y-m-d')."', ";
													$sqlmovmat .= "3, NULL, $Material[$i], $decQtdItem, ";
													$sqlmovmat .= "$decValItem, $decValorMedio, ".$_SESSION['_cgrempcodi_'].", ".$_SESSION['_cusupocodi_'].", '".$DataHora."', ";
													$sqlmovmat .= "$AnoExercicio, $NotaFiscal, $TipoMovimento, NULL, ";
													$sqlmovmat .= "NULL )";
													
													$resmovmat  = $db->query($sqlmovmat);
													
													if (PEAR::isError($resmovmat)) {
														$Rollback = 1;
														$db->query("ROLLBACK");
														EmailErroDB("Erro em SQL", "Erro em SQL", $resmovmat);
													}
													
													# É um material (material portal) que não está no estoque  #
												} else {
													$QtdEstoque = $decQtdItem;
													$decValorMedio = $decValItem;
													
													if ($EstoqueVirtual == 'S') {
														$QtdEstoque = 0;
													}
													
													# Grava direto na tabela de armazenamento sem cálculo e grava a movimentação com o tipo entrada por nf de compra #
													$sqlarmat2  = "INSERT INTO SFPC.TBARMAZENAMENTOMATERIAL ( ";
													$sqlarmat2 .= "CMATEPSEQU, CLOCMACODI, AARMATQTDE, AARMATMAXI, ";
													$sqlarmat2 .= "AARMATESTS, AARMATESTR, AARMATVIRT, AARMATESTC, ";
													$sqlarmat2 .= "AARMATNIVR, AARMATPONT, VARMATUMED, VARMATULTC, ";
													$sqlarmat2 .= "CGREMPCODI, CUSUPOCODI, TARMATULAT ";
													$sqlarmat2 .= ") VALUES ( ";
													$sqlarmat2 .= "$Material[$i], $Localizacao, $QtdEstoque, NULL, ";
													$sqlarmat2 .= "NULL, "; //Refere-se ao campo AARMATESTS
                                                    $sqlarmat2 .= "$QtdEstoque, 0, "; //Refere-se ao campo AARMATESTR
													$sqlarmat2 .= " NULL, "; //Refere-se ao campo AARMATESTC
													$sqlarmat2 .= "NULL, NULL, $decValorMedio, $decValItem, ";
													$sqlarmat2 .= "".$_SESSION['_cgrempcodi_'].", ".$_SESSION['_cusupocodi_'].", '".$DataHora."' )";
													
													$resarmat2  = $db->query($sqlarmat2);
													
													if (PEAR::isError($resarmat2)) {
														$Rollback = 1;
														$db->query("ROLLBACK");
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlarmat2");
													} else {
														# Pega o Máximo valor do Movimento do Material do Tipo - ENTRADA POR NF DE COMPRA #
														$sqltipo  = "SELECT MAX(CMOVMACODT) FROM SFPC.TBMOVIMENTACAOMATERIAL";
														$sqltipo .= " WHERE CALMPOCODI = $Almoxarifado AND AMOVMAANOM = ".date("Y")." ";
														$sqltipo .= "   AND CTIPMVCODI = 3 ";

														$restipo  = $db->query($sqltipo);
														
														if (PEAR::isError($restipo)) {
															$Rollback = 1;
															$db->query("ROLLBACK");
															ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqltipo");
														} else {
															$LinhaTipo     = $restipo->fetchRow();
															$TipoMovimento = $LinhaTipo[0] + 1;
														}

														# Insere na tabela de Movimentação de Material do Tipo 3 - ENTRADA POR NF DE COMPRA #
														$sqlmovmat2  = "INSERT INTO SFPC.TBMOVIMENTACAOMATERIAL ( ";
														$sqlmovmat2 .= "CALMPOCODI, AMOVMAANOM, CMOVMACODI, DMOVMAMOVI, ";
														$sqlmovmat2 .= "CTIPMVCODI, CREQMASEQU, CMATEPSEQU, AMOVMAQTDM, ";
														$sqlmovmat2 .= "VMOVMAVALO, VMOVMAUMED, CGREMPCODI, CUSUPOCODI, TMOVMAULAT, ";
														$sqlmovmat2 .= "AENTNFANOE, CENTNFCODI, CMOVMACODT, AMOVMAMATR, ";
														$sqlmovmat2 .= "NMOVMARESP ";
														$sqlmovmat2 .= ") VALUES ( ";
														$sqlmovmat2 .= "$Almoxarifado, $AnoExercicio, $Movimento, '".date('Y-m-d')."', ";
														$sqlmovmat2 .= "3, NULL, $Material[$i], $decQtdItem, ";
														$sqlmovmat2 .= "$decValItem, $decValItem, ".$_SESSION['_cgrempcodi_'].", ".$_SESSION['_cusupocodi_'].", '".$DataHora."', ";
														$sqlmovmat2 .= "$AnoExercicio, $NotaFiscal, $TipoMovimento, NULL, ";
														$sqlmovmat2 .= "NULL )";
														
														$resmovmat2  = $db->query($sqlmovmat2);
														
														if (PEAR::isError($resmovmat2)) {
															$Rollback = 1;
															$db->query("ROLLBACK");
															ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmovmat2");
														}
													}
												}

												# Movimentação de requisição de nota fiscal virtual #
												if ($EstoqueVirtual == 'S') {
													$Movimento++;
													#Caso valor médio não foi calculado, usar o valor unitário
													#(valor médio não é calculado caso o material não existia no almoxarifado, e está sendo incluso agora pela nota fiscal)

													# Insere na tabela de Movimentação de Material do Tipo 4 - SAÍDA POR REQUISIÇÃO # (atendida)
													$sqlmovmat2  = "INSERT INTO SFPC.TBMOVIMENTACAOMATERIAL ( ";
													$sqlmovmat2 .= "CALMPOCODI, AMOVMAANOM, CMOVMACODI, DMOVMAMOVI, ";
													$sqlmovmat2 .= "CTIPMVCODI, CREQMASEQU, CMATEPSEQU, AMOVMAQTDM, ";
													$sqlmovmat2 .= "VMOVMAVALO, VMOVMAUMED, CGREMPCODI, CUSUPOCODI, TMOVMAULAT, ";
													$sqlmovmat2 .= "AENTNFANOE, CENTNFCODI, CMOVMACODT, AMOVMAMATR, ";
													$sqlmovmat2 .= "NMOVMARESP ";
													$sqlmovmat2 .= ") VALUES ( ";
													$sqlmovmat2 .= "$Almoxarifado, $AnoExercicio, $Movimento, '".date('Y-m-d')."', ";
													$sqlmovmat2 .= "4, $SeqRequisicao, $Material[$i], $decQtdItem, ";
													$sqlmovmat2 .= "$decValorMedio, $decValorMedio, ".$_SESSION['_cgrempcodi_'].", ".$_SESSION['_cusupocodi_'].", '".$DataHora."', ";
													$sqlmovmat2 .= "$AnoExercicio, $NotaFiscal, $TipoMovimento, NULL, ";
													$sqlmovmat2 .= "NULL )";
													
													$resmovmat2  = $db->query($sqlmovmat2);
													
													if (PEAR::isError($resmovmat2)) {
														$Rollback = 1;
														$db->query("ROLLBACK");
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmovmat2");
													}
												}
											}
										}
									}
								}
							}
						}
					}

					if ($Rollback != 1) {
						# Atualiza o valor da nota através dos itens da nota #
						$sql  = "UPDATE SFPC.TBENTRADANOTAFISCAL ";
						$sql .= "   SET VENTNFTOTA = $decValorNota, CGREMPCODI = ".$_SESSION['_cgrempcodi_'].", ";
						$sql .= "       CUSUPOCODI = ".$_SESSION['_cusupocodi_'].", TENTNFULAT = '".$DataHora."' ";
						$sql .= " WHERE CENTNFCODI = $NotaFiscal AND CALMPOCODI = $Almoxarifado AND AENTNFANOE = $AnoExercicio ";
						
						$res  = $db->query($sql);
						
						if (PEAR::isError($res)) {
							$db->query("ROLLBACK");
							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						} else {
							$db->query("COMMIT");
							$db->query("END TRANSACTION");
							$Mens      = 1;
							$Tipo      = 1;
							$Mensagem  = "Nota Fiscal Incluída com Sucesso";
							
							if ($EstoqueVirtual == 'S') {
								$Mensagem .= "<br/>Número da Requisição Gerado foi: ".substr($Requisicao+100000,1)."/$AnoRequisicao";
							}

							# Limpando os dados da nota #
							$TipoUsuario    = "";
							$OrgaoUsuario   = "";
							$CentroCusto    = "";
							$Almoxarifado   = "";
							$NumeroNota     = "";
							$SerieNota      = "";
							$DataEmissao    = "";
                            $EstoqueVirtual = "";
                            $CNPJ_CPF       = "";
							$CnpjCpf        = "";
							$RazaoSocial    = "";
							$DataEntrada    = "";
							$ValorNota      = "";
							$ValorTotalNota = "";
                            $Localizacao    = "";

							# Limpando variáveis de empenho #
							$CheckEmp = "";
							unset($Empenhos);
							unset($_SESSION['Empenho']);

							# Limpandos os dados do item da nota #
							$CheckItem        = "";
							$Material         = "";
							$DescMaterial     = "";
							$Unidade          = "";
                            $SituacaoMaterial = "";
							$Quantidade       = "";
							$ValorUnitario    = "";
							$ValorTotal       = "";
							$InicioPrograma   = "";
							unset($ItemNotaFiscal);
							unset($_SESSION['item']);

							# Valores dos calculos #
							$ValInicial      = 0;
							$QtdInicial      = 0;
							$SomaValorMedio  = 0;
							$SomaQtdMedio    = 0;
							$ValorMedio      = 0;
							$ValUnitarioItem = 0;
							$QuantidadeItem  = 0;

							#Caixa de requisição
							unset($ItemRequisicao);
							$DataRequisicao = "";
							$Observacao     = "";
						}
					}
				} else {
					$Mens      = 1;
					$Tipo      = 2;
					$Mensagem = "O Usuário do grupo INTERNET não pode fazer inclusão de Nota Fiscal";
				}
				
				$db->disconnect();
			}
		}
	}
} elseif ($Botao == "Retirar") {
	if (count($ItemNotaFiscal) != 0) {
		for ($i=0; $i< count($ItemNotaFiscal); $i++) {
			if ($CheckItem[$i] == "") {
				$Qtd++;
				$CheckItem[$i]          = "";
				$ItemNotaFiscal[$Qtd-1] = $ItemNotaFiscal[$i];
				$Material[$Qtd-1]       = $Material[$i];
				$DescMaterial[$Qtd-1]   = $DescMaterial[$i];
				$Unidade[$Qtd-1]        = $Unidade[$i];
                $SituacaoMaterial[Qtd-1] = $SituacaoMaterial[$i];
				$Quantidade[$Qtd-1]     = $Quantidade[$i];
				$ValorUnitario[$Qtd-1]  = $ValorUnitario[$i];
				$ValorTotal[$Qtd-1]     = $ValorTotal[$i];
			}
		}
		
		if (count($ItemNotaFiscal) > 1) {
			$ItemNotaFiscal = array_slice($ItemNotaFiscal,0,$Qtd);
			$Material       = array_slice($Material,0,$Qtd);
			$DescMaterial   = array_slice($DescMaterial,0,$Qtd);
			$Unidade        = array_slice($Unidade,0,$Qtd);
            $SituacaoMaterial = array_slice($SituacaoMaterial,0,$Qtd);
			$Quantidade     = array_slice($Quantidade,0,$Qtd);
			$ValorUnitario  = array_slice($ValorUnitario,0,$Qtd);
			$ValorTotal     = array_slice($ValorTotal,0,$Qtd);
		} else {
			unset($ItemNotaFiscal);
			unset($Material);
			unset($DescMaterial);
			unset($Unidade);
            unset($SituacaoMaterial);
			unset($Quantidade);
			unset($ValorUnitario);
			unset($ValorTotal);
		}
	}
	unset($_SESSION['item']);
} elseif ($Botao == "Totalizar") {
	if (count($Quantidade) != 0) {
		# Verifica se existe alguma quantidade igual a branco ou zero #
		for ($i=0; $i<count($Quantidade); $i++) {
			if ($TipoItem[$i] != "B") {
				if ((str_replace(",",".",$Quantidade[$i]) == 0 or $Quantidade[$i] == "") and $Existe == "") {
					$Existe  = "S";
					$Posicao = $i;
				}
			}
		}
				
		if ($Existe == "S") {
			if ($Mens == 1) {
				$Mensagem .= ", ";
			}
			
			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "<a href=\"javascript:document.getElementsByName('Quantidade[$i]').item(0).focus();\" class=\"titulo2\">Quantidade</a>";
		}
		
		# Verifica se as quantidades só são numeros e decimais #
		if ($Existe == "") {
			$Posicao = "";
			
			for ($k=0; $k<count($Quantidade);$k++) {
				if ($TipoItem[$k] != "B") {
					if ((!SoNumVirg($Quantidade[$k])) and ($Existe == "")) {
						$Existe  = "S";
						$Posicao = $k;
					}
				}
			}
						
			if ($Existe == "") {
				for ($j=0; $j<count($Quantidade); $j++) {
					if ($TipoItem[$j] != "B") {
						if ((!Decimal($Quantidade[$j])) and $Existe == "") {
							$Existe  = "S";
							$Posicao = $j;
						}
					}
				}
			}
			
			if ($Existe == "S") {
				if ($Mens == 1) {
					$Mensagem .= ", ";
				}

				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.getElementsByName('Quantidade[$i]').item(0).focus();\" class=\"titulo2\">Quantidade Válida</a>";
			}
		}
	}
	
	if (count($ValorUnitario) != 0) {
		$Existe = "";
		
		# Verifica se existe algum valor unitario igual a branco ou zero #
		for ($i=0; $i<count($ValorUnitario); $i++) {
			if ($TipoItem[$i] != "B") {
				if ((str_replace(",",".",$ValorUnitario[$i]) == 0 or $ValorUnitario[$i] == "") and ! SoNumeros($ValorUnitario[$i]) and $Existe == "") {
					$Existe  = "S";
					$Posicao = $i;
				}
			}
		}
		
		if ($Existe == "S") {
			if ($Mens == 1) {
				$Mensagem .= ", ";
			}

			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "<a href=\"javascript:document.getElementsByName('ValorUnitario[$i]').item(0).focus();\" class=\"titulo2\">Valor Unitário</a>";
		}
		
		# Verifica se os valores só são numeros e decimais #
		if ($Existe == "") {
			$Posicao = "";
			
			for ($k=0; $k<count($ValorUnitario); $k++) {
				if ($TipoItem[$k] != "B") {
					if ((!SoNumVirg($ValorUnitario[$k])) and ($Existe == "")) {
						$Existe  = "S";
						$Posicao = $k;
					}
				}
			}
			
			if ($Existe == "") {
				for ($j=0; $j<count($ValorUnitario); $j++) {
					if ($TipoItem[$j] != "B") {
						if ((!DecimalValor($ValorUnitario[$j])) and $Existe == "") {
							$Existe  = "S";
							$Posicao = $j;
						}
					}
				}
			}
			
			if ($Existe == "S") {
				if ($Mens == 1) {
					$Mensagem .= ", ";
				}
							
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.getElementsByName('ValorUnitario[$i]').item(0).focus();\" class=\"titulo2\">Valor Unitário Válido</a>";
			}
		}
	}
} elseif ($Botao == "RetirarEmpenho") {
	if (count($Empenhos) != 0) {
		for ($i=0; $i< count($Empenhos); $i++) {
			if ($CheckEmp[$i] == "") {
				$Qtd++;
				$CheckEmp[$i]     = "";
				$Empenhos[$Qtd-1] = $Empenhos[$i];
			}
		}
		
		if (count($Empenhos) > 1) {
			$Empenhos = array_slice($Empenhos,0,$Qtd);
		} else {
			unset($Empenhos);
		}
	}
}

if ($Botao == "") {
	# Verificar aqui se é a primeira vez q entra neste programa #
	if ($InicioPrograma == "") {
		unset($_SESSION['item']);
	}
	
	if ($_SESSION['_cgrempcodi_'] != 0) {
		# Verifica se o Usuário está ligado a algum centro de Custo #
		$db = Conexao();
				
		$sql  = "SELECT USUCEN.CUSUPOCODI ";
		$sql .= "  FROM SFPC.TBUSUARIOCENTROCUSTO USUCEN, SFPC.TBCENTROCUSTOPORTAL CENCUS, ";
		$sql .= "       SFPC.TBGRUPOEMPRESA GRUEMP,SFPC.TBORGAOLICITANTE ORGSOL,SFPC.TBUSUARIOPORTAL USUPOR ";
		$sql .= " WHERE USUCEN.CGREMPCODI <> 0 AND USUCEN.CCENPOSEQU = CENCUS.CCENPOSEQU AND USUCEN.FUSUCCTIPO IN ('T','R') ";
		$sql .= "   AND USUCEN.CGREMPCODI = GRUEMP.CGREMPCODI ";
		$sql .= "   AND CENCUS.CORGLICODI = ORGSOL.CORGLICODI ";
		$sql .= "   AND USUCEN.CUSUPOCODI = USUPOR.CUSUPOCODI  ";
		$sql .= "   AND USUCEN.CGREMPCODI = ".$_SESSION['_cgrempcodi_']." ";
		$sql .= "   AND USUCEN.CUSUPOCODI = ".$_SESSION['_cusupocodi_']." ";
		$sql .= "   AND CENCUS.FCENPOSITU <> 'I' "; // Inclusão da condição para mostrar centro de custos diferentes de inativos
		$sql .= " ORDER BY GRUEMP.EGREMPDESC, ORGSOL.EORGLIDESC, CENCUS.ECENPODESC, USUPOR.EUSUPORESP ";
		
		$res  = $db->query($sql);
		
		if (PEAR::isError($res)) {
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		} else {
			$Rows = $res->numRows();
		
			if ($Rows == 0) {
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "O Usuário não está ligado a nenhum Centro de Custo";
			}
		}

		# Carrega o Tipo do Usuário e Orgão Solicitante do GrupoEmpresa/Usuário Logado #
		$sql  = "SELECT USUCEN.FUSUCCTIPO,CENCUS.CORGLICODI ";
		$sql .= "  FROM SFPC.TBUSUARIOCENTROCUSTO USUCEN, SFPC.TBCENTROCUSTOPORTAL CENCUS ";
		$sql .= " WHERE USUCEN.CCENPOSEQU = CENCUS.CCENPOSEQU AND USUCEN.FUSUCCTIPO IN ('T','R')  ";
		$sql .= "   AND ( ( USUCEN.CUSUPOCODI = ".$_SESSION['_cusupocodi_']." AND ";
		$sql .= "           USUCEN.CGREMPCODI = ".$_SESSION['_cgrempcodi_']." ) OR ";
		$sql .= "         ( USUCEN.CUSUPOCOD1 = ".$_SESSION['_cusupocodi_']." AND ";
		$sql .= "           USUCEN.CGREMPCOD1 = ".$_SESSION['_cgrempcodi_']." AND ";
		$sql .= "           '$DataAtual' BETWEEN DUSUCCINIS AND DUSUCCFIMS ) ";
		$sql .= "       ) AND USUCEN.FUSUCCTIPO = 'T' ";
		$sql .= "   AND CENCUS.FCENPOSITU <> 'I' "; // Inclusão da condição para mostrar centro de custos diferentes de inativos
		
		$res = $db->query($sql);
		
		if (PEAR::isError($res)) {
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		} else {
			$Rows = $res->numRows();
			
			if ($Rows != 0) {
				$Linha        = $res->fetchRow();
				$TipoUsuario  = $Linha[0];
				$OrgaoUsuario = $Linha[1];
				if($TipoUsuario == "R") {
					$DescUsuario = "Requisitante";
				} elseif ($TipoUsuario == "A") {
					$DescUsuario = "Aprovador";
				} else {
					$DescUsuario = "Atendimento";
				}
			} else {
				$TipoUsuario  = "";
				$OrgaoUsuario = "";
			}
		}

		# Carrega os dados do usuário logado #
		$sql  = "SELECT EUSUPORESP FROM SFPC.TBUSUARIOPORTAL ";
		$sql .= " WHERE CGREMPCODI = ".$_SESSION['_cgrempcodi_']." ";
		$sql .= "   AND CUSUPOCODI = ".$_SESSION['_cusupocodi_']."";
		
		$res = $db->query($sql);
		
		if (PEAR::isError($res)) {
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		} else {
			$Linha = $res->fetchRow();
			$Nome  = $Linha[0];
		}
		
		$db->disconnect();

		# Deixa requisição como default
		if (is_null($DataRequisicao)) {
			$DataRequisicao= date('d/m/Y');
		}
	} else {
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "O Usuário do grupo INTERNET não pode fazer Inclusão de Nota Fiscal";
	}
}

# Monta o array de itens da NF #
if (count($_SESSION['item']) != 0) {
	sort($_SESSION['item']);
	
	if ($ItemNotaFiscal == "") {
		for ($i=0; $i<count($_SESSION['item']); $i++) {
			$ItemNotaFiscal[count($ItemNotaFiscal)] = $_SESSION['item'][$i];
		}
	} else {
		for ($i=0; $i<count($ItemNotaFiscal); $i++) {
			$DadosItem            = explode($SimboloConcatenacaoArray,$ItemNotaFiscal[$i]);
			$SequencialItem[$i]   = $DadosItem[1];
		}
		
		for ($i=0; $i<count($_SESSION['item']); $i++) {
			$DadosSessao          = explode($SimboloConcatenacaoArray,$_SESSION['item'][$i]);
			$SequencialSessao[$i] = $DadosSessao[1];
			
			if (!in_array($SequencialSessao[$i],$SequencialItem)) {
				$ItemNotaFiscal[count($ItemNotaFiscal)] = $_SESSION['item'][$i];
			}
		}
	}
	unset($_SESSION['item']);
}
?>

<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="">
	<!--
	function enviar(valor){
		document.CadNotaFiscalMaterialIncluir.Botao.value = valor;
		document.CadNotaFiscalMaterialIncluir.submit();
	}
	function AbreJanela(url,largura,altura){
		window.open(url,'paginadetalhe','status=no,scrollbars=yes,left=90,top=150,width='+largura+',height='+altura);
	}
	function AbreJanelaItem(url,largura,altura){
		window.open(url,'paginaitem','status=no,scrollbars=yes,left=90,top=150,width='+largura+',height='+altura);
	}
	function AbreJanelaEmp(url,largura,altura){
		window.open(url,'paginaemp','status=no,scrollbars=yes,left=90,top=150,width='+largura+',height='+altura);
	}
	<?php MenuAcesso(); ?>
	//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form action="CadNotaFiscalMaterialIncluir.php" method="post" name="CadNotaFiscalMaterialIncluir">
		<br><br><br><br><br>
		<table cellpadding="3" border="0" summary="">
			<!-- Caminho -->
			<tr>
				<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
				<td align="left" class="textonormal" colspan="5">
					<font class="titulo2">|</font>
					<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Nota Fiscal > Incluir
				</td>
			</tr>
			<!-- Fim do Caminho-->
			<!-- Erro -->
			<?php if ($Mens == 1) { ?>
				<tr>
					<td width="100"></td>
					<td align="left" colspan="5"><?php ExibeMens($Mensagem,$Tipo,$Virgula); ?></td>
				</tr>
			<?php } ?>
			<!-- Fim do Erro -->
			<!-- Corpo -->
			<tr>
				<td width="100"></td>
				<td class="textonormal">
					<table border="0" cellspacing="0" cellpadding="3" width="100%" summary="">
						<tr>
							<td class="textonormal">
								<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" width="100%" bgcolor="#FFFFFF" summary="">
									<tr>
										<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="5">
											INCLUIR - NOTA FISCAL
										</td>
									</tr>
									<tr>
										<td class="textonormal" colspan="5">
											<p align="justify">
												Para incluir uma nova Nota Fiscal, informe os dados abaixo e clique no botão "Incluir Nota Fiscal". Os itens obrigatórios estão com *.
											</p>
										</td>
									</tr>
									<tr>
										<td>
											<table class="textonormal" border="0" align="left" width="100%" summary="">
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Almoxarifado</td>
													<td class="textonormal">
														<?php
														# Mostra o(s) Almoxarifado(s) de Acordo com o Usuário Logado #
														$db = Conexao();

														if ($_SESSION['_cgrempcodi_'] == 0) {
															$sql = "SELECT A.CALMPOCODI, A.EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL A ";

																if ($Almoxarifado) {
																	$sql .= "WHERE A.CALMPOCODI = $Almoxarifado AND A.FALMPOSITU = 'A' ";
																}
														} else {
															$sql  = "SELECT A.CALMPOCODI, A.EALMPODESC ";
															$sql .= "FROM SFPC.TBALMOXARIFADOPORTAL A, SFPC.TBALMOXARIFADOORGAO B , SFPC.TBLOCALIZACAOMATERIAL C ";
                            								$sql .= "LEFT OUTER JOIN (SELECT * FROM SFPC.TBINVENTARIOCONTAGEM WHERE (CLOCMACODI, AINVCOANOB, AINVCOSEQU) IN ( SELECT A.CLOCMACODI, A.AINVCOANOB, MAX(A.AINVCOSEQU) AS AINVCOSEQU FROM SFPC.TBINVENTARIOCONTAGEM A WHERE (A.FINVCOFECH = 'S') AND (A.CLOCMACODI, A.AINVCOANOB) IN ( SELECT CLOCMACODI, MAX(AINVCOANOB) FROM SFPC.TBINVENTARIOCONTAGEM GROUP BY CLOCMACODI) GROUP BY A.CLOCMACODI, A.AINVCOANOB ) ) AS D ";
															$sql .= "ON C.CLOCMACODI = D.CLOCMACODI ";
															$sql .= "WHERE A.CALMPOCODI = C.CALMPOCODI AND A.CALMPOCODI = B.CALMPOCODI ";

																if ($Almoxarifado) {
																	$sql .= "AND A.CALMPOCODI = $Almoxarifado AND A.FALMPOSITU = 'A' ";
																}

															$sql .= "AND B.CORGLICODI in ";
															$sql .= "(SELECT DISTINCT CEN.CORGLICODI ";
															$sql .= "FROM SFPC.TBCENTROCUSTOPORTAL CEN, SFPC.TBUSUARIOCENTROCUSTO USU ";
															$sql .= "WHERE USU.CCENPOSEQU = CEN.CCENPOSEQU AND USU.CUSUPOCODI = ". $_SESSION['_cusupocodi_']." AND CEN.FCENPOSITU <> 'I' AND USU.FUSUCCTIPO IN ('T','R') AND CASE WHEN USU.FUSUCCTIPO = 'T' THEN B.CALMPOCODI = USU.CALMPOCODI ELSE CEN.FCENPOSITU <> 'I' END) ";

															# Trecho com relação ao bloqueio no inventário #
															$sql .= "AND (( TRUE ";
															
															# Trecho com bloqueio quando inventário está aberto #
															
															# DESCOMENTAR DEPOIS (01-2014)
															/* 
															$sql .= "   AND CASE WHEN ('".date("Y-m-d")."'>='".$InventarioDataInicial."') THEN ";
															# Para que inventário seja feito no período determinado, sem passar da data final definida, descomentar a linha abaixo e comentar a posterior #
															# $sql .= "            (A.FALMPOINVE = 'N' OR A.FALMPOINVE IS NULL) AND D.TINVCOFECH >= '".$InventarioDataInicial."' AND D.TINVCOFECH <= '".$InventarioDataFinal."' ";
															$sql .= "            (A.FALMPOINVE = 'N' OR A.FALMPOINVE IS NULL) AND D.TINVCOFECH >= '".$InventarioDataInicial."' ";
															$sql .= "       ELSE ";
															$sql .= "            (A.FALMPOINVE = 'N' OR A.FALMPOINVE IS NULL) ";
															$sql .= "        END ";
															*/ 
															# FIM DESCOMENTAR  

															# Trecho com relação ao período de inventário obrigatório #
															$sql .= "AND ( ";
															$sql .= "TO_DATE('".date('Y-m-d')."','YYYY-MM-DD') < TO_DATE('".$InventarioDataInicial."','YYYY-MM-DD') ";
															$sql .= "OR TO_DATE('".date('Y-m-d')."','YYYY-MM-DD') > TO_DATE('".$InventarioDataFinal."','YYYY-MM-DD') ";
															$sql .= ") ";
															$sql .= ") ";
															
															# Linha para permitir movimentações de um determinado órgão
															
															# COMENTAR DEPOIS (01-2014)
															#  $sql .= "				OR B.CORGLICODI IN (10, 6, 39)";
															# FIM COMENTAR

															// [CUSTOMIZAÇÃO] - Nova condição para liberar órgãos dentro do período de bloqueio do sistema.
															//					As variáveis $InventarioDataInicial e $InventarioDataFinal são alimentadas
															//					com os valores definidos nos parâmetros do sistema conforme CR 212.
															
															$dataAtual = date('Y-m-d') . ' 23:59:59';
															
															$sql .= "OR B.CORGLICODI IN ( ";
															$sql .= "SELECT CORGLICODI ";
															$sql .= "FROM SFPC.TBLIBERACAOMOVIMENTACAO ";
															$sql .= "WHERE ";
															$sql .= "TLIBMODINI BETWEEN '" . $InventarioDataInicial . "' AND '" . $dataAtual . "' ";
															$sql .= "OR ";
															$sql .= "TLIBMODFIN BETWEEN '" . $dataAtual . "' AND '" . $InventarioDataFinal . "' ";
															$sql .= "GROUP BY CORGLICODI ";
															$sql .= "ORDER BY CORGLICODI ";
															$sql .= ") ";
															
															// [/CUSTOMIZAÇÃO]
															
															$sql .= ") ";
														}

														$sql .= " ORDER BY A.EALMPODESC ";
												 
														$res  = $db->query($sql);

														if (PEAR::isError($res)) {
															ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														} else {
															$Rows = $res->numRows();
														
															if ($Rows == 1) {
																$Linha = $res->fetchRow();			
																$Almoxarifado     = $Linha[0];
																$DescAlmoxarifado = $Linha[1];

																echo "$DescAlmoxarifado<br>";
																echo "<input type=\"hidden\" name=\"Almoxarifado\" value=\"$Almoxarifado\">";
															} elseif ($Rows > 1) {
																echo "<select name=\"Almoxarifado\" class=\"textonormal\" onChange=\"submit();\">\n";
																echo "	<option value=\"\">Selecione um Almoxarifado...</option>\n";
															
																for ($i=0; $i< $Rows; $i++) {
																	$Linha = $res->fetchRow();
																	$DescAlmoxarifado = $Linha[1];

																	if ($Linha[0] == $Almoxarifado) {
																		echo"<option value=\"$Linha[0]\" selected>$DescAlmoxarifado</option>\n";
																	} else {
																		echo"<option value=\"$Linha[0]\">$DescAlmoxarifado</option>\n";
																	}
																}
																echo "</select>\n";
																$CarregaAlmoxarifado = "";
															} else {
																echo "ALMOXARIFADO NÃO CADASTRADO, INATIVO OU SOB INVENTÁRIO";
																echo "<input type=\"hidden\" name=\"CarregaAlmoxarifado\" value=\"N\">";
															}
														}
														$db->disconnect();
														?>
													</td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Localização*</td>
													<td class="textonormal">
														<?php
														$db = Conexao();
														
														if (($Localizacao != "") and ($Almoxarifado)) {
															# Mostra a Descrição de Acordo com o Almoxarifado #
															$sql  = "SELECT A.FLOCMAEQUI, A.ALOCMANEQU, A.ALOCMAPRAT, A.ALOCMACOLU, B.EARLOCDESC ";
															$sql .= "FROM SFPC.TBLOCALIZACAOMATERIAL A, SFPC.TBAREAALMOXARIFADO B ";
															$sql .= "WHERE A.CLOCMACODI = $Localizacao AND A.FLOCMASITU = 'A' ";
															$sql .= "AND A.CARLOCCODI = B.CARLOCCODI ";
															
															$res  = $db->query($sql);
														
															if (PEAR::isError($res)) {
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
															} else {
																$Linha = $res->fetchRow();
															
																if ($Linha[0] == "E") {
																	$Equipamento = "ESTANTE";
																}
																
																if ($Linha[0] == "A") {
																	$Equipamento = "ARMÁRIO";
																}	
																
																if ($Linha[0] == "P") {
																	$Equipamento = "PALETE";
																}
															
																$DescArea = $Linha[4];
																echo "ÁREA: $DescArea - $Equipamento - $Linha[1]: ESCANINHO $Linha[2]$Linha[3]";
																echo "<input type=\"hidden\" name=\"Localizacao\" value=\"$Localizacao\">";
															}
														} elseif($Almoxarifado) {
															# Mostra as Localizações de acordo com o Almoxarifado #
															$sql  = "SELECT A.CLOCMACODI, A.FLOCMAEQUI, A.ALOCMANEQU, ";
															$sql .= "A.ALOCMAPRAT, A.ALOCMACOLU, B.EARLOCDESC ";
															$sql .= "FROM SFPC.TBLOCALIZACAOMATERIAL A, SFPC.TBAREAALMOXARIFADO B ";
															$sql .= "WHERE A.CALMPOCODI = $Almoxarifado AND A.FLOCMASITU = 'A' ";
															$sql .= "AND A.CARLOCCODI = B.CARLOCCODI ";
															$sql .= "ORDER BY B.EARLOCDESC DESC, A.FLOCMAEQUI, A.ALOCMANEQU, ";
															$sql .= "A.ALOCMAPRAT, A.ALOCMACOLU ";
														
															$res  = $db->query($sql);
															
															if (PEAR::isError($res)) {
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
															} else {
																$Rows = $res->numRows();
																
																if ($Rows == 0) {
																	echo "NENHUMA LOCALIZAÇÃO CADASTRADA PARA ESTE ALMOXARIFADO";
																	echo "<input type=\"hidden\" name=\"CarregaLocalizacao\" value=\"N\">";
																} else {
																	if ($Rows == 1) {
																		$Linha = $res->fetchRow();
																		
																		if ($Linha[1] == "E") {
																			$Equipamento = "ESTANTE";
																		}
																		
																		if ($Linha[1] == "A") {
																			$Equipamento = "ARMÁRIO";
																		}
																		
																		if ($Linha[1] == "P") {
																			$Equipamento = "PALETE";
																		}
																		
																		echo "ÁREA: $Linha[5] - $Equipamento - $Linha[2]: ESCANINHO $Linha[3]$Linha[4]";
																		$Localizacao = $Linha[0];
																		echo "<input type=\"hidden\" name=\"Localizacao\" value=\"$Localizacao\">";
																	} else {
																		if ($Rows == 1) {
																			$Linha = $res->fetchRow();
																			
																			if ($Linha[1] == "E") {
																				$Equipamento = "ESTANTE";
																			}
																			
																			if ($Linha[1] == "A") {
																				$Equipamento = "ARMÁRIO";
																			}
																			
																			if ($Linha[1] == "P") {
																				$Equipamento = "PALETE";
																			}
																			
																			echo "ÁREA: $Linha[5] - $Equipamento - $Linha[2]: ESCANINHO $Linha[3]$Linha[4]";
																			$Localizacao = $Linha[0];
																			echo "<input type=\"hidden\" name=\"Localizacao\" value=\"$Localizacao\">";
																		} else {
																			echo "<select name=\"Localizacao\" class=\"textonormal\" onChange=\"submit();\">\n";
																			echo "	<option value=\"\">Selecione uma Localização...</option>\n";
																		
																			$EquipamentoAntes = "";
																			$DescAreaAntes    = "";
																		
																			for ($i=0; $i< $Rows; $i++) {
																				$Linha = $res->fetchRow();
																				$CodEquipamento = $Linha[2];
																			
																				if ($Linha[1] == "E") {
																					$Equipamento = "ESTANTE";
																				}
																				
																				if ($Linha[1] == "A") {
																					$Equipamento = "ARMÁRIO";
																				}
																				
																				if ($Linha[1] == "P") {
																					$Equipamento = "PALETE";
																				}
																			
																				$NumeroEquip = $Linha[2];
																				$Prateleira  = $Linha[3];
																				$Coluna      = $Linha[4];
																				$DescArea    = $Linha[5];
																			
																				if ($DescAreaAntes != $DescArea) {
																					echo"<option value=\"\">$DescArea</option>\n";
																					$Edentecao = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
																				}
																				
																				if($CodEquipamentoAntes != $CodEquipamento or $EquipamentoAntes != $Equipamento){
																					echo"<option value=\"\">$Edentecao $Equipamento - $NumeroEquip</option>\n";
																				}
																				
																				if($Localizacao == $Linha[0]){
																					echo"<option value=\"$Linha[0]\" selected>$Edentecao $Edentecao ESCANINHO $Prateleira$Coluna</option>\n";
																				} else {
																					echo"<option value=\"$Linha[0]\">$Edentecao $Edentecao ESCANINHO $Prateleira$Coluna</option>\n";
																				}
																			
																				$DescAreaAntes       = $DescArea;
																				$CodEquipamentoAntes = $CodEquipamento;
																				$EquipamentoAntes    = $Equipamento;
																			}
																			echo "</select>\n";
																			$CarregaLocalizacao = "";
																		}
																	}
																}
															}
														} else {
															echo "<input type=\"hidden\" name=\"CarregaLocalizacao\" value=\"N\">";
														}
														$db->disconnect();
														?>
													</td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Número da Nota*</td>
													<td class="textonormal">
														<input type="text" name="NumeroNota" size="15" maxlength="10" value="<?php echo $NumeroNota; ?>" class="textonormal">
													</td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Série da Nota*</td>
													<td class="textonormal">
														<input type="text" name="SerieNota" size="10" maxlength="8" value="<?php echo $SerieNota; ?>" class="textonormal">
													</td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Data da Emissão*</td>
													<td class="textonormal">
														<?php $URL = "../calendario.php?Formulario=CadNotaFiscalMaterialIncluir&Campo=DataEmissao";?>
														<input type="text" name="DataEmissao" size="10" maxlength="10" value="<?php echo $DataEmissao?>" class="textonormal">
														<a href="javascript:janela('<?php echo $URL ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
														<font class="textonormal">dd/mm/aaaa</font>
													</td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" width="30%">
														<input type="radio" name="CNPJ_CPF" value="1" <?php if( $CNPJ_CPF == 1 ){ echo "checked"; }?>>CNPJ*
														<input type="radio" name="CNPJ_CPF" value="2" <?php if( $CNPJ_CPF == 2 ){ echo "checked"; }?>>CPF*
													</td>
													<td class="textonormal">
														<input type="text" name="CnpjCpf" size="15" maxlength="14" value="<?php echo $CnpjCpf; ?>" class="textonormal">
														<a href="javascript:enviar('Verificar');"><img src="../midia/lupa.gif" border="0"></a>
													</td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Razão Social</td>
													<td class="textonormal">
														<font class="textonormal"><?php echo $RazaoSocial; ?></font>
													</td>
												</tr>
                								<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Valor Total da Nota Fiscal</td>
													<td class="textonormal">
                        								<input type="text" name="ValorTotalNota" size="20" value="<?php echo $ValorTotalNota; ?>" class="textonormal">
													</td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Valor Total dos itens da Nota Fiscal</td>
													<td class="textonormal">
														<?php
														$ValorNota = 0;
														
														$contaQuantidade = (is_null($Quantidade)) ? $Quantidade = 0: count($Quantidade);

														for ($i=0; $i < $contaQuantidade; $i++) {
															$decQuantidade    = str_replace(",",".",$Quantidade[$i]);
															$decValorUnitario = str_replace(",",".",str_replace(".","",$ValorUnitario[$i]));
															$decValorTotal    = str_replace(",",".",($decQuantidade * $decValorUnitario));
															$ValorTotal[$i]   = str_replace(",",".",$decValorTotal);
															$ValorNota        = str_replace(",",".",($ValorNota + $ValorTotal[$i]));
														}
														
														echo converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorNota)));
														?>
														
														<input type="hidden" name="ValorNota" value="<?php if($ValorNota == ""){ echo 0; }else{ echo converte_valor_estoques(sprintf('%01.4f',str_replace(",",".",$ValorNota))); } ?>" class="textonormal">
													</td>
												</tr>
												<tr>
													<?php 
													$db = Conexao();
											
													if (!empty($Almoxarifado)) {

													} else {
														$Almoxarifado = 0;
													}

													$sqlEstVir = "SELECT FALMPOESTV FROM SFPC.TBALMOXARIFADOPORTAL WHERE CALMPOCODI = $Almoxarifado";
											
													$resEstVir  = $db->query($sqlEstVir);
													
													if (PEAR::isError($resEstVir)) {
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlEstVir");
													} else {
														$Linhas = $resEstVir->fetchRow();
														$AlmoxVirtual  = $Linhas[0];
													}
													
													$db->disconnect();

													if ($_SESSION['_cperficodi_'] == 2 || $_SESSION['_cperficodi_'] == 6 || $AlmoxVirtual == 'S') {
													?>
                      								
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Estoque Virtual</td>
                      								<td>
                        								<input type="checkbox" name="EstoqueVirtual" value="S" <?php if($EstoqueVirtual == 'S'){ echo " checked "; }?> onClick="javascript:enviar('');"/>
													</td>
													
													<?php
													}
													?>
                    							</tr>
												<?php
												if ($EstoqueVirtual == 'S') {
												?>
													<tr>
														<td class="textonormal" colspan="5">
															<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" width="100%" bgcolor="#FFFFFF" summary="">
																<tr>
																	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
																		REQUISIÇÃO DE MATERIAL ATENDIDO PELA NOTA
																	</td>
																</tr>
																<tr>
																	<td>
																		<table class="textonormal" border="0" align="left" width="100%" summary="">
																			<tr>
																				<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Ano</td>
																				<td class="textonormal"><?php echo date("Y"); ?></td>
																			</tr>
																			<tr>
																				<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Centro de Custo*</td>
																				<td class="textonormal">
																					<?php
																					# Exibe os Centro de Custo #
																					$db = Conexao();

																					if (($_SESSION['_cgrempcodi_'] != 0 ) and ($TipoUsuario == "R")) {
																						$sqlCC  = "SELECT A.CCENPOSEQU, A.ECENPODESC, A.CCENPONRPA, A.ECENPODETA, B.CORGLICODI, B.EORGLIDESC ";
																						$sqlCC .= "FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBORGAOLICITANTE B ";
																						$sqlCC .= "WHERE A.CORGLICODI IS NOT NULL AND A.ACENPOANOE = ".date("Y")." ";
																						$sqlCC .= "AND A.CORGLICODI = B.CORGLICODI ";
																						$sqlCC .= "AND A.FCENPOSITU <> 'I' "; // Inclusão da condição para mostrar centro de custos diferentes de inativos
																						$sqlCC .= "AND A.CCENPOSEQU IN ";
																						$sqlCC .= "(SELECT USU.CCENPOSEQU FROM SFPC.TBUSUARIOCENTROCUSTO USU ";
																						$sqlCC .= "WHERE USU.CUSUPOCODI = ". $_SESSION['_cusupocodi_'] ." AND USU.FUSUCCTIPO IN ('T','R')) ";
																						$sqlCC .= "ORDER BY B.EORGLIDESC, A.CCENPONRPA, A.ECENPODESC, A.CCENPOCENT, A.CCENPODETA ";
																					} else {
																						$sqlCC  = "SELECT A.CCENPOSEQU, A.ECENPODESC, A.CCENPONRPA, A.ECENPODETA, D.CORGLICODI, D.EORGLIDESC ";
																						$sqlCC .= "FROM SFPC.TBCENTROCUSTOPORTAL A,  SFPC.TBGRUPOORGAO B, SFPC.TBGRUPOEMPRESA C, SFPC.TBORGAOLICITANTE D ";
																						$sqlCC .= "WHERE A.CORGLICODI IS NOT NULL AND A.ACENPOANOE = ".date("Y")." ";
																						$sqlCC .= "AND A.CORGLICODI = B.CORGLICODI AND C.CGREMPCODI = B.CGREMPCODI ";
																						$sqlCC .= "AND B.CORGLICODI = D.CORGLICODI ";
																						$sqlCC .= "AND A.FCENPOSITU <> 'I' "; // Inclusão da condição para mostrar centro de custos diferentes de inativos

																						if ($TipoUsuario == "T") {
																							$sqlCC .= "AND C.CGREMPCODI = ".$_SESSION['_cgrempcodi_']." ";
																						}

																						$sqlCC   .= "ORDER BY D.EORGLIDESC,A.CCENPONRPA, A.CCENPOCENT, A.CCENPODETA ";
																					}

																					$resCC = $db->query($sqlCC);

																					if (PEAR::isError($resCC)) {
																						$CodErroEmail  = $resCC->getCode();
																						$DescErroEmail = $resCC->getMessage();
																						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlCC\n\n$DescErroEmail ($CodErroEmail)");
																					} else {
																						$RowsCC = $resCC->numRows();

																						if ($RowsCC == 0) {
																							echo "Nenhum Centro de Custo cadastrado";
																						} elseif ($RowsCC == 1) {
																							$Linha           = $resCC->fetchRow();
																							$CentroCusto     = $Linha[0];
																							$DescCentroCusto = $Linha[1];
																							$RPA             = $Linha[2];
																							$Detalhamento    = $Linha[3];
																							$Orgao           = $Linha[4];
																							$DescOrgao       = $Linha[5];
																							echo $DescOrgao."<br>&nbsp;&nbsp;&nbsp;&nbsp;";
																							echo "RPA ".$RPA."<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
																							echo $DescCentroCusto."<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
																							echo $Detalhamento;
																						} else {
																							$Url = "CadIncluirCentroCusto.php?ProgramaOrigem=CadNotaFiscalMaterialIncluir&TipoUsuario=$TipoUsuario";

																							if (!in_array($Url,$_SESSION['GetUrl'])) {
																								$_SESSION['GetUrl'][] = $Url;
																							}

																							echo "<a href=\"javascript:AbreJanela('$Url',700,370);\"><img src=\"../midia/lupa.gif\" border=\"0\"></a><br>\n";

																							if ($CentroCusto != "") {
																								# Carrega os dados do Centro de Custo selecionado #
																								$sql  = "SELECT A.ECENPODESC, B.EORGLIDESC, A.CORGLICODI, A.CCENPONRPA, A.ECENPODETA ";
																								$sql .= "FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBORGAOLICITANTE B ";
																								$sql .= "WHERE A.CORGLICODI = B.CORGLICODI AND A.CCENPOSEQU = $CentroCusto ";
																								$sql .= "AND A.FCENPOSITU <> 'I' "; // Inclusão da condição para mostrar centro de custos diferentes de inativos

																								$res  = $db->query($sql);

																								if (PEAR::isError($res)) {
																									$CodErroEmail  = $res->getCode();
																									$DescErroEmail = $res->getMessage();
																									ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
																								} else {
																									while ($Linha = $res->fetchRow()) {
																										$DescCentroCusto = $Linha[0];
																										$DescOrgao       = $Linha[1];
																										$Orgao           = $Linha[2];
																										$RPA             = $Linha[3];
																										$Detalhamento    = $Linha[4];
																									}

																									echo $DescOrgao."<br>&nbsp;&nbsp;&nbsp;&nbsp;";
																									echo "RPA ".$RPA."<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
																									echo $DescCentroCusto."<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
																									echo $Detalhamento;
																								}
																							}
																						}
																					}
																					$db->disconnect();
																					?>
																				</td>
																				<input type="hidden" name="CentroCusto" value="<?php echo $CentroCusto; ?>">
																			</tr>
																			<tr>
																				<td class="textonormal" bgcolor="#DCEDF7">Data da Requisição*</td>
																				<td class="textonormal">
																					<?php $URL = "../calendario.php?Formulario=CadNotaFiscalMaterialIncluir&Campo=DataRequisicao";?>
																					<input type="text" name="DataRequisicao" size="10" maxlength="10" value="<?php echo $DataRequisicao?>" class="textonormal">
																					<a href="javascript:janela('<?php echo $URL ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
																					<font class="textonormal">dd/mm/aaaa</font>
																				</td>
																			</tr>
																			<tr>
																				<td class="textonormal" bgcolor="#DCEDF7">Observação</td>
																				<td class="textonormal">
																					<font class="textonormal">máximo de 200 caracteres</font>
																					<input type="text" name="NCaracteresO" size="3" value="<?php echo $NCaracteresO ?>" OnFocus="javascript:document.CadNotaFiscalMaterialIncluir.Observacao.focus();" class="textonormal"><br>
																					<textarea name="Observacao" cols="50" rows="4" OnKeyUp="javascript:ncaracteresO(1)" OnBlur="javascript:ncaracteresO(0)" OnSelect="javascript:ncaracteresO(1)" class="textonormal"><?php echo $Observacao; ?></textarea>
																				</td>
																			</tr>
																		</table>
																	</td>
																</tr>
															</table>
														</td>
													</tr>
												<?php
												}
												?>
												<tr>
													<td class="textonormal" colspan="5">
														<table border="1" cellpadding="3" cellspacing="0" bgcolor="#bfdaf2" bordercolor="#75ADE6" width="100%" summary="">
															<tr>
																<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="6">
																	ITENS
																</td>
															</tr>
															<?php
															$contItemNotaFiscal = is_null($ItemNotaFiscal)?$ItemNotaFiscal=0:count($ItemNotaFiscal);
															if ($contItemNotaFiscal != 0) {
																sort($ItemNotaFiscal);
															}
														
															for ($i=0; $i< $contItemNotaFiscal; $i++) {
																$Dados                 = explode($SimboloConcatenacaoArray,$ItemNotaFiscal[$i]);
																$DescMaterial[$i]      = $Dados[0];
																$Material[$i]          = $Dados[1];
																$Unidade[$i]           = $Dados[2];
                              									$SituacaoMaterial[$i]  = $Dados[3];
																$Quantidade[$i]        = $Dados[4];
																$ValorUnitario[$i]     = $Dados[5];

																if ($Quantidade[$i] === (string) "M") {
																	$Quantidade[$i] = '';
																}

																# Variaveis para calculo de valores #
																$decQuantidade    = str_replace(",",".",$Quantidade[$i]);
																$decValorUnitario = str_replace(",",".",$ValorUnitario[$i]);
																$decValorTotal    = str_replace(",",".",($decQuantidade * $decValorUnitario));
																$ValorTotal[$i]   = str_replace(",",".",$decValorTotal);
	
																if ($i == 0) {
																	echo "		<tr>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" width=\"70%\">DESCRIÇÃO DO MATERIAL</td>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" width=\"10%\">CÓD.RED.</td>\n";
                                  									echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" width=\"10%\">UNIDADE</td>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" width=\"10%\" align=\"center\">QUANTIDADE</td>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" width=\"10%\">VALOR UNITÁRIO</td>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" width=\"10%\">VALOR TOTAL</td>\n";
																	echo "		</tr>\n";
																}
																?>
																<tr>
																	<td class="textonormal" width="60%">
																		<input type="hidden" name="ItemNotaFiscal[<?php echo $i; ?>]" value="<?php echo $ItemNotaFiscal[$i]; ?>">
																		<input type="checkbox" name="CheckItem[<?php echo $i; ?>]" value="<?php echo $i; ?>">
																			<?php
																			$Url = "CadItemDetalhe.php?ProgramaOrigem=$ProgramaOrigem&Material=$Material[$i]";
																		
																			if (!in_array($Url,$_SESSION['GetUrl'])) {
																				$_SESSION['GetUrl'][] = $Url;
																			}
																			?>
																		
																			<a href="javascript:AbreJanela('<?=$Url;?>',700,370);">
																				<font color="#000000">
																		
																					<?php
																					$Descricao = explode($SimboloConcatenacaoDesc,$DescMaterial[$i]);
																					echo $Descricao[1];
																					?>
																		
																				</font>
																			</a>
																		<input type="hidden" name="DescMaterial[<?php echo $i; ?>]" value="<?php echo $DescMaterial[$i]; ?>">
                              											<input type="hidden" name="SituacaoMaterial[<?php echo $i; ?>]" value="<?php echo $SituacaoMaterial[$i]; ?>">
																	</td>
                            										<td class="textonormal" width="10%" align="center">
																		<div id="codMaterial<?php echo $i; ?>"><?php echo $Material[$i];?></div>
                              											<input type="hidden" name="Material[<?php echo $i; ?>]" value="<?php echo $Material[$i]; ?>">
																	</td>
																	<td class="textonormal" width="10%" align="center">
																		<?php
																		echo $Unidade[$i];
																		?>
																	
																		<input type="hidden" name="Unidade[<?php echo $i; ?>]" value="<?php echo $Unidade[$i]; ?>">
																	</td>
																	<td class="textonormal" align="center" width="10%">
																		<input type="text" name="Quantidade[<?php echo $i; ?>]" size="10" maxlength="10" value="<?php echo str_replace(".",",",$Quantidade[$i]); ?>" class="textonormal">
																	</td>
																	<td class="textonormal" align="center" width="10%">
																		<input type="text" name="ValorUnitario[<?php echo $i; ?>]" size="10" maxlength="10" value="<?php echo str_replace(".",",",$ValorUnitario[$i]); ?>" class="textonormal">
																	</td>
																	<td class="textonormal" align="center" width="10%">
																		<?php
																		if ($ValorTotal[$i] == "") {
																			echo 0;
																		} else {
																			echo converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorTotal[$i])));
																		}
																		?>
																	
																		<input type="hidden" name="ValorTotal[<?php echo $i; ?>]" size="10" maxlength="10" value="<?php echo $ValorTotal[$i]; ?>" class="textonormal">
																	</td>
																</tr>
															<?php
															}
															?>
															<tr>
																<td class="textonormal" colspan="6" align="center">
																	<?php
																	if ($Almoxarifado) {
																		$Url = "CadIncluirItem.php?ProgramaOrigem=$ProgramaOrigem&Almoxarifado=$Almoxarifado";
																		echo "<input type=\"button\" name=\"IncluirItem\" value=\"Incluir Item\" class=\"botao\" onclick=\"javascript:AbreJanelaItem('$Url',700,350);\">\n";
																		echo "<input type=\"button\" name=\"Retirar\" value=\"Retirar Item\" class=\"botao\" onClick=\"javascript:enviar('Retirar');\">\n";
																		echo "<input type=\"button\" name=\"Totalizar\" value=\"Totalizar\" class=\"botao\" onClick=\"javascript:enviar('Totalizar');\">\n";
																	} else {
																		$Url = "CadIncluirItem.php?ProgramaOrigem=$ProgramaOrigem&Almoxarifado=$Almoxarifado";
																		echo "<input type=\"button\" name=\"IncluirItem\" value=\"Incluir Item\" class=\"botao\" onclick=\"javascript:AbreJanelaItem('$Url',700,350);\" disabled>\n";
																		echo "<input type=\"button\" name=\"Retirar\" value=\"Retirar Item\" class=\"botao\" onClick=\"javascript:enviar('Retirar');\"  disabled>\n";
																		echo "<input type=\"button\" name=\"Totalizar\" value=\"Totalizar\" class=\"botao\" onClick=\"javascript:enviar('Totalizar');\" disabled>\n";
																	}
																		
																	if (!in_array($Url,$_SESSION['GetUrl'])) {
																		$_SESSION['GetUrl'][] = $Url;
																	}
																	?>
																</td>
															</tr>
														</table>
													</td>
												</tr>
												<tr>
													<td class="textonormal" colspan="5">
														<table border="1" cellpadding="3" cellspacing="0" bgcolor="#bfdaf2" bordercolor="#75ADE6" width="100%" summary="">
															<tr>
																<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="6">
																	EMPENHOS
																</td>
															</tr>
															<?php
															# Exibe os empenhos #
															$contEmpenho = is_null($Empenhos)?$Empenhos=0:count($Empenhos);
															for ($i=0; $i< $contEmpenho; $i++) {
																# Imprime o cabeçalho se for a primeira execução #
																if ($i == 0) {
																	echo "		<tr>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" width=\"5%\">&nbsp;</td>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" width=\"25%\">Ano</td>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" width=\"20%\">Órgão</td>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" width=\"20%\">Unidade</td>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" width=\"30%\">Sequencial</td>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" width=\"10%\">Parcela</td>\n";
																	echo "		</tr>\n";
																}
																  
																# Separa Ano, Órgão, Unidade, Sequencial e Parcela #
																$Emp = explode(".",$Empenhos[$i]);  
																$AnoEmp         = $Emp[2];
																$OrgaoEmp       = $Emp[3];
																$UnidadeEmp     = $Emp[4];
																$SequencialEmp  = $Emp[5];
																$ParcelaEmp     = $Emp[6];
															?>
																<tr>
																	<td class="textonormal" align="center" width="10%">
																		<input type="checkbox" name="CheckEmp[<?php echo $i; ?>]" value="<?php echo $i; ?>">
																		<?php
																		# Guarda informações de empenho para o próximo post #
																		echo "<input type=\"hidden\" name=\"Empenhos[$i]\" value=\"$Empenhos[$i]\">";
																		?>
																	</td>
																	<td class="textonormal" align="center" width="10%">
																		<?php
																		echo $AnoEmp;
																		?>
																	</td>
																	<td class="textonormal" align="center" width="10%">
																		<?php
																		echo $OrgaoEmp;
																		?>
																	</td>
																	<td class="textonormal" align="center" width="10%">
																		<?php
																		echo $UnidadeEmp;
																		?>
																	</td>
																	<td class="textonormal" align="center" width="10%">
																		<?php
																		echo $SequencialEmp;
																		?>
																	</td>
																	<td class="textonormal" align="center" width="10%">
																		<?php
																		if ($ParcelaEmp != 0) {
																			echo $ParcelaEmp;
																		} else {
																			echo "&nbsp;";
																		}
																		?>
																	</td>
																</tr>
															<?php
															}
															?>
															<tr>
																<td class="textonormal" colspan="6" align="center">
																	<?php
																	if ($Almoxarifado) {
																		# Botões habilitados #
																		$Url = "CadIncluirEmpenho.php?ProgramaOrigem=$ProgramaOrigem";
																		echo "<input type=\"button\" name=\"IncluirEmpenho\" value=\"Incluir Empenho\" class=\"botao\" onclick=\"javascript:AbreJanelaEmp('$Url',700,320);\">\n";
																		echo "<input type=\"button\" name=\"RetirarEmpenho\" value=\"Retirar Empenho\" class=\"botao\" onClick=\"javascript:enviar('RetirarEmpenho');\">\n";
																	} else {
																		# Botões desabilitados #
																		$Url = "CadIncluirEmpenho.php?ProgramaOrigem=$ProgramaOrigem";
																		echo "<input type=\"button\" name=\"IncluirEmpenho\" value=\"Incluir Empenho\" class=\"botao\" onclick=\"javascript:AbreJanelaEmp('$Url',700,320);\" disabled>\n";
																		echo "<input type=\"button\" name=\"RetirarEmpenho\" value=\"Retirar Empenho\" class=\"botao\" onClick=\"javascript:enviar('RetirarEmpenho');\"  disabled>\n";
																	}
																	
																	if (!in_array($Url,$_SESSION['GetUrl'])) {
																		$_SESSION['GetUrl'][] = $Url;
																	}
																	?>
																</td>
															</tr>
														</table>
													</td>
												</tr>
											</table>
										</td>
									</tr>
									<tr>
										<td class="textonormal" align="right">
											<?php
											# Modificação, na qual o usuário não digita mais a data de entrada da NF #
											$DataEntrada = date("d/m/Y");
											echo "<input type=\"hidden\" name=\"DataEntrada\" value=\"$DataEntrada\">";
											?>
											<input type="hidden" name="InicioPrograma" value="1">
											<input type="hidden" name="Totalizou" value="">
											<input type="hidden" name="ValorNota" value="<?php echo $ValorNota; ?>">
											<input type="hidden" name="TipoUsuario" value="<?php echo $TipoUsuario; ?>">
											<input type="hidden" name="OrgaoUsuario" value="<?php echo $OrgaoUsuario; ?>">
											<input type="hidden" name="RazaoSocial" value="<?php echo $RazaoSocial; ?>">
											<input type="button" name="Incluir" value="Incluir Nota Fiscal" class="botao" onClick="javascript:enviar('Incluir');">
											<input type="hidden" name="Botao" value="">
					                		<!--TESTE-->
											<input type="hidden" name="Matricula" value="">
    	              						<!--TESTE-->
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