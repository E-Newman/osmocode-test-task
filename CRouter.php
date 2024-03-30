<?php

class CRouter
{
	/**
	 * @var array Параметры из шаблона маршрута
	 */
	static $routing_parameters = array();


	static function check_route_rules($rules)
	{
		$ok = 0;
        if (class_exists("CDebug",false) && CDebug::isEnabled())
            CDebug::getInstance()->logPrint(__FILE__.":".__LINE__,["ROUTING","check_route_rules ".$rules['type'],$rules]);

		if ($rules["type"] == "or")
		{
			foreach ($rules["rules"] as $rr)
				if (self::check_route_rules($rr)) return 1;
			return 0;
		}
		elseif ($rules["type"] == "and")
		{
			foreach ($rules["rules"] as $rr)
				if (!self::check_route_rules($rr)) return 0;
			return 1;
		}
		elseif ($rules["type"] == "request_var" || $rules["type"] == "request_uri")
		{
			if ($rules["type"] == "request_var")
				$var = $_REQUEST[$rules["name"]];
			elseif ($rules["type"] == "request_uri")
				$var = $_SERVER["REQUEST_URI"];
			if ($rules["regexp"])
			{
				//print_r($r);

				if (preg_match("/".$rules["regexp"]."/", $var, $m))
				{
					$ok = 1;
					if ($rules["default_parameters"])
						self::$routing_parameters = $rules["default_parameters"];
					if ($rules["parameters"])
						foreach ($rules["parameters"] as $i => $p)
							self::$routing_parameters[$p] = urldecode($m[$i + 1]);
				}
			}
			elseif (($var == $rules["value"]) || ($rules["value"] == "any" && isset($var)))
				$ok = 1;
            if (class_exists("CDebug",false) && CDebug::isEnabled())
                CDebug::getInstance()->logPrint(__FILE__.":".__LINE__,["ROUTING","var '".$rules["name"]."' check result=".$ok,["value"=>$var,"rules"=>$rules]]);
			return $ok;
		}
		elseif (isset($rules["type"]))
		{
            if (class_exists("CDebug",false) && CDebug::isEnabled())
                CDebug::getInstance()->logPrint(__FILE__.":".__LINE__,["ROUTING","unknown rule",$rules]);
		}
		else
			foreach ($rules as $r)
			{
				if (self::check_route_rules($r))
					return 1;
				return 0;
			}

        if (class_exists("CDebug",false) && CDebug::isEnabled())
            CDebug::getInstance()->logPrint(__FILE__.":".__LINE__,["ROUTING","check_route_rules result",$ok]);
		return $ok;
	}

}


?>