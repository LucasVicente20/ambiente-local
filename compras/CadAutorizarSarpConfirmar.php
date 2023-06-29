<?php



#------------------------------------------------------
# Portal da DGCO
# Programa: CadAutorizarSarpConfirmar.php
# Autor:  	Heraldo Botelho
# Data:		19/01/2012
# Objetivo:	Autorizar Sarp - Demanda Redmine #5070
# Ultimas Alterações
#    Data:      06/2012
#    Objetivo:  Ativar o preeempenho   
#------------------------------------------------------
# OBS.:		Tabulação 2 epsaços

#  usar $_SESSION['_fperficorp_']
#------------------------------------------------------
# Alterado: Osmar Celestino
# Data:		11/01/2023
# Objetivo: Corrigir problema de tela quebrando: Urgência em Produção.
# -----------------------------------------------------------------------------------------------------------------------------------------------


$programa = "CadAutorizarSarp.php";

# Acesso ao arquivo de funções #
require_once("funcoesCompras.php");

# Executa o controle de segurança #
session_start();
Seguranca();



# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/compras/CadAutorizarSarpConfirmar.php' ); 
# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/compras/ConsAcompSolicitacaoCompra.php' );


# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST" ){
 	$chave = $_POST['chave'];
 	$Botao = $_POST['Botao'];
 	$Tipo  = $_POST['Tipo'];
	
}else if( $_SERVER['REQUEST_METHOD'] == "GET" ){
	$chave = $_GET['chave'];
	
}

 
if( $Botao == "Voltar" ){
		header("location: CadAutorizarSarp.php");
		exit;
}

 

//----------------------------
//  Criar Conexao
//----------------------------
$db = Conexao();
$db->query("BEGIN TRANSACTION");

$dbOracle = ConexaoOracle();




//----------------------------
//  Consultar Solicitcao
//----------------------------
$result = consultarSolicitacao($db,$chave);
$row 			= $result->fetchRow(DB_FETCHMODE_OBJECT);
$codigo 		= $row->codigo;
$ano    		= $row->ano;
$data   		= $row->data;
$empresa 		= $row->empresa;
$centrocusto 	= $row->detalhe;
$situacao 		= $row->situacao;
$detalhe  		= $row->detalhe;
$codsit   		= $row->codsit;
$tiposarp       = $row->tiposarp;
$geracontrato   = $row->geracontrato;



//---------------------------------
// Seção de verificação de erros
//---------------------------------
// Se tentar Retirar SARP verificar se SOFIN Importou a Pre-Solicitacao de Compra
if ( $Botao == "Retirar" and sofinImportou($db,$chave)> 0  ) {
	$Mens=1;
	$Tipo=2;
	$Mensagem="Não é possível desfazer a autorização SARP pois o SOFIN já efetuou a importação dos dados da PSE desta SCC";
}


//---------------------------------
// Seção de atualização
//---------------------------------
//--------------------------------------------------
//  Autorizar  SARP
//--------------------------------------------------
if ( $Mens!=1) {
	
if( $Botao == "Autorizar"  ){
  
    $Tipo=1;  
	
    $consolidar=false;		 
  	if ( $tiposarp == "C") {
  		// Atualizar flag de autorização de carona 
  		$carona = "'S'";
		atualizarCarona($db,$chave,$carona);
  	}
	else {
  	   // Atualicitacao para "PARTICIPANTE='S'";
  	   $participante = "'S'";
		atualizarLicitacao($db,$chave,$participante );
	}
	
	
	// Se for gerar contrato
	if ( $geracontrato=="S") {
		$situacaoAux = 4;
  		// Atualizar situacao da solicitacao para 'pendente de contrato'
		atualizarSolicitacao($db,$chave,4);		
		// Inserir no historico de situacao da solicitacao
		inserirHistorico($db,$chave,4);
	    $consolidar=true;		 
	} else {
		
		$situacaoAux = 3;		
  		// Atualizar situacao da solicitacao para 'pendente de empenho'  
		atualizarSolicitacao($db,$chave,3);		
		// Inserir no historico de situacao da solicitacao
	    inserirHistorico($db,$chave,3) ;
	   	// $consolidar=true;  
	   	$GLOBALS["iniciouTransacaoBanco"]=true; 
	     try {
  	          gerarPreSolicitacaoEmpenho($db, $dbOracle, $chave);
		     $consolidar=true;
	    }
	    catch ( ExcecaoPendenciasUsuario $e )  {
	      $Mens=1;
	      $Tipo=2;
	      $Mensagem=$e->getMessage();
	      
	    }
	    
	}
	
}




//--------------------------------------------------
//  Autorizar  Retirar Autorizacao
//--------------------------------------------------
if( $Botao == "Retirar"  ){
       
        $Tipo=1;     
	    $consolidar=false;		 	
  		if ( $tiposarp == "C") {
  			// Atualizar flag de autorização de carona (= null)
			atualizarCarona($db,$chave,"null"); 
			atualizarLicitacao($db,$chave,"null" );			
  		}
		else {
  		   // Atualicitacao para "PARTICIPANTE=nulo";
			atualizarLicitacao($db,$chave,"null" );
		}
		
		$situacaoAux = 5;
		// Excluir itens da Presolicitacao	
        deletaItensPresolicitacao($db,$chave);
		// Excluir itens da Presolicitacao	
        deletaPresolicitacao($db,$chave);            		
		// Atualizar situação da solicitação
		atualizarSolicitacao($db,$chave,5);
		// Inserir no historico de situacao da solicitacao
		inserirHistorico($db,$chave,5); 
		$consolidar=true;
		
}

}


