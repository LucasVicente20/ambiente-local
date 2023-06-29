<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadPregaoPresencialSelecionar.php
# Autor:    Madson Felix
# Data:     13/12/2019
# Objetivo: Programa para correçao de lack de dados no banco 
#-------------------------------------------------------------------------
include ('funcoes.php');
# Adiciona páginas no MenuAcesso #
AddMenuAcesso( 'corrige.php' );
$button = $_POST['botao'];



if( $button == 'Iniciar'){

	$db = conexao();

	$sqlDados = "select ia.carpnosequ, il.eitelpdescmat, ia.eitarpdescmat, il.citelpsequ
				from sfpc.tbitemataregistropreconova ia, 
				sfpc.tbataregistroprecointerna a 
				inner join sfpc.tbitemlicitacaoportal il
				on a.clicpoproc = il.clicpoproc
				and a.alicpoanop = il.alicpoanop
				and a.cgrempcodi = il.cgrempcodi
				and a.ccomlicodi = il.ccomlicodi
				and a.corglicodi = il.corglicodi
				where ia.carpnosequ = a.carpnosequ
				and ia.eitarpdescmat <> il.eitelpdescmat
				and ia.citarpsequ = il.citelpsequ";
				// usado para testar casos específicos and ia.carpnosequ = ";
	$i=0; 
	$result = $db->query($sqlDados);
	
	if( PEAR::isError($result) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlDados");
	}
	
	while (	$Linha = $result->fetchRow()) 
	{	
		$i++;
		$array['carpnosequ'][$i]     =    $Linha[0];
		$array['eitelpdescmat'][$i]  =    $Linha[1];
		$array['eitarpdescmat'][$i]  =    $Linha[2];
		$array['citelpsequ'][$i]  =    $Linha[3];
	}
	if($i == 0){
		echo "<script>alert('".$i." Ocorrências encontradas para a busca');</script>";
		return false;
	}
	
	
	for($j=1; $j <= $i; $j++){
		

		$sqlUpdate  = 	"update sfpc.tbitemataregistropreconova ";
		$sqlUpdate .=	"set eitarpdescmat = '".$array['eitelpdescmat'][$j]."'";
		$sqlUpdate .=	" where carpnosequ = ".$array['carpnosequ'][$j];
		$sqlUpdate .=   " and citarpsequ = ".$array['citelpsequ'][$j];


		$resultUpdate = $db->query($sqlUpdate);
		
		if( PEAR::isError($resultUpdate) ){
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlUpdate");
		}

	}
	echo "<script>alert('Alteração concluida! ".$i." alterações realizadas');</script>";
}

?>
<html>

<script language="javascript" type="">
<!--
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" bgcolor="#FFFFFF" text="#000000" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="corrige.php" method="post" name="CorrigeBD">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    
  </tr>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<tr>
	  <td width="150"></td>
	  
	  <td align="left" colspan="2">
			<?php if( $_SESSION['Mens'] != 0 ){ ExibeMens($_SESSION['Mensagem'],$_SESSION['Tipo'],$_SESSION['Virgula']); }
			
			$_SESSION['Mens'] = null;
			$_SESSION['Tipo'] = null;
			$_SESSION['Mensagem'] = null	
			
			?>	  
	  </td>
	</tr>

	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="150"></td>
		<td class="textonormal">
			<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
        <tr>
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	           Corrigir tbitemataregistrodepreconova
          </td>
        </tr>
        <tr>
          <td class="textonormal" bgcolor="#FFFFFF">
             <p align="center">
             Clique em iniciar para realizar a correção.
             </p>
          </td>
        </tr>
        <tr>
 	        <td class="textonormal" align="center">
          	<input type="submit" name="botao" value="Iniciar" class="botao">
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
