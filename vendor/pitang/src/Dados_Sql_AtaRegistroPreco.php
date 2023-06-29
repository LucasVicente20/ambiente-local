<?php
// 220038--
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
 * @category  Pitang_Registro_Preco
 * @package   registropreco
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 * @version   GIT: v1.18.0-17-g9920068
 */

/**
 */
class Dados_Sql_AtaRegistroPreco
{

    /**
     * [sqlSelecionarAtaRegistro description]
     *
     * @param ArrayObject $entidade
     *            [description]
     * @return [type] [description]
     */
    public static function sqlSelecionarAtaRegistro($entidade)
    {
        $identificadorGrupo = $entidade['identificadorGrupo'];

        $vigencia = $entidade['tipoAta'] == 'I' ? 'arpi.aarpinpzvg' : 'arpi.aarpexpzvg';
        $situacao = $entidade['tipoAta'] == 'I' ? 'arpi.farpinsitu' : 'arpi.farpexsitu';

        $sql = "SELECT DISTINCT (arpn.tarpnoincl + ($vigencia || ' month')::INTERVAL) AS vigencia, arpn.carpnosequ, $situacao";

        if ($entidade['tipoAta'] == 'I') {
            $sql .= ",arpi.cgrempcodi, arpi.clicpoproc,arpi.alicpoanop,arpi.ccomlicodi,arpi.earpinobje,arpi.corglicodi,cl.ecomlidesc, ol.eorglidesc,";
        } elseif ($entidade['tipoAta'] == 'E') {
            $sql .= ",arpi.earpexorgg,arpi.earpexproc, arpi.aarpexanon,arpi.earpexobje,";
        }

        $sql .= "arpn.carpnotiat FROM sfpc.tbataregistropreconova arpn";

        if ($entidade['tipoAta'] == 'I') {
            $sql .= " JOIN sfpc.tbataregistroprecointerna arpi";
            $sql .= " ON arpi.carpnosequ = arpn.carpnosequ";
        } elseif ($entidade['tipoAta'] == 'E') {
            $sql .= " JOIN sfpc.tbataregistroprecoexterna arpi";
            $sql .= " ON arpi.carpnosequ = arpn.carpnosequ";
        }

        $sql .= " JOIN sfpc.tbparticipanteatarp parp";
        $sql .= " ON parp.carpnosequ = arpn.carpnosequ";
        $sql .= " JOIN sfpc.tbitemataregistropreconova iarpn";
        $sql .= " ON iarpn.carpnosequ = arpn.carpnosequ";

        if (! empty($entidade['material'])) {
            $sql .= " LEFT JOIN sfpc.tbmaterialportal mp";
            $sql .= " ON mp.cmatepsequ = iarpn.cmatepsequ";

            if ($identificadorGrupo == 'M') {
                $sql .= " JOIN sfpc.tbsubclassematerial scm";
                $sql .= " ON scm.csubclsequ = mp.csubclsequ";
                $sql .= " JOIN sfpc.tbclassematerialservico cms";
                $sql .= " ON (cms.cclamscodi = scm.cclamscodi)";
            }
        }
        if (! empty($entidade['servico'])) {
            $sql .= " JOIN sfpc.tbservicoportal sp";
            $sql .= " ON sp.cservpsequ = iarpn.cservpsequ";
            if ($identificadorGrupo == 'S') {
                $sql .= " JOIN sfpc.tbsubclassematerial scm";
                $sql .= " ON scm.csubclsequ = mp.csubclsequ";
            }
        }

        if ($entidade['tipoAta'] == 'I') {
            $sql .= " JOIN sfpc.tbcomissaolicitacao cl";
            $sql .= " ON cl.ccomlicodi = arpi.ccomlicodi";
            $sql .= " JOIN sfpc.tborgaolicitante ol";
            $sql .= " ON ol.corglicodi = arpi.corglicodi";
        }
        $sql .= " WHERE 1 = 1";
        if (! empty($entidade['tipoAta'])) {
            $sql .= " AND arpn.carpnotiat = '%s'";
            $sql = sprintf($sql, $entidade['tipoAta']);
        }
        if (! empty($entidade['numeroAta'])) {
            $sql .= " AND arpn.carpnosequ = %d ";
            $sql = sprintf($sql, $entidade['numeroAta']);
        }

        if (! empty($entidade['processo'])) {
            $sql .= " AND arpi.clicpoproc = %d ";
            $sql = sprintf($sql, $entidade['processo']);
        }

        if (! empty($entidade['ano'])) {
            $sql .= " AND arpi.alicpoanop = %s ";
            $sql = sprintf($sql, $entidade['ano']);
        }

        if (! empty($entidade['orgaoGerenciador'])) {
            $sql .= " AND arpi.corglicodi = %d ";
            $sql = sprintf($sql, $entidade['orgaoGerenciador']);
        }

        if (! empty($entidade['orgaoParticipante'])) {
            $sql .= " AND parp.corglicodi = %d ";
            $sql = sprintf($sql, $entidade['orgaoParticipante']);
        }

        if (! empty($entidade['fornecedor'])) {
            $sql .= " AND arpi.aforcrsequ = %d ";
            $sql = sprintf($sql, $entidade['fornecedor']);
        }

        if (! empty($entidade['material'])) {
            $sql .= " AND iarpn.cmatepsequ = %d ";
            $sql = sprintf($sql, $entidade['material']);
        }

        if (! empty($entidade['servico'])) {
            $sql .= " AND iarpn.cservpsequ = %d ";
            $sql = sprintf($sql, $entidade['servico']);
        }

        if (! empty($entidade['identificadorGrupo']) && (! empty($entidade['material']) || ! empty($entidade['servico']))) {
            $sql .= " AND cms.cgrumscodi = %d ";
            $sql = sprintf($sql, $entidade['identificadorGrupo']);
        }

        if (empty($isativo) && ! empty($entidade['material'])) {
            $sql .= " AND mp.cmatepsitu = 'A'";
        }

        if (empty($isativo) && ! empty($entidade['servico'])) {
            $sql .= " AND sp.cservpsitu = 'A'";
        }

        if (! empty($entidade['vigentes'])) {
            $sql .= " AND CAST((EXTRACT(DAY FROM NOW() - arpn.tarpnoincl)/365)*12 AS int) < $vigencia";
        }

        if ($entidade['tipoAta'] == 'I') {
            $sql .= " GROUP BY arpi.cgrempcodi,arpi.corglicodi,ol.eorglidesc,arpn.carpnotiat,arpi.clicpoproc,arpi.farpinsitu,vigencia,arpi.alicpoanop,arpi.ccomlicodi,arpi.earpinobje,arpn.carpnosequ,cl.ecomlidesc";
        } else {
            $sql .= " GROUP BY arpi.earpexorgg,arpi.earpexproc, arpn.carpnotiat,arpi.aarpexanon,arpi.earpexobje,vigencia,arpn.carpnosequ,$situacao";
        }
        $sql .= " ORDER BY arpn.carpnosequ";

        return $sql;
    }
}
