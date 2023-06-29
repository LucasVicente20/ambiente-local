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
 *
 * @version   GIT:  EMPREL-SAD-PORTAL-COMPRAS-REGISTRO-PRECO-BL-FUNC-20160614-0945
 */

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
class RegistroPreco_Dados_CadAtaRegistroPrecoInternaManterEspecial extends Dados_Abstrata
{

   
    /**
     * Consulta SQL para listar todos os processos que estão na fase 13 = HOMOLOGACAO
     * e que são do tipo registro de preço (flicporegp = 'S').
     *
     * @param int $ano
     *                   [description]
     * @param int $orgao
     *                   [description]
     *
     * @return string [description]
     */
    private function sqlProcessoRegistroPrecoEmHomologacao($ano, $orgao)
    {
        $licHomologacao = 13;

        $sql = "
            SELECT
                DISTINCT l.clicpoproc,
                l.alicpoanop,
                l.ccomlicodi,
                l.cgrempcodi,
                l.corglicodi,
                B.ecomlidesc,
                e.cfasescodi
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
						AND l.cgrempcodi = F.cgrempcodi
						AND l.ccomlicodi = F.ccomlicodi
						AND l.corglicodi = F.corglicodi
            WHERE  l.alicpoanop = %d    ";

        if($_SESSION['_fperficorp_'] != 'S') {
            $sql .= " AND l.cgrempcodi = ".$_SESSION['_cgrempcodi_'];
        }

        $sql .= " AND l.flicporegp LIKE 'S'
                AND l.corglicodi = %d
                AND e.cfasescodi = %d
            ORDER BY
                B.ecomlidesc ASC,
                l.alicpoanop DESC,
                l.clicpoproc DESC
        ";


        return sprintf($sql, $ano, $orgao, $licHomologacao);
    }

    /**
     * [consultarProcessoDoOrgaoNoAno description]
     *
     * @param [type] $orgao
     *            [description]
     * @param [type] $ano
     *            [description]
     * @return [type] [description]
     */
    public function consultarProcessoDoOrgaoNoAno($orgao, $ano)
    {
        $res = $this->executarSQL($this->sqlProcessoRegistroPrecoEmHomologacao($orgao, $ano));

        $this->hasError($res);

        return $res;
    }

    public function consultarOrgaoComNumeracaoGerada()
    {
        return ClaDatabasePostgresql::executarSQL($this->sqlOrgaoAtaGerada());
    }

    /**
     * Consulta o órgão de acordo com o usuário logado
     *
     * @return string
     */
    private function sqlOrgaoAtaGerada()
    {
        $cgrempcodi = $_SESSION['_cgrempcodi_'];
        $cusupocodi = $_SESSION['_cusupocodi_'];

        $sql = "
            SELECT		ol.corglicodi,
						ol.eorglidesc
			FROM		sfpc.tborgaolicitante ol
			ORDER BY	ol.eorglidesc ASC
        ";

        return $sql;
    }

    /**
     * Pesquisa o processo de registro de preco na situacao de homologacao.
     *
     * @param int $ano
     *                 $param integer $orgao
     *
     * @return array [description]
     */
    public function pesquisarProcessoRegistroPrecoEmHomologacao($ano, $orgao)
    {
        $resultados = array();
        $processo = null;

        $sql = $this->sqlProcessoRegistroPrecoEmHomologacao($ano, $orgao);

        $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $sql);

        while ($resultado->fetchInto($processo, DB_FETCHMODE_OBJECT)) {
            $resultados[] = $processo;
        }

        return $resultados;
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
class RegistroPreco_Negocio_CadAtaRegistroPrecoInternaManterEspecial extends Negocio_Abstrata
{

