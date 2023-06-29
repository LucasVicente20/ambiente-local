<?php
# -------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadFaseLicitacaoExcluir.php
# Autor:    Rossana Lira
# Data:     02/05/03
# Objetivo: Programa de Exclusão da Fase de Licitação
# -------------------------------------------------------------------------
# Alterado: Ariston
# Data:     26/05/2011 - Salvar usuário responsável pela exclusão da fase
# -------------------------------------------------------------------------
# Alterado: Heraldo Botelho
# Data:     17/09/2012 - Adaptação para licitação c solicitação
# -------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     26/03/2018
# Objetivo: Tarefa Redmine 14899 e 95892 - Linha alterada: 119
# -------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     30/11/2018
# Objetivo: Tarefa Redmine 207414
# -------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso ('/licitacoes/CadFaseLicitacaoAlterar.php');
AddMenuAcesso ('/licitacoes/CadFaseLicitacaoSelecionar.php');
AddMenuAcesso ('/oracle/licitacoes/RotValidaBloqueio.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$Botao                     = $_POST['Botao'];
	$Critica                   = $_POST['Critica'];
	$Processo                  = $_POST['Processo'];
	$ProcessoAno               = $_POST['ProcessoAno'];
	$ComissaoCodigo            = $_POST['ComissaoCodigo'];
	$OrgaoLicitanteCodigo      = $_POST['OrgaoLicitanteCodigo'];
	$ModalidadeCodigo          = $_POST['ModalidadeCodigo'];
	$RegistroPreco             = $_POST['RegistroPreco'];
	$FaseCodigo                = $_POST['FaseCodigo'];
	$FaseLicitacaoDetalhe      = $_POST['FaseLicitacaoDetalhe'];
	$FlagValorHomologado       = $_POST['FlagValorHomologado'];
	$BloqueiosDot              = $_POST['BloqueiosDot'];
	$ComissaoDescricao         = $_POST['ComissaoDescricao'];
	$FaseDescricao             = $_POST['FaseDescricao'];
	$FaseLicitacaoUltAlteracao = $_POST['FaseLicitacaoUltAlteracao'];
	$FaseLicitacaoDetalhe      = $_POST['FaseLicitacaoDetalhe'];
	$ValorHomologado           = $_POST['ValorHomologado'];
} else {
	$Processo                 = $_GET['Processo'];
	$ProcessoAno              = $_GET['ProcessoAno'];
	$ComissaoCodigo           = $_GET['ComissaoCodigo'];
	$OrgaoLicitanteCodigo     = $_GET['OrgaoLicitanteCodigo'];
	$ModalidadeCodigo         = $_GET['ModalidadeCodigo'];
	$RegistroPreco            = $_GET['RegistroPreco'];
	$FaseCodigo               = $_GET['FaseCodigo'];
	$AlteraValorHomologadoBlo = $_GET['AlteraValorHomologadoBlo'];
}

# Verifica a existe solicitacao  
$licitacao_possui_solicitacao = false;

$db = Conexao();

$sql  = "SELECT COUNT(*) FROM SFPC.tbsolicitacaolicitacaoportal ";
$sql .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
$sql .= "   AND CGREMPCODI = ".$_SESSION['_cgrempcodi_']." AND CCOMLICODI = $ComissaoCodigo ";
$sql .= "   AND CORGLICODI = $OrgaoLicitanteCodigo ";

$result = $db->query($sql);

if (PEAR::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
} else {
	$Linha = $result->fetchRow();
}

if ($Linha[0] > 0) {
	$licitacao_possui_solicitacao = true;
}

