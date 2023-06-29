<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsSetorASEX.php
# Autor:    Rossana Lira
# Data:     28/05/03
# Objetivo: Programa de Consulta do Organograma da DGCO - SETOR ASEX
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
	document.SetorASEX.Botao.value=valor;
	document.SetorASEX.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="ConsSetorASEX.php" method="post" name="SetorASEX">
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
		    					INFORMAÇÕES ORGANIZACIONAIS - ASSESSORIA EXECUTIVA
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
										     	Nome: Karla Vacemberg Paulino
			     							</p>
												<p align="justify">
													Fone: 3232-8374
												</p>
												<p align="justify">
												  <?php
											    $Pessoa = urlencode("Karla Vacemberg Paulino");
											    $Para   = urlencode("karlav@recife.pe.gov.br");
												  ?>
												  E-mail: <a href="../institucional/RotEmailPadrao.php?Pessoa=<?php echo $Pessoa;?>&Para=<?php echo $Para;?>">karlav@recife.pe.gov.br</a><br>
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
													I - executar atividades de secretariado em suporte a toda a estrutura da DGCO;
												</p>
												<p align="justify">
													II - executar a gestão de protocolo da DGCO, compreendendo o recebimento, registro, tramitação, distribuição e
													expedição de todos os documentos, processos e correspondências que nela tramitem;
												</p>
												<p align="justify">
													III - executar a gestão do arquivo local da DGCO, compreendendo controle, guarda e pesquisa;
												</p>
												<p align="justify">
													IV - gerenciar as correspondências da DGCO;
												</p>
												<p align="justify">
													V - manter o controle das publicações no Diário Oficial do Município;
												</p>
												<p align="justify">
													VI - executar atividades relativas ao atendimento ao público em geral da DGCO, inclusive, de recepção, telefonia e
													outros meios de comunicação;
												</p>
												<p align="justify">
													VII - viabilizar o suprimento administrativo de materiais de serviços de suporte às atividades de competência da
													DGCO, seus departamentos, divisões e assessorias;
												</p>
												<p align="justify">
													VIII - redigir a documentação oficial solicitada pelo Diretor da DGCO;
												</p>
												<p align="justify">
													X - subsidiar as decisões, no âmbito da estrutura da DGCO, envolvendo atividades de suporte administrativo.
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
