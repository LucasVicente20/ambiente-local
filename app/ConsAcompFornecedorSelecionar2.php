<?php

//require_once ("../funcoes.php");
require_once dirname(__FILE__) . "/TemplateAppPadrao.php";

# Acesso ao arquivo de funções #
require_once dirname(__FILE__) . "/../fornecedores/funcoesFornecedores.php";
//
// session_start();

HelperPitang::setErrors();

function proccessPrincipal()
{
    $tpl = new TemplateAppPadrao("templates/ConsAcompFornecedorSelecionar2.html", "ConsAcompFornecedor");

    # Variáveis com o global off #
    if ($_SERVER['REQUEST_METHOD']  == "GET") {
        $Sequencial     = $_GET['Sequencial'];
        $Irregularidade = $_GET['Irregularidade'];
    } else {
        //$Botao      = $_POST['Botao'];
        $Sequencial = $_POST['Sequencial'];
        $Mensagem   = $_POST['Mensagem'];
    }

    $dbase = Conexao();

    $dt_val_bal = getDataUltimoBalanco($dbase, $Sequencial);

    $Mens     = 0;
    $Mensagem = "";

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

    $result = $dbase->query($sql);
    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
    } else {
        $Linha = $result->fetchRow();

        // Variáveis Formulário A #
        $Sequencial = $Linha [0];
        //$PreInscricao = $Linha [1];
        $CNPJ = $Linha [2];
        $CPF = $Linha [3];
        $MicroEmpresa = $Linha [45];
        $Identidade = $Linha [4];
        $OrgaoEmissorUF = $Linha [5];
        $RazaoSocial = $Linha [6];
        $NomeFantasia = $Linha [7];
        if ($Linha [8] != "") {
            $CEP = $Linha [8];
        } else {
            $CEP = $Linha [9];
        }
        $Logradouro = $Linha [10];
        $Numero = $Linha [11];
        $Complemento = $Linha [12];
        $Bairro = $Linha [13];
        $Cidade = $Linha [14];
        $UF = $Linha [15];
        $DDD = $Linha [16];
        $Telefone = $Linha [17];
        $Fax = $Linha [18];
        $Email = $Linha [19];
        $Email2 = $Linha [48];
        if ($Linha [20] != "") {
            $CPFContato = substr($Linha [20], 0, 3) . "." . substr($Linha [20], 3, 3) . "." . substr($Linha [20], 6, 3) . "-" . substr($Linha [19], 9, 2);
        }
        $NomeContato = $Linha [21];
        $CargoContato = $Linha [22];
        $DDDContato = $Linha [23];
        $TelefoneContato = $Linha [24];
        $RegistroJunta = $Linha [25];
        if ($Linha [26] != "") {
            $DataRegistro = substr($Linha [26], 8, 2) . "/" . substr($Linha [26], 5, 2) . "/" . substr($Linha [26], 0, 4);
        } else {
            $DataRegistro = "";
        }

        // Variáveis Formulário B #
        $InscEstadual = $Linha [27];
        $InscMercantil = $Linha [28];
        $InscOMunic = $Linha [29];

        // Variáveis Formulário C #
        $CapSocial = converte_valor($Linha [30]);
        $CapIntegralizado = converte_valor($Linha [31]);
        $Patrimonio = converte_valor($Linha [32]);
        $IndLiqCorrente = converte_valor($Linha [33]);
        $IndLiqGeral = converte_valor($Linha [34]);
        $IndEndividamento = converte_valor($Linha [46]);
        $IndSolvencia = converte_valor($Linha [47]);
        if ($Linha [35] != "") {
            $DataUltBalanco = substr($Linha [35], 8, 2) . "/" . substr($Linha [35], 5, 2) . "/" . substr($Linha [35], 0, 4);
            $DataNovaUltBalanco = $Linha [35]; // data sem formatação
        }
        if ($Linha [36] != "") {
            $DataCertidaoNeg = substr($Linha [36], 8, 2) . "/" . substr($Linha [36], 5, 2) . "/" . substr($Linha [36], 0, 4);
            $DataNovaCertidaoNeg = $Linha [36]; // data sem formatção
        }
        if ($Linha [50] != "") {
            $DataContratoEstatuto = substr($Linha [50], 8, 2) . "/" . substr($Linha [50], 5, 2) . "/" . substr($Linha [50], 0, 4);
        }

        // Variáveis Formulário D #
        $NomeEntidade = $Linha [37];
        $RegistroEntidade = $Linha [38];
        $TecnicoEntidade = $Linha [39];
        if ($Linha [40] != "") {
            $DataVigencia = substr($Linha [40], 8, 2) . "/" . substr($Linha [40], 5, 2) . "/" . substr($Linha [40], 0, 4);
        }
        $DataInscricao = substr($Linha [41], 8, 2) . "/" . substr($Linha [41], 5, 2) . "/" . substr($Linha [41], 0, 4);
        $Cumprimento = $Linha [42];
        $ComissaoResp = $Linha [43];
        if ($Linha [44] != "") {
            $DataAnaliseDoc = substr($Linha [44], 8, 2) . "/" . substr($Linha [44], 5, 2) . "/" . substr($Linha [44], 0, 4);
        } else {
            $DataAnaliseDoc = "";
        }
        $TipoHabilitacao = $Linha [49];
    }

    // Pega os Dados da Tabela de Situação #
    $sql = "
        SELECT A.DFORSISITU, B.CFORTSCODI, A.EFORSIMOTI, A.DFORSIEXPI, A.TFORSIULAT
        FROM SFPC.TBFORNSITUACAO A, SFPC.TBFORNECEDORTIPOSITUACAO B
        WHERE A.AFORCRSEQU = %d
            AND A.CFORTSCODI = B.CFORTSCODI
            ORDER BY A.TFORSIULAT DESC
    ";

    $result = $dbase->query($sprintf($sql, $Sequencial));

    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
    } else {
        for ($i = 0; $i < 1; $i ++) {
            $Linha = $result->fetchRow();
            if ($Linha [0] != "") {
                $DataSituacao = substr($Linha [0], 8, 2) . "/" . substr($Linha [0], 5, 2) . "/" . substr($Linha [0], 0, 4);
            } else {
                $DataSituacao = "";
            }
            $Situacao = $Linha [1];
            if ($Situacao == 5) {
                $Url = "CadGestaoFornecedorExcluido.php?Programa=" . urlencode("ConsAcompFornecedor") . "&Sequencial=$Sequencial";
                if (! in_array($Url, $_SESSION ['GetUrl'])) {
                    $_SESSION ['GetUrl'] [] = $Url;
                }
                header("location: " . $Url);
                exit();
            }
            $Motivo = strtoupper2($Linha [2]);
            if ($Linha [3] != "") {
                $DataSuspensao = substr($Linha [3], 8, 2) . "/" . substr($Linha [3], 5, 2) . "/" . substr($Linha [3], 0, 4);
            } else {
                $DataSuspensao = "";
            }
        }
    }

    $Cadastrado = "HABILITADO";

    if ($TipoHabilitacao == "L") {
        // Verifica a Validação das Certidões do Fornecedor #
        $sql = "SELECT A.CTIPCECODI, A.ETIPCEDESC, B.DFORCEVALI ";
        $sql .= "  FROM SFPC.TBTIPOCERTIDAO A, SFPC.TBFORNECEDORCERTIDAO B ";
        $sql .= " WHERE A.CTIPCECODI = B.CTIPCECODI AND A.FTIPCEOBRI = 'S' ";
        $sql .= "   AND B.AFORCRSEQU = $Sequencial";
        $sql .= " ORDER BY B.DFORCEVALI";

        $result = $dbase->query($sql);
        if (PEAR::isError($result)) {
            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
        } else {
            $Rows = $result->numRows();
            for ($i = 0; $i <= $Rows; $i ++) {
                $DataHoje = date("Y-m-d");
                $Linha = $result->fetchRow();
                if ($i == 0) {
                    if ($Linha [2] < $DataHoje) {
                        $Cadastrado = "INABILITADO";
                        $InabilitacaoCertidaoObrigatoria = true;
                    } else {
                        $Cadastrado = "HABILITADO";
                    }
                }
            }
        }

        // Everton - correção de erro

        $dataHoje = new DateTime();

        // Verifica também se a data de balanço anual está no prazo #
        // só compara se Data Ultimo Ballanço for diferento de nulo

        if (! empty($DataNovaUltBalanco)) {
            if ($DataNovaUltBalanco < prazoUltimoBalanço()->format('Y-m-d')) {
                $Cadastrado = "INABILITADO";
                $InabilitacaoUltBalanco = true;
            }
        }

        if ($DataNovaCertidaoNeg < prazoCertidaoNegDeFalencia()->format('Y-m-d')) {
            $Cadastrado = "INABILITADO";
            $InabilitacaoCertidaoNeg = true;
        }
    } else {
        $Cadastrado = "HABILITADO";
    }

    // Mostra Tabela de Situação #
    $sql = "SELECT EFORTSDESC FROM SFPC.TBFORNECEDORTIPOSITUACAO";
    $sql .= " WHERE CFORTSCODI = " . $Situacao . "";
    $result = $dbase->query($sql);
    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
    } else {
        $situacao = $result->fetchRow();
        $DescSituacao = $situacao [0];
        if ($Situacao == 1) {
            if ($TipoHabilitacao == "L") {
                $DescSituacao = $DescSituacao . " " . $Cadastrado;
            } else {
                $DescSituacao = $DescSituacao;
            }
        }
    }

    // Verifica se já Existe Data de CHF #
    $sql = "SELECT DFORCHGERA,DFORCHVALI FROM SFPC.TBFORNECEDORCHF ";
    $sql .= " WHERE AFORCRSEQU = $Sequencial ";
    $result = $dbase->query($sql);
    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
    } else {
        $Rows = $result->numRows();
        if ($Rows != 0) {
            $Linha = $result->fetchRow();
            $DataGeracaoCHF = substr($Linha [0], 8, 2) . "/" . substr($Linha [0], 5, 2) . "/" . substr($Linha [0], 0, 4);
            $DataValidadeCHF = substr($Linha [1], 8, 2) . "/" . substr($Linha [1], 5, 2) . "/" . substr($Linha [1], 0, 4);
        } else {
            $DataGeracaoCHF = "-";
            $DataValidadeCHF = "-";
        }
    }

    // Busca os Dados da Tabela de Conta Bancária de acordo com o sequencial do Fornecedor #
    $sql = "SELECT CFORCBBANC, CFORCBAGEN, CFORCBCONT, TFORCBULAT ";
    $sql .= "  FROM SFPC.TBFORNCONTABANCARIA ";
    $sql .= " WHERE AFORCRSEQU = $Sequencial ";
    $sql .= " ORDER BY TFORCBULAT";
    $result = $dbase->query($sql);
    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
    } else {
        $Rows = $result->numRows();
        for ($i = 0; $i < $Rows; $i ++) {
            $Linha = $result->fetchRow();
            if ($i == 0) {
                $Banco1 = $Linha [0];
                $Agencia1 = $Linha [1];
                $ContaCorrente1 = $Linha [2];
            } else {
                $Banco2 = $Linha [0];
                $Agencia2 = $Linha [1];
                $ContaCorrente2 = $Linha [2];
            }
        }
    }
    // Verifica se o Fornecedor está Regular na Prefeitura #
    if ($Irregularidade == "") {
        if ($CNPJ != "") {
            $TipoDoc = 1;
            $CPF_CNPJ = $CNPJ;
        } elseif ($CPF != "") {
            $TipoDoc = 2;
            $CPF_CNPJ = $CPF;
        }
        $NomePrograma = "ConsAcompFornecedorSelecionar2.php";
        $Url = "RotDebitoCredorConsulta.php?NomePrograma=$NomePrograma&TipoDoc=$TipoDoc&CPF_CNPJ=$CPF_CNPJ&Sequencial=$Sequencial";
        $_SESSION['GetUrl'] = array("nothing", "nothing2");
        if (! in_array($Url, $_SESSION['GetUrl'])) {
            $_SESSION ['GetUrl'] [] = $Url;
        }
        //Redireciona ( $Url );
        header("location: " . $Url);

        exit();
    }
    # Aviso para Fornecedor de estoque #
    if ($TipoHabilitacao == "E") {
        $Mens     = 1;
        $Tipo     = 2;
        $Mensagem = "FORNECEDOR DE MÓDULO DE ESTOQUES DEVE SER ALTERADO PARA TIPO (COMPRA DIRETA) ou (LICITAÇÃO) ";
    }

    if ($TipoHabilitacao=='L' and $CNPJ != "") {
        # Mensagem para Fornecedor Inabilitado #
        if ($Situacao != 3) {
            $inabilitarFornecedor = false; //imprimir mensagem de fornecedor INABILITADO?
            if ($Cadastrado == "INABILITADO" and $InabilitacaoCertidaoObrigatoria) {
                if ($Irregularidade == "S") {
                    $Mens     = 1;
                    $Tipo     = 1;
                    if (!$inabilitarFornecedor) {
                        $inabilitarFornecedor = true;
                        $Mensagem = "Fornecedor INABILITADO ";
                    }
                    if ($Cadastrado == "INABILITADO") {
                        $Mensagem .= "CERTIDÃO(ÕES) FORA DO PRAZO DE VALIDADE E COM SITUAÇÃO IRREGULAR NA PREFEITURA";
                    } else {
                        $Mensagem .= "SITUAÇÃO IRREGULAR NA PREFEITURA";
                    }
                } elseif ($Irregularidade == "N" and  $Cadastrado == "INABILITADO") {
                    $Mens     = 1;
                    $Tipo     = 1;
                    $Mensagem .= "CERTIDÃO(ÕES) FORA DO PRAZO DE VALIDADE";
                }
            }



            if (empty($MicroEmpresa)) {
                if ($Cadastrado == "INABILITADO" and $InabilitacaoUltBalanco) {
                    if ($Mens == 1) {
                        $Mensagem .=", ";
                    }
                    $Mens     = 1;
                    $Tipo     = 1;
                    if (!$inabilitarFornecedor) {
                        $inabilitarFornecedor = true;
                        $Mensagem = "FORNECEDOR INABILITADO ";
                    }
                    $Mensagem.= "DATA DE VALIDADE DO BALANÇO EXPIRADA"/*(data menor que ".prazoUltimoBalanço()->format('d/m/Y').")"*/;
                }
            }



            if ($Cadastrado == "INABILITADO" and $InabilitacaoCertidaoNeg) {
                if ($Mens == 1) {
                    $Mensagem .=", ";
                }
                $Mens     = 1;
                $Tipo     = 1;
                if (!$inabilitarFornecedor) {
                    $inabilitarFornecedor = true;
                    $Mensagem = "FORNECEDOR INABILITADO ";
                }
                $Mensagem.= "DATA CERTIDÃO NEGATIVA EXPIRADA "/*(data menor que ".prazoCertidaoNegDeFalencia()->format('d/m/Y').")"*/;
            }


            if (!empty($MicroEmpresa) and  empty($dt_val_bal)) {
                if ($Mens == 1) {
                    $Mensagem .=", ";
                }
                $Mens     = 1;
                $Tipo     = 1;
                $Mensagem.= "CHF SIMPLIFICADO SEM DEMONSTRAÇÕES CONTÁBEIS" ;
            }



            /*
                # Verifica se a data de balanço expirou, baseado no seguinte: se a data atual for maior que 01/05 do ano corrente só aceitar
            # a data de balanço com um ano a menos do ano atual, caso contrário aceitar com 2 anos a menos do ano atual
            if (    (date("Y-m-d") <= date("Y")."04"."30")) {
            $AnoBalanco = date("Y") - 2;
            if  (substr($DataUltBalanco,6,4) < $AnoBalanco) {
            if( $Mens == 0 ){
            $Mensagem = "Fornecedor com ";
            }
            if( $Mens == 1 ){ $Mensagem .=", "; }
            $Mens      = 1;
            $Tipo      = 1;
            $Virgula   = 1;
            $Mensagem .= " Ano de validade do balanço menor que $AnoBalanco";
            }
            }   */
        }
    }

    $sql  = "SELECT A.CFORTOCODI, A.EFOROCDETA, A.DFOROCDATA, B.EFORTODESC ";
    $sql .= "  FROM SFPC.TBFORNECEDOROCORRENCIA A, SFPC.TBFORNTIPOOCORRENCIA B";
    $sql .= " WHERE A.CFORTOCODI = B.CFORTOCODI AND A.AFORCRSEQU = $Sequencial ORDER BY 3,1";
    $res  = $dbase->query($sql);

    $tpl->VALOR_CODIGO_FORNECEDOR = $Sequencial;

    if ($TipoHabilitacao == "L") {
        $tpl->VALOR_CUMPRIMENTO_ART_7 = "SIM";
        $tpl->block("BLOCK_TIPO_ABILITACAO");
    }

    $tpl->VALOR_SITUACAO = $DescSituacao;

    if ($Situacao != 1) {
        $tpl->VALOR_DATA_SITUACAO = $DataSituacao;
        $tpl->VALOR_MOTIVO = strtoupper2($Motivo);
        $tpl->block("BLOCK_SITUACAO_1");
    }

    if ($Situacao == 3) {
        $tpl->VALOR_DATA_EXPIRACAO_SUSPENCAO = $DataSuspensao;
        $tpl->block("BLOCK_SITUACAO_3");
    }

    $tpl->VALOR_DATA_GERACAO_CHF = $DataGeracaoCHF;
    $tpl->VALOR_DATA_VALIDADE_CHF = $DataValidadeCHF;
    $tpl->VALOR_DATA_CADASTRAMENTO = $DataInscricao;
    $tpl->VALOR_COMISSAO_RESPONSAVEL_ANALISE = $ComissaoResp;
    $tpl->VALOR_DATA_ANALISE = $DataAnaliseDoc;

    if ($TipoHabilitacao == "D") {
        $tpl->VALOR_ABILITACAO_FORNECEDOR = "COMPRA DIRETA";
    } elseif ($TipoHabilitacao == "L") {
        $tpl->VALOR_ABILITACAO_FORNECEDOR = "LICITAÇÃO";
    } elseif ($TipoHabilitacao == "E") {
        $tpl->VALOR_ABILITACAO_FORNECEDOR = "MÓDULO DE ESTOQUES";
    }

    $tpl->block("BLOCK_CONSULTA_FORNECEDORES_INSCRITOS");

    if (PEAR::isError($res)) {
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
    } else {
        $Rows = $res->numRows();
        if ($Rows == 0) {
            $tpl->block("BLOCK_NENHUMA_OCORRENCIAS");
        } else {
            for ($i=0; $i<$Rows; $i++) {
                $Linha     = $res->fetchRow();
                $Codigo    = $Linha[0];
                $Detalhe   = $Linha[1];
                $Data      = $Linha[2];
                $Descricao = $Linha[3];

                $tpl->VALOR_DATA_OCORRENCIA = substr($Data, 8, 2);
                $tpl->VALOR_TIPO_OCORRENCIA = strtoupper2($Descricao);
                $tpl->VALOR_DETALHAMENTO = $Detalhe;

                $tpl->block("BLOCK_OCORRENCIAS");
            }
        }
    }

    if ($CNPJ <> 0) {
        $tpl->VALOR_HABILITACAO_JURIDICA_CNPJ = substr($CNPJ, 0, 2).".".substr($CNPJ, 2, 3).".".substr($CNPJ, 5, 3)."/".substr($CNPJ, 8, 4)."-".substr($CNPJ, 12, 2);
        $tpl->VALOR_IDENTIDADE_REPRESENTANTE_LEGAL = $Identidade;
        $tpl->block("BLOCK_HABILITACAO_JURIDICA_CNPJ");
    } else {
        $tpl->VALOR_HABILITACAO_JURIDICA_CPF = substr($CPF, 0, 3).".".substr($CPF, 3, 3).".".substr($CPF, 6, 3)."-".substr($CPF, 9, 2);
        $tpl->block("BLOCK_HABILITACAO_JURIDICA_CPF");
    }

    if ($Identidade <> "") {
        if ($CNPJ <> 0) {
            $tpl->VALOR_IDENTIDADE_REPRESENTANTE_LEGAL = $Identidade;
            $tpl->block("BLOCK_REPRES_LEGAL");
        } else {
            $tpl->VALOR_IDENTIDADE = $Identidade;
            $tpl->block("BLOCK_IDENTIDADE");
        }
        $tpl->VALOR_ORGAO_EMISSOR_UF = $OrgaoEmissorUF;
    }

    if ($CNPJ <> 0) {
        $tpl->VALOR_RAZAO_SOCIAL = $RazaoSocial;
        $tpl->block("BLOCK_RAZAO_SOCIAL");
    } else {
        $tpl->VALOR_NOME = $RazaoSocial;
        $tpl->block("BLOCK_NOME");
    }

    if ($NomeFantasia != "") {
        $tpl->VALOR_NOME_FANTASIA = $NomeFantasia;
    } else {
        $tpl->VALOR_NOME_FANTASIA = "NÃO INFORMADO";
    }

    $tpl->VALOR_CEP = $CEP;
    $tpl->VALOR_LOGRADOURO = $Logradouro;

    if ($Numero != "") {
        $tpl->VALOR_NUMERO = $Numero;
    } else {
        $tpl->VALOR_NUMERO = "NÃO INFORMADO";
    }

    if ($Complemento != "") {
        $tpl->VALOR_COMPLEMENTO = $Complemento;
    } else {
        $tpl->VALOR_COMPLEMENTO = "NÃO INFORMADO";
    }

    $tpl->VALOR_BAIRRO = $Bairro;
    $tpl->VALOR_CIDADE = $Cidade;
    $tpl->VALOR_UF = $UF;

    if ($DDD != "") {
        $tpl->VALOR_DDD = $DDD;
    } else {
        $tpl->VALOR_DDD = "NÃO INFORMADO";
    }

    if ($Telefone != "") {
        $tpl->VALOR_TELEFONE = $Telefone;
    } else {
        $tpl->VALOR_TELEFONE = "NÃO INFORMADO";
    }

    if ($Email != "") {
        $tpl->VALOR_EMAIL_1 = $Email;
    } else {
        $tpl->VALOR_EMAIL_1 = "NÃO INFORMADO";
    }

    if ($Email2 != "") {
        $tpl->VALOR_EMAIL_2 = $Email2;
    } else {
        $tpl->VALOR_EMAIL_2 = "NÃO INFORMADO";
    }

    if ($Fax != "") {
        $tpl->VALOR_FAX = $Fax;
    } else {
        $tpl->VALOR_FAX = "NÃO INFORMADO";
    }

    if ($RegistroJunta != "") {
        $tpl->VALOR_REGISTRO_JUNTA_COMERCIAL = $RegistroJunta;
    } else {
        $tpl->VALOR_REGISTRO_JUNTA_COMERCIAL = "NÃO INFORMADO";
    }

    if ($DataRegistro != "") {
        $tpl->VALOR_DATA_JUNTA_COMERCIAL = $DataRegistro;
    } else {
        $tpl->VALOR_DATA_JUNTA_COMERCIAL = "NÃO INFORMADO";
    }

    if ($NomeContato != "") {
        $tpl->VALOR_NOME_CONTATO = $NomeContato;
    } else {
        $tpl->VALOR_NOME_CONTATO = "NÃO INFORMADO";
    }

    if ($CPFContato != "") {
        $tpl->VALOR_CPF_CONTATO = $CPFContato;
    } else {
        $tpl->VALOR_CPF_CONTATO = "NÃO INFORMADO";
    }

    if ($CargoContato != "") {
        $tpl->VALOR_CARGO_CONTATO = $CargoContato;
    } else {
        $tpl->VALOR_CARGO_CONTATO = "NÃO INFORMADO";
    }

    if ($DDDContato != "") {
        $tpl->VALOR_DDD_CONTATO = $DDDContato;
    } else {
        $tpl->VALOR_DDD_CONTATO = "NÃO INFORMADO";
    }

    if ($TelefoneContato != "") {
        $tpl->VALOR_TELEFONE_CONTATO = $TelefoneContato;
    } else {
        $tpl->VALOR_TELEFONE_CONTATO = "NÃO INFORMADO";
    }

    $tpl->block("BLOCK_HABILITACAO_JURIDICA2");

    if ($CNPJ <> 0) {
        # Pega os Dados dos sócios #
        $sql  = "SELECT
                 asoforcada, nsofornome
                 FROM SFPC.TBsociofornecedor
                 WHERE aforcrsequ = ".$Sequencial."
                ";

        $res = $dbase->query($sql);

        if (PEAR::isError($res)) {
            EmailErroSQL('Erro ao obter sócios de fornecedor', __FILE__, __LINE__, 'Erro ao obter sócios de fornecedor', $sql, $res);
        } else {
            $Rows = $res->numRows();
            if ($Rows==0) {
                $tpl->block("BLOCK_SEM_DADOS_SOCIOS");
            } else {
                for ($itr=0; $itr<$Rows; $itr++) {
                    $Linha = $res->fetchRow();
                    $socioCPF = $Linha[0];
                    $socioNome = $Linha[1];
                    $tpl->VALOR_NOME_SOCIOS = $socioNome;
                    $tpl->VALOR_CPF_CNPJ_SOCIOS = $socioCPF;
                    $tpl->block("BLOCK_DADOS_SOCIOS");
                }
            }
            $tpl->block("BLOCK_SOCIOS");
        }
    }

    if ($InscMercantil != "") {
        $tpl->VALOR_INSCRICAO_MERCANTIL = $InscMercantil;
    } else {
        $tpl->VALOR_INSCRICAO_MERCANTIL = "-";
    }

    if ($InscOMunic != "") {
        $tpl->VALOR_INSCRICAO_OUTRO_MUNICIPIO = $InscOMunic;
    } else {
        $tpl->VALOR_INSCRICAO_OUTRO_MUNICIPIO = "-";
    }

    if ($InscEstadual != "") {
        $tpl->VALOR_INSCRICAO_ESTADUAL = $InscEstadual;
    } else {
        $tpl->VALOR_INSCRICAO_ESTADUAL = "NÃO INFORMADO";
    }

    $tpl->block("BLOCK_REGULARIDADE_FISCAL");

    if ($TipoHabilitacao == "L") {
        $sql = "SELECT CTIPCECODI, ETIPCEDESC FROM SFPC.TBTIPOCERTIDAO WHERE FTIPCEOBRI = 'S' ORDER BY 1";
        $res = $dbase->query($sql);

        if (PEAR::isError($res)) {
            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
        } else {
            $Rows = $res->numRows();
            for ($i=0; $i<$Rows; $i++) {
                $Linha       = $res->fetchRow();
                $DescricaoOb = substr($Linha[1], 0, 75);
                $CertidaoOb  = $Linha[0];

                # Verifica se existem certidões obrigatórias cadastradas para o Fornecedor #
                $sqlData  = "SELECT DFORCEVALI FROM SFPC.TBFORNECEDORCERTIDAO ";
                $sqlData .= " WHERE AFORCRSEQU = $Sequencial AND CTIPCECODI = $CertidaoOb";

                $resData  = $dbase->query($sqlData);
                if (PEAR::isError($resData)) {
                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                } else {
                    $LinhaData = $resData->fetchRow();
                    if ($LinhaData[0] != 0) {
                        $DataCertidaoOb[$ob-1] = substr($LinhaData[0], 8, 2)."/".substr($LinhaData[0], 5, 2)."/".substr($LinhaData[0], 0, 4);
                    } else {
                        $DataCertidaoOb[$ob-1] = null;
                    }
                }
                if ($LinhaData[0] < date("Y-m-d")) {
                    $Validade = "titulo1";
                } else {
                    $Validade = "textonormal";
                }

                if ($DescricaoOb == "DATA ÚLTIMO BALANÇO ") {
                    $dataUltimoBalanco = $Validade;
                }

                $tpl->VALOR_NOME_CERTIDAO_OBRIGATORIA = $DescricaoOb;
                if (is_null($DataCertidaoOb[$ob-1])) {
                    $tpl->VALOR_DATA_VALIDADE_CERTIDAO_OBRIGATORIA = "NÃO INFORMADO";
                } else {
                    $tpl->VALOR_DATA_VALIDADE_CERTIDAO_OBRIGATORIA = $DataCertidaoOb[$ob-1];
                }
                $tpl->block("BLOCK_DADOS_CERTIDAO_FISCAL_OBRIGRATORIA");
            }
        }

        $sql  = "SELECT A.DFORCEVALI, B.CTIPCECODI, B.ETIPCEDESC  ";
        $sql .= "  FROM SFPC.TBFORNECEDORCERTIDAO A, SFPC.TBTIPOCERTIDAO B ";
        $sql .= " WHERE A.AFORCRSEQU = $Sequencial AND A.CTIPCECODI = B.CTIPCECODI";
        $sql .= "   AND B.FTIPCEOBRI = 'N' ORDER BY B.CTIPCECODI";
        $res = $dbase->query($sql);
        if (PEAR::isError($res)) {
            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
        } else {
            $Rows = $res->numRows();

            if ($Rows != 0) {
                # Mostra as certidões complementares cadastradas #
                for ($i=0; $i<$Rows; $i++) {
                    $Linha = $res->fetchRow();
                    $DescricaoOp                        = substr($Linha[2], 0, 75);
                    $CertidaoOpCodigo               = $Linha[1];
                    $CertidaoComplementar[$i] = $Linha[1];
                    $DataCertidaoOp[$i]     = substr($Linha[0], 8, 2)."/".substr($Linha[0], 5, 2)."/".substr($Linha[0], 0, 4);

                    $tpl->VALOR_NOME_CERTIDAO_COMPLEMENTAR = $DescricaoOp;
                    $tpl->VALOR_DATA_VALIDADE_CERTIDAO_COMPLEMENTAR = $DataCertidaoOp[$i];
                    $tpl->block("BLOCK_DADOS_CERTIDAO_FISCAL_COMPLEMENTAR");
                }
            } else {
                $tpl->block("BLOCK_SEM_DADOS_CERTIDAO_FISCAL_COMPLEMENTAR");
            }
        }
        $tpl->block("BLOCK_CERTIDAO_FISCAL");
    }

    if ($CNPJ <> 0 and $TipoHabilitacao == 'L') {
        $tpl->VALOR_CAPITAL_SOCIAL = $CapSocial;

        if ($CapIntegralizado != "") {
            $tpl->VALOR_CAPITAL_INTEGRALIZADO = $CapIntegralizado;
        } else {
            $tpl->VALOR_CAPITAL_INTEGRALIZADO = "NÃO INFORMADO";
        }

        $tpl->VALOR_PATRIMONIO_LIQUIDO = $Patrimonio;

        if ($IndLiqCorrente != "") {
            $tpl->VALOR_INDICE_LIQUIDEZ_CORRENTE = $IndLiqCorrente;
        } else {
            $tpl->VALOR_INDICE_LIQUIDEZ_CORRENTE = "NÃO INFORMADO";
        }

        if ($IndLiqGeral != "") {
            $tpl->VALOR_INDICE_LIQUIDEZ_GERAL = $IndLiqGeral;
        } else {
            $tpl->VALOR_INDICE_LIQUIDEZ_GERAL = "NÃO INFORMADO";
        }

        if ($IndEndividamento != "") {
            $tpl->VALOR_INDICE_ENDIVIDAMENTO = $IndEndividamento;
        } else {
            $tpl->VALOR_INDICE_ENDIVIDAMENTO = "NÃO INFORMADO";
        }

        if ($IndSolvencia != "") {
            $tpl->VALOR_INDICE_SOLVENCIA = $IndSolvencia;
        } else {
            $tpl->VALOR_INDICE_SOLVENCIA = "NÃO INFORMADO";
        }

        if ($DataUltBalanco != "") {
            $tpl->VALOR_DATA_VALIDADE_BALANCO = $DataUltBalanco;
        } else {
            $tpl->VALOR_DATA_VALIDADE_BALANCO = "NÃO INFORMADO";
        }

        if ($DataCertidaoNeg != "") {
            $tpl->VALOR_DATA_CERTIDAO_NEGATIVA_FALENCIA = $DataCertidaoNeg;
        } else {
            $tpl->VALOR_DATA_CERTIDAO_NEGATIVA_FALENCIA = "NÃO INFORMADO";
        }

        if ($DataContratoEstatuto != "") {
            $tpl->VALOR_DATA_ULTIMA_ALTERACAO_CONTRATO = $DataContratoEstatuto;
        } else {
            $tpl->VALOR_DATA_ULTIMA_ALTERACAO_CONTRATO = "NÃO INFORMADO";
        }
    }

    if ($Banco1 == "" and  $Banco2 == "") {
        $tpl->block("BLOCK_DADOS_BANCO_NAO_INFORMADO");
    } else {
        if ($Banco1 != "") {
            $tpl->VALOR_BANCO = $Banco1;
            $tpl->VALOR_AGENCIA = $Agencia1;
            $tpl->VALOR_CONTA_CORRENTE = $ContaCorrente1;
            $tpl->block("BLOCK_DADOS_BANCO");
        }
        if ($Banco2 != "") {
            $tpl->VALOR_BANCO = $Banco2;
            $tpl->VALOR_AGENCIA = $Agencia2;
            $tpl->VALOR_CONTA_CORRENTE = $ContaCorrente2;
            $tpl->block("BLOCK_DADOS_BANCO");
        }
    }

    $tpl->block("BLOCK_BANCO");

    $tpl->block("BLOCK_QUALIFICACAO_ECONOMICA");

    if ($TipoHabilitacao == "L") {
        if ($NomeEntidade != "") {
            $tpl->VALOR_NOME_ENTIDADE = $NomeEntidade;
        } else {
            $tpl->VALOR_NOME_ENTIDADE = "NÃO INFORMADO";
        }

        if ($RegistroEntidade != "") {
            $tpl->VALOR_REGISTRO_INSCRICAO = $RegistroEntidade;
        } else {
            $tpl->VALOR_REGISTRO_INSCRICAO = "NÃO INFORMADO";
        }

        if ($DataVigencia != "") {
            $tpl->VALOR_DATA_VIGENCIA = $DataVigencia;
        } else {
            $tpl->VALOR_DATA_VIGENCIA = "NÃO INFORMADO";
        }

        if ($TecnicoEntidade != "") {
            $tpl->VALOR_REGISTRO_INSCRICAO_TECNICO = $TecnicoEntidade;
        } else {
            $tpl->VALOR_REGISTRO_INSCRICAO_TECNICO = "NÃO INFORMADO";
        }

        $tpl->block("BLOCK_QUALIFICACAO_TECNICA");
    }

    $sql  = "SELECT AFORAENUMA, NFORAENOMA, DFORAEVIGE FROM SFPC.TBFORNAUTORIZACAOESPECIFICA ";
    $sql .= " WHERE AFORCRSEQU = $Sequencial";
    $res = $dbase->query($sql);
    if (PEAR::isError($res)) {
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
    } else {
        $Rows = $res->numRows();
        if ($Rows <> 0) {
            for ($i=0; $i<$Rows; $i++) {
                $Linha              = $res->fetchRow();
                $RegistroAutor= $Linha[0];
                $NomeAutor      = $Linha[1];
                $DataVigAutor   = substr($Linha[2], 8, 2)."/".substr($Linha[2], 5, 2)."/".substr($Linha[2], 0, 4);

                if ($NomeAutor != "") {
                    $tpl->VALOR_NOME_ENTIDADE_EMISSORA_ESPECIFICA = $NomeAutor;
                } else {
                    $tpl->VALOR_NOME_ENTIDADE_EMISSORA_ESPECIFICA = "NÃO INFORMADO";
                }

                if ($RegistroAutor != "") {
                    $tpl->VALOR_REGISTRO_INSCRICAO_ESPECIFICA = $RegistroAutor;
                } else {
                    $tpl->VALOR_REGISTRO_INSCRICAO_ESPECIFICA = "NÃO INFORMADO";
                }

                if ($DataVigAutor != "") {
                    $tpl->VALOR_DATA_VIGENCIA_ESPECIFICA = $DataVigAutor;
                } else {
                    $tpl->VALOR_DATA_VIGENCIA_ESPECIFICA = "NÃO INFORMADO";
                }

                $tpl->block("BLOCK_DADOS_AUTORIZACAO_ESPECIFICA");
            }
            $tpl->block("BLOCK_AUTORIZACAO_ESPECIFICA");
        } else {
            $tpl->block("BLOCK_AUTORIZACAO_ESPECIFICA_NAO_INFORMADO");
        }
    }

    $sql  = "SELECT A.AFORCRSEQU, B.CGRUMSCODI, B.EGRUMSDESC ";
    $sql .= "  FROM SFPC.TBGRUPOFORNECEDOR A, SFPC.TBGRUPOMATERIALSERVICO B ";
    $sql .= " WHERE A.AFORCRSEQU = $Sequencial AND A.CGRUMSCODI = B.CGRUMSCODI ";
    $sql .= "   AND B.FGRUMSTIPO = 'M' ORDER BY 1,3";
    $res = $dbase->query($sql);
    if (PEAR::isError($res)) {
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
    } else {
        $Rows = $res->numRows();
        if ($Rows <> 0) {
            # Mostra os grupos de materiais cadastrados #
                $DescricaoGrupoAntes = "";
            for ($i=0; $i<$Rows; $i++) {
                $Linha                          = $res->fetchRow();
                $DescricaoGrupo                 = substr($Linha[2], 0, 75);

                $Materiais[$i]= "M#".$Linha[1];
                if ($DescricaoGrupoAntes <> $DescricaoGrupo) {
                    $tpl->VALOR_MATERIAIS = $DescricaoGrupo;
                    $tpl->block("BLOCK_DADOS_MATERIAIS_GRUPOS_FORNECIMENTO");
                }
                $DescricaoGrupoAntes = $DescricaoGrupo;
            }
            $tpl->block("BLOCK_MATERIAIS_GRUPOS_FORNECIMENTO");
        }
    }

        # Mostra os grupos de serviços já cadastrados do Fornecedor #
        $sql  = "SELECT A.AFORCRSEQU, B.CGRUMSCODI, B.EGRUMSDESC ";
    $sql .= "  FROM SFPC.TBGRUPOFORNECEDOR A, SFPC.TBGRUPOMATERIALSERVICO B  ";
    $sql .= " WHERE A.AFORCRSEQU = $Sequencial AND A.CGRUMSCODI = B.CGRUMSCODI ";
    $sql .= "   AND B.FGRUMSTIPO = 'S' ORDER BY 1,3";
    $res = $dbase->query($sql);
    if (PEAR::isError($res)) {
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
    } else {
        $Rows = $res->numRows();
        if ($Rows <> 0) {
            # Mostra os grupos de serviços cadastrados #
                $DescricaoGrupoAntes = "";
            for ($i=0; $i<$Rows; $i++) {
                $Linha = $res->fetchRow();
                $DescricaoGrupo   = substr($Linha[2], 0, 75);
                $Servicos[$i]= "S#".$Linha[1];
                if ($DescricaoGrupo <> $DescricaoGrupoAntes) {
                    $tpl->VALOR_SERVICOS = $DescricaoGrupo;
                    $tpl->block("BLOCK_DADOS_SERVICOS_GRUPOS_FORNECIMENTO");
                }
                $DescricaoGrupoAntes = $DescricaoGrupo;
            }
            $tpl->block("BLOCK_SERVICOS_GRUPOS_FORNECIMENTO");
        }
    }

    $tpl->block("BLOCK_GRUPOS_FORNECIMENTO");

    $tpl->VALOR_SEQUENCIAL = $Sequencial;

    if (!empty($Mensagem)) {
        $tpl->VALOR_MENSAGEM = $Mensagem;
        $tpl->block('BLOCO_MENSAGEM');
    }

    $tpl->show();
}

