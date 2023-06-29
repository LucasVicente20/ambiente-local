<?php
// --------------------------------------------------------------------------------
// Portal da DGCO
// Programa: RelEmissaoCHFPdf.php
// Autor: Roberta Costa
// Data: 19/10/04
// Objetivo: Programa de Impressão do Certificado de Habilitação de Firmas
// Alterado: Rossana Lira
// Data: 16/05/07 - Troca do nome fornecedor para firma
// Data: 09/07/07 - Exibir mensagem de fornecedor c/certidões fora do
// prazo de validade
// Alterado: Rodrigo Melo
// Data: 25/04/2011 - Exibir a certidão de falência, no final de certidões obrigatórias, devido a solicitação do usuário. Tarefa Redmine: 2205.
// - Retirar exibição de classes de fornecedores (manter apenas o grupo), devido a solicitação do usuário. Tarefa Redmine: 2205.
// Alterado: Rodrigo Melo
// Data: 02/06/2011 - Tarefa Redmine: 2203 - Fornecedores devem estar associados a grupos de materiais
// - Alteração do nome do arquivo de "CadIncluirClasses.php" para "CadIncluirGrupos.php"
// OBS.: Tabulação 2 espaços
// --------------------------------------------------------------------------------
#-------------------------------------------------------------------------
# Alterado: Caio Coutinho
# Data: 04/07/2018
# Objetivo: Tarefa Redmine #198149
#-------------------------------------------------------------------------

// 220038--

// Sempre vai buscar o programa no servidor #
header("Expires: 0");
header("Cache-Control: private");

// Executa o controle de segurança #
session_cache_limiter('private');
session_start();

if (! @require_once dirname(__FILE__) . '/../bootstrap.php') {
    throw new Exception('Error Processing Request - Bootstrap', 1);
}

// Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $Sequencial = $_GET['Sequencial'];
    $Mensagem = urldecode($_GET['Mensagem']);
    if ($Mensagem != "") {
        $Mensagem = "Atenção! " . $Mensagem;
    }
}

// Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

// Fução exibe o Cabeçalho e o Rodapé #
//CabecalhoRodapeBG();
CabecalhoRodapePaisagem();

// Informa o Título do Relatório #
$TituloRelatorio = "EXTRATO ATAS - DETALHE";

// Cria o objeto PDF, o Default é formato Retrato, A4 e a medida em milímetros #
$pdf = new PDF("L", "mm", "A4");

// Define um apelido para o número total de páginas #
$pdf->AliasNbPages();

// Define as cores do preenchimentos que serão usados #
$pdf->SetFillColor(220, 220, 220);

// Adiciona uma página no documento #
$pdf->AddPage();

// Seta as fontes que serão usadas na impressão de strings #
$pdf->SetFont("Arial", "", 7);

$ata        = $_REQUEST['ata'];
$orgao      = $_REQUEST['orgao'];
$item       = $_REQUEST['item'];
$tipoItem   = $_REQUEST['tipoItem'];
$material   = $_REQUEST['tipoItem'] == 'M' ? $item : null;
$servico    = $_REQUEST['tipoItem'] == 'M' ? null : $item;

// $sql = " select ata.*, iarpn.aitarporde, iarpn.cmatepsequ, iarpn.cservpsequ, iarpn.eitarpdescmat, iarpn.eitarpdescse,";
// $sql .= " iarpn.aitarpqtor,iarpn.aitarpqtat from sfpc.tbataregistroprecointerna ata";
// $sql .= " inner join sfpc.tbitemataregistropreconova iarpn";
// $sql .= " on iarpn.carpnosequ = ata.carpnosequ";
// // $sql .= " inner join sfpc.tbparticipanteitematarp piarp";
// // $sql .= " on piarp.carpnosequ = ata.carpnosequ";
// // $sql .= " and piarp.citarpsequ = iarpn.citarpsequ";
// $sql .= " where ata.carpnosequ = " . $ata;
// if ($material != null) {
//     $sql .= " and iarpn.cmatepsequ=" . $material;
// } else {
//     $sql .= " and iarpn.cservpsequ=" . $servico;
// }



