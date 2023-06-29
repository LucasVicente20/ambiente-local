<?php
# -------------------------------------------------------------------------
# Prefeitura do Recife
# Portal de Compras
# Programa: TabTramitacaoAcaoAlterar.php
# Autor:    Lucas Baracho
# Data:     12/07/2018
# Objetivo: Tarefa Redmine 199049
# -------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso ('/tabelasbasicas/TabTramitacaoAcaoExcluir.php');
AddMenuAcesso ('/tabelasbasicas/TabTramitacaoAcaoSelecionar.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Botao         = $_POST['Botao'];
    $Critica       = $_POST['Critica'];
    $acaoCodigo    = $_POST['AcaoCodigo'];
    $acaoDescricao = strtoupper2(trim($_POST['AcaoDescricao']));
    $ordem         = trim($_POST['Ordem']);
    $prazo         = trim($_POST['Prazo']);
    $grupo         = trim($_POST['GrupoCodigo']);
    $situacao      = trim($_POST['Situacao']);
    $acaoInicial   = trim($_POST['AcaoInicial']);
    $acaoFinal     = trim($_POST['AcaoFinal']);
    $infoComissao  = trim($_POST['InformarComissao']);
    $obrigaAnexo   = trim($_POST['ObrigaAnexo']);
    $enviarTodos   = trim($_POST['EnviarTodos']);
    $dadosChecklist = $_POST['dados'];
    
    
    

} else {
    $acaoCodigo = $_GET['AcaoCodigo'];
}

//carrega o nome do usuário
$db = Conexao();

$sqlUsuario = "SELECT u.eusuporesp
FROM sfpc.tbusuarioportal u
where u.cusupocodi =". $_SESSION['_cusupocodi_'];

$usuarioDesc = '';

$res = $db->query($sqlUsuario);

if (PEAR::isError($res)) {
    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sqlUsuario");
} else {
    $Linha = $res->fetchRow();
    $usuarioDesc = $Linha[0];

}


# Redireciona para a página de excluir #
if ($Botao == "Excluir") {
    $Url = "TabTramitacaoAcaoExcluir.php?AcaoCodigo=$acaoCodigo";
    
    if (!in_array($Url, $_SESSION['GetUrl'])) {
        $_SESSION['GetUrl'][] = $Url;
    }
    
    header("location: " . $Url);
    exit();
} elseif ($Botao == "Voltar") {
    header("location: TabTramitacaoAcaoSelecionar.php");
    exit();
} else {

}

