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
 * @category  Pitang Core
 *
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 *
 * @version   Git: $Id:$
 * ----------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     04/06/2019
 * Objetivo: Tarefa Redmine 217955
 * ----------------------------------------------------------------------------------------
 */

 // 220038--

session_start();

if (! @require_once dirname(__FILE__) . '/funcoes.php') {
    throw new Exception('Error Processing Request - funcoes', 1);
}

if (! @require_once dirname(__FILE__) . '/compras/funcoesCompras.php') {
    throw new Exception('Error Processing Request - funcoes Compras', 1);
}

if (! @require_once dirname(__FILE__) . '/registropreco/funcoesRP.php') {
    throw new Exception('Error Processing Request - funcoes Compras', 1);
}

if (! @require_once dirname(__FILE__) . '/estoques/funcoesEstoques.php') {
    throw new Exception('Error Processing Request - funcoes Estoques', 1);
}

if (! @require_once dirname(__FILE__) . '/fornecedores/funcoesFornecedores.php') {
    throw new Exception('Error Processing Request - funcoes Fornecedores', 1);
}

if (! @require_once dirname(__FILE__) . '/licitacoes/funcoesLicitacoes.php') {
    throw new Exception('Error Processing Request - funcoes Licitações', 1);
}

if (! @require_once dirname(__FILE__) . '/materiais/funcoesMateriais.php') {
    throw new Exception('Error Processing Request - funcoes Materiais', 1);
}

if (! @require_once dirname(__FILE__) . '/vendor/pitang/src/Helper_RegistroPreco.php') {
    throw new Exception('Error Processing Request - Helper RegistroPreco', 1);
}

require_once dirname(__FILE__) . "/vendor/autoload.php";

/**
 */
class MensagemVO
{

    private $mensagem;

    /**
     * [__construct description]
     *
     * @param [type] $mensagem
     *            [description]
     */
    public function __construct($mensagem = null)
    {
        $this->mensagem = $mensagem;
    }

    /**
     * [get description]
     *
     * @return [type] [description]
     */
    public function get()
    {
        return $this->mensagem;
    }

    /**
     * [__toString description]
     *
     * @return string [description]
     */
    public function __toString()
    {
        return 'Mensagem : ' . $this->mensagem;
    }
}

/**
 */
interface IntefaceGraficaUsuario
{

    /**
     * Seta template
     *
     * @param Template $template
     *
     */
    public function setTemplate(Template $template);

    /**
     * Retorna o template setado
     *
     * @return Template o template que será usado pelo o programa
     */
    public function getTemplate();

    /**
     * [blockErro description]
     *
     * @param [type] $mensagem
     *            [description]
     * @return [type] [description]
     */
    public function blockErro($mensagem);
}

/**
 */
class BaseIntefaceGraficaUsuario implements IntefaceGraficaUsuario
{

    /**
     * Armazena o objeto do tipo Template que será usado pelo o programa que será desenvolvido
     *
     * @var Template
     */
    private $template;

    private $adaptacao;

    /**
     * [setTemplate description]
     *
     * @param Template $template
     *            [description]
     */
    public function setTemplate(Template $template)
    {
        $this->template = $template;
    }

    /**
     * [getTemplate description]
     *
     * @return [type] [description]
     */
    public function getTemplate()
    {
        return $this->template;
    }

    public function setAdaptacao(AbstractAdaptacao $adaptacao)
    {
        $this->adaptacao = $adaptacao;
    }

    /**
     * [getTemplate description]
     *
     * @return [type] [description]
     */
    public function getAdaptacao()
    {
        return $this->adaptacao;
    }

    /**
     * [blockErro description]
     *
     * @param MensagemVO $mensagem
     *            [description]
     * @return [type] [description]
     */
    public function blockErro($mensagem, $tipo = 1)
    {
        if (isset($_SESSION['mensagemFeedback']) || ! empty($mensagem)) {
            $this->getTemplate()->MENSAGEM_ERRO = empty($mensagem) ? $_SESSION['mensagemFeedback'] : ExibeMensStr($mensagem, $tipo, 0);
            $this->getTemplate()->block('BLOCO_ERRO', true);
            unset($_SESSION['mensagemFeedback']);
        }
    }
}

interface IAdaptacao
{

    public function setTemplate($negocio);

    public function getTemplate();

    public function setNegocio($negocio);

    public function getNegocio();
}

class AbstractAdaptacao implements IAdaptacao
{

    private $template;

    /**
     *
     * @var Negocio
     */
    private $negocio;

    /**
     *
     * @var IntefaceGraficaUsuario
     */
    private $gui;

    /**
     *
     * @return IntefaceGraficaUsuario
     */
    public function getGui()
    {
        return $this->gui;
    }

    /**
     *
     * @param IntefaceGraficaUsuario $gui
     */
    public function setGui(IntefaceGraficaUsuario $gui)
    {
        $this->gui = $gui;

        return $this;
    }

    public function setTemplate($template)
    {
        $this->template = $template;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function setNegocio($negocio)
    {
        $this->negocio = $negocio;
    }

    public function getNegocio()
    {
        return $this->negocio;
    }
}

/**
 */
interface InterfaceNegocio
{

    public function setDados($dados);

    public function getDados();
}

/**
 */
class BaseNegocio implements InterfaceNegocio
{

    private $dados;

    /**
     * [__construct description]
     */
    public function __construct()
    {
        $this->setDados(new Basedados());
    }

    /**
     * [setGUI description]
     *
     * @param BaseIntefaceGraficaUsuario $gui
     *            [description]
     */
    public function setDados($dados)
    {
        $this->dados = $dados;
    }

    /**
     * [getGUI description]
     *
     * @return [type] [description]
     */
    public function getDados()
    {
        return $this->dados;
    }
}

/**
 * Interface comum para definição do estilo do formulário.
 *
 * @author José Almir <jose.almir@pitang.com>
 */
interface IEstiloFormulario
{

    public function setCampos(LinhaFormulario $campos);

    public function setBotoes(ElementoLinha $botoes);

    public function setForm(Element $form);

    public function renderizar();
}

/**
 * Interface para implementar um novo programa.
 */
interface IPrograma
{

    /**
     * Implementação do fluxo do programa.
     *
     * @return string html que deverá ser renderizado
     */
    public static function frontController();
}

/**
 * Classe abstrata para a implementação de formulários HTML.
 *
 * @author José Almir <jose.almir@pitang.com>
 */
abstract class Formulario
{

    private $nome;

    private $metodo;

    private $acao;

    private $atributosExtras;

    private $estilo;

    private $camposDoFormulario;

    private $botoesDoFormulario;

    /**
     * Construtor da classe.
     *
     * @param string $nome
     *            valor do atributo name
     * @param IEstiloFormulario $estilo
     *            forma de renderização do formulário
     * @param string $metodo
     *            valor do atributo method
     * @param string $acao
     *            valor do atributo action
     * @param array $atributosExtras
     *            onde chave do array é nome do atributo e o valor dessa chave o valor do atributo
     */
    public function __construct($nome, IEstiloFormulario $estilo, $metodo = 'post', $acao = '', $atributosExtras = array())
    {
        $this->nome = $nome;
        $this->estilo = $estilo;
        $this->metodo = $metodo;
        $this->acao = $acao;
        $this->atributosExtras = $atributosExtras;

        $this->configurar();
    }

    /**
     * Realiza configurações para a renderização do formulário.
     */
    private function configurar()
    {
        $this->camposDoFormulario = $this->campos();
        $this->botoesDoFormulario = $this->botoes();
    }

    /**
     * Renderiza o formulário.
     *
     * @return string
     */
    public function renderizar()
    {
        $this->estilo->setCampos($this->camposDoFormulario);
        $this->estilo->setBotoes($this->botoesDoFormulario);
        $this->estilo->setForm($this->tagForm());

        return $this->estilo->renderizar();
    }

    /**
     * Monta a tag form.
     *
     * @return Element
     */
    private function tagForm()
    {
        $form = new Element('form');
        $form->set('name', $this->nome);
        $form->set('method', $this->metodo);
        $form->set('action', $this->acao);
        $form->set($this->atributosExtras);

        return $form;
    }

    /**
     * Popula todos os campos do formulário.
     *
     * @param array $valoresCampos
     *            onde a chave é o name do campo
     */
    public function popular($valoresCampos)
    {
        $camposEmLinhas = $this->campos();
        $linhas = $camposEmLinhas->getLinhas();

        $elementos = array();
        foreach ($linhas as $linha) {
            $elementos = array_merge($elementos, $linha->getElementos());
        }

        foreach ($elementos as $elemento) {
            $campo = $elemento['campo'];

            if (isset($valoresCampos[$campo->get('name')])) {
                $this->popularCampo($campo, $valoresCampos[$campo->get('name')]);
            }
        }

        $this->camposDoFormulario = $camposEmLinhas;
    }

    /**
     * Popular o campo informado.
     *
     * @param Element $campo
     */
    private function popularCampo(Element $campo, $valor)
    {
        if ($campo->type == 'input') {
            $tipo = $campo->get('type');

            if ($tipo == 'text') {
                $campo->set('value', $valor);
            } elseif ($tipo == 'checkbox' || $campo->get('value') == $valor) {
                $campo->set('checked', 'checked');
            }
        } elseif ($campo->type == 'select') {
            $strPesquisa = 'value="' . $valor . '"';
            $strSubstituicao = $strPesquisa . ' selected';
            $strOptions = str_replace($strPesquisa, $strSubstituicao, $campo->get('text'));

            $campo->set('text', $strOptions);
        }
    }

    /**
     * Deve implementar os botões do formulário.
     *
     * @return ElementoLinha
     */
    abstract public function botoes();

    /**
     * Deve implementar os campos do formulário.
     *
     * Utilizar o objeto ElementoLinha para adicionar objetos Element a uma linha.
     * Após adicionar objetos Element a uma linha, utilizar o objeto LinhaFormulario para criar uma linha do
     * formulário, invocando o método 'adicionarLinha' e passando como argumento um objeto ElementoLinha.
     *
     * @see ElementoLinha, LinhaFormulario
     *
     * @return LinhaFormulario
     */
    abstract public function campos();

    public function setNome($nome)
    {
        $this->nome = $nome;
    }

    public function getNome()
    {
        return $this->nome;
    }

    public function setMetodo($metodo)
    {
        $this->metodo = $metodo;
    }

    public function getMetodo()
    {
        return $this->metodo;
    }

    public function setAcao($acao)
    {
        $this->acao = $acao;
    }

    public function getAcao()
    {
        return $this->acao;
    }

    public function setAtributosExtras($atributosExtras)
    {
        $this->atributosExtras = $atributosExtras;
    }

