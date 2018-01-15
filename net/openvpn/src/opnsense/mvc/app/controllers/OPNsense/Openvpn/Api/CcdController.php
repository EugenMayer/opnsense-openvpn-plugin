<?php

namespace OPNsense\Openvpn\Api;


use \OPNsense\Base\ApiMutableModelControllerBase;
use \OPNsense\Core\Config;
use \OPNsense\Openvpn\Ccd;

/**
 * Class CcdController
 * @method \OPNsense\Openvpn\Ccd getModel
 * @method array getNodes
 * @method setNodes
 * @property \Phalcon\Http\Request request
 * @package OPNsense\Openvpn\Api
 */
class CcdController extends ApiMutableModelControllerBase
{
    static protected $internalModelName = 'Ccd';
    static protected $internalModelClass = '\OPNsense\Openvpn\Ccd';

    /**
     * Payload must look like this
     * {
     *   "ccd": { "common_name":"newtest" }
     * }
     *
     * If uuid is given, operates as update but ensures name is unique ( fails otherwise )
     * if uud is omited, operates as create
     * @param string|null $uuid item unique id
     * @return array
     */
    public function setCcdAction($uuid = null)
    {
        if ($this->request->isPost() && $this->request->hasPost("ccd")) {
            if ($uuid != null) {
                $node = $this->getModel()->getNodeByReference("ccds.ccd.$uuid");
            } else {
                /** @var \OPNsense\Openvpn\Ccd $node */
                $node = $this->getModel()->ccds->ccd->Add();
            }

            $data = $this->request->getPost("ccd");
            if ($this->getModel()->getCcdByName($data['common_name']) == NULL) {
                $node->setNodes($data);
                return $this->validateAndSave($node, 'ccd');
            } else {
                return ["result" => "failed", 'validation' => "a ccd with the name '{$data['common_name']}' already exists"];
            }
        }
        return array("result" => "failed");
    }

    /**
     * Payload must look like this
     * {
     *   "ccd": { "common_name":"newtest" }
     * }
     *
     * in comparison to setCcdAction this method tries to find your given CCD by name
     * it does find it, it rather does a update, otherwise and insert.
     * So this will automatically update if a name matches an existing entry or create
     * if that name yet does not exist
     *
     * @param string|null $uuid item unique id
     * @return array
     */
    public function setCcdByNameAction()
    {
        if ($this->request->isPost() && $this->request->hasPost("ccd")) {
            $data = $this->request->getPost("ccd");
            $lookupUuid = $this->getModel()->getCcdByName($data['common_name']);
            if ($lookupUuid == NULL) {
                // create case
                $node = $this->getModel()->ccds->ccd->Add();
                $node->setNodes($data);
                return $this->validateAndSave($node, 'ccd');
            } else {
                // update case
                $node = $this->getModel()->getNodeByReference("ccds.ccd.$lookupUuid");
                $node->setNodes($data);
                return $this->validateAndSave($node, 'ccd');
            }
        }
        return array("result" => "failed");
    }

    /**
     * @param string|null $uuid item unique id
     * @return array
     */
    public function getCcdAction($uuid = null)
    {
        if ($uuid == null) {
            // list all
            return array($this->getModel()->getNodes());
        } else {
            $node = $this->getModel()->getNodeByReference('ccds.ccd.' . $uuid);
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