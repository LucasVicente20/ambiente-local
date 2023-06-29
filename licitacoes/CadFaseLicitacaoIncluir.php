<?php
# -------------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadFaseLicitacaoIncluir.php
# Autor: Rossana Lira
# Objetivo: Programa de Inclusão de Fase de Licitação
# OBS.: Tabulação 2 espaços
# -------------------------------------------------------------------------------
# Alterado: Rossana
# Data: 17/10/2007 - Não exigir bloqueio para modalidade leilão
# -------------------------------------------------------------------------------
# Alterado: Heraldo Botelho
# Objetivo: Pegar mensagem de erro na comparacao da data da fase como :
# Dt. Ultima Fase , Dt. Ultima Solicitacao;
# Data : 19/11/2012
# -------------------------------------------------------------------------------
# Alterado: Pitang Agile TI
# Objetivo: CR 170 - Fase de Licitação - Incluir e Manter - só exibir a fase de
# arquivamento para o perfil corporativo
# Data : 10/03/2015
# -------------------------------------------------------------------------------
# Alterado: Pitang Agile TI
# Objetivo: CR73624 - TRP - Fase Licitação Incluir - Nova regra para a TRP
# Data : 14/07/2015
# ------------------------------------------------------------------------------
# Alterado: Pitang Agile TI
# Objetivo: Requisito 135854: Licitação Fase Incluir (#441)
# Data :    28/06/2016
# versão:   EMPREL-SAD-PORTAL-COMPRAS-BL-FUNC-20160512-1240
# ------------------------------------------------------------------------------
# Alterado: Pitang Agile TI
# Data :    05/04/2018
# Objetivo: CR 187219 - Alteração de cálculo na homologação de uma licitação
# ------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     20/04/2018
# Objetivo: Tarefa Redmine 192306
# ------------------------------------------------------------------------------
# Alterado: Pitang Agile TI
# Data :    24/04/2018
# Objetivo: CR165624 - Não gravar TRP #538
# ------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data :    03/07/2018
# Objetivo: Tarefa Redmine 73665
# ------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:	 	04/07/2018
# Objetivo: Tarefa Redmine 95885
# ------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     14/08/2018
# Objetivo: Tarefa Redmine 189350
# ------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		24/10/2018
# Objetivo: Tarefa Redmine 73662
# ------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     26/11/2018
# Objetivo: Tarefa Redmine 207311
# ------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     27/12/2018
# Objetivo: Tarefa Redmine 208783
# ------------------------------------------------------------------------------
# Alterado: Osmar Celestino 
# Data:     27/04/2021
# Objetivo: Tarefa Redmine 247328
# ------------------------------------------------------------------------------
# Alterado: Osmar Celestino 
# Data:     05/07/2021
# Objetivo: Tarefa Redmine 250515
# ------------------------------------------------------------------------------
# Alterado: Lucas Vicente
# Data:     10/10/2022
# Objetivo: Tarefa Redmine 206442
# 
// Acesso ao arquivo de funções #
require_once 'funcoesLicitacoes.php';

// Executa o controle de segurança #
session_start();
Seguranca();

// Adiciona páginas no MenuAcesso #
AddMenuAcesso('/licitacoes/CadFaseLicitacaoConfirmar.php');

// Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $ProcessoAnoComissaoOrgao = $_POST['ProcessoAnoComissaoOrgao'];
    $Botao                    = $_POST['Botao'];
    $Critica                  = $_POST['Critica'];
    $Processo                 = $_POST['Processo'];
    $ProcessoAno              = $_POST['ProcessoAno'];
    $ComissaoCodigo           = $_POST['ComissaoCodigo'];
    $OrgaoLicitanteCodigo     = $_POST['OrgaoLicitanteCodigo'];
    $ModalidadeCodigo         = $_POST['ModalidadeCodigo'];
    $RegistroPreco            = $_POST['RegistroPreco'];
    $FaseCodigo               = $_POST['FaseCodigo'];
    $FaseCodigoDesc           = $_POST['FaseCodigoDesc'];
    $DataFase                 = $_POST['DataFase'];
    
    if ($DataFase != "") {
        $DataFase = FormataData($DataFase);
    }

    $FaseLicitacaoDetalhe     = strtoupper2(trim($_POST['FaseLicitacaoDetalhe']));
    $ValorHomologado          = $_POST['ValorHomologado'];
    $TotalGeralEstimado       = $_POST['TotalGeralEstimado'];
    $Homologacao              = $_POST['Homologacao'];
    $QtdBloqueios             = $_POST['QtdBloqueios'];
}
$codigoUsuario = $_SESSION["_cperficodi_"];
//var_dump($codigoUsuario);
$db = Conexao();

$NProcessoAnoComissao = explode("_", $ProcessoAnoComissaoOrgao);
$Processo             = substr($NProcessoAnoComissao[0] + 10000, 1);
$ProcessoAno          = $NProcessoAnoComissao[1];
$ComissaoCodigo       = $NProcessoAnoComissao[2];
$OrgaoLicitanteCodigo = $NProcessoAnoComissao[3];
$ModalidadeCodigo     = $NProcessoAnoComissao[4];
$RegistroPreco        = $NProcessoAnoComissao[5];

$Fase       = explode("_", $FaseCodigoDesc);
$FaseCodigo = $Fase[0];

// Verificar se licitacao possui solicitacao
if ($Critica == 1 and ! empty($ProcessoAno)) {
    $licitacao_possui_solicitacao = false;
    $Grupo                        = $_SESSION['_cgrempcodi_'];
    
    $sql = "SELECT  COUNT(*)
            FROM    SFPC.TBSOLICITACAOLICITACAOPORTAL 
            WHERE   CLICPOPROC = $Processo
                    AND ALICPOANOP = $ProcessoAno
                    AND CGREMPCODI = $Grupo
                    AND CCOMLICODI = $ComissaoCodigo
                    AND CORGLICODI = $OrgaoLicitanteCodigo ";

    $result = executarSQL($db, $sql);

    $Linha = $result->fetchRow();
    
    if ($Linha[0] > 0) {
        $licitacao_possui_solicitacao = true;
    }
    
    // fazer calculo de TotalGeralEstimado e ValorHomologado
    if ($licitacao_possui_solicitacao) {
        $TotalGeralEstimado = 0;
        $ValorHomologado    = 0;
        $Grupo              = $_SESSION['_cgrempcodi_'];
        
        // total geral estimado
        $sql = "SELECT  SUM(VITELPUNIT * AITELPQTSO)
                FROM    SFPC.TBITEMLICITACAOPORTAL
                WHERE   CLICPOPROC = $Processo
                        AND ALICPOANOP = $ProcessoAno
                        AND CGREMPCODI = $Grupo
                        AND CCOMLICODI = $ComissaoCodigo
                        AND CORGLICODI = $OrgaoLicitanteCodigo
                        AND FITELPLOGR = 'S' ";
        
        $result = executarSQL($db, $sql);
        
        $Linha = $result->fetchRow();

        $TotalGeralEstimado = $Linha[0];
        
        // valor homologado
        $sql = "SELECT  SUM(VITELPVLOG * AITELPQTSO)
                FROM    SFPC.TBITEMLICITACAOPORTAL
                WHERE   CLICPOPROC = $Processo
                        AND ALICPOANOP = $ProcessoAno
                        AND CGREMPCODI = $Grupo
                        AND CCOMLICODI = $ComissaoCodigo
                        AND CORGLICODI = $OrgaoLicitanteCodigo
                        AND FITELPLOGR = 'S' ";
        
        $result = executarSQL($db, $sql);
        
        $Linha = $result->fetchRow();

        $ValorHomologado = $Linha[0];
        
        // formata os valores
        $TotalGeralEstimado = number_format((float) $TotalGeralEstimado, 2, ",", ".");
        $ValorHomologado    = number_format((float) $ValorHomologado, 2, ",", ".");
    }
}

