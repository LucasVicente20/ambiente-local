<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadAtasFaseManter.php
# Autor:    Rossana Lira
# Data:     23/04/03
# Objetivo: Programa de Inclusão/Exclusão das Atas/Fase de Licitação
#----------------------------
# Alterado: Ariston Cordeiro
# Data:     03/03/2011 - Enviar email ao incluir ou excluir atas.
#											 - Adicionando Observação de ata
# Alterado: Ariston Cordeiro
# Data:     26/05/2011 - Salvar emails na nova tabela de email
# Alterado: Luiz Alves
# Data:     25/08/2011 - Correção do erro de inclusão de arquivos maiores que 12mb.
# Alterado: João Batista Brito
# Data:     15/12/2011 - Correção de erro - Mensagem redirecionada para seleção de Licitação.
# Alterado: Pitang Agile TI
# Data:     29/12/2015 - Requisito 103053 - Licitação - Atas da fase - Exclusão
#--------------------------------------------------------------------------------
# Alterado: Osmar Celestino
# Data:     20/04/2023
# Objetivo: Cr 281706 
# -------------------------------------------------------------------------------
# Alterado: Osmar Celestino
# Data:     27/04/2023
# Objetivo: Cr 282313 
# -------------------------------------------------------------------------------


# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
if (count($_POST)== 0 && count($_GET) == 0){
  	$Mensagem2 .= "";
  	header("location: CadAtasFaseSelecionar.php");
  	exit();
}

//Valor booleano para verificar se o perfil logado é de um adm geral
$IS_ADM_GERAL = ($_SESSION["_cperficodi_"] == 2);

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){

		$Critica              = $_POST['Critica'];
		$Botao                = $_POST['Botao'];
		$LicitacaoProcesso    = $_POST['LicitacaoProcesso'];
		$LicitacaoAno         = $_POST['LicitacaoAno'];
		$ComissaoCodigo       = $_POST['ComissaoCodigo'];
		$ComissaoDescricao    = $_POST['ComissaoDescricao'];
		$OrgaoLicitanteCodigo = $_POST['OrgaoLicitanteCodigo'];
		$FaseCodigo           = $_POST['FaseCodigo'];
		$FaseDescricao        = $_POST['FaseDescricao'];
		$QuantArquivos        = $_POST['QuantArquivos'];
		$AtasFases            = $_POST['AtasFases'];
		$Observacao           = $_POST['Observacao'];
		$NCaracteres          = $_POST['NCaracteres'];

}else{
		$LicitacaoProcesso    = $_GET['LicitacaoProcesso'];
		$LicitacaoAno         = $_GET['LicitacaoAno'];
		$ComissaoCodigo       = $_GET['ComissaoCodigo'];
		$OrgaoLicitanteCodigo = $_GET['OrgaoLicitanteCodigo'];
		$FaseCodigo           = $_GET['FaseCodigo'];

}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "CadAtasFaseManter.php";

