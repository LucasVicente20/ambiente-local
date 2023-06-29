<?php 
#-----------------------------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# Data: 04/09/2018
# Objetivo: Tarefa Redmine 201677
#-----------------------------------------------------------------

class CR92 {

    public static function retornarItensMateriasAtaSarp()
    {
        $itens = array();

        if ($_SESSION['materialSarp'] != null) {
            $itens = $_SESSION['materialSarp'];
            unset($_SESSION['materialSarp']);
        }

        return $itens;
    }

    public static function retornarItensServicoAtaSarp()
    {
        $itens = array();

        if ($_SESSION['servicoSarp'] != null) {
            $itens = $_SESSION['servicoSarp'];
            unset($_SESSION['servicoSarp']);
        }

        return $itens;
    }

    public static function sqlVerificarCarpnosequ($Solicitacao) {
        $sql  = " SELECT sc.carpnosequ, sc.ctpcomcodi ";
        $sql .= " FROM SFPC.TBsolicitacaocompra sc ";
        $sql .= " WHERE sc.csolcosequ = " . $Solicitacao ;
        $sql .= " AND sc.csitsocodi in (1,5) ";
        $sql .= " AND sc.ctpcomcodi = 5 ";

        return $sql;
    }

    private function sqlQuantidadeItemAtaCarona($ata, $item, $isMaterial)
    {
        $sql = 'select sum(coei.acoeitqtat) as qtdTotalOrgao from sfpc.tbcaronaorgaoexterno coe';
        ' left outer join sfpc.tbcaronaorgaoexternoitem coei';
        ' on coe.ccaroesequ = coei.ccaroesequ';
        ' left outer join sfpc.tbitemataregistropreconova iarpn';
        ' on iarpn.carpnosequ = coe.carpnosequ';
        ' and iarpn.citarpsequ = coei.citarpsequ';
        ' where coe.carpnosequ =' . $ata;
        if ($isMaterial) {
            $sql = ' and iarpn.cmatepsequ =' . $item;
        } else {
            $sql = ' and iarpn.cservpsequ =' . $item;
        }

        return $sql;
    }

    private function sqlvalidarCondicaoSARPParticpante($orgao, $ata, $item, $isMaterial)
    {
        $sql = 'select sum(itp.apiarpqtat) as qtdTDSoli from sfpc.tbsolicitacaocompra s';
        $sql .= ' left outer join sfpc.tbitemsolicitacaocompra i';
        $sql .= ' on i.csolcosequ = s.csolcosequ';
        $sql .= ' left outer join sfpc.tbparticipanteatarp p';
        $sql .= ' on s.carpnosequ = p.carpnosequ';
        $sql .= ' left outer join sfpc.tbparticipanteitematarp itp';
        $sql .= ' on itp.carpnosequ = s.carpnosequ ';
        $sql .= ' and itp.carpnosequ = p.carpnosequ';
        $sql .= ' where s.carpnosequ =' . $ata;

        if (! empty($orgao)) {
            $sql .= ' and p.corglicodi =' . $orgao;
        }

        if ($isMaterial) {
            $sql .= ' and i.cmatepsequ =' . $item;
        } else {
            $sql .= ' and i.cservpsequ =' . $item;
        }

        return $sql;
    }

    private function sqlQuantidadeMaxAtaParticipante($orgao, $ata, $item, $isMaterial)
    {
        $sql = 'select sum(iarp.aitarpqtor) as qtdMaxAta from sfpc.tbparticipanteatarp p';
        $sql .= ' left outer join sfpc.tbitemataregistropreconova iarp';
        $sql .= ' on iarp.carpnosequ = p.carpnosequ';
        $sql .= ' where p.carpnosequ =' . $ata;
        $sql .= ' and p.corglicodi =' . $orgao;
        if ($isMaterial) {
            $sql .= ' and iarp.cmatepsequ =' . $item;
        } else {
            $sql .= ' and iarp.cservpsequ =' . $item;
        }

        return $sql;
    }

    /* Validação Retirada para entrega sem registro de preço, favor recolocar quando o ato de entregar */
    public static function validarCondicaoSARPCarona($orgao, $ata, $item, $isMaterial, $quantidadeInformada)
    {
        // $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), self::sqlvalidarCondicaoSARPParticpante(null, $ata, $item, $isMaterial));
        //
        // $resultado->fetchInto($quantidadeSolicitadaSemOrgao, DB_FETCHMODE_OBJECT);
        //
        // $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), self::sqlQuantidadeItemAtaCarona($ata, $item, $isMaterial));
        //
        // $resultado->fetchInto($quantidadeSolicitadaCarona, DB_FETCHMODE_OBJECT);
        //
        // if ($quantidadeSolicitadaSemOrgao > $quantidadeInformada) {
        // return false;
        // }
        //
        // if ($quantidadeSolicitadaCarona > (5 * $quantidadeSolicitadaSemOrgao)) {
        // return false;
        // }
        return true;
    }

    /* Validação Retirada para entrega sem registro de preço, favor recolocar quando o ato de entregar */
    public static function validarCondicaoSARPParticpante($orgao, $ata, $item, $isMaterial, $quantidadeInformada)
    {
        // $dao = Conexao();
        //
        // $resultado = executarSQL($dao, self::sqlvalidarCondicaoSARPParticpante($orgao, $ata, $item, $isMaterial));
        //
        // $resultado->fetchInto($quantidadeSolicitada, DB_FETCHMODE_OBJECT);
        //
        // $resultado = executarSQL($dao, self::sqlQuantidadeMaxAtaParticipante($orgao, $ata, $item, $isMaterial));
        //
        // $resultado->fetchInto($quantidadeTotalItem, DB_FETCHMODE_OBJECT);
        //
        // $qtdSolicitada = $quantidadeSolicitada->qtdTDSoli;
        // $qtdMaxAtaItem = $quantidadeTotalItem->qtdMaxAta;
        //
        // if ($qtdMaxAtaItem < ($qtdSolicitada + $quantidadeInformada)) {
        // return false;
        // }
        return true;
    }
}