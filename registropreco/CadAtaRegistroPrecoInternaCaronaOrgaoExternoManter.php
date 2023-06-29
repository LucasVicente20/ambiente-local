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

#-----------------------------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# Data: 17/09/2018
# Objetivo: Tarefa Redmine 203513
#-----------------------------------------------------------------

// 220038--

if (! @require_once dirname(__FILE__) . '/../bootstrap.php') {
    throw new Exception('Error Processing Request - Bootstrap', 1);
}

Seguranca();

class RegistroPreco_Dados_CadAtaRegistroPrecoInternaCaronaOrgaoExternoManter extends Dados_Abstrata
{

    /**
     *
     * @param Negocio_ValorObjeto_Clicpoproc $clicpoproc
     * @param Negocio_ValorObjeto_Alicpoanop $alicpoanop
     */
    public function consultarParticipantesProcesso(Negocio_ValorObjeto_Clicpoproc $clicpoproc, Negocio_ValorObjeto_Alicpoanop $alicpoanop)
    {
        $sql = '
            SELECT
                ol.eorglidesc
            FROM
                sfpc.tbsolicitacaolicitacaoportal sc
            INNER JOIN
                sfpc.tborgaolicitante ol ON ol.corglicodi = sc.corglicodi
            WHERE
                sc.clicpoproc = %d
                AND sc.alicpoanop = %d
        ';

        $resultado = ClaDatabasePostgresql::executarSQL(sprintf($sql, $clicpoproc->getValor(), $alicpoanop->getValor()));

        ClaDatabasePostgresql::hasError($resultado);

        return $resultado;
    }

    /**
     * Consultar os dados da licitação
     *
     * @param Negocio_ValorObjeto_Clicpoproc $clicpoproc
     *            [description]
     * @param Negocio_ValorObjeto_Alicpoanop $alicpoanop
     *            [description]
     * @param Negocio_ValorObjeto_Corglicodi $corglicodi
     *            [description]
     * @return [type] [description]
     */
    public function consultarLicitacaoAtas($processo, Negocio_ValorObjeto_Alicpoanop $alicpoanop, Negocio_ValorObjeto_Corglicodi $corglicodi)
    {
        // $db = Conexao();
        // $sql = Dados_Sql_AtaRegistroPrecoInterna::procurarPorProcessoLicitatorioOrgao($clicpoproc, $alicpoanop, $corglicodi);
        // $res = executarSQL($db, $sql);

        // $licitacoes = array();
        // $licitacao = null;
        // while ($res->fetchInto($licitacao, DB_FETCHMODE_OBJECT)) {
        //     $licitacoes[] = $licitacao;
        // }
        // if (PEAR::isError($res)) {
        //     ExibeErroBD(__FILE__ . "\nLinha: ".__LINE__."\nSql: " . $database->getMessage());
        // }
        // $db->disconnect();
 
        //  return $licitacoes;


        $sql = Dados_Sql_AtaRegistroPrecoNova::sqlAtaLicitacaoList($processo, $orgao, $ano);
        $resultado = ClaDatabasePostgresql::executarSQL($sql);
        ClaDatabasePostgresql::hasError($resultado);
        return $resultado;
    }

