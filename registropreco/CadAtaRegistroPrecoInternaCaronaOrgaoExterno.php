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
 * @version   Git: $Id:$
 */

 // 220038--
 
if (! @require_once dirname(__FILE__) . "/../bootstrap.php") {
    throw new Exception("Error Processing Request - Bootstrap", 1);
}

class CadAtaRegistroPrecoInternaCaronaOrgaoExterno
{
    /**
     * [$template description]
     *
     * @var \TemplatePaginaPadrao
     */
    private $template;

    /**
     * [$variables description]
     *
     * @var \ArrayObject
     */
    private $variables;

    /**
     * [$intencao description]
     *
     * @var unknown
     */
    private $intencao;

    /**
     * Gets the value of template.
     *
     * @return mixed
     */
    private function getTemplate()
    {
        return $this->template;
    }

    /**
     * Sets the value of template.
     *
     * @param TemplatePaginaPadrao $template
     *            the template
     *
     * @return self
     */
    private function setTemplate(TemplatePaginaPadrao $template)
    {
        $this->template = $template;
        
        return $this;
    }

    /**
     * [proccessPrincipal description]
     *
     * @param [type] $variablesGlobals
     *            [description]
     * @return [type] [description]
     */
    private function proccessPrincipal()
    {
        $orgao = $this->variables['get']['orgao'];
        $ano = $this->variables['get']['ano'];
        $processo = $this->variables['get']['processo'];
        
        $_SESSION["orgao"] = $orgao;
        $_SESSION["ano"] = $ano;
        $_SESSION["processo"] = $processo;
        
        $licitacao = $this->consultarLicitacaoAtaInterna($ano, $processo, $orgao);
        
        $participantes = $this->consultarParticipantesProcesso($processo, $ano);
        
        $this->plotarBlocoLicitacao($licitacao, $participantes);
        
        $atasLicitacao = $this->consultarLicitacaoAtas($processo, $orgao, $ano);
        $this->plotarBlocoAta($atasLicitacao);
    }

    /* Método chamado ao voltar para tela */
    private function reiniciarTela()
    {
        $orgao = $_SESSION["orgao"];
        $ano = $_SESSION["ano"];
        $processo = $_SESSION["processo"];
        
        $licitacao = $this->consultarLicitacaoAtaInterna($ano, $processo, $orgao);
        $this->plotarBlocoLicitacao($licitacao);
        
        $atasLicitacao = $this->consultarLicitacaoAtas($processo, $orgao, $ano);
        $this->plotarBlocoAta($atasLicitacao);
    }

    /**
     * [sqlSelectItemIntencao description]
     *
     * @param int $sequencialIntencao
     * @param int $anoIntencao
     * @return string
     */
    /* Início Funções de exibição de Templetes Bloco */
    private function plotarBlocoLicitacao($processos, $participantes)
    {
        $textoParticipante = "<ul>";
        foreach ($participantes as $participante) {
            $textoParticipante .= "<li>" . $participante->eorglidesc . "</li>";
        }
        $textoParticipante .= "</ul>";
        
        $this->getTemplate()->VALOR_COMISSAO = $processos->ecomlidesc;
        $this->getTemplate()->VALOR_PROCESSO = ClaHelper::formatarComZeros(4, $processos->clicpoproc);
        $this->getTemplate()->VALOR_ANO = $processos->alicpoanop;
        $this->getTemplate()->VALOR_MODALIDADE = $processos->emodlidesc;
        $this->getTemplate()->VALOR_LICITACAO = ClaHelper::formatarComZeros(4, $processos->clicpoproc) . "*";
        $this->getTemplate()->VALOR_ANO_LICITACAO = $processos->alicpoanop . "*";
        $this->getTemplate()->VALOR_ORG_LIMITE = $processos->eorglidesc;
        $this->getTemplate()->VALOR_PARTICIPANTES = $textoParticipante;
        $this->getTemplate()->VALOR_OBJETO = $processos->xlicpoobje;
        
        $this->getTemplate()->block("BLOCO_LICITACAO");
        $this->getTemplate()->block("BLOCO_RESULTADO_PEQUISA");
    }

