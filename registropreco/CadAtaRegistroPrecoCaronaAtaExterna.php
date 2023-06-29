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
 */

# Alterado: Pitang Agile TI - Caio Coutinho
# Data:     04/12/2018
# Objetivo: Tarefa Redmine 207316
# --------------------------------------------------------------

// 220038--

if (! @require_once dirname(__FILE__) . "/../bootstrap.php") {
    throw new Exception("Error Processing Request - Bootstrap", 1);
}

Seguranca();

/**
 * A camada de Dados conterá o código que irá realizar todo o acesso aos dados.
 *
 * @author jfsi
 *
 */
class RegistroPreco_Dados_CadAtaRegistroPrecoCaronaAtaExterna extends Dados_Abstrata
{
    /**
     *
     * @param integer $orgao
     * @param integer $ano
     * @return string
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
            INNER JOIN sfpc.tbataregistroprecointerna arpi
                ON l.clicpoproc = arpi.clicpoproc
                AND l.alicpoanop = arpi.alicpoanop
                AND l.cgrempcodi = arpi.cgrempcodi
                AND l.ccomlicodi = arpi.ccomlicodi
                AND l.corglicodi = arpi.corglicodi
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
     *
     * @return string
     */
    private function sqlOrgaoAtaGerada()
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
     *
     * @return string
     */
    private function sqlGeralOrgaoAtaGerada()
    {
        $sql = " SELECT distinct Orgao.corglicodi, Orgao.eorglidesc 
                FROM sfpc.tborgaolicitante AS Orgao
                WHERE 1=1 ORDER BY Orgao.eorglidesc ASC";

        return $sql;
    }

