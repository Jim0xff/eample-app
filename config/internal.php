<?php
/**
 * Internal Service Client Base URI settings
 */
return [
    'graph' => env('GRAPH_URL', 'http://localhost:8000/subgraphs/name/metis-pump-subgraph'),
    'coingecko' => env('COINGECKO_URL', 'https://pro-api.coingecko.com/'),
    'task_point' => env('TASK_POINT_URL', 'http://localhost:8010/'),
    'service_fee_percent' => env('SERVICE_FEE_PERCENT', '1'),
    'open-launch-chat-service' => env('OPEN_LAUNCH_CHAT_SERVICE', 'http://localhost:4000/'),
    'co_build_agent_internal_service' => env('CO_BUILD_AGENT_SERVICE', 'https://laz-chat-test-wlk9n.ondigitalocean.app/'),
    'bonding_curve_a' => env('BOUNDING_CURVE_A', '19827401'),
    'coingecko_api_key' => env('COINGECKO_API_KEY', ''),
    'airdrop_service_url' => env('AIRDROP_SERVICE', 'http://localhost:4001/'),
    'airdrop_service_api_key' => env('AIRDROP_API_KEY', '27831'),
    'vendor' => env('INTERNAL_VENDOR', 'http://192.168.3.115:8817/vender-service/'),
    'wallet' => env('INTERNAL_WALLET', 'http://192.168.3.247:8090/walletservice/api/wallet/'),
    'foundation_data' => env('INTERNAL_FOUNDATION_DATA', 'http://192.168.3.115:8808/foundationdata/'),
    'notification' => env('INTERNAL_NOTIFICATION', 'http://192.168.3.115:8840/notification/'),
    'order' => env('INTERNAL_ORDER', 'http://192.168.3.115:8804/orderservice/'),
    'operation' => env('INTERNAL_OPERATION', 'http://192.168.3.115:8802/operationservice/'),
    'foundation' => env('INTERNAL_FOUNDATION', 'http://192.168.3.247:8801/foundationservice/'),
    'service_factory' => env('SERVICE_FACTORY', 'http://192.168.3.115:8850/servicefactory/'),
    'inspection' => env('INTERNAL_INSPECTION', 'http://192.168.3.115:8819/inspectionservice/'),
    'inquiry' => env('INTERNAL_INQUIRY', 'http://192.168.3.115:8811/inquiry/'),//线上http://100.98.210.122:8080/inquiry
    'bi_service' => env('INTERNAL_BI', 'http://192.168.3.115:8824/biservice/'),//线上:http://100.114.52.13:8080/biservice
    'payment' => env('INTERNAL_PAYMENT', 'http://192.168.3.115:8807/payment/'),
    'airent'  => env('INTERNAL_AIRENT','http://test11.bbf.airent.test.aiershou.com/easyUse/pay-ment/'),
    'logistics'  => env('INTERNAL_LOGISTICS', 'http://47.96.53.33/logistics/'),
    'finance' => env('INTERNAL_FINANCE', 'http://192.168.3.115:8821/financeservice/'),
    'finance_account_id' => env('FINANCE_ACCOUNT_ID', '1274'),
    'opt_foundation_data' => env('INTERNAL_OPT_FOUNDATION_DATA', 'http://192.168.3.115:8889/opt-foundation-service/'),
    'inspection_proxy' => env('INSPECTION_PROXY','http://47.96.53.33:8080/opt-app-service/'),
    'opt_risk_control' => env('INTERNAL_OPT_RISK_CONTROL','http://10.180.3.101/opt-risk-control/'),
    'opt_wallet' => env('INTERNAL_OPT_WALLET','http://10.180.3.102/opt-wallet/'),
    'quotation_product' => env('INTERNAL_QUOTATION_PRODUCT'),
    'opt_trade' => env('INTERNAL_OPT_TRADE', 'http://47.96.53.33/opt-trade-service/'),
    'mta' => env('INTERNAL_MTA', 'https://uatmtaaa.aihuishou.com/'),
    'aijihui_service' => env('INTERNAL_AIJIHUI_SERVICE', 'http://47.96.53.33/aijihui-service'),
    'opt_inspection_service' => env('OPT_INSPECTION_SERVICE', 'http://47.96.53.33/opt-inspection-service/'),
    'foundation_data_center' => env('INTERNAL_FOUNDATION_DATA_CENTER', 'http://47.96.53.33/foundation-data-center/'),
    'recycle_business_center' => env('INTERNAL_RECYCLE_BUSINESS_CENTER', 'http://47.96.53.33/recycle-business-center/'),

];
