<?php
#---------------------------------------------------------------------------
# Portal da DGCO
# Programa: RotDebitoCredorConsulta.php
# Objetivo: Programa de Verificação da Certidão Negativa dos Fornecedores
# Autor:    Roberta Costa
# Data:     14/10/2003
# Alterado: Álvaro Faria
# Data:     03/07/2006 - Uso do Pear / Mudanças para rodar em Cohab/Varzea / Correções
# Alterado: Carlos Abreu
# Data:     14/05/2007 - Corrigir chute!
# Alterado: Rodrigo Melo
# Data:     11/03/2008 - Correção para não receber CPF e CNPJ formatados ao realizar as queries no banco de dados.
# Alterado: Pitang Agile TI
# Data:     27/01/2015 - Redmine 249
#
# OBS.:     Tabulação 4 espaços
#---------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

//Array para remover a mascara
$RemoveMascara = array("." => "", "/" => "", "-" => "");

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $NomePrograma    = urldecode($_GET['NomePrograma']);
    $ProgramaSelecao = urldecode($_GET['ProgramaSelecao']);
    $TipoDoc         = $_GET['TipoDoc'];
    $Destino         = $_GET['Destino'];
    $Sequencial      = $_GET['Sequencial']; // Para Inscrição > Avaliação; Cadastro e Gestão; Acompanhamento
    $Situacao        = $_GET['Situacao'];   // Situação do Inscrito
    $CPF_CNPJ        = strtr($_GET['CPF_CNPJ'], $RemoveMascara);
    $Botao           = $_GET['Botao'];      // Para Inscrição > Avaliação;
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

$Mens       = 0;
$TipoDebito = 0;

