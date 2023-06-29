<?php
/**
 * Portal da DGCO.
 *
 * PHP version 5.2.5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt. If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category  Pitang Novo Layout
 *
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 *
 * @version   GIT: v1.13.0-41-gf34a9d8
 *
 * -----------------------------------------------------------------------------
 * HISTORICO DE ALTERACOES NO PROGRAMA
 * -----------------------------------------------------------------------------
 * Alterado: Pitang Agile IT
 * Data:     21/07/2015
 * Objetivo: CR80716 - Avisos de Licitação - problema ao acessar um link de um processo
 * -----------------------------------------------------------------------------
 * Alterado: Pitang Agile TI <contato@pitang.com>
 * Data:     17/09/2015
 * Objetivo: CR 100458 - Mensagem de erro recorrente
 * Versão:   20150916_1550-1-gf471375
 * -----------------------------------------------------------------------------
 *  Alterado: Osmar Celestino
 *  Data:     21/09/2022
 *  Objetivo: Cr 269036
 * -----------------------------------------------------------------------------
 *  Alterado: Lucas André
 *  Data:     19/06/2023
 *  Objetivo: Cr 284666
 * -----------------------------------------------------------------------------
 */

if (! @require_once dirname(__FILE__) . "/TemplateAppPadrao.php") {
    throw new Exception("Error Processing Request - TemplateAppPadrao.php", 1);
}

if (! @require_once dirname(__FILE__) . "/../licitacoes/funcoesLicitacoes.php") {
    throw new Exception("Error Processing Request - funcoesLicitacoes.php", 1);
}

// Senão for acessado via POST não deve funcionar o programa
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: ConsAvisosPesquisar.php');
    exit();
}

$_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

if ($_POST['LicitacaoAno'] != "") {
    $_POST['LicitacaoAno'] = filter_var($_POST['LicitacaoAno'], FILTER_SANITIZE_NUMBER_INT);
}

if ($_POST['LicitacaoProcesso'] != "") {
    $_POST['LicitacaoProcesso'] = filter_var($_POST['LicitacaoProcesso'], FILTER_SANITIZE_NUMBER_INT);
}

if ($_POST['ComissaoCodigo'] != "") {
    $_POST['ComissaoCodigo'] = filter_var($_POST['ComissaoCodigo'], FILTER_SANITIZE_NUMBER_INT);
}

if ($_POST['DocumentoCodigo'] != "") {
    $_POST['DocumentoCodigo'] = filter_var($_POST['DocumentoCodigo'], FILTER_SANITIZE_NUMBER_INT);
}

if ($_POST['GrupoCodigo'] != "") {
    $_POST['GrupoCodigo'] = filter_var($_POST['GrupoCodigo'], FILTER_SANITIZE_NUMBER_INT);
}

if ($_POST['OrgaoLicitanteCodigo'] != "") {
    $_POST['OrgaoLicitanteCodigo'] = filter_var($_POST['OrgaoLicitanteCodigo'], FILTER_SANITIZE_NUMBER_INT);
}

if ($_POST['DocumentoCodigo'] != "") {
    $_POST['DocumentoCodigo'] = filter_var($_POST['DocumentoCodigo'], FILTER_SANITIZE_NUMBER_INT);
}

$tpl = new TemplateAppPadrao("templates/ConsAvisosDownload.html", "ConsAvisosDownload");

$_SESSION['ValidaArquivoDownload'] = "ValidaArquivoDownload";

AddMenuAcesso('/ConsAvisosDocumentos.php');
AddMenuAcesso('/ConsAvisosArquivo.php');

$existente = false;
$existenteFornecedor = false;
// Variáveis com o global off #

$Botao = $_POST['Botao'];
// $Critica = $_REQUEST['Critica'];

$FornCad = $_POST['FornCad'];

$CPF_CNPJ = removeSimbolos($_POST['CPF_CNPJ']);
$CnpjCpf = $_POST['TipoCnpjCpf'];

