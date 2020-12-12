<?php

namespace SP;

use pocketmine\Player;
use pocketmine\Server;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

use pocketmine\scheduler\Task;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;

use jojoe77777\FormAPI;
use jojoe77777\FormAPI\SimpleForm;

class Main extends PluginBase implements Listener {
	
	public function onEnable(){
		$this->saveResource("config.yml");
        $this->saveResource("messages.yml");
		$this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $this->messages = new Config($this->getDataFolder() . "messages.yml", Config::YAML);
		
		$this->getServer()->getLogger()->info("§a[§bSettingPlayer Enable§a]");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
	}
	
	public function onDisable() {
        $this->getServer()->getLogger()->info("§a[§cDisabling SettingPlayer§a]");
    }
	
	public function onJoin(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		$player->setGamemode(Player::SURVIVAL);
		$player->setAllowFlight(false);
		$player->removeAllEffects();
		$player->setHealth(20);
        $player->setFood(20);
		$player->setScale(1);
	}
	
	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool {
        switch($cmd->getName()){                    
            case "setting":
                if($sender instanceof Player){
					$this->onForm($sender);
                }else{
                    $sender->sendMessage("§cUps! Please run this command in GameS");
						return true;
				}
            break;
        }
        return true;
    }
	
	public function onForm(Player $player){
		$form = new SimpleForm(function (Player $player, $data){
		$result = $data;
		if($result === null){
			return true;
			}
			switch($result) {
				
				// Nick Player
				case 0:
					if($player->hasPermission("sett.nickplayer")){
						$this->setNickName($player);
					}else{
						$player->sendMessage("§cYou don't have permission to select this!");
					}
				break;
				
				// Fly Player
                case 1:
					if($player->hasPermission("sett.flyplayer")){
						if(!$player->isCreative()){
							$volume = mt_rand();
							$player->sendMessage($player->getAllowFlight() === false ? $this->messages->getNested("MessageFly.enable") : $this->messages->getNested("MessageFly.disable"));
							$player->setAllowFlight($player->getAllowFlight() === false ? true : false);
							$player->setFlying($player->isFlying() === false ? true : false);
							$player->getLevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_LEVELUP, (int) $volume);
						}else{
							$player->sendMessage("§l§f» §r§cYou already in gamemode creative");
						}
					}else{
						$player->sendMessage("§cYou don't have permission to select this!");
					}
                break;
				
				// Hide Player
				case 2:
					if($player->hasPermission("sett.sizeplayer")){
						if($player->getScale() == 1) {
							$volume = mt_rand();
							$player->setScale(0.5);
							$player->sendMessage($this->messages->getNested("MessageSize.small"));
							$player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_ENDERMAN_TELEPORT, (int) $volume);
						}else{
							$volume = mt_rand();
							$player->setScale(1);
							$player->sendMessage($this->messages->getNested("MessageSize.normal"));
							$player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_ENDERMAN_TELEPORT, (int) $volume);
						}
                    }else{
						$player->sendMessage("§cYou don't have permission to select this!");
					}
				break;
                
				// Gamemode Creative
                case 3:
                    if($player->hasPermission("sett.gmplayer")){
						if($player->isSurvival()){
							$volume = mt_rand();
							$player->setGamemode(Player::CREATIVE);
							$player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_TOTEM, (int) $volume);
							$player->sendMessage($this->messages->getNested("MessageGM.creative"));
						}else{
							$volume = mt_rand();
							$player->setGamemode(Player::SURVIVAL);
							$player->sendMessage($this->messages->getNested("MessageGM.survival"));
							$player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_TOTEM, (int) $volume);
						}
					}else{
						$player->sendMessage("§cYou don't have permission to select this!");
					}
                break;
				
				case 4:
                break;
            }
        });
        $form->setTitle("§lSetting Player");
        $form->setContent(" You can setting mode here:");
		// This is add t button 1.
		if($this->config->getNested("Form.Button.nick") == true){
			if($player->hasPermission("sett.nickplayer")){
				$name = $player->getDisplayName();
				$form->addButton("§0Custom Name » §c[".$name."]\n§eClick Change Name",0,"textures/ui/mashup_hangar",0);
			}else{
				$form->addButton("§0Fitur Locked",0,"textures/ui/deop",0);
			}
		}
		
		// This is add t button 2.
		if($this->config->getNested("Form.Button.flying") == true){
			if($player->hasPermission("sett.flyplayer")){
				if($player->getAllowFlight() == true){
					$form->addButton("§0Flying » §6[ON]\n§eClick to Trun Off",0,"textures/items/feather",1);
				}else{
					$form->addButton("§0Flying » §c[OFF]\n§eClick to Trun On",0,"textures/items/feather",1);
				}
			}else{
				$form->addButton("§0Fitur Locked",0,"textures/ui/deop",1);
			}
		}
		
		// This is add t button 3.
		if($this->config->getNested("Form.Button.sizeplayer") == true){
			if($player->hasPermission("sett.sizeplayer")){
				if($player->getScale() == 1){
					$form->addButton("§0Size Player » §c[NORMAL]\n§eClick to Mode Small",0,"textures/ui/icon_multiplayer",2);
				}else{
					$form->addButton("§0Size Player » §6[SMALL]\n§eClick to Mode Normal",0,"textures/ui/icon_multiplayer",2);
				}
			}else{
				$form->addButton("§0Fitur Locked",0,"textures/ui/deop",2);
			}
		}
		
		// This is add t button 4.
		if($this->config->getNested("Form.Button.gamemode") == true){
			if($player->hasPermission("sett.gmplayer")){
				if($player->isCreative()){
					$form->addButton("§0Gamemode » §6[CREATIVE]\n§eClick Change Survival",0,"textures/ui/op",3);
				}else{
				$form->addButton("§0Gamemode » §c[SURVIVAL]\n§eClick Change Creative",0,"textures/ui/op",3);
				}
			}else{
				$form->addButton("§0Fitur Locked",0,"textures/ui/deop",3);
			}
		}
		
		// This is add t button 5.
		$form->addButton("§cClose",0,"textures/ui/Caution",4);
		$form->sendToPlayer($player);
	}
	
	public function setNickName(Player $player){
		$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createCustomForm(function (Player $player, array $data = null) {
			if($data === null){
				return true;
			}
			// First input will retrun $data[1]
			$volume = mt_rand();
			$player->setDisplayName($data[1]);
			$player->sendMessage(str_replace(["{player}", "{custom_name}"], [$player->getName(), $data[1]], $this->messages->getNested("MessageCustom.success")));
			$player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_ANVIL_USE, (int) $volume);
		});
		$form->setTitle("§lCustom Name");
        $form->addLabel("You can change your name as you wish!");
		$form->addInput("Input your custom display name here:", "Enter anyware here");
		$form->sendToPlayer($player);
	}
}
