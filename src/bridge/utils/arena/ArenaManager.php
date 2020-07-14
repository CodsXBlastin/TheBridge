<?php

/*
  _____    _  _    __  __     ___   
 |_   _|  | || |   \ \/ /    / __|  
   | |    | __ |    >  <    | (__   
  _|_|_   |_||_|   /_/\_\    \___|  
_|"""""| _|"""""| _|"""""| _|"""""| 
"`-0-0-' "`-0-0-' "`-0-0-' "`-0-0-' 
**/

     namespace bridge\utils\arena;


     use pocketmine\level\Position;
     use pocketmine\Player;

     use pocketmine\nbt\tag\{CompoundTag, ByteTag, IntTag, ListTag, DoubleTag, FloatTag
};

     use pocketmine\event\entity\EntityDamageEvent;

     use pocketmine\block\Block;
     use pocketmine\item\Item;
     use pocketmine\entity\Effect;
     use pocketmine\item\enchantment\Enchantment;

     use pocketmine\utils\Color;
     use pocketmine\utils\TextFormat as T;
     use Scoreboards\Scoreboards;

     use bridge\utils\Team;
     use bridge\utils\Utils;
     use bridge\Main;


    use pocketmine\math\Vector3;
    use pocketmine\math\Vector2;
    use pocketmine\utils\Config;
    
    use xenialdan\apibossbar\BossBar;

/**
 * Class Arena
 * @package Bridge\Untils\Arena
 */
class ArenaManager{
    
    
	const STAT_ESPERA = 0;
	const STAT_STATING = 1;
	const STAT_START = 2;
	const STAT_RUN = 3;
	const STAT_RESTART = 5;
	const STAT_GANHO = 6;
	
	private $players = [];
	
	public $stat = 0;
	
	private $time = 0;
	private $times = 0;
	
	public $plugin;
	private $nametag = [];
	
	public function __construct(Main $plugin, $data){
		$this->plugin = $plugin;
		$this->data = $data;
		$this->initMapa();
		$this->reset(false);
	}
	
	public function getUtils(){
		return (new Utils($this->plugin));
	}
	
	public function initMapa(){
		$utils = $this->getUtils();
		$map = $this->data["world"];
		if($utils->backupExists($map)){
			$this->resetMap();
			return true;
		}
		$utils->backupMap($map, $this->plugin->getDataFolder());
	}
	
	public function resetMap(){
		$utils = $this->getUtils();
		$map = $this->data["world"];
		$utils->resetMap($map);
	}
	
	public function getServer(){
		return $this->plugin->getServer();
	}
	
	public function getData(){
		return $this->data;
	}
	
	public function getPlayers(){
		return $this->players;
	}
	
	public function getConfig(){
		return $this->plugin->getConfig()->getAll();
	}
	
	public function getPos1($v = true){
		$data = $this->getData();
		if(isset($data["pos1"])){
			$dt = $data["pos1"];
			if(!$v){
				return new Vector2($dt["x"], $dt["z"]);
			}
			if(isset($data["world"])){
				$name = $data["world"];
				if($this->isLoad($name)){
					$level = $this->getServer()->getLevelByName($name);
					$pos = new Position($dt["x"], $dt["y"], $dt["z"], $level);
					return $pos;
				}
			}
		}
		return null;
	}
	
	public function getPos2($v = true){
		$data = $this->getData();
		if(isset($data["pos2"])){
			$dt = $data["pos2"];
			if(!$v){
				return new Vector2($dt["x"], $dt["z"]);
			}
			if(isset($data["world"])){
				$name = $data["world"];
				if($this->isLoad($name)){
					$level = $this->getServer()->getLevelByName($name);
					$pos = new Position($dt["x"], $dt["y"], $dt["z"], $level);
					return $pos;
				}
			}
		}
		return null;
	}
	
	public function getSpawn1(){
		$data = $this->getData();
		if(isset($data["spawn1"])){
			$dt = $data["spawn1"];
			if(isset($data["world"])){
				$name = $data["world"];
				if($this->isLoad($name)){
					$level = $this->getServer()->getLevelByName($name);
					$pos = new Position($dt["x"], $dt["y"], $dt["z"], $level);
					return $pos;
				}
			}
		}
		return null;
	}
	
