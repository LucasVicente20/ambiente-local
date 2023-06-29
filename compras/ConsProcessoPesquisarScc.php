<?php
/**
 * Portal de Compras
 * 
 * Programa: ConsProcessoPesquisarScc.php
 * Autor:    Pitang Agile TI - Caio Coutinho
 * ----------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     26/11/2018
 * Objetivo: Tarefa Redmine 206846
 * ----------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     07/02/2019
 * Objetivo: Tarefa Redmine 210593
 * ----------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     11/03/2019
 * Objetivo: Tarefa Redmine 212523
 * ----------------------------------------------------------------------------------------
 */

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Botao                          = $_POST['Botao'];
    $numeroAta                      = $_POST['numeroAta'];
    $Objeto                         = $_POST['Objeto'];
    $OrgaoLicitanteCodigo           = $_POST['OrgaoLicitanteCodigo'];
    $ComissaoCodigo                 = $_POST['ComissaoCodigo'];
    $ModalidadeCodigo               = $_POST['ModalidadeCodigo'];
    $LicitacaoAno                   = $_POST['LicitacaoAno'];
    $Programa                       = $_POST['Programa'];
    $CampoProcessoSARP              = $_POST['CampoProcessoSARP'];
    $CampoAnoSARP                   = $_POST['CampoAnoSARP'];
    $TipoSarp                       = $_POST['TipoSarp'];
    $CampoComissaoCodigoSARP        = $_POST['CampoComissaoCodigoSARP'];
    $CampoOrgaoLicitanteCodigoSARP  = $_POST['CampoOrgaoLicitanteCodigoSARP'];
    $CampoGrupoEmpresaCodigoSARP    = $_POST['CampoGrupoEmpresaCodigoSARP'];
    $CampoCarregaProcessoSARP       = $_POST['CampoCarregaProcessoSARP'];
    $tipo_ata                       = $_POST['TipoAta'];
} else {
    $Programa                       = $_GET['Programa'];
    $CampoProcessoSARP              = $_GET['CampoProcessoSARP'];
    $CampoAnoSARP                   = $_GET['CampoAnoSARP'];
    $CampoComissaoCodigoSARP        = $_GET['CampoComissaoCodigoSARP'];
    $CampoOrgaoLicitanteCodigoSARP  = $_GET['CampoOrgaoLicitanteCodigoSARP'];
    $CampoGrupoEmpresaCodigoSARP    = $_GET['CampoGrupoEmpresaCodigoSARP'];
    $CampoCarregaProcessoSARP       = $_GET['CampoCarregaProcessoSARP'];
    $TipoSarp                       = $_GET['TipoSarp'];
    $tipo_ata                       = $_GET['TipoAta'];    
    $_SESSION['TipoAta']            = $_GET['TipoAta'];    
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "ConsProcessoPesquisarScc.php";

function getOrgaoCentroCusto($db, $centroCusto) {
    $sql = "SELECT CCPORT.CCENPOSEQU, CCPORT.CORGLICODI ";
    $sql .= "   FROM SFPC.TBCENTROCUSTOPORTAL CCPORT ";
    $sql .= "   WHERE CCPORT.CCENPOSEQU = " . $centroCusto;

    $linha = resultLinhaUnica(executarSQL($db, $sql));
    $resultado = $linha[1];
    
    return $resultado; 
}
?>
<html>
<head>
    <title>Portal de Compras - Detalhes do <?php echo $Descricao; ?></title>
    <script language="javascript" src="../import/jquery/jquery-1.7.2.min.js" type="text/javascript"></script>
    <script language="javascript" src="../import/jquery/jquery.maskedinput.js" type="text/javascript"></script>
    <script language="javascript" type="">
        $(document).ready(function() {
            $('#numeroAta').mask('9999.9999/9999');
        });

        function enviar(valor) {
            document.ConsProcessoPesquisar.Botao.value = valor;
            document.ConsProcessoPesquisar.submit();
        }
    </script>
    <link rel="stylesheet" type="text/css" href="../estilo.css">
</head>
<body background="../midia/bg.jpg" marginwidth="0" marginheight="0">
    <table cellpadding="0" border="0" summary="">
        <!-- Corpo -->
        <tr>
            <td class="textonormal">
                <table  border="0" cellspacing="0" cellpadding="0" bgcolor="#ffffff" summary="">
                    <tr>
                        <td class="textonormal">
                            <form action="ConsProcessoPesquisarScc.php" method="post" name="ConsProcessoPesquisar">
                                <table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal">
                                    <?php
                                    if ($tipo_ata == 'I') {
                                        ?>
                                        <tr>
                                            <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
                                                PROCESSOS LICITATÓRIOS HOMOLOGADOS COM ATA <?php echo ($tipo_ata == 'I') ? 'INTERNA' : 'EXTERNA'; ?> VIGENTE E ATIVA <?php echo ($TipoSarp == 'P') ? 'DO ÓRGÃO SELECIONADO COMO PARTICIPANTE' : '';?>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                    <tr>
                                        <td class="textonormal">
                                            <p align="justify">
                                                Para consultar os processos licitatórios homologados, selecione o item de pesquisa e  clique no botão "Pesquisar". Para limpar a pesquisa, clique no botão "Limpar".
                                            </p>
                                        </td>
                                    <tr>
                                        <td>
                                            <table class="textonormal" border="0" summary="">
                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Número da Ata</td>
                                                    <td class="textonormal">
                                                        <input type="text" name="numeroAta" id="numeroAta" size="45" maxlength="60" value="<?php echo $numeroAta;?>" class="textonormal">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Objeto</td>
                                                    <td class="textonormal">
                                                        <input type="text" name="Objeto" id="Objeto" size="45" maxlength="60" value="<?php echo $Objeto;?>" class="textonormal">
                                                    </td>
                                                </tr>
                                                <tr <?php echo ($tipo_ata == 'E') ? 'style="display:none"' : ''; ?>>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Órgão Licitante</td>
                                                    <td class="textonormal">
                                                        <select name="OrgaoLicitanteCodigo" class="textonormal">
                                                            <option value="">Todos os Órgãos Licitantes...</option>
                                                            <?php
                                                            $db = Conexao();

                                                            $sql = "SELECT CORGLICODI, EORGLIDESC FROM SFPC.TBORGAOLICITANTE ORDER BY EORGLIDESC";

                                                            $result = $db->query($sql);

                                                            if (PEAR::isError($result)) {
                                                                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                                            } else {
                                                                while ($Linha = $result->fetchRow()) {
                                                                    if ($Linha[0] == $OrgaoLicitanteCodigo) {
                                                                        echo "<option value=\"$Linha[0]\" selected>$Linha[1]</option>\n";
                                                                    } else {
                                                                        echo "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
                                                                    }
                                                                }
                                                            }

                                                            $db->disconnect();
                                                            ?>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr <?php echo ($tipo_ata == 'E') ? 'style="display:none"' : ''; ?>>
                                                    <td class="textonormal"  bgcolor="#DCEDF7">Comissão </td>
                                                    <td class="textonormal">
                                                        <select name="ComissaoCodigo" class="textonormal">
                                                            <option value="">Todas as Comissões...</option>
                                                            <?php
                                                            $db = Conexao();

                                                            $sql  = "SELECT CCOMLICODI, ECOMLIDESC, CGREMPCODI ";
                                                            $sql .= "FROM SFPC.TBCOMISSAOLICITACAO ORDER BY CGREMPCODI,ECOMLIDESC";

                                                            $result = $db->query($sql);

                                                            if (PEAR::isError($result)) {
                                                                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                                            } else {
                                                                while ($Linha = $result->fetchRow()) {
                                                                    if ($Linha[0] == $ComissaoCodigo) {
                                                                        echo "<option value=\"$Linha[0]\" selected>$Linha[1]</option>\n";
                                                                    } else {
                                                                        echo "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
                                                                    }
                                                                }
                                                            }

                                                            $db->disconnect();
                                                            ?>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr <?php echo ($tipo_ata == 'E') ? 'style="display:none"' : ''; ?>>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Modalidade</td>
                                                    <td class="textonormal">
                                                        <select name="ModalidadeCodigo" class="textonormal">
                                                            <option value="">Todas as Modalidades...</option>
                                                            <?php
                                                            $db = Conexao();

                                                            $sql = "SELECT CMODLICODI, EMODLIDESC FROM SFPC.TBMODALIDADELICITACAO ORDER BY AMODLIORDE";

                                                            $result = $db->query($sql);

                                                            if (PEAR::isError($result)) {
                                                                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                                            } else {
                                                                while ($Linha = $result->fetchRow()) {
                                                                    if ($Linha[0] == $ModalidadeCodigo) {
                                                                        echo "<option value=\"$Linha[0]\" selected>$Linha[1]</option>\n";
                                                                    } else {
                                                                        echo "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
                                                                    }
                                                                }
                                                            }

                                                            $db->disconnect();
                                                            ?>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Ano</td>
                                                    <td class="textonormal">
                                                        <select name="LicitacaoAno" class="textonormal">
                                                            <option value="">Selecione o Ano</option>
                                                            <?php
                                                            $db = Conexao();

                                                            $sql  = "SELECT DISTINCT TO_CHAR(TLICPODHAB,'YYYY') ";
                                                            $sql .= "  FROM SFPC.TBLICITACAOPORTAL ";
                                                            $sql .= " WHERE TO_CHAR(TLICPODHAB,'YYYY') <= '".date('Y')."' ";
                                                            $sql .= " ORDER BY TO_CHAR(TLICPODHAB,'YYYY') DESC";

                                                            $result = $db->query($sql);

                                                            if (PEAR::isError($result)) {
                                                                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                                            }

                                                            while ($Linha = $result->fetchRow()) {
                                                                if ($Linha[0] == $LicitacaoAno) {
                                                                    echo "<option value=\"$Linha[0]\" selected>$Linha[0]</option>\n";
                                                                } else {
                                                                    echo "<option value=\"$Linha[0]\">$Linha[0]</option>\n";
                                                                }
                                                            }

                                                            $db->disconnect();
                                                            ?>
                                                        </select>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="textonormal" align="right">
                                            <input type="button" name="Pesquisar" value="Pesquisar" class="botao" onclick="javascript:enviar('Pesquisar');">
                                            <input type="button" name="Limpar" value="Limpar" class="botao" onclick="javascript:enviar('Limpar');">
                                            <input type="hidden" name="Botao" value="">
                                            <input type="hidden" name="TipoAta" value="<?php echo $tipo_ata; ?>">
                                            <input type="hidden" name="TipoSarp" value="<?php echo $TipoSarp; ?>">
                                            <input type="hidden" name="Programaparp" value="<?php echo $Programa; ?>">
                                            <input type="hidden" name="CampoProcessoSARP" value="<?php echo $CampoProcessoSARP; ?>">
                                            <input type="hidden" name="CampoAnoSARP" value="<?php echo $CampoAnoSARP; ?>">
                                            <input type="hidden" name="CampoComissaoCodigoSARP" value="<?php echo $CampoComissaoCodigoSARP; ?>">
                                            <input type="hidden" name="CampoOrgaoLicitanteCodigoSARP" value="<?php echo $CampoOrgaoLicitanteCodigoSARP; ?>">
                                            <input type="hidden" name="CampoGrupoEmpresaCodigoSARP" value="<?php echo $CampoGrupoEmpresaCodigoSARP; ?>">
                                            <input type="hidden" name="CampoCarregaProcessoSARP" value="<?php echo $CampoCarregaProcessoSARP; ?>">
                                        </td>
                                    </tr>
                                    <?php
                                    //-- Início do conteúdo do arquivo ConsAvisosResultado --------------------------
                                    if ($Botao == "Pesquisar") {
                                        $condiçãoAtaInterna = "SFPC.tbataregistroprecointerna ata";
                                        $condiçãoAtaExterna = "SFPC.tbataregistroprecoexterna ata";

                                        $db = Conexao();

                                        if ($tipo_ata == 'I') {
                                            if ($TipoSarp == 'C') {
                                                $sql  = "SELECT DISTINCT ON (ATA.ALICPOANOP, ATA.CLICPOPROC, ATA.CGREMPCODI, ATA.CCOMLICODI, ATA.CORGLICODI) ";
                                                $sql .= "       C.EGREMPDESC, E.EMODLIDESC, D.ECOMLIDESC, A.CLICPOPROC, A.ALICPOANOP, ";
                                                $sql .= "       A.CLICPOCODL, A.ALICPOANOL, A.XLICPOOBJE, A.TLICPODHAB, B.EORGLIDESC, ";
                                                $sql .= "       A.CGREMPCODI, A.CCOMLICODI, A.CORGLICODI, D.ECOMLILOCA, D.ACOMLIFONE, D.ACOMLINFAX, ";
                                                $sql .= "       ARPN.TARPNOINCL + (ATA.AARPINPZVG || ' MONTH')::INTERVAL AS VIGENCIA ";
                                                $sql .= "FROM   SFPC.TBLICITACAOPORTAL A ";
                                                $sql .= "       LEFT JOIN SFPC.TBORGAOLICITANTE B ON A.CORGLICODI = B.CORGLICODI ";
                                                $sql .= "       LEFT JOIN SFPC.TBGRUPOEMPRESA C ON A.CGREMPCODI = C.CGREMPCODI ";
                                                $sql .= "       LEFT JOIN SFPC.TBCOMISSAOLICITACAO D ON A.CCOMLICODI = D.CCOMLICODI ";
                                                $sql .= "       LEFT JOIN SFPC.TBMODALIDADELICITACAO E ON A.CMODLICODI = E.CMODLICODI ";
                                                $sql .= "       INNER JOIN SFPC.TBFASELICITACAO F ON F.CLICPOPROC = A.CLICPOPROC ";
                                                $sql .= "                                           AND F.ALICPOANOP = A.ALICPOANOP ";
                                                $sql .= "                                           AND F.CGREMPCODI = A.CGREMPCODI ";
                                                $sql .= "                                           AND F.CCOMLICODI = A.CCOMLICODI ";
                                                $sql .= "                                           AND F.CORGLICODI = A.CORGLICODI ";
                                                $sql .= "       LEFT JOIN SFPC.TBATAREGISTROPRECOINTERNA ATA ON A.CLICPOPROC = ata.CLICPOPROC";            
                                                $sql .= "                                               AND A.ALICPOANOP = ata.ALICPOANOP ";
                                                $sql .= "                                               AND A.CGREMPCODI = ata.CGREMPCODI ";
                                                $sql .= "                                               AND A.CCOMLICODI = ata.CCOMLICODI ";
                                                $sql .= "                                               AND A.CORGLICODI = ata.CORGLICODI ";
                                                $sql .= "       LEFT JOIN SFPC.TBATAREGISTROPRECONOVA ARPN ON ATA.CARPNOSEQU = ARPN.CARPNOSEQU ";

                                                    if (!empty($numeroAta)) {
                                                        $sql .= ' JOIN SFPC.TBCENTROCUSTOPORTAL CENTROCUSTO  ON CENTROCUSTO.CORGLICODI = ATA.CORGLICODI ';
                                                    }

                                                $sql .= "WHERE  A.FLICPOSTAT = 'A' ";
                                                $sql .= "       AND ATA.FARPINSITU <> 'I' ";
                                                $sql .= "       AND ((ARPN.TARPNOINCL + (ATA.AARPINPZVG || ' MONTH')::INTERVAL) >= current_timestamp) ";
                                                $sql .= "       AND A.FLICPOREGP = 'S' AND F.CFASESCODI = 13 ";

                                                    if ($Objeto != "") {
                                                        $sql .= " AND ( A.XLICPOOBJE LIKE '%".strtoupper2($Objeto)."%')";
                                                    }

                                                    if ($ComissaoCodigo != "") {
                                                        $sql .= " AND A.CCOMLICODI = $ComissaoCodigo ";
                                                    }

                                                    if ($ModalidadeCodigo != "") {
                                                        $sql .= " AND A.CMODLICODI = $ModalidadeCodigo ";
                                                    }

                                                    if ($OrgaoLicitanteCodigo != "") {
                                                        $sql .= " AND A.CORGLICODI = $OrgaoLicitanteCodigo ";
                                                    }

                                                    if ($LicitacaoAno != "" && empty($numeroAta)) {
                                                        $sql .= " AND A.ALICPOANOP = $LicitacaoAno ";
                                                    }

                                                    if (!empty($numeroAta)) {
                                                        $ccenpocorg = ltrim(substr($numeroAta, 0,2), "0");
                                                        $ccenpounid = ltrim(substr($numeroAta, 2,2), "0");
                                                        $carpincodn = ltrim(substr($numeroAta, 5,4), "0");
                                                        $aarpinanon = substr($numeroAta, 10,4);

                                                        $sql .= ' AND ATA.CORGLICODI = ( ';
                                                        $sql .= '     SELECT CENTROCUSTO.CORGLICODI FROM SFPC.TBCENTROCUSTOPORTAL CENTROCUSTO WHERE ';
                                                        $sql .= '     CENTROCUSTO.CCENPOCORG =  ' . $ccenpocorg;
                                                        $sql .= '     AND CENTROCUSTO.CCENPOUNID =  ' . $ccenpounid;;
                                                        $sql .= '     LIMIT 1           ';  
                                                        $sql .= ' ) ';
                                                        $sql .= ' AND ATA.CARPINCODN =' . $carpincodn;
                                                        $sql .= ' AND ATA.AARPINANON =' . $aarpinanon;
                                                    }

                                                $sql .= " AND A.CLICPOPROC =  ATA.CLICPOPROC";
                                                $sql .= " AND ATA.FARPINSITU = 'A'";
                                                $sql .= " ORDER BY ATA.ALICPOANOP, ATA.CLICPOPROC, ATA.CGREMPCODI, ATA.CCOMLICODI, ATA.CORGLICODI, C.EGREMPDESC, E.EMODLIDESC, D.ECOMLIDESC,  A.ALICPOANOP, A.CLICPOPROC";
                                            } else {
                                                $sql = "  SELECT DISTINCT ON (ARPI.ALICPOANOP, ARPI.CLICPOPROC, ARPI.CGREMPCODI, ARPI.CCOMLICODI, ARPI.CORGLICODI) ";
                                                $sql .= "           GE.EGREMPDESC, ML.EMODLIDESC, CL.ECOMLIDESC, LP.CLICPOPROC, LP.ALICPOANOP,  ";            
                                                $sql .= "           LP.CLICPOCODL, LP.ALICPOANOL, LP.XLICPOOBJE, LP.TLICPODHAB, OL.EORGLIDESC, ";
                                                $sql .= "           LP.CGREMPCODI, LP.CCOMLICODI, LP.CORGLICODI, CL.ECOMLILOCA, CL.ACOMLIFONE, CL.ACOMLINFAX,  ";
                                                $sql .= "           arpn.tarpnoincl + (ARPI.aarpinpzvg || ' month')::INTERVAL AS VIGENCIA  ";
                                                $sql .= "  FROM  ";
                                                $sql .= "       SFPC.TBATAREGISTROPRECOINTERNA ARPI ";
                                                $sql .= "       INNER JOIN SFPC.TBPARTICIPANTEATARP PARP ON ARPI.CARPNOSEQU = PARP.CARPNOSEQU    ";
                                                $sql .= "       INNER JOIN SFPC.TBLICITACAOPORTAL LP ON  ARPI.CLICPOPROC = LP.CLICPOPROC  ";
                                                $sql .= "           AND ARPI.ALICPOANOP = LP.ALICPOANOP  ";
                                                $sql .= "           AND ARPI.CCOMLICODI = LP.CCOMLICODI  ";
                                                $sql .= "           AND ARPI.CGREMPCODI = LP.CGREMPCODI  ";
                                                $sql .= "       LEFT JOIN SFPC.TBCOMISSAOLICITACAO CL on LP.CCOMLICODI = CL.CCOMLICODI";
                                                $sql .= "       LEFT JOIN SFPC.TBGRUPOEMPRESA GE on LP.CGREMPCODI = GE.CGREMPCODI   ";
                                                $sql .= "       LEFT JOIN SFPC.TBORGAOLICITANTE OL on LP.CORGLICODI = OL.CORGLICODI ";
                                                $sql .= "       LEFT JOIN SFPC.tbataregistropreconova arpn ON ARPI.carpnosequ = arpn.carpnosequ ";
                                                $sql .= "       INNER JOIN SFPC.TBMODALIDADELICITACAO ML on LP.cmodlicodi = ML.cmodlicodi   ";

                                                if (!empty($numeroAta)) {
                                                    $sql .= '       JOIN SFPC.tbcentrocustoportal centroCusto  ON centroCusto.corglicodi = arpi.corglicodi ';
                                                }

                                                $sql .= "  WHERE	    ";
                                                $sql .= "  ARPI.FARPINSITU = 'A'    ";

                                                if ($Objeto != "") {
                                                    $sql .= " AND ( LP.XLICPOOBJE LIKE '%".strtoupper2($Objeto)."%')";
                                                }
                                                
                                                if ($ComissaoCodigo != "") {
                                                    $sql .= " AND LP.CCOMLICODI = $ComissaoCodigo ";
                                                }
                                                
                                                if ($ModalidadeCodigo != "") {
                                                    $sql .= " AND LP.CMODLICODI = $ModalidadeCodigo ";
                                                }
                                                
                                                if ($OrgaoLicitanteCodigo != "") {
                                                    $sql .= " AND LP.CORGLICODI = $OrgaoLicitanteCodigo ";
                                                }
                                                
                                                if ($LicitacaoAno != "" && empty($numeroAta)) {
                                                    $sql .= " AND LP.ALICPOANOP = $LicitacaoAno ";
                                                }

                                                if (!empty($numeroAta)) {                
                                                    $ccenpocorg = ltrim(substr($numeroAta, 0,2), "0");
                                                    $ccenpounid = ltrim(substr($numeroAta, 2,2), "0");
                                                    $carpincodn = ltrim(substr($numeroAta, 5,4), "0");
                                                    $aarpinanon = substr($numeroAta, 10,4);

                                                    $sql .= ' and arpi.corglicodi = ( ';
                                                    $sql .= '     select centroCusto.corglicodi from sfpc.tbcentrocustoportal centroCusto where ';
                                                    $sql .= '     centroCusto.ccenpocorg =  ' . $ccenpocorg;
                                                    $sql .= '     and centroCusto.ccenpounid =  ' . $ccenpounid;;
                                                    $sql .= '     limit 1           ';  
                                                    $sql .= ' ) ';
                                                    $sql .= ' and arpi.carpincodn =' . $carpincodn;
                                                    $sql .= ' and arpi.aarpinanon =' . $aarpinanon;
                                                }

                                                $orgaoCentroCustoAtual = getOrgaoCentroCusto($db, $_SESSION['centroCustoAnterior']);

                                                $sql .= "  AND PARP.CORGLICODI = " . $orgaoCentroCustoAtual;
                                            }
                                        } else {
                                            $sql  = "SELECT A.CARPEXCODN, A.EARPEXPROC, A.EARPEXOBJE, B.AFORCRCCGC, B.AFORCRCCPF, B.NFORCRRAZS,  ";
                                            $sql .= "  A.EARPEXORGG, A.CARPNOSEQU, A.AARPEXANON ";
                                            $sql .= "  FROM ";
                                            $sql .= "  SFPC.TBATAREGISTROPRECOEXTERNA A, ";
                                            $sql .= "  SFPC.TBFORNECEDORCREDENCIADO B, ";
                                            $sql .= "  SFPC.TBATAREGISTROPRECONOVA C ";
                                            $sql .= " WHERE A.AFORCRSEQU = B.AFORCRSEQU ";
                                            $sql .= "   AND A.CARPNOSEQU = C.CARPNOSEQU ";
                                            $sql .= "   AND C.CARPNOTIAT = 'E' ";
                                            $sql .= "   AND A.FARPEXSITU = 'A' ";

                                            if ($Objeto != "") {
                                                $sql .= " AND ( A.EARPEXOBJE LIKE '%".$Objeto."%')";
                                            }

                                            if ($LicitacaoAno != "") {
                                                $sql .= " AND A.AARPEXANON = $LicitacaoAno ";
                                            }
                                        }

                                        $result = $db->query($sql);

                                        if (PEAR::isError($result)) {
                                            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                        } else {
                                            $Rows = $result->numRows();
                                        }

                                        $GrupoDescricaoricao = "";

                                        if ($Rows != 0) {
                                            echo "<tr>";
                                            echo "      <td class=\"textonormal\">\n";
                                            echo "<table width='100%' cellpadding=\"0\" border=\"0\">\n";
                                            echo "  <tr>\n";
                                            echo "      <td class=\"textonormal\">\n";
                                            echo "      <table  width='100%' border=\"0\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"#ffffff\" summary=\"\">\n";
                                            echo "        <tr>\n";
                                            echo "          <td class=\"textonormal\">\n";
                                            echo "              <table width='100%' border=\"1\" cellpadding=\"3\" cellspacing=\"0\" bordercolor=\"#75ADE6\" class=\"textonormal\" summary=\"\">\n";
                                            echo "              <tr>\n";
                                            echo "                  <td align=\"center\" bgcolor=\"#75ADE6\" valign=\"middle\" colspan=\"5\" class=\"titulo3\">\n";
                                            echo "                              RESULTADO DA PESQUISA\n";
                                            echo "                  </td>\n";
                                            echo "                  </tr>\n";

                                            if ($tipo_ata == 'E') {
                                                echo "<tr><td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">ATAS EXTERNAS</td>\n";
                                                echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">PROCESSO LIC. EXTERNO</td>\n";
                                                echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">OBJETO</td>\n";
                                                echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">FORNECEDOR</td>\n";
                                                echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">ORGÃO GESTOR EXTERNO</td>\n";
                                            }

                                            while ($Linha = $result->fetchRow()) {
                                                if ($GrupoDescricaoricao != $Linha[0] && $tipo_ata != 'E') {
                                                    $GrupoDescricaoricao = $Linha[0];
                                                    echo "<tr><td align=\"center\" class=\"titulo3\" colspan=\"5\" bgcolor=\"#DCEDF7\">$GrupoDescricaoricao</td></tr>\n";
                                                    $ModalidadeDescricaoricao = "";
                                                }

                                                if ($ModalidadeDescricaoricao != $Linha[1] && $tipo_ata != 'E') {
                                                    $ModalidadeDescricaoricao = $Linha[1];
                                                    echo "<tr><td align=\"center\" class=\"titulo3\" colspan=\"5\">$ModalidadeDescricaoricao</td></tr>\n";
                                                    $ComissaoDescricaoSARPBanco = "";
                                                }

                                                if ($ComissaoDescricaoSARPBanco != $Linha[2]) {
                                                    $ComissaoDescricaoSARPBanco = $Linha[2];
                                                    
                                                    if ($tipo_ata != 'E') {
                                                        echo "<tr><td class=\"titulo2\" colspan=\"5\" color=\"#000000\">$ComissaoDescricaoSARPBanco</tr></td>\n";
                                                        echo "<tr><td class=\"textonormal\" colspan=\"5\" color=\"#000000\">$Linha[13] - TEL: $Linha[14] - FAX: $Linha[15]</tr></td>\n";
                                                        echo "<tr><td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">PROCESSO</td>\n";
                                                        echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">LICITAÇÃO</td>\n";
                                                        echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">OBJETO</td>\n";
                                                        echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">DATA/HORA ABERTURA</td>\n";
                                                        echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">ÓRGÃO LICITANTE</td>\n";
                                                    } 
                                                }

                                                echo "<tr>\n";

                                                if ($tipo_ata != 'E') {
                                                    $NProcesso  = substr($Linha[3] + 10000, 1);
                                                    $NLicitacao = substr($Linha[5] + 10000, 1); //Código da Licitação de acordo com a Comissão de Licitação e Modalidade do Processo Licitatório
                                                    $LicitacaoAnoBanco = $Linha[6]; //Ano da Licitação de acordo com a Comissão de Licitação e Modalidade do Processo Licitatório
                                                    $ObjetoBanco = $Linha[7];
                                                    $OrgaoLicitanteDescricaoSARPBanco = $Linha[9];
                                                    $LicitacaoDtAbertura   = substr($Linha[8], 8, 2)."/".substr($Linha[8], 5, 2)."/".substr($Linha[8], 0, 4);
                                                    $LicitacaoHoraAbertura = substr($Linha[8], 11, 5);
                                                    $GrupoCodigo          = $Linha[10];
                                                    $ProcessoAno          = $Linha[4];
                                                    $ComissaoCodigoBanco       = $Linha[11];
                                                    $OrgaoLicitanteCodigoBanco = $Linha[12];

                                                    echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\"><a href=\"javascript:retorna('$NProcesso$SimboloConcatenacaoDesc$ProcessoAno$SimboloConcatenacaoDesc$ComissaoDescricaoSARPBanco$SimboloConcatenacaoDesc$OrgaoLicitanteDescricaoSARPBanco$SimboloConcatenacaoDesc$ComissaoCodigoBanco$SimboloConcatenacaoDesc$OrgaoLicitanteCodigoBanco$SimboloConcatenacaoDesc$GrupoCodigo');\" class=\"textonormal\"><u>$NProcesso/$ProcessoAno</u></a></td>\n";
                                                    echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$NLicitacao/$LicitacaoAnoBanco</td>\n";
                                                    echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$ObjetoBanco</td>\n";
                                                    echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$LicitacaoDtAbertura<br>$LicitacaoHoraAbertura&nbsp;h</td>\n";
                                                    echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$OrgaoLicitanteDescricaoSARPBanco</td>\n";
                                                } else {
                                                    $ProcessoAno          = $Linha[8];
                                                    $NProcesso            = $Linha[7];

                                                    echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\"><a href=\"javascript:retorna('$NProcesso$SimboloConcatenacaoDesc$ProcessoAno$SimboloConcatenacaoDesc$ComissaoDescricaoSARPBanco$SimboloConcatenacaoDesc$OrgaoLicitanteDescricaoSARPBanco$SimboloConcatenacaoDesc$ComissaoCodigoBanco$SimboloConcatenacaoDesc$OrgaoLicitanteCodigoBanco$SimboloConcatenacaoDesc$tipo_ata');\" class=\"textonormal\"><u>$Linha[0]/$Linha[8]</u></a></td>\n";
                                                    echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$Linha[1]</td>\n";
                                                    echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$Linha[2]</td>\n";

                                                    $Name_ = (!empty($Linha[3])) ? $Linha[3] : $Linha[4];

                                                    echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$Name_ -<br> $Linha[5]</td>\n";
                                                    echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$Linha[6]</td>\n";
                                                }
                                                
                                                echo "</tr>\n";
                                            }
                                        } else {
                                            echo "<tr>\n";
                                            echo "  <td valign=\"top\" colspan=\"4\" class=\"textonormal\" bgcolor=\"FFFFFF\">\n";
                                            echo "  Nenhuma ocorrência foi encontrada.\n";
                                            echo "  </td>\n";
                                            echo "</tr>\n";
                                        }

                                        echo "            </table>\n";
                                        echo "                  </td>\n";
                                        echo "              </tr>\n";
                                        echo "      </table>\n";
                                        echo "      </td>\n";
                                        echo "  </tr>\n";
                                        echo "  <!-- Fim do Corpo -->\n";
                                        echo "</table>\n";
                                        echo "      </td>\n";
                                        echo "</tr>\n";
                                        
                                        $db->disconnect();
                                    }
                                    //-- Fim do conteúdo do arquivo ConsAvisosResultado --------------------------
                                    ?>
                                </td>
                            </tr>
                        </table>
                    </form>
                </td>
            </tr>
        </table>
    </body>
</html>
<script language="JavaScript">
    window.focus();
    
    function retorna(Valor) {
        var str = Valor;
        array = str.split("<?php echo $SimboloConcatenacaoDesc; ?>");

        <?php
        if ($tipo_ata != 'E') {
            ?>
            Processo = array[0];
            Ano = array[1];
            ComissaoDescricao = array[2];
            OrgaoDescricao = array[3];
            ComissaoCodigo = array[4];
            OrgaoCodigo = array[5];
            GrupoEmpresa = array[6];
            TipoSarp = '<?= $_SESSION['tipoSarpAnterior']; ?>';

            //Escrevendo nos campos Processo e Ano do form do programa CadSolicitacaoCompraIncluir.php
            opener.document.getElementById('<?php echo $CampoProcessoSARP; ?>').value=Processo;
            opener.document.getElementById('<?php echo $CampoAnoSARP; ?>').value=Ano;
            opener.document.getElementById('<?php echo $CampoComissaoCodigoSARP; ?>').value=ComissaoCodigo;
            opener.document.getElementById('<?php echo $CampoOrgaoLicitanteCodigoSARP; ?>').value=OrgaoCodigo;
            opener.document.getElementById('<?php echo $CampoGrupoEmpresaCodigoSARP; ?>').value=GrupoEmpresa;
            opener.document.getElementById('<?php echo $CampoCarregaProcessoSARP; ?>').value=1; //Utilizado para carregar o processo na tela principal quando o pop-up for chamado.
            opener.document.forms[0].submit(); // atualizando formulário da janela que abriu este popup

            location.href = 'ConsSelecionarAtaProcessoScc.php?processo='+Processo+'&ano='+Ano+'&orgao='+OrgaoCodigo+'&grupo='+GrupoEmpresa+'&tipoSarp='+TipoSarp+'&comissao='+ComissaoCodigo
            <?php
        } else {
            ?>       
            Ata = array[0];
            Ano = array[1];

            <?php
            if (!empty($CampoProcessoSARP)) {
                ?>
                opener.document.getElementById('<?php echo $CampoProcessoSARP; ?>').value=array[0];
                <?php
            }
            ?>

            <?php
            if (!empty($CampoProcessoSARP)) {
                ?>
                opener.document.getElementById('<?php echo $CampoAnoSARP; ?>').value=Ano;
                <?php
            }
            ?>

            <?php
            if (!empty($CampoCarregaProcessoSARP)) {
                ?>
                opener.document.getElementById('<?php echo $CampoCarregaProcessoSARP; ?>').value=1; //Utilizado para carregar o processo na tela principal quando o pop-up for chamado.
                <?php
            }
            ?>
        
            opener.document.forms[0].submit(); // atualizando formulário da janela que abriu este popup
        
            TipoAta = array[6];
            location.href = 'ConsSelecionarItensAtaProcessoScc.php?ata='+Ata+'&TipoAta='+TipoAta+'&ano='+Ano
            <?php
        }
        ?>
    }

    document.ConsProcessoPesquisar.Objeto.focus();
</script>