if ($licitacao_possui_solicitacao) {
	$existeSolicitacao = "SIM";
} else {
	$existeSolicitacao = "SIM";
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Critica dos Campos #
if ($Botao == "Voltar") {
	$Url = "CadFaseLicitacaoAlterar.php?Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&ModalidadeCodigo=$ModalidadeCodigo&RegistroPreco=$RegistroPreco&FaseCodigo=$FaseCodigo";
	
	if (!in_array($Url,$_SESSION['GetUrl'])) {
		$_SESSION['GetUrl'][] = $Url;
	}
	header("location: ".$Url);
	exit();
} else {
	$Mens = 0;
	
	if ($Critica == 1) {
		$Mensagem = "Informe: ";
		
		# Verifica se a fase de licitação está relacionada com alguma ata da fase de licitação #
		$db = Conexao();
		
		$sql  = "SELECT COUNT(*) FROM SFPC.TBATASFASE ";
		$sql .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
		$sql .= "   AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = ".$_SESSION['_cgrempcodi_']." ";
		$sql .= "   AND CORGLICODI=$OrgaoLicitanteCodigo AND CFASESCODI = $FaseCodigo";
		
		$result = $db->query($sql);
		
		if (PEAR::isError($result)) {
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}
		
		while ($Linha = $result->fetchRow()) {
			$QtdAta = $Linha[0];
		}
		
		if ($QtdAta > 0) {
		    $Mens = 1;
		    $Mensagem = "Exclusão Cancelada!<br>Fase da Licitação está Relacionada com ($QtdAta) Ata(s) da Fase(s)";
		}
		
		if ($Mens == 0) {
			if ($FlagValorHomologado == "N" and $FaseCodigo == 13) {
				$Mens      = 1;
				$Tipo      = 2;
				$Virgula   = 2;
				$Mensagem  = "A Fase de Licitação não pode ser Excluída, pois as informações do(s) bloqueio(s) já foram ajustadas no SOFIN";
			} else {
				# Exclui FaseLicitacao #
				$db->query("BEGIN TRANSACTION");
				
				$sql  = "DELETE FROM SFPC.TBFASELICITACAO ";
				$sql .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
				$sql .= "   AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = ".$_SESSION['_cgrempcodi_']."";
				$sql .= "   AND CORGLICODI = $OrgaoLicitanteCodigo AND CFASESCODI = $FaseCodigo";
								
				$result = $db->query($sql);
				
				if (PEAR::isError($result)) {
  			    	$Rollback = 1;								  
					$db->query("ROLLBACK");
					EmailErroDB("Erro em SQL", "Erro em SQL", $result);
				} else {
					# Adiciona Usuário no último registro da tabela de log #
					$usuario = $_SESSION['_cusupocodi_'];

					$sql = "UPDATE	SFPC.TBLICITACAO_LOG
							SET		CUSUPOCODI = $usuario
							WHERE	CUSUPOCODI IS NULL
									AND	CLPLOGCODI = (SELECT LAST_VALUE FROM SFPC.TBLICITACAO_LOG_CLPLOGCODI_SEQU) ";
					
					$result = $db->query($sql);
										
					if (PEAR::isError($result)) {
  			            $Rollback = 1;
						$db->query("ROLLBACK");
						EmailErroSQL("Erro no SQL", __FILE__, __LINE__, "Erro no SQL", $sql, $result);
					}
										
					if ($FaseCodigo == 13) {
 						$sql  = "UPDATE SFPC.TBLICITACAOPORTAL ";
						$sql .= "   SET VLICPOVALH = NULL, VLICPOTGES = NULL, ";
						$sql .= "       CUSUPOCODI = ".$_SESSION['_cusupocodi_'].", TLICPOULAT = '".date("Y-m-d H:i:s")."' ";
						$sql .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
						$sql .= "   AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = ".$_SESSION['_cgrempcodi_'];
						$sql .= "   AND CORGLICODI = $OrgaoLicitanteCodigo ";

						$result  = $db->query($sql);
						
						if (PEAR::isError($result)) {
  			                $Rollback = 1;
							$db->query("ROLLBACK");
		 					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		 				}

		 				$sql  = " delete    ";
		 				$sql .= " from ";
		 				$sql .= " sfpc.tbtabelareferencialprecos  ";
		 				$sql .= " where ";
		 				$sql .= " clicpoproc=$Processo ";
		 				$sql .= " and  alicpoanop=$ProcessoAno ";
		 				$sql .= " and  cgrempcodi=".$_SESSION['_cgrempcodi_'] ;
		 				$sql .= " and  ccomlicodi=$ComissaoCodigo ";
		 				$sql .= " and  corglicodi=$OrgaoLicitanteCodigo ";
						 
						$result  = $db->query($sql);
						 
						if (PEAR::isError($result)) {
		 					$Rollback = 1;
		 					$db->query("ROLLBACK");
		 					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		 				}

		 				$sql  = " delete from sfpc.tbtabelareferencialprecos ";
						$sql .= " where ";
						$sql .= " csolcosequ in ( ";
						$sql .= " select csolcosequ ";
						$sql .= "	where ";
		 				$sql .= " clicpoproc=$Processo ";
		 				$sql .= " and  alicpoanop=$ProcessoAno ";
		 				$sql .= " and  cgrempcodi=".$_SESSION['_cgrempcodi_'] ;
		 				$sql .= " and  ccomlicodi=$ComissaoCodigo ";
		 				$sql .= " and  corglicodi=$OrgaoLicitanteCodigo ";
						$sql .= " ) ";
		 										
		 				if (PEAR::isError($result)) {
		 					$Rollback = 1;
		 					$db->query("ROLLBACK");
		 					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		 				}
		 											
                        $sql =  " select ";
                        $sql .= " pre.apresoanoe , pre.cpresosequ  ";
                        $sql .= " from ";
                        $sql .= " sfpc.tbsolicitacaolicitacaoportal sol, sfpc.tbpresolicitacaoempenho pre ";
                        $sql .= " where ";
		                $sql .= " sol.clicpoproc =".$Processo ;
  		                $sql .= " and sol.alicpoanop =".$ProcessoAno;
   		                $sql .= " and sol.cgrempcodi =".$_SESSION['_cgrempcodi_'];
   		                $sql .= " and sol.ccomlicodi =".$ComissaoCodigo;
   		                $sql .= " and sol.corglicodi =".$OrgaoLicitanteCodigo;
                        $sql .= " and sol.csolcosequ = pre.csolcosequ " ;
						
						$result  = $db->query($sql);

						if (PEAR::isError($result)) {
  			                $Rollback = 1;
							$db->query("ROLLBACK");
		 				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		 				}
						
						while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
   		                    $sql     = "   DELETE FROM  SFPC.tbitempresolicitacaoempenho ";
							$sql    .= "   WHERE ";
							$sql    .= "   apresoanoe = ".$row->apresoanoe;
							$sql    .= "   and cpresosequ = ".$row->cpresosequ;
							
							$result2  = $db->query($sql);
	 
   		                   	if (PEAR::isError($result2)) {
  			                    $Rollback = 1;
							    $db->query("ROLLBACK");
		 					    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		 					}

   		                    $sql     = "   DELETE FROM  SFPC.tbpresolicitacaoempenho ";
							$sql    .= "   WHERE ";
							$sql    .= "   apresoanoe = ".$row->apresoanoe;
							$sql    .= "   and cpresosequ = ".$row->cpresosequ;
							
							$result3  = $db->query($sql);
								  
							if (PEAR::isError($result3)) {
  			                    $Rollback = 1;
							    $db->query("ROLLBACK");
		 					    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		 					}
   		                }

		            	$sql    = " update sfpc.tbsolicitacaocompra ";
		            	$sql   .= " set  csitsocodi=9,";
		            	$sql   .= "      cusupocod1=".$_SESSION['_cusupocodi_'].",";
	                	$sql   .= "      tsolcoulat=now() ";
		            	$sql   .= " where ";
		            	$sql   .= " csolcosequ in ";
		            	$sql   .= "( select csolcosequ from sfpc.tbsolicitacaolicitacaoportal where  ";
						$sql   .= "   CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
						$sql   .= "   AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = ".$_SESSION['_cgrempcodi_'];
						$sql   .= "   AND CORGLICODI = $OrgaoLicitanteCodigo ";
		            	$sql   .=  " ) ";
						
						$result  = $db->query($sql);
                     
		                if (PEAR::isError($result)) {
			                $Rollback = 1;
			                $db->query("ROLLBACK");
		                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		                }

                        $sql  = " insert into  sfpc.tbhistsituacaosolicitacao ";
                        $sql .= " ( csolcosequ, thsitsdata, csitsocodi,xhsitsobse,cusupocodi ) ";
                        $sql .= " select  csolcosequ, now(),9 ,'alteracao de situacao',".$_SESSION['_cusupocodi_']." from sfpc.tbsolicitacaolicitacaoportal ";
   		                $sql .= " where ";
		                $sql .= " clicpoproc =".$Processo ;
  		                $sql .= " and alicpoanop =".$ProcessoAno;
   		                $sql .= " and cgrempcodi =".$_SESSION['_cgrempcodi_'];
   		                $sql .= " and ccomlicodi =".$ComissaoCodigo;
   		                $sql .= " and corglicodi =".$OrgaoLicitanteCodigo;
						
						$result  = $db->query($sql);
						
						if (PEAR::isError($result)) {
			                $Rollback = 1;
			                $db->query("ROLLBACK");
		                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}
					} elseif ($FaseCodigo == 2) {
						$sql  = "UPDATE SFPC.TBLICITACAOPORTAL ";
						$sql .= "SET	FLICPOSTAT = 'I', CUSUPOCODI = ".$_SESSION['_cusupocodi_'].", TLICPOULAT = '".date("Y-m-d H:i:s")."' ";
						$sql .= "WHERE	CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
						$sql .= "   	AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = ".$_SESSION['_cgrempcodi_'];
						$sql .= "   	AND CORGLICODI = $OrgaoLicitanteCodigo ";

						$result  = $db->query($sql);
						
						if (PEAR::isError($result)) {
  			                $Rollback = 1;
							$db->query("ROLLBACK");
		 					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		 				}
					}

					$db->query("COMMIT");
					$db->query("END TRANSACTION");
					$db->disconnect();

					# Envia mensagem para página selecionar #
					$Mensagem = "Fase da Licitação Excluída com Sucesso";
					$Url = "CadFaseLicitacaoSelecionar.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
					
					if (!in_array($Url,$_SESSION['GetUrl'])) {
						$_SESSION['GetUrl'][] = $Url;
					}
					header("location: ".$Url);
					exit();
				}
				$db->disconnect();
			}
		} else {
		    $db->disconnect();
			
			$Url = "CadFaseLicitacaoSelecionar.php?Mensagem=".urlencode($Mensagem)."&Mens=$Mens&Tipo=2";
			
			if (!in_array($Url,$_SESSION['GetUrl'])) {
				$_SESSION['GetUrl'][] = $Url;
			}
		    header("location: ".$Url);
		    exit();
		}
	}
}