//---------------------------
// Definir se pode atualizar ou cancelar transações
//---------------------------
if ( $Tipo==1 )  {
	 $Mens=1;  
     $Mensagem="Atualização Efetuada";
   	 executarTransacao($db, "commit");
   	 $db->query("END TRANSACTION");
   	 executarTransacao($dbOracle, "commit");
   	 $dbOracle->query("END TRANSACTION");   	 		 	   	 		 
}

if ( $Tipo==2 )  {
   	 executarTransacao($db, "rollback");
   	 executarTransacao($dbOracle, "rollback");		 	   	 		 
}


 
//----------------------------
//  Consultar Solicitcao
//----------------------------

$result = consultarSolicitacao($db,$chave);

$row 			= $result->fetchRow(DB_FETCHMODE_OBJECT);
$codigo 		= $row->codigo;
$ano    		= $row->ano;
$data   		= $row->data;
$empresa 		= $row->empresa;
$centrocusto 	= $row->detalhe;
$situacao 		= $row->situacao;
$detalhe  		= $row->detalhe;
$codsit   		= $row->codsit;
$tiposarp       = $row->tiposarp;
$geracontrato   = $row->geracontrato;


//----------------------------
//  Consultar Licitacao
//----------------------------
$vetor = dadosDaLicitacao($db,$chave );

$procLic=$vetor[0];
$anoLic=$vetor[1];
$grupoLic=$vetor[2];
$comLic=$vetor[3];
$orgaoLic=$vetor[4];
$particLic=$vetor[5];  







function atualizarCarona($db,$chave,$flag){
	$agora   = date("Y-m-d H:i:s");	
	$sql  =	" update sfpc.tbsolicitacaocompra ";
	$sql .= " set fsolcoautc = $flag, ";
	$sql .= " tsolcoulat= '$agora', ";
	$sql .= " cusupocod1=".$_SESSION['_cusupocodi_'];
	$sql .= " where  csolcosequ=$chave ";
	$result = executarTransacao($db, $sql);		
}

function atualizarLicitacao($db,$chave,$flag ){
    //Pesquisar a licitação
    $sql =	" select clicpoproc, alicpoanop,  cgrempcodi, ccomlicodi , corglicodi";  
    $sql .=  " from ";
    $sql .=  " sfpc.tbsolicitacaolicitacaoportal sol  ";
    $sql .=  " where csolcosequ=$chave ";
 
     
    $result	= executarTransacao($db, $sql);
    $row	= $result->fetchRow(DB_FETCHMODE_OBJECT);      
    $processoLic=$row->clicpoproc;
    $anoLic=$row->alicpoanop;
    $grupoLic=$row->cgrempcodi;
    $comissaoLic=$row->ccomlicodi;
    $orgaoLic=$row->corglicodi;
    // atualizar flag de autorização de participante do processo licitatório)
    if ( ! empty($processoLic)) {
	   $agora   = date("Y-m-d H:i:s");	    
 	   $sql =  "update sfpc.tblicitacaoportal";
 	   $sql .= " set FLICPOAUTP = $flag, ";
 	   $sql .= " tlicpoulat= '$agora', ";	   
	   $sql .= " cusupocodi =".$_SESSION['_cusupocodi_'];
       $sql .= " where ";
   	   $sql .= " clicpoproc=$processoLic   and ";
 	   $sql .= " alicpoanop=$anoLic and ";
 	   $sql .= " cgrempcodi=$grupoLic  and ";
 	   $sql .= " ccomlicodi=$comissaoLic and ";
 	   $sql .= " corglicodi=$orgaoLic  ";
 	   $result	= executarTransacao($db, $sql);
    }
 
    
   
    
}

