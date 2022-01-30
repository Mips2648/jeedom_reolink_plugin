<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';
require_once dirname(__FILE__).'/../../3rdparty/reolinkapi.class.php';

class reolink extends eqLogic {

    /************* Static methods ************/
    public static function getReolinkConnection($id) {
      $camera = reolink::byId($id, 'reolink');
      $adresseIP = $camera->getConfiguration('adresseip');
      $port = $camera->getConfiguration('port');
      $username = $camera->getConfiguration('login');
      $password = $camera->getConfiguration('password');
      $cnxtype = $camera->getConfiguration('cnxtype');

      if (!empty($adresseIP) && !empty($username) && !empty($password))
      {
        $cnxinfo = array("adresseIP" => $adresseIP, "port" => $port, "username" => $username, "password" => $password, "cnxtype" => $cnxtype);
        $camcnx = new reolinkAPI($cnxinfo);
        return $camcnx;
      } else {
        log::add('reolink', 'warning', "Information de connexion manquantes : connexion à la caméra impossible");
        return false;
      }
  	}

    public static function TryConnect($id) {
      $reolinkConn = reolink::getReolinkConnection($id);
      if ($reolinkConn->$is_loggedin == true) {
        log::add('reolink', 'info', 'Connection à la caméra réussie');
        return true;
      } else {
        log::add('reolink', 'error', 'Connection à la caméra NOK');
        return false;
      }
    }

    public static function GetCamNFO($id) {
      log::add('reolink', 'debug', 'Obtention des informations de la caméra');
      $camera = reolink::byId($id, 'reolink');

      // Devices Info
      $reolinkConn = reolink::getReolinkConnection($id);
      $deviceInfo = $reolinkConn->SendCMD('GetDevInfo', array());
      if (!$deviceInfo) {
        return false;
      }

      foreach ($deviceInfo  as $key => $value) {
        $camera->setConfiguration($key, $value);

        if ($key == "model") {
          // Download CAM img ICON
          $modelURL = urlencode($value);
          $iconurl = "https://cdn.reolink.com/wp-content/assets/app/model-images/$modelURL/light_off.png";
          $camera->setConfiguration("camicon", $iconurl);

          $dir = realpath(dirname(__FILE__) . '/../../desktop');

          if (!file_exists($dir.'/img')) {
              mkdir($dir.'/img', 0775, true);
              log::add('reolink', 'debug', 'Création du répertoire visuel caméra = '.$dir.'/img');
          }

          $fileToWrite = $dir.'/img/camera'.$id.'.png';

          log::add('reolink', 'debug', 'Enregistrement du visuel de la caméra '.$value.' depuis serveur Reolink ('.$iconurl. ' => '.$fileToWrite.')');

          $ch = curl_init ($iconurl);
          curl_setopt($ch, CURLOPT_HEADER, false);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_BINARYTRANSFER,true);
          curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
          curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
          $rawdata=curl_exec($ch);

          $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
          $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
          $header = substr($response, 0, $header_size);

          if ($httpcode == 200) {
            log::add('reolink', 'debug', 'HTTP code 200 OK');
          } else {
            log::add('reolink', 'error', 'HTTP code '.$httpcode.' NOK '.curl_error($ch). ' Entête : '.$header);
            return false;
          }
          curl_close ($ch);
          $fp = fopen($fileToWrite,'w');
          fwrite($fp, $rawdata);
          fclose($fp);
          log::add('reolink', 'debug', 'Ecriture OK');
        }
      }
      log::add('reolink', 'debug', 'GetDeviceInfo ajout de '.count($deviceInfo). ' items');


