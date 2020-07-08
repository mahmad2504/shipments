<?php

namespace App\Http\Controllers;
use App\Database;
use \MongoDB\Client;
use \MongoDB\BSON\UTCDateTime;

use Auth;
use Illuminate\Http\Request;
use Response;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
	
    }
	public function Index(Request $request)
	{
		$db = new Database();
		$tickets = $db->ReadActive()->toArray();
		for($i=0;$i<count($tickets);$i++)
		{
			$tickets[$i] = $tickets[$i]->jsonSerialize();
			unset($tickets[$i]->_id);
			unset($tickets[$i]->id);
			$desc = str_replace("\n"," ",$tickets[$i]->desc);
			$parts = explode('**',$desc);
			if(count($parts)>2)
				$tickets[$i]->details = $parts[2];
			else
				$tickets[$i]->details = "Not Found";
			
			if(count($parts)>4)
				$tickets[$i]->source = $parts[4];
			else
				$tickets[$i]->source = "Not Found";
			
			if(count($parts)>6)
				$tickets[$i]->dest = $parts[6];
			else
				$tickets[$i]->dest = "Not Found";
		}
		
	
		$lastupdated="Never Updated";
		if(file_exists("../lastupdated"))
		{
			$lastupdated = file_get_contents("../lastupdated");
			$lastupdated =  new \DateTime($lastupdated);
			$lastupdated->setTimezone(new \DateTimeZone('Asia/Karachi'));
			$lastupdated=$lastupdated->format('Y-m-d H:i:s');
		}
		return view('home',compact('tickets','lastupdated'));
	}
}