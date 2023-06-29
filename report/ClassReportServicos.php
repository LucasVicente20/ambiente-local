<?php
session_start();
require_once dirname(__FILE__) . '/../funcoes.php';

class ReportServicos  {
    public $con;

    public function __construct()
    {
        $this->con = conexao();
    }

    public function DadosReportPuroServico() {
        $sql = "SELECT DISTINCT iteml.alicpoanop AS anoprocesso, iteml.cservpsequ AS codigoservico, iteml.corglicodi AS orgaoprocesso, servp.eservpdesc AS descricaoresumida, iteml.eitelpdescse AS descricaodetalhada, iteml.eitelpmarc AS marca, ";
        $sql .= " iteml.clicpoproc AS numeroprocesso, iteml.eitelpmode AS modelo, REPLACE ( iteml.aitelpqtso, '.', ',' ) AS quantidade, REPLACE ( iteml.vitelpvlog, '.', ',' ) AS valorunitario, REPLACE ( ( ( iteml.aitelpqtso * iteml.vitelpvlog ) ), '.', ',' ) AS valortotal, ";
        $sql .= " grupo.egrumsdesc AS subgrupo, comis.ecomlidesc AS comissaolicitacao, iteml.clicpoproc AS numeroprocesso, orgli.eorglidesc AS orgaolicitante, modal.emodlidesc AS modalidade, licit.clicpocodl AS numprocessomodalidade, licit.alicpoanol AS anoprocessomodalidade, ";
        $sql .= " fasel.tfaseldata AS Datafase, informacoesAta.numataitem, informacoesAta.numataseq, informacoesAta.citarpsequ, informacoesAta.aitarporde, informacoesAta.cservpsequ, informacoesAta.citarpitel, CASE WHEN solco.fsolcocont = 'S' THEN ";
        $sql .= " 'SIM' ELSE'NÃO' END AS geracontrato, contr.ectrpcnumf AS numerocontrato, informacoesAta.NumAtaa FROM sfpc.tbitemlicitacaoportal iteml INNER JOIN ( SELECT lpad( CAST ( centro.ccenpocorg AS VARCHAR ), 2, '0' ) || lpad( CAST ( centro.ccenpounid AS VARCHAR ), 2, '0' ) || '.' || lpad( CAST ( atarpint.carpincodn AS VARCHAR ), 4, '0' ) || '/' || atarpint.aarpinanon AS NumAtaa, ";
        $sql .= " itemata.carpnosequ AS numataitem, itemata.citarpsequ, itemata.aitarporde, itemata.cservpsequ, itemata.citarpitel, atarpint.carpnosequ AS numataseq, atarpint.alicpoanop, atarpint.clicpoproc, atarpint.corglicodi, atarpint.ccomlicodi, ";
        $sql .= " atarpint.cgrempcodi FROM sfpc.tbataregistroprecointerna atarpint, sfpc.tbitemataregistropreconova itemata, ( SELECT DISTINCT ccenpocorg, ccenpounid, corglicodi FROM sfpc.tbcentrocustoportal WHERE acenpoanoe = 2022 AND fcenpositu = 'A' ) AS centro ";
        $sql .= " WHERE atarpint.carpnosequ = itemata.carpnosequ  AND atarpint.corglicodi = centro.corglicodi AND atarpint.farpincorp = 'S' ) AS informacoesAta ON iteml.alicpoanop = informacoesAta.alicpoanop AND iteml.clicpoproc = informacoesAta.clicpoproc AND iteml.corglicodi = informacoesAta.corglicodi ";
        $sql .= " AND iteml.ccomlicodi = informacoesAta.ccomlicodi AND iteml.cgrempcodi = informacoesAta.cgrempcodi AND informacoesAta.numataseq = informacoesAta.numataitem AND iteml.citelpsequ = informacoesAta.citarpitel LEFT JOIN sfpc.tbsolicitacaolicitacaoportal sollic ON iteml.alicpoanop = sollic.alicpoanop ";
        $sql .= " AND iteml.clicpoproc = sollic.clicpoproc AND iteml.corglicodi = sollic.corglicodi AND iteml.ccomlicodi = sollic.ccomlicodi AND iteml.cgrempcodi = sollic.cgrempcodi, sfpc.tbservicoportal servp, sfpc.tbgrupomaterialservico grupo, ";
        $sql .= " sfpc.tborgaolicitante orgli, sfpc.tbcomissaolicitacao comis, sfpc.tbsolicitacaocompra solco LEFT JOIN sfpc.tbcontratosfpc contr ON solco.csolcosequ = contr.csolcosequ, ( SELECT DISTINCT ccenpocorg, ccenpounid, corglicodi FROM sfpc.tbcentrocustoportal WHERE acenpoanoe = 2022 AND fcenpositu = 'A' ) AS centro, ";
        $sql .= " sfpc.tblicitacaoportal licit, sfpc.tbfaselicitacao fasel, sfpc.tbfases fases, sfpc.tbmodalidadelicitacao modal, ( SELECT fasel.CLICPOPROC AS Proc, fasel.ALICPOANOP AS Ano, fasel.CGREMPCODI AS Grupo, fasel.CCOMLICODI AS Comis, fasel.CORGLICODI AS Orgao, ";
        $sql .= " MAX ( fase.AFASESORDE ) AS Maior FROM SFPC.TBFASELICITACAO fasel, SFPC.TBFASES fase WHERE fasel.CFASESCODI = fase.CFASESCODI GROUP BY fasel.CLICPOPROC, fasel.ALICPOANOP, fasel.CGREMPCODI, fasel.CCOMLICODI, fasel.CORGLICODI ) AS maiorordem WHERE ";
        $sql .= " iteml.alicpoanop >= 2018 AND iteml.alicpoanop < 2022 AND iteml.vitelpvlog IS NOT NULL AND iteml.cservpsequ = servp.cservpsequ AND servp.cgrumscodi = grupo.cgrumscodi AND iteml.corglicodi = orgli.corglicodi AND iteml.ccomlicodi = comis.ccomlicodi AND sollic.csolcosequ = solco.csolcosequ ";
        $sql .= " AND iteml.corglicodi = centro.corglicodi AND iteml.alicpoanop = fasel.alicpoanop AND iteml.clicpoproc = fasel.clicpoproc AND iteml.corglicodi = fasel.corglicodi AND iteml.ccomlicodi = fasel.ccomlicodi AND iteml.cgrempcodi = fasel.cgrempcodi AND fasel.CFASESCODI = fases.CFASESCODI ";
        $sql .= " AND iteml.CLICPOPROC = maiorordem.Proc AND iteml.ALICPOANOP = maiorordem.Ano AND iteml.CGREMPCODI = maiorordem.Grupo AND iteml.CCOMLICODI = maiorordem.Comis AND iteml.CORGLICODI = maiorordem.Orgao AND fases.AFASESORDE = maiorordem.Maior AND Maior >= 96 ";
        $sql .= " AND iteml.alicpoanop = licit.alicpoanop AND iteml.clicpoproc = licit.clicpoproc AND iteml.corglicodi = licit.corglicodi AND iteml.ccomlicodi = licit.ccomlicodi AND iteml.cgrempcodi = licit.cgrempcodi AND licit.cmodlicodi = modal.cmodlicodi ORDER BY 1,2 ";
        
        $resultado = executarSQL($this->con, $sql);

        $dadosRetorno = array();
        while ($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
            $dadosRetorno[] = $retorno;
        }
        return $dadosRetorno;
    }
    // public function DadosReport() {
    //     $sql = "SELECT DISTINCT iteml.alicpoanop as anoprocesso, iteml.cmatepsequ as codigomaterial, iteml.corglicodi as orgaoprocesso, matep.ematepdesc as descricaoresumida, ";
    //     $sql .= " matep.ematepcomp as especificacao, iteml.eitelpdescmat as descricaodetalhada, unid.eunidmdesc as unidade, iteml.eitelpmarc as marca, ";
    //     $sql .= " iteml.clicpoproc  as numeroprocesso, matep.ematepcomp as especificacao, iteml.eitelpmode as modelo, replace(iteml.aitelpqtso,'.',',') as quantidade, ";
    //     $sql .= " replace(iteml.vitelpvlog,'.',',') as valorunitario, replace(((iteml.aitelpqtso*iteml.vitelpvlog)),'.',',') as valortotal, ";
    //     $sql .= " CASE WHEN grupo.fgrumstipm = 'C' THEN 'CONSUMO' ELSE 'PERMANENTE' END as Grupo, grupo.egrumsdesc as subgrupo, comis.ecomlidesc as comissaolicitacao, ";
    //     $sql .= " iteml.clicpoproc as numeroprocesso, orgli.eorglidesc as orgaolicitante, modal.emodlidesc as modalidade, licit.clicpocodl as numprocessomodalidade, ";
    //     $sql .= " licit.alicpoanol as anoprocessomodalidade, fasel.tfaseldata as Datafase, informacoesAta.numataitem, informacoesAta.numataseq, informacoesAta.citarpsequ, ";
    //     $sql .= " informacoesAta.aitarporde, informacoesAta.cmatepsequ, informacoesAta.citarpitel, informacoesAta.OrgaoParticipante, informacoesAta.somaparticipante, ";
    //     $sql .= " informacoesAta.OrgaoCarona, informacoesAta.somacarona, CASE WHEN solco.fsolcocont = 'S' THEN 'SIM' ELSE 'NÃO' END as geracontrato, contr.ectrpcnumf as numerocontrato,  informacoesAta.NumAtaa FROM ";
    //     $sql .= " sfpc.tbitemlicitacaoportal iteml LEFT JOIN (SELECT lpad(cast(centro.ccenpocorg as varchar),2,'0')||lpad(cast(centro.ccenpounid as varchar),2,'0') ";
    //     $sql .= " ||'.'||lpad(cast(atarpint.carpincodn as varchar),4,'0')|| '/'||atarpint.aarpinanon as NumAtaa, itemata.carpnosequ as numataitem, itemata.citarpsequ, ";
    //     $sql .= " itemata.aitarporde, itemata.cmatepsequ, itemata.citarpitel, atarpint.carpnosequ as numataseq, atarpint.alicpoanop, atarpint.clicpoproc, atarpint.corglicodi, ";
    //     $sql .= " atarpint.ccomlicodi, atarpint.cgrempcodi, resultadouniao.NumAta, resultadouniao.OrgaoParticipante, resultadouniao.somaparticipante, resultadouniao.OrgaoCarona, resultadouniao.somacarona, ";
    //     $sql .= " resultadouniao.ItemAta FROM sfpc.tbataregistroprecointerna atarpint, sfpc.tbitemataregistropreconova itemata, (select distinct ccenpocorg, ccenpounid, corglicodi ";
    //     $sql .= " from sfpc.tbcentrocustoportal where acenpoanoe = 2022 AND fcenpositu = 'A') as centro, ( SELECT uniaoP.NumAta, uniaoP.ItemAta, uniaoP.OrgaoParticipante, ";
    //     $sql .= " uniaoP.somaparticipante as somaparticipante, uniaoC.NumAtaC, uniaoC.ItemAtaC, uniaoC.OrgaoCarona, uniaoC.somacarona as somaCarona ";
    //     $sql .= " FROM ( (SELECT itemata.carpnosequ as NumAta, itemata.citarpsequ as ItemAta, resultadoscc.corglicodi AS OrgaoParticipante, SUM(resultadoscc.aitescqtso) AS somaparticipante ";
    //     $sql .= " FROM sfpc.tbitemataregistropreconova itemata LEFT JOIN (SELECT itemsol.csolcosequ, itemsol.carpnosequ, itemsol.citarpsequ, itemsol.aitescqtso, solco.corglicodi, ";
    //     $sql .= " solco.fsolcorpcp FROM sfpc.tbitemsolicitacaocompra itemsol, sfpc.tbsolicitacaocompra solco WHERE itemsol.csolcosequ = solco.csolcosequ AND solco.fsolcorpcp = 'P' ";
    //     $sql .= " AND solco.csitsocodi = 3 ) AS resultadoscc ON itemata.carpnosequ = resultadoscc.carpnosequ AND itemata.citarpsequ = resultadoscc.citarpsequ WHERE resultadoscc.aitescqtso IS NOT NULL ";
    //     $sql .= " GROUP BY NumAta, ItemAta, OrgaoParticipante ) UNION ( SELECT 	itemparticipanterp.carpnosequ as NumAta, itemparticipanterp.citarpsequ as ItemAta, itemparticipanterp.corglicodi AS OrgaoParticipante, ";
    //     $sql .= " SUM(itemparticipanterp.apiarpqtut) AS somaparticipante FROM sfpc.tbitemataregistropreconova itemata LEFT JOIN sfpc.tbparticipanteitematarp itemparticipanterp ON itemata.carpnosequ = itemparticipanterp.carpnosequ ";
    //     $sql .= " AND itemata.citarpsequ = itemparticipanterp.citarpsequ AND ( itemparticipanterp.apiarpqtut <> 0 AND itemparticipanterp.apiarpqtut <> 0.0000 ) WHERE itemparticipanterp.corglicodi IS NOT NULL ";
    //     $sql .= " GROUP BY NumAta, ItemAta, OrgaoParticipante ) )  AS uniaoP, ( (SELECT itemata.carpnosequ as NumAtaC, itemata.citarpsequ as ItemAtaC, resultadoscc.corglicodi AS OrgaoCarona, ";
    //     $sql .= " SUM(resultadoscc.aitescqtso) AS somacarona FROM sfpc.tbitemataregistropreconova itemata LEFT JOIN (SELECT itemsol.csolcosequ, itemsol.carpnosequ, itemsol.citarpsequ, itemsol.aitescqtso, ";
    //     $sql .= " solco.corglicodi, solco.fsolcorpcp FROM sfpc.tbitemsolicitacaocompra itemsol, sfpc.tbsolicitacaocompra solco WHERE itemsol.csolcosequ = solco.csolcosequ 	AND solco.fsolcorpcp = 'C' ";
    //     $sql .= " AND solco.csitsocodi = 3 ) AS resultadoscc ON itemata.carpnosequ = resultadoscc.carpnosequ AND itemata.citarpsequ = resultadoscc.citarpsequ WHERE resultadoscc.aitescqtso IS NOT NULL ";
    //     $sql .= " AND itemata.carpnosequ = 569 GROUP BY NumAtaC, ItemAtaC, OrgaoCarona ) UNION ( SELECT itemcaronarp.carpnosequ as NumAtaC, itemcaronarp.citarpsequ as ItemAtaC, itemcaronarp.corglicodi AS OrgaoCarona, ";
    //     $sql .= " SUM(itemcaronarp.aitcrpqtut) AS somacarona FROM 	sfpc.tbitemataregistropreconova itemata LEFT JOIN sfpc.tbitemcaronainternaatarp itemcaronarp ON itemata.carpnosequ = itemcaronarp.carpnosequ ";
    //     $sql .= " AND itemata.citarpsequ = itemcaronarp.citarpsequ AND ( itemcaronarp.aitcrpqtut <> 0 AND itemcaronarp.aitcrpqtut <> 0.0000 ) WHERE itemata.carpnosequ = 569 AND itemcaronarp.corglicodi IS NOT NULL ";
    //     $sql .= " GROUP BY NumAtaC, ItemAtaC, OrgaoCarona ) ) AS uniaoC ) as resultadouniao  WHERE atarpint.carpnosequ = itemata.carpnosequ AND   atarpint.carpnosequ = 569 AND   atarpint.corglicodi = centro.corglicodi ";
    //     $sql .= " AND   atarpint.carpnosequ = resultadouniao.NumAta AND   itemata.citarpitel  = resultadouniao.ItemAta AND  atarpint.carpnosequ = resultadouniao.NumAtaC AND  itemata.citarpitel  = resultadouniao.ItemAtaC ";
    //     $sql .= " ) as informacoesAta ON  iteml.alicpoanop = informacoesAta.alicpoanop AND iteml.clicpoproc = informacoesAta.clicpoproc AND iteml.corglicodi = informacoesAta.corglicodi AND iteml.ccomlicodi = informacoesAta.ccomlicodi ";
    //     $sql .= " AND iteml.cgrempcodi = informacoesAta.cgrempcodi AND informacoesAta.numataseq = informacoesAta.numataitem AND iteml.citelpsequ = informacoesAta.citarpitel LEFT JOIN sfpc.tbsolicitacaolicitacaoportal sollic ON iteml.alicpoanop = sollic.alicpoanop ";
    //     $sql .= " AND iteml.clicpoproc = sollic.clicpoproc AND iteml.corglicodi = sollic.corglicodi AND iteml.ccomlicodi = sollic.ccomlicodi AND iteml.cgrempcodi = sollic.cgrempcodi, sfpc.tbmaterialportal matep, sfpc.tbsubclassematerial  subcl, ";
    //     $sql .= " sfpc.tbunidadedemedida unid, sfpc.tbgrupomaterialservico grupo, sfpc.tborgaolicitante orgli, sfpc.tbcomissaolicitacao comis, sfpc.tbsolicitacaocompra solco LEFT JOIN sfpc.tbcontratosfpc contr ON solco.csolcosequ = contr.csolcosequ, ";
    //     $sql .= " (SELECT DISTINCT ccenpocorg, ccenpounid, corglicodi from sfpc.tbcentrocustoportal where acenpoanoe = 2022 and fcenpositu = 'A') as centro, sfpc.tblicitacaoportal licit, sfpc.tbfaselicitacao fasel, sfpc.tbfases fases, sfpc.tbmodalidadelicitacao modal, ";
    //     $sql .= " ( SELECT fasel.CLICPOPROC as Proc, fasel.ALICPOANOP as Ano, fasel.CGREMPCODI as Grupo, fasel.CCOMLICODI as Comis, fasel.CORGLICODI as Orgao, MAX(fase.AFASESORDE) as Maior FROM SFPC.TBFASELICITACAO fasel, SFPC.TBFASES fase WHERE fasel.CFASESCODI = fase.CFASESCODI ";
    //     $sql .= " GROUP BY fasel.CLICPOPROC, fasel.ALICPOANOP, fasel.CGREMPCODI, fasel.CCOMLICODI, fasel.CORGLICODI ) as maiorordem WHERE iteml.alicpoanop = 2018 AND iteml.clicpoproc = 14 AND iteml.ccomlicodi = 60 AND iteml.vitelpvlog is not null AND iteml.cmatepsequ = matep.cmatepsequ ";
    //     $sql .= " AND matep.csubclsequ = subcl.csubclsequ AND subcl.cgrumscodi = grupo.cgrumscodi AND iteml.corglicodi = orgli.corglicodi AND iteml.ccomlicodi = comis.ccomlicodi and matep.cunidmcodi = unid.cunidmcodi and sollic.csolcosequ = solco.csolcosequ and iteml.corglicodi = centro.corglicodi ";
    //     $sql .= " and iteml.alicpoanop = fasel.alicpoanop and iteml.clicpoproc = fasel.clicpoproc and iteml.corglicodi = fasel.corglicodi and iteml.ccomlicodi = fasel.ccomlicodi and iteml.cgrempcodi = fasel.cgrempcodi and fasel.CFASESCODI = fases.CFASESCODI and iteml.CLICPOPROC = maiorordem.Proc AND iteml.ALICPOANOP  = maiorordem.Ano ";
    //     $sql .= " and iteml.CGREMPCODI = maiorordem.Grupo  AND iteml.CCOMLICODI  = maiorordem.Comis and iteml.CORGLICODI = maiorordem.Orgao  AND fases.AFASESORDE  = maiorordem.Maior and Maior >= 96 and iteml.alicpoanop = licit.alicpoanop and iteml.clicpoproc = licit.clicpoproc ";
    //     echo $sql .= " and iteml.corglicodi = licit.corglicodi and iteml.ccomlicodi = licit.ccomlicodi and iteml.cgrempcodi = licit.cgrempcodi and licit.cmodlicodi = modal.cmodlicodi ORDER BY 1,2 ";
    //     die;
    //     $resultado = executarSQL($this->con, $sql);
            
