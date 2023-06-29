<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadCentroCustoUsuarioManter.php
# Objetivo: Programa que liga o Usuário ao(s) Centro(s) de Custo
# Autor:    Roberta Costa
# Data:     09/06/05
# Alterado: Marcus Thiago
# Data:			09/01/2005
# Alterado: Carlos Abreu
# Data:     11/06/2007 - Colocado condicao de visao para usuario corporativo na lista de usuarios
# Alterado: Carlos Abreu
# Data:     26/06/2007 - Correção na troca de centro de custo
# Alterado: Carlos Abreu
# Data:     26/06/2007 - Correção na escolha do almoxarifado quando atendimento
# Alterado: Carlos Abreu
# Data:     16/08/2007 - Correção para alterar dados apenas do centro de custo selecionado
# Alterado: Carlos Abreu
# Data:     05/10/2007 - Ajuste na exibicao dos usuarios do centro de custo quando usuario possuir visao corporativa
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------
# Acesso ao arquivo de funções #
include '../funcoes.php';
# Executa o controle de segurança #
session_start();
Seguranca();
# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/CadCentroCustoUsuarioSelecionar.php' );
# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao       = $_POST['Botao'];
		$Usuario     = $_POST['Usuario'];
		$TipoUsuario = $_POST['TipoUsuario'];
		$GrupoEmp    = $_POST['GrupoEmp'];
		$CentroCusto = $_POST['CentroCusto'];
		$Orgao       = $_POST['Orgao'];
		if ($TipoUsuario=="T"){
			$Almoxarifado = $_POST['Almoxarifado'];
		}
}else{
		$CentroCusto = $_GET['CentroCusto'];
		$TipoUsuario = $_GET['TipoUsuario'];
}
# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;
# Redireciona para a página de excluir #
if( $Botao == "Voltar" ){
	  header("location: CadCentroCustoUsuarioSelecionar.php");
	  exit;
}elseif( $Botao == "Manter" ){
	$Mens     = 0;
	$Mensagem = "Informe: ";
	if( $TipoUsuario == "" ) {
	    $Mens      = 1;
	    $Tipo      = 2;
			$Mensagem .= "<a href=\"javascript:document.CadCentroCustoUsuarioManter.TipoUsuario.focus();\" class=\"titulo2\">Tipo De Usuário</a>";
	} elseif ($TipoUsuario == "T") {
		if ($Almoxarifado == ""){
			$Mens      = 1;
		    $Tipo      = 2;
  			$Mensagem .= "<a href=\"javascript:document.CadCentroCustoUsuarioManter.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
		}
	}
	if( $Mens == 0 ){
	    # Deleta os centros de custo para o usuário #
		$db = Conexao();
		$db->query("BEGIN TRANSACTION");
	    $sql  = "DELETE FROM SFPC.TBUSUARIOCENTROCUSTO ";
	    $sql .= " WHERE CCENPOSEQU = $CentroCusto ";
	    if ($TipoUsuario=='T'){
	    	$sql .= "   AND FUSUCCTIPO = '$TipoUsuario' AND CALMPOCODI = $Almoxarifado ";
	    } else {
	    	$sql .= "   AND FUSUCCTIPO = '$TipoUsuario' ";
	    }
	    $sql .= "   AND FUSUCCTIPO IN ('T','R') ";
		$res  = $db->query($sql);
		if( PEAR::isError($res) ) {
			$db->query("ROLLBACK");
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
			# Verifica se foram selecionadas os usuários #
			for( $i=0;$i< count($Usuario);$i++ ){
				if( $Usuario[$i] != "" ){
					$Seleciona = "S";
					$Dados = explode($SimboloConcatenacaoDesc,$Usuario[$i]);
					$UsuarioCC = $Dados[0];
					$GrupoEmp  = $Dados[1];
					$UsuarioSelecionados[count($UsuarioSelecionados)] = $UsuarioCC;
				}
			}
			if( $Seleciona == "S" ){
				# Valores Provisórios #
				$GrupoSub   = "NULL";
				$UsuarioSub = "NULL";
				$DataIniSub = "NULL";
				$DataFimSub = "NULL";
				for( $i=0;$i < count($UsuarioSelecionados);$i++ ){
					$Dados        = explode("_",$UsuarioSelecionados[$i]);
					$UsuarioDados = $Dados[0];
					$GrupoDados   = $Dados[1];
					$Data = date("Y-m-d H:i:s");
					if ($TipoUsuario == 'R'){
						$Almoxarifado = 'NULL';
					}
					
					$sql  = "DELETE FROM SFPC.TBUSUARIOCENTROCUSTO ";
					$sql .= " WHERE CGREMPCODI = $GrupoDados ";
					$sql .= "   AND CUSUPOCODI = $UsuarioDados ";
					$sql .= "   AND CCENPOSEQU = $CentroCusto ";
					$result = $db->query($sql);
					if( PEAR::isError($result) ){
						$db->query("ROLLBACK");
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
					}
					
					$sql  = "INSERT INTO SFPC.TBUSUARIOCENTROCUSTO ( ";
					$sql .= "CGREMPCODI, CUSUPOCODI, CCENPOSEQU, FUSUCCTIPO, ";
					$sql .= "CGREMPCOD1, CUSUPOCOD1, DUSUCCINIS, DUSUCCFIMS, ";
					$sql .= "TUSUCCULAT, CALMPOCODI ";
					$sql .= " ) VALUES ( ";
					$sql .= "$GrupoDados, $UsuarioDados, $CentroCusto, '$TipoUsuario', ";
					$sql .= "$GrupoSub, $UsuarioSub, $DataIniSub, $DataFimSub, ";
					$sql .= "'$Data', $Almoxarifado)";
					$result = $db->query($sql);
					if( PEAR::isError($result) ){
						$db->query("ROLLBACK");
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
					}
				}
				$Almoxarifado = null;
			}
	        $db->query("COMMIT");
	        $db->query("END TRANSACTION");
	        $db->disconnect();
	        # Envia mensagem para página selecionar #
	        $Mens     = 1;
	        $Tipo     = 1;
	        $Mensagem = "A Manutenção de Usuário/Centro de Custo foi Executada com Sucesso";
		}
	}
	$Botao = "";
}
if( $Botao == "" or $Botao == "TipoUsuario" ){
		# Carrega os dados do Centro de Custo selecionado #
		$db     = Conexao();
		$sql    = "SELECT A.ECENPODESC, B.EORGLIDESC, A.CORGLICODI, A.CCENPONRPA, A.ECENPODETA ";
		$sql   .= "  FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBORGAOLICITANTE B ";
		$sql   .= " WHERE A.CORGLICODI = B.CORGLICODI AND A.CCENPOSEQU = $CentroCusto ";
		$sql	 .= "   AND A.FCENPOSITU <> 'I' "; // Inclusão da condição para mostrar centro de custos diferentes de inativos
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
						$DescCentroCusto = $Linha[0];
						$DescOrgao       = $Linha[1];
						$Orgao           = $Linha[2];
						$RPA             = $Linha[3];
						$Detalhamento    = $Linha[4];
				}
		}
		# Carrega os dados do usuário/centro de custo #
		$sql    = "SELECT CUSUPOCODI, CGREMPCODI, FUSUCCTIPO, CALMPOCODI FROM SFPC.TBUSUARIOCENTROCUSTO ";
		$sql   .= " WHERE CCENPOSEQU = $CentroCusto AND FUSUCCTIPO IN ('T','R') ";
		if( $TipoUsuario != "" ){
				$sql   .= " AND FUSUCCTIPO = '$TipoUsuario' ";
				if ($TipoUsuario=='T' and $Almoxarifado != ""){
					$sql .= " AND CALMPOCODI = $Almoxarifado ";
				}
		}
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
						$UsuarioCentroCusto[] .= $Linha[0].$SimboloConcatenacaoDesc.$Linha[1].$SimboloConcatenacaoDesc.$Linha[3];
						$TipoUsuario           = $Linha[2];
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
	document.CadCentroCustoUsuarioManter.Botao.value=valor;
	document.CadCentroCustoUsuarioManter.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadCentroCustoUsuarioManter.php" method="post" name="CadCentroCustoUsuarioManter">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Centro de Custo > Centro de Custo/Usuário
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
	        	MANTER - CENTRO DE CUSTO/USUÁRIO
          </td>
        </tr>
        <tr>
          <td class="textonormal">
             <p align="justify">
             Para associar um ou mais Usuários ao Centro de Custo selecionado, marque a caixa ao lado do nome do Usuário.
             Para remover desmarque a caixa. A operação tem que ser confirmada com um clique em "Manter".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table border="0" summary="">
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Centro de Custo</td>
               	<td class="textonormal">
	               	<?php
	               	echo $DescOrgao."<br>&nbsp;&nbsp;&nbsp;&nbsp;";
	               	echo "RPA ".$RPA."<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	               	echo $DescCentroCusto."<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
									echo $Detalhamento;
	               	?>
               	</td>
              </tr>
             	<tr>
        	   		<td class="textonormal" bgcolor="#DCEDF7">Tipo de Usuário</td>
        	   		<td class="textonormal">
									<select name="TipoUsuario" class="textonormal" onChange="enviar('TipoUsuario');">
										<option value="">Selecione um Tipo de Usuário...</option>
										<option value="R" <?php if( $TipoUsuario == "R" ){ echo "selected"; }?>>REQUISITANTE</option>
										<option value="T" <?php if( $TipoUsuario == "T" ){ echo "selected"; }?>>ATENDIMENTO</option>
									</select>
	        	  	</td>
        	  	</tr>
        	  	<?php
        	  	
        	  	if ($TipoUsuario=="T"){
        	  		echo "<tr>\n";
        	  		echo "<td class=\"textonormal\" bgcolor=\"#DCEDF7\">Almoxarifado</td>";
        	  		echo "<td class=\"textonormal\">\n";
					# Mostra o(s) Almoxarifado(s) de Acordo com o Usuário Logado #
					$db  = Conexao();
					$sql  = "SELECT A.CALMPOCODI, A.EALMPODESC FROM SFPC.TBCENTROCUSTOPORTAL CC ";
					$sql .= " INNER JOIN SFPC.TBALMOXARIFADOORGAO AO ";
					$sql .= "    ON CC.CORGLICODI = AO.CORGLICODI ";
					$sql .= " INNER JOIN SFPC.TBALMOXARIFADOPORTAL A ";
					$sql .= "    ON AO.CALMPOCODI = A.CALMPOCODI ";
					$sql .= " WHERE CC.CCENPOSEQU = $CentroCusto ";
					if($Almoxarifado){
							$sql   .= " AND A.CALMPOCODI = $Almoxarifado AND A.FALMPOSITU = 'A' ";
					}
					$sql .= " ORDER BY A.EALMPODESC ";
					$res  = $db->query($sql);
					if( PEAR::isError($res) ){
							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
					}else{
							$Rows = $res->numRows();
							if($Rows == 1){
									$Linha = $res->fetchRow();
									$Almoxarifado     = $Linha[0];
									$DescAlmoxarifado = $Linha[1];
									echo "$DescAlmoxarifado<br>";
									echo "<input type=\"hidden\" name=\"Almoxarifado\" value=\"$Almoxarifado\">";
							}elseif( $Rows > 1 ){
									echo "<select name=\"Almoxarifado\" class=\"textonormal\" onChange=\"submit();\">\n";
									echo "	<option value=\"\">Selecione um Almoxarifado...</option>\n";
									for($i=0; $i< $Rows; $i++){
											$Linha = $res->fetchRow();
											$DescAlmoxarifado = $Linha[1];
											if($Linha[0] == $Almoxarifado){
													echo"<option value=\"$Linha[0]\" selected>$DescAlmoxarifado</option>\n";
											}else{
													echo"<option value=\"$Linha[0]\">$DescAlmoxarifado</option>\n";
											}
									}
									echo "</select>\n";
									$CarregaAlmoxarifado = "";
							}else{
									echo "ALMOXARIFADO NÃO CADASTRADO, INATIVO OU SOB INVENTÁRIO";
									echo "<input type=\"hidden\" name=\"CarregaAlmoxarifado\" value=\"N\">";
							}
					}
					$db->disconnect();
        	  		echo "</td>\n";
        	  		echo "</tr>\n";
        	  	}
        	  	
	           	# Pega a descrição do Perfil do usuário logado #
				if( $_SESSION['_cperficodi_'] != 2 and $_SESSION['_cperficodi_'] != 0 ){
						$db  = Conexao();
						$sqlusuario = "SELECT CPERFICODI, EPERFIDESC FROM SFPC.TBPERFIL ";
						$sqlusuario .= "WHERE CPERFICODI = ".$_SESSION['_cperficodi_']." ";
						$resultUsuario = $db->query($sqlusuario);
						if( PEAR::isError($result) ){
						    ExibeErroBD("$ErroPrograma\nLinha: 239\nSql: $sqlusuario");
						}else{
							$PerfilUsuario = $resultUsuario->fetchRow();
							$PerfilUsuarioDesc = $PerfilUsuario[1];
						}
				}
				if (($TipoUsuario=='T' and $Almoxarifado!="") or $TipoUsuario!='T'){
				?>
             	<tr>
        	   		<td class="textonormal" bgcolor="#DCEDF7" valign="top">Usuário</td>
        	   		<td class="textonormal">
                  	<?php
            		# Mostra os usuários cadastrados #
            		$db   = Conexao();
        			$sql  = "SELECT DISTINCT(A.CUSUPOCODI), A.EUSUPORESP, B.CGREMPCODI ";
            		$sql .= "  FROM SFPC.TBUSUARIOPORTAL A, SFPC.TBGRUPOEMPRESA B, SFPC.TBGRUPOORGAO C, ";
            		$sql .= "       SFPC.TBORGAOLICITANTE D ";
             		$sql .= " WHERE A.CGREMPCODI = B.CGREMPCODI AND A.CGREMPCODI = C.CGREMPCODI ";
              		$sql .= "   AND C.CORGLICODI = D.CORGLICODI AND C.CORGLICODI = $Orgao";
              		$sql .= "   AND A.CGREMPCODI <> 0 "; // Para retirar o grupo internet
            		if ($_SESSION['_cgrempcodi_'] != 0 and $_SESSION['_fperficorp_'] != 'S' ) {
		                  $sql .= "   AND A.CGREMPCODI = ".$_SESSION['_cgrempcodi_']."";
                  }
            		$sql .= " ORDER BY A.EUSUPORESP";
            		$res  = $db->query($sql);
							if( PEAR::isError($res) ){
							    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
							}else{
									while( $Linha = $res->fetchRow() ){
											if( FindArray($Linha[0].$SimboloConcatenacaoDesc.$Linha[2].$SimboloConcatenacaoDesc.$Almoxarifado,$UsuarioCentroCusto) ){
													$CheckBox[$L] = "<tr><td class=\"textonormal\">\n<input type=\"checkbox\" checked name=\"Usuario[]\" value=\"$Linha[0]_$Linha[2]\"> $Linha[1]\n</td></tr>\n";
											}else{
													$CheckBox[$L] = "<tr><td class=\"textonormal\">\n<input type=\"checkbox\" name=\"Usuario[]\" value=\"$Linha[0]_$Linha[2]\"> $Linha[1]\n</td></tr>\n";
											}
											echo "
												<table width='100%' border='0' cellpadding='2' cellspacing='0' bordercolor='#75ADE6' summary='' class='textonormal' bgcolor='#FFFFFF'>
										        <tr>
										          <td align='left' valign='middle' class='textonormal'>$CheckBox[$L]
										          </td>
										        </tr>
										    </table>";
		                	}
		              }
           			 	$db->disconnect();
      	            ?>
					</td>
				</tr>
				<?php } ?>
            </table>
          </td>
        </tr>
        <tr>
 	        <td class="textonormal" align="right">
          	<input type="hidden" name="GrupoEmp" value="<?php echo $GrupoEmp; ?>">
          	<input type="hidden" name="CentroCusto" value="<?php echo $CentroCusto; ?>">
          	<input type="hidden" name="Orgao" value="<?php echo $Orgao; ?>">
          	<input type="button" value="Manter" class="botao" onclick="javascript:enviar('Manter');">
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
</body>
</html>
