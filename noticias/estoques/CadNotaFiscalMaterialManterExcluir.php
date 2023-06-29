<?php
# -------------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadNotaFiscalMaterialManterExcluir.php
# Objetivo: Programa de Exclusão de Itens na Nota Fiscal a partir da Pesquisa
# Data:     13/09/2005
# Autor:    Altamiro Pedrosa
# OBS.:     Tabulação 2 espaços
#           Deixar os comentário pra validação de empenho obrigatório
# -------------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     08/02/2006 - Regras para exclusão de itens
# -------------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     26/05/2006 - Regras de exclusão de itens para as novas movimentações
# -------------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     08/06/2006 - Passagem do parâmentro ano da nota para a pesquisa de movimentações
#                        Data de entrada pelo sistema, não mais pelo usuário
# -------------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     27/07/2006 - Permitir mais de um empenho
# -------------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     06/11/2006 - Correção para não chutar o usuário após alteração de nota fiscal
# -------------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     14/12/2006 - strtoupper no número de série da nota
# -------------------------------------------------------------------------------
# Alterado: Carlos Abreu
# Data:     16/01/2007 - Correção para inserir AnoNota em vez de AnoMovimentacao no Ano da Nota
#                        fiscal da tabela de movimentacao
# -------------------------------------------------------------------------------
# Alterado: Carlos Abreu
# Data:     17/01/2007 - Correção para nos select desbloquear ano e checar movimentacoes por chave completa e
#                        nao por codigo da movimentacao
# -------------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     02/02/2007 - Relatório de auxílio para Manutenção de Nota Fiscal
# -------------------------------------------------------------------------------
# Alterado: Carlos Abreu
# Data:     22/03/2007 - Ajuste para restringir o ano de emissao entre o ano atual e o ano anterior
# -------------------------------------------------------------------------------
# Alterado: Carlos Abreu
# Data:     19/04/2007 - Inclusao de bloqueio para evitar movimentacoes anteriores a ultimo inventario
# -------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     10/01/2008 - Ajuste na query para evitar que a movimentação seja realizada no período anterior ao último inventário
#                                do almoxarifado, ou seja, ajuste para buscar apenas o último sequencial e o último ano do inventário do almoxarifado.
# -------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     21/01/2008 - Ajuste na query da validação da movimentação, pois, deve-se permitir a alteração da nota fiscal com base na data e hora da última
#                                 atualização da movimentação (entrada da nota fiscal) com a data e hora do fechamento do último inventário.
#                                 E não levar em consideração a data de emissão da nota fiscal, como estava anteriormente.
# -------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     19/03/2008 - Correção para obter os dados corretos do empenho.
# -------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     27/03/2008 - Correção para exibir critica para não permitir que o usuário inclua mais de um empenho com no mesmo ano e com os mesmo orgãos, unidades e sequencial para a mesma nota fiscal do almoxarifado no ano de exercicío.
# -------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     07/04/2008 - Correção para não permitir que materiais inativos sejam inseridos na base de dados.
# -------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:      11/06/2008 - Alteração para informar o valor total da nota fiscal, para comparar com o valor total dos materiais.
# -------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     03/07/2008 - Correção para não excluir todos o itens da nota fiscal e salvar a alteração.
# -------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:      07/07/2008 - Alteração para inserir no campo estoque virtual na tabela de armazenamento de material e flag para identificar uma nota fiscal Virtual.
# -------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:      11/07/2008 - Alteração para o obter empenhos válidos, ou seja, não nulos e que não sejam subempenhos. Além de obter o valor do empenho - valor anulado do empenho, caso este seja > 0.
# -------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:      30/07/2008 - Correção da critica em que o "Valor Total da Nota Fiscal" deve ser igual ao Valor Total dos Itens da Nota Fiscal.
# -------------------------------------------------------------------------------
# Alterado: Ariston Cordeiro / Rodrigo Melo
# Data:     30/07/2008 	- Alteração para obter empenhos / Subempenhos válidos para alterar uma nota fiscal (empenho/subempenho não anulado completamente)
# -------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:      01/08/2008 - Correção para calcular o valor dos empenhos apenas de notas fiscais não canceladas.
# -------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:      12/08/2008 - alteração para que possa entrar com valores de notas fiscais com uma diferença de até R$ 2,00 do valor do empenho/subempenho.
# -------------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:      13/08/2008 - Arredondamento de total de valor dos itens da nota fiscal para 4 dígitos fracionários para comparação com o total de nota fiscal informado pelo usuario
# -------------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:      11/09/2008 - Removido todos acessos a SFPC.TBFORNECEDORESTOQUE
# -------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:      13/11/2008 - Correção para obter o valor da última movimentação para permitir desfazer movimentações, como por exemplo: Cancelamento de nota fiscal
# -------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:      27/11/2008 - Corrigindo Programa para permitir entrada de empenhos com sequenciais diferentes, ou seja, diferentes subempenhos.
#                         				Foi convecionado que um empenho com parcela igual a zero não será um subempenho, mas um empenho pois a chave primária da tabela foi
#                                  alterada para incluir a parcela do subempenho. Desta forma quando a parcela tiver o valor 0 será um empenho e se tiver um valor
#                                  diferente de 0 será um subempenho.
# -------------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:      27/01/2009	- Mudando para checagem de aviso que nota fiscal não tem itens para ser igual a checagem ao iniciar a alteração
# 	      				- Verificando se nota fiscal está sendo recebida via POST ou GET
# 	      				- Corrigindo bug que só verificava CNPJ do fornecedor, dando erro quando o fornecedor é pessoa física
# -------------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:     06/04/2009 - Nova movimentação: "saída por processo administrativo" (37)
# -------------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:     08/09/2009 - Mudando variáveis GET do RelAuxilioCancelamentoNotaPdf.php. Ao invés de enviar o Ulat e
#           período, agora é enviado a nota fiscal a ser cancelada. Necessário para modificações no relatório
# -------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		24/10/2018
# Objetivo: Tarefa Redmine 73662
# -------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/CadItemDetalhe.php' );
AddMenuAcesso( '/estoques/CadIncluirEmpenho.php' );
AddMenuAcesso( '/estoques/RelAuxilioCancelamentoNotaPdf.php' );

/*
   Função responsável por verificar se existe uma data maior do que o as datas de um array e retorna null caso a data de
   comparação não seja maior do que nenhuma data do array, caso haja alguma data, a função retorna os indices do array.
*/
function comparaDataMaior($data, $arrayData){
  for($i = 0; $i < count($arrayData); $i++){
    if($data > $arrayData[$i]){
      $DatasMaiores[count($DatasMaiores)] = $i;
    }
  }
  return $DatasMaiores;
}