# licitacaoEnviaEmailsLicitantes- envia email a todos licitantes inscritos em uma licitação.
function licitacaoEnviaEmailsLicitantes($db, $LicitacaoProcesso, $LicitacaoAno, $ComissaoCodigo, $OrgaoLicitanteCodigo, $GrupoCodigo, $titulo, $corpo, $FlagConfirmaEnviarEmails, $FlagIniciouTransacao){
	if($FlagConfirmaEnviarEmails){
		if(!$FlagIniciouTransacao or is_null($FlagIniciouTransacao)){
			$db->query("BEGIN TRANSACTION");
		}
		# Pegando email de comissão
		$sql = "
			select ecomlimail
			from SFPC.TBcomissaolicitacao
			WHERE
				CGREMPCODI = ".$GrupoCodigo."
				AND CCOMLICODI = $ComissaoCodigo
		";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
			$db->query("ROLLBACK");
			EmailErroSQL("Erro de SQL", __FILE__, __LINE__, "Erro de SQL", $sql, $result);
		}
		$linha = $result->fetchRow();
		$emailComissao=$linha[0];

		# pegando listas de interessados do processo
		$sql = "
			SELECT ELISOLNOME, ELISOLMAIL, CLISOLCODI
			FROM SFPC.TBLISTASOLICITAN
			WHERE
				CLICPOPROC = $LicitacaoProcesso
				AND ALICPOANOP = $LicitacaoAno
				AND CGREMPCODI = $GrupoCodigo
				AND CCOMLICODI = $ComissaoCodigo
				AND CORGLICODI = $OrgaoLicitanteCodigo
				AND FLISOLPART = 'S'
		";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
			$db->query("ROLLBACK");
			EmailErroSQL("Erro de SQL", __FILE__, __LINE__, "Erro de SQL", $sql, $result);
		}
		# Salvando registro do Email

		$sql2 = "
			Insert into SFPC.TBlicitacaoemail (
				CGREMPCODI, CLICPOPROC, ALICPOANOP, CCOMLICODI,
				CORGLICODI, XLICEMTITL, XLICEMBODY, DLICEMULAT,
				flicemanex
			)
			VALUES (
				$GrupoCodigo, $LicitacaoProcesso, $LicitacaoAno, $ComissaoCodigo,
				$OrgaoLicitanteCodigo, '$titulo', '$corpo', NOW(), 'N'
			)
		";
		$result2 = $db->query($sql2);
		if( PEAR::isError($result2) ){
			$db->query("ROLLBACK");
			EmailErroSQL("Erro de SQL", __FILE__, __LINE__, "Erro de SQL", $sql2, $result2);
		}
		$sql2 = "
			select last_value from SFPC.TBlicitacaoemail_clicemcodi_sequ
		";
		$result2 = $db->query($sql2);
		if( PEAR::isError($result2) ){
			$db->query("ROLLBACK");
			EmailErroSQL("Erro de SQL", __FILE__, __LINE__, "Erro de SQL", $sql2, $result2);
		}
		$Linha2 = $result2->fetchRow();
		$codigoEmail = $Linha2[0];


		# enviando emails
		while( $Linha = $result->fetchRow() ){
			$nome  = $Linha[0];
			$email = $Linha[1];
			$solCodi = $Linha[2];
			$sql2 = "
				Insert into SFPC.TBlicitacaoemailsolicitante (
					CGREMPCODI, CLICPOPROC, ALICPOANOP, CCOMLICODI,
					CORGLICODI, DLEMSOULAT, CLISOLCODI, CLICEMCODI
				)
				VALUES (
					$GrupoCodigo, $LicitacaoProcesso, $LicitacaoAno, $ComissaoCodigo,
					$OrgaoLicitanteCodigo, NOW(), $solCodi, $codigoEmail
				)
		";
			$result2 = $db->query($sql2);
			if( PEAR::isError($result2) ){
				$db->query("ROLLBACK");
				EmailErroSQL("Erro de SQL", __FILE__, __LINE__, "Erro de SQL", $sql2, $result2);
			}
			EnviaEmail($email, $titulo, $corpo, $emailComissao);
		}
		if(!$FlagIniciouTransacao or is_null($FlagIniciouTransacao)){
			$db->query("BEGIN TRANSACTION");
		}
	}

}