# Item 2 - Se nTipoDoc = 1 ( Se for Pessoa Jurídica ) #
if ($TipoDoc == 1) {
    # Item 2.1 - Seleciona todas as empresas do mesmo CNPJ em SFCI.TBCONTRIBUINTE com condições #
        # Conectando no SFCI.TBCONTRIBUINTE para pegar a Inscrição Municipal Recife #
        $db = ConexaoOracle();
    $Sql  = "SELECT * FROM SFCI.TBCONTRIBUINTE ";
    $Sql .= " WHERE ACONTBDOCU = $CPF_CNPJ";
    $res = $db->query($Sql);
    if (PEAR::isError($res)) {
        $db->disconnect;
        ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $Sql");
        exit;
    } else {
        while ($Linha = $res->fetchRow()) {
            ;
            $SequencialDebito[] = $Linha[0];
            $NomeCont[]         = $Linha[1];
            $row++;
        }
    }

    for ($c=0;$c<$row;$c++) {
        $SequCont = $SequencialDebito[$c];
                # Conectando ao SFCM.TBMERCANTIL para verificar se existe Inscrição Municipal Recife #
                $Sql  = "SELECT AMERCTINSC, CSITUMCODI FROM SFCM.TBMERCANTIL ";
        $Sql .= " WHERE ACONTBSEQU = $SequCont";
        $res  = $db->query($Sql);
        if (PEAR::isError($res)) {
            $db->disconnect;
            ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $Sql");
            exit;
        } else {
            $Col = $res->numRows();
            if ($Col > 0) {
                $Linha        = $res->fetchRow();
                $Inscricao    = $Linha[0];
                $SituacaoMerc = $Linha[1];

                                # Item 3.1 - Verificar se existe débito mercantil para o contribuinte #
                                $Retorno1 = DebMercantil($TipoDebito, $Inscricao, $SituacaoMerc);

                                # Item 3.2 - Verificar se existe imóvel associado ao contribuinte mercantil #
                                $Retorno2 = ImovelAssociado($Retorno1, $Inscricao);
            }
        }
                # Item 3.3 - Verificar se existe débito imobiliário para o contribuinte #
              $Retorno3 = DebImobiliario($Retorno2, $SequCont);

                # Item 3.4 - Verificar se existe débito de ITBI para o contribuinte #
          $Retorno4 = DebITBI($Retorno3, $SequCont);
    }
    $db->disconnect();

    $NomeCont = urlencode($NomeCont[0]);
    if ($Retorno > 0) {
        $Irregularidade = "S";
    } else {
        $Irregularidade = "N";
    }

    // [CUSTOMIZAÇÃO]
    $programaDeRedirecionamento = getUriRedirecionamentoFornecedores($NomePrograma, $_SERVER["HTTP_REFERER"]);
    $Url = "$programaDeRedirecionamento?ProgramaSelecao=$ProgramaSelecao&Irregularidade=$Irregularidade&Destino=$Destino&Origem=A&Sequencial=$Sequencial&Situacao=$Situacao&CPF_CNPJ=$CPF_CNPJ&Botao=$Botao&TipoDoc=$TipoDoc";
    // [/CUSTOMIZAÇÃO]
    
    // [ORIGINAL]
    //$Url = "fornecedores/$NomePrograma?ProgramaSelecao=$ProgramaSelecao&Irregularidade=$Irregularidade&Destino=$Destino&Origem=A&Sequencial=$Sequencial&Situacao=$Situacao&CPF_CNPJ=$CPF_CNPJ&Botao=$Botao&TipoDoc=$TipoDoc";
    // [/ORIGINAL]
    
    if (!in_array($Url, $_SESSION['GetUrl'])) {
    	$_SESSION['GetUrl'][] = $Url;
    }

    header('Location: ' . $Url);
# Item 3 - Se nTipoDoc = 2 ( Se for Pessoa Fisica ) #
} elseif ($TipoDoc == 2) {
    # Conectando no SFCI.TBCONTRIBUINTE para pegar a Inscrição Municipal Recife #
        $db = ConexaoOracle();
    $Sql   = "SELECT * FROM SFCI.TBCONTRIBUINTE ";
    $Sql  .= " WHERE ACONTBDOCU = $CPF_CNPJ";
    $res   = $db->query($Sql);
    if (PEAR::isError($res)) {
        $db->disconnect;
        ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $Sql");
        exit;
    } else {
        while ($Linha = $res->fetchRow()) {
            $SequencialDebito[] =  $Linha[0];
            $NomeCont[]         =  $Linha[0];
            $row++;
        }
    }

    for ($c=0;$c<$row;$c++) {
        $SequCont = $SequencialDebito[$c];
        $Sql   = "SELECT COUNT(*) AS Cont FROM SFCM.TBMERCANTIL ";
        $Sql  .= "WHERE ACONTBSEQU = $SequCont";
        $res   = $db->query($Sql);
        if (PEAR::isError($res)) {
            $db->disconnect;
            ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $Sql");
            exit;
        } else {
            $Linha = $res->fetchRow();
            $Col   =  $Linha[0];
        }

        if ($Col != 0) {
            $Sql  = "SELECT AMERCTINSC, CSITUMCODI FROM SFCM.TBMERCANTIL ";
            $Sql .= "WHERE ACONTBSEQU = $SequCont";
            $res   = $db->query($Sql);
            if (PEAR::isError($res)) {
                $db->disconnect;
                ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $Sql");
                exit;
            } else {
                $Linha = $res->fetchRow();
                $Inscricao    = $Linha[0];
                $SituacaoMerc = $Linha[1];
            }

                        # Item 3.1 - Verificar se existe débito mercantil para o contribuinte #
                        $Retorno1 = DebMercantil($TipoDebito, $Inscricao, $SituacaoMerc);
            if ($Retorno1 != 0) {
                $Retorno = $Retorno + 1;
            }

                        # Item 3.2 - Verificar se existe imóvel associado ao contribuinte mercantil #
                        $Retorno2 = ImovelAssociado($Retorno1, $Inscricao);
            if ($Retorno2 != 0) {
                $Retorno = $Retorno + 1;
            }
        }

                # Item 3.3 - Verificar se existe débito imobiliário para o contribuinte #
              $Retorno3 = DebImobiliario($Retorno2, $SequCont);
        if ($Retorno3 != 0) {
            $Retorno = $Retorno + 1;
        }

                # Item 3.4 - Verificar se existe débito de ITBI para o contribuinte #
          $Retorno4 = DebITBI($Retorno3, $SequCont);
        if ($Retorno4 != 0) {
            $Retorno = $Retorno + 1;
        }
    }
    $db->disconnect();

    $NomeCont = urlencode($NomeCont[0]);
    if ($Retorno > 0) {
        $Irregularidade = "S";
    } else {
        $Irregularidade = "N";
    }

    // [CUSTOMIZAÇÃO]
    $programaDeRedirecionamento = getUriRedirecionamentoFornecedores($NomePrograma, $_SERVER["HTTP_REFERER"]);
    $Url = "$programaDeRedirecionamento?ProgramaSelecao=$ProgramaSelecao&Irregularidade=$Irregularidade&Destino=$Destino&Origem=A&Sequencial=$Sequencial&Situacao=$Situacao&CPF_CNPJ=$CPF_CNPJ&Botao=$Botao&TipoDoc=$TipoDoc";
    // [/CUSTOMIZAÇÃO]

    // [ORIGINAL]
    //$Url = "app/$NomePrograma?ProgramaSelecao=$ProgramaSelecao&Irregularidade=$Irregularidade&Destino=$Destino&Origem=A&Sequencial=$Sequencial&Situacao=$Situacao&CPF_CNPJ=$CPF_CNPJ&Botao=$Botao&TipoDoc=$TipoDoc";
    // [/ORIGINAL]

    if (!in_array($Url, $_SESSION['GetUrl'])) {
        $_SESSION['GetUrl'][] = $Url;
    }
    
    header('Location: ' . $Url);
}

