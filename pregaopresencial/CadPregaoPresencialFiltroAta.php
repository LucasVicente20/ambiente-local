<?php
#----------------------------------------------------------------------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadPregaoPresencialFiltroAta.php
# Autor:    Hélio Miranda
# Data:     06/02/2017
# Objetivo: Programa de Seleção de Proceso Licitatório (Pregão Presencial)
#----------------------------------------------------------------------------------------------------------------------------------------------------
# Autor:    Lucas Baracho
# Data:     16/11/2017
# Objetivo: Correção da função de modificar informações adicionais na ata
#----------------------------------------------------------------------------------------------------------------------------------------------------
# Autor:    Lucas Baracho
# Data:     [em andamento]
# Objetivo: Tarefa Redmine 179266 - Criar campo para verificação e alteração da data e hora de início da sessão
#----------------------------------------------------------------------------------------------------------------------------------------------------

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
		$_SESSION['Botao']				   = $_POST['Botao'];
		$NCaracteresX        			   = $_POST['NCaracteresX'];
		$NCaracteresO        			   = $_POST['NCaracteresO'];
		$NCaracteresB        			   = $_POST['NCaracteresB'];
		$NCaracteresC        			   = $_POST['NCaracteresC'];
		$NCaracteresD        			   = $_POST['NCaracteresD'];   
}else{
		$Critica                           = $_GET['Critica'];
		$Mensagem                          = $_GET['Mensagem'];
		$Mens                              = $_GET['Mens'];
		$Tipo                              = $_GET['Tipo'];
}

$_SESSION['DemaisParticipantes']  = null;
$_SESSION['ParagrafoAdicionalA']  = null;
$_SESSION['ParagrafoAdicionalB']  = null;
$_SESSION['ParagrafoAdicionalC']  = null;
$_SESSION['ParagrafoAdicionalD']  = null;	

$TamanhoMaximoParagrafos  = 4999;

$Processo             = $_SESSION['Processo'];
$ProcessoAno          = $_SESSION['ProcessoAno'];
$ComissaoCodigo       = $_SESSION['ComissaoCodigo'];
$OrgaoLicitanteCodigo = $_SESSION['OrgaoLicitanteCodigo'];
$PregaoCod			  = $_SESSION['PregaoCod'];

$db = Conexao();

$sql  = "SELECT LIC.CCOMLICODI,
				COM.ECOMLIDESC,
				LIC.CLICPOPROC,
				LIC.ALICPOANOP,
				LIC.CMODLICODI,
				MOD.EMODLIDESC, 
				LIC.FLICPOREGP,
				LIC.CLICPOCODL, 
				LIC.ALICPOANOL, 
				LIC.XLICPOOBJE, 
				LIC.CORGLICODI, 
				LIC.flicpovfor
		 FROM SFPC.TBLICITACAOPORTAL LIC
			INNER JOIN SFPC.TBCOMISSAOLICITACAO COM ON LIC.CCOMLICODI = COM.CCOMLICODI
			INNER JOIN SFPC.TBMODALIDADELICITACAO MOD ON LIC.CMODLICODI = MOD.CMODLICODI
         WHERE LIC.CLICPOPROC = $Processo 
			AND LIC.ALICPOANOP = $ProcessoAno
			AND LIC.CCOMLICODI = $ComissaoCodigo 
			AND LIC.corglicodi = $OrgaoLicitanteCodigo
		 ORDER BY LIC.CCOMLICODI ASC"; 

$res = $db->query($sql);

                                                     
if ( PEAR::isError($res) ) {
  $CodErroEmail  = $res->getCode();
  $DescErroEmail = $res->getMessage();
  var_export($DescErroEmail);
  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
} else {
  $Linha = $res->fetchRow();
  
  $_SESSION['CodigoComissao'] 		= $Linha[0];
  $_SESSION['NomeComissao'] 		= $Linha[1];
  $_SESSION['NumeroDoProcesso'] 	= $Linha[2];
  $_SESSION['AnoDoExercicio'] 		= $Linha[3];
  $_SESSION['Modalidade'] 			= $Linha[5]; 
  $_SESSION['RegistroPreco'] 		= $Linha[6];
  $_SESSION['Licitação'] 			= $Linha[7];
  $_SESSION['AnoLicitação'] 		= $Linha[8];
  $_SESSION['Objeto'] 				= $Linha[9];
}

// $SqlDataHora  = "SELECT PGP.TPREGAABER
				 // FROM SFPC.TBPREGAOPRESENCIAL PGP
				 // WHERE PGP.CLICPOPROC = $Processo AND PGP.ALICPOANOP = $ProcessoAno
					// AND PGP.CCOMLICODI = $ComissaoCodigo AND PGP.CORGLICODI = $OrgaoLicitanteCodigo"; 

// $ResDataHora = $db->query($SqlDataHora);

                                                     
// if ( PEAR::isError($ResDataHora) ) {
  // $CodErroEmail  = $ResDataHora->getCode();
  // $DescErroEmail = $ResDataHora->getMessage();
  // var_export($DescErroEmail);
  // ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $SqlDataHora\n\n$DescErroEmail ($CodErroEmail)");
// } else {
  // $Linha = $ResDataHora->fetchRow();
  
  // $_SESSION['DataHoraAbertura'] = $Linha[0];
// }

$SQLTotalLotes = "SELECT COUNT(lt.cpregtsequ)
				  FROM sfpc.tbpregaopresenciallote lt  
				  WHERE lt.cpreslsequ > 1
					AND lt.cpregasequ = $PregaoCod";						

				