if ($Critica == 1) {
    # Critica dos Campos #
    $Mens = 0;
    $Mensagem = "Informe: ";
    
    if ($grupo == "") {
        $Critica   = 1;
        $LerTabela = 0;
        $Mens      = 1;
        $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript:document.Acao.GrupoCodigo.focus();\" class=\"titulo2\">Grupo</a>";
    }

    if ($acaoDescricao == "") {
        if ($Mens == 1) {
            $Mensagem .= ", ";
        }
        $Critica   = 1;
        $LerTabela = 0;
        $Mens      = 1;
        $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript:document.Acao.AcaoDescricao.focus();\" class=\"titulo2\">Ação</a>";
    }

    if ($ordem == "") {
        if ($Mens == 1) {
            $Mensagem .= ", ";
        }
        $Critica   = 1;
        $LerTabela = 0;
        $Mens      = 1;
        $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript:document.Acao.Ordem.focus();\" class=\"titulo2\">Ordem</a>";
    } else {
        if (!SoNumeros($ordem)) {
            $Mens = 1;
            $Tipo = 2;
            $Mensagem = "<a href=\"javascript:document.Acao.Ordem.focus();\" class=\"titulo2\">Ordem de exibição inválida</a>";
        }
    }

    if ($prazo == "") {
        if ($Mens == 1) {
            $Mensagem .= ", ";
        }
        $Critica   = 1;
        $LerTabela = 0;
        $Mens      = 1;
        $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript:document.Acao.Prazo.focus();\" class=\"titulo2\">Prazo</a>";
    } else {
        if (!SoNumeros($prazo)) {
            $Mens = 1;
            $Tipo = 2;
            $Mensagem = "<a href=\"javascript:document.Acao.Prazo.focus();\" class=\"titulo2\">Prazo inválido</a>";
        }
    }

    if ($Mens == 0) {
        # Verifica a duplicidade de ações #
        $db = Conexao();
        
        $sql = "SELECT COUNT(CTACAOSEQU) FROM SFPC.TBTRAMITACAOACAO WHERE RTRIM(LTRIM(ETACAODESC)) = '$acaoDescricao' AND CGREMPCODI = $grupo AND CTACAOSEQU <> $acaoCodigo";
        
        $result = $db->query($sql);
        
        if (PEAR::isError($result)) {
            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
        } else {
            $Linha = $result->fetchRow();
            $Qtd = $Linha[0];
            
            if ($Qtd > 0) {
                $Mens = 1;
                $Tipo = 2;
                $Mensagem = "<a href=\"javascript:document.Acao.AcaoDescricao.focus();\" class=\"titulo2\">Ação já cadastrada para este grupo</a>";
            }
        }
    }
    
    if ($Mens == 0) {
        # Verifica a Duplicidade da Ordem #
        $sql = "SELECT COUNT(CTACAOSEQU) FROM SFPC.TBTRAMITACAOACAO WHERE ATACAOORDE = $ordem AND CTACAOSEQU <> $acaoCodigo AND CGREMPCODI = $grupo";
                
        $result = $db->query($sql);
                
        if (PEAR::isError($result)) {
            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
        } else {
            $Linha = $result->fetchRow();
            $Qtd = $Linha[0];
                    
            if ($Qtd > 0) {
                $Mens = 1;
                $Tipo = 2;
                $Mensagem = "<a href=\"javascript:document.Acao.Ordem.focus();\" class=\"titulo2\">Ordem de ação já cadastrada para este grupo</a>";
            }
        }
    }

    # Verifica duplicidade de ação inicial para o grupo #
    if ($Mens == 0 && $acaoInicial == 'S') {
        $sql = "SELECT  COUNT(FTACAOINIC)
                FROM    SFPC.TBTRAMITACAOACAO
                WHERE   CGREMPCODI = $grupo
                        AND FTACAOINIC = 'S' AND CTACAOSEQU <> $acaoCodigo";
    
        $res = $db->query($sql);

        if (PEAR::isError($res)) {
            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
        } else {
            $Linha = $res->fetchRow();
            $qtd = $Linha[0];
        
            if ($qtd > 0) {
                $Mens = 1;
                $Tipo = 2;
                $Mensagem = "<a href=\"javascript:document.Acao.AcaoInicial.focus();\" class=\"titulo2\">Este grupo já possui ação inicial cadastrada</a>";
            }
        }
    }

    # Verifica duplicidade de ação final para o grupo #
    if ($Mens == 0 && $acaoFinal == 'S') {
        $sql = "SELECT  COUNT(FTACAOFINA)
                FROM    SFPC.TBTRAMITACAOACAO
                WHERE   CGREMPCODI = $grupo
                        AND FTACAOFINA = 'S' AND CTACAOSEQU <> $acaoCodigo";
    
        $res = $db->query($sql);

        if (PEAR::isError($res)) {
            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
        } else {
            $Linha = $res->fetchRow();
            $qtd = $Linha[0];
        
            if ($qtd > 0) {
                $Mens = 1;
                $Tipo = 2;
                $Mensagem = "<a href=\"javascript:document.Acao.AcaoFinal.focus();\" class=\"titulo2\">Este grupo já possui ação final cadastrada</a>";
            }
        }
    }
    
    if ($Mens == 0) {
        # Atualiza ações #
        $codUsuario = $_SESSION['_cusupocodi_'];
        $Data = date("Y-m-d H:i:s");
        $db->query("BEGIN TRANSACTION");
                        
        $sql  = "UPDATE SFPC.TBTRAMITACAOACAO ";
        $sql .= "SET    ETACAODESC = '$acaoDescricao', ";
        $sql .= "       CGREMPCODI = $grupo," ;
        $sql .= "       ATACAOORDE = $ordem, ";
        $sql .= "       ATACAOPRAZ = $prazo, ";
        $sql .= "       FTACAOSITU = '$situacao', ";
        $sql .= "       CUSUPOCODI = $codUsuario, ";
        $sql .= "       TTACAOULAT = '$Data', ";
        $sql .= "       FTACAOINIC = '$acaoInicial', ";
        $sql .= "       FTACAOFINA = '$acaoFinal', ";
        $sql .= "       FTACAOCOMI = '$infoComissao', ";
        $sql .= "       FTACAOANEX = '$obrigaAnexo', ";
        $sql .= "       FTACAOTUSU = '$enviarTodos' ";
        $sql .= " WHERE CTACAOSEQU = $acaoCodigo";
                        
        $result = $db->query($sql);
                        
        if (PEAR::isError($result)) {
            $db->query("ROLLBACK");
            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
        } else {
            $db->query("COMMIT");
            $db->query("END TRANSACTION");


            //Deletando CHECKLIST DE ACOES
            $arrChecklist = json_decode($dadosChecklist); 

            $db->query("BEGIN TRANSACTION");

            $sql  = "DELETE FROM sfpc.tbtramitacaoacaochecklist  ";
            $sql .= "WHERE ctacaosequ = ".$acaoCodigo;

            $res = $db->query($sql);
        
            if (PEAR::isError($res)) {
                $db->query("ROLLBACK");
                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
            }else{
                $db->query("COMMIT");
                $db->query("END TRANSACTION");
            }

            //incluir CHECKLIST DE ACOES
            for($i = 0; $i < count($arrChecklist); $i++){

                $objChecklist = $arrChecklist[$i];

                $sql = "SELECT ctacacsequ
                        FROM sfpc.tbtramitacaoacaochecklist order by ctacacsequ DESC limit 1";

                $res = $db->query($sql);


                if (PEAR::isError($res)) {
                    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                }else{

                    $Linha = $res->fetchRow();
                    $seqchecklist = $Linha[0] + 1;
                

                    $db->query("BEGIN TRANSACTION");

                    $sql  = "INSERT INTO sfpc.tbtramitacaoacaochecklist ( ";
                    $sql .= "ctacaosequ, ctacacsequ, etacacdesc, atacacorde, ";
                    $sql .= " ftacacsitu, cusupocodi, ttacaculat ";
                    $sql .= ") VALUES ( ";
                    $sql .= "$acaoCodigo, $seqchecklist, '$objChecklist->descricao', $objChecklist->ordem, 'A', $objChecklist->codUsuario, now() ); ";
                    
                    $res = $db->query($sql);
                
                    if (PEAR::isError($res)) {
                        $db->query("ROLLBACK");
                        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                    }else{
                        $db->query("COMMIT");
                        $db->query("END TRANSACTION");
                    }

                }
            }


            $db->disconnect();
                            
            # Envia mensagem para página selecionar #
            $Mensagem = urlencode("Ação alterada com sucesso!");
            $Url = "TabTramitacaoAcaoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1&Critica=0";
                            
            if (! in_array($Url, $_SESSION['GetUrl'])) {
                $_SESSION['GetUrl'][] = $Url;
            }
            header("location: " . $Url);
            exit();
        }
    }
}

