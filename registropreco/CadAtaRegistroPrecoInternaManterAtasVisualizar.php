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
 * @category  Pitang Registro Preço
 *
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 */

 // 220038--
 
if (!@require_once dirname(__FILE__).'/../bootstrap.php') {
    throw new Exception('Error Processing Request - Bootstrap', 1);
}

Seguranca();

global $SimboloConcatenacaoArray, $SimboloConcatenacaoDesc;

class RegistroPreco_Dados_CadAtaRegistroPrecoInternaManterAtasVisualizar extends Dados_Abstrata
{
    /**
     * @param unknown $processo
     * @param unknown $orgao
     * @param unknown $ano
     * @param unknown $chaveAta
     *
     * @throws InvalidArgumentException
     */
    public function sqlAtaPorchave($processo, $orgao, $ano, $chaveAta)
    {
        if (!filter_var($processo, FILTER_VALIDATE_INT)) {
            throw new InvalidArgumentException('$processo deve ser inteiro válido');
        }

        if (!filter_var($orgao, FILTER_VALIDATE_INT)) {
            throw new InvalidArgumentException('$orgaoUsuario deve ser inteiro válido');
        }

        if (!filter_var($ano, FILTER_VALIDATE_INT)) {
            throw new InvalidArgumentException('$ano deve ser inteiro válido');
        }

        if (!filter_var($chaveAta, FILTER_VALIDATE_INT)) {
            throw new InvalidArgumentException('$chaveAta deve ser inteiro válido');
        }

        $ano = filter_var($ano, FILTER_SANITIZE_NUMBER_INT);
        $processo = filter_var($processo, FILTER_SANITIZE_NUMBER_INT);
        $orgao = filter_var($orgao, FILTER_SANITIZE_NUMBER_INT);
        $chaveAta = filter_var($chaveAta, FILTER_SANITIZE_NUMBER_INT);

        $sql = "select a.aarpinpzvg, a.tarpindini, f.nforcrrazs, d.edoclinome,";
        $sql .= " a.corglicodi, a.carpnosequ, a.alicpoanop, s.csolcosequ, a.aarpinanon, carpnoseq1, a.cgrempcodi, a.cusupocodi, a.earpinobje,";

        $sql .= " f.nforcrrazs, f.aforcrccgc, f.aforcrccpf, f.eforcrlogr, ";
        $sql .= " f.aforcrnume, f.eforcrbair, f.nforcrcida, f.cforcresta, ";

        $sql .= " fa.nforcrrazs as razaoFornecedorAtual, fa.aforcrccgc as cgcFornecedorAtual, fa.aforcrccpf as cpfFornecedorAtual, fa.eforcrlogr as logradouroFornecedorAtual, ";
        $sql .= " fa.aforcrnume as numeroEnderecoFornecedorAtual, fa.eforcrbair as bairroFornecedorAtual, fa.nforcrcida as cidadeFornecedorAtual, fa.cforcresta as estadoFornecedorAtual ";

        $sql .= " from sfpc.tbataregistroprecointerna a";

        $sql .=  " left outer join sfpc.tbsolicitacaolicitacaoportal s";
        $sql .=  " on (s.clicpoproc = a.clicpoproc";
        $sql .=  " and s.alicpoanop = a.alicpoanop";
        $sql .=  " and s.ccomlicodi = a.ccomlicodi";
        $sql .=  " and s.corglicodi = a.corglicodi)";

        $sql .= " left outer join sfpc.tbfornecedorcredenciado f";
        $sql .= " on f.aforcrsequ = a.aforcrsequ";

        $sql .= " left outer join sfpc.tbfornecedorcredenciado fa";
        $sql .= " on fa.aforcrsequ = (select afa.aforcrsequ from sfpc.tbataregistroprecointerna afa where afa.carpnosequ = a.carpnoseq1)";

        $sql .= " left outer join sfpc.tbdocumentolicitacao d";
        $sql .= " on d.clicpoproc =a.clicpoproc";
        $sql .= " and d.clicpoproc = %d";
        $sql .= " and d.corglicodi = %d";
        $sql .= " and d.alicpoanop = %d";
        $sql .= ' where a.carpnosequ = %d';

        return sprintf($sql, $processo, $orgao, $ano, $chaveAta);
    }

