<?php
# -------------------------------------------------------------------------
# Portal da DGCO teste 
# Programa: CadPregaoPresencialLance.php
# Autor:    Hélio Miranda
# Data:     29/07/2016
# Objetivo: Programa de Lances do Pregão Presencial
# -------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		24/10/2018
# Objetivo: Tarefa Redmine 73662
# -------------------------------------------------------------------------

header("Content-Type: text/html; charset=UTF-8",true);


# Acesso ao arquivo de funções #  
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabUsuarioAlterar.php' );  

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$UsuarioCodigo = $_POST['UsuarioCodigo'];
		$Critica       = $_POST['Critica'];
		
		$_SESSION['Botao']						= $_POST['Botao'];
		$_SESSION['CodSituacaoClassificacao']	= $_POST['CodSituacaoClassificacao'];
}else{
		$Critica       							= $_GET['Critica'];
		$Mensagem      							= urldecode($_GET['Mensagem']);
		$Mens          							= $_GET['Mens'];
		$Tipo          							= $_GET['Tipo'];
		$_SESSION['CodFornecedorSelecionado']	= $_GET['CodFornecedorSelecionado'];

}

//$_SESSION['UltimaSessaoDesfeita'] = False;
$AliquotaVantagem = 5;

if(($_POST['MotivoSituacao'] == null or $_POST['MotivoSituacao'] == "") and $_POST['CodSituacaoClassificacao'] <> 1)
{
	$_SESSION['MotivoSituacao'] = $Linha[0];
}

$TamanhoMaximoMotivo = 500;

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "CadPregaoPresencialLance.php";

if($Critica == 1){
		# Critica dos Campos #
		$Mens     = 0;
		$Mensagem = "Informe: ";	
}

if($_SESSION['Botao'] == "Limpar")
{
	$_SESSION['Botao']					= null;
	$_SESSION['RazaoSocial']			= null;
	$_SESSION['CpfCnpj']				= null;
	$_SESSION['RepresentanteNome']		= null;
	$_SESSION['RepresentanteRG']		= null;		
	$_SESSION['RepresentanteOrgaoUF']	= null;
}	

if($_SESSION['Botao'] == "DesfazerUltimaRodada") 
{
	$_SESSION['Botao'] = null;
	$UltimaRodada 						= ($_POST['RodadaAtual'] - 1); 
	$TotalParticipantes					= $_POST['TotalParticipantes'];
	$CodLoteSelecionado					= $_SESSION['CodLoteSelecionado'];
	$CodSituacaoClassificacao			= $_SESSION['CodSituacaoClassificacao'];
	
	if($UltimaRodada > 0)
	{
		$db     = Conexao();
		$_SESSION['UltimaSessaoDesfeita'] 	= True; 
		
		$sql 			= "SELECT la.vpreglvall FROM sfpc.tbpregaopresenciallance la WHERE cpreglnumr = $UltimaRodada AND cpregtsequ = $CodLoteSelecionado";
		$resUltimoPreco = $db->query($sql);
		
		if (PEAR::isError($resUltimoPreco)) {
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
			$LinhaUltimoValorDeLance  		= $resUltimoPreco->fetchRow();
		}		
		
		for($itr = 0; $itr < $TotalParticipantes; ++ $itr)
		{
			$_SESSION['UltimoPreco_'.$itr] 	= $LinhaUltimoValorDeLance[0];
			
			$LinhaUltimoValorDeLance  		= $resUltimoPreco->fetchRow();
		}
		
		$sqlEF = "SELECT COUNT(la.cpreglsequ) FROM sfpc.tbpregaopresenciallance la WHERE la.fpreglefic = 1 AND la.cpregtsequ = $CodLoteSelecionado";
		$resEF = $db->query($sqlEF);

		if (PEAR::isError($resEF)) {
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}
		else
		{
			$LinhaEF  	= $resEF->fetchRow();
			$EF			= $LinhaEF[0];
		}			
		
		if($EF > 0)
		{
			$sql = "UPDATE sfpc.tbpregaopresenciallance SET fpreglurod = 0 WHERE cpregtsequ = $CodLoteSelecionado AND fpreglurod = 1";		
			$res = $db->query($sql);
			if (PEAR::isError($res)) {
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			}			
		}
		
		$sql = "DELETE FROM sfpc.tbpregaopresenciallance WHERE cpreglnumr = $UltimaRodada AND cpregtsequ = $CodLoteSelecionado";
		$res = $db->query($sql);
		if (PEAR::isError($res)) {
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}		
		$sql = "UPDATE sfpc.tbpregaopresenciallote SET cpregfsequ = null, vpregtvalv = 0.00, cpreslsequ = 1 WHERE cpregtsequ = $CodLoteSelecionado";		
		$res = $db->query($sql);
		if (PEAR::isError($res)) {
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}		

		$db->disconnect();
		
					
		echo "<script>opener.document.CadPregaoPresencialSessaoPublica.Destino.value = 'E';</script>";
		echo "<script>opener.document.CadPregaoPresencialSessaoPublica.CodLoteSelecionado.value = $CodLoteSelecionado;</script>";
		echo "<script>opener.document.CadPregaoPresencialSessaoPublica.Botao.value = 'SelecionarLote';</script>";
		echo "<script>opener.document.CadPregaoPresencialSessaoPublica.submit()</script>";			
		
		$_SESSION['Mens'] = 1;
		$_SESSION['Tipo'] = 1;
		$_SESSION['Mensagem'] .= "- Última Rodada de Lances removida com sucesso!";		
	}
}