    public function getAtributosExtras()
    {
        return $this->atributosExtras;
    }

    public function setEstilo(IEstiloFormulario $estilo)
    {
        $this->estilo = $estilo;
    }

    public function getEstilo()
    {
        return $this->estilo;
    }
}

/**
 */
abstract class AbstractPdfRegistroPreco
{

    private $pdf;

    /**
     * [__construct description].
     *
     * @param string $orientacao
     *            [description]
     * @param string $unidadeMedida
     *            [description]
     * @param string $formato
     *            [description]
     */
    public function __construct($orientacao = 'L', $unidadeMedida = 'mm', $formato = 'A4')
    {
        $this->configurarCabecalhoRodape();
        $this->pdf = new PDF($orientacao, $unidadeMedida, $formato);
        $this->configurarPdf();
    }

    /**
     * [getInstance description].
     *
     * @return [type] [description]
     */
    public function getInstance()
    {
        return $this->pdf;
    }

    /**
     * [configurarCabecalhoRodape description].
     *
     * @return [type] [description]
     */
    private function configurarCabecalhoRodape()
    {
        $GLOBALS['TituloRelatorio'] = $this->getTitulo();
        CabecalhoRodapePaisagem();
    }

    /**
     * [configurarPdf description].
     *
     * @return [type] [description]
     */
    private function configurarPdf()
    {
        $this->pdf->AliasNbPages();
        $this->pdf->SetFillColor(255, 255, 255);
        $this->pdf->AddPage();
        $this->pdf->SetFont('Arial', '', 7);
    }

    /**
     * Invoca os métodos da instância do objeto PDF presente na classe.
     *
     * @see http://php.net/manual/pt_BR/language.oop5.overloading.php#language.oop5.overloading.methods
     *
     * @param string $metodo
     * @param array $argumentos
     */
    public function __call($metodo, $argumentos = array())
    {
        if (empty($argumentos)) {
            return $this->pdf->$metodo();
        } else {
            $strValores = '';
            $valores = array_values($argumentos);

            foreach ($valores as $argumento) {
                $strValores .= "'" . $argumento . "' ,";
            }

            $strValores = substr($strValores, 0, - 2);

            $eval = 'return $this->pdf->' . $metodo . '(' . $strValores . ');';

            return eval($eval);
        }
    }

    /**
     * Deve retornar o título do relatório.
     *
     * @return string Título do relatório
     */
    abstract public function getTitulo();

    /**
     * Deve implementar a visualização do relatório.
     */
    abstract public function gerarRelatorio();
}

/**
 * Implementação da interface IEstiloFormulario.
 */
class EstiloBootstrap implements IEstiloFormulario
{

    private $campos;

    private $botoes;

    private $form;

    const NUMERO_COLUNAS = 12;

    public function setCampos(LinhaFormulario $campos)
    {
        $this->campos = $campos;
    }

    public function setBotoes(ElementoLinha $botoes)
    {
        $this->botoes = $botoes;
    }

    public function setForm(Element $form)
    {
        $this->form = $form;
    }

    /**
     * Monta o formulário com o estilo do bootstrap.
     *
     * @see IEstiloFormulario::renderizar()
     */
    public function renderizar()
    {
        $botoes = $this->montarBotoes();
        $campos = $this->montarCampos();
        $divWell = $this->tagDivWell();
        $article = $this->tagArticle();

        $divWell->inject($campos);
        $divWell->inject(new Element('hr'));
        $divWell->inject($botoes);
        $article->inject($divWell);
        $this->form->inject($article);

        return $this->form->build();
    }

    /**
     * Monta a tag article.
     *
     * @return Element
     */
    private function tagArticle()
    {
        $article = new Element('article');
        $article->set('class', 'article');

        return $article;
    }

    /**
     * Monta a tag div com a class well.
     *
     * @return Element
     */
    private function tagDivWell()
    {
        $divWell = new Element('div');
        $divWell->set('class', 'well');

        return $divWell;
    }

    /**
     * Monta a tag div dos botões.
     *
     * @return Element
     */
    private function tagDivBotoes()
    {
        $divBotoes = new Element('div');
        $divBotoes->set('class', 'btn-toolbar pagination-right');

        return $divBotoes;
    }

    /**
     * Monta os botões.
     *
     * @return Element
     */
    private function montarBotoes()
    {
        $divBotoes = $this->tagDivBotoes();
        $elementosDaLinha = $this->botoes;
        $botoes = $elementosDaLinha->getElementos();

        foreach ($botoes as $elemento) {
            $divBotoes->inject($elemento['campo']);
        }

        return $divBotoes;
    }

    private function tagDivCampos()
    {
        $divCampos = new Element('div');
        $divCampos->set('class', 'row-fluid');

        return $divCampos;
    }

    private function montarCampos()
    {
        $divAgrupaCampos = new Element('div');
        $linhasDeCampos = $this->campos;
        $campos = $linhasDeCampos->getLinhas();

        foreach ($campos as $camposDaLinha) {
            $numeroDeLoop = 1;
            $numeroColunas = (! is_null($camposDaLinha->getNumeroColunasLinha())) ? $camposDaLinha->getNumeroColunasLinha() : self::NUMERO_COLUNAS;
            $totalDeElementos = $camposDaLinha->totalDeElementos();
            $larguraSpanCampo = floor($numeroColunas / $totalDeElementos);
            $divCampos = $this->tagDivCampos();
            $elementosDaLinha = $camposDaLinha->getElementos();

            foreach ($elementosDaLinha as $elemento) {
                if ($numeroDeLoop == $totalDeElementos) {
                    $restoDaDivisao = $numeroColunas % $totalDeElementos;
                    $larguraSpanCampo = $larguraSpanCampo + $restoDaDivisao;
                }

                $campoAjustado = $this->ajustarCampo($elemento, $larguraSpanCampo);
                $divCampos->inject($campoAjustado);
                ++ $numeroDeLoop;
            }

            $divAgrupaCampos->inject($divCampos);
        }

        return $divAgrupaCampos;
    }

    private function ajustarCampo($elemento, $larguraSpanCampo)
    {
        $divSpan = new Element('div');
        $divSpan->set('class', 'span' . $larguraSpanCampo);

        $divSpan->inject($elemento['label']);
        $divSpan->inject($elemento['campo']);

        return $divSpan;
    }
}

/**
 * Representa objetos Element em uma linha do formulário.
 *
 * @author José Almir <jose.almir@pitang.com>
 */
class ElementoLinha
{

    private $elementos = array();

    private $numeroColunasNaLinha;

    public function __construct($numeroColunasNaLinha = null)
    {
        $this->numeroColunasNaLinha = $numeroColunasNaLinha;
    }

    /**
     * Adiciona objetos Element.
     *
     * @param Element $elemento
     */
    public function adicionarElemento(Element $elemento, Element $label = null, $indice = null)
    {
        if (! is_null($indice) && ! isset($this->elementos[$indice])) {
            throw new OutOfBoundsException("Não existe elemento na posição $indice");
        }

        if (! is_null($indice)) {
            $this->elementos[$indice] = array(
                'campo' => $elemento,
                'label' => $label
            );
        } else {
            $this->elementos[] = array(
                'campo' => $elemento,
                'label' => $label
            );
        }
    }

    /**
     * Retorna um array com todos os objeto existentes na linha.
     *
     * @return array
     */
    public function getElementos()
    {
        return $this->elementos;
    }

    /**
     * Retorna o total de elementos na linha.
     *
     * @return number
     */
    public function totalDeElementos()
    {
        return count($this->elementos);
    }

    /**
     * Remove um elemento no índice informado.
     *
     * @param int $indice
     *
     * @throws OutOfBoundsException caso a posição informada esteja fora dos limites
     */
    public function removerElemento($indice)
    {
        if (! isset($this->elementos[$indice])) {
            throw new OutOfBoundsException("Não existe elemento na posição $indice");
        }

        unset($this->elementos[$indice]);
        $this->elementos = array_values($this->elementos);
    }

    /**
     * Retorna um elemento na posição informada pelo parâmetro $indice.
     *
     * @param int $indice
     *
     * @throws OutOfBoundsException caso a posição informada esteja fora dos limites
     *
     * @return Element
     */
    public function getElemento($indice)
    {
        if (! isset($this->elementos[$indice])) {
            throw new OutOfBoundsException("Não existe elemento na posição $indice");
        }

        return $this->elementos[$indice];
    }

    /**
     * Limpa todos os elementos.
     */
    public function limpar()
    {
        $this->elementos = array();
    }

    public function setNumeroColunasLinha($numeroColunasNaLinha)
    {
        $this->numeroColunasNaLinha = $numeroColunasNaLinha;
    }

    public function getNumeroColunasLinha()
    {
        return $this->numeroColunasNaLinha;
    }
}

/**
 * Representa uma linha completa do formulário com todos os elementos.
 *
 * @author José Almir <jose.almir@pitang.com>
 */
class LinhaFormulario
{

    private $linhas = array();

    /**
     * Adiciona uma linha completa, representada por objetos ElementoLinha.
     *
     * @param ElementoLinha $linha
     */
    public function adicionarLinha(ElementoLinha $linha)
    {
        $this->linhas[] = $linha;
    }

    /**
     * Retorno todas as linhas.
     *
     * @return array
     */
    public function getLinhas()
    {
        return $this->linhas;
    }

    /**
     * Retorna uma linha na posição informada pelo parâmetro $indice.
     *
     * @param int $indice
     *
     * @throws OutOfBoundsException caso a posição informada esteja fora dos limites
     *
     * @return ElementoLinha
     */
    public function getLinha($indice)
    {
        if (! isset($this->linhas[$indice])) {
            throw new OutOfBoundsException("Não existe linha na posição $indice");
        }

        return $this->linhas[$indice];
    }

    /**
     * Retorna uma linha na posição informada pelo parâmetro $indice.
     *
     * @param int $indice
     *
     * @throws OutOfBoundsException caso a posição informada esteja fora dos limites
     *
     * @return ElementoLinha
     */
    public function getElementosDaLinha($indice)
    {
        if (! isset($this->linhas[$indice])) {
            throw new OutOfBoundsException("Não existe linha na posição $indice");
        }

        return $this->linhas[$indice];
    }

    /**
     * Remove uma linha no índice informado.
     *
     * @param int $indice
     *
     * @throws OutOfBoundsException caso a posição informada esteja fora dos limites
     */
    public function removerLinha($indice)
    {
        if (! isset($this->linhas[$indice])) {
            throw new OutOfBoundsException("Não existe linha na posição $indice");
        }

        unset($this->linhas[$indice]);
        $this->linhas = array_values($this->linhas);
    }

