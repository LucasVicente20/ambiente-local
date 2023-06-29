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
 * @version   GIT: v1.19.0-13-gf127ebe
 *-----------------------------------------------------------------
 * CR 74234 - Acompanhamento de Fornecedores - mensagem errada
 * @version   v1.19.0-13-gf127ebe
 * ----------------------------------------------------------------
 * Alterado: Pitang Agile IT
 * CR 82766 *
 * @version v1.20.0-16-gc04b9f0
 */
if (!require_once dirname(__FILE__).'/TemplateAppPadrao.php') {
    throw new Exception('Error Processing Request - TemplateAppPadrao.php', 1);
}
require_once '../fornecedores/funcoesFornecedores.php';
require_once '../fornecedores/funcoesDocumento.php';

$tpl = new TemplateAppPadrao('templates/ConsAcompFornecedor.html', 'ConsAcompFornecedor');

// Adiciona páginas no MenuAcesso #
AddMenuAcesso('/fornecedores/ConsAcompFornecedorSenha.php');
AddMenuAcesso('/fornecedores/ConsAcompFornecedorSelecionar.php');
AddMenuAcesso('/fornecedores/RelAcompFornecedorPdf.php');
AddMenuAcesso('/fornecedores/CadGestaoFornecedorExcluido.php');
AddMenuAcesso('/oracle/fornecedores/RotDebitoCredorConsulta.php');

// Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $Irregularidade = $_GET['Irregularidade'];
} else {
    $Botao = $_POST['Botao'];
    $Mensagem = $_POST['Mensagem'];
    $_SESSION['Situacao'] = $_REQUEST['Situacao'];
    $download = $_POST['CodDownload'];
}

$Sequencial = $_REQUEST['Sequencial'];

// Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

// Redireciona o programa de acordo com o botão voltar #
if ($Botao == 'Voltar') {
    if ($_SESSION['_cperficodi_'] == 0) {
        header('Location: ConsAcompFornecedorSenha.php');
        exit();
    } else {
        if ($_SESSION['AcompFornecedorDesvio'] == 'CadRenovacaoCadastroIncluir') {
            header('Location: ConsAcompFornecedorSelecionar2.php?Desvio=CadRenovacaoCadastroIncluir');
            exit();
        } elseif ($_SESSION['AcompFornecedorDesvio'] == 'CadAnaliseCertidaoFornecedor') {
            header('Location: ConsAcompFornecedorSelecionar2.php?Desvio=CadAnaliseCertidaoFornecedor');
            exit();
        } else {
            header('Location: ConsAcompFornecedorSelecionar2.php');
            exit();
        }
    }
} elseif ($Botao == 'Imprimir') {
    $Url = "RelAcompFornecedorPdf.php?Sequencial=$Sequencial&Mensagem=".urlencode($Mensagem).'';
    if (!in_array($Url, $_SESSION['GetUrl'])) {
        $_SESSION['GetUrl'][] = $Url;
    }
    header('Location: '.$Url);
    exit();

}elseif ($Botao == 'Download'){

    //$docDown = getTramitacaoLicitacaoAnexos($licDown, $protDown, $seqDown);
    $docDown = $_SESSION['Arquivos_Upload'];

    $qtdup = count($docDown['conteudo']);
    for ($arqC = 0; $arqC < $qtdup; ++ $arqC) {

        if($download == $arqC){

            $arrNome = explode('.',$docDown['nome'][$arqC]);
            $extensao = $arrNome[1];
        
            $mimetype = 'application/octet-stream';
            
            header( 'Content-type: '.$mimetype ); 
            header( 'Content-Disposition: attachment; filename='.$docDown['nome'][$arqC] );   
            header( 'Content-Transfer-Encoding: binary' );
            header( 'Pragma: no-cache');
            
            echo pg_unescape_bytea($docDown['conteudo'][$arqC]);
        
            die();

        }

    }
}
$db = Conexao();

// Verificar data da certidão de último balanço
$dt_val_bal = getDataUltimoBalanco($db, $Sequencial);