      if (count($deviceInfo) > 1) {
        $camera->Save();
        return true;
      } else {
        return false;
      }
    }

    public static function GetCamAbility($id) {
      log::add('reolink', 'debug', 'Interrogation de la caméra sur ses capacités hardware/software...');
      $reolinkConn = reolink::getReolinkConnection($id);

      // Devices Ability
      if (is_object($reolinkConn)) {
        $deviceAbility = $reolinkConn->SendCMD('GetAbility', array("User" => array("userName" => "admin")));
        log::add('reolink', 'debug', 'GetAbility à récupérer : '.count($deviceAbility). ' items');

        if (count($deviceAbility) > 1) {
          return $deviceAbility;
        } else {
          return false;
        }
      } else {
        return false;
      }

    }

    public static function updatePTZpreset($id, $data) {
      $camera=reolink::byId($id, 'reolink');
      $cmd = $camera->getCmd(null, 'SetPtzByPreset');
      $ptzlist = NULL;
      if (is_object($cmd) && is_array($data)) {
        foreach ($data  as $key => $value) {
          if ($value['enable'] == 1) {
              log::add('reolink', 'debug',  'Ajout du PTZ preset = '.$value['id'].'|'.$value['name']);
              $ptzlist .=  $value['id'].'|'.$value['name'].";";
          }
        }
        $ptzlist = substr($ptzlist, 0, -1);
        $cmd->setConfiguration('listValue', $ptzlist);
        $cmd->save();
        $cmd->getEqLogic()->refreshWidget();
        return true;
      } else {
        return false;
      }
    }

    public static function setFTP($id) {
      $camcmd = reolink::byId($id, 'reolink');
      $cam = reolink::getReolinkConnection($id);

      $param = array ("Ftp" => array(
                      	"anonymous" => intval($camcmd->getConfiguration("ftp_anonymous")),
                      	"interval" => intval($camcmd->getConfiguration("ftp_interval")),
                      	"maxSize" => intval($camcmd->getConfiguration("ftp_maxfilesize")),
                      	"mode" => intval($camcmd->getConfiguration("ftp_mode")),
                      	"ssl" => intval($camcmd->getConfiguration("ftp_usessltls")),
                      	"password" => $camcmd->getConfiguration("ftp_passwd"),
                      	"port" => intval($camcmd->getConfiguration("ftp_port")),
                      	"remoteDir" => $camcmd->getConfiguration("ftp_path"),
                      	"server" => $camcmd->getConfiguration("ftp_server"),
                      	"userName" => $camcmd->getConfiguration("ftp_account")
                      	)
                      );
      $camresp = $cam->SendCMD(reolinkAPI::CAM_SET_FTP, $param);
      return $camresp;
    }

    public static function setEmail($id) {
      $camcmd = reolink::byId($id, 'reolink');
      $cam = reolink::getReolinkConnection($id);

      $param = array ("Email" => array(
                        "addr1" => $camcmd->getConfiguration("mailto_addr1"),
                        "addr2" => $camcmd->getConfiguration("mailto_addr2"),
                        "addr3" => $camcmd->getConfiguration("mailto_addr3"),
                        "attachmentType" => $camcmd->getConfiguration("smtp_attachement"),
                        "interval" => $camcmd->getConfiguration("smtp_interval"),
                        "nickName" => $camcmd->getConfiguration("mailfrom_name"),
                        "password" => $camcmd->getConfiguration("smtp_password"),
                        "smtpPort" => intval($camcmd->getConfiguration("smtp_port")),
                        "smtpServer" => $camcmd->getConfiguration("smtp_server"),
                        "ssl" => intval($camcmd->getConfiguration("smtp_usessltls")),
                        "userName" => $camcmd->getConfiguration("smtp_login")
                        )
                      );
      $camresp = $cam->SendCMD(reolinkAPI::CAM_SET_EMAIL, $param);
      return $camresp;
    }


    public static function refreshNFO($id) {
      $camcmd = reolink::byId($id, 'reolink');
      $camcnx = reolink::getReolinkConnection($id);


      $res = $camcnx->SendCMD(reolinkAPI::CAM_GET_PUSH, array());
      $camcmd->checkAndUpdateCmd('SetPushState', $res['schedule']['enable']);

      $res = $camcnx->SendCMD(reolinkAPI::CAM_GET_FTP, array());
        $camcmd->checkAndUpdateCmd('SetFTPState', $res['schedule']['enable']);
        $camcmd->setConfiguration("ftp_state", $res['schedule']['enable']);
        $camcmd->setConfiguration("ftp_server", $res['server']);
        $camcmd->setConfiguration("ftp_anonymous", $res['anonymous']);
        $camcmd->setConfiguration("ftp_account", $res['userName']);
        $camcmd->setConfiguration("ftp_passwd", $res['password']);
        $camcmd->setConfiguration("ftp_path", $res['remoteDir']);
        $camcmd->setConfiguration("ftp_port", $res['port']);
        $camcmd->setConfiguration("ftp_mode", $res['mode']);
        $camcmd->setConfiguration("ftp_maxfilesize", $res['maxSize']);
        $camcmd->setConfiguration("ftp_filetosend", $res['streamType']);

      $res = $camcnx->SendCMD(reolinkAPI::CAM_GET_EMAIL, array());
        $camcmd->checkAndUpdateCmd('SetEmailState', $res['schedule']['enable']);
        $camcmd->setConfiguration("smtp_state", $res['schedule']['enable']);
        $camcmd->setConfiguration("smtp_server", $res['smtpServer']);
        $camcmd->setConfiguration("smtp_port", $res['smtpPort']);
        $camcmd->setConfiguration("mailto_addr1", $res['addr1']);
        $camcmd->setConfiguration("mailto_addr2", $res['addr2']);
        $camcmd->setConfiguration("mailto_addr3", $res['addr3']);
        $camcmd->setConfiguration("smtp_password", $res['password']);
        $camcmd->setConfiguration("smtp_login", $res['userName']);
        $camcmd->setConfiguration("smtp_usessltls", $res['ssl']);
        $camcmd->setConfiguration("smtp_interval", $res['interval']);
        $camcmd->setConfiguration("smtp_attachement", $res['attachment']);

      $res = $camcnx->SendCMD(reolinkAPI::CAM_GET_ENC, array("channel" => 0));
        $camcmd->checkAndUpdateCmd('SetMicrophoneState', $res['audio']);
        $camcmd->checkAndUpdateCmd('SetResolutionst1State', $res['mainStream']['size']);
        $camcmd->checkAndUpdateCmd('SetFPSst1State', $res['mainStream']['size']);
        $camcmd->checkAndUpdateCmd('SetBitratest1State', $res['mainStream']['bitRate']);
        $camcmd->checkAndUpdateCmd('SetResolutionst2State', $res['subStream']['size']);
        $camcmd->checkAndUpdateCmd('SetFPSst2State', $res['subStream']['size']);
        $camcmd->checkAndUpdateCmd('SetBitratest2State', $res['subStream']['size']);

      $res = $camcnx->SendCMD(reolinkAPI::CAM_GET_REC, array("channel" => 0));
        $camcmd->checkAndUpdateCmd('SetRecordState', $res['schedule']['enable']);
        $camcmd->checkAndUpdateCmd('SetPreRecordState', $res['preRec']);
        $camcmd->checkAndUpdateCmd('SetOverwriteState', $res['overwrite']);
        $camcmd->checkAndUpdateCmd('SetPostRecordState', $res['postRec']);


      $res = $camcnx->SendCMD(reolinkAPI::CAM_GET_AUDIOALARM, array());
      $camcmd->checkAndUpdateCmd('SetAudioAlarmState', $res['schedule']['enable']);

      $res = $camcnx->SendCMD(reolinkAPI::CAM_GET_IRLIGHTS, array());
      $camcmd->checkAndUpdateCmd('SetIrLightsState', $res['state']);

      $res = $camcnx->SendCMD(reolinkAPI::CAM_GET_POWERLED, array());
      ($res['state'] == 0) ? $value = "Off" : $value = "On";
      $camcmd->checkAndUpdateCmd('SetPowerLedState', $value);

      $res = $camcnx->SendCMD(reolinkAPI::CAM_GET_AUTOFOCUS, array("channel" => 0));
      $camcmd->checkAndUpdateCmd('SetAutoFocusState', $res['disable']);

      $res = $camcnx->SendCMD(reolinkAPI::CAM_GET_MASK, array("channel" => 0));
      $camcmd->checkAndUpdateCmd('SetMaskState', $res['enable']);

      $res = $camcnx->SendCMD(reolinkAPI::CAM_GET_AUTOMAINT, array("channel" => 0));
      $camcmd->checkAndUpdateCmd('SetAutoMaintState', $res['enable']);

      $res = $camcnx->SendCMD(reolinkAPI::CAM_GET_ISP, array("channel" => 0));
        $camcmd->checkAndUpdateCmd('SetRotationState', $res['rotation']);
        $camcmd->checkAndUpdateCmd('SetMirroringState', $res['mirroring']);
        $camcmd->checkAndUpdateCmd('SetAntiFlickerState', $res['antiFlicker']);
        $camcmd->checkAndUpdateCmd('SetBackLightState', $res['backLight']);
        $camcmd->checkAndUpdateCmd('SetBlcState', $res['blc']);
        $camcmd->checkAndUpdateCmd('SetBlueGainState', $res['blueGain']); // ???
        $camcmd->checkAndUpdateCmd('SetDayNightState', $res['dayNight']);
        $camcmd->checkAndUpdateCmd('SetDrcState', $res['drc']);
        $camcmd->checkAndUpdateCmd('SetNr3dState', $res['nr3d']);
        $camcmd->checkAndUpdateCmd('SetRedGainState', $res['redGain']); // ???
        $camcmd->checkAndUpdateCmd('SetWhiteBalanceState', $res['whiteBalance']); // ???

      $res = $camcnx->SendCMD(reolinkAPI::CAM_GET_OSD, array("channel" => 0));
        $camcmd->checkAndUpdateCmd('SetWatermarkState', $res['watermark']);
        $camcmd->checkAndUpdateCmd('SetOsdTimeState', $res['osdTime']['enable']);
        $camcmd->checkAndUpdateCmd('SetOsdChannelState', $res['osdChannel']['enable']);
        $camcmd->checkAndUpdateCmd('SetPosOsdTimeState', $res['osdTime']['pos']);
        $camcmd->checkAndUpdateCmd('SetPosOsdChannelState', $res['osdChannel']['pos']);

      $res = $camcnx->SendCMD(reolinkAPI::CAM_GET_IMAGE, array("channel" => 0));
        $camcmd->checkAndUpdateCmd('SetBrightState', $res['bright']);
        $camcmd->checkAndUpdateCmd('SetContrastState', $res['contrast']);
        $camcmd->checkAndUpdateCmd('SetSaturationState', $res['saturation']);
        $camcmd->checkAndUpdateCmd('SetHueState', $res['hue']);
        $camcmd->checkAndUpdateCmd('SetSharpenState', $res['sharpen']);

      $res = $camcnx->SendCMD(reolinkAPI::CAM_GET_HDDINFO, array());
        if ($res['format'] == 1 && $res['mount'] == 1) {
          $camcmd->checkAndUpdateCmd('driveAvailable', 1);
        } else {
          $camcmd->checkAndUpdateCmd('driveAvailable', 0);
        }
        if (is_numeric($res['size']) && is_numeric($res['capacity'])) {
          $percoccupancy = round(($res['size'] * 100) / $res['capacity'], 0, PHP_ROUND_HALF_DOWN);
          $camcmd->checkAndUpdateCmd('driveSpaceAvailable', $percoccupancy);
        }
        if ($res['storageType'] == 1) {
          $camcmd->checkAndUpdateCmd('driveType', "HDD");
        } elseif ($res['storageType'] == 2) {
          $camcmd->checkAndUpdateCmd('driveType', "Sdcard");
        }

        $camcmd->save();
    }

  /*
   * Permet de définir les possibilités de personnalisation du widget (en cas d'utilisation de la fonction 'toHtml' par exemple)
   * Tableau multidimensionnel - exemple: array('custom' => true, 'custom::layout' => false)
	public static $_widgetPossibility = array();
   */

    /*     * ***********************Methode static*************************** */

      public static function cron() {
        $eqLogics = ($_eqlogic_id !== null) ? array(eqLogic::byId($_eqlogic_id)) : eqLogic::byType('reolink', true);
        foreach ($eqLogics as $camera) {
          $autorefresh = $camera->getConfiguration('autorefresh','*/15 * * * *');
          if ($autorefresh != '') {
            try {
              $c = new Cron\CronExpression(checkAndFixCron($autorefresh), new Cron\FieldFactory);
              if ($c->isDue()) {
                log::add('reolink', 'debug', '#### CRON refresh '.$camera->getHumanName());

                $camera->refreshNFO($camera->getId());
              }
            } catch (Exception $exc) {
              log::add('reolink', 'error', __('Expression cron non valide pour ', __FILE__) . $camera->getHumanName() . ' : ' . $autorefresh);
            }
          }
        }
      }


    /*
     * Fonction exécutée automatiquement toutes les 5 minutes par Jeedom
      public static function cron5() {
      }
     */

    /*
     * Fonction exécutée automatiquement toutes les 10 minutes par Jeedom
      public static function cron10() {
      }
     */

    /*
     * Fonction exécutée automatiquement toutes les 15 minutes par Jeedom
      public static function cron15() {
      }
     */

    /*
     * Fonction exécutée automatiquement toutes les 30 minutes par Jeedom
      public static function cron30() {
      }
     */

    /*
     * Fonction exécutée automatiquement toutes les heures par Jeedom
      public static function cronHourly() {
      }
     */

    /*
     * Fonction exécutée automatiquement tous les jours par Jeedom
      public static function cronDaily() {
      }
     */



    /*     * *********************Méthodes d'instance************************* */

 // Fonction exécutée automatiquement avant la création de l'équipement
    public function preInsert() {

    }

 // Fonction exécutée automatiquement après la création de l'équipement
    public function postInsert() {


    }

 // Fonction exécutée automatiquement avant la mise à jour de l'équipement
    public function preUpdate() {
      if ($this->getConfiguration('adresseip') == NULL) {
        throw new Exception(__('L\'adresse IP est obligatoire', __FILE__));
      }
      if (!filter_var($this->getConfiguration('adresseip'), FILTER_VALIDATE_IP)) {
        throw new Exception("Adresse IP de la caméra invalide " . $this->ip);
      }
      if ($this->getConfiguration('login') == NULL) {
        throw new Exception(__('Le champ login est obligatoire', __FILE__));
      }
      if ($this->getConfiguration('password') == NULL) {
        throw new Exception(__('Le mot de passe ne peut pas être vide', __FILE__));
      }
      // Champs OK
    }

 // Fonction exécutée automatiquement après la mise à jour de l'équipement
    public function postUpdate() {

    }

 // Fonction exécutée automatiquement avant la sauvegarde (création ou mise à jour) de l'équipement
    public function preSave() {

    }

 // Fonction exécutée automatiquement après la sauvegarde (création ou mise à jour) de l'équipement
    public function postSave() {

    }

 // Fonction exécutée automatiquement avant la suppression de l'équipement
    public function preRemove() {

    }

 // Fonction exécutée automatiquement après la suppression de l'équipement
    public function postRemove() {

    }


    public function loadCmdFromConf($id) {
      $devAbilityReturn = reolink::GetCamAbility($id);

      if (!$devAbilityReturn) {
        log::add('reolink', 'debug', 'Erreur lors de l\'obtention des capacités hardware/software de la caméra');
        return false;
      }

      log::add('reolink', 'debug', 'Chargement des commandes depuis le fichiers de config : '.dirname(__FILE__) . '/../config/reolinkapicmd.json');
      $content = file_get_contents(dirname(__FILE__) . '/../config/reolinkapicmd.json');


      if (!is_json($content)) {
        log::add('reolink', 'error', 'Format du fichier de configuration n\'est pas du JSON valide !');
        return false;
      }
      $device = json_decode($content, true);
      if (!is_array($device) || !isset($device['commands'])) {
        log::add('reolink', 'error', 'Pas de configuration valide trouvé dans le fichier');
        return false;
      }
      log::add('reolink', 'info', 'Nombre de commandes dans le fichier de configuration : '.count($device['commands']));
      $cmd_order = 0;

      foreach ($device['commands'] as $command) {
          // Chack cam ability
          $cmd = null;
          foreach ($this->getCmd() as $liste_cmd) {
            if ((isset($command['logicalId']) && $liste_cmd->getLogicalId() == $command['logicalId'])
            || (isset($command['name']) && $liste_cmd->getName() == $command['name'])) {
              $cmd = $liste_cmd;
              break;
            }
          }

          if ($cmd == null || !is_object($cmd))
          {
            // Check cam ability
            $ability = false;
            $abilityfound = false;
            // Global Ability
            foreach ($devAbilityReturn as $abilityName => $abilityParam) {

                if ($command['abilityneed'] == "none") {
                  $ability = true;
                  break;
                }

                if ($command['abilityneed'] == $abilityName) {
                  $abilityfound = true;
                  if ($abilityParam['permit'] != 0) {
                    // Function available for this model ADD
                    log::add('reolink', 'info', '=> Capacité hardware/software OK pour : '.$command['name']);
                    $ability = true;
                    break;
                  } else {
                    // Function NOT available for this model DO NOT ADD
                    log::add('reolink', 'debug', '=> Ignorer, capacité hardware/software NOK pour : '.$command['name']);
                    break;
                  }
                  break;
                }
              }
            // Channel Ability
            if (!$ability) {
              foreach ($devAbilityReturn['abilityChn'][0] as $abilityName => $abilityParam) {
                  if ($command['abilityneed'] == $abilityName) {
                    $abilityfound = true;
                    if ($abilityParam['permit'] != 0) {
                      // Function available for this model ADD
                      log::add('reolink', 'info', '=> Capacité hardware/software OK pour : '.$command['name']);
                      $ability = true;
                      break;
                    } else {
                      // Function NOT available for this model DO NOT ADD
                      log::add('reolink', 'debug', '=> Ignorer, capacité hardware/software NOK pour : '.$command['name']);
                      break;
                    }
                    break;
                  }
                }
            }

            if (!$abilityfound && !$ability) {
              log::add('reolink', 'error', 'Aucun match de capacité '.$command['abilityneed'].' pour la CMD : '.$command['name']);
            }

            if ($ability) {
              log::add('reolink', 'info', '-> Ajout de : '.$command['name']);
              $cmd = new reolinkCmd();
              $cmd->setOrder($cmd_order);
              $cmd->setEqLogic_id($this->getId());
              utils::a2o($cmd, $command);
              $cmd->save();
              if ($cmd->getConfiguration('valueFrom') != "") {
                $valueLink = $cmd->getConfiguration('valueFrom');
                $camera = reolink::byId($id, 'reolink');
                $cmdlogic = reolinkCmd::byEqLogicIdAndLogicalId($camera->getId(), $valueLink);
                if (is_object($cmdlogic)) {
                  $cmd->setValue($cmdlogic->getId());
                  $cmd->save();
                  log::add('reolink', 'debug', '--> Valeur lier depuis : '.$valueLink." (".$cmdlogic->getId().")");
                } else {
                  log::add('reolink', 'warning', 'X--> Liaison impossible objet introuvable : '.$valueLink);
                }
              }
              $cmd_order++;
            }
          } else {
            log::add('reolink', 'debug', 'Commande déjà présente : '.$command['name']);
          }
      }
      return $cmd_order;
    }

    /*
     * Non obligatoire : permet de modifier l'affichage du widget (également utilisable par les commandes)
      public function toHtml($_version = 'dashboard') {

      }
     */

    /*
     * Non obligatoire : permet de déclencher une action après modification de variable de configuration
    public static function postConfig_<Variable>() {
    }
     */

    /*
     * Non obligatoire : permet de déclencher une action avant modification de variable de configuration
    public static function preConfig_<Variable>() {
    }
     */

    /*     * **********************Getteur Setteur*************************** */
}