    public function consultarTodosDocumentosAta($carpnosequ)
    {
        $db = Conexao();
        $sql = "
            SELECT encode(idocatarqu, 'base64') as arquivo, carpnosequ, cdocatsequ, edocatnome, cusupocodi
              FROM sfpc.tbdocumentoatarp
             WHERE carpnosequ = %d
        ";

        $sql = sprintf($sql, $carpnosequ);
        $res = executarSQL($db, $sql);
        
        $documentos = array();
        $documento = null;
        while ($res->fetchInto($documento, DB_FETCHMODE_OBJECT)) {
            $documentos[] = $documento;
        }
        if (PEAR::isError($res)) {
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

class RegistroPreco_Negocio_CadAtaRegistroPrecoInternaCaronaOrgaoExternoManter extends Negocio_Abstrata
{

    /**
     *
     * {@inheritdoc}
     *
     * @see Negocio_Abstrata::getDados()
     */
    public function getDados()
    {
        $this->setDados(new RegistroPreco_Dados_CadAtaRegistroPrecoInternaCaronaOrgaoExternoManter());

        return parent::getDados();
    }

    public function consultarTodosDocumentosAta($carpnosequ)
    {
        return $this->getDados()->consultarTodosDocumentosAta($carpnosequ);
    }

    public function consultarDCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi)
    {   
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

    public function procurarAtaInterna($carpnosequ)
    {   
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

class RegistroPreco_Adaptacao_CadAtaRegistroPrecoInternaCaronaOrgaoExternoManter extends Adaptacao_Abstrata
{

    /**
     *
     * {@inheritdoc}
     *
     * @see Adaptacao_Abstrata::getNegocio()
     */
    public function getNegocio()
    {
        $this->setNegocio(new RegistroPreco_Negocio_CadAtaRegistroPrecoInternaCaronaOrgaoExternoManter());

        return parent::getNegocio();
    }

    /**
     *
     * @param int $ano
     * @param int $processo
     * @param int $orgao
     */
    public function consultarLicitacaoAtaInterna($ano, $processo, $orgao, $comissao, $grupo)
    {
        $db = Conexao();
        $sql = Dados_Sql_AtaRegistroPrecoNova::sqlLicitacaoAtaInternaNova($ano, $processo, $orgao, $comissao, $grupo);
        $sql = sprintf($sql);

        $res = executarSQL($db, $sql);
        
        $licitacoes = array();
        $licitacao = null;
        while ($res->fetchInto($licitacao, DB_FETCHMODE_OBJECT)) {
            $licitacoes[] = $licitacao;
        }
        if (PEAR::isError($res)) {
            ExibeErroBD(__FILE__ . "\nLinha: ".__LINE__."\nSql: " . $database->getMessage());
        }
        $db->disconnect();

        return $licitacoes;
    }

    /**
     *
     * @param int $processo
     * @param int $ano
     */
    public function consultarParticipantesProcesso($processo, $ano, $orgaoGestor)
    {
        $db = Conexao();
        $sql = Dados_Sql_AtaRegistroPrecoNova::sqlOrgaosParticipantesAta($processo, $ano, $orgaoGestor);
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

    public function consultarLicitacaoAtas($processo, $orgao, $ano)
    {
        return $this->getNegocio()
            ->getDados()
            ->consultarLicitacaoAtas($processo, new Negocio_ValorObjeto_Alicpoanop($ano), new Negocio_ValorObjeto_Corglicodi($orgao));
    }

    public function listarTodosDocumentos($carpnosequ)
    {
        return $this->getNegocio()->consultarTodosDocumentosAta($carpnosequ);
    }
}

class RegistroPreco_UI_CadAtaRegistroPrecoInternaCaronaOrgaoExternoManter extends UI_Abstrata
{

    /**
     *
     * {@inheritdoc}
     *
     * @see UI_Abstrata::getAdaptacao()
     */
    public function getAdaptacao()
    {
        $this->setAdaptacao(new RegistroPreco_Adaptacao_CadAtaRegistroPrecoInternaCaronaOrgaoExternoManter());

        return parent::getAdaptacao();
    }

    /**
     */
    public function __construct()
    {
        $template = new TemplatePaginaPadrao('templates/CadAtaRegistroPrecoInternaTrocarFornecedorAtas.html', 'Registro de Preço > Ata Interna >  Carona Órgão Externo > Manter');
        $this->setTemplate($template);

        $this->getTemplate()->NOME_PROGRAMA = 'CadAtaRegistroPrecoInternaCaronaOrgaoExternoManter';

        $this->getTemplate()->ACAO_VISUALIZAR = 'selecionar';
        $this->getTemplate()->VALUE_VISUALIZAR = 'Selecionar';
        $this->getTemplate()->ENVIAR_VISUALIZAR = 'Selecionar';
        $this->getTemplate()->TITULO_PROGRAMA = 'MANTER - CARONA ÓRGÃO EXTERNO';

        $this->getTemplate()->BLOCK('BLOCO_BOTAO_ATAS_VISUALIZAR');
    }

    /**
     * [sqlSelectItemIntencao description].
     *
     * @param int $sequencialIntencao
     * @param int $anoIntencao
     *
     * @return string
     */
    /* Início Funções de exibição de Templetes Bloco */
    public function plotarBlocoLicitacao($processos, $participantes)
    {
        $processos = current($processos);

        $this->getTemplate()->VALOR_COMISSAO = $processos->ecomlidesc;
        $this->getTemplate()->VALOR_PROCESSO = str_pad($processos->clicpoproc, 4, '0', STR_PAD_LEFT);
        $this->getTemplate()->VALOR_ANO = $processos->alicpoanop;
        $this->getTemplate()->VALOR_MODALIDADE = $processos->emodlidesc;
        $this->getTemplate()->VALOR_LICITACAO = str_pad($processos->clicpocodl, 4, '0', STR_PAD_LEFT);
        $this->getTemplate()->VALOR_ANO_LICITACAO = $processos->alicpoanol;
        $this->getTemplate()->VALOR_ORG_LIMITE = $processos->eorglidesc;
        $this->getTemplate()->VALOR_PARTICIPANTES = $participantes;
        $this->getTemplate()->VALOR_OBJETO = $processos->xlicpoobje;

        $this->getTemplate()->block('BLOCO_LICITACAO');
        $this->getTemplate()->block('BLOCO_RESULTADO_PEQUISA');
    }

    /* Exibe na tela as */
    public function plotarBlocoAta($atas)
    {
        if ($atas == null) {
            return;
        }
        foreach ($atas as $ata) {
            $this->getTemplate()->VALOR_ATA = $this->getNumeroAtaInterna($ata);
            $this->getTemplate()->VALOR_ATA_REAL = $ata->carpnosequ;
            $this->getTemplate()->VALOR_VIGENCIA = $ata->aarpinpzvg == null ? '' : $ata->aarpinpzvg . ' MESES';

            $documentosAtas = $this->getAdaptacao()->listarTodosDocumentos($ata->carpnosequ);

            foreach ($documentosAtas as $key => $documento) {
                $_SESSION['documento'.$ata->carpnosequ.'arquivo'.$key] = $documento->idocatarqu;
                $this->getTemplate()->VALOR_DOCUMENTO_KEY = 'documento'.$ata->carpnosequ.'arquivo'.$key;
                $this->getTemplate()->HEX_DOCUMENTO       = base64_decode($documento->arquivo);

                $this->getTemplate()->VALOR_DOCUMENTO = $documento->edocatnome;

                $this->getTemplate()->block("BLOCO_DOCUMENTOS");
            }

            $data = explode(' ', $ata->tarpinulat);
            $date = new DataHora($data[0]);
            $this->getTemplate()->VALOR_DATA = $date->format('d/m/Y');
            $this->getTemplate()->VALOR_SITUACAO = $ata->farpinsitu == 'A' ? 'ATIVA' : 'INATIVA';

            $this->getTemplate()->block('BLOCO_RESULTADO_ATAS');
        }
    }

    private function getNumeroAtaInterna($ata)
    {
        
        $dto                = $this->getAdaptacao()->getNegocio()->consultarDCentroDeCustoUsuario($ata->cgrempcodi, $ata->cusupocodi, $ata->corglicodi);
        $objeto             = current($dto);
        $ataInterna         = current($this->getAdaptacao()->getNegocio()->procurarAtaInterna((int)$ata->carpnosequ));
        $numeroAtaFormatado = $objeto->ccenpocorg . str_pad($objeto->ccenpounid, 2, '0', STR_PAD_LEFT);
        $numeroAtaFormatado .= "." . str_pad($ataInterna->carpincodn, 4, "0", STR_PAD_LEFT) . "/" . $ata->aarpinanon;

        return $numeroAtaFormatado;
    }
}

class CadAtaRegistroPrecoInternaCaronaOrgaoExternoManter extends ProgramaAbstrato
{

    /**
     */
    private function proccessPrincipal()
    {
        $orgao         = filter_var($_SESSION['orgao'], FILTER_SANITIZE_NUMBER_INT);
        $ano           = filter_var($_SESSION['ano'], FILTER_SANITIZE_NUMBER_INT);
        $processo      = filter_var($_SESSION['processo'], FILTER_SANITIZE_NUMBER_INT);
        $codProcesso   = explode('-', $processo);  
        $licitacao     = $this->getUI()->getAdaptacao()->consultarLicitacaoAtaInterna($ano, $codProcesso[0], $orgao, $codProcesso[3], $codProcesso[2]);
        $participantes = $this->getUI()->getAdaptacao()->consultarParticipantesProcesso($codProcesso[0], $ano, $orgao);
        $atasLicitacao = $this->getUI()->getAdaptacao()->consultarLicitacaoAtas($processo, $orgao, $ano);

        $this->getUI()->plotarBlocoLicitacao($licitacao, $participantes);
        $this->getUI()->plotarBlocoAta($atasLicitacao);
    }

    /* Método chamado ao voltar para tela */
    private function reiniciarTela()
    {
        $orgao         = $_SESSION['orgao'];
        $ano           = $_SESSION['ano'];
        $processo      = $_SESSION['processo'];        
        $licitacao     = $this->consultarLicitaçãoAtaInterna($ano, $processo, $orgao);
        $atasLicitacao = $this->consultarLicitacaoAtas($processo, $orgao, $ano);

        $this->plotarBlocoLicitacao($licitacao);
        $this->plotarBlocoAta($atasLicitacao);
    }

    /* Fim Funções de exibição de Templetes Bloco */

    /* início funcoes de Apoio */
    private function carregarAno()
    {
        $anoAtual = (int) date('Y');
        $anos = array();
        for ($i = 0; $i < 3; ++ $i) {
            array_push($anos, strval($anoAtual - $i));
        }

        return $anos;
    }

    /* Fim Funções gerenciadoras/Executoras de query */

    /* Inicio Funções básicas */
    private function processVoltar()
    {
        unset($_SESSION['processo'], $_SESSION['ano'], $_SESSION['orgao']);
        $uri = 'CadAtaRegistroPrecoInternaSelecionarProcessoOrgaoExternoManter.php';
        header('Location: ' . $uri);
    }

    /* Redireciona para a página de alteração */
    private function processSelecionar()
    {
        $_SESSION['ata'] = filter_var($_REQUEST['ata_real'], FILTER_SANITIZE_STRING);
        $_SESSION['nAtaFormatado'] = $_REQUEST['ata'];

        $uri = 'CadCaronaOrgaoExternoManterSelecionar.php';
        header('Location: ' . $uri);
        exit();
    }

    public function configuracao()
    {
        $this->setUI(new RegistroPreco_UI_CadAtaRegistroPrecoInternaCaronaOrgaoExternoManter());
    }

    /**
     */
    public function frontController()
    {
        $botao = isset($_POST['Botao']) ? filter_var($_POST['Botao'], FILTER_SANITIZE_STRING) : 'Principal';

        switch ($botao) {
            case 'Voltar':
                $this->processVoltar();
                break;

            case 'Selecionar':
                $this->processSelecionar();
                break;

            case 'Principal':

            default:
                $this->proccessPrincipal();
        }
    }
}

ProgramaAbstrato::iniciar(new CadAtaRegistroPrecoInternaCaronaOrgaoExternoManter());