if ($Botao == '') {
    $Mens = 0;
    $Mensagem = '';
    // Pega os Dados do Fornecedor Cadastrado #
    $sql = "
        SELECT
            AFORCRSEQU, APREFOSEQU, AFORCRCCGC, AFORCRCCPF, AFORCRIDEN,
            NFORCRORGU, NFORCRRAZS, NFORCRFANT, CCEPPOCODI, CCELOCCODI,
            EFORCRLOGR, AFORCRNUME, EFORCRCOMP, EFORCRBAIR, NFORCRCIDA,
            CFORCRESTA, AFORCRCDDD, AFORCRTELS, AFORCRNFAX, NFORCRMAIL,
            AFORCRCPFC, NFORCRCONT, NFORCRCARG, AFORCRDDDC, AFORCRTELC,
            AFORCRREGJ, DFORCRREGJ, AFORCRINES, AFORCRINME, AFORCRINSM,
            VFORCRCAPS, VFORCRCAPI, VFORCRPATL, VFORCRINLC, VFORCRINLG,
            DFORCRULTB, DFORCRCNFC, NFORCRENTP, AFORCRENTR, AFORCRENTT,
            DFORCRVIGE, DFORCRGERA, FFORCRCUMP, ECOMLIDESC, DFORCRANAL,
            FFORCRMEPP, VFORCRINDI, VFORCRINSO, NFORCRMAI2, FFORCRTIPO,
            DFORCRCONT

                FROM
            SFPC.TBFORNECEDORCREDENCIADO FORN
                LEFT OUTER JOIN SFPC.TBCOMISSAOLICITACAO COM ON FORN.CCOMLICODI = COM.CCOMLICODI
        WHERE AFORCRSEQU = $Sequencial
    ";

    $result = $db->query($sql);
    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
    } else {
        $Linha = $result->fetchRow();

        // Variáveis Formulário A #
        $tpl->SEQUENCIAL = $Linha[0];
        $PreInscricao = $Linha[1];
        $MicroEmpresa = $Linha[45];
        $tpl->CNPJ = $Linha[2];
        $CNPJ = $Linha[2];
        $tpl->CPF = $Linha[3];
        $CPF = $Linha[3];

        if ($CNPJ != null) {
            $CPF_CNPJ = $CNPJ;
        } elseif ($CPF != null) {
            $CPF_CNPJ = $CPF;
        }

        if ($Linha[2] == null) {
            $tpl->NOMECNPJCPF = 'CPF';
            $tpl->NOME_OR_RAZAO = 'Nome';
        } else {
            $tpl->NOMECNPJCPF = 'CNPJ';
            $tpl->NOME_OR_RAZAO = 'Razão Social';
        }

        $tpl->TITULO_MICROEMPRESA = getDescPorteEmpresaTitulo();
        $tpl->MICROEMPRESA = getDescPorteEmpresa($Linha[45]);
        $tpl->IDENTIDADE = $Linha[4];
        $tpl->ORGAOEMISSORUF = $Linha[5];
        $tpl->RAZAOSOCIAL = $Linha[6];
        $tpl->NOMEFANTASIA = $Linha[7];
        if ($Linha[7] == null) {
            $tpl->NOMEFANTASIA = 'NÃO INFORMADO';
        }
        if ($Linha[8] != '') {
            $tpl->CEP = $Linha[8];
        } else {
            $tpl->CEP = $Linha[9];
        }
        $tpl->LOGADOURO = $Linha[10];
        $tpl->NUMERO = $Linha[11];
        $tpl->COMPLEMENTO = $Linha[12];
        $tpl->BAIRRO = $Linha[13];
        $tpl->CIDADE = $Linha[14];
        $tpl->UF = $Linha[15];
        $tpl->DDD = $Linha[16];
        $tpl->TELEFONE = $Linha[17];
        $tpl->FAX = $Linha[18];
        if ($Linha[18] == null) {
            $tpl->FAX = 'NÃO INFORMADO';
        }
        $tpl->EMAIL = $Linha[19];
        $tpl->EMAIL2 = $Linha[48];
        if ($Linha[48] == null) {
            $tpl->EMAIL2 = 'NÃO INFORMADO';
        }
        if ($Linha[20] != '') {
            $tpl->CPFCONTATO = substr($Linha[20], 0, 3).'.'.substr($Linha[20], 3, 3).'.'.substr($Linha[20], 6, 3).'-'.substr($Linha[19], 9, 2);
        } else {
            $tpl->CPFCONTATO = 'NÃO INFORMADO';
        }
        $tpl->NOMECONTATO = $Linha[21];
        $tpl->CARGOCONTATO = $Linha[22];
        if ($Linha[22] == null) {
            $tpl->CARGOCONTATO = 'NÃO INFORMADO';
        }
        $tpl->DDDCONTATO = $Linha[23];
        if ($Linha[23] == null) {
            $tpl->DDDCONTATO = 'NÃO INFORMADO';
        }
        $tpl->TELEFONECONTATO = $Linha[24];
        if ($Linha[24] == null) {
            $tpl->TELEFONECONTATO = 'NÃO INFORMADO';
        }
        $tpl->REGISTROJUNTA = $Linha[25];
        if ($Linha[26] != '') {
            $tpl->DATAREGSTRO = substr($Linha[26], 8, 2).'/'.substr($Linha[26], 5, 2).'/'.substr($Linha[26], 0, 4);
        } else {
            $tpl->DATAREGSTRO = '';
        }

        // Variáveis Formulário B #
        $InscEstadual = $Linha[27];
        $tpl->InscEstadual = $InscEstadual;
        if ($InscEstadual == null) {
            $tpl->InscEstadual = 'NÃO INFORMADO';
        }

        $InscMercantil = $Linha[28];
        $tpl->InscMercantil = $InscMercantil;
        if ($InscMercantil == null) {
            $tpl->InscMercantil = 'NÃO INFORMADO';
        }

        $InscOMunic = $Linha[29];
        $tpl->InscOMunic = $InscOMunic;
        if ($InscOMunic == null) {
            $tpl->InscOMunic = '-';
        }

        // Variáveis Formulário C #
        $CapSocial = converte_valor($Linha[30]);
        $CapIntegralizado = converte_valor($Linha[31]);
        $Patrimonio = converte_valor($Linha[32]);
        $IndLiqCorrente = converte_valor($Linha[33]);
        $IndLiqGeral = converte_valor($Linha[34]);
        $IndEndividamento = converte_valor($Linha[46]);
        $IndSolvencia = converte_valor($Linha[47]);

        if ($Linha[35] != '') {
            $DataUltBalanco = substr($Linha[35], 8, 2).'/'.substr($Linha[35], 5, 2).'/'.substr($Linha[35], 0, 4);
            $DataNovaUltBalanco = $Linha[35]; // data sem formatação
        }

        if ($Linha[36] != '') {
            $DataCertidaoNeg = substr($Linha[36], 8, 2).'/'.substr($Linha[36], 5, 2).'/'.substr($Linha[36], 0, 4);
            $DataNovaCertidaoNeg = $Linha[36]; // data sem formatção
        }

        if ($Linha[50] != '') {
            $DataContratoEstatuto = substr($Linha[50], 8, 2).'/'.substr($Linha[50], 5, 2).'/'.substr($Linha[50], 0, 4);
        }

        // Variáveis Formulário D #
        $tpl->NomeEntidade = $Linha[37];
        if ($Linha[37] == null) {
            $tpl->NomeEntidade = 'NÃO INFORMADO';
        }
        $tpl->RegistroEntidade = $Linha[38];
        if ($Linha[38] == null) {
            $tpl->RegistroEntidade = 'NÃO INFORMADO';
        }
        $tpl->TecnicoEntidade = $Linha[39];
        if ($Linha[39] == null) {
            $tpl->TecnicoEntidade = 'NÃO INFORMADO';
        }
        if ($Linha[40] != '') {
            $DataVigencia = substr($Linha[40], 8, 2).'/'.substr($Linha[40], 5, 2).'/'.substr($Linha[40], 0, 4);
            $tpl->DataVigencia = $DataVigencia;
        } else {
            $tpl->DataVigencia = 'NÃO INFORMADO';
        }
        $tpl->DataInscricao = substr($Linha[41], 8, 2).'/'.substr($Linha[41], 5, 2).'/'.substr($Linha[41], 0, 4);
        $Cumprimento = $Linha[42];
        $tpl->ComissaoResp = $Linha[43];
        if ($Linha[44] != '') {
            $tpl->DataAnaliseDoc = substr($Linha[44], 8, 2).'/'.substr($Linha[44], 5, 2).'/'.substr($Linha[44], 0, 4);
        } else {
            $tpl->DataAnaliseDoc = '';
        }
        $TipoHabilitacao = $Linha[49];
        $tpl->CUMPRIMENTOINC = ' ';

        if ($TipoHabilitacao == 'D') {
            $tpl->HABILITACAO = 'COMPRA DIRETA';
        } elseif ($TipoHabilitacao == 'L') {
            $tpl->HABILITACAO = 'LICITAÇÃO';
        } elseif ($TipoHabilitacao == 'E') {
            $tpl->HABILITACAO = 'MÓDULO DE ESTOQUES';
        }
    }

    // Pega os Dados da Tabela de Situação #
    $sql = 'SELECT A.DFORSISITU, B.CFORTSCODI, A.EFORSIMOTI, A.DFORSIEXPI, A.TFORSIULAT ';
    $sql .= '  FROM SFPC.TBFORNSITUACAO A, SFPC.TBFORNECEDORTIPOSITUACAO B ';
    $sql .= " WHERE A.AFORCRSEQU = $Sequencial ";
    $sql .= '   AND A.CFORTSCODI = B.CFORTSCODI ';
    $sql .= ' ORDER BY A.TFORSIULAT DESC';

    $result = $db->query($sql);
    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
    } else {
        $Linha = $result->fetchRow();

        // $DataSituacao = "";
        // if ($Linha[0] != "") {
        // $DataSituacao = substr($Linha[0], 8, 2) . "/" . substr($Linha[0], 5, 2) . "/" . substr($Linha[0], 0, 4);
        // }
        $Situacao = 0;
        if (isset($Linha[1])) {
            $Situacao = $Linha[1];
        }
        if ($Situacao == 5) {
            $Url = 'CadGestaoFornecedorExcluido.php?Programa='.urlencode('ConsAcompFornecedor')."&Sequencial=$Sequencial";

            if (!in_array($Url, $_SESSION['GetUrl'])) {
                $_SESSION['GetUrl'][] = $Url;
            }

            header('Location: '.$Url);
            exit();
        }
        // $Motivo = strtoupper2($Linha[2]);
        if ($Linha[3] != '') {
            $DataSuspensao = substr($Linha[3], 8, 2).'/'.substr($Linha[3], 5, 2).'/'.substr($Linha[3], 0, 4);
            $tpl->DATASUSPENSAO = '
                            <tr>
                                <td colspan="4" style="text-align: left"> Data de Geração de CHF </td>
                                <td colspan="8" style="text-align: left"> $DataSuspensao </td>
                            </tr>';
        } else {
            $DataSuspensao = '';
            $tpl->DATASUSPENSAO = $DataSuspensao;
        }
    }

    $Cadastrado = 'HABILITADO';

    if ($TipoHabilitacao == 'L') {
        // Verifica a Validação das Certidões do Fornecedor #
        $sql = 'SELECT A.CTIPCECODI, A.ETIPCEDESC, B.DFORCEVALI ';
        $sql .= '  FROM SFPC.TBTIPOCERTIDAO A, SFPC.TBFORNECEDORCERTIDAO B ';
        $sql .= " WHERE A.CTIPCECODI = B.CTIPCECODI AND A.FTIPCEOBRI = 'S' ";
        $sql .= "   AND B.AFORCRSEQU = $Sequencial";
        $sql .= ' ORDER BY B.DFORCEVALI';

        $tpl->CUMPRIMENTOINC = '
           <tr>
                <td colspan="4" style="text-align: left">Cumprimento Inc. XXXIII Art. 7º Cons. Fed.</td>
                <td colspan="8" style="text-align: left">SIM</td>
            </tr>';

        $result = $db->query($sql);
        if (PEAR::isError($result)) {
            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
        } else {
            $Rows = $result->numRows();
            for ($i = 0; $i <= $Rows; ++$i) {
                $DataHoje = date('Y-m-d');
                $Linha = $result->fetchRow();
                if ($i == 0) {
                    if ($Linha[2] < $DataHoje) {
                        $Cadastrado = 'INABILITADO';
                        $InabilitacaoCertidaoObrigatoria = true;
                    } else {
                        $Cadastrado = 'HABILITADO';
                    }
                }
            }
        }

        // Everton - correção de erro
        // $dataHoje = new DateTime();
        // Verifica também se a data de balanço anual está no prazo #
        // só compara se Data Ultimo Ballanço for diferento de nulo

        if (!empty($DataNovaUltBalanco)) {
            if ($DataNovaUltBalanco < prazoUltimoBalanço()->format('Y-m-d')) {
                $Cadastrado = 'INABILITADO';
                $InabilitacaoUltBalanco = true;
            }
        }

        if ($DataNovaCertidaoNeg < prazoCertidaoNegDeFalencia()->format('Y-m-d')) {
            $Cadastrado = 'INABILITADO';
            $InabilitacaoCertidaoNeg = true;
        }
    } else {
        $Cadastrado = 'HABILITADO';
    }

    // if ($Situacao > 0) {
    // Mostra Tabela de Situação #
    $sql = 'SELECT EFORTSDESC FROM SFPC.TBFORNECEDORTIPOSITUACAO';
    $sql .= ' WHERE CFORTSCODI = '.$Situacao.'';
    $result = $db->query($sql);
    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
    } else {
        $situacao = $result->fetchRow();
        $DescSituacao = $situacao[0];
        $tpl->DESCSITUACAO = $DescSituacao;
        if ($Situacao == 1) {
            if ($TipoHabilitacao == 'L') {
                $DescSituacao = $DescSituacao.' '.$Cadastrado;
                $tpl->DESCSITUACAO = $DescSituacao;
            } else {
                $DescSituacao = $DescSituacao;
                $tpl->DESCSITUACAO = $DescSituacao;
            }
        }
    }
    // }

    // Verifica se já Existe Data de CHF #
    $sql = 'SELECT DFORCHGERA,DFORCHVALI FROM SFPC.TBFORNECEDORCHF ';
    $sql .= " WHERE AFORCRSEQU = $Sequencial ";
    $result = $db->query($sql);
    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
    } else {
        $Rows = $result->numRows();
        if ($Rows != 0) {
            $Linha = $result->fetchRow();
            $DataGeracaoCHF = substr($Linha[0], 8, 2).'/'.substr($Linha[0], 5, 2).'/'.substr($Linha[0], 0, 4);
            $DataValidadeCHF = substr($Linha[1], 8, 2).'/'.substr($Linha[1], 5, 2).'/'.substr($Linha[1], 0, 4);
            $tpl->DATAVALIDADECHF = $DataValidadeCHF;
            $tpl->DATAGERACAOCHF = $DataGeracaoCHF;
        } else {
            $DataGeracaoCHF = '-';
            $DataValidadeCHF = '-';
            $tpl->DATAVALIDADECHF = $DataValidadeCHF;
            $tpl->DATAGERACAOCHF = $DataGeracaoCHF;
        }
    }

    // Busca os Dados da Tabela de Conta Bancária de acordo com o sequencial do Fornecedor #
    $sql = 'SELECT CFORCBBANC, CFORCBAGEN, CFORCBCONT, TFORCBULAT ';
    $sql .= '  FROM SFPC.TBFORNCONTABANCARIA ';
    $sql .= " WHERE AFORCRSEQU = $Sequencial ";
    $sql .= ' ORDER BY TFORCBULAT';
    $result = $db->query($sql);
    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
    } else {
        $Rows = $result->numRows();
        for ($i = 0; $i < $Rows; ++$i) {
            $Linha = $result->fetchRow();
            if ($i == 0) {
                $tpl->Banco1 = $Linha[0];
                $tpl->Agencia1 = $Linha[1];
                $tpl->ContaCorrente1 = $Linha[2];
            } else {
                $tpl->Banco2 = $Linha[0];
                $tpl->Agencia2 = $Linha[1];
                $tpl->ContaCorrente2 = $Linha[2];
                // $tpl->CONTA = "NÃO INFORMADO";

                $tpl->block('CONTA2');
            }
        }
    }
    // Verifica se o Fornecedor está Regular na Prefeitura #
    /*if ($Irregularidade == null) {
        if ($CNPJ != null) {
            $TipoDoc = 1;
            $CPF_CNPJ = $CNPJ;
        } elseif ($CPF != null) {
            $TipoDoc = 2;
            $CPF_CNPJ = $CPF;
        }

        $NomePrograma = urlencode('ConsAcompFornecedor.php');
        $Url = "fornecedores/RotDebitoCredorConsulta.php?NomePrograma=$NomePrograma&TipoDoc=$TipoDoc&CPF_CNPJ=$CPF_CNPJ&Sequencial=$Sequencial";

        if (!in_array($Url, $_SESSION['GetUrl'])) {
            $_SESSION['GetUrl'][] = $Url;
        }
        Redireciona($Url);
        exit();
    }*/
}

