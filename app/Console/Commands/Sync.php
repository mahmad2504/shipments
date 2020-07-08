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
		$url = "https://api.trello.com/1/cards/".$ticket->id."?key=".$this->key.'&token='.$this->token."&fields=name,badges,desc,labels";
		$data = file_get_contents($url);
		$data = json_decode($data);
		
		$ticket->name = $data->name;
		$ticket->desc = $data->desc;
		$ticket->checkItems = $data->badges->checkItems;
		$ticket->checkItemsChecked = $data->badges->checkItemsChecked;
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

    public function handle()
    {
		$minutes = $this->option('beat');
		if($minutes % 10 == 0)// Every 10 minutes
		    file_get_contents("https://script.google.com/macros/s/AKfycbwCNrLh0BxlYtR3I9iW2Z-4RQK88Hryd4DEC03lIYLoLCce80A/exec?func=alive&device=localshipments");
		else
			return;
	//$url = "https://api.trello.com/1/cards/".'5efed2f133625080952d69bb'."?key=005173e331a61db3768a13e6e9d1160e&token=0e457d47dbd6eb1ed558ac42f8ba03b94738cac35a738d991cdf797d6fcfbbe9";
	
	//$url = "https://api.trello.com/1/lists/".'5e96d7c8ebdb461cc84f83ba'."/cards?key=005173e331a61db3768a13e6e9d1160e&token=0e457d47dbd6eb1ed558ac42f8ba03b94738cac35a738d991cdf797d6fcfbbe9";
			
	//$data = file_get_contents($url);
	//$data = json_decode($data);	
	//dd($data);
	//return;
		$this->db = new Database();
        $url="https://api.trello.com/1/members/me/boards?key=".$this->key."&token=".$this->token."&fields=name,id,dateLastActivity";
		$data = file_get_contents($url);
		$boards = json_decode($data);
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
						
			$url = "https://api.trello.com/1/lists/".$list."/cards?key=005173e331a61db3768a13e6e9d1160e&token=0e457d47dbd6eb1ed558ac42f8ba03b94738cac35a738d991cdf797d6fcfbbe9&fields=dateLastActivity,closed";
			$listdata = file_get_contents($url);
			$listdata = json_decode($listdata );
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
