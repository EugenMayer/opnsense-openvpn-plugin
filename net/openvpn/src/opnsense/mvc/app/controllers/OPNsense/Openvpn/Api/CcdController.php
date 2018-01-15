<?php
namespace OPNsense\Openvpn\Api;


use OPNsense\Base\ApiMutableModelControllerBase;
use OPNsense\Core\Config;
use OPNsense\Openvpn\Ccd;

class CcdController extends ApiMutableModelControllerBase
{
    static protected $internalModelName = 'Ccd';
    static protected $internalModelClass = '\OPNsense\Openvpn\Ccd';

    /**
     * Payload must look like this
     * {
     *   "ccd": { "common_name":"newtest" }
     * }
     * @param string|null $uuid item unique id
     * @return array
     */
    public function setCcdAction($uuid = null)
    {
        if ($this->request->isPost() && $this->request->hasPost("ccd")) {
            if ($uuid != null) {
                $node = $this->getModel()->getNodeByReference("ccds.ccd.$uuid");
            } else {
                $node = $this->getModel()->ccds->ccd->Add();
            }
            $node->setNodes($this->request->getPost("ccd"));
            return $this->validateAndSave($node, 'ccd');
        }
        return array("result"=>"failed");
    }

    /**
     * @param string|null $uuid item unique id
     * @return array
     */
    public function getCcdAction($uuid = null)
    {
        if ($uuid == null) {
            // generate new node, but don't save to disc
            $node = $this->getModel()->ccds->ccd->Add();
            return array("ccd" => $node->getNodes());
        } else {
            $node = $this->getModel()->getNodeByReference('ccds.ccd.'.$uuid);
            if ($node != null) {
                // return node
                return array("ccd" => $node->getNodes());
            }
        }
        return array();
    }


    public function delCcdAction($uuid)
    {
        $result = array('result' => 'failed');
        if ($this->request->isPost()) {
            if ($this->getModel()->ccds->ccd->del($uuid)) {
                $result = $this->validateAndSave();
            }
        }
        return $result;
    }
}