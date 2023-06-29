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

 // 220038--
 
if (! @require_once dirname(__FILE__) . "/../bootstrap.php") {
    throw new Exception("Error Processing Request - Bootstrap", 1);
}

class CadRegistroPrecoLicitacaoNumeracaoAtas
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
     * Gets the value of template.
     *
     * @return mixed
     */
    private $licitacao;

    public function getTemplate()
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
    public function setTemplate(TemplatePaginaPadrao $template)
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
        $ano        = $_REQUEST['ano'];
        $processo   = $_REQUEST['processo'];
        $fornecedor = $_REQUEST['fornecedor'];
        $grupo      = $_REQUEST['grupo'];
        $ata        = $_REQUEST['seqAta'];

        $this->getTemplate()->PROCESSO = $processo;
        $this->getTemplate()->ANO = $ano;
                
        //$orgaoUsuario = FuncoesUsuarioLogado::obterOrgaoUsuarioLogado();
        $orgaoUsuario = end(explode("-", $_SESSION['processo_selecionado']));
        $licitacaoAtas = $this->consultarLicitacaoAtasInterna($ano, $processo, $orgaoUsuario, $fornecedor, $ata, $grupo);
        $licitacaoAtas = (object) $licitacaoAtas;

        $this->plotarDadosLicitacaoAtas($licitacaoAtas);

        $_SESSION["numeroAta"] = $ata;

        $itensAta = $this->consultarItemRegistroPreco($ata);

        if ($itensAta != null) {
            $this->plotarDadosItemRegistroPreco($itensAta);
        }
    }

    private function plotarDadosLicitacaoAtas($licitacaoAtas)
    {
        $this->getTemplate()->VALOR_COMISSAO = $licitacaoAtas->ecomlidesc;
        $this->getTemplate()->VALOR_PROCESSO = substr($licitacaoAtas->clicpoproc + 10000, 1);
        $this->getTemplate()->VALOR_ANO = $licitacaoAtas->alicpoanop;
        $this->getTemplate()->VALOR_MODALIDADE = $licitacaoAtas->emodlidesc;
        $this->getTemplate()->VALOR_LICITACAO = substr($licitacaoAtas->clicpocodl + 10000, 1);
        $this->getTemplate()->VALOR_ANO_LICITACAO = $licitacaoAtas->alicpoanol;
        $this->getTemplate()->VALOR_ORG_LIMITE = $licitacaoAtas->eorglidesc;
        $this->getTemplate()->VALOR_DATA_INICIAL = DataBarra($licitacaoAtas->tarpindini);
        $this->getTemplate()->VALOR_VIGENCIA_ATA = $licitacaoAtas->aarpinpzvg;
        $this->getTemplate()->VALOR_FORNECEDOR_ORIGINAL = $licitacaoAtas->nforcrrazs;

        $this->getTemplate()->VALOR_NUMERO_ATA = '';
        $this->getTemplate()->VALOR_CONTROLE_ATA = tipoControle($licitacaoAtas->farpnotsal);

      
        if (! empty($licitacaoAtas->csolcosequ) || true) {
            $_SESSION['valor_ccenpocorg'] = $_REQUEST['ccenpocorg'];
            $_SESSION['valor_ccenpounid'] = $_REQUEST['ccenpounid'];

            $ccenpocorg = $_REQUEST['ccenpocorg'];
            $ccenpounid = $_REQUEST['ccenpounid'];

            $numeroAta = $ccenpocorg . str_pad($ccenpounid, 2, '0', STR_PAD_LEFT);
            $numeroAta .= "." . str_pad($licitacaoAtas->carpincodn, 4, "0", STR_PAD_LEFT) . "/" . $licitacaoAtas->aarpinanon;

            $this->getTemplate()->VALOR_NUMERO_ATA = $numeroAta;
        }

        $this->getTemplate()->block("BLOCO_LICITACAO_ATA");
        $this->getTemplate()->block("BLOCO_RESULTADO_PEQUISA");
    }

    private function plotarDadosItemRegistroPreco($itensAta)
    {
        foreach ($itensAta as $item) {            
            $this->getTemplate()->VALOR_ORDEM = $item->aitarporde;
            // $this->getTemplate()->VALOR_DESCRICAO = ($item->eitarpdescmat != null) ? $item->eitarpdescmat : $item->eitarpdescse;
            $this->getTemplate()->VALOR_DESCRICAO = ($item->ematepdesc != null) ? $item->ematepdesc : $item->eservpdesc;
            $this->getTemplate()->VALOR_TIPO = ($item->cmatepsequ != null) ? "CADUM" : "CADUS";
            $this->getTemplate()->VALOR_COD_RED = $item->cmatepsequ == null ? $item->cservpsequ : $item->cmatepsequ;
            $this->getTemplate()->VALOR_LOTE = $item->citarpnuml;

            $this->getTemplate()->VALOR_MARCA = ($item->eitarpmarc == 'null') ? '' : $item->eitarpmarc;
            $this->getTemplate()->VALOR_MODELO = ($item->eitarpmode == 'null') ? '' : $item->eitarpmode;

            $this->getTemplate()->VALOR_UNIDADE = $item->eunidmdesc;
            $this->getTemplate()->VALOR_QUANTIDADE = converte_valor_estoques($item->aitarpqtor);
            $this->getTemplate()->VALOR_HOMOLOGADO = converte_valor_licitacao($item->vitarpvori);
            $this->getTemplate()->VALOR_TOTAL = converte_valor_licitacao($item->vitarpvori * $item->aitarpqtor);

            $this->getTemplate()->block("BLOCO_RESULTADO_ATAS");
        }
    }

    private function consultarItemRegistroPreco($sequencialAtaInterna)
    {
        $db = Conexao();
        $sql = $this->sqlItemAtaRegistroPreco($sequencialAtaInterna);
        $resultado = executarSQL($db, $sql);

        $arrayItens = array();
        while ($resultado->fetchInto($itemAta, DB_FETCHMODE_OBJECT)) {
            $arrayItens[] = $itemAta;
        }
        return $arrayItens;
    }

    private function consultarLicitacaoAtasInterna($ano, $processo, $orgaoUsuario, $fornecedor, $ata, $grupo)
    {
        $db = Conexao();
        $sql = $this->sqlLicitacaoAtaInterna($ano, $processo, $orgaoUsuario, $grupo);
        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($licitacaoAtas, DB_FETCHMODE_OBJECT);

        $sql = $this->sqlLicitacaoAta($ata);
        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($atas, DB_FETCHMODE_OBJECT);

        $licitacaoInternaAtas = array();

        $licitacaoInternaAtas['ecomlidesc'] = $licitacaoAtas->ecomlidesc;
        $licitacaoInternaAtas['clicpoproc'] = $licitacaoAtas->clicpoproc;
        $licitacaoInternaAtas['alicpoanop'] = $licitacaoAtas->alicpoanop;
        $licitacaoInternaAtas['emodlidesc'] = $licitacaoAtas->emodlidesc;
        $licitacaoInternaAtas['carpnosequ'] = $atas->carpnosequ;
        $licitacaoInternaAtas['aarpinanon'] = $atas->aarpinanon;
        $licitacaoInternaAtas['eorglidesc'] = $licitacaoAtas->eorglidesc;
        $licitacaoInternaAtas['tarpindini'] = $atas->tarpindini != null ? $atas->tarpindini : "";
        $licitacaoInternaAtas['aarpinpzvg'] = $atas->aarpinpzvg != null ? $atas->aarpinpzvg : "";
        $licitacaoInternaAtas['nforcrrazs'] = $atas->nforcrrazs;
        $licitacaoInternaAtas['clicpocodl'] = $licitacaoAtas->clicpocodl;
        $licitacaoInternaAtas['alicpoanol'] = $licitacaoAtas->alicpoanol;
        $licitacaoInternaAtas['csolcosequ'] = $licitacaoAtas->csolcosequ;
        $licitacaoInternaAtas['corglicodi'] = $licitacaoAtas->corglicodi;
        $licitacaoInternaAtas['carpincodn'] = $atas->carpincodn;
        $licitacaoInternaAtas['farpnotsal'] = $licitacaoAtas->farpnotsal;
    
        return $licitacaoInternaAtas;
    }

    private function sqlLicitacaoAtaInterna($ano, $processo, $orgaoUsuario, $grupo)
    {
        $processoCompleto = $_SESSION['processo_selecionado'];
        $ata = $_GET['seqAta'];
        $valores = explode('-', $processoCompleto);
        
        $sql = " SELECT ";
        $sql .= "	DISTINCT l.clicpoproc, ";
        $sql .= "   l.alicpoanop, l.xlicpoobje, l.ccomlicodi, c.ecomlidesc, o.corglicodi, ";
        $sql .= "   o.eorglidesc, m.emodlidesc, l.clicpocodl, l.alicpoanol, l.cgrempcodi, l.clicpocodl, l.alicpoanol, slp.csolcosequ, arpn.farpnotsal ";
        $sql .= " FROM ";
        $sql .= "	sfpc.tblicitacaoportal l  ";
        $sql .= "       LEFT JOIN sfpc.tbataregistropreconova arpn on arpn.carpnosequ = " . $ata;
        $sql .= "		INNER JOIN sfpc.tborgaolicitante o ON o.corglicodi = $orgaoUsuario AND l.corglicodi = o.corglicodi ";
        $sql .= "     	INNER JOIN sfpc.tbcomissaolicitacao c ON l.ccomlicodi = c.ccomlicodi ";
        $sql .= "		INNER JOIN sfpc.tbmodalidadelicitacao m ON l.cmodlicodi = m.cmodlicodi ";
        $sql .= "       INNER JOIN sfpc.tbcomissaolicitacao cs ON l.ccomlicodi = cs.ccomlicodi AND cs.cgrempcodi =" . $grupo;
        $sql .= "       LEFT OUTER JOIN sfpc.tbsolicitacaolicitacaoportal slp ON slp.alicpoanop = $ano ";
        $sql .= "    		AND slp.clicpoproc = $processo AND slp.cgrempcodi = $grupo AND slp.corglicodi = $orgaoUsuario";
        $sql .= " WHERE ";
        //$sql .= "		l.alicpoanop = $ano AND l.clicpoproc = $processo AND l.cgrempcodi =" . $codigoGrupo;

        $sql .= " l.clicpoproc = %d AND l.alicpoanop = %d AND l.cgrempcodi = %d AND l.ccomlicodi = %d AND l.corglicodi = %d";

        $sql = sprintf($sql, $valores[0], $valores[1], $grupo, $valores[3], $valores[4]);

        return $sql;
    }

    private function sqlLicitacaoAta($ata)
    {
        $sql = " select a.carpnosequ, a.tarpindini, a.aarpinpzvg, f.nforcrrazs, a.aforcrsequ, a.carpincodn, a.aarpinanon From sfpc.tbataregistroprecointerna a";
        $sql .= " INNER JOIN sfpc.tbfornecedorcredenciado f";
        $sql .= " ON a.aforcrsequ = f.aforcrsequ";
        $sql .= " WHERE a.carpnosequ =" . $ata;
        $sql .= " GROUP BY f.nforcrrazs, a.carpnosequ,a.tarpindini,a.aarpinpzvg,a.aforcrsequ,a.carpincodn, a.aarpinanon";
        $sql .= " ORDER BY a.carpnosequ asc";

        return $sql;
    }

    private function sqlItemAtaRegistroPreco($sequencialAta)
    {
        // $sql = " select i.aitarporde,";
        // $sql .= " i.eitarpdescmat,i.eitarpdescse,";
        // $sql .= " i.cmatepsequ, i.citarpnuml,";
        // $sql .= " i.aitarpqtor, i.vitarpvatu,i.cservpsequ,i.eitarpdescse,";
        // $sql .= " (CASE WHEN i.cmatepsequ IS NOT NULL";
        // $sql .= " THEN( SELECT um.eunidmsigl from sfpc.tbunidadedemedida um";
        // $sql .= " RIGHT JOIN sfpc.tbmaterialportal mp ON mp.cmatepsequ = i.cmatepsequ";
        // $sql .= " where um.cunidmcodi = mp.cunidmcodi";
        // $sql .= " )END ) AS eunidmdesc";
        // $sql .= " from sfpc.tbitemataregistropreconova i";
        // $sql .= " where i.carpnosequ=" . $sequencialAta;
        $sql = "
          SELECT
            i.aitarporde,
            mpo.ematepdesc,
            i.eitarpdescmat,
            srvp.eservpdesc,
            i.eitarpdescse,
            i.cmatepsequ,
            i.citarpnuml,
            i.aitarpqtor,
            i.vitarpvatu,
            i.cservpsequ,
            i.eitarpdescse,
            i.vitarpvori,
            (
                CASE
                    WHEN i.cmatepsequ IS NOT NULL
                    THEN(
                        SELECT
                            um.eunidmsigl
                        FROM
                            sfpc.tbunidadedemedida um RIGHT JOIN sfpc.tbmaterialportal mp
                                ON mp.cmatepsequ = i.cmatepsequ
                        WHERE
                            um.cunidmcodi = mp.cunidmcodi
                    )
                END
            ) AS eunidmdesc,
            i.eitarpmarc,
            i.eitarpmode
        FROM
            sfpc.tbitemataregistropreconova i
            LEFT JOIN sfpc.tbmaterialportal mpo ON mpo.cmatepsequ = i.cmatepsequ
            LEFT JOIN sfpc.tbservicoportal srvp ON srvp.cservpsequ = i.cservpsequ
        WHERE
            i.carpnosequ = $sequencialAta
        ";        
        return $sql;
    }

    private function processImprimir()
    {
        $ano = $this->variables['post']['ano'];
        $processo = $_SESSION['processo_selecionado'];

        $pdf = new PdfLicitacaoDetalhamentoAtas();
        $pdf->setProcesso($processo);
        $pdf->setAno($ano);

        $pdf->gerarRelatorio();
    }

    private function processCopiar()
    {
        $ata = $_SESSION["numeroAta"];

        $uri = 'CadIncluirIntencaoRegistroPrecoCopiarItens.php?ata=' . $ata;
        header('location: ' . $uri);
        exit();
    }

    private function processVoltar()
    {
        $ano = $this->variables['post']['ano'];
        $processo = $_SESSION['processo_selecionado'];

        $uri = 'CadRegistroPrecoLicitacaoAtas.php?processo=' . $processo . '&ano=' . $ano;
        header('location: ' . $uri);
        exit();
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
            case 'Desfazer':
                $this->desfazerAtasLicitacao();
                break;
            case 'Gerar':
                $this->gerarAtasLicitacao();
                break;
            case 'Imprimir':
                $this->processImprimir();
                break;
            case 'Copiar':
                $this->processCopiar();
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
    global $SimboloConcatenacaoArray, $SimboloConcatenacaoDesc;

    /**
     */
    $template = new TemplatePaginaPadrao("templates/CadRegistroPrecoLicitacaoNumeracaoAtas.html", "Registro Preço > Ata Interna > Gerar Numeração");

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

    $app = new CadRegistroPrecoLicitacaoNumeracaoAtas($template, $arrayGlobals);
    echo $app->run();
}

bootstrap();