    /**
     * Limpa todas as linhas.
     */
    public function limpar()
    {
        $this->linhas = array();
    }
}

/**
 * Classe que representa o elemento select.
 *
 * @author Rodolfo Oliveira <rodolfo.oliveira@pitang.com>
 */
class SelectBox extends Element
{

    private $arrayResultado;

    private $valor;

    private $class;

    private $valorDefault;

    private $labelDefault;

    private $arrayLabel;

    private $separador;

    private $nome;

    public function __construct($nome)
    {
        $this->arrayLabel = array();
        $this->label = '';
        $this->nome = $nome;
    }

    public function gerarElementoParaTemplate($template, $tag)
    {
        $meu_select = $this->gerarSelect(new Element('select'));
        $template->$tag = $meu_select->build();
    }

    public function gerarElemento()
    {
        return $this->gerarSelect(new Element('select'));
    }

    private function gerarSelect($meu_select)
    {
        if ($this->valorDefault != null || $this->labelDefault != null) {
            $meu_option = new Element('option');
            $meu_option->set('value', $this->valorDefault);
            $meu_option->set('text', $this->labelDefault);
            $meu_select->inject($meu_option);
        }

        for ($i = 0; $i < sizeof($this->arrayResultado); ++ $i) {
            $objeto = $this->arrayResultado[$i];

            if ($objeto instanceof stdClass) {
                $meu_option = $this->gerarSelectStdClass($objeto);
            } elseif (is_array($objeto)) {
                $meu_option = $this->gerarSelectArray($objeto);
            }

            $meu_select->inject($meu_option);
        }

        $meu_select->set('name', $this->nome);
        $meu_select->set('class', $this->class);

        return $meu_select;
    }

    private function gerarSelectStdClass($objeto)
    {
        $valor = $this->valor;
        $label = $this->label;

        $meu_option = new Element('option');
        $meu_option->set('value', $objeto->$valor);

        $tamanho = sizeof($this->arrayLabel);

        for ($j = 0; $j < $tamanho; ++ $j) {
            $coluna = $this->arrayLabel[$j];

            if ($j + 1 < $tamanho) {
                $label .= $objeto->$coluna . ' ' . $this->separador . ' ';
            } else {
                $label .= $objeto->$coluna;
            }
        }

        $meu_option->set('text', $label);

        return $meu_option;
    }

    private function gerarSelectArray($objeto)
    {
        $valor = $this->valor;
        $label = $this->label;

        $meu_option = new Element('option');
        $meu_option->set('value', $objeto[$valor]);

        $tamanho = sizeof($this->arrayLabel);

        for ($j = 0; $j < $tamanho; ++ $j) {
            $coluna = $this->arrayLabel[$j];

            if ($j + 1 < $tamanho) {
                $label .= $objeto[$coluna] . ' ' . $this->separador . ' ';
            } else {
                $label .= $objeto[$coluna];
            }
        }

        $meu_option->set('text', $label);

        return $meu_option;
    }

    public function setArrayResultado($arrayResultado)
    {
        $this->arrayResultado = $arrayResultado;
    }

    public function setLabel($label)
    {
        array_push($this->arrayLabel, $label);
    }

    public function setValor($valor)
    {
        $this->valor = $valor;
    }

    public function setClass($class)
    {
        $this->class = $class;
    }

    public function setValorDefault($valorDefault)
    {
        $this->valorDefault = $valorDefault;
    }

    public function setLabelDefault($labelDefault)
    {
        $this->labelDefault = $labelDefault;
    }

    public function setLabelComposto($arrayLabel, $separador)
    {
        $this->arrayLabel = $arrayLabel;
        $this->separador = $separador;
    }
}

class DataTable extends Element
{

    /* vars */
    private $arrayResultado;

    private $colunas;

    private $valores;

    private $class;

    private $titulo;

    private $labelDefault;

    private $classeColuna;

    private $colspancabecalho;

    private $colspandados;

    private $tipo;

    public function __construct($tipo)
    {
        $this->colunas = array();
        $this->valores = array();
        $this->tipo = $tipo;
    }

    public function gerarElementoParaTemplate($template, $tag)
    {
        $tabela = $this->gerarTabela();
        $template->$tag = $tabela->build();
    }

    private function gerarCorpoTabelaCascata($meu_data_table)
    {
        $minha_coluna = new Element('tr');
        $tamanhoColunas = sizeof($this->colunas);
        for ($i = 0; $i < $tamanhoColunas; ++ $i) {
            $meu_cabecalho = new Element('td');
            $meu_cabecalho->set('class', $this->classeColuna);
            $meu_cabecalho->set('text', $this->colunas[$i]);
            $meu_cabecalho->set('colspan', $this->colspancabecalho);

            $minha_coluna->inject($meu_cabecalho);
        }
        $meu_data_table->inject($minha_coluna);

        $tamanhoValores = sizeof($this->valores);
        $tamanhoResultado = sizeof($this->arrayResultado);
        for ($i = 0; $i < $tamanhoResultado; ++ $i) {
            $minha_coluna_dados = new Element('tr');
            $objeto = $this->arrayResultado[$i];
            for ($k = 0; $k < $tamanhoValores; ++ $k) {
                $coluna = $this->valores[$k];

                $meu_dado = new Element('td');
                $meu_dado->set('class', $this->classeColuna);
                $meu_dado->set('text', $objeto->$coluna);
                $meu_dado->set('colspan', $this->colspandados);

                $minha_coluna_dados->inject($meu_dado);
            }
            $meu_data_table->inject($minha_coluna_dados);
        }
    }

    private function geraCorpoTabelaEmLinha($meu_data_table)
    {
        $objeto = $this->arrayResultado[0];

        $tamanhoColunas = sizeof($this->colunas);

        for ($k = 0; $k < $tamanhoColunas; ++ $k) {
            $minha_tr = new Element('tr');

            $minha_td_cabecalho = new Element('td');
            $minha_td_cabecalho->set('class', $this->classeColuna);
            $minha_td_cabecalho->set('text', $this->colunas[$k]);
            $minha_td_cabecalho->set('colspan', $this->colspancabecalho);

            $minha_tr->inject($minha_td_cabecalho);

            $coluna = $this->valores[$k];

            $minha_td = new Element('td');
            $minha_td->set('class', $this->classeColuna);
            $minha_td->set('text', $objeto->$coluna);
            $minha_td->set('colspan', $this->colspancabecalho);
            $minha_tr->inject($minha_td);

            $meu_data_table->inject($minha_tr);
        }
    }

    public function gerarElemento()
    {
        return $this->gerarTabela();
    }

    private function gerarTabela()
    {
        $meu_data_table = new Element('table');

        $meu_thead = new Element('thead');
        $meu_th = new Element('th');

        $meuSpan = new Element('span');
        $meuSpan->set('text', $this->titulo);
        $meuSpan->set('class', 'text-center');

        $meu_th->inject($meuSpan);
        $meu_th->set('colspan', '12');

        $meu_thead->inject($meu_th);
        $meu_data_table->inject($meu_thead);

        if ($this->tipo == 0) {
            $this->gerarCorpoTabelaCascata($meu_data_table);
        } else {
            $this->geraCorpoTabelaEmLinha($meu_data_table);
        }

        $meu_data_table->set('class', $this->class);

        return $meu_data_table;
    }

    public function setArrayResultado($arrayResultado)
    {
        $this->arrayResultado = $arrayResultado;
    }

    public function setColunas($colunas)
    {
        $this->colunas = $colunas;
    }

    public function setValores($valores)
    {
        $this->valores = $valores;
    }

    public function setClass($class)
    {
        $this->class = $class;
    }

    public function setTitulo($titulo)
    {
        $this->titulo = $titulo;
    }

    public function valores($labelDefault)
    {
        $this->labelDefault = $labelDefault;
    }
}

class Dao
{

    /*
     * $tabela - A tabela na qual a consulta ocorrerá
     * $arrayColunas - As colunas que serão trazidas no objeto de retorno, caso não seja informada, será todas
     * $arrayWhere - Deverá receber o par coluna - valor para formação do where
     * $arrayOrdenacao - As colunas na qual deseja-se ordenar, a posição no array é diretamente proporcional
     *
     */
    private static function consultaTodos($conexao, $tabela, $arrayColunas = null, $arrayWhere = null, $arrayOrdenacao = null)
    {
        $valorSelect = 'select ';
        $condicaoWhere = ' where 1 = 1';
        $arrayValoresCondicoes = array();
        $consultaOrder = ' order by ';
        $consultaColunas = '';

        for ($i = 0; $i < sizeof($arrayColunas); ++ $i) {
            if ($i + 1 == sizeof($arrayColunas)) {
                $consultaColunas .= $arrayColunas[$i];
            } else {
                $consultaColunas .= $arrayColunas[$i] . ',';
            }
        }
        if ($arrayColunas == null) {
            $consultaColunas = '*';
        }

        for ($i = 0; $i < sizeof($arrayWhere); ++ $i) {
            $condicaoWhere .= ' and ' . $arrayWhere[$i];
            array_push($arrayValoresCondicoes, $arrayWhere[$i][1]);
        }

        for ($i = 0; $i < sizeof($arrayOrdenacao); ++ $i) {
            if ($i + 1 == sizeof($arrayOrdenacao)) {
                $consultaOrder .= $arrayOrdenacao[$i];
            } else {
                $consultaOrder .= $arrayOrdenacao[$i] . ',';
            }
        }

        $sql = $valorSelect . $consultaColunas . ' from ' . $tabela . $condicaoWhere;
        $sql .= sizeof($arrayOrdenacao) > 0 ? $consultaOrder : '';

        $dados = $conexao->getAll($sql, $arrayValoresCondicoes, DB_FETCHMODE_OBJECT);

        return $dados;
    }

    public static function consultaTodosOracle($tabela, $arrayColunas = null, $arrayWhere = null, $arrayOrdenacao = null)
    {
        return self::consultaTodos(ClaDatabaseOracle::getConexao(), $tabela, $arrayColunas, $arrayWhere, $arrayOrdenacao);
    }

    public static function consultaTodosPostgre($tabela, $arrayColunas = null, $arrayWhere = null, $arrayOrdenacao = null)
    {
        return self::consultaTodos(ClaDatabasePostgresql::getConexao(), $tabela, $arrayColunas, $arrayWhere, $arrayOrdenacao);
    }
}

/**
 * ClaHelper
 *
 * @author jfsi
 *
 */
class ClaHelper
{

    /**
     * Um construtor privado; previne a criação direta do objeto
     */
    private function __construct()
    {}

