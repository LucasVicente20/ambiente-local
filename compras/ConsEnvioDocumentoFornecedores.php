<?php
# -------------------------------------------------------------------------
# Portal de compras
# Programa: ConsEnvioDocumentoFornecedor.php
# Autor:    João Madson
# Data:     22/03/2020
# Objetivo: CR #245334
# -------------------------------------------------------------------------

# Acesso ao arquivo de funções #
require_once("funcoesCompras.php");

# Executa o controle de segurança #
session_start();
Seguranca();
# Adiciona páginas no MenuAcesso #
AddMenuAcesso ('/estoques/CadItemDetalhe.php');
AddMenuAcesso ('/compras/'.$programaSelecao);

function MascarasCPFCNPJ($valor){
    $checaSeFormatado = strripos($valor, "-");
    if($checaSeFormatado == true){
        return $valor;
    }
    if(strlen($valor) == 11){
        $mascara = "###.###.###-##";
        for($i =0; $i <= strlen($mascara); $i++){
            if($mascara[$i] == "#"){
                if(isset($valor[$k])){
                $maskared .= $valor[$k++];
                }
            }else{
                $maskared .= $mascara[$i];
            }
        }
        return $maskared;
    }
    if(strlen($valor) == 14){
        $mascara = "##.###.###/####-##";
        for($i =0; $i <= strlen($mascara); $i++){
            if($mascara[$i] == "#"){
                if(isset($valor[$k])){
                $maskared .= $valor[$k++];
                }
            }else{
                $maskared .= $mascara[$i];
            }
        }
        // var_dump($maskared);
        return $maskared;
    }
}

if($_SERVER['REQUEST_METHOD'] == "POST"){
	$_SESSION['dados']['sequencial']   = $_POST['SeqSolicitacao'];
	$_SESSION['dados']['AssuntoEmail']   = $_POST['AssuntoEmail'];
	$_SESSION['dados']['textoEmail']   = $_POST['textoEmail'];		
}

$dadosFornecedoresMat = ($_SESSION['forn']['mat']); 
$dadosFornecedoresServ = ($_SESSION['forn']['serv']);
$quantFornMat = count($dadosFornecedoresMat);
$quantFornServ = count($dadosFornecedoresServ);
$listaDeFornecedores = "";

for($i=0; $i<$quantFornMat; $i++){
	#Organiza o email
	if((!empty($dadosFornecedoresMat[$i]->nforcrmail) && !is_null($dadosFornecedoresMat[$i]->nforcrmail)) || (!empty($dadosFornecedoresMat[$i]->nforcrmai2) && !is_null($dadosFornecedoresMat[$i]->nforcrmai2))){
		if($dadosFornecedoresMat[$i]->nforcrmail == $dadosFornecedoresMat[$i]->nforcrmai2){
			$emailFmat = $dadosFornecedoresMat[$i]->nforcrmail.","; 
		}elseif(!empty($dadosFornecedoresMat[$i]->nforcrmail) && !is_null($dadosFornecedoresMat[$i]->nforcrmail)){
			$emailFmat = $dadosFornecedoresMat[$i]->nforcrmail.","; 
		}elseif(!empty($dadosFornecedoresMat[$i]->nforcrmai2) && !is_null($dadosFornecedoresMat[$i]->nforcrmai2)){
			$emailFmat = $dadosFornecedoresMat[$i]->nforcrmai2.","; 
		}
		#Retira a ultima vírgula
		$emailFmat = substr($emailFmat, 0, -1);
		#Coloca espaços depois da virgula
		$emailFmat = str_replace(",", ", ", $emailFmat);
	}

	#Monta a Linha de Informação
	if(!is_null($dadosFornecedoresMat[$i]->aforcrccgc) && !empty($dadosFornecedoresMat[$i]->aforcrccgc)){
		$listaDeFornecedores .= "CNPJ:".MascarasCPFCNPJ($dadosFornecedoresMat[$i]->aforcrccgc)." - ".$dadosFornecedoresMat[$i]->nforcrrazs." - E-mail(s): ".$emailFmat."<br>";
	}else{
		$listaDeFornecedores .= "CPF: ".MascarasCPFCNPJ($dadosFornecedoresMat[$i]->aforcrccpf)." - ".$dadosFornecedoresMat[$i]->nforcrrazs." - E-mail(s): ".$emailFmat."<br>";
	}
	
}
for($i=0; $i<$quantFornServ; $i++){
	#Organiza o email
	if((!empty($dadosFornecedoresServ[$i]->nforcrmail) && !is_null($dadosFornecedoresServ[$i]->nforcrmail)) || (!empty($dadosFornecedoresServ[$i]->nforcrmai2) && !is_null($dadosFornecedoresServ[$i]->nforcrmai2))){
		if($dadosFornecedoresServ[$i]->nforcrmail == $dadosFornecedoresServ[$i]->nforcrmai2){
			$emailFserv = $dadosFornecedoresServ[$i]->nforcrmail.","; 
		}elseif(!empty($dadosFornecedoresServ[$i]->nforcrmail) && !is_null($dadosFornecedoresServ[$i]->nforcrmail)){
			$emailFserv = $dadosFornecedoresServ[$i]->nforcrmail.","; 
		}elseif(!empty($dadosFornecedoresServ[$i]->nforcrmai2) && !is_null($dadosFornecedoresServ[$i]->nforcrmai2)){
			$emailFserv = $dadosFornecedoresServ[$i]->nforcrmai2.","; 
		}
		#Retira a ultima vírgula
		$emailFserv = substr($emailFserv, 0, -1);
		#Coloca espaços depois da virgula
		$emailFserv = str_replace(",", ", ", $emailFserv);
	}
	#Monta a linha de informação
	if(!is_null($dadosFornecedoresServ[$i]->aforcrccgc) && !empty($dadosFornecedoresServ[$i]->aforcrccgc)){
		$listaDeFornecedores .= "CNPJ: ".MascarasCPFCNPJ($dadosFornecedoresServ[$i]->aforcrccgc)." - ".$dadosFornecedoresServ[$i]->nforcrrazs." - E-mail(s): ".$emailFserv."<br>" ;
	}else{
		$listaDeFornecedores .= "CPF: ".MascarasCPFCNPJ($dadosFornecedoresServ[$i]->aforcrccpf)." - ".$dadosFornecedoresServ[$i]->nforcrrazs." - E-mail(s): ".$emailFserv."<br>" ;
	}
	
}
$ErroPrograma = __FILE__;