    /**
     * @param int $ano
     * @param int $processo
     * @param int $orgaoUsuario
     *
     * @throws InvalidArgumentException
     */
    public function sqlLicitacaoAtaInterna($ano, $processo, $orgaoUsuario)
    {
        if (!filter_var($ano, FILTER_VALIDATE_INT)) {
            throw new InvalidArgumentException('$ano deve ser inteiro válido');
        }

        if (!filter_var($processo, FILTER_VALIDATE_INT)) {
            throw new InvalidArgumentException('$processo deve ser inteiro válido');
        }

        if (!filter_var($orgaoUsuario, FILTER_VALIDATE_INT)) {
            throw new InvalidArgumentException('$orgaoUsuario deve ser inteiro válido');
        }

        $ano = filter_var($ano, FILTER_SANITIZE_NUMBER_INT);
        $processo = filter_var($processo, FILTER_SANITIZE_NUMBER_INT);
        $orgaoUsuario = filter_var($orgaoUsuario, FILTER_SANITIZE_NUMBER_INT);

        $sql = 'SELECT distinct sc.csolcocodi,
    sc.asolcoanos, l.clicpoproc,';
        $sql .= ' l.alicpoanop,';
        $sql .= ' l.xlicpoobje,';
        $sql .= ' l.ccomlicodi,';
        $sql .= ' c.ecomlidesc,';
        $sql .= ' o.corglicodi,';
        $sql .= ' o.eorglidesc,';
        $sql .= ' m.emodlidesc,';
        $sql .= ' l.clicpocodl,';
        $sql .= ' l.alicpoanol';
        $sql .= ' from sfpc.tblicitacaoportal l';
        $sql .= ' inner join sfpc.tborgaolicitante o';
        $sql .= ' on o.corglicodi= %d';
        $sql .= ' and l.corglicodi = o.corglicodi';
        $sql .= ' inner join sfpc.tbcomissaolicitacao c';
        $sql .= ' on l.ccomlicodi = c.ccomlicodi';
        $sql .= ' inner join sfpc.tbmodalidadelicitacao m';
        $sql .= ' on l.cmodlicodi = m.cmodlicodi';
        $sql .= ' INNER JOIN sfpc.tbsolicitacaolicitacaoportal slp
        ON slp.clicpoproc = l.clicpoproc
    AND slp.alicpoanop = l.alicpoanop INNER JOIN sfpc.tbsolicitacaocompra sc
        ON sc.csolcosequ = slp.csolcosequ';
        $sql .= ' where l.alicpoanop = %d';
        $sql .= ' and l.clicpoproc = %d';

        return sprintf($sql, $orgaoUsuario, $ano, $processo);
    }

    /**
     * Seleciona os órgãos participantes de uma ata.
     *
     * @param integer $processo da ata
     * @param integer $ano da ata
     * @param integer $orgaoGestor da ata
     *
     * @return string
     */
     public static function sqlOrgaosParticipantesAta($processo, $ano, $orgaoGestor)
     {
         if (!filter_var($processo, FILTER_VALIDATE_INT)) {
             throw new InvalidArgumentException('$processo deve ser inteiro válido');
         }
 
         if (!filter_var($ano, FILTER_VALIDATE_INT)) {
             throw new InvalidArgumentException('$ano deve ser inteiro válido');
         }
 
         if (!filter_var($orgaoGestor, FILTER_VALIDATE_INT)) {
             throw new InvalidArgumentException('$orgaoUsuario deve ser inteiro válido');
         }
 
         $processo = filter_var($processo, FILTER_SANITIZE_NUMBER_INT);
         $ano = filter_var($ano, FILTER_SANITIZE_NUMBER_INT);
         $orgaoGestor = filter_var($orgaoGestor, FILTER_SANITIZE_NUMBER_INT);
 
         $sql = "
             SELECT distinct o.eorglidesc
             FROM sfpc.tbparticipanteatarp p 
             INNER JOIN sfpc.tborgaolicitante o ON o.corglicodi = p.corglicodi
             WHERE p.carpnosequ IN 
                 (SELECT a.carpnosequ
                 FROM sfpc.tbataregistroprecointerna a
                 LEFT OUTER JOIN sfpc.tbsolicitacaolicitacaoportal s ON
                     (s.clicpoproc = a.clicpoproc
                     AND s.alicpoanop = a.alicpoanop
                     AND s.ccomlicodi = a.ccomlicodi
                     AND s.corglicodi = a.corglicodi)
                 WHERE a.clicpoproc   = %d
                     AND a.alicpoanop = %d
                     AND a.corglicodi = %d 
                 ORDER BY a.carpnosequ)";
         return sprintf($sql, $processo, $ano, $orgaoGestor);
     }

