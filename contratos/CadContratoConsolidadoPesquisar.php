<?php
# -------------------------------------------------------------------------
# Portal da Compras
# Programa: CadContratoConsolidadoPesquisar.php
# Autor:    Eliakim Ramos | João Madson
# Data:     11/12/2019
# -------------------------------------------------------------------------
# Autor:    Madson Felix
# Data:     28/04/2021
# CR #246939
# -------------------------------------------------------------------------
# Autor:    Osmar Celestino
# Data:     12/01/2022
# CR #257662
# -------------------------------------------------------------------------
# Alterado : Osmar Celestino
# Data: 17/03/2022
# Objetivo: CR #260719
#---------------------------------------------------------------------------
# Alterado : Osmar Celestino
# Data: 28/06/2023
# Objetivo: CR #285483 && 285484
#---------------------------------------------------------------------------
# Acesso ao arquivo de funções #

require_once("../funcoes.php");
require_once "ClassPesquisarRelContratoConsolidado.php";# Executa o controle de segurança #
session_start();
if(empty($_REQUEST['portalCompras'])){
	Seguranca();


	# Adiciona páginas no MenuAcesso #
	AddMenuAcesso ('/estoques/CadItemDetalhe.php');
	AddMenuAcesso ('/compras/'.$programaSelecao);

	unset($_SESSION['documento_anexo']);
	unset($_SESSION['dadosFornecedor']);
	unset($_SESSION['fiscal_selecionado']);
	unset($_SESSION["fornsequ".$_SESSION['csolcosequ']]);
	unset($_SESSION["org".$_SESSION['csolcosequ']]);
	unset($_SESSION["flagCPFPJ".$_SESSION['csolcosequ']]);
	unset($_SESSION['csolcosequ']);
	unset($_SESSION['dadosObjOrgao']);
	unset($_SESSION['dadosSalvar']);
	unset($_SESSION['dadosContratado']);
	unset($_SESSION['origemScc']);
	unset($_SESSION['numScc']);
	unset($_SESSION['CpfCnpj']);
	$URLDataInicio  = "../calendario.php?Formulario=formulario&Campo=datainicial";
    $URLvDataFim  = "../calendario.php?Formulario=formulario&Campo=datafinal";
	?>
	<html>
	<?php
	# Carrega o layout padrão #
	layout();
	?>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script language="javascript" src="../janela.js" type="text/javascript"></script>
	<script language="javascript" src="../import/jquery/jquery-1.7.2.min.js" type="text/javascript"></script>
	<script language="javascript" src="../import/jquery/jquery.maskmoney.js" type="text/javascript"></script>
	<script language="javascript" src="../import/jquery/jquery.maskedinput.js" type="text/javascript"></script>
	<script language="javascript" type="">

		
		
		function montaTabelaView( objJson,orgao){
			
			let tableHtml = "";
			tableHtml  	 +='<tr class="removeresult">';
			tableHtml  	 +='<td align="center" bgcolor="#75ADE6" colspan="4" class="titulo3">';
			tableHtml  	 +='RESULTADO DA PESQUISA ';
			tableHtml  	 +='</td>';
			tableHtml  	 +='</tr>';
			if(objJson == null  || objJson=='' || objJson == 'undefined'){
					tableHtml  	 +='<tr class="removeresult">';
					tableHtml  	 +='<td align="center"  colspan="4" class="titulo3">';
					tableHtml  	 += 'Pesquisa sem ocorrências.';
					tableHtml  	 +='</td>';
					tableHtml  	 +='</tr>';
			}
			for(k in orgao){
				if(orgao[k].eorglidesc != '' && orgao[k].eorglidesc != 'undefined'){
					tableHtml  	 +='<tr class="removeresult">';
					tableHtml  	 +='<td align="center" bgcolor="#BFDAF2" colspan="4" class="titulo3">';
					tableHtml  	 +=orgao[k].eorglidesc;
					tableHtml  	 +='</td>';
					tableHtml  	 +='</tr>';
					tableHtml  	 +='<tr class="removeresult">';
					tableHtml  	 +='	<td class="textonormal" >';
					tableHtml  	 +='<div id="formTablePesquisa "   >';
					tableHtml  	 +='<div class="loader"></div>';
					tableHtml  	 +='<table class="tablePesquisa textonormal" style=" position:relative;  width : 100%; " >';
					tableHtml  	 +="<thead >";
					tableHtml  	 +="<tr>";
					tableHtml  	 +="<td>N° CONTRATO</td>";
					tableHtml  	 +="<td>SCC</td>";
					tableHtml  	 +="<td>OBJETO</td>";
					tableHtml  	 +="<td>ORIGEM</td>";
					tableHtml  	 +="<td>FORNECEDOR</td>";
					tableHtml  	 +="<td>CNPJ/CPF</td>";
					tableHtml  	 +="<td>SITUACAO</td>";
					tableHtml  	 +="</tr>";
					tableHtml  	 +="</thead>";
					tableHtml  	 +="<tbody>";
					for(i in objJson){
						if(orgao[k].eorglidesc == objJson[i].eorglidesc){ console.log(objJson[i].etpcomnome);
							tableHtml     +="<tr>"; 
							let numeroContrato = objJson[i].ectrpcnumf != ""?objJson[i].ectrpcnumf:"Aguardando Numeração";
							tableHtml     +='<td width="8%"> <a href="javascript:selecionar('+objJson[i].cdocpcsequ+')" style="text-transform: capitalize">'+numeroContrato.toLowerCase()+'</a></td>';
							tableHtml     +='<td width="8%">'+objJson[i].SCC+'</td>';
							tableHtml     +='<td width="30%">'+objJson[i].ectrpcobje+'</td>';
							tableHtml     +='<td width="14%">'+objJson[i].etpcomnome+'</td>';
							tableHtml     +='<td width="14%">'+objJson[i].nforcrrazs+'</td>';
							tableHtml     +='<td width="12%">'+objJson[i].cpfCNPJ+'</td>';
							tableHtml     +='<td width="14%">'+objJson[i].esitdcdesc+'</td>';
							tableHtml     +="</tr>";
						}
					}
					tableHtml +="</tbody>";
					tableHtml +='</table>';
					tableHtml +='</div>';
					tableHtml +='</td>';
					tableHtml +="</tr>";
				}
			}
				return tableHtml;
		}
		function selecionar(registro){
			// formPesquisa
			if(registro != '' && registro!= null && registro !='undefined' ){
				$("#regitrocontrato").val(registro);
				$("#formPesquisa").attr('action','./CadContratoConsolidado.php');
				$("#formPesquisa").submit();
			}
		}
		<!--
		$(document).ready(function() {
			const urlStr = window.location.href;
			const url = new URL(urlStr);
			const mensagem = url.searchParams.get("m");
			const show = url.searchParams.get("h");
			$('#numeroScc').mask('9999.9999/9999');
			$('#numerocontratoano').mask('9999.9999/9999');
			$('#cnpj').mask('99.999.999/9999-99');
			$('#cpf').mask('999.999.999-99');
			$.post("postDadosPesquisa.php",{op:"OrgaoGestor"}, function(data){
					$("#orgaoGestor").append(data);
				});
			
			if(show == "show"){
				$("#tdmensagem").show();
				$(".error").html("Atenção!");
				$(".error").css("color","#007fff");
				$(".mensagem-texto").html(mensagem);
			}
			$('#radio-cpf').on('click',function(){
				$("#mostracnpj").hide();
				$("#cnpj").val('');
				$("#mostracpf").show();
			});
			$('#radio-cnpj').on('click',function(){
				$("#mostracnpj").show();
				$("#cpf").val('');
				$("#mostracpf").hide();
			});
			$("#btnLimpa").on('click', function(){
				window.location.reload();
			});
			$("#btnIncluir").on('click', function(){
				window.location.href="./CadContratoIncluir.php";
			});
			$("#btnPesquisar").on('click', function(){
				$("#formTablePesquisa").show();
				$(".loader").show();
				$(".tablePesquisa").html("");
				$(".removeresult").remove();
				const documento = $("input[name='cpf-cnpj']:checked").val();
				//console.log("teste");

				if (document.getElementById("nvigente").checked) {
					vigente = "nvigente";
				} else { 
					vigente = "vigente";
				}

				$.post("postDadosPesquisa.php",
						{
							op					:"PesquisaContrato",
							cpf					:$("#cpf").val(),
							cnpj				:$("#cnpj").val(),
							numerocontratoano	:$("#numerocontratoano").val(),
							Orgao				:$("#orgaoGestor").val(),
							numeroScc			:$("#numeroScc").val(),
							doc					:documento,
							vigente				:vigente,
							dataInicial			:$('#datainicial').val(),
							dataFinal			:$('#datafinal').val()
						}, 
					function(data){
						const retorno = JSON.parse(data);
						if(!retorno.status){
							$(".loader").hide();
							$("#tdmensagem").show();
							$(".mensagem-texto").html(retorno.msm);
							$(".tablePesquisa").html("");
							$(".tablePesquisa").hide();
						}else{
							$(".loader").hide();
							$(".removeresult").remove();
							$("#btnPesquisar").val("Pesquisar");
							$(".tablePesquisa").html("");
							$("#tabelaMaster").append(montaTabelaView(retorno.dados,retorno.orgao));
							$(".tablePesquisa").show();
							$("#formTablePesquisa").show();
							$("#btnIncluir").hide();
							$("#btnLimpa").show();
							$("#tdmensagem").hide();
							$(".mensagem-texto").html("");
						}
						
				});
				
			});
			$(".btn-pesquisa-fornecedor").on('click', function(){
				const documento = $("input[name='cpf-cnpj']:checked").val();
				$.post("postDadosPesquisa.php",{op:"Fornecerdor",cpf:$("#cpf").val(),cnpj:$("#cnpj").val(),doc:documento}, function(data){
					const objJSON = JSON.parse(data);
					if(objJSON.status){
						$(".dadosFornec").html(objJSON.msm);
						$("#tdmensagem").hide();
						$(".mensagem-texto").html("");
					}else{
						$("#tdmensagem").show();
						$(".mensagem-texto").html(objJSON.msm);
					}
					
				});
			});

		});

		<?php MenuAcesso(); ?>
		//-->
	</script>
	<link rel="stylesheet" type="text/css" href="../estilo.css?v=<?php echo time();?>">
	<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
		<script language="JavaScript" src="../menu.js"></script>
		<script language="JavaScript">Init();</script>
		<form action="<?=$programa?>" id="formPesquisa" method="post" name="formulario">
		<input type="hidden" name="idregistro" id="regitrocontrato">
			<br><br><br><br><br>
			<table cellpadding="3" border="0" summary="">
				<!-- Caminho -->
				<tr>
					<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
					<td align="left" class="textonormal" colspan="2">
						<font class="titulo2">|</font>			
						<a href="../index.php">
								<font color="#000000">Página Principal</font>
						</a> > Contratos  > Contratos  Consolidado
					</td>
				</tr>
				<!-- Fim do Caminho-->
				<!-- Erro -->
					<tr>
						<td width="150"></td>
						<td align="left" colspan="2" id="tdmensagem">
							<div class="mensagem">
								<div class="error">
								Erro!
								</div>
								<span class="mensagem-texto">
							
								</span>
							</div>
						</td>
					</tr>
				
				<!-- Fim do Erro -->
				<!-- Corpo -->
				<tr>
					<td width="150"></td>
					<td class="textonormal" width="70%" style=" position:absolute;" >
						<table width="80%" border="1" id="tabelaMaster" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
							<tr>
								<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="4">
												CONTRATO CONSOLIDADO
								</td>
							</tr>
							<tr>
								<td colspan="8">
									<table border="0" width="100%" summary="">
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" width="50%">Número do Contrato/Ano</td>
											<td class="textonormal"  width="50%">
												<input type="text" id="numerocontratoano" value="<?php echo (!empty($numeroSccAtual)) ? $numeroSccAtual : ''; ?>" name="numerocontratoano" class="textonormal" />
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" width="50%" height="20">Órgão</td>
											<td class="textonormal" width="50%">
												<select name="Orgao" class="textonormal" id="orgaoGestor">
													
												</select>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" width="50%">Número da Solicitação (SCC)</td>
											<td class="textonormal" width="50%">
												<input type="text" id="numeroScc" value="<?php echo (!empty($numeroSccAtual)) ? $numeroSccAtual : ''; ?>" name="numeroScc" class="textonormal" />
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" width="50%">
												Fornecedor
											</td>
											<td class="textonormal" width="50%">
												<input type="radio" name="cpf-cnpj" id="radio-cpf" value="CPF"><label for="cpf">CPF</label>
												<input type="radio" name="cpf-cnpj" id="radio-cnpj" value="CNPJ" checked><label for="CNPJ">CNPJ</label>
												<div class="textonormal"  id="mostracnpj" >
													<input type="text" name="cnpj" id="cnpj">
													<a href="#" class="btn-pesquisa-fornecedor">
														<img src="../midia/lupa.gif" border="0">
													</a>
													<br>
												</div>
												<div class="textonormal"  id="mostracpf" >
													<input type="text" name="cpf" id="cpf">
													<a href="#" class="btn-pesquisa-fornecedor">
														<img src="../midia/lupa.gif" border="0">
													</a>
													<br>
												</div>
													<div>
														<br>
														<br>
														<br>
														<span class="dadosFornec"></span>
													</div>
											</td>
											
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" width="50%">
											Apenas Contratos Vigentes ?
											</td>
											<td class="textonormal" width="50%">
											<input type="radio" name="vigente" id="vigente" value="vigente" checked><label for="vigente">Sim</label>
                                            <input type="radio" name="vigente" id="nvigente" value="nvigente"><label for="nvigente">Não</label>
												
											</td>
											
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" width="50%">
												Data cadastro:
											</td>
											<td>
												<!-- <input type="date" name="datainicial" id="datainicial"> -->
												<span id="datainicialGroup">
                                                    <input id="datainicial" type="text" name="datainicial" size="12" class="data" maxlength="10"  title="" style="font-size: 10.6667px;">
                                                    <a id="calendariovdi" href="javascript:janela('<?php echo $URLDataInicio ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
                                                </span>
											
												<!-- <input type="date" name="datafinal" id="datafinal"> -->
												<span id="datafinalGroup">
                                                    <input id="datafinal" type="text" name="datafinal" size="12" class="data" maxlength="10"  title="" style="font-size: 10.6667px;">
                                                    <a id="calendariovdi" href="javascript:janela('<?php echo $URLvDataFim ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
                                                </span>
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td class="textonormal" align="right" colspan="4">
									<input type="button" name="Pesquisar" value="Pesquisar" class="botao" id="btnPesquisar">
									<input type="button" name="Limpar" value="Limpar" class="botao" id="btnLimpa" >
									<!-- <input type="button" name="IncluirContrato" title="Incluir Contrato" value="Incluir Contrato" class="botao" id="btnIncluir"> -->
									<input type="hidden" name="Botao" value="">
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</form>
		<br><br>	
	</body>
	</html>
<?php
 }else{
	
	error_reporting(E_ALL ^ E_NOTICE);
	require_once "ClassContratoPesquisar.php";
	$ObjContrato = new ContratoPesquisar();
	$dadosTipoCompra = $ObjContrato->ListTipoCompra();
	$dadosSituacaoDocumento = $ObjContrato->ListaSituacaoDoc(); 
	$arrayTirar  = array('.',',','-','/');
	 $internet = $_GET['portalCompras'];
	if (!@require_once dirname(__FILE__) . "/TemplateAppPadrao.php") {
		throw new Exception("Error Processing Request - TemplateAppPadrao.php", 1);
	}
	$tpl = new TemplateAppPadrao("CadContratoConsolidadoPesquisar.html","ConsLicitacoesAndamento");
	$tpl->BASEURL_CONTRATO = "http://".$_SERVER['HTTP_HOST'].str_replace('app','',dirname($_SERVER['REQUEST_URI']));
	$tpl->BLOCK_RESULTADO_PESQUISA = 'style="display:none;"';
	$tpl->BTNPESQUISAR = "Pesquisar";
	$tpl->BTNPESQUISAR_VALOR = "Pesquisar";

	if(!empty($dadosTipoCompra)){
		$arrayTodos = array();
		foreach($dadosTipoCompra as $tipoCompra){
			$arrayTodos[] = $tipoCompra->ctpcomcodi;
			if($_POST['origem'] == $tipoCompra->ctpcomcodi && empty($tpl->SELECTEDORI)){
				$tpl->SELECTEDORI = "selected";
			}else{
				$tpl->SELECTEDORI = "";
			}
			$tpl->VALUE_ORIGEM = $tipoCompra->ctpcomcodi;
			$tpl->NOME_ORIGEM  = $tipoCompra->etpcomnome;
			$tpl->block('BLOCK_ORIGEM');
		}
		
		if(empty($_POST['origem'])){
			$tpl->SELECTEDTDORI ="selected";
		}
		$tpl->VALUE_TODOS_ORIGEM = implode(',',$arrayTodos);
	}

	if(!empty($dadosSituacaoDocumento)){
		$arrayTodas = array();
		foreach($dadosSituacaoDocumento as $situacaoDocumento){
			// $arrayTodas[] = $situacaoDocumento->cfasedsequ."-".$situacaoDocumento->csitdcsequ;
			if($_POST['situacao'] == ($situacaoDocumento->cfasedsequ."-".$situacaoDocumento->csitdcsequ) && empty($tpl->SELECTEDSIT)){
				$tpl->SELECTEDSIT ="selected";
			}else{
				$tpl->SELECTEDSIT = "";
			}
			$tpl->VALUE_SITUACAO = $situacaoDocumento->cfasedsequ."-".$situacaoDocumento->csitdcsequ;
			$tpl->NOME_SITUACAO  = $situacaoDocumento->esitdcdesc;
			$tpl->block('BLOCK_SITUACAO');
		}
		if(empty($_POST['situacao'])){
			$tpl->SELECTEDTDSIT ="selected";
		}
		$tpl->VALUE_TODAS_SITUACAO = "";
	}

	if($_POST['Botao'] == "Pesquisar"){
			$tpl->BLOCK_RESULTADO_PESQUISA = 'style="display:block;"';
				$tpl->NUMEROCONTRATO =$_POST['numerocontratoano'];
				$tpl->RAZAO =$_POST['razao'];
				$tpl->OBJETO =$_POST['objeto'];
				$tpl->READONLY = "readonly";
				$tpl->BTNPESQUISAR = "Nova Pesquisa";
				$tpl->BTNPESQUISAR_VALOR = "Nova Pesquisa";
				$tpl->VALUE_ORGAO_LICITANTE = $_POST['Orgao'];
				if($_POST['tprazao'] == "iniciado"){
					$tpl->SELECTEDINI ="selected";
				}
				if($_POST['tprazao'] == "contendo"){
					$tpl->SELECTEDCON ="selected";
				}
				if($_POST['vigente'] == "vigente"){
					$tpl->VIGENTE ="checked";
				}
				if($_POST['vigente'] == "nvigente"){
					$tpl->NVIGENTE ="checked";
				}
				$tpl->VALOR_DATA_INI = $_POST['dataInicial'];
                $tpl->VALOR_DATA_FIM = $_POST['dataFinal'];
				
				$cnpj = str_replace($arrayTirar,'',$_POST['cnpj']);
				$Cpf  = str_replace($arrayTirar,'',$_POST['cpf']);
				$tudook = true;
				if(!$ObjContrato->validaCPF($Cpf) && $_POST['doc'] == "CPF" && !empty($_POST['cpf'])){
					$tpl->exibirMensagemFeedback("O CPF informado não é válido. Informe corretamente.", 1);
					//$tpl->show();
				}
				if(!$ObjContrato->valida_cnpj($cnpj) && $_POST['doc'] == "CNPJ" && !empty($_POST['cnpj'])){
					$tpl->exibirMensagemFeedback("O CNPJ informado não é válido. Informe corretamente.", 1);
					//$tpl->show();
				}
				$ArrayDados = array(
									'numerocontratoano' => $_POST['numerocontratoano'],
									'Orgao'             => $_POST['Orgao'],
									'tipop'             => $_POST['tprazao'],
									'razao'             => $razao = strtoupper($_POST['razao']),
									"origem"         	=> $_POST['origem'],
									'situacao'			=> $_POST['situacao'],
									'objeto'			=> $_POST['objeto'],
									'vigente'			=> $_POST['vigente'],
									'dataInicial'       => $_POST['dataInicial'],
                            		'dataFinal'       	=> $_POST['dataFinal']
								);
				//var_dump($razao);
				
				$dadosTabela = $ObjContrato->Pesquisar($ArrayDados);
				$dadosOrgao = $ObjContrato->GetOrgaoById($ArrayDados,$internet);
				$dadosTabela;
				$tbHtmlOrgao = array();
				$orgao = '';
				$show = false;
				if(!empty($dadosOrgao)){
					foreach($dadosOrgao as $infOrgao){
						$tpl->NOME_ORGAO_PESQUISA = $infOrgao->eorglidesc;
						$qtd=0;
						foreach($dadosTabela as $informacoesTable){	
								if($informacoesTable->eorglidesc == $infOrgao->eorglidesc){
									$show = true;
									$cpfCNPJ        = (!empty($informacoesTable->aforcrccgc))?$informacoesTable->aforcrccgc:$informacoesTable->aforcrccpf;
									$SCC            = "";
									if(!empty($informacoesTable->ccenpocorg) && !empty($informacoesTable->ccenpounid) && !empty($informacoesTable->csolcocodi) && !empty($informacoesTable->asolcoanos)){
										$SCC       = sprintf('%02s', $informacoesTable->ccenpocorg) . sprintf('%02s', $informacoesTable->ccenpounid) . '.' . sprintf('%04s', $informacoesTable->csolcocodi) . '/' . $informacoesTable->asolcoanos;
									}
									$situacaoForne = $ObjContrato->GetSituacaoFornecedor($informacoesTable->aforcrsequ);
									$TipoCompra    = $ObjContrato->GetTipoCompra($informacoesTable->ctpcomcodi);
									$SituacaoContrato  = $ObjContrato->GetSituacaoContrato($informacoesTable->cfasedsequ,$informacoesTable->codsequsituacaodoc);
									$tpl->CDOCPSEQU  = $informacoesTable->cdocpcsequ;
									$tpl->SCC 		 = $SCC;
									$tpl->ECTRPCNUMF  = $informacoesTable->ectrpcnumf;
									$tpl->ECTRPCOBJE = wordwrap($informacoesTable->ectrpcobje, 30, "\n", true);
									$tpl->ETPCOMNOME = $TipoCompra->etpcomnome;
									$tpl->NFORCRRAZS = $informacoesTable->nforcrrazs;
									$tpl->CPFCNPJ    = $ObjContrato->MascarasCPFCNPJ($cpfCNPJ);
									$tpl->DATA_INI    = date("d/m/Y", strtotime($informacoesTable->datainivige));
									$tpl->DATA_FIM    = date("d/m/Y", strtotime($informacoesTable->datafimvige));
									//osmar
									$tpl->ESITDCDESC = $SituacaoContrato->esitdcdesc;
									$tpl->block('CONTRATO_RESULTADO');
									$qtd++;									
								}
								
								$tpl->QTDCONTRATOS = $qtd;
							}

							if(!empty($show)){
								$tpl->block('BLOCK_ORGAO');
								$show=false;
							}
					}
				}else{
					$tpl->block('BLOCK_SEM_OCORRENCIA');
				}
				unset($informacoesTable);
				unset($situacaoForne);
				unset($TipoCompra);
				unset($SituacaoContrato);
				unset($cpfCNPJ);
				unset($SCC);
				//$ObjContrato->DesconectaBanco();
	}
	
	$tpl->show();
}?>
