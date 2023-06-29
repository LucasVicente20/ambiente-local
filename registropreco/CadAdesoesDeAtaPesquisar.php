<?php
# -------------------------------------------------------------------------
# Portal da Compras
# # Programa: CadAdesoesDeAtaPesquisa.php
# Autor:    Eliakim Ramos
# Data:     11/07/2022
# -------------------------------------------------------------------------
# Autor:    João Madson
# Data:     06/09/2022
# CR:		268479
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
unset($_SESSION['anexoInseridoOuRetirado']);
unset($_SESSION['dadosItensContrato']);
unset($_SESSION['dadosManter']);
unset($_SESSION['manterItensZerados']);
unset($_SESSION['valorCalculado']);
unset($_SESSION['citelpnuml']);
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

	function selecionar(registro){
		// formPesquisa
		console.log(registro);
		if(registro != '' && registro!= null && registro !='undefined' ){
			$("#regitrocontrato").val(registro);
			$("#formPesquisa").attr('action','./CadContratoManter.php');
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
		$('#containerTbResult').hide(); // Evita uma borda abaixo dos botões antes que seja feita a pesquisa.
		$.post("postDadosAdesoesDeAta.php",{op:"OrgaoGestor"}, function(data){
			$("#orgaoGestor").append(data);
		});
		
		if(show == "show"){
			$("#tdmensagem").show();
			$(".error").html("Atenção!");
			$(".error").css("color","#007fff");
			$(".mensagem-texto").html(mensagem);
		}
        $("#btnPesquisar").on('click', function(){
			 $("#formTablePesquisa").show();
			 $(".loader").show();
			 $(".tablePesquisa").html("");
			 $(".removeresult").remove();
			 const tipoAta = $("input[name='tipo_ata']:checked").val();
			 console.log(tipoAta);
			 const tipoSarp = $("input[name='tipo_sarp']:checked").val();
			 console.log(tipoSarp);
			$.post("postDadosAdesoesDeAta.php",
					{
						op					:"Pesquisa",
						Orgao				:$("#orgaoGestor").val(),
						numeroScc			:$("#numeroScc").val(),
						tipo_ata			:tipoAta,
						tipo_sarp			:tipoSarp,
						Data_inicio			:$("#DataIni").val(),
						Data_fim			:$("#DataFim").val()

					},
				function(data){
					$('#containerTbResult').show();
					$("#divretorno").html(data);
			});
        });

		//Mecanismo que desativa a opção PARTICIPANTE quando selecionado o tipo de ata externa
		$('#radio-externa').on('click', ()=>{
			$('#radio-carona').attr("checked", true);
			$('#radio-participante').attr("disabled", true);
		})
		$('#radio-interna').on('click', ()=>{
			$('#radio-participante').attr("disabled", false);
		})
		$('#btnLimpa').on('click', ()=>{
			$('#radio-participante').attr("disabled", false);
		})
		// Fim do mecanismo

    });

	<?php MenuAcesso(); ?>
	//-->
</script>
<link rel="stylesheet" type="text/css" href="./templates/css/ataAdesao.css<?php echo "?".time();?>">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form action="<?php $programa ?>" id="formPesquisa" method="post" name="formulario">
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
					</a> > Registro de preço  > Processo Adesão Atas
				</td>
			</tr>
			<!-- Fim do Caminho-->
			<!-- Corpo -->
			<tr>
				<td width="150"></td>
				<td class="textonormal" width="70%" style=" position:absolute;" >
					<table width="850px" border="1" id="tabelaMaster" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
						<tr>
							<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="4">
                                            ADESÕES ÀS ATAS DE REGISTRO DE PREÇOS
							</td>
						</tr>
						<tr>
							<td colspan="8">
								<table border="0" width="100%" summary="">
									<tr>
                                        <td class="textonormal" bgcolor="#DCEDF7" width="50%">Número da Solicitação (SCC) : </td>
                                        <td class="textonormal" width="50%">
                                            <input type="text" id="numeroScc" value="<?php echo (!empty($numeroSccAtual)) ? $numeroSccAtual : ''; ?>" name="numeroScc" class="textonormal" />
                                        </td>
                                    </tr>
                                    <tr>
										<td class="textonormal" bgcolor="#DCEDF7" width="50%" height="20">Órgão:</td>
										<td class="textonormal" width="50%">
											<select name="Orgao[]" class="textonormal" id="orgaoGestor">

											</select>
										</td>
									</tr>
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7" width="50%">
											Tipo de Ata* :
                                        </td>
                                        <td class="textonormal" width="50%">
                                            <input type="radio" class = "radioContratos" style= "margin-top: 5px;"  name="tipo_ata" id="radio-interna" value="INTERNA" ><label for="interna">INTERNA</label>
                                            <input type="radio" name="tipo_ata" id="radio-externa" value="EXTERNA"><label for="externa">EXTERNA</label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7" width="50%">
											Tipo de SARP* :
                                        </td>
                                        <td class="textonormal" width="50%">
                                            <input type="radio" class = "radioContratos" style= "margin-top: 5px;"  name="tipo_sarp" id="radio-carona" value="C"><label for="carona">CARONA</label>
                                            <input type="radio" name="tipo_sarp" id="radio-participante" value="P" ><label for="participante">PARTICIPANTE</label>
                                        </td>
                                    </tr>
									<tr>
										<td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Período*</td>
										<td class="textonormal">
											<?php
											$DataMes = DataMes();
											if ($DataIni == "") { $DataIni = $DataMes[0]; }
											if ($DataFim == "") { $DataFim = $DataMes[1]; }
											$URLIni = "../calendario.php?Formulario=formulario&Campo=DataIni";
											$URLFim = "../calendario.php?Formulario=formulario&Campo=DataFim";
											?>
											<input type="text" id="DataIni" name="DataIni" size="10" maxlength="10" value="<?php echo $DataIni;?>" class="textonormal">
											<a href="javascript:janela('<?php echo $URLIni ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
											&nbsp;a&nbsp;
											<input type="text" id="DataFim" name="DataFim" size="10" maxlength="10" value="<?php echo $DataFim;?>" class="textonormal">
											<a href="javascript:janela('<?php echo $URLFim ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td class="textonormal" align="right" colspan="4">
								<input type="button" name="Pesquisar" value="Pesquisar" class="botao" id="btnPesquisar">
								<input type="reset" name="Limpar" value="Limpar" class="botao" id="btnLimpa" >
								<input type="hidden" name="Botao" value="">
							</td>
						</tr>
						<tr id="containerTbResult">
							<td colspan="8" style="padding-left: 0px; padding-right: 0px; border-right-width: 0px; border-left-width: 0px;">
								<spam id="divretorno" style="margin-left: 0px; margin-top: 0px;"></spam>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</form>
</body>
</html>