# Funções #

# 001 - Verifica se Existe Débito Mercantil para o Contribuinte #
function DebMercantil($TipoDebito, $Inscricao, $SituacaoMerc)
{
    # Conectando ao SFCM.TBMERCANTILTRIBUTO para pegar o codigo do Tributo #
        $db = ConexaoOracle();
    $Sql   = "SELECT CTRIBUCODI FROM SFCM.TBMERCANTILTRIBUTO ";
    $Sql  .= "WHERE AMERCTINSC = $Inscricao";
    $res   = $db->query($Sql);
    if (PEAR::isError($res)) {
        $db->disconnect;
        ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $Sql");
        exit;
    } else {
        $Linha = $res->fetchRow();
        $Tributo = $Linha[0];
    }
        # Conectando ao SFCM.TBLANCAMERCANTIL para pegar o codigo do Tributo #
        $Sql   = "SELECT count(*) FROM SFCM.TBLANCAMERCANTIL ";
    $Sql  .= "WHERE AMERCTINSC = $Inscricao AND CTRIBUCODI = $Tributo";
    $res   = $db->query($Sql);
    if (PEAR::isError($res)) {
        $db->disconnect;
        ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $Sql");
        exit;
    } else {
        $Linha = $res->fetchRow();
        $Resultado1 = $Linha[0];
    }
    if ($Resultado1 != 0) {
        if ($TipoDebito != 0) {
            $TipoDebito = 3;
        } else {
            $TipoDebito = 2;
        }
    }
    return $TipoDebito;
    $db->disconnect();
}

# 002 - Verifica se Existe Imóvel associado ao Contribuinte Mercantil #
function ImovelAssociado($TipoDebito, $Inscricao)
{
    # Conectando ao SFCM.TBMERCANTILIMOVEL para pegar o Sequencial do Imóvel #
        $db = ConexaoOracle();
    $Sql   = "SELECT AIMOVESEQU FROM SFCM.TBMERCANTILIMOVEL ";
    $Sql  .= " WHERE AMERCTINSC = $Inscricao AND CTPENDCODI = 1";
    $res   = $db->query($Sql);
    if (PEAR::isError($res)) {
        $db->disconnect;
        ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $Sql");
        exit;
    } else {
        while ($Linha = $res->fetchRow()) {
            $ImovSequ[] = $Linha[0];
            $row++;
        }
    }
    for ($c=0;$c<$row;$c++) {
        $ImovCont = $ImovSequ[$c];
        $Sql   = "SELECT COUNT(*) AS Cont FROM SFCI.TBLANCAIMOBILIARIO ";
        $Sql  .= " WHERE AIMOVESEQU = $ImovCont";
        $res   = $db->query($Sql);
        if (PEAR::isError($res)) {
            $db->disconnect;
            ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $Sql");
            exit;
        } else {
            $Linha = $res->fetchRow();
            $Resultado2 = $Linha[0];
        }
    }
    if ($Resultado2 != 0) {
        if ($TipoDebito == 2) {
            $TipoDebito = 3;
        } else {
            $TipoDebito = 1;
        }
    }
    return $TipoDebito;
    $db->disconnect();
}

