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
 * @category  Pitang_Registro_Preço
 * @package   Registropreco
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 * @version   GIT: 20150209_143500-93-g66fecca
 */

 // 220038--
 
if (! @require_once dirname(__FILE__) . "/../bootstrap.php") {
    throw new Exception("Error Processing Request - Bootstrap", 1);
}

Seguranca();

class RegistroPreco_Dados_CadAtaRegistroPrecoInternaSelecionarProcessoOrgaoExterno extends Dados_Abstrata
{

    /**
     *
     * @return NULL
     */
    public function consultarAtasInternas()
    {
        $repositorio = new Negocio_Repositorio_AtaRegistroPrecoInterna();
        return $repositorio->listarTodos();
    }

    /**
     * [consultarOrgaoComNumeracaoGerada description]
     *
     * @return [type] [description]
     */
    public function consultarOrgaoComNumeracaoGerada($corporativo = false)
    {
        if($corporativo) {
            $resultado = ClaDatabasePostgresql::executarSQL(self::sqlGeralOrgaoAtaGerada());
        } else {
            $resultado = ClaDatabasePostgresql::executarSQL($this->sqlOrgaoAtaGerada());
        }

        ClaDatabasePostgresql::hasError($resultado);

        return $resultado;
    }

    /**
     * [sqlProcessoRegistroPrecoEmHomologacao description]
     *
     * @param [type] $ano
     *            [description]
     * @return [type] [description]
     */
     private function sqlProcessoRegistroPrecoEmHomologacao($ano, $orgao)
     {
         $sql = "
            SELECT
            DISTINCT l.clicpoproc,
            l.alicpoanop,
            l.ccomlicodi,
            l.cgrempcodi,
            l.corglicodi,
            B.ecomlidesc            
            FROM
                sfpc.tblicitacaoportal l
                INNER JOIN
                    sfpc.tbcomissaolicitacao B
                        ON l.ccomlicodi = B.ccomlicodi
                INNER JOIN sfpc.tbusuariocomis D
                    ON l.cgrempcodi = D.cgrempcodi
                        AND D.ccomlicodi = l.ccomlicodi
                INNER JOIN sfpc.tbfaselicitacao E
                    ON l.clicpoproc = e.clicpoproc
                        AND l.alicpoanop = e.alicpoanop
                        AND l.cgrempcodi = e.cgrempcodi
                        AND l.ccomlicodi = e.ccomlicodi
                        AND l.corglicodi = e.corglicodi
				INNER JOIN sfpc.tbataregistroprecointerna F
					ON l.clicpoproc = F.clicpoproc
						AND l.alicpoanop = F.alicpoanop
						AND l.ccomlicodi = F.ccomlicodi
						AND l.corglicodi = F.corglicodi
						AND l.cgrempcodi = F.cgrempcodi
            WHERE
                l.alicpoanop = %d
                AND l.flicporegp LIKE 'S'
                AND l.corglicodi = %d
                AND e.cfasescodi IN (13, 26)
            ORDER BY
                B.ecomlidesc ASC,
                l.alicpoanop DESC,
                l.clicpoproc DESC";

         return sprintf($sql, $ano, $orgao);
     }

    /**
     * [sqlOrgaoAtaGerada description]
     *
     * @return [type] [description]
     */
    public function sqlOrgaoAtaGerada()
    {
        $cgrempcodi = $_SESSION['_cgrempcodi_'];
        $cusupocodi = $_SESSION['_cusupocodi_'];

        $sql = "
            select distinct Orgao.corglicodi, Orgao.eorglidesc from sfpc.tbusuariocentrocusto as UsuarioCusto
            inner join sfpc.tbcentrocustoportal as CentroCusto on UsuarioCusto.ccenposequ = CentroCusto.ccenposequ
	        inner join sfpc.tborgaolicitante as Orgao on Orgao.corglicodi = CentroCusto.corglicodi
            where UsuarioCusto.cgrempcodi = $cgrempcodi and UsuarioCusto.cusupocodi = $cusupocodi and UsuarioCusto.fusucctipo = 'C'
        ";
        
        return $sql;
    }

    /**
     * [sqlOrgaoAtaGerada description]
     *
     * @return [type] [description]
     */
    public function sqlGeralOrgaoAtaGerada()
    {
        $cgrempcodi = $_SESSION['_cgrempcodi_'];
        $cusupocodi = $_SESSION['_cusupocodi_'];

        $sql = "
            SELECT		ol.corglicodi, ol.eorglidesc
			FROM		sfpc.tborgaolicitante ol
			ORDER BY	ol.eorglidesc ASC
        ";

        return $sql;
    }

