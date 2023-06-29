<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabSubclasseMaterialExcluir.php
# Autor:    Roberta Costa
# Data:     06/06/05
# Objetivo: Programa de Exclusão do Subclasse de Material
# Alterado: Rodrigo Melo
# Data:     09/11/2007 - Alteração para evitar que haja redirecionamento de 
#                        página após a exclusão.
# Alterado: Rodrigo Melo
# Data:     11/02/2008 - Alteração para que a subclasse ao ser excluída caso não exista nenhuma movimentação com nenhum material que anteriormente pertenceu a esta subclasse. 
# Alterado: Rodrigo Melo
# Data:     03/03/2008 - Remoção da integração com a tabela de histórico.
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/materiais/TabSubclasseMaterialAlterar.php' );
AddMenuAcesso( '/materiais/TabSubclasseMaterialSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST" ){
		$Botao 	            = $_POST['Botao'];
		$TipoMaterial       = $_POST['TipoMaterial'];
		$Grupo	 			      = $_POST['Grupo'];
		$GrupoDescricao	 		= $_POST['GrupoDescricao'];
		$Classe             = $_POST['Classe'];
		$ClasseDescricao    = $_POST['ClasseDescricao'];
		$Subclasse          = $_POST['Subclasse'];
		$SubclasseDescricao = $_POST['SubclasseDescricao'];
		$Situacao           = $_POST['Situacao'];
}else{
		$Grupo	 	 = $_GET['Grupo'];
		$Classe    = $_GET['Classe'];
		$Subclasse = $_GET['Subclasse'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $Botao == "Voltar" ){
		$Url = "TabSubclasseMaterialAlterar.php?Grupo=$Grupo&Classe=$Classe&Subclasse=$Subclasse";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: $Url");
		exit;
}elseif( $Botao == "Excluir" ){		
		
		# Critica dos Campos #
		$Mens     = 0;
		$Mensagem = "Informe: ";
		# Resgata o Sequencial da Subclasse #
		$sql = "SELECT CSUBCLSEQU FROM SFPC.TBSUBCLASSEMATERIAL ";
		$sql .= " WHERE CGRUMSCODI = $Grupo AND CCLAMSCODI = $Classe ";
		$sql .= "   AND CSUBCLCODI = $Subclasse ";
		$db = Conexao();
		$result = $db->query($sql);
    $Linha = $result->fetchRow();
    $SubclasseSequ = $Linha[0];
		# Verifica se a Subclasse está relacionada com algum Cadstro #
		$sql = "SELECT COUNT(CSUBCLSEQU) FROM SFPC.TBMATERIALPORTAL ";
		$sql .= " WHERE CSUBCLSEQU = $SubclasseSequ ";
		$db = Conexao();
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
		    $QtdCad = $result->fetchRow();
		 		if( $QtdCad[0] > 0 ) {
			    	$Mens     = 1;
		       	$Tipo     = 2;
						$Mensagem = "Exclusão Cancelada!<br>Subclasse Relacionada com ($QtdCad[0]) Cadastro(s)";
				}else{          
          # Exclui Subclasse #
          $sql    = "DELETE FROM SFPC.TBSUBCLASSEMATERIAL ";
          $sql   .= " WHERE CGRUMSCODI = $Grupo AND CCLAMSCODI = $Classe ";
          $sql   .= "   AND CSUBCLCODI = $Subclasse ";
          $result = $db->query($sql);
          if( PEAR::isError($result) ){
              $db->query("ROLLBACK");
              ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
          }else{
              $db->query("COMMIT");
              $db->query("END TRANSACTION");
              $db->disconnect();								
              # Envia mensagem para página selecionar #
              $Mensagem = urlencode("Subclasse Excluída com Sucesso");
              $Url = "TabSubclasseMaterialSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
              if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
              header("location: ".$Url);								
              exit;
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
<form action="TabSubclasseMaterialExcluir.php" method="post" name="Classe">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Materiais > Subclasse > Manter
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
	        	EXCLUIR - SUBCLASSE DE MATERIAL
          </td>
        </tr>
        <tr>
          <td class="textonormal">
             <p align="justify">
               Para confirmar a exclusão da Classe de Material, clique no botão "Excluir", caso contrário clique no botão "Voltar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table border="0" width="100%" summary="">
        			<tr>
            		<td class="textonormal"  bgcolor="#DCEDF7" width="30%" height="20">Tipo de Material</td>
  	           	<td class="textonormal">
	                <?php if( $TipoMaterial == "C" ){ echo "CONSUMO"; }else{ echo "PERMANENTE"; } ?>
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
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Subclasse</td>
               	<td class="textonormal">
               		<?php echo $SubclasseDescricao; ?>
                </td>
              </tr>
        			<tr>
            		<td class="textonormal"  bgcolor="#DCEDF7" height="20">Situação</td>
               	<td class="textonormal">
	                <?php if( $Situacao == "A" ){ echo "ATIVO"; }else{ echo "INATIVO"; } ?>
               	</td>
        			</tr>
            </table>
          </td>
        </tr>
        <tr>
 	        <td class="textonormal" align="right">
          	<input type="hidden" name="TipoMaterial" value="<?php echo $TipoMaterial;?>">
          	<input type="hidden" name="Grupo" value="<?php echo $Grupo;?>">
          	<input type="hidden" name="GrupoDescricao" value="<?php echo $GrupoDescricao;?>">
          	<input type="hidden" name="Classe" value="<?php echo $Classe; ?>">
          	<input type="hidden" name="ClasseDescricao" value="<?php echo $ClasseDescricao; ?>">
          	<input type="hidden" name="Subclasse" value="<?php echo $Subclasse; ?>">
          	<input type="hidden" name="SubclasseDescricao" value="<?php echo $SubclasseDescricao; ?>">
          	<input type="hidden" name="Situacao" value="<?php echo $Situacao; ?>">
          	<input type="button" value="Excluir" class="botao" onclick="javascript:enviar('Excluir');">
            <input type="button" value="Voltar"  class="botao" onclick="javascript:enviar('Voltar');">
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