// Aviso para Fornecedor de estoque #
if ($TipoHabilitacao == 'E') {
    $Mens = 1;
    $Tipo = 2;
    $Mensagem = 'FORNECEDOR DE MÓDULO DE ESTOQUES DEVE SER ALTERADO PARA TIPO (COMPRA DIRETA) ou (LICITAÇÃO) ';
}
if ($TipoHabilitacao == 'L' && ($Situacao == 1)) {
    // Mensagem para Fornecedor Inabilitado #
    $inabilitarFornecedor = false; // imprimir mensagem de fornecedor INABILITADO?

    if ($Cadastrado == 'INABILITADO' and $InabilitacaoCertidaoObrigatoria) {
        $Mens = 1;
        $Tipo = 1;

        if (!$inabilitarFornecedor) {
            $inabilitarFornecedor = true;
            $Mensagem = 'FORNECEDOR INABILITADO ';
        }

        if ($Irregularidade == 'S') {
            if ($Cadastrado == 'INABILITADO') {
                $Mensagem .= 'CERTIDÃO(ÕES) FORA DO PRAZO DE VALIDADE E COM SITUAÇÃO IRREGULAR NA PREFEITURA';
            } else {
                $Mensagem .= 'SITUAÇÃO IRREGULAR NA PREFEITURA';
            }
        } else {
            $Mensagem .= 'CERTIDÃO(ÕES) FORA DO PRAZO DE VALIDADE';
        }
    }
    if ($Cadastrado == 'INABILITADO' and $InabilitacaoUltBalanco) {
        if ($Mens == 1) {
            $Mensagem .= ', ';
        }
        $Mens = 1;
        $Tipo = 1;
        if (!$inabilitarFornecedor) {
            $inabilitarFornecedor = true;
            $Mensagem = 'FORNECEDOR INABILITADO ';
        }
        $Mensagem .= 'DATA DE VALIDADE DO BALANÇO EXPIRADA'/*(data menor que ".prazoUltimoBalanço()->format('d/m/Y').")"*/;
    }

    if ($Cadastrado == 'INABILITADO' and $InabilitacaoCertidaoNeg) {
        if ($Mens == 1) {
            $Mensagem .= ', ';
        }
        $Mens = 1;
        $Tipo = 1;
        if (!$inabilitarFornecedor) {
            $inabilitarFornecedor = true;
            $Mensagem = 'FORNECEDOR INABILITADO ';
        }
        $Mensagem .= 'DATA CERTIDÃO NEGATIVA EXPIRADA '/*(data menor que ".prazoCertidaoNegDeFalencia()->format('d/m/Y').")"*/;
    }

    if (!empty($MicroEmpresa) and empty($dt_val_bal)) {
        if ($Mens == 1) {
            $Mensagem .= ', ';
        }
        $Mens = 1;
        $Tipo = 1;
        $Mensagem .= 'CHF SIMPLIFICADO SEM DEMONSTRAÇÕES CONTÁBEIS';
    }

    // if ($Mens) {
    $tpl->exibirMensagemFeedback($Mensagem, $Tipo);
    // }
}

