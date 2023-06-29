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
 * @version   Git: v1.8.0-101-g07e25e1
 */

#-----------------------------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# Data: 17/09/2018
# Objetivo: Tarefa Redmine 203513
#-----------------------------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# Data: 14/11/2018
# Objetivo: Tarefa Redmine 205798
#-----------------------------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# Data:     05/12/2018
# Objetivo: Tarefa Redmine 207316
# ----------------------------------------------
# Alterado: Lucas André
# Data:     26/06/2023
# Objetivo: Tarefa Redmine 285227
# ----------------------------------------------

// 220038--

if (! @require_once dirname(__FILE__) . "/../bootstrap.php") {
    throw new Exception("Error Processing Request - Bootstrap", 1);
}

/**
 * A camada de Dados conterá o código que irá realizar todo o acesso aos dados.
 *
 * @author jfsi
 *
 */
class RegistroPreco_Dados_CadAtaRegistroPrecoCaronaAtaExternaListar extends Dados_Abstrata
{

    /**
     * [sqlAtaLicitacao description]
     *
     * @param integer $processo
     *            [description]
     * @param integer $orgao
     *            [description]
     * @param integer $ano
     *            [description]
     * @return string [description]
     */
    private function sqlAtaLicitacao($processo, $orgao, $ano)
    {
        $sql = "
        SELECT
            a.farpinsitu,
            a.aarpinpzvg,
            a.tarpinulat,
            a.carpnosequ,
            a.alicpoanop,
            a.corglicodi,
            s.csolcosequ,
            d.edoclinome
        FROM
            sfpc.tbataregistroprecointerna a
        LEFT OUTER JOIN
            sfpc.tbsolicitacaolicitacaoportal s
            ON (
                s.clicpoproc = a.clicpoproc
                AND s.alicpoanop = a.alicpoanop
                AND s.ccomlicodi = a.ccomlicodi
                AND s.corglicodi = a.corglicodi
            )
        LEFT OUTER JOIN
            sfpc.tbdocumentolicitacao d
            ON (
                d.clicpoproc = a.clicpoproc
                AND d.alicpoanop = a.alicpoanop
                AND d.ccomlicodi = a.ccomlicodi
                AND d.corglicodi = a.corglicodi
            )
         WHERE
            a.clicpoproc = %d AND a.alicpoanop = %d AND a.corglicodi = %d order by a.carpnosequ
        ";

        return sprintf($sql, $processo, $ano, $orgao);
    }

    /**
     * Consulta as atas da licitação
     *
     * @param unknown $processo
     * @param unknown $orgao
     * @param unknown $ano
     * @return NULL
     */
    public function consultarLicitacaoAtas($processo, $orgao, $ano)
    {
        $sql = Dados_Sql_AtaRegistroPrecoNova::sqlAtaLicitacaoList($processo, $orgao, $ano);
        $resultado = ClaDatabasePostgresql::executarSQL($sql);
        ClaDatabasePostgresql::hasError($resultado);
        return $resultado;
    }

    /**
     * Executa a licitação na qual a ata interna se refere
     */
    public function consultarLicitacaoAtaInterna($ano, $processo, $orgaoUsuario, $comissao, $grupo)
    {
        $db = Conexao();
        $sql = Dados_Sql_AtaRegistroPrecoNova::sqlLicitacaoAtaInternaNova($ano, $processo, $orgaoUsuario, $comissao, $grupo);
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

    public function consultarAtaParticipanteChave($numeroAta)
    {	
        $db = Conexao();
        $sql = $this->sqlAtaParticipanteAta($numeroAta);
		
        $res = executarSQL($db, $sql);
        $itens = array();
        $item = null;
        $itemTipo = new stdClass();        
        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $item->tipoItem = $itemTipo->tipoItem;
            $itens[] = $item;            
        }
        return $itens;
    }