    /**
     * Previne que o usuário clone a instância
     */
    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }

    /**
     *
     * @param mixed $data
     *
     * @return string
     */
    public static function converterDataBrParaBanco($data)
    {
        $dataBr = explode('/', $data);

        return $dataBr[2] . '-' . $dataBr[1] . '-' . $dataBr[0];
    }

    /**
     * Converte data formato banco para data brasileira
     *
     * @param mixed $data
     * @param string $exibirHora
     *
     * @return string
     */
    public static function converterDataBancoParaBr($data, $exibirHora = false)
    {
        $dataHoraBanco = explode(' ', $data);

        $arrayDataBanco = explode('-', $dataHoraBanco[0]);
        $dataBanco = $arrayDataBanco[2] . '/' . $arrayDataBanco[1] . '/' . $arrayDataBanco[0];

        if ($exibirHora && isset($dataHoraBanco[1])) {
            $dataBanco .= ' ' . substr($dataHoraBanco[1], 0, 5);
        }

        return $dataBanco;
    }

    /**
     * Dado um formato, valida uma data
     *
     * @param string $data
     * @param string $formato
     * @return bool
     */
    public static function validationData($data, $formato = 'DD/MM/AAAA')
    {
        switch ($formato) {
            case 'DD-MM-AAAA':
            case 'DD/MM/AAAA':
                list ($dia, $mes, $ano) = preg_split('/[-\.\/ ]/', $data);
                break;
            case 'AAAA/MM/DD':
            case 'AAAA-MM-DD':
                list ($ano, $mes, $dia) = preg_split('/[-\.\/ ]/', $data);
                break;
            case 'AAAA/DD/MM':
            case 'AAAA-DD-MM':
                list ($ano, $dia, $mes) = preg_split('/[-\.\/ ]/', $data);
                break;
            case 'MM-DD-AAAA':
            case 'MM/DD/AAAA':
                list ($mes, $dia, $ano) = preg_split('/[-\.\/ ]/', $data);
                break;
            case 'AAAAMMDD':
                $ano = substr($data, 0, 4);
                $mes = substr($data, 4, 2);
                $dia = substr($data, 6, 2);
                break;
            case 'AAAADDMM':
                $ano = substr($data, 0, 4);
                $dia = substr($data, 4, 2);
                $mes = substr($data, 6, 2);
                break;
            default:
                throw new Exception("Formato de data inválido");
                break;
        }

        return checkdate($mes, $dia, $ano);
    }

    /**
     * Formatar com Zeros
     *
     * @param integer $tamanho
     * @param integer $valor
     * @return string
     */
    public static function formatarComZeros($tamanho, $valor)
    {
        $formatador = '1';
        for ($i = 0; $i < $tamanho; $i ++) {
            $formatador = $formatador . '0';
        }

        return substr($valor + (int) $formatador, 1);
    }

    /**
     * [DateTimeFormat description]
     *
     * @param string $data
     *            no formato dd/mm/aaaa
     * @return DateTime
     */
    public static function dateTimeFormat($data)
    {
        list ($day, $month, $year) = sscanf($data, '%02d/%02d/%04d');

        return new DateTime("$year-$month-$day");
    }
}

class ClaItem
{

    public static function clean($strIndexSession = 'intencaoItem')
    {
        $_SESSION[$strIndexSession] = null;
        unset($_SESSION[$strIndexSession]);
    }

    /**
     * Remove item da lista (session)
     *
     * @param array $checkItens
     * @param string $strIndexSession
     */
    public static function removeItemLista(array $checkItens, $strIndexSession = 'intencaoItem')
    {
        if (count($checkItens) > 0) {
            foreach ($checkItens as $value) {
                $value = $value - 1;
                if (isset($_SESSION[$strIndexSession][$value])) {
                    $_SESSION[$strIndexSession][$value] = null;
                    unset($_SESSION[$strIndexSession][$value]);
                }
            }

            $aux = array();
            foreach ($_SESSION[$strIndexSession] as $value) {
                $aux[] = $value;
            }

            self::clean($strIndexSession);

            $_SESSION[$strIndexSession] = $aux;
            unset($aux);
        }
    }

    /**
     * Coleta dados do CadItemIncluir que foram setado em session['item']
     * e move para session['intencaoItem']
     *
     * @param string $strIndexSession
     */
    public static function collectorSessionItem($strIndexSession = 'intencaoItem')
    {
        $existeItemNaSession = isset($_SESSION['item']) && count($_SESSION['item']) > 0;
        if ($existeItemNaSession) {
            $countItem = count($_SESSION['item']);
            for ($i = 0; $i < $countItem; $i ++) {
                $newItem = $_SESSION['item'][$i];
                $_SESSION[$strIndexSession][] = $newItem;
            }
        }
        // cleaning for news itens
        $_SESSION['item'] = false;
        unset($_SESSION['item']);
        session_write_close();
    }
}

class Criterion
{

    public $wheres = array();

    public $ors = array();

    /**
     * Add an "or" part to the criterion.
     *
     * @param string $column
     * @param mixed $value[optional]
     * @param string $operator[optional]
     */
    public function addOr($column, $value = null, $operator = Criteria::DB_EQUALS)
    {
        if (! is_array($value)) {
            $this->ors[] = array(
                'column' => $column,
                'value' => $value,
                'operator' => $operator
            );
        } else {
            $this->ors[] = array(
                'column' => $column,
                'value' => $value,
                'operator' => $operator
            );
        }
    }

    /**
     * Add a "where" part to the criterion.
     *
     * @param string $column
     * @param mixed $value[optional]
     * @param string $operator[optional]
     */
    public function addWhere($column, $value = null, $operator = Criteria::DB_EQUALS)
    {
        if (! is_array($value)) {
            $this->wheres[] = array(
                'column' => $column,
                'value' => $value,
                'operator' => $operator
            );
        } else {
            $this->wheres[] = array(
                'column' => $column,
                'value' => $value,
                'operator' => $operator
            );
        }
    }

    /**
     * Generate a new criterion.
     *
     * @param string $column
     * @param mixed $value[optional]
     * @param string $operator[optional]
     * @param string $variable[optional]
     * @param string $additional[optional]
     * @param string $special[optional]
     */
    public function __construct($column, $value = '', $operator = Criteria::DB_EQUALS, $variable = '', $additional = '', $special = '')
    {
        if ($column != '') {
            $this->wheres[] = array(
                'column' => $column,
                'value' => $value,
                'operator' => $operator,
                'variable' => $variable,
                'additional' => $additional,
                'special' => $special
            );
        }
    }
}

class ClaDatabase extends ClaDatabasePostgresql
{
}

class HelperRegistroDePreco
{

    public static function getNumeroSolicitacaoCompra($idSolicitacao)
    {
        $db = ClaDatabasePostgresql::getConexao();

        assercao(! is_null($db), "Variável de banco de dados não foi inicializada");
        assercao(! is_null($idSolicitacao), "Parâmetro 'idSolicitacao' requerido");

        $sql = "
		select distinct ccenpocorg, ccenpounid, csolcocodi, asolcoanos
		from sfpc.tbsolicitacaocompra scc, SFPC.TBcentrocustoportal cc
		where scc.ccenposequ = cc.ccenposequ
		and csolcosequ = $idSolicitacao
		";

        $linha = resultLinhaUnica(executarSQL($db, $sql));
        $resposta = array();
        $resposta['orgaoSofin'] = $linha[0];
        $resposta['unidadeSofin'] = $linha[1];
        $resposta['solicitacao'] = $linha[2];
        $resposta['anoSolicitacao'] = $linha[3];

        $numeroSolicitacao = sprintf("%02s", $resposta['orgaoSofin']);
        $numeroSolicitacao .= sprintf("%02s", $resposta['unidadeSofin']);
        $numeroSolicitacao .= ".";
        $numeroSolicitacao .= sprintf("%04s", $resposta['solicitacao']);
        $numeroSolicitacao .= ".";
        $numeroSolicitacao .= $resposta['anoSolicitacao'];

        $resposta['numeroSolicitacao'] = $numeroSolicitacao;

        return $resposta['numeroSolicitacao'];
    }

    public static function getNumeroAta($codigoOrgaoLicitante, $codigoSolicitacaoCompra, $sequencialAta, $anoProcessoLicitatorio)
    {
        $numeroAta = self::getNumeroSolicitacaoCompra($codigoSolicitacaoCompra);
        $valoresExploded = explode(".", $numeroAta);
        $valorUnidadeOrc = substr($valoresExploded[0], 2, 2);

        $valorAta = str_pad($codigoOrgaoLicitante, 2, '0', STR_PAD_LEFT);
        $valorAta .= $valorUnidadeOrc . ".";
        $valorAta .= str_pad($sequencialAta, 4, '0', STR_PAD_LEFT);
        $valorAta .= '/';
        $valorAta .= $anoProcessoLicitatorio;

        return $valorAta;
    }

    public static function getNumeroSolicitacaoLicitacaoCompra($codigoProcesso, $anoLicitacao, $codigoGrupo, $codigoComissao, $codigoOrgaoLicitante)
    {
        $sql = "SELECT
					csolcosequ, clicpoproc, alicpoanop, cgrempcodi, ccomlicodi, corglicodi, cusupocodi, tsolclulat
				FROM
					sfpc.tbsolicitacaolicitacaoportal
				WHERE
					clicpoproc = $codigoProcesso AND alicpoanop = $anoLicitacao AND
					cgrempcodi = $codigoGrupo AND ccomlicodi = $codigoComissao AND
					corglicodi = $codigoOrgaoLicitante";

        $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $sql);
        $resultado->fetchInto($solicitacaoLicitacao, DB_FETCHMODE_OBJECT);

        return $solicitacaoLicitacao->csolcosequ;
    }
}

/**
 *
 * @author jfsi
 */
class Criteria
{

    protected $criterias = array();

    protected $jointables = array();

    protected $sort_orders = array();

    protected $sort_groups = array();

    protected $selections = array();

    protected $values = array();

    protected $distinct = false;

    protected $ors = array();

    protected $updates = array();

    protected $aliases = array();

    protected $return_selections = array();

    protected $indexby = null;

    protected $colunsTable = array();

    protected $ForingKeyTables = array();

    protected $keysTable = array();

    /**
     * Parent table.
     *
     * @var Table
     */
    protected $fromtable;

    protected $limit = null;

    protected $offset = null;

    protected $customsel = false;

    public $action;

    public $sql;

    const DB_EQUALS = '=';

    const DB_NOT_EQUALS = '!=';

    const DB_GREATER_THAN = '>';

    const DB_LESS_THAN = '<';