if ($Critica == 0) {
    $db = Conexao();
    
    $sql  = "SELECT CTACAOSEQU, CGREMPCODI, ETACAODESC, ATACAOORDE, ";
    $sql .= "       ATACAOPRAZ, FTACAOSITU, FTACAOINIC, FTACAOFINA, ";
    $sql .= "       FTACAOCOMI, FTACAOANEX, FTACAOTUSU ";
    $sql .= "FROM   SFPC.TBTRAMITACAOACAO ";
    $sql .= "WHERE CTACAOSEQU = $acaoCodigo ";
    
    $result = $db->query($sql);
    
    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
    } else {
        while ($Linha = $result->fetchRow()) {
            $sequencialAcao = $Linha[0];
            $grupo         = $Linha[1];
            $acaoDescricao = $Linha[2];
            $ordem         = $Linha[3];
            $prazo         = $Linha[4];
            $situacao      = $Linha[5];
            $acaoInicial   = $Linha[6];
            $acaoFinal     = $Linha[7];
            $infoComissao  = $Linha[8];
            $obrigaAnexo   = $Linha[9];
            $enviarTodos   = $Linha[10];
        }

        //traz os dados do checklist
        $arrChecklist = "[]";

        $sqlCheck  = "SELECT c.ctacacsequ, c.etacacdesc, c.atacacorde,  ";
        $sqlCheck .= " c.cusupocodi, c.ttacaculat , u.eusuporesp ";
        $sqlCheck .= " FROM sfpc.tbtramitacaoacaochecklist c ";
        $sqlCheck .= " left join sfpc.tbusuarioportal u on c.cusupocodi = u.cusupocodi ";
        $sqlCheck .= " where c.ftacacsitu = 'A' and c.ctacaosequ =".$sequencialAcao; 
        
        $resultCheck = $db->query($sqlCheck);
        
        if (PEAR::isError($resultCheck)) {
            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sqlCheck");
        } else {

            $arrChecklist = "[";
            $contCheck = 0;

            while ($dadoCheck = $resultCheck->fetchRow()) {

                if($contCheck > 0){
                    $arrChecklist .= ',';
                }

                $arrData = explode('-',substr($dadoCheck[4],0,10));
                $data = $arrData[2].'/'.$arrData[1].'/'.$arrData[0];
                $arrChecklist .= '{"sequencialCheck":'.$dadoCheck[0].',"descricao":"'.$dadoCheck[1].'","ordem":'.$dadoCheck[2].',"data":"'.$data.'","codUsuario":'.$dadoCheck[3].',"usuario":"'.$dadoCheck[5].'"}'; 
                $contCheck = $contCheck + 1;
            }
            $arrChecklist .= "]";
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

<?php MenuAcesso(); ?>

function CaracteresObjeto(text,campo){

    campo.value = text.value.length;

}


//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
    <script language="JavaScript" src="../menu.js"></script>
    <script language="JavaScript">Init();</script>
    <form action="TabTramitacaoAcaoAlterar.php" method="post" name="Acao">
    <br> <br> <br> <br> <br>
        <table cellpadding="3" border="0">
            <!-- Caminho -->
            <tr>
                <td width="150">
                    <img border="0" src="../midia/linha.gif" alt="">
                </td>
                <td align="left" class="textonormal">
                    <font class="titulo2">|</font> <a href="../index.php">
                    <font color="#000000">Página Principal</font></a> > Tabelas > Licitações > Tramitação > Ação > Manter
                </td>
            </tr>
            <!-- Fim do Caminho-->
            <!-- Erro -->
	        <?php if ($Mens == 1) { ?>
            <tr>
                <td width="150"></td>
                <td align="left" colspan="2" id="erroTd">
                    <?php ExibeMens($Mensagem,$Tipo,1); ?>
                    
                </td>
            </tr>
	        <?php } ?>
	        <!-- Fim do Erro -->
            <!-- Corpo -->
            <tr>
                <td width="150"></td>
                <td class="textonormal">
                    <table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
                        <tr>
                            <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">MANTER - AÇÃO</td>
                        </tr>
                        <tr>
                            <td class="textonormal">
                                <p align="justify">Para atualizar a ação, preencha os dados abaixo e clique no botão "Alterar". Para apaga-lá, clique no botão "Excluir".</p>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table>
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7">Grupo*</td>
                                        <td class="textonormal">
                                            <select name="GrupoCodigo" class="textonormal">                                                
                                                <?php
                                                # Mostra os grupos #
                		                        $db = Conexao();
                                                
                                                $sql = "SELECT CGREMPCODI, EGREMPDESC FROM SFPC.TBGRUPOEMPRESA WHERE CGREMPCODI <> 0 ORDER BY EGREMPDESC ASC";
                                                
                                                $result = $db->query($sql);

                                                if (PEAR::isError($result) ){
										            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										        } else {
												    while ($Linha = $result->fetchRow()) {
                                                ?>
                                                <option <?php echo $Linha[0] == $grupo ? 'selected' : ''; ?> value="<?php echo $Linha[0]; ?>"><?php echo $Linha[1]; ?></option>';
                                                <?php
                                                    }
			                                    }
                                                $db->disconnect();
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7">Ação*</td>
                                        <td class="textonormal">

                                            <textarea id="AcaoDescricao" name="AcaoDescricao"
                                            cols="50"
                                            rows="4"
                                            maxlength="400"
                                            onkeyup="javascript:CaracteresObjeto(this,Acao.NCaracteresObjeto)" onblur="javascript:CaracteresObjeto(this,Acao.NCaracteresObjeto)"
                                            onselect="javascript:CaracteresObjeto(this,Acao.NCaracteresObjeto)"
                                            class="textonormal"><?php echo (!empty($acaoDescricao)) ? $acaoDescricao : ''; ?></textarea>
                                            <br><font class="textonormal">máximo de 400 caracteres</font>
                                            <input type="text" name="NCaracteresObjeto" id="NCaracteresObjeto" disabled  readonly="" size="3" value="0" class="textonormal"><br>
                                            <!--<input type="text" name="AcaoDescricao" size="40" maxlength="60" value="<?php echo $acaoDescricao; ?>" class="textonormal">-->
                                            <input type="hidden" name="Critica" value="1">
                                            <input type="hidden" name="AcaoCodigo" value="<?php echo $acaoCodigo; ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7">Ordem de exibição*</td>
                                        <td class="textonormal">
                                            <input type="text" name="Ordem" size="3" value="<?php echo $ordem; ?>" maxlength="3" class="textonormal">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7">Prazo estimado em dias úteis*</td>
                                        <td class="textonormal">
                                            <input type="text" name="Prazo" size="3" value="<?php echo $prazo; ?>" maxlength="3" class="textonormal">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7">Situação</td>
                                        <td class="textonormal">
	                                        <?php  if ($situacao == "A") {
                                                    $descSituacao = "ATIVO";
                                                } else {
                                                    $descSituacao = "INATIVO";
                                                }
	                                        ?>
	                                        <select name="Situacao" value="<?php echo $descSituacao; ?>" class="textonormal">
	        	                                <option value="A" <?php if ( $situacao == "A" ) { echo "selected"; }?>>ATIVO</option>
                                                <option value="I" <?php if ( $situacao == "I" ) { echo "selected"; }?>>INATIVO</option>
                                            </select>                
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7">Ação Inicial</td>
                                        <td class="textonormal">
	                                        <?php  if ($acaoInicial == "S") {
                                                    $descAcaoInicial = "SIM";
                                                } else {
                                                    $descAcaoInicial = "NÃO";
                                                }
	                                        ?>
	                                        <select name="AcaoInicial" value="<?php echo $acaoInicial; ?>" class="textonormal">
	        	                                <option value="S" <?php if ($acaoInicial == "S") { echo "selected"; }?>>SIM</option>
                                                <option value="N" <?php if ($acaoInicial == "N") { echo "selected"; }?>>NÃO</option>
                                            </select>                
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7">Ação Final</td>
                                        <td class="textonormal">
	                                        <?php  if ($acaoFinal == "S") {
                                                    $descAcaoFinal = "SIM";
                                                } else {
                                                    $descAcaoFinal = "NÃO";
                                                }
	                                        ?>
	                                        <select name="AcaoFinal" value="<?php echo $acaoFinal; ?>" class="textonormal">
	        	                                <option value="S" <?php if ($acaoFinal == "S") { echo "selected"; }?>>SIM</option>
                                                <option value="N" <?php if ($acaoFinal == "N") { echo "selected"; }?>>NÃO</option>
                                            </select>                
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7">Informar Comissão</td>
                                        <td class="textonormal">
	                                        <?php  if ($infoComissao == "S") {
                                                    $descIC = "SIM";
                                                } else {
                                                    $descIC = "NÃO";
                                                }
	                                        ?>
	                                        <select name="InformarComissao" value="<?php echo $infoComissao; ?>" class="textonormal">
	        	                                <option value="S" <?php if ($infoComissao == "S") { echo "selected"; }?>>SIM</option>
                                                <option value="N" <?php if ($infoComissao == "N") { echo "selected"; }?>>NÃO</option>
                                            </select>                
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7">Anexo Obrigatório</td>
                                        <td class="textonormal">
	                                        <?php  if ($obrigaAnexo == "S") {
                                                    $descOA = "SIM";
                                                } else {
                                                    $descOA = "NÃO";
                                                }
	                                        ?>
	                                        <select name="ObrigaAnexo" value="<?php echo $obrigaAnexo; ?>" class="textonormal">
	        	                                <option value="S" <?php if ($obrigaAnexo == "S") { echo "selected"; }?>>SIM</option>
                                                <option value="N" <?php if ($obrigaAnexo == "N") { echo "selected"; }?>>NÃO</option>
                                            </select>                
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7">Enviar para todos os usuários do agente</td>
                                        <td class="textonormal">
	                                        <?php  if ($enviarTodos == "S") {
                                                    $descET = "SIM";
                                                } else {
                                                    $descET = "NÃO";
                                                }
	                                        ?>
	                                        <select name="EnviarTodos" value="<?php echo $enviarTodos; ?>" class="textonormal">
	        	                                <option value="S" <?php if ($enviarTodos == "S") { echo "selected"; }?>>SIM</option>
                                                <option value="N" <?php if ($enviarTodos == "N") { echo "selected"; }?>>NÃO</option>
                                            </select>                
                                        </td>
                                    </tr>
                                    <tr>  
                                        <td class="textonormal" bgcolor="#DCEDF7">Item Checklist</td>
                                        <td class="textonormal">
                                            <input type="text" name="txtChecklist" id="txtChecklist"  value="<?php echo $txtChecklist; ?>" size="45" maxlength="100" class="textonormal"> 
                                            <input type="button" name="Incluir" value="Incluir Item" class="botao" onClick="addItemChecklist()">
                                            
                                        </td>
                                    </tr>
                                    <tr>
                                        <table border="1" cellpadding="3" cellspacing="0" bgcolor="#bfdaf2" bordercolor="#75ADE6" summary="" class="textonormal" id="tbChecklist" name="tbChecklist" width="100%">
                                        </table>   
                                        <input type="hidden" name="dados" id="dados" value="">         
                                    </tr>
                                    <tr>
                                        <td class="textonormal" align="right">
                                            <input type="button" value="Alterar" class="botao" onclick="javascript:enviar('Alterar');">
                                            <input type="button" value="Excluir" class="botao" onclick="javascript:enviar('Excluir');">
                                            <input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar')">
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
</body>
</html>
<script language="javascript" type="">
<!--
<?php 

    if($dadosChecklist){
        echo 'CHECKLIST = '.$dadosChecklist;
    }else{

        if($arrChecklist){
            echo 'CHECKLIST = '.$arrChecklist;
        }else{
            echo 'CHECKLIST = []';
        }

        
    }

?>
 
    usuarioCod = <?php echo $_SESSION['_cusupocodi_']?>;                                                       
    usuarioDesc = '<?php echo $usuarioDesc ?>';
    dataAtual = '<?php echo date('d/m/Y') ?>';       
    $('#tbChecklist').hide();

    function showErro(str){

        
        htmlErro = '<div id="divErro" ><table border="0" width="100%">';
        htmlErro += '<tbody>';
        htmlErro += '<tr>';
        htmlErro += '<td bgcolor="DCEDF7" class="titulo1">';
        htmlErro += '<blink><font class="titulo1">Erro!</font></blink>';
        htmlErro += '</td>';
        htmlErro += '</tr>';
        htmlErro += '<tr>';
        htmlErro += '<td class="titulo2">Informe: <b>'+str+'</b>.</td>';
        htmlErro += '</tr>';
        htmlErro += '</tbody>';
        htmlErro += '</table></div>';

        $('#erroTd').html('');
        $('#erroTd').html(htmlErro);
        $('#divErro').show();
    }

    function hideErro(){
        $('#divErro').html('');
        $('#divErro').hide();
    }

    function addItemChecklist(){

        strDesc = $('#txtChecklist').val().toUpperCase();

        if(strDesc != "" && strDesc.length > 0){
            objChecklist = {};
            objChecklist.descricao = strDesc;
            objChecklist.ordem = (CHECKLIST.length + 1);
            objChecklist.data = dataAtual; 
            objChecklist.codUsuario = usuarioCod;   
            objChecklist.usuario = usuarioDesc;
            
            CHECKLIST.push(objChecklist);
                
            $('#txtChecklist').val('');

            listarItemChecklist();
        }else{
            showErro("Preencha o campo item de checklist antes de inserir");
        }
    }


    function removeItemChecklist(){

        
        for (var i = CHECKLIST.length; i >= 0 ; i--) { 

            //console.log( $('#chkItem'+i).checked );

            if($('#chkItem'+i).prop('checked')){

                CHECKLIST.splice(i, 1);
            }

        }

        listarItemChecklist();

    }

    function gerarSelectOrdem(ordem, id){

        htmlSelectOrdem = '<select id="selOrd'+id+'" style="width:80px;text-align-last:center;">';
        for (var i = 0; i < CHECKLIST.length; i++) { 
            cont = i+1;
            if(ordem == cont ){
                htmlSelectOrdem += '<option selected>'+cont+'</option>';
            }else{
                htmlSelectOrdem += '<option>'+cont+'</option>';
            }
            
        }
        htmlSelectOrdem += '</select>';
        return htmlSelectOrdem;

    }


    function listarItemChecklist(){

        if(CHECKLIST.length > 0){

            htmlChecklist = '';
            htmlChecklist += '<tr>';
            htmlChecklist += '<td bgcolor="#75ADE6" class="textoabasoff" colspan="5" align="center">CHECKLIST - ITEM</td>';
            htmlChecklist += '</tr>';
            htmlChecklist += '<tr>';
            htmlChecklist += '<td bgcolor="#bfdaf2" align="center"><b>  </b></td>';
            htmlChecklist += '<td bgcolor="#bfdaf2" align="center"><b> DESCRIÇÃO</b></td>';
            htmlChecklist += '<td bgcolor="#bfdaf2" align="center"><b> ORDEM</b></td>';
            htmlChecklist += '<td bgcolor="#bfdaf2" align="center"><b> DATA INCLUSÃO</b></td>';
            htmlChecklist += '<td bgcolor="#bfdaf2" align="center"><b> USUÁRIO RESPONSÁVEL</b></td>';
            htmlChecklist += '</tr>';



            for (var i = 0; i < CHECKLIST.length; i++) { 
                cont = i + 1;
                objChecklist = CHECKLIST[i];

                htmlChecklist +=  '<tr>';
                htmlChecklist += '<td align="center" width="5%" bgcolor="#ffffff">';
                htmlChecklist += '<input type="checkbox" name="chkItem'+i+'" id="chkItem'+i+'" value="" >  ';
                htmlChecklist += '</td>';


                htmlChecklist += '<td class="textonormal" bgcolor="#ffffff">'+objChecklist.descricao+'</td>';
                htmlChecklist += '<td class="textonormal" align="center" bgcolor="#ffffff">'+gerarSelectOrdem(objChecklist.ordem, i)+'</td>';
                htmlChecklist += '<td class="textonormal" align="center" bgcolor="#ffffff">'+objChecklist.data+'</td>';
                htmlChecklist += '<td class="textonormal" align="center" bgcolor="#ffffff">'+objChecklist.usuario+'</td>';
                htmlChecklist += '</tr>';

            }

            htmlChecklist += '<tr>';
            htmlChecklist += '<td bgcolor="#bfdaf2" class="textoabasoff" colspan="5" align="right">';
            htmlChecklist += '<input type="button" name="Retirar" value="Retirar Item" class="botao" onclick="javascript:removeItemChecklist()">';
            htmlChecklist += '</td>';
            htmlChecklist += '</tr>';
            
            $('#tbChecklist').html(htmlChecklist);
            hideErro();
            $('#tbChecklist').show();
        }else{
            $('#tbChecklist').hide();
            hideErro();
        }

    }

    function enviar(valor){
        

        for (var i = 0; i < CHECKLIST.length; i++) { 
            cont = i + 1;

            CHECKLIST[i].ordem = $('#selOrd'+i).val();
        }

        arrCheck = JSON.stringify(CHECKLIST);
        $('#dados').val(arrCheck);

        

        document.Acao.Botao.value = valor;
        document.Acao.submit();
    }

    listarItemChecklist();


    document.Acao.AcaoDescricao.focus();


CaracteresObjeto(document.getElementById('AcaoDescricao'),Acao.NCaracteresObjeto);
    //-->
</script>