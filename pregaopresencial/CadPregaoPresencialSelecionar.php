<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadPregaoPresencialSelecionar.php
# Autor:    Hélio Miranda
# Data:     04/06/2016
# Objetivo: Programa de Seleção de Proceso Licitatório (Pregão Presencial)
# OBS.:     Tabulação 2 espaços
#			Irão aparecer as licitações de acordo com a(s) comissão(ões)
#           do usuário que está logado
#-------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		17/04/2018
# Objetivo: Tarefa Redmine 192108
#-------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		23/05/2018
# Objetivo: Tarefa Redmine 194641
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/pregaopresencial/CadPregaoPresencialSessaoPublica.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$LicitacaoProcessoAnoComissaoOrgao = $_POST['LicitacaoProcessoAnoComissaoOrgao'];
	$Critica                           = $_POST['Critica'];
} else {
	$Critica                           = $_GET['Critica'];
	$Mensagem                          = $_GET['Mensagem'];
	$Mens                              = $_GET['Mens'];
	$Tipo                              = $_GET['Tipo'];
}

$_SESSION['CodLoteSelecionado'] = null;

# Critica dos Campos #
$Mensagem = urldecode($Mensagem);

if ($Critica == 1) {
	$Mens = 0;
	$Mensagem = "Informe: ";

	if ($LicitacaoProcessoAnoComissaoOrgao == "") {
	    $Mens = 1;
		$Tipo = 2;
		$Troca = 1;
        $Mensagem .= "<a href=\"javascript: document.PregaoPresencial.PregaoPresencialCodigo.focus();\" class=\"titulo2\">Selecione um Processo (Processo/Ano)</a>";
    } else {
		$db = Conexao();

		$sqlUC = "SELECT uc.ccomlicodi FROM sfpc.tbusuariocomis uc WHERE uc.cusupocodi = ".$_SESSION['_cusupocodi_']." AND uc.cgrempcodi = ".$_SESSION['_cgrempcodi_'];

		$resUC = $db->query($sqlUC);

		if (PEAR::isError($resUC)) {
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		} else {
			$LinhaUC  	= $resUC->fetchRow();
			$UC			= $LinhaUC[0];
		}			

		if ($UC > 0) {
			$NProcessoAnoComissao = explode("_",$LicitacaoProcessoAnoComissaoOrgao);
			$Processo             = $NProcessoAnoComissao[0];
			$ProcessoAno          = $NProcessoAnoComissao[1];
			$ComissaoCodigo       = $NProcessoAnoComissao[2];
			$OrgaoLicitanteCodigo = $NProcessoAnoComissao[3];

			//Verificando se existe licitações ligadas a o processo , para ver qual programa devo chamar
			$sqlSolicitacoes = " SELECT  csolcosequ ,clicpoproc , alicpoanop , cgrempcodi ,ccomlicodi ,corglicodi
										FROM SFPC.TBSOLICITACAOLICITACAOPORTAL SOL 
										WHERE 	SOL.CLICPOPROC = $Processo 
												AND SOL.ALICPOANOP = $ProcessoAno
												AND SOL.CCOMLICODI = $ComissaoCodigo 
												AND SOL.corglicodi = $OrgaoLicitanteCodigo 
												AND  SOL.cgrempcodi =". $_SESSION['_cgrempcodi_'] ; 



			$db = Conexao();

			$resultSoli = $db->query($sqlSolicitacoes);

			if (PEAR::isError($resultSoli)) {
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlSolicitacoes");
			}

			$intQuantidade = $resultSoli->numRows();

			if ($intQuantidade > 0) {
				$Url = "CadPregaoPresencialSessaoPublica.php";
			} else {
				$Url = "CadPregaoPresencialSessaoPublica.php";
			}

			$_SESSION['Processo'] = $Processo;
			$_SESSION['ProcessoAno'] = $ProcessoAno;
			$_SESSION['ComissaoCodigo'] = $ComissaoCodigo;
			$_SESSION['OrgaoLicitanteCodigo'] = $OrgaoLicitanteCodigo;	

			$IncluidoComSucesso = False;
			$Incluso = False;

			$Grupo = $_SESSION['_cgrempcodi_'];

			//Verificando se existe licitações ligadas a o processo , para ver qual programa devo chamar
			$sqlSolicitacoes = " SELECT  cpregasequ, fpregatipo
										FROM sfpc.tbpregaopresencial pp 
										WHERE 		pp.clicpoproc  = $Processo 
												AND pp.alicpoanop  = $ProcessoAno
												AND pp.ccomlicodi  = $ComissaoCodigo 
												AND pp.corglicodi  = $OrgaoLicitanteCodigo 
												AND pp.cgrempcodi  =". $_SESSION['_cgrempcodi_'] ; 




			$result = $db->query($sqlSolicitacoes);

			if (PEAR::isError($resultSoli)) {
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlSolicitacoes");
			}

			$Linha = $result->fetchRow();

			$intQuantidade = 0;

			$intQuantidade = $result->numRows();

			if ($intQuantidade > 0) {
				$_SESSION['PregaoCod'] 	= $Linha[0];
				$_SESSION['PregaoTipo'] = $Linha[1];
				$Incluso = True;
			} else {
				$sql = "SELECT MAX(cpregasequ) FROM sfpc.tbpregaopresencial";

				$res = $db->query($sql);

				if (PEAR::isError($res)) {
					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				} else {
					$Linha  = $res->fetchRow();
					$Codigo = $Linha[0] + 1;
				}

				# Insere Pregão Presencial #
				$sql  = "INSERT INTO sfpc.tbpregaopresencial( ";
				$sql .= "cpregasequ, clicpoproc, alicpoanop, ccomlicodi, corglicodi,";
				$sql .= "cgrempcodi, fpregatipo, tpregaaber, dpregacada, ";
				$sql .= "tpregaulat ";
				$sql .= " ) VALUES ( ";
				$sql .= "$Codigo, $Processo, $ProcessoAno, $ComissaoCodigo, $OrgaoLicitanteCodigo, $Grupo, 'N',";
				$sql .= "(select	tlicpodhab
						from	sfpc.tblicitacaoportal
						where	clicpoproc = $Processo
								and alicpoanop = $ProcessoAno
								and ccomlicodi = $ComissaoCodigo
								and corglicodi = $OrgaoLicitanteCodigo
								and cgrempcodi = $Grupo),";
				$sql .= "'".date("Y-m-d")."', ";
				$sql .= "'".date("Y-m-d H:i:s")."' )";

				$res  = $db->query($sql);

				if (PEAR::isError($result)) {
					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}

				$_SESSION['Mens'] = 1;
				$_SESSION['Tipo'] = 1;
				$_SESSION['Mensagem'] .= "- Pregão Presencial incluído com sucesso! <br/>";
				$IncluidoComSucesso = True;
			}
			
			//Verificando se existe licitações ligadas a o processo , para ver qual programa devo chamar
			$sqlSolicitacoes = " SELECT  cpregasequ, fpregatipo
										FROM sfpc.tbpregaopresencial pp 
										WHERE 		pp.clicpoproc  = $Processo 
												AND pp.alicpoanop  = $ProcessoAno
												AND pp.ccomlicodi  = $ComissaoCodigo 
												AND pp.corglicodi  = $OrgaoLicitanteCodigo 
												AND pp.cgrempcodi  =". $_SESSION['_cgrempcodi_'] ; 
				
				
			
			$result= $db->query($sqlSolicitacoes);
			$Linha = $result->fetchRow();
			$_SESSION['PregaoCod'] 	= $Linha[0];
			$_SESSION['PregaoTipo'] = $Linha[1];
			
			//Início - Lotes
			//Verificando se existem lotes ligados a o processo 
			
			$sqlSolicitacoes = "SELECT  		pl.cpregtsequ
									FROM 		sfpc.tbpregaopresenciallote pl 
									WHERE 		pl.cpregasequ  =".$_SESSION['PregaoCod']; 					
								
								
			
			$result = $db->query($sqlSolicitacoes);
			
			if( PEAR::isError($resultSoli) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlSolicitacoes");
			}
			
			$Linha = $result->fetchRow();
			
			$intQuantidade = 0;
			
			$intQuantidade = $result->numRows();					
			
			if($intQuantidade == 0)
			{
				$sqlSolicitacoes = "SELECT DISTINCT	ip.citelpnuml
										FROM 		sfpc.tbitemlicitacaoportal ip 
										WHERE 		ip.clicpoproc  = $Processo 
											AND 	ip.alicpoanop  = $ProcessoAno
											AND 	ip.ccomlicodi  = $ComissaoCodigo 
											AND 	ip.corglicodi  = $OrgaoLicitanteCodigo 
											AND 	ip.cgrempcodi  =". $_SESSION['_cgrempcodi_']."
										ORDER BY	ip.citelpnuml" ;

										
				$resultLotesInclusao = $db->query($sqlSolicitacoes);
				
				if( PEAR::isError($resultLotesInclusao) ){
					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlSolicitacoes");
				}
				
				$Linha = $resultLotesInclusao->fetchRow();
				
				$intQuantidadeInclusao = 0;
			
				$intQuantidadeInclusao = $resultLotesInclusao->numRows();
				
				if($intQuantidadeInclusao > 0)
				{
					for($itr = 0; $itr < $intQuantidadeInclusao; ++ $itr)
					{
						
						
						$sql = "SELECT MAX(cpregtsequ) FROM sfpc.tbpregaopresenciallote";
						$res = $db->query($sql);
						
						if (PEAR::isError($res)) {
							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								$LinhaCodLote	= $res->fetchRow();
								$Codigo			= $LinhaCodLote[0] + 1;
								$PregaoCod		= $_SESSION['PregaoCod'];
								$NumLote		= $Linha[0];
						}							
						
						
						$sql  = "INSERT INTO sfpc.tbpregaopresenciallote( ";
						$sql .= "cpregtsequ, cpregasequ, cpreslsequ, cpregtnuml, epregtdesc, vpregtvalv, vpregtvalr,";
						$sql .= "dpregtcada, ";
						$sql .= "tpregtulat ";
						$sql .= " ) VALUES ( ";
						$sql .= "$Codigo, $PregaoCod, 1, $NumLote, '', 0, 0,";
						$sql .= "'".date("Y-m-d")."', ";
						$sql .= "'".date("Y-m-d H:i:s")."' )";
						
						$result  = $db->query($sql);
						
						
						if( PEAR::isError($result) ){
							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}
						else
						{
							$Linha = $resultLotesInclusao->fetchRow();
						}
					}
					
					$_SESSION['Mens'] = 1;
					$_SESSION['Tipo'] = 1;
					$_SESSION['Mensagem'] .= "- Lote(s) incluído(s) com sucesso!";							
				}
				else
				{
					$_SESSION['Mensagem'] = "";	
					
					$sql = "DELETE FROM sfpc.tbpregaopresencial WHERE cpregasequ = $Codigo AND clicpoproc = $Processo AND alicpoanop = $ProcessoAno AND ccomlicodi = $ComissaoCodigo AND corglicodi = $OrgaoLicitanteCodigo AND cgrempcodi = $Grupo";
					$res = $db->query($sql);						
					$IncluidoComSucesso = False;
					
					$_SESSION['Mens'] = 1;
					$_SESSION['Tipo'] = 2;
					$_SESSION['Mensagem'] .= "- Processo sem Lotes! <br />";						
				}						
			}				
			
			//Fim - Lotes					
			
			$db->disconnect();	
			
			if( PEAR::isError($resultSoli) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlSolicitacoes");
			}
			
			
			if($_SESSION['PregaoCod'] != 0 and ($IncluidoComSucesso == True or $Incluso == True))
			{
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				exit();
			}
		}
		else
		{
			$_SESSION['Mens'] = 1;
			$_SESSION['Tipo'] = 2;
			$_SESSION['Mensagem'] .= "- O Usuário não está vinculado a nenhuma Comissão! <br />";				
		}
		
		$db->disconnect();		
    }
}
?>
<html>
<?
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
<!--
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" bgcolor="#FFFFFF" text="#000000" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadPregaoPresencialSelecionar.php" method="post" name="PregaoPresencial">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Pregão Presencial > Sessão Pública
    </td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<tr>
	  <td width="150"></td>
	  
	  <td align="left" colspan="2">
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
		<td width="150"></td>
		<td class="textonormal">
			<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
        <tr>
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	           SELECIONAR - PREGÃO PRESENCIAL
          </td>
        </tr>
        <tr>
          <td class="textonormal" bgcolor="#FFFFFF">
             <p align="justify">
             Para iniciar um pregão presencial, selecione o processo e clique no botão "Selecionar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table border="0" width="100%" summary="">
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7" style="font-weight: bold;">Processo: </td>
                <td class="textonormal"><input type="hidden" name="Critica" value="1"></td>
                <td class="textonormal" bgcolor="#FFFFFF">
                  <select name="LicitacaoProcessoAnoComissaoOrgao" class="textonormal">
                  	<option value="">Selecione um processo licitatório...</option>
                  	<!-- Mostra as licitações cadastradas -->
                  	<?php
										$db     = Conexao();					

										$sql	="	SELECT  A.CLICPOPROC,
															A.ALICPOANOP,
															A.CCOMLICODI,
															B.ECOMLIDESC,
															A.CORGLICODI
													FROM    SFPC.TBLICITACAOPORTAL A,
															SFPC.TBCOMISSAOLICITACAO B,
															SFPC.TBUSUARIOCOMIS D,
															SFPC.TBFASELICITACAO E 
													WHERE   D.CGREMPCODI = ".$_SESSION['_cgrempcodi_']."
														AND D.CUSUPOCODI = ".$_SESSION['_cusupocodi_']."
														AND D.CCOMLICODI = A.CCOMLICODI 
														AND A.CGREMPCODI = D.CGREMPCODI
														AND A.CCOMLICODI = B.CCOMLICODI  
														AND A.CMODLICODI = 5 
														AND A.ALICPOANOP = E.ALICPOANOP 
														AND A.CLICPOPROC = E.CLICPOPROC 
														AND A.CGREMPCODI = E.CGREMPCODI 
														AND A.CCOMLICODI = E.CCOMLICODI 
														AND A.CORGLICODI = E.CORGLICODI 
														AND E.CFASESCODI IN (2)									
														AND (   SELECT COUNT(ip.citelpnuml)
																FROM sfpc.tbitemlicitacaoportal ip 
																WHERE ip.clicpoproc  = A.CLICPOPROC
																	AND	ip.alicpoanop  = A.ALICPOANOP
																	AND ip.ccomlicodi  = A.CCOMLICODI
																	AND ip.corglicodi  = A.CORGLICODI
																	AND ip.cgrempcodi  = A.CGREMPCODI) > 0
													ORDER BY B.ECOMLIDESC ASC,
															 A.CGREMPCODI ASC,
															 A.ALICPOANOP DESC,
															 A.CLICPOPROC DESC";
																				
										print "<br> ".$sql."; <br />";
										$result = $db->query($sql);
										
										if( PEAR::isError($result) ){
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}
										else
										{
											$ComissaoCodigoAnt = "";
											
											while( $Linha = $result->fetchRow() ){
													if( $Linha[2] != $ComissaoCodigoAnt )
													{
															$ComissaoCodigoAnt = $Linha[2];
															echo "<option value=\"\">$Linha[3]</option>\n" ;
													}
													$NProcesso = substr($Linha[0] + 10000,1);
													echo "<option value=\"$Linha[0]_$Linha[1]_$Linha[2]_$Linha[4]\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$NProcesso/$Linha[1]</option>\n" ;
											}
										}
										$db->disconnect();
										?>
                  </select>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
 	        <td class="textonormal" align="right">
          	<input type="submit" value="Selecionar" class="botao">
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