    /**
     * Filtra processo por ano selecionada.
     *
     * @return [type] [description]
     */
    public function filtrarProcessoPorAnoSelecionado()
    {
        $anos = HelperPitang::carregarAno();
        $gerarNumeracaoAno = filter_var($_POST['GerarNumeracaoAno'], FILTER_SANITIZE_NUMBER_INT) ? (int) $_POST['GerarNumeracaoAno'] : 0;
        $anoSelecionado = $anos[$gerarNumeracaoAno];
        $orgaoGestor = filter_var($_POST['orgaoGestor'], FILTER_SANITIZE_NUMBER_INT) ? (int) $_POST['orgaoGestor'] : 0;
        if ($orgaoGestor == 0) {
            return false;
        }
        $processos = $this->getDados()->pesquisarProcessoRegistroPrecoEmHomologacao($anoSelecionado, $orgaoGestor);

        return $processos;
    }

    public function getDados()
    {
        $this->setDados(new RegistroPreco_Dados_CadAtaRegistroPrecoInternaManterEspecial());
        return parent::getDados();
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
class RegistroPreco_Adaptacao_CadAtaRegistroPrecoInternaManterEspecial extends Adaptacao_Abstrata
{

    public function __construct()
    {
    }

    public function getNegocio()
    {
        $this->setNegocio(new RegistroPreco_Negocio_CadAtaRegistroPrecoInternaManterEspecial());
        return parent::getNegocio();
    }
    

    /**
     * [plotarBlocoProcesso description].
     *
     * @param RegistroPreco_GUI_CadAtaRegistroPrecoGerarNumeracao $gui
     *                                                                       [description]
     * @param [type]                                              $processos
     *                                                                       [description]
     *
     * @return [type] [description]
     */
    public static function plotarBlocoProcesso(RegistroPreco_UI_CadAtaRegistroPrecoGerarNumeracao $gui, $processos)
    {
    }

    public function existeAtaRegistroPrecoInterna($processo)
    {
        $clicpoproc = new Negocio_ValorObjeto_Clicpoproc($processo->clicpoproc);
        $alicpoanop = new Negocio_ValorObjeto_Alicpoanop($processo->alicpoanop);
        $cgrempcodi = new Negocio_ValorObjeto_Cgrempcodi($processo->cgrempcodi);
        $ccomlicodi = new Negocio_ValorObjeto_Ccomlicodi($processo->ccomlicodi);
        $corglicodi = new Negocio_ValorObjeto_Corglicodi($processo->corglicodi);

        $repositorio = new Negocio_Repositorio_AtaRegistroPrecoInterna();
        $res = $repositorio->procurarPorProcessoLicitatorio($clicpoproc, $alicpoanop, $cgrempcodi, $ccomlicodi, $corglicodi);

        $entidade = current($res);

        return ($entidade->carpnosequ > 0) ? true : false;
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
class RegistroPreco_UI_CadAtaRegistroPrecoInternaManterEspecial extends UI_Abstrata
{

    /**
     * [__construct description]
     */
    public function __construct()
    {
        $this->setTemplate(new TemplatePaginaPadrao("templates/CadAtaRegistroPrecoInternaManterEspecial.html", "Registro de Preço > Ata Interna > Manter Especial"));
    }

    public function getAdaptacao()
    {
        $this->setAdaptacao(new RegistroPreco_Adaptacao_CadAtaRegistroPrecoInternaManterEspecial());
        return parent::getAdaptacao();
    }

    /**
     * [processSelecionar description]
     *
     * @return [type] [description]
     */
    public function processSelecionar()
    {
        
        $arrValidacao = $this->validaFormulario();

        if ($arrValidacao) {            
            $this->proccessPrincipal();
            return;
        }else{            
            $anoAtual = filter_input(INPUT_POST, 'GerarNumeracaoAno', FILTER_SANITIZE_NUMBER_INT);
            $processoAtual = filter_var($_POST['GerarNumeracaoProcess'], FILTER_SANITIZE_NUMBER_INT);
            $orgao = $_POST['orgaoGestor'];
            $anos = HelperPitang::carregarAno();
            $uri = 'CadAtaRegistroPrecoInternaManterEspecialAtas.php?processo='.$processoAtual.'&ano='.$anos[$anoAtual] . '&orgao=' . $orgao;
            header('Location: '.$uri);
            exit();
        }

    }

    /**
     * [validaFormulario description].
     *
     * @return [type] [description]
     */
    private function validaFormulario()
    {
        
        $anoAtual = filter_input(INPUT_POST, 'GerarNumeracaoAno', FILTER_SANITIZE_NUMBER_INT);
        $processoAtual = filter_var($_POST['GerarNumeracaoProcess'], FILTER_SANITIZE_NUMBER_INT);
        $error = false;
        if (($processoAtual == null || $processoAtual == '') || ($anoAtual == null || $anoAtual == '') ) {
            $_SESSION['colecaoMensagemErro'][] = 'Todos os campos são obrigatórios';
            $error = true;
            
        }
        

        return $error;
    }

    public function proccessPrincipal()
    {

        // Se a chamada for GET, então
        // pressume-se que é a primeira vez na tela, então limpa arquivos
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            unset($_SESSION['Arquivos_Upload']);
        }
        unset($_SESSION['post_itens_armazenar_tela']);
        unset($_SESSION['post_itens_armazenar_tela_normais']);

        // $anos = HelperPitang::carregarAno();
        // $this->plotarBlocoAno($anos);

       $orgaos = $this->getAdaptacao()
            ->getNegocio()
            ->getDados()
            ->consultarOrgaoComNumeracaoGerada();

        $this->plotarBlocoOrgao($orgaos);
        $this->plotarBlocoAno(HelperPitang::carregarAno());
        $processos = $this->getAdaptacao()
            ->getNegocio()
            ->filtrarProcessoPorAnoSelecionado();
        $this->plotarBlocoProcesso($processos);

        if (isset($_SESSION['mensagemFeedback'])) {
            $this->getTemplate()->MENSAGEM_ERRO = ExibeMensStr($_SESSION['mensagemFeedback'], 1, 0);
            $this->getTemplate()->block('BLOCO_ERRO', true);
            unset($_SESSION['mensagemFeedback']);
        }
    }

    public function alterarAtual()
    {
        $processos = $this->getAdaptacao()
            ->getNegocio()
            ->filtrarProcessoPorAnoSelecionado();
        $this->plotarBlocoProcesso($processos);
        // está aqui nessa classe: RegistroPreco_UI_CadAtaRegistroPrecoInternaManterEspecial

        $orgaos = $this->getAdaptacao()->getNegocio()->getDados()->consultarOrgaoComNumeracaoGerada();
        $this->plotarBlocoOrgao($orgaos);

        $anos = HelperPitang::carregarAno();
        $this->plotarBlocoAno($anos);
    }

    /**
     * [plotarBlocoAno description].
     *
     * @param [type] $anos
     *                     [description]
     *
     * @return [type] [description]
     */
    public function plotarBlocoAno($anos)
    {
        if (!is_array($anos)) {
            throw new Exception('Error Processing Request', 1);
        }

        $gerarNumeracao = isset($_POST['GerarNumeracaoAno']) ? filter_var($_POST['GerarNumeracaoAno'], FILTER_SANITIZE_NUMBER_INT) : -1;
        $this->getTemplate()->ANO_VALUE = '-1';
        $this->getTemplate()->ANO_TEXT = 'Selecione...';
        $this->getTemplate()->clear('ANO_SELECTED');
        $this->getTemplate()->block('BLOCO_ANO');

        foreach ($anos as $value => $text) {
            $this->getTemplate()->ANO_VALUE = $value;
            $this->getTemplate()->ANO_TEXT = $text;

            $anoAtual = date('Y');

            // Vendo se a opção atual deve ter o atributo "selected"
            if ($gerarNumeracao == $value || $text == $anoAtual) {
                $this->getTemplate()->ANO_SELECTED = 'selected';
            } else {
                // Caso esta não seja a opção atual, limpamos o valor da variável SELECTED
                $this->getTemplate()->clear('ANO_SELECTED');
            }
            $this->getTemplate()->block('BLOCO_ANO');
        }
    }

    /**
     * [plotarBlocoOrgao description]
     *
     * @param GUI $gui
     *            [description]
     * @param [type] $orgaos
     *            [description]
     * @return [type] [description]
     */
    public function plotarBlocoOrgao(array $orgaos)
    {
        $orgaoNumeracao = filter_var($_POST['orgaoGestor'], FILTER_VALIDATE_INT);

        $this->getTemplate()->ORGAO_VALUE       = -1;
        $this->getTemplate()->ORGAO_TEXT        = 'Selecione o órgão';
        $this->getTemplate()->ORGAO_SELECTED    = 'selected';
        $this->getTemplate()->clear('ORGAO_SELECTED');
        $this->getTemplate()->block('BLOCO_ORGAO');

        foreach ($orgaos as $orgao) {
            $this->getTemplate()->ORGAO_VALUE   = $orgao->corglicodi;
            $this->getTemplate()->ORGAO_TEXT    = $orgao->eorglidesc;

            // Vendo se a opção atual deve ter o atributo "selected"
            if ($orgaoNumeracao == $orgao->corglicodi) {
                $this->getTemplate()->ORGAO_SELECTED = 'selected';
            } else {
                $this->getTemplate()->clear('ORGAO_SELECTED');
            }

            $this->getTemplate()->block('BLOCO_ORGAO');
        }
    }

    /**
     * [plotarBlocoProcesso description]
     *
     * @param GUI $gui
     *            [description]
     * @param [type] $processos
     *            [description]
     * @return [type] [description]
     */
    public function plotarBlocoProcesso($processos)
    {
        if ($processos == null) {
            return;
        }

        $ultCodComiss = 0;
        $this->getTemplate()->TEXTO_PROCESSO_SELECIONE = 'Selecione...';
        $this->getTemplate()->PROCESSO_VALUE = -1;
        $this->getTemplate()->PROCESSO_SELECTED = 'selected';

        foreach ($processos as $processo) {
            if ($processo != null) {
                if ($ultCodComiss == 0) {
                    $ultCodComiss = $processo->ccomlicodi;
                    $this->getTemplate()->OPTGROUP_INICIO = '<optgroup label="'.$processo->ecomlidesc.'">';
                } elseif ($ultCodComiss != $processo->ccomlicodi) {
                    $this->getTemplate()->block('BLOCK_PROCESSO');
                    $this->getTemplate()->OPTGROUP_FINAL = '</optgroup>';
                    $ultCodComiss = $processo->ccomlicodi;
                    $this->getTemplate()->OPTGROUP_INICIO = '<optgroup label="'.$processo->ecomlidesc.'">';
                }
                $numeroProcesso = str_pad($processo->clicpoproc, 4, '0', STR_PAD_LEFT);
                $textoProcesso = $numeroProcesso.'/'.$processo->alicpoanop;
                if ($this->getAdaptacao()->existeAtaRegistroPrecoInterna($processo)) {
                    //$textoProcesso .= ' * ';
                }
                $this->getTemplate()->TEXTO_PROCESSO = $textoProcesso;
                $formataId = implode('-', array(
                    $processo->clicpoproc,
                    $processo->alicpoanop,
                    $processo->cgrempcodi,
                    $processo->ccomlicodi,
                    $processo->corglicodi,
                ));
                $this->getTemplate()->PROCESSO_VALUE = $formataId;
            }

            // Vendo se a opção atual deve ter o atributo "selected"CadAtaRegistroPrecoInternaManter
            if ($processo == null) {
                $this->getTemplate()->PROCESSO_SELECTED = 'selected';
            } else {
                // Caso esta não seja a opção atual, limpamos o valor da variável SELECTED
                $this->getTemplate()->clear('PROCESSO_SELECTED');
            }
            $this->getTemplate()->block('BLOCK_OPTION');
        }
        $this->getTemplate()->block('BLOCK_PROCESSO'); 
    }
}

/**
 * [$app description]
 *
 * @var Negocio
 */
$app = new RegistroPreco_UI_CadAtaRegistroPrecoInternaManterEspecial();

$acao = filter_input(INPUT_POST, 'Botao', FILTER_SANITIZE_STRING);

switch ($acao) {
    case 'Selecionar':
        $app->processSelecionar();
        break;
    case 'AlterarProcesso':
        $app->alterarAtual();
        break;
    case 'Principal':
    default:
        $app->proccessPrincipal();
        break;
}

echo $app->getTemplate()->show();
