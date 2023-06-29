<?php
# -------------------------------------------------------------------------
# Portal da Compras
# # Programa: CadAdesoesDeAtaFileUpload.php
# Autor:    Eliakim Ramos
# Data:     11/07/2022
# -------------------------------------------------------------------------
# Autor:    João Madson
# Data:     06/09/2022
# CR:		268479
# -------------------------------------------------------------------------
# Acesso ao arquivo de funções #
 require_once("../funcoes.php");
 require_once("./ClassAdesaoAtaPesquisa.php");

 # Executa o controle de segurança #
 session_start();
 Seguranca();
 ini_set('display_errors', '1');


 $objClassAdesaoAta = new ClassAdesaoAtaPesquisa();
 $orgao = $objClassAdesaoAta->GetOrgaoById($_GET['orgao']);
if($_SESSION['token'] == $_POST['token']){
	if($_SERVER['REQUEST_METHOD'] == "POST"){
		if($_POST['Incluir']){
			$parametros = $objClassAdesaoAta->GetParametrosGerais();
			$olsSeqcrpaddcodi = $objClassAdesaoAta->GetSeqArquivo();
			$arrayRetira = array('.','/');
			$nameDir =  $GLOBALS ['CAMINHO_UPLOADS'] ."registropreco";
			$ex      = array('.doc','.docx','.pdf','.xls','.xlsx', '.rar', '.zip');
			$SizeMaxPermitido  = $parametros->qpargetmad * 1024;
			$uploads_dir = $nameDir."/".$_POST['nomepasta'];
			$crpaddcodi = $olsSeqcrpaddcodi->oldseq+1;
				if(is_dir($uploads_dir)){
					foreach($_FILES["docs"]["error"] as $key => $error){
						if ($error == UPLOAD_ERR_OK) {
							$extArq = explode('.',$_FILES['docs']['name'][$key]);
							$sizeArq = $_FILES['docs']['size'][$key];
							if(!in_array('.'.strtolower2($extArq[count($extArq)-1]),$ex) ){
								$msgError="Tipo de arquivo não suportado. Selecione somente documento com extensão .doc, .docx, .pdf, .xls, .xlsx, .rar, .zip";
								
							}
							// if($sizeArq > $SizeMaxPermitido){
							// 	 $msgError=" Este arquivo é muito grande. Tamanho Máximo: ".$SizeMaxPermitido." Kb. | Tamanho do seu Arquivo: ".strval($sizeArq)."kb.";
							// 	
							// }
							if($sizeArq == 0){
								$msgError="Este arquivo está vazio. Tamanho Máximo: ".$SizeMaxPermitido." Kb. | Tamanho do seu Arquivo: ".strval($sizeArq)."kb.";
								
							}
							
							if(strlen($_FILES['documento']['name'][$i]) >= 100){
								$msgError="Nome do Arquivo deve ser menor que 100 caracetes.";
								
							}
							$tmp_name = $_FILES["docs"]["tmp_name"][$key];
							
							$name = basename($_FILES["docs"]["name"][$key]);
							if(file_exists("$uploads_dir/$name")){
								$name = time().$name;
							}
							if(empty($msgError)){
								$moveuoarquivo = move_uploaded_file($tmp_name, "$uploads_dir/$name");
								if($moveuoarquivo && file_exists("$uploads_dir/$name") ){
									$dados = array(
													'csolcosequ' => $objClassAdesaoAta->anti_injection($_GET['csolcosequ']),
													'erpaddnome' => $name,
													'crpaddcodi' => $crpaddcodi
									);
									$objClassAdesaoAta->insertAdsaoAta($dados);
								}
							}
						}else{
							$msgError="Problemas no upload, acione a equipe responsável";
								
						}
						$crpaddcodi++;
					}
				}else{
					$pastaCriada = mkdir($uploads_dir,0777);
					if($pastaCriada){
						foreach($_FILES["docs"]["error"] as $key => $error){
							if ($error == UPLOAD_ERR_OK) {
								$extArq = explode('.',$_FILES['docs']['name'][$key]);
								$sizeArq = $_FILES['docs']['size'][$key];
								if(!in_array('.'.strtolower2($extArq[count($extArq)-1]),$ex) ){
									$msgError="Tipo de arquivo não suportado. Selecione somente documento com extensão .doc, .docx, .pdf, .xls, .xlsx, .rar, .zip";
									
								}
								if($sizeArq > $SizeMaxPermitido){
									$msgError=" Este arquivo é muito grande. Tamanho Máximo: ".$SizeMaxPermitido." Kb. | Tamanho do seu Arquivo: ".strval($sizeArq)."kb.";
									
								}
								if($sizeArq == 0){
									$msgError="Este arquivo está vazio. Tamanho Máximo: ".$SizeMaxPermitido." Kb. | Tamanho do seu Arquivo: ".strval($sizeArq)."kb.";
									
								}
								
								if(strlen($_FILES['documento']['name'][$i]) >= 100){
									$msgError="Nome do Arquivo deve ser menor que 100 caracetes.";
									
								}
								$tmp_name = $_FILES["docs"]["tmp_name"][$key];
								
								$name = basename($_FILES["docs"]["name"][$key]);
								if(file_exists("$uploads_dir/$name")){
									$name = time().$name;
								}
								if(empty($msgError)){
									$moveuoarquivo = move_uploaded_file($tmp_name, "$uploads_dir/$name");
									if($moveuoarquivo && file_exists("$uploads_dir/$name") ){
										$dados = array(
														'csolcosequ' => $objClassAdesaoAta->anti_injection($_GET['csolcosequ']),
														'erpaddnome' => $name,
														'crpaddcodi' => $crpaddcodi
										);
										$objClassAdesaoAta->insertAdsaoAta($dados);
									}
								}
							}else{
								$msgError="Problemas no upload, acione a equipe responsável";
								
							}
							$crpaddcodi++;
						}
					} else {
						$msgError="Problemas na criação da pasta "; //$uploads_dir;
					}
				}
		} 
		if($_POST['Excluir']){
			foreach($_POST['documentos'] as $doc){
				$keys = explode('-',$doc);
				$dados = array('csolcosequ' => $keys[0], 'crpaddcodi'=> $keys[1]);
				$result = $objClassAdesaoAta->ExcluirArquivo($dados);
			}
		}

	}
}
$arquivos = $objClassAdesaoAta->GetAllArquivo($objClassAdesaoAta->anti_injection($_GET['csolcosequ']));
unset($_SERVER['REQUEST_METHOD']);
unset($_POST['Incluir']);
unset($_POST['Excluir']);
unset($_SESSION['token']);

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

	<!--
    $(document).ready(function() {
        const urlStr = window.location.href;
		const url = new URL(urlStr);
		const mensagem = url.searchParams.get("m");
		const show = url.searchParams.get("h");
        $('#numeroScc').mask('9999.9999/9999');
        $('#numerocontratoano').mask('9999.9999/9999');
		$.post("postDadosAdesoesDeAta.php",{op:"OrgaoGestor"}, function(data){
				$("#orgaoGestor").append(data);
			});
		
		if(show == "show"){
			$("#tdmensagem").show();
			$(".error").html("Atenção!");
			$(".error").css("color","#007fff");
			$(".mensagem-texto").html(mensagem);
		}
		$("#btnVoltar").on("click",function(){
			window.location.href = "CadAdesoesDeAtaPesquisar.php";
		});
		$("#btnExcluir").on("click",function(){
			if(window.confirm("Você realmente Excluir esse arquivo?"))
			{
				$("#Excluir").val("Excluir");
				$("#formIncluir").submit();
			}
		});
		$("#btnIncluir").on("click",function(){
			var input = document.getElementById('docs');
			var tbmsg = document.getElementById('msgerror');
			var msg = "";
			var msgTexto = "";
			for(var i=0; i<input.files.length; i++){
				if(input.files[i].size > 838860800){
					msgTexto += "<br/> Arquivo muito grande "+input.files[i].name;
				}
			}
			if(msgTexto != ""){
				msg = '<td width="150"></td>';
                msg += '<td align="left" colspan="2" id="tdmensagem">';
                msg += '<div class="mensagem">';
                msg += '<div class="error">';
                msg += 'Error!';
                msg += '</div>';
                msg += '<span class="mensagem-texto">';
                msg += msgTexto;
                msg +='</span>';
                msg +='</div>';
                msg +='</td>';
				tbmsg.style.display = "contents";
				tbmsg.innerHTML = msg;
				return false;
			}
			
			$("#Incluir").val("Incluir");
			$("#formIncluir").submit();
		});

    });

	<?php MenuAcesso(); ?>
	//-->