#Comandos dos botões buscam os emails separadamente porém pela mesma função para evitar perca de dados mantendo  centralização da função 

?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" src="../import/jquery/jquery-1.7.2.min.js" type="text/javascript"></script>
<script language="javascript" src="../import/jquery/jquery.maskmoney.js" type="text/javascript"></script>
<script language="javascript" src="../import/jquery/jquery.maskedinput.js" type="text/javascript"></script>
<script language="javascript" type="">
	<!--
	function enviar(valor){
		if(valor == "voltar"){
			document.formulario.action = "ConsEnvioDocumento.php";
			document.formulario.submit();
		}
	}
	
	function AbreJanela(url,largura,altura){
		window.open(url,'paginadetalhe','status=no,scrollbars=yes,left=15,top=15,width='+largura+',height='+altura);
	}

	function onClickDesativado(erro){
		alert(erro);
	}

    $(document).ready(function() {
        //$('#numeroAno').mask('9999/9999');
        $('#numeroScc').mask('9999.9999/9999');
    });

	<?php MenuAcesso(); ?>
	//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form action="<?=$programa?>" method="post" name="formulario"  enctype="multipart/form-data">
	<input type="hidden" name="sequencial" value="<?php echo $_POST['sequencial'];?>">
	<input type="hidden" name="AssuntoEmail" value="<?php echo $_POST['AssuntoEmail'];?>">
	<input type="hidden" name="textoEmail" value="<?php echo $_POST['textoEmail'];?>">
		<br><br><br><br><br>
		<table cellpadding="3" border="0" summary="">
			<!-- Caminho -->
			<tr>
				<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
				<td align="left" class="textonormal" colspan="2">
					<font class="titulo2">|</font>
					<a href="../index.php">
						<font color="#000000">Página Principal</font>
					</a> > Compras > Enviar Documentos
				</td>
			</tr>
			<!-- Fim do Caminho-->
			<!-- Erro -->
			<?php if ($Mens == 1) { ?>
				<tr>
					<td width="150"></td>
					<td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
				</tr>
			<?php } ?>
			<!-- Fim do Erro -->
			<!-- Corpo -->
			<tr>
				<td width="150"></td>
				<td class="textonormal">
					<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
						<tr>
							<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="4">
								ENVIAR DOCUMENTOS - EXIBIR FORNECEDORES
							</td>
						</tr>
						<tr>
							<td class="textonormal" colspan="4">
								<p align="justify">
								Para copiar o(s) interessado(s), posicione o mouse antes da primeira letra do primeiro nome, clique e arraste até o último nome. Com os nomes selecionados use a tecla (CTRL) + C e onde desejar colá-los use a tecla (CTRL) + V.
								</p>
							</td>
						</tr>
						<tr>
							<td colspan="4">
								<table border="0" width="100%" summary="">
                                    <tr>
                                        <td class="textonormal">
                                            <?php
												echo $listaDeFornecedores;
											?>
                                        </td>
                                    </tr>
								</table>
							</td>
						</tr>
						<tr>
							<td class="textonormal" align="right" colspan="4">
								<input type="button" name="Voltar" value="Voltar" class="botao" onClick="javascript:enviar('voltar')">
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
