<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabGrupoMaterialServicoExcluir.php
# Autor:    Rossana Lira
# Data:     01/02/05
# Objetivo: Programa de Exclusão do Grupo de Material e Serviço
# Alterado: Rodrigo Melo
# Data:     02/06/2011 - Tarefa Redmine: 2203 - Fornecedores devem estar associados a grupos de materiais
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso("/materiais/TabGrupoMaterialServicoAlterar.php");
AddMenuAcesso("/materiais/TabGrupoMaterialServicoSelecionar.php");

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']  == "POST"){
		$Botao      								= $_POST['Botao'];
		$Critica  						  		= $_POST['Critica'];
		$TipoGrupo	 								= $_POST['TipoGrupo'];
		$TipoMaterial	 							= $_POST['TipoMaterial'];
		$GrupoCodigo 								= $_POST['GrupoCodigo'];
		$GrupoDescricao 						= $_POST['GrupoDescricao'];
		$Situacao           			  = $_POST['Situacao'];
}else{
		$GrupoCodigo 								= $_GET['GrupoCodigo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $Botao == "Voltar" ){
		$Url = "TabGrupoMaterialServicoAlterar.php?GrupoCodigo=$GrupoCodigo";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit;
}else{
		# Critica dos Campos #
		if( $Critica == 1 ) {
		    $Mens     = 0;
			$Mensagem = "Informe: ";
			$db     = Conexao();

				#Modificação devido a Tarefa do Redmine: 2203.

				//Inicio das criticas referente ao fornecedor e prefornecedor

			  # Verifica se a Classe está relacionada com algum fornecedor ou preFornecedor
			  # No futuro verificar se a Classe está relacionada com algum item #
				$sql = "
					SELECT
						PF.APREFOCCGC, PF.APREFOCCPF, PF.NPREFORAZS
					FROM
						SFPC.TBGRUPOPREFORNECEDOR GPF,
						SFPC.TBPREFORNECEDOR PF
					WHERE
						CGRUMSCODI = $GrupoCodigo
						AND GPF.APREFOSEQU = PF.APREFOSEQU
					ORDER BY PF.NPREFORAZS
				";
		 		$resPreFornecedor = $db->query($sql);
				if( PEAR::isError($resPreFornecedor) ){
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				    $db->disconnect();
				    exit(0);
				}
				$noPreFornecedores = $resPreFornecedor->numRows();

				$sql2 = "
					SELECT
						F.AFORCRCCGC, F.AFORCRCCPF, F.NFORCRRAZS
					FROM
						SFPC.TBGRUPOFORNECEDOR GF,
						SFPC.TBFORNECEDORCREDENCIADO F
					WHERE
						CGRUMSCODI = $GrupoCodigo
						AND GF.AFORCRSEQU = F.AFORCRSEQU
					ORDER BY F.NFORCRRAZS
				";

		 		$resFornecedor = $db->query($sql2);
				if( PEAR::isError($resFornecedor) ){
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql2");
				    $db->disconnect();
				    exit(0);
				}
				$noFornecedores = $resFornecedor->numRows();

				if($noPreFornecedores+$noFornecedores > 0){
		    	$Mens = 1;$Tipo = 2;
					$Critica  = 0;
					$Mensagem = "Exclusão Cancelada!<br/>Grupo Relacionado com os seguintes fornecedor(es) e/ou pré-fornecedor(es) inscritos:";

					if($noFornecedores>0){
						$Mensagem .= "<br/><br/><b style='color:#FF0000'>Fornecedor(es):</b>";
						for($i=1;$i<=$noFornecedores;$i++){
							$Mensagem .= "<br/>";
							$Linha 		= $resFornecedor->fetchRow();
							$cnpjFornecedor = $Linha[0];
							$cpfFornecedor = $Linha[1];
							$nomeFornecedor = $Linha[2];
							$Mensagem .= "<b style='color:#0000ff'>Nome:</b> ".$nomeFornecedor." &nbsp;&nbsp;&nbsp;&nbsp;";
							if(!is_null($cnpjFornecedor)){
								$Mensagem .= "<b style='color:#0000ff'>CNPJ:</b> ".FormataCNPJ($cnpjFornecedor);
							}else{
								$Mensagem .= "<b style='color:#0000ff'>CPF:</b> ".FormataCPF($cpfFornecedor);
							}
						}
					}

					if($noPreFornecedores>0){
						$Mensagem .= "<br/><br/><b style='color:#FF0000'>Pre-Fornecedor(es):</b>";

						for($i=1;$i<=$noPreFornecedores;$i++){
							$Mensagem .= "<br/>";
							$Linha 		= $resPreFornecedor->fetchRow();
							$cnpjFornecedor = $Linha[0];
							$cpfFornecedor = $Linha[1];
							$nomeFornecedor = $Linha[2];
							$Mensagem .= "<b style='color:#0000ff'>Nome:</b> ".$nomeFornecedor." &nbsp;&nbsp;&nbsp;&nbsp;";
							if(!is_null($cnpjFornecedor)){
								$Mensagem .= "<b style='color:#0000ff'>CNPJ:</b> ".FormataCNPJ($cnpjFornecedor);
							}else{
								$Mensagem .= "<b style='color:#0000ff'>CPF:</b> ".FormataCPF($cpfFornecedor);
							}
						}
					}

				//Fim das criticas referente ao fornecedor e prefornecedor


				//Adicionado devido a TAREFA 2203

				}else{

			    # Verifica se o grupo está relacionado com alguma classe #
					$sql    = "SELECT COUNT(CGRUMSCODI) FROM SFPC.TBCLASSEMATERIALSERVICO ";
					$sql   .= " WHERE CGRUMSCODI = $GrupoCodigo ";
			 		$result = $db->query($sql);
					if( PEAR::isError($result) ){
					    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
					}else{
					    $Linha = $result->fetchRow();
					    $Qtd = $Linha[0];
			    		if( $Qtd > 0 ) {
						    	$Mens = 1;$Tipo = 2;
									$Mensagem = "Exclusão Cancelada!<br>Grupo Relacionado com ($Qtd) Classe(s)";
							}else{
									# Exclui Grupo #
									$sql    = "DELETE FROM SFPC.TBGRUPOMATERIALSERVICO WHERE CGRUMSCODI = $GrupoCodigo ";
									$result = $db->query($sql);
									if( PEAR::isError($result) ){
											$db->query("ROLLBACK");
									    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
									}else{
											$db->query("COMMIT");
											$db->query("END TRANSACTION");
											$db->disconnect();

											# Envia mensagem para página selecionar #
											$Mensagem = urlencode("Grupo Excluído com Sucesso");
											$Url = "TabGrupoMaterialServicoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
											if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
											header("location: ".$Url);
											exit;
									}
							}
							$db->query("COMMIT");
							$db->query("END TRANSACTION");
							$db->disconnect();
				 	}

			 	//Adicionado devido a TAREFA 2203
			 	}
		  		$db->disconnect();
		}
}
if( $Critica == 0 ){
		$db     = Conexao();
		$sql    = "SELECT  CGRUMSCODI, FGRUMSTIPO, FGRUMSTIPM, EGRUMSDESC, FGRUMSSITU FROM SFPC.TBGRUPOMATERIALSERVICO WHERE CGRUMSCODI = $GrupoCodigo";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
			  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
						$GrupoCodigo		= $Linha[0];
						$TipoGrupo      = $Linha[1];
						$TipoMaterial   = $Linha[2];
						$GrupoDescricao = $Linha[3];
						$Situacao       = $Linha[4];
				}
		}
		$db->disconnect();
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
	document.Grupo.Botao.value=valor;
	document.Grupo.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabGrupoMaterialServicoExcluir.php" method="post" name="Grupo">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Grupo > Manter
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
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
		<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
        <tr>
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	        	EXCLUIR - GRUPO DE MATERIAL OU SERVIÇO
          </td>
        </tr>
        <tr>
          <td class="textonormal">
             <p align="justify">
               Para confirmar a exclusão do Grupo de Material ou serviço, clique no botão "Excluir", caso contrário clique no botão "Voltar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table summary="">
        			<tr>
            		<td class="textonormal"  bgcolor="#DCEDF7">Tipo de Grupo</td>
	                <?php
                  if (($TipoGrupo == "M") or ($TipoGrupo == "")) {
                     $DescTipo = "MATERIAL";
                  }else{
                     $DescTipo = "SERVIÇO";
                  }
	                ?>
  	           	<td class="textonormal">
               		<?php echo $DescTipo; ?>
               	</td>
        			</tr>
	            <?php if ($TipoGrupo == "M") { ?>
	        			<tr>
	            		<td class="textonormal"  bgcolor="#DCEDF7">Tipo de Material</td>
		                <?php
	                  if (($TipoMaterial == "M") or ($TipoMaterial == "")) {
	                     $DescTipoMaterial = "CONSUMO";
	                  }else{
	                     $DescTipoMaterial = "PERMANENTE";
	                  }
		                ?>
	  	           	<td class="textonormal">
	               		<?php echo $DescTipoMaterial; ?>
	               	</td>
	        			</tr>
	            <?php } ?>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Grupo</td>
               	<td class="textonormal">
               		<?php echo $GrupoDescricao; ?>
                	<input type="hidden" name="Critica" value="1">
                	<input type="hidden" name="TipoGrupo" value="<?php echo $TipoGrupo; ?>">
                	<input type="hidden" name="TipoMaterial" value="<?php echo $TipoMaterial; ?>">
                	<input type="hidden" name="GrupoCodigo" value="<?php echo $GrupoCodigo; ?>">
                	<input type="hidden" name="GrupoDescricao" value="<?php echo $GrupoDescricao; ?>">
         					<input type="hidden" name="Situacao" value="<?php echo $Situacao; ?>">
                </td>
              </tr>
        			<tr>
            		<td class="textonormal"  bgcolor="#DCEDF7">Situação</td>
	                <?php
                  if($Situacao == "A") {
                     $DescSituacao = "ATIVO";
                  }else{
                     $DescSituacao = "INATIVO";
                  }
	                ?>
               	<td class="textonormal">
               		<?php echo $DescSituacao; ?>
               	</td>
        			</tr>
            </table>
          </td>
        </tr>
        <tr>
 	        <td class="textonormal" align="right">
          	<input type="submit" value="Excluir" class="botao" onclick="javascript:enviar('Excluir')">
            <input type="button" value="Voltar"  class="botao" onclick="javascript:enviar('Voltar')">
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