    const DB_GREATER_THAN_EQUAL = '>=';

    const DB_LESS_THAN_EQUAL = '<=';

    const DB_IS_NULL = 'IS NULL';

    const DB_IS_NOT_NULL = 'IS NOT NULL';

    const DB_LIKE = 'LIKE';

    const DB_ILIKE = 'ILIKE';

    const DB_NOT_LIKE = 'NOT LIKE';

    const DB_NOT_ILIKE = 'NOT ILIKE';

    const DB_IN = 'IN';

    const DB_NOT_IN = 'NOT IN';

    const DB_LEFT_JOIN = 'LEFT JOIN';

    const DB_INNER_JOIN = 'INNER JOIN';

    const DB_RIGHT_JOIN = 'RIGHT JOIN';

    const DB_COUNT = 'COUNT';

    const DB_MAX = 'MAX';

    const DB_SUM = 'SUM';

    const DB_CONCAT = 'CONCAT';

    const DB_LOWER = 'LOWER';

    const DB_DISTINCT = 'DISTINCT';

    const DB_COUNT_DISTINCT = 'COUNT(DISTINCT';

    const SORT_ASC = 'asc';

    const SORT_DESC = 'desc';

    /**
     * Constructor.
     *
     * @param string $table
     *            [optional]
     *
     * @return Criteria
     */
    public function __construct($table = null, $setupjointable = false)
    {
        if ($table !== null) {
            $this->setFromTable($table, $setupjointable);
        }

        return $this;
    }

    /**
     * Set the "from" table.
     *
     * @param \b2db\Table $table
     *            The table
     * @param bool $setupjointables
     *            [optional] Whether to automatically join other tables
     *
     * @return Base2DBCriteria
     */
    public function setFromTable($tabela, $setarTabelasJoin = false)
    {
        $this->fromtable = $tabela;
        if ($setarTabelasJoin) {
            $this->setupJoinTables();
        }

        return $this;
    }

    /**
     * Returns a criterion, to use with more advanced SQL queries.
     *
     * @param string $column
     * @param mixed $value
     * @param string $operator
     *
     * @return Criterion
     */
    public function returnCriterion($column, $value, $operator = self::DB_EQUALS)
    {
        $ret = new Criterion($column, $value, $operator);

        return $ret;
    }

    /**
     * Get added values.
     *
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * Adicionar uma columa ao select.
     *
     * @param string $columa
     * @param string $alias
     *            [optional] An alias for the column
     * @param string $especial
     *            [optional] Whether to use a special method on the column
     * @param string $variavel
     *            [optional] An optional variable to assign it to
     * @param string $adicional
     *            [optional] Additional parameter
     *
     * @return Criteria
     */
    public function addSelectionColumn($columa, $alias = '', $especial = '', $variavel = '', $adicional = '')
    {
        $this->customsel = true;
        $column = $column;
        $alias = ($alias === '') ? str_replace('.', '_', $column) : $alias;
        $this->_addSelectionColumn($column, $alias, $special, $variable, $additional);

        return $this;
    }

    protected function _addSelectionColumn($column, $alias = '', $special = '', $variable = '', $additional = '')
    {
        $coluna = ($this->aliases[$this->fromtable] != null) ? $this->aliases[$this->fromtable] . '.' . $column : $column;
        $this->selections[$this->fromtable . $column] = array(
            'column' => $coluna,
            'alias' => $alias,
            'special' => $special,
            'variable' => $variable,
            'additional' => $additional
        );
    }

    /**
     * Adds an "or" part to the query.
     *
     * @param string $column
     *            The column to update
     * @param mixed $value
     *            The value
     * @param mixed $operator
     *            [optional]
     *
     * @return Base2DBCriteria
     */
    public function addOr($column, $value = null, $operator = self::DB_EQUALS)
    {
        if ($column instanceof Criterion) {
            $this->ors[] = $column;
        } else {
            $this->ors[] = new Criterion($column, $value, $operator);
        }

        return $this;
    }

    /**
     * Add a field to update.
     *
     * @param string $column
     *            The column name
     * @param mixed $value
     *            The value to update
     *
     * @return Base2DBCriteria
     */
    public function addWhere($table, $column, $value = '', $operator = self::DB_EQUALS, $variable = '', $additional = '', $special = '')
    {
        $coluna = ($this->aliases[$table] != null) ? $this->aliases[$table] . '.' . $column : $column;
        if ($column instanceof Criterion) {
            $this->criterias[] = $coluna;
        } else {
            $this->criterias[] = new Criterion($coluna, $value, $operator, $variable, $additional, $special);
        }

        return $this;
    }

    /**
     * Join one table on another.
     *
     * @param Table $jointable
     *            The table to join
     * @param string $col1
     *            The left matching column
     * @param string $col2
     *            The right matching column
     * @param array $criterias
     *            An array of criteria (ex: array(array(DB_FLD_ISSUE_ID, 1), array(DB_FLD_ISSUE_STATE, 1));
     * @param string $jointype
     *            Type of join
     * @param Table $ontable
     *            If different than the main table, specify the left side of the join here
     *
     * @return Base2DBCriteria
     */
    public function addJoin($jointable, $foreigncol, $tablecol, $criterias = array(), $jointype = self::DB_LEFT_JOIN, $ontable = null)
    {
        foreach ($this->jointables as $ajt) {
            if ($this->aliases[$ajt['jointable']] == $this->aliases[$jointable]) {
                $jointable = clone $jointable;
                break;
            }
        }

        $col1 = $this->aliases[$jointable] . '.' . $foreigncol;
        if ($ontable === null) {
            $col2 = $this->aliases[$this->fromtable] . '.' . $tablecol;
        } else {
            $col2 = $this->aliases[$this->$ontable] . '.' . $tablecol;
        }
        $this->jointables[$this->aliases[$jointable]] = array(
            'jointable' => $jointable,
            'col1' => $col1,
            'col2' => $col2,
            'original_column' => tablecol,
            'criterias' => $criterias,
            'jointype' => $jointype
        );

        return $jointable;
    }

    /**
     * Generates "select all" SQL.
     *
     * @return string
     */
    protected function _generateSelectAllSQL()
    {
        $sqls = array();
        foreach ($this->getSelectionColumns() as $column_data) {
            if ($column_data['column'] != '*') {
                $sqls[] = $column_data['column'] . ' AS ' . $this->getSelectionAlias($column_data['column']);
            } else {
                $sqls[] = $column_data['column'];
            }
        }

        return implode(', ', $sqls);
    }

    /**
     * Adds all select columns from all available tables in the query.
     */
    protected function _addAllSelectColumns()
    {
        foreach ($this->colunsTable[$this->fromtable] as $aColumn) {
            $this->_addSelectionColumn($aColumn);
        }
        foreach ($this->ForingKeyTables as $table) {
            foreach ($table['jointable']->getAliasColumns() as $aColumn) {
                $this->_addSelectionColumn($aColumn);
            }
        }
    }

    /**
     * Add an order by clause.
     *
     * @param string $column
     *            The column to order by
     * @param string $sort
     *            [optional] The sort order
     *
     * @return Base2DBCriteria
     */
    public function addOrderBy($column, $sort = null, $join_column = null)
    {
        if ($join_column !== null) {
            $column = null;
            foreach ($this->jointables as $table_alias => $join_options) {
                if ($join_options['col2'] == $join_column) {
                    $column = $join_options['jointable']->getSelectionAlias($column);
                }
            }
        }
        if (is_array($column)) {
            foreach ($column as $a_sort) {
                $this->sort_orders[] = array(
                    'column' => $a_sort[0],
                    'sort' => $a_sort[1]
                );
            }
        } else {
            $this->sort_orders[] = array(
                'column' => $column,
                'sort' => $sort
            );
        }

        return $this;
    }

    /**
     * Limit the query.
     *
     * @param int $limit
     *            The number to limit
     *
     * @return Base2DBCriteria
     */
    public function setLimit($limit)
    {
        $this->limit = (int) $limit;

        return $this;
    }

    /**
     * Add a group by clause.
     *
     * @param string $column
     *            The column to group by
     * @param string $sort
     *            [optional] The sort order
     *
     * @return Base2DBCriteria
     */
    public function addGroupBy($column, $sort = null)
    {
        if (is_array($column)) {
            foreach ($column as $a_sort) {
                $this->sort_groups[] = array(
                    'column' => $a_sort[0],
                    'sort' => $a_sort[1]
                );
            }
        } else {
            $this->sort_groups[] = array(
                'column' => $column,
                'sort' => $sort
            );
        }

        return $this;
    }

    /**
     * Returns the SQL string for the current criteria.
     *
     * @return string
     */
    public function getSQL()
    {
        return $this->sql;
    }

    /**
     * Add all available foreign tables.
     *
     * @param array $join
     *            [optional]
     */
    public function setupJoinTables($join = 'all')
    {
        if (! is_array($join) && $join == 'all') {
            $foreign_tables = $this->ForingKeyTables[$this->fromtable];
            foreach ($foreign_tables as $aForeign) {
                $fTable = $aForeign;
                $fKey = $this->aliases[$fTable] . '.' . $this->keysTable[$aForeign];
                $fColumn = $this->aliases[$this->fromtable] . '.' . $this->keysTable[$aForeign];
                $this->addJoin($fTable, $fKey, $fColumn);
            }
        } elseif (is_array($join)) {
            foreach ($join as $join_column) {
                $foreign = $this->fromtable->getForeignTableByLocalColumn($join_column);
                $this->addJoin($foreign['table'], $foreign['table']->getB2DBAlias() . '.' . $this->getColumnName($foreign['key']), $this->fromtable->getB2DBAlias() . '.' . $this->getColumnName($foreign['column']));
            }
        }
    }

    /**
     * Generate a "select" query.
     *
     * @param bool $all
     *            [optional]
     */
    public function generateSelectSQL($all = false)
    {
        $this->values = array();
        $this->sql = '';
        $this->action = 'select';
        $sql = $this->_generateSelectSQL();
        $sql .= $this->_generateJoinSQL();

        if (! $all) {
            $sql .= $this->_generateWhereSQL();
        }
        $this->sql = $sql;
    }

    /**
     * Generate a "count" query.
     */
    public function generateCountSQL()
    {
        $this->values = array();
        $this->sql = '';
        $this->action = 'count';
        $sql = $this->_generateCountSQL();
        $sql .= $this->_generateJoinSQL();
        $sql .= $this->_generateWhereSQL();
        $this->sql = $sql;
    }

