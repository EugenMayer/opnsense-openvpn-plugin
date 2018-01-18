# WAT

Yet partial implementation of the OpenVPN plugin in MVC style ( not legacy ) offering API interface to automate things.

Yet it includes

 - CCD / client specific overides CRUD operations
 
**This plugin is NOT transparent to the GUI part of openvpn (legacy) which handles `Client Specific Overides`. So you 
want to use the openvpn GUI for CSO or this Web-API. You can use all other GUI aspects though**
 
## Installation

You should install / use it along the core openvpn "plugin" - consider this plugin as a addition.

On your opnsense box do

```bash
curl -Lo os-openvpn-devel-0.0.3.txz https://github.com/EugenMayer/opnsense-openvpn-plugin/raw/master/dist/os-openvpn-devel-0.0.3.txz
pkg add os-openvpn-devel-0.0.3.txz
```

## Using the API

Enable/install the plugin

#### Create / Update CCDs

`POST` on `api/openvpn/ccd/setCcdBy`
```
{
  "ccd": { 
    "enabled": "1",
    "common_name": "newtests",
    "description": "",
    "tunnel_network": "11.11.11.2/224",
    "tunnel_networkv6": "",
    "local_network": "",
    "local_network6": "",
    "remote_network": "",
    "remote_networkv6": "",
    "push_reset": "0",
    "block": "0"
  }
}
```

If a ccd with that `common_name` already exists, and update will be done, otherwise it will be created 


#### Delete CCD

`POST`  on `api/openvpn/ccd/delCcd/<uuid>`

-> If the ccd matching the given <uuid> it will be deleted

#### Get CCD(s)

`GET` on `api/openvpn/ccd/getCcd` 
- This will return all ccd entries

`GET` on`api/openvpn/ccd/getCcd/<uuid>`
- This will return you the ccd matching this uuid 