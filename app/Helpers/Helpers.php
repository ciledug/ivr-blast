<?php

namespace App\Helpers;

class Helpers
{
	// generate voice number
	static function generateNumber($number)
	{
		$number = (int)$number;
		$digit[] = null;
		$angka = array("", "1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12", "13", "14", "15", "16", "17", "18", "19");
		for($i=1;$i<20;$i++){
			if(in_array($i, $angka)){
				$digit[] = $i.".wav";
			}else{
				$digit[] = "";
			}
		}
		
		if($number==0){
			return "";
		}elseif ($number !=0 && $number < 12) {
			return "" . trim($digit[$number]);
		} elseif ($number < 20) {
			return trim($digit[$number]);
		} elseif ($number < 100) {
			return trim(self::generateNumber($number / 10)*10) . ".wav " . trim(self::generateNumber($number % 10));
		} elseif ($number < 200) {
			return " 100.wav " . trim(self::generateNumber($number - 100));
		} elseif ($number < 1000) {
			return trim(self::generateNumber($number / 100)) . " ratus.wav " . trim(self::generateNumber($number % 100));
		} elseif ($number < 2000) {
			return " 1000.wav " . trim(self::generateNumber($number - 1000));
		} elseif ($number < 1000000) {
			return trim(self::generateNumber($number / 1000)) . " ribu.wav " . trim(self::generateNumber($number % 1000));
		} elseif ($number < 1000000000) {
			return trim(self::generateNumber($number / 1000000)) . " juta.wav " . trim(self::generateNumber($number % 1000000));
		}elseif ($number < 1000000000000) {
			return false;
		}
	}
	
	
	// generate voice date
	static function generateDate($date)
	{
		$day 	= self::generateNumber(substr($date,8,2));
		$year 	= self::generateNumber(substr($date,0,4));
		$month 	= '';
		
		switch (substr($date,5,2)){
			case 1:
			  $month = "m01.wav";
			  break;
			case 2:
			  $month = "m02.wav";
			  break;
			case 3:
			  $month = "m03.wav";
			  break;
			case 4:
			  $month = "m04.wav";
			  break;
			case 5:
			  $month = "m05.wav";
			  break;
			case 6:
			  $month = "m06.wav";
			  break;
			case 7:
			  $month = "m07.wav";
			  break;
			case 8:
			  $month = "m08.wav";
			  break;
			case 9:
			  $month = "m09.wav";
			  break;
			case 10:
			  $month = "m10.wav";
			  break;
			case 11:
			  $month = "m11.wav";
			  break;
			case 12:
			  $month = "m12.wav";
			  break;
		}
		
		return trim($day).' '.trim($month).' '.trim($year);
	}
	
	/*
	static function generateVoice($data)
	{
		// data is array contain bill_date, due_date, nominal and filename to generate file voice
		$billDate 	= $data['bill_date'];
		$dueDate	= $data['due_date'];
		$nominal	= $data['nominal'];
		
		$fileVoice = 'main1.wav '.trim(self::generateDate($billDate)).' main2.wav '.trim(self::generateNumber($nominal)).' main3.wav '.trim(self::generateDate($dueDate)).' main4.wav';
		
		return $fileVoice;
	}
	*/

	static function generateVoice($textVoice, $voiceColumns, $dataRow)
	{
		$voiceHolders = [];
		$tempGenerates = [];
		$headerName = '';

		foreach ($voiceColumns AS $keyVoice => $valVoice) {
			$headerName = strtolower(preg_replace('/\W+/i', '_', $valVoice->name));
			switch ($valVoice->column_type) {
				case 'numeric': $tempGenerates[] = self::generateNumber($dataRow->$headerName); break;
				case 'date': $tempGenerates[] = self::generateDate($dataRow->$headerName); break;
				default: $tempGenerates[] = $dataRow->$headerName; break;
			}
			$voiceHolders[] = '<voice-' . ($keyVoice + 1) . '>';
		}

		if ($textVoice) return str_replace($voiceHolders, $tempGenerates, $textVoice);
		else return implode(' ', $tempGenerates);
	}


	static function secondsToHms($seconds){
		$hour = floor($seconds / 3600);
		$min  = floor($seconds / 60 % 60);
		$sec  = floor($seconds % 60);
		
		$dateFormat = sprintf('%02d:%02d:%02d', $hour , $min , $sec );
		return $dateFormat;
	}
}