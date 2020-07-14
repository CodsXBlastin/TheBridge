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

    use pocketmine\event\Listener;

    use pocketmine\event\player\{PlayerMoveEvent, PlayerQuitEvent, PlayerDeathEvent, PlayerInteractEvent, PlayerCommandPreprocessEvent
};

    use pocketmine\event\server\DataPacketReceiveEvent;

    use pocketmine\event\entity\{EntityDamageByEntityEvent, EntityDamageEvent, ProjectileHitEvent, EntityExplodeEvent
};

    use pocketmine\event\block\BlockBreakEvent;
    use pocketmine\event\block\BlockPlaceEvent;

    use pocketmine\math\Vector3;
    use pocketmine\math\Vector2;
    use pocketmine\item\Item;
    use pocketmine\block\Block;

    use pocketmine\entity\Arrow;
    use pocketmine\entity\Effect;
    use pocketmine\level\Explosion;


    use pocketmine\nbt\tag\CompoundTag;
    use pocketmine\nbt\tag\IntTag;
    use pocketmine\nbt\tag\StringTag;

    use pocketmine\Player;
    use bridge\Main;

class Arena implements Listener{
	
	public function __construct(Main $plugin){
		$this->plugin = $plugin;
	}
	
	public function getPlugin(){
		return $this->plugin;
	}
	
	
	public function onMove(PlayerMoveEvent $e){
		$p = $e->getPlayer();
		$name = strtolower($p->getName());
		$arena = $this->getPlugin()->getPlayerArena($p);
		if(is_null($arena)){
			return true;
		}
		if($arena->stat < 3 or $arena->stat > 3){
			return true;
		}
		$pos = $arena->getPontPos($p);
		if($p->distance($pos) <= 3){
			$arena->addPont($p);
			return true;
		}
		$poss = $arena->getPontPos($p, false);
		if($p->distance($poss) <= 3){
			$p->getInventory()->clearAll();
			$arena->respawnPlayer($p);
			$p->sendMessage("§l§eRULES\n§r§cYou can't scored in your own well!");
		}
	}
	
	public function onBreak(BlockBreakEvent $e){
		$p = $e->getPlayer();
		$arena = $this->getPlugin()->getPlayerArena($p);
		if(is_null($arena)){
			return true;
		}
		if($arena->stat < 3 or $arena->stat > 3){
			$e->setCancelled();
			return true;
		}
		$b = $e->getBlock();
		if($b->getId() !== 159){
			$e->setCancelled();
		}
	}
	
	public function onExplode(EntityExplodeEvent $e){
		$ent = $e->getEntity();
		if($ent instanceof Arrow){
			$p = $ent->shootingEntity;
			
			if($p instanceof Player){
				$arena = $this->getPlugin()->getPlayerArena($p);
				if(is_null($arena)){
					return true;
				}
				$arr = [];
				foreach($e->getBlockList() as $block){
					if($block->getId() == 159 and $block->getDamage() >= 1){
						$arr[] = $block;
					}
				}
				$e->setBlockList($arr);
			}
		}
	}
	
	
	public function onPlace(BlockPlaceEvent $e){
		$p = $e->getPlayer();
		$arena = $this->getPlugin()->getPlayerArena($p);
		if(is_null($arena)){
			return true;
		}
		if($arena->stat < 3 or $arena->stat > 3){
			$e->setCancelled();
			return true;
		}
		$b = $e->getBlock();
		$spawn = $arena->getSpawn1();
		
		if($b->y > ($spawn->y + 15)){
			$e->setCancelled();
		
			return true;
		}
		$pos1 = $arena->getRespawn1(false);
		$pos2 = $arena->getRespawn2(false);
		$pos3 = $arena->getPos1(false);
		$pos4 = $arena->getPos2(false);
		$vector = new Vector2($b->x, $b->z);
		
		if(($vector->distance($pos1) <= 5) or ($vector->distance($pos3) <= 6) or ($vector->distance($pos4) <= 6) or ($vector->distance($pos2) <= 5)){
			$e->setCancelled();
		} else {
			$b->getLevel()->setBlock($b, $b);
		}
	}
	
	public function onDeath(PlayerDeathEvent $e){
		$p = $e->getPlayer();
		
		        }
	
	public function onInteract(PlayerInteractEvent $e){
		$p = $e->getPlayer();
		$arena = $this->getPlugin()->getPlayerArena($p);
		if(is_null($arena)){
			return true;
		}
		$item = $e->getItem();
		if($arena->useHab($item, $p)){
			$e->setCancelled();
			return true;
		}
		if($e->getAction() == 1 and $arena->stat !== 2){
			$custom = $item->getCustomName();
			if($item->getId() == 120 and $custom == "§r§eBack To Hub\n§7 (Click To Leave)"){
				$e->setCancelled();
				$arena->quit($p);
				$p->sendMessage("...");
			}

		if($e->getAction() == 1 and $arena->stat !== 2){
		    $custom = $item->getCustomName();
		    if($item->getId() == 54 and $custom == "§r§eTeams\n§7 (Click To Switch)"){
		        
		    }
			if($item->getId() == 35 and $custom == "§r§7Join:§9§l BLUE§r\n§7 (Click To Enter)"){
				$e->setCancelled();
				if($arena->getTeam($p) == "blue"){
					$p->sendMessage("§l§bBLUE§e TEAM\n§bWelcome to red team make sure to play fair and please don't cross team! Cross teamers will be kick\n§l§6REPORT§7 BUGS\n§bIf there is any issus with the game please contact our developer's\n");
					return true;
				}
				if($arena->setTeam("blue", $p)){
					$p->sendMessage("§6you have joined blue§a!§r§8\n");
				} else {
					$p->sendMessage("§6You have left blue team!\n");
				}
			}
			if($item->getId() == 35 and $custom == "§r§7Join:§c§lRED§r\n§7 (Click To Enter)"){
				$e->setCancelled();
				if($arena->getTeam($p) == "red"){
					$p->sendMessage("§l§eyou have joined §c§lRed§r!");
					return true;
				}
				if($arena->setTeam("red", $p)){
					$p->sendMessage("§l§cRED§e TEAM\n§bWelcome to red team make sure to play fair and please don't cross team!\n§l§6REPORT§7 BUGS\n§bIf there is any issus with the game please contact our developer's\n");
				} else {
					$p->sendMessage("§6You have left red team\n");
			
					return true;
				}
			}
		}
		}
	}
	