$ProgramaOrigem="CadNotaFiscalMaterialManterExcluir";

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao             = $_POST['Botao'];
		$InicioPrograma    = $_POST['InicioPrograma'];
		$Localizacao       = $_POST['Localizacao'];
		$CarregaLocalizacao = $_POST['CarregaLocalizacao'];
		$Almoxarifado      = $_POST['Almoxarifado'];
		$AnoNota           = $_POST['AnoNota'];
		$NotaFiscal        = $_POST['NotaFiscal'];
		$CNPJ_CPF          = $_POST['CNPJ_CPF'];
		if($_POST['CnpjCpf'] != ""){
				if($CNPJ_CPF == 2){
						$CnpjCpf  = substr("00000000000".$_POST['CnpjCpf'],-11);    // CPF
				}else{
						$CnpjCpf  = substr("00000000000000".$_POST['CnpjCpf'],-14); // CNPJ
				}
		}else{
				$CnpjCpf = $_POST['CnpjCpf'];
		}
		$NumeroNota        = $_POST['NumeroNota'];
		$SerieNota         = strtoupper2(RetiraAcentos($_POST['SerieNota']));
		$NotaAnterior      = $_POST['NotaAnterior'];
		$FornecedorAnterior= $_POST['FornecedorAnterior'];
		$DataEntrada       = $_POST['DataEntrada'];
		$DataEmissao       = $_POST['DataEmissao'];
		if( $DataEmissao  != "" ){ $DataEmissao = FormataData($DataEmissao); }
    $DataUltimaAlteracao = $_POST['DataUltimaAlteracao'];
		$ValorNota         = $_POST['ValorNota'];
		$ValNota           = $_POST['ValNota'];
		$CheckItem       	 = $_POST['CheckItem'];
		$Material          = $_POST['Material'];
		$DescMaterial      = $_POST['DescMaterial'];
		$Unidade           = $_POST['Unidade'];
    $SituacaoMaterial  = $_POST['SituacaoMaterial'];
		$Quantidade        = $_POST['Quantidade'];
		$ValorUnitario     = $_POST['ValorUnitario'];
		$ValorTotal        = $_POST['ValorTotal'];
    $ValorTotalNota    = $_POST['ValorTotalNota'];
    $EstoqueVirtual    = $_POST['EstoqueVirtual'];
		$RazaoSocial   		 = $_POST['RazaoSocial'];
		$DataHora          = $_POST['DataHora'];
		$Empenhos          = $_POST['Empenhos'];
		$CheckEmp          = $_POST['CheckEmp'];
		$Montou            = $_POST['Montou'];
		for($i=0; $i<count($DescMaterial); $i++){
				//$ItemNotaFiscal[$i] = $DescMaterial[$i].$SimboloConcatenacaoArray.$Material[$i].$SimboloConcatenacaoArray.$Unidade[$i].$SimboloConcatenacaoArray.$Quantidade[$i].$SimboloConcatenacaoArray.$ValorUnitario[$i].$SimboloConcatenacaoArray.$DataHora[$i];
        $ItemNotaFiscal[$i] = $DescMaterial[$i].$SimboloConcatenacaoArray.$Material[$i].$SimboloConcatenacaoArray.$Unidade[$i].$SimboloConcatenacaoArray.$SituacaoMaterial[$i].$SimboloConcatenacaoArray.str_replace(".","",$Quantidade[$i]).$SimboloConcatenacaoArray.str_replace(".","",$ValorUnitario[$i]).$SimboloConcatenacaoArray.$DataHora[$i];
		}
}else{
		$Almoxarifado      = $_GET['Almoxarifado'];
		$AnoNota           = $_GET['AnoNota'];
		$NotaFiscal        = $_GET['NotaFiscal'];
		$Mens              = $_GET['Mens'];
		$Tipo              = $_GET['Tipo'];
		$Mensagem          = urldecode($_GET['Mensagem']);
		$Botao             = $_GET['Botao'];
    $InicioPrograma    = $_GET['InicioPrograma'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Ano do Exercicio #
$AnoExercicio = date("Y");
$DataAtual    = date("Y-m-d");

# Caso haja informações de empenho na variável de sessão, traz para a nota e apaga a variável #
if($_SESSION['Empenho']){
		if (!$Empenhos){ $Empenhos = array(); }
		if(!in_array($_SESSION['Empenho'],$Empenhos)){
				$Empenhos[] = $_SESSION['Empenho'];
		}
		unset($_SESSION['Empenho']);
}

if( (is_null($Almoxarifado)) or (is_null($AnoNota)) or (is_null($NotaFiscal)) ){
		header("location: /portalcompras/estoques/CadNotaFiscalMaterialManter.php");
		exit;
}

if( $Botao == "Voltar" ){
		header("location: CadNotaFiscalMaterialManter.php");
		exit;
}
if( (!$Botao) and (!$Montou) ){
		if ($InicioPrograma == '') {
		  unset($_SESSION['item']);
			unset($_SESSION['ItemDelete']);
			LimparSessaoNotaFiscal();
		}
		# Pega os dados da Entrada por NF de acordo com o Sequencial #
		$db   = Conexao();
		$sql  = "SELECT A.AENTNFNOTA, A.AENTNFSERI, A.DENTNFENTR, ";
		$sql .= "       A.DENTNFEMIS, A.VENTNFTOTA, ";
		$sql .= "       B.AITENFQTDE, B.VITENFUNIT, ";
		$sql .= "       C.CMATEPSEQU, C.EMATEPDESC, D.EUNIDMSIGL, ";
		$sql .= "       A.AFORCRSEQU, A.CFORESCODI, B.TITENFULAT, A.TENTNFULAT, C.CMATEPSITU, A.FENTNFVIRT  ";
		$sql .= "  FROM SFPC.TBENTRADANOTAFISCAL A, SFPC.TBITEMNOTAFISCAL B, ";
		$sql .= "       SFPC.TBMATERIALPORTAL C, SFPC.TBUNIDADEDEMEDIDA D ";
		$sql .= " WHERE A.CENTNFCODI = B.CENTNFCODI AND B.CMATEPSEQU = C.CMATEPSEQU ";
		$sql .= "   AND A.CALMPOCODI = B.CALMPOCODI AND A.CENTNFCODI = B.CENTNFCODI ";
		$sql .= "   AND A.AENTNFANOE = B.AENTNFANOE AND C.CUNIDMCODI = D.CUNIDMCODI ";
		$sql .= "   AND A.CALMPOCODI = $Almoxarifado ";
		$sql .= "   AND A.AENTNFANOE = $AnoNota ";
		$sql .= "   AND A.CENTNFCODI = $NotaFiscal ";
		$sql .= " ORDER BY A.AENTNFNOTA, C.EMATEPDESC ";
		$res  = $db->query($sql);
		if( db::isError($res) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Rows = $res->numRows();
				for($i=0; $i<$Rows; $i++){
						$Linha            	= $res->fetchRow();
						$NumeroNota       	= $Linha[0];
						$SerieNota        	= $Linha[1];
						$DataEntrada      	= DataBarra($Linha[2]);
						$DataEmissao      	= DataBarra($Linha[3]);
						$ValNota      	    = str_replace(",",".",$Linha[4]);
            $Quantidade[$i]     = str_replace(".",",",$Linha[5]);
            $ValorUnitario[$i]  = str_replace(".",",",$Linha[6]);
						$Material[$i]       = $Linha[7];
						$DescMaterial[$i]   = RetiraAcentos($Linha[8]).$SimboloConcatenacaoDesc.str_replace("\"","”",$Linha[8]);
						$Unidade[$i]        = $Linha[9];
						$FornecedorSequ 	  = $Linha[10];
						$FornecedorCodi 	  = $Linha[11];
						$DataHora[$i]       = $Linha[12];
            $DataUltimaAlteracao = $Linha[13];
            $SituacaoMaterial[$i] = $Linha[14];
            $EstoqueVirtual      = $Linha[15];
						#$ItemNotaFiscal[$i] = $DescMaterial[$i].$SimboloConcatenacaoArray.$Material[$i].$SimboloConcatenacaoArray.$Unidade[$i].$SimboloConcatenacaoArray.$Quantidade[$i].$SimboloConcatenacaoArray.$ValorUnitario[$i].$SimboloConcatenacaoArray.$DataHora[$i];
            $ItemNotaFiscal[$i] = $DescMaterial[$i].$SimboloConcatenacaoArray.$Material[$i].$SimboloConcatenacaoArray.$Unidade[$i].$SimboloConcatenacaoArray.$SituacaoMaterial[$i].$SimboloConcatenacaoArray.$Quantidade[$i].$SimboloConcatenacaoArray.$ValorUnitario[$i].$SimboloConcatenacaoArray.$DataHora[$i];
						$Montou             = "S";
						$NotaAnterior       = $NumeroNota."-".$SerieNota;
						if($FornecedorSequ){
								$FornecedorAnterior = $FornecedorSequ;
						}else{
								$FornecedorAnterior = $FornecedorCodi;
						}
				}

				# Recupera dados dos empenhos #
				$sqlemp  = "SELECT ANFEMPANEM, CNFEMPOREM, CNFEMPUNEM, ";
				$sqlemp .= "       CNFEMPSEEM, CNFEMPPAEM ";
				$sqlemp .= "  FROM SFPC.TBNOTAFISCALEMPENHO ";
				$sqlemp .= " WHERE CALMPOCODI = $Almoxarifado ";
				$sqlemp .= "   AND AENTNFANOE = $AnoNota ";
				$sqlemp .= "   AND CENTNFCODI = $NotaFiscal ";
				$resemp  = $db->query($sqlemp);
				if(db::isError($resemp)){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlemp");
				}else{
            # Abre a Conexão com Oracle #
            $dbora  = ConexaoOracle();

						while($LinhaEmp = $resemp->fetchRow()){
								$AnoEmp        = $LinhaEmp[0];
								$OrgaoEmp      = $LinhaEmp[1];
								$UnidadeEmp    = $LinhaEmp[2];
								$SequencialEmp = $LinhaEmp[3];
								$ParcelaEmp    = $LinhaEmp[4];

                # Monta a Query para obter o valor e a data de emissão do empenho.

                /*
                $sql = "SELECT ";

                if ($ParcelaEmp != null && trim($ParcelaEmp) != '') {
                  $sql .= " TO_CHAR(SUB.DSBEMPEMIS, 'YYYY-MM-DD HH24:MI:SS'), "; // PARA SUBEMPENHO
                } else {
                  $sql .= " TO_CHAR(EMP.DEMPENEMIS, 'YYYY-MM-DD HH24:MI:SS'), "; //PARA EMPENHO
                }

                if ($ParcelaEmp != null && trim($ParcelaEmp) != '') {
                  $sql .= " (NVL(SUB.VSBEMPSUBE,0) - (NVL(SUB.VSBEMPANUL,0) + NVL(SUB.VSBEMPPAGO,0))) AS VALOR "; //VALOR DO SUBEMPENHO
                } else {
                  $sql .= " (NVL(EMP.VEMPENEMPE,0) - (NVL(EMP.VEMPENANUL,0) + NVL(EMP.VEMPENPAGO,0))) AS VALOR "; //VALOR DO EMPENHO
                }

                $sql .= " FROM SFCO.TBTIPOEMPENHO TIP, SFCO.TBEMPENHO EMP ";

                if ($ParcelaEmp != null && trim($ParcelaEmp) != '') {
                  $sql .= "   , SFCO.TBSUBEMPENHO SUB ";
                }

                $sql .= " WHERE EMP.DEMPENANOO = $AnoEmp ";
                $sql .= " AND EMP.CORGORCODI = $OrgaoEmp ";
                $sql .= " AND EMP.CUNDORCODI = $UnidadeEmp ";
                $sql .= " AND EMP.AEMPENSEQU = $SequencialEmp ";

                if ($ParcelaEmp != null && trim($ParcelaEmp) != '') {
                  $sql .= " AND SUB.ASBEMPSEQU = $ParcelaEmp ";

                  $sql .= " AND EMP.DEMPENANOO = SUB.DEMPENANOO ";
                  $sql .= " AND EMP.AEMPENNUME = SUB.AEMPENNUME ";
                  $sql .= " AND EMP.CORGORCODI = SUB.CORGORCODI ";
                  $sql .= " AND EMP.CUNDORCODI = SUB.CUNDORCODI ";
                  $sql .= " AND EMP.AEMPENSEQU = SUB.AEMPENSEQU ";
                }

                if ($ParcelaEmp != null && trim($ParcelaEmp) != '') {
                  $sql .= " AND (NVL(SUB.VSBEMPSUBE,0) - (NVL(SUB.VSBEMPANUL,0) + NVL(SUB.VSBEMPPAGO,0))) > 0 "; //PARA SUBEMPENHO
                } else {
                  $sql .= " AND (NVL(EMP.VEMPENEMPE,0) - (NVL(EMP.VEMPENANUL,0) + NVL(EMP.VEMPENPAGO,0))) > 0 "; //PARA EMPENHO
                }

                $sql .= " AND EMP.CTPEMPCODI = TIP.CTPEMPCODI ";

                if ($ParcelaEmp != null && trim($ParcelaEmp) != '') {
                  $sql .= " AND TIP.FTPEMPSUEM = 'S' "; //PARA SUBEMPENHO
                } else {
                  $sql .= " AND TIP.FTPEMPSUEM = 'N' "; //PARA EMPENHO
                }
                */


                $sql = "SELECT ";

                if (($ParcelaEmp != null && trim($ParcelaEmp) != '') && $ParcelaEmp != 0) {
                  $sql .= " TO_CHAR(SUB.DSBEMPEMIS, 'YYYY-MM-DD HH24:MI:SS'), "; // PARA SUBEMPENHO
                  $sql .= " ( NVL(SUB.VSBEMPSUBE,0) - NVL(SUB.VSBEMPANUL,0) ) AS VALOR "; //VALOR DO SUBEMPENHO
                } else {
                  $sql .= " TO_CHAR(EMP.DEMPENEMIS, 'YYYY-MM-DD HH24:MI:SS'), "; //PARA EMPENHO
                	$sql .= " ( NVL(EMP.VEMPENEMPE,0) - NVL(EMP.VEMPENANUL,0) ) AS VALOR "; //VALOR DO EMPENHO
                }

                $sql .= " FROM SFCO.TBTIPOEMPENHO TIP, SFCO.TBEMPENHO EMP ";

                if (($ParcelaEmp != null && trim($ParcelaEmp) != '') && $ParcelaEmp != 0) {
                  $sql .= "   , SFCO.TBSUBEMPENHO SUB ";
                }

                $sql .= " WHERE EMP.DEMPENANOO = $AnoEmp ";
                $sql .= " AND EMP.CORGORCODI = $OrgaoEmp ";
                $sql .= " AND EMP.CUNDORCODI = $UnidadeEmp ";
                $sql .= " AND EMP.AEMPENSEQU = $SequencialEmp ";
                $sql .= " AND EMP.CTPEMPCODI = TIP.CTPEMPCODI ";

                # verifica se recebe parcela  (se é subempenho)
                # Foi convecionado que um empenho com parcela igual a zero não será um subempenho, mas um empenho pois a chave primária da tabela foi alterada
                # para incluir a parcela do subempenho. Desta forma quando a parcela tiver o valor 0 será um empenho e se tiver um valor diferente de 0 será um subempenho.
                if (($ParcelaEmp != null && trim($ParcelaEmp) != '') && $ParcelaEmp != 0) {
                  $sql .= " AND SUB.ASBEMPSEQU = $ParcelaEmp ";
                  $sql .= " AND EMP.DEMPENANOO = SUB.DEMPENANOO ";
                  $sql .= " AND EMP.AEMPENNUME = SUB.AEMPENNUME ";
                  $sql .= " AND EMP.CORGORCODI = SUB.CORGORCODI ";
                  $sql .= " AND EMP.CUNDORCODI = SUB.CUNDORCODI ";
                  $sql .= " AND EMP.AEMPENSEQU = SUB.AEMPENSEQU ";
                  $sql .= " AND TIP.FTPEMPSUEM = 'S' "; //PARA SUBEMPENHO

                	# caso seja uma alteração (CadNotaFiscalMaterialManterIncluir ou CadNotaFiscalMaterialManterExcluir) o valor total do subempenho não pode estar anulado
                	$sql .= " AND ( NVL(SUB.VSBEMPSUBE,0) - NVL(SUB.VSBEMPANUL,0) ) > 0 "; //PARA SUBEMPENHO

                } else{

                	if($ProgramaOrigem=="CadNotaFiscalMaterialIncluir"){
                		# Caso seja entrada de nota fiscal, o valor total do empenho não deve estar nem pago nem anulado
                		$sql .= " AND (NVL(EMP.VEMPENEMPE,0) - (NVL(EMP.VEMPENANUL,0) + NVL(EMP.VEMPENPAGO,0))) > 0 "; //PARA EMPENHO
                	}else{
                		# caso seja uma alteração (CadNotaFiscalMaterialManterIncluir ou CadNotaFiscalMaterialManterExcluir) o valor total do empenho não pode estar anulado
                		$sql .= " AND ( NVL(EMP.VEMPENEMPE,0) - NVL(EMP.VEMPENANUL,0) ) > 0 "; //PARA EMPENHO
                	}

                  $sql .= " AND TIP.FTPEMPSUEM = 'N' "; //PARA EMPENHO
                }

                # Roda a Query
                $res  = $dbora->query($sql);

                if( db::isError($res) ){
                		$dbora->disconnect();
                		ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                		exit;
                }else{
                  $row  = $res->fetchRow();
                  $DataEmissaoEmp = $row[0];
                  $ValorEmp = $row[1];
                  $ValorEmp = str_replace(".",",",$ValorEmp);

                  //Colocar os empenhos sempre desta forma e remover as demais, pois a parcela sempre será informada
                  $Empenhos[] = "$ValorEmp.$DataEmissaoEmp.$AnoEmp.$OrgaoEmp.$UnidadeEmp.$SequencialEmp.$ParcelaEmp";
								}
						}

            # Fecha a Conexão com Oracle #
            $dbora->disconnect();
				}

				if( $FornecedorSequ != "" ){
						# Verifica se o Fornecedor de Estoque é Credenciado #
						$sqlforn  = "SELECT NFORCRRAZS, AFORCRCCGC, AFORCRCCPF FROM SFPC.TBFORNECEDORCREDENCIADO ";
						$sqlforn .= " WHERE AFORCRSEQU = $FornecedorSequ ";
						$resforn  = $db->query($sqlforn);
						if( db::isError($resforn) ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlforn");
						}else{
								$Linhaforn   = $resforn->fetchRow();
								$RazaoSocial = $Linhaforn[0];
								if( $Linhaforn[1] != "" ){
										$CnpjCpf  = $Linhaforn[1];
										$CNPJ_CPF = 1;
								}else{
										$CnpjCpf  = $Linhaforn[2];
										$CNPJ_CPF = 2;
								}
						}
				}else{/*
						# Verifica se o Fornecedor de Estoque já está cadastrado #
						$sqlforn  = "SELECT EFORESRAZS, AFORESCCGC, AFORESCCPF FROM SFPC.TBFORNECEDORESTOQUE ";
						$sqlforn .= "	WHERE CFORESCODI = $FornecedorCodi ";
						$resforn  = $db->query($sqlforn);
						if( db::isError($resforn) ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlforn");
						}else{
								$Linhaforn   = $resforn->fetchRow();
								$RazaoSocial = $Linhaforn[0];
								$CnpjCpf     = $Linhaforn[1];
								if( $Linhaforn[1] != "" ){
										$CnpjCpf  = $Linhaforn[1];
										$CNPJ_CPF = 1;
								}else{
										$CnpjCpf  = $Linhaforn[2];
										$CNPJ_CPF = 2;
								}
						}*/
														EmailErro(__FILE__."- Fornecedor não encontrado.", __FILE__, __LINE__, "Fornecedor informado não foi encontrado em SFPC.TBFORNECEDORCREDENCIADO.\n\nSequencial do fornecedor informado: '".$FornecedorSequ."'\n\nVerificar se o dado informado pelo sistema foi correto ou se há algum fornecedor que não foi migrado de SFPC.TBFORNECEDORESTOQUE para SFPC.TBFORNECEDORCREDENCIADO corretamente.");
				}
		}
		$db->disconnect();
}

# Critica para caso tente realizar operacao com data anterior a data de inventario
$db   = Conexao();
$sql  = "SELECT COUNT(B.TINVCOFECH) ";
$sql .= "  FROM SFPC.TBINVENTARIOCONTAGEM B ";
$sql .= " INNER JOIN SFPC.TBLOCALIZACAOMATERIAL C ";
$sql .= "    ON B.CLOCMACODI = C.CLOCMACODI ";
$sql .= "   AND C.CALMPOCODI = $Almoxarifado ";
$sql .= " WHERE (B.AINVCOANOB,B.AINVCOSEQU) = ";
$sql .= "       (SELECT MAX(A.AINVCOANOB),MAX(A.AINVCOSEQU) ";
$sql .= "          FROM SFPC.TBINVENTARIOCONTAGEM A ";
$sql .= "         WHERE A.AINVCOANOB = ";
$sql .= "               (SELECT MAX(AINVCOANOB) ";
$sql .= "                  FROM SFPC.TBINVENTARIOCONTAGEM  ";
$sql .= "                 WHERE A.AINVCOANOB = AINVCOANOB ) ";
$sql .= "           AND A.CLOCMACODI = B.CLOCMACODI) ";
//$DataCritica = explode("/",$DataEmissao);
//$sql .= "   AND B.TINVCOFECH < '".$DataCritica[2]."-".$DataCritica[1]."-".$DataCritica[0]."'";
$sql .= "   AND B.TINVCOFECH < '".$DataUltimaAlteracao."'";
$res  = $db->query($sql);
if( db::isError($res) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}else{
		$Linha = $res->fetchRow();
		if ($Linha[0]==0){
			$Mens     = 1;
			$Tipo     = 2;
			$Mensagem = "Nota Fiscal não pode ser alterada pois possui referência a período anterior ao último inventário do almoxarifado";
			$Botao = "";
		}
}
$db->disconnect();

if($Botao == "Verificar"){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if($CNPJ_CPF == ""){
				if($Mens == 1){ $Mensagem.=", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "A opção CNPJ ou CPF";
		}else{
				if($CNPJ_CPF == 1){ $TipoDocumento = "CNPJ"; }else{ $TipoDocumento = "CPF"; }
				if($CnpjCpf == ""){
						if( $Mens == 1 ){ $Mensagem.=", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialManterExcluir.CnpjCpf.focus();\" class=\"titulo2\">$TipoDocumento</a>";
				}else{
						if($CNPJ_CPF == 1){
								$valida_cnpj = valida_CNPJ($CnpjCpf);
								if($valida_cnpj === false ){
										$Mens      = 1;
										$Tipo      = 2;
										$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialManterExcluir.CnpjCpf.focus();\" class=\"titulo2\">CNPJ Válido</a>";
								}
						}else{
								$valida_cpf = valida_CPF($CnpjCpf);
								if($valida_cpf === false){
										$Mens      = 1;
										$Tipo      = 2;
										$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialManterExcluir.CnpjCpf.focus();\" class=\"titulo2\">CPF Válido</a>";
								}
						}
				}
				if( ( $CNPJ_CPF == 1 and $valida_cnpj === true ) or ( $CNPJ_CPF == 2 and $valida_cpf === true )  ){
						$db   = Conexao();
						# Verifica se o CNPJ Existe no cadastro de Fornecedores Credenciados #
						$sql  = "SELECT NFORCRRAZS FROM SFPC.TBFORNECEDORCREDENCIADO ";
						$sql .= " WHERE ";
						if( $CNPJ_CPF == 1 ){
								$sql .= " AFORCRCCGC = '$CnpjCpf' ";
						}else{
								$sql .= " AFORCRCCPF = '$CnpjCpf' ";
						}
						$res  = $db->query($sql);
						if( db::isError($res) ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								$rows = $res->numRows();
								if( $rows > 0 ){
										$linha       = $res->fetchRow();
										$RazaoSocial = $linha[0];
								}else{/*
										# Verifica se o Fornecedor de Estoque já está cadastrado #
										$db   = Conexao();
										$sql  = "SELECT EFORESRAZS FROM SFPC.TBFORNECEDORESTOQUE ";
										$sql .= "	WHERE ";
										if( $CNPJ_CPF == 1 ){
												$sql .= "	AFORESCCGC = '$CnpjCpf' ";
										}else{
											  $sql .= "	AFORESCCPF = '$CnpjCpf' ";
										}
										$res  = $db->query($sql);
										if( db::isError($res) ){
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												$rows = $res->numRows();
												if ($rows > 0){
														$linha       = $res->fetchRow();
														$RazaoSocial = $linha[0];
												}else{
														if( $Mens == 1 ){ $Mensagem.=", "; }
														$Mens     = 1;
														$Tipo     = 1;
														$Mensagem = "Fornecedor Não Cadastrado";
												}
										}
										$db->disconnect();*/
										if( $Mens == 1 ){ $Mensagem.=", "; }
										$Mens     = 1;
										$Tipo     = 1;
										$Mensagem = "Fornecedor Não Cadastrado";
								}
						}
				}
		}
}

if($Botao == "Retirar"){

    $Mens     = 0;
		$Mensagem = "Informe: ";

		if( ($Localizacao == "") && ($CarregaLocalizacao == 'N') ){
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Localização";
		}elseif($Localizacao == ""){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialManterExcluir.Localizacao.focus();\" class=\"titulo2\">Localização</a>";
		}else{
				if( count($ItemNotaFiscal) != 0 ){
						$db = Conexao();
						for( $i=0; $i< count($ItemNotaFiscal); $i++ ){

								# Verifica se com a retirada a quantidade e o valor ficaram negativos #
								if( $CheckItem[$i] != "" ){
										# Resgata os dados em armazenamentomaterial do material corrente #
										$sqlarmat  = "SELECT AARMATQTDE FROM SFPC.TBARMAZENAMENTOMATERIAL ";
										$sqlarmat .= " WHERE CMATEPSEQU = $Material[$i] AND CLOCMACODI = $Localizacao ";
										$resarmat  = $db->query($sqlarmat);
										if( db::isError($resarmat) ){
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlarmat");
										}else{
												$Linhaarmat 	  = $resarmat->fetchRow();
												$QuantidadeEst  = str_replace(",",".",$Linhaarmat[0]);
										}
										$QuantidadeVerifica = ($QuantidadeEst - $Quantidade[$i]);
										if($QuantidadeVerifica < 0){
												$Existe  = "S";
												$Posicao = $i;
										}

										# Verifica se não há movimentação posterior ao cadastramento da nota a ser cancelada para o item em andamento no loop #
										$sqlmov  = "SELECT A.CALMPOCODI, A.AMOVMAANOM, A.CMOVMACODI, A.CTIPMVCODI, A.AMOVMAQTDM, "; // Geral
										$sqlmov .= "       B.ETIPMVDESC, A.DMOVMAMOVI, A.CMOVMACODT, ";                             // Movimentação
										$sqlmov .= "       A.AENTNFANOE, A.CENTNFCODI, E.AENTNFNOTA, E.AENTNFSERI, ";               // Nota Fiscal
										$sqlmov .= "       D.CREQMASEQU, D.CREQMACODI, D.AREQMAANOR ";                              // Requisição
										$sqlmov .= "  FROM SFPC.TBTIPOMOVIMENTACAO B, ";
										$sqlmov .= "       SFPC.TBMOVIMENTACAOMATERIAL A ";
										$sqlmov .= "  LEFT OUTER JOIN SFPC.TBENTRADANOTAFISCAL  E ON (A.CALMPOCODI = E.CALMPOCODI AND A.AENTNFANOE = E.AENTNFANOE AND A.CENTNFCODI = E.CENTNFCODI) ";
										$sqlmov .= "  LEFT OUTER JOIN SFPC.TBREQUISICAOMATERIAL D ON (A.CREQMASEQU = D.CREQMASEQU) ";
										$sqlmov .= " WHERE A.CALMPOCODI = $Almoxarifado ";
										$sqlmov .= "   AND A.CMATEPSEQU = $Material[$i] ";
										$sqlmov .= "   AND A.TMOVMAULAT > '$DataHora[$i]' ";                                        // TimeStamp da NotaFiscal - procura movimentações criadas após o cadastro da nota a ser cancelada
										$sqlmov .= "   AND A.CTIPMVCODI = B.CTIPMVCODI ";
										$sqlmov .= "   AND A.CTIPMVCODI NOT IN (1,5) ";                                             // Saldo Inicial e Inventário
										$sqlmov .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A') ";                          // Apresentar só as movimentações ativas
										$sqlmov .= "   AND ((A.CTIPMVCODI <> 3 AND A.CTIPMVCODI <> 7)"; 														// Trazer todas as movimentações, menos 3 - Entrada por nota fiscal e 7 - Entrada por alteração de nota fiscal
										$sqlmov .= "    OR ((A.CTIPMVCODI = 3 OR A.CTIPMVCODI = 7) ";   														// ou, se o tipo for 1 e 7,
										$sqlmov .= "   AND (A.CENTNFCODI,A.AENTNFANOE) <> ($NotaFiscal, $AnoNota)))";               // não trazendo a própria nota fiscal
										$resmov  = $db->query($sqlmov);
										if( db::isError($resmov) ){
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmov");
										}else{
												$Rowsmov = $resmov->numRows();
												for($k=0; $k<$Rowsmov; $k++){
														$Linhamov         = $resmov->fetchRow();
														# Geral
														$Almoxarifado   	= $Linhamov[0];
														$AnoMovimentacao  = $Linhamov[1];
														$MovimentacaoCod  = $Linhamov[2];
														$TipoMovimentacao = $Linhamov[3];
														$QuantidadeChk    = $Linhamov[4];
														# Movimentação pura
														$DescMovimentacao = $Linhamov[5];
														$Data		          = databarra($Linhamov[6]);
														$NumeroDaMov      = $Linhamov[7];
														# Nota Fiscal
														if($Linhamov[8]) { $NotaAno    = $Linhamov[8]; }else{ $NotaAno    = "NULL"; }
														if($Linhamov[9]) { $NotaCodigo = $Linhamov[9]; }else{ $NotaCodigo = "NULL"; }
														if($Linhamov[10]){ $NotaNumero = $Linhamov[10];}else{ $NotaNumero = "NULL"; }
														if($Linhamov[11]){ $NotaSerie  = $Linhamov[11];}else{ $NotaSerie  = "NULL"; }
														# Requisição
														if($Linhamov[12]){ $RequisicaoSeq = $Linhamov[12]; }else{ $RequisicaoSeq = "NULL"; }
														if($Linhamov[13]){ $Requisicao    = $Linhamov[13]; }else{ $Requisicao    = "NULL"; }
														if($Linhamov[14]){ $AnoRequisicao = $Linhamov[14]; }else{ $AnoRequisicao = "NULL"; }

														$CodMovArray = array(); // Inicia array que será usado em algumas movimentações


														# Verifica se houve devolução para cada tipo de movimentação - INÍCIO #

														# NOTA FISCAL: ENTRADAS : 3 - Nota, 7 - Alteração --> SAÍDA: 8 - Alteração
														if ( ($TipoMovimentacao == 3) or ($TipoMovimentacao == 7) ) {
																$sqlnota  = "SELECT A.AMOVMAQTDM ";
																$sqlnota .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A ";
																$sqlnota .= "  LEFT OUTER JOIN SFPC.TBENTRADANOTAFISCAL B ";
																$sqlnota .= "    ON A.CALMPOCODI = B.CALMPOCODI ";
																$sqlnota .= "   AND A.AENTNFANOE = B.AENTNFANOE ";
																$sqlnota .= "   AND A.CENTNFCODI = B.CENTNFCODI ";
																$sqlnota .= "   AND (A.CALMPOCODI, A.AENTNFANOE, A.CENTNFCODI) <> ($Almoxarifado, $NotaAno, $NotaFiscal) "; // Para não checar a própria nota fiscal que está sendo cancelada no momento
																$sqlnota .= " WHERE A.CALMPOCODI = $Almoxarifado ";
																$sqlnota .= "   AND (A.CALMPOCODI, A.AMOVMAANOM, A.CMOVMACODI) <> ($Almoxarifado, $AnoMovimentacao, $MovimentacaoCod) ";             // Para não trazer a própria movimentação
																$sqlnota .= "   AND A.CMATEPSEQU = $Material[$i] ";
																$sqlnota .= "   AND A.TMOVMAULAT >= '$DataHora[$i]' ";              // TimeStamp do item da Nota Fiscal corrente no loop
																$sqlnota .= "   AND A.CTIPMVCODI = 8 "; 					                  // Só traz movimentações de saída por alteração de nota fiscal
																$sqlnota .= "   AND A.AENTNFANOE = $NotaAno ";                      // Chave da nota para trazer o cancelamento da nota correspondente
																$sqlnota .= "   AND A.CENTNFCODI = $NotaCodigo ";                   // Chave da nota para trazer o cancelamento da nota correspondente
																$sqlnota .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																$resnota  = $db->query($sqlnota);
																if( db::isError($resnota) ){
																		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlnota");
																}else{
																		$Rowsnota = $resnota->numRows();
																		for($j=0; $j<$Rowsnota; $j++ ){
																				$Linhanota      = $resnota->fetchRow();
																				$QuantidadeNota = $Linhanota[0];
																				$QuantidadeChk  = $QuantidadeChk - $QuantidadeNota;
																		}
																}
																If($QuantidadeChk > 0){
																		if ($ItemNaMens) $MensagemMov .= ", ";
																		$MensagemMov .= "a movimentação na Nota Fiscal: ".$NotaNumero."/".$NotaSerie;
																		$ItemNaMens = 1;
																}
														}

														# NOTA FISCAL: SAÍDA: 8 - Alteração --> ENTRADAS: 3 - Nota, 7 - Alteração
														if($TipoMovimentacao == 8){
																$sqlnota  = "SELECT A.AMOVMAQTDM ";
																$sqlnota .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A ";
																$sqlnota .= "  LEFT OUTER JOIN SFPC.TBENTRADANOTAFISCAL B ";
																$sqlnota .= "    ON A.CALMPOCODI = B.CALMPOCODI ";
																$sqlnota .= "   AND A.AENTNFANOE = B.AENTNFANOE ";
																$sqlnota .= "   AND A.CENTNFCODI = B.CENTNFCODI ";
																$sqlnota .= "   AND (A.CALMPOCODI, A.AENTNFANOE, A.CENTNFCODI) <> ($Almoxarifado, $NotaAno, $NotaFiscal) "; // Para não checar a própria nota fiscal que está sendo cancelada no momento
																$sqlnota .= " WHERE A.CALMPOCODI = $Almoxarifado ";
																$sqlnota .= "   AND (A.CALMPOCODI, A.AMOVMAANOM, A.CMOVMACODI) <> ($Almoxarifado, $AnoMovimentacao, $MovimentacaoCod) ";             // Para não trazer a própria movimentação
																$sqlnota .= "   AND A.CMATEPSEQU = $Material[$i] ";
																$sqlnota .= "   AND A.TMOVMAULAT >= '$DataHora[$i]' ";              // TimeStamp do item da Nota Fiscal corrente no loop
																$sqlnota .= "   AND A.CTIPMVCODI IN (3,7) "; 		                    // Só traz movimentações de saída por alteração de nota fiscal
																$sqlnota .= "   AND A.AENTNFANOE = $NotaAno ";                      // Chave da nota para trazer o cancelamento da nota correspondente
																$sqlnota .= "   AND A.CENTNFCODI = $NotaCodigo ";                   // Chave da nota para trazer o cancelamento da nota correspondente
																$sqlnota .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																$resnota  = $db->query($sqlnota);
																if( db::isError($resnota) ){
																		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlnota");
																}else{
																		$Rowsnota = $resnota->numRows();
																		for ($j=0;$j<$Rowsnota;$j++){
																				$Linhanota      = $resnota->fetchRow();
																				$QuantidadeNota = $Linhanota[0];
																				$QuantidadeChk  = $QuantidadeChk - $QuantidadeNota;
																		}
																}
																If($QuantidadeChk > 0){
																		if($ItemNaMens) $MensagemMov .= ", ";
																		$MensagemMov .= "a movimentação na Nota Fiscal: ".$NotaNumero."/".$NotaSerie;
																		$ItemNaMens = 1;
																}
														}

														# REQUISIÇÃO: SAÍDAS: 4 - Atendimento Requisição, 20 - Acerto Requisição, 22 - Acerto Devolução Interna --> ENTRADAS: 2 - Devolução Interna, 18 - Cancelamento, 19 - Acerto Req, 21 - Acerto Devolução Interna
														if ( ($TipoMovimentacao == 4) or ($TipoMovimentacao == 20) or ($TipoMovimentacao == 22) ) {
																$sqlrequ  = "SELECT A.AMOVMAQTDM, A.CTIPMVCODI ";
																$sqlrequ .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A ";
																$sqlrequ .= "  LEFT OUTER JOIN SFPC.TBREQUISICAOMATERIAL B ON (A.CREQMASEQU = B.CREQMASEQU) ";
																$sqlrequ .= " WHERE A.CALMPOCODI = $Almoxarifado ";
																$sqlrequ .= "   AND (A.CALMPOCODI, A.AMOVMAANOM, A.CMOVMACODI) <> ($Almoxarifado, $AnoMovimentacao, $MovimentacaoCod) ";             // Para não trazer a própria movimentação
																$sqlrequ .= "   AND A.CMATEPSEQU = $Material[$i] ";
																$sqlrequ .= "   AND A.CREQMASEQU = $RequisicaoSeq ";                // Chave da requisição para trazer o cancelamento da requisição correspondente
																$sqlrequ .= "   AND A.TMOVMAULAT >= '$DataHora[$i]' ";              // TimeStamp do item da Nota Fiscal corrente no loop
																$sqlrequ .= "   AND A.CTIPMVCODI IN (4,20,22, 2,18,19,21) ";        // Só traz movimentações de entrada (2,18,19,21) devolvendo saídas para atender requisições (4) e outras de saída (20,22) que podem "incrementar o problema"
																$sqlrequ .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																$resrequ  = $db->query($sqlrequ);
																if( db::isError($resrequ) ){
																		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlrequ");
																}else{
																		$Rowsrequ = $resrequ->numRows();
																		for($j=0;$j<$Rowsrequ;$j++){
																				$Linharequ      = $resrequ->fetchRow();
																				$QuantidadeRequ = $Linharequ[0];
																				$TipoMovRequ    = $Linharequ[1];
																				if ( ($TipoMovRequ == 4) or ($TipoMovRequ == 20) or ($TipoMovRequ == 22) ) { // Se for também saída, "aumenta o problema"
																						$QuantidadeChk = $QuantidadeChk + $QuantidadeRequ;
																				}else{                                                                       // Se for entradas, "diminui o problema"
																						$QuantidadeChk = $QuantidadeChk - $QuantidadeRequ;
																				}
																		}
																}
																If($QuantidadeChk > 0){
																		if ($ItemNaMens) $MensagemMov .= ", ";
																		$MensagemMov .= "a movimentação na Requisição: ".$Requisicao."/".$AnoRequisicao;
																		$ItemNaMens = 1;
																}
														}

														# REQUISIÇÃO: ENTRADAS: 2 - Devolução Interna, 19 - Acerto da Requisição, 21 - Acerto Devolução Interna --> SAÍDAS: 4 - Saída por Requisição, 20 - Acerto Requisição, 22 - Acerto Devolução Interna / ENTRADA: 18 - Cancelamento Requisição
														if( ($TipoMovimentacao == 2) or ($TipoMovimentacao == 19) or ($TipoMovimentacao == 21) ) {
																$sqlrequ  = "SELECT A.AMOVMAQTDM, A.CTIPMVCODI ";
																$sqlrequ .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A ";
																$sqlrequ .= "  LEFT OUTER JOIN SFPC.TBREQUISICAOMATERIAL B ON (A.CREQMASEQU = B.CREQMASEQU) ";
																$sqlrequ .= " WHERE A.CALMPOCODI = $Almoxarifado ";
																$sqlrequ .= "   AND (A.CALMPOCODI, A.AMOVMAANOM, A.CMOVMACODI) <> ($Almoxarifado, $AnoMovimentacao, $MovimentacaoCod) ";             // Para não trazer a própria movimentação
																$sqlrequ .= "   AND A.CMATEPSEQU = $Material[$i] ";
																$sqlrequ .= "   AND A.CREQMASEQU = $RequisicaoSeq ";                // Chave da requisição para trazer o cancelamento da requisição correspondente
																$sqlrequ .= "   AND A.TMOVMAULAT >= '$DataHora[$i]' ";              // TimeStamp do item da Nota Fiscal corrente no loop
																$sqlrequ .= "   AND A.CTIPMVCODI IN (2,19,21, 4,20,22, 18) ";       // Só traz movimentações de saída devolvendo entradas (22) e outras de entrada (2,18,19,21) que podem incrementar o problema. Se achar uma movimentação do Tipo 18 - Cancelamento Requisição, armazena o saldo na variável de compensação para posterior modificação do estoque e calculo do valor médio
																$sqlrequ .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																$sqlrequ .= " ORDER BY A.TMOVMAULAT ";
																$resrequ  = $db->query($sqlrequ);
																if( db::isError($resrequ) ){
																		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlrequ");
																}else{
																		$Rowsrequ = $resrequ->numRows();
																		for($j=0;$j<$Rowsrequ;$j++){
																				$Linharequ      = $resrequ->fetchRow();
																				$QuantidadeRequ = $Linharequ[0];
																				$TipoMovRequ    = $Linharequ[1];
																				if ( ($TipoMovRequ == 2) or ($TipoMovRequ == 19) or ($TipoMovRequ == 21) ) { // Se for também entrada, "aumenta o problema"
																						$QuantidadeChk = $QuantidadeChk + $QuantidadeRequ;
																				}elseif($TipoMovRequ == 18){
																						$LiberaPorCancelamento = 1;
																				}else{																																			// Se forem saídas, "diminui o problema"
																						$QuantidadeChk = $QuantidadeChk - $QuantidadeRequ;
																				}
																		}
																}
																If( ($QuantidadeChk > 0) and (!$LiberaPorCancelamento) ) {
																		if ($ItemNaMens) $MensagemMov .= ", ";
																		$MensagemMov .= "a movimentação na Requisição: ".$Requisicao."/".$AnoRequisicao;
																		$ItemNaMens = 1;
																}
																$LiberaPorCancelamento = null;
														}

														# REQUISIÇÃO: ENTRADA: 18 - Cancelamento de Requisição --> EXCEÇÃO - Não há uma movimentação que Cancele esta movimentação.
														#																													 Neste momento o sistema vai deixar passar, mas armazena a quantidade
														#																													 numa variável de compensação para subtrair a quantidade em estoque
														#																													 para cálculo do preço médio, no cancelamento da nota, caso a requisição
														#																													 que está sendo Cancelada tenha sido feita antes da entrada da nota
														if($TipoMovimentacao == 18) {
																$sqlrequ  = "SELECT A.AMOVMAQTDM, A.CTIPMVCODI ";
																$sqlrequ .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A ";
																$sqlrequ .= "  LEFT OUTER JOIN SFPC.TBREQUISICAOMATERIAL B ON (A.CREQMASEQU = B.CREQMASEQU) ";
																$sqlrequ .= " WHERE A.CALMPOCODI = $Almoxarifado ";
																$sqlrequ .= "   AND (A.CALMPOCODI, A.AMOVMAANOM, A.CMOVMACODI) <> ($Almoxarifado, $AnoMovimentacao, $MovimentacaoCod) ";             // Para não trazer a própria movimentação
																$sqlrequ .= "   AND A.CMATEPSEQU = $Material[$i] ";
																$sqlrequ .= "   AND A.CREQMASEQU = $RequisicaoSeq ";                // Chave da requisição para trazer o cancelamento da requisição correspondente
																$sqlrequ .= "   AND A.TMOVMAULAT >= '$DataHora[$i]' ";              // TimeStamp do item da Nota Fiscal corrente no loop
																$sqlrequ .= "   AND A.CTIPMVCODI IN (4,20, 19) ";                   // Só traz movimentações de saída (4,20) devolvendo entrada por cancelamento de requisição (18) e outras entradas que incrementam o problema (19)
																$sqlrequ .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																$resrequ  = $db->query($sqlrequ);
																if( db::isError($resrequ) ){
																		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlrequ");
																}else{
																		$Rowsrequ = $resrequ->numRows();
																		if($Rowsrequ > 0){
																				for($j=0;$j<$Rowsrequ;$j++){
																						$Linharequ      = $resrequ->fetchRow();
																						$QuantidadeRequ = $Linharequ[0];
																						$TipoMovRequ    = $Linharequ[1];
																						if(!$QuantComp[$i]) $QuantComp[$i] = $QuantidadeChk;
																						if($TipoMovRequ == 19) {                  // Se for também entrada, aumenta a Compensação
																								$QuantComp[$i] = $QuantComp[$i] + $QuantidadeRequ;
																						}else{                   									 // Se forem saídas, diminui Compensação"
																								$QuantComp[$i] = $QuantComp[$i] - $QuantidadeRequ;
																						}
																				}
																		}else{
																				$QuantComp[$i] = $QuantidadeChk;               // Compensação para material vigente do array na íntegra caso não haja nenhuma movimentação que incremente ou decremente o problema
																		}
																}
														}

														# MOVIMENTAÇÃO: ENTRADA: 6 - Empréstimo --> SAÍDA: 13 - Devolução Empréstimo
														if($TipoMovimentacao == 6){
																$sqlmovi  = "SELECT A.AMOVMAQTDM, A.CTIPMVCODI, A.CALMPOCOD1, A.AMOVMAANO1, A.CMOVMACOD1 ";
																$sqlmovi .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A ";
																$sqlmovi .= " WHERE A.CALMPOCODI = $Almoxarifado ";
																$sqlmovi .= "   AND (A.CALMPOCODI, A.AMOVMAANOM, A.CMOVMACODI) <> ($Almoxarifado, $AnoMovimentacao, $MovimentacaoCod) ";             // Para não trazer a própria movimentação
																$sqlmovi .= "   AND A.CMATEPSEQU = $Material[$i] ";
																$sqlmovi .= "   AND A.TMOVMAULAT >= '$DataHora[$i]' ";              // TimeStamp do item da Nota Fiscal corrente no loop
																$sqlmovi .= "   AND ( ";
																$sqlmovi .= "          ( ";
																$sqlmovi .= "          A.CTIPMVCODI = 13 ";                        // Só traz movimentações de saída devolvendo empréstimo
																$sqlmovi .= "          AND A.CMOVMACOD1 = (SELECT CMOVMACOD1 FROM SFPC.TBMOVIMENTACAOMATERIAL WHERE CMOVMACODI = $MovimentacaoCod AND CTIPMVCODI = 6) ";
																$sqlmovi .= "          ) ";
																$sqlmovi .= "          OR (A.CTIPMVCODI = 31) ";
																$sqlmovi .= "       ) ";
																$sqlmovi .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																$resmovi  = $db->query($sqlmovi);
																if( db::isError($resmovi) ){
																		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmovi");
																}else{
																		$Rowsmovi = $resmovi->numRows();
																		for($j=0;$j<$Rowsmovi;$j++){
																				$Linhamovi      = $resmovi->fetchRow();
																				$QuantidadeMovi = $Linhamovi[0];
																				$TipoMovRequ    = $Linhamovi[1];
																				$CodMovSec      = $Linhamovi[2].$Linhamovi[3].$Linhamovi[4];
																				if( ($TipoMovRequ == 31) and (in_array($CodMovSec,$CodMovArray)) ){ // Se for também entrada, "aumenta o problema", mas só se for cancelamento de 13 - SAÍDA por Devolução Empréstimo, já armazenada anteriormente no array
																						$QuantidadeChk = $QuantidadeChk + $QuantidadeMovi;
																				}else{                                                              // Se forem saídas, "diminui o problema", e armazena movimentação em array para verificar se depois não é cancelada
																						$CodMovArray[count($CodMovArray)] = $CodMovSec;
																						$QuantidadeChk = $QuantidadeChk - $QuantidadeMovi;
																				}
																		}
																}
																If($QuantidadeChk > 0) {
																		if($ItemNaMens) $MensagemMov .= ", ";
																		$MensagemMov .= "a Movimentação: ".$NumeroDaMov." - ".$DescMovimentacao." realizada em ".$Data;
																		$ItemNaMens = 1;
																}
																$CodMovArray = array();
														}

														# MOVIMENTAÇÃO: SAÍDA: 13 - Devolução Empréstimo --> ENTRADA: 6 - Empréstimo, 31 - Cancelamento de Movimentação
														if($TipoMovimentacao == 13) {
																$sqlmovi  = "SELECT A.AMOVMAQTDM ";
																$sqlmovi .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A ";
																$sqlmovi .= " WHERE A.CALMPOCODI = $Almoxarifado ";
																$sqlmovi .= "   AND (A.CALMPOCODI, A.AMOVMAANOM, A.CMOVMACODI) <> ($Almoxarifado, $AnoMovimentacao, $MovimentacaoCod) ";             // Para não trazer a própria movimentação
																$sqlmovi .= "   AND A.CMATEPSEQU = $Material[$i] ";
																$sqlmovi .= "   AND A.TMOVMAULAT >= '$DataHora[$i]' ";              // TimeStamp do item da Nota Fiscal corrente no loop
																$sqlmovi .= "   AND (";
																$sqlmovi .= "        A.CTIPMVCODI IN (13, 6) ";                     // Só traz movimentações de entrada confirmando empréstimo e a de saída que aumenta o problema
																$sqlmovi .= "        OR ( A.CTIPMVCODI = 31 AND A.CMOVMACOD1 = $MovimentacaoCod ) ";
																$sqlmovi .= "       ) ";
																$sqlmovi .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																$resmovi  = $db->query($sqlmovi);
																if( db::isError($resmovi) ){
																		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlrequ");
																}else{
																		$Rowsmovi = $resmovi->numRows();
																		for($j=0;$j<$Rowsmovi;$j++){
																				$Linhamovi      = $resmovi->fetchRow();
																				$QuantidadeMovi = $Linhamovi[0];
																				$TipoMovRequ    = $Linhamovi[1];
																				if($TipoMovRequ == 13){                                        // Se for também entrada, "aumenta o problema"
																						$QuantidadeChk = $QuantidadeChk + $QuantidadeMovi;
																				}else{                                                         // Se forem saídas, "diminui o problema"
																						$QuantidadeChk = $QuantidadeChk - $QuantidadeMovi;
																				}
																		}
																}
																if($QuantidadeChk > 0){
																		if(!$MensMov13){
																				if($ItemNaMens) $MensagemMov .= ", ";
																				$MensagemMov .= "a(s) Movimentação(ões) de ".$DescMovimentacao; // Mensagem genérica
																				$MensMov13 = 1;
																		}
																		$ItemNaMens = 1;
																}
														}

														# MOVIMENTAÇÃO: SAÍDA: 12 - Empréstimo --> ENTRADA: 9 - Devolução de Empréstimo, 31 - Cancelamento de Movimentação
														if($TipoMovimentacao == 12) {
																$sqlmovi  = "SELECT A.AMOVMAQTDM ";
																$sqlmovi .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A ";
																$sqlmovi .= " WHERE A.CALMPOCODI = $Almoxarifado ";
																$sqlmovi .= "   AND (A.CALMPOCODI, A.AMOVMAANOM, A.CMOVMACODI) <> ($Almoxarifado, $AnoMovimentacao, $MovimentacaoCod) ";             // Para não trazer a própria movimentação
																$sqlmovi .= "   AND A.CMATEPSEQU = $Material[$i] ";
																$sqlmovi .= "   AND A.TMOVMAULAT >= '$DataHora[$i]' ";              // TimeStamp do item da Nota Fiscal corrente no loop
																$sqlmovi .= "   AND ( ";
																$sqlmovi .= "         (A.CTIPMVCODI = 9  AND A.CMOVMACODI = (SELECT CMOVMACOD1 FROM SFPC.TBMOVIMENTACAOMATERIAL WHERE CMOVMACODI = $MovimentacaoCod AND CTIPMVCODI = 13) ) "; // Só traz movimentações de entrada confirmando devolução de empréstimo
																$sqlmovi .= "         OR (A.CTIPMVCODI = 31 AND A.CMOVMACOD1 = $MovimentacaoCod) ";
																$sqlmovi .= "       ) ";                                                                                            // Só traz movimentações de entrada cancelando devolução de empréstimo
																$sqlmovi .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																$resmovi  = $db->query($sqlmovi);
																if( db::isError($resmovi) ){
																		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmovi");
																}else{
																		$Rowsmovi = $resmovi->numRows();
																		for($j=0;$j<$Rowsmovi;$j++){
																				$Linhamovi      = $resmovi->fetchRow();
																				$QuantidadeMovi = $Linhamovi[0];
																				$QuantidadeChk  = $QuantidadeChk - $QuantidadeMovi;
																		}
																}
																if($QuantidadeChk > 0) {
																		if($ItemNaMens) $MensagemMov .= ", ";
																		$MensagemMov .= "a Movimentação: ".$NumeroDaMov." - ".$DescMovimentacao." realizada em ".$Data; // Mensagem específica
																		$ItemNaMens = 1;
																}
														}

														# MOVIMENTAÇÃO: ENTRADA: 9 - Devolução de Empréstimo --> SAÍDA: 12 - Empréstimo
														if($TipoMovimentacao == 9){
																$sqlmovi  = "SELECT A.AMOVMAQTDM, A.CTIPMVCODI, A.CALMPOCOD1, A.AMOVMAANO1, A.CMOVMACOD1 ";
																$sqlmovi .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A ";
																$sqlmovi .= " WHERE A.CALMPOCODI = $Almoxarifado ";
																$sqlmovi .= "   AND (A.CALMPOCODI, A.AMOVMAANOM, A.CMOVMACODI) <> ($Almoxarifado, $AnoMovimentacao, $MovimentacaoCod) ";             // Para não trazer a própria movimentação
																$sqlmovi .= "   AND A.CMATEPSEQU = $Material[$i] ";
																$sqlmovi .= "   AND A.TMOVMAULAT >= '$DataHora[$i]' ";              // TimeStamp do item da Nota Fiscal corrente no loop
																$sqlmovi .= "   AND A.CTIPMVCODI IN (9,31, 12) ";                   // Só traz movimentações de saída confirmando empréstimo, e outras entradas que aumentam o problema
																$sqlmovi .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																$resmovi  = $db->query($sqlmovi);
																if( db::isError($resmovi) ){
																		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlrequ");
																}else{
																		$Rowsmovi = $resmovi->numRows();
																		for($j=0;$j<$Rowsmovi;$j++){
																				$Linhamovi      = $resmovi->fetchRow();
																				$QuantidadeMovi = $Linhamovi[0];
																				$TipoMovRequ    = $Linhamovi[1];
																				$CodMovSec      = $Linhamovi[2].$Linhamovi[3].$Linhamovi[4];
																				if($TipoMovRequ == 9){                                                  // Se for também entrada, "aumenta o problema"
																						$QuantidadeChk = $QuantidadeChk + $QuantidadeMovi;
																				}elseif( ($TipoMovRequ == 31) and (in_array($CodMovSec,$CodMovArray)) ){ // Se for também entrada, "aumenta o problema", mas só se for cancelamento de 12 - SAÍDA por Empréstimo, já armazenada anteriormente no array
																						$QuantidadeChk = $QuantidadeChk + $QuantidadeMovi;
																				}else{                                                                   // Se forem saídas (12), "diminui o problema", e armazena movimentação em array para verificar se depois não é cancelada
																						$CodMovArray[count($CodMovArray)] = $CodMovSec;
																						$QuantidadeChk = $QuantidadeChk - $QuantidadeMovi;
																				}
																		}
																}
																If($QuantidadeChk > 0){
																		if(!$MensMov9){
																				if($ItemNaMens) $MensagemMov .= ", ";
																				$MensagemMov .= "a(s) Movimentação(ões) de ".$DescMovimentacao;          // Mensagem genérica
																				$MensMov9 = 1;
																		}
																		$ItemNaMens = 1;
																}
																$CodMovArray = array();
														}

														# MOVIMENTAÇÃO: ENTRADA: 10 - Doação Externa --> SAÍDA: 27 - Cancelamento para NF
														if($TipoMovimentacao == 10) {
																$sqlmovi  = "SELECT A.AMOVMAQTDM, A.CTIPMVCODI ";
																$sqlmovi .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A ";
																$sqlmovi .= " WHERE A.CALMPOCODI = $Almoxarifado ";
																$sqlmovi .= "   AND (A.CALMPOCODI, A.AMOVMAANOM, A.CMOVMACODI) <> ($Almoxarifado, $AnoMovimentacao, $MovimentacaoCod) ";             // Para não trazer a própria movimentação
																$sqlmovi .= "   AND A.CMATEPSEQU = $Material[$i] ";
																$sqlmovi .= "   AND A.TMOVMAULAT >= '$DataHora[$i]' ";              // TimeStamp do item da Nota Fiscal corrente no loop
																$sqlmovi .= "   AND A.CTIPMVCODI IN (10, 27) ";                            // Só traz movimentações de saídas devolvendo doações
																$sqlmovi .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																$resmovi  = $db->query($sqlmovi);
																if( db::isError($resmovi) ){
																		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmovi");
																}else{
																		$Rowsmovi = $resmovi->numRows();
																		for($j=0;$j<$Rowsmovi;$j++){
																				$Linhamovi      = $resmovi->fetchRow();
																				$QuantidadeMovi = $Linhamovi[0];
																				$TipoMovRequ    = $Linhamovi[1];
																				if($TipoMovRequ == 10){                                     // Se for também entrada, "aumenta o problema"
																						$QuantidadeChk = $QuantidadeChk + $QuantidadeMovi;
																				}else{                                                      // Se for saída, "diminui o problema"
																						$QuantidadeChk = $QuantidadeChk - $QuantidadeMovi;
																				}
																		}
																}
																if($QuantidadeChk > 0){
																		if(!$MensMov10){
																				if($ItemNaMens) $MensagemMov .= ", ";
																				$MensagemMov .= "a(s) Movimentação(ões) de ".$DescMovimentacao; // Mensagem genérica
																				$MensMov10 = 1;
																		}
																		$ItemNaMens = 1;
																}
														}

														# MOVIMENTAÇÃO: SAÍDA: 27 - Cancelamento para NF --> ENTRADA: 10 - Doação Externa, 26 - Cancelamento de Movimentação
														if($TipoMovimentacao == 27){
																$sqlmovi  = "SELECT A.AMOVMAQTDM, A.CTIPMVCODI ";
																$sqlmovi .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A ";
																$sqlmovi .= " WHERE A.CALMPOCODI = $Almoxarifado ";
																$sqlmovi .= "   AND (A.CALMPOCODI, A.AMOVMAANOM, A.CMOVMACODI) <> ($Almoxarifado, $AnoMovimentacao, $MovimentacaoCod) ";             // Para não trazer a própria movimentação
																$sqlmovi .= "   AND A.CMATEPSEQU = $Material[$i] ";
																$sqlmovi .= "   AND A.TMOVMAULAT >= '$DataHora[$i]' ";              // TimeStamp do item da Nota Fiscal corrente no loop
																$sqlmovi .= "   AND A.CTIPMVCODI IN (27, 10,26) ";                            // Só traz movimentações de entrada confirmando Doação Externa, e outras saídas que aumentam o problema
																$sqlmovi .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																$resmovi  = $db->query($sqlmovi);
																if( db::isError($resmovi) ){
																		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmovi");
																}else{
																		$Rowsmovi = $resmovi->numRows();
																		for($j=0;$j<$Rowsmovi;$j++){
																				$Linhamovi      = $resmovi->fetchRow();
																				$QuantidadeMovi = $Linhamovi[0];
																				$TipoMovRequ    = $Linhamovi[1];
																				if($TipoMovRequ == 27){                                     // Se for também entrada, "aumenta o problema"
																						$QuantidadeChk = $QuantidadeChk + $QuantidadeMovi;
																				}else{                                                      // Se for saída, "diminui o problema"
																						$QuantidadeChk = $QuantidadeChk - $QuantidadeMovi;
																				}
																		}
																}
																if($QuantidadeChk > 0){
																		if(!$MensMov27){
																				if ($ItemNaMens) $MensagemMov .= ", ";
																				$MensagemMov .= "a(s) Movimentação(ões) de ".$DescMovimentacao; // Mensagem genérica
																				$MensMov27 = 1;
																		}
																		$ItemNaMens = 1;
																}
														}

														# MOVIMENTAÇÃO: ENTRADA: 11 - TROCA --> SAÍDA: 15 - TROCA
														if( $TipoMovimentacao == 11 ){
																$sqlmovi  = "SELECT A.AMOVMAQTDM, A.CTIPMVCODI, A.CALMPOCOD1, A.AMOVMAANO1, A.CMOVMACOD1 ";
																$sqlmovi .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A ";
																$sqlmovi .= " WHERE A.CALMPOCODI = $Almoxarifado ";
																$sqlmovi .= "   AND (A.CALMPOCODI, A.AMOVMAANOM, A.CMOVMACODI) <> ($Almoxarifado, $AnoMovimentacao, $MovimentacaoCod) ";             // Para não trazer a própria movimentação
																$sqlmovi .= "   AND A.CMATEPSEQU = $Material[$i] ";
																$sqlmovi .= "   AND A.TMOVMAULAT >= '$DataHora[$i]' ";              // TimeStamp do item da Nota Fiscal corrente no loop
																$sqlmovi .= "   AND A.CTIPMVCODI IN (11,31, 15) ";                  // Só traz movimentações de saída (15) devolvendo entradas por troca (11), e outras de entrada por troca que aumentam o problema
																$sqlmovi .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																$sqlmovi .= " ORDER BY A.TMOVMAULAT ";
																$resmovi  = $db->query($sqlmovi);
																if( db::isError($resmovi) ){
																		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmovi");
																}else{
																		$Rowsmovi = $resmovi->numRows();
																		for($j=0;$j<$Rowsmovi;$j++){
																				$Linhamovi      = $resmovi->fetchRow();
																				$QuantidadeMovi = $Linhamovi[0];
																				$TipoMovRequ    = $Linhamovi[1];
																				$CodMovSec      = $Linhamovi[2].$Linhamovi[3].$Linhamovi[4];
																				if ($TipoMovRequ == 11){                                                 // Se for também entrada, "aumenta o problema"
																						$QuantidadeChk = $QuantidadeChk + $QuantidadeMovi;
																				}elseif( ($TipoMovRequ == 31) and (in_array($CodMovSec,$CodMovArray)) ){ // Se for também entrada, "aumenta o problema", mas só se for cancelamento de 15 - SAÍDA por Troca, já armazenada anteriormente no array
																						$QuantidadeChk = $QuantidadeChk + $QuantidadeMovi;
																				}else{                                                                   // Se forem saídas (15), "diminui o problema", e armazena movimentação em array para verificar se depois não é cancelada
																						$CodMovArray[count($CodMovArray)] = $CodMovSec;
																						$QuantidadeChk = $QuantidadeChk - $QuantidadeMovi;
																				}
																		}
																}
																if($QuantidadeChk > 0){
																		if(!$MensMov11){
																				if($ItemNaMens) $MensagemMov .= ", ";
																				$MensagemMov .= "a(s) Movimentação(ões) de ".$DescMovimentacao; // Mensagem genérica
																				$MensMov11 = 1;
																		}
																		$ItemNaMens = 1;
																}
																$CodMovArray[] = null;
														}

														# MOVIMENTAÇÃO: SAÍDA: 15 - TROCA --> ENTRADA: 11 - TROCA, 31 - CANCELAMENTO DE MOVIMENTAÇÃO
														if( ($TipoMovimentacao == 15) ){
																$sqlmovi  = "SELECT A.AMOVMAQTDM, A.CTIPMVCODI ";
																$sqlmovi .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A ";
																$sqlmovi .= " WHERE A.CALMPOCODI = $Almoxarifado ";
																$sqlmovi .= "   AND (A.CALMPOCODI, A.AMOVMAANOM, A.CMOVMACODI) <> ($Almoxarifado, $AnoMovimentacao, $MovimentacaoCod) ";             // Para não trazer a própria movimentação
																$sqlmovi .= "   AND A.CMATEPSEQU = $Material[$i] ";
																$sqlmovi .= "   AND A.TMOVMAULAT >= '$DataHora[$i]' ";              // TimeStamp do item da Nota Fiscal corrente no loop
																$sqlmovi .= "   AND (";
																$sqlmovi .= "       A.CTIPMVCODI IN (15, 11) ";                  // Só traz movimentações de entrada (11) devolvendo saídas por troca (15), e outras saídas por troca que aumentam o problema
																$sqlmovi .= "       OR ((A.CTIPMVCODI = 31) AND (A.CMOVMACOD1 = $MovimentacaoCod))";
																$sqlmovi .= "       ) "; // Ou movimentações de cancelamento, específicas da 15 - saída por troca
																$sqlmovi .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																$resmovi  = $db->query($sqlmovi);
																if( db::isError($resmovi) ){
																		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmovi");
																}else{
																		$Rowsmovi = $resmovi->numRows();
																		for($j=0;$j<$Rowsmovi;$j++){
																				$Linhamovi      = $resmovi->fetchRow();
																				$QuantidadeMovi = $Linhamovi[0];
																				$TipoMovRequ    = $Linhamovi[1];
																				if($TipoMovRequ == 15) {                                        // Se for também saída, "aumenta o problema"
																						$QuantidadeChk = $QuantidadeChk + $QuantidadeMovi;
																				}else{                                                          // Se for entradas, "diminui o problema"
																						$QuantidadeChk = $QuantidadeChk - $QuantidadeMovi;
																				}
																		}
																}
																if($QuantidadeChk > 0) {
																		if(!$MensMov15){
																				if($ItemNaMens) $MensagemMov .= ", ";
																				$MensagemMov .= "a(s) Movimentação(ões) de ".$DescMovimentacao; // Mensagem genérica
																				$MensMov15 = 1;
																		}
																		$ItemNaMens = 1;
																}
														}

														# MOVIMENTAÇÃO: SAÍDA: 14 - Obsoletismo, 16 - Avaria, 17 - Vencimento, 23 - Furto, 24 - Doação Externa --> ENTRADA: 26 - Cancelamento para NF
														if( ($TipoMovimentacao == 14) or ($TipoMovimentacao == 16) or ($TipoMovimentacao == 17) or ($TipoMovimentacao == 23) or ($TipoMovimentacao == 24) or ($TipoMovimentacao == 37) ){
																$sqlmovi  = "SELECT A.AMOVMAQTDM, A.CTIPMVCODI ";
																$sqlmovi .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A ";
																$sqlmovi .= " WHERE A.CALMPOCODI = $Almoxarifado ";
																$sqlmovi .= "   AND (A.CALMPOCODI, A.AMOVMAANOM, A.CMOVMACODI) <> ($Almoxarifado, $AnoMovimentacao, $MovimentacaoCod) ";             // Para não trazer a própria movimentação
																$sqlmovi .= "   AND A.CMATEPSEQU = $Material[$i] ";
																$sqlmovi .= "   AND A.TMOVMAULAT >= '$DataHora[$i]' ";              // TimeStamp do item da Nota Fiscal corrente no loop
																$sqlmovi .= "   AND A.CTIPMVCODI IN (14,16,17,23,24, 26,37) ";         // Só traz movimentações de entrada confirmando movimentações de saída diversas
																$sqlmovi .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																$resmovi  = $db->query($sqlmovi);
																if( db::isError($resmovi) ){
																		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmovi");
																}else{
																		$Rowsmovi = $resmovi->numRows();
																		for($j=0;$j<$Rowsmovi;$j++){
																				$Linhamovi      = $resmovi->fetchRow();
																				$QuantidadeMovi = $Linhamovi[0];
																				$TipoMovRequ    = $Linhamovi[1];
																				if($TipoMovRequ == 14 or $TipoMovRequ == 16 or $TipoMovRequ == 17 or $TipoMovRequ == 23 or $TipoMovRequ == 24 or $TipoMovRequ == 37){ // Se for também saída, "aumenta o problema"
																						$QuantidadeChk = $QuantidadeChk + $QuantidadeMovi;
																				}else{                                                                                                          // Se for entrada, "diminui o problema"
																						$QuantidadeChk = $QuantidadeChk - $QuantidadeMovi;
																				}
																		}
																}
																if($QuantidadeChk > 0) {
																		$variavel = "MensMov".$TipoMovimentacao;
																		if(!$$variavel){ // Variável variável. Dependendo de $TipoMovimentacao, pode ser a variável $MensMov14, $MensMov16, $MensMov17 e etc
																				if($ItemNaMens) $MensagemMov .= ", ";
																				$MensagemMov .= "a(s) Movimentação(ões) de ".$DescMovimentacao; // Mensagem genérica
																				$$variavel = 1;
																		}
																		$ItemNaMens = 1;
																}
														}

														# MOVIMENTAÇÃO: ENTRADA: 26 - Cancelamento para NF --> SAÍDA: 14 - Obsoletismo, 16 - Avaria, 17 - Vencimento, 23 - Furto, 24 - Doação Externa, 27 - Cancelamento de movimentação
														if($TipoMovimentacao == 26){
																$sqlmovi  = "SELECT A.AMOVMAQTDM, A.CTIPMVCODI ";
																$sqlmovi .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A ";
																$sqlmovi .= " WHERE A.CALMPOCODI = $Almoxarifado ";
																$sqlmovi .= "   AND (A.CALMPOCODI, A.AMOVMAANOM, A.CMOVMACODI) <> ($Almoxarifado, $AnoMovimentacao, $MovimentacaoCod) ";             // Para não trazer a própria movimentação
																$sqlmovi .= "   AND A.CMATEPSEQU = $Material[$i] ";
																$sqlmovi .= "   AND A.TMOVMAULAT >= '$DataHora[$i]' ";              // TimeStamp do item da Nota Fiscal corrente no loop
																$sqlmovi .= "   AND A.CTIPMVCODI IN (26, 14,16,17,23,24,27,37) ";      // Só traz movimentações de saída (14,16,17,23,24,27) confirmando a movimentação de entrada (26) e movimentações de entrada (26) que piora o problema
																$sqlmovi .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																$resmovi  = $db->query($sqlmovi);
																if( db::isError($resmovi) ){
																		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmovi");
																}else{
																		$Rowsmovi = $resmovi->numRows();
																		for($j=0;$j<$Rowsmovi;$j++){
																				$Linhamovi      = $resmovi->fetchRow();
																				$QuantidadeMovi = $Linhamovi[0];
																				$TipoMovRequ    = $Linhamovi[1];
																				if($TipoMovRequ == 26){                                     // Se for também entrada, "aumenta o problema"
																						$QuantidadeChk = $QuantidadeChk + $QuantidadeMovi;
																				}else{                                                      // Se for saída, "diminui o problema"
																						$QuantidadeChk = $QuantidadeChk - $QuantidadeMovi;
																				}
																		}
																}
																if($QuantidadeChk > 0) {
																		if(!$MensMov26){
																				if($ItemNaMens) $MensagemMov .= ", ";
																				$MensagemMov .= "a(s) Movimentação(ões) de ".$DescMovimentacao; // Mensagem genérica
																				$MensMov26 = 1;
																		}
																		$ItemNaMens = 1;
																}
														}

														# MOVIMENTAÇÃO: ENTRADA: 29 - Doação Entre Almoxarifados --> SAÍDA: 30 - Doação Entre Almoxarifados
														if($TipoMovimentacao == 29){
																$sqlmovi  = "SELECT A.AMOVMAQTDM, A.CTIPMVCODI, A.CALMPOCOD1, A.AMOVMAANO1, A.CMOVMACOD1 ";
																$sqlmovi .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A ";
																$sqlmovi .= " WHERE A.CALMPOCODI = $Almoxarifado ";
																$sqlmovi .= "   AND (A.CALMPOCODI, A.AMOVMAANOM, A.CMOVMACODI) <> ($Almoxarifado, $AnoMovimentacao, $MovimentacaoCod) ";             // Para não trazer a própria movimentação
																$sqlmovi .= "   AND A.CMATEPSEQU = $Material[$i] ";
																$sqlmovi .= "   AND A.TMOVMAULAT >= '$DataHora[$i]' ";              // TimeStamp do item da Nota Fiscal corrente no loop
																$sqlmovi .= "   AND A.CTIPMVCODI IN (29,31, 30) ";                  // Só traz movimentações de Saída e outras Entradas que aumentam o problema
																$sqlmovi .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																$resmovi  = $db->query($sqlmovi);
																if( db::isError($resmovi) ){
																		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmovi");
																}else{
																		$Rowsmovi = $resmovi->numRows();
																		for($j=0;$j<$Rowsmovi;$j++){
																				$Linhamovi      = $resmovi->fetchRow();
																				$QuantidadeMovi = $Linhamovi[0];
																				$TipoMovRequ    = $Linhamovi[1];
																				$CodMovSec      = $Linhamovi[2].$Linhamovi[3].$Linhamovi[4];
																				if($TipoMovRequ == 29) {                                                 // Se for também entrada, "aumenta o problema"
																						$QuantidadeChk = $QuantidadeChk + $QuantidadeMovi;
																				}elseif( ($TipoMovRequ == 31) and (in_array($CodMovSec,$CodMovArray)) ){ // Se for também entrada, "aumenta o problema", mas só se for cancelamento de 30 - SAÍDA por Doação Entre Almoxarifados, já armazenada anteriormente no array
																						$QuantidadeChk = $QuantidadeChk + $QuantidadeMovi;
																				}else{                                                                   // Se for 30 - SAÍDA por Doação Entre Almoxarifados diminui o problema, e armazena movimentação em array para verificar se depois não é cancelada
																						$CodMovArray[count($CodMovArray)] = $CodMovSec;
																						$QuantidadeChk = $QuantidadeChk - $QuantidadeMovi;
																				}
																		}
																}
																if($QuantidadeChk > 0){
																		if(!$MensMov29){
																				if($ItemNaMens) $MensagemMov .= ", ";
																				$MensagemMov .= "a(s) Movimentação(ões) de ".$DescMovimentacao;          // Mensagem genérica
																				$MensMov29 = 1;
																		}
																		$ItemNaMens = 1;
																}
																$CodMovArray = array();
														}

														# MOVIMENTAÇÃO: SAÍDA: 30 - Doação Entre Almoxarifados --> ENTRADA: 29 - Doação Entre Almoxarifados, 31 - Cancelamento de Movimentação
														if($TipoMovimentacao == 30){
																$sqlmovi  = "SELECT A.AMOVMAQTDM, A.CTIPMVCODI ";
																$sqlmovi .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A ";
																$sqlmovi .= " WHERE A.CALMPOCODI = $Almoxarifado ";
																$sqlmovi .= "   AND (A.CALMPOCODI, A.AMOVMAANOM, A.CMOVMACODI) <> ($Almoxarifado, $AnoMovimentacao, $MovimentacaoCod) ";             // Para não trazer a própria movimentação
																$sqlmovi .= "   AND A.CMATEPSEQU = $Material[$i] ";
																$sqlmovi .= "   AND A.TMOVMAULAT >= '$DataHora[$i]' ";              // TimeStamp do item da Nota Fiscal corrente no loop
																$sqlmovi .= "   AND A.CTIPMVCODI IN (30, 29) OR (A.CTIPMVCODI = 31 AND A.CMOVMACOD1 = $MovimentacaoCod) "; // Só traz movimentações de Entrada, e outras Saídas que aumentam o problema. A de entrada por cancelamento (31) tem que ser específica da Saída por Doação gerada
																$sqlmovi .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																$resmovi  = $db->query($sqlmovi);
																if( db::isError($resmovi) ){
																		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmovi");
																}else{
																		$Rowsmovi = $resmovi->numRows();
																		for($j=0;$j<$Rowsmovi;$j++){
																				$Linhamovi      = $resmovi->fetchRow();
																				$QuantidadeMovi = $Linhamovi[0];
																				$TipoMovRequ    = $Linhamovi[1];
																				if($TipoMovRequ == 30) {                                     // Se for também saída, "aumenta o problema"
																						$QuantidadeChk = $QuantidadeChk + $QuantidadeMovi;
																				}else{                                                       // Se for entradas, "diminui o problema"
																						$QuantidadeChk = $QuantidadeChk - $QuantidadeMovi;
																				}
																		}
																}
																If($QuantidadeChk > 0){
																		if(!$MensMov30){
																				if ($ItemNaMens) $MensagemMov .= ", ";
																				$MensagemMov .= "a(s) Movimentação(ões) de ".$DescMovimentacao; // Mensagem genérica
																				$MensMov30 = 1;
																		}
																		$ItemNaMens = 1;
																}
														}

														# MOVIMENTAÇÃO: ENTRADA: 31 - Cancelamento de Movimentação --> Saídas: 12 - Empréstimo entre órgãos, 13 - Devolução de Empréstimo, 15 - Troca, 30 - Doação entre almoxarifados
														if($TipoMovimentacao == 31){
																$sqlmovi  = "SELECT A.AMOVMAQTDM, A.CTIPMVCODI ";
																$sqlmovi .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A ";
																$sqlmovi .= " WHERE A.CALMPOCODI = $Almoxarifado ";
																$sqlmovi .= "   AND (A.CALMPOCODI, A.AMOVMAANOM, A.CMOVMACODI) <> ($Almoxarifado, $AnoMovimentacao, $MovimentacaoCod) ";             // Para não trazer a própria movimentação
																$sqlmovi .= "   AND A.CMATEPSEQU = $Material[$i] ";
																$sqlmovi .= "   AND A.TMOVMAULAT >= '$DataHora[$i]' ";              // TimeStamp do item da Nota Fiscal corrente no loop
																$sqlmovi .= "   AND A.CTIPMVCODI IN (31, 12,13,15,30) ";            // Só traz movimentações de Saída, e outras Entrada que aumentam o problema
																$sqlmovi .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																$resmovi  = $db->query($sqlmovi);
																if( db::isError($resmovi) ){
																		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmovi");
																}else{
																		$Rowsmovi = $resmovi->numRows();
																		for($j=0;$j<$Rowsmovi;$j++){
																				$Linhamovi      = $resmovi->fetchRow();
																				$QuantidadeMovi = $Linhamovi[0];
																				$TipoMovRequ    = $Linhamovi[1];
																				if($TipoMovRequ == 31) {                                     // Se for também saída, "aumenta o problema"
																						$QuantidadeChk = $QuantidadeChk + $QuantidadeMovi;
																				}else{                                                       // Se for entradas, "diminui o problema"
																						$QuantidadeChk = $QuantidadeChk - $QuantidadeMovi;
																				}
																		}
																}
																if($QuantidadeChk > 0){
																		if(!$MensMov31){
																				if($ItemNaMens) $MensagemMov .= ", ";
																				$MensagemMov .= "a(s) Movimentação(ões) de ".$DescMovimentacao; // Mensagem genérica
																				$MensMov31 = 1;
																		}
																		$ItemNaMens = 1;
																}
														}

														# Verifica se houve devolução para cada tipo de movimentação - FIM #

												}
										}
										if( ($MaterialTestado != $Material[$i]) and ($ItemNaMens) ) {
												if($MensagemMovimentacao) $MensagemMovimentacao .= ". ";
												$DescricaoMat = explode($SimboloConcatenacaoDesc,$DescMaterial[$i]);
												$Virgula = 2;
												if(strrpos($MensagemMov, ",") != 0 ) { $MensagemMov = substr_replace($MensagemMov, " e ", strrpos($MensagemMov, ",")) . substr($MensagemMov,(strrpos($MensagemMov, ",")+1)); }
												$DataIni = DataBarra($DataHora[$i]);
												$DataFim = DataBarra(date("Y-m-d"));
												//$Url = "RelAuxilioCancelamentoNotaPdf.php?Almoxarifado=$Almoxarifado&Localizacao=$Localizacao&Material=$Material[$i]&DataIni=$DataIni&DataFim=$DataFim&Ulat=".urlencode($DataHora[$i])."&Procedimento=M";
												$Url = "RelAuxilioCancelamentoNotaPdf.php?Almoxarifado=$Almoxarifado&Localizacao=$Localizacao&Material=$Material[$i]&NotaFiscal=".$NotaFiscal."&AnoNota=".$AnoNota."&Procedimento=M";
												if(!$MensagemMovimentacao) {
														$MensagemMovimentacao = "A Retirada do Item $DescricaoMat[1] não poderá ser efetuada, pois ele está presente em movimentações posteriores a sua entrada no sistema. Deverá(ão) ser desfeita(s) $MensagemMov.";
														if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
														$MensagemMovimentacao .= " Utilize o relatório de <a href=\"$Url\">Auxílio para Manutenção de Nota Fiscal</a> para identificar estas movimentações";
												}else{
														$MensagemMovimentacao .= "<BR><BR>A Retirada do Item $DescricaoMat[1] não poderá ser efetuada, pois ele está presente em movimentações posteriores a sua entrada no sistema. Deverá(ão) ser desfeita(s) $MensagemMov.";
														if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
														$MensagemMovimentacao .= " Utilize o relatório de <a href=\"$Url\">Auxílio para Manutenção de Nota Fiscal</a> para identificar estas movimentações";
												}
												$MaterialTestado != $Material[$i];
												$MensagemMov    = null;
												$ItemNaMens     = null;
												# Seta Null nas variáveis de repetição de mensagens genéricas, para que possam aparecer novamente para novos materiais
												$MensMov9       = null;
												$MensMov10      = null;
												$MensMov11      = null;
												$MensMov13      = null;
												$MensMov14      = null;
												$MensMov15      = null;
												$MensMov16      = null;
												$MensMov17      = null;
												$MensMov23      = null;
												$MensMov24      = null;
												$MensMov26      = null;
												$MensMov27      = null;
												$MensMov29      = null;
												$MensMov30      = null;
												$MensMov31      = null;
												$MensMov37      = null;
												$ItemNaMensagem = 1;
										}
								}
						}
						if($Existe == "S"){
								$Mens      = 1;
								$Tipo      = 2;
								$Virgula   = 2;
								$DescMat   = explode($SimboloConcatenacaoDesc,$DescMaterial[$Posicao]);
								$Mensagem  = "A Retirada do Item não poderá ser efetuada, pois o item $DescMat[1] irá ficar com quantidade negativa";
						}elseif ($ItemNaMensagem){
								$Mens      = 1;
								$Tipo      = 2;
								$Mensagem = $MensagemMovimentacao;
						}else{
                $Qtd = 0;
								for( $i=0; $i< count($ItemNotaFiscal); $i++ ){
										if( $CheckItem[$i] == "" ){
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
										}else{
												# Verifica se existe o item em estoque #
												$sql  = "SELECT COUNT(CMATEPSEQU) FROM SFPC.TBARMAZENAMENTOMATERIAL ";
												$sql .= " WHERE CLOCMACODI = $Localizacao AND CMATEPSEQU = $Material[$i]";
												$res  = $db->query($sql);
												if( db::isError($res) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$QtdEst = $res->fetchRow();
														if( $QtdEst[0] > 0 ){
																# Monta um Array para Deletar itens da Nota Fiscal(NF) #
																if( $_SESSION['ItemDelete'] == "" or ! in_array($Material[$i].$SimboloConcatenacaoArray.$DescMaterial[$i].$SimboloConcatenacaoArray.$Localizacao.$SimboloConcatenacaoArray.$Quantidade[$i].$SimboloConcatenacaoArray.$ValorUnitario[$i],$_SESSION['ItemDelete']) ){
																		$_SESSION['ItemDelete'][count($_SESSION['ItemDelete'])] = $Material[$i].$SimboloConcatenacaoArray.$DescMaterial[$i].$SimboloConcatenacaoArray.$Localizacao.$SimboloConcatenacaoArray.$Quantidade[$i].$SimboloConcatenacaoArray.$ValorUnitario[$i];
																}
														}
												}
										}
								}
								if(count($ItemNotaFiscal) >= 1 ){
										$ItemNotaFiscal = array_slice($ItemNotaFiscal,0,$Qtd);
										$Material       = array_slice($Material,0,$Qtd);
										$DescMaterial   = array_slice($DescMaterial,0,$Qtd);
										$Unidade        = array_slice($Unidade,0,$Qtd);
                    $SituacaoMaterial = array_slice($SituacaoMaterial,0,$Qtd);
										$Quantidade     = array_slice($Quantidade,0,$Qtd);
										$ValorUnitario  = array_slice($ValorUnitario,0,$Qtd);
										$ValorTotal     = array_slice($ValorTotal,0,$Qtd);
								}else{
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
						$db->disconnect();
				}
		}
		unset($_SESSION['item']);
}elseif($Botao == "RetirarEmpenho"){
		if(count($Empenhos) != 0){
				for($i=0; $i< count($Empenhos); $i++){
						if($CheckEmp[$i] == ""){
								$Qtd++;
								$CheckEmp[$i]     = "";
								$Empenhos[$Qtd-1] = $Empenhos[$i];
						}
				}
				if(count($Empenhos) > 1){
						$Empenhos = array_slice($Empenhos,0,$Qtd);
				}else{
						unset($Empenhos);
				}
		}
}elseif($Botao == "Alterar"){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if($Almoxarifado == ""){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialManterExcluir.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
		}
		if( ($Localizacao == "") && ($CarregaLocalizacao == 'N') ){
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Localização";
		}elseif($Localizacao == ""){
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialManterExcluir.Localizacao.focus();\" class=\"titulo2\">Localização</a>";
		}
		if($NumeroNota == ""){
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialManterExcluir.NumeroNota.focus();\" class=\"titulo2\">Número da Nota</a>";
		}else{
				if(!SoNumeros($NumeroNota)){
						if ( $Mens == 1 ) { $Mensagem .= ", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialManterExcluir.NumeroNota.focus();\" class=\"titulo2\">Número da Nota Válido</a>";
				}
		}
		if( $SerieNota == "" ){
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialManterExcluir.SerieNota.focus();\" class=\"titulo2\">Série da Nota</a>";
		}
		if( $DataEmissao == "" ){
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialManterExcluir.DataEmissao.focus();\" class=\"titulo2\">Data de Emissão</a>";
		}else{
				$DataValida = ValidaData($DataEmissao) ;
				if( $DataValida != "" ){
						if ( $Mens == 1 ) { $Mensagem .= ", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialManterExcluir.DataEmissao.focus();\" class=\"titulo2\">Data da Nota Válida</a>";
				}elseif ( DataInvertida($DataEmissao) > $DataAtual ) {
						if ( $Mens == 1 ) { $Mensagem .= ", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialManterExcluir.DataEmissao.focus();\" class=\"titulo2\">Data de Emissão menor que a atual</a>";
				}else{
						list(,,$AnoEmissao)=explode("/",$DataEmissao);
            if($AnoEmissao < date('Y')-1){
						    if($Mens == 1){ $Mensagem .= ", "; }
						    $Mens      = 1;
						    $Tipo      = 2;
						    $Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialManterExcluir.DataEmissao.focus();\" class=\"titulo2\">Data de Emissão com ano posterior ou igual ao ano anterior</a>";
				    }
				}
		}

    $ValorTotalNotaCritica  = str_replace(",",".",str_replace(".","",$ValorTotalNota));
    if($ValorTotalNota == ""){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialManterExcluir.ValorTotalNota.focus();\" class=\"titulo2\">Valor Total da Nota Fiscal</a>";
		} elseif($ValorTotalNotaCritica != sprintf("%01.4f",str_replace(",",".",$ValorNota))){
      if($Mens == 1){ $Mensagem .= ", "; }
			$Mens      = 1;
			$Tipo      = 2;
      //$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialManterExcluir.ValorTotalNota.focus();\" class=\"titulo2\">Valor Total da Nota Fiscal diferente do Valor Total dos Itens da Nota Fiscal</a>";
      $Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialIncluir.ValorTotalNota.focus();\" class=\"titulo2\">Valor Total da Nota Fiscal diferente do Valor Total dos Itens da Nota Fiscal</a>";
    }

		if( $CNPJ_CPF == "" ){
				if( $Mens == 1 ){ $Mensagem.=", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "A opção CNPJ ou CPF";
		}else{
				if( $CNPJ_CPF == 1 ){ $TipoDocumento = "CNPJ"; }else{ $TipoDocumento = "CPF"; }
				if( $CnpjCpf == "" ){
						if( $Mens == 1 ){ $Mensagem.=", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialManterExcluir.CnpjCpf.focus();\" class=\"titulo2\">$TipoDocumento</a>";
				}else{
						if( $CNPJ_CPF == 1 ){
								$valida_cnpj = valida_CNPJ($CnpjCpf);
								if( $valida_cnpj === false ){
										$Mens      = 1;
										$Tipo      = 2;
										$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialManterExcluir.CnpjCpf.focus();\" class=\"titulo2\">CNPJ Válido</a>";
								}
						}else{
								$valida_cpf = valida_CPF($CnpjCpf);
								if( $valida_cpf === false ){
										$Mens      = 1;
										$Tipo      = 2;
										$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialManterExcluir.CnpjCpf.focus();\" class=\"titulo2\">CPF Válido</a>";
								}
						}
				}
				if( ( $CNPJ_CPF == 1 and $valida_cnpj === true ) or ( $CNPJ_CPF == 2 and $valida_cpf === true )  ){
						# Verifica se o CNPJ Existe no cadastro de Fornecedores Credenciados #
						$db   = Conexao();
						$sql  = "SELECT NFORCRRAZS FROM SFPC.TBFORNECEDORCREDENCIADO ";
						$sql .= " WHERE ";
						if( $CNPJ_CPF == 1 ){
								$sql .= " AFORCRCCGC = '$CnpjCpf' ";
						}else{
								$sql .= " AFORCRCCPF = '$CnpjCpf' ";
						}
						$res  = $db->query($sql);
						if( db::isError($res) ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								$rows = $res->numRows();
								if( $rows > 0 ){
										$linha       = $res->fetchRow();
										$RazaoSocial = $linha[0];
								}else{/*
										# Verifica se o Fornecedor de Estoque já está cadastrado #
										$db   = Conexao();
										$sql  = "SELECT EFORESRAZS FROM SFPC.TBFORNECEDORESTOQUE ";
										$sql .= "	WHERE ";
										if( $CNPJ_CPF == 1 ){
												$sql .= "	AFORESCCGC = '$CnpjCpf' ";
										}else{
											  $sql .= "	AFORESCCPF = '$CnpjCpf' ";
										}
										$res  = $db->query($sql);
										if( db::isError($res) ){
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												$rows = $res->numRows();
												if ($rows > 0){
														$linha       = $res->fetchRow();
														$RazaoSocial = $linha[0];
												}else{
														if( $Mens == 1 ){ $Mensagem.=", "; }
														$Mens     = 1;
														$Tipo     = 1;
														$Mensagem = "Fornecedor Não Cadastrado";
												}
										}
										$db->disconnect();*/
										if( $Mens == 1 ){ $Mensagem.=", "; }
										$Mens     = 1;
										$Tipo     = 1;
										$Mensagem = "Fornecedor Não Cadastrado";
								}
						}
				}
		}

		# Checa se nenhum número de empenho foi digitado #
		if(!$Empenhos){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Pelo menos um Número de Empenho";
		}

    /*
           Colocar para este caso se o material estiver inativo, verificar se a data de emissão do empenho > data de atualização do material. Logo,
           devemos considerar que os materiais inativos, não podem entrar mais através da entrada de nota fiscal. Porém se o item for inativo e a
           data de emissão do empenho <= data de alteração do material este item pode entrar no estoque por meio da entrada por nota fiscal.
        */
    // print_r($SituacaoMaterial);
    // echo "<BR>";
    // echo "in_array: ".in_array('I',$SituacaoMaterial);
    // echo "<BR>";

    # Verifica se existe algum item na nota #
		if( count($ItemNotaFiscal) == 0 ){
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Pelo menos um Item";
		}

    if(is_array($SituacaoMaterial) && $Mens == 0){
      if(in_array('I',$SituacaoMaterial)){ //Verifica se o material é Inativo
        $SequencialMateriais = implode(", ", $Material);

        for($i=0; $i< count($Empenhos); $i++){
          $Emp = explode(".",$Empenhos[$i]);
          $DataEmissaoEmp[$i] = $Emp[1];
        }

        // Obtém os materiais inativos válidos, ou seja, que foram inativados após a data de emissão do empenho.
        $db   = Conexao();
        $sql  = "SELECT CMATEPSEQU FROM SFPC.TBMATERIALPORTAL ";
        $sql .= "WHERE CMATEPSEQU IN ($SequencialMateriais) AND CMATEPSITU = 'I' ";

        if(count($DataEmissaoEmp) > 0){
          $sql .= "   AND (  ";
          $sql .= "   TMATEPULAT < '$DataEmissaoEmp[0]'";
          if(count($DataEmissaoEmp) > 1){
            for($i=1; $i < count($DataEmissaoEmp); $i++){
              $sql .= "   OR TMATEPULAT < '$DataEmissaoEmp[$i]' ";
            }
          }
          $sql .= "   )  ";
        }

        $res  = $db->query($sql);
        if( db::isError($res) ){
              ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
        }else{
          $QtdeMateriaisInativos = $res->numRows();
          if($QtdeMateriaisInativos > 0){
            $Mens      = 1;
            $Tipo      = 1;
            $Mensagem = "Favor remover o(s) material(is) inativo(s) com o(s) Cód. Red.: ";

            for ($i = 0; $i < $QtdeMateriaisInativos; $i++) {
                $Linha = $res->fetchRow();
                $MateriaisInativos[$i] = $Linha[0];
            }

            $IndiceMatInativosInvalidos = array();

            for($i = 0; $i < count($MateriaisInativos); $i++){
              $IndiceMatInativoInvalido = array_keys($Material, $MateriaisInativos[$i]); //Só existe um código para o cada elemento do array, portanto sempre retornará um único registro para cada busca no array.
              array_push($IndiceMatInativosInvalidos, $IndiceMatInativoInvalido[0]);
            }

            for($i = 0; $i < count($IndiceMatInativosInvalidos); $i++) {
              $indice = $IndiceMatInativosInvalidos[$i];
              $Mensagem .= "<a href=\"javascript: document.getElementById('codMaterial$indice').style.fontWeight='bold'; document.getElementsByName('CheckItem[$indice]').item(0).focus();\" class=\"titulo2\">$Material[$indice]</a>";

              if($i == count($IndiceMatInativosInvalidos) - 2) {
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

		if( $Mens == 0 ){
				# Verifica se existe algum material com armazenamento zerado #
				$db = Conexao();
				for( $i=0;$i< count($ItemNotaFiscal);$i++ ){
						$sql  = "SELECT COUNT(*) FROM SFPC.TBARMAZENAMENTOMATERIAL ";
						$sql .= " WHERE CMATEPSEQU = $Material[$i] AND CLOCMACODI = $Localizacao ";
						$res  = $db->query($sql);
						if( db::isError($res) ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								$Linha  = $res->fetchRow();
								$QtdRec = $Linha[0];
								if ($QtdRec > 0) {
										$sql  = "SELECT VARMATUMED FROM SFPC.TBARMAZENAMENTOMATERIAL ";
										$sql .= " WHERE CMATEPSEQU = $Material[$i] AND CLOCMACODI = $Localizacao ";
										$res  = $db->query($sql);
										if( db::isError($res) ){
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												$Linha = $res->fetchRow();
												$ValArmazenado = $Linha[0];
												if ( ($ValArmazenado == 0 or $ValArmazenado == "") and ($Existe == "") ) {
														$Existe  = "S";
														$Posicao = $i;
												}
										}
								}else{
										$Existe = "";
								}
						}
				}
				if($Existe == "S"){
						$Mens      = 1;
						$Tipo      = 2;
						$Virgula   = 2;
						$Mensagem  = "Alteração da Nota Fiscal não poderá ser efetuada, pois o item $DescMaterial[$Posicao] não tem valor unitário";
				}
				$db->disconnect();
		}


    //Verificando se existe valor de empenho disponível para a nota fiscal.
    if($Mens == 0){
      $ValorEmpTotal = 0;

      //Variaveis para obter o valor do(s) empenho(s)
      for($i=0; $i< count($Empenhos); $i++){
        //O índice 1 - refere-se a data de emissão do empenho/subempenho.
        $Emp = explode(".",$Empenhos[$i]);
        $ValorEmpTotal = $ValorEmpTotal + str_replace(",",".",$Emp[0]);
        $AnoEmp[$i]         = $Emp[2];
        $OrgaoEmp[$i]       = $Emp[3];
        $UnidadeEmp[$i]     = $Emp[4];
        $SequencialEmp[$i]  = $Emp[5];
        $ParcelaEmp[$i]     = $Emp[6];

        # Se o empenho não tiver parcela, recebe 0 para pesquisar no banco #
        if(!$ParcelaEmp[$i]) $ParcelaEmp[$i] = 0;

      }

      $db   = Conexao();
      $sql  = "SELECT SUM(VENTNFTOTA) FROM SFPC.TBENTRADANOTAFISCAL ENT ";
      $sql .= " WHERE (CALMPOCODI <> $Almoxarifado AND AENTNFANOE <> $AnoNota AND CENTNFCODI <> $NotaFiscal)  ";
      $sql .= " AND (FENTNFCANC IS NULL OR FENTNFCANC = 'N')  ";
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

        if(count($Empenhos) > 1){
          for($i=1; $i < count($Empenhos); $i++){
            $sql .= "      OR   ";
            $sql .= "       (   ";
            $sql .= "         EMP.ANFEMPANEM = ".$AnoEmp[$i]." AND EMP.CNFEMPOREM = ".$OrgaoEmp[$i]."   ";
            $sql .= "         AND EMP.CNFEMPUNEM = ".$UnidadeEmp[$i]." AND EMP.CNFEMPSEEM = ".$SequencialEmp[$i]." ";
            $sql .= "         AND EMP.CNFEMPPAEM = ".$ParcelaEmp[$i]."  ";
            $sql .= "       )  ";
          }
        }
        $sql .= "   )  ";

      $res  = $db->query($sql);
      if( db::isError($res) ){
            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
      }else{
         $Linha = $res->fetchRow();
         $ValorNotaTotal = $Linha[0];
      }

      $db->disconnect();



      //Verificar se o valor do(s) empenho(s) > [valor da(s) nota(s) fisca(is) associada(s) ao(s) empenho(s) + Valor da nota a ser inserida]
      $ValorDisponivel = $ValorEmpTotal - ($ValorNotaTotal + $ValorNota);

      if($ValorDisponivel < -2){ //Está sendo colocado -2 para que possa entrar com valores de notas fiscais com uma diferença de até R$ 2,00 do valor do empenho/subempenho.
          $Mens      = 1;
          $Tipo      = 2;
          $Mensagem .= "Valor do(s) empenhos disponível(is) menor do que valor da nota fiscal";
      }
    }

    //Faz últimas validações e alteração a partir daqui
    if($Mens == 0){
				if( $_SESSION['_cgrempcodi_'] != 0 ){
						$db = Conexao();
						# Verifica se com a retirada do item a quantidade e o valor ficaram negativos #
						if( count($ItemNotaFiscal) != 0 ){
								# Verifica se o Fornecedor de Estoque é Credenciado #
								$sql  = "SELECT AFORCRSEQU FROM SFPC.TBFORNECEDORCREDENCIADO ";
								$sql .= " WHERE ";
								if( $CNPJ_CPF == 1 ){
										$sql .= "	AFORCRCCGC = '$CnpjCpf' ";
								}else{
										$sql .= "	AFORCRCCPF = '$CnpjCpf' ";
								}
								$res  = $db->query($sql);
								if( db::isError($res) ){
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$rows = $res->numRows();
										if( $rows > 0 ){
												$Linha = $res->fetchRow();
												$FornecedorSequ = $Linha[0];
										}else{/*
												# Verifica se o Fornecedor de Estoque já está cadastrado #
												$sql  = "SELECT CFORESCODI FROM SFPC.TBFORNECEDORESTOQUE ";
												$sql .= "	WHERE ";
												if( $CNPJ_CPF == 1 ){
														$sql .= "	AFORESCCGC = '$CnpjCpf' ";
												}else{
														$sql .= "	AFORESCCPF = '$CnpjCpf' ";
												}
												$res  = $db->query($sql);
												if( db::isError($res) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$rows = $res->numRows();
														if( $rows > 0 ){
																$Linha       = $res->fetchRow();
																$FornecedorCodi = $Linha[0];
														}
												}*/
														EmailErro(__FILE__."- Fornecedor não encontrado.", __FILE__, __LINE__, "Fornecedor informado não foi encontrado em SFPC.TBFORNECEDORCREDENCIADO.\n\nSequencial do fornecedor informado: '".$FornecedorSequ."'\n\nVerificar se o dado informado pelo sistema foi correto ou se há algum fornecedor que não foi migrado de SFPC.TBFORNECEDORESTOQUE para SFPC.TBFORNECEDORCREDENCIADO corretamente.");
										}
										if( (!$FornecedorCodi) and (!$FornecedorSequ) ){
												if( $Mens == 1 ){ $Mensagem .= ", "; }
												$Mens      = 1;
												$Tipo      = 2;
												$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialManterExcluir.CnpjCpf.focus();\" class=\"titulo2\">Fornecedor Cadastrado</a>";
										}else{
												if( $NumeroNota != "" and SoNumeros($NumeroNota) ){
														# Verifica se já existe alguma nota com o mesmo número/série do mesmo fornecedor #
														$sql  = "SELECT COUNT(*) AS QTD FROM SFPC.TBENTRADANOTAFISCAL ";
														$sql .= "WHERE AENTNFNOTA = $NumeroNota AND AENTNFSERI = '$SerieNota' ";
														if($FornecedorSequ) {
																$sql .= "AND AFORCRSEQU = $FornecedorSequ ";
																$Fornecedor = $FornecedorSequ;
														}else{
																$sql .= "AND CFORESCODI = $FornecedorCodi ";
																$Fornecedor = $FornecedorCodi;
														}
														$sql .= "  AND (FENTNFCANC IS NULL OR FENTNFCANC = 'N')";
														$res  = $db->query($sql);
														if( db::isError($res) ){
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														}else{
																$Linha  = $res->fetchRow();
																if(  ( ($Linha[0] > 0) and ( ($NumeroNota."-".$SerieNota) != $NotaAnterior) ) or ( ($Linha[0] > 0) and ( ($FornecedorAnterior) != $Fornecedor) )  ){
																		$Mens      = 1;
																		$Tipo      = 2;
																		$Mensagem = "<a href=\"javascript:document.CadNotaFiscalMaterialManterExcluir.NumeroNota.focus();\" class=\"titulo2\">Número da Nota Fiscal já cadastrado para este Fornecedor</a>";
																}
														}
												}
										}
								}

                #valida os empenhos
                //Obtem os empenhos sem data de emissão já cadastrados
                $SqlempCad = "SELECT ANFEMPANEM,  CNFEMPOREM, CNFEMPUNEM, CNFEMPSEEM, CNFEMPPAEM FROM SFPC.TBNOTAFISCALEMPENHO ";
                $SqlempCad .= " WHERE CALMPOCODI = $Almoxarifado";
                $SqlempCad .= " AND AENTNFANOE = $AnoNota";
                $SqlempCad .= " AND CENTNFCODI = $NotaFiscal";

                $resultEmpCad = $db->query($SqlempCad);

                if( db::isError($resultEmp) ){
                  $Rollback = 1;
                  $db->query("ROLLBACK");
                  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $Sqlemp");
                } else {
                  $EmpenhosCadastrados = array();

                  while($EmpenhosCad = $resultEmpCad->fetchRow()) {
                    $AnoEmpCad        = $EmpenhosCad[0];
                    $OrgaoEmpCad      = $EmpenhosCad[1];
                    $UnidadeEmpCad    = $EmpenhosCad[2];
                    $SequencialEmpCad = $EmpenhosCad[3];
                    $ParcelaEmpCad    = $EmpenhosCad[4];

                    $EmpenhosCadastrados[] = "$AnoEmpCad.$OrgaoEmpCad.$UnidadeEmpCad.$SequencialEmpCad.$ParcelaEmpCad";
                  }
                }

                /*
                for($i=0; $i < count($Empenhos) and !$Rollback; $i++){
                  # Separa Ano, Órgão, Unidade, Sequencial e Parcela #
                  $Emp = explode(".",$Empenhos[$i]);
                  $AnoEmp        = $Emp[2];
                  $OrgaoEmp      = $Emp[3];
                  $UnidadeEmp    = $Emp[4];
                  $SequencialEmp = $Emp[5];
                  $ParcelaEmp    = $Emp[6];

                  if($ParcelaEmp){
                    $EmpenhoSemDataEmissao = "$AnoEmp.$OrgaoEmp.$UnidadeEmp.$SequencialEmp.$ParcelaEmp";
                  }else{
                    $EmpenhoSemDataEmissao = "$AnoEmp.$OrgaoEmp.$UnidadeEmp.$SequencialEmp";
                  }

                  if(!in_array($EmpenhoSemDataEmissao, $EmpenhosCadastrados)){
                    $Sqlemp  = "SELECT COUNT(*) FROM SFPC.TBNOTAFISCALEMPENHO ";
                    $Sqlemp .= "WHERE CALMPOCODI = $Almoxarifado AND AENTNFANOE = $AnoNota AND CENTNFCODI = $NotaFiscal ";
                    $Sqlemp .= "AND ANFEMPANEM = $AnoEmp AND CNFEMPOREM = $OrgaoEmp AND CNFEMPUNEM = $UnidadeEmp ";
                    $Sqlemp .= "AND CNFEMPSEEM = $SequencialEmp ";

                    $resultEmp = $db->query($Sqlemp);

                    if( db::isError($resultEmp) ){
                        $Rollback = 1;
                        $db->query("ROLLBACK");
                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $Sqlemp");
                    } else {
                      $Qtde  = $resultEmp->fetchRow();
                      $QtdeEmpenhos = $Qtde[0]; //A variavel $QtdeEmpenhos é utilizada para obter a quantidade de empenhos relacionadas com a nota fiscal a ser alterada do almoxarifado. E será permitido a alteração da nota a menos que o Select tenha retornado 0 para está variavel.
                      if($QtdeEmpenhos > 0){ //and count($Empenhos) > $QtdeEmpenhos){
                        $Mens      = 1;
                        $Tipo      = 2;

                        //Deve-se colocar a seguinte informação: Ano, Órgão, Unidade, Sequencial apenas com virgula, pois o programa transforma a última virgula na letra 'e'.
                        $Mensagem = "Número de Empenho(Ano, Órgão, Unidade, Sequencial) já cadastrado para esta nota fiscal";
                      }
                    }
                  }
                }
                */
                for($i=0; $i < count($Empenhos) and !$Rollback; $i++){ //Só poder um único empenho, isto é, o conjunto: ano, orgão, unidade e sequencial do empenho, logo QtdeEmpenhos deve ser igual a 1
                  # Separa Ano, Órgão, Unidade, Sequencial e Parcela #
                  $Emp = explode(".",$Empenhos[$i]);
                  $AnoEmp        = $Emp[2];
                  $OrgaoEmp      = $Emp[3];
                  $UnidadeEmp    = $Emp[4];
                  $SequencialEmp = $Emp[5];
                  $ParcelaEmp = $Emp[6];


                  $EmpenhoSemDataEmissao = "$AnoEmp.$OrgaoEmp.$UnidadeEmp.$SequencialEmp.$ParcelaEmp";


                  $QtdeEmpenhos = 0;

                  for($j=0; $j < count($Empenhos) and !$Rollback and QtdeEmpenhos <= 1; $j++){ //Só poder um único empenho, isto é, o conjunto: ano, orgão, unidade e sequencial do empenho, logo QtdeEmpenhos deve ser igual a 1
                    $Emp = explode(".",$Empenhos[$j]);
                    $AnoEmpItem        = $Emp[2];
                    $OrgaoEmpItem      = $Emp[3];
                    $UnidadeEmpItem    = $Emp[4];
                    $SequencialEmpItem = $Emp[5];
                    $ParcelaEmpItem = $Emp[6];

                    $ItemEmpenhoSemDataEmissao = "$AnoEmpItem.$OrgaoEmpItem.$UnidadeEmpItem.$SequencialEmpItem.$ParcelaEmpItem";

                    if($EmpenhoSemDataEmissao == $ItemEmpenhoSemDataEmissao){
                      $QtdeEmpenhos++;
                    }
                  }

                  if($QtdeEmpenhos > 1){
                    break;
                  }
                }
                if($QtdeEmpenhos > 1){
                  $Mens      = 1;
                  $Tipo      = 2;

                  //Deve-se colocar a seguinte informação: Ano, Órgão, Unidade, Sequencial apenas com virgula, pois o programa transforma a última virgula na letra 'e'.
                  $Mensagem = "Número de Empenho(Ano, Órgão, Unidade, Sequencial, Parcela) já cadastrado para esta nota fiscal";
                }


								if($Mens==0){
										$DataGravacao = date("Y-m-d H:i:s");
										$db->query("BEGIN TRANSACTION");
										for( $i=0; $i< count($ItemNotaFiscal); $i++ ){
												if( $CheckItem[$i] != "" ){
														# Resgata os dados em armazenamentomaterial do material corrente e trava campos para evitar alterações concorrentes#
														$sqlarmat  = "SELECT AARMATQTDE FROM SFPC.TBARMAZENAMENTOMATERIAL ";
														$sqlarmat .= " WHERE CMATEPSEQU = $Material[$i] AND CLOCMACODI = $Localizacao ";
														$sqlarmat .= "   FOR UPDATE ";
														$resarmat  = $db->query($sqlarmat);
														if( db::isError($resarmat) ){
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlarmat");
														}else{
																$Linhaarmat 	 = $resarmat->fetchRow();
																$QuantidadeEst = str_replace(",",".",$Linhaarmat[0]);
														}

														# Verifica se com a retirada a quantidade e o valor ficaram negativos #
														$QuantidadeVerifica = ($QuantidadeEst - $Quantidade[$i]);
														if($QuantidadeVerifica < 0){
																$Existe  = "S";
																$Posicao = $i;
														}
												}
										}
										if( $Existe == "S" ){ //a quantidade de alguma movimentação fica negativa com a retirada
												$Mens      = 1;
												$Tipo      = 2;
												$Virgula   = 2;
												$Mensagem  = "Alteração da Nota Fiscal não poderá ser efetuada, pois o item $DescMaterial[$Posicao] irá ficar com quantidade negativa";
												$db->query("ROLLBACK");
												$db->query("END TRANSACTION");
												$db->disconnect();
										}else{
												# Verifica se o fornecedor de estoque é credenciado #
												$sqlfor  = "SELECT AFORCRSEQU FROM SFPC.TBFORNECEDORCREDENCIADO ";
												if( $CNPJ_CPF == 1 ){
													$sql .= " AFORCRCCGC = '$CnpjCpf' ";
												}else{
													$sql .= " AFORCRCCPF = '$CnpjCpf' ";
												}
												$resfor  = $db->query($sqlfor);
												if( db::isError($resfor) ){
														$Mens = 1;
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlfor");
														$db->query("ROLLBACK");
														$db->query("END TRANSACTION");
														$db->disconnect();
												}else{
														$rows = $resfor->fetchRow();
														if($rows != ""){
																$FornecedorSequ = $rows[0];
														}else{/*
																# Verifica se o Fornecedor de Estoque já está cadastrado #
																$sqlfor  = "SELECT CFORESCODI FROM SFPC.TBFORNECEDORESTOQUE ";
																$sqlfor .= " WHERE AFORESCCGC = '$CnpjCpf' OR AFORESCCPF = '$CnpjCpf' ";
																$resfor  = $db->query($sqlfor);
																if( db::isError($resfor) ){
																		$Mens = 1;
																		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlfor");
																		$db->query("ROLLBACK");
																		$db->query("END TRANSACTION");
																		$db->disconnect();
																}else{
																		$Linhafor       = $resfor->fetchRow();
																		$FornecedorCodi = $Linhafor[0];
																}*/
																$db->query("ROLLBACK");
																$db->query("END TRANSACTION");
																$db->disconnect();
																EmailErro(__FILE__."- Fornecedor não encontrado.", __FILE__, __LINE__, "Fornecedor informado não foi encontrado em SFPC.TBFORNECEDORCREDENCIADO.\n\nCPF/CNPJ do fornecedor informado: '".$CnpjCpf."'\n\nSQL: ".$sqlfor);
														}
												}
										}
								}

								if($Mens == 0){
										# Atualiza a Nota Fiscal #
										$sql  = "UPDATE SFPC.TBENTRADANOTAFISCAL ";
										$sql .= "   SET AENTNFNOTA = $NumeroNota, AENTNFSERI = '$SerieNota', ";
										$sql .= "       DENTNFEMIS = '".DataInvertida($DataEmissao)."', DENTNFENTR = '".DataInvertida($DataEntrada)."', ";
										$sql .= "       CGREMPCODI = ".$_SESSION['_cgrempcodi_'].", ";
										$sql .= "       CUSUPOCODI = ".$_SESSION['_cusupocodi_'].", TENTNFULAT = '".$DataGravacao."', ";
										if($FornecedorSequ != ""){
												$sql .= " AFORCRSEQU = $FornecedorSequ, CFORESCODI = NULL ";
										}else{
												$sql .=  "CFORESCODI = $FornecedorCodi, AFORCRSEQU = NULL ";
										}
										$sql .= " WHERE CALMPOCODI = $Almoxarifado AND AENTNFANOE = $AnoNota ";
										$sql .= "   AND CENTNFCODI = $NotaFiscal	";
										$res  = $db->query($sql);
										if( db::isError($res) ){
												$Rollback = 1;
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												$db->query("ROLLBACK");
												$db->query("END TRANSACTION");
												$db->disconnect();
										}else{
												# Apaga empenhos relativos a esta nota para posterior re-inclusão #
												$sqldelemp  = "DELETE FROM SFPC.TBNOTAFISCALEMPENHO ";
												$sqldelemp .= " WHERE CALMPOCODI = $Almoxarifado ";
												$sqldelemp .= "   AND AENTNFANOE = $AnoNota ";
												$sqldelemp .= "   AND CENTNFCODI = $NotaFiscal ";
												$resdelemp  = $db->query($sqldelemp);
												if( db::isError($resdelemp) ){
														$Rollback = 1;
														$db->query("ROLLBACK");
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqldelemp");
												}else{
														# Insere os Empenhos #
														for($i=0; $i < count($Empenhos) and !$Rollback; $i++){
																# Separa Ano, Órgão, Unidade, Sequencial e Parcela #
																$Emp = explode(".",$Empenhos[$i]);
																$AnoEmp        = $Emp[2];
																$OrgaoEmp      = $Emp[3];
																$UnidadeEmp    = $Emp[4];
																$SequencialEmp = $Emp[5];
																$ParcelaEmp    = $Emp[6];
																# Se o empenho não tiver parcela, recebe 0 para inserir no banco #
																if(!$ParcelaEmp) $ParcelaEmp = 0;
																# Insere empenho #
																$sqlemp  = "INSERT INTO SFPC.TBNOTAFISCALEMPENHO ( ";
																$sqlemp .= " CALMPOCODI, AENTNFANOE, CENTNFCODI, ";
																$sqlemp .= " ANFEMPANEM, CNFEMPOREM, CNFEMPUNEM, ";
																$sqlemp .= " CNFEMPSEEM, CNFEMPPAEM, TNFEMPULAT ";
																$sqlemp .= ") VALUES ( ";
																$sqlemp .= " $Almoxarifado, $AnoNota, $NotaFiscal, ";
																$sqlemp .= " $AnoEmp, $OrgaoEmp, $UnidadeEmp, ";
																$sqlemp .= " $SequencialEmp, $ParcelaEmp, '".$DataGravacao."' ) ";
																$resemp = $db->query($sqlemp);
																if( db::isError($resemp) ){
																		$Rollback = 1;
																		$db->query("ROLLBACK");
																		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlemp");
																}
														}
												}

												if($Rollback != 1){
														# Apaga item da NF #
														$ValorNotaDel = 0;
														for( $i=0; $i< count($_SESSION['ItemDelete']); $i++ ){
																$Dados            = split($SimboloConcatenacaoArray,$_SESSION['ItemDelete'][$i]);
																$MaterialDel      = $Dados[0];
																$DescMaterialDel  = $Dados[1];
																$LocalizacaoDel   = $Dados[2];
																$QuantidadeDel    = str_replace(",",".",$Dados[3]);
																$ValorUnitarioDel = str_replace(",",".",$Dados[4]);
																$DataHora[$i]     = $Dados[5]; // TimeStamp da inclusão do item da nota
																$ValorTotalDel    = ($QuantidadeDel * $ValorUnitarioDel);

																# Apaga o item da nota fiscal #
																$sqlnf  = "DELETE FROM SFPC.TBITEMNOTAFISCAL ";
																$sqlnf .= " WHERE CALMPOCODI = $Almoxarifado AND AENTNFANOE = $AnoNota ";
																$sqlnf .= "   AND CENTNFCODI = $NotaFiscal AND CMATEPSEQU = $MaterialDel ";
																$resnf  = $db->query($sqlnf);
																if( db::isError($resnf) ){
																		$Rollback = 1;
																		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlnf");
																		$db->query("ROLLBACK");
																		$db->query("END TRANSACTION");
																		$db->disconnect();
																}else{
																		# Resgata o ultimo valor do item na ultima nota fiscal(nf) #
																		$sqlultvalnot  = "SELECT DISTINCT(VITENFUNIT) FROM SFPC.TBITEMNOTAFISCAL ";
																		$sqlultvalnot .= " WHERE CMATEPSEQU = $MaterialDel AND CALMPOCODI = $Almoxarifado ";
																		$sqlultvalnot .= "   AND AENTNFANOE = $AnoNota ";
																		$sqlultvalnot .= "   AND CENTNFCODI = ( SELECT MAX(A.CENTNFCODI) FROM SFPC.TBITEMNOTAFISCAL A, SFPC.TBENTRADANOTAFISCAL B ";
																		$sqlultvalnot .= "                       WHERE A.CENTNFCODI <> $NotaFiscal AND A.CMATEPSEQU = $MaterialDel ";
																		$sqlultvalnot .= "                         AND A.CALMPOCODI = $Almoxarifado AND A.AENTNFANOE = $AnoNota ";
																		$sqlultvalnot .= "                         AND A.CALMPOCODI = B.CALMPOCODI ";
																		$sqlultvalnot .= "                         AND A.AENTNFANOE = B.AENTNFANOE ";
																		$sqlultvalnot .= "                         AND A.CENTNFCODI = B.CENTNFCODI ";
																		$sqlultvalnot .= "                         AND B.FENTNFCANC <> 'S' )";
																		$resultvalnot  = $db->query($sqlultvalnot);
																		if( db::isError($resultvalnot) ){
																				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlultvalnot");
																		}else{
																				$Linhaultvalnot   = $resultvalnot->fetchRow();
																				$ValorUnitarioUlt = $Linhaultvalnot[0];
																				if( $ValorUnitarioUlt == "" ){
																				    $ValorUnitarioUlt = 0;
																				}
																		}

																		# Resgata os dados em SFPC.TBARMAZENAMENTOMATERIAL do material corrente #
																		$sqlarmat  = "SELECT AARMATQTDE, VARMATUMED, AARMATESTR, AARMATVIRT FROM SFPC.TBARMAZENAMENTOMATERIAL ";
																		$sqlarmat .= " WHERE CMATEPSEQU = $MaterialDel AND CLOCMACODI = $LocalizacaoDel ";
																		$resarmat  = $db->query($sqlarmat);
																		if( db::isError($resarmat) ){
																				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlarmat");
																		}else{
                                      $Linhaarmat 			= $resarmat->fetchRow();
                                      $QuantidadeEst    = str_replace(",",".",$Linhaarmat[0]);
                                      if ($QuantComp[$i]) { // Exceção - Se houver compensação gerada pela movimentação de ENTRADA POR CANCELAMENTO DE REQUISIÇÃO, subtrai da quantidade armazenada, para efetuar cálculo de valor médio
                                          $QuantidadeEst = $QuantidadeEst - $QuantComp[$i];
                                      }
                                      $ValorUnitarioEst = str_replace(",",".",$Linhaarmat[1]);
                                      $decQtdEstoqueReal   = str_replace(",",".",$Linhaarmat[2]);
                                      $decQtdEstoqueVirtual   = str_replace(",",".",$Linhaarmat[3]);
                                      $ValorTotalEst    = ($QuantidadeEst * $ValorUnitarioEst);

                                      if ($decQtdEstoqueReal == null || $decQtdEstoqueReal == '') {
                                        $decQtdEstoqueReal = 0;
                                      }

                                      if ($decQtdEstoqueVirtual == null || $decQtdEstoqueVirtual == '') {
                                        $decQtdEstoqueVirtual = 0;
                                      }
																		}

																		# Calcula o valor medio a partir do totalizador do estoque atual e o totalizador do item da nota #
																		$VerificaQuantidadeMedio = ($QuantidadeEst - $QuantidadeDel);
																		if($VerificaQuantidadeMedio == 0){
																				$sql  = "select distinct vmovmaumed from sfpc.tbmovimentacaomaterial where calmpocodi = $Almoxarifado ";
																				$sql .= "and cmatepsequ = $MaterialDel ";
																				$sql .= "and (vmovmaumed is not null and vmovmaumed > 0) ";
																				$sql .= "and tmovmaulat = (select max(tmovmaulat) from sfpc.tbmovimentacaomaterial where calmpocodi = $Almoxarifado and cmatepsequ = $MaterialDel) ";
																				$res = $db->query($sql);

																				if( db::isError($res) ){
																					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                        }else{
                                          $Linha = $res->fetchRow();
                                          $decValorMedio = $Linha[0];
																				}
																		}else{
																				$ValorMedio    = ( $ValorTotalEst - $ValorTotalDel ) / ( $QuantidadeEst - $QuantidadeDel );
																				$decValorMedio = str_replace(",",".",$ValorMedio);
																		}

																		# Se não existir nota p/ aquele item, o ultimo valor assume o valor medio #
																		if($ValorUnitarioUlt == 0){
																				$ValorUnitarioUlt = $decValorMedio;
																		}

																		# Atualiza o valor unitário medio e o ultimo valor de compra de cada item #
																		$sqlupdarmat  = "UPDATE SFPC.TBARMAZENAMENTOMATERIAL ";
																		$sqlupdarmat .= "   SET AARMATQTDE = ($QuantidadeEst - $QuantidadeDel), ";

                                    if($EstoqueVirtual == 'S'){
                                      $sqlupdarmat .= "       AARMATVIRT = ($decQtdEstoqueVirtual - $QuantidadeDel), ";
                                    } else {
                                      $sqlupdarmat .= "       AARMATESTR = ($decQtdEstoqueReal - $QuantidadeDel), ";
                                    }

																		$sqlupdarmat .= "       VARMATUMED = $decValorMedio, ";
																		$sqlupdarmat .= "       VARMATULTC = $ValorUnitarioUlt, CGREMPCODI = ".$_SESSION['_cgrempcodi_'].", ";
																		$sqlupdarmat .= "       CUSUPOCODI = ".$_SESSION['_cusupocodi_'].", TARMATULAT = '".$DataGravacao."' ";
																		$sqlupdarmat .= " WHERE CMATEPSEQU = $MaterialDel AND CLOCMACODI = $LocalizacaoDel ";
																		$resupdarmat = $db->query($sqlupdarmat);
																		if( db::isError($resupdarmat) ){
																				$Rollback = 1;
																				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlupdarmat");
																				$db->query("ROLLBACK");
																				$db->query("END TRANSACTION");
																				$db->disconnect();
																		}else{
																				# Pega o máximo valor do movimento de material #
																				$sqlmaxmov  = "SELECT MAX(CMOVMACODI) AS CODIGO FROM SFPC.TBMOVIMENTACAOMATERIAL ";
																				$sqlmaxmov .= " WHERE CALMPOCODI = $Almoxarifado AND AMOVMAANOM = $AnoExercicio ";
																				//$sqlmaxmov .= "   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																				$resmaxmov  = $db->query($sqlmaxmov);
																				if( db::isError($resmaxmov) ){
																						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmaxmov");
																				}else{
																						$Linhamaxmov = $resmaxmov->fetchRow();
																						$Movimento   = $Linhamaxmov[0] + 1;
																				}

																				# Pega o Máximo valor do Movimento do Material do Tipo 8 - SAÍDA POR ALTERAÇÃO DE NOTA FISCAL #
																				$sqltipo  = "SELECT MAX(CMOVMACODT) FROM SFPC.TBMOVIMENTACAOMATERIAL";
																				$sqltipo .= " WHERE CALMPOCODI = $Almoxarifado AND AMOVMAANOM = $AnoExercicio ";
																				$sqltipo .= "   AND CTIPMVCODI = 8 ";
																				//$sqltipo .= "   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																				$restipo  = $db->query($sqltipo);
																				if( db::isError($restipo) ){
																				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqltipo");
																				}else{
																						$LinhaTipo     = $restipo->fetchRow();
																						$TipoMovimento = $LinhaTipo[0] + 1;
																				}

																				# Insere na tabela de Movimentação de Material do Tipo 8 - SAÍDA POR ALTERAÇÃO DE NOTA FISCAL #
																				$sqlmovmat  = "INSERT INTO SFPC.TBMOVIMENTACAOMATERIAL ( ";
																				$sqlmovmat .= "CALMPOCODI, AMOVMAANOM, CMOVMACODI, DMOVMAMOVI, ";
																				$sqlmovmat .= "CTIPMVCODI, CREQMASEQU, CMATEPSEQU, AMOVMAQTDM, ";
																				$sqlmovmat .= "VMOVMAVALO, VMOVMAUMED, CGREMPCODI, CUSUPOCODI, TMOVMAULAT, ";
																				$sqlmovmat .= "AENTNFANOE, CENTNFCODI, CMOVMACODT, AMOVMAMATR, ";
																				$sqlmovmat .= "NMOVMARESP ";
																				$sqlmovmat .= ") VALUES ( ";
																				$sqlmovmat .= "$Almoxarifado, $AnoExercicio, $Movimento, '".date('Y-m-d')."', ";
																				$sqlmovmat .= "8, NULL, $MaterialDel, $QuantidadeDel, ";
																				$sqlmovmat .= "$ValorUnitarioDel, $decValorMedio, ".$_SESSION['_cgrempcodi_'].", ".$_SESSION['_cusupocodi_'].", '".$DataGravacao."', ";
																				$sqlmovmat .= "$AnoNota, $NotaFiscal, $TipoMovimento, NULL, ";
																				$sqlmovmat .= "NULL )";
																				$resmovmat  = $db->query($sqlmovmat);
																				if( db::isError($resmovmat) ){
																						$Rollback = 1;
																						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmovmat");
																						$db->query("ROLLBACK");
																						$db->query("END TRANSACTION");
																						$db->disconnect();
																				}
																		}
																		# Atualiza o valor da nota fiscal através do valor do item deletado #
																		$ValorNotaDel = ($ValorNotaDel + $ValorTotalDel);
																}
														}
												}
										}

										if($Rollback != 1){
												# Atualiza o valor da nota através dos itens da nota #
												$decValorNota 		 = str_replace(",",".",$ValNota);
												$decValorNotaDel   = str_replace(",",".",$ValorNotaDel);
												$decValorTotalNota = ($decValorNota - $decValorNotaDel);
												$sql  = "UPDATE SFPC.TBENTRADANOTAFISCAL ";
												$sql .= "   SET VENTNFTOTA = $decValorTotalNota, CGREMPCODI = ".$_SESSION['_cgrempcodi_'].", ";
												$sql .= "       CUSUPOCODI = ".$_SESSION['_cusupocodi_'].", TENTNFULAT = '".$DataGravacao."' ";
												$sql .= " WHERE CENTNFCODI = $NotaFiscal AND CALMPOCODI = $Almoxarifado ";
												$sql .= "   AND AENTNFANOE = $AnoNota ";
												$res  = $db->query($sql);
												if( db::isError($res) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														$db->query("ROLLBACK");
														$db->query("END TRANSACTION");
														$db->disconnect();
												}else{
														$db->query("COMMIT");
														$db->query("END TRANSACTION");
														$db->disconnect();

														# Limpando os dados da nota #
														$TipoUsuario	   = "";
														$OrgaoUsuario	   = "";
														$CentroCusto	   = "";
														$NumeroNota      = "";
														$SerieNota       = "";
                            $EstoqueVirtual  = "";
														$DataEmissao     = "";
														$CNPJ_CPF        = "";
														$CnpjCpf         = "";
														$RazaoSocial     = "";
														$DataEntrada     = "";
														$ValorNota       = "";
														$Localizacao     = "";

														# Limpando variáveis de empenho #
														$CheckEmp        = "";
														unset($Empenhos);
														unset($_SESSION['Empenho']);

														# Limpandos os dados do item da nota #
														$CheckItem       = "";
														$Material        = "";
														$DescMaterial    = "";
														$Unidade         = "";
                            $SituacaoMaterial  = "";
														$Quantidade      = "";
														$ValorUnitario   = "";
														$ValorTotal      = "";
														$InicioPrograma  = "";
														unset($ItemNotaFiscal);
														unset($_SESSION['item']);
														unset($_SESSION['ItemDelete']);

														# Valores dos calculos #
														$ValInicial			 = 0;
														$QtdInicial 		 = 0;
														$SomaValorMedio  = 0;
														$SomaQtdMedio    = 0;
														$ValorMedio 		 = 0;
														$ValUnitarioItem = 0;
														$QuantidadeItem  = 0;

														# Redireciona para a tela de seleção #
														$Mensagem = "Nota Fiscal Alterada com Sucesso";
														$Url = "CadNotaFiscalMaterialManter.php?Mens=1&Tipo=1&Mensagem=".urlencode($Mensagem);
														if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
														header("Location: ".$Url);
														exit;
												}
										}
								}
						}
				}else{
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "O Usuário do grupo INTERNET não pode fazer inclusão de Nota Fiscal";
				}
		}
}

# Monta o array de itens da NF #
if( count($_SESSION['item']) != 0 ){
		sort($_SESSION['item']);
		if( $ItemNotaFiscal == "" ){
				for( $i=0;$i<count($_SESSION['item']);$i++ ){
						$ItemNotaFiscal[count($ItemNotaFiscal)] = $_SESSION['item'][$i];
				}
		}else{
				for($i=0;$i<count($ItemNotaFiscal);$i++){
						$DadosItem            = split($SimboloConcatenacaoArray,$ItemNotaFiscal[$i]);
						$SequencialItem[$i]   = $DadosItem[1];
				}
				for($i=0; $i<count($_SESSION['item']); $i++){
						$DadosSessao          = split($SimboloConcatenacaoArray,$_SESSION['item'][$i]);
						$SequencialSessao[$i] = $DadosSessao[1];
						if(!in_array($SequencialSessao[$i],$SequencialItem) ){
								$ItemNotaFiscal[count($ItemNotaFiscal)] = $_SESSION['item'][$i];
						}
				}
		}
		unset($_SESSION['item']);
}
?>
<html>
<?
# Carrega o layout padrão #
layout();
?>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.CadNotaFiscalMaterialManterExcluir.Botao.value = valor;
	document.CadNotaFiscalMaterialManterExcluir.submit();
}
function AbreJanela(url,largura,altura) {
	window.open(url,'pagina','status=no,scrollbars=yes,left=60,top=150,width='+largura+',height='+altura);
}
function AbreJanelaItem(url,largura,altura) {
	window.open(url,'paginaitem','status=no,scrollbars=yes,left=90,top=150,width='+largura+',height='+altura);
}
function AbreJanelaEmp(url,largura,altura) {
	window.open(url,'paginaemp','status=no,scrollbars=yes,left=90,top=150,width='+largura+',height='+altura);
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadNotaFiscalMaterialManterExcluir.php" method="post" name="CadNotaFiscalMaterialManterExcluir">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Nota Fiscal > Manter
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
		<td width="100"></td>
		<td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,$Virgula); ?></td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="100"></td>
		<td class="textonormal">
			<table  border="0" cellspacing="0" cellpadding="3" width="100%" summary="">
				<tr>
					<td class="textonormal">
						<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" width="100%" bgcolor="#FFFFFF" summary="">
							<tr>
								<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
									MANTER - NOTA FISCAL
								</td>
							</tr>
							<tr>
								<td class="textonormal">
									<p align="justify">
										Para atualizar a Nota Fiscal, preencha os dados abaixo e clique no botão "Alterar Nota Fiscal". <br>
										Para excluir os itens da nota fiscal, clique no botão "Excluir Item" e para retornar a tela anterior clique no botão "Voltar".<br>
									</p>
								</td>
							</tr>
							<tr>
								<td>
									<table class="textonormal" border="0" align="left" width="100%" summary="">
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Operação</td>
											<td class="textonormal">
												EXCLUSÃO DE ITEM(NS)
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Almoxarifado</td>
											<td class="textonormal">
												<?php
												$db   = Conexao();
												$sql  = "SELECT EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL WHERE CALMPOCODI = $Almoxarifado";
												$res  = $db->query($sql);
												if( db::isError($res) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$Linha = $res->fetchRow();
														echo "$Linha[0]<br>";
														echo "<input type=\"hidden\" name=\"Almoxarifado\" value=\"$Almoxarifado\">";
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
												if ($Almoxarifado != ""){
														# Mostra as Localizações de acordo com o Almoxarifado #
														$sql    = "SELECT A.CLOCMACODI, A.FLOCMAEQUI, A.ALOCMANEQU, ";
														$sql   .= "       A.ALOCMAPRAT, A.ALOCMACOLU, B.EARLOCDESC ";
														$sql   .= "  FROM SFPC.TBLOCALIZACAOMATERIAL A, SFPC.TBAREAALMOXARIFADO B ";
														$sql   .= " WHERE A.CALMPOCODI = $Almoxarifado AND A.FLOCMASITU = 'A'";
														$sql   .= "   AND A.CARLOCCODI = B.CARLOCCODI	";
														$sql   .= " ORDER BY B.EARLOCDESC DESC, A.FLOCMAEQUI, A.ALOCMANEQU, ";
														$sql   .= "          A.ALOCMAPRAT, A.ALOCMACOLU";
														$res  = $db->query($sql);
														if( db::isError($res) ){
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														}else{
																$Rows = $res->numRows();
																if( $Rows == 0 ){
																		echo "NENHUMA LOCALIZAÇÃO CADASTRADA PARA ESTE ALMOXARIFADO";
																		echo "<input type=\"hidden\" name=\"CarregaLocalizacao\" value=\"N\">";
																}else{
																	if($Rows == 1){
																			$Linha = $res->fetchRow();
																			if( $Linha[1] == "E" ){
																				  $Equipamento = "ESTANTE";
																			}if( $Linha[1] == "A" ){
																				  $Equipamento = "ARMÁRIO";
																			}if( $Linha[1] == "P" ){
																				  $Equipamento = "PALETE";
																			}
																			echo "ÁREA: $Linha[5] - $Equipamento - $Linha[2]: ESCANINHO $Linha[3]$Linha[4]";
																			$Localizacao = $Linha[0];
																			echo "<input type=\"hidden\" name=\"Localizacao\" value=\"$Localizacao\">";
																	}else{
																			$EquipamentoAntes = "";
																			$DescAreaAntes    = "";
																			echo "<select name=\"Localizacao\" class=\"textonormal\">\n";
																			echo "	<option value=\"\">Selecione uma Localização...</option>\n";
																				for( $i=0;$i< $Rows; $i++ ){
																						$Linha = $res->fetchRow();
																						$CodEquipamento = $Linha[2];
																						if($Linha[1] == "E"){
																								$Equipamento = "ESTANTE";
																						}if($Linha[1] == "A"){
																								$Equipamento = "ARMÁRIO";
																						}if($Linha[1] == "P"){
																								$Equipamento = "PALETE";
																						}
																						$NumeroEquip = $Linha[2];
																						$Prateleira  = $Linha[3];
																						$Coluna      = $Linha[4];
																						$DescArea    = $Linha[5];
																						if( $DescAreaAntes != $DescArea ){
																								echo"<option value=\"\">$DescArea</option>\n";
																								$Edentecao = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
																						}
																						if( $CodEquipamentoAntes != $CodEquipamento or $EquipamentoAntes != $Equipamento ){
																								echo"<option value=\"\">$Edentecao $Equipamento - $NumeroEquip</option>\n";
																						}
																						if( $Localizacao == $Linha[0] ){
																								echo"<option value=\"$Linha[0]\" selected>$Edentecao $Edentecao ESCANINHO $Prateleira$Coluna</option>\n";
																						}else{
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
												$db->disconnect();
												?>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Número da Nota</td>
											<td class="textonormal">
												<input type="text" name="NumeroNota" size="15" maxlength="10" value="<?php echo $NumeroNota; ?>" class="textonormal">
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Série da Nota</td>
											<td class="textonormal">
												<input type="text" name="SerieNota" size="10" maxlength="8" value="<?php echo $SerieNota; ?>" class="textonormal">
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Data de Emissão</td>
											<td class="textonormal">
												<?php $URL = "../calendario.php?Formulario=CadNotaFiscalMaterialManterExcluir&Campo=DataEmissao";?>
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
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Razão Social</td>
											<td class="textonormal"><?php echo $RazaoSocial; ?></td>
										</tr>
                    <tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Situação</td>
											<td class="textonormal">
											<?php
                        //Apenas notas fiscais não canceladas podem ser alteradas, portanto, a mesma, será virtual ou normal.
                        if($EstoqueVirtual == 'S'){
                          echo "Virtual";
                        } else {
                          echo "Normal";
                        }
											?>
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
												<?
													$ValorNota = 0;
													for( $i=0;$i<count($Quantidade);$i++ ){
															$decQuantidade    = str_replace(",",".",$Quantidade[$i]);
															$decValorUnitario = str_replace(",",".",$ValorUnitario[$i]);
															$decValorTotal    = str_replace(",",".",($decQuantidade * $decValorUnitario));
															$ValorTotal[$i]   = str_replace(",",".",$decValorTotal);
															$ValorNota        = str_replace(",",".",($ValorNota + $ValorTotal[$i]));
													}
													echo converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorNota)));
													?>
												<input type="hidden" name="ValorNota" value="<?php if ($ValorNota == ""){ echo 0; }else{ echo converte_valor_estoques(sprintf('%01.2f',str_replace(",",".",$ValorNota))); } ?>" class="textonormal">
											  <input type="hidden" name="EstoqueVirtual" value="<?php echo $EstoqueVirtual; ?>">
                      </td>
										</tr>
										<tr>
											<td class="textonormal" colspan="5">
												<table border="1" cellpadding="3" cellspacing="0" bgcolor="#bfdaf2" bordercolor="#75ADE6" width="100%" summary="">
													<tr>
                            <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="6">
															ITENS DA NOTA FISCAL
														</td>
													</tr>
													<?php
													if(count($ItemNotaFiscal) != 0){ sort($ItemNotaFiscal); }
													for($i=0; $i< count($ItemNotaFiscal); $i++){
															$Dados             = split($SimboloConcatenacaoArray,$ItemNotaFiscal[$i]);
															$DescMaterial[$i]  = $Dados[0];
															$Material[$i]      = $Dados[1];
															$Unidade[$i]       = $Dados[2];
                              $SituacaoMaterial[$i]  = $Dados[3];
															$Quantidade[$i]    = $Dados[4];
															$ValorUnitario[$i] = $Dados[5];
															$DataHora[$i]      = $Dados[6];

															# Variaveis para calculo de valores #
															$decQuantidade    = str_replace(",",".",$Quantidade[$i]);
															$decValorUnitario = str_replace(",",".",$ValorUnitario[$i]);
															$decValorTotal    = str_replace(",",".",($decQuantidade * $decValorUnitario));
															$ValorTotal[$i]   = str_replace(",",".",$decValorTotal);
															if($i == 0){
																	echo "		<tr>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\">DESCRIÇÃO DO MATERIAL</td>\n";
                                  echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" width=\"10%\">CÓD.RED.</td>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" width=\"5%\">UNIDADE</td>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" width=\"10%\">QUANTIDADE</td>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" width=\"5%\" align=\"center\">VALOR UNITÁRIO</td>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" width=\"5%\" align=\"center\">VALOR TOTAL</td>\n";
																	echo "		</tr>\n";
															}
															?>
													<tr>
														<td class="textonormal" width="60%">
															<input type="hidden" name="DataHora[<?php echo $i; ?>]" value="<?php echo $DataHora[$i]; ?>">
															<input type="hidden" name="ItemNotaFiscal[<?php echo $i; ?>]" value="<?php echo $ItemNotaFiscal[$i]; ?>">
															<input type="hidden" name="Material[<?php echo $i; ?>]" value="<?php echo $Material[$i]; ?>">
															<input type="checkbox" name="CheckItem[<?php echo $i; ?>]" value="<?php echo $i; ?>">
															<?
															$Url = "CadItemDetalhe.php?ProgramaOrigem=CadNotaFiscalMaterialManterExcluir&Material=$Material[$i];";
															if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
															?>
															<a href="javascript:AbreJanela('<?=$Url;?>',700,370);">
																<font color="#000000">
																	<?php
																	$Descricao = split($SimboloConcatenacaoDesc,$DescMaterial[$i]);
																	echo $Descricao[1];
																	?>
																</font>
															</a>
															<input type="hidden" name="DescMaterial[<?php echo $i; ?>]" value="<?php echo $DescMaterial[$i]; ?>">
                              <input type="hidden" name="SituacaoMaterial[<?php echo $i; ?>]" value="<?php echo $SituacaoMaterial[$i]; ?>">
														</td>
                            <td class="textonormal" width="10%" align="center">
															<div id="codMaterial<?php echo $i; ?>"><?php echo $Material[$i];?></div>
														</td>
														<td class="textonormal" width="10%" align="center">
															<?php echo $Unidade[$i];?>
															<input type="hidden" name="Unidade[<?php echo $i; ?>]" value="<?php echo $Unidade[$i]; ?>">
														</td>
														<td class="textonormal" align="right" width="10%">
															<?php if( $Quantidade[$i] == "" ){ echo 0; }else{ echo converte_quant(sprintf("%01.2f",str_replace(",",".",$Quantidade[$i]))); } ?>
															<input type="hidden" name="Quantidade[<?php echo $i; ?>]" value="<?php echo $Quantidade[$i]; ?>">
														</td>
														<td class="textonormal" align="right" width="10%">
															<?php if( $ValorUnitario[$i] == "" ){ echo 0; }else{ echo converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorUnitario[$i]))); } ?>
															<input type="hidden" name="ValorUnitario[<?php echo $i; ?>]" value="<?php echo $ValorUnitario[$i]; ?>">
														</td>
														<td class="textonormal" align="right" width="10%">
															<?php if( $ValorTotal[$i] == "" ){ echo 0; }else{ echo converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorTotal[$i]))); } ?>
															<input type="hidden" name="ValorTotal[<?php echo $i; ?>]" value="<?php echo $ValorTotal[$i]; ?>">
														</td>
													</tr>
													<?php } ?>
													<tr>
														<td class="textonormal" colspan="6" align="center">
															<input type="button" name="Retirar" value="Retirar Item" class="botao" onClick="javascript:enviar('Retirar');">
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
													for($i=0; $i< count($Empenhos); $i++){
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
															# separa Ano, Órgão, Unidade, Sequencial e Parcela #
															$Emp = explode(".",$Empenhos[$i]);
															$AnoEmp        = $Emp[2];
															$OrgaoEmp      = $Emp[3];
															$UnidadeEmp    = $Emp[4];
															$SequencialEmp = $Emp[5];
															$ParcelaEmp    = $Emp[6];
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
																	<?php echo $AnoEmp;?>
																</td>
																<td class="textonormal" align="center" width="10%">
																	<?php echo $OrgaoEmp;?>
																</td>
																<td class="textonormal" align="center" width="10%">
																	<?php echo $UnidadeEmp;?>
																</td>
																<td class="textonormal" align="center" width="10%">
																	<?php echo $SequencialEmp;?>
																</td>
																<td class="textonormal" align="center" width="10%">
																	<?php if($ParcelaEmp != 0){ echo $ParcelaEmp; }else{ echo "&nbsp;"; } ?>
																</td>
															</tr>
													<?php } ?>
													<tr>
														<td class="textonormal" colspan="6" align="center">
															<?php
																	$Url = "CadIncluirEmpenho.php?ProgramaOrigem=$ProgramaOrigem";
																	if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																	echo "<input type=\"button\" name=\"IncluirEmpenho\" value=\"Incluir Empenho\" class=\"botao\" onclick=\"javascript:AbreJanelaEmp('$Url',700,320);\">\n";
																	echo "<input type=\"button\" name=\"RetirarEmpenho\" value=\"Retirar Empenho\" class=\"botao\" onClick=\"javascript:enviar('RetirarEmpenho');\">\n";
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
									echo "<input type=\"hidden\" name=\"DataEntrada\" value=\"$DataEntrada\">";
                  echo "<input type=\"hidden\" name=\"DataUltimaAlteracao\" value=\"$DataUltimaAlteracao\">";
									?>
									<input type="hidden" name="NotaAnterior" value="<?php echo $NotaAnterior; ?>">
									<input type="hidden" name="FornecedorAnterior" value="<?php echo $FornecedorAnterior; ?>">
									<input type="hidden" name="Totalizou" value="">
									<input type="hidden" name="InicioPrograma" value="1">
									<input type="hidden" name="Montou" value="<?php echo $Montou; ?>">
									<input type="hidden" name="ValNota" value="<?php echo $ValNota; ?>">
									<input type="hidden" name="AnoNota" value="<?php echo $AnoNota; ?>">
									<input type="hidden" name="NotaFiscal" value="<?php echo $NotaFiscal; ?>">

                  <input type="hidden" name="ValorNota" value="<?php echo $ValorNota; ?>">

									<input type="hidden" name="Almoxarifado" value="<?php echo $Almoxarifado; ?>">
									<input type="hidden" name="RazaoSocial" value="<?php echo $RazaoSocial; ?>">
									<input type="button" name="Alterar" value="Alterar Nota Fiscal" class="botao" onClick="javascript:enviar('Alterar');">
									<input type="button" name="Voltar" value="Voltar" class="botao" onClick="javascript:enviar('Voltar');">
									<input type="hidden" name="Botao" value="">
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
