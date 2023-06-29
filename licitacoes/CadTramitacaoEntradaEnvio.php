<?php
/**
 * Portal de Compras
 * 
 * Programa: CadTramitacaoEntradaEnvio.php
 * Autor:    Pitang Agile TI - Ernesto Ferreira
 * Data:     02/08/2018
 * Objetivo: Tarefa Redmine 199436
 * --------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     08/04/2019
 * Objetivo: Tarefa Redmine 213474
 * --------------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     21/05/2019
 * Objetivo: Tarefa Redmine 217241
 * --------------------------------------------------------------------------------------------------------------
 */

# Acesso ao arquivo de funções #
//include "../funcoes.php";

# Acesso ao arquivo de funções #
//require_once '../compras/funcoesCompras.php';
require_once 'funcoesTramitacao.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Critica               = $_POST['Critica'];
    $botao                 = $_POST['botao'];
    $numTramitacao         = $_POST['numTramitacao'];
    $acao                  = $_POST['acao'];
    $agenteDestino         = $_POST['agenteDestino'];
    $usuarioResponsavel    = $_POST['usuarioResponsavel'];
    $prazoAcao             = $_POST['prazoAcao'];
    $observacao            = $_POST['observacao'];
    $comissaoAtual         = $_POST['comissaoAtual'];
	$numProtocoloRetorno   = $_POST['numProtocoloRetorno'];
	$anoProtocoloRetorno   = $_POST['anoProtocoloRetorno'];
	$orgaoRetorno          = $_POST['orgaoRetorno'];
	$objetoRetorno         = $_POST['objetoRetorno'];
	$numerociRetorno       = $_POST['numerociRetorno'];
	$numeroOficioRetorno   = $_POST['numeroOficioRetorno'];
	$numeroSccRetorno      = $_POST['numeroSccRetorno'];
	$proLicitatorioRetorno = $_POST['proLicitatorioRetorno'];
	$acaoRetorno           = $_POST['acaoRetorno'];
	$origemRetorno         = $_POST['origemRetorno'];
	$DataEntradaIniRetorno = $_POST['DataEntradaIniRetorno'];
    $DataEntradaFimRetorno = $_POST['DataEntradaFimRetorno'];
    $anexoObrigatorio      = $_POST['anexoObrigatorio'];
    $window                = $_POST['window'];
    $receberProtocolo      = $_POST['receberProtocolo'];
    $DDocumento            = $_POST['DDocumento'];
} else {
    $Critica               = $_GET['Critica'];
    $botao                 = $_GET['botao'];
    $numTramitacao         = $_GET['numTramitacao']; 
    $window                = $_GET['window'];
    $receberProtocolo      = $_GET['receberProtocolo'];
	$numProtocoloRetorno   = $_GET['numProtocolo'];
	$anoProtocoloRetorno   = $_GET['anoProtocolo'];
	$orgaoRetorno          = $_GET['orgao'];
	$objetoRetorno         = $_GET['objeto'];
	$numerociRetorno       = $_GET['numeroci'];
	$numeroOficioRetorno   = $_GET['numeroOficio'];
	$numeroSccRetorno      = $_GET['numeroScc'];
	$proLicitatorioRetorno = $_GET['proLicitatorio'];
	$acaoRetorno           = $_GET['acao'];
	$origemRetorno         = $_GET['origem'];
	$DataEntradaIniRetorno = $_GET['DataEntradaIni'];
    $DataEntradaFimRetorno = $_GET['DataEntradaFim'];
}

if ($botao == 'Incluir_Documento') {
    $parametrosGerais = dadosParametrosGerais();

    $tamanhoArquivo     = $parametrosGerais[4];
    $tamanhoNomeArquivo = $parametrosGerais[5];
    $extensoesArquivo   = $parametrosGerais[6];

    $Critica = 0;

    if ($_FILES['Documentacao']['tmp_name']) {
        $_FILES['Documentacao']['name'] = tratarNomeArquivo($_FILES['Documentacao']['name']);

        $extensoesArquivo .= ', .zip, .xlsm, .xls, .ods, .pdf, .doc, .odt, .docx';

        $extensoes = explode(',', strtolower2($extensoesArquivo));

        array_push($extensoes, '.zip', '.xlsm');

        $noExtensoes = count($extensoes);
        $isExtensaoValida = false;

        for ($itr = 0; $itr < $noExtensoes; ++ $itr) {
            if (preg_match('/\\' . trim($extensoes[$itr]) . '$/', strtolower2($_FILES['Documentacao']['name']))) {
                $isExtensaoValida = true;
            }
        }

        if (! $isExtensaoValida) {
            $Mens      = 1;
            $Tipo      = 2;
            $Mensagem .= 'Selecione somente documento com a(s) extensão(ões) ' . $extensoesArquivo;
        }

        if (strlen($_FILES['Documentacao']['name']) > $tamanhoNomeArquivo) {
            if ($Mens == 1) {
                $Mensagem .= ', ';
            }

            $Mens      = 1;
            $Tipo      = 2;
            $Mensagem .= 'Nome do Arquivo com até ' . $tamanhoNomeArquivo . ' Caracateres ( atualmente com ' . strlen($_FILES['Documentacao']['name']) . ' )';
        }

        $Tamanho = $tamanhoArquivo * 1024;

        if (($_FILES['Documentacao']['size'] > $Tamanho) || ($_FILES['Documentacao']['size'] == 0)) {
            if ($Mens == 1) {
                $Mensagem .= ', ';
            }

            $Kbytes    = $tamanhoArquivo;
            $Kbytes    = (int) $Kbytes;
            $Mens      = 1;
            $Tipo      = 2;
            $Mensagem .= "Este arquivo ou é muito grande ou está vazio. Tamanho Máximo: $Kbytes Kb";
        }

        if ($Mens == '') {
            if (! ($_SESSION['Arquivos_Upload']['conteudo'][] = file_get_contents($_FILES['Documentacao']['tmp_name']))) {
                $Mens     = 1;
                $Tipo     = 2;
                $Mensagem = 'Caminho da Documentação Inválido';
            } else {
                $_SESSION['Arquivos_Upload']['nome'][] = $_FILES['Documentacao']['name'];
                $_SESSION['Arquivos_Upload']['situacao'][] = 'novo'; // situacao pode ser: novo, existente, cancelado e excluido
                $_SESSION['Arquivos_Upload']['codigo'][] = ''; // como é um arquivo novo, ainda nao possui código
            }
        }
    } else {
        $Mens     = 1;
        $Tipo     = 2;
        $Mensagem = 'Documentação Inválida';
    }

    $valorEstimatoTotalAtual = str_replace(',','.',str_replace('.','',$valorEstimatoTotalAtual));
} elseif ($botao == 'Retirar_Documento') {
    $Critica = 0;

    if ($DDocumento) {
        foreach ($DDocumento as $valor) {
            if ($_SESSION['Arquivos_Upload']['situacao'][$valor] == 'novo') {
                $_SESSION['Arquivos_Upload']['situacao'][$valor] = 'cancelado'; // cancelado- quando o usuário incluiu um arquivo novo mas desistiu
            } elseif ($_SESSION['Arquivos_Upload']['situacao'][$valor] == 'existente') {
                $_SESSION['Arquivos_Upload']['situacao'][$valor] = 'excluido'; // excluído- quando o arquivo já existe e deve ser excluido no sistema
            }
        }

        $valorEstimatoTotalAtual = str_replace(',','.',str_replace('.','',$valorEstimatoTotalAtual));
    } else {
        $Mens     = 1;
        $Tipo     = 2;
        $Mensagem = 'Selecione um anexo para ser retirado';
    }
}

