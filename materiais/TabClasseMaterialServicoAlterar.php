<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabClasseMaterialServicoAlterar.php
# Autor:    Rossana Lira
# Data:     02/02/05
# Objetivo: Programa de Alteração do Classe de Material e Serviço
# Alterado: Rodrigo Melo
# Data:     09/11/2007 - Alteração das críticas para permitir alterar o nome da
#                        classe mesmo que a mesma tenha subclasses já relacionadas a ela.
# Alterado: Ariston Cordeiro
# Data:     30/03/2009	- Proibir colocar classe como inativa quando um material dela estiver na tabela de preços
#												- Redirecionar para TabClasseMaterialServicoSelecionar.php quando o http request não vir os valores necessários
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/materiais/TabClasseMaterialServicoExcluir.php' );
AddMenuAcesso( '/materiais/TabClasseMaterialServicoSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao                		 = $_POST['Botao'];
		$Critica             			 = $_POST['Critica'];
		$ClasseCodigo          		 = $_POST['ClasseCodigo'];
		$Grupo	 								   = $_POST['Grupo'];
		$TipoMaterial	             = $_POST['TipoMaterial'];
		$ClasseDescricao       		 = strtoupper2(trim($_POST['ClasseDescricao']));
		$Situacao             	 	 = $_POST['Situacao'];
		$TipoGrupo	 						   = $_POST['TipoGrupo'];
		$GrupoDescricao       		 = $_POST['GrupoDescricao'];
}else{
		$Grupo	 								   = $_GET['Grupo'];
		$ClasseCodigo       		   = $_GET['ClasseCodigo'];
}

