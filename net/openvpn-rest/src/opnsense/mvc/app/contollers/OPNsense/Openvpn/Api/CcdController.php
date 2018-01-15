<?php
namespace OPNsense\Openvpn\Api;


use OPNsense\Base\ApiMutableModelControllerBase;
use OPNsense\Core\Config;
use OPNsense\Openvpn\common\OpenVpn;

class CcdController extends ApiMutableModelControllerBase
{
    static protected $internalModelName = 'Ccd';
    static protected $internalModelClass = '\OPNsense\Openvpn\Ccd';

    public function getAction()
    {
        // define list of configurable settings
        $result = array();
        if ($this->request->isGet()) {
            $mdlUser = new User();
            $result['ccd'] = $mdlUser->getNodes();
        }
        return $result;
    }

    public function setAction()
    {
        $result = array("result" => "failed");
        if ($this->request->isPost()) {
            // load model and update with provided data
            $mdlCcd = new User();
            $mdlCcd->setNodes($this->request->getPost("ccd"));
            // perform validation
            $valMsgs = $mdlCcd->performValidation();
            foreach ($valMsgs as $field => $msg) {
                if (!array_key_exists("validations", $result)) {
                    $result["validations"] = array();
                }
                $result["validations"]["ccd." . $msg->getField()] = $msg->getMessage();
            }
            // serialize model to config and save
            if ($valMsgs->count() == 0) {
                $mdlCcd->serializeToConfig();
                Config::getInstance()->save();
                $result["result"] = "saved";
            }
        }
        return $result;
    }

    public function addCcdAction()
    {
        $result = array("result" => "failed");
        if ($this->request->isPost() && $this->request->hasPost("ccd")) {
            $result = array("result" => "failed", "validations" => array());
            $mdlCcd = $this->getModel();
            $node = $mdlCcd->users->user->Add();
            $newCcd = $this->request->getPost("ccd");
            $node->setNodes($newCcd);
            $valMsgs = $mdlCcd->performValidation();
            foreach ($valMsgs as $field => $msg) {
                $fieldnm = str_replace($node->__reference, "ccd", $msg->getField());
                $result["validations"][$fieldnm] = $msg->getMessage();
            }
            if (count($result['validations']) == 0) {
                unset($result['validations']);
                // save config if validated correctly
                $mdlCcd->serializeToConfig();
                Config::getInstance()->save();
                unset($result['validations']);
                $result["result"] = "saved";
            }
        }
        return $result;
    }

    public function delCcdAction($uuid)
    {
        $result = array("result" => "failed");
        if ($this->request->isPost()) {
            $mdlCcd = $this->getModel();
            if ($uuid != null) {
                $userToBeDeleted = $mdlCcd->getNodeByReference("$uuid");
                if ($mdlCcd->del($uuid)) {
                    $mdlCcd->serializeToConfig();
                    Config::getInstance()->save();
                    $result['result'] = 'deleted';
                } else {
                    $result['result'] = 'not found';
                }
            }
        }
        return $result;
    }

    public function setCcdAction($uuid)
    {
        if ($this->request->isPost() && $this->request->hasPost("ccd")) {
            $mdlSetting = $this->getModel();
            if ($uuid != null) {
                $node = $mdlSetting->getNodeByReference($uuid);
                if ($node != null) {
                    $result = array("result" => "failed", "validations" => array());
                    $ccdInfo = $this->request->getPost("ccd");
                    $node->setNodes($ccdInfo);
                    $valMsgs = $mdlSetting->performValidation();
                    foreach ($valMsgs as $field => $msg) {
                        $fieldnm = str_replace($node->__reference, "ccd", $msg->getField());
                        $result["validations"][$fieldnm] = $msg->getMessage();
                    }
                    if (count($result['validations']) == 0) {
                        // save config if validated correctly
                        $mdlSetting->serializeToConfig();
                        Config::getInstance()->save();
                        $result = array("result" => "saved");
                    }
                    return $result;
                }
            }
        }
        return array("result" => "failed");
    }
}