$Objeto = $_POST['Objeto'];
$OrgaoLicitanteCodigo = $_POST['OrgaoLicitanteCodigo'];
$ComissaoCodigo = $_POST['ComissaoCodigo'];
$ModalidadeCodigo = $_POST['ModalidadeCodigo'];
$GrupoCodigo = $_POST['GrupoCodigo'];
$LicitacaoProcesso = $_POST['LicitacaoProcesso'];
$LicitacaoAno = $_POST['LicitacaoAno'];
$DocumentoCodigo = $_POST['DocumentoCodigo'];
$VerificaCPF = $_POST['VerificaCPF'];
$DocumentoCodigo = $_POST['DocumentoCodigo'];

$RazaoSocial = strtoupper2(trim($_POST['RazaoSocial']));
$Endereco = strtoupper2(trim($_POST['Endereco']));
$Email = trim($_POST['Email']);
if(!empty($Email)){
    $_SESSION['elisolmail'] = $Email;
}
$Telefone = trim($_POST['Telefone']);
$Fax = trim($_POST['Fax']);
$NomeContato = strtoupper2($_POST['NomeContato']);
$Participacao = $_POST['Participacao'];

if ($CnpjCpf != null) {
    $tpl->TIPOCPFCNPJ = $CnpjCpf;
    $tpl->VALORCPFCNPJ = $CPF_CNPJ;
} else {
    $CPF_CNPJ = $_POST['VALORCPFCNPJ'];
    $CnpjCpf = $_POST['TIPOCPFCNPJ'];
}

// Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

$Mens = 0;
$Mensagem = "Campo(s) ";
$FornCad = "N";
$SolicitanteCodigo = 0;