	public function getSpawn2(){
		$data = $this->getData();
		if(isset($data["spawn2"])){
			$dt = $data["spawn2"];
			if(isset($data["world"])){
				$name = $data["world"];
				if($this->isLoad($name)){
					$level = $this->getServer()->getLevelByName($name);
					$pos = new Position($dt["x"], $dt["y"], $dt["z"], $level);
					return $pos;
				}
			}
		}
		return null;
	}
	
	public function getRespawn1($v = true){
		$data = $this->getData();
		if(isset($data["respawn1"])){
			$dt = $data["respawn1"];
			if(!$v){
				return new Vector2($dt["x"], $dt["z"]);
			}
			if(isset($data["world"])){
				$name = $data["world"];
				if($this->isLoad($name)){
					$level = $this->getServer()->getLevelByName($name);
					$pos = new Position($dt["x"], $dt["y"], $dt["z"], $level);
					return $pos;
				}
			}
		}
		return null;
	}
	
	public function getRespawn2($v = true){
		$data = $this->getData();
		if(isset($data["respawn2"])){
			$dt = $data["respawn2"];
			if(!$v){
				return new Vector2($dt["x"], $dt["z"]);
			}
			if(isset($data["world"])){
				$name = $data["world"];
				if($this->isLoad($name)){
					$level = $this->getServer()->getLevelByName($name);
					$pos = new Position($dt["x"], $dt["y"], $dt["z"], $level);
					return $pos;
				}
			}
		}
		return null;
	}
	
	public function getLevel(){
		$data = $this->getData();
		if(isset($data["world"])){
			$name = $data["world"];
			if($this->isLoad($name)){
				$level = $this->getServer()->getLevelByName($name);
				return $level;
			}
		}
		return null;
	}
	
	public function isTeamMode(){
		$data = $this->getData();
		if(isset($data["mode"])){
			if($data["mode"] == "team" or $data["mode"] == "squad"){
				return true;
			}
		}
		return false;
	}
	
	public function getSpawn(){
		$data = $this->getData();
		if(isset($data["local-de-espera"])){
			$dt = $data["local-de-espera"];
			if(!isset($dt["level"])){
				return null;
			}
			$pos = new Position($dt["x"], $dt["y"], $dt["z"]);
			if($this->isLoad($dt["level"])){
				$level = $this->getServer()->getLevelByName($dt["level"]);
				$pos->setLevel($level);
			} else {
				$this->broadcast("§c§lErro§r§c No Such Thing As " . $dt["level"] . " Doesn't exist!");
				return null;
			}
			return $pos;
		}
		return null;
	}
	
	public function isInArena($name){
		if($name instanceof Player){
			$name = $name->getName();
		}
		$name = strtolower($name);
		if(isset($this->players[$name])){
			return true;
		}
		return false;
	}
	
	public function getNecCount(){
		$data = $this->getData();
		if(isset($data["mode"])){
			switch($data["mode"]){
				case "solo":
				return 2;
				case "team":
				return 4;
				case "squad":
				return 8;
			}
		}
		return 2;
	}
	
