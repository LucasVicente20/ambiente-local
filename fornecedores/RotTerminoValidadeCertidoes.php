<?php
# -------------------------------------------------------------------------
# Portal da DGCO
# Programa: RotTerminoValidadeCertidoes.php
# Autor:    Roberta Costa
# Data:     02/09/04
# Objetivo: Programa de Gestão de Fornecedores
# OBS.:     Tabulação 2 espaços
# -------------------------------------------------------------------------
# Alterado: Rossana Lira
# Data:     01/06/2007 - Ajustes para colocar em produção
# -------------------------------------------------------------------------
# Alterado: Carlos Abreu
# Data:     03/09/2007 - Ajustes para apresentar somente dados que estiverem em atraso
# -------------------------------------------------------------------------
# Alterado: Rossana Lira
# Data:     20/11/2007 - Ajustes no texto solicitado pelo usuário
# -------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     11/03/2008 - Alteração no texto solicitado pelo usuário
# -------------------------------------------------------------------------
# Alterado: Rossana Lira
# Data:     08/05/2008 - Alteração no texto solicitado pelo usuário
# -------------------------------------------------------------------------
# Alterado: Rossana Lira
# Data:     15/05/2008 - Correção de exibição de uma certidão a mais para alguns fornecedores
# -------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     15/07/2008 - Correção do link do sítio passado por e-mail para o fornecedor.
# -------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     24/05/2011	- Tarefa Redmine: 2209 - Mandar envio de emails para os 2 emails do fornecedor
# -------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		24/10/2018
# Objetivo: Tarefa Redmine 73662
# -------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "/home/wwwdisco1/portal/html/pr/secfinancas/portalcompras/programas/funcoes.php";

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;
$Assunto      = "Erro na Rotina de Aviso de Término da Validade de Certidões";
$From         = $GLOBALS["EMAIL_FROM"];
$Inicio       = "Início: ".date("d/m/Y H:i:s");