    /**
     * Add a specified value.
     *
     * @param mixed $value
     */
    protected function _addValue($value)
    {
        if (is_bool($value)) {
            if (Core::getDBtype() == 'mysql') {
                $this->values[] = (int) $value;
            } elseif (Core::getDBtype() == 'pgsql') {
                $this->values[] = ($value) ? 'true' : 'false';
            }
        } else {
            $this->values[] = $value;
        }
    }

    /**
     * Generate the "select" part of the query.
     *
     * @return string
     */
    protected function _generateSelectSQL()
    {
        $sql = ($this->distinct) ? 'SELECT DISTINCT ' : 'SELECT ';
        if ($this->customsel) {
            if ($this->distinct) {
                foreach ($this->sort_orders as $a_sort) {
                    $this->addSelectionColumn($a_sort['column']);
                }
            }
            $sqls = array();

            foreach ($this->selections as $column => $selection) {
                $alias = ($selection['alias']) ? $selection['alias'] : $this->getSelectionAlias($column);
                $sub_sql = (isset($selection['variable']) && $selection['variable'] != '') ? ' @' . $selection['variable'] . ':=' : '';
                if ($selection['special'] != '') {
                    $sub_sql .= mb_strtoupper($selection['special']) . '(' . $selection['column'] . ')';
                    if ($selection['additional'] != '') {
                        $sub_sql .= ' ' . $selection['additional'] . ' ';
                    }
                    if (mb_strpos($selection['special'], '(') !== false) {
                        $sub_sql .= ')';
                    }
                } else {
                    $sub_sql .= $selection['column'];
                }
                $sub_sql .= ' AS ' . $alias;
                $sqls[] = $sub_sql;
            }
            $sql .= implode(', ', $sqls);
        } else {
            $this->_addAllSelectColumns();
            $sql .= $this->_generateSelectAllSQL();
        }

        return $sql;
    }

    /**
     * Get all select columns.
     *
     * @return array
     */
    public function getSelectionColumns()
    {
        return $this->selections;
    }

    /**
     * Generate the "count" part of the query.
     *
     * @return string
     */
    protected function _generateCountSQL()
    {
        $sql = ($this->distinct) ? 'SELECT COUNT(DISTINCT ' : 'SELECT COUNT(';
        $sql .= $this->getSelectionColumn($this->getTable()
            ->getIdColumn());
        $sql .= ') as num_col';

        return $sql;
    }

    /**
     * Return a select column.
     *
     * @param string $column
     * @param string $join_column[optional]
     * @param bool $debug[optional]
     *
     * @return string
     */
    public function getSelectionColumn($column, $join_column = null, $throw_exceptions = true)
    {
        if (isset($this->selections[$column])) {
            return $this->selections[$column];
        }
        foreach ($this->selections as $a_sel) {
            if ($a_sel['alias'] == $column) {
                return $column;
            }
        }
        list ($table_name, $column_name) = explode('.', $column);
        if ($join_column === null) {
            if ($this->aliases[$this->fromtable] == $table_name) {
                return $this->aliases[$this->fromtable] . '.' . $column_name;
            } elseif (isset($this->jointables[$table_name])) {
                return $this->aliases[$this->jointables[$table_name]['jointable']] . '.' . $column_name;
            }
        }
        foreach ($this->jointables as $a_table) {
            if (($join_column !== null && $a_table['col2'] == $join_column) || ($join_column === null && $a_table['jointable']->getB2DBName() == $table_name)) {
                return $this->aliases[$a_table['jointable']] . '.' . $column_name;
            }
        }

        return;
    }

    /**
     * Set the query to distinct mode.
     */
    public function setDistinct()
    {
        $this->distinct = true;
    }

    protected function _sanityCheck($details)
    {
        if (! in_array($details['operator'], array(
            self::DB_EQUALS,
            self::DB_GREATER_THAN,
            self::DB_GREATER_THAN_EQUAL,
            self::DB_ILIKE,
            self::DB_IN,
            self::DB_IS_NOT_NULL,
            self::DB_IS_NULL,
            self::DB_LESS_THAN,
            self::DB_LESS_THAN_EQUAL,
            self::DB_LIKE,
            self::DB_NOT_EQUALS,
            self::DB_NOT_ILIKE,
            self::DB_NOT_IN,
            self::DB_NOT_LIKE
        ))) {
            throw new Exception('Invalid operator', $this->getSQL());
        }
    }

    protected function _generateSQLPart($part, $strip)
    {
        $initial_sql = ($strip) ? $part['column'] : $part['column'];

        $sql = (isset($part['special']) && $part['special'] != '') ? $part['special'] . "({$initial_sql})" : $initial_sql;
        if (is_null($part['value']) && ! in_array($part['operator'], array(
            self::DB_IS_NOT_NULL,
            self::DB_IS_NULL
        ))) {
            $part['operator'] = ($part['operator'] == self::DB_EQUALS) ? self::DB_IS_NULL : self::DB_IS_NOT_NULL;
        } elseif (is_array($part['value'])) {
            $part['operator'] = self::DB_IN;
        }
        $sql .= ' ' . $part['operator'] . ' ';
        if (is_array($part['value'])) {
            $placeholders = array();
            foreach ($part['value'] as $value) {
                $placeholders[] = '?';
                $this->_addValue($value);
            }
            $sql .= '(' . implode(', ', $placeholders) . ')';
        } elseif ($part['operator'] != self::DB_IS_NULL && $part['operator'] != self::DB_IS_NOT_NULL) {
            $sql .= ($part['operator'] == self::DB_IN) ? '(?)' : '?';
            $this->_addValue($part['value']);
        }

        return $sql;
    }

    /**
     * Get the selection alias for a specified column.
     *
     * @param string $column
     *
     * @return string
     */
    public function getSelectionAlias($column)
    {
        if (! is_numeric($column) && ! is_string($column)) {
            if (is_array($column) && array_key_exists('column', $column)) {
                $column = $column['column'];
            } else {
                throw new Exception('Invalid column!', $this->getSQL());
            }
        }
        if (! isset($this->aliases[$column])) {
            $this->aliases[$column] = str_replace('.', '_', $column);
        }

        return $this->aliases[$column];
    }

    /**
     * Parses the given criterion and returns the SQL string.
     *
     * @param Criterion $critn
     * @param bool $strip
     *
     * @return string
     */
    protected function _parseCriterion($critn, $strip = false)
    {
        $sql = '';
        $where_sqls = array();
        $or_sqls = array();
        if (count($critn->wheres) > 0) {
            foreach ($critn->wheres as $where_part) {
                if (! $where_part['column'] instanceof Criterion) {
                    $this->_sanityCheck($where_part);
                    $where_sqls[] = $this->_generateSQLPart($where_part, $strip);
                } else {
                    $where_sqls[] = $this->_parseCriterion($where_part['column']);
                }
            }
            $sql = '(' . implode(' AND ', $where_sqls) . ')';
        }
        if (count($critn->ors) > 0) {
            foreach ($critn->ors as $or_part) {
                if (! $where_part['column'] instanceof Criterion) {
                    $this->_sanityCheck($where_part);
                    $where_sqls[] = $this->_generateSQLPart($where_part, $strip);
                } else {
                    $where_sqls[] = $this->_parseCriterion($where_part['column']);
                }
            }
            $sql = '(' . implode(' AND ', $where_sqls) . ')';
        }
        if (count($critn->ors) > 0) {
            foreach ($critn->ors as $or_part) {
                if (! $or_part['column'] instanceof Criterion) {
                    $this->_sanityCheck($or_part);
                    $or_sqls[] = $this->_generateSQLPart($or_part, $strip);
                } else {
                    $or_sqls[] = $this->_parseCriterion($or_part['column']);
                }
            }
            $sql = ' (' . $sql . ' OR ' . implode(' OR ', $or_sqls) . ') ';
        }

        return $sql;
    }

    protected function _generateWherePart($strip)
    {
        $where_sqls = array();
        $or_sqls = array();
        foreach ($this->criterias as $a_crit) {
            $where_sqls[] = $this->_parseCriterion($a_crit, $strip);
        }

        $sql = implode(' AND ', $where_sqls);
        if (count($this->ors) > 0) {
            foreach ($this->ors as $a_crit) {
                $or_sqls[] = $this->_parseCriterion($a_crit, $strip);
            }
            $sql = '(' . $sql . ' OR ' . implode(' OR ', $or_sqls) . ')';
        }

        return ' WHERE ' . $sql;
    }

    /**
     * Generate the "where" part of the query.
     *
     * @param bool $strip[optional]
     *
     * @return string
     */
    protected function _generateWhereSQL($strip = false)
    {
        $sql = '';
        if (count($this->criterias) > 0) {
            $sql = $this->_generateWherePart($strip);
        }
        if (count($this->sort_groups) > 0 || (count($this->sort_orders) > 0 && $this->action == 'count')) {
            $group_columns = array();
            $groups = array();
            foreach ($this->sort_groups as $a_group) {
                $column_name = $a_group['column'];
                $groups[] = $column_name . ' ' . $a_group['sort'];
                if ($this->action == 'count') {
                    $group_columns[$column_name] = $column_name;
                }
            }
            $sql .= ' GROUP BY ' . implode(', ', $groups);
            if ($this->action == 'count') {
                $sort_groups = array();
                foreach ($this->sort_orders as $a_sort) {
                    $column_name = $a_sort['column'];
                    if (! array_key_exists($column_name, $group_columns)) {
                        $sort_groups[] = $column_name . ' ';
                    }
                }
                $sql .= implode(', ', $sort_groups);
            }
        }
        if (count($this->sort_orders) > 0) {
            $sort_sqls = array();
            foreach ($this->sort_orders as $a_sort) {
                if (is_array($a_sort['sort'])) {
                    $subsort_sqls = array();
                    foreach ($a_sort['sort'] as $sort_elm) {
                        $subsort_sqls[] = $a_sort['column'] . '=' . $sort_elm;
                    }
                    $sort_sqls[] = implode(',', $subsort_sqls);
                } else {
                    $sort_sqls[] = $a_sort['column'] . ' ' . $a_sort['sort'];
                }
            }
            $sql .= ' ORDER BY ' . implode(', ', $sort_sqls);
        }
        if ($this->action == 'select') {
            if ($this->limit != null) {
                $sql .= ' LIMIT ' . (int) $this->limit;
            }
            if ($this->offset != null) {
                $sql .= ' OFFSET ' . (int) $this->offset;
            }
        }

        return $sql;
    }

