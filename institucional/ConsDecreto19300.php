<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsDecreto19300.php
# Autor:    Rossana Lira
# Data:     05/09/03
# Objetivo: Programa de Consulta do Decreto 19300
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/institucional/ConsLegislacaoDecretos.php' );

if( $Botao == "Voltar" ){
	  header("location: ConsLegislacaoDecretos.php");
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
	document.Decreto19300.Botao.value=valor;
	document.Decreto19300.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="ConsDecreto19300.php" method="post" name="Decreto19300">
<br><br><br><br><br>
<table cellpadding="3" border="0">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif"></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Institucional > Legislação > Decretos
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
	        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" class="textonormal" summary="">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
		    					DECRETO Nº 19.300 /2002
		          	</td>
		        	</tr>
							<tr>
							  <td>
							  	<table>
										<tr>
											<td class="textonegrito">
												<p align="right">
													Estabelece medidas desburocratizadoras para a celebração de contratos no âmbito da Administração Municipal e delega competência.
													<br><br>
												</p>
			     						</td>
			     					</tr>
			     					<tr>
			     						<td>
												<p align="justify" class="textonormal">
													O PREFEITO DO RECIFE, no exercício da competência que lhe é outorgada pelo art. 54, inciso IV, da Lei Orgânica do Município do Recife, e <br><br>
													CONSIDERANDO a necessidade de desburocratização dos procedimentos de contratação no âmbito da Administração Municipal; <br><br>
													CONSIDERANDO a necessidade de criação de uma alçada para assinatura dos contratos no âmbito da Administração Municipal, <br><br>
													D E C R E T A: <br>
												</p>
											</td>
										</tr>
										<tr>
			     						<td>
												<p align="justify" class="textonormal">
													Art. 1º - Os contratos para a compra de bens móveis e para a contratação de serviços firmados pelo Município do Recife deverão ser assinados:
													<br><br>
													I - pelo contratado, pelo Secretário da Pasta, pelo Secretário de Assuntos Jurídicos e pelo Secretário de Finanças, quando se tratar de contratos cujo valor não ultrapasse o limite estabelecido no Artigo 23, Inciso II, alínea b da Lei Federal no 8.666/93;
													<br><br>
													II - pelo contratado, pelo Secretário da Pasta, pelo Secretário de Assuntos Jurídicos, pelo Secretário de Finanças e pelo Prefeito, quando se tratar de contratos cujo valor seja superior ao estabelecido no Artigo 23, Inciso II, alínea b da Lei Federal no 8.666/93.
													<br><br>
													§ 1o - Nos casos de negócios cujo valor não atinja o limite de dispensa de licitação o instrumento de contrato poderá ser substituído por nota de empenho ou termo simplificado de contrato, exceto quando se tratar de obrigações diferidas.
													<br><br>
													§ 2o - Os contratos temporários previstos na Lei no 15.612/92, serão firmados pelo contratado, pelo secretário da pasta interessada e pelos Secretários de Administração, Assuntos Jurídicos e Finanças.
													<br><br>
													Art. 2º - Ficam revogados os incisos I, II, III, V e VI do artigo 7º do Decreto no 18.116, de 23 de dezembro de 1998.
													<br><br>
													Art. 3º - Este Decreto entra em vigor na data de sua publicação.
													<br><br>
													Recife, 08 de maio de 2002.
												</p>
											</td>
										</tr>
										<tr>
											<td class="textonegrito">
												<p align="center">
													João Paulo
													<br>
													Prefeito
													<br><br>
													Bruno Ariosto
													<br>
													Secretário de Assuntos Jurídicos
													<br><br>
													Reginaldo Muniz
													<br>
													Secretário de Finanças
													<br>
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
