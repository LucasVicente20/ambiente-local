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
 * @category  Pitang Registro Preço
 * @package   registropreco
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 * @version   GIT: v1.9.0-101-gd211908
 */

 // 220038--
 
if (! @require_once dirname(__FILE__) . "/../bootstrap.php") {
    throw new Exception("Error Processing Request - Bootstrap", 1);
}

/**
 *
 * @author jfsi
 *
 */
class RegistroPreco_Dados_CadAtaRegistroPrecoInternaTrocarFornecedorAtas extends Dados_Abstrata
{

    /* Executa a licitação na qual a ata interna se refere */
    public function consultarLicitacaoAtaInterna($ano, $processo, $orgaoUsuario)
    {
        $db = Conexao();
        $sql = Dados_Sql_AtaRegistroPrecoNova::sqlLicitacaoAtaInterna($ano, $processo, $orgaoUsuario);
        $sql = sprintf($sql, $carpnosequ);
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

    /* Consulta as atas da licitação */
    public function consultarLicitacaoAtas($processo, $orgao, $ano)
    {
        // $sql = Dados_Sql_AtaRegistroPrecoNova::sqlAtaLicitacao($processo, $orgao, $ano);
        $sql = Dados_Sql_AtaRegistroPrecoNova::sqlAtaLicitacaoList($processo, $orgao, $ano);
        return ClaDatabasePostgresql::executarSQL($sql);
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

/**
 *
 * @author jfsi
 *
 */
class RegistroPreco_Negocio_CadAtaRegistroPrecoInternaTrocarFornecedorAtas extends Negocio_Abstrata
{

    /**
     *
     * {@inheritdoc}
     *
     * @see Negocio_Abstrata::getDados()
     */
    public function getDados()
    {
        $this->setDados(new RegistroPreco_Dados_CadAtaRegistroPrecoInternaTrocarFornecedorAtas());
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

    public function procurar($carpnosequ)
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

/**
 *
 * @author jfsi
 *
 */
class RegistroPreco_Adaptacao_CadAtaRegistroPrecoInternaTrocarFornecedorAtas extends Adaptacao_Abstrata
{

    /**
     *
     * {@inheritdoc}
     *
     * @see Adaptacao_Abstrata::getNegocio()
     */
    public function getNegocio()
    {
        $this->setNegocio(new RegistroPreco_Negocio_CadAtaRegistroPrecoInternaTrocarFornecedorAtas());
        return parent::getNegocio();
    }

    public function listarTodosDocumentos($carpnosequ)
    {
        return $this->getNegocio()->consultarTodosDocumentosAta($carpnosequ);
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
}

class RegistroPreco_UI_CadAtaRegistroPrecoInternaTrocarFornecedorAtas extends UI_Abstrata
{

    /**
     *
     * {@inheritdoc}
     *
     * @see UI_Abstrata::getAdaptacao()
     */
    public function getAdaptacao()
    {
        $this->setAdaptacao(new RegistroPreco_Adaptacao_CadAtaRegistroPrecoInternaTrocarFornecedorAtas());
        $this->getTemplate()->TITULO_PROGRAMA = "MANTER ATA INTERNA - TROCAR FORNECEDOR ";
        return parent::getAdaptacao();
    }

    /**
     */
    public function __construct()
    {
        $template = new TemplatePaginaPadrao("templates/CadAtaRegistroPrecoInternaTrocarFornecedorAtas.html", "Registro de Preço > Ata Interna > Trocar Fornecedor");
        $this->setTemplate($template);
    }

    /**
     * [sqlSelectItemIntencao description]
     *
     * @param int $sequencialIntencao
     * @param int $anoIntencao
     * @return string
     */
    /* Início Funções de exibição de Templetes Bloco */
    public function plotarBlocoLicitacao(array $processos)
    {
        $adaptacao = new RegistroPreco_Adaptacao_CadAtaRegistroPrecoInternaTrocarFornecedorAtas();
        
        $processos = current($processos);

        $this->getTemplate()->VALOR_COMISSAO        = $processos->ecomlidesc;
        $this->getTemplate()->VALOR_PROCESSO        = str_pad($processos->clicpoproc, 4, '0', STR_PAD_LEFT);
        $this->getTemplate()->VALOR_ANO             = $processos->alicpoanop;
        $this->getTemplate()->VALOR_MODALIDADE      = $processos->emodlidesc;
        $this->getTemplate()->VALOR_LICITACAO       = str_pad($processos->clicpocodl, 4, '0', STR_PAD_LEFT);
        $this->getTemplate()->VALOR_ANO_LICITACAO   = $processos->alicpoanol;
        $this->getTemplate()->VALOR_ORG_LIMITE      = $processos->eorglidesc;
        $this->getTemplate()->VALOR_PARTICIPANTES   = $adaptacao->consultarOrgaosParticipantesAta($processos->clicpoproc, $processos->alicpoanop, $processos->corglicodi);
        $this->getTemplate()->VALOR_OBJETO          = $processos->xlicpoobje;

        $this->getTemplate()->block("BLOCO_LICITACAO");
        $this->getTemplate()->block("BLOCO_RESULTADO_PEQUISA");
    }

    /* Exibe na tela as */
    public function plotarBlocoAta(array $atas)
    {
        foreach ($atas as $ata) {
            $this->getTemplate()->VALOR_ATA         = $this->getNumeroAtaInterna($ata);
            $this->getTemplate()->VALOR_VIGENCIA    = $ata->aarpinpzvg == null ? "" : $ata->aarpinpzvg . " MESES";
            $this->getTemplate()->VALOR_ATA_REAL    = $ata->carpnosequ;
            
            $documentosAtas = $this->getAdaptacao()->listarTodosDocumentos($ata->carpnosequ);
            
            foreach ($documentosAtas as $key => $documento) {
                $_SESSION['documento'.$ata->carpnosequ.'arquivo'.$key] = $documento->idocatarqu;
                $this->getTemplate()->VALOR_DOCUMENTO_KEY = 'documento'.$ata->carpnosequ.'arquivo'.$key;

                $documentoDecodificado = base64_decode($documento->arquivo);

                $this->getTemplate()->HEX_DOCUMENTO  = $documentoDecodificado;

                $this->getTemplate()->VALOR_DOCUMENTO = $documento->edocatnome;

                $this->getTemplate()->block("BLOCO_DOCUMENTOS");
            }

            $this->getTemplate()->VALOR_DATA        = date("d/m/Y", strtotime($ata->tarpinulat));
            $this->getTemplate()->VALOR_SITUACAO    = $ata->farpinsitu == "A" ? "ATIVA" : "INATIVA";

            $this->getTemplate()->block("BLOCO_RESULTADO_ATAS");
        }
    }

    private function getNumeroAtaInterna($ata)
    {
        $dto = $this->getAdaptacao()->getNegocio()->consultarDCentroDeCustoUsuario($ata->cgrempcodi, $ata->cusupocodi, $ata->corglicodi);
        $objeto = current($dto);
        $ataInterna = current($this->getAdaptacao()->getNegocio()->procurar((int)$ata->carpnosequ));

        $numeroAtaFormatado = $objeto->ccenpocorg . str_pad($objeto->ccenpounid, 2, '0', STR_PAD_LEFT);
        $numeroAtaFormatado .= "." . str_pad($ataInterna->carpincodn, 4, "0", STR_PAD_LEFT) . "/" . $ata->aarpinanon;

        return $numeroAtaFormatado;
    }

    /* Método chamado ao voltar para tela */
    public function reiniciarTela()
    {
        $orgao = $_SESSION["orgao"];
        $ano = $_SESSION["ano"];
        $processo = $_SESSION["processo"];

        $licitacao = $this->consultarLicitacaoAtaInterna($ano, $processo, $orgao);
        $this->plotarBlocoLicitacao($licitacao);

        $atasLicitacao = $this->consultarLicitacaoAtas($processo, $orgao, $ano);
        $this->plotarBlocoAta($atasLicitacao);
    }
}

class CadAtaRegistroPrecoInternaTrocarFornecedorAtas extends ProgramaAbstrato
{

    /**
     */
    private function proccessPrincipal()
    {
        if (!empty($_SESSION['mensagemFeedback'])) {
            $this->getUI()->blockErro($_SESSION['mensagemFeedback']);
        }
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $this->getUI()->garbageCollection();
        }
        $orgao = isset($_GET['orgao']) ? filter_var($_GET['orgao'], FILTER_SANITIZE_NUMBER_INT) : $_SESSION['orgao'];
        $ano = isset($_GET['ano']) ? filter_var($_GET['ano'], FILTER_SANITIZE_NUMBER_INT) : $_SESSION['ano'];
        $processo = isset($_GET['processo']) ? filter_var($_GET['processo'], FILTER_SANITIZE_NUMBER_INT) : $_SESSION['processo'];

        $_SESSION["orgao"] = $orgao;
        $_SESSION["ano"] = $ano;
        $_SESSION["processo"] = $processo;

        $codProcesso = explode('-', $processo);

        $licitacao = $this->getUI()
            ->getAdaptacao()
            ->getNegocio()
            ->getDados()
            ->consultarLicitacaoAtaInterna($ano, $codProcesso[0], $orgao);


        $this->getUI()->plotarBlocoLicitacao($licitacao);

        $atasLicitacao = $this->getUI()
            ->getAdaptacao()
            ->getNegocio()
            ->getDados()
            ->consultarLicitacaoAtas($processo, $orgao, $ano);

        $this->getUI()->plotarBlocoAta($atasLicitacao);
    }

    /* Inicio Funções básicas */
    private function processVoltar()
    {
        $uri = 'CadAtaRegistroPrecoInternaTrocarFornecedor.php';
        header('location: ' . $uri);
    }

    private function processoAtualizar($ano, $processo, $orgao)
    {
        $uri = 'CadAtaRegistroPrecoInternaTrocarFornecedorAtas.php?ano=' . $ano . '&processo=' . $processo . '&orgao=' . $orgao;
        header('location: ' . $uri);
    }

    /* Redireciona para a página de alteração */
    private function processTrocarFornecedor()
    {
        $orgao = $_SESSION["orgao"];
        $ano = $_SESSION["ano"];
        $processo = $_SESSION["processo"];
        $ata = filter_var($_REQUEST['ata_real'], FILTER_SANITIZE_NUMBER_INT);
        $_SESSION['ata'] = $_REQUEST['ata'];

        if (empty($_REQUEST['ata']) || is_null($_REQUEST['ata'])) {
            $this->getUI()->setMensagemFeedBack("Não é possível trocar o fornecedor", 1, 0);
            $this->processoAtualizar($ano, $processo, $orgao);
            return false;
        }

        $uri = 'CadAtaRegistroPrecoInternaTrocarFornecedorAtasAlterar.php?ano=' . $ano . '&processo=' . $processo . '&orgao=' . $orgao . '&ata=' . $ata;
        header('location: ' . $uri);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see ProgramaAbstrato::configuracao()
     */
    protected function configuracao()
    {
        $this->setUI(new RegistroPreco_UI_CadAtaRegistroPrecoInternaTrocarFornecedorAtas());

        $this->getUI()->getTemplate()->ACAO_ALTERAR = 'TrocarFornecedor';
        $this->getUI()->getTemplate()->VALUE_ALTERAR = 'Trocar Fornecedor';
        $this->getUI()->getTemplate()->ENVIAR_ALTERAR = 'TrocarFornecedor';
        $this->getUI()
            ->getTemplate()
            ->block('BLOCO_BOTAO_ATAS_ALTERAR');

        $this->getUI()->getTemplate()->NOME_PROGRAMA = "CadAtaRegistroPrecoInternaTrocarFornecedorAtas";
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see ProgramaAbstrato::frontController()
     */
    public function frontController()
    {
        $botao = isset($_REQUEST['Botao']) ? filter_var($_POST['Botao'], FILTER_SANITIZE_STRING) : 'Principal';
        switch ($botao) {
            case 'Voltar':
                $this->processVoltar();
                break;
            case 'TrocarFornecedor':
                $this->processTrocarFornecedor();
                break;
            case 'Principal':
            default:
                $this->proccessPrincipal();
        }
    }
}

ProgramaAbstrato::iniciar(new CadAtaRegistroPrecoInternaTrocarFornecedorAtas());
