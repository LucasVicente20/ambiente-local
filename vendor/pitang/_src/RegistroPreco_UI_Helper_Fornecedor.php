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
 * @category  Pitang_Registro_Preco
 *
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 *
 * @version   GIT:
 */

/**
 * Auxilia a Camada UI na renderização de dados
 *
 * @author Pitang Agile TI <contato@pitang.com>
 *
 */
class RegistroPreco_UI_Helper_Fornecedor
{
    /**
     * Mapeamento para padrão de informação
     *
     * @param stdClass $fornecedor
     * @return stdClass
     */
    public static function mapear($linha)
    {
        $fornecedorOriginal = new stdClass();
        $fornecedorOriginal->fornecedorOriginal = $linha->nforcrrazs;
        $fornecedorOriginal->numeroInscricaoFornecedor = (empty($linha->aforcrccgc)) ? $linha->aforcrccpf : $linha->aforcrccgc;
        $fornecedorOriginal->logradouroFornecedor = $linha->eforcrlogr;
        $fornecedorOriginal->numeroLogradouroFornecedor = $linha->aforcrnume;
        $fornecedorOriginal->bairroFornecedor = $linha->eforcrbair;
        $fornecedorOriginal->cidadeFornecedor = $linha->nforcrcida;
        $fornecedorOriginal->estadoFornecedor = $linha->cforcresta;

        return $fornecedorOriginal;
    }

    /**
     * Trata dados do fornecedor para exibir na tela
     *
     * @param stdClass $fornecedor
     * @return string
     *
     */
    public static function trataDadosDoFornecedorDaAta($fornecedor)
    {
        $cpfCnpj = $fornecedor->numeroInscricaoFornecedor;
        $cpfCnpjFormatado = (strlen($cpfCnpj) == 11) ? FormataCPF($cpfCnpj) : FormataCNPJ($cpfCnpj);
        $dadosFornecedor = $cpfCnpjFormatado . ' - ' . $fornecedor->fornecedorOriginal;
        if (! empty($fornecedor->logradouroFornecedor)) {
            $dadosFornecedor .= '<br />' . $fornecedor->logradouroFornecedor;
            $dadosFornecedor .= ', ' .$fornecedor->numeroLogradouroFornecedor;
            $dadosFornecedor .= ', ' . $fornecedor->bairroFornecedor;
            $dadosFornecedor .= ' - ' . $fornecedor->cidadeFornecedor . '/' . $fornecedor->estadoFornecedor;
        }

        return $dadosFornecedor;
    }
}
