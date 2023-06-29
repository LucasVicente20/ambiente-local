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

if (!@require_once dirname(__FILE__)."/../bootstrap.php") {
    throw new Exception("Error Processing Request - Bootstrap", 1);
}

class CadAdicionarParticipante
{
    /**
     * [$template description]
     * @var \TemplatePaginaPadrao
     */
    private $template;
    /**
     * [$variables description]
     * @var \ArrayObject
     */
    private $variables;

     /**
     * Gets the value of template.
     *
     * @return mixed
     */

    private $files;

    private function getTemplate()
    {
        return $this->template;
    }

    /**
     * Sets the value of template.
     *
     * @param TemplatePaginaPadrao $template the template
     *
     * @return self
     */
    private function setTemplate(TemplatePortal $template)
    {
        $this->template = $template;
        return $this;
    }

   /**
     * [proccessPrincipal description]
     * @param  [type] $variablesGlobals [description]
     * @return [type] [description]
     */
    private function proccessPrincipal()
    {
        $orgao =    $this->variables['get']['orgao'];
        $ano =      $this->variables['get']['ano'];
        $processo = $this->variables['get']['processo'];
        $ata =      $this->variables['get']['ata'];
        $itemSelecionado =     $this->variables['get']['item'];


        $this->getTemplate()->ITEMGET = $itemSelecionado;

        $this->listarOrgaos($ata, $ano, $itemSelecionado);
        $this->listarItemAta($ata, $ano, $itemSelecionado);
    }

    private function listarItemAta($numeroAta, $anoAta, $itemSelecionado)
    {
        $itens = $_SESSION['itens'];

        $contador =1;
        //Verifica se algum item foi informado
        $condicaoItemInformado = !empty($itemSelecionado);

        foreach ($itens as $item) {
            if ($condicaoItemInformado) {
                if ($item->codigoReduzido == $itemSelecionado) {
                    $this->getTemplate()->VALOR_ORD = $contador;
                    $this->getTemplate()->VALOR_DESCRICAO = $item->descricao;
                    $this->getTemplate()->SEQ_ITEM = $item->codigoReduzido;
                    $this->getTemplate()->block('BLOCO_ITEM');
                    $contador++;
                }
            } else {
                $this->getTemplate()->VALOR_ORD = $contador;
                $this->getTemplate()->VALOR_DESCRICAO = $item->descricao;
                $this->getTemplate()->SEQ_ITEM = $item->codigoReduzido;
                $this->getTemplate()->block('BLOCO_ITEM');
                $contador++;
            }
        }
    }

    private function listarOrgaos($ata, $ano)
    {
        $db = Conexao();
        $sql = $this->sqlListarOrgaos($ata, $ano);
        $resultado = executarSQL($db, $sql);

        while ($resultado->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $this->getTemplate()->ORGAO_SEQ = $item->corglicodi;
            $this->getTemplate()->ORGAO_NOME = $item->eorglidesc;
            $this->getTemplate()->block('BLOCO_ORGAO');
        }
    }

    private function sqlListarOrgaos($sequencialAta, $ano)
    {
        // Retornar apenas os participantes
        $subQuery = "SELECT
						sc.corglicodi
					FROM
						sfpc.tbataregistroprecointerna arpi
						INNER JOIN sfpc.tblicitacaoportal lp
							ON lp.clicpoproc = arpi.clicpoproc
							AND lp.alicpoanop = arpi.alicpoanop
							AND lp.cgrempcodi = arpi.cgrempcodi
							AND lp.ccomlicodi = arpi.ccomlicodi
						INNER JOIN sfpc.tbsolicitacaolicitacaoportal slp
							ON slp.clicpoproc = arpi.clicpoproc
							AND slp.alicpoanop = arpi.alicpoanop
							AND slp.cgrempcodi = arpi.cgrempcodi
							AND slp.ccomlicodi = arpi.ccomlicodi
						INNER JOIN sfpc.tbsolicitacaocompra sc
							ON sc.csolcosequ = slp.csolcosequ
							AND sc.fsolcorgpr = 'S'
							AND sc.csitsocodi != 10
					WHERE
						arpi.carpnosequ = $sequencialAta";

        // Retorna os órgãos não participantes
        $sql = "SELECT
    				ol.corglicodi, ol.eorglidesc
    			FROM
    				sfpc.tborgaolicitante ol
    			WHERE
    				ol.corglicodi NOT IN($subQuery)
    			ORDER BY
    				ol.eorglidesc";

        return $sql;
    }

