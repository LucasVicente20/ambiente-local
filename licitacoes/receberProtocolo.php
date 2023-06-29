<?php
# ------------------------------------------------------------------
# Portal de Compras
# Programa: ReceberProtocolo.php
# Autor:    Ernesto Ferreira
# Data:     19/03/2019
# Objetivo: Tarefa Redmine 212934
# ------------------------------------------------------------------

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
    $Critica        = $_POST['Critica'];//strtoupper2(
    $botao          = $_POST['botao'];
    $numTramitacao  = $_POST['numTramitacao'];
    $acao           = $_POST['acao'];
    $agenteDestino  = $_POST['agenteDestino'];
    $usuarioResponsavel  = $_POST['usuarioResponsavel'];
    $prazoAcao           = $_POST['prazoAcao'];
    $observacao          = $_POST['observacao'];
    $comissaoAtual            = $_POST['comissaoAtual'];
        //Dados para retornar a tela de pesquisa
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
	$DataEntradaIniRetorno    = $_POST['DataEntradaIniRetorno'];
    $DataEntradaFimRetorno    = $_POST['DataEntradaFimRetorno'];
    $anexoObrigatorio         = $_POST['anexoObrigatorio'];


    $window         = $_POST['window'];
    $receberProtocolo = $_POST['receberProtocolo'];
    $DDocumento              = $_POST['DDocumento'];
}else{
    $Critica        = $_GET['Critica'];//strtoupper2(
    $botao          = $_GET['botao'];
    $numTramitacao  = $_GET['numTramitacao']; 
    $window         = $_GET['window'];
    $receberProtocolo = $_GET['receberProtocolo'];
    //dados para voltar
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
	$DataEntradaIniRetorno    = $_GET['DataEntradaIni'];
    $DataEntradaFimRetorno    = $_GET['DataEntradaFim'];

}

if ($botao == 'Incluir_Documento') {
    $parametrosGerais = dadosParametrosGerais();

    $tamanhoArquivo = $parametrosGerais[4];
    $tamanhoNomeArquivo = $parametrosGerais[5];
    $extensoesArquivo = $parametrosGerais[6];

    $Critica = 0;
    if ($_FILES['Documentacao']['tmp_name']) {
        $_FILES['Documentacao']['name'] = tratarNomeArquivo($_FILES['Documentacao']['name']);

        $extensoesArquivo .= ', .zip, .xlsm, .xls, .ods, .pdf';


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
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= 'Selecione somente documento com a(s) extensão(ões) ' . $extensoesArquivo;
        }
        if (strlen($_FILES['Documentacao']['name']) > $tamanhoNomeArquivo) {
            if ($Mens == 1) {
                $Mensagem .= ', ';
            }
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= 'Nome do Arquivo com até ' . $tamanhoNomeArquivo . ' Caracateres ( atualmente com ' . strlen($_FILES['Documentacao']['name']) . ' )';
        }
        $Tamanho = $tamanhoArquivo * 1024;
        if (($_FILES['Documentacao']['size'] > $Tamanho) || ($_FILES['Documentacao']['size'] == 0)) {
            if ($Mens == 1) {
                $Mensagem .= ', ';
            }
            $Kbytes = $tamanhoArquivo;
            $Kbytes = (int) $Kbytes;
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "Este arquivo ou é muito grande ou está vazio. Tamanho Máximo: $Kbytes Kb";
        }
        if ($Mens == '') {
            if (! ($_SESSION['Arquivos_Upload']['conteudo'][] = file_get_contents($_FILES['Documentacao']['tmp_name']))) {
                $Mens = 1;
                $Tipo = 2;
                $Mensagem = 'Caminho da Documentação Inválido';
            } else {
                $_SESSION['Arquivos_Upload']['nome'][] = $_FILES['Documentacao']['name'];
                $_SESSION['Arquivos_Upload']['situacao'][] = 'novo'; // situacao pode ser: novo, existente, cancelado e excluido
                $_SESSION['Arquivos_Upload']['codigo'][] = ''; // como é um arquivo novo, ainda nao possui código
            }
        }
    } else {
        $Mens = 1;
        $Tipo = 2;
        $Mensagem = 'Documentação Inválida';
    }
    //passagem de parametro para não dar erro
    $valorEstimatoTotalAtual = str_replace(',','.',str_replace('.','',$valorEstimatoTotalAtual));

} elseif ($botao == 'Retirar_Documento') {
    $Critica = 0;
    if ($DDocumento){
        foreach ($DDocumento as $valor) {

            if ($_SESSION['Arquivos_Upload']['situacao'][$valor] == 'novo') {
                $_SESSION['Arquivos_Upload']['situacao'][$valor] = 'cancelado'; // cancelado- quando o usuário incluiu um arquivo novo mas desistiu
            } elseif ($_SESSION['Arquivos_Upload']['situacao'][$valor] == 'existente') {
                $_SESSION['Arquivos_Upload']['situacao'][$valor] = 'excluido'; // excluído- quando o arquivo já existe e deve ser excluido no sistema
            }

        }
        //passagem de parametro para não dar erro
        $valorEstimatoTotalAtual = str_replace(',','.',str_replace('.','',$valorEstimatoTotalAtual));
    }else{
        $Mens = 1;
        $Tipo = 2;
        $Mensagem = 'Selecione um anexo para ser retirado';
    }
}



