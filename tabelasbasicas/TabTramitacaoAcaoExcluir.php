<?php
/**
 * Portal de Compras
 * 
 * Programa: TabTramitacaoAcaoExcluir.php
 * Autor:    Lucas Baracho
 * Data:     12/07/2018
 * Objetivo: Tarefa Redmine 199046
 * ------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     27/05/2019
 * Objetivo: Tarefa Redmine 217614
 * ------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     03/06/2019
 * Objetivo: Tarefa Redmine 218099
 * ------------------------------------------------------------------------------------
 */

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/tabelasbasicas/TabTramitacaoAcaoAlterar.php');
AddMenuAcesso('/tabelasbasicas/TabTramitacaoAcaoSelecionar.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD']  == "POST") {
	$Botao			= $_POST['Botao'];
	$Critica		= $_POST['Critica'];
	$acaoCodigo	= $_POST['AcaoCodigo'];
} else {
	$acaoCodigo = $_GET['AcaoCodigo'];
}

# Critica dos Campos #
if ($Botao == "Voltar") {
	$Url = "TabTramitacaoAcaoAlterar.php?AcaoCodigo=$acaoCodigo";
    
    if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	    header("location: ".$Url);
	    exit();
} else {
	if ($Critica == 1) {
		$Mens     = 0;
		$Mensagem = "Informe: ";

		# Verifica se a ação está relacionada com algum processo #
		$db = Conexao();

        $sql = "SELECT COUNT(*) FROM SFPC.TBTRAMITACAOLICITACAO WHERE CTACAOSEQU = $acaoCodigo";

        $result = $db->query($sql);

        if (PEAR::isError($result)) {
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		} else {
			$Linha = $result->fetchRow();
			$qtdProceso = $Linha[0];

			if ($qtdProceso > 0) {
				$Mensagem = urlencode("Exclusão cancelada!<br>Ação relacionada com ($qtdProceso) processo(s)");
				$Url = "TabTramitacaoAcaoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=2";

				if (!in_array($Url,$_SESSION['GetUrl'])) {
                    $_SESSION['GetUrl'][] = $Url;
                }
                
                header("location: ".$Url);
				exit();
			}

			if ($Mens == 0) {
				# Exclui ações #
				$db = Conexao();

				// Exclusão do checklist
				$db->query("BEGIN TRANSACTION");

				$sql  = "DELETE FROM sfpc.tbtramitacaoacaochecklist  ";
				$sql .= "WHERE ctacaosequ = ".$acaoCodigo;

				$res = $db->query($sql);

				if (PEAR::isError($res)) {
					$db->query("ROLLBACK");
					ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
				} else {
					$db->query("COMMIT");
					$db->query("END TRANSACTION");
				}

				// Exclusão da ação
				$db->query("BEGIN TRANSACTION");

                $sql = "DELETE FROM SFPC.TBTRAMITACAOACAO WHERE CTACAOSEQU = $acaoCodigo";

				$result = $db->query($sql);

				if (PEAR::isError($result)) {
					$db->query("ROLLBACK");
					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				} else {
					$db->query("COMMIT");
					$db->query("END TRANSACTION");
					$db->disconnect();

					# Envia mensagem para página selecionar #
					$Mensagem = urlencode("Ação excluída com sucesso");
					$Url = "TabTramitacaoAcaoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";

					if (!in_array($Url,$_SESSION['GetUrl'])) {
                        $_SESSION['GetUrl'][] = $Url;
                    }

                    header("location: ".$Url);
					exit();
				}

                $db->disconnect();				
			}
		}
	}
}

if ($Critica == 0) {
	$db = Conexao();

    $sql = "SELECT CTACAOSEQU, CGREMPCODI, ETACAODESC, ATACAOORDE, ATACAOPRAZ, FTACAOSITU FROM SFPC.TBTRAMITACAOACAO WHERE CTACAOSEQU = $acaoCodigo";

    $result = $db->query($sql);

    if (PEAR::isError($result)) {
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	} else {
		while ($Linha = $result->fetchRow()) {
			$grupo		   = $Linha[1];
			$acaoDescricao = $Linha[2];
			$ordem         = $Linha[3];
			$prazo         = $Linha[4];
			$situacao      = $Linha[5];
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
	    document.Acao.Botao.value=valor;
	    document.Acao.submit();
    }
    <?php MenuAcesso(); ?>
    //-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
    <script language="JavaScript" src="../menu.js"></script>
    <script language="JavaScript">Init();</script>
    <form action="TabTramitacaoAcaoExcluir.php" method="post" name="Acao">
        <br><br><br><br><br>
        <table cellpadding="3" border="0">
	        <!-- Caminho -->
	        <tr>
		        <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    	        <td align="left" class="textonormal">
      		        <font class="titulo2">|</font>
      			        <a href="../index.php">
			        <font color="#000000">Página Principal</font></a> > Tabelas > Licitações > Tramitação > Ação > Manter > Excluir
		        </td>
	        </tr>
	        <!-- Fim do Caminho-->
	        <!-- Erro -->
            <?php
            if ($Mens == 1) {
                ?>
  	            <tr>
  		            <td width="150"></td>
		            <td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
	            </tr>
                <?php
            }
            ?>
	        <!-- Fim do Erro -->
	        <!-- Corpo -->
	        <tr>
		        <td width="150"></td>
		        <td class="textonormal">
			        <table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#ffffff">
                        <tr>
                            <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">EXCLUIR - AÇÃO</td>
                        </tr>
                        <tr>
                            <td class="textonormal">
                                <p align="justify">
                                    Para confirmar a exclusão da ação, clique no botão "Excluir", caso contrário clique no botão "Voltar".
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table border="0">
				                    <tr>
					                    <td class="textonormal" bgcolor="#DCEDF7" height="20">Grupo</td>
					                    <td class="textonormal">
						                    <?php  # Mostra os grupos #
                		   		            $db = Conexao();

                                            $sql = "SELECT CGREMPCODI, EGREMPDESC FROM SFPC.TBGRUPOEMPRESA WHERE CGREMPCODI = $grupo";

                                            $result = $db->query($sql);

                                            if (PEAR::isError($result) ){
									            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								            } else {
									            while ($Linha = $result->fetchRow()) 
									                echo $Linha[1];
								            }

                                            $db->disconnect();
						                    ?>
					                    </td>
				                    </tr>
            	                    <tr>
                	                    <td class="textonormal" bgcolor="#DCEDF7" height="20">Ação</td>
               		                    <td class="textonormal"><?php echo $acaoDescricao; ?>
    	            	                    <input type="hidden" name="Critica" value="1">
        	        	                    <input type="hidden" name="AcaoCodigo" value="<?php echo $acaoCodigo; ?>">
                	                    </td>
              	                    </tr>
              	                    <tr>
					                    <td class="textonormal" bgcolor="#DCEDF7" height="20">Ordem de Exibição</td>
					                    <td class="textonormal"><?php echo $ordem; ?></td>
              	                    </tr>
				                    <tr>
					                    <td class="textonormal" bgcolor="#DCEDF7" height="20">Prazo</td>
					                    <td class="textonormal"><?php echo $prazo; ?></td>
				                    </tr>
				                    <tr>
					                    <td class="textonormal" bgcolor="#DCEDF7" height="20">Situação</td>
					                    <td class="textonormal"><?php echo ($situacao == 'A') ? 'Ativo' : 'Inativo'; ?></td>
				                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
 	                        <td class="textonormal" align="right">
          	                    <input type="button" value="Excluir" class="botao" onclick="javascript:enviar('Excluir');">
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