if ($Botao == "Confirmar") {
    if ($CnpjCpf == "CPF") {
        $CPF_CNPJ = FormataCPF_CNPJ($CPF_CNPJ, "CPF");
        // $CnpjCpf = $CPF;
        $Qtd = strlen($CPF_CNPJ);
        
        if (($Qtd != 11) and ($Qtd != 0)) {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }
            
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "CPF deve ter 11 números";
        } elseif ($CPF_CNPJ == "") {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }
            
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "CPF obrigatório";
        } else {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }
            
            $valor = valida_CPF($CPF_CNPJ);
            
            if ($valor == false) {
                $Mens = 1;
                $Tipo = 2;
                $Mensagem .= "CPF não possui um valor válido";
            }
        }
        
        $CnpjCpf = "CPF";
    } elseif ($CnpjCpf == "CNPJ") {
        $CPF_CNPJ = FormataCPF_CNPJ($CPF_CNPJ, "CNPJ");
        // $CnpjCpf = $CNPJ;
        $Qtd = strlen($CPF_CNPJ);
        
        if (($Qtd != 14) and ($Qtd != 0)) {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }
            
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "CNPJ deve ter 14 números";
        } elseif ($CPF_CNPJ == "") {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }
            
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "CNPJ obrigatório";
        } else {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }
            
            $valor = valida_CNPJ($CPF_CNPJ);
            
            if ($valor == false) {
                $Mens = 1;
                $Tipo = 2;
                $Mensagem .= "CNPJ não possui um valor válido";
            }
        }
        
        $CnpjCpf = "CNPJ";
    }
    $valor = true;
    
    if ($valor === true) {
       
        if(!empty($Email)){
        // Verifica a existência do CPF/CNPJ #
                $db = Conexao();
                /* Verifica se é fornecedor, se for a tela é diferente */
                $sql = "SELECT NFORCRRAZS, CCEPPOCODI, EFORCRLOGR, AFORCRNUME, ";
                $sql .= "       EFORCRCOMP, EFORCRBAIR, NFORCRCIDA, CFORCRESTA, ";
                $sql .= "  			AFORCRCDDD, AFORCRTELS, AFORCRNFAX, NFORCRMAIL, ";
                $sql .= "  			NFORCRCONT ";
                $sql .= "  FROM SFPC.TBFORNECEDORCREDENCIADO WHERE ";
                
                if ($CnpjCpf == "CNPJ") {
                    $sql .= " AFORCRCCGC = '$CPF_CNPJ'";
                } elseif ($CnpjCpf == "CPF") {
                    $sql .= " AFORCRCCPF = '$CPF_CNPJ'";
                }
                //
                $result = $db->query($sql);
                
                if (PEAR::isError($result)) {
                    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                }
                
                while ($Linha = $result->fetchRow()) {
                    $existente = true;
                    $existenteFornecedor = true;
                }

                
                // Verifica a existência do CPF/CNPJ #
                $db = Conexao();
                $sql = "SELECT CLISOLCODI, ELISOLNOME, ELISOLMAIL, ELISOLENDE, ALISOLFONE, ";
                $sql .= "  			ALISOLNFAX, NLISOLCONT, ALISOLNUME, ELISOLCOMP, ELISOLBAIR, ";
                $sql .= "  			NLISOLCIDA, CLISOLESTA, FLISOLPART ";
                $sql .= "  FROM SFPC.TBLISTASOLICITAN ";
                // $sql .= " WHERE CLICPOPROC = $LicitacaoProcesso AND ALICPOANOP = $LicitacaoAno ";
                // $sql .= " AND CGREMPCODI = $GrupoCodigo AND CCOMLICODI = $ComissaoCodigo ";
                // $sql .= " AND CORGLICODI = $OrgaoLicitanteCodigo AND ";
                $sql .= " WHERE 1=1";
                if ($CnpjCpf == "CPF") {
                    $sql .= " and CLISOLCCPF = '$CPF_CNPJ' ";
                } elseif ($CnpjCpf == "CNPJ") {
                    $sql .= " and CLISOLCNPJ = '$CPF_CNPJ' ";
                }
                $result = $db->query($sql);
                if (PEAR::isError($result)) {
                    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                } else {
                    $Rows = $result->numRows();
                    if ($Rows > 0) {
                        if (! $existenteFornecedor) {
                            $existente = false;
                            $existenteFornecedor = false;
                        }
                        while ($Linha = $result->fetchRow()) {
                            $SolicitanteCodigo = $Linha[0];
                            $RazaoSocial = $Linha[1];
                            $Email = $Linha[2];
                            $Endereco = $Linha[3];
                            $Telefone = removeSimbolos(trim($Linha[4]));
                            $Fax = removeSimbolos(trim($Linha[5]));
                            $NomeContato = $Linha[6];
                            $Numero = $Linha[7];
                            $Complemento = $Linha[8];
                            $Bairro = $Linha[9];
                            $Cidade = $Linha[10];
                            $Estado = $Linha[11];
                            $Participacao = $Linha[12];
                        }
                    } 
                }
            }
        
        $VerificaCPF = "S";
        if (! empty($Botao) && $Botao == 'Confirmar') {
            $tpl->block("BLOCO_TEXTO_ENVIAR");
            $tpl->block("BLOCO_CONFIRMACAO_PARTICIPACAO");
        } else {
            $tpl->block("BLOCO_TEXTO_CONFIRMACAO");
        }
        
        if ($Participacao == "S" || $Participacao == "") {
            $tpl->CHECKED_SIM = "checked";
        }
        
        if ($Participacao == "N") {
            $tpl->CHECKED_NAO = "checked";
        }
        
        $db->disconnect();
    }
} elseif ($Botao == "Enviar") {
    if(!empty($Email)){
            $db = Conexao();
            $sql = "SELECT NFORCRRAZS, CCEPPOCODI, EFORCRLOGR, AFORCRNUME, ";
            $sql .= "       EFORCRCOMP, EFORCRBAIR, NFORCRCIDA, CFORCRESTA, ";
            $sql .= "  			AFORCRCDDD, AFORCRTELS, AFORCRNFAX, NFORCRMAIL, ";
            $sql .= "  			NFORCRCONT ";
            $sql .= "  FROM SFPC.TBFORNECEDORCREDENCIADO WHERE ";
            
            if ($CnpjCpf == "CNPJ") {
                $sql .= " AFORCRCCGC = '$CPF_CNPJ'";
            } elseif ($CnpjCpf == "CPF") {
                $sql .= " AFORCRCCPF = '$CPF_CNPJ'";
            } else {
                $sql .= " NFORCRMAIL = '".$Email."'";
            }
            $result = $db->query($sql);
            
            if (PEAR::isError($result)) {
                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
            }
            $Rows = $result->numRows();
            $Participacao = "S";
            if($Rows > 0 ){
                while ($Linha = $result->fetchRow()) {
                    $existente = true;
                    $existenteFornecedor = true;
                    $RazaoSocial = $Linha[0];
                    $CEP = $Linha[1];
                    $Endereco = $Linha[2];
                    $Numero = $Linha[3];
                    $Complemento = $Linha[4];
                    $Bairro = $Linha[5];
                    $Cidade = $Linha[6];
                    $Estado = $Linha[7];
                    $DDD = $Linha[8];
                    
                    if ($Linha[9] != "") {
                        $Telefone = substr($DDD . " " . $Linha[9], 0, 25);
                    }
                    
                    if ($Linha[10] != "") {
                        $Fax = substr($DDD . " " . $Linha[10], 0, 25);
                    }
                    
                    $Email = $Linha[11];
                    $NomeContato = $Linha[12];
                }
                $Participacao = "S";
                 
            
            }else{
                $FornCad = "N";
            }
    } else{
      $FornCad = "N";
    }
    $Data = date("Y-m-d G:i:s");

    $Mens = 0;
    if($Mens == 0){
        $db = Conexao();
        $db->query("BEGIN TRANSACTION");
        if ($FornCad == "S") {
            // Atualiza na tabela TLISTASOLICITAN #
            if ($CnpjCpf == "CPF") {
                $CPF = "'$CPF_CNPJ'";
                $CNPJ = "NULL";
            } elseif ($CnpjCpf == "CNPJ") {
                $CNPJ = "'$CPF_CNPJ'";
                $CPF = "NULL";
            }
            if ($Numero == "") {
                $Numero = "NULL";
            }
            if ($Complemento == "") {
                $Complemento = "NULL";
            } else {
                $Complemento = "'" . substr($Complemento, 0, 20) . "'";
            }
            if ($Bairro == "") {
                $Bairro = "NULL";
            } else {
                $Bairro = "'" . substr($Bairro, 0, 60) . "'";
            }
            if ($Cidade == "") {
                $Cidade = "NULL";
            } else {
                $Cidade = "'" . substr($Cidade, 0, 30) . "'";
            }
            if ($Estado == "") {
                $Estado = "NULL";
            } else {
                $Estado = "'" . substr($Estado, 0, 2) . "'";
            }
            
            $RazaoSocial = substr($RazaoSocial, 0, 60);
            $Email = substr($Email, 0, 255);
            $Endereco = substr($Endereco, 0, 60);
            $Telefone = substr($Telefone, 0, 25);
            $Fax = substr($Fax, 0, 25);
            $NomeContato = substr($NomeContato, 0, 60);
            $sql = "UPDATE SFPC.TBLISTASOLICITAN ";
            $sql .= "   SET FLISOLENVI = '$Participacao', TLISOLDREC = '$Data', ";
            $sql .= "       ELISOLNOME = '$RazaoSocial', ELISOLENDE = '$Endereco', ";
            $sql .= "       ELISOLMAIL = '$Email', ALISOLFONE = '$Telefone', ALISOLNFAX = '$Fax', NLISOLCONT = '$NomeContato' ";
            
            if ($CnpjCpf == "CPF") {
                $sql .= ", CLISOLCCPF = '$CPF_CNPJ' ";
            } elseif ($CnpjCpf == "CNPJ") {
                $sql .= ", CLISOLCNPJ = '$CPF_CNPJ' ";
            }
            
            $sql .= " WHERE CLICPOPROC = $LicitacaoProcesso AND ALICPOANOP = $LicitacaoAno";
            $sql .= "   AND CGREMPCODI = $GrupoCodigo AND CCOMLICODI = $ComissaoCodigo ";
            $sql .= "   AND CORGLICODI = $OrgaoLicitanteCodigo AND CLISOLCODI = $SolicitanteCodigo ";
            
            $result = $db->query($sql);
            
            if (PEAR::isError($result)) {
                $db->query("ROLLBACK");
                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
            } else {
                $db->query("COMMIT");
                $db->query("END TRANSACTION");
                $db->disconnect();
                
            }
        } else {        
                    $sql = "SELECT MAX(CLISOLCODI) FROM SFPC.TBLISTASOLICITAN ";
                    $sql .= " WHERE CLICPOPROC = $LicitacaoProcesso AND ALICPOANOP = $LicitacaoAno";
                    $sql .= "   AND CGREMPCODI = $GrupoCodigo AND CCOMLICODI = $ComissaoCodigo ";
                    $sql .= "   AND CORGLICODI = $OrgaoLicitanteCodigo";
                    
                    $result = $db->query($sql);
                    
                    if (PEAR::isError($result)) {
                        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                    } else {
                        while ($Linha = $result->fetchRow()) {
                            $SolicitanteCodigoMax = $Linha[0];
                        }
                        
                        if (empty($SolicitanteCodigoMax)) {
                            $SolicitanteCodigo = 1;
                        } else {
                            $SolicitanteCodigo = $SolicitanteCodigoMax + 1;
                        }
                        
                        // Insere na tabela TLISTASOLICITAN #
                        if ($CnpjCpf == "CPF") {
                            $CPF = "'$CPF_CNPJ'";
                            $CNPJ = "'NULL'";
                        } elseif ($CnpjCpf == "CNPJ") {
                            $CNPJ = "'$CPF_CNPJ'";
                            $CPF = "NULL";
                        }else{
                            $CNPJ = "NULL";
                            $CPF = "NULL";
                        }
                        
                        if ($Numero == "") {
                            $Numero = "NULL";
                        }
                        if ($Complemento == "") {
                            $Complemento = "NULL";
                        } else {
                            $Complemento = "'" . substr($Complemento, 0, 20) . "'";
                        }
                        if ($Bairro == "") {
                            $Bairro = "NULL";
                        } else {
                            $Bairro = "'" . substr($Bairro, 0, 60) . "'";
                        }
                        if ($Cidade == "") {
                            $Cidade = "NULL";
                        } else {
                            $Cidade = "'" . substr($Cidade, 0, 30) . "'";
                        }
                        if ($Estado == "") {
                            $Estado = "NULL";
                        } else {
                            $Estado = "'" . substr($Estado, 0, 2) . "'";
                        }
                        
                        $RazaoSocial = substr($RazaoSocial, 0, 60);
                        $Email = empty($Email) ? null : substr($Email, 0, 255);
                        $Endereco = empty($Endereco) ? null : substr($Endereco, 0, 60);
                        $Telefone = empty($Telefone) ? null : substr($Telefone, 0, 25);
                        $Fax = empty($Fax) ? null : substr($Fax, 0, 25);
                        $NomeContato = empty($NomeContato) ? null : substr($NomeContato, 0, 60);
                        $sql = "INSERT INTO SFPC.TBLISTASOLICITAN ( ";
                        $sql .= "clicpoproc, alicpoanop, cgrempcodi, ccomlicodi, ";
                        $sql .= "corglicodi, clisolcodi, elisolnome, clisolcnpj, ";
                        $sql .= "clisolccpf, elisolmail, elisolende, alisolfone, ";
                        $sql .= "alisolnfax, nlisolcont, flisolenvi, tlisoldrec, ";
                        $sql .= "tlisolulat, alisolnume, elisolcomp, ";
                        $sql .= "elisolbair, nlisolcida, clisolesta ";
                        $sql .= " ) VALUES ( ";
                        $sql .= "$LicitacaoProcesso, $LicitacaoAno, $GrupoCodigo, $ComissaoCodigo, ";
                        $sql .= "$OrgaoLicitanteCodigo, $SolicitanteCodigo, '$RazaoSocial', $CNPJ,";
                        $sql .= "$CPF, '$Email', '$Endereco', '$Telefone', ";
                        $sql .= "'$Fax', '$NomeContato', '$Participacao', '$Data', ";
                        $sql .= "'$Data', $Numero, $Complemento, ";
                        $sql .= "$Bairro, $Cidade, $Estado )";
                        $result = $db->query($sql);
                        
                        if (PEAR::isError($result)) {
                            $db->query("ROLLBACK");
                            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                        } else {
                            $db->query("COMMIT");
                            $db->query("END TRANSACTION");
                            $db->disconnect();
                            
                            // Redirecionar para página de seleção #
                            // echo "<html>\n";
                            // echo "<body>\n";
                            // echo "<form method=\"post\" action=\"ConsAvisosArquivo.php\" name=\"Arquivo\">\n";
                            // echo "<input type=\"hidden\" name=\"Objeto\" value=\"$Objeto\">\n";
                            // echo "<input type=\"hidden\" name=\"OrgaoLicitanteCodigo\" value=\"$OrgaoLicitanteCodigo\">\n";
                            // echo "<input type=\"hidden\" name=\"ComissaoCodigo\" value=\"$ComissaoCodigo\">\n";
                            // echo "<input type=\"hidden\" name=\"ModalidadeCodigo\" value=\"$ModalidadeCodigo\">\n";
                            // echo "<input type=\"hidden\" name=\"GrupoCodigo\" value=\"$GrupoCodigo\">\n";
                            // echo "<input type=\"hidden\" name=\"LicitacaoProcesso\" value=\"$LicitacaoProcesso\">\n";
                            // echo "<input type=\"hidden\" name=\"LicitacaoAno\" value=\"$LicitacaoAno\">\n";
                            // echo "<input type=\"hidden\" name=\"DocumentoCodigo\" value=\"$DocumentoCodigo\">\n";
                            // echo "<input type=\"hidden\" name=\"SolicitanteCodigo\" value=\"$SolicitanteCodigo\">\n";
                            // echo "</form>\n";
                            // echo "</body>\n";
                            // echo "<script language=\"javascript\">";
                            // echo "document.Arquivo.submit();";
                            // echo "</script>";
                            // echo "</html>\n";
                            // exit();
                        }
                    }
                // } else {
                //     $sql = "UPDATE SFPC.TBLISTASOLICITAN ";
                //     $sql .= "   SET FLISOLPART = '$Participacao', TLISOLDREC = '$Data', ";
                    
                //     if ($CnpjCpf == "CPF") {
                //         $sql .= " CLISOLCCPF = '$CPF_CNPJ' ";
                //     } elseif ($CnpjCpf == "CNPJ") {
                //         $sql .= " CLISOLCNPJ = '$CPF_CNPJ' ";
                //     }
                    
                //     $sql .= " WHERE CLICPOPROC = $LicitacaoProcesso AND ALICPOANOP = $LicitacaoAno";
                //     $sql .= "   AND CGREMPCODI = $GrupoCodigo AND CCOMLICODI = $ComissaoCodigo ";
                //     $sql .= "   AND CORGLICODI = $OrgaoLicitanteCodigo AND CLISOLCODI = $SolicitanteCodigo ";
                //     $result = $db->query($sql);
                    
                //     if (PEAR::isError($result)) {
                //         $db->query("ROLLBACK");
                //         ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                //     } else {
                //         $db->query("COMMIT");
                //         $db->query("END TRANSACTION");
                //         $db->disconnect();
                        
                //         // Redirecionar para página de seleção #
                //         echo "<html>\n";
                //         echo "<body>\n";
                //         echo "<form method=\"post\" action=\"ConsAvisosArquivo.php\" name=\"Arquivo\">\n";
                //         echo "<input type=\"hidden\" name=\"Objeto\" value=\"$Objeto\">\n";
                //         echo "<input type=\"hidden\" name=\"OrgaoLicitanteCodigo\" value=\"$OrgaoLicitanteCodigo\">\n";
                //         echo "<input type=\"hidden\" name=\"ComissaoCodigo\" value=\"$ComissaoCodigo\">\n";
                //         echo "<input type=\"hidden\" name=\"ModalidadeCodigo\" value=\"$ModalidadeCodigo\">\n";
                //         echo "<input type=\"hidden\" name=\"GrupoCodigo\" value=\"$GrupoCodigo\">\n";
                //         echo "<input type=\"hidden\" name=\"LicitacaoProcesso\" value=\"$LicitacaoProcesso\">\n";
                //         echo "<input type=\"hidden\" name=\"LicitacaoAno\" value=\"$LicitacaoAno\">\n";
                //         echo "<input type=\"hidden\" name=\"DocumentoCodigo\" value=\"$DocumentoCodigo\">\n";
                //         echo "<input type=\"hidden\" name=\"SolicitanteCodigo\" value=\"$SolicitanteCodigo\">\n";
                //         echo "</form>\n";
                //         echo "</body>\n";
                //         echo "<script language=\"javascript\">";
                //         echo "document.Arquivo.submit();";
                //         echo "</script>";
                //         echo "</html>\n";
                //         exit();
                //     }
                // }
                
                $db->query("COMMIT");
                $db->query("END TRANSACTION");
                $db->disconnect();
        }
    }

    
        $db = Conexao();
        $sql = 'SELECT EDOCLINOME FROM SFPC.TBDOCUMENTOLICITACAO ';
        $sql .= " WHERE CLICPOPROC = $LicitacaoProcesso ";
        $sql .= "   AND ALICPOANOP = $LicitacaoAno AND CCOMLICODI = $ComissaoCodigo ";
        $sql .= "   AND CGREMPCODI = $GrupoCodigo AND CDOCLICODI = $DocumentoCodigo";
        $result = $db->query($sql);
        
        if (PEAR::isError($result)) {
            ExibeErroBD("$ErroPrograma\nLinha: 67\nSql: $sql");
        } else {
            while ($Linha = $result->fetchRow()) {
                $NomeArquivo = $Linha[0];
            }
        }
        
        $db->disconnect();
    
        $ArquivoNomeServidor = 'licitacoes/DOC' . $GrupoCodigo . '_' . $LicitacaoProcesso . '_' . $LicitacaoAno . '_' . $ComissaoCodigo . '_' . $OrgaoLicitanteCodigo . '_' . $DocumentoCodigo;
        $Arq = $GLOBALS['CAMINHO_UPLOADS'] . $ArquivoNomeServidor;
        
        if (file_exists($Arq)) {
            
            addArquivoAcesso($ArquivoNomeServidor);
            $ArquivoNomeServidor = str_replace('/', '%2F', $ArquivoNomeServidor);
            $url = '../carregarArquivo.php?arq=' . $ArquivoNomeServidor . '&arq_nome=' . urlencode($NomeArquivo);
            header("Location: $url ");
            exit();
        }
}