if ($CNPJ != 0 and $TipoHabilitacao == 'L') {
    $tpl->CAPITAL_SOCIAL = $CapSocial;
    $tpl->CAPITAL_INTEGRALIZADO = ($CapIntegralizado != '') ? $CapIntegralizado : 'NÃO INFORMADO';
    $tpl->PATRIMONIO_LIQUIDO = $Patrimonio;
    $tpl->INDICE_LIQUIDEZ_CORRENTE = ($IndLiqCorrente != '') ? $IndLiqCorrente : 'NÃO INFORMADO';

    $tpl->INDICE_LIQUIDEZ_GERAL = ($IndLiqGeral != '') ? $IndLiqGeral : 'NÃO INFORMADO';
    $tpl->INDICE_ENDIVIDAMENTO = ($IndEndividamento != '') ? $IndEndividamento : 'NÃO INFORMADO';
    $tpl->INDICE_SOLVENCIA = ($IndSolvencia != '') ? $IndSolvencia : 'NÃO INFORMADO';
    $tpl->DATA_VALIDADE_BALANCO = ($DataUltBalanco != '') ? $DataUltBalanco : 'NÃO INFORMADO';
    $tpl->DATA_CERTIDAO_NEGATIVA_FALENCIA_CONCORDATA = ($DataCertidaoNeg != '') ? $DataCertidaoNeg : 'NÃO INFORMADO';
    $tpl->DATA_ULTIMA_ALTERACAO_CONTRATO = ($DataContratoEstatuto != '') ? $DataContratoEstatuto : 'NÃO INFORMADO';
}
// query dos dados dos sócios - se houver
// if( $CNPJ <> 0 ){
// Pega os Dados dos sócios #
$sql = 'SELECT
         asoforcada, nsofornome
         FROM SFPC.TBsociofornecedor
         WHERE aforcrsequ = '.$Sequencial.'';