# Verifica a Validação das Certidões do Fronecedor #
$db       = Conexao();
$sqlforn  = "SELECT A.AFORCRSEQU, COUNT(B.DFORCEVALI), A.AFORCRCCGC, A.AFORCRCCPF ";
$sqlforn .= "  FROM SFPC.TBFORNECEDORCREDENCIADO A, SFPC.TBFORNECEDORCERTIDAO B";
$sqlforn .= " WHERE A.AFORCRSEQU = B.AFORCRSEQU AND A.NFORCRMAIL <> ''";
$sqlforn .= "   AND B.DFORCEVALI < '".date('Y-m-d')."'";
$sqlforn .= " GROUP BY  A.AFORCRSEQU, A.AFORCRCCGC, A.AFORCRCCPF ";
$sqlforn .= "HAVING COUNT(B.DFORCEVALI) > 0 ";
$sqlforn .= " ORDER BY 1 ";
$resforn  = $db->query($sqlforn);
if( PEAR::isError($resforn) ){
		$Texto = "$ErroPrograma\nLinha: ".__LINE__."\n$Inicio\nSql: $sqlforn";
		EnviaEmail($Email, $Assunto, $Texto, $From);
		exit;
}else{
		$rowforn = $resforn->numRows();
		for( $j=0;$j<$rowforn;$j++ ){
				$Fornecedor = $resforn->fetchRow();

				# Pega os dados do Fornecedor #
				$sql  = "SELECT B.AFORCRSEQU, C.NFORCRRAZS, C.NFORCRMAIL, A.CTIPCECODI, ";
				$sql .= "       A.ETIPCEDESC, B.DFORCEVALI, C.NFORCRMAI2 ";
				$sql .= "  FROM SFPC.TBTIPOCERTIDAO A, SFPC.TBFORNECEDORCERTIDAO B, SFPC.TBFORNECEDORCREDENCIADO C ";
				$sql .= " WHERE A.CTIPCECODI = B.CTIPCECODI AND B.AFORCRSEQU = C.AFORCRSEQU ";
				$sql .= "   AND C.NFORCRMAIL <> '' AND B.AFORCRSEQU = $Fornecedor[0]";
				$sql .= "   AND B.DFORCEVALI < '".date('Y-m-d')."' ";
				$sql .= " ORDER BY 1,3,4";
				$res  = $db->query($sql);
				if( PEAR::isError($res) ){
						$Texto = "$ErroPrograma\nLinha: ".__LINE__."\n$Inicio\nSql: $sql";
						EnviaEmail($Email, $Assunto, $Texto, $From);
						exit;
				}else{
						$Rows = $res->numRows();
						for( $Certidoes = null, $i=0; $i<$Rows;$i++ ){
								$Linha = $res->fetchRow();
								if( $i == 0 ){
										$Sequencial = $Linha[0];
										$Nome       = $Linha[1];
										$Email      = $Linha[2];
										$Email2      = $Linha[6];
								}
								if( $Linha[5] < date("Y-m-d") ){
										$Certidoes[$i]    = $Linha[4];
										$DataValidade[$i] = $Linha[5];
								}
						}
  					if( $Email != "" ){
								# Envia a e-mail para usuário #
								if ($Fornecedor[2]){//CNPJ
										$Corpo  = "Fornecedor [".FormataCNPJ($Fornecedor[2])."] $Nome,\n\n\t";
								} else {//CPF
										$Corpo  = "Fornecedor [".FormataCPF($Fornecedor[3])."] $Nome,\n\n\t";
								}
								$Corpo .= "Verificamos em nossos sistemas que a(s) sua(s) certidão(ões) ";
								$Corpo .= "abaixo descrita(s) está(ão) com prazo(s) de validade vencido(s):\n\n";

								for( $i=0;$i<count($Certidoes);$i++){
										if( $i == 0 ){
												$Corpo .= " VALIDADE   CERTIDÃO\n";
										}

										$Corpo .= " ".substr($DataValidade[$i],8,2)."/".substr($DataValidade[$i],5,2)."/".substr($DataValidade[$i],0,4)." $Certidoes[$i]\n";
								}

								$Corpo .= "\n1 – Para os fornecedores que realizaram atualização ou cadastramento até o dia 31.05.2007, será necessário realizar novo cadastramento. Sendo assim, consultar informações no sítio http://www.recife.pe.gov.br/portalcompras/fornecedores/ConsInformacaoFornecedores.php, no intuito de verificar novos procedimentos, dentre eles a atualização de documentação contábil.\n\n";
								$Corpo .= "2 – Para os fornecedores cadastrados e/ou atualizados a partir de 1º de junho de 2007, após obter novas certidões, preencher o formulário de atualização de Certidões do SICREF, que se encontra disponível no sítio http://www.recife.pe.gov.br/portalcompras/fornecedores/ConsInformacaoFornecedores.php e enviar ao protocolo da GGLIC – 2º andar, sala 19 para regularizar sua situação no SICREF - Sistema de Credenciamento de Fornecedores da Prefeitura do Recife.\n\n";

                $Corpo .= "Prefeitura do Recife\n";
								$Corpo .= "Gerência Geral de Licitações e Compras - GGLIC\n";
								$Corpo .= "Gerência de Serviços de Credenciamento de Fornecedores - GSCF\n";
								$Corpo .= "Cais do Apolo, 925 - 11º andar\n";
								$Corpo .= "CEP:50030-903 - Bairro do Recife - Recife - PE\n";
								$Corpo .= "Telefones: 3232-8275/ 8368";
								EnviaEmail($Email, "Certidões Sem Validade no Portal de Compras", $Corpo, $From);

								if($Email2!="" and !is_null($Email2) ){
									EnviaEmail($Email2, "Certidões Sem Validade no Portal de Compras", $Corpo, $From);
								}
						}
				}
		}
}
$db->disconnect();
?>
