<?php
	#----------------------------------------------------------------------------
	# Portal da DGCO
	# Programa: RelUsuariosImpressao.php
	# Autor:    Roberta Costa
	# Data:     28/08/03
	# Objetivo: Programa que Imprime o Relatório de Acompanhamento de Licitações
	# OBS.:     Tabulação 2 espaços
	#----------------------------------------------------------------------------

	# Acesso ao arquivo de funções #
	include "../funcoes.php";
	include "../gerais/funcoesGui.php";

	# Executa o controle de segurança #
	session_start();
	Seguranca();

	# Variáveis com o global off #
	if( $_SERVER['REQUEST_METHOD'] == "GET"){
		$Critica = $_GET['Critica'];
		$Botao   = $_GET['Botao'];
		$Opcao   = urldecode($_GET['Opcao']);
	}

	# Identifica o Programa para Erro de Banco de Dados #
	$ErroPrograma = "RelUsuariosImpressao.php";

	# Constóri a Lista com Nome e Email da tabela de usuários de produção #
	$db  = Conexao();
	$sql = "SELECT a.EUSUPORESP, a.EUSUPOMAIL, a.CGREMPCODI, a.AUSUPOFONE FROM SFPC.TBUSUARIOPORTAL a ";
	if( $_SESSION['_cgrempcodi_'] >= 0 ){
		if( $Opcao == "Grupo" ){
			$sql  .= " , SFPC.TBGRUPOEMPRESA b WHERE a.CGREMPCODI = b.CGREMPCODI AND a.CGREMPCODI <> 0 ORDER BY b.EGREMPDESC, a.EUSUPORESP";
		}elseif( $Opcao == "Alfabetica" ){
			$sql .= " WHERE a.CGREMPCODI <> 0 ORDER BY a.EUSUPORESP";
		}
	}
	$result = $db->query($sql);
	if( PEAR::isError($result) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	}
?>
<html>
<body marginwidth="0" marginheight="0">
<link rel="Stylesheet" type="Text/Css" href="../estilo.css">
<form action="RelUsuariosImpressao.php" method="post" name="Relatorio">
	<div id="cabecalho">
		<p class="titulo3" align="center">
			Prefeitura da Cidade do Recife<br><br>
			<a href="javascript:Fecha()"><img src="../midia/brasao.jpg" width="50" height="40" border="0"></a>
		</p>
		<?php $cabecalho = retornaCabecalho(); ?>
		<ul style="display:inline-block; margin-left: -20px;">
			<li class="titulo3 left" align="left" style="list-style: none;">
				<?php echo $cabecalho[empresa]; ?>
			</li>
			<li class="titulo3 left" align="left" style="list-style: none;">
				<?php echo $cabecalho[orgao2]; ?>
			</li>
			<li class="titulo3 left" align="left" style="list-style: none;">
				<?php echo $cabecalho[nomesistema]; ?>
			</li>
		</ul>

		<ul style="display: inline-block;float: right;margin-right: 20px;">
			<li class="titulo3 right" align="right" style="list-style: none;">
				<?php echo $cabecalho[orgao1]; ?>
			</li>
			<li class="titulo3 right" align="right" style="list-style: none;">
				<?php echo $cabecalho[setor1]; ?>
			</li>
			<li class="titulo3 right" align="right" style="list-style: none;">
				Data: <?php echo date("d/m/Y H:i"); ?>
			</li>
		</ul>
			<font class="textonegrito"><center>Lista de Usuários do Portal de Compras</center></font>
	</div>
	<hr><br>
	<table align="center" border="1" cellpadding="3" cellspacing="0" summary="" class="textonormal">
		<tr bgcolor="#DCEDF7">
			<td class="textonormal" colspan="2">Nome</td>
			<td class="textonormal" colspan="2">E-mail</td>
			<td class="textonormal" colspan="2">Telefone</td>
		</tr>
		<?php
			while( $Linha = $result->fetchRow() ){
				if( $Opcao == "Grupo" ){
					$con = "SELECT EGREMPDESC FROM SFPC.TBGRUPOEMPRESA WHERE CGREMPCODI = $Linha[2] ORDER BY EGREMPDESC";
					$res = $db->query($con);
					if( PEAR::isError($res) ){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $con");
					}else{
						while( $Reg = $res->fetchRow() ){
							if( $GrupoDescricao != $Reg[0] ){
								$GrupoDescricao = $Reg[0]; ?>
								<tr><td class="titulo3" colspan="4" bgcolor="#DCEDF7"><?php echo $GrupoDescricao; ?></td></tr>
							<?php
							}
						}
					}
				}
				?>
				<tr>
					<td colspan="2" class="textonormal"><?php echo $Linha[0]; ?></td>
					<td colspan="2" class="textonormal"><?php echo $Linha[1]; ?></td>
					<td colspan="2" class="textonormal"><?php echo $Linha[3]; ?></td>
				</tr>
			<?php } ?>
		<!-- Fim do Corpo -->
	</table>
</form>
</body>
</html>
<script language="javascript" type="">
	<!--
	window.focus();
	self.print();

	function Fecha(){
		window.close();
	}
	//-->
</script>