$res = $db->query($sql);
if (PEAR::isError($res)) {
    EmailErroSQL('Erro ao obter sócios de fornecedor', __FILE__, __LINE__, 'Erro ao obter sócios de fornecedor', $sql, $res);
} else {
    $Rows = $res->numRows();
    if ($Rows == 0) {
        $tpl->DADOSOCIOS = '<tr> <td colspan="12" style="text-align: center"> Nenhum Sócio Cadastrado </td></tr>';
    } else {
        $tpl->DADOSOCIOS = '<tr>
                            <td colspan="6" style="text-align: left"><strong> Nome </strong></td>
                            <td colspan="6" style="text-align: left"><strong> CPF/CNPJ </strong></td>
                            </tr>';
        for ($itr = 0; $itr < $Rows; ++$itr) {
            $Linha = $res->fetchRow();
            $tpl->socioCPF = $Linha[0];
            $tpl->socioNome = $Linha[1];
            $tpl->block('BLOCKSOCIOS');
        }
    }
}
// }

// query de ocorrências
$sql = 'SELECT A.CFORTOCODI, A.EFOROCDETA, A.DFOROCDATA, B.EFORTODESC ';
$sql .= '  FROM SFPC.TBFORNECEDOROCORRENCIA A, SFPC.TBFORNTIPOOCORRENCIA B';
$sql .= " WHERE A.CFORTOCODI = B.CFORTOCODI AND A.AFORCRSEQU = $Sequencial ORDER BY 3,1";
$res = $db->query($sql);
if (PEAR::isError($res)) {
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
} else {
    $Rows = $res->numRows();
    if ($Rows == 0) {
        $tpl->OCORRENCIAS = '<tr> <td colspan="12" style="text-align: center"> Nenhuma Ocorrência Informada </td></tr>';
    } else {
        $tpl->OCORRENCIAS = '';
        for ($i = 0; $i < $Rows; ++$i) {
            $Linha = $res->fetchRow();
            // $Codigo = $Linha[0];
            $tpl->TIPOCORRENCIA = $Linha[1];
            $tpl->DATAOCORRENCIA = $Linha[2];
            $tpl->DETALHAMENTOOCORRENCIA = $Linha[3];
            $tpl->block('GRUPOOCORRENCIAS');
        }
    }
}

// Query certidoes fiscais obrigatórias
$sql = "SELECT CTIPCECODI, ETIPCEDESC FROM SFPC.TBTIPOCERTIDAO WHERE FTIPCEOBRI = 'S' ORDER BY 1";
$res = $db->query($sql);
if (PEAR::isError($res)) {
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
} else {
    $Rows = $res->numRows();
    for ($i = 0; $i < $Rows; ++$i) {
        $Linha = $res->fetchRow();
        $DescricaoOb = substr($Linha[1], 0, 75);
        $CertidaoOb = $Linha[0];
        $tpl->DESCRICAOCERTOB = $DescricaoOb;

        // Verifica se existem certidões obrigatórias cadastradas para o Fornecedor #
        $sqlData = 'SELECT DFORCEVALI FROM SFPC.TBFORNECEDORCERTIDAO ';
        $sqlData .= " WHERE AFORCRSEQU = $Sequencial AND CTIPCECODI = $CertidaoOb";

        $resData = $db->query($sqlData);
        if (PEAR::isError($resData)) {
            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
        } else {
            $DataCertidaoOb = array();
            $ob = 0;
            $LinhaData = $resData->fetchRow();

            if ($LinhaData[0] != 0) {
                $DataCertidaoOb[$ob - 1] = substr($LinhaData[0], 8, 2).'/'.substr($LinhaData[0], 5, 2).'/'.substr($LinhaData[0], 0, 4);
                $tpl->DATACERTOB = $DataCertidaoOb[$ob - 1];
            } else {
                $DataCertidaoOb[$ob - 1] = null;
                $tpl->DATACERTOB = 'NÃO INFORMADO';
            }
        }

        // monta o bloco
        $tpl->BLOCK('CERTIDOESOBRIGATORIAS');
    }
}