    /**
     *
     * @return NULL
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
     *
     * @param integer $orgao
     * @param integer $ano
     * @return NULL
     */
    public function consultarProcessoDoOrgaoNoAno($orgao, $ano)
    {
        return ClaDatabasePostgresql::executarSQL($this->sqlProcessoRegistroPrecoEmHomologacao($ano, $orgao));
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
class RegistroPreco_Negocio_CadAtaRegistroPrecoCaronaAtaExterna extends Negocio_Abstrata
{
    /**
     *
     * {@inheritDoc}
     *
     * @see Negocio_Abstrata::getDados()
     */
    public function getDados()
    {
        $this->setDados(new RegistroPreco_Dados_CadAtaRegistroPrecoCaronaAtaExterna());

        return parent::getDados();
    }

    /**
     */
    public function validarFormulario()
    {
        $validado = true;
        $_SESSION['mensagemFeedback'] = array();
        if (! filter_var($_POST['orgaoGestor'], FILTER_VALIDATE_INT)) {
            array_push($_SESSION['mensagemFeedback'], "Órgão não foi selecionado");
            $validado = false;
        }

        if (! filter_var($_POST['valorAno'], FILTER_VALIDATE_INT)) {
            array_push($_SESSION['mensagemFeedback'], "Ano não foi selecionado");
            $validado = false;
        }

        if ($_POST['GerarNumeracaoProcesso'] == "") {
            array_push($_SESSION['mensagemFeedback'], "Processo Licitatório não foi selecionado");
            $validado = false;
        }

        return $validado;
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
class RegistroPreco_Adaptacao_CadAtaRegistroPrecoCaronaAtaExterna extends Adaptacao_Abstrata
{
    /**
     *
     * {@inheritDoc}
     *
     * @see Adaptacao_Abstrata::getNegocio()
     */
    public function getNegocio()
    {
        $this->setNegocio(new RegistroPreco_Negocio_CadAtaRegistroPrecoCaronaAtaExterna());

        return parent::getNegocio();
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
class RegistroPreco_UI_CadAtaRegistroPrecoCaronaAtaExterna extends UI_Abstrata
{
    private $corporativo;    

    /**
     *
     * {@inheritDoc}
     *
     * @see UI_Abstrata::getAdaptacao()
     */
    public function getAdaptacao()
    {
        $this->setAdaptacao(new RegistroPreco_Adaptacao_CadAtaRegistroPrecoCaronaAtaExterna());

        return parent::getAdaptacao();
    }

    /**
     * [__construct description]
     */
    public function __construct()
    {
        $this->setTemplate(new TemplatePaginaPadrao("templates/CadAtaRegistroPrecoCaronaAtaExterna.html", "Registro de Preço > Ata Interna > Carona Órgão Externo > Incluir"));
        $this->corporativo = ($_SESSION['_fperficorp_'] == 'S') ? true : false;
    }

    public function getCorporativo() {
        return $this->corporativo;
    }

    /**
     *
     * @param array $orgaos
     */
    public function plotarBlocoOrgao(array $orgaos)
    {
        $orgaoNumeracao = filter_var($_REQUEST['orgaoGestor'], FILTER_SANITIZE_NUMBER_INT);
        if ($orgaos == null) {
            return;
        }
        $this->getTemplate()->ORGAO_VALUE = null;
        $this->getTemplate()->ORGAO_TEXT = "Selecione o Órgão ...";
        $this->getTemplate()->ORGAO_SELECTED = "selected";
        $this->getTemplate()->clear("ORGAO_SELECTED");
        $this->getTemplate()->block("BLOCO_ORGAO");

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
     * @param array $processos
     */
    public function plotarBlocoProcesso(array $processos)
    {
        $ultCodComiss = 0;
        $this->getTemplate()->TEXTO_PROCESSO_SELECIONE = "Selecione o processo";
        

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
            if ($processo == null) {
                $this->getTemplate()->PROCESSO_SELECTED = "selected";
            } else {
                // Caso esta não seja a opção atual, limpamos o valor da variável SELECTED
                $this->getTemplate()->clear("PROCESSO_SELECTED");
            }
            $this->getTemplate()->block("BLOCK_OPTION");
        }
        $this->getTemplate()->block("BLOCK_PROCESSO");
    }

    /**
     *
     * @param array $anos
     */
    public function plotarBlocoAno(array $anos)
    {
        $gerarNumeracao = filter_input(INPUT_POST, 'valorAno');
        $this->getTemplate()->ANO_VALUE = null;
        $this->getTemplate()->ANO_TEXT = "Selecione o Ano...";
        $this->getTemplate()->ANO_SELECTED = "selected";
        $this->getTemplate()->clear("ANO_SELECTED");
        $this->getTemplate()->block("BLOCO_ANO");
        foreach ($anos as $text) {
            $this->getTemplate()->ANO_VALUE = $text;
            $this->getTemplate()->ANO_TEXT = $text;

            // Vendo se a opção atual deve ter o atributo "selected"

            if ($gerarNumeracao != null) {
                if ($text == $gerarNumeracao) {
                    $this->getTemplate()->ANO_SELECTED = "selected";
                }
            }

            if ($gerarNumeracao == $text) {
                $this->getTemplate()->ANO_SELECTED = "selected";
            } else {
                $this->getTemplate()->clear("ANO_SELECTED");
            }

            $this->getTemplate()->block("BLOCO_ANO");
        }
    }
}

class CadAtaRegistroPrecoCaronaAtaExterna extends ProgramaAbstrato
{
    /**
     */
    private function atualizaProcessos()
    {
        $orgao = filter_var($_POST['orgaoGestor'], FILTER_SANITIZE_NUMBER_INT);
        $ano = filter_var($_POST['valorAno'], FILTER_SANITIZE_NUMBER_INT);
        $anos = HelperPitang::carregarAno();

        $processos = $this->getUI()
            ->getAdaptacao()
            ->getNegocio()
            ->getDados()
            ->consultarProcessoDoOrgaoNoAno($orgao, $ano);
        //var_dump($processos);exit;
        $this->getUI()->plotarBlocoProcesso($processos);
        $this->getUI()->plotarBlocoAno($anos);
        
        $orgaos = $this->getUI()
            ->getAdaptacao()
            ->getNegocio()
            ->getDados()
            ->consultarOrgaoComNumeracaoGerada($this->getUI()->getCorporativo());
        
        $this->getUI()->plotarBlocoOrgao($orgaos);
    }

    /**
     */
    private function processSelecionar()
    {
        if (! $this->getUI()
            ->getAdaptacao()
            ->getNegocio()
            ->validarFormulario()) {
            $this->getUI()->mensagemSistema(implode(',', $_SESSION['mensagemFeedback']), 0, 1);
            $this->proccessPrincipal();
            return;
        }

        $orgao = filter_var($_POST['orgaoGestor'], FILTER_SANITIZE_NUMBER_INT);
        $ano = filter_var($_POST['valorAno'], FILTER_SANITIZE_NUMBER_INT);
        $processo = filter_var($_POST['GerarNumeracaoProcesso'], FILTER_SANITIZE_NUMBER_INT);

        $uri = "CadAtaRegistroPrecoCaronaAtaExternaListar.php?ano=$ano&processo=$processo&orgao=$orgao";
        header('Location: ' . $uri);
        exit();
    }

    /**
     */
    private function proccessPrincipal()
    {
        $anos = HelperPitang::carregarAno();
        $this->getUI()->plotarBlocoAno($anos);

        $orgaos = $this->getUI()
            ->getAdaptacao()
            ->getNegocio()
            ->getDados()
            ->consultarOrgaoComNumeracaoGerada($this->getUI()->getCorporativo());
        $this->getUI()->plotarBlocoOrgao($orgaos);

        $this->getUI()->plotarBlocoProcesso(array());
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see ProgramaAbstrato::frontController()
     */
    protected function frontController()
    {
        $acao = filter_var($_REQUEST['Botao'], FILTER_SANITIZE_STRING);

        switch ($acao) {
            case 'Selecionar':
                $this->processSelecionar();
                break;
            case 'atualizaProcessos':
                $this->atualizaProcessos();
                break;
            case 'Principal':
            default:
                $this->proccessPrincipal();
                break;
        }
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see ProgramaAbstrato::configuracao()
     */
    protected function configuracao()
    {
        $this->setUI(new RegistroPreco_UI_CadAtaRegistroPrecoCaronaAtaExterna());
    }
}

ProgramaAbstrato::iniciar(new CadAtaRegistroPrecoCaronaAtaExterna());
