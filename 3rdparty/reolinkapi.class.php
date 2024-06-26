<?php

class reolinkAPI {
  private $ip;
  private $port;
  private $token;
  private $tokenexp;
  private $tagtoken;
  private $user;
  private $password;
  private $cnxtype;
  private $authmethod;

  public $is_loggedin;

  const CAM_GET_ABILITY = 'GetAbility';
  const CAM_GET_DEVINFO = 'GetDevInfo';
  const CAM_GET_DEVNAME = 'GetDevName';
  const CAM_SET_DEVNAME = 'SetDevName';
  const CAM_GET_TIME = 'GetTime';
  const CAM_SET_TIME = 'SetTime';
  const CAM_GET_AUTOMAINT = 'GetAutoMaint';
  const CAM_SET_AUTOMAINT = 'SetAutoMaint';
  const CAM_GET_HDDINFO = 'GetHddInfo';
  const CAM_FORMAT = 'Format';
  const CAM_UPGRADE = 'Upgrade';
  const CAM_RESTORE = 'Restore';
  const CAM_REBOOT = 'Reboot';
  const CAM_GET_PERFORMANCE = 'GetPerformance';
  const CAM_UPGRADEPREPARE = 'UpgradePrepare';
  const CAM_GET_AUTOUPGRADE = 'GetAutoUpgrade';
  const CAM_SET_AUTOUPGRADE = 'SetAutoUpgrade';
  const CAM_CHECKFIRMWARE = 'CheckFirmware';
  const CAM_UPGRADEONLINE = 'UpgradeOnline';
  const CAM_UPGRADESTATUS = 'UpgradeStatus';
  const CAM_GET_CHANNELSTATUS = 'Getchannelstatus';
  const CAM_LOGIN = 'Login';
  const CAM_LOGOUT = 'Logout';
  const CAM_GET_USER = 'GetUser';
  const CAM_ADDUSER = 'AddUser';
  const CAM_DELUSER = 'DelUser';
  const CAM_MODIFYUSER = 'ModifyUser';
  const CAM_GET_ONLINE = 'GetOnline';
  const CAM_DISCONNECT = 'Disconnect';
  const CAM_GET_LOCALLINK = 'GetLocalLink';
  const CAM_SET_LOCALLINK = 'SetLocalLink';
  const CAM_GET_DDNS = 'GetDdns';
  const CAM_SET_DDNS = 'SetDdns';
  const CAM_GET_EMAIL = 'GetEmail';
  const CAM_SET_EMAIL = 'SetEmail';
  const CAM_GET_EMAILV20 = 'GetEmailV20';
  const CAM_SET_EMAILV20 = 'SetEmailV20';
  const CAM_TESTEMAIL = 'TestEmail';
  const CAM_GET_FTP = 'GetFtp';
  const CAM_SET_FTP = 'SetFtp';
  const CAM_GET_FTPV20 = 'GetFtpV20';
  const CAM_SET_FTPV20 = 'SetFtpV20';
  const CAM_TESTFTP = 'TestFtp';
  const CAM_GET_NTP = 'GetNtp';
  const CAM_SET_NTP = 'SetNtp';
  const CAM_GET_NETPORT = 'GetNetPort';
  const CAM_SET_NETPORT = 'SetNetPort';
  const CAM_GET_UPNP = 'GetUpnp';
  const CAM_SET_UPNP = 'SetUpnp';
  const CAM_GET_WIFI = 'GetWifi';
  const CAM_SET_WIFI = 'SetWifi';
  const CAM_TESTWIFI = 'TestWifi';
  const CAM_SCANWIFI = 'ScanWifi';
  const CAM_GET_WIFISIGNAL = 'GetWifiSignal';
  const CAM_GET_PUSH = 'GetPush';
  const CAM_SET_PUSH = 'SetPush';
  const CAM_GET_PUSHV20 = 'GetPushV20';
  const CAM_SET_PUSHV20 = 'SetPushV20';
  const CAM_GET_PUSHCFG = 'GetPushCfg';
  const CAM_SET_PUSHCFG = 'SetPushCfg';
  const CAM_GET_P2P = 'GetP2p';
  const CAM_SET_P2P = 'SetP2p';
  const CAM_GET_CERTIFICATEINFO = 'GetCertificateInfo';
  const CAM_CERTIFICATECLEAR = 'CertificateClear';
  const CAM_GET_RTSPURL = 'GetRtspUrl';
  const CAM_GET_NORM = 'GetNorm';
  const CAM_SET_NORM = 'SetNorm';
  const CAM_GET_IMAGE = 'GetImage';
  const CAM_SET_IMAGE = 'SetImage';
  const CAM_GET_OSD = 'GetOsd';
  const CAM_SET_OSD = 'SetOsd';
  const CAM_GET_ISP = 'GetIsp';
  const CAM_SET_ISP = 'SetIsp';
  const CAM_GET_MASK = 'GetMask';
  const CAM_SET_MASK = 'SetMask';
  const CAM_PREVIEW = 'Preview';
  const CAM_GET_CROP = 'GetCrop';
  const CAM_SET_CROP = 'SetCrop';
  const CAM_GET_ENC = 'GetEnc';
  const CAM_SET_ENC = 'SetEnc';
  const CAM_GET_REC = 'GetRec';
  const CAM_SET_REC = 'SetRec';
  const CAM_GET_RECV20 = 'GetRecV20';
  const CAM_SET_RECV20 = 'SetRecV20';
  const CAM_SEARCH = 'Search';
  const CAM_DOWNLOAD = 'Download';
  const CAM_SNAP = 'Snap';
  const CAM_PLAYBACK = 'Playback';
  const CAM_NVRDOWNLOAD = 'NvrDownload';
  const CAM_GET_PTZPRESET = 'GetPtzPreset';
  const CAM_SET_PTZPRESET = 'SetPtzPreset';
  const CAM_GET_PTZPATROL = 'GetPtzPatrol';
  const CAM_SET_PTZPATROL = 'SetPtzPatrol';
  const CAM_PTZCTRL = 'PtzCtrl';
  const CAM_GET_PTZSERIAL = 'GetPtzSerial';
  const CAM_SET_PTZSERIAL = 'SetPtzSerial';
  const CAM_GET_PTZTATTERN = 'GetPtzTattern';
  const CAM_SET_PTZTATTERN = 'SetPtzTattern';
  const CAM_GET_AUTOFOCUS = 'GetAutoFocus';
  const CAM_SET_AUTOFOCUS = 'SetAutoFocus';
  const CAM_GET_ZOOMFOCUS = 'GetZoomFocus';
  const CAM_STARTZOOMFOCUS = 'StartZoomFocus';
  const CAM_GET_PTZGUARD = 'GetPtzGuard';
  const CAM_SET_PTZGUARD = 'SetPtzGuard';
  const CAM_GET_PTZCHECKSTATE = 'GetPtzCheckState';
  const CAM_PTZCHECK = 'PtzCheck';
  const CAM_GET_ALARM = 'GetAlarm';
  const CAM_SET_ALARM = 'SetAlarm';
  const CAM_GET_MDALARM = 'GetMdAlarm';
  const CAM_SET_MDALARM = 'SetMdAlarm';
  const CAM_GET_MDSTATE = 'GetMdState';
  const CAM_GET_AUDIOALARM = 'GetAudioAlarm';
  const CAM_SET_AUDIOALARM = 'SetAudioAlarm';
  const CAM_GET_AUDIOALARMV20 = 'GetAudioAlarmV20';
  const CAM_SET_AUDIOALARMV20 = 'SetAudioAlarmV20';
  const CAM_GET_BUZZERALARMV20 = 'GetBuzzerAlarmV20';
  const CAM_SET_BUZZERALARMV20 = 'SetBuzzerAlarmV20';
  const CAM_AUDIOALARMPLAY = 'AudioAlarmPlay';
  const CAM_GET_AUDIOCFG = 'GetAudioCfg';
  const CAM_SET_AUDIOCFG = 'SetAudioCfg';
  const CAM_GET_IRLIGHTS = 'GetIrLights';
  const CAM_SET_IRLIGHTS = 'SetIrLights';
  const CAM_GET_POWERLED = 'GetPowerLed';
  const CAM_SET_POWERLED = 'SetPowerLed';
  const CAM_GET_WHITELED = 'GetWhiteLed';
  const CAM_SET_WHITELED = 'SetWhiteLed';
  const CAM_GET_AIALARM = 'GetAiAlarm';
  const CAM_SET_AIALARM = 'SetAiAlarm';
  const CAM_SET_ALARMAREA = 'SetAlarmArea';
  const CAM_GET_AICFG = 'GetAiCfg';
  const CAM_SET_AICFG = 'SetAiCfg';
  const CAM_GET_AISTATE = 'GetAiState';
  const CAM_GET_SYSCFG = 'GetSysCfg';
  const CAM_SET_SYSCFG = 'SetSysCfg';