$ResultTotalLotes = $db->query($SQLTotalLotes);
if( PEAR::isError($ResultTotalLotes) )
{
	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $SQLTotalLotes");
}	
else
{
	$LinhaTotalLotes 					= $ResultTotalLotes->fetchRow();
	$_SESSION['TotalLotes']  			= $LinhaTotalLotes[0];
	
	if($_SESSION['LoteInicialIntervalo'] == 0)
	{		
	$_SESSION['LoteInicialIntervalo'] = 1;
	}
	
	if($_SESSION['LoteFinalIntervalo'] == 0)
	{		
		$_SESSION['LoteFinalIntervalo']	= $_SESSION['TotalLotes'];
	}
}	
 
$SQLAta = "SELECT pa.epreatpara,
				  pa.epreatparb,
				  pa.epreatparc,
				  pa.epreatpard,
				  pa.epreatdpar
		   FROM sfpc.tbpregaopresencialata pa  
		   WHERE pa.cpregasequ = $PregaoCod";

$ResultAta = $db->query($SQLAta);
$LinhaAta = $ResultAta->fetchRow();
$_SESSION['DemaisParticipantes']  = (empty ($_POST['DemaisParticipantes']) ? '' : strtoupper( $_POST['DemaisParticipantes']));
$_SESSION['ParagrafoAdicionalA']  = (empty ($_POST['ParagrafoAdicionalA']) ? '' : strtoupper( $_POST['ParagrafoAdicionalA']));
$_SESSION['ParagrafoAdicionalB']  = (empty ($_POST['ParagrafoAdicionalB']) ? '' : strtoupper( $_POST['ParagrafoAdicionalB']));
$_SESSION['ParagrafoAdicionalC']  = (empty ($_POST['ParagrafoAdicionalC']) ? '' : strtoupper( $_POST['ParagrafoAdicionalC']));
$_SESSION['ParagrafoAdicionalD']  = (empty ($_POST['ParagrafoAdicionalD']) ? '' : strtoupper( $_POST['ParagrafoAdicionalD']));


$db->disconnect();

if( $_SESSION['Botao'] == "Voltar" ){
		$_SESSION['Botao'] = "";
		$Url = "CadPregaoPresencialSelecionarFiltroAta.php";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit;
}

if($_SESSION['Botao'] == "CamposTipoAta")
{
	$_SESSION['Botao'] 			= null;
	$_SESSION['TipoAta']  		= $_POST['TipoAta'];
	$TipoAta					= $_SESSION['TipoAta'];
	$_SESSION['IntervaloLotes'] = False;
	
	if($TipoAta > 1)
	{
		$_SESSION['IntervaloLotes'] = True; 
	}
}