# Redireciona para a página de excluir #
if( $Botao == "Excluir" ){

      /**
       * Verifica-se primeiro se o usuário que pediu para excluir
       * Seja do perfil administrador geral, caso positivo
       * Pergunta se é ele realmente deseja apagar FISICAMENTE
       * REDMINE - requisito - #103053
       * [RLFO]
       *
      **/


		$Mens =0;
		// if( strlen($Observacao) <1 ){
		// 		if ( $Mens == 1 ) { $Mensagem .= ", "; }
		// 		$Mens      = 1;
		// 		$Tipo      = 2;
		// 		$Mensagem .= "Campo Observação deve ser preenchido";
		// }
		// if( strlen($Observacao) > 200 ){
		// 		if ( $Mens == 1 ) { $Mensagem .= ", "; }
		// 		$Mens      = 1;
		// 		$Tipo      = 2;
		// 		$Mensagem .= "Campo Observação com até 200 Caracteres ( atualmente com ". strlen($Observacao) ." )";
		// }
		if($Mens ==0){
		  if( $QuantArquivos > 0 ){
                    $db = Conexao();


                    for( $Row = 0 ; $Row < $QuantArquivos ; $Row++ ){
                            if( $AtasFases[$Row] != "" ){
								$db->query("BEGIN TRANSACTION");
                                //Código 2 é o que se refere ao perfil ADM geral
                                //Se assim for, deve deletar fisicamente
                                if($IS_ADM_GERAL) {
                                   $sql    = "DELETE FROM SFPC.TBATASFASE ";
                                }
                                //CCaso não seja ADM geral, só deverá atualizar
                                else {
                                   $sql = "
										UPDATE SFPC.TBATASFASE
										SET
											fatasfexcl = 'S',
											eatasfobse = '$Observacao',
											cusupocodi = ".$_SESSION['_cusupocodi_'].",
											tatasfulat = now()";
                                }

                                $sql .= " WHERE CLICPOPROC =". $LicitacaoProcesso;
								$sql .=	" AND ALICPOANOP =". $LicitacaoAno;
								$sql .=	" AND CGREMPCODI =". $_SESSION['_cgrempcodi_'];
								$sql .= " AND CCOMLICODI =". $ComissaoCodigo;
								$sql .=	" AND CATASFCODI =". $AtasFases[$Row];

                                $result = $db->query($sql);
									if( PEAR::isError($result) ){
											$db->query("ROLLBACK");
											ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
									}else{
											$Mens = 1;$Tipo = 1;
											$Mensagem = "Atas(s) Excluída(s) com Sucesso";

											$db->query("COMMIT");
											$db->query("END TRANSACTION");
									}
                        }
                    }

                    #### Enviando email a todos interessados ####
					if($cntArquivosExcluidos>0){

						$strArquivos = "ARQUIVOS: \n";
						# Pegando informações de todos documentos sendo excluídos
						for( $Row = 0 ; $Row < $QuantArquivos ; $Row++ ){
								if( $Documentos[$Row] != "" ){
										$sql = "
											SELECT
												edoclinome, edocliobse
											FROM
												SFPC.TBATASFASE
											WHERE
												CLICPOPROC = $LicitacaoProcesso
												AND ALICPOANOP = $LicitacaoAno
												AND CGREMPCODI = ".$_SESSION['_cgrempcodi_']."
												AND CCOMLICODI = $ComissaoCodigo
												AND CDOCLICODI = ".$Documentos[$Row]."
										";
										$result = $db->query($sql);
										if( PEAR::isError($result) ){
											EmailErroSQL("Erro de SQL", __FILE__, __LINE__, "Erro de SQL", $sql, $result);
										}
										$Linha = $result->fetchRow();
										$arquivoNome = $Linha[0];
										$arquivoObservacao = $Linha[1];
										$strArquivos .= $arquivoNome."\n";
								}
						}
						$strArquivos .= "\nJUSTIFICATIVA:\n".$Observacao."";
						$str="Comunicamos que houve exclusão da(s) seguinte(s) ata(s) no Portal de Compras, referente ao ano ".$LicitacaoAno." e processo ".$LicitacaoProcesso.":\n\n".$strArquivos;
						licitacaoEnviaEmailsLicitantes($db, $LicitacaoProcesso, $LicitacaoAno, $ComissaoCodigo, $OrgaoLicitanteCodigo, $_SESSION['_cgrempcodi_'], "Portal de Compras- Exclusão de documento(s) em licitação", $str, TRUE, TRUE);

						$Observacao = "";
					}
					#########################################

					$db->disconnect();
				}
			}
}else if( $Botao == "Voltar" ){
	  header("location: CadAtasFaseSelecionar.php");
	  exit();
}else if( $Botao == "Incluir" ){
		# Critica dos Campos #
		if( $Critica == 1 ) {
				if( is_null($Observacao) ){
						if ( $Mens == 1 ) { $Mensagem .= ", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "Observação";
				}
				$_FILES['NomeArquivo']['name'] = RetiraAcentos($_FILES['NomeArquivo']['name']);
				
				$Tamanho = 30720000; /* 30MB*/
				if( ( $_FILES['NomeArquivo']['size'] > $Tamanho ) || ( $_FILES['NomeArquivo']['size'] == 0)){
						if ($Mens == 1){ $Mensagem .= ", "; }
						$Mens = 1; $Tipo = 2;
						$Kbytes = $Tamanho/1024000; 
						$Kbytes = (int) $Kbytes;
						$Mensagem .= "Este arquivo é muito grande ou está vazio. Tamanho Máximo: $Kbytes MB";
				}
				//$Observacao = strtoupper2(trim($Observacao));
				if( strlen($Observacao) <1 ){
						if ( $Mens == 1 ) { $Mensagem .= ", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "Campo Observação deve ser preenchido";
				}
				if( strlen($Observacao) > 200 ){
						if ( $Mens == 1 ) { $Mensagem .= ", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "Campo Observação com até 200 Caracteres ( atualmente com ". strlen($Observacao) ." )";
				}
				$Tam = strlen($_FILES['NomeArquivo']['name']);
				if( strlen($_FILES['NomeArquivo']['name']) > 100 ){
						if ( $Mens == 1 ) { $Mensagem .= ", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "Nome do Arquivo com até 100 Caracateres ( atualmente com ".strlen($_FILES['NomeArquivo']['name'])." )";
				}
				if($Mens == 0){
						$db     = Conexao();
						$sql    = "
							SELECT COUNT(CATASFCODI)
							FROM SFPC.TBATASFASE
							WHERE
								CLICPOPROC = ".$LicitacaoProcesso."
								AND ALICPOANOP = ".$LicitacaoAno."
								AND CCOMLICODI= ".$ComissaoCodigo."
								AND CGREMPCODI = ".$_SESSION['_cgrempcodi_']."
								AND EATASFNOME = '".$_FILES['NomeArquivo']['name']."'
						";
						$result = $db->query($sql);
						if( PEAR::isError($result) ){
							EmailErroSQL("Erro de SQL", __FILE__, __LINE__, "SQL falhou", $sql, $result);
						    exit(0);
						}
						$Linha = $result->fetchRow();
						$noArquivos = $Linha[0];
						if($noArquivos>0){
							//if ( $Mens == 1 ) { $Mensagem .= ", "; }
							$Mens      = 1;
							$Tipo      = 1;
							$Mensagem = "Arquivo já foi incluído";
						}
				}


				if( $Mens == 0 ){
						$db     = Conexao();
						$sql    = "SELECT MAX(CATASFCODI) FROM SFPC.TBATASFASE ";
						$sql   .= " WHERE CLICPOPROC = $LicitacaoProcesso AND ALICPOANOP = $LicitacaoAno ";
						$sql   .= "   AND CCOMLICODI= $ComissaoCodigo AND CGREMPCODI = ".$_SESSION['_cgrempcodi_']."";
						$result = $db->query($sql);
						if( PEAR::isError($result) ){
						    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								$Linha = $result->fetchRow();
								$AtasFaseCod = $Linha[0] + 1;

								$nomeArquivo = basename($_FILES['NomeArquivo']['name']);
								$extensao = substr($nomeArquivo, -4);
								$nomeArquivoNoServidor = "ATASFASE".$_SESSION['_cgrempcodi_']."_".$LicitacaoProcesso."_".$LicitacaoAno."_".$ComissaoCodigo."_".$OrgaoLicitanteCodigo."_".$FaseCodigo."_".$AtasFaseCod.$extensao;
								# Insere na tabela de Atas da Fase #
								
								$db->query("BEGIN TRANSACTION");
								$sql       = "INSERT INTO SFPC.TBATASFASE( ";
								$sql      .= "CFASESCODI, CLICPOPROC, ALICPOANOP, CGREMPCODI, ";
								$sql      .= "CCOMLICODI, CORGLICODI, CATASFCODI, EATASFNOME,  ";
								$sql      .= "TATASFDATA, CUSUPOCODI, TATASFULAT, eatasfobse, fatasfexcl, EATASFNOMS";
								$sql      .= ") VALUES ( ";
								$sql      .= "$FaseCodigo, $LicitacaoProcesso, $LicitacaoAno, ".$_SESSION['_cgrempcodi_'].", ";
								$sql      .= "$ComissaoCodigo, $OrgaoLicitanteCodigo, $AtasFaseCod, '".$_FILES['NomeArquivo']['name']."', ";
								$sql      .= "'".date("Y-m-d")."',".$_SESSION['_cusupocodi_'].", '".date("Y-m-d H:i:s")."', '".$Observacao."', 'N','".$nomeArquivoNoServidor."')";
								$result   = $db->query($sql);
								if( PEAR::isError($result) ){
										$db->query("ROLLBACK");
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										
										$ArquivoDestino = $GLOBALS["CAMINHO_UPLOADS"]."licitacoes2/".$nomeArquivoNoServidor;
										$Arquivo = $GLOBALS["CAMINHO_UPLOADS"]."licitacoes/ATASFASE".$_SESSION['_cgrempcodi_']."_".$LicitacaoProcesso."_".$LicitacaoAno."_".$ComissaoCodigo."_".$OrgaoLicitanteCodigo."_".$FaseCodigo."_".$AtasFaseCod;
										$tempName = $_FILES['NomeArquivo']['tmp_name'];


										if( file_exists($ArquivoDestino) ){ unlink ($ArquivoDestino);}
										if( file_exists($Arquivo) ){ unlink ($Arquivo);}

										if(copy($tempName, $Arquivo)) {
												$Mens              = 1;
												$Tipo              = 1;
												$Mensagem          = "Ata Carregada com Sucesso";

												$str ="Comunicamos que houve inclusão da seguinte ata no Portal de Compras, referente ao ano ".$LicitacaoAno." e processo ".$LicitacaoProcesso.":\n\nARQUIVO:\n".$_FILES['NomeArquivo']['name']."\n\nOBSERVAÇÃO:\n".$Observacao;
												licitacaoEnviaEmailsLicitantes($db, $LicitacaoProcesso, $LicitacaoAno, $ComissaoCodigo, $OrgaoLicitanteCodigo, $_SESSION['_cgrempcodi_'], "Portal de Compras- Inclusão de documento em licitação", $str, TRUE, TRUE);

												$db->query("COMMIT");
												$db->query("END TRANSACTION");

												$Observacao = "";


										}else{
												$Mens     = 1;
												$Tipo     = 2;
												$Mensagem = "Erro no Carregamento do Arquivo";
												$db->query("ROLLBACK");
										}



										if(@move_uploaded_file($tempName, $ArquivoDestino)) {
											$Mens = 1;
											$Tipo = 1;
											$Mensagem = "Ata Carregada com Sucesso";
											
											$Observacao = "";

										} else {
											$Mens = 1;
											$Tipo = 2;
											$Mensagem = "Erro no Carregamento do Arquivo";
											$db->query("ROLLBACK");
										}

								}
						}
						$db->disconnect();
				}
		}
}

# Busca descrição da comissão #
$db     = Conexao();
$sql    = "SELECT A.ECOMLIDESC FROM SFPC.TBCOMISSAOLICITACAO A WHERE A.CCOMLICODI = $ComissaoCodigo";
$result = $db->query($sql);
if( PEAR::isError($result) ){
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}else{
		$Linha = $result->fetchRow();
		$ComissaoDescricao = $Linha[0];
}


# Busca descrição da Fase #
$sql    = "SELECT A.EFASESDESC FROM SFPC.TBFASES A WHERE A.CFASESCODI = $FaseCodigo";
$result = $db->query($sql);
if( PEAR::isError($result) ){
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}else{
		$Linha = $result->fetchRow();
		$FaseDescricao = $Linha[0];
}
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">

function enviar(valor){

	if (valor == "Excluir") {
	<?php
		if ($IS_ADM_GERAL) {
    ?>
       var resultado = window.confirm("Este procedimento excluirá permanentemente esta(s) ata(s). Deseja Continuar ?");
       	if(!resultado){
			return;
       	}
      <?php
       }
       ?>
	}
    document.AtasFase.Botao.value=valor;
	document.AtasFase.submit();
}
function ncaracteres(valor){
	document.AtasFase.NCaracteres.value = '' +  document.AtasFase.Observacao.value.length;
	/*if( navigator.appName == 'Netscape' && valor ) {  //Netscape Only
		document.AtasFase.NCaracteres.focus();
	}*/
}

<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form enctype="multipart/form-data" action="CadAtasFaseManter.php" method="post" name="AtasFase">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Atas da Fase 	</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
  <tr>
  	<td width="150"></td>
		<td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="150"></td>
		<td class="textonormal"><br>
			<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
        <tr>
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	        	MANTER - ATAS DAS FASES DE LICITAÇÃO
          </td>
        </tr>
        <tr>
          <td class="textonormal">
		         <p align="justify">
		         Para incluir a Ata, localize o arquivo e clique no botão "Incluir". Para apagar a(s) Atas(s), selecione-a(s) e clique no botão "Excluir".
		         </p>
          </td>
        </tr>
        <tr>
          <td>
            <table border="0" summary="" style="width:100%;">
              <tr>
	              <td class="textonormal" bgcolor="#DCEDF7" height="20">Comissão </td>
	              <td class="textonormal"><?php echo $ComissaoDescricao; ?></td>
	            </tr>
 							<tr>
	              <td class="textonormal" bgcolor="#DCEDF7" height="20">Processo </td>
	              <td class="textonormal"><?php echo substr($LicitacaoProcesso + 10000,1); ?></td>
	            </tr>
	            <tr>
	              <td class="textonormal" bgcolor="#DCEDF7" height="20">Ano </td>
	              <td class="textonormal"><?php echo $LicitacaoAno; ?></td>
	            </tr>
							<tr>
	              <td class="textonormal" bgcolor="#DCEDF7" height="20">Fase </td>
	              <td class="textonormal"><?php echo $FaseDescricao; ?></td>
	            </tr>
							<tr>
	              <td class="textonormal" bgcolor="#DCEDF7" height="20">Arquivo* </td>
								<td class="textonormal">
									<input type="file" name="NomeArquivo" class="textonormal" >
									<input type="hidden" name="LicitacaoProcesso" value="<?php echo $LicitacaoProcesso; ?>">
									<input type="hidden" name="LicitacaoAno" value="<?php echo $LicitacaoAno; ?>">
									<input type="hidden" name="ComissaoCodigo" value="<?php echo $ComissaoCodigo; ?>">
									<input type="hidden" name="OrgaoLicitanteCodigo" value="<?php echo $OrgaoLicitanteCodigo; ?>">
									<input type="hidden" name="FaseCodigo" value="<?php echo $FaseCodigo; ?>">
									<input type="hidden" name="FaseDescricao" value="<?php echo $FaseDescricao; ?>">
					                <input type="hidden" name="ComissaoDescricao" value="<?php echo $ComissaoDescricao; ?>">
									<input type="hidden" name="Critica" value="1">
								</td>
	            </tr>
							<tr>
	              <td class="textonormal" bgcolor="#DCEDF7" valign="top">Observação<br/>da inclusão ou<br/>Justificativa<br/>da exclusão*</td>
	              <td class="textonormal">
	                máximo de 200 caracteres
									<input type="text" name="NCaracteres" disabled size="3" value="<?php echo $NCaracteres; ?>" class="textonormal"><br>
	              	<textarea name="Observacao" cols="40" rows="5" OnKeyUp="javascript:ncaracteres(1)" OnBlur="javascript:ncaracteres(0)" OnSelect="javascript:ncaracteres(1)" class="textonormal"><?php echo $Observacao;?></textarea>
	              </td>
	            </tr>
							<tr>
	              <td class="textonormal" bgcolor="#DCEDF7" valign="top"> Atas Cadastradas </td>
									<td class="textonormal">
									<?php
									$sql    = "SELECT CATASFCODI, EATASFNOME, TATASFDATA, eatasfobse, fatasfexcl, u.eusuporesp";
									$sql   .= "  FROM SFPC.TBATASFASE a, sfpc.tbusuarioportal u ";
									$sql   .= " WHERE CLICPOPROC = $LicitacaoProcesso AND ALICPOANOP = $LicitacaoAno ";
									$sql   .= "   AND CCOMLICODI = $ComissaoCodigo AND a.CGREMPCODI = ".$_SESSION['_cgrempcodi_']." ";
									$sql   .= "   AND CFASESCODI = $FaseCodigo AND a.cusupocodi = u.cusupocodi";
									$result = $db->query($sql);
									if( PEAR::isError($result) ){
									    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
									}else{
											$Rows = $result->numRows();
											if( $Rows > 0 ){
														?>
											<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" style="width:100%;  border:1px;">
												<tr>
													<td valign="top" bgcolor="#F7F7F7" class="textonegrito">&nbsp;</td>
													<td valign="top" bgcolor="#F7F7F7" class="textonegrito">ATA</td>
													<td valign="top" bgcolor="#F7F7F7" class="textonegrito">RESPONSÁVEL</td>
													<td valign="top" bgcolor="#F7F7F7" class="textonegrito">DATA<br/>CRIAÇÃO</td>
													<td valign="top" bgcolor="#F7F7F7" class="textonegrito">OBSERVAÇÃO/<br/>JUSTIFICATIVA</td>
												</tr>

														<?php
													while( $Linha = $result->fetchRow() ){
															$cont++;
															$row  = $cont-1;
															$Data = substr($Linha[2],8,2)."/".substr($Linha[2],5,2)."/".substr($Linha[2],0,4);
															$itemObservacao = $Linha[3];
															$itemCodigo = $Linha[0];
															$itemNome = $Linha[1];
															$itemExcluido = $Linha[4];
															$itemAutor = $Linha[5];
															if($itemExcluido == "S"){
																$itemNome="<s style='text-decoration:line-through;'>".$itemNome."</s> (excluído)";
															}

															?>
															<tr>
																<td class="textonormal"><input type=checkbox name="AtasFases[<?php $Row;?>]" value="<?php echo $itemCodigo;?>"/></td>
																<td class="textonormal" bgcolor="#F7F7F7"><?php echo $itemNome;?>&nbsp;</td>
																<td class="textonormal" bgcolor="#F7F7F7"><?php echo $itemAutor;?>&nbsp;</td>
																<td class="textonormal" bgcolor="#F7F7F7"><?php echo $Data;?>&nbsp;</td>
																<td class="textonormal" bgcolor="#F7F7F7"><?php echo $itemObservacao;?>&nbsp;</td>
															</tr>

															<?php
													}
													?></table><?php

											}else{
													echo "Nenhum Ata Cadastrada!";
											}
									}
									$db->disconnect();
									?>

								</td>
	            </tr>
            </table>
            <input type="hidden" name="QuantArquivos" value="<?php echo $Rows?>">
            </td>
        </tr>
        <tr>
 	        <td class="textonormal" align="right">
            <input type="button" value="Incluir" class="botao" onclick="javascript:enviar('Incluir');">
						<input type="button" value="Excluir" class="botao" onclick="javascript:enviar('Excluir');">
            <input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
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