	public function onDamage(EntityDamageEvent $e){
		$ent = $e->getEntity();
		if($ent instanceof Player){
			$name = strtolower($ent->getName());
			$arena = $this->getPlugin()->getPlayerArena($ent);
			if(is_null($arena)){
				return true;
			}
			if($arena->stat < 3 or $arena->stat > 3){
				$e->setCancelled();
				if($e->getCause() == 11){
					if($arena->stat > 3){
						$ent->getInventory()->clearAll();
						$arena->respawnPlayer($ent, false);
						return true;
					}
					$level = $ent->getLevel();
					$ent->teleport($level->getSafeSpawn());
					return true;
				}
			}
			if($e->getCause() == 4){
				$e->setCancelled();
				return true;
			}
			if($e->getCause() == 10 or $e->getCause() == 9){
				$e->setCancelled();
				return true;
			}
			if($e->getCause() == 11){
				$e->setCancelled();
				$ent->getInventory()->clearAll();
				$ent->getArmorInventory()->clearAll();
				$arena->respawnPlayer($ent);
				return true;
			}
			$cause = $ent->getLastDamageCause();
			$damage = $e->getFinalDamage();
			if($e instanceof EntityDamageByEntityEvent){
				$p = $e->getDamager();
				if($p instanceof Player){
					if($arena->isTeamMode() && $arena->isTeam($p, $ent)){
						$e->setCancelled();
						return true;
					}
				}
			}
			
			if(($ent->getHealth() - round($damage)) <= 1){
				$e->setCancelled();
				$ent->getInventory()->clearAll();
				$ent->getArmorInventory()->clearAll();
				$arena->respawnPlayer($ent);
				if($e instanceof EntityDamageByEntityEvent){
					$p = $e->getDamager();
					if($p instanceof Player){
						$arena->broadcast("§7" . $p->getNameTag() . "§l§e Has killen§c " . $ent->getNameTag(), 3);
						if($arena->hasHab($p, "matador")){
							$eff = Effect::getEffect(5);
							$eff->setDuration(20*20);
							$eff->setAmplifier(3);
							
							$p->addEffect($eff);
						}
						return true;
					}
				}
				$arena->broadcast("§f" . $ent->getNameTag() . " (a)", 3);
				return true;
			}
			switch($e->getCause()){
				case 1:
				case 2:
				case 4:
				case 11:
				if($cause instanceof EntityDamageByEntityEvent){
					$ev = new EntityDamageByEntityEvent($cause->getDamager(), $ent, 1, $e->getFinalDamage());
					$ent->setLastDamageCause($ev);
				}
				break;
			}
}
}
	
	public function onQuit(PlayerQuitEvent $e){
		$p = $e->getPlayer();
		$arena = $this->getPlugin()->getPlayerArena($p);
		if(!is_null($arena)){
			$arena->broadcast("§f" . $p->getNameTag() . "§l§c has left the match", 3);
			
			$arena->quit($p, false);
		}
	}
	
	public function onData(DataPacketReceiveEvent $e){
		$p = $e->getPlayer();
		$arena = $this->getPlugin()->getPlayerArena($p);
		if(is_null($arena)){
			return true;
		}
		$packet = $e->getPacket();
		$name = strtolower($p->getName());
		switch($packet::NETWORK_ID){
			case 0x29:
			$e->setCancelled();
			$item = $packet->item;
			$p->getInventory()->addItem($item);
			break;
		}
	}
	
   public function onC(PlayerCommandPreprocessEvent $e){
    	$p = $e->getPlayer();
    	$arena = $this->getPlugin()->getPlayerArena($p);
		if(is_null($arena)){
			return true;
		}
    	$cmd = strtolower($e->getMessage());
    	if(substr($cmd, 0, 1) == "/"){
    		if(!$p->hasPermission("bridge.cmd")){
    			$e->setCancelled();
    		}
    		$args = explode(" ", $cmd);
    		if(substr($args[0], 1) == "tb"){
    			if(isset($args[1])){
    				if(strtolower($args[1]) == "leave"){
    					$e->setCancelled();
    					$arena->broadcast("§f" . $p->getNameTag() . "§e Left The Match!", 3);
        				$arena->quit($p);
    					$p->getInventory()->clearAll();
        				$p->getArmorInventory()->clearAll();
    
    					return true;
    				}
    			}
    		} elseif(substr($args[0], 1) == "kill"){
    			$e->setCancelled();
    			$p->sendMessage("  ");
    			return true;
    		}
    		if(!$p->hasPermission("bridge.cmd")){
    			$e->setCancelled();
    			$p->sendMessage("   ");
    		}
    	}
    }
}