$resposta = "NÃO";

if ($licitacao_possui_solicitacao) {
    $resposta = "SIM";
}

// ComboBox Fase
if ($Botao == "Fase") {
    if ($ProcessoAnoComissaoOrgao == "") {
        adicionarMensagem("<a href=\"javascript: document.FaseLicitacao.ProcessoAnoComissaoOrgao.focus();\" class=\"titulo2\">Selecione um Processo (Processo/Ano)</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
    }
    
    if ($Mens == 0) {
        if ($FaseCodigo == FASE_LICITACAO_HOMOLOGACAO) {
            // Pega a Quantidade de Bloqueios
            $Grupo = $_SESSION['_cgrempcodi_'];

            $sql = "SELECT  COUNT(*)
                    FROM    SFPC.TBLICITACAOBLOQUEIOORCAMENT
                    WHERE   CLICPOPROC = $Processo
                            AND ALICPOANOP = $ProcessoAno
                            AND CGREMPCODI = $Grupo
                            AND CCOMLICODI = $ComissaoCodigo
                            AND CORGLICODI = $OrgaoLicitanteCodigo ";

            $result = executarSQL($db, $sql);
            
            $Linha = $result->fetchRow();
            
            $QtdBloqueios = $Linha[0];
        }
    }

    // Botão Incluir(transformado em homologação)
} elseif ($Botao == "IncluirHomologacao") {
    // Critica Homologacao    
    if (! $licitacao_possui_solicitacao) {
        $Mens     = 0;
        $Mensagem = "Informe: ";
        
        // add false no if não entrar na codição de número de bloqueio
        if (($QtdBloqueios == 0 and $RegistroPreco != "S" and !in_array($ModalidadeCodigo, array(4, 10, 11, 15,18, 20))) && false) {
            adicionarMensagem("Não foi informado nenhum Número de Bloqueio para este Processo", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
            $Homologacao = "N";
        } else {
            if ($ProcessoAnoComissaoOrgao == "") {
                adicionarMensagem("<a href=\"javascript: document.FaseLicitacao.ProcessoAnoComissaoOrgao.focus();\" class=\"titulo2\">Selecione um Processo (Processo/Ano)</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
            }
            
            if ($DataFase == "") {
                adicionarMensagem("<a href=\"javascript: document.FaseLicitacao.DataFase.focus();\" class=\"titulo2\">Data da Fase</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
            } else {
                $MensErro = ValidaData($DataFase);
                
                if ($MensErro != "") {
                    adicionarMensagem("<a href=\"javascript:document.FaseLicitacao.DataFase.focus();\" class=\"titulo2\">Data da Fase Válida</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                } else {
                    $dataAux = substr($DataFase, 6, 4) . "-" . substr($DataFase, 3, 2) . "-" . substr($DataFase, 0, 2);
                    
                    if ($dataAux > date("Y-m-d")) {
                        adicionarMensagem("<a href=\"javascript:document.FaseLicitacao.DataFase.focus();\" class=\"titulo2\">Data da Fase deve ser menor ou igual a hoje</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                    }
                }
            }
            
            if ($TotalGeralEstimado == "") {
                adicionarMensagem("<a href=\"javascript:document.FaseLicitacao.TotalGeralEstimado.focus();\" class=\"titulo2\">Total Geral Estimado</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
            } else {
                if (! validaMonetario($TotalGeralEstimado)) {
                    adicionarMensagem("<a href=\"javascript:document.FaseLicitacao.TotalGeralEstimado.focus();\" class=\"titulo2\">Total Geral Estimado Válido</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                }
            }
            
            if ($ValorHomologado == "") {
                adicionarMensagem("<a href=\"javascript:document.FaseLicitacao.ValorHomologado.focus();\" class=\"titulo2\">Valor Homologado</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
            } else {
                if (! validaMonetario($ValorHomologado)) {
                    adicionarMensagem("<a href=\"javascript:document.FaseLicitacao.ValorHomologado.focus();\" class=\"titulo2\">Valor Homologado Válido</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                    $Homologacao = "";
                }
                
                if ($Mens == 0) {
                    $Grupo = $_SESSION['_cgrempcodi_'];

                    // Pega o Valor estimado desse Processo Licitatório
                    $sql = "SELECT  VLICPOVALE
                            FROM    SFPC.TBLICITACAOPORTAL
                            WHERE   CLICPOPROC = $Processo
                                    AND ALICPOANOP = $ProcessoAno
                                    AND CGREMPCODI = $Grupo
                                    AND CCOMLICODI = $ComissaoCodigo
                                    AND CORGLICODI = $OrgaoLicitanteCodigo ";
                    
                    $result = executarSQL($db, $sql);
                    
                    $Linha = $result->fetchRow();
                    
                    if ($Linha[0] == "") {
                        $ValorEstimado = "0,00";
                    } else {
                        $ValorEstimado = converte_valor($Linha[0]);
                    }
                    
                    if ($ValorHomologado == "" or $ValorHomologado == 0) {
                        $ValorHomologadoDepois = "0,00";
                    } else {
                        $ValorHomologadoDepois = sprintf("%01.2f", str_replace(",", ".", $ValorHomologado));
                        $ValorHomologadoDepois = converte_valor($ValorHomologadoDepois);
                    }
                    
                    if ($ValorHomologadoDepois != $ValorEstimado) {
                        $Homologacao = "S";
                    } else {
                        $Homologacao = "";
                        $Botao = "Incluir";
                    }
                }
            }
        }
    } else {
        $Homologacao = "";
        $Botao = "Incluir";
    }
}

// Botão Incluir
if ($Botao == "Incluir") {
    // Critica Geral
    if (! $licitacao_possui_solicitacao) {
        // Critica dos Campos
        $Mens = 0;
        $Mensagem = "Informe: ";
        
        if ($ProcessoAnoComissaoOrgao == "") {
            adicionarMensagem("<a href=\"javascript: document.FaseLicitacao.ProcessoAnoComissaoOrgao.focus();\" class=\"titulo2\">Processo Licitatório(Processo/Ano)</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
        }
        
        if ($DataFase == "") {
            adicionarMensagem("<a href=\"javascript: document.FaseLicitacao.DataFase.focus();\" class=\"titulo2\">Data da Fase</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
        } else {
            $MensErro = ValidaData($DataFase);
            if ($MensErro != "") {
                adicionarMensagem("<a href=\"javascript:document.FaseLicitacao.DataFase.focus();\" class=\"titulo2\">Data da Fase Válida</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
            }
            
            $dataAux = substr($DataFase, 6, 4) . "-" . substr($DataFase, 3, 2) . "-" . substr($DataFase, 0, 2);
            
            if ($dataAux > date("Y-m-d")) {
                adicionarMensagem("<a href=\"javascript:document.FaseLicitacao.DataFase.focus();\" class=\"titulo2\">Data da Fase deve ser menor ou igual a hoje</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
            }
        }
        
        if ($FaseCodigo == FASE_LICITACAO_REVOGACAO || $FaseCodigo == FASE_LICITACAO_ANULACAO || $FaseCodigo == FASE_LICITACAO_CANCELAMENTO) {
            if (empty($FaseLicitacaoDetalhe)) {
                adicionarMensagem("<a href=\"javascript: document.FaseLicitacao.FaseLicitacaoDetalhe.focus();\" class=\"titulo2\">Falta preencher detalhe</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
            }
        }
        
        if (strlen($FaseLicitacaoDetalhe) > 1000) {
            adicionarMensagem("Detalhamento da Fase com até 1000 Caracteres ( atualmente com " . strlen($FaseLicitacaoDetalhe) . " )", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
        }
        
        if ($FaseCodigo == FASE_LICITACAO_HOMOLOGACAO) {
            if ($DataFase == "") {
                adicionarMensagem("<a href=\"javascript: document.FaseLicitacao.DataFase.focus();\" class=\"titulo2\">Data da Fase</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
            } else {
                $MensErro = ValidaData($DataFase);
                
                if ($MensErro != "") {
                    adicionarMensagem("<a href=\"javascript:document.FaseLicitacao.DataFase.focus();\" class=\"titulo2\">Data da Fase Válida</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                } else {
                    $dataAux = substr($DataFase, 6, 4) . "-" . substr($DataFase, 3, 2) . "-" . substr($DataFase, 0, 2);
                    
                    if ($dataAux > date("Y-m-d")) {
                        adicionarMensagem("<a href=\"javascript:document.FaseLicitacao.DataFase.focus();\" class=\"titulo2\">Data da Fase deve ser menor ou igual a hoje</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                    }
                }
            }
            
            if ($ValorHomologado == "") {
                adicionarMensagem("<a href=\"javascript:document.FaseLicitacao.ValorHomologado.focus();\" class=\"titulo2\">Valor Homologado</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
            } else {
                if (! validaMonetario($ValorHomologado)) {
                    adicionarMensagem("<a href=\"javascript:document.FaseLicitacao.ValorHomologado.focus();\" class=\"titulo2\">Valor Homologado Válido</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                    $Homologacao = "";
                }
            }
            
            if ($TotalGeralEstimado == "") {
                adicionarMensagem("<a href=\"javascript:document.FaseLicitacao.TotalGeralEstimado.focus();\" class=\"titulo2\">Total Geral Estimado</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
            } else {
                if (! validaMonetario($TotalGeralEstimado)) {
                    adicionarMensagem("<a href=\"javascript:document.FaseLicitacao.TotalGeralEstimado.focus();\" class=\"titulo2\">Total Geral Estimado Válido</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                }
            }
        }
    } else {
        // Verificar se a fase de publicação tem lotes
        if ($FaseCodigo == FASE_LICITACAO_PUBLICACAO) {
            $sql = "SELECT  COUNT(*)
			        FROM    SFPC.TBITEMLICITACAOPORTAL
			        WHERE   CITELPNUML IS NULL
                            AND (CLICPOPROC, ALICPOANOP, CGREMPCODI, CCOMLICODI, CORGLICODI) IN ((" . $Processo . "," . $ProcessoAno . "," . $_SESSION['_cgrempcodi_'] . "," . $ComissaoCodigo . "," . $OrgaoLicitanteCodigo . ")) ";
            
            $res = executarSQL($db, $sql);
            
            $linha = $res->fetchRow();
            
            $qtdeItensSemLote = $linha[0];
            
            if ($qtdeItensSemLote > 0) {
                 adicionarMensagem("É necessário atribuir Lotes a todos os itens da licitação antes da publicação", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                }

                     $Grupo = $_SESSION['_cgrempcodi_'];
                    //verifica se tem algum documento relacionado.
                    $sql = "SELECT count(*)  FROM sfpc.tbdocumentolicitacao
                    WHERE (clicpoproc, alicpoanop, cgrempcodi,  ccomlicodi,  corglicodi) IN ((" . $Processo . "," . $ProcessoAno . "," . $Grupo . "," . $ComissaoCodigo . "," . $OrgaoLicitanteCodigo . ")) ";
                    $res = executarSQL($db,$sql);

                    $fasePublicacaoLinha = $res->fetchRow();
                    
                    $verificaAnexos = $fasePublicacaoLinha[0];

                    if($verificaAnexos == 0){
                        adicionarMensagem("O processo precisa ter anexos", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                    }

         }
        $Grupo = $_SESSION['_cgrempcodi_'];

       

        // Verifica se houve solicitacao de compra c situacao diferente de 9 (Em Licitacao)
        $sql = "SELECT  COUNT(*) AS QTDNAOLICITACAO
                FROM    SFPC.TBLICITACAOPORTAL L, SFPC.TBSOLICITACAOLICITACAOPORTAL SL, SFPC.TBSOLICITACAOCOMPRA SC
                WHERE   L.CLICPOPROC = $Processo
                        AND L.ALICPOANOP = $ProcessoAno
                        AND L.CGREMPCODI = $Grupo
                        AND L.CCOMLICODI = $ComissaoCodigo
                        AND L.CORGLICODI = $OrgaoLicitanteCodigo
                        AND L.CLICPOPROC = SL.CLICPOPROC
                        AND L.ALICPOANOP = SL.ALICPOANOP
                        AND L.CGREMPCODI = SL.CGREMPCODI
                        AND L.CCOMLICODI = SL.CCOMLICODI
                        AND L.CORGLICODI = SL.CORGLICODI
                        AND SL.CSOLCOSEQU = SC.CSOLCOSEQU
                        AND SC.CSITSOCODI <> 9 ";
        
        $result = executarSQL($db, $sql);

        $row = $result->fetchRow(DB_FETCHMODE_OBJECT);

        $qtdNaoLicitacao = $row->qtdnaolicitacao;
        
        // Verifica se houve fase cancelada
        $sql = "SELECT  COUNT(*) AS QTDCANCELADA
                FROM    SFPC.TBFASELICITACAO F
                WHERE   F.CLICPOPROC = $Processo
                        AND F.ALICPOANOP = $ProcessoAno
                        AND F.CGREMPCODI = $Grupo
                        AND F.CCOMLICODI = $ComissaoCodigo
                        AND F.CORGLICODI = $OrgaoLicitanteCodigo
                        AND F.CFASESCODI IN (11,12,17) ";
        
        $result = executarSQL($db, $sql);

        $row = $result->fetchRow(DB_FETCHMODE_OBJECT);

        $qtdCancelada = $row->qtdcancelada;
        
        // Verifica se houve resultado da licitacao
        $sql = "SELECT  L.FLICPORESU AS RESULTADO
                FROM    SFPC.TBLICITACAOPORTAL L
                WHERE   L.CLICPOPROC = $Processo
                        AND L.ALICPOANOP = $ProcessoAno
                        AND L.CGREMPCODI = $Grupo
                        AND L.CCOMLICODI = $ComissaoCodigo
                        AND L.CORGLICODI = $OrgaoLicitanteCodigo ";
        
        $result = executarSQL($db, $sql);

        $row = $result->fetchRow(DB_FETCHMODE_OBJECT);

        $resultado = $row->resultado;
        
        // Verifica se houve pelo menos uma solicitacao não registro de preco
        $sql = "SELECT  COUNT(*) AS QTDNAOREGCOMPRA
                FROM    SFPC.TBLICITACAOPORTAL L, SFPC.TBSOLICITACAOLICITACAOPORTAL SL, SFPC.TBSOLICITACAOCOMPRA SC
                WHERE   L.CLICPOPROC = $Processo
                        AND L.ALICPOANOP = $ProcessoAno
                        AND L.CGREMPCODI = $Grupo
                        AND L.CCOMLICODI = $ComissaoCodigo
                        AND L.CORGLICODI = $OrgaoLicitanteCodigo
                        AND L.CLICPOPROC = SL.CLICPOPROC
                        AND L.ALICPOANOP = SL.ALICPOANOP
                        AND L.CGREMPCODI = SL.CGREMPCODI
                        AND L.CCOMLICODI = SL.CCOMLICODI
                        AND L.CORGLICODI = SL.CORGLICODI
                        AND SL.CSOLCOSEQU = SC.CSOLCOSEQU
                        AND SC.FSOLCORGPR <> 'S' ";
        
        $result = executarSQL($db, $sql);

        $row = $result->fetchRow(DB_FETCHMODE_OBJECT);

        $qtdnaoregcompra = $row->qtdnaoregcompra;
        
        if ($ProcessoAnoComissaoOrgao == "") {
            adicionarMensagem("<a href=\"javascript: document.FaseLicitacao.ProcessoAnoComissaoOrgao.focus();\" class=\"titulo2\">Processo Licitatório(Processo/Ano)</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
        }
        
        if ($DataFase == "") {
            adicionarMensagem("<a href=\"javascript: document.FaseLicitacao.DataFase.focus();\" class=\"titulo2\">Data da Fase</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
        } else {
            $MensErro = ValidaData($DataFase);
            
            if ($MensErro != "") {
                adicionarMensagem("<a href=\"javascript:document.FaseLicitacao.DataFase.focus();\" class=\"titulo2\">Data da Fase Válida</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
            } else {
                $dataAux = substr($DataFase, 6, 4) . "-" . substr($DataFase, 3, 2) . "-" . substr($DataFase, 0, 2);
                
                if ($dataAux > date("Y-m-d")) {
                    adicionarMensagem("<a href=\"javascript:document.FaseLicitacao.DataFase.focus();\" class=\"titulo2\">Data da Fase deve ser menor ou igual a hoje</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                }
            }
        }
        
        if ($FaseCodigoDesc == "") {
            adicionarMensagem("<a href=\"javascript:document.FaseLicitacao.FaseCodigoDesc.focus();\" class=\"titulo2\">Fase</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
        }
        
        if (strlen($FaseLicitacaoDetalhe) > 1000) {
            adicionarMensagem("Detalhamento da Fase com até 1000 Caracteres ( atualmente com " . strlen($FaseLicitacaoDetalhe) . " )", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
        }
        
        if ($FaseCodigo == FASE_LICITACAO_REVOGACAO || $FaseCodigo == FASE_LICITACAO_ANULACAO || $FaseCodigo == FASE_LICITACAO_CANCELAMENTO) {
            if (empty($FaseLicitacaoDetalhe)) {
                adicionarMensagem("<a href=\"javascript: document.FaseLicitacao.FaseLicitacaoDetalhe.focus();\" class=\"titulo2\">Falta preencher detalhe</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
            }
            
            if ($qtdNaoLicitacao > 0 && $codigoUsuario != 2 && $codigoUsuario != 6) {
                adicionarMensagem("<a href=\"javascript: document.FaseLicitacao.ProcessoAnoComissaoOrgao.focus();\" class=\"titulo2\">Situação da solicitação diferente de 'Em licitação', não podendo ser anulada, Revogada e nem Cancelada</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
            }
            
            if ($qtdHomologacao > 0) {
                adicionarMensagem("<a href=\"javascript: document.FaseLicitacao.ProcessoAnoComissaoOrgao.focus();\" class=\"titulo2\">Licitação na fase de Homologação não poderá ser Revogada, Anulada ou Cancelada</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
            }
        }
        
        if ($FaseCodigo == FASE_LICITACAO_HOMOLOGACAO) {
            if ($ValorHomologado == "0,00") {
                adicionarMensagem("<a href=\"javascript:document.FaseLicitacao.ValorHomologadoo.focus();\" class=\"titulo2\">Valor Homologado não pode ser zero</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
            }
            
            if ($TotalGeralEstimado == "0,00") {
                adicionarMensagem("<a href=\"javascript:document.FaseLicitacao.TotalGeralEstimado.focus();\" class=\"titulo2\">Total Geral Estimado não pode ser zero</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
            }
            
            if ($resultado != "S") {
                adicionarMensagem("<a href=\"javascript: document.FaseLicitacao.ProcessoAnoComissaoOrgao.focus();\" class=\"titulo2\">Licitação sem resultado informado</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
            }
            
            if ($ValorHomologado == "") {
                adicionarMensagem("<a href=\"javascript:document.FaseLicitacao.ValorHomologado.focus();\" class=\"titulo2\">Valor Homologado</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
            } else {
                if (! validaMonetario($ValorHomologado)) {
                    adicionarMensagem("<a href=\"javascript:document.FaseLicitacao.ValorHomologado.focus();\" class=\"titulo2\">Valor Homologado Válido</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                    $Homologacao = "";
                }
            }
            
            if ($TotalGeralEstimado == "") {
                adicionarMensagem("<a href=\"javascript:document.FaseLicitacao.TotalGeralEstimado.focus();\" class=\"titulo2\">Total Geral Estimado</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
            } else {
                if (! validaMonetario($TotalGeralEstimado)) {
                    adicionarMensagem("<a href=\"javascript:document.FaseLicitacao.TotalGeralEstimado.focus();\" class=\"titulo2\">Total Geral Estimado Válido</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                }
            }
        }
    }

    // Critica Duplicidade
    if ($Mens == 0) {
        $Grupo = $_SESSION['_cgrempcodi_'];

        $sql = "SELECT  COUNT(CFASESCODI)
                FROM    SFPC.TBFASELICITACAO
                WHERE   CFASESCODI = $FaseCodigo
                        AND CLICPOPROC = $Processo
                        AND ALICPOANOP = $ProcessoAno
                        AND CCOMLICODI = $ComissaoCodigo
                        AND CGREMPCODI = $Grupo
                        AND CORGLICODI = $OrgaoLicitanteCodigo ";
        
        $result = executarSQL($db, $sql);
        
        $Linha = $result->fetchRow();
        
        if ($Linha[0] > 0) {
            adicionarMensagem("<a href=\"javascript:document.FaseLicitacao.FaseCodigoDesc.focus();\" class=\"titulo2\">Fase da Licitação Já Cadastrada</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
        }
    }
    
    // Se Critica=0 e Licitacao com Solicitacao exibir tela pedindo confirmação    
    if ($Mens == 0 && $licitacao_possui_solicitacao) {
        include_once 'CadFaseLicitacaoIncluirConfirmacao.php'; // licitacoes novas
        exit();
    } else {
        // PROCESSO PARA INCLUIR FASE        
        if ($Mens == 0) {
            $Homologacao = "";
            $Mensagem = "";
            
            if (($FaseCodigo == FASE_LICITACAO_CANCELAMENTO) and ($RegistroPreco != 'S')) {} else {
                $Data = date("Y-m-d H:i:s");
                // Insere FaseLicitacao
                $sql = "INSERT  INTO SFPC.TBFASELICITACAO
                                (CFASESCODI, CLICPOPROC, ALICPOANOP, CGREMPCODI, CCOMLICODI, CORGLICODI, EFASELDETA, TFASELDATA, CUSUPOCODI, TFASELULAT)
                        VALUES  ($FaseCodigo, $Processo, $ProcessoAno, " . $_SESSION['_cgrempcodi_'] . ", $ComissaoCodigo, $OrgaoLicitanteCodigo, '$FaseLicitacaoDetalhe', '" . $DataFase . "', " . $_SESSION['_cusupocodi_'] . ", '$Data') ";
                
                executarTransacao($db, $sql);
                
                if ($FaseCodigo == 2) {
                    $Grupo   = $_SESSION['_cgrempcodi_'];
                    $Usuario = $_SESSION['_cusupocodi_'];

                    $sql = "UPDATE  SFPC.TBLICITACAOPORTAL
                            SET     FLICPOSTAT = 'A',
                                    CUSUPOCODI = $Usuario,
                                    TLICPOULAT = '$Data'
                            WHERE   CLICPOPROC = $Processo
                                    AND ALICPOANOP = $ProcessoAno
                                    AND CCOMLICODI = $ComissaoCodigo
                                    AND CGREMPCODI = $Grupo ";

                    executarTransacao($db, $sql);
                } else {
                    // Se for a fase de Homologação
                    if ($FaseCodigo == FASE_LICITACAO_HOMOLOGACAO) {
                        $ValorHomologadoAux    = str_replace(".", "", $ValorHomologado);
                        $TotalGeralEstimadoAux = str_replace(".", "", $TotalGeralEstimado);
                        $ValorHomologadoAux    = str_replace(",", ".", $ValorHomologadoAux);
                        $TotalGeralEstimadoAux = str_replace(",", ".", $TotalGeralEstimadoAux);
                        
                        $Grupo   = $_SESSION['_cgrempcodi_'];
                        $Usuario = $_SESSION['_cusupocodi_'];
                        
                        // Atualiza a Licitação para fase de Homologação
                        $sql = "UPDATE  SFPC.TBLICITACAOPORTAL
                                SET     VLICPOVALH = $ValorHomologadoAux,
                                        VLICPOTGES = $TotalGeralEstimadoAux,
                                        CUSUPOCODI = $Usuario,
                                        TLICPOULAT = '$Data'
                                WHERE   CLICPOPROC = $Processo
                                        AND ALICPOANOP = $ProcessoAno
                                        AND CCOMLICODI = $ComissaoCodigo
                                        AND CGREMPCODI = $Grupo ";
                        
                        executarTransacao($db, $sql);
                    }
                }
                finalizarTransacao($db);

                if ($FaseCodigo == FASE_LICITACAO_PUBLICACAO) {
                    adicionarMensagem("Fase da Licitação Incluída com Sucesso e foi ativada a Exibição da Licitação na Internet. Um e-mail de aviso foi enviado para todos os fornecedores cadastrados no SICREF incluídos nos grupos dos itens cadastrados deste processo", $GLOBALS["TIPO_MENSAGEM_ATENCAO"]);
                } else {
                    adicionarMensagem("Fase da Licitação Incluída com Sucesso", $GLOBALS["TIPO_MENSAGEM_ATENCAO"]);
                }
                
                // Limpando os Campos #
                $ProcessoAnoComissaoOrgao = "";
                $Processo                 = "";
                $ProcessoAno              = "";
                $OrgaoLicitanteCodigo     = "";
                $ModalidadeCodigo         = "";
                $RegistroPreco            = "";
                $FaseCodigoDesc           = "";
                $DataFase                 = "";
                $FaseLicitacaoDetalhe     = "";
                $NCaracteres              = "";
                $FaseCodigo               = "";
            }
        }
    }
}

// Botão Confimado
if ($Botao == "Confirmado") {
    // Verifica Duplicidade
    if ($Mens == 0) {
        $Homologacao = "";
        $Mensagem    = "";
        
        // Inserir fase
        $Data = date("Y-m-d H:i:s");

        $sql = "INSERT  INTO SFPC.TBFASELICITACAO
                        (CFASESCODI, CLICPOPROC, ALICPOANOP, CGREMPCODI, CCOMLICODI, CORGLICODI, EFASELDETA, TFASELDATA, CUSUPOCODI,TFASELULAT)
                VALUES  ($FaseCodigo, $Processo, $ProcessoAno, " . $_SESSION['_cgrempcodi_'] . ",$ComissaoCodigo, $OrgaoLicitanteCodigo, '$FaseLicitacaoDetalhe', '" . $DataFase . "', " . $_SESSION['_cusupocodi_'] . ", now()) ";

        executarTransacao($db, $sql);

        // Se processo se for uma pulicacao
        if ($FaseCodigo == FASE_LICITACAO_PUBLICACAO) {
            $Grupo   = $_SESSION['_cgrempcodi_'];
            $Usuario = $_SESSION['_cusupocodi_'];

            $sql = "UPDATE  SFPC.TBLICITACAOPORTAL
                    SET     FLICPOSTAT = 'A',
                            CUSUPOCODI = $Usuario,
                            TLICPOULAT = '$Data'
                    WHERE   CLICPOPROC = $Processo
                            AND ALICPOANOP = $ProcessoAno
                            AND CCOMLICODI = $ComissaoCodigo
                            AND CGREMPCODI = $Grupo ";
            
            executarTransacao($db, $sql);
            
            // Carrega dados da licitação necessários para o e-mail de aviso de publicação de licitação
            $sq2 = "SELECT  CL.ECOMLIDESC, OL.EORGLIDESC, ML.EMODLIDESC, LP.CLICPOCODL, LP.ALICPOANOL, LP.TLICPODHAB, LP.XLICPOOBJE, LP.FLICPOVFOR, LP.FLICPODEMC 
                    FROM    SFPC.TBLICITACAOPORTAL LP 
                            LEFT JOIN SFPC.TBCOMISSAOLICITACAO CL ON LP.CCOMLICODI = CL.CCOMLICODI 
                            LEFT JOIN SFPC.TBORGAOLICITANTE OL ON LP.CORGLICODI = OL.CORGLICODI 
                            LEFT JOIN SFPC.TBMODALIDADELICITACAO ML ON LP.CMODLICODI = ML.CMODLICODI 
                    WHERE   LP.CLICPOPROC = $Processo 
                            AND LP.ALICPOANOP = $ProcessoAno
                            AND LP.CGREMPCODI = $Grupo
                            AND LP.CCOMLICODI = $ComissaoCodigo 
                            AND LP.CORGLICODI = $OrgaoLicitanteCodigo ";

            $result2 = executarTransacao($db, $sq2);

            $Linha = $result2->fetchRow();

            $DescComissao               = $Linha[0];
            $DescOrgao                  = $Linha[1];
            $DescModalicdade            = $Linha[2];
            $LicitacaoCodigo            = $Linha[3];
            $LicitacaoAno               = $Linha[4];
            $DataHoraAbertura           = $Linha[5];
            $Objeto                     = $Linha[6];
            $FlagTratamentoDiferenciado = $Linha[7];
            $FlagValidacaoFornecedor    = $Linha[8];

            $DescTratamentoDiferencido = getDescricaoTratamentoDiferenciado($FlagTratamentoDiferenciado);

            if ($FlagValidacaoFornecedor == 'S') {
                $NecessidadeContabeis = 'Sim';
            } else {
                $NecessidadeContabeis = 'Não';
            }

            // Carrega os itens da licitação
            $sqI = "SELECT  CMATEPSEQU, CSERVPSEQU
                    FROM    SFPC.TBITEMLICITACAOPORTAL
                    WHERE   CLICPOPROC = $Processo
                            AND ALICPOANOP = $ProcessoAno
                            AND CGREMPCODI = $Grupo
                            AND CCOMLICODI = $ComissaoCodigo
                            AND CORGLICODI = $OrgaoLicitanteCodigo";
            
            $resultI = executarTransacao($db, $sqI);
            
            while ($row = $resultI->fetchRow(DB_FETCHMODE_OBJECT)) {
                $materialI = $row->cmatepsequ;
                $servicoI  = $row->cservpsequ;
            

            // Carrega dados dos fornecedores cadastrados nos grupos dos itens da licitação, que são necessários para o e-mail de aviso de publicação de licitação
            $sq3 = "SELECT  DISTINCT FC.AFORCRSEQU, FC.NFORCRMAIL, FC.NFORCRMAI2
                    FROM    SFPC.TBFORNECEDORCREDENCIADO FC
                            LEFT JOIN SFPC.TBGRUPOFORNECEDOR GF ON FC.AFORCRSEQU = GF.AFORCRSEQU ";
                    if ($materialI) {            
                        $sq3 .="LEFT JOIN SFPC.TBSUBCLASSEMATERIAL SCM ON GF.CGRUMSCODI = SCM.CGRUMSCODI
                                LEFT JOIN SFPC.TBMATERIALPORTAL MP ON SCM.CSUBCLSEQU = MP.CSUBCLSEQU
                                LEFT JOIN SFPC.TBITEMLICITACAOPORTAL ILP ON MP.CMATEPSEQU = ILP.CMATEPSEQU ";
                    } elseif ($servicoI) {
                        $sq3 .= "LEFT JOIN SFPC.TBSERVICOPORTAL SP ON GF.CGRUMSCODI = SP.CGRUMSCODI
                                LEFT JOIN SFPC.TBITEMLICITACAOPORTAL ILP ON SP.CSERVPSEQU = ILP.CSERVPSEQU ";
                    }
            $sq3 .= "
                    WHERE   ILP.CLICPOPROC = $Processo
                            AND ILP.ALICPOANOP = $ProcessoAno
                            AND ILP.CGREMPCODI = $Grupo
                            AND ILP.CCOMLICODI = $ComissaoCodigo
                            AND ILP.CORGLICODI = $OrgaoLicitanteCodigo
                    ORDER BY FC.AFORCRSEQU ASC";

            $result3 = executarTransacao($db, $sq3);

            $Linha = $result3->fetchRow();

            $CodFornecedor = $Linha[0];
            $EmailForn1    = $Linha[1];
            $EmailForn2    = $Linha[2];

            $DataHoraAbertura2 = substr($DataHoraAbertura, 8, 2) . '/' . substr($DataHoraAbertura, 5, 2) . '/' . substr($DataHoraAbertura, 0, 4) . ' - ' . substr($DataHoraAbertura, 11, 5);

            // Envia e-mail para os fornecedores
            // Kim
           /*  EnviaEmail ($EmailForn1,
                        "PREFEITURA DO RECIFE - AVISO DE PUBLICAÇÃO DE LICITAÇÃO",
                        "A Prefeitura do Recife informa a publicação da seguinte licitação: \n\nÓrgão: $DescOrgao\nProcesso: $Processo/$ProcessoAno - $DescComissao\nModalidade: $DescModalicdade\nLicitação: $LicitacaoCodigo/$LicitacaoAno\nData e hora de abertura: $DataHoraAbertura2\nObjeto: $Objeto\n\nPara mais informações, acesse o Portal de Compras da Prefeitura do Recife através do site: www.recife.pe.gov.br/portalcompras/app/ConsAvisosPesquisar.php ",
                        "from: portalcompras@recife.pe.gov.br");

            EnviaEmail ($EmailForn2,
                        "PREFEITURA DO RECIFE - AVISO DE PUBLICAÇÃO DE LICITAÇÃO",
                        "A Prefeitura do Recife informa a publicação da seguinte licitação: \n\nÓrgão: $DescOrgao\nProcesso: $Processo/$ProcessoAno - $DescComissao\nModalidade: $DescModalicdade\nLicitação: $LicitacaoCodigo/$LicitacaoAno\nData e hora de abertura: $DataHoraAbertura2\nObjeto: $Objeto\n\nPara mais informações, acesse o Portal de Compras da Prefeitura do Recife através do site: www.recife.pe.gov.br/portalcompras/app/ConsAvisosPesquisar.php ",
                        "from: portalcompras@recife.pe.gov.br"); */
            }
        } elseif ($FaseCodigo == FASE_LICITACAO_HOMOLOGACAO) {
            // Processo se for uma homologacao
            $ValorHomologadoAux    = str_replace(".", "", $ValorHomologado);
            $TotalGeralEstimadoAux = str_replace(".", "", $TotalGeralEstimado);
            $ValorHomologadoAux    = str_replace(",", ".", $ValorHomologadoAux);
            $TotalGeralEstimadoAux = str_replace(",", ".", $TotalGeralEstimadoAux);

            $Grupo   = $_SESSION['_cgrempcodi_'];
            $Usuario = $_SESSION['_cusupocodi_'];
            
            // Atualiza a Licitação para fase de Homologação #
            $sql = "UPDATE  SFPC.TBLICITACAOPORTAL
                    SET     VLICPOVALH = $ValorHomologadoAux,
                            VLICPOTGES = $TotalGeralEstimadoAux, 
                            CUSUPOCODI = $Usuario,
                            TLICPOULAT = '$Data' 
                    WHERE   CLICPOPROC = $Processo
                            AND ALICPOANOP = $ProcessoAno 
                            AND CCOMLICODI = $ComissaoCodigo
                            AND CGREMPCODI = $Grupo ";
            
            executarTransacao($db, $sql);
            
            // Verificar a quantidade de itens de material na solicitacao de compras
            $sql = "SELECT  COUNT(*) AS QTDITENS
                    FROM    SFPC.TBSOLICITACAOLICITACAOPORTAL S,
                            SFPC.TBITEMSOLICITACAOCOMPRA I
                    WHERE   S.CLICPOPROC = $Processo
                            AND S.ALICPOANOP = $ProcessoAno
                            AND S.CGREMPCODI = $Grupo
                            AND S.CCOMLICODI = $ComissaoCodigo
                            AND S.CORGLICODI = $OrgaoLicitanteCodigo
                            AND S.CSOLCOSEQU = I.CSOLCOSEQU
                            AND I.CMATEPSEQU IS NOT NULL ";

            $result = executarTransacao($db, $sql);
            
            $row = $result->fetchRow(DB_FETCHMODE_OBJECT);

            $qtdItens = $row->qtditens;
            
            // Acumular os sequenciais da solicitacao caso existam solicitacoes associadas em vetor $vetorSolicitacoes
            $sql = "SELECT  S.CSOLCOSEQU AS SEQSOL
                    FROM    SFPC.TBSOLICITACAOLICITACAOPORTAL S
                    WHERE   S.CLICPOPROC = $Processo
                            AND S.ALICPOANOP = $ProcessoAno
                            AND S.CGREMPCODI = $Grupo
                            AND S.CCOMLICODI = $ComissaoCodigo
                            AND S.CORGLICODI = $OrgaoLicitanteCodigo ";
            
            $result = executarTransacao($db, $sql);
            
            $i = 0;
            
            $vetorSolicitacoes = array();
            
            while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
                $vetorSolicitacoes[$i] = $row->seqsol;
                $i                     = $i + 1;
            }
            
            // Capturar indicadores de registro de preço, geração de contrato e modalidade
            $sql = "SELECT  FLICPOREGP, FLICPOCONT, CMODLICODI
                    FROM    SFPC.TBLICITACAOPORTAL 
                    WHERE   CLICPOPROC = $Processo
                            AND alicpoanop = $ProcessoAno
                            AND cgrempcodi = $Grupo
                            AND ccomlicodi = $ComissaoCodigo
                            AND corglicodi = $OrgaoLicitanteCodigo ";

            $resultAux = executarTransacao($db, $sql);
            
            $Linha = $resultAux->fetchRow();

            $RegistroPreco     = $Linha[0];
            $GeracaoDeContrato = $Linha[1];
            $modalidade        = $Linha[2];
            
            // Atualizar as situaçoes das solicitacoes (2 = Concluído / 3 = Pendente de Empenho / 4 = Pendente de Contrato)
            if ($RegistroPreco == "S") {
                $situacaoAux = $GLOBALS['TIPO_SITUACAO_SCC_CONCLUIDA'];
            } else {
                if ($GeracaoDeContrato != "S") {
                    $situacaoAux = $GLOBALS['TIPO_SITUACAO_SCC_PENDENTE_DE_EMPENHO'];
                } else {
                    $situacaoAux = $GLOBALS['TIPO_SITUACAO_SCC_PENDENTE_DE_CONTRATO'];
                }
            }
            
            $sql = "UPDATE  SFPC.TBSOLICITACAOCOMPRA
                    SET     CSITSOCODI = $situacaoAux,
                            CUSUPOCOD1 = $Usuario,
                            TSOLCOULAT = now()
                    WHERE   CSOLCOSEQU IN ( SELECT  CSOLCOSEQU
                                            FROM    SFPC.TBSOLICITACAOLICITACAOPORTAL
                                            WHERE   CLICPOPROC = $Processo
                                                    AND ALICPOANOP = $ProcessoAno
                                                    AND CGREMPCODI = $Grupo
                                                    AND CCOMLICODI = $ComissaoCodigo
                                                    AND CORGLICODI = $OrgaoLicitanteCodigo) ";
            
            executarTransacao($db, $sql);
            
            // Inserir na tabela de historico
            $sql = "INSERT  INTO SFPC.TBHISTSITUACAOSOLICITACAO (CSOLCOSEQU, THSITSDATA, CSITSOCODI, XHSITSOBSE, CUSUPOCODI)
                    SELECT  CSOLCOSEQU, now(), $situacaoAux , 'alteracao de situacao', $Usuario
                    FROM    SFPC.TBSOLICITACAOLICITACAOPORTAL
                    WHERE   CLICPOPROC = $Processo
                            AND ALICPOANOP = $ProcessoAno
                            AND CGREMPCODI = $Grupo
                            AND CCOMLICODI = $ComissaoCodigo
                            AND CORGLICODI = $OrgaoLicitanteCodigo ";

            executarTransacao($db, $sql);
            
            // Inserir pre-solicitacao de empenho se não gerou contrato            
            if ($RegistroPreco != "S" and $GeracaoDeContrato != "S") {
                $Grupo   = $_SESSION['_cgrempcodi_'];

                $sql = "SELECT  CSOLCOSEQU AS CHAVESOLICITACAO
                        FROM    SFPC.TBSOLICITACAOLICITACAOPORTAL
                        WHERE   CLICPOPROC = $Processo
                                AND ALICPOANOP = $ProcessoAno
                                AND CGREMPCODI = $Grupo
                                AND CCOMLICODI = $ComissaoCodigo
                                AND CORGLICODI = $OrgaoLicitanteCodigo ";

                $result = executarTransacao($db, $sql);
                
                // Criar conexão Oracle
                while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
                    try {
                        $GLOBALS["iniciouTransacaoBanco"] = true;
                        // $dbPost=Conexao();
                        $dbOracle = ConexaoOracle();
                        $chaveSolicitacao = $row->chavesolicitacao;
                        gerarPreSolicitacaoEmpenho($db, $dbOracle, $chaveSolicitacao);
                        // $dbPost->disconnect();
                        $dbOracle->disconnect();
                    } catch (Excecao $e) {
                        $msgAux = $e->getMessage();
                        $Mens = 1;
                        $Tipo = 2;
                        $Mensagem = $e->getMessage();
                        cancelarTransacao($db);
                        break;
                    }
                }
            }
            
            if ($Mens == 0) { // Verificar se existe pelo menos um solicitacao cujo tipo não seja "licitacao"
                $Grupo   = $_SESSION['_cgrempcodi_'];
                
                $sql = "SELECT  COUNT(*) AS QTD
                        FROM    SFPC.TBSOLICITACAOCOMPRA S,
                                SFPC.TBSOLICITACAOLICITACAOPORTAL SL
                        WHERE   SL.CLICPOPROC = $Processo
                                AND SL.ALICPOANOP = $ProcessoAno
                                AND SL.CGREMPCODI = $Grupo
                                AND SL.CCOMLICODI = $ComissaoCodigo
                                AND SL.CORGLICODI = $OrgaoLicitanteCodigo
                                AND SL.CSOLCOSEQU =  S.CSOLCOSEQU 
                                AND S.CTPCOMCODI <> 2 ";

                $result = executarTransacao($db, $sql);
                
                $row = $result->fetchRow(DB_FETCHMODE_OBJECT);

                $qtdTipoNaoLicitacao = $row->qtd;
                
                // Inverter datas: ano atras e data da fase
                $umAnoAntesInvertido = date("Y-m-d");
                $dataFaseInvertida = date('Y-m-d', strtotime($DataFase));
                //$anoAux              = substr($hoje, 6, 4);
                //$umAnoAntesAux       = substr($hoje, 0, 2) . '/' . substr($hoje, 3, 2) . '/' . ($anoAux - 1);
                //$umAnoAntesAux       = SomaData(1, $umAnoAntesAux);
                //$umAnoAntesInvertido = DataInvertida($umAnoAntesAux);
                //$dataFaseInvertida   = DataInvertida($DataFase);
                
                // Inserir na TRP
                    // Display para verificar se será gravado na TRP
                    // Inibir depois de verificar                
                // Verificar se pode gravar TRP
                if (($modalidade == 14 or $modalidade == 5) and ($dataFaseInvertida >= $umAnoAntesInvertido)) {
                    // se (modalidade = 14 (pregao eletronico) ou 5 (pregao presencial)) e (data >= um ano antes))
                    inserirItensLicitacaoNaTrp($Processo, $ProcessoAno, $_SESSION['_cgrempcodi_'], $ComissaoCodigo, $OrgaoLicitanteCodigo, $dataFaseInvertida, $db);
                } elseif (($qtdItens > 0) and ($situacaoAux == $GLOBALS['TIPO_SITUACAO_SCC_PENDENTE_DE_EMPENHO'])) {
                    // senão se ((possui itens de materias na solicitacao > 0) e (situacao=pendente de empenho))
                    for ($i = 0; $i < count($vetorSolicitacoes); $i ++) {
                        inserirItensSCCNaTrp($vetorSolicitacoes[$i], $db);
                    }
                } else {
                    // Verificar itens de material da licitacao
                    $Grupo = $_SESSION['_cgrempcodi_'];

                    $sql = "SELECT  ILP.CITELPSEQU, ILP.CMATEPSEQU, ILP.VITELPVLOG, MATPORT.FMATEPNTRP
                            FROM    SFPC.TBITEMLICITACAOPORTAL ILP 
                                    INNER JOIN sfpc.tbmaterialportal matport ON matport.cmatepsequ = ilp.cmatepsequ 
                            WHERE   ILP.CLICPOPROC = $Processo
                                    AND ILP.ALICPOANOP = $ProcessoAno
                                    AND ILP.CGREMPCODI = $Grupo
                                    AND ILP.CCOMLICODI = $ComissaoCodigo
                                    AND ILP.CORGLICODI = $OrgaoLicitanteCodigo
                                    AND ILP.CMATEPSEQU IS NOT NULL
                                    AND ILP.VITELPVLOG > 0 ";
                    
                    // Varrer itens e atualizar na TRP
                    $result = executarTransacao($db, $sql);

                    while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
                        $codItem        = $row->citelpsequ;
                        $codMaterial    = $row->cmatepsequ;
                        $valor          = $row->vitelpvlog;
                        $naoGravarNaTRP = $row->fmatepntrp;
                        $vetor          = array($Processo, $ProcessoAno, $_SESSION['_cgrempcodi_'], $ComissaoCodigo, $OrgaoLicitanteCodigo, $codItem, $codMaterial, $valor, $dataFaseInvertida, $naoGravarNaTRP);
                        if (CR307::verificarNovaRegraIncluirTRP($db, $vetor)) {
                            unset($vetor[9]);
                            CR307::gravaPrecoNaTRPAceite($db, $vetor);
                        } else {
                            unset($vetor[9]);
                            CR307::gravaPrecoNaTRPNulo($db, $vetor);
                        }
                    }
                }
            }
        }
       
        if ($Mens == 0) {
            finalizarTransacao($db);
            if ($FaseCodigo == FASE_LICITACAO_PUBLICACAO) {
                adicionarMensagem("Fase da Licitação Incluída com Sucesso e foi ativada a Exibição da Licitação na Internet. Um e-mail de aviso foi enviado para todos os fornecedores cadastrados no SICREF incluídos nos grupos dos itens cadastrados deste processo", $GLOBALS["TIPO_MENSAGEM_ATENCAO"]);
            } else {
                adicionarMensagem("Fase da Licitação Incluída com Sucesso", $GLOBALS["TIPO_MENSAGEM_ATENCAO"]);
            }
            
            // Limpando os Campos #
            $ProcessoAnoComissaoOrgao = "";
            $Processo                 = "";
            $ProcessoAno              = "";
            $OrgaoLicitanteCodigo     = "";
            $ModalidadeCodigo         = "";
            $RegistroPreco            = "";
            $FaseCodigoDesc           = "";
            $DataFase                 = "";
            $FaseLicitacaoDetalhe     = "";
            $NCaracteres              = "";
            $FaseCodigo               = "";
        }
    }
}

// GERANDO O HTML- INÍCIO
$template = new TemplatePaginaPadrao("templates/CadFaseLicitacaoIncluir.template.html", "Licitações > Fase Licitação > Incluir");

$descricao = "Para incluir uma nova Fase de Licitação, informe os dados abaixo e clique no botão 'Incluir'. Os itens obrigatórios estão com *.<br>";

if ($FaseCodigo == FASE_LICITACAO_HOMOLOGACAO) {
    $descricao .= "O Total Geral Estimado (itens que lograram êxito) é obtido através do somatório do produto do preço unitário dos itens que lograram êxito pelo seus respectivos quantitativos.";
}

$template->DESCRICAO = $descricao;

$Grupo = $_SESSION['_cgrempcodi_'];
$Usuario = $_SESSION['_cusupocodi_'];

// Mostra as licitações cadastradas 
$sql = "SELECT  A.CLICPOPROC, A.ALICPOANOP, A.CCOMLICODI, B.ECOMLIDESC, C.EGREMPDESC, A.CORGLICODI, A.CMODLICODI, A.FLICPOREGP 
        FROM    SFPC.TBLICITACAOPORTAL A, SFPC.TBCOMISSAOLICITACAO B, SFPC.TBGRUPOEMPRESA C, SFPC.TBUSUARIOCOMIS D 
        WHERE   D.CGREMPCODI = $Grupo
                AND D.CUSUPOCODI = $Usuario
                AND D.CCOMLICODI = A.CCOMLICODI
                AND A.CGREMPCODI = D.CGREMPCODI
                AND A.CCOMLICODI = B.CCOMLICODI
                AND B.CGREMPCODI = C.CGREMPCODI
                AND make_date(A.ALICPOANOP,1,1) > CURRENT_DATE - INTERVAL '5 YEARS' --CR 206442 make_date
        ORDER BY B.ECOMLIDESC ASC, A.CGREMPCODI ASC, A.ALICPOANOP DESC, A.CLICPOPROC DESC ";

$result = executarSQL($db, $sql);

$ComissaoCodigoAnt = "";

while ($Linha = $result->fetchRow()) {
    if ($Linha[2] != $ComissaoCodigoAnt) {
        $ComissaoCodigoAnt = $Linha[2];

        $template->LICITACAO_ITEM = $Linha[3];
        $template->LICITACAO_ITEM_VALOR = '';
        $template->LICITACAO_ITEM_ATRIBUTOS = '';
        $template->block("BLOCO_LICITACAO_ITEM");
    }
    
    $NProcesso = substr($Linha[0] + 10000, 1);

    $template->LICITACAO_ITEM = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $NProcesso . "/" . $Linha[1];
    $template->LICITACAO_ITEM_VALOR = $Linha[0] . "_" . $Linha[1] . "_" . $Linha[2] . "_" . $Linha[5] . "_" . $Linha[6] . "_" . $Linha[7];
    
    if ($ProcessoAnoComissaoOrgao == "$Linha[0]_$Linha[1]_$Linha[2]_$Linha[5]_$Linha[6]_$Linha[7]") {
        $template->LICITACAO_ITEM_ATRIBUTOS = 'selected';
    } else {
        $template->LICITACAO_ITEM_ATRIBUTOS = '';
    }
    $template->block("BLOCO_LICITACAO_ITEM");
}

$sql = "SELECT  CFASESCODI, EFASESDESC, AFASESORDE
        FROM    SFPC.TBFASES
        ORDER BY AFASESORDE ";

$result = executarSQL($db, $sql);

while ($Linha = $result->fetchRow()) {
    if ($Linha[0] != "19" || ($Linha[0] == "19" && $_SESSION["_fperficorp_"] == "S")) {
        $template->FASE_ITEM = $Linha[1];
        $template->FASE_ITEM_VALOR = $Linha[0] . "_" . $Linha[1];
        
        if ("$Linha[0]_$Linha[1]" == $FaseCodigoDesc) {
            $template->FASE_ITEM_ATRIBUTOS = 'selected';
        } else {
            $template->FASE_ITEM_ATRIBUTOS = '';
        }
        $template->block("BLOCO_FASE_ITEM");
    }
}

$template->DATA_FASE = $DataFase;

if ($FaseCodigo == FASE_LICITACAO_REVOGACAO || $FaseCodigo == FASE_LICITACAO_ANULACAO || $FaseCodigo == FASE_LICITACAO_CANCELAMENTO) {
    $template->DETALHE_OBRIGATORIO = "*";
}

$template->DETALHE_CAMPO = gerarTextArea("FaseLicitacao", "FaseLicitacaoDetalhe", $FaseLicitacaoDetalhe, 1000);

if ($FaseCodigo == FASE_LICITACAO_HOMOLOGACAO) {
    $template->TOTAL_LOGRADO = $TotalGeralEstimado;
    $template->TOTAL_HOMOLOGADO = $ValorHomologado;
    
    if ($licitacao_possui_solicitacao) {
        $template->TOTAL_LOGRADO_ATRIBUTO = "readonly";
        $template->TOTAL_HOMOLOGADO_ATRIBUTO = "";
    } else {
        $template->TOTAL_LOGRADO_ATRIBUTO = "";
        $template->TOTAL_HOMOLOGADO_ATRIBUTO = "";
    }
    $template->block("BLOCO_HOMOLOGACAO");
}

$template->PROCESSO       = $Processo;
$template->PROCESSO_ANO   = $ProcessoAno;
$template->COMISSAO       = $ComissaoCodigo;
$template->ORGAO          = $OrgaoLicitanteCodigo;
$template->MODALIDADE     = $ModalidadeCodigo;
$template->REGISTRO_PRECO = $RegistroPreco;
$template->QTD_BLOQUEIOS  = $QtdBloqueios;
$template->HOMOLOGACAO    = $Homologacao;
$template->FASE           = $FaseCodigo;

if ($FaseCodigo == FASE_LICITACAO_HOMOLOGACAO) {
    $template->BOTAO_INCLUIR_ACAO = 'IncluirHomologacao';
} else {
    $template->BOTAO_INCLUIR_ACAO = 'Incluir';
}

$Url = "CadFaseLicitacaoConfirmar.php?ProgramaOrigem=FaseLicitacao&ValorHomologado=$ValorHomologado&Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&ModalidadeCodigo=$ModalidadeCodigo";

if (!in_array($Url, $_SESSION['GetUrl'])) {
    $_SESSION['GetUrl'][] = $Url;
}

$template->URL_CONFIRMAR = $Url;

$template->show();
// GERANDO O HTML- FIM

$db->disconnect();