if(is_null($Grupo) or is_null($ClasseCodigo) ){
		header("location: TabClasseMaterialServicoSelecionar.php");
		exit;
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Redireciona para a página de excluir #
if( $Botao == "Excluir" ){
		$Url = "TabClasseMaterialServicoExcluir.php?Grupo=$Grupo&ClasseCodigo=$ClasseCodigo";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	  header("location: ".$Url);
	  exit;
}elseif( $Botao == "Voltar" ){
	  header("location: TabClasseMaterialServicoSelecionar.php");
	  exit;
}elseif( $Botao == "Alterar" ){
		# Critica dos Campos #
		if( $Critica == 1 ) {
			  $Mens     = 0;
		    $Mensagem = "Informe: ";
		    if( $ClasseDescricao == "" ) {
			      $Critica   = 1;
				 	  $Mens      = 1;
				 	  $Tipo      = 2;
				    $Mensagem .= "<a href=\"javascript:document.Classe.ClasseDescricao.focus();\" class=\"titulo2\">Classe</a>";
		    }

		    if( $Mens == 0 ){

					$db     = Conexao();

					# Verifica se a classe possui materiais registrados na tabela de preços, neste caso não poderá ser inativado

					$sql = "
						select count(*)
						from
							SFPC.TBclassematerialservico c, SFPC.TBsubclassematerial sc, SFPC.TBmaterialportal m
						where
							m.cmatepsequ in (
								select distinct cmatepsequ
								from SFPC.TBprecomaterial
							)
							and c.cclamscodi = $ClasseCodigo
							and c.cgrumscodi = $Grupo
							and c.cclamscodi = sc.cclamscodi
							and c.cgrumscodi = sc.cgrumscodi
							and sc.csubclsequ = m.csubclsequ
					";

					$result = $db->query($sql);
					if( PEAR::isError($result) ){
					    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
					    exit(0);
					}
					$Linha = $result->fetchRow();
					$noMateriaisRegistrados = $Linha[0];
					if(($noMateriaisRegistrados>0) and ($Situacao=='I')){
			      $Critica   = 1;
				 	  $Mens      = 1;
				 	  $Tipo      = 2;
				    $Mensagem .= "Classe possui materiais registrados na tabela de preço e portanto não pode ser inativa";
				    $Situacao='A';
					}else{


														# Verifica a Duplicidade de Classe #
														$sql    = "SELECT COUNT(CCLAMSCODI) FROM SFPC.TBCLASSEMATERIALSERVICO ";
														$sql   .= " WHERE RTRIM(LTRIM(ECLAMSDESC)) = '$ClasseDescricao' ";
														$sql   .= "   AND (CGRUMSCODI = $Grupo AND CCLAMSCODI <> $ClasseCodigo)";
												 		$result = $db->query($sql);
														if( PEAR::isError($result) ){
														    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														}else{
														    $Linha = $result->fetchRow();
														    $Qtd   = $Linha[0];
												    		if( $Qtd > 0 ) {
																	$Critica   = 1;
														    	$Mens      = 1;
														    	$Tipo      = 2;
																	$Mensagem  = "<a href=\"javascript:document.Classe.ClasseDescricao.focus();\" class=\"titulo2\"> Classe Já Cadastrada</a>";
												    		}
																if( $Mens == 0 ){
																		# Atualiza Classe #
																		$Data   = date("Y-m-d H:i:s");
																		$db->query("BEGIN TRANSACTION");
																		$sql    = "UPDATE SFPC.TBCLASSEMATERIALSERVICO ";
																		$sql   .= "   SET ECLAMSDESC = '$ClasseDescricao',  ";
																		$sql   .= "       FCLAMSSITU = '$Situacao', TCLAMSULAT = '$Data' ";
																		$sql   .= " WHERE CGRUMSCODI = $Grupo AND CCLAMSCODI = $ClasseCodigo";
																		$result = $db->query($sql);
																		if( PEAR::isError($result) ){
																				$db->query("ROLLBACK");
																		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																		}
														        $db->query("COMMIT");
														        $db->query("EN TRANSACTION");
																		$db->disconnect();

														        # Envia mensagem para página selecionar #
														        $Mensagem = urlencode("Classe Alterada com Sucesso");
														        $Url = "TabClasseMaterialServicoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1&Critica=0";
																		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
														        header("location: ".$Url);
														        exit;
																}
														}
					}
					$db->disconnect();
				}
		}
}
if( $Critica == 0 ){
		# Carrega os dados da Classe selecionada #
		$db     = Conexao();
		$sql    = "SELECT  A.CCLAMSCODI, A.ECLAMSDESC, A.FCLAMSSITU, B.FGRUMSTIPO,  B.FGRUMSTIPM, B.EGRUMSDESC " ;
		$sql   .= " FROM SFPC.TBCLASSEMATERIALSERVICO A, SFPC.TBGRUPOMATERIALSERVICO B ";
		$sql   .= " WHERE A.CGRUMSCODI = $Grupo AND CCLAMSCODI = $ClasseCodigo AND A.CGRUMSCODI = B.CGRUMSCODI";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
		    while( $Linha = $result->fetchRow() ){
						$ClasseCodigo			= $Linha[0];
						$ClasseDescricao 	= $Linha[1];
						$Situacao       	= $Linha[2];
						$TipoGrupo			 	= $Linha[3];
						$TipoMaterial 		= $Linha[4];
						$GrupoDescricao 	= $Linha[5];
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
<form action="TabClasseMaterialServicoAlterar.php" method="post" name="Classe">
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
	        	MANTER - CLASSE DE MATERIAL OU SERVIÇO
          </td>
        </tr>
        <tr>
          <td class="textonormal">
             <p align="justify">
             Para atualizar a Classe de Material ou Serviço, preencha os dados abaixo e clique no botão "Alterar". <br>
             Para apagar a Classe clique no botão "Excluir".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table summary="">
        			<tr>
		            <tr>
		              <td class="textonormal" bgcolor="#DCEDF7" height="20">Tipo de Grupo </td>
		              <td class="textonormal">
		              	<?php
		              	 if( $TipoGrupo == "M" ) {
		              	 	 echo " MATERIAL";
		              	 } else {
		              	 		if( $TipoGrupo == "S" ){
		              	 			echo " SERVIÇO";
		              	 		}
		              	 }
		              	 ?>
		              </td>
		            </tr>
        			</tr>
	            <?php if ($TipoGrupo == "M") { ?>
		            <tr>
		              <td class="textonormal" bgcolor="#DCEDF7" height="20">Tipo de Material</td>
		              <td class="textonormal">
		              	<?php
		              	 if( $TipoMaterial == "C" ) {
		              	 	 echo " CONSUMO";
		              	 } else {
		              	 		if( $TipoMaterial == "P" ){
		              	 			echo " PERMANENTE";
		              	 		}
		              	 }
		              	 ?>
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
                <td class="textonormal" bgcolor="#DCEDF7">Classe</td>
               	<td class="textonormal">
               		<input type="text" name="ClasseDescricao" size="45" maxlength="100" value="<?php echo $ClasseDescricao; ?>" class="textonormal">
                	<input type="hidden" name="Critica" value="1">
                	<input type="hidden" name="Grupo" value="<?php echo $Grupo; ?>">
                	<input type="hidden" name="ClasseCodigo" value="<?php echo $ClasseCodigo; ?>">
                	<input type="hidden" name="TipoGrupo" value="<?php echo $TipoGrupo; ?>">
                	<input type="hidden" name="TipoMaterial" value="<?php echo $TipoMaterial; ?>">
                	<input type="hidden" name="GrupoDescricao" value="<?php echo $GrupoDescricao; ?>">
                </td>
	            </tr>
        			<tr>
            		<td class="textonormal"  bgcolor="#DCEDF7">Situação*</td>
	                <?php
                  if($Situacao == "A") {
                     $DescSituacao = "ATIVO";
                  }else{
                     $DescSituacao = "INATIVO";
                  }
	                ?>
	              <td>
	                <select name="Situacao" value="<?php echo $DescSituacao; ?>" class="textonormal">
	        	        <option value="A" <?php if ( $Situacao == "A" ) { echo "selected"; }?>>ATIVO</option>
                    <option value="I" <?php if ( $Situacao == "I" ) { echo "selected"; }?>>INATIVO</option>
                  </select>
                </td>
        			</tr>
            </table>
          </td>
        </tr>
        <tr>
 	        <td class="textonormal" align="right">
            <input type="button" value="Alterar" class="botao" onclick="javascript:enviar('Alterar');">
						<input type="button" value="Excluir" class="botao" onclick="javascript:enviar('Excluir');">
            <input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
            <input type="hidden" name="Botao" value="">
          </td>
        </tr>
      </table>
		</td>
	</tr>
	<!-- Fim do Corpo -->
</table>
</form>
<script language="javascript" type="">
<!--
document.Classe.ClasseDescricao.focus();
//-->
</script>
</body>
</html>
