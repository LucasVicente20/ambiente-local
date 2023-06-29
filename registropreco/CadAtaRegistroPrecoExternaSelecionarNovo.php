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
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 */

#-------------------------------------------------------------------------
# Alterado: Caio Coutinho - Pitang Agile TI
# Data:     04/04/2019
# Objetivo: Tarefa Redmine 214306
#-------------------------------------------------------------------------
# Alterado: João Madson
# Data:     06/07/2020
# Objetivo: CR #226853
#-------------------------------------------------------------------------

// 220038--

if (! @include_once dirname(__FILE__) . '/../bootstrap.php') {
    throw new Exception('Error Processing Request - Bootstrap', 1);
}

Seguranca();
//Madson, foi retirado durante a CR #226853 , pois evitava que qualquer mensagem fosse mostrada na tela vindo de outras telas e não deveria.
if(!empty($_SESSION['mensagemFeedback'])){    //Condição para chegagem de mensagem de erro indevidamente vindo de outra pag. e limpar o campo de mensagem. |Madson|
    if($_SESSION['conferePagina'] == 'carona' || $_SESSION['conferePagina'] == 'participante' || $_SESSION['conferePagina'] == 'selecionar' || $_SESSION['conferePagina'] == 'pesquisar'){
        unset($_SESSION['mensagemFeedback']);
        unset($_SESSION['conferePagina']);
    }
}
class RegistroPreco_Dados_CadAtaRegistroPrecoExternaSelecionarNovo extends Dados_Abstrata
{

    /**
     *
     * @param unknown $ano
     */
    public function consultarProcessoExterno($ano)
    {
        $sql = new Dados_Sql_AtaRegistroPrecoExterna();
        $resultado = ClaDatabasePostgresql::executarSQL($sql->sqlSelecionaTodasAtasPeloAno($ano));

        ClaDatabasePostgresql::hasError($resultado);

        return $resultado;
    }
}

class RegistroPreco_Negocio_CadAtaRegistroPrecoExternaSelecionarNovo extends Negocio_Abstrata
{

    /**
     *
     * {@inheritdoc}
     *
     * @see Negocio_Abstrata::getDados()
     */
    public function getDados()
    {
        $this->setDados(new RegistroPreco_Dados_CadAtaRegistroPrecoExternaSelecionarNovo());

        return parent::getDados();
    }
}

class RegistroPreco_Adaptacao_CadAtaRegistroPrecoExternaSelecionarNovo extends Adaptacao_Abstrata
{

    /**
     *
     * {@inheritdoc}
     *
     * @see Adaptacao_Abstrata::getNegocio()
     */
    public function getNegocio()
    {
        $this->setNegocio(new RegistroPreco_Negocio_CadAtaRegistroPrecoExternaSelecionarNovo());

        return parent::getNegocio();
    }
}

class RegistroPreco_UI_CadAtaRegistroPrecoExternaSelecionarNovo extends UI_Abstrata
{

    public function __construct()
    {
        $template = new TemplatePaginaPadrao('templates/CadAtaRegistroPrecoExternaSelecionarNovo.html', 'Registro de Preço > Ata Externa > Manter');
        $this->setTemplate($template);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see UI_Abstrata::getAdaptacao()
     */
    public function getAdaptacao()
    {
        $this->setAdaptacao(new RegistroPreco_Adaptacao_CadAtaRegistroPrecoExternaSelecionarNovo());

        return parent::getAdaptacao();
    }

    /**
     *
     * @param array $processos
     */
    public function plotarBlocoProcesso($processos = array())
    {
        $this->getTemplate()->PROCESSO_VALUE    = null;
        $this->getTemplate()->PROCESSO_TEXT     = 'Selecione um processo..';
        $this->getTemplate()->PROCESSO_SELECTED = 'selected';
        $this->getTemplate()->clear('PROCESSO_SELECTED');
        $this->getTemplate()->block('BLOCO_PROCESSO');

        foreach ($processos as $processo) {
            $fornecedor = !empty($processo->atual) ? $processo->atual : $processo->original; 
            $this->getTemplate()->PROCESSO_VALUE    = $processo->carpnosequ;
            $this->getTemplate()->PROCESSO_TEXT     = $processo->earpexproc . " - " . $fornecedor;

            // Caso esta não seja a opção atual, limpamos o valor da variável SELECTED
            $this->getTemplate()->clear('PROCESSO_SELECTED');
            $this->getTemplate()->block('BLOCO_PROCESSO');
        }
    }

    /**
     *
     * @param array $anos
     */
    public function plotarBlocoAno()
    {
        $this->imprimeBlocoMensagem();
        date_default_timezone_set('America/Recife');
        $anos = HelperPitang::carregarAno();
        $anoProcesso = isset($_POST['anoProcesso']) ? filter_var($_POST['anoProcesso'], FILTER_SANITIZE_NUMBER_INT) : 0;

        $this->getTemplate()->ANO_VALUE = null;
        $this->getTemplate()->ANO_TEXT = 'Selecione um ano...';
        $this->getTemplate()->ANO_SELECTED = 'selected';
        $this->getTemplate()->clear('ANO_SELECTED');
        $this->getTemplate()->block('BLOCO_ANO');

        foreach ($anos as $text) {
            $this->getTemplate()->ANO_VALUE = $text;
            $this->getTemplate()->ANO_TEXT = $text;

            // Caso esta não seja a opção atual, limpamos o val$or da variável SELECTED
            $this->getTemplate()->clear('ANO_SELECTED');
            // Vendo se a opção atual deve ter o atributo "selected"
            if ($anoProcesso == $text) {
                $this->getTemplate()->ANO_SELECTED = 'selected';
            }
            $this->getTemplate()->block('BLOCO_ANO');
        }
    }
}

/**
 * CadRegistroPrecoIntencaoIncluir.
 *
 * Class application
 */
class CadAtaRegistroPrecoExternaSelecionarNovo extends ProgramaAbstrato
{

