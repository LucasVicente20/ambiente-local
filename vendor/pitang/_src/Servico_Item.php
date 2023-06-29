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
 * @version   GIT: EMPREL-SAD-PORTAL-COMPRAS-REGISTRO-PRECO-BL-FUNC-20160620-1355
 */

/**
 */
class Servico_Item
{

    /**
     *
     * @param string $strIndexSession
     */
    public static function clean($strIndexSession = 'intencaoItem')
    {
        $_SESSION[$strIndexSession] = null;
        unset($_SESSION[$strIndexSession]);
    }

    /**
     * Remove item da lista (session).
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

            if (count($_SESSION[$strIndexSession] > 0)) {
                self::collectorSessionItem($strIndexSession);
            }
        }
    }

    /**
     * Coleta dados do CadItemIncluir que foram setado em session['item']
     * e move para session['intencaoItem'].
     *
     * @param string $strIndexSession
     */
    public static function collectorSessionItem($strIndexSession = 'intencaoItem')
    {
        $existeItemNaSession = isset($_SESSION['item']) && count($_SESSION['item']) > 0;
        
        if ($existeItemNaSession) {
            $countItem = count($_SESSION['item']);
            if (!isset($_SESSION[$strIndexSession])) {
                $_SESSION[$strIndexSession] = array();
            }
            for ($i = 0; $i < $countItem; ++ $i) {
                $valorItemAdicionado = $_SESSION['item'][$i];
                if (isset($_SESSION[$strIndexSession]) && is_array($_SESSION[$strIndexSession])) {
                    // if (! in_array($valorItemAdicionado, $_SESSION[$strIndexSession])) {
                    // $_SESSION[$strIndexSession][] = $valorItemAdicionado;
                    // }
                    // } else {
                    $_SESSION[$strIndexSession][] = $valorItemAdicionado;
                }
            }
        }
        // cleaning for news itens
        $_SESSION['item'] = false;
        unset($_SESSION['item']);
        session_write_close();
    }
}