    //     $dadosRetorno = array();
    //     while ($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
    //         $dadosRetorno[] = $retorno;
    //     }
    //     return $dadosRetorno;
    // }

    public function DadosReportParticipante($carpnosequ , $CSERVPSEQU , $citelpsequ) {
        $sql ="SELECT NumAta, orgaoAta, ItemAta, OrgaoParticipante, SUM(somaparticipante) AS somaparticipante FROM	( ( SELECT itemata.carpnosequ AS NumAta, orgao.eorglidesc AS orgaoAta, itemata.citarpsequ AS ItemAta, resultadoscc.corglicodi AS OrgaoParticipante, SUM ( resultadoscc.aitescqtso ) AS somaparticipante ";
        $sql .=" FROM sfpc.tbitemataregistropreconova itemata LEFT JOIN ( SELECT itemsol.csolcosequ, itemsol.carpnosequ, itemsol.citarpsequ, itemsol.aitescqtso, solco.corglicodi, solco.fsolcorpcp ";
        $sql .=" FROM sfpc.tbitemsolicitacaocompra itemsol, sfpc.tbsolicitacaocompra solco WHERE itemsol.csolcosequ = solco.csolcosequ AND solco.fsolcorpcp = 'P' AND solco.csitsocodi in (3,4) ";
        $sql .=" ) AS resultadoscc ON itemata.carpnosequ = resultadoscc.carpnosequ AND itemata.citarpsequ = resultadoscc.citarpsequ, sfpc.tborgaolicitante AS orgao WHERE resultadoscc.aitescqtso IS NOT NULL ";
        $sql .=" AND itemata.carpnosequ = ".$carpnosequ." AND itemata.CSERVPSEQU = ".$CSERVPSEQU." AND itemata.citarpsequ = ".$citelpsequ." AND resultadoscc.corglicodi = orgao.corglicodi GROUP BY ";
        $sql .=" NumAta, orgaoAta, ItemAta, OrgaoParticipante ) UNION (SELECT itemparticipanterp.carpnosequ AS NumAta, orgao.eorglidesc AS orgaoAta, itemparticipanterp.citarpsequ AS ItemAta, itemparticipanterp.corglicodi AS OrgaoParticipante, ";
        $sql .=" SUM ( itemparticipanterp.apiarpqtut ) AS somaparticipante FROM sfpc.tbitemataregistropreconova itemata LEFT JOIN sfpc.tbparticipanteitematarp itemparticipanterp ON itemata.carpnosequ = itemparticipanterp.carpnosequ ";
        $sql .=" AND itemata.citarpsequ = itemparticipanterp.citarpsequ AND ( itemparticipanterp.apiarpqtut <> 0 AND itemparticipanterp.apiarpqtut <> 0.0000 ), sfpc.tborgaolicitante AS orgao WHERE itemparticipanterp.corglicodi IS NOT NULL ";
        $sql .=" AND itemata.carpnosequ = ".$carpnosequ." AND itemata.CSERVPSEQU = ".$CSERVPSEQU." AND itemata.citarpsequ = ".$citelpsequ." AND itemparticipanterp.corglicodi = orgao.corglicodi GROUP BY NumAta, orgaoAta, ItemAta, OrgaoParticipante )";
        $sql .=" ) as tab GROUP BY NumAta, orgaoAta, ItemAta,	OrgaoParticipante ";
        $resultado = executarSQL($this->con, $sql);
            
        $dadosRetorno = array();
        while ($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
            $dadosRetorno[] = $retorno;
        }
        return $dadosRetorno;
    }