    /**
     * Generate the "join" part of the sql.
     *
     * @return string
     */
    protected function _generateJoinSQL()
    {
        $sql = ' FROM ' . $this->fromtable . ' ' . $this->aliases[$this->fromtable];
        foreach ($this->jointables as $a_jt) {
            $sql .= ' ' . $a_jt['jointype'] . ' ' . ' ' . $a_jt['jointable'] . ' ' . $this->aliases[$a_jt['jointable']];
            $sql .= ' ON (' . $a_jt['col1'] . self::DB_EQUALS . $a_jt['col2'];

            foreach ($a_jt['criterias'] as $a_crit) {
                $sql .= ' AND ';
                $a_crit = new Criterion($a_crit[0], $a_crit[1]);
                $sql .= $this->_parseCriterion($a_crit);
            }
            $sql .= ')';
        }

        return $sql;
    }

    /**
     * Retrieve a list of foreign tables.
     *
     * @return array
     */
    public function getForeignTables()
    {
        return $this->jointables;
    }

    public function setColumnTable($table, $Colunas = array())
    {
        $this->colunsTable[$table] = (empty($Colunas)) ? array(
            '*'
        ) : $Colunas;
    }

    public function setAliases($table, $aliases)
    {
        $this->aliases[$table] = $aliases;
    }
}

/**
 *
 * @author jfsi
 */
class ClaRegistroPrecoIntencaoResposta
{
    // Um construtor privado; previne a criação direta do objeto
    private function __construct()
    {}

    // Previne que o usuário clone a instância
    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }

    /**
     *
     * @param int $sequencialIntencao
     * @param int $anoIntencao
     *
     * @return string
     */
    protected static function sqlDeleteIntencaoResposta($sequencialIntencao, $anoIntencao)
    {
        ClaValidationIntencao::validationIntencaoSequencial($sequencialIntencao);
        ClaValidationIntencao::validationIntencaoAno($anoIntencao);

        // Deleta respostas
        $sqlRespostas = '
            DELETE FROM sfpc.tbrespostaintencaorp
            WHERE cintrpsequ = %d AND cintrpsano = %d
        ';

        return sprintf($sqlRespostas, $sequencialIntencao, $anoIntencao);
    }

    /**
     *
     * @param int $sequencialIntencao
     * @param int $anoIntencao
     *
     * @return string
     */
    protected static function sqlDeleteIntencaoRespostaItem($sequencialIntencao, $anoIntencao)
    {
        ClaValidationIntencao::validationIntencaoSequencial($sequencialIntencao);
        ClaValidationIntencao::validationIntencaoAno($anoIntencao);

        // Itens da resposta
        $sqlItensResposta = '
        DELETE FROM
            sfpc.tbitemrespostaintencaorp
        WHERE
            cintrpsequ = %d AND cintrpsano = %d;
        ';

        return sprintf($sqlItensResposta, $sequencialIntencao, $anoIntencao);
    }

    /**
     *
     * @param int $sequencialIntencao
     * @param int $anoIntencao
     * @param string $situacaoResposta
     *
     * @return string
     */
    protected static function sqlSelectRespostaIntencao($sequencialIntencao, $anoIntencao, $situacaoResposta = null)
    {
        ClaValidationIntencao::validationIntencaoSequencial($sequencialIntencao);
        ClaValidationIntencao::validationIntencaoAno($anoIntencao);

        $sql = "
            SELECT cintrpsequ, cintrpsano, corglicodi, frinrpsitu, trinrpdcad, cusupocodi, trinrpulat, xrinrpjust
            FROM
                sfpc.tbrespostaintencaorp
            WHERE
                cintrpsequ = $sequencialIntencao
                AND cintrpsano = $anoIntencao
        ";

        if (! is_null($situacaoResposta)) {
            $sql .= " AND frinrpsitu = '$situacaoResposta' ";
        }

        return sprintf($sql, $sequencialIntencao, $anoIntencao);
    }
}

/**
 *
 * @author jfsi
 */
class ClaRegistroPrecoIntencaoSQL
{

    /**
     *
     * @param int $orgaoCodigo
     *
     * @throws InvalidArgumentException
     */
    public static function validationOrgaoCodigo($orgaoCodigo)
    {
        if (! is_int($orgaoCodigo)) {
            throw new InvalidArgumentException('O $orgaoCodigo not is integer');
        }
    }

    /**
     *
     * @param int $intencaoSequencial
     *
     * @throws InvalidArgumentException
     */
    public static function validationIntencaoSequencial($sequencialIntencao)
    {
        if (! is_int($sequencialIntencao)) {
            throw new InvalidArgumentException('O $sequencialIntencao not is integer');
        }
    }

    /**
     *
     * @param int $intencaoAno
     *
     * @throws InvalidArgumentException
     */
    public static function validationIntencaoAno($anoIntencao)
    {
        if (! is_int($anoIntencao)) {
            throw new InvalidArgumentException('O $anoIntencao not is integer');
        }
    }

    /**
     * Um construtor privado; previne a criação direta do objeto.
     */
    private function __construct()
    {}