function dadosDaLicitacao($db,$chave ){
  //Pesquisar a licitação
  $sql =  " select ";
  $sql .= " lic.clicpoproc as processo, lic.alicpoanop as ano, lic.cgrempcodi as grupo, lic.ccomlicodi as com, lic.corglicodi as orgao, lic.flicpoautp as participante ";
  $sql .= " from ";
  $sql .= "  sfpc.tblicitacaoportal lic, sfpc.tbsolicitacaolicitacaoportal sol ";
  $sql .= "  where ";
  $sql .= "  sol.csolcosequ  = $chave ";
  $sql .= "  and  lic.clicpoproc = sol.clicpoproc ";
  $sql .= "  and  lic.alicpoanop = sol.alicpoanop ";
  $sql .= "  and  lic.cgrempcodi = sol.cgrempcodi ";
  $sql .= "  and  lic.ccomlicodi = sol.ccomlicodi ";
  $sql .= "  and  lic.corglicodi = sol.corglicodi ";
 
  //echo $sql;
  //exit;
  
  
  $result	= executarTransacao($db, $sql);
  $row	= $result->fetchRow(DB_FETCHMODE_OBJECT);      
  $vetor[0]= $row->processo;
  $vetor[1]= $row->ano;
  $vetor[2]= $row->grupo;
  $vetor[3]= $row->com;
  $vetor[4]= $row->orgao;
  $vetor[5]= $row->participante;  
  
  
  
  return $vetor;
  
}






function atualizarSolicitacao($db,$chave,$situacao){
	$agora   = date("Y-m-d H:i:s");	
	$sql  =	" update sfpc.tbsolicitacaocompra ";
	$sql .= " set  CSITSOCODI = $situacao, ";
	$sql .= " tsolcoulat= '$agora', ";
	$sql .= " cusupocod1=".$_SESSION['_cusupocodi_'];
	$sql .= " where  csolcosequ=$chave ";
	$result	= executarTransacao($db, $sql);
}


function inserirHistorico($db,$chave,$situacao){
	$agora   = date("Y-m-d H:i:s");
	$sql = " insert into sfpc.tbhistsituacaosolicitacao ";
	$sql .= " (csolcosequ,thsitsdata,csitsocodi,xhsitsobse,cusupocodi)"; 
	$sql .= " values ";
	$sql .= " ($chave, '$agora', $situacao, 'autorizacao/desautorizacao sarp',".$_SESSION['_cusupocodi_']." ) "; 
	$result	= executarTransacao($db, $sql); 
}

function consultarSolicitacao($db,$chave) {
	$sql  = " SELECT SOL.CSOLCOSEQU as codigo, SOL.ASOLCOANOS as ano,";
	$sql .= " to_char(SOL.TSOLCODATA, 'DD/MM/YYYY' ) as data, ORG.EORGLIDESC as empresa,";
	$sql .= " CEN.ECENPODESC as centrocusto, CEN.ECENPODETA as detalhe, "; 
	$sql .= " SSO.ESITSONOME as situacao, sso.csitsocodi as codsit,  "; 
	$sql .= " SOL.fsolcorpcp as tiposarp, sol.fsolcocont as geracontrato ";  
	$sql .= " FROM SFPC.TBSOLICITACAOCOMPRA SOL, SFPC.TBORGAOLICITANTE ORG, "; 
	$sql .= " SFPC.TBCENTROCUSTOPORTAL CEN, SFPC.TBSITUACAOSOLICITACAO SSO ";
	$sql .= " WHERE "; 
	$sql .= " SOL.CORGLICODI = ORG.CORGLICODI AND ";
	$sql .= " sol.csolcosequ = $chave and ";
	$sql .= " CEN.CCENPOSEQU = SOL.CCENPOSEQU AND ";
	$sql .= " SOL.CSITSOCODI = SSO.CSITSOCODI  ";
	
	$result	= executarTransacao($db, $sql); 
	return 	$result;	
}


function sofinImportou($db,$chave) {
	$sql =  " select count(*) as qtd from SFPC.TBPRESOLICITACAOEMPENHO ";
	$sql .= " where ";
	$sql .= " csolcosequ=$chave  and "; 
	$sql .= " TPRESOIMPO is not null "; 
	$result	= executarTransacao($db, $sql);
	$row	= $result->fetchRow(DB_FETCHMODE_OBJECT);      
	return $row->qtd;
}

function deletaItensPresolicitacao($db,$chave) {
    $sql =  " delete  from sfpc.tbitempresolicitacaoempenho item ";
    $sql .= " where ";
    $sql .= " exists ";
    $sql .= " ( select  pre.apresoanoe ";
    $sql .= "  from sfpc.tbpresolicitacaoempenho pre ";
    $sql .= "  where "; 
    $sql .= "  pre.apresoanoe = item.apresoanoe and ";
    $sql .= "  pre.cpresosequ = item.cpresosequ and ";
    $sql .= "  pre.csolcosequ = $chave "; 
    $sql .= "  ) ";
	$result	= executarTransacao($db, $sql);
}