	public function onRun($timer = null){
		$nec = $this->getNecCount();
		switch($this->stat){
			case self::STAT_ESPERA:
			$players = $this->getPlayers();
			if(count($players) >= $nec){
				$this->stat = self::STAT_STATING;
			} else {
				if(count($players) <= 0){
					return true;
				}
				$this->broadcast("§l§eTHEBRIDGE\n§r§bWaiting for players to start§7( " . ($nec - count($players)). " ...");
			}
			break;
			case self::STAT_STATING:
			$players = $this->getPlayers();
			if(count($players) < $nec){
				$this->stat = self::STAT_ESPERA;
				$this->time = 20;
			} else {
				$this->time--;
				$time = $this->time - 6;
				if($time <= 0){
	
					$thi->stat = self::STAT_START;
					$this->replaceSpawn();
					$this->teleportPlayers($this->getPlayers());
				} else {
					$temp = $this->getTemp($time);
					$message = "§a§lGAMES STARTS:\n§r§6 in§b§f $temp";
					$this->broadcast($message);
				}
			}
			break;
			case self::STAT_START:
			$this->time--;
			if($this->time <= 0){
				$this->stat = self::STAT_RUN;

			}
			return true;
			break;
			case self::STAT_RUN:
			    $this->time = 12000;
				$time = $this->time - 12000;
			    $level = $this->getLevel();
			//just to make sure it works
			$this->removeY($this->getSpawn1(), true, null, 4);
			$this->removeY($this->getSpawn2(), true, null, 4);

			if(!is_null($level)){
				foreach($level->getPlayers() as $p){
				    $name = $data["world"];
				    $map = $this->getSever()->getLevelByName($name);
				    $data = $this->getData();

				    $bar = (new BossBar())->setTitle("§l§e§oTHEBRIDGE")->setSubTitle("§l§amap§7: $map")->setPercentage(1)->addPlayer($p);

				    $goal = new Config("plugin_data/TheBridge/goals.yml", Config::YAML);
                    $goal->getAll();
                    $kills = new Config("plugin_data/Thebridge/kills.yml", Config::YAML);
                    $kills->getAll();

				    $api = Scoreboards::getInstance();
				    $api->new($p, "TheBridge", T::BOLD . T::GOLD . "THEBRIDGE" . T::RESET . T::GREEN . " ");
				    $api->setLine($p, 1, T::GRAY." ".date("d/m/Y").T::BLACK." ");
				    
				    $api->setLine($p, 2, T::WHITE."Hello§7, " . $p->getName);
				    
				    $api->setLine($p, 4, T::RED."   ");
				    $api->setLine($p, 5, T::RED."RED TEAM ".T::GREEN.$this->ponts["red"]);
				    $api->setLine($p, 6, T::BLUE."BLUE TEAM ".T::GREEN.$this->ponts["blue"] . "§a/5");
				    
				    $api->setLine($p, 7, T::RED."       ");
				    
				    $api->setLine($p, 9, T::WHITE."Goals§7: " . $goal->get($p->getName(), 0));
				    
				    $api->setLine($p, 9, T::WHITE."Kills§7: " . $kill->get($p->getName(), 0));
				    
				    $api->setLine($p, 7, T::RED."          ");
				    
				    $api->setLine($p, 8, T::GREEN."§rMode:".T::GREEN.$this->data["mode"]);
				    
				    $api->setLine($p, 9, T::RED."                ");
				    
				    $api->setLine($p, 10, T::YELLOW."testserver.com");
				    
				    $api->getObjectiveName($p);
			
			if($time <= 0){
			    $player = $p();
				$this->stat = self::STAT_GANHO;
		    $config = new Config("plugin_data/Thebridge/kills.yml", Config::YAML);
            $config->getAll();
            $config->set($player->getName(), $config->remove($player->getName(), "               "));
            $config->set($player->getName(), $config->remove($player->getName(), "0"));
            $config->save();
		    $gconfig = new Config("plugin_data/Thebridge/goals.yml", Config::YAML);
            $gconfig->getAll();
            $gconfig->set($player->getName(), $gconfig->remove($player->getName(), "               "));
            $gconfig->set($player->getName(), $gconfig->remove($player->getName(), "0"));
            $gconfig->save();
				}
			}
		}
			
			$this->initPlayers();
			break;
			case self::STAT_RESTART:
			$this->time--;
			if($this->time <= 0){
				$this->stat = self::STAT_RUN;
				$this->broadcast("§l§eSCORE §r\n§7  §9Blue§r§f " . $this->ponts["blue"] . " §7vs §cRed§r§f " . $this->ponts["red"] . "§8\n§8\n§8\n§8\n", 2);
				$this->startGame();
			} else {
				$this->broadcast("§l§6" . $this->time . "§r§8\n§8\n§8\n§8\n§8", 2);
			}
			return true;
			break;
			case self::STAT_GANHO:
			$this->segs--;
			$this->broadcast($this->lastMessage, 2);
			if($this->segs <= 0){
				$players = $this->getPlayers();
				foreach($players as $name => $pl){
				    $players = $pl();
		    $config = new Config("plugin_data/Thebridge/kills.yml", Config::YAML);
            $config->getAll();
            $config->set($player->getName(), $config->remove($player->getName(), "               "));
            $config->set($player->getName(), $config->remove($player->getName(), "0"));
            $config->save();
		    $gconfig = new Config("plugin_data/Thebridge/goals.yml", Config::YAML);
            $gconfig->getAll();
            $gconfig->set($player->getName(), $gconfig->remove($player->getName(), "               "));
            $gconfig->set($player->getName(), $gconfig->remove($player->getName(), "0"));
            $gconfig->save();
					$p = $this->plugin->getServer()->getPlayerExact($name);
					if(is_null($p)){
						unset($this->players[$name]);
						continue;
					}
					$this->quit($p);
				}
				$this->reset(true);
			}
			break;
		}
	}
	