if ($Critica == 1) {
    # Critica dos Campos #
    $Mens = 0;
    $Mensagem = "Informe: ";
    
    if($botao=='Voltar'){

        // volta pra tela anterior
        $Url = "CadTramitacaoEntrada.php?";
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
        if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
        header("location: ".$Url);
        exit();

    }  

    //Verifica se a acao foi preenchida
    if ($acao == '0_0_0_0_0') {

        if ($Mens == 1) {
            $Mensagem .= ", ";
        }
        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "Próxima Ação";
    
    }

    $arrAcao = explode("_",$acao);

    if($arrAcao[2] == 'S'){

        if($comissaoAtual == '0'){
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "Comissão de Licitação";
        }
    }

    if($anexoObrigatorio == 'S'){
        if (empty($_SESSION['Arquivos_Upload']) ) {

            if ($Mens == 1) {
                $Mensagem .= ", ";
            }
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "Esta ação exige anexação de um documento";
        
        }
    }

    //Verifica se o agente de Destino foi selecionado
    if ($agenteDestino == '0_0' ) {

        if ($Mens == 1) {
            $Mensagem .= ", ";
        }
        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "Agente de Tramitação Destino";
    
    }


    $arrAgDestVer = explode("_",$agenteDestino);



    if($receberProtocolo){

        //Verifica se o Usuario foi selecionado
        //Funciona apenas no caso de receber Protocolo

        if ($arrAcao[4] != 'S' ) {// verifica se a ação exige usuário ou pode ser enviada para todos do grupo

            if ($usuarioResponsavel == '0'){

                if ($Mens == 1) {
                    $Mensagem .= ", ";
                }
                $Mens = 1;
                $Tipo = 2;
                $Mensagem .= "Usuário responsável";

            }

        }else{

            if ($usuarioResponsavel == '0'){
                $usuarioResponsavel = $_SESSION['_cusupocodi_'];
            }

            
        }


    }else{
        //Verifica se o agente de Destino foi selecionado
        if($arrAgDestVer[1] == 'I' && $usuarioResponsavel <= 0 && $arrAcao[4] != 'S'){ 

            if ($Mens == 1) {
                $Mensagem .= ", ";
            }
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "Usuário Responsável";
        
        }
    }


    if($Mens == 0){

        if($botao=='Enviar'){
            
            $db = Conexao();
            $sql = "SELECT (ctramlsequ + 1)as novo_sequencial 
                    FROM sfpc.tbtramitacaolicitacao 
                    where cprotcsequ=".$numTramitacao."
                    order by ctramlsequ desc
                    limit 1 ";
        
            $resultadoPesquisa = $db->query($sql);
        
            if (PEAR::isError($resultadoPesquisa)) {
                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
            } else {
                $Linha = $resultadoPesquisa->fetchRow();
                $seqTramitacao = $Linha[0];//
                $seqTramAnterior = $Linha[0] -1 ;//
                $sequencialValido = false;
                while($sequencialValido!= true){
                    $db = Conexao();
                    $sql = "SELECT count(cprotcsequ)
                            FROM sfpc.tbtramitacaolicitacao 
                            where cprotcsequ = ".$numTramitacao." 
                                  AND ctramlsequ = ".$seqTramitacao;
                
                    $resultDuplicada = $db->query($sql);
                    if (PEAR::isError($resultadoPesquisa)) {
                        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                    } else {
                        $duplicado = $resultadoPesquisa->fetchRow();

                        if($duplicado > 0){
                            $sequencialValido = false;
                            $seqTramitacao++;
                        }else{
                            $sequencialValido = true;
                        }

                    }


                }    

            }



            $arrAgenteDestino = explode("_",$agenteDestino);
            $codAgenteDestino = $arrAgenteDestino[0];
            $arrAcao = explode("_",$acao);
            $codAcao = $arrAcao[0];
            $prazoAcao = $arrAcao[1];

            //informações sobre a ação
            $dataSaida = 'NULL';
            $sqlAcaoSel = "SELECT CTACAOSEQU, ETACAODESC, ATACAOPRAZ, FTACAOCOMI, FTACAOANEX, FTACAOFINA FROM SFPC.TBTRAMITACAOACAO 
                    WHERE CTACAOSEQU =".$arrAcao[0];
            $resultAcaoSel = $db->query($sqlAcaoSel);

            if (PEAR::isError($resultAcaoSel)) {
                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sqlAcaoSel");
            } else {
                $Linha = $resultAcaoSel->fetchRow();
                if($Linha[5] == 'S' ){
                    $dataSaida = 'NOW()';
                }                
            }


            # Encontra a informacao referente ao codigo da tramitacao enviado via GET
            $db = Conexao();

            if(!$comissaoAtual){
                $comissaoAtual = 'NULL';
            }
            if($usuarioResponsavel == '0' || $usuarioResponsavel == '' || empty($usuarioResponsavel)){
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
            }else{
                // atualiza a tramitação anterior
                $sqlUpdate = "UPDATE sfpc.tbtramitacaolicitacao
                                SET ttramlsaid = NOW(), ttramlulat = NOW(), cusupocodi = ".$_SESSION['_cusupocodi_']."
                                WHERE cprotcsequ = ".$numTramitacao." AND ctramlsequ = ". $seqTramAnterior;

                $resUpdate = executarSQL($db, $sqlUpdate);
                if (PEAR::isError($resUpdate)) {
                    cancelarTransacao($db);
                    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
                    $Botao = "";
                }else{

                    // Buscar o id da licitação para usar no ANEXO
                    $sequencialProtocolo = $numTramitacao;

                    // inserir documentos
                    if(!empty($_SESSION['Arquivos_Upload'])) {
                        // Quantidade anexo
                        $sql = ' SELECT count(*) FROM SFPC.TBTRAMITACAOLICITACAOANEXO WHERE  1=1';
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



                    if(!$receberProtocolo){
                        $Url = "CadTramitacaoEntrada.php?";
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
                        if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
                        header("location: ".$Url);
                        exit();
                    }

                }
            }
            



        }else{
            echo 'A variável $botao deveria conter "Enviar"';
        }
    }


  
}

if($Mens == 1){
    echo $Mensagem;
}else{
    echo '1';
}
?>