</script>
<link rel="stylesheet" type="text/css" href="./templates/css/ataAdesao.css<?php echo "?".time();?>">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form action="CadAdesoesDeAtaFileUpload.php?scc=<?php echo $_GET['scc'];?>&ata=<?php echo $_GET['ata'];?>&tipo=<?php echo $_GET['tipo'];?>&orgao=<?php echo $_GET['orgao'];?>&carpnosequ=<?php echo $_GET['carpnosequ'];?>&csolcosequ=<?php echo $_GET['csolcosequ'];?>" 
	      id="formIncluir" method="post" name="formulario" enctype="multipart/form-data" >
	<input type="hidden" name="nomepasta" value="<?php echo str_replace($arrayRetira,'',$_GET['csolcosequ']);?>">
	<?php $_SESSION['token'] = md5(time());?>
	<input type="hidden" name="token" value="<?php echo $_SESSION['token'];?>">
	<!-- <input type="hidden" name="nomepasta" value="<?php //echo str_replace($arrayRetira,'',$_GET['carpnosequ']);?>"> -->
	<input type="hidden" name="Incluir" id="Incluir">
	<input type="hidden" name="Excluir" id="Excluir">
		<br><br><br><br><br>
		<table cellpadding="3" border="0" summary="">
			<!-- Caminho -->
			<tr>
				<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
				<td align="left" class="textonormal" colspan="2">
					<font class="titulo2">|</font>
					<a href="../index.php">
							<font color="#000000">Página Principal</font>
					</a> > Registro de preço  > Adesão Atas
				</td>
			</tr>
			<!-- Fim do Caminho-->
			<?php
			if($msgError){?>
				<tr>
					<td width="150"></td>
					<td align="left" colspan="2" id="tdmensagem">
						<div class="mensagem">
							<div class="error">
								Error!
							</div>
							<span class="mensagem-texto">
								<?php echo $msgError;?>
							</span>
						</div>
					</td>
				</tr>
			<?php unset($msgError);
			}?>
			    <tr id="msgerror" style="display: none;">
					
				</tr>
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
							<td>
								Para incluir um documento, localize o arquivo e clique no botão "Incluir". Para apagar o(s) documeto(s),
								selecione-o(s) e clique no botão "Excluir"

							</td>
						</tr>
						<tr>
							<td colspan="8">
								<table border="0" width="100%" summary="" class="textonormal">
									<tr>
                                        <td class="textonormal" bgcolor="#DCEDF7" width="50%">Número da SCC : </td>
                                        <td class="textonormal" width="50%">
                                            <?php echo !empty($_GET['scc']) ? $_GET['scc'] : "0000.0000/0000"; ?>
                                        </td>
                                    </tr>
                                    <tr>
										<td class="textonormal" bgcolor="#DCEDF7" width="50%" height="20">Órgão:</td>
										<td class="textonormal" width="50%">
											<?php echo !empty($orgao->eorglidesc) ? $orgao->eorglidesc : "";?>
										</td>
									</tr>
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7" width="50%">
											Tipo de Ata :
                                        </td>
                                        <td class="textonormal" width="50%">
                                            <?php echo !empty($_GET['ata']) ? $_GET['ata'] : "";?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7" width="50%">
											Tipo de SARP :
                                        </td>
                                        <td class="textonormal" width="50%">
											<?php
												 if(!empty($_GET['tipo'])) {
													switch($_GET['tipo']){
														case "P":
															echo "PARTICIPANTE";
														break;
														case "C":
															echo "CARONA";
														break;
													}
												 }
												 else
												 {
													echo "";
												 }
											?>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7" width="50%">
											Arquivo :
                                        </td>
                                        <td class="textonormal" width="50%">
										<input type="file" id="docs" name="docs[]" multiple accept=".doc,.docx,.pdf,xls,.xlsx,.rar,.zip">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7" width="50%">
											Documentos do processo de adesão:
                                        </td>
                                        <td class="textonormal" width="50%">
											<table  border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" style="width:100%;  border:1px;">
												<thead>
													<tr>
														<th valign="top" bgcolor="#F7F7F7" class="textonegrito"></th>
														<th valign="top" bgcolor="#F7F7F7" class="textonegrito">DOCUMENTOS</th>
													</tr>
												</thead>
												<tbody >
													<?php if (!empty($arquivos)){ 
																foreach($arquivos as $arquivo){
													?>
																<tr>
																	<td valign="top" bgcolor="#F7F7F7" class="textonegrito"><input type="checkbox" name="documentos[]" value="<?php echo $arquivo->csolcosequ.'-'.$arquivo->crpaddcodi;?>" /></td>
																	<td valign="top" bgcolor="#F7F7F7" class="textonegrito"><?php echo $arquivo->erpaddnome;?></td>
																</tr>


													<?php 		 }
														   }
													?>

												</tbody>
											</table>
                                        </td>
                                    </tr>
								</table>
							</td>
						</tr>
						<tr>
							<td class="textonormal" align="right" colspan="4">
								<input type="button" value="Incluir" class="botao" id="btnIncluir">
								<input type="button" value="Excluir" class="botao" id="btnExcluir" >
								<input type="button" name="voltar" value="Voltar"   class="botao" id="btnVoltar">
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</form>
</body>
</html>