	private $segs = 10;
	
	private function addWin($winner = "blue"){
		$date = $this->getServer()->getPluginManager()->getPlugin("Slapperwin");
		
		$players = $this->getPlayers();
		foreach($players as $name => $pl){
			$team = $this->getTeam($name);
			if($team == $winner){
				if(!is_null($date)){
					$date->addWin($name, "TheBridgeTopWinner");
				}
			}
		}
	}
	
	public function getCount(){
		$count = count($this->players);
		return $count;
	}
	
	private $base = [
	"blue" => 0,
	"red" => 0
	];
	
	private $ponts = [];
	private $team = null;
	
	public function getTeamData(){
		if(is_null($this->team)){
			$nec = $this->getNecCount() / 2;
			$this->team = new Team($nec);
		}
		return $this->team;
	}
	
	private function setRadomTeam($name){
		$data = $this->getTeamData();
		
		if(!$data->isInTeam($name)){
			if($this->setTeam("blue", $name)){
				$this->broadcast("§e$name has joined §bblue team", 3);
			} elseif($this->setTeam("red", $name)){
				$this->broadcast("§e$name has joined §cred team", 3);
			}
			return true;
		}
		return false;
	}
	
	public function setTeam($team = "blue", $name){
		$data = $this->getTeamData();
		
		if($this->getTeam($name) == $team){
			return true;
		}
		if($data->addPlayerTeam($name, $team)){
			return true;
		}
		return false;
	}
	
	private function setTeams(){
		$data = $this->getTeamData();
		$players = $this->getPlayers();
		
		if(count($players) <= 0){
			$this->reset();
			return true;
		}
		foreach($players as $name => $p){
			if(!$data->isInTeam($name)){
				if($this->setTeam("blue", $name)){
					$this->broadcast("§l§6$name§r§e has joined §bBlue team", 3);
				} elseif($this->setTeam("red", $name)){
					$this->broadcast("§l§6$name§r§e has joined§c Red team", 3);
				}
			}
		}
	}
	
	public function getTeam($name){
		$data = $this->getTeamData();
		return $data->getPlayerTeam($name);
	}
	
	public function isTeam($p1, $p2){
		$data = $this->getTeamData();
		if($data->isTeam($p1, $p2)){
			return true;
		}
		return false;
	}
	
	public function reset($value = true){
		$players = $this->getPlayers();
		$this->players = [];
		
		$this->time = 15;
		$this->segs = 10;
		$this->winner = "blue";
		$this->stat = self::STAT_ESPERA;
		
		$this->getTeamData()->reset();
		$this->nametag = [];
		$this->ponts = $this->base;
		
		if($value){
			$this->resetMap();
		}
	}
	
	public function close(){
		$players = $this->getPlayers();
		if(count($players) <= 0){
			$this->reset(true);
			return true;
		}
		foreach($players as $name => $pl){
			$p = $this->plugin->getServer()->getPlayerExact($name);
			if(!is_null($p)){
				$this->quit($p);
			} else {
				unset($this->players[$name]);
			}
		}
		$this->reset(true);
	}
	
	private $lastMessage = "";
	
	public function broadcast($message, $type = 1){
		$players = $this->getPlayers();
		if(count($players) <= 0){
			if($message !== 2){
				$this->reset();
			}
			return true;
		}
		foreach($players as $name => $pl){
			$p = $this->plugin->getServer()->getPlayerExact($name);
			if(is_null($p)){
				unset($this->players[$name]);
				continue;
			} elseif(!$this->isInArena($p)){
				unset($this->players[$name]);
				continue;
			}
		
			if($message == 2){
				$team = $this->team[$name] == "blue" ? "§l§9BLUE§r" : "§l§cRED§r";
				$p->sendTip("§l                                             §l§cTHE §eBRIDGE§r§b (" . $this->data["mode"] . ")\n§8\n§8                                                          §fPLAYERS:§r§a " . count($this->players) . "§8\n§8                                                          §fTime§a: $team §r§8\n§8\n§8                                                         §l§aDragon§eBE\n§8\n§8\n§8\n§8", 2);
				continue;
			}
			$this->lastMessage = $message;
			switch($type){
				case 1:
				$p->sendPopup($message);
				break;
				case 2:
				$p->sendTip($message);
				break;
				case 3:
				$p->sendMessage($message);
				break;
			}
		}
	}
	