    /* Consulta o número maior p */
    public function sqlCodigoMaximoDocumento($processo, $orgao, $ano, $grupo)
    {
        $sql = 'select max(d.cdoclicodi) from sfpc.tbdocumentolicitacao d';
        $sql .= 'where d.clicpoproc ='.$processo;
        $sql .= 'and d.cgrempcodi ='.$grupo;
        $sql .= 'and d.corglicodi ='.$orgao;
        $sql .= 'and d.alicpoanop ='.$ano;

        return $sql;
    }

    /**
     * [carregarTodosDocumentosAta description]
     *
     * @param Negocio_ValorObjeto_Carpnosequ $carpnosequ
     *            [description]
     * @return [type] [description]
     */
    public function carregarTodosDocumentosAta(Negocio_ValorObjeto_Carpnosequ $carpnosequ)
    {
        $db = Conexao();
        $sql = sprintf("
                SELECT 
                carpnosequ,
                cdocatsequ,
                edocatnome,
                encode(idocatarqu, 'base64') as idocatarqu,                
                tdocatcada,
                cusupocodi,
                tdocatulat

                  FROM sfpc.tbdocumentoatarp
                 WHERE carpnosequ = %d
            ", $carpnosequ->getValor());

            
        $resultado = executarSQL($db, $sql);

        $documentos = array();
        $documento = null;
        while ($resultado->fetchInto($documento, DB_FETCHMODE_OBJECT)) {
            $documentos[] = $documento;
        }
        if (PEAR::isError($resultado)) {
            ExibeErroBD(__FILE__ . "\nLinha: ".__LINE__."\nSql: " . $database->getMessage());
        }
        $db->disconnect();

        return $documentos;
    }

    public function sqlConsultarCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi)
    {
        $sql = "
            SELECT
                   ccp.ccenpocorg, ccp.ccenpounid, ccp.corglicodi
              FROM sfpc.tbcentrocustoportal ccp
             WHERE 1=1
        ";

        if ($corglicodi != null || $corglicodi != "") {
          $sql .= " AND ccp.corglicodi = %d";
        }

        return sprintf($sql, $corglicodi);
    }

    public function sqlConsultarProcurarAta($carpnosequ)
    {
        $sql = "
            SELECT * FROM sfpc.tbataregistroprecointerna WHERE carpnosequ = %d             
        ";

        return sprintf($sql, $carpnosequ);
    }
}

class RegistroPreco_Negocio_CadAtaRegistroPrecoInternaManterAtasVisualizar extends Negocio_Abstrata
{
    /**
     * [carregarTodosDocumentosAta description]
     *
     * @param Negocio_ValorObjeto_Carpnosequ $carpnosequ
     *            [description]
     * @return [type] [description]
     */
    public function carregarTodosDocumentosAta(Negocio_ValorObjeto_Carpnosequ $carpnosequ)
    {
        $this->setDados(new RegistroPreco_Dados_CadAtaRegistroPrecoInternaManterAtasVisualizar());
        return $this->getDados()->carregarTodosDocumentosAta($carpnosequ);
    }

    public function consultarDCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi)
    {   
        $this->setDados(new RegistroPreco_Dados_CadAtaRegistroPrecoInternaManterAtasVisualizar());
        $db = Conexao();
        $sql = $this->getDados()->sqlConsultarCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi);
        
        $res = executarSQL($db, $sql);
        
        $itens = array();
        $item = null;
        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $itens[] = $item;
        }
        if (PEAR::isError($res)) {
            ExibeErroBD(__FILE__ . "\nLinha: ".__LINE__."\nSql: " . $database->getMessage());
        }
        $db->disconnect();
        return $itens;
    }

    public function procurar($carpnosequ)
    {   
        $this->setDados(new RegistroPreco_Dados_CadAtaRegistroPrecoInternaManterAtasVisualizar());
        $db = Conexao();
        $sql = $this->getDados()->sqlConsultarProcurarAta($carpnosequ);
        
        $res = executarSQL($db, $sql);
        
        $itens = array();
        $item = null;
        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $itens[] = $item;
        }
        if (PEAR::isError($res)) {
            ExibeErroBD(__FILE__ . "\nLinha: ".__LINE__."\nSql: " . $database->getMessage());
        }
        $db->disconnect();
        return $itens;
    }
}

