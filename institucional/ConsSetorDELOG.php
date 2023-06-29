<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsSetorDELOG.php
# Autor:    Rossana Lira
# Data:     28/05/03
# Objetivo: Programa de Consulta do Organograma da DGCO - SETOR DELOG
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
	document.SetorDELOG.Botao.value=valor;
	document.SetorDELOG.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="ConsSetorDELOG.php" method="post" name="SetorDELOG">
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
		    					INFORMAÇÕES ORGANIZACIONAIS - DEPARTAMENTO DE LOGÍSTICA DE MATERIAIS
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
										     	Nome: Ivan Medeiros Alves
			     							</p>
												<p align="justify">
													Fone: 3232-8229
												</p>
												<p align="justify">
												  <?php
											    $Pessoa = urlencode("Ivan Medeiros Alves");
											    $Para   = urlencode("ivanalves@recife.pe.gov.br");
												  ?>
												  E-mail: <a href="../institucional/RotEmailPadrao.php?Pessoa=<?php echo $Pessoa;?>&Para=<?php echo $Para;?>">ivanalves@recife.pe.gov.br</a><br>
												</p>
											</td>
										</tr>
				  	      	<tr>
											<td class="textonegrito" align="left" valign="middle" colspan="2">	ATRIBUIÇÕES </td>
				          	</tr>
				  	      	<tr>
				    	      	<td class="textonormal" colspan="2">
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
									      	I - coordenar as atividades relativas à gestão da logística, compreendendo a armazenagem e distribuição dos
													materiais de uso comum;
				          	   	</p>
												<p align="justify">
									       	II - coordenar a gestão do Almoxarifado Central da Prefeitura do Recife, fixando procedimentos de operação e
													controle dos materiais estocados;
				          	   	</p>
												<p align="justify">
									       	III - fixar procedimentos de operação e controle dos estoques descentralizados e específicos destinados ao
													atendimento da Administração direta;
				          	   	</p>
												<p align="justify">
									       	IV - fixar procedimentos de recebimento e guarda, este último mediante acordo com a Guarda Municipal, dos
													materiais a serem estocados no Almoxarifado Central da Prefeitura do Recife;
								  	   	</p>
												<p align="justify">
									       	V - coordenar, de forma articulada com os demais órgãos da Administração direta, atividades para desenvolver a
													gestão do Almoxarifado Central da Prefeitura do Recife;
								  	   	</p>
								  	   	<p align="justify">
													VII - coordenar o sistema informatizado de controle de estoques;
								  	   	</p>
												<p align="justify">
													VIII - coordenar a produção de informações sobre o abastecimento de bens dos órgãos da Administração direta;
								  	   	</p>
								  	   	<p align="justify">
													IX - subsidiar as decisões do Secretário de Finanças e do Diretor da DGCO quanto aos processos de gestão de
													estoques, armazenagem e distribuição de materiais.
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