// Verifica se existem certidões complementares cadastradas para o Fornecedor #
$sql = 'SELECT A.DFORCEVALI, B.CTIPCECODI, B.ETIPCEDESC  ';
$sql .= '  FROM SFPC.TBFORNECEDORCERTIDAO A, SFPC.TBTIPOCERTIDAO B ';
$sql .= " WHERE A.AFORCRSEQU = $Sequencial AND A.CTIPCECODI = B.CTIPCECODI";
$sql .= "   AND B.FTIPCEOBRI = 'N' ORDER BY B.CTIPCECODI";
$res = $db->query($sql);
if (PEAR::isError($res)) {
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
} else {
    $Rows = $res->numRows();
    if ($Rows != 0) {
        $tpl->CERTNAOOBRIGAT = ' ';

        for ($i = 0; $i < $Rows; ++$i) {
            $Linha = $res->fetchRow();
            $DataCertidaoOp = array();
            $DescricaoOp = substr($Linha[2], 0, 75);
            // $CertidaoOpCodigo = $Linha[1];
            // $CertidaoComplementar[$i] = $Linha[1];
            $DataCertidaoOp[$i] = substr($Linha[0], 8, 2).'/'.substr($Linha[0], 5, 2).'/'.substr($Linha[0], 0, 4);

            $tpl->DESCRICAOCERTNAOOB = $DescricaoOp;
            $tpl->DATACERTNAOOB = $DataCertidaoOp[$i];

            $tpl->block('CERTNAOOBRIGATORIA');
        }
    } else {
        $tpl->CERTNAOOBRIGAT = '<tr>
                                <td colspan="12" class="text-center">NÃO INFORMADO</td>
                                </tr>';
    }
}

// Mostra as autorizações específicas do Inscrito cadatradas #
$sql = 'SELECT AFORAENUMA, NFORAENOMA, DFORAEVIGE FROM SFPC.TBFORNAUTORIZACAOESPECIFICA ';
$sql .= " WHERE AFORCRSEQU = $Sequencial";
$res = $db->query($sql);
if (PEAR::isError($res)) {
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
} else {
    $Rows = $res->numRows();
    if ($Rows != 0) {
        $tpl->AUTORIZACAOESPECIFICA = ' ';
        for ($i = 0; $i < $Rows; ++$i) {
            $Linha = $res->fetchRow();
            $RegistroAutor = $Linha[0];
            $tpl->REGISTROAUTOR = $RegistroAutor;
            if ($Linha[0] == null) {
                $tpl->REGISTROAUTOR = 'NÃO INFORMADO';
            }
            $NomeAutor = $Linha[1];
            $tpl->NOMEAUTOR = $NomeAutor;
            if ($Linha[1] == null) {
                $tpl->NOMEAUTOR = 'NÃO INFORMADO';
            }
            $DataVigAutor = substr($Linha[2], 8, 2).'/'.substr($Linha[2], 5, 2).'/'.substr($Linha[2], 0, 4);
            $tpl->DATAVIGAUTOR = $DataVigAutor;
            if ($DataVigAutor == null) {
                $tpl->DATAVIGAUTOR = 'NÃO INFORMADO';
            }
            $tpl->block('AUTORIZACAOBLOCO');
        }
    } else {
        $tpl->AUTORIZACAOESPECIFICA = 'NÂO INFORMADO';
    }
}

