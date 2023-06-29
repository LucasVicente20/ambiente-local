<?php

#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: DataHora.class.php
# Autor:    Ariston Cordeiro
# Data:     08/11/2012
# Objetivo: Classe que manipula data e hora, e reconhece formatação em português.
#----------------------------------------------------------------------

/** 
 * Classe que manipula data e hora, e reconhece formatação em português.
 * Para criar um objeto: $data = DataHora('2008/09/05'); //(reconhece também string de data no formato oracle)
 * Para comparar datas: if ($data1 > $data2) {...}
 * Para mostrar data: $data->format('d/m/Y');
 * Para mostrar data/hora em oracle: $data->format('Y-m-d H:i:s'); // para mostrar apenas data retirar ' H:i:s'
 * Para somar/subtrair datas, usar $data1->soma($data2) ou $data1->subtrai($data2)
 * */
class DataHora extends DateTime{
	function __construct($dataStr){
		if(preg_match("/[0-9]{2}\/[0-9]{2}\/[0-9]{4}/", $dataStr)){
			# data está no formato d/m/Y, que não é reconhecido pelo DateTime.
			$Dia   = substr($dataStr,0,2);
			$Mes   = substr($dataStr,3,2);
			$Ano   = substr($dataStr,6,4);
			$date = date("Y-m-d H:i:s",$dataStr);
			
			$dataOracle  = $date;	
			//echo 	$dataOracle;
			parent::__construct($dataOracle);
			
		}else{
			# tratando data no formato oracle
			parent::__construct($dataStr);
		}
	}
	/**
	 * Soma esta data a partir de um período
	 * */
	function somar($dia, $mes=0, $ano=0, $hora=0, $minuto=0, $segundo=0){
		$this->setDate($this->format("Y")+$dia,$this->format("m")+$mes,$this->format("d")+$ano);
		$this->setTime($this->format("H")+$hora,$this->format("i")+$minuto,$this->format("s")+$segundo);
	}
	/**
	 * Subtrai esta data a partir de um período
	 * */
	function subtrair($dia, $mes=0, $ano=0, $hora=0, $minuto=0, $segundo=0){
		$this->setDate($this->format("Y")-$dia,$this->format("m")-$mes,$this->format("d")-$ano);
		$this->setTime($this->format("H")-$hora,$this->format("i")-$minuto,$this->format("s")-$segundo);
	}
	/**
	 * Mesmo que format(), mas converte data em texto de inglês para português
	 * */
	function formata($formato){
		$ingles  = Array(
			"January","February","March","April","May","June","July","August","September","October","November","December",
			"Sunday","Monday","Tuersday","Wednesday","Thursday","Friday","Saturday",
			"JANUARY","FEBRUARY","MARCH","APRIL","MAY","JUNE","JULY","AUGUST","SEPTEMBER","OCTOBER","NOVEMBER","DECEMBER",
			"SUNDAY","MONDAY","TUERSDAY","WEDNESDAY","THURSDAY","FRIDAY","SATURDAY",
			"Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec",
			"Sun","Mon","Tue","Wed","Thu","Fri","Sat",
			"JAN","FEB","MAR","APR","MAY","JUN","JUL","AUG","SEP","OCT","NOV","DEC",
			"SUN","MON","TUE","WED","THU","FRI","SAT"
		);
		$portugues  = Array(
			"Janeiro","Fevereiro","Março","Abril","Maio","Junho","Julho","Agosto","Setembro","Outubro","Novembro","Dezembro",
			"Domingo","Segunda","Terça","Quarta","Quinta","Sexta","Sábado",
			"JANEIRO","FEVEREIRO","MARÇO","ABRIL","MAIO","JUNHO","JULHO","AGOSTO","SETEMBRO","OUTUBRO","NOVEMBRO","DEZEMBRO",
			"DOMINGO","SEGUNDA","TERÇA","QUARTA","QUINTA","SEXTA","SÁBADO",
			"Jan","Fev","Mar","Abr","Mai","Jun","Jul","Ago","Set","Out","Nov","Dez",
			"Dom","Seg","Ter","Qua","Qui","Sex","Sab",
			"JAN","FEV","MAR","ABR","MAI","JUN","JUL","AGO","SET","OUT","NOV","DEZ",
			"DOM","SEG","TER","QUA","QUI","SEX","SAB"
		);
		$dataStr = $this->format($formato);
		$dataStr =  str_replace($ingles, $portugues, $dataStr);
		return $dataStr;
	}
}

?>