    private function getNumeroAtaInterna($ata)
    {
        $numeroAta = getNumeroSolicitacaoCompra(ClaDatabasePostgresql::getConexao(), $ata->csolcosequ);
        $valoresExploded = explode(".", $numeroAta);
        $valorUnidadeOrc = substr($valoresExploded[0], 2, 2);

        $valorAta = str_pad($ata->corglicodi, 2, '0', STR_PAD_LEFT);
        $valorAta .= $valorUnidadeOrc . '.';
        $valorAta .= str_pad($ata->carpnosequ, 4, '0', STR_PAD_LEFT) . '/';
        $valorAta .= $ata->alicpoanop;

        return $valorAta;
    }

    private function montarTela()
    {
        $ano     =    $this->variables['post']['ano'];
        $orgao   =    $this->variables['post']['orgao'];
        $processo=    $this->variables['post']['processo'];
        $ata     =  $this->variables['post']['ata'];

        $this->plotarBlocoBotao($ano, $orgao, $processo, $ata);

        $atas = $this->consultarAtaPorChave($ano, $processo, $orgao, $ata);
        $licitacao = $this->consultarLicitaçãoAtaInterna($ano, $processo, $orgao);

        $dada = $_REQUEST["data"];
        $vigencia = $_REQUEST["vigencia"];

        $this->plotarBlocoLicitacao($licitacao, $atas, $dada, $vigencia);
    }

