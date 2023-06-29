<?php
// 220038--
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
 * @author     Pitang Agile TI <contato@pitang.com>
 * @copyright  2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license    http://www.php.net/license/3_01.txt PHP License 3.01
 *
 * @version   GIT: EMPREL-SAD-PORTAL-COMPRAS-REGISTRO-PRECO-BL-FUNC-20160609-0940
 */
abstract class UI_Abstrata implements UI_Interface
{

    /**
     * Armazena instancia de Template
     *
     * @var Template
     */
    private $template;

    /**
     * Armazena uma implementação Adaptacao_Interface
     *
     * @var Adaptacao_Interface
     */
    private $adaptacao;

    /**
     */
    protected function imprimeBlocoMensagem()
    {
        /* Exibe as diversas mensagens */
        if ($_SESSION['mensagemFeedback'] != null) {
            $mensagemExibir = "";

            $valores = $_SESSION['mensagemFeedback'];

            if (is_array($valores)) {
                foreach ($valores as $mensagem) {
                    $mensagemExibir .= $mensagem . " ";
                }
            } else {
                $mensagemExibir = $valores;
            }
            $tipo = isset($_SESSION['mensagemFeedbackTipo']) ? $_SESSION['mensagemFeedbackTipo'] : 0;
            $this->mensagemSistema($mensagemExibir, $tipo, 1);
            unset($_SESSION['mensagemFeedback']);
        }
    }

    /**
     */
    public function garbageCollection()
    {
        unset($_SESSION['item'], $_SESSION['intencaoItemOld'], $_SESSION['intencaoItem'], $_SESSION['anoProcesso'], $_SESSION['processoExterno'], $_SESSION['orgao'], $_SESSION['ano'], $_SESSION['processo'], $_SESSION['ata'], $_SESSION['Mens'], $_SESSION['ultimoFiltro'], $_SESSION['sequencialItemAta'], $_SESSION['numeroAta'], $_SESSION['Arquivos_Upload'], $_SESSION['Arquivos_Upload_banco'], $_SESSION['itens'], $_SESSION['mensagemFeedback'], $_SESSION['mensagemFeedbackTipo'], $_SESSION['documentos'], $_SESSION['ataExternaItem']);
    }

    /**
     * [tenario description]
     *
     * @param [type] $condicao
     *            [description]
     * @param [type] $verdadeiro
     *            [description]
     * @param [type] $falso
     *            [description]
     * @return [type] [description]
     *
     * @deprecated 201510030 deve sair logo do código
     */
    public function tenario($condicao, $verdadeiro, $falso)
    {
        return $condicao ? $verdadeiro : $falso;
    }

    /**
     *
     * @return the Template
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     *
     * @param Template $template
     */
    public function setTemplate(Template $template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     *
     * @return the Adaptacao_Interface
     */
    public function getAdaptacao()
    {
        return $this->adaptacao;
    }

    /**
     *
     * @param Adaptacao_Interface $adaptacao
     */
    public function setAdaptacao(Adaptacao_Interface $adaptacao)
    {
        $this->adaptacao = $adaptacao;

        return $this;
    }

    /**
     * limpa a mensagem do programa
     *
     * @return void
     */
    public function limparMensagemSistema()
    {
        unset($_SESSION['colecaoMensagemErro'], $_SESSION['mensagemFeedback'], $_SESSION['mensagemFeedbackTipo']);
        $this->getTemplate()->MENSAGEM_ERRO = null;
        $this->getTemplate()->clear('MENSAGEM_ERRO');
    }

    /**
     * Exibe a mensagem na tela do sistema
     *
     * @param string $mensagem
     *            Mensagem a ser exibida na tela
     * @param boolean $tipo
     *            use 1 = para sucesso e qualquer coisa para erro
     * @param integer $troca
     *            se desejar substituir a ultima virgula em 'e'
     *
     * @return void
     */
    public function mensagemSistema($mensagem, $tipo, $troca = 0)
    {
        assercao(! empty($mensagem), 'ERRO: $mensagem deve ser diferente de vazio');
        assercao(is_integer($tipo), 'ERRO: $tipo deve ser 0 ou 1');

        $this->getTemplate()->MENSAGEM_ERRO = ExibeMensStr($mensagem, $tipo, $troca);
        $this->getTemplate()->block('BLOCO_ERRO', true);

        $this->limparMensagemSistema();
    }

    /**
     *
     * @deprecated [<version>] [<description>]
     */
    public function blockErro($mensagem, $tipo = 1)
    {
        $this->mensagemSistema($mensagem, $tipo);
    }

    public function setMensagemFeedBack($mensagem, $tipo = 0, $troca = 0)
    {
        $_SESSION['mensagemFeedback'] = $mensagem;
        $_SESSION['mensagemFeedbackTipo'] = $tipo;
    }
}