class RegistroPreco_Adaptacao_CadAtaRegistroPrecoInternaManterAtasVisualizar extends Adaptacao_Abstrata
{
    /* Consulta ata por chaves */
    public function consultarAtaPorChave($processo, $orgao, $ano, $numeroAta)
    {
        $dados = new RegistroPreco_Dados_CadAtaRegistroPrecoInternaManterAtasVisualizar();
        $sql = $dados->sqlAtaPorchave($processo, $orgao, $ano, $numeroAta);

        return ClaDatabasePostgresql::executarSQL($sql);
    }

    /**
     * Executa a consulta dos órgãos participantes de uma ata.
     *
     * @param integer $processo da ata
     * @param integer $ano da ata
     * @param integer $orgaoGestor da ata
     *
     * @return NULL|stdClass
     */
     public static function consultarOrgaosParticipantesAta($processo, $ano, $orgaoGestor)
     {
        $db = Conexao();
        $sql = Dados_Sql_AtaRegistroPrecoNova::sqlOrgaosParticipantesAta($processo, $ano, $orgaoGestor, $seqAta);
        $res = executarSQL($db, $sql);

        $orgaos = array();
        $orgao = null;
        while ($res->fetchInto($orgao, DB_FETCHMODE_OBJECT)) {
            $orgaos[] = $orgao;
        }
        if (PEAR::isError($res)) {
            ExibeErroBD(__FILE__ . "\nLinha: ".__LINE__."\nSql: " . $database->getMessage());
        }
        $db->disconnect();


         foreach($orgaos as $orgao) {
             $strOrgaos .= $orgao->eorglidesc . "<br />";
         }
 
         return $strOrgaos;
     }

    /**
     * @param int $ano
     * @param int $processo
     * @param int $orgaoUsuario
     */
     public function consultarLicitacaoAtaInterna($ano, $processo, $orgaoUsuario)
     {
         $sql = Dados_Sql_AtaRegistroPrecoNova::sqlLicitacaoAtaInterna($ano, $processo, $orgaoUsuario);
         $resultado = ClaDatabasePostgresql::executarSQL($sql);
         ClaDatabasePostgresql::hasError($resultado);
 
         return $resultado;
     }

    /**
     * @param int $ata
     */
    public function consultarItensAtaRegitroPreco($ata)
    {
        $db = Conexao();
        $sql = Dados_Sql_ItemAtaRegistroPrecoNova::sqlFind(new Negocio_ValorObjeto_Carpnosequ($ata[0]->carpnosequ));
        $res = executarSQL($db, $sql);

        $itens = array();
        $item = null;
        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $itens[] = $item;
        }
        if (PEAR::isError($res)) {
            ExibeErroBD(__FILE__ . "\nLinha: ".__LINE__."\nSql: " . $database->getMessage());
        }
        $db->disconnect();
        return $itens;
    }

    /* Gerência a consulta do item da ata */
    public function consultarItemAta($numeroAta)
    {
        $resultados = array();
        $db = Conexao();
        $sql = $this->sqlItemAtaNova($numeroAta);
        $resultado = executarSQL($db, $sql);
        while ($resultado->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $resultados[] = $item;
        }

        return $resultados;
    }

    /* Gerência a consulta do valor máximo do documento para as chaves */
    public function consultarValorMaximoDocumento($processo, $orgao, $ano, $grupo)
    {
        $db = Conexao();
        $sql = $this->sqlCodigoMaximoDocumento($processo, $orgao, $ano, $grupo);
        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($documento, DB_FETCHMODE_OBJECT);

        return $documento;
    }
    /**
     * [carregarTodosDocumentosAta description]
     *
     * @param [type] $carpnosequ
     *            [description]
     * @return [type] [description]
     */
    public function carregarTodosDocumentosAta($carpnosequ)
    {
        $this->setNegocio(new RegistroPreco_Negocio_CadAtaRegistroPrecoInternaManterAtasVisualizar());
        return $this->getNegocio()->carregarTodosDocumentosAta(new Negocio_ValorObjeto_Carpnosequ($carpnosequ));
    }

    public function consultarCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi) {
        $this->setNegocio(new RegistroPreco_Negocio_CadAtaRegistroPrecoInternaManterAtasVisualizar());
        return $this->getNegocio()->consultarDCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi);
    }

    public function procurarAtaInterna($carpnosequ)
    {
        $this->setNegocio(new RegistroPreco_Negocio_CadAtaRegistroPrecoInternaManterAtasVisualizar());
        return $this->getNegocio()->procurar($carpnosequ);
    }
}