if ($Critica == 0) {
	# Busca descrição da comissão #
	$db = Conexao();
	
	$sql = "SELECT A.ECOMLIDESC FROM SFPC.TBCOMISSAOLICITACAO A WHERE A.CCOMLICODI = $ComissaoCodigo";
	
	$result = $db->query($sql);
	
	if (PEAR::isError($result)) {
	    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	} else {
		$Linha = $result->fetchRow();
		$ComissaoDescricao = $Linha[0];
	}

	# Busca descrição da Fase #
	$sql = "SELECT A.EFASESDESC FROM SFPC.TBFASES A WHERE A.CFASESCODI = $FaseCodigo";
	
	$result = $db->query($sql);
	
	if (PEAR::isError($result)) {
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	} else {
		$Linha = $result->fetchRow();
		$FaseDescricao = $Linha[0];
	}

	# Busca o detalhamento da Fase da Licitação #
	$sql    = "SELECT EFASELDETA, TFASELDATA, TFASELULAT FROM SFPC.TBFASELICITACAO ";
	$sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno";
	$sql   .= "   AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = ".$_SESSION['_cgrempcodi_']."";
	$sql   .= "   AND CORGLICODI = $OrgaoLicitanteCodigo AND CFASESCODI = $FaseCodigo";
	
	$result = $db->query($sql);
	
	if (PEAR::isError($result)) {
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	} else {
		$Linha = $result->fetchRow();
		
		if ($Linha[0] != "") {
			$FaseLicitacaoDetalhe = $Linha[0];
		} else {
			$FaseLicitacaoDetalhe = "NÃO INFORMADO";
		}
		
		$DataFase                  = DataBarra($Linha[1]);
		$FaseLicitacaoUltAlteracao = DataBarra($Linha[2])." ".substr($Linha[2],11,8);
	}
	
	# Busca o Valor Homologado do Processo #
	$sql    = "SELECT VLICPOVALH, VLICPOTGES FROM SFPC.TBLICITACAOPORTAL ";
	$sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno";
	$sql   .= "   AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = ".$_SESSION['_cgrempcodi_']."";
	$sql   .= "   AND CORGLICODI = $OrgaoLicitanteCodigo ";
	
	$result = $db->query($sql);
	
	if (PEAR::isError($result)) {
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	} else {
		$Linha = $result->fetchRow();
		
		if ($Linha[0] == "") {
			$ValorHomologado = "0,00";
		} else {
			$ValorHomologado = converte_valor($Linha[0]);
		}
		
		if ($Linha[1] == "") {
			$TotalGeralEstimado = "0,00";
		} else {
			$TotalGeralEstimado = converte_valor($Linha[1]);
		}
	}

	# Busca os Dados dos do Bloqueio #
	$sql    = "SELECT TUNIDOEXER, CUNIDOORGA, CUNIDOCODI, ALICBLSEQU ";
	$sql   .= "  FROM SFPC.TBLICITACAOBLOQUEIOORCAMENT";
	$sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
	$sql   .= "   AND CCOMLICODI = $ComissaoCodigo ";
	$sql   .= "   AND CGREMPCODI = ".$_SESSION['_cgrempcodi_']."";
	$sql   .= " ORDER BY ALICBLSEQU";
	
	$result = $db->query($sql);
	
	if (PEAR::isError($result)) {
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	} else {
    	$Rows = $result->numRows();
		
		for ($i=0; $i < $Rows;$i++) {
			$Linha             = $result->fetchRow();
			$ExercicioBloq[$i] = $Linha[0];
			$ExercicioDot      = $ExercicioDot."_".$ExercicioBloq[$i];
			$Orgao[$i]         = $Linha[1];
			$OrgaoDot          = $OrgaoDot."_".$Orgao[$i];
			$Unidade[$i]       = $Linha[2];
			$UnidadeDot        = $UnidadeDot."_".$Unidade[$i];
			$Bloqueios[$i]     = $Linha[3];
			$BloqueiosDot      = $BloqueiosDot."_".$Bloqueios[$i];
		}
	}
	
	$db->disconnect();
	
	if ($BloqueiosDot != "") {
		if ($AlteraValorHomologadoBlo == "") {
			# Redireciona para a RotValidaBloqueio para Pegar o número de Bloqueio #
			$Url = "licitacoes/RotValidaBloqueio.php?NomePrograma=".urlencode("CadFaseLicitacaoExcluir.php")."&BloqueiosDot=$BloqueiosDot&ExercicioDot=$ExercicioDot&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo&OrgaoDot=$OrgaoDot&UnidadeDot=$UnidadeDot&FaseCodigo=$FaseCodigo";
			
			if (!in_array($Url,$_SESSION['GetUrl'])) {
				$_SESSION['GetUrl'][] = $Url;
			}
			
			Redireciona($Url);
		} else {
			$AlteraValorHomologado = explode("_",$AlteraValorHomologadoBlo);
			
			for ($j=1; $j < count($AlteraValorHomologado);$j++) {
				if ($AlteraValorHomologado[$i] == "N") {
					$FlagValorHomologado = "N";
				}
			}
		}
	}
}
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript">
	<!--
	function enviar(valor){
		document.FaseLicitacao.Botao.value=valor;
		document.FaseLicitacao.submit();
	}
	<?php MenuAcesso(); ?>
	//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form action="CadFaseLicitacaoExcluir.php" method="post" name="FaseLicitacao">
		<br><br><br><br><br>
		<table cellpadding="3" border="0" summary="">
			<!-- Caminho -->
			<tr>
				<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
				<td align="left" class="textonormal" colspan="2">
					<font class="titulo2">|</font>
					<a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Fase Licitação > Manter
				</td>
			</tr>
			<!-- Fim do Caminho-->
			<!-- Erro -->
			<?php if ($Mens == 1) { ?>
  				<tr>
  					<td width="150"></td>
					<td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,$Virgula); ?></td>
				</tr>
			<?php } ?>
			<!-- Fim do Erro -->
			<!-- Corpo -->
			<tr>
				<td width="150"></td>
				<td class="textonormal">
					<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" bgcolor="#FFFFFF" class="textonormal" summary="">
        				<tr>
          					<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	           					EXCLUIR - FASE DE LICITAÇÃO
          					</td>
        				</tr>
        				<tr>
          					<td class="textonormal" >
             					<p align="justify">
               						Para confirmar a exclusão da Fase de Licitação clique no botão "Excluir", caso contrário clique no botão "Voltar".
             					</p>
          					</td>
        				</tr>
        				<tr>
          					<td>
            					<table>
              						<tr>
	              						<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Comissão </td>
	              						<td class="textonormal"><?php echo $ComissaoDescricao; ?></td>
	            					</tr>
 									<tr>
	              						<td class="textonormal" bgcolor="#DCEDF7" height="20">Processo </td>
	              						<td class="textonormal"><?php echo $Processo; ?></td>
	            					</tr>
	            					<tr>
	              						<td class="textonormal" bgcolor="#DCEDF7" height="20">Ano </td>
	              						<td class="textonormal"><?php echo $ProcessoAno; ?></td>
	            					</tr>
									<tr>
	              						<td class="textonormal" bgcolor="#DCEDF7" height="20">Fase </td>
	              						<td class="textonormal">
	              							<?php echo $FaseDescricao; ?>
											<input type="hidden" name="Processo" value="<?php echo $Processo?>">
											<input type="hidden" name="ProcessoAno" value="<?php echo $ProcessoAno?>">
											<input type="hidden" name="ComissaoCodigo" value="<?php echo $ComissaoCodigo?>">
											<input type="hidden" name="OrgaoLicitanteCodigo" value="<?php echo $OrgaoLicitanteCodigo?>">
											<input type="hidden" name="FaseCodigo" value="<?php echo $FaseCodigo?>">
											<input type="hidden" name="Critica" value="1">
										</td>
	            					</tr>
									<tr>
	              						<td class="textonormal" bgcolor="#DCEDF7" height="20">Data da Fase </td>
	              						<td class="textonormal"><?php echo $DataFase; ?></td>
	            					</tr>
									<tr>
	              						<td class="textonormal" bgcolor="#DCEDF7" height="20">Última Alteração </td>
	              						<td class="textonormal"><?php echo $FaseLicitacaoUltAlteracao; ?></td>
	            					</tr>
        	   						<tr>
	              						<td class="textonormal" bgcolor="#DCEDF7" height="20">Detalhe </td>
	              						<td class="textonormal"><?php echo $FaseLicitacaoDetalhe; ?></td>
	            					</tr>
									<?php
									if ($FaseCodigo == 13) { ?>
	           							<tr>
	              							<td class="textonormal" bgcolor="#DCEDF7">Total Geral Estimado<br>(Itens que Lograram Êxito)</td>
	              							<td class="textonormal"><?php echo $TotalGeralEstimado; ?></td>
	            						</tr>
	           							<tr>
	              							<td class="textonormal" bgcolor="#DCEDF7" height="20">Valor Homologado<br>(Itens que Lograram Êxito)</td>
	              							<td class="textonormal"><?php echo $ValorHomologado; ?></td>
	            						</tr>
									<?php
									}
									?>
				   				</table>
        					</td>
      					</tr>
        				<tr>
 	        				<td class="textonormal" align="right">
								<input type="hidden" name="ModalidadeCodigo" value="<?php echo $ModalidadeCodigo; ?>">
								<input type="hidden" name="RegistroPreco" value="<?php echo $RegistroPreco; ?>">
								<input type="hidden" name="ComissaoDescricao" value="<?php echo $ComissaoDescricao; ?>">
								<input type="hidden" name="FaseDescricao" value="<?php echo $FaseDescricao; ?>">
								<input type="hidden" name="FaseLicitacaoDetalhe" value="<?php echo $FaseLicitacaoDetalhe; ?>">
								<input type="hidden" name="DataFase" value="<?php echo $DataFase; ?>">
								<input type="hidden" name="FaseLicitacaoUltAlteracao" value="<?php echo $FaseLicitacaoUltAlteracao; ?>">
								<input type="hidden" name="ValorHomologado" value="<?php echo $ValorHomologado; ?>">
								<input type="hidden" name="FlagValorHomologado" value="<?php echo $FlagValorHomologado; ?>">
								<input type="hidden" name="BloqueiosDot" value="<?php echo $BloqueiosDot; ?>">
								<input type="submit" value="Excluir" class="botao" onclick="javascript:enviar('Excluir')">
								<input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar')">
								<input type="hidden" name="Botao" value="">
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