<?php
$ip = $_SERVER['REMOTE_ADDR'];
/*
if (!in_array($ip, [])) {
    header('HTTP/1.1 403 Forbidden');
    exit(0);
}
*/
$config = [
    'log' => [
        "access" => "/var/log/v2ray/access.log",
        "error" => "/var/log/v2ray/error.log",
        "loglevel" => "warning"
    ],
    "inbound" => [
        "port" => 20567,
        "protocol" => "vmess",
        "settings" => [
            "clients" => [
                [
                    "id" => "3b7ae84e-6630-4d4f-a874-3ad9838972a3",
                    "level" => 1,
                    "alterId" => 64
                ],
            ],
        ]
    ],
    'inboundDetour' => [
        [
            "port" => 10000,
            "protocol" => "vmess",
            "settings" => [
                "clients" => [
                    [
                        "id" => "c6fd1b72-9b7f-4e4b-9e93-e6ffd54568c2",
                        "level" => 1,
                        "alterId" => 64
                    ]
                ]
            ],
            "streamSettings" => [
                "network" => "ws",
                "wsSettings" => [
                    "path" => "/fetch"
                ]
            ]
        ]
    ], "outbound" => [
        "protocol" => "freedom",
        "settings" => []
    ],
    "outboundDetour" => [
        [
            "protocol" => "blackhole",
            "settings" => [],
            "tag" => "blocked"
        ]
    ],
    "routing" => [
        "strategy" => "rules",
        "settings" => [
            "rules" => [
                [
                    "type" => "field",
                    "domain" => [
                        "domain:totheglory.im",
                        "domain:hdchina.org",
                        "domain:hdsky.me",
                        "domain:hdcmct.org",
                        "domain:chdbits.co",
                        "domain:dmhy.org",
                        "domain:hdtime.org",
                        "domain:open.cd",
                        "domain:jpopsuki.eu",
                        "domain:uhdbits.org",
                        "domain:m-team.cc",
                        "domain:hdroute.org",
                        "domain:et8.org",
                        "domain:ccfbits.org",
                        "domain:eastgame.org",
                        "domain:leniter.org",
                        "domain:upxin.net",
                        "domain:hdhome.org",
                        "domain:hdarea.co",
                        "domain:icetorrent.org"
                    ],
                    "outboundTag" => "blocked"
                ],
                [
                    "type" => "field",
                    "ip" => [
                        "0.0.0.0/8",
                        "10.0.0.0/8",
                        "100.64.0.0/10",
                        "127.0.0.0/8",
                        "169.254.0.0/16",
                        "172.16.0.0/12",
                        "192.0.0.0/24",
                        "192.0.2.0/24",
                        "192.168.0.0/16",
                        "198.18.0.0/15",
                        "198.51.100.0/24",
                        "203.0.113.0/24",
                        "::1/128",
                        "fc00::/7",
                        "fe80::/10"
                    ],
                    "outboundTag" => "blocked"
                ]
            ]
        ]
    ]
];
header('Content-type: application/json');
echo json_encode($config);
exit(0)
?>