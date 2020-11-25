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
	public function HandleInternationalShipments(Request $request)
	{
		$db = new Database("mongodb://127.0.0.1","ishipments");
		$tickets = $db->ReadAll();
        $filtered = [];
		for($i=0;$i<count($tickets);$i++)
		{
			$ticket = $tickets[$i];
			$obj =  new \StdClass();
			$ticket = $ticket->jsonSerialize();
			unset($ticket->_id);
			/// Hardware Details //////
			$parts = explode("Qty:",$ticket->desc);
			$parts = explode("\n",$parts[0]);
			$del = '';
			$hardware = '';
			for($j=1;$j<count($parts);$j++)
			{
				if(strlen(trim($parts[$j]))>0)
				{
					$parts[$j] = str_replace("-",'',$parts[$j]);
					$hardware .= $del.trim($parts[$j]);
					$del=',';
				}
			}
			$obj->hardware = $hardware;
			// Owener ////
			$parts = explode("-",$ticket->name);
			$obj->owner = $parts[2];
			$obj->source = $parts[1];
			$obj->team = $ticket->label;
			if(isset($ticket->checkitems['Shipment Dispatched']->state))
			{
				if($ticket->checkitems['Shipment Dispatched']->state == 'complete')
				{
					$obj->shipment_date = $ticket->checkitems['Shipment Dispatched']->date;
				}
			}
			$obj->trackingno = $ticket->trackingno;
			if(($ticket->list == 'Upcoming')||($ticket->list == 'Shipment')) 
			    $obj->status = 'Ready';
			if($ticket->checkitems['Shipment Dispatched'] == 'complete')
				$obj->status = 'Dispatched';
			if($ticket->list == 'Custom')
				$obj->status = 'Customs';
			if($ticket->list == 'Expense')
				$obj->status = 'Received';
			$obj->url = $ticket->url;
			$filtered[]=$obj;
		}
		$tickets = $filtered;
		$lastupdated="Never Updated";
		if(file_exists("../lastupdated_ishipment"))
		{
			$lastupdated = file_get_contents("../lastupdated_ishipment");
			$lastupdated =  new \DateTime($lastupdated);
			$lastupdated->setTimezone(new \DateTimeZone('Asia/Karachi'));
			$lastupdated=$lastupdated->format('Y-m-d H:i:s');
		}
		
		return view('ishipment.home',compact('tickets','lastupdated'));
	}
	public function TeamView(Request $request,$team,$code='nonenone')
	{
		if(strlen($code) < 5)
		{
			return ['result'=>'Unautorized Aceess'];
		}
		if(strtolower($team) == 'international')
		{
			return $this->HandleInternationalShipments($request);
		
		}
		if( count(explode($code,md5(strtolower($team))))==2)
		{}
		else
			return ['result'=>'Unautorized Aceess'];
		
		$db = new Database("mongodb://127.0.0.1","lshipments");
		$tickets = $db->ReadActive()->toArray();
		$filtered = [];
		for($i=0;$i<count($tickets);$i++)
		{
			//dump($tickets[$i]->closed);
			$tickets[$i] = $tickets[$i]->jsonSerialize();
			unset($tickets[$i]->_id);
			//unset($tickets[$i]->id);
			$desc = str_replace("\n"," ",$tickets[$i]->desc);
			$parts = explode('**',$desc);
			if(count($parts)>2)
			{
				$tickets[$i]->details = trim($parts[2]);
				if(strlen($tickets[$i]->details)>0)
				{
					if($tickets[$i]->details[0] == '-')
						$tickets[$i]->details[0]=" ";
				}
			}
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
			
			if(count($parts)>8)
				$tickets[$i]->priority = $parts[8];
			else
				$tickets[$i]->priority = "Low";
			
			if(count($parts)>10)
				$tickets[$i]->requestor = trim($parts[10]);
			else
				$tickets[$i]->requestor = "";
			
			if(strlen($tickets[$i]->requestor)>0)
			{
				if(is_numeric(trim($tickets[$i]->requestor[0])))
					$tickets[$i]->requestor = "";
	
			}
			$parts = explode("-",$tickets[$i]->priority);

			$tickets[$i]->priority = trim($parts[0]);
			$tickets[$i]->priority_tip = '';
			if(isset($parts[1]))
				$tickets[$i]->priority_tip = $parts[1];
			
			if( (strtolower($tickets[$i]->priority)=='high')||(strtolower($tickets[$i]->priority)=='urgent')||(strtolower($tickets[$i]->priority)=='low'))
			{
			}
			else
				$tickets[$i]->priority = 'Low';
			
			if(trim($tickets[$i]->label)=='')
				$tickets[$i]->label = 'Others';
			
			if(strtolower($team)=='admin')
				$filtered[] = $tickets[$i];

			else 
			{
				if(strtolower($tickets[$i]->label) == strtolower($team))
					$filtered[] = $tickets[$i];
			}
		}
		$tickets = $filtered;
	
		$lastupdated="Never Updated";
		if(file_exists("../lastupdated_lshipment"))
		{
			$lastupdated = file_get_contents("../lastupdated_lshipment");
			$lastupdated =  new \DateTime($lastupdated);
			$lastupdated->setTimezone(new \DateTimeZone('Asia/Karachi'));
			$lastupdated=$lastupdated->format('Y-m-d H:i:s');
		}
		$admin=0;
		if(strtolower($team)=='admin')
			$admin=1;
		
		$team = ucfirst($team);
		return view('lshipment.home',compact('admin','tickets','lastupdated','team'));
	}
	public function AdminView(Request $request)
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
		$admin=1;
		return view('home',compact('admin','tickets','lastupdated'));
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
		$admin=0;
		return view('home',compact('admin','tickets','lastupdated'));
	}
}