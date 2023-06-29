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
 * ClaHelper.
 */
class ClaHelper
{
    /**
     * Um construtor privado; previne a criação direta do objeto.
     */
    private function __construct()
    {
    }

    /**
     * Previne que o usuário clone a instância.
     */
    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }

    /**
     * @param mixed $data
     *
     * @return string
     */
    public static function converterDataBrParaBanco($data)
    {
        $dataBr = explode('/', $data);

        return $dataBr[2].'-'.$dataBr[1].'-'.$dataBr[0];
    }

    /**
     * Converte data formato banco para data brasileira.
     *
     * @param mixed  $data
     * @param string $exibirHora
     *
     * @return string
     */
    public static function converterDataBancoParaBr($data, $exibirHora = false)
    {
        $dataHoraBanco = explode(' ', $data);

        $arrayDataBanco = explode('-', $dataHoraBanco[0]);
        $dataBanco = $arrayDataBanco[2].'/'.$arrayDataBanco[1].'/'.$arrayDataBanco[0];

        if ($exibirHora && isset($dataHoraBanco[1])) {
            $dataBanco .= ' '.substr($dataHoraBanco[1], 0, 5);
        }

        return $dataBanco;
    }

    /**
     * Dado um formato, valida uma data.
     *
     * @param string $data
     * @param string $formato
     *
     * @return bool
     */
    public static function validationData($data, $formato = 'DD/MM/AAAA')
    {
        switch ($formato) {
            case 'DD-MM-AAAA':
            case 'DD/MM/AAAA':
                list($dia, $mes, $ano) = preg_split('/[-\.\/ ]/', $data);
                break;
            case 'AAAA/MM/DD':
            case 'AAAA-MM-DD':
                list($ano, $mes, $dia) = preg_split('/[-\.\/ ]/', $data);
                break;
            case 'AAAA/DD/MM':
            case 'AAAA-DD-MM':
                list($ano, $dia, $mes) = preg_split('/[-\.\/ ]/', $data);
                break;
            case 'MM-DD-AAAA':
            case 'MM/DD/AAAA':
                list($mes, $dia, $ano) = preg_split('/[-\.\/ ]/', $data);
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
                throw new Exception('Formato de data inválido');
                break;
        }

        return checkdate($mes, $dia, $ano);
    }

    /**
     * Formatar com Zeros.
     *
     * @param int $tamanho
     * @param int $valor
     *
     * @return string
     */
    public static function formatarComZeros($tamanho, $valor)
    {
        $formatador = '1';
        for ($i = 0; $i < $tamanho; ++$i) {
            $formatador = $formatador.'0';
        }

        return substr($valor + (int) $formatador, 1);
    }

    /**
     * [DateTimeFormat description].
     *
     * @param string $data
     *                     no formato dd/mm/aaaa
     *
     * @return DateTime
     */
    public static function dateTimeFormat($data)
    {
        list($day, $month, $year) = sscanf($data, '%02d/%02d/%04d');

        return new DateTime("$year-$month-$day");
    }
}
