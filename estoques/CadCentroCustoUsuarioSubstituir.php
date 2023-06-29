<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadCentroCustoUsuarioSubstituir.php
# Autor:    Roberta Costa
# Data:     09/06/05
# Objetivo: Programa que Substitui o Aprovador do Centro de Custo
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/CadCentroCustoUsuarioSubstituirSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao             = $_POST['Botao'];
		$Usuario           = $_POST['Usuario'];
		$GrupoEmp          = $_POST['GrupoEmp'];
		$CentroCusto       = $_POST['CentroCusto'];
		$UsuarioSubstituto = $_POST['UsuarioSubstituto'];
		$DataIni           = $_POST['DataIni'];
		if( $DataIni != "" ){ $DataIni = FormataData($DataIni); }
		$DataFim           = $_POST['DataFim'];
		if( $DataFim != "" ){ $DataFim = FormataData($DataFim); }
}else{
		$Usuario = $_GET['Usuario'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Redireciona para a página de excluir #
if( $Botao == "Voltar" ){
	  header("location: CadCentroCustoUsuarioSubstituirSelecionar.php");
	  exit;
}elseif( $Botao == "Substituir" ){
		$Mens     = 0;
		$Mensagem = "Informe: ";
	  if( $UsuarioSubstituto == "" ) {
		    $Mens      = 1;
		    $Tipo      = 2;
  			$Mensagem .= "<a href=\"javascript:document.CadCentroCustoUsuarioSubstituir.UsuarioSubstituto.focus();\" class=\"titulo2\">Usuário Substituto</a>";
    }
 		if( $DataIni == "" ){
		  	$Mens 		 = 1;
		  	$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadCentroCustoUsuarioSubstituir.DataIni.focus();\" class=\"titulo2\">Data Inicial do Período</a>";
		}else{
				$MensErro = ValidaData($DataIni);
				if( $MensErro != "" ){
						$Mens      = 1;
				  	$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.CadCentroCustoUsuarioSubstituir.DataIni.focus();\" class=\"titulo2\">Data Inicial Válida</a>";
				}
		}
		if( $DataFim == "" ){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens 		 = 1;
		  	$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadCentroCustoUsuarioSubstituir.DataIni.focus();\" class=\"titulo2\">Data Final do Período</a>";
		}else{
				$MensErro = ValidaData($DataFim);
				if( $MensErro != "" ){
						if( $Mens == 1 ){ $Mensagem .= ", "; }
						$Mens      = 1;
				  	$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.CadCentroCustoUsuarioSubstituir.DataFim.focus();\" class=\"titulo2\">Data Final Válida</a>";
				}
		}

		if( $Mens == 0 ){
				$Dados      = explode($SimboloConcatenacaoDesc,$UsuarioSubstituto);
				$UsuarioSub = $Dados[0];
				$GrupoSub   = $Dados[1];

				# Atualiza a Tabela de Usuário Centro de Custo com o Substituto #
				$db = Conexao();
				$db->query("BEGIN TRANSACTION");
				$sql    = "UPDATE SFPC.TBUSUARIOCENTROCUSTO ";
				$sql   .= "   SET CGREMPCOD1 = $GrupoSub, CUSUPOCOD1 = $UsuarioSub, ";
				$sql   .= "       DUSUCCINIS = '".DataInvertida($DataIni)."', DUSUCCFIMS = '".DataInvertida($DataFim)."', ";
				$sql   .= "       TUSUCCULAT = '".date("Y-m-d H:i:s")."'";
				$sql   .= " WHERE CGREMPCODI = $GrupoEmp AND CUSUPOCODI = $Usuario ";
				$sql   .= "   AND CCENPOSEQU = $CentroCusto AND FUSUCCTIPO IN ('T','R') ";
				$result = $db->query($sql);
				if( PEAR::isError($result) ){
		        $db->query("ROLLBACK");
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}
        $db->query("COMMIT");
        $db->query("END TRANSACTION");
        $db->disconnect();

        # Envia mensagem para página selecionar #
        $Mensagem = urlencode("A Substituição do Usuário Aprovador para o Centro de Custo foi Efetuada com Sucesso");
        $Url = "CadCentroCustoUsuarioSubstituirSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1&Critica=0";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
        header("location: ".$Url);
        exit;
		}
		$Botao = "";
}

if( $Botao == "" ){
		# Carrega os dados do Centro de Custo selecionado #
		$db   = Conexao();
		$sql  = "SELECT A.CGREMPCODI, C.EGREMPDESC, D.EORGLIDESC, E.EUSUPORESP, ";
		$sql .= "       B.ECENPODESC, B.CCENPOSEQU ";
		$sql .= "  FROM SFPC.TBUSUARIOCENTROCUSTO A, SFPC.TBCENTROCUSTOPORTAL B, SFPC.TBGRUPOEMPRESA C, ";
		$sql .= "       SFPC.TBORGAOLICITANTE D, SFPC.TBUSUARIOPORTAL E ";
		$sql .= " WHERE A.CCENPOSEQU = B.CCENPOSEQU AND A.CGREMPCODI = C.CGREMPCODI AND A.FUSUCCTIPO IN ('T','R') ";
		$sql .= "   AND B.CORGLICODI = D.CORGLICODI ";
 		$sql .= "   AND A.CUSUPOCODI = E.CUSUPOCODI AND A.CUSUPOCODI = $Usuario ";
 		$sql .= "   AND B.FCENPOSITU <> 'I' "; // Inclusão da condição para mostrar centro de custos diferentes de inativos
		$sql .= " ORDER BY C.EGREMPDESC, D.EORGLIDESC, E.EUSUPORESP";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
						$GrupoEmp        = $Linha[0];
						$GrupoDesc       = $Linha[1];
						$OrgaoDesc       = $Linha[2];
						$UsuarioDesc     = $Linha[3];
						$CentroCustoDesc = $Linha[4];
						$CentroCusto     = $Linha[5];
				}
		}
		$db->disconnect();
}
?>
<html>
<?
# Carrega o layout padrão #
layout();
?>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.CadCentroCustoUsuarioSubstituir.Botao.value=valor;
	document.CadCentroCustoUsuarioSubstituir.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadCentroCustoUsuarioSubstituir.php" method="post" name="CadCentroCustoUsuarioSubstituir">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Centro de Custo > Substituição de Aprovador
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
	        	SUBSTITUIÇÃO DE APROVADOR
          </td>
        </tr>
        <tr>
          <td class="textonormal">
             <p align="justify">
             Para atualizar o Centro de Custo/Usuário, selecione uma ou mais Centro de Custo.
             Use (CTRL) +  clique no botão esquerdo do mouse para selecionar mais de um Centro de Custo.
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table border="0" summary="">
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Centro de Custo</td>
               	<td class="textonormal">
               		<?php echo $OrgaoDesc."<br>&nbsp;&nbsp;&nbsp;&nbsp;".$CentroCustoDesc; ?>
               	</td>
              </tr>
             	<tr>
        	   		<td class="textonormal" bgcolor="#DCEDF7" height="20">Usuário Aprovador</td>
        	   		<td class="textonormal"><?php echo $UsuarioDesc; ?></td>
        	  	</tr>
             	<tr>
        	   		<td class="textonormal" bgcolor="#DCEDF7">Usuário Substituto*</td>
        	   		<td class="textonormal">
                  <select name="UsuarioSubstituto" class="textonormal">
                  	<option value="">Selecione um Usuário...</option>
                  	<?
                		# Mostra os usuários cadastrados #
                		$db   = Conexao();
            				$sql  = "SELECT A.CUSUPOCODI, A.CGREMPCODI, B.EUSUPORESP ";
                		$sql .= "  FROM SFPC.TBUSUARIOCENTROCUSTO A, SFPC.TBUSUARIOPORTAL B ";
                 		$sql .= " WHERE A.CGREMPCODI = B.CGREMPCODI AND A.CUSUPOCODI = B.CUSUPOCODI AND A.FUSUCCTIPO IN ('T','R') ";
	              		$sql .= "   AND A.CGREMPCODI = $GrupoEmp AND A.CUSUPOCODI <> $Usuario ";
	              		$sql .= "   AND A.FUSUCCTIPO <> 'A' ";
                		$sql .= " ORDER BY B.EUSUPORESP";
                		$res  = $db->query($sql);
										if( PEAR::isError($res) ){
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												while( $Linha = $res->fetchRow() ){
		          							if( "$Linha[0]_$Linha[1]" == $UsuarioSubstituto ){
				          	      			echo"<option value=\"".$Linha[0].$SimboloConcatenacaoDesc.$Linha[1]."\" selected>$Linha[2]</option>\n";
				          	      	}else{
		          	      					echo"<option value=\"".$Linha[0].$SimboloConcatenacaoDesc.$Linha[1]."\">$Linha[2]</option>\n";
		          	      			}
			                	}
			              }
	           			 	$db->disconnect();
      	            ?>
                  </select>
								</td>
							</tr>
							<tr>
								<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Período*</td>
    	      		<td class="textonormal">
									<?php
    	      			$DataMes = DataMes();
    	      			if( $DataIni == "" ){ $DataIni = $DataMes[0]; }
									if( $DataFim == "" ){ $DataFim = $DataMes[1]; }
									$URLIni = "../calendario.php?Formulario=CadCentroCustoUsuarioSubstituir&Campo=DataIni";
									$URLFim = "../calendario.php?Formulario=CadCentroCustoUsuarioSubstituir&Campo=DataFim";
									?>
									<input type="text" name="DataIni" size="10" maxlength="10" value="<?php echo $DataIni;?>" class="textonormal">
									<a href="javascript:janela('<?php echo $URLIni ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
									&nbsp;a&nbsp;
									<input type="text" name="DataFim" size="10" maxlength="10" value="<?php echo $DataFim;?>" class="textonormal">
									<a href="javascript:janela('<?php echo $URLFim ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
								</td>
	          	</tr>
            </table>
          </td>
        </tr>
        <tr>
 	        <td class="textonormal" align="right">
          	<input type="hidden" name="Usuario" value="<?php echo $Usuario; ?>">
          	<input type="hidden" name="GrupoEmp" value="<?php echo $GrupoEmp; ?>">
          	<input type="hidden" name="CentroCusto" value="<?php echo $CentroCusto; ?>">
          	<input type="button" value="Substituir" class="botao" onclick="javascript:enviar('Substituir');">
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