    /**
     *
     * @param Negocio_ValorObjeto_Corglicodi $orgao
     * @param Negocio_ValorObjeto_Alicpoanop $ano
     * @return NULL
     */
     public function consultarProcessoDoOrgaoNoAno($orgao, $ano)
     {
        $res = $this->executarSQL($this->sqlProcessoRegistroPrecoEmHomologacao($ano, $orgao));
 
         $this->hasError($res);
 
         return $res;
     }
}

class RegistroPreco_Negocio_CadAtaRegistroPrecoInternaSelecionarProcessoOrgaoExterno extends Negocio_Abstrata
{

    /**
     *
     * {@inheritDoc}
     *
     * @see Negocio_Abstrata::getDados()
     */
    public function getDados()
    {
        $this->setDados(new RegistroPreco_Dados_CadAtaRegistroPrecoInternaSelecionarProcessoOrgaoExterno());
        return parent::getDados();
    }

    /**
     *
     * @return boolean
     */
    public function validacao()
    {
        $_SESSION['mensagemFeedback'] = array();
        $retorno = true;
        if (! filter_var($_POST['orgaoGestor'], FILTER_VALIDATE_INT)) {
            $_SESSION['mensagemFeedback'][] = 'Órgão Gestor não selecionado';
            $retorno = false;
        }

        if (! filter_var($_POST['valorAno'], FILTER_VALIDATE_INT)) {
            $_SESSION['mensagemFeedback'][] = 'Ano não selecionado';
            $retorno = false;
        }

        if ($_POST['GerarNumeracaoProcesso'] == "") {
            $_SESSION['mensagemFeedback'][] = 'Processo não selecionado';
            $retorno = false;
        }

        return $retorno;
    }
}

class RegistroPreco_Adaptacao_CadAtaRegistroPrecoInternaSelecionarProcessoOrgaoExterno extends Adaptacao_Abstrata
{

    /**
     *
     * {@inheritDoc}
     *
     * @see Adaptacao_Abstrata::getNegocio()
     */
    public function getNegocio()
    {
        $this->setNegocio(new RegistroPreco_Negocio_CadAtaRegistroPrecoInternaSelecionarProcessoOrgaoExterno());
        return parent::getNegocio();
    }
}

class RegistroPreco_UI_CadAtaRegistroPrecoInternaSelecionarProcessoOrgaoExterno extends UI_Abstrata
{

    private $corporativo;    

    /**
     */
    public function __construct()
    {
        $template = new TemplatePaginaPadrao("templates/CadAtaRegistroPrecoInternaTrocarFornecedor.html", "Registro de Preço > Ata Interna > Carona Órgão Externo > Manter");
        $this->setTemplate($template);

        $this->getTemplate()->TITULO_SUPERIOR = "MANTER - CARONA ÓRGÃO EXTERNO";
        $this->getTemplate()->DESCRICAO_REGRAS_PAG = "Selecione o Gestor,
    			 o Ano e o Processo Licitatório que já possui
    			 a numeração das Atas Internas e clique no botão “Selecionar”.";
        $this->getTemplate()->NAME_PROGRAMA = "CadAtaRegistroPrecoInternaSelecionarProcessoOrgaoExternoManter";
        $this->corporativo = ($_SESSION['_fperficorp_'] == 'S') ? true : false;
    }