  public function __construct(array $cnxinfo) {
    log::add('reolink', 'debug', str_repeat("~", 60));
    $this->is_loggedin = false;
    $this->ip = trim($cnxinfo['adresseIP']);

    if (strtolower($cnxinfo['cnxtype'] == "")) {
      $this->cnxtype = "http";
    } else {
      $this->cnxtype = strtolower($cnxinfo['cnxtype']);
    }

    if ($cnxinfo['port'] == "" || !is_numeric($cnxinfo['port'])) {
      if ($this->cnxtype == "http") {
        $this->port = 80;
      } else {
        $this->port = 443;
      }
    } else {
      $this->port = trim($cnxinfo['port']);
    }


    $this->user = trim($cnxinfo['username']);
    $this->password = trim($cnxinfo['password']);
    $this->tagtoken = str_replace(".", "", $this->ip);


    // Try to get Token from Config
    $this->token = config::byKey("token" . $this->tagtoken, 'reolink', 0);
    $this->tokenexp = config::byKey("tokenEXP" . $this->tagtoken, 'reolink', 0);

    $this->authmethod = config::byKey("authmethod", 'reolink');

    if ($this->authmethod == 0 && $this->reolinkTokenValidity() == false) {
      // TOKEN NOK (get new one)
      $this->login();
    } else {
      $this->is_loggedin = true;
    }
  }