if($_SESSION['Botao'] == "ProximaRodada")
{
	$DebugMod = False;
	
	$_SESSION['Botao'] = null;
	$_SESSION['UltimaSessaoDesfeita']   = False;
	$RodadaAtual 						= $_POST['RodadaAtual'];
	$TotalParticipantes					= $_POST['TotalParticipantes'];
	$CodLoteSelecionado					= $_SESSION['CodLoteSelecionado'];
	$CodSituacaoClassificacao			= $_SESSION['CodSituacaoClassificacao'];
	$PreenchimentoCorreto 				= True;
	$TotalLancesInseridos				= 0;
	$CodFornecedorVencedor 				= 0;
	$FornecedorVencedor					= array();
	$EmpateFicto 						= False;
	$LoteFinalizado 					= False;
	$_SESSION['ValorReferenciaOE']		= 0;
	
	if($TotalParticipantes > 0)
	{
		$db     = Conexao();
		
		
		$UltimoValorDeLance = 0;
		
		if($RodadaAtual > 1)
		{
			#Recebe o último valor vencedor da rodada anterior#
			$RodadaAnterior = $RodadaAtual - 1;
			
			$sql = "SELECT la.vpreglvall FROM sfpc.tbpregaopresenciallance la WHERE la.cpreglnumr = $RodadaAnterior AND la.fpregllven = 1 AND la.cpregtsequ = $CodLoteSelecionado";
			$res = $db->query($sql);
			
			if (PEAR::isError($res)) {
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			}else{
					$LinhaUltimoValorDeLance  	= $res->fetchRow();
					$UltimoValorDeLance			= $LinhaUltimoValorDeLance[0]; 
			}			
		}
		
		$TotalParticipantesRodada	= $TotalParticipantes;
		
	//Detecta Empate Ficto
	
		$sqlEF = "SELECT COUNT(la.cpreglsequ) FROM sfpc.tbpregaopresenciallance la WHERE la.fpreglefic = 1 AND la.cpregtsequ = $CodLoteSelecionado";
		$resEF = $db->query($sqlEF);

		if (PEAR::isError($resEF)) {
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}
		else
		{
			$LinhaEF  	= $resEF->fetchRow();
			$EF			= $LinhaEF[0];
			
			if($EF > 0)
			{
				$EmpateFicto = True;
				
				if($_SESSION['ValorReferenciaOE'] == 0 or $_SESSION['ValorReferenciaOE'] == null)
				{
					$UltimaRodadaValida = $RodadaAtual - 2;
					
					if($_SESSION['PregaoTipo'] == 'N')
					{
						$tipoBusca = "MIN";
					}
					else
					{
						$tipoBusca = "MAX";
					}

					//Search for the lowest price
					$sql 			= "SELECT $tipoBusca(la.vpreglvall) FROM sfpc.tbpregaopresenciallance la, sfpc.tbpregaopresencialfornecedor fn WHERE la.cpregfsequ = fn.cpregfsequ AND cpreglnumr = $UltimaRodadaValida AND la.vpreglvall > 0 AND cpregtsequ = $CodLoteSelecionado AND fn.fpregfmepp = 0 GROUP BY la.cpregfsequ, fn.fpregfmepp LIMIT 1";
					$resUltimoPreco = $db->query($sql);

					if (PEAR::isError($resUltimoPreco)) {
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
					}else{
						$LinhaUltimoValorDeLance  	= $resUltimoPreco->fetchRow();

						if($LinhaUltimoValorDeLance[0] > 0)
						{
							$_SESSION['ValorReferenciaOE'] = $LinhaUltimoValorDeLance[0];
							
							$ValorReferenciaOE = $_SESSION['ValorReferenciaOE'];
						}
					}

					if($DebugMod == True)
					{
						print "<br /># VERIFICAÇÃO DE EMPATE FÍCTO INICIAL: <br />";
						print "EF Total: ".$EF.", ValRefOE: ".$ValorReferenciaOE.", Ultima Rodada Válida: ".$UltimaRodadaValida."<br />";
						print "SQL: ".$sql."<br />";
					}					
				}
				else
				{
					$ValorReferenciaOE = $_SESSION['ValorReferenciaOE'];
				}
			}
			else
			{
				$EmpateFicto = False;
				$ValorReferenciaOE = 0;
				$_SESSION['ValorReferenciaOE'] = 0;
			}
		}
		
		$ValorReferenciaOE = $_SESSION['ValorReferenciaOE'];
	//Fim	
	
		if($DebugMod == True)
		{
			print "EF: ".$EmpateFicto.", ValRefOE: ".$ValorReferenciaOE."<br />";
		}
	
		if($EmpateFicto == False)
		{	
	
			if($DebugMod == True)
			{
				print "Não há EMPATE FÍCTO; <br />";
			}
			
			for($itr = 0; $itr < $TotalParticipantes; ++ $itr)
			{				
				$CodFornecedor					= $_POST['CodFornecedor_'.$itr];
				$CodPrecoInicial				= $_POST['CodPrecoInicial_'.$itr];
				$ValLance 						= $_POST['ValLance_'.$itr];
				$_SESSION['ValLance_'.$itr]		= $ValLance;
				$PrecoInicial 					= $_POST['PrecoInicial_'.$itr];
				$TipoEmpresa 					= $_POST['TipoEmpresa_'.$itr];
				$NomeFornecedor					= $_POST['NomeFornecedor_'.$itr];
				$EmpateFicto 					= 0;  
				$ConclusaoEmpateFicto 			= $_POST['EmpateFicto'];
				$_SESSION['UltimoPreco_'.$itr]	= null;
				
				$ValLance  		= str_replace(".", "", $ValLance);			
				$ValLance  		= str_replace(",", ".", $ValLance);
			
				
				if($ConclusaoEmpateFicto == '' or $ConclusaoEmpateFicto == null)
				{
					$ConclusaoEmpateFicto = 0;
				}
					

				//Valor de referência do Preco inicial
				if($itr == 0)
				{
					$ValorReferenciaPrecoInicial = $PrecoInicial;
					
					if(!is_numeric($ValorReferenciaPrecoInicial))
					{
						$ValorReferenciaPrecoInicial = 0.00;
					}
					
					if($ValorReferenciaPrecoInicial == "" or $ValorReferenciaPrecoInicial == null)
					{
						$ValorReferenciaPrecoInicial = 0.00;
					}				
					
					if($_SESSION['PregaoTipo'] == 'N')
					{
						for($itrB = 0; $itrB < $TotalParticipantes; ++ $itrB)
						{
							$ValA = $_POST['PrecoInicial_'.$itrB];
							
							if(!is_numeric($ValA))
							{
								$ValA = 0.00;
							}
							
							if($ValA == "" or $ValA == null)
							{
								$ValA = 0.00;
							}						
							if($ValA > 0)
							{
								if($ValorReferenciaPrecoInicial == 0)
								{
									$ValorReferenciaPrecoInicial = $ValA;
								}
								
								if($ValA > 0 and $ValA < $ValorReferenciaPrecoInicial)
								{
									$ValorReferenciaPrecoInicial = $ValA;
								}
							}
						}
					}
					else
					{
						for($itrB = 0; $itrB < $TotalParticipantes; ++ $itrB)
						{
							$ValA = $_POST['PrecoInicial_'.$itrB];
							
							if(!is_numeric($ValA))
							{
								$ValA = 0.00;
							}
							
							if($ValA == "" or $ValA == null)
							{
								$ValA = 0.00;
							}						
							if($ValA > 0)
							{
								if($ValorReferenciaPrecoInicial == 0)
								{
									$ValorReferenciaPrecoInicial = $ValA;
								}
								
								if($ValA > 0 and $ValA > $ValorReferenciaPrecoInicial)
								{
									$ValorReferenciaPrecoInicial = $ValA;
								}
							}
						}
					}			
				}			
				
				//Valor de referência
				if($itr == 0)
				{
					if($RodadaAtual == 1)
					{
						$ValorReferencia = $ValLance;
					}
					else
					{
						$ValorReferencia = $UltimoValorDeLance;  
					}
					
					if(!is_numeric($ValorReferencia))
					{
						$ValorReferencia = 0.00;
					}
					
					if($ValorReferencia == "" or $ValorReferencia == null)
					{
						$ValorReferencia = 0.00;
					}					
					
					if($_SESSION['PregaoTipo'] == 'N')
					{
						for($itrB = 0; $itrB < $TotalParticipantes; ++ $itrB)
						{
							$ValA = $_POST['ValLance_'.$itrB];
							$ValA  = str_replace(".", "", $ValA);
							$ValA  = str_replace(",", ".", $ValA);
							
							if(!is_numeric($ValA))
							{
								$ValA = 0.00;
							}
							
							if($ValA == "" or $ValA == null)
							{
								$ValA = 0.00;
							}						
							if($ValA > 0)
							{
								if($ValorReferencia == 0)
								{
									$ValorReferencia = $ValA;
								}
								
								if($ValA > 0 and $ValA < $ValorReferencia)
								{
									$ValorReferencia = $ValA;
								}
							}
						}
					}
					else
					{
						for($itrB = 0; $itrB < $TotalParticipantes; ++ $itrB)
						{
							$ValA = $_POST['ValLance_'.$itrB];
							$ValA  = str_replace(".", "", $ValA);
							$ValA  = str_replace(",", ".", $ValA);
							
							if(!is_numeric($ValA))
							{
								$ValA = 0.00;
							}
							
							if($ValA == "" or $ValA == null) 
							{
								$ValA = 0.00;
							}						
							if($ValA > 0)
							{
								if($ValorReferencia == 0)
								{
									$ValorReferencia = $ValA;
								}
								
								if($ValA > 0 and $ValA > $ValorReferencia)
								{
									$ValorReferencia = $ValA;
								}
							}
						}
					}			
				}
				
				if($ValorReferenciaPrecoInicial == 0)
				{
					$PreenchimentoCorreto = False;
					
					$_SESSION['Mens'] = 1;
					$_SESSION['Tipo'] = 2;
					$_SESSION['Mensagem'] .= "- O Preço Inicial não pode ser zero! <br />";					
				}
				
				if($ValorReferencia == 0)
				{
					$ValorReferencia = $ValorReferenciaPrecoInicial;
				}
				
				if(!is_numeric($ValLance))
				{
					$ValLance = 0.00;
				}
				
				if($ValLance == "" or $ValLance == null)
				{
					$ValLance = 0.00;
				}			
				
				//Validação do Valor do Lance referentye ao Preco Inicial
				if(($ValLance > 0 or $itr == 0 ))
				{				
					if($RodadaAtual == 1 and $PrecoInicial == $ValorReferenciaPrecoInicial)
					{
						if($_SESSION['PregaoTipo'] == 'N')
						{
							if($ValLance > $ValorReferenciaPrecoInicial)
							{
								$PreenchimentoCorreto = False;
								
								$_SESSION['Mens'] = 1;
								$_SESSION['Tipo'] = 2;
								$_SESSION['Mensagem'] .= "- Valor [".$ValLance."] 'Superior ao Preço Inicial de Menor Valor' para a Rodada de Lances Atual! <br />";							
							}
							else if ($ValLance == $ValorReferenciaPrecoInicial)
							{
								$PreenchimentoCorreto = False;
								
								$_SESSION['Mens'] = 1;
								$_SESSION['Tipo'] = 2;
								$_SESSION['Mensagem'] .= "- Valor [".$ValLance."] 'Igual ao Preço Inicial de Menor Valor' para a Rodada de Lances Atual! <br />";								
							}
						}
						else
						{
							if($ValLance < $ValorReferenciaPrecoInicial)
							{
								$PreenchimentoCorreto = False;
								
								$_SESSION['Mens'] = 1;
								$_SESSION['Tipo'] = 2;
								$_SESSION['Mensagem'] .= "- Valor [".$ValLance."] 'Inferior a Maior Oferta Inicial' para a Rodada de Lances Atual! <br />";							
							}
							else if ($ValLance == $ValorReferenciaPrecoInicial)
							{
								$PreenchimentoCorreto = False;
								
								$_SESSION['Mens'] = 1;
								$_SESSION['Tipo'] = 2;
								$_SESSION['Mensagem'] .= "- Valor [".$ValLance."] 'Igual a Maior Oferta Inicial' para a Rodada de Lances Atual! <br />";								
							}
						}
					}
					
					//Valor de referência do Preco inicial
					if($itr == 0 and $RodadaAtual > 1  and $PreenchimentoCorreto == True)
					{
						$ValorIgualARodadaAnterior = False;
						
						if($ValLance == $UltimoValorDeLance)
						{
							$ValorIgualARodadaAnterior = True;
						}
						
						if($_SESSION['PregaoTipo'] == 'N')
						{
							for($itrB = 0; $itrB < $TotalParticipantes; ++ $itrB)
							{
								if($PreenchimentoCorreto == False)
								{
									break;
								}
								
								for($itrC = ($itrB + 1); $itrC < $TotalParticipantes; ++ $itrC)
								{
									
									if($PreenchimentoCorreto == False)
									{
										break;
									}								
									
									$ValA = $_POST['ValLance_'.$itrB];
									$ValB = $_POST['ValLance_'.$itrC];
									
									$ValA  = str_replace(".", "", $ValA);
									$ValB  = str_replace(".", "", $ValB);								
									
									$ValA  = str_replace(",", ".", $ValA);
									$ValB  = str_replace(",", ".", $ValB);
									
									if(!is_numeric($ValA))
									{
										$ValA = 0.00;
									}
									
									if($ValA == "" or $ValA == null)
									{
										$ValA = 0.00;
									}

									if(!is_numeric($ValB))
									{
										$ValB = 0.00;
									}
									
									if($ValB == "" or $ValB == null)
									{
										$ValB = 0.00;
									}

									if($DebugMod == True)
									{
										print "<br /># VALIDAÇÃO DE PREÇOS: <br />";
										print "Validação Preços - Itr: ".$itr.", ItrB: ".$itrB.", ItrC: ".$itrC.", Participantes: ".$TotalParticipantes.", Rod. Atual: ".$RodadaAtual.", ValA: ".$ValA.", ValB: ".$ValB.", UltValLan: ".$UltimoValorDeLance."<br />";
									}									
									
									if($ValA <= 0 and ($itrC + 1) == $TotalParticipantes and $ValB > 0 and $PreenchimentoCorreto == True)
									{
										$ValA = $ValB;
										$ValB = 0;
										
										if($DebugMod == True)
										{
											print "<br /># TRANSFERÊNCIA DE VALORES: <br />";
											print "Transferência de Valores de Validação - ValA: ".$ValA.", ValB: ".$ValB."<br />";
										}
									}
									
									if($ValA > 0)
									{	
										if($RodadaAtual > 1 and $ValA > $UltimoValorDeLance)
										{
											$PreenchimentoCorreto = False;
											
											$_SESSION['Mens'] = 1;
											$_SESSION['Tipo'] = 2;
											$_SESSION['Mensagem'] .= "- Valores 'Superiores ao vendecor da Rodada Anterior' para a Rodada de Lances Atual! <br />";											
										}
										else if (($RodadaAtual > 1 and $ValA == $UltimoValorDeLance) or $ValorIgualARodadaAnterior == True)
										{
											$PreenchimentoCorreto = False;
											
											$_SESSION['Mens'] = 1;
											$_SESSION['Tipo'] = 2;
											$_SESSION['Mensagem'] .= "- Valores 'Iguais ao vendecor da Rodada Anterior' para a Rodada de Lances Atual! <br />";										
										}
										else if($ValA > 0 and $ValA < $ValB)
										{
											$PreenchimentoCorreto = False;
											
											$_SESSION['Mens'] = 1;
											$_SESSION['Tipo'] = 2;
											$_SESSION['Mensagem'] .= "- Valores em 'Ordem Incorreta' para a Rodada de Lances Atual! <br />";											
										}
										else if ($ValA > 0 and $ValA == $ValB)
										{
											$PreenchimentoCorreto = False;
											
											$_SESSION['Mens'] = 1;
											$_SESSION['Tipo'] = 2;
											$_SESSION['Mensagem'] .= "- Valores 'Duplicados' para a Rodada de Lances Atual! <br />";											
										}
									}
								}							
							}
						}
						else
						{
							for($itrB = 0; $itrB < $TotalParticipantes; ++ $itrB)
							{
								if($PreenchimentoCorreto == False)
								{
									break;
								}
									
								for($itrC = ($itrB + 1); $itrC < $TotalParticipantes; ++ $itrC)
								{
									if($PreenchimentoCorreto == False)
									{
										break;
									}								
									
									$ValA = $_POST['ValLance_'.$itrB];
									$ValB = $_POST['ValLance_'.$itrC];
									
									$ValA  = str_replace(".", "", $ValA);
									$ValB  = str_replace(".", "", $ValB);								
									
									$ValA  = str_replace(",", ".", $ValA);
									$ValB  = str_replace(",", ".", $ValB);								
									
									if(!is_numeric($ValA))
									{
										$ValA = 0.00;
									}
									
									if($ValA == "" or $ValA == null)
									{
										$ValA = 0.00;
									}

									if(!is_numeric($ValB))
									{
										$ValB = 0.00;
									}
									
									if($ValB == "" or $ValB == null)
									{
										$ValB = 0.00;
									}								
									
									if($ValA > 0)
									{
										if($RodadaAtual > 1 and $ValA > $UltimoValorDeLance)
										{
											$PreenchimentoCorreto = False;
											
											$PreenchimentoCorreto = False;
											$_SESSION['Mens'] = 1;
											$_SESSION['Tipo'] = 2;
											$_SESSION['Mensagem'] .= "- Ofertas 'Inferiores ao vendecor da Rodada Anterior' para a Rodada de Lances Atual! <br />";											
										}
										else if (($RodadaAtual > 1 and $ValA == $UltimoValorDeLance) or $ValorIgualARodadaAnterior == True)
										{
											$PreenchimentoCorreto = False;
											
											$_SESSION['Mens'] = 1;
											$_SESSION['Tipo'] = 2;
											$_SESSION['Mensagem'] .= "- Ofertas 'Iguais ao vendecor da Rodada Anterior' para a Rodada de Lances Atual! <br />";										
										}									
										else if($ValA > 0 and $ValA > $ValB)
										{
											$PreenchimentoCorreto = False;
											
											$_SESSION['Mens'] = 1;
											$_SESSION['Tipo'] = 2;
											$_SESSION['Mensagem'] .= "- Valores de Oferta em 'Ordem Incorreta' para a Rodada de Lances Atual! <br />";										
										}
										else if ($ValA > 0 and $ValA == $ValB)
										{
											$PreenchimentoCorreto = False;
											
											$_SESSION['Mens'] = 1;
											$_SESSION['Tipo'] = 2;
											$_SESSION['Mensagem'] .= "- Ofertas 'Duplicadas' para a Rodada de Lances Atual! <br />";										
										}
									}
								}							
							}
						}			
					}				
					
				}
				
				if($itr == 0 and $ValorReferenciaPrecoInicial > 0 and $PreenchimentoCorreto == True)
				{	
					$ValReferenciaDuplicidade = 0;
					
					for($itrB = 0; $itrB < $TotalParticipantes; ++ $itrB)
					{
						if($_SESSION['PregaoTipo'] == 'N')
						{
							for($itrB = 0; $itrB < $TotalParticipantes; ++ $itrB)
							{
								$ValA = $_POST['ValLance_'.$itrB];
								$PreA = $_POST['PrecoInicial_'.$itrB];

								$ValA  = str_replace(".", "", $ValA);
								$PreA  = str_replace(".", "", $PreA);							
								
								$ValA  = str_replace(",", ".", $ValA);
								$PreA  = str_replace(",", ".", $PreA);							
								
								if(!is_numeric($ValA))
								{
									$ValA = 0;
								}
								
								if($ValA == "" or $ValA == null)
								{
									$ValA = 0;
								}
								
								if(!is_numeric($PreA))
								{
									$PreA = 0;
								}
								
								if($PreA == "" or $PreA == null)
								{
									$PreA = 0;
								}																											
								
								if($ValA > 0)
								{	
							
									if($itrB == 0 and $ValReferenciaDuplicidade == 0)
									{
										$ValReferenciaDuplicidade = $ValA;
									}
									else if($itrB > 0 and $ValReferenciaDuplicidade > 0)
									{
										if($ValReferenciaDuplicidade == $ValA)
										{
											$PreenchimentoCorreto = False;
										
											$_SESSION['Mens'] = 1;
											$_SESSION['Tipo'] = 2;
											$_SESSION['Mensagem'] .= "- Valores 'Duplicados' para a Rodada de Lances Atual! <br />";												
										}
									}
									
									if($ValA >= $ValorReferenciaPrecoInicial)
									{
										$TotalParticipantesRodada = $TotalParticipantesRodada - 1;
									}
									if($ValA > $PreA and $PreenchimentoCorreto == True)
									{
										$PreenchimentoCorreto = False;
										
										$_SESSION['Mens'] = 1;
										$_SESSION['Tipo'] = 2;
										$_SESSION['Mensagem'] .= "- O Fornecedor não pode dar um Lance 'Superior' ao seu 'Preço Inicial'! <br />";
									}									
								}
								else if (($ValA == 0 and $PreA != $ValorReferenciaPrecoInicial) or ($ValA == 0 and $ValorReferencia < $ValorReferenciaPrecoInicial and $PreA == $ValorReferenciaPrecoInicial))
								{	
									$TotalParticipantesRodada = $TotalParticipantesRodada - 1;
								}
								else if ($ValA < 0 and $PreenchimentoCorreto == True)
								{
										$PreenchimentoCorreto = False;
										
										$_SESSION['Mens'] = 1;
										$_SESSION['Tipo'] = 2;
										$_SESSION['Mensagem'] .= "- Não podem haver valores 'Negativos'! <br />";									
								}
							}
						}
						else
						{	
							for($itrB = 0; $itrB < $TotalParticipantes; ++ $itrB)
							{
								$ValA = $_POST['ValLance_'.$itrB];
								$PreA = $_POST['PrecoInicial_'.$itrB];
								
								$ValA  = str_replace(".", "", $ValA);
								$PreA  = str_replace(".", "", $PreA);							
								
								$ValA  = str_replace(",", ".", $ValA);
								$PreA  = str_replace(",", ".", $PreA);							
								
								if(!is_numeric($ValA))
								{
									$ValA = 0;
								}
								
								if($ValA == "" or $ValA == null)
								{
									$ValA = 0;
								}

								if(!is_numeric($PreA))
								{
									$PreA = 0;
								}
								
								if($PreA == "" or $PreA == null)
								{
									$PreA = 0;
								}	
								
								if($ValA > 0)
								{	
									if($itrB == 0 and $ValReferenciaDuplicidade == 0)
									{
										$ValReferenciaDuplicidade = $ValA;
									}
									else if($itrB > 0 and $ValReferenciaDuplicidade > 0)
									{
										if($ValReferenciaDuplicidade == $ValA)
										{
											$PreenchimentoCorreto = False;
										
											$_SESSION['Mens'] = 1;
											$_SESSION['Tipo'] = 2;
											$_SESSION['Mensagem'] .= "- Ofertas 'Duplicadas' para a Rodada de Lances Atual! <br />";												
										}
									}
									
									if($ValA <= $ValorReferenciaPrecoInicial)
									{
										$TotalParticipantesRodada = $TotalParticipantesRodada - 1;
									}
									if($ValA < $PreA and $PreenchimentoCorreto == False)
									{
										
										
										$_SESSION['Mens'] = 1;
										$_SESSION['Tipo'] = 2;
										$_SESSION['Mensagem'] .= "- O Fornecedor não pode dar um Lance 'Inferior' a sua 'Oferta Inicial'! <br />";	
									}
								}
								else if (($ValA == 0 and $PreA != $ValorReferenciaPrecoInicial) or ($ValA == 0 and $ValorReferencia > $ValorReferenciaPrecoInicial and $PreA == $ValorReferenciaPrecoInicial))
								{
									$TotalParticipantesRodada = $TotalParticipantesRodada - 1;
								}
								else if ($ValA < 0 and $PreenchimentoCorreto == True)
								{
										$PreenchimentoCorreto = False;
										
										$_SESSION['Mens'] = 1;
										$_SESSION['Tipo'] = 2;
										$_SESSION['Mensagem'] .= "- Não podem haver valores 'Negativos'! <br />";									
								}
							}
						}					
					}
					
					if($_SESSION['PregaoTipo'] == 'N')
					{
						if($ValorReferencia > $ValorReferenciaPrecoInicial and $TotalParticipantesRodada == 1)
						{
							$ValorReferencia = $ValorReferenciaPrecoInicial;
						}
					}
					else
					{
						if($ValorReferencia < $ValorReferenciaPrecoInicial and $TotalParticipantesRodada == 1)
						{
							$ValorReferencia = $ValorReferenciaPrecoInicial;
						}					
					}				
				}
				
				if($ValorReferencia == $ValorReferenciaPrecoInicial and $ValorReferenciaPrecoInicial > 0 and $TotalParticipantesRodada == 0 and $RodadaAtual == 1)
				{
					$TotalParticipantesRodada = 1;
				}				
				
				if($PreenchimentoCorreto == True and $TotalParticipantesRodada > 0)
				{
					#Recebe o último código de Preço Inicial#
					$sql = "SELECT MAX(cpreglsequ) FROM sfpc.tbpregaopresenciallance";
					$res = $db->query($sql);
					
					if (PEAR::isError($res)) {
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
					}else{
							$LinhaLance  			= $res->fetchRow();
							$CodigoLance			= $LinhaLance[0] + 1;
					}			
					
					
					
					# Validação para Inclusão de Lance#
					
					
					$LanceVencedorRodada = ((($ValLance > 0 and $ValorReferencia > 0 and $ValorReferencia == $ValLance and $ValorReferencia != $ValorReferenciaPrecoInicial) or ($RodadaAtual == 1 and $TotalParticipantesRodada == 1 and $ValLance == 0 and $PrecoInicial == $ValorReferenciaPrecoInicial and $ValorReferencia == $ValorReferenciaPrecoInicial )) ? 1 : 0);
					$DescricaoLance = "";
					
					if($ValLance > 0)
					{
						if($_SESSION['PregaoTipo'] == 'N')
						{
							if($RodadaAtual == 1 and $ValLance > $ValorReferenciaPrecoInicial) 
							{
								$DescricaoLance = "APENAS REGISTRO DE VALOR";
							}
						}
						else
						{
							if($RodadaAtual == 1 and $ValLance < $ValorReferenciaPrecoInicial)
							{
								$DescricaoLance = "APENAS REGISTRO DE VALOR";
							}					
						}
					}
					
					//Guarda a mensagem de, apenas, registro de preço.
					if($DescricaoLance != "")
					{
						$_SESSION['Mens'] = 1;
						$_SESSION['Tipo'] = 1;
						$_SESSION['Mensagem'] .= "- Preço do Fornecedor: $NomeFornecedor registrado com sucesso! O mesmo, apenas, registrou lance. Sendo assim, não poderá participar das demais Rodadads de Lances. <br />";					
					}
					
					//Finaliza a rodada pelo Fornecedor com o menor preço inicial.
					if(($RodadaAtual == 1) and ($TotalParticipantesRodada == 1) and ($ValLance == 0) and ($PrecoInicial == $ValorReferenciaPrecoInicial) and ($ValorReferencia == $ValorReferenciaPrecoInicial ))
					{
						$DescricaoLance = "VENCEDOR PELO PREÇO INICIAL";
						
						$_SESSION['Mens'] = 1;
						$_SESSION['Tipo'] = 1;
						$_SESSION['Mensagem'] .= "- Fornecedor: $NomeFornecedor venceu a disputa pelo seu Preço Inicial. <br />";						
					}
					
					if($TotalParticipantes == 1 and $ValLance == 0)
					{
						if($PrecoInicial > 0)
						{
							$ValLance = $PrecoInicial;
							$LanceVencedorRodada = 1;
						}
						else
						{
							$PreenchimentoCorreto = False; 
							
							$_SESSION['Mens'] = 1;
							$_SESSION['Tipo'] = 2;
							
							if($_SESSION['PregaoTipo'] == 'N')
							{
								$_SESSION['Mensagem'] .= "- O Preço inicial do Fornecedor não deve ser zero! <br />"; 
							}						
							else
							{
								$_SESSION['Mensagem'] .= "- A Oferta inicial do Fornecedor não deve ser zero! <br />";
							}							
						}
					}
					else if($TotalParticipantes == 1 and $ValLance > 0) 
					{
							$PreenchimentoCorreto = False;
							
							$_SESSION['Mens'] = 1;
							$_SESSION['Tipo'] = 2;
							$_SESSION['Mensagem'] .= "- Como só há 01 Fornecedor participante da sessão, o valor do lance deverá ser zero, o que automaticamente colocará o valor do seu preço inicial como lance vencedor, e, caso seja necessário alterar o preço, tal procedimento deverá ser realizado na opção 'Renegociar Preço'! <br />";
					}
					
					$UltimaRodada = (($TotalParticipantesRodada == 0) ? 1 : 0);
					
					if(($TotalParticipantesRodada > 0 or $RodadaAtual == 1) and $PreenchimentoCorreto == True)
					{	
						if(count($CodFornecedoresVencedores) > 0)
						{
							$_SESSION['Mensagem'] .= "- EMPATE FÍCTO detectado! <br />";   
						}
						
						if($ConclusaoEmpateFicto == 1)
						{
							$UltimaRodada = 1;
						}
						
						if($ValorReferencia == $ValorReferenciaPrecoInicial and $RodadaAtual == 1)
						{
							$UltimaRodada = 1;
							
							if($LanceVencedorRodada == 1)
							{
								$sql = "UPDATE sfpc.tbpregaopresenciallote SET cpregfsequ = $CodFornecedor, vpregtvalv = $ValorReferenciaPrecoInicial, cpreslsequ = 2 WHERE cpregtsequ = $CodLoteSelecionado";
								$res = $db->query($sql);								
							}
						}						
						
						$sql  = "INSERT INTO sfpc.tbpregaopresenciallance( ";
						$sql .= "cpreglsequ, cpregfsequ, cpregtsequ, cpregpsequ, cpreglnumr, fpreglurod, vpreglvall, fpregllven, epregldesc, fpreglmpre, fpreglefic, fpreglrpfn, ";
						$sql .= "dpreglcada, ";
						$sql .= "tpreglulat ";
						$sql .= " ) VALUES ( ";
						$sql .= "$CodigoLance, $CodFornecedor, $CodLoteSelecionado, $CodPrecoInicial, $RodadaAtual, $UltimaRodada, $ValLance, $LanceVencedorRodada, '$DescricaoLance', 0, $EmpateFicto, 0, ";
						$sql .= "'".date("Y-m-d")."', ";
						$sql .= "'".date("Y-m-d H:i:s")."' )";
						
						$res  = $db->query($sql);
						
						
						if( PEAR::isError($res) ){
							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}
						
						$TotalLancesInseridos = $TotalLancesInseridos + 1;				

						if($UltimaRodada == 1 and $LanceVencedorRodada == 1 and $ValorReferencia != $ValorReferenciaPrecoInicial)
						{
							$sql = "UPDATE sfpc.tbpregaopresenciallote SET cpregfsequ = $CodFornecedor, vpregtvalv = $ValLance, cpreslsequ = 2 WHERE cpregtsequ = $CodLoteSelecionado";
							$res = $db->query($sql);						
						}			
					}					
				}
			}
		}
		else if ($_SESSION['ValorReferenciaOE'] > 0)
		{	
			//Empate Ficto == True
			//Laço for buscando o único preço do fornecedor de empate fícto 
			//Update na Tabela de lances 
			
			if($DebugMod == True)
			{
				print "Há EMPATE FÍCTO; <br />";
			}			
			
			$ValLanceEF 			= 0;
			$TotalLancesInseridos 	= 0;
			
			for($itr = 0; $itr < $TotalParticipantes; ++ $itr)
			{				
				$CodFornecedor					= $_POST['CodFornecedor_'.$itr];
				$ValLance 						= $_POST['ValLance_'.$itr];
				$TipoEmpresa 					= $_POST['TipoEmpresa_'.$itr];
				$NomeFornecedor					= $_POST['NomeFornecedor_'.$itr];
				$CodPrecoInicial				= $_POST['CodPrecoInicial_'.$itr];
				
				$ValLance  		= str_replace(".", "", $ValLance);			
				$ValLance  		= str_replace(",", ".", $ValLance);
				
				if($ValLance > 0 and $ValLanceEF == 0)
				{
					$ValLanceEF 		= $ValLance;
					$CodFornecedorEF 	= $CodFornecedor;
					$TipoEmpresaEF		= $TipoEmpresa;
					$NomeFornecedorEF 	= $NomeFornecedor;
				}
				/*
				$sql = "SELECT MAX(cpreglsequ) FROM sfpc.tbpregaopresenciallance";
				$resA = $db->query($sql);
				
				if (PEAR::isError($resA)) {
					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
					$LinhaLance  			= $resA->fetchRow();
					$CodigoLance			= $LinhaLance[0] + 1;
				}				
				
				$sql = "";
				
				$sql  = "INSERT INTO sfpc.tbpregaopresenciallance( ";
				$sql .= "cpreglsequ, cpregfsequ, cpregtsequ, cpregpsequ, cpreglnumr, fpreglurod, vpreglvall, fpregllven, epregldesc, fpreglmpre, fpreglefic, fpreglrpfn, ";
				$sql .= "dpreglcada, ";
				$sql .= "tpreglulat ";
				$sql .= " ) VALUES ( ";
				$sql .= "$CodigoLance, $CodFornecedor, $CodLoteSelecionado, $CodPrecoInicial, $RodadaAtual, 1, 0.00, 0, '', 0, 0, 0, ";
				$sql .= "'".date("Y-m-d")."', ";
				$sql .= "'".date("Y-m-d H:i:s")."' )";
				
				$resB  = $db->query($sql);
				
				if (PEAR::isError($resB)) {
					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}				
				
				$TotalLancesInseridos = $TotalLancesInseridos + 1;
				
				*/
			}
			
			
			if($ValLanceEF > 0 and $ValorReferenciaOE > 0)
			{	
				if($_SESSION['PregaoTipo'] == 'N')
				{
					
					if($DebugMod == True)
					{
						print "<br /> # COMPARAÇÃO EMPATE FÍCTO COM ÚLTIMO LANCE OE: <br />";
						print "ValorEF: ".$ValLanceEF.", ValOE: ".$ValorReferenciaOE."(".$_SESSION['ValorReferenciaOE'].") <br />";
					}					
					
					
					if($ValLanceEF >= $ValorReferenciaOE) 
					{
						
						if($DebugMod == True)
						{
							print "O valor de EF($ValLanceEF) é 'maior ou igual' ao Valor OE($ValorReferenciaOE); <br />";
						}						
						
						$_SESSION['Mens'] = 1;
						$_SESSION['Tipo'] = 2;
						$_SESSION['Mensagem'] .= "- Valor NÃO deve ser IGUAL ou SUPERIOR ao Valor do Vencedor da Rodada anterior! <br />";							
						
						$PermissaoGravacaoEmpate = False;
						$PreenchimentoCorreto 	 = False;
						$LoteFinalizado 		 = False;
						

						$sqlF = "DELETE FROM sfpc.tbpregaopresenciallance WHERE cpregtsequ = $CodLoteSelecionado AND fpreglurod = 1 AND cpreglnumr > $UltimaRodadaValida";
						$resF = $db->query($sqlF);						
					}
					else
					{
						$PermissaoGravacaoEmpate = True;
					}
				}
				else
				{
					if($ValLanceEF <= $ValorReferenciaOE)
					{
						if($DebugMod == True)
						{
							print "A oferta de EF($ValLanceEF) é 'menor ou igual' ao Valor OE($ValorReferenciaOE); <br />";
						}						
						
						$_SESSION['Mens'] = 1;
						$_SESSION['Tipo'] = 2;
						$_SESSION['Mensagem'] .= "- A oferta NÃO deve ser IGUAL ou INFERIOR a Oferta do Vencedor da Rodada anterior! <br />";						
						
						$PermissaoGravacaoEmpate = False;
						$PreenchimentoCorreto 	 = False;
						$LoteFinalizado 		 = False;
						
						$sqlF = "DELETE FROM sfpc.tbpregaopresenciallance WHERE cpregtsequ = $CodLoteSelecionado AND fpreglurod = 1 AND cpreglnumr > $UltimaRodadaValida";
						$resF = $db->query($sqlF);	
					}
					else
					{
						$PermissaoGravacaoEmpate = True;
					}							
				}
				
				if($PermissaoGravacaoEmpate == True AND $ValLanceEF > 0)
				{
					/*
					$sqlA = "UPDATE sfpc.tbpregaopresenciallance SET fpregllven = 1, vpreglvall = $ValLanceEF, epregldesc = 'VENCEDOR PROVISÓRIO POR EMPATE FÍCTO;' WHERE cpregtsequ = $CodLoteSelecionado AND cpregfsequ = $CodFornecedorEF AND fpreglurod = 1";
					$resA = $db->query($sqlA); 
					*/

					$sqlA = "UPDATE sfpc.tbpregaopresenciallance SET vpreglvall = $ValLanceEF, epregldesc = 'VENCEDOR PROVISÓRIO POR EMPATE FÍCTO;' WHERE cpregtsequ = $CodLoteSelecionado AND cpregfsequ = $CodFornecedorEF AND fpregllven = 1 AND fpreglefic = 1";
					$resA = $db->query($sqlA);					
					
					$sqlB = "UPDATE sfpc.tbpregaopresenciallote SET cpregfsequ = $CodFornecedorEF, vpregtvalv = $ValLanceEF, cpreslsequ = 2 WHERE cpregtsequ = $CodLoteSelecionado";
					$resB = $db->query($sqlB);
					
					$sqlC = "UPDATE sfpc.tbpregaopresenciallance SET fpreglurod = 0 WHERE cpregtsequ = $CodLoteSelecionado AND fpreglurod = 1";
					$resC = $db->query($sqlC);					
					
					$sqlD = "UPDATE sfpc.tbpregaopresenciallance SET fpreglurod = 1 WHERE cpregtsequ = $CodLoteSelecionado AND fpreglefic = 1";
					$resD = $db->query($sqlD);						

					$_SESSION['Mens'] = 1;
					$_SESSION['Tipo'] = 1;							
					$_SESSION['Mensagem'] .= "- Disputa Encerrada com Sucesso! <br />";						
					$_SESSION['Mensagem'] .= "- O Fornecedor: $NomeFornecedorEF".", ficou marcado como 'Vencedor Provisório', até que o Pregoeiro, após a análise classificatória, o marque como 'Vencedor Definitivo'! <br />";					
				}
			}
			else
			{						
				$sqlC = "DELETE FROM sfpc.tbpregaopresenciallance WHERE cpregtsequ = $CodLoteSelecionado AND (fpreglefic = 1 OR cpreglnumr > $UltimaRodadaValida)";
				$resC = $db->query($sqlC);

				$UltimaRodadaValida = $RodadaAtual - 2;
				
				$sqlD = "UPDATE sfpc.tbpregaopresenciallance SET fpreglurod = 1 WHERE cpregtsequ = $CodLoteSelecionado AND cpreglnumr = $UltimaRodadaValida";
				$resD = $db->query($sqlD);					
				
				$sqlE = "UPDATE sfpc.tbpregaopresenciallance SET fpregllven = 1, epregldesc = 'O FORNECEDOR COM DIREITO EMPATE FÍCTO NÃO DEU LANCE;' WHERE cpregtsequ = $CodLoteSelecionado AND cpreglnumr = $UltimaRodadaValida AND vpreglvall = $ValorReferenciaOE";
				$resE = $db->query($sqlE);

				$LoteFinalizado = True;
				
				if($DebugMod == True)
				{
					print "<br /># FORNECEDOR DE EMPATE FÍCTO NÃO DEU LANCE: <br />";
					print "SQL_C: ".$sqlC."<br />";
					print "SQL_D: ".$sqlD."<br />";
					print "SQL_E: ".$sqlE."<br />";
				}				
			}
							
		}

		if($TotalParticipantesRodada == 0 AND $LoteFinalizado == False)
		{
			if($RodadaAtual > 1)
			{
				$UltimaRodadaValida = $RodadaAtual - 1;
				
				if($_SESSION['PregaoTipo'] == 'N')
				{
					$tipoBusca = "MIN";
				}
				else
				{
					$tipoBusca = "MAX";
				}

				//Search for the lowest price
				$sql 			= "SELECT $tipoBusca(la.vpreglvall), la.cpregfsequ, fn.fpregfmepp FROM sfpc.tbpregaopresenciallance la, sfpc.tbpregaopresencialfornecedor fn WHERE la.cpregfsequ = fn.cpregfsequ AND cpreglnumr = $UltimaRodadaValida AND la.vpreglvall > 0 AND cpregtsequ = $CodLoteSelecionado AND fn.fpregfmepp = 0 GROUP BY la.cpregfsequ, fn.fpregfmepp LIMIT 1";
				$resUltimoPreco = $db->query($sql);

				if (PEAR::isError($resUltimoPreco)) {
					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
					$LinhaUltimoValorDeLance  	= $resUltimoPreco->fetchRow();
										
					$FornecedorVencedor[0] 		= $LinhaUltimoValorDeLance[0];
					$FornecedorVencedor[1] 		= $LinhaUltimoValorDeLance[1];
					$FornecedorVencedor[2] 		= $LinhaUltimoValorDeLance[2];

					if($FornecedorVencedor[0] > 0)
					{
						$_SESSION['ValorReferenciaOE'] = $FornecedorVencedor[0];
						
						$ValorReferenciaOE = $_SESSION['ValorReferenciaOE'];
					}
				}
				
				if($FornecedorVencedor[0] > 0 and $FornecedorVencedor[1] > 0 and $FornecedorVencedor[2] == 0)
				{	
					//Search for the lowest price of EPP, ME and MEI
					if($_SESSION['PregaoTipo'] == 'N')
					{
						$tipoBusca = "MIN";
					}
					else
					{
						$tipoBusca = "MAX";
					}

					$sql 					= " SELECT		la.vpreglvall, la.cpregfsequ, fn.fpregfmepp 
													FROM 	sfpc.tbpregaopresenciallance la, sfpc.tbpregaopresencialfornecedor fn 
													WHERE 	la.cpregfsequ = fn.cpregfsequ 
														AND la.cpregtsequ = $CodLoteSelecionado 
														AND	la.vpreglvall = (SELECT $tipoBusca(la.vpreglvall) 
																				FROM sfpc.tbpregaopresenciallance la, sfpc.tbpregaopresencialfornecedor fn 
																				WHERE la.cpregfsequ = fn.cpregfsequ 
																					AND la.cpregtsequ = $CodLoteSelecionado 
																					AND la.vpreglvall > 0 
																					AND fn.fpregfmepp > 0
																					AND fn.npregfnomr <> '')";
					$resUltimoPrecoMEEPPMEI = $db->query($sql);
					
					if (PEAR::isError($resUltimoPrecoMEEPPMEI)) {
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
					}else{
						$LinhaUltimoValorDeLanceEPPMEMEI  		= $resUltimoPrecoMEEPPMEI->fetchRow();
					}				
					
					if($LinhaUltimoValorDeLanceEPPMEMEI[0] > 0)
					{
						$PercentualVantagem = $AliquotaVantagem / 100; 
						
						$VantagemEmpresaEppMeMeiN = 1 - $PercentualVantagem;
						$VantagemEmpresaEppMeMeiM = 1 + $PercentualVantagem;
						
						$ValA = $LinhaUltimoValorDeLanceEPPMEMEI[0];
						
						if($DebugMod == True)
						{
							print "<br /> # VALIDAÇÃO DE EMPATE FÍCTO: <br />";
							print "Validação E.F. - Val. (EPP, ME, MEI): ".$ValA.", Val. (OE): ".$ValorReferenciaOE."<br />";
						}
						
						if($_SESSION['PregaoTipo'] == 'N') 
						{
							$ValA = $ValA * $VantagemEmpresaEppMeMeiN;
							
							if($ValorReferenciaOE >= $ValA)
							{
								$EmpateFicto = True;
							}
						}
						else 
						{  
							$ValA = $ValA * $VantagemEmpresaEppMeMeiM;
							
							if($ValorReferenciaOE <= $ValA)
							{
								$EmpateFicto = True;
							}
						}
					}					
					
				}

				if($RodadaAtual > 1 and $TotalParticipantesRodada == 0)
				{
					$UltimaRodadaValida = $RodadaAtual - 1;
					$sqlD = "UPDATE sfpc.tbpregaopresenciallance SET fpreglurod = 1 WHERE cpregtsequ = $CodLoteSelecionado AND cpreglnumr = $UltimaRodadaValida";
					$resD = $db->query($sqlD);		
				}				
				
				if($EmpateFicto == False)
				{
					$sql = "UPDATE sfpc.tbpregaopresenciallance SET fpreglurod = 1 WHERE cpreglnumr = $RodadaAnterior AND cpregtsequ = $CodLoteSelecionado";
					$res = $db->query($sql);

					$sqlVencedor = "SELECT la.cpregfsequ, la.vpreglvall, fn.npregfrazs 
									FROM sfpc.tbpregaopresenciallance la,
										 sfpc.tbpregaopresencialfornecedor fn
									WHERE 	la.cpreglnumr = $RodadaAnterior 
										AND la.cpregtsequ = $CodLoteSelecionado 
										AND la.fpreglurod = 1 
										AND la.fpregllven = 1
										AND	la.cpregfsequ = fn.cpregfsequ";
					
					$resultVencedor = $db->query($sqlVencedor);
					
					if (PEAR::isError($resultVencedor)) 
					{
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
					}
					else
					{
						$LinhaVencedor  		= $resultVencedor->fetchRow();
						$QuantidadeVencedor 	= $resultVencedor->numRows();
						
						if($QuantidadeVencedor > 0)
						{
							$sql = "UPDATE sfpc.tbpregaopresenciallote SET cpregfsequ = $LinhaVencedor[0], vpregtvalv = $LinhaVencedor[1], cpreslsequ = 2 WHERE cpregtsequ = $CodLoteSelecionado";
							$res = $db->query($sql);

							$_SESSION['Mens'] = 1;
							$_SESSION['Tipo'] = 1;							
							$_SESSION['Mensagem'] .= "- Disputa Encerrada com Sucesso! <br />";						
							$_SESSION['Mensagem'] .= "- O Fornecedor: $LinhaVencedor[2]".", ficou marcado como 'Vencedor Provisório', até que o Pregoeiro, após a análise classificatória, o marque como 'Vencedor Definitivo'! <br />";						
							
							
							echo "<script>opener.document.CadPregaoPresencialSessaoPublica.Destino.value = 'E';</script>";
							echo "<script>opener.document.CadPregaoPresencialSessaoPublica.CodLoteSelecionado.value = $CodLoteSelecionado;</script>";
							echo "<script>opener.document.CadPregaoPresencialSessaoPublica.Botao.value = 'SelecionarLote';</script>";
							echo "<script>opener.document.CadPregaoPresencialSessaoPublica.submit()</script>";							
						}
					}
				}
				else
				{

					for($itrB = 0; $itrB < $TotalParticipantes; ++ $itrB)
					{
						$CodFornecedor					= $_POST['CodFornecedor_'.$itrB];
						$CodPrecoInicial				= $_POST['CodPrecoInicial_'.$itrB];

						$sqlCod = "SELECT MAX(cpreglsequ) FROM sfpc.tbpregaopresenciallance";
						$resCod = $db->query($sqlCod);
						
						if (PEAR::isError($resCod)) {
							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlCod");
						}else{
								$LinhaLance  			= $resCod->fetchRow();
								$CodigoLance			= $LinhaLance[0] + 1;
						}						
						
						$sql  = "INSERT INTO sfpc.tbpregaopresenciallance( ";
						$sql .= "cpreglsequ, cpregfsequ, cpregtsequ, cpregpsequ, cpreglnumr, fpreglurod, vpreglvall, fpregllven, epregldesc, fpreglmpre, fpreglefic, fpreglrpfn,";
						$sql .= "dpreglcada, ";
						$sql .= "tpreglulat ";
						$sql .= " ) VALUES ( ";
						$sql .= "$CodigoLance, $CodFornecedor, $CodLoteSelecionado, $CodPrecoInicial, $RodadaAtual, 0, 0.00, 0, '', 0, 1, 0, ";
						$sql .= "'".date("Y-m-d")."', ";
						$sql .= "'".date("Y-m-d H:i:s")."' );";	

						$res  = $db->query($sql);						
						
						if( PEAR::isError($res) ){
							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}	

						$TotalLancesInseridos = $TotalLancesInseridos + 1;		
						
					}
					
					$sql = "UPDATE sfpc.tbpregaopresenciallance SET vpreglvall = $LinhaUltimoValorDeLanceEPPMEMEI[0], fpreglefic = 1, epregldesc = 'EMPATE FÍCTO', fpregllven = 1 WHERE cpreglnumr = $RodadaAtual AND cpregtsequ = $CodLoteSelecionado AND cpregfsequ = $LinhaUltimoValorDeLanceEPPMEMEI[1]";
					$res = $db->query($sql);						
				}
				
			}
		}
		
		if($TotalLancesInseridos < $TotalParticipantes)
		{
			$sql = "DELETE FROM sfpc.tbpregaopresenciallance WHERE cpreglnumr = $RodadaAtual AND cpregtsequ = $CodLoteSelecionado";
			$res = $db->query($sql);					
			
			if($DebugMod == True)
			{
				print "<br /># REMOVER RODADA ATUAL POR INCOERÊNCIA DE PARTICIPANTES POR LANCES: <br />";
				print "SQL: ".$sql."<br />";
			}			
		}				
		
		echo "<script>opener.document.CadPregaoPresencialSessaoPublica.Destino.value = 'E';</script>";
		echo "<script>opener.document.CadPregaoPresencialSessaoPublica.CodLoteSelecionado.value = $CodLoteSelecionado;</script>";
		echo "<script>opener.document.CadPregaoPresencialSessaoPublica.Botao.value = 'SelecionarLote';</script>";
		echo "<script>opener.document.CadPregaoPresencialSessaoPublica.submit()</script>";	 	
		
		if($PreenchimentoCorreto == True and $UltimaRodada == 0)
		{
			
			for($itrB = 0; $itrB < $TotalParticipantes; ++ $itrB)
			{			
				$_SESSION['ValLance_'.$itrB]	= null;
			}			
			
			$_SESSION['Mens'] = 1;
			$_SESSION['Tipo'] = 1;
			$_SESSION['Mensagem'] .= "- $RodadaAtual"."ª Rodada de Lances incluída com sucesso! <br />";
		}
		
		$TotalVencedores = 0;
		
		if($RodadaAtual > 1)
		{
		
			$sqlA = "SELECT COUNT(la.cpreglsequ) FROM sfpc.tbpregaopresenciallance la WHERE la.fpregllven = 1 AND la.fpreglurod = 1 AND la.cpregtsequ = $CodLoteSelecionado";
			$resA = $db->query($sqlA);
			
			if (PEAR::isError($resA)) {
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			}
			else
			{
				$LinhaA  			= $resA->fetchRow();
				$TotalVencedores	= $LinhaA[0];
			}
		}
		
		if($TotalVencedores > 1)
		{
			$UltimaRodada 						= ($_POST['RodadaAtual'] - 1);
			$_SESSION['UltimaSessaoDesfeita'] 	= True; 
			
			$sql 			= "SELECT la.vpreglvall FROM sfpc.tbpregaopresenciallance la WHERE cpreglnumr = $UltimaRodada AND cpregtsequ = $CodLoteSelecionado";
			$resUltimoPreco = $db->query($sql);
			
			if (PEAR::isError($resUltimoPreco)) {
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			}else{
				$LinhaUltimoValorDeLance  		= $resUltimoPreco->fetchRow();
			}		
			
			for($itr = 0; $itr < $TotalParticipantes; ++ $itr)
			{
				$_SESSION['UltimoPreco_'.$itr] 	= $LinhaUltimoValorDeLance[0];
				
				$LinhaUltimoValorDeLance  		= $resUltimoPreco->fetchRow();
			}
			
			$sqlEF = "SELECT COUNT(la.cpreglsequ) FROM sfpc.tbpregaopresenciallance la WHERE la.fpreglefic = 1 AND la.cpregtsequ = $CodLoteSelecionado";
			$resEF = $db->query($sqlEF);

			if (PEAR::isError($resEF)) {
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			}
			else
			{
				$LinhaEF  	= $resEF->fetchRow();
				$EF			= $LinhaEF[0];
			}			
			
			if($EF > 0)
			{
				$sql = "UPDATE sfpc.tbpregaopresenciallance SET fpreglurod = 0 WHERE cpregtsequ = $CodLoteSelecionado AND fpreglurod = 1";		
				$res = $db->query($sql);
				if (PEAR::isError($res)) {
					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}			
			}
			
			$sql = "DELETE FROM sfpc.tbpregaopresenciallance WHERE cpreglnumr = $UltimaRodada AND cpregtsequ = $CodLoteSelecionado";
			$res = $db->query($sql);
			if (PEAR::isError($res)) {
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			}		
			$sql = "UPDATE sfpc.tbpregaopresenciallote SET cpregfsequ = null, vpregtvalv = 0.00, cpreslsequ = 1 WHERE cpregtsequ = $CodLoteSelecionado";		
			$res = $db->query($sql);
			if (PEAR::isError($res)) {
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			}		
			
						
			echo "<script>opener.document.CadPregaoPresencialSessaoPublica.Destino.value = 'E';</script>";
			echo "<script>opener.document.CadPregaoPresencialSessaoPublica.CodLoteSelecionado.value = $CodLoteSelecionado;</script>";
			echo "<script>opener.document.CadPregaoPresencialSessaoPublica.Botao.value = 'SelecionarLote';</script>";
			echo "<script>opener.document.CadPregaoPresencialSessaoPublica.submit()</script>";			
		}

		$db->disconnect();		
	}
}