    /**
     */
    private function proccessPrincipal()
    {
        $this->getUI()->plotarBlocoAno();

        $this->getUI()->plotarBlocoProcesso(array());

        $this->getUI()
            ->getTemplate()
            ->block('BLOCO_FORMULARIO_MANTER');
    }

    /**
     */
    private function processSelecionar()
    {
        if (! filter_var($_POST['anoProcesso'], FILTER_VALIDATE_INT)) {
            $this->getUI()->mensagemSistema('Ano deve ser selecionado', 0);
            $this->proccessPrincipal();

            return;
        }
        $anoProcesso = isset($_POST['anoProcesso']) ? filter_var($_POST['anoProcesso'], FILTER_SANITIZE_NUMBER_INT) : date('Y');
        if (! filter_var($_POST['processoexterno'], FILTER_VALIDATE_INT)) {
            $this->getUI()->plotarBlocoAno();
            $this->getUI()->mensagemSistema('O Processo deve ser selecionado', 0);
            $processos = $this->getUI()
                ->getAdaptacao()
                ->getNegocio()
                ->getDados()
                ->consultarProcessoExterno($anoProcesso);
            $this->getUI()->plotarBlocoProcesso($processos);

            $this->getUI()
                ->getTemplate()
                ->block('BLOCO_FORMULARIO_MANTER');

            return;
        }

        $processo = filter_var($_POST['processoexterno'], FILTER_SANITIZE_NUMBER_INT);

        $uri = 'CadAtaRegistroPrecoExternaSelecionarAlterar.php?' . http_build_query(array(
            'processo' => $processo,
            'ano' => $anoProcesso
        ));

        header('Location: ' . $uri);
        exit();
    }

    /**
     * Proccess Retirar.
     */
    private function atualizaProcessos()
    {
        $this->getUI()->plotarBlocoAno();
        date_default_timezone_set('America/Recife');
        $anoProcesso = isset($_POST['anoProcesso']) ? filter_var($_POST['anoProcesso'], FILTER_SANITIZE_NUMBER_INT) : date('Y');
        if (! filter_var($_POST['anoProcesso'], FILTER_VALIDATE_INT)) {}
        $processos = $this->getUI()
            ->getAdaptacao()
            ->getNegocio()
            ->getDados()
            ->consultarProcessoExterno($anoProcesso);
        $this->getUI()->plotarBlocoProcesso($processos);

        $this->getUI()
            ->getTemplate()
            ->block('BLOCO_FORMULARIO_MANTER');
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see ProgramaAbstrato::configuracao()
     */
    protected function configuracao()
    {
        $this->setUI(new RegistroPreco_UI_CadAtaRegistroPrecoExternaSelecionarNovo());
    }

    /**
     * Front Controller.
     */
    protected function frontController()
    {
        $botao = isset($_POST['Botao']) ? $_POST['Botao'] : 'Principal';
        switch ($botao) {
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
}

ProgramaAbstrato::iniciar(new CadAtaRegistroPrecoExternaSelecionarNovo());
