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
 * @version   GIT: v1.16.1-445-g312229e
 */
class Helper_RegistroPreco
{
    /**
     *
     * @param integer $idSolicitacao
     */
    public static function getNumeroSolicitacaoCompra($idSolicitacao = null)
    {
        $db = Conexao();

        assercao(! is_null($db), 'Variável de banco de dados não foi inicializada');
        assercao(! is_null($idSolicitacao), "Parâmetro 'idSolicitacao' requerido");

        $sql = "
        SELECT DISTINCT
            ccenpocorg,
            ccenpounid,
            csolcocodi,
            asolcoanos
        FROM
            sfpc.tbsolicitacaocompra scc
        INNER JOIN
            sfpc.tbcentrocustoportal cc
            ON scc.ccenposequ = cc.ccenposequ
        WHERE csolcosequ = %d
        ";

        $linha = resultLinhaUnica(executarSQL($db, sprintf($sql, $idSolicitacao)));
        $resposta = array();
        $resposta['orgaoSofin'] = $linha[0];
        $resposta['unidadeSofin'] = $linha[1];
        $resposta['solicitacao'] = $linha[2];
        $resposta['anoSolicitacao'] = $linha[3];

        $numeroSolicitacao = sprintf('%02s', $resposta['orgaoSofin']);
        $numeroSolicitacao .= sprintf('%02s', $resposta['unidadeSofin']);
        $numeroSolicitacao .= '.';
        $numeroSolicitacao .= sprintf('%04s', $resposta['solicitacao']);
        $numeroSolicitacao .= '.';
        $numeroSolicitacao .= $resposta['anoSolicitacao'];

        $resposta['numeroSolicitacao'] = $numeroSolicitacao;

        return $resposta['numeroSolicitacao'];
    }

    /**
     *
     * @param integer $codigoOrgaoLicitante
     * @param integer $codigoSolicitacaoCompra
     * @param integer $sequencialAta
     * @param integer $anoProcessoLicitatorio
     */
    public static function getNumeroAta($codigoOrgaoLicitante, $codigoSolicitacaoCompra, $sequencialAta, $anoProcessoLicitatorio)
    {
        assercao(!is_null($codigoOrgaoLicitante),       "Parâmetro $codigoOrgaoLicitante requerido");
        assercao(!is_null($codigoSolicitacaoCompra),    "Parâmetro $codigoSolicitacaoCompra requerido");
        assercao(!is_null($sequencialAta),              "Parâmetro $sequencialAta requerido");
        assercao(!is_null($anoProcessoLicitatorio),     "Parâmetro $anoProcessoLicitatorio requerido");
        $numeroAta          = self::getNumeroSolicitacaoCompra($codigoSolicitacaoCompra);
        $valoresExploded    = explode('.', $numeroAta);
        $valorUnidadeOrc    = substr($valoresExploded[0], 2, 2);

        $valorAta = str_pad($codigoOrgaoLicitante, 2, '0', STR_PAD_LEFT);
        $valorAta .= $valorUnidadeOrc . '.';
        $valorAta .= str_pad($sequencialAta, 4, '0', STR_PAD_LEFT);
        $valorAta .= '/';
        $valorAta .= $anoProcessoLicitatorio;

        return $valorAta;
    }

    /**
     *
     * @param integer $codigoProcesso
     * @param integer $anoLicitacao
     * @param integer $codigoGrupo
     * @param integer $codigoComissao
     * @param integer $codigoOrgaoLicitante
     */
    public static function getNumeroSolicitacaoLicitacaoCompra($codigoProcesso, $anoLicitacao, $codigoGrupo, $codigoComissao, $codigoOrgaoLicitante)
    {
        assercao(! is_null($codigoProcesso), "Parâmetro $codigoProcesso requerido");
        assercao(! is_null($anoLicitacao), "Parâmetro $anoLicitacao requerido");
        assercao(! is_null($codigoGrupo), "Parâmetro $codigoGrupo requerido");
        assercao(! is_null($codigoComissao), "Parâmetro $codigoComissao requerido");
        assercao(! is_null($codigoOrgaoLicitante), "Parâmetro $codigoOrgaoLicitante requerido");

        $sql = "
            SELECT
                csolcosequ,
                clicpoproc,
                alicpoanop,
                cgrempcodi,
                ccomlicodi,
                corglicodi,
                cusupocodi,
                tsolclulat
            FROM
                sfpc.tbsolicitacaolicitacaoportal
            WHERE
                clicpoproc = %d
                AND alicpoanop = %d
                AND cgrempcodi = %d
                AND ccomlicodi = %d
                AND corglicodi = %d
        ";

        $resultado = ClaDatabasePostgresql::executarSQL(sprintf($sql, $codigoProcesso, $anoLicitacao, $codigoGrupo, $codigoComissao, $codigoOrgaoLicitante));

        ClaDatabasePostgresql::hasError($resultado);

        return current($resultado);
    }