    public function getCorporativo() {
        return $this->corporativo;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see UI_Abstrata::getAdaptacao()
     */
    public function getAdaptacao()
    {
        $this->setAdaptacao(new RegistroPreco_Adaptacao_CadAtaRegistroPrecoInternaSelecionarProcessoOrgaoExterno());
        return parent::getAdaptacao();
    }

    /**
     *
     * @param array $anos
     */
    public function plotarBlocoAno(array $anos, $ano = null)
    {
        $contador = 0;
        if (empty($ano)) {
            date_default_timezone_set('America/Recife');
            $ano = date('Y');
        }
        $this->getTemplate()->ANO_VALUE = null;
        $this->getTemplate()->ANO_TEXT = "Selecione o ano...";
        $this->getTemplate()->ANO_SELECTED = "selected";
        $this->getTemplate()->block("BLOCO_ANO");
        $this->getTemplate()->clear("ANO_SELECTED");

        foreach ($anos as $value => $text) {
            $contador ++;

            $this->getTemplate()->ANO_VALUE = $text;
            $this->getTemplate()->ANO_TEXT = $text;

            // Vendo se a opção atual deve ter o atributo "selected"

            if ($ano != null && $text == $ano) {
                $this->getTemplate()->ANO_SELECTED = "selected";
            } else {
                // Caso esta não seja a opção atual, limpamos o valor da variável SELECTED
                $this->getTemplate()->clear("ANO_SELECTED");
            }

            $this->getTemplate()->block("BLOCO_ANO");
        }
    }

    /**
     *
     * @param unknown $orgaos
     */
    public function plotarBlocoOrgao($orgaos)
    {
        $orgaoNumeracao = filter_var($_POST['orgaoGestor'], FILTER_SANITIZE_NUMBER_INT);

        if ($orgaos == null) {
            return;
        }

        $this->getTemplate()->ORGAO_VALUE = null;
        $this->getTemplate()->ORGAO_TEXT = "Selecione um Órgão Gestor...";
        $this->getTemplate()->ORGAO_SELECTED = "selected";
        $this->getTemplate()->block("BLOCO_ORGAO");
        $this->getTemplate()->clear("ORGAO_SELECTED");

        foreach ($orgaos as $orgao) {
            $this->getTemplate()->ORGAO_VALUE = $orgao->corglicodi;

            $this->getTemplate()->ORGAO_TEXT = $orgao->eorglidesc;

            // Vendo se a opção atual deve ter o atributo "selected"
            if ($orgaoNumeracao == $orgao->corglicodi) {
                $this->getTemplate()->ORGAO_SELECTED = "selected";
            } else {
                $this->getTemplate()->clear("ORGAO_SELECTED");
            }

            $this->getTemplate()->block("BLOCO_ORGAO");
        }
    }

    /**
     *
     * @param unknown $processos
     */
    public function plotarBlocoProcesso($processos)
    {
        $processoNumeracao = null;
        if (isset($_POST['GerarNumeracaoProcess'])) {
            $processoNumeracao = filter_var($_POST['GerarNumeracaoProcess'], FILTER_SANITIZE_NUMBER_INT);
        }

        if ($processos == null) {
            return;
        }

        $ultCodComiss = 0;

        foreach ($processos as $processo) {
            if ($processo != null) {
                if ($ultCodComiss == 0) {
                    $ultCodComiss = $processo->ccomlicodi;
                    $this->getTemplate()->OPTGROUP_INICIO = '<optgroup label="' . $processo->ecomlidesc . '">';
                } elseif ($ultCodComiss != $processo->ccomlicodi) {
                    $this->getTemplate()->block("BLOCK_PROCESSO");
                    $this->getTemplate()->OPTGROUP_FINAL = '</optgroup>';
                    $ultCodComiss = $processo->ccomlicodi;
                    $this->getTemplate()->OPTGROUP_INICIO = '<optgroup label="' . $processo->ecomlidesc . '">';
                }

                $this->getTemplate()->TEXTO_PROCESSO = str_pad($processo->clicpoproc, 4, '0', STR_PAD_LEFT) . '/' . $processo->alicpoanop;
                
                $formataId = implode('-', array(
                    $processo->clicpoproc,
                    $processo->alicpoanop,
                    $processo->cgrempcodi,
                    $processo->ccomlicodi,
                    $processo->corglicodi,
                ));
               $this->getTemplate()->PROCESSO_VALUE = $formataId;

                //$this->getTemplate()->PROCESSO_VALUE = $processo->clicpoproc;
            }

            // Vendo se a opção atual deve ter o atributo "selected"CadAtaRegistroPrecoInternaManter
            if ($processo == $processoNumeracao) {
                $this->getTemplate()->PROCESSO_SELECTED = "selected";
            } else {
                // Caso esta não seja a opção atual, limpamos o valor da variável SELECTED
                $this->getTemplate()->clear("PROCESSO_SELECTED");
            }
            $this->getTemplate()->block("BLOCK_OPTION");
        }
        $this->getTemplate()->block("BLOCK_PROCESSO");
        // DIE;
    }
}

class CadAtaRegistroPrecoInternaSelecionarProcessoOrgaoExterno extends ProgramaAbstrato
{

    /**
     */
    private function processSelecionar()
    {
        if (! $this->getUI()
            ->getAdaptacao()
            ->getNegocio()
            ->validacao()) {
            $this->getUI()->mensagemSistema(implode(' ', $_SESSION['mensagemFeedback']), 0, 1);
            $this->proccessPrincipal();
            return;
        }

        $orgao = filter_var($_POST['orgaoGestor'], FILTER_SANITIZE_NUMBER_INT);
        $ano = filter_var($_POST['valorAno'], FILTER_SANITIZE_NUMBER_INT);
        $processo = filter_var($_POST['GerarNumeracaoProcesso'], FILTER_SANITIZE_NUMBER_INT);

        $_SESSION['orgao'] = $orgao;
        $_SESSION['ano'] = $ano;
        $_SESSION['processo'] = $processo;

        $uri = 'CadAtaRegistroPrecoInternaCaronaOrgaoExternoManter.php?processo='.$processo.'&ano='.$ano . '&orgao=' . $orgao;
        header('Location: ' . $uri);
        exit();
    }