    private function insereDocumento()
    {
        $file = $_REQUEST["fileArquivo"];
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



    private function consultarLicitacaoAtaInterna($ano, $processo, $orgaoUsuario)
    {
        $db = Conexao();
        $sql = $this->sqlLicitacaoAtaInterna($ano, $processo, $orgaoUsuario);
        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($licitacao, DB_FETCHMODE_OBJECT);
        return $licitacao;
    }

    private function consultarAtaPorChave($processo, $orgao, $ano, $numeroAta)
    {
        $db = Conexao();
        $sql = $this->sqlAtaPorchave($processo, $orgao, $ano, $numeroAta);
        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($ata, DB_FETCHMODE_OBJECT);
        return $ata;
    }

    private function consultarValorMaximoDocumento($processo, $orgao, $ano, $grupo)
    {
        $db = Conexao();
        $sql = $this->sqlCodigoMaximoDocumento($processo, $orgao, $ano, $grupo);
        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($documento, DB_FETCHMODE_OBJECT);
        return $documento;
    }

    private function montaValoresInsercaoDocumento($processo, $orgao, $ano, $grupo, $comissao)
    {
        $timestamp = date('U');
        $swatch = date('B');

        $now = $timestamp.$swatch;

        $docCodMAx = 1;
        $docNome = "Documento.txt";
        $valores = $processo.",".$ano.",".$grupo.",".$comissao.",".$orgao.",".$docCodMAx.",".$docNome.",".$timestamp.",".$_SESSION['_cusupocodi_'].",".$now;
    }

    private function sqlLicitacaoAtaInterna($ano, $processo, $orgaoUsuario)
    {
        $sql = "select distinct l.clicpoproc,";
        $sql .= " l.alicpoanop,";
        $sql .= " l.xlicpoobje,";
        $sql .= " l.ccomlicodi,";
        $sql .= " c.ecomlidesc,";
        $sql .= " o.corglicodi,";
        $sql .= " o.eorglidesc,";
        $sql .= " m.emodlidesc,";
        $sql .= " l.clicpocodl,";
        $sql .= " l.alicpoanol";
        $sql .= " from sfpc.tblicitacaoportal l";
        $sql .= " inner join sfpc.tborgaolicitante o";
        $sql .= " on o.corglicodi=".$orgaoUsuario;
        $sql .= " and l.corglicodi = o.corglicodi";
        $sql .= " inner join sfpc.tbcomissaolicitacao c";
        $sql .= " on l.ccomlicodi = c.ccomlicodi";
        $sql .= " inner join sfpc.tbmodalidadelicitacao m";
        $sql .= " on l.cmodlicodi = m.cmodlicodi";
        $sql .= " where l.alicpoanop =".$ano;
        $sql .= " and l.clicpoproc =".$processo;
        return $sql;
    }

    private function sqlAtaPorchave($processo, $orgao, $ano, $chaveAta)
    {
        $sql = "select a.aarpinpzvg, a.tarpindini, f.nforcrrazs, d.edoclinome,";
        $sql .= " a.corglicodi, a.carpnosequ, a.alicpoanop, s.csolcosequ, a.aarpinanon, carpnoseq1, ";

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
        $sql .= " and d.clicpoproc =".$processo;
        $sql .= " and d.corglicodi =".$orgao;
        $sql .= " and d.alicpoanop =".$ano;

        $sql .= " where a.carpnosequ =".$chaveAta;

        return $sql;
    }

    private function sqlCodigoMaximoDocumento($processo, $orgao, $ano, $grupo)
    {
        $sql="select max(d.cdoclicodi) from sfpc.tbdocumentolicitacao d";
        $sql.="where d.clicpoproc =".$processo;
        $sql.="and d.cgrempcodi =".$grupo;
        $sql.="and d.corglicodi =".$orgao;
        $sql.="and d.alicpoanop =".$ano;

        return $sql;
    }

    private function sqlInsereDocumento($valores)
    {
        $sql="INSERT INTO sfpc.tbdocumentolicitacao (clicpoproc,alicpoanop,cgrempcodi,ccomlicodi,corglicodi,";
        $sql.= "cdoclicodi,edoclinome,tdoclidata,cusupocodi,tdocliulat)";
        $sql.=" VALUES (".$valores.")";
    }

    private function processVoltar()
    {
        $uri  = 'CadAtaRegistroPrecoInternaManterEspecial.php';
        header('location: ' . $uri);
    }

    private function participanteCadastrado($codigoOrgao, $participantes)
    {
        foreach ($participantes as $participante) {
            if ($participante->sequencial == $codigoOrgao) {
                return true;
            }
        }
        return false;
    }

    private function adicionar()
    {
        $sequencialOrgao = $this->variables['post']['orgaoParticipante'];
        $itens = $this->variables['post']['item'];
        $orgao = $this->getOrgao($sequencialOrgao);
        $itemSelecionado = $_REQUEST['itemGet'];

        foreach ($_SESSION['itens'] as $itemAta) {
            foreach ($itens as $sequencialItem => $quantidade) {
                $novoOrgaoParticipante = new stdClass();
                $qtdMaiorQueZero = (floatval($quantidade) > 0);

                if (!empty($itemSelecionado) && $itemSelecionado == $itemAta->codigoReduzido && $qtdMaiorQueZero) {
                    $novoOrgaoParticipante->sequencial = $orgao->corglicodi;
                    $novoOrgaoParticipante->descricao = $orgao->eorglidesc;
                    $novoOrgaoParticipante->quantidadeItem = $quantidade;
                    $novoOrgaoParticipante->inativo = 'N';
                    $novoOrgaoParticipante->situacaoParaItem = 'A';

                    if (!$this->participanteCadastrado($orgao->corglicodi, $itemAta->participantes)) {
                        $itemAta->participantes[] = $novoOrgaoParticipante;
                    }
                } else {
                    if (empty($itemSelecionado) && $sequencialItem == $itemAta->codigoReduzido && floatval($quantidade) > 0) {
                        $novoOrgaoParticipante->sequencial = $orgao->corglicodi;
                        $novoOrgaoParticipante->descricao = $orgao->eorglidesc;
                        $novoOrgaoParticipante->quantidadeItem = $quantidade;
                        $novoOrgaoParticipante->inativo = 'N';
                        $novoOrgaoParticipante->situacaoParaItem = 'A';

                        if (!$this->participanteCadastrado($orgao->corglicodi, $itemAta->participantes)) {
                            $itemAta->participantes[] = $novoOrgaoParticipante;
                        }
                    }
                }
            }
        }

        echo "<script>opener.document.cadMigracaoAtaAlterar.submit()</script>";
        echo "<script>self.close()</script>";

        echo "<script>opener.document.CadAtaRegistroPrecoInternaManterEspecialAtasAlterar.submit()</script>";
        echo "<script>self.close()</script>";

        return;
    }

    private function getOrgao($sequencialOrgao)
    {
        $database = Conexao();
        $sql = $this->sqlSelectOrgao($sequencialOrgao);
        $resultado = executarSQL($database, $sql);
        $resultado->fetchInto($orgao, DB_FETCHMODE_OBJECT);

        return $orgao;
    }

    private function sqlSelectOrgao($sequencialOrgao)
    {
        $sql = "SELECT
    				ol.corglicodi,
				    ol.eorglidesc
				FROM
				    sfpc.tborgaolicitante ol
				WHERE
				    ol.corglicodi = %d";

        return sprintf($sql, $sequencialOrgao);
    }

    /**
     * [frontController description]
     * @return [type] [description]
     */
    private function frontController()
    {
        $botao = isset($this->variables['post']['Botao'])
            ? $this->variables['post']['Botao']
            : 'Principal';

        switch ($botao) {
            case 'Voltar':
                $this->processVoltar();
                break;
            case 'Adicionar':
                $this->adicionar();
                break;
            case 'Principal':
            default:
                $this->proccessPrincipal();
        }
    }

    /**
     * [__construct description]
     * @param TemplatePaginaPadrao $template [description]
     * @param ArrayObject          $session  [description]
     */
    public function __construct(TemplatePortal $template, ArrayObject $variablesGlobals)
    {
        $this->setTemplate($template);
        $this->variables = $variablesGlobals;

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

    $template = new TemplatePortal("templates/CadAdicionarParticipante.html");

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

    $app = new CadAdicionarParticipante($template, $arrayGlobals);
    echo $app->run();

    unset($app, $template, $arrayGlobals);
}

bootstrap();
