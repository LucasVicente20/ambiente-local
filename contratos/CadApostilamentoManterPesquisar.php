<?php
# -------------------------------------------------------------------------
# Portal da Compras
# Programa: CadApostilamentoManterPesquisar.php
# Autor:    Eliakim Ramos | Edson Dionisio
# Data:     28/05/2020
# -------------------------------------------------------------------------
# Autor:    Madson Felix
# Data:     28/04/2021
# CR #246939
# -------------------------------------------------------------------------
# Autor:    Osmar Celestino
# Data:     27/05/2021
# CR #248619
# -------------------------------------------------------------------------
# Autor:    Osmar Celestino
# Data:     12/01/2022
# CR #257662
# -------------------------------------------------------------------------
# Autor:    Lucas Vicente
# Data:     04/05/2022
# CR #262947
# -------------------------------------------------------------------------

# Acesso ao arquivo de funções #
require_once("../funcoes.php");

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso ('/estoques/CadItemDetalhe.php');
AddMenuAcesso ('/compras/'.$programaSelecao);

unset($_SESSION['documento_anexo']);
unset($_SESSION['dadosFornecedor']);
unset($_SESSION['fiscal_selecionado']);
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
		}else{
			for(k in orgao){
				if(orgao[k].eorglidesc != '' && orgao[k].eorglidesc != 'undefined'){
					tableHtml  	 +='<tr class="removeresult">';
					tableHtml  	 +='<td align="center" bgcolor="#BFDAF2" colspan="5" class="titulo3">';
					tableHtml  	 +=orgao[k].eorglidesc;
					tableHtml  	 +='</td>';
					tableHtml  	 +='</tr>';
					tableHtml  	 +='<tr class="removeresult">';
					tableHtml  	 +='	<td class="textonormal" >';
					tableHtml  	 +='<div id="formTablePesquisa"  >';
					tableHtml  	 +='<div class="loader"></div>';
					tableHtml  	 +='<table class="tablePesquisa textonormal" style=" position:relative;  width : 100%; ">';
					tableHtml  	 +="<thead >";
					tableHtml  	 +="<tr>";
					tableHtml  	 +="<td>N° CONTRATO</td>";
					tableHtml  	 +="<td>OBJETO</td>";
					tableHtml  	 +="<td>FORNECEDOR</td>";
					tableHtml  	 +="<td>CNPJ/CPF</td>";				
					tableHtml  	 +="</tr>";
					tableHtml  	 +="</thead>";
					tableHtml  	 +="<tbody>";
					for(i in objJson){
						if(orgao[k].eorglidesc == objJson[i].eorglidesc){
							tableHtml     +="<tr>"; 
							let numeroContrato = objJson[i].ectrpcnumf != ""?objJson[i].ectrpcnumf:"Aguardando Numeração";
							tableHtml     +='<td width="8%"> <a href="javascript:selecionar('+objJson[i].cdocpcsequ+')">'+numeroContrato+'</a></td>';
							tableHtml     +='<td width="49%">'+objJson[i].ectrpcobje+'</td>';
							tableHtml     +='<td>'+objJson[i].nforcrrazs+'</td>';
							tableHtml     +='<td width="14%">'+objJson[i].cpfCNPJ+'</td>';
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
		}
		
			return tableHtml;
	}
	function selecionar(registro){
		// formPesquisa
		console.log(registro);
		//return false;
		if(registro != '' && registro!= null && registro !='undefined' ){
			$("#regitrocontrato").val(registro);
			$("#formPesquisa").attr('action','./CadApostilamentoManter.php');
			$("#formPesquisa").submit();
		}
	}
	<!--
    $(document).ready(function() {
        const urlStr = window.location.href;
		const url = new URL(urlStr);
		const mensagem = url.searchParams.get("m");
		const show = url.searchParams.get("h");
		const mensagemk = '<?php echo $_POST['mensagem'];?>';
            if(mensagemk != ''){
                $('html, body').animate({scrollTop:0}, 'slow');
                 $(".mensagem-texto").html(mensagemk);
                 $(".error").css("color","#007fff");
                 $(".error").html("Atenção!");
                 $("#tdmensagem").show();
            }
        $('#numeroScc').mask('9999.9999/9999');
        $('#codcontrato').mask('9999.9999/9999');
        $('#cnpj').mask('99.999.999/9999-99');
        $('#cpf').mask('999.999.999-99');

		$.post("postDadosPesquisaApostilamento.php",{op:"OrgaoGestor"}, function(data){
				$("#orgaoGestor").append(data);
			});
		
		if(show == "show"){
			$("#tdmensagem").show();
			$(".error").html("Atenção!");
			$(".error").css("color","#007fff");
			$(".mensagem-texto").html(mensagem);
		}
		$('#radio-cpf').on('click',function(){
			$("#cnpj").val('');
            $("#mostracpf").show();
			$("#mostracnpj").hide();
			//$("#mostracnpj").css("marginTop", "-20px"); TESTE DA CR 262947
        });
        $('#radio-cnpj').on('click',function(){
			$("#mostracnpj").show();
			$("#cpf").val('');
            $("#mostracpf").hide();
			//$("#mostracpf").css("marginTop", "-20px"); TESTE DA CR 262947
        });
        $("#btnLimpa").on('click', function(){
            window.location.reload();
        });
        $("#btnIncluir").on('click', function(){
            window.location.href="./CadApostilamentoIncluir.php";
        });
        $("#btnPesquisar").on('click', function(){
			 $("#formTablePesquisa").show();
			 $(".loader").show();
			 $(".tablePesquisa").html("");
			 $(".removeresult").remove();
			const documento = $("input[name='cpf-cnpj']:checked").val();
			$.post("postDadosPesquisaApostilamento.php", //Substituição do postDadosPesquisaMedição CR#238403
					{
						op					:"PesquisaContrato",
						cpf					:$("#cpf").val(),
						cnpj				:$("#cnpj").val(),
						numerocontratoano	:$("#codcontrato").val(),
						Orgao				:$("#orgaoGestor").val(),
						numeroScc			:$("#numeroScc").val(),
						doc					:documento
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
			$.post("postDadosPesquisaApostilamento.php",{op:"Fornecerdor",cpf:$("#cpf").val(),cnpj:$("#cnpj").val(),doc:documento}, function(data){
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
	<input type="hidden" name="idcontrato" id="regitrocontrato">
		<br><br><br><br><br>
		<table cellpadding="3" border="0" summary="">
			<!-- Caminho -->
			<tr>
				<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
				<td align="left" class="textonormal" colspan="2">
					<font class="titulo2">|</font>			
					<a href="../index.php">
							<font color="#000000">Página Principal</font>
					</a> > Contratos  > Apostilamento > Manter
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
				<td class="textonormal" style=" position:absolute;  width : 53%; ">
					<table width="850px" border="1" id="tabelaMaster" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
						<tr>
							<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="4">
                                            MANTER APOSTILAMENTO CONTRATO
							</td>
						</tr>
						<tr>
							<td class="textonormal" align="left" colspan="4">
							Preencha os dados abaixo para efetuar a pesquisa e clique no número do contrato desejado. Os contratos cancelados, conclusos ou sem numeração não serão exibidos.
							</td>
						</tr>
						<tr>
							<td colspan="8">
								<table border="0" width="100%" summary="">
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7" width="50%">Número do Contrato/Ano</td>
                                        <td class="textonormal"  width="50%">
                                            <input type="text" id="codcontrato" value="<?php echo (!empty($numeroSccAtual)) ? $numeroSccAtual : ''; ?>" name="codcontrato" class="textonormal" />
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
                                            <input type="radio" name="cpf-cnpj" style= "margin-top: 5px;" id="radio-cpf" value="CPF" ><label for="cpf">CPF</label>
                                            <input type="radio" name="cpf-cnpj" id="radio-cnpj" value="CNPJ" checked><label for="CNPJ" >CNPJ</label>
                                            <div class="textonormal" id="mostracnpj" >
												<input type="text" name="cnpj" id="cnpj">
                                                <a href="#" class="btn-pesquisa-fornecedor">
                                                    <img src="../midia/lupa.gif" border="0">
                                                </a>
												<br>
										    </div>
                                            <div class="textonormal" id="mostracpf" >
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