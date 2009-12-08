<?php
/*

print TradeDoubler's trackback image
Copyright (C) 2008 Korbinian Pauli

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation version 2.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

*/

//description
/*



*/


//Update these fields with data you got from TradeDoubler or the company using TradeDoubler's tdIntegral
define('tdTrackback_secretCode','1234');
define('tdTrackback_orgId','4321');
define('tdTrackback_eventId','3445');


class tdTrackback {

	//create contructor (PHP4 compatible)
	function tdTrackback() {
		$this->orderNumber = rand();
		$this->orderValue = '1.00';
		$this->currency = 'EUR';
	}


	function saveTduid() {
		$tduid = $_GET['tduid'];
		if (!empty($tduid)) {
			setcookie("tdTrackback",$tduid,  time() + 60*60*24*365);
		}
	}



	//Only use this method, if you integrate this class into xt:commerce
	function getSetXtCommerceData($orderNumber) {

		//get and set currency
		$rows = xtc_db_query("SELECT currency FROM orders ".
			"WHERE orders_id='" . $orderNumber . "' " .
			"LIMIT 1");
		$row = xtc_db_fetch_array($rows);
		$this->setCurrency($row['currency']);

		//get and set orderValue
		$rows = xtc_db_query("SELECT value FROM orders_total " .
			"WHERE orders_id='" . $orderNumber . "' " .
			"AND class='ot_subtotal' " .
			"LIMIT 1");
		$row = xtc_db_fetch_array($rows);
		$this->setOrderValue($row['value']);

		//set orderNumber
		$this->setOrderNumber($orderNumber);

	}



	function setOrderNumber($orderNumber) {
		$this->orderNumber = 'tdTrackback-' . $orderNumber;
	}

	function setOrderValue($orderValue) {
		//TradeDoubler need the 2 decimal places
		$this->orderValue = sprintf("%.2f",$orderValue);
	}

	function setCurrency($currency) {
		$this->currency = $currency;
	}


	function sendTrackback() {

		//calculate the checksum
		$checksum = 'v04' . md5(tdTrackback_secretCode . $this->orderNumber . $this->orderValue);

		//read cookie with tduid
		$tduid = $_COOKIE['tdTrackback'];

		//create trackback URL
		$trackbackImage = "http://tbs.tradedoubler.com/report?" .
			"organization=" . tdTrackback_orgId .
			"&amp;event=" . tdTrackback_eventId .
			"&amp;orderNumber=" . $this->orderNumber .
			"&amp;checksum=" . $checksum .
			"&amp;tduid=" . $tduid .
			"&amp;reportInfo=" . '' .
			"&amp;orderValue=" . $this->orderValue .
			"&amp;currency="    . $this->currency;

		//echo $trackbackImage;

		//display trackback image
		echo '<img width="1" height="1" src="' . $trackbackImage . '" />';

	}
}
