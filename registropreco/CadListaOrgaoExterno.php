<?php


# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança	#
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
// AddMenuAcesso( '/estoques/CadItemDetalhe.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST" ){
		$ProgramaOrigem  = $_POST['ProgramaOrigem'];
		$Botao           = $_POST['Botao'];
		$Descricao       = strtoupper2(trim($_POST['Descricao']));

}else{
		$ProgramaOrigem	= $_GET['ProgramaOrigem'];
		$Requisitante		= $_GET['TipoUsuario'];
}
# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;
$ProgramaOrigem = 'CadAtaRegistroPrecoCaronaAtaExternaIncluir';
// $permiteCorporativo=true;
// $modulo = 'E'; //Estoques
// if($ProgramaOrigem=='CadSolicitacaoCompraIncluirManterExcluir'){
// 	//$permiteCorporativo=false;
// 	$modulo = 'C'; //Compras
// }

if( $Botao == "Incluir" ){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( $CentroCustoSel == "" ){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadIncluirCentroCusto.CentroCustoSel.focus();\" class=\"titulo2\">Centro de Custo</a>";
		}
		if( $Mens == 0 ){
				echo "<script>opener.document.$ProgramaOrigem.orgaoCarona</script>";
				echo "<script>opener.document.$ProgramaOrigem.submit()</script>";
				echo "<script>self.close()</script>";
		}
}elseif( $Botao == "Selecionar" ){
        $_SESSION['orgaoCarona'] = $_POST['orgaoCarona'];
		echo "<script>opener.document.$ProgramaOrigem.orgaoCarona.value=$descricaoOrgao</script>";
		echo "<script>opener.document.$ProgramaOrigem.submit()</script>";
		echo "<script>self.close()</script>";
}elseif( $Botao == "Pesquisar" ){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( $Descricao == "" and $Todos == "" and $Orgao == "" ){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadIncluirCentroCusto.Descricao.focus();\" class=\"titulo2\">Descrição Centro de Custo</a>";
		}
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

?>
<html>
<head>  
<script language="javascript" type="">
function enviar(valor,seq,detalhe){
	// document.CadListaOrgaoExterno.CentroCusto.value = seq;
	document.CadListaOrgaoExterno.Botao.value = valor;
	document.CadListaOrgaoExterno.submit();
}
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
</head>
<body background="../midia/bg.jpg" marginwidth="0" marginheight="0">
<form action="CadListaOrgaoExterno.php" method="post" name="CadListaOrgaoExterno">
	<table cellpadding="0" border="0" summary="">
		<!-- Erro -->
		<tr>
			<td align="left" colspan="4">
				<?php if( $Mens != 0 ){ ExibeMens($Mensagem,$Tipo,1);	}?>
			</td>
		</tr>
		<!-- Fim do Erro -->

		<!-- Corpo -->
		<tr>
			<td class="textonormal">
				<table border="0" cellspacing="0" cellpadding="3" summary="">
					<tr>
						<td class="textonormal">
							<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
								</tr>
								
								<?php

                                $db = Conexao();
                                $sql = 'select distinct(coe.ecaroeorgg)
                                from sfpc.tbcaronaorgaoexterno coe
                                order by coe.ecaroeorgg asc';
                                // var_dump($sql);die;
                                $res  = $db->query($sql);
                                if( PEAR::isError($res) ){
                                    EmailErroDB('Erro de SQL', 'erro de SQL', $res);
                                }else{
                                        
                                        echo "<tr>\n";
                                        echo "  <td align=\"center\" bgcolor=\"#75ADE6\" colspan=\"4\" class=\"titulo3\">ÓRGÃO EXTERNO</td>\n";
                                        echo "  </tr>\n";
                                        $rows = $res->numRows();
                                        for( $i=0;$i<$rows;$i++ ){
                                                $Cont++;
                                                $Linha      = $res->fetchRow();
                                                $descricaoOrgao = $Linha[0];
                                                
                                                if(!empty($descricaoOrgao)) {
                                    
                                                    echo "  <tr>";
                                                    echo "  <td>";
                                                    echo "  <input type=\"radio\" name=\"orgaoCarona\" value=\"$descricaoOrgao\" id=\"$Cont\" ";
                                                    echo "  <label for=\"$Cont\">$descricaoOrgao</label><br>";
                                                    echo "  </td>\n";
                                                    echo "  </tr>\n";
                                                    echo "  </tr>\n";
                                                        
                                                }else{
                                                echo "<tr>\n";
                                                echo "	<td valign=\"top\" colspan=\"4\" class=\"textonormal\" bgcolor=\"FFFFFF\">\n";
                                                echo "		Pesquisa sem Ocorrências.\n";
                                                echo "	</td>\n";
                                                echo "</tr>\n";
                                                }
                                            }
                                            
                                $db->disconnect();
                                }
                                
								?>
                                <tr>
									<td colspan="4" align="right">
										<input type="hidden" name="$orgaoCarona" value="<?php echo $descricaoOrgao; ?>">
										<input type="button" value="Cadastrar Novo Órgão Externo" class="botao" onclick="javascript:enviar('Novo');">
										<input type="button" value="Selecionar" class="botao" onclick="javascript:enviar('Selecionar');">
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
<script language="javascript" type="">
window.focus();
//-->
</script>
</body>
</html>