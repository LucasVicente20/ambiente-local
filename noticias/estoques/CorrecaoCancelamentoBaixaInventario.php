<?php
exit;
include('../funcoes.php');

function SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db){
		$sql  = "SELECT MAX(CMOVCUSEQU) FROM SFCP.TBMOVCUSTOALMOXARIFADO ";
		$sql .= " WHERE DEXERCANOR = $AnoBaixa AND AMOVCUMESM = $MesBaixa AND AMOVCUDIAM = $DiaBaixa ";
		$res  = $dbora->query($sql);
		if(db::isError($res)){
				# Desfaz alterações no Postgre #
				$db->disconnect();
				# Desfaz alterações no Oracle #
				$dbora->query("ROLLBACK");
				$dbora->query("END TRANSACTION");
				$dbora->disconnect();
				ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				exit;
		}else{
				$Linha      = $res->fetchRow();
				$Sequencial = $Linha[0] + 1;
				return $Sequencial; 
		}
}
function SequMaxContabil($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db){
		$sql  = "SELECT MAX(CMOVCUSEQU) FROM SFCP.TBMOVCONTABILALMOXARIFADO ";
		$sql .= " WHERE APLCTAANOC = $AnoBaixa AND AMVCALMESM = $MesBaixa AND AMVCALDIAM = $DiaBaixa ";
		$res  = $dbora->query($sql);
		if(db::isError($res)){
				# Desfaz alterações no Postgre #
				$db->disconnect();
				# Desfaz alterações no Oracle #
				$dbora->query("ROLLBACK");
				$dbora->query("END TRANSACTION");
				$dbora->disconnect();
				ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				exit;
		}else{
				$Linha      = $res->fetchRow();
				$Sequencial = $Linha[0] + 1;
				return $Sequencial; 
		}
}
echo "<font face=verdana size=-2>";
$db    = Conexao();
$dbora = ConexaoOracle();

$dbora->query("BEGIN TRANSACTION");

