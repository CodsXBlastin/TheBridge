<?php

/*
  _____    _  _    __  __     ___   
 |_   _|  | || |   \ \/ /    / __|  
   | |    | __ |    >  <    | (__   
  _|_|_   |_||_|   /_/\_\    \___|  
_|"""""| _|"""""| _|"""""| _|"""""| 
"`-0-0-' "`-0-0-' "`-0-0-' "`-0-0-' 
**/

namespace bridge\task;

use pocketmine\scheduler\Task;
use bridge\Main;

class BridgeTask extends Task{
	
	public function __construct(Main $plugin){
		$this->plugin = $plugin;
	}
	
	public function onRun($timer){
		$this->plugin->updateArenas(true);
	}
}