    /**
     */
    private function atualizaAnos()
    {
        $anos = HelperPitang::carregarAno();
        $this->getUI()->plotarBlocoAno($anos);
    }

    /**
     *
     * @param integer $ano
     */
    private function atualizaProcessos($ano = null)
    {
        $orgao = null;
        if (isset($_POST['orgaoGestor'])) {
            $orgao = filter_var($_POST['orgaoGestor'], FILTER_SANITIZE_NUMBER_INT);
        }

        $ano = 0;
        if (isset($_POST['valorAno']) && ! empty($_POST['valorAno'])) {
            $ano = filter_var($_POST['valorAno'], FILTER_SANITIZE_NUMBER_INT);
        }

        $orgaos = $this->getUI()
            ->getAdaptacao()
            ->getNegocio()
            ->getDados()
            ->consultarOrgaoComNumeracaoGerada($this->getUI()->getCorporativo());
        $this->getUI()->plotarBlocoOrgao($orgaos);

        $anos = HelperPitang::carregarAno();

        if ($ano == 0) {
            $ano = $anos[$ano];
        }
        $this->getUI()->plotarBlocoAno($anos, $ano);

        $processos = $this->getUI()
            ->getAdaptacao()
            ->getNegocio()
            ->getDados()
            ->consultarProcessoDoOrgaoNoAno($orgao, $ano);
        $this->getUI()->plotarBlocoProcesso($processos);
    }

    /**
     */
    private function proccessPrincipal()
    {
        unset($_SESSION['orgao'], $_SESSION['ano'], $_SESSION['processo']);

        $atasInternas = $this->getUI()
            ->getAdaptacao()
            ->getNegocio()
            ->getDados()
            ->consultarAtasInternas();
        if ($GLOBALS['REQUEST_METHOD'] == 'POST' && empty($atasInternas)) {
            $mensagem = 'Não existe Ata Interna cadastrada';
            $this->getUI()->mensagemSistema($mensagem, 1, 0);
            return;
        }

        $orgaos = $this->getUI()
            ->getAdaptacao()
            ->getNegocio()
            ->getDados()
            ->consultarOrgaoComNumeracaoGerada($this->getUI()->getCorporativo());
        $this->getUI()->plotarBlocoOrgao($orgaos);

        $this->getUI()->getTemplate()->ANO_VALUE = null;
        $this->getUI()->getTemplate()->ANO_TEXT = "Selecione o ano...";
        $this->getUI()->getTemplate()->ANO_SELECTED = "selected";
        $this->getUI()
            ->getTemplate()
            ->block("BLOCO_ANO");
        $this->getUI()
            ->getTemplate()
            ->clear("ANO_SELECTED");

        $this->getUI()->getTemplate()->TEXTO_PROCESSO = "Selecione um processo...";
        $this->getUI()->getTemplate()->PROCESSO_VALUE = null;
        $this->getUI()->getTemplate()->PROCESSO_SELECTED = "selected";
        $this->getUI()
            ->getTemplate()
            ->block("BLOCK_OPTION");
        $this->getUI()
            ->getTemplate()
            ->clear("PROCESSO_SELECTED");
        $this->getUI()
            ->getTemplate()
            ->block("BLOCK_PROCESSO");
    }

    /**
     */
    public function configuracao()
    {
        $this->setUI(new RegistroPreco_UI_CadAtaRegistroPrecoInternaSelecionarProcessoOrgaoExterno());
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see IPrograma::frontController()
     */
    public function frontController()
    {
        $botao = isset($_POST['Botao']) ? filter_input(INPUT_POST, 'Botao', FILTER_SANITIZE_STRING) : 'Principal';

        switch ($botao) {
            case 'Voltar':
                $this->processVoltar();
                break;
            case 'Selecionar':
                $this->processSelecionar();
                break;
            case 'AtualizaAnos':
            case 'AtualizaProcessos':
                $this->atualizaProcessos();
                break;
            case 'Principal':
            default:
                $this->proccessPrincipal();
                break;
        }
    }
}

ProgramaAbstrato::iniciar(new CadAtaRegistroPrecoInternaSelecionarProcessoOrgaoExterno());
