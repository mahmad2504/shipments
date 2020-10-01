<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Database;
class Sync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync {--beat=0}';

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
    public function __construct()
    {
		$this->key=env("TRELLO_KEY"); 
		$this->token=env("TRELLO_TOKEN"); 
		parent::__construct();
    }
    public function UpdateTicket($ticket)
	{
		
		//Fetch activity
		// dueComplete should be set when delivered 
		//https://api.trello.com/1/cards/5f0eba3c9b5a3854607f8573/actions?key=005173e331a61db3768a13e6e9d1160e&token=0e457d47dbd6eb1ed558ac42f8ba03b94738cac35a738d991cdf797d6fcfbbe9&filter=updateCard&fields=date
		//$url = "https://api.trello.com/1/cards/".$ticket->id."?key=".$this->key.'&token='.$this->token."&fields=name,badges,desc,labels,url,dueComplete";
		$data = $this->Get("/cards/".$ticket->id,'name,badges,desc,labels,url,dueComplete,idChecklists');
		$data->checkitems = [];
		if(count($data->idChecklists)>0)
		{
			$checklist_data = $this->Get("/checklists/".$data->idChecklists[0],"checkItems");
			foreach($checklist_data->checkItems as $checkItem)
			{
				$data->checkitems[$checkItem->name]=$checkItem->state;
			}
			
		}
		//$url = "https://api.trello.com/1/cards/".$ticket->id."?key=".$this->key.'&token='.$this->token;
		
		//$data = file_get_contents($url);
		//$ch = curl_init();
		// set URL and other appropriate options
		//curl_setopt($ch, CURLOPT_URL, $url);
		//curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		//curl_setopt($ch, CURLOPT_HEADER, 0);
		// grab URL and pass it to the browser
		//$data = curl_exec($ch);
		// close cURL resource, and free up system resources
		//curl_close($ch);
		
		//$data = json_decode($data);
		//$states = $this->Get("/cards/".$ticket->id."/checkItemStates",'all');
		
		
		//$url = "https://api.trello.com/1/cards/".$ticket->id."/checkItemStates?key=".$this->key.'&token='.$this->token;
		//$ch = curl_init();
		// set URL and other appropriate options
		//curl_setopt($ch, CURLOPT_URL, $url);
		//curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		//curl_setopt($ch, CURLOPT_HEADER, 0);
		// grab URL and pass it to the browser
		//$states = curl_exec($ch);
		// close cURL resource, and free up system resources
		//curl_close($ch);
		
		//$states = json_decode($states);
		
			
	//	dump($states);
		
		$ticket->name = $data->name;
		$ticket->desc = $data->desc;
		$ticket->dueComplete = $data->dueComplete;
        $ticket->checkitems =   $data->checkitems;
		if(($ticket->dueComplete)||(!isset($ticket->createdon)))
		{
			$url = "https://api.trello.com/1/cards/".$ticket->id."/actions?key=".$this->key.'&token='.$this->token."&filter=updateCard";
			//$actions = file_get_contents($url);
			$ch = curl_init();
			// set URL and other appropriate options
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
			//curl_setopt($ch, CURLOPT_HEADER, 0);
			// grab URL and pass it to the browser
			$actions = curl_exec($ch);
			// close cURL resource, and free up system resources
			curl_close($ch);
			$actions = json_decode($actions);
			
			foreach($actions as $action)
			{
				if(isset($action->data->card->dueComplete))
					if($action->data->card->dueComplete === true)
						$ticket->deliveredon = explode("T",$action->date)[0];
			}	
			if(isset($action))
				$ticket->createdon = explode("T",$action->date)[0];
		}
		if($ticket->dueComplete == false)
			$ticket->deliveredon = '';
		
		$ticket->checkItems = $data->badges->checkItems;
		$ticket->checkItemsChecked = $data->badges->checkItemsChecked;
		$ticket->url = $data->url;
		$ticket->due = explode("T",$data->badges->due)[0];
		$ticket->progress = ($ticket->checkItemsChecked/$ticket->checkItems)*100;
		$ticket->label = '';
		foreach($data->labels as $label)
		{
			$ticket->label = $label->name;
			break;
		}
		//$sticket->dateLastActivity= $ticket->dateLastActivity;
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
		$minutes = $this->option('beat');
		if($minutes % 10 == 0)// Every 10 minutes
		    file_get_contents("https://script.google.com/macros/s/AKfycbwCNrLh0BxlYtR3I9iW2Z-4RQK88Hryd4DEC03lIYLoLCce80A/exec?func=alive&device=localshipments");
		else
			return;
		//$this->checklist_data = $this->Get("/checklists/5f756c81d94bde6ff4560019","checkItems");
		//dump($this->checklist_data);
		//$this->checklist_data = $this->Get("/checklists/5f74b1da68758c4ce61b0077","checkItems");
		//dump($this->checklist_data);
		//foreach( $this->checklist_data->checkItems as $checkItems)
		//{
		//	dump($checkItems);
		//}
		
	//$url = "https://api.trello.com/1/cards/".'5efed2f133625080952d69bb'."?key=005173e331a61db3768a13e6e9d1160e&token=0e457d47dbd6eb1ed558ac42f8ba03b94738cac35a738d991cdf797d6fcfbbe9";
	
	//$url = "https://api.trello.com/1/lists/".'5e96d7c8ebdb461cc84f83ba'."/cards?key=005173e331a61db3768a13e6e9d1160e&token=0e457d47dbd6eb1ed558ac42f8ba03b94738cac35a738d991cdf797d6fcfbbe9";
			
	//$data = file_get_contents($url);
	//$data = json_decode($data);	
	//dd($data);
	//return;
		$this->db = new Database();
		$boards = $this->Get("/members/me/boards","name,id,dateLastActivity");
		
        //$url="https://api.trello.com/1/members/me/boards?key=".$this->key."&token=".$this->token."&fields=name,id,dateLastActivity";
		//$data = file_get_contents($url);
		//$ch = curl_init();
		// set URL and other appropriate options
		//curl_setopt($ch, CURLOPT_URL, $url);
		//curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		//curl_setopt($ch, CURLOPT_HEADER, 0);
		// grab URL and pass it to the browser
		//$data = curl_exec($ch);
		// close cURL resource, and free up system resources
		//curl_close($ch);

		//$boards = json_decode($data);
		$board = null;
		foreach($boards as $b)
		{
			if($b->id == '5e96d769cab6ce1d5e4fdb91')
			{
				$board = $b;
				break;
			}	
			//echo $b->name." ".$b->id."\n";
		}
		if($board == null)
		{
			echo "Board Not Found\n";
			return;
		}
		
		if(file_exists("lastupdated"))
		{
			$lastupdated = file_get_contents("lastupdated");
			//if($board->dateLastActivity == $lastupdated)
			//	return;
			
		}
		echo "Updating ".$board->name."\n";
		
		echo $board->dateLastActivity."\n";
		//$url="https://api.trello.com/1/boards/5e96d769cab6ce1d5e4fdb91/lists?key=005173e331a61db3768a13e6e9d1160e&token=0e457d47dbd6eb1ed558ac42f8ba03b94738cac35a738d991cdf797d6fcfbbe9";
		$lists =  ["5e96d7c8ebdb461cc84f83ba","5e96d7a0dffcf41ccac595c6"];
		
		//$user = $this->db->where( 'id', '=', '5efb567cb1442d86786cf203' )->fetch();
		//dd($user);
	
		foreach($lists as $list)
		{
			echo "Processing List ".$list."\n";
			$listdata = $this->Get("/lists/".$list."/cards","dateLastActivity,closed");
						
			//$url = "https://api.trello.com/1/lists/".$list."/cards?key=005173e331a61db3768a13e6e9d1160e&token=0e457d47dbd6eb1ed558ac42f8ba03b94738cac35a738d991cdf797d6fcfbbe9&fields=dateLastActivity,closed";
			//$listdata = file_get_contents($url);
			
			//$ch = curl_init();
			// set URL and other appropriate options
		///	curl_setopt($ch, CURLOPT_URL, $url);
			//curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
			//curl_setopt($ch, CURLOPT_HEADER, 0);
			// grab URL and pass it to the browser
			//$listdata = curl_exec($ch);
			// close cURL resource, and free up system resources
			//curl_close($ch);
			
			
			//$listdata = json_decode($listdata );
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
						$this->UpdateTicket($sticket);
						
					}
					break;
				}
				$inprocess++;
			}
			//dd(json_decode($listdata));
			//$url = "https://api.trello.com/1/cards/5efcd1ec74b57929f0c5e9c2?key=005173e331a61db3768a13e6e9d1160e&token=0e457d47dbd6eb1ed558ac42f8ba03b94738cac35a738d991cdf797d6fcfbbe9&fields=dateLastActivity,closed,name,checkItemStates";
			
			
			//$data = file_get_contents($url);
			//dd(json_decode($data));
		}
		//$data = file_get_contents($url);
		//dd(json_decode($data));
		//$ch = curl_init();
		//curl_setopt($ch, CURLOPT_URL, $url);
		//$response = curl_exec($ch);
		//dd($response);
		//$data = file_get_contents($url);
		
		file_put_contents("lastupdated",$board->dateLastActivity);
    }
}