function getEntidade(Negocio_ValorObjeto_Carpnosequ $valorObjeto)
{
    $repositorioAtaRPNova   = new Negocio_Repositorio_AtaRegistroPrecoNova();
    $entidadeAta            = $repositorioAtaRPNova->procurar($valorObjeto);
    $dtoArray               = (array) $entidadeAta;    
    $userIdLog              = $dtoArray['cusupocodi'];

    if ($dtoArray['carpnotiat'] == 'I') {
        $repositorioAtaInterna  = new Negocio_Repositorio_AtaRegistroPrecoInterna();
        $dtoArray               = array_merge($dtoArray, (array) $repositorioAtaInterna->procurar($valorObjeto));
        $repositorioLicitacao   = new Negocio_Repositorio_LicitacaoPortal();

        $clicpoproc = new Negocio_ValorObjeto_Clicpoproc($dtoArray['clicpoproc']);
        $alicpoanop = new Negocio_ValorObjeto_Alicpoanop($dtoArray['alicpoanop']);
        $cgrempcodi = new Negocio_ValorObjeto_Cgrempcodi($dtoArray['cgrempcodi']);
        $ccomlicodi = new Negocio_ValorObjeto_Ccomlicodi($dtoArray['ccomlicodi']);
        $corglicodi = new Negocio_ValorObjeto_Corglicodi($dtoArray['corglicodi']);
        $dtoArray   = array_merge($dtoArray, (array) $repositorioLicitacao->procurar($clicpoproc, $alicpoanop, $cgrempcodi, $ccomlicodi, $corglicodi));

        $repositorioModalidade = new Negocio_Repositorio_ModalidadeLicitacao();
        $dtoArray = array_merge(
            $dtoArray,
            (array) $repositorioModalidade->consultarPorCodigo(
                new Negocio_ValorObjeto_Cmodlicodi($dtoArray['cmodlicodi'])
            )
        );
        $repositorioOrgaoLicitante = new Negocio_Repositorio_OrgaoLicitante();
        $dtoArray = array_merge(
            $dtoArray,
            (array) current($repositorioOrgaoLicitante->selecionaDescricaoOrgaoLicitante($corglicodi->getValor()))
        );
        $repositorioComissao = new Negocio_Repositorio_ComissaoLicitacao();
        $dtoArray = array_merge(
            $dtoArray,
            (array) $repositorioComissao->procurar($ccomlicodi)
        );
        $repositorioFornecedor = new Negocio_Repositorio_FornecedorCredenciado();
        $dtoArray = array_merge(
            $dtoArray,
            (array) $repositorioFornecedor->procurar(new Negocio_ValorObjeto_Aforcrsequ($dtoArray['aforcrsequ']))
        );
        $dtoArray['ccomlicodi'] = $ccomlicodi->getValor();

        //$repositorioDocumento   = new Negocio_Repositorio_DocumentoAtaRP();
        //$dtoArray['documentos'] = $repositorioDocumento->procurarPorCarpnosequ(new Negocio_ValorObjeto_Carpnosequ($dtoArray['carpnosequ']));

        //$dtoArray['documentos'] = $this->getNegocio()->consultarDocumentos($dtoArray['carpnosequ']);

        $repositorioParticipante    = new Negocio_Repositorio_ParticipanteAtaRP();
        $listaParticipantes         = $repositorioParticipante->procurarPorCarpnosequ(new Negocio_ValorObjeto_Carpnosequ($dtoArray['carpnosequ']));

        foreach ($listaParticipantes as $participante) {
            $objeto                         = current($repositorioOrgaoLicitante->selecionaDescricaoOrgaoLicitante($participante->corglicodi));
            $participante->eorglidesc       = $objeto->eorglidesc;
            $dtoArray['participantes'][]    = $participante;
        }
    } else {
        $repositorioAtaExterna  = new Negocio_Repositorio_AtaRegistroPrecoExterna();
        $dtoArray               = array_merge($dtoArray, (array) $repositorioAtaExterna->procurar($valorObjeto));

        $repositorioModalidade = new Negocio_Repositorio_ModalidadeLicitacao();
        $dtoArray = array_merge(
            $dtoArray,
            (array) $repositorioModalidade->consultarPorCodigo(
                new Negocio_ValorObjeto_Cmodlicodi($dtoArray['cmodlicodi'])
            )
        );

        //$dtoArray['documentos'] = $this->getNegocio()->consultarDocumentos($dtoArray['carpnosequ']);
        $repositorioFornecedor = new Negocio_Repositorio_FornecedorCredenciado();
        $dtoArray = array_merge(
            $dtoArray,
            (array) $repositorioFornecedor->procurar(new Negocio_ValorObjeto_Aforcrsequ($dtoArray['aforcrsequ']))
        );

        $repositorioParticipante    = new Negocio_Repositorio_ParticipanteAtaRP();
        $listaParticipantes         = $repositorioParticipante->procurarPorCarpnosequ(new Negocio_ValorObjeto_Carpnosequ($dtoArray['carpnosequ']));

        foreach ($listaParticipantes as $participante) {
            $objeto                         = current($repositorioOrgaoLicitante->selecionaDescricaoOrgaoLicitante($participante->corglicodi));
            $participante->eorglidesc       = $objeto->eorglidesc;
            $dtoArray['participantes'][]    = $participante;
        }
    }

    $dtoArray['cusupocodi'] = $userIdLog;

    return new ArrayObject($dtoArray);
}