// Se o fornecedor existir no sicref
if ($existenteFornecedor) {
    $tpl->MOSTRAR_DADOS = 'none';
} else {
    $tpl->MOSTRAR_DADOS = 'block';
}

if ($Mens != 0) {
    $tpl->exibirMensagemFeedback($Mensagem, $Tipo);
}

$tpl->NUMERO_PROCESSO = $LicitacaoProcesso;
$tpl->ANO = $LicitacaoAno;
$tpl->ORGAO_LICITANTE_CODIGO = $OrgaoLicitanteCodigo;
$tpl->COMISSAO_CODIGO = $ComissaoCodigo;
$tpl->MODALIDADE_CODIGO = $ModalidadeCodigo;
$tpl->GRUPO_CODIGO = $GrupoCodigo;
$tpl->LICITACAO_PROCESSO = $LicitacaoProcesso;
$tpl->LICITACAO_ANO = $LicitacaoAno;
$tpl->DOCUMENTO_CODIGO = $DocumentoCodigo;
$tpl->VERIFICA_CPF = $VerificaCPF;

if ($CPF_CNPJ != "" && $VerificaCPF == "S") {
    $numeroCpfCnpj = "";
    
    if (strlen($CPF_CNPJ) == 14) {
        $numeroCpfCnpj = FormataCNPJ($CPF_CNPJ);
    } else {
        $numeroCpfCnpj = FormataCPF($CPF_CNPJ);
    }
    
    $tpl->NUMERO_INSCRICAO = $numeroCpfCnpj;
    $tpl->CPF_CNPJ = $CPF_CNPJ;
    $tpl->CNPJ_CPF = $CnpjCpf;
    
    $tpl->block("BLOCO_CPF_CNPJ_CONFIRMADO");
} else {
    if ($CnpjCpf == "CPF" || $CnpjCpf == "") {
        $tpl->CHECKED_CPF = "checked";
    }
    
    if ($CnpjCpf == "CNPJ") {
        $tpl->CHECKED_CNPJ = "checked";
    }
    
    $tpl->CPF_CNPJ = $CPF_CNPJ;
    if (! $existente) {
        $tpl->block("BLOCO_INPUT_CPF_CNPJ");
    }
}