  public function __destruct() {
    log::add('reolink', 'debug', str_repeat("=", 60));
  }

  private function request($cmd, $payload) {
    $ch = curl_init();
    $url = "$this->cnxtype://$this->ip:$this->port/cgi-bin/api.cgi?$cmd";
    // Debug REMOVE PWD
    $urldebug = preg_replace('/password=(.*?)$/', 'password=******', $url);
    // Debug REMOVE PWD
    $payloadLOG = preg_replace('/password":"(.*?)"}}}/', 'password":"******"}}}', $payload);
    log::add('reolink', 'debug', 'URL de requête => ' . $urldebug);
    log::add('reolink', 'debug', 'Payload => ' . $payloadLOG);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt(
      $ch,
      CURLOPT_HTTPHEADER,
      array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($payload)
      )
    );
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $header_size);

    if ($httpcode == 200) {
      log::add('reolink', 'debug', 'HTTP code 200 OK');
    } else {
      log::add('reolink', 'error', 'HTTP code ' . $httpcode . ' NOK ' . curl_error($ch) . ' Entête : ' . $header);
      return false;
    }
    curl_close($ch);

    //log::add('reolink', 'debug', 'Payload => '.print_r($payload, true));
    $debugResp = preg_replace('/\s+/', '', print_r($response, true));
    log::add('reolink', 'debug', 'Réponse caméra >> ' . $debugResp);
    return $response;
  }

  private function reolinkTokenValidity() {
    $date_utc = new DateTime("now", new DateTimeZone("UTC"));
    $tsnow = $date_utc->getTimestamp();

    log::add('reolink', 'debug', 'Vérification à ' . $tsnow . ' du TOKEN : ' . $this->token . ' Valable jusqu\'à : ' . ($this->tokenexp - 15));

    if (empty($this->token)) {
      log::add('reolink', 'debug', 'Aucun API Token > récupération nécéssaire');
      return false;
    }

    if ($tsnow > ($this->tokenexp) - 15) {
      log::add('reolink', 'debug', 'API Token expiré > renouvellement requis.');
      $this->is_loggedin = false;
      unset($this->token);
      unset($this->tokenexp);
      try {
        log::add('reolink', 'debug', 'Effacement du TOKEN id=' . $this->tagtoken);
        config::save("token" . $this->tagtoken, 0, 'reolink');
        config::save("tokenEXP" . $this->tagtoken, 0, 'reolink');
      } catch (Exception $e) {
        log::add('reolink', 'error', 'Erreur lors de l\'effacement du TOKEN');
      }
      return false;
    } else {
      log::add('reolink', 'debug', 'API Token OK');
      return true;
    }
  }

  private function login() {
    log::add('reolink', 'debug', 'Camera login...');
    $this->is_loggedin = false;
    unset($this->token);
    unset($this->tokenexp);
    $loginParameters = '[{"cmd":"Login","param":{"User":{"userName":"' . $this->user . '","password":"' . $this->password . '"}}}]';
    // query camera with parameters and return true if successful else false
    $response = $this->SendCMD($loginParameters, "Login");

    $this->token = $response[0]['value']['Token']['name'];
    $date_utc = new DateTime("now", new DateTimeZone("UTC"));
    $this->tokenexp = intval($response[0]['value']['Token']['leaseTime']) + ($date_utc->getTimestamp());


    if (!empty($this->token) && !empty($this->tokenexp)) {
      // Login OK
      $this->is_loggedin = true;
      log::add('reolink', 'debug', 'Token : ' . $this->token . ' Validité : ' . $this->tokenexp);
      try {
        config::save("token" . $this->tagtoken, $this->token, 'reolink');
        config::save("tokenEXP" . $this->tagtoken, $this->tokenexp, 'reolink');
        log::add('reolink', 'debug', 'TOKEN récupéré, enregistrement OK');
        return true;
      } catch (Exception $e) {
        log::add('reolink', 'error', 'Erreur lors de l\'enregistrement du TOKEN');
      }
    } else {
      // Login FAILED
      $this->is_loggedin = false;
      log::add('reolink', 'error', 'Echec > Impossible de se logguer sur la caméra');
      return false;
    }
  }


  public function SendCMD($POSTparameters, $URLrequest = NULL) {

    if (!$this->is_loggedin && !strpos($POSTparameters, 'cmd":"Login')) {
      log::add('reolink', 'error', 'Envoi de la commande impossible non loggué sur la caméra');
      return false;
    }

    if ($this->authmethod == 0) {
      $authURL = 'token=' . $this->token;
    } else {
      $authURL = 'user=' . $this->user . '&password=' . $this->password;
    }
    // Send Request

    if (!is_json($POSTparameters)) {
      log::add('reolink', 'error', 'Format du payload POST n\'est pas un JSON valide, envoi commande échoué');
      return false;
    }

    if (isset($URLrequest)) {
      log::add('reolink', 'debug', 'Ajout URL request = ' . $URLrequest);
      $URLrequest = "cmd=" . $URLrequest . "&";
    }

    $maxRetry = 0;
    do {

      $response = $this->request($URLrequest . $authURL, $POSTparameters);
      $checkedresponse = $this->checkResponse($response, $URLrequest);

      if (!$checkedresponse) {
        $maxRetry++;
        log::add('reolink', 'debug', 'Echec de la requête nouvelle tentative...');
      } else {
        break;
      }
    } while ($maxRetry < 3);

    return $checkedresponse;
  }

  /*
    Check Response validity
    */
  private function checkResponse($response, $command) {
    $data = json_decode($response, true);

    // General Case
    if (!$data || !isset($data[0])) {
      log::add('reolink', 'error', 'Requête vers la caméra en erreur. Réponse vide');
      return false;
    }

    if (isset($data[0]['error']['rspCode'])) {
      if ($data[0]['error']['rspCode'] == -6) {
        // Login failed, re-authentification need
        $this->login();
        return false;
      }

      $errorNFO = $this->GetErrorNFO($data[0]['error']['rspCode']);
      log::add('reolink', 'error', 'Requête vers la caméra en erreur. Réponse de la caméra : ' . ucfirst($errorNFO[0]) . '/' . ucfirst($errorNFO[1]));
      return false;
    }

    return $data;
  }

  private function GetErrorNFO($errorCode) {
    switch ($errorCode) {
      case -1:
        return array('not exist', 'Missing parameters');
      case -2:
        return array('out of mem', 'Used up memory');
      case -3:
        return array('check err', 'Check error');
      case -4:
        return array('param error', 'Parameters error');
      case -5:
        return array('max session', 'Reached the max session number.');
      case -6:
        return array('please login first', 'Login required');
      case -7:
        return array('login failed', 'Login error');
      case -8:
        return array('timeout', 'Operation timeout');
      case -9:
        return array('not support', 'Not supported');
      case -10:
        return array('protocol', 'Protocol error');
      case -11:
        return array('fcgi read failed', 'Failed to read operation');
      case -12:
        return array('get config failed', 'Failed to get configuration.');
      case -13:
        return array('set config failed', 'Failed to set configuration.');
      case -14:
        return array('malloc failed', 'Failed to apply for memory');
      case -15:
        return array('create socket failed', 'Failed to created socket');
      case -16:
        return array('send failed', 'Failed to send data');
      case -17:
        return array('rcv failed', 'Failed to receiver data');
      case -18:
        return array('open file failed', 'Failed to open file');
      case -19:
        return array('read file failed', 'Failed to read file');
      case -20:
        return array('write file failed', 'Failed to write file');
      case -21:
        return array('error token', 'Token error');
      case -22:
        return array('The length of the string exceeds the limit', 'The length of the string exceeds the limitmation');
      case -23:
        return array('missing param', 'Missing parameters');
      case -24:
        return array('error command', 'Command error');
      case -25:
        return array('internal error', 'Internal error');
      case -26:
        return array('ability error', 'Ability error');
      case -27:
        return array('invalid user', 'Invalid user');
      case -28:
        return array('user already exist', 'User already exist');
      case -29:
        return array('maximum number of users', 'Reached the maximum number of users');
      case -30:
        return array('same version', 'The version is identical to the current one.');
      case -31:
        return array('busy', 'Ensure only one user can upgrade');
      case -32:
        return array('ip conflict', 'Modify IP conflicted with used IP');
      case -34:
        return array('need bing email', 'Cloud login need bind email first');
      case -35:
        return array('unbind', 'Cloud login unbind camera');
      case -36:
        return array('network timeout', 'Cloud login get login information out of time');
      case -37:
        return array('password err', 'Cloud login password error');
      case -38:
        return array('uid err', 'Cloud bind camera uid error');
      case -39:
        return array('user not exist', 'Cloud login user doesn’t exist');
      case -40:
        return array('unbind failed', 'Cloud unbind camera failed');
      case -41:
        return array('cloud not support', 'The device doesn’t support cloud');
      case -42:
        return array('login cloud server failed', 'Cloud login server failed');
      case -43:
        return array('bind failed', 'Cloud bind camera failed');
      case -44:
        return array('cloud unknown err', 'Cloud unknown error');
      case -45:
        return array('need verify code', 'Cloud bind camera need verify code');
      case -48:
        return array('Fetching a picture failed', 'Snap a picture failed');
      case -49:
        return array('Channel invalid', 'Channel is invalid');
      case -99:
        return array('unknow', 'unknow');
      case -100:
        return array('test failed', 'Test Email/Ftp/Wifi failed');
      case -101:
        return array('check firmware failed', 'Upgrade checking firmware failed');
      case -102:
        return array('download online failed', 'Upgrade download online failed');
      case -103:
        return array('get upgrade status failed', 'Upgrade get upgrade status failed');
      case -105:
        return array('Frequent logins, please try again later!', 'Frequent logins');
      case -451:
        return array('ftp login failed', 'ftp test login failed');
      case -452:
        return array('ftp create dir failed', 'Creat ftp dir failed');
      case -453:
        return array('ftp upload failed', 'Upload ftp file failed');
      case -454:
        return array('ftp connect failed', 'Cannot connect ftp server');
      case -480:
        return array('email undefined failed', 'Some undifined errors');
      case -481:
        return array('email connect failed', 'Cannot connect email server');
      case -482:
        return array('email auth failed', 'Auth user failed');
      case -483:
        return array('email network err', 'Email network err');
      case -484:
        return array('email server err', 'Something wrong with email server ');
      case -485:
        return array('email memory err', 'Something wrong with memory');
    }
  }
}
