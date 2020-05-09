<?php

namespace App\Modules\Nower;

use App\Modules\Comment\Entity\ModuleComment_EntityComment;
use App\Modules\User\ModuleUser;
use Engine\LS;
use Engine\Module;

class ModuleNower extends Module {
    public function Init() {}

    public function PostNotificationWithComment($aData, ModuleComment_EntityComment $oComment) {
    	$aData = $aData->getArrayData();
    	if ($oUserDelete = LS::Make(ModuleUser::class)->GetUserById($oComment->getDeleteUserId())) {
			$deleteUserLogin = $oUserDelete->getLogin();
		} else {
			$deleteUserLogin = "";
		}
		$aData = array_merge($aData, array('comment_extra' => array(
			'text' => $oComment->getText(),
			'deleteReason' => $oComment->getDeleteReason(),
			'deleteUserLogin' => $deleteUserLogin,
			'rating' => $oComment->getRating(),
			'countVote' => $oComment->getCountVote()
		)));
		$this->post($aData);
    }

    public function PostNotification($aData=array()) {
    	$aData = $aData->getArrayData();
		$this->post($aData);
    }

    private function post($json) {
		$myCurl = curl_init();
		curl_setopt_array($myCurl, array(
			CURLOPT_URL => 'http://127.0.0.1:3000/notification', //TODO: Вынести в конфиг
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => http_build_query($json)
		));
		curl_exec($myCurl);
		curl_close($myCurl);
	}
}