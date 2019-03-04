**DISCONTINUED:** this plugin has been for no discontinued since it has been replaced with https://github.com/opnsense/core/pull/3277 , also see https://github.com/EugenMayer/opnsense-openvpn-ldap-cso

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
setenv openvpn_version 0.0.6
curl -Lo os-openvpn-devel-${openvpn_version}.txz https://github.com/EugenMayer/opnsense-openvpn-plugin/raw/master/dist/os-openvpn-devel-${openvpn_version}.txz
pkg add os-openvpn-devel-${openvpn_version}.txz
```

## Using the API

Enable/install the plugin

#### Create / Update CCDs

`POST` on `api/openvpn/ccd/setCcd`
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
Creates a new CCD


`POST` on `api/openvpn/ccd/setCcd/<uuid>`
same pyaload as above, but with `uuid` - Update 


`POST` on `api/openvpn/ccd/setCcdByName`
same pyaload as above, but with will do an update if the common_name already exists 

#### Delete CCD

`POST`  on `api/openvpn/ccd/delCcd/<uuid>`
-> If the ccd matching the given <uuid> it will be deleted

`POST`  on `api/openvpn/ccd/delCcdByName/<commanName>`
-> If the ccd matching the given <commonName> it will be deleted

#### Get CCD(s)

`GET` on `api/openvpn/ccd/getCcd` 
- This will return all ccd entries

`GET` on`api/openvpn/ccd/getCcd/<uuid>`
- This will return you the ccd matching this uuid

`GET` on `api/openvpn/ccd/getCcdByName/<commonName>` 
- This will return a ccd matched by name

#### Generate all CCDS

`POST` on `api/openvpn/ccd/generateCcds`
- no payload needed, this will regenerate all existing CCDs for all servers and write them on the disk for openvpn to pickup during connections

## Development

### Start

No magic involved here, fires up a vagrant build on the recent [opnsense-build](https://app.vagrantup.com/eugenmayer/boxes/opnsense)

```
make start
```

1. You see the plugin deployed in the opnsense instance, access it by https://localhost:10443
2. If you change code, just run `make sync_plugin`
3. Its all on you now :)

### Stop ( pause )
To stop the vm ( not losing state, continue later )
```   
make stop
```

### Rm ( end, remove all )
To remove the VM
```
make rm
```

## During development

### Plugins

If you change code of the plugin, run

    make sync_plugin 
