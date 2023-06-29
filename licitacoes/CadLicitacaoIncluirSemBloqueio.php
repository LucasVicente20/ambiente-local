<?php
#-------------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadLicitacaoIncluirSemBloqueio.php
# Autor:    Rossana Lira
# Data:     03/01/05
# Objetivo: Programa de Inclusão de Licitação sem número de bloqueio
# OBS.:     Tabulação 2 espaços
#						Irão aparecer as comissões de acordo com o usuário que está logado
# Alterado: Rossana
# Data:     24/05/2007 - Liberar Permissão Remunerada de Uso para Tomada de Preços
# Alterado: João Batista Brito
# Data:     29/10/2012 - Erros na inclusão de licitação sem bloqueio - Redmine 16688
#---------------------------------------------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# Data: 06/07/2018
# Objetivo: Tarefa #196114
#-----------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# Data: 04/01/2019
# Objetivo: Tarefa #208518
#-----------------------------------------------
# Alterado: Osmar Celestino
# Data:     27/04/2023
# Objetivo: Tarefa Redmine 282315
# -------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/licitacoes/CadLicitacaoBloqueio.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
    $Critica                  = $_POST['Critica'];
    $Botao                    = $_POST['Botao'];
    $Processo                 = trim($_POST['Processo']);
    $ProcessoAno              = trim($_POST['ProcessoAno']);
    $ModalidadeCodigo         = $_POST['ModalidadeCodigo'];
    $RegistroPreco            = $_POST['RegistroPreco'];
	$legislacao				  = $_POST['legislacao'];
    $ComissaoCodigo           = $_POST['ComissaoCodigo'];
    $Licitacao                = trim($_POST['Licitacao']);
    $LicitacaoAno             = trim($_POST['LicitacaoAno']);
    $LicitacaoDtAbertura      = trim($_POST['LicitacaoDtAbertura']);
    $LicitacaoHoraAbertura    = trim($_POST['LicitacaoHoraAbertura']);
    $LicitacaoDtEncerramento  = trim($_POST['LicitacaoDtEncerramento']);
    $LicitacaoHoraEncerramento= trim($_POST['LicitacaoHoraEncerramento']);
    $OrgaoLicitanteCodigo     = $_POST['OrgaoLicitanteCodigo'];
    $CodUsuario				  = $_POST['CodUsuario'];
    $Grupo                    = $_POST['Grupo'];
    $NCaracteres              = $_POST['NCaracteres'];
    $LicitacaoObjeto          = strtoupper(trim($_POST['LicitacaoObjeto']));
    $ValorTotal               = $_POST['ValorTotal'];
    $licitacaoTipo            = $_POST['LicitacaoTipoSelecionado'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "CadLicitacaoIncluirSemBloqueio.php";

# Situação #
$LicitacaoStatus = "I";

if( $ComissaoCodigo <> "" ){
		$db = Conexao();
		# Busca o Grupo da Comissão de Licitação
	  $sql    = " SELECT CGREMPCODI FROM SFPC.TBCOMISSAOLICITACAO ";
		$sql   .= "  WHERE CCOMLICODI = $ComissaoCodigo";
		$result = $db->query($sql);
		if( db::isError($result) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$LinhaGrupo = $result->fetchRow();
				$Grupo		  = $LinhaGrupo[0];
		}
		$db->disconnect();
}



if( $Critica == 1 ){
	$db = Conexao();
	if ( $Botao == "Incluir" ) {
			$Mens     = 0;
			$Mensagem = "Informe: ";
			if( $ComissaoCodigo == "" ){
					if ( $Mens == 1 ) { $Mensagem .= ", "; }
					$Mens      = 1;
					$Tipo      = 2;
					$Mensagem .= "<a href=\"javascript:document.Licitacao.ComissaoCodigo.focus();\" class=\"titulo2\">Comissão</a>";
			}

			if( $Processo == "" ){
					if ( $Mens == 1 ) { $Mensagem .= ", "; }
					$Mens      = 1;
					$Tipo      = 2;
					$Mensagem .= "<a href=\"javascript:document.Licitacao.Processo.focus();\" class=\"titulo2\">Processo</a>";
			} /*elseif (!preg_match("^([0-9]){1,}$",$Processo)) {
          		if ($Mens == 1){ $Mensagem.=", "; }
              		$Mens = 1;
              		$Tipo = 2;
              		$Mensagem .= "<a href=\"javascript:document.Licitacao.Processo.focus();\" class=\"titulo2\">Campo Processo apenas permite números</a>";
      		}*/

			if( $ProcessoAno == "" ){
					if ( $Mens == 1 ) { $Mensagem .= ", "; }
					$Mens      = 1;
					$Tipo      = 2;
					$Mensagem .= "<a href=\"javascript:document.Licitacao.Processo.focus();\" class=\"titulo2\">Ano do Processo</a>";
			}
			if( $Licitacao == "" ){
					if ( $Mens == 1 ) { $Mensagem .= ", "; }
					$Mens      = 1;
					$Tipo      = 2;
					$Mensagem .= "<a href=\"javascript:document.Licitacao.Licitacao.focus();\" class=\"titulo2\">Licitação</a>";
			}
			if( $LicitacaoAno == "" ){
					if ( $Mens == 1 ) { $Mensagem .= ", "; }
					$Mens      = 1;
					$Tipo      = 2;
					$Mensagem .= "<a href=\"javascript:document.Licitacao.LicitacaoAno.focus();\" class=\"titulo2\">Ano da Licitação</a>";
			}
			if( $ModalidadeCodigo == "" ){
					if ( $Mens == 1 ) { $Mensagem .= ", "; }
					$Mens      = 1;
					$Tipo      = 2;
					$Mensagem .= "<a href=\"javascript:document.Licitacao.ModalidadeCodigo.focus();\" class=\"titulo2\">Modalidade</a>";
			}
			$ValidaData = ValidaData($LicitacaoDtAbertura);
			if( $ValidaData != "" ){
					if ( $Mens == 1 ) { $Mensagem .= ", "; }
					$Mens      = 1;
					$Tipo      = 2;
					$Mensagem .= "<a href=\"javascript:document.Licitacao.LicitacaoDtAbertura.focus();\" class=\"titulo2\">Data Válida</a>";
			}
			$ValidaData = ValidaData($LicitacaoDtEncerramento);
			if( $ValidaData != "" && $legislacao =="14133"){
					if ( $Mens == 1 ) { $Mensagem .= ", "; }
					$Mens      = 1;
					$Tipo      = 2;
					$Mensagem .= "<a href=\"javascript:document.Licitacao.LicitacaoDtEncerramento.focus();\" class=\"titulo2\">Data de Encerramento Válida</a>";
			}
			$ValidaHora = ValidaHora($LicitacaoHoraAbertura);
			if( $ValidaHora != "" ){
					if ( $Mens == 1 ) { $Mensagem .= ", "; }
					$Mens      = 1;
					$Tipo      = 2;
					$Mensagem .= "<a href=\"javascript:document.Licitacao.LicitacaoHoraAbertura.focus();\" class=\"titulo2\">Hora Válida</a>";
			}else{
					$HhMm = explode(":",$LicitacaoHoraAbertura);
					$Hh   = substr($HhMm[0] + 100,1);
					$Mm   = substr($HhMm[1] + 100,1);
					$LicitacaoHoraAbertura = $Hh .":". $Mm;
			}
			$ValidaHoraEncerramento = ValidaHora($LicitacaoHoraEncerramento);
			if( $ValidaHoraEncerramento != "" && $legislacao == "14133"){
					if ( $Mens == 1 ) { $Mensagem .= ", "; }
					$Mens      = 1;
					$Tipo      = 2;
					$Mensagem .= "<a href=\"javascript:document.Licitacao.LicitacaoHoraAbertura.focus();\" class=\"titulo2\">Hora de Encerramento Válida</a>";
			}else{
					$HhMm = explode(":",$LicitacaoHoraEncerramento);
					$Hh   = substr($HhMm[0] + 100,1);
					$Mm   = substr($HhMm[1] + 100,1);
					$LicitacaoHoraEncerramento = $Hh .":". $Mm;
			}
			if( $OrgaoLicitanteCodigo == "" ){
					if ( $Mens == 1 ) { $Mensagem .= ", "; }
					$Mens      = 1;
					$Tipo      = 2;
					$Mensagem .= "<a href=\"javascript:document.Licitacao.OrgaoLicitanteCodigo.focus();\" class=\"titulo2\">Órgão Licitante</a>";
			}
			if( $LicitacaoObjeto == "" ){
					if ( $Mens == 1 ) { $Mensagem .= ", "; }
					$Mens      = 1;
					$Tipo      = 2;
					$Mensagem .= "<a href=\"javascript:document.Licitacao.LicitacaoObjeto.focus();\" class=\"titulo2\">Objeto</a>";
			} elseif (strlen($LicitacaoObjeto) > 900) {
					if ($Mens == 1) {
						$Mensagem .= ", ";
					}
					$Mens      = 1;
					$Tipo      = 2;
					$Mensagem .= "<a href=\"javascript:document.Licitacao.LicitacaoObjeto.focus();\" class=\"titulo2\">Objeto da Licitação com até 900 Caracteres</a>";
			}
			if( $Mens == 0 ){
					if( $ModalidadeCodigo != 10 ){
							if( $ValorTotal == "" ) {
									if ( $Mens == 1 ) { $Mensagem .= ", "; }
									$Mens      = 1;
									$Tipo      = 2;
									$Mensagem .= "<a href=\"javascript:document.Licitacao.ValorTotal.focus();\" class=\"titulo2\">Valor Total Estimado</a>";
							}else{
									if( ! SoNumVirg($ValorTotal) ){
											if ( $Mens == 1 ) { $Mensagem .= ", "; }
											$Mens      = 1;
											$Tipo      = 2;
											$Mensagem .= "<a href=\"javascript:document.Licitacao.ValorTotal.focus();\" class=\"titulo2\">Valor Total Estimado Válido</a>";
									}else{
											$Numero = Decimal($ValorTotal);
											if( ! $Numero ){
													if ( $Mens == 1 ) { $Mensagem .= ", "; }
													$Mens      = 1;
													$Tipo      = 2;
													$Mensagem .= "<a href=\"javascript:document.Licitacao.ValorTotal.focus();\" class=\"titulo2\">Valor Total Estimado Válido</a>";
											}else{
											 		$ValorTotal = $Numero;
						 							if( $ValorTotal == 0 ){
															if( $Mens == 1 ){ $Mensagem .= ", "; }
															$Mens      = 1;
															$Tipo      = 2;
															$Mensagem .= "<a href=\"javascript:document.Licitacao.ValorTotal.focus();\" class=\"titulo2\">Valor Total Diferente de Zero</a>";
            							}
											}
									}
							}
					}
			}
			if( $CodUsuario == "" ){
					if ( $Mens == 1 ) { $Mensagem .= ", "; }
					$Mens       = 1;
					$Tipo       = 2;
					$Mensagem  .= "<a href=\"javascript:document.Licitacao.CodUsuario.focus();\" class=\"titulo2\">Informar: Usuário</a>";
			}
		  if( $Mens == 0 ){
					# Verifica duplicidade de Processo Licitatório/Ano #
		  	  $sql  = " SELECT CLICPOPROC ";
		  	  $sql .= "   FROM SFPC.TBLICITACAOPORTAL ";
					$sql .= "  WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno  ";
					$sql .= "    AND CGREMPCODI = $Grupo AND CCOMLICODI = $ComissaoCodigo ";
					$result = $db->query($sql);
					if( db::isError($result) ){
							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
					}else{
							$Rows = $result->numRows();
							if( $Rows == 0 or $Rows == "" ){
									# Verifica duplicidade de Modalidade/Número de Licitação #
									$sql  = " SELECT CLICPOPROC ";
									$sql .= "   FROM SFPC.TBLICITACAOPORTAL ";
									$sql .= "  WHERE CGREMPCODI = $Grupo ";
									$sql .= "    AND CCOMLICODI = $ComissaoCodigo AND CMODLICODI = $ModalidadeCodigo ";
									$sql .= "    AND CLICPOCODL = $Licitacao AND ALICPOANOP = $ProcessoAno  ";
									$sql .= "    AND ( CLICPOPROC <> $Processo OR ALICPOANOP <> $ProcessoAno  )";
									$result = $db->query($sql);
									if( db::isError($result) ){
									   ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
									}else{
											$Rows = $result->numRows();
											if( $Rows == 0 or $Rows == "" ){
													$Data                     = date("Y-m-d H:i:s");
													$LicitacaoDtAberturaFinal = substr($LicitacaoDtAbertura,6,4)."-".substr($LicitacaoDtAbertura,3,2)."-".substr($LicitacaoDtAbertura,0,2);
													$DataHoraAbertura         = "$LicitacaoDtAberturaFinal $LicitacaoHoraAbertura:00";
													$LicitacaoDtEncerramentoFinal = substr($LicitacaoDtEncerramento,6,4)."-".substr($LicitacaoDtEncerramento,3,2)."-".substr($LicitacaoDtEncerramento,0,2);
													$DataHoraEncerramento         = "$LicitacaoDtEncerramentoFinal $LicitacaoHoraEncerramento:00";
													if( $ValorTotal != "" ){ $ValorTotal = str_replace(",",".",$ValorTotal); }
													if( $ModalidadeCodigo == 10 ){ $ValorTotal = 0; }
													
													if($DataHoraEncerramento == "-- 00:00:00"){
														$DataHoraEncerramento = "NULL";
													}else{
														$DataHoraEncerramento = "'".$DataHoraEncerramento."'";
													}

													$sqlSigla = "SELECT cl.ecomlisigl
													from sfpc.tbcomissaolicitacao cl
													WHERE cl.ccomlicodi = $ComissaoCodigo";

													$resultadoSigla = executarSQL($db,$sqlSigla);
													$linhaSigla = $resultadoSigla->fetchRow();

													$numeroLicitacaoPNCP = $linhaSigla[0]."-PL ".$Processo; 


													# Insere Licitacao #
													$db->query("BEGIN TRANSACTION");
													$sql  = "INSERT INTO SFPC.TBLICITACAOPORTAL ( ";
													$sql .= "CLICPOPROC, ALICPOANOP, CGREMPCODI, CCOMLICODI, CORGLICODI, ";
													$sql .= "CMODLICODI, CLICPOCODL, ALICPOANOL, TLICPODHAB, XLICPOOBJE, ";
													$sql .= "VLICPOVALE, VLICPOVALH, FLICPOSTAT, CUSUPOCODI, TLICPOULAT, ";
													$sql .= "FLICPOREGP, FLICPOTIPO, ELICPONUME, flicpolegi, tlicpodhfe ";
													$sql .= ") VALUES (";
													$sql .= "$Processo, $ProcessoAno,$Grupo, $ComissaoCodigo, $OrgaoLicitanteCodigo, ";
													$sql .= "$ModalidadeCodigo, $Licitacao, $LicitacaoAno, '$DataHoraAbertura', '$LicitacaoObjeto', ";
													$sql .= "$ValorTotal, NULL,'$LicitacaoStatus', $CodUsuario, '$Data', ";
													$sql .= "'$RegistroPreco', '$licitacaoTipo', '$numeroLicitacaoPNCP','$legislacao', $DataHoraEncerramento )";
													
													$result = $db->query($sql);
													if( db::isError($result) ){
													    $result = $db->query("ROLLBACK");
													    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
													}
													$result = $db->query("COMMIT");
													$result = $db->query("END TRANSACTION");

													$db->disconnect();

													# Enviando Mensagem #
													$Mens     = 1;
													$Tipo     = 1;
													$Mensagem = "Licitação Incluída com Sucesso";

													# Limpando Variáveis #
													$ComissaoCodigo        = "";
													$Grupo                 = "";
													$Processo              = "";
													$ProcessoAno           = "";
													$Licitacao             = "";
													$LicitacaoAno          = "";
													$LicitacaoDtAbertura   = "";
													$LicitacaoHoraAbertura = "";
													$LicitacaoDtEncerramento  = "";
													$LicitacaoHoraEncerramento = "";
													$ModalidadeCodigo      = "";
													$OrgaoLicitanteCodigo  = "";
													$LicitacaoObjeto       = "";
													$CodUsuario            = "";
													$NCaracteres           = 0;
													$ValorTotal            = "";
													$Total                 = "";
								     	}else{
													$Mens     = 1;
													$Tipo     = 2;
													$Mensagem = "Licitação/Ano Já Cadastrado para esta Modalidade";
											}
									}
								}else{
										$Mens     = 1;
										$Tipo     = 2;
										$Mensagem = "Processo/Ano Já Cadastrado para esta Comissão";
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
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.Licitacao.Botao.value=valor;
	document.Licitacao.submit();
}
function ncaracteres(valor){
	document.Licitacao.NCaracteres.value = '' +  document.Licitacao.LicitacaoObjeto.value.length;
	if( navigator.appName == 'Netscape' && valor ) {  //Netscape Only
		document.Licitacao.NCaracteres.focus();
	}
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadLicitacaoIncluirSemBloqueio.php" method="post" name="Licitacao">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Licitação > Incluir Sem Bloqueio
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
      <table  border="0" cellspacing="0" cellpadding="3" summary="">
        <tr>
	      	<td class="textonormal">
	        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" width="100%" bgcolor="#ffffff">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
		    					INCLUIR - LICITAÇÃO SEM BLOQUEIO
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	      	    		<p align="justify">
	        	    		Para incluir uma nova licitação, informe os dados abaixo e clique no botão "Incluir". Os itens obrigatórios estão com *. <br>
	        	    		O Número do Processo Licitatório é gerado sequencialmente para cada Comissão de Licitação e o Número da Licitação é gerado de acordo com a
	        	    		Comissão de Licitação e Modalidade.<br>
	          	   	</p>
	          		</td>
		        	</tr>
		        	<tr>
				        <td>
				          <table class="textonormal" border="0" align="left" width="100%" summary="">
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Comissão* </td>
				              <td class="textonormal">
				                <select name="ComissaoCodigo" class="textonormal" onchange="submit()">
						  						<option value="">Selecione uma Comissão...</option>
						   						<?php
											    $db   = Conexao();
											    $sql  = " SELECT A.CCOMLICODI, A.ECOMLIDESC ";
											    $sql .= "   FROM SFPC.TBCOMISSAOLICITACAO A ";
											    $sql .= "  WHERE FCOMLISTAT = 'A' ORDER BY A.ECOMLIDESC";
											    $result = $db->query($sql);
													if( db::isError($result) ){
													   ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
													}else{
															while( $Linha = $result->fetchRow() ){
																  if( $Linha[0] == $ComissaoCodigo ){
																  		echo "<option value=\"$Linha[0]\" selected>$Linha[1]</option>\n";
																  }else{
																  		echo "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
																  }
													    }
													}
						   						?>
												</select>
				              </td>
				            </tr>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7" height="20">Processo</td>
				              <td class="textonormal">
				                <input type="text" name="Processo" size="4" maxlength="4" value="<?php echo $Processo; ?>" class="textonormal">
				              </td>
				            </tr>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7" height="20">Ano</td>
				              <td class="textonormal">
				                <input type="text" name="ProcessoAno" size="4" maxlength="4" value="<?php echo $ProcessoAno; ?>" class="textonormal">
				              </td>
				            </tr>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Modalidade* </td>
				              <td class="textonormal">
				                <select name="ModalidadeCodigo" class="textonormal">
						  						<option value="">Selecione uma Modalidade...</option>
								    	  	<?php
										      $sql    = "SELECT CMODLICODI, EMODLIDESC FROM	SFPC.TBMODALIDADELICITACAO ORDER BY AMODLIORDE";
										      $result = $db->query($sql);
													if( db::isError($result) ){
													   ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
													}else{
															while( $Linha = $result->fetchRow() ){
															   	if( $Linha[0] == $ModalidadeCodigo ){
															    		echo "<option value=\"$Linha[0]\" selected>$Linha[1]</option>\n";
															   	}else{
															      	echo "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
															   	}
												     	}
												   }
											    ?>
												</select>
				              </td>
				            </tr>
										<?php if( $ModalidadeCodigo == 14 or $ModalidadeCodigo == 3 or $ModalidadeCodigo == 5 or $ModalidadeCodigo == 10 or $ModalidadeCodigo == 2 ){ ?>
										<tr>
				              <td class="textonormal" bgcolor="#DCEDF7" height="20">
				              	<!-- Caso a modalidade seja concorrência ou tomada de preços apareça nome Permissão Remunerada de Uso -->
				              	Registro de Preço<?php if( $ModalidadeCodigo == 3 or $ModalidadeCodigo == 2 ){ echo "/Permissão Remunerada de Uso"; }?>
				              </td>
				              <td class="textonormal">
				              	<input type="checkbox" name="RegistroPreco" value="S" <?php if( $RegistroPreco == "S" ){ echo "checked"; } ?> >
				              </td>
				            </tr>
				            <?php } ?>
										<tr>
				              <td class="textonormal" bgcolor="#DCEDF7" height="20">Licitação</td>
				              <td class="textonormal">
				                <input type="text" name="Licitacao" size="4" maxlength="4" value="<?php echo $Licitacao; ?>" class="textonormal">
				              </td>
				            </tr>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7" height="20">Ano da Licitação</td>
				              <td class="textonormal">
				                <input type="text" name="LicitacaoAno" size="4" maxlength="4" value="<?php echo $LicitacaoAno; ?>" class="textonormal">
				              </td>
				            </tr>
				            <tr>

				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7" height="20">Legislação de compras*</td>
				              <td class="textonormal" onclick="javascript:enviar('legislacao');">
							 	<input type="radio" id="lei8666" name="legislacao" value="8666" <?php if ($_POST['legislacao'] == "8666"){ ?> checked <?php } ?>>
								<label for="lei8666">Lei 8.666/1993</label><br>
								<input type="radio" id="lei14133" name="legislacao" value="14133" <?php if ($_POST['legislacao'] == "14133"){ ?> checked <?php } ?>>
								<label for="lei14133">Lei 14.133/2021</label>
				              </td>
				            </tr>

				            <tr>

				              <td class="textonormal" bgcolor="#DCEDF7">Data de Abertura* </td>
				              <td class="textonormal">
												<?php $URL = "../calendario.php?Formulario=Licitacao&Campo=LicitacaoDtAbertura";?>
												<input type="text" name="LicitacaoDtAbertura" size="10" maxlength="10" value="<?php echo $LicitacaoDtAbertura; ?>" class="textonormal">
												<a href="javascript:janela('<?php echo $URL ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
												<font class="textonormal">dd/mm/aaaa</font>
					      			</td>
				            </tr>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Hora de Abertura* </td>
				              <td class="textonormal">
				                <input type="text" name="LicitacaoHoraAbertura" size="4" maxlength="5" value="<?php echo $LicitacaoHoraAbertura; ?>" class="textonormal">
				                <font class="textonormal">hh:mm</font><br>
				              </td>
				            </tr>
							<?php if($_POST['legislacao'] == "14133"){?>
				            <tr>

				              <td class="textonormal" bgcolor="#DCEDF7">Data de Encerramento* </td>
				              <td class="textonormal">
												<?php $URL = "../calendario.php?Formulario=Licitacao&Campo=LicitacaoDtEncerramento";?>
												<input type="text" name="LicitacaoDtEncerramento" size="10" maxlength="10" value="<?php echo $LicitacaoDtEncerramento; ?>" class="textonormal">
												<a href="javascript:janela('<?php echo $URL ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
												<font class="textonormal">dd/mm/aaaa</font>
					      			</td>
				            </tr>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Hora de Encerramento* </td>
				              <td class="textonormal">
				                <input type="text" name="LicitacaoHoraEncerramento" size="4" maxlength="5" value="<?php echo $LicitacaoHoraEncerramento; ?>" class="textonormal">
				                <font class="textonormal">hh:mm</font><br>
				              </td>
				            </tr>
							<?php } ?>
 				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Órgão Licitante*</td>
				              <td class="textonormal">
					           		<select name="OrgaoLicitanteCodigo" class="textonormal">
											  	<option value="">Selecione um Órgão Licitante...</option>
								    			<?php if( $Grupo != "" ){
								    			$sql  = "   SELECT A.CORGLICODI, A.EORGLIDESC ";
											    $sql .= "     FROM SFPC.TBORGAOLICITANTE A, ";
											    $sql .= "          SFPC.TBGRUPOORGAO G ";
											    $sql .= "    WHERE A.FORGLISITU <> 'I' ";
											    $sql .= "      AND G.CORGLICODI = A.CORGLICODI ";
											    $sql .= "      AND G.CGREMPCODI = $Grupo ";
											    $sql .= "    ORDER BY A.EORGLIDESC ";
											    $result = $db->query($sql);
													if( db::isError($result) ){
													  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
													}
													while( $Linha = $result->fetchRow() ){
														  if( $Linha[0] == $OrgaoLicitanteCodigo ){
														  		echo "<option value=\"$Linha[0]\" selected>$Linha[1]</option>\n";
														  }else{
														    	echo "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
														  }
												    }
								    			}
											    ?>
									</select>
								      </td>
				            </tr>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Objeto*</td>
				              <td class="textonormal">
				                <font class="textonormal">máximo de 900 caracteres</font>
												<input type="text" name="NCaracteres" size="3" value="<?php echo $NCaracteres; ?>" OnFocus="javascript:document.Licitacao.LicitacaoObjeto.focus();" class="textonormal"><br>
												<textarea name="LicitacaoObjeto" cols="39" rows="5" OnKeyUp="javascript:ncaracteres(1)" OnBlur="javascript:ncaracteres(0)" OnSelect="javascript:ncaracteres(1)" class="textonormal"><?php echo $LicitacaoObjeto; ?></textarea>
						          </td>
				            </tr>
                            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Valor Total Estimado* </td>
				              <td class="textonormal">
				                <input type="text" name="ValorTotal" size="17" maxlength="17" value="<?php echo $ValorTotal; ?>" class="textonormal">
				              </td>
				            </tr>
                            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Usuário da Comissão de Licitação*</td>
				              <td class="textonormal">
                                <select name="CodUsuario" class="textonormal">
                                    <option value="">Selecione um Usuário da Comissão de Licitação...</option>
                                    <?php
                                    if(!empty($ComissaoCodigo)) {
                                        $sql = " SELECT DISTINCT B.CUSUPOCODI, C.EUSUPORESP ";
                                        $sql .= "   FROM SFPC.TBCOMISSAOLICITACAO A, SFPC.TBUSUARIOCOMIS B, SFPC.TBUSUARIOPORTAL C ";
                                        $sql .= "  WHERE A.CGREMPCODI = $Grupo AND A.CCOMLICODI = $ComissaoCodigo ";
                                        $sql .= "    AND B.CGREMPCODI = A.CGREMPCODI AND B.CCOMLICODI = A.CCOMLICODI ";
                                        $sql .= "    AND C.CGREMPCODI = B.CGREMPCODI AND C.CUSUPOCODI = B.CUSUPOCODI ";
										$result = $db->query($sql);
                                        while ($Linha = $result->fetchRow()) {
                                            if ($Linha[0] == $CodUsuario) {
                                                echo "<option value=\"$Linha[0]\" selected>$Linha[1]</option>\n";
                                            } else {
                                                echo "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
                                            }
                                        }
                                    }
                                    ?>
                                </select>
                              </td>
				            </tr>
                              <tr>
                                <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1">Critério de Julgamento*</td>
                                <td>
                                    <select name="LicitacaoTipoSelecionado" class="textonormal">
										<?php
                                        	$sql = 	" SELECT cj.ccrjulcodi, cj.ecrjulnome ";
											$sql .= " FROM sfpc.tbcriteriojulgamento cj ";
											$sql .= " ORDER BY cj.ccrjulcodi desc";
											$result = $db->query($sql);
											while($Linha = $result->fetchRow()){
												echo "<option value=\"$Linha[0]\" selected>$Linha[1]</option>\n";
											}
                                    	?>
                                    </select>

                                  </td>
                              </tr>
                          </table>
				        </td>
				     	</tr>
				      <tr>
				        <td class="textonormal" align="right">
			            <input type="hidden" name="Critica" value="1">
				        <input type="button" value="Incluir" class="botao" onclick="javascript:enviar('Incluir');">
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
<script language="javascript" type="">
<!--
document.Licitacao.ComissaoCodigo.focus();
//-->
</script>