    /* Exibe na tela as */
    private function plotarBlocoAta($atas)
    {
        if ($atas == null) {
            return;
        }
        $dbase = & Conexao();
        
        foreach ($atas as $ata) {
            $numeroAta = getNumeroSolicitacaoCompra($dbase, $ata->csolcosequ);
            $valoresExploded = explode(".", $numeroAta);
            $valoUnidadeOrcamentaria = substr($valoresExploded[0], 2, 2);
            
            $this->getTemplate()->VALOR_ATA = str_pad($ata->corglicodi, 2, "0", STR_PAD_RIGHT) . $valoUnidadeOrcamentaria . "." . str_pad($ata->carpnosequ, 4, "0", STR_PAD_RIGHT) . '/' . $ata->alicpoanop;
            $this->getTemplate()->VALOR_ATA_REAL = $ata->carpnosequ;
            $this->getTemplate()->VALOR_VIGENCIA = $ata->aarpinpzvg == null ? "" : $ata->aarpinpzvg . " MESES";
            $this->getTemplate()->VALOR_DOCUMENTO = $ata->edoclinome;
            $this->getTemplate()->VALOR_DATA = date("d/m/Y", $ata->tarpinulat);
            $this->getTemplate()->VALOR_SITUACAO = $ata->farpinsitu == "A" ? "ATIVA" : "INATIVA";
            
            $this->getTemplate()->block("BLOCO_RESULTADO_ATAS");
        }
    }

    /* Fim Funções de exibição de Templetes Bloco */
    
    /* início funcoes de Apoio */
    private function carregarAno()
    {
        $anoAtual = (int) date('Y');
        $anos = array();
        for ($i = 0; $i < 3; $i ++) {
            array_push($anos, strval($anoAtual - $i));
        }
        return $anos;
    }
    
    // Recupera o seguencial da ata a partir do número da ata selecionado
    private function recuperarSequencialAta()
    {
        $numeroAta = $this->variables['post']['ata'];
        $numeroAtaExp = explode(".", $numeroAta);
        $numeroAtaCodi = explode("/", $numeroAtaExp[1]);
        $codigo = (int) $numeroAtaCodi;
        
        $codigo = preg_replace('/[^A-Za-z0-9\-]/', '', $numeroAta);
        
        return $numeroAta;
    }

    /* Consulta as atas da licitação */
    private function consultarLicitacaoatas($processo, $orgao, $ano)
    {
        $resultados = array();
        $db = Conexao();
        $sql = $this->sqlAtaLicitacao($processo, $orgao, $ano);
        $resultado = executarSQL($db, $sql);
        while ($resultado->fetchInto($licitacao, DB_FETCHMODE_OBJECT)) {
            array_push($resultados, $licitacao);
        }
        
        return $resultados;
    }

    /* Executa a licitação na qual a ata interna se refere */
    private function consultarLicitacaoAtaInterna($ano, $processo, $orgaoUsuario)
    {
        $db = Conexao();
        $sql = $this->sqlLicitacaoAtaInterna($ano, $processo, $orgaoUsuario);
        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($licitacao, DB_FETCHMODE_OBJECT);
        return $licitacao;
    }

    private function consultarParticipantesProcesso($processo, $ano)
    {
        $resultados = array();
        $sql = $this->sqlConsutarParticipantesProcesso($processo, $ano);
        $db = Conexao();
        $resultado = executarSQL($db, $sql);
        while ($resultado->fetchInto($participante, DB_FETCHMODE_OBJECT)) {
            $resultados[] = $participante;
        }
        return $resultados;
    }

    /* Fim Funções gerenciadoras/Executoras de query */
    
    /* Inicio Querys Sqls */
    private function sqlAtaLicitacao($processo, $orgao, $ano)
    {
        $sql = "select a.farpinsitu,a.aarpinpzvg,";
        $sql .= "a.tarpinulat,a.carpnosequ,a.alicpoanop,";
        $sql .= "a.corglicodi,s.csolcosequ,d.edoclinome from sfpc.tbataregistroprecointerna a";
        $sql .= " left outer join sfpc.tbsolicitacaolicitacaoportal s";
        $sql .= " on (s.clicpoproc = a.clicpoproc";
        $sql .= " and s.alicpoanop = a.alicpoanop";
        $sql .= " and s.ccomlicodi = a.ccomlicodi";
        $sql .= " and s.corglicodi = a.corglicodi)";
        $sql .= "left outer join sfpc.tbdocumentolicitacao d";
        $sql .= " on (d.clicpoproc = a.clicpoproc";
        $sql .= " and d.alicpoanop = a.alicpoanop";
        $sql .= " and d.ccomlicodi = a.ccomlicodi";
        $sql .= " and d.corglicodi = a.corglicodi)";
        $sql .= " where a.clicpoproc =" . $processo;
        $sql .= " and a.alicpoanop =" . $ano;
        // $sql .= " and a.corglicodi =".$orgao;

        return $sql;
    }

