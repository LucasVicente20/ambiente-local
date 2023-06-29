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
 * @category  Pitang_Registro_Preco
 *
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 *
 * @version   GIT:
 */

/**
 * Auxilia a Camada UI na renderização de elementos
 *
 * @author Pitang Agile TI <contato@pitang.com>
 *        
 */
class RegistroPreco_UI_Helper
{
    /**
     * Monta um elemento select com processos agrupados por comissão
     *
     * @param array $processos
     * @param string $processoSelecionado
     * @param string $nameElement
     * @param string $idElement
     * @param string $cssClass
     */
    public static function renderizarProcessosAgrupadosPorComissao(array $processos, $processoNumeracao = null,
        $nameElement = '', $idElement = '', $cssClass = '')
    {
        $elementoSelect = new Element('select');
        $elementoSelect->set('name', $nameElement);
        $elementoSelect->set('id', $idElement);
        $elementoSelect->set('class', $cssClass);
        
        $optionDefault = new Element('option');
        $optionDefault->set('value', '-1');
        $optionDefault->set('text', 'Selecione um processo');
        
        $elementoSelect->inject($optionDefault);
        
        if (!empty($processos)) {
            $ultCodComiss = null;
            $optgroup = null;
            foreach ($processos as $processo) {
                if ($ultCodComiss != $processo->ccomlicodi) {
                    $optgroup = new Element('optgroup');
                    $optgroup->set('label', $processo->ecomlidesc);
                }
                
                $option = new Element('option');
                $option->set('value', $processo->clicpoproc);
                $option->set('text', str_pad($processo->clicpoproc, 4, '0', STR_PAD_LEFT) . '/' . $processo->alicpoanop);
                
                // Vendo se a opção atual deve ter o atributo "selected"
                if ($processoNumeracao == $processo->clicpoproc) {
                    $option->set('selected', 'selected');
                }
                
                $optgroup->inject($option);
                
                if ($ultCodComiss != $processo->ccomlicodi) {
                    $ultCodComiss = $processo->ccomlicodi;
                    $elementoSelect->inject($optgroup);
                }
            }
        }
        
        return $elementoSelect->build();
    }
}
