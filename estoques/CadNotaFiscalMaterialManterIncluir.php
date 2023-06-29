<?php
# ---------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadNotaFiscalMaterialManterIncluir.php
# Objetivo: Programa de Inclusão de Itens na Nota Fiscal a partir da Pesquisa
# Data:     08/09/2005
# Autor:    Altamiro Pedrosa
# OBS.:     Tabulação 2 espaços
#           Deixar os comentário pra validação de empenho obrigatório
# ---------------------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data      21/10/2005
# ---------------------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data      26/05/2006
# ---------------------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     08/06/2006 - Data de entrada pelo sistema, não mais pelo usuário
# ---------------------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     19/06/2006 - Checagem de quantidade e valor válido no Totalizar
# ---------------------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     27/07/2006 - Permitir mais de um empenho
# ---------------------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     06/11/2006 - Correção para não chutar o usuário após alteração de nota fiscal
# ---------------------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     14/12/2006 - strtoupper no número de série da nota
# ---------------------------------------------------------------------------------------
# Alterado: Carlos Abreu
# Data:     16/01/2007 - Correção para inserir AnoNota em vez de AnoMovimentacao no Ano da Nota 
#                        fiscal da tabela de movimentacao
# ---------------------------------------------------------------------------------------
# Alterado: Carlos Abreu
# Data:     22/03/2007 - Ajuste para restringir o ano de emissao entre o ano atual e o ano anterior
# ---------------------------------------------------------------------------------------
# Alterado: Carlos Abreu
# Data:     19/04/2007 - Inclusao de bloqueio para evitar movimentacoes anteriores a ultimo inventario
# ---------------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     10/01/2008 - Ajuste na query para evitar que a movimentação seja realizada no período anterior ao último inventário 
#                                do almoxarifado, ou seja, ajuste para buscar apenas o último sequencial e o último ano do inventário do almoxarifado.
# ---------------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     21/01/2008 - Ajuste na query da validação da movimentação, pois, deve-se permitir a alteração da nota fiscal com base na data e hora da última 
#                                 atualização da movimentação (entrada da nota fiscal) com a data e hora do fechamento do último inventário. 
#                                 E não levar em consideração a data de emissão da nota fiscal, como estava anteriormente.
# ---------------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     14/03/2008 - Alteração para que na alteração da nota fiscal seja possível a entrada de materiais inativos que foram cadastrados na licitação do 
#                                 SOFIN antes da inativação do material no portal de compras.
# ---------------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     27/03/2008 - Correção para exibir critica para não permitir que o usuário inclua mais de um empenho com no mesmo ano e com os mesmo orgãos, unidades e sequencial para a mesma nota fiscal do almoxarifado no ano de exercicío e correção da exibição do "Valor Total da Nota".
# ---------------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     07/04/2008 - Correção do foco quando o usuário receber criticas de quantidade e valor unitário inválidos ou em branco.
# ---------------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:      11/06/2008 - Alteração para informar o valor total da nota fiscal, para comparar com o valor total dos materiais.
# ---------------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:      07/07/2008 - Alteração para inserir no campo estoque virtual na tabela de armazenamento de material e flag para identificar uma nota fiscal Virtual.
# ---------------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:      11/07/2008 - Alteração para o obter empenhos válidos, ou seja, não nulos e que não sejam subempenhos. Além de obter o valor do empenho - valor anulado do empenho, caso este seja > 0.
# ---------------------------------------------------------------------------------------
# Alterado: Ariston Cordeiro / Rodrigo Melo
# Data:     30/07/2008 	- Alteração para obter empenhos / Subempenhos válidos para alterar uma nota fiscal (empenho/subempenho não anulado completamente)
# ---------------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:      01/08/2008 - Correção para calcular o valor dos empenhos apenas de notas fiscais não canceladas.
# ---------------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:      12/08/2008 - alteração para que possa entrar com valores de notas fiscais com uma diferença de até R$ 2,00 do valor do empenho/subempenho.
# ---------------------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:      11/09/2008 - Removido todos acessos a SFPC.TBFORNECEDORESTOQUE.
# ---------------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:      26/11/2008 - Corrigindo Programa para permitir entrada de empenhos com sequenciais diferentes, ou seja, diferentes subempenhos. 
#                                  Foi convecionado que um empenho com parcela igual a zero não será um subempenho, mas um empenho pois a chave primária da tabela foi 
#                                  alterada para incluir a parcela do subempenho. Desta forma quando a parcela tiver o valor 0 será um empenho e se tiver um valor 
#                                  diferente de 0 será um subempenho.
# ---------------------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:      01/12/2008	- Não permitir alteração de notas fiscais virtuais
#												- Redirecionar para página de pesquisa caso os dados do request http sejam perdidos
# ---------------------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:      04/12/2008	- Correções nas mensagens de erro de CPF e CNPJ inválidos
# ---------------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		24/10/2018
# Objetivo: Tarefa Redmine 73662
# ---------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/CadIncluirItem.php' );
AddMenuAcesso( '/estoques/CadItemDetalhe.php' );
AddMenuAcesso( '/estoques/CadIncluirEmpenho.php' );
AddMenuAcesso( '/estoques/CadNotaFiscalMaterialManter.php' );

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