// Mostra os grupos de materiais já cadastrados do Fornecedor #
$sql = 'SELECT A.AFORCRSEQU, B.CGRUMSCODI, B.EGRUMSDESC ';
$sql .= '  FROM SFPC.TBGRUPOFORNECEDOR A, SFPC.TBGRUPOMATERIALSERVICO B ';
$sql .= " WHERE A.AFORCRSEQU = $Sequencial AND A.CGRUMSCODI = B.CGRUMSCODI ";
$sql .= "   AND B.FGRUMSTIPO = 'M' ORDER BY 1,3";
$res = $db->query($sql);
if (PEAR::isError($res)) {
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
} else {
    $Rows = $res->numRows();
    if ($Rows != 0) {
        $tpl->MATERIAISHEAD = "<tr>
                                  <td colspan='12' class='text-center'><strong>MATERIAIS</strong></td>
                                  </tr>";

        $DescricaoGrupoAntes = '';
        $Materiais = array();
        for ($i = 0; $i < $Rows; ++$i) {
            $Linha = $res->fetchRow();
            $DescricaoGrupo = substr($Linha[2], 0, 75);

            $Materiais[$i] = 'M#'.$Linha[1];
            if ($DescricaoGrupoAntes != $DescricaoGrupo) {
                $tpl->VALOR_MATERIAL = $DescricaoGrupo;
            }

            $DescricaoGrupoAntes = $DescricaoGrupo;
            $tpl->block('VALOR_LOOP_MATERIAL');
        }
    } else {
        $tpl->MATERIAISHEAD = ' ';
    }
}
// Mostra os grupos de serviços já cadastrados do Fornecedor #
$sql = 'SELECT A.AFORCRSEQU, B.CGRUMSCODI, B.EGRUMSDESC ';
$sql .= '  FROM SFPC.TBGRUPOFORNECEDOR A, SFPC.TBGRUPOMATERIALSERVICO B  ';
$sql .= " WHERE A.AFORCRSEQU = $Sequencial AND A.CGRUMSCODI = B.CGRUMSCODI ";
$sql .= "   AND B.FGRUMSTIPO = 'S' ORDER BY 1,3";
$res = $db->query($sql);
if (PEAR::isError($res)) {
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
} else {
    $Rows = $res->numRows();
    if ($Rows != 0) {
        // Mostra os grupos de serviços cadastrados #
        $tpl->SERVICOSHEAD = "<tr>
                                    <td colspan='12' class='text-center'><strong>SERVIÇOS</strong></td>
                                </tr>";

        $DescricaoGrupoAntes = '';
        $Servicos = array();
        for ($i = 0; $i < $Rows; ++$i) {
            $Linha = $res->fetchRow();
            $DescricaoGrupo = substr($Linha[2], 0, 75);
            $Servicos[$i] = 'S#'.$Linha[1];

            if ($DescricaoGrupo != $DescricaoGrupoAntes) {
                $tpl->VALOR_SERVICO = $DescricaoGrupo;
            }

            $DescricaoGrupoAntes = $DescricaoGrupo;
            $tpl->block('VALOR_LOOP_SERVICO');
        }
    }
}

        // carrega arquivos cadastrados
        $db = Conexao();
        $sql = "  SELECT doc.cfdocusequ, doc.aprefosequ, doc.aforcrsequ, doc.afdocuanoa, 
                    doc.cfdoctcodi, doc.efdocunome, doc.ifdocuarqu, doc.ffdocuforn, 
                    doc.tfdocuanex, doc.ffdocusitu, doc.cusupocodi, doc.tfdoctulat,
                    (SELECT h.cfdocscodi
                    FROM sfpc.tbfornecedordocumentohistorico h
                    where h.cfdocusequ = doc.cfdocusequ order by h.tfdochulat desc limit 1) as situacao, 
                    (SELECT h.efdochobse
                    FROM sfpc.tbfornecedordocumentohistorico h
                    where h.cfdocusequ = doc.cfdocusequ order by h.tfdochulat desc limit 1) as observacao, 
    
                    t.efdoctdesc, 
    
                    (SELECT h.cusupocodi
                    FROM sfpc.tbfornecedordocumentohistorico h
                    where h.cfdocusequ = doc.cfdocusequ order by h.tfdochulat desc limit 1) as usuarioUltimaAlt, 
                    (SELECT u.eusuporesp
                    FROM sfpc.tbfornecedordocumentohistorico h
                    join sfpc.tbusuarioportal u on h.cusupocodi = u.cusupocodi
                    where h.cfdocusequ = doc.cfdocusequ order by h.tfdochulat desc limit 1) as nomeUsuUltimaAlt, 
                    
                    (SELECT h.tfdochulat
                        FROM sfpc.tbfornecedordocumentohistorico h
                        where h.cfdocusequ = doc.cfdocusequ order by h.tfdochulat desc limit 1) as datahoraUltimaAlt ,
                    (SELECT s.efdocsdesc
                        FROM sfpc.tbfornecedordocumentosituacao s
                        where s.cfdocscodi = (SELECT h.cfdocscodi
                        FROM sfpc.tbfornecedordocumentohistorico h
                        where h.cfdocusequ = doc.cfdocusequ order by h.tfdochulat desc limit 1)) as situacao_nome,
                    (SELECT u.eusuporesp
                        FROM sfpc.tbfornecedordocumentohistorico h
                        join sfpc.tbusuarioportal u on h.cusupocodi = u.cusupocodi
                        where h.cfdocusequ = doc.cfdocusequ order by h.tfdochulat asc limit 1) as nomeUsuAnex 
                    
                FROM sfpc.tbfornecedordocumento doc
                join sfpc.tbfornecedordocumentotipo t ON t.cfdoctcodi = doc.cfdoctcodi
                WHERE aforcrsequ = " . $Sequencial;
                if($PreInscricao){
                    $sql .= " OR aprefosequ = " . $PreInscricao;
                }
                $sql .= " AND ffdocusitu = 'A' order by tfdoctulat DESC";
        
    
                //print_r($sql);
                //die();
        $result = $db->query($sql);
        if (db :: isError($result)) {
            ExibeErroBD($_SESSION['ErroPrograma'] . "\nLinha: " . __LINE__ . "\nSql: $sql");
        } else {
            
            //die('ENtrou aqui na hora de atualizar...por isso não funcionou...');
            unset($_SESSION['Arquivos_Upload']);
            //$resultado = $result->fetchRow();
            while ($linha = $result->fetchRow()) {
    
                    //verifica se quem cadastrou foi PCR ou o próprio fornecedor
                    $nomeUsuAnex = '';
                    $nomeUsuUltAlt = '';
                    
                    if($linha[7] == 'S'){
                        
                        if ($CPF_CNPJ != "") {
                            if (strlen($CPF_CNPJ) == 14) {
                                $nomeUsuAnex = substr($CPF_CNPJ,0,2).".".substr($CPF_CNPJ,2,3).".".substr($CPF_CNPJ,5,3)."/".substr($CPF_CNPJ,8,4)."-".substr($CPF_CNPJ,12,2);
                            } else {
                                $nomeUsuAnex = substr($CPF_CNPJ,0,3).".".substr($CPF_CNPJ,3,3).".".substr($CPF_CNPJ,6,3)."-".substr($CPF_CNPJ,9,2);
                            }
                        }


                        //Usuário que fez a última alteração
                        if($linha[15]>0){
                            $nomeUsuUltAlt = 'PCR - '.$linha[16];
                        }else{
                            $nomeUsuUltAlt = $nomeUsuAnex;
                        }
    
                    }else{
                        $nomeUsuAnex = $linha[19];
    
                        //Usuário que fez a última alteração
                        $nomeUsuUltAlt = 'PCR - '.$linha[16];
    
                    }
    
                $_SESSION['Arquivos_Upload']['nome'][] = $linha[5];
                $_SESSION['Arquivos_Upload']['situacao'][] = 'existente'; // situacao pode ser: novo, existente, cancelado e excluido
                $_SESSION['Arquivos_Upload']['codigo'][] = $linha[0]; // como é um arquivo novo, ainda nao possui código
                $_SESSION['Arquivos_Upload']['tipoCod'][] = $linha[4]; 
                $_SESSION['Arquivos_Upload']['tipoDocumentoDesc'][] = $linha[14]; 
                $_SESSION['Arquivos_Upload']['observacao'][] = $linha[13]; 
                $_SESSION['Arquivos_Upload']['conteudo'][] = $linha[6]; 
                $_SESSION['Arquivos_Upload']['anoAnex'][] = $linha[3];
                $_SESSION['Arquivos_Upload']['dataHora'][] = formatarDataHora($linha[8]); 
    
                $_SESSION['Arquivos_Upload']['codUsuarioUltAlt'][] = $linha[15];
                $_SESSION['Arquivos_Upload']['usuarioUltAlt'][] = $nomeUsuUltAlt;
                $_SESSION['Arquivos_Upload']['usuarioAnex'][] = $nomeUsuAnex;
                $_SESSION['Arquivos_Upload']['externo'][] = $linha[7];
    
                $_SESSION['Arquivos_Upload']['dataHoraUltAlt'][] = formatarDataHora($linha[17]); 
                $_SESSION['Arquivos_Upload']['situacaoHist'][] = $linha[12]; 
                $_SESSION['Arquivos_Upload']['situacaoDesc'][] = $linha[18]; 
    
            }
        }