	public function initPlayers(){
		$nec = $this->getNecCount() / 2;
		foreach($this->players as $name => $pl){
			$p = $this->plugin->getServer()->getPlayerExact($name);
			if(is_null($p)){
				unset($this->players[$name]);
				continue;
			} elseif(!$this->isInArena($p)){
				unset($this->players[$name]);
				continue;
			}
		}
		
		$count = count($this->players);
		if($count <= 0){
			$this->reset(true);
			return false;
		} elseif($count <= $nec){
			$value = false;
			if($this->isTeamMode()){
				$data = $this->players;
				if($count <= 1){
					$value = true;
				} elseif($this->isTeam(array_shift($data), array_shift($data))){
					$value = true;
				}
			} else {
				$value = true;
			}
			if($value){
				$data = $this->players;
				$team = $this->getTeam(array_shift($data));
				
			
				switch($team){
					case "blue":
					
				}
				$players = $this->getPlayers();
				foreach($players as $name => $pl){
					$p = $this->plugin->getServer()->getPlayerExact($name);
					if(is_null($p)){
						unset($this->players[$name]);
						continue;
					}
					$this->respawnPlayer($p, false);
				}
				//when the games end
				$p->addTitle("§l§6VICTORY!", "§7Your team won the match!");
				$this->segs = 10;
				$this->stat = self::STAT_GANHO;
				$this->addWin($team);
				return true;
			}
		}
	}
	
	public function startGame($value = true){
	    $this->removeY($this->getSpawn1(), true, null, 4);
		$this->removeY($this->getSpawn2(), true, null, 4);
	}
	
	public function replaceSpawn($value = true){
		$this->removeY($this->getSpawn1(), false);
		$this->removeY($this->getSpawn2(), false);
	}
	
	public function removeY($pos, $v = true, $dis = null, $ad = 0){
		$level = $this->getLevel();
		if(is_null($dis)){
			$dis = $this->getNecCount();
			$dis = $dis > 4 ? 4 : $dis;
		}
		$yy = $v ? 3 : 5;
		$yy += $ad;
		for($x = $pos->x - $dis; $x <= $pos->x + $dis; $x++){
			for($y = $pos->y + $yy; $y >= $pos->y - 1; $y--){
				for($z = $pos->z + $dis; $z >= $pos->z - $dis; $z--){
					if($v == true){
						$level->setBlock(new Vector3($x, $y, $z), Block::get(0));
					} else {
						$level->setBlock(new Vector3($x, $y, $z), Block::get(20));
					}
				}
			}
		}
		if(!$v){
			$this->removeY($pos->add(0, 1), true, $dis - 1);
		}
	}
	
	public function teleportPlayers($players){
		$level = $this->getLevel();
		
		foreach($players as $name => $pl){
			$p = $this->getServer()->getPlayerExact($name);
			if(is_null($p)){
				unset($this->players[$name]);
				continue;
			}
			
			$named = "§c" . $p->getName();
			$pos = $this->getSpawn2();
			
			$team = $this->getTeam($name);
			
			switch($team){
				case "blue":
				$named = "§b" . $p->getName();
				$pos = $this->getSpawn1();
				break;
				case "red":
				$named = "§c" . $p->getName();
				$pos = $this->getSpawn2();
				break;
			}
			if(!isset($this->nametag[$name])){
				$this->nametag[$name] = $p->getNameTag();
			}
			
			$p->setNameTag($named);
			$this->addItens($p);
			$p->teleport($pos);
		}
	}
	
