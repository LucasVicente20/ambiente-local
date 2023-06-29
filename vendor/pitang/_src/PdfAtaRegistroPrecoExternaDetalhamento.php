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
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 */
class PdfAtaRegistroPrecoExternaDetalhamento extends AbstractPdfRegistroPreco
{

    /**
     *
     * @var integer
     */
    private $ano;

    /**
     *
     * @var integer
     */
    private $processo;

    /**
     *
     * @var integer
     */
    const ALTURA_PADRAO = 4;

    /**
     *
     * @param string $orientacao
     * @param string $unidadeMedida
     * @param string $formato
     */
    public function __construct($orientacao = "L", $unidadeMedida = "mm", $formato = "A4")
    {
        parent::__construct($orientacao, $unidadeMedida, $formato);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see AbstractPdfRegistroPreco::getTitulo()
     */
    public function getTitulo()
    {
        return "Detalhamento de Ata de Registro de Preço Externa";
    }

    /* Método responsável por gerar o relatório */
    public function gerarRelatorio($name = 'DetalhamentoAtaExterna_')
    {
        // echo '<pre>';
        // print_r($_POST);
        // die;
        $dadoLicitacao = $this->dadosAtaLicitacao();

        if ($dadoLicitacao->carpnosequ != null) {
            $itensIntencoes = $this->consultarItemRegistroPreco($dadoLicitacao->carpnosequ);
        }

        if ($itensIntencoes != null) {
            $this->montarDadosItensIntencao($itensIntencoes);
        }

        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        $this->Output($name. date('d/m/Y_h_i_s') .'.pdf', 'I');
    }

    /**
     *
     * @return mixed
     */
    private function loadLicitacaoAta()
    {
        $licitacaoAtas = $this->consultarProcessoExterno($this->processo);
        return $licitacaoAtas;
    }

    /**
     *
     * @param integer $carpnosequ
     */
    private function consultarItemRegistroPreco($carpnosequ)
    {
        $sql = Dados_Sql_ItemAtaRegistroPrecoNova::sqlFind(new Negocio_ValorObjeto_Carpnosequ($carpnosequ));

        $resultado = ClaDatabasePostgresql::executarSQL($sql);

        ClaDatabasePostgresql::hasError($resultado);

        return $resultado;
    }

    /**
     *
     * @return mixed
     */
    private function dadosAtaLicitacao()
    {
        $licitacaoAtas = $this->loadLicitacaoAta();

        $modalidade = $this->consultarModalidade($licitacaoAtas->cmodlicodi);

        $dataFormato = date("d/m/Y", strtotime($licitacaoAtas->tarpexdini));

        // $fornecedorOriginal = $this->consultarFornecedor($licitacaoAtas->aforcrsequ);
        $fornecedorOriginal = current(FornecedorService::getFornecedorOriginalAtaExterna($licitacaoAtas->carpnosequ));
        $numeroCnpjOrCpfOriginal = $fornecedorOriginal->aforcrccgc != null ? $fornecedorOriginal->aforcrccgc : $fornecedorOriginal->aforcrccpf;
        $blocoFornecedorOriginal = FormataCpfCnpj($numeroCnpjOrCpfOriginal) . ' - ' . $fornecedorOriginal->nforcrrazs;
        $blocoFornecedorOriginal .= '  ' . $fornecedorOriginal->eforcrlogr;

        $blocoFornecedorAtual = '';
        if (! empty($licitacaoAtas->aforcrseq1)) {
            // $fornecedorAtual = $this->consultarFornecedor();
            $fornecedorAtual = current(FornecedorService::getFornecedorAtualAtaExterna($licitacaoAtas->carpnosequ));
            // echo '<pre>';
            // print_r($fornecedorAtual);
            // die;
            $numeroCnpjOrCpfAtual = $fornecedorAtual->aforcrccgc != null ? $fornecedorAtual->aforcrccgc : $fornecedorAtual->aforcrccpf;
            $blocoFornecedorAtual = FormataCpfCnpj($numeroCnpjOrCpfAtual) . ' - ' . $fornecedorAtual->nforcrrazs;
            $blocoFornecedorAtual .= '  ' . $fornecedorAtual->eforcrlogr;
        }

        $this->SetFillColor(220, 220, 220);

        $this->Cell(50, 6, " Nº Ata Externa ", 1, 0, "L", 1);
        $this->Cell(230, 6, $licitacaoAtas->carpexcodn, 1, 0, "L", 0);
        $this->Ln();

        $this->Cell(50, 6, "Ano Ata Externa", 1, 0, "L", 1);
        $this->Cell(230, 6, $licitacaoAtas->aarpexanon, 1, 0, "L", 0);
        $this->Ln();

        $this->Cell(50, 6, " Processo Licitatório Externo", 1, 0, "L", 1);
        $this->Cell(230, 6, $licitacaoAtas->earpexproc, 1, 0, "L", 0);
        $this->Ln();

        $this->Cell(50, 6, " Modalidade ", 1, 0, "L", 1);
        $this->Cell(230, 6, $modalidade->emodlidesc, 1, 0, "L", 0);
        $this->Ln();

        /* Verificar esse valor */
        $this->Cell(50, 6, " Órgão Gestor da Ata Externa ", 1, 0, "L", 1);
        $this->Cell(230, 6, $licitacaoAtas->earpexorgg, 1, 0, "L", 0);
        $this->Ln();

        $l = $this->getInstance()->GetStringHeight(120, 6, trim($licitacaoAtas->earpexobje), "L");
        $altura = ceil($l / 2) + 1;
        if ($l <= 6) {
            $altura = $l;
        }
        $this->Cell(50, $altura, " Objeto ", 1, 0, "L", 1);
        $this->MultiCell(230, 5, $licitacaoAtas->earpexobje, 1, "L", 0);

        $this->Cell(50, 6, " Data Inicial ", 1, 0, "L", 1);
        $this->Cell(230, 6, $dataFormato, 1, 0, "L", 0);
        $this->Ln();

        $this->Cell(50, 6, " Vigência ", 1, 0, "L", 1);
        $this->Cell(230, 6, $licitacaoAtas->aarpexpzvg . " Meses", 1, 0, "L", 0);
        $this->Ln();

        $this->Cell(50, 6, " Fornecedor Original ", 1, 0, "L", 1);
        $this->Cell(230, 6, $blocoFornecedorOriginal, 1, 0, "L", 0);
        $this->Ln();

        $this->Cell(50, 6, " Fornecedor Atual ", 1, 0, "L", 1);
        $this->Cell(230, 6, $blocoFornecedorAtual, 1, 0, "L", 0);
        $this->Ln();

        $this->SetFillColor(220, 220, 220);

        $this->Cell(8, 10, " LOTE ", 1, 0, "C", 1);
        $this->Cell(8, 10, " ORD.", 1, 0, "C", 1);
        $this->Cell(10, 10, " TIPO ", 1, 0, "C", 1);
        $this->Cell(15, 10, " COD. RED. ", 1, 0, "C", 1);
        $this->Cell(25, 10, " DESCRIÇÃO", 1, 0, "C", 1);
        
        //$this->Cell(25, 12, " DESCRIÇÃO DET", 1, 0, "C", 1);
        
        $x = $this->GetX() + 25;
        $y = $this->GetY();
        $this->MultiCell(25, 5, " DESCRIÇÃO DETALHADA", 1, "C", 1);
        $this->SetXY($x, $y);     

        $this->Cell(8, 10, " UND", 1, 0, "C", 1);
        $this->Cell(19, 10, " QTD ORIGINAL", 1, 0, "C", 1);
        
        $x = $this->GetX() + 22;
        $y = $this->GetY();
        $this->MultiCell(22, 5, " VALOR ORIG. UNIT", 1, "C", 1);
        $this->SetXY($x, $y);    
        
        $x = $this->GetX() + 26;
        $y = $this->GetY();
        $this->MultiCell(26, 5, " VALOR ORIG. TOTAL", 1, "C", 1);
        $this->SetXY($x, $y);    
        
        $this->Cell(20, 10, " QTD ATUAL", 1, 0, "C", 1);
        $this->Cell(25, 10, " VALOR ATUAL UNIT", 1, 0, "C", 1);
        $this->Cell(28, 10, " VALOR ATUAL TOTAL", 1, 0, "C", 1);
        $this->Cell(16, 10, " MARCA ", 1, 0, "C", 1);
        $this->Cell(17, 10, " MODELO ", 1, 0, "C", 1);
        $this->Cell(8, 10, " SIT. ", 1, 0, "C", 1);

        $this->Ln();
        return $licitacaoAtas;
    }

    /**
     *
     * @param unknown $itemRegistroPreco
     */
    private function montarDadosItensIntencao($itemRegistroPreco)
    {
        $this->SetFillColor(255, 255, 255);
        foreach ($itemRegistroPreco as $item) {
            // CADUM = material e CADUS = serviço
            $descricaoTipo = 'CADUM';
            $descDetalhada = '';
            $unidade = $item->eunidmsigl;
            if (is_null($item->cmatepsequ) == true) {
                $tipo = 'CADUS';
                $descDetalhada = $item->eitarpdescse;
            } else {
                if($item->fmatepgene == 'S') {
                    $descDetalhada = $item->eitarpdescmat;
                }
            }

            // Código do item
            $codReduzido = $item->cmatepsequ;
            if ($tipo == 'CADUS') {
                $unidade = ' - ';
                $codReduzido = $item->cservpsequ;
            }

            // Informa o número de ordem
            $descricao = $item->ematepdesc != null ? $item->ematepdesc : $item->eservpdesc;
            $tipo = $descricaoTipo;
            $total = $item->vitarpvatu * $item->aitarpqtor;

            $situacao = $item->fitarpsitu == 'A' ? 'Ativo' : 'Inativo';

            $alturaDesc = $this->getInstance()->GetStringHeight(25, self::ALTURA_PADRAO, trim($descricao), "L");
            $alturaDescDet = $this->getInstance()->GetStringHeight(25, self::ALTURA_PADRAO, trim($descDetalhada), "L");
            $alturaModelo = $this->getInstance()->GetStringHeight(16, self::ALTURA_PADRAO, trim($item->eitarpmode), "L");
            $alturaMarca = $this->getInstance()->GetStringHeight(17, self::ALTURA_PADRAO, trim($item->eitarpmarc), "L");

            // Verifica a maior altura
            $maiorAltura = ($alturaDesc > $alturaDescDet) ? $alturaDesc : $alturaDescDet;
            $maiorAltura = ($maiorAltura > $alturaModelo) ? $maiorAltura : $alturaModelo;
            $maiorAltura = ($maiorAltura > $alturaMarca) ? $maiorAltura : $alturaMarca;

            // Verifica a altura correta para a célula que pode ter mais de uma linha
            $alturaDesc     = $maiorAltura / ($alturaDesc / self::ALTURA_PADRAO);
            $alturaDescDet  = $maiorAltura / ($alturaDescDet / self::ALTURA_PADRAO);
            $alturaModelo   = $maiorAltura / ($alturaModelo / self::ALTURA_PADRAO);
            $alturaMarca    = $maiorAltura / ($alturaMarca / self::ALTURA_PADRAO);        

            $this->Cell(8, $maiorAltura, $item->citarpnuml, 1, 0, "C", 1);
            $this->Cell(8, $maiorAltura, $item->aitarporde, 1, 0, "C", 1);
            $this->Cell(10, $maiorAltura, $tipo, 1, 0, "C", 1);
            $this->Cell(15, $maiorAltura, $codReduzido, 1, 0, "C", 1);
            
            $x = $this->GetX() + 25;
            $y = $this->GetY();
            $this->MultiCell(25, $alturaDesc, removeSimbolos($descricao), 1, "L", 0);
            $this->SetXY($x, $y);
            
            $x = $this->GetX() + 25;
            $y = $this->GetY();
            $this->MultiCell(25, $alturaDescDet, removeSimbolos($descDetalhada), 1, "L", 0);
            $this->SetXY($x, $y);
            
            $this->Cell(8, $maiorAltura, $unidade, 1, 0, "C", 1);
            $this->Cell(19, $maiorAltura, converte_valor_licitacao($item->aitarpqtor), 1, 0, "C", 1);
            $this->Cell(22, $maiorAltura, converte_valor_licitacao($item->vitarpvori), 1, 0, "C", 1);
            $valoOriginalTotal = $item->aitarpqtor * $item->vitarpvori;
            $this->Cell(26, $maiorAltura, converte_valor_licitacao($valoOriginalTotal), 1, 0, "C", 1);            
            $this->Cell(20, $maiorAltura, converte_valor_licitacao($item->aitarpqtat), 1, 0, "C", 1);
            $this->Cell(25, $maiorAltura, converte_valor_licitacao($item->vitarpvatu), 1, 0, "C", 1);
            $valorAtualTotal = $item->aitarpqtat * $item->vitarpvatu;
            $this->Cell(28, $maiorAltura, converte_valor_licitacao($valorAtualTotal), 1, 0, "C", 1);
            
            $x = $this->GetX() + 16;
            $y = $this->GetY();
            $this->MultiCell(16, $alturaMarca, $item->eitarpmarc, 1, "L", 0);
            $this->SetXY($x, $y);            

            $x = $this->GetX() + 17;
            $y = $this->GetY();
            $this->MultiCell(17, $alturaModelo, $item->eitarpmode, 1, "L", 0);
            $this->SetXY($x, $y);
            
            $this->Cell(8, $maiorAltura, $situacao, 1, 0, "C", 1);
            $this->Ln();
        }

        $this->Ln();
    }

    /**
     *
     * @param integer $processo
     * @return string
     */
    private function sqlProcessoExterno($processo)
    {
        $sql = "SELECT a.carpnosequ, a.aarpexanon, a.carpexcodn, a.earpexproc, a.cmodlicodi,";
        $sql .= " a.earpexorgg, a.earpexobje, a.tarpexdini, a.aarpexpzvg, a.aforcrsequ, a.aforcrseq1, a.farpexsitu, a.cusupocodi, a.tarpinulat";
        $sql .= " FROM sfpc.tbataregistroprecoexterna a";
        $sql .= " where a.earpexproc = '$processo'";

        return $sql;
    }

    private function sqlModalidade($codigo)
    {
        $sql = "select m.emodlidesc from sfpc.tbmodalidadelicitacao m";
        $sql .= " where m.cmodlicodi=" . $codigo;
        return $sql;
    }

    private function sqlFornecedor($codigofornecedor)
    {
        $sql = "select f.aforcrccgc, f.aforcrccpf, f.nforcrrazs, f.eforcrlogr";
        $sql .= " from sfpc.tbfornecedorcredenciado f where f.aforcrsequ =" . $codigofornecedor;
        return $sql;
    }

    private function sqlItemAtaExterna($codigoAta)
    {
        $sql = "SELECT carpnosequ, citarpsequ, aitarporde, cmatepsequ, cservpsequ, aitarpqtor,";
        $sql .= "aitarpqtat, vitarpvori, vitarpvatu, citarpnuml, eitarpmarc, eitarpmode,";
        $sql .= "eitarpdescmat, eitarpdescse, fitarpsitu, fitarpincl, fitarpexcl,";
        $sql .= "titarpincl, cusupocodi, titarpulat";
        $sql .= " FROM sfpc.tbitemataregistropreconova i";
        $sql .= " where i.carpnosequ =" . $codigoAta;

        return $sql;
    }

    /**
     *
     * @param integer $processo
     * @return mixed
     */
    private function consultarProcessoExterno($processo)
    {
        $repositorio = new Negocio_Repositorio_AtaRegistroPrecoExterna();
        return $repositorio->procurar(new Negocio_ValorObjeto_Carpnosequ($processo));
    }

    private function consultarModalidade($codigo)
    {
        $db = Conexao();
        $sql = $this->sqlModalidade($codigo);
        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($modalidade, DB_FETCHMODE_OBJECT);

        return $modalidade;
    }

    private function consultarFornecedor($codigo)
    {
        $db = Conexao();
        $sql = $this->sqlFornecedor($codigo);
        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($fornecedor, DB_FETCHMODE_OBJECT);
        return $fornecedor;
    }

    private function consultarItemAtaInterna($codigoAta)
    {
        $resultados = array();

        $db = Conexao();
        $sql = $this->sqlItemAtaExterna($codigoAta);
        $resultado = executarSQL($db, $sql);
        while ($resultado->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            array_push($resultados, $item);
        }
        return $resultados;
    }

    private function obterOrgaoUsuarioLogado()
    {
        $arrayGlobals = new ArrayObject();

        $arrayGlobals['session'] = $_SESSION;
        $grupoUsuario = $arrayGlobals['session']['_cgrempcodi_'];

        // Recupera o orgão do usuário logado
        $orgaoUsuario = $this->getOrgaoLicitanteCodigo($grupoUsuario);

        return $orgaoUsuario;
    }

    private function getOrgaoLicitanteCodigo($grupoCodigo)
    {
        $sql = "SELECT x.corglicodi FROM sfpc.tbgrupoorgao x WHERE x.cgrempcodi =" . $grupoCodigo;
        $database = & Conexao();
        $resultado = executarSQL($database, $sql);
        $resultado->fetchInto($orgao, DB_FETCHMODE_OBJECT);

        return (integer) $res;
    }

    public function setProcesso($processo)
    {
        $this->processo = $processo;
    }

    public function setAno($ano)
    {
        $this->ano = $ano;
    }
}