$sqlpost = "
select 
a.calmpocodi, a.clocmacodi, f.creqmasequ, g.fgrumstipc, sum(mov.amovmaqtdm*
 CASE WHEN mov.CTIPMVCODI = 4 THEN 
       ( 
       SELECT VMOVMAVALO FROM SFPC.TBMOVIMENTACAOMATERIAL 
        WHERE CMATEPSEQU = mov.CMATEPSEQU 
          AND CREQMASEQU = mov.CREQMASEQU 
          AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A' ) 
          AND TMOVMAULAT = ( 
                SELECT MAX(TMOVMAULAT) FROM SFPC.TBMOVIMENTACAOMATERIAL 
                 WHERE CTIPMVCODI IN (4,19,20) 
                   AND CMATEPSEQU = mov.CMATEPSEQU 
                   AND CREQMASEQU = mov.CREQMASEQU 
                   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A' ) 
              ) 
       ) 
       ELSE 
mov.VMOVMAVALO 
END
), cc.ccenponrpa,cc.ccenpocent, cc.ccenpodeta,cc.ccenpocorg, cc.ccenpounid
from SFPC.TBlocalizacaomaterial a
left outer join 
(
select c.clocmacodi,c.ainvcoanob,c.ainvcosequ,c.tinvcofech
from SFPC.TBinventariocontagem c
where (c.clocmacodi,c.ainvcoanob,c.ainvcosequ) = (
select b.clocmacodi,b.ainvcoanob,max(b.ainvcosequ)
from sfpc.tbinventariocontagem b
where c.clocmacodi = b.clocmacodi
and c.ainvcoanob = c.ainvcoanob
and (b.clocmacodi,b.ainvcoanob) = (select clocmacodi,max(ainvcoanob) 
from sfpc.tbinventariocontagem 
where b.clocmacodi = clocmacodi
group by clocmacodi)
and b.tinvcofech is not null
group by b.clocmacodi,b.ainvcoanob
)
) z
on a.clocmacodi = z.clocmacodi
inner join SFPC.TBrequisicaomaterial f
on a.calmpocodi = f.calmpocodi
and 
(
select count(*)
from SFPC.TBsituacaorequisicao
where f.creqmasequ = creqmasequ
and areqmaanor = 2006
and ctipsrcodi in (3,4)
and tsitreulat < z.tinvcofech
) > 1
and
(
select count(*)
from SFPC.TBsituacaorequisicao
where f.creqmasequ = creqmasequ
and areqmaanor = 2006
and ctipsrcodi = 5
and tsitreulat > z.tinvcofech
) = 1
inner join SFPC.TBmovimentacaomaterial mov
on f.creqmasequ = mov.creqmasequ
inner join SFPC.TBrequisicaomaterial req
on mov.creqmasequ = req.creqmasequ
inner join SFPC.TBcentrocustoportal cc
on req.ccenposequ = cc.ccenposequ
inner join SFPC.TBmaterialportal m
on mov.cmatepsequ = m.cmatepsequ
inner join SFPC.TBsubclassematerial s
on m.csubclsequ = s.csubclsequ
inner join SFPC.TBgrupomaterialservico g
on s.cgrumscodi = g.cgrumscodi
--group by a.calmpocodi, a.clocmacodi, g.fgrumstipc
group by a.calmpocodi, a.clocmacodi, f.creqmasequ, g.fgrumstipc,cc.ccenponrpa,cc.ccenpocent, cc.ccenpodeta,cc.ccenpocorg, cc.ccenpounid
order by 1,2,3
";
$res  = $db->query($sqlpost);
/*
echo "<table border=1>";
echo "<tr>";
echo "<td>ALMOXARIFADO</td>";
echo "<td>REQUISICAO</td>";
echo "<td>VALOR</td>";
echo "<td>LOTE</td>";
echo "<td>TIP MOV</td>";
echo "<td>HISTORICO</td>";
echo "<td>CONTA</td>";
echo "<td>NATUREZA</td>";
echo "<td>ORG</td>";
echo "<td>UNID</td>";
echo "</tr>";
*/
if( db::isError($res) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlpost");
		exit;
}else{
		echo $res->numRows()."<hr>";
		while($Linha = $res->fetchRow()){
			switch ($Linha[3]){
				case "C":
					$Linha[3] = 3;
					break;
				case "D":
					$Linha[3] = 6;
					break;
				case "F":
					$Linha[3] = 30;
					break;
				case "L":
					$Linha[3] = 37;
					break;
				case "P":
					$Linha[3] = 27;
					break;
			}
			
			$sql = "
				INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO 
				(
				DEXERCANOR,AMOVCUMESM,AMOVCUDIAM,CMOVCUSEQU,CRPAAACODI,
				CCENCPCODI,CESPCPCODI,CDETCPCODI,CORGORCODI,CUNDORCODI,
				AMOVCUMATR,NMOVCURECE,CEMPRECODI,AUSUACMATR,CORGORCOD1,
				CUNDORCOD1,VMOVCUREQU,TMOVCUULAT,FMOVCULANC,EMOVCUDESC,
				CMOVCUREQU,CMOVCUALMO,CMOVCUCODI,DMOVCUAMVA
				)
				VALUES 
				(
				".date("Y").",".date("m").",".date("d").",".SequMax(date("Y"),date("m"),date("d"),$dbora,$db).",".$Linha[5].",
				".$Linha[6].",".$Linha[3].",".$Linha[7].",".$Linha[8].",".$Linha[9].",
				7242,'ROSSANA LIRA',NULL,NULL,".$Linha[8].",
				".$Linha[9].",".round($Linha[4],2).",SYSDATE,'S','CANCELAMENTO BAIXA PÓS INVENTARIO',
				".$Linha[2].",".$Linha[0].",NULL,'".date("Y")."'
				)";
			echo $sql;
			$resoracle  = $dbora->query($sql);
			if(db::isError($resoracle)){
				echo "<br>".$resoracle->getCode();
				echo "<br>".$resoracle->getMessage();
				$dbora->query("ROLLBACK");
				$dbora->query("END TRANSACTION");
				$dbora->disconnect();
				$db->disconnect();
				exit;
			}
			
			/*
			echo "
				INSERT INTO SFCP.TBMOVCONTABILALMOXARIFADO 
				(
				APLCTAANOC,AMVCALMESM,AMVCALDIAM,AMVCALSEQU,VMVCALVALR,
				AMVCALLOTE,CTIPMOCODI,AHMOVINUME,APLCTACONT,FMVCALDBCD,
				CORGORCODI,DEXERCANOR,CUNDORCODI,AMVCALMATR,NMVCALRECE,
				EMVCALDESC,CMVCALREQU,CMVCALALMO,CMVCALCODI,TMVCALULAT,
				DMVCALAMVA
				)
				VALUES 
				(
				".date("Y").",".date("m").",".date("d").",".SequMax(date("Y"),date("m"),date("d"),$dbora,$db).",".round($Linha[4],2).",
				99999,3,908,1131801,'D',
				".$Linha[8].",".date("Y").",".$Linha[9].",7242,'ROSSANA LIRA',
				'CANCELAMENTO BAIXA PÓS INVENTARIO',".$Linha[2].",".$Linha[0].",NULL,SYSDATE,
				".date("Y")."
				);
				<hr>";
			echo "
				INSERT INTO SFCP.TBMOVCONTABILALMOXARIFADO 
				(
				APLCTAANOC,AMVCALMESM,AMVCALDIAM,AMVCALSEQU,VMVCALVALR,
				AMVCALLOTE,CTIPMOCODI,AHMOVINUME,APLCTACONT,FMVCALDBCD,
				CORGORCODI,DEXERCANOR,CUNDORCODI,AMVCALMATR,NMVCALRECE,
				EMVCALDESC,CMVCALREQU,CMVCALALMO,CMVCALCODI,TMVCALULAT,
				DMVCALAMVA
				)
				VALUES 
				(
				".date("Y").",".date("m").",".date("d").",".SequMax(date("Y"),date("m"),date("d"),$dbora,$db).",".round($Linha[4],2).",
				99999,3,908,523120201,'C',
				".$Linha[8].",".date("Y").",".$Linha[9].",7242,'ROSSANA LIRA',
				'CANCELAMENTO BAIXA PÓS INVENTARIO',".$Linha[2].",".$Linha[0].",NULL,SYSDATE,
				".date("Y")."
				);
				<hr>";
			*/
			/*
			echo "<tr>";
			echo "<td>".$Linha[0]."</td>";
			echo "<td>".$Linha[2]."</td>";
			echo "<td>".round($Linha[4],2)."</td>";
			echo "<td>99999</td>";
			echo "<td>3</td>";
			if ($Linha[3] == 27){
				echo "<td>953</td>";
				echo "<td>1421292</td>";
			} else {
				echo "<td>908</td>";
				echo "<td>1131801</td>";
			}
			echo "<td>D</td>";
			echo "<td>".$Linha[8]."</td>";
			echo "<td>".$Linha[9]."</td>";
			echo "</tr>";
			
			echo "<tr>";
			echo "<td>".$Linha[0]."</td>";
			echo "<td>".$Linha[2]."</td>";
			echo "<td>".round($Linha[4],2)."</td>";
			echo "<td>99999</td>";
			echo "<td>3</td>";
			if ($Linha[3] == 27){
				echo "<td>953</td>";
				echo "<td>523120101</td>";
			} else {
				echo "<td>908</td>";
				echo "<td>523120201</td>";
			}
			echo "<td>C</td>";
			echo "<td>".$Linha[8]."</td>";
			echo "<td>".$Linha[9]."</td>";
			echo "</tr>";
			*/
		}
}

$dbora->query("COMMIT");
$dbora->query("END TRANSACTION");

$dbora->disconnect();
$db->disconnect();

?>