	public function quit(Player $player, $msg = true){
		$name = strtolower($player->getName());
		if(!$this->isInArena($player)){
			return false;
		}
		
		$player->getInventory()->clearAll();
		$player->getArmorInventory()->clearAll();
		$player->getCursorInventory()->clearAll();
		$player->setFood(20);
		$player->setHealth(20);
		$player->setGamemode(0);
		$player->removeAllEffects();
		$player->setHealth($player->getMaxHealth());
		//Just to make sure it clears
		  $inv = $player->getInventory();
		  $inv->clearAll();
		$this->getTeamData()->removePlayerTeam($name);
		  
		    $config = new Config("plugin_data/Thebridge/kills.yml", Config::YAML);
            $config->getAll();
            $config->set($player->getName(), $config->remove($player->getName(), "               "));
            $config->set($player->getName(), $config->remove($player->getName(), "0"));
            $config->save();
		    $gconfig = new Config("plugin_data/Thebridge/goals.yml", Config::YAML);
            $gconfig->getAll();
            $gconfig->set($player->getName(), $gconfig->remove($player->getName(), "               "));
            $gconfig->set($player->getName(), $gconfig->remove($player->getName(), "0"));
            $gconfig->save();

		
		if(isset($this->nametag[$name])){
			$player->setNameTag($this->nametag[$name]);
			unset($this->nametag[$name]);
		}
		$level = $this->getServer()->getDefaultLevel();
		
		unset($this->players[$name]);
		if($msg){
			$player->sendMessage("");
		}
	}
	
	public function join(Player $player){
		$nec = $this->getNecCount();
		if($this->stat == self::STAT_ESPERA){
			if(count($this->players) >= $nec){
				return false;
			}
		} elseif($this->stat < 2){
			if(count($this->players) >= $nec){
				return false;
			}
			
		} else {
			return false;
		}
		$player->setGamemode(Player::SURVIVAL);
		$player->removeAllEffects();
		$player->setHealth($player->getMaxHealth());
		$player->setFood(20);
		$player->setAllowFlight(false);
		
		  $inv = $player->getInventory();
		 
		  $inv->clearAll();
		  $inv->setItem(0, Item::get(44, 0, 1));
		 
		 $inv = $player->getInventory();
		 $inv->clearAll();
		 
		 $inv->setItem(1, Item::get(120, 0, 1)->setCustomName("§r§eBack To Hub\n§7 (Click To Leave)"));
		 $inv->setItem(3, Item::get(54, 0, 1)->setCustomName("§r§eKits\n§7 (Click To Switch)"));
		 //ITEMS
		 $inv->setItem(5, Item::get(54, 0, 11)->setCustomName("§r§7Join:§9§l BLUE§r\n§7 (Click To Enter)"));
		 $inv->setItem(7, Item::get(54, 0, 14)->setCustomName("§r§7Join:§c§lRED§r\n§7 (Click To Enter)"));

		 
		 $spawn = $this->getSpawn();
		 if(!is_null($spawn)){
		 	$player->teleport($spawn);
		 }
		
		$name = strtolower($player->getName());
		$this->players[$name] = $player;
		
		$this->setRadomTeam($name);
		return true;
	}
	
	
	public function respawnPlayer($p, $v = true){
		$name = strtolower($p->getName());
		$this->addItens($p, $v);
		
		$team = $this->getTeam($name);
		if(!is_null($team)){
			switch($team){
				case "blue":
				$pos = $this->getRespawn1();
				$p->teleport($pos);
				break;
				case "red":
				$pos = $this->getRespawn2();
				$p->teleport($pos);
				break;
			}
		}
	}
	public function addItens($p, $v = true){
		
		$p->setGamemode(Player::SURVIVAL);
		$p->setHealth($p->getMaxHealth());
		$p->setFood(20);
		
		if(!$v){
			$p->removeAllEffects();
			$inv = $p->getInventory();
			$inv->clearAll();
			
			$inv->setItem(7, Item::get(355, 0, 1)->setCustomName("§r§cTap To Leave\n§7 (Click To Leave)"));
			return true;
		}
		
		$name = strtolower($p->getName());
		$damage = 14;
		
		$team = $this->getTeam($name);
		if(!is_null($team)){
			switch($team){
				case "blue":
				$damage = 11;
				break;
				case "red":
				$damage = 14;
				break;
			}
		}
			
		}
	
		$arco = Item::get(261, 0, 1);
		$flecha = Item::get(262, 0, 8);
		$food = Item::get(364, 0, 5);
		$food2 = Item::get(322, 0, 2);
		$block = Item::get(159, $damage, 64);
		
