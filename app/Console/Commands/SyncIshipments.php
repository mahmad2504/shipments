<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Database;
class SyncIshipments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'syncishipments {--beat=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
	 
	protected $dbname = "ishipments";
    protected $server = "mongodb://127.0.0.1";
	protected $lists = null;
	protected $checklists = [];
    public function __construct()
    {
		$this->key=env("TRELLO_KEY"); 
		$this->token=env("TRELLO_TOKEN"); 
		parent::__construct();
    }
    public function UpdateTicket($ticket)
	{
		//5aa212ebaac901de308b610c
		//5a7c3ccdb5181394a42a0b06
		//5a78b08c6f85c304e464aa07
		
		//5db286ad4973fc041fe623ba
		
		$data = $this->Get("/cards/".$ticket->id,'name,badges,desc,labels,url,dueComplete,idChecklists,idList');
		
		$attachements = $this->Get("/cards/".$ticket->id."/attachments",'name');
		$data->trackingno = '';
		foreach($attachements as $attachement)
		{
			$parts = explode('Tracking Number: #',$attachement->name);
			if(count($parts)==2)
				$data->trackingno = $parts[1];
		}
		
		//dump($data->idList);
		//$listinfo = $this->Get("/lists/".$data->idList,'all');
		//$data->nameList = $listinfo->name;
		//dump($listinfo->name);
		
		//return;
		//$listinfo = $this->Get("/lists/".$data->idList,'all');
		//dd($listinfo);
		$data->checkitems = [];
		if(count($data->idChecklists)>0)
		{
			foreach($data->idChecklists as $id)
			{
				if($id = '5db286ad4973fc041fe623ba')
				{
					if(isset($this->checklists[$id]))
						$checklist_data = $this->checklists[$id];
					else
						$checklist_data = $this->Get("/checklists/".$id,"checkItems");
					foreach($checklist_data->checkItems as $checkItem)
					{
						$data->checkitems[$checkItem->name]=new \StdClass();
					}
				}
			}
		}
		if($data->badges->checkItemsChecked > 0)
		{
			$url = "https://api.trello.com/1/cards/".$ticket->id."/actions?key=".$this->key.'&token='.$this->token."&filter=updateCheckItemStateOnCard";
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
			$actions = curl_exec($ch);
			curl_close($ch);
			$actions = json_decode($actions);
			
			foreach($actions as $action)
			{
				if(isset($action->data->checkItem->name))
				{
					if(isset($data->checkitems[$action->data->checkItem->name]))
					{   if(!isset($data->checkitems[$action->data->checkItem->name]->state))
						{
							$dt = explode("T",$action->date)[0];
							$data->checkitems[$action->data->checkItem->name]->state = $action->data->checkItem->state;
							$data->checkitems[$action->data->checkItem->name]->date = $dt;
						}
					}
				}
			}	
		}
		//dd($data->checkitems);
		$ticket->trackingno = $data->trackingno;
		$ticket->name = $data->name;
		$ticket->desc = $data->desc;
		$ticket->dueComplete = $data->dueComplete;
        $ticket->checkitems =   $data->checkitems;
		$ticket->url = $data->url;
		$ticket->label = '';
		foreach($data->labels as $label)
		{
			$ticket->label = $label->name;
			break;
		}
		$this->db->Update(["id"=>$ticket->id],$ticket);
	}
	
    /**
     * Execute the console command.
     *
	 
     * @return mixed
     */
    public function Get($resource,$fields)
	{
		$url="https://api.trello.com/1".$resource."?key=".$this->key."&token=".$this->token."&fields=".$fields;
		//dump($url);
		$ch = curl_init();
		// set URL and other appropriate options
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		//curl_setopt($ch, CURLOPT_HEADER, 0);
		// grab URL and pass it to the browser
		$data = curl_exec($ch);
		// close cURL resource, and free up system resources
		curl_close($ch);

		return json_decode($data);
		
	}
    public function handle()
    {
		//$boards = $this->Get("/members/me/boards","name,id,dateLastActivity");
		//dd($boards); 5a78b043543acc40d8ba06f9
		$board = $this->Get("/boards/5a78b043543acc40d8ba06f9","dateLastActivity,name");
		echo "Updating ".$board->name."\n";
		$minutes = $this->option('beat');
		$this->db = new Database($this->server,$this->dbname);
		$this->lists = ["Upcoming"=>"5aa212ebaac901de308b610c","Shipment"=>"5a78b08798546b40f68be6ee","Custom"=>"5a7c3ccdb5181394a42a0b06","Expense"=>"5a78b08c6f85c304e464aa07"];
		foreach($this->lists as $name=>$list)
		{
			echo "Processing List ".$name."\n";
			$listdata = $this->Get("/lists/".$list."/cards","dateLastActivity,closed");
			$total = count($listdata);
			
			$inprocess = 1;
			foreach($listdata as $ticket)
			{

				$ticket->dayLastActivity = explode("T",$ticket->dateLastActivity)[0];
				$stickets = $this->db->Read(["id"=>$ticket->id]);
				$stickets = $stickets->toArray();
				if(count($stickets)==0)
				{
					echo "Processing ticket $inprocess/$total ".$ticket->id."\n";
					$ticket->list = $name;
					$this->UpdateTicket($ticket);
					$inprocess++;
					continue;
				}
				foreach($stickets as $sticket)
				{
					//if($sticket->dueComplete === true)
					if(($sticket->dateLastActivity != $ticket->dateLastActivity)||!isset($sticket->name))
					{
						echo "Processing ticket $inprocess/$total ".$sticket->id."\n";
						$sticket->dateLastActivity= $ticket->dateLastActivity;
						$sticket->dayLastActivity= $ticket->dayLastActivity;
						$sticket->list = $name;
						$this->UpdateTicket($sticket);
						
					}
					break;
				}
				$inprocess++;
			}
			file_put_contents("lastupdated_ishipment",$board->dateLastActivity);
		}
    }
}