function getDadosDoFornecedorDaAta($fornecedor)
{
    $cpfCnpj = ($fornecedor['aforcrccgc'] == '') ? $fornecedor['aforcrccpf'] : $fornecedor['aforcrccgc'];
    $cpfCnpjFormatado = (strlen($cpfCnpj) == 11) ? FormataCPF($cpfCnpj) : FormataCNPJ($cpfCnpj);
    $dadosFornecedor = $cpfCnpjFormatado . ' - ' . $fornecedor['nforcrrazs'];
    if (! empty($fornecedor['eforcrlogr'])) {
        $dadosFornecedor .= '<br>' . $fornecedor['eforcrlogr'] . ', ';
        $dadosFornecedor .= $fornecedor['aforcrnume'] . ', ';
        $dadosFornecedor .= $fornecedor['eforcrbair'] . ', ';
        $dadosFornecedor .= $fornecedor['nforcrcida'] . ' / ' . $fornecedor['cforcresta'];
    }

    return $dadosFornecedor;
}

function sqlItensAtaRegistroPreco($codigoAta)
{
    $sql = " select * from sfpc.tbitemataregistropreconova iarpn ";
    $sql .= " LEFT OUTER JOIN sfpc.tbmaterialportal m ON iarpn.cmatepsequ = m.cmatepsequ ";    				
    $sql .= " LEFT OUTER JOIN sfpc.tbservicoportal s ON iarpn.cservpsequ = s.cservpsequ ";
    $sql .= " LEFT JOIN sfpc.tbunidadedemedida um ON um.cunidmcodi = m.cunidmcodi ";
    $sql .= " where iarpn.carpnosequ =$codigoAta";

    return $sql;
}


function consultarItensAtaRegistroPreco($codigoAta)
{
    $itensAta = array();
    $sql = sqlItensAtaRegistroPreco($codigoAta);
    $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $sql);
    while ($resultado->fetchInto($item, DB_FETCHMODE_OBJECT)) {
        $itensAta[] = $item;
    }
    return $itensAta;
}

function sqlFornecedorDaAtaInterna($sequencialAta)
{
    $sql = "select forn.* ";
    $sql .= " from sfpc.tbataregistroprecointerna arp";
    $sql .= " join sfpc.tbfornecedorcredenciado forn";
    $sql .= " on forn.aforcrsequ = arp.aforcrsequ and arp.carpnosequ = $sequencialAta";

    return $sql;
}

function sqlFornecedorDaAtaExterna($sequencialAta)
{
    $sql = "select arp.* ";
    $sql .= " from sfpc.tbfornecedorcredenciado arp";       
    $sql .= " where arp.aforcrsequ = $sequencialAta";

    return $sql;
}


function consultarFornecedorDaAtaExterna($sequencialAta)
{
    $sql = sqlFornecedorDaAtaExterna($sequencialAta);
    $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $sql);
    $resultado->fetchInto($fornecedor, DB_FETCHMODE_OBJECT);
    return $fornecedor;
}