		$inv->setItem(0, $esp);
		$inv->setItem(1, $pic);
		$inv->setItem(2, $arco);
		
		$inv->setItem(3, $block);
		$inv->setItem(4, $block);
		$inv->setItem(6, $food2);
		$inv->setItem(7, $food);
		$inv->setItem(32, $flecha);
		
		$cap = Item::get(298, 0, 1);
		
		$peit = Item::get(299, 0, 1);

		$calc = Item::get(300, 0, 1);
		
		$bot = Item::get(301, 0, 1);
		
		$p->getArmorInventory()->setHelmet($cap);
		$p->getArmorInventory()->setChestplate($peit);
		$p->getArmorInventory()->setLeggings($calc);
		$p->getArmorInventory()->setBoots($bot);
	}
	
	public function getPontPos($p, $v = true){
		$name = strtolower($p->getName());
		$team = $this->getTeam($name);
		
		if(!is_null($team)){
			switch($team){
				case "blue":
				if(!$v){
					return $this->getPos1();
				}
				$pos = $this->getPos2();
				return $pos;
				case "red":
				if(!$v){
					return $this->getPos2();
				}
				$pos = $this->getPos1();
				return $pos;
			}
		}
	}
	
	public function addPont($p){
		$name = strtolower($p->getName());
		
		$team = $this->getTeam($name);
		if(!is_null($team)){
			if(isset($this->ponts[$team])){
				if($this->ponts[$team] >= 5){
					return true;
				}
				$this->ponts[$team]++;
			}
			$p->addTitle("§r§6" . $p->getName() ." §l§aSCORED!");
			$msg = "§o§l§eT§6B§r§b " . $p->getName() . " §l§eSCORED§r§6 " . $this->ponts[$team];
			$msg2 = " §4§lRED§r §cHas Joined The Match";
			switch($team){
				case " blue":
				$p->addTitle("§6" . $p->getName() ." §l§eSCORED!");
				$msg = "§o§l§eT§6B§r§b " . $p->getName() . " §l§eSCORED§r§6 " . $this->ponts[$team];
				$msg2 = " §l§bBLUE §r§bHas Joined The Match!";
			}
			$this->broadcast("$msg", 3);
			if($this->ponts[$team] >= 5){
				$players = $this->getPlayers();
				foreach($players as $name => $pl){
					$p = $this->plugin->getServer()->getPlayerExact($name);
					if(is_null($p)){
						unset($this->players[$name]);
						continue;
					}
					$this->respawnPlayer($p, false);
				}
				$this->broadcast("$msg2\n§8\n§8\n§8\n", 2);
				$this->segs = 10;
				$this->stat = self::STAT_GANHO;
				$p->addTitle("§l§6GAME END!", "§7The game has ended!");
				$this->addWin($team);
				return true;
			}
		}
		$this->replaceSpawn();
		$this->teleportPlayers($this->getPlayers());
		foreach($this->players as $name => $p){
			if($this->getTeam($p) == $team){
				if($this->hasHab($p, "velocista")){
					$eff = Effect::getEffect(1);
					$eff->setDuration(80*20);
					$eff->setAmplifier(1);
					
					$p->addEffect($eff);
				}
			}
		}
		
		$this->time = 6;
		$this->stat = self::STAT_RESTART;
	}
	
	public function getPont($team = "blue"){
		if(!isset($this->ponts[$team])){
			return 0;
		}
		return $this->ponts[$team];
	}
	
	public function getTemp($time){
		$seg = (int)($time % 60);
		$time /= 60;
		$min = (int)($time % 60);
		$time /= 60;
		$hora = (int)($time % 24);
		if($seg < 10){
			$seg = "0" . $seg;
		}
		if($min < 10){
			$min = "0" . $min;
		}
		if($hora < 10){
			$hora = "0" . $hora;
		}
		return "$min:$seg";
	}
	
	public function isLoad($world){
		if($this->getServer()->isLevelLoaded($world)){
			return true;
		}
		if(!$this->getServer()->isLevelGenerated($world)){
			return false;
		}
		$this->getServer()->loadLevel($world);
		return $this->getServer()->isLevelLoaded($world);
	}
}