function configurarBlocoDocumentos($tpl) {

    // Tipos de documento
    $htmlTipoDoc = '';

    /*$db = Conexao();																																			
    $sql = "SELECT CFDOCTCODI, EFDOCTDESC FROM 
            SFPC.TBFORNECEDORDOCUMENTOTIPO
            WHERE FFDOCTSITU = 'A' ORDER BY afdoctorde, EFDOCTDESC";
    $res = $db->query($sql);

    if (PEAR::isError($res)) {
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
    } else {
        
        while ($tipoDoc = $res->fetchRow()) {
            
            if($tipoDoc[0] == $_SESSION['tipoDoc'] ){
                $htmlTipoDoc .= '<option value="'.$tipoDoc[0].'" selected>'.$tipoDoc[1].'</option>';
            }else{
                $htmlTipoDoc .= '<option value="'.$tipoDoc[0].'">'.$tipoDoc[1].'</option>';
            }	

        }
    }*/

    //Documentos Anexados até o momento
    $htmlDoc = '';

    $qtd_anexo = 0;

        for ($j = 0; $j < count($_SESSION['Arquivos_Upload']['conteudo']) ; ++ $j) {
            if ($_SESSION['Arquivos_Upload']['situacao'][$j] == 'novo' ||  $_SESSION['Arquivos_Upload']['situacao'][$j] == 'existente') {
                $qtd_anexo++;
            }
        }

    if( $qtd_anexo > 0){
        $htmlDoc .= '<table border="1" cellpadding="3" cellspacing="0" bgcolor="#bfdaf2" bordercolor="#75ADE6" summary="" class="textonormal" width="100%">

        <tr>
            <td bgcolor="#bfdaf2" align="center"><b> Tipo do documento</b></td>
            <td bgcolor="#bfdaf2" align="center"><b> Nome</b></td>
            <td bgcolor="#bfdaf2" align="center"><b> Responsável anexação</b></td>
            <td bgcolor="#bfdaf2" align="center"><b> Data/Hora Anexação</b></td>
            <td bgcolor="#bfdaf2" align="center"><b> Situação</b></td>
            <td bgcolor="#bfdaf2" align="center"><b> Observação</b></td>
        </tr> ';
        


            
        $DTotal = count($_SESSION['Arquivos_Upload']['conteudo']);
        for ($Dcont = 0; $Dcont < $DTotal; ++ $Dcont) {
            if ($_SESSION['Arquivos_Upload']['situacao'][$Dcont] == 'novo' || $_SESSION['Arquivos_Upload']['situacao'][$Dcont] == 'existente') {
                $htmlDoc .= '<tr>
                    <td class="textonormal" bgcolor="#ffffff">
                        '.$_SESSION['Arquivos_Upload']['tipoDocumentoDesc'][$Dcont].'
                    </td>
                    <td class="textonormal" bgcolor="#ffffff">
                    <a href="javascript: baixarArquivo('.$Dcont.');">'.$_SESSION['Arquivos_Upload']['nome'][$Dcont].'</a>
                    </td>
                    <td class="textonormal" bgcolor="#ffffff" align="center">';


                if($_SESSION['Arquivos_Upload']['situacao'][$Dcont]=='existente'){
                    $htmlDoc .= $_SESSION['Arquivos_Upload']['usuarioAnex'][$Dcont]; 
                }else{
                    $htmlDoc .= 'PCR - '.$nome_usuario;
                }

                $htmlDoc .=  '</td>
                    <td class="textonormal" bgcolor="#ffffff">
                        '.$_SESSION['Arquivos_Upload']['dataHora'][$Dcont].'
                    </td>
                    <td class="textonormal" bgcolor="#ffffff">
                    '.$_SESSION['Arquivos_Upload']['situacaoDesc'][$Dcont].'
                    </td>
                    <td class="textonormal" bgcolor="#ffffff">
                        '.$_SESSION['Arquivos_Upload']['observacao'][$Dcont].'
                    </td>
                    </tr>';


            }
        }

        $htmlDoc .= '</table>';

    }

    $tpl->htmlDoc               = $htmlDoc;

}
//Insere o bloco de documentos
configurarBlocoDocumentos($tpl);
$tpl->block("bloco_documentos");

$tpl->show();
$db->disconnect();