class RegistroPreco_UI_CadAtaRegistroPrecoInternaManterAtasVisualizar extends UI_Abstrata
{
    public function __construct()
    {
        $template = new TemplatePaginaPadrao('templates/CadAtaRegistroPrecoInternaManterAtasVisualizar.html', 'Registro de Preço > Intenção > Manter');
        $this->setTemplate($template);
    }

    /**
     * Coletar Documentos adicionado via SESSION ou via Banco de Dados
     *
     * @return void [description]
     */
    private function coletarDocumentosAdicionados($ata)
    {
        $this->setAdaptacao(new RegistroPreco_Adaptacao_CadAtaRegistroPrecoInternaManterAtasVisualizar());
        $documentosAta = $this->getAdaptacao()->carregarTodosDocumentosAta($ata->carpnosequ);
        foreach ($documentosAta as $documento) {
            $this->getTemplate()->NOME_DOCUMENTO = $documento->edocatnome;
            $this->getTemplate()->block('BLOCO_DOCUMENTO');
        }
    }

    /* MOstra o bloco botao */
    public function plotarBlocoBotao($ano, $orgao, $processo, $ata)
    {
        $this->getTemplate()->VALOR_ANO_SESSAO = $ano;
        $this->getTemplate()->VALOR_ORGAO_SESSAO = $orgao;
        $this->getTemplate()->VALOR_PROCESSO_SESSAO = $processo;
        $this->getTemplate()->VALOR_ATA_SESSAO = $ata;
        $this->getTemplate()->block('BLOCO_BOTAO');
    }

    /* Mostra na tela os dados do item da ata */
    public function plotarBlocoItemAta($itens)
    {
        if ($itens == null) {
            return;
        }

        foreach ($itens as $item) {
           
            $this->getTemplate()->VALOR_LOTE = $item->citarpnuml;
            $this->getTemplate()->VALOR_MARCA = $item->eitarpmarc;
            $this->getTemplate()->VALOR_MODELO = $item->eitarpmode;

            $this->getTemplate()->VALOR_ORDEM = $item->aitarporde;
            $this->getTemplate()->VALOR_TIPO = ($item->cmatepsequ == null) ? 'CADUS' : 'CADUM';
            $this->getTemplate()->VALOR_CODIGO_REDUZIDO = ($item->cmatepsequ == null) ? $item->cservpsequ : $item->cmatepsequ;
            $this->getTemplate()->VALOR_DESCRICAO = $item->cmatepsequ == null ? $item->eservpdesc : $item->ematepdesc;
            $this->getTemplate()->VALOR_UND = 'UN';
            $this->getTemplate()->VALOR_QTD_ORIGINAL = converte_valor_licitacao($item->aitarpqtor);
            $this->getTemplate()->VALOR_ORIGINAL = converte_valor_licitacao($item->vitarpvori);
            $this->getTemplate()->VALOR_TOTAL_ORIGINAL = converte_valor_licitacao($item->vitarpvori * $item->aitarpqtor);
            $this->getTemplate()->VALOR_LOTE = $item->citarpnuml;
            $this->getTemplate()->VALOR_QTD_ATUAL = $item->aitarpqtat;
            $this->getTemplate()->VALOR_VALOR_ATUAL = converte_valor_licitacao($item->vitarpvatu);
            $this->getTemplate()->VALOR_TOTAL_ATUAL = converte_valor_licitacao($item->vitarpvatu * $item->aitarpqtat);
            $this->getTemplate()->VALOR_SITUACAO = $item->fitarpsitu == 'A' ? 'ATIVO' : 'INATIVO';
            $this->getTemplate()->block('BLOCO_RESULTADO_ATAS');
        }
    }