    /**
     * Previne que o usuário clone a instância.
     */
    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }

    /**
     * Sql all Intencao Item.
     *
     * @param int $intencaoSequencial
     * @param int $intencaoAno
     *
     * @return string
     */
    public static function sqlAllIntencaoItem($sequencialIntencao, $anoIntencao)
    {
        $sql = '
            SELECT
                i.cintrpsequ ,
                i.cintrpsano ,
                i.citirpsequ ,
                i.cmatepsequ ,
                m.ematepdesc ,
                i.cservpsequ ,
                s.eservpdesc ,
                i.aitirporde ,
                i.vitirpvues ,
                i.eitirpdescmat ,
                i.eitirpdescse ,
                i.tintrpdcad ,
                i.cusupocodi ,
                i.titirpulat
            FROM
                sfpc.tbitemintencaoregistropreco i
            LEFT JOIN
                sfpc.tbmaterialportal m
                ON m.cmatepsequ = i.cmatepsequ
            LEFT JOIN
                sfpc.tbservicoportal s
                ON s.cservpsequ = i.cservpsequ
            WHERE
                i.cintrpsequ = %d
                AND i.cintrpsano =  %d
            ORDER BY
                i.aitirporde ASC
        ';

        return sprintf($sql, $sequencialIntencao, $anoIntencao);
    }

    /**
     * Seleciona os dados da intenção passando o sequencial e o ano da intenção.
     *
     * @param int $sequencialIntencao
     * @param int $anoIntencao
     *
     * @return string
     *
     * @throws InvalidArgumentException
     */
    public static function sqlSelectIntencao($sequencialIntencao, $anoIntencao)
    {
        self::validationIntencaoSequencial($sequencialIntencao);
        self::validationIntencaoAno($anoIntencao);

        $sql = '
            SELECT DISTINCT a.cintrpsequ,  a.cintrpsano, a.tintrpdlim, a.xintrpobje, a.xintrpobse, a.fintrpsitu,
                a.tintrpdcad, a.cusupocodi, a.tintrpulat
            FROM
                sfpc.tbintencaoregistropreco a
            INNER JOIN sfpc.tbintencaorporgao b
                ON a.cintrpsequ = b.cintrpsequ
                    AND a.cintrpsano = b.cintrpsano
            INNER JOIN sfpc.tborgaolicitante c
                ON b.corglicodi = c.corglicodi
            WHERE
                a.cintrpsequ = %d AND a.cintrpsano = %d
            ORDER BY a.cintrpsequ
        ';

        return sprintf($sql, $sequencialIntencao, $anoIntencao);
    }

    /**
     * Sql Update situacao intencao.
     *
     * @param int $sequencialIntencao
     * @param int $anoIntencao
     * @param string $situacao
     *
     * @return string
     *
     * @throws InvalidArgumentException
     */
    public static function sqlUpdateSituacaoIntencao($sequencialIntencao, $anoIntencao, $situacao)
    {
        self::validationIntencaoSequencial($sequencialIntencao);
        self::validationIntencaoAno($anoIntencao);

        if (! is_string($situacao)) {
            throw new InvalidArgumentException('O $situacao not is string');
        }

        $sql = "
            UPDATE
                sfpc.tbintencaoregistropreco
            SET
                fintrpsitu = '%s', tintrpulat = now()
            WHERE
                cintrpsequ = %d
                AND cintrpsano = %d
        ";

        return sprintf($sql, strtoupper2($situacao), $sequencialIntencao, $anoIntencao);
    }

    /**
     *
     * @param int $sequencialIntencao
     * @param int $anoIntencao
     *
     * @return string
     */
    public static function sqlDeleteIntencao($sequencialIntencao, $anoIntencao)
    {
        self::validationIntencaoSequencial($sequencialIntencao);
        self::validationIntencaoAno($anoIntencao);

        // Itens da resposta
        $sqlItensResposta = ' DELETE FROM sfpc.tbitemrespostaintencaorp ';
        $sqlItensResposta .= " WHERE cintrpsequ = $sequencialIntencao AND cintrpsano = $anoIntencao; ";

        // Deleta respostas
        $sqlRespostas = ' DELETE FROM sfpc.tbrespostaintencaorp ';
        $sqlRespostas .= " WHERE cintrpsequ = $sequencialIntencao AND cintrpsano = $anoIntencao; ";

        // Deleta itens
        $sqlItens = ' DELETE FROM sfpc.tbitemintencaoregistropreco ';
        $sqlItens .= " WHERE cintrpsequ = $sequencialIntencao AND cintrpsano = $anoIntencao; ";

        // Deleta órgãos
        $sqlOrgaos = ' DELETE FROM sfpc.tbintencaorporgao ';
        $sqlOrgaos .= " WHERE cintrpsequ = $sequencialIntencao AND cintrpsano = $anoIntencao; ";

        // Deleta anexos
        $sqlAnexos = ' DELETE FROM sfpc.tbintencaoregistroprecoanexo ';
        $sqlAnexos .= " WHERE cintrpsequ = $sequencialIntencao AND cintrpsano = $anoIntencao; ";

        // Deleta intenção
        $sqlIntencao = ' DELETE FROM sfpc.tbintencaoregistropreco ';
        $sqlIntencao .= " WHERE cintrpsequ = $sequencialIntencao AND cintrpsano = $anoIntencao; ";

        return $sqlItensResposta . $sqlRespostas . $sqlItens . $sqlOrgaos . $sqlAnexos . $sqlIntencao;
    }

    /**
     *
     * @param int $sequencialIntencao
     * @param int $anoIntencao
     * @param string $situacaoResposta
     *
     * @return string
     */
    public static function sqlSelectRespostaIntencao($sequencialIntencao, $anoIntencao, $situacaoResposta = null, $orgao = null) {
        self::validationIntencaoSequencial($sequencialIntencao);
        self::validationIntencaoAno($anoIntencao);

        $sql  = "SELECT  CINTRPSEQU, CINTRPSANO, CORGLICODI, FRINRPSITU, TRINRPDCAD, ";
        $sql .= "       CUSUPOCODI, TRINRPULAT, XRINRPJUST ";
        $sql .= "FROM    SFPC.TBRESPOSTAINTENCAORP ";
        $sql .= "WHERE   CINTRPSEQU = " . $sequencialIntencao;
        $sql .= "       AND CINTRPSANO = " . $anoIntencao;

            if (! is_null($situacaoResposta)) {
                $sql .= " AND FRINRPSITU = '$situacaoResposta' ";
            }

            if (!is_null($orgao)) {
                $sql .= " AND CORGLICODI = " . $orgao;
            }

        return sprintf($sql, $sequencialIntencao, $anoIntencao);
    }

    /**
     * sql Select Item Intencao.
     *
     * @param int $sequencialIntencao
     * @param int $anoIntencao
     *
     * @return string
     */
    public static function sqlSelectItemIntencao($sequencialIntencao, $anoIntencao)
    {
        self::validationIntencaoSequencial($sequencialIntencao);
        self::validationIntencaoAno($anoIntencao);

        $sql = '
            select i.cmatepsequ, m.ematepdesc, i.eitirpdescmat, s.eservpdesc, i.cservpsequ, i.eitirpdescse,
                i.aitirporde, i.vitirpvues, m.fmatepgene,i.citirpsequ,i.cintrpsequ
            FROM sfpc.tbitemintencaoregistropreco i
            full outer JOIN
                sfpc.tbmaterialportal m
                ON i.cmatepsequ = m.cmatepsequ
            full outer JOIN
                sfpc.tbservicoportal s
                ON i.cservpsequ = s.cservpsequ
            WHERE
                i.cintrpsequ = %d
                AND i.cintrpsano = %d
                AND i.cmatepsequ IS NOT NULL
                OR i.cservpsequ IS NOT NULL
            ORDER BY i.aitirporde
        ';

        return sprintf($sql, $sequencialIntencao, $anoIntencao);
    }

    /**
     * sql Select Orgao Licitante Ativo.
     *
     * @return string
     */
    public static function sqlSelectOrgaosLicitantesAtivos()
    {
        return "
            SELECT DISTINCT a.corglicodi, b.eorglidesc
            FROM sfpc.tbcentrocustoportal a
            INNER JOIN
                sfpc.tborgaolicitante b
                ON a.corglicodi = b.corglicodi
            WHERE a.fcenpositu <> 'I'
            ORDER BY b.eorglidesc
        ";
    }

    /**
     *
     * @param int $sequencialIntencao
     * @param int $anoIntencao
     *
     * @return string
     */
    public static function sqlSelectOrgaoLicitanteIntencao($sequencialIntencao, $anoIntencao)
    {
        self::validationIntencaoSequencial($sequencialIntencao);
        self::validationIntencaoAno($anoIntencao);

        $sql = "
            SELECT CORGLICODI
            FROM SFPC.TBINTENCAORPORGAO
            WHERE
                CINTRPSEQU = %d
                AND CINTRPSANO = %d
                AND FINRPOSITU = 'A'
            ORDER BY CORGLICODI
        ";

        return sprintf($sql, $sequencialIntencao, $anoIntencao);
    }

    /**
     * Delete Item(ns) da Intenção.
     *
     * @param int $sequencialIntencao
     * @param int $anoIntencao
     * @param int $item
     *
     * @return string SQL (DELETE FROM ..)
     *
     * @see Para excluir um item especifico, informe o id do item (integer $item), caso contrário irá excluir todos
     *      os itens da intenção
     */
    public static function sqlDeleteItemIntencaoRegistroPreco($sequencialIntencao, $anoIntencao, $item)
    {
        self::validationIntencaoSequencial($sequencialIntencao);
        self::validationIntencaoAno($anoIntencao);

        // Deleta itens
        $sqlItens = '
            DELETE FROM
                sfpc.tbitemintencaoregistropreco
            WHERE
                cintrpsequ = %d AND cintrpsano = %d;
        ';

        if (! is_null($item)) {
            $sqlItens .= ' AND citirpsequ = %d ';

            return sprintf($sqlItens, $sequencialIntencao, $anoIntencao, $item);
        }

        return sprintf($sqlItens, $sequencialIntencao, $anoIntencao);
    }

    /**
     *
     * @param int $sequencialIntencao
     * @param int $anoIntencao
     */
    public static function sqlDeleteOrgaosIntencaoRegistroPreco($sequencialIntencao, $anoIntencao)
    {
        self::validationIntencaoSequencial($sequencialIntencao);
        self::validationIntencaoAno($anoIntencao);

        // Deleta órgãos
        $sqlOrgaos = '
            DELETE FROM
                sfpc.tbintencaorporgao
            WHERE
                cintrpsequ = %d AND cintrpsano = %d;
        ';

        return sprintf($sqlOrgaos, $sequencialIntencao, $anoIntencao);
    }

    /**
     * Sql Select Intencao.
     *
     * @param int $sequencialIntencao
     * @param int $anoIntencao
     * @param string $dataInicio
     * @param string $dataFim
     *
     * @return string [description]
     */
    public static function sqlSelectIntencaoByDataInicioAndDataFimAndGrupoUsuario($sequencialIntencao = null, $anoIntencao = null, $dataInicio = null, $dataFim = null, $grupoUsuario = null)
    {
        $codigoUsuario = $_SESSION['_cusupocodi_'];
        $anoAtual = date('Y');
        $sql = "
            SELECT DISTINCT a.cintrpsequ, a.cintrpsano, a.tintrpdlim, a.xintrpobje, a.tintrpdcad, a.cusupocodi,
                a.xintrpobse
            FROM
                sfpc.tbintencaoregistropreco a
            INNER JOIN
                sfpc.tbintencaorporgao b
                ON a.cintrpsequ = b.cintrpsequ
                    AND a.cintrpsano = b.cintrpsano
                     AND B.CORGLICODI IN
    (SELECT DISTINCT c.CORGLICODI
    FROM SFPC.TBCENTROCUSTOPORTAL c
    WHERE c.CORGLICODI IS NOT NULL AND c.ACENPOANOE = $anoAtual
    AND c.FCENPOSITU <> 'I'
    AND c.CCENPOSEQU IN
        (SELECT USU.CCENPOSEQU FROM SFPC.TBUSUARIOCENTROCUSTO USU
         WHERE USU.CUSUPOCODI =$codigoUsuario
         AND USU.fusucctipo = 'C')
    ORDER BY 1)
            WHERE
                1 = 1
        ";

        if (! is_null($sequencialIntencao)) {
            $sql .= " AND a.cintrpsequ = $sequencialIntencao ";
        }

        if (! is_null($anoIntencao)) {
            $sql .= " AND a.cintrpsano = $anoIntencao ";
        }

        if (! is_null($dataInicio) && is_null($dataFim)) {
            $sql .= " AND a.tintrpdcad >= '$dataInicio' ";
        }

        if (! is_null($dataFim) && is_null($dataInicio)) {
            $sql .= " AND a.tintrpdcad <= '$dataFim' ";
        }

        if (! is_null($dataInicio) && ! is_null($dataFim)) {
            $sql .= " AND a.tintrpdcad >= '$dataInicio' ";
            $sql .= " AND a.tintrpdcad <= '$dataFim' ";
        }

        $sql .= " AND a.fintrpsitu LIKE 'A' ";

        return $sql;
    }

    /**
     *
     * @return string
     */
    public static function sqlExistRespostaIntencao()
    {
        $sql = '
            SELECT a.cintrpsequ, a.cintrpsano, a.corglicodi
            FROM sfpc.tbrespostaintencaorp a
            WHERE a.cintrpsequ = ?
                AND a.cintrpsano = ?
                AND a.corglicodi = ?
        ';

        return $sql;
    }

    /**
     * Get itens da resposta pela a intencao, ano e orgao codigo.
     *
     * @param int $sequencialIntencao
     * @param int $anoIntencao
     * @param int $orgaoCodigo
     *
     * @return string
     */
    public static function sqlItensRespostaByIntencaoAnoOrgao($sequencialIntencao, $anoIntencao, $orgaoCodigo)
    {
        self::validationIntencaoSequencial($sequencialIntencao);

        self::validationIntencaoAno($anoIntencao);

        $sql = '
          SELECT
                irirp.cintrpsequ ,
                irirp.cintrpsano ,
                irirp.corglicodi ,
                irirp.citirpsequ ,
                irirp.airirpqtpr ,
                irirp.tirirpdcad ,
                irirp.cusupocodi ,
                irirp.tirirpulat ,
                iirp.cmatepsequ ,
                iirp.cservpsequ ,
                iirp.eitirpdescmat ,
                iirp.eitirpdescse ,
                iirp.vitirpvues ,
                material.ematepdesc ,
                servico.eservpdesc
            FROM
                sfpc.tbitemrespostaintencaorp irirp INNER JOIN sfpc.tbitemintencaoregistropreco iirp
                    ON iirp.cintrpsequ = irirp.cintrpsequ
                AND iirp.cintrpsano = irirp.cintrpsano
                AND iirp.citirpsequ = irirp.citirpsequ LEFT OUTER JOIN sfpc.tbmaterialportal material
                    ON iirp.cmatepsequ = material.cmatepsequ LEFT OUTER JOIN sfpc.tbservicoportal servico
                    ON iirp.cservpsequ = servico.cservpsequ
            WHERE
                irirp.cintrpsequ = %d
                AND irirp.cintrpsano = %d
                AND irirp.corglicodi = %d
            ORDER BY
                irirp.citirpsequ
        ';

        return sprintf($sql, $sequencialIntencao, $anoIntencao, $orgaoCodigo);
    }
}


/**
 */
class BaseEntidade
{

    /**
     * [getEntidade description].
     *
     * @param [type] $nomeTabela
     *            [description]
     *
     * @return [type] [description]
     * @deprecated use ClaDatabasePostgresql::getEntidade($nomeTabela)
     */
    public function getEntidade($nomeTabela)
    {
        $informacaoTabela = ClaDatabasePostgresql::getConexao()->tableInfo($nomeTabela);
        $entidade = array();

        foreach ($informacaoTabela as $value) {
            $name = $value['name'];
            $entidade[$name] = null;
        }

        return (object) $entidade;
    }

    /**
     * Retorna Entidade da tabela informada.
     *
     * @param string $nomeTabela
     *            [description]
     *
     * @return stdClass [description]
     */
    public static function retornaEntidade($nomeTabela = null)
    {
        assercao(! is_null($nomeTabela), 'o $nomeTabela deve ser informado!');
        $base = new self();

        return $base->getEntidade($nomeTabela);
    }
}