if ($VerificaCPF != "") {
    // Razão social
    $tpl->RAZAO_SOCIAL = $RazaoSocial;
    if ($existente == false) {
        $tpl->block("BLOCO_INPUT_RAZAO_SOCIAL");
    } else {
        $tpl->block("BLOCO_P_RAZAO_SOCIAL");
    }
    
    // Endereço
    $FormEnd = $Endereco;
    if ($Numero != "") {
        $FormEnd .= ", $Numero";
    }
    if ($Complemento != "") {
        $FormEnd .= " $Complemento";
    }
    if ($Bairro != "") {
        $FormEnd .= " - $Bairro";
    }
    if ($Estado != "") {
        $FormEnd .= "/$Estado";
    }
    $tpl->ENDERECO = $FormEnd;
    if ($existente == false) {
        $tpl->block("BLOCO_INPUT_ENDERECO");
    } else {
        $tpl->block("BLOCO_P_ENDERECO");
    }
    
    // Renderiza linha BLOCO_RAZAO_SOCIAL_E_ENDERECO
    $tpl->block("BLOCO_RAZAO_SOCIAL_E_ENDERECO");
    
    // Valores de campos ocultos
    $tpl->NUMERO = $Numero;
    $tpl->COMPLEMENTO = $Complemento;
    $tpl->BAIRRO = $Bairro;
    $tpl->CIDADE = $Cidade;
    $tpl->ESTADO = $Estado;
    $tpl->CEP = $CEP;
    
    // E-mail
    $tpl->EMAIL = $Email;
    if ($existente == false) {
        $tpl->block("BLOCO_INPUT_EMAIL");
    } else {
        $tpl->block("BLOCO_P_EMAIL");
    }
    
    // Telefone
    $tpl->TELEFONE = $Telefone;
    if (! $existenteFornecedor) {
        if ($existente == false) {
            $tpl->block("BLOCO_INPUT_TELEFONE");
        } else {
            $tpl->block("BLOCO_P_TELEFONE");
        }
    }
    
    // Renderiza linha BLOCO_EMAIL_E_TELEFONE
    $tpl->block("BLOCO_EMAIL_E_TELEFONE");
    
    // Fax
    $tpl->FAX = $Fax;
    if (! $existenteFornecedor) {
        if ($existente == false) {
            $tpl->block("BLOCO_INPUT_FAX");
        } else {
            $tpl->block("BLOCO_P_FAX");
        }
    }
    
    // Nome do contato
    $tpl->NOME_CONTATO = $NomeContato;
    if ($existente == false) {
        $tpl->block("BLOCO_INPUT_NOME_CONTATO");
    } else {
        $tpl->block("BLOCO_P_NOME_CONTATO");
    }
    
    // Renderiza linha BLOCO_FAX_E_NOME_CONTATO
    $tpl->block("BLOCO_FAX_E_NOME_CONTATO");
    
    // $tpl->block("BLOCO_VERIFICADO");
    $tpl->block("BLOCO_BOTAO_ENVIAR");
} else {
    $tpl->block("BLOCO_BOTAO_CONFIRMAR");
}

$tpl->TIPOCPFCNPJ = $CnpjCpf;
$tpl->VALOREMAIL = $_SESSION['elisolmail'];

$tpl->show();