# 003 - Verifica se Existe Débito Imobiliário para o Contribuinte #
function DebImobiliario($TipoDebito, $SequencialDebito)
{
    # Conectando ao SFCI.TBCONTIBIMOVEL para pegar o Sequencial do Imóvel #
        $db = ConexaoOracle();
    $Sql   = " SELECT AIMOVESEQU FROM SFCI.TBCONTIBIMOVEL ";
    $Sql  .= " WHERE ACONTBSEQU = $SequencialDebito";
    $res   = $db->query($Sql);
    if (PEAR::isError($res)) {
        $db->disconnect;
        ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $Sql");
        exit;
    } else {
        while ($Linha = $res->fetchRow()) {
            $ImovSequ[] = $Linha[0];
            $row++;
        }
    }
    for ($c=0;$c<$row;$c++) {
        $ImovCont = $ImovSequ[$c];
        $Sql   = "SELECT count(*) FROM SFCI.TBLANCAIMOBILIARIO ";
        $Sql  .= "WHERE AIMOVESEQU = $ImovCont";
        $res   = $db->query($Sql);
        if (PEAR::isError($res)) {
            $db->disconnect;
            ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $Sql");
            exit;
        } else {
            $Linha = $res->fetchRow();
            $Resultado3 = $Linha[0];
        }
    }
    if ($Resultado3 != 0) {
        if ($TipoDebito != 0 or $TipoDebito != "") {
            $TipoDebito = 3;
        } else {
            $TipoDebito = 1;
        }
    }
    return $TipoDebito;
    $db->disconnect();
}

# 004 - Verifica se Existe Débito de ITBI para o Contribuinte #
function DebITBI($TipoDebito, $SequencialDebito)
{
    # Conectando ao SFIT.TBCONTRIBPROCESSO para pegar o número do Processo #
        $db = ConexaoOracle();
        # Ativação de NumRows #
//		$db->setOption('portability', DB_PORTABILITY_NUMROWS);
        $Sql  = "SELECT APROCENUMR FROM SFIT.TBCONTRIBPROCESSO ";
    $Sql .= "WHERE ACONTBSEQU = $SequencialDebito"; //Sequencial do Contribuinte
        $res  = $db->query($Sql);
    if (PEAR::isError($res)) {
        $db->disconnect;
        ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $Sql");
        exit;
    } else {
        $Linha = $res->fetchRow();
        $ProcNum = $Linha[0];
        if ($ProcNum) {
            # Conectando ao SFIT.TBPROCESSOITBI para pegar o Processo Pago #
                        $Sql  = "SELECT FPROITPPAG FROM SFIT.TBPROCESSOITBI ";
            $Sql .= "WHERE APROCENUMR = $ProcNum";
            $res  = $db->query($Sql);
            if (PEAR::isError($res)) {
                $db->disconnect;
                ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $Sql");
                exit;
            } else {
                $Linha = $res->fetchRow();
                $ProcPag = $Linha[0];
                if ($Procpag = 'S') {
                    if ($TipoDebito != 0) {
                        $TipoDebito = 3;
                    } else {
                        $TipoDebito = 1;
                    }
                } else {
                    # Conectando ao SFIT.TBPROCESSOITBI e SFIT.TBCONTRIBPROCESSO para pegar o Processo Pago #
                                        $Sql  = "SELECT b.ACONTBSEQU FROM SFIT.TBPROCESSOITBI a, SFIT.TBCONTRIBPROCESSO b ";
                    $Sql .= "WHERE b.AIMOVSEQU = a.AIMOVSEQU AND a.APROCENUMR = b.APROCENUMR ";
                    $Sql .= "ORDER BY DPROITAVAL";
                    $res   = $db->query($Sql);
                    if (PEAR::isError($res)) {
                        $db->disconnect;
                        ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $Sql");
                        exit;
                    } else {
                        $Linha = $res->fetchRow();
                        $SequCont = $Linha[0];
                        if ($SequCont == $SequencialDebito) {
                            $Sql   = "SELECT AIMOVESEQU FROM SFCI.TBIMOVEL ";
                            $Sql  .= "WHERE ACONTBSEQU = $SequencialDebito";
                            $res   = $db->query($Sql);
                            if (PEAR::isError($res)) {
                                $db->disconnect;
                                ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                exit;
                            } else {
                                $Linha = $res->fetchRow();
                                $ImovSequ = $Linha[0];
                                $Sql  = "SELECT AIMOVESEQU FROM SFCI.TBLANCAIMOBILIARIO ";
                                $Sql .= "WHERE AIMOVESEQU = $ImovSequ";
                                $res   = $db->query($Sql);
                                if (PEAR::isError($res)) {
                                    $db->disconnect;
                                    ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $Sql");
                                    exit;
                                } else {
                                    $Rows = $res->numRows();
                                                                        //if( $Rows > 0 ){
                                                                                if ($TipoDebito == 2) {
                                                                                    $TipoDebito = 3;
                                                                                } else {
                                                                                    $TipoDebito = 1;
                                                                                }
                                                                        //}
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    return $TipoDebito;
    $db->disconnect();
}