class reolinkCmd extends cmd {
    /*     * *************************Attributs****************************** */

    /*
      public static $_widgetPossibility = array();
    */

    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */

    /*
     * Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
      public function dontRemoveCmd() {
      return true;
      }
     */

  // Exécution d'une commande
     public function execute($_options = array()) {
      log::add('reolink', 'debug', 'Action demandé : '.$this->getLogicalId());
      $EqId = $this->getEqLogic_id();
      $cam = reolink::getReolinkConnection($EqId);

      $channel = $this->getConfiguration('channel');
      if ($channel == NULL) {
        $channel = 0;
      }

       switch ($this->getLogicalId()) {
          case 'refresh':
              reolink::refreshNFO($EqId);
              break;
          case 'GetPtzPreset':
              $data = $cam->SendCMD(reolinkAPI::CAM_GET_PTZPRESET, array("channel" => $channel));
              reolink::updatePTZpreset($EqId, $data);
              break;
          case 'SetSpeed':
              $this->setConfiguration('speedvalue', $_options['slider']);
              break;
          default:
            // Speed NFO
            $cmd = reolinkCmd::byEqLogicIdAndLogicalId($EqId, "SetSpeed");
            if (is_object($cmd)) {
              $speed = $cmd->getConfiguration('speedvalue');
            } else {
              $speed = 32;
            }


            $actionAPI = $this->getConfiguration('actionapi');
            $linkedvalue = $this->getConfiguration('valueFrom');
            if ($actionAPI != NULL) {
              $payload = str_replace('\\', '', $this->getConfiguration('payload'));
              $payload = str_replace('#OPTSELECTEDINT#', intval($_options['select']), $payload);
              $payload = str_replace('#OPTSELECTEDSTR#', '"'.$_options['select'].'"', $payload);
              $payload = str_replace('#OPTSLIDER#', intval($_options['slider']), $payload);
              $payload = str_replace('#CHANNEL#', 0, $payload);
              $payload = str_replace('#SPEED#', $speed, $payload);

              log::add('reolink', 'debug', 'Payload avec paramètre utilisateur demandé = '.$payload);

              $camresp = $cam->SendCMD($actionAPI, json_decode($payload, true));

              // Check return and update CMD State
              if ($camresp == 200) {
                // CMD OK cam return code 200
                if ($linkedvalue != NULL) {
                  // UPDATE VALUE
                }
              } else {
                throw new Exception(__('Echec d\'execution de la commande (consultez le log pour plus de détails)', __FILE__));
              }

            }
        }
     }

    /*     * **********************Getteur Setteur*************************** */
}