function deletaPresolicitacao($db,$chave) {
    $sql =  " delete  from sfpc.tbpresolicitacaoempenho pre ";
    $sql .= " where ";
    $sql .= "  pre.csolcosequ = $chave "; 
	$result	= executarTransacao($db, $sql);
}



?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.CadAutorizarSarpConfirmar.Botao.value=valor;
	document.CadAutorizarSarpConfirmar.submit();
}


<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script   src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadAutorizarSarpConfirmar.php" method="post" name="CadAutorizarSarpConfirmar">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Compras > Solicitação > Autorizar SARP
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
		<td width="100"></td>
		<td align="left" colspan="2">
			<?php if ( $Mens == 1 ) { ExibeMens($Mensagem,$Tipo,1); } ?>
		</td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="100"></td>
		<td class="textonormal">
			<table border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="">
				<tr>
					<td class="textonormal">
						<table border="0" cellspacing="0" cellpadding="0" summary="">
							<tr>
								<td class="textonormal">
									<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF" width="600px">
										<tr>
											<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="5" >
												AUTORIZAR SARP - SOLICITAÇÃO DE COMPRA E CONTRATAÇÃO (SCC)
												<?php

												//  echo "<p> (Chave Solicitacao)= $chave  Situacao=$codsit Gera Contrato=$geracontrato </p> ";
												//  echo "<p>  (Chave Licitacao)Proc=$procLic Ano=$anoLic Grupo=$grupoLic Comissao=$comLic Orgao=$orgaoLic </p>" ;
												//  echo "<p> Tipo SARP(solicitacao) = $tiposarp </p>";
												//  echo "<p> Indicador de Participante(licitacao) = $particLic <p>";
												?>
												
												
											</td>
										</tr>
										<tr>
											<td class="titulo3" bgcolor="#BFDAF2" height="20" colspan="5" align="center">
												<?php echo $empresa ?>
											</td>
										</tr>
										<tr>
											<td class="titulo3" bgcolor="#BFDAF2" height="20" colspan="5" align="center">
												<?php echo $centrocusto ?>
											</td>
										</tr>
										<tr class="titulo3">
										<td>SOLICITAÇÃO</td>
										<td>DETALHAMENTO</td>
										<td>DATA</td>
										<td>SITUAÇÃO</td>
										</tr>
										<tr>
										<td> 
										<?php
										    $strSolicitacaoCodigo = getNumeroSolicitacaoCompra($db, $codigo);
										    $programa="CadAutorizarSarp.php";
										    //$codSolAno = $codigo."/".$ano;
									    	$programaSelecao =  "ConsAcompSolicitacaoCompra.php";
									    	$Url = $programaSelecao."?SeqSolicitacao=$codigo&programa=$programa";
							                echo "<a href=\"$Url\"><font color=\"#000000\">".$strSolicitacaoCodigo."</font></a>"		 
										?>
										
										<?php //echo $codigo."/".$ano ?>
										</td>
										<td><?php echo $detalhe ?></td>
										<td><?php echo $data ?></td>
										<td><?php echo $situacao ?></td> 
										</tr>
										<tr>
										</tr>
				 
										<tr>
											<td  align="right" colspan="5" >
												<input type="hidden" name="TipoMaterial" value="<?php echo $TipoMaterial; ?>">
												<input type="hidden" name="Grupo" value="<?php echo $Grupo; ?>">
												<input type="hidden" name="DescGrupo" value="<?php echo $DescGrupo; ?>">
                                                <?php if ( $Tipo!=1 ) {  ?>
													<?php  if ($codsit==5) { ?>
													    <input type="button" value="Autorizar SARP" class="botao" onclick="javascript:enviar('Autorizar');">
									                <?php  } else {?>
													    <input type="button" value="Retirar Autorização SARP" class="botao" onclick="javascript:enviar('Retirar');">
   									                <?php  }  ?>
   								                <?php  } ?>
												<input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
												<input type="hidden" name="Botao" value="">
												<input type="hidden" name="Tipo" value="<?php echo $Tipo ?>">
												
												
												<input type="hidden" name="chave" value="<?php echo $chave ?>"> 
												
											</td>
										</tr>
									</table>
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
<?php
//---------------------------
// Finalizar Transações
//---------------------------
$db->disconnect();
$dbOracle->disconnect();

?>


<script language="JavaScript">
 
</script>
