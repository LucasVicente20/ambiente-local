<?php
# -------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabOrgaoLicitanteExcluir.php
# Autor:    Roberta Costa
# Data:     01/04/03
# Objetivo: Programa de Exclusão do Órgão Licitante
# -------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     03/12/2018
# Objetivo: Tarefa Redmine 207615
# -------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso ('/tabelasbasicas/TabOrgaoLicitanteAlterar.php');
AddMenuAcesso ('/tabelasbasicas/TabOrgaoLicitanteSelecionar.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD']  == "POST") {
	$Botao                = $_POST['Botao'];
	$Critica              = $_POST['Critica'];
	$OrgaoLicitanteCodigo = $_POST['OrgaoLicitanteCodigo'];
} else {
	$OrgaoLicitanteCodigo = $_GET['OrgaoLicitanteCodigo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabOrgaoLicitanteExcluir.php";

if ($Botao == "Voltar") {
	$Url = "TabOrgaoLicitanteAlterar.php?OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo";
    
    if (!in_array($Url,$_SESSION['GetUrl'])) {
        $_SESSION['GetUrl'][] = $Url;
    }
	header("location: ".$Url);
	exit();
} else {
	# Critica dos Campos #
	if ($Botao == "Excluir") {
		$Mens = 0;
		$Mensagem = "Informe: ";

		$db = Conexao();
        
        $sql = "SELECT COUNT(*) FROM SFPC.TBLICITACAOPORTAL WHERE CORGLICODI = $OrgaoLicitanteCodigo";
        
        $result = $db->query($sql);
        
        if (PEAR::isError($result)) {
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		} else {
			$Linha = $result->fetchRow();
			$QtdLicitacao = $Linha[0];
            
            if ($QtdLicitacao > 0) {
				$Mensagem = urlencode("Exclusão Cancelada!<br>Órgão Relacionado com ($QtdLicitacao) Licitação(ões)");
				$Url = "TabOrgaoLicitanteSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=2";
                
                if (!in_array($Url,$_SESSION['GetUrl'])) {
                    $_SESSION['GetUrl'][] = $Url;
                }
				header("location: ".$Url);
				exit();
			} else {
				$sql = "SELECT COUNT(*) FROM SFPC.TBGRUPOORGAO WHERE CORGLICODI = $OrgaoLicitanteCodigo";
                
                $result = $db->query($sql);
                
                if (PEAR::isError($result)) {
					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				} else {
					$Linha = $result->fetchRow();
					$QtdGrupo = $Linha[0];
                    
                    if ($QtdGrupo > 0) {
						if ($Mens == 1) {
                            $Mensagem .= "<br>";
                        } else {
                            $Mensagem .= "Exclusão Cancelada!<br>";
                        }

					    # Envia mensagem para página selecionar #
						$Mensagem .= urlencode("Órgão Relacionado com ($QtdGrupo) Grupo(s)");
						$Url = "TabOrgaoLicitanteSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=2";
                        
                        if (!in_array($Url,$_SESSION['GetUrl'])) {
                            $_SESSION['GetUrl'][] = $Url;
                        }
						header("location: ".$Url);
						exit();
					}
                    
                    if ($Mens == 0) {
						# Exclui Orgao #
						$db->query("BEGIN TRANSACTION");
                        
                        $sql = "DELETE FROM SFPC.TBORGAOLICITANTE WHERE CORGLICODI = $OrgaoLicitanteCodigo";
                        
                        $result = $db->query($sql);
                        
                        if (PEAR::isError($result)) {
							$db->query("ROLLBACK");
							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						} else {
							$db->query("COMMIT");
							$db->query("END TRANSACTION");
							$db->disconnect();

						    # Envia mensagem para página selecionar #
							$Mensagem = urlencode("Órgão Licitante Excluído com Sucesso");
							$Url = "TabOrgaoLicitanteSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
                            
                            if (!in_array($Url,$_SESSION['GetUrl'])) {
                                $_SESSION['GetUrl'][] = $Url;
                            }
							header("location: ".$Url);
							exit();
						}
					}
				}
			}
		}
		$db->disconnect();
	}
}

if ($Critica == 0) {
	$db = Conexao();

    $sql = "SELECT EORGLIDESC, FORGLISITU, FORGLITIPO, EORGLIORDE, FORGLIEXVE, AORGLICNPJ FROM SFPC.TBORGAOLICITANTE WHERE CORGLICODI = $OrgaoLicitanteCodigo";

    $result = $db->query($sql);

    if (PEAR::isError($result)) {
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	} else {
		while ($Linha = $result->fetchRow()) {
			$OrgaoLicitanteDescricao    = $Linha[0];
			$Situacao                   = $Linha[1];
			$Tipo                       = $Linha[2];
            $OrdenadorDespesasDescricao = $Linha[3];
            $flagExibicaoValor          = $Linha[4];
            $cnpj                       = $Linha[5];
			$cnpj 						 = str_pad($cnpj, 14, "0",STR_PAD_LEFT);
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
	    document.Orgao.Botao.value=valor;
	    document.Orgao.submit();
    }
    <?php MenuAcesso(); ?>
    //-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
    <script language="JavaScript" src="../menu.js"></script>
    <script language="JavaScript">Init();</script>
    <form action="TabOrgaoLicitanteExcluir.php" method="post" name="Orgao">
        <br><br><br><br><br>
        <table cellpadding="3" border="0">
	        <!-- Caminho -->
	        <tr>
		        <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		        <td align="left" class="textonormal" colspan="2">
			        <font class="titulo2">|</font>
			        <a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Órgao Licitante > Excluir
		        </td>
	        </tr>
	        <!-- Fim do Caminho-->
	        <!-- Erro -->
	        <?php if ($Mens == 1) { ?>
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
						        EXCLUIR - ÓRGÃO LICITANTE
                            </td>
                        </tr>
                        <tr>
                            <td class="textonormal">
                                <p align="justify">
                                    Para confirmar a exclusão do Órgão Licitante clique no botão "Excluir", caso contrário clique no botão "Voltar".
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table>
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7" height="20">Órgão Licitante </td>
               	                        <td class="textonormal"><?php echo $OrgaoLicitanteDescricao; ?></td>
                                        <td class="textonormal">
                	                        <input type="hidden" name="OrgaoLicitanteCodigo" value="<?php echo $OrgaoLicitanteCodigo ?>" class="textonormal">
                	                        <input type="hidden" name="Critica" value="1">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7">CNPJ</td>
                                        <td class="textonormal">
                                            <?php echo $cnpj; ?>
                                        </td>


                                    </tr>
              	                    <tr>
	        	                        <td class="textonormal" bgcolor="#DCEDF7">Ordenador de Despesas</td>
	        	                        <td class="textonormal">
	          		                        <?php echo $OrdenadorDespesasDescricao; ?>
	                                    </td>
                                    </tr>
                                    <tr>
              	                        <td class="textonormal" bgcolor="#DCEDF7" height="20">Situação</td>
	                                    <td class="textonormal">
	                                        <?php
                                            if ($Situacao == "A") {
	                                            $DescSituacao = "ATIVO";
                                            } else {
                    	                        $DescSituacao = "INATIVO";
                                            }
                                            echo $DescSituacao;
	                                        ?>
                                        </td>
	                                </tr>
                                    <tr>
              	                        <td class="textonormal" bgcolor="#DCEDF7" height="20">Tipo de administração </td>
	                                    <td class="textonormal">
	                                        <?php
                                            if ($Tipo == "D") {
	                                            $DescSituacao = "DIRETA";
                                            } else {
                    	                        $DescSituacao = "INDIRETA";
                                            }
                                            echo $DescSituacao;
	                                        ?>
                                        </td>
	                                </tr>
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7" height="20">Exibição na internet do valor estimado de processos licitatórios em andamento</td>
                                        <td class="textonormal">
                                            <?php
                                            if ($flagExibicaoValor == "N") {
                                                $exibicaoValor = "NÃO";
                                            } else {
                                                $exibicaoValor = "SIM";
                                            }
                                            echo $exibicaoValor;
                                            ?>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
	                        <td class="textonormal" align="right">
						        <input type="button" value="Excluir" class="botao" onclick="javascript:enviar('Excluir')">
						        <input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar')">
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
<script language="JavaScript">
    $('#cnpj').mask('99.999.999/9999-99');
</script>