    private function sqlAtaParticipanteAta($chaveAta)
    {
        $sql  = "select DISTINCT o.eorglidesc ";
        $sql .= " from sfpc.tbparticipanteatarp pa ";
        $sql .= " inner join sfpc.tborgaolicitante o on ";
        $sql .= " o.corglicodi = pa.corglicodi  ";
        $sql .= "  where pa.carpnosequ in (" . $chaveAta . " )";

        

        return $sql;
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
 * A camada de Negócio conterá o código que irá implementar todas as regras de negócio do sistema.
 *
 * Utiliza serviços da camada de Dados.
 *
 * @author jfsi
 *
 */
class RegistroPreco_Negocio_CadAtaRegistroPrecoCaronaAtaExternaListar extends Negocio_Abstrata
{

    /**
     *
     * {@inheritDoc}
     *
     * @see Negocio_Abstrata::getDados()
     */
    public function getDados()
    {
        $this->setDados(new RegistroPreco_Dados_CadAtaRegistroPrecoCaronaAtaExternaListar());
        return parent::getDados();
    }

    public function validarAtaSelecionada()
    {
        if (strlen($_REQUEST['ata']) != 14) {
            return false;
        }

        return true;
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
 * A camada de Adaptação e Transformação conterá o código que tratará a lógica de apresentação dos resultados
 * das requisições dos usuários e a troca de dados com sistemas externos.
 *
 * Utiliza serviços da camada de Negócio.
 *
 * @author jfsi
 *
 */
class RegistroPreco_Adaptacao_CadAtaRegistroPrecoCaronaAtaExternaListar extends Adaptacao_Abstrata
{

    /**
     *
     * {@inheritDoc}
     *
     * @see Adaptacao_Abstrata::getNegocio()
     */
    public function getNegocio()
    {
        $this->setNegocio(new RegistroPreco_Negocio_CadAtaRegistroPrecoCaronaAtaExternaListar());
        return parent::getNegocio();
    }

    /**
     * Recupera o seguencial da ata a partir do número da ata selecionado
     *
     * @return [type] [description]
     */
    public function recuperarSequencialAta()
    {
        if (! $this->getNegocio()->validarAtaSelecionada()) {
            return false;
        }
        $numeroAta = $_REQUEST['ata'];

        $numeroAtaExp = explode(".", $numeroAta);
        $numeroAtaCodi = explode("/", $numeroAtaExp[1]);
        $codigo = (int) $numeroAtaCodi[0];

        return $codigo;
    }

    public function listarTodosDocumentos($carpnosequ)
    {
        return $this->getNegocio()->consultarTodosDocumentosAta($carpnosequ);
    }
}

/**
 * A camada de Interface Gráfica com o Usuário é a camada que conterá o código que irá implementar a interação
 * do sistema com os usuários (telas, relatórios e troca de dados).
 *
 * Utiliza serviços da camada de Adaptação e Transformação.
 *
 * @author jfsi
 *
 */
class RegistroPreco_UI_CadAtaRegistroPrecoCaronaAtaExternaListar extends UI_Abstrata
{

    /**
     *
     * {@inheritDoc}
     *
     * @see UI_Abstrata::getAdaptacao()
     */
    public function getAdaptacao()
    {
        $this->setAdaptacao(new RegistroPreco_Adaptacao_CadAtaRegistroPrecoCaronaAtaExternaListar());
        return parent::getAdaptacao();
    }

    /**
     * [__construct description]
     */
    public function __construct()
    {
        $this->setTemplate(new TemplatePaginaPadrao("templates/CadAtaRegistroPrecoCaronaAtaExternaListar.html", "Registro de Preço > Ata Interna > Carona Órgão Externo > Incluir"));
    }

    /**
     *
     * @param array $processos
     */
    public function plotarBlocoLicitacao(array $processos, $participantes)
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

        $this->getTemplate()->block("BLOCO_LICITACAO");
    }

    /**
     *
     * @param unknown $atas
     */
    public function plotarBlocoAta($atas)
    {
        foreach ($atas as $ata) {
            $this->getTemplate()->VALOR_ATA         = $this->getNumeroAtaInterna($ata);
            $this->getTemplate()->VALOR_VIGENCIA    = $ata->aarpinpzvg == null ? "" : $ata->aarpinpzvg . " MESES";
            $this->getTemplate()->VALOR_DATA        = date("d/m/Y", strtotime($ata->tarpinulat));
            $this->getTemplate()->VALOR_SITUACAO    = $ata->farpinsitu == "A" ? "ATIVA" : "INATIVA";
            $this->getTemplate()->VALOR_ATA_REAL    = $ata->carpnosequ;
            
            $display = '';
            if($ata->farpinsitu != "A") {
                $display = 'display: none';
            }

            $this->getTemplate()->VALOR_DISPLAY = $display;
            
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
}

class CadAtaRegistroPrecoCaronaAtaExternaListar extends ProgramaAbstrato
{

    /**
     */
    private function proccessSelecionar()
    {
        $ata = $this->getUI() ->getAdaptacao() ->recuperarSequencialAta();
        if (!$ata) {
            $this->getUI()->mensagemSistema("Ata de Registro não foi selecionada", 0, 1);
            $this->proccessPrincipal();
            return;
        }

        $orgao            = $_SESSION["orgao"];
        $ano              = $_SESSION["ano"];
        $processo         = $_SESSION["processo"];
        $ata              = filter_var($_REQUEST['ata_real'], FILTER_SANITIZE_NUMBER_INT);
        $_SESSION["NAta"] = $_POST['ata'];

        $uri = 'CadAtaRegistroPrecoCaronaAtaExternaIncluir.php?ano=' . $ano . '&processo=' . $processo . '&orgao=' . $orgao . '&ata=' . $ata;
        header('Location: ' . $uri);
        exit();
    }

    private function processVoltar()
    {
        $uri = 'CadAtaRegistroPrecoCaronaAtaExterna.php';
        header('Location: ' . $uri);
        exit();
    }

    private function proccessPrincipal()
    {

        if(isset($_SESSION['mensagemFeedback']) && !empty($_SESSION['mensagemFeedback'])){
            $this->getUI()->mensagemSistema($_SESSION['mensagemFeedback'], 1, 1);
            unset($_SESSION['mensagemFeedback']);
            $this->proccessPrincipal();
            return;
        }
        
        $_SESSION["NAta"] = null;



        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $orgao = filter_var($_GET['orgao'], FILTER_SANITIZE_NUMBER_INT);
            $ano = filter_var($_GET['ano'], FILTER_SANITIZE_NUMBER_INT);
            $processo = filter_var($_GET['processo'], FILTER_SANITIZE_NUMBER_INT);

            $_SESSION["orgao"] = $orgao;
            $_SESSION["ano"] = $ano;
            $_SESSION["processo"] = $processo;
        } else {
            $orgao = $_SESSION['orgao'];
            $ano = $_SESSION['ano'];
            $processo = $_SESSION['processo'];
        }

        $codProcesso   = explode('-', $processo);
        $licitacao     = $this->getUI()->getAdaptacao()->getNegocio()->getDados()->consultarLicitacaoAtaInterna($ano, $codProcesso[0], $orgao, $codProcesso[3], $codProcesso[2]);
        $atasLicitacao = $this->getUI()->getAdaptacao()->getNegocio()->getDados()->consultarLicitacaoAtas($processo, $orgao, $ano);
       
    
        $arrayMontado = null;
        foreach ($atasLicitacao as $key => $value) {
            $arrayMontado[] = $value->carpnosequ;
        }
            
        $stringParticipante = '';
        if($arrayMontado != null){
            $chaveAta = implode(',',$arrayMontado);
            $ataParticipante = $this->getUI()->getAdaptacao()->getNegocio()->getDados()->consultarAtaParticipanteChave($chaveAta);
            foreach ($ataParticipante as $key => $valueParti) {
                $stringParticipante .= $valueParti->eorglidesc .'<br />';
            }
        }

        $this->getUI()->plotarBlocoLicitacao($licitacao, $stringParticipante);
        $this->getUI()->plotarBlocoAta($atasLicitacao);
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see ProgramaAbstrato::configuracao()
     */
    protected function configuracao()
    {
        $this->setUI(new RegistroPreco_UI_CadAtaRegistroPrecoCaronaAtaExternaListar());
        $this->getUI()->getTemplate()->NOME_PROGRAMA = "CadAtaRegistroPrecoCaronaAtaExternaListar";
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see ProgramaAbstrato::frontController()
     */
    protected function frontController()
    {
        $acao = filter_input(INPUT_POST, 'Botao', FILTER_SANITIZE_STRING);

        switch ($acao) {
            case 'Voltar':
                $this->processVoltar();
                break;
            case 'Selecionar':
                $this->proccessSelecionar();
                break;
            case 'Principal':
            default:
                $this->proccessPrincipal();
                break;
        }
    }
}

ProgramaAbstrato::iniciar(new CadAtaRegistroPrecoCaronaAtaExternaListar());
if(isset($_SESSION['orgaoExterno'])) {
    unset($_SESSION['orgaoExterno']);
}

if(isset($_SESSION['Arquivos_Upload'])) {
    unset($_SESSION['Arquivos_Upload']);
}