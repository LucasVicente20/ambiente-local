<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsSetorDSL.php
# Autor:    Rossana Lira
# Data:     28/05/03
# Objetivo: Programa de Consulta do Organograma da DGCO - SETOR DSL
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/institucional/ConsOrganograma.php' );

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
	document.SetorDSL.Botao.value=valor;
	document.SetorDSL.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="ConsSetorDSL.php" method="post" name="SetorDSL">
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
		    					INFORMAÇÕES ORGANIZACIONAIS - DIVISÃO DE SUPORTE À LICITAÇÕES
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
										     	Nome: Mirella Oliveira De Matos Barboza
			     							</p>
												<p align="justify">
													Fone: 3232-8235
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
													I - executar atividades relativas à fase preparatória das licitações promovidas pelos órgãos da Administração direta;
												</p>
												<p align="justify">
													II - prestar suporte administrativo às Comissões de Licitação antes, durante e após as sessões;
												</p>
												<p align="justify">
													III - viabilizar o trâmite legal dos processos licitatórios, desde o pedido de abertura até a homologação;
												</p>
												<p align="justify">
													IV - subsidiar as decisões, no âmbito do Departamento de Licitações e Contratos, envolvendo informações relativas
													aos procedimentos licitatórios;
												</p>
												<p align="justify">
													V - executar atividades de suporte administrativo tais como reprografia, impressão e encadernação, visando atender
													necessidade de toda a estrutura da DGCO.
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
