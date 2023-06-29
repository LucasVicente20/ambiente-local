<?php
#--------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RotTipoFornecimentoIntegrar.php
# Autor:    Rossana Lira
# Data:     01/03/04
# Objetivo: Programa que integra a tabela de tipo de fornecimento(Oracle) com a
#           Classe Fornecimento do PostGre
# OBS.:     Tabulação 2 espaços
#--------------------------------------------------------------------------------------
# Acesso ao arquivo de funções #
include "../funcoes.php";
# Executa o controle de segurança #
session_start();
Seguranca();
# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao               				= $_POST['Botao'];
		$TipoFornecimento						= $_POST['TipoFornecimento'];
		$TipoGrupo	     				    = $_POST['TipoGrupo'];
		$Grupo	 										= $_POST['Grupo'];
		$Classe											= $_POST['Classe'];
		$CheckTipoFor     		  	  = $_POST['CheckTipoFor'];
		$TipoFor              		  = $_POST['TipoFor'];
		$CodGrupo                		= $_POST['CodGrupo'];
		$CodClasse                	= $_POST['CodClasse'];
}else{
		$Erro = $_GET['Erro'];
}
# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;
if( $Botao == "Integrar" ){
	  $Mens     = 0;
    $Mensagem = "Informe: ";
    if( $TipoFornecimento == "" ){
        if ($Mens == 1){$Mensagem.=", ";}
    		$Mens      = 1;
    		$Tipo      = 2;
		    $Mensagem .= "<a href=\"javascript:document.Integracao.TipoFornecimento.focus();\" class=\"titulo2\">Tipo de Fornecimento</a>";
    }
    if( $Grupo == "" ){
        if ($Mens == 1){$Mensagem.=", ";}
    		$Mens      = 1;
    		$Tipo      = 2;
		    $Mensagem .= "<a href=\"javascript:document.Integracao.Grupo.focus();\" class=\"titulo2\" >Grupo</a>";
    }
    if( $Classe == "" ){
        if ($Mens == 1){$Mensagem.=", ";}
    		$Mens      = 1;
    		$Tipo      = 2;
		    $Mensagem .= "<a href=\"javascript:document.Integracao.Classe.focus();\" class=\"titulo2\" >Classe</a>";
    }
		if( $Mens == 0 ){
				# Verifica na tabela de Classe da existência do Tipo de Fornecimento #
				$db 		= Conexao();
				$sql    = "SELECT COUNT(*) FROM SFPC.TBCLASSEMATERIALSERVICO ";
				$sql   .= " WHERE CCLAMSTIPF = $TipoFornecimento ";
				$result = $db->query($sql);
				if( PEAR::isError($result) ){
						$db->query("ROLLBACK");
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						$Linha     = $result->fetchRow();
						if ($Linha[0] == 0) {
								# Atualiza a tabela de Classe com o Tipo de Fornecimento #
								$db->query("BEGIN TRANSACTION");
								$sql    = "UPDATE SFPC.TBCLASSEMATERIALSERVICO ";
								$sql   .= "   SET CCLAMSTIPF = $TipoFornecimento, TCLAMSULAT = '".date("Y-m-d H:i:s")."' ";
								$sql   .= " WHERE CGRUMSCODI = $Grupo AND CCLAMSCODI = $Classe ";
								$result = $db->query($sql);
								if( PEAR::isError($result) ){
										$db->query("ROLLBACK");
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$db->query("COMMIT");
										$TipoFornecimento		 = "";
										$Grupo               = "";
										$Classe              = "";
										$Mens                = 1;
						    		$Tipo                = 1;
								    $Mensagem            = "Integração Realizada com Sucesso";
								}
								$db->query("END TRANSACTION");
						} else {
								$Mens     = 1;
				    		$Tipo     = 2;
						    $Mensagem = "Integração Cancelada! Este Tipo de Fornecimento já foi Relacionado";
						}
				}
				$db->disconnect();
		}
		$Botao = "";
}elseif( $Botao == "Retirar" ){
		if( count($TipoFor) != 0 ){
				for( $i=0; $i< count($TipoFor); $i++ ){
						if( $CheckTipoFor[$i] == "" ){
								$Qtd++;
								$CheckTipoFor[$i] = "";
								$TipoFor[$Qtd-1]  = $TipoFor[$i];
						}else{
								# Retira um tipo de fornecimento da tabela de Classe #
								$db = Conexao();
								$db->query("BEGIN TRANSACTION");
								$sql    = "UPDATE SFPC.TBCLASSEMATERIALSERVICO ";
								$sql   .= "   SET CCLAMSTIPF = NULL, TCLAMSULAT = '".date("Y-m-d H:i:s")."' ";
								$sql   .= " WHERE CGRUMSCODI = $CodGrupo[$i] AND CCLAMSCODI = $CodClasse[$i]";
								$result = $db->query($sql);
								if( PEAR::isError($result) ){
										$db->query("ROLLBACK");
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}
								$db->query("COMMIT");
								$db->query("END TRANSACTION");
								$db->disconnect();
						}
				}
				$CheckTipoFor = array_slice($CheckTipoFor,0,$Qtd);
				$TipoFor      = array_slice($TipoFor,0,$Qtd);
				if( count($TipoFor) == 1 ){ $TipoFor == ""; }
		}
		$Botao = "";
}
if ( $Botao == "" ){
	# Verifica se há integração entre tipo de fornecimento (Oracle) e classes de fornecimento (Portal)#
	$db 	  = Conexao();
	$sql    = "SELECT COUNT(CCLAMSTIPF) FROM SFPC.TBCLASSEMATERIALSERVICO WHERE CCLAMSTIPF <> 0 ";
	$result = $db->query($sql);
	if( PEAR::isError($result) ){
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	}else{
			$Linha 	= $result->fetchRow();
			$Existe = $Linha[0];
	}
	$db->disconnect();
}
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="" >
<!--
function enviar(valor){
	document.Integracao.Botao.value=valor;
	document.Integracao.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css" >
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="RotTipoFornecimentoIntegrar.php" method="post" name="Integracao" >
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="" >
  <!-- Caminho -->
  <tr>
    <td width="100" ><img border="0" src="../midia/linha.gif" alt="" ></td>
    <td align="left" class="textonormal" colspan="2" >
      <font class="titulo2" >|</font>
      <a href="../index.php" ><font color="#000000" >Página Principal</font></a> > Materiais > Integração
    </td>
  </tr>
  <!-- Fim do Caminho-->
	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
  <tr>
  	<td width="150" ></td>
		<td align="left" colspan="2" ><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->
	<!-- Corpo -->
	<tr>
		<td width="100" ></td>
		<td class="textonormal" >
      <table  border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" >
        <tr>
	      	<td class="textonormal" >
	        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" class="textonormal" summary="" >
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" >
		    					INTEGRAÇÃO DA TABELA DE CLASSE COM TIPO DE FORNECIMENTO
		          	</td>
		        	</tr>
		        	<tr>
	    	      	<td class="textonormal" >
	      	    		<p align="justify" >
	        	    		Para fazer a integração da tabela de Tipo de Fornecimento (Oracle) com a tabela de classes,
	        	    		relacione os campos abaixo e clique no botão "Integrar".<br>
	        	    		Quando houver alguma integração feita, será exibida uma lista, para retirar um ou mais itens
	        	    		dessa lista marque o(s) item(s) desejados e clique no botão "Retirar".
	          	   	</p>
	          		</td>
		        	</tr>
		        	<tr>
	  	        	<td>
	    	      		<table class="textonormal" border="0" align="left" summary="" >
					        	<tr>
				              <td class="textonormal" bgcolor="#DCEDF7" >Tipo de Fornecimento (Oracle)* </td>
					        		<td>
							        	<!-- Carregar o array com os tipos de fornecimento cadastrados no Oracle -->
									      <select name="TipoFornecimento" class="textonormal" >
									        <option value="">Selecione um Tipo de Fornecimento...</option>
									        <option value="1" <?php Seleciona($TipoFornecimento,1);?>>1- Fornecimento de água mineral</option>
									        <option value="2" <?php Seleciona($TipoFornecimento,2);?>>2- Fornecimento de aparelho elétrico</option>
									        <option value="3" <?php Seleciona($TipoFornecimento,3);?>>3- Fornecimento de aparelho eletrodoméstico</option>
									        <option value="4" <?php Seleciona($TipoFornecimento,4);?>>4- Fornecimento de aparelho eletrônico</option>
									        <option value="5" <?php Seleciona($TipoFornecimento,5);?>>5- Fornecimento de auto-peças</option>
									        <option value="6" <?php Seleciona($TipoFornecimento,6);?>>6- Fornecimento de bebida</option>
									        <option value="7" <?php Seleciona($TipoFornecimento,7);?>>7- Fornecimento de crédito combustível / cartão</option>
									        <option value="8" <?php Seleciona($TipoFornecimento,8);?>>8- Fornecimento de equipamento de informática</option>
								          <option value="9" <?php Seleciona($TipoFornecimento,9);?>>9- Fornecimento de gênero alimentício</option>
								          <option value="10" <?php Seleciona($TipoFornecimento,10);?>>10- Fornecimento de instrumento musical</option>
								          <option value="11" <?php Seleciona($TipoFornecimento,11);?>>11- Fornecimento de máquina industrial</option>
								          <option value="12" <?php Seleciona($TipoFornecimento,12);?>>12- Fornecimento de material de construção</option>
								          <option value="13" <?php Seleciona($TipoFornecimento,13);?>>13- Fornecimento de material de consumo</option>
								          <option value="14" <?php Seleciona($TipoFornecimento,14);?>>14- Fornecimento de material de expediente</option>
								          <option value="15" <?php Seleciona($TipoFornecimento,15);?>>15- Fornecimento de material de limpeza</option>
								          <option value="16" <?php Seleciona($TipoFornecimento,16);?>>16- Fornecimento de material de livraria</option>
								          <option value="17" <?php Seleciona($TipoFornecimento,17);?>>17- Fornecimento de material de papelaria</option>
								          <option value="18" <?php Seleciona($TipoFornecimento,18);?>>18- Fornecimento de material de segurança</option>
								          <option value="19" <?php Seleciona($TipoFornecimento,19);?>>19- Fornecimento de material de telecomunicação</option>
								          <option value="20" <?php Seleciona($TipoFornecimento,20);?>>20- Fornecimento de material elétrico</option>
								          <option value="21" <?php Seleciona($TipoFornecimento,21);?>>21- Fornecimento de material esportivo</option>
								          <option value="22" <?php Seleciona($TipoFornecimento,22);?>>22- Fornecimento de material fotográfico</option>
								          <option value="23" <?php Seleciona($TipoFornecimento,23);?>>23- Fornecimento de material hidráulico</option>
													<option value="24" <?php Seleciona($TipoFornecimento,24);?>>24- Fornecimento de material permanente</option>
													<option value="25" <?php Seleciona($TipoFornecimento,25);?>>25- Fornecimento de material têxtil</option>
													<option value="26" <?php Seleciona($TipoFornecimento,26);?>>26- Fornecimento de passagem aérea</option>
													<option value="27" <?php Seleciona($TipoFornecimento,27);?>>27- Fornecimento de produto de perfumaria/cosmético</option>
													<option value="28" <?php Seleciona($TipoFornecimento,28);?>>28- Fornecimento de produto descartável</option>
													<option value="29" <?php Seleciona($TipoFornecimento,29);?>>29- Fornecimento de produto farmacêutico</option>
													<option value="30" <?php Seleciona($TipoFornecimento,30);?>>30- Fornecimento de produto hospitalar</option>
													<option value="31" <?php Seleciona($TipoFornecimento,31);?>>31- Fornecimento de produto médico</option>
													<option value="32" <?php Seleciona($TipoFornecimento,32);?>>32- Fornecimento de produto odontológico</option>
													<option value="33" <?php Seleciona($TipoFornecimento,33);?>>33- Fornecimento de produto têxtil</option>
													<option value="34" <?php Seleciona($TipoFornecimento,34);?>>34- Fornecimento de seguro</option>
													<option value="35" <?php Seleciona($TipoFornecimento,35);?>>35- Fornecimento de suprimento de informática</option>
													<option value="36" <?php Seleciona($TipoFornecimento,36);?>>36- Fornecimento de tíquete combustível</option>
													<option value="37" <?php Seleciona($TipoFornecimento,37);?>>37- Fornecimento de tíquete refeição</option>
													<option value="38" <?php Seleciona($TipoFornecimento,38);?>>38- Fornecimento de veículo</option>
													<option value="39" <?php Seleciona($TipoFornecimento,39);?>>39- Locação de embarcação</option>
													<option value="40" <?php Seleciona($TipoFornecimento,40);?>>40- Locação de mão-de-obra</option>
													<option value="41" <?php Seleciona($TipoFornecimento,41);?>>41- Locação de máquina copiadora</option>
													<option value="42" <?php Seleciona($TipoFornecimento,42);?>>42- Locação de rádio de comunicação</option>
													<option value="43" <?php Seleciona($TipoFornecimento,43);?>>43- Locação de rádios troncalizados</option>
													<option value="44" <?php Seleciona($TipoFornecimento,44);?>>44- Locação de veículo</option>
													<option value="45" <?php Seleciona($TipoFornecimento,45);?>>45- Serviços automotivos</option>
													<option value="46" <?php Seleciona($TipoFornecimento,46);?>>46- Serviços contábeis</option>
													<option value="47" <?php Seleciona($TipoFornecimento,47);?>>47- Serviços de arquitetura</option>
													<option value="48" <?php Seleciona($TipoFornecimento,48);?>>48- Serviços de Buffê/ Coquetel</option>
													<option value="49" <?php Seleciona($TipoFornecimento,49);?>>49- Serviços de carpintaria</option>
													<option value="50" <?php Seleciona($TipoFornecimento,50);?>>50- Serviços de climatização</option>
													<option value="51" <?php Seleciona($TipoFornecimento,51);?>>51- Serviços de conservação</option>
													<option value="52" <?php Seleciona($TipoFornecimento,52);?>>52- Serviços de consultoria</option>
													<option value="53" <?php Seleciona($TipoFornecimento,53);?>>53- Serviços de dedetização</option>
													<option value="54" <?php Seleciona($TipoFornecimento,54);?>>54- Serviços de descupinização</option>
													<option value="55" <?php Seleciona($TipoFornecimento,55);?>>55- Serviços de desinfeccção</option>
													<option value="56" <?php Seleciona($TipoFornecimento,56);?>>56- Serviços de desratização</option>
													<option value="57" <?php Seleciona($TipoFornecimento,57);?>>57- Serviços de engenharia - construção</option>
													<option value="58" <?php Seleciona($TipoFornecimento,58);?>>58- Serviços de engenharia - reforma</option>
													<option value="59" <?php Seleciona($TipoFornecimento,59);?>>59- Serviços de entrega/coleta</option>
													<option value="60" <?php Seleciona($TipoFornecimento,60);?>>60- Serviços de esgoto</option>
													<option value="61" <?php Seleciona($TipoFornecimento,61);?>>61- Serviços de higienização</option>
													<option value="62" <?php Seleciona($TipoFornecimento,62);?>>62- Serviços de impermeabilização</option>
													<option value="63" <?php Seleciona($TipoFornecimento,63);?>>63- Serviços de instalação/manutenção elétrica</option>
													<option value="64" <?php Seleciona($TipoFornecimento,64);?>>64- Serviços de instalação/manutenção hidráulica</option>
													<option value="65" <?php Seleciona($TipoFornecimento,65);?>>65- Serviços de instalação/manutenção hidrosanitária</option>
													<option value="66" <?php Seleciona($TipoFornecimento,66);?>>66- Serviços de jardinagem</option>
													<option value="67" <?php Seleciona($TipoFornecimento,67);?>>67- Serviços de Limpeza urbana</option>
													<option value="68" <?php Seleciona($TipoFornecimento,68);?>>68- Serviços de manutenção de ar condicionado</option>
													<option value="69" <?php Seleciona($TipoFornecimento,69);?>>69- Serviços de manutenção de veículo</option>
													<option value="70" <?php Seleciona($TipoFornecimento,70);?>>70- Serviços de marcenaria</option>
													<option value="71" <?php Seleciona($TipoFornecimento,71);?>>71- Serviços de pavimentação</option>
													<option value="72" <?php Seleciona($TipoFornecimento,72);?>>72- Serviços de pintura</option>
													<option value="73" <?php Seleciona($TipoFornecimento,73);?>>73- Serviços de processamento de dados</option>
													<option value="74" <?php Seleciona($TipoFornecimento,74);?>>74- Serviços de publicidade</option>
													<option value="75" <?php Seleciona($TipoFornecimento,75);?>>75- Serviços de rastreamento de notícia</option>
													<option value="76" <?php Seleciona($TipoFornecimento,76);?>>76- Serviços de recrutamento/seleção de pessoal</option>
													<option value="77" <?php Seleciona($TipoFornecimento,77);?>>77- Serviços de reforma</option>
													<option value="78" <?php Seleciona($TipoFornecimento,78);?>>78- Serviços de reforma de móveis</option>
													<option value="79" <?php Seleciona($TipoFornecimento,79);?>>79- Serviços de Saneamento básico</option>
													<option value="80" <?php Seleciona($TipoFornecimento,80);?>>80- Serviços de Serralharia</option>
													<option value="81" <?php Seleciona($TipoFornecimento,81);?>>81- Serviços de telecomunicações</option>
													<option value="82" <?php Seleciona($TipoFornecimento,82);?>>82- Serviços de telemática</option>
													<option value="83" <?php Seleciona($TipoFornecimento,83);?>>83- Serviços de topografia</option>
													<option value="84" <?php Seleciona($TipoFornecimento,84);?>>84- Serviços de transporte</option>
													<option value="85" <?php Seleciona($TipoFornecimento,85);?>>85- Serviços de vidraçaria</option>
													<option value="86" <?php Seleciona($TipoFornecimento,86);?>>86- Serviços gerais</option>
													<option value="87" <?php Seleciona($TipoFornecimento,87);?>>87- Serviços gráficos</option>
													<option value="88" <?php Seleciona($TipoFornecimento,88);?>>88- Serviços jurídicos</option>
													<option value="89" <?php Seleciona($TipoFornecimento,89);?>>89- Locação de imóveis</option>
													<option value="90" <?php Seleciona($TipoFornecimento,90);?>>90- Fornecimento de produto químico</option>
													<option value="91" <?php Seleciona($TipoFornecimento,91);?>>91- Fornecimento de máquina copiadora</option>
													<option value="92" <?php Seleciona($TipoFornecimento,92);?>>92- Serviços de vigilância</option>
													<option value="93" <?php Seleciona($TipoFornecimento,93);?>>93- Fornecimento de equipamentos de vigilância</option>
													<option value="94" <?php Seleciona($TipoFornecimento,94);?>>94- Fornecimento de produtos Laboratoriais</option>
													<option value="95" <?php Seleciona($TipoFornecimento,95);?>>95- Serviços de transporte de água potável</option>
													<option value="96" <?php Seleciona($TipoFornecimento,96);?>>96- Serviços de informática</option>
													<option value="97" <?php Seleciona($TipoFornecimento,97);?>>97- Fornecimento de materiais plásticos</option>
													<option value="98" <?php Seleciona($TipoFornecimento,98);?>>98- Serviços de terraplanagem</option>
													<option value="99" <?php Seleciona($TipoFornecimento,99);?>>99- Fornecimento de materiais ortopédicos</option>
													<option value="100" <?php Seleciona($TipoFornecimento,100);?>>100- Fornecimento de gases industriais</option>
													<option value="101" <?php Seleciona($TipoFornecimento,101);?>>101- Serviços jornalísticos</option>
													<option value="102" <?php Seleciona($TipoFornecimento,102);?>>102- Serviços de Leiloeiro</option>
													<option value="103" <?php Seleciona($TipoFornecimento,103);?>>103- Fornecimento de equipamento de Som</option>
													<option value="104" <?php Seleciona($TipoFornecimento,104);?>>104- Fornecimento de produtos cirúrgicos</option>
													<option value="105" <?php Seleciona($TipoFornecimento,105);?>>105- Fornecimento de combustíveis</option>
													<option value="106" <?php Seleciona($TipoFornecimento,106);?>>106- Serviços de hotelaria</option>
													<option value="107" <?php Seleciona($TipoFornecimento,107);?>>107- Fornecimento de produtos veterinários</option>
													<option value="108" <?php Seleciona($TipoFornecimento,108);?>>108- Fornecimento de materiais de Sinalização</option>
													<option value="109" <?php Seleciona($TipoFornecimento,109);?>>109- Serviços de Sinalização</option>
													<option value="110" <?php Seleciona($TipoFornecimento,110);?>>110- Serviços de auditoria</option>
													<option value="111" <?php Seleciona($TipoFornecimento,111);?>>111- Serviços de advocacia</option>
													<option value="112" <?php Seleciona($TipoFornecimento,112);?>>112- Fornecedor - Sistema de Contratos</option>
													<option value="113" <?php Seleciona($TipoFornecimento,113);?>>113- Serviços de instalação e manutenção telefônica</option>
													<option value="114" <?php Seleciona($TipoFornecimento,114);?>>114- Fornecimento de material telefônico</option>
													<option value="115" <?php Seleciona($TipoFornecimento,115);?>>115- Fornecimento de material didático</option>
													<option value="116" <?php Seleciona($TipoFornecimento,116);?>>116- Fornecimento de material audio visual</option>
													<option value="117" <?php Seleciona($TipoFornecimento,117);?>>117- Fornecimento de material de copa e cozinha</option>
													<option value="118" <?php Seleciona($TipoFornecimento,118);?>>118- Fornecimento de miudezas em geral</option>
													<option value="119" <?php Seleciona($TipoFornecimento,119);?>>119- Fornecimento de artesanato</option>
													<option value="120" <?php Seleciona($TipoFornecimento,120);?>>120- Fornecimento de brinquedos</option>
													<option value="121" <?php Seleciona($TipoFornecimento,121);?>>121- Locação de cadeiras e toldos</option>
												</select>
											</td>
										</tr>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7" width="40%">Tipo de Grupo*</td>
				              <td class="textonormal">
				              	<input type="radio" name="TipoGrupo" value="M" onClick="javascript:document.Integracao.Critica.value=0;document.Integracao.submit();" <?php if( $TipoGrupo == "" or $TipoGrupo == "M" ){ echo "checked"; } ?> > Material
				              	<input type="radio" name="TipoGrupo" value="S" onClick="javascript:document.Integracao.Critica.value=0;document.Integracao.submit();" <?php if( $TipoGrupo == "S" ){ echo "checked"; }?> > Serviço
				              </td>
										</tr>
										<tr>
				              <td class="textonormal" bgcolor="#DCEDF7" >Grupo* </td>
											<td>
			                  <input type="hidden" name="Critica" value="1" >
				              	<select name="Grupo" class="textonormal" onChange="javascript:document.Integracao.Critica.value=0;document.Integracao.submit();" >
				              		<option value="" >Selecione um Grupo...</option>
				              		<?php
				              		$db   = Conexao();
													$sql  = "SELECT CGRUMSCODI,EGRUMSDESC FROM SFPC.TBGRUPOMATERIALSERVICO WHERE ";
													if( $TipoGrupo == "M" or $TipoGrupo == "" ){
															$sql .= "FGRUMSTIPO = 'M'";
													}else{
															$sql .= "FGRUMSTIPO = 'S'";
													}
													$sql .= "ORDER BY 2";
													$res  = $db->query($sql);
												  if( PEAR::isError($res) ){
														  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
													}else{
															while( $Linha = $res->fetchRow() ){
					          	      			$Descricao   = substr($Linha[1],0,75);
					          	      			if( $Linha[0] == $Grupo ){
										    	      			echo"<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
								      	      		}else{
										    	      			echo"<option value=\"$Linha[0]\" >$Descricao</option>\n";
								      	      		}
					                  	}
													}
			  	              	$db->disconnect();
				              		?>
				              	</select>
				              </td>
				            </tr>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7" >Classe* </td>
				              <td class="textonormal" >
				              	<select name="Classe" class="textonormal" <?php echo $Classe;?>>
				              		<option value="" >Selecione uma Classe...</option>
				              		<?php
				              		if( $Grupo != "" ){
						              		$db  = Conexao();
															$sql = "SELECT CCLAMSCODI,ECLAMSDESC FROM SFPC.TBCLASSEMATERIALSERVICO WHERE CGRUMSCODI = $Grupo ";
															$sql.= "ORDER BY 2";
															$res = $db->query($sql);
														  if( PEAR::isError($res) ){
																  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
															}else{
																	while( $Linha = $res->fetchRow() ){
							          	      			$Descricao = substr($Linha[1],0,75);
							          	      			if( $Linha[0] == $Classe){
												    	      			echo"<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
										      	      		}else{
																					echo"<option value=\"$Linha[0]\" >$Descricao</option>\n";
										      	      		}
								                	}
															}
					  	              	$db->disconnect();
					  	            }
				              		?>
				              	</select>
				              </td>
				            </tr>
									</table>
								</td>
		        	</tr>
	  	      	<tr>
   	  	  			<td class="textonormal" align="right" >
									<input type="hidden" name="Existe" value="<?php echo $Existe; ?>" >
			            <input type="button" value="Integrar" class="botao" onclick="javascript:enviar('Integrar');" >
			            <input type="hidden" name="Botao" value="" >
		          	</td>
		        	</tr>
		        	<?php if( $Existe != 0 ){ ?>
		        	<tr>
	  	        	<td>
	    	      		<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" width="100%" >
										<tr>
											<td class="titulo3" bgcolor="#DCEDF7" height="20" width="5%" >&nbsp;</td>
											<td class="titulo3" bgcolor="#DCEDF7" height="20" width="10%" >TIPO DE FORNECIMENTO</td>
											<td class="titulo3" bgcolor="#DCEDF7" height="20" width="45%" >GRUPO</td>
											<td class="titulo3" bgcolor="#DCEDF7" height="20" width="45%" >CLASSE</td>
										</tr>
							    	<?php
                  	# Mostra as classes com tipos de fornecimentos integrados #
                		$db     = Conexao();
                		$sql    = "SELECT A.CGRUMSCODI, A.CCLAMSCODI, A.ECLAMSDESC, A.CCLAMSTIPF, B.EGRUMSDESC ";
                		$sql   .= "  FROM SFPC.TBCLASSEMATERIALSERVICO A, SFPC.TBGRUPOMATERIALSERVICO B ";
                		$sql   .= " WHERE A.CGRUMSCODI = B.CGRUMSCODI AND A.CCLAMSTIPF IS NOT NULL ";
										$sql   .= " ORDER BY A.CCLAMSTIPF ";
                		$result = $db->query($sql);
                		if( PEAR::isError($result) ){
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												$Rows = $result->numRows();
												for( $i=0; $i< $Rows;$i++ ){
														$Linha = $result->fetchRow();
		          	      			echo "<tr>\n";
														echo "<td class=\"textonormal\" bgcolor=\"#F7F7F7\" height=\"20\" width=\"5%\" align=\"center\" valign=\"top\" >\n";
														echo "	<input type=\"checkbox\" name=\"CheckTipoFor[$i]\" value=\"$Linha[3]\" >\n";
														echo "</td>\n";
		          	      			echo "<td class=\"textonormal\" bgcolor=\"#F7F7F7\" height=\"20\" width=\"10%\" valign=\"top\">\n";
		          	      			echo "	$Linha[3]\n";
		          	      			echo "	<input type=\"hidden\" name=\"TipoFor[$i]\" value=\"$Linha[3]\" >\n";
														echo "</td>\n";
		          	      			echo "<td class=\"textonormal\" bgcolor=\"#F7F7F7\" height=\"20\" width=\"45%\" valign=\"top\">\n";
		          	      			echo "	$Linha[4]\n";
		          	      			echo "	<input type=\"hidden\" name=\"CodGrupo[$i]\" value=\"$Linha[0]\" >\n";
														echo "</td>\n";
		          	      			echo "<td class=\"textonormal\" bgcolor=\"#F7F7F7\" height=\"20\" width=\"45%\" valign=\"top\">\n";
		          	      			echo "	$Linha[2]\n";
		          	      			echo "	<input type=\"hidden\" name=\"CodClasse[$i]\" value=\"$Linha[1]\" >\n";
														echo "</td>\n";
		          	      			echo "</tr>\n";
			                	}
			              }
  	              	$db->disconnect();
      	            ?>
									</table>
								</td>
		        	</tr>
							<tr>
	  	        	<td>
	    	      		<table border="0" cellpadding="3" cellspacing="0" class="textonormal" width="100%" summary="" >
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="26%" >Total de Integrações</td>
											<td class="textonormal" ><?php if ($i == "") { echo '0';} else { echo $i; } ?></td>
						      	</tr>
									</table>
								</td>
		        	</tr>
			      	<tr>
				  			<td class="textonormal" align="right" >
			        	  <input type="button" value="Retirar" class="botao" onclick="javascript:enviar('Retirar');" >
			        	</td>
			      	</tr>
		        	<?php } ?>
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
function Seleciona($TipoFornecimento, $Valor){
	if( $TipoFornecimento == $Valor ){ echo "selected"; }
}
?>
