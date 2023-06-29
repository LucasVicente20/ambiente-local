<?php
/**
 * Portal da DGCO
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
 * @package   App
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 * @version   Git: $Id:$
 */

if (!@require_once dirname(__FILE__)."/TemplateAppPadrao.php") {
    throw new Exception("Error Processing Request - TemplateAppPadrao.php", 1);
}

$tpl = new TemplateAppPadrao("templates/EmissaoCHF.html");
/*
 * Acesso ao arquivo de funções
 */
require_once dirname(__FILE__) . '/../fornecedores/funcoesFornecedores.php';

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/fornecedores/EmissaoCHFSenha.php');
AddMenuAcesso('/fornecedores/EmissaoCHFSelecionar.php');
AddMenuAcesso('/fornecedores/RelEmissaoCHFPdf.php');
AddMenuAcesso('/oracle/fornecedores/RotDebitoCredorConsulta.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $Sequencial     = $_GET['Sequencial'];
    $Irregularidade = $_GET['Irregularidade'];
    $CPF_CNPJ       = $_GET['CPF_CNPJ'];
    $TipoDoc        = $_GET['TipoDoc'];
    $TipoCnpjCpf    = $_GET['TipoCnpjCpf'];

    if ($TipoCnpjCpf == "CNPJ") {
        $TipoDoc = 1;
    } elseif ($TipoCnpjCpf == "CPF") {
        $TipoDoc = 2;
    }

    $Mens           = $_GET['Mens'];
    $Mensagem       = $_GET['Mensagem'];
    $Tipo           = $_GET['Tipo'];
} else {
    $Mensagem       = $_POST['Mensagem'];
    $Botao          = $_POST['Botao'];
    $Sequencial     = $_POST['Sequencial'];
    $CPF_CNPJ       = $_POST['CPF_CNPJ'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Redireciona o programa de acordo com o botão voltar #
if ($Botao == "Voltar") {
    if ($_SESSION['_cperficodi_'] == 0) {
        header("location: EmissaoCHFSenha.php");
        exit;
    } else {
        header("location: EmissaoCHFSelecionar.php");
        exit;
    }
}

$db = Conexao();

if ($Botao == "Imprimir") {
    $db->query("BEGIN TRANSACTION");

    if ($_SESSION['_cperficodi_'] == 0) {
        $sql = " SELECT MAX(AFORCHNEMF) FROM SFPC.TBFORNECEDORCHF";
    } else {
        $sql = " SELECT MAX(AFORCHNEMU) FROM SFPC.TBFORNECEDORCHF";
    }

    $sql   .= " WHERE AFORCRSEQU = $Sequencial";

    $result = $db->query($sql);

    if (PEAR::isError($result)) {
        $db->query("ROLLBACK");
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
    } else {
        $Linha = $result->fetchRow();

        if ($Linha[0] == 0) {
            $QtdVias = 1;
        } else {
            $QtdVias = $Linha[0] + 1;
        }

        # Atualiza a tabela de CHF #
        $sql = " UPDATE SFPC.TBFORNECEDORCHF SET ";

        if ($_SESSION['_cperficodi_'] == 0) {
            $sql .= " AFORCHNEMF = $QtdVias, DFORCHULEF = '".date("Y-m-d")."', ";
        } else {
            $sql .= " AFORCHNEMU = $QtdVias, CGREMPCOD1 = ".$_SESSION['_cgrempcodi_'].", ";
            $sql .= " CUSUPOCOD1 = ".$_SESSION['_cusupocodi_'].", DFORCHULEU = '".date("Y-m-d")."', ";
        }

        $sql   .= "        TFORCHULAT = '".date("Y-m-d H:i:s")."'";
        $sql   .= "  WHERE AFORCRSEQU = $Sequencial";

        $result = $db->query($sql);

        if (PEAR::isError($result)) {
            $db->query("ROLLBACK");
            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
        } else {
            $db->query("COMMIT");
            $db->query("END TRANSACTION");

            $Url = "RelEmissaoCHFPdf.php?Sequencial=$Sequencial&Mensagem=".urlencode($Mensagem)."";

            if (!in_array($Url, $_SESSION['GetUrl'])) {
                $_SESSION['GetUrl'][] = $Url;
            }

            header("location: ".$Url);
            exit;
        }
    }
}

if ($Botao == "") {
    $Fornecedor = "";

    # Verifica se o fornecedor foi incluido por inscrição e aprovado #
    $sqlpre  = "SELECT COUNT(A.APREFOSEQU) ";
    $sqlpre .= "  FROM SFPC.TBPREFORNECEDOR A, SFPC.TBFORNECEDORCREDENCIADO B ";
    $sqlpre .= "  WHERE A.APREFOSEQU = B.APREFOSEQU AND ";

    if ($TipoDoc == 1) {
        $sqlpre .= " A.APREFOCCGC = '$CPF_CNPJ'";
    } elseif ($TipoDoc == 2) {
        $sqlpre .= " A.APREFOCCPF = '$CPF_CNPJ'";
    }

    $respre = $db->query($sqlpre);

    if (PEAR::isError($respre)) {
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlpre");
    } else {
        $Linha = $respre->fetchRow();

        if ($Linha[0] == 0) {
            # Verifica se o fornecedor foi incluido por cadastro e gestão #
            $sqlfor  = " SELECT COUNT(AFORCRSEQU)FROM SFPC.TBFORNECEDORCREDENCIADO ";
            $sqlfor .= "  WHERE AFORCRSEQU = $Sequencial";

            $resfor  = $db->query($sqlfor);

            if (PEAR::isError($resfor)) {
                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
            } else {
                $Linha = $resfor->fetchRow();

                if ($Linha[0] == 0) {
                    $Mens     = 1;
                    $Tipo     = 2;
                    $Mensagem = "O Fornecedor está apenas Inscrito não pode Emitir CHF";

                    if ($_SESSION['_cperficodi_'] == 0) {
                        $Url = "EmissaoCHFSenha.php?Mens=$Mens&Tipo=$Tipo&Mensagem=".urlencode($Mensagem)."";

                        if (!in_array($Url, $_SESSION['GetUrl'])) {
                            $_SESSION['GetUrl'][] = $Url;
                        }

                        header("location: ".$Url);
                        exit;
                    } else {
                        $Url = "EmissaoCHFSelecionar.php?Mens=$Mens&Tipo=$Tipo&Mensagem=".urlencode($Mensagem)."";

                        if (!in_array($Url, $_SESSION['GetUrl'])) {
                            $_SESSION['GetUrl'][] = $Url;
                        }

                        header("location: ".$Url);
                        exit;
                    }
                } else {
                    $Fornecedor = "S";
                }
            }
        } else {
            $Fornecedor = "S";
        }

        if ($Fornecedor == "S") {
            # Pega os Dados do Fornecedor Cadastrado #
            // inserir as colunas DFORCRULTB, FFORCRMEPP (Heraldo)
            $sqlfor  = " SELECT AFORCRSEQU, APREFOSEQU, AFORCRCCGC, AFORCRCCPF, NFORCRRAZS ";
            $sqlfor .= " 			 ,DFORCRULTB, DFORCRCNFC,  DFORCRULTB, FFORCRMEPP  ";
            $sqlfor .= "   FROM SFPC.TBFORNECEDORCREDENCIADO ";
            $sqlfor .= "  WHERE AFORCRSEQU = $Sequencial";

            $resfor  = $db->query($sqlfor);

            if (PEAR::isError($resfor)) {
                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
            } else {
                $Linha = $resfor->fetchRow();
                $Sequencial          = $Linha[0];
                $PreInscricao        = $Linha[1];
                $CNPJ                = $Linha[2];
                $CPF                 = $Linha[3];
                $RazaoSocial         = $Linha[4];
                $DataNovaUltBalanco  = $Linha[5];
                $DataNovaCertidaoNeg = $Linha[6];
                $DataBalanco         = $Linha[7];
                $MicroEmpresa        = $Linha[8];

                # Pega os Dados da Tabela de Situação #
                $sql    = "SELECT A.DFORSISITU, B.CFORTSCODI, A.EFORSIMOTI, A.DFORSIEXPI, B.EFORTSDESC ";
                $sql   .= "  FROM SFPC.TBFORNSITUACAO A, SFPC.TBFORNECEDORTIPOSITUACAO B ";
                $sql   .= " WHERE A.AFORCRSEQU = $Sequencial ";
                $sql   .= "   AND A.CFORTSCODI = B.CFORTSCODI ";
                $sql   .= " ORDER BY A.DFORSISITU DESC";

                $result = $db->query($sql);

                if (PEAR::isError($result)) {
                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                } else {
                    $Linha = $result->fetchRow();

                    if ($Linha[0] != "") {
                        $DataSituacao = substr($Linha[0], 8, 2)."/".substr($Linha[0], 5, 2)."/".substr($Linha[0], 0, 4);
                    } else {
                        $DataSituacao = "";
                    }

                    if ($Linha[1] <> 1) {
                        $Mens     = 1;
                        $Tipo     = 1;
                        $Mensagem = "Fornecedor $RazaoSocial -".$Linha[4];
                        if ($_SESSION['_cperficodi_'] == 0) {
                            $Url = "EmissaoCHFSenha.php?Mens=$Mens&Tipo=$Tipo&Mensagem=".urlencode($Mensagem)."";

                            if (!in_array($Url, $_SESSION['GetUrl'])) {
                                $_SESSION['GetUrl'][] = $Url;
                            }

                            header("location: ".$Url);
                            exit;
                        } else {
                            $Url = "EmissaoCHFSelecionar.php?Mens=$Mens&Tipo=$Tipo&Mensagem=".urlencode($Mensagem)."";

                            if (!in_array($Url, $_SESSION['GetUrl'])) {
                                $_SESSION['GetUrl'][] = $Url;
                            }

                            header("location: ".$Url);
                            exit;
                        }
                    }
                }

                $Cadastrado = "HABILITADO";

                // Variáveis informando os motivos de inabilitação
                $InabilitacaoCertidaoObrigatoria = false;
                $InabilitacaoUltBalanco = false;
                $InabilitacaoCertidaoNeg = false;
                
                # Verifica também se a data de balanço anual está no prazo #
                if (!empty($DataNovaUltBalanco) and !empty($MicroEmpresa)) {
                    if ($DataNovaUltBalanco < prazoUltimoBalanço()->format('Y-m-d')) {
                        $InabilitacaoUltBalanco = true;
                    }
                }

                # Verifica também se a data de certidão negativa está no prazo #
                if ($DataNovaCertidaoNeg < prazoCertidaoNegDeFalencia()->format('Y-m-d')) {
                    $Cadastrado = "INABILITADO";
                    $InabilitacaoCertidaoNeg = true;
                }
                
                $Cadastrado = 0;

                # Verifica a Validação das Certidões do Fornecedor #
                $sql  = "SELECT A.CTIPCECODI, A.ETIPCEDESC, B.DFORCEVALI ";
                $sql .= "  FROM SFPC.TBTIPOCERTIDAO A, SFPC.TBFORNECEDORCERTIDAO B ";
                $sql .= " WHERE A.CTIPCECODI = B.CTIPCECODI AND A.FTIPCEOBRI = 'S' ";
                $sql .= "   AND B.AFORCRSEQU = $Sequencial";
                $sql .= " ORDER BY 2";

                $result = $db->query($sql);

                if (PEAR::isError($result)) {
                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                } else {
                    $Rows = $result->numRows();

                    for ($i=0; $i<$Rows;$i++) {
                        $DataHoje = date("Y-m-d");
                        $Linha = $result->fetchRow();

                        if ($Linha[2] < $DataHoje) {
                            $Cadastrado = "INABILITADO";
                            $InabilitacaoCertidaoObrigatoria = true;
                            break;
                        }
                    }
                }

                # Verifica se já Existe Data de CHF #
                $sql    = "SELECT DFORCHGERA, DFORCHVALI, AFORCHNEMF, DFORCHULEF, ";
                $sql   .= "       AFORCHNEMU, CGREMPCOD1, CUSUPOCOD1, DFORCHULEU ";
                $sql   .= " FROM SFPC.TBFORNECEDORCHF WHERE AFORCRSEQU = $Sequencial ";

                $result = $db->query($sql);

                if (PEAR::isError($result)) {
                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                } else {
                    $Rows = $result->numRows();

                    if ($Rows != 0) {
                        $Linha = $result->fetchRow();
                        $DataGeracaoCHF = DataBarra($Linha[0]);
                        $DataValidade   = DataBarra($Linha[1]);
                        $NumFornecedor  = $Linha[2];

                        if ($Linha[3] != "") {
                            $DataFornecedor = DataBarra($Linha[3]);
                        }

                        $NumPrefeitura  = $Linha[4];
                        $Grupo          = $Linha[5];
                        $Usuario        = $Linha[6];

                        if ($Linha[7] != "") {
                            $DataPrefeitura = DataBarra($Linha[7]);
                        }
                    } else {
                        $Mens     = 1;
                        $Tipo     = 2;
                        $Mensagem = "Data de Validade do CHF não informado no Cadastro";

                        if ($_SESSION['_cperficodi_'] == 0) {
                            $Url = "EmissaoCHFSenha.php?Mens=$Mens&Tipo=$Tipo&Mensagem=".urlencode($Mensagem)."";

                            if (!in_array($Url, $_SESSION['GetUrl'])) {
                                $_SESSION['GetUrl'][] = $Url;
                            }

                            header("location: ".$Url);
                            exit;
                        } else {
                            $Url = "EmissaoCHFSelecionar.php?Mens=$Mens&Tipo=$Tipo&Mensagem=".urlencode($Mensagem)."";

                            if (!in_array($Url, $_SESSION['GetUrl'])) {
                                $_SESSION['GetUrl'][] = $Url;
                            }

                            header("location: ".$Url);
                            exit;
                        }
                    }
                }

                if ($NumPrefeitura != 0) {
                    # Pega o Nome do Responsável #
                    $sql    = "SELECT EUSUPORESP FROM SFPC.TBUSUARIOPORTAL";
                    $sql   .= " WHERE CGREMPCODI = $Grupo AND CUSUPOCODI = $Usuario";

                    $result = $db->query($sql);

                    if (PEAR::isError($result)) {
                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                    } else {
                        $Linha         = $result->fetchRow();
                        $Responsavel = $Linha[0];
                    }
                }
            }

            # Verifica se o Fornecedor está Regular na Prefeitura #
            /*
            if ($Irregularidade == "") {
                if ($CNPJ != "") {
                    $TipoDoc  = 1;
                    $CPF_CNPJ = $CNPJ;
                } elseif ($CPF != "") {
                    $TipoDoc  = 2;
                    $CPF_CNPJ = $CPF;
                }
                
                $NomePrograma = urlencode("EmissaoCHF.php");
                $Url = "fornecedores/RotDebitoCredorConsulta.php?NomePrograma=$NomePrograma&TipoDoc=$TipoDoc&CPF_CNPJ=$CPF_CNPJ&Sequencial=$Sequencial";

                if (!in_array($Url, $_SESSION['GetUrl'])) {
                    $_SESSION['GetUrl'][] = $Url;
                }

                Redireciona($Url);
                exit;
            }*/
        }
    }
}

# Mensagem para Fornecedor Inabilitado #
$bloquearFornecedor = false;

if ($InabilitacaoCertidaoObrigatoria) {
    if ($Irregularidade == "S") {
        $Mens     = 1;
        $Tipo     = 1;
        $bloquearFornecedor = true;

        if ($Cadastrado == "INABILITADO") {
            $Mensagem = "Certidão(ões) fora do prazo de validade e com situação irregular na Prefeitura";
        } else {
            $Mensagem = "situação irregular na Prefeitura";
        }

        if ($_SESSION['_cperficodi_'] == 0) {
            $Url = "EmissaoCHFSenha.php?Mens=$Mens&Tipo=$Tipo&Mensagem=".urlencode($Mensagem)."";

            if (!in_array($Url, $_SESSION['GetUrl'])) {
                $_SESSION['GetUrl'][] = $Url;
            }

            header("location: ".$Url);
            exit;
        } else {
            $Url = "EmissaoCHFSelecionar.php?Mens=$Mens&Tipo=$Tipo&Mensagem=".urlencode($Mensagem)."";

            if (!in_array($Url, $_SESSION['GetUrl'])) {
                $_SESSION['GetUrl'][] = $Url;
            }

            header("location: ".$Url);
            exit;
        }
    } else {
        if ($Cadastrado == "INABILITADO") {
            $Mens     = 1;
            $Tipo     = 1;
            $Mensagem = "Certidão(ões) fora do prazo de validade";
            $bloquearFornecedor = true;
        }
    }
}

if ($Cadastrado == "INABILITADO" and $InabilitacaoUltBalanco) {
    if ($Mens == 1) {
        $Mensagem .=", ";
    }

    $Mens     = 1;
    $Tipo     = 1;
    $Mensagem.= "Data de Validade do Balanço expirada";
    $bloquearFornecedor = true;
}

if ($Cadastrado == "INABILITADO" && $InabilitacaoCertidaoNeg) {
    if ($Mens == 1) {
        $Mensagem .=", ";
    }

    $Mens     = 1;
    $Tipo     = 1;
    $Mensagem.= "Data de Certidão Negativa expirada";
    $bloquearFornecedor = true;
}


if (!empty($MicroEmpresa) && empty($DataBalanco)) {
    if ($Mens == 1) {
        $Mensagem .=", ";
    }

    $Mens      = 1;
    $Tipo      = 1;
    $Mensagem .= "CHF simplificado sem demonstrações contábeis";
    $bloquearFornecedor = true;
}

if ($bloquearFornecedor) {
    $Mensagem =  "Fornecedor com " . $Mensagem;
}

if (!empty($Mensagem)) {
    $tpl->exibirMensagemFeedback($Mensagem, $Tipo);
}

if ($CNPJ != "") {
    $tpl->CPF_OR_CNPJ = "CNPJ\n";
} else {
    $tpl->CPF_OR_CNPJ = "CPF\n";
}

if ($CNPJ != 0) {
    $tpl->CPF_CNPJ = FormataCNPJ($CNPJ);
} else {
    $tpl->CPF_CNPJ = FormataCPF($CPF);
}

$tpl->RAZAO_SOCIAL  = $RazaoSocial;
$tpl->DATA_GERACAO  = $DataGeracaoCHF;
$tpl->DATA_VALIDADE = $DataValidade;

if ($NumFornecedor != 0) {
    $tpl->NUMERO_EMISSAO = $NumFornecedor;
} else {
    $tpl->NUMERO_EMISSAO = "0";
}

if ($DataFornecedor != 0) {
    $tpl->ULTIMA_DATA_EMISSAO = $DataFornecedor;
} else {
    $tpl->ULTIMA_DATA_EMISSAO = "-";
}

if ($NumPrefeitura != 0) {
    $tpl->NUMERO_EMISSAO_PREFEITURA = $NumPrefeitura;
} else {
    $tpl->NUMERO_EMISSAO_PREFEITURA = "0";
}

if ($DataPrefeitura != "") {
    $tpl->ULTIMA_DATA_EMISSAO_PREFEITURA = $DataPrefeitura;
} else {
    $tpl->ULTIMA_DATA_EMISSAO_PREFEITURA = "-";
}

if ($Responsavel != "") {
    $tpl->RESPONSAVEL_EMISSAO_PREFEITURA = $Responsavel;
} else {
    $tpl->RESPONSAVEL_EMISSAO_PREFEITURA = "-";
}

$tpl->CPF_CNPJ   = $CPF_CNPJ;
$tpl->GRUPO      = $Grupo;
$tpl->USUARIO    = $Usuario;
$tpl->SEQUENCIAL = $Sequencial;
$tpl->MSG        = $Mensagem;

echo $tpl->show();
/**
 * END
 */