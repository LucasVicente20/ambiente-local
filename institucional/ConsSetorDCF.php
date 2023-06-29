<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsSetorDCF.php
# Autor:    Rossana Lira
# Data:     28/05/03
# Objetivo: Programa de Consulta do Organograma da DGCO - SETOR DCF
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/institucional/ConsOrganograma.php' );
AddMenuAcesso( '/institucional/RotEmailPadrao.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao   = $_POST['Botao'];
}

# Redireciona para página do Organograma #
if( $Botao == "Voltar" ){
	  header("location: ConsOrganograma.php");
	  exit;
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
	document.SetorDCF.Botao.value=valor;
	document.SetorDCF.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="ConsSetorDCF.php" method="post" name="SetorDCF">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Institucional > Organograma
    </td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Corpo -->
	<tr>
		<td width="100"></td>
		<td class="textonormal">
      <table  border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="">
        <tr>
	      	<td class="textonormal">
	        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" colspan="2" class="titulo3">
		    					INFORMAÇÕES ORGANIZACIONAIS - DIVISÃO DE CREDENCIAMENTO DE FORNECEDORES
		          	</td>
		        	</tr>
							<tr>
							  <td>
							  	<table border="0" summary="">
							  		<tr>
											<td class="textonegrito" align="left" valign="middle" colspan="2">	RESPONSÁVEL </td>
				          	</tr>
										<tr>
											<td class="textonormal" colspan="2">
				      	    		<p align="justify">
										     	Nome: José Raimundo
			     							</p>
												<p align="justify">
													Fone: 3232-8275
												</p>
												<p align="justify">
												  <?php
											    $Pessoa = urlencode("José Raimundo");
											    $Para   = urlencode("joseraimundo@recife.pe.gov.br");
												  ?>
												  E-mail: <a href="../institucional/RotEmailPadrao.php?Pessoa=<?php echo $Pessoa;?>&Para=<?php echo $Para;?>">joseraimundo@recife.pe.gov.br</a><br>
												</p>
											</td>
										</tr>
										<tr>
											<td class="textonegrito"  align="left" valign="middle" colspan="2">	ATRIBUIÇÕES </td>
				          	</tr>
										<tr>
				    	      	<td class="textonormal"  colspan="2">
				      	    		<p align="justify">
								     			- Regulamentação da área:
												</p>
												<p align="justify">
								    			- Competência:
												</p>
											</td>
										</tr>
										<tr>
											<td class="textonormal">&nbsp;</td>
											<td class="textonormal">
												<p align="justify">
													I - executar atividades relacionadas ao Sistema de Credenciamento Unificado de Fornecedores da Prefeitura do
													Recife - SICREF e ao SOFIN, compreendendo registro, exclusão e alteração cadastral dos fornecedores da
													Administração pública municipal;
												</p>
												<p align="justify">
													II - promover o compartilhamento de informações, entre a DCF e as áreas tributárias e de arrecadação da Prefeitura,
													quanto à situação fiscal dos fornecedores cadastrados no SICREF e no SOFIN;
												</p>
												<p align="justify">
													III - divulgar, na página oficial da Prefeitura do Recife na Internet, todos os editais de licitação da Administração
													direta, bem como expedir as cartas-convites aos fornecedores;
												</p>
												<p align="justify">
													IV - disponibilizar, aos interessados, no espaço físico da Divisão, os editais referentes às licitações processadas pelas
													Comissões de Licitação da Administração direta;
												</p>
												<p align="justify">
													V - subsidiar as decisões, no âmbito do Departamento de Licitações e Contratos, envolvendo os registros cadastrais
													dos fornecedores da Administração pública municipal, bem como a divulgação e disponibilização dos editais aos
													fornecedores, nos termos dos incisos III e IV deste artigo.
												</p>
											</td>
					        	</tr>
					        </table>
					      </td>
					    </tr>
					    <tr>
   	  	  			<td class="textonormal" align="right">
        	      	<input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
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
