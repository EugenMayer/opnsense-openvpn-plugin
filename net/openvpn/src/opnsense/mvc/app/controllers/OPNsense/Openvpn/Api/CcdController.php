<?php

namespace OPNsense\Openvpn\Api;


use \OPNsense\Base\ApiMutableModelControllerBase;
use \OPNsense\Core\Config;
use \OPNsense\Openvpn\Ccd;
use OPNsense\Openvpn\common\CcdDts;
use OPNsense\Openvpn\common\OpenVpn;
use Phalcon\Http\Response\Headers;

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
            if ($this->getModel()->getUuidByCcdName($data['common_name']) == NULL) {
                $node->setNodes($data);
                $this->validateAndSave($node, 'ccd');
                OpenVpn::generateCCDconfigurationOnDisk([CcdDts::fromModelNode($data)]);
                $result['uuid'] = $this->getModel()->getUuidByCcdName($data['common_name']);
                $this->returnData($result);
            } else {
                http_response_code(405);
                $this->returnError("a ccd with the name '{$data['common_name']}' already exists");
            }
        }
        http_response_code(500);
        $this->returnError("only POST supported");
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
     */
    public function setCcdByNameAction()
    {
        if ($this->request->isPost() && $this->request->hasPost("ccd")) {
            $data = $this->request->getPost("ccd");
            $lookupUuid = $this->getModel()->getUuidByCcdName($data['common_name']);
            if ($lookupUuid == NULL) {
                // create case
                $node = $this->getModel()->ccds->ccd->Add();
                $node->setNodes($data);
            } else {
                // update case
                $node = $this->getModel()->getNodeByReference("ccds.ccd.$lookupUuid");
                $node->setNodes($data);
            }
            $result = $this->validateAndSave($node, 'ccd');
            OpenVpn::generateCCDconfigurationOnDisk([CcdDts::fromModelNode($data)]);
            $result['uuid'] = $this->getModel()->getUuidByCcdName($data['common_name']);
            $this->returnData($result);
        }
        http_response_code(500);
        $this->returnError("only POST supported");
    }

    /**
     * @param string|null $uuid item unique id
     */
    public function getCcdAction($uuid = null)
    {
        if ($uuid == null) {
            // list all
            $data = [];
            foreach($this->getModel()->getNodes()['ccds']['ccd'] as $uuid => $ccd) {
                $ccd['uuid'] = $uuid;
                $data[] = $ccd;
            }
            $this->returnData($data);
        } else {
            $node = $this->getModel()->getNodeByReference('ccds.ccd.' . $uuid);
            if ($node != null) {
                // return node
                $ccd = $node->getNodes();
                $ccd['uuid'] = $uuid;
            }
            else {
                http_response_code(404);
                $this->returnError("not found");
            }
        }
    }

    /**
     * @param string|null $commonName item unique id
     */
    public function getCcdByNameAction($commonName = null)
    {
        if ($commonName == null) {
            // list all
            $data = [];
            foreach($this->getModel()->getNodes()['ccds']['ccd'] as $uuid => $ccd) {
                $ccd['uuid'] = $uuid;
                $data[] = $ccd;
            }
            $this->returnData($data);
        } else {
            $lookupUuid = $this->getModel()->getUuidByCcdName($commonName);
            $node = $this->getModel()->getNodeByReference('ccds.ccd.' . $lookupUuid);
            if ($node != null) {
                // return node
                $ccd = $node->getNodes();
                $ccd['uuid'] = $lookupUuid;
                $this->returnData($ccd);
            }
            else {
                http_response_code(404);
                $this->returnError("not found");
            }
        }
    }


    /**
     * @param $uuid
     */
    public function delCcdAction($uuid)
    {
        $result = array('result' => 'failed');
        if ($this->request->isPost()) {
            $node = $this->getModel()->getNodeByReference("ccds.ccd.$uuid");
            if ($node == NULL) {
                http_response_code(404);
                $this->returnError("not found");
            }

            $ccd = CcdDts::fromModelNode($node->getNodes());
            OpenVpn::deleteCCD($ccd->common_name);
            if ($this->getModel()->ccds->ccd->del($uuid)) {
                $result = $this->validateAndSave();
                $result['uuid'] = $uuid;
                $this->returnData($result);
            }
            http_response_code(404);
            $this->returnError("not found");
        }

        http_response_code(500);
        $this->returnError("only POST supported");
    }

    /**
     * @param $commonName
     */
    public function delCcdByNameAction($commonName)
    {
        if ($this->request->isPost()) {
            $lookupUuid = $this->getModel()->getUuidByCcdName($commonName);
            $node = $this->getModel()->getNodeByReference("ccds.ccd.$lookupUuid");
            if ($node == NULL) {
                http_response_code(404);
                $this->returnError("not found");
            }

            $ccd = CcdDts::fromModelNode($node->getNodes());
            OpenVpn::deleteCCD($ccd->common_name);
            if ($this->getModel()->ccds->ccd->del($lookupUuid)) {
                $result = $this->validateAndSave();
                $result['removed_uuid'] = $lookupUuid;
                $this->returnData($result);
            }

            http_response_code(404);
            $this->returnError("not found");
        }

        http_response_code(500);
        $this->returnError("only POST supported");
    }

    /**
     * Regenerate all ccds for all servers
     */
    public function generateCcdsAction()
    {
        if ($this->request->isPost()) {
            try {
                OpenVpn::generateCCDconfigurationOnDisk();
                http_response_code(200);
                $this->returnData([]);
            } catch(\Exception $e) {
                http_response_code(500);
                $this->returnError("Error:".$e->getMessage());
            }

        }
        http_response_code(500);
        $this->returnError("only POST supported");
    }

    private function returnData($data) {
        $response = new \stdClass();
        $response->data = $data;
        $response->status = 'success';
        header('Content-type: application/json');
        echo json_encode($response);
        exit(0);
    }

    private function returnError($message) {
        $response = new \stdClass();
        $response->status = "error";
        $response->message = $message;
        header('Content-type: application/json');
        echo json_encode($response);
        exit(0);
    }
}