<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsSetorDEC.php
# Autor:    Rossana Lira
# Data:     28/05/03
# Objetivo: Programa de Consulta do Organograma da DGCO - SETOR DEC
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
	document.SetorDEC.Botao.value=valor;
	document.SetorDEC.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="ConsSetorDEC.php" method="post" name="SetorDEC">
<br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2"><br>
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
		    					INFORMAÇÕES ORGANIZACIONAIS - DIVISÃO DE EXECUÇÃO DE COMPRAS
		          	</td>
		        	</tr>
							<tr>
							  <td>
							  	<table border="0" summary="">
							  		<tr>
											<td class="textonegrito"  align="left" valign="middle" colspan="2">	RESPONSÁVEL </td>
				          	</tr>
										<tr>
											<td class="textonormal"  colspan="2">
				      	    		<p align="justify">
										     	Nome: Célia Lúcia Alencar Falcão
			     							</p>
												<p align="justify">
													Fone: 3232-8229
												</p>
												<p align="justify">
												  <?php
											    $Pessoa = urlencode("Célia Lúcia Alencar Falcão");
											    $Para   = urlencode("dgco@recife.pe.gov.br");
												  ?>
												  E-mail: <a href="../institucional/RotEmailPadrao.php?Pessoa=<?php echo $Pessoa;?>&Para=<?php echo $Para;?>">dgco@recife.pe.gov.br</a><br>
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
													I - executar as atividades relativas ao relacionamento comercial entre a Administração direta e seus fornecedores,
													efetivos e potenciais, nos processos de compras;
												</p>
												<p align="justify">
													II - gerir os processos de compras dispensadas de licitação pelo valor, a partir do preenchimento da Requisição de
													Materiais e Serviços pelos órgãos da Administração direta;
												</p>
												<p align="justify">
													III - gerenciar as atas do Sistema de Registro de Preços, bem como dar início aos processos de compras
													correspondentes;
												</p>
												<p align="justify">
													IV - executar as atividades de operação e atualização dos dados contidos no Portal Eletrônico de Compras da
													Prefeitura do Recife;
												</p>
												<p align="justify">
													V - comunicar à Assessoria Jurídica da SEFIN a inadimplência de fornecedores na entrega de materiais, conforme
													informações prestadas pelo Almoxarifado Central, após insucesso em prévia negociação;
												</p>
												<p align="justify">
													VI - subsidiar o processo de tomada de decisões, no âmbito do Departamento de Relações Comerciais, que
													envolvam informações oriundas dos processos de execução de compras.
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