function consultarFornecedorDaAtaInterna($sequencialAta)
{
    $sql = sqlFornecedorDaAtaInterna($sequencialAta);
    $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $sql);
    $resultado->fetchInto($fornecedor, DB_FETCHMODE_OBJECT);
    return $fornecedor;
}

function consultarCentroCusto($entidade)
{
    $sqlCentroCusto = " SELECT ccp.ccenpocorg, ccp.ccenpounid, ccp.corglicodi
        FROM sfpc.tbcentrocustoportal ccp
        WHERE 1 = 1";
    $sqlCentroCusto .= " AND ccp.corglicodi = " . $entidade['corglicodi'];
            
    $resCentro = executarSQL(ClaDatabasePostgresql::getConexao(), $sqlCentroCusto);    
    $resultadoCentro = array();
    $resultCentro = null;
    while ($resCentro->fetchInto($resultCentro, DB_FETCHMODE_OBJECT)) {
        $resultadoCentro[] = $resultCentro;
    }


    return  $objetoDado = current($resultadoCentro);
}


    if (isset($_REQUEST['ata'])) {
        $carpnosequ = filter_var($_REQUEST['ata'], FILTER_SANITIZE_NUMBER_INT);
    }

    if (isset($_REQUEST['carpnosequ'])) {
        $carpnosequ = filter_var($_REQUEST['carpnosequ'], FILTER_SANITIZE_NUMBER_INT);
    }

    $valorObjetoAta = new Negocio_ValorObjeto_Carpnosequ($carpnosequ);

    $entidade = getEntidade($valorObjetoAta);
    $numeroAtaFormatadoOrigem = '';

    if($entidade['carpnotiat'] == "I"){   
        $objetoDado = consultarCentroCusto($entidade);            
        $numeroAtaFormatado = $objetoDado->ccenpocorg . str_pad($objetoDado->ccenpounid, 2, '0', STR_PAD_LEFT);
        $numeroAtaFormatado .= "." . str_pad($entidade['carpincodn'], 4, "0", STR_PAD_LEFT) . "/" . $entidade['aarpinanon'];
        
        if($entidade['carpnoseq1'] != '' || $entidade['carpnoseq1'] != null){
            $valorObjetoAtaOrigem = new Negocio_ValorObjeto_Carpnosequ($entidade['carpnoseq1']);
            $entidadeOrigem = getEntidade($valorObjetoAtaOrigem);
            if(!empty($entidadeOrigem)){  
                $objetoDadoOrigem = consultarCentroCusto($entidade);                
                $numeroAtaFormatadoOrigem = $objetoDadoOrigem->ccenpocorg . str_pad($objetoDadoOrigem->ccenpounid, 2, '0', STR_PAD_LEFT);
                $numeroAtaFormatadoOrigem .= "." . str_pad($entidadeOrigem['carpincodn'], 4, "0", STR_PAD_LEFT) . "/" . $entidadeOrigem['aarpinanon'];
                $numeroAtaFormatadoOrigem = $numeroAtaFormatadoOrigem;
            }
        }    

        $processo = str_pad($entidade['clicpoproc'], 4, '0', STR_PAD_LEFT);
        $anoProcesso = $entidade['alicpoanop'];
        $comissao = $entidade['ecomlidesc'];
        $modalidade = $entidade['emodlidesc'];
        $orgaoLicitante = $entidade['eorglidesc'];
        $objetivo = $entidade['xlicpoobje'];
        $dataInicial = (empty($entidade['tarpindini'])) ? '' : ClaHelper::converterDataBancoParaBr($entidade['tarpindini']);
        $vigencia = $entidade['aarpinpzvg'];

    } else {
        $numeroAtaFormatado = $entidade['carpexcodn'] . "/" . $entidade['aarpexanon'];
        $dataInicial        = (empty($entidade['tarpexdini'])) ? '' : ClaHelper::converterDataBancoParaBr($entidade['tarpexdini']);
        $processo           = $entidade['earpexproc'];    
        $anoProcesso        = $entidade['aarpexanon'];    
        $modalidade         = $entidade['emodlidesc'];
        $orgaoLicitante     = $entidade['eorglidesc'];
        $objetivo           = $entidade['earpexobje'];
        $orgaoLicitante     = $entidade['earpexorgg'];
        $vigencia           = $entidade['aarpexpzvg'];
    }
    
    $pdf->ln(03);
    $pdf->Cell(63, 5, "Nº da Ata", 1, 0, 'L', 1);
    $pdf->Cell(217, 5, $numeroAtaFormatado, 1, 0, 'L', 0);
    $breaks = array("<br />","<br>","<br/>"); 

    if($entidade['carpnotiat'] == "I" && $numeroAtaFormatadoOrigem != ''){
        $pdf->ln(05);        
        $pdf->Cell(63, 5, "Nº da Ata Origem", 1, 0, 'L', 1);
        $pdf->Cell(217, 5, $numeroAtaFormatadoOrigem, 1, 0, 'L', 0);
    }

    $pdf->ln(05);
    $pdf->Cell(63, 5, 'Processo Licitatório', 1, 0, 'L', 1);
    $pdf->Cell(217, 5, $processo, 1, 0, 'L', 0);

    $pdf->ln(05);
    $pdf->Cell(63, 5, 'Ano Processo', 1, 0, 'L', 1);
    $pdf->Cell(217, 5, $anoProcesso, 1, 0, 'L', 0);
    
    if($entidade['carpnotiat'] == "I"){
        $pdf->ln(05);
        $pdf->Cell(63, 5, 'Comissão', 1, 0, 'L', 1);
        $pdf->Cell(217, 5, $comissao, 1, 0, 'L', 0);
    }
    
    $pdf->ln(05);
    $pdf->Cell(63, 5, 'Modalidade', 1, 0, 'L', 1);
    $pdf->Cell(217, 5, $modalidade, 1, 0, 'L', 0);

    $pdf->ln(05);
    $pdf->Cell(63, 5, 'Órgão Licitante', 1, 0, 'L', 1);
    $pdf->Cell(217, 5, $orgaoLicitante, 1, 0, 'L', 0);        

    $pdf->ln(05);
    $pdf->Cell(63, 5, 'Objeto', 1, 0, 'L', 1);
    $pdf->Cell(217, 5, $objetivo, 1, 0, 'L', 0);

    $pdf->ln(05);
    $pdf->Cell(63, 5, 'Data Inicial da Ata', 1, 0, 'L', 1);
    $pdf->Cell(217, 5, $dataInicial, 1, 0, 'L', 0);


    $pdf->ln(05);
    $pdf->Cell(63, 5, 'Vigência da Ata', 1, 0, 'L', 1);
    $pdf->Cell(217, 5, $vigencia . ' Meses', 1, 0, 'L', 0);
    
    if (isset($entidade['participantes'])) {
        $pdf->ln(05);
        $participantes = "";        
        foreach ($entidade['participantes'] as $participante) {
            $participantes .= $participante->eorglidesc ."; ";            
        }

        $h0 = $pdf->GetStringHeight(217, 5, trim(str_ireplace($breaks, "\r\n", $participantes)), "L");
        if ($h0 < 5) {
            $h0 = 5;        
        }
        $pdf->Cell(63, $h0, 'Participantes', 1, 0, 'L', 1);
        $pdf->MultiCell(217,5,$participantes,1,"L",0);
    
    }else{
        $pdf->ln(05);
    }

    $fornecedorAtual = '';
    $fornecedorOriginal = getDadosDoFornecedorDaAta($entidade);    

    $h1 = 5;

    $h1 = $pdf->GetStringHeight(217, 5, trim(str_ireplace($breaks, "\r\n", $fornecedorOriginal)), "L");

    if ($h1 < 5) {
        $h1 = 5;        
    }

    $pdf->Cell(63, $h1, 'Fornecedor Original', 1, 0, 'L', 1);    
    $pdf->MultiCell(217, 5, str_ireplace($breaks, "\r\n", $fornecedorOriginal), 1, 1, 'L', 0);

    if($entidade['carpnotiat'] == "I"){
        if (isset($entidade['carpnoseq1']) && $entidade['carpnoseq1'] != "") {
            $objFornecedor = consultarFornecedorDaAtaInterna($entidade['carpnoseq1']);
            $fornecedorAtual = getDadosDoFornecedorDaAta((array) $objFornecedor);
        }
    }else{
        if (isset($entidade['aforcrseq1']) && $entidade['aforcrseq1'] != "") {
            $objFornecedor = consultarFornecedorDaAtaExterna($entidade['aforcrseq1']);                 
            $fornecedorAtual = getDadosDoFornecedorDaAta((array) $objFornecedor);
        }
    }

    $h2 = $pdf->GetStringHeight(217, 5, trim(str_ireplace($breaks, "\r\n", $fornecedorAtual)), "L"); 
    if ($h2 < 5) {
        $h2 = 5;        
    }
    $pdf->Cell(63, $h2, 'Fornecedor Atual', 1, 0, 'L', 1);
    $pdf->MultiCell(217, 5, str_ireplace($breaks, "\r\n", $fornecedorAtual), 1, 1, 'L', 0);

    $pdf->Cell(63, 10, 'Fornecedor Atual', 1, 0, 'L', 1);    
    $pdf->MultiCell(217, 10, str_ireplace($breaks, "\r\n", $fornecedorAtual), 1, 1, 'L', 0);
    $tipoControle = tipoControle($entidade['farpnotsal']); 

    $pdf->Cell(63, 5, 'Tipo de Controle', 1, 0, 'L', 1);
    $pdf->Cell(217, 5, strtoupper2($tipoControle), 1, 0, 'L', 0);

    //$pdf->ln(1);
    $pdf->Cell(280, 5, 'ITENS DA ATA', 1, 0, 'C', 1);

    $itensAta = consultarItensAtaRegistroPreco($entidade['carpnosequ']);

    $somatorio = 0;

    $pdf->ln();
    $pdf->SetFont("Arial", "", 7);
    $pdf->Cell(8, 10, 'LOTE', 1, 0, 'C', 1);
    $pdf->Cell(8, 10, " ORD.", 1, 0, "C", 1);
    $pdf->Cell(10, 10, 'TIPO', 1, 0, 'C', 1);        
    $pdf->Cell(15, 10, 'COD. RED.', 1, 0, 'C', 1);
    $pdf->Cell(25, 10, 'DESCRIÇÃO', 1, 0, 'C', 1);
    
    //$pdf->Cell(75, 5, '', 1, 0, 'C', 1);
    $x = $pdf->GetX() + 25;
    $y = $pdf->GetY();
    $pdf->MultiCell(25, 5, " DESCRIÇÃO DETALHADA", 1, "C", 1);
    $pdf->SetXY($x, $y);  

    $pdf->Cell(8, 10, 'UN.', 1, 0, 'C', 1);
    $pdf->Cell(19, 10, 'QTD ORIGINAL', 1, 0, 'C', 1);
    
    $x = $pdf->GetX() + 22;
    $y = $pdf->GetY();
    $pdf->MultiCell(22, 5, " VALOR ORIG. UNIT", 1, "C", 1);
    $pdf->SetXY($x, $y);    

    $x = $pdf->GetX() + 26;
    $y = $pdf->GetY();
    $pdf->MultiCell(26, 5, " VALOR ORIG. TOTAL", 1, "C", 1);
    $pdf->SetXY($x, $y);   
        
    $pdf->Cell(20, 10, 'QTD ATUAL', 1, 0, 'C', 1);
    $pdf->Cell(25, 10, 'VALOR ATUAL UNIT', 1, 0, 'C', 1);
    $pdf->Cell(28, 10, 'VALOR ATUAL TOTAL', 1, 0, 'C', 1);      
    $pdf->Cell(16, 10, " MARCA", 1, 0, "C", 1);
    $pdf->Cell(17, 10, " MODELO", 1, 0, "C", 1);
    $pdf->Cell(8, 10, " SIT. ", 1, 0, "C", 1);      
    $pdf->ln();

    foreach ($itensAta as $item) {
        // CADUM = material e CADUS = serviço
        $tipo = 'material';
        if (is_null($item->cmatepsequ) == true) {
            $tipo = 'servico';
        }

        $situacao = $item->fitarpsitu == 'A' ? 'Ativo' : 'Inativo';

        // Código, descrição e descrição detalhada do item
        $valorCodigo    = $item->cmatepsequ;
        $valorDescricao = $item->ematepdesc;
        $descDetalhada  = '';
        $unidade = $item->eunidmsigl;
        if ($tipo == 'servico') {
            $unidade = ' - ';
            $valorCodigo = $item->cservpsequ;
            $valorDescricao = $item->eservpdesc;
            $descDetalhada = $item->eitarpdescse;
        } else {
            if($item->fmatepgene == 'S') {
                $descDetalhada = $item->eitarpdescmat;
            }
        }

        $largura1 = 25; $largura2 = 25; $largura3 = 16; $largura4 = 17;
        $h = 5;

        $alturaDescricao    = $pdf->GetStringHeight($largura1, $h, $valorDescricao, "L");
        $alturaDescricaoDet = $pdf->GetStringHeight(25, 5, $descDetalhada, "L");
        $alturaMarca        = $pdf->GetStringHeight($largura3, $h, $item->eitarpmarc, "L");
        $alturaModelo       = $pdf->GetStringHeight($largura4, $h, $item->eitarpmode, "L");

        // Calcular a maior altura
        $hm = ($alturaDescricao > $alturaDescricaoDet) ? $alturaDescricao : $alturaDescricaoDet;
        $hm = ($hm > $alturaMarca) ? $hm : $alturaMarca;
        $hm = ($hm > $alturaModelo) ? $hm : $alturaModelo;
        
        $alturaDescricao    = $hm / ($alturaDescricao / $h);        
        $alturaDescricaoDet = $hm / ($alturaDescricaoDet / $h);  
        $alturaMarca        = $hm / ($alturaMarca / $h);   
        $alturaModelo       = $hm / ($alturaModelo / $h);        

        $pdf->Cell(8, $hm, $item->citarpnuml, 1, 0, 'C', 0);
        $pdf->Cell(8, $hm, $item->aitarporde, 1, 0, 'C', 0);
        $pdf->Cell(10, $hm, $item->cmatepsequ != null ? 'CADUM' : 'CADUS', 1, 0, 'C', 0);        
        $pdf->Cell(15, $hm, $valorCodigo, 1, 0, 'C', 0);
                
        $x = $pdf->GetX() + $largura1;
        $y = $pdf->GetY();
        $pdf->MultiCell($largura1, $alturaDescricao, $valorDescricao, 1, "L", 0);
        $pdf->SetXY($x, $y);

        $x = $pdf->GetX() + $largura2;
        $y = $pdf->GetY();
        $pdf->MultiCell($largura2, $alturaDescricaoDet, $descDetalhada, 1, "L", 0);
        $pdf->SetXY($x, $y);

        $pdf->Cell(8, $hm, $unidade, 1, 0, 'C', 0);
        $pdf->Cell(19, $hm, converte_valor_licitacao($item->aitarpqtor), 1, 0, 'C', 0);
        $pdf->Cell(22, $hm, converte_valor_licitacao($item->vitarpvori), 1, 0, 'C', 0);
        $pdf->Cell(26, $hm, converte_valor_licitacao($item->aitarpqtor * $item->vitarpvori), 1, 0, 'C', 0);        
        $pdf->Cell(20, $hm, $item->aitarpqtat != 0 ? converte_valor_licitacao($item->aitarpqtat) : '---', 1, 0, 'C', 0);
        $pdf->Cell(25, $hm, $item->vitarpvatu != 0 ? converte_valor_licitacao($item->vitarpvatu) : '---', 1, 0, 'C', 0);
        $pdf->Cell(28, $hm, ($item->vitarpvatu * $item->aitarpqtat) != 0 ? converte_valor_licitacao($item->vitarpvatu * $item->aitarpqtat) : '---', 1, 0, 'C', 0);      
        
        $x = $pdf->GetX() + $largura3;
        $y = $pdf->GetY();
        $pdf->MultiCell($largura3, $alturaMarca, $item->eitarpmarc, 1, "C", 0);
        $pdf->SetXY($x, $y);
        
        $x = $pdf->GetX() + $largura4;
        $y = $pdf->GetY();
        $pdf->MultiCell($largura4, $alturaModelo, $item->eitarpmode, 1, "C", 0);
        $pdf->SetXY($x, $y);

        $pdf->Cell(8, $hm, $situacao, 1, 0, 'C', 0);
        $pdf->ln();
    }


$pdf->Output();
