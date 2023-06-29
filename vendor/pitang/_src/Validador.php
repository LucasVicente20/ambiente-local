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
class Validador extends FormValidator
{

    private $especificacao;

    private function montarMensagem($elemento)
    {
        $elem = new Element('a');
        $elem->set('class', 'titulo2');
        $elem->set('text', $elemento['text']);
        $elem->set('href', $elemento['href']);
        return $elem->build();
    }

    /**
     *
     * @param array $especificacao
     *            um array com a seguinte estrutura
     * @example
     *
     */
    public function __construct($especificacao)
    {
        $validations = array();
        $required = array();
        $sanitize = array();

        foreach ($especificacao as $spec) {

            if (isset($spec['field'])) {
                $this->especificacao[$spec['field']] = $spec;
                $validations[$spec['field']] = $spec['validations'];
                $required[$spec['field']] = $spec['required'];
                $sanitize[$spec['field']] = $spec['sanitize'];
            }
        }

        parent::__construct($validations, $required, $sanitize);
    }

    /**
     */
    public function montarMensagemErro()
    {
        $getErrors = $this->getErrors();

        if (! empty($getErrors)) {

            $mensagens = array();
            foreach ($getErrors as $key => $value) {
                if (! isset($mensagens[$key])) {
                    $mensagens[$key] = $this->especificacao[$key];
                }
            }
            $errors = array();
            $cont = 0;
            $mensagemExibicao = "Informe: ";
            foreach ($mensagens as $elemento) {
                $mensagemMontada = $this->montarMensagem($elemento);
                if ($cont == 0) {
                    $mensagemExibicao .= $mensagemMontada;
                } else {
                    $mensagemExibicao .= ", " . $mensagemMontada;
                }
                $cont ++;
            }
            $_SESSION['mensagemFeedback'] = $mensagemExibicao;
        }
    }
}