function processImprimir()
{
    # Variáveis com o global off #
    if ($_SERVER['REQUEST_METHOD']  == "GET") {
        $Sequencial     = $_GET['Sequencial'];
        $Irregularidade = $_GET['Irregularidade'];
    } else {
        $Botao      = $_POST['Botao'];
        $Sequencial = $_POST['Sequencial'];
        $Mensagem   = $_POST['Mensagem'];
    }

    $Url = "RelAcompFornecedorPdf.php?Sequencial=$Sequencial&Mensagem=".urlencode($Mensagem)."";
    if (!in_array($Url, $_SESSION['GetUrl'])) {
        $_SESSION['GetUrl'][] = $Url;
    }
    header("location: ".$Url);
    exit;
}

function proccessVoltar()
{
    $Url = "ConsSancoesSelecionar.php";
    if (!in_array($Url, $_SESSION['GetUrl'])) {
        $_SESSION['GetUrl'][] = $Url;
    }
    header("location: ".$Url);
    exit;
}

/**
 * [frontController description]
 *
 * @return void
 */
function frontController()
{
    $botao = isset($_REQUEST ['BotaoAcao']) ? $_REQUEST ['BotaoAcao'] : 'Principal';

    switch ($botao) {
        case 'Imprimir':
            processImprimir();
            break;
        case 'Voltar':
            proccessVoltar();
            break;
        case 'Principal':
        default:
            proccessPrincipal();
    }
}

frontController();