    /* Query de consulta da licitação, tranzendo as descrições relativas às chaves */
    private function sqlLicitacaoAtaInterna($ano, $processo, $orgaoUsuario)
    {
        $sql = "select distinct l.clicpoproc,";
        // $sql .= " a.carpnosequ,";
        // $sql .= " a.alicpoanop,";
        $sql .= " l.alicpoanop,";
        $sql .= " l.xlicpoobje,";
        $sql .= " l.ccomlicodi,";
        $sql .= " c.ecomlidesc,";
        $sql .= " o.corglicodi,";
        $sql .= " o.eorglidesc,";
        $sql .= " m.emodlidesc,";
        $sql .= " l.clicpocodl,";
        $sql .= " l.cgrempcodi,";
        $sql .= " l.alicpoanol";
        $sql .= " from sfpc.tblicitacaoportal l";
        // $sql .= " inner join sfpc.tbataregistroprecointerna a";
        // $sql .= " on l.clicpoproc= a.clicpoproc";
        $sql .= " inner join sfpc.tborgaolicitante o";
        $sql .= " on o.corglicodi=" . $orgaoUsuario;
        $sql .= " and l.corglicodi = o.corglicodi";
        $sql .= " inner join sfpc.tbcomissaolicitacao c";
        $sql .= " on l.ccomlicodi = c.ccomlicodi";
        $sql .= " inner join sfpc.tbmodalidadelicitacao m";
        $sql .= " on l.cmodlicodi = m.cmodlicodi";
        $sql .= " where l.alicpoanop =" . $ano;
        $sql .= " and l.clicpoproc =" . $processo;
        
        return $sql;
    }

    public function sqlConsutarParticipantesProcesso($processo, $ano)
    {
        $sql = "select ol.eorglidesc from sfpc.tbsolicitacaolicitacaoportal sc";
        $sql .= " join sfpc.tborgaolicitante ol";
        $sql .= " on ol.corglicodi = sc.corglicodi";
        $sql .= " where sc.clicpoproc = " . $processo;
        $sql .= " and sc.alicpoanop =" . $ano;
        
        return $sql;
    }

    /* Inicio Funções básicas */
    private function processVoltar()
    {
        $uri = 'CadAtaRegistroPrecoInternaSelecionarProcessoOrgaoExterno.php';
        header('location: ' . $uri);
    }

    /* Redireciona para a página de alteração */
    private function processSelecionar()
    {
        $processo = $_SESSION["processo"];
        $_SESSION['sequencialAta'] = $this->recuperarSequencialAta();
        
        $uri = 'CadCaronaOrgaoExternoIncluir.php?processo=' . $processo . '&ata=' . $_POST['ata_real'];
        header('location: ' . $uri);
    }

    /**
     * [frontController description]
     *
     * @return [type] [description]
     */
    private function frontController()
    {
        $botao = isset($this->variables['post']['Botao']) ? $this->variables['post']['Botao'] : 'Principal';
        
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

    /**
     * [__construct description]
     *
     * @param TemplatePaginaPadrao $template
     *            [description]
     * @param ArrayObject $session
     *            [description]
     */
    public function __construct(TemplatePaginaPadrao $template, ArrayObject $variablesGlobals)
    {
        /**
         * Settings
         */
        $this->setTemplate($template);
        $this->variables = $variablesGlobals;
        /**
         * Front Controller for action
         */
        $this->getTemplate()->NOME_PROGRAMA = "CadAtaRegistroPrecoInternaCaronaOrgaoExterno";
        $this->getTemplate()->TITULO_PROGRAMA = "Incluir - Carona Órgão Externo";
        $this->getTemplate()->ACAO_VISUALIZAR = "selecionar";
        $this->getTemplate()->VALUE_VISUALIZAR = "Selecionar";
        $this->getTemplate()->ENVIAR_VISUALIZAR = "Selecionar";
        
        $this->getTemplate()->BLOCK('BLOCO_BOTAO_ATAS_VISUALIZAR');
        $this->frontController();
    }

    /**
     * Running the application
     */
    public function run()
    {
        /**
         * Rendering the application
         */
        return $this->getTemplate()->show();
    }
}

/**
 * Bootstrap application
 */
function bootstrap()
{
    // Adiciona páginas no MenuAcesso #
    /**
     */
    $template = new TemplatePaginaPadrao("templates/CadAtaRegistroPrecoInternaTrocarFornecedorAtas.html", "Registro de Preço > Ata Interna >  Carona Órgão Externo > Incluir");
    
    $arrayGlobals = new ArrayObject();
    $arrayGlobals['session'] = $_SESSION;
    $arrayGlobals['server'] = $_SERVER;
    $arrayGlobals['separatorArray'] = $SimboloConcatenacaoArray;
    $arrayGlobals['separatorDesc'] = $SimboloConcatenacaoDesc;
    
    if ($arrayGlobals['server']['REQUEST_METHOD'] == "POST") {
        $arrayGlobals['post'] = $_POST;
    }
    
    if ($arrayGlobals['server']['REQUEST_METHOD'] == 'GET') {
        $arrayGlobals['get'] = $_GET;
    }
    
    $app = new CadAtaRegistroPrecoInternaCaronaOrgaoExterno($template, $arrayGlobals);
    echo $app->run();
    
    unset($app, $template, $arrayGlobals);
}

bootstrap();