?>
<html>
<head>
<title>Portal de Compras - Incluir Fornecedor</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<script language="javascript" type="">
<!--
function checktodos(){
	document.CadPregaoPresencialLance.Subclasse.value = '';
	document.CadPregaoPresencialLance.submit();
}
function enviar(valor){
	document.CadPregaoPresencialLance.Botao.value = valor;
	document.CadPregaoPresencialLance.submit();
}
function validapesquisa(){
	if( ( document.CadPregaoPresencialLance.MaterialDescricaoDireta.value != '' ) || ( document.CadPregaoPresencialLance.SubclasseDescricaoDireta.value != '') ) {
		if(document.CadPregaoPresencialLance.Grupo){
			document.CadPregaoPresencialLance.Grupo.value = '';
		}
		if(document.CadPregaoPresencialLance.Classe){
			document.CadPregaoPresencialLance.Classe.value = '';
		}
		document.CadPregaoPresencialLance.Botao.value = 'Validar';
	}
	if(document.CadPregaoPresencialLance.Subclasse){
		if(document.CadPregaoPresencialLance.SubclasseDescricaoFamilia.value != "") {
			document.CadPregaoPresencialLance.Subclasse.value = '';
		}
	}
	document.CadPregaoPresencialLance.submit();
}
function AbreJanela(url,largura,altura) {
	window.open(url,'paginadetalhe','status=no,scrollbars=yes,left=45,top=150,width='+largura+',height='+altura);
}
function voltar(){
	self.close();
}
function remeter(){
	document.CadPregaoPresencialLance.submit();
}

