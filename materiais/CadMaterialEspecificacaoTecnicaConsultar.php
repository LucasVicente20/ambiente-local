<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadMaterialEspecificacaoTecnicaConsultar.php
# Autor:    Carlos Abreu
# Data:     19/06/2007
# Objetivo: Programa de Seleção de Especificação Tecnica de Material para Download
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# Data:     14/01/2019
# Objetivo: Tarefa Redmine 77809
# ------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/materiais/CadMaterialEspecificacaoTecnicaResultado.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
	$Critica      = $_POST['Critica'];
	$GrupoCodigo  = $_POST['GrupoCodigo'];
	$ClasseCodigo = $_POST['ClasseCodigo'];
    $ItemCodigo   = $_POST['ItemCodigo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "CadMaterialEspecificacaoTecnicaConsultar.php";

# Critica dos Campos #
$Mensagem = urldecode($Mensagem);

if( $Critica == 1 ){
	$Mens = 0;
	$Mensagem = "Informe: ";
	if( $GrupoCodigo == "" ) {
		$Mens = 1; $Tipo = 2; $Troca = 1;
		$Mensagem .= "<a href=\"javascript: document.MaterialPrecoEspecificacaoTecnica.GrupoCodigo.focus();\" class=\"titulo2\">Grupo</a>";
	}
	if ($ClasseCodigo == ""){
		if ($Mens==1){
			$Mensagem .= ", ";
		}
		$Mens = 1; $Tipo = 2; $Troca = 1;
		$Mensagem .= "<a href=\"javascript: document.MaterialPrecoEspecificacaoTecnica.ClasseCodigo.focus();\" class=\"titulo2\">Classe</a>";
	}
	if ($Mens==0){
		$Url = "CadMaterialEspecificacaoTecnicaResultado.php?GrupoCodigo=$GrupoCodigo&ClasseCodigo=$ClasseCodigo&ItemCodigo=$ItemCodigo";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit();
    }
}
?>

<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
<!--
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadMaterialEspecificacaoTecnicaConsultar.php" method="post" name="MaterialPrecoEspecificacaoTecnica">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Materiais > Especificação Técnica
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
			<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" bgcolor="#FFFFFF" class="textonormal" summary="">
        <tr>
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	           CONSULTAR - ESPECIFICAÇÃO TÉCNICA
          </td>
        </tr>
        <tr>
          <td class="textonormal">
             <p align="justify">
             Para consultar um especificação técnica cadastrada, selecione grupo e classe do material e clique no botão "Selecionar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table border="0" summary="">
            	<tr>
    	      		<td class="textonormal" bgcolor="#DCEDF7">Grupo*</td>
					<td class="textonormal">
					<select name="GrupoCodigo" class="textonormal" onChange="javascript:<?php if ($ClasseCodigo!=""){echo "document.MaterialPrecoEspecificacaoTecnica.ClasseCodigo.selectedIndex=0;";}?>document.MaterialPrecoEspecificacaoTecnica.Critica.value=0;document.MaterialPrecoEspecificacaoTecnica.submit();">
					<option value="">Selecione um Grupo...</option>
					<?php
				    $db   = Conexao();
					$sql  = "SELECT DISTINCT GRU.CGRUMSCODI, GRU.EGRUMSDESC ";
					$sql .= "  FROM SFPC.TBGRUPOMATERIALSERVICO GRU ";
					$sql .= " INNER JOIN SFPC.TBESPECIFICACAOTECNICA ESP ";
					$sql .= "    ON GRU.CGRUMSCODI = ESP.CGRUMSCODI ";
					$sql .= " ORDER BY GRU.EGRUMSDESC";
					$result = $db->query($sql);
					if( PEAR::isError($result) ){
  						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
					}
					while( $Linha = $result->fetchRow() ){
					   	if( $Linha[0] == $GrupoCodigo ){
							echo "<option value=\"$Linha[0]\" selected>$Linha[1]</option>\n";
					   	}else{
							echo "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
					   	}
	     			}
	     			$db->disconnect();
					?>
					</select>
					</td>
            	</tr>
            	<?php if ($GrupoCodigo!=""){ ?>
				<tr>
                	<td class="textonormal" bgcolor="#DCEDF7">Classe*</td>
                	<td class="textonormal">
					<select name="ClasseCodigo" value="" class="textonormal"
                            onChange="javascript:document.MaterialPrecoEspecificacaoTecnica.Critica.value=0;document.MaterialPrecoEspecificacaoTecnica.submit();">
                  	<option value="">Selecione uma Classe...</option>
                  	<?php
					$db   = Conexao();
					$sql  = "SELECT DISTINCT CLA.CCLAMSCODI, CLA.ECLAMSDESC ";
					$sql .= "  FROM SFPC.TBCLASSEMATERIALSERVICO CLA ";
					$sql .= " INNER JOIN SFPC.TBESPECIFICACAOTECNICA ESP ";
					$sql .= "    ON CLA.CGRUMSCODI = ESP.CGRUMSCODI ";
					$sql .= "   AND CLA.CCLAMSCODI = ESP.CCLAMSCODI ";
					$sql .= " WHERE CLA.CGRUMSCODI = $GrupoCodigo ";
					$sql .= " ORDER BY CLA.ECLAMSDESC";
					$result = $db->query($sql);
					if( PEAR::isError($result) ){
  						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
					}else{
						while( $Linha = $result->fetchRow() ){
							if( $Linha[0] == $ClasseCodigo ){
								echo "<option value=\"".$Linha[0]."\" selected>".$Linha[1]."</option>\n" ;
							} else {
								echo "<option value=\"".$Linha[0]."\">".$Linha[1]."</option>\n" ;
							}
						}
					}
					$db->disconnect();
					?>
					</select>
					</td>
				</tr>
				<?php } ?>
                <?php
                if ($ClasseCodigo!=""){
                    // Verificar se é serviço ou material pelo grupo
                    $db             = Conexao();
                    $sql_grupo    = "SELECT FGRUMSTIPO FROM SFPC.TBGRUPOMATERIALSERVICO WHERE CGRUMSCODI = $GrupoCodigo";
                    $result_grupo   = $db->query($sql_grupo);
                    $result_grupo   = resultValorUnico($result_grupo);
                    $tipoTexto = ($result_grupo) == 'M' ? 'Material' : 'Serviço';

                    if($result_grupo == 'M'){
                        $sql   = "SELECT MP.CMATEPSEQU, MP.EMATEPDESC FROM SFPC.TBMATERIALPORTAL MP
                                  LEFT JOIN SFPC.TBSUBCLASSEMATERIAL SCM ON 
                                      MP.CSUBCLSEQU = SCM.CSUBCLSEQU 
                                  LEFT JOIN SFPC.TBESPECIFICACAOTECNICA ET ON 
                                      ET.CMATEPSEQU = MP.CMATEPSEQU 
                                  WHERE SCM.CCLAMSCODI = $ClasseCodigo AND SCM.CGRUMSCODI = $GrupoCodigo ORDER BY MP.CMATEPSEQU";
                    } else {
                        $sql    = "SELECT SP.CSERVPSEQU, SP.ESERVPDESC FROM SFPC.TBSERVICOPORTAL SP
                                    LEFT JOIN SFPC.TBESPECIFICACAOTECNICA ET ON 
                                       ET.CSERVPSEQU = SP.CSERVPSEQU 
                                    WHERE SP.CCLAMSCODI = $ClasseCodigo ORDER BY SP.CSERVPSEQU";
                    }

                    $result = $db->query($sql);
                    ?>
                    <tr>
                        <td class="textonormal" bgcolor="#DCEDF7"><?php echo $tipoTexto; ?></td>
                        <td class="textonormal">
                            <select name="ItemCodigo" value="" class="textonormal">
                                <option value="">Selecione um <?php echo $tipoTexto; ?>...</option>
                                <?php
                                if( PEAR::isError($result) ){
                                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                }else{
                                    while( $Linha = $result->fetchRow() ){
                                        if( $Linha[0] == $ClasseCodigo ){
                                            echo "<option value=\"".$Linha[0]."\" selected>".$Linha[0]. " - " .$Linha[1]."</option>\n" ;
                                        } else {
                                            echo "<option value=\"".$Linha[0]."\">".$Linha[0]. " - " .$Linha[1]."</option>\n" ;
                                        }
                                    }
                                }
                                $db->disconnect();
                                ?>
                            </select>
                        </td>
                    </tr>
                <?php } ?>
            </table>
          </td>
        </tr>
        <tr>
 	        <td class="textonormal" align="right">
          	<input type="submit" value="Selecionar" class="botao">
          	<input type="hidden" name="Critica" value="1">
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
<script language="javascript" type="">
<!--
document.MaterialPrecoEspecificacaoTecnica.GrupoCodigo.focus();
//-->
</script>
