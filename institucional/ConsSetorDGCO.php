<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsSetorDGCO.php
# Autor:    Rossana Lira
# Data:     28/05/03
# Objetivo: Programa de Consulta do Organograma da DGCO
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
	document.SetorDLC.Botao.value=valor;
	document.SetorDLC.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="ConsSetorDGCO.php" method="post" name="SetorDLC">
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
		    					INFORMAÇÕES ORGANIZACIONAIS DA DIRETORIA GERAL DE COMPRAS - DLC
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
										     	Nome: Breno José Baracuhy De Melo
			     							</p>
												<p align="justify">
													Fone: 3232-8374
												</p>
												<p align="justify">
												  <?php
											    $Pessoa = urlencode("Breno José Baracuhy de Melo");
											    $Para   = urlencode("breno@recife.pe.gov.br");
												  ?>
												  E-mail: <a href="../institucional/RotEmailPadrao.php?Pessoa=<?php echo $Pessoa;?>&Para=<?php echo $Para;?>">breno@recife.pe.gov.br</a><br>
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
														I - propor e desenvolver diretrizes e ações destinadas à desburocratização, modernização e maior transparência dos
														processos de aquisição de bens, contratação de serviços e gestão de estoques no âmbito da Administração pública
														municipal;
				          	   	</p>
												<p align="justify">
														II - supervisionar as relações comerciais entre os órgãos e entidades da Administração pública municipal e seus
														fornecedores, efetivos e potenciais, nos processos de aquisição de bens e contratação de serviços;
				          	   	</p>
												<p align="justify">
														III - supervisionar as atividades relativas à gestão da logística, compreendendo a armazenagem e distribuição dos
														materiais de uso ou consumo da Administração direta;
				          	   	</p>
												<p align="justify">
														IV - subsidiar as decisões do Secretário de Finanças relativamente aos processos licitatórios, bem como às atividades
														das Comissões de Licitação da Administração direta;
				          	   	</p>
												<p align="justify">
														V - promover medidas destinadas à uniformização procedimental para os processos de compras, licitações, contratos
														e gestão de estoques, no âmbito da Administração pública municipal;
				          	   	</p>
												<p align="justify">
														VI - subsidiar as atividades do Secretário de Finanças na coordenação dos processos licitatórios, no âmbito da
														Administração direta, assim como os de dispensa e inexigibilidade de licitação;
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