    public function DadosReportCarona($carpnosequ  , $CSERVPSEQU, $citelpsequ) {
        $sql = "SELECT NumAtaC, orgaoAta, ItemAta, OrgaoCarona, SUM(somacarona) AS somacarona FROM	((SELECT itemata.carpnosequ as NumAtaC, orgao.eorglidesc as orgaoAta, itemata.citarpsequ as ItemAta, resultadoscc.corglicodi AS OrgaoCarona, SUM(resultadoscc.aitescqtso) AS somacarona FROM sfpc.tbitemataregistropreconova itemata ";
        $sql .= " LEFT JOIN (SELECT itemsol.csolcosequ, itemsol.carpnosequ, itemsol.citarpsequ, itemsol.aitescqtso, solco.corglicodi, solco.fsolcorpcp FROM sfpc.tbitemsolicitacaocompra itemsol, sfpc.tbsolicitacaocompra solco ";
        $sql .= " WHERE itemsol.csolcosequ = solco.csolcosequ AND solco.fsolcorpcp = 'C' AND solco.csitsocodi in (3,4) ) AS resultadoscc ON itemata.carpnosequ = resultadoscc.carpnosequ AND itemata.citarpsequ = resultadoscc.citarpsequ,sfpc.tborgaolicitante AS orgao WHERE ";
        $sql .= " resultadoscc.aitescqtso IS NOT NULL AND itemata.carpnosequ = ".$carpnosequ." AND itemata.CSERVPSEQU = ".$CSERVPSEQU." AND itemata.citarpsequ = ".$citelpsequ." AND resultadoscc.corglicodi = orgao.corglicodi GROUP BY NumAtaC, orgaoAta, ItemAta, OrgaoCarona ) UNION ALL ( SELECT itemcaronarp.carpnosequ as NumAtaC, orgao.eorglidesc as orgaoAta,";
        $sql .= " itemcaronarp.citarpsequ as ItemAta, itemcaronarp.corglicodi AS OrgaoCarona, SUM(itemcaronarp.aitcrpqtut) AS somacarona FROM sfpc.tbitemataregistropreconova itemata LEFT JOIN sfpc.tbitemcaronainternaatarp ";
        $sql .= " itemcaronarp ON itemata.carpnosequ = itemcaronarp.carpnosequ AND itemata.citarpsequ = itemcaronarp.citarpsequ AND ( itemcaronarp.aitcrpqtut <> 0 AND itemcaronarp.aitcrpqtut <> 0.0000 ), sfpc.tborgaolicitante as orgao WHERE itemata.carpnosequ = ".$carpnosequ." ";
        $sql .= " AND itemcaronarp.corglicodi IS NOT NULL AND itemata.CSERVPSEQU = ".$CSERVPSEQU." AND itemata.citarpsequ = ".$citelpsequ." AND itemcaronarp.corglicodi = orgao.corglicodi GROUP BY NumAtaC, orgaoAta, ItemAta, OrgaoCarona ) ";
        $sql .= " ) as tab GROUP BY NumAtaC, orgaoAta, ItemAta, OrgaoCarona ";
        $resultado = executarSQL($this->con, $sql);
            
        $dadosRetorno = array();
        while ($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
            $dadosRetorno[] = $retorno;
        }
        return $dadosRetorno;
    }

}