    /* Mostra o bloco licitaçao */
    public function plotarBlocoLicitacao($licitacao, $ata, $dataInformada = null, $vigenciaInformada = null)
    {
        $licitacao = current($licitacao);
        $ata = current($ata);



        $adaptacao = new RegistroPreco_Adaptacao_CadAtaRegistroPrecoInternaManterAtasVisualizar();
        
        $this->getTemplate()->VALOR_COMISSAO        = $licitacao->ecomlidesc;
        $this->getTemplate()->VALOR_PROCESSO        = str_pad($licitacao->clicpoproc, 4, '0', STR_PAD_LEFT);
        $this->getTemplate()->VALOR_ANO             = $licitacao->alicpoanop;
        $this->getTemplate()->VALOR_MODALIDADE      = $licitacao->emodlidesc;
        $this->getTemplate()->VALOR_LICITACAO       = str_pad($licitacao->clicpocodl, 4, '0', STR_PAD_LEFT);
        $this->getTemplate()->VALOR_ANO_LICITACAO   = $licitacao->alicpoanol;
        $this->getTemplate()->VALOR_ORG_LIMITE      = $licitacao->eorglidesc;
        $this->getTemplate()->VALOR_PARTICIPANTES = $adaptacao->
            consultarOrgaosParticipantesAta($licitacao->clicpoproc, $licitacao->alicpoanop, $licitacao->corglicodi);
        $this->getTemplate()->VALOR_OBJETO          = (empty($ata->earpinobje)) ? $licitacao->xlicpoobje : $ata->earpinobje;
        $this->getTemplate()->VALOR_ATA             = $this->getNumeroAtaInterna($ata, $adaptacao);

        if ($dataInformada == null) {
            $timeDataInicial = strtotime($ata->tarpindini);
            $dataInicial = date('d/m/Y', $timeDataInicial);

            $this->getTemplate()->VALOR_DATA = $dataInicial;
        } else {
            $this->getTemplate()->VALOR_DATA = $dataInformada;
        }

        if ($vigenciaInformada == null) {
            $this->getTemplate()->VALOR_VIGENCIA = $ata->aarpinpzvg == null ? '' : $ata->aarpinpzvg.' MESES';
        } else {
            $this->getTemplate()->VALOR_VIGENCIA = $vigenciaInformada;
        }

        //$this->getTemplate()->VALOR_DOCUMENTO = $ata->xlicpoobje;
        $this->getTemplate()->VALOR_FORNECEDOR = $ata->nforcrrazs;

        $this->coletarDocumentosAdicionados($ata);
        $this->getTemplate()->block('BLOCO_LICITACAO');
        $this->getTemplate()->block('BLOCO_RESULTADO_PEQUISA');
    }

    private function getNumeroAtaInterna($ata, $adaptacao)
    {
        $dto = $adaptacao->consultarCentroDeCustoUsuario($ata->cgrempcodi, $ata->cusupocodi, $ata->corglicodi);
        $objeto = current($dto);
        $ataInterna = current($adaptacao->procurarAtaInterna((int)$ata->carpnosequ));

        $numeroAtaFormatado = $objeto->ccenpocorg . str_pad($objeto->ccenpounid, 2, '0', STR_PAD_LEFT);
        $numeroAtaFormatado .= "." . str_pad($ataInterna->carpincodn, 4, "0", STR_PAD_LEFT) . "/" . $ata->aarpinanon;

        return $numeroAtaFormatado;
    }

    /* Método para capturar valores iniciais da tela e mostrar templates */
    public function montarTela()
    {
        $ano = $this->variables['post']['ano'];
        $orgao = $this->variables['post']['orgao'];
        $processo = $this->variables['post']['processo'];
        $ata = $this->variables['post']['ata'];

        $this->plotarBlocoBotao($ano, $orgao, $processo, $ata);

        $atas = $this->consultarAtaPorChave($ano, $processo, $orgao, $ata);
        $licitacao = $this->consultarLicitacaoAtaInterna($ano, $processo, $orgao);

        $dada = $_REQUEST['data'];
        $vigencia = $_REQUEST['vigencia'];

        $this->plotarBlocoLicitacao($licitacao, $atas, $dada, $vigencia);
    }