if ($Critica == 1) {
    # Critica dos Campos #
    $Mens     = 0;
    $Mensagem = "Informe: ";

    if ($botao=='Voltar') {
        // volta pra tela anterior
        $Url  = "CadTramitacaoEntrada.php?";
        $Url .= "numProtocolo=".$numProtocoloRetorno;
        $Url .= "&anoProtocolo=".$anoProtocoloRetorno;
        $Url .= "&orgao=".$orgaoRetorno;
        $Url .= "&objeto=".$objetoRetorno;
        $Url .= "&numeroci=".$numerociRetorno;
        $Url .= "&numeroOficio=".$numeroOficioRetorno;
        $Url .= "&numeroScc=".$numeroSccRetorno;
        $Url .= "&proLicitatorio=".$proLicitatorioRetorno;
        $Url .= "&acao=".$acaoRetorno;
        $Url .= "&origem=".$origemRetorno;
        $Url .= "&DataEntradaIni=".$DataEntradaIniRetorno;
        $Url .= "&DataEntradaFim=".$DataEntradaFimRetorno;
        $Url .= "&botao=Pesquisar&Critica=1";
        $Url .= "&t=".mktime();

        if (!in_array($Url,$_SESSION['GetUrl'])) {
            $_SESSION['GetUrl'][] = $Url;
        }

        header("location: ".$Url);
        exit();
    }

    //Verifica se a acao foi preenchida
    if ($acao == '0_0_0_0_0') {
        if ($Mens == 1) {
            $Mensagem .= ", ";
        }

        $Mens      = 1;
        $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript:document.Entrada.acao.focus();\" class=\"titulo2\">Próxima Ação</a>";
    }

    $arrAcao = explode("_",$acao);

    if ($arrAcao[2] == 'S') {
        if ($comissaoAtual == '0') {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }

            $Mens      = 1;
            $Tipo      = 2;
            $Mensagem .= "<a href=\"javascript:document.Entrada.comissaoAtual.focus();\" class=\"titulo2\">Comissão de Licitação</a>";
        }
    }

    if ($anexoObrigatorio == 'S') {
        if (empty($_SESSION['Arquivos_Upload'])) {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }

            $Mens      = 1;
            $Tipo      = 2;
            $Mensagem .= "<a href=\"javascript:document.Entrada.agenteDestino.focus();\" class=\"titulo2\">Esta ação exige anexação de um documento</a>";
        }
    }

    //Verifica se o agente de Destino foi selecionado
    if ($agenteDestino == '0_0' ) {
        if ($Mens == 1) {
            $Mensagem .= ", ";
        }

        $Mens      = 1;
        $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript:document.Entrada.agenteDestino.focus();\" class=\"titulo2\">Agente de Tramitação Destino</a>";
    }

    $arrAgDestVer = explode("_",$agenteDestino);

    //Verifica se o agente de Destino foi selecionado
    if ($arrAgDestVer[1] == 'I' && $usuarioResponsavel <= 0 && $arrAcao[4] != 'S') {
        if ($Mens == 1) {
            $Mensagem .= ", ";
        }

        $Mens      = 1;
        $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript:document.Entrada.usuarioResponsavel.focus();\" class=\"titulo2\">Usuário Responsável</a>";
    }

    if ($receberProtocolo) {
        // Verifica se o Usuario foi selecionado
        // Funciona apenas no caso de receber Protocolo
        if ($usuarioResponsavel == '0' ) {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }

            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "<a href=\"javascript:document.Entrada.usuarioResponsavel.focus();\" class=\"titulo2\">Usuário responsável</a>";
        }
    }

    if ($Mens == 0) {
        if ($botao=='Enviar') {
            $db = Conexao();

            $sql  = "SELECT (CTRAMLSEQU + 1) AS NOVO_SEQUENCIAL ";
            $sql .= "FROM   SFPC.TBTRAMITACAOLICITACAO ";
            $sql .= "WHERE  CPROTCSEQU = " . $numTramitacao;
            $sql .= "ORDER BY CTRAMLSEQU DESC ";
            $sql .= "LIMIT 1 ";

            $resultadoPesquisa = $db->query($sql);

            if (PEAR::isError($resultadoPesquisa)) {
                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
            } else {
                $Linha           = $resultadoPesquisa->fetchRow();
                $seqTramitacao   = $Linha[0];
                $seqTramAnterior = $Linha[0] -1 ;

                $sequencialValido = false;

                while ($sequencialValido!= true) {
                    $db = Conexao();

                    $sql  = "SELECT COUNT(CPROTCSEQU) ";
                    $sql .= "FROM   SFPC.TBTRAMITACAOLICITACAO ";
                    $sql .= "WHERE  CPROTCSEQU = " . $numTramitacao;
                    $sql .= "       AND CTRAMLSEQU = " . $seqTramitacao;

                    $resultDuplicada = $db->query($sql);

                    if (PEAR::isError($resultadoPesquisa)) {
                        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                    } else {
                        $duplicado = $resultadoPesquisa->fetchRow();

                        if ($duplicado > 0) {
                            $sequencialValido = false;
                            $seqTramitacao++;
                        } else {
                            $sequencialValido = true;
                        }
                    }
                }
            }

            $arrAgenteDestino = explode("_",$agenteDestino);
            $codAgenteDestino = $arrAgenteDestino[0];

            $arrAcao   = explode("_",$acao);
            $codAcao   = $arrAcao[0];
            $prazoAcao = $arrAcao[1];

            // Informações sobre a ação
            $dataSaida = 'NULL';

            $sqlAcaoSel  = "SELECT  CTACAOSEQU, ETACAODESC, ATACAOPRAZ, FTACAOCOMI, FTACAOANEX, ";
            $sqlAcaoSel .= "        FTACAOFINA ";
            $sqlAcaoSel .= "FROM    SFPC.TBTRAMITACAOACAO ";
            $sqlAcaoSel .= "WHERE   CTACAOSEQU = " . $arrAcao[0];

            $resultAcaoSel = $db->query($sqlAcaoSel);

            if (PEAR::isError($resultAcaoSel)) {
                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sqlAcaoSel");
            } else {
                $Linha = $resultAcaoSel->fetchRow();

                if ($Linha[5] == 'S' ) {
                    $dataSaida = 'NOW()';
                }
            }

            # Encontra a informacao referente ao codigo da tramitacao enviado via GET
            $db = Conexao();

            if (!$comissaoAtual) {
                $comissaoAtual = 'NULL';
            }

            if ($usuarioResponsavel == '0' || $usuarioResponsavel == '' || empty($usuarioResponsavel)) {
                $usuarioResponsavel = '0';
            }

            $sqlInsert = "INSERT INTO sfpc.tbtramitacaolicitacao
                        (cprotcsequ, ctramlsequ, ctacaosequ, ctagensequ, cusupocodi, 
                        ttramlentr, ttramlsaid, atramlpraz, xtramlobse, ftramlsitu, 
                        cusupocod1, ttramlulat, ccomlicodi)
                        VALUES(".$numTramitacao.", $seqTramitacao, ".$codAcao.", ".$codAgenteDestino.", 
                        $usuarioResponsavel, NOW(), $dataSaida, $prazoAcao, '".strtoUpper2($observacao)."', 'A',". $_SESSION['_cusupocodi_'].", NOW(), $comissaoAtual )";

            $res = executarSQL($db, $sqlInsert);

            if (PEAR::isError($res)) {
                cancelarTransacao($db);
                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");

                $Botao = "";
            } else {
                // Atualiza a tramitação anterior
                $sqlUpdate  = "UPDATE   SFPC.TBTRAMITACAOLICITACAO ";
                $sqlUpdate .= "SET      TTRAMLSAID = NOW(), ";
                $sqlUpdate .= "         TTRAMLULAT = NOW(), ";
                $sqlUpdate .= "         CUSUPOCODI = " . $_SESSION['_cusupocodi_'];
                $sqlUpdate .= "WHERE    CPROTCSEQU = " . $numTramitacao;
                $sqlUpdate .= "         AND CTRAMLSEQU = " . $seqTramAnterior;

                $resUpdate = executarSQL($db, $sqlUpdate);

                if (PEAR::isError($resUpdate)) {
                    cancelarTransacao($db);
                    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
                    $Botao = "";
                } else {
                    // Buscar o id da licitação para usar no ANEXO
                    $sequencialProtocolo = $numTramitacao;

                    // inserir documentos
                    if (!empty($_SESSION['Arquivos_Upload'])) {
                        // Quantidade anexo
                        $sql = 'SELECT COUNT(*) FROM SFPC.TBTRAMITACAOLICITACAOANEXO WHERE 1 = 1';

                        $doc_seq = resultValorUnico(executarTransacao($db, $sql)) + 1;

                        $dirdestino = $GLOBALS['CAMINHO_UPLOADS'] . 'licitacoes/';

                        for ($i = 0; $i < count($_SESSION['Arquivos_Upload']['conteudo']); ++$i) {
                            $NomeDocto = 'DOC_' . $sequencialProtocolo . '_' . $doc_seq . '_' . $_SESSION['Arquivos_Upload']['nome'][$i];

                            if ($_SESSION['Arquivos_Upload']['situacao'][$i] == 'novo') {
                                $arquivo =  bin2hex($_SESSION['Arquivos_Upload']['conteudo'][$i]);

                                $sql = " INSERT INTO SFPC.tbtramitacaolicitacaoanexo
                                        (ctramasequ, cprotcsequ, ctramlsequ, etramanome, itramaarqu, ttramacada, cusupocodi, ttramaulat
                                    ) VALUES(
                                        $doc_seq, $sequencialProtocolo, $seqTramitacao, '" . $NomeDocto . "', decode('".$arquivo."','hex'), NOW(),  " . $_SESSION['_cusupocodi_'] . ",  NOW()
                                    )";

                                executarTransacao($db, $sql);

                                $doc_seq++;
                            }
                        }

                        finalizarTransacao($db);
                    }

                    if (!$receberProtocolo) {
                        $Url  = "CadTramitacaoEntrada.php?";
                        $Url .= "numProtocolo=".$numProtocoloRetorno;
                        $Url .= "&anoProtocolo=".$anoProtocoloRetorno;
                        $Url .= "&orgao=".$orgaoRetorno;
                        $Url .= "&objeto=".$objetoRetorno;
                        $Url .= "&numeroci=".$numerociRetorno;
                        $Url .= "&numeroOficio=".$numeroOficioRetorno;
                        $Url .= "&numeroScc=".$numeroSccRetorno;
                        $Url .= "&proLicitatorio=".$proLicitatorioRetorno;
                        $Url .= "&acao=".$acaoRetorno;
                        $Url .= "&origem=".$origemRetorno;
                        $Url .= "&DataEntradaIni=".$DataEntradaIniRetorno;
                        $Url .= "&DataEntradaFim=".$DataEntradaFimRetorno;
                        $Url .= "&botao=Pesquisar&Critica=0&inseriu=1";
                        $Url .= "&t=".mktime();

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
}

    # Encontra a informacao referente ao codigo da tramitacao enviado via GET
    $db = Conexao();//4, 10 , 16, 20
    $sql = "SELECT cprotcsequ, cgrempcod1, cprotcnump, aprotcanop, corglicod1, 
    xprotcobje, eprotcnuci, eprotcnuof, csolcosequ, prot.clicpoproc, prot.alicpoanop, 
    prot.cgrempcodi, prot.ccomlicodi, prot.corglicodi, TPROTCENTR, vprotcvale, xprotcobse, 
    prot.cusupocodi, cusupocod1, tprotculat, org.eorglidesc, 
        (   select f.efasesdesc from sfpc.tbfaselicitacao fase 
            join sfpc.tbfases f on f.cfasescodi = fase.cfasescodi 
            where fase.clicpoproc = prot.clicpoproc and fase.alicpoanop = prot.alicpoanop 
            and fase.ccomlicodi= prot.ccomlicodi and fase.corglicodi = prot.corglicodi 
            and fase.cgrempcodi = prot.cgrempcodi
            order by fase.tfaselulat asc
            limit 1
        ) as fase_licitacao,
        (select acao.etacaodesc from sfpc.tbtramitacaolicitacao tram
            join sfpc.tbtramitacaoacao acao on acao.ctacaosequ = tram.ctacaosequ
            where prot.cprotcsequ = tram.cprotcsequ
            order by tram.ttramlentr desc, acao.ttacaoulat desc
            limit 1)as acaoDesc,

        (select agente.etagendesc from sfpc.tbtramitacaolicitacao tram
            join sfpc.tbtramitacaoagente agente on agente.ctagensequ = tram.ctagensequ
            where prot.cprotcsequ = tram.cprotcsequ
            order by tram.ttramlentr desc 
            limit 1)as agenteOrigemDesc,

        (select tram.ctagensequ from sfpc.tbtramitacaolicitacao tram
            where prot.cprotcsequ = tram.cprotcsequ
            order by tram.ttramlentr desc
            limit 1)as codAgenteOrigem,

        (select usu.eusuporesp from sfpc.tbtramitacaolicitacao tram
            join sfpc.tbtramitacaoagenteusuario agusu on agusu.ctagensequ = tram.ctagensequ
            join sfpc.tbusuarioportal usu on usu.cusupocodi = agusu.cusupocodi
            where prot.cprotcsequ = tram.cprotcsequ
            order by tram.ttramlentr desc
            limit 1)as agenteUsuDesc,

        (select tram.ttramlentr from sfpc.tbtramitacaolicitacao tram
            where prot.cprotcsequ = tram.cprotcsequ
            order by tram.ttramlentr desc
            limit 1)as datahoraEntradaAcao,

        (select acao.ftacaoanex from sfpc.tbtramitacaolicitacao tram
            join sfpc.tbtramitacaoacao acao on acao.ctacaosequ = tram.ctacaosequ
            where prot.cprotcsequ = tram.cprotcsequ
            order by tram.ttramlentr desc, acao.ttacaoulat desc
            limit 1)as anexoObrigatorio,

        (select acao.ftacaotusu from sfpc.tbtramitacaolicitacao tram
            join sfpc.tbtramitacaoacao acao on acao.ctacaosequ = tram.ctacaosequ
            where prot.cprotcsequ = tram.cprotcsequ
            order by tram.ttramlentr desc, acao.ttacaoulat desc
            limit 1)as acaoParaTodosUsu,

        (select usu.cusupocodi from sfpc.tbtramitacaolicitacao tram
            join sfpc.tbusuarioportal usu on usu.cusupocodi = tram.cusupocodi
            where prot.cprotcsequ = tram.cprotcsequ
            order by tram.ttramlentr desc
            limit 1)as tramUsuCod,

            (select usu.eusuporesp from sfpc.tbtramitacaolicitacao tram
                join sfpc.tbusuarioportal usu on usu.cusupocodi = tram.cusupocodi
                where prot.cprotcsequ = tram.cprotcsequ
                order by tram.ttramlentr desc
                limit 1)as tramUsuDesc
        
    FROM sfpc.tbtramitacaoprotocolo prot
    join sfpc.tborgaolicitante org on org.corglicodi = prot.corglicod1
    WHERE prot.cprotcsequ =".$numTramitacao;


    $resultadoPesquisa = $db->query($sql);

    if (PEAR::isError($resultadoPesquisa)) {
        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
    } else {
        //dados
        while($Linha = $resultadoPesquisa->fetchRow()){
            $seq = $Linha[0];//
            $numProtocolo = str_pad($Linha[2], 4, "0", STR_PAD_LEFT)."/".$Linha[3];// Protocolo / Ano
            $orgao = $Linha[20];//órgão
            $objeto = $Linha[5];//objeto
            $numeroci = $Linha[6];//num. CI
            $numerooficio = $Linha[7];//num oficio
            if($Linha[8]){
                $numeroscc = getNumeroSolicitacaoCompra($db, $Linha[8]);//Numero SCC
            }else{
                $numeroscc = '';
            }

            if($Linha[9]>0){

                $sql = "SELECT B.ECOMLIDESC 
                FROM SFPC.TBLICITACAOPORTAL A
                join SFPC.TBCOMISSAOLICITACAO B on A.CCOMLICODI = B.CCOMLICODI
                WHERE  A.CLICPOPROC =".$Linha[9]." AND A.ALICPOANOP = ".$Linha[10]." 
                AND A.CGREMPCODI=".$Linha[11]." AND A.CCOMLICODI = ".$Linha[12]." 
                AND A.CORGLICODI = ".$Linha[13]." 
                ORDER BY B.ECOMLIDESC ASC, A.ALICPOANOP DESC, A.CLICPOPROC DESC";

                $resultadoProcLic = $db->query($sql);

                if (PEAR::isError($resultadoProcLic)) {
                    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                } else {
                    $Dados = $resultadoProcLic->fetchRow();
                    $procLic = str_pad($Linha[9], 4, "0", STR_PAD_LEFT)."/".$Linha[10]." - ".$Dados[0];
                }
            }else{
                $procLic = '';
            }
            
            // DADOS DA AÇÃO ATUAL
            $acaoAtual = $Linha[22];
            //Usuario
            $usuarioRespAtual = '';
            if($Linha[28]=='S'){
                                            
                if($Linha[29] <= 0 ){
                    $usuarioRespAtual = $Linha[23];
                }else{

                    $usuarioRespAtual = $Linha[30];
                }
            }else{
                if($Linha[29] <= 0){
                    $usuarioRespAtual = 'ÓRGÃO EXTERNO';
                }else{
                    $usuarioRespAtual = $Linha[30];
                }
            }

            $agenteAtual = $Linha[23];
            $codAgenteAtual = $Linha[24];
            $dataTramitacao = date('d/m/Y H:i:s',strtotime($Linha[26]));

            $anexoObrigatorio = $Linha[27];

            $faseLic = $Linha[21];//fase licitacao
                 
            //Retorna os Dados das Ações
            $htmlAcao = '';

            //pega a última acao realizada para que não apareça na listagem
            $ultimoPasso = getTramitacaoUltimoPasso($numTramitacao);


            $sql = "SELECT CTACAOSEQU, ETACAODESC, ATACAOPRAZ, FTACAOCOMI, FTACAOANEX, FTACAOTUSU FROM SFPC.TBTRAMITACAOACAO 
                    WHERE FTACAOSITU = 'A' AND CGREMPCODI = ".$_SESSION['_cgrempcodi_'];


            if(!$receberProtocolo){
                if($ultimoPasso){
                    $sql .= " AND CTACAOSEQU <> ".$ultimoPasso[2];
                }
            }

            $sql .= " ORDER BY atacaoorde ASC ";
            $result = $db->query($sql);
            if (PEAR::isError($result)) {
                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
            } else {

                while ($Linha = $result->fetchRow()) {
                    if ($Linha[0]."_".$Linha[2]."_".$Linha[3]."_".$Linha[4]."_".$Linha[5] == $acao) {
                        $htmlAcao.= "<option selected='selected' value=\"$Linha[0]_$Linha[2]_$Linha[3]_$Linha[4]_$Linha[5]\">$Linha[1]</option>\n";
                        $acaoDesc = $Linha[1];
                    } else {
                        $htmlAcao.= "<option value=\"$Linha[0]_$Linha[2]_$Linha[3]_$Linha[4]_$Linha[5]\">$Linha[1]</option>\n";
                    }
                }

            }


            // DADOS DO CHECKLIST
            $sqlCheck  = "SELECT c.ctacacsequ, c.etacacdesc, c.atacacorde,  ";
            $sqlCheck .= " c.cusupocodi, c.ttacaculat , u.eusuporesp ";
            $sqlCheck .= " FROM sfpc.tbtramitacaoacaochecklist c ";
            $sqlCheck .= " left join sfpc.tbusuarioportal u on c.cusupocodi = u.cusupocodi ";
            $sqlCheck .= " where c.ftacacsitu = 'A' and c.ctacaosequ =".$ultimoPasso[2]; 
            
            $resultCheck = $db->query($sqlCheck);
            
            if (PEAR::isError($resultCheck)) {
                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sqlCheck");
            } else {
                    
                $checkListRows = $resultCheck->numRows();

                $dadosChecklist = '<table>';
                while ($dadoCheck = $resultCheck->fetchRow()) {

                    $arrData = explode('-',substr($dadoCheck[4],0,10));
                    $data = $arrData[2].'/'.$arrData[1].'/'.$arrData[0]; 

                    $dadosChecklist .= '<tr><td class="textonormal"> - '.$dadoCheck[1].'</td></tr>';

                }
                $dadosChecklist .= '</table>';

            }

            //Retorna os Dados dos Agentes Destino
            $htmlAgenteDestino = '';

            if (!$receberProtocolo) {
                $sql = "SELECT CTAGENSEQU, ETAGENDESC, FTAGENTIPO FROM SFPC.TBTRAMITACAOAGENTE WHERE FTAGENSITU = 'A' AND CGREMPCODI = ".$_SESSION['_cgrempcodi_'];
            } else {
                $sql  = "SELECT CTAGENSEQU, ETAGENDESC, FTAGENTIPO ";
                $sql .= "FROM   SFPC.TBTRAMITACAOAGENTE ";
                $sql .= "WHERE  CGREMPCODI = " . $_SESSION['_cgrempcodi_'];
                $sql .= "       AND (CTAGENSEQU IN (SELECT AGUSU.CTAGENSEQU ";
                $sql .= "                          FROM SFPC.TBTRAMITACAOAGENTEUSUARIO AGUSU ";
                $sql .= "                          JOIN SFPC.TBUSUARIOPORTAL USU ON USU.CUSUPOCODI = AGUSU.CUSUPOCODI ";
                $sql .= "                          WHERE USU.CUSUPOCODI = " . $_SESSION['_cusupocodi_']." )) ";
                $sql .= "       OR CTAGENSEQU = (SELECT CTAGENSEQU ";
                $sql .= "                        FROM   SFPC.TBTRAMITACAOLICITACAO ";
                $sql .= "                        WHERE  CPROTCSEQU = " . $numTramitacao;
                $sql .= "                               AND TTRAMLSAID IS NOT NULL ";
                $sql .= "                        ORDER BY CTRAMLSEQU DESC ";
                $sql .= "                        LIMIT 1) ";
            }

            $sql .= "ORDER BY ETAGENDESC ASC ";

            $result = $db->query($sql);

            if (PEAR::isError($result)) {
                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
            } else {
                while ($Linha = $result->fetchRow()) {
                    if ("$Linha[0]_$Linha[2]" == $agenteDestino) {
                        $htmlAgenteDestino.= "<option selected='selected' value=\"$Linha[0]_$Linha[2]\">$Linha[1]</option>\n";
                        $destinoDesc = $Linha[1];
                    } else {
                        $htmlAgenteDestino.= "<option value=\"$Linha[0]_$Linha[2]\">$Linha[1]</option>\n";
                    }
                }
            }

            //Retorna os Dados dos Agentes Origem
            $htmlUsuarioResponsavel = '';

            if($agenteDestino){
                $arrAgenDest = explode('_', $agenteDestino);

                if($arrAgenDest[0] > 0){
                    $txtAgenteDestSql= ' AND agusu.ctagensequ = '.$arrAgenDest[0].' ';
                }else{
                    $txtAgenteDestSql= '';
                }

            }else{
                $txtAgenteDestSql= '';
            }

            if(!$receberProtocolo){
                $sql = "select usu.cusupocodi, usu.eusuporesp, agusu.ctagensequ 
                from sfpc.tbtramitacaoagenteusuario agusu 
                join sfpc.tbusuarioportal usu on usu.cusupocodi = agusu.cusupocodi
                where 1=1 ".$txtAgenteDestSql."
                ORDER BY usu.eusuporesp
                ";
            }else{
                $sql = "select usu.cusupocodi, usu.eusuporesp, agusu.ctagensequ 
                from sfpc.tbtramitacaoagenteusuario agusu 
                join sfpc.tbusuarioportal usu on usu.cusupocodi = agusu.cusupocodi
                where usu.cusupocodi = ".$_SESSION['_cusupocodi_']." ".$txtAgenteDestSql." ORDER BY usu.eusuporesp";
            }
            $result = $db->query($sql);
            if (PEAR::isError($result)) {
                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
            } else {
                while ($Linha = $result->fetchRow()) {
                    if ($Linha[0] == $usuarioResponsavel) {
                        $htmlUsuarioResponsavel.= "<option selected='selected' value=\"$Linha[0]\">$Linha[1]</option>\n";
                        //$destinoDesc = $Linha[1];
                    } else {
                        $htmlUsuarioResponsavel.= "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
                    }
                    $arrUsuariosAgente[] = $Linha;
                }

                //while ($Linha2 = $result->fetchRow()) {
                    
                //}
            }

        }
    }



?>
<html>


<style>

.titulo_resultado{
    background-color: #DCEDF7
}

.tamanho_campo{
    width:100%;
}
</style>    
<?php
# Carrega o layout padrão @

layout();


?>
<script language="javascript" type="">

var arrUsuariosAgente = <?php echo json_encode($arrUsuariosAgente)?>;


<!--
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
    <?php if(!$window){ ?> 
        <script language="JavaScript" src="../menu.js"></script>
    <?php } ?>
    <script language="JavaScript">Init();</script>
    <form action="CadTramitacaoEntradaEnvio.php" method="post" name="Entrada" id="Entrada" enctype="multipart/form-data">
        <input type="hidden" name="botao" value="">
        <input type="hidden" name="receberProtocolo" value="<?php echo $receberProtocolo?>">
        <input type="hidden" name="window" value="<?php echo $window?>">
        <?php if(!$window){ ?> 
            <br> <br> <br> <br> <br><br>
        <?php } ?>
        <table cellpadding="3" border="0">
            <!-- Caminho -->
            <tr>
                <?php if(!$window){ ?> 
                    <td width="100">
                        <img border="0" src="../midia/linha.gif" alt="">
                    </td>
                
                <td align="left" class="textonormal">
                    <font class="titulo2">|</font>
                    <a href="../index.php">
                        <font color="#000000">Página Principal</font>
                    </a>
                    > Licitações > Tramitação > Entrada > Envio
                </td>
                <?php } ?>
            </tr>
            <!-- Fim do Caminho-->
            <!-- Erro -->
	        
	        <tr>
                <?php if(!$window){ ?> 
                    <td width="100"></td>
                <?php } ?>
                <td align="left" colspan="2" id="erroTd">
                    <?php if ( $Mens == 1 ) {?>
                        <?php ExibeMens($Mensagem,$Tipo,1); ?>
                    <?php } ?>
                </td>
            </tr>
	        
	        <!-- Fim do Erro -->
            <!-- Corpo -->
            <tr>
                <?php if(!$window){ ?> 
                    <td width="100"></td>
                <?php } ?>
                <td class="textonormal">
                    <table border="0" cellspacing="0" cellpadding="3" >
                        <tr>
                            <td class="textonormal">
                                <table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF" style="width: 100%;">
                                    <tr>
                                        <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">TRAMITAR – PROCESSOS LICITATÓRIOS - ENVIO</td>
                                    </tr>

                                   
                                    <tr>
                                        <td>
                                            <table class="textonormal" border="0" align="left" class="caixa">
                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Número/Ano do Protocolo do Processo Licitatório</td>
                                                    <td class="textonormal">
                                                        <?php echo $numProtocolo; ?>
                                                    </td>
                                                    
                                                </tr>
                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Órgão demandante</td>
                                                    <td class="textonormal">
                                                        <?php echo $orgao;?>
                                                    </td>
                                                    
                                                </tr>

                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Objeto</td>
                                                    <td class="textonormal">
                                                        <?php echo $objeto; ?>
                                                        
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Número CI</td>
                                                    <td class="textonormal">
                                                        <?php echo $numeroci; ?>
                                                    </td>
                                                    
                                                </tr>

                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Número Ofício</td>
                                                    <td class="textonormal">
                                                        <?php echo $numerooficio; ?>
                                                    </td>
                                                    
                                                </tr>

                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Número da SCC</td>
                                                    <td class="textonormal">
                                                       <?php echo $numeroscc; ?>
                                                    </td>
                                                    
                                                </tr>

                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Processo Licitatório</td>
                                                    <td class="textonormal">

                                                    <?php echo $procLic;?>
                                                    </td>
                                                    
                                                </tr>
                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Ação</td>
                                                    <td ><?php echo $acaoAtual ?></td>
                                                </tr> 
                                                <?php if($checkListRows > 0){ ?> 
                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Checklist</td>
                                                    <td ><?php echo $dadosChecklist ?></td>
                                                </tr>  
                                                <?php } ?>
                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Data de Entrada</td>
                                                    <td ><?php echo $dataTramitacao ?></td>
                                                </tr>   
                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Agente</td>
                                                    <td class='apresentaHintAgente' id ='<?php echo $codAgenteAtual ?>' ><?php echo $agenteAtual ?></td>
                                                </tr> 
                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Usuário responsável</td>
                                                    <td ><?php echo $usuarioRespAtual ?></td>
                                                </tr> 


                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Próxima Ação*</td>
                                                    <td class="textonormal">
                                                        <input type="hidden" name="prazoAcao" value="0">
                                                        <select name="acao" id="acao" class="tamanho_campo" onclick='preenchePrazoDaAcao(this.value)' >
                                                                <option value="0_0_0_0_0">Selecione uma Ação...</option>
                                                            <?php echo $htmlAcao ?>
                                                        </select>
                                                    </td>
                                                    
                                                </tr>

                                                
                                                <tr class = "comissaoLic">
                                                    <td class="textonormal" bgcolor="#DCEDF7">Comissão de Licitação*</td>
                                                    <td class="textonormal">
                                                    <select id="comissaoAtual" name="comissaoAtual" class="tamanho_campo" class="textonormal">
                                                        <option value="0">Selecione a comissão de licitação...</option>
                                                        <?php
                                                        $grupoAtual = $_SESSION['_cgrempcodi_'];
                                                        if(!empty($grupoAtual)) {
                                                            $comissoes = getComissaoLicitacao($grupoAtual);
                                                            while ($comissao = $comissoes->fetchRow()) {
                                                                ?>
                                                                <option <?php echo (!empty($comissaoAtual) && $comissaoAtual == $comissao[0]) ? 'selected' : ''?> value="<?php echo $comissao[0]; ?>"><?php echo $comissao[1]; ?></option>
                                                                <?php
                                                            }
                                                        }

                                                        ?>
                                                    </select>
                                                    </td>
                                                    
                                                </tr>
                                                

                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7"> Agente de tramitação Destino*</td>
                                                    <td class="textonormal">
                                                        <select name="agenteDestino" id="agenteDestino" class="tamanho_campo"  onclick='exibeUsuarioResponsavel(this.value)'>
                                                            <option value="0_0">Selecione um Agente de Destino...</option>
                                                            <?php echo $htmlAgenteDestino ?>
                                                        </select>
                                                    </td>
                                                    
                                                </tr>

                                                <tr class = "usuarioResp">
                                                    <td class="textonormal usuarioResp" bgcolor="#DCEDF7"> Usuário Responsável</td>
                                                    <td class="textonormal usuarioResp">
                                                        <select name="usuarioResponsavel" id="usuarioResponsavel" class="tamanho_campo">
                                                            <option value="0">Selecione um usuário...</option>
                                                            <?php echo $htmlUsuarioResponsavel ?>
                                                        </select>
                                                    </td>
                                                    
                                                </tr>

                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7" width="30%">Data Saída Tramitação</td>
                                                    <td name="DataTramitacao"  class="textonormal">
                                                        <?php echo date('d/m/Y');?>
                                                    </td>

                                                </tr>

                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7"> Observação</td>
                                                    <td class="textonormal">
                                                    <font class="textonormal">máximo de 500 caracteres</font>
                                                        <input type="text" name="NCaracteres" disabled="" readonly="" size="3" value="0" class="textonormal"><br>
                                                        <textarea id="observacao" name="observacao" maxlength="500" cols="50" rows="4" onkeyup="javascript:CaracteresObservacao(1)" onblur="javascript:CaracteresObservacao(0)" onselect="javascript:CaracteresObservacao(1)" class="textonormal"><?php echo $observacao; ?></textarea>
                                                        <script language="javascript" type="">
                                                        function CaracteresObservacao(valor){
                                                            Entrada.NCaracteres.value = '' +  Entrada.observacao.value.length;
                                                        }
                                                        </script>
                                                        <!--<input type="text" name="DianDescricao" value="<?php echo $observacao; ?>" size="45" maxlength="400" class="textonormal"> -->
                                                    
                                                    </td>
                                                    
                                                </tr>
                                                <tr>
                                                    <td align="center" class="textonormal titulo3" bgcolor="#75ADE6" colspan="3"><b>Anexação de Documentos</b></td>
                                                </tr>
                                                <tr>
                                                    <?php $DTotal = count($_SESSION['Arquivos_Upload']['conteudo']);?>
                                                    <td class="textonormal" bgcolor="#DCEDF7" rowspan="<?php echo $DTotal?>">Anexação de Documento(s) </td>
                                                    <td class="textonormal" >
                                                        <input type="file" name="Documentacao" class="textonormal" />
                                                        <table width="100%" border="0" cellpadding="0" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
                                                        <?php
                                                            for ($Dcont = 0; $Dcont < $DTotal; ++ $Dcont) {
                                                                if ($_SESSION['Arquivos_Upload']['situacao'][$Dcont] == 'novo' || $_SESSION['Arquivos_Upload']['situacao'][$Dcont] == 'existente') {
                                                                    echo '<tr>';
                                                                    if (! $ocultarCamposEdicao) {
                                                                    // echo "<td bgcolor='#DCEDF7'></td>";
                                                                        echo "<td align='center' width='10%'><input type='checkbox' name='DDocumento[$Dcont]' value='$Dcont' ></td>\n";
                                                                    }
                                                                    echo "<td class='textonormal' >";
                                                                    if (! $ocultarCamposEdicao) {
                                                                        echo $_SESSION['Arquivos_Upload']['nome'][$Dcont];
                                                                    } else {
                                                                        $arquivo = 'compras/' . $_SESSION['Arquivos_Upload']['nome'][$Dcont];
                                                                        addArquivoAcesso($arquivo);

                                                                        echo "<a href='../carregarArquivo.php?arq=" . urlencode($arquivo) . "'>" . $_SESSION['Arquivos_Upload']['nome'][$Dcont] . '</a>';
                                                                    }
                                                                    echo '</td></tr>';
                                                                }
                                                            }
                                                        ?>
                                                        </table>
                                                        <input type="button" name="IncluirDocumento" value="Incluir Documento" class="botao" onclick="javascript:enviarPadrao('Incluir_Documento');">
                                                        <input type="button" name="RetirarDocumento" value="Retirar Documento" class="botao" onclick="javascript:enviarPadrao('Retirar_Documento');">
                                                    </td>
                                                </tr>
                                                <!-- dados para retorno da tela -->
                                                <input type="hidden" name="numProtocoloRetorno" value="<?php echo $numProtocoloRetorno ?>">
                                                <input type="hidden" name="anoProtocoloRetorno" value="<?php echo $anoProtocoloRetorno ?>">
                                                <input type="hidden" name="orgaoRetorno" value="<?php echo $orgaoRetorno ?>">
                                                <input type="hidden" name="objetoRetorno" value="<?php echo $objetoRetorno ?>">
                                                <input type="hidden" name="numerociRetorno" value="<?php echo $numerociRetorno ?>">
                                                <input type="hidden" name="numeroOficioRetorno" value="<?php echo $numeroOficioRetorno ?>">
                                                <input type="hidden" name="numeroSccRetorno" value="<?php echo $numeroSccRetorno ?>">
                                                <input type="hidden" name="proLicitatorioRetorno" value="<?php echo $proLicitatorioRetorno ?>">
                                                <input type="hidden" name="acaoRetorno" value="<?php echo $acaoRetorno ?>">
                                                <input type="hidden" name="origemRetorno" value="<?php echo $origemRetorno ?>">
                                                <input type="hidden" name="DataEntradaIniRetorno" value="<?php echo $DataEntradaIniRetorno ?>">
                                                <input type="hidden" name="DataEntradaFimRetorno" value="<?php echo $DataEntradaFimRetorno ?>">
                                                <input type="hidden" name="anexoObrigatorio" value="<?php echo $anexoObrigatorio ?>">
                                                                    
                                                <input type="hidden" name="Critica" value="1"> 
                                                <input type="hidden" name="numTramitacao" value="<?php echo $numTramitacao; ?>"> 
                                            </table>
                                        </td>
                                    </tr>
                                    <tr colspan='13'>
                                        <td class="textonormal" align="right">
                                                    <a name="Enviar" id="btnEnviar" value="Enviar" class="botao" href="<?php if(!$window){ ?>javascript:enviarPadrao('Enviar');<?php }else{ ?>javascript:enviar();<?php } ?>" style="padding: 1px 6px;">Enviar</a>
                                                    <a name="Voltar" id="btnVoltar" value="Voltar" class="botao" href="<?php if(!$window){ ?>javascript:enviarPadrao('Voltar');<?php }else{ ?>javascript:window.close(); <?php } ?>" style="padding: 1px 6px;">Voltar</a>
                                        </td>
                                            
                                    </tr>
  
                                </table>
                                
                            </td>
                        </tr>
                    <tr>
                        <td>
                            
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

document.Entrada.observacao.focus();
CaracteresObservacao(0);
<?php 
$arrAgenteDestinoVer = explode("_",$agenteDestino);
$arrAcaoVer = explode("_",$acao);

if(empty($agenteDestino) || $agenteDestino == '0_0' || $arrAgenteDestinoVer[1] == 'E' || $arrAcaoVer[4] == 'S'){ ?>
$('.usuarioResp').hide();
<?php 
}

$arrAcaoVer = explode("_",$acao);
if(empty($acao) || $acao == '0_0_0_0_0' || $arrAcaoVer[2] != 'S'){ ?>
$('.comissaoLic').hide();
<?php } ?>

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



function enviar(){

    document.Entrada.botao.value = 'Enviar';
    //CadTramitacaoEntradaEnvio
    $.post('receberProtocolo.php', $('#Entrada').serialize())
    .done(function(data) {

            console.log("Retorno",data);
            if(data == '1'){
                window.opener.atualizarPag();
                window.close();
            }else{
                showErro(data);
            }

        })


}

function enviarPadrao(strAcao){

document.Entrada.botao.value = strAcao;
document.Entrada.submit();


}

function exibeUsuarioResponsavel(numAgenteDestino){
    var arrAgenteDestino = numAgenteDestino.explode("_"),
        tipoAgenteDestino = arrAgenteDestino[1],
        dadosAcao = $('#acao').val(),
        arrAcao = dadosAcao.explode("_"),
        apresentaUsuario = arrAcao[4],
        apresentaComissao = arrAcao[2];

    preencheUsuarioAgente(arrAgenteDestino[0]);

    if(tipoAgenteDestino != 'I'){
        $('.usuarioResp').hide();
    }else{
       //alert(apresentaUsuario);
        if(apresentaUsuario =='S'){
            $('.usuarioResp').hide();
        }else{
            if(apresentaComissao =='S'){
                $('.usuarioResp').hide();
            }else{
                $('.usuarioResp').show();
            }
        }
    }

}

function preencheUsuarioAgente(codAgente){

    var options = $('#usuarioResponsavel');

    options.find('option').remove();
    $('<option>').val('0').text('Selecione um usuário...').appendTo(options);

    i=0;
    $.each(arrUsuariosAgente, function (key, value) {
        arrUsuarios = arrUsuariosAgente[i];
        if(codAgente == arrUsuarios[2]){
            $('<option>').val(arrUsuarios[0]).text(arrUsuarios[1]).appendTo(options);
        }    
        i++;
    });  


}

function preenchePrazoDaAcao(numAcao){
    var arrAcao = numAcao.explode("_"),
        prazoAcao = arrAcao[1],
        apresentaComissao = arrAcao[2],
        apresentaUsuario= arrAcao[4],
        dadosAgente = $('#agenteDestino').val(),
        arrAgente = dadosAgente.explode("_"),
        tipoAgenteDestino = arrAgente[1];

    document.Entrada.prazoAcao.value = prazoAcao;

    if(apresentaComissao=='S'){
        apresentarComissao();
        $('.usuarioResp').hide();
    }else{
        esconderComissao();

        if(tipoAgenteDestino != 'I'){
            $('.usuarioResp').hide();
        }else{
        //alert(apresentaUsuario);
            if(apresentaUsuario =='S'){
                $('.usuarioResp').hide();
            }else{
                if(apresentaComissao =='S'){
                    $('.usuarioResp').hide();
                }else{
                    $('.usuarioResp').show();
                }
            }
        }

    }



}


function apresentarComissao(){
    $('.comissaoLic').show();
}

function esconderComissao(){
    $('.comissaoLic').hide();
}
</script>