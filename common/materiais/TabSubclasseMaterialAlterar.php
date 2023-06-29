<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabSubclasseMaterialAlterar.php
# Autor:    Roberta Costa
# Data:     06/06/05
# Objetivo: Programa de Alteração do Subclasse de Material
#---------------------------
# Alterado: Ariston Cordeiro
# Data:     30/03/2009	- Proibir colocar subclasse como inativa quando um material dela estiver na tabela de preços
#												- Redirecionar para TabSubclasseMaterialSelecionar.php quando o http request não vir os valores necessários
#---------------------------
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/materiais/TabSubclasseMaterialExcluir.php' );
AddMenuAcesso( '/materiais/TabSubclasseMaterialSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST" ){
		$Botao              = $_POST['Botao'];
		$Grupo	 				    = $_POST['Grupo'];
		$GrupoDescricao     = $_POST['GrupoDescricao'];
		$TipoMaterial	      = $_POST['TipoMaterial'];
		$Classe             = $_POST['Classe'];
		$ClasseDescricao    = strtoupper2(trim($_POST['ClasseDescricao']));
		$Subclasse          = $_POST['Subclasse'];
		$SubclasseDescricao = strtoupper2(trim($_POST['SubclasseDescricao']));
		$Situacao           = $_POST['Situacao'];
}else{
		$Grupo	 	 = $_GET['Grupo'];
		$Classe    = $_GET['Classe'];
		$Subclasse = $_GET['Subclasse'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if(is_null($Grupo) or is_null($Classe) or is_null($Subclasse) ){
		header("location: TabSubclasseMaterialSelecionar.php");
		exit;
}

# Redireciona para a página de excluir #
if( $Botao == "Excluir" ){
		$Url = "TabSubclasseMaterialExcluir.php?Grupo=$Grupo&Classe=$Classe&Subclasse=$Subclasse";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	  header("location: ".$Url);
	  exit;
}elseif( $Botao == "Voltar" ){
	  header("location: TabSubclasseMaterialSelecionar.php");
	  exit;
}elseif( $Botao == "Alterar" ){
		# Critica dos Campos #
	  $Mens     = 0;
    $Mensagem = "Informe: ";
    if( $SubclasseDescricao == "" ) {
		 	  $Mens      = 1;
		 	  $Tipo      = 2;
		    $Mensagem .= "<a href=\"javascript:document.Subclasse.SubclasseDescricao.focus();\" class=\"titulo2\">Subclasse</a>";
    }
    if( $Mens == 0 ){
			$db     = Conexao();

					# Verifica se a subclasse possui materiais registrados na tabela de preços, neste caso não poderá ser inativado

					$sql = "
						select count(*)
						from
							SFPC.TBclassematerialservico c, SFPC.TBsubclassematerial sc, SFPC.TBmaterialportal m
						where
							m.cmatepsequ in (
								select distinct cmatepsequ
								from SFPC.TBprecomaterial
							)
							and c.cclamscodi = $Classe
							and c.cgrumscodi = $Grupo
							and sc.csubclcodi = $Subclasse
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
				    $Mensagem .= "Subclasse possui materiais registrados na tabela de preço e portanto não pode ser inativa";
				    $Situacao='A';
					}else{

										# Verifica a Duplicidade de Sublasse #
										$sql  = "
											SELECT COUNT(CSUBCLCODI)
											FROM SFPC.TBSUBCLASSEMATERIAL
											WHERE
												RTRIM(LTRIM(ESUBCLDESC)) = '$SubclasseDescricao'
												AND CGRUMSCODI = $Grupo
												AND CCLAMSCODI = $Classe
										";
								 		$res  = $db->query($sql);
										if( PEAR::isError($res) ){
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
										    $Qtd = $res->fetchRow();
								    		if( $Qtd[0] > 0 ) {
											    	$Mens      = 1;
											    	$Tipo      = 2;
														$Mensagem  = "<a href=\"javascript:document.Classe.ClasseDescricao.focus();\" class=\"titulo2\"> Subclasse Já Cadastrada</a>";
								    		}
												if( $Mens == 0 ){
														# Atualiza Classe #
														$db->query("BEGIN TRANSACTION");
														$sql  = "UPDATE SFPC.TBSUBCLASSEMATERIAL ";
														$sql .= "   SET ESUBCLDESC = '$SubclasseDescricao', FSUBCLSITU = '$Situacao', ";
														$sql .= "       TSUBCLULAT = '".date("Y-m-d H:i:s")."' ";
														$sql .= " WHERE CGRUMSCODI = $Grupo AND CCLAMSCODI = $Classe ";
														$sql .= "   AND CSUBCLCODI = $Subclasse";
														$res  = $db->query($sql);
														if( PEAR::isError($res) ){
																$db->query("ROLLBACK");
														    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														}
										        $db->query("COMMIT");
										        $db->query("EN TRANSACTION");
														$db->disconnect();

										        # Envia mensagem para página selecionar #
										        $Mensagem = urlencode("Subclasse Alterada com Sucesso");
										        $Url = "TabSubclasseMaterialSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
														if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
										        header("location: ".$Url);
										        exit;
												}
										}
			}
			$db->disconnect();
		}
}
if( $Botao == "" ){
		# Carrega os dados da subclasse selecionada #
		$db   = Conexao();
		$sql  = "SELECT A.CCLAMSCODI, A.ECLAMSDESC, A.FCLAMSSITU, B.FGRUMSTIPO, ";
		$sql .= "       B.FGRUMSTIPM, B.EGRUMSDESC, C.CSUBCLCODI, C.ESUBCLDESC, ";
		$sql .= "       C.FSUBCLSITU ";
		$sql .= "  FROM SFPC.TBCLASSEMATERIALSERVICO A, SFPC.TBGRUPOMATERIALSERVICO B, SFPC.TBSUBCLASSEMATERIAL C ";
		$sql .= " WHERE A.CGRUMSCODI = B.CGRUMSCODI AND A.CGRUMSCODI = C.CGRUMSCODI ";
		$sql .= "   AND A.CCLAMSCODI = C.CCLAMSCODI AND A.CGRUMSCODI = $Grupo ";
		$sql .= "   AND A.CCLAMSCODI = $Classe AND C.CSUBCLCODI = $Subclasse";
		$res  = $db->query($sql);
		if( PEAR::isError($res) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
		    while( $Linha = $res->fetchRow() ){
						$Classe     			  = $Linha[0];
						$ClasseDescricao 	  = $Linha[1];
						$Situacao       	  = $Linha[2];
						$TipoGrupo			   	= $Linha[3];
						$TipoMaterial 	  	= $Linha[4];
						$GrupoDescricao   	= $Linha[5];
						$Subclasse        	= $Linha[6];
						$SubclasseDescricao = $Linha[7];
						$Situacao           = $Linha[8];
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
	document.Subclasse.Botao.value=valor;
	document.Subclasse.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabSubclasseMaterialAlterar.php" method="post" name="Subclasse">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Materiais > 	Subclasse > Manter
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
	        	MANTER - SUBCLASSE DE MATERIAL
          </td>
        </tr>
        <tr>
          <td class="textonormal">
             <p align="justify">
             Para atualizar a Subclasse de Material, preencha os dados abaixo e clique no botão "Alterar".<br>
             Para apagar a Subclasse clique no botão "Excluir".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table border="0" width="100%" summary="">
	            <tr>
	              <td class="textonormal" bgcolor="#DCEDF7" height="20">Tipo de Material</td>
	              <td class="textonormal">
	              	<?php if( $TipoMaterial == "C" ){ echo " CONSUMO"; }elseif( $TipoMaterial == "P" ){ echo " PERMANENTE"; } ?>
	              </td>
	            </tr>
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
                </td>
	            </tr>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7">Subclasse</td>
               	<td class="textonormal">
               		<input type="text" name="SubclasseDescricao" size="45" maxlength="100" value="<?php echo $SubclasseDescricao; ?>" class="textonormal">
                	<input type="hidden" name="TipoMaterial" value="<?php echo $TipoMaterial; ?>">
                	<input type="hidden" name="Grupo" value="<?php echo $Grupo; ?>">
                	<input type="hidden" name="GrupoDescricao" value="<?php echo $GrupoDescricao; ?>">
                	<input type="hidden" name="Classe" value="<?php echo $Classe; ?>">
                	<input type="hidden" name="ClasseDescricao" value="<?php echo $ClasseDescricao; ?>">
                	<input type="hidden" name="Subclasse" value="<?php echo $Subclasse; ?>">
                </td>
	            </tr>
        			<tr>
            		<td class="textonormal"  bgcolor="#DCEDF7">Situação*</td>
	              <td>
	                <select name="Situacao" class="textonormal">
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
document.Subclasse.SubclasseDescricao.focus();
//-->
</script>
</body>
</html>
