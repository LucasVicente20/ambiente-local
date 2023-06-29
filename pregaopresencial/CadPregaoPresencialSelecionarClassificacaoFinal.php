<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadPregaoPresencialSelecionarClassificacaoFinal.php
# Autor:    Hélio Miranda
# Data:     06/02/2017
# Objetivo: Programa de Seleção de Proceso Licitatório (Pregão Presencial)
# OBS.:     Tabulação 2 espaços
#			Irão aparecer as licitações de acordo com a(s) comissão(ões)
#           do usuário que está logado
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/pregaopresencial/CadPregaoPresencialSessaoPublica.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$LicitacaoProcessoAnoComissaoOrgao = $_POST['LicitacaoProcessoAnoComissaoOrgao'];
		$Critica                           = $_POST['Critica'];
}else{
		$Critica                           = $_GET['Critica'];
		$Mensagem                          = $_GET['Mensagem'];
		$Mens                              = $_GET['Mens'];
		$Tipo                              = $_GET['Tipo'];
}

$_SESSION['CodLoteSelecionado'] = null;

# Critica dos Campos #
$Mensagem = urldecode($Mensagem);
if( $Critica == 1){
		$Mens = 0;
		$Mensagem = "Informe: ";
    if( $LicitacaoProcessoAnoComissaoOrgao == "" ) {
	      $Mens = 1; $Tipo = 2; $Troca = 1;
        $Mensagem .= "<a href=\"javascript: document.PregaoPresencial.PregaoPresencialCodigo.focus();\" class=\"titulo2\">Selecione um Processo (Processo/Ano)</a>";
    }else
	{
		$db     = Conexao();
		
		$sqlUC = "SELECT uc.ccomlicodi FROM sfpc.tbusuariocomis uc WHERE uc.cusupocodi = ".$_SESSION['_cusupocodi_']." AND uc.cgrempcodi = ".$_SESSION['_cgrempcodi_'];
		$resUC = $db->query($sqlUC);

		if (PEAR::isError($resUC)) {
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}
		else
		{
			$LinhaUC  	= $resUC->fetchRow();
			$UC			= $LinhaUC[0];
		}			
		
		if($UC > 0)
		{		
		
			$NProcessoAnoComissao = explode("_",$LicitacaoProcessoAnoComissaoOrgao);
			$Processo             = $NProcessoAnoComissao[0];
			$ProcessoAno          = $NProcessoAnoComissao[1];
			$ComissaoCodigo       = $NProcessoAnoComissao[2];
			$OrgaoLicitanteCodigo = $NProcessoAnoComissao[3];
			$PregaoPresencialCod  = $NProcessoAnoComissao[4];
			
			//Verificando se existe licitações ligadas a o processo , para ver qual programa devo chamar
			$sqlSolicitacoes = " SELECT  csolcosequ ,clicpoproc , alicpoanop , cgrempcodi ,ccomlicodi ,corglicodi
										FROM SFPC.TBSOLICITACAOLICITACAOPORTAL SOL 
										WHERE 	SOL.CLICPOPROC = $Processo 
												AND SOL.ALICPOANOP = $ProcessoAno
												AND SOL.CCOMLICODI = $ComissaoCodigo 
												AND SOL.corglicodi = $OrgaoLicitanteCodigo 
												AND  SOL.cgrempcodi =". $_SESSION['_cgrempcodi_'] ; 
				
				
			
			$resultSoli = $db->query($sqlSolicitacoes);
			
			if( PEAR::isError($resultSoli) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlSolicitacoes");
			}
			
			$intQuantidade = $resultSoli->numRows();
			
			
			if($intQuantidade>0){
				$Url = "RelPregaoPresencialClassificacaoFinalPdf.php?Processo=$Processo&Ano=$ProcessoAno&Comissao=$ComissaoCodigo&Orgao=$OrgaoLicitanteCodigo";
			}else{
				$Url = "RelPregaoPresencialClassificacaoFinalPdf.php?Processo=$Processo&Ano=$ProcessoAno&Comissao=$ComissaoCodigo&Orgao=$OrgaoLicitanteCodigo";
			}
			
			  $_SESSION['Processo'] 			= $Processo;
			  $_SESSION['ProcessoAno'] 			= $ProcessoAno;
			  $_SESSION['ComissaoCodigo'] 		= $ComissaoCodigo;
			  $_SESSION['OrgaoLicitanteCodigo'] = $OrgaoLicitanteCodigo;
			  $_SESSION['PregaoPresencialCod'] 	= $PregaoPresencialCod;	

			  
			  $IncluidoComSucesso = False;
			  $Incluso = False;

			  $Grupo = $_SESSION['_cgrempcodi_'];
			  
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
				
				$PregaoCod				= $_SESSION['PregaoCod'];

			//Início Verificação de Deserta...				
				
				$sqlDeserta 			= " SELECT  COUNT(pl.cpregtsequ) as desertos
											FROM 	sfpc.tbpregaopresenciallote pl 
											WHERE 		pl.cpregasequ  = $PregaoCod  
													AND pl.cpreslsequ  = 4"; 
				

				
				$ResultDeserta			= $db->query($sqlDeserta);
				$LinhaDeserta 			= $ResultDeserta->fetchRow();
				
				if($LinhaDeserta[0] > 0)
				{
					$_SESSION['deserta'] = "D";
				}
				else
				{
					$_SESSION['deserta'] = "N";
				}
									
			
			//... Fim Verificação de Deserta					
			
			if($_SESSION['PregaoPresencialCod'] != 0)
			{
				$sql = "SELECT cpreatsequ FROM sfpc.tbpregaopresencialata WHERE cpregasequ = $PregaoCod";
				$res = $db->query($sql);
				
				if (PEAR::isError($res)) {
					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						$Linha  			= $res->fetchRow();
						$CodigoExistente 	= $Linha[0];
				}					
				
				
				if($CodigoExistente == 0 or $CodigoExistente == null)
				{
					$_SESSION['ParagrafoAdicionalA']  = null;
					$_SESSION['ParagrafoAdicionalB']  = null;
					$_SESSION['ParagrafoAdicionalC']  = null;
					$_SESSION['ParagrafoAdicionalD']  = null;
					$_SESSION['ParagrafoAdicionalE']  = null;
					
					$_SESSION['OrgaoLicitante']  			= null;
					$_SESSION['EnderecoOrgaoLicitante']  	= null;	
							$_SESSION['LoteInicialIntervalo']		= null;
							$_SESSION['LoteFinalIntervalo']			= null;						
				}
				else
				{
					$sql = "SELECT epreatpara, epreatparb, epreatparc, epreatpard, epreatorgl, epreatendo, npreattemd FROM sfpc.tbpregaopresencialata WHERE cpreatsequ = $CodigoExistente";
					$res = $db->query($sql);
					
					if (PEAR::isError($res)) {
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
					}else{
							$Linha  			= $res->fetchRow();
							
							$_SESSION['ParagrafoAdicionalA']  = $Linha[0];
							$_SESSION['ParagrafoAdicionalB']  = $Linha[1];
							$_SESSION['ParagrafoAdicionalC']  = $Linha[2];
							$_SESSION['ParagrafoAdicionalD']  = $Linha[3];	

							$_SESSION['OrgaoLicitante']  			= $Linha[4];
							$_SESSION['EnderecoOrgaoLicitante']  	= $Linha[5];								
							$_SESSION['TempoDeToleranciaDeserta']  	= $Linha[6];
							$_SESSION['TipoAta']     				= null;
							$_SESSION['TotalLotes']					= 0;
							
							$_SESSION['LoteInicialIntervalo']		= null;
							$_SESSION['LoteFinalIntervalo']			= null;
					}						
				}
				
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				$db->disconnect();
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
<form action="CadPregaoPresencialSelecionarClassificacaoFinal.php" method="post" name="PregaoPresencial">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Pregão Presencial > Relatórios > Classificação Final
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
             Para gerar a Classificação Final de um Pregão Presencial, selecione um pregão presencial finalizado e clique no botão "Selecionar".
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
                  	<option value="">Selecione um Pregão...</option>
                  	<!-- Mostra as licitações cadastradas -->
                  	<?php
										$db     = Conexao();										
										
										$sql = "SELECT 		DISTINCT P.cpregasequ, P.clicpoproc, P.alicpoanop, P.cgrempcodi, P.ccomlicodi, P.corglicodi, P.fpregatipo, C.ecomlidesc, C.ccomlicodi
												FROM 		tbpregaopresencial P, tbcomissaolicitacao C, tbusuariocomis U 
												WHERE 		(SELECT COUNT(L.cpregtsequ) FROM tbpregaopresenciallote L, tbpregaopresencial PB WHERE L.cpregasequ = PB.cpregasequ AND PB.cpregasequ = P.cpregasequ) = 
															(SELECT COUNT(LB.cpregtsequ) FROM tbpregaopresenciallote LB, tbpregaopresencial PB WHERE LB.cpregasequ = PB.cpregasequ AND PB.cpregasequ = P.cpregasequ AND LB.cpreslsequ > 2)
														AND	P.ccomlicodi = C.ccomlicodi
														AND	U.cgrempcodi = P.cgrempcodi
														AND	U.cusupocodi = ".$_SESSION['_cusupocodi_']." 
												ORDER BY 	C.ecomlidesc ASC, P.cgrempcodi ASC, P.alicpoanop DESC, P.clicpoproc DESC";										
										
										$result = $db->query($sql);
										
										if( PEAR::isError($result) ){
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql"); 
										}
										else
										{
											$ComissaoCodigoAnt = "";
											
											while( $Linha = $result->fetchRow() ){
													if( $Linha[8] != $ComissaoCodigoAnt )
													{
															$ComissaoCodigoAnt = $Linha[8];
															echo "<option value=\"\">$Linha[7]</option>\n" ;
													}
													$NProcesso = substr($Linha[1] + 10000,1);
													echo "<option value=\"$Linha[1]_$Linha[2]_$Linha[4]_$Linha[5]_$Linha[0]\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$NProcesso/$Linha[2]</option>\n" ;
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