    /**
     *
     * @param integer $sequencialAta
     *
     * @return stdClass
     */
    public static function getFornecedorDaAtaInterna($sequencialAta)
    {
        assercao(! is_null($sequencialAta), "Parâmetro '$sequencialAta' requerido");
        $sql = "
            SELECT
    				forn.*
			FROM
			    sfpc.tbataregistroprecointerna arp
			INNER JOIN sfpc.tbfornecedorcredenciado forn ON forn.aforcrsequ = arp.aforcrsequ
			AND arp.carpnosequ = %d";

        $resultado = executarSQL(Conexao(), sprintf($sql, $sequencialAta));
        $fornecedor = null;
        $resultado->fetchInto($fornecedor, DB_FETCHMODE_OBJECT);

        return $fornecedor;
    }

    public static function getFornecedorDaAtaExterna($sequencialAta)
    {
        assercao(! is_null($sequencialAta), "Parâmetro '$sequencialAta' requerido");
        $sql = "
            SELECT
    				forn.*
			FROM
			    sfpc.tbataregistroprecoexterna arp
			INNER JOIN sfpc.tbfornecedorcredenciado forn ON forn.aforcrsequ = arp.aforcrsequ
			AND arp.carpnosequ = %d";

        $resultado = executarSQL(Conexao(), sprintf($sql, $sequencialAta));
        $fornecedor = null;
        $resultado->fetchInto($fornecedor, DB_FETCHMODE_OBJECT);

        return $fornecedor;
    }

    /**
     *
     * @param integer $cpfCnpj
     * @param string $fornecedorOriginal
     * @param string $logradouroFornecedor
     * @param string $numeroLogradouroFornecedor
     * @param string $bairroFornecedor
     * @param string $cidadeFornecedor
     * @param string $estadoFornecedor
     */
    public static function montarDadosDoFornecedorDaAta($cpfCnpj, $fornecedorOriginal, $logradouroFornecedor, $numeroLogradouroFornecedor, $bairroFornecedor, $cidadeFornecedor, $estadoFornecedor)
    {
        assercao(! is_null($cpfCnpj), "Parâmetro '$cpfCnpj' requerido");
        assercao(! is_null($fornecedorOriginal), "Parâmetro '$fornecedorOriginal' requerido");
        //assercao(! is_null($logradouroFornecedor), "Parâmetro '$logradouroFornecedor' requerido");
        //assercao(! is_null($numeroLogradouroFornecedor), "Parâmetro '$numeroLogradouroFornecedor' requerido");
        //assercao(! is_null($bairroFornecedor), "Parâmetro '$bairroFornecedor' requerido");
        //assercao(! is_null($cidadeFornecedor), "Parâmetro '$cidadeFornecedor' requerido");
        //assercao(! is_null($estadoFornecedor), "Parâmetro '$estadoFornecedor' requerido");

        $cpfCnpjFormatado = (strlen($cpfCnpj) == 11) ? FormataCPF($cpfCnpj) : FormataCNPJ($cpfCnpj);
        $dadosFornecedor = $cpfCnpjFormatado . ' - ' . $fornecedorOriginal;

        if (! empty($logradouroFornecedor)) {
            $dadosFornecedor .= '<br>' . $logradouroFornecedor;
            $dadosFornecedor .= ', ' . $numeroLogradouroFornecedor;
            $dadosFornecedor .= ' - ' . $bairroFornecedor;
            $dadosFornecedor .= ' - ' . $cidadeFornecedor . '/' . $estadoFornecedor;
        }

        return $dadosFornecedor;
    }
}
