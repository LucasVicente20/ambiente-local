<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabClasseMaterialServicoExcluir.php
# Autor:    Rossana Lira
# Data:     02/02/05
# Objetivo: Programa de Exclusão do Classe de Material e Serviço
#--------------------------
# Alterado: Ariston Cordeiro
# Data:     17/03/2010	- Correção de comandos de transação (SQL) ao deletar a classe
#												- Detecção de pré-cadastros de materiais e serviços
# Alterado: Rodrigo Melo
# Data:     02/06/2011 - Tarefa Redmine: 2203 - Fornecedores devem estar associados a grupos de materiais
#----------------------------
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/materiais/TabClasseMaterialServicoAlterar.php' );
AddMenuAcesso( '/materiais/TabClasseMaterialServicoSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']  == "POST"){
		$Botao 	        = $_POST['Botao'];
		$Critica	      = $_POST['Critica'];
		$TipoMaterial   = $_POST['TipoMaterial'];
		$Grupo	 			  = $_POST['Grupo'];
		$ClasseCodigo   = $_POST['ClasseCodigo'];
}else{
		$Grupo	 			= $_GET['Grupo'];
		$ClasseCodigo = $_GET['ClasseCodigo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $Botao == "Voltar" ){
		$Url = "TabClasseMaterialServicoAlterar.php?Grupo=$Grupo&ClasseCodigo=$ClasseCodigo";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit;
}else{
		# Critica dos Campos #
		if( $Critica == 1 ) {
		    $Mens     = 0;
			  $Mensagem = "Informe: ";
			  $db     = Conexao();

			    # verificar se existe alguma subclasse relacionado com a Classe #
					$sql    = "SELECT COUNT(CSUBCLSEQU) FROM SFPC.TBSUBCLASSEMATERIAL ";
					$sql   .= " WHERE CGRUMSCODI = $Grupo AND CCLAMSCODI = $ClasseCodigo ";
					$result = $db->query($sql);
					if( PEAR::isError($result) ){
	    				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
					}else{
	    				$Linha 		= $result->fetchRow();
					    $QtdSub 	= $Linha[0];
						if( $QtdSub > 0 ) {
						    	$Mens = 1;$Tipo = 2;
									$Mensagem = "Exclusão Cancelada!<br>Classe Relacionada com ($QtdSub) Subclasse(s)";
									$Critica  = 0;
							}else{

						    # verificar se existe algum material ou serviço relacionado com a Classe #
								$sql    = "
									SELECT COUNT(CSERVPSEQU)
									FROM SFPC.TBSERVICOPORTAL
									WHERE
										CGRUMSCODI = $Grupo AND CCLAMSCODI = $ClasseCodigo
								";
		 						$result = $db->query($sql);
								if( PEAR::isError($result) ){
										EmailErroSQL("Erro em SQL", __FILE__, __LINE__, "Erro em SQL", $sql, $result);
								}else{
				    				$Linha 		= $result->fetchRow();
								    $Qtd 	= $Linha[0];
		    						if( $Qtd > 0 ) {
									    	$Mens = 1;$Tipo = 2;
												$Mensagem = "Exclusão Cancelada!<br>Classe Relacionada com ($Qtd) serviço(s)";
												$Critica  = 0;
										}else{

									    # verificar se existe algum pre cadastro relacionado com a Classe #
											$sql    = "
												SELECT COUNT(CPREMACODI)
												FROM SFPC.TBPREMATERIALSERVICO
												WHERE
													CGRUMSCODI = $Grupo AND CCLAMSCODI = $ClasseCodigo
											";
					 						$result = $db->query($sql);
											if( PEAR::isError($result) ){
							    				EmailErroSQL("Erro em SQL", __FILE__, __LINE__, "Erro em SQL", $sql, $result);
											}else{
							    				$Linha 		= $result->fetchRow();
											    $Qtd 	= $Linha[0];
					    						if( $Qtd > 0 ) {
												    	$Mens = 1;$Tipo = 2;
															$Mensagem = "Exclusão Cancelada!<br>Classe Relacionada com ($Qtd) Pre-cadastro(s) de materiais(s) ou serviço(s)";
															$Critica  = 0;
													}else{


															# Exclui Classe #
															$db->query("BEGIN TRANSACTION");
											        $sql    = "DELETE FROM SFPC.TBCLASSEMATERIALSERVICO WHERE CGRUMSCODI = $Grupo AND CCLAMSCODI = $ClasseCodigo ";
															$result = $db->query($sql);
															if( PEAR::isError($result) ){

																	$db->query("ROLLBACK");
																	$db->query("END TRANSACTION");
																	$db->disconnect();
											   	 				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
															}else{
																	$db->query("COMMIT");
																	$db->query("END TRANSACTION");
																	$db->disconnect();
																	# Envia mensagem para página selecionar #
																	$Mensagem = urlencode("Classe Excluída com Sucesso");
																	$Url = "TabClasseMaterialServicoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
																	if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																	header("location: ".$Url);
																	exit;
															}
					    						}
											}
		    					}
			        }
 					}
				}

		  $db->disconnect();
    }
}
if( $Critica == 0 ){
		$db     = Conexao();
		$sql    = "SELECT  A.CGRUMSCODI, A.CCLAMSCODI, A.ECLAMSDESC, A.FCLAMSSITU, B.FGRUMSTIPO,  B.FGRUMSTIPM, B.EGRUMSDESC " ;
		$sql   .= " FROM SFPC.TBCLASSEMATERIALSERVICO A, SFPC.TBGRUPOMATERIALSERVICO B ";
		$sql   .= " WHERE A.CGRUMSCODI = $Grupo AND CCLAMSCODI = $ClasseCodigo AND A.CGRUMSCODI = B.CGRUMSCODI";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
			  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
						$ClasseCodigo			= $Linha[1];
						$ClasseDescricao 	= $Linha[2];
						$Situacao       	= $Linha[3];
						$TipoGrupo			 	= $Linha[4];
						$TipoMaterial 		= $Linha[5];
						$GrupoDescricao 	= $Linha[6];
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
	document.Classe.Botao.value=valor;
	document.Classe.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabClasseMaterialServicoExcluir.php" method="post" name="Classe">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Materiais > Classe > Manter
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
	        	EXCLUIR - CLASSE DE MATERIAL OU SERVIÇO
          </td>
        </tr>
        <tr>
          <td class="textonormal">
             <p align="justify">
               Para confirmar a exclusão da Classe de Material ou Serviço, clique no botão "Excluir", caso contrário clique no botão "Voltar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table summary="">
        			<tr>
            		<td class="textonormal"  bgcolor="#DCEDF7" height="20">Tipo</td>
	                <?php
                  if($TipoGrupo == "M") {
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
	            		<td class="textonormal"  bgcolor="#DCEDF7" height="20">Tipo de Material</td>
		                <?php
	                  if ($TipoMaterial == "C") {
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
                </td>
              </tr>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Classe</td>
               	<td class="textonormal">
               		<?php echo $ClasseDescricao; ?>
                	<input type="hidden" name="Critica" value="1">
                	<input type="hidden" name="Grupo" value="<?php echo $Grupo; ?>">
                	<input type="hidden" name="ClasseCodigo" value="<?php echo $ClasseCodigo; ?>">
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