//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">

</head>
<body background="../midia/bg.jpg" marginwidth="0" marginheight="0">
<form action="CadPregaoPresencialLance.php" method="post" name="CadPregaoPresencialLance">
<table cellpadding="3" border="0" summary="" width="100%">
	<!-- Erro -->
	<tr>
		<td>
			<?php if( $_SESSION['Mens'] != 0 ){ ExibeMens($_SESSION['Mensagem'],$_SESSION['Tipo'],$_SESSION['Virgula']); }
			
			$_SESSION['Mens'] = null;
			$_SESSION['Tipo'] = null;
			$_SESSION['Mensagem'] = null	
			
			?>
		</td>
	</tr>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td class="textonormal">
			<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF" >
        <tr>
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	        	SALA DE DISPUTA - PREGÃO PRESENCIAL
				
				
				
							<?
							
								if($_SESSION['CodLoteSelecionado'] <> null)
								{
									$Processo 				= $_SESSION['Processo'];
									$ProcessoAno 			= $_SESSION['ProcessoAno'];
									$ComissaoCodigo 		= $_SESSION['ComissaoCodigo'];
									$OrgaoLicitanteCodigo 	= $_SESSION['OrgaoLicitanteCodigo'];								
									$NumeroLoteSelecionado 	= $_SESSION['NumeroLoteSelecionado'];
									
									$db     = Conexao();
										
//Fornecedores - Início
									
									if(isset($_SESSION['CodLoteSelecionado']))
									{
										$PregaoCod 			= $_SESSION['PregaoCod'];
										$CodLoteSelecionado = $_SESSION['CodLoteSelecionado'];
										
								//Verificando se existe licitações ligadas a o processo , para ver qual programa devo chamar
										$sqlMinMax = "SELECT		MIN(pi.vpregpvali), MAX(pi.vpregpvali)
															FROM 		sfpc.tbpregaopresencialfornecedor fn,
																		sfpc.tbpregaopresencialclassificacao cl,
																		sfpc.tbpregaopresencialsituacaofornecedor sf,
																		sfpc.tbpregaopresenciallote lt,
																		sfpc.tbpregaopresencialprecoinicial pi
															WHERE		lt.cpregtsequ  = $CodLoteSelecionado
																AND 	sf.cpresfsequ  = 1
																AND 	fn.cpregfsequ  = cl.cpregfsequ
																AND		lt.cpregtsequ  = cl.cpregtsequ
																AND 	sf.cpresfsequ  = cl.cpresfsequ
																AND 	cl.cpregfsequ  = pi.cpregfsequ
																AND		cl.cpregtsequ  = pi.cpregtsequ
																AND		pi.vpregpvali > 0"; 
																
										$resultMinMax = $db->query($sqlMinMax);	
										$LinhaMinMax = $resultMinMax->fetchRow();
										
										if($_SESSION['PregaoTipo'] == 'N')
										{
											$tipoOrdenacao = "DESC";
										}
										else
										{
											$tipoOrdenacao = "ASC";
										}										
										
										//Verificando se existe licitações ligadas a o processo , para ver qual programa devo chamar 
										$sqlFornecedores = "SELECT		fn.apregfccgc, fn.apregfccpf, fn.npregfrazs, fn.npregfnomr, fn.apregfnurg, 
																		fn.epregfsitu, fn.cpregfsequ, npregforgu, sf.epresfnome, sf.cpresfsequ, lt.cpregtsequ,
																		pi.vpregpvali, pi.cpregpsequ, fn.npregfnomr, fn.fpregfmepp, pi.fpregpalan, fn.fpregfmepp
															FROM 		sfpc.tbpregaopresencialfornecedor fn,
																		sfpc.tbpregaopresencialclassificacao cl,
																		sfpc.tbpregaopresencialsituacaofornecedor sf,
																		sfpc.tbpregaopresenciallote lt,
																		sfpc.tbpregaopresencialprecoinicial pi
															WHERE		lt.cpregtsequ  = $CodLoteSelecionado
																AND 	fn.cpregfsequ  = cl.cpregfsequ
																AND		lt.cpregtsequ  = cl.cpregtsequ
																AND 	sf.cpresfsequ  = cl.cpresfsequ
																AND 	cl.cpregfsequ  = pi.cpregfsequ
																AND		cl.cpregtsequ  = pi.cpregtsequ
																AND		pi.fpregpalan  = 1
															ORDER BY	pi.vpregpvali $tipoOrdenacao, pi.cpregpoemp ASC, fn.npregfrazs ASC"; 
											
											
										$resultFornecedores = $db->query($sqlFornecedores);

										if( PEAR::isError($resultFornecedores) ){
											ExibeErroBD("$ErroPrograma\nLinhaPrecoInicial: ".__LINE__."\nSql: $sqlSolicitacoes");
										}
										
										$ValorReferencia = 0;
										
										$LinhaPrecoInicial = $resultFornecedores->fetchRow();
										
										$QuantidadeFornecedores = 0;
										
										$QuantidadeFornecedores = $resultFornecedores->numRows();	
										
										if($_SESSION['PregaoTipo'] == 'N')
										{
											$ValorReferencia = $LinhaMinMax[0]; 
										}
										else
										{
											$ValorReferencia = $LinhaMinMax[1];
										}
										
								//Pegar a última rodada de lances - Se não houver o valor passado será zero
								
										$sqlUltimaRodadaLances = "SELECT			MAX(la.cpreglnumr), MAX(la.fpreglefic)
																	FROM 			sfpc.tbpregaopresenciallance la,
																					sfpc.tbpregaopresenciallote lt,
																					sfpc.tbpregaopresencialprecoinicial pi,
																					sfpc.tbpregaopresencialfornecedor fn
																	WHERE			lt.cpregtsequ  = $CodLoteSelecionado
																		AND			lt.cpregtsequ  = la.cpregtsequ
																		AND			lt.cpregtsequ  = pi.cpregtsequ
																		AND			la.cpregpsequ  = pi.cpregpsequ
																		AND 		pi.cpregfsequ  = fn.cpregfsequ
																		AND			la.fpreglrpfn  = 0
																		AND			pi.fpregpalan  = 1"; 
																
										$resultUltimaRodadaLances 	= $db->query($sqlUltimaRodadaLances);
										
										if( PEAR::isError($resultUltimaRodadaLances) ){
											ExibeErroBD("$ErroPrograma\nLinhaPrecoInicial: ".__LINE__."\nSql: $sqlSolicitacoes");
										}										
										
										$LinhaUltimaRodadaLances 	= $resultUltimaRodadaLances->fetchRow();								
										$UltimaRodadaLances 		= $LinhaUltimaRodadaLances[0];
										$EmpateFicto 				= $LinhaUltimaRodadaLances[1];
										
										if($UltimaRodadaLances == '' or $UltimaRodadaLances == null)
										{
											$UltimaRodadaLances = 0;
										}
										else if ($UltimaRodadaLances > 0)
										{
											$sqlValUltimaRodadaLances = "SELECT				la.vpreglvall, la.fpreglurod, la.fpregllven, la.fpreglefic
																			FROM 			sfpc.tbpregaopresenciallance la, 
																							sfpc.tbpregaopresenciallote lt,
																							sfpc.tbpregaopresencialprecoinicial pi,
																							sfpc.tbpregaopresencialfornecedor fn
																			WHERE			lt.cpregtsequ  = $CodLoteSelecionado
																				AND			lt.cpregtsequ  = la.cpregtsequ
																				AND			lt.cpregtsequ  = pi.cpregtsequ
																				AND			la.cpregpsequ  = pi.cpregpsequ
																				AND 		pi.cpregfsequ  = fn.cpregfsequ
																				AND			pi.fpregpalan  = 1
																				AND			la.fpreglrpfn  = 0
																				AND			la.cpreglnumr  = $UltimaRodadaLances
																			ORDER BY		pi.vpregpvali $tipoOrdenacao, pi.cpregpoemp ASC, fn.npregfrazs ASC"; 
																	
											$resultValUltimaRodadaLances 	= $db->query($sqlValUltimaRodadaLances);	
											
											if( PEAR::isError($resultValUltimaRodadaLances) ){
												ExibeErroBD("$ErroPrograma\nLinhaPrecoInicial: ".__LINE__."\nSql: $sqlSolicitacoes");
											}												
											
											$LinhaValUltimaRodadaLances 	= $resultValUltimaRodadaLances->fetchRow();	
											$PregaoFinalizado = 0;										
										}
										
										$TotalColunasFixas = 6;
										$TotalColunasDinamicas = 0;											
									}
//Fornecedores - Fim									
								}								
							?>				
          </td>
        </tr>
        <tr>
          <td class="textonormal">
             <p align="justify">
				Para incluir lances deve-se informar o valor correspondente ao lance do Fornecedor, caso queira encerrar a sequência de Lances de um Fornecedor, deve-se deixar o valor
				"0.00" e o mesmo ficará como "S/L" (Sem Lance). Para avançar para a próxima rodada deve-se clicar no botão "Próxima Rodada". Para redigitar um valor incorreto numa rodada 
				já encerrada, deve-se clicar sobre o título da rodada (Rodada 01, Rodada 02...), e a mesma, ficará editável novamente. Para encerrar as Rodadas de Lances deve-se clicar 
				no botão "Finalizar Lances"
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table border="0" summary="" width="<?=(900 + ($UltimaRodadaLances * 60))?>">
              <tr>
                <td class="textonormal" bgcolor="#FFFFFF">
					<table border="0" width="100%" summary="">
					  <tr>
							<td align="left" bgcolor="#DCEDF7" class="textonormal"  style="font-weight: bold;">Nº Lote: </td>
							<td align="left" class="textonormal">
							  <label><?php echo $_SESSION['NumeroLoteSelecionado']; ?></label>
							</td>							
					  </tr>
					  
					  <tr>
							<td align="left" bgcolor="#DCEDF7" class="textonormal"  style="font-weight: bold;">Descrição Lote: </td>
							<td align="left" class="textonormal"  >
							  <label><?php echo $_SESSION['DescricaoLoteSelecionado']; ?></label>
							</td>							
					  </tr>	

					  <tr>
							<td align="left" bgcolor="#DCEDF7" class="textonormal"  style="font-weight: bold;">Tipo de Classificação: </td>
							<td align="left" class="textonormal" >
							  <label><?php echo ($_SESSION['PregaoTipo'] == 'N' ? 'MENOR PREÇO' : 'MAIOR OFERTA'); ?></label>
							</td>							
					  </tr>						  
					  
					  <tr >
						<td colspan="2">
						
										<table cellpadding="0" cellspacing="0" border="0" bordercolor="#75ADE6" width="100%" summary="">
											<tr bgcolor="#bfdaf2">
												<td >
												
												
												
													<table
														id="scc_material"
														summary=""
														bgcolor="#bfdaf2"
														border="1"
														bordercolor="#75ADE6"
														width="100%"
													>
														<tbody>
															<tr>
																<td
																	colspan="17"
																	class="titulo3 itens_material"
																	align="center"
																	bgcolor="#75ADE6"
																	valign="middle"
																>FORNECEDORES PARTICIPANTES</td>
															</tr>
															
															
															
															<!-- Headers ITENS DA SOLICITAÇÃO DE MATERIAL  -->
												<tr class="head_principal">

													<?php // <!--  Coluna 1 = ORD--> ?>
													<td
																	class="textoabason"
																	align="center"
																	bgcolor="#DCEDF7"
																	width="10%"
																><br /> ORD </td>
													
													<?php // <!--  Coluna 2 = CNPJ/CPF--> ?>
													<td
																	class="textoabason"
																	align="center"
																	bgcolor="#DCEDF7"
																	width="15%"
																><img
																	src="../midia/linha.gif"
																	alt=""
																	border="0"
																	height="1px"
																/> <br /> CNPJ/CPF </td>													
													
													<?php // <!--  Coluna 3 = RAZÃO SOCIAL/NOME--> ?>
													<td
																	class="textoabason"
																	align="center"
																	bgcolor="#DCEDF7"
																	width="20%"
																><img
																	src="../midia/linha.gif"
																	alt=""
																	border="0"
																	height="1px"
																/> <br /> RAZÃO SOCIAL/NOME </td>				
																
													<?php // <!--  Coluna 4 = TIPO -> ?>
													<td
																	class="textoabason"
																	align="center"
																	bgcolor="#DCEDF7"
																	width="10%"
																><img
																	src="../midia/linha.gif"
																	alt=""
																	border="0"
																	height="1px"
																/> <br /> TIPO </td>																	
																
													<?php // <!--  Coluna 3 = REPRESENTADO--> ?>
													<td
																	class="textoabason"
																	align="center"
																	bgcolor="#DCEDF7"
																	width="10%"
																><img
																	src="../midia/linha.gif"
																	alt=""
																	border="0"
																	height="1px"
																/> <br /> REPRESENTADO </td>																
																
													<?php // <!--  Coluna 5 = R.G.--> ?>
													<td
																	class="textoabason"
																	align="center"
																	bgcolor="#DCEDF7"
																	width="10%"
																	style="cursor: help;"
																	title = "<?php echo ($_SESSION['PregaoTipo'] == 'N' ? 'R$' : '%'); ?>"																		
																><img
																	src="../midia/linha.gif"
																	alt=""
																	border="0"
																	height="1px"
																/> <br /> <?php echo ($_SESSION['PregaoTipo'] == 'N' ? 'PREÇO INICIAL' : 'OFERTA INICIAL'); ?> </td>																
															
													<?php // <!--  Coluna 6 = SITUAÇÃO--> ?>
													<?																												
														
														if($LinhaValUltimaRodadaLances[1] == 0)
														{
															$TotalColunasDinamicas ++;
													?>													
													<td
																	class="textoabason"
																	align="center"
																	bgcolor="#DCEDF7"
																	width="15%"
																><img
																	src="../midia/linha.gif"
																	alt=""
																	border="0"
																	height="1px"
																/> <br /> <?=$UltimaRodadaLances + 1?>ª RODADA <?=(($LinhaValUltimaRodadaLances[3] == 1) ? ("<br />[EMPATE FÍCTO]") : (""))?></td>															

											
											<?
														}
													if($UltimaRodadaLances > 0)
													{
														for ($itr = $UltimaRodadaLances; $itr > 0; -- $itr) 
														{
															$TotalColunasDinamicas ++;
															
															$sqlLances 		= "SELECT 	fpreglefic, fpreglurod
																					FROM 	sfpc.tbpregaopresenciallance 
																					WHERE 	cpreglnumr = $itr 
																						AND cpregtsequ = $CodLoteSelecionado 
																					LIMIT 1";
															
															$resultLances 	= $db->query($sqlLances);
															
															
															if (PEAR::isError($resultLances)) {
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
															}else{
																$LinhaLances  = $resultLances->fetchRow();
															}															
															
											?>
															<td
																class="textoabason"
																align="center"
																bgcolor="#DCEDF7"
																width="50px"
															><img
																src="../midia/linha.gif"
																alt=""
																border="0"
																height="1px"
															/> <br />  <?=(($LinhaLances[1] == 1) ? ("ÚLTIMA RODADA") : (($LinhaLances[0] == 1) ? ("EMPATE FÍCTO") : ($itr . "ª RODADA")))?> </td>											
											<?
														}
													}	
											?>
											
											<?php
											// Membros do POST-----------------------------------
											
											$UltimoPreco = 0;
											$ContadorPrecosParticipantes = 0;
											
											for ($itr = 0; $itr < $QuantidadeFornecedores; ++ $itr) {
												
												//Início: Tipo de Empresa
												$TipoEmpresaOrigem	= (($LinhaPrecoInicial[14] == 0 or $LinhaPrecoInicial[14] == '' or $LinhaPrecoInicial[14] == null) ? 0 : $LinhaPrecoInicial[14]); 
												
												switch($TipoEmpresaOrigem)
												{
													case 0:
													$TipoEmpresa 		= 'OE';
													$DescTipoEmpresa 	= 'Outras Empresas';
													break;
													case 1:
													$TipoEmpresa 		= 'ME';
													$DescTipoEmpresa 	= 'Micro Empresa';
													break;
													case 2:
													$TipoEmpresa 		= 'EPP';
													$DescTipoEmpresa 	= 'Empresa de Pequeno Porte';
													break;
													case 3:
													$TipoEmpresa 		= 'MEI';
													$DescTipoEmpresa 	= 'Micro Empreendedor Individual';
													break;
												}	

												//Fim: Tipo de Empresa	

												if($LinhaValUltimaRodadaLances[1] == 1 and $LinhaValUltimaRodadaLances[2] == 1)
												{
													$PregaoFinalizado = 1;
												}												
												
											?>
											
											<!-- Dados MEMBRO DE COMISSÃO  -->
															<tr>
																<!--  Coluna 1 = Codido-->
																<td
																	class="textonormal"
																	align="center"
																	style="text-align: center; <?=(($LinhaValUltimaRodadaLances[1] == 1 and $LinhaValUltimaRodadaLances[2] == 1) ? "background-color: yellow;" : "")?>"
																	
																>
																<?= ($itr + 1)?>
														</td>
														
																<!--  Coluna 2  = CPF/CNPJ -->
																<td class="textonormal" align="center"
																style="<?=(($LinhaValUltimaRodadaLances[1] == 1 and $LinhaValUltimaRodadaLances[2] == 1) ? "background-color: yellow;" : "")?>">																
																	
																	<?= ($LinhaPrecoInicial[1] == "" 
																	?
																	(substr($LinhaPrecoInicial[0], 0, 2).'.'.substr($LinhaPrecoInicial[0], 2, 3).'.'.substr($LinhaPrecoInicial[0], 5, 3).'/'.substr($LinhaPrecoInicial[0], 8, 4).'-'.substr($LinhaPrecoInicial[0], 12, 2)) 
																	: 
																	(substr($LinhaPrecoInicial[1], 0, 3).'.'.substr($LinhaPrecoInicial[1], 3, 3).'.'.substr($LinhaPrecoInicial[1], 6, 3).'-'.substr($LinhaPrecoInicial[1], 9, 2)));?>																	
																	
																</td>
																
																<!--  Coluna 3  = Razão Social -->
																<td align="center" class="textonormal"
																style="<?=(($LinhaValUltimaRodadaLances[1] == 1 and $LinhaValUltimaRodadaLances[2] == 1) ? "background-color: yellow;" : "")?>">																	
																	
																	<?= $LinhaPrecoInicial[2] ?>																	
																	
																</td>
																
																<!--  Coluna 3  = Tipo de Empresa -->
																<td class="textonormal" align="center" title="<?=$DescTipoEmpresa?>" style="cursor: help; <?=(($LinhaValUltimaRodadaLances[1] == 1 and $LinhaValUltimaRodadaLances[2] == 1) ? "background-color: yellow;" : "")?>">																	
																	
																	<?= $TipoEmpresa?>																	
																	
																</td>																	
																
																<!--  Coluna 3  = REPRESENTADO -->
																<td align="center" class="textonormal" title="
																<?
																	if($LinhaPrecoInicial[13] == '')
																	{
																		echo "SEM REPRESENTANTE!";
																	}
																	else
																	{
																		echo $LinhaPrecoInicial[13];
																	}																
																?>" style="color:
																
																<?
																	
																	if($LinhaPrecoInicial[13] == '')
																	{
																		$FornecedorRepresentado = 0;
																		echo "red";
																	}
																	else
																	{
																		$FornecedorRepresentado = 1;
																		echo "blue";
																	}
																?> ; cursor: help; <?=(($LinhaValUltimaRodadaLances[1] == 1 and $LinhaValUltimaRodadaLances[2] == 1) ? "background-color: yellow;" : "")?>"
																>																	
																	
																	<?= ($LinhaPrecoInicial[13] == '' ? "NÃO" : "SIM") ?>																	
																	
																</td>																

																<!-- Início do cálculo % -->
																	<?
																	
																		if($LinhaPrecoInicial[11] > 0)
																		{
																			if($ValorReferencia > 0)
																			{
																				if($_SESSION['PregaoTipo'] == 'N')
																				{
																					$Percentual = (($LinhaPrecoInicial[11] - $ValorReferencia) / $ValorReferencia) * 100;
																					
																					$Percentual = number_format($Percentual, 3, ',', '');
																				}
																				else
																				{
																					$Percentual = (($ValorReferencia - $LinhaPrecoInicial[11]) / $ValorReferencia) * 100;
																				}
																			}																			
																		}
																	?>	
																	
																<!-- Fim do cálculo % -->
																
																<!--  Coluna 5  = Preço inicial -->
																<td class="textonormal" style="text-align: center; cursor: help;<?=($itr + 1 == $QuantidadeFornecedores ? 'font-weight: bold;' : '')?> <?=(($LinhaValUltimaRodadaLances[1] == 1 and $LinhaValUltimaRodadaLances[2] == 1) ? "background-color: yellow;" : "")?>" title="<?=$Percentual."%"?>">																																		
																	
																	
																	<?= number_format($LinhaPrecoInicial[11], 4, ',', '.')?>
																</td>	

																<!--  Coluna 6  = Lance inicial -->
																<?
																	$Valor = ($_SESSION['ValLance_'.$itr] == null ? '0,00' : $_SESSION['ValLance_'.$itr]);
																	
																	if($_SESSION['UltimaSessaoDesfeita'] == True)
																	{
																		$Valor = number_format($_SESSION['UltimoPreco_'.$itr], 4, ',', '.');
																	}
																	
																	if($LinhaValUltimaRodadaLances[1] == 0)
																	{
																?>
																<td class="textonormal" style="text-align: center; <?=(($LinhaValUltimaRodadaLances[1] == 1 and $LinhaValUltimaRodadaLances[2] == 1) ? "background-color: yellow;" : "")?>">																
																	
																	<input type="text" name="ValLance_<?= $itr?>" size="12" maxlength="12" <?=(($FornecedorRepresentado == 0 or ($UltimaRodadaLances > 0 and $LinhaValUltimaRodadaLances[0] <= 0) or ($LinhaValUltimaRodadaLances[1] == 1) or ($LinhaValUltimaRodadaLances[0] > $ValorReferencia) and ($LinhaValUltimaRodadaLances[0] < $ValorReferencia)) or ($EmpateFicto == 1 and $LinhaValUltimaRodadaLances[3] < 1)) ? 'readonly' : ''?>
																	style="<?=(($FornecedorRepresentado == 0 or ($UltimaRodadaLances > 0 and $LinhaValUltimaRodadaLances[0] <= 0) or ($LinhaValUltimaRodadaLances[1] == 1) or ($LinhaValUltimaRodadaLances[0] > $ValorReferencia) and ($LinhaValUltimaRodadaLances[0] < $ValorReferencia)) or ($EmpateFicto == 1 and $LinhaValUltimaRodadaLances[3] < 1)) ? 'background-color: #D8D8D8;' : ''?>"
																	value="<?=(($FornecedorRepresentado == 0 or ($UltimaRodadaLances > 0 and $LinhaValUltimaRodadaLances[0] <= 0) or ($LinhaValUltimaRodadaLances[1] == 1) or ($LinhaValUltimaRodadaLances[0] > $ValorReferencia) and ($LinhaValUltimaRodadaLances[0] < $ValorReferencia)) or ($EmpateFicto == 1 and $LinhaValUltimaRodadaLances[3] < 1)) ? 'SEM LANCE' : $Valor?>"
																	class="textonormal"
																	style="background-color: <?=$LinhaPrecoInicial[9] == 1 ? "white" : "#E0E0E0"?>"/> 						
																</td>
																<?
																	}
																?>
																	<input type="hidden" name="CodFornecedor_<?= $itr?>" value="<?=$LinhaPrecoInicial[6]?>">
																	<input type="hidden" name="CodPrecoInicial_<?= $itr?>" value="<?=$LinhaPrecoInicial[12]?>">
																	<input type="hidden" name="RodadaAtual" value="<?=($UltimaRodadaLances + 1)?>">
																	<input type="hidden" name="TotalParticipantes" value="<?=$QuantidadeFornecedores?>">
																	<input type="hidden" name="PrecoInicial_<?= $itr?>" value="<?=$LinhaPrecoInicial[11]?>">
																	<input type="hidden" name="PrecoRodadaAnterior_<?= $itr?>" value="<?=$LinhaValUltimaRodadaLances[0]?>">
																	<input type="hidden" name="TipoEmpresa_<?= $itr?>" value="<?=$LinhaPrecoInicial[16]?>">		
																	<input type="hidden" name="NomeFornecedor_<?= $itr?>" value="<?=$LinhaPrecoInicial[2]?>">      
																	<input type="hidden" name="EmpateFicto" value="<?=$EmpateFicto?>">



															<?
																	if($UltimaRodadaLances > 0)
																	{
																		for ($itrD = $UltimaRodadaLances; $itrD > 0; -- $itrD) 
																		{
																			
																			
																			$sqlLancesAnteriores = "SELECT 	vpreglvall, fpregllven, fpreglefic
																									FROM 	sfpc.tbpregaopresenciallance 
																									WHERE 	cpreglnumr = $itrD 
																										AND cpregtsequ = $CodLoteSelecionado 
																										AND	cpregfsequ = $LinhaPrecoInicial[6]";
																			
																			$resultLancesAnteriores = $db->query($sqlLancesAnteriores);
																			
																			
																			if (PEAR::isError($resultLancesAnteriores)) {
																				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																			}else{
																				$LinhaLancesAnteriores  = $resultLancesAnteriores->fetchRow();
																			}																		

																		
															?>
																<td class="textonormal" style="text-align: center; <?=($LinhaLancesAnteriores[1] == 1 ? 'cursor: help; font-weight: bold;' : '')?> <?=(($LinhaValUltimaRodadaLances[1] == 1 and $LinhaValUltimaRodadaLances[2] == 1) ? "background-color: yellow;" : "")?>" title="<?=($LinhaLancesAnteriores[1] == 1 ? 'Lance Vencedor da Rodada' : '')?>">																																		
																	
																	<?
																	if($LinhaLancesAnteriores[2] == 1 and $LinhaLancesAnteriores[0] > 0)
																	{
																		$sqlLanceVencedor = "SELECT 	vpreglvall, fpregllven, fpreglefic
																								FROM 	sfpc.tbpregaopresenciallance 
																								WHERE 	cpregtsequ = $CodLoteSelecionado
																									AND fpreglurod = 1
																									AND fpregllven = 1";
																		
																		$resultLanceVencedor = $db->query($sqlLanceVencedor);
																		
																		
																		if (PEAR::isError($resultLanceVencedor)) {
																			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																		}else{
																			$LinhaLanceVencedor  = $resultLanceVencedor->fetchRow();
																			$ValVencedor = $LinhaLanceVencedor[0];
																			
																			if($ValVencedor > 0)
																			{
																				$PercentualEmpateFicto 			= (($LinhaLancesAnteriores[0] > $ValVencedor ) ? $ValVencedor / $LinhaLancesAnteriores[0] : $LinhaLancesAnteriores[0] / $ValVencedor);
																				$PercentualEmpateFicto 			= ($PercentualEmpateFicto * -100) + 100;
																				$PercentualEmpateFictoFormatado = number_format($PercentualEmpateFicto, 4, ',', '.');
																			}
																			
																		}																		
																		
																	?>
																		<label title="A Empresa está com um preço <?= $PercentualEmpateFictoFormatado ?>% acima do Preço Vencedor, mas como está no grupo de EPP/ME/MEI, entrará no EMPATE FÍCTO pois está abaixo de 5%." style="color: blue;cursor: help;"><?= number_format($LinhaLancesAnteriores[0], 4, ',', '.')?></label>																	
																	<?
																	}
																	else
																	{
																	?>
																	
																		<label title="<?=(($LinhaLancesAnteriores[0] > 0) ? ("") : ("Sem Lance"))?>" style="<?=(($LinhaLancesAnteriores[0] > 0) ? ("") : ("cursor: help; color: red;"))?>"><?= (($LinhaLancesAnteriores[0] > 0) ? (number_format($LinhaLancesAnteriores[0], 4, ',', '.')) : ("S/L"))?></label>
																	
																	<?
																	}
																	
																	?>
																</td>										
															<?
																		}
																	}	
															?>																	

											<?php
												$LinhaPrecoInicial = $resultFornecedores->fetchRow();
												
												if($UltimaRodadaLances > 0 or ($_SESSION['UltimaSessaoDesfeita'] == True and $UltimaRodadaLances > 0))
												{
													$db     = Conexao();
													$LinhaValUltimaRodadaLances = $resultValUltimaRodadaLances->fetchRow();
												}
												
												for($itrB = 0; $itrB < $QuantidadeFornecedores; ++ $itrB)
												{			
													$_SESSION['ValLance_'.$itr]	= null;
												}
											}
											
											$db->disconnect();
											?>																
																


											<?php

											if ($QuantidadeFornecedores <= 0) {
												?>
											<tr>
																<td
																	class="textonormal itens_material"
																	colspan="<?=$TotalColunasFixas + $TotalColunasDinamicas?>"
																	style="color: red"
																>Nenhum Fornecedor Participante do Pregão</td>
															</tr>
															<!-- FIM Dados ITENS DA SOLICITAÇÃO DE MATERIAL  -->

											<?php

											}
											?>

											<?php

											if ($QuantidadeFornecedores > 0) {
												?>												
											
													<tr>
																<td
																	colspan="<?=(($TotalColunasFixas + $TotalColunasDinamicas) - 1)?>"
																	class="titulo3 itens_material menosum"
																	width="95%"
																>TOTAL DE FORNECEDORES: </td>
																
																<td
																	class="textonormal"
																	align="center"
																	width="5%"
																>
																	<div id="MaterialTotal" style="font-weight: bold;"><?= $QuantidadeFornecedores ?></div>
																</td>
															</tr>
															
											<?php

											}
											?>																
															
														</tbody>
													</table>						
						
						</td>				  
					  </tr>
					</table>				
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
 	        <td class="textonormal" align="left">
			<?
				if($PregaoFinalizado == 0)
				{				
			?>
          	<input type="submit" value="Próxima Rodada" class="botao" onclick="javascript:enviar('ProximaRodada');">		
			<?
				}
				if($UltimaRodadaLances > 0)
				{
			?>
			<input type="submit" value="Desfazer Última Rodada" class="botao" onclick="javascript:enviar('DesfazerUltimaRodada');">
			<?
				}
			?>
			<input type="button" value="Voltar" class="botao" onclick="javascript:voltar();">
			<input type="hidden" name="Botao" value="">			
          </td>
        </tr>
      </table>
	  
		  <?
		  if($PregaoFinalizado == 1)
		  {
		  ?>
			<tr>
				<td>
					* O Fornecedor destacado em 'AMARELO' é o Arrematante da Disputa.
				</td>
			</tr>
		  <?
		  }
		  ?>	  
		</td>
	</tr>
	<!-- Fim do Corpo -->
</table>
</form>
</body>
</html>
<script language="javascript" type="">
<!--
document.Usuario.UsuarioCodigo.focus();
//-->
</script>