$ProgramaOrigem="CadNotaFiscalMaterialManterIncluir";

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "POST"){
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
		if($DataEmissao  != ""){ $DataEmissao = FormataData($DataEmissao); }
    $DataUltimaAlteracao = $_POST['DataUltimaAlteracao'];
		$ValorNota         = $_POST['ValorNota'];
    $CentroCusto       = $_POST['CentroCusto'];
		$ValNota           = $_POST['ValNota'];
		$CheckItem         = $_POST['CheckItem'];
		$Material          = $_POST['Material'];
		$DescMaterial      = $_POST['DescMaterial'];
		$Unidade           = $_POST['Unidade'];
    $SituacaoMaterial  = $_POST['SituacaoMaterial'];
		$Quantidade        = $_POST['Quantidade'];		
		$ValorUnitario     = $_POST['ValorUnitario'];
		$TipoItem          = $_POST['TipoItem'];
		$ValorTotal        = $_POST['ValorTotal'];
    $ValorTotalNota    = $_POST['ValorTotalNota'];    
    $EstoqueVirtual    = $_POST['EstoqueVirtual'];
		$RazaoSocial       = $_POST['RazaoSocial'];
		$Empenhos          = $_POST['Empenhos'];		
		$CheckEmp          = $_POST['CheckEmp'];
		$Montou            = $_POST['Montou'];
    
		for( $i=0;$i<count($DescMaterial);$i++ ){				
        $ItemNotaFiscal[$i] = $DescMaterial[$i].$SimboloConcatenacaoArray.$Material[$i].$SimboloConcatenacaoArray.$Unidade[$i].$SimboloConcatenacaoArray.$SituacaoMaterial[$i].$SimboloConcatenacaoArray.str_replace(".","",$Quantidade[$i]).$SimboloConcatenacaoArray.str_replace(".","",$ValorUnitario[$i]).$SimboloConcatenacaoArray.$TipoItem[$i];
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

# Caso os dados do request http sejam perdidos, redirecionar para procura
if(is_null($Almoxarifado)){
		header("location: /portalcompras/estoques/CadNotaFiscalMaterialManter.php");
		exit;
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

if( $Botao == "Voltar" ){
		header("location: CadNotaFiscalMaterialManter.php");
		exit;
}

if( (!$Botao) and (!$Montou) ){
		# Limpa o array de itens da primeira vez que entra no programa #
		if ($InicioPrograma == "" ) { unset($_SESSION['item']); LimparSessaoNotaFiscal(); }
		# Pega os dados da Entrada por NF de acordo com o Sequencial #
		$db   = Conexao();
		$sql  = "SELECT A.AENTNFNOTA, A.AENTNFSERI, A.DENTNFENTR, ";
		$sql .= "       A.DENTNFEMIS, A.VENTNFTOTA, ";
		$sql .= "       B.AITENFQTDE, B.VITENFUNIT, ";
		$sql .= "       C.CMATEPSEQU, C.EMATEPDESC, D.EUNIDMSIGL, ";
		$sql .= "       A.AFORCRSEQU, A.CFORESCODI, A.TENTNFULAT, C.CMATEPSITU, A.FENTNFVIRT ";
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
		if( PEAR::isError($res) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Rows = $res->numRows();
				for($i=0;$i<$Rows;$i++){
						$Linha               = $res->fetchRow();
						$NumeroNota          = $Linha[0];
						$SerieNota           = $Linha[1];
						$DataEntrada         = DataBarra($Linha[2]);
						$DataEmissao         = DataBarra($Linha[3]);
						$ValNota             = str_replace(",",".",$Linha[4]);
						$Quantidade[$i]    = str_replace(".",",",$Linha[5]);
            $ValorUnitario[$i]   = str_replace(".",",",$Linha[6]);
						$Material[$i]        = $Linha[7];
						$DescMaterial[$i]    = RetiraAcentos($Linha[8]).$SimboloConcatenacaoDesc.str_replace("\"","”",$Linha[8]);
						$Unidade[$i]         = $Linha[9];
						$FornecedorSequ 	   = $Linha[10];
						$FornecedorCodi 	   = $Linha[11];
            $DataUltimaAlteracao = $Linha[12];
            $SituacaoMaterial[$i] = $Linha[13];
            $EstoqueVirtual      = $Linha[14];
            
						$ItemNotaFiscal[$i]  = $DescMaterial[$i].$SimboloConcatenacaoArray.$Material[$i].$SimboloConcatenacaoArray.$Unidade[$i].$SimboloConcatenacaoArray.$SituacaoMaterial[$i].$SimboloConcatenacaoArray.$Quantidade[$i].$SimboloConcatenacaoArray.$ValorUnitario[$i].$SimboloConcatenacaoArray."B";
						$Montou              = "S";
						$NotaAnterior        = $NumeroNota."-".$SerieNota;
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
				if(PEAR::isError($resemp)){
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
                if( PEAR::isError($res) ){
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

				if($FornecedorSequ != ""){
						# Verifica se o Fornecedor de Estoque é Credenciado #
						$sqlforn  = "SELECT NFORCRRAZS, AFORCRCCGC, AFORCRCCPF FROM SFPC.TBFORNECEDORCREDENCIADO ";
						$sqlforn .= " WHERE AFORCRSEQU = $FornecedorSequ ";
						$resforn  = $db->query($sqlforn);
						if(PEAR::isError($resforn)){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlforn");
						}else{
								$Linhaforn   = $resforn->fetchRow();
								$RazaoSocial = $Linhaforn[0];
								if($Linhaforn[1] != ""){
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
						if( PEAR::isError($resforn) ){
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
if( PEAR::isError($res) ){
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

if($EstoqueVirtual=="S"){
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem = "Notas Fiscais de estoque virtual não podem ser alterados";
}


if($Botao == "Verificar"){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if($CNPJ_CPF == ""){
				if( $Mens == 1 ){ $Mensagem.=", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "A opção CNPJ ou CPF";
		}else{
				if($CNPJ_CPF == 1){ $TipoDocumento = "CNPJ"; }else{ $TipoDocumento = "CPF"; }
				if($CnpjCpf == ""){
						if($Mens == 1){ $Mensagem.=", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialManterIncluir.CnpjCpf.focus();\" class=\"titulo2\">$TipoDocumento</a>";
				}else{
						if($CNPJ_CPF == 1){
								$valida_cnpj = valida_CNPJ($CnpjCpf);
								if($valida_cnpj === false){
										$Mens      = 1;
										$Tipo      = 2;
										$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialManterIncluir.CnpjCpf.focus();\" class=\"titulo2\">CNPJ Válido</a>";
								}
						}else{
								$valida_cpf = valida_CPF($CnpjCpf);
								if($valida_cpf === false){
										$Mens      = 1;
										$Tipo      = 2;
										$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialManterIncluir.CnpjCpf.focus();\" class=\"titulo2\">CPF Válido</a>";
								}
						}
				}
				if( ( $CNPJ_CPF == 1 and $valida_cnpj === true ) or ( $CNPJ_CPF == 2 and $valida_cpf === true )  ){
						$db   = Conexao();
						# Verifica se o CNPJ Existe no cadastro de Fornecedores Credenciados #
						$sql  = "SELECT NFORCRRAZS FROM SFPC.TBFORNECEDORCREDENCIADO ";
						$sql .= " WHERE ";
						if($CNPJ_CPF == 1){
								$sql .= " AFORCRCCGC = '$CnpjCpf' ";
						}else{
								$sql .= " AFORCRCCPF = '$CnpjCpf' ";
						}
						$res  = $db->query($sql);
						if(PEAR::isError($res)){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								$rows = $res->numRows();
								if($rows > 0){
										$linha       = $res->fetchRow();
										$RazaoSocial = $linha[0];
								}else{/*
										# Verifica se o Fornecedor de Estoque já está cadastrado #
										$db   = Conexao();
										$sql  = "SELECT EFORESRAZS FROM SFPC.TBFORNECEDORESTOQUE ";
										$sql .= "	WHERE ";
										if($CNPJ_CPF == 1){
												$sql .= "	AFORESCCGC = '$CnpjCpf' ";
										}else{
												$sql .= "	AFORESCCPF = '$CnpjCpf' ";
										}
										$res  = $db->query($sql);
										if(PEAR::isError($res)){
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												$rows = $res->numRows();
												if($rows > 0){
														$linha       = $res->fetchRow();
														$RazaoSocial = $linha[0];
												}else{
														if($Mens == 1){ $Mensagem.=", "; }
														$Mens     = 1;
														$Tipo     = 1;
														$Mensagem = "Fornecedor Não Cadastrado";
												}
										}
										$db->disconnect();*/
										if($Mens == 1){ $Mensagem.=", "; }
										$Mens     = 1;
										$Tipo     = 1;
										$Mensagem = "Fornecedor Não Cadastrado";
								}
						}
				}
		}
}

if($Botao == "Retirar"){
		if(count($ItemNotaFiscal) != 0){
				for($i=0; $i< count($ItemNotaFiscal); $i++){
						if($CheckItem[$i] == ""){
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
				if(count($ItemNotaFiscal) >= 1){
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
		unset($_SESSION['item']);
}

if($Botao == "Totalizar"){
		if(count($Quantidade) != 0){
				# Verifica se existe alguma quantidade igual a branco ou zero #
				for($i=0; $i<count($Quantidade); $i++){
						if ($TipoItem[$i] != "B") {
								if( (str_replace(",",".",$Quantidade[$i]) == 0 or $Quantidade[$i] == "") and $Existe == "" ){
										$Existe  = "S";
										$Posicao = $i;
								}
						}
				}
				if($Existe == "S"){
						if($Mens == 1){ $Mensagem .= ", "; }						
						$Mens      = 1;
						$Tipo      = 2;						
						$Mensagem .= "<a href=\"javascript:document.getElementsByName('Quantidade[$Posicao]').item(0).focus();\" class=\"titulo2\">Quantidade</a>";
				}

				# Verifica se as quantidades só são numeros e decimais #
				if($Existe == ""){
						$Posicao = "";
						for($k=0; $k<count($Quantidade); $k++){
								if ($TipoItem[$k] != "B") {
										if( ( ! SoNumVirg($Quantidade[$k]) ) and ( $Existe == "" ) ){
												$Existe  = "S";
												$Posicao = $k;
										}
								}
						}
						if($Existe == ""){
								for($j=0;$j<count($Quantidade);$j++){
										if ($TipoItem[$j] != "B") {
												if( ( ! Decimal($Quantidade[$j]) ) and $Existe == "" ){
														$Existe  = "S";
														$Posicao = $j;
												}
										}
								}
						}
						if($Existe == "S"){
								if($Mens == 1){ $Mensagem .= ", "; }								
								$Mens      = 1;
								$Tipo      = 2;								
								$Mensagem .= "<a href=\"javascript:document.getElementsByName('Quantidade[$Posicao]').item(0).focus();\" class=\"titulo2\">Quantidade Válida</a>";
						}
				}
		}
    
		if(count($ValorUnitario) != 0){
				$Existe = "";
				# Verifica se existe algum valor unitario igual a branco ou zero #
				for( $i=0;$i<count($ValorUnitario);$i++ ){
						if($TipoItem[$i] != "B"){
								if( (str_replace(",",".",$ValorUnitario[$i]) == 0 or $ValorUnitario[$i] == "") and ! SoNumeros($ValorUnitario[$i]) and $Existe == "" ){
										$Existe  = "S";
										$Posicao = $i;
								}
						}
				}
				if($Existe == "S"){
						if($Mens == 1){ $Mensagem .= ", "; }						
						$Mens      = 1;
						$Tipo      = 2;						
						$Mensagem .= "<a href=\"javascript:document.getElementsByName('ValorUnitario[$Posicao]').item(0).focus();\" class=\"titulo2\">Valor Unitário</a>";
				}
				# Verifica se os valores só são numeros e decimais #
				if($Existe == ""){
						$Posicao = "";
						for($k=0; $k<count($ValorUnitario); $k++){
								if($TipoItem[$k] != "B"){
										if( ( ! SoNumVirg($ValorUnitario[$k]) ) and ( $Existe == "" ) ){
												$Existe  = "S";
												$Posicao = $k;
										}
								}
						}
						if($Existe == ""){
								for($j=0; $j<count($ValorUnitario); $j++){
										if($TipoItem[$j] != "B"){
												if( ( ! DecimalValor($ValorUnitario[$j]) ) and $Existe == "" ){
														$Existe  = "S";
														$Posicao = $j;
												}
										}
								}
						}
						if($Existe == "S"){
								if( $Mens == 1 ){ $Mensagem .= ", "; }								
								$Mens      = 1;
								$Tipo      = 2;								
								$Mensagem .= "<a href=\"javascript:document.getElementsByName('ValorUnitario[$Posicao]').item(0).focus();\" class=\"titulo2\">Valor Unitário Válido</a>";
						}
				}
		}
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
		if($EstoqueVirtual=="S"){
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Notas Fiscais de estoque virtual não podem ser alterados";
		}

		if($Almoxarifado == ""){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialManterIncluir.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
		}
		if( ($Localizacao == "") && ($CarregaLocalizacao == 'N') ){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Localização";
		}elseif($Localizacao == ""){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialManterIncluir.Localizacao.focus();\" class=\"titulo2\">Localização</a>";
		}
		if($NumeroNota == ""){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialManterIncluir.NumeroNota.focus();\" class=\"titulo2\">Número da Nota</a>";
		}else{
				if(!SoNumeros($NumeroNota)) {
						if ( $Mens == 1 ) { $Mensagem .= ", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialManterIncluir.NumeroNota.focus();\" class=\"titulo2\">Número da Nota Válido</a>";
				}
		}
		if($SerieNota == ""){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialManterIncluir.SerieNota.focus();\" class=\"titulo2\">Série da Nota</a>";
		}
		if($DataEmissao == ""){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialManterIncluir.DataEmissao.focus();\" class=\"titulo2\">Data de Emissão</a>";
		}else{
				$DataValida = ValidaData($DataEmissao) ;
				if($DataValida != ""){
						if($Mens == 1){ $Mensagem .= ", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialManterIncluir.DataEmissao.focus();\" class=\"titulo2\">Data da Nota Válida</a>";
				}elseif(DataInvertida($DataEmissao) > $DataAtual){
						if($Mens == 1){ $Mensagem .= ", "; }
						$Mens      = 1;
						$Tipo      = 2;
  					$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialManterIncluir.DataEmissao.focus();\" class=\"titulo2\">Data de Emissão menor que a atual</a>";
				}else{
						list(,,$AnoEmissao)=explode("/",$DataEmissao);
            if($AnoEmissao < date('Y')-1){
						    if($Mens == 1){ $Mensagem .= ", "; }
						    $Mens      = 1;
						    $Tipo      = 2;
						    $Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialManterIncluir.DataEmissao.focus();\" class=\"titulo2\">Data de Emissão com ano posterior ou igual ao ano anterior</a>";
				    }
				}
		}
    
    $ValorTotalNotaCritica  = str_replace(",",".",str_replace(".","",$ValorTotalNota));
    if($ValorTotalNota == ""){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialManterIncluir.ValorTotalNota.focus();\" class=\"titulo2\">Valor Total da Nota Fiscal</a>";				
		} elseif($ValorTotalNotaCritica != sprintf("%01.4f",str_replace(",",".",$ValorNota))){
      if($Mens == 1){ $Mensagem .= ", "; }
			$Mens      = 1;
			$Tipo      = 2;
      $Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialManterIncluir.ValorTotalNota.focus();\" class=\"titulo2\">Valor Total da Nota Fiscal diferente do Valor Total dos Itens da Nota Fiscal</a>";      
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
						$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialManterIncluir.CnpjCpf.focus();\" class=\"titulo2\">$TipoDocumento</a>";
				}else{
						if( $CNPJ_CPF == 1 ){
								$valida_cnpj = valida_CNPJ($CnpjCpf);
								if( $valida_cnpj === false ){
										if($Mens == 1){ $Mensagem .= ", "; }
										$Mens      = 1;
										$Tipo      = 2;
										$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialManterIncluir.CnpjCpf.focus();\" class=\"titulo2\">CNPJ Válido</a>";
								}
						}else{
								$valida_cpf = valida_CPF($CnpjCpf);
								if( $valida_cpf === false ){
										if($Mens == 1){ $Mensagem .= ", "; }
										$Mens      = 1;
										$Tipo      = 2;
										$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialManterIncluir.CnpjCpf.focus();\" class=\"titulo2\">CPF Válido</a>";
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
						if( PEAR::isError($res) ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								$rows = $res->numRows();
								if($rows > 0){
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
										if( PEAR::isError($res) ){
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												$rows = $res->numRows();
												if($rows > 0){
														$linha       = $res->fetchRow();
														$RazaoSocial = $linha[0];
												}else{
														if($Mens == 1){ $Mensagem.=", "; }
														$Mens     = 1;
														$Tipo     = 1;
														$Mensagem = "Fornecedor Não Cadastrado";
												}
										}
										$db->disconnect();*/
										if($Mens == 1){ $Mensagem.=", "; }
										$Mens     = 1;
										$Tipo     = 1;
										$Mensagem = "Fornecedor Não Cadastrado";
								}
						}
				}
		}

		if( count($Quantidade) != 0 ){
				# Verifica se existe alguma quantidade igual a branco ou zero #
				for( $i=0;$i<count($Quantidade);$i++ ){
						if ($TipoItem[$i] != "B") {
								if( (str_replace(",",".",$Quantidade[$i]) == 0 or $Quantidade[$i] == "") and $Existe == "" ){
										$Existe  = "S";
										$Posicao = $i;                    
								}
						}
				}
        
				if( $Existe == "S" ){
						if( $Mens == 1 ){ $Mensagem .= ", "; }						
						$Mens      = 1;
						$Tipo      = 2;						
						$Mensagem .= "<a href=\"javascript:document.getElementsByName('Quantidade[$Posicao]').item(0).focus();\" class=\"titulo2\">Quantidade</a>";
				}
        
				# Verifica se as quantidades só são numeros e decimais #
				if($Existe == ""){
						$Posicao = "";
						for( $k=0;$k<count($Quantidade);$k++ ){
								if ($TipoItem[$k] != "B") {
										if( ( ! SoNumVirg($Quantidade[$k]) ) and ( $Existe == "" ) ){
												$Existe  = "S";
												$Posicao = $k;
										}
								}
						}
						if( $Existe == "" ){
								for( $j=0;$j<count($Quantidade);$j++ ){
										if ($TipoItem[$j] != "B") {
												if( ( ! Decimal($Quantidade[$j]) ) and $Existe == "" ){
														$Existe  = "S";
														$Posicao = $j;
												}
										}
								}
						}
						if( $Existe == "S" ){
								if( $Mens == 1 ){ $Mensagem .= ", "; }								
								$Mens      = 1;
								$Tipo      = 2;								
								$Mensagem .= "<a href=\"javascript:document.getElementsByName('Quantidade[$Posicao]').item(0).focus();\" class=\"titulo2\">Quantidade Válida</a>";
						}
				}
		}
		if( count($ValorUnitario) != 0 ){
				$Existe = "";
				# Verifica se existe algum valor unitario igual a branco ou zero #
				for( $i=0;$i<count($ValorUnitario);$i++ ){
						if ($TipoItem[$i] != "B") {
								if( (str_replace(",",".",$ValorUnitario[$i]) == 0 or $ValorUnitario[$i] == "") and ! SoNumeros($ValorUnitario[$i]) and $Existe == "" ){
										$Existe  = "S";
										$Posicao = $i;
								}
						}
				}
				if( $Existe == "S" ){
						if( $Mens == 1 ){ $Mensagem .= ", "; }						
						$Mens      = 1;
						$Tipo      = 2;						
						$Mensagem .= "<a href=\"javascript:document.getElementsByName('ValorUnitario[$Posicao]').item(0).focus();\" class=\"titulo2\">Valor Unitário</a>";
				}

				# Verifica se os valores só são numeros e decimais #
				if( $Existe == "" ){
						$Posicao = "";
						for( $k=0;$k<count($ValorUnitario);$k++ ){
								if ($TipoItem[$k] != "B") {
										if( ( ! SoNumVirg($ValorUnitario[$k]) ) and ( $Existe == "" ) ){
												$Existe  = "S";
												$Posicao = $k;
										}
								}
						}
						if( $Existe == "" ){
								for( $j=0;$j<count($ValorUnitario);$j++ ){
										if ($TipoItem[$j] != "B") {
												if( ( ! DecimalValor($ValorUnitario[$j]) ) and $Existe == "" ){
														$Existe  = "S";
														$Posicao = $j;
												}
										}
								}
						}
						if( $Existe == "S" ){
								if( $Mens == 1 ){ $Mensagem .= ", "; }								
								$Mens      = 1;
								$Tipo      = 2;								
								$Mensagem .= "<a href=\"javascript:document.getElementsByName('ValorUnitario[$Posicao]').item(0).focus();\" class=\"titulo2\">Valor Unitário Válido</a>";
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
      if( PEAR::isError($res) ){
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
            
            $Mensagem .= "<a href=\"javascript: document.getElementById('codMaterial$indice').style.fontWeight='bold'; ";
            
            if($TipoItem[$indice] != "B"){            
              $Mensagem .= "document.getElementsByName('CheckItem[$indice]').item(0).focus();";
            } else {
              $Mensagem .= "document.getElementsByName('ParaElements[$indice]').item(0).focus();";
            }
            $Mensagem .= "\" class=\"titulo2\">$Material[$indice]</a>";
            
            if($i <= count($IndiceMatInativosInvalidos) - 2) {              
              $Mensagem .= ", ";
            }             
          }                  
        } 
      }
      $db->disconnect();
    }
    
		if( $Mens == 0 ){
				# Verifica se existe algum material com armazenamento zerado #
				$db = Conexao();
				for( $i=0;$i< count($ItemNotaFiscal);$i++ ){
						$sql  = "SELECT COUNT(*) FROM SFPC.TBARMAZENAMENTOMATERIAL ";
						$sql .= " WHERE CMATEPSEQU = $Material[$i] AND CLOCMACODI = $Localizacao ";
						$res  = $db->query($sql);
						if( PEAR::isError($res) ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								$Linha  = $res->fetchRow();
								$QtdRec = $Linha[0];
								if ($QtdRec > 0) {
										$sql  = "SELECT VARMATUMED, AARMATQTDE FROM SFPC.TBARMAZENAMENTOMATERIAL ";
										$sql .= " WHERE CMATEPSEQU = $Material[$i] AND CLOCMACODI = $Localizacao ";
										$res  = $db->query($sql);
										if( PEAR::isError($res) ){
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												$Linha = $res->fetchRow();
												$ValArmazenado = $Linha[0];
												$QtdArmazenado = $Linha[1];
												if ( ($ValArmazenado == 0 or $ValArmazenado == "") and ($QtdArmazenado != 0 ) and ($Existe == "") ) {
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
      if( PEAR::isError($res) ){
            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
      }else{
         $Linha = $res->fetchRow();
         $ValorNotaTotal = $Linha[0];
         
         if($ValorNotaTotal == null || $ValorNotaTotal == ''){
           $ValorNotaTotal = 0;
         }
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
						$db   = Conexao();
						# Verifica se o Fornecedor de Estoque é Credenciado #
						$sql  = "SELECT AFORCRSEQU FROM SFPC.TBFORNECEDORCREDENCIADO ";
						$sql .= "	WHERE ";
						if( $CNPJ_CPF == 1 ){
								$sql .= "	AFORCRCCGC = '$CnpjCpf' ";
						}else{
								$sql .= "	AFORCRCCPF = '$CnpjCpf' ";
						}
						$res  = $db->query($sql);
						if( PEAR::isError($res) ){
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
                  if( PEAR::isError($res) ){
                      ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                  }else{
                      $rows = $res->numRows();
                      if( $rows > 0 ){
                          $Linha          = $res->fetchRow();
                          $FornecedorCodi = $Linha[0];
                      }
                  }*/
									EmailErro(__FILE__."- Fornecedor não encontrado.", __FILE__, __LINE__, "Fornecedor informado não foi encontrado em SFPC.TBFORNECEDORCREDENCIADO.\n\nSequencial do fornecedor informado: '".$FornecedorSequ."'\n\nVerificar se o dado informado pelo sistema foi correto ou se há algum fornecedor que não foi migrado de SFPC.TBFORNECEDORESTOQUE para SFPC.TBFORNECEDORCREDENCIADO corretamente.");
              }
              if( (!$FornecedorCodi) and (!$FornecedorSequ) ){
                  if( $Mens == 1 ){ $Mensagem .= ", "; }
                  $Mens      = 1;
                  $Tipo      = 2;
                  $Mensagem .= "<a href=\"javascript:document.CadNotaFiscalMaterialManterIncluir.CnpjCpf.focus();\" class=\"titulo2\">Fornecedor Cadastrado</a>";
              }else{
                if( $NumeroNota != "" and SoNumeros($NumeroNota) ){
                    # Verifica se já existe alguma nota com o mesmo número/série do mesmo fornecedor #
                    $sql  = "SELECT COUNT(*) AS QTD FROM SFPC.TBENTRADANOTAFISCAL ";
                    $sql .= "WHERE AENTNFNOTA = $NumeroNota AND AENTNFSERI = '$SerieNota' ";
                    if($FornecedorSequ){
                        $sql .= "AND AFORCRSEQU = $FornecedorSequ ";
                        $Fornecedor = $FornecedorSequ;
                    }else{
                        $sql .= "AND CFORESCODI = $FornecedorCodi ";
                        $Fornecedor = $FornecedorCodi;
                    }
                    $sql .= "  AND (FENTNFCANC IS NULL OR FENTNFCANC = 'N')";						
                    $res  = $db->query($sql);
                    if( PEAR::isError($res) ){
                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                    }else{
                        $Linha  = $res->fetchRow();
                        if (  ( ($Linha[0] > 0) and ( ($NumeroNota."-".$SerieNota) != $NotaAnterior) ) or ( ($Linha[0] > 0) and ( ($FornecedorAnterior) != $Fornecedor) )  ){
                            $Mens      = 1;
                            $Tipo      = 2;
                            $Mensagem = "<a href=\"javascript:document.CadNotaFiscalMaterialManterIncluir.NumeroNota.focus();\" class=\"titulo2\">Número da Nota Fiscal já cadastrado para este Fornecedor</a>";
                        }
                    }
                }                               
                            
                for($i=0; $i < count($Empenhos) and !$Rollback; $i++){ //Só poder um único empenho, isto é, o conjunto: ano, orgão, unidade e sequencial do empenho, logo QtdeEmpenhos deve ser igual a 1
                  # Separa Ano, Órgão, Unidade, Sequencial e Parcela #
                  $Emp = explode(".",$Empenhos[$i]);
                  $AnoEmp        = $Emp[2];
                  $OrgaoEmp      = $Emp[3];
                  $UnidadeEmp    = $Emp[4];
                  $SequencialEmp = $Emp[5];                  
                  $ParcelaEmp    = $Emp[6];                  
                  
                  
                  $EmpenhoSemDataEmissao = "$AnoEmp.$OrgaoEmp.$UnidadeEmp.$SequencialEmp.$ParcelaEmp";
                                    
                  
                  $QtdeEmpenhos = 0;
                  
                  for($j=0; $j < count($Empenhos) and !$Rollback and QtdeEmpenhos <= 1; $j++){ //Só poder um único empenho, isto é, o conjunto: ano, orgão, unidade e sequencial do empenho, logo QtdeEmpenhos deve ser igual a 1
                    $Emp = explode(".",$Empenhos[$j]);
                    $AnoEmpItem        = $Emp[2];
                    $OrgaoEmpItem      = $Emp[3];
                    $UnidadeEmpItem    = $Emp[4];
                    $SequencialEmpItem = $Emp[5];
                    $ParcelaEmpItem    = $Emp[6];
                    
                                        
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
              }              
						}

						if($Mens == 0){
								$DataHora = date("Y-m-d H:i:s");
								$db->query("BEGIN TRANSACTION");
								# Atualiza a Nota Fiscal #
								$sql  = "UPDATE SFPC.TBENTRADANOTAFISCAL ";
								$sql .= "   SET AENTNFNOTA = $NumeroNota, AENTNFSERI = '$SerieNota', ";
								$sql .= "       DENTNFEMIS = '".DataInvertida($DataEmissao)."', DENTNFENTR = '".DataInvertida($DataEntrada)."', ";
								$sql .= "       CGREMPCODI = ".$_SESSION['_cgrempcodi_'].", ";
								$sql .= "       CUSUPOCODI = ".$_SESSION['_cusupocodi_'].", TENTNFULAT = '".$DataHora."', ";
								if ($FornecedorSequ != "") {
										$sql .= "AFORCRSEQU = $FornecedorSequ, CFORESCODI = NULL ";
								}else{
										$sql .= "CFORESCODI = $FornecedorCodi, AFORCRSEQU = NULL ";
								}
								$sql .= " WHERE CALMPOCODI = $Almoxarifado AND AENTNFANOE = $AnoNota ";
								$sql .= "   AND CENTNFCODI = $NotaFiscal	";
								$res  = $db->query($sql);
								if( PEAR::isError($res) ){
										$Rollback = 1;
										$db->query("ROLLBACK");
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										# Apaga empenhos relativos a esta nota para posterior re-inclusão #
										$sqldelemp  = "DELETE FROM SFPC.TBNOTAFISCALEMPENHO ";
										$sqldelemp .= " WHERE CALMPOCODI = $Almoxarifado ";
										$sqldelemp .= "   AND AENTNFANOE = $AnoNota ";
										$sqldelemp .= "   AND CENTNFCODI = $NotaFiscal ";
										$resdelemp  = $db->query($sqldelemp);
										if( PEAR::isError($resdelemp) ){
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
                        $sqlemp .= " $SequencialEmp, $ParcelaEmp, '".$DataHora."' ) ";
                        $resemp = $db->query($sqlemp);
                        if( PEAR::isError($resemp) ){
                            $Rollback = 1;
                            $db->query("ROLLBACK");
                            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlemp");
                        }                          
                      }
										}
										
										if($Rollback != 1){
												# Insere Itens da Nota Fiscal #
												$decValorNota = 0;
												for($i=0; $i< count($ItemNotaFiscal); $i++){
														if($TipoItem[$i] != "B"){
																# Formata Valores para uso de calculos #
																$decQtdItem   = str_replace(",",".",$Quantidade[$i]);
																$decValItem   = str_replace(",",".",$ValorUnitario[$i]);
																$decTotalItem = str_replace(",",".",($decQtdItem*$decValItem));
																$decValorNota = $decValorNota + $decTotalItem;
																$sqlnf  = "INSERT INTO SFPC.TBITEMNOTAFISCAL ( ";
																$sqlnf .= "CALMPOCODI, AENTNFANOE, CENTNFCODI, ";
																$sqlnf .= "CMATEPSEQU, AITENFQTDE, VITENFUNIT, TITENFULAT ";
																$sqlnf .= ") VALUES ( ";
																$sqlnf .= "$Almoxarifado, $AnoNota, $NotaFiscal, ";
																$sqlnf .= "$Material[$i], $decQtdItem, $decValItem, '".$DataHora."' ) ";
																$resnf  = $db->query($sqlnf);
																if( PEAR::isError($resnf) ){
																		$Rollback = 1;
																		$db->query("ROLLBACK");
																		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlnf");
																}else{
																		# Pega o Máximo valor do Movimento de Material #
																		$sqlmaxmov  = "SELECT MAX(CMOVMACODI) AS CODIGO FROM SFPC.TBMOVIMENTACAOMATERIAL";
																		$sqlmaxmov .= " WHERE CALMPOCODI = $Almoxarifado AND AMOVMAANOM = $AnoExercicio ";
																		//$sqlmaxmov .= "   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																		$resmaxmov  = $db->query($sqlmaxmov);
																		if( PEAR::isError($resmaxmov) ){
														   					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmaxmov");
																		}else{
																				$Linhamaxmov = $resmaxmov->fetchRow();
																				$Movimento   = $Linhamaxmov[0] + 1;
																		}
																		if( $Quantidade[$i] != "" ){
																				# Pega o valor e quantidade atual do estoque(armazenamento) #
																				$sqlarmat  = "SELECT VARMATUMED, AARMATQTDE, AARMATESTR, AARMATVIRT FROM SFPC.TBARMAZENAMENTOMATERIAL ";
																				$sqlarmat .= "WHERE CMATEPSEQU = $Material[$i] AND CLOCMACODI = $Localizacao ";
																				$resarmat  = $db->query($sqlarmat);
																				if( PEAR::isError($resarmat) ){
																						$Rollback = 1;
																						$db->query("ROLLBACK");
																						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlarmat");
																				}else{
																						$rowsarmat = $resarmat->numRows();
																						# É um item do estoque (armazenamento) #
																						if($rowsarmat > 0){
																								$Linhaarmat      = $resarmat->fetchRow();
																								$decValEstoque   = str_replace(",",".",$Linhaarmat[0]);
																								$decQtdEstoque   = str_replace(",",".",$Linhaarmat[1]);
                                                $decQtdEstoqueReal   = str_replace(",",".",$Linhaarmat[2]);
																								$decQtdEstoqueVirtual   = str_replace(",",".",$Linhaarmat[3]);
																								$decTotalEstoque = str_replace(",",".",($decQtdEstoque*$decValEstoque));
                                                
                                                
                                                if ($decQtdEstoqueReal == null || $decQtdEstoqueReal == '') {
                                                  $decQtdEstoqueReal = 0;
                                                }
                                                
                                                if ($decQtdEstoqueVirtual == null || $decQtdEstoqueVirtual == '') {
                                                  $decQtdEstoqueVirtual = 0;
                                                }
                                                
																								# Calcula o valor medio a partir do totalizador do estoque atual e o totalizador do item da nota #
																								if( ($decQtdEstoque == 0) or ($decQtdEstoque == '') ) {
																										$decValorMedio = str_replace(",",".",$decValItem); // Se o estoque está zerado, o valor médio será o valor do item na nota
																								}else{
																										$ValorMedio    = ( $decTotalItem + $decTotalEstoque ) / ( $decQtdItem + $decQtdEstoque );
																										$decValorMedio = str_replace(",",".",$ValorMedio);
																								}

																								# Atualiza o valor unitário medio e o ultimo valor de compra de cada item #
																								$sqlupdarmat  = "UPDATE SFPC.TBARMAZENAMENTOMATERIAL ";
																								$sqlupdarmat .= "   SET AARMATQTDE = ($decQtdItem + $decQtdEstoque), ";
                                                
                                                if($EstoqueVirtual == 'S'){
                                                  $sqlupdarmat .= "       AARMATVIRT = ($decQtdItem + $decQtdEstoqueVirtual), ";
                                                } else {
                                                  $sqlupdarmat .= "       AARMATESTR = ($decQtdItem + $decQtdEstoqueReal), ";
                                                }
                                                
																								$sqlupdarmat .= " 			VARMATUMED = $decValorMedio, VARMATULTC = $decValItem, ";
																								$sqlupdarmat .= "				CGREMPCODI = ".$_SESSION['_cgrempcodi_'].", CUSUPOCODI = ".$_SESSION['_cusupocodi_'].", ";
																								$sqlupdarmat .= "				TARMATULAT = '".$DataHora."' ";
																								$sqlupdarmat .= " WHERE CMATEPSEQU = $Material[$i] AND CLOCMACODI = $Localizacao ";
																								$resupdarmat = $db->query($sqlupdarmat);
																								if( PEAR::isError($resupdarmat) ){
																										$Rollback = 1;
																										$db->query("ROLLBACK");
																										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlupdarmat");
																								}else{
																										# Pega o Máximo valor do Movimento do Material do Tipo 7 - ENTRADA POR ALTERAÇÃO DE NOTA FISCAL #
																										$sqltipo  = "SELECT MAX(CMOVMACODT) FROM SFPC.TBMOVIMENTACAOMATERIAL";
																										$sqltipo .= " WHERE CALMPOCODI = $Almoxarifado AND AMOVMAANOM = ".date("Y")." ";
																										$sqltipo .= "   AND CTIPMVCODI = 7 ";
																										//$sqltipo .= "   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																										$restipo  = $db->query($sqltipo);
																										if( PEAR::isError($restipo) ){
																												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqltipo");
																										}else{
																												$LinhaTipo     = $restipo->fetchRow();
																												$TipoMovimento = $LinhaTipo[0] + 1;
																										}
																										
																										# Insere na tabela de Movimentação de Material do Tipo 7 - ENTRADA POR ALTERAÇÃO DE NOTA FISCAL #
																										$sqlmovmat  = "INSERT INTO SFPC.TBMOVIMENTACAOMATERIAL ( ";
																										$sqlmovmat .= "CALMPOCODI, AMOVMAANOM, CMOVMACODI, DMOVMAMOVI, ";
																										$sqlmovmat .= "CTIPMVCODI, CREQMASEQU, CMATEPSEQU, AMOVMAQTDM, ";
																										$sqlmovmat .= "VMOVMAVALO, VMOVMAUMED, CGREMPCODI, CUSUPOCODI, TMOVMAULAT, ";
																										$sqlmovmat .= "AENTNFANOE, CENTNFCODI, CMOVMACODT, AMOVMAMATR, ";
																										$sqlmovmat .= "NMOVMARESP ";
																										$sqlmovmat .= ") VALUES ( ";
																										$sqlmovmat .= "$Almoxarifado, $AnoExercicio, $Movimento, '".date('Y-m-d')."', ";
																										$sqlmovmat .= "7, NULL, $Material[$i], $decQtdItem, ";
																										$sqlmovmat .= "$decValItem, $decValorMedio, ".$_SESSION['_cgrempcodi_'].", ".$_SESSION['_cusupocodi_'].", '".$DataHora."', ";
																										$sqlmovmat .= "$AnoNota, $NotaFiscal, $TipoMovimento, NULL, ";
																										$sqlmovmat .= " NULL )";
																										$resmovmat  = $db->query($sqlmovmat);
																										if( PEAR::isError($resmovmat) ){
																												$Rollback = 1;
																												$db->query("ROLLBACK");
																												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmovmat");
																										}
																								}
																						# É Um Material (Material Portal) #
																						}else{
																								# Grava direto na tabela de armazenamento sem cálculo e grava a movimentação com o tipo entrada por nf de compra #
																								$sqlarmat2  = "INSERT INTO SFPC.TBARMAZENAMENTOMATERIAL ( ";
																								$sqlarmat2 .= "CMATEPSEQU, CLOCMACODI, AARMATQTDE, AARMATMAXI, ";
																								$sqlarmat2 .= "AARMATESTS, AARMATESTR, AARMATVIRT, AARMATESTC, ";
																								$sqlarmat2 .= "AARMATNIVR, AARMATPONT, VARMATUMED, VARMATULTC, ";
																								$sqlarmat2 .= "CGREMPCODI, CUSUPOCODI, TARMATULAT ";
																								$sqlarmat2 .= ") VALUES ( ";
																								$sqlarmat2 .= "$Material[$i], $Localizacao, $decQtdItem, NULL, ";
                                                $sqlarmat2 .= "NULL, "; //Refere-se ao campo AARMATESTS
																										
                                                if($EstoqueVirtual == 'S'){
                                                  $sqlarmat2 .= "0, $decQtdItem, "; //Refere-se ao campo AARMATVIRT
                                                } else {
                                                  $sqlarmat2 .= "$decQtdItem, 0, "; //Refere-se ao campo AARMATESTR
                                                }
                                                
                                                $sqlarmat2 .= " NULL, "; //Refere-se ao campo AARMATESTC
																								$sqlarmat2 .= "NULL, NULL, $decValItem, $decValItem, ";
																								$sqlarmat2 .= "".$_SESSION['_cgrempcodi_'].", ".$_SESSION['_cusupocodi_'].", '".$DataHora."' )";
																								$resarmat2  = $db->query($sqlarmat2);
																								if( PEAR::isError($resarmat2) ){
																										$Rollback = 1;
																										$db->query("ROLLBACK");
																										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlarmat2");
																								}else{
																										# Pega o Máximo valor do Movimento do Material do Tipo 7 - ENTRADA POR ALTERAÇÃO DE NOTA FISCAL #
																										$sqltipo  = "SELECT MAX(CMOVMACODT) FROM SFPC.TBMOVIMENTACAOMATERIAL";
																										$sqltipo .= " WHERE CALMPOCODI = $Almoxarifado AND AMOVMAANOM = $AnoExercicio ";
																										$sqltipo .= "   AND CTIPMVCODI = 7 ";
																										//$sqltipo .= "   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																										$restipo  = $db->query($sqltipo);
																										if( PEAR::isError($restipo) ){
																										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqltipo");
																										}else{
																												$LinhaTipo     = $restipo->fetchRow();
																												$TipoMovimento = $LinhaTipo[0] + 1;
																										}
																										# Insere na tabela de Movimentação de Material
																										$sqlmovmat2  = "INSERT INTO SFPC.TBMOVIMENTACAOMATERIAL ( ";
																										$sqlmovmat2 .= "CALMPOCODI, AMOVMAANOM, CMOVMACODI, DMOVMAMOVI, ";
																										$sqlmovmat2 .= "CTIPMVCODI, CREQMASEQU, CMATEPSEQU, AMOVMAQTDM, ";
																										$sqlmovmat2 .= "VMOVMAVALO, VMOVMAUMED, CGREMPCODI, CUSUPOCODI, TMOVMAULAT, ";
																										$sqlmovmat2 .= "AENTNFANOE, CENTNFCODI, CMOVMACODT, AMOVMAMATR, ";
																										$sqlmovmat2 .= "NMOVMARESP ";
																										$sqlmovmat2 .= ") VALUES ( ";
																										$sqlmovmat2 .= "$Almoxarifado, $AnoExercicio, $Movimento, '".date('Y-m-d')."', ";
																										$sqlmovmat2 .= "7, NULL, $Material[$i], $decQtdItem, ";
																										$sqlmovmat2 .= "$decValItem, $decValItem, ".$_SESSION['_cgrempcodi_'].", ".$_SESSION['_cusupocodi_'].", '".$DataHora."', ";
																										$sqlmovmat2 .= "$AnoNota, $NotaFiscal, $TipoMovimento, NULL, NULL )";
																										$resmovmat2  = $db->query($sqlmovmat2);
																										if( PEAR::isError($resmovmat2) ){
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
								}

								if($Rollback != 1){                
										# Atualiza o valor da nota através dos itens da nota #
										$sql  = "UPDATE SFPC.TBENTRADANOTAFISCAL ";
										$sql .= "   SET VENTNFTOTA = (VENTNFTOTA + $decValorNota), CGREMPCODI = ".$_SESSION['_cgrempcodi_'].", ";
										$sql .= "       CUSUPOCODI = ".$_SESSION['_cusupocodi_'].", TENTNFULAT = '".$DataHora."' ";
										$sql .= " WHERE CENTNFCODI = $NotaFiscal AND CALMPOCODI = $Almoxarifado ";
										$sql .= "   AND AENTNFANOE = $AnoNota ";
										$res  = $db->query($sql);
										if( PEAR::isError($res) ){
												$db->query("ROLLBACK");
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												$db->query("COMMIT");

												# Limpando os dados da nota #
												$TipoUsuario	  = "";
												$OrgaoUsuario	  = "";
												$CentroCusto	  = "";
												$NumeroNota     = "";
												$SerieNota      = "";
												$DataEmissao    = "";
												$CNPJ_CPF       = "";
												$CnpjCpf        = "";
												$RazaoSocial    = "";
												$DataEntrada    = "";
												$ValorNota      = "";
												$Localizacao    = "";
                        $EstoqueVirtual = "";

												# Limpando variáveis de empenho #
												$CheckEmp       = "";
												unset($Empenhos);                        
												unset($_SESSION['Empenho']);

												# Limpandos os dados do item da nota #
												$CheckItem      = "";
												$Material       = "";
												$DescMaterial   = "";
												$Unidade        = "";
                        $SituacaoMaterial  = "";
												$Quantidade     = "";
												$ValorUnitario  = "";
												$ValorTotal     = "";
												$InicioPrograma = "";
												unset($ItemNotaFiscal);
												unset($_SESSION['item']);

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
												header("Location: $Url");
												exit;
										}
								}
								$db->query("END TRANSACTION");
						}
						$db->disconnect();
				}else{
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "O Usuário do grupo INTERNET não pode fazer inclusão de Nota Fiscal";
				}
		}
}

# Monta o array de itens da NF #
if(count($_SESSION['item']) != 0){
		sort($_SESSION['item']);
		if($ItemNotaFiscal == ""){
				for($i=0;$i<count($_SESSION['item']);$i++){
						$ItemNotaFiscal[count($ItemNotaFiscal)] = $_SESSION['item'][$i];
				}
		}else{
				for($i=0; $i<count($ItemNotaFiscal); $i++){
						$DadosItem            = explode($SimboloConcatenacaoArray,$ItemNotaFiscal[$i]);
						$SequencialItem[$i]   = $DadosItem[1];
				}
				for($i=0;$i<count($_SESSION['item']);$i++){
						$DadosSessao          = explode($SimboloConcatenacaoArray,$_SESSION['item'][$i]);
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
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.CadNotaFiscalMaterialManterIncluir.Botao.value = valor;
	document.CadNotaFiscalMaterialManterIncluir.submit();
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
<form action="CadNotaFiscalMaterialManterIncluir.php" method="post" name="CadNotaFiscalMaterialManterIncluir">
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
	<?php 
	if( $Mens == 1 ){
	?>
	<tr>
		<td width="100"></td>
		<td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
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
										Para incluir novos itens da nota, clique no botão "Incluir Item" e para retornar a tela anterior clique no botão "Voltar".<br>
									</p>
								</td>
							</tr>
							<tr>
								<td>
									<table class="textonormal" border="0" align="left" width="100%" summary="">
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Operação</td>
											<td class="textonormal">
												INCLUSÃO DE ITEM(NS)
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Almoxarifado</td>
											<td class="textonormal">
												<?php
												$db  = Conexao();
												$sql    = "SELECT EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL";
												$sql   .= " WHERE CALMPOCODI = $Almoxarifado ";
												$res  = $db->query($sql);
												if( PEAR::isError($res) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$Linha = $res->fetchRow();
														echo "$Linha[0]<br>";
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
												if($Almoxarifado != ""){
														# Mostra as Localizações de acordo com o Almoxarifado #
														$sql    = "SELECT A.CLOCMACODI, A.FLOCMAEQUI, A.ALOCMANEQU, ";
														$sql   .= "       A.ALOCMAPRAT, A.ALOCMACOLU, B.EARLOCDESC ";
														$sql   .= "  FROM SFPC.TBLOCALIZACAOMATERIAL A, SFPC.TBAREAALMOXARIFADO B ";
														$sql   .= " WHERE A.CALMPOCODI = $Almoxarifado AND A.FLOCMASITU = 'A'";
														$sql   .= "   AND A.CARLOCCODI = B.CARLOCCODI	";
														$sql   .= " ORDER BY B.EARLOCDESC DESC, A.FLOCMAEQUI, A.ALOCMANEQU, ";
														$sql   .= "       A.ALOCMAPRAT, A.ALOCMACOLU";
														$res  = $db->query($sql);
														if( PEAR::isError($res) ){
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														}else{
																$Rows = $res->numRows();
																if($Rows == 0){
																		echo "NENHUMA LOCALIZAÇÃO CADASTRADA PARA ESTE ALMOXARIFADO";
																		echo "<input type=\"hidden\" name=\"CarregaLocalizacao\" value=\"N\">";
																}else{
																		if($Rows == 1){
																				$Linha = $res->fetchRow();
																				if($Linha[1] == "E"){
																						$Equipamento = "ESTANTE";
																				}if($Linha[1] == "A"){
																						$Equipamento = "ARMÁRIO";
																				}if($Linha[1] == "P"){
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
																						if( $Linha[1] == "E" ){
																								$Equipamento = "ESTANTE";
																						}if( $Linha[1] == "A" ){
																								$Equipamento = "ARMÁRIO";
																						}if( $Linha[1] == "P" ){
																								$Equipamento = "PALETE";
																						}
																						$NumeroEquip = $Linha[2];
																						$Prateleira  = $Linha[3];
																						$Coluna      = $Linha[4];
																						$DescArea    = $Linha[5];
																						if($DescAreaAntes != $DescArea){
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
												<?php $URL = "../calendario.php?Formulario=CadNotaFiscalMaterialManterIncluir&Campo=DataEmissao";?>
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
												<?php
												$ValorNota = 0;
												for($i=0; $i<count($Quantidade); $i++){
														$decQuantidade    = str_replace(",",".",$Quantidade[$i]);
														$decValorUnitario = str_replace(",",".",$ValorUnitario[$i]);                            
														$decValorTotal    = str_replace(",",".",($decQuantidade * $decValorUnitario));
														$ValorTotal[$i]   = str_replace(",",".",$decValorTotal);
														$ValorNota        = str_replace(",",".",($ValorNota + $ValorTotal[$i]));
												}
												echo converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorNota)));
												?>
                        <input type="hidden" name="ValorNota" value="<?php if ($ValorNota == ""){ echo 0; }else{ echo converte_valor_estoques(sprintf('%01.4f',str_replace(",",".",$ValorNota))); } ?>" class="textonormal">                        
											</td>
										</tr>
										<tr>
											<td class="textonormal" colspan="6">
												<table border="1" cellpadding="3" cellspacing="0" bgcolor="#bfdaf2" bordercolor="#75ADE6" width="100%" summary="">
													<tr>														
                            <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="6">
															ITENS DA NOTA FISCAL
														</td>
													</tr>
													<?php                          
													if( count($ItemNotaFiscal) != 0 ){ sort($ItemNotaFiscal); }
														for( $i=0;$i< count($ItemNotaFiscal);$i++ ){
															$Dados             = explode($SimboloConcatenacaoArray,$ItemNotaFiscal[$i]);
															$DescMaterial[$i]  = $Dados[0];
															$Material[$i]      = $Dados[1];
															$Unidade[$i]       = $Dados[2];
                              $SituacaoMaterial[$i]  = $Dados[3];
															$Quantidade[$i]    = $Dados[4];
															$ValorUnitario[$i] = $Dados[5];
															$TipoItem[$i]      = $Dados[6];                              
                              
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
															<input type="hidden" name="ItemNotaFiscal[<?php echo $i; ?>]" value="<?php echo $ItemNotaFiscal[$i]; ?>">
															<input type="hidden" name="Material[<?php echo $i; ?>]" value="<?php echo $Material[$i]; ?>">
															<input type="hidden" name="TipoItem[<?php echo $i; ?>]" value="<?php echo $TipoItem[$i]; ?>">
															<?php
															if($TipoItem[$i] != "B"){
																	echo "<input type=\"checkbox\" name=\"CheckItem[$i]\" value=\"$i\">";
															}else{
																	echo "<input type=\"hidden\" name=\"ParaElements[$i]\" value=\"$i\">";
															}
															$Url = "CadItemDetalhe.php?ProgramaOrigem=CadNotaFiscalMaterialManterIncluir&Material=$Material[$i]";
															if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
															?>
															<a href="javascript:AbreJanela('<?php $Url;?>',700,370);">
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
														</td>
														<td class="textonormal" width="10%" align="center">
															<?php echo $Unidade[$i];?>
															<input type="hidden" name="Unidade[<?php echo $i; ?>]" value="<?php echo $Unidade[$i]; ?>">
														</td>
														<td class="textonormal" align="right" width="10%">
															<?php
															if($TipoItem[$i] == "B"){                                  
																	if( $Quantidade[$i] == "" ){ echo 0; }else{ echo converte_quant(sprintf("%01.2f",str_replace(",",".",$Quantidade[$i]))); }																	
																	echo "<input type=\"hidden\" name=\"Quantidade[$i]\" value=\"$Quantidade[$i]\">";                                  
															}else{                                
															?>
															<input type="text" name="Quantidade[<?php echo $i; ?>]" size="10" maxlength="10" value="<?php echo str_replace(".",",",$Quantidade[$i]); ?>" class="textonormal">
															<?php } ?> 
                              
															
														</td>
														<td class="textonormal" align="right" width="10%">
															<?php
															if($TipoItem[$i] == "B"){                                 
																	if( $ValorUnitario[$i] == "" ){ echo 0; }else{ echo converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorUnitario[$i]))); }
																	echo "<input type=\"hidden\" name=\"ValorUnitario[$i]\" value=\"$ValorUnitario[$i]\">";																	                                  
															}else{
															?>
															<input type="text" name="ValorUnitario[<?php echo $i; ?>]" size="10" maxlength="10" value="<?php echo str_replace(".",",",$ValorUnitario[$i]); ?>" class="textonormal">
															<?php } ?>
                              
														</td>
														<td class="textonormal" align="right" width="10%">
															<?php
															if( $ValorTotal[$i] == "" ){ echo 0; }else{ echo converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorTotal[$i]))); }															
															echo "<input type=\"hidden\" name=\"ValorTotal[$i]\" value=\"$ValorTotal[$i]\">";                              
															?>
														</td>
													</tr>
													<?php } ?>
													<tr>
														<td class="textonormal" colspan="6" align="center">
															<?php
															$Url = "CadIncluirItem.php?ProgramaOrigem=CadNotaFiscalMaterialManterIncluir&Almoxarifado=$Almoxarifado";
															if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
															?>
															<input type="button" name="IncluirItem" value="Incluir Item" class="botao" onclick="javascript:AbreJanelaItem('<?php $Url;?>',700,350);">
															<input type="button" name="Retirar" value="Retirar Item" class="botao" onClick="javascript:enviar('Retirar');">
															<input type="button" name="Totalizar" value="Totalizar" class="botao" onClick="javascript:enviar('Totalizar');">
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
                  <input type="hidden" name="EstoqueVirtual" value="<?php echo $EstoqueVirtual; ?>">
                  <input type="hidden" name="ValorNota" value="<?php echo $ValorNota; ?>">
                  <input type="hidden" name="CentroCusto" value="<?php echo $CentroCusto; ?>">
                  
                  
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