    /* Monta valores para inserção de domcumentos */
    public function montaValoresInsercaoDocumento($processo, $orgao, $ano, $grupo, $comissao)
    {
        $timestamp = date('U');
        $swatch = date('B');

        $now = $timestamp.$swatch;

        $docCodMAx = 1;
        $docNome = 'Documento.txt';
        $valores = $processo.','.$ano.','.$grupo.','.$comissao.','.$orgao.','.$docCodMAx.','.$docNome.','.$timestamp.','.$_SESSION['_cusupocodi_'].','.$now;
    }
}

class CadAtaRegistroPrecoInternaManterAtasVisualizar
{
    /**
     * @var UI_Interface
     */
    private $gui;

    /**
     * @param UI_Interface $gui
     */
    private function setUI(UI_Interface $gui)
    {
        $this->gui = $gui;
    }

    /**
     * @return UI_Interface
     */
    private function getUI()
    {
        return $this->gui;
    }

    /**
     * [proccessPrincipal description].
     *
     * @param [type] $variablesGlobals
     *                                 [description]
     *
     * @return [type] [description]
     */
    private function proccessPrincipal()
    {
        $orgao = filter_var($_GET['orgao'], FILTER_SANITIZE_NUMBER_INT);
        $ano = filter_var($_GET['ano'], FILTER_SANITIZE_NUMBER_INT);
        $processo = filter_var($_GET['processo'], FILTER_SANITIZE_NUMBER_INT);
        $ata = filter_var($_GET['ata'], FILTER_SANITIZE_NUMBER_INT);

        $codProcesso = explode('-', $processo);

        $this->getUI()->plotarBlocoBotao($ano, $orgao, $codProcesso[0], $ata);

        $adaptacao = new RegistroPreco_Adaptacao_CadAtaRegistroPrecoInternaManterAtasVisualizar();

        $atas = $adaptacao->consultarAtaPorChave($ano, $codProcesso[0], $orgao, $ata);
        $licitacao = $adaptacao->consultarLicitacaoAtaInterna($ano, $codProcesso[0], $orgao);

        $this->getUI()->plotarBlocoLicitacao($licitacao, $atas, null, null);

        $itens = $adaptacao->consultarItensAtaRegitroPreco($atas);
        $this->getUI()->plotarBlocoItemAta($itens);
    }

    /* Método para inserir documento */
    private function insereDocumento()
    {
        $file = $_REQUEST['fileArquivo'];
        $this->files = $_SESSION['files'];

        if ($this->files == null) {
            $this->files = array();
        }
        array_push($this->files, $file);
        $_SESSION['files'] = $this->files;

        $this->montarTela();
    }

    private function removeDocumento()
    {
        $this->files = $_SESSION['files'];
        array_pop($this->files);

        $_SESSION['files'] = $this->files;
    }

    /* Redireciona para a tela anterior */
    private function processVoltar()
    {
        $ano = filter_var($_SESSION['ano'], FILTER_SANITIZE_NUMBER_INT);
        $processo = filter_var($_SESSION['processo'], FILTER_SANITIZE_NUMBER_INT);
        $orgao = filter_var($_SESSION['orgao'], FILTER_SANITIZE_NUMBER_INT);

        $uri = 'CadAtaRegistroPrecoInternaManterAtas.php?ano='.$ano.'&processo='.$processo.'&orgao='.$orgao;
        header('location: '.$uri);
    }

    /**
     * [frontController description].
     *
     * @return [type] [description]
     */
    private function frontController()
    {
        $botao = isset($_POST['Botao']) ? $_POST['Botao'] : 'Principal';

        switch ($botao) {
            case 'Voltar':
                $this->processVoltar();
                break;
            case 'VisualizarConsolidacao':
                $this->processVisualizarConsolidacao();
                break;
            case 'Remover':
                $this->removeDocumento();
                break;
            case 'Inserir':
                $this->insereDocumento();
                break;
            case 'Principal':
            default:
                $this->proccessPrincipal();
        }
    }

    /**
     */
    public function __construct()
    {
        $this->setUI(new RegistroPreco_UI_CadAtaRegistroPrecoInternaManterAtasVisualizar());
        /*
         * Front Controller for action
         */
        $this->frontController();
    }

    /**
     * Running the application.
     */
    public function run()
    {
        /*
         * Rendering the application
         */
        return $this->getUI()
            ->getTemplate()
            ->show();
    }
}

$app = new CadAtaRegistroPrecoInternaManterAtasVisualizar();
echo $app->run();

unset($app);