if($_SESSION['Botao'] == "GerarAta")
{
	$_SESSION['Botao'] 					= null;
	$TipoAta							= $_POST['TipoAta'];
	$Recurso							= $_POST['Recurso'];
	$TipoAtaGeracao						= $_POST['TipoAtaGeracao'];
	$TempoDeToleranciaDeserta			= $_POST['TempoDeToleranciaDeserta'];
	$_SESSION['LoteInicialIntervalo']	= $_POST['LoteInicialIntervalo'];
	$_SESSION['LoteFinalIntervalo']		= $_POST['LoteFinalIntervalo'];
	$NomeEquipe							= $_POST['nome_equipe'];
	// $DataHoraAbertura					= $_POST['DataHoraAbertura'];
	
	if($TempoDeToleranciaDeserta == "" or $TempoDeToleranciaDeserta == null)
	{
		$TempoDeToleranciaDeserta = 0;
	}		
	
	$PreenchimentoCorreto 	= True;

	$_SESSION['DemaisParticipantes']  = null;
	$_SESSION['ParagrafoAdicionalA']  = null;
	$_SESSION['ParagrafoAdicionalB']  = null;
	$_SESSION['ParagrafoAdicionalC']  = null;
	$_SESSION['ParagrafoAdicionalD']  = null;

	$_SESSION['OrgaoLicitante']  			= null;
	$_SESSION['EnderecoOrgaoLicitante'] 	= null;	
	
	$_SESSION['DemaisParticipantes']  = (empty ($_POST['DemaisParticipantes']) ? '' : strtoupper( $_POST['DemaisParticipantes']));
	$_SESSION['ParagrafoAdicionalA']  = (empty ($_POST['ParagrafoAdicionalA']) ? '' : strtoupper( $_POST['ParagrafoAdicionalA']));
	$_SESSION['ParagrafoAdicionalB']  = (empty ($_POST['ParagrafoAdicionalB']) ? '' : strtoupper( $_POST['ParagrafoAdicionalB']));
	$_SESSION['ParagrafoAdicionalC']  = (empty ($_POST['ParagrafoAdicionalC']) ? '' : strtoupper( $_POST['ParagrafoAdicionalC']));
	$_SESSION['ParagrafoAdicionalD']  = (empty ($_POST['ParagrafoAdicionalD']) ? '' : strtoupper( $_POST['ParagrafoAdicionalD']));
	
	$_SESSION['OrgaoLicitante']  			= $_POST['OrgaoLicitante'];
	$_SESSION['EnderecoOrgaoLicitante']  	= $_POST['EnderecoOrgaoLicitante'];	
	
	$DemaisParticipantes = $_SESSION['DemaisParticipantes'];
	$ParagrafoAdicionalA = $_SESSION['ParagrafoAdicionalA'];
	$ParagrafoAdicionalB = $_SESSION['ParagrafoAdicionalB'];
	$ParagrafoAdicionalC = $_SESSION['ParagrafoAdicionalC'];
	$ParagrafoAdicionalD = $_SESSION['ParagrafoAdicionalD'];
	
	$OrgaoLicitante 		= $_SESSION['OrgaoLicitante'];
	$EnderecoOrgaoLicitante = $_SESSION['EnderecoOrgaoLicitante'];	

	$UsuarioCodigo 	= $_SESSION['_cusupocodi_'];
	$PregaoCod		= $_SESSION['PregaoCod'];
	
	
	if($_SESSION['deserta'] == "N")
	{
		if($_SESSION['IntervaloLotes'] == True)
		{
			$IntervaloLotes = $_SESSION['IntervaloLotes'];
			
			if($_SESSION['LoteInicialIntervalo'] <= 0)
			{
				$_SESSION['Mens'] = 1;
				$_SESSION['Tipo'] = 2;
				$_SESSION['Mensagem'] .= "- O Lote inicial do intervalo não deve ser MENOR ou IGUAL a zero! <br />";

				$PreenchimentoCorreto 	= False;		
			}

			if($_SESSION['LoteFinalIntervalo']	> $_SESSION['TotalLotes'] or $_SESSION['LoteFinalIntervalo'] < $_SESSION['LoteInicialIntervalo'])
			{
				$_SESSION['Mens'] = 1;
				$_SESSION['Tipo'] = 2;
				
				if($_SESSION['LoteFinalIntervalo']	> $_SESSION['TotalLotes'])
				{
					$_SESSION['Mensagem'] .= "- O Lote final do intervalo não deve ser MAIOR que o total de Lotes do Pregão! <br />";
				}
				else if ($_SESSION['LoteFinalIntervalo'] < $_SESSION['LoteInicialIntervalo'])
				{
					$_SESSION['Mensagem'] .= "- O Lote final do intervalo não deve ser MENOR que o Lote inicial do intervalo! <br />";
				}

				$PreenchimentoCorreto 	= False;		
			}
		}		
		
		if($TipoAta == 0)
		{
			$_SESSION['Mens'] = 1;
			$_SESSION['Tipo'] = 2;
			$_SESSION['Mensagem'] .= "- Deve-se informar o TIPO DE ATA! <br />";

			$PreenchimentoCorreto 	= False;		
		}
		
		if($Recurso == "")
		{
			$_SESSION['Mens'] = 1;
			$_SESSION['Tipo'] = 2;
			$_SESSION['Mensagem'] .= "- Deve-se informar se há RECURSO! <br />";

			$PreenchimentoCorreto 	= False;		
		}

		if($TipoAtaGeracao == 0)
		{
			$_SESSION['Mens'] = 1;
			$_SESSION['Tipo'] = 2;
			$_SESSION['Mensagem'] .= "- Deve-se informar qual tipo de DOCUMENTOS deseja gerar! <br />";	

			$PreenchimentoCorreto 	= False;		
		}
	}
	if($_SESSION['deserta'] == "D")
	{
		if($TempoDeToleranciaDeserta <= 0)
		{
			$_SESSION['Mens'] = 1;
			$_SESSION['Tipo'] = 2;
			$_SESSION['Mensagem'] .= "- Deve-se informar o TEMPO DE TOLERÂNCIA para Pregões Presenciais com Situação igual a DESERTA! <br />";	

			$PreenchimentoCorreto 	= False;		
		}
		
		$TipoAta = 5;
	}

    if($PreenchimentoCorreto == True or $TipoAtaGeracao > 2)
	{
		if($DemaisParticipantes != "" or $ParagrafoAdicionalA != "" or $ParagrafoAdicionalB != "" or $ParagrafoAdicionalC != "" or $ParagrafoAdicionalD != "" or $OrgaoLicitante != "" or $EnderecoOrgaoLicitante != "" or $TempoDeToleranciaDeserta > 0)
		{
            //Altera parágrafos no Banco de Dados!
            $db     = Conexao();
            $sql    = "SELECT cpreatsequ FROM sfpc.tbpregaopresencialata WHERE cpregasequ = $PregaoCod";
            $res    = $db->query($sql);

            if (PEAR::isError($res)) {
                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
            } else {
                    $Linha  			= $res->fetchRow();
                    $CodigoExistente 	= $Linha[0];
            }

            if($CodigoExistente > 0) {
                $sql = "UPDATE sfpc.tbpregaopresencialata 
                        SET epreatpara = '$ParagrafoAdicionalA',
                            epreatparb = '$ParagrafoAdicionalB',
                            epreatparc = '$ParagrafoAdicionalC',
                            epreatpard = '$ParagrafoAdicionalD',
                            epreatorgl = '$OrgaoLicitante',
                            epreatendo = '$EnderecoOrgaoLicitante',
                            npreattemd = $TempoDeToleranciaDeserta,
                            epreatdpar = '$DemaisParticipantes'
                        WHERE cpreatsequ = $CodigoExistente";
                $res = $db->query($sql);
                if (PEAR::isError($res)) {
                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                }
                $_SESSION['Mens'] = 1;
                $_SESSION['Tipo'] = 1;
                $_SESSION['Mensagem'] .= "- Dados atualizados com sucesso!";
            } else {
                //Insere parágrafos no Banco de Dados!
                $db = Conexao();
                $sql = "SELECT cpreatsequ FROM sfpc.tbpregaopresencialata WHERE cpregasequ = $PregaoCod";
                $res = $db->query($sql);

                if (PEAR::isError($res)) {
                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                } else {
                    $Linha  			= $res->fetchRow();
                    $CodigoExistente 	= $Linha[0];
                }

                if($CodigoExistente == 0 or $CodigoExistente == null) {
                    $sql = "SELECT MAX(cpreatsequ) FROM sfpc.tbpregaopresencialata";
                    $res = $db->query($sql);

                    if (PEAR::isError($res)) {
                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                    } else {
                        $Linha  = $res->fetchRow();
                        $Codigo = $Linha[0] + 1;
                    }

                    # Insere Membro de Comissão #
                    $sql  = "INSERT INTO SFPC.TBPREGAOPRESENCIALATA (CPREATSEQU, CUSUPOCODI, CPREGASEQU, EPREATPARA, EPREATPARB, EPREATPARC, EPREATPARD, EPREATORGL, EPREATENDO, EPREATDPAR, NPREATTEMD, DPREATCADA, TPREATULAT) VALUES ( ";
                    $sql .= "$Codigo, $UsuarioCodigo, $PregaoCod, '".$ParagrafoAdicionalA."', '".$ParagrafoAdicionalB."', '".$ParagrafoAdicionalC."', '".$ParagrafoAdicionalD."', '".$OrgaoLicitante."', '".$EnderecoOrgaoLicitante."', '".$DemaisParticipantes."', $TempoDeToleranciaDeserta, ";
                    $sql .= "'".date("Y-m-d")."', ";
                    $sql .= "'".date("Y-m-d H:i:s")."' )";

                    $res  = $db->query($sql);


                    if( PEAR::isError($res) ){
                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                    }

                    $_SESSION['Mens'] = 1;
                    $_SESSION['Tipo'] = 1;
                    $_SESSION['Mensagem'] .= "- Parágrafo(s) incluído(s) com sucesso!";
                }
            }
        } else {
            //Insere parágrafos no Banco de Dados!
            $db = Conexao();
            $sql = "SELECT cpreatsequ FROM sfpc.tbpregaopresencialata WHERE cpregasequ = $PregaoCod";
            $res = $db->query($sql);

            if (PEAR::isError($res)) {
                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
            } else {
                    $Linha  			= $res->fetchRow();
                    $CodigoExistente 	= $Linha[0];
            }

            if($CodigoExistente == 0 or $CodigoExistente == null) {
                $sql = "SELECT MAX(cpreatsequ) FROM sfpc.tbpregaopresencialata";
                $res = $db->query($sql);

                if (PEAR::isError($res)) {
                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                } else {
                        $Linha  = $res->fetchRow();
                        $Codigo = $Linha[0] + 1;
                }

                # Insere Membro de Comissão #
                $sql  = "INSERT INTO SFPC.TBPREGAOPRESENCIALATA (CPREATSEQU, CUSUPOCODI, CPREGASEQU, EPREATPARA, EPREATPARB, EPREATPARC, EPREATPARD, EPREATORGL, EPREATENDO, EPREATDPAR, NPREATTEMD, DPREATCADA, TPREATULAT) VALUES ( ";
                $sql .= "$Codigo, $UsuarioCodigo, $PregaoCod, '".$ParagrafoAdicionalA."', '".$ParagrafoAdicionalB."', '".$ParagrafoAdicionalC."', '".$ParagrafoAdicionalD."', '".$OrgaoLicitante."', '".$EnderecoOrgaoLicitante."', '".$DemaisParticipantes."', $TempoDeToleranciaDeserta, ";
                $sql .= "'".date("Y-m-d")."', ";
                $sql .= "'".date("Y-m-d H:i:s")."' )";

                $res  = $db->query($sql);


                if( PEAR::isError($res) ){
                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                }

                $_SESSION['Mens'] = 1;
                $_SESSION['Tipo'] = 1;
                $_SESSION['Mensagem'] .= "- Parágrafo(s) incluído(s) com sucesso!";
            }
		} 
		
		$db->disconnect();
		
		if($TipoAtaGeracao > 2 or $PreenchimentoCorreto == True)
		{	
			# Redireciona para a página de Impressão #
			if($TipoAtaGeracao == 2)
			{
				$Url = "RelPregaoPresencialAtaPdf.php?Processo=$Processo&Ano=$ProcessoAno&Comissao=$ComissaoCodigo&Orgao=$OrgaoLicitanteCodigo&Recurso=$Recurso&TipoAta=$TipoAta&IntervaloLotes=$IntervaloLotes&NomeEquipe=$NomeEquipe";
					if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				exit;				
			}			
			if($TipoAtaGeracao == 3)
			{
				$Url = "RelPregaoPresencialAnexoIPdf.php?Processo=$Processo&Ano=$ProcessoAno&Comissao=$ComissaoCodigo&Orgao=$OrgaoLicitanteCodigo&IntervaloLotes=$IntervaloLotes&NomeEquipe=$NomeEquipe";
					if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				exit;
			}
			if($TipoAtaGeracao == 4)
			{
				$Url = "RelPregaoPresencialAnexoIIPdf.php?Processo=$Processo&Ano=$ProcessoAno&Comissao=$ComissaoCodigo&Orgao=$OrgaoLicitanteCodigo&IntervaloLotes=$IntervaloLotes&NomeEquipe=$NomeEquipe";
					if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				exit;				
			}
			if($TipoAtaGeracao == 5)
			{
				$Url = "RelPregaoPresencialAnexoIIIPdf.php?Processo=$Processo&Ano=$ProcessoAno&Comissao=$ComissaoCodigo&Orgao=$OrgaoLicitanteCodigo&IntervaloLotes=$IntervaloLotes&NomeEquipe=$NomeEquipe";
					if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				exit;				
			}
			if($TipoAtaGeracao == 6)
			{
				$Url = "RelPregaoPresencialAnexoIVPdf.php?Processo=$Processo&Ano=$ProcessoAno&Comissao=$ComissaoCodigo&Orgao=$OrgaoLicitanteCodigo&IntervaloLotes=$IntervaloLotes&NomeEquipe=$NomeEquipe";
					if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				exit;				
			}
		}			
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
function enviar(valor){
	document.CadPregaoPresencialFiltroAta.Botao.value = valor;
	document.CadPregaoPresencialFiltroAta.submit();
}

function enviarDestino(valor, Destino){
	document.CadPregaoPresencialSessaoPublica.Destino.value = Destino;
	document.CadPregaoPresencialSessaoPublica.Botao.value = valor;
	document.CadPregaoPresencialSessaoPublica.submit();
}

function ncaracteresX(valor){
	document.CadPregaoPresencialFiltroAta.NCaracteresX.value = '' +  (document.CadPregaoPresencialFiltroAta.TamanhoMaximoParagrafos.value - document.CadPregaoPresencialFiltroAta.DemaisParticipantes.value.length);
	if( navigator.appName == 'Netscape' && valor ){ //Netscape Only
		document.CadPregaoPresencialFiltroAta.NCaracteresX.focus();
	}
}

function ncaracteresO(valor){
	document.CadPregaoPresencialFiltroAta.NCaracteresO.value = '' +  (document.CadPregaoPresencialFiltroAta.TamanhoMaximoParagrafos.value - document.CadPregaoPresencialFiltroAta.ParagrafoAdicionalA.value.length);
	if( navigator.appName == 'Netscape' && valor ){ //Netscape Only
		document.CadPregaoPresencialFiltroAta.NCaracteresO.focus();
	}
}

function ncaracteresB(valor){
	document.CadPregaoPresencialFiltroAta.NCaracteresB.value = '' +  (document.CadPregaoPresencialFiltroAta.TamanhoMaximoParagrafos.value - document.CadPregaoPresencialFiltroAta.ParagrafoAdicionalB.value.length);
	if( navigator.appName == 'Netscape' && valor ){ //Netscape Only
		document.CadPregaoPresencialFiltroAta.NCaracteresB.focus();
	}
}

function ncaracteresC(valor){
	document.CadPregaoPresencialFiltroAta.NCaracteresC.value = '' +  (document.CadPregaoPresencialFiltroAta.TamanhoMaximoParagrafos.value - document.CadPregaoPresencialFiltroAta.ParagrafoAdicionalC.value.length);
	if( navigator.appName == 'Netscape' && valor ){ //Netscape Only
		document.CadPregaoPresencialFiltroAta.NCaracteresC.focus();
	}
}

function ncaracteresD(valor){
	document.CadPregaoPresencialFiltroAta.NCaracteresD.value = '' +  (document.CadPregaoPresencialFiltroAta.TamanhoMaximoParagrafos.value - document.CadPregaoPresencialFiltroAta.ParagrafoAdicionalD.value.length);
	if( navigator.appName == 'Netscape' && valor ){ //Netscape Only
		document.CadPregaoPresencialFiltroAta.NCaracteresD.focus();
	}
}

</script>
<link rel="stylesheet" type="text/css" href="../estilo.css"> 
<body background="../midia/bg.gif" bgcolor="#FFFFFF" text="#000000" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadPregaoPresencialFiltroAta.php" method="post" name="CadPregaoPresencialFiltroAta">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Pregão Presencial > Relatórios > Ata Padrão
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
				   FILTROS PARA GERAÇÃO DE ATA - PREGÃO PRESENCIAL
			  </td>
			</tr>
			
			<tr>
			  <td class="textonormal" bgcolor="#FFFFFF">
				 <p align="justify">
				 Preencha corretamente os filtros para geração de Ata do Pregão Presencial, após o correto preenchimento e clique no botão "Avançar".
				 </p>
			  </td>
			</tr>
			
			<tr>
				<td>
					<table border="0" width="100%" summary="">
					  <tr>
						<td width="20%" align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Comissão:</td>
						<td align="left" class="textonormal" colspan="3" >
						  <label style="width:500px;"><?php echo $_SESSION['NomeComissao'];?></label>
						  <input type="hidden" name="CodigoDaComissao" value="<?php echo $_SESSION['CodigoComissao'];?>" />
						</td>
					  </tr>
					  <tr>
						<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Processo:</td>
						<td align="left" class="textonormal" colspan="3" >
						  <label><?php echo substr($_SESSION['NumeroDoProcesso'] + 10000,1); ?></label>
						  <input type="hidden" name="NumeroDoProcesso" value="<?php echo substr($_SESSION['NumeroDoProcesso'] + 10000,1);?>" />
						</td>
					  </tr>
					  <tr>
						<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Ano:</td>
						<td align="left" class="textonormal" colspan="3" >
						  <label><?php echo $_SESSION['AnoDoExercicio']; ?></label>
						  <input type="hidden" name="AnoDoExercicio" value="<?php echo $_SESSION['AnoDoExercicio'];?>" />
						</td>
					  </tr>
					  <tr>
						<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Modalidade:</td>
						<td align="left" class="textonormal" colspan="3" >
						  <label><?php echo $_SESSION['Modalidade']; ?></label>
						</td>
					  </tr>
					  <tr>
						<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Registro de Preço:</td>
						<td align="left" class="textonormal" colspan="3" >
						  <input type="hidden" id="registroPreco" name="registroPreco" value="<?php echo $_SESSION['RegistroPreco'];?>"/>
						  <label>
						  <?php
							if ($RegistroPreco) {
							  echo "Sim";
							} else {
							  echo "Não";
							}
						  ?>
						  </label>
						</td>
					  </tr>
					  <tr>
						<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Licitação:</td>
						<td align="left" class="textonormal" colspan="3" >
						  <label><?php echo substr($_SESSION['Licitação'] + 10000,1); ?></label>
						</td>
					  </tr>
					  <tr>
						<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Ano da Licitação:</td>
						<td align="left" class="textonormal" colspan="3" >
						  <label><?php echo $_SESSION['AnoLicitação']; ?></label>
						</td>
					  </tr>
					  <tr>
						<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Objeto:</td>
						<td>
						  <label class="textonormal" style="word-wrap:break-word;" ><?php echo $_SESSION['Objeto'];?></label>
						</td>
					  </tr>
					  
					  <tr>
						<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Tipo:</td>
						<td>
						  <label class="textonormal" style="word-wrap:break-word;" ><?=(($_SESSION['PregaoTipo'] == 'N') ? ("MENOR PREÇO") : ("MAIOR OFERTA"))?></label>
						</td>
					  </tr>									  
					</table>				
				</td>
			</tr>
			
			<tr>
			  <td>
				<table border="0" width="100%" summary="">
				  
				  <tr>
					<td class="textonormal" bgcolor="#DCEDF7" style="font-weight: bold; cursor: help;" title="Campo obrigatório apenas para opções: 'Ata e Anexos' e 'Somente a Ata'">Tipos de Ata*: </td>
					<td class="textonormal"><input type="hidden" name="Critica" value="1"></td>
					<input type="hidden" name="TamanhoMaximoParagrafos" value="<?=$TamanhoMaximoParagrafos?>" />
					<td class="textonormal" bgcolor="#FFFFFF">
					  <select name="TipoAta" class="textonormal"
					  onchange="javascript:enviar('CamposTipoAta');"
					  >  
					  <?
						if($_SESSION['deserta'] == "N")
						{
					  ?>					  
						<option value="0">Selecione um Tipo de Ata...</option>						
						<option value="1" <?=(($_SESSION['TipoAta'] == 1) ? ("selected") : (""))?>>Sessão Única</option>
						<option value="2" <?=(($_SESSION['TipoAta'] == 2) ? ("selected") : (""))?>>Sessão Inicial</option>
						<option value="3" <?=(($_SESSION['TipoAta'] == 3) ? ("selected") : (""))?>>Continuação de Sessão</option>
						<option value="4" <?=(($_SESSION['TipoAta'] == 4) ? ("selected") : (""))?>>Sessão Final</option>
					  <?
						}
					  ?>						
					  <?
						if($_SESSION['deserta'] == "D")
						{
					  ?>						
							<option value="5" <?=(($_SESSION['deserta'] == "D") ? ("selected") : (""))?>>Deserta</option>
							
					  <?
						}
					  ?>
					  <?
						if($_SESSION['deserta'] == "F")
						{
					  ?>					  
						<option value="6">Fracassada</option>
					  <?
						}
					  ?>						
					  </select>
					  
					  <?
					  if($_SESSION['IntervaloLotes'] == True)
					  {
					  ?>
						<span style="font-weight: bold"> | Lote Inicial: </span>
						<input type="text" name="LoteInicialIntervalo" size="6" maxlength="4" value="<?php echo $_SESSION['LoteInicialIntervalo'] ?>" class="textonormal">
						<span style="font-weight: bold"> | Lote Final: </span>
						<input type="text" name="LoteFinalIntervalo" size="6" maxlength="4" value="<?php echo $_SESSION['LoteFinalIntervalo'] ?>" class="textonormal">
						<span style="font-weight: bold">|</span>
					  <?
					  }
					  ?>
					</td>
				  </tr>
				  
				  <?
					if($_SESSION['deserta'] == "N")
					{
				  ?>				  
					  <tr>
						<td class="textonormal" bgcolor="#DCEDF7" style="font-weight: bold; cursor: help;" title="Campo obrigatório apenas para opções: 'Ata e Anexos' e 'Somente a Ata'">Recurso*: </td>
						<td class="textonormal"><input type="hidden" name="Critica" value="1"></td>
						<td class="textonormal" bgcolor="#FFFFFF">
						  <select name="Recurso" class="textonormal">
							<option value="">Selecione uma opção...</option>
							<option value="S">Sim</option>
							<option value="N">Não</option>
						  </select>
						</td>
					  </tr>
				  <?
					}
				  ?>

				  <tr>
					<td class="textonormal" bgcolor="#DCEDF7" style="font-weight: bold; cursor: help;" title="Como a equipe deve ser nomedada na Ata">Equipe: </td>
					<td class="textonormal"><input type="hidden" name="Critica" value="1"></td>
					<td class="textonormal" bgcolor="#FFFFFF">
					  <select name="nome_equipe" class="textonormal">
						<option value="0">... membros de comissão...</option>
						<option value="1">... equipe de apoio...</option>
					  </select>
					</td>
				  </tr>
				  
				  <tr>
					<td class="textonormal" bgcolor="#DCEDF7" style="font-weight: bold; cursor: help;" title="Caso opte por deixar essa opção em branco, o Órgão Licitante vinculado ao Usuário automaticamente será selecionado.">Órgão:</td>
					<td class="textonormal">
						<input type="hidden" name="Critica" value="1">
					</td>
					<td class="textonormal" bgcolor="#FFFFFF">
						<input type="text" name="OrgaoLicitante" size="80" maxlength="150" value="<?php echo $_SESSION['OrgaoLicitante'] ?>" class="textonormal">						
					</td>
				  </tr>

				  <tr>
					<td class="textonormal" bgcolor="#DCEDF7" style="font-weight: bold; cursor: help;" title="Endereço de sede do Órgão, onde será realizado o certame">Endereço:</td>
					<td class="textonormal">
						<input type="hidden" name="Critica" value="1">
					</td>
					<td class="textonormal" bgcolor="#FFFFFF">
						<input type="text" name="EnderecoOrgaoLicitante" size="80" maxlength="200" value="<?php echo $_SESSION['EnderecoOrgaoLicitante'] ?>" class="textonormal">						
					</td>
				  </tr>
				  
				  

				  <?
					if($_SESSION['deserta'] == "D")
					{
				  ?>
					  <tr>
						<td class="textonormal" bgcolor="#DCEDF7" style="font-weight: bold; cursor: help;" title="Tempo de tolerância para Pregão Presencial com situação igual a DESERTA (em minutos).">Tempo de Tolerância*:</td>
						<td class="textonormal">
							<input type="hidden" name="Critica" value="1">
						</td>
						<td class="textonormal" bgcolor="#FFFFFF">
							<input type="text" name="TempoDeToleranciaDeserta" size="6" maxlength="4" value="<?php echo $_SESSION['TempoDeToleranciaDeserta'] ?>" class="textonormal">	
							Minutos						
						</td>
					  </tr>				  
				  <?
					}
				  ?>
				  
					  <tr>
						<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold; cursor:help;" title="Demais participantes do Pregão Presencial.">Demais Participantes:</td>
						<td align="left" class="textonormal" colspan="3" >
							
							<textarea
								name="DemaisParticipantes"
								cols="80"
								rows="12"
								maxlength="<?=$TamanhoMaximoParagrafos?>"
								OnKeyUp="javascript:ncaracteresX(1)"
								OnBlur="javascript:ncaracteresX(0)"
								OnSelect="javascript:ncaracteresX(1)"
								class="textonormal"><?= $_SESSION['DemaisParticipantes'] ?></textarea>							
							
							<br /> 
							
							<font class="textonormal">máximo de <?=$TamanhoMaximoParagrafos?> caracteres (Restantes: </font>
							
							<input
								disabled
								type="text"
								name="NCaracteresX"
								OnFocus="javascript:document.CadPregaoPresencialFiltroAta.DemaisParticipantes.focus();"
								size="3"
								value="<?php echo (($NCaracteresX == "" or $NCaracteresX == null) ? $TamanhoMaximoParagrafos : $NCaracteresX) ?>"
								class="textonormal" />							
							)
						</td>						
					  </tr>				  
				  
				  <?
					if($_SESSION['deserta'] == "N")
					{
				  ?>				  
					  <tr>
						<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold; cursor:help;" title="Caso: Sorteio entre empresas em caso de empate de preço inicial, empresa precisará corrigir o prazo de validade da proposta de acordo com o edital.">Parágrafo Adicional 01:</td>
						<td align="left" class="textonormal" colspan="3" >
							
							<textarea
								name="ParagrafoAdicionalA"
								cols="80"
								rows="12"
								maxlength="<?=$TamanhoMaximoParagrafos?>"
								OnKeyUp="javascript:ncaracteresO(1)"
								OnBlur="javascript:ncaracteresO(0)"
								OnSelect="javascript:ncaracteresO(1)"
								class="textonormal"><?= $_SESSION['ParagrafoAdicionalA'] ?></textarea>							
							
							<br /> 
							
							<font class="textonormal">máximo de <?=$TamanhoMaximoParagrafos?> caracteres (Restantes: </font>
							
							<input
								disabled
								type="text"
								name="NCaracteresO"
								OnFocus="javascript:document.CadPregaoPresencialFiltroAta.ParagrafoAdicionalA.focus();"
								size="3"
								value="<?php echo (($NCaracteresO == "" or $NCaracteresO == null) ? $TamanhoMaximoParagrafos : $NCaracteresO) ?>"
								class="textonormal" />							
							)
						</td>						
					  </tr>

					  <tr>
						<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold; cursor:help;" title="Caso: Registrar informações sobre a continuidade do processo em outra(s) sessão(ões).">Parágrafo Adicional 02:</td>
						<td align="left" class="textonormal" colspan="3" >
							
							<textarea
								name="ParagrafoAdicionalB"
								cols="80"
								rows="12"
								maxlength="<?=$TamanhoMaximoParagrafos?>"
								OnKeyUp="javascript:ncaracteresB(1)"
								OnBlur="javascript:ncaracteresB(0)"
								OnSelect="javascript:ncaracteresB(1)"
								class="textonormal"><?= $_SESSION['ParagrafoAdicionalB'] ?></textarea>							
							
							<br /> 
							
							<font class="textonormal">máximo de <?=$TamanhoMaximoParagrafos?> caracteres (Restantes: </font>
							
							<input
								disabled
								type="text"
								name="NCaracteresB"
								OnFocus="javascript:document.CadPregaoPresencialFiltroAta.ParagrafoAdicionalB.focus();"
								size="3"
								value="<?php echo (($NCaracteresB == "" or $NCaracteresB == null) ? $TamanhoMaximoParagrafos : $NCaracteresB) ?>"
								class="textonormal" />							
							)
						</td>						
					  </tr>	

					  <tr>
						<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold; cursor:help;" title="Caso: Registrar as empresas que vão entrar com recurso e devolução de envelope.">Parágrafo Adicional 03:</td>
						<td align="left" class="textonormal" colspan="3" >
							
							<textarea
								name="ParagrafoAdicionalC"
								cols="80"
								rows="12"
								maxlength="<?=$TamanhoMaximoParagrafos?>"
								OnKeyUp="javascript:ncaracteresC(1)"
								OnBlur="javascript:ncaracteresC(0)"
								OnSelect="javascript:ncaracteresC(1)"
								class="textonormal"><?= $_SESSION['ParagrafoAdicionalC'] ?></textarea>							
							
							<br /> 
							
							<font class="textonormal">máximo de <?=$TamanhoMaximoParagrafos?> caracteres (Restantes: </font>
							
							<input
								disabled
								type="text"
								name="NCaracteresC"
								OnFocus="javascript:document.CadPregaoPresencialFiltroAta.ParagrafoAdicionalC.focus();"
								size="3"
								value="<?php echo (($NCaracteresC == "" or $NCaracteresC == null) ? $TamanhoMaximoParagrafos : $NCaracteresC) ?>"
								class="textonormal" />							
							)
						</td>						
					  </tr>

					  <tr>
						<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold; cursor:help;" title="Caso: Aceitação do mesmo valor do arrematante, ausência de fornecedor antes do término da sessão.">Parágrafo Adicional 04:</td>
						<td align="left" class="textonormal" colspan="3" >

							
							<textarea
								name="ParagrafoAdicionalD"
								cols="80"
								rows="12"
								maxlength="<?=$TamanhoMaximoParagrafos?>"
								OnKeyUp="javascript:ncaracteresD(1)"
								OnBlur="javascript:ncaracteresD(0)"
								OnSelect="javascript:ncaracteresD(1)"
								class="textonormal"><?= $_SESSION['ParagrafoAdicionalD'] ?></textarea>							
							
							<br /> 
							
							<font class="textonormal">máximo de <?=$TamanhoMaximoParagrafos?> caracteres (Restantes: </font>
							
							<input
								disabled
								type="text"
								name="NCaracteresD"
								OnFocus="javascript:document.CadPregaoPresencialFiltroAta.ParagrafoAdicionalD.focus();"
								size="3"
								value="<?php echo (($NCaracteresD == "" or $NCaracteresD == null) ? $TamanhoMaximoParagrafos : $NCaracteresD) ?>"
								class="textonormal" />							
							)
						</td>						
					  </tr>					  
				  <?
					}
					else
					{
				  ?>
					  <tr>
						<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold; cursor:help;" title="Caso: Parágrafo adicional para Pregão Presencial do tipo DESERTO.">Parágrafo Adicional [Deserto]:</td>
						<td align="left" class="textonormal" colspan="3" >
							
							<textarea
								name="ParagrafoAdicionalA"
								cols="80"
								rows="12"
								maxlength="<?=$TamanhoMaximoParagrafos?>"
								OnKeyUp="javascript:ncaracteresO(1)"
								OnBlur="javascript:ncaracteresO(0)"
								OnSelect="javascript:ncaracteresO(1)"
								class="textonormal"><?= $_SESSION['ParagrafoAdicionalA'] ?></textarea>							
							
							<br /> 
							
							<font class="textonormal">máximo de <?=$TamanhoMaximoParagrafos?> caracteres (Restantes: </font>
							
							<input
								disabled
								type="text"
								name="NCaracteresO"
								OnFocus="javascript:document.CadPregaoPresencialFiltroAta.ParagrafoAdicionalA.focus();"
								size="3"
								value="<?php echo (($NCaracteresO == "" or $NCaracteresO == null) ? $TamanhoMaximoParagrafos : $NCaracteresO) ?>"
								class="textonormal" />							
							)
						</td>					
					  </tr>	
				  <?
					}
				  ?>				  
				  <tr>
					<td class="textonormal" bgcolor="#DCEDF7" style="font-weight: bold;">Gerar: </td>   
					<td class="textonormal"><input type="hidden" name="Critica" value="1"></td>
					<td class="textonormal" bgcolor="#FFFFFF">
					  <select name="TipoAtaGeracao" class="textonormal"> 							
							
						<option value="2">Somente a Ata</option>
						
					  <?
						if($_SESSION['deserta'] == "N")
						{
					  ?>						
							<option value="3">Anexo I - Preços Iniciais de cada fornecedor por lote</option>
							<option value="4">Anexo II - Histórico de Lances</option>
							<option value="5">Anexo III - Lances dos fornecedores arrematantes por lote</option>
							<option value="6">Anexo IV - Lances dos fornecedores vencedores por lote</option>
					  <?
						}
					  ?>							
					  </select>
					  
					  <input type="submit" value="Avançar" class="botao" onclick="javascript:enviar('GerarAta');">
